<?php

/** One finite M82 profile of the accepted snapshot/formatter audit; no SQL, HTTP or active writes. */
$file=__DIR__.'/m78-bank-audit-projection.php';
if(PHP_SAPI!=='cli'||is_link($file)||!is_file($file)
    ||!hash_equals('1df46fd529f9a43dfaf3108e9831a803f3fd2931459edf81c58c3841b9c384bc',hash_file('sha256',$file))){
    throw new RuntimeException('M82 immutable bank-audit primitive differs.');
}
$source=file_get_contents($file);
$selectionSha='4f907d2ca2877469e9c534ec6b199fece0418e64d5ee2a4f91f6729af0bc2fff';
$bindingsSha='a998208e041a2d231deff859cc5ec37e72800a7a6c79c61b64deef2b67ad4fc0';
$beforeSha='94312691251f96433ee72c3fe544db71767c795495874af3ba1c80c2cbb49c62';
foreach([$selectionSha,$bindingsSha,$beforeSha] as $sha){
    if(preg_match('/^[a-f0-9]{64}$/D',$sha)!==1||$sha===str_repeat('0',64)){
        throw new RuntimeException('M82 independent widget/binding review is not frozen.');
    }
}
$populationPrelude=<<<'PHP'
$privateSelected=[];
$selectedBankPagewideCount=0;$allPagewideType4Count=0;$allPagewideQuestionCount=0;
PHP;
$populationOwner=<<<'PHP'
    $snapshots=$page['relations']['question_snapshots']; $poolRows=[]; $poolByBank=[];
    $allPagewideQuestionCount+=count($page['relations']['questions']);
    $allPagewideType4Count+=count(array_filter($page['relations']['questions'],static fn($q)=>(string)$q['type']==='4'));
PHP;
$populationBank=<<<'PHP'
        if (count($pagewide)!==($i===5?24:72)) { throw new RuntimeException('M82 exact original selected-bank pagewide population differs.'); }
        $selectedBankPagewideCount+=count($pagewide);
PHP;
$populationEnd=<<<'PHP'
if([$selectedBankPagewideCount,$allPagewideType4Count,$allPagewideQuestionCount]!==[384,480,912]
    ||array_map(static fn($key)=>$audit['review_summary'][$key],['selected_bank_pagewide_type4_pool','all_pagewide_original_type4_pool','all_pagewide_original_question_pool'])!==[384,480,912]){
    throw new RuntimeException('M82 selected-bank versus all-page original population accounting differs.');
}

/** Replay actual unchanged native methods; old case-unsafe candidates are excluded, not repaired here. */
PHP;
$rules=[
    ['M78PhrasalQuestionBasics','M82CommonMistakesLevelRevision',2],
    ['M78','M82',46],
    ['m78-phrasal-question-basics','m82-common-mistakes-level-revision',2],
    ['m78-','m82-',2],
    ['327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422',$beforeSha,2],
    ['63cfcb4fccea1a3a8008d07cfcd323224ef0699b2c58e948d10d92a5ee44300a',$bindingsSha,1],
    ['4b009bc23f878b27d6feee2ca6a8c919f892fe21c0f93f1842afdb6f32512e30',$selectionSha,1],
    ['native-bindings-v1.json','native-bindings-v2.json',1],
    ["'actual_filtered_pool'=>132,'pagewide_original_type4_pool'=>432", "'actual_filtered_pool'=>84,'selected_bank_pagewide_type4_pool'=>384,'all_pagewide_original_type4_pool'=>480,'all_pagewide_original_question_pool'=>912",1],
    ['($i===2?72:12)','($i===5?24:12)',1],
    ['[12,12,72,12,12,12]','[12,12,12,12,12,24]',1],
    ['Only Question Forms has actual SQL NULL and therefore72 eligible originals. A/B new B1 practices and D/E/F continuingA1 practices each have12 eligible originals; display ranges never select questions.',
        'A-E exact A2/A2/A2/B1/A1 practice filters select twelve primary thematic originals each. F exact A2 reads the existing linked FinalDrillA2 bank of twenty-four originals for mixed-program breadth. Standalone theory selection does not alter course models, memberships, completion, score or progress. Display ranges and SQL NULL teaching blocks do not widen the practice filter.',1],
    ['$privateSelected=[];',$populationPrelude,1],
    ["    \$snapshots=\$page['relations']['question_snapshots']; \$poolRows=[]; \$poolByBank=[];",$populationOwner,1],
    ["        if (count(\$pagewide)!==72) { throw new RuntimeException('M82 original pagewide bank must contain exactly72 existing type4 rows.'); }",$populationBank,1],
    ['/** Replay actual unchanged native methods; no old M76 trap is part of this new package. */',$populationEnd,1],
];
foreach($rules as [$old,$new,$count]){
    if(substr_count($source,$old)!==$count){throw new RuntimeException('M82 reviewed immutable bank-profile literal count differs: '.$old);}
    $source=str_replace($old,$new,$source);
}
if(!str_starts_with($source,"<?php\n")){throw new RuntimeException('M82 immutable bank procedure prefix differs.');}
eval(substr($source,6));
