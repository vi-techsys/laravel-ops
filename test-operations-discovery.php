<?php

/**
 * Test script to verify dynamic operation discovery
 * Run this from Laravel Ops: php test-operations-discovery.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the application
$application = \App\Models\ManagedApplication::find('eb82004d-436c-468f-9343-8ee397c64a81');

if (!$application) {
    echo " Application not found!\n";
    exit(1);
}

echo "🔍 Testing Dynamic Operation Discovery for: {$application->name}\n";
echo "URL: {$application->base_url}\n";
echo "\n";

// Create connector client
$client = new \App\Services\ConnectorClient($application);

echo "📡 Fetching capabilities from connector...\n";
$capabilities = $client->getCapabilities();

if ($capabilities['success']) {
    echo "✅ Capabilities fetched successfully!\n";
    echo "\n";
    
    if (isset($capabilities['data']['supported_operations'])) {
        $operations = $capabilities['data']['supported_operations'];
        echo "📋 Supported Operations (" . count($operations) . "):\n";
        echo str_repeat("-", 50) . "\n";
        
        foreach ($operations as $opName => $opDetails) {
            echo "  • {$opDetails['name']}\n";
            echo "    Category: {$opDetails['category']}\n";
            echo "    Risk: {$opDetails['risk_level']}\n";
            echo "    Description: {$opDetails['description']}\n";
            echo "\n";
        }
        
        // Store operations in database
        echo "💾 Storing operations in database...\n";
        $application->update([
            'supported_operations' => $operations,
        ]);
        echo "✅ Operations stored successfully!\n";
        echo "\n";
        
        // Verify storage
        $application->refresh();
        echo "🔍 Verifying stored operations...\n";
        if ($application->supported_operations && count($application->supported_operations) > 0) {
            echo "✅ Operations verified in database: " . count($application->supported_operations) . " operations\n";
        } else {
            echo " Operations not found in database!\n";
        }
    } else {
        echo " No supported_operations found in capabilities response\n";
        print_r($capabilities['data']);
    }
} else {
    echo "❌ Failed to fetch capabilities!\n";
    echo "Error: " . ($capabilities['error'] ?? 'Unknown error') . "\n";
}
