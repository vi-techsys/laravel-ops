<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckController extends Controller
{
    /**
     * Display the application health status.
     */
    public function __invoke()
    {
        $checks = [
            'application' => $this->checkApplication(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'ok')
            ? 'healthy'
            : 'degraded';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ]);
    }

    /**
     * Check application status.
     */
    private function checkApplication(): array
    {
        return [
            'status' => 'ok',
            'message' => 'Application is running',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    /**
     * Check database connection.
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check cache functionality.
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_test';
            $testValue = 'test_' . time();
            
            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'ok',
                    'message' => 'Cache is working',
                    'driver' => config('cache.default'),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Cache read/write mismatch',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache check failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Cache error',
            ];
        }
    }
}
