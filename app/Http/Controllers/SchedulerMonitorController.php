<?php

namespace App\Http\Controllers;

use App\Models\ManagedApplication;
use App\Services\SchedulerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchedulerMonitorController extends Controller
{
    /**
     * Display scheduler monitoring dashboard.
     */
    public function index(ManagedApplication $application)
    {
        $this->authorize('view', $application);

        $schedulerClient = new SchedulerClient($application);
        $scheduleData = $schedulerClient->inspect();
        $history = $schedulerClient->getHistory(50);

        return view('applications.scheduler.index', compact(
            'application',
            'scheduleData',
            'history'
        ));
    }

    /**
     * Manually run a scheduled task.
     */
    public function runTask(Request $request, ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $command = $request->input('command');

        if (!$command) {
            return redirect()
                ->route('applications.scheduler.index', $application)
                ->with('error', 'Command parameter is required');
        }

        $schedulerClient = new SchedulerClient($application);
        $result = $schedulerClient->runTask($command);

        if ($result['success'] ?? false) {
            return redirect()
                ->route('applications.scheduler.index', $application)
                ->with('success', $result['message'] ?? 'Task executed successfully!');
        }

        return redirect()
            ->route('applications.scheduler.index', $application)
            ->with('error', $result['error'] ?? 'Failed to execute task');
    }
}
