<?php

namespace Tests\Feature;

use App\Models\SavedGrammarTest;
use App\Models\User;
use App\Models\UserPolyglotAnswerAttempt;
use App\Models\UserPolyglotLessonProgress;
use App\Services\PolyglotProgressService;
use Illuminate\Support\Facades\DB;
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
            // A correct answer advances past the submitted queue index.
            ->assertJsonPath('lesson_progress.current_queue_index', 4)
            ->assertJsonPath('lesson_progress.correct_attempts', 1)
            ->assertJsonPath('lesson_progress.question_stats.polyglot-q-1.correct', 1);

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
            'question_uuid' => 'replayed-question',
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
            ->assertJsonPath('lesson.answered_count', 1)
            ->assertJsonPath('lesson_progress.correct_attempts', 1)
            ->assertJsonPath('lesson_progress.question_stats.replayed-question.shown', 1);

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
            ->assertJsonPath("progress.lesson_progress.$firstLesson.current_queue_index", 2)
            ->assertJsonPath("progress.lesson_progress.$firstLesson.correct_attempts", 2)
            ->assertJsonPath("progress.lesson_progress.$firstLesson.question_stats", []);

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

    public function test_payload_uses_only_requested_rows_and_batches_attempt_reads_across_lessons(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $course = 'polyglot-english-a1';
        $lessons = [
            ['slug' => 'started', 'lesson_order' => 1],
            ['slug' => 'unanswered', 'lesson_order' => 2],
            ['slug' => 'no-progress-row', 'lesson_order' => 3],
            ['slug' => 'only-progress-row', 'lesson_order' => 4],
        ];
        $service = app(PolyglotProgressService::class);
        $first = '2026-07-01 00:01:00';
        $tie = '2026-07-02 00:02:00';
        $last = '2026-07-03 00:03:00';

        foreach ([
            ['Tie-z', 4.50, false, $tie],
            ['Tie-a', 4.49, false, $tie],
            [" Alpha\t", 4.50, null, $last],
            ['alpha', 4.49, false, $tie],
            ['Alpha', 4.49, false, $first],
            ['Alpha', 1.00, true, $tie],
            [null, 1.00, true, $tie],
            [" \t\r\n", 4.50, false, $tie],
            ['', 4.49, false, $tie],
        ] as [$uuid, $rating, $isCorrect, $answeredAt]) {
            $this->addAttempt($user, $course, 'started', $uuid, $rating, $isCorrect, $answeredAt);
        }
        $this->addAttempt($user, $course, 'unanswered', 'zero-count-row', 5, true, $tie);
        $this->addAttempt($user, $course, 'no-progress-row', 'not-visible', 5, true, $tie);
        $this->addAttempt($otherUser, $course, 'started', 'wrong-user', 5, true, $tie);
        $this->addAttempt($user, 'another-course', 'started', 'wrong-course', 5, true, $tie);
        $this->addAttempt($user, $course, 'off-manifest', 'wrong-lesson', 5, true, $tie);
        $this->addProgressRow($user, $course, 'off-manifest', 1);
        $this->addProgressRow($otherUser, $course, 'started', 1);
        $this->addProgressRow($user, 'another-course', 'started', 1);

        $read = fn () => $service->getCourseProgress($user, $course, $lessons);
        [$empty, $emptyQueries] = $this->captureQueries($read);
        $this->assertSame(0, $empty['lesson_progress']['started']['correct_attempts']);
        $this->assertSame([], $empty['lesson_progress']['started']['question_stats']);
        $this->assertCount(0, $this->attemptReads($emptyQueries));

        $this->addProgressRow($user, $course, 'started', 9);
        [$one, $oneQueries] = $this->captureQueries($read);

        $this->addProgressRow($user, $course, 'unanswered', 0);
        $this->addProgressRow($user, $course, 'only-progress-row', 0);
        [$several, $severalQueries] = $this->captureQueries($read);

        $progress = $several['lesson_progress'];
        $this->assertSame(array_column($lessons, 'slug'), array_keys($progress));
        $this->assertSame($one['lesson_progress']['started'], $progress['started']);
        $this->assertSame(9, $progress['started']['total_attempts']);
        $this->assertSame(5, $progress['started']['correct_attempts']);
        $tieJson = '2026-07-01T21:02:00.000000Z';
        $this->assertSame([
            'Alpha' => $this->questionStat('Alpha', 3, 2, '2026-07-02T21:03:00.000000Z'),
            'Tie-z' => $this->questionStat('Tie-z', 1, 1, $tieJson),
            'Tie-a' => $this->questionStat('Tie-a', 1, 0, $tieJson),
            'alpha' => $this->questionStat('alpha', 1, 0, $tieJson),
        ], $progress['started']['question_stats']);
        $this->assertSame(0, $progress['unanswered']['total_attempts']);
        $this->assertSame(1, $progress['unanswered']['correct_attempts']);
        $this->assertSame([
            'zero-count-row' => $this->questionStat('zero-count-row', 1, 1, $tieJson),
        ], $progress['unanswered']['question_stats']);
        foreach (['no-progress-row', 'only-progress-row'] as $lesson) {
            $this->assertSame(0, $progress[$lesson]['correct_attempts']);
            $this->assertSame([], $progress[$lesson]['question_stats']);
        }

        $oneReads = count($this->attemptReads($oneQueries));
        $this->assertGreaterThan(0, $oneReads);
        $this->assertLessThanOrEqual(2, $oneReads);
        $this->assertCount($oneReads, $this->attemptReads($severalQueries));
        foreach ([$emptyQueries, $oneQueries, $severalQueries] as $queries) {
            $attemptSchemaChecks = array_filter($queries, static fn (array $query) =>
                preg_match('/sqlite_(master|schema)/i', $query['query'])
                && (str_contains($query['query'], 'user_polyglot_answer_attempts')
                    || in_array('user_polyglot_answer_attempts', $query['bindings'], true)));
            $this->assertCount(1, $attemptSchemaChecks);
        }
    }

    public function test_attempt_history_is_complete_across_pages_and_is_read_fresh_in_the_same_transaction(): void
    {
        $user = User::factory()->create();
        [$lesson] = $this->createCourseLessons();
        $course = 'polyglot-english-a1';
        $this->addProgressRow($user, $course, $lesson, 1201);
        $rows = array_fill(0, 1201, [
            'user_id' => $user->id,
            'course_slug' => $course,
            'lesson_slug' => $lesson,
            'question_uuid' => 'repeated-question',
            'rating' => 4.49,
            'is_correct' => false,
            'answered_at' => '2026-07-02 00:02:00',
        ]);
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('user_polyglot_answer_attempts')->insert($chunk);
        }

        $service = app(PolyglotProgressService::class);
        $read = fn () => $service->getCourseProgressPayload($user, $course)['lesson_progress'][$lesson];
        $before = $read();
        $this->assertSame(0, $before['correct_attempts']);
        $this->assertSame([
            'repeated-question' => $this->questionStat('repeated-question', 1201, 0, '2026-07-01T21:02:00.000000Z'),
        ], $before['question_stats']);

        DB::transaction(function () use ($user, $course, $lesson, $read) {
            $this->addAttempt($user, $course, $lesson, 'repeated-question', 4.50, false, '2026-07-03 00:03:00');
            $after = $read();
            $this->assertSame(1, $after['correct_attempts']);
            $this->assertSame([
                'repeated-question' => $this->questionStat('repeated-question', 1202, 1, '2026-07-02T21:03:00.000000Z'),
            ], $after['question_stats']);
        });
    }

    public function test_debug_reset_rebuilds_progress_from_the_remaining_attempts(): void
    {
        $user = User::factory()->create();
        [$firstLesson, $secondLesson] = $this->createCourseLessons();
        $course = 'polyglot-english-a1';
        $this->addProgressRow($user, $course, $firstLesson, 1);
        $this->addProgressRow($user, $course, $secondLesson, 1);
        $this->addAttempt($user, $course, $firstLesson, 'reset-me', 5, true, '2026-07-02 00:02:00');
        $this->addAttempt($user, $course, $secondLesson, 'keep-me', 5, true, '2026-07-02 00:02:00');

        $this->withSession($this->adminSession($user))
            ->postJson("/courses/$course/progress/debug", [
                'action' => 'reset-current-lesson',
                'lesson_slug' => $firstLesson,
            ])
            ->assertOk()
            ->assertJsonPath('applied', true)
            ->assertJsonPath('lesson_progress.correct_attempts', 0)
            ->assertJsonPath('lesson_progress.question_stats', [])
            ->assertJsonPath("course_progress.lesson_progress.$secondLesson.correct_attempts", 1)
            ->assertJsonPath("course_progress.lesson_progress.$secondLesson.question_stats.keep-me.shown", 1);
        $this->assertDatabaseCount('user_polyglot_answer_attempts', 1);
    }

    public function test_missing_attempt_storage_keeps_read_and_write_fallback(): void
    {
        $user = User::factory()->create();
        [$firstLesson] = $this->createCourseLessons();
        $course = 'polyglot-english-a1';
        $this->addProgressRow($user, $course, $firstLesson, 7);
        Schema::drop('user_polyglot_answer_attempts');

        $this->actingAs($user)
            ->getJson("/courses/$course/progress")
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath("progress.lessons.$firstLesson.answered_count", 0)
            ->assertJsonPath("progress.lesson_progress.$firstLesson.correct_attempts", 0)
            ->assertJsonPath("progress.lesson_progress.$firstLesson.question_stats", []);

        $this->postJson("/courses/$course/progress/attempt", ['lesson_slug' => $firstLesson, 'rating' => 5])
            ->assertOk()
            ->assertJsonPath('progress_storage_available', false)
            ->assertJsonPath('lesson_progress.correct_attempts', 0)
            ->assertJsonPath('lesson_progress.question_stats', []);
        $this->assertDatabaseHas('user_polyglot_lesson_progress', [
            'user_id' => $user->id,
            'lesson_slug' => $firstLesson,
            'answered_count' => 7,
        ]);
    }

    private function addProgressRow(User $user, string $course, string $lesson, int $answered): void
    {
        UserPolyglotLessonProgress::query()->create([
            'user_id' => $user->id,
            'course_slug' => $course,
            'lesson_slug' => $lesson,
            'answered_count' => $answered,
        ]);
    }

    private function addAttempt(
        User $user, string $course, string $lesson, ?string $uuid, float $rating, ?bool $isCorrect, string $answeredAt,
    ): void {
        UserPolyglotAnswerAttempt::query()->create([
            'user_id' => $user->id,
            'course_slug' => $course,
            'lesson_slug' => $lesson,
            'question_uuid' => $uuid,
            'rating' => $rating,
            'is_correct' => $isCorrect,
            'answered_at' => $answeredAt,
        ]);
    }

    private function questionStat(string $uuid, int $shown, int $correct, string $last): array
    {
        return [
            'uuid' => $uuid,
            'shown' => $shown,
            'correct' => $correct,
            'incorrect' => $shown - $correct,
            'last_seen_at' => $last,
            'last_answered_at' => $last,
        ];
    }

    private function captureQueries(callable $read): array
    {
        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            return [$read(), $connection->getQueryLog()];
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }
    }

    private function attemptReads(array $queries): array
    {
        return array_filter($queries, static fn (array $query) =>
            str_contains(strtolower($query['query']), 'from "user_polyglot_answer_attempts"'));
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
