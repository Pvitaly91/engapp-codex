<?php

use App\Console\Commands\PatchM26Content;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Boot the unchanged working web application while reading only M26 sources from this worktree.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-ppc-layers-m26') {
    fwrite(STDERR, "M26 adapter requires the working Windows CLI.\n");
    exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
require_once $source.'/app/Support/M26DetailPackage.php';
foreach (['M11LocalTargetGuard', 'M26LocalTargetGuard', 'M26ContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
require_once $source.'/app/Console/Commands/PatchM26Content.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
if (strtolower(str_replace('\\', '/', (string) realpath($app->basePath()))) !== strtolower($working)) {
    fwrite(STDERR, "M26 working application root mismatch.\n");
    exit(1);
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(PatchM26Content::class));
$input = new ArgvInput;
$status = $kernel->handle($input, new ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
