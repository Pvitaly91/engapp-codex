<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ArtisanManagerTest extends TestCase
{
    /**
     * Test that the artisan manager index route exists
     */
    public function test_artisan_manager_route_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('artisan.index'),
            'The artisan.index route should exist'
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('artisan.execute'),
            'The artisan.execute route should exist'
        );
    }

    /**
     * Test that the config is loaded correctly
     */
    public function test_config_is_loaded(): void
    {
        $this->assertEquals(
            'admin/artisan',
            config('artisan-manager.route_prefix')
        );

        $this->assertIsArray(config('artisan-manager.middleware'));
        $this->assertIsArray(config('artisan-manager.enabled_commands'));
    }

    /**
     * Test that enabled commands are configurable
     */
    public function test_enabled_commands_contain_expected_commands(): void
    {
        $enabledCommands = config('artisan-manager.enabled_commands');

        $this->assertContains('cache_clear', $enabledCommands);
        $this->assertContains('config_clear', $enabledCommands);
        $this->assertContains('route_clear', $enabledCommands);
        $this->assertContains('view_clear', $enabledCommands);
        $this->assertContains('optimize', $enabledCommands);
    }

    /**
     * Test that artisan commands can be executed programmatically
     */
    public function test_artisan_commands_can_be_executed(): void
    {
        // Test that cache:clear command works
        $exitCode = Artisan::call('cache:clear');
        $this->assertEquals(0, $exitCode, 'cache:clear should execute successfully');

        // Test that config:clear command works
        $exitCode = Artisan::call('config:clear');
        $this->assertEquals(0, $exitCode, 'config:clear should execute successfully');

        // Test that route:clear command works
        $exitCode = Artisan::call('route:clear');
        $this->assertEquals(0, $exitCode, 'route:clear should execute successfully');
    }
}
