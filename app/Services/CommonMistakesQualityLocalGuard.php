<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Five writable local lessons; the already corrected Word Order page is audit-only. */
final class CommonMistakesQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Common Mistakes quality';
    protected const PRIVATE_DIRECTORY='common-mistakes-quality';
    protected const ENDPOINT='common-mistakes-quality-';
    public const BINDINGS=[
        'articles-common-mistakes'=>['page_id'=>38,'category_id'=>9,'identity'=>'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesArticlesTheorySeeder','path'=>'/theory/common-mistakes/articles-common-mistakes'],
        'present-perfect-common-mistakes'=>['page_id'=>42,'category_id'=>9,'identity'=>'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPresentPerfectTheorySeeder','path'=>'/theory/common-mistakes/present-perfect-common-mistakes'],
        'countable-vs-uncountable-nouns-common-mistakes'=>['page_id'=>40,'category_id'=>9,'identity'=>'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesCountableUncountableTheorySeeder','path'=>'/theory/common-mistakes/countable-vs-uncountable-nouns-common-mistakes'],
        'prepositions-common-mistakes'=>['page_id'=>41,'category_id'=>9,'identity'=>'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesPrepositionsTheorySeeder','path'=>'/theory/common-mistakes/prepositions-common-mistakes'],
        'b1-b2-grammar-traps'=>['page_id'=>39,'category_id'=>9,'identity'=>'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesB1B2GrammarTrapsTheorySeeder','path'=>'/theory/common-mistakes/b1-b2-grammar-traps'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-authorized task-only alternative to unreliable Windows TCP-state output. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-common-mistakes-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
