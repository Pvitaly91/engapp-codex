<?php

namespace Tests\Feature;

use App\Services\M82WhitespaceCorrectionPatch as Patch;
use App\Support\M26DetailPackage as Digest;
use App\Support\M82CommonMistakesLevelRevision as Package;
use App\Support\M82WhitespaceRevision as Revision;
use App\Support\TheoryNativeContentRelease;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Real frozen six-owner sources on synthetic SQLite, not working-local acceptance. */
class M82WhitespaceCorrectionPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $private;
    private array $patch;
    private array $previous;
    private array $baseline;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk');
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment()); self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName()); self::assertSame('array', config('cache.default'));
        self::assertSame('array', config('session.driver')); self::assertNotSame(base_path(), app()->environmentPath());
        [$before, $this->patch, $master, $contract] = Package::load(); self::assertSame('1.0.2', $master['version']);
        $this->previous = json_decode(File::get(base_path(Revision::PREVIOUS_SOURCE)), true, flags: JSON_THROW_ON_ERROR);
        $this->root = storage_path('app/m82-whitespace-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        foreach ([[9, 'common-mistakes'], [59, 'mixed-revision']] as [$id, $slug]) {
            DB::table('page_categories')->insert(['id' => $id, 'parent_id' => null, 'slug' => $slug, 'title' => $slug, 'type' => 'theory', 'language' => 'uk']);
        }
        $time = '2026-01-01 12:00:00';
        foreach ($this->previous['targets'] as $i => $target) {
            $source = $target['after']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'seeder' => $owner,
                'slug' => $source['slug'], 'title' => $source['page']['title'], 'text' => $source['page']['subtitle_text'], 'type' => 'theory',
                'created_at' => $time, 'updated_at' => $time]);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $source['page']['subtitle_html'],
                'level' => $source['page']['subtitle_level'] ?? null, 'uuid_key' => $source['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$source['page']['blocks']] as $position => $config) {
                $id = $i === 1 && isset(Revision::TARGETS[$position - 1]) ? Revision::TARGETS[$position - 1]['id'] : 820000 + $i * 100 + $position;
                DB::table('text_blocks')->insert(['id' => $id, 'uuid' => Digest::uuid($owner, $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i], 'locale' => 'uk', 'seeder' => $owner,
                    'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'], 'heading' => $config['heading'] ?? null,
                    'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null, 'body' => $config['body'],
                    'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->sole();
            for ($n = 0; $n < Patch::ELIGIBLE_POOL_COUNTS[$i]; ++$n) {
                $id = 8200000 + $i * 100 + $n; $uuid = 'm82-whitespace-synthetic-'.$id;
                DB::table('questions')->insert(['id' => $id, 'uuid' => $uuid, 'question' => 'Ізольований незмінний тематичний fixture.',
                    'type' => '4', 'level' => Package::LEVELS[$i], 'seeder' => $contract['targets'][$i]['bank_seeders'][0],
                    'created_at' => $time, 'updated_at' => $time]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero['uuid'],
                    'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
            }
            unset($hero['id']);
            foreach (['en', 'pl'] as $locale) {
                $hero['uuid'] = 'm82-whitespace-foreign-'.$i.'-'.$locale; $hero['locale'] = $locale; $hero['body'] = 'Keep '.$locale;
                DB::table('text_blocks')->insert($hero);
            }
        }
        DB::connection()->getSchemaBuilder()->create('m82_whitespace_protected_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m82_whitespace_protected_progress')->insert(['value' => 'Exact unchanged progress.']);
        $this->baseline = (new M82SyntheticWhitespaceCorrection(DB::connection(), $this->root.'/database', $this->private))->rawFingerprints();
    }

    private function service(): M82SyntheticWhitespaceCorrection
    {
        $service = new M82SyntheticWhitespaceCorrection(DB::connection(), $this->root.'/database', $this->private);
        $service->baselineSql = $this->baseline; return $service;
    }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function refused(callable $call): void
    {
        $error = null; try { $call(); } catch (RuntimeException $caught) { $error = $caught; }
        self::assertInstanceOf(RuntimeException::class, $error);
    }
    private function snapshot(): array
    {
        $out = []; $tables = DB::connection()->getSchemaBuilder()->getTableListing(schema: 'main', schemaQualified: false); sort($tables);
        foreach ($tables as $table) {
            $q = DB::table($table); $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id', $columns, true) ? ['id'] : $columns as $column) { $q->orderBy($column); }
            $out[$table] = $q->get()->map(static fn ($row) => (array) $row)->all();
        }
        return $out;
    }

    public function test_five_literal_lists_change_only_three_prose_fields_two_bodies_and_no_tasks(): void
    {
        [$before, $active, $master] = Package::load();
        $prior = json_decode(File::get(base_path(Revision::PREVIOUS_MASTER)), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(Revision::PREVIOUS_SOURCE_SHA, hash_file('sha256', base_path(Revision::PREVIOUS_SOURCE)));
        self::assertSame(Revision::PREVIOUS_MASTER_SHA, hash_file('sha256', base_path(Revision::PREVIOUS_MASTER)));
        self::assertSame(Revision::PREVIOUS_CHECKSUMS_SHA, hash_file('sha256', base_path(Revision::PREVIOUS_CHECKSUMS)));
        self::assertSame($master, Revision::reviseMaster($prior)); self::assertCount(3, Revision::changes());
        self::assertSame(5, array_sum(array_map(static fn ($change) => count($change['replacements']), Revision::changes())));
        Revision::assertTransition($this->previous, $active, $master, $before, new TheoryNativeContentRelease(Package::profile()));
        foreach ($master['lessons'] as $i => $lesson) {
            self::assertSame($prior['lessons'][$i]['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]], $lesson['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]);
            self::assertSame($prior['lessons'][$i]['details'], $lesson['details']);
            if ($i !== 1) { self::assertSame($prior['lessons'][$i], $lesson); }
        }
        foreach ([['blocks', 2, 'data', 'sections', 1, 'description'], ['blocks', 2, 'data', 'sections', 1, 'note'], ['blocks', 4, 'data', 'items', 2]] as $path) {
            $a = $prior['lessons'][1]; $b = $master['lessons'][1]; foreach ($path as $key) { $a = $a[$key]; $b = $b[$key]; }
            self::assertSame(preg_replace('/\s+/u', '', $a), preg_replace('/\s+/u', '', $b));
        }
        $mutation = $active; $mutation['targets'][1]['after']['page']['blocks'][4]['body'] .= 'x';
        $this->refused(fn () => Revision::assertTransition($this->previous, $mutation, $master, $before, new TheoryNativeContentRelease(Package::profile())));
    }

    public function test_two_body_updates_are_atomic_raw_backed_up_and_repeat_as_zero_write_noop(): void
    {
        $service = $this->service(); $old = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot()); self::assertSame('before', $plan['state']); self::assertCount(2, $plan['updates']); self::assertSame([], $plan['inserts']);
        self::assertSame([1673, 1675], array_column($plan['updates'], 'id')); self::assertSame(['text_blocks', 'text_blocks'], array_column($plan['updates'], 'table'));
        foreach ($plan['updates'] as $update) { self::assertSame(['body'], array_keys($update['before'])); self::assertSame(['body'], array_keys($update['after'])); }
        foreach ([2 => 1673, 4 => 1675] as $slot => $id) {
            $raw = array_values(array_filter($plan['pages'][Package::OWNERS[1]]['blocks'], static fn ($row) => $row['id'] === $id))[0];
            self::assertSame($this->previous['targets'][1]['after']['page']['blocks'][$slot]['body'], $raw['body']);
        }
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
        self::assertSame($plan, json_decode(File::get($this->file('backup')), true, flags: JSON_THROW_ON_ERROR));
        $expected = $old;
        foreach ($expected['text_blocks'] as &$row) {
            foreach (Revision::TARGETS as $slot => $identity) { if ($row['id'] === $identity['id']) { $row['body'] = $this->patch['targets'][1]['after']['page']['blocks'][$slot]['body']; } }
        }
        unset($row); self::assertSame($expected, $this->snapshot(), 'All other columns, timestamps, courses, questions, translations and rows remain exact.');
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame($plan['protected'], $after['protected']);
        foreach (['preview', 'after'] as $name) {
            self::assertSame('no-op', $service->apply($this->file($name), $this->file('unused-'.$name))['status']); self::assertFileDoesNotExist($this->file('unused-'.$name));
        }
    }

    public function test_either_one_row_partial_correction_and_first_or_last_update_failure_are_refused_or_rolled_back(): void
    {
        foreach (Revision::TARGETS as $slot => $identity) {
            $id = $identity['id']; DB::table('text_blocks')->where('id', $id)->update(['body' => $this->patch['targets'][1]['after']['page']['blocks'][$slot]['body']]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table('text_blocks')->where('id', $id)->update(['body' => $this->previous['targets'][1]['after']['page']['blocks'][$slot]['body']]);
        }
        foreach ([0, 1] as $index) {
            $service = $this->service(); $service->savePlan($this->file('rollback-preview-'.$index)); $old = $this->snapshot(); $service->failAtUpdate = $index;
            $this->refused(fn () => $service->apply($this->file('rollback-preview-'.$index), $this->file('rollback-backup-'.$index)));
            self::assertSame($old, $this->snapshot());
        }
    }

    public function test_raw_metadata_timestamps_foreign_bodies_questions_and_progress_cannot_be_normalized_away(): void
    {
        foreach (['uuid' => 'foreign', 'seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999, 'locale' => 'pl',
            'sort_order' => 9, 'type' => 'box', 'column' => 'right', 'heading' => 'manual', 'css_class' => 'visual', 'level' => 'B2',
            'body' => 'Unknown prose', 'updated_at' => '2026-02-01 00:00:00'] as $field => $value) {
            $old = DB::table('text_blocks')->where('id', 1673)->value($field); DB::table('text_blocks')->where('id', 1673)->update([$field => $value]);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot(), $field);
            DB::table('text_blocks')->where('id', 1673)->update([$field => $old]);
        }
        $foreign = DB::table('text_blocks')->where('locale', 'en')->first()->id;
        foreach ([['questions', 8200000, 'question'], ['text_blocks', $foreign, 'body'], ['m82_whitespace_protected_progress', 1, 'value']] as [$table, $id, $field]) {
            $old = DB::table($table)->where('id', $id)->value($field); DB::table($table)->where('id', $id)->update([$field => 'Outside correction.']);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
    }

    public function test_stale_source_preview_exclusive_backup_and_historical_predecessor_are_fail_closed(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); $old = $this->snapshot();
        File::put($this->file('occupied'), 'Keep existing backup.');
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('occupied'))); self::assertSame($old, $this->snapshot());
        self::assertSame('Keep existing backup.', File::get($this->file('occupied')));
        $path = $this->root.'/'.Revision::PREVIOUS_SOURCE; File::put($path, File::get($path)."\n");
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('no-predecessor')));
        self::assertSame($old, $this->snapshot()); self::assertFileDoesNotExist($this->file('no-predecessor'));
    }

    public function test_correction_cli_does_not_infer_production_or_accept_backup_without_apply(): void
    {
        $old = $this->snapshot();
        foreach ([[], ['--local-target' => 'gramlyze.ub', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => '../proof.json'],
            ['--local-target' => 'gramlyze.loc', '--local-proof' => 'local-proof-'.str_repeat('a', 32).'.json', '--backup' => 'not-apply.json']] as $args) {
            $this->artisan('content:patch-m82-whitespace-correction', $args)->assertExitCode(1); self::assertSame($old, $this->snapshot());
        }
    }
}

class M82SyntheticWhitespaceCorrection extends Patch
{
    public array $baselineSql = [];
    public ?int $failAtUpdate = null;
    public function rawFingerprints(): array { return $this->correctionFingerprints(); }
    protected function baselineEvidence(): array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        if ($this->baselineSql === []) { throw new RuntimeException('Synthetic correction baseline unavailable.'); }
        return ['sql_after' => $this->baselineSql];
    }
    protected function afterUpdate(int $index): void
    {
        if ($this->failAtUpdate === $index) { throw new RuntimeException('Injected isolated correction update failure.'); }
    }
}
