<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollmentApiController extends Controller
{
    /**
     * Validate an enrollment token (called by connector).
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'app_url' => 'required|url',
            'app_name' => 'required|string',
            'laravel_version' => 'required|string',
            'php_version' => 'required|string',
        ]);

        $token = EnrollmentToken::where('token', $request->token)
            ->with('managedApplication')
            ->first();

        if (!$token) {
            Log::warning('Enrollment: Invalid token', [
                'token' => substr($request->token, 0, 10) . '...',
                'app_url' => $request->app_url,
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Invalid enrollment token',
            ], 404);
        }

        if (!$token->isValid()) {
            Log::warning('Enrollment: Token expired or used', [
                'token_id' => $token->id,
                'expired' => $token->isExpired(),
                'used' => $token->isUsed(),
            ]);

            return response()->json([
                'valid' => false,
                'message' => $token->isExpired() 
                    ? 'Enrollment token has expired' 
                    : 'Enrollment token has already been used',
            ], 401);
        }

        // Mark token as used
        $token->markAsUsed($request->ip());

        $application = $token->managedApplication;

        Log::info('Enrollment: Token validated successfully', [
            'application_id' => $application->id,
            'app_url' => $request->app_url,
            'app_name' => $request->app_name,
        ]);

        return response()->json([
            'valid' => true,
            'api_key' => $application->api_key,
            'api_secret' => $application->api_secret,
            'application_id' => $application->id,
            'application_name' => $application->name,
        ]);
    }
}
