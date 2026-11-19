<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Seven approved UK adjective/adverb leaves; no remote or course authority. */
final class AdjectivesAdverbsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Adjectives and Adverbs quality';
    protected const PRIVATE_DIRECTORY = 'adjectives-adverbs-quality';
    protected const ENDPOINT = 'adjectives-adverbs-quality-';

    public const BINDINGS = [
        'adverbs' => ['page_id'=>279,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesVsAdverbsTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/adjectives-vs-adverbs'],
        'degrees' => ['page_id'=>274,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesDegreesOfComparisonTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/theory-degrees-of-comparison'],
        'comparative' => ['page_id'=>273,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/comparative-vs-superlative'],
        'equality' => ['page_id'=>276,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/equality-comparison-asas-not-asas'],
        'order' => ['page_id'=>278,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesOrderOfAdjectivesTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/order-of-adjectives'],
        'eding' => ['page_id'=>275,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesEdIngAdjectivesTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives'],
        'intensifiers' => ['page_id'=>277,'category_id'=>52,'identity'=>'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesIntensifiersTheorySeeder','path'=>'/theory/prykmetniky-ta-pryslinknyky/intensifiers'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn(array $binding): array => [
            'identity'=>$binding['identity'],'locale'=>'uk','path'=>$binding['path'],
        ],self::BINDINGS));
    }

    /** This task's explicitly approved PID/root/live-loopback evidence only. */
    protected function windowsEvidence(int $port): array
    {
        if ($port !== 3306) { throw new RuntimeException('Exact local MySQL port required.'); }
        $script=dirname(__DIR__,2).'/tools/diagnostics/inspect-adjectives-adverbs-local-target.ps1';
        $commands='& {'.file_get_contents($script).'} -DatabasePort 3306';
        $process=new Process(['C:/Windows/System32/WindowsPowerShell/v1.0/powershell.exe','-NoProfile','-NonInteractive','-Command',$commands]);
        $process->setTimeout(20); $process->run();
        if (!$process->isSuccessful()) { throw new RuntimeException('Task-specific local endpoint/PID evidence unavailable.'); }
        $evidence=json_decode(trim($process->getOutput()),true,flags:JSON_THROW_ON_ERROR);
        if (($evidence['listener_evidence']??null)!=='bound-endpoint-pid-and-live-loopback-connect') {
            throw new RuntimeException('Task-specific TCP accept evidence missing.');
        }
        return $evidence;
    }
}
