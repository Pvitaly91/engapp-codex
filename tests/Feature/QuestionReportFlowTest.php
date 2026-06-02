<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use App\Models\VerbHint;
use App\Services\QuestionReportFileStore;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class QuestionReportFlowTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
            'tests.compose_shuffle_enabled' => false,
            'tests.compose_shuffle_seed' => null,
        ]);

        $this->rebuildComposeTestSchema();

        $this->storageRoot = storage_path('framework/testing/question-report-flow-'.Str::random(8));
        File::ensureDirectoryExists($this->storageRoot);
        config(['filesystems.disks.local.root' => $this->storageRoot]);
        Storage::forgetDisk('local');
    }

    protected function tearDown(): void
    {
        Storage::forgetDisk('local');

        if (isset($this->storageRoot) && Str::startsWith($this->storageRoot, storage_path('framework/testing/question-report-flow-'))) {
            File::deleteDirectory($this->storageRoot);
        }

        parent::tearDown();
    }

    public function test_report_submit_saves_issue_types_without_comment(): void
    {
        $question = $this->createReportableQuestion();

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'issue_types' => ['missing_verb_hint', 'wrong_accepted_answer'],
                'test_slug' => 'sample-test',
                'mode' => 'saved-test-js-step-compose-v2',
            ]);

        $response->assertCreated()
            ->assertJsonPath('report.issue_types', ['missing_verb_hint', 'wrong_accepted_answer']);

        $report = $this->storedReport();

        $this->assertSame(['missing_verb_hint', 'wrong_accepted_answer'], $report['issue_types']);
        $this->assertSame(['Відсутній verb_hint', 'Неправильна правильна відповідь'], $report['issue_labels']);
        $this->assertSame('', $report['comment']);
    }

    public function test_new_report_stores_question_snapshot(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'comment' => 'Snapshot this question.',
                'test_slug' => 'sample-test',
                'mode' => 'saved-test-js-step-compose-v2',
            ])
            ->assertCreated();

        $report = $this->storedReport();

        $this->assertArrayHasKey('question_snapshot', $report);
        $this->assertSame(1, data_get($report, 'question_snapshot.snapshot_version'));
        $this->assertSame('report_time_db_state', data_get($report, 'question_snapshot.snapshot_source'));
        $this->assertSame($question->uuid, data_get($report, 'question_snapshot.question.uuid'));
        $this->assertSame('Ми будемо вдома до восьмої.', data_get($report, 'question_snapshot.question.text'));
        $this->assertSame('will', data_get($report, 'question_snapshot.answers.0.option_value'));
        $this->assertSame('will', data_get($report, 'question_snapshot.options.0.option'));
    }

    public function test_report_submit_saves_comment_without_issue_types(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_uuid' => $question->uuid,
                'comment' => 'The accepted answer should be will.',
            ])
            ->assertCreated();

        $report = $this->storedReport();

        $this->assertSame([], $report['issue_types']);
        $this->assertSame([], $report['issue_labels']);
        $this->assertSame('The accepted answer should be will.', $report['comment']);
    }

    public function test_report_submit_validation_requires_issue_type_or_comment_and_rejects_unknown_issue_type(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['issue_types']);

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'issue_types' => ['not_a_real_issue'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['issue_types.0']);

        $this->assertSame([], Storage::disk('local')->files('question-reports'));
    }

    public function test_question_snapshot_includes_answers_options_verb_hints_hints_variants_and_tags(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'issue_types' => ['correct_answer_in_verb_hint'],
            ])
            ->assertCreated();

        $snapshot = $this->storedReport()['question_snapshot'];

        $this->assertSame('report_time_db_state', data_get($snapshot, 'snapshot_source'));
        $this->assertSame('will', data_get($snapshot, 'answers.0.option_value'));
        $this->assertSame('a1', data_get($snapshot, 'answers.0.marker'));
        $this->assertSame('will', data_get($snapshot, 'options.0.option'));
        $this->assertSame('base verb: be', data_get($snapshot, 'verb_hints.0.option_value'));
        $this->assertSame('Think about future simple.', data_get($snapshot, 'hints.0.hint'));
        $this->assertSame('We {a1} be home by eight.', data_get($snapshot, 'variants.0.text'));
        $this->assertSame('future-simple', data_get($snapshot, 'tags.0.name'));
        $this->assertSame('saved-future-test', data_get($snapshot, 'saved_tests.0.slug'));
    }

    public function test_admin_ui_shows_report_snapshot_current_state_and_diff(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'issue_types' => ['wrong_accepted_answer'],
            ])
            ->assertCreated();

        $question->forceFill(['question' => 'Changed current DB question {a1}.'])->save();

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('Дамп питання на момент report');
        $response->assertSee('Поточне питання в базі');
        $response->assertSee('Ми будемо вдома до восьмої.');
        $response->assertSee('Changed current DB question {a1}.');
        $response->assertSee('Змінено');
    }

    public function test_old_report_without_snapshot_does_not_break_admin_ui(): void
    {
        Storage::disk('local')->put('question-reports/legacy-report.json', json_encode([
            'id' => 'legacy-report',
            'status' => 'open',
            'reported_at' => now()->toIso8601String(),
            'question' => [
                'id' => 777,
                'uuid' => 'legacy-question',
                'text' => 'Legacy question text',
            ],
            'comment' => 'Legacy comment.',
            'file' => 'question-reports/legacy-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('Snapshot: missing');
        $response->assertSee('Legacy question text');
    }

    public function test_backfill_snapshots_command_dry_run_does_not_write(): void
    {
        $question = $this->createReportableQuestion();
        $this->putLegacyReportForQuestion($question, 'legacy-report');
        $before = Storage::disk('local')->get('question-reports/legacy-report.json');

        $exitCode = Artisan::call('question-reports:backfill-snapshots', ['--dry-run' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Would backfill: question-reports/legacy-report.json', $output);
        $this->assertSame($before, Storage::disk('local')->get('question-reports/legacy-report.json'));
    }

    public function test_backfill_snapshots_command_writes_snapshot(): void
    {
        $question = $this->createReportableQuestion();
        $this->putLegacyReportForQuestion($question, 'legacy-report');

        $exitCode = Artisan::call('question-reports:backfill-snapshots');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Backfilled: question-reports/legacy-report.json', $output);

        $report = json_decode(Storage::disk('local')->get('question-reports/legacy-report.json'), true);

        $this->assertSame('current_db_state', data_get($report, 'question_snapshot.snapshot_source'));
        $this->assertSame('current_db_state', $report['snapshot_backfill_source']);
        $this->assertArrayHasKey('snapshot_backfilled_at', $report);
        $this->assertSame($question->uuid, data_get($report, 'question_snapshot.question.uuid'));
    }

    public function test_backfill_snapshots_command_skips_missing_question_without_crashing(): void
    {
        Storage::disk('local')->put('question-reports/missing-question.json', json_encode([
            'id' => 'missing-question',
            'status' => 'open',
            'reported_at' => now()->toIso8601String(),
            'question' => [
                'id' => 123456,
                'uuid' => 'missing-question-uuid',
                'text' => 'Missing question text',
            ],
            'comment' => 'Question no longer exists.',
            'file' => 'question-reports/missing-question.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $exitCode = Artisan::call('question-reports:backfill-snapshots');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Skipped question not found: question-reports/missing-question.json', $output);

        $report = json_decode(Storage::disk('local')->get('question-reports/missing-question.json'), true);

        $this->assertSame('Question not found by uuid/id', $report['snapshot_backfill_error']);
        $this->assertArrayNotHasKey('question_snapshot', $report);
    }

    public function test_build_fix_prompt_includes_selected_issue_instructions(): void
    {
        $prompt = app(QuestionReportFileStore::class)->buildFixPrompt([
            $this->fakeReport('q-1', [
                'correct_answer_in_verb_hint',
                'missing_verb_hint',
                'wrong_accepted_answer',
            ]),
        ]);

        $this->assertStringContainsString('Перевір, що verb_hint не розкриває правильну відповідь', $prompt);
        $this->assertStringContainsString('Додай verb_hint або уточни формулювання питання', $prompt);
        $this->assertStringContainsString('Перевір accepted answers/question_answers', $prompt);
        $this->assertStringContainsString('Issue keys: correct_answer_in_verb_hint, missing_verb_hint, wrong_accepted_answer', $prompt);
    }

    public function test_build_fix_prompt_includes_snapshot_current_state_and_diff(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'issue_types' => ['typo_or_bad_translation'],
                'comment' => 'Original wording is suspicious.',
                'test_slug' => 'sample-test',
                'url' => 'http://engapp-codex.loc/test/sample-test/step/compose',
            ])
            ->assertCreated();

        $question->forceFill(['question' => 'Changed current DB question {a1}.'])->save();

        $reports = app(QuestionReportFileStore::class)->all();
        $prompt = app(QuestionReportFileStore::class)->buildFixPrompt($reports);

        $this->assertStringContainsString('Дамп питання на момент report', $prompt);
        $this->assertStringContainsString('Поточне питання в базі', $prompt);
        $this->assertStringContainsString('Question text: Ми будемо вдома до восьмої.', $prompt);
        $this->assertStringContainsString('Question text: Changed current DB question {a1}.', $prompt);
        $this->assertStringContainsString('- Question text: Змінено', $prompt);
        $this->assertStringContainsString('Snapshot note: Original report-time snapshot is available.', $prompt);
    }

    public function test_build_fix_prompt_requires_full_seeder_audit(): void
    {
        $prompt = app(QuestionReportFileStore::class)->buildFixPrompt([
            $this->fakeReport('q-1', ['missing_verb_hint']),
        ]);

        $this->assertStringContainsString('Оскільки проблема знайдена в питанні з seeder Database\\Seeders\\V3\\DemoSeeder', $prompt);
        $this->assertStringContainsString('перевір усі питання, які генеруються цим seeder', $prompt);
    }

    public function test_multiple_reports_from_same_seeder_share_one_seeder_wide_requirement(): void
    {
        $prompt = app(QuestionReportFileStore::class)->buildFixPrompt([
            $this->fakeReport('q-1', ['missing_verb_hint']),
            $this->fakeReport('q-2', ['wrong_or_extra_option']),
        ]);

        $this->assertStringContainsString('Question UUID: q-1', $prompt);
        $this->assertStringContainsString('Question UUID: q-2', $prompt);
        $this->assertSame(
            1,
            substr_count($prompt, 'Оскільки проблема знайдена в питанні з seeder Database\\Seeders\\V3\\DemoSeeder')
        );
    }

    public function test_admin_sees_report_highlight_payload_in_public_test(): void
    {
        $question = $this->createReportableQuestion();
        Test::query()->create([
            'name' => 'Report Visibility Test',
            'slug' => 'report-visibility-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $this->putOpenReportForQuestion($question, 'ADMIN_ONLY_COMMENT');

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get('/test/report-visibility-test/step');

        $response->assertOk();
        $response->assertSee('window.__QUESTION_REPORT_BY_QUESTION__', false);
        $response->assertSee('ADMIN_ONLY_COMMENT', false);
        $response->assertSee('missing_verb_hint', false);
        $response->assertSee('\\u0404 report', false);
    }

    public function test_guest_does_not_receive_report_payload_or_issue_labels(): void
    {
        $question = $this->createReportableQuestion();
        Test::query()->create([
            'name' => 'Report Visibility Test',
            'slug' => 'report-visibility-test',
            'filters' => [],
            'questions' => [$question->id],
        ]);

        $this->putOpenReportForQuestion($question, 'GUEST_MUST_NOT_SEE_THIS');

        $response = $this->get('/test/report-visibility-test/step');

        $response->assertOk();
        $response->assertDontSee('GUEST_MUST_NOT_SEE_THIS', false);
        $response->assertDontSee('missing_verb_hint', false);
        $response->assertDontSee('\\u0404 report', false);
        $response->assertSee('window.__QUESTION_REPORT_BY_QUESTION__ = []', false);
    }

    public function test_report_comment_is_escaped_in_admin_view(): void
    {
        $question = $this->createReportableQuestion();

        $this
            ->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'comment' => '<script>alert(1)</script>',
            ])
            ->assertCreated();

        $response = $this
            ->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_legacy_reports_without_issue_types_or_snapshot_remain_backward_compatible(): void
    {
        Storage::disk('local')->put('question-reports/legacy-report.json', json_encode([
            'id' => 'legacy-report',
            'reported_at' => now()->toIso8601String(),
            'question' => [
                'id' => 777,
                'uuid' => 'legacy-question',
                'text' => 'Legacy question text',
                'seeder' => [
                    'class' => 'Database\\Seeders\\LegacySeeder',
                ],
            ],
            'comment' => 'Legacy comment.',
            'file' => 'question-reports/legacy-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $store = app(QuestionReportFileStore::class);
        $reports = $store->all();

        $this->assertCount(1, $reports);
        $this->assertSame([], $reports[0]['issue_types']);
        $this->assertSame([], $reports[0]['issue_labels']);
        $this->assertFalse($reports[0]['has_question_snapshot']);
        $this->assertSame('missing', $reports[0]['snapshot_status']);
        $this->assertCount(1, $store->openReports($reports));
        $this->assertCount(1, $store->selectReports($reports, ['legacy-report']));

        $prompt = $store->buildFixPrompt($reports);

        $this->assertStringContainsString('Snapshot status: missing', $prompt);
        $this->assertStringContainsString('Snapshot is missing', $prompt);
        $this->assertStringContainsString('Legacy question text', $prompt);
    }

    private function createReportableQuestion(): Question
    {
        $category = Category::query()->create(['name' => 'Future Simple']);
        $source = Source::query()->create(['name' => 'Manual QA']);
        $tag = Tag::query()->create(['name' => 'future-simple', 'category' => 'grammar']);

        $question = Question::query()->create([
            'uuid' => (string) Str::uuid(),
            'question' => 'Ми будемо вдома до восьмої.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'source_id' => $source->id,
            'type' => Question::TYPE_COMPOSE_TOKENS,
            'seeder' => 'Database\\Seeders\\V3\\DemoSeeder',
        ]);

        $will = QuestionOption::query()->create(['option' => 'will']);
        $wont = QuestionOption::query()->create(['option' => "won't"]);
        $baseVerb = QuestionOption::query()->create(['option' => 'base verb: be']);

        foreach ([$will, $wont] as $option) {
            DB::table('question_option_question')->insert([
                'question_id' => $question->id,
                'option_id' => $option->id,
                'flag' => null,
            ]);
        }

        QuestionAnswer::query()->create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $will->id,
        ]);

        VerbHint::query()->create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $baseVerb->id,
        ]);

        QuestionHint::query()->create([
            'question_id' => $question->id,
            'provider' => 'manual',
            'locale' => 'uk',
            'hint' => 'Think about future simple.',
        ]);

        QuestionVariant::query()->create([
            'question_id' => $question->id,
            'text' => 'We {a1} be home by eight.',
        ]);

        $question->tags()->attach($tag->id);

        $savedTestId = DB::table('saved_grammar_tests')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Saved Future Test',
            'slug' => 'saved-future-test',
            'filters' => json_encode([]),
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('saved_grammar_test_questions')->insert([
            'saved_grammar_test_id' => $savedTestId,
            'question_uuid' => $question->uuid,
            'position' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $question;
    }

    private function storedReport(): array
    {
        $files = Storage::disk('local')->files('question-reports');

        $this->assertCount(1, $files);

        return json_decode(Storage::disk('local')->get($files[0]), true);
    }

    /**
     * @param  array<int, string>  $issueTypes
     * @return array<string, mixed>
     */
    private function fakeReport(string $uuid, array $issueTypes): array
    {
        return [
            'id' => 'report-'.$uuid,
            'status' => 'open',
            'reported_at' => now()->toIso8601String(),
            'test' => [
                'slug' => 'polyglot-demo-a1',
                'mode' => 'saved-test-js-step-compose-v2',
                'url' => 'http://engapp-codex.loc/test/polyglot-demo-a1/step/compose',
            ],
            'question' => [
                'id' => 10,
                'uuid' => $uuid,
                'text' => 'Ми будемо вдома до восьмої.',
                'type' => Question::TYPE_COMPOSE_TOKENS,
                'level' => 'A1',
                'difficulty' => 1,
                'category' => 'Future Simple',
                'source' => ['name' => 'Demo source'],
                'seeder' => [
                    'class' => 'Database\\Seeders\\V3\\DemoSeeder',
                    'file' => 'database/seeders/V3/DemoSeeder.php',
                ],
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => null,
                        'option_value' => 'will',
                        'option_id' => 1,
                        'verb_hint' => null,
                    ],
                ],
                'options' => [
                    ['id' => 1, 'value' => 'will'],
                    ['id' => 2, 'value' => "won't"],
                ],
                'verb_hints' => [
                    ['marker' => 'a1', 'option_value' => 'base verb: be', 'option_id' => 3],
                ],
                'hints' => [
                    ['provider' => 'manual', 'locale' => 'uk', 'hint' => 'Think about future simple.'],
                ],
                'variants' => [
                    ['id' => 1, 'text' => 'We {a1} be home by eight.'],
                ],
                'tags' => [
                    ['name' => 'future-simple', 'category' => 'grammar'],
                ],
            ],
            'issue_types' => $issueTypes,
            'comment' => '',
            'file' => 'question-reports/report-'.$uuid.'.json',
        ];
    }

    private function putOpenReportForQuestion(Question $question, string $comment): void
    {
        Storage::disk('local')->makeDirectory('question-reports');
        Storage::disk('local')->put('question-reports/open-report.json', json_encode([
            'id' => 'open-report',
            'status' => 'open',
            'reported_at' => now()->toIso8601String(),
            'test' => [
                'slug' => 'report-visibility-test',
                'mode' => 'saved-test-js-step-v2',
            ],
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'seeder' => [
                    'class' => $question->seeder,
                    'file' => null,
                ],
            ],
            'issue_types' => ['missing_verb_hint'],
            'issue_labels' => ['Відсутній verb_hint'],
            'comment' => $comment,
            'file' => 'question-reports/open-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function putLegacyReportForQuestion(Question $question, string $id): void
    {
        Storage::disk('local')->makeDirectory('question-reports');
        Storage::disk('local')->put("question-reports/{$id}.json", json_encode([
            'id' => $id,
            'status' => 'open',
            'reported_at' => now()->toIso8601String(),
            'test' => ['slug' => 'sample-test'],
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'text' => $question->question,
                'seeder' => [
                    'class' => $question->seeder,
                    'file' => null,
                ],
            ],
            'comment' => 'Legacy comment.',
            'file' => "question-reports/{$id}.json",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
