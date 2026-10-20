<?php

namespace Tests\Feature;

use App\Services\M72ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M72PartitivesNegativesGeography as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** The existing M26 writer on isolated SQLite; synthetic rows do not assert acceptance of live selected teaching banks. */
class M72ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $private;
    private array $patch;
    private array $before;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment());
        self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('2bcd468b25af3c44dcf34ccee0e012f3f683335d', Package::BASE_SHA);
        self::assertSame([88, 87, 83], Package::PAGE_IDS);
        self::assertSame([14, 13, 15], Package::CONTINUING_COUNTS);
        self::assertSame(42, array_sum(Package::CONTINUING_COUNTS));
        [$this->before, $this->patch, , $contract] = Package::load();
        $this->root = storage_path('app/m72-native-fixture-'.bin2hex(random_bytes(7)));
        $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[1, 'foreign-category'], [16, 'imennyky-artykli-ta-kilkist']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00'; $questionId = 6000000;
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'seeder' => $target['identity'], 'slug' => $old['slug'], 'title' => $old['page']['title'],
                'text' => $old['page']['subtitle_text'] ?? null, 'type' => 'theory']);
            self::assertArrayHasKey('subtitle_html', $old['page']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle']];
            foreach ($old['page']['blocks'] as $slot => $config) { $configs[$slot + 1] = $config; }
            foreach ($configs as $position => $config) {
                DB::table('text_blocks')->insert(['uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk',
                    'seeder' => $target['identity'], 'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                    'css_class' => $config['css_class'] ?? null, 'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $heroUuid = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm72-synthetic-bank-'.$questionId;
                    DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid, 'question' => 'Ізольоване питання.',
                        'level' => null, 'difficulty' => 2, 'type' => '4', 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $heroUuid,
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->first();
            foreach (['en', 'pl'] as $locale) {
                unset($foreign['id']); $foreign['uuid'] = 'm72-nontarget-'.$i.'-'.$locale; $foreign['locale'] = $locale;
                $foreign['body'] = 'Unchanged '.$locale; DB::table('text_blocks')->insert($foreign);
            }
        }
        DB::connection()->getSchemaBuilder()->dropIfExists('m72_protected_progress');
        DB::connection()->getSchemaBuilder()->create('m72_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m72_protected_progress')->insert(['value' => 'Synthetic progress must remain literal.']);
    }

    private function service(): M72SyntheticNativePatch
    {
        return new M72SyntheticNativePatch(DB::connection(), $this->root.'/database', $this->private);
    }

    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }

    private function snapshot(): array
    {
        $out = [];
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            $query = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected finite M72 refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    /** Derive the actual changed-body allowlist, rather than importing the preceding release's counts. */
    private function expectedUpdateCounts(): array
    {
        $counts = ['pages' => 0, 'text_blocks' => 0];
        foreach ($this->patch['targets'] as $i => $target) {
            $before = $this->before['targets'][$i]['before']['page']; $after = $target['after']['page'];
            if ($before['subtitle_text'] !== $after['subtitle_text']) { $counts['pages']++; }
            if ($before['subtitle_html'] !== $after['subtitle_html']) { $counts['text_blocks']++; }
            self::assertCount(count($before['blocks']), $after['blocks']);
            foreach ($before['blocks'] as $slot => $block) {
                if ($block['body'] !== $after['blocks'][$slot]['body']) { $counts['text_blocks']++; }
            }
        }
        return $counts;
    }

    public function test_body_only_transaction_updates_continuing_rows_and_repeated_noop_preserves_every_question_and_progress_family(): void
    {
        $service = $this->service(); $old = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertSame([], $plan['inserts']);
        self::assertSame([], $plan['deletes'] ?? []);
        $expected = $this->expectedUpdateCounts();
        self::assertSame(['pages' => 3, 'text_blocks' => 45], $expected);
        self::assertCount(array_sum($expected), $plan['updates']);
        foreach ($expected as $table => $count) {
            self::assertCount($count, array_filter($plan['updates'], static fn ($change) => $change['table'] === $table));
        }
        self::assertCount(count($plan['updates']), array_unique(array_map(static fn ($u) => $u['table'].':'.$u['id'], $plan['updates'])));
        self::assertSame([12, 11, 13], Package::PRACTICE_SOURCE_INDICES);
        foreach (Package::PAGE_IDS as $i => $pageId) {
            $practice = (array) DB::table('text_blocks')->where('page_id', $pageId)->where('locale', 'uk')->where('type', 'practice-set')->sole();
            self::assertSame(Package::PRACTICE_UUIDS[$i], $practice['uuid']);
            self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i] + 1, $practice['sort_order']);
            self::assertNull($practice['level']);
        }
        foreach ($plan['updates'] as $update) {
            self::assertSame($update['table'] === 'pages' ? ['text'] : ['body'], array_keys($update['before']));
            self::assertSame(array_keys($update['before']), array_keys($update['after']));
        }
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']);
        self::assertSame(array_sum($expected), $result['updated']); self::assertSame(0, $result['inserted']);
        $after = $service->savePlan($this->file('after'));
        self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        self::assertCount(count($old['text_blocks']), $current['text_blocks']);
        foreach ($old['text_blocks'] as $row) {
            $matches = array_values(array_filter($current['text_blocks'], static fn ($value) => $value['id'] === $row['id']));
            self::assertCount(1, $matches); $now = $matches[0];
            if ($row['locale'] === 'uk') { unset($row['body'], $now['body']); }
            self::assertSame($row, $now, 'Existing IDs, UUIDs, technical NULL, metadata, EN/PL and timestamps must remain exact.');
        }
        foreach ($old['pages'] as $row) {
            $now = DB::table('pages')->where('id', $row['id'])->first(); $now = (array) $now;
            $i = array_search($row['id'], Package::PAGE_IDS, true);
            self::assertSame($this->patch['targets'][$i]['after']['page']['subtitle_text'], $now['text']);
            unset($row['text'], $now['text']); self::assertSame($row, $now);
        }
        foreach ($old as $table => $rows) {
            if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); }
        }
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-backup'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-after-backup'))['status']);
        self::assertFileDoesNotExist($this->file('unused-backup')); self::assertFileDoesNotExist($this->file('unused-after-backup'));
        self::assertSame($current, $this->snapshot());
    }

    public function test_update_failure_rolls_back_page_text_and_all_continuing_body_updates(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview')); $before = $this->snapshot();
        $service->failAfterUpdate = true;
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('backup')));
        self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan());
        self::assertFileExists($this->file('backup'));
    }

    public function test_duplicate_practice_and_partial_owner_release_are_refused_without_additional_writes(): void
    {
        $service = $this->service(); $plan = $service->plan();
        $duplicate = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[1])->where('locale', 'uk')->where('type', 'practice-set')->sole();
        unset($duplicate['id']); $duplicate['uuid'] = 'm72-foreign-duplicate-practice';
        DB::table('text_blocks')->insert($duplicate); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('text_blocks')->where('uuid', 'm72-foreign-duplicate-practice')->delete();
        foreach ($plan['updates'] as $update) {
            if ($update['seeder'] === Package::OWNERS[0]) { DB::table($update['table'])->where('id', $update['id'])->update($update['after']); }
        }
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_continuing_practice_uuid_level_and_owner_metadata_cannot_be_reassigned(): void
    {
        $service = $this->service();
        foreach (Package::PAGE_IDS as $i => $pageId) {
            $original = (array) DB::table('text_blocks')->where('uuid', Package::PRACTICE_UUIDS[$i])->sole();
            foreach (['uuid' => 'm72-lost-uuid-'.$i, 'level' => 'A2', 'seeder' => 'Foreign\\Owner',
                'page_id' => Package::PAGE_IDS[($i + 1) % 3], 'page_category_id' => 1, 'locale' => 'en',
                'type' => 'box', 'column' => 'right', 'sort_order' => 99] as $field => $value) {
                DB::table('text_blocks')->where('id', $original['id'])->update([$field => $value]);
                $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan());
                self::assertSame($snapshot, $this->snapshot(), $field.' cannot be normalized into an accepted practice.');
                DB::table('text_blocks')->where('id', $original['id'])->update([$field => $original[$field]]);
            }
            self::assertSame($pageId, $original['page_id']);
        }
    }

    public function test_foreign_page_owner_category_locale_and_category_as_page_are_refused(): void
    {
        $service = $this->service();
        $original = (array) DB::table('pages')->where('id', Package::PAGE_IDS[0])->sole();
        foreach (['seeder' => 'Foreign\\CategorySeeder', 'page_category_id' => 1, 'slug' => 'foreign', 'type' => 'category'] as $field => $value) {
            DB::table('pages')->where('id', $original['id'])->update([$field => $value]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('pages')->where('id', $original['id'])->update([$field => $original[$field]]);
        }
        $category = (array) DB::table('page_categories')->where('id', 16)->sole();
        foreach (['type' => 'page', 'language' => 'en', 'slug' => 'articles-and-quantifiers', 'parent_id' => 1] as $field => $value) {
            DB::table('page_categories')->where('id', 16)->update([$field => $value]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('page_categories')->where('id', 16)->update([$field => $category[$field]]);
        }
        $wrongTarget = $this->patch['targets'][0]; $wrongTarget['identity'] = 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCategorySeeder';
        $this->refused(fn () => Patch::assertDefinitionScope($this->before['targets'][0]['before'], $wrongTarget['after'], $wrongTarget, 0));
    }

    public function test_stale_plan_body_or_metadata_and_unregistered_uk_tail_fail_before_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $original = (array) DB::table('text_blocks')->where('uuid', Package::PRACTICE_UUIDS[2])->sole();
        foreach (['body' => $original['body']."\n", 'heading' => 'Manual later edit'] as $field => $value) {
            DB::table('text_blocks')->where('id', $original['id'])->update([$field => $value]);
            $snapshot = $this->snapshot();
            $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-'.$field)));
            self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-'.$field));
            DB::table('text_blocks')->where('id', $original['id'])->update([$field => $original[$field]]);
        }
        $tail = $original; unset($tail['id']); $tail['uuid'] = 'm72-unregistered-tail'; $tail['sort_order'] = 99;
        DB::table('text_blocks')->insert($tail); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_stale_protected_progress_and_changed_versioned_source_are_not_overwritten(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        DB::table('m72_protected_progress')->where('id', 1)->update(['value' => 'Later synthetic progress.']);
        $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-progress')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-progress'));
        DB::table('m72_protected_progress')->where('id', 1)->update(['value' => 'Synthetic progress must remain literal.']);
        $source = $this->root.'/'.$this->patch['targets'][0]['path']; $bytes = File::get($source);
        File::put($source, $bytes."\n"); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-source'));
    }

    public function test_exclusive_backup_is_not_overwritten_and_short_foreign_or_wrong_type_pools_fail(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        File::put($this->file('existing-backup'), 'Already owned private backup.'); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('existing-backup')));
        self::assertSame('Already owned private backup.', File::get($this->file('existing-backup')));
        self::assertSame($snapshot, $this->snapshot());
        $question = (array) DB::table('questions')->where('id', 6000004)->sole();
        foreach (['type' => '0', 'seeder' => 'Foreign\\UnregisteredBank'] as $field => $value) {
            DB::table('questions')->where('id', $question['id'])->update([$field => $value]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan());
            self::assertSame($snapshot, $this->snapshot(), 'Four correct questions plus one foreign question cannot satisfy five.');
            DB::table('questions')->where('id', $question['id'])->update([$field => $question[$field]]);
        }
        $link = (array) DB::table('question_theory_text_blocks')->where('question_uuid', $question['uuid'])->sole();
        DB::table('question_theory_text_blocks')->where('question_uuid', $question['uuid'])->delete();
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('question_theory_text_blocks')->insert($link);
    }

    public function test_null_technical_level_remains_an_absent_bank_filter_and_is_not_rewritten_to_a2(): void
    {
        $service = $this->service();
        foreach (Package::PAGE_IDS as $pageId) {
            self::assertNull(DB::table('text_blocks')->where('page_id', $pageId)->where('locale', 'uk')->where('type', 'practice-set')->value('level'));
        }
        DB::table('questions')->where('id', 6000004)->update(['level' => 'C2']);
        $snapshot = $this->snapshot(); $plan = $service->plan();
        self::assertSame('before', $plan['state']); self::assertSame($snapshot, $this->snapshot());
        $pool = $plan['pages'][Package::OWNERS[0]]['relations']['questions'];
        self::assertCount(5, $pool);
        self::assertContains('C2', array_column($pool, 'level'));
        self::assertSame(4, count(array_filter($pool, static fn ($q) => $q['level'] === null)));
        self::assertSame([], $plan['inserts']);
    }

    public function test_continuing_practice_links_are_never_hidden_by_appended_row_relation_exclusion(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $service->apply($this->file('preview'), $this->file('backup'));
        $page = (array) DB::table('pages')->where('id', Package::PAGE_IDS[0])->sole();
        $rows = DB::table('text_blocks')->where('page_id', $page['id'])->get()->map(static fn ($row) => (array) $row)->all();
        $relations = new ReflectionMethod(Patch::class, 'relations');
        $expected = $relations->invoke($service, $page, $rows, false);
        $practiceUuid = Package::PRACTICE_UUIDS[0]; $questionUuid = DB::table('questions')->value('uuid');
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => $questionUuid, 'text_block_uuid' => $practiceUuid, 'position' => 9]);
        $actual = $relations->invoke($service, $page, $rows, false);
        self::assertNotSame($expected, $actual, 'All three practice rows continue; none is an excluded appended row.');
        self::assertCount(count($expected['question_theory_text_blocks']) + 1, $actual['question_theory_text_blocks']);
        self::assertCount(1, array_filter($actual['question_theory_text_blocks'], static fn ($row) => $row['text_block_uuid'] === $practiceUuid));
    }
}

class M72SyntheticNativePatch extends Patch
{
    public bool $failAfterUpdate = false;

    protected function afterUpdate(int $index): void
    {
        if ($this->failAfterUpdate && $index === 8) {
            throw new RuntimeException('Injected SQLite continuing-update transaction failure.');
        }
    }
}
