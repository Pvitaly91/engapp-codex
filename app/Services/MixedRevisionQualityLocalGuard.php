<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Six exact writable local Mixed Revision lessons; all historical content remains protected. */
final class MixedRevisionQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Mixed Revision quality';
    protected const PRIVATE_DIRECTORY='mixed-revision-quality';
    protected const ENDPOINT='mixed-revision-quality-';
    public const BINDINGS=[
        'a1-mixed-revision'=>['page_id'=>325,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA1MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/a1-mixed-revision'],
        'a2-mixed-revision'=>['page_id'=>326,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarA2MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/a2-mixed-revision'],
        'b1-mixed-revision'=>['page_id'=>327,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/b1-mixed-revision'],
        'b2-mixed-revision'=>['page_id'=>328,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\B2MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/b2-mixed-revision'],
        'c1-mixed-revision'=>['page_id'=>329,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/c1-mixed-revision'],
        'c2-mixed-revision'=>['page_id'=>330,'category_id'=>59,'identity'=>'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder','path'=>'/theory/mixed-revision/c2-mixed-revision'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-mixed-revision-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
