<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagedApplicationRequest;
use App\Http\Requests\UpdateManagedApplicationRequest;
use App\Models\ManagedApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagedApplicationController extends Controller
{
    /**
     * Display a listing of the managed applications.
     */
    public function index()
    {
        $applications = Auth::user()
            ->managedApplications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Auth::user()->managedApplications()->count(),
            'connected' => Auth::user()->managedApplications()->where('connection_status', 'connected')->count(),
            'pending' => Auth::user()->managedApplications()->where('connection_status', 'pending')->count(),
            'disconnected' => Auth::user()->managedApplications()->where('connection_status', 'disconnected')->count(),
        ];

        return view('applications.index', compact('applications', 'stats'));
    }

    /**
     * Show the form for creating a new managed application.
     */
    public function create()
    {
        $this->authorize('create', ManagedApplication::class);

        return view('applications.create');
    }

    /**
     * Store a newly created managed application in storage.
     */
    public function store(StoreManagedApplicationRequest $request)
    {
        $this->authorize('create', ManagedApplication::class);

        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        // Generate credentials
        $data['api_key'] = ManagedApplication::generateApiKey();
        $data['api_secret'] = ManagedApplication::generateApiSecret();
        $data['webhook_secret'] = ManagedApplication::generateWebhookSecret();
        $data['connection_status'] = 'pending';

        $application = ManagedApplication::create($data);

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application registered successfully. Please deploy the connector to establish connection.');
    }

    /**
     * Display the specified managed application.
     */
    public function show(ManagedApplication $application)
    {
        $this->authorize('view', $application);

        $application->load('user');

        return view('applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified managed application.
     */
    public function edit(ManagedApplication $application)
    {
        $this->authorize('update', $application);

        return view('applications.edit', compact('application'));
    }

    /**
     * Update the specified managed application in storage.
     */
    public function update(UpdateManagedApplicationRequest $request, ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $application->update($request->validated());

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application updated successfully.');
    }

    /**
     * Remove the specified managed application from storage.
     */
    public function destroy(ManagedApplication $application)
    {
        $this->authorize('delete', $application);

        $application->delete();

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    /**
     * Disconnect the specified managed application.
     */
    public function disconnect(ManagedApplication $application)
    {
        $this->authorize('disconnect', $application);

        $application->markAsDisconnected();

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application disconnected successfully.');
    }

    /**
     * Revoke the specified managed application.
     */
    public function revoke(ManagedApplication $application)
    {
        $this->authorize('revoke', $application);

        $application->markAsRevoked();

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application revoked. It can no longer connect to Laravel Ops.');
    }

    /**
     * Regenerate API credentials for the specified managed application.
     */
    public function regenerateCredentials(ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $application->update([
            'api_key' => ManagedApplication::generateApiKey(),
            'api_secret' => ManagedApplication::generateApiSecret(),
            'connection_status' => 'pending',
        ]);

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Credentials regenerated. Please redeploy the connector with new credentials.');
    }

    /**
     * Test connection to the managed application.
     */
    public function testConnection(ManagedApplication $application)
    {
        $this->authorize('connect', $application);

        $client = new \App\Services\ConnectorClient($application);
        $result = $client->testConnection();

        if ($result['success']) {
            return redirect()
                ->route('applications.show', $application)
                ->with('success', 'Connection successful! Application is now connected.');
        }

        return redirect()
            ->route('applications.show', $application)
            ->with('error', 'Connection failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Generate an enrollment token for the managed application.
     */
    public function generateEnrollmentToken(ManagedApplication $application)
    {
        $this->authorize('update', $application);

        $token = \App\Models\EnrollmentToken::generate($application);

        return redirect()
            ->route('applications.show', $application)
            ->with('enrollment_token', $token->token)
            ->with('token_expires_at', $token->expires_at);
    }
}
