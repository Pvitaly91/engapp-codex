<?php

// Independent exact-field review of a saved plan; no application boot and no DB access.
if (PHP_SAPI !== 'cli') { exit(1); }
$source = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
$directory = $working.'/storage/app/seo-m39-local';
require $source.'/vendor/autoload.php';
// Path-only container: no kernel bootstrap, .env loading or database connection.
$app = new Illuminate\Foundation\Application($source);
$name = $argv[1] ?? '';
if (!preg_match('/^m39-preview-[a-z0-9-]+\.json$/D', $name)) { throw new RuntimeException('Invalid plan basename.'); }
$read = static fn ($path) => json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$plan = $read($directory.'/'.$name);
[$before, $package] = App\Support\M39AuthoredRevisionPackage::load($source);
App\Support\M39AuthoredRevisionPackage::validate($before, $package);
$assert = static function ($actual, $expected, $message): void {
    if ($actual !== $expected) { throw new RuntimeException($message); }
};
$assert($plan['state'], 'before', 'Review requires unapplied state.');
$assert(count($plan['updates']), count($package['targets']), 'Exactly one existing box update per accepted target required.');
$expectedInserts = array_sum(array_map(fn ($t) => count($t['after']['page']['blocks']) - 2, $package['targets']));
$assert(count($plan['inserts']), $expectedInserts, 'Exact native source insert count required.');
$assert($plan['names'], array_column($package['targets'], 'identity'), 'Owner list differs.');
$assert($plan['connection'], ['driver'=>'mysql','database'=>'gr2','server'=>gethostname(),'port'=>3306], 'Wrong physical local connection.');
$inventory = $read($directory.'/m39-before-v1.json');
$assert(count($plan['protected']), 20, 'All protected table fingerprints required.');
$assert(array_keys($plan['protected']), array_keys($inventory['fingerprints']), 'Protected table set differs.');
foreach ($plan['protected'] as $table=>$fingerprint) {
    $assert($fingerprint, $table==='text_blocks' ? $inventory['non_target_text_blocks'] : $inventory['fingerprints'][$table], 'Protected fingerprint differs: '.$table);
}
$assert(count($inventory['regressions']), 41, 'Complete M26-M38 regression owner snapshot required.');
$digest = $plan['sha256']; $digestPlan = $plan; unset($digestPlan['sha256']);
$assert(App\Services\M26ContentPatch::digest($digestPlan), $digest, 'Saved preview digest differs.');
foreach ($plan['sources'] as $path => $sha) {
    if ($path === 'private:'.App\Services\M39ContentPatch::SHARED_REVIEW) {
        App\Services\M39ContentPatch::reviewedSharedSources($source, $directory);
        $assert(hash_file('sha256', $directory.'/'.App\Services\M39ContentPatch::SHARED_REVIEW), $sha, 'Stale shared-source review.');
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
    $assert($owner['page_category_id'], $inventory['targets'][$i]['category_id'], 'Exact owner leaf category ID differs.');
    $assert(array_column($plan['pages'][$identity]['category_ancestry'], 'slug'),
        App\Services\M39ContentPatch::ANCESTRIES[$identity], 'Finite owner/category ancestry differs.');
    $assert($inventory['targets'][$i]['category_ancestry'], App\Services\M39ContentPatch::ANCESTRIES[$identity], 'Actual inventory ancestry differs.');
    $assert($inventory['targets'][$i]['category_slug'], App\Services\M39ContentPatch::CATEGORIES[$identity], 'Actual inventory leaf category differs.');
    $old = $before['targets'][$i]['before']['page']['blocks'][1];
    $new = $target['after']['page']['blocks'][1];
    $fidelity = $inventory['source_fidelity'][$identity] ?? [];
    $assert($fidelity['state'] ?? null, 'accepted_author_before', 'Fresh author-master/definition/actual DB gate missing.');
    $assert($fidelity['master_definition_db_exact'] ?? null, true, 'Fresh author fidelity gate did not pass.');
    $assert($fidelity['body_sha256'] ?? null, hash('sha256', $old['body']), 'Actual before body differs from accepted author master.');
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
    $assert($update['id'], $fidelity['box_id'], 'Fresh author gate belongs to another actual box.');
    $assert($update['uuid'], $fidelity['box_uuid'], 'Fresh author gate UUID differs.');
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
$expectedBackupPaths = ['app/Support/M39AuthoredRevisionPackage.php', 'app/Services/M39ContentPatch.php',
    'app/Services/M39LocalTargetGuard.php', 'app/Console/Commands/PatchM39Content.php', 'tools/diagnostics/run-m39-working-local.php',
    App\Support\M39AuthoredRevisionPackage::BEFORE, App\Support\M39AuthoredRevisionPackage::SOURCE,
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
    $assert($file['mode'], in_array($file['path'], App\Services\M39ContentPatch::SHARED_VIEWS, true) ? 'shared-verify-only' : 'finite-sync', 'Source sync mode differs.');
}
$review = ['at'=>gmdate('c'),'plan'=>$name,'plan_sha256'=>$plan['sha256'],'exact_field_review'=>true,
    'source_backup_verified'=>true,'updated'=>count($plan['updates']),'inserted'=>$expectedInserts,'deleted'=>0,'regression_db_writes'=>0,'rows'=>$rows];
$bytes = json_encode($review,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
$file = fopen($directory.'/'.substr($name,0,-5).'-review.json','x');
if (!$file || fwrite($file,$bytes)!==strlen($bytes) || !fflush($file)) { throw new RuntimeException('Exclusive review evidence failed.'); }
fclose($file);
echo json_encode(['exact_field_review'=>true,'source_backup_verified'=>true,'plan_sha256'=>$plan['sha256'],'updated'=>count($plan['updates']),'inserted'=>$expectedInserts,'deleted'=>0])."\n";
