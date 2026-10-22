<?php

/**
 * Simple test script to verify connector connection
 * Run this from Laravel Ops: php test-connection.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the application
$application = \App\Models\ManagedApplication::find('eb82004d-436c-468f-9343-8ee397c64a81');

if (!$application) {
    echo "❌ Application not found!\n";
    exit(1);
}

echo "🔍 Testing Connection to: {$application->name}\n";
echo "URL: {$application->base_url}\n";
echo "API Key: {$application->api_key}\n";
echo "\n";

// Create connector client
$client = new \App\Services\ConnectorClient($application);

echo "📡 Sending health check request...\n";
$result = $client->healthCheck();

if ($result['success']) {
    echo "✅ SUCCESS!\n";
    echo "Status: " . ($result['data']['status'] ?? 'unknown') . "\n";
    echo "Response Time: " . ($result['data']['response_time_ms'] ?? 'N/A') . "ms\n";
    echo "\n";
    print_r($result['data']);
} else {
    echo "❌ FAILED!\n";
    echo "Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    echo "Status Code: " . ($result['status_code'] ?? 'N/A') . "\n";
    echo "Response: " . ($result['body'] ?? 'No response') . "\n";
    echo "\n";
    
    echo "🔧 Troubleshooting:\n";
    echo "1. Check if app is running: http://...\n";
    echo "2. Check if CONNECTOR_API_KEY is set in http://.env\n";
    echo "3. Check if CONNECTOR_API_SECRET is set in http://.env\n";
    echo "4. Try accessing: http://app.../api/connector/v1/health\n";
}
