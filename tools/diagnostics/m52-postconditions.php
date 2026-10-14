<?php

/** Explicit final read-only M52 data/source gate, not apply/restore/seeder or startup work. */
if (PHP_SAPI !== 'cli' || count($argv) !== 1 || PHP_OS_FAMILY !== 'Windows') { throw new RuntimeException('Explicit M52 final audit only.'); }
set_exception_handler(static function (Throwable $e): void {
    fwrite(STDERR, $e instanceof RuntimeException && !$e instanceof PDOException ? $e->getMessage()."\n" : 'M52 final audit incomplete. No writes.'); exit(1);
});
$root = 'D:/DEV/htdocs/gramlyze.loc';
if (strtolower(str_replace('\\', '/', (string) realpath(dirname(__DIR__, 2)))) !== strtolower($root)) { throw new RuntimeException('M52 audit ROOT differs.'); }
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection(); App\Services\M52LocalTargetGuard::assertConfiguredDatabase($db);
$dir = $root.'/storage/app/seo-m52-local';
$load = static function ($name, $sha = null) use ($dir): array {
    $file = $dir.'/'.$name;
    if (!is_file($file) || is_link($file) || ($sha !== null && hash_file('sha256', $file) !== $sha)) { throw new RuntimeException('M52 private final evidence differs.'); }
    return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
};
$before = $load('before-v1.json', App\Services\M52ContentPatch::BEFORE_SHA);
$plan = $load('preview-v2.json');
if ($plan['sha256'] !== 'b1283f9b37f33330e36ba1c017b580336d2e5e4f93ba8cbaebaafc44453e202f'
    || count($plan['updates']) !== 37 || count($plan['inserts']) !== 3) { throw new RuntimeException('M52 reviewed exact plan differs.'); }
[$sourceBefore, $patch, $master, $contract] = App\Support\M52PassiveBasicsPackage::load($root);
App\Support\M52PassiveBasicsPackage::validate($sourceBefore, $patch, $master, $contract);
foreach ($before['physical_hashes'] as $path => $sha) { if (hash_file('sha256', $path) !== $sha) { throw new RuntimeException('M52 physical target changed.'); } }
foreach ([$before['visual_hashes'], $before['frozen_hashes']] as $hashes) {
    foreach ($hashes as $path => $sha) { if (hash_file('sha256', $root.'/'.$path) !== $sha) { throw new RuntimeException('M52 design/frozen source changed.'); } }
}
$sync = $load(App\Services\M52ContentPatch::SYNC_RECEIPT);
foreach ($sync['files'] as $record) {
    if (hash_file('sha256', $root.'/'.$record['path']) !== $record['root_after_sha256']
        || hash_file('sha256', App\Services\M52ContentPatch::SOURCE_WORKTREE.'/'.$record['path']) !== $record['worktree_sha256']) {
        throw new RuntimeException('M52 source-sync changed.');
    }
}
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $runtime = App\Services\M52LocalTargetGuard::publicIdentity($db, $root.'/public');
    $targets = []; $ids = []; $nonUk = 0; $continuing = 0;
    foreach ($plan['pages'] as $owner => $state) {
        $page = (array) $db->table('pages')->where('id', $state['page']['id'])->sole();
        $oldPage = $state['page']; $i = array_search($owner, App\Support\M52PassiveBasicsPackage::OWNERS, true);
        $oldPage['text'] = $patch['targets'][$i]['after']['page']['subtitle_text'];
        if ($page !== $oldPage) { throw new RuntimeException('M52 Page metadata/subtitle differs.'); }
        foreach ($state['blocks'] as $old) {
            $actual = (array) $db->table('text_blocks')->where('id', $old['id'])->sole();
            if ($old['locale'] === 'uk') {
                $old['body'] = $old['sort_order'] === 0 ? $patch['targets'][$i]['after']['page']['subtitle_html']
                    : $patch['targets'][$i]['after']['page']['blocks'][$old['sort_order'] - 1]['body'];
                $ids[] = $old['id']; ++$continuing;
            } else { ++$nonUk; }
            if ($old !== $actual) { throw new RuntimeException('M52 continuing/EN/PL row differs.'); }
        }
    }
    foreach ($plan['inserts'] as $insert) {
        $actual = (array) $db->table('text_blocks')->where('uuid', $insert['fields']['uuid'])->sole();
        foreach ($insert['fields'] as $field => $value) { if ($actual[$field] !== $value) { throw new RuntimeException('M52 appended practice differs.'); } }
        if (empty($actual['created_at']) || $actual['created_at'] !== $actual['updated_at']) { throw new RuntimeException('M52 new practice timestamp differs.'); }
        $ids[] = $actual['id']; $targets[] = ['owner' => $insert['seeder'], 'practice_id' => $actual['id'], 'practice_uuid' => $actual['uuid']];
    }
    $service = new App\Services\M52ContentPatch($db, $root.'/database', $dir);
    $fingerprint = new ReflectionMethod($service, 'protectedFingerprints');
    $protected = $fingerprint->invoke($service, $ids);
    if ($protected !== $plan['protected']) { throw new RuntimeException('M52 any protected table/row/progress changed.'); }
} finally { $db->rollBack(); }
$receipt = ['checked_at' => gmdate('c'), 'runtime' => $runtime, 'plan_sha256' => $plan['sha256'],
    'continuing_uk_rows' => $continuing, 'unchanged_en_pl_rows' => $nonUk, 'new_practices' => $targets,
    'protected_tables' => count($protected), 'all_protected_exact' => true,
    'visual_files_unchanged' => count($before['visual_hashes']), 'frozen_files_unchanged' => count($before['frozen_hashes']),
    'source_sync_unchanged' => true, 'db_writes' => 0, 'browser_acceptance' => 'separate evidence'];
$bytes = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
$file = $dir.'/final-readonly-v1.json'; if (is_link($file) || file_exists($file)) { throw new RuntimeException('M52 audit receipt exists; no overwrite.'); }
$handle = fopen($file, 'x'); if (!$handle) { throw new RuntimeException('M52 audit receipt unavailable.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M52 audit receipt incomplete.'); } } finally { fclose($handle); }
echo $bytes;
