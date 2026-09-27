<?php

use App\Console\Commands\PatchArgumentationCohesionContent;
use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Boot the unchanged working app; only this fixed M18 command uses worktree sources.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-argumentation-cohesion-m18') {
    fwrite(STDERR, "M18 adapter requires the working Windows CLI.\n");
    exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
foreach (['M11LocalTargetGuard', 'M18LocalTargetGuard', 'LinkingWordsContentPatch', 'ArgumentationCohesionContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
foreach (['PatchLinkingWordsContent', 'PatchArgumentationCohesionContent'] as $name) {
    require_once $source.'/app/Console/Commands/'.$name.'.php';
}
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
if (strtolower(str_replace('\\', '/', (string) realpath($app->basePath()))) !== strtolower($working)) {
    fwrite(STDERR, "Working application root mismatch.\n");
    exit(1);
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(PatchArgumentationCohesionContent::class));
$input = new ArgvInput;
$status = $kernel->handle($input, new ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
