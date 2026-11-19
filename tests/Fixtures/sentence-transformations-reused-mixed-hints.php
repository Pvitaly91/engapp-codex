<?php
declare(strict_types=1);
// Finite public educational payloads only. No Laravel/.env/DB/HTTP/browser profile.
require dirname(__DIR__,2).'/vendor/autoload.php';
use App\Support\SentenceTransformationsReusedMixedHints as Hints;
use App\Support\SentenceReorderQuestionFactory as Reorder;
use App\Support\WordOrderPracticeQuality as WordOrder;
$before=[];
foreach(['standard','builder'] as $kind)foreach(WordOrder::compile('transformations',$kind)['questions'] as $row) {
    $answers=array_map(static fn($marker)=>$marker['answer'],$row['markers']);
    $before[]=WordOrder::decorate(['uuid'=>$row['uuid'],'question'=>$row['question'],'type'=>(string)$row['type'],
        'level'=>$row['level'],'answer_map'=>$answers,'answers'=>array_values($answers),'markers'=>array_keys($answers),
        'verb_hint'=>$row['verb_hint'],'verb_hints'=>array_fill_keys(array_keys($answers),$row['verb_hint']),
        'options'=>$row['options']??$row['markers']['a1']['options'],
        'options_by_marker'=>$row['options_by_marker']??array_map(static fn($marker)=>$marker['options']??[],$row['markers'])]);
}
$before=Reorder::addToMixedTheoryTest($before,['theory_page_mixed_type_profile'=>WordOrder::REVISION,
    'theory_page_mixed_interleave_question_types'=>true,'__meta'=>['theory_page_mixed_polyglot_test'=>true]]);
$after=array_map(static fn($row)=>Hints::project($row,'uk'),$before);
echo json_encode(['before'=>$before,'after'=>$after],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
