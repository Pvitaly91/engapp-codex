<?php

/** M53 exact consumer profile of the immutable M52 read-only procedure. */
require __DIR__.'/frozen-readonly-profile.php';

$context = [];
if (($argv[1] ?? '') === 'audit') {
    if (count($argv) !== 7 || preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $argv[5] ?? '') !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $argv[6] ?? '') !== 1) {
        throw new RuntimeException('M53 audit additionally requires a reviewed final-readonly basename and raw SHA-256.');
    }
    $context = ['final_file' => $argv[5], 'final_sha256' => $argv[6]];
    $argv = array_slice($argv, 0, 5); // Validate M53-only args first; retain inherited CLI contract.
}
$oldBaselineLoop = <<<'PHP'
foreach ($workingBefore['sql_before'] as $table => $expected) {
    // Only page subtitles/text block learning data may change during scoped M53 apply.
    if ($mode === 'audit' && in_array($table, ['pages', 'text_blocks'], true)) { continue; }
    if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
        throw new RuntimeException('M53 consumer capture found SQL drift from working BEFORE: '.$table);
    }
}
PHP;
$reviewedBaselineLoop = <<<'PHP'
if ($mode === 'audit') {
    $finalName = $profileContext['final_file'] ?? '';
    $finalSha = $profileContext['final_sha256'] ?? '';
    if (preg_match('/^final-readonly-v[1-9][0-9]*\.json$/D', $finalName) !== 1
        || preg_match('/^[a-f0-9]{64}$/D', $finalSha) !== 1
        || !is_file($dir.'/'.$finalName) || is_link($dir.'/'.$finalName)
        || !hash_equals($finalSha, hash_file('sha256', $dir.'/'.$finalName))) {
        throw new RuntimeException('M53 reviewed independent final receipt is missing, linked or changed.');
    }
    $final = json_decode(file_get_contents($dir.'/'.$finalName), true, flags: JSON_THROW_ON_ERROR);
    if (($final['independent_before_sha256'] ?? null) !== $workingBeforeSha
        || ($final['question_plan_sha256'] ?? null) !== App\Services\M53ContentPatch::QUESTION_PLAN_SHA
        || ($final['runtime'] ?? null) !== ($workingBefore['identity'] ?? null)
        || ($final['all_old_rows_and_timestamps_exact'] ?? null) !== true
        || ($final['independent_before_projection_exact'] ?? null) !== true
        || ($final['reviewed_protected_projection_exact'] ?? null) !== true
        || ($final['normalized_before'] ?? null) !== $workingBefore['sql_before']
        || ($final['sql_after'] ?? null) !== $allTablesBeforeHttp
        || ($final['db_writes'] ?? null) !== 0 || ($final['http_requests'] ?? null) !== 0) {
        throw new RuntimeException('M53 every current SQL table must equal the reviewed final AFTER with exact independent BEFORE reconstruction.');
    }
} else {
    foreach ($workingBefore['sql_before'] as $table => $expected) {
        if (($allTablesBeforeHttp[$table] ?? null) !== $expected) {
            throw new RuntimeException('M53 consumer capture found SQL drift from working BEFORE: '.$table);
        }
    }
}
PHP;
runFrozenReadOnlyProfile('m52-old-consumers.php', '1894851d356452bf37f858cf4868915fdf23123b62584b52404461547edb3775', [
    ['M52', 'M53', 43], ['m52', 'm53', 4],
    ['passive-voice-formation-rules-theory-links.json', 'passive-voice-causative-theory-links.json', 1],
    ['passive-voice-negatives-questions-theory-links.json', 'passive-voice-get-passive-theory-links.json', 1],
    ['passive-voice-modal-verbs-theory-links.json', 'passive-voice-impersonal-passive-theory-links.json', 1],
    ['PassiveVoiceFormationRulesTheorySeeder', 'PassiveVoiceCausativeTheorySeeder', 1],
    ['PassiveVoiceNegativesQuestionsTheorySeeder', 'PassiveVoiceGetPassiveTheorySeeder', 1],
    ['PassiveVoiceModalVerbsTheorySeeder', 'PassiveVoiceImpersonalPassiveTheorySeeder', 1],
    ['2ca4aa326eb5de3bafdb714967a5172f0f30c5614266fe76f576550f3c990bff',
        '62612fa65e7ad3da6540de2e023b249727fc56db8a14033a92ef53e878785a33', 1],
    [$oldBaselineLoop, $reviewedBaselineLoop, 1],
    ["\$result['all_tables_before_http'] = \$allTablesBeforeHttp;",
        "if (\$mode === 'audit') { \$result['authorized_apply_final_receipt'] = \$profileContext; }\n\$result['all_tables_before_http'] = \$allTablesBeforeHttp;", 1],
], $context);
