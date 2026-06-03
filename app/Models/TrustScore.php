<?php

namespace App\Models;

use App\Services\TrustScoreService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustScore extends Model
{
    protected $fillable = [
        'candidate_id',
        'score',
        'level',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function recalculate(): static
    {
        $score = app(TrustScoreService::class)->calculate($this->candidate);

        $this->update([
            'score' => $score,
            'level' => app(TrustScoreService::class)->getLevel($score),
        ]);

        return $this;
    }
}
