<?php

namespace Tests\Feature;

use App\Services\M56ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M56GerundSimplePassivesPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M56FailureFixturePatch extends M56ContentPatch
{
    public string $failure = '';
    protected function afterUpdate(int $index): void
    {
        if ($this->failure === 'update' && $index === 0) { throw new RuntimeException('Injected M56 update failure.'); }
    }
    protected function afterInsert(int $index): void
    {
        if ($this->failure === 'insert' && $index === 0) { throw new RuntimeException('Injected M56 insert failure.'); }
    }
}

/** Actual M56 writer policy exercised only in isolated SQLite with private copies. */
class M56ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $fixture;
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
        $this->fixture = storage_path('app/m56-patch-'.bin2hex(random_bytes(7)));
        $this->evidence = $this->fixture.'/evidence'; File::makeDirectory($this->evidence, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->fixture);
        foreach (M56ContentPatch::sourcePaths($this->patch, $this->contract) as $path) {
            File::makeDirectory(dirname($this->fixture.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->fixture.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 1, 'slug' => 'passive-voice', 'title' => 'Пасивний стан',
            'type' => 'theory', 'language' => 'uk', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        DB::table('page_categories')->insert(['id' => 17, 'slug' => 'passive-voice-infinitives-gerund', 'title' => 'Інфінітив та герундій у пасиві',
            'parent_id' => 1, 'type' => 'theory', 'language' => 'uk', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
        foreach ($this->patch['targets'] as $i => $target) {
            $old = $this->before['targets'][$i]['before']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'seeder' => $owner,
                'slug' => $old['slug'], 'title' => $old['page']['title'], 'text' => $old['page']['subtitle_text'], 'type' => 'theory',
                'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $old['page']['subtitle_html'],
                'level' => $old['page']['subtitle_level'] ?? null, 'uuid_key' => $old['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$old['page']['blocks']] as $position => $source) {
                $fields = ['uuid' => M26DetailPackage::uuid($owner, $source, $position), 'page_id' => Package::PAGE_IDS[$i],
                    'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk', 'type' => $source['type'], 'column' => $source['column'],
                    'heading' => $source['heading'] ?? null, 'css_class' => $source['css_class'] ?? null, 'sort_order' => $position,
                    'level' => $source['level'] ?? null, 'body' => $source['body'], 'seeder' => $owner,
                    'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00'];
                DB::table('text_blocks')->insert($fields);
                if ($position > 0) {
                    foreach (['en', 'pl'] as $locale) {
                        DB::table('text_blocks')->insert(array_replace($fields,
                            ['uuid' => 'm56-protected-'.$i.'-'.$position.'-'.$locale, 'locale' => $locale, 'body' => 'Protected '.$locale]));
                    }
                }
            }
            File::put($this->fixture.'/'.$target['path'], M26DetailPackage::json($target['after']));
            $block = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('locale', 'uk')->where('sort_order', 1)->sole();
            foreach ($this->contract['targets'][$i]['bank_seeders'] as $bankIndex => $bank) {
                for ($n = 1; $n <= 5; ++$n) {
                    $uuid = 'm56-isolated-'.$i.'-'.$bankIndex.'-'.$n;
                    DB::table('questions')->insert(['uuid' => $uuid, 'question' => 'Ізольована тематична умова '.$uuid,
                        'type' => '4', 'level' => $this->contract['targets'][$i]['level'], 'seeder' => $bank]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $block->uuid, 'position' => $n]);
                }
            }
        }
        DB::connection()->getSchemaBuilder()->create('m56_fixture_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m56_fixture_progress')->insert(['value' => 'Original personal progress.']);
    }

    private function service(): M56FailureFixturePatch { return new M56FailureFixturePatch(DB::connection(), $this->fixture.'/database', $this->evidence); }
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
    private function rejectsWithoutWrites(callable $operation): void
    {
        $before = $this->snapshot(); $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught); self::assertSame($before, $this->snapshot());
    }

    public function test_actual_10_9_10_slots_mixed_levels_and_three_practices_apply_once_with_zero_question_writes(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($original, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertSame([10, 9, 10], Package::CONTINUING_COUNTS);
        self::assertSame([102, 113, 110], array_column(array_column($plan['pages'], 'page'), 'id'));
        self::assertSame(32, DB::table('text_blocks')->where('locale', 'uk')->count());
        self::assertSame(58, DB::table('text_blocks')->whereIn('locale', ['en', 'pl'])->count());
        self::assertCount(3, $plan['inserts']); self::assertSame(array_keys($original), array_keys($plan['protected']));
        self::assertArrayNotHasKey('question_projection', $plan); self::assertArrayNotHasKey('question_supplement', $plan);
        foreach ($plan['updates'] as $change) { self::assertSame($change['table'] === 'pages' ? ['text'] : ['body'], array_keys($change['after'])); }
        self::assertSame(['B2', 'A2', 'A2'], array_column(array_column($plan['inserts'], 'fields'), 'level'));
        $result = $service->apply($this->file('preview'), $this->file('backup'));
        self::assertSame('applied', $result['status']); self::assertSame(3, $result['inserted']);
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        $current = $this->snapshot();
        foreach ($original as $table => $rows) { if (!in_array($table, ['pages', 'text_blocks'], true)) { self::assertSame($rows, $current[$table], $table); } }
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('unused')));
        self::assertFileDoesNotExist($this->file('unused')); self::assertSame($current, $this->snapshot());
    }

    public function test_stale_body_foreign_owner_locale_level_and_wrong_ancestry_refuse_without_writes(): void
    {
        $row = (array) DB::table('text_blocks')->where('page_id', 102)->where('locale', 'uk')->where('sort_order', 5)->sole();
        self::assertSame('B2', $row['level']);
        foreach (['body' => 'Newer manual body', 'locale' => 'en', 'seeder' => 'ForeignOwner', 'page_id' => 113,
            'page_category_id' => 999, 'sort_order' => 999, 'level' => 'B1', 'heading' => 'Foreign heading', 'css_class' => 'foreign'] as $field => $value) {
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $value]);
            $this->rejectsWithoutWrites(fn () => $this->service()->savePlan($this->file('reject-'.$field)));
            self::assertFileDoesNotExist($this->file('reject-'.$field));
            DB::table('text_blocks')->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        DB::table('page_categories')->where('id', 17)->update(['parent_id' => null]);
        $this->rejectsWithoutWrites(fn () => $this->service()->plan());
        DB::table('page_categories')->where('id', 17)->update(['parent_id' => 1]);
        DB::table('page_categories')->where('id', 1)->update(['slug' => 'foreign-category']);
        $this->rejectsWithoutWrites(fn () => $this->service()->plan());
    }

    public function test_transaction_failures_roll_back_all_rows_and_exclusive_backup_never_overwrites(): void
    {
        foreach (['update', 'insert'] as $phase) {
            $service = $this->service(); $service->failure = $phase; $service->savePlan($this->file('preview-'.$phase));
            $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertFileExists($this->file('backup-'.$phase)); $bytes = File::get($this->file('backup-'.$phase));
            $this->rejectsWithoutWrites(fn () => $this->service()->apply($this->file('preview-'.$phase), $this->file('backup-'.$phase)));
            self::assertSame($bytes, File::get($this->file('backup-'.$phase)));
        }
    }

    public function test_stale_plan_progress_question_source_and_partial_release_drift_is_rejected_before_backup(): void
    {
        foreach (['m56_fixture_progress' => 'value', 'questions' => 'question'] as $table => $field) {
            $service = $this->service(); $service->savePlan($this->file('preview-'.$table));
            $row = (array) DB::table($table)->orderBy('id')->first(); DB::table($table)->where('id', $row['id'])->update([$field => 'Unapproved edit']);
            $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview-'.$table), $this->file('no-backup-'.$table)));
            self::assertFileDoesNotExist($this->file('no-backup-'.$table)); DB::table($table)->where('id', $row['id'])->update([$field => $row[$field]]);
        }
        $service = $this->service(); $plan = $service->savePlan($this->file('preview'));
        $change = array_values(array_filter($plan['updates'], static fn ($update) => $update['table'] === 'text_blocks'))[0];
        DB::table('text_blocks')->where('id', $change['id'])->update($change['after']);
        $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-partial-backup')));
        self::assertFileDoesNotExist($this->file('no-partial-backup'));
        DB::table('text_blocks')->where('id', $change['id'])->update($change['before']);
        File::append($this->fixture.'/app/Services/M26ContentPatch.php', "\n// Unapproved source drift\n");
        $this->rejectsWithoutWrites(fn () => $service->apply($this->file('preview'), $this->file('no-source-backup')));
        self::assertFileDoesNotExist($this->file('no-source-backup'));
    }
}
