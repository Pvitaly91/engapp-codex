<?php

namespace Tests\Feature;

use App\Models\SavedGrammarTest;
use App\Models\User;
use App\Models\UserPolyglotAnswerAttempt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PolyglotUserProgressTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_polyglot_answer_attempts');
        Schema::dropIfExists('user_polyglot_lesson_progress');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        $this->rebuildComposeTestSchema();
        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        (require database_path('migrations/2026_04_25_000001_create_user_polyglot_lesson_progress_table.php'))->up();
        (require database_path('migrations/2026_04_25_000002_create_user_polyglot_answer_attempts_table.php'))->up();
    }

    public function test_authenticated_admin_can_record_attempt_and_progress_updates(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();

        $response = $this->withSession($this->adminSession($user))
            ->postJson('/courses/polyglot-english-a1/progress/attempt', [
                'lesson_slug' => $firstLesson,
                'question_uuid' => 'polyglot-q-1',
                'rating' => 5,
                'is_correct' => true,
                'answer_payload' => [
                    'submitted_answer' => 'I am ready.',
                    'current_queue_index' => 3,
                    'password' => 'should-not-be-stored',
                ],
                'client_attempt_uuid' => 'attempt-1',
            ]);

        $response->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('lesson.answered_count', 1)
            ->assertJsonPath('lesson.last_100_count', 1)
            ->assertJsonPath('lesson.last_100_average', 5)
            ->assertJsonPath('lesson_progress.current_queue_index', 3);

        $this->assertDatabaseHas('user_polyglot_answer_attempts', [
            'user_id' => $user->id,
            'course_slug' => 'polyglot-english-a1',
            'lesson_slug' => $firstLesson,
            'question_uuid' => 'polyglot-q-1',
            'client_attempt_uuid' => 'attempt-1',
        ]);

        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'course_slug' => 'polyglot-english-a1',
            'lesson_slug' => $firstLesson,
            'answered_count' => 1,
            'last_100_count' => 1,
            'is_completed' => false,
        ]);

        $this->assertArrayNotHasKey(
            'password',
            UserPolyglotAnswerAttempt::query()->where('client_attempt_uuid', 'attempt-1')->firstOrFail()->answer_payload ?? []
        );
    }

    public function test_lesson_completed_only_after_100_attempts_with_required_average(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();

        $this->recordAttempts($user, $firstLesson, array_fill(0, 99, 5));

        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'lesson_slug' => $firstLesson,
            'answered_count' => 99,
            'is_completed' => false,
        ]);

        $this->recordAttempts($user, $firstLesson, [5], 99);

        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'lesson_slug' => $firstLesson,
            'answered_count' => 100,
            'last_100_count' => 100,
            'last_100_average' => 5,
            'is_completed' => true,
        ]);
    }

    public function test_lesson_not_completed_when_last_100_average_is_too_low(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();
        $ratings = [
            ...array_fill(0, 89, 5),
            ...array_fill(0, 11, 0),
        ];

        $this->recordAttempts($user, $firstLesson, $ratings);

        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'lesson_slug' => $firstLesson,
            'answered_count' => 100,
            'last_100_count' => 100,
            'last_100_average' => 4.45,
            'is_completed' => false,
        ]);
    }

    public function test_next_lesson_unlocks_only_when_previous_lesson_is_completed(): void
    {
        $user = User::factory()->create();
        [$firstLesson, $secondLesson] = $this->createCourseLessons();

        $this->withSession($this->adminSession($user))
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath("progress.lessons.$firstLesson.is_unlocked", true)
            ->assertJsonPath("progress.lessons.$secondLesson.is_unlocked", false);

        $this->recordAttempts($user, $firstLesson, array_fill(0, 100, 5));

        $this->withSession($this->adminSession($user))
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath("progress.lessons.$firstLesson.is_completed", true)
            ->assertJsonPath("progress.lessons.$secondLesson.is_unlocked", true);
    }

    public function test_another_user_does_not_see_existing_user_progress(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();

        $this->recordAttempts($user, $firstLesson, [5]);
        $this->flushSession();

        $this->actingAs($otherUser)
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 0)
            ->assertJsonPath("progress.lessons.$firstLesson.is_completed", false);
    }

    public function test_admin_session_progress_prefers_admin_user_over_existing_auth_user(): void
    {
        $adminUser = User::factory()->create();
        $otherUser = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();

        $this->recordAttempts($adminUser, $firstLesson, [5]);

        $this->actingAs($otherUser)
            ->withSession($this->adminSession($adminUser))
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('progress.user_id', $adminUser->id)
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 1);
    }

    public function test_unauthenticated_attempt_does_not_write_db_progress(): void
    {
        [$firstLesson] = $this->createCourseLessons();

        $this->postJson('/courses/polyglot-english-a1/progress/attempt', [
            'lesson_slug' => $firstLesson,
            'rating' => 5,
            'client_attempt_uuid' => 'guest-attempt',
        ])
            ->assertOk()
            ->assertJsonPath('authenticated', false);

        $this->assertDatabaseCount('user_polyglot_answer_attempts', 0);
        $this->assertDatabaseCount('user_polyglot_lesson_progress', 0);
    }

    public function test_duplicate_client_attempt_uuid_does_not_double_count(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();
        $payload = [
            'lesson_slug' => $firstLesson,
            'rating' => 5,
            'is_correct' => true,
            'client_attempt_uuid' => 'duplicate-attempt',
        ];

        $this->withSession($this->adminSession($user))
            ->postJson('/courses/polyglot-english-a1/progress/attempt', $payload)
            ->assertOk();

        $this->withSession($this->adminSession($user))
            ->postJson('/courses/polyglot-english-a1/progress/attempt', $payload)
            ->assertOk()
            ->assertJsonPath('lesson.answered_count', 1);

        $this->assertDatabaseCount('user_polyglot_answer_attempts', 1);
        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'lesson_slug' => $firstLesson,
            'answered_count' => 1,
        ]);
    }

    public function test_import_syncs_existing_local_progress_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();
        $localProgress = [
            'lesson_progress' => [
                $firstLesson => [
                    'lesson_slug' => $firstLesson,
                    'course_slug' => 'polyglot-english-a1',
                    'current_queue_index' => 2,
                    'rolling_results' => [5, 0, 5],
                    'total_attempts' => 3,
                ],
            ],
        ];

        $this->withSession($this->adminSession($user))
            ->postJson('/courses/polyglot-english-a1/progress/import', [
                'local_progress' => $localProgress,
            ])
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 3)
            ->assertJsonPath("progress.lessons.$firstLesson.last_100_count", 3)
            ->assertJsonPath("progress.lessons.$firstLesson.last_100_average", 3.33)
            ->assertJsonPath("progress.lesson_progress.$firstLesson.current_queue_index", 2);

        $this->assertDatabaseCount('user_polyglot_answer_attempts', 3);
        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'course_slug' => 'polyglot-english-a1',
            'lesson_slug' => $firstLesson,
            'answered_count' => 3,
            'last_100_count' => 3,
            'last_100_average' => 3.33,
            'is_completed' => false,
        ]);

        $this->withSession($this->adminSession($user))
            ->postJson('/courses/polyglot-english-a1/progress/import', [
                'local_progress' => $localProgress,
            ])
            ->assertOk()
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 3);

        $this->assertDatabaseCount('user_polyglot_answer_attempts', 3);

        $this->withSession($this->adminSession($user))
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 3);
    }

    public function test_admin_session_without_user_id_is_bound_to_configured_user(): void
    {
        config([
            'admin.username' => 'admin',
            'admin.user_email' => 'admin@example.test',
        ]);
        [$firstLesson] = $this->createCourseLessons();

        $this->withSession(['admin_authenticated' => true])
            ->getJson('/courses/polyglot-english-a1/progress')
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath("progress.lessons.$firstLesson.is_unlocked", true);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'name' => 'admin',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    private function recordAttempts(User $user, string $lessonSlug, array $ratings, int $offset = 0): void
    {
        foreach (array_values($ratings) as $index => $rating) {
            $this->withSession($this->adminSession($user))
                ->postJson('/courses/polyglot-english-a1/progress/attempt', [
                    'lesson_slug' => $lessonSlug,
                    'question_uuid' => 'polyglot-q-'.$index,
                    'rating' => $rating,
                    'is_correct' => $rating >= 4.5,
                    'client_attempt_uuid' => 'attempt-'.($offset + $index),
                ])
                ->assertOk()
                ->assertJsonPath('authenticated', true);
        }
    }

    private function createCourseLessons(): array
    {
        $firstLesson = 'polyglot-fixture-lesson-1';
        $secondLesson = 'polyglot-fixture-lesson-2';

        $this->createLesson($firstLesson, 1, null, $secondLesson);
        $this->createLesson($secondLesson, 2, $firstLesson, null);

        return [$firstLesson, $secondLesson];
    }

    private function createLesson(string $slug, int $order, ?string $previousSlug, ?string $nextSlug): void
    {
        SavedGrammarTest::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => Str::headline($slug),
            'slug' => $slug,
            'filters' => [
                'course_slug' => 'polyglot-english-a1',
                'lesson_order' => $order,
                'previous_lesson_slug' => $previousSlug,
                'next_lesson_slug' => $nextSlug,
                'topic' => 'fixture topic',
                'level' => 'A1',
                'mode' => 'compose_tokens',
                'completion' => [
                    'rolling_window' => 100,
                    'min_rating' => 4.5,
                ],
            ],
            'description' => 'Progress fixture',
        ]);
    }

    private function adminSession(User $user): array
    {
        return [
            'admin_authenticated' => true,
            'admin_user_id' => $user->id,
        ];
    }
}
