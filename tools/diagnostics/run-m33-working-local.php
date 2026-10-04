<?php

// Boot the unchanged working .loc application; use only this worktree's finite M33 command.
if (PHP_SAPI!=='cli'||PHP_OS_FAMILY!=='Windows'||($argv[1]??'')!=='content:patch-academic-english-m33') { exit(1); }
$working='D:/DEV/htdocs/gramlyze.loc'; $source=dirname(__DIR__,2);
require $working.'/vendor/autoload.php';
require_once $source.'/app/Support/M33AcademicEnglishPackage.php';
foreach (['M11LocalTargetGuard','M26ContentPatch','M33LocalTargetGuard','M33ContentPatch'] as $name) { require_once $source.'/app/Services/'.$name.'.php'; }
require_once $source.'/app/Console/Commands/PatchM33Content.php';
$app=require $working.'/bootstrap/app.php'; $kernel=$app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap();
if (strtolower(str_replace('\\','/',realpath($app->basePath())))!==strtolower($working)) { throw new RuntimeException('Working root mismatch.'); }
$app->useDatabasePath($source.'/database');
$kernel->registerCommand($app->make(App\Console\Commands\PatchM33Content::class));
$input=new Symfony\Component\Console\Input\ArgvInput;
$status=$kernel->handle($input,new Symfony\Component\Console\Output\ConsoleOutput); $kernel->terminate($input,$status); exit($status);
