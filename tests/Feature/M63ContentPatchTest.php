<?php

namespace Tests\Feature;

use App\Services\M63ContentPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M63RelativeCompletionLinkingPackage as Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real unchanged M26 transaction against isolated SQLite; synthetic bank rows are not live-bank acceptance. */
class M63ContentPatchTest extends TestCase
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
        $this->root = storage_path('app/m63-native-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[1, null, 'foreign-category'], [3, null, 'reported-speech'], [34, null, 'clauses-and-linking-words']] as [$id, $parent, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => $parent, 'slug' => $slug,
                'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00'; $questionId = 5900000;
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'seeder' => $target['identity'], 'slug' => $old['slug'], 'title' => $old['page']['title'],
                'text' => $old['page']['subtitle_text'], 'type' => 'theory']);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$old['page']['blocks']] as $position => $config) {
                DB::table('text_blocks')->insert(['uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk',
                    'seeder' => $target['identity'], 'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null,
                    'css_class' => $config['css_class'] ?? null, 'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $heroUuid = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm63-synthetic-bank-'.$questionId;
                    DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid, 'question' => 'Ізольоване питання.',
                        'level' => Package::LEVELS[$i], 'difficulty' => Package::LEVELS[$i] === 'B2' ? 4 : 3,
                        'type' => '4', 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $heroUuid,
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            $foreign = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->first();
            foreach (['en', 'pl'] as $locale) {
                unset($foreign['id']); $foreign['uuid'] = 'm63-nontarget-'.$i.'-'.$locale; $foreign['locale'] = $locale;
                $foreign['body'] = 'Unchanged '.$locale; DB::table('text_blocks')->insert($foreign);
            }
        }
    }

    private function service(): M63SyntheticNativePatch { return new M63SyntheticNativePatch(DB::connection(), $this->root.'/database', $this->private); }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
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
    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected finite M63 refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }

    public function test_inherited_transaction_explicit_metadata_and_teaching_inserts_repeated_noop_and_zero_question_writes(): void
    {
        $service = $this->service(); $old = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(27, $plan['updates']); self::assertCount(3, $plan['inserts']);
        self::assertCount(3, array_filter($plan['inserts'], static fn ($row) => $row['fields']['type'] === 'practice-set'));
        $metadata = array_values(array_filter($plan['updates'], static fn ($row) => $row['table'] === 'text_blocks'
            && array_diff_assoc(array_diff_key($row['before'], ['body' => true]), array_diff_key($row['after'], ['body' => true])) !== []));
        self::assertCount(15, $metadata);
        foreach ($metadata as $update) {
            self::assertContains($update['seeder'], Package::OWNERS);
            self::assertSame(['body', 'type', 'heading'], array_keys($update['before']));
            self::assertSame(['body', 'type', 'heading'], array_keys($update['after']));
            self::assertSame('box', $update['before']['type']); self::assertNull($update['after']['heading']);
            self::assertContains($update['after']['type'], ['forms-grid', 'comparison-table', 'usage-panels', 'mistakes-grid', 'summary-list']);
            self::assertNull(DB::table('text_blocks')->where('id', $update['id'])->value('level'));
        }
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        foreach ($old as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); } }
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-backup'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-after-backup'))['status']);
        self::assertFileDoesNotExist($this->file('unused-backup')); self::assertFileDoesNotExist($this->file('unused-after-backup'));
    }

    public function test_update_or_insert_failure_rolls_back_all_fifteen_box_metadata_transitions_and_every_new_slot(): void
    {
        $service = $this->service(); $plan = $service->savePlan($this->file('preview')); $before = $this->snapshot();
        $service->failAfterInsert = true;
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('backup')));
        self::assertSame($before, $this->snapshot()); self::assertSame($plan, $service->plan());
    }

    public function test_stale_body_wrong_ancestry_and_unregistered_uk_tail_are_refused_without_writes(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $original = DB::table('text_blocks')->where('page_id', 32)->where('sort_order', 4)->first();
        DB::table('text_blocks')->where('id', $original->id)->update(['heading' => 'Manual changed focus']);
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('refused')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('refused'));
        DB::table('text_blocks')->where('id', $original->id)->update(['heading' => $original->heading]);
        DB::table('pages')->where('id', 32)->update(['page_category_id' => 1]);
        $this->refused(fn () => $service->plan()); DB::table('pages')->where('id', 32)->update(['page_category_id' => 34]);
        $row = (array) $original; unset($row['id']); $row['uuid'] = 'unregistered-tail'; $row['sort_order'] = 99;
        DB::table('text_blocks')->insert($row); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_relation_exclusion_is_exact_and_never_hides_foreign_or_edited_appended_rows(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $service->apply($this->file('preview'), $this->file('backup'));
        $page = (array) DB::table('pages')->where('id', 32)->sole();
        $rows = DB::table('text_blocks')->where('page_id', 32)->get()->map(static fn ($row) => (array) $row)->all();
        $relations = new \ReflectionMethod(Patch::class, 'relations');
        $expected = $relations->invoke($service, $page, $rows, false);
        $appended = array_values(array_filter($rows, static fn ($row) => $row['locale'] === 'uk' && $row['sort_order'] === 8))[0];
        $questionUuid = DB::table('questions')->value('uuid');
        DB::table('question_theory_text_blocks')->insert(['question_uuid' => $questionUuid, 'text_block_uuid' => $appended['uuid'], 'position' => 9]);
        self::assertSame($expected, $relations->invoke($service, $page, $rows, false), 'Only an exact declared appended row is excluded.');
        foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'type' => 'box', 'column' => 'right', 'level' => 'B2', 'page_id' => 999, 'page_category_id' => 999, 'body' => 'Manual changed body', 'sort_order' => 99] as $field => $value) {
            $changed = $rows; foreach ($changed as &$row) { if ($row['uuid'] === $appended['uuid']) { $row[$field] = $value; } } unset($row);
            self::assertNotSame($expected, $relations->invoke($service, $page, $changed, false), $field.' cannot disappear from relation protection');
        }
    }
}

class M63SyntheticNativePatch extends Patch
{
    public bool $failAfterInsert = false;
    protected function afterInsert(int $index): void
    {
        if ($this->failAfterInsert && $index === 0) { throw new RuntimeException('Injected SQLite transaction failure.'); }
    }
}
