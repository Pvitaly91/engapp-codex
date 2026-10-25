<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Support\M26DetailPackage as Digest;
use App\Support\M51QuestionSupplement;
use App\Support\M91BuilderSelectionPresentation as Selection;
use App\Support\M91FutureSimpleCompletionContinuous as Package;
use App\Support\TheoryBuilderSelectionPresentation;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * All thirty public audit samples plus invented unsaved hydration fixtures.
 * Recomputed fixture hashes are NOT captured SQL snapshot or browser acceptance.
 * The unchanged M89 regression suite remains separate and is not replaced.
 */
class M91VerbHintBoundaryTest extends TestCase
{
    private const SCHEMA='complete-loaded-verb-hints-v1';
    private const HINT_KEYS=['id','question_id','option_id','marker','locale','created_at','updated_at'];
    private const OPTION_KEYS=['id','option','created_at','updated_at'];
    private ?ConnectionResolverInterface $priorResolver=null;
    private M91ForbiddenConnectionResolver $forbidden;

    protected function setUp(): void
    {
        parent::setUp();$this->priorResolver=Model::getConnectionResolver();
        $this->forbidden=new M91ForbiddenConnectionResolver;Model::setConnectionResolver($this->forbidden);
    }
    protected function tearDown(): void
    {
        try { self::assertSame(0,$this->forbidden->calls,'No database resolver attempt is allowed.'); }
        finally {
            if($this->priorResolver===null){Model::unsetConnectionResolver();}else{Model::setConnectionResolver($this->priorResolver);}
            parent::tearDown();
        }
    }
    private function audit(): array { return Selection::loadAudit(dirname(__DIR__,2)); }
    private function model(string $class,array $attributes): Model
    {
        $m=new $class;$m->setRawAttributes($attributes,true);return $m;
    }
    private function change(Model $model,string $key,mixed $value,bool $remove=false): void
    {
        $a=$model->getAttributes();if($remove){unset($a[$key]);}else{$a[$key]=$value;}$model->setRawAttributes($a,true);
    }
    private function matchSnapshot(Collection $questions,array $target): bool
    {
        return (new ReflectionMethod(TheoryBuilderSelectionPresentation::class,'matchesQuestions'))->invoke(null,$questions,$target);
    }
    private function hintRows(Question $q): array
    {
        $out=[];
        foreach($q->getRelation('verbHints') as $h){
            $a=$o=[];foreach(self::HINT_KEYS as $k){$a[$k]=$h->getAttributes()[$k];}
            foreach(self::OPTION_KEYS as $k){$o[$k]=$h->getRelation('option')->getAttributes()[$k];}
            $out[]=['attributes'=>$a,'option'=>$o];
        }
        return $out;
    }
    private function newHint(Question $q,int $owner,int $index): VerbHint
    {
        $option=$this->model(QuestionOption::class,['id'=>9090000+$owner*100+$index,'option'=>'синтетична підказка',
            'created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00']);
        $hint=$this->model(VerbHint::class,['id'=>9080000+$owner*100+$index,'question_id'=>$q->getAttributes()['id'],
            'option_id'=>$option->getAttributes()['id'],'marker'=>'a1','locale'=>'uk',
            'created_at'=>'2000-02-01 00:00:00','updated_at'=>'2000-02-01 00:00:00']);
        $hint->setRelation('option',$option);return $hint;
    }

