<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exactly three approved UK Some / Any leaves; no remote or separate-course authority. */
final class SomeAnyQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Some / Any quality';
    protected const PRIVATE_DIRECTORY = 'some-any-quality';
    protected const ENDPOINT = 'some-any-quality-';

    public const BINDINGS = [
        'people' => ['page_id'=>263,'category_id'=>50,'identity'=>'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPeopleTheorySeeder','path'=>'/theory/some-any/theory-some-any-people'],
        'places' => ['page_id'=>264,'category_id'=>50,'identity'=>'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPlacesTheorySeeder','path'=>'/theory/some-any/theory-some-any-places'],
        'things' => ['page_id'=>265,'category_id'=>50,'identity'=>'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyThingsTheorySeeder','path'=>'/theory/some-any/theory-some-any-things'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn(array $binding): array => [
            'identity'=>$binding['identity'],'locale'=>'uk','path'=>$binding['path'],
        ],self::BINDINGS));
    }

    protected function windowsEvidence(int $port): array
    {
        if ($port !== 3306) { throw new RuntimeException('Exact local MySQL port required.'); }
        $script=dirname(__DIR__,2).'/tools/diagnostics/inspect-some-any-local-target.ps1';
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
