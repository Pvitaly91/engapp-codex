<?php

/**
 * Thin M76/registry successor: reuse the unchanged generic matcher/HTTP command.
 * From actual served ROOT, after reviewed final SQL evidence and source sync:
 * php tools/diagnostics/m76-sentence-builder-audit.php m76 m76-sentence-builders-v1.json final-readonly-v1.json <final-raw-sha256>
 * php tools/diagnostics/m76-sentence-builder-audit.php registry registry-sentence-builders-v1.json final-readonly-v1.json <final-raw-sha256>
 * Browser visibility/interaction and full SQL-after-HTTP equality remain separate gates.
 */
$m76Failure=static function(Throwable $error):void {
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ? 'M76 sentence-builder audit: '.$error->getMessage()."\n"
        : "M76 sentence-builder audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($m76Failure);
require __DIR__.'/frozen-readonly-profile.php';

$preflight = <<<'PHP'
set_exception_handler(static function(Throwable $error):void {
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ? 'M76 sentence-builder audit: '.$error->getMessage()."\n"
        : "M76 sentence-builder audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
});
if (PHP_OS_FAMILY!=='Windows' || preg_match('/^'.preg_quote($argv[1],'/').'-sentence-builders-v[1-9][0-9]*\.json$/D',$argv[2])!==1
    || preg_match('/^(?:anchor-)?final-readonly-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    || preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1) {
    throw new RuntimeException('M76 explicit scope, fresh output basename and reviewed final receipt/SHA required.');
}
$m76Dir=$root.'/storage/app/seo-m76-local';
if (!is_dir($m76Dir) || is_link($m76Dir)
    || strtolower(str_replace('\\','/',(string)realpath($m76Dir)))!==strtolower($m76Dir)
    || file_exists($m76Dir.'/'.$argv[2]) || is_link($m76Dir.'/'.$argv[2])) {
    throw new RuntimeException('M76 actual private evidence directory differs or output exists.');
}
$m76Hash=static function(string $path):string {
    if (!is_file($path)||is_link($path)) { throw new RuntimeException('M76 audit input missing or linked.'); }
    $sha=hash_file('sha256',$path);if (!is_string($sha)) { throw new RuntimeException('M76 audit input cannot be hashed.'); }return $sha;
};
$m76Pins=[];
$m76Read=static function(string $name,string $sha)use($m76Dir,$m76Hash,&$m76Pins):array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1 || preg_match('/^[a-f0-9]{64}$/D',$sha)!==1
        || !hash_equals($sha,$m76Hash($m76Dir.'/'.$name))) { throw new RuntimeException('M76 independent audit evidence bytes differ.'); }
    $m76Pins[$m76Dir.'/'.$name]=$sha;return json_decode(file_get_contents($m76Dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
};
$m76Before=$m76Read(App\Services\M76ContentPatch::BEFORE_FILE,App\Services\M76ContentPatch::BEFORE_SHA);
$m76Final=$m76Read($argv[3],$argv[4]);
if (str_starts_with($argv[3],'anchor-') && (($m76Final['successor_version']??null)!=='1.0.1'
    ||($m76Final['original_final_sha256']??null)!=='214ddb282ee6daad426c84ccc3cb9e9b26d12423a0ace44efe33aa2dab21b053'
    ||($m76Final['revision_sha256']??null)!=='9d30fa87cd71ba53730f7f48d77eec73fb66c245f4149db2a296ee64a8b1bbfb'
    ||($m76Final['predecessor_sql_exact']??null)!==true||($m76Final['anchor_body_updates']??null)!==1)) {
    throw new RuntimeException('M76 exact anchor successor lineage differs.');
}
$m76RegistrySha='6dad2253bf9730478debc9e4defaeb1ad53b928f387efb01f59234b81ac5f541';
$m76Registry=$m76Read('registry-before-sentence-builders-v1.json',$m76RegistrySha);
if (($m76Before['base_sha']??null)!==App\Support\M76PronounRelations::BASE_SHA || ($m76Before['served_root']??null)!==$root
    || ($m76Before['sql_before']??null)!==($m76Before['sql_after_http']??null) || count($m76Before['sql_before']??[])!==46
    || ($m76Final['independent_before_sha256']??null)!==App\Services\M76ContentPatch::BEFORE_SHA
    || ($m76Final['runtime']??null)!==($m76Before['identity']??null)
    || ($m76Final['normalized_before']??null)!==($m76Before['sql_before']??null)
    || ($m76Final['all_old_rows_and_timestamps_exact']??null)!==true
    || ($m76Final['independent_before_projection_exact']??null)!==true
    || ($m76Final['reviewed_protected_projection_exact']??null)!==true
    || ($m76Final['visual_files_unchanged']??null)!==326 || ($m76Final['frozen_files_unchanged']??null)!==2642
    || ($m76Final['source_sync_unchanged']??null)!==true || ($m76Final['all_preview_sources_exact']??null)!==true
    || ($m76Final['db_writes']??null)!==0 || ($m76Final['http_requests']??null)!==0
    || count($m76Final['sql_after']??[])!==46
    || count($m76Before['visual_hashes']??[])!==326 || count($m76Before['frozen_hashes']??[])!==2642) {
    throw new RuntimeException('M76 exact independent BEFORE/final/visual/frozen lineage differs.');
}
[$m76SourceBefore,$m76Patch,$m76Master,$m76Contract]=App\Support\M76PronounRelations::load($root);
App\Support\M76PronounRelations::validate($m76SourceBefore,$m76Patch,$m76Master,$m76Contract);
$m76Audit=App\Support\M76BuilderSelectionPresentation::loadAudit($root);
if (array_column($m76Contract['targets'],'identity')!==App\Support\M76PronounRelations::OWNERS
    || array_column($m76Contract['targets'],'level')!==[null,null,null]
    || ($m76Audit['review_summary']['technical_filter']??null)!==[null,null,null]) {
    throw new RuntimeException('M76 exact three native NULL filters differ.');
}
foreach (App\Support\M76PronounRelations::profile()['artifacts'] as $path=>$sha) { $m76Pins[$root.'/'.$path]=$sha; }
$m76Pins[$root.'/'.App\Support\M76BuilderSelectionPresentation::AUDIT]=App\Support\M76BuilderSelectionPresentation::AUDIT_SHA;
foreach ($m76Patch['targets'] as $target) {
    if (json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']) {
        throw new RuntimeException('M76 actual served definition is not reviewed frozen AFTER.');
    }
    $m76Pins[$root.'/'.$target['path']]=$m76Hash($root.'/'.$target['path']);
}
foreach ([$m76Before['visual_hashes'],$m76Before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path=>$sha) { $m76Pins[$root.'/'.$path]=$sha; }
}
$m76Pins[__DIR__.'/m76-sentence-builder-audit.php']=$m76Hash(__DIR__.'/m76-sentence-builder-audit.php');
$m76Pins[__DIR__.'/m52-sentence-builder-audit.php']='9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69';
foreach ($m76Pins as $path=>$sha) { if (!hash_equals($sha,$m76Hash($path))) { throw new RuntimeException('M76 audit input changed before command.'); } }
$options = ['--target' => 'gramlyze.loc'];
PHP;

$check = <<<'PHP'
$data=json_decode($output,true,flags:JSON_THROW_ON_ERROR);
if ($exit!==0 || ($data['failed']??null)!==0 || ($data['writes']??null)!==0
    || ($data['runtime']??null)!==($m76Before['identity']??null)) {
    throw new RuntimeException('M76 generic registry command failed or runtime differs.');
}
$m76ByOwner=static function(array $rows):array {
    $result=[];
    foreach ($rows as $row) {
        $owner=$row['identity']??null;
        if (!is_string($owner)||$owner===''||isset($result[$owner])) { throw new RuntimeException('M76 registry owner absent or duplicated.'); }
        $result[$owner]=$row;
    }
    ksort($result);return $result;
};
$m76Rows=$m76ByOwner($data['rows']??[]);
if (($data['targets']??null)!==count($m76Rows)) { throw new RuntimeException('M76 actual registry row count differs.'); }
$m76Expected=App\Support\M76PronounRelations::OWNERS;sort($m76Expected);
if ($argv[1]==='registry') {
    $old=$m76ByOwner($m76Registry['rows']??[]);
    if (count($old)!==148 || ($m76Registry['targets']??null)!==count($old)
        || ($m76Registry['failed']??null)!==0 || ($m76Registry['writes']??null)!==0
        || ($m76Registry['runtime']??null)!==($data['runtime']??null)) {
        throw new RuntimeException('M76 independent accepted 148-owner registry baseline differs.');
    }
    $transport=[];
    foreach ($old as $owner=>$record) {
        $current=$m76Rows[$owner]??null;
        if (!is_array($current) || preg_match('/^[a-f0-9]{64}$/D',$record['http_sha256']??'')!==1
            || preg_match('/^[a-f0-9]{64}$/D',$current['http_sha256']??'')!==1) {
            throw new RuntimeException('M76 old registry transport evidence absent or malformed.');
        }
        $transport[$owner]=['before_sha256'=>$record['http_sha256'],'after_sha256'=>$current['http_sha256'],
            'raw_equal'=>$record['http_sha256']===$current['http_sha256']];
        unset($record['http_sha256'],$current['http_sha256']);
        if ($record!==$current) { throw new RuntimeException('M76 accepted old registry structural record changed: '.$owner); }
    }
    $new=array_values(array_diff(array_keys($m76Rows),array_keys($old)));sort($new);
    $expected=App\Support\M76PronounRelations::OWNERS;sort($expected);
    if ($new!==$expected || count($m76Rows)!==count($old)+count($expected)) {
        throw new RuntimeException('M76 registry gained owners outside the exact three approved pages.');
    }
    $data['independent_registry_before_sha256']=$m76RegistrySha;
    $data['old_registry_records_preserved']=count($old);$data['new_registry_owners']=$new;$data['old_registry_transport']=$transport;
    $data['registry_comparison_policy']='All 148 captured owner-keyed structural records exact; full HTTP hashes retained separately and not claimed byte-identical. Current count equals captured baseline plus the exact three M76 owners.';
} elseif (array_keys($m76Rows)!==$m76Expected) {
    throw new RuntimeException('M76 filtered audit owners differ.');
}
foreach ($m76Audit['targets'] as $i=>$target) {
    $current=$m76Rows[$target['identity']]??[];
    if (($target['identity']??null)!==App\Support\M76PronounRelations::OWNERS[$i] || !array_key_exists('level',$target) || $target['level']!==null
        || ($current['page_id']??null)!==App\Support\M76PronounRelations::PAGE_IDS[$i]
        || ($current['practice_id']??null)!==[4140,8937,4086][$i]
        || ($current['practice_uuid']??null)!==App\Support\M76PronounRelations::PRACTICE_UUIDS[$i]
        || ($current['effective_config']['source']??null)!=='theory_links'
        || ($current['effective_config']['question_types']??null)!==['4']
        || ($current['effective_config']['seeder_classes']??null)!==$target['bank_seeders']
        || ($current['selected_ids']??null)!==$target['expected_selected_ids']
        || ($current['eligible_count']??null)!==array_sum(array_map('count',$target['expected_pool_ids_by_bank']))
        || ($current['selected_count']??null)!==5 || ($current['selected_types']??null)!==array_fill(0,5,'4')
        || ($current['widget_count']??null)!==1 || ($current['practice_widget_count']??null)!==1
        || ($current['http_status']??null)!==200 || ($current['final_url']??null)!==$target['local_url']) {
        throw new RuntimeException('M76 actual widget differs from finite independently pinned thematic selection.');
    }
}
foreach ($m76Pins as $path=>$sha) { if (!hash_equals($sha,$m76Hash($path))) { throw new RuntimeException('M76 pinned evidence/source changed during generic audit.'); } }
$data['m76_declared_technical_filters']=[null,null,null];$data['m76_exact_selected_ids_checked']=15;
$data['m76_independent_before_sha256']=App\Services\M76ContentPatch::BEFORE_SHA;
$data['m76_reviewed_final_file']=$argv[3];$data['m76_reviewed_final_sha256']=$argv[4];
$data['m76_package_sha256']=App\Support\M76PronounRelations::SOURCE_SHA;
$data['m76_bank_audit_sha256']=App\Support\M76BuilderSelectionPresentation::AUDIT_SHA;
$data['m76_visual_files_unchanged']=326;$data['m76_frozen_files_unchanged']=2642;
$data['m76_wrapper_sha256']=$m76Pins[__DIR__.'/m76-sentence-builder-audit.php'];
$data['sql_after_http_equality']='Separate independent read-only final/old-consumer gate; this wrapper does not claim a full SQL fingerprint after HTTP.';
PHP;

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M76',7],['m52','m76',5],
    ['count($argv) !== 2','count($argv) !== 5',1],
    ["$"."options = ['--target' => 'gramlyze.loc'];",$preflight,1],
    ['m76-passive-basics.v1.json','m76-relative-each-reciprocal.v1.json',1],
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);",$check,1],
    ["$"."root.'/storage/app/seo-m76-local/'.$"."argv[1].'-sentence-builders-v1.json'","$"."root.'/storage/app/seo-m76-local/'.$"."argv[2]",1],
]);
