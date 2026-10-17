<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TwoFactorService
{
    /**
     * Generate and send a 2FA code to the user.
     */
    public function generateAndSendCode(User $user): string
    {
        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code with expiration (10 minutes)
        $user->update([
            'two_factor_code' => bcrypt($code),
            'two_factor_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send the code via email
        $this->sendCodeEmail($user, $code);

        return $code;
    }

    /**
     * Verify the 2FA code.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->hasValidTwoFactorCode()) {
            return false;
        }

        // Check if the code matches
        if (!password_verify($code, $user->two_factor_code)) {
            return false;
        }

        // Clear the code and mark as confirmed
        $user->update([
            'two_factor_code' => null,
            'two_factor_code_expires_at' => null,
            'two_factor_confirmed_at' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Send the 2FA code via email.
     */
    protected function sendCodeEmail(User $user, string $code): void
    {
        Mail::send('emails.two-factor-code', ['code' => $code, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Two-Factor Authentication Code');
        });
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable(User $user): void
    {
        $user->update([
            'two_factor_enabled' => true,
        ]);
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_code_expires_at' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
