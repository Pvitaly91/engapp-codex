<?php

// Mechanical finite source sync for the real .loc webroot, with exact conflict guards.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || !in_array($argv[1] ?? '', ['check', 'apply'], true)) { exit(1); }
$source = dirname(__DIR__, 2); $root = 'D:/DEV/htdocs/gramlyze.loc';
require $source.'/vendor/autoload.php';
$files = ['app/Support/LocalizedComposeText.php', 'app/Support/PastPerfectContinuousPracticeQuality.php',
    'app/Support/PpcComposePresentation.php',
    'database/content-patches/ppc-compose-presentation/builder.json',
    'database/content-patches/ppc-compose-presentation/mixed.json',
    'app/Support/PpcOrderedTheoryLinks.php',
    'app/Services/PpcQualityLocalTargetGuard.php', 'app/Support/Database/JsonTestSeeder.php', 'app/Support/Database/JsonTestLocalizationManager.php',
    'app/Http/Controllers/TestJsV2Controller.php', 'app/Http/Controllers/GrammarTestController.php', 'app/Services/TheoryCourseTestPoolService.php', 'app/Services/QuestionExportService.php', 'app/Services/QuestionImportService.php',
    'app/Support/SentenceReorderQuestionFactory.php', 'resources/views/components/sentence-reorder-question-js.blade.php',
    'app/Support/SavedTestJsState.php',
    'app/Services/TheoryPagePromptLinkedTestsService.php',
    'resources/views/components/saved-test-js-helpers.blade.php',
    'resources/views/test-modes/step-compose.blade.php',
    'resources/views/test-modes/card-easy.blade.php',
    'resources/views/test-modes/step-easy.blade.php',
    'resources/views/test-modes/card-expert.blade.php',
    'resources/views/test-modes/step-expert.blade.php',
    'resources/views/test-modes/theory-mixed.blade.php',
    'public/js/theory-mixed-test.js',
    'resources/views/components/authored-compose-manual-preview.blade.php',
    'resources/views/components/text-block-practice-questions.blade.php', 'resources/views/engram/theory/blocks-v3/practice-set.blade.php',
    'resources/views/theory/show.blade.php',
    'database/content-patches/ppc-practice-quality.v2.json', 'tools/diagnostics/inspect-m11-local-target.ps1'];
foreach (['uk', 'en', 'pl'] as $locale) { $files[] = 'resources/lang/'.$locale.'/frontend.php'; }
foreach (['Forms', 'Negatives', 'Questions', 'TimeExpressions'] as $suffix) {
    $theory = 'database/seeders/Page_V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$suffix.'TheorySeeder';
    $files = [...$files, $theory.'/definition.json', $theory.'/localizations/en.json', $theory.'/localizations/pl.json',
        'database/seeders/V3/Tenses/PastPerfectContinuous/PastPerfectContinuous'.$suffix.'AllLevelsV3Seeder/definition.json',
        'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuous'.$suffix.'AllLevelsLessonSeeder/definition.json'];
    $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $suffix));
    $files[] = 'database/seeders/V3/TheoryLinks/data/past-perfect-continuous-'.$kebab.'-theory-links.json';
}
$basics = 'database/seeders/V3/Polyglot/PolyglotPastPerfectContinuousBasicsB2LessonSeeder'; $files[] = $basics.'/definition.json';
foreach (['uk', 'en', 'pl'] as $locale) { $files[] = $basics.'/localizations/'.$locale.'.json'; }
$normal = fn ($s) => str_replace("\r\n", "\n", $s); $pending = []; $records = [];
foreach ($files as $path) {
    if (is_link($source.'/'.$path) || is_link($root.'/'.$path) || !is_file($source.'/'.$path)) { throw new RuntimeException('Unsafe/missing finite source: '.$path); }
    $new = file_get_contents($source.'/'.$path); $old = is_file($root.'/'.$path) ? file_get_contents($root.'/'.$path) : null;
    if ($old !== null && $normal($old) !== $normal($new)) {
        $accepted = false;
        foreach (['4644a1729d326b9a967d03c397998506f00d704b', '41820a2bebdf69004fa7209a2a38457f93efabbd'] as $sha) {
            $p = new Symfony\Component\Process\Process(['git', '-c', 'safe.directory='.str_replace('\\', '/', $source), 'show', $sha.':'.$path], $source);
            $p->run(); if ($p->isSuccessful() && $normal($p->getOutput()) === $normal($old)) { $accepted = true; break; }
        }
        // A later verified task fix may replace our own earlier finite sync,
        // but never an intervening user edit: require its exact recorded output hash.
        if (!$accepted) {
            foreach (glob($root.'/storage/app/ppc-quality-local/source-backup-*/manifest.json') ?: [] as $manifestPath) {
                if (is_link($manifestPath) || !preg_match('/source-backup-[a-f0-9]{16}\/manifest\.json$/D', str_replace('\\', '/', $manifestPath))) { continue; }
                foreach (json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR) as $record) {
                    if (($record['path'] ?? null) === $path && ($record['after_sha256'] ?? null) === hash('sha256', $old)) { $accepted = true; break 2; }
                }
            }
        }
        if (!$accepted) { throw new RuntimeException('Unrelated working edit, refusing overwrite: '.$path); }
    }
    $pending[$path] = [$old, $new]; $records[] = ['path' => $path, 'before_sha256' => $old === null ? null : hash('sha256', $old), 'after_sha256' => hash('sha256', $new)];
}
if ($argv[1] === 'check') { echo json_encode(['status' => 'checked-no-writes', 'files' => count($records), 'records' => $records], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"; exit(0); }
$dir = $root.'/storage/app/ppc-quality-local/source-backup-'.bin2hex(random_bytes(8));
if (!mkdir($dir, 0700)) { throw new RuntimeException('Exclusive source backup failed.'); }
foreach ($pending as $path => [$old]) {
    if ($old === null) { continue; } if (!is_dir(dirname($dir.'/'.$path))) { mkdir(dirname($dir.'/'.$path), 0700, true); }
    $f = fopen($dir.'/'.$path, 'x'); if (!$f || fwrite($f, $old) !== strlen($old) || !fflush($f)) { throw new RuntimeException('Incomplete backup.'); } fclose($f);
}
$f = fopen($dir.'/manifest.json', 'x'); fwrite($f, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); fflush($f); fclose($f);
foreach ($pending as $path => [$old, $new]) {
    if ((is_file($root.'/'.$path) ? file_get_contents($root.'/'.$path) : null) !== $old) { throw new RuntimeException('Concurrent working edit.'); }
}
foreach ($pending as $path => [$old, $new]) {
    if (!is_dir(dirname($root.'/'.$path))) { mkdir(dirname($root.'/'.$path), 0700, true); }
    if (file_put_contents($root.'/'.$path, $new, LOCK_EX) !== strlen($new)) { throw new RuntimeException('Source sync incomplete.'); }
}
echo json_encode(['status' => 'synced', 'files' => count($records), 'exclusive_backup' => $dir])."\n";
