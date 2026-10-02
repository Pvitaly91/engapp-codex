<?php

namespace Tests\Feature;

use App\Services\M26InteractivePracticePatch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage as Package;
use App\Support\M26InteractivePractice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M26InteractivePracticePatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $private;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root = storage_path('app/m26-interactive-fixture-'.bin2hex(random_bytes(8)));
        $this->private = $this->root.'/evidence';
        File::makeDirectory($this->private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$master, $manifest] = Package::load();
        $paths = [Package::MASTER, Package::BEFORE, M26InteractivePractice::SOURCE];
        $code = File::get(base_path('app/Services/M26InteractivePracticePatch.php'));
        preg_match_all("~'(app/[^']+\\.php|resources/views/[^']+\\.php)'~", $code, $matches);
        $paths = array_merge($paths, $matches[1], array_column($master['targets'], 'definition_path'));
        foreach (array_unique($paths) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['title' => 'Tenses', 'slug' => 'tenses', 'type' => 'theory', 'language' => 'uk']);
        foreach ($master['targets'] as $target) {
            $path = $this->root.'/'.$target['definition_path'];
            $before = $manifest['definitions'][$target['identity']];
            $legacy = Package::definition($target, $before);
            $latest = isset($target['practice_insert']) ? M26InteractivePractice::definition($target, $before) : $legacy;
            // Isolated SQLite fixture only: emulate the exact already-applied M26 predecessor.
            File::put($path, Package::json($legacy));
            try {
                (new class($path) extends JsonPageSeeder {
                    public function __construct(private string $path) {}
                    protected function definitionPath(): string { return $this->path; }
                })->run();
            } finally { File::put($path, Package::json($latest)); }
        }
        DB::table('questions')->insert(['uuid' => 'protected-bank', 'question' => 'Protected {a1}']);
        $row = (array) DB::table('text_blocks')->where('locale', 'uk')->first();
        unset($row['id']); $row['uuid'] = 'protected-locale'; $row['locale'] = 'pl';
        DB::table('text_blocks')->insert($row);
    }

    private function service(): M26InteractivePracticePatch
    {
        return new M26InteractivePracticePatch(DB::connection(), $this->root.'/database', $this->private);
    }
    private function path(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function snapshot(): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'tags', 'page_tag', 'page_category_tag', 'tag_text_block',
            'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks',
            'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants',
            'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs'] as $table) {
            $out[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }
        return $out;
    }
    private function refuses(callable $operation): void
    {
        $before = $this->snapshot();
        try { $operation(); self::fail('Unsafe M26 interactive mutation was accepted'); }
        catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
    }

    public function test_four_existing_rows_upgrade_no_inserts_or_bank_writes_noop_and_guarded_restore(): void
    {
        $patch = $this->service(); $before = $this->snapshot();
        $plan = $patch->savePlan($this->path('preview'));
        self::assertSame($before, $this->snapshot());
        self::assertSame('m26-ppc-interactive-practice-v1', $plan['patch']);
        self::assertSame(M26InteractivePracticePatch::NAMES, $plan['names']);
        self::assertCount(5, $plan['pages']); // Includes read-only overview fidelity check.
        self::assertCount(4, $plan['updates']); self::assertSame([], $plan['inserts']);
        self::assertCount(20, $plan['protected']);
        foreach ($plan['updates'] as $change) {
            self::assertSame(['type', 'heading', 'body'], array_keys($change['before']));
            self::assertSame(['type', 'heading', 'body'], array_keys($change['after']));
            self::assertSame('box', $change['before']['type']);
            self::assertSame('practice-set', $change['after']['type']);
            self::assertNull($change['after']['heading']);
        }
        self::assertSame(['status' => 'applied', 'updated' => 4, 'inserted' => 0, 'backup' => $this->path('backup')],
            $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
        $after = $patch->plan();
        self::assertSame($plan['protected'], $after['protected']);
        self::assertCount(count($before['text_blocks']), $this->snapshot()['text_blocks']);
        foreach ($plan['updates'] as $change) {
            $old = collect($before['text_blocks'])->firstWhere('id', $change['id']);
            $new = (array) DB::table('text_blocks')->where('id', $change['id'])->first();
            self::assertSame(array_replace($old, $change['after']), $new);
        }
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0],
            $patch->apply($this->path('preview'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status' => 'restored', 'updated' => 4, 'deleted' => 0], $patch->restore($this->path('backup')));
        self::assertSame($before, $this->snapshot());
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'deleted' => 0], $patch->restore($this->path('backup')));
        File::put($this->path('exists'), '{}');
        $this->refuses(fn () => $patch->apply($this->path('preview'), $this->path('exists')));
    }

    public static function conflicts(): array
    {
        return array_map(fn ($kind) => [$kind], ['owner', 'locale', 'uuid', 'category', 'order', 'type', 'heading', 'body',
            'partial', 'timestamp', 'native', 'overview', 'extra', 'source', 'payload', 'master', 'stale-bank', 'relation']);
    }

    #[DataProvider('conflicts')]
    public function test_identity_manual_rows_sources_and_stale_preview_refuse_without_writes(string $kind): void
    {
        $patch = $this->service(); $plan = $patch->savePlan($this->path('preview'));
        $change = $plan['updates'][0]; $query = DB::table('text_blocks')->where('id', $change['id']);
        $edits = ['owner' => ['seeder' => 'foreign'], 'locale' => ['locale' => 'en'], 'uuid' => ['uuid' => 'foreign-uuid'],
            'category' => ['page_category_id' => 999], 'order' => ['sort_order' => 999], 'type' => ['type' => 'forms-grid'],
            'heading' => ['heading' => 'Manual heading'], 'body' => ['body' => 'Manual practice'],
            'timestamp' => ['updated_at' => '2001-01-01 01:01:01'], 'partial' => $change['after']];
        if (isset($edits[$kind])) { $query->update($edits[$kind]); }
        if ($kind === 'native') { DB::table('text_blocks')->where('type', 'forms-grid')->update(['body' => '{changed}']); }
        if ($kind === 'overview') { DB::table('text_blocks')->whereNull('page_id')->where('type', 'usage-panels')->update(['body' => '{changed}']); }
        if ($kind === 'extra') {
            $row = (array) $query->first(); unset($row['id']); $row['uuid'] = 'manual-extra';
            DB::table('text_blocks')->insert($row);
        }
        if ($kind === 'stale-bank') { DB::table('questions')->update(['question' => 'Later question edit']); }
        if ($kind === 'relation') {
            DB::table('tag_text_block')->insert(['text_block_id' => $change['id'], 'tag_id' => DB::table('tags')->value('id')]);
        }
        if ($kind === 'source') {
            [$master] = Package::load(); File::append($this->root.'/'.$master['targets'][1]['definition_path'], ' ');
        }
        if ($kind === 'payload') { File::append($this->root.'/'.M26InteractivePractice::SOURCE, ' '); }
        if ($kind === 'master') { File::append($this->root.'/'.Package::MASTER, ' '); }
        $this->refuses(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_restore_and_noop_refuse_later_foreign_relationship_and_manual_practice(): void
    {
        $patch = $this->service(); $plan = $patch->savePlan($this->path('preview'));
        $patch->apply($this->path('preview'), $this->path('backup'));
        $id = $plan['updates'][0]['id'];
        DB::table('question_theory_text_blocks')->insert(['text_block_uuid' => $plan['updates'][0]['uuid'], 'question_uuid' => 'protected-bank']);
        $this->refuses(fn () => $patch->restore($this->path('backup')));
        $this->refuses(fn () => $patch->apply($this->path('preview'), $this->path('unused')));
        DB::table('question_theory_text_blocks')->where('text_block_uuid', $plan['updates'][0]['uuid'])->delete();
        DB::table('text_blocks')->where('id', $id)->update(['body' => 'Later manual practice edit']);
        $this->refuses(fn () => $patch->restore($this->path('backup')));
    }

    public function test_update_rollback_keeps_exclusive_backup_and_production_profile_refuses_without_proof(): void
    {
        $patch = new class(DB::connection(), $this->root.'/database', $this->private) extends M26InteractivePracticePatch {
            protected function afterUpdate(int $index): void { if ($index === 0) throw new RuntimeException('Injected update rollback'); }
        };
        $patch->savePlan($this->path('preview'));
        $this->refuses(fn () => $patch->apply($this->path('preview'), $this->path('backup')));
        self::assertFileExists($this->path('backup'));
        app()->detectEnvironment(fn () => 'production');
        $this->refuses(fn () => $this->service()->apply($this->path('preview'), $this->path('no-production')));
        self::assertFileDoesNotExist($this->path('no-production'));
        $this->artisan('content:patch-ppc-interactive-m26', ['--apply' => true, '--plan' => 'unknown.json', '--backup' => 'unknown-backup.json'])
            ->expectsOutput('Production-profile M26 writes require verified gramlyze.loc local-target.')
            ->assertFailed();
    }
}
