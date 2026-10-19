<?php

// Boot the unchanged working app; only this fixed M12 command uses worktree sources.
if (PHP_SAPI !== 'cli' || PHP_OS_FAMILY !== 'Windows' || ($argv[1] ?? '') !== 'content:patch-inversion-clefts-m12') {
    fwrite(STDERR, "M12 adapter requires the working Windows CLI.\n"); exit(1);
}
$working = 'D:/DEV/htdocs/gramlyze.loc';
$source = dirname(__DIR__, 2);
require $working.'/vendor/autoload.php';
foreach (['M11LocalTargetGuard', 'M12LocalTargetGuard', 'LinkingWordsContentPatch', 'InversionCleftsContentPatch'] as $name) {
    require_once $source.'/app/Services/'.$name.'.php';
}
foreach (['PatchLinkingWordsContent', 'PatchInversionCleftsContent'] as $name) {
    require_once $source.'/app/Console/Commands/'.$name.'.php';
}
$app = require $working.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
if (strtolower(str_replace('\\', '/', (string) realpath($app->basePath()))) !== strtolower($working)) {
    fwrite(STDERR, "Working application root mismatch.\n"); exit(1);
}
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(App\Console\Commands\PatchInversionCleftsContent::class));
$input = new Symfony\Component\Console\Input\ArgvInput;
$status = $kernel->handle($input, new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate($input, $status);
exit($status);
