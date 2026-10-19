<?php
/** Read-only exact allowlist review of the fresh M46 preview against independent BEFORE. */
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || count($argv) !== 2) { exit(1); }
$root = dirname(__DIR__, 2); $working = 'D:/DEV/htdocs/gramlyze.loc';
require $working.'/vendor/autoload.php';
$name = $argv[1];
if (!preg_match('/^preview-v[1-9][0-9]*\.json$/D', $name)) { throw new RuntimeException('M46 preview basename required.'); }
$dir = $working.'/storage/app/seo-m46-local';
$plan = json_decode(file_get_contents($dir.'/'.$name), true, flags: JSON_THROW_ON_ERROR);
$withoutHash = $plan; unset($withoutHash['sha256']);
if ($plan['sha256'] !== App\Services\M26ContentPatch::digest($withoutHash)) { throw new RuntimeException('M46 preview digest differs.'); }
[$before, $package, $master] = App\Support\M46ConditionalsPackage::load($root);
App\Support\M46ConditionalsPackage::validate($before, $package, $master);
$capture = json_decode(file_get_contents($dir.'/before-v3.json'), true, flags: JSON_THROW_ON_ERROR);
if (hash_file('sha256', $dir.'/before-v3.json') !== App\Services\M46ContentPatch::BEFORE_SHA) { throw new RuntimeException('Independent M46 BEFORE changed.'); }
$changes = []; $insertions = [];
foreach ($plan['pages'] as $identity => $page) {
    if ($page['page'] !== $capture['pages'][$identity]['page']) { throw new RuntimeException('M46 fresh owner differs from BEFORE.'); }
    $old = $capture['pages'][$identity]['blocks']; $fresh = $page['blocks'];
    usort($old, static fn ($a,$b) => $a['id'] <=> $b['id']); usort($fresh, static fn ($a,$b) => $a['id'] <=> $b['id']);
    if ($old !== $fresh) { throw new RuntimeException('M46 fresh block rows differ from BEFORE.'); }
}
foreach ($plan['updates'] as $change) {
    $i = array_search($change['seeder'], App\Support\M46ConditionalsPackage::OWNERS, true);
    if ($i === false || !in_array($change['table'], ['pages','text_blocks'], true)
        || array_keys($change['after']) !== [$change['table'] === 'pages' ? 'text' : 'body']) { throw new RuntimeException('M46 field outside reviewed allowlist.'); }
    $new = $change['table'] === 'pages' ? $package['targets'][$i]['after']['page']['subtitle_text'] : null;
    if ($change['table'] === 'text_blocks') {
        $row = array_values(array_filter($plan['pages'][$change['seeder']]['blocks'], static fn ($r) => $r['id'] === $change['id']))[0];
        if ($row['locale'] !== 'uk') { throw new RuntimeException('M46 non-UK update refused.'); }
        $slot = $row['sort_order'];
        $new = $slot === 0 ? $package['targets'][$i]['after']['page']['subtitle_html'] : $package['targets'][$i]['after']['page']['blocks'][$slot - 1]['body'];
    }
    if (array_values($change['after'])[0] !== $new) { throw new RuntimeException('M46 reviewed content differs from master.'); }
    $changes[] = ['table'=>$change['table'],'id'=>$change['id'],'uuid'=>$change['uuid'],'identity'=>$change['seeder'],
        'fields'=>array_keys($change['after']),'before_sha256'=>$change['before_sha256'],'after_sha256'=>$change['after_sha256']];
}
foreach ($plan['inserts'] as $insert) {
    $f=$insert['fields']; $i=array_search($insert['seeder'],App\Support\M46ConditionalsPackage::OWNERS,true);
    if ($i===false||$insert['table']!=='text_blocks'||$f['locale']!=='uk'||$f['sort_order']<7) { throw new RuntimeException('M46 insert outside approved scope.'); }
    $cfg=$package['targets'][$i]['after']['page']['blocks'][$f['sort_order']-1];
    if ($f['uuid']!==App\Support\M26DetailPackage::uuid($insert['seeder'],$cfg,$f['sort_order']) ||$f['body']!==$cfg['body']) { throw new RuntimeException('M46 new stable content differs.'); }
    $insertions[]=['uuid'=>$f['uuid'],'identity'=>$insert['seeder'],'type'=>$f['type'],'sort_order'=>$f['sort_order'],'sha256'=>$insert['sha256']];
}
$review=['at'=>gmdate('c'),'preview'=>$name,'preview_sha256'=>$plan['sha256'],'master_sha256'=>App\Support\M46ConditionalsPackage::MASTER_SHA,
    'updates'=>$changes,'inserts'=>$insertions,'deleted'=>0,'protected_tables'=>array_keys($plan['protected'])];
$out=$dir.'/reviewed-allowlist-'.substr($name,0,-5).'.json';$bytes=json_encode($review,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$handle=fopen($out,'x');if(!$handle){throw new RuntimeException('M46 review receipt exists; no overwrite.');}
try{if(fwrite($handle,$bytes)!==strlen($bytes)||!fflush($handle)){throw new RuntimeException('Incomplete M46 review.');}}finally{fclose($handle);}
echo json_encode(['status'=>'reviewed','updated'=>count($changes),'inserted'=>count($insertions),'deleted'=>0,
    'preview_sha256'=>$plan['sha256'],'fields'=>['pages.text','text_blocks.body'],
    'new_components'=>array_count_values(array_column($insertions,'type')),'file'=>basename($out)],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
