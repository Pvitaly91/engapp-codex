<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact seventeen local clauses-linking-words lessons. Supplies no remote, course or startup authority. */
final class ClausesLinkingQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Clauses and linking words quality';
    protected const PRIVATE_DIRECTORY = 'clauses-linking-words-quality';
    protected const ENDPOINT = 'clauses-linking-words-quality-';

    public const BINDINGS = [
        'relative-clauses'=>['page_id'=>202,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder','path'=>'/theory/clauses-and-linking-words/relative-clauses'],
        'defining-relative-clauses'=>['page_id'=>31,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder','path'=>'/theory/clauses-and-linking-words/defining-relative-clauses'],
        'non-defining-relative-clauses'=>['page_id'=>33,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder','path'=>'/theory/clauses-and-linking-words/non-defining-relative-clauses'],
        'omitting-relative-pronouns'=>['page_id'=>34,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder','path'=>'/theory/clauses-and-linking-words/omitting-relative-pronouns'],
        'relative-clauses-with-whose-and-which'=>['page_id'=>284,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichTheorySeeder','path'=>'/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which'],
        'reason-because-since-as'=>['page_id'=>36,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsReasonTheorySeeder','path'=>'/theory/clauses-and-linking-words/reason-because-since-as'],
        'result-so-therefore-as-a-result'=>['page_id'=>37,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsResultTheorySeeder','path'=>'/theory/clauses-and-linking-words/result-so-therefore-as-a-result'],
        'contrast-although-though-however'=>['page_id'=>30,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsContrastTheorySeeder','path'=>'/theory/clauses-and-linking-words/contrast-although-though-however'],
        'purpose-to-in-order-to-so-that'=>['page_id'=>35,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsPurposeTheorySeeder','path'=>'/theory/clauses-and-linking-words/purpose-to-in-order-to-so-that'],
        'linking-words'=>['page_id'=>32,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsLinkingWordsTheorySeeder','path'=>'/theory/clauses-and-linking-words/linking-words'],
        'linking-words-reason-result-contrast'=>['page_id'=>290,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\LinkingWordsReasonResultContrastTheorySeeder','path'=>'/theory/clauses-and-linking-words/linking-words-reason-result-contrast'],
        'advanced-linking-devices'=>['page_id'=>302,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedLinkingDevicesTheorySeeder','path'=>'/theory/clauses-and-linking-words/advanced-linking-devices'],
        'concessive-and-contrastive-structures'=>['page_id'=>315,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ConcessiveAndContrastiveStructuresTheorySeeder','path'=>'/theory/clauses-and-linking-words/concessive-and-contrastive-structures'],
        'participle-clauses-basics'=>['page_id'=>285,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesBasicsTheorySeeder','path'=>'/theory/clauses-and-linking-words/participle-clauses-basics'],
        'participle-clauses'=>['page_id'=>299,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesTheorySeeder','path'=>'/theory/clauses-and-linking-words/participle-clauses'],
        'advanced-participle-and-absolute-clauses'=>['page_id'=>318,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedParticipleAndAbsoluteClausesTheorySeeder','path'=>'/theory/clauses-and-linking-words/advanced-participle-and-absolute-clauses'],
        'discourse-markers-and-cohesion'=>['page_id'=>310,'category_id'=>34,'identity'=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\DiscourseMarkersAndCohesionTheorySeeder','path'=>'/theory/clauses-and-linking-words/discourse-markers-and-cohesion'],
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
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-clauses-linking-words-local-target.ps1';
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
