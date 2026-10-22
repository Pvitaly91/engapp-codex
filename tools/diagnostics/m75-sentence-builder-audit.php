<?php

/**
 * Thin M75/registry successor: reuse the unchanged generic matcher/HTTP command.
 * From actual served ROOT, after reviewed final SQL evidence and source sync:
 * php tools/diagnostics/m75-sentence-builder-audit.php m75 m75-sentence-builders-v1.json final-readonly-v1.json <final-raw-sha256>
 * php tools/diagnostics/m75-sentence-builder-audit.php registry registry-sentence-builders-v1.json final-readonly-v1.json <final-raw-sha256>
 * Browser visibility/interaction and full SQL-after-HTTP equality remain separate gates.
 */
$m75Failure=static function(Throwable $error):void {
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ? 'M75 sentence-builder audit: '.$error->getMessage()."\n"
        : "M75 sentence-builder audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
};
set_exception_handler($m75Failure);
require __DIR__.'/frozen-readonly-profile.php';

$preflight = <<<'PHP'
set_exception_handler(static function(Throwable $error):void {
    fwrite(STDERR,$error instanceof RuntimeException&&!$error instanceof PDOException
        ? 'M75 sentence-builder audit: '.$error->getMessage()."\n"
        : "M75 sentence-builder audit incomplete; no receipt. No SQL writes.\n");
    exit(1);
});
if (PHP_OS_FAMILY!=='Windows' || preg_match('/^'.preg_quote($argv[1],'/').'-sentence-builders-v[1-9][0-9]*\.json$/D',$argv[2])!==1
    || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D',$argv[3])!==1
    || preg_match('/^[a-f0-9]{64}$/D',$argv[4])!==1) {
    throw new RuntimeException('M75 explicit scope, fresh output basename and reviewed final receipt/SHA required.');
}
$m75Dir=$root.'/storage/app/seo-m75-local';
if (!is_dir($m75Dir) || is_link($m75Dir)
    || strtolower(str_replace('\\','/',(string)realpath($m75Dir)))!==strtolower($m75Dir)
    || file_exists($m75Dir.'/'.$argv[2]) || is_link($m75Dir.'/'.$argv[2])) {
    throw new RuntimeException('M75 actual private evidence directory differs or output exists.');
}
$m75Hash=static function(string $path):string {
    if (!is_file($path)||is_link($path)) { throw new RuntimeException('M75 audit input missing or linked.'); }
    $sha=hash_file('sha256',$path);if (!is_string($sha)) { throw new RuntimeException('M75 audit input cannot be hashed.'); }return $sha;
};
$m75Pins=[];
$m75Read=static function(string $name,string $sha)use($m75Dir,$m75Hash,&$m75Pins):array {
    if (preg_match('/^[a-zA-Z0-9_-]+\.json$/D',$name)!==1 || preg_match('/^[a-f0-9]{64}$/D',$sha)!==1
        || !hash_equals($sha,$m75Hash($m75Dir.'/'.$name))) { throw new RuntimeException('M75 independent audit evidence bytes differ.'); }
    $m75Pins[$m75Dir.'/'.$name]=$sha;return json_decode(file_get_contents($m75Dir.'/'.$name),true,flags:JSON_THROW_ON_ERROR);
};
$m75Before=$m75Read(App\Services\M75ContentPatch::BEFORE_FILE,App\Services\M75ContentPatch::BEFORE_SHA);
$m75Final=$m75Read($argv[3],$argv[4]);
$m75RegistrySha='01dfa8128a12fb185209823ecd2831bd92a0a65e3d4278fde496f6459df68f98';
$m75Registry=$m75Read('registry-before-sentence-builders-v1.json',$m75RegistrySha);
if (($m75Before['base_sha']??null)!==App\Support\M75PronounForms::BASE_SHA || ($m75Before['served_root']??null)!==$root
    || ($m75Before['sql_before']??null)!==($m75Before['sql_after_http']??null) || count($m75Before['sql_before']??[])!==46
    || ($m75Final['independent_before_sha256']??null)!==App\Services\M75ContentPatch::BEFORE_SHA
    || ($m75Final['runtime']??null)!==($m75Before['identity']??null)
    || ($m75Final['normalized_before']??null)!==($m75Before['sql_before']??null)
    || ($m75Final['all_old_rows_and_timestamps_exact']??null)!==true
    || ($m75Final['independent_before_projection_exact']??null)!==true
    || ($m75Final['reviewed_protected_projection_exact']??null)!==true
    || ($m75Final['visual_files_unchanged']??null)!==326 || ($m75Final['frozen_files_unchanged']??null)!==2624
    || ($m75Final['source_sync_unchanged']??null)!==true || ($m75Final['all_preview_sources_exact']??null)!==true
    || ($m75Final['db_writes']??null)!==0 || ($m75Final['http_requests']??null)!==0
    || count($m75Final['sql_after']??[])!==46
    || count($m75Before['visual_hashes']??[])!==326 || count($m75Before['frozen_hashes']??[])!==2624) {
    throw new RuntimeException('M75 exact independent BEFORE/final/visual/frozen lineage differs.');
}
[$m75SourceBefore,$m75Patch,$m75Master,$m75Contract]=App\Support\M75PronounForms::load($root);
App\Support\M75PronounForms::validate($m75SourceBefore,$m75Patch,$m75Master,$m75Contract);
$m75Audit=App\Support\M75BuilderSelectionPresentation::loadAudit($root);
if (array_column($m75Contract['targets'],'identity')!==App\Support\M75PronounForms::OWNERS
    || array_column($m75Contract['targets'],'level')!==[null,null,null]
    || ($m75Audit['review_summary']['technical_filter']??null)!==[null,null,null]) {
    throw new RuntimeException('M75 exact three native NULL filters differ.');
}
foreach (App\Support\M75PronounForms::profile()['artifacts'] as $path=>$sha) { $m75Pins[$root.'/'.$path]=$sha; }
$m75Pins[$root.'/'.App\Support\M75BuilderSelectionPresentation::AUDIT]=App\Support\M75BuilderSelectionPresentation::AUDIT_SHA;
$m75Pins[$root.'/'.App\Support\M75QuestionSupplement::SOURCE]=App\Support\M75QuestionSupplement::SOURCE_SHA;
foreach (App\Services\M75ContentPatch::questionEvidencePins() as $path=>$sha) {
    if (!str_starts_with($path,'private:')) { throw new RuntimeException('M75 private question evidence path differs.'); }
    $m75Read(substr($path,8),$sha);
}
if (($m75Final['question_plan_sha256']??null)!==App\Services\M75ContentPatch::QUESTION_PLAN_SHA
    || ($m75Final['question_inventory_sha256']??null)!==App\Services\M75ContentPatch::QUESTION_INVENTORY_SHA
    || ($m75Final['question_source_sha256']??null)!==App\Support\M75QuestionSupplement::SOURCE_SHA) {
    throw new RuntimeException('M75 final question evidence differs.');
}
foreach ($m75Patch['targets'] as $target) {
    if (json_decode(file_get_contents($root.'/'.$target['path']),true,flags:JSON_THROW_ON_ERROR)!==$target['after']) {
        throw new RuntimeException('M75 actual served definition is not reviewed frozen AFTER.');
    }
    $m75Pins[$root.'/'.$target['path']]=$m75Hash($root.'/'.$target['path']);
}
foreach ([$m75Before['visual_hashes'],$m75Before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path=>$sha) { $m75Pins[$root.'/'.$path]=$sha; }
}
$m75Pins[__DIR__.'/m75-sentence-builder-audit.php']=$m75Hash(__DIR__.'/m75-sentence-builder-audit.php');
$m75Pins[__DIR__.'/m52-sentence-builder-audit.php']='9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69';
foreach ($m75Pins as $path=>$sha) { if (!hash_equals($sha,$m75Hash($path))) { throw new RuntimeException('M75 audit input changed before command.'); } }
$options = ['--target' => 'gramlyze.loc'];
PHP;

