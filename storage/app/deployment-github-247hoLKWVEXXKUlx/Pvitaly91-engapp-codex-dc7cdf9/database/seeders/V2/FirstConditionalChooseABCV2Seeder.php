<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class FirstConditionalChooseABCV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Conditionals'])->id;

        $sectionSources = [
            'result_future' => Source::firstOrCreate(['name' => 'Custom: First Conditional Result Clauses A/B/C V2'])->id,
            'result_imperative' => Source::firstOrCreate(['name' => 'Custom: First Conditional Imperatives A/B/C V2'])->id,
            'condition' => Source::firstOrCreate(['name' => 'Custom: First Conditional If-Clauses A/B/C V2'])->id,
        ];

        $themeTags = [
            'result_future' => Tag::firstOrCreate(['name' => 'First Conditional Result Clauses'], ['category' => 'English Grammar Theme'])->id,
            'result_imperative' => Tag::firstOrCreate(['name' => 'First Conditional Imperative Results'], ['category' => 'English Grammar Theme'])->id,
            'condition' => Tag::firstOrCreate(['name' => 'First Conditional If-Clauses'], ['category' => 'English Grammar Theme'])->id,
        ];

        $detailTags = [
            'result_future_positive' => Tag::firstOrCreate(['name' => 'Result Clause — Will + Base'], ['category' => 'English Grammar Detail'])->id,
            'result_future_negative' => Tag::firstOrCreate(['name' => 'Result Clause — Will Not'], ['category' => 'English Grammar Detail'])->id,
            'result_imperative' => Tag::firstOrCreate(['name' => 'Result Clause — Imperative'], ['category' => 'English Grammar Detail'])->id,
            'condition_present_positive' => Tag::firstOrCreate(['name' => 'If-Clause — Present Simple'], ['category' => 'English Grammar Detail'])->id,
            'condition_present_negative' => Tag::firstOrCreate(['name' => 'If-Clause — Negative Present Simple'], ['category' => 'English Grammar Detail'])->id,
        ];

        $patternConfig = [
            'result_future_positive' => [
                'section' => 'result_future',
                'detail' => 'result_future_positive',
                'tense' => ['First Conditional'],
                'structure' => 'First Conditional Sentences',
                'hint_short' => 'Result clause with will',
                'markers' => 'If + Present Simple, will + V1',
                'verb_hint' => 'future auxiliary (result clause)',
            ],
            'result_future_negative' => [
                'section' => 'result_future',
                'detail' => 'result_future_negative',
                'tense' => ['First Conditional'],
                'structure' => 'First Conditional Sentences',
                'hint_short' => 'Result clause with will not',
                'markers' => 'If + Present Simple, will not + V1',
                'verb_hint' => 'not',
            ],
            'result_imperative' => [
                'section' => 'result_imperative',
                'detail' => 'result_imperative',
                'tense' => ['First Conditional'],
                'structure' => 'First Conditional Sentences',
                'hint_short' => 'Result clause as imperative',
                'markers' => 'If + Present Simple, (just) base verb',
                'verb_hint' => 'imperative form',
            ],
            'condition_present_positive' => [
                'section' => 'condition',
                'detail' => 'condition_present_positive',
                'tense' => ['First Conditional'],
                'structure' => 'First Conditional Sentences',
                'hint_short' => 'If-clause with Present Simple',
                'markers' => 'If + Present Simple, will + V1',
                'verb_hint' => 'Present Simple',
            ],
            'condition_present_negative' => [
                'section' => 'condition',
                'detail' => 'condition_present_negative',
                'tense' => ['First Conditional'],
                'structure' => 'First Conditional Sentences',
                'hint_short' => 'If-clause with do/does not',
                'markers' => 'If + do/does not + V1, will + V1',
                'verb_hint' => 'negative present simple — use not',
            ],
        ];

        $structureTagIds = [];
        foreach ($patternConfig as $config) {
            $structure = $config['structure'];
            if (! isset($structureTagIds[$structure])) {
                $structureTagIds[$structure] = Tag::firstOrCreate([
                    'name' => $structure,
                ], ['category' => 'English Grammar Structure'])->id;
            }
        }

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $entries = [
            $this->entry('A2', 'result_future_positive', 'If we come home late, mum {a1} angry.', 'will be', ['is', "won't be", 'will be']),
            $this->entry('A2', 'condition_present_positive', 'Meg will be ill if she {a1} a lot of chocolates.', 'eats', ['eats', 'will eat', 'eat']),
            $this->entry('A2', 'result_future_negative', 'He {a1} to your party if you don\'t invite him.', "won't come", ['will come', 'comes', "won't come"]),
            $this->entry('A2', 'condition_present_positive', 'The boys will have to play well if they {a1} to win the game.', 'want', ['wanted', 'will want', 'want']),
            $this->entry('A2', 'result_imperative', 'If it rains, {a1} an umbrella.', 'take', ['take', 'you will take', 'will take']),
            $this->entry('A2', 'condition_present_positive', 'If it {a1}, we\'ll go skiing.', 'snows', ['snow', 'will snow', 'snows']),
            $this->entry('A2', 'result_future_positive', 'If you make lunch, I {a1} the dishes.', 'will wash', ['wash', 'will wash', 'washes']),
            $this->entry('A2', 'condition_present_positive', 'If you {a1} up late, you\'ll be tired in the morning.', 'stay', ["'ll stay", 'stay', 'stays']),
            $this->entry('A2', 'condition_present_negative', 'If he {a1} his homework, his teacher won\'t be pleased.', "doesn't do", ["don't do", 'does', "doesn't do"]),
            $this->entry('A2', 'result_imperative', 'If you want to come with us, {a1} me.', 'call', ['you call', 'call', 'called']),
            $this->entry('A2', 'condition_present_negative', 'If he {a1} all his exams, his father won\'t buy him a bike.', "doesn't pass", ["don't pass", "doesn't passes", "doesn't pass"]),
            $this->entry('A2', 'result_imperative', 'If you can\'t find the answer, {a1} your teacher.', 'ask', ['asked', 'will ask', 'ask']),
        ];

        $questionsBySection = [
            'result_future' => [],
            'result_imperative' => [],
            'condition' => [],
        ];

        foreach ($entries as $entry) {
            $config = $patternConfig[$entry['pattern']];
            $answer = $entry['answer'];
            $example = $this->formatExample($entry['question'], $answer);

            $questionsBySection[$config['section']][] = [
                'detail' => $config['detail'],
                'question' => $entry['question'],
                'options' => $entry['options'],
                'answers' => ['a1' => $answer],
                'hints' => ['a1' => $this->buildHint($entry['pattern'], $example, $config)],
                'explanations' => $this->buildExplanations($entry['pattern'], $entry['options'], $answer, $example),
                'verb_hint' => ['a1' => '(' . $config['verb_hint'] . ')'],
                'tense' => $config['tense'],
                'level' => $entry['level'],
                'structure_tag_id' => $structureTagIds[$config['structure']] ?? null,
            ];
        }

        $tenseTags = [];
        foreach ($questionsBySection as $sectionQuestions) {
            foreach ($sectionQuestions as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTags[$tenseName])) {
                        $tenseTags[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
                    }
                }
            }
        }

        $items = [];
        $meta = [];

        foreach ($questionsBySection as $sectionKey => $sectionQuestions) {
            foreach ($sectionQuestions as $index => $question) {
                $uuid = $this->generateQuestionUuid($sectionKey, $index, $question['question']);

                $answers = [];
                $optionMarkerMap = [];
                $firstMarker = array_key_first($question['answers']);

                if ($firstMarker !== null) {
                    foreach ($question['options'] as $option) {
                        $optionMarkerMap[$option] = $firstMarker;
                    }
                }

                foreach ($question['answers'] as $marker => $answer) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $answer,
                        'verb_hint' => $this->normalizeHint($question['verb_hint'][$marker] ?? null),
                    ];
                    $optionMarkerMap[$answer] = $marker;
                }

                $tagIds = [$themeTags[$sectionKey]];
                $detailKey = $question['detail'] ?? null;
                if ($detailKey !== null && isset($detailTags[$detailKey])) {
                    $tagIds[] = $detailTags[$detailKey];
                }

                if (isset($question['structure_tag_id'])) {
                    $tagIds[] = $question['structure_tag_id'];
                }

                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $question['question'],
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 0,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => [],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $question['answers'],
                    'option_markers' => $optionMarkerMap,
                    'hints' => $question['hints'],
                    'explanations' => $question['explanations'],
                ];
            }
        }

        $this->seedQuestionData($items, $meta);
    }

    private function entry(string $level, string $pattern, string $question, string $answer, array $options): array
    {
        return [
            'level' => $level,
            'pattern' => $pattern,
            'question' => $question,
            'answer' => $answer,
            'options' => $options,
        ];
    }

    private function buildHint(string $pattern, string $example, array $config): string
    {
        return match ($pattern) {
            'result_future_positive' => "Час: {$config['tense'][0]}.  \nФормула: **If + Present Simple, will + V1**.  \nПояснення: Головна частина першого умовного потребує will + початкову форму дієслова.  \nПриклад: *{$example}*  \nМаркери: {$config['markers']}.",
            'result_future_negative' => "Час: {$config['tense'][0]}.  \nФормула: **If + Present Simple, won't + V1**.  \nПояснення: У запереченні в головній частині ставимо will not (won't) перед початковою формою дієслова.  \nПриклад: *{$example}*  \nМаркери: {$config['markers']}.",
            'result_imperative' => "Час: {$config['tense'][0]}.  \nФормула: **If + Present Simple, + V1 (наказ)**.  \nПояснення: Результат може бути у формі наказу, тому використовуємо базову форму дієслова без will.  \nПриклад: *{$example}*  \nМаркери: {$config['markers']}.",
            'condition_present_positive' => "Час: {$config['tense'][0]}.  \nФормула: **If + Present Simple, will + V1**.  \nПояснення: В if-клаузі першого умовного завжди вживаємо Present Simple без will.  \nПриклад: *{$example}*  \nМаркери: {$config['markers']}.",
            'condition_present_negative' => "Час: {$config['tense'][0]}.  \nФормула: **If + do/does not + V1, will + V1**.  \nПояснення: Заперечення в if-клаузі будуємо за допомогою do/does + not і початкової форми дієслова.  \nПриклад: *{$example}*  \nМаркери: {$config['markers']}.",
            default => '',
        };
    }

    private function buildExplanations(string $pattern, array $options, string $answer, string $example): array
    {
        $explanations = [];
        foreach ($options as $option) {
            if ($option === $answer) {
                $explanations[$option] = $this->buildCorrectExplanation($pattern, $answer, $example);
            } else {
                $explanations[$option] = $this->buildWrongExplanation($pattern, $option, $answer, $example);
            }
        }

        return $explanations;
    }

    private function buildCorrectExplanation(string $pattern, string $answer, string $example): string
    {
        return match ($pattern) {
            'result_future_positive' => "✅ «{$answer}» правильно, бо результат у першому умовному виражається конструкцією will + дієслово.  \nПриклад: *{$example}*",
            'result_future_negative' => "✅ «{$answer}» правильно, бо для заперечення в головній частині ставимо won't + дієслово.  \nПриклад: *{$example}*",
            'result_imperative' => "✅ «{$answer}» правильно, бо після if речення результат подаємо як наказ у базовій формі без will.  \nПриклад: *{$example}*",
            'condition_present_positive' => "✅ «{$answer}» правильно, бо в if-клаузі першого умовного потрібен Present Simple.  \nПриклад: *{$example}*",
            'condition_present_negative' => "✅ «{$answer}» правильно, бо заперечну if-клаузу будуємо через does not + базове дієслово.  \nПриклад: *{$example}*",
            default => '✅',
        };
    }

    private function buildWrongExplanation(string $pattern, string $option, string $answer, string $example): string
    {
        return match ($pattern) {
            'result_future_positive' => $this->explainWrongResultFuturePositive($option, $answer, $example),
            'result_future_negative' => $this->explainWrongResultFutureNegative($option, $answer, $example),
            'result_imperative' => $this->explainWrongResultImperative($option, $answer, $example),
            'condition_present_positive' => $this->explainWrongConditionPositive($option, $answer, $example),
            'condition_present_negative' => $this->explainWrongConditionNegative($option, $answer, $example),
            default => "❌ Неправильний варіант.  \nПриклад: *{$example}*",
        };
    }

    private function explainWrongResultFuturePositive(string $option, string $answer, string $example): string
    {
        if (str_contains($option, "won't")) {
            return "❌ «{$option}» не підходить, бо це заперечна форма, а у реченні потрібно ствердне «{$answer}».  \nПриклад: *{$example}*";
        }

        if ($option === 'is' || $option === 'wash') {
            return "❌ «{$option}» не має допоміжного will, а результат першого умовного вимагає will + дієслово.  \nПриклад: *{$example}*";
        }

        return "❌ «{$option}» не передає майбутній результат. Потрібно використати конструкцію will + дієслово.  \nПриклад: *{$example}*";
    }

    private function explainWrongResultFutureNegative(string $option, string $answer, string $example): string
    {
        if ($option === 'will come') {
            return "❌ «{$option}» стверджує, що дія відбудеться, тоді як нам потрібне заперечення «{$answer}».  \nПриклад: *{$example}*";
        }

        if ($option === 'comes') {
            return "❌ «{$option}» ужито в Present Simple без will, а заперечний результат повинен мати won't + дієслово.  \nПриклад: *{$example}*";
        }

        return "❌ Невірна форма для заперечення. Треба «{$answer}».  \nПриклад: *{$example}*";
    }

    private function explainWrongResultImperative(string $option, string $answer, string $example): string
    {
        if (str_contains($option, 'will')) {
            return "❌ «{$option}» містить will, але в наказовій формі після if ми вживаємо тільки базове дієслово.  \nПриклад: *{$example}*";
        }

        if (str_contains($option, 'called') || str_contains($option, 'asked')) {
            return "❌ «{$option}» у минулому часі, а наказ повинен бути у початковій формі: «{$answer}».  \nПриклад: *{$example}*";
        }

        return "❌ Після if наказ формуємо лише базовим дієсловом. Оберіть «{$answer}».  \nПриклад: *{$example}*";
    }

    private function explainWrongConditionPositive(string $option, string $answer, string $example): string
    {
        if (str_contains($option, "'ll")) {
            return "❌ «{$option}» містить скорочене will, але в if-клаузі першого умовного ми використовуємо тільки Present Simple.  \nПриклад: *{$example}*";
        }

        if ($option === 'stays' || $option === 'washes' || $option === 'will snow') {
            return "❌ «{$option}» має неправильне закінчення або will. Умовна частина повинна бути у Present Simple: «{$answer}».  \nПриклад: *{$example}*";
        }

        return "❌ Умовна частина потребує Present Simple без will.  \nПриклад: *{$example}*";
    }

    private function explainWrongConditionNegative(string $option, string $answer, string $example): string
    {
        if ($option === "don't do" || $option === "don't pass") {
            return "❌ «{$option}» вживає do not, але з третьою особою однини потрібно does not.  \nПриклад: *{$example}*";
        }

        if ($option === 'does' || $option === "doesn't passes") {
            return "❌ «{$option}» не містить правильної базової форми після does/doesn't. Треба «{$answer}».  \nПриклад: *{$example}*";
        }

        return "❌ Неправильна форма заперечення в if-клаузі. Правильно: «{$answer}».  \nПриклад: *{$example}*";
    }
}
