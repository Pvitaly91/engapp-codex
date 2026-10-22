<?php

namespace Tests\Feature;

use App\Services\M75ContentPatch as Patch;
use App\Support\ComposeTokenCase;
use App\Support\M26DetailPackage;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\M75PronounForms as Package;
use App\Support\M75QuestionSupplement as Supplement;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual M75 insert trait and M26 transaction, with invented SQLite evidence only. */
class M75PronounFormsWriterTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private string $evidenceHash;
    private array $evidence;
    private array $expected;
    private array $source;
    private array $patch;
    private array $matches;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        $this->rebuildComposeTestSchema();
        // SQLite BINARY is not the production option lookup contract. This owned fixture
        // deliberately models case-insensitive uniqueness while preserving stored bytes.
        DB::connection()->getSchemaBuilder()->drop('question_options');
        DB::statement('CREATE TABLE question_options (id INTEGER PRIMARY KEY AUTOINCREMENT, option VARCHAR(255) COLLATE NOCASE NOT NULL UNIQUE, created_at DATETIME NULL, updated_at DATETIME NULL)');

        $sourceRoot = dirname(__DIR__, 2);
        [$before, $this->patch, , $contract] = Package::load($sourceRoot);
        Package::validate($before, $this->patch, Package::load($sourceRoot)[2], $contract);
        $this->source = Supplement::load($sourceRoot);
        $this->root = storage_path('app/m75-writer-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract, $sourceRoot) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy($sourceRoot.'/'.$path, $this->root.'/'.$path);
        }
        foreach (Package::profile()['artifacts'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', $this->root.'/'.$path));
        }

        $oldTime = '2001-02-03 04:05:06';
        DB::table('page_categories')->insert(['id' => 20, 'title' => 'Synthetic pronoun category',
            'slug' => 'zaimennyky-ta-vkazivni-slova', 'language' => 'uk', 'type' => 'theory', 'parent_id' => null,
            'seeder' => 'SyntheticOnly', 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        DB::table('categories')->insert(['id' => Supplement::CATEGORY_ID, 'name' => 'Synthetic question category', 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        DB::table('sources')->insert(['id' => Supplement::SOURCE_ID, 'name' => 'Synthetic thematic source', 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 20,
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'],
                'type' => 'theory', 'seeder' => $target['identity'], 'created_at' => $oldTime, 'updated_at' => $oldTime]);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'],
                ...$old['page']['blocks']];
            $baseId = [1000, 2000, 3913][$i];
            foreach ($configs as $position => $config) {
                DB::table('text_blocks')->insert(['id' => $baseId + $position,
                    'uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 20, 'locale' => 'uk',
                    'type' => $config['type'], 'level' => $config['level'] ?? null, 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
                    'sort_order' => $position, 'body' => $config['body'], 'seeder' => $target['identity'],
                    'created_at' => $oldTime, 'updated_at' => $oldTime]);
            }
            foreach (['en', 'pl'] as $j => $locale) {
                DB::table('text_blocks')->insert(['id' => 8000 + $i * 10 + $j,
                    'uuid' => 'm75-synthetic-nontarget-'.$i.'-'.$locale, 'page_id' => Package::PAGE_IDS[$i],
                    'page_category_id' => 20, 'locale' => $locale, 'type' => 'box', 'level' => null,
                    'column' => 'left', 'heading' => null, 'css_class' => null, 'sort_order' => 90 + $j,
                    'body' => 'Unchanged synthetic '.$locale.' teaching content.', 'seeder' => $target['identity'],
                    'created_at' => $oldTime, 'updated_at' => $oldTime]);
            }
            $bank = $contract['targets'][$i]['bank_seeders'][0];
            $bankPath = 'database/seeders/'.str_replace('\\', '/', substr($bank, strlen('Database\\Seeders\\'))).'/definition.json';
            $bankSource = json_decode(File::get($sourceRoot.'/'.$bankPath), true, flags: JSON_THROW_ON_ERROR);
            foreach (array_slice($bankSource['questions'], 0, 5) as $n => $sourceQuestion) {
                $id = 500000 + $i * 100 + $n;
                $uuid = 'm75-synthetic-old-'.$i.'-'.$n;
                DB::table('questions')->insert(['id' => $id, 'uuid' => $uuid,
                    'question' => $sourceQuestion['question'], 'difficulty' => 2,
                    'level' => $sourceQuestion['level'] ?? 'A1', 'category_id' => Supplement::CATEGORY_ID,
                    'source_id' => Supplement::SOURCE_ID, 'flag' => 0, 'type' => '4', 'options_by_marker' => null,
                    'theory_text_block_uuid' => null, 'seeder' => $bank, 'created_at' => $oldTime, 'updated_at' => $oldTime]);
                DB::table('question_theory_text_blocks')->insert(['id' => 600000 + $i * 100 + $n,
                    'question_uuid' => $uuid, 'text_block_uuid' => Package::PRACTICE_UUIDS[$i],
                    'position' => $n, 'created_at' => $oldTime, 'updated_at' => $oldTime]);
            }
        }
        $this->matches = [];
        $uniqueTokens = array_values(array_unique(array_merge(...array_column($this->source['questions'], 'options'))));
        foreach ($uniqueTokens as $index => $token) {
            $stored = in_array($token, ['Nothing', 'Anyone'], true) ? mb_strtolower($token) : $token;
            $row = ['id' => 100 + $index, 'option' => $stored, 'created_at' => $oldTime, 'updated_at' => $oldTime];
            DB::table('question_options')->insert($row);
            $this->matches[$token] = [$row];
        }
        // One old answer/pivot uses the same shared row as a new question; another
        // reused option is used only by the new families. Both relation cases matter.
        $oldQuestionId = 500200;
        $firstOption = $this->matches['Nothing'][0]['id'];
        DB::table('question_answers')->insert(['id' => 700000, 'question_id' => $oldQuestionId,
            'option_id' => $firstOption, 'marker' => 'a1', 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        DB::table('question_option_question')->insert(['id' => 710000, 'question_id' => $oldQuestionId,
            'option_id' => $firstOption, 'flag' => null]);
        DB::table('saved_grammar_tests')->insert(['id' => 1, 'uuid' => 'm75-synthetic-saved',
            'name' => 'Protected synthetic saved test', 'slug' => 'm75-synthetic-saved', 'filters' => '{}',
            'description' => 'Never broaden this membership.', 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        DB::table('saved_grammar_test_questions')->insert(['id' => 1, 'saved_grammar_test_id' => 1,
            'question_uuid' => 'm75-synthetic-old-2-0', 'position' => 0, 'created_at' => $oldTime, 'updated_at' => $oldTime]);
        DB::connection()->getSchemaBuilder()->create('m75_synthetic_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value'); $table->timestamps();
        });
        DB::table('m75_synthetic_protected_progress')->insert(['id' => 1, 'value' => 'Synthetic immutable progress.',
            'created_at' => $oldTime, 'updated_at' => $oldTime]);

        $projection = Supplement::project($this->source, Supplement::binding(), $this->matches);
        $schema = $allocation = $ids = [];
        foreach (Rows::ROW_COLUMNS as $table => $_) {
            $schema[$table] = DB::connection()->getSchemaBuilder()->getColumns($table);
            $max = (int) (DB::table($table)->max('id') ?? 0);
            $allocation[$table] = ['max_id' => $max, 'next_id' => $max + 1];
            $ids[$table] = [];
            foreach (array_column($projection[$table], 'key') as $offset => $key) { $ids[$table][$key] = $max + 1 + $offset; }
        }
        $timestamp = '2002-03-04 05:06:07';
        $this->expected = Rows::materialize($projection, $ids, $timestamp);
        $capturedPages = [];
        foreach (Package::OWNERS as $i => $owner) {
            $capturedPages[$owner] = [
                'blocks' => DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all(),
                'relations' => ['questions' => DB::table('questions')->where('seeder', $contract['targets'][$i]['bank_seeders'][0])->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all()],
            ];
        }
        $practice = (array) DB::table('text_blocks')->where('id', Supplement::PRACTICE_ID)->sole();
        self::assertSame(Supplement::PRACTICE_UUID, $practice['uuid']);
        $taxonomy = ['categories' => DB::table('categories')->where('id', Supplement::CATEGORY_ID)->get()->map(static fn ($row) => (array) $row)->all(),
            'sources' => DB::table('sources')->where('id', Supplement::SOURCE_ID)->get()->map(static fn ($row) => (array) $row)->all()];
        $reference = (array) DB::table('questions')->where('id', $oldQuestionId)->sole();
        $inventory = ['binding' => Supplement::binding(), 'question_collisions' => array_fill_keys(array_keys(Rows::ROW_COLUMNS), []),
            'projection' => $projection, 'option_matches' => $this->matches, 'ids' => $ids, 'row_timestamp' => $timestamp,
            'materialized' => $this->expected, 'schema' => $schema, 'allocation' => $allocation,
            'practice_before' => $practice, 'taxonomy' => $taxonomy, 'taxonomy_question' => $reference];
        // These are explicitly fabricated runtime objects, not copied private BEFORE/proof files.
        $this->evidence = ['binding' => Supplement::binding(), 'projection' => $projection, 'ids' => $ids,
            'row_timestamp' => $timestamp, 'materialized' => $this->expected, 'taxonomy' => $taxonomy,
            '_inventory' => $inventory, '_before' => ['pages' => $capturedPages]];
        $this->saveSyntheticEvidence();
    }

    private function saveSyntheticEvidence(): void
    {
        File::put($this->file('synthetic-question-evidence'), json_encode($this->evidence, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $this->evidenceHash = hash_file('sha256', $this->file('synthetic-question-evidence'));
    }

    private function service(): M75SyntheticPronounFormsWriter
    {
        return new M75SyntheticPronounFormsWriter(DB::connection(), $this->root.'/database', $this->private, $this->evidenceHash);
    }

    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            $query = DB::table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refused(callable $call, ?string $message = null): void
    {
        $error = null;
        try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
        if ($message !== null) { self::assertStringContainsString($message, $error->getMessage()); }
    }

    private function apply(M75SyntheticPronounFormsWriter $service): array
    {
        $service->savePlan($this->file('preview'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']);
        return $result;
    }

    public function test_preview_is_read_only_and_plans_three_continuing_practices_and_thirty_insert_only_rows(): void
    {
        $before = $this->snapshot(); $plan = $this->service()->plan();
        self::assertSame($before, $this->snapshot());
        self::assertSame('before', $plan['state']);
        self::assertCount(40, $plan['updates']);
        self::assertSame([], $plan['inserts']);
        self::assertSame([], $plan['deletes'] ?? []);
        foreach ($plan['updates'] as $update) {
            self::assertContains($update['table'], ['pages', 'text_blocks']);
            self::assertSame($update['table'] === 'pages' ? ['text'] : ['body'], array_keys($update['before']));
            self::assertSame(array_keys($update['before']), array_keys($update['after']));
        }
        $practiceUpdates = array_values(array_filter($plan['updates'], static fn ($u) => in_array($u['uuid'] ?? null, Package::PRACTICE_UUIDS, true)));
        self::assertCount(3, $practiceUpdates);
        self::assertSame($this->expected, $plan['question_supplement']['expected']);
        self::assertSame(array_fill_keys(array_keys(Rows::ROW_COLUMNS), []), $plan['question_supplement']['actual']);
        self::assertSame(['questions' => 2, 'question_options' => 0, 'question_answers' => 10,
            'question_option_question' => 16, 'question_theory_text_blocks' => 2], array_map(count(...), $this->expected));
        self::assertSame(30, array_sum(array_map(count(...), $this->expected)));
        self::assertCount(15, $plan['question_supplement']['projection']['reused_options']);
        self::assertSame('nothing', $this->matches['Nothing'][0]['option']);
        self::assertSame('anyone', $this->matches['Anyone'][0]['option']);
        self::assertSame(['Nothing', 'is', 'on', 'the', 'shelf'], ComposeTokenCase::normalize(['nothing', 'is', 'on', 'the', 'shelf']));
    }

    public function test_real_transaction_updates_only_allowed_content_inserts_exact_families_and_repeats_as_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot();
        $preview = $service->savePlan($this->file('preview'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame(['status' => 'applied', 'updated' => 40, 'inserted' => 0, 'backup' => $this->file('backup')], $result);
        $after = $this->snapshot(); $normalized = $after;
        foreach ($preview['updates'] as $update) {
            foreach ($normalized[$update['table']] as &$row) {
                if ($row['id'] === $update['id']) {
                    foreach ($update['after'] as $field => $value) { self::assertSame($value, $row[$field]); }
                    $row = array_replace($row, $update['before']);
                }
            }
            unset($row);
        }
        foreach ($this->expected as $table => $inserted) {
            foreach ($inserted as $row) { self::assertContains($row, $after[$table]); }
            $ids = array_column($inserted, 'id');
            $normalized[$table] = array_values(array_filter($normalized[$table], static fn ($row) => !in_array($row['id'], $ids, true)));
            self::assertCount(count($before[$table]) + count($inserted), $after[$table]);
        }
        self::assertSame($before, $normalized, 'All old fields, metadata, timestamps, shared options, consumers and synthetic progress remain exact.');
        self::assertCount(count($before['text_blocks']), $after['text_blocks'], 'No fourth or replacement practice row is inserted.');
        self::assertSame($before['question_options'], $after['question_options']);
        $afterPlan = $service->savePlan($this->file('after'));
        self::assertSame('after', $afterPlan['state']);
        self::assertSame([], $afterPlan['updates']);
        self::assertSame($preview['protected'], $afterPlan['protected']);
        $backup = File::get($this->file('backup'));
        foreach (['preview', 'after'] as $name) {
            self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file($name), $this->file('unused-'.$name)));
            self::assertFileDoesNotExist($this->file('unused-'.$name));
        }
        self::assertSame($backup, File::get($this->file('backup')));
        self::assertSame($after, $this->snapshot());
    }

    public function test_partial_family_and_colliding_allocated_rows_are_not_repaired(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach ($this->expected as $table => $rows) {
            if ($rows === []) { continue; }
            $row = $rows[0]; DB::table($table)->insert($row);
            $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-backup-'.$table)));
            self::assertSame($before, $this->snapshot());
            self::assertFileDoesNotExist($this->file('no-backup-'.$table));
            DB::table($table)->where('id', $row['id'])->delete();
        }
        $collision = $this->expected['questions'][0];
        $collision['uuid'] = 'm75-synthetic-allocated-foreign'; $collision['seeder'] = 'Synthetic\\ForeignBank';
        DB::table('questions')->insert($collision); $before = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($before, $this->snapshot());
    }

    public function test_foreign_uuid_bank_and_theory_link_collisions_are_found_even_below_the_allocation_maximum(): void
    {
        foreach (['uuid', 'bank'] as $kind) {
            $row = $this->expected['questions'][0]; $row['id'] = 9;
            if ($kind === 'uuid') { $row['seeder'] = 'Synthetic\\ForeignBank'; }
            else { $row['uuid'] = 'm75-synthetic-foreign-in-new-bank'; }
            DB::table('questions')->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $this->service()->plan(), 'collision');
            self::assertSame($before, $this->snapshot());
            DB::table('questions')->where('id', 9)->delete();
        }
        $row = $this->expected['question_theory_text_blocks'][0]; $row['id'] = 9;
        DB::table('question_theory_text_blocks')->insert($row); $before = $this->snapshot();
        $this->refused(fn () => $this->service()->plan(), 'collision');
        self::assertSame($before, $this->snapshot());
    }

    public function test_allocation_schema_and_synthetic_inventory_drift_refuse_without_backup_or_replanning(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $foreignQuestion = $this->expected['questions'][0];
        $foreignQuestion['id'] += 10000; $foreignQuestion['uuid'] = 'm75-synthetic-future-allocation';
        $foreignQuestion['seeder'] = 'Synthetic\\ForeignBank';
        $foreignOption = ['id' => 990000, 'option' => 'synthetic-future-option', 'created_at' => '2001-02-03 04:05:06', 'updated_at' => '2001-02-03 04:05:06'];
        foreach (['questions' => $foreignQuestion, 'question_options' => $foreignOption] as $table => $row) {
            DB::table($table)->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-allocation-'.$table)), 'allocation');
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-allocation-'.$table));
            DB::table($table)->where('id', $row['id'])->delete();
        }
        $original = $this->evidence;
        $this->evidence['_inventory']['allocation']['questions']['next_id']++;
        $this->saveSyntheticEvidence(); $before = $this->snapshot();
        $this->refused(fn () => $this->service()->plan(), 'allocation');
        self::assertSame($before, $this->snapshot());
        $this->evidence = $original; $this->saveSyntheticEvidence();
        DB::connection()->getSchemaBuilder()->table('question_answers', static function (Blueprint $table): void { $table->text('synthetic_unapproved_column')->nullable(); });
        $before = $this->snapshot();
        $this->refused(fn () => $this->service()->plan(), 'schema');
        self::assertSame($before, $this->snapshot());
    }

    public function test_reused_option_case_punctuation_timestamps_and_existing_taxonomy_are_exact_not_fuzzy(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $nothing = $this->matches['Nothing'][0]['id']; $can = $this->matches['can'][0]['id'];
        foreach ([
            ['question_options', $nothing, 'option', 'Nothing'],
            ['question_options', $nothing, 'option', 'nothing.'],
            ['question_options', $can, 'option', 'Can'],
            ['question_options', $nothing, 'updated_at', '2001-02-03 04:05:07'],
            ['categories', Supplement::CATEGORY_ID, 'name', 'Later category'],
            ['sources', Supplement::SOURCE_ID, 'updated_at', '2001-02-03 04:05:07'],
            ['questions', 500200, 'difficulty', 3],
            ['questions', 500200, 'category_id', 99],
        ] as $n => [$table, $id, $field, $value]) {
            $old = DB::table($table)->where('id', $id)->value($field);
            DB::table($table)->where('id', $id)->update([$field => $value]);
            $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-original-'.$n)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-original-'.$n));
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
        $bad = $this->matches; $bad['can'][0]['option'] = 'Can';
        $this->refused(fn () => Supplement::project($this->source, Supplement::binding(), $bad), 'case');
        $bad = $this->matches; $bad['Nothing'][0]['option'] = 'nothing.';
        $this->refused(fn () => Supplement::project($this->source, Supplement::binding(), $bad), 'collision');
        $bad = $this->matches; $bad['Nothing'][] = $bad['Nothing'][0];
        $this->refused(fn () => Supplement::project($this->source, Supplement::binding(), $bad), 'ambiguous');
    }

    public function test_continuing_practice_metadata_and_body_cannot_be_reassigned_or_partially_applied(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $original = (array) DB::table('text_blocks')->where('id', Supplement::PRACTICE_ID)->sole();
        foreach ([
            'uuid' => 'm75-synthetic-foreign-practice', 'page_id' => 123, 'page_category_id' => 16,
            'locale' => 'en', 'type' => 'box', 'level' => 'A2', 'column' => 'right', 'sort_order' => 12,
            'seeder' => 'Synthetic\\ForeignOwner', 'heading' => 'Later heading', 'css_class' => 'later-style',
            'body' => $original['body']."\n", 'updated_at' => '2001-02-03 04:05:07',
        ] as $field => $value) {
            DB::table('text_blocks')->where('id', Supplement::PRACTICE_ID)->update([$field => $value]);
            $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-practice-'.$field)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-practice-'.$field));
            DB::table('text_blocks')->where('id', Supplement::PRACTICE_ID)->update([$field => $original[$field]]);
        }
        $body = $this->patch['targets'][2]['after']['page']['blocks'][10]['body'];
        DB::table('text_blocks')->where('id', Supplement::PRACTICE_ID)->update(['body' => $body]);
        $before = $this->snapshot(); $this->refused(fn () => $service->plan());
        self::assertSame($before, $this->snapshot());
    }

    public function test_exact_after_content_without_both_complete_question_families_is_not_noop(): void
    {
        $service = $this->service(); $plan = $service->plan();
        foreach ($plan['updates'] as $update) { DB::table($update['table'])->where('id', $update['id'])->update($update['after']); }
        $before = $this->snapshot();
        $this->refused(fn () => $service->plan());
        self::assertSame($before, $this->snapshot());
    }

    public function test_edited_or_missing_after_rows_and_unapproved_relations_never_become_noop(): void
    {
        $service = $this->service(); $this->apply($service);
        $qid = $this->expected['questions'][0]['id'];
        foreach ([
            ['questions', $qid, 'question', 'Later edited synthetic condition.'],
            ['questions', $qid, 'updated_at', '2002-03-04 05:06:08'],
            ['question_answers', $this->expected['question_answers'][0]['id'], 'marker', 'a9'],
            ['question_option_question', $this->expected['question_option_question'][0]['id'], 'flag', 1],
            ['question_theory_text_blocks', $this->expected['question_theory_text_blocks'][0]['id'], 'position', 1],
        ] as $n => [$table, $id, $field, $value]) {
            $old = DB::table($table)->where('id', $id)->value($field);
            DB::table($table)->where('id', $id)->update([$field => $value]); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-after-'.$n)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-after-'.$n));
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
        foreach (['questions', 'question_answers', 'question_option_question', 'question_theory_text_blocks'] as $table) {
            $row = $this->expected[$table][0]; DB::table($table)->where('id', $row['id'])->delete(); $before = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($before, $this->snapshot());
            DB::table($table)->insert($row);
        }
        $time = '2002-03-04 05:06:07';
        $unapproved = [
            'question_hints' => ['id' => 1, 'question_id' => $qid, 'provider' => 'synthetic', 'locale' => 'uk', 'hint' => 'Непогоджена підказка.', 'created_at' => $time, 'updated_at' => $time],
            'question_variants' => ['id' => 1, 'question_id' => $qid, 'text' => 'Непогоджений варіант.', 'created_at' => $time, 'updated_at' => $time],
            'verb_hints' => ['id' => 1, 'question_id' => $qid, 'option_id' => $this->matches['Nothing'][0]['id'], 'marker' => 'a1', 'created_at' => $time, 'updated_at' => $time],
            'saved_grammar_test_questions' => ['id' => 2, 'saved_grammar_test_id' => 1, 'question_uuid' => Supplement::UUIDS[0], 'position' => 1, 'created_at' => $time, 'updated_at' => $time],
        ];
        foreach ($unapproved as $table => $row) {
            DB::table($table)->insert($row); $before = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($before, $this->snapshot());
            DB::table($table)->where('id', $row['id'])->delete();
        }
    }

    public function test_failure_after_each_real_insert_table_rolls_back_every_content_update_and_new_row(): void
    {
        foreach ([['questions', 0], ['questions', 1], ['question_answers', 0], ['question_option_question', 0], ['question_theory_text_blocks', 0]] as $n => [$table, $index]) {
            $service = $this->service(); $service->failAtTable = $table; $service->failAtIndex = $index;
            $plan = $service->savePlan($this->file('preview-failure-'.$n)); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview-failure-'.$n), $this->file('backup-failure-'.$n)), 'Injected');
            self::assertSame($before, $this->snapshot());
            self::assertSame($plan, $service->plan());
            self::assertSame($plan, json_decode(File::get($this->file('backup-failure-'.$n)), true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_stale_public_and_sealed_synthetic_evidence_and_protected_progress_refuse_before_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        foreach ([$this->root.'/'.Supplement::SOURCE, $this->file('synthetic-question-evidence')] as $n => $path) {
            $bytes = File::get($path); File::put($path, $bytes."\n"); $before = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-source-'.$n)));
            self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-source-'.$n));
            File::put($path, $bytes);
        }
        DB::table('m75_synthetic_protected_progress')->where('id', 1)->update(['value' => 'Later external synthetic progress.']);
        $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-progress')));
        self::assertSame($before, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-progress'));
    }

    public function test_existing_backups_private_paths_and_restore_scope_are_never_overwritten_or_broadened(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        File::put($this->file('owned-backup'), 'Existing synthetic backup.'); $before = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('owned-backup')), 'exclusive');
        self::assertSame('Existing synthetic backup.', File::get($this->file('owned-backup')));
        self::assertSame($before, $this->snapshot());
        $preview = File::get($this->file('preview'));
        $this->refused(fn () => $service->savePlan($this->file('preview')), 'exclusive');
        self::assertSame($preview, File::get($this->file('preview')));
        $this->refused(fn () => $service->apply($this->file('preview'), $this->root.'/outside-private.json'), 'direct private JSON');
        self::assertFileDoesNotExist($this->root.'/outside-private.json');
        $this->refused(fn () => $service->restore($this->file('missing-backup')), 'outside');
        self::assertSame($before, $this->snapshot());
    }

    public function test_reviewed_ids_projection_timestamp_and_complete_collation_inventory_cannot_be_changed_together(): void
    {
        $original = $this->evidence;
        $changes = [
            static function (&$e): void { $e['ids']['questions'][Supplement::UUIDS[0]]++; },
            static function (&$e): void { $e['row_timestamp'] = '2002-03-04 05:06:08'; },
            static function (&$e): void { $e['projection']['questions'][0]['fields']['difficulty'] = 3; },
            static function (&$e): void { $e['_inventory']['binding']['level'] = 'B1'; },
            static function (&$e): void { $e['_inventory']['question_collisions']['questions'] = [['id' => 9]]; },
            static function (&$e): void { $e['_inventory']['practice_before']['level'] = 'A2'; },
            static function (&$e): void { $e['_inventory']['option_matches']['Nothing'] = []; },
            static function (&$e): void { $e['_before']['pages'][Supplement::OWNER]['relations']['questions'] = []; },
        ];
        foreach ($changes as $change) {
            $this->evidence = $original; $change($this->evidence); $this->saveSyntheticEvidence();
            $before = $this->snapshot(); $this->refused(fn () => $this->service()->plan());
            self::assertSame($before, $this->snapshot());
        }
        $this->evidence = $original; $this->saveSyntheticEvidence();
    }
}

/**
 * No production authority bypass is exported. Only this test subclass substitutes a
 * sealed invented evidence object; actual M75 projections, collision queries, scopes,
 * relation normalization, M26 exclusive backup and transactional SQL stay inherited.
 */
class M75SyntheticPronounFormsWriter extends Patch
{
    public ?string $failAtTable = null;
    public int $failAtIndex = 0;

    public function __construct(Connection $db, string $databasePath, string $privateDirectory, private string $fixtureEvidenceHash)
    {
        parent::__construct($db, $databasePath, $privateDirectory, localTarget: null, localProof: null);
    }

    public function connection(?string $expected = null, bool $writing = false): array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        return parent::connection($expected, $writing);
    }

    protected function questionEvidence(): array
    {
        IsolatedTestEnvironment::assertOwnedPath($this->privateDirectory);
        $path = $this->privateDirectory.'/synthetic-question-evidence.json';
        if (!is_file($path) || is_link($path) || !hash_equals($this->fixtureEvidenceHash, hash_file('sha256', $path))) {
            throw new RuntimeException('Synthetic M75 evidence changed; no implicit replan.');
        }
        return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function extendPlan(array &$plan, array $patch, ?array $baseline, bool $lock): void
    {
        parent::extendPlan($plan, $patch, $baseline, $lock);
        // Parent used the real trait and initialized its private approved-row/last-update
        // state. Replace only unavailable production evidence-file pin references, not
        // projections, fields, allocations, protected rows, timestamps or SQL operations.
        foreach (parent::questionEvidencePins() as $path => $_) { unset($plan['sources'][$path]); }
        $plan['sources']['private:synthetic-question-evidence.json'] = $this->fixtureEvidenceHash;
    }

    protected function afterQuestionRowInsert(string $table, int $index): void
    {
        if ($this->failAtTable === $table && $this->failAtIndex === $index) {
            throw new RuntimeException('Injected isolated M75 question insert failure.');
        }
    }
}
