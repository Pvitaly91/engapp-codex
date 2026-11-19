<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Four exact writable local Academic English lessons; all historical content remains protected. */
final class AcademicEnglishQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Academic English quality';
    protected const PRIVATE_DIRECTORY='academic-english-quality';
    protected const ENDPOINT='academic-english-quality-';
    public const BINDINGS=[
        'hedging-and-cautious-language-basics'=>['page_id'=>293,'category_id'=>54,'identity'=>'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageBasicsTheorySeeder','path'=>'/theory/academic-english/hedging-and-cautious-language-basics'],
        'hedging-and-cautious-language'=>['page_id'=>298,'category_id'=>54,'identity'=>'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageTheorySeeder','path'=>'/theory/academic-english/hedging-and-cautious-language'],
        'stance-register-and-evaluation'=>['page_id'=>314,'category_id'=>54,'identity'=>'Database\\Seeders\\Page_V3\\AcademicEnglish\\StanceRegisterAndEvaluationTheorySeeder','path'=>'/theory/academic-english/stance-register-and-evaluation'],
        'argumentation-and-academic-tone'=>['page_id'=>323,'category_id'=>54,'identity'=>'Database\\Seeders\\Page_V3\\AcademicEnglish\\ArgumentationAndAcademicToneTheorySeeder','path'=>'/theory/academic-english/argumentation-and-academic-tone'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-academic-english-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
