<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\AccountLockoutService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorChallengeController extends Controller
{
    protected $twoFactorService;
    protected $lockoutService;
    protected $auditService;

    public function __construct(TwoFactorService $twoFactorService, AccountLockoutService $lockoutService, AuditService $auditService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->lockoutService = $lockoutService;
        $this->auditService = $auditService;
    }

    /**
     * Display the 2FA challenge view.
     */
    public function create(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code.
     */
    public function store(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        // Apply rate limiting
        $throttleKey = $userId.'|'.$request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('two-factor:'.$throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('two-factor:'.$throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'code' => "Too many verification attempts. Please try again in {$minutes} minutes.",
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit('two-factor:'.$throttleKey);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::findOrFail($userId);

        // Verify the code
        if (!$this->twoFactorService->verifyCode($user, $request->code)) {
            // Log failed verification
            AuditLog::logSecurityEvent('two_factor.failed', $user->id, ['email' => $user->email, 'reason' => 'invalid_code']);
            
            return back()->withErrors([
                'code' => 'The verification code is invalid or has expired.',
            ]);
        }

        // Log successful verification
        $this->auditService->logTwoFactorEvent('verified', $user);

        // Clear 2FA session data
        $request->session()->forget('2fa_user_id');

        // Clear login attempts
        $this->lockoutService->clearAttempts($user);

        // Log the user in
        Auth::login($user, $request->session()->get('remember', false));
        $request->session()->regenerate();

        // Mark 2FA as verified for this session
        $request->session()->put('2fa_verified', true);

        // Clear rate limiter on successful verification
        \Illuminate\Support\Facades\RateLimiter::clear('two-factor:'.$throttleKey);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Resend the 2FA code.
     */
    public function resend(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $this->twoFactorService->generateAndSendCode($user);

        // Log the resend event
        $this->auditService->logTwoFactorEvent('code_resent', $user);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
