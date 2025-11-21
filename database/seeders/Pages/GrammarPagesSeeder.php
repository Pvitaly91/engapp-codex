<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Articles\AAnThePageSeeder;
use Database\Seeders\Pages\Articles\QuantifiersPageSeeder;
use Database\Seeders\Pages\Articles\SomeAnyPageSeeder;
use Database\Seeders\Pages\Conditions\FirstConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\MixedConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\SecondConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\ThirdConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\ZeroConditionalPageSeeder;
use Database\Seeders\Pages\Adjectives\DegreesOfComparisonPageSeeder;
use Database\Seeders\Pages\Demonstratives\ThisThatTheseThosePageSeeder;
use Database\Seeders\Pages\HaveGot\HaveGotPageSeeder;
use Database\Seeders\Pages\IrregularVerbs\IrregularVerbsPageSeeder;
use Database\Seeders\Pages\Modals\CanCouldPageSeeder;
use Database\Seeders\Pages\Modals\MayMightPageSeeder;
use Database\Seeders\Pages\Modals\MustHaveToPageSeeder;
use Database\Seeders\Pages\Modals\PerfectModalsPageSeeder;
use Database\Seeders\Pages\Modals\NeedNeedToPageSeeder;
use Database\Seeders\Pages\Modals\ShouldOughtToPageSeeder;
use Database\Seeders\Pages\Pronouns\PronounsPageSeeder;
use Database\Seeders\Pages\Questions\QuestionFormsPageSeeder;
use Database\Seeders\Pages\Questions\ShortAnswersPageSeeder;
use Database\Seeders\Pages\Structures\ContractionsPageSeeder;
use Database\Seeders\Pages\Structures\DoDoesIsArePageSeeder;
use Database\Seeders\Pages\Structures\ThereIsThereArePageSeeder;
use Database\Seeders\Pages\Structures\VerbToBePageSeeder;
use Database\Seeders\Pages\Tenses\FutureContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\FuturePerfectContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\FuturePerfectPageSeeder;
use Database\Seeders\Pages\Tenses\FutureSimplePageSeeder;
use Database\Seeders\Pages\Tenses\PastContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\PastPerfectContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\PastPerfectPageSeeder;
use Database\Seeders\Pages\Tenses\PastSimplePageSeeder;
use Database\Seeders\Pages\Tenses\PresentContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\PresentPerfectContinuousPageSeeder;
use Database\Seeders\Pages\Tenses\PresentPerfectPageSeeder;
use Database\Seeders\Pages\Tenses\PresentSimplePageSeeder;
use Database\Seeders\Pages\Translation\TranslationTechniquesPageSeeder;
use Database\Seeders\Pages\Vocabulary\VocabularyStrategiesPageSeeder;
use App\Support\Database\Seeder;

class GrammarPagesSeeder extends Seeder
{
    private const SEEDERS = [
        FuturePerfectPageSeeder::class,
        PastPerfectPageSeeder::class,
        FutureSimplePageSeeder::class,
        PresentPerfectPageSeeder::class,
        FutureContinuousPageSeeder::class,
        PresentPerfectContinuousPageSeeder::class,
        PastPerfectContinuousPageSeeder::class,
        PastSimplePageSeeder::class,
        PresentSimplePageSeeder::class,
        FuturePerfectContinuousPageSeeder::class,
        PastContinuousPageSeeder::class,
        PresentContinuousPageSeeder::class,
        AAnThePageSeeder::class,
        SomeAnyPageSeeder::class,
        QuantifiersPageSeeder::class,
        ZeroConditionalPageSeeder::class,
        FirstConditionalPageSeeder::class,
        SecondConditionalPageSeeder::class,
        ThirdConditionalPageSeeder::class,
        MixedConditionalPageSeeder::class,
        CanCouldPageSeeder::class,
        MayMightPageSeeder::class,
        MustHaveToPageSeeder::class,
        ShouldOughtToPageSeeder::class,
        NeedNeedToPageSeeder::class,
        PerfectModalsPageSeeder::class,
        PronounsPageSeeder::class,
        ThisThatTheseThosePageSeeder::class,
        HaveGotPageSeeder::class,
        IrregularVerbsPageSeeder::class,
        QuestionFormsPageSeeder::class,
        ShortAnswersPageSeeder::class,
        ContractionsPageSeeder::class,
        VerbToBePageSeeder::class,
        DoDoesIsArePageSeeder::class,
        ThereIsThereArePageSeeder::class,
        VocabularyStrategiesPageSeeder::class,
        TranslationTechniquesPageSeeder::class,
        DegreesOfComparisonPageSeeder::class,
    ];

    public function run(): void
    {
        $this->call(self::SEEDERS);
    }

    protected function shouldRun(): bool
    {
        return true;
    }
}
