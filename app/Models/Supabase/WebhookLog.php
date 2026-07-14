<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WebhookLog extends Model
{
    protected $connection = 'supabase';

    protected $table = 'webhook_logs';

    /**
     * Maximum number of automatic retry attempts before a webhook is
     * permanently marked as "dead" and requires manual investigation.
     */
    public const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'status',
        'error',
        'processed_at',
        'attempts',
        'next_retry_at',
    ];

    protected $casts = [
        'payload'       => 'array',
        'processed_at'  => 'datetime',
        'next_retry_at' => 'datetime',
        'attempts'      => 'integer',
    ];

    /* ------------------------------------------------------------------ */
    /*  Query Scopes                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Webhook logs that have failed and are eligible for a retry attempt.
     * Eligibility: status = 'failed', attempts < MAX, next_retry_at <= now.
     */
    public function scopeRetryable(Builder $query): Builder
    {
        return $query
            ->where('status', 'failed')
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->where(function (Builder $q) {
                $q->whereNull('next_retry_at')
                  ->orWhere('next_retry_at', '<=', now());
            });
    }

    /**
     * Webhook logs that have exceeded the max retry attempts.
     */
    public function scopeDead(Builder $query): Builder
    {
        return $query->where('status', 'dead');
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Calculate exponential backoff delay: 2^attempts minutes (1, 2, 4, 8, 16 min).
     */
    public function nextRetryDelay(): int
    {
        return (int) pow(2, $this->attempts); // minutes
    }

    /**
     * Mark as failed and schedule the next retry (or mark dead).
     */
    public function markFailedWithRetry(string $error): void
    {
        $this->attempts = ($this->attempts ?? 0) + 1;
        $this->error    = $error;

        if ($this->attempts >= self::MAX_ATTEMPTS) {
            $this->status       = 'dead';
            $this->next_retry_at = null;
        } else {
            $this->status        = 'failed';
            $this->next_retry_at = now()->addMinutes($this->nextRetryDelay());
        }

        $this->save();
    }

    /**
     * Mark as successfully processed.
     */
    public function markProcessed(): void
    {
        $this->update([
            'status'        => 'processed',
            'processed_at'  => now(),
            'error'         => null,
            'next_retry_at' => null,
        ]);
    }
}
