<?php
declare(strict_types=1);

// File-only fixture export for the actual card/step option normalizers.
require dirname(__DIR__,2).'/vendor/autoload.php';
$source=json_decode(file_get_contents(dirname(__DIR__,2).'/'.App\Support\FutureSimplePassivePracticeQuality::COMPOSE),true,flags:JSON_THROW_ON_ERROR);
$payloads=[];
foreach($source['questions'] as $row){
    $answers=array_map(static fn($marker)=>$marker['answer'],$row['markers']);
    $payloads[]=App\Support\FutureSimplePassivePracticeQuality::decorate([
        'id'=>$row['id'],'uuid'=>$row['uuid'],'question'=>$row['question'],'type'=>4,'level'=>$row['level'],
        'answer_map'=>$answers,'answers'=>array_values($answers),'answer'=>reset($answers),
        'markers'=>array_keys($answers),'markers_count'=>count($answers),'options'=>array_values($answers),
        'options_by_marker'=>array_fill(0,count($answers),array_slice(array_values($answers),0,3)),
    ]);
}
echo json_encode($payloads,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
