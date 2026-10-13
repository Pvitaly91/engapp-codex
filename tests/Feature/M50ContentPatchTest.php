<?php

namespace Tests\Feature;

use App\Services\M50ContentPatch as Patch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M50ObligationAdvicePackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real inherited transaction/backup with small isolated fixtures; no working DB or bank reseed. */
class M50ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $before;
    private array $patch;
    private array $fixtureLinks;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        [$this->before, $this->patch, $master] = Package::load(); Package::validate($this->before, $this->patch, $master);
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $this->root = storage_path('app/m50-patch-fixture-'.bin2hex(random_bytes(8))); $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        foreach (Patch::sourcePaths($this->patch) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 15, 'slug' => 'modal-verbs', 'title' => 'Модальні дієслова', 'type' => 'theory', 'language' => 'uk']);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']; $file = $this->root.'/'.$target['path'];
            DB::table('pages')->insert(['id' => $target['page_binding']['id'], 'page_category_id' => 15,
                'seeder' => $target['identity'], 'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'],
                'type' => 'theory', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            File::put($file, M26DetailPackage::json($old));
            (new class($file) extends JsonPageSeeder {
                public function __construct(private string $file) {}
                protected function definitionPath(): string { return $this->file; }
            })->run();
            File::put($file, M26DetailPackage::json($target['after']));
            $row = (array) DB::table('text_blocks')->where('seeder', $target['identity'])->where('type', 'forms-grid')->first();
            foreach (['en', 'pl'] as $locale) {
                $other = $row; unset($other['id']); $other['locale'] = $locale; $other['uuid'] = 'm50-unchanged-'.$i.'-'.$locale; $other['body'] = 'Protected '.$locale;
                DB::table('text_blocks')->insert($other);
            }
        }
        DB::connection()->getSchemaBuilder()->dropIfExists('m50_existing_progress');
        DB::connection()->getSchemaBuilder()->create('m50_existing_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m50_existing_progress')->insert(['value' => 'personal progress must remain exact']);
        $this->fixtureLinks = Patch::loadLinks();
        foreach ($this->fixtureLinks['pairs'] as $pair) {
            DB::table('questions')->insert(['id' => $pair['question_id'], 'uuid' => $pair['question_uuid'],
                'question' => 'Власна ізольована умова '.$pair['question_id'], 'type' => '4', 'level' => 'A2',
                'seeder' => $pair['bank_seeder'], 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $option = DB::table('question_options')->insertGetId(['option' => 'Synthetic answer '.$pair['question_id']]);
            DB::table('question_answers')->insert(['question_id' => $pair['question_id'], 'option_id' => $option, 'marker' => 'a1']);
            DB::table('question_option_question')->insert(['question_id' => $pair['question_id'], 'option_id' => $option, 'flag' => 1]);
            DB::table('verb_hints')->insert(['question_id' => $pair['question_id'], 'option_id' => $option, 'marker' => 'a1']);
            DB::table('question_hints')->insert(['question_id' => $pair['question_id'], 'provider' => 'fixture', 'locale' => 'uk', 'hint' => 'Незмінна підказка']);
            DB::table('question_variants')->insert(['question_id' => $pair['question_id'], 'text' => 'Незмінний варіант']);
        }
        // Only synthetic SQL snapshots differ. Real public owner/bank/UUID/provenance pins are not changed.
        $reader = new M50SyntheticFixturePatch(DB::connection(), $this->root.'/database', $this->evidence);
        foreach ($this->fixtureLinks['pairs'] as &$pair) {
            $snapshot = $reader->fixtureQuestionSnapshot($pair);
            foreach ($pair['hashes'] as $key => &$sha) { $sha = Patch::digest($snapshot[$key]); }
            unset($sha);
        }
        unset($pair);
    }

    private function service(): M50SyntheticFixturePatch
    {
        $service = new M50SyntheticFixturePatch(DB::connection(), $this->root.'/database', $this->evidence);
        $service->fixtureLinks = $this->fixtureLinks;

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

    public function test_preview_apply_exact_old_rows_new_practices_and_repeat_noop_preserve_all_protected_data(): void
    {
        $service = $this->service(); $snapshot = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($snapshot, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertSame(Package::OWNERS, $plan['names']); self::assertCount(3, $plan['inserts']);
        self::assertCount(5, $plan['link_inserts']); self::assertSame('before', $plan['link_state']);
        foreach ($plan['link_inserts'] as $insert) {
            self::assertSame('question_theory_text_blocks', $insert['table']);
            self::assertSame(Patch::NEED_PRACTICE_UUID, $insert['fields']['text_block_uuid']);
            self::assertSame(Patch::digest($insert['fields']), $insert['sha256']);
        }
        self::assertArrayHasKey('m50_existing_progress', $plan['protected']);
        foreach ($plan['updates'] as $change) { self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after'])); }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(count($plan['updates']), $result['updated']); self::assertSame(3, $result['inserted']);
        foreach ($plan['pages'] as $owner) {
            foreach ($owner['blocks'] as $old) {
                $actual = (array) DB::table('text_blocks')->where('id', $old['id'])->first();
                if ($old['locale'] === 'uk') { unset($old['body'], $actual['body']); }
                self::assertSame($old, $actual);
            }
        }
        self::assertSame($snapshot['m50_existing_progress'], $this->snapshot()['m50_existing_progress']);
        foreach (['questions', 'question_answers', 'question_options', 'question_option_question', 'verb_hints', 'question_hints', 'question_variants'] as $table) {
            self::assertSame($snapshot[$table], $this->snapshot()[$table], $table);
        }
        self::assertSame(5, DB::table('question_theory_text_blocks')->where('text_block_uuid', Patch::NEED_PRACTICE_UUID)->count());
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame([], $after['link_inserts']); self::assertSame('after', $after['link_state']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('not-created')));
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('after'), $this->file('not-created-after')));
        self::assertFileDoesNotExist($this->file('not-created'));
        self::assertFileDoesNotExist($this->file('not-created-after'));
    }

    public function test_stale_rows_foreign_owner_locale_category_and_partial_state_refuse_without_writes(): void
    {
        foreach (['body', 'locale', 'category', 'owner'] as $change) {
            $row = (array) DB::table('text_blocks')->where('seeder', Package::OWNERS[0])->where('locale', 'uk')->where('type', 'forms-grid')->first();
            $mutation = match ($change) { 'body' => ['body' => 'manual newer content'], 'locale' => ['locale' => 'en'],
                'category' => ['page_category_id' => 999], 'owner' => ['seeder' => 'ForeignOwner'] };
            DB::table('text_blocks')->where('id', $row['id'])->update($mutation); $snapshot = $this->snapshot();
            $error = null; try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $change); self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('id', $row['id'])->update(array_intersect_key($row, $mutation));
        }
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        DB::table('text_blocks')->where('id', $plan['updates'][1]['id'])->update($plan['updates'][1]['after']);
        $snapshot = $this->snapshot(); $error = null;
        try { $service->apply($this->file('preview'), $this->file('not-created')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created'));
    }

    public function test_failure_inside_inherited_transaction_rolls_back_and_exclusive_backup_cannot_be_overwritten(): void
    {
        $service = new class(DB::connection(), $this->root.'/database', $this->evidence) extends M50SyntheticFixturePatch {
            protected function afterInsert(int $index): void { if ($index === 0) { throw new RuntimeException('Synthetic transaction failure'); } }
        };
        $service->fixtureLinks = $this->fixtureLinks;
        $snapshot = $this->snapshot(); $service->savePlan($this->file('preview')); $error = null;
        try { $service->apply($this->file('preview'), $this->file('backup')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); self::assertFileExists($this->file('backup'));
        $backup = File::get($this->file('backup')); $error = null;
        try { $this->service()->apply($this->file('preview'), $this->file('backup')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($backup, File::get($this->file('backup'))); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_new_uuid_collision_and_edited_source_refuse_before_backup_or_updates(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview')); $fields = $plan['inserts'][0]['fields'];
        DB::table('text_blocks')->insert(array_replace($fields, ['seeder' => 'ForeignOwner'])); $snapshot = $this->snapshot(); $error = null;
        try { $service->apply($this->file('preview'), $this->file('not-created')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created'));
        DB::table('text_blocks')->where('uuid', $fields['uuid'])->delete();
        $source = $this->root.'/'.$this->patch['targets'][0]['path']; File::append($source, ' changed'); $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (\Throwable $caught) { $error = $caught; }
        self::assertNotNull($error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_failure_after_third_link_rolls_back_all_content_practice_and_links_atomically(): void
    {
        $service = new class(DB::connection(), $this->root.'/database', $this->evidence) extends M50SyntheticFixturePatch {
            protected function afterPracticeLinkInsert(int $index): void { if ($index === 2) { throw new RuntimeException('Synthetic link transaction failure'); } }
        };
        $service->fixtureLinks = $this->fixtureLinks; $snapshot = $this->snapshot(); $service->savePlan($this->file('link-preview'));
        $error = null;
        try { $service->apply($this->file('link-preview'), $this->file('link-backup')); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
        self::assertSame(0, DB::table('text_blocks')->where('uuid', Patch::NEED_PRACTICE_UUID)->count());
        self::assertSame(0, DB::table('question_theory_text_blocks')->count()); self::assertFileExists($this->file('link-backup'));
    }

    public function test_question_answer_drift_pair_collision_and_same_bank_foreign_pool_refuse_without_writes(): void
    {
        foreach (['questions' => 'question', 'question_options' => 'option'] as $table => $field) {
            $row = (array) DB::table($table)->orderBy('id')->first();
            DB::table($table)->where('id', $row['id'])->update([$field => 'Manual newer original data']); $snapshot = $this->snapshot(); $error = null;
            try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
            DB::table($table)->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        $pair = $this->fixtureLinks['pairs'][0];
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => $pair['question_uuid'], 'text_block_uuid' => Patch::NEED_PRACTICE_UUID, 'position' => 0]);
        $snapshot = $this->snapshot(); $error = null;
        try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
        DB::table('question_theory_text_blocks')->where('text_block_uuid', Patch::NEED_PRACTICE_UUID)->delete();
        DB::table('questions')->insert(['id' => 999, 'uuid' => 'm50-foreign-same-bank', 'question' => 'Не схвалена умова',
            'type' => '4', 'level' => 'A2', 'seeder' => Package::BANK_SETS[2][0]]);
        $oldNeed = DB::table('text_blocks')->where('page_id', 76)->where('locale', 'uk')->first();
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm50-foreign-same-bank', 'text_block_uuid' => $oldNeed->uuid, 'position' => 0]);
        $snapshot = $this->snapshot(); $error = null;
        try { $this->service()->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_applied_links_reject_missing_extra_position_timestamp_and_original_answer_drift(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $row = (array) DB::table('question_theory_text_blocks')->where('text_block_uuid', Patch::NEED_PRACTICE_UUID)->first();
        foreach (['position' => 1, 'updated_at' => '2030-01-01 00:00:00', 'created_at' => null] as $field => $value) {
            DB::table('question_theory_text_blocks')->where('id', $row['id'])->update([$field => $value]); $snapshot = $this->snapshot(); $error = null;
            try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error, $field); self::assertSame($snapshot, $this->snapshot());
            DB::table('question_theory_text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        DB::table('question_theory_text_blocks')->where('id', $row['id'])->delete(); $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
        DB::table('question_theory_text_blocks')->insert($row);
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm50-unknown', 'text_block_uuid' => Patch::NEED_PRACTICE_UUID,
            'position' => 0, 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
        $snapshot = $this->snapshot(); $error = null;
        try { $service->plan(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_literal_controller_insertions_keep_foreign_bytes_and_refuse_ambiguous_or_already_patched_sources(): void
    {
        $current = file_get_contents(base_path(Patch::CONTROLLER_PATH));
        $lines = preg_split('/\r\n|\n/', $current);
        $before = implode("\n", array_values(array_filter($lines, static fn ($line) => !str_contains($line, 'M50ObligationAdvicePackage::preserveExcludedTextBlock')
            && !in_array($line, ['                    if ($block === null) { continue; }', '                if ($block === null) { continue; }'], true))));
        self::assertSame(Patch::CONTROLLER_GIT_BASE_LF_SHA, hash('sha256', $before));
        self::assertSame(str_replace("\r\n", "\n", $current), Patch::approvedControllerAfter($before));
        $foreign = "\xEF\xBB\xBF".str_replace("\n", "\r\n", $before)."\r\n// Foreign unfinished source is retained exactly.\r\n";
        $after = Patch::approvedControllerAfter($foreign);
        self::assertStringStartsWith("\xEF\xBB\xBF", $after); self::assertStringEndsWith("\r\n// Foreign unfinished source is retained exactly.\r\n", $after);
        self::assertSame(4, substr_count($after, 'M50ObligationAdvicePackage::preserveExcludedTextBlock'));
        foreach ([$after, str_replace('                    if (isset($seenBlockUuids[$block->uuid])) {', 'changed anchor', $before),
            $before."\n                \$textBlock = TextBlock::where('uuid', \$q->theory_text_block_uuid)->first();\n                if (\$textBlock) {"] as $bad) {
            $error = null; try { Patch::approvedControllerAfter($bad); } catch (RuntimeException $caught) { $error = $caught; }
            self::assertInstanceOf(RuntimeException::class, $error);
        }
    }

    public function test_duplicate_new_pair_is_rejected_by_existing_unique_constraint_without_changes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $pair = $this->fixtureLinks['pairs'][0]; $snapshot = $this->snapshot(); $error = null;
        try {
            DB::table('question_theory_text_blocks')->insert(['question_uuid' => $pair['question_uuid'],
                'text_block_uuid' => $pair['text_block_uuid'], 'position' => 0]);
        } catch (\Illuminate\Database\QueryException $caught) { $error = $caught; }
        self::assertInstanceOf(\Illuminate\Database\QueryException::class, $error); self::assertSame($snapshot, $this->snapshot());
        self::assertSame([], $service->plan()['link_inserts']);
    }
}

/** Test-only frozen synthetic SQL snapshots; production links/source/semantic scope are independently pinned. */
class M50SyntheticFixturePatch extends Patch
{
    public array $fixtureLinks = [];
    protected function links(): array { return $this->fixtureLinks; }
    public function fixtureQuestionSnapshot(array $pair): array { return parent::questionSnapshot($pair, false); }
}
