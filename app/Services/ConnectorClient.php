<?php

namespace App\Services;

use App\Models\ManagedApplication;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConnectorClient
{
    /**
     * The managed application instance.
     */
    protected ManagedApplication $application;

    /**
     * Create a new connector client instance.
     */
    public function __construct(ManagedApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Send a health check request to the connector.
     */
    public function healthCheck(): array
    {
        return $this->sendSignedRequest('GET', '/api/connector/v1/health');
    }

    /**
     * Get capabilities from the connector.
     */
    public function getCapabilities(): array
    {
        return $this->sendSignedRequest('GET', '/api/connector/v1/capabilities');
    }

    /**
     * Execute an operation on the connector.
     */
    public function executeOperation(string $operation, array $parameters = []): array
    {
        $startTime = microtime(true);

        $result = $this->sendSignedRequest('POST', '/api/connector/v1/operations/execute', [
            'operation' => $operation,
            'parameters' => $parameters,
        ]);

        $durationMs = round((microtime(true) - $startTime) * 1000);

        if ($result['success']) {
            $result['duration_ms'] = $durationMs;
        }

        return $result;
    }

    /**
     * List available seeders from the connector.
     */
    public function listSeeders(): array
    {
        return $this->sendSignedRequest('GET', '/api/connector/v1/seeders');
    }

    /**
     * Send a signed request to the connector.
     */
    protected function sendSignedRequest(string $method, string $path, array $data = []): array
    {
        $url = rtrim($this->application->base_url, '/') . $path;
        $timestamp = now()->toIso8601String();
        $nonce = bin2hex(random_bytes(16));

        // Build string to sign
        $bodyHash = !empty($data) ? hash('sha256', json_encode($data)) : '';
        $stringToSign = sprintf('%s|%s|%s|%s|%s',
            strtoupper($method),
            $path,
            $timestamp,
            $nonce,
            $bodyHash
        );

        // Calculate signature
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
                ->withOptions([
                    'verify' => $this->application->verify_ssl,
                ])
                ->send($method, $url, !empty($data) ? ['json' => $data] : []);

            if ($response->successful()) {
                // Update last contact timestamp
                $this->application->update([
                    'last_contact_at' => now(),
                    'last_health_check_at' => $method === 'GET' && str_contains($path, 'health') ? now() : $this->application->last_health_check_at,
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status_code' => $response->status(),
                ];
            }

            // Handle error responses (4xx, 5xx)
            $errorBody = $response->json();
            return [
                'success' => false,
                'error' => $errorBody['error_message'] ?? $errorBody['error'] ?? 'Request failed',
                'error_type' => $errorBody['error_type'] ?? 'unknown',
                'status_code' => $response->status(),
                'body' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Connector request failed', [
                'application_id' => $this->application->id,
                'url' => $url,
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
     * Test the connection to the connector.
     */
    public function testConnection(): array
    {
        $health = $this->healthCheck();

        if ($health['success']) {
            $this->application->markAsConnected($health['data']['connector']['version'] ?? null);

            // Fetch and store supported operations
            $this->fetchAndStoreOperations();

            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $health['data'],
            ];
        }

        return [
            'success' => false,
            'message' => 'Connection failed',
            'error' => $health['error'] ?? 'Unknown error',
        ];
    }

    /**
     * Fetch supported operations from connector and store in application.
     */
    protected function fetchAndStoreOperations(): void
    {
        try {
            $capabilities = $this->getCapabilities();

            if ($capabilities['success'] && isset($capabilities['data']['supported_operations'])) {
                $this->application->update([
                    'supported_operations' => $capabilities['data']['supported_operations'],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch operations from connector', [
                'application_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
