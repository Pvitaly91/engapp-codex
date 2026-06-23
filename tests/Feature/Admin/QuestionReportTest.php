<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuestionReportTest extends TestCase
{
    private string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $viewsPath = storage_path('framework/views');
        if (! is_dir($viewsPath)) {
            mkdir($viewsPath, 0777, true);
        }
        config(['view.compiled' => $viewsPath]);

        $this->storageRoot = storage_path('framework/testing/question-report-store-'.Str::random(8));
        File::ensureDirectoryExists($this->storageRoot);
        config(['filesystems.disks.local.root' => $this->storageRoot]);
        Storage::forgetDisk('local');

        $this->ensureSchema();
        $this->resetData();
    }

    protected function tearDown(): void
    {
        Storage::forgetDisk('local');

        if (isset($this->storageRoot) && Str::startsWith($this->storageRoot, storage_path('framework/testing/question-report-store-'))) {
            File::deleteDirectory($this->storageRoot);
        }

        parent::tearDown();
    }

    public function test_admin_can_report_question_and_store_git_trackable_json_file(): void
    {
        $category = Category::create(['name' => 'Present Simple']);
        $source = Source::create(['name' => 'Seeded source']);
        $question = Question::create([
            'uuid' => 'polyglot-to-be-a1-q01',
            'question' => 'I {a1} to school every day.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'source_id' => $source->id,
            'seeder' => 'Database\\Seeders\\Ai\\ExampleSeeder',
        ]);
        $option = QuestionOption::create(['option' => 'go']);
        $question->options()->attach($option->id);
        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a1',
            'option_id' => $option->id,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'question_uuid' => $question->uuid,
                'comment' => 'У варіанті відповіді має бути goes.',
                'test_slug' => 'present-simple-test',
                'test_name' => 'Present Simple Test',
                'mode' => 'saved-test-js-step-v2',
                'url' => 'http://localhost/test/present-simple-test/step',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Репорт збережено.');

        $files = Storage::disk('local')->files('question-reports');
        $this->assertCount(1, $files);

        $payload = json_decode(Storage::disk('local')->get($files[0]), true);

        $this->assertSame('open', $payload['status']);
        $this->assertSame('У варіанті відповіді має бути goes.', $payload['comment']);
        $this->assertSame('present-simple-test', $payload['test']['slug']);
        $this->assertSame('polyglot-to-be-a1-q01', $payload['question']['uuid']);
        $this->assertSame('I {a1} to school every day.', $payload['question']['text']);
        $this->assertSame('Database\\Seeders\\Ai\\ExampleSeeder', $payload['question']['seeder']['class']);
        $this->assertSame([], $payload['issue_types']);
        $this->assertSame([], $payload['issue_labels']);
        $this->assertSame('a1', $payload['question']['answers'][0]['marker']);
        $this->assertNull($payload['question']['answers'][0]['answer']);
        $this->assertSame('go', $payload['question']['answers'][0]['option_value']);
        $this->assertSame($option->id, $payload['question']['answers'][0]['option_id']);
        $this->assertNull($payload['question']['answers'][0]['verb_hint']);
        $this->assertSame('go', $payload['question']['answers'][0]['value']);
        $this->assertStringStartsWith('question-reports/', $payload['file']);
    }

    public function test_authenticated_admin_user_can_report_question_with_long_virtual_test_url(): void
    {
        $admin = new User;
        $admin->forceFill([
            'id' => 91,
            'name' => 'Admin User',
            'email' => 'admin@example.test',
        ]);
        $admin->setAttribute('is_admin', true);

        $category = Category::create(['name' => 'Virtual category']);
        $question = Question::create([
            'uuid' => 'virtual-category-question',
            'question' => 'She {a1} ready.',
            'difficulty' => 1,
            'level' => 'A1',
            'category_id' => $category->id,
            'seeder' => 'Database\\Seeders\\V3\\AI\\Example\\VirtualCategorySeeder',
        ]);

        $longUrl = 'http://localhost/test/theory-category-301-a1?filters='.str_repeat('a', 5000).'&launch=test-launch';

        $response = $this->actingAs($admin)
            ->postJson(route('question-reports.store'), [
                'question_id' => $question->id,
                'comment' => 'Неправильний варіант відповіді.',
                'test_slug' => 'theory-category-301-a1',
                'test_name' => 'Category A1',
                'mode' => 'saved-test-js-v2',
                'url' => $longUrl,
            ]);

        $response->assertCreated();

        $files = Storage::disk('local')->files('question-reports');
        $this->assertCount(1, $files);

        $payload = json_decode(Storage::disk('local')->get($files[0]), true);

        $this->assertSame('theory-category-301-a1', $payload['test']['slug']);
        $this->assertSame($longUrl, $payload['test']['url']);
        $this->assertSame('virtual-category-question', $payload['question']['uuid']);
    }

    public function test_admin_can_view_question_reports_from_files(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'test' => [
                'slug' => 'reported-test',
                'url' => 'http://localhost/test/reported-test',
            ],
            'question' => [
                'id' => 10,
                'uuid' => '44444444-4444-4444-8444-444444444444',
                'text' => 'Bad question text',
                'level' => 'A2',
                'seeder' => [
                    'class' => 'Database\\Seeders\\ReportedSeeder',
                    'file' => 'database/seeders/ReportedSeeder.php',
                ],
                'answers' => [
                    ['marker' => 'a1', 'value' => 'answer'],
                ],
                'options' => ['answer', 'wrong answer'],
            ],
            'comment' => 'Коментар адміна',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('Репорти питань');
        $response->assertSee('Невиконаний');
        $response->assertSee('Debug info питання');
        $response->assertSee('Знімок питання');
        $response->assertSee('Опції');
        $response->assertSee('Bad question text');
        $response->assertSee('Database\\Seeders\\ReportedSeeder');
        $response->assertSee('Коментар адміна');
        $response->assertSee('question-reports/report.json');
    }

    public function test_admin_question_report_details_show_filled_answer_preview_and_missing_verb_hint(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 24,
                'uuid' => 'tomorrow-a24-a-great-day',
                'text' => 'Tomorrow {a24} a great day!',
                'answers' => [
                    [
                        'marker' => 'a24',
                        'answer' => null,
                        'option_value' => 'will be',
                        'option_id' => 7,
                        'verb_hint' => null,
                    ],
                ],
                'options' => [
                    ['id' => 7, 'value' => 'will be'],
                    ['id' => 8, 'value' => 'was'],
                ],
                'verb_hints' => [],
            ],
            'issue_types' => ['missing_verb_hint'],
            'issue_labels' => ['Відсутній verb_hint'],
            'comment' => 'Немає підказки.',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('Заповнене питання');
        $response->assertSee('Tomorrow');
        $response->assertSee('will be');
        $response->assertSee('a24');
        $response->assertSee('correct');
        $response->assertSee('Відсутній verb_hint у цьому питанні');
    }

    public function test_admin_question_report_details_enrich_legacy_snapshot_with_current_verb_hint(): void
    {
        $category = Category::create(['name' => 'Verb to Be']);
        $question = Question::create([
            'uuid' => '87-i-a87-sure-this-is-the-right-appr',
            'question' => 'I {a87} sure this is the right approach.',
            'difficulty' => 4,
            'level' => 'B2',
            'category_id' => $category->id,
            'seeder' => 'Database\\Seeders\\ReportedSeeder',
        ]);
        $answerOption = QuestionOption::create(['option' => 'am not']);
        $hintOption = QuestionOption::create(['option' => 'I + am not (hedging)']);
        $question->options()->attach([$answerOption->id]);
        QuestionAnswer::create([
            'question_id' => $question->id,
            'marker' => 'a87',
            'option_id' => $answerOption->id,
        ]);
        DB::table('verb_hints')->insert([
            'question_id' => $question->id,
            'marker' => 'a87',
            'option_id' => $hintOption->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 35207,
                'uuid' => '87-i-a87-sure-this-is-the-right-appr',
                'text' => 'I {a87} sure this is the right approach.',
                'answers' => [
                    ['marker' => 'a87', 'value' => 'am not'],
                ],
                'options' => ['am not', "isn't", "aren't"],
            ],
            'issue_types' => ['correct_answer_in_verb_hint'],
            'issue_labels' => ['Правильна відповідь показана у verb_hint'],
            'comment' => 'Не повинно не бути правильної відповіді в verb_hint',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'));

        $response->assertOk();
        $response->assertSee('Заповнене питання');
        $response->assertSee('Debug info питання');
        $response->assertSee('I + am not (hedging)');
        $response->assertSee('answer: am not');
        $response->assertDontSee('Відсутній verb_hint у цьому питанні');
        $response->assertDontSee('Verb_hint не збережено у snapshot цього report');
    }

    public function test_admin_can_mark_question_report_as_fixed(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'comment' => 'Коментар адміна',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->patch(route('question-reports.status', 'report'), [
                'status' => 'fixed',
            ]);

        $response->assertRedirect(route('question-reports.index'));
        $response->assertSessionHas('status', 'Репорт позначено як виправлений.');

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame('fixed', $payload['status']);
        $this->assertArrayHasKey('fixed_at', $payload);
        $this->assertArrayHasKey('fixed_by', $payload);

        $this->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'))
            ->assertOk()
            ->assertSee('Редагувати report')
            ->assertSee('Що не так із питанням?')
            ->assertSee('Правильна відповідь показана у verb_hint')
            ->assertSee('Виправлено');
    }

    public function test_admin_can_mark_all_db_changed_question_reports_as_fixed(): void
    {
        [$changedQuestion, $sameQuestion] = Question::withoutEvents(fn () => [
            Question::create([
                'uuid' => 'changed-question',
                'question' => 'Current question text',
                'difficulty' => 1,
            ]),
            Question::create([
                'uuid' => 'same-question',
                'question' => 'Same question text',
                'difficulty' => 1,
            ]),
        ]);

        Storage::disk('local')->put('question-reports/changed-report.json', json_encode([
            'id' => 'changed-report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => $changedQuestion->id,
                'uuid' => $changedQuestion->uuid,
                'text' => 'Legacy changed text',
            ],
            'question_snapshot' => [
                'snapshot_source' => 'report_time_db_state',
                'question' => [
                    'id' => $changedQuestion->id,
                    'uuid' => $changedQuestion->uuid,
                    'text' => 'Original question text',
                ],
                'answers' => [],
                'options' => [],
                'verb_hints' => [],
                'hints' => [],
                'tags' => [],
                'saved_tests' => [],
            ],
            'comment' => 'Changed in DB',
            'file' => 'question-reports/changed-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Storage::disk('local')->put('question-reports/same-report.json', json_encode([
            'id' => 'same-report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => $sameQuestion->id,
                'uuid' => $sameQuestion->uuid,
                'text' => $sameQuestion->question,
            ],
            'question_snapshot' => [
                'snapshot_source' => 'report_time_db_state',
                'question' => [
                    'id' => $sameQuestion->id,
                    'uuid' => $sameQuestion->uuid,
                    'text' => $sameQuestion->question,
                ],
                'answers' => [],
                'options' => [],
                'verb_hints' => [],
                'hints' => [],
                'tags' => [],
                'saved_tests' => [],
            ],
            'comment' => 'No DB changes',
            'file' => 'question-reports/same-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Storage::disk('local')->put('question-reports/already-fixed-report.json', json_encode([
            'id' => 'already-fixed-report',
            'status' => 'fixed',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => $changedQuestion->id,
                'uuid' => $changedQuestion->uuid,
                'text' => 'Legacy fixed text',
            ],
            'question_snapshot' => [
                'snapshot_source' => 'report_time_db_state',
                'question' => [
                    'id' => $changedQuestion->id,
                    'uuid' => $changedQuestion->uuid,
                    'text' => 'Another original text',
                ],
                'answers' => [],
                'options' => [],
                'verb_hints' => [],
                'hints' => [],
                'tags' => [],
                'saved_tests' => [],
            ],
            'comment' => 'Already fixed',
            'file' => 'question-reports/already-fixed-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->post(route('question-reports.fix-db-changed'));

        $response->assertRedirect(route('question-reports.index'));
        $response->assertSessionHas('status', 'Позначено виконаними репортів зі змінами в БД: 1.');

        $changedPayload = json_decode(Storage::disk('local')->get('question-reports/changed-report.json'), true);
        $samePayload = json_decode(Storage::disk('local')->get('question-reports/same-report.json'), true);
        $alreadyFixedPayload = json_decode(Storage::disk('local')->get('question-reports/already-fixed-report.json'), true);

        $this->assertSame('fixed', $changedPayload['status']);
        $this->assertArrayHasKey('fixed_at', $changedPayload);
        $this->assertArrayHasKey('fixed_by', $changedPayload);
        $this->assertArrayNotHasKey('snapshot_diff', $changedPayload);
        $this->assertSame('open', $samePayload['status']);
        $this->assertSame('fixed', $alreadyFixedPayload['status']);
        $this->assertArrayNotHasKey('fixed_at', $alreadyFixedPayload);
    }

    public function test_admin_can_edit_question_report_issue_types_without_comment(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
                'seeder' => ['class' => 'Database\\Seeders\\ReportedSeeder'],
            ],
            'issue_types' => ['missing_verb_hint'],
            'comment' => 'Old comment',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->patch(route('question-reports.update', 'report'), [
                'issue_types' => ['wrong_accepted_answer', 'wrong_or_extra_option'],
                'comment' => '',
            ]);

        $response->assertRedirect(route('question-reports.index'));
        $response->assertSessionHas('status', 'Репорт оновлено.');

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame(['wrong_accepted_answer', 'wrong_or_extra_option'], $payload['issue_types']);
        $this->assertSame(['Неправильна правильна відповідь', 'Неправильний або зайвий варіант відповіді'], $payload['issue_labels']);
        $this->assertSame('', $payload['comment']);
        $this->assertSame('reported-question', $payload['question']['uuid']);
        $this->assertArrayHasKey('edited_at', $payload);
        $this->assertArrayHasKey('edited_by', $payload);
    }

    public function test_admin_can_edit_question_report_comment_without_issue_types(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'issue_types' => ['missing_verb_hint'],
            'comment' => '',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->patch(route('question-reports.update', 'report'), [
                'comment' => 'Only comment after edit.',
            ]);

        $response->assertRedirect(route('question-reports.index'));

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame([], $payload['issue_types']);
        $this->assertSame([], $payload['issue_labels']);
        $this->assertSame('Only comment after edit.', $payload['comment']);
    }

    public function test_admin_can_edit_question_report_over_ajax_without_page_reload_contract(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'test' => ['slug' => 'reported-test'],
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
                'seeder' => [
                    'class' => 'Database\\Seeders\\ReportedSeeder',
                    'file' => 'database/seeders/ReportedSeeder.php',
                ],
            ],
            'issue_types' => ['missing_verb_hint'],
            'comment' => 'Original comment.',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->patchJson(route('question-reports.update', 'report'), [
                'issue_types' => ['other'],
                'comment' => 'Ajax comment.',
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Репорт оновлено.');
        $response->assertJsonPath('report.id', 'report');
        $response->assertJsonPath('report.issue_types.0', 'other');
        $response->assertJsonPath('report.issue_labels.0', 'Інше');
        $response->assertJsonPath('report.comment', 'Ajax comment.');
        $response->assertJsonPath('report.question_uuid', 'reported-question');
        $response->assertJsonPath('report.seeder.class', 'Database\\Seeders\\ReportedSeeder');

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame(['other'], $payload['issue_types']);
        $this->assertSame(['Інше'], $payload['issue_labels']);
        $this->assertSame('Ajax comment.', $payload['comment']);
        $this->assertArrayHasKey('edited_at', $payload);
        $this->assertArrayHasKey('edited_by', $payload);
    }

    public function test_admin_ajax_report_edit_persists_browser_form_issue_types(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'issues' => ['missing_verb_hint'],
            'issue_types' => ['missing_verb_hint'],
            'comment' => 'Original comment.',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post(route('question-reports.update', 'report'), [
                '_method' => 'PATCH',
                'issue_types' => ['wrong_accepted_answer', 'other'],
                'comment' => '',
            ]);

        $response->assertOk();
        $response->assertJsonPath('report.issue_types.0', 'wrong_accepted_answer');
        $response->assertJsonPath('report.issue_types.1', 'other');

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame(['wrong_accepted_answer', 'other'], $payload['issue_types']);
        $this->assertSame(['Неправильна правильна відповідь', 'Інше'], $payload['issue_labels']);
        $this->assertSame('', $payload['comment']);
        $this->assertArrayNotHasKey('issues', $payload);
    }

    public function test_admin_ajax_report_edit_returns_validation_errors(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'issue_types' => ['missing_verb_hint'],
            'comment' => 'Original comment.',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->patchJson(route('question-reports.update', 'report'), [
                'comment' => '',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['issue_types']);

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame(['missing_verb_hint'], $payload['issue_types']);
        $this->assertSame('Original comment.', $payload['comment']);
        $this->assertArrayNotHasKey('edited_at', $payload);
    }

    public function test_admin_report_edit_requires_issue_type_or_comment(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'issue_types' => ['missing_verb_hint'],
            'comment' => 'Original comment.',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->from(route('question-reports.index'))
            ->patch(route('question-reports.update', 'report'), [
                'comment' => '',
            ]);

        $response->assertRedirect(route('question-reports.index'));
        $response->assertSessionHasErrors(['issue_types']);

        $payload = json_decode(Storage::disk('local')->get('question-reports/report.json'), true);

        $this->assertSame(['missing_verb_hint'], $payload['issue_types']);
        $this->assertSame('Original comment.', $payload['comment']);
        $this->assertArrayNotHasKey('edited_at', $payload);
    }

    public function test_admin_can_delete_question_report_file(): void
    {
        Storage::disk('local')->put('question-reports/report.json', json_encode([
            'id' => 'report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'question' => [
                'id' => 10,
                'uuid' => 'reported-question',
                'text' => 'Bad question text',
            ],
            'comment' => 'Коментар адміна',
            'file' => 'question-reports/report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $response = $this->withSession(['admin_authenticated' => true])
            ->delete(route('question-reports.destroy', 'report'));

        $response->assertRedirect(route('question-reports.index'));
        $response->assertSessionHas('status', 'Репорт видалено.');

        $this->assertFalse(Storage::disk('local')->exists('question-reports/report.json'));

        $this->withSession(['admin_authenticated' => true])
            ->get(route('question-reports.index'))
            ->assertOk()
            ->assertDontSee('Bad question text')
            ->assertSee('Репортів ще немає');
    }

    public function test_admin_can_generate_fix_prompt_from_open_or_selected_reports(): void
    {
        Storage::disk('local')->put('question-reports/open-report.json', json_encode([
            'id' => 'open-report',
            'status' => 'open',
            'reported_at' => '2026-05-04T12:00:00+03:00',
            'test' => ['slug' => 'open-test', 'url' => 'http://localhost/test/open'],
            'question' => [
                'id' => 10,
                'uuid' => 'open-question',
                'text' => 'Open question',
                'level' => 'A1',
                'category' => 'Polyglot',
                'source' => ['name' => 'Open source'],
                'seeder' => [
                    'class' => 'Database\\Seeders\\OpenSeeder',
                    'file' => 'database/seeders/OpenSeeder.php',
                ],
                'answers' => [['marker' => 'a1', 'value' => 'open answer']],
                'options' => ['open answer', 'other answer'],
            ],
            'comment' => 'Open report comment',
            'file' => 'question-reports/open-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Storage::disk('local')->put('question-reports/fixed-report.json', json_encode([
            'id' => 'fixed-report',
            'status' => 'fixed',
            'reported_at' => '2026-05-04T13:00:00+03:00',
            'question' => [
                'id' => 11,
                'uuid' => 'fixed-question',
                'text' => 'Fixed question',
                'seeder' => ['class' => 'Database\\Seeders\\FixedSeeder'],
            ],
            'comment' => 'Fixed report comment',
            'file' => 'question-reports/fixed-report.json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $openPromptResponse = $this->withSession(['admin_authenticated' => true])
            ->post(route('question-reports.prompt'), [
                'scope' => 'open',
            ]);

        $openPromptResponse->assertOk();
        $content = $openPromptResponse->getContent();
        $this->assertStringContainsString('Сформований prompt', $content);
        $this->assertStringContainsString('Питання: Open question', $content);
        $this->assertStringNotContainsString('Питання: Fixed question', $content);

        $selectedPromptResponse = $this->withSession(['admin_authenticated' => true])
            ->post(route('question-reports.prompt'), [
                'scope' => 'selected',
                'report_ids' => ['fixed-report'],
            ]);

        $selectedPromptResponse->assertOk();
        $this->assertStringContainsString('Питання: Fixed question', $selectedPromptResponse->getContent());
    }

    public function test_guest_cannot_store_question_report(): void
    {
        $response = $this->postJson(route('question-reports.store'), [
            'question_uuid' => (string) Str::uuid(),
            'comment' => 'Hidden from guests.',
        ]);

        $response->assertRedirect(route('login.show'));
    }

    private function ensureSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'question_option_question',
            'verb_hints',
            'question_answers',
            'question_options',
            'questions',
            'sources',
            'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->text('question');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('level', 2)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedTinyInteger('flag')->default(0);
            $table->string('type')->nullable();
            $table->json('options_by_marker')->nullable();
            $table->string('theory_text_block_uuid', 36)->nullable();
            $table->string('seeder')->nullable();
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->string('option')->unique();
            $table->timestamps();
        });

        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->string('marker');
            $table->timestamps();
        });

        Schema::create('verb_hints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('marker');
            $table->unsignedBigInteger('option_id');
            $table->timestamps();
        });

        Schema::create('question_option_question', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id');
            $table->tinyInteger('flag')->nullable();
        });
    }

    private function resetData(): void
    {
        foreach ([
            'question_option_question',
            'verb_hints',
            'question_answers',
            'question_options',
            'questions',
            'sources',
            'categories',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}
