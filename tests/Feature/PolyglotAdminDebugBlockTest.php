<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use App\Models\UserPolyglotAnswerAttempt;
use Database\Seeders\V2\Polyglot\PolyglotPresentPerfectContinuousBasicsLessonSeeder;
use Illuminate\Support\Facades\Schema;
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

    public function test_admin_debug_payload_includes_seeder_and_server_question_stats(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_polyglot_answer_attempts');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        (require database_path('migrations/2026_04_25_000002_create_user_polyglot_answer_attempts_table.php'))->up();

        $lessonSlug = 'polyglot-present-perfect-continuous-basics-b1';
        $initialResponse = $this
            ->withSession(['admin_authenticated' => true])
            ->get("/test/{$lessonSlug}/step/compose");

        $initialResponse->assertOk();

        $initialPayload = $initialResponse->viewData('polyglotAdminDebugPayload');
        $initialQuestion = $initialPayload['questions'][0] ?? null;
        $questionUuid = (string) data_get($initialQuestion, 'uuid');
        $courseSlug = (string) data_get($initialPayload, 'course.slug');
        $question = Question::query()->where('uuid', $questionUuid)->firstOrFail();
        $user = User::factory()->create();

        UserPolyglotAnswerAttempt::query()->create([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
            'question_uuid' => $question->uuid,
            'rating' => 5,
            'is_correct' => true,
            'client_attempt_uuid' => 'debug-correct',
            'answered_at' => now()->subMinute(),
        ]);

        UserPolyglotAnswerAttempt::query()->create([
            'user_id' => $user->id,
            'course_slug' => $courseSlug,
            'lesson_slug' => $lessonSlug,
            'question_uuid' => $question->uuid,
            'rating' => 1,
            'is_correct' => false,
            'client_attempt_uuid' => 'debug-wrong',
            'answered_at' => now(),
        ]);

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get("/test/{$lessonSlug}/step/compose");

        $response->assertOk();
        $response->assertSee('Server stats', false);
        $response->assertSee('Seeder', false);

        $payload = $response->viewData('polyglotAdminDebugPayload');
        $debugQuestion = collect($payload['questions'] ?? [])->firstWhere('uuid', $question->uuid);

        $this->assertNotNull($debugQuestion);
        $this->assertStringContainsString(
            'PolyglotPresentPerfectContinuousBasicsLessonSeeder',
            (string) data_get($debugQuestion, 'tech_info.seeder.class')
        );
        $this->assertSame(2, data_get($debugQuestion, 'server_stats.answered'));
        $this->assertSame(1, data_get($debugQuestion, 'server_stats.correct'));
        $this->assertSame(1, data_get($debugQuestion, 'server_stats.incorrect'));
        $this->assertSame(50.0, data_get($debugQuestion, 'server_stats.correct_percent'));
    }

    public function test_guest_does_not_receive_polyglot_debug_block_or_payload(): void
    {
        $response = $this->get('/test/polyglot-present-perfect-continuous-basics-b1/step/compose');

        $response->assertOk();
        $response->assertDontSee('id="polyglot-admin-debug"', false);
        $response->assertDontSee('polyglot-admin-debug-payload', false);
        $response->assertDontSee('data-polyglot-debug-correct-answer', false);
        $response->assertDontSee('window.__POLYGLOT_ADMIN_DEBUG__', false);
    }
}
