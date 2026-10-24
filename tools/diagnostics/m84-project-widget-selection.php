<?php

/** Pinned pure selection from independent BEFORE and reviewed insert-only inventory; no SQL/HTTP. */
if(PHP_SAPI!=='cli'||count($argv)!==4||preg_match('/^question-inventory-v[1-9][0-9]*\.json$/D',$argv[1])!==1
    ||preg_match('/^[a-f0-9]{64}$/D',$argv[2])!==1||preg_match('/^widget-selection-v[1-9][0-9]*\.json$/D',$argv[3])!==1){throw new RuntimeException('M84 exact inventory pin and exclusive selection basename required.');}
$root='D:/DEV/htdocs/gramlyze.loc';$wt=$root.'/storage/app/m75-worktree';$dir=$root.'/storage/app/seo-m84-local';
require $root.'/vendor/autoload.php';
use App\Support\M84WordOrderHaveGot as Package;
use App\Support\M84QuestionSupplement as Supplement;
use App\Support\M51QuestionSupplement as Rows;
use App\Support\ComposeTokenCase;
$read=static function($file,$sha){if(!is_file($file)||is_link($file)||!hash_equals($sha,hash_file('sha256',$file))){throw new RuntimeException('M84 selection input drift.');}return json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);};
$beforeSha='babac2b3efa88b87fc8ea50b1ad45fb9feb8cadfdf766c33e76ca7b24aa19916';
$before=$read($dir.'/before-v1.json',$beforeSha);$inventory=$read($dir.'/'.$argv[1],$argv[2]);$source=Supplement::load($wt);
if($inventory['source_sha256']!==Supplement::SOURCE_SHA||$inventory['before_sha256']!==$beforeSha||$inventory['db_writes']!==0
    ||$inventory['sql_before']!==$before['sql_before']){throw new RuntimeException('M84 selection source/SQL lineage differs.');}
$projection=Supplement::project($source,Supplement::bindings(),$inventory['option_matches']);
$materialized=Rows::materialize($projection,$inventory['ids'],$inventory['row_timestamp']);
if($materialized!==$inventory['materialized']||$projection!==$inventory['projection']){throw new RuntimeException('M84 insert projection differs.');}
$options=array_column($materialized['question_options'],null,'id');
foreach($projection['reused_options'] as $r){$options[$r['id']]=$r['row'];}
$questions=array_column($materialized['questions'],null,'uuid');
$selection=['schema_version'=>1,'package'=>'m84-word-order-have-got-widget-selection','version'=>'1.0.0',
    'independent_before_sha256'=>$beforeSha,'question_inventory'=>['file'=>$argv[1],'sha256'=>$argv[2]],
    'supplement_sha256'=>Supplement::SOURCE_SHA,'scope'=>'30 selected / 25 minimal new / 5 reused; original banks/keys/options and saved membership untouched.',
    'review'=>'Independent agent and main editorial/SQL-label review, not human review or browser acceptance.',
    'targets'=>[]];
foreach(Package::OWNERS as $i=>$owner){$selected=[];
    if($i<5){foreach($source['banks'][$i]['questions'] as $draft){$q=$questions[$draft['source_uid']];
        $answers=array_values(array_filter($materialized['question_answers'],static fn($a)=>$a['question_id']===$q['id']));
        usort($answers,static fn($a,$b)=>strnatcmp($a['marker'],$b['marker']));
        $raw=array_map(static fn($a)=>$options[$a['option_id']]['option'],$answers);$tokens=ComposeTokenCase::normalize($raw);
        if($tokens!==$draft['tokens_correct']){throw new RuntimeException('M84 actual option labels no longer compose reviewed tokens.');}
        $pivots=array_values(array_filter($materialized['question_option_question'],static fn($p)=>$p['question_id']===$q['id']));
        $selected[]=['id'=>$q['id'],'uuid'=>$q['uuid'],'level'=>$q['level'],'source'=>$q['question'],
            'condition'=>Supplement::condition($draft),'translation'=>$draft['translation_uk'],'settled'=>$draft['canonical_en'],
            'raw_tokens'=>$raw,'tokens'=>$tokens,'original_options'=>array_map(static fn($p)=>$options[$p['option_id']]['option'],$pivots),
            'original_hints'=>[],'original_verb_hints'=>[],'coverage'=>implode('; ',$draft['learning_requirements'])];
    }}else{
        $conditions=[
            'У мене є маленький собака. Склади повне твердження про теперішню наявність за моделлю have got; почни з мовця й збережи всі факти.',
            'У неї є синя сумка. Склади повне твердження про теперішню наявність за моделлю have got; збережи особу, колір і річ.',
            'У нас немає машини. Склади повне заперечення про теперішню наявність за моделлю have got; у цьому тренуванні використай повну форму.',
            'У нього немає брата. Склади повне заперечення за моделлю have got; збережи родинний зв’язок і використай повну форму.',
            'Постав нейтральне повне питання за моделлю have got про теперішню наявність квитка в одного співрозмовника: у тебе є квиток?',
        ];
        $settled=['I have got a small dog.','She has got a blue bag.','We have not got a car.','He has not got a brother.','Have you got a ticket?'];
        for($n=0;$n<5;$n++){$uuid='bg-hg-poly-a1-'.str_pad((string)($n+1),2,'0',STR_PAD_LEFT);$s=$before['pages'][$owner]['relations']['question_snapshots'][$uuid];$q=$s['question'];
            if($q['seeder']!==Package::BANKS[$i]||(string)$q['type']!=='4'||$q['level']!=='A1'||$s['hints']!==[]||$s['verb_hints']!==[]){throw new RuntimeException('M84 five existing Have got families differ.');}
            $raw=array_column($s['ordered_answer_instances'],'option');
            $selected[]=['id'=>$q['id'],'uuid'=>$uuid,'level'=>'A1','source'=>$q['question'],'condition'=>$conditions[$n],
                'translation'=>$q['question'],'settled'=>$settled[$n],'raw_tokens'=>$raw,'tokens'=>ComposeTokenCase::normalize($raw),
                'original_options'=>array_column($s['options'],'option'),'original_hints'=>[],'original_verb_hints'=>[],
                'coverage'=>['first-person possession','third-person singular possession','plural negative possession','family relationship negative','have got yes-no question'][$n]];
        }
    }
    $selection['targets'][]=['identity'=>$owner,'bank'=>$i<5?Supplement::BANKS[$i]:Package::BANKS[$i],
        'actual_practice_level'=>null,'selected_levels'=>array_column($selected,'level'),'selected'=>$selected];
}
$file=$dir.'/'.$argv[3];if(file_exists($file)||is_link($file)){throw new RuntimeException('M84 selection output exists.');}
$bytes=json_encode($selection,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$h=fopen($file,'x');if(!$h){throw new RuntimeException('M84 selection output unavailable.');}try{if(fwrite($h,$bytes)!==strlen($bytes)||!fflush($h)){throw new RuntimeException('M84 selection output incomplete.');}}finally{fclose($h);}
echo json_encode(['file'=>$argv[3],'sha256'=>hash('sha256',$bytes),'selected'=>30,'new'=>25,'reused'=>5,'db_writes'=>0,'http_requests'=>0],JSON_THROW_ON_ERROR)."\n";
