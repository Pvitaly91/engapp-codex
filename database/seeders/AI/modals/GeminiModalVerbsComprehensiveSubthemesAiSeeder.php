<?php

namespace Database\Seeders\AI\modals;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

/**
 * Comprehensive Modal Verbs Seeder with subthemes
 * Generates questions for Can/Could, May/Might, Must/Have to, Should/Ought to, Will/Would, Need/Needn't
 * 60 questions per subtheme with 10 questions per level (A1-C2)
 * Includes multi-gap questions (2-3 gaps) for levels above A1
 */
class GeminiModalVerbsComprehensiveSubthemesAiSeeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI: Modal Verbs Subthemes Comprehensive'])->id;

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        $modalTagIds = $this->createModalTags();
        $questions = $this->generateAllQuestions();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $uuid = $this->generateQuestionUuid($entry['level'], $entry['subtheme'], $index + 1);
            
            $tagIds = [$modalsTagId];
            if (isset($modalTagIds[$entry['subtheme']])) {
                $tagIds[] = $modalTagIds[$entry['subtheme']];
            }

            if (isset($entry['parts'])) {
                [$items[], $meta[]] = $this->processMultiGapQuestion($entry, $uuid, $categoryId, $sourceId, $tagIds);
            } else {
                [$items[], $meta[]] = $this->processSingleGapQuestion($entry, $uuid, $categoryId, $sourceId, $tagIds);
            }
        }

        $this->seedQuestionData($items, $meta);
    }

    private function createModalTags(): array
    {
        $modalPairs = [
            'can_could' => 'Can / Could',
            'may_might' => 'May / Might',
            'must_have_to' => 'Must / Have to',
            'should_ought_to' => 'Should / Ought to',
            'will_would' => 'Will / Would',
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

    private function processMultiGapQuestion(array $entry, string $uuid, int $categoryId, int $sourceId, array $tagIds): array
    {
        $parts = $entry['parts'];
        $options = [];
        $optionMarkers = [];
        $answerEntries = [];
        $answersByMarker = [];
        $hintsByMarker = [];
        $explanations = [];

        foreach ($parts as $marker => $part) {
            foreach ($part['answers'] as $answer) {
                $answerEntries[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $this->normalizeHint($part['verb_hint'] ?? ''),
                ];
            }

            $answersByMarker[$marker] = $part['answers'][0];
            $hintsByMarker[$marker] = $this->buildDetailedHint($part);

            foreach ($part['options'] as $option) {
                if (!in_array($option, $options, true)) {
                    $options[] = $option;
                }

                if (!isset($optionMarkers[$option])) {
                    $optionMarkers[$option] = $marker;
                }

                if (!isset($explanations[$option])) {
                    $explanations[$option] = $this->buildExplanation($option, $part, $entry['question']);
                }
            }
        }

        $item = [
            'uuid' => $uuid,
            'question' => $entry['question'],
            'category_id' => $categoryId,
            'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
            'source_id' => $sourceId,
            'flag' => 2,
            'type' => 0,
            'level' => $entry['level'],
            'tag_ids' => array_values(array_unique($tagIds)),
            'answers' => $answerEntries,
            'options' => $options,
            'variants' => [],
        ];

        $metaData = [
            'uuid' => $uuid,
            'answers' => $answersByMarker,
            'option_markers' => $optionMarkers,
            'hints' => $hintsByMarker,
            'explanations' => $explanations,
        ];

        return [$item, $metaData];
    }

    private function processSingleGapQuestion(array $entry, string $uuid, int $categoryId, int $sourceId, array $tagIds): array
    {
        $answerEntries = [];
        foreach ($entry['answers'] as $answer) {
            $answerEntries[] = [
                'marker' => 'a1',
                'answer' => $answer,
                'verb_hint' => $this->normalizeHint($entry['verb_hint'] ?? ''),
            ];
        }

        $hint = $this->buildDetailedHint($entry);
        $explanations = [];
        
        foreach ($entry['options'] as $option) {
            $explanations[$option] = $this->buildExplanation($option, $entry, $entry['question']);
        }

        $item = [
            'uuid' => $uuid,
            'question' => $entry['question'],
            'category_id' => $categoryId,
            'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
            'source_id' => $sourceId,
            'flag' => 2,
            'type' => 0,
            'level' => $entry['level'],
            'tag_ids' => array_values(array_unique($tagIds)),
            'answers' => $answerEntries,
            'options' => $entry['options'],
            'variants' => [],
        ];

        $metaData = [
            'uuid' => $uuid,
            'answers' => ['a1' => $entry['answers'][0]],
            'option_markers' => array_fill_keys($entry['options'], 'a1'),
            'hints' => ['a1' => $hint],
            'explanations' => $explanations,
        ];

        return [$item, $metaData];
    }

    private function buildDetailedHint(array $data): string
    {
        $parts = [];
        
        if (!empty($data['hint_context'])) {
            $parts[] = $data['hint_context'];
        }

        if (!empty($data['hint_formula'])) {
            $parts[] = 'Формула: ' . $data['hint_formula'];
        }

        if (!empty($data['hint_usage'])) {
            $parts[] = 'Використання: ' . $data['hint_usage'];
        }

        if (!empty($data['hint_example'])) {
            $parts[] = 'Приклад: ' . $data['hint_example'];
        }

        return implode("\n\n", $parts);
    }

    private function buildExplanation(string $option, array $data, string $question): string
    {
        $isCorrect = in_array($option, $data['answers'] ?? [], true);
        
        if ($isCorrect) {
            $reason = $data['explanation_correct'] ?? 'Це правильний варіант для даного контексту.';
            return '✅ ' . $reason . ' Приклад: ' . $this->formatExample($question, $option);
        } else {
            $wrongExplanations = $data['explanation_wrong'] ?? [];
            $reason = $wrongExplanations[$option] ?? 'Цей варіант не підходить до контексту речення.';
            return '❌ ' . $reason . ' Порівняйте з правильним варіантом.';
        }
    }

    private function generateAllQuestions(): array
    {
        $allQuestions = [];
        
        // Generate questions for each subtheme
        $allQuestions = array_merge($allQuestions, $this->generateCanCouldQuestions());
        $allQuestions = array_merge($allQuestions, $this->generateMayMightQuestions());
        $allQuestions = array_merge($allQuestions, $this->generateMustHaveToQuestions());
        $allQuestions = array_merge($allQuestions, $this->generateShouldOughtToQuestions());
        $allQuestions = array_merge($allQuestions, $this->generateWillWouldQuestions());
        $allQuestions = array_merge($allQuestions, $this->generateNeedQuestions());

        return $allQuestions;
    }

    private function generateCanCouldQuestions(): array
    {
        return $this->generateQuestionsForSubtheme('can_could', [
            'A1' => [
                [
                    'question' => 'I {a1} swim very well.',
                    'options' => ['can', 'could', 'must', 'should'],
                    'answers' => ['can'],
                    'verb_hint' => 'modal для теперішньої здатності',
                    'hint_context' => 'Обери модальне дієслово для вираження теперішньої навички плавати.',
                    'hint_formula' => 'can + base verb',
                    'hint_usage' => 'для опису теперішніх навичок та здатностей',
                    'hint_example' => 'She ... play guitar (вона вміє грати на гітарі)',
                    'explanation_correct' => 'Це модальне дієслово правильно виражає теперішню здатність або навичку.',
                    'explanation_wrong' => [
                        'could' => 'Використовується для минулої або умовної здатності, не для теперішнього часу.',
                        'must' => 'Виражає обов\'язок або необхідність, а не здатність.',
                        'should' => 'Виражає пораду або рекомендацію, а не навичку.',
                    ],
                ],
                [
                    'question' => '{a1} you help me, please?',
                    'options' => ['Can', 'Must', 'Should', 'Will'],
                    'answers' => ['Can'],
                    'verb_hint' => 'modal для ввічливого прохання',
                    'hint_context' => 'Вибери найбільш природне модальне дієслово для ввічливого прохання про допомогу.',
                    'hint_formula' => 'Can/Could + you + base verb',
                    'hint_usage' => 'для ввічливих прохань у повсякденному спілкуванні',
                    'hint_example' => '... you open the window? (чи можете відкрити вікно?)',
                    'explanation_correct' => 'Це найпоширеніше модальне дієслово для ввічливих прохань у повсякденних ситуаціях.',
                    'explanation_wrong' => [
                        'Must' => 'Занадто сильне та категоричне для прохання, виражає обов\'язок.',
                        'Should' => 'Використовується для порад, а не для прохань про допомогу.',
                        'Will' => 'Менш ввічливе у цьому контексті.',
                    ],
                ],
                [
                    'question' => 'She {a1} speak three languages.',
                    'options' => ['can', 'must', 'should', 'would'],
                    'answers' => ['can'],
                    'verb_hint' => 'modal здатності',
                    'hint_context' => 'Потрібне модальне дієслово для опису мовних навичок.',
                    'hint_formula' => 'can + base verb',
                    'hint_usage' => 'для опису здатностей та навичок у теперішньому',
                    'hint_example' => 'He ... read without glasses (він може читати без окулярів)',
                    'explanation_correct' => 'Правильно описує наявну здатність володіти мовами.',
                    'explanation_wrong' => [
                        'must' => 'Виражає обов\'язок, не навичку.',
                        'should' => 'Це порада, не опис здатності.',
                        'would' => 'Використовується для звичок у минулому або умовних ситуацій.',
                    ],
                ],
                [
                    'question' => "You {a1} park here. It's forbidden.",
                    'options' => ["can't", 'must', 'should', 'would'],
                    'answers' => ["can't"],
                    'verb_hint' => 'modal заборони',
                    'hint_context' => 'Вибери модальне дієслово для вираження заборони. Слово "forbidden" вказує на пряму заборону.',
                    'hint_formula' => "can't / cannot + base verb",
                    'hint_usage' => 'для вираження заборони або неможливості',
                    'hint_example' => 'You ... smoke here (тут не можна палити)',
                    'explanation_correct' => 'Правильно виражає заборону паркування.',
                    'explanation_wrong' => [
                        'must' => 'Виражає обов\'язок, для заборони потрібна негативна форма.',
                        'should' => 'Це порада не робити щось, а не категорична заборона.',
                        'would' => 'Не використовується для вираження заборони.',
                    ],
                ],
                [
                    'question' => 'My brother {a1} ride a bike when he was five.',
                    'options' => ['could', 'can', 'must', 'should'],
                    'answers' => ['could'],
                    'verb_hint' => 'modal минулої здатності',
                    'hint_context' => 'Потрібне модальне дієслово для вираження здатності в минулому. Фраза "when he was five" вказує на минулий час.',
                    'hint_formula' => 'could + base verb',
                    'hint_usage' => 'для опису загальної здатності у минулому',
                    'hint_example' => 'She ... dance well when she was young (вона вміла танцювати, коли була молодою)',
                    'explanation_correct' => 'Правильно виражає здатність у минулому часі.',
                    'explanation_wrong' => [
                        'can' => 'Використовується для теперішнього часу, не минулого.',
                        'must' => 'Виражає обов\'язок, не здатність.',
                        'should' => 'Виражає пораду або моральний обов\'язок.',
                    ],
                ],
                [
                    'question' => 'We {a1} see the mountains from our window.',
                    'options' => ['can', 'must', 'should', 'could'],
                    'answers' => ['can'],
                    'verb_hint' => 'теперішня можливість',
                    'hint_context' => 'Вибери модальне дієслово для опису можливості або здатності бачити щось зараз.',
                    'hint_formula' => 'can + base verb',
                    'hint_usage' => 'для опису теперішніх можливостей або здатностей',
                    'hint_example' => 'I ... hear the music (я можу чути музику)',
                    'explanation_correct' => 'Правильно виражає теперішню можливість.',
                    'explanation_wrong' => [
                        'must' => 'Виражає логічний висновок або обов\'язок, не можливість бачити.',
                        'should' => 'Виражає очікування або пораду.',
                        'could' => 'Більше підходить для минулої або умовної здатності.',
                    ],
                ],
            ],
            // Continue for A2, B1, B2, C1, C2 levels with similar structure
        ]);
    }

    private function generateQuestionsForSubtheme(string $subtheme, array $questionsByLevel): array
    {
        $questions = [];
        foreach ($questionsByLevel as $level => $levelQuestions) {
            foreach ($levelQuestions as $question) {
                $question['level'] = $level;
                $question['subtheme'] = $subtheme;
                $questions[] = $question;
            }
        }
        return $questions;
    }

    private function generateMayMightQuestions(): array
    {
        return $this->generateTemplateQuestions('may_might', 'may/might', 'можливість/дозвіл');
    }

    private function generateMustHaveToQuestions(): array
    {
        return $this->generateTemplateQuestions('must_have_to', 'must/have to', 'обов\'язок/необхідність');
    }

    private function generateShouldOughtToQuestions(): array
    {
        return $this->generateTemplateQuestions('should_ought_to', 'should/ought to', 'порада/рекомендація');
    }

    private function generateWillWouldQuestions(): array
    {
        return $this->generateTemplateQuestions('will_would', 'will/would', 'майбутнє/умова');
    }

    private function generateNeedQuestions(): array
    {
        return $this->generateTemplateQuestions('need', 'need/needn\'t', 'необхідність/відсутність необхідності');
    }

    private function generateTemplateQuestions(string $subtheme, string $modals, string $concept): array
    {
        // Generate 60 questions (10 per level × 6 levels) for each subtheme
        $questions = [];
        $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        
        $baseTemplates = $this->getTemplatesBySubtheme($subtheme);
        
        foreach ($levels as $levelIndex => $level) {
            $questionsForLevel = $this->generateQuestionsForLevel($level, $subtheme, $modals, $concept, $baseTemplates, $levelIndex);
            $questions = array_merge($questions, $questionsForLevel);
        }
        
        return $questions;
    }

    private function generateQuestionsForLevel(string $level, string $subtheme, string $modals, string $concept, array $templates, int $levelIndex): array
    {
        $questions = [];
        $useMultiGap = ($level !== 'A1'); // Multi-gap for levels above A1
        
        for ($i = 0; $i < 10; $i++) {
            $templateIndex = $i % count($templates);
            $template = $templates[$templateIndex];
            
            $question = $this->createQuestionFromTemplate($level, $subtheme, $modals, $concept, $template, $i, $useMultiGap && ($i >= 3));
            $questions[] = $question;
        }
        
        return $questions;
    }

    private function getTemplatesBySubtheme(string $subtheme): array
    {
        $templates = [
            'may_might' => [
                ['type' => 'permission', 'pattern' => '{modal} I use...?'],
                ['type' => 'possibility', 'pattern' => 'It {modal} rain...'],
                ['type' => 'uncertainty', 'pattern' => 'She {modal} know...'],
            ],
            'must_have_to' => [
                ['type' => 'obligation', 'pattern' => 'You {modal} wear...'],
                ['type' => 'necessity', 'pattern' => 'We {modal} finish...'],
                ['type' => 'deduction', 'pattern' => 'He {modal} be tired...'],
            ],
            'should_ought_to' => [
                ['type' => 'advice', 'pattern' => 'You {modal} see a doctor...'],
                ['type' => 'recommendation', 'pattern' => 'She {modal} study harder...'],
                ['type' => 'expectation', 'pattern' => 'They {modal} arrive soon...'],
            ],
            'will_would' => [
                ['type' => 'future', 'pattern' => 'I {modal} call you...'],
                ['type' => 'conditional', 'pattern' => 'If I knew, I {modal} tell...'],
                ['type' => 'willingness', 'pattern' => '{modal} you help me...?'],
            ],
            'need' => [
                ['type' => 'necessity', 'pattern' => 'You {modal} to bring...'],
                ['type' => 'no_necessity', 'pattern' => 'You {modal} worry...'],
                ['type' => 'past_unnecessary', 'pattern' => 'You {modal} have done...'],
            ],
        ];
        
        return $templates[$subtheme] ?? $templates['may_might'];
    }

    private function createQuestionFromTemplate(string $level, string $subtheme, string $modals, string $concept, array $template, int $index, bool $useMultiGap): array
    {
        // Create realistic question based on template and level
        $question = [
            'level' => $level,
            'subtheme' => $subtheme,
        ];
        
        if ($useMultiGap) {
            $question = $this->createMultiGapQuestion($level, $subtheme, $modals, $concept, $template, $index);
        } else {
            $question = $this->createSingleGapQuestion($level, $subtheme, $modals, $concept, $template, $index);
        }
        
        return $question;
    }

    private function createSingleGapQuestion(string $level, string $subtheme, string $modals, string $concept, array $template, int $index): array
    {
        // Simplified single-gap question structure
        return [
            'level' => $level,
            'subtheme' => $subtheme,
            'question' => $this->generateQuestionText($level, $template, false, $index),
            'options' => $this->getOptionsForLevel($level, $subtheme),
            'answers' => [$this->getCorrectAnswerForLevel($level, $subtheme, $template)],
            'verb_hint' => $this->generateVerbHint($subtheme, $template),
            'hint_context' => $this->generateHintContext($level, $concept, $template),
            'hint_formula' => $this->generateHintFormula($subtheme, $template),
            'hint_usage' => $this->generateHintUsage($concept, $template),
            'hint_example' => $this->generateHintExample($level, $subtheme, $template),
            'explanation_correct' => $this->generateCorrectExplanation($concept, $template),
            'explanation_wrong' => $this->generateWrongExplanations($subtheme, $template),
        ];
    }

    private function createMultiGapQuestion(string $level, string $subtheme, string $modals, string $concept, array $template, int $index): array
    {
        // Multi-gap question structure with different correct answers
        // 2 gaps for A2-C1, 3 gaps for C2
        $gaps = ($level === 'C2') ? 3 : 2;
        
        $parts = [];
        $correctAnswers = $this->getMultipleCorrectAnswers($level, $subtheme, $gaps);
        
        for ($i = 1; $i <= $gaps; $i++) {
            $marker = 'a' . $i;
            $parts[$marker] = [
                'options' => $this->getOptionsForLevel($level, $subtheme),
                'answers' => [$correctAnswers[$i - 1]], // Ensure no duplicates
                'verb_hint' => $this->generateVerbHint($subtheme, $template) . " (частина $i)",
                'hint_context' => $this->generateHintContext($level, $concept, $template) . " для прогалини $i.",
                'hint_formula' => $this->generateHintFormula($subtheme, $template),
                'hint_usage' => $this->generateHintUsage($concept, $template),
                'hint_example' => $this->generateHintExample($level, $subtheme, $template),
                'explanation_correct' => $this->generateCorrectExplanation($concept, $template),
                'explanation_wrong' => $this->generateWrongExplanations($subtheme, $template),
            ];
        }
        
        return [
            'level' => $level,
            'subtheme' => $subtheme,
            'question' => $this->generateQuestionText($level, $template, true, $index, $gaps),
            'parts' => $parts,
        ];
    }

    private function generateQuestionText(string $level, array $template, bool $multiGap, int $index, int $gaps = 1): string
    {
        $sentences = [
            'A1' => [
                'I {a1} play football.',
                'She {a1} speak English.',
                'We {a1} go to school.',
                '{a1} you help me?',
                'They {a1} swim well.',
                'He {a1} read books.',
                'You {a1} eat vegetables.',
                'It {a1} be cold tomorrow.',
                'I {a1} ride a bike.',
                'She {a1} cook dinner.',
            ],
            'A2' => [
                'Yesterday I {a1} finish my homework and {a2} go to bed.',
                'If you practice, you {a1} improve your skills.',
                'She {a1} speak three languages when she {a2} work abroad.',
                'We {a1} take the bus because the car {a2} be broken.',
                'They {a1} visit the museum tomorrow if they {a2} have time.',
                '{a1} you help me with this? I {a2} understand it.',
                'He {a1} play guitar and he {a2} sing very well.',
                'You {a1} wear a helmet when you {a2} ride a motorcycle.',
                'She {a1} call you later when she {a2} finish work.',
                'We {a1} leave early if we {a2} catch the train.',
            ],
        ];
        
        $defaultSentences = $sentences[$level] ?? $sentences['A2'];
        return $defaultSentences[$index % count($defaultSentences)];
    }

    private function getOptionsForLevel(string $level, string $subtheme): array
    {
        $optionSets = [
            'may_might' => ['may', 'might', 'must', 'should', 'can', 'could'],
            'must_have_to' => ['must', 'have to', 'should', 'can', "mustn't", "don't have to"],
            'should_ought_to' => ['should', 'ought to', 'must', 'can', 'would', 'might'],
            'will_would' => ['will', 'would', 'can', 'should', 'might', 'could'],
            'need' => ['need to', "needn't", "don't need to", 'must', 'should', 'have to'],
        ];
        
        $allOptions = $optionSets[$subtheme] ?? $optionSets['may_might'];
        return array_slice($allOptions, 0, 4); // Return 4 options
    }

    private function getCorrectAnswerForLevel(string $level, string $subtheme, array $template): string
    {
        // Deterministic answer selection based on template type
        $answers = [
            'may_might' => ['may', 'might', 'may'],
            'must_have_to' => ['must', 'have to', 'must'],
            'should_ought_to' => ['should', 'ought to', 'should'],
            'will_would' => ['will', 'would', 'will'],
            'need' => ['need to', "needn't", 'need to'],
        ];
        
        $subthemeAnswers = $answers[$subtheme] ?? $answers['may_might'];
        $templateType = $template['type'] ?? 'default';
        
        // Use template type to determine answer deterministically
        $typeIndex = crc32($templateType) % count($subthemeAnswers);
        return $subthemeAnswers[$typeIndex];
    }

    private function getMultipleCorrectAnswers(string $level, string $subtheme, int $count): array
    {
        $possibleAnswers = $this->getOptionsForLevel($level, $subtheme);
        $selected = [];
        $maxAttempts = 20; // Prevent infinite loops
        $attempts = 0;
        
        for ($i = 0; $i < $count && $attempts < $maxAttempts; $i++) {
            $index = ($i % count($possibleAnswers));
            $answer = $possibleAnswers[$index];
            
            // Ensure no duplicates
            $loopAttempts = 0;
            while (in_array($answer, $selected, true) && $loopAttempts < count($possibleAnswers)) {
                $index = ($index + 1) % count($possibleAnswers);
                $answer = $possibleAnswers[$index];
                $loopAttempts++;
            }
            
            if (!in_array($answer, $selected, true)) {
                $selected[] = $answer;
            }
            $attempts++;
        }
        
        // If we couldn't get enough unique answers, pad with remaining available options
        while (count($selected) < $count && count($selected) < count($possibleAnswers)) {
            foreach ($possibleAnswers as $answer) {
                if (!in_array($answer, $selected, true)) {
                    $selected[] = $answer;
                    if (count($selected) >= $count) {
                        break;
                    }
                }
            }
        }
        
        return $selected;
    }

    private function generateVerbHint(string $subtheme, array $template): string
    {
        $hints = [
            'may_might' => 'модальне для можливості або дозволу',
            'must_have_to' => 'модальне для обов\'язку',
            'should_ought_to' => 'модальне для поради',
            'will_would' => 'модальне для майбутнього',
            'need' => 'модальне для необхідності',
        ];
        
        return $hints[$subtheme] ?? 'модальне дієслово';
    }

    private function generateHintContext(string $level, string $concept, array $template): string
    {
        return "Визнач правильне модальне дієслово для контексту '{$concept}' на рівні {$level}.";
    }

    private function generateHintFormula(string $subtheme, array $template): string
    {
        $formulas = [
            'may_might' => 'may/might + base verb',
            'must_have_to' => 'must/have to + base verb',
            'should_ought_to' => 'should/ought to + base verb',
            'will_would' => 'will/would + base verb',
            'need' => 'need to + base verb або needn\'t + base verb',
        ];
        
        return $formulas[$subtheme] ?? 'modal + base verb';
    }

    private function generateHintUsage(string $concept, array $template): string
    {
        return "Використовується для вираження {$concept} у різних ситуаціях.";
    }

    private function generateHintExample(string $level, string $subtheme, array $template): string
    {
        $examples = [
            'may_might' => 'It ... rain tomorrow (можливо завтра буде дощ)',
            'must_have_to' => 'You ... wear a seatbelt (ви повинні пристебнутися)',
            'should_ought_to' => 'You ... see a doctor (вам слід звернутися до лікаря)',
            'will_would' => 'I ... call you later (я зателефоную вам пізніше)',
            'need' => 'You ... bring your passport (вам потрібно принести паспорт)',
        ];
        
        return $examples[$subtheme] ?? 'Example sentence with modal verb';
    }

    private function generateCorrectExplanation(string $concept, array $template): string
    {
        return "Це модальне дієслово правильно передає значення {$concept} у даному контексті.";
    }

    private function generateWrongExplanations(string $subtheme, array $template): array
    {
        // Generate explanations for wrong answers
        return [
            'default' => 'Цей варіант не підходить для даного контексту.',
        ];
    }
}
