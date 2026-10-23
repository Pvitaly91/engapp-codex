<?php

/** Reuse the immutable native snapshot/formatter audit with one finite M81 profile. */
$file=__DIR__.'/m78-bank-audit-projection.php';
if (PHP_SAPI!=='cli'||is_link($file)||!is_file($file)
    ||!hash_equals('1df46fd529f9a43dfaf3108e9831a803f3fd2931459edf81c58c3841b9c384bc',hash_file('sha256',$file))) {
    throw new RuntimeException('M81 immutable bank-audit primitive differs.');
}
$source=file_get_contents($file);
$selectionSha='9e91306f8514dee063a63a009c482846f33893b4f1ca39c1e80bf1473ddb975f';
if (preg_match('/^[a-f0-9]{64}$/D',$selectionSha)!==1) { throw new RuntimeException('M81 independent widget review is not frozen.'); }
$rules=[
    ['M78PhrasalQuestionBasics','M81TransformationsCommonMistakes',2],
    ['M78','M81',46],
    ['m78-phrasal-question-basics','m81-transformations-common-mistakes',2],
    ['m78-','m81-',2],
    ['327424de32f1cebaad86adef078d25aa1d4747b257894be3fdadc6706c630422','facabc5369230dac0958f2e2277c7cd0e4b0523fb794c041afd46116e095c023',2],
    ['63cfcb4fccea1a3a8008d07cfcd323224ef0699b2c58e948d10d92a5ee44300a','d118fb390a713cb5256e57c04377afdf6c9aa7dad0259c80db1058582353bc23',1],
    ['4b009bc23f878b27d6feee2ca6a8c919f892fe21c0f93f1842afdb6f32512e30',$selectionSha,1],
    ["'actual_filtered_pool'=>132","'actual_filtered_pool'=>72",1],
    ['($i===2?72:12)','12',1],
    ['[12,12,72,12,12,12]','[12,12,12,12,12,12]',1],
    ['Only Question Forms has actual SQL NULL and therefore72 eligible originals. A/B new B1 practices and D/E/F continuingA1 practices each have12 eligible originals; display ranges never select questions.',
        'Six new exact B1/B1/B1/B2/A2/A2 practice filters each select twelve existing thematic type-four rows. Continuing source ranges and SQL NULL teaching blocks are retained and do not select widget questions.',1],
    ['no old M76 trap is part of this new package','old case-unsafe candidates are excluded rather than repaired in this package',1],
];
foreach ($rules as [$old,$new,$count]) {
    if (substr_count($source,$old)!==$count) { throw new RuntimeException('M81 reviewed immutable bank profile count differs: '.$old); }
    $source=str_replace($old,$new,$source);
}
if (!str_starts_with($source,"<?php\n")) { throw new RuntimeException('M81 immutable bank procedure prefix differs.'); }
eval(substr($source,6));
