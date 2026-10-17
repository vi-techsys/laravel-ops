<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EnrollmentToken extends Model
{
    protected $fillable = [
        'managed_application_id',
        'token',
        'expires_at',
        'used_at',
        'used_by_ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Generate a new enrollment token for an application.
     */
    public static function generate(ManagedApplication $application, int $ttlMinutes = 60): self
    {
        // Invalidate any existing tokens for this application
        static::where('managed_application_id', $application->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return static::create([
            'managed_application_id' => $application->id,
            'token' => static::generateToken(),
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    /**
     * Generate a secure random token.
     */
    protected static function generateToken(): string
    {
        return 'enr_' . bin2hex(random_bytes(32));
    }

    /**
     * Check if the token is valid (not expired and not used).
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the token has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Mark the token as used.
     */
    public function markAsUsed(string $ip = null): void
    {
        $this->update([
            'used_at' => now(),
            'used_by_ip' => $ip,
        ]);
    }

    /**
     * Get the managed application this token belongs to.
     */
    public function managedApplication()
    {
        return $this->belongsTo(ManagedApplication::class);
    }

    /**
     * Scope to only valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
                    ->where('expires_at', '>', now());
    }
}
