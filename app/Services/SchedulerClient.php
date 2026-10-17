<?php

namespace App\Services;

use App\Models\ManagedApplication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SchedulerClient
{
    protected ManagedApplication $application;

    public function __construct(ManagedApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Inspect scheduled tasks.
     */
    public function inspect(): array
    {
        return $this->sendRequest('GET', '/api/connector/v1/schedule/inspect');
    }

    /**
     * Get scheduler execution history.
     */
    public function getHistory(int $limit = 50): array
    {
        return $this->sendRequest('GET', '/api/connector/v1/schedule/history', [
            'limit' => $limit,
        ]);
    }

    /**
     * Manually run a scheduled task.
     */
    public function runTask(string $command): array
    {
        return $this->sendRequest('POST', '/api/connector/v1/schedule/run-task', [
            'command' => $command,
        ]);
    }

    /**
     * Send authenticated request to connector.
     */
    protected function sendRequest(string $method, string $path, array $data = []): array
    {
        $url = rtrim($this->application->base_url, '/') . $path;
        $timestamp = now()->toIso8601String();
        $nonce = bin2hex(random_bytes(16));

        $bodyHash = !empty($data) ? hash('sha256', json_encode($data)) : '';
        $stringToSign = sprintf('%s|%s|%s|%s|%s',
            strtoupper($method),
            $path,
            $timestamp,
            $nonce,
            $bodyHash
        );

        $signature = hash_hmac('sha256', $stringToSign, $this->application->api_secret);

        try {
            $response = Http::timeout($this->application->timeout_seconds)
                ->withHeaders([
                    'X-Connector-Key' => $this->application->api_key,
                    'X-Connector-Timestamp' => $timestamp,
                    'X-Connector-Nonce' => $nonce,
                    'X-Connector-Signature' => $signature,
                    'Accept' => 'application/json',
                ])
                ->withOptions(['verify' => $this->application->verify_ssl])
                ->send($method, $url, !empty($data) ? ['json' => $data] : []);

            if ($response->successful()) {
                return $response->json();
            }

            $errorBody = $response->json();
            return [
                'success' => false,
                'error' => $errorBody['error_message'] ?? $errorBody['error'] ?? 'Request failed',
                'error_type' => $errorBody['error_type'] ?? 'unknown',
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Scheduler client request failed', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Connection failed',
                'message' => $e->getMessage(),
            ];
        }
    }
}
