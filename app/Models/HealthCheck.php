<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCheck extends Model
{
    protected $fillable = [
        'managed_application_id',
        'status',
        'message',
        'checks',
        'response_time_ms',
        'connector_version',
        'error_type',
        'error_message',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'checks' => 'array',
        'response_time_ms' => 'integer',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the managed application this health check belongs to.
     */
    public function managedApplication(): BelongsTo
    {
        return $this->belongsTo(ManagedApplication::class);
    }

    /**
     * Check if the health status is healthy.
     */
    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    /**
     * Check if the health status is degraded.
     */
    public function isDegraded(): bool
    {
        return $this->status === 'degraded';
    }

    /**
     * Check if the application was unreachable.
     */
    public function isUnreachable(): bool
    {
        return $this->status === 'unreachable';
    }

    /**
     * Get status badge class for Bootstrap.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'healthy' => 'success',
            'degraded' => 'warning',
            'unreachable' => 'danger',
            'unknown' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'healthy' => '✓',
            'degraded' => '⚠',
            'unreachable' => '✗',
            'unknown' => '?',
            default => '?',
        };
    }

    /**
     * Scope to only healthy checks.
     */
    public function scopeHealthy($query)
    {
        return $query->where('status', 'healthy');
    }

    /**
     * Scope to only failed checks.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['degraded', 'unreachable']);
    }

    /**
     * Scope to recent checks.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('checked_at', '>=', now()->subHours($hours));
    }
}
