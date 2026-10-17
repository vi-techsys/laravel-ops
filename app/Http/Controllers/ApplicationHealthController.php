<?php

namespace App\Http\Controllers;

use App\Models\HealthCheck;
use App\Models\ManagedApplication;
use App\Services\HealthMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationHealthController extends Controller
{
    /**
     * Display health check history for an application.
     */
    public function index(ManagedApplication $application)
    {
        $this->authorize('view', $application);

        $healthChecks = $application->healthChecks()
            ->orderBy('checked_at', 'desc')
            ->paginate(20);

        $statistics = HealthMonitor::getStatistics($application);

        return view('applications.health.index', compact('application', 'healthChecks', 'statistics'));
    }

    /**
     * Perform a manual health check.
     */
    public function check(ManagedApplication $application)
    {
        $this->authorize('connect', $application);

        $monitor = new HealthMonitor($application);
        $healthCheck = $monitor->check('manual');

        if ($healthCheck->isHealthy()) {
            return redirect()
                ->route('applications.health.index', $application)
                ->with('success', 'Health check passed! Application is healthy.');
        }

        return redirect()
            ->route('applications.health.index', $application)
            ->with('error', 'Health check failed: ' . $healthCheck->message);
    }

    /**
     * Display a single health check.
     */
    public function show(ManagedApplication $application, HealthCheck $healthCheck)
    {
        $this->authorize('view', $application);

        // Ensure health check belongs to this application
        if ($healthCheck->managed_application_id !== $application->id) {
            abort(404);
        }

        return view('applications.health.show', compact('application', 'healthCheck'));
    }
}
