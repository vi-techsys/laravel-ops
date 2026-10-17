<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Stage1SetupTest extends TestCase
{
    /**
     * Test that the dashboard page loads successfully.
     */
    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Centralized Laravel Management');
    }

    /**
     * Test that the health check endpoint returns JSON.
     */
    public function test_health_check_returns_json(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'version',
            'checks' => [
                'application',
                'database',
                'cache',
            ],
        ]);
    }

    /**
     * Test that the health check includes application status.
     */
    public function test_health_check_includes_application_status(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 'ok',
        ]);
    }

    /**
     * Test that the application name is configured.
     */
    public function test_application_name_is_configured(): void
    {
        $this->assertEquals('Laravel Ops', config('app.name'));
    }

    /**
     * Test that the database connection is configured.
     */
    public function test_database_connection_is_configured(): void
    {
        $connection = config('database.default');
        
        $this->assertEquals('mysql', $connection);
    }
}
