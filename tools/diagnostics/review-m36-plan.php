<?php

// Independent exact-field review of a saved plan; no application boot and no DB access.
if (PHP_SAPI !== 'cli') { exit(1); }
$source = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
$directory = $working.'/storage/app/seo-m36-local';
require $source.'/vendor/autoload.php';
// Path-only container: no kernel bootstrap, .env loading or database connection.
$app = new Illuminate\Foundation\Application($source);
$name = $argv[1] ?? '';
if (!preg_match('/^m36-preview-[a-z0-9-]+\.json$/D', $name)) { throw new RuntimeException('Invalid plan basename.'); }
$read = static fn ($path) => json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$plan = $read($directory.'/'.$name);
[$before, $package] = App\Support\M36ModalsSubjunctivePackage::load($source);
App\Support\M36ModalsSubjunctivePackage::validate($before, $package);
$assert = static function ($actual, $expected, $message): void {
    if ($actual !== $expected) { throw new RuntimeException($message); }
};
$assert($plan['state'], 'before', 'Review requires unapplied state.');
$assert(count($plan['updates']), 3, 'Exactly three updates required.');
$expectedInserts = array_sum(array_map(fn ($t) => count($t['after']['page']['blocks']) - 2, $package['targets']));
$assert(count($plan['inserts']), $expectedInserts, 'Exact native source insert count required.');
$assert($plan['names'], array_column($package['targets'], 'identity'), 'Owner list differs.');
$assert($plan['connection'], ['driver'=>'mysql','database'=>'gr2','server'=>gethostname(),'port'=>3306], 'Wrong physical local connection.');
$inventory = $read($directory.'/m36-before-v2.json');
$assert(count($plan['protected']), 20, 'All protected table fingerprints required.');
$assert(array_keys($plan['protected']), array_keys($inventory['fingerprints']), 'Protected table set differs.');
foreach ($plan['protected'] as $table=>$fingerprint) {
    $assert($fingerprint, $table==='text_blocks' ? $inventory['non_target_text_blocks'] : $inventory['fingerprints'][$table], 'Protected fingerprint differs: '.$table);
}
$assert(count($inventory['regressions']), 32, 'Complete M26-M35 regression owner snapshot required.');
$digest = $plan['sha256']; $digestPlan = $plan; unset($digestPlan['sha256']);
$assert(App\Services\M26ContentPatch::digest($digestPlan), $digest, 'Saved preview digest differs.');
foreach ($plan['sources'] as $path => $sha) {
    if ($path === 'private:'.App\Services\M36ContentPatch::SHARED_REVIEW) {
        App\Services\M36ContentPatch::reviewedSharedSources($source, $directory);
        $assert(hash_file('sha256', $directory.'/'.App\Services\M36ContentPatch::SHARED_REVIEW), $sha, 'Stale shared-source review.');
    } else {
        $assert(hash_file('sha256', $source.'/'.$path), $sha, 'Stale source: '.$path);
    }
}
$rows = []; $insertIndex = 0;
foreach ($package['targets'] as $i => $target) {
    $identity = $target['identity'];
    $owner = $plan['pages'][$identity]['page'];
    $assert($owner['slug'], $target['slug'], 'Slug differs.');
    $assert($owner['id'], $inventory['targets'][$i]['page_id'], 'Actual owner ID differs.');
    $assert($owner['page_category_id'], $inventory['targets'][$i]['category_id'], 'Exact owner root category ID differs.');
    $assert(array_column($plan['pages'][$identity]['category_ancestry'], 'slug'),
        [App\Services\M36ContentPatch::CATEGORIES[$identity]], 'Finite owner/category mapping differs.');
    $assert($inventory['targets'][$i]['category_slug'], App\Services\M36ContentPatch::CATEGORIES[$identity], 'Actual inventory root category differs.');
    $old = $before['targets'][$i]['before']['page']['blocks'][1];
    $new = $target['after']['page']['blocks'][1];
    $update = $plan['updates'][$i];
    $assert($update['table'], 'text_blocks', 'Update table differs.');
    $assert($update['seeder'], $identity, 'Update owner differs.');
    $assert($update['before'], ['type'=>$old['type'], 'body'=>$old['body']], 'Old author body differs.');
    $assert($update['after'], ['type'=>$new['type'], 'body'=>$new['body']], 'Projected body differs.');
    $assert($update['uuid'], App\Support\M26DetailPackage::uuid($identity, $old, 2), 'First UUID differs.');
    $found = array_values(array_filter($plan['pages'][$identity]['blocks'], fn ($b) => $b['id'] === $update['id']));
    $assert(count($found), 1, 'Update ID not owned.');
    $assert($found[0]['sort_order'], 2, 'First order differs.');
    $assert($found[0]['uuid'], $update['uuid'], 'Update row UUID differs.');
    $assert($update['id'], $inventory['targets'][$i]['blocks'][2]['id'], 'Original actual box ID differs.');
    $rows[] = ['slug'=>$target['slug'], 'page_id'=>$owner['id'], 'update_id'=>$update['id'], 'updated_fields'=>['type','body'], 'inserted'=>[]];
    foreach (array_slice($target['after']['page']['blocks'], 2, null, true) as $j=>$block) {
        $position = $j+1;
        $fields = ['uuid'=>App\Support\M26DetailPackage::uuid($identity, $block, $position),
            'page_id'=>$owner['id'], 'page_category_id'=>$owner['page_category_id'], 'locale'=>'uk',
            'type'=>$block['type'], 'column'=>$block['column'], 'heading'=>$block['heading']??null,
            'css_class'=>$block['css_class']??null, 'sort_order'=>$position, 'body'=>$block['body'],
            'level'=>$block['level']??null, 'seeder'=>$identity];
        $insert = $plan['inserts'][$insertIndex++];
        $assert($insert['table'], 'text_blocks', 'Insert table differs.');
        $assert($insert['seeder'], $identity, 'Insert owner differs.');
        $assert($insert['fields'], $fields, 'Insert fields differ from exact native source.');
        $body = json_decode($fields['body'], true, flags: JSON_THROW_ON_ERROR);
        $rows[$i]['inserted'][] = ['uuid'=>$fields['uuid'], 'order'=>$position, 'type'=>$fields['type'], 'title'=>$body['title'], 'body_sha256'=>hash('sha256',$fields['body'])];
    }
    $anchors = $inventory['anchor_reference_inventory']['sources'][$identity]['ids'];
    $rendered = implode("\n", array_column($target['after']['page']['blocks'], 'body'));
    foreach ($anchors as $anchor) {
        if (!str_contains($rendered, $anchor)) { throw new RuntimeException('Existing author anchor removed: '.$anchor); }
    }
}
$backupName = $argv[2] ?? '';
if (!preg_match('/^source-backup-[a-f0-9]{16}$/D', $backupName)) { throw new RuntimeException('Invalid source backup basename.'); }
$backup = $directory.'/'.$backupName;
$backupRecords = $read($backup.'/manifest.json');
$expectedBackupPaths = ['app/Support/M36ModalsSubjunctivePackage.php', 'app/Services/M36ContentPatch.php',
    'app/Services/M36LocalTargetGuard.php', 'app/Console/Commands/PatchM36Content.php', 'tools/diagnostics/run-m36-working-local.php',
    App\Support\M36ModalsSubjunctivePackage::BEFORE, App\Support\M36ModalsSubjunctivePackage::SOURCE,
    ...array_column($package['targets'], 'path'), 'resources/views/theory/partials/content-block.blade.php',
    'resources/views/theory/partials/point-detail-fragment.blade.php',
    'resources/views/engram/theory/blocks-v3/usage-panels.blade.php',
    'resources/views/engram/theory/blocks-v3/comparison-table.blade.php',
    'resources/views/engram/theory/blocks-v3/summary-list.blade.php',
    'resources/views/engram/theory/blocks-v3/practice-set.blade.php'];
