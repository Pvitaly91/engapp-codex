<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class ActiveAndPassiveVoiceV2Seeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'Custom: Active and Passive Voice Practice'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Active vs Passive'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Voice Construction'],
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
                'source_id' => $sourceId,
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
            [
                'question' => 'This problem {a1} by your brother yesterday.',
                'options' => [
                    'a1' => ['was solved', 'will be solved', 'is solved', 'solves'],
                ],
                'answers' => ['a1' => 'was solved'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'solve (вирішувати)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) вживається для завершеної дії в минулому.',
                    'Слово "yesterday" вказує на минулий час.',
                    'Формула: was/were + V3 (past participle).',
                ],
                'explanations' => [
                    'a1' => [
                        'was solved' => '✅ Past Simple Passive (was solved) правильно описує дію, яка була завершена в минулому. "Yesterday" вказує на Past Simple.',
                        'will be solved' => '❌ Future Simple Passive. "Yesterday" вказує на минулий час, а не на майбутній.',
                        'is solved' => '❌ Present Simple Passive. "Yesterday" вказує на минулий час, а не на теперішній.',
                        'solves' => '❌ Це активний стан (Active Voice) Present Simple. Потрібен пасивний стан.',
                    ],
                ],
            ],
            [
                'question' => 'My father wrote this book. It {a1} by my father.',
                'options' => [
                    'a1' => ['will be wrote', 'was write', 'was written', 'is written'],
                ],
                'answers' => ['a1' => 'was written'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'write (писати)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) вживається для завершеної дії в минулому.',
                    'Перше речення в Past Simple Active ("wrote"), тому друге має бути в Past Simple Passive.',
                    '**Written** — третя форма дієслова write.',
                ],
                'explanations' => [
                    'a1' => [
                        'was written' => '✅ Past Simple Passive (was written) правильно. "Written" — це past participle від "write".',
                        'will be wrote' => '❌ Подвійна помилка: "will be" — це Future, а "wrote" — це Past Simple, не past participle.',
                        'was write' => '❌ Після "was" потрібен past participle (written), а не інфінітив (write).',
                        'is written' => '❌ Present Simple Passive. Контекст вказує на минулий час (wrote), тому потрібен Past Simple.',
                    ],
                ],
            ],
            [
                'question' => 'This clock {a1} in 1750.',
                'options' => [
                    'a1' => ['is made', 'was made', 'is making', 'will be made'],
                ],
                'answers' => ['a1' => 'was made'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'make (робити)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) вживається для завершеної дії в минулому.',
                    'Рік "1750" вказує на минулий час.',
                    '**Made** — третя форма дієслова make.',
                ],
                'explanations' => [
                    'a1' => [
                        'was made' => '✅ Past Simple Passive (was made) правильно. Дата "1750" вказує на минулий час.',
                        'is made' => '❌ Present Simple Passive. Дата "1750" вказує на минулий час, а не на теперішній.',
                        'is making' => '❌ Present Continuous Active. Потрібен пасивний стан у минулому часі.',
                        'will be made' => '❌ Future Simple Passive. Дата "1750" вказує на минулий час, а не на майбутній.',
                    ],
                ],
            ],
            [
                'question' => 'Bronson scored a goal. Yes, a goal {a1} by Bronson.',
                'options' => [
                    'a1' => ['is scored', 'scored', 'will be scored', 'was scored'],
                ],
                'answers' => ['a1' => 'was scored'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'score (забивати)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) вживається для завершеної дії в минулому.',
                    'Перше речення в Past Simple Active ("scored"), тому друге має бути в Past Simple Passive.',
                ],
                'explanations' => [
                    'a1' => [
                        'was scored' => '✅ Past Simple Passive (was scored) правильно відповідає минулому часу першого речення.',
                        'is scored' => '❌ Present Simple Passive. Перше речення в Past Simple, тому потрібен Past Simple Passive.',
                        'scored' => '❌ Це активний стан без допоміжного дієслова. Потрібен пасивний стан.',
                        'will be scored' => '❌ Future Simple Passive. Дія вже відбулася в минулому.',
                    ],
                ],
            ],
            [
                'question' => 'This job {a1} by my friend next week.',
                'options' => [
                    'a1' => ['is done', 'did', 'will be done', 'was done'],
                ],
                'answers' => ['a1' => 'will be done'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'do (робити)'],
                'hints' => [
                    '**Future Simple Passive** (will be + past participle) вживається для майбутньої дії.',
                    'Слова "next week" вказують на майбутній час.',
                ],
                'explanations' => [
                    'a1' => [
                        'will be done' => '✅ Future Simple Passive (will be done) правильно. "Next week" вказує на майбутній час.',
                        'is done' => '❌ Present Simple Passive. "Next week" вказує на майбутній час, а не на теперішній.',
                        'did' => '❌ Це активний стан Past Simple. Потрібен пасивний стан у майбутньому часі.',
                        'was done' => '❌ Past Simple Passive. "Next week" вказує на майбутній час, а не на минулий.',
                    ],
                ],
            ],
            [
                'question' => 'This house was {a1} my grandfather.',
                'options' => [
                    'a1' => ['build for', 'build by', 'built for', 'built by'],
                ],
                'answers' => ['a1' => 'built by'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'build (будувати)'],
                'hints' => [
                    '**Built** — третя форма (past participle) від дієслова "build".',
                    '**By** вживається для вказівки на виконавця дії в пасивному стані.',
                    '**For** вказує на призначення, а не на виконавця.',
                ],
                'explanations' => [
                    'a1' => [
                        'built by' => '✅ "Built" — правильна форма past participle, "by" вказує на виконавця дії.',
                        'build for' => '❌ "Build" — інфінітив, потрібен past participle "built". "For" вказує на призначення.',
                        'build by' => '❌ "Build" — інфінітив, потрібен past participle "built".',
                        'built for' => '❌ "For" вказує на призначення (для кого), а не на виконавця. Потрібен "by".',
                    ],
                ],
            ],
            [
                'question' => 'This exercise will {a1} at home by me.',
                'options' => [
                    'a1' => ['be doing', 'do', 'have done', 'be done'],
                ],
                'answers' => ['a1' => 'be done'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'do (робити)'],
                'hints' => [
                    '**Future Simple Passive** = will + be + past participle.',
                    'Після "will" у пасивному стані використовується "be + past participle".',
                ],
                'explanations' => [
                    'a1' => [
                        'be done' => '✅ Future Simple Passive (will be done) — правильна формула: will + be + V3.',
                        'be doing' => '❌ "Will be doing" — Future Continuous Active, а не Passive. Потрібен пасивний стан.',
                        'do' => '❌ Це активний стан. Потрібен пасивний стан з "be + past participle".',
                        'have done' => '❌ Це Perfect Active. Потрібен Future Simple Passive: will be + past participle.',
                    ],
                ],
            ],
            [
                'question' => 'Was the window pane {a1} the children?',
                'options' => [
                    'a1' => ['broke', 'broken by', 'broke for', 'broken for'],
                ],
                'answers' => ['a1' => 'broken by'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'break (ламати)'],
                'hints' => [
                    '**Broken** — третя форма (past participle) від дієслова "break".',
                    '**By** вживається для вказівки на виконавця дії в пасивному стані.',
                    'Формула питання: Was + subject + V3 + by + agent?',
                ],
                'explanations' => [
                    'a1' => [
                        'broken by' => '✅ "Broken" — past participle від "break", "by" вказує на виконавця дії.',
                        'broke' => '❌ "Broke" — Past Simple Active, а не past participle. Потрібен "broken".',
                        'broke for' => '❌ "Broke" — неправильна форма. "For" вказує на призначення, а не на виконавця.',
                        'broken for' => '❌ "For" вказує на призначення, а потрібен "by" для виконавця.',
                    ],
                ],
            ],
            [
                'question' => 'All the beds were {a1} my grandmother.',
                'options' => [
                    'a1' => ['made by', 'made for', 'make by', 'make for'],
                ],
                'answers' => ['a1' => 'made by'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'make (робити)'],
                'hints' => [
                    '**Made** — третя форма (past participle) від дієслова "make".',
                    '**By** вживається для вказівки на виконавця дії.',
                    'Формула: was/were + V3 + by + agent.',
                ],
                'explanations' => [
                    'a1' => [
                        'made by' => '✅ "Made" — правильна форма past participle, "by" вказує на виконавця.',
                        'made for' => '❌ "For" вказує на призначення (для кого зроблено), а не на виконавця дії.',
                        'make by' => '❌ "Make" — інфінітив, потрібен past participle "made".',
                        'make for' => '❌ "Make" — неправильна форма, і "for" неправильний прийменник.',
                    ],
                ],
            ],
            [
                'question' => 'Many writers were {a1} Shakespeare.',
                'options' => [
                    'a1' => ['influence by', 'influenced by', 'influence for', 'influenced for'],
                ],
                'answers' => ['a1' => 'influenced by'],
                'level' => 'B1',
                'verb_hints' => ['a1' => 'influence (впливати)'],
                'hints' => [
                    '**Influenced** — третя форма (past participle) від дієслова "influence".',
                    '**By** вживається для вказівки на виконавця дії в пасивному стані.',
                ],
                'explanations' => [
                    'a1' => [
                        'influenced by' => '✅ "Influenced" — past participle, "by" правильно вказує на джерело впливу.',
                        'influence by' => '❌ "Influence" — інфінітив, потрібен past participle "influenced".',
                        'influence for' => '❌ "Influence" — неправильна форма, і "for" неправильний прийменник.',
                        'influenced for' => '❌ "For" вказує на призначення, а потрібен "by" для виконавця.',
                    ],
                ],
            ],
            [
                'question' => 'The money {a1} stolen by the thieves if you leave it there.',
                'options' => [
                    'a1' => ['was', 'will be', 'has been', 'is'],
                ],
                'answers' => ['a1' => 'will be'],
                'level' => 'B1',
                'verb_hints' => ['a1' => 'steal (красти)'],
                'hints' => [
                    '**First Conditional** з пасивним станом: if + Present Simple, will be + V3.',
                    '"If you leave" — умова в Present Simple, головне речення в Future Passive.',
                ],
                'explanations' => [
                    'a1' => [
                        'will be' => '✅ First Conditional Passive: if + Present Simple, will be + past participle.',
                        'was' => '❌ Past Simple Passive. Умовне речення вказує на майбутню можливість.',
                        'has been' => '❌ Present Perfect Passive. First Conditional використовує will be.',
                        'is' => '❌ Present Simple Passive. First Conditional використовує will be в головному реченні.',
                    ],
                ],
            ],
            [
                'question' => 'Mr Johnson {a1} this book.',
                'options' => [
                    'a1' => ['is translated', 'translated by', 'translated', 'was translated'],
                ],
                'answers' => ['a1' => 'translated'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'translate (перекладати)'],
                'hints' => [
                    'Підмет "Mr Johnson" виконує дію — це **Active Voice**.',
                    '**Past Simple Active**: Subject + V2 (past simple).',
                    'Людина переклала книгу (активний стан, минулий час).',
                ],
                'explanations' => [
                    'a1' => [
                        'translated' => '✅ Past Simple Active — Mr Johnson виконує дію (переклав книгу).',
                        'is translated' => '❌ Present Simple Passive — підмет "Mr Johnson" є виконавцем, а не об\'єктом.',
                        'translated by' => '❌ "Translated by" потребує попереднього допоміжного дієслова (was translated by).',
                        'was translated' => '❌ Passive Voice — але Mr Johnson є виконавцем дії, а не об\'єктом.',
                    ],
                ],
            ],
            [
                'question' => 'This policeman {a1} that man.',
                'options' => [
                    'a1' => ['was arrested by', 'arrested for', 'arrested', 'will be arrested'],
                ],
                'answers' => ['a1' => 'arrested'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'arrest (заарештовувати)'],
                'hints' => [
                    'Підмет "This policeman" виконує дію — це **Active Voice**.',
                    '**Past Simple Active**: Subject + V2 (past simple).',
                    'Поліцейський заарештував когось (активний стан, минулий час).',
                ],
                'explanations' => [
                    'a1' => [
                        'arrested' => '✅ Past Simple Active — поліцейський виконує дію (заарештував).',
                        'was arrested by' => '❌ Passive Voice — але поліцейський є виконавцем, а не об\'єктом.',
                        'arrested for' => '❌ "Arrested for" означає "заарештований за (злочин)", неправильна конструкція.',
                        'will be arrested' => '❌ Future Passive — поліцейський є виконавцем, а не об\'єктом.',
                    ],
                ],
            ],
            [
                'question' => 'We will {a1} by that teacher.',
                'options' => [
                    'a1' => ['be teached', 'have taught', 'be taught', 'been taught'],
                ],
                'answers' => ['a1' => 'be taught'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'teach (навчати)'],
                'hints' => [
                    '**Future Simple Passive** = will + be + past participle.',
                    '**Taught** — третя форма (past participle) від "teach" (неправильне дієслово).',
                    '"Teached" — неправильна форма (teach-taught-taught).',
                ],
                'explanations' => [
                    'a1' => [
                        'be taught' => '✅ Future Simple Passive (will be taught) — правильна формула: will + be + V3.',
                        'be teached' => '❌ "Teached" — неправильна форма. Teach — неправильне дієслово: teach-taught-taught.',
                        'have taught' => '❌ Perfect Active form. Потрібен Future Simple Passive: will be + V3.',
                        'been taught' => '❌ "Been taught" без "have/has/had" неповна форма. Потрібно "be taught".',
                    ],
                ],
            ],
            [
                'question' => 'Many things {a1} in this house.',
                'options' => [
                    'a1' => ['is said', 'are said', 'they say', 'they are said'],
                ],
                'answers' => ['a1' => 'are said'],
                'level' => 'B1',
                'verb_hints' => ['a1' => 'say (говорити)'],
                'hints' => [
                    '**Present Simple Passive** = am/is/are + past participle.',
                    '"Many things" — множина, тому "are" (не "is").',
                    '**Said** — третя форма від "say".',
                ],
                'explanations' => [
                    'a1' => [
                        'are said' => '✅ Present Simple Passive з "are" для множини "many things".',
                        'is said' => '❌ "Is" для однини, а "many things" — множина. Потрібен "are".',
                        'they say' => '❌ Active Voice з окремим підметом "they". Порушує структуру речення.',
                        'they are said' => '❌ Додатковий підмет "they" порушує структуру. Потрібен тільки "are said".',
                    ],
                ],
            ],
            [
                'question' => 'This mansion {a1} in 1750.',
                'options' => [
                    'a1' => ['is built', 'was built', 'will be built', 'builded'],
                ],
                'answers' => ['a1' => 'was built'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'build (будувати)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle) для завершеної дії в минулому.',
                    'Рік "1750" вказує на минулий час.',
                    '**Built** — третя форма від "build" (неправильне дієслово).',
                ],
                'explanations' => [
                    'a1' => [
                        'was built' => '✅ Past Simple Passive (was built) — дата "1750" вказує на минулий час.',
                        'is built' => '❌ Present Simple Passive. Дата "1750" вказує на минулий час.',
                        'will be built' => '❌ Future Simple Passive. Дата "1750" вказує на минулий час.',
                        'builded' => '❌ Неправильна форма. Build — неправильне дієслово: build-built-built.',
                    ],
                ],
            ],
            [
                'question' => 'They will {a1} this car soon.',
                'options' => [
                    'a1' => ['be bought', 'buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'buy'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'buy (купувати)'],
                'hints' => [
                    'Підмет "They" виконує дію — це **Active Voice**.',
                    '**Future Simple Active** = will + інфінітив (base form).',
                    'Вони куплять (активний стан, не пасивний).',
                ],
                'explanations' => [
                    'a1' => [
                        'buy' => '✅ Future Simple Active (will buy) — "They" виконує дію.',
                        'be bought' => '❌ Future Passive. Але "They" є виконавцем, а не об\'єктом дії.',
                        'buying' => '❌ Після "will" використовується інфінітив без -ing.',
                        'is bought' => '❌ Present Simple Passive. Потрібен Future Simple Active.',
                    ],
                ],
            ],
            [
                'question' => 'This car will {a1} soon.',
                'options' => [
                    'a1' => ['be bought', 'be buying', 'buy', 'is bought'],
                ],
                'answers' => ['a1' => 'be bought'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'buy (купувати)'],
                'hints' => [
                    '**Future Simple Passive** = will + be + past participle.',
                    '"This car" — об\'єкт дії (машину куплять), тому пасивний стан.',
                ],
                'explanations' => [
                    'a1' => [
                        'be bought' => '✅ Future Simple Passive (will be bought) — машина є об\'єктом дії.',
                        'be buying' => '❌ "Be buying" — неправильна форма для пасивного стану.',
                        'buy' => '❌ Active Voice. Машина не може купити (потрібен пасив).',
                        'is bought' => '❌ Present Simple Passive. Потрібен Future Simple Passive.',
                    ],
                ],
            ],
            [
                'question' => 'The jar {a1} by the maid.',
                'options' => [
                    'a1' => ['was broken', 'broke', 'is broken', 'breaks'],
                ],
                'answers' => ['a1' => 'was broken'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'break (ламати)'],
                'hints' => [
                    '**Past Simple Passive** (was/were + past participle).',
                    '"By the maid" вказує на виконавця в пасивному стані.',
                    '**Broken** — третя форма від "break".',
                ],
                'explanations' => [
                    'a1' => [
                        'was broken' => '✅ Past Simple Passive (was broken) — правильна форма з виконавцем "by the maid".',
                        'broke' => '❌ Past Simple Active. Глечик не може ламати — його зламали (пасив).',
                        'is broken' => '❌ Present Simple Passive. Контекст вказує на завершену дію в минулому.',
                        'breaks' => '❌ Present Simple Active. Глечик не може ламати, і час неправильний.',
                    ],
                ],
            ],
            [
                'question' => 'The centre forward {a1} a goal.',
                'options' => [
                    'a1' => ['was scored', 'scores', 'is scored', 'has scored'],
                ],
                'answers' => ['a1' => 'scores'],
                'level' => 'A2',
                'verb_hints' => ['a1' => 'score (забивати)'],
                'hints' => [
                    'Підмет "The centre forward" виконує дію — це **Active Voice**.',
                    '**Present Simple Active** = Subject + V (base form / -s for 3rd person).',
                    'Нападник забиває гол (активний стан).',
                    'Це питання тестує різницю між Active та Passive Voice.',
                ],
                'explanations' => [
                    'a1' => [
                        'scores' => '✅ Present Simple Active з "-s" для третьої особи однини. Нападник виконує дію.',
                        'was scored' => '❌ Past Simple Passive. Нападник виконує дію (Active), а не є її об\'єктом (Passive).',
                        'is scored' => '❌ Present Simple Passive. Нападник забиває (Active Voice), а не "забивається" (Passive Voice).',
                        'has scored' => '❌ Хоча Present Perfect граматично можливий, у цьому контексті тестується Active vs Passive Voice. "Scores" — найкращий вибір для Active Voice.',
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
