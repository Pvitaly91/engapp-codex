<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Ramsey\Uuid\Uuid;

class PastPerfectSimpleVsContinuousCTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Past'])->id;

        $sources = [
            'C' => Source::firstOrCreate(['name' => 'Past Perfect Simple vs Continuous — Section C'])->id,
            'D' => Source::firstOrCreate(['name' => 'Past Tense Mixed Multiple Choice — Section D'])->id,
        ];

        $sectionTags = [
            'C' => Tag::firstOrCreate(['name' => 'Past Perfect Simple vs Continuous'], ['category' => 'Grammar'])->id,
            'D' => Tag::firstOrCreate(['name' => 'Mixed Past Tense Practice'], ['category' => 'Grammar'])->id,
        ];

        $questionData = [
            'C' => [
                [
                    'question' => 'I lost the earrings she {a1} me.',
                    'verb_hint' => ['a1' => '(give)'],
                    'options' => ['had given', 'gave', 'has given'],
                    'answers' => ['a1' => 'had given'],
                    'explanations' => [
                        'had given' => '✅ Правильна відповідь. Past Perfect підкреслює, що подарунок відбувся до іншої минулої події (втрати сережок).',
                        'gave' => '❌ Past Simple позначає минулу дію, але не передає попередності.',
                        'has given' => '❌ Present Perfect описує теперішній результат, але контекст — минулий.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect: had + V3. Використовується, коли одна дія відбулася перед іншою у минулому.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'When I arrived at the theatre, the play {a1}.',
                    'verb_hint' => ['a1' => '(already/start)'],
                    'options' => ['had already started', 'already started', 'has already started'],
                    'answers' => ['a1' => 'had already started'],
                    'explanations' => [
                        'had already started' => '✅ Правильна відповідь. Past Perfect показує, що вистава почалася ДО мого приходу.',
                        'already started' => '❌ Past Simple не підкреслює послідовність.',
                        'has already started' => '❌ Present Perfect вказує на теперішнє, а не минуле.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple: had + already + V3.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'She was angry because she {a1} for me for more than two hours.',
                    'verb_hint' => ['a1' => '(wait)'],
                    'options' => ['had been waiting', 'was waiting', 'waited'],
                    'answers' => ['a1' => 'had been waiting'],
                    'explanations' => [
                        'had been waiting' => '✅ Правильна відповідь. Past Perfect Continuous показує тривале очікування перед іншою подією.',
                        'was waiting' => '❌ Past Continuous описує процес у моменті, але не тривалість до іншої події.',
                        'waited' => '❌ Past Simple — лише факт, без відтінку тривалості.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Continuous: had been + V-ing. Для вираження довготривалості.',
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'Alex was Rita’s best friend. She {a1} him all her life.',
                    'verb_hint' => ['a1' => '(know)'],
                    'options' => ['had known', 'knew', 'has known'],
                    'answers' => ['a1' => 'had known'],
                    'explanations' => [
                        'had known' => '✅ Правильна відповідь. Past Perfect Simple підкреслює тривалий досвід до певного моменту.',
                        'knew' => '❌ Past Simple можливий, але не настільки чітко виражає тривалість і попередність.',
                        'has known' => '❌ Present Perfect відноситься до теперішнього.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple: had + V3. Використовується для тривалого досвіду до певного моменту.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'I was exhausted because I {a1} the house all day.',
                    'verb_hint' => ['a1' => '(clean)'],
                    'options' => ['had been cleaning', 'was cleaning', 'cleaned'],
                    'answers' => ['a1' => 'had been cleaning'],
                    'explanations' => [
                        'had been cleaning' => '✅ Правильна відповідь. Past Perfect Continuous підкреслює тривалий процес прибирання до моменту, коли я був виснажений.',
                        'was cleaning' => '❌ Past Continuous — дія в конкретний момент, а не весь день.',
                        'cleaned' => '❌ Past Simple — лише факт, без акценту на тривалості.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Continuous: had been + V-ing. Для дій, що тривали довго до іншої події.',
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'Everything was white because it {a1} the night before.',
                    'verb_hint' => ['a1' => '(snow)'],
                    'options' => ['had snowed', 'snowed', 'was snowing'],
                    'answers' => ['a1' => 'had snowed'],
                    'explanations' => [
                        'had snowed' => '✅ Правильна відповідь. Past Perfect показує, що сніг випав ДО того, як ми побачили результат.',
                        'snowed' => '❌ Past Simple не підкреслює попередність.',
                        'was snowing' => '❌ Past Continuous означає процес, але не завершений результат.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple: had + V3.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'I {a1} Rebecca for years but I recognized her the moment I saw her.',
                    'verb_hint' => ['a1' => '(not/see)'],
                    'options' => ['had not seen', 'did not see', 'have not seen'],
                    'answers' => ['a1' => 'had not seen'],
                    'explanations' => [
                        'had not seen' => '✅ Правильна відповідь. Past Perfect Simple показує, що відсутність бачення тривала ДО зустрічі.',
                        'did not see' => '❌ Past Simple передає факт, але не тривалість і попередність.',
                        'have not seen' => '❌ Present Perfect стосується теперішнього.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple (negative): had not + V3.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
            ],
            'D' => [
                [
                    'question' => 'I was relieved when I found my keys. I {a1} for them for hours.',
                    'verb_hint' => ['a1' => '(look)'],
                    'options' => ['had looked', 'had been looking'],
                    'answers' => ['a1' => 'had been looking'],
                    'explanations' => [
                        'had looked' => '❌ Past Perfect Simple підкреслює результат, але тут важливий процес пошуку.',
                        'had been looking' => '✅ Правильна відповідь. Past Perfect Continuous показує, що пошук тривав довгий час перед тим, як я знайшов ключі.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Continuous: had been + V-ing.',
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'We were very hungry because we {a1} anything.',
                    'verb_hint' => ['a1' => '(eat/not)'],
                    'options' => ['hadn’t been eating', 'hadn’t eaten'],
                    'answers' => ['a1' => 'hadn’t eaten'],
                    'explanations' => [
                        'hadn’t been eating' => '❌ Continuous міг би підкреслити тривалість, але \'eat\' тут краще виразити як результат.',
                        'hadn’t eaten' => '✅ Правильна відповідь. Past Perfect Simple показує, що ми нічого не їли ДО цього моменту, тому були голодні.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple: had + not + V3.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'The earthquake {a1} the house that they had built.',
                    'verb_hint' => ['a1' => '(destroy)'],
                    'options' => ['had destroyed', 'destroyed'],
                    'answers' => ['a1' => 'destroyed'],
                    'explanations' => [
                        'had destroyed' => '❌ Past Perfect означав би, що руйнування сталось до іншої минулої дії. Але в реченні це основна подія.',
                        'destroyed' => '✅ Правильна відповідь. Past Simple описує факт землетрусу і руйнування.',
                    ],
                    'hints' => [
                        'a1' => 'Past Simple: V2. Використовується для головних подій у минулому.',
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'When the kids went out to play, they {a1} their homework.',
                    'verb_hint' => ['a1' => '(already/do)'],
                    'options' => ['had already been doing', 'had already done'],
                    'answers' => ['a1' => 'had already done'],
                    'explanations' => [
                        'had already been doing' => '❌ Continuous підкреслює процес, але тут важливий завершений результат.',
                        'had already done' => '✅ Правильна відповідь. Past Perfect Simple підкреслює завершення дії до іншої у минулому.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Simple: had + already + V3.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'They {a1} all the food that I had prepared.',
                    'verb_hint' => ['a1' => '(eat)'],
                    'options' => ['ate', 'had been eating'],
                    'answers' => ['a1' => 'ate'],
                    'explanations' => [
                        'ate' => '✅ Правильна відповідь. Past Simple описує завершену дію — вони з’їли їжу.',
                        'had been eating' => '❌ Continuous підкреслює процес, але тут важливий факт завершення.',
                    ],
                    'hints' => [
                        'a1' => 'Past Simple: V2. Використовується для завершених подій.',
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'The children {a1} wet because they had been playing in the lake.',
                    'verb_hint' => ['a1' => '(be)'],
                    'options' => ['were', 'had been'],
                    'answers' => ['a1' => 'were'],
                    'explanations' => [
                        'were' => '✅ Правильна відповідь. Past Simple описує стан у минулому — вони були мокрі.',
                        'had been' => '❌ Past Perfect вимагав би продовження, наприклад \'had been tired\'. Тут це неприродно.',
                    ],
                    'hints' => [
                        'a1' => 'Past Simple: was/were + adj/noun.',
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'We {a1} the film when the lights went out.',
                    'verb_hint' => ['a1' => '(watch)'],
                    'options' => ['had watched', 'watched'],
                    'answers' => ['a1' => 'watched'],
                    'explanations' => [
                        'had watched' => '❌ Past Perfect означає, що ми вже закінчили дивитися до відключення світла, що не відповідає контексту.',
                        'watched' => '✅ Правильна відповідь. Past Simple описує дію, що відбувалася тоді, коли зникло світло.',
                    ],
                    'hints' => [
                        'a1' => 'Past Simple: V2.',
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'Little John’s face was dirty because he {a1} chocolate.',
                    'verb_hint' => ['a1' => '(eat)'],
                    'options' => ['ate', 'had been eating'],
                    'answers' => ['a1' => 'had been eating'],
                    'explanations' => [
                        'ate' => '❌ Past Simple означає факт, але не пояснює брудного обличчя як результат процесу.',
                        'had been eating' => '✅ Правильна відповідь. Past Perfect Continuous підкреслює процес, наслідки якого були помітні.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Continuous: had been + V-ing.',
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'When he {a1}, she had already gone to bed.',
                    'verb_hint' => ['a1' => '(return)'],
                    'options' => ['had returned', 'returned'],
                    'answers' => ['a1' => 'returned'],
                    'explanations' => [
                        'had returned' => '❌ Past Perfect означав би, що його повернення сталося РАНІШЕ від її сну, але у реченні все навпаки.',
                        'returned' => '✅ Правильна відповідь. Past Simple описує подію, яка сталася після іншої (вона вже лягла).',
                    ],
                    'hints' => [
                        'a1' => 'Past Simple: V2.',
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'They {a1} all day, so when we met them they were really tired.',
                    'verb_hint' => ['a1' => '(work)'],
                    'options' => ['had been working', 'had worked'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'had been working' => '✅ Правильна відповідь. Past Perfect Continuous підкреслює довготривалу роботу, яка пояснює втому.',
                        'had worked' => '❌ Past Perfect Simple підкреслює лише факт роботи, але не її тривалість.',
                    ],
                    'hints' => [
                        'a1' => 'Past Perfect Continuous: had been + V-ing.',
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
            ],
        ];

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $tenseTagIds = [];
        foreach ($questionData as $sectionQuestions) {
            foreach ($sectionQuestions as $question) {
                foreach ($question['tense'] as $tenseName) {
                    if (! isset($tenseTagIds[$tenseName])) {
                        $tenseTagIds[$tenseName] = Tag::firstOrCreate(['name' => $tenseName], ['category' => 'Tenses'])->id;
                    }
                }
            }
        }

        $service = new QuestionSeedingService();
        $items = [];
        $meta = [];

        foreach ($questionData as $sectionKey => $questions) {
            foreach ($questions as $index => $question) {
                $uuid = (string) Uuid::uuid5(Uuid::NAMESPACE_URL, "past-perfect-simple-vs-continuous-c-test-{$sectionKey}-{$index}-{$question['question']}");
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
                }

                $tagIds = [$sectionTags[$sectionKey]];
                foreach ($question['tense'] as $tenseName) {
                    $tagIds[] = $tenseTagIds[$tenseName];
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $question['question'],
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sources[$sectionKey],
                    'flag' => 0,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'question' => $question['question'],
                    'answers' => $question['answers'],
                    'option_markers' => $optionMarkerMap,
                    'hints' => $question['hints'],
                    'explanations' => $question['explanations'],
                ];
            }
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

    private function normalizeHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() \t\n\r");
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $parts[] = '{' . $marker . '} ' . ltrim($text);
        }

        return implode("\n", $parts);
    }
}
