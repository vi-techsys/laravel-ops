<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke()
    {
        // Simplified: just redirect to dashboard since we're not using email verification
        return redirect()->route('dashboard');
    }
}
