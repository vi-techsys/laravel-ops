<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $mixed)
    {
        if (!Auth::check()) {
            return $mixed($request);
        }

        $user = Auth::user();

        // If 2FA is enabled and user hasn't confirmed it in this session
        if ($user->hasTwoFactorEnabled() && !$request->session()->has('2fa_verified')) {
            // Store the intended URL
            $request->session()->put('url.intended', $request->url());
            
            // Redirect to 2FA challenge
            return redirect()->route('two-factor.challenge');
        }

        return $mixed($request);
    }
}
