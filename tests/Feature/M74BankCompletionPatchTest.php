<?php

namespace Tests\Feature;

use App\Services\M74BankCompletionPatch as Patch;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M74QuestionSupplement as Supplement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Synthetic SQLite writer tests; not a substitute for proven local MySQL or live acceptance. */
class M74BankCompletionPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $scope;
    private array $transition;
    private array $pins;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());

        $this->root = storage_path('app/m74-bank-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);

        $previous = json_decode(File::get(base_path(Patch::PREVIOUS_PATCH)), true, flags: JSON_THROW_ON_ERROR);
        $old = $previous['targets'][0]['after']['page']['blocks'][6]['body'];
        $next = json_decode($old, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([Supplement::OLD_BANK], $next['linked_practice']['seeder_classes']);
        $next['linked_practice']['seeder_classes'][] = Supplement::BANK;
        $new = json_encode($next, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        self::assertSame('aea17bc05f0b8c77598e75e2d70b8054f12a0e23602ee777286e57bba96bec6e', hash('sha256', $old));
        self::assertSame('659bd7d475c905f8c10970526913a64f04026f7f2d470d2de0c51d517be5af68', hash('sha256', $new));
        File::put($this->root.'/teaching.json', json_encode(['before' => $old, 'after' => $new], JSON_THROW_ON_ERROR));
        $this->transition = ['before' => $old, 'after' => $new,
            'sources' => ['teaching.json' => hash_file('sha256', $this->root.'/teaching.json')]];

        $time = '2026-01-01 12:00:00';
        DB::table('page_categories')->insert(['id' => 16, 'title' => 'Synthetic category', 'slug' => 'synthetic-category',
            'type' => 'theory', 'language' => 'uk', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('pages')->insert(['id' => 81, 'page_category_id' => 16, 'slug' => 'another-other-the-other',
            'title' => 'Another / Other / The Other', 'text' => 'Unchanged subtitle.', 'type' => 'theory',
            'seeder' => Supplement::OWNER, 'created_at' => $time, 'updated_at' => $time]);
        $practice = ['id' => Patch::PRACTICE_ID, 'uuid' => Supplement::PRACTICE_UUID,
            'page_id' => 81, 'page_category_id' => 16, 'locale' => 'uk', 'type' => 'practice-set',
            'level' => 'B1', 'column' => 'left', 'heading' => null, 'css_class' => null, 'sort_order' => 7,
            'body' => $old, 'seeder' => Supplement::OWNER, 'created_at' => $time, 'updated_at' => $time];
        DB::table('text_blocks')->insert($practice);
        foreach (['uk', 'en', 'pl'] as $index => $locale) {
            $sentinel = $practice;
            $sentinel['id'] = 13000 + $index; $sentinel['uuid'] = 'm74-bank-sentinel-'.$locale;
            $sentinel['locale'] = $locale; $sentinel['type'] = 'box'; $sentinel['sort_order'] = $index;
            $sentinel['body'] = 'Unchanged '.$locale.' teaching sentinel.';
            DB::table('text_blocks')->insert($sentinel);
        }
        DB::table('categories')->insert(['id' => Supplement::CATEGORY_ID, 'name' => 'Synthetic question category', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('sources')->insert(['id' => Supplement::SOURCE_ID, 'name' => 'Synthetic exact taxonomy', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('questions')->insert(['id' => 32707, 'uuid' => 'aot-poly-b1-05',
            'question' => 'Unchanged original source condition.', 'difficulty' => 3, 'level' => 'B1',
            'category_id' => Supplement::CATEGORY_ID, 'source_id' => Supplement::SOURCE_ID,
            'flag' => 0, 'type' => '4', 'options_by_marker' => null, 'theory_text_block_uuid' => null,
            'seeder' => Supplement::OLD_BANK, 'created_at' => $time, 'updated_at' => $time]);
        $matches = [];
        foreach ([...Supplement::TOKENS, ...Supplement::DISTRACTORS] as $index => $token) {
            // Existing storage casing is retained; the unchanged native normalizer capitalizes the first token.
            $row = ['id' => 100 + $index, 'option' => $token === 'The' ? 'the' : $token,
                'created_at' => $time, 'updated_at' => $time];
            DB::table('question_options')->insert($row); $matches[$token] = [$row];
            DB::table('question_option_question')->insert(['id' => 200 + $index, 'question_id' => 32707, 'option_id' => $row['id'], 'flag' => null]);
            if ($index < 4) {
                DB::table('question_answers')->insert(['id' => 300 + $index, 'question_id' => 32707,
                    'option_id' => $row['id'], 'marker' => 'a'.($index + 1), 'created_at' => $time, 'updated_at' => $time]);
            }
        }
        DB::table('question_theory_text_blocks')->insert(['id' => 400, 'question_uuid' => 'aot-poly-b1-05',
            'text_block_uuid' => Supplement::PRACTICE_UUID, 'position' => 1, 'created_at' => $time, 'updated_at' => $time]);
        DB::table('saved_grammar_tests')->insert(['id' => 1, 'uuid' => 'm74-bank-saved-test', 'name' => 'Protected saved test',
            'slug' => 'm74-bank-saved-test', 'filters' => '{}', 'description' => 'Unchanged.', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('saved_grammar_test_questions')->insert(['id' => 1, 'saved_grammar_test_id' => 1,
            'question_uuid' => 'aot-poly-b1-05', 'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
        DB::connection()->getSchemaBuilder()->create('m74_bank_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value'); $table->timestamps();
        });
        DB::table('m74_bank_protected_progress')->insert(['id' => 1, 'value' => 'Unchanged synthetic progress.', 'created_at' => $time, 'updated_at' => $time]);
        // Exercise the all-table protection with exactly 46 owned fixture tables, not a working-database dump.
        for ($n = 0; count($this->tables()) < 46; ++$n) {
            $name = 'm74_bank_protected_fixture_'.$n;
            DB::connection()->getSchemaBuilder()->create($name, static function (Blueprint $table): void { $table->id(); $table->text('value'); });
            DB::table($name)->insert(['id' => 1, 'value' => 'Synthetic immutable field '.$n]);
        }
        self::assertCount(46, $this->tables());

        $source = Supplement::load(base_path());
        $binding = ['owner' => Supplement::OWNER, 'level' => 'B1', 'category_id' => Supplement::CATEGORY_ID,
            'source_id' => Supplement::SOURCE_ID, 'practice_uuid' => Supplement::PRACTICE_UUID];
        $projection = Supplement::project($source, $binding, $matches);
        $schema = $allocation = $ids = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $schema[$table] = DB::connection()->getSchemaBuilder()->getColumns($table);
            $max = (int) (DB::table($table)->max('id') ?? 0);
            $allocation[$table] = ['max_id' => $max, 'next_id' => $max + 1]; $ids[$table] = [];
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $ids[$table][$key] = $max + 1 + $offset; }
        }
        $timestamp = '2026-10-21 12:00:00'; $expected = Rows::materialize($projection, $ids, $timestamp);
        $fingerprints = Patch::fingerprints(DB::connection());
        $inventory = ['binding' => $binding, 'option_matches' => $matches, 'schema' => $schema, 'allocation' => $allocation,
            'sql_before' => $fingerprints, 'taxonomy_question' => (array) DB::table('questions')->where('id', 32707)->sole(),
            'practice_before' => (array) DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->sole(),
            'projection' => $projection, 'materialized' => $expected, 'ids' => $ids, 'row_timestamp' => $timestamp];
        $evidence = ['projection' => $projection, 'materialized' => $expected, 'ids' => $ids, 'row_timestamp' => $timestamp];
        $baseline = ['sql_after' => $fingerprints];
        $this->scope = compact('source', 'inventory', 'evidence', 'baseline', 'projection', 'expected');
        $this->pins = [];
        foreach (['fixture-inventory' => $inventory, 'fixture-question-plan' => $evidence, 'fixture-baseline' => $baseline] as $name => $data) {
            File::put($this->file($name), json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $this->pins['private:'.$name.'.json'] = hash_file('sha256', $this->file($name));
        }
    }

    private function tables(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables); return $tables;
    }

    private function service(): M74SyntheticBankCompletionPatch
    {
        return new M74SyntheticBankCompletionPatch(DB::connection(), $this->root.'/database', $this->private,
            scope: $this->scope, transition: $this->transition, pins: $this->pins);
    }

    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = [];
        foreach ($this->tables() as $table) {
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table); $query = DB::table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refused(callable $call, ?string $message = null): void
    {
        $error = null; try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
        if ($message !== null) { self::assertStringContainsString($message, $error->getMessage()); }
    }

    private function apply(M74SyntheticBankCompletionPatch $service): void
    {
        $service->savePlan($this->file('preview'));
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
    }

    public function test_preview_is_read_only_and_contains_one_body_update_and_exact_fourteen_row_family(): void
    {
        $before = $this->snapshot(); $plan = $this->service()->plan();
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(46, $plan['protected']); self::assertCount(1, $plan['updates']);
        self::assertSame([], $plan['inserts']); self::assertSame([], $plan['deletes'] ?? []);
        self::assertSame(['body'], array_keys($plan['updates'][0]['before']));
        self::assertSame(['body'], array_keys($plan['updates'][0]['after']));
        self::assertSame(Patch::PRACTICE_ID, $plan['updates'][0]['id']);
        self::assertSame(Supplement::PRACTICE_UUID, $plan['updates'][0]['uuid']);
        self::assertSame(['questions' => 1, 'question_options' => 0, 'question_answers' => 4,
            'question_option_question' => 8, 'question_theory_text_blocks' => 1], $plan['question_family_counts']);
        self::assertSame(14, array_sum($plan['question_family_counts']));
        self::assertCount(8, $this->scope['projection']['reused_options']);
        self::assertSame($this->scope['expected'], $plan['question_rows']);
        self::assertSame(Supplement::BANK, $plan['question_rows']['questions'][0]['seeder']);
        self::assertSame('4', $plan['question_rows']['questions'][0]['type']);
        self::assertSame('B1', $plan['question_rows']['questions'][0]['level']);
        self::assertSame(45, $plan['question_rows']['questions'][0]['category_id']);
        self::assertSame(321, $plan['question_rows']['questions'][0]['source_id']);
    }

    public function test_transaction_changes_only_bank_list_and_adds_complete_family_then_old_and_new_plans_are_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame(['status' => 'applied', 'updated' => 1, 'inserted' => 0, 'backup' => $this->file('backup')], $result);
        $after = $this->snapshot(); $normalized = $after;
        foreach ($normalized['text_blocks'] as &$row) {
            if ($row['id'] === Patch::PRACTICE_ID) { self::assertSame($this->transition['after'], $row['body']); $row['body'] = $this->transition['before']; }
        }
        unset($row);
        foreach ($this->scope['expected'] as $table => $inserted) {
            foreach ($inserted as $row) { self::assertContains($row, $after[$table]); }
            $ids = array_column($inserted, 'id');
            $normalized[$table] = array_values(array_filter($normalized[$table], static fn ($row) => !in_array($row['id'], $ids, true)));
            self::assertCount(count($before[$table]) + count($inserted), $after[$table]);
        }
        self::assertSame($before, $normalized, 'All 46 original table rows, fields, casing, metadata and timestamps remain exact.');
        $afterPlan = $service->savePlan($this->file('after'));
        self::assertSame('after', $afterPlan['state']); self::assertSame([], $afterPlan['updates']);
        self::assertSame($plan['protected'], $afterPlan['protected']);
        $backup = File::get($this->file('backup'));
        foreach (['preview', 'after'] as $name) {
            self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0],
                $service->apply($this->file($name), $this->file('unused-'.$name)));
            self::assertFileDoesNotExist($this->file('unused-'.$name));
        }
        self::assertSame($backup, File::get($this->file('backup'))); self::assertSame($after, $this->snapshot());
    }

    public function test_stale_progress_and_every_other_protected_table_refuse_before_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach (['m74_bank_protected_progress', 'm74_bank_protected_fixture_0'] as $table) {
            $old = DB::table($table)->where('id', 1)->value('value');
            DB::table($table)->where('id', 1)->update(['value' => 'Later external state.']); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-'.$table)), 'original question/options/answers/links/taxonomy/progress changed');
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-'.$table));
            DB::table($table)->where('id', 1)->update(['value' => $old]);
        }
    }

    public function test_stale_public_source_and_private_evidence_are_never_refreshed_implicitly(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach ([$this->root.'/teaching.json', $this->file('fixture-question-plan')] as $index => $path) {
            $old = File::get($path); File::put($path, $old."\n"); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source-'.$index)), 'Reviewed bank source changed');
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-source-'.$index));
            File::put($path, $old);
        }
    }

    public function test_practice_uuid_body_scope_level_and_timestamps_are_not_repaired_or_reassigned(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $original = (array) DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->sole();
        foreach (['uuid' => 'm74-foreign-practice', 'body' => $original['body']."\n", 'page_id' => 82,
            'page_category_id' => 20, 'locale' => 'en', 'level' => null, 'column' => 'right', 'sort_order' => 8,
            'type' => 'box', 'seeder' => 'Foreign\\Owner', 'heading' => 'External heading',
            'css_class' => 'external-style', 'updated_at' => '2026-10-21 13:00:00'] as $field => $value) {
            DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->update([$field => $value]); $before = $this->snapshot();
            $name = 'not-created-'.str_replace('_', '-', $field);
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file($name)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file($name));
            DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->update([$field => $original[$field]]);
        }
    }

    public function test_partial_new_family_or_colliding_allocated_row_is_refused_without_writes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach ($this->scope['expected'] as $table => $rows) {
            if ($rows === []) { continue; }
            $row = $rows[0]; DB::table($table)->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-collision-'.$table)), 'collision');
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-collision-'.$table));
            DB::table($table)->where('id', $row['id'])->delete();
        }
        $collision = $this->scope['expected']['questions'][0];
        $collision['uuid'] = 'm74-unrelated-allocated-id'; $collision['seeder'] = 'Foreign\\Bank';
        DB::table('questions')->insert($collision); $before = $this->snapshot();
        $this->refused(fn () => $service->plan(), 'collision'); self::assertSame($before, $this->snapshot());
    }

    public function test_foreign_uuid_or_bank_collision_is_rejected_even_outside_allocated_id(): void
    {
        foreach (['uuid', 'seeder'] as $field) {
            $collision = $this->scope['expected']['questions'][0]; $collision['id'] = 50000;
            if ($field === 'uuid') { $collision['seeder'] = 'Foreign\\Bank'; }
            else { $collision['uuid'] = 'm74-foreign-question-in-new-bank'; }
            DB::table('questions')->insert($collision); $before = $this->snapshot();
            $this->refused(fn () => $this->service()->plan(), 'collision'); self::assertSame($before, $this->snapshot());
            DB::table('questions')->where('id', $collision['id'])->delete();
        }
    }

    public function test_reused_option_literal_case_timestamp_and_reference_taxonomy_drift_refuse(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach ([['question_options', 100, 'option', 'The'], ['question_options', 100, 'updated_at', '2026-10-21 13:00:00'],
            ['questions', 32707, 'category_id', 16], ['questions', 32707, 'difficulty', 1]] as $index => [$table, $id, $field, $value]) {
            $old = DB::table($table)->where('id', $id)->value($field); DB::table($table)->where('id', $id)->update([$field => $value]);
            $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-original-'.$index)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-original-'.$index));
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
    }

    public function test_partial_after_state_and_edited_new_family_do_not_become_noop(): void
    {
        $service = $this->service(); $this->apply($service);
        $qid = $this->scope['expected']['questions'][0]['id'];
        foreach ([['questions', $qid, 'question', 'Later edited condition.'],
            ['questions', $qid, 'updated_at', '2026-10-21 13:00:00'],
            ['question_answers', $this->scope['expected']['question_answers'][0]['id'], 'marker', 'a9'],
            ['question_option_question', $this->scope['expected']['question_option_question'][0]['id'], 'flag', 1],
            ['question_theory_text_blocks', $this->scope['expected']['question_theory_text_blocks'][0]['id'], 'position', 1]] as $index => [$table, $id, $field, $value]) {
            $old = DB::table($table)->where('id', $id)->value($field); DB::table($table)->where('id', $id)->update([$field => $value]);
            $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-after-'.$index)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-after-'.$index));
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
        $answer = $this->scope['expected']['question_answers'][0];
        DB::table('question_answers')->where('id', $answer['id'])->delete(); $before = $this->snapshot();
        $this->refused(fn () => $service->plan(), 'family differs'); self::assertSame($before, $this->snapshot());
        DB::table('question_answers')->insert($answer);
        DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->update(['body' => $this->transition['before']]);
        $before = $this->snapshot(); $this->refused(fn () => $service->plan(), 'collision'); self::assertSame($before, $this->snapshot());
    }

    public function test_successor_body_without_the_complete_family_is_refused(): void
    {
        DB::table('text_blocks')->where('id', Patch::PRACTICE_ID)->update(['body' => $this->transition['after']]);
        $before = $this->snapshot(); $this->refused(fn () => $this->service()->plan(), 'family differs');
        self::assertSame($before, $this->snapshot());
    }

    public function test_injected_failure_at_each_insert_table_rolls_back_body_and_all_new_rows(): void
    {
        foreach (['questions', 'question_answers', 'question_option_question', 'question_theory_text_blocks'] as $table) {
            $service = $this->service(); $service->failAtTable = $table;
            $plan = $service->savePlan($this->file('preview-'.$table)); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview-'.$table), $this->file('backup-'.$table)), 'Injected');
            self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan());
            self::assertFileExists($this->file('backup-'.$table));
            self::assertSame($plan, json_decode(File::get($this->file('backup-'.$table)), true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_exclusive_backup_and_evidence_paths_cannot_overwrite_existing_data(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        File::put($this->file('owned-backup'), 'Existing private backup.'); $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('owned-backup')), 'exclusive');
        self::assertSame('Existing private backup.', File::get($this->file('owned-backup'))); self::assertSame($before, $this->snapshot());
        $preview = File::get($this->file('preview'));
        $this->refused(fn () => $service->savePlan($this->file('preview')), 'exclusive');
        self::assertSame($preview, File::get($this->file('preview')));
        $this->refused(fn () => $service->apply($this->file('preview'), $this->root.'/outside-evidence.json'), 'direct private JSON');
        self::assertFileDoesNotExist($this->root.'/outside-evidence.json'); self::assertSame($before, $this->snapshot());
    }

    public function test_unrelated_future_row_allocation_drift_is_not_silently_replanned(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $foreignQuestion = $this->scope['expected']['questions'][0];
        $foreignQuestion['id'] = 60000; $foreignQuestion['uuid'] = 'm74-unrelated-future-question';
        $foreignQuestion['seeder'] = 'Foreign\\Bank';
        $foreignOption = ['id' => 60000, 'option' => 'unrelated-token',
            'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00'];
        foreach (['questions' => $foreignQuestion, 'question_options' => $foreignOption] as $table => $row) {
            DB::table($table)->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-allocation-'.$table)), 'Reviewed row allocation changed');
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-allocation-'.$table));
            DB::table($table)->where('id', $row['id'])->delete();
        }
    }

    public function test_schema_drift_is_not_silently_replanned(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        DB::connection()->getSchemaBuilder()->table('question_answers', static function (Blueprint $table): void { $table->text('external_column')->nullable(); });
        $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-schema')), 'detailed schema changed');
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-schema'));
    }

    public function test_restore_is_explicitly_outside_scope_and_does_not_touch_sql(): void
    {
        $before = $this->snapshot();
        $this->refused(fn () => $this->service()->restore($this->file('not-existing')), 'outside the approved scope');
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-existing'));
    }
}

/** Only evidence/authority are synthetic; plan, guards, transaction and all SQL mutation stay real. */
class M74SyntheticBankCompletionPatch extends Patch
{
    public ?string $failAtTable = null;

    public function __construct(\Illuminate\Database\Connection $db, string $databasePath, string $privateDirectory,
        private array $scope, private array $transition, private array $pins)
    {
        parent::__construct($db, $databasePath, $privateDirectory);
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        return parent::connection($expected, $writing);
    }

    protected function loadEvidence(): array { return $this->scope; }
    protected function assertTeachingSources(): array { return $this->transition; }
    protected function evidencePins(): array { return $this->pins; }

    protected function afterQuestionRowInsert(string $table, int $index): void
    {
        if ($this->failAtTable === $table && $index === 0) { throw new RuntimeException('Injected isolated question-row insert failure.'); }
    }
}