    /** Keep thirty teaching identities; every other raw field is invented. Synthetic owners0–3 deliberately add complete nonempty hint relations although ALL actual M91 selected samples are captured empty. */
    private function hydrate(array $audit,int $owner): array
    {
        $target=$audit['targets'][$owner];$questions=[];
        foreach($target['first_five'] as $index=>&$sample){
            $qid=$sample['id'];$base=9010000+$owner*10000+$index*100;
            $attrs=['id'=>$qid,'uuid'=>$sample['uuid'],'question'=>'Синтетична умова для структурного тесту.',
                'difficulty'=>1,'level'=>Package::FOCUS_LEVELS[$owner],'category_id'=>9000001,'source_id'=>9000002,
                'flag'=>0,'type'=>'4','options_by_marker'=>null,'theory_text_block_uuid'=>null,
                'seeder'=>'M91InventedSnapshotFixture','created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00'];
            $q=$this->model(Question::class,$attrs);$options=$optionRows=$pivots=[];
            foreach($sample['token_bank_with_multiplicity'] as $n=>$token){
                $a=['id'=>$base+$n,'option'=>$token,'created_at'=>'1999-01-01 00:00:00','updated_at'=>'1999-01-01 00:00:00'];
                $o=$this->model(QuestionOption::class,$a);$p=['question_id'=>$qid,'option_id'=>$a['id'],'flag'=>null];
                $o->setRelation('pivot',$this->model(Pivot::class,$p));$options[]=$o;$optionRows[]=['id'=>$a['id'],'option'=>$token];$pivots[]=$p;
            }
            $answers=$answerRows=$used=[];
            foreach($sample['correct_tokens'] as $n=>$token){
                $found=null;foreach($options as $k=>$o){if(!isset($used[$k])&&$o->getAttributes()['option']===$token){$found=$k;break;}}
                self::assertNotNull($found,'Every correct token must exist with native bank multiplicity.');$used[$found]=true;$o=$options[$found];
                $a=['id'=>$base+70+$n,'question_id'=>$qid,'option_id'=>$o->getAttributes()['id'],'marker'=>'a'.($n+1),'position'=>null];
                $answer=$this->model(QuestionAnswer::class,$a);$answer->setRelation('option',$o);$answers[]=$answer;
                $answerRows[]=['id'=>$a['id'],'marker'=>$a['marker'],'position'=>null,'option_id'=>$a['option_id'],'option'=>$token];
            }
            $q->setRelation('options',new Collection($options));$q->setRelation('answers',new Collection($answers));$q->setRelation('hints',new Collection);
            $q->setRelation('verbHints',new Collection($owner<4?[$this->newHint($q,$owner,$index)]:[]));
            $sample['question_attribute_keys']=M51QuestionSupplement::ROW_COLUMNS['questions'];
            $ordered=[];foreach($sample['question_attribute_keys'] as $key){$ordered[$key]=$attrs[$key];}
            $sample['snapshot_hashes']=array_map(Digest::digest(...),[
                'question_snapshot'=>$ordered,'ordered_answer_snapshot'=>$answerRows,'original_options_snapshot'=>$optionRows,
                'loaded_option_pivots'=>$pivots,'hints_snapshot'=>[],'verb_hints_snapshot'=>$this->hintRows($q)]);
            $questions[]=$q;
        }
        unset($sample);return [new Collection($questions),$target];
    }

    private function rawState(Collection $questions): array
    {
        return $questions->map(static function (Question $question): array {
            $relations = [];
            foreach ($question->getRelations() as $name => $rows) {
                $relations[$name] = ['collection_id' => is_object($rows) ? spl_object_id($rows) : null, 'rows' => []];
                if (!$rows instanceof Collection) { continue; }
                foreach ($rows as $row) {
                    $children = [];
                    if ($row instanceof Model) {
                        foreach ($row->getRelations() as $key => $child) {
                            $children[$key] = ['id' => is_object($child) ? spl_object_id($child) : null,
                                'attributes' => $child instanceof Model ? $child->getAttributes() : null];
                        }
                    }
                    $relations[$name]['rows'][] = ['id' => is_object($row) ? spl_object_id($row) : null,
                        'attributes' => $row instanceof Model ? $row->getAttributes() : null,
                        'exists' => $row instanceof Model ? $row->exists : null, 'relations' => $children];
                }
            }
            return ['attributes' => $question->getAttributes(), 'original' => $question->getRawOriginal(),
                'exists' => $question->exists, 'relations' => $relations];
        })->all();
    }

    public function test_all_thirty_actual_samples_opt_in_and_preserve_captured_empty_hint_digests(): void
    {
        $audit=$this->audit();$nonempty=$empty=0;
        foreach($audit['targets'] as $owner=>$target){
            self::assertSame(Package::OWNERS[$owner],$target['identity']);self::assertNull($target['level']);
            self::assertCount(5,$target['first_five']);self::assertCount(72,$target['expected_pool_ids_by_bank'][Package::BANKS[$owner]]);
            foreach($target['first_five'] as $sample){
                self::assertSame(self::SCHEMA,$sample['verb_hint_snapshot_schema']);
                self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D',$sample['snapshot_hashes']['verb_hints_snapshot']);
                self::assertSame(Digest::digest([]),$sample['snapshot_hashes']['verb_hints_snapshot']);++$empty;
            }
        }
        self::assertSame([0,30],[$nonempty,$empty]);
    }

