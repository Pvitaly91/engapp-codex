<?php

/** Fresh native registry snapshot before any M83 registration; no apply. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M83',7],['m52','m83',5],
    ["['m83', 'registry']","['registry-before']",1],
    ['App\\Services\\M83LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
$priorPath=$root.'/storage/app/seo-m82-local/registry-sentence-builders-v2.json';
if(!is_file($priorPath)||is_link($priorPath)||!hash_equals('293f961d9d7fe037b805f314c3b2dcba1cd94d143f21c7ac517c82f67314fa69',hash_file('sha256',$priorPath))
    ||glob($root.'/database/content-patches/m83-*.json')){throw new RuntimeException('Accepted M82 registry evidence differs or M83 already registered.');}
$prior=json_decode(file_get_contents($priorPath),true,flags:JSON_THROW_ON_ERROR);
$semantic=static function(array $rows):array {
    $out=[];foreach($rows as $row){$id=$row['identity']??'';if(!is_string($id)||$id===''||isset($out[$id])){throw new RuntimeException('Registry identity missing/repeated.');}
        unset($row['http_sha256']);$out[$id]=$row;}ksort($out);return $out;
};
$expected=$semantic($prior['rows']??[]);$actual=$semantic($data['rows']??[]);
if(($prior['failed']??null)!==0||($prior['writes']??null)!==0||($data['failed']??null)!==0||($data['writes']??null)!==0
    ||($data['targets']??null)!==count($actual)||count($actual)!==count($expected)||$actual!==$expected){
    throw new RuntimeException('Fresh M83 registry BEFORE must preserve every accepted existing owner/semantic field.');
}
$data['m83_before_lineage']=['kind'=>'Fresh native registry observations, compared with accepted M82 final only as independent expected owner set; not reused HTTP/SQL evidence.',
    'previous_registry_sha256'=>hash_file('sha256',$priorPath),'previous_semantic_rows_exact'=>count($expected),
    'volatile_whole_http_hash_only_excluded'=>true,'future_total_policy'=>'Exact fresh BEFORE union with completed new owners, not a permanent cardinality cap.'];
PHP,1],
]);
