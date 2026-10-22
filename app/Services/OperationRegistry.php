<?php

namespace App\Services;

use App\Models\ManagedApplication;

/**
 * Registry of allowed operations that can be executed remotely.
 * This is the single source of truth for what operations are permitted.
 */
class OperationRegistry
{
    /**
     * Get all registered operations.
     * If an application is provided, use its supported operations.
     */
    public static function all(?ManagedApplication $application = null): array
    {
        // If application has supported operations, use those
        if ($application && $application->supported_operations) {
            return $application->supported_operations;
        }

        // Fallback to default operations (for backward compatibility)
        return [
            // Cache Operations
            'cache:clear' => [
                'name' => 'Clear Application Cache',
                'description' => 'Flush the application cache',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            'config:clear' => [
                'name' => 'Clear Config Cache',
                'description' => 'Remove the configuration cache file',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            'route:clear' => [
                'name' => 'Clear Route Cache',
                'description' => 'Remove the route cache file',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            'view:clear' => [
                'name' => 'Clear View Cache',
                'description' => 'Clear all compiled view files',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            'event:clear' => [
                'name' => 'Clear Event Cache',
                'description' => 'Clear all cached events and listeners',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            'optimize:clear' => [
                'name' => 'Clear All Caches',
                'description' => 'Remove all cached files (cache, config, route, view, event)',
                'category' => 'cache',
                'requires_confirmation' => false,
                'risk_level' => 'low',
                'parameters' => [],
            ],

            // Database Operations
            'migrate:status' => [
                'name' => 'Migration Status',
                'description' => 'Show the status of each migration',
                'category' => 'database',
                'requires_confirmation' => false,
                'risk_level' => 'none',
                'parameters' => [],
            ],
        ];
    }

    /**
     * Check if an operation is registered.
     */
    public static function exists(string $operation, ?ManagedApplication $application = null): bool
    {
        $operations = static::all($application);
        return array_key_exists($operation, $operations);
    }

    /**
     * Get operation details.
     */
    public static function get(string $operation, ?ManagedApplication $application = null): ?array
    {
        $operations = static::all($application);
        return $operations[$operation] ?? null;
    }

    /**
     * Get operation parameters.
     */
    public static function getParameters(string $operation, ?ManagedApplication $application = null): array
    {
        $op = static::get($operation, $application);
        return $op['parameters'] ?? [];
    }

    /**
     * Check if operation has required parameters.
     */
    public static function hasParameters(string $operation, ?ManagedApplication $application = null): bool
    {
        $parameters = static::getParameters($operation, $application);
        return !empty($parameters);
    }

    /**
     * Check if operation requires confirmation.
     */
    public static function requiresConfirmation(string $operation, ?ManagedApplication $application = null): bool
    {
        $op = static::get($operation, $application);
        return $op ? ($op['requires_confirmation'] ?? false) : false;
    }

    /**
     * Get operations by category.
     */
    public static function getByCategory(string $category, ?ManagedApplication $application = null): array
    {
        $operations = static::all($application);
        return array_filter($operations, fn($op) => ($op['category'] ?? 'other') === $category);
    }

    /**
     * Get all categories.
     */
    public static function getCategories(?ManagedApplication $application = null): array
    {
        $operations = static::all($application);
        return array_unique(array_column($operations, 'category'));
    }

    /**
     * Validate operation parameters.
     */
    public static function validateParameters(string $operation, array $parameters, ?ManagedApplication $application = null): array
    {
        $op = static::get($operation, $application);

        if (!$op) {
            return ['valid' => false, 'errors' => ['Operation not found']];
        }

        $errors = [];
        $allowedParams = $op['parameters'] ?? [];
        
        // Extract parameter names from the parameters array
        // Parameters can be either [['name' => 'param1', ...], ...] or ['param1', 'param2', ...]
        $allowedParamNames = [];
        foreach ($allowedParams as $param) {
            if (is_array($param) && isset($param['name'])) {
                $allowedParamNames[] = $param['name'];
            } elseif (is_string($param)) {
                $allowedParamNames[] = $param;
            }
        }

        // Check for unexpected parameters
        foreach (array_keys($parameters) as $key) {
            if (!in_array($key, $allowedParamNames)) {
                $errors[] = "Unexpected parameter: {$key}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
