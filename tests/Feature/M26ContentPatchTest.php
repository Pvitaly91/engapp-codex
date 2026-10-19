<?php

namespace Tests\Feature;

use App\Services\M26ContentPatch;
use App\Support\M26DetailPackage as Package;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M26ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $private;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root = storage_path('app/m26-fixture-'.bin2hex(random_bytes(8)));
        $this->private = $this->root.'/evidence';
        File::makeDirectory($this->private, 0700, true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$master, $manifest] = Package::load();
        $paths = [Package::MASTER, Package::BEFORE];
        // Mirror only the source files read by the finite plan, into owned test storage.
        $code = file_get_contents(base_path('app/Services/M26ContentPatch.php'));
        preg_match_all("~'(app/[^']+\.php|resources/views/[^']+\.php)'~", $code, $matches);
        $paths = array_merge($paths, $matches[1], array_column($master['targets'], 'definition_path'));
        foreach (array_unique($paths) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['title' => 'Tenses', 'slug' => 'tenses', 'type' => 'theory', 'language' => 'uk']);
        foreach ($master['targets'] as $t) {
            $path = $this->root.'/'.$t['definition_path'];
            // This legacy command is tested against its own immutable M26 source,
            // not against the later interactive upgrade's four definitions.
            $after = Package::json(Package::definition($t, $manifest['definitions'][$t['identity']]));
            File::put($path, Package::json($manifest['definitions'][$t['identity']]));
            try {
                (new class($path) extends JsonPageSeeder {
                    public function __construct(private string $path) {}
                    protected function definitionPath(): string { return $this->path; }
                })->run();
            } finally { File::put($path, $after); }
        }
        DB::table('questions')->insert(['uuid' => 'protected-bank', 'question' => 'Protected {a1}']);
        $b = (array) DB::table('text_blocks')->where('locale', 'uk')->first();
        unset($b['id']); $b['uuid'] = 'protected-locale'; $b['locale'] = 'pl';
        DB::table('text_blocks')->insert($b);
    }

    private function service(): M26ContentPatch { return new M26ContentPatch(DB::connection(), $this->root.'/database', $this->private); }
    private function path(string $name): string { return $this->private.'/'.$name.'.json'; }
    private function snapshot(): array
    {
        $out = [];
        foreach (['pages', 'page_categories', 'text_blocks', 'tags', 'tag_text_block', 'page_tag', 'page_category_tag',
            'questions', 'question_answers', 'question_options', 'question_theory_text_blocks', 'seed_runs'] as $t) {
            $out[$t] = DB::table($t)->get()->map(fn ($r) => (array) $r)->all();
        }
        return $out;
    }
    private function refuses(callable $operation): void
    {
        $before = $this->snapshot();
        try { $operation(); self::fail('Unsafe mutation was accepted'); } catch (RuntimeException) {}
        self::assertSame($before, $this->snapshot());
    }

    public function test_preview_exclusive_backup_four_inserts_fourteen_updates_noop_restore(): void
    {
        $p = $this->service(); $before = $this->snapshot();
        $plan = $p->savePlan($this->path('preview'));
        self::assertSame($before, $this->snapshot());
        self::assertCount(14, $plan['updates']); self::assertCount(4, $plan['inserts']);
        self::assertSame(M26ContentPatch::NAMES, array_keys($plan['pages']));
        $r = $p->apply($this->path('preview'), $this->path('backup'));
        self::assertSame(['status' => 'applied', 'updated' => 14, 'inserted' => 4, 'backup' => $this->path('backup')], $r);
        self::assertSame($plan, json_decode(File::get($this->path('backup')), true));
        self::assertSame($plan['protected'], $p->plan()['protected']);
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $p->apply($this->path('preview'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status' => 'restored', 'updated' => 14, 'deleted' => 4], $p->restore($this->path('backup')));
        self::assertSame($before, $this->snapshot());
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'deleted' => 0], $p->restore($this->path('backup')));
        File::put($this->path('exists'), '{}');
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('exists')));
    }

    public static function conflicts(): array { return array_map(fn ($x) => [$x], ['owner', 'locale', 'category', 'order', 'body', 'partial', 'source', 'hash', 'stale']); }

    #[DataProvider('conflicts')]
    public function test_conflicts_and_stale_preview_refuse_without_writes(string $kind): void
    {
        $p = $this->service(); $plan = $p->savePlan($this->path('preview'));
        $c = $plan['updates'][0]; $q = DB::table('text_blocks')->where('id', $c['id']);
        if ($kind === 'owner') $q->update(['seeder' => 'foreign']);
        if ($kind === 'locale') $q->update(['locale' => 'en']);
        if ($kind === 'category') $q->update(['page_category_id' => 999]);
        if ($kind === 'order') $q->update(['sort_order' => 999]);
        if ($kind === 'body') $q->update(['body' => '{invalid']);
        if ($kind === 'partial') $q->update($c['after']);
        if ($kind === 'stale') DB::table('questions')->update(['question' => 'Later edit']);
        if ($kind === 'source') {
            [$m] = Package::load(); File::append($this->root.'/'.$m['targets'][0]['definition_path'], ' ');
        }
        if ($kind === 'hash') File::append($this->root.'/'.Package::MASTER, ' ');
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_foreign_relation_and_manual_practice_edit_refuse_restore(): void
    {
        $p = $this->service(); $plan = $p->savePlan($this->path('preview'));
        $p->apply($this->path('preview'), $this->path('backup'));
        $id = DB::table('text_blocks')->where('uuid', $plan['inserts'][0]['fields']['uuid'])->value('id');
        DB::table('tag_text_block')->insert(['text_block_id' => $id, 'tag_id' => DB::table('tags')->value('id')]);
        $this->refuses(fn () => $p->restore($this->path('backup')));
        DB::table('tag_text_block')->where('text_block_id', $id)->delete();
        DB::table('text_blocks')->where('id', $id)->update(['body' => 'Manual later edit']);
        $this->refuses(fn () => $p->restore($this->path('backup')));
    }

    public function test_transaction_rollback_and_production_profile_guard(): void
    {
        $p = new class(DB::connection(), $this->root.'/database', $this->private) extends M26ContentPatch {
            protected function afterInsert(int $index): void { if ($index === 0) throw new RuntimeException('Injected rollback'); }
        };
        $p->savePlan($this->path('preview'));
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileExists($this->path('backup'));
        app()->detectEnvironment(fn () => 'production');
        $this->refuses(fn () => $this->service()->apply($this->path('preview'), $this->path('no-production')));
        self::assertFileDoesNotExist($this->path('no-production'));
    }
}
