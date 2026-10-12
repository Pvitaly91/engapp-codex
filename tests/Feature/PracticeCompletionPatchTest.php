<?php

namespace Tests\Feature;

use App\Services\PracticeCompletionPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\PracticeCompletionPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class PracticeCompletionPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $original;
    private array $package;
    private array $baseline;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->root = storage_path('app/practice-completion-fixture-'.bin2hex(random_bytes(8)));
        $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$this->original, $this->package, $master] = Package::load();
        Package::validate($this->original, $this->package, $master);

        $categories = [];
        foreach ($this->original['targets'] as $target) {
            $slug = $target['ancestry'][0];
            $categories[$slug] = ['id' => $target['page']['page_category_id'], 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk'];
        }
        DB::table('page_categories')->insert(array_values($categories));
        foreach (Patch::sourcePaths($this->original, $this->package) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        // Seed the unchanged historical six-task releases, never the new supplemental AFTER.
        foreach ($this->package['targets'] as $target) {
            $before = Patch::historicalDefinition($target);
            // Real captured IDs are part of the finite binding; fixture seeding must not invent new IDs.
            DB::table('pages')->insert(array_diff_key($target['page'], ['locale' => true]) + [
                'seeder' => $target['identity'], 'text' => $before['page']['subtitle_text'],
            ]);
            $path = $this->root.'/'.$target['definition'];
            File::put($path, M26DetailPackage::json($before));
            (new class($path) extends JsonPageSeeder {
                public function __construct(private string $path) {}
                protected function definitionPath(): string { return $this->path; }
            })->run();
            File::put($path, M26DetailPackage::json(Patch::afterDefinition($before, $target)));
        }
        $this->createProtectedFixtures();
        $this->baseline = [];
        foreach (Package::OWNERS as $identity) {
            $owner = (array) DB::table('pages')->where('seeder', $identity)->first();
            $this->baseline[$identity] = ['page' => $owner, 'blocks' => $this->ownerRows($owner['id'])];
        }
    }

    private function createProtectedFixtures(): void
    {
        foreach (Package::OWNERS as $i => $identity) {
            $source = (array) DB::table('text_blocks')->where('locale', 'uk')->where('type', 'forms-grid')
                ->where('seeder', $identity)->first();
            foreach (['en', 'pl'] as $locale) {
                $foreign = $source; unset($foreign['id']);
                $foreign['uuid'] = 'practice-completion-'.$i.'-'.$locale;
                $foreign['locale'] = $locale; $foreign['body'] = 'Independent '.$locale.' teaching content.';
                DB::table('text_blocks')->insert($foreign);
            }
        }
        $pageId = DB::table('pages')->insertGetId(['slug' => 'completion-protected-neighbour',
            'title' => 'Unchanged neighbour', 'text' => 'Unchanged text', 'type' => 'theory', 'seeder' => 'ProtectedNeighbour']);
        DB::table('text_blocks')->insert(['uuid' => 'completion-protected-block', 'page_id' => $pageId,
            'locale' => 'uk', 'body' => 'Unchanged neighbouring lesson', 'seeder' => 'ProtectedNeighbour']);
        $practice = $this->practiceRow(0);
        $question = DB::table('questions')->insertGetId(['uuid' => 'completion-bank-question',
            'question' => 'Protected {a1}', 'type' => '4', 'theory_text_block_uuid' => $practice['uuid']]);
        $option = DB::table('question_options')->insertGetId(['option' => 'Protected answer']);
        DB::table('question_answers')->insert(['question_id' => $question, 'option_id' => $option, 'marker' => 'a1']);
        DB::table('question_option_question')->insert(['question_id' => $question, 'option_id' => $option, 'flag' => 1]);
        DB::table('verb_hints')->insert(['question_id' => $question, 'option_id' => $option, 'marker' => 'a1']);
        DB::table('question_hints')->insert(['question_id' => $question, 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Unchanged hint']);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'completion-bank-question',
            'text_block_uuid' => $practice['uuid'], 'position' => 0]);
        $saved = DB::table('saved_grammar_tests')->insertGetId(['uuid' => 'completion-saved-test',
            'name' => 'Protected saved test', 'slug' => 'completion-existing-test', 'filters' => '{}']);
        DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id' => $saved,
            'question_uuid' => 'completion-bank-question', 'position' => 0]);
        foreach (['completion_existing_sessions', 'completion_existing_progress', 'completion_unlisted_table'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, function (Blueprint $schema) {
                $schema->id(); $schema->text('value'); $schema->timestamps();
            });
            DB::table($table)->insert(['value' => 'unchanged',
                'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        }
    }

    private function service(): Patch { return new Patch(DB::connection(), $this->root.'/database', $this->evidence); }
    private function path(string $name): string { return $this->evidence.'/'.$name.'.json'; }
    private function ownerRows(int $id): array
    {
        return DB::table('text_blocks')->where('page_id', $id)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }
    private function practiceRow(int $index): array
    {
        return (array) DB::table('text_blocks')->where('uuid', $this->package['targets'][$index]['before']['practice']['uuid'])->first();
    }
    private function snapshot(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false);
        sort($tables); $out = [];
        foreach ($tables as $table) {
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function refusesWithoutDbChange(callable $operation): RuntimeException
    {
        $before = $this->snapshot(); $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught, 'Unsafe operation was accepted.');
        self::assertSame($before, $this->snapshot(), 'Refusal must not write to any actual table.');
        return $caught;
    }

    public function test_twelve_body_only_updates_preserve_old_tasks_metadata_all_rows_and_repeat_noop(): void
    {
        $patch = $this->service(); $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('preview'));
        self::assertSame($before, $this->snapshot());
        self::assertSame('before', $plan['state']);
        self::assertSame(Package::OWNERS, array_keys($plan['pages']));
        self::assertSame(array_column(array_column($this->package['targets'], 'page'), 'id'),
            array_column(array_column(array_values($plan['pages']), 'page'), 'id'));
        self::assertCount(12, $plan['updates']);
        self::assertSame([], $plan['inserts']);
        self::assertSame(array_keys($before), array_keys($plan['protected']));
        self::assertSame(count($before['text_blocks']), $plan['protected']['text_blocks']['count'],
            'Target rows remain fingerprinted; only their body column is excluded.');
        foreach ($plan['updates'] as $update) {
            self::assertSame('text_blocks', $update['table']);
            self::assertSame(['body'], array_keys($update['before']));
            self::assertSame(['body'], array_keys($update['after']));
        }
        $result = $patch->apply($this->path('preview'), $this->path('backup'));
        self::assertSame('applied', $result['status']);
        self::assertSame(12, $result['updated']);
        self::assertSame(0, $result['inserted']);
        self::assertFileExists($this->path('backup'));

        $after = $this->snapshot(); $projected = $after;
        foreach ($plan['updates'] as $update) {
            foreach ($projected['text_blocks'] as &$row) {
                if ($row['id'] === $update['id']) {
                    self::assertSame($update['uuid'], $row['uuid']);
                    self::assertSame($update['after']['body'], $row['body']);
                    $oldData = json_decode($update['before']['body'], true);
                    $newData = json_decode($row['body'], true);
                    foreach ($oldData as $key => $value) {
                        if ($key === 'inputs' && $value === []) { continue; }
                        self::assertSame($value, $newData[$key], 'Every historical practice field remains exact.');
                    }
                    self::assertCount(2, $newData['inputs']);
                    self::assertSame([7, 8], array_column($newData['inputs'], 'source_index'));
                    $row['body'] = $update['before']['body'];
                }
            }
            unset($row);
        }
        self::assertSame($before, $projected, 'Only the twelve bodies may differ, including timestamps and all bank/progress rows.');
        $afterPlan = $patch->plan();
        self::assertSame('after', $afterPlan['state']);
        self::assertSame($plan['protected'], $afterPlan['protected']);
        self::assertSame([], $afterPlan['updates']);
        $noOp = $patch->apply($this->path('preview'), $this->path('unused-noop-backup'));
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $noOp);
        self::assertFileDoesNotExist($this->path('unused-noop-backup'));
        self::assertSame($after, $this->snapshot());
    }

    public function test_exclusive_backup_restore_denial_and_production_profile_cannot_expand_authority(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        File::put($this->path('backup'), 'pre-existing backup bytes');
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertSame('pre-existing backup bytes', File::get($this->path('backup')));
        $this->refusesWithoutDbChange(fn () => $patch->restore($this->path('preview')));
        $this->app->detectEnvironment(fn () => 'production');
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('production-backup')));
        self::assertFileDoesNotExist($this->path('production-backup'));
    }

    public function test_failure_after_first_update_rolls_back_all_twelve_bodies(): void
    {
        $patch = new class(DB::connection(), $this->root.'/database', $this->evidence) extends Patch {
            protected function afterUpdate(int $index): void
            {
                if ($index === 0) { throw new RuntimeException('Injected completion failure'); }
            }
        };
        $patch->savePlan($this->path('preview'));
        $error = $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertSame('Injected completion failure', $error->getMessage());
        self::assertFileExists($this->path('backup'));
    }

    public function test_non_target_progress_and_target_row_metadata_side_effects_roll_back(): void
    {
        foreach (['progress', 'target-metadata', 'page-text'] as $mode) {
            $row = $this->practiceRow(0);
            $patch = new class(DB::connection(), $this->root.'/database', $this->evidence, $mode, $row) extends Patch {
                public function __construct($db, string $databasePath, string $directory, private string $mode, private array $row)
                { parent::__construct($db, $databasePath, $directory); }
                protected function afterUpdate(int $index): void
                {
                    if ($index !== 0) { return; }
                    if ($this->mode === 'progress') { $this->db->table('completion_existing_progress')->update(['value' => 'Unapproved']); }
                    if ($this->mode === 'target-metadata') { $this->db->table('text_blocks')->where('id', $this->row['id'])->update(['updated_at' => '2099-01-01 00:00:00']); }
                    if ($this->mode === 'page-text') { $this->db->table('pages')->where('id', $this->row['page_id'])->update(['text' => 'Unapproved']); }
                }
            };
            $patch->savePlan($this->path($mode));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path($mode), $this->path($mode.'-backup')));
            self::assertFileExists($this->path($mode.'-backup'));
        }
    }

    public function test_partial_known_after_or_manual_body_is_never_adopted(): void
    {
        $patch = $this->service(); $row = $this->practiceRow(0);
        $patch->savePlan($this->path('preview'));
        foreach (['partial', 'manual'] as $mode) {
            $body = $mode === 'partial' ? $this->package['targets'][0]['after']['practice']['body'] : 'Concurrent manual lesson';
            DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $body]);
            $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path($mode)));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path($mode.'-backup')));
            self::assertFileDoesNotExist($this->path($mode.'-backup'));
            DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']]);
        }
    }

    public function test_fresh_and_stale_plans_reject_wrong_owner_locale_sort_metadata_and_missing_uuid(): void
    {
        $patch = $this->service(); $row = $this->practiceRow(0);
        $patch->savePlan($this->path('preview'));
        $otherPage = DB::table('pages')->where('slug', 'completion-protected-neighbour')->value('id');
        foreach (['seeder' => Package::OWNERS[1], 'locale' => 'en', 'page_id' => $otherPage,
            'page_category_id' => 999, 'sort_order' => 999, 'level' => 'C2', 'heading' => 'Foreign heading',
            'css_class' => 'foreign', 'uuid' => 'foreign-practice-uuid'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]);
            $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('fresh-'.$field)));
            $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('stale-'.$field)));
            self::assertFileDoesNotExist($this->path('stale-'.$field));
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
    }

    public function test_global_uuid_ambiguity_is_rejected_without_changing_unique_schema(): void
    {
        $uuid = $this->practiceRow(0)['uuid'];
        $patch = new class(DB::connection(), $this->root.'/database', $this->evidence, $uuid) extends Patch {
            public function __construct($db, string $databasePath, string $directory, private string $uuid)
            { parent::__construct($db, $databasePath, $directory); }
            protected function rows(string $table, callable $filter, bool $lock): array
            {
                $rows = parent::rows($table, $filter, $lock);
                if ($table === 'text_blocks' && count($rows) === 1 && ($rows[0]['uuid'] ?? null) === $this->uuid) { $rows[] = $rows[0]; }
                return $rows;
            }
        };
        $error = $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('ambiguous')));
        self::assertSame('Practice completion practice UUID missing/ambiguous.', $error->getMessage());
    }

    public function test_substantive_source_change_and_tampered_plan_are_rejected_before_backup(): void
    {
        $patch = $this->service(); $plan = $patch->savePlan($this->path('preview'));
        $path = $this->root.'/'.$this->package['targets'][0]['definition'];
        $original = File::get($path); $data = json_decode($original, true);
        $slot = $this->package['targets'][0]['after']['practice']['sort_order'] - 1;
        $body = json_decode($data['page']['blocks'][$slot]['body'], true);
        $body['inputs'][0]['after'] .= ' A different task condition.';
        $data['page']['blocks'][$slot]['body'] = M26DetailPackage::json($body);
        File::put($path, M26DetailPackage::json($data));
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('source-backup')));
        self::assertFileDoesNotExist($this->path('source-backup'));
        File::put($path, $original);
        $plan['updates'][0]['after']['body'] = 'Unreviewed replacement';
        File::put($this->path('preview'), M26DetailPackage::json($plan));
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('tampered-backup')));
        self::assertFileDoesNotExist($this->path('tampered-backup'));
    }

    public function test_definition_projection_rejects_all_changes_other_than_exact_practice_body(): void
    {
        foreach ($this->package['targets'] as $i => $target) {
            $old = Patch::historicalDefinition($target);
            $after = Patch::afterDefinition($old, $target);
            Patch::assertDefinitionScope($old, $after, $target, $i);
            $this->addToAssertionCount(1);
            foreach (['title', 'subtitle', 'theory', 'practice-metadata', 'extra'] as $mode) {
                $bad = $after;
                if ($mode === 'title') { $bad['page']['title'] .= ' changed'; }
                if ($mode === 'subtitle') { $bad['page']['subtitle_html'] .= ' changed'; }
                if ($mode === 'theory') { $bad['page']['blocks'][1]['body'] = 'changed'; }
                if ($mode === 'practice-metadata') {
                    $slot = $target['before']['practice']['sort_order'] - 1;
                    $bad['page']['blocks'][$slot]['column'] = $after['page']['blocks'][$slot]['column'] === 'left' ? 'right' : 'left';
                    self::assertNotSame($after['page']['blocks'][$slot]['column'], $bad['page']['blocks'][$slot]['column']);
                }
                if ($mode === 'extra') { $bad['page']['unknown_field'] = 'not authorized'; }
                $this->refusesWithoutDbChange(fn () => Patch::assertDefinitionScope($old, $bad, $target, $i));
            }
        }
    }

    public function test_independent_baseline_exempts_only_the_known_practice_body_and_performs_no_queries(): void
    {
        $target = $this->package['targets'][0]; $captured = $this->baseline[$target['identity']];
        $owner = $captured['page']; $rows = $captured['blocks'];
        $uuid = $target['before']['practice']['uuid'];
        $beforeBody = $target['before']['practice']['body']; $afterBody = $target['after']['practice']['body'];
        $index = array_search($uuid, array_column($rows, 'uuid'), true);
        self::assertNotFalse($index);
        $afterRows = $rows; $afterRows[$index]['body'] = $afterBody;
        DB::flushQueryLog(); DB::enableQueryLog();
        try {
            Patch::assertMetadataBaseline($captured, $owner, $rows, $uuid, $beforeBody, $afterBody);
            Patch::assertMetadataBaseline($captured, $owner, $afterRows, $uuid, $beforeBody, $afterBody);
            self::assertSame([], DB::getQueryLog());
        } finally { DB::disableQueryLog(); }
        foreach (array_keys($owner) as $field) {
            $changed = $owner; $changed[$field] = 'changed-'.$field;
            $this->refusesWithoutDbChange(fn () => Patch::assertMetadataBaseline($captured, $changed, $rows, $uuid, $beforeBody, $afterBody));
        }
        foreach (array_keys($rows[$index]) as $field) {
            $changed = $rows; $changed[$index][$field] = 'changed-'.$field;
            $this->refusesWithoutDbChange(fn () => Patch::assertMetadataBaseline($captured, $owner, $changed, $uuid, $beforeBody, $afterBody));
        }
        foreach ($rows as $j => $row) {
            if ($j === $index) { continue; }
            $changed = $rows; $changed[$j]['body'] = 'changed non-practice or localized body';
            $this->refusesWithoutDbChange(fn () => Patch::assertMetadataBaseline($captured, $owner, $changed, $uuid, $beforeBody, $afterBody));
        }
        foreach ([array_slice($rows, 1), [...$rows, $rows[0]]] as $changed) {
            $this->refusesWithoutDbChange(fn () => Patch::assertMetadataBaseline($captured, $owner, $changed, $uuid, $beforeBody, $afterBody));
        }
        $duplicateCapture = $captured; $duplicateCapture['blocks'][1] = $duplicateCapture['blocks'][0];
        $this->refusesWithoutDbChange(fn () => Patch::assertMetadataBaseline($duplicateCapture, $owner, $rows, $uuid, $beforeBody, $afterBody));
    }

    public function test_fingerprints_include_target_metadata_but_ignore_only_approved_bodies(): void
    {
        $patch = new class(DB::connection(), $this->root.'/database', $this->evidence) extends Patch {
            public function fingerprints(array $ids): array { return $this->protectedFingerprints($ids); }
        };
        $ids = array_map(fn ($i) => $this->practiceRow($i)['id'], array_keys($this->package['targets']));
        $before = $patch->fingerprints($ids);
        $row = $this->practiceRow(0);
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => 'Only this field is excluded from fingerprints']);
        self::assertSame($before, $patch->fingerprints($ids));
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body'], 'updated_at' => '2099-01-01 00:00:00']);
        self::assertNotSame($before['text_blocks'], $patch->fingerprints($ids)['text_blocks']);
        DB::table('text_blocks')->where('id', $row['id'])->update(['updated_at' => $row['updated_at']]);
        DB::table('pages')->where('id', $row['page_id'])->update(['text' => 'No page field is excluded']);
        self::assertNotSame($before['pages'], $patch->fingerprints($ids)['pages']);
    }

    public function test_changed_locale_or_extra_row_after_preview_invalidates_the_plan(): void
    {
        $patch = $this->service(); $patch->savePlan($this->path('preview'));
        $row = DB::table('text_blocks')->where('seeder', Package::OWNERS[0])->where('locale', 'en')->first();
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => 'Concurrent EN edit']);
        $this->refusesWithoutDbChange(fn () => $patch->apply($this->path('preview'), $this->path('locale-backup')));
        self::assertFileDoesNotExist($this->path('locale-backup'));
        DB::table('text_blocks')->where('id', $row->id)->update(['body' => $row->body]);
        $practice = $this->practiceRow(0);
        DB::table('text_blocks')->insert(['uuid' => 'completion-unexpected-row', 'page_id' => $practice['page_id'],
            'page_category_id' => $practice['page_category_id'], 'locale' => 'uk', 'seeder' => $practice['seeder'], 'body' => 'Manual extra row']);
        $this->refusesWithoutDbChange(fn () => $patch->savePlan($this->path('extra-row')));
        self::assertFileDoesNotExist($this->path('extra-row'));
    }
}
