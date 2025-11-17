<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtisanCommandsModuleTest extends TestCase
{
    /**
     * Test that the artisan commands index page is accessible.
     */
    public function test_artisan_commands_index_page_requires_authentication(): void
    {
        $response = $this->get(route('artisan-commands.index'));

        // Should redirect to login if not authenticated
        $response->assertStatus(302);
    }

    /**
     * Test that the artisan commands config is loaded.
     */
    public function test_artisan_commands_config_is_loaded(): void
    {
        $config = config('artisan-commands');

        $this->assertNotNull($config);
        $this->assertIsArray($config);
        $this->assertArrayHasKey('commands', $config);
        $this->assertArrayHasKey('route_prefix', $config);
        $this->assertEquals('admin/artisan-commands', $config['route_prefix']);
    }

    /**
     * Test that the artisan commands config contains expected commands.
     */
    public function test_artisan_commands_config_contains_expected_commands(): void
    {
        $config = config('artisan-commands.commands');

        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('optimization', $config);
        $this->assertArrayHasKey('maintenance', $config);

        // Check cache commands
        $this->assertNotEmpty($config['cache']);
        $cacheCommands = collect($config['cache']);
        $this->assertTrue($cacheCommands->contains('key', 'cache_clear'));
        $this->assertTrue($cacheCommands->contains('key', 'config_clear'));

        // Check optimization commands
        $this->assertNotEmpty($config['optimization']);
        $optimizationCommands = collect($config['optimization']);
        $this->assertTrue($optimizationCommands->contains('key', 'config_cache'));
        $this->assertTrue($optimizationCommands->contains('key', 'optimize'));
    }

    /**
     * Test that routes are registered correctly.
     */
    public function test_artisan_commands_routes_are_registered(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('artisan-commands.index'),
            'artisan-commands.index route should be registered'
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('artisan-commands.execute'),
            'artisan-commands.execute route should be registered'
        );
    }
}
