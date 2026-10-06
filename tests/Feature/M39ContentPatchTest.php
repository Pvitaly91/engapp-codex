<?php

namespace Tests\Feature;

use App\Services\M39ContentPatch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M39AuthoredRevisionPackage as Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M39ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root; private string $private;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root=storage_path('app/m39-fixture-'.bin2hex(random_bytes(8))); $this->private=$this->root.'/evidence';
        File::makeDirectory($this->private,0700,true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$before,$package]=Package::load();
        DB::table('page_categories')->insert(['slug'=>'fixture-parent','title'=>'Unrelated parent','language'=>'uk','type'=>'theory']);
        preg_match_all("~'((?:app/|resources/views/|tools/diagnostics/)[^']+\\.(?:php|ps1))'~",file_get_contents(base_path('app/Services/M39ContentPatch.php')),$m);
        foreach ([Package::BEFORE,Package::SOURCE,...$m[1], 'docs/content/m23-authored-content.v1.json', 'docs/content/m23-author-sources.md'] as $path) { File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true); File::copy(base_path($path),$this->root.'/'.$path); }
        foreach ($package['targets'] as $i=>$t) {
            $path=$this->root.'/'.$t['path']; File::makeDirectory(dirname($path),0700,true,true);
            File::put($path,Package::json($before['targets'][$i]['before']));
            (new class($path) extends JsonPageSeeder {
                public function __construct(private string $path) {}
                protected function definitionPath(): string { return $this->path; }
            })->run();
            File::put($path,Package::json($t['after']));
        }
        $row=(array)DB::table('text_blocks')->first(); unset($row['id']); $row['uuid']='protected-locale'; $row['locale']='pl'; DB::table('text_blocks')->insert($row);
        DB::table('questions')->insert(['uuid'=>'protected-bank','question'=>'Protected {a1}']);
    }
    private function service(): M39ContentPatch { return new M39ContentPatch(DB::connection(),$this->root.'/database',$this->private); }
    private function path(string $n): string { return $this->private.'/'.$n.'.json'; }
    private function snapshot(): array
    {
        $out=[]; foreach (['pages','page_categories','text_blocks','tags','tag_text_block','page_tag','page_category_tag','questions','question_answers','question_options','question_theory_text_blocks','seed_runs'] as $t) { $out[$t]=DB::table($t)->get()->map(fn($r)=>(array)$r)->all(); } return $out;
    }
    private function refuses(callable $f): void
    {
        $before=$this->snapshot(); try { $f(); self::fail('Unsafe operation accepted.'); } catch (RuntimeException) {} self::assertSame($before,$this->snapshot());
    }
    public function test_preview_apply_exclusive_backup_noop_restore_preserve_every_protected_row(): void
    {
        $p=$this->service(); $before=$this->snapshot(); $plan=$p->savePlan($this->path('preview'));
        self::assertSame($before,$this->snapshot()); self::assertCount(3,$plan['updates']); self::assertCount(array_sum(array_map(fn($t)=>count($t['after']['page']['blocks'])-2,Package::load()[1]['targets'])),$plan['inserts']);
        $r=$p->apply($this->path('preview'),$this->path('backup')); self::assertSame('applied',$r['status']);
        self::assertSame($plan,json_decode(File::get($this->path('backup')),true)); self::assertSame($plan['protected'],$p->plan()['protected']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$p->apply($this->path('preview'),$this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status'=>'restored','updated'=>3,'deleted'=>count($plan['inserts'])],$p->restore($this->path('backup'))); self::assertSame($before,$this->snapshot());
        File::put($this->path('exists'),'{}'); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('exists')));
    }
    public static function conflicts(): array { return array_map(fn($x)=>[$x],['source','master','policy','owner','locale','category','category_swap','category_language','category_type','parent','order','heading','level','css_class','body','unknown','stale']); }
    #[DataProvider('conflicts')]
    public function test_conflicts_stop_without_writes(string $kind): void
    {
        $p=$this->service(); $p->savePlan($this->path('preview'));
        $row=DB::table('text_blocks')->where('sort_order',2)->first();
        if ($kind==='source') { [, $pack]=Package::load(); File::append($this->root.'/'.$pack['targets'][0]['path'],'broken'); }
        elseif ($kind==='master') { File::append($this->root.'/docs/content/m23-authored-content.v1.json', ' '); }
        elseif ($kind==='policy') { File::append($this->root.'/docs/content/m23-author-sources.md', ' '); }
        elseif ($kind==='owner') { DB::table('pages')->where('id',$row->page_id)->update(['seeder'=>'Foreign']); }
        elseif ($kind==='category') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['slug'=>'foreign']); }
        elseif ($kind==='category_swap') {
            $other = DB::table('page_categories')->where('slug', 'mixed-revision')->value('id');
            self::assertNotNull($other); self::assertNotSame($row->page_category_id, $other);
            DB::table('pages')->where('id', $row->page_id)->update(['page_category_id' => $other]);
            DB::table('text_blocks')->where('page_id', $row->page_id)->update(['page_category_id' => $other]);
        }
        elseif ($kind==='category_language') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['language'=>'pl']); }
        elseif ($kind==='category_type') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['type'=>'foreign']); }
        elseif ($kind==='parent') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['parent_id'=>DB::table('page_categories')->where('slug','fixture-parent')->value('id')]); }
        elseif ($kind==='unknown') { $new=(array)$row; unset($new['id']); $new['uuid']='unknown'; DB::table('text_blocks')->insert($new); }
        elseif ($kind==='stale') { DB::table('questions')->where('uuid','protected-bank')->update(['question'=>'Changed after preview']); }
        else { DB::table('text_blocks')->where('id',$row->id)->update(match($kind) {'locale'=>['locale'=>'pl'],'order'=>['sort_order'=>99],'heading'=>['heading'=>'Manual heading'],'level'=>['level'=>'A1'],'css_class'=>['css_class'=>'manual-class'],default=>['body'=>'Manual']}); }
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_mid_transaction_failure_rolls_back(): void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M39ContentPatch {
            protected function afterUpdate(int $i): void { throw new RuntimeException('Injected failure.'); }
        };
        $p->savePlan($this->path('preview')); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileExists($this->path('backup'));
    }
    public function test_insert_failure_rolls_back_updates_and_new_native_rows(): void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M39ContentPatch {
            protected function afterInsert(int $index): void { if ($index === 1) { throw new RuntimeException('Injected insert failure.'); } }
        };
        $p->savePlan($this->path('preview'));
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));
        self::assertFileExists($this->path('backup'));
    }
    public function test_production_profile_without_verified_working_target_never_writes(): void
    {
        $p=$this->service(); $p->savePlan($this->path('preview'));
        $this->app->detectEnvironment(fn()=>'production');
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_preview_covers_all_nineteen_protected_tables_and_every_non_target_block(): void
    {
        $before = $this->snapshot(); $plan = $this->service()->plan();
        self::assertSame($before, $this->snapshot());
        self::assertSame(['pages', 'page_categories', 'text_blocks', 'tags', 'page_tag', 'page_category_tag', 'tag_text_block',
            'questions', 'question_answers', 'question_options', 'question_option_question', 'question_theory_text_blocks',
            'question_tag', 'question_marker_tag', 'verb_hints', 'question_hints', 'question_variants',
            'saved_grammar_tests', 'saved_grammar_test_questions', 'seed_runs'], array_keys($plan['protected']));
        $ids = array_column($plan['updates'], 'id'); $hash = hash_init('sha256'); $count = 0;
        foreach (DB::table('text_blocks')->whereNotIn('id', $ids)->orderBy('id')->cursor() as $row) {
            hash_update($hash, M39ContentPatch::digest((array) $row)."\n"); $count++;
        }
        self::assertSame(['count' => $count, 'sha256' => hash_final($hash)], $plan['protected']['text_blocks']);
    }

    public function test_finite_three_owner_mapping_keeps_two_shared_root_categories(): void
    {
        $plan = $this->service()->plan();
        self::assertSame(['formal-english', 'mixed-revision', 'mixed-revision'], array_values(M39ContentPatch::CATEGORIES));
        self::assertSame([['formal-english'], ['mixed-revision'], ['mixed-revision']], array_values(M39ContentPatch::ANCESTRIES));
        foreach (M39ContentPatch::NAMES as $identity) {
            self::assertSame(M39ContentPatch::ANCESTRIES[$identity], array_column($plan['pages'][$identity]['category_ancestry'], 'slug'));
        }
        self::assertCount(2, array_unique(array_column(array_column($plan['pages'], 'page'), 'page_category_id')));
    }

    public static function ownerIndices(): array { return [[0], [1], [2]]; }

    #[DataProvider('ownerIndices')]
    public function test_another_allowed_m39_category_is_foreign_for_each_exact_owner(int $index): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        $identity = M39ContentPatch::NAMES[$index];
        $page = DB::table('pages')->where('seeder', $identity)->sole();
        $foreign = M39ContentPatch::CATEGORIES[$identity] === 'formal-english' ? 'mixed-revision' : 'formal-english';
        $category = DB::table('page_categories')->where('slug', $foreign)->value('id');
        self::assertNotSame($page->page_category_id, $category);
        DB::table('pages')->where('id', $page->id)->update(['page_category_id' => $category]);
        DB::table('text_blocks')->where('page_id', $page->id)->update(['page_category_id' => $category]);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_a_non_target_localised_body_edit_after_preview_refuses_apply(): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        DB::table('text_blocks')->where('uuid', 'protected-locale')->update(['body' => 'Unrelated manual content']);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public static function rootConflicts(): array
    {
        $out = [];
        foreach ([0, 1] as $index) {
            foreach (['parent', 'cycle', 'language', 'type'] as $kind) { $out[] = [$index, $kind]; }
        }
        return $out;
    }

    #[DataProvider('rootConflicts')]
    public function test_both_target_roots_reject_wrong_ancestry_language_type_or_cycle(int $index, string $kind): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        $slug = ['formal-english', 'mixed-revision'][$index];
        $category = DB::table('page_categories')->where('slug', $slug)->sole();
        $parent = DB::table('page_categories')->where('slug', 'fixture-parent')->sole();
        DB::table('page_categories')->where('id', $category->id)->update(match ($kind) {
            'parent' => ['parent_id' => $parent->id], 'cycle' => ['parent_id' => $category->id],
            'language' => ['language' => 'pl'], default => ['type' => 'foreign'],
        });
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_absent_nominal_category_title_and_existing_shared_mixed_category_are_preserved(): void
    {
        [$before, $package] = Package::load();
        self::assertArrayNotHasKey('title', $before['targets'][0]['before']['page']['category']);
        foreach ([0, 1, 2] as $i) {
            self::assertSame($before['targets'][$i]['before']['page']['category'], $package['targets'][$i]['after']['page']['category']);
        }
        DB::table('page_categories')->where('slug', 'mixed-revision')->update(['title' => 'Existing shared category title']);
        $category = DB::table('page_categories')->where('slug', 'mixed-revision')->sole();
        $p = $this->service(); $plan = $p->savePlan($this->path('preview'));
        self::assertSame('Existing shared category title', $plan['pages'][M39ContentPatch::NAMES[1]]['category_ancestry'][0]['title']);
        self::assertSame('Existing shared category title', $plan['pages'][M39ContentPatch::NAMES[2]]['category_ancestry'][0]['title']);
        $p->apply($this->path('preview'), $this->path('backup'));
        self::assertSame((array) $category, (array) DB::table('page_categories')->where('id', $category->id)->sole());
        self::assertSame(['status' => 'no-op', 'updated' => 0, 'inserted' => 0], $p->apply($this->path('preview'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
    }

    public function test_category_title_changed_after_preview_is_a_stale_conflict_before_backup(): void
    {
        $p = $this->service(); $p->savePlan($this->path('preview'));
        DB::table('page_categories')->where('slug', 'formal-english')->update(['title' => 'Concurrent title edit']);
        $this->refuses(fn () => $p->apply($this->path('preview'), $this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }

    public function test_git_lf_author_checkout_is_portable_and_fresh_plan_binds_actual_raw_bytes(): void
    {
        foreach (['docs/content/m23-authored-content.v1.json', 'docs/content/m23-author-sources.md'] as $path) {
            File::put($this->root.'/'.$path, str_replace("\r\n", "\n", File::get($this->root.'/'.$path)));
        }
        $p = $this->service(); $plan = $p->savePlan($this->path('preview'));
        foreach (['docs/content/m23-authored-content.v1.json', 'docs/content/m23-author-sources.md'] as $path) {
            self::assertSame(hash_file('sha256', $this->root.'/'.$path), $plan['sources'][$path]);
        }
        self::assertSame('applied', $p->apply($this->path('preview'), $this->path('backup'))['status']);
        self::assertSame(['status'=>'no-op', 'updated'=>0, 'inserted'=>0], $p->apply($this->path('preview'), $this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
    }
}
