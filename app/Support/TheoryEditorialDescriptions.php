<?php

namespace App\Support;

/** Reviewed public lesson summaries, separate from teaching content. See the M7 report for sources. */
final class TheoryEditorialDescriptions
{
    // Page.seeder is the server-resolved, portable identity; never use a title, short slug or request input.
    public const UK = [
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesOneOnesTheorySeeder' =>
            'One і ones в однині та множині: приклади з прикметниками, артиклями, вказівними займенниками й прийменниковими фразами.',
        'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesReciprocalPronounsTheorySeeder' =>
            'Як each other передає взаємну дію, на відміну від myself, herself і himself. Порівняння речень про дію одне на одного та на себе.',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder' =>
            'Present Perfect і Present Perfect Continuous: завершений результат та досвід проти процесу й тривалості. Порівняння форм, часових маркерів і вживання з дієсловами стану.',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesNarrativeTensesTheorySeeder' =>
            'Як поєднувати Past Simple, Past Continuous і Past Perfect у розповіді: основні події, фонові дії та попередні події. Роль when, while, before й after у послідовності подій.',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\AdvancedGerundInfinitivePatternsTheorySeeder' =>
            'Вибір між герундієм та інфінітивом після дієслів: avoid doing, decide to do і зміна значення з remember. Окреме правило для форми -ing після прийменників.',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder' =>
            'Як unless, provided that і as long as задають умову. Короткі правила й приклади для значення «якщо не», обов’язкової умови та умови, що зберігає чинність.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionBasicsTheorySeeder' =>
            'Базові моделі інверсії після never, rarely, only after та not only. Як поставити допоміжне дієслово перед підметом і побудувати not only … but also.',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesBasicsTheorySeeder' =>
            'Як виділити потрібну частину речення за допомогою it is/was … that/who та what … be. Короткі моделі для наголосу на особі, потребі або контрастній інформації.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\LinkingWordsReasonResultContrastTheorySeeder' =>
            'Зв’язок причини, наслідку й протиставлення: because of перед іменником, so для наслідку та although для контрасту. Короткий огляд споріднених слів-зв’язок.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\FormalRegisterAndNominalisationBasicsTheorySeeder' =>
            'Початкові орієнтири формального стилю: дієслова conduct, obtain і require та перетворення дії на іменникову фразу. Приклади формулювань для професійного спілкування.',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageBasicsTheorySeeder' =>
            'Як пом’якшувати категоричні твердження за допомогою seem, tend to, probably та generally. Модальні may, might і could для обережних висновків.',
        'Database\\Seeders\\Page_V3\\Conditionals\\AdvancedConditionalsTheorySeeder' =>
            'Змішані умовні речення: минула умова з теперішнім результатом і теперішня умова з минулим результатом. Приклад формальної інверсії з Had I known.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder' =>
            'Інверсія після заперечних і обмежувальних виразів: never, only then та under no circumstances. Окремо — Past Perfect у конструкціях hardly … when і no sooner … than.',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveReportingStructuresTheorySeeder' =>
            'Як передавати повідомлення через it is believed that та subject + is said to. Форма to have + V3 для дії, яка відбулася раніше за момент повідомлення.',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\CleftSentencesEmphasisTheorySeeder' =>
            'Виділення особи, дії чи результату конструкціями it-cleft і what-cleft. Як зробити акцент у питанні за моделлю When was it that …?',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\HedgingAndCautiousLanguageTheorySeeder' =>
            'Обережні академічні твердження з seem, suggest і модальними дієсловами. Як позначити межі висновку через to some extent, not necessarily та визнання обмеженості доказів.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ParticipleClausesTheorySeeder' =>
            'Скорочені конструкції з -ing, V3 і having + V3 для фонової, пасивної та попередньої дії. Приклади, що показують причину й послідовність подій.',
        'Database\\Seeders\\Page_V3\\RelativeClauses\\ComplexRelativeClausesTheorySeeder' =>
            'Точність означальних речень: whose для належності, прийменник перед whom або which у формальному стилі та коми для додаткової інформації.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalisationFormalRegisterTheorySeeder' =>
            'Як іменникові фрази надають висловлюванню формальної дистанції й узагальненості. Приклади з make a decision, conduct an evaluation та нейтральними академічними формулюваннями.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedLinkingDevicesTheorySeeder' =>
            'Логічні зв’язки у формальному викладі: допустове значення з although, наслідок із consequently та умова з provided that. Також наведено засоби додавання аргументів.',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder' =>
            'Modal + have + V3 для висновків про минуле: впевненість із must have, можливість із might have та оцінка минулих дій із should have й needn’t have.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder' =>
            'Базова форма дієслова після формальних вимог і рекомендацій, альтернатива з should та конструкції з lest. Приклади It is essential that і The committee recommended that.',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveAndCausativeTheorySeeder' =>
            'Have/get + object + V3 для дій, виконаних на чиєсь замовлення, і складні пасивні конструкції на кшталт is expected to be revised. Нейтральний фокус на дії, а не виконавці.',
        'Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\AdvancedArticleAndQuantifierNuanceTheorySeeder' =>
            'Короткі орієнтири вибору little, a little, few і a few та артиклів для точного значення кількості й конкретності. Приклади тверджень про докази та дослідження.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\RegisterToneAndParaphraseTheorySeeder' =>
            'Перехід від розмовного до нейтрального чи формального викладу зі збереженням змісту. Як перефразування змінює ступінь упевненості, ввічливості та дистанції.',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalAlternativesAndNuanceTheorySeeder' =>
            'Точні умови з provided that і assuming, відсутній чинник у but for та had it not been for. Формальні гіпотетичні умови з інверсією should або were … to.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\DiscourseMarkersAndCohesionTheorySeeder' =>
            'Як nevertheless, moreover і accordingly пов’язують протиставлення, додавання та наслідок. Вирази in light of this і with this in mind прив’язують оцінку до попередньої інформації.',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\EllipsisSubstitutionAndReferenceTheorySeeder' =>
            'Як уникати повторів за допомогою so, not, do so та one/ones. Вирази the former і the latter пов’язують нове речення з уже згаданими предметами чи ідеями.',
        'Database\\Seeders\\Page_V3\\SentenceStructure\\ComplexNounPhrasesTheorySeeder' =>
            'Побудова складних іменникових груп: означення перед головним іменником і уточнення після нього. Як компактно передавати абстрактні ідеї у формальному викладі.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\AdvancedFrontingAndEmphasisTheorySeeder' =>
            'Винесення обставин і доповнень на початок речення для акценту та зв’язності. Приклади з At the heart of the problem і Only then та інверсія там, де її вимагає конструкція.',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\StanceRegisterAndEvaluationTheorySeeder' =>
            'Як висловити позицію без надмірної впевненості: it could be argued that, обережні оцінки та формальний тон. Добір оцінних слів відповідно до сили доказів.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ConcessiveAndContrastiveStructuresTheorySeeder' =>
            'Допустове значення з although і despite та зіставлення різних позицій через while і whereas. Як розмістити уточнення, щоб головне твердження залишалося зрозумілим.',
        'Database\\Seeders\\Page_V3\\VocabularyAndCollocations\\AdvancedCollocationAndLexicalChoiceTheorySeeder' =>
            'Точні сполучення дієслів з іменниками: pose a challenge, set a precedent і draw a distinction. Вибір лексики відповідно до сили твердження та формального регістру.',
        'Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\PrecisionWithArticlesAndDeterminersTheorySeeder' =>
            'Вибір a/an, the або нульового артикля для загального й конкретного значення. Як some, each, either та інші визначники уточнюють обсяг висловлювання й відсилання до відомого.',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\AdvancedParticipleAndAbsoluteClausesTheorySeeder' =>
            'Having + V3 для попередньої дії та V3 для пасивного значення. Абсолютна конструкція noun + participle додає окрему обставину, як у All conditions having been met.',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\ComplexPassiveImpersonalStyleTheorySeeder' =>
            'Безособове It is believed that і особове is said to у формальних повідомленнях. Перфектний пасивний інфінітив to have been + V3 для ранішої дії.',
        'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder' =>
            'Модальні відтінки: ймовірність із may well, м’яка порада з might want to та минула необхідність із need not have. Короткі правила й приклади в контексті.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder' =>
            'Як implementation, assessment і reduction перетворюють дії на компактні іменникові структури. Поєднання уточнень зі зрозумілим головним твердженням у формальному стилі.',
        'Database\\Seeders\\Page_V3\\FormalEnglish\\ParaphraseAndReformulationTheorySeeder' =>
            'Уточнення думки через in other words, that is to say та put differently. Як змінити акцент, зберігши основний зміст і не повторюючи твердження нечіткими словами.',
        'Database\\Seeders\\Page_V3\\AcademicEnglish\\ArgumentationAndAcademicToneTheorySeeder' =>
            'Побудова обережних тверджень на основі доказів і формальних висновків із therefore, thus та consequently. Як пов’язати аргумент із даними без перебільшення.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarB1MixedRevisionTheorySeeder' =>
            'Пам’ятка для повторення часів, непрямої мови, підрядних речень і модальних конструкцій. Орієнтири для перевірки граматичної форми, часу події та типу підрядного речення.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder' =>
            'Спільний огляд умовних речень, інверсії, пасивних повідомлень і формального стилю. Приклади нагадують, як зберегти час, акцент та логічний зв’язок під час вибору конструкції.',
        'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder' =>
            'Підсумкові орієнтири для умовних конструкцій, зв’язності та модальних відтінків. У центрі уваги — точність змісту, формальний тон і поєднання речень без втрати логіки.',
    ];

    public static function forPageSeeder(string $seeder, string $locale): ?string
    {
        return $locale === 'uk' ? (self::UK[$seeder] ?? null) : null;
    }
}