    public function test_all_thirty_teaching_samples_accept_complete_invented_hydration_without_original_audit_mutation(): void
    {
        $audit=$this->audit();$before=$audit;$count=0;
        foreach(range(0,5) as $owner){[$q,$t]=$this->hydrate($audit,$owner);$raw=$this->rawState($q);
            self::assertTrue($this->matchSnapshot($q,$t));self::assertSame($raw,$this->rawState($q));$count+=$q->count();
            foreach($q as $question){self::assertFalse($question->exists);self::assertSame([],$question->getDirty());}
        }
        self::assertSame(30,$count);self::assertSame($before,$audit);
    }

    public function test_missing_or_unexpected_hints_refuse_every_one_of_the_thirty_samples_without_repair(): void
    {
        $audit=$this->audit();$count=0;
        foreach(range(0,5) as $owner){foreach(range(0,4) as $index){
            [$q,$t]=$this->hydrate($audit,$owner);$q[$index]->setRelation('verbHints',new Collection($owner<4?[]:[$this->newHint($q[$index],$owner,$index)]));
            $raw=$this->rawState($q);self::assertFalse($this->matchSnapshot($q,$t));self::assertSame($raw,$this->rawState($q));++$count;
        }}
        self::assertSame(30,$count);
    }

    public function test_each_nonempty_hint_binds_all_seven_attributes_and_all_four_separate_option_attributes(): void
    {
        $audit=$this->audit();$count=0;
        foreach(range(0,3) as $owner){foreach(range(0,4) as $index){
            foreach([...self::HINT_KEYS,'unknown_null'] as $key){
                [$q,$t]=$this->hydrate($audit,$owner);$h=$q[$index]->getRelation('verbHints')[0];
                $this->change($h,$key,null,$key!=='unknown_null');self::assertFalse($this->matchSnapshot($q,$t),$key);++$count;
            }
            foreach([...self::OPTION_KEYS,'unknown_null'] as $key){
                [$q,$t]=$this->hydrate($audit,$owner);$o=$q[$index]->getRelation('verbHints')[0]->getRelation('option');
                $this->change($o,$key,null,$key!=='unknown_null');self::assertFalse($this->matchSnapshot($q,$t),$key);++$count;
            }
        }}
        self::assertSame(260,$count);
    }

    public function test_schema_omission_unloaded_relation_wrong_owner_and_ukrainian_option_never_become_an_english_bank_token(): void
    {
        $audit=$this->audit();
        foreach(range(0,5) as $owner){
            foreach(['schema','unloaded','unknown-schema','question-order','bank-drift'] as $case){
                [$q,$t]=$this->hydrate($audit,$owner);
                if($case==='schema'){unset($t['first_five'][0]['verb_hint_snapshot_schema']);}
                elseif($case==='unloaded'){$q[0]->unsetRelation('verbHints');}
                elseif($case==='unknown-schema'){$t['first_five'][0]['verb_hint_snapshot_schema']='other-v1';}
                elseif($case==='question-order'){$q=$q->reverse()->values();}
                else{$this->change($q[0]->getRelation('options')[0],'option','синтетична підказка');}
                $expected=$case==='schema'&&$owner>=4;
                self::assertSame($expected,$this->matchSnapshot($q,$t),$owner.':'.$case);
            }
            if($owner<4){
                [$q,$t]=$this->hydrate($audit,$owner);$hint=$q[0]->getRelation('verbHints')[0];$hintOption=$hint->getRelation('option');
                self::assertNotContains($hintOption->id,$q[0]->getRelation('options')->pluck('id')->all());
                self::assertTrue($this->matchSnapshot($q,$t),'Separate complete flag1-style UA option remains outside native English options.');
                $this->change($hint,'question_id',$hint->question_id+1);
                $t['first_five'][0]['snapshot_hashes']['verb_hints_snapshot']=Digest::digest($this->hintRows($q[0]));
                self::assertFalse($this->matchSnapshot($q,$t),'Recomputed hash cannot grant a foreign owner.');
            }
        }
    }
}

final class M91ForbiddenConnectionResolver implements ConnectionResolverInterface
{
    public int $calls=0;
    public function connection($name=null){++$this->calls;throw new RuntimeException('M91 forbidden database connection.');}
    public function getDefaultConnection(){return 'forbidden';}
    public function setDefaultConnection($name){throw new RuntimeException('M91 forbidden connection mutation.');}
}
