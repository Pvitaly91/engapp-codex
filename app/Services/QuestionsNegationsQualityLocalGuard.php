<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Twelve approved question/negation leaves; no remote or unrelated theme authority. */
final class QuestionsNegationsQualityLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Questions and Negations quality';
    protected const PRIVATE_DIRECTORY = 'questions-negations-quality';
    protected const ENDPOINT = 'questions-negations-quality-';

    public const BINDINGS = [
        'question-forms' => ['page_id'=>131,'category_id'=>21,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsQuestionFormsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/question-forms'],
        'yes-no' => ['page_id'=>140,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/yesno-questions-general-questions'],
        'wh' => ['page_id'=>139,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/wh-questions-special-questions-who-what-where-when-why-how'],
        'subject' => ['page_id'=>138,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsSubjectQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/subject-questions'],
        'answers' => ['page_id'=>133,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAnswersToQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/answers-to-questions-yes-i-do-no-i-dont'],
        'alternative' => ['page_id'=>132,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsAlternativeQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/alternative-questions-coffee-or-tea'],
        'tags' => ['page_id'=>137,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/question-tags-disjunctive-questions-dont-you-isnt-it'],
        'negative-questions' => ['page_id'=>136,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsNegativeQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/negative-questions-dont-you-know'],
        'embedded' => ['page_id'=>134,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsEmbeddedQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/embedded-questions'],
        'indirect' => ['page_id'=>135,'category_id'=>22,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\TypesOfQuestions\\TypesOfQuestionsIndirectQuestionsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/indirect-questions'],
        'negative-forms' => ['page_id'=>331,'category_id'=>21,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsNegativeFormsTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/negative-forms'],
        'double-negatives' => ['page_id'=>332,'category_id'=>21,'identity'=>'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsDoubleNegativesTheorySeeder','path'=>'/theory/pytalni-rechennia-ta-zaperechennia/double-negatives'],
    ];

    public static function scope(): array
    {
        return array_values(array_map(static fn(array $binding): array => [
            'identity'=>$binding['identity'],'locale'=>'uk','path'=>$binding['path'],
        ],self::BINDINGS));
    }

    /** Explicitly approved for this theme only; the shared inspector is not changed. */
    protected function windowsEvidence(int $port): array
    {
        if ($port !== 3306) { throw new RuntimeException('Exact local MySQL port required.'); }
        $script=dirname(__DIR__,2).'/tools/diagnostics/inspect-questions-negations-local-target.ps1';
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
