<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\TwoFactorService;
use App\Services\AccountLockoutService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    protected $twoFactorService;
    protected $lockoutService;
    protected $auditService;

    public function __construct(
        TwoFactorService $twoFactorService, 
        AccountLockoutService $lockoutService,
        AuditService $auditService
    ) {
        $this->twoFactorService = $twoFactorService;
        $this->lockoutService = $lockoutService;
        $this->auditService = $auditService;
    }
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // Apply rate limiting
        $throttleKey = strtolower($request->input('email')).'|'.$request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts('login:'.$throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn('login:'.$throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minutes.",
            ])->onlyInput('email');
        }

        \Illuminate\Support\Facades\RateLimiter::hit('login:'.$throttleKey);

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find the user
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            // Log failed attempt for non-existent user
            AuditLog::logSecurityEvent('login.failed', null, ['email' => $request->email, 'reason' => 'user_not_found']);
            
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check if account is locked
        if ($this->lockoutService->isLockedOut($user)) {
            $remainingTime = $this->lockoutService->getRemainingLockoutTime($user);
            
            // Log lockout attempt
            $this->auditService->logAccountLockout($user, $remainingTime);
            
            return back()->withErrors([
                'email' => "Your account is locked. Please try again in {$remainingTime} minutes.",
            ])->onlyInput('email');
        }

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // Record failed attempt
            $this->lockoutService->recordFailedAttempt($user);
            
            // Log failed login attempt
            $this->auditService->logLoginAttempt($user, false, ['attempts' => $user->login_attempts]);
            
            $remainingAttempts = $this->lockoutService->getRemainingAttempts($user);
            
            if ($remainingAttempts > 0) {
                return back()->withErrors([
                    'email' => "These credentials do not match our records. You have {$remainingAttempts} attempts remaining.",
                ])->onlyInput('email');
            } else {
                return back()->withErrors([
                    'email' => "Too many failed attempts. Your account is locked for 15 minutes.",
                ])->onlyInput('email');
            }
        }

        // Successful authentication - check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Log successful password verification
            $this->auditService->logLoginAttempt($user, true, ['requires_2fa' => true]);
            
            // Generate and send 2FA code
            $this->twoFactorService->generateAndSendCode($user);
            
            // Log 2FA code sent
            $this->auditService->logTwoFactorEvent('code_sent', $user);
            
            // Store user ID in session for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('remember', $request->boolean('remember'));
            
            // Log out the user temporarily until 2FA is verified
            Auth::logout();
            
            return redirect()->route('two-factor.challenge');
        }

        // No 2FA - clear login attempts and proceed
        $this->lockoutService->clearAttempts($user);
        
        // Log successful login
        $this->auditService->logLoginAttempt($user, true);
        
        // Clear rate limiter on successful login
        \Illuminate\Support\Facades\RateLimiter::clear('login:'.$throttleKey);
        
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
