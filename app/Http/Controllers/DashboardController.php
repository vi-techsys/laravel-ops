<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function __invoke()
    {
        $user = Auth::user();
        
        // Get real application statistics
        $stats = [
            'total' => $user->managedApplications()->count(),
            'connected' => $user->managedApplications()->where('connection_status', 'connected')->count(),
            'pending' => $user->managedApplications()->where('connection_status', 'pending')->count(),
            'disconnected' => $user->managedApplications()->where('connection_status', 'disconnected')->count(),
        ];

        // Get recent health checks
        $recentHealthChecks = \App\Models\HealthCheck::whereHas('managedApplication', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('managedApplication')
            ->orderBy('checked_at', 'desc')
            ->limit(10)
            ->get();

        // Get applications needing attention (disconnected or degraded)
        $applicationsNeedingAttention = $user->managedApplications()
            ->whereIn('connection_status', ['disconnected', 'revoked'])
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentHealthChecks', 'applicationsNeedingAttention'));
    }
}
