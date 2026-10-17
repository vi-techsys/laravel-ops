<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Rate limiting for login attempts
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower($request->input('email')).'|'.$request->ip();
            
            return Limit::perMinute(5)->by($throttleKey);
        });

        // Rate limiting for 2FA attempts
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('2fa_user_id').'|'.$request->ip());
        });

        // Rate limiting for password reset attempts
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
