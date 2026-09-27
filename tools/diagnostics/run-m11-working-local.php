<?php

// CLI adapter for the M11 worktree: boot the unchanged working application,
// then use this checkout's M11 sources/command. No environment/config override.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-linking-words-m11') {
    fwrite(STDERR, "M11 adapter requires the working Windows CLI.\n"); exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
foreach (['M11LocalTargetGuard', 'LinkingWordsContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
require_once $source.'/app/Console/Commands/PatchLinkingWordsContent.php';
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
if (strtolower(str_replace('\\', '/', (string) realpath($app->basePath()))) !== strtolower($working)) {
    fwrite(STDERR, "Working application root mismatch.\n"); exit(1);
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(App\Console\Commands\PatchLinkingWordsContent::class));
$status = $kernel->handle(new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate(new Symfony\Component\Console\Input\ArgvInput, $status);
exit($status);
