<?php

namespace Tests\Feature;

use App\Services\M39PracticeUiPatch;
use App\Support\Database\JsonPageSeeder;
use App\Support\M39AuthoredRevisionPackage as Author;
use App\Support\M39PracticeUiPackage as Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M39PracticeUiPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root; private string $private;
    protected function setUp():void
    {
        parent::setUp();$this->rebuildComposeTestSchema();
        $this->root=storage_path('app/m39-ui-fixture-'.bin2hex(random_bytes(8)));$this->private=$this->root.'/evidence';
        File::makeDirectory($this->private,0700,true);IsolatedTestEnvironment::assertOwnedPath($this->root);
        $package=Package::load();
        preg_match_all("~'((?:app/|tools/|resources/|docs/|public/)[^']+\\.(?:php|js|ps1|json|md))'~",File::get(base_path('app/Services/M39PracticeUiPatch.php')),$matches);
        foreach(array_unique([Package::SOURCE,Author::BEFORE,Author::SOURCE,...$matches[1]]) as $path){
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);File::copy(base_path($path),$this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['slug'=>'unrelated-parent','title'=>'Unrelated','language'=>'uk','type'=>'theory']);
        foreach($package['targets'] as $target){
            $path=$this->root.'/'.$target['path'];File::makeDirectory(dirname($path),0700,true,true);File::put($path,Author::json($target['before']));
            (new class($path) extends JsonPageSeeder{
                public function __construct(private string $path){}
                protected function definitionPath():string{return $this->path;}
            })->run();
            File::put($path,Author::json($target['after']));
        }
        $foreign=(array)DB::table('text_blocks')->first();unset($foreign['id']);$foreign['uuid']='protected-ui-locale';$foreign['locale']='pl';DB::table('text_blocks')->insert($foreign);
        DB::table('questions')->insert(['uuid'=>'protected-ui-question','question'=>'Protected {a1}']);
    }
    private function patcher():M39PracticeUiPatch{return new M39PracticeUiPatch(DB::connection(),$this->root.'/database',$this->private);}
    private function path(string $name):string{return $this->private.'/'.$name.'.json';}
    private function snapshot():array
    {
        $result=[];foreach(['pages','page_categories','text_blocks','tags','tag_text_block','page_tag','page_category_tag','questions','question_answers','question_options','question_theory_text_blocks','seed_runs'] as $table)$result[$table]=DB::table($table)->get()->map(fn($row)=>(array)$row)->all();return $result;
    }
    private function refuses(callable $call):void
    {
        $before=$this->snapshot();try{$call();self::fail('Unsafe UI update accepted.');}catch(RuntimeException){}self::assertSame($before,$this->snapshot());
    }
    public function test_body_only_preview_apply_exclusive_backup_noop_restore():void
    {
        $p=$this->patcher();$before=$this->snapshot();$plan=$p->savePlan($this->path('preview'));
        self::assertSame($before,$this->snapshot());self::assertCount(3,$plan['updates']);self::assertSame([],$plan['inserts']);
        foreach($plan['updates'] as $change){self::assertSame(['body'],array_keys($change['before']));self::assertSame(['body'],array_keys($change['after']));}
        self::assertSame('applied',$p->apply($this->path('preview'),$this->path('backup'))['status']);
        self::assertSame($plan,json_decode(File::get($this->path('backup')),true));
        self::assertSame($plan['protected'],$p->plan()['protected']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$p->apply($this->path('preview'),$this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        self::assertSame(['status'=>'restored','updated'=>3,'deleted'=>0],$p->restore($this->path('backup')));self::assertSame($before,$this->snapshot());
        File::put($this->path('exists'),'{}');$this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('exists')));
    }
    public static function conflicts():array
    {
        return array_map(fn($kind)=>[$kind],['source','master','policy','owner','category','category-swap','category-language','category-type','parent',
            'locale','type','heading','level','order','body','uuid','unknown-row','core-body','protected-locale','question']);
    }
    #[DataProvider('conflicts')]
    public function test_conflicts_stop_before_backup_and_without_writes(string $kind):void
    {
        $p=$this->patcher();$p->savePlan($this->path('preview'));$row=DB::table('text_blocks')->where('type','practice-set')->first();
        if($kind==='source')File::append($this->root.'/'.Package::load()['targets'][0]['path'],'broken');
        elseif($kind==='master')File::append($this->root.'/docs/content/m23-authored-content.v1.json',' ');
        elseif($kind==='policy')File::append($this->root.'/docs/content/m23-author-sources.md',' ');
        elseif($kind==='owner')DB::table('pages')->where('id',$row->page_id)->update(['seeder'=>'Foreign']);
        elseif($kind==='category')DB::table('page_categories')->where('id',$row->page_category_id)->update(['slug'=>'foreign']);
        elseif($kind==='category-swap'){
            $id=DB::table('page_categories')->where('slug','mixed-revision')->value('id');
            DB::table('pages')->where('id',$row->page_id)->update(['page_category_id'=>$id]);DB::table('text_blocks')->where('page_id',$row->page_id)->update(['page_category_id'=>$id]);
        }
        elseif($kind==='category-language')DB::table('page_categories')->where('id',$row->page_category_id)->update(['language'=>'pl']);
        elseif($kind==='category-type')DB::table('page_categories')->where('id',$row->page_category_id)->update(['type'=>'foreign']);
        elseif($kind==='parent')DB::table('page_categories')->where('id',$row->page_category_id)->update(['parent_id'=>DB::table('page_categories')->where('slug','unrelated-parent')->value('id')]);
        elseif($kind==='unknown-row'){$unknown=(array)$row;unset($unknown['id']);$unknown['uuid']='unknown-ui-row';DB::table('text_blocks')->insert($unknown);}
        elseif($kind==='core-body')DB::table('text_blocks')->where('page_id',$row->page_id)->where('sort_order',2)->update(['body'=>'Unexpected core edit']);
        elseif($kind==='protected-locale')DB::table('text_blocks')->where('uuid','protected-ui-locale')->update(['body'=>'Unexpected locale edit']);
        elseif($kind==='question')DB::table('questions')->where('uuid','protected-ui-question')->update(['question'=>'Unexpected bank edit']);
        else DB::table('text_blocks')->where('id',$row->id)->update(match($kind){
            'locale'=>['locale'=>'pl'],'type'=>['type'=>'box'],'heading'=>['heading'=>'Unexpected'],'level'=>['level'=>'A1'],
            'order'=>['sort_order'=>99],'uuid'=>['uuid'=>'unknown-ui-uuid'],default=>['body'=>'Unexpected manual practice body'],
        });
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_mid_transaction_update_failure_rolls_back():void
    {
        $p=new class(DB::connection(),$this->root.'/database',$this->private) extends M39PracticeUiPatch{
            protected function afterUpdate(int $index):void{if($index===1)throw new RuntimeException('Injected UI failure');}
        };
        $p->savePlan($this->path('preview'));$this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));self::assertFileExists($this->path('backup'));
    }
    public function test_partial_ui_package_cannot_apply():void
    {
        $package=Package::load();$row=DB::table('text_blocks')->where('type','practice-set')->first();
        $new=array_values(array_filter($package['targets'][0]['after']['page']['blocks'],fn($b)=>$b['type']==='practice-set'))[0];
        DB::table('text_blocks')->where('id',$row->id)->update(['body'=>$new['body']]);
        $this->refuses(fn()=>$this->patcher()->savePlan($this->path('preview')));self::assertFileDoesNotExist($this->path('preview'));
    }
    public function test_production_profile_without_verified_local_scope_never_writes():void
    {
        $p=$this->patcher();$p->savePlan($this->path('preview'));$this->app->detectEnvironment(fn()=>'production');
        $this->refuses(fn()=>$p->apply($this->path('preview'),$this->path('backup')));self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_row_count_and_all_protected_fields_stay_exact_after_apply():void
    {
        $p=$this->patcher();$plan=$p->savePlan($this->path('preview'));$before=DB::table('text_blocks')->orderBy('id')->get()->map(fn($r)=>(array)$r)->all();
        $p->apply($this->path('preview'),$this->path('backup'));$after=DB::table('text_blocks')->orderBy('id')->get()->map(fn($r)=>(array)$r)->all();
        $changes=array_column($plan['updates'],null,'id');self::assertCount(count($before),$after);
        foreach($before as $i=>$row){if(isset($changes[$row['id']]))$row['body']=$changes[$row['id']]['after']['body'];self::assertSame($row,$after[$i]);}
    }
}
