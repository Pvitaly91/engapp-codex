<?php
declare(strict_types=1);
// Actual finite payloads, file-only: no Laravel/.env/DB/network.
require dirname(__DIR__,2).'/vendor/autoload.php';
use App\Support\MixedRevisionPracticeQuality as Quality;
$all = [];
$onlyTopic = null;
$categoryLevel = null;
foreach (array_slice($argv,1) as $argument) {
    if (str_starts_with($argument,'--only-topic=')) $onlyTopic=substr($argument,13);
    if (str_starts_with($argument,'--category-level=')) $categoryLevel=substr($argument,17);
}
foreach (array_keys(Quality::TOPICS) as $topic) {
    if ($onlyTopic!==null && $topic!==$onlyTopic) continue;
    $payloads = []; $ids = array_fill_keys(array_merge(array_column(Quality::compile($topic,'standard')['questions'],'uuid'),array_column(Quality::compile($topic,'builder')['questions'],'uuid')),true);
    foreach (Quality::questions() as $row) {
        if (!isset($ids[$row['uuid']])) { continue; }
        $answers = array_map(static fn($m) => $m['answer'],$row['markers']);
        $payloads[] = Quality::decorate(['uuid'=>$row['uuid'],'question'=>$row['question'],'type'=>(string)$row['type'],'level'=>$row['level'],
            'answer_map'=>$answers,'answers'=>array_values($answers),'markers'=>array_keys($answers),'verb_hint'=>$row['verb_hint'],
            'verb_hints'=>array_map(static fn($m) => $m['verb_hint'],$row['markers']),'markers_count'=>count($answers),
            'options'=>$row['options'] ?? $row['markers']['a1']['options'],
            'options_by_marker'=>$row['options_by_marker'] ?? array_map(static fn($m) => $m['options'] ?? [],$row['markers'])]);
    }
    if (($argv[1] ?? '') === '--mixed') {
        $payloads = App\Support\SentenceReorderQuestionFactory::addToMixedTheoryTest($payloads,[
            'theory_page_mixed_type_profile'=>Quality::REVISION,'theory_page_mixed_interleave_question_types'=>true,
            '__meta'=>['theory_page_mixed_polyglot_test'=>true]]);
    }
    foreach($payloads as &$q)$q['_topic']=$topic;unset($q);
    array_push($all,...$payloads);
}
if ($categoryLevel!==null) {
    if (!in_array($categoryLevel,Quality::LEVELS,true)) throw new InvalidArgumentException('Known category level required.');
    $all=[];
    foreach (Quality::compositeMembers() as $member) {
        $catalog=$member['catalog'];$topic=$member['topic'];
        foreach (['standard'=>2,'builder'=>1] as $bank=>$quota) {
            $rows=array_values(array_filter($catalog::compile($topic,$bank)['questions'],static fn($q)=>$q['level']===$categoryLevel));
            foreach(array_slice($rows,0,$quota) as $row) {
                $answers=array_map(static fn($m)=>$m['answer'],$row['markers']);
                $all[]=Quality::restoreClarifiedHints($catalog::decorate(['uuid'=>$row['uuid'],'question'=>$row['question'],'type'=>(string)$row['type'],'level'=>$row['level'],
                    'answer_map'=>$answers,'answers'=>array_values($answers),'markers'=>array_keys($answers),'verb_hint'=>$row['verb_hint'],
                    'verb_hints'=>array_map(static fn($m)=>$m['verb_hint'],$row['markers']),'markers_count'=>count($answers),
                    'options'=>$row['options']??$row['markers']['a1']['options'],
                    'options_by_marker'=>$row['options_by_marker']??array_map(static fn($m)=>$m['options']??[],$row['markers']),
                    '_topic'=>$member['slug']]),'uk');
            }
        }
    }
    $all=App\Support\SentenceReorderQuestionFactory::addToMixedTheoryTest($all,[
        'theory_page_mixed_type_profile'=>Quality::REVISION,'theory_page_mixed_interleave_question_types'=>true,
        '__meta'=>['theory_page_mixed_polyglot_test'=>true]]);
}
echo json_encode($all,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);


