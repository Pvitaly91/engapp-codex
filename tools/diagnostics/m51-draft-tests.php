<?php

/** Pre-freeze test bootstrap: only three artifact SHA constants are replaced in a private class fixture. */
if (PHP_SAPI !== 'cli') { throw new RuntimeException('M51 isolated draft tests require CLI.'); }
$root = dirname(__DIR__, 2);
require $root.'/tests/bootstrap.php';
$runtime = Tests\Support\IsolatedTestEnvironment::prepare();
$package = $root.'/app/Support/M51ModalMeaningsPackage.php';
$bytes = file_get_contents($package); $originalHash = hash('sha256', $bytes);
$pins = ['MASTER' => 'docs/content/m51-modal-meanings.v1.0.0.json',
    'BEFORE' => 'database/content-patches/m51-modal-meanings-before.v1.json',
    'SOURCE' => 'database/content-patches/m51-modal-meanings.v1.json'];
foreach ($pins as $constant => $artifact) {
    $needle = "public const {$constant}_SHA = 'UNREVIEWED';";
    if (substr_count($bytes, $needle) !== 1) { throw new RuntimeException('M51 draft test bootstrap is no longer applicable.'); }
    $bytes = str_replace($needle, "public const {$constant}_SHA = '".hash_file('sha256', $root.'/'.$artifact)."';", $bytes);
}
$fixture = $runtime.'/app/M51ModalMeaningsPackage.draft.php';
$handle = fopen($fixture, 'x');
if (!$handle) { throw new RuntimeException('M51 draft class fixture collision.'); }
try { if (fwrite($handle, $bytes) !== strlen($bytes) || !fflush($handle)) { throw new RuntimeException('M51 draft fixture incomplete.'); } }
finally { fclose($handle); }
require $fixture;
$result = (new PHPUnit\TextUI\Application)->run(['phpunit', 'tests/Feature/M51ModalMeaningsPackageTest.php',
    'tests/Feature/M51QuestionSupplementTest.php', '--do-not-cache-result', '--colors=never',
    '--log-junit', $runtime.'/m51-draft.junit.xml']);
if (hash_file('sha256', $package) !== $originalHash) { throw new RuntimeException('M51 draft bootstrap modified source code.'); }
echo "\nDraft artifact pins used only in private fixture; working .env and MySQL not loaded.\n";
exit($result);
