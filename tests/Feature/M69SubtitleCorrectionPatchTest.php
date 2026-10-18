<?php

namespace Tests\Feature;

use App\Services\M69SubtitleCorrectionPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M69AdjectivesAdverbs as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual three-owner frozen sources, synthetic SQLite rows; not working-DB acceptance. */
class M69SubtitleCorrectionPatchTest extends TestCase
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
        self::assertSame('testing', app()->environment()); self::assertNotSame(base_path(), app()->environmentPath());
        self::assertSame('sqlite', DB::connection()->getDriverName()); self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertSame('array', config('cache.default')); self::assertSame('array', config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        [$before, $this->patch, $master, $contract] = Package::load(); self::assertSame('1.0.1', $master['version']);
        $this->previous = json_decode(file_get_contents(base_path(Patch::PREVIOUS_SOURCE)), true, flags: JSON_THROW_ON_ERROR);
        $this->root = storage_path('app/m69-subtitle-correction-fixture-'.bin2hex(random_bytes(7))); $this->private = $this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private, 0700, true);
        foreach (Patch::sourcePaths($this->patch, $contract) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 52, 'parent_id' => null, 'slug' => 'prykmetniky-ta-pryslinknyky',
            'title' => 'Прикметники та прислівники', 'type' => 'theory', 'language' => 'uk']);
        $time = '2026-01-01 12:00:00'; $questionId = 6950000;
        foreach ($this->patch['targets'] as $i => $target) {
            $source = $target['after'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => 52, 'seeder' => $target['identity'],
                'slug' => $source['slug'], 'title' => $source['page']['title'], 'text' => $source['page']['subtitle_text'],
                'type' => 'theory', 'created_at' => $time, 'updated_at' => $time]);
            $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $source['page']['subtitle_html'],
                'level' => $source['page']['subtitle_level'] ?? null, 'uuid_key' => $source['page']['subtitle_uuid_key'] ?? 'subtitle'];
            foreach ([$subtitle, ...$source['page']['blocks']] as $position => $config) {
                $id = 690000 + Package::PAGE_IDS[$i] * 100 + $position;
                if ($i === 2 && $position === 0) { $id = Patch::TARGET_ID; }
                elseif ($config['type'] === 'practice-set') { $id = [12753, 12754, 12756][$i]; }
                DB::table('text_blocks')->insert(['id' => $id, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $position),
                    'page_id' => Package::PAGE_IDS[$i], 'page_category_id' => 52, 'locale' => 'uk', 'seeder' => $target['identity'],
                    'sort_order' => $position, 'type' => $config['type'], 'column' => $config['column'],
                    'heading' => $config['heading'] ?? null, 'level' => $config['level'] ?? null, 'css_class' => $config['css_class'] ?? null,
                    'body' => $config['body'], 'created_at' => $time, 'updated_at' => $time]);
            }
            $hero = (array) DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->sole();
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm69-correction-synthetic-question-'.$questionId;
                    DB::table('questions')->insert(['id' => $questionId++, 'uuid' => $uuid, 'question' => 'Ізольоване тематичне питання.',
                        'type' => '4', 'level' => Package::LEVELS[$i], 'seeder' => $bank, 'created_at' => $time, 'updated_at' => $time]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero['uuid'],
                        'position' => 0, 'created_at' => $time, 'updated_at' => $time]);
                }
            }
            unset($hero['id']);
            foreach (['en', 'pl'] as $locale) {
                $hero['uuid'] = 'm69-correction-foreign-'.$i.'-'.$locale; $hero['locale'] = $locale; $hero['body'] = 'Exact old '.$locale;
                DB::table('text_blocks')->insert($hero);
            }
        }
        DB::table('pages')->where('id', 275)->update(['text' => $this->previous['targets'][2]['after']['page']['subtitle_text']]);
        DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update(['body' => $this->previous['targets'][2]['after']['page']['subtitle_html']]);
        DB::connection()->getSchemaBuilder()->dropIfExists('m69_correction_protected_progress');
        DB::connection()->getSchemaBuilder()->create('m69_correction_protected_progress', static function (Blueprint $table): void {
            $table->id(); $table->text('value');
        });
        DB::table('m69_correction_protected_progress')->insert(['value' => 'Keep synthetic progress exact.']);
        $this->baseline = (new M69SyntheticSubtitleCorrection(DB::connection(), $this->root.'/database', $this->private))->rawFingerprints();
    }

    private function service(): M69SyntheticSubtitleCorrection
    {
        $service = new M69SyntheticSubtitleCorrection(DB::connection(), $this->root.'/database', $this->private);
        $service->baselineSql = $this->baseline; return $service;
    }
    private function file(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function refused(callable $call): void
    {
        try { $call(); self::fail('Expected exact two-value subtitle correction refusal.'); }
        catch (RuntimeException $error) { self::assertNotSame('', $error->getMessage()); }
    }
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

    public function test_two_update_zero_insert_transaction_has_raw_backup_preserves_every_other_row_and_repeats_as_noop(): void
    {
        $service = $this->service(); $old = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame($old, $this->snapshot()); self::assertSame('before', $plan['state']);
        self::assertCount(2, $plan['updates']); self::assertSame([], $plan['inserts']);
        self::assertSame(['pages', 'text_blocks'], array_column($plan['updates'], 'table'));
        self::assertSame([275, Patch::TARGET_ID], array_column($plan['updates'], 'id'));
        self::assertSame(['text'], array_keys($plan['updates'][0]['before'])); self::assertSame(['body'], array_keys($plan['updates'][1]['before']));
        self::assertSame($this->previous['targets'][2]['after']['page']['subtitle_text'], $plan['pages'][Package::OWNERS[2]]['page']['text']);
        $rawSubtitle = array_values(array_filter($plan['pages'][Package::OWNERS[2]]['blocks'], static fn ($row) => $row['id'] === Patch::TARGET_ID))[0];
        self::assertSame($this->previous['targets'][2]['after']['page']['subtitle_html'], $rawSubtitle['body'], 'Parent validation copies must not leak into the backup.');
        self::assertSame('applied', $service->apply($this->file('preview'), $this->file('backup'))['status']);
        self::assertSame($plan, json_decode(File::get($this->file('backup')), true, flags: JSON_THROW_ON_ERROR));
        $expected = $old;
        foreach ($expected['pages'] as &$row) { if ($row['id'] === 275) { $row['text'] = $this->patch['targets'][2]['after']['page']['subtitle_text']; } } unset($row);
        foreach ($expected['text_blocks'] as &$row) { if ($row['id'] === Patch::TARGET_ID) { $row['body'] = $this->patch['targets'][2]['after']['page']['subtitle_html']; } } unset($row);
        self::assertSame($expected, $this->snapshot(), 'Only two values change; all IDs, UUIDs, timestamps, EN/PL, practices and question/progress families stay literal.');
        $after = $service->savePlan($this->file('after')); self::assertSame('after', $after['state']);
        self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']); self::assertSame($plan['protected'], $after['protected']);
        self::assertSame('no-op', $service->apply($this->file('preview'), $this->file('unused-old'))['status']);
        self::assertSame('no-op', $service->apply($this->file('after'), $this->file('unused-after'))['status']);
        self::assertFileDoesNotExist($this->file('unused-old')); self::assertFileDoesNotExist($this->file('unused-after'));
    }

    public function test_either_single_value_partial_correction_is_refused_without_writes(): void
    {
        $page = $this->previous['targets'][2]['after']['page']; $next = $this->patch['targets'][2]['after']['page'];
        DB::table('pages')->where('id', 275)->update(['text' => $next['subtitle_text']]); $snapshot = $this->snapshot();
        $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('pages')->where('id', 275)->update(['text' => $page['subtitle_text']]);
        DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update(['body' => $next['subtitle_html']]); $snapshot = $this->snapshot();
        $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_failure_after_either_update_rolls_back_both_exact_values_and_all_other_tables(): void
    {
        foreach ([0, 1] as $index) {
            $service = $this->service(); $plan = $service->savePlan($this->file('rollback-preview-'.$index)); $old = $this->snapshot();
            $service->failAtUpdate = $index;
            $this->refused(fn () => $service->apply($this->file('rollback-preview-'.$index), $this->file('rollback-backup-'.$index)));
            self::assertSame($old, $this->snapshot()); $service->failAtUpdate = null; self::assertSame($plan, $service->plan());
        }
    }

    public function test_stale_plan_or_protected_progress_drift_is_refused_before_new_backup(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        DB::table('m69_correction_protected_progress')->where('id', 1)->update(['value' => 'Changed outside correction.']); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-progress')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-progress'));
        DB::table('m69_correction_protected_progress')->where('id', 1)->update(['value' => 'Keep synthetic progress exact.']);
        $source = $this->root.'/'.$this->patch['targets'][0]['path']; File::put($source, File::get($source)."\n"); $snapshot = $this->snapshot();
        $this->refused(fn () => $service->apply($this->file('preview'), $this->file('not-created-source')));
        self::assertSame($snapshot, $this->snapshot()); self::assertFileDoesNotExist($this->file('not-created-source'));
    }

    public function test_exact_subtitle_identity_metadata_and_timestamps_cannot_be_masked_by_normalization(): void
    {
        $original = (array) DB::table('text_blocks')->where('id', Patch::TARGET_ID)->sole();
        foreach (['uuid' => 'foreign', 'seeder' => 'ForeignOwner', 'page_id' => 999, 'page_category_id' => 999,
            'locale' => 'en', 'sort_order' => 1, 'type' => 'box', 'column' => 'left', 'heading' => 'Manual',
            'css_class' => 'new-design', 'level' => 'A2', 'body' => 'Manual body', 'updated_at' => '2026-02-01 00:00:00'] as $field => $value) {
            DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update([$field => $value]); $snapshot = $this->snapshot();
            $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot(), $field);
            DB::table('text_blocks')->where('id', Patch::TARGET_ID)->update([$field => $original[$field]]);
        }
    }

    public function test_wrong_page_title_or_other_owner_body_question_and_translation_drift_are_not_allowed(): void
    {
        $oldTitle = DB::table('pages')->where('id', 275)->value('title');
        DB::table('pages')->where('id', 275)->update(['title' => 'ed / -ing Adjectives']); $snapshot = $this->snapshot();
        $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
        DB::table('pages')->where('id', 275)->update(['title' => $oldTitle]);
        foreach ([['questions', 6950000, 'question'], ['text_blocks', 690000 + 279 * 100 + 1, 'body']] as [$table, $id, $field]) {
            $old = DB::table($table)->where('id', $id)->value($field); DB::table($table)->where('id', $id)->update([$field => 'Changed outside scope.']);
            $snapshot = $this->snapshot(); $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
            DB::table($table)->where('id', $id)->update([$field => $old]);
        }
        $foreign = (array) DB::table('text_blocks')->where('locale', 'pl')->first();
        DB::table('text_blocks')->where('id', $foreign['id'])->update(['body' => 'Changed Polish.']); $snapshot = $this->snapshot();
        $this->refused(fn () => $this->service()->plan()); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_exclusive_backup_and_frozen_historical_source_are_never_overwritten_or_bypassed(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview')); File::put($this->file('existing-backup'), 'Already owned.');
        $snapshot = $this->snapshot(); $this->refused(fn () => $service->apply($this->file('preview'), $this->file('existing-backup')));
        self::assertSame('Already owned.', File::get($this->file('existing-backup'))); self::assertSame($snapshot, $this->snapshot());
        $path = $this->root.'/'.Patch::PREVIOUS_SOURCE; File::put($path, File::get($path)."\n");
        $this->refused(fn () => $service->plan()); self::assertSame($snapshot, $this->snapshot());
    }
}

class M69SyntheticSubtitleCorrection extends Patch
{
    public array $baselineSql = [];
    public ?int $failAtUpdate = null;
    public function rawFingerprints(): array { return $this->correctionFingerprints(); }
    protected function baselineEvidence(): array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        if ($this->baselineSql === []) { throw new RuntimeException('Synthetic baseline not captured.'); }
        return ['sql_after' => $this->baselineSql];
    }
    protected function afterUpdate(int $index): void
    {
        if ($this->failAtUpdate === $index) { throw new RuntimeException('Injected isolated update failure.'); }
    }
}
