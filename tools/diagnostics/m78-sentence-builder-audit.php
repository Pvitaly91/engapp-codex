<?php

/** Finite M78 profile of the immutable read-only registry gate; never changes data. */
require __DIR__.'/frozen-readonly-profile.php';
$decodeWithSafeFailure = <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
if ($exit !== 0 || ($data['failed'] ?? null) !== 0 || ($data['writes'] ?? null) !== 0) {
    $failed=[];
    foreach ($data['rows'] ?? [] as $row) {
        if (($row['errors'] ?? []) !== []) {
            $failed[]=['identity'=>$row['identity']??null,'url'=>$row['url']??null,'errors'=>$row['errors']];
        }
    }
    fwrite(STDERR,json_encode(['scope'=>$argv[1],'command_exit'=>$exit,'targets'=>$data['targets']??null,
        'failed'=>$data['failed']??null,'writes'=>$data['writes']??null,'failed_rows'=>$failed,
        'context'=>array_intersect_key($data['context']??[],array_flip(['stage','owner','url','source_basename']))],
        JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
PHP;
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M78',7],['m52','m78',5],
    ['m78-passive-basics.v1.json','m78-phrasal-question-basics.v1.json',1],
    ["(\$data['targets'] ?? null) !== 3","(\$data['targets'] ?? null) !== 6",1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);",$decodeWithSafeFailure,1],
]);

