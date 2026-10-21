<?php

namespace Tests\Feature;

use App\Services\M73ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M73GenericIrregularCollective as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Existing writer on isolated SQLite; synthetic banks are not live teaching-bank acceptance. */
class M73ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $patch;
    private array $before;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [$this->before, $this->patch, , $contract] = Package::load();
        self::assertSame([80, 86, 84], Package::PAGE_IDS); self::assertSame([13, 7, 6], Package::CONTINUING_COUNTS);
        $this->root = storage_path('app/m73-native-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign-category'], [16, 'imennyky-artykli-ta-kilkist']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug, 'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00'; $questionId = 7300000;
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 16, 'seeder' => $target['identity'],
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle']];
            foreach ($old['page']['blocks'] as $slot => $config) { $configs[$slot + 1] = $config; }
            foreach ($configs as $position => $config) {
                DB::table('text_blocks')->insert(['uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 16, 'locale' => 'uk', 'seeder' => $target['identity'],
                    'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null,
                    'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) { for ($n = 0; $n < 5; ++$n) {
                $uuid = 'm73-synthetic-bank-'.$questionId;
                DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid, 'question' => 'Ізольоване питання, не selected live question.',
                    'level' => ['B2', 'A2', 'B1'][$i], 'difficulty' => 2, 'type' => '4', 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero,
                    'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
            }}
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->first();
            foreach (['en', 'pl'] as $locale) {
                unset($foreign['id']); $foreign['uuid'] = 'm73-nontarget-'.$i.'-'.$locale; $foreign['locale'] = $locale;
                $foreign['body'] = 'Unchanged '.$locale; DB::table('text_blocks')->insert($foreign);
            }
        }
        DB::connection()->getSchemaBuilder()->create('m73_protected_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m73_protected_progress')->insert(['value' => 'Unchanged synthetic progress.']);
    }

    private function service(): M73SyntheticNativePatch { return new M73SyntheticNativePatch(DB::connection(), $this->root.'/database', $this->private); }
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

    private function refused(callable $call): void
    {
        $caught = null; try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught);
    }

    /** Derive current content deltas rather than importing M72's 48/0 count. */
    private function expectedCounts(): array
    {
        $counts = ['pages' => 0, 'text_blocks' => 0, 'inserts' => 0];
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']['page']; $after = $target['after']['page'];
            if ($old['subtitle_text'] !== $after['subtitle_text']) { ++$counts['pages']; }
            if ($old['subtitle_html'] !== $after['subtitle_html']) { ++$counts['text_blocks']; }
            foreach ($old['blocks'] as $slot => $block) { if ($block['body'] !== $after['blocks'][$slot]['body']) { ++$counts['text_blocks']; } }
            $counts['inserts'] += count($after['blocks']) - count($old['blocks']);
        }
        return $counts;
    }

    public function test_transaction_updates_continuing_rows_and_inserts_only_the_two_absent_practices_then_is_noop(): void
    {
        $service = $this->service(); $before = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($before, $this->snapshot()); self::assertSame('before', $plan['state']);
        $expected = $this->expectedCounts(); self::assertSame(3, $expected['pages']); self::assertSame(2, $expected['inserts']);
        self::assertCount($expected['pages'] + $expected['text_blocks'], $plan['updates']); self::assertCount(2, $plan['inserts']);
        self::assertSame([], $plan['deletes'] ?? []);
        foreach (['pages', 'text_blocks'] as $table) { self::assertCount($expected[$table], array_filter($plan['updates'], static fn ($u) => $u['table'] === $table)); }
        foreach ($plan['updates'] as $u) { self::assertSame($u['table'] === 'pages' ? ['text'] : ['body'], array_keys($u['before'])); }
        self::assertCount(count($plan['updates']), array_unique(array_map(static fn ($u) => $u['table'].':'.$u['id'], $plan['updates'])));
        self::assertSame([0, 0], array_map(static fn ($id) => DB::table('text_blocks')->where('page_id', $id)->where('locale', 'uk')->where('type', 'practice-set')->count(), [86, 84]));
        self::assertSame('4ec54834-db1f-55f8-983a-e6537527e477', DB::table('text_blocks')->where('page_id', 80)->where('type', 'practice-set')->value('uuid'));
        foreach ($plan['inserts'] as $n => $insert) {
            $i = $n + 1; $fields = $insert['fields'];
            self::assertSame('text_blocks', $insert['table']); self::assertSame(Package::OWNERS[$i], $insert['seeder']);
            self::assertSame(Package::PAGE_IDS[$i], $fields['page_id']); self::assertSame('uk', $fields['locale']);
            self::assertSame('practice-set', $fields['type']); self::assertSame('left', $fields['column']);
            self::assertSame(['A2', 'B1'][$n], $fields['level']); self::assertSame([8, 7][$n], $fields['sort_order']);
        }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(2, $result['inserted']);
        self::assertSame($expected['pages'] + $expected['text_blocks'], $result['updated']);
        $afterPlan = $service->savePlan($this->file('after')); self::assertSame('after', $afterPlan['state']);
        self::assertSame([], $afterPlan['updates']); self::assertSame([], $afterPlan['inserts']); self::assertSame($plan['protected'], $afterPlan['protected']);
        $after = $this->snapshot(); self::assertCount(count($before['text_blocks']) + 2, $after['text_blocks']);
        foreach ($before['text_blocks'] as $row) {
            $now = (array) DB::table('text_blocks')->where('id', $row['id'])->sole();
            if ($row['locale'] === 'uk') { unset($row['body'], $now['body']); }
            self::assertSame($row, $now, 'Old UUID/order/columns/mixed levels, EN/PL and timestamps remain exact.');
        }
        foreach ($before['pages'] as $row) { $now = (array) DB::table('pages')->where('id', $row['id'])->sole(); unset($row['text'], $now['text']); self::assertSame($row, $now); }
        foreach ($before as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $after[$table], $table); } }
        foreach (Package::PAGE_IDS as $i => $pageId) {
            $practice = (array) DB::table('text_blocks')->where('page_id', $pageId)->where('locale', 'uk')->where('type', 'practice-set')->sole();
            self::assertSame([null, 'A2', 'B1'][$i], $practice['level']); self::assertSame([12, 8, 7][$i], $practice['sort_order']);
        }
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-before-backup'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-after-backup'))['status']);
        self::assertFileDoesNotExist($this->file('unused-before-backup')); self::assertFileDoesNotExist($this->file('unused-after-backup'));
        self::assertSame($after, $this->snapshot());
    }

    public function test_failures_during_updates_and_after_first_insert_roll_back_the_entire_transaction(): void
    {
        foreach (['update', 'insert'] as $stage) {
            $service = $this->service(); $plan = $service->savePlan($this->file('preview-'.$stage)); $before = $this->snapshot();
            $service->failAfterUpdate = $stage === 'update'; $service->failAfterInsert = $stage === 'insert';
            $this->refused(fn () => $service->apply($this->file('preview-'.$stage), $this->file('backup-'.$stage)));
            self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan()); self::assertFileExists($this->file('backup-'.$stage));
        }
    }

    public function test_duplicate_existing_practice_new_uuid_collision_and_partial_release_are_refused(): void
    {
        $service = $this->service(); $plan = $service->plan();
        $duplicate = (array) DB::table('text_blocks')->where('page_id', 80)->where('type', 'practice-set')->sole();
        unset($duplicate['id']); $duplicate['uuid'] = 'm73-duplicate-practice'; DB::table('text_blocks')->insert($duplicate);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', 'm73-duplicate-practice')->delete();
        $collision = $plan['inserts'][0]['fields']; $collision['created_at'] = $collision['updated_at'] = '2026-01-01 12:00:00';
        DB::table('text_blocks')->insert($collision); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('text_blocks')->where('uuid', $collision['uuid'])->delete();
        foreach ($plan['updates'] as $u) { if ($u['seeder'] === Package::OWNERS[0]) { DB::table($u['table'])->where('id', $u['id'])->update($u['after']); } }
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_foreign_page_category_and_continuing_row_identity_are_never_reassigned(): void
    {
        $service = $this->service(); $page = (array) DB::table('pages')->where('id', 80)->sole();
        foreach (['seeder' => 'Foreign\\Owner', 'page_category_id' => 1, 'slug' => 'foreign', 'type' => 'category'] as $field => $value) {
            DB::table('pages')->where('id', 80)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('pages')->where('id', 80)->update([$field => $page[$field]]);
        }
        $category = (array) DB::table('page_categories')->where('id', 16)->sole();
        foreach (['type' => 'page', 'language' => 'en', 'slug' => 'foreign', 'parent_id' => 1] as $field => $value) {
            DB::table('page_categories')->where('id', 16)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('page_categories')->where('id', 16)->update([$field => $category[$field]]);
        }
        $row = (array) DB::table('text_blocks')->where('page_id', 80)->where('type', 'practice-set')->sole();
        foreach (['uuid' => 'm73-lost-uuid', 'level' => 'B2', 'locale' => 'en', 'column' => 'right', 'sort_order' => 99, 'heading' => 'Later heading'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
    }

    public function test_stale_body_progress_versioned_source_and_unregistered_tail_fail_without_new_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $row = (array) DB::table('text_blocks')->where('page_id', 80)->where('type', 'practice-set')->sole();
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']."\n"]); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-body')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-body'));
        DB::table('text_blocks')->where('id', $row['id'])->update(['body' => $row['body']]);
        DB::table('m73_protected_progress')->where('id', 1)->update(['value' => 'Later synthetic progress.']); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-progress')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-progress'));
        DB::table('m73_protected_progress')->where('id', 1)->update(['value' => 'Unchanged synthetic progress.']);
        $source = $this->root.'/'.$this->patch['targets'][0]['path']; $bytes = File::get($source); File::put($source, $bytes."\n"); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-source')); File::put($source, $bytes);
        $tail = $row; unset($tail['id']); $tail['uuid'] = 'm73-unregistered-tail'; $tail['sort_order'] = 99; DB::table('text_blocks')->insert($tail);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_exact_a2_b1_filters_and_generic_null_filter_are_not_substituted_by_display_ranges(): void
    {
        $service = $this->service(); DB::table('questions')->where('id', 7300004)->update(['level' => 'C2']);
        $snapshot = $this->snapshot(); $plan = $service->plan(); self::assertSame($snapshot, $this->snapshot());
        $generic = $plan['pages'][Package::OWNERS[0]]['relations']['questions'];
        self::assertCount(5, $generic); self::assertContains('C2', array_column($generic, 'level'));
        foreach ([[7300009, 'B1'], [7300014, 'A2']] as [$id, $wrongLevel]) {
            $original = DB::table('questions')->where('id', $id)->value('level'); DB::table('questions')->where('id', $id)->update(['level' => $wrongLevel]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('questions')->where('id', $id)->update(['level' => $original]);
        }
        // The fixture deliberately includes EN/PL hero clones. Select the exact UK owner, not an arbitrary first row.
        $heroSource = $this->before['targets'][1]['before']['page']['blocks'][0];
        $rangeRow = DB::table('text_blocks')->where('page_id', 86)->where('locale', 'uk')->where('seeder', Package::OWNERS[1])
            ->where('uuid', M26DetailPackage::uuid(Package::OWNERS[1], $heroSource, 1))->where('type', 'hero')->where('sort_order', 1)->sole();
        self::assertSame('A1–A2', $rangeRow->level, 'Historical display/source range is not rewritten to A2.');
    }

    public function test_exclusive_backup_short_foreign_type_and_unlinked_pools_do_not_authorize_writes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); File::put($this->file('owned-backup'), 'Existing private backup.');
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('owned-backup')));
        self::assertSame('Existing private backup.', File::get($this->file('owned-backup'))); self::assertSame($snapshot, $this->snapshot());
        $q = (array) DB::table('questions')->where('id', 7300004)->sole();
        foreach (['type' => '0', 'seeder' => 'Foreign\\Bank'] as $field => $value) {
            DB::table('questions')->where('id', $q['id'])->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot()); DB::table('questions')->where('id', $q['id'])->update([$field => $q[$field]]);
        }
        DB::table('question_theory_text_blocks')->where('question_uuid', $q['uuid'])->delete(); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_generic_continuing_practice_links_are_not_hidden_as_if_it_were_an_appended_row(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $page = (array) DB::table('pages')->where('id', 80)->sole();
        $rows = DB::table('text_blocks')->where('page_id', 80)->get()->map(static fn ($r) => (array) $r)->all();
        $relations = new ReflectionMethod(Patch::class, 'relations'); $before = $relations->invoke($service, $page, $rows, false);
        $uuid = DB::table('text_blocks')->where('page_id', 80)->where('type', 'practice-set')->value('uuid');
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => 'm73-synthetic-bank-7300000', 'text_block_uuid' => $uuid, 'position' => 9]);
        $after = $relations->invoke($service, $page, $rows, false);
        self::assertNotSame($before, $after); self::assertCount(count($before['question_theory_text_blocks']) + 1, $after['question_theory_text_blocks']);
        self::assertCount(1, array_filter($after['question_theory_text_blocks'], static fn ($r) => $r['text_block_uuid'] === $uuid));
    }
}

class M73SyntheticNativePatch extends Patch
{
    public bool $failAfterUpdate = false;
    public bool $failAfterInsert = false;

    protected function afterUpdate(int $index): void
    {
        if ($this->failAfterUpdate && $index === 0) { throw new RuntimeException('Injected SQLite update failure.'); }
    }

    protected function afterInsert(int $index): void
    {
        if ($this->failAfterInsert && $index === 0) { throw new RuntimeException('Injected SQLite insert failure.'); }
    }
}
