<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Conditions\FirstConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\MixedConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\SecondConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\ThirdConditionalPageSeeder;
use Database\Seeders\Pages\Conditions\ZeroConditionalPageSeeder;
use Database\Seeders\Pages\ArticlesAndQuantifiersPageSeeder;
use Database\Seeders\Pages\DemonstrativesPageSeeder;
use Database\Seeders\Pages\HaveGotPageSeeder;
use Database\Seeders\Pages\IrregularVerbsPageSeeder;
use Database\Seeders\Pages\ModalVerbsPageSeeder;
use Database\Seeders\Pages\PronounsPageSeeder;
use Database\Seeders\Pages\QuestionsPageSeeder;
use Database\Seeders\Pages\ThereIsThereArePageSeeder;
use Database\Seeders\Pages\ToBeVerbPageSeeder;
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
        ZeroConditionalPageSeeder::class,
        FirstConditionalPageSeeder::class,
        SecondConditionalPageSeeder::class,
        ThirdConditionalPageSeeder::class,
        MixedConditionalPageSeeder::class,
        ModalVerbsPageSeeder::class,
        ArticlesAndQuantifiersPageSeeder::class,
        DemonstrativesPageSeeder::class,
        HaveGotPageSeeder::class,
        IrregularVerbsPageSeeder::class,
        PronounsPageSeeder::class,
        QuestionsPageSeeder::class,
        ThereIsThereArePageSeeder::class,
        ToBeVerbPageSeeder::class,
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
