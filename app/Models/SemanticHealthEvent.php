<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only operational log for the semantic layer (embedding generations
 * and scoring fallbacks). Contains no personal data - used by the admin
 * analytics to surface provider health, latency and fallback rates.
 */
class SemanticHealthEvent extends Model
{
    protected $table = 'semantic_health_events';

    protected $fillable = [
        'provider',
        'model',
        'action',
        'status',
        'latency_ms',
        'message',
        'error',
    ];
}
