<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    /**
     * Log a login attempt.
     */
    public function logLoginAttempt(User $user, bool $success, array $metadata = []): void
    {
        AuditLog::logSecurityEvent(
            $success ? 'login.success' : 'login.failed',
            $user->id,
            array_merge($metadata, ['email' => $user->email])
        );
    }

    /**
     * Log a logout event.
     */
    public function logLogout(User $user): void
    {
        AuditLog::logSecurityEvent('logout', $user->id, ['email' => $user->email]);
    }

    /**
     * Log 2FA events.
     */
    public function logTwoFactorEvent(string $event, User $user, array $metadata = []): void
    {
        AuditLog::logSecurityEvent(
            "two_factor.{$event}",
            $user->id,
            array_merge($metadata, ['email' => $user->email])
        );
    }

    /**
     * Log account lockout.
     */
    public function logAccountLockout(User $user, int $duration): void
    {
        AuditLog::logSecurityEvent(
            'account.locked',
            $user->id,
            [
                'email' => $user->email,
                'duration_minutes' => $duration,
                'failed_attempts' => $user->login_attempts,
            ]
        );
    }

    /**
     * Log password change.
     */
    public function logPasswordChange(User $user): void
    {
        AuditLog::logSecurityEvent('password.changed', $user->id, ['email' => $user->email]);
    }

    /**
     * Log security settings change.
     */
    public function logSecuritySettingsChange(User $user, string $setting, bool $enabled): void
    {
        AuditLog::logSecurityEvent(
            'security_settings.changed',
            $user->id,
            [
                'email' => $user->email,
                'setting' => $setting,
                'enabled' => $enabled,
            ]
        );
    }

    /**
     * Get recent security events for a user.
     */
    public function getUserSecurityEvents(User $user, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
