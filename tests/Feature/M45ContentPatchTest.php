<?php

namespace Tests\Feature;

use App\Services\M45ContentPatch as Patch;
use App\Support\M45FutureComparisonsPackage as Package;
use App\Support\Database\JsonPageSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

class M45ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $evidence;
    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema();
        $this->root=storage_path('app/m45-fixture-'.bin2hex(random_bytes(8)));
        $this->evidence=$this->root.'/evidence';
        File::makeDirectory($this->evidence,0700,true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$before,$source]=Package::load();
        DB::table('page_categories')->insert(['slug'=>'maibutni-formy','title'=>'Майбутні форми','type'=>'theory','language'=>'uk']);
        foreach(Patch::sourcePaths($before,$source) as $path){
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);
            File::copy(base_path($path),$this->root.'/'.$path);
        }
        foreach($source['targets'] as $i=>$target){
            $path=$this->root.'/'.$target['path']; File::put($path,Package::json($before['targets'][$i]['before']));
            (new class($path) extends JsonPageSeeder{
                public function __construct(private string $path){}
                protected function definitionPath():string{return $this->path;}
            })->run();
            File::put($path,Package::json($target['after']));
        }
        $foreign=(array)DB::table('text_blocks')->first();unset($foreign['id']);
        $foreign['uuid']='m45-protected-pl';$foreign['locale']='pl';DB::table('text_blocks')->insert($foreign);
        DB::table('questions')->insert(['uuid'=>'m45-protected-question','question'=>'Protected question {a1}','type'=>'4']);
        DB::connection()->getSchemaBuilder()->create('arbitrary_m45_protected',function($s){$s->id();$s->text('value');});
        DB::table('arbitrary_m45_protected')->insert(['value'=>'unchanged']);
    }
    private function service():Patch{return new Patch(DB::connection(),$this->root.'/database',$this->evidence);}
    private function path(string $name):string{return $this->evidence.'/'.$name.'.json';}
    private function snapshot():array
    {
        $out=[];$tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false);sort($tables);
        foreach($tables as $table){$q=DB::table($table);$columns=DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach(in_array('id',$columns,true)?['id']:$columns as $column)$q->orderBy($column);
            $out[$table]=$q->get()->map(fn($r)=>(array)$r)->all();}
        return $out;
    }
    private function refuses(callable $call):void
    {
        $before=$this->snapshot();try{$call();self::fail('Unsafe write accepted');}catch(RuntimeException){}
        self::assertSame($before,$this->snapshot());
    }
    public function test_preview_exclusive_backup_transaction_exact_projection_and_repeated_noop():void
    {
        $patch=$this->service();$before=$this->snapshot();$plan=$patch->savePlan($this->path('preview'));
        self::assertSame($before,$this->snapshot());self::assertCount(3,$plan['inserts']);
        self::assertSame([1,1,1],array_map(fn($owner)=>count(array_filter($plan['inserts'],fn($r)=>$r['seeder']===$owner)),Patch::NAMES));
        File::put($this->path('exists'),'{}');$this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('exists')));
        self::assertSame('applied',$patch->apply($this->path('preview'),$this->path('backup'))['status']);
        self::assertSame($plan,json_decode(File::get($this->path('backup')),true));
        $after=$patch->plan();self::assertSame($plan['protected'],$after['protected']);
        self::assertSame([], $after['updates']);self::assertSame([], $after['inserts']);
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$patch->apply($this->path('preview'),$this->path('unused')));
        self::assertFileDoesNotExist($this->path('unused'));
        $this->refuses(fn()=>$patch->restore($this->path('backup')));
    }
    public function test_stale_frozen_source_unknown_block_and_foreign_locale_refuse_before_backup():void
    {
        $patch=$this->service();$patch->savePlan($this->path('preview'));
        File::append($this->root.'/'.Package::MASTER_PATH,' ');
        $this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
        File::copy(base_path(Package::MASTER_PATH),$this->root.'/'.Package::MASTER_PATH);
        DB::table('text_blocks')->where('uuid','m45-protected-pl')->update(['body'=>'Manual foreign change']);
        $this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('backup')));
        self::assertFileDoesNotExist($this->path('backup'));
    }
    public function test_rollback_after_insert_preserves_every_table():void
    {
        $patch=new class(DB::connection(),$this->root.'/database',$this->evidence) extends Patch{
            protected function afterInsert(int $index):void{if($index===1)throw new RuntimeException('Injected failure');}
        };
        $patch->savePlan($this->path('preview'));
        $this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('backup')));
        self::assertFileExists($this->path('backup'));
    }
    public function test_protected_side_effect_rolls_back_and_production_profile_has_no_write_authority():void
    {
        $patch=new class(DB::connection(),$this->root.'/database',$this->evidence) extends Patch{
            protected function afterUpdate(int $index):void{if($index===0)$this->db->table('arbitrary_m45_protected')->update(['value'=>'side effect']);}
        };
        $patch->savePlan($this->path('preview'));$this->refuses(fn()=>$patch->apply($this->path('preview'),$this->path('backup')));
        $this->app->detectEnvironment(fn()=>'production');
        $this->refuses(fn()=>$this->service()->apply($this->path('preview'),$this->path('prod-backup')));
        self::assertFileDoesNotExist($this->path('prod-backup'));
    }
    public function test_preview_binds_all_actual_tables_and_keeps_protected_owner_metadata():void
    {
        $snapshot=$this->snapshot();$plan=$this->service()->plan();
        self::assertSame(array_keys($snapshot),array_keys($plan['protected']));
        self::assertArrayHasKey('arbitrary_m45_protected',$plan['protected']);
        foreach($plan['pages'] as $page)self::assertSame(['maibutni-formy'],array_column($page['category_ancestry'],'slug'));
        foreach($plan['updates'] as $change)self::assertSame($change['table']==='pages'?['text']:['type','body'],array_keys($change['after']));
        DB::table('page_categories')->where('slug','maibutni-formy')->update(['language'=>'pl']);
        $this->refuses(fn()=>$this->service()->savePlan($this->path('foreign')));
    }
}
