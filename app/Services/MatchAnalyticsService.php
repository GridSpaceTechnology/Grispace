<?php

namespace App\Services;

use App\Enums\MatchOutcomeEventType;
use App\Models\CandidateRecommendationFeedback;
use App\Models\EmployerRecommendationFeedback;
use App\Models\MatchOutcomeEvent;
use App\Models\MatchSnapshot;
use App\Models\SemanticEmbedding;
use App\Models\SemanticHealthEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Internal matching analytics built on the outcome event log and match
 * snapshots.
 *
 * All reports are aggregate-only (no candidate/job PII is exposed) and every
 * conversion metric is suppressed until enough events exist to be meaningful.
 * This service is the evidence base for human-controlled calibration; it never
 * changes matching weights itself.
 */
class MatchAnalyticsService
{
    public const BANDS = [
        '0-19' => [0, 19],
        '20-39' => [20, 39],
        '40-59' => [40, 59],
        '60-69' => [60, 69],
        '70-79' => [70, 79],
        '80-89' => [80, 89],
        '90-100' => [90, 100],
    ];

    public function sampleSatisfied(): bool
    {
        return $this->eventCount() >= (int) config('matching.metrics.min_sample', 20);
    }

    public function eventCount(?CarbonInterface $from = null): int
    {
        return MatchOutcomeEvent::query()
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->count();
    }

    public function eventCountByType(?CarbonInterface $from = null): Collection
    {
        return MatchOutcomeEvent::query()
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->select('event_type', DB::raw('count(*) as total'))
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->pluck('total', 'event_type');
    }

    /**
     * Candidate-side funnel assembled from the outcome log plus live
     * application statuses, so older applications pre-outcome-log still count.
     */
    public function funnel(?CarbonInterface $from = null): array
    {
        $base = $this->eventCountByType($from);

        $recommended = (int) ($base['job_recommended'] ?? 0);
        $viewed = (int) ($base['job_viewed'] ?? 0);
        $applied = (int) ($base['job_applied'] ?? 0);
        $shortlisted = (int) ($base['candidate_shortlisted'] ?? 0);
        $interview = (int) ($base['interview_scheduled'] ?? 0);
        $hired = (int) ($base['hire_recorded'] ?? 0);
        $rejected = (int) ($base['application_rejected'] ?? 0);

        return [
            'recommended' => $recommended,
            'viewed' => $viewed,
            'applied' => $applied,
            'shortlisted' => $shortlisted,
            'interview' => $interview,
            'hired' => $hired,
            'rejected' => $rejected,
            'view_rate' => $recommended > 0 ? round($viewed / $recommended * 100, 1) : null,
            'apply_rate' => $viewed > 0 ? round($applied / $viewed * 100, 1) : null,
            'shortlist_rate' => $applied > 0 ? round($shortlisted / $applied * 100, 1) : null,
            'interview_rate' => $applied > 0 ? round($interview / $applied * 100, 1) : null,
            'hire_rate' => $interview > 0 ? round($hired / $interview * 100, 1) : null,
        ];
    }

    /**
     * Conversion-as-a-function-of-match-band, derived from snapshots. A
     * snapshot is the exposure record; outcome events link back to it, so
     * every metric is grounded in the exact score the candidate saw.
     */
    public function bandsReport(): Collection
    {
        $exposures = MatchSnapshot::query()
            ->select('candidate_id', 'job_id', 'profile_match_score')
            ->get();

        $exposedKeys = $exposures->map(fn ($s) => "{$s->candidate_id}:{$s->job_id}")->unique();

        $viewedKeys = $this->snapshottedKeys([MatchOutcomeEventType::JobViewed->value]);
        $appliedKeys = $this->snapshottedKeys([MatchOutcomeEventType::JobApplied->value]);
        $interviewKeys = $this->snapshottedKeys([MatchOutcomeEventType::InterviewScheduled->value]);
        $hiredKeys = $this->snapshottedKeys([MatchOutcomeEventType::HireRecorded->value]);

        $rows = [];

        foreach (self::BANDS as $band => [$min, $max]) {
            $inBand = $exposures
                ->filter(fn ($s) => $s->profile_match_score >= $min && $s->profile_match_score <= $max)
                ->map(fn ($s) => "{$s->candidate_id}:{$s->job_id}")
                ->unique();

            $exposed = $inBand->count();
            $viewed = count(array_intersect($inBand->all(), $viewedKeys->all()));
            $applied = count(array_intersect($inBand->all(), $appliedKeys->all()));
            $interview = count(array_intersect($inBand->all(), $interviewKeys->all()));
            $hired = count(array_intersect($inBand->all(), $hiredKeys->all()));

            $rows[$band] = [
                'band' => $band,
                'exposed' => $exposed,
                'viewed' => $viewed,
                'applied' => $applied,
                'interview' => $interview,
                'hired' => $hired,
                'ctr' => $exposed > 0 ? round($viewed / $exposed * 100, 1) : null,
                'apply_rate' => $exposed > 0 ? round($applied / $exposed * 100, 1) : null,
                'interview_rate' => $applied > 0 ? round($interview / $applied * 100, 1) : null,
                'hire_rate' => $applied > 0 ? round($hired / $applied * 100, 1) : null,
            ];
        }

        return collect($rows)->map(fn (array $row) => collect($row));
    }

