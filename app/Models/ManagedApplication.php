<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ManagedApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'base_url',
        'environment',
        'timezone',
        'description',
        'connection_status',
        'connector_version',
        'last_contact_at',
        'last_health_check_at',
        'api_key',
        'api_secret',
        'webhook_secret',
        'verify_ssl',
        'timeout_seconds',
        'max_retries',
        'capabilities',
        'supported_operations',
        'metadata',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'verify_ssl' => 'boolean',
        'timeout_seconds' => 'integer',
        'max_retries' => 'integer',
        'capabilities' => 'array',
        'supported_operations' => 'array',
        'metadata' => 'array',
        'last_contact_at' => 'datetime',
        'last_health_check_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'webhook_secret',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the managed application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the health checks for the managed application.
     */
    public function healthChecks()
    {
        return $this->hasMany(HealthCheck::class);
    }

    /**
     * Get the latest health check.
     */
    public function latestHealthCheck()
    {
        return $this->hasOne(HealthCheck::class)->latestOfMany('checked_at');
    }

    /**
     * Check if the application is connected.
     */
    public function isConnected(): bool
    {
        return $this->connection_status === 'connected';
    }

    /**
     * Check if the application is pending.
     */
    public function isPending(): bool
    {
        return $this->connection_status === 'pending';
    }

    /**
     * Check if the application is disconnected.
     */
    public function isDisconnected(): bool
    {
        return $this->connection_status === 'disconnected';
    }

    /**
     * Check if the application is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->connection_status === 'revoked';
    }

    /**
     * Mark the application as connected.
     */
    public function markAsConnected(string $connectorVersion = null): void
    {
        $this->update([
            'connection_status' => 'connected',
            'connector_version' => $connectorVersion ?? $this->connector_version,
            'last_contact_at' => now(),
        ]);
    }

    /**
     * Mark the application as disconnected.
     */
    public function markAsDisconnected(): void
    {
        $this->update([
            'connection_status' => 'disconnected',
        ]);
    }

    /**
     * Mark the application as revoked.
     */
    public function markAsRevoked(): void
    {
        $this->update([
            'connection_status' => 'revoked',
        ]);
    }

    /**
     * Update the last health check timestamp.
     */
    public function updateHealthCheck(): void
    {
        $this->update([
            'last_health_check_at' => now(),
        ]);
    }

    /**
     * Generate a new API key.
     */
    public static function generateApiKey(): string
    {
        return 'lops_' . bin2hex(random_bytes(32));
    }

    /**
     * Generate a new API secret.
     */
    public static function generateApiSecret(): string
    {
        return bin2hex(random_bytes(64));
    }

    /**
     * Generate a new webhook secret.
     */
    public static function generateWebhookSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get the displayable environment.
     */
    public function getEnvironmentBadgeClassAttribute(): string
    {
        return match($this->environment) {
            'production' => 'danger',
            'staging' => 'warning',
            'local', 'development' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->connection_status) {
            'connected' => 'success',
            'pending' => 'warning',
            'disconnected' => 'secondary',
            'revoked' => 'danger',
            default => 'secondary',
        };
    }
}
