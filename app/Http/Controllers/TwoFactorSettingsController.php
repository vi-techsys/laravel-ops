<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TwoFactorSettingsController extends Controller
{
    protected $twoFactorService;
    protected $auditService;

    public function __construct(TwoFactorService $twoFactorService, AuditService $auditService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->auditService = $auditService;
    }

    /**
     * Display 2FA settings page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        return view('settings.two-factor', [
            'user' => $user,
            'enabled' => $user->hasTwoFactorEnabled(),
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        
        // Generate and send a confirmation code
        $this->twoFactorService->generateAndSendCode($user);
        
        // Enable 2FA (but require confirmation)
        $user->update([
            'two_factor_enabled' => true,
        ]);

        // Log the event
        $this->auditService->logSecuritySettingsChange($user, 'two_factor', true);
        $this->auditService->logTwoFactorEvent('enabled', $user);

        return redirect()->route('two-factor.settings')
            ->with('status', 'Two-factor authentication enabled! A confirmation code has been sent to your email.');
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $user = $request->user();

        // Verify password before disabling
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        $this->twoFactorService->disable($user);

        // Log the event
        $this->auditService->logSecuritySettingsChange($user, 'two_factor', false);
        $this->auditService->logTwoFactorEvent('disabled', $user);

        return redirect()->route('two-factor.settings')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}
