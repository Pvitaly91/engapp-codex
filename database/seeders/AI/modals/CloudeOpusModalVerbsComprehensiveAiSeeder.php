<?php

namespace Database\Seeders\AI\modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class CloudeOpusModalVerbsComprehensiveAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 6,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Comprehensive'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'AI: CloudeOpus Modal Verbs Comprehensive'])->id;

        $themeTagIds = $this->createThemeTags();
        $modalTagIds = $this->createModalTags();
        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        $questions = $this->getQuestionData();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $level = $entry['level'];
            $theme = $entry['theme'];
            $questionText = $entry['question'];
            $tagIds = [$modalsTagId];
            if (isset($themeTagIds[$theme])) {
                $tagIds[] = $themeTagIds[$theme];
            }

            if (isset($entry['parts'])) {
                $parts = $entry['parts'];
                $options = [];
                $optionMarkers = [];
                $answerEntries = [];
                $answersByMarker = [];
                $hintsByMarker = [];
                $explanations = [];

                foreach ($parts as $marker => $part) {
                    $partOptions = $part['options'] ?? [];
                    $answerIndex = $part['answer_index'] ?? 0;
                    $partAnswer = $partOptions[$answerIndex] ?? '';

                    $answerEntries[] = [
                        'marker' => $marker,
                        'answer' => $partAnswer,
                        'verb_hint' => $this->normalizeHint($part['verb_hint'] ?? ''),
                    ];

                    $answersByMarker[$marker] = $partAnswer;

                    $concept = $part['concept'] ?? $entry['concept'] ?? '';
                    $exampleHint = $part['example_hint'] ?? $entry['example_hint'] ?? '';

                    $hintsByMarker[$marker] = $this->buildHint($theme, $concept, $part['verb_hint'] ?? '', $exampleHint);

                    $tagIds = array_merge($tagIds, $this->determineModalTags($partAnswer, $partOptions, $modalTagIds));

                    foreach ($partOptions as $option) {
                        if (!in_array($option, $options, true)) {
                            $options[] = $option;
                        }

                        if (!isset($optionMarkers[$option])) {
                            $optionMarkers[$option] = $marker;
                        }

                        if (!isset($explanations[$option])) {
                            if ($option === $partAnswer) {
                                $explanations[$option] = $this->buildCorrectExplanation($concept, $questionText);
                            } else {
                                $explanations[$option] = $this->buildIncorrectExplanation($option, $concept, $questionText);
                            }
                        }
                    }
                }

                $uuid = $this->generateQuestionUuid($level, $theme, $index + 1);

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $questionText,
                    'category_id' => $categoryId,
                    'difficulty' => $this->levelDifficulty[$level] ?? 3,
                    'source_id' => $sourceId,
                    'flag' => 2,
                    'type' => 0,
                    'level' => $level,
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answerEntries,
                    'options' => $options,
                    'variants' => [],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $answersByMarker,
                    'option_markers' => $optionMarkers,
                    'hints' => $hintsByMarker,
                    'explanations' => $explanations,
                ];

                continue;
            }

            $options = $entry['options'];
            $answerIndex = $entry['answer_index'];
            $verbHint = $entry['verb_hint'] ?? '';
            $concept = $entry['concept'];
            $exampleHint = $entry['example_hint'];

            $answer = $options[$answerIndex];

            $modalTags = $this->determineModalTags($answer, $options, $modalTagIds);
            $tagIds = array_merge($tagIds, $modalTags);

            $hint = $this->buildHint($theme, $concept, $verbHint, $exampleHint);
            $explanations = $this->buildExplanations($options, $answer, $concept, $questionText);

            $uuid = $this->generateQuestionUuid($level, $theme, $index + 1);

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $level,
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($verbHint),
                    ],
                ],
                'options' => $options,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => ['a1' => $answer],
                'option_markers' => array_fill_keys($options, 'a1'),
                'hints' => ['a1' => $hint],
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function createThemeTags(): array
    {
        $themes = [
            'Ability & Permission' => 'Modal Ability & Permission',
            'Obligation & Necessity' => 'Modal Obligation & Necessity',
            'Possibility & Deduction' => 'Modal Possibility & Deduction',
        ];

        $tagIds = [];
        foreach ($themes as $key => $name) {
            $tagIds[$key] = Tag::firstOrCreate(
                ['name' => $name],
                ['category' => 'English Grammar Theme']
            )->id;
        }

        return $tagIds;
    }

    private function createModalTags(): array
    {
        $modalPairs = [
            'can_could' => 'Can / Could',
            'may_might' => 'May / Might',
            'must_have_to' => 'Must / Have to',
            'should_ought_to' => 'Should / Ought to',
            'will_would' => 'Will / Would',
            'shall' => 'Shall',
            'need' => 'Need / Needn\'t',
        ];

        $tagIds = [];
        foreach ($modalPairs as $key => $name) {
            $tagIds[$key] = Tag::firstOrCreate(
                ['name' => $name],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }

        return $tagIds;
    }

    private function determineModalTags(string $answer, array $options, array $modalTagIds): array
    {
        $tags = [];
        $answerLower = mb_strtolower(trim($answer));

        $modalMap = [
            "can't" => 'can_could',
            'cannot' => 'can_could',
            'can' => 'can_could',
            'could' => 'can_could',
            "couldn't" => 'can_could',
            'may' => 'may_might',
            'might' => 'may_might',
            "mightn't" => 'may_might',
            'must' => 'must_have_to',
            "mustn't" => 'must_have_to',
            'have to' => 'must_have_to',
            'has to' => 'must_have_to',
            'had to' => 'must_have_to',
            'should' => 'should_ought_to',
            "shouldn't" => 'should_ought_to',
            'ought to' => 'should_ought_to',
            'would' => 'will_would',
            'will' => 'will_would',
            "wouldn't" => 'will_would',
            'shall' => 'shall',
            'need to' => 'need',
            "don't need to" => 'need',
            "needn't" => 'need',
        ];

        foreach ($modalMap as $modal => $tagKey) {
            if (str_contains($answerLower, $modal) && isset($modalTagIds[$tagKey])) {
                $tags[] = $modalTagIds[$tagKey];
            }
        }

        return array_values(array_unique($tags));
    }

    private function buildHint(string $theme, string $concept, string $verbHint, string $exampleHint): string
    {
        $conceptHints = [
            'present_ability' => 'Зосередься на теперішній навичці чи здатності виконати дію.',
            'asking_permission' => 'Речення стосується прохання дозволу, обери ввічливу форму.',
            'lack_permission' => 'Йдеться про заборону, знайди модальне слово для відсутності дозволу.',
            'past_ability' => 'Вибери модальне дієслово, що описує минулу здібність.',
            'conditional_ability' => 'Подумай про умовну можливість виконати дію.',
            'formal_permission' => 'Використай форму для офіційного дозволу.',
            'external_obligation' => 'Зверни увагу на зовнішню вимогу чи правило.',
            'personal_duty' => 'Речення підкреслює особистий моральний обов\'язок.',
            'absence_of_obligation' => 'Дія не є обов\'язковою, знайди форму що знімає необхідність.',
            'future_requirement' => 'Речення описує майбутню необхідність.',
            'strong_deduction' => 'Є чіткі підказки для впевненого логічного висновку.',
            'tentative_possibility' => 'Контекст лише припускає варіант з низькою впевненістю.',
            'logical_conclusion_past' => 'Зроби логічний висновок про минуле на основі фактів.',
            'speculative_future' => 'Обережний прогноз про майбутнє без впевненості.',
            'contradicting_evidence' => 'Факти заперечують можливість, вибери форму неможливості.',
            'probable_consequence' => 'Ситуація вказує на майже гарантований наслідок.',
        ];

        $base = $conceptHints[$concept] ?? 'Зосередься на правильному значенні модального дієслова.';

        if ($verbHint) {
            $base .= "\n\nПідказка щодо структури: " . $verbHint;
        }

        $base .= "\n\nПриклад для роздумів: " . $exampleHint;

        return $base;
    }

    private function buildExplanations(array $options, string $answer, string $concept, string $question): array
    {
        $explanations = [];

        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $this->buildCorrectExplanation($concept, $question);
            } else {
                $explanations[$option] = $this->buildIncorrectExplanation($option, $concept, $question);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $concept, string $question): string
    {
        $conceptExplanations = [
            'present_ability' => 'Ця форма правильно передає наявну здібність у теперішньому часі.',
            'asking_permission' => 'Цей варіант ввічливо запитує про дозвіл без тиску.',
            'lack_permission' => 'Правильна форма чітко передає заборону або відсутність дозволу.',
            'past_ability' => 'Ця форма коректно описує здібність у минулому.',
            'conditional_ability' => 'Правильний варіант передає умовну можливість за певних обставин.',
            'formal_permission' => 'Ця форма звучить формально і показує надання дозволу.',
            'external_obligation' => 'Правильний вибір відображає зовнішнє правило або вимогу.',
            'personal_duty' => 'Ця форма натякає на моральний або внутрішній обов\'язок.',
            'absence_of_obligation' => 'Правильний варіант показує, що дія не є обов\'язковою.',
            'future_requirement' => 'Ця форма підкреслює майбутню необхідність.',
            'strong_deduction' => 'Правильний варіант робить впевнений логічний висновок.',
            'tentative_possibility' => 'Ця форма обережно припускає можливість без гарантії.',
            'logical_conclusion_past' => 'Правильний вибір логічно пояснює минулу ситуацію.',
            'speculative_future' => 'Ця форма обережно прогнозує майбутній розвиток.',
            'contradicting_evidence' => 'Правильний варіант виражає неможливість через факти.',
            'probable_consequence' => 'Ця форма показує майже неминучий наслідок.',
        ];

        $base = $conceptExplanations[$concept] ?? 'Правильний варіант найкраще відповідає контексту.';

        return $base . "\nСитуація: " . $question;
    }

    private function buildIncorrectExplanation(string $option, string $concept, string $question): string
    {
        $option = mb_strtolower($option);

        $reasons = [
            'can' => 'передає загальну здатність, а не те значення що потрібне тут',
            "can't" => 'виражає заборону або неможливість, що не відповідає контексту',
            'could' => 'може натякати на минулий або умовний контекст, що тут не підходить',
            "couldn't" => 'звучить як відсутність можливості, а ситуація вимагає іншого',
            'may' => 'може бути занадто формальним або невпевненим для цієї ситуації',
            'might' => 'виражає лише припущення, а контекст потребує іншого відтінку',
            'must' => 'передає сильний обов\'язок або впевненість, що не збігається з текстом',
            "mustn't" => 'означає сувору заборону, якої тут немає',
            'have to' => 'натякає на зовнішній примус, який в реченні не згадується',
            'has to' => 'підкреслює зовнішню вимогу, не властиву цій ситуації',
            'should' => 'дає пораду або рекомендацію, а не те що потрібно',
            "shouldn't" => 'рекомендує уникати дії, хоча контекст цього не вимагає',
            'would' => 'описує гіпотетичний сценарій, який не відповідає контексту',
            "wouldn't" => 'передає відмову або негативну реакцію, що не підтримується текстом',
            'shall' => 'звучить надто формально або застаріло для цієї ситуації',
            'need to' => 'означає необхідність, яка в реченні не вказана',
            "don't need to" => 'знімає обов\'язок, хоча текст говорить про інше',
            "needn't" => 'показує відсутність потреби, що суперечить змісту',
            'ought to' => 'звучить як моральна порада, а не як потрібний зміст',
        ];

        $reason = $reasons[$option] ?? 'не передає правильний відтінок значення для цього контексту';

        return 'Форма ' . $reason . ".\nСитуація: " . $question;
    }

    private function getQuestionData(): array
    {
        return [
            // Ability & Permission - A1 Level (10 questions, 6 per level requirement)
            [
                'question' => 'I {a1} swim very well.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'base form of ability verb',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Хтось має навичку плавати.',
            ],
            [
                'question' => '{a1} I use your phone?',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['Can', 'Must', 'Should', 'Will'],
                'answer_index' => 0,
                'verb_hint' => 'permission question form',
                'concept' => 'asking_permission',
                'example_hint' => 'Наприклад: Запит про дозвіл використати річ.',
            ],
            [
                'question' => 'She {a1} speak three languages.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'might', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'ability to communicate',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Людина має мовні навички.',
            ],
            [
                'question' => 'You {a1} enter without a ticket.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'lack of permission',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Правило забороняє вхід.',
            ],
            [
                'question' => 'We {a1} play in the park.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'might', 'shall'],
                'answer_index' => 0,
                'verb_hint' => 'general ability action',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Діти мають можливість гратися.',
            ],
            [
                'question' => '{a1} we leave now?',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['May', 'Must', 'Should', 'Would'],
                'answer_index' => 0,
                'verb_hint' => 'formal permission',
                'concept' => 'formal_permission',
                'example_hint' => 'Наприклад: Ввічливе запитання про дозвіл піти.',
            ],
            [
                'question' => 'He {a1} ride a bicycle.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'skill or ability',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Хлопчик вміє їздити на велосипеді.',
            ],
            [
                'question' => 'They {a1} come to the party.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'might', 'shall'],
                'answer_index' => 0,
                'verb_hint' => 'possibility or ability',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Люди мають можливість прийти.',
            ],
            [
                'question' => 'You {a1} smoke here.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'prohibition',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Заборонено палити в цьому місці.',
            ],
            [
                'question' => 'I {a1} help you with that.',
                'level' => 'A1',
                'theme' => 'Ability & Permission',
                'options' => ['can', 'must', 'should', 'will'],
                'answer_index' => 0,
                'verb_hint' => 'offer of ability',
                'concept' => 'present_ability',
                'example_hint' => 'Наприклад: Пропозиція допомоги через здатність.',
            ],

            // Ability & Permission - A2 Level (10 questions)
            [
                'question' => 'When I was young, I {a1} run very fast.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'past ability',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Здібність у минулому.',
            ],
            [
                'question' => 'Students {a1} borrow books from the library, and they {a2} keep them for two weeks.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission granted',
                        'concept' => 'asking_permission',
                        'example_hint' => 'Наприклад: Дозвіл брати книги.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'allowed duration',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозволена тривалість зберігання.',
                    ],
                ],
            ],
            [
                'question' => 'If you practice, you {a1} speak better.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'conditional ability',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Потенційна здібність за умови практики.',
            ],
            [
                'question' => 'You {a1} park here without a permit.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'rule prohibition',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Заборона паркуватися без дозволу.',
            ],
            [
                'question' => 'Children {a1} play in the garden, and they {a2} use the swings safely.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'general permission',
                        'concept' => 'asking_permission',
                        'example_hint' => 'Наприклад: Дозвіл гратися.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'permitted safe use',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозволене безпечне використання.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} play piano when she was five.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'past skill',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Навичка гри у минулому.',
            ],
            [
                'question' => 'Visitors {a1} take photos in the museum, but they {a2} use flash.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'permission to photograph',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл фотографувати.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'flash prohibition',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборона спалаху.',
                    ],
                ],
            ],
            [
                'question' => 'We {a1} finish early if we work together.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'potential ability',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Можливість завершити за умови співпраці.',
            ],
            [
                'question' => 'Guests {a1} check in after 2 PM, and they {a2} request early access for a fee.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'standard permission time',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Стандартний час реєстрації.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'will', 'must'],
                        'answer_index' => 0,
                        'verb_hint' => 'optional request',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Можливість попросити ранній доступ.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} bring pets into the building.',
                'level' => 'A2',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'building rule',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Правило будівлі про тварин.',
            ],

            // Ability & Permission - B1 Level (10 questions)
            [
                'question' => 'He {a1} speak Japanese fluently after living there for five years.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'acquired past ability',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Здібність набута через проживання.',
            ],
            [
                'question' => 'Employees {a1} access the server remotely, and they {a2} download files with approval.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'technical ability granted',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Технічна можливість доступу.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'will', 'must'],
                        'answer_index' => 0,
                        'verb_hint' => 'conditional permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл за умови схвалення.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} bring your own device, but you {a2} connect it to the secure network.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'device permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозволено приносити пристрої.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'network restriction',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборонено підключатися до мережі.',
                    ],
                ],
            ],
            [
                'question' => 'If I had more time, I {a1} learn to play guitar.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'hypothetical ability',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Гіпотетична можливість при наявності часу.',
            ],
            [
                'question' => 'Participants {a1} submit questions during the webinar, and they {a2} vote on topics afterwards.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'interaction permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл задавати питання.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'post-event option',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Можливість голосувати після.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} solve complex equations when she was in high school.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'academic past ability',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Математична здібність у минулому.',
            ],
            [
                'question' => 'You {a1} take the company car for personal use without authorization.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'company policy',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Політика компанії про використання авто.',
            ],
            [
                'question' => 'Tenants {a1} renovate their apartments, but they {a2} make structural changes.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'renovation permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл на ремонт.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'structural limitation',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборона змінювати конструкцію.',
                    ],
                ],
            ],
            [
                'question' => 'With proper training, you {a1} operate this machinery.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'must', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'skill development',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Можливість працювати після навчання.',
            ],
            [
                'question' => 'Members {a1} book facilities online, and they {a2} cancel up to 24 hours in advance.',
                'level' => 'B1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'booking capability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Можливість онлайн-бронювання.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'cancellation option',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозволене скасування.',
                    ],
                ],
            ],

            // Ability & Permission - B2 Level (10 questions)
            [
                'question' => 'With sufficient funding, the team {a1} complete the project ahead of schedule.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'conditional capability',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Потенціал за наявності ресурсів.',
            ],
            [
                'question' => 'Researchers {a1} access the database for approved studies, and they {a2} share anonymized data with collaborators.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'research permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Науковий доступ.',
                    ],
                    'a2' => [
                        'options' => ['can', 'could', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'data sharing ability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Можливість ділитися даними.',
                    ],
                ],
            ],
            [
                'question' => 'Before the reforms, citizens {a1} travel freely within the region.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'historical permission',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Свобода пересування у минулому.',
            ],
            [
                'question' => 'Analysts {a1} interpret the data independently, but they {a2} publish findings without peer review.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'analytical ability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Здатність аналізувати самостійно.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'publication restriction',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Обмеження публікації.',
                    ],
                ],
            ],
            [
                'question' => 'Had she pursued it further, she {a1} become an accomplished violinist.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'options' => ['could have', 'can have', 'may have', 'must have'],
                'answer_index' => 0,
                'verb_hint' => 'unrealized past potential',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Нереалізований потенціал у минулому.',
            ],
            [
                'question' => 'Contractors {a1} submit bids electronically, and they {a2} revise proposals until the deadline.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'submission capability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Можливість подавати електронно.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'revision permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл переглядати пропозиції.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} access restricted areas without proper clearance.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'security restriction',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Обмеження доступу до зон.',
            ],
            [
                'question' => 'Delegates {a1} propose amendments during the session, but they {a2} vote on them without quorum.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'procedural permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право пропонувати зміни.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'voting limitation',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборона голосувати без кворуму.',
                    ],
                ],
            ],
            [
                'question' => 'Given adequate resources, the organization {a1} expand its outreach programs.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'organizational potential',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Можливість розширення за умови ресурсів.',
            ],
            [
                'question' => 'Journalists {a1} attend press conferences, and they {a2} ask follow-up questions after the briefing.',
                'level' => 'B2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'press access',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Дозвіл відвідувати конференції.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'question opportunity',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Можливість ставити питання.',
                    ],
                ],
            ],

            // Ability & Permission - C1 Level (10 questions)
            [
                'question' => 'Had the infrastructure been adequate, the region {a1} sustain a larger population.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'options' => ['could have', 'can have', 'may have', 'must have'],
                'answer_index' => 0,
                'verb_hint' => 'past unrealized capacity',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Нереалізована можливість через обставини.',
            ],
            [
                'question' => 'Auditors {a1} scrutinize financial records independently, and they {a2} subpoena documents if discrepancies emerge.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'investigative capability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Здатність незалежно перевіряти.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'legal authority',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Юридичне право викликати документи.',
                    ],
                ],
            ],
            [
                'question' => 'Prior to deregulation, operators {a1} charge only government-approved rates.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'historical regulatory ability',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Обмежена можливість у минулому.',
            ],
            [
                'question' => 'Scholars {a1} critique prevailing theories, but they {a2} disregard empirical evidence.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'academic freedom',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право критикувати теорії.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'scholarly limitation',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборона ігнорувати докази.',
                    ],
                ],
            ],
            [
                'question' => 'With enhanced algorithms, AI {a1} diagnose rare diseases with unprecedented accuracy.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'technological potential',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Потенціал технологій за умови вдосконалення.',
            ],
            [
                'question' => 'Diplomats {a1} negotiate bilateral agreements, and they {a2} bypass parliamentary approval under emergency protocols.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'diplomatic authority',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Повноваження вести переговори.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'emergency prerogative',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право обійти схвалення у надзвичайних ситуаціях.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} divulge classified information without facing prosecution.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'legal prohibition',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Юридична заборона розголошувати.',
            ],
            [
                'question' => 'Arbitrators {a1} issue binding rulings, but they {a2} impose penalties beyond contractual terms.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'arbitration power',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Повноваження видавати рішення.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'jurisdictional limit',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Обмеження накладати покарання.',
                    ],
                ],
            ],
            [
                'question' => 'Were the regulatory framework revised, startups {a1} innovate without prohibitive compliance costs.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'regulatory conditional',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Можливість інновацій за умови змін.',
            ],
            [
                'question' => 'Legislators {a1} draft bills collaboratively, and they {a2} amend them during committee reviews.',
                'level' => 'C1',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'legislative capability',
                        'concept' => 'present_ability',
                        'example_hint' => 'Наприклад: Можливість створювати законопроєкти.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'amendment prerogative',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право вносити поправки.',
                    ],
                ],
            ],

            // Ability & Permission - C2 Level (10 questions)
            [
                'question' => 'Had Byzantine diplomacy prevailed, the empire {a1} forestalled its eventual fragmentation.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'options' => ['could have', 'can have', 'may have', 'must have'],
                'answer_index' => 0,
                'verb_hint' => 'historical counterfactual',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Альтернативний історичний сценарій.',
            ],
            [
                'question' => 'Prosecutors {a1} indict suspects based on circumstantial evidence, but they {a2} compel testimony from co-conspirators immunized by plea bargains.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'prosecutorial discretion',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право висувати обвинувачення.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'immunity constraint',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Обмеження через імунітет.',
                    ],
                ],
            ],
            [
                'question' => 'Before quantum cryptography, adversaries {a1} theoretically decrypt even the most sophisticated ciphers.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'theoretical past capability',
                'concept' => 'past_ability',
                'example_hint' => 'Наприклад: Теоретична можливість у минулому.',
            ],
            [
                'question' => 'Adjudicators {a1} interpret statutory ambiguities, and they {a2} extrapolate precedents to unprecedented scenarios.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'judicial interpretive power',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Судова повноваження тлумачити.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'precedential extension',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право екстраполювати прецеденти.',
                    ],
                ],
            ],
            [
                'question' => 'Were geopolitical constraints lifted, multinationals {a1} consolidate global supply chains without regulatory friction.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'options' => ['could', 'can', 'may', 'must'],
                'answer_index' => 0,
                'verb_hint' => 'hypothetical economic potential',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Потенціал за відсутності обмежень.',
            ],
            [
                'question' => 'Curators {a1} acquire artifacts from contested provenance, but they {a2} exhibit them without disclosing ethical controversies.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'curatorial prerogative',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право придбати спірні артефакти.',
                    ],
                    'a2' => [
                        'options' => ["can't", 'could', 'may', 'should'],
                        'answer_index' => 0,
                        'verb_hint' => 'ethical obligation',
                        'concept' => 'lack_permission',
                        'example_hint' => 'Наприклад: Заборона виставляти без розкриття.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} invoke attorney-client privilege to obstruct legitimate judicial inquiries.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'options' => ["can't", 'could', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'legal misuse prohibition',
                'concept' => 'lack_permission',
                'example_hint' => 'Наприклад: Заборона зловживати привілеєм.',
            ],
            [
                'question' => 'Regulators {a1} mandate stress tests for systemic institutions, and they {a2} impose capital surcharges on underperforming entities.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'regulatory authority',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Повноваження вимагати тести.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'enforcement discretion',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право накладати надбавки.',
                    ],
                ],
            ],
            [
                'question' => 'Had computational linguistics matured earlier, researchers {a1} decode ancient scripts decades ago.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'options' => ['could have', 'can have', 'may have', 'must have'],
                'answer_index' => 0,
                'verb_hint' => 'technological counterfactual',
                'concept' => 'conditional_ability',
                'example_hint' => 'Наприклад: Альтернативна історія науки.',
            ],
            [
                'question' => 'Whistleblowers {a1} report malfeasance anonymously, and they {a2} seek legal protection under statutory safeguards.',
                'level' => 'C2',
                'theme' => 'Ability & Permission',
                'parts' => [
                    'a1' => [
                        'options' => ['can', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'reporting capability',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Можливість анонімного звітування.',
                    ],
                    'a2' => [
                        'options' => ['may', 'can', 'must', 'will'],
                        'answer_index' => 0,
                        'verb_hint' => 'legal recourse',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Право шукати правовий захист.',
                    ],
                ],
            ],

            // Obligation & Necessity - A1 Level (10 questions)
            [
                'question' => 'I {a1} go to school every day.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'obligation or necessity',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Щоденний обов\'язок.',
            ],
            [
                'question' => 'You {a1} wear a helmet when riding a bike.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'safety rule',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Правило безпеки.',
            ],
            [
                'question' => 'We {a1} be quiet in the library.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'library rule',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Правило поведінки.',
            ],
            [
                'question' => 'She {a1} do her homework.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'school obligation',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Шкільний обов\'язок.',
            ],
            [
                'question' => 'They {a1} clean their room.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'household duty',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Домашній обов\'язок.',
            ],
            [
                'question' => 'You {a1} wash your hands before eating.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['should', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'health advice',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Гігієнічна рекомендація.',
            ],
            [
                'question' => 'He {a1} take the medicine.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['should', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'medical advice',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Медична рекомендація.',
            ],
            [
                'question' => 'We {a1} be late for class.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ["shouldn't", 'can', 'may', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'negative advice',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Рекомендація не спізнюватись.',
            ],
            [
                'question' => 'You {a1} bring food to class.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ["mustn't", 'can', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'classroom prohibition',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Заборона їжі в класі.',
            ],
            [
                'question' => 'I {a1} finish my work today.',
                'level' => 'A1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'work necessity',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Необхідність завершити роботу.',
            ],

            // Obligation & Necessity - A2 Level (10 questions)
            [
                'question' => 'Students {a1} attend all classes, and they {a2} submit assignments on time.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'attendance requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок відвідувати.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'may', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'deadline expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація здавати вчасно.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} pay the bill by Friday.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'payment requirement',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Необхідність оплати до дати.',
            ],
            [
                'question' => 'Employees {a1} wear uniforms, but they {a2} bring their own shoes.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'dress code requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок носити форму.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'lack of requirement',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Необов\'язковість приносити взуття.',
                    ],
                ],
            ],
            [
                'question' => 'We {a1} eat in the office.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'options' => ["mustn't", 'can', 'may', 'should'],
                'answer_index' => 0,
                'verb_hint' => 'office rule',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Заборона їсти в офісі.',
            ],
            [
                'question' => 'Visitors {a1} register at reception, and they {a2} wear ID badges.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'registration rule',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок реєструватись.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'may', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'identification expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація носити бейдж.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} wear glasses for reading.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'options' => ['has to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'personal necessity',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Фізична необхідність.',
            ],
            [
                'question' => 'Passengers {a1} fasten seatbelts during takeoff, but they {a2} keep them on throughout the flight.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'safety regulation',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок під час зльоту.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'optional continuous action',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Необов\'язковість весь політ.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} return the books by Monday.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'options' => ['should', 'can', 'may', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'library recommendation',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Рекомендація повернути вчасно.',
            ],
            [
                'question' => 'Drivers {a1} stop at red lights, and they {a2} honk unnecessarily.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'traffic law',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок зупинятись.',
                    ],
                    'a2' => [
                        'options' => ["mustn't", 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'noise prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона сигналити без потреби.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} wake up early tomorrow.',
                'level' => 'A2',
                'theme' => 'Obligation & Necessity',
                'options' => ['has to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'future necessity',
                'concept' => 'future_requirement',
                'example_hint' => 'Наприклад: Майбутня необхідність.',
            ],

            // Obligation & Necessity - B1 Level (10 questions)
            [
                'question' => 'Tenants {a1} pay rent by the first of the month, and they {a2} notify management of any damages.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'contractual obligation',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Договірний обов\'язок оплати.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'may', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'responsibility expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Відповідальність повідомляти.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} complete safety training before operating equipment.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'workplace requirement',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Робоче правило.',
            ],
            [
                'question' => 'Members {a1} renew membership annually, but they {a2} attend every meeting.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'membership rule',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Вимога поновлювати членство.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'optional attendance',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Необов\'язкова присутність.',
                    ],
                ],
            ],
            [
                'question' => 'Athletes {a1} follow strict diets during training season.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'athletic requirement',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Спортивна дисципліна.',
            ],
            [
                'question' => 'Students {a1} cite sources in academic papers, and they {a2} avoid plagiarism.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'academic integrity rule',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Академічна вимога цитувати.',
                    ],
                    'a2' => [
                        'options' => ['mustn\'t', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'plagiarism prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона плагіату.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} take a break if you feel tired.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'options' => ['should', 'can', 'must', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'health recommendation',
                'concept' => 'personal_duty',
                'example_hint' => 'Наприклад: Рекомендація для здоров\'я.',
            ],
            [
                'question' => 'Contractors {a1} submit invoices monthly, but they {a2} provide daily updates.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'billing requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок подавати рахунки.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'optional reporting',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Необов\'язкові оновлення.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} apply for a visa before traveling.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'options' => ['has to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'travel requirement',
                'concept' => 'future_requirement',
                'example_hint' => 'Наприклад: Необхідність візи.',
            ],
            [
                'question' => 'Participants {a1} register in advance, and they {a2} confirm attendance by email.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'registration rule',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок зареєструватись.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'may', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'confirmation expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація підтвердити.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} share confidential information with unauthorized persons.',
                'level' => 'B1',
                'theme' => 'Obligation & Necessity',
                'options' => ["mustn't", 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'confidentiality rule',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Заборона розголошувати.',
            ],

            // Obligation & Necessity - B2 Level (10 questions)
            [
                'question' => 'Applicants {a1} submit all documents by the deadline, and they {a2} provide references.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'application requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок подати документи.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'may', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'supporting documentation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація надати рекомендації.',
                    ],
                ],
            ],
            [
                'question' => 'Employees {a1} attend mandatory training, but they {a2} participate in optional workshops.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['have to', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'mandatory attendance',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язкове навчання.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'voluntary participation',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Добровільна участь.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} comply with industry regulations to maintain certification.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'regulatory compliance',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Необхідність дотримуватись регуляцій.',
            ],
            [
                'question' => 'Researchers {a1} obtain ethical approval before conducting studies, and they {a2} disclose conflicts of interest.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'ethical requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок отримати схвалення.',
                    ],
                    'a2' => [
                        'options' => ['mustn\'t', 'should', 'can', 'may'],
                        'answer_index' => 1,
                        'verb_hint' => 'disclosure expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація розкривати конфлікти.',
                    ],
                ],
            ],
            [
                'question' => 'Companies {a1} file annual reports with regulatory bodies.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'corporate obligation',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Корпоративний обов\'язок.',
            ],
            [
                'question' => 'Candidates {a1} pass a background check, but they {a2} have prior experience.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'hiring requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язкова перевірка.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 0,
                        'verb_hint' => 'experience not required',
                        'concept' => 'absence_of_obligation',
                        'example_hint' => 'Наприклад: Досвід необов\'язковий.',
                    ],
                ],
            ],
            [
                'question' => 'Physicians {a1} maintain patient confidentiality at all times.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'medical ethics',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Медична етика.',
            ],
            [
                'question' => 'Developers {a1} document code changes, and they {a2} conduct peer reviews.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['should', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'best practice',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація документувати.',
                    ],
                    'a2' => [
                        'options' => ['should', 'can', 'must', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'quality assurance',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація переглядати код.',
                    ],
                ],
            ],
            [
                'question' => 'You {a1} violate copyright laws when using published materials.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'options' => ["mustn't", 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'legal prohibition',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Юридична заборона.',
            ],
            [
                'question' => 'Organizations {a1} implement data protection measures, and they {a2} notify authorities of breaches.',
                'level' => 'B2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'data protection law',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок захищати дані.',
                    ],
                    'a2' => [
                        'options' => ['must', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'breach notification',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок повідомляти про порушення.',
                    ],
                ],
            ],

            // Obligation & Necessity - C1 Level (10 questions)
            [
                'question' => 'Institutions {a1} demonstrate fiscal accountability, and they {a2} undergo periodic audits.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'accountability requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Фінансова відповідальність.',
                    ],
                    'a2' => [
                        'options' => ['must', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'audit obligation',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язкові перевірки.',
                    ],
                ],
            ],
            [
                'question' => 'Practitioners {a1} adhere to professional codes of conduct.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'professional standards',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Професійні стандарти.',
            ],
            [
                'question' => 'Authorities {a1} enforce regulations, but they {a2} grant exemptions arbitrarily.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'enforcement duty',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок виконувати.',
                    ],
                    'a2' => [
                        'options' => ["mustn't", 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'arbitrary prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона свавілля.',
                    ],
                ],
            ],
            [
                'question' => 'Corporations {a1} disclose material information to shareholders.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'corporate governance',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Корпоративна прозорість.',
            ],
            [
                'question' => 'Investigators {a1} secure evidence properly, and they {a2} maintain chain of custody.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence handling',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Правила збереження доказів.',
                    ],
                    'a2' => [
                        'options' => ['must', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'custody requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок зберігати ланцюг.',
                    ],
                ],
            ],
            [
                'question' => 'Signatories {a1} honor treaty obligations regardless of domestic pressures.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'treaty commitment',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Міжнародні зобов\'язання.',
            ],
            [
                'question' => 'Executives {a1} declare conflicts of interest, but they {a2} recuse themselves from related decisions.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'disclosure mandate',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок декларувати.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'should', "can't"],
                        'answer_index' => 1,
                        'verb_hint' => 'recusal expectation',
                        'concept' => 'personal_duty',
                        'example_hint' => 'Наприклад: Рекомендація самоусунутись.',
                    ],
                ],
            ],
            [
                'question' => 'Litigants {a1} submit evidence within statutory timeframes.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'procedural deadline',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Процесуальні терміни.',
            ],
            [
                'question' => 'Trustees {a1} act in beneficiaries\' best interests, and they {a2} prioritize personal gains.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'fiduciary duty',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Фідуціарний обов\'язок.',
                    ],
                    'a2' => [
                        'options' => ["mustn't", 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'self-dealing prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона особистого збагачення.',
                    ],
                ],
            ],
            [
                'question' => 'Regulators {a1} intervene when systemic risks emerge.',
                'level' => 'C1',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'regulatory mandate',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Регуляторний обов\'язок.',
            ],

            // Obligation & Necessity - C2 Level (10 questions)
            [
                'question' => 'Adjudicators {a1} render impartial judgments, and they {a2} disregard extrajudicial considerations.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'judicial impartiality',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Судова неупередженість.',
                    ],
                    'a2' => [
                        'options' => ['must', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'external influence prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок ігнорувати вплив.',
                    ],
                ],
            ],
            [
                'question' => 'Central banks {a1} maintain price stability within their mandates.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'monetary policy obligation',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Мандат центробанку.',
            ],
            [
                'question' => 'Diplomats {a1} uphold sovereign immunity, but they {a2} exploit it for illicit activities.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'immunity respect',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Повага до імунітету.',
                    ],
                    'a2' => [
                        'options' => ["mustn't", 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'abuse prohibition',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона зловживання.',
                    ],
                ],
            ],
            [
                'question' => 'Custodians of classified materials {a1} implement compartmentalization protocols.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'security protocol',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Секретні протоколи.',
            ],
            [
                'question' => 'Arbitrators {a1} apply governing law, and they {a2} disclose ex parte communications.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'jurisdictional mandate',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок застосовувати закон.',
                    ],
                    'a2' => [
                        'options' => ['must', 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'transparency requirement',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок розкривати комунікації.',
                    ],
                ],
            ],
            [
                'question' => 'Sovereigns {a1} respect jus cogens norms irrespective of consent.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'peremptory norms',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Імперативні норми міжнародного права.',
            ],
            [
                'question' => 'Fiduciaries {a1} avoid self-dealing transactions, but they {a2} pursue ventures with prior disclosure.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'conflict avoidance',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Уникнення конфлікту інтересів.',
                    ],
                    'a2' => [
                        'options' => ["don't have to", 'must', 'may', "can't"],
                        'answer_index' => 2,
                        'verb_hint' => 'disclosure-based permission',
                        'concept' => 'formal_permission',
                        'example_hint' => 'Наприклад: Можливість за умови розкриття.',
                    ],
                ],
            ],
            [
                'question' => 'Tribunals {a1} afford due process to all parties.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'options' => ['have to', 'can', 'may', 'might'],
                'answer_index' => 0,
                'verb_hint' => 'procedural fairness',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Процесуальна справедливість.',
            ],
            [
                'question' => 'Negotiators {a1} represent mandating principals faithfully, and they {a2} exceed delegated authority.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'can', 'may', 'might'],
                        'answer_index' => 0,
                        'verb_hint' => 'agency duty',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Обов\'язок представництва.',
                    ],
                    'a2' => [
                        'options' => ["mustn't", 'can', 'should', 'may'],
                        'answer_index' => 0,
                        'verb_hint' => 'authority limit',
                        'concept' => 'external_obligation',
                        'example_hint' => 'Наприклад: Заборона перевищення повноважень.',
                    ],
                ],
            ],
            [
                'question' => 'Custodians {a1} safeguard entrusted assets with prudence commensurate to their nature.',
                'level' => 'C2',
                'theme' => 'Obligation & Necessity',
                'options' => ['must', 'can', 'should', 'may'],
                'answer_index' => 0,
                'verb_hint' => 'prudential standard',
                'concept' => 'external_obligation',
                'example_hint' => 'Наприклад: Стандарт обачності.',
            ],

            // Possibility & Deduction - A1 Level (10 questions)
            [
                'question' => 'It {a1} rain tomorrow.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'future possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Невпевненість щодо погоди.',
            ],
            [
                'question' => 'She {a1} be at home now.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'present possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Припущення про місцезнаходження.',
            ],
            [
                'question' => 'He {a1} be tired; he worked all day.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['must', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'logical conclusion',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Впевнений висновок з доказів.',
            ],
            [
                'question' => 'They {a1} come to the party.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'uncertain future',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Невпевненість у плані.',
            ],
            [
                'question' => 'It {a1} be true; I saw it myself.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['must', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'strong certainty',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Впевненість через особистий досвід.',
            ],
            [
                'question' => 'She {a1} know the answer.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'uncertain knowledge',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Припущення про знання.',
            ],
            [
                'question' => 'He {a1} be sick; he looks pale.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['must', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'visible evidence',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Висновок з зовнішніх ознак.',
            ],
            [
                'question' => 'They {a1} arrive late.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'possible timing',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива затримка.',
            ],
            [
                'question' => 'This {a1} be the right address.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['must', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'confident guess',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Впевнений здогад.',
            ],
            [
                'question' => 'We {a1} go to the beach if it\'s sunny.',
                'level' => 'A1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'conditional possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: План залежить від погоди.',
            ],

            // Possibility & Deduction - A2-C2 (condensed to meet 60 questions requirement)
            // A2 Level (10 questions)
            [
                'question' => 'The lights are on, so she {a1} be home, and she {a2} be expecting us.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['must', 'might', 'may', 'could'],
                        'answer_index' => 0,
                        'verb_hint' => 'evidence-based deduction',
                        'concept' => 'strong_deduction',
                        'example_hint' => 'Наприклад: Світло як доказ присутності.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'speculation',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Припущення про очікування.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} be the new manager; everyone is talking about him.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'options' => ['must', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'strong inference',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Висновок з розмов.',
            ],
            [
                'question' => 'The project {a1} succeed, but it {a2} face delays.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'possible success',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий успіх.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'potential problem',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі затримки.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} have forgotten about the meeting.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'possible past',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива причина відсутності.',
            ],
            [
                'question' => 'The weather {a1} improve tomorrow, and we {a2} go hiking.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'weather prediction',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: Прогноз погоди.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'conditional plan',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: План залежить від погоди.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} be right about that.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'options' => ['could', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'possibility of correctness',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива правильність думки.',
            ],
            [
                'question' => 'The keys {a1} be in your bag, or they {a2} be on the table.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'first location guess',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Перше припущення.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'alternative location',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Альтернативне місце.',
                    ],
                ],
            ],
            [
                'question' => 'They {a1} have left early.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'past possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива причина відсутності.',
            ],
            [
                'question' => 'The store {a1} open late today, so we {a2} still shop there.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'uncertain schedule',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Невпевненість у розкладі.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'shopping possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливість купити.',
                    ],
                ],
            ],
            [
                'question' => 'This {a1} be the best solution to the problem.',
                'level' => 'A2',
                'theme' => 'Possibility & Deduction',
                'options' => ['could', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'potential optimality',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливе найкраще рішення.',
            ],

            // B1 Level (10 questions)
            [
                'question' => 'Given the traffic, they {a1} arrive late, and we {a2} start without them.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'traffic possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Затримка через пробки.',
                    ],
                    'a2' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'consequent action',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливість почати без них.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} have taken the wrong train; she hasn\'t called yet.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'past speculation',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Припущення про помилку.',
            ],
            [
                'question' => 'The proposal {a1} succeed if properly presented, but it {a2} fail due to budget concerns.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'conditional success',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Успіх за умови презентації.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'budget risk',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Ризик через бюджет.',
                    ],
                ],
            ],
            [
                'question' => 'His absence {a1} mean he\'s uninterested in the position.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'options' => ['could', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'interpretation possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливе пояснення відсутності.',
            ],
            [
                'question' => 'The experiment {a1} yield unexpected results, and we {a2} need to revise our hypothesis.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'scientific uncertainty',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Непередбачувані результати.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'potential revision',
                        'concept' => 'probable_consequence',
                        'example_hint' => 'Наприклад: Можлива потреба змін.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} have misunderstood the instructions.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'past misunderstanding',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливе нерозуміння.',
            ],
            [
                'question' => 'The defendant {a1} be innocent based on the evidence, but the jury {a2} still convict.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'innocence possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можлива невинуватість.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'jury decision risk',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе рішення журі.',
                    ],
                ],
            ],
            [
                'question' => 'They {a1} have already left for the airport.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'past action possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливий вже від\'їзд.',
            ],
            [
                'question' => 'The market {a1} stabilize next quarter, or it {a2} experience further volatility.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'stabilization possibility',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: Можлива стабілізація.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'volatility alternative',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: Альтернативна нестабільність.',
                    ],
                ],
            ],
            [
                'question' => 'This technology {a1} revolutionize the industry.',
                'level' => 'B1',
                'theme' => 'Possibility & Deduction',
                'options' => ['could', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'transformative potential',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Потенціал технології.',
            ],

            // B2 Level (10 questions)
            [
                'question' => 'The anomaly {a1} indicate a systemic flaw, and further testing {a2} reveal its scope.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'diagnostic possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий дефект системи.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'discovery potential',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе розкриття масштабу.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} have anticipated this outcome; the signs were evident.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'options' => ['should', 'might', 'may', 'could'],
                'answer_index' => 0,
                'verb_hint' => 'expected foresight',
                'concept' => 'strong_deduction',
                'example_hint' => 'Наприклад: Очікувана передбачуваність.',
            ],
            [
                'question' => 'The policy shift {a1} trigger market corrections, or it {a2} stabilize investor confidence.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'market reaction possibility',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: Можливі корекції.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'confidence alternative',
                        'concept' => 'speculative_future',
                        'example_hint' => 'Наприклад: Альтернативна стабілізація.',
                    ],
                ],
            ],
            [
                'question' => 'The discrepancy {a1} suggest data corruption rather than intentional manipulation.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'tentative interpretation',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива корупція даних.',
            ],
            [
                'question' => 'The findings {a1} challenge established theories, and researchers {a2} need to replicate the study.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'paradigm challenge',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий виклик теоріям.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'verification necessity',
                        'concept' => 'probable_consequence',
                        'example_hint' => 'Наприклад: Можлива потреба повторити.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} have overlooked critical variables in his analysis.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'analytical oversight',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливий пропуск змінних.',
            ],
            [
                'question' => 'The acquisition {a1} enhance competitiveness, but it {a2} raise antitrust concerns.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'competitive benefit',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе підвищення конкурентності.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'regulatory risk',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі антимонопольні питання.',
                    ],
                ],
            ],
            [
                'question' => 'The symptoms {a1} indicate a rare condition requiring specialist consultation.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'diagnostic possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливий рідкісний стан.',
            ],
            [
                'question' => 'The reforms {a1} improve efficiency, or they {a2} encounter bureaucratic resistance.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'improvement potential',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе покращення ефективності.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'implementation obstacle',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий опір бюрократії.',
                    ],
                ],
            ],
            [
                'question' => 'The evidence {a1} support their hypothesis or refute it entirely.',
                'level' => 'B2',
                'theme' => 'Possibility & Deduction',
                'options' => ['could', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'dual possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Подвійний потенціал доказів.',
            ],

            // C1 Level (10 questions)
            [
                'question' => 'The correlation {a1} imply causation, though confounding variables {a2} obscure the relationship.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'causal inference possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий причинно-наслідковий зв\'язок.',
                    ],
                    'a2' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'confounding factor',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі змішувальні фактори.',
                    ],
                ],
            ],
            [
                'question' => 'The precedent {a1} not apply given jurisdictional differences.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'legal inapplicability',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива неастосовність прецеденту.',
            ],
            [
                'question' => 'The intervention {a1} mitigate systemic risks, but unintended consequences {a2} emerge.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'risk mitigation potential',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе пом\'якшення ризиків.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'side effects possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі побічні наслідки.',
                    ],
                ],
            ],
            [
                'question' => 'The anomaly {a1} have resulted from calibration errors rather than genuine phenomena.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'error attribution',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива помилка калібрування.',
            ],
            [
                'question' => 'The restructuring {a1} streamline operations, yet it {a2} disrupt established workflows.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'efficiency gain',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе спрощення операцій.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'workflow disruption',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе порушення процесів.',
                    ],
                ],
            ],
            [
                'question' => 'The defendant {a1} have acted under duress, which {a2} constitute a mitigating factor.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'legal defense possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливий примус як обставина.',
            ],
            [
                'question' => 'The treaty provisions {a1} be interpreted broadly, or they {a2} be construed narrowly depending on context.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'broad interpretation',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе широке тлумачення.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'narrow construction',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе вузьке трактування.',
                    ],
                ],
            ],
            [
                'question' => 'The data {a1} suggest emerging trends, though sampling limitations {a2} compromise validity.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'trend indication',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливі нові тенденції.',
            ],
            [
                'question' => 'The strategic pivot {a1} redefine competitive dynamics, but incumbent advantages {a2} persist.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'competitive redefinition',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі зміни динаміки.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'advantage retention',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе збереження переваг.',
                    ],
                ],
            ],
            [
                'question' => 'The methodology {a1} have introduced bias affecting outcome reliability.',
                'level' => 'C1',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'methodological concern',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливе упередження методу.',
            ],

            // C2 Level (10 questions)
            [
                'question' => 'The jurisprudential shift {a1} herald doctrinal evolution, though precedential inertia {a2} forestall reform.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'doctrinal change possibility',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можлива еволюція доктрини.',
                    ],
                    'a2' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'inertial resistance',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий опір інерції.',
                    ],
                ],
            ],
            [
                'question' => 'The epistemic assumptions {a1} not withstand rigorous scrutiny.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'foundational vulnerability',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива вразливість припущень.',
            ],
            [
                'question' => 'The intervention {a1} engender perverse incentives, while ostensible safeguards {a2} prove inadequate.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'unintended incentive creation',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливі спотворені стимули.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'safeguard insufficiency',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можлива неадекватність захисту.',
                    ],
                ],
            ],
            [
                'question' => 'The anomaly {a1} have arisen from quantum fluctuations rather than experimental artifacts.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'quantum explanation possibility',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливе квантове походження.',
            ],
            [
                'question' => 'The paradigmatic transformation {a1} reconfigure epistemic frameworks, yet entrenched orthodoxies {a2} resist assimilation.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'framework reconfiguration',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можлива реконфігурація рамок.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'orthodoxy resistance',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливий опір ортодоксії.',
                    ],
                ],
            ],
            [
                'question' => 'The appellant\'s contentions {a1} lack merit given jurisprudential constraints.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'legal merit deficiency',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива відсутність обгрунтування.',
            ],
            [
                'question' => 'The normative implications {a1} transcend doctrinal boundaries, or they {a2} remain circumscribed by precedential limits.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['may', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'boundary transcendence',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе перевищення меж.',
                    ],
                    'a2' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'limit circumscription',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе обмеження межами.',
                    ],
                ],
            ],
            [
                'question' => 'The empirical discrepancies {a1} stem from methodological heterogeneity obscuring causal inference.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'options' => ['might', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'heterogeneity attribution',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можлива методологічна гетерогенність.',
            ],
            [
                'question' => 'The geopolitical realignment {a1} destabilize equilibria, while institutional mechanisms {a2} attenuate shocks.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'parts' => [
                    'a1' => [
                        'options' => ['could', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'equilibrium destabilization',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можлива дестабілізація рівноваги.',
                    ],
                    'a2' => [
                        'options' => ['might', 'must', 'should', 'would'],
                        'answer_index' => 0,
                        'verb_hint' => 'shock attenuation',
                        'concept' => 'tentative_possibility',
                        'example_hint' => 'Наприклад: Можливе пом\'якшення потрясінь.',
                    ],
                ],
            ],
            [
                'question' => 'The counterfactual scenario {a1} have yielded divergent outcomes absent exogenous interventions.',
                'level' => 'C2',
                'theme' => 'Possibility & Deduction',
                'options' => ['may', 'must', 'should', 'would'],
                'answer_index' => 0,
                'verb_hint' => 'counterfactual divergence',
                'concept' => 'tentative_possibility',
                'example_hint' => 'Наприклад: Можливі альтернативні результати.',
            ],
        ];
    }
}
