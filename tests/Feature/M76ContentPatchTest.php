<?php

namespace Tests\Feature;

use App\Services\M76ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M76PronounRelations as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Existing transactional writer on isolated SQLite. Synthetic banks are NOT live thematic-bank acceptance. */
class M76ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $patch;
    private array $before;

    protected function setUp(): void
    {
        parent::setUp();
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        [$this->before, $this->patch, , $contract] = Package::load();
        self::assertSame([129, 121, 127], Package::PAGE_IDS); self::assertSame([12, 12, 12], Package::CONTINUING_COUNTS);
        $this->root = storage_path('app/m76-native-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign-category'], [20, 'zaimennyky-ta-vkazivni-slova']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00'; $questionId = 7600000;
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 20, 'seeder' => $target['identity'],
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle']];
            foreach ($old['page']['blocks'] as $slot => $config) { $configs[$slot + 1] = $config; }
            foreach ($configs as $position => $config) {
                DB::table('text_blocks')->insert(['uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 20, 'locale' => 'uk', 'seeder' => $target['identity'],
                    'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null,
                    'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm76-synthetic-bank-'.$questionId;
                    DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid,
                        'question' => 'Ізольоване питання, не selected live question.', 'level' => ['A1', 'A2', 'B1', 'B2', 'C2'][$n],
                        'difficulty' => 2, 'type' => '4', 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero,
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->first();
            foreach (['en', 'pl'] as $locale) {
                unset($foreign['id']); $foreign['uuid'] = 'm76-nontarget-'.$i.'-'.$locale; $foreign['locale'] = $locale;
                $foreign['body'] = 'Unchanged '.$locale; DB::table('text_blocks')->insert($foreign);
            }
        }
        DB::connection()->getSchemaBuilder()->create('m76_protected_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m76_protected_progress')->insert(['value' => 'Unchanged synthetic progress.']);
    }

    private function service(): M76SyntheticNativePatch { return new M76SyntheticNativePatch(DB::connection(), $this->root.'/database', $this->private); }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            if (str_starts_with($table, 'sqlite_')) { continue; }
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refused(callable $callback): void
    {
        $error = null; try { $callback(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }

    private function expectedCounts(): array
    {
        $counts = ['pages' => 0, 'text_blocks' => 0, 'inserts' => 0, 'metadata_rows' => 0, 'metadata_fields' => 0];
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']['page']; $after = $target['after']['page'];
            if ($old['subtitle_text'] !== $after['subtitle_text']) { ++$counts['pages']; }
            if ($old['subtitle_html'] !== $after['subtitle_html']) { ++$counts['text_blocks']; }
            foreach ($old['blocks'] as $slot => $source) {
                $new = $after['blocks'][$slot]; $changed = $source['body'] !== $new['body']; $metadata = 0;
                foreach (['type', 'heading'] as $field) { if (($source[$field] ?? null) !== ($new[$field] ?? null)) { ++$metadata; } }
                if ($changed || $metadata) { ++$counts['text_blocks']; }
                if ($metadata) { ++$counts['metadata_rows']; $counts['metadata_fields'] += $metadata; }
            }
            $counts['inserts'] += count($after['blocks']) - count($old['blocks']);
        }
        return $counts;
    }

    public function test_transaction_updates_only_exact_continuing_fields_preserves_all_three_practice_identities_and_repeats_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $plan['state']);
        $expected = $this->expectedCounts(); self::assertSame(3, $expected['pages']); self::assertSame(0, $expected['inserts']);
        self::assertSame(10, $expected['metadata_rows']); self::assertSame(14, $expected['metadata_fields']);
        self::assertCount($expected['pages'] + $expected['text_blocks'], $plan['updates']); self::assertSame([], $plan['inserts']);
        self::assertSame([], $plan['deletes'] ?? []);
        foreach ($plan['updates'] as $update) {
            $fields = $update['table'] === 'pages' ? ['text'] : ['body', 'type', 'heading'];
            self::assertSame($fields, array_keys($update['before'])); self::assertSame($fields, array_keys($update['after']));
        }
        self::assertCount(count($plan['updates']), array_unique(array_map(static fn ($row) => $row['table'].':'.$row['id'], $plan['updates'])));
        self::assertSame([1, 1, 0], array_map(static fn ($id) => DB::table('text_blocks')->where('page_id', $id)->where('locale', 'uk')->where('type', 'practice-set')->count(), Package::PAGE_IDS));
        foreach (Package::PRACTICE_UUIDS as $i => $uuid) {
            $row = DB::table('text_blocks')->where('uuid', $uuid)->sole();
            self::assertSame(Package::PAGE_IDS[$i], $row->page_id); self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i] + 1, $row->sort_order); self::assertNull($row->level);
        }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(0, $result['inserted']); self::assertCount($result['updated'], $plan['updates']);
        $afterPlan = $service->savePlan($this->file('after'));
        self::assertSame('after', $afterPlan['state']); self::assertSame([], $afterPlan['updates']); self::assertSame([], $afterPlan['inserts']);
        self::assertSame($plan['protected'], $afterPlan['protected']); $after = $this->snapshot();
        self::assertCount(count($before['text_blocks']), $after['text_blocks']);
        foreach ($before['text_blocks'] as $row) {
            $now = (array) DB::table('text_blocks')->where('id', $row['id'])->sole();
            if ($row['locale'] === 'uk') {
                $i = array_search($row['page_id'], Package::PAGE_IDS, true); $page = $this->patch['targets'][$i]['after']['page'];
                $expectedRow = $row['sort_order'] === 0 ? ['type' => 'subtitle', 'heading' => null, 'body' => $page['subtitle_html']]
                    : $page['blocks'][$row['sort_order'] - 1];
                foreach (['body', 'type', 'heading'] as $field) { self::assertSame($expectedRow[$field] ?? null, $now[$field]); unset($row[$field], $now[$field]); }
                self::assertNull($now['level']);
            }
            self::assertSame($row, $now, 'No existing UUID/order/column/level/CSS/EN/PL/timestamp change is authorized.');
        }
        foreach ($before['pages'] as $row) { $now = (array) DB::table('pages')->where('id', $row['id'])->sole(); unset($row['text'], $now['text']); self::assertSame($row, $now); }
        foreach ($before as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table); } }
        foreach (Package::PAGE_IDS as $i => $id) {
            $practice = DB::table('text_blocks')->where('page_id', $id)->where('locale', 'uk')->where('type', 'practice-set')->sole();
            self::assertSame(Package::PRACTICE_UUIDS[$i], $practice->uuid); self::assertNull($practice->level);
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i] + 1, $practice->sort_order);
        }
        foreach (['preview', 'after'] as $name) {
            self::assertSame('no-op', $service->apply($this->file($name), $this->file('unused-'.$name))['status']);
            self::assertFileDoesNotExist($this->file('unused-'.$name)); self::assertSame($after, $this->snapshot());
        }
    }

    public function test_failure_after_first_or_last_update_rolls_back_all_content_and_legacy_metadata(): void
    {
        foreach (['first', 'last'] as $stage) {
            $service = $this->service(); $plan = $service->savePlan($this->file('preview-'.$stage)); $before = $this->snapshot();
            $service->failAfterUpdateIndex = $stage === 'first' ? 0 : count($plan['updates']) - 1;
            $this->refused(fn () => $service->apply($this->file('preview-'.$stage), $this->file('backup-'.$stage)));
            self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan()); self::assertFileExists($this->file('backup-'.$stage));
        }
    }

    public function test_duplicate_continuing_uuid_tail_and_partial_release_are_refused(): void
    {
        $service = $this->service(); $plan = $service->plan();
        $duplicate = (array) DB::table('text_blocks')->where('uuid', Package::PRACTICE_UUIDS[0])->sole();
        unset($duplicate['id']); $duplicate['uuid'] = 'm76-duplicate-practice'; DB::table('text_blocks')->insert($duplicate);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', 'm76-duplicate-practice')->delete();
        foreach ($plan['updates'] as $update) { if ($update['seeder'] === Package::OWNERS[2]) { DB::table($update['table'])->where('id', $update['id'])->update($update['after']); } }
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_foreign_page_category_and_continuing_metadata_are_not_reassigned(): void
    {
        $service = $this->service(); $page = (array) DB::table('pages')->where('id', 127)->sole();
        foreach (['seeder' => 'Foreign\\Owner', 'page_category_id' => 1, 'slug' => 'foreign', 'type' => 'category'] as $field => $value) {
            DB::table('pages')->where('id', 127)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('pages')->where('id', 127)->update([$field => $page[$field]]);
        }
        $category = (array) DB::table('page_categories')->where('id', 20)->sole();
        foreach (['type' => 'page', 'language' => 'en', 'slug' => 'foreign', 'parent_id' => 1] as $field => $value) {
            DB::table('page_categories')->where('id', 20)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('page_categories')->where('id', 20)->update([$field => $category[$field]]);
        }
        $row = (array) DB::table('text_blocks')->where('uuid', Package::PRACTICE_UUIDS[2])->sole();
        foreach (['uuid' => 'm76-lost-uuid', 'level' => 'A2', 'locale' => 'en', 'column' => 'right', 'sort_order' => 99,
            'heading' => 'Later heading', 'type' => 'practice-set'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
    }

    public function test_stale_body_protected_progress_source_and_unknown_tail_fail_before_creating_new_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $row = (array) DB::table('text_blocks')->where('uuid', Package::PRACTICE_UUIDS[2])->sole();
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']."\n"]); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-body')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-body')); DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']]);
        DB::table('m76_protected_progress')->where('id', 1)->update(['value' => 'Later synthetic progress.']); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-progress')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-progress'));
        DB::table('m76_protected_progress')->where('id', 1)->update(['value' => 'Unchanged synthetic progress.']);
        $source = $this->root.'/'.$this->patch['targets'][0]['path']; $bytes = File::get($source); File::put($source, $bytes."\n"); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-source')); File::put($source, $bytes);
        unset($row['id']); $row['uuid'] = 'm76-unregistered-tail'; $row['sort_order'] = 99; DB::table('text_blocks')->insert($row);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_all_three_null_practice_filters_keep_mixed_cefr_banks_not_curriculum_display_levels(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->plan(); self::assertSame($before, $this->snapshot());
        foreach (Package::OWNERS as $i => $owner) {
            $questions = $plan['pages'][$owner]['relations']['questions']; self::assertGreaterThanOrEqual(5, count($questions));
            self::assertContains('A1', array_column($questions, 'level')); self::assertContains('C2', array_column($questions, 'level'));
            self::assertNull(Package::LEVELS[$i]);
        }
    }

    public function test_exclusive_backup_foreign_type_and_unlinked_banks_cannot_authorize_writes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); File::put($this->file('owned-backup'), 'Existing private backup.');
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('owned-backup')));
        self::assertSame('Existing private backup.', File::get($this->file('owned-backup'))); self::assertSame($snapshot, $this->snapshot());
        $question = (array) DB::table('questions')->where('id', 7600004)->sole();
        foreach (['type' => '0', 'seeder' => 'Foreign\\Bank'] as $field => $value) {
            DB::table('questions')->where('id', $question['id'])->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('questions')->where('id', $question['id'])->update([$field => $question[$field]]);
        }
        DB::table('question_theory_text_blocks')->where('question_uuid', $question['uuid'])->delete(); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_all_continuing_practice_relations_remain_protected_after_legacy_conversion(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        foreach (Package::PAGE_IDS as $i => $id) {
            $page = (array) DB::table('pages')->where('id', $id)->sole();
            $rows = DB::table('text_blocks')->where('page_id', $id)->get()->map(static fn ($row) => (array) $row)->all();
            $method = new ReflectionMethod(Patch::class, 'relations'); $before = $method->invoke($service, $page, $rows, false);
            $question = $before['questions'][0]['uuid']; $uuid = Package::PRACTICE_UUIDS[$i];
            DB::table('question_theory_text_blocks')->insert(['question_uuid' => $question, 'text_block_uuid' => $uuid, 'position' => 9]);
            $after = $method->invoke($service, $page, $rows, false);
            self::assertNotSame($before, $after); self::assertCount(count($before['question_theory_text_blocks']) + 1, $after['question_theory_text_blocks']);
            self::assertCount(1, array_filter($after['question_theory_text_blocks'], static fn ($row) => $row['text_block_uuid'] === $uuid));
        }
    }

    public function test_cli_rejects_ambient_or_foreign_authority_before_reading_any_real_target(): void
    {
        $before = $this->snapshot();
        foreach ([[], ['--local-target' => 'gramlyze.ub', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => '../proof.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json', '--backup' => 'not-apply.json']] as $arguments) {
            $this->artisan('content:patch-relative-each-reciprocal-m76', $arguments)->assertExitCode(1); self::assertSame($before, $this->snapshot());
        }
    }
}

class M76SyntheticNativePatch extends Patch
{
    public ?int $failAfterUpdateIndex = null;

    protected function afterUpdate(int $index): void
    {
        if ($this->failAfterUpdateIndex === $index) { throw new RuntimeException('Injected isolated SQLite update failure.'); }
    }
}
