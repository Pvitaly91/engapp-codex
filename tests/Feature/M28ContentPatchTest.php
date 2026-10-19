<?php

namespace Tests\Feature;

use App\Services\M28ContentPatch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M28EmphasisPackage as Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M28ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root; private string $private;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root=storage_path('app/m28-fixture-'.bin2hex(random_bytes(8))); $this->private=$this->root.'/evidence';
        File::makeDirectory($this->private,0700,true); IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$before,$package]=Package::load();
        $parent=DB::table('page_categories')->insertGetId(['slug'=>'basic-grammar','title'=>'Basic Grammar','language'=>'uk','type'=>'theory']);
        DB::table('page_categories')->insert(['slug'=>'word-order','title'=>'Word Order','language'=>'uk','type'=>'theory','parent_id'=>$parent]);
        preg_match_all("~'(app/[^']+\\.php|resources/views/[^']+\\.php)'~",file_get_contents(base_path('app/Services/M28ContentPatch.php')),$m);
        foreach ([Package::BEFORE,Package::SOURCE,...$m[1]] as $path) { File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true); File::copy(base_path($path),$this->root.'/'.$path); }
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
    private function service(): M28ContentPatch { return new M28ContentPatch(DB::connection(),$this->root.'/database',$this->private); }
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
        self::assertSame($before,$this->snapshot()); self::assertCount(3,$plan['updates']); self::assertCount(20,$plan['inserts']);
        $r=$p->apply($this->path('preview'),$this->path('backup')); self::assertSame('applied',$r['status']);
        self::assertSame($plan,json_decode(File::get($this->path('backup')),true)); self::assertSame($plan['protected'],$p->plan()['protected']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$p->apply($this->path('preview'),$this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status'=>'restored','updated'=>3,'deleted'=>20],$p->restore($this->path('backup'))); self::assertSame($before,$this->snapshot());
        File::put($this->path('exists'),'{}'); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('exists')));
    }
    public static function conflicts(): array { return array_map(fn($x)=>[$x],['source','owner','locale','category','parent','order','body','unknown','stale']); }
    #[DataProvider('conflicts')]
    public function test_conflicts_stop_without_writes(string $kind): void
    {
        $p=$this->service(); $p->savePlan($this->path('preview'));
        $row=DB::table('text_blocks')->where('sort_order',2)->first();
        if ($kind==='source') { [, $pack]=Package::load(); File::append($this->root.'/'.$pack['targets'][0]['path'],'broken'); }
        elseif ($kind==='owner') { DB::table('pages')->where('id',$row->page_id)->update(['seeder'=>'Foreign']); }
        elseif ($kind==='category') { DB::table('page_categories')->where('id',$row->page_category_id)->update(['slug'=>'foreign']); }
        elseif ($kind==='parent') { DB::table('page_categories')->where('slug','basic-grammar')->update(['slug'=>'foreign-parent']); }
        elseif ($kind==='unknown') { $new=(array)$row; unset($new['id']); $new['uuid']='unknown'; DB::table('text_blocks')->insert($new); }
        elseif ($kind==='stale') { DB::table('questions')->where('uuid','protected-bank')->update(['question'=>'Changed after preview']); }
        else { DB::table('text_blocks')->where('id',$row->id)->update(match($kind) {'locale'=>['locale'=>'pl'],'order'=>['sort_order'=>99],default=>['body'=>'Manual']}); }
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_mid_transaction_failure_rolls_back(): void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M28ContentPatch {
            protected function afterUpdate(int $i): void { throw new RuntimeException('Injected failure.'); }
        };
        $p->savePlan($this->path('preview')); $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup'))); self::assertFileExists($this->path('backup'));
    }
}
