<?php

namespace App\Services;

use App\Models\ManagedApplication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogClient
{
    protected ManagedApplication $application;

    public function __construct(ManagedApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get recent log entries from the connector.
     */
    public function getLogs(int $limit = 100, string $level = null, string $search = null, int $page = 1): array
    {
        $url = rtrim($this->application->base_url, '/') . '/api/connector/v1/logs';
        
        $params = [
            'limit' => $limit,
            'page' => $page,
        ];

        if ($level) {
            $params['level'] = $level;
        }

        if ($search) {
            $params['search'] = $search;
        }

        try {
            $response = Http::timeout($this->application->timeout_seconds)
                ->withHeaders($this->getAuthHeaders())
                ->withOptions(['verify' => $this->application->verify_ssl])
                ->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Request failed',
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Log client request failed', [
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

    /**
     * Get log statistics from the connector.
     */
    public function getLogStatistics(): array
    {
        $url = rtrim($this->application->base_url, '/') . '/api/connector/v1/logs/statistics';

        try {
            $response = Http::timeout($this->application->timeout_seconds)
                ->withHeaders($this->getAuthHeaders())
                ->withOptions(['verify' => $this->application->verify_ssl])
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Request failed',
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Log statistics request failed', [
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

    /**
     * Get authentication headers for connector requests.
     */
    protected function getAuthHeaders(string $method = 'GET', string $path = '/api/connector/v1/logs'): array
    {
        $timestamp = now()->toIso8601String();
        $nonce = bin2hex(random_bytes(16));

        $stringToSign = sprintf('%s|%s|%s|%s|%s',
            $method,
            $path,
            $timestamp,
            $nonce,
            ''
        );

        $signature = hash_hmac('sha256', $stringToSign, $this->application->api_secret);

        return [
            'X-Connector-Key' => $this->application->api_key,
            'X-Connector-Timestamp' => $timestamp,
            'X-Connector-Nonce' => $nonce,
            'X-Connector-Signature' => $signature,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Clear log files.
     */
    public function clearLogs(): array
    {
        $url = rtrim($this->application->base_url, '/') . '/api/connector/v1/logs/clear';

        try {
            $response = Http::timeout($this->application->timeout_seconds)
                ->withHeaders($this->getAuthHeaders('POST', '/api/connector/v1/logs/clear'))
                ->withOptions(['verify' => $this->application->verify_ssl])
                ->post($url);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'error' => 'Request failed',
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Log clear request failed', [
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
