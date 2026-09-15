<?php

namespace App\Services\Semantic;

use App\Models\Job;
use App\Models\SemanticEmbedding;
use App\Models\SemanticHealthEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Stored-vector lifecycle for the optional hosted embedding provider.
 *
 * Vectors live in the semantic_embeddings table, keyed by (entity_type,
 * entity_id, provider, model, embedding_version). Generation is idempotent: a
 * stored vector whose content hash matches the current representation is never
 * regenerated. All input is the privacy-filtered representation built by
 * SemanticRepresentation; raw names, emails and phone numbers never reach a
 * provider.
 *
 * Generation is deliberately NOT performed on ranking hot paths. It is driven
 * by the Generate* queue jobs (wired into the existing recalculate jobs) and
 * the semantic:refresh command, so shared hosting without a persistent worker
 * can batch generation via cron.
 */
class SemanticEmbeddingService
{
    public function __construct(protected SemanticRepresentation $representations) {}

    public function active(): bool
    {
        return $this->configuredProvider() === 'embeddings'
            && (bool) config('matching.semantic.enabled', false)
            && trim((string) config('matching.embedding.api_key', '')) !== '';
    }

    public function vectorFor(string $entityType, int $entityId): ?array
    {
        if ($this->configuredProvider() !== 'embeddings') {
            return null;
        }

        $row = $this->embeddingFor($entityType, $entityId);

        return $row?->vector();
    }

    public function embeddingFor(string $entityType, int $entityId): ?SemanticEmbedding
    {
        return SemanticEmbedding::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('provider', $this->configuredProvider())
            ->where('model', $this->model())
            ->where('embedding_version', $this->version())
            ->latest()
            ->first();
    }

    public function generateForCandidate(User $candidate): string
    {
        if (! $this->active()) {
            return 'disabled';
        }

        return $this->generate($this->representations->forCandidate($candidate));
    }

    public function generateForJob(Job $job): string
    {
        if (! $this->active()) {
            return 'disabled';
        }

        return $this->generate($this->representations->forJob($job));
    }

    public function generateForEntity(User|Job $entity): string
    {
        return $entity instanceof Job
            ? $this->generateForJob($entity)
            : $this->generateForCandidate($entity);
    }

    /**
     * Cosine similarity between two equal-length vectors. Returns 0 for
     * empty or mismatched input.
     */
    public function cosine(array $a, array $b): float
    {
        if ($a === [] || $b === [] || count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $index => $value) {
            $dot += (float) $value * (float) $b[$index];
            $normA += (float) $value ** 2;
            $normB += (float) $b[$index] ** 2;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function generate(array $payload): string
    {
        $existing = $this->embeddingFor($payload['entity_type'], (int) $payload['id']);

        if ($existing !== null && $existing->isCurrent((string) $payload['content_hash'])) {
            return 'current';
        }

        if ($this->mustDefer()) {
            return 'deferred';
        }

        $started = microtime(true);

        try {
            $response = Http::timeout((int) config('matching.embedding.timeout_seconds', 30))
                ->acceptJson()
                ->withToken(trim((string) config('matching.embedding.api_key')))
                ->retry(2, 500)
                ->post(config('matching.embedding.api_url'), [
                    'model' => $this->model(),
                    'input' => $this->representations->toText($payload),
                ]);

            $response->throw();

            $vector = $response->json('data.0.embedding');

            if (! is_array($vector) || $vector === []) {
                throw new \RuntimeException('Embedding provider response is missing data[0].embedding');
            }

            $this->upsert($payload, array_values($vector));

            $this->health('generate', 'success', $this->latencyMs($started));

            return 'generated';
        } catch (\Throwable $e) {
            $this->health('generate', 'failure', $this->latencyMs($started), null, $e->getMessage());

            return 'failed';
        }
    }

    private function upsert(array $payload, array $vector): void
    {
        SemanticEmbedding::updateOrCreate(
            [
                'entity_type' => $payload['entity_type'],
                'entity_id' => (int) $payload['id'],
                'provider' => $this->configuredProvider(),
                'model' => $this->model(),
                'embedding_version' => $this->version(),
            ],
            [
                'embedding' => $vector,
                'dimensions' => count($vector),
                'content_hash' => (string) $payload['content_hash'],
                'generated_at' => now(),
            ],
        );
    }

    /**
     * Space out provider calls under shared hosting so a batch never trips
     * the provider's rate limit. Returns true when a generation should be
     * deferred to a later invocation.
     */
    private function mustDefer(): bool
    {
        $minIntervalMs = (int) config('matching.embedding.min_interval_ms', 800);

        if ($minIntervalMs <= 0) {
            return false;
        }

        $last = SemanticEmbedding::query()
            ->where('provider', $this->configuredProvider())
            ->where('model', $this->model())
            ->max('updated_at');

        if ($last === null) {
            return false;
        }

        return Carbon::parse($last)->addMilliseconds($minIntervalMs)->isAfter(now());
    }

    private function health(string $action, string $status, ?int $latencyMs = null, ?string $message = null, ?string $error = null): void
    {
        SemanticHealthEvent::create([
            'provider' => $this->configuredProvider(),
            'model' => $this->model(),
            'action' => $action,
            'status' => $status,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'error' => $error !== null ? Str::limit($error, 500) : null,
        ]);
    }

    private function configuredProvider(): string
    {
        return (string) config('matching.semantic.provider', 'lexical');
    }

    private function model(): string
    {
        return (string) config('matching.embedding.model', 'text-embedding-3-small');
    }

    private function version(): string
    {
        return (string) config('matching.embedding.version', '2026-09-15');
    }

    private function latencyMs(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }
}
