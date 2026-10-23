<?php

namespace Tests\Feature;

use App\Services\M79PracticeCriteriaPatch as Writer;
use App\Support\M79PracticeCriteriaRevision as Revision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

require_once dirname(__DIR__,2).'/app/Support/M79PracticeCriteriaRevision.php';
require_once dirname(__DIR__,2).'/app/Services/M79PracticeCriteriaPatch.php';

/** New finite correction tests; all SQL is invented SQLite :memory:, never working content. */
class M79PracticeCriteriaRevisionTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private string $private;
    private string $evidenceSha;
    private array $evidence;
    private array $source;

    protected function setUp(): void
    {
        parent::setUp(); IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame(':memory:',DB::connection()->getDatabaseName());
        self::assertSame('array',config('session.driver')); self::assertSame('array',config('cache.default'));
        self::assertNotSame(realpath(base_path()),realpath(app()->environmentPath()));
        $root=dirname(__DIR__,2); $this->source=Revision::load($root);
        $this->rebuildComposeTestSchema(); $rows=[]; $time='2001-02-03 04:05:06';
        foreach ($this->source['transitions'] as $n=>$t) {
            DB::table('pages')->insert(['id'=>$t['page_id'],'page_category_id'=>22,'slug'=>'synthetic-criteria-'.$n,
                'title'=>'Synthetic criteria '.$n,'text'=>'Unchanged invented subtitle.','type'=>'theory','seeder'=>$t['identity'],
                'created_at'=>$time,'updated_at'=>$time]);
            DB::table('text_blocks')->insert(['id'=>$t['id'],'uuid'=>$t['uuid'],'page_id'=>$t['page_id'],'page_category_id'=>22,
                'locale'=>'uk','type'=>'practice-set','column'=>'left','heading'=>null,'css_class'=>null,'sort_order'=>12,
                'level'=>$t['level'],'seeder'=>$t['identity'],'body'=>$t['before'],'created_at'=>$time,'updated_at'=>$time]);
            $rows[]=(array)DB::table('text_blocks')->where('id',$t['id'])->sole();
        }
        $schema=[]; $tables=DB::connection()->getSchemaBuilder()->getTableListing(schema:'main',schemaQualified:false); sort($tables);
        foreach ($tables as $table) { $schema[$table]=DB::connection()->getSchemaBuilder()->getColumns($table); }
        $this->private=storage_path('app/m79-criteria-synthetic-'.bin2hex(random_bytes(8)));
        File::makeDirectory($this->private,0700,true);
        File::put($this->private.'/synthetic-evidence.json',json_encode(['invented_test_only'=>true],JSON_THROW_ON_ERROR));
        $this->evidenceSha=hash_file('sha256',$this->private.'/synthetic-evidence.json');
        $this->evidence=['transitions'=>$this->source['transitions'],'baseline'=>['rows'=>$rows,'schema'=>$schema,'sql_before'=>Writer::fingerprints(DB::connection())],
            'source_pins'=>['private:synthetic-evidence.json'=>$this->evidenceSha]];
    }

    private function writer(bool $fail=false): M79SyntheticCriteriaWriter
    {
        return new M79SyntheticCriteriaWriter(DB::connection(),$this->private.'/database',$this->private,$this->evidence,$fail);
    }

    private function refused(callable $call,string $message): void
    {
        $caught=null; try { $call(); } catch(RuntimeException $e) { $caught=$e; }
        self::assertInstanceOf(RuntimeException::class,$caught); self::assertStringContainsString($message,$caught->getMessage());
    }

    private function materials(): array
    {
        $root=dirname(__DIR__,2); $read=static fn($p)=>json_decode(file_get_contents($root.'/'.$p),true,flags:JSON_THROW_ON_ERROR);
        return [$read(Revision::OLD_PATCH),$read(Revision::PATCH),$read(Revision::OLD_MASTER),$read(Revision::MASTER),$read(Revision::LEDGER)];
    }

    public function test_all_six_native_select_criteria_are_literal_visible_label_prefixes(): void
    {
        [$old,$next,$oldMaster,$master,$ledger]=$this->materials();
        self::assertCount(2,Revision::exactTransition($old,$next,$oldMaster,$master,$ledger));
        self::assertCount(6,$ledger['labels']);
        foreach ($ledger['labels'] as $row) {
            $task=$master['lessons'][$row['lesson_index']]['blocks'][11]['data']['selects'][$row['group_index']];
            self::assertSame($row['prompt'].' '.$row['before_label'],$task['label']);
            self::assertSame($row['prompt'],$task['prompt']);
            self::assertStringStartsWith($task['prompt'],$task['label']);
            self::assertSame(1,substr_count($task['label'],$task['prompt']));
            self::assertSame($oldMaster['lessons'][$row['lesson_index']]['blocks'][11]['data']['selects'][$row['group_index']]['answer'],$task['answer']);
        }
        self::assertStringContainsString('тренер просить назвати одну з двох здібностей',$master['lessons'][0]['blocks'][11]['data']['selects'][2]['label']);
        self::assertStringContainsString('тут потрібне скорочення',$master['lessons'][2]['blocks'][11]['data']['selects'][1]['label']);
        foreach ($ledger['predecessor_pins'] as $path=>$sha) { self::assertSame($sha,hash_file('sha256',dirname(__DIR__,2).'/'.$path)); }
    }

    public function test_exact_transition_rejects_any_extra_learning_or_identity_mutation(): void
    {
        [$old,$next,$oldMaster,$master,$ledger]=$this->materials();
        foreach (['answer','accepted','prompt','owner','other_lesson','version','extra_label','ledger'] as $mutation) {
            $p=$next; $m=$master; $l=$ledger;
            if ($mutation==='version') { $m['version']='1.0.2'; }
            elseif ($mutation==='owner') { $p['targets'][0]['page_binding']['id']=999; }
            elseif ($mutation==='other_lesson') { $m['lessons'][4]['blocks'][0]['data']['intro']='Unauthorized.'; }
            elseif ($mutation==='extra_label') { $m['lessons'][0]['blocks'][11]['data']['selects'][0]['label'].=' Extra.'; }
            elseif ($mutation==='ledger') { $l['labels'][0]['after_label']='Hidden criterion'; }
            else {
                $body=json_decode($p['targets'][0]['after']['page']['blocks'][11]['body'],true,flags:JSON_THROW_ON_ERROR);
                $body['selects'][0][$mutation]=$mutation==='accepted'?['a']:'changed';
                $p['targets'][0]['after']['page']['blocks'][11]['body']=json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
            }
            $caught=null; try { Revision::exactTransition($old,$p,$oldMaster,$m,$l); } catch(RuntimeException $e) { $caught=$e; }
            self::assertInstanceOf(RuntimeException::class,$caught,$mutation);
        }
    }

    public function test_bank_audit_keeps_all_selected_questions_and_only_rebinds_two_body_hashes(): void
    {
        $root=dirname(__DIR__,2); $old=Revision::pinned($root.'/'.Revision::OLD_AUDIT,Revision::OLD_AUDIT_SHA);
        $next=Revision::pinned($root.'/'.Revision::AUDIT,Revision::AUDIT_SHA); $ledger=$this->source['ledger'];
        Revision::exactAuditTransition($old,$next,$this->source['transitions'],$ledger);
        self::assertSame($old['targets'],$next['targets']); self::assertCount(6,$next['practice_bindings']);
        $next['targets'][0]['page_id']=999;
        $this->refused(fn()=>Revision::exactAuditTransition($old,$next,$this->source['transitions'],$ledger),'changes beyond');
    }

    public function test_real_m26_primitive_applies_two_bodies_then_repeats_noop_without_timestamp_or_protected_changes(): void
    {
        $writer=$this->writer(); $plan=$writer->savePlan($this->private.'/preview.json');
        self::assertSame('before',$plan['state']); self::assertCount(2,$plan['updates']); self::assertSame([],$plan['inserts']);
        $result=$writer->apply($this->private.'/preview.json',$this->private.'/backup.json');
        self::assertSame(['applied',2,0],[$result['status'],$result['updated'],$result['inserted']]);
        self::assertFileExists($this->private.'/backup.json');
        foreach ($this->source['transitions'] as $n=>$t) {
            $row=(array)DB::table('text_blocks')->where('id',$t['id'])->sole();
            self::assertSame($t['after'],$row['body']);
            $row['body']=$t['before']; self::assertSame($this->evidence['baseline']['rows'][$n],$row);
        }
        self::assertSame($this->evidence['baseline']['sql_before'],Writer::fingerprints(DB::connection(),$this->source['transitions']));
        self::assertSame(['status'=>'no-op','updated'=>0,'inserted'=>0],$writer->apply($this->private.'/preview.json',$this->private.'/unused.json'));
        self::assertFileDoesNotExist($this->private.'/unused.json');
    }

    public function test_partial_successor_and_target_metadata_drift_are_refused_before_writes(): void
    {
        $t=$this->source['transitions'][0]; DB::table('text_blocks')->where('id',$t['id'])->update(['body'=>$t['after']]);
        $this->refused(fn()=>$this->writer()->plan(),'partial package');
        DB::table('text_blocks')->where('id',$t['id'])->update(['body'=>$t['before'],'locale'=>'en']);
        $this->refused(fn()=>$this->writer()->plan(),'metadata/timestamps');
        self::assertSame($this->source['transitions'][1]['before'],DB::table('text_blocks')->where('id',4372)->value('body'));
    }

    public function test_stale_protected_page_or_private_evidence_prevents_apply(): void
    {
        $writer=$this->writer(); $writer->savePlan($this->private.'/preview.json');
        DB::table('pages')->where('id',132)->update(['text'=>'Unrelated changed subtitle.']);
        $this->refused(fn()=>$writer->apply($this->private.'/preview.json',$this->private.'/backup.json'),'all-table');
        self::assertFileDoesNotExist($this->private.'/backup.json');
        DB::table('pages')->where('id',132)->update(['text'=>'Unchanged invented subtitle.']);
        File::append($this->private.'/synthetic-evidence.json',' ');
        $this->refused(fn()=>$writer->apply($this->private.'/preview.json',$this->private.'/backup.json'),'source/evidence drift');
        self::assertFileDoesNotExist($this->private.'/backup.json');
    }

    public function test_transaction_rolls_back_both_bodies_on_injected_failure_after_first_update(): void
    {
        $writer=$this->writer(true); $writer->savePlan($this->private.'/preview.json');
        $this->refused(fn()=>$writer->apply($this->private.'/preview.json',$this->private.'/backup.json'),'Synthetic injected failure');
        self::assertFileExists($this->private.'/backup.json');
        self::assertSame($this->evidence['baseline']['sql_before'],Writer::fingerprints(DB::connection()));
    }

    public function test_existing_exclusive_backup_is_never_overwritten_and_restore_is_out_of_scope(): void
    {
        $writer=$this->writer(); $writer->savePlan($this->private.'/preview.json'); File::put($this->private.'/backup.json','keep');
        $this->refused(fn()=>$writer->apply($this->private.'/preview.json',$this->private.'/backup.json'),'exclusive evidence already exists');
        self::assertSame('keep',file_get_contents($this->private.'/backup.json'));
        self::assertSame($this->evidence['baseline']['sql_before'],Writer::fingerprints(DB::connection()));
        $this->refused(fn()=>$writer->restore($this->private.'/backup.json'),'outside');
    }

    protected function tearDown(): void
    {
        if (isset($this->private)) { File::deleteDirectory($this->private); }
        parent::tearDown();
    }
}

class M79SyntheticCriteriaWriter extends Writer
{
    public function __construct($db,string $databasePath,string $privateDirectory,private array $synthetic,private bool $fail)
    {
        parent::__construct($db,$databasePath,$privateDirectory);
    }
    protected function evidence(): array { return $this->synthetic; }
    protected function afterUpdate(int $index): void
    {
        if ($this->fail&&$index===0) { throw new RuntimeException('Synthetic injected failure after first exact body update.'); }
    }
}
