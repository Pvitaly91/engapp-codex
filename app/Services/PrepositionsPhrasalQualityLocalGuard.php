<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact seven local lessons. No remote, course, startup or wider mutation authority. */
final class PrepositionsPhrasalQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Prepositions and phrasal verbs quality';
    protected const PRIVATE_DIRECTORY='prepositions-phrasal-verbs-quality';
    protected const ENDPOINT='prepositions-phrasal-verbs-quality-';
    public const BINDINGS=[
        'prepositions-of-time'=>['page_id'=>120,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/prepositions-of-time'],
        'prepositions-of-place'=>['page_id'=>119,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfPlaceTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/prepositions-of-place'],
        'prepositions-of-movement'=>['page_id'=>118,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfMovementTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/prepositions-of-movement'],
        'dependent-prepositions'=>['page_id'=>115,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\DependentPrepositionsTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/dependent-prepositions'],
        'phrasal-verbs-basics'=>['page_id'=>116,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsBasicsTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/phrasal-verbs-basics'],
        'separable-and-inseparable-phrasal-verbs'=>['page_id'=>117,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PhrasalVerbsSeparableInseparableTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/separable-and-inseparable-phrasal-verbs'],
        'common-phrasal-verbs-by-topic'=>['page_id'=>114,'category_id'=>19,'identity'=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\CommonPhrasalVerbsByTopicTheorySeeder','path'=>'/theory/prepositions-and-phrasal-verbs/common-phrasal-verbs-by-topic'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-authorized task-only fallback for unreliable Windows TCP state. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-prepositions-phrasal-verbs-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
