<?php

namespace App\Http\Controllers;

use App\Models\ManagedApplication;
use App\Models\OperationResult;
use App\Services\OperationExecutor;
use App\Services\OperationRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    /**
     * Display available operations and operation history.
     */
    public function index(ManagedApplication $application)
    {
        $this->authorize('view', $application);

        $operations = OperationRegistry::all($application);
        $history = OperationExecutor::getHistory($application, 50);
        $statistics = OperationExecutor::getStatistics($application);

        return view('applications.operations.index', compact('application', 'operations', 'history', 'statistics'));
    }

    /**
     * Show the form to execute an operation.
     */
    public function create(ManagedApplication $application, string $operation = null)
    {
        $this->authorize('connect', $application);

        $operationDetails = $operation ? OperationRegistry::get($operation, $application) : null;

        if ($operation && !$operationDetails) {
            return redirect()
                ->route('applications.operations.index', $application)
                ->with('error', 'Operation not found in registry.');
        }

        $availableOperations = OperationRegistry::all($application);

        return view('applications.operations.create', compact('application', 'operationDetails', 'availableOperations'));
    }

    /**
     * Execute an operation.
     */
    public function store(Request $request, ManagedApplication $application)
    {
        $this->authorize('connect', $application);

        $request->validate([
            'operation' => 'required|string',
            'confirmed' => 'nullable',
        ]);

        $operation = $request->input('operation');
        $parameters = $request->input('parameters', []);

        // Check if operation exists
        if (!OperationRegistry::exists($operation, $application)) {
            return back()
                ->with('error', 'Operation not registered: ' . $operation);
        }

        // Check if confirmation is required
        $requiresConfirmation = OperationRegistry::requiresConfirmation($operation, $application);
        if ($requiresConfirmation && !$request->has('confirmed')) {
            return back()
                ->with('error', 'This operation requires confirmation.');
        }

        try {
            $executor = new OperationExecutor($application);
            $operationResult = $executor->execute(
                $operation,
                $parameters,
                'manual',
                $requiresConfirmation
            );

            if ($operationResult->isSucceeded()) {
                return redirect()
                    ->route('applications.operations.show', [$application, $operationResult])
                    ->with('success', 'Operation executed successfully!');
            }

            return redirect()
                ->route('applications.operations.show', [$application, $operationResult])
                ->with('error', 'Operation failed: ' . $operationResult->error_message);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified operation result.
     */
    public function show(ManagedApplication $application, OperationResult $operation)
    {
        $this->authorize('view', $application);

        // Ensure operation belongs to this application
        if ($operation->managed_application_id !== $application->id) {
            abort(404);
        }

        $operation->load('user', 'logs');

        return view('applications.operations.show', compact('application', 'operation'));
    }

    /**
     * Confirm an operation (for operations requiring confirmation).
     */
    public function confirm(Request $request, ManagedApplication $application, OperationResult $operation)
    {
        $this->authorize('connect', $application);

        if ($operation->managed_application_id !== $application->id) {
            abort(404);
        }

        if (!$operation->requires_confirmation || $operation->confirmed) {
            return back()->with('error', 'Operation does not require confirmation or is already confirmed.');
        }

        $operation->confirm();

        return back()->with('success', 'Operation confirmed. You can now execute it.');
    }
}
