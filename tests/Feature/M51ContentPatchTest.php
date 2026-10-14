<?php

namespace Tests\Feature;

use App\Services\M51ContentPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M51ModalMeaningsPackage as Package;
use App\Support\M51QuestionSupplement as Supplement;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real inherited backup/transaction with separately declared synthetic SQL snapshots, never working MySQL. */
class M51ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $before;
    private array $patch;
    private array $questionEvidence;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        [$this->before, $this->patch, $master] = Package::load(); Package::validate($this->before, $this->patch, $master);
        $this->root = storage_path('app/m51-patch-fixture-'.bin2hex(random_bytes(8))); $this->evidence = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->evidence, 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        foreach (Patch::sourcePaths($this->patch) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 15, 'slug' => 'modal-verbs', 'title' => 'Модальні дієслова', 'type' => 'theory', 'language' => 'uk']);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']; $file = $this->root.'/'.$target['path'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 15, 'seeder' => $target['identity'],
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory',
                'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            File::put($file, M26DetailPackage::json($old));
            (new class($file) extends JsonPageSeeder {
                public function __construct(private string $file) {}
                protected function definitionPath(): string { return $this->file; }
            })->run();
            File::put($file, M26DetailPackage::json($target['after']));
            $row = (array) DB::table('text_blocks')->where('seeder', $target['identity'])->where('type', 'forms-grid')->first();
            foreach (['en', 'pl'] as $locale) {
                $other = $row; unset($other['id']); $other['locale'] = $locale; $other['uuid'] = 'm51-unmodified-'.$i.'-'.$locale; $other['body'] = 'Protected '.$locale;
                DB::table('text_blocks')->insert($other);
            }
        }
        DB::connection()->getSchemaBuilder()->dropIfExists('m51_existing_progress');
        DB::connection()->getSchemaBuilder()->create('m51_existing_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m51_existing_progress')->insert(['value' => 'Original progress stays byte-exact.']);
        $time = '2026-01-01 12:00:00';
        DB::table('categories')->insert(['id' => 45, 'name' => 'English Sentence Builder', 'created_at' => $time, 'updated_at' => $time]);
        foreach ([198 => 'Modals of Deduction', 204 => 'Past Modals', 397 => 'Requests / Offers / Suggestions'] as $id => $name) {
            DB::table('sources')->insert(['id' => $id, 'name' => $name, 'created_at' => $time, 'updated_at' => $time]);
        }
        foreach ([40 => 'the', 41 => 'have', 42 => 'protected'] as $id => $option) {
            DB::table('question_options')->insert(['id' => $id, 'option' => $option, 'created_at' => $time, 'updated_at' => $time]);
        }
        DB::table('questions')->insert(['id' => 1, 'uuid' => 'm51-protected-original-question', 'question' => 'Чинна стороння умова.',
            'difficulty' => 2, 'level' => 'A2', 'category_id' => 45, 'source_id' => 198, 'type' => '4',
            'seeder' => 'ExistingForeignBank', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('question_answers')->insert(['id' => 1, 'question_id' => 1, 'option_id' => 42, 'marker' => 'a1', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('question_option_question')->insert(['id' => 1, 'question_id' => 1, 'option_id' => 42, 'flag' => 1]);
        $oldBlock = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[0])->where('locale', 'uk')->where('type', 'forms-grid')->first();
        DB::table('question_theory_text_blocks')->insert(['id' => 1, 'question_uuid' => 'm51-protected-original-question', 'text_block_uuid' => $oldBlock->uuid,
            'position' => 2, 'created_at' => $time, 'updated_at' => $time]);
        DB::table('question_hints')->insert(['question_id' => 1, 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Оригінальна підказка.']);
        $this->questionEvidence = $this->buildQuestionEvidence();
    }

    private function rows(string $table): array { return DB::table($table)->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all(); }

    private function buildQuestionEvidence(): array
    {
        $source = Supplement::load(); $bindings = [];
        foreach ($this->patch['targets'] as $i => $target) {
            $bindings[] = ['owner' => Package::OWNERS[$i], 'level' => Package::TECHNICAL_LEVELS[$i], 'category_id' => 45,
                'source_id' => [198, 204, 397][$i], 'practice_uuid' => M26DetailPackage::uuid($target['identity'], $target['after']['page']['blocks'][6], 7)];
        }
        $matches = [];
        foreach ($source['banks'] as $bank) {
            foreach ($bank['questions'] as $q) {
                foreach ([...$q['tokens_correct'], ...$q['distractors']] as $token) {
                    $matches[$token] = ['requested' => $token, 'rows' => DB::table('question_options')->whereRaw('LOWER(option) = ?', [mb_strtolower($token)])
                        ->orderBy('id')->get()->map(static fn ($row) => (array) $row)->all()];
                }
            }
        }
        $projection = Supplement::project($source, $bindings, Patch::optionRowsFromInventory($matches)); $ids = []; $allocation = [];
        foreach (Supplement::ROW_COLUMNS as $table => $_) {
            $max = (int) (DB::table($table)->max('id') ?? 0); $next = $max + 1;
            $allocation[$table] = ['max_id' => $max, 'auto_increment' => ['next_id' => $next]]; $ids[$table] = [];
            foreach ($projection[$table] as $row) { $ids[$table][$row['key']] = $next++; }
        }
        $taxonomy = ['question_categories' => $this->rows('categories'), 'sources' => $this->rows('sources')];

        return ['schema_version' => 1, 'base_sha' => Package::BASE_SHA, 'before_sha256' => Patch::BEFORE_SHA,
            'source_sha256' => Supplement::SOURCE_SHA, 'inventory' => ['file' => 'question-inventory-v1.json', 'sha256' => str_repeat('1', 64)],
            'bindings' => $bindings, 'taxonomy' => $taxonomy, 'projection' => $projection, 'ids' => $ids,
            '_inventory' => ['base_sha' => Package::BASE_SHA, 'draft_sha256' => Supplement::SOURCE_SHA,
                'categories' => $taxonomy['question_categories'], 'sources' => $taxonomy['sources'], 'option_matches' => $matches, 'allocation' => $allocation]];
    }

    private function service(): M51SyntheticFixturePatch
    {
        $service = new M51SyntheticFixturePatch(DB::connection(), $this->root.'/database', $this->evidence);
        $service->fixtureQuestionEvidence = $this->questionEvidence;

        return $service;
    }
    private function file(string $name): string { return $this->evidence.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }

        return $out;
    }

    public function test_preview_inherited_atomic_apply_exact_question_families_and_repeated_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(3, $plan['inserts']); self::assertCount(15, $plan['question_projection']['questions']);
        self::assertSame($this->questionEvidence['ids'], $plan['question_ids']);
        self::assertSame(array_keys(Supplement::ROW_COLUMNS), array_keys($plan['question_inserts']));
        foreach ($plan['updates'] as $change) { self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after'])); }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(3, $result['inserted']);
        $after = $service->savePlan($this->file('after'));
        self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        foreach ($after['question_inserts'] as $rows) { self::assertSame([], $rows); }
        self::assertSame($plan['question_projection'], $after['question_projection']); self::assertSame($plan['question_ids'], $after['question_ids']);
        self::assertSame($plan['protected'], $after['protected']);
        $snapshot = $this->snapshot();
        foreach ($before as $table => $rows) {
            if (in_array($table, ['pages', 'text_blocks'], true)) { continue; }
            if (array_key_exists($table, Supplement::ROW_COLUMNS)) {
                $newIds = array_values($this->questionEvidence['ids'][$table]);
                self::assertSame($rows, array_values(array_filter($snapshot[$table], static fn ($r) => !in_array($r['id'], $newIds, true))), $table);
            } else { self::assertSame($rows, $snapshot[$table], $table); }
        }
        foreach ($plan['pages'] as $owner) {
            foreach ($owner['blocks'] as $old) {
                $actual = (array) DB::table('text_blocks')->where('id', $old['id'])->first();
                if ($old['locale'] === 'uk') { unset($old['body'], $actual['body']); }
                self::assertSame($old, $actual);
            }
        }
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('not-created')));
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('after'), $this->file('not-created-after')));
        self::assertFileDoesNotExist($this->file('not-created')); self::assertFileDoesNotExist($this->file('not-created-after'));
    }

    public function test_failure_in_each_insert_family_rolls_back_all_content_and_questions_and_preserves_exclusive_backup(): void
    {
        foreach (array_keys(Supplement::ROW_COLUMNS) as $family) {
            $service = new class(DB::connection(), $this->root.'/database', $this->evidence) extends M51SyntheticFixturePatch {
                public string $failFamily;
                protected function afterQuestionRowInsert(string $table, int $index): void
                {
                    if ($table === $this->failFamily && $index === 0) { throw new RuntimeException('Synthetic per-row family failure'); }
                }
            };
            $service->failFamily = $family; $service->fixtureQuestionEvidence = $this->questionEvidence;
            $snapshot = $this->snapshot(); $service->savePlan($this->file('preview-'.$family)); $error = null;
            try { $service->apply($this->file('preview-'.$family), $this->file('backup-'.$family)); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot(), $family);
            self::assertFileExists($this->file('backup-'.$family)); $backup = File::get($this->file('backup-'.$family));
            $error = null;
            try { $this->service()->apply($this->file('preview-'.$family), $this->file('backup-'.$family)); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($backup, File::get($this->file('backup-'.$family))); self::assertSame($snapshot, $this->snapshot());
        }
    }

    public function test_foreign_owner_locale_category_body_and_partial_state_refuse_before_backup(): void
    {
        foreach (['body', 'locale', 'category', 'owner'] as $change) {
            $row = (array) DB::table('text_blocks')->where('seeder', Package::OWNERS[0])->where('locale', 'uk')->where('type', 'forms-grid')->first();
            $mutation = match ($change) { 'body' => ['body' => 'Manual newer content'], 'locale' => ['locale' => 'en'],
                'category' => ['page_category_id' => 999], 'owner' => ['seeder' => 'ForeignOwner'] };
            DB::table('text_blocks')->where('id', $row['id'])->update($mutation); $snapshot = $this->snapshot(); $error = null;
            try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $change); self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('id', $row['id'])->update(array_intersect_key($row, $mutation));
        }
        $service = $this->service(); $plan = $service->savePlan($this->file('partial-preview'));
        DB::table('text_blocks')->where('id', $plan['updates'][1]['id'])->update($plan['updates'][1]['after']); $snapshot = $this->snapshot(); $error = null;
        try { $service->apply($this->file('partial-preview'), $this->file('not-created')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created'));
    }

    public function test_reused_option_taxonomy_projection_id_and_question_collisions_refuse_without_writes(): void
    {
        foreach (['question_options' => ['id' => 40, 'field' => 'option'], 'categories' => ['id' => 45, 'field' => 'name'], 'sources' => ['id' => 198, 'field' => 'name']] as $table => $item) {
            $row = (array) DB::table($table)->where('id', $item['id'])->first(); DB::table($table)->where('id', $item['id'])->update([$item['field'] => 'Changed original']);
            $snapshot = $this->snapshot(); $error = null; try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $table); self::assertSame($snapshot, $this->snapshot());
            DB::table($table)->where('id', $item['id'])->update([$item['field'] => $row[$item['field']]]);
        }
        foreach (['projection', 'ids', 'binding', 'option_lookup_request', 'option_lookup_rows'] as $kind) {
            $service = $this->service();
            if ($kind === 'projection') { $service->fixtureQuestionEvidence['projection']['questions'][0]['fields']['level'] = 'A1'; }
            if ($kind === 'ids') { $key = array_key_first($service->fixtureQuestionEvidence['ids']['questions']); $service->fixtureQuestionEvidence['ids']['questions'][$key] = 999; }
            if ($kind === 'binding') { $service->fixtureQuestionEvidence['bindings'][0]['category_id'] = 70; }
            if ($kind === 'option_lookup_request') {
                $token = array_key_first($service->fixtureQuestionEvidence['_inventory']['option_matches']);
                $service->fixtureQuestionEvidence['_inventory']['option_matches'][$token]['requested'] = 'Unrelated requested token';
            }
            if ($kind === 'option_lookup_rows') {
                foreach ($service->fixtureQuestionEvidence['_inventory']['option_matches'] as &$lookup) {
                    if ($lookup['rows'] !== []) { $lookup['rows'][] = $lookup['rows'][0]; break; }
                }
                unset($lookup);
            }
            $snapshot = $this->snapshot(); $error = null; try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $kind); self::assertSame($snapshot, $this->snapshot());
        }
        $question = $this->questionEvidence['projection']['questions'][0]['fields'];
        DB::table('questions')->insert(['id' => 999] + $question); $snapshot = $this->snapshot(); $error = null;
        try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_applied_question_rows_and_links_reject_missing_extra_edited_type_level_uuid_and_timestamp(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $id = array_values($this->questionEvidence['ids']['questions'])[0]; $row = (array) DB::table('questions')->where('id', $id)->first();
        foreach (['type' => '1', 'level' => 'A1', 'uuid' => 'manual-changed-uuid', 'updated_at' => '2030-01-01 00:00:00', 'theory_text_block_uuid' => 'foreign-direct-block'] as $field => $value) {
            DB::table('questions')->where('id', $id)->update([$field => $value]); $snapshot = $this->snapshot(); $error = null;
            try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $field); self::assertSame($snapshot, $this->snapshot());
            DB::table('questions')->where('id', $id)->update([$field => $row[$field]]);
        }
        $linkId = array_values($this->questionEvidence['ids']['question_theory_text_blocks'])[0]; $link = (array) DB::table('question_theory_text_blocks')->where('id', $linkId)->first();
        DB::table('question_theory_text_blocks')->where('id', $linkId)->delete(); $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); DB::table('question_theory_text_blocks')->insert($link);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'foreign-question', 'text_block_uuid' => $link['text_block_uuid'], 'position' => 0,
            'created_at' => $link['created_at'], 'updated_at' => $link['updated_at']]); $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_new_practice_collision_or_active_source_edit_refuses_before_backup(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        DB::table('text_blocks')->insert(array_replace($plan['inserts'][0]['fields'], ['seeder' => 'ForeignOwner'])); $snapshot = $this->snapshot(); $error = null;
        try { $service->apply($this->file('preview'), $this->file('not-created')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created'));
        DB::table('text_blocks')->where('uuid', $plan['inserts'][0]['fields']['uuid'])->delete();
        File::append($this->root.'/'.$this->patch['targets'][0]['path'], ' foreign edit'); $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (\Throwable $caught) { $error = $caught; }
        self::assertNotNull($error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_captured_option_lookup_records_preserve_requested_case_and_refuse_old_or_incomplete_shapes(): void
    {
        $records = $this->questionEvidence['_inventory']['option_matches'];
        $decoded = Patch::optionRowsFromInventory($records);
        self::assertSame(array_keys($records), array_keys($decoded));
        foreach ($records as $token => $record) {
            self::assertSame(['requested', 'rows'], array_keys($record)); self::assertSame($token, $record['requested']);
            self::assertSame($record['rows'], $decoded[$token]);
        }
        foreach ([['must' => []], ['must' => ['requested' => 'Must', 'rows' => []]],
            ['must' => ['requested' => 'must', 'rows' => ['not-a-list' => []]]],
            ['must' => ['requested' => 'must', 'rows' => [], 'unapproved' => true]]] as $bad) {
            $error = null; try { Patch::optionRowsFromInventory($bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
        }
    }

    public function test_old_consumer_capture_requires_null_reference_and_exact_full_independent_baseline(): void
    {
        $snapshot = $this->snapshot(); $tables = [];
        foreach ($snapshot as $table => $rows) { $tables[$table] = ['count' => count($rows), 'sha256' => Patch::digest($rows)]; }
        $before = ['sql_before' => $tables]; $valid = ['schema_version' => 1, 'mode' => 'capture', 'before_sha256' => null,
            'all_tables_before_http' => $tables, 'all_tables_after_http' => $tables, 'unchanged_during_http' => true];
        Patch::assertConsumerCapture($before, $valid);
        self::assertCount(count($snapshot), $valid['all_tables_before_http']);
        $first = array_key_first($tables);
        foreach (['mode', 'nonnull_reference', 'missing_reference', 'same_capture_but_changed_baseline', 'missing_table', 'extra_table', 'after_change', 'write_flag'] as $kind) {
            $bad = $valid;
            if ($kind === 'mode') { $bad['mode'] = 'audit'; }
            if ($kind === 'nonnull_reference') { $bad['before_sha256'] = Patch::BEFORE_SHA; }
            if ($kind === 'missing_reference') { unset($bad['before_sha256']); }
            if ($kind === 'same_capture_but_changed_baseline') {
                $bad['all_tables_before_http'][$first]['sha256'] = str_repeat('f', 64);
                $bad['all_tables_after_http'] = $bad['all_tables_before_http'];
            }
            if ($kind === 'missing_table') { unset($bad['all_tables_before_http'][$first], $bad['all_tables_after_http'][$first]); }
            if ($kind === 'extra_table') {
                $bad['all_tables_before_http']['unapproved-extra-table'] = ['count' => 0, 'sha256' => str_repeat('a', 64)];
                $bad['all_tables_after_http'] = $bad['all_tables_before_http'];
            }
            if ($kind === 'after_change') { $bad['all_tables_after_http'][$first]['count']++; }
            if ($kind === 'write_flag') { $bad['unchanged_during_http'] = false; }
            $error = null; try { Patch::assertConsumerCapture($before, $bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $kind); self::assertSame($snapshot, $this->snapshot());
        }
        $badBefore = $before; $badCapture = $valid;
        $badBefore['sql_before'][$first]['count'] = '0';
        $badCapture['all_tables_before_http'] = $badBefore['sql_before']; $badCapture['all_tables_after_http'] = $badBefore['sql_before'];
        $error = null; try { Patch::assertConsumerCapture($badBefore, $badCapture); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    public function test_pure_controller_insertions_preserve_every_foreign_byte_eol_bom_and_require_four_unique_m50_anchors(): void
    {
        $current = str_replace("\r\n", "\n", File::get(base_path(Patch::CONTROLLER_PATH)));
        // Main may already have synced the exact owned hunks; remove only those literal additions to recover the fixture BEFORE.
        foreach ([
            "                    \$block = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                    if (\$block === null) { continue; }\n",
            "                \$block = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$block, 'standalone_test');\n                if (\$block === null) { continue; }\n",
            "                if (\$textBlock) { \$textBlock = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n",
            "            if (\$textBlock) { \$textBlock = \\App\\Support\\M51ModalMeaningsPackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }\n",
        ] as $owned) { $current = str_replace($owned, '', $current); }
        $foreign = "\xEF\xBB\xBF".str_replace("\n", "\r\n", $current)."\r\n// Foreign unfinished bytes retained exactly.\r\n";
        $after = Patch::approvedControllerAfter($foreign);
        self::assertSame(4, substr_count($after, 'M51ModalMeaningsPackage::preserveExcludedTextBlock'));
        self::assertSame(4, substr_count($after, 'M50ObligationAdvicePackage::preserveExcludedTextBlock'));
        self::assertStringStartsWith("\xEF\xBB\xBF", $after); self::assertStringEndsWith("\r\n// Foreign unfinished bytes retained exactly.\r\n", $after);
        foreach ([$after, str_replace('M50ObligationAdvicePackage::preserveExcludedTextBlock', 'MissingM50Anchor::preserveExcludedTextBlock', $current),
            $current."\n            if (\$textBlock) { \$textBlock = \\App\\Support\\M50ObligationAdvicePackage::preserveExcludedTextBlock(\$textBlock, 'standalone_test'); }",
            str_replace("\n", "\r\n", $current)."\n// Mixed EOL"] as $bad) {
            $error = null; try { Patch::approvedControllerAfter($bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
        }
    }
}

/** Test-only evidence supplies exact synthetic originals; production private hash checks remain mandatory. */
class M51SyntheticFixturePatch extends Patch
{
    public array $fixtureQuestionEvidence = [];
    protected function questionEvidence(): array { return $this->fixtureQuestionEvidence; }
}
