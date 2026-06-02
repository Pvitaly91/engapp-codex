<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ModalVerbsComprehensiveAiSeeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    private array $themes = [
        'ability' => [
            'label' => 'Modal Ability and Possibility',
            'tag' => 'Modal Ability Focus',
        ],
        'permission' => [
            'label' => 'Modal Permission and Requests',
            'tag' => 'Modal Permission Focus',
        ],
        'obligation' => [
            'label' => 'Modal Obligation and Necessity',
            'tag' => 'Modal Obligation Focus',
        ],
        'advice' => [
            'label' => 'Modal Advice and Suggestions',
            'tag' => 'Modal Advice Focus',
        ],
        'deduction' => [
            'label' => 'Modal Probability and Deduction',
            'tag' => 'Modal Deduction Focus',
        ],
    ];

    private array $typeConfig = [
        'question' => [
            'label' => 'Interrogative Focus',
            'tag' => 'Modal Question Form',
        ],
        'negative' => [
            'label' => 'Negative Focus',
            'tag' => 'Modal Negative Form',
        ],
        'past' => [
            'label' => 'Past Statement Focus',
            'tag' => 'Modal Past Usage',
        ],
        'present' => [
            'label' => 'Present Statement Focus',
            'tag' => 'Modal Present Usage',
        ],
        'future' => [
            'label' => 'Future Statement Focus',
            'tag' => 'Modal Future Usage',
        ],
    ];

    private array $tenseTagMap = [
        'past' => 'Tense: Past',
        'present' => 'Tense: Present',
        'future' => 'Tense: Future',
    ];

    protected array $modalTagConfig = [
        'can_could' => [
            'name' => 'Can / Could',
            'keywords' => [
                'can',
                'cannot',
                "can't",
                'can have',
                "can't have",
                'could',
                "couldn't",
                'could have',
                "couldn't have",
            ],
        ],
        'may_might' => [
            'name' => 'May / Might',
            'keywords' => [
                'may',
                'may not',
                'may have',
                'may not have',
                'might',
                'might not',
                'might have',
                'might not have',
            ],
        ],
        'must_have_to' => [
            'name' => 'Must / Have to',
            'keywords' => [
                'must',
                "mustn't",
                'must have',
                "mustn't have",
                'have to',
                'has to',
                'had to',
                "don't have to",
                "doesn't have to",
                "didn't have to",
            ],
        ],
        'need_need_to' => [
            'name' => 'Need / Need to',
            'keywords' => [
                'need',
                'need to',
                'needs to',
                'needed to',
                "needn't",
                'need not',
                "needn't have",
            ],
        ],
        'should_ought_to' => [
            'name' => 'Should / Ought to',
            'keywords' => [
                'should',
                "shouldn't",
                'should have',
                "shouldn't have",
                'ought to',
                'ought not to',
                'ought to have',
                'ought not to have',
            ],
        ],
        'will_would' => [
            'name' => 'Will / Would',
            'keywords' => [
                'will',
                "won't",
                'will have',
                "won't have",
                'would',
                "wouldn't",
                'would have',
                "wouldn't have",
            ],
        ],
        'shall' => [
            'name' => 'Shall',
            'keywords' => [
                'shall',
                "shan't",
                'shall have',
                "shan't have",
            ],
        ],
    ];

    private array $themeGuidance = [
        'ability' => [
            'hint' => 'Підказка: обери модальне дієслово, яке показує здібність або можливість виконати дію.',
            'goal' => 'показує здібність або можливість виконати дію',
            'correct' => "✅ «%option%» підходить, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*",
            'incorrect' => "❌ «%option%» %meaning%. Нам потрібна форма, що %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*",
        ],
        'permission' => [
            'hint' => 'Підказка: шукай форму, що передає дозвіл або заборону.',
            'goal' => 'дозволяє або забороняє дію ввічливо чи офіційно',
            'correct' => "✅ «%option%» доречне, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*",
            'incorrect' => "❌ «%option%» %meaning%. Для цього речення потрібна форма, що %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*",
        ],
        'obligation' => [
            'hint' => 'Підказка: зверни увагу на модальні дієслова, що виражають необхідність або обов’язок.',
            'goal' => 'підкреслює необхідність або обов’язок виконати дію',
            'correct' => "✅ «%option%» пасує, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*",
            'incorrect' => "❌ «%option%» %meaning%. Нам треба конструкцію, яка %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*",
        ],
        'advice' => [
            'hint' => 'Підказка: обери модальне дієслово, яке звучить як порада або рекомендація.',
            'goal' => 'слугує порадою, рекомендацією або м’яким очікуванням',
            'correct' => "✅ «%option%» підходить, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*",
            'incorrect' => "❌ «%option%» %meaning%. Потрібна форма, що %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*",
        ],
        'deduction' => [
            'hint' => 'Підказка: вибери форму, яка показує логічне припущення або ймовірність.',
            'goal' => 'дає змогу зробити логічний висновок чи оцінити ймовірність',
            'correct' => "✅ «%option%» доречно, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*",
            'incorrect' => "❌ «%option%» %meaning%. Треба форма, яка %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*",
        ],
    ];

    private array $typeHintTexts = [
        'question' => 'Це запитання, тому модальне дієслово ставимо перед підметом.',
        'negative' => 'Речення заперечне — не забудь про «not» після модального дієслова.',
        'past' => 'Це стверджувальне речення: модальне дієслово стоїть перед основною дією.',
        'present' => 'Це стверджувальне речення в теперішньому часі: спочатку модальне дієслово, потім інфінітив.',
        'future' => 'Це стверджувальне речення про майбутнє, тож модальне дієслово передає заплановану дію.',
    ];

    private array $typeExplanationTexts = [
        'question' => ' Це питальне речення, тому модальне дієслово стоїть перед підметом.',
        'negative' => ' Це заперечне речення, тож після модального дієслова додаємо «not».',
        'past' => ' Це стверджувальне речення про минуле.',
        'present' => ' Це стверджувальне речення про теперішню ситуацію.',
        'future' => ' Це стверджувальне речення про майбутній сценарій.',
    ];

    private array $tenseHintTexts = [
        'past' => 'Контекст описує минулі події — обери форму, що узгоджується з минулим.',
        'present' => 'Контекст про теперішнє — зверни увагу на звичні або поточні ситуації.',
        'future' => 'Контекст спрямований у майбутнє — подумай про плани чи припущення.',
    ];

    private array $tenseExplanationTexts = [
        'past' => ' Контекст описує минулу подію чи висновок про минуле.',
        'present' => ' Контекст говорить про теперішню ситуацію.',
        'future' => ' Контекст спрямований у майбутнє.',
    ];

    private array $modalMeaningPatterns = [
        "won't be allowed to" => 'показує, що в майбутньому буде заборонено робити дію',
        'will be allowed to' => 'виражає, що в майбутньому щось буде дозволено',
        'be allowed to' => 'описує офіційний дозвіл на дію',
        'allowed to' => 'описує дозвіл виконати дію',
        'still be able to' => 'наголошує на збереженій можливості виконати дію',
        'now be able to' => 'підкреслює, що тепер стало можливим виконати дію',
        'will be able to' => 'говорить про майбутню здатність виконати дію',
        'be able to' => 'виражає здатність або можливість виконати дію',
        'able to' => 'виражає здатність виконати дію',
        'cannot have' => 'стверджує, що щось не могло статися в минулому',
        "can't have" => 'стверджує, що щось не могло статися в минулому',
        'cannot' => 'показує неможливість або заборону',
        "can't" => 'показує неможливість або заборону',
        'can have' => 'описує можливість, яка могла відбутися у минулому',
        'can' => 'виражає загальну здатність або дозвіл у теперішньому',
        "couldn't have" => 'говорить, що дія в минулому була неможливою',
        "couldn't" => 'виражає неможливість або заборону у минулому',
        'could have' => 'описує можливість, що могла статися у минулому',
        'could' => 'вказує на можливість або ввічливе прохання',
        'may not have' => 'натякає, що дія, ймовірно, не відбулася у минулому',
        'may not' => 'висловлює заборону чи сумнів',
        'may have' => 'описує можливість, що могла статися у минулому',
        'may' => 'вказує на дозвіл або ймовірність',
        'might not have' => 'припускає, що дія, ймовірно, не трапилася',
        'might not' => 'виражає сумнів щодо події',
        'might have' => 'описує невпевнену можливість у минулому',
        'might' => 'виражає ймовірність або несміливу пропозицію',
        "mustn't have" => 'робить висновок, що дія у минулому не могла відбутися',
        "mustn't" => 'означає сувору заборону',
        'must have' => 'робить впевнений висновок про минуле',
        'must' => 'виражає сильний обов’язок або логічний висновок',
        "won't have to" => 'вказує, що в майбутньому не буде необхідності щось робити',
        'will have to' => 'означає майбутній обов’язок',
        "don't have to" => 'виражає відсутність необхідності зараз',
        "doesn't have to" => 'показує, що третя особа не має обов’язку',
        "didn't have to" => 'означає, що в минулому не було потреби',
        'have to' => 'описує зовнішній обов’язок',
        'has to' => 'підкреслює обов’язок для третьої особи',
        'had to' => 'описує обов’язок у минулому',
        "needn't have" => 'означає, що дія в минулому була зайвою',
        'need not' => 'виражає відсутність необхідності',
        "needn't" => 'виражає відсутність необхідності',
        'needed to' => 'описує потребу в минулому',
        'needs to' => 'говорить про потребу третьої особи',
        'need to' => 'виражає потребу виконати дію',
        'need' => 'виражає потребу або необхідність',
        'should have' => 'говорить про пораду в минулому, яку слід було виконати',
        "shouldn't have" => 'критикує дію, якої не слід було робити',
        "shouldn't" => 'радить не робити дію',
        'should' => 'виражає пораду або очікування',
        'ought not to have' => 'вказує, що в минулому не слід було щось робити',
        'ought not to' => 'радить утриматися від дії',
        'ought to have' => 'говорить, що в минулому слід було щось зробити',
        'ought to' => 'означає моральний обов’язок або пораду',
        "won't" => 'виражає відмову або заперечення в майбутньому',
        'will' => 'виражає рішення або майбутню дію',
        'would' => 'описує гіпотетичні чи ввічливі ситуації',
        'supposed to' => 'говорить про очікуваний або встановлений обов’язок',
    ];

    private array $nonModalMeanings = [
        'also' => 'це прислівник, а не модальне дієслово',
        'only' => 'це обмежувальна частка, а не модальне дієслово',
        'never' => 'це прислівник частоти, а не модальне дієслово',
        'always' => 'це прислівник частоти, а не модальне дієслово',
        'rarely' => 'це прислівник частоти, а не модальне дієслово',
        'hardly' => 'це прислівник, що виражає ступінь, а не модальне дієслово',
        'barely' => 'це прислівник, що виражає ступінь, а не модальне дієслово',
        'already' => 'це прислівник часу, а не модальне дієслово',
        'even' => 'це підсилювальна частка, а не модальне дієслово',
        'just' => 'це прислівник, а не модальне дієслово',
        'simply' => 'це прислівник, а не модальне дієслово',
        'ever' => 'це прислівник частоти, а не модальне дієслово',
        'to' => 'це частка інфінітива, а не модальне дієслово',
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Modal Verbs Comprehensive AI Practice'])->id;

        $sourceMap = [];
        foreach ($this->themes as $themeKey => $themeData) {
            foreach ($this->typeConfig as $typeKey => $typeData) {
                $sourceName = sprintf(
                    'AI Modals %s - %s',
                    $themeData['label'],
                    $typeData['label']
                );

                $sourceMap[$themeKey][$typeKey] = Source::firstOrCreate(['name' => $sourceName])->id;
            }
        }

        $themeTagIds = [];
        foreach ($this->themes as $themeKey => $themeData) {
            $themeTagIds[$themeKey] = Tag::firstOrCreate(
                ['name' => $themeData['tag']],
                ['category' => 'English Grammar Theme']
            )->id;
        }

        $typeTagIds = [];
        foreach ($this->typeConfig as $typeKey => $typeData) {
            $typeTagIds[$typeKey] = Tag::firstOrCreate(
                ['name' => $typeData['tag']],
                ['category' => 'English Grammar Structure']
            )->id;
        }

        $tenseTagIds = [];
        foreach ($this->tenseTagMap as $tenseKey => $tagName) {
            $tenseTagIds[$tenseKey] = Tag::firstOrCreate(
                ['name' => $tagName],
                ['category' => 'English Grammar Tense']
            )->id;
        }

        $modalPairTagIds = [];
        foreach ($this->modalTagConfig as $modalKey => $modalData) {
            $modalPairTagIds[$modalKey] = Tag::firstOrCreate(
                ['name' => $modalData['name']],
                ['category' => 'English Grammar Modal Pair']
            )->id;
        }

        $modalsTagId = Tag::firstOrCreate(
            ['name' => 'Modal Verbs'],
            ['category' => 'English Grammar Theme']
        )->id;

        $fixedTagId = Tag::firstOrCreate(
            ['name' => 'fixed'],
            ['category' => 'Question Status']
        )->id;

        $questions = $this->buildQuestionBank();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $level = $entry['level'];
            $typeKey = $entry['type'];
            $themeKey = $entry['theme'];
            $tenseKey = $entry['tense'];

            $answers = [];
            $answersMap = [];
            $verbHints = [];
            $optionsPerMarker = [];
            $optionMarkerMap = [];

            foreach ($entry['markers'] as $marker => $markerData) {
                $answer = (string) ($markerData['answer'] ?? '');
                $answersMap[$marker] = $answer;
                $verbHints[$marker] = $this->normalizeHint($markerData['verb_hint'] ?? null);

                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $verbHints[$marker],
                ];

                $options = array_values(array_unique(array_map('strval', $markerData['options'] ?? [])));
                $optionsPerMarker[$marker] = $options;

                foreach ($options as $option) {
                    $optionMarkerMap[$option] = $marker;
                }
            }

            $optionBuckets = array_values($optionsPerMarker);
            $flattenedOptions = $optionBuckets !== []
                ? array_values(array_unique(array_merge(...$optionBuckets)))
                : [];

            $example = $this->formatExample($entry['question'], $answersMap);
            $hintTexts = $this->buildHintsForEntry($entry);
            $explanations = $this->buildExplanationsForEntry(
                $entry,
                $optionsPerMarker,
                $answersMap,
                $example
            );

            $tagIds = array_filter([
                $modalsTagId,
                $themeTagIds[$themeKey] ?? null,
                $typeTagIds[$typeKey] ?? null,
                $tenseTagIds[$tenseKey] ?? null,
            ]);

            $modalTagMatches = $this->determineModalTagIds($entry, $modalPairTagIds);
            $tagIds = array_values(array_unique(array_merge($tagIds, $modalTagMatches)));

            // Add fix tags if present
            if (isset($entry['fix_tags']) && is_array($entry['fix_tags'])) {
                $tagIds[] = $fixedTagId;
                foreach ($entry['fix_tags'] as $fixTagName) {
                    $fixTag = Tag::firstOrCreate(
                        ['name' => $fixTagName],
                        ['category' => 'Fix Description']
                    );
                    $tagIds[] = $fixTag->id;
                }
            }

            $uuid = $this->generateQuestionUuid($level, $themeKey, $typeKey, $index + 1);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$level] ?? 3,
                'source_id' => $sourceMap[$themeKey][$typeKey] ?? reset($sourceMap[$themeKey]),
                'flag' => 2,
                'level' => $level,
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $flattenedOptions,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $answersMap,
                'option_markers' => $optionMarkerMap,
                'hints' => $hintTexts,
                'explanations' => $explanations,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function buildQuestionBank(): array
    {
        $levels = $this->getLevelData();
        $questions = [];

        foreach ($levels as $level => $entries) {
            foreach ($entries as $entry) {
                $questions[] = array_merge($entry, ['level' => $level]);
            }
        }

        return $questions;
    }

    private function determineModalTagIds(array $entry, array $modalPairTagIds): array
    {
        $answers = [];

        foreach ($entry['markers'] ?? [] as $markerData) {
            $answer = trim((string) ($markerData['answer'] ?? ''));

            if ($answer === '') {
                continue;
            }

            $answers[] = mb_strtolower($answer);
        }

        if ($answers === []) {
            return [];
        }

        $matched = [];

        foreach ($this->modalTagConfig as $modalKey => $modalConfig) {
            $keywords = array_map('mb_strtolower', $modalConfig['keywords'] ?? []);

            if ($keywords === []) {
                continue;
            }

            if ($this->answersMatchKeywords($answers, $keywords) && isset($modalPairTagIds[$modalKey])) {
                $matched[] = $modalPairTagIds[$modalKey];
            }
        }

        return array_values(array_unique($matched));
    }

    private function buildHintsForEntry(array $entry): array
    {
        $themeKey = $entry['theme'] ?? '';
        $typeKey = $entry['type'] ?? '';
        $tenseKey = $entry['tense'] ?? '';
        $context = $this->extractSubjectContext($entry['markers'] ?? []);

        $hints = [];

        if (isset($this->themeGuidance[$themeKey]['hint'])) {
            $hints[] = $this->themeGuidance[$themeKey]['hint'];
        }

        if (isset($this->typeHintTexts[$typeKey])) {
            $hints[] = $this->typeHintTexts[$typeKey];
        }

        if (isset($this->tenseHintTexts[$tenseKey])) {
            $hints[] = $this->tenseHintTexts[$tenseKey];
        }

        if ($context['subjects'] !== []) {
            $subjectHint = $this->buildSubjectHint($context['subjects']);
            if ($subjectHint !== '') {
                $hints[] = $subjectHint;
            }
        }

        if ($context['needs_not']) {
            $hints[] = 'Пам’ятай додати «not» після модального дієслова.';
        }

        $hints = array_map('trim', $hints);

        return array_values(array_filter($hints));
    }

    private function buildExplanationsForEntry(
        array $entry,
        array $optionsPerMarker,
        array $answersMap,
        string $example
    ): array {
        $themeKey = $entry['theme'] ?? '';
        $context = $this->extractSubjectContext($entry['markers'] ?? []);
        $themeGuidance = $this->themeGuidance[$themeKey] ?? null;

        $explanations = [];

        foreach ($optionsPerMarker as $marker => $options) {
            $answer = $answersMap[$marker] ?? null;

            if ($answer === null) {
                continue;
            }

            foreach ($options as $option) {
                $explanations[$option] = $this->buildExplanationText(
                    $option,
                    $answer,
                    $entry,
                    $example,
                    $context,
                    $themeGuidance
                );
            }
        }

        return $explanations;
    }

    private function buildExplanationText(
        string $option,
        string $answer,
        array $entry,
        string $example,
        array $context,
        ?array $themeGuidance
    ): string {
        $isCorrect = mb_strtolower(trim($option)) === mb_strtolower(trim($answer));
        $goal = $themeGuidance['goal'] ?? 'передає потрібне модальне значення';
        $template = $isCorrect
            ? ($themeGuidance['correct'] ?? "✅ «%option%» правильне, бо %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПриклад: *%example%*")
            : ($themeGuidance['incorrect'] ?? "❌ «%option%» %meaning%. Нам потрібна форма, що %goal%.%subject_clause%%type_clause%%tense_clause%%not_clause%\nПравильний варіант дає: *%example%*");

        $subjectClause = $this->buildSubjectClause($context['subjects'], $isCorrect);
        $typeClause = $this->typeExplanationTexts[$entry['type'] ?? ''] ?? '';
        $tenseClause = $this->tenseExplanationTexts[$entry['tense'] ?? ''] ?? '';
        $notClause = $context['needs_not'] ? ' Не забудь про «not» після модального дієслова.' : '';

        $replacements = [
            '%option%' => $option,
            '%answer%' => $answer,
            '%goal%' => $goal,
            '%meaning%' => $this->describeModalMeaning($option),
            '%subject_clause%' => $subjectClause,
            '%type_clause%' => $typeClause,
            '%tense_clause%' => $tenseClause,
            '%not_clause%' => $notClause,
            '%example%' => $example,
        ];

        $text = strtr($template, $replacements);
        $text = preg_replace("/\s+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n\s+/", "\n", $text) ?? $text;

        return trim($text);
    }

    private function extractSubjectContext(array $markers): array
    {
        $subjects = [];
        $needsNot = false;

        foreach ($markers as $marker) {
            $hint = $this->normalizeHint($marker['verb_hint'] ?? null);

            if ($hint === null || $hint === '') {
                continue;
            }

            $parts = preg_split('/[;,]/', $hint) ?: [$hint];

            foreach ($parts as $part) {
                $clean = trim((string) $part);

                if ($clean === '') {
                    continue;
                }

                if (preg_match('/^not\s+/i', $clean) === 1) {
                    $needsNot = true;
                    $clean = trim(preg_replace('/^not\s+/i', '', $clean) ?? '');
                }

                if ($clean === '') {
                    continue;
                }

                $subjects[] = $clean;
            }
        }

        return [
            'subjects' => array_values(array_unique($subjects)),
            'needs_not' => $needsNot,
        ];
    }

    private function buildSubjectHint(array $subjects): string
    {
        $list = $this->formatSubjectList($subjects);

        if ($list === '') {
            return '';
        }

        $prefix = count(array_unique($subjects)) > 1 ? 'Зверни увагу на підмети ' : 'Зверни увагу на підмет ';

        return $prefix . $list . '.';
    }

    private function buildSubjectClause(array $subjects, bool $isCorrect): string
    {
        if ($subjects === []) {
            return '';
        }

        $list = $this->formatSubjectList($subjects);

        if ($list === '') {
            return '';
        }

        $prefix = count(array_unique($subjects)) > 1 ? 'Для підметів ' : 'Для підмета ';
        $suffix = $isCorrect
            ? ' ця форма звучить природно.'
            : ' краще використати правильну модальну форму.';

        return ' ' . $prefix . $list . $suffix;
    }

    private function formatSubjectList(array $subjects): string
    {
        $unique = array_values(array_unique(array_filter(array_map('trim', $subjects))));

        if ($unique === []) {
            return '';
        }

        $quoted = array_map(static fn ($value) => '«' . $value . '»', $unique);

        if (count($quoted) === 1) {
            return $quoted[0];
        }

        $last = array_pop($quoted);

        return implode(', ', $quoted) . ' та ' . $last;
    }

    private function describeModalMeaning(string $option): string
    {
        $normalized = mb_strtolower(trim($option));

        if ($normalized === '') {
            return 'не надає потрібного модального значення';
        }

        foreach ($this->modalMeaningPatterns as $pattern => $description) {
            if (str_contains($normalized, $pattern)) {
                return $description;
            }
        }

        if (isset($this->nonModalMeanings[$normalized])) {
            return $this->nonModalMeanings[$normalized];
        }

        if (preg_match('/\bnot\b/', $normalized) === 1) {
            return 'створює заперечення, але не відповідає змісту вправи';
        }

        return 'не передає потрібного модального значення в цьому контексті';
    }

    private function answersMatchKeywords(array $answers, array $keywords): bool
    {
        foreach ($answers as $answer) {
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);

                if ($keyword === '') {
                    continue;
                }

                if ($this->keywordMatches($answer, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function keywordMatches(string $text, string $keyword): bool
    {
        if (str_contains($keyword, ' ')) {
            return str_contains($text, $keyword);
        }

        $pattern = '/\\b' . preg_quote($keyword, '/') . '\\b/u';

        if (preg_match($pattern, $text) === 1) {
            return true;
        }

        return str_contains($text, $keyword);
    }

    protected function getLevelData(): array
    {
        return [
            'A1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} your sister ride a bike without training wheels yet?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Must'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we leave the classroom now, teacher?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} we finish the homework before the movie tonight?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Can', 'Might'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I take an umbrella to school today?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Can'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} this be the right bus to the zoo?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} your grandfather swim when he was five?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Can', 'Should'],
                            'verb_hint' => 'he',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'The sign says we {a1} feed the ducks.',
                    'markers' => [
                        'a1' => [
                            'answer' => "mustn't",
                            'options' => ["mustn't", "shouldn't", "can't"],
                            'verb_hint' => 'not we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} wear a tie to this picnic; it is casual.',
                    'markers' => [
                        'a1' => [
                            'answer' => "don't have to",
                            'options' => ["don't have to", "mustn't", "shouldn't"],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} eat so many sweets before dinner.',
                    'markers' => [
                        'a1' => [
                            'answer' => "shouldn't",
                            'options' => ["shouldn't", "mustn't", "might not"],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'This {a1} be Nina’s coat; it is too big for her.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "might not", "shouldn't"],
                            'verb_hint' => 'not it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'Lily {a1} reach the top shelf yesterday, so I helped.',
                    'markers' => [
                        'a1' => [
                            'answer' => "couldn't",
                            'options' => ["couldn't", "mustn't", "shouldn't"],
                            'verb_hint' => 'not she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'We {a1} stay out late on school nights next week.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't be allowed to",
                            'options' => ["won't be allowed to", "shouldn't", "might not"],
                            'verb_hint' => 'not we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Yesterday I {a1} help my dad fix the fence.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'needed to',
                            'options' => ['needed to', 'had to', 'should'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Grandma said we {a1} call her when we got home.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'When the lights went out, we still {a1} find the door easily.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'can', 'may'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The door was open; someone {a1} left it after lunch.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must have',
                            'options' => ['must have', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'At this museum you {a1} take photos without flash.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'We {a1} wear helmets when we ride our bikes in the park.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'may', 'might'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} drink water during the game to stay hydrated.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The sky is dark; it {a1} rain soon.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'might',
                            'options' => ['might', 'must', 'can'],
                            'verb_hint' => 'it',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Next year Emma {a1} join the advanced dance group.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will be able to',
                            'options' => ['will be able to', 'may', 'must'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After the test we {a1} go outside to play.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will be allowed to',
                            'options' => ['will be allowed to', 'might', 'have to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'We {a1} clean the classroom after the art project tomorrow.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will have to',
                            'options' => ['will have to', 'might', 'could'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If it is sunny tomorrow, you {a1} wear a hat.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                
            ],
            'A2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} you {a2} join the video call if the bus is delayed?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'ought to'],
                            'verb_hint' => 'you',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we {a2} invite a guest to the members-only lounge?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'also',
                            'options' => ['also', 'only', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I {a2} submit the report before lunch or is the afternoon fine?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Do',
                            'options' => ['Do', 'Must', 'Should'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'ought to', 'be able to'],
                            'verb_hint' => 'I',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} we {a2} bring anything special to the networking dinner tomorrow?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} this {a2} been the train that arrived early this morning?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} your colleagues {a2} solve this coding issue without the documentation?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Staff {a1} {a2} use personal email accounts during client meetings.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You {a1} {a2} attend the optional workshop next Friday.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'need to'],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'They {a1} {a2} rely on last year’s data for this forecast.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'may'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'always', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'This {a1} {a2} been Paul’s laptop; his is silver, not black.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Maria {a1} {a2} attend the rehearsal because of her flight schedule.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'should', 'may'],
                            'verb_hint' => 'not she',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'not she',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Last year interns {a1} {a2} work remotely on Fridays.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We {a1} {a2} complete safety training before entering the lab.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'have to', 'ought to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'She {a1} {a2} talk to the mentor before accepting the offer.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'she',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'she',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'From the footprints, the hikers {a1} {a2} taken the northern trail.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Engineers {a1} {a2} adapt the prototype quickly when feedback arrives.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'may'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Guests {a1} {a2} bring small pets into the outdoor café area.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'All drivers {a1} {a2} keep their documents in the vehicle.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'have',
                            'options' => ['have', 'should', 'might'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'able to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} break big tasks into smaller steps for clarity.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'could'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'always',
                            'options' => ['always', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'With all the lights off, they {a1} {a2} already left the office.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'might',
                            'options' => ['might', 'must', 'can'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'By winter we {a1} {a2} operate the new machinery safely.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Residents {a1} {a2} extend their leases after the renovation.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Next quarter we {a1} {a2} report progress every Monday.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'ought to'],
                            'verb_hint' => 'we',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If the forecast changes, you {a1} {a2} adjust the schedule.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'could'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'B1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} your team {a2} {a3} a backup plan if negotiations fail?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'draft',
                            'options' => ['draft', 'decide', 'avoid'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we {a2} {a3} use the auditorium for an unscheduled rehearsal?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'please',
                            'options' => ['please', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'still',
                            'options' => ['still', 'already', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} the contractors {a2} {a3} finish the wiring by Friday?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Did',
                            'options' => ['Did', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'actually',
                            'options' => ['actually', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I {a2} {a3} the itinerary so the guests know what to expect?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'go over',
                            'options' => ['go over', 'delay', 'skip'],
                            'verb_hint' => 'I',
                        ],
                        'a3' => [
                            'answer' => 'again',
                            'options' => ['again', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} this {a2} {a3} the server that keeps crashing?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be',
                            'options' => ['be', 'have', 'do'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} your mentor {a2} {a3} new software so quickly last quarter?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'How',
                            'options' => ['How', 'Could', 'Should'],
                            'verb_hint' => 'he',
                        ],
                        'a2' => [
                            'answer' => 'manage to',
                            'options' => ['manage to', 'need to', 'have to'],
                            'verb_hint' => 'he',
                        ],
                        'a3' => [
                            'answer' => 'learn',
                            'options' => ['learn', 'delay', 'refuse'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Volunteers {a1} {a2} {a3} enter the archive without supervision next week.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Consultants {a1} {a2} {a3} send daily updates during the maintenance window.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'do',
                            'options' => ['do', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => "n't",
                            'options' => ["n't", 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'need to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You {a1} {a2} {a3} underestimate the client’s expectations again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'absolutely',
                            'options' => ['absolutely', 'hardly', 'scarcely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not you',
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'That explanation {a1} {a2} {a3} correct because the logs disagree.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'cannot',
                            'options' => ['cannot', "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be',
                            'options' => ['be', 'have', 'do'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'They {a1} {a2} {a3} reach consensus despite several mediation rounds.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'should', 'must'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'manage to',
                            'options' => ['manage to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'During the pilot phase, analysts {a1} {a2} {a3} access confidential files after hours.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'temporarily',
                            'options' => ['temporarily', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We {a1} {a2} {a3} attend every stakeholder briefing last season.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'able to', 'need to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'personally',
                            'options' => ['personally', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} {a2} {a3} double-check the invoices before authorizing them.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'never', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The timeline {a1} {a2} {a3} adjusted earlier to meet the launch date.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'been',
                            'options' => ['been', 'done', 'set'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our system {a1} {a2} {a3} handle multilingual input smoothly now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Members {a1} {a2} {a3} reserve collaboration rooms via the new app.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'freely',
                            'options' => ['freely', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'All contractors {a1} {a2} {a3} follow the updated safety protocol.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'at all times',
                            'options' => ['at all times', 'seldom', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'strictly',
                            'options' => ['strictly', 'casually', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} involve the support team before launching updates.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'always',
                            'options' => ['always', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'consult',
                            'options' => ['consult', 'ignore', 'postpone'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Given the empty parking lot, the committee {a1} {a2} {a3} concluded early.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'With the new funding, the lab {a1} {a2} {a3} develop custom sensors.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'independently',
                            'options' => ['independently', 'rarely', 'hesitantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After accreditation, students {a1} {a2} {a3} enroll in evening clinics.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Project leads {a1} {a2} {a3} submit risk assessments every quarter.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'still',
                            'options' => ['still', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If supply issues continue, you {a1} {a2} {a3} diversify vendors quickly.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'plan to',
                            'options' => ['plan to', 'refuse to', 'delay to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'B2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} the research unit {a2} {a3} prototype a solution within six weeks?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'still be able to',
                            'options' => ['still be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'rapidly',
                            'options' => ['rapidly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} visiting fellows {a2} {a3} access the advanced analytics lab after hours?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'temporarily',
                            'options' => ['temporarily', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be admitted to',
                            'options' => ['be admitted to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} we {a2} {a3} escalate every variance to the board next quarter?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Must', 'Should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'ought to', 'be able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'rarely', 'slowly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I {a2} {a3} stakeholders before locking the sprint goals?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'consult',
                            'options' => ['consult', 'sideline', 'delay'],
                            'verb_hint' => 'I',
                        ],
                        'a3' => [
                            'answer' => 'each time',
                            'options' => ['each time', 'rarely', 'hardly ever'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} the auditors {a2} {a3} uncovered this discrepancy last year?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} your analysts {a2} {a3} adapt the model when the dataset doubles overnight?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Should', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'retrain it',
                            'options' => ['retrain it', 'ignore it', 'delay it'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Contractors {a1} {a2} {a3} disclose client data outside secured channels.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our division {a1} {a2} {a3} submit duplicate compliance reports next cycle.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not we',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'not we',
                        ],
                        'a3' => [
                            'answer' => 'anymore',
                            'options' => ['anymore', 'always', 'seldom'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} rely solely on intuition for these investment calls.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'again',
                            'options' => ['again', 'soon', 'always'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'This spike {a1} {a2} {a3} resulted from user activity; the logs show maintenance scripts.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'possibly',
                            'options' => ['possibly', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our suppliers {a1} {a2} {a3} guarantee next-day shipping during peak season.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Before the merger, teams {a1} {a2} {a3} file expenses without pre-approval.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'could',
                            'options' => ['could', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'routinely',
                            'options' => ['routinely', 'rarely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We {a1} {a2} {a3} deliver weekly dashboards during the pilot rollout.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'need to', 'able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'meticulously',
                            'options' => ['meticulously', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} {a2} {a3} brief the legal team before signing the vendor agreement.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'may'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'definitely',
                            'options' => ['definitely', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Given the timestamps, the contractors {a1} {a2} {a3} completed the audit overnight.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'already',
                            'options' => ['already', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our platform {a1} {a2} {a3} process cross-border payments in real time now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'reliably',
                            'options' => ['reliably', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Partners {a1} {a2} {a3} access the beta features under the new licensing plan.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'freely',
                            'options' => ['freely', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Every branch {a1} {a2} {a3} comply with the revised transparency charter.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'fully',
                            'options' => ['fully', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'adhere',
                            'options' => ['adhere', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} archive your notes in the shared knowledge base.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'document',
                            'options' => ['document', 'dismiss', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Given the silence, the panel {a1} {a2} {a3} reached a unanimous verdict already.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'just',
                            'options' => ['just', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After the upgrade, the support bots {a1} {a2} {a3} resolve billing issues autonomously.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'swiftly',
                            'options' => ['swiftly', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Once certified, trainees {a1} {a2} {a3} lead onsite inspections.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'hardly', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Regional offices {a1} {a2} {a3} submit ESG metrics alongside financial statements.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If competition intensifies, you {a1} {a2} {a3} reposition the product narrative.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'quickly',
                            'options' => ['quickly', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'work to',
                            'options' => ['work to', 'refuse to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'C1' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} the crisis team {a2} {a3} pivot the strategy without executive sign-off?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Can',
                            'options' => ['Can', 'Could', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'credibly',
                            'options' => ['credibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'claim to',
                            'options' => ['claim to', 'hesitate to', 'refuse to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} the consortium {a2} {a3} publish interim results before peer review concludes?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'May',
                            'options' => ['May', 'Might', 'Must'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'ever',
                            'options' => ['ever', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be authorised to',
                            'options' => ['be authorised to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} the regulator {a2} {a3} impose stricter capital buffers after the audit?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Did',
                            'options' => ['Did', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'unilaterally',
                            'options' => ['unilaterally', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} we {a2} {a3} disclose the risk scenarios to reassure investors?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'proactively',
                            'options' => ['proactively', 'rarely', 'begrudgingly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'share them',
                            'options' => ['share them', 'withhold them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} the cyberattack {a2} {a3} originated from an insider, given the access logs?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'credibly',
                            'options' => ['credibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} the biotech firm {a2} {a3} ramp production if regulators approve tomorrow?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Will',
                            'options' => ['Will', 'Should', 'Might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'have to', 'need to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'External counsel {a1} {a2} {a3} circulate draft agreements beyond the legal board.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'may'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'rarely'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be permitted to',
                            'options' => ['be permitted to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The task force {a1} {a2} {a3} submit redundant status reports under the new policy.',
                    'markers' => [
                        'a1' => [
                            'answer' => "won't",
                            'options' => ["won't", 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'need to', 'ought to'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'any longer',
                            'options' => ['any longer', 'always', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} prioritise vanity metrics over retention signals again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'under no circumstances',
                            'options' => ['under no circumstances', 'rarely', 'occasionally'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'ever',
                            'options' => ['ever', 'seldom', 'always'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'That valuation {a1} {a2} {a3} emerged from independent analysts; the language matches marketing decks.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'plausibly',
                            'options' => ['plausibly', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'Our distributed teams {a1} {a2} {a3} synchronise daily once the firewall restrictions tighten.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'not',
                            'options' => ['not', 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Senior fellows {a1} {a2} {a3} invite external reviewers during the confidential deliberations.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were',
                            'options' => ['were', 'could', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We {a1} {a2} {a3} convene emergency sessions throughout the litigation.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'had',
                            'options' => ['had', 'must', 'should'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'to',
                            'options' => ['to', 'need to', 'able to'],
                            'verb_hint' => 'we',
                        ],
                        'a3' => [
                            'answer' => 'repeatedly',
                            'options' => ['repeatedly', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} {a2} {a3} outline clear contingencies before pitching the acquisition.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'could', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'have',
                            'options' => ['have', 'be', 'ever'],
                            'verb_hint' => 'you',
                        ],
                        'a3' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'vaguely', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The negotiation team {a1} {a2} {a3} concessions overnight; the terms barely shifted.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must not have',
                            'options' => ['must not have', 'could not', 'should not'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'actually',
                            'options' => ['actually', 'hardly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'won',
                            'options' => ['won', 'secured', 'abandoned'],
                            'verb_hint' => null,
                        ],
                    ],
                    'fix_tags' => ['double verb removed -> single verb', 'won secured -> won'], // Fixed: removed duplicate verb "secured" from question
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our analytics stack {a1} {a2} {a3} parse multilingual sentiment in near real time now.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'sporadically', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Advisory partners {a1} {a2} {a3} join the confidential steering meetings under NDA.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'formally',
                            'options' => ['formally', 'informally', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Each subsidiary {a1} {a2} {a3} implement the whistleblower safeguards without delay.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'fully',
                            'options' => ['fully', 'partially', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'comply',
                            'options' => ['comply', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} publish transparent post-mortems after each incident.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'ought to',
                            'options' => ['ought to', 'have to', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'consistently',
                            'options' => ['consistently', 'sporadically', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'release them',
                            'options' => ['release them', 'withhold them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The silence in chat {a1} {a2} {a3} mean the deployment failed; logs show success.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", 'might', 'should'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'really',
                            'options' => ['really', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'signify',
                            'options' => ['signify', 'ignore', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'With the AI overhaul, the platform {a1} {a2} {a3} diagnose anomalies before outages.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'autonomously',
                            'options' => ['autonomously', 'rarely', 'reluctantly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'After ratification, regional leads {a1} {a2} {a3} negotiate bespoke pricing tiers.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'finally',
                            'options' => ['finally', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be allowed to',
                            'options' => ['be allowed to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Boards {a1} {a2} {a3} certify ESG disclosures alongside audited statements.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'now',
                            'options' => ['now', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If macro signals deteriorate, you {a1} {a2} {a3} recalibrate hiring plans immediately.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'probably',
                            'options' => ['probably', 'barely', 'never'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'move to',
                            'options' => ['move to', 'avoid to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
            'C2' => [
                [
                    'theme' => 'ability',
                    'type' => 'question',
                    'tense' => 'past',
                    'question' => '{a1} your predecessor {a2} {a3} rescued the merger talks without the emergency loan facility?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Might', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'possibly have',
                            'options' => ['possibly have', 'ever', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'salvaged',
                            'options' => ['salvaged', 'abandoned', 'ignored'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} the oversight board {a2} {a3} waive the embargo if the leak investigation concludes today?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Might',
                            'options' => ['Might', 'Must', 'Should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'lawfully',
                            'options' => ['lawfully', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be empowered to',
                            'options' => ['be empowered to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} I {a2} {a3} escalate every whistleblower allegation to the audit chair immediately?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Must',
                            'options' => ['Must', 'Should', 'Could'],
                            'verb_hint' => 'I',
                        ],
                        'a2' => [
                            'answer' => 'without fail',
                            'options' => ['without fail', 'rarely', 'begrudgingly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'notify',
                            'options' => ['notify', 'delay', 'dismiss'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'question',
                    'tense' => 'future',
                    'question' => '{a1} we {a2} {a3} hedge aggressively if the central bank hints at tapering?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Should',
                            'options' => ['Should', 'Must', 'Might'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'perhaps',
                            'options' => ['perhaps', 'never', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'rebalance',
                            'options' => ['rebalance', 'delay', 'ignore'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'question',
                    'tense' => 'present',
                    'question' => '{a1} this spike {a2} {a3} signalling regulatory scrutiny, given the unusual data requests?',
                    'markers' => [
                        'a1' => [
                            'answer' => 'Could',
                            'options' => ['Could', 'Must', 'Should'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'realistically be',
                            'options' => ['realistically be', 'barely be', 'never be'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'quietly',
                            'options' => ['quietly', 'loudly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
                [
                    'theme' => 'permission',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The ethics panel {a1} {a2} {a3} authorise undisclosed data sharing under any scenario.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'should', 'might'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'never',
                            'options' => ['never', 'rarely', 'hardly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be permitted to',
                            'options' => ['be permitted to', 'need to', 'have to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'negative',
                    'tense' => 'present',
                    'question' => 'Directors {a1} {a2} {a3} convene extraordinary sessions if thresholds are not breached.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'do',
                            'options' => ['do', 'must', 'should'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => "n't",
                            'options' => ["n't", 'ever', 'always'],
                            'verb_hint' => 'not they',
                        ],
                        'a3' => [
                            'answer' => 'need to',
                            'options' => ['need to', 'have to', 'ought to'],
                            'verb_hint' => 'not they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'You {a1} {a2} {a3} chase yield with leveraged bets once volatility spikes again.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'not you',
                        ],
                        'a2' => [
                            'answer' => 'categorically',
                            'options' => ['categorically', 'rarely', 'hesitantly'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'avoid',
                            'options' => ['avoid', 'seek', 'ignore'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'negative',
                    'tense' => 'past',
                    'question' => 'Those numbers {a1} {a2} {a3} come from finance; the rounding mimics sales projections.',
                    'markers' => [
                        'a1' => [
                            'answer' => "can't",
                            'options' => ["can't", "shouldn't", 'might not'],
                            'verb_hint' => 'not they',
                        ],
                        'a2' => [
                            'answer' => 'possibly have',
                            'options' => ['possibly have', 'ever have', 'rarely have'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'originated',
                            'options' => ['originated', 'drifted', 'collapsed'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'negative',
                    'tense' => 'future',
                    'question' => 'The legacy platform {a1} {a2} {a3} handle real-time fraud scoring once the user base triples.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'no longer',
                            'options' => ['no longer', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be able to',
                            'options' => ['be able to', 'need to', 'have to'],
                            'verb_hint' => 'not it',
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'Under the emergency decree, ministers {a1} {a2} {a3} bypass procurement rules entirely.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'were briefly',
                            'options' => ['were briefly', 'might', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'allowed to',
                            'options' => ['allowed to', 'supposed to', 'able to'],
                            'verb_hint' => 'they',
                        ],
                        'a3' => [
                            'answer' => 'lawfully',
                            'options' => ['lawfully', 'reluctantly', 'rarely'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'We {a1} {a2} {a3} notify antitrust regulators before concluding that strategic alliance.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should have',
                            'options' => ['should have', 'could have', 'might have'],
                            'verb_hint' => 'we',
                        ],
                        'a2' => [
                            'answer' => 'formally',
                            'options' => ['formally', 'vaguely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'briefed them',
                            'options' => ['briefed them', 'ignored them', 'delayed them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'You {a1} {a2} {a3} diversified your suppliers before sanctions were announced.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'ought to have',
                            'options' => ['ought to have', 'might have', 'could have'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'strategically',
                            'options' => ['strategically', 'randomly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'rebalanced',
                            'options' => ['rebalanced', 'hoarded', 'ignored'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'past',
                    'tense' => 'past',
                    'question' => 'The committee {a1} {a2} {a3} leaked the memo; the watermark belongs to legal.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must have',
                            'options' => ['must have', 'might have', 'should have'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'inadvertently',
                            'options' => ['inadvertently', 'deliberately', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'released it',
                            'options' => ['released it', 'destroyed it', 'ignored it'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'ability',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Our quantum prototype {a1} {a2} {a3} model systemic shocks beyond traditional Monte Carlo.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'it',
                        ],
                        'a2' => [
                            'answer' => 'now be able to',
                            'options' => ['now be able to', 'need to', 'have to'],
                            'verb_hint' => 'it',
                        ],
                        'a3' => [
                            'answer' => 'efficiently',
                            'options' => ['efficiently', 'rarely', 'slowly'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'permission',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Institutional clients {a1} {a2} {a3} deploy the sandbox under the premium covenant.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'may',
                            'options' => ['may', 'must', 'should'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'opt to',
                            'options' => ['opt to', 'refuse to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'Every portfolio manager {a1} {a2} {a3} certify ESG exposure with audited evidence.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'must',
                            'options' => ['must', 'should', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'personally',
                            'options' => ['personally', 'rarely', 'casually'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'attest',
                            'options' => ['attest', 'question', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'You {a1} {a2} {a3} interrogate leading indicators rather than lagging ones.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'habitually',
                            'options' => ['habitually', 'rarely', 'sporadically'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'analyse them',
                            'options' => ['analyse them', 'ignore them', 'delay them'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                [
                    'theme' => 'deduction',
                    'type' => 'present',
                    'tense' => 'present',
                    'question' => 'The absence of volatility {a1} {a2} {a3} indicate complacency; hedges are active.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should not',
                            'options' => ['should not', 'must', 'might'],
                            'verb_hint' => 'not it',
                        ],
                        'a2' => [
                            'answer' => 'necessarily',
                            'options' => ['necessarily', 'barely', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'imply',
                            'options' => ['imply', 'erase', 'delay'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
                [
                    'theme' => 'permission',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Post-ratification, independent directors {a1} {a2} {a3} veto related-party deals outright.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'explicitly',
                            'options' => ['explicitly', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'be authorised to',
                            'options' => ['be authorised to', 'need to', 'have to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'obligation',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'Supervisors {a1} {a2} {a3} certify climate risk assumptions alongside stress tests.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'will',
                            'options' => ['will', 'might', 'could'],
                            'verb_hint' => 'they',
                        ],
                        'a2' => [
                            'answer' => 'soon',
                            'options' => ['soon', 'rarely', 'barely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'have to',
                            'options' => ['have to', 'be able to', 'need to'],
                            'verb_hint' => 'they',
                        ],
                    ],
                ],
                [
                    'theme' => 'advice',
                    'type' => 'future',
                    'tense' => 'future',
                    'question' => 'If liquidity evaporates, you {a1} {a2} {a3} unwind exposure before markets seize.',
                    'markers' => [
                        'a1' => [
                            'answer' => 'should',
                            'options' => ['should', 'must', 'might'],
                            'verb_hint' => 'you',
                        ],
                        'a2' => [
                            'answer' => 'immediately',
                            'options' => ['immediately', 'slowly', 'rarely'],
                            'verb_hint' => null,
                        ],
                        'a3' => [
                            'answer' => 'move to',
                            'options' => ['move to', 'choose to', 'forget to'],
                            'verb_hint' => null,
                        ],
                    ],
                ],
                
            ],
        ];
    }
}
