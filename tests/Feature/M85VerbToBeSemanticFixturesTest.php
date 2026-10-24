<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\M85VerbToBe as Package;
use PHPUnit\Framework\TestCase;

/** Independent authored teaching literals; expectations are not copied from the AFTER projection at runtime. */
class M85VerbToBeSemanticFixturesTest extends TestCase
{
    private const EXPECTED=[
        [['is','play'],['a','b'],['I am in the small garden.','The red boxes are heavy.']],
        [['am not','are not'],['b','a'],['I am not at the station.','The children are not in the car.']],
        [['Are','a'],['Готовність Олени та її брата, від імені яких вона відповідає.','Людина хоче дізнатися, чи бібліотека відчинена; самого факту ще не встановлено.'],['No, I am not at home.','Who is ready?']],
        [['were','Was'],['Марта була зайнята вчора; її стан зараз цим реченням не визначено.','Про обох дітей у згаданому вчорашньому ранку.'],['Olena and I were at the museum yesterday.','The library was not open yesterday.']],
        [['will not be','will be'],['Оля очікує холоду завтра; прогноз може не справдитися.','А'],['We will not be at home on Sunday.','Where will the teachers be at noon?']],
        [['is','А'],['А','Ні: речення не встановлює повної відсутності; може бути, наприклад, один стілець.'],['There is some milk in the fridge.','Are there any clean plates in the cupboard?']],
    ];
    private function practices(): array
    {
        $m=Package::load(dirname(__DIR__,2))[2];
        return array_map(static fn($l,$i)=>$l['blocks'][Package::PRACTICE_SOURCE_INDICES[$i]]['data'],$m['lessons'],array_keys($m['lessons']));
    }
    private static function visible(array $task): string
    {
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags(str_replace('<br>',' ',$task['label']??$task['after']??'').' '.str_replace('<br>',' ',$task['prompt']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8')));
    }
    private function reviewed(array $task,string $answer,array $accepted,array $criteria,array $rejected): void
    {
        self::assertSame($answer,$task['answer']);self::assertSame($accepted,$task['accepted']);
        foreach($criteria as $text){self::assertStringContainsString(mb_strtolower($text),mb_strtolower(self::visible($task)),'Criterion must be visible before answering.');}
        foreach($rejected as $bad){
            foreach($accepted as $canonical){
                self::assertNotContains(Package::comparisonKey($bad),array_map(Package::comparisonKey(...),AcceptedAnswerVariants::for($canonical)),$bad);
            }
        }
    }
    public function test_all_36_canonical_answers_are_independent_literals_and_only_12_are_full_inputs(): void
    {
        $data=$this->practices();self::assertCount(6,$data);$count=$full=0;
        foreach(self::EXPECTED as $i=>$groups){foreach(['selects','choices','inputs'] as $g=>$group){
            self::assertCount(2,$data[$i][$group]);
            foreach($groups[$g] as $n=>$expected){
                $task=$data[$i][$group][$n];self::assertSame($expected,$task['answer']);self::assertContains($expected,$task['accepted']);
                self::assertNotSame('',self::visible($task));self::assertNotEmpty($task['feedback']);++$count;
                if($group==='inputs'){++$full;self::assertStringContainsString('/',$task['before']);self::assertArrayNotHasKey('hint',$task);self::assertArrayNotHasKey('placeholder',$task);}
            }
        }}self::assertSame([36,12],[$count,$full]);
    }
    public function test_present_noun_agreement_and_be_lexical_verb_boundary_have_explicit_intent(): void
    {
        $p=$this->practices()[0];
        $this->reviewed($p['selects'][0],'is',['is'],['одну людину','The new student'],['am','are']);
        $this->reviewed($p['selects'][1],'play',['play'],['просту модель звички','не дію зараз','on Sundays'],['are play','are playing']);
        $this->reviewed($p['inputs'][0],'I am in the small garden.',['I am in the small garden.',"I'm in the small garden."],['про себе','маленькому саду'],['I in the small garden.','You are in the small garden.']);
        self::assertSame(['a','b'],array_column($p['choices'],'answer'));
    }
    public function test_negatives_keep_not_same_participants_and_do_not_invent_opposite_state_or_place(): void
    {
        $p=$this->practices()[1];
        $this->reviewed($p['selects'][1],'are not',['are not',"aren't"],['кілька дверей','не відчинені'],['is not','do not']);
        $this->reviewed($p['inputs'][1],'The children are not in the car.',['The children are not in the car.',"The children aren't in the car."],['діти','не в машині'],['The children are in the car.','The child is not in the car.']);
        self::assertStringContainsString('не щаслива',mb_strtolower(self::visible($p['choices'][0])));
        self::assertStringContainsString('точного іншого стану',mb_strtolower(self::visible($p['choices'][0])));
        self::assertStringContainsString('не в школі',mb_strtolower(self::visible($p['choices'][1])));
        self::assertSame(['b','a'],array_column($p['choices'],'answer'));
    }
    public function test_questions_bind_actual_speakers_and_subject_who_and_never_terminal_positive_contraction(): void
    {
        $p=$this->practices()[2];
        $this->reviewed($p['selects'][0],'Are',['Are'],['двох друзів','you ready'],['Am','Is']);
        $this->reviewed($p['selects'][1],'a',['a'],['відповідає лише за себе','коротку ствердну','Yes, I am.','Yes, I’m.'],['b',"Yes, I'm."]);
        $this->reviewed($p['inputs'][0],'No, I am not at home.',['No, I am not at home.',"No, I'm not at home."],['Ти вдома','лише за себе','повну негативну'],['No, you are not at home.','Yes, I am at home.']);
        $this->reviewed($p['inputs'][1],'Who is ready?',['Who is ready?'],['who як підметом','не додавай he'],['Who is he ready?']);
        self::assertStringContainsString('самого факту ще не встановлено',$p['choices'][1]['answer']);
    }
    public function test_past_noun_agreement_keeps_time_group_and_not_open_without_a_present_inference(): void
    {
        $p=$this->practices()[3];
        $this->reviewed($p['selects'][0],'were',['were'],['два вікна','вчора'],['was','are']);
        $this->reviewed($p['selects'][1],'Was',['Was'],['Олен','вчора','вокзал'],['Did','Were']);
        $this->reviewed($p['inputs'][0],'Olena and I were at the museum yesterday.',['Olena and I were at the museum yesterday.'],['двох людей','yesterday'],['I was at the museum yesterday.']);
        $this->reviewed($p['inputs'][1],'The library was not open yesterday.',['The library was not open yesterday.',"The library wasn't open yesterday."],['запереч','сьогодні','open'],['The library is not open today.','The library was open yesterday.']);
        self::assertStringContainsString('зараз цим реченням не визначено',$p['choices'][0]['answer']);
    }
    public function test_future_keeps_be_not_subject_who_and_prediction_vs_guarantee_scope(): void
    {
        $p=$this->practices()[4];
        $this->reviewed($p['selects'][0],'will not be',['will not be'],['повну форму заперечення','діти'],['will be','will not are']);
        $this->reviewed($p['selects'][1],'will be',['will be'],['сам підмет','Who'],['will he be','will is']);
        $this->reviewed($p['choices'][1],'А',['А'],['одного Марка','самостійну позитивну коротку','Yes, I will.','Yes, I’ll.'],['Б',"Yes, I'll."]);
        $this->reviewed($p['inputs'][0],'We will not be at home on Sunday.',['We will not be at home on Sunday.',"We won't be at home on Sunday.","We'll not be at home on Sunday."],['Ми не будемо','у неділю'],['We be at home on Sunday.','We will be at home on Sunday.','We will not at home on Sunday.']);
        $this->reviewed($p['inputs'][1],'Where will the teachers be at noon?',['Where will the teachers be at noon?'],['питання про місце','the teachers'],['Where will be the teachers at noon?','Where the teachers will be at noon?']);
        self::assertStringContainsString('може не справдитися',$p['choices'][0]['answer']);
    }
    public function test_there_covers_uncountable_existence_known_reference_and_not_two_vs_none(): void
    {
        $p=$this->practices()[5];
        $this->reviewed($p['selects'][0],'is',['is'],['Milk','незлічуване'],['are','am']);
        $this->reviewed($p['selects'][1],'А',['А'],['бачить аптеку','самостійну позитивну коротку','Yes, there is.','Yes, there’s.'],['Б',"Yes, there's."]);
        $this->reviewed($p['choices'][0],'А',['А'],['вперше повідомити','There is a lamp','The lamp is','It is a lamp'],['Б','В']);
        self::assertStringContainsString('не встановлює повної відсутності',$p['choices'][1]['answer']);
        $this->reviewed($p['inputs'][0],'There is some milk in the fridge.',['There is some milk in the fridge.',"There's some milk in the fridge."],['молоко','холодильник'],['There are some milk in the fridge.','It is some milk in the fridge.']);
        $this->reviewed($p['inputs'][1],'Are there any clean plates in the cupboard?',['Are there any clean plates in the cupboard?'],['питання про наявність','стандартній письмовій','any'],['Are they any clean plates in the cupboard?','Is there any clean plates in the cupboard?']);
    }
}
