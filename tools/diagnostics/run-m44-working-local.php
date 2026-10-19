<?php

// Explicit invocation only. Boot the real local app and use this worktree's exact M44 source package.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-authored-future-forms-m44') { exit(1); }
$working = 'D:/DEV/htdocs/gramlyze.loc'; $source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
require_once $source.'/app/Support/M44AuthoredFutureFormsPackage.php';
foreach (['M11LocalTargetGuard', 'M26ContentPatch', 'M44LocalTargetGuard', 'M44ContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
require_once $source.'/app/Console/Commands/PatchM44Content.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap();
if (realpath($app->basePath()) !== realpath($working) || realpath($app->environmentPath()) !== realpath($working)) {
    throw new RuntimeException('M44 working application/environment root mismatch.');
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(App\Console\Commands\PatchM44Content::class));
$input = new Symfony\Component\Console\Input\ArgvInput;
$status = $kernel->handle($input, new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate($input, $status); exit($status);
