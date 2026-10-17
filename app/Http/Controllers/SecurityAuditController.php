<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class SecurityAuditController extends Controller
{
    /**
     * Display the security audit log.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $logs = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('settings.security-audit', [
            'logs' => $logs,
        ]);
    }
}
