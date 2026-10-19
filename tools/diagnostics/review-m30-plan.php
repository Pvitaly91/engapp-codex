<?php

// Independent exact-field review of a saved plan; no application boot and no DB access.
if (PHP_SAPI !== 'cli') { exit(1); }
$source = dirname(__DIR__, 2);
$working = 'D:/DEV/htdocs/gramlyze.loc';
$directory = $working.'/storage/app/seo-m30-local';
require $source.'/vendor/autoload.php';
// Path-only container: no kernel bootstrap, .env loading or database connection.
$app = new Illuminate\Foundation\Application($source);
$name = $argv[1] ?? '';
if (!preg_match('/^m30-preview-[a-z0-9-]+\.json$/D', $name)) { throw new RuntimeException('Invalid plan basename.'); }
$read = static fn ($path) => json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$plan = $read($directory.'/'.$name);
[$before, $package] = App\Support\M30ParticipleClausesPackage::load($source);
App\Support\M30ParticipleClausesPackage::validate($before, $package);
$assert = static function ($actual, $expected, $message): void {
    if ($actual !== $expected) { throw new RuntimeException($message); }
};
$assert($plan['state'], 'before', 'Review requires unapplied state.');
$assert(count($plan['updates']), 3, 'Exactly three updates required.');
$assert(count($plan['inserts']), 21, 'Exactly twenty-one inserts required.');
$assert($plan['names'], array_column($package['targets'], 'identity'), 'Owner list differs.');
$assert($plan['connection']['database'], 'gr2', 'Wrong database.');
foreach ($plan['sources'] as $path => $sha) {
    $assert(hash_file('sha256', $source.'/'.$path), $sha, 'Stale source: '.$path);
}
$rows = [];
foreach ($package['targets'] as $i => $target) {
    $identity = $target['identity'];
    $owner = $plan['pages'][$identity]['page'];
    $assert($owner['slug'], $target['slug'], 'Slug differs.');
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
    $rows[] = ['slug'=>$target['slug'], 'page_id'=>$owner['id'], 'update_id'=>$update['id'], 'updated_fields'=>['type','body'], 'inserted'=>[]];
    foreach (array_slice($target['after']['page']['blocks'], 2, null, true) as $j=>$block) {
        $position = $j+1;
        $fields = ['uuid'=>App\Support\M26DetailPackage::uuid($identity, $block, $position),
            'page_id'=>$owner['id'], 'page_category_id'=>$owner['page_category_id'], 'locale'=>'uk',
            'type'=>$block['type'], 'column'=>$block['column'], 'heading'=>$block['heading']??null,
            'css_class'=>$block['css_class']??null, 'sort_order'=>$position, 'body'=>$block['body'],
            'level'=>$block['level']??null, 'seeder'=>$identity];
        $insert = $plan['inserts'][$i*7+$j-2];
        $assert($insert['table'], 'text_blocks', 'Insert table differs.');
        $assert($insert['seeder'], $identity, 'Insert owner differs.');
        $assert($insert['fields'], $fields, 'Insert fields differ from exact native source.');
        $body = json_decode($fields['body'], true, flags: JSON_THROW_ON_ERROR);
        $rows[$i]['inserted'][] = ['uuid'=>$fields['uuid'], 'order'=>$position, 'type'=>$fields['type'], 'title'=>$body['title'], 'body_sha256'=>hash('sha256',$fields['body'])];
    }
}
$backup = $directory.'/source-backup-d33ac2b0e929e635';
foreach ($read($backup.'/manifest.json') as $file) {
    if ($file['before_sha256'] !== null) { $assert(hash_file('sha256',$backup.'/'.$file['path']), $file['before_sha256'], 'Incomplete source backup.'); }
    $assert(hash_file('sha256',$working.'/'.$file['path']), $file['after_sha256'], 'Working source differs.');
    $assert(hash_file('sha256',$source.'/'.$file['path']), $file['after_sha256'], 'Worktree source differs.');
}
$review = ['at'=>gmdate('c'),'plan'=>$name,'plan_sha256'=>$plan['sha256'],'exact_field_review'=>true,
    'source_backup_verified'=>true,'updated'=>3,'inserted'=>21,'deleted'=>0,'m29_db_writes'=>0,'rows'=>$rows];
$bytes = json_encode($review,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
$file = fopen($directory.'/'.substr($name,0,-5).'-review.json','x');
if (!$file || fwrite($file,$bytes)!==strlen($bytes) || !fflush($file)) { throw new RuntimeException('Exclusive review evidence failed.'); }
fclose($file);
echo json_encode(['exact_field_review'=>true,'source_backup_verified'=>true,'plan_sha256'=>$plan['sha256'],'updated'=>3,'inserted'=>21,'deleted'=>0])."\n";
