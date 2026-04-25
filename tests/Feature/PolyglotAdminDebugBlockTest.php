<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotAdminDebugBlockTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
        $this->seed(PolyglotPresentPerfectContinuousBasicsLessonSeeder::class);
    }

    public function test_admin_session_receives_polyglot_debug_block_and_payload(): void
    {
        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get('/test/polyglot-present-perfect-continuous-basics-b1/step/compose');

        $response->assertOk();
        $response->assertSee('id="polyglot-admin-debug"', false);
        $response->assertSee('data-polyglot-admin-debug="1"', false);
        $response->assertSee('polyglot-admin-debug-payload', false);
        $response->assertSee('data-polyglot-debug-correct-answer', false);
        $response->assertSee('window.__POLYGLOT_ADMIN_DEBUG__', false);
    }

    public function test_admin_session_receives_debug_block_even_when_auth_user_is_not_flagged_admin(): void
    {
        $user = new User([
            'name' => 'Session Admin',
            'email' => 'session-admin@example.test',
        ]);
        $user->id = 123;
        $user->setAttribute('is_admin', false);

        $response = $this
            ->actingAs($user)
            ->withSession(['admin_authenticated' => true])
            ->get('/test/polyglot-present-perfect-continuous-basics-b1/step/compose');

        $response->assertOk();
        $response->assertSee('id="polyglot-admin-debug"', false);
        $response->assertSee('data-polyglot-admin-debug="1"', false);
        $response->assertSee('window.__POLYGLOT_ADMIN_DEBUG__', false);
    }

    public function test_guest_does_not_receive_polyglot_debug_block_or_payload(): void
    {
        $response = $this->get('/test/polyglot-present-perfect-continuous-basics-b1/step/compose');

        $response->assertOk();
        $response->assertDontSee('polyglot-admin-debug', false);
        $response->assertDontSee('data-polyglot-admin-debug', false);
        $response->assertDontSee('polyglot-admin-debug-payload', false);
        $response->assertDontSee('data-polyglot-debug-correct-answer', false);
        $response->assertDontSee('window.__POLYGLOT_ADMIN_DEBUG__', false);
    }
}
