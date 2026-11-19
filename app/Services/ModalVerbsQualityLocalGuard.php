<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exact ten local modal lessons. This supplies no remote or course authority. */
final class ModalVerbsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Modal Verbs quality';
    protected const PRIVATE_DIRECTORY = 'modal-verbs-quality';
    protected const ENDPOINT = 'modal-verbs-quality-';

    public const BINDINGS = [
        'can-could'=>['page_id'=>72,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder','path'=>'/theory/modal-verbs/can-could'],
        'may-might'=>['page_id'=>74,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMayMightTheorySeeder','path'=>'/theory/modal-verbs/may-might'],
        'must-have-to'=>['page_id'=>75,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsMustHaveToTheorySeeder','path'=>'/theory/modal-verbs/must-have-to'],
        'should-ought-to'=>['page_id'=>79,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsShouldOughtToTheorySeeder','path'=>'/theory/modal-verbs/should-ought-to'],
        'need-neednt-dont-have-to'=>['page_id'=>76,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsNeedNeedntDontHaveToTheorySeeder','path'=>'/theory/modal-verbs/need-neednt-dont-have-to'],
        'modals-of-deduction'=>['page_id'=>73,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsDeductionTheorySeeder','path'=>'/theory/modal-verbs/modals-of-deduction'],
        'past-modals'=>['page_id'=>77,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsPastModalsTheorySeeder','path'=>'/theory/modal-verbs/past-modals'],
        'requests-offers-suggestions'=>['page_id'=>78,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsRequestsOffersSuggestionsTheorySeeder','path'=>'/theory/modal-verbs/requests-offers-suggestions'],
        'modal-perfect-and-deduction'=>['page_id'=>303,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder','path'=>'/theory/modal-verbs/modal-perfect-and-deduction'],
        'subtle-modal-meanings'=>['page_id'=>320,'category_id'=>15,'identity'=>'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder','path'=>'/theory/modal-verbs/subtle-modal-meanings'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn(array $row):array=>[
            'identity'=>$row['identity'],'locale'=>'uk','path'=>$row['path'],
        ],self::BINDINGS));
    }

    protected function windowsEvidence(int $port): array
    {
        if($port!==3306){throw new RuntimeException('Exact local MySQL port required.');}
        $file=dirname(__DIR__,2).'/tools/diagnostics/inspect-modal-verbs-local-target.ps1';
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
