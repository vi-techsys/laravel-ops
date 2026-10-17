<?php

namespace App\Services;

use App\Models\ManagedApplication;
use App\Models\OperationLog;
use App\Models\OperationResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OperationExecutor
{
    /**
     * The managed application instance.
     */
    protected ManagedApplication $application;

    /**
     * The connector client instance.
     */
    protected ConnectorClient $client;

    /**
     * Create a new operation executor instance.
     */
    public function __construct(ManagedApplication $application)
    {
        $this->application = $application;
        $this->client = new ConnectorClient($application);
    }

    /**
     * Execute an operation on the managed application.
     */
    public function execute(
        string $operation,
        array $parameters = [],
        string $initiatedBy = 'manual',
        bool $confirmed = false
    ): OperationResult {
        // Validate operation is registered
        if (!OperationRegistry::exists($operation, $this->application)) {
            throw new \InvalidArgumentException("Operation not registered: {$operation}");
        }

        // Validate parameters
        $validation = OperationRegistry::validateParameters($operation, $parameters, $this->application);
        if (!$validation['valid']) {
            throw new \InvalidArgumentException(implode(', ', $validation['errors']));
        }

        // Check if confirmation is required
        $requiresConfirmation = OperationRegistry::requiresConfirmation($operation, $this->application);
        if ($requiresConfirmation && !$confirmed) {
            throw new \RuntimeException("Operation requires confirmation: {$operation}");
        }

        // Create operation result record
        $operationResult = OperationResult::create([
            'managed_application_id' => $this->application->id,
            'user_id' => Auth::id(),
            'operation' => $operation,
            'parameters' => $parameters,
            'correlation_id' => 'op_' . Str::uuid(),
            'status' => 'pending',
            'initiated_by' => $initiatedBy,
            'requires_confirmation' => $requiresConfirmation,
            'confirmed' => $confirmed,
            'confirmed_at' => $confirmed ? now() : null,
        ]);

        // Log the operation start
        $this->log($operationResult, 'info', "Operation initiated: {$operation}", [
            'parameters' => $parameters,
            'initiated_by' => $initiatedBy,
        ]);

        // Execute the operation
        try {
            $operationResult->markAsRunning();
            $this->log($operationResult, 'info', "Operation running: {$operation}");

            $result = $this->client->executeOperation($operation, $parameters);

            if ($result['success']) {
                $durationMs = $result['duration_ms'] ?? null;
                $operationResult->markAsSucceeded($result['data'] ?? [], $durationMs);
                
                $this->log($operationResult, 'info', "Operation succeeded: {$operation}", [
                    'duration_ms' => $durationMs,
                    'result' => $result['data'] ?? null,
                ]);

                Log::info('Operation executed successfully', [
                    'operation' => $operation,
                    'application_id' => $this->application->id,
                    'duration_ms' => $durationMs,
                ]);
            } else {
                $durationMs = $result['duration_ms'] ?? null;
                $operationResult->markAsFailed(
                    $result['error_type'] ?? 'unknown',
                    $result['error'] ?? 'Operation failed',
                    $durationMs
                );

                $this->log($operationResult, 'error', "Operation failed: {$operation}", [
                    'error_type' => $result['error_type'] ?? 'unknown',
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);

                Log::warning('Operation failed', [
                    'operation' => $operation,
                    'application_id' => $this->application->id,
                    'error' => $result['error_message'] ?? 'Unknown',
                ]);
            }

        } catch (\Exception $e) {
            $operationResult->markAsFailed(
                get_class($e),
                $e->getMessage()
            );

            $this->log($operationResult, 'error', "Operation exception: {$operation}", [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            Log::error('Operation exception', [
                'operation' => $operation,
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $operationResult->fresh();
    }

    /**
     * Add a log entry to an operation.
     */
    protected function log(OperationResult $operationResult, string $level, string $message, array $context = []): void
    {
        OperationLog::create([
            'operation_result_id' => $operationResult->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'logged_at' => now(),
        ]);
    }

    /**
     * Get operation history for an application.
     */
    public static function getHistory(ManagedApplication $application, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return OperationResult::where('managed_application_id', $application->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get operation statistics for an application.
     */
    public static function getStatistics(ManagedApplication $application, int $hours = 24): array
    {
        $operations = OperationResult::where('managed_application_id', $application->id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->get();

        return [
            'total' => $operations->count(),
            'succeeded' => $operations->where('status', 'succeeded')->count(),
            'failed' => $operations->where('status', 'failed')->count(),
            'pending' => $operations->where('status', 'pending')->count(),
            'running' => $operations->where('status', 'running')->count(),
            'success_rate' => $operations->count() > 0 
                ? round(($operations->where('status', 'succeeded')->count() / $operations->count()) * 100, 2)
                : 0,
            'avg_duration' => round($operations->avg('duration_ms') ?? 0, 2),
        ];
    }
}