$check = <<<'PHP'
$data=json_decode($output,true,flags:JSON_THROW_ON_ERROR);
if ($exit!==0 || ($data['failed']??null)!==0 || ($data['writes']??null)!==0
    || ($data['runtime']??null)!==($m75Before['identity']??null)) {
    throw new RuntimeException('M75 generic registry command failed or runtime differs.');
}
$m75ByOwner=static function(array $rows):array {
    $result=[];
    foreach ($rows as $row) {
        $owner=$row['identity']??null;
        if (!is_string($owner)||$owner===''||isset($result[$owner])) { throw new RuntimeException('M75 registry owner absent or duplicated.'); }
        $result[$owner]=$row;
    }
    ksort($result);return $result;
};
$m75Rows=$m75ByOwner($data['rows']??[]);
if (($data['targets']??null)!==count($m75Rows)) { throw new RuntimeException('M75 actual registry row count differs.'); }
$m75Expected=App\Support\M75PronounForms::OWNERS;sort($m75Expected);
if ($argv[1]==='registry') {
    $old=$m75ByOwner($m75Registry['rows']??[]);
    if (count($old)!==145 || ($m75Registry['targets']??null)!==count($old)
        || ($m75Registry['failed']??null)!==0 || ($m75Registry['writes']??null)!==0
        || ($m75Registry['runtime']??null)!==($data['runtime']??null)) {
        throw new RuntimeException('M75 independent accepted 145-owner registry baseline differs.');
    }
    $transport=[];
    foreach ($old as $owner=>$record) {
        $current=$m75Rows[$owner]??null;
        if (!is_array($current) || preg_match('/^[a-f0-9]{64}$/D',$record['http_sha256']??'')!==1
            || preg_match('/^[a-f0-9]{64}$/D',$current['http_sha256']??'')!==1) {
            throw new RuntimeException('M75 old registry transport evidence absent or malformed.');
        }
        $transport[$owner]=['before_sha256'=>$record['http_sha256'],'after_sha256'=>$current['http_sha256'],
            'raw_equal'=>$record['http_sha256']===$current['http_sha256']];
        unset($record['http_sha256'],$current['http_sha256']);
        if ($record!==$current) { throw new RuntimeException('M75 accepted old registry structural record changed: '.$owner); }
    }
    $new=array_values(array_diff(array_keys($m75Rows),array_keys($old)));sort($new);
    $expected=App\Support\M75PronounForms::OWNERS;sort($expected);
    if ($new!==$expected || count($m75Rows)!==count($old)+count($expected)) {
        throw new RuntimeException('M75 registry gained owners outside the exact three approved pages.');
    }
    $data['independent_registry_before_sha256']=$m75RegistrySha;
    $data['old_registry_records_preserved']=count($old);$data['new_registry_owners']=$new;$data['old_registry_transport']=$transport;
    $data['registry_comparison_policy']='All 145 captured owner-keyed structural records exact; full HTTP hashes retained separately and not claimed byte-identical. Current count equals captured baseline plus the exact three M75 owners.';
} elseif (array_keys($m75Rows)!==$m75Expected) {
    throw new RuntimeException('M75 filtered audit owners differ.');
}
foreach ($m75Audit['targets'] as $i=>$target) {
    $current=$m75Rows[$target['identity']]??[];
    if (($target['identity']??null)!==App\Support\M75PronounForms::OWNERS[$i] || !array_key_exists('level',$target) || $target['level']!==null
        || ($current['page_id']??null)!==App\Support\M75PronounForms::PAGE_IDS[$i]
        || ($current['practice_id']??null)!==[4011,4103,3924][$i]
        || ($current['practice_uuid']??null)!==App\Support\M75PronounForms::PRACTICE_UUIDS[$i]
        || ($current['effective_config']['source']??null)!=='theory_links'
        || ($current['effective_config']['question_types']??null)!==['4']
        || ($current['effective_config']['seeder_classes']??null)!==$target['bank_seeders']
        || ($current['selected_ids']??null)!==$target['expected_selected_ids']
        || ($current['eligible_count']??null)!==array_sum(array_map('count',$target['expected_pool_ids_by_bank']))
        || ($current['selected_count']??null)!==5 || ($current['selected_types']??null)!==array_fill(0,5,'4')
        || ($current['widget_count']??null)!==1 || ($current['practice_widget_count']??null)!==1
        || ($current['http_status']??null)!==200 || ($current['final_url']??null)!==$target['local_url']) {
        throw new RuntimeException('M75 actual widget differs from finite independently pinned thematic selection.');
    }
}
foreach ($m75Pins as $path=>$sha) { if (!hash_equals($sha,$m75Hash($path))) { throw new RuntimeException('M75 pinned evidence/source changed during generic audit.'); } }
$data['m75_declared_technical_filters']=[null,null,null];$data['m75_exact_selected_ids_checked']=15;
$data['m75_independent_before_sha256']=App\Services\M75ContentPatch::BEFORE_SHA;
$data['m75_reviewed_final_file']=$argv[3];$data['m75_reviewed_final_sha256']=$argv[4];
$data['m75_package_sha256']=App\Support\M75PronounForms::SOURCE_SHA;
$data['m75_bank_audit_sha256']=App\Support\M75BuilderSelectionPresentation::AUDIT_SHA;
$data['m75_visual_files_unchanged']=326;$data['m75_frozen_files_unchanged']=2624;
$data['m75_wrapper_sha256']=$m75Pins[__DIR__.'/m75-sentence-builder-audit.php'];
$data['sql_after_http_equality']='Separate independent read-only final/old-consumer gate; this wrapper does not claim a full SQL fingerprint after HTTP.';
PHP;

runFrozenReadOnlyProfile('m52-sentence-builder-audit.php','9da92df6f76753f441036a42bc4cd38bc7ae6e450fa4d53dbfe1b24f041ccc69',[
    ['M52','M75',7],['m52','m75',5],
    ['count($argv) !== 2','count($argv) !== 5',1],
    ["$"."options = ['--target' => 'gramlyze.loc'];",$preflight,1],
    ['m75-passive-basics.v1.json','m75-possessive-reflexive-indefinite.v1.json',1],
    ["$"."data = json_decode($"."output, true, flags: JSON_THROW_ON_ERROR);",$check,1],
    ["$"."root.'/storage/app/seo-m75-local/'.$"."argv[1].'-sentence-builders-v1.json'","$"."root.'/storage/app/seo-m75-local/'.$"."argv[2]",1],
]);
