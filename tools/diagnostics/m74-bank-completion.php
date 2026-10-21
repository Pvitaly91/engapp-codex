<?php

/** Explicit finite bank inventory/preview/apply/final-audit CLI; no startup or route hooks. */
$failure = static function (Throwable $error): void {
    fwrite(STDERR, $error instanceof RuntimeException && !$error instanceof PDOException
        && !$error instanceof Illuminate\Database\QueryException
        ? 'M74 bank completion: '.$error->getMessage()."\n"
        : "M74 bank completion incomplete; no successful receipt. Inspect the explicit mode and transactional evidence.\n");
    exit(1);
};
set_exception_handler($failure);
$mode = $argv[1] ?? ''; $subject = $argv[2] ?? ''; $name = $mode === 'apply' ? ($argv[4] ?? '') : $subject;
$safeName = static fn (string $value, string $prefix): bool => preg_match('/^'.preg_quote($prefix, '/').'-v[1-9][0-9]*\.json$/D', $value) === 1;
$proofName = match ($mode) { 'preview', 'final-audit' => $argv[3] ?? '', 'apply' => $argv[5] ?? '', default => null };
$valid = match ($mode) {
    'inventory' => count($argv) === 3 && $name === 'bank-completion-inventory-v1.json',
    'evidence' => count($argv) === 3 && $name === 'bank-completion-question-plan-v1.json',
    'preview' => count($argv) === 4 && $safeName($name, 'bank-preview'),
    'apply' => count($argv) === 6 && $safeName($subject, 'bank-preview') && $safeName($argv[3] ?? '', 'bank-backup') && $safeName($name, 'bank-apply'),
    'final-audit' => count($argv) === 4 && $safeName($name, 'bank-final-readonly'),
    default => false,
};
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !$valid
    || ($proofName !== null && preg_match('/^local-proof-[a-f0-9]{32}\.json$/D', $proofName) !== 1)) {
    throw new RuntimeException('Exact finite mode/output basenames and fresh nonce-proof basename are required.');
}
$root = 'D:/DEV/htdocs/gramlyze.loc'; $dir = $root.'/storage/app/seo-m74-local';
$normal = static function (string $path): string {
    $real = realpath($path);
    if ($real === false) { throw new RuntimeException('Required actual local path is missing.'); }
    return rtrim(strtolower(str_replace('\\', '/', $real)), '/');
};
if ($normal(dirname(__DIR__, 2)) !== $normal($root) || !is_dir($dir) || is_link($dir)
    || $normal($dir) !== $normal($root).'/storage/app/seo-m74-local'
    || file_exists($dir.'/'.$name) || is_link($dir.'/'.$name)) {
    throw new RuntimeException('Actual ROOT/private output differs or exclusive output already exists.');
}
$hash = static function (string $path): string {
    if (!is_file($path) || is_link($path)) { throw new RuntimeException('Pinned input is missing or linked.'); }
    $sha = hash_file('sha256', $path);
    if (!is_string($sha)) { throw new RuntimeException('Pinned input cannot be hashed.'); }
    return $sha;
};
$write = static function (array $value) use ($dir, $name, $hash): string {
    $raw = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
    $handle = fopen($dir.'/'.$name, 'x');
    if ($handle === false) { throw new RuntimeException('Exclusive private output could not be opened.'); }
    try {
        if (fwrite($handle, $raw) !== strlen($raw) || !fflush($handle)) { throw new RuntimeException('Exclusive private write is incomplete.'); }
    } finally { fclose($handle); }
    if (!hash_equals(hash('sha256', $raw), $hash($dir.'/'.$name))) { throw new RuntimeException('Exclusive private bytes could not be verified.'); }
    return $hash($dir.'/'.$name);
};
if ($mode === 'evidence') {
    require $root.'/vendor/autoload.php';
    $inventoryPath = $dir.'/'.App\Services\M74BankCompletionPatch::INVENTORY_FILE;
    $inventorySha = App\Services\M74BankCompletionPatch::INVENTORY_SHA;
    if (!hash_equals($inventorySha, $hash($inventoryPath))) { throw new RuntimeException('Frozen private allocation inventory differs.'); }
    $inventory = json_decode(file_get_contents($inventoryPath), true, flags: JSON_THROW_ON_ERROR);
    $source = App\Support\M74QuestionSupplement::load($root);
    $projection = App\Support\M74QuestionSupplement::project($source, $inventory['binding'], $inventory['option_matches']);
    $expected = App\Support\M51QuestionSupplement::materialize($projection, $inventory['ids'], $inventory['row_timestamp']);
    if ($projection !== $inventory['projection'] || $expected !== $inventory['materialized']
        || $inventory['source_sha256'] !== App\Support\M74QuestionSupplement::SOURCE_SHA
        || $inventory['baseline_sha256'] !== App\Services\M74BankCompletionPatch::BASELINE_SHA
        || $inventory['db_writes'] !== 0 || $inventory['http_requests'] !== 0
        || array_map('count', $expected) !== ['questions' => 1, 'question_options' => 0, 'question_answers' => 4,
            'question_option_question' => 8, 'question_theory_text_blocks' => 1]) {
        throw new RuntimeException('Pure materialization differs from frozen allocation inventory.');
    }
    $evidence = ['schema_version' => 1, 'kind' => 'M74_bank_completion_question_plan', 'inventory_sha256' => $inventorySha,
        'source_sha256' => App\Support\M74QuestionSupplement::SOURCE_SHA,
        'baseline_sha256' => App\Services\M74BankCompletionPatch::BASELINE_SHA,
        'projection' => $projection, 'materialized' => $expected, 'ids' => $inventory['ids'],
        'row_timestamp' => $inventory['row_timestamp'], 'db_writes' => 0, 'http_requests' => 0];
    echo json_encode(['result' => 'PASS', 'output' => $name, 'sha256' => $write($evidence),
        'family_counts' => array_map('count', $expected), 'db_connections' => 0, 'db_writes' => 0, 'http_requests' => 0],
        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$finalName = 'final-readonly-v2.json';
$finalSha = '754bceb43a9b72c31a0efc540cdd6639716e30916a43aa6682f24a70043c5bc9';
if (!hash_equals($finalSha, $hash($dir.'/'.$finalName))) {
    throw new RuntimeException('Independent post-browser final receipt bytes differ.');
}
$final = json_decode(file_get_contents($dir.'/'.$finalName), true, flags: JSON_THROW_ON_ERROR);
if (($final['independent_before_projection_exact'] ?? null) !== true
    || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
    || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0
    || count($final['sql_after'] ?? []) !== 46) {
    throw new RuntimeException('Independent final receipt does not protect all 46 tables.');
}
require $root.'/vendor/autoload.php'; $app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler($failure);
use App\Services\M74BankCompletionPatch as Patch;
use App\Services\M74LocalTargetGuard as Guard;
use App\Support\M74QuestionSupplement as Supplement;
if ($normal(base_path()) !== $normal($root) || $normal(app()->environmentPath()) !== $normal($root)
    || app()->getLocale() !== 'uk') {
    throw new RuntimeException('Actual application/environment directory or locale differs.');
}
$beforePath = $dir.'/before-v1.json'; $beforeSha = '9e7abb860af2a61bfb10f61047f7ffaad055e20368edfc24c9ed095301516fdc';
if (!hash_equals($beforeSha, $hash($beforePath))) { throw new RuntimeException('Independent original BEFORE bytes differ.'); }
$before = json_decode(file_get_contents($beforePath), true, flags: JSON_THROW_ON_ERROR);
$inputs = [__FILE__ => $hash(__FILE__), $beforePath => $beforeSha, $dir.'/'.$finalName => $finalSha,
    $dir.'/'.Patch::BASELINE_FILE => Patch::BASELINE_SHA, $root.'/'.Patch::PREVIOUS_PATCH => Patch::PREVIOUS_PATCH_SHA,
    $root.'/'.Supplement::SOURCE => Supplement::SOURCE_SHA];
foreach ([Patch::class, Supplement::class, App\Support\M51QuestionSupplement::class, Guard::class] as $class) {
    $path = (new ReflectionClass($class))->getFileName(); $inputs[$path] = $hash($path);
}
foreach ($before['physical_hashes'] as $path => $sha) {
    if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('Independently captured physical target bytes changed.'); }
    $inputs[$path] = $sha;
}
foreach ($inputs as $path => $sha) {
    if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('Exact inventory source/evidence input changed.'); }
}
$db = Illuminate\Support\Facades\DB::connection(); Guard::assertConfiguredDatabase($db);
$guard = new Guard; $physical = $guard->physicalEvidence();
$server = (array) $db->selectOne('SELECT DATABASE() AS db, @@port AS port, @@pid_file AS pid_file, @@datadir AS datadir, @@version_compile_os AS os');
foreach (['datadir', 'pid_file'] as $key) {
    if (!is_string($server[$key] ?? null) || preg_match('~^[A-Za-z]:/~D', str_replace('\\', '/', $server[$key])) !== 1) {
        throw new RuntimeException('Physical MySQL evidence is not an absolute local Windows path.');
    }
}
$dataPath = $normal($server['datadir']); $pidPath = $normal($server['pid_file']);
$pid = trim((string) file_get_contents($server['pid_file']));
if (($server['db'] ?? null) !== 'gr2' || (int) ($server['port'] ?? 0) !== 3306 || !str_starts_with($server['os'] ?? '', 'Win')
    || !ctype_digit($pid) || !str_starts_with($pidPath, $dataPath.'/') || str_starts_with($dataPath, '//')) {
    throw new RuntimeException('Actual loopback/local MySQL identity differs.');
}
$guard->assertEvidence($physical, $normal($root), (int) $pid, 3306);
if (Guard::publicIdentity($db, $root.'/public') !== $final['runtime'] || $final['runtime'] !== $before['identity']) {
    throw new RuntimeException('Actual safe runtime differs from original BEFORE and post-browser final receipt.');
}
if (in_array($mode, ['preview', 'apply', 'final-audit'], true)) {
    $proof = Guard::readFreshProof($proofName); $proofPath = $dir.'/'.$proofName; $proofSha = $hash($proofPath);
    $service = new Patch($db, $root.'/database', $dir, 'gramlyze.loc', $proofName);
    $assertInputs = static function () use ($inputs, $hash, $proofPath, $proofSha): void {
        foreach ($inputs + [$proofPath => $proofSha] as $path => $sha) {
            if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('Frozen physical/source/evidence input changed during explicit bank operation.'); }
        }
    };
    if ($mode === 'apply') {
        // Only the inherited writer owns SQL mutation, fresh guard, exclusive backup and transaction.
        $planPath = $dir.'/'.$subject; $backupPath = $dir.'/'.$argv[3];
        $planSha = $hash($planPath); $assertInputs();
        $result = $service->apply($planPath, $backupPath, 'gr2');
        $after = $service->plan();
        if ($after['state'] !== 'after' || !in_array($result['status'] ?? null, ['applied', 'no-op'], true)) {
            throw new RuntimeException('Inherited writer did not prove complete final bank state.');
        }
        $assertInputs();
        if (!hash_equals($planSha, $hash($planPath))) { throw new RuntimeException('Exact reviewed preview bytes changed during explicit operation.'); }
        $applied = $result['status'] === 'applied';
        $receipt = ['schema_version' => 1, 'kind' => 'M74_bank_completion_apply', 'created_at' => gmdate('c'),
            'status' => $result['status'], 'body_updates' => $applied ? 1 : 0,
            'question_family_inserted' => $applied ? $after['question_family_counts'] : array_fill_keys(array_keys($after['question_family_counts']), 0),
            'actual_inserted_rows' => $applied ? array_sum($after['question_family_counts']) : 0,
            'actual_db_row_writes' => $applied ? 1 + array_sum($after['question_family_counts']) : 0,
            'inherited_result' => $result, 'preview_file' => $subject, 'preview_sha256' => $planSha,
            'backup_file' => $argv[3], 'backup_sha256' => file_exists($backupPath) ? $hash($backupPath) : null,
            'proof_file' => $proofName, 'proof_sha256' => $proofSha, 'final_plan_sha256' => $after['sha256'],
            'all_46_original_tables_exact' => true, 'sources' => $after['sources'],
            'local_guard_http' => 'Unchanged fresh fixed-loopback proof guard; no content-page GETs in this helper.',
            'production_requests' => 0];
        $sha = $write($receipt);
        echo json_encode(['result' => 'PASS', 'status' => $result['status'], 'output' => $name, 'sha256' => $sha,
            'body_updates' => $receipt['body_updates'], 'actual_inserted_rows' => $receipt['actual_inserted_rows'],
            'actual_db_row_writes' => $receipt['actual_db_row_writes']], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
        exit(0);
    }
    $db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
    try {
        if ($mode === 'preview') {
            $plan = $service->savePlan($dir.'/'.$name);
            $assertInputs();
            $summary = ['result' => 'PASS', 'output' => $name, 'sha256' => $hash($dir.'/'.$name), 'state' => $plan['state'],
                'body_updates' => count($plan['updates']), 'question_family_counts' => $plan['question_family_counts'],
                'proposed_new_rows' => array_sum($plan['question_family_counts']), 'db_writes' => 0,
                'protected_tables' => count($plan['protected'])];
        } else {
            $plan = $service->plan();
            if ($plan['state'] !== 'after' || $plan['updates'] !== [] || $plan['inserts'] !== []) {
                throw new RuntimeException('Final audit requires exact fully-applied successor and no remaining update.');
            }
            $inventoryPath = $dir.'/'.Patch::INVENTORY_FILE;
            if (!hash_equals(Patch::INVENTORY_SHA, $hash($inventoryPath))) { throw new RuntimeException('Pinned bank BEFORE inventory changed.'); }
            $inventory = json_decode(file_get_contents($inventoryPath), true, flags: JSON_THROW_ON_ERROR);
            $old = json_decode(file_get_contents($root.'/'.Patch::PREVIOUS_PATCH), true, flags: JSON_THROW_ON_ERROR);
            $next = json_decode(file_get_contents($root.'/'.Patch::NEXT_PATCH), true, flags: JSON_THROW_ON_ERROR);
            $beforeBody = $old['targets'][0]['after']['page']['blocks'][6]['body'];
            $afterBody = $next['targets'][0]['after']['page']['blocks'][6]['body'];
            $sqlAfter = Patch::fingerprints($db);
            $normalized = Patch::fingerprints($db, $plan['question_rows'],
                ['id' => Patch::PRACTICE_ID, 'before' => $beforeBody, 'after' => $afterBody]);
            if ($normalized !== $inventory['sql_before'] || count($normalized) !== 46 || $plan['protected'] !== $normalized
                || array_sum($plan['question_family_counts']) !== 14) {
                throw new RuntimeException('Complete independent normalization is not exact 46-table bank BEFORE plus fourteen reviewed rows.');
            }
            $assertInputs();
            $receipt = ['schema_version' => 1, 'kind' => 'M74_bank_completion_final', 'created_at' => gmdate('c'),
                'inventory_file' => Patch::INVENTORY_FILE, 'inventory_sha256' => Patch::INVENTORY_SHA,
                'question_plan_file' => Patch::QUESTION_PLAN_FILE, 'question_plan_sha256' => Patch::QUESTION_PLAN_SHA,
                'core_post_browser_final_file' => $finalName, 'core_post_browser_final_sha256' => $finalSha,
                'runtime' => $final['runtime'], 'proof_file' => $proofName, 'proof_sha256' => $proofSha,
                'sql_before' => $inventory['sql_before'], 'sql_after' => $sqlAfter, 'normalized_before' => $normalized,
                'all_old_rows_and_timestamps_exact' => true, 'exact_one_bank_list_body_update' => true,
                'practice_id' => Patch::PRACTICE_ID, 'practice_uuid' => Supplement::PRACTICE_UUID,
                'before_body_sha256' => hash('sha256', $beforeBody), 'after_body_sha256' => hash('sha256', $afterBody),
                'new_question_rows' => $plan['question_rows'], 'question_family_counts' => $plan['question_family_counts'],
                'sources' => $plan['sources'], 'plan_sha256' => $plan['sha256'], 'db_writes' => 0,
                'local_guard_http' => 'Unchanged fresh fixed-loopback proof guard; no content-page GETs in this helper.',
                'production_requests' => 0];
            $summary = ['result' => 'PASS', 'output' => $name, 'sha256' => $write($receipt), 'protected_tables' => 46,
                'exact_inserted_rows' => 14, 'all_old_rows_and_timestamps_exact' => true, 'db_writes' => 0];
        }
    } finally { $db->rollBack(); }
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
    exit(0);
}
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $inventory = (new Patch($db, $root.'/database', $dir))->inventory();
    if ($inventory['sql_before'] !== $final['sql_after']) { throw new RuntimeException('Post-browser all-table SQL projection changed.'); }
    $inventory['post_browser_final_file'] = $finalName; $inventory['post_browser_final_sha256'] = $finalSha;
    $inventory['physical_target_verified'] = true;
    $inventory['input_hashes'] = $inputs;
} finally { $db->rollBack(); }
foreach ($inputs as $path => $sha) {
    if (!hash_equals($sha, $hash($path))) { throw new RuntimeException('An exact input changed during read-only inventory.'); }
}
echo json_encode(['result' => 'PASS', 'output' => $name, 'sha256' => $write($inventory),
    'protected_tables' => count($inventory['sql_before']), 'family_counts' => array_map('count', $inventory['materialized']),
    'proposed_ids' => $inventory['ids'], 'row_timestamp' => $inventory['row_timestamp'],
    'reused_options' => count($inventory['projection']['reused_options']), 'db_writes' => 0, 'http_requests' => 0],
    JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
