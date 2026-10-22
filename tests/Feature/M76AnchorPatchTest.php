<?php

namespace Tests\Feature;

use App\Services\M76AnchorPatch as Patch;
use App\Support\M76PronounRelations as Package;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Invented SQLite rows plus public publisher sources; never loads private local evidence or working .env. */
class M76AnchorPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $private;
    private array $scope;
    private array $beforeRow;

    protected function setUp():void
    {
        parent::setUp();$this->rebuildComposeTestSchema();
        self::assertSame('testing',app()->environment());self::assertNotSame(base_path(),app()->environmentPath());
        self::assertSame('sqlite',DB::connection()->getDriverName());self::assertSame(':memory:',DB::connection()->getDatabaseName());
        self::assertSame('array',config('cache.default'));self::assertSame('array',config('session.driver'));
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        $this->root=storage_path('app/m76-anchor-fixture-'.bin2hex(random_bytes(7)));$this->private=$this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));File::makeDirectory($this->private,0700,true);
        IsolatedTestEnvironment::assertOwnedPath($this->root);
        [$old,$next,$oldMaster,$newMaster]=$this->publicSources();$t=Patch::exactTransition($old,$next,$oldMaster,$newMaster);
        File::put($this->root.'/source.json',json_encode($t,JSON_THROW_ON_ERROR));
        $time='2000-01-01 12:00:00';
        DB::table('page_categories')->insert(['id'=>20,'title'=>'Synthetic pronoun category','slug'=>'synthetic-pronouns',
            'type'=>'theory','language'=>'uk','created_at'=>$time,'updated_at'=>$time]);
        DB::table('pages')->insert(['id'=>127,'page_category_id'=>20,'slug'=>'reciprocal-pronouns-each-other-one-another',
            'title'=>'Reciprocal pronouns','text'=>'Unchanged synthetic subtitle.','type'=>'theory','seeder'=>Patch::OWNER,
            'created_at'=>$time,'updated_at'=>$time]);
        $row=['id'=>Patch::ROW_ID,'uuid'=>'a4b3630c-46f0-5de6-914d-459208252fc6','page_id'=>127,'page_category_id'=>20,
            'locale'=>'uk','type'=>'hero','level'=>null,'column'=>'header','heading'=>null,'css_class'=>null,'sort_order'=>1,
            'body'=>$t['before'],'seeder'=>Patch::OWNER,'created_at'=>$time,'updated_at'=>$time];
        DB::table('text_blocks')->insert($row);
        $foreign=$row;$foreign['id']=4078;$foreign['uuid']='synthetic-m76-anchor-sentinel';$foreign['locale']='en';
        $foreign['type']='box';$foreign['sort_order']=2;$foreign['body']='Protected foreign-language sentinel.';
        DB::table('text_blocks')->insert($foreign);
        for($i=0;count($this->tables())<46;++$i){
            $name='m76_anchor_synthetic_'.$i;
            DB::connection()->getSchemaBuilder()->create($name,static function(Blueprint $t):void{$t->id();$t->text('value');});
            DB::table($name)->insert(['id'=>1,'value'=>'Protected invented progress '.$i]);
        }
        self::assertCount(46,$this->tables());$schema=[];
        foreach($this->tables() as $table){$schema[$table]=DB::connection()->getSchemaBuilder()->getColumns($table);}
        $this->beforeRow=(array)DB::table('text_blocks')->where('id',4077)->sole();
        $this->scope=$t+['row_before'=>$this->beforeRow,'schema'=>$schema,
            'baseline'=>['sql_after'=>Patch::fingerprints(DB::connection())],
            'sources'=>['source.json'=>hash_file('sha256',$this->root.'/source.json')]];
    }
    protected function tearDown():void
    {
        try{if(isset($this->root)&&is_dir($this->root)){IsolatedTestEnvironment::assertOwnedPath($this->root);File::deleteDirectory($this->root);}}
        finally{parent::tearDown();}
    }
    private function publicSources():array
    {
        $root=dirname(__DIR__,2);$read=static fn($p)=>json_decode(file_get_contents($root.'/'.$p),true,flags:JSON_THROW_ON_ERROR);
        return [$read(Patch::PREVIOUS_PATCH),$read(Package::SOURCE),$read(Patch::PREVIOUS_MASTER),$read(Package::MASTER)];
    }
    private function tables():array
    {
        $tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false);sort($tables);return $tables;
    }
    private function service():M76SyntheticAnchorPatch
    {
        return new M76SyntheticAnchorPatch(DB::connection(),$this->root.'/database',$this->private,$this->scope);
    }
    private function file(string $name):string{return $this->private.'/'.$name.'.json';}
    private function rejected(callable $call):void
    {
        $error=null;try{$call();}catch(RuntimeException $e){$error=$e;}self::assertInstanceOf(RuntimeException::class,$error);
    }
    public function test_exact_public_successor_changes_only_version_anchor_and_derived_raw_intro_hash():void
    {
        [$old,$next,$oldMaster,$newMaster]=$this->publicSources();
        $t=Patch::exactTransition($old,$next,$oldMaster,$newMaster);
        self::assertSame('b07fd5ca744d132e566d8270dcbd45b7762c2f4ac5e95221c5674936a38dcea6',hash('sha256',$t['before']));
        self::assertSame('7ae6e44375b57281a98656380be6d1b5f0b1cb8a6038ef3c593fa95c220d5803',hash('sha256',$t['after']));
        $oldData=json_decode($t['before'],true,flags:JSON_THROW_ON_ERROR);$newData=json_decode($t['after'],true,flags:JSON_THROW_ON_ERROR);
        self::assertSame(strip_tags($oldData['intro']),strip_tags($newData['intro']));
        self::assertSame(1,substr_count($newData['intro'],'id="block-4077"'));
        self::assertSame(Patch::PREVIOUS_PATCH_SHA,hash_file('sha256',dirname(__DIR__,2).'/'.Patch::PREVIOUS_PATCH));
        foreach([
            static function(&$p,&$m):void{$p['targets'][0]['after']['page']['title']='Unrelated';},
            static function(&$p,&$m):void{$m['lessons'][2]['subtitle_text']='Unrelated';},
            static function(&$p,&$m):void{$m['editorial_review']['expected_http_metadata']['metadata'][2]['hero_intro_sha256']=str_repeat('0',64);},
            static function(&$p,&$m):void{$d=json_decode($p['targets'][2]['after']['page']['blocks'][0]['body'],true);$d['intro'].=' Extra text.';$p['targets'][2]['after']['page']['blocks'][0]['body']=json_encode($d);},
        ] as $mutate){$p=$next;$m=$newMaster;$mutate($p,$m);$this->rejected(fn()=>Patch::exactTransition($old,$p,$oldMaster,$m));}
    }
    public function test_one_body_apply_exclusive_backup_repeated_noop_and_all_original_fields_are_preserved():void
    {
        $service=$this->service();$plan=$service->savePlan($this->file('preview'));
        self::assertSame('before',$plan['state']);self::assertCount(1,$plan['updates']);self::assertSame([],$plan['inserts']);
        self::assertSame(['body'],array_keys($plan['updates'][0]['before']));self::assertCount(46,$plan['protected']);
        $result=$service->apply($this->file('preview'),$this->file('backup'));
        self::assertSame(['applied',1,0],[$result['status'],$result['updated'],$result['inserted']]);
        self::assertSame(file_get_contents($this->file('preview')),file_get_contents($this->file('backup')));
        $after=(array)DB::table('text_blocks')->where('id',4077)->sole();
        self::assertSame(array_replace($this->beforeRow,['body'=>$this->scope['after']]),$after);
        self::assertSame($this->scope['baseline']['sql_after'],Patch::fingerprints(DB::connection(),[
            'id'=>4077,'before'=>$this->scope['before'],'after'=>$this->scope['after']]));
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$service->apply($this->file('preview'),$this->file('unused-backup')));
        self::assertFileDoesNotExist($this->file('unused-backup'));self::assertSame('after',$service->plan()['state']);
    }
    public function test_target_foreign_metadata_timestamp_and_manual_body_changes_fail_closed():void
    {
        foreach(['uuid'=>'foreign','locale'=>'en','level'=>'A2','column'=>'left','updated_at'=>'2001-01-01 00:00:00','body'=>'Manual foreign content.'] as $field=>$value){
            DB::table('text_blocks')->where('id',4077)->update([$field=>$value]);$this->rejected(fn()=>$this->service()->plan());
            DB::table('text_blocks')->where('id',4077)->update([$field=>$this->beforeRow[$field]]);
        }
        self::assertSame('before',$this->service()->plan()['state']);
    }
    public function test_unrelated_rows_schema_and_sources_cannot_be_omitted_from_all_table_protection():void
    {
        DB::table('text_blocks')->where('id',4078)->update(['body'=>'Changed foreign sentinel.']);
        $this->rejected(fn()=>$this->service()->plan());
        DB::table('text_blocks')->where('id',4078)->update(['body'=>'Protected foreign-language sentinel.']);
        File::append($this->root.'/source.json',"\n");$this->rejected(fn()=>$this->service()->plan());
        File::put($this->root.'/source.json',json_encode(array_intersect_key($this->scope,['before'=>true,'after'=>true]),JSON_THROW_ON_ERROR));
        DB::connection()->getSchemaBuilder()->table('m76_anchor_synthetic_0',static function(Blueprint $t):void{$t->string('foreign_column')->nullable();});
        $this->rejected(fn()=>$this->service()->plan());
    }
    public function test_stale_plan_backup_collision_and_postupdate_failure_do_not_commit_a_body():void
    {
        $service=$this->service();$service->savePlan($this->file('preview'));
        File::put($this->file('collision'),'Existing backup bytes.');
        $this->rejected(fn()=>$service->apply($this->file('preview'),$this->file('collision')));
        self::assertSame('Existing backup bytes.',file_get_contents($this->file('collision')));
        self::assertSame($this->beforeRow,(array)DB::table('text_blocks')->where('id',4077)->sole());
        $service->failAfterUpdate=true;
        $this->rejected(fn()=>$service->apply($this->file('preview'),$this->file('rollback-backup')));
        self::assertSame($this->beforeRow,(array)DB::table('text_blocks')->where('id',4077)->sole());
        $service->failAfterUpdate=false;
        DB::table('text_blocks')->where('id',4078)->update(['body'=>'Concurrent changed content.']);
        $this->rejected(fn()=>$service->apply($this->file('preview'),$this->file('stale-backup')));
        self::assertFileDoesNotExist($this->file('stale-backup'));
        self::assertSame($this->beforeRow,(array)DB::table('text_blocks')->where('id',4077)->sole());
    }
    public function test_cli_without_explicit_local_target_and_nonce_fails_before_database_access():void
    {
        $this->artisan('content:patch-m76-anchor',['--plan'=>'probe.json'])->assertExitCode(1);
    }
}

/** Only private evidence/pins are replaced; real planner, fingerprints and M26 transaction code execute. */
final class M76SyntheticAnchorPatch extends Patch
{
    public bool $failAfterUpdate=false;
    public function __construct(Connection $db,string $databasePath,string $directory,private array $fixture)
    {
        parent::__construct($db,$databasePath,$directory);
    }
    protected function evidence():array{return $this->fixture;}
    protected function afterUpdate(int $index):void
    {
        if($this->failAfterUpdate){throw new RuntimeException('Injected after-body failure.');}
    }
}

