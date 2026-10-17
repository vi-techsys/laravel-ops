<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OperationResult extends Model
{
    protected $fillable = [
        'managed_application_id',
        'user_id',
        'operation',
        'parameters',
        'correlation_id',
        'status',
        'result',
        'error_type',
        'error_message',
        'duration_ms',
        'started_at',
        'completed_at',
        'timed_out_at',
        'initiated_by',
        'requires_confirmation',
        'confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'timed_out_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'requires_confirmation' => 'boolean',
        'confirmed' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->correlation_id)) {
                $model->correlation_id = 'op_' . Str::uuid();
            }
        });
    }

    /**
     * Get the managed application this operation belongs to.
     */
    public function managedApplication(): BelongsTo
    {
        return $this->belongsTo(ManagedApplication::class);
    }

    /**
     * Get the user who initiated the operation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the operation logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    /**
     * Check if operation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if operation is running.
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if operation succeeded.
     */
    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Check if operation failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if operation timed out.
     */
    public function isTimedOut(): bool
    {
        return $this->status === 'timed_out';
    }

    /**
     * Check if operation is complete (succeeded, failed, or timed_out).
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['succeeded', 'failed', 'timed_out', 'cancelled']);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'running' => 'primary',
            'succeeded' => 'success',
            'failed' => 'danger',
            'timed_out' => 'warning',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => '⏳',
            'running' => '▶',
            'succeeded' => '✓',
            'failed' => '✗',
            'timed_out' => '⏱',
            'cancelled' => '⊘',
            default => '?',
        };
    }

    /**
     * Mark operation as running.
     */
    public function markAsRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark operation as succeeded.
     */
    public function markAsSucceeded(array $result = [], int $durationMs = null): void
    {
        $this->update([
            'status' => 'succeeded',
            'result' => $result,
            'duration_ms' => $durationMs,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark operation as failed.
     */
    public function markAsFailed(string $errorType, string $errorMessage, int $durationMs = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark operation as timed out.
     */
    public function markAsTimedOut(): void
    {
        $this->update([
            'status' => 'timed_out',
            'timed_out_at' => now(),
        ]);
    }

    /**
     * Confirm the operation.
     */
    public function confirm(): void
    {
        $this->update([
            'confirmed' => true,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Scope to recent operations.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to operations by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
