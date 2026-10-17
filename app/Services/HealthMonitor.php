<?php

namespace App\Services;

use App\Models\HealthCheck;
use App\Models\ManagedApplication;
use Illuminate\Support\Facades\Log;

class HealthMonitor
{
    /**
     * The connector client instance.
     */
    protected ConnectorClient $client;

    /**
     * The managed application instance.
     */
    protected ManagedApplication $application;

    /**
     * Create a new health monitor instance.
     */
    public function __construct(ManagedApplication $application)
    {
        $this->application = $application;
        $this->client = new ConnectorClient($application);
    }

    /**
     * Perform a health check and record the result.
     */
    public function check(string $checkedBy = 'manual'): HealthCheck
    {
        $startTime = microtime(true);

        try {
            $result = $this->client->healthCheck();
            $responseTime = round((microtime(true) - $startTime) * 1000);

            if ($result['success']) {
                return $this->recordHealthy($result['data'], $responseTime, $checkedBy);
            }

            return $this->recordFailure($result, $responseTime, $checkedBy);

        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);

            return $this->recordException($e, $checkedBy);
        }
    }

    /**
     * Record a healthy check.
     */
    protected function recordHealthy(array $data, int $responseTime, string $checkedBy): HealthCheck
    {
        $application = $this->application;

        // Determine overall status
        $status = 'healthy';
        $message = 'All systems operational';

        // Check if any component is degraded
        if (isset($data['checks'])) {
            foreach ($data['checks'] as $component => $check) {
                if (isset($check['status']) && $check['status'] !== 'ok') {
                    $status = 'degraded';
                    $message = "Some components are not operational";
                    break;
                }
            }
        }

        $healthCheck = HealthCheck::create([
            'managed_application_id' => $application->id,
            'status' => $status,
            'message' => $message,
            'checks' => $data['checks'] ?? [],
            'response_time_ms' => $responseTime,
            'connector_version' => $data['connector']['version'] ?? null,
            'checked_by' => $checkedBy,
            'checked_at' => now(),
        ]);

        // Update application status
        $application->update([
            'connection_status' => 'connected',
            'last_contact_at' => now(),
            'last_health_check_at' => now(),
            'connector_version' => $data['connector']['version'] ?? $application->connector_version,
        ]);

        return $healthCheck;
    }

    /**
     * Record a failed check.
     */
    protected function recordFailure(array $result, int $responseTime, string $checkedBy): HealthCheck
    {
        $application = $this->application;

        $healthCheck = HealthCheck::create([
            'managed_application_id' => $application->id,
            'status' => 'unreachable',
            'message' => $result['error'] ?? 'Health check failed',
            'response_time_ms' => $responseTime,
            'error_type' => 'http_error',
            'error_message' => $result['body'] ?? null,
            'checked_by' => $checkedBy,
            'checked_at' => now(),
        ]);

        // Update application status
        $application->update([
            'connection_status' => 'disconnected',
        ]);

        return $healthCheck;
    }

    /**
     * Record an exception during health check.
     */
    protected function recordException(\Exception $e, string $checkedBy): HealthCheck
    {
        $application = $this->application;

        $healthCheck = HealthCheck::create([
            'managed_application_id' => $application->id,
            'status' => 'unreachable',
            'message' => 'Connection failed',
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
            'checked_by' => $checkedBy,
            'checked_at' => now(),
        ]);

        // Update application status
        $application->update([
            'connection_status' => 'disconnected',
        ]);

        return $healthCheck;
    }

    /**
     * Get health check history for an application.
     */
    public static function getHistory(ManagedApplication $application, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return HealthCheck::where('managed_application_id', $application->id)
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get health statistics for an application.
     */
    public static function getStatistics(ManagedApplication $application, int $hours = 24): array
    {
        $checks = HealthCheck::where('managed_application_id', $application->id)
            ->where('checked_at', '>=', now()->subHours($hours))
            ->get();

        return [
            'total' => $checks->count(),
            'healthy' => $checks->where('status', 'healthy')->count(),
            'degraded' => $checks->where('status', 'degraded')->count(),
            'unreachable' => $checks->where('status', 'unreachable')->count(),
            'uptime_percentage' => $checks->count() > 0 
                ? round(($checks->where('status', 'healthy')->count() / $checks->count()) * 100, 2)
                : 0,
            'avg_response_time' => round($checks->avg('response_time_ms') ?? 0, 2),
        ];
    }
}
