<?php

/** Fresh native registry BEFORE M84 registration, no SQL writes. */
require __DIR__.'/frozen-readonly-profile.php';
runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M84',7],['m52','m84',5],
    ["['m84', 'registry']","['registry-before']",1],
    ['App\\Services\\M84LocalTargetGuard::assertConfiguredDatabase','App\\Services\\M49LocalTargetGuard::assertConfiguredDatabase',1],
    ["\$data = json_decode(\$output, true, flags: JSON_THROW_ON_ERROR);", <<<'PHP'
$data = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
$priorPath=$root.'/storage/app/seo-m83-local/registry-sentence-builders-v2.json';
if(!is_file($priorPath)||is_link($priorPath)||!hash_equals('beedb6bab34df4c3dbd5e8818019ba063a3ce8e7f985164bd692dc3f9ee69b50',hash_file('sha256',$priorPath))
    ||glob($root.'/database/content-patches/m84-*.json')){throw new RuntimeException('Accepted M83 registry evidence differs or M84 already registered.');}
$prior=json_decode(file_get_contents($priorPath),true,flags:JSON_THROW_ON_ERROR);
$semantic=static function(array $rows):array {
    $out=[];foreach($rows as $row){$id=$row['identity']??'';if(!is_string($id)||$id===''||isset($out[$id])){throw new RuntimeException('Registry identity missing/repeated.');}
        unset($row['http_sha256']);$out[$id]=$row;}ksort($out);return $out;
};
$expected=$semantic($prior['rows']??[]);$actual=$semantic($data['rows']??[]);
if(($prior['failed']??null)!==0||($prior['writes']??null)!==0||($data['failed']??null)!==0||($data['writes']??null)!==0
    ||($data['targets']??null)!==count($actual)||count($actual)!==count($expected)||$actual!==$expected){
    throw new RuntimeException('Fresh M84 registry BEFORE must preserve every accepted existing owner/semantic field.');
}
$data['m84_before_lineage']=['kind'=>'Fresh registry observations compared with accepted M83 expected owner set, not reused HTTP/SQL evidence.',
    'previous_registry_sha256'=>hash_file('sha256',$priorPath),'previous_semantic_rows_exact'=>count($expected),
    'volatile_whole_http_hash_only_excluded'=>true,'future_total_policy'=>'Exact fresh BEFORE union with completed new owners, no permanent cap.'];
PHP,1],
]);
