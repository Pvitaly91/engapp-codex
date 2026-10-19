<?php

use App\Console\Commands\PatchM26InteractivePractice;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Boot the actual, unchanged local environment; finite source package is read from this worktree.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-ppc-interactive-m26') {
    fwrite(STDERR, "M26 interactive adapter requires the working Windows CLI.\n");
    exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
require_once $source.'/app/Support/M26DetailPackage.php';
require_once $source.'/app/Support/M26InteractivePractice.php';
foreach (['M11LocalTargetGuard', 'M26LocalTargetGuard', 'M26InteractiveLocalTargetGuard',
    'M26ContentPatch', 'M26InteractivePracticePatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
require_once $source.'/app/Console/Commands/PatchM26InteractivePractice.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
if (strtolower(str_replace('\\', '/', (string) realpath($app->basePath()))) !== strtolower($working)) {
    fwrite(STDERR, "M26 interactive working application root mismatch.\n");
    exit(1);
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(PatchM26InteractivePractice::class));
$input = new ArgvInput;
$status = $kernel->handle($input, new ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
