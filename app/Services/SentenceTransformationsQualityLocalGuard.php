<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Seven writable local lessons; the accepted Word Order/Emphasis lesson is audit-only. */
final class SentenceTransformationsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Sentence Transformations quality';
    protected const PRIVATE_DIRECTORY='sentence-transformations-quality';
    protected const ENDPOINT='sentence-transformations-quality-';
    public const BINDINGS=[
        'tenses'=>['page_id'=>152,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsTensesTheorySeeder','path'=>'/theory/sentence-transformations/tenses'],
        'modals'=>['page_id'=>148,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsModalsTheorySeeder','path'=>'/theory/sentence-transformations/modals'],
        'passive'=>['page_id'=>149,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsPassiveTheorySeeder','path'=>'/theory/sentence-transformations/passive'],
        'reported-speech'=>['page_id'=>151,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsReportedSpeechTheorySeeder','path'=>'/theory/sentence-transformations/reported-speech'],
        'conditionals'=>['page_id'=>147,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsConditionalsTheorySeeder','path'=>'/theory/sentence-transformations/conditionals'],
        'relative-clauses-linking-words'=>['page_id'=>150,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder','path'=>'/theory/sentence-transformations/relative-clauses-linking-words'],
        'articles-quantifiers'=>['page_id'=>146,'category_id'=>23,'identity'=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsArticlesQuantifiersTheorySeeder','path'=>'/theory/sentence-transformations/articles-quantifiers'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-sentence-transformations-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
