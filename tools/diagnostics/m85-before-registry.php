<?php

/** Fresh native registry BEFORE M85 registration, no SQL writes. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M85',7],['m52','m85',5],
    ["['m85', 'registry']","['registry-before']",1],
    ['App\\Services\\M85LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
$priorPath=$root.'/storage/app/seo-m84-local/registry-sentence-builders-v1.json';
if(!is_file($priorPath)||is_link($priorPath)||!hash_equals('d96330cd25edce3165aa920534b8855a367698f07101a3cd48fe3e520a8ecc0d',hash_file('sha256',$priorPath))
    ||glob($root.'/database/content-patches/m85-*.json')){throw new RuntimeException('Accepted M84 registry evidence differs or M85 already registered.');}
$prior=json_decode(file_get_contents($priorPath),true,flags:JSON_THROW_ON_ERROR);
$semantic=static function(array $rows):array {
    $out=[];foreach($rows as $row){$id=$row['identity']??'';if(!is_string($id)||$id===''||isset($out[$id])){throw new RuntimeException('Registry identity missing/repeated.');}
        unset($row['http_sha256']);$out[$id]=$row;}ksort($out);return $out;
};
$expected=$semantic($prior['rows']??[]);$actual=$semantic($data['rows']??[]);
if(($prior['failed']??null)!==0||($prior['writes']??null)!==0||($data['failed']??null)!==0||($data['writes']??null)!==0
    ||($data['targets']??null)!==count($actual)||count($actual)!==count($expected)||$actual!==$expected){
    throw new RuntimeException('Fresh M85 registry BEFORE must preserve every accepted existing owner/semantic field.');
}
$data['m85_before_lineage']=['kind'=>'Fresh registry observations compared with accepted M84 expected owner set, not reused HTTP/SQL evidence.',
    'previous_registry_sha256'=>hash_file('sha256',$priorPath),'previous_semantic_rows_exact'=>count($expected),
    'volatile_whole_http_hash_only_excluded'=>true,'future_total_policy'=>'Exact fresh BEFORE union with completed new owners, no permanent cap.'];
PHP,1],
]);
