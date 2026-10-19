<?php

namespace Tests\Feature;

use App\Services\M53ContentPatch;
use App\Services\TheoryNativeContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M53PassiveConstructionsPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Tests the generic content writer only; M53 supplement acceptance is separate. */
class NativeContentOnlyFixturePatch extends TheoryNativeContentPatch
{
    public const NAMES = Package::OWNERS;
    protected const ID = 'native-content-only-isolated-fixture';
    protected static function packageClass(): string { return Package::class; }
    protected static function policy(): array
    {
        return (new \ReflectionMethod(M53ContentPatch::class, 'policy'))->invoke(null);
    }
    public string $failure = '';
    protected function afterUpdate(int $index): void
    {
        if ($this->failure === 'update' && $index === 0) { throw new RuntimeException('Injected content update failure.'); }
    }
    protected function afterInsert(int $index): void
    {
        if ($this->failure === 'insert' && $index === 0) { throw new RuntimeException('Injected content insert failure.'); }
    }
}

class TheoryNativeContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $root;
    private string $evidence;
    private array $before;
    private array $patch;
    private array $contract;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [$this->before, $this->patch, $master, $this->contract] = Package::load();
        Package::validate($this->before, $this->patch, $master, $this->contract);
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        $this->root = storage_path('app/native-patch-'.bin2hex(random_bytes(7))); $this->evidence = $this->root.'/evidence';
        File::makeDirectory($this->evidence, 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        foreach (NativeContentOnlyFixturePatch::sourcePaths($this->patch, $this->contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 1, 'slug' => 'passive-voice', 'title' => 'Пасивний стан',
            'type' => 'theory', 'language' => 'uk', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 1, 'seeder' => $owner,
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory',
                'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$old['page']['blocks']] as $position => $source) {
                $fields = ['uuid' => M26DetailPackage::uuid($owner, $source, $position), 'page_id' => Package::PAGE_IDS[$i],
                    'page_category_id' => 1, 'locale' => 'uk', 'type' => $source['type'], 'column' => $source['column'],
                    'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null, 'sort_order' => $position,
                    'level' => $source['level'] ?? null, 'body' => $source['body'], 'seeder' => $owner,
                    'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00'];
                DB::table('text_blocks')->insert($fields);
                if ($position > 0) {
                    foreach (['en', 'pl'] as $locale) {
                        DB::table('text_blocks')->insert(array_replace($fields, ['uuid' => 'protected-'.$i.'-'.$position.'-'.$locale,
                            'locale' => $locale, 'body' => 'Protected '.$locale.' slot '.$position]));
                    }
                }
            }
            File::put($this->root.'/'.$target['path'], M26DetailPackage::json($target['after']));
            $block = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', 1)->sole();
            foreach ($this->contract['targets'][$i]['bank_seeders'] as $bankIndex => $bank) {
                for ($n = 1; $n <= 5; ++$n) {
                    $uuid = 'native-fixture-'.$i.'-'.$bankIndex.'-'.$n;
                    DB::table('questions')->insert(['uuid' => $uuid, 'question' => 'Ізольована тематична умова '.$uuid,
                        'type' => '4', 'level' => Package::LEVELS[$i], 'seeder' => $bank]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $block->uuid, 'position' => $n]);
                }
            }
        }
        DB::connection()->getSchemaBuilder()->create('native_fixture_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('native_fixture_progress')->insert(['value' => 'Original personal progress.']);
    }

    private function service(): NativeContentOnlyFixturePatch
    {
        return new NativeContentOnlyFixturePatch(DB::connection(), $this->root.'/database', $this->evidence);
    }
    private function file(string $name): string { return $this->evidence.'/'.$name.'.json'; }
    private function snapshot(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table); $query = DB::table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $query->orderBy($column); }
            $out[$table] = $query->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function rejectsWithoutWrites(callable $call): void
    {
        $before = $this->snapshot(); $caught = null;
        try { $call(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught); self::assertSame($before, $this->snapshot());
    }

    public function test_variable_10_9_10_slots_and_mixed_levels_apply_once_then_repeat_without_new_backup(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($original, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertSame([10, 9, 10], Package::CONTINUING_COUNTS);
        self::assertSame([92, 95, 96], array_column(array_column($plan['pages'], 'page'), 'id'));
        self::assertSame(32, DB::table('text_blocks')->where('locale', 'uk')->count());
        self::assertSame(58, DB::table('text_blocks')->whereIn('locale', ['en', 'pl'])->count());
        self::assertCount(3, $plan['inserts']); self::assertSame(array_keys($original), array_keys($plan['protected']));
        self::assertArrayNotHasKey('question_projection', $plan, 'This is explicitly the content-only primitive, not supplement acceptance.');
        foreach ($plan['updates'] as $change) { self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after'])); }
        self::assertSame(['B1', 'B1', 'B2'], array_column(array_column($plan['inserts'], 'fields'), 'level'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(3, $result['inserted']);
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        foreach ($original as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); } }
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('unused')));
        self::assertFileDoesNotExist($this->file('unused')); self::assertSame($current, $this->snapshot());
    }

    public function test_stale_bodies_foreign_metadata_or_ancestry_refuse_before_any_backup(): void
    {
        $row = (array) DB::table('text_blocks')->where('page_id', 92)->where('locale', 'uk')->where('sort_order', 6)->sole();
        self::assertSame('B2', $row['level']);
        foreach (['body' => 'Manual newer body', 'locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => 95,
            'page_category_id' => 999, 'sort_order' => 999, 'level' => 'B1', 'heading' => 'Foreign heading', 'css_class' => 'foreign'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]);
            $this->rejectsWithoutWrites(fn () => $this->service()->savePlan($this->file('reject-'.$field)));
            self::assertFileDoesNotExist($this->file('reject-'.$field));
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        DB::table('page_categories')->where('id', 1)->update(['slug' => 'foreign-category']);
        $this->rejectsWithoutWrites(fn () => $this->service()->plan());
    }

    public function test_update_insert_and_backup_collision_failures_are_transactional(): void
    {
        foreach (['update', 'insert'] as $phase) {
            $service = $this->service(); $service->failure = $phase; $service->savePlan($this->file('preview-'.$phase));
            $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertFileExists($this->file('backup-'.$phase)); $bytes = File::get($this->file('backup-'.$phase));
            $this->rejectsWithoutWrites(fn () => $this->service()->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertSame($bytes, File::get($this->file('backup-'.$phase)));
        }
    }

    public function test_saved_preview_detects_non_target_progress_question_or_source_drift_and_partial_package(): void
    {
        foreach (['native_fixture_progress' => 'value', 'questions' => 'question'] as $table => $field) {
            $service = $this->service(); $service->savePlan($this->file('preview-'.$table));
            $row = (array) DB::table($table)->orderBy('id')->first(); DB::table($table)->where('id', $row['id'])->update([$field => 'Unapproved edit']);
            $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview-'.$table), $this->file('no-backup-'.$table)));
            self::assertFileDoesNotExist($this->file('no-backup-'.$table)); DB::table($table)->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        $change = array_values(array_filter($plan['updates'], static fn ($u) => $u['table'] === 'text_blocks'))[0];
        DB::table('text_blocks')->where('id', $change['id'])->update($change['after']);
        $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-partial-backup')));
        self::assertFileDoesNotExist($this->file('no-partial-backup'));
        DB::table('text_blocks')->where('id', $change['id'])->update($change['before']);
        File::append($this->root.'/app/Services/M26ContentPatch.php', "\n// unapproved source drift\n");
        $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-source-backup')));
        self::assertFileDoesNotExist($this->file('no-source-backup'));
    }
}
