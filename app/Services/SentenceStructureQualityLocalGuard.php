<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Four exact writable local Sentence Structure lessons; all historical content remains protected. */
final class SentenceStructureQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Sentence Structure quality';
    protected const PRIVATE_DIRECTORY='sentence-structure-quality';
    protected const ENDPOINT='sentence-structure-quality-';
    public const BINDINGS=[
        'cleft-sentences-basics'=>['page_id'=>289,'category_id'=>53,'identity'=>'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesBasicsTheorySeeder','path'=>'/theory/sentence-structure/cleft-sentences-basics'],
        'cleft-sentences-emphasis'=>['page_id'=>297,'category_id'=>53,'identity'=>'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesEmphasisTheorySeeder','path'=>'/theory/sentence-structure/cleft-sentences-emphasis'],
        'ellipsis-substitution-and-reference'=>['page_id'=>311,'category_id'=>53,'identity'=>'Database\\Seeders\\Page_V3\\SentenceStructure\\EllipsisSubstitutionAndReferenceTheorySeeder','path'=>'/theory/sentence-structure/ellipsis-substitution-and-reference'],
        'complex-noun-phrases'=>['page_id'=>312,'category_id'=>53,'identity'=>'Database\\Seeders\\Page_V3\\SentenceStructure\\ComplexNounPhrasesTheorySeeder','path'=>'/theory/sentence-structure/complex-noun-phrases'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-sentence-structure-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
