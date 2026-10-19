<?php

// Explicit invocation only: the real local app, with this worktree's finite M46 package.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows'
    || ($argv[1] ?? '') !== 'content:patch-conditionals-m46') { exit(1); }

$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
foreach (['TextBlock/TextBlockUuidGenerator', 'M26DetailPackage', 'M46ConditionalsPackage'] as $name) {
    require_once $source.'/app/Support/'.$name.'.php';
}
foreach (['PronounContentRepair', 'M11LocalTargetGuard', 'M26ContentPatch', 'M46LocalTargetGuard', 'M46ContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
require_once $source.'/app/Console/Commands/PatchM46Content.php';

$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
if (realpath($app->basePath()) !== realpath($working)
    || realpath($app->environmentPath()) !== realpath($working)) {
    throw new RuntimeException('M46 working application/environment root mismatch.');
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(App\Console\Commands\PatchM46Content::class));
$input = new Symfony\Component\Console\Input\ArgvInput;
$status = $kernel->handle($input, new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
