<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Exactly 27 approved UK Passive Voice leaves; no remote or separate-course authority. */
final class PassiveVoiceQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Passive Voice quality';
    protected const PRIVATE_DIRECTORY = 'passive-voice-quality';
    protected const ENDPOINT = 'passive-voice-quality-';

    public const BINDINGS = [
        'formation' => ['page_id'=>1,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormationRulesTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-formation-rules'],
        'causative' => ['page_id'=>92,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCausativeTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-causative'],
        'mistakes' => ['page_id'=>93,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCommonMistakesTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-common-mistakes'],
        'style' => ['page_id'=>94,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceFormalityStyleTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-formality-style'],
        'get' => ['page_id'=>95,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceGetPassiveTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-get-passive'],
        'impersonal' => ['page_id'=>96,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceImpersonalPassiveTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-impersonal-passive'],
        'modals' => ['page_id'=>97,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-modal-verbs'],
        'negatives' => ['page_id'=>98,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceNegativesQuestionsTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-negatives-questions'],
        'phrasal' => ['page_id'=>99,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoicePhrasalVerbsTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-phrasal-verbs'],
        'restrictions' => ['page_id'=>100,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceRestrictionsTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-restrictions'],
        'two-object' => ['page_id'=>101,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceTwoObjectVerbsTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-two-object-verbs'],
        'gerund' => ['page_id'=>102,'category_id'=>17,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveGerundTheorySeeder','path'=>'/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund'],
        'infinitive' => ['page_id'=>103,'category_id'=>17,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\InfinitivesGerund\\PassiveVoicePassiveInfinitiveTheorySeeder','path'=>'/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive'],
        'agent' => ['page_id'=>104,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\SpecialCases\\PassiveVoiceByPhraseTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-by-phrase'],
        'future-continuous' => ['page_id'=>105,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureContinuousTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-continuous'],
        'future-perfect' => ['page_id'=>106,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFuturePerfectTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect'],
        'future-simple' => ['page_id'=>107,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple'],
        'past-continuous' => ['page_id'=>108,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastContinuousTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-continuous'],
        'past-perfect' => ['page_id'=>109,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastPerfectTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect'],
        'past-simple' => ['page_id'=>110,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-past-simple'],
        'present-continuous' => ['page_id'=>111,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentContinuousTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-continuous'],
        'present-perfect' => ['page_id'=>112,'category_id'=>18,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentPerfectTheorySeeder','path'=>'/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect'],
        'present-simple' => ['page_id'=>113,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder','path'=>'/theory/passive-voice/theory-passive-voice-present-simple'],
        'advanced' => ['page_id'=>282,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\AdvancedPassiveVoiceTheorySeeder','path'=>'/theory/passive-voice/advanced-passive-voice'],
        'reporting' => ['page_id'=>296,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveReportingStructuresTheorySeeder','path'=>'/theory/passive-voice/passive-reporting-structures'],
        'complex-causative' => ['page_id'=>305,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveAndCausativeTheorySeeder','path'=>'/theory/passive-voice/complex-passive-and-causative'],
        'complex-impersonal' => ['page_id'=>319,'category_id'=>1,'identity'=>'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder','path'=>'/theory/passive-voice/complex-passive-impersonal-style'],
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
        $script=dirname(__DIR__,2).'/tools/diagnostics/inspect-passive-voice-local-target.ps1';
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


