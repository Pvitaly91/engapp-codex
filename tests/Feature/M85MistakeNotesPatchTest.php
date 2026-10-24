<?php

namespace Tests\Feature;

use App\Services\M85MistakeNotesPatch as Patch;
use App\Support\M26DetailPackage;
use App\Support\M85VerbToBe as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Synthetic SQLite transaction tests, not actual-local acceptance evidence. */
class M85MistakeNotesPatchTest extends TestCase
{
    use RebuildsComposeTestSchema;
    private string $source;
    private string $root;
    private string $private;
    private array $previous;
    private array $next;
    private array $baseline;

    protected function setUp(): void
    {
        parent::setUp(); IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing',app()->environment()); self::assertSame('sqlite',DB::connection()->getDriverName());
        self::assertSame(':memory:',DB::connection()->getDatabaseName()); self::assertSame('array',config('cache.default'));
        self::assertSame('array',config('session.driver')); self::assertNotSame(base_path(),app()->environmentPath());
        $this->rebuildComposeTestSchema();$this->source=str_replace('\\','/',dirname(__DIR__,2));
        [, $this->previous,$this->next]=Patch::revision($this->source);
        $this->root=storage_path('app/m85-note-fixture-'.bin2hex(random_bytes(7)));$this->private=$this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app')); File::makeDirectory($this->private,0700,true);
        foreach(Patch::syncPaths($this->source) as $path){
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);File::copy($this->source.'/'.$path,$this->root.'/'.$path);
        }
        DB::table('page_categories')->insert(['id'=>2,'parent_id'=>null,'slug'=>'basic-grammar','title'=>'Basic','type'=>'theory','language'=>'uk']);
        DB::table('page_categories')->insert(['id'=>8,'parent_id'=>2,'slug'=>'verb-to-be','title'=>'Be','type'=>'theory','language'=>'uk']);
        $time='2026-01-01 12:00:00';
        foreach($this->previous['targets'] as $i=>$target){
            $data=$target['after'];$owner=$target['identity'];$page=Package::PAGE_IDS[$i];
            DB::table('pages')->insert(['id'=>$page,'page_category_id'=>8,'slug'=>$data['slug'],'title'=>$data['page']['title'],
                'text'=>$data['page']['subtitle_text'],'type'=>'theory','seeder'=>$owner,'created_at'=>$time,'updated_at'=>$time]);
            $subtitle=['type'=>'subtitle','column'=>'header','body'=>$data['page']['subtitle_html'],'level'=>null,
                'uuid_key'=>$data['page']['subtitle_uuid_key']??'subtitle'];
            foreach([$subtitle,...$data['page']['blocks']] as $position=>$block){
                $id=850000+$i*100+$position;
                foreach(Patch::TARGETS as $targetRow){if($targetRow['owner_index']===$i&&$targetRow['source_index']+1===$position){$id=$targetRow['id'];}}
                DB::table('text_blocks')->insert(['id'=>$id,'uuid'=>M26DetailPackage::uuid($owner,$block,$position),
                    'page_id'=>$page,'page_category_id'=>8,'locale'=>'uk','sort_order'=>$position,'type'=>$block['type'],
                    'column'=>$block['column'],'heading'=>$block['heading']??null,'css_class'=>$block['css_class']??null,
                    'level'=>$block['level']??null,'body'=>$block['body'],'seeder'=>$owner,'created_at'=>$time,'updated_at'=>$time]);
            }
            foreach(['en','pl'] as $locale){DB::table('text_blocks')->insert(['uuid'=>'m85-note-foreign-'.$i.'-'.$locale,
                'page_id'=>$page,'page_category_id'=>8,'locale'=>$locale,'sort_order'=>1,'type'=>'box','column'=>'left',
                'body'=>'Keep exact foreign '.$locale,'level'=>null,'seeder'=>$owner,'created_at'=>$time,'updated_at'=>$time]);}
        }
        DB::table('questions')->insert(['id'=>850001,'uuid'=>'m85-note-old-question','question'=>'Untouched synthetic question.',
            'type'=>'4','level'=>'A1','seeder'=>'SyntheticM85OldBank','created_at'=>$time,'updated_at'=>$time]);
        DB::table('question_options')->insert(['id'=>850001,'option'=>'Untouched old answer','created_at'=>$time,'updated_at'=>$time]);
        DB::table('question_answers')->insert(['id'=>850001,'question_id'=>850001,'option_id'=>850001,'marker'=>'a1','created_at'=>$time,'updated_at'=>$time]);
        DB::table('saved_grammar_tests')->insert(['id'=>850001,'uuid'=>'m85-note-old-saved','slug'=>'m85-note-old-saved','name'=>'Old saved','created_at'=>$time,'updated_at'=>$time]);
        DB::table('saved_grammar_test_questions')->insert(['id'=>850001,'saved_grammar_test_id'=>850001,
            'question_uuid'=>'m85-note-old-question','position'=>0,'created_at'=>$time,'updated_at'=>$time]);
        foreach(['m85_note_progress','m85_note_course_membership'] as $table){
            DB::connection()->getSchemaBuilder()->create($table,static function(Blueprint $schema):void{$schema->id();$schema->text('payload');});
            DB::table($table)->insert(['payload'=>'Keep exact protected state.']);
        }
        foreach(Patch::TARGETS as $target){self::assertSame($target['uuid'],DB::table('text_blocks')->where('id',$target['id'])->value('uuid'));}
        $this->baseline=(new M85SyntheticMistakeNotes(DB::connection(),$this->root.'/database',$this->private))->rawFingerprints();
    }
    private function service():M85SyntheticMistakeNotes
    {
        $service=new M85SyntheticMistakeNotes(DB::connection(),$this->root.'/database',$this->private);$service->baselineSql=$this->baseline;return $service;
    }
    private function file(string $name):string{return $this->private.'/'.$name.'.json';}
    private function snapshot():array
    {
        $tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false);sort($tables);$out=[];
        foreach($tables as $table){$q=DB::table($table);$columns=DB::connection()->getSchemaBuilder()->getColumnListing($table);
            foreach(in_array('id',$columns,true)?['id']:$columns as $column){$q->orderBy($column);}
            $out[$table]=$q->get()->map(static fn($r)=>(array)$r)->all();}return $out;
    }
    private function refused(callable $call):void
    {
        $error=null;try{$call();}catch(RuntimeException $e){$error=$e;}self::assertInstanceOf(RuntimeException::class,$error);
    }

    public function test_preview_then_apply_updates_two_body_cells_only_and_both_repeat_paths_are_noop():void
    {
        $service=$this->service();$before=$this->snapshot();$plan=$service->savePlan($this->file('preview'));
        self::assertSame($before,$this->snapshot());self::assertSame('before',$plan['state']);self::assertCount(2,$plan['updates']);self::assertSame([],$plan['inserts']);
        self::assertSame([1313,1288],array_column($plan['updates'],'id'));
        foreach($plan['updates'] as $change){self::assertSame(['body'],array_keys($change['before']));self::assertSame(['body'],array_keys($change['after']));}
        $result=$service->apply($this->file('preview'),$this->file('backup'));
        self::assertSame('applied',$result['status']);self::assertSame(2,$result['updated']);self::assertSame(0,$result['inserted']);
        self::assertSame(File::get($this->file('preview')),File::get($this->file('backup')));
        $expected=$before;
        foreach($expected['text_blocks'] as &$row){foreach(Patch::TARGETS as $target){if($row['id']===$target['id']){
            $row['body']=$this->next['targets'][$target['owner_index']]['after']['page']['blocks'][$target['source_index']]['body'];
        }}}unset($row);
        self::assertSame($expected,$this->snapshot(),'Every other row/column/timestamp remains byte-equivalent.');
        $after=$service->savePlan($this->file('after'));self::assertSame('after',$after['state']);self::assertSame([],$after['updates']);
        self::assertSame($plan['protected'],$after['protected']);
        foreach(['preview','after'] as $name){$r=$service->apply($this->file($name),$this->file('unused-'.$name));
            self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$r);self::assertFileDoesNotExist($this->file('unused-'.$name));
            self::assertSame($expected,$this->snapshot());}
    }
    public function test_one_repaired_row_with_one_old_row_and_partial_note_payload_are_rejected():void
    {
        $target=Patch::TARGETS[0];$old=$this->previous['targets'][4]['after']['page']['blocks'][5]['body'];
        $new=$this->next['targets'][4]['after']['page']['blocks'][5]['body'];
        DB::table('text_blocks')->where('id',$target['id'])->update(['body'=>$new]);$state=$this->snapshot();
        $this->refused(fn()=>$this->service()->plan());self::assertSame($state,$this->snapshot());
        $partial=json_decode($new,true,flags:JSON_THROW_ON_ERROR);unset($partial['items'][1]['note']);
        DB::table('text_blocks')->where('id',$target['id'])->update(['body'=>json_encode($partial,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)]);
        $state=$this->snapshot();$this->refused(fn()=>$this->service()->plan());self::assertSame($state,$this->snapshot());
        DB::table('text_blocks')->where('id',$target['id'])->update(['body'=>$old]);
    }
    public function test_all_exact_target_metadata_and_timestamps_fail_closed_when_changed():void
    {
        $original=(array)DB::table('text_blocks')->where('id',1313)->sole();
        foreach(['uuid'=>'unknown','seeder'=>'ForeignOwner','page_id'=>999,'page_category_id'=>999,'locale'=>'en','sort_order'=>9,
            'type'=>'box','column'=>'right','heading'=>'Redesign','css_class'=>'foreign','level'=>'A2','updated_at'=>'2026-02-01 00:00:00'] as $field=>$value){
            DB::table('text_blocks')->where('id',1313)->update([$field=>$value]);$state=$this->snapshot();
            $this->refused(fn()=>$this->service()->plan());self::assertSame($state,$this->snapshot(),$field);
            DB::table('text_blocks')->where('id',1313)->update([$field=>$original[$field]]);
        }
    }
    public function test_old_questions_options_foreign_text_saved_membership_and_extra_progress_tables_are_protected():void
    {
        $foreign=DB::table('text_blocks')->where('locale','pl')->orderBy('id')->value('id');
        foreach([['questions',850001,'question'],['question_options',850001,'option'],['question_answers',850001,'marker'],
            ['saved_grammar_test_questions',850001,'position'],['text_blocks',$foreign,'body'],
            ['m85_note_progress',1,'payload'],['m85_note_course_membership',1,'payload']] as [$table,$id,$field]){
            $old=DB::table($table)->where('id',$id)->value($field);DB::table($table)->where('id',$id)->update([$field=>$field==='position'?9:'Foreign drift']);
            $state=$this->snapshot();$this->refused(fn()=>$this->service()->plan());self::assertSame($state,$this->snapshot(),$table);
            DB::table($table)->where('id',$id)->update([$field=>$old]);
        }
    }
    public function test_failure_after_second_update_rolls_back_both_rows_but_retains_exact_exclusive_backup():void
    {
        $service=$this->service();$plan=$service->savePlan($this->file('preview'));$old=$this->snapshot();$service->failAtUpdate=1;
        $this->refused(fn()=>$service->apply($this->file('preview'),$this->file('rollback')));self::assertSame($old,$this->snapshot());
        self::assertSame($plan,json_decode(File::get($this->file('rollback')),true,flags:JSON_THROW_ON_ERROR));
        $service->failAtUpdate=null;self::assertSame($plan,$service->plan());$this->refused(fn()=>$service->restore($this->file('rollback')));
        self::assertSame($old,$this->snapshot());
    }
    public function test_rehashed_forged_update_and_stale_source_are_refused_before_any_new_backup():void
    {
        $service=$this->service();$plan=$service->savePlan($this->file('preview'));$old=$this->snapshot();
        $plan['updates'][0]['after']['body'].=' Forged';$plan['updates'][0]['after_sha256']=Patch::digest($plan['updates'][0]['after']);
        unset($plan['sha256']);$plan['sha256']=Patch::digest($plan);File::put($this->file('forged'),json_encode($plan,JSON_THROW_ON_ERROR));
        $this->refused(fn()=>$service->apply($this->file('forged'),$this->file('unused-forged')));self::assertFileDoesNotExist($this->file('unused-forged'));
        self::assertSame($old,$this->snapshot());$path=$this->root.'/'.$this->next['targets'][0]['path'];File::put($path,File::get($path)."\n");
        $this->refused(fn()=>$service->apply($this->file('preview'),$this->file('unused-stale')));self::assertFileDoesNotExist($this->file('unused-stale'));
        self::assertSame($old,$this->snapshot());
    }
    public function test_occupied_evidence_and_tampered_immutable_predecessor_are_not_overwritten_or_bypassed():void
    {
        $service=$this->service();$plan=$service->savePlan($this->file('preview'));$old=$this->snapshot();
        $this->refused(fn()=>$service->savePlan($this->file('preview')));self::assertSame($plan,json_decode(File::get($this->file('preview')),true,flags:JSON_THROW_ON_ERROR));
        File::put($this->file('occupied'),'Keep caller evidence.');$this->refused(fn()=>$service->apply($this->file('preview'),$this->file('occupied')));
        self::assertSame('Keep caller evidence.',File::get($this->file('occupied')));self::assertSame($old,$this->snapshot());
        $path=$this->root.'/'.Patch::PREVIOUS_SOURCE;File::put($path,File::get($path)."\n");$this->refused(fn()=>$service->plan());self::assertSame($old,$this->snapshot());
    }
    public function test_proof_route_template_is_finite_get_domain_bound_and_never_installs_itself():void
    {
        $nonce=str_repeat('a',32);$hunk=Patch::proofRouteHunk($nonce);
        self::assertSame(1,substr_count($hunk,"Route::get('/_local/m85-target-".$nonce));
        self::assertStringContainsString("})->domain('gramlyze.loc');",$hunk);
        self::assertStringContainsString("SET TRANSACTION READ ONLY",$hunk);self::assertStringContainsString('$db->rollBack();',$hunk);
        self::assertStringNotContainsString('password',$hunk);self::assertStringNotContainsString('APP_KEY',$hunk);
        $this->refused(fn()=>Patch::proofRouteHunk('bad'));
        $writer=File::get($this->source.'/app/Services/M85MistakeNotesPatch.php');
        self::assertStringNotContainsString('file_put_contents',$writer);self::assertStringNotContainsString('Route::post',$writer);
    }
}

class M85SyntheticMistakeNotes extends Patch
{
    public array $baselineSql=[];
    public ?int $failAtUpdate=null;
    protected function baselineEvidence():array
    {
        IsolatedTestEnvironment::assertSafeDatabase($this->db);
        if($this->baselineSql===[]){throw new RuntimeException('Synthetic baseline not supplied.');}
        return ['sql_after'=>$this->baselineSql];
    }
    protected function afterUpdate(int $index):void
    {
        if($this->failAtUpdate===$index){throw new RuntimeException('Injected synthetic second-update failure.');}
    }
}
