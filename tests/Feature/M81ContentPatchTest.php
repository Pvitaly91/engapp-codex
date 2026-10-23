<?php

namespace Tests\Feature;

use App\Services\M81ContentPatch as Patch;
use App\Support\M26DetailPackage as Digest;
use App\Support\M81TransformationsCommonMistakes as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Synthetic SQLite proves writer boundaries; never substitutes for working-local SQL acceptance. */
class M81ContentPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $root;
    private string $private;
    private array $before;
    private array $patch;
    private array $contract;

    protected function setUp():void
    {
        parent::setUp();IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing',app()->environment());self::assertNotSame(base_path(),app()->environmentPath());
        self::assertSame('sqlite',DB::connection()->getDriverName());self::assertSame(':memory:',DB::connection()->getDatabaseName());
        self::assertSame('array',config('cache.default'));self::assertSame('array',config('session.driver'));
        $this->rebuildComposeTestSchema();app()->setLocale('uk');
        $source=str_replace('\\','/',dirname(__DIR__,2));
        [$this->before,$this->patch,,$this->contract]=Package::load($source);
        self::assertSame([147,150,146,153,38,43],Package::PAGE_IDS);
        self::assertSame([23,23,23,23,9,9],Package::CATEGORY_IDS);
        self::assertSame([6,6,6,6,6,6],Package::CONTINUING_COUNTS);
        self::assertSame(['B1','B1','B1','B2','A2','A2'],Package::LEVELS);
        $this->root=storage_path('app/m81-native-fixture-'.bin2hex(random_bytes(7)));$this->private=$this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));File::makeDirectory($this->private,0700,true);
        foreach (Patch::sourcePaths($this->patch,$this->contract,$source) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);File::copy($source.'/'.$path,$this->root.'/'.$path);
        }
        foreach ([[1,'foreign'],[9,'common-mistakes'],[23,'sentence-transformations']] as [$id,$slug]) {
            DB::table('page_categories')->insert(['id'=>$id,'parent_id'=>null,'slug'=>$slug,'title'=>$slug,'type'=>'theory','language'=>'uk']);
        }
        $time='2026-01-01 12:00:00';
        foreach ($this->before['targets'] as $i=>$target) {
            $old=$target['before'];$owner=$target['identity'];
            DB::table('pages')->insert(['id'=>Package::PAGE_IDS[$i],'page_category_id'=>Package::CATEGORY_IDS[$i],
                'seeder'=>$owner,'slug'=>$old['slug'],'title'=>$old['page']['title'],'text'=>$old['page']['subtitle_text'],'type'=>'theory']);
            $configs=[['type'=>'subtitle','column'=>'header','body'=>$old['page']['subtitle_html'],
                'level'=>$old['page']['subtitle_level']??null,'uuid_key'=>$old['page']['subtitle_uuid_key']??'subtitle'],...$old['page']['blocks']];
            foreach ($configs as $position=>$config) {
                DB::table('text_blocks')->insert(['uuid'=>Digest::uuid($owner,$config,$position),'page_id'=>Package::PAGE_IDS[$i],
                    'page_category_id'=>Package::CATEGORY_IDS[$i],'locale'=>'uk','seeder'=>$owner,'sort_order'=>$position,
                    'type'=>$config['type'],'column'=>$config['column'],'heading'=>$config['heading']??null,'level'=>$config['level']??null,
                    'css_class'=>$config['css_class']??null,'body'=>$config['body'],'created_at'=>$time,'updated_at'=>$time]);
            }
            $hero=DB::table('text_blocks')->where('page_id',Package::PAGE_IDS[$i])->where('sort_order',1)->value('uuid');
            for ($n=0;$n<13;++$n) {
                $id=8000000+$i*100+$n;$uuid='m81-synthetic-'.$id;
                DB::table('questions')->insert(['id'=>$id,'uuid'=>$uuid,'question'=>'Ізольований fixture, не live question.',
                    'difficulty'=>2,'level'=>$n<12?Package::LEVELS[$i]:'C2','type'=>'4','seeder'=>$this->contract['targets'][$i]['bank_seeders'][0],
                    'created_at'=>$time,'updated_at'=>$time]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid'=>$uuid,'text_block_uuid'=>$hero,'position'=>0,
                    'created_at'=>$time,'updated_at'=>$time]);
            }
            $foreign=(array)DB::table('text_blocks')->where('page_id',Package::PAGE_IDS[$i])->where('sort_order',1)->sole();unset($foreign['id']);
            foreach (['en','pl'] as $locale) { $foreign['uuid']='m81-foreign-'.$i.'-'.$locale;$foreign['locale']=$locale;$foreign['body']='Unchanged '.$locale;DB::table('text_blocks')->insert($foreign); }
        }
        DB::connection()->getSchemaBuilder()->create('m81_protected_progress',static function(Blueprint $table):void { $table->id();$table->text('value'); });
        DB::table('m81_protected_progress')->insert(['value'=>'Unchanged synthetic progress.']);
    }
    private function service():M81SyntheticNativePatch { return new M81SyntheticNativePatch(DB::connection(),$this->root.'/database',$this->private); }
    private function file(string $name):string { return $this->private.'/'.$name.'.json'; }
    private function snapshot():array
    {
        $out=[];$tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false);sort($tables);
        foreach ($tables as $table) {
            if (str_starts_with($table,'sqlite_')) { continue; }
            $q=DB::table($table);$columns=DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach (in_array('id',$columns,true)?['id']:$columns as $column) { $q->orderBy($column); }
            $out[$table]=$q->get()->map(static fn($row)=>(array)$row)->all();
        }
        return $out;
    }
    private function refused(callable $call):void
    {
        $error=null;try { $call(); } catch(RuntimeException $caught) { $error=$caught; }self::assertInstanceOf(RuntimeException::class,$error);
    }
    public function test_six_new_practices_and_twelve_metadata_transitions_are_atomic_exact_and_repeat_as_noop():void
    {
        $service=$this->service();$before=$this->snapshot();$plan=$service->savePlan($this->file('preview'));
        self::assertSame($before,$this->snapshot());self::assertSame('before',$plan['state']);
        $expectedUpdates=array_sum(array_map(static fn(array $target):int=>count($target['before']['page']['blocks'])+2,$this->before['targets']));
        self::assertSame(48,$expectedUpdates,'Six owners × (Page.text + subtitle + six continuing blocks), independently of planned totals.');
        self::assertCount($expectedUpdates,$plan['updates']);self::assertCount(6,$plan['inserts']);self::assertSame([],$plan['deletes']??[]);
        $metadata=[];
        foreach ($plan['updates'] as $update) {
            self::assertSame($update['table']==='pages'?['text']:['body','type','heading'],array_keys($update['after']));
            if ($update['table']==='text_blocks'&&$update['before']['type']!==$update['after']['type']) { $metadata[]=$update; }
        }
        self::assertCount(12,$metadata);
        foreach ($metadata as $update) {
            self::assertSame('box',$update['before']['type']);self::assertContains($update['after']['type'],['usage-panels','comparison-table']);
            self::assertNotNull($update['before']['heading']);self::assertNull($update['after']['heading']);
            self::assertNull(DB::table('text_blocks')->where('id',$update['id'])->value('level'));
        }
        foreach ($plan['inserts'] as $insert) { self::assertSame('text_blocks',$insert['table']);self::assertSame('practice-set',$insert['fields']['type']); }
        $result=$service->apply($this->file('preview'),$this->file('backup'));
        self::assertSame('applied',$result['status']);self::assertSame(6,$result['inserted']);self::assertSame($expectedUpdates,$result['updated']);
        self::assertSame($plan,json_decode(File::get($this->file('backup')),true,flags:JSON_THROW_ON_ERROR));
        $after=$this->snapshot();self::assertCount(count($before['text_blocks'])+6,$after['text_blocks']);
        foreach ($before['text_blocks'] as $row) {
            $now=(array)DB::table('text_blocks')->where('id',$row['id'])->sole();
            if ($row['locale']==='uk') {
                $i=array_search($row['page_id'],Package::PAGE_IDS,true);$new=$this->patch['targets'][$i]['after']['page'];
                $expected=$row['sort_order']===0?['body'=>$new['subtitle_html'],'type'=>'subtitle','heading'=>null]:$new['blocks'][$row['sort_order']-1];
                foreach (['body','type','heading'] as $field) { self::assertSame($expected[$field]??null,$now[$field]);unset($row[$field],$now[$field]); }
            }
            self::assertSame($row,$now,'Old IDs/UUIDs/SQL levels/order/columns/timestamps and EN/PL are exact.');
        }
        foreach ($before as $table=>$rows) { if (!in_array($table,['pages','text_blocks'],true)) { self::assertSame($rows,$after[$table],$table); } }
        foreach (Package::PAGE_IDS as $i=>$id) {
            $practice=DB::table('text_blocks')->where('page_id',$id)->where('locale','uk')->where('type','practice-set')->sole();
            self::assertSame(Package::LEVELS[$i],$practice->level);self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i]+1,$practice->sort_order);
        }
        $repeat=$service->savePlan($this->file('repeat'));self::assertSame('after',$repeat['state']);self::assertSame([],$repeat['updates']);self::assertSame([],$repeat['inserts']);
        foreach (['preview','repeat'] as $name) { self::assertSame('no-op',$service->apply($this->file($name),$this->file('unused-'.$name))['status']);self::assertFileDoesNotExist($this->file('unused-'.$name));self::assertSame($after,$this->snapshot()); }
    }
    public function test_first_last_update_and_insert_failures_roll_back_all_six_owners():void
    {
        foreach (['first-update','last-update','first-insert','last-insert'] as $stage) {
            $s=$this->service();$p=$s->savePlan($this->file('plan-'.$stage));$before=$this->snapshot();
            if (str_ends_with($stage,'update')) { $s->failUpdate=str_starts_with($stage,'first')?0:count($p['updates'])-1; }
            else { $s->failInsert=str_starts_with($stage,'first')?0:count($p['inserts'])-1; }
            $this->refused(fn()=>$s->apply($this->file('plan-'.$stage),$this->file('backup-'.$stage)));self::assertSame($before,$this->snapshot());
            self::assertSame($p,$s->plan());self::assertFileExists($this->file('backup-'.$stage));
        }
    }
    public function test_foreign_owner_category_locale_metadata_range_and_bank_fail_without_writes():void
    {
        $s=$this->service();
        foreach ([['pages',43,['seeder'=>'Foreign\\Owner','page_category_id'=>1,'slug'=>'foreign']],
            ['text_blocks',DB::table('text_blocks')->where('page_id',146)->where('sort_order',2)->value('id'),
                ['level'=>'B1-B2','locale'=>'en','column'=>'right','heading'=>'Unknown','type'=>'usage-panels']],
            ['questions',8000000,['seeder'=>'Foreign\\Bank','type'=>'1','level'=>'A1-B1']]] as [$table,$id,$changes]) {
            $old=(array)DB::table($table)->where('id',$id)->sole();
            foreach ($changes as $field=>$value) {
                DB::table($table)->where('id',$id)->update([$field=>$value]);$before=$this->snapshot();$this->refused(fn()=>$s->plan());
                self::assertSame($before,$this->snapshot());DB::table($table)->where('id',$id)->update([$field=>$old[$field]]);
            }
        }
    }
    public function test_stale_body_protected_progress_plan_and_backup_collision_are_fail_closed():void
    {
        $s=$this->service();$s->savePlan($this->file('preview'));
        $id=DB::table('text_blocks')->where('page_id',147)->where('sort_order',1)->value('id');$body=DB::table('text_blocks')->where('id',$id)->value('body');
        DB::table('text_blocks')->where('id',$id)->update(['body'=>$body.' stale']);$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('no-body')));self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-body'));
        DB::table('text_blocks')->where('id',$id)->update(['body'=>$body]);
        DB::table('m81_protected_progress')->where('id',1)->update(['value'=>'Later synthetic progress']);$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('no-progress')));self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-progress'));
        DB::table('m81_protected_progress')->where('id',1)->update(['value'=>'Unchanged synthetic progress.']);
        File::put($this->file('occupied'),'unrelated backup');$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('occupied')));self::assertSame($before,$this->snapshot());self::assertSame('unrelated backup',File::get($this->file('occupied')));
        $p=json_decode(File::get($this->file('preview')),true,flags:JSON_THROW_ON_ERROR);$p['updates'][0]['after']['text']='Tampered';File::put($this->file('tampered'),json_encode($p));
        $this->refused(fn()=>$s->apply($this->file('tampered'),$this->file('no-tampered')));self::assertSame($before,$this->snapshot());
    }
    public function test_cli_has_no_ambient_production_or_backup_only_authority():void
    {
        $before=$this->snapshot();
        foreach ([[],['--local-target'=>'gramlyze.ub','--local-proof'=>'local-proof-'.str_repeat('a',32).'.json'],
            ['--local-target'=>'gramlyze.loc','--local-proof'=>'../proof.json'],
            ['--local-target'=>'gramlyze.loc','--local-proof'=>'local-proof-'.str_repeat('a',32).'.json','--backup'=>'without-apply.json']] as $args) {
            $this->artisan('content:patch-transformations-common-mistakes-m81',$args)->assertExitCode(1);self::assertSame($before,$this->snapshot());
        }
    }
}

class M81SyntheticNativePatch extends Patch
{
    public ?int $failUpdate=null;
    public ?int $failInsert=null;
    protected function afterUpdate(int $index):void { if ($this->failUpdate===$index) { throw new RuntimeException('Injected isolated update failure.'); } }
    protected function afterInsert(int $index):void { if ($this->failInsert===$index) { throw new RuntimeException('Injected isolated insert failure.'); } }
}
