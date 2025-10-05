<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class PastTimeClausesMixedTestSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Past Time Clauses Mixed Test'])->id;

        $generalTagId = Tag::firstOrCreate(['name' => 'Past Time Clauses Practice'], ['category' => 'Grammar'])->id;

        $detailTags = [
            'time_clauses' => Tag::firstOrCreate(['name' => 'Time Clauses'], ['category' => 'Grammar Focus'])->id,
            'reason_clauses' => Tag::firstOrCreate(['name' => 'Reason Clauses'], ['category' => 'Grammar Focus'])->id,
            'inversion' => Tag::firstOrCreate(['name' => 'Inversion Structures'], ['category' => 'Grammar Focus'])->id,
            'interrupted_actions' => Tag::firstOrCreate(['name' => 'Interrupted Actions'], ['category' => 'Grammar Focus'])->id,
            'simultaneous_actions' => Tag::firstOrCreate(['name' => 'Simultaneous Actions'], ['category' => 'Grammar Focus'])->id,
            'passive_voice' => Tag::firstOrCreate(['name' => 'Passive Voice'], ['category' => 'Grammar Focus'])->id,
        ];

        $tenseTags = [
            'Past Perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses'])->id,
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses'])->id,
            'Past Continuous' => Tag::firstOrCreate(['name' => 'Past Continuous'], ['category' => 'Tenses'])->id,
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $questions = [
            [
                'text' => 'By the time he was 25, he _____ a million pounds.',
                'answer' => 'had made',
                'options' => ['made', 'was making', 'has made', 'had made'],
                'explanations' => [
                    'made' => "❌ Past Simple (V2). Використовується для події у минулому, але тут важлива дія, що завершилась до цього віку.",
                    'was making' => '❌ Past Continuous = was/were + V-ing. Показує процес, а не завершений факт.',
                    'has made' => '❌ Present Perfect. Ми говоримо про минуле, тому ця форма неправильна.',
                    'had made' => '✅ Past Perfect = had + V3. Правильно, бо дія завершилася до конкретного моменту у минулому (25 років).',
                ],
                'hint' => "Час: Past Perfect.  \nФормула: **had + V3**.  \nПриклад: *By the time she was 18, she had finished school.*",
                'tense' => 'Past Perfect',
                'level' => 'B1',
                'detail_tag' => 'time_clauses',
            ],
            [
                'text' => 'After he _____ his course, he went to England to continue his study.',
                'answer' => 'had finished',
                'options' => ['finished', 'had finished', 'has finished', 'was finished'],
                'explanations' => [
                    'finished' => '❌ Past Simple. Можливе вживання, але краще показати дію раніше іншої.',
                    'had finished' => '✅ Past Perfect. Показує, що він закінчив курс перед тим, як поїхати.',
                    'has finished' => '❌ Present Perfect. Неправильний час для минулих подій.',
                    'was finished' => '❌ Passive voice, форма не підходить.',
                ],
                'hint' => "Час: Past Perfect.  \nФормула: **had + V3**.  \nПриклад: *After he had eaten, he left the house.*",
                'tense' => 'Past Perfect',
                'level' => 'B1',
                'detail_tag' => 'time_clauses',
            ],
            [
                'text' => 'Alex didn’t come to see the film last night because he _____ it before.',
                'answer' => 'had seen',
                'options' => ['saw', 'had seen', 'has been seen', 'was seeing'],
                'explanations' => [
                    'saw' => '❌ Past Simple. Не підкреслює, що дія відбулася раніше іншої.',
                    'had seen' => '✅ Past Perfect. Він бачив цей фільм раніше, тому не прийшов.',
                    'has been seen' => '❌ Present Perfect Passive. Некоректно в цьому контексті.',
                    'was seeing' => '❌ Past Continuous. Не відповідає змісту.',
                ],
                'hint' => "Час: Past Perfect.  \nФормула: **had + V3**.  \nПриклад: *He didn’t watch the movie because he had seen it before.*",
                'tense' => 'Past Perfect',
                'level' => 'B1',
                'detail_tag' => 'reason_clauses',
            ],
            [
                'text' => 'No sooner _____ the telephone down than her boss rang back.',
                'answer' => 'had she put',
                'options' => ['had she put', 'did she put', 'she had put', 'she put'],
                'explanations' => [
                    'had she put' => '✅ Інверсія з No sooner. Правильна форма: *No sooner had + subject + V3 …*',
                    'did she put' => '❌ Past Simple із допоміжним дієсловом. Не підходить.',
                    'she had put' => '❌ Немає інверсії після *No sooner*.',
                    'she put' => '❌ Past Simple. Немає граматичної відповідності.',
                ],
                'hint' => "Структура: *No sooner had + subject + V3 … than …*.  \nПриклад: *No sooner had I arrived than it started to rain.*",
                'tense' => 'Past Perfect (inversion)',
                'level' => 'B2',
                'detail_tag' => 'inversion',
            ],
            [
                'text' => 'As soon as Martina saw the fire, she _____ the fire department.',
                'answer' => 'made',
                'options' => ['made', 'had finished', 'has finished', 'was finished'],
                'explanations' => [
                    'made' => '✅ Past Simple. Подія відбулася одразу після того, як вона побачила вогонь.',
                    'had finished' => '❌ Past Perfect. Не має сенсу.',
                    'has finished' => '❌ Present Perfect. Подія відбулася у минулому.',
                    'was finished' => '❌ Passive voice. Не підходить.',
                ],
                'hint' => "Час: Past Simple.  \nФормула: **V2**.  \nПриклад: *As soon as he saw me, he smiled.*",
                'tense' => 'Past Simple',
                'level' => 'A2',
                'detail_tag' => 'time_clauses',
            ],
            [
                'text' => 'I was reading the book when the phone _____.',
                'answer' => 'rang',
                'options' => ['was ringing', 'had rung', 'rang', 'has rung'],
                'explanations' => [
                    'was ringing' => '❌ Past Continuous. Можливий варіант, але тут потрібно показати коротку дію.',
                    'had rung' => '❌ Past Perfect. Не має сенсу, бо дія трапилася одночасно.',
                    'rang' => '✅ Past Simple. Телефон задзвонив під час процесу читання.',
                    'has rung' => '❌ Present Perfect. Неправильний час.',
                ],
                'hint' => "Час: Past Simple.  \nФормула: **V2**.  \nПриклад: *I was cooking when the phone rang.*",
                'tense' => 'Past Simple',
                'level' => 'A2',
                'detail_tag' => 'interrupted_actions',
            ],
            [
                'text' => 'My brother fell while he _____ his bicycle and hurt himself.',
                'answer' => 'was riding',
                'options' => ['was riding', 'had ridden', 'rid', 'has ridden'],
                'explanations' => [
                    'was riding' => '✅ Past Continuous = was/were + V-ing. Процес у момент падіння.',
                    'had ridden' => '❌ Past Perfect. Не підходить, бо дії були одночасні.',
                    'rid' => '❌ Неправильна форма.',
                    'has ridden' => '❌ Present Perfect. Неправильний час.',
                ],
                'hint' => "Час: Past Continuous.  \nФормула: **was/were + V-ing**.  \nПриклад: *He fell while he was running.*",
                'tense' => 'Past Continuous',
                'level' => 'A2',
                'detail_tag' => 'simultaneous_actions',
            ],
            [
                'text' => 'When I went out of the restaurant, I realized that my car _____.',
                'answer' => 'had disappeared',
                'options' => ['had disappeared', 'has disappeared', 'disappeared', 'was disappearing'],
                'explanations' => [
                    'had disappeared' => '✅ Past Perfect. Машина зникла до того моменту, коли я вийшов.',
                    'has disappeared' => '❌ Present Perfect. Це вже минула дія.',
                    'disappeared' => '❌ Past Simple. Не підкреслює «раніше іншої дії».',
                    'was disappearing' => '❌ Past Continuous. Не підходить.',
                ],
                'hint' => "Час: Past Perfect.  \nФормула: **had + V3**.  \nПриклад: *When I arrived, the bus had left.*",
                'tense' => 'Past Perfect',
                'level' => 'B1',
                'detail_tag' => 'time_clauses',
            ],
            [
                'text' => 'Over 28,000 people _____ in the tsunami.',
                'answer' => 'were killed',
                'options' => ['was killing', 'had killed', 'were killed', 'have been killed'],
                'explanations' => [
                    'was killing' => '❌ Past Continuous. Некоректно для пасиву.',
                    'had killed' => '❌ Past Perfect Active. Не пасивна форма.',
                    'were killed' => '✅ Past Simple Passive. Правильна форма: to be (were) + V3.',
                    'have been killed' => '❌ Present Perfect Passive. Подія відбулася в минулому.',
                ],
                'hint' => "Час: Past Simple Passive.  \nФормула: **was/were + V3**.  \nПриклад: *Thousands of people were killed in the war.*",
                'tense' => 'Past Simple Passive',
                'level' => 'B1',
                'detail_tag' => 'passive_voice',
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questions as $index => $question) {
            [$questionText, $markers] = $this->replaceBlanks($question['text']);
            $marker = $markers[0] ?? 'a1';

            $answers = [
                [
                    'marker' => $marker,
                    'answer' => $question['answer'],
                    'verb_hint' => null,
                ],
            ];

            $tagIds = [$generalTagId];

            $tenseName = $this->normalizeTenseName($question['tense']);
            if (isset($tenseTags[$tenseName])) {
                $tagIds[] = $tenseTags[$tenseName];
            }

            if (isset($detailTags[$question['detail_tag']])) {
                $tagIds[] = $detailTags[$question['detail_tag']];
            }

            $uuid = $this->generateQuestionUuid($index + 1, $questionText);

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $question['level'],
                'tag_ids' => array_values(array_unique($tagIds)),
                'answers' => $answers,
                'options' => $question['options'],
                'variants' => [$question['text']],
            ];

            $optionMarkerMap = [];
            foreach ($question['options'] as $option) {
                $optionMarkerMap[$option] = $marker;
            }

            $meta[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'answers' => [$marker => $question['answer']],
                'option_markers' => $optionMarkerMap,
                'hints' => [$marker => $question['hint']],
                'explanations' => $question['explanations'],
            ];
        }

        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $marker ? ($data['answers'][$marker] ?? reset($data['answers'])) : reset($data['answers']);

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    private function replaceBlanks(string $text): array
    {
        $markers = [];
        $index = 1;
        while (str_contains($text, '_____')) {
            $marker = 'a' . $index;
            $text = preg_replace('/_____/', '{' . $marker . '}', $text, 1);
            $markers[] = $marker;
            $index++;
        }

        return [$text, $markers];
    }

    private function normalizeTenseName(string $tense): string
    {
        if (str_contains($tense, 'Past Perfect')) {
            return 'Past Perfect';
        }

        if (str_contains($tense, 'Past Simple')) {
            return 'Past Simple';
        }

        if (str_contains($tense, 'Past Continuous')) {
            return 'Past Continuous';
        }

        return $tense;
    }

    protected function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            if ($text === null || $text === '') {
                continue;
            }

            $parts[] = '{' . $marker . '} ' . ltrim($text);
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }
}
