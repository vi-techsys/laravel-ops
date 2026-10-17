<?php

namespace App\Http\Controllers;

use App\Models\ManagedApplication;
use App\Services\QueueClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueMonitorController extends Controller
{
    /**
     * Display queue monitoring dashboard.
     */
    public function index(ManagedApplication $application)
    {
        $this->authorize('view', $application);

        $queueClient = new QueueClient($application);
        $queueData = $queueClient->inspect();
        $failedJobs = $queueClient->getFailedJobs(50, 0);

        return view('applications.queue.index', compact(
            'application',
            'queueData',
            'failedJobs'
        ));
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll(ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $queueClient = new QueueClient($application);
        $result = $queueClient->retryAll();

        if ($result['success'] ?? false) {
            return redirect()
                ->route('applications.queue.index', $application)
                ->with('success', $result['message'] ?? 'All failed jobs retried successfully!');
        }

        return redirect()
            ->route('applications.queue.index', $application)
            ->with('error', $result['error'] ?? 'Failed to retry jobs');
    }

    /**
     * Purge queue jobs.
     */
    public function purge(Request $request, ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $queue = $request->input('queue', 'default');

        $queueClient = new QueueClient($application);
        $result = $queueClient->purge($queue);

        if ($result['success'] ?? false) {
            return redirect()
                ->route('applications.queue.index', $application)
                ->with('success', $result['message'] ?? 'Queue purged successfully!');
        }

        return redirect()
            ->route('applications.queue.index', $application)
            ->with('error', $result['error'] ?? 'Failed to purge queue');
    }
}
