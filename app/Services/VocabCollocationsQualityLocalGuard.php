<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** One exact writable local Vocabulary and collocations lesson; historical content remains protected. */
final class VocabCollocationsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Vocabulary and collocations quality';
    protected const PRIVATE_DIRECTORY='vocabulary-collocations-quality';
    protected const ENDPOINT='vocabulary-collocations-quality-';
    public const BINDINGS=[
        'advanced-collocation-and-lexical-choice'=>['page_id'=>316,'category_id'=>58,'identity'=>'Database\\Seeders\\Page_V3\\VocabularyAndCollocations\\AdvancedCollocationAndLexicalChoiceTheorySeeder','path'=>'/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-vocabulary-collocations-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}

