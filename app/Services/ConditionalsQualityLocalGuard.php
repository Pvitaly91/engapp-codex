<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact thirteen local conditional lessons. Supplies no remote, course or startup authority. */
final class ConditionalsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Conditionals quality';
    protected const PRIVATE_DIRECTORY = 'conditionals-quality';
    protected const ENDPOINT = 'conditionals-quality-';

    public const BINDINGS = [
        'zero-conditional'=>['page_id'=>53,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsZeroTheorySeeder','path'=>'/theory/conditionals/zero-conditional'],
        'first-conditional'=>['page_id'=>44,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder','path'=>'/theory/conditionals/first-conditional'],
        'second-conditional'=>['page_id'=>48,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsSecondTheorySeeder','path'=>'/theory/conditionals/second-conditional'],
        'third-conditional'=>['page_id'=>49,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsThirdTheorySeeder','path'=>'/theory/conditionals/third-conditional'],
        'mixed-conditionals'=>['page_id'=>47,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsMixedTheorySeeder','path'=>'/theory/conditionals/mixed-conditionals'],
        'unless-provided-as-long-as'=>['page_id'=>50,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsUnlessProvidedAsLongAsTheorySeeder','path'=>'/theory/conditionals/unless-provided-as-long-as'],
        'in-case'=>['page_id'=>46,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsInCaseTheorySeeder','path'=>'/theory/conditionals/in-case'],
        'wish-if-only'=>['page_id'=>51,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWishIfOnlyTheorySeeder','path'=>'/theory/conditionals/wish-if-only'],
        'would-rather'=>['page_id'=>52,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWouldRatherTheorySeeder','path'=>'/theory/conditionals/would-rather'],
        'had-better'=>['page_id'=>45,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsHadBetterTheorySeeder','path'=>'/theory/conditionals/had-better'],
        'conditionals-with-unless-provided-as-long-as'=>['page_id'=>287,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder','path'=>'/theory/conditionals/conditionals-with-unless-provided-as-long-as'],
        'advanced-conditionals'=>['page_id'=>294,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\AdvancedConditionalsTheorySeeder','path'=>'/theory/conditionals/advanced-conditionals'],
        'conditional-alternatives-and-nuance'=>['page_id'=>309,'category_id'=>10,'identity'=>'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalAlternativesAndNuanceTheorySeeder','path'=>'/theory/conditionals/conditional-alternatives-and-nuance'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn(array $row):array=>[
            'identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path'],
        ],self::BINDINGS));
    }

    /** User-authorized task-only fallback for the unreliable Windows TCP state field. */
    protected function windowsEvidence(int $port): array
    {
        if($port!==3306){throw new RuntimeException('Exact local MySQL port required.');}
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-conditionals-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive',
            '-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful()){throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');}
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect'){
            throw new RuntimeException('Task-specific TCP accept evidence missing.');
        }
        return $evidence;
    }
}
