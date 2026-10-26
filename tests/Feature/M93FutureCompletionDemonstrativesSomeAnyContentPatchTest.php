<?php

namespace Tests\Feature;

use App\Services\M93ContentPatch as Patch;
use App\Support\M26DetailPackage as Digest;
use App\Support\M93FutureCompletionDemonstrativesSomeAny as Package;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Synthetic SQLite proves body-only writer boundaries; never substitutes for working-local SQL acceptance. */
class M93FutureCompletionDemonstrativesSomeAnyContentPatchTest extends TestCase
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
        self::assertSame([70,71,130,264,263,265],Package::PAGE_IDS);
        self::assertSame([14,14,20,50,50,50],Package::CATEGORY_IDS);
        self::assertSame([7,7,6,8,6,6],Package::CONTINUING_COUNTS);
        self::assertSame([null,null,'A1',null,'A2','A2'],Package::LEVELS);
        $this->root=storage_path('app/m93-native-fixture-'.bin2hex(random_bytes(7)));$this->private=$this->root.'/evidence';
        IsolatedTestEnvironment::assertOwnedPath(storage_path('app'));File::makeDirectory($this->private,0700,true);
        foreach (Patch::sourcePaths($this->patch,$this->contract,$source) as $path) {
            File::makeDirectory(dirname($this->root.'/'.$path),0700,true,true);File::copy($source.'/'.$path,$this->root.'/'.$path);
        }
        foreach ([[1,'foreign',null],[12,'maibutni-formy',null],[14,'future-perfect-continuous',12],[20,'zaimennyky-ta-vkazivni-slova',null],[50,'some-any',null]] as [$id,$slug,$parent]) {
            DB::table('page_categories')->insert(['id'=>$id,'parent_id'=>$parent,'slug'=>$slug,'title'=>$slug,'type'=>'theory','language'=>'uk']);
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
            $poolSize=Patch::FULL_MAIN_POOL_COUNTS[$i];
            for ($n=0;$n<$poolSize;++$n) {
                $id=8000000+$i*100+$n;$uuid='m93-synthetic-'.$id;
                DB::table('questions')->insert(['id'=>$id,'uuid'=>$uuid,'question'=>'Ізольований fixture, не live question.',
                    'difficulty'=>2,'level'=>['A1','A2','B1','B2','C1','C2'][$n%6],'type'=>'4','seeder'=>$this->contract['targets'][$i]['bank_seeders'][0],
                    'created_at'=>$time,'updated_at'=>$time]);
                DB::table('question_theory_text_blocks')->insert(['question_uuid'=>$uuid,'text_block_uuid'=>$hero,'position'=>0,
                    'created_at'=>$time,'updated_at'=>$time]);
            }
            $foreign=(array)DB::table('text_blocks')->where('page_id',Package::PAGE_IDS[$i])->where('sort_order',1)->sole();unset($foreign['id']);
            foreach (['en','pl'] as $locale) { $foreign['uuid']='m93-foreign-'.$i.'-'.$locale;$foreign['locale']=$locale;$foreign['body']='Unchanged '.$locale;DB::table('text_blocks')->insert($foreign); }
        }
        DB::connection()->getSchemaBuilder()->create('m93_protected_progress',static function(Blueprint $table):void { $table->id();$table->text('value'); });
        DB::table('m93_protected_progress')->insert(['value'=>'Unchanged synthetic progress.']);
        // Synthetic final-drill/enrollment rows are protected families, not newly authored questions.
        foreach (['m93_protected_course_lessons','m93_protected_course_enrollment'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table,static function(Blueprint $schema):void {
                $schema->id();$schema->unsignedInteger('course_id');$schema->text('payload');
            });
        }
        foreach ([0=>1] as $ownerIndex=>$courseId) {
            $uuid='m93-synthetic-'.(8000000+$ownerIndex*100);
            $questionId=8000000+$ownerIndex*100;
            DB::table('question_options')->insert(['id'=>$courseId,'option'=>'Unchanged synthetic token '.$courseId]);
            DB::table('question_option_question')->insert(['question_id'=>$questionId,'option_id'=>$courseId,'flag'=>null]);
            DB::table('question_answers')->insert(['question_id'=>$questionId,'option_id'=>$courseId,'marker'=>'a1',
                'created_at'=>$time,'updated_at'=>$time]);
            DB::table('m93_protected_course_lessons')->insert(['course_id'=>$courseId,
                'payload'=>json_encode(['lesson'=>16,'theory_page_id'=>Package::PAGE_IDS[$ownerIndex],
                    'final_drill'=>[$uuid],'count'=>1,'passing_score'=>1],JSON_THROW_ON_ERROR)]);
            DB::table('m93_protected_course_enrollment')->insert(['course_id'=>$courseId,
                'payload'=>json_encode(['user'=>100,'completed_lessons'=>[1,2],'final_count'=>1,'score'=>0],JSON_THROW_ON_ERROR)]);
            DB::table('saved_grammar_tests')->insert(['id'=>$courseId,'uuid'=>'m93-saved-'.$courseId,
                'name'=>'Unchanged final-drill fixture','slug'=>'m93-saved-'.$courseId]);
            DB::table('saved_grammar_test_questions')->insert(['saved_grammar_test_id'=>$courseId,'question_uuid'=>$uuid,
                'position'=>0,'created_at'=>$time,'updated_at'=>$time]);
        }
    }
    private function service():M93SyntheticNativePatch { return new M93SyntheticNativePatch(DB::connection(),$this->root.'/database',$this->private); }
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
    private function refused(callable $call,string $case=''):void
    {
        $error=null;try { $call(); } catch(RuntimeException $caught) { $error=$caught; }self::assertInstanceOf(RuntimeException::class,$error,$case);
    }
    private function expectedUpdateRows():int
    {
        $count=0;
        foreach ($this->before['targets'] as $i=>$target) {
            $old=$target['before']['page'];$new=$this->patch['targets'][$i]['after']['page'];
            $count+=(int)($old['subtitle_text']!==$new['subtitle_text']);
            $count+=(int)($old['subtitle_html']!==$new['subtitle_html']);
            foreach ($old['blocks'] as $slot=>$block) {
                $oldFields=$newFields=[];
                foreach (['body'] as $field) {
                    $oldFields[$field]=$block[$field]??null;$newFields[$field]=$new['blocks'][$slot][$field]??null;
                }
                $count+=(int)($oldFields!==$newFields);
            }
        }
        return $count;
    }
    public function test_one_existing_and_five_new_practices_are_atomic_with_only_exact_body_updates():void
    {
        $service=$this->service();$before=$this->snapshot();$plan=$service->savePlan($this->file('preview'));
        self::assertSame($before,$this->snapshot());self::assertSame('before',$plan['state']);
        $expectedUpdates=$this->expectedUpdateRows();
        self::assertSame(40,array_sum(Package::CONTINUING_COUNTS));
        self::assertCount(46,array_filter($before['text_blocks'],static fn($row)=>$row['locale']==='uk'));
        self::assertGreaterThan(0,$expectedUpdates);
        self::assertCount($expectedUpdates,$plan['updates']);self::assertCount(5,$plan['inserts']);self::assertArrayNotHasKey('deletes',$plan);
        foreach ($plan['updates'] as $update) {
            if($update['table']==='text_blocks'){self::assertSame(['body'],array_keys($update['after']));}
            else{self::fail('M93 must not update Page text or any other table.');}
        }
        foreach($plan['inserts'] as $insert){self::assertSame('text_blocks',$insert['table']);self::assertContains($insert['seeder'],array_intersect_key(Package::OWNERS,array_flip([0,1,2,4,5])));}
        $result=$service->apply($this->file('preview'),$this->file('backup'));
        self::assertSame('applied',$result['status']);self::assertSame(5,$result['inserted']);self::assertSame($expectedUpdates,$result['updated']);
        self::assertSame($plan,json_decode(File::get($this->file('backup')),true,flags:JSON_THROW_ON_ERROR));
        $after=$this->snapshot();self::assertSame(count($before['text_blocks'])+5,count($after['text_blocks']));
        foreach ($before['text_blocks'] as $row) {
            $now=(array)DB::table('text_blocks')->where('id',$row['id'])->sole();
            if ($row['locale']==='uk') {
                $i=array_search($row['page_id'],Package::PAGE_IDS,true);$new=$this->patch['targets'][$i]['after']['page'];
                $expected=$row['sort_order']===0?['body'=>$new['subtitle_html'],'type'=>'subtitle','heading'=>null]:$new['blocks'][$row['sort_order']-1];
                foreach (['body'] as $field) { self::assertSame($expected[$field]??null,$now[$field]);unset($row[$field],$now[$field]); }
            }
            self::assertSame($row,$now,'Old IDs/UUIDs/SQL levels/order/columns/timestamps and EN/PL are exact.');
        }
        foreach ($before['pages'] as $row) {
            $now=(array)DB::table('pages')->where('id',$row['id'])->sole();
            $i=array_search($row['id'],Package::PAGE_IDS,true);
            self::assertSame($this->patch['targets'][$i]['after']['page']['subtitle_text'],$now['text']);
            self::assertSame($row['text'],$now['text'],'All six Page text values remain exact.');
            unset($row['text'],$now['text']);self::assertSame($row,$now,'All Page fields/timestamps remain exact; M93 has no Page update authority.');
        }
        foreach ($before as $table=>$rows) { if (!in_array($table,['pages','text_blocks'],true)) { self::assertSame($rows,$after[$table],$table); } }
        foreach (Package::PAGE_IDS as $i=>$id) {
            $practice=DB::table('text_blocks')->where('page_id',$id)->where('locale','uk')->where('type','practice-set')->sole();
            self::assertSame(Package::LEVELS[$i],$practice->level);self::assertSame(Package::PRACTICE_SOURCE_INDICES[$i]+1,$practice->sort_order);
            $source=$this->patch['targets'][$i]['after']['page']['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]];
            self::assertSame(Digest::uuid(Package::OWNERS[$i],$source,Package::PRACTICE_SOURCE_INDICES[$i]+1),$practice->uuid);
            if($i===3){self::assertSame(Package::EXISTING_PRACTICE_UUIDS[3],$practice->uuid);}
        }
        $repeat=$service->savePlan($this->file('repeat'));self::assertSame('after',$repeat['state']);self::assertSame([],$repeat['updates']);self::assertSame([],$repeat['inserts']);
        foreach (['preview','repeat'] as $name) { self::assertSame('no-op',$service->apply($this->file($name),$this->file('unused-'.$name))['status']);self::assertFileDoesNotExist($this->file('unused-'.$name));self::assertSame($after,$this->snapshot()); }
    }
    public function test_first_and_last_update_or_insert_failures_roll_back_all_six_owners():void
    {
        foreach (['first-update','last-update','first-insert','last-insert'] as $stage) {
            $s=$this->service();$p=$s->savePlan($this->file('plan-'.$stage));$before=$this->snapshot();
            self::assertCount(5,$p['inserts']);
            if(str_ends_with($stage,'insert')){$s->failInsert=str_starts_with($stage,'first')?0:count($p['inserts'])-1;}
            else{$s->failUpdate=str_starts_with($stage,'first')?0:count($p['updates'])-1;}
            $this->refused(fn()=>$s->apply($this->file('plan-'.$stage),$this->file('backup-'.$stage)));self::assertSame($before,$this->snapshot());
            self::assertSame($p,$s->plan());self::assertFileExists($this->file('backup-'.$stage));
        }
    }
    public function test_foreign_owner_category_locale_metadata_range_and_bank_fail_without_writes():void
    {
        $s=$this->service();
        foreach ([['pages',265,['seeder'=>'Foreign\\Owner','page_category_id'=>1,'slug'=>'foreign']],
            ['text_blocks',DB::table('text_blocks')->where('page_id',130)->where('sort_order',2)->value('id'),
                ['level'=>'B1-B2','locale'=>'en','column'=>'right','heading'=>'Unknown','type'=>'box']],
            ['questions',8000000,['seeder'=>'Foreign\\Bank','type'=>'1']]] as [$table,$id,$changes]) {
            $old=(array)DB::table($table)->where('id',$id)->sole();
            foreach ($changes as $field=>$value) {
                DB::table($table)->where('id',$id)->update([$field=>$value]);$before=$this->snapshot();$this->refused(fn()=>$s->plan(),$table.'.'.$field);
                self::assertSame($before,$this->snapshot());DB::table($table)->where('id',$id)->update([$field=>$old[$field]]);
            }
        }
        // Technical NULL deliberately does not invent a CEFR filter. Once reviewed,
        // an original question-level drift is protected exactly like every old row.
        $s->savePlan($this->file('question-level-before'));
        $oldLevel=DB::table('questions')->where('id',8000000)->value('level');
        DB::table('questions')->where('id',8000000)->update(['level'=>'A1-B1']);$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('question-level-before'),$this->file('no-question-level')),'Reviewed original question CEFR changed.');
        self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-question-level'));
        DB::table('questions')->where('id',8000000)->update(['level'=>$oldLevel]);
        DB::table('page_categories')->where('id',14)->update(['slug'=>'foreign-category']);$before=$this->snapshot();
        $this->refused(fn()=>$s->plan());self::assertSame($before,$this->snapshot());
        DB::table('page_categories')->where('id',14)->update(['slug'=>'future-perfect-continuous']);
    }
    public function test_stale_body_protected_progress_plan_and_backup_collision_are_fail_closed():void
    {
        $s=$this->service();$s->savePlan($this->file('preview'));
        $id=DB::table('text_blocks')->where('page_id',70)->where('sort_order',1)->value('id');$body=DB::table('text_blocks')->where('id',$id)->value('body');
        DB::table('text_blocks')->where('id',$id)->update(['body'=>$body.' stale']);$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('no-body')));self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-body'));
        DB::table('text_blocks')->where('id',$id)->update(['body'=>$body]);
        DB::table('m93_protected_progress')->where('id',1)->update(['value'=>'Later synthetic progress']);$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('no-progress')));self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-progress'));
        DB::table('m93_protected_progress')->where('id',1)->update(['value'=>'Unchanged synthetic progress.']);
        foreach (['m93_protected_course_lessons','m93_protected_course_enrollment','saved_grammar_test_questions','question_answers'] as $table) {
            $row=(array)DB::table($table)->orderBy('id')->first();$id=$row['id'];
            $field=array_key_exists('payload',$row)?'payload':($table==='question_answers'?'marker':'position');
            DB::table($table)->where('id',$id)->update([$field=>$field==='position'?9:'Later unrelated state']);
            $before=$this->snapshot();$basename='no-'.str_replace('_','-',$table);
            $this->refused(fn()=>$s->apply($this->file('preview'),$this->file($basename)));
            self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file($basename));
            DB::table($table)->where('id',$id)->update([$field=>$row[$field]]);
        }
        File::put($this->file('occupied'),'unrelated backup');$before=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview'),$this->file('occupied')));self::assertSame($before,$this->snapshot());self::assertSame('unrelated backup',File::get($this->file('occupied')));
        $p=json_decode(File::get($this->file('preview')),true,flags:JSON_THROW_ON_ERROR);$p['updates'][0]['after']['body']='Tampered';File::put($this->file('tampered'),json_encode($p));
        $this->refused(fn()=>$s->apply($this->file('tampered'),$this->file('no-tampered')));self::assertSame($before,$this->snapshot());
    }
    public function test_cli_has_no_ambient_production_or_backup_only_authority():void
    {
        self::assertArrayHasKey('content:patch-future-completion-demonstratives-some-any-m93', \Illuminate\Support\Facades\Artisan::all(),
            'An unregistered CLI command must not make every negative case pass for the wrong reason.');
        $before=$this->snapshot();
        foreach ([[],['--local-target'=>'gramlyze.ub','--local-proof'=>'local-proof-'.str_repeat('a',32).'.json'],
            ['--local-target'=>'gramlyze.loc','--local-proof'=>'../proof.json'],
            ['--local-target'=>'gramlyze.loc','--local-proof'=>'local-proof-'.str_repeat('a',32).'.json','--backup'=>'without-apply.json']] as $args) {
            $this->artisan('content:patch-future-completion-demonstratives-some-any-m93',$args)->assertExitCode(1);self::assertSame($before,$this->snapshot());
        }
    }
    public function test_page_text_and_partial_owner_or_partial_row_states_are_rejected_before_backup():void
    {
        $s=$this->service();$plan=$s->savePlan($this->file('preview-page-policy'));
        $pageUpdates=array_values(array_filter($plan['updates'],static fn($row)=>$row['table']==='pages'));
        self::assertSame([],$pageUpdates);$allowedPages=[];
        self::assertSame(['text_blocks'=>['body'],'pages'=>$allowedPages],$s->allowedFieldPolicy());
        self::assertSame([],$s->allowedFieldPolicy()['pages']);
        DB::table('pages')->where('id',71)->update(['text'=>'Unapproved Page.text']);$now=$this->snapshot();
        $this->refused(fn()=>$s->apply($this->file('preview-page-policy'),$this->file('no-page-text')));
        self::assertSame($now,$this->snapshot());self::assertFileDoesNotExist($this->file('no-page-text'));
        DB::table('pages')->where('id',71)->update(['text'=>$this->before['targets'][1]['before']['page']['subtitle_text']]);
        // One exact AFTER row among BEFORE rows is still an unapproved partial owner.
        $change=array_values(array_filter($plan['updates'],static fn($r)=>$r['seeder']===Package::OWNERS[0]&&$r['table']==='text_blocks'))[0];
        DB::table('text_blocks')->where('id',$change['id'])->update($change['after']);$now=$this->snapshot();
        $this->refused(fn()=>$s->plan());self::assertSame($now,$this->snapshot());
        DB::table('text_blocks')->where('id',$change['id'])->update($change['before']);
        // The complete first owner is not permission to apply only part of the six-owner release.
        foreach($plan['updates'] as $change){if($change['seeder']===Package::OWNERS[0]){DB::table($change['table'])->where('id',$change['id'])->update($change['after']);}}
        $now=$this->snapshot();$this->refused(fn()=>$s->plan());self::assertSame($now,$this->snapshot());
    }
    public function test_full_72_main_pools_and_exact_variable_12_or_72_filters_ignore_no_foreign_bank_rows():void
    {
        $s=$this->service();$plan=$s->plan();$now=$this->snapshot();
        foreach(Package::OWNERS as $i=>$owner){
            $baseline=$plan['pages'][$owner];$questions=$baseline['relations']['questions'];
            self::assertCount(72,$questions);self::assertSame(12,count(array_filter($questions,static fn($q)=>$q['level']===Package::FOCUS_LEVELS[$i])));
            self::assertSame([72,72,12,72,12,12],Patch::ELIGIBLE_POOL_COUNTS);
            self::assertCount(Patch::ELIGIBLE_POOL_COUNTS[$i],array_filter($questions,static fn($q)=>Package::LEVELS[$i]===null||$q['level']===Package::LEVELS[$i]));
            $baseline['bank_inventory'][Package::BANKS[$i]]=['eligible_count'=>12,'eligible_ids_uuid_order'=>array_slice(array_column($questions,'id'),0,12)];
            $s->inspectPools($questions,$this->contract['targets'][$i],$baseline);
            $s->inspectPools($questions,$this->contract['targets'][$i],null);
            foreach(['missing','text','level','duplicate'] as $kind){
                $changed=$questions;
                if($kind==='missing'){array_pop($changed);}
                elseif($kind==='text'){$changed[71]['question'].=' changed';}
                elseif($kind==='level'){$changed[71]['level']='A1-B1';}
                else{$changed[71]['id']=$changed[0]['id'];}
                $this->refused(fn()=>$s->inspectPools($changed,$this->contract['targets'][$i],$baseline),$owner.':'.$kind);
            }
            $wrong=$this->contract['targets'][$i];$wrong['bank_seeders']=[Package::BANKS[($i+1)%6]];
            $this->refused(fn()=>$s->inspectPools($questions,$wrong,$baseline));
        }
        self::assertSame($now,$this->snapshot(),'Pool inspection is read-only even for every rejected candidate.');
    }
    public function test_only_places_practice_uuid_belongs_to_exact_owner_without_invented_uuid_key_or_restore_authority():void
    {
        $s=$this->service();
        foreach([3] as $i){
            $uuid=Package::EXISTING_PRACTICE_UUIDS[$i];$row=(array)DB::table('text_blocks')->where('uuid',$uuid)->sole();
            self::assertSame(Package::PAGE_IDS[$i],$row['page_id']);self::assertSame(Package::OWNERS[$i],$row['seeder']);
            DB::table('text_blocks')->where('id',$row['id'])->update(['seeder'=>Package::OWNERS[($i+1)%6]]);$now=$this->snapshot();
            $this->refused(fn()=>$s->plan(),'The exact continuing practice UUID cannot migrate to the other owner.');self::assertSame($now,$this->snapshot());
            DB::table('text_blocks')->where('id',$row['id'])->update(['seeder'=>$row['seeder']]);
        }
        $now=$this->snapshot();$this->refused(fn()=>$s->restore($this->file('not-authorized')));self::assertSame($now,$this->snapshot());
    }
    public function test_complete_original_twenty_one_bank_profiles_including_foreign_ai_are_bound_before_database_access(): void
    {
        self::assertSame('94874d0ff6075434e851bafe2ccf2bb2af5483a618dbad71e799973d0d8d1edf',Patch::BANK_SOURCE_CAPTURE_SHA);
        self::assertCount(42,Patch::BANK_SOURCE_PROFILES);
        self::assertCount(21,array_filter(Patch::BANK_SOURCE_PROFILES,static fn($p)=>$p['kind']==='definition'));
        self::assertCount(21,array_filter(Patch::BANK_SOURCE_PROFILES,static fn($p)=>$p['kind']==='wrapper'));
        $sourceCounts=array_values(array_unique(array_column(array_values(array_filter(Patch::BANK_SOURCE_PROFILES,static fn($p)=>$p['kind']==='definition')),'question_count')));
        sort($sourceCounts,SORT_NUMERIC);self::assertSame([24,72,108,144],$sourceCounts);
        $sources=[];
        foreach(Patch::BANK_SOURCE_PROFILES as $path=>$profile){
            foreach(['root'=>\App\Services\M93LocalTargetGuard::ROOT,'worktree'=>Patch::SOURCE_WORKTREE] as $context=>$root){
                self::assertFileExists($root.'/'.$path);self::assertFalse(is_link($root.'/'.$path));
                $sources[$path][$context]=file_get_contents($root.'/'.$path);
            }
        }
        Patch::assertOriginalBankSourceBytes($sources);
        $untouched=$sources;$first=array_key_first($sources);
        foreach(['missing','raw-eol','json-token','profile-swap'] as $case){
            $bad=$sources;
            if($case==='missing'){unset($bad[$first]);}
            elseif($case==='raw-eol'){$raw=$bad[$first]['root'];$bad[$first]['root']=str_contains($raw,"\r\n")?str_replace("\r\n","\n",$raw):str_replace("\n","\r\n",$raw);}
            elseif($case==='json-token'){$bad[$first]['root'].=" ";}
            else{$keys=array_keys($sources);$bad[$first]['root']=$sources[$keys[2]]['root'];}
            $this->refused(fn()=>Patch::assertOriginalBankSourceBytes($bad),$case);
        }
        self::assertSame($untouched,$sources,'Immutable bank sources are never repaired or rewritten.');
    }

    public function test_tampered_plan_cannot_insert_an_extra_practice_or_update_a_question_family(): void
    {
        $s=$this->service();$original=$s->savePlan($this->file('no-new-row-plan'));$before=$this->snapshot();
        foreach (['practice-insert','question-write'] as $case) {
            $p=$original;
            if ($case==='practice-insert') {
                $p['inserts'][]=['table'=>'text_blocks','seeder'=>Package::OWNERS[0],
                    'fields'=>['uuid'=>'m93-unapproved-append','type'=>'practice-set','page_id'=>Package::PAGE_IDS[0]]];
            } else {
                $p['updates'][]=['table'=>'questions','id'=>8000000,'seeder'=>Package::OWNERS[0],
                    'before'=>['question'=>'Synthetic'],'after'=>['question'=>'Unapproved question rewrite']];
            }
            File::put($this->file($case),json_encode($p,JSON_THROW_ON_ERROR));
            $this->refused(fn()=>$s->apply($this->file($case),$this->file('no-'.$case)));
            self::assertSame($before,$this->snapshot());self::assertFileDoesNotExist($this->file('no-'.$case));
        }
    }

}

class M93SyntheticNativePatch extends Patch
{
    public ?int $failUpdate=null;
    public ?int $failInsert=null;
    protected function afterUpdate(int $index):void { if ($this->failUpdate===$index) { throw new RuntimeException('Injected isolated update failure.'); } }
    protected function afterInsert(int $index):void { if ($this->failInsert===$index) { throw new RuntimeException('Injected isolated insert failure.'); } }
    public function allowedFieldPolicy():array{return ['text_blocks'=>$this->allowedUpdateFields(),'pages'=>$this->allowedPageUpdateFields()];}
    public function inspectPools(array $questions,array $contract,?array $baseline):void{$this->assertExistingPools($questions,$contract,$baseline);}
}
