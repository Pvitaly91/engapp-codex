<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ActivePassiveV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Passive Voice'])->id;
        $sourceIds = [
            'page1' => Source::firstOrCreate(['name' => 'Custom: Active and Passive Voice (Page 1)'])->id,
            'page2' => Source::firstOrCreate(['name' => 'Custom: Active and Passive Voice (Page 2)'])->id,
            'page3' => Source::firstOrCreate(['name' => 'Custom: Active and Passive Voice (Page 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Active and Passive Voice'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Voice Transformation'],
            ['category' => 'English Grammar Structure']
        )->id;

        $tensesTagId = Tag::firstOrCreate(
            ['name' => 'Mixed Tenses'],
            ['category' => 'English Grammar Focus']
        )->id;

        $questions = $this->questionEntries();

        $tagIds = [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $tensesTagId,
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
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
    }

    private function questionEntries(): array
    {
        return [
            // Page 1 - Questions 1-7
            [
                'question' => 'This problem {a1} by your brother yesterday',
                'options' => [
                    'a1' => ['was solved', 'will be solved', 'is solved', 'solves'],
                ],
                'answers' => ['a1' => 'was solved'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'solve'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) використовується для дій, що відбулися в минулому.',
                    'Слово "yesterday" вказує на минулий час.',
                    'Проблему вирішили (пасивний стан), а не вона вирішила (активний стан).',
                ],
                'explanations' => [
                    'a1' => [
                        'was solved' => '✅ Past Simple Passive (was solved) правильно описує дію, що відбулася вчора. Формула: was/were + past participle.',
                        'will be solved' => '❌ Майбутній час (will be) не підходить, бо є слово "yesterday" (вчора), що вказує на минулий час.',
                        'is solved' => '❌ Теперішній час (is solved) не підходить для дії, що сталася вчора.',
                        'solves' => '❌ Це активний стан у теперішньому часі. Потрібен пасивний стан у минулому часі.',
                    ],
                ],
            ],
            [
                'question' => 'My father wrote this book. It {a1} by my father',
                'options' => [
                    'a1' => ['will be wrote', 'was write', 'was written', 'is written'],
                ],
                'answers' => ['a1' => 'was written'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'write'],
                'hints' => [
                    '**Past Simple Passive** потребує форми was/were + past participle (written, not write or wrote).',
                    'Перше речення "wrote" вказує на минулий час, тому друге речення також має бути в минулому часі.',
                    'Past participle від "write" є "written".',
                ],
                'explanations' => [
                    'a1' => [
                        'was written' => '✅ Past Simple Passive (was written) правильний. "Written" є past participle від "write". Формула: was/were + past participle.',
                        'will be wrote' => '❌ Майбутній час не підходить. Також "wrote" є past simple, а не past participle. Потрібно "written".',
                        'was write' => '❌ Після "was" потрібен past participle (written), а не інфінітив (write).',
                        'is written' => '❌ Теперішній час не підходить, бо перше речення в минулому часі (wrote).',
                    ],
                ],
            ],
            [
                'question' => 'This clock {a1} in 1750',
                'options' => [
                    'a1' => ['is made', 'was made', 'is making', 'will be made'],
                ],
                'answers' => ['a1' => 'was made'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    'Конкретна дата в минулому (1750) вимагає Past Simple.',
                    'Годинник виготовили (пасив), а не він щось виготовляв (актив).',
                    '**Past Simple Passive** = was/were + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'was made' => '✅ Past Simple Passive (was made) правильний для події, що сталася в конкретному році в минулому.',
                        'is made' => '❌ Теперішній час не підходить для події, що сталася в 1750 році.',
                        'is making' => '❌ Present Continuous Active не підходить. Потрібен пасивний стан у минулому часі.',
                        'will be made' => '❌ Майбутній час не підходить для події, що вже сталася в 1750 році.',
                    ],
                ],
            ],
            [
                'question' => 'Bronson scored a goal. Yes, a goal {a1} by Bronson',
                'options' => [
                    'a1' => ['is scored', 'scored', 'will be scored', 'was scored'],
                ],
                'answers' => ['a1' => 'was scored'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'score'],
                'hints' => [
                    'Перше речення "scored" (Past Simple) вказує на минулий час.',
                    'Потрібен пасивний стан: гол забили (was scored), а не гол забив (scored).',
                    'В пасивному стані додаємо "by" для вказівки виконавця дії.',
                ],
                'explanations' => [
                    'a1' => [
                        'was scored' => '✅ Past Simple Passive (was scored) правильний. Відповідає першому реченню в минулому часі.',
                        'is scored' => '❌ Теперішній час не відповідає минулому часу першого речення (scored).',
                        'scored' => '❌ Це активний стан. В другому реченні потрібен пасивний стан: "a goal was scored".',
                        'will be scored' => '❌ Майбутній час не відповідає минулому часу першого речення.',
                    ],
                ],
            ],
            [
                'question' => 'This job {a1} by my friend next week',
                'options' => [
                    'a1' => ['is done', 'did', 'will be done', 'was done'],
                ],
                'answers' => ['a1' => 'will be done'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'do'],
                'hints' => [
                    '**Future Simple Passive** (will be + past participle) для майбутніх дій.',
                    '"Next week" вказує на майбутній час.',
                    'Роботу виконають (пасив), а не вона виконає (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'will be done' => '✅ Future Simple Passive (will be done) правильний для дії, що відбудеться наступного тижня.',
                        'is done' => '❌ Теперішній час не підходить для майбутньої дії (next week).',
                        'did' => '❌ Past Simple Active не підходить. Потрібен пасивний стан у майбутньому часі.',
                        'was done' => '❌ Минулий час не підходить для майбутньої дії (next week).',
                    ],
                ],
            ],
            [
                'question' => 'This house was {a1} my grandfather',
                'options' => [
                    'a1' => ['build for', 'build by', 'built for', 'built by'],
                ],
                'answers' => ['a1' => 'built by'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    'Після "was" потрібен **past participle** (built), а не інфінітив (build).',
                    '**By** вказує на виконавця дії в пасивному стані.',
                    '**For** вказує на призначення або користь, що не підходить тут.',
                ],
                'explanations' => [
                    'a1' => [
                        'built by' => '✅ "Built" є past participle від "build". "By" правильно вказує на виконавця дії (grandfather).',
                        'build for' => '❌ "Build" є інфінітивом. Після "was" потрібен past participle "built". Також "for" не підходить для вказівки виконавця.',
                        'build by' => '❌ "Build" є інфінітивом. Після "was" потрібен past participle "built".',
                        'built for' => '❌ "For" вказує на призначення (для кого/чого), а не на виконавця. Для виконавця використовується "by".',
                    ],
                ],
            ],
            [
                'question' => 'This exercise will {a1} at home by me',
                'options' => [
                    'a1' => ['be doing', 'do', 'have done', 'be done'],
                ],
                'answers' => ['a1' => 'be done'],
                'level' => 'A2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'do'],
                'hints' => [
                    '**Future Simple Passive** = will + be + past participle.',
                    'Після "will" в пасивному стані потрібно "be + past participle".',
                    'Вправу виконають (пасив), а не вона щось виконає (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'be done' => '✅ Future Simple Passive (will be done) правильний. Формула: will + be + past participle.',
                        'be doing' => '❌ "Be doing" є continuous form активного стану, а не пасивного.',
                        'do' => '❌ Інфінітив активного стану. В пасивному стані потрібно "be done".',
                        'have done' => '❌ "Have done" є perfect form активного стану, а не пасивного.',
                    ],
                ],
            ],

            // Page 2 - Questions 8-14
            [
                'question' => 'Was the window pane {a1} the children?',
                'options' => [
                    'a1' => ['broke', 'broken by', 'broke for', 'broken for'],
                ],
                'answers' => ['a1' => 'broken by'],
                'level' => 'A2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'break'],
                'hints' => [
                    'В питаннях Past Simple Passive: Was/Were + subject + past participle + by?',
                    '"Broken" є past participle від "break".',
                    '"By" вказує на виконавця дії.',
                ],
                'explanations' => [
                    'a1' => [
                        'broken by' => '✅ "Broken" є past participle від "break". "By" правильно вказує на виконавця (children).',
                        'broke' => '❌ "Broke" є past simple, а не past participle. Після "was" потрібен past participle "broken".',
                        'broke for' => '❌ "Broke" є past simple. Потрібен past participle "broken". Також "for" не підходить для вказівки виконавця.',
                        'broken for' => '❌ "For" вказує на призначення, а не на виконавця. Для виконавця використовується "by".',
                    ],
                ],
            ],
            [
                'question' => 'All the beds were {a1} my grandmother',
                'options' => [
                    'a1' => ['made by', 'made for', 'make by', 'make for'],
                ],
                'answers' => ['a1' => 'made by'],
                'level' => 'A2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'make'],
                'hints' => [
                    'Після "were" потрібен past participle (made), а не інфінітив (make).',
                    '"By" вказує на виконавця дії в пасивному стані.',
                    'Past participle від "make" також "made".',
                ],
                'explanations' => [
                    'a1' => [
                        'made by' => '✅ "Made" є past participle від "make". "By" правильно вказує на виконавця дії (grandmother).',
                        'made for' => '❌ "For" вказує на призначення (для кого), а не на виконавця. Для виконавця використовується "by".',
                        'make by' => '❌ "Make" є інфінітивом. Після "were" потрібен past participle "made".',
                        'make for' => '❌ "Make" є інфінітивом. Після "were" потрібен past participle "made". Також "for" не підходить.',
                    ],
                ],
            ],
            [
                'question' => 'Many writers were {a1} Shakespeare',
                'options' => [
                    'a1' => ['influence by', 'influenced by', 'influence for', 'influenced for'],
                ],
                'answers' => ['a1' => 'influenced by'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'influence'],
                'hints' => [
                    'Past participle від "influence" є "influenced".',
                    '"By" вказує на джерело впливу або виконавця дії.',
                    'Письменників вплинув Шекспір (пасив).',
                ],
                'explanations' => [
                    'a1' => [
                        'influenced by' => '✅ "Influenced" є past participle від "influence". "By" правильно вказує на джерело впливу (Shakespeare).',
                        'influence by' => '❌ "Influence" є інфінітивом або present simple. Після "were" потрібен past participle "influenced".',
                        'influence for' => '❌ "Influence" є інфінітивом. Потрібен past participle. Також "for" не підходить для вказівки джерела впливу.',
                        'influenced for' => '❌ "For" не використовується з дієсловом "influence" для вказівки джерела впливу. Потрібно "by".',
                    ],
                ],
            ],
            [
                'question' => 'The money {a1} stolen by the thieves if you leave it there',
                'options' => [
                    'a1' => ['was', 'will be', 'has been', 'is'],
                ],
                'answers' => ['a1' => 'will be'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'be (допоміжне дієслово)'],
                'hints' => [
                    'Умовне речення з "if" в Present Simple вимагає Future Simple у головній частині.',
                    '**First Conditional Passive** = will be + past participle.',
                    '"If you leave" (Present Simple) → "will be stolen" (Future Simple Passive).',
                ],
                'explanations' => [
                    'a1' => [
                        'will be' => '✅ First Conditional Passive (will be stolen) правильний для реальної умови в майбутньому. If + Present Simple → will + infinitive.',
                        'was' => '❌ Минулий час не підходить для умови про майбутнє.',
                        'has been' => '❌ Present Perfect не підходить для умовного речення про майбутнє.',
                        'is' => '❌ Present Simple не підходить для результату умовного речення про майбутнє.',
                    ],
                ],
            ],
            [
                'question' => 'Mr Johnson {a1} this book',
                'options' => [
                    'a1' => ['is translated', 'translated by', 'translated', 'was translated'],
                ],
                'answers' => ['a1' => 'translated'],
                'level' => 'A2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'translate'],
                'hints' => [
                    'Mr Johnson є суб\'єктом, який виконує дію (переклав книгу).',
                    'Потрібен **активний стан**, а не пасивний.',
                    'Відсутність контексту часу дозволяє використати Past Simple Active.',
                ],
                'explanations' => [
                    'a1' => [
                        'translated' => '✅ Past Simple Active (translated) правильний. Mr Johnson виконав дію (переклав книгу), тому потрібен активний стан.',
                        'is translated' => '❌ Пасивний стан неправильний. Mr Johnson переклав книгу (активна дія), а не книгу перекладають.',
                        'translated by' => '❌ "Translated by" використовується в пасивному стані, але тут потрібен активний стан без "by".',
                        'was translated' => '❌ Пасивний стан неправильний. Суб\'єкт (Mr Johnson) виконує дію, а не підлягає дії.',
                    ],
                ],
            ],
            [
                'question' => 'This policeman {a1} that man',
                'options' => [
                    'a1' => ['was arrested by', 'arrested for', 'arrested', 'will be arrested'],
                ],
                'answers' => ['a1' => 'arrested'],
                'level' => 'A2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    'Поліцейський є суб\'єктом, який виконує дію (арештував людину).',
                    'Потрібен **активний стан**: поліцейський арештував (arrested).',
                    'Пасивний стан був би: "The man was arrested by the policeman".',
                ],
                'explanations' => [
                    'a1' => [
                        'arrested' => '✅ Past Simple Active (arrested) правильний. Поліцейський виконав дію арешту, тому потрібен активний стан.',
                        'was arrested by' => '❌ Пасивний стан неправильний і граматично не підходить. Поліцейський арештував (актив), а не його арештували.',
                        'arrested for' => '❌ "For" не підходить. Потрібна пряма конструкція: arrested + об\'єкт (that man).',
                        'will be arrested' => '❌ Майбутній час і пасивний стан не підходять. Потрібен активний стан у минулому або теперішньому.',
                    ],
                ],
            ],
            [
                'question' => 'We will {a1} by that teacher',
                'options' => [
                    'a1' => ['be teached', 'have taught', 'be taught', 'been taught'],
                ],
                'answers' => ['a1' => 'be taught'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'teach'],
                'hints' => [
                    '**Future Simple Passive** = will + be + past participle.',
                    'Past participle від "teach" є "taught" (неправильне дієслово).',
                    '"Teached" не існує - це помилкова форма.',
                ],
                'explanations' => [
                    'a1' => [
                        'be taught' => '✅ Future Simple Passive (will be taught) правильний. "Taught" є past participle від "teach". Формула: will + be + past participle.',
                        'be teached' => '❌ "Teached" не існує в англійській мові. "Teach" є неправильним дієсловом: teach-taught-taught.',
                        'have taught' => '❌ "Have taught" є active perfect form. Потрібен пасивний стан: be taught.',
                        'been taught' => '❌ Після "will" потрібен інфінітив "be", а не past participle "been".',
                    ],
                ],
            ],

            // Page 3 - Questions 15-20
            [
                'question' => 'Many things {a1} in this house',
                'options' => [
                    'a1' => ['is said', 'are said', 'they say', 'they are said'],
                ],
                'answers' => ['a1' => 'are said'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'say'],
                'hints' => [
                    '**Present Simple Passive** для узагальнених фактів: is/are + past participle.',
                    '"Many things" (множина) потребує "are", а не "is".',
                    'Речі говорять (пасив), а не вони говорять речі (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'are said' => '✅ Present Simple Passive (are said) правильний. "Things" у множині потребують "are". Формула: are + past participle.',
                        'is said' => '❌ "Is" підходить для однини, а "things" — множина. Потрібно "are said".',
                        'they say' => '❌ Активний стан не підходить. Суб\'єкт "things", а не "they". Потрібен пасивний стан.',
                        'they are said' => '❌ Граматично неправильна конструкція. Суб\'єкт вже є (things), не потрібно "they".',
                    ],
                ],
            ],
            [
                'question' => 'This mansion {a1} in 1750',
                'options' => [
                    'a1' => ['is built', 'was built', 'will be built', 'builded'],
                ],
                'answers' => ['a1' => 'was built'],
                'level' => 'A2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'build'],
                'hints' => [
                    'Конкретна дата в минулому (1750) вимагає Past Simple.',
                    '**Past Simple Passive** = was/were + past participle.',
                    'Past participle від "build" є "built" (неправильне дієслово).',
                ],
                'explanations' => [
                    'a1' => [
                        'was built' => '✅ Past Simple Passive (was built) правильний для події в конкретному році минулого. "Built" є past participle від "build".',
                        'is built' => '❌ Теперішній час не підходить для події, що сталася в 1750 році.',
                        'will be built' => '❌ Майбутній час не підходить для події, що вже сталася в 1750 році.',
                        'builded' => '❌ "Builded" не існує. "Build" є неправильним дієсловом: build-built-built.',
                    ],
                ],
            ],
            [
                'question' => 'They will {a1} this car soon',
                'options' => [
                    'a1' => ['be bought', 'buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'buy'],
                'level' => 'A2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    '"They" є суб\'єктом, який виконує дію (купують машину).',
                    'Потрібен **активний стан**, а не пасивний.',
                    '**Future Simple Active** = will + інфінітив без "to".',
                ],
                'explanations' => [
                    'a1' => [
                        'buy' => '✅ Future Simple Active (will buy) правильний. Вони купують машину (активна дія). Формула: will + infinitive.',
                        'be bought' => '❌ Пасивний стан неправильний. "They" виконують дію, а не підлягають дії.',
                        'buying' => '❌ Після "will" потрібен інфінітив (buy), а не герундій (buying).',
                        'is bought' => '❌ Present Simple Passive не підходить. Потрібен активний стан у майбутньому часі.',
                    ],
                ],
            ],
            [
                'question' => 'This car will {a1} soon',
                'options' => [
                    'a1' => ['be bought', 'be buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'be bought'],
                'level' => 'A2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'buy'],
                'hints' => [
                    'Машина є суб\'єктом, який підлягає дії (машину куплять).',
                    'Потрібен **пасивний стан**: машину куплять (will be bought).',
                    '**Future Simple Passive** = will + be + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'be bought' => '✅ Future Simple Passive (will be bought) правильний. Машину куплять (пасивна дія). Формула: will + be + past participle.',
                        'be buying' => '❌ "Be buying" є continuous form активного стану, а не пасивного.',
                        'buy' => '❌ Активний стан неправильний. Машина не може купувати, її купують.',
                        'is bought' => '❌ Present Simple Passive не підходить для майбутньої дії. Потрібен Future Simple Passive.',
                    ],
                ],
            ],
            [
                'question' => 'The jar {a1} by the maid',
                'options' => [
                    'a1' => ['was broken', 'broke', 'is broken', 'breaks'],
                ],
                'answers' => ['a1' => 'was broken'],
                'level' => 'A2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'break'],
                'hints' => [
                    'Наявність "by the maid" вказує на пасивний стан.',
                    '**Past Simple Passive** = was/were + past participle.',
                    'Глечик розбили (пасив), а не він розбив (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'was broken' => '✅ Past Simple Passive (was broken) правильний. "By the maid" вказує на виконавця в пасивному стані.',
                        'broke' => '❌ Активний стан неправильний. Потрібен пасивний стан із "by the maid".',
                        'is broken' => '❌ Present Simple Passive може описувати стан, але контекст із "by the maid" вказує на конкретну минулу дію.',
                        'breaks' => '❌ Present Simple Active неправильний. Потрібен пасивний стан у минулому часі.',
                    ],
                ],
            ],
            [
                'question' => 'The centre forward {a1} a goal',
                'options' => [
                    'a1' => ['was scored', 'scores', 'is scored', 'has scored'],
                ],
                'answers' => ['a1' => 'scores'],
                'level' => 'A2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'score'],
                'hints' => [
                    'Нападник (centre forward) є суб\'єктом, який виконує дію (забиває гол).',
                    'Потрібен **активний стан**: нападник забиває (scores).',
                    'Present Simple Active для узагальнених фактів або звичайних дій.',
                ],
                'explanations' => [
                    'a1' => [
                        'scores' => '✅ Present Simple Active (scores) правильний. Нападник забиває гол (активна дія). Може описувати узагальнений факт або конкретну дію.',
                        'was scored' => '❌ Пасивний стан неправильний. Нападник забиває (актив), а не гол забивається нападником.',
                        'is scored' => '❌ Present Simple Passive неправильний. Суб\'єкт виконує дію, а не підлягає дії.',
                        'has scored' => '❌ Present Perfect може бути правильним, але в контексті без додаткової інформації про завершену дію, Present Simple більш підходящий.',
                    ],
                ],
            ],
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $values = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return array_values(array_unique($values));
    }
}
