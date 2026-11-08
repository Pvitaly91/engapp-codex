<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Two exact writable local Articles and quantifiers lessons; all historical content remains protected. */
final class ArticlesQuantifiersQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Articles and quantifiers quality';
    protected const PRIVATE_DIRECTORY='articles-and-quantifiers-quality';
    protected const ENDPOINT='articles-and-quantifiers-quality-';
    public const BINDINGS=[
        'advanced-article-and-quantifier-nuance'=>['page_id'=>306,'category_id'=>57,'identity'=>'Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\AdvancedArticleAndQuantifierNuanceTheorySeeder','path'=>'/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance'],
        'precision-with-articles-and-determiners'=>['page_id'=>317,'category_id'=>57,'identity'=>'Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\PrecisionWithArticlesAndDeterminersTheorySeeder','path'=>'/theory/articles-and-quantifiers/precision-with-articles-and-determiners'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-articles-and-quantifiers-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
