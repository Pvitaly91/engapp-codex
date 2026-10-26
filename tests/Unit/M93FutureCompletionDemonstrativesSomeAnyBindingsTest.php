<?php

namespace Tests\Unit;

use App\Support\M93FutureCompletionDemonstrativesSomeAny as Package;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** Pure actual mixed metadata and one-retained/five-appended binding contract; no application/SQL/HTTP. */
class M93FutureCompletionDemonstrativesSomeAnyBindingsTest extends TestCase
{
    private const EXISTING = '9fcd6c4f-a0e3-5e1b-95a3-64ce4ede412c';
    private function bindings(): array
    {
        $out=[];
        foreach(Package::OWNERS as$i=>$owner){
            $counts=$i===3?['selects'=>3,'choices'=>2,'inputs'=>5]:['selects'=>2,'choices'=>2,'inputs'=>2];
            $out[]=['identity'=>$owner,'slug'=>Package::SLUGS[$i],'page_id'=>Package::PAGE_IDS[$i],
                'category_id'=>Package::CATEGORY_IDS[$i],'ancestry'=>Package::ANCESTRIES[$i],
                'local_url'=>'http://gramlyze.loc/theory/'.implode('/',Package::ANCESTRIES[$i]).'/'.Package::SLUGS[$i],
                'practice_level'=>Package::LEVELS[$i],'display_level'=>Package::DISPLAY_LEVELS[$i],
                'rendered_title'=>Package::RENDERED_TITLES[$i],'continuing_levels'=>Package::CONTINUING_LEVELS[$i],
                'appended_blocks'=>$i===3?[]:[['key'=>Package::PRACTICE_KEYS[$i],'type'=>'practice-set','column'=>'left','level'=>Package::LEVELS[$i]]],
                'practice_contract'=>['counts'=>$counts,'sentence_input_indices'=>Package::SENTENCE_INPUT_INDICES[$i]]];
            if($i===3){$out[$i]['existing_practice']=['source_index'=>6,'uuid'=>self::EXISTING,'level'=>null];}
        }
        return$out;
    }
    private function refused(array$b,string$why):void
    {
        $error=null;try{Package::assertBindings($b);}catch(RuntimeException$e){$error=$e;}
        self::assertInstanceOf(RuntimeException::class,$error,$why);self::assertStringStartsWith('M93 ',$error->getMessage());
    }
    public function test_six_mixed_technical_levels_are_not_editorial_focus_or_a_global_null_default():void
    {
        self::assertSame('6d3003884fb8d15e870285c1ac0875995ff84fc8',Package::BASE_SHA);
        self::assertSame([70,71,130,264,263,265],Package::PAGE_IDS);
        self::assertSame([14,14,20,50,50,50],Package::CATEGORY_IDS);
        self::assertSame([7,7,6,8,6,6],Package::CONTINUING_COUNTS);
        self::assertSame([7,7,6,6,6,6],Package::PRACTICE_SOURCE_INDICES);
        self::assertSame([6,6,5,7,5,5],Package::NAVIGATION_SOURCE_INDICES);
        self::assertSame([null,null,'A1',null,'A2','A2'],Package::LEVELS);
        self::assertSame(['B2','B2','A1','A2','A2','A2'],Package::FOCUS_LEVELS);
        self::assertSame(['B2','B2','A1','A2','A2','A2'],Package::DISPLAY_LEVELS);
        self::assertSame([['maibutni-formy','future-perfect-continuous'],['maibutni-formy','future-perfect-continuous'],
            ['zaimennyky-ta-vkazivni-slova'],['some-any'],['some-any'],['some-any']],Package::ANCESTRIES);
        self::assertSame([null,null,null,null,null,null,null],Package::CONTINUING_LEVELS[0]);
        self::assertSame(['A1','A1','A1','A1','A1',null],Package::CONTINUING_LEVELS[2]);
        self::assertSame(['A2','A2','A2','A2','A2',null],Package::CONTINUING_LEVELS[4]);
        $b=$this->bindings();Package::assertBindings($b);$p=Package::draftProfile($b);
        self::assertSame($b,$p['bindings']);self::assertSame('m93_native_v1',$p['marker']);self::assertArrayNotHasKey('artifacts',$p);
    }
    public function test_foreign_m75_owner_wrong_articles_ancestry_missing_reordered_or_duplicated_owner_is_refused():void
    {
        foreach(range(0,5)as$i){foreach(['owner','page','category','url','ancestry']as$case){
            $b=$this->bindings();
            if($case==='owner'){$b[$i]['identity']='Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesIndefinitePronounsTheorySeeder';}
            elseif($case==='page'){$b[$i]['page_id']=Package::PAGE_IDS[($i+1)%6];}
            elseif($case==='category'){$b[$i]['category_id']=1;}
            elseif($case==='url'){$b[$i]['local_url']='http://gramlyze.loc/theory/articles/some-any/'.Package::SLUGS[$i];}
            else{$b[$i]['ancestry']=['articles','some-any'];}
            $this->refused($b,$i.':'.$case);
        }}
        $b=$this->bindings();array_pop($b);$this->refused($b,'missing');
        $b=$this->bindings();[$b[0],$b[1]]=[$b[1],$b[0]];$this->refused($b,'reorder');
        $b=$this->bindings();$b[5]=$b[0];$this->refused($b,'duplicate');
    }
    public function test_only_places_retains_its_actual_uuid_source_six_and_never_receives_a_duplicate_append():void
    {
        self::assertSame([null,null,null,self::EXISTING,null,null],Package::EXISTING_PRACTICE_UUIDS);
        foreach(['missing','uuid','source','duplicate','invent-key']as$case){
            $b=$this->bindings();
            if($case==='missing'){unset($b[3]['existing_practice']);}
            elseif($case==='uuid'){$b[3]['existing_practice']['uuid']='72c49e46-5882-5ef6-b040-12e4956d31e1';}
            elseif($case==='source'){$b[3]['existing_practice']['source_index']=7;}
            elseif($case==='duplicate'){$b[3]['appended_blocks']=[['key'=>'extra','type'=>'practice-set','column'=>'left','level'=>null]];}
            else{$b[3]['existing_practice']['uuid_key']='invented-old-key';}
            $this->refused($b,$case);
        }
    }
    public function test_five_new_practices_have_exact_owner_key_column_and_explicit_level_without_old_uuid():void
    {
        foreach([0,1,2,4,5]as$i){foreach(['missing','duplicate','key','column','level','invented-existing']as$case){
            $b=$this->bindings();
            if($case==='missing'){$b[$i]['appended_blocks']=[];}
            elseif($case==='duplicate'){$b[$i]['appended_blocks'][]=$b[$i]['appended_blocks'][0];}
            elseif($case==='key'){$b[$i]['appended_blocks'][0]['key']='foreign';}
            elseif($case==='column'){$b[$i]['appended_blocks'][0]['column']='right';}
            elseif($case==='level'){$b[$i]['appended_blocks'][0]['level']=Package::LEVELS[$i]===null?'A1':null;}
            else{$b[$i]['existing_practice']=['source_index'=>6,'uuid'=>self::EXISTING,'level'=>null];}
            $this->refused($b,$i.':'.$case);
        }}
    }
    public function test_variable_partial_full_input_positions_counts_and_footer_null_levels_cannot_be_normalized():void
    {
        self::assertSame([[0,1],[0,1],[0,1],[3,4],[0,1],[0,1]],Package::SENTENCE_INPUT_INDICES);
        foreach(range(0,5)as$i){foreach(['level','missing-level','display','full-indices','continuing','counts']as$case){
            $b=$this->bindings();
            if($case==='level'){$b[$i]['practice_level']=Package::LEVELS[$i]===null?Package::FOCUS_LEVELS[$i]:null;}
            elseif($case==='missing-level'){unset($b[$i]['practice_level']);}
            elseif($case==='display'){$b[$i]['display_level']='A1–C2';}
            elseif($case==='full-indices'){$b[$i]['practice_contract']['sentence_input_indices']=[0,1,2];}
            elseif($case==='continuing'){$b[$i]['continuing_levels'][count($b[$i]['continuing_levels'])-1]='A1';}
            else{$b[$i]['practice_contract']['counts']['inputs']=$i===3?2:5;}
            $this->refused($b,$i.':'.$case);
        }}
    }
}
