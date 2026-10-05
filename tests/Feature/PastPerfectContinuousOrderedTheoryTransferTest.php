<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Services\QuestionExportService;
use App\Services\QuestionImportService;
use App\Support\PpcOrderedTheoryLinks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PastPerfectContinuousOrderedTheoryTransferTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const QUESTION = '381ff955-7329-5a60-9f14-94578615c2ac';
    private const BLOCKS = [
        '68518eb1-6169-536b-9a12-94578615c2ac',
        '78518eb1-6169-536b-9a12-94578615c2ac',
        '88518eb1-6169-536b-9a12-94578615c2ac',
    ];
    private const SEEDER = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
    }

    public static function bankKinds(): array
    {
        return [
            'builder' => [self::SEEDER, '4'],
            'mixed' => ['Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsAllLevelsV3Seeder', '0'],
        ];
    }

    #[DataProvider('bankKinds')]
    public function test_finite_export_import_preserves_all_ordered_links_and_exports_only_the_final_committed_payload(string $seeder, string $type): void
    {
        $question = $this->fixture($seeder, $type);
        $payload = $this->exported($question);
        $this->assertSame($this->links(), $payload[PpcOrderedTheoryLinks::FIELD]);
        $id = $question->id;
        DB::table('saved_grammar_test_questions')->insert([
            'saved_grammar_test_id' => 900001, 'question_uuid' => self::QUESTION, 'position' => 4,
        ]);
        $membership = DB::table('saved_grammar_test_questions')->get()->map(fn ($row) => (array) $row)->all();
        DB::table('question_theory_text_blocks')->where('question_uuid', self::QUESTION)->delete();
        $payload['question']['question'] = 'Оновлена умова після повного перенесення.';
        foreach ($payload['hints'] as &$hint) {
            $hint['hint'] = 'Updated '.$hint['locale'].' source';
        }
        unset($hint);

        $savedEvents = 0;
        Question::saved(static function () use (&$savedEvents): void { $savedEvents++; });
        $restored = $this->import($payload);
        $this->assertSame(0, $savedEvents, 'Finite ordered import must bypass the intermediate synchronous observer export.');
        $this->assertSame($id, $restored->id);
        $this->assertSame(self::QUESTION, $restored->uuid);
        $this->assertSame($this->links(), $this->persistedLinks());
        $this->assertSame(self::BLOCKS[0], $restored->theory_text_block_uuid);
        $this->assertSame($membership, DB::table('saved_grammar_test_questions')->get()->map(fn ($row) => (array) $row)->all());
        $final = json_decode(File::get($this->exportPath()), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($payload['question']['question'], $final['question']['question']);
        $this->assertSame($this->links(), $final[PpcOrderedTheoryLinks::FIELD]);
        $this->assertEquals(['uk' => 'Updated uk source', 'en' => 'Updated en source', 'pl' => 'Updated pl source'], array_column($final['hints'], 'hint', 'locale'));
    }

    public function test_a_new_question_can_opt_in_from_incoming_canonical_seeder_and_prompt_rows(): void
    {
        $payload = $this->exported($this->fixture());
        $newUuid = '481ff955-7329-5a60-9f14-94578615c2ac';
        $payload['question']['uuid'] = $newUuid;
        $restored = $this->import($payload);
        $this->assertSame($newUuid, $restored->uuid);
        $this->assertSame($this->links(), DB::table('question_theory_text_blocks')->where('question_uuid', $newUuid)
            ->orderBy('position')->get(['text_block_uuid', 'position'])->map(fn ($row) => (array) $row)->all());
        $this->assertSame($this->links(), json_decode(File::get(config('questions.export_path').'/'.$newUuid.'.json'), true)[PpcOrderedTheoryLinks::FIELD]);
    }

    public function test_legacy_payload_without_the_new_field_does_not_delete_existing_pivots(): void
    {
        $payload = $this->exported($this->fixture());
        unset($payload[PpcOrderedTheoryLinks::FIELD]);
        foreach (['type', 'seeder', 'options_by_marker', 'theory_text_block_uuid'] as $field) {
            unset($payload['question'][$field]);
        }
        $before = DB::table('question_theory_text_blocks')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $savedEvents = 0;
        Question::saved(static function () use (&$savedEvents): void { $savedEvents++; });
        $this->import($payload);
        $this->assertSame(1, $savedEvents, 'The existing observer path remains unchanged for old exports.');
        $this->assertSame($before, DB::table('question_theory_text_blocks')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all());
    }

    public function test_other_themes_ignore_the_additive_field_and_never_gain_it_on_export(): void
    {
        $question = $this->fixture('Database\\Seeders\\V3\\Polyglot\\PolyglotPresentPerfectContinuousBasicsLessonSeeder');
        $payload = $this->exported($question);
        $this->assertArrayNotHasKey(PpcOrderedTheoryLinks::FIELD, $payload);
        $payload[PpcOrderedTheoryLinks::FIELD] = [['text_block_uuid' => 'not-a-block', 'position' => 99]];
        $before = $this->persistedLinks();
        $this->import($payload);
        $this->assertSame($before, $this->persistedLinks());
        $this->assertArrayNotHasKey(PpcOrderedTheoryLinks::FIELD, json_decode(File::get($this->exportPath()), true));
    }

    public function test_incoming_prompt_provider_is_required_even_when_the_existing_question_was_opted_in(): void
    {
        $payload = $this->exported($this->fixture());
        foreach ($payload['hints'] as &$hint) {
            $hint['provider'] = 'legacy-hint';
        }
        unset($hint);
        $payload[PpcOrderedTheoryLinks::FIELD] = [['text_block_uuid' => 'not-a-block', 'position' => 99]];
        $before = $this->persistedLinks();
        $savedEvents = 0;
        Question::saved(static function () use (&$savedEvents): void { $savedEvents++; });
        $restored = $this->import($payload);
        $this->assertSame(1, $savedEvents, 'Stored opt-in hints must not grant incoming payload eligibility.');
        $this->assertSame($before, $this->persistedLinks());
        $this->assertArrayNotHasKey(PpcOrderedTheoryLinks::FIELD, $this->exported($restored));
    }

    public static function invalidLinks(): array
    {
        return [
            'missing-block' => ['missing'],
            'duplicate-block' => ['duplicate'],
            'out-of-order' => ['order'],
            'noninteger-position' => ['string-position'],
            'primary-mismatch' => ['primary'],
            'empty-list' => ['empty'],
        ];
    }

    #[DataProvider('invalidLinks')]
    public function test_invalid_links_are_rejected_before_any_category_source_or_filesystem_write(string $failure): void
    {
        $payload = $this->exported($this->fixture());
        $bytes = File::get($this->exportPath());
        $filesBefore = $this->exportFilesSnapshot();
        $before = $this->snapshot();
        $payload['question']['question'] = 'Must never be persisted or exported';
        $payload['question']['category_id'] = null;
        $payload['question']['source_id'] = null;
        $payload['category'] = ['name' => 'Must never create category'];
        $payload['source'] = ['name' => 'Must never create source'];
        switch ($failure) {
            case 'missing':
                $payload[PpcOrderedTheoryLinks::FIELD][2]['text_block_uuid'] = '98518eb1-6169-536b-9a12-94578615c2ac';
                break;
            case 'duplicate':
                $payload[PpcOrderedTheoryLinks::FIELD][2]['text_block_uuid'] = self::BLOCKS[1];
                break;
            case 'order':
                $payload[PpcOrderedTheoryLinks::FIELD] = array_reverse($payload[PpcOrderedTheoryLinks::FIELD]);
                break;
            case 'string-position':
                $payload[PpcOrderedTheoryLinks::FIELD][1]['position'] = '1';
                break;
            case 'primary':
                $payload['question']['theory_text_block_uuid'] = self::BLOCKS[1];
                break;
            case 'empty':
                $payload[PpcOrderedTheoryLinks::FIELD] = [];
                break;
        }
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        try {
            $this->import($payload);
            $this->fail('Invalid finite link payload must be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('PPC', $exception->getMessage());
        }
        $writes = array_filter(DB::connection()->getQueryLog(), fn ($query) => preg_match('/^\s*(insert|update|delete)\b/i', $query['query']));
        $this->assertSame([], $writes, 'Validation must occur before any category/source/question write.');
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($bytes, File::get($this->exportPath()));
        $this->assertSame($filesBefore, $this->exportFilesSnapshot());
    }

    public function test_missing_link_schema_fails_without_mutating_question_or_export(): void
    {
        $payload = $this->exported($this->fixture());
        $bytes = File::get($this->exportPath());
        $before = Question::first()->getAttributes();
        Schema::drop('question_theory_text_blocks');
        $payload['question']['question'] = 'Rejected schema update';
        try {
            $this->import($payload);
            $this->fail('Missing schema must not silently discard ordered links.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('schema', $exception->getMessage());
        }
        $this->assertSame($before, Question::first()->getAttributes());
        $this->assertSame($bytes, File::get($this->exportPath()));
    }

    public function test_incoming_optin_cannot_silently_skip_transfer_when_a_required_question_column_is_missing(): void
    {
        $payload = $this->exported($this->fixture());
        $bytes = File::get($this->exportPath());
        Schema::table('questions', fn ($table) => $table->dropColumn('seeder'));
        $before = $this->snapshot();
        $payload['question']['question'] = 'Must not bypass schema validation';
        try {
            $this->import($payload);
            $this->fail('Incoming canonical opt-in must validate the schema before an attribute can be skipped.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('schema', $exception->getMessage());
        }
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($bytes, File::get($this->exportPath()));
    }

    public function test_outer_transaction_rollback_never_runs_the_deferred_export(): void
    {
        $payload = $this->exported($this->fixture());
        $bytes = File::get($this->exportPath());
        $before = $this->snapshot();
        $payload['question']['question'] = 'Nested uncommitted update';
        DB::beginTransaction();
        try {
            $this->import($payload);
            $this->assertSame('Nested uncommitted update', Question::first()->question);
            $this->assertSame($bytes, File::get($this->exportPath()), 'Export must wait for the outer commit.');
        } finally {
            DB::rollBack();
        }
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($bytes, File::get($this->exportPath()));
    }

    public function test_export_rejects_a_missing_referenced_block_without_overwriting_the_previous_file(): void
    {
        $question = $this->fixture();
        $this->exported($question);
        $bytes = File::get($this->exportPath());
        DB::table('text_blocks')->where('uuid', self::BLOCKS[2])->delete();
        try {
            app(QuestionExportService::class)->export($question->fresh());
            $this->fail('An orphan pivot must not be silently omitted by relation joins.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Missing referenced', $exception->getMessage());
        }
        $this->assertSame($bytes, File::get($this->exportPath()));
    }

    private function fixture(string $seeder = self::SEEDER, string $type = '4'): Question
    {
        foreach (self::BLOCKS as $uuid) {
            DB::table('text_blocks')->insert(['uuid' => $uuid, 'body' => 'Private test block']);
        }
        $question = Question::withoutEvents(fn () => Question::create([
            'uuid' => self::QUESTION, 'question' => 'Авторська умова.', 'type' => $type,
            'seeder' => $seeder, 'level' => 'A1', 'difficulty' => 1, 'flag' => 0,
            'theory_text_block_uuid' => self::BLOCKS[0], 'options_by_marker' => ['a1' => ['I']],
        ]));
        foreach (['uk', 'en', 'pl'] as $locale) {
            QuestionHint::create(['question_id' => $question->id, 'provider' => 'compose_prompt', 'locale' => $locale, 'hint' => 'Source '.$locale]);
        }
        $option = QuestionOption::create(['option' => 'I']);
        $question->options()->attach($option->id);
        QuestionAnswer::create(['question_id' => $question->id, 'marker' => 'a1', 'option_id' => $option->id]);
        // Insert out of order to prove that position, not insertion id, is authoritative.
        foreach ([2, 0, 1] as $position) {
            DB::table('question_theory_text_blocks')->insert([
                'question_uuid' => self::QUESTION, 'text_block_uuid' => self::BLOCKS[$position], 'position' => $position,
            ]);
        }
        return $question->fresh();
    }

    private function exported(Question $question): array
    {
        app(QuestionExportService::class)->export($question);
        return json_decode(File::get($this->exportPath()), true, flags: JSON_THROW_ON_ERROR);
    }

    private function import(array $payload): Question
    {
        $service = new class extends QuestionImportService {
            public function fromArray(array $payload): Question { return $this->importPayload($payload); }
        };
        return $service->fromArray($payload);
    }

    private function exportPath(): string
    {
        $path = config('questions.export_path').'/'.self::QUESTION.'.json';
        IsolatedTestEnvironment::assertOwnedPath(dirname($path));
        return $path;
    }

    private function links(): array
    {
        return array_map(fn ($uuid, $position): array => ['text_block_uuid' => $uuid, 'position' => $position], self::BLOCKS, array_keys(self::BLOCKS));
    }

    private function persistedLinks(): array
    {
        return DB::table('question_theory_text_blocks')->where('question_uuid', self::QUESTION)->orderBy('position')
            ->get(['text_block_uuid', 'position'])->map(fn ($row) => (array) $row)->all();
    }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach (['questions', 'categories', 'sources', 'question_options', 'question_answers', 'question_hints', 'question_theory_text_blocks'] as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }
        return $snapshot;
    }

    private function exportFilesSnapshot(): array
    {
        $files = [];
        foreach (File::files(config('questions.export_path')) as $file) {
            $files[$file->getFilename()] = hash_file('sha256', $file->getPathname());
        }
        ksort($files);
        return $files;
    }
}
