<?php

namespace App\Http\Controllers;

use App\Models\ManagedApplication;
use App\Services\LogClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogViewerController extends Controller
{
    /**
     * Display the log viewer.
     */
    public function index(ManagedApplication $application, Request $request)
    {
        $this->authorize('view', $application);

        $limit = $request->input('limit', 100);
        $level = $request->input('level');
        $search = $request->input('search');
        $page = $request->input('page', 1);

        $logClient = new LogClient($application);
        $logData = $logClient->getLogs($limit, $level, $search, $page);

        $statistics = $logClient->getLogStatistics();

        return view('applications.logs.index', compact(
            'application',
            'logData',
            'statistics',
            'limit',
            'level',
            'search',
            'page'
        ));
    }

    /**
     * Clear logs for the application.
     */
    public function clear(ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $logClient = new LogClient($application);
        $result = $logClient->clearLogs();

        if ($result['success'] ?? false) {
            return redirect()
                ->route('applications.logs.index', $application)
                ->with('success', $result['message'] ?? 'Logs cleared successfully!');
        }

        return redirect()
            ->route('applications.logs.index', $application)
            ->with('error', $result['error'] ?? 'Failed to clear logs');
    }
}
