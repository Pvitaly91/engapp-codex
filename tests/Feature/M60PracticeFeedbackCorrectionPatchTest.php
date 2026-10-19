<?php

namespace Tests\Feature;

use App\Services\M26ContentPatch;
use App\Services\M60ContentPatch;
use App\Services\M60PracticeFeedbackCorrectionPatch;
use App\Support\M26DetailPackage;
use App\Support\M60ReportedSpeechBasicsPackage as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M60FeedbackIsolatedPatch extends M60PracticeFeedbackCorrectionPatch
{
    public array $fixtureBaseline = [];
    public bool $fail = false;
    protected function baselineEvidence(): array { return $this->fixtureBaseline; }
    protected function sourceAudit(string $root): array { return []; }
    protected function afterUpdate(int $index): void
    {
        if ($this->fail && $index === 0) { throw new RuntimeException('Injected feedback update failure.'); }
    }
}
/** Same M26 transaction in an owned SQLite-only after-1.0.0 fixture. */
class M60PracticeFeedbackCorrectionPatchTest extends TestCase
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
        [, $patch, , $contract] = Package::load(); $this->delta = M60PracticeFeedbackCorrectionPatch::correction(base_path());
        $this->fixture = storage_path('app/m60-feedback-'.bin2hex(random_bytes(7)));
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));
        File::makeDirectory($this->fixture.'/evidence', 0700, true); IsolatedTestEnvironment::assertOwnedPath($this->fixture);
        $paths = [...M60ContentPatch::sourcePaths($patch, $contract), M60PracticeFeedbackCorrectionPatch::DELTA,
            'app/Services/M60PracticeFeedbackCorrectionPatch.php', 'app/Console/Commands/PatchM60PracticeFeedback.php',
            ...array_keys($this->delta['source_artifacts'])];
        foreach (array_unique($paths) as $path) {
            File::makeDirectory(dirname($this->fixture.'/'.$path), 0700, true, true); File::copy(base_path($path), $this->fixture.'/'.$path);
        }
        DB::table('page_categories')->insert(['id' => 3, 'slug' => 'reported-speech', 'title' => 'Непряма мова', 'type' => 'theory', 'language' => 'uk']);
        foreach ($patch['targets'] as $i => $target) {
            $definition = $target['after']; $owner = $target['identity'];
            DB::table('pages')->insert(['id' => Package::PAGE_IDS[$i], 'page_category_id' => Package::CATEGORY_IDS[$i],
                'seeder' => $owner, 'type' => 'theory', 'slug' => $target['slug'], 'title' => $definition['page']['title'],
                'text' => $definition['page']['subtitle_text'], 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            $configs = [['type' => 'subtitle', 'column' => 'header', 'body' => $definition['page']['subtitle_html'],
                'level' => $definition['page']['subtitle_level'] ?? null,
                'uuid_key' => $definition['page']['subtitle_uuid_key'] ?? 'subtitle'], ...$definition['page']['blocks']];
            foreach ($configs as $position => $config) {
                $change = array_values(array_filter($this->delta['updates'], fn ($c) => $c['identity'] === $owner && $c['source_index'] + 1 === $position))[0] ?? null;
                DB::table('text_blocks')->insert(['id' => $change['id'] ?? (50000 + $i * 100 + $position),
                    'uuid' => $change['uuid'] ?? M26DetailPackage::uuid($owner, $config, $position), 'page_id' => Package::PAGE_IDS[$i],
                    'page_category_id' => Package::CATEGORY_IDS[$i], 'seeder' => $owner, 'locale' => 'uk',
                    'type' => $config['type'], 'column' => $config['column'], 'heading' => $config['heading'] ?? null,
                    'css_class' => $config['css_class'] ?? null, 'level' => $config['level'] ?? null, 'sort_order' => $position,
                    'body' => $change['before_body'] ?? $config['body'], 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-01 12:00:00']);
            }
            $hero = DB::table('text_blocks')->where('page_id', Package::PAGE_IDS[$i])->where('sort_order', 1)->value('uuid');
            foreach ($contract['targets'][$i]['bank_seeders'] as $bank) {
                for ($n = 0; $n < 5; ++$n) {
                    $uuid = 'm60-feedback-fixture-'.($i * 100 + $n).'-'.substr(hash('sha256', $bank), 0, 8);
                    DB::table('questions')->insert(['uuid' => $uuid, 'question' => 'Ізольоване питання.', 'type' => '4', 'level' => 'B1', 'seeder' => $bank]);
                    DB::table('question_theory_text_blocks')->insert(['question_uuid' => $uuid, 'text_block_uuid' => $hero, 'position' => 0]);
                }
            }
        }
        DB::connection()->getSchemaBuilder()->create('m60_feedback_progress', static function (Blueprint $table): void { $table->id(); $table->text('value'); });
        DB::table('m60_feedback_progress')->insert(['value' => 'Personal state.']);
        $this->baseline = ['sql_after' => $this->fingerprints(), 'normalized_before' => $this->fingerprints(),
            'runtime' => ['fixture' => 'isolated-sqlite-only'], 'new_native_blocks' => [], 'independent_before_sha256' => M60ContentPatch::BEFORE_SHA];
    }

    private function service(): M60FeedbackIsolatedPatch
    {
        $patch = new M60FeedbackIsolatedPatch(DB::connection(), $this->fixture.'/database', $this->fixture.'/evidence');
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

    public function test_three_exact_body_updates_zero_inserts_and_two_noops_preserve_all_page_fields_and_timestamps(): void
    {
        $service = $this->service(); $original = $this->snapshot(); $plan = $service->savePlan($this->file('preview'));
        self::assertSame('before', $plan['state']); self::assertSame(M60PracticeFeedbackCorrectionPatch::IDS, array_column($plan['updates'], 'id'));
        self::assertSame([], $plan['inserts']); self::assertSame(array_keys($original), array_keys($plan['protected']));
        foreach ($plan['updates'] as $change) { self::assertSame('text_blocks', $change['table']); self::assertSame(['body'], array_keys($change['after'])); }
        self::assertSame(['status' => 'applied', 'updated' => 3, 'inserted' => 0, 'backup' => $this->file('backup')],
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
        $row = (array) DB::table('text_blocks')->where('id', 12720)->sole();
        foreach (['body' => 'A newer manual body', 'locale' => 'en', 'seeder' => 'Foreign', 'uuid' => 'Foreign',
            'page_id' => 102, 'page_category_id' => 17, 'type' => 'box', 'column' => 'right', 'level' => 'B2',
            'heading' => 'Foreign', 'css_class' => 'Foreign', 'sort_order' => 999] as $field => $value) {
            DB::table('text_blocks')->where('id', 12720)->update([$field => $value]);
            $this->rejects(fn () => $this->service()->savePlan($this->file('bad-'.$field)));
            self::assertFileDoesNotExist($this->file('bad-'.$field)); DB::table('text_blocks')->where('id', 12720)->update([$field => $row[$field]]);
        }
        DB::table('text_blocks')->where('id', 12720)->update(['body' => $this->delta['updates'][0]['after_body']]);
        $this->rejects(fn () => $this->service()->plan());
        $this->rejects(fn () => $this->service()->verification());
    }

    public function test_full_tables_protect_progress_page_text_non_target_body_and_source_drift(): void
    {
        foreach ([['m60_feedback_progress', 1, 'value'], ['pages', 3, 'text'], ['text_blocks', 50001, 'body']] as [$table, $id, $field]) {
            $row = (array) DB::table($table)->where('id', $id)->sole();
            DB::table($table)->where('id', $id)->update([$field => 'Not authorized by feedback correction']);
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

    public function test_all_eighteen_task_feedback_maps_are_plain_and_exact_ninety_four_leaves_only(): void
    {
        $changed = 0; $tasks = 0;
        foreach ($this->delta['updates'] as $i => $change) {
            $old = json_decode($change['before_body'], true, flags: JSON_THROW_ON_ERROR);
            $new = json_decode($change['after_body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($change['after_body'], M60PracticeFeedbackCorrectionPatch::plainFeedbackBody($change['before_body'], [52, 22, 20][$i]));
            foreach (['selects', 'choices', 'inputs'] as $group) {
                foreach ($old[$group] as $n => $task) {
                    ++$tasks; self::assertSame(array_keys($task['feedback']), array_keys($new[$group][$n]['feedback']));
                    foreach ($task['feedback'] as $key => $text) {
                        $plain = str_replace(['<br>', '<span lang="en">', '<span lang="uk">', '</span>'], ["\n", '', '', ''], $text);
                        self::assertNotSame($plain, $text); ++$changed;
                        self::assertSame($plain, $new[$group][$n]['feedback'][$key]);
                        self::assertDoesNotMatchRegularExpression('/<[^>]*>/', $plain);
                    }
                    unset($old[$group][$n]['feedback'], $new[$group][$n]['feedback']);
                }
            }
            self::assertSame($old, $new, 'All prompts/labels/answers/options/aliases/tokens and metadata stay exact.');
        }
        self::assertSame(18, $tasks); self::assertSame(94, $changed);
    }

    public function test_corrected_after_is_replay_compatible_with_the_regular_native_planner(): void
    {
        $service = $this->service(); $service->savePlan($this->file('preview'));
        $service->apply($this->file('preview'), $this->file('backup'));
        $snapshot = $this->snapshot();
        $regular = new M60ContentPatch(DB::connection(), $this->fixture.'/database', $this->fixture.'/evidence');
        $after = $regular->savePlan($this->file('regular-after'));
        self::assertSame('after', $after['state']); self::assertSame([], $after['updates']); self::assertSame([], $after['inserts']);
        self::assertSame('no-op', $regular->apply($this->file('regular-after'), $this->file('never-created'))['status']);
        self::assertFileDoesNotExist($this->file('never-created')); self::assertSame($snapshot, $this->snapshot());
    }

    public function test_plain_feedback_projection_refuses_wrong_count_unsupported_markup_or_incomplete_groups(): void
    {
        $original = $this->delta['updates'][0]['before_body'];
        foreach (['count', 'markup', 'group'] as $case) {
            $data = json_decode($original, true, flags: JSON_THROW_ON_ERROR); $count = 52;
            if ($case === 'count') { $count = 51; }
            if ($case === 'markup') { $key = array_key_first($data['selects'][0]['feedback']); $data['selects'][0]['feedback'][$key] .= '<strong>Unsupported</strong>'; }
            if ($case === 'group') { array_pop($data['inputs']); }
            $caught = null;
            try { M60PracticeFeedbackCorrectionPatch::plainFeedbackBody(M26DetailPackage::json($data), $count); }
            catch (RuntimeException $error) { $caught = $error; }
            self::assertInstanceOf(RuntimeException::class, $caught, $case);
        }
    }
}
