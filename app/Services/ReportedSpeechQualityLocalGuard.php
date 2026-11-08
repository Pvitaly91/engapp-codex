<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact seven local reported-speech lessons. Supplies no remote, course or startup authority. */
final class ReportedSpeechQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Reported speech quality';
    protected const PRIVATE_DIRECTORY = 'reported-speech-quality';
    protected const ENDPOINT = 'reported-speech-quality-';

    public const BINDINGS = [
        'reported-statements'=>['page_id'=>3,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechStatementsTheorySeeder','path'=>'/theory/reported-speech/reported-statements'],
        'reported-questions'=>['page_id'=>143,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechQuestionsTheorySeeder','path'=>'/theory/reported-speech/reported-questions'],
        'reported-commands-and-requests'=>['page_id'=>142,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCommandsRequestsTheorySeeder','path'=>'/theory/reported-speech/reported-commands-and-requests'],
        'backshift-of-tenses'=>['page_id'=>141,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder','path'=>'/theory/reported-speech/backshift-of-tenses'],
        'time-and-place-changes'=>['page_id'=>145,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder','path'=>'/theory/reported-speech/time-and-place-changes'],
        'say-tell-ask'=>['page_id'=>144,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder','path'=>'/theory/reported-speech/say-tell-ask'],
        'advanced-reported-speech'=>['page_id'=>283,'category_id'=>3,'identity'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder','path'=>'/theory/reported-speech/advanced-reported-speech'],
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
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-reported-speech-local-target.ps1';
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