$assert(array_column($backupRecords, 'path'), $expectedBackupPaths, 'Source backup finite allowlist differs.');
foreach ($backupRecords as $file) {
    if ($file['before_sha256'] !== null) { $assert(hash_file('sha256',$backup.'/'.$file['path']), $file['before_sha256'], 'Incomplete source backup.'); }
    $assert(hash_file('sha256',$working.'/'.$file['path']), $file['after_sha256'], 'Working source differs.');
    $assert(hash_file('sha256',$source.'/'.$file['path']), $file['worktree_sha256'], 'Worktree source differs.');
    $assert($file['mode'], in_array($file['path'], App\Services\M36ContentPatch::SHARED_VIEWS, true) ? 'shared-verify-only' : 'finite-sync', 'Source sync mode differs.');
}
$review = ['at'=>gmdate('c'),'plan'=>$name,'plan_sha256'=>$plan['sha256'],'exact_field_review'=>true,
    'source_backup_verified'=>true,'updated'=>3,'inserted'=>$expectedInserts,'deleted'=>0,'regression_db_writes'=>0,'rows'=>$rows];
$bytes = json_encode($review,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
$file = fopen($directory.'/'.substr($name,0,-5).'-review.json','x');
if (!$file || fwrite($file,$bytes)!==strlen($bytes) || !fflush($file)) { throw new RuntimeException('Exclusive review evidence failed.'); }
fclose($file);
echo json_encode(['exact_field_review'=>true,'source_backup_verified'=>true,'plan_sha256'=>$plan['sha256'],'updated'=>3,'inserted'=>$expectedInserts,'deleted'=>0])."\n";
