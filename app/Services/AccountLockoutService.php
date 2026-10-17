<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class AccountLockoutService
{
    /**
     * Maximum login attempts before lockout.
     */
    protected const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes.
     */
    protected const LOCKOUT_DURATION = 15;

    /**
     * Record a failed login attempt.
     */
    public function recordFailedAttempt(User $user): void
    {
        $attempts = $user->login_attempts + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $user->update([
                'login_attempts' => $attempts,
                'locked_until' => Carbon::now()->addMinutes(self::LOCKOUT_DURATION),
            ]);
        } else {
            $user->update([
                'login_attempts' => $attempts,
            ]);
        }
    }

    /**
     * Clear login attempts after successful login.
     */
    public function clearAttempts(User $user): void
    {
        $user->update([
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Check if user is locked out.
     */
    public function isLockedOut(User $user): bool
    {
        return $user->isLocked();
    }

    /**
     * Get remaining lockout time in minutes.
     */
    public function getRemainingLockoutTime(User $user): int
    {
        if (!$user->isLocked()) {
            return 0;
        }

        return max(0, Carbon::now()->diffInMinutes($user->locked_until, false));
    }

    /**
     * Get remaining attempts before lockout.
     */
    public function getRemainingAttempts(User $user): int
    {
        return max(0, self::MAX_ATTEMPTS - $user->login_attempts);
    }
}
