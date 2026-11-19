<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Six exact writable local Formal English lessons; all historical content remains protected. */
final class FormalEnglishQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='Formal English quality';
    protected const PRIVATE_DIRECTORY='formal-english-quality';
    protected const ENDPOINT='formal-english-quality-';
    public const BINDINGS=[
        'formal-register-and-nominalisation-basics'=>['page_id'=>291,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\FormalRegisterAndNominalisationBasicsTheorySeeder','path'=>'/theory/formal-english/formal-register-and-nominalisation-basics'],
        'nominalisation-formal-register'=>['page_id'=>301,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalisationFormalRegisterTheorySeeder','path'=>'/theory/formal-english/nominalisation-formal-register'],
        'register-tone-and-paraphrase'=>['page_id'=>307,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\RegisterToneAndParaphraseTheorySeeder','path'=>'/theory/formal-english/register-tone-and-paraphrase'],
        'paraphrase-and-reformulation'=>['page_id'=>322,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\ParaphraseAndReformulationTheorySeeder','path'=>'/theory/formal-english/paraphrase-and-reformulation'],
        'subjunctive-and-formal-structures'=>['page_id'=>304,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder','path'=>'/theory/formal-english/subjunctive-and-formal-structures'],
        'nominal-style-and-information-density'=>['page_id'=>321,'category_id'=>55,'identity'=>'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder','path'=>'/theory/formal-english/nominal-style-and-information-density'],
    ];
    public static function scope():array
    {
        return array_values(array_map(static fn(array $row):array=>['identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path']],self::BINDINGS));
    }
    /** User-approved task-only corroboration; shared server guards/configuration are unchanged. */
    protected function windowsEvidence(int $port):array
    {
        if($port!==3306)throw new RuntimeException('Exact local MySQL port required.');
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-formal-english-local-target.ps1';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command','& {'.file_get_contents($file).'} -DatabasePort 3306']);
        $process->setTimeout(20);$process->run();
        if(!$process->isSuccessful())throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.');
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if(($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect')throw new RuntimeException('Task-specific TCP accept evidence missing.');
        return $evidence;
    }
}
