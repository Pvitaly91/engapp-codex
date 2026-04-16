<?php

namespace Tests\Support;

final class TheoryRouteMatrix
{
    private const TOP_LEVEL_CATEGORIES = [
        'basic-grammar' => [
            'titles' => [
                'uk' => 'Базова граматика',
                'en' => 'Basic Grammar',
                'pl' => 'Podstawy gramatyki',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarCategorySeeder',
            'representative_page' => [
                'slug' => 'basic-conjunctions-and-but-or-because-so',
                'title' => 'Conjunctions — and, but, or, because, so',
                'seeder' => 'Database\\Seeders\\Page_V3\\BasicGrammar\\BasicGrammarConjunctionsTheorySeeder',
            ],
        ],
        'imennyky-artykli-ta-kilkist' => [
            'titles' => [
                'uk' => 'Іменники, артиклі й кількість',
                'en' => 'Nouns / Articles / Quantity',
                'pl' => 'Rzeczowniki / Przedimki / Ilość',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityCategorySeeder',
            'representative_page' => [
                'slug' => 'advanced-articles-generic-reference-the-rich-a-tiger-people',
                'title' => 'Generic Reference — Узагальнення',
                'seeder' => 'Database\\Seeders\\Page_V3\\NounsArticlesQuantity\\NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder',
            ],
        ],
        'zaimennyky-ta-vkazivni-slova' => [
            'titles' => [
                'uk' => 'Займенники та вказівні слова',
                'en' => 'Pronouns / Demonstratives',
                'pl' => 'Zaimki i słowa wskazujące',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesCategorySeeder',
            'representative_page' => [
                'slug' => 'each-every-all',
                'title' => 'Each / Every / All',
                'seeder' => 'Database\\Seeders\\Page_V3\\PronounsDemonstratives\\PronounsDemonstrativesEachEveryAllTheorySeeder',
            ],
        ],
        'maibutni-formy' => [
            'titles' => [
                'uk' => 'Майбутні форми',
                'en' => 'Future Forms',
                'pl' => 'Formy przyszłości',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsCategorySeeder',
            'representative_page' => [
                'slug' => 'will-vs-be-going-to',
                'title' => 'Will vs Be Going To',
                'seeder' => 'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsWillVsBeGoingToTheorySeeder',
            ],
        ],
        'pytalni-rechennia-ta-zaperechennia' => [
            'titles' => [
                'uk' => 'Питальні речення та заперечення',
                'en' => 'Questions and Negations',
                'pl' => 'Pytania i przeczenia',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsCategorySeeder',
            'representative_page' => [
                'slug' => 'question-forms',
                'title' => 'Question Forms',
                'seeder' => 'Database\\Seeders\\Page_V3\\QuestionsNegations\\QuestionsNegationsQuestionFormsTheorySeeder',
            ],
        ],
        'prykmetniky-ta-pryslinknyky' => [
            'titles' => [
                'uk' => 'Прикметники та прислівники',
                'en' => 'Adjectives and Adverbs',
                'pl' => 'Przymiotniki i przysłówki',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesCategorySeeder',
            'representative_page' => [
                'slug' => 'comparative-vs-superlative',
                'title' => 'Comparative vs Superlative',
                'seeder' => 'Database\\Seeders\\Page_V3\\Adjectives\\AdjectivesComparativeVsSuperlativeTheorySeeder',
            ],
        ],
        'some-any' => [
            'titles' => [
                'uk' => 'Some / Any',
                'en' => 'Some / Any',
                'pl' => 'Some / Any',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyCategorySeeder',
            'representative_page' => [
                'slug' => 'theory-some-any-places',
                'title' => 'Some / Any — Places',
                'seeder' => 'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPlacesTheorySeeder',
            ],
        ],
        'tenses' => [
            'titles' => [
                'uk' => 'Часи',
                'en' => 'Tenses',
                'pl' => 'Czasy',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\Tenses\\TensesCategorySeeder',
            'representative_page' => [
                'slug' => 'present-perfect-vs-present-perfect-continuous',
                'title' => 'Present Perfect vs Present Perfect Continuous',
                'seeder' => 'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder',
            ],
        ],
        'passive-voice' => [
            'titles' => [
                'uk' => 'Пасивний стан',
                'en' => 'Passive Voice',
                'pl' => 'Strona bierna',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\PassiveVoiceCategorySeeder',
            'representative_page' => [
                'slug' => 'theory-passive-voice-causative',
                'title' => 'Causative — have/get something done',
                'seeder' => 'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceCausativeTheorySeeder',
            ],
        ],
        'modal-verbs' => [
            'titles' => [
                'uk' => 'Модальні дієслова',
                'en' => 'Modal Verbs',
                'pl' => 'Czasowniki modalne',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCategorySeeder',
            'representative_page' => [
                'slug' => 'can-could',
                'title' => 'Can / Could',
                'seeder' => 'Database\\Seeders\\Page_V3\\ModalVerbs\\ModalVerbsCanCouldTheorySeeder',
            ],
        ],
        'conditionals' => [
            'titles' => [
                'uk' => 'Умовні речення',
                'en' => 'Conditionals',
                'pl' => 'Okresy warunkowe',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsCategorySeeder',
            'representative_page' => [
                'slug' => 'first-conditional',
                'title' => 'First Conditional',
                'seeder' => 'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsFirstTheorySeeder',
            ],
        ],
        'reported-speech' => [
            'titles' => [
                'uk' => 'Непряма мова',
                'en' => 'Reported Speech',
                'pl' => 'Mowa zależna',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCategorySeeder',
            'representative_page' => [
                'slug' => 'backshift-of-tenses',
                'title' => 'Backshift of Tenses',
                'seeder' => 'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder',
            ],
        ],
        'clauses-and-linking-words' => [
            'titles' => [
                'uk' => "Підрядні речення та слова-зв'язки",
                'en' => 'Clauses and Linking Words',
                'pl' => 'Zdania podrzędne i wyrazy łączące',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsCategorySeeder',
            'representative_page' => [
                'slug' => 'contrast-although-though-however',
                'title' => 'Contrast: although / though / however',
                'seeder' => 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsContrastTheorySeeder',
            ],
        ],
        'prepositions-and-phrasal-verbs' => [
            'titles' => [
                'uk' => 'Прийменники та фразові дієслова',
                'en' => 'Prepositions and Phrasal Verbs',
                'pl' => 'Przyimki i czasowniki frazowe',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsAndPhrasalVerbsCategorySeeder',
            'representative_page' => [
                'slug' => 'prepositions-of-time',
                'title' => 'Prepositions of Time',
                'seeder' => 'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\PrepositionsOfTimeTheorySeeder',
            ],
        ],
        'common-mistakes' => [
            'titles' => [
                'uk' => 'Типові помилки',
                'en' => 'Common Mistakes',
                'pl' => 'Typowe błędy',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesCategorySeeder',
            'representative_page' => [
                'slug' => 'articles-common-mistakes',
                'title' => 'Articles: Common Mistakes',
                'seeder' => 'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesArticlesTheorySeeder',
            ],
        ],
        'sentence-transformations' => [
            'titles' => [
                'uk' => 'Трансформації речень',
                'en' => 'Sentence Transformations',
                'pl' => 'Transformacje zdań',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsCategorySeeder',
            'representative_page' => [
                'slug' => 'articles-quantifiers',
                'title' => 'Sentence Transformations — Articles / Quantifiers',
                'seeder' => 'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsArticlesQuantifiersTheorySeeder',
            ],
        ],
        'verb-patterns' => [
            'titles' => [
                'uk' => 'Дієслівні моделі',
                'en' => 'Verb Patterns',
                'pl' => 'Schematy czasownikowe',
            ],
            'category_seeder' => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsCategorySeeder',
            'representative_page' => [
                'slug' => 'bare-infinitive',
                'title' => 'Bare Infinitive',
                'seeder' => 'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBareInfinitiveTheorySeeder',
            ],
        ],
    ];

    private const EXTRA_PAGE_SEEDERS = [
        'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyPeopleTheorySeeder',
        'Database\\Seeders\\Page_V3\\Articles\\SomeAny\\SomeAnyThingsTheorySeeder',
        'Database\\Seeders\\Page_V3\\PassiveVoice\\Basics\\PassiveVoiceModalVerbsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Tenses\\TensesUsedToWouldTheorySeeder',
        'Database\\Seeders\\Page_V3\\VerbPatterns\\VerbPatternsBeUsedToGetUsedToUsedToTheorySeeder',
        'Database\\Seeders\\Page_V3\\CommonMistakes\\CommonMistakesWordOrderTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsWordOrderEmphasisTheorySeeder',
        'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\ClausesAndLinkingWordsRelativeClausesTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder',
        'Database\\Seeders\\Page_V3\\Conditionals\\ConditionalsMixedTheorySeeder',
        'Database\\Seeders\\Page_V3\\SentenceTransformations\\SentenceTransformationsConditionalsTheorySeeder',
    ];

    public static function topLevelCategories(): array
    {
        return self::TOP_LEVEL_CATEGORIES;
    }

    public static function indexTitleSamples(): array
    {
        return [
            'uk' => ['Базова граматика', 'Питальні речення та заперечення', 'Дієслівні моделі'],
            'en' => ['Basic Grammar', 'Questions and Negations', 'Verb Patterns'],
            'pl' => ['Podstawy gramatyki', 'Pytania i przeczenia', 'Schematy czasownikowe'],
        ];
    }

    public static function categorySmokeCases(): array
    {
        $cases = [];

        foreach (self::TOP_LEVEL_CATEGORIES as $categorySlug => $category) {
            foreach (['uk', 'en', 'pl'] as $locale) {
                $cases["{$locale}_{$categorySlug}"] = [
                    'locale' => $locale,
                    'category_slug' => $categorySlug,
                    'category_title' => $category['titles'][$locale],
                    'representative_page_slug' => $category['representative_page']['slug'],
                ];
            }
        }

        return $cases;
    }

    public static function representativeLessonCases(): array
    {
        $cases = [];

        foreach (self::TOP_LEVEL_CATEGORIES as $categorySlug => $category) {
            $page = $category['representative_page'];

            foreach (['uk', 'en', 'pl'] as $locale) {
                $cases["{$locale}_{$categorySlug}_{$page['slug']}"] = [
                    'locale' => $locale,
                    'category_slug' => $categorySlug,
                    'page_slug' => $page['slug'],
                    'page_title' => $page['title'],
                ];
            }
        }

        return $cases;
    }

    public static function overlapPairs(): array
    {
        return [
            'used_to_vs_be_used_to' => [
                [
                    'category_slug' => 'tenses',
                    'page_slug' => 'used-to-would',
                    'page_title' => 'Used to / Would',
                ],
                [
                    'category_slug' => 'verb-patterns',
                    'page_slug' => 'be-used-to-get-used-to-used-to',
                    'page_title' => 'Be used to / Get used to / Used to',
                ],
            ],
            'word_order_mistakes_vs_transformations' => [
                [
                    'category_slug' => 'common-mistakes',
                    'page_slug' => 'word-order-common-mistakes',
                    'page_title' => 'Word Order: Common Mistakes',
                ],
                [
                    'category_slug' => 'sentence-transformations',
                    'page_slug' => 'word-order-emphasis',
                    'page_title' => 'Sentence Transformations — Word Order / Emphasis',
                ],
            ],
            'relative_clauses_vs_transformations' => [
                [
                    'category_slug' => 'clauses-and-linking-words',
                    'page_slug' => 'relative-clauses',
                    'page_title' => 'Relative Clauses',
                ],
                [
                    'category_slug' => 'sentence-transformations',
                    'page_slug' => 'relative-clauses-linking-words',
                    'page_title' => 'Sentence Transformations — Relative Clauses / Linking Words',
                ],
            ],
            'conditionals_topic_vs_transformations' => [
                [
                    'category_slug' => 'conditionals',
                    'page_slug' => 'mixed-conditionals',
                    'page_title' => 'Mixed Conditionals',
                ],
                [
                    'category_slug' => 'sentence-transformations',
                    'page_slug' => 'conditionals',
                    'page_title' => 'Sentence Transformations — Conditionals',
                ],
            ],
        ];
    }

    public static function englishLeakageCases(): array
    {
        return [
            'en_some_any_places' => [
                'category_slug' => 'some-any',
                'page_slug' => 'theory-some-any-places',
                'expected_h1' => 'Some / Any — Places',
                'unexpected_fragments' => ['показує'],
            ],
            'en_modal_verbs_can_could' => [
                'category_slug' => 'modal-verbs',
                'page_slug' => 'can-could',
                'expected_h1' => 'Can / Could',
                'unexpected_fragments' => [],
            ],
            'en_verb_patterns_used_to' => [
                'category_slug' => 'verb-patterns',
                'page_slug' => 'be-used-to-get-used-to-used-to',
                'expected_h1' => 'Be used to / Get used to / Used to',
                'unexpected_fragments' => [],
            ],
        ];
    }

    public static function polishLeakageCases(): array
    {
        return [
            'pl_questions_category' => [
                'path' => self::localizedPath('pl', 'pytalni-rechennia-ta-zaperechennia'),
                'expected_h1' => 'Pytania i przeczenia',
                'unexpected_fragments' => ['Pytania, krótkie odpowiedzi i przeczenia po angielsku'],
            ],
            'pl_adjectives_category' => [
                'path' => self::localizedPath('pl', 'prykmetniky-ta-pryslinknyky'),
                'expected_h1' => 'Przymiotniki i przysłówki',
                'unexpected_fragments' => ['Przymiotniki, przysłówki i porównania po angielsku'],
            ],
            'pl_passive_modal_page' => [
                'path' => self::localizedPath('pl', 'passive-voice', 'theory-passive-voice-modal-verbs'),
                'expected_h1' => 'Strona bierna po czasownikach modalnych',
                'unexpected_fragments' => [
                    'Passive with modal verbs',
                    'Пасив з модальними дієсловами',
                    'Значення модальних дієслів у пасиві',
                ],
            ],
        ];
    }

    public static function selectedCategoryNavigationCases(): array
    {
        return [
            'some_any_category' => [
                'path' => self::localizedPath('uk', 'some-any'),
                'expected_links' => [
                    self::localizedPath('uk', 'some-any', 'theory-some-any-places'),
                    self::localizedPath('uk', 'some-any', 'theory-some-any-people'),
                    self::localizedPath('uk', 'some-any', 'theory-some-any-things'),
                ],
                'expected_labels' => [
                    'Some / Any — Places',
                    'Some / Any — People',
                    'Some / Any — Things',
                ],
            ],
            'sentence_transformations_category' => [
                'path' => self::localizedPath('uk', 'sentence-transformations'),
                'expected_links' => [
                    self::localizedPath('uk', 'sentence-transformations', 'articles-quantifiers'),
                    self::localizedPath('uk', 'sentence-transformations', 'word-order-emphasis'),
                ],
                'expected_labels' => [
                    'Sentence Transformations — Articles / Quantifiers',
                    'Sentence Transformations — Word Order / Emphasis',
                ],
            ],
        ];
    }

    public static function selectedLessonNavigationCases(): array
    {
        return [
            'some_any_places' => [
                'path' => self::localizedPath('uk', 'some-any', 'theory-some-any-places'),
                'expected_labels' => [
                    'Some / Any — Places',
                    'Some / Any — People',
                    'Some / Any — Things',
                ],
            ],
            'verb_patterns_used_to' => [
                'path' => self::localizedPath('uk', 'verb-patterns', 'be-used-to-get-used-to-used-to'),
                'expected_labels' => [
                    'Bare Infinitive',
                    'Gerund',
                    'Be used to / Get used to / Used to',
                ],
            ],
        ];
    }

    public static function fixtureSeederClasses(): array
    {
        $seeders = ['Database\\Seeders\\SiteTreeSeeder'];

        foreach (self::TOP_LEVEL_CATEGORIES as $category) {
            $seeders[] = $category['category_seeder'];
            $seeders[] = $category['representative_page']['seeder'];
        }

        return array_values(array_unique(array_merge($seeders, self::EXTRA_PAGE_SEEDERS)));
    }

    public static function localizedPath(string $locale, ?string $categorySlug = null, ?string $pageSlug = null): string
    {
        $prefix = $locale === 'uk' ? '' : '/' . $locale;
        $path = $prefix . '/theory';

        if ($categorySlug !== null) {
            $path .= '/' . $categorySlug;
        }

        if ($pageSlug !== null) {
            $path .= '/' . $pageSlug;
        }

        return $path;
    }
}
