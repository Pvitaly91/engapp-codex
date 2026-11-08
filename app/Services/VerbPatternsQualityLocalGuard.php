<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Nine exact writable local Verb Patterns lessons; all historical content remains protected. */
final class VerbPatternsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Verb Patterns quality';
    protected const PRIVATE_DIRECTORY='verb-patterns-quality';
    protected const ENDPOINT='verb-patterns-quality-';
    public const BINDINGS=[
        'gerund'=>['page_id'=>195,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundTheorySeeder','path'=>'/theory/verb-patterns/gerund'],
        'to-infinitive'=>['page_id'=>198,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsToInfinitiveTheorySeeder','path'=>'/theory/verb-patterns/to-infinitive'],
        'bare-infinitive'=>['page_id'=>193,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBareInfinitiveTheorySeeder','path'=>'/theory/verb-patterns/bare-infinitive'],
        'gerund-vs-infinitive'=>['page_id'=>196,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsGerundVsInfinitiveTheorySeeder','path'=>'/theory/verb-patterns/gerund-vs-infinitive'],
        'verbs-plus-gerund'=>['page_id'=>199,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusGerundTheorySeeder','path'=>'/theory/verb-patterns/verbs-plus-gerund'],
        'verbs-plus-infinitive'=>['page_id'=>200,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsVerbsPlusInfinitiveTheorySeeder','path'=>'/theory/verb-patterns/verbs-plus-infinitive'],
        'stop-remember-forget-try-regret'=>['page_id'=>197,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsStopRememberForgetTryRegretTheorySeeder','path'=>'/theory/verb-patterns/stop-remember-forget-try-regret'],
        'be-used-to-get-used-to-used-to'=>['page_id'=>194,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder','path'=>'/theory/verb-patterns/be-used-to-get-used-to-used-to'],
        'advanced-gerund-infinitive-patterns'=>['page_id'=>286,'category_id'=>33,'identity'=>'Database\\Seeders\\Page_V3\\VerbPatterns\\AdvancedGerundInfinitivePatternsTheorySeeder','path'=>'/theory/verb-patterns/advanced-gerund-infinitive-patterns'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-verb-patterns-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
