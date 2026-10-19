<?php

namespace Tests\Feature;

use App\Services\M26ContentPatch;
use App\Services\M56ContentPatch;
use App\Services\M56HrefCorrectionPatch;
use App\Support\M26DetailPackage;
use App\Support\M56GerundSimplePassivesPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M56HrefIsolatedPatch extends M56HrefCorrectionPatch
{
    public array $fixtureBaseline = [];
    public bool $fail = false;
    protected function baselineEvidence(): array { return $this->fixtureBaseline; }
    protected function sourceAudit(string $root): array { return []; }
    protected function afterUpdate(int $index): void
    {
        if ($this->fail && $index === 0) { throw new RuntimeException('Injected href update failure.'); }
    }
}

/** Same M26 transaction in an owned SQLite-only after-1.0.0 fixture. */
class M56HrefCorrectionPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $fixture;
    private array $delta;
    private array $baseline;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [, $patch, , $contract] = Package::load(); $this->delta = M56HrefCorrectionPatch::correction(base_path());
        $this->fixture = storage_path('app/m56-href-'.bin2hex(random_bytes(7)));
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->fixture.'/evidence', 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->fixture);
        $paths = [...M56ContentPatch::sourcePaths($patch, $contract), M56HrefCorrectionPatch::DELTA,
            'app/Services/M56HrefCorrectionPatch.php', 'app/Console/Commands/PatchM56Hrefs.php',
            ...array_keys($this->delta['source_artifacts'])];
        foreach (array_unique($paths) as $path) {
            File::makeDirectory(dirname($this->fixture.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->fixture.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 1, 'slug' => 'passive-voice', 'title' => 'Пасив', 'type' => 'theory', 'language' => 'uk']);
        DB::table('page_categories')->insert(['id' => 17, 'parent_id' => 1, 'slug' => 'passive-voice-infinitives-gerund',
            'title' => 'Інфінітив та герундій', 'type' => 'theory', 'language' => 'uk']);
        foreach ($patch['targets'] as $i => $target) {
            $definition = $target['after']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'seeder' => $owner, 'type' => 'theory', 'slug' => $target['slug'], 'title' => $definition['page']['title'],
                'text' => $definition['page']['subtitle_text'], 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html']], ...$definition['page']['blocks']];
            foreach ($configs as $position => $config) {
                $change = array_values(array_filter($this->delta['updates'], fn ($c) => $c['identity'] === $owner && $c['source_index'] + 1 === $position))[0] ?? null;
                DB::table('text_blocks')->insert(['id' => $change['id'] ?? (50000 + $i * 100 + $position),
                    'uuid' => $change['uuid'] ?? M26DetailPackage::uuid($owner, $config, $position), 'page_id' => Package::PAGE_IDS[$i],
                    'page_category_id' => Package::CATEGORY_IDS[$i], 'seeder' => $owner, 'locale' => 'uk',
                    'type' => $config['type'], 'column' => $config['column'], 'heading' => $config['heading'] ?? null,
                    'css_class' => $config['css_class'] ?? null, 'level' => $config['level'] ?? null, 'sort_order' => $position,
                    'body' => $change['before_body'] ?? $config['body'], 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            }
        }
        DB::connection()->getSchemaBuilder()->create('m56_href_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m56_href_progress')->insert(['value' => 'Personal state.']);
        $this->baseline = ['sql_after' => $this->fingerprints(), 'normalized_before' => $this->fingerprints(),
            'runtime' => ['fixture' => 'isolated-sqlite-only'], 'independent_before_sha256' => M56ContentPatch::BEFORE_SHA];
    }

    private function service(): M56HrefIsolatedPatch
    {
        $patch = new M56HrefIsolatedPatch(DB::connection(), $this->fixture.'/database', $this->fixture.'/evidence');
        $patch->fixtureBaseline = $this->baseline; return $patch;
    }
    private function file(string $name): string { return $this->fixture.'/evidence/'.$name.'.json'; }
    private function snapshot(): array
    {
        $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables); $out = [];
        foreach ($tables as $table) {
            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table); $q = DB::table($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $q->orderBy($column); }
            $out[$table] = $q->get()->map(fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function fingerprints(): array
    {
        $out = [];
        foreach ($this->snapshot() as $table => $rows) {
            $hash = hash_init('sha256'); foreach ($rows as $row) { hash_update($hash, M26ContentPatch::digest($row)."\n"); }
            $out[$table] = ['count' => count($rows), 'sha256' => hash_final($hash)];
        }
        return $out;
    }
    private function rejects(callable $operation): void
    {
        $before = $this->snapshot(); $caught = null;
        try { $operation(); } catch (RuntimeException $error) { $caught = $error; }
        self::assertInstanceOf(RuntimeException::class, $caught); self::assertSame($before, $this->snapshot());
    }

    public function test_four_exact_body_updates_zero_inserts_and_two_noops_preserve_all_page_fields_and_timestamps(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame('before', $plan['state']); self::assertSame(M56HrefCorrectionPatch::IDS, array_column($plan['updates'], 'id'));
        self::assertSame([], $plan['inserts']); self::assertSame(array_keys($original), array_keys($plan['protected']));
        foreach ($plan['updates'] as $change) { self::assertSame('text_blocks', $change['table']); self::assertSame(['body'], array_keys($change['after'])); }
        self::assertSame(['status' => 'applied', 'updated' => 4, 'inserted' => 0, 'backup' => $this->file('backup')],
            $service->apply($this->file('preview'), $this->file('backup')));
        $current = $this->snapshot(); self::assertSame($original['pages'], $current['pages']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('preview'), $this->file('unused')));
        self::assertFileDoesNotExist($this->file('unused'));
        $after = $service->savePlan($this->file('after')); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $service->apply($this->file('after'), $this->file('unused-2')));
        self::assertFileDoesNotExist($this->file('unused-2')); self::assertSame($current, $this->snapshot());
        $verification = $service->verification(); self::assertSame($this->baseline['sql_after'], $verification['normalized_after_1_0_0']);
        self::assertSame($this->fingerprints(), $verification['sql_after']); self::assertSame(0, $verification['db_writes']);
    }

    public function test_stale_body_foreign_metadata_and_partial_transition_refuse_before_backup(): void
    {
        $row = (array) DB::table('text_blocks')->where('id', 3701)->sole();
        foreach (['body' => 'A newer manual body', 'locale' => 'en', 'seeder' => 'Foreign', 'uuid' => 'Foreign',
            'page_id' => 102, 'page_category_id' => 17, 'type' => 'box', 'column' => 'right', 'level' => 'B2',
            'heading' => 'Foreign', 'css_class' => 'Foreign', 'sort_order' => 999] as $field => $value) {
            DB::table('text_blocks')->where('id', 3701)->update([$field => $value]);
            $this->rejects(fn () => $this->service()->savePlan($this->file('bad-'.$field)));
            self::assertFileDoesNotExist($this->file('bad-'.$field)); DB::table('text_blocks')->where('id', 3701)->update([$field => $row[$field]]);
        }
        DB::table('text_blocks')->where('id', 3701)->update(['body' => $this->delta['updates'][0]['after_body']]);
        $this->rejects(fn () => $this->service()->plan());
        $this->rejects(fn () => $this->service()->verification());
    }

    public function test_full_tables_protect_progress_page_text_non_target_body_and_source_drift(): void
    {
        foreach ([['m56_href_progress', 1, 'value'], ['pages', 102, 'text'], ['text_blocks', 50001, 'body']] as [$table, $id, $field]) {
            $row = (array) DB::table($table)->where('id', $id)->sole();
            DB::table($table)->where('id', $id)->update([$field => 'Not authorized by href correction']);
            $this->rejects(fn () => $this->service()->plan());
            DB::table($table)->where('id', $id)->update([$field => $row[$field]]);
        }
        $service = $this->service(); $service->savePlan($this->file('preview'));
        File::append($this->fixture.'/app/Services/M26ContentPatch.php', "\n// Source drift.\n");
        $this->rejects(fn () => $service->apply($this->file('preview'), $this->file('no-backup')));
        self::assertFileDoesNotExist($this->file('no-backup'));
    }

    public function test_transaction_rolls_back_and_existing_backup_is_never_overwritten_and_restore_is_denied(): void
    {
        $service = $this->service(); $service->fail = true; $service->savePlan($this->file('preview'));
        $this->rejects(fn () => $service->apply($this->file('preview'), $this->file('backup')));
        self::assertFileExists($this->file('backup')); $bytes = File::get($this->file('backup'));
        $this->rejects(fn () => $this->service()->apply($this->file('preview'), $this->file('backup')));
        self::assertSame($bytes, File::get($this->file('backup')));
        $this->rejects(fn () => $this->service()->restore($this->file('backup')));
    }

    public function test_correction_is_only_four_bodies_and_seven_explicit_native_nested_destinations(): void
    {
        $topics = ['present-continuous', 'present-perfect', 'past-continuous', 'past-perfect', 'future-simple', 'future-continuous', 'future-perfect'];
        $expected = array_map(fn ($topic) => '/theory/passive-voice/passive-voice-tenses/theory-passive-voice-'.$topic, $topics);
        $occurrences = 0;
        foreach ($this->delta['updates'] as $change) {
            $before = $change['before_body']; $after = $change['after_body'];
            foreach ($topics as $n => $topic) {
                $old = '/theory/passive-voice/theory-passive-voice-'.$topic;
                $occurrences += substr_count($before, $old); $before = str_replace($old, $expected[$n], $before);
                self::assertStringNotContainsString($old, $after);
            }
            self::assertSame($before, $after);
        }
        self::assertSame(16, $occurrences);
    }
}