    /**
     * Precision@K / Recall@K over recommendation exposure. For every candidate
     * with snapshots, rank all exposed jobs by snapshotted recommendation
     * score and measure how many of the top K produced the chosen outcome
     * (applied or viewed).
     */
    public function precisionRecallK(int $k, string $criterion = 'applied'): array
    {
        $snapshots = MatchSnapshot::query()
            ->where('source', MatchSnapshotService::SOURCE_RECOMMENDED)
            ->get()
            ->groupBy('candidate_id');

        $outcomeKeys = $this->snapshottedKeys([
            $criterion === 'viewed'
                ? MatchOutcomeEventType::JobViewed->value
                : MatchOutcomeEventType::JobApplied->value,
        ]);

        $precisions = [];
        $recalls = [];

        foreach ($snapshots as $snapshots) {
            $relevant = $snapshots
                ->filter(fn ($s) => $outcomeKeys->contains("{$s->candidate_id}:{$s->job_id}"))
                ->pluck('job_id')
                ->unique()
                ->values();

            if ($relevant->isEmpty()) {
                continue;
            }

            $topK = $snapshots
                ->sortByDesc('recommendation_score')
                ->take($k)
                ->pluck('job_id')
                ->unique()
                ->values();

            $hits = count(array_intersect($topK->all(), $relevant->all()));

            $precisions[] = $hits / max(1, $k);
            $recalls[] = $hits / max(1, $relevant->count());
        }

        return [
            'k' => $k,
            'criterion' => $criterion,
            'candidates' => count($precisions),
            'precision_at_k' => $precisions === [] ? null : round(array_sum($precisions) / count($precisions), 4),
            'recall_at_k' => $recalls === [] ? null : round(array_sum($recalls) / count($recalls), 4),
        ];
    }

    public function versionUsage(): Collection
    {
        $snapshotUsage = MatchSnapshot::query()
            ->select('algorithm_version', DB::raw('count(*) as total'))
            ->groupBy('algorithm_version')
            ->orderBy('algorithm_version')
            ->pluck('total', 'algorithm_version');

        return $snapshotUsage->map(
            fn (int $total, int $version) => [
                'version' => $version,
                'label' => config("matching.experiments.versions.{$version}", "v{$version}"),
                'snapshots' => $total,
            ]
        )->values();
    }

    public function feedbackReport(): array
    {
        $candidate = CandidateRecommendationFeedback::query()
            ->select('feedback_type', DB::raw('count(*) as total'))
            ->groupBy('feedback_type')
            ->orderByDesc('total')
            ->pluck('total', 'feedback_type')
            ->all();

        $employer = EmployerRecommendationFeedback::query()
            ->select('feedback_type', DB::raw('count(*) as total'))
            ->groupBy('feedback_type')
            ->orderByDesc('total')
            ->pluck('total', 'feedback_type')
            ->all();

        return [
            'candidate' => $candidate,
            'employer' => $employer,
            'negative_penalties' => CandidateRecommendationFeedback::query()
                ->where('is_relevant', false)
                ->count(),
        ];
    }

    /**
     * Distinct candidate:job pairs that produced one of the given event types
     * while a snapshot existed, using the snapshot_id linkage.
     */
    protected function snapshottedKeys(array $eventTypes): Collection
    {
        return MatchOutcomeEvent::query()
            ->whereIn('event_type', $eventTypes)
            ->whereNotNull('snapshot_id')
            ->whereNotNull('candidate_id')
            ->whereNotNull('job_id')
            ->get()
            ->map(fn (MatchOutcomeEvent $event) => "{$event->candidate_id}:{$event->job_id}")
            ->unique()
            ->values();
    }

    /**
     * Semantic-layer operational report: whether the layer is active, how many
     * vectors exist per entity/provider, and the recent health of generation.
     * Aggregate-only; exposes no candidate or job identifiers.
     */
    public function semanticReport(): array
    {
        $active = config('matching.semantic.enabled', false);

        $embeddings = SemanticEmbedding::query()
            ->select('entity_type', 'provider', 'model', 'embedding_version', DB::raw('count(*) as total'))
            ->groupBy('entity_type', 'provider', 'model', 'embedding_version')
            ->orderBy('entity_type')
            ->get();

        $health = SemanticHealthEvent::query()
            ->select('action', 'status', DB::raw('count(*) as total'))
            ->groupBy('action', 'status')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn (SemanticHealthEvent $event) => [
                $event->action.':'.$event->status => (int) $event->total,
            ])
            ->all();

        return [
            'enabled' => $active,
            'provider' => config('matching.semantic.provider', 'lexical'),
            'maximum_influence' => (int) config('matching.semantic.maximum_influence', 15),
            'recent_failures' => SemanticHealthEvent::query()
                ->where('status', 'failure')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'vectors' => count($embeddings) > 0 ? $embeddings->map(fn ($row) => [
                'entity_type' => $row->entity_type,
                'provider' => $row->provider,
                'model' => $row->model,
                'embedding_version' => $row->embedding_version,
                'total' => (int) $row->total,
            ])->all() : [],
            'health' => $health,
        ];
    }
}
