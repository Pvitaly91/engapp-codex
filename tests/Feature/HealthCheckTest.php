<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_is_public_and_reports_a_healthy_status(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_health_endpoint_returns_500_without_leaking_connection_details(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('select 1')
            ->andThrow(new \RuntimeException('database password=secret'));

        $response = $this->getJson('/health');

        $response->assertStatus(500)
            ->assertExactJson(['status' => 'error']);

        $this->assertStringNotContainsString('secret', $response->getContent());
        $this->assertStringNotContainsString('database password', $response->getContent());
    }
}
