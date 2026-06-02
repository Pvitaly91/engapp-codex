<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class PassiveVoiceAllTensesV2Seeder extends QuestionSeeder
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
            'page1' => Source::firstOrCreate(['name' => 'test-english.com: Passive Voice All Tenses (Page 1)'])->id,
            'page2' => Source::firstOrCreate(['name' => 'test-english.com: Passive Voice All Tenses (Page 2)'])->id,
            'page3' => Source::firstOrCreate(['name' => 'test-english.com: Passive Voice All Tenses (Page 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice All Tenses'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Passive Construction'],
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
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
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
            // Page 1
            [
                'question' => 'The new chemical {a1} when it exploded.',
                'options' => [
                    'a1' => ['was being tested', 'had being tested', 'was testing'],
                ],
                'answers' => ['a1' => 'was being tested'],
                'level' => 'B1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'test'],
                'hints' => [
                    '**Past Continuous Passive** (was/were being + past participle) вживається для дії, що тривала в момент у минулому.',
                    'Слово "when" вказує на момент, коли щось відбувалося.',
                ],
                'explanations' => [
                    'a1' => [
                        'was being tested' => '✅ Past Continuous Passive (was being tested) правильно описує дію, що тривала в певний момент у минулому. Формула: was/were + being + past participle.',
                        'had being tested' => '❌ Неправильна форма. "Had being" не існує в англійській мові. Past Perfect Passive має форму: had been + past participle.',
                        'was testing' => '❌ Це активний стан (Active Voice), а потрібен пасивний (Passive Voice). Хімічну речовину тестували, а не вона тестувала щось.',
                    ],
                ],
            ],
            [
                'question' => 'How could you insult the manager? You {a1} fired.',
                'options' => [
                    'a1' => ['might have been', 'might have', 'might have being'],
                ],
                'answers' => ['a1' => 'might have been'],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'fire (звільнити)'],
                'hints' => [
                    '**Modal Perfect Passive** (modal + have been + past participle) виражає можливість або припущення про минулу дію в пасивному стані.',
                    'Після модального дієслова (might, could, should) в пасиві: have been + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'might have been' => '✅ Modal Perfect Passive (might have been fired) правильно виражає можливість звільнення в минулому. Формула: modal + have been + past participle.',
                        'might have' => '❌ Не вистачає "been" для утворення пасивного стану. "Might have" потребує past participle для активного стану.',
                        'might have being' => '❌ Неправильна форма. "Being" не використовується після "have". Правильно: might have been.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} of everything.',
                'options' => [
                    'a1' => ['has been informed', 'had been inform', 'has being informed'],
                ],
                'answers' => ['a1' => 'has been informed'],
                'level' => 'B1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Present Perfect Passive** (has/have been + past participle) виражає дію, завершену до теперішнього моменту.',
                    'Формула: has/have + been + past participle.',
                ],
                'explanations' => [
                    'a1' => [
                        'has been informed' => '✅ Present Perfect Passive (has been informed) правильно показує, що її проінформували про все. Формула: has/have + been + past participle.',
                        'had been inform' => '❌ Неправильна форма. Past Perfect потребує past participle (informed, not inform). Також "had" вказує на минулий час, а тут потрібен Present Perfect.',
                        'has being informed' => '❌ Неправильна форма. "Being" не використовується після "has/have". Правильно: has been.',
                    ],
                ],
            ],
            [
                'question' => 'When I opened the cupboard, I saw that all the cookies {a1}.',
                'options' => [
                    'a1' => ['had been eaten', 'had eaten', 'were eaten'],
                ],
                'answers' => ['a1' => 'had been eaten'],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'eat'],
                'hints' => [
                    '**Past Perfect Passive** (had been + past participle) виражає дію, що сталася до іншої дії в минулому.',
                    'Печиво з\'їли ДО того, як відкрили шафу.',
                ],
                'explanations' => [
                    'a1' => [
                        'had been eaten' => '✅ Past Perfect Passive (had been eaten) правильно показує, що печиво з\'їли до моменту відкриття шафи. Формула: had + been + past participle.',
                        'had eaten' => '❌ Це активний стан. Печиво не може їсти, його з\'їли (пасивний стан).',
                        'were eaten' => '❌ Past Simple Passive не показує, що одна дія відбулася раніше за іншу. Потрібен Past Perfect Passive.',
                    ],
                ],
            ],
            [
                'question' => "You {a1} to be so late if you worked for me.",
                'options' => [
                    'a1' => ["wouldn't be allowed", "wouldn't allow", "wouldn't have allowed"],
                ],
                'answers' => ['a1' => "wouldn't be allowed"],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'allow'],
                'hints' => [
                    '**Conditional Passive** (would/wouldn\'t be + past participle) в умовних реченнях.',
                    'Тебе не дозволили б (пасив), а не ти не дозволив би (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        "wouldn't be allowed" => '✅ Second Conditional Passive (wouldn\'t be allowed) правильно виражає гіпотетичну ситуацію. Формула: would/wouldn\'t + be + past participle.',
                        "wouldn't allow" => '❌ Це активний стан. Тут потрібен пасивний стан: тебе не дозволили б (пасив).',
                        "wouldn't have allowed" => '❌ Це Third Conditional про минуле. Тут Second Conditional про гіпотетичну теперішню/майбутню ситуацію.',
                    ],
                ],
            ],
            [
                'question' => 'After {a1}, he insulted the referee.',
                'options' => [
                    'a1' => ['being sent off', 'be sending off', 'sending off'],
                ],
                'answers' => ['a1' => 'being sent off'],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'send off (видалити з поля)'],
                'hints' => [
                    '**Gerund Passive** (being + past participle) після прийменників (after, before, without).',
                    'Його вислали з поля (пасив), а не він когось висилав (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'being sent off' => '✅ Gerund Passive (being sent off) правильно використовується після прийменника "after". Формула: being + past participle.',
                        'be sending off' => '❌ Неправильна форма. Після прийменника використовується герундій (being), а не інфінітив (be).',
                        'sending off' => '❌ Це активний стан. Його вислали (пасив), а не він когось висилав (актив).',
                    ],
                ],
            ],
            [
                'question' => 'A new restaurant {a1} in our street next week.',
                'options' => [
                    'a1' => ['is going to be opened', 'is going to been opened', 'will being opened'],
                ],
                'answers' => ['a1' => 'is going to be opened'],
                'level' => 'B1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'open'],
                'hints' => [
                    '**Future Passive (going to)** = is/are going to be + past participle.',
                    'Виражає заплановану майбутню дію в пасивному стані.',
                ],
                'explanations' => [
                    'a1' => [
                        'is going to be opened' => '✅ Future Passive з "going to" (is going to be opened) правильно виражає заплановану дію. Формула: is/are going to + be + past participle.',
                        'is going to been opened' => '❌ Неправильна форма. Після "going to" використовується інфінітив "be", а не "been".',
                        'will being opened' => '❌ Неправильна форма. Після "will" використовується "be", а не "being". Правильно: will be opened.',
                    ],
                ],
            ],
            [
                'question' => 'The question {a1} at the meeting.',
                'options' => [
                    'a1' => ['is still being discussed', 'is still discussing', 'is still been discussed'],
                ],
                'answers' => ['a1' => 'is still being discussed'],
                'level' => 'B1',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'discuss'],
                'hints' => [
                    '**Present Continuous Passive** (is/are being + past participle) для дії, що відбувається зараз.',
                    'Питання обговорюють (пасив), а не питання обговорює щось (актив).',
                ],
                'explanations' => [
                    'a1' => [
                        'is still being discussed' => '✅ Present Continuous Passive (is being discussed) правильно показує, що питання все ще обговорюється. Формула: is/are + being + past participle.',
                        'is still discussing' => '❌ Це активний стан. Питання не може обговорювати, його обговорюють (пасив).',
                        'is still been discussed' => '❌ Неправильна форма. Після "is" для Continuous потрібен "being", а не "been".',
                    ],
                ],
            ],
            [
                'question' => "The car isn't there anymore. It {a1} stolen.",
                'options' => [
                    'a1' => ['must have been', 'must be', 'must have being'],
                ],
                'answers' => ['a1' => 'must have been'],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'steal'],
                'hints' => [
                    '**Modal Perfect Passive** (must have been + past participle) виражає впевнене припущення про минулу дію.',
                    '"Must have been" = напевно, мабуть (про минуле).',
                ],
                'explanations' => [
                    'a1' => [
                        'must have been' => '✅ Modal Perfect Passive (must have been stolen) виражає впевнене припущення про те, що сталося в минулому. Формула: modal + have been + past participle.',
                        'must be' => '❌ "Must be" стосується теперішнього часу, а машина вже зникла (минулий час). Потрібен Perfect.',
                        'must have being' => '❌ Неправильна форма. "Being" не використовується після "have". Правильно: must have been.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} about the decision and got angry.',
                'options' => [
                    'a1' => ["hadn't been told", "wasn't being told", "hadn't told"],
                ],
                'answers' => ['a1' => "hadn't been told"],
                'level' => 'B2',
                'source' => 'page1',
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Past Perfect Passive** (hadn\'t been + past participle) показує, що йому не сказали до того, як він розізлився.',
                    'Одна дія (не сказали) відбулася раніше за іншу (розізлився).',
                ],
                'explanations' => [
                    'a1' => [
                        "hadn't been told" => '✅ Past Perfect Passive (hadn\'t been told) правильно показує, що йому не розповіли до моменту, коли він розізлився. Формула: hadn\'t + been + past participle.',
                        "wasn't being told" => '❌ Past Continuous Passive показує процес у певний момент, а не завершену дію до іншої дії в минулому.',
                        "hadn't told" => '❌ Це активний стан. Він не розповів (актив), а йому не розповіли (пасив).',
                    ],
                ],
            ],

            // Page 2
            [
                'question' => 'The report {a1} two days ago.',
                'options' => [
                    'a1' => ['should have been finished', 'should been finished', 'should finish', 'should have being finished'],
                ],
                'answers' => ['a1' => 'should have been finished'],
                'level' => 'B2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'finish'],
                'hints' => [
                    '**Modal Perfect Passive** (should have been + past participle) виражає, що щось мало б бути зроблено в минулому.',
                    '"Should have been finished" = мав би бути завершений.',
                ],
                'explanations' => [
                    'a1' => [
                        'should have been finished' => '✅ Modal Perfect Passive (should have been finished) правильно виражає, що звіт мав би бути завершений раніше. Формула: modal + have been + past participle.',
                        'should been finished' => '❌ Не вистачає "have". Правильно: should have been finished.',
                        'should finish' => '❌ Це активний стан і теперішній час. Потрібен пасив і Perfect для минулої дії.',
                        'should have being finished' => '❌ Неправильна форма. "Being" не використовується після "have". Правильно: should have been.',
                    ],
                ],
            ],
            [
                'question' => 'This kind of job used {a1} only by professionals in the past.',
                'options' => [
                    'a1' => ['to be done', 'to being done', 'to do', 'to been done'],
                ],
                'answers' => ['a1' => 'to be done'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'do'],
                'hints' => [
                    '**Infinitive Passive** після "used to" = used to be + past participle.',
                    '"Used to be done" = раніше виконувалося (в минулому).',
                ],
                'explanations' => [
                    'a1' => [
                        'to be done' => '✅ Infinitive Passive (to be done) правильно використовується після "used". Формула: to be + past participle.',
                        'to being done' => '❌ Неправильна форма. Після "to" використовується інфінітив "be", а не герундій "being".',
                        'to do' => '❌ Це активний стан. Роботу робили (пасив), а не робота робила щось (актив).',
                        'to been done' => '❌ Неправильна форма. "Been" не може стояти після "to". Правильно: to be done.',
                    ],
                ],
            ],
            [
                'question' => 'They hated {a1} mercenaries, but that\'s what they were.',
                'options' => [
                    'a1' => ['being called', 'to being called', 'be called', 'been called'],
                ],
                'answers' => ['a1' => 'being called'],
                'level' => 'B2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'call'],
                'hints' => [
                    '**Gerund Passive** (being + past participle) після дієслів типу hate, like, love.',
                    'Після "hate" використовується герундій (being called).',
                ],
                'explanations' => [
                    'a1' => [
                        'being called' => '✅ Gerund Passive (being called) правильно використовується після дієслова "hated". Формула: being + past participle.',
                        'to being called' => '❌ Неправильна форма. Після "to" не може стояти "being". Може бути "to be called" або "being called" (без to).',
                        'be called' => '❌ Після "hated" потрібен герундій (being called), а не інфінітив (be called).',
                        'been called' => '❌ "Been" без допоміжного дієслова не може утворити правильну форму. Потрібен герундій "being called".',
                    ],
                ],
            ],
            [
                'question' => 'The new product {a1} by the FDA.',
                'options' => [
                    'a1' => ['is now being examined', 'is now examined', 'is now examining', 'is now been examined'],
                ],
                'answers' => ['a1' => 'is now being examined'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'examine'],
                'hints' => [
                    '**Present Continuous Passive** (is/are being + past participle) для дії, що відбувається зараз.',
                    '"Is being examined" = перевіряється зараз.',
                ],
                'explanations' => [
                    'a1' => [
                        'is now being examined' => '✅ Present Continuous Passive (is being examined) правильно показує, що продукт перевіряється зараз. Формула: is/are + being + past participle.',
                        'is now examined' => '❌ Present Simple Passive не показує, що дія відбувається зараз. Потрібен Continuous.',
                        'is now examining' => '❌ Це активний стан. Продукт не може перевіряти, його перевіряють (пасив).',
                        'is now been examined' => '❌ Неправильна форма. Після "is" для Continuous потрібен "being", а не "been".',
                    ],
                ],
            ],
            [
                'question' => 'Why {a1} about the meeting yesterday?',
                'options' => [
                    'a1' => ["wasn't I told", "didn't I tell", "hasn't I being told", "wasn't I been told"],
                ],
                'answers' => ['a1' => "wasn't I told"],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Past Simple Passive** (was/wasn\'t told) для питань про минулу дію.',
                    '"Wasn\'t I told" = мені не сказали?',
                ],
                'explanations' => [
                    'a1' => [
                        "wasn't I told" => '✅ Past Simple Passive (wasn\'t I told) правильно утворює питання про минулу дію. Формула: was/were + past participle.',
                        "didn't I tell" => '❌ Це активний стан. Мені не сказали (пасив), а не я не сказав (актив).',
                        "hasn't I being told" => '❌ Неправильні час і форма. "Yesterday" вимагає Past Simple, а не Present Perfect. Також "being" неправильно.',
                        "wasn't I been told" => '❌ Неправильна форма. "Been" не використовується з "was" для Simple Passive. Правильно: wasn\'t I told.',
                    ],
                ],
            ],
            [
                'question' => 'When we arrived, the car {a1}.',
                'options' => [
                    'a1' => ['had disappeared', 'had been disappeared', 'was being disappeared', 'was disappear'],
                ],
                'answers' => ['a1' => 'had disappeared'],
                'level' => 'B2',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'disappear'],
                'hints' => [
                    '**Увага!** "Disappear" — неперехідне дієслово, не має пасивного стану.',
                    'Використовуємо Past Perfect Active (had disappeared) для дії, що сталася до іншої дії в минулому.',
                ],
                'explanations' => [
                    'a1' => [
                        'had disappeared' => '✅ Past Perfect Active (had disappeared) правильний, бо "disappear" — неперехідне дієслово, не має пасиву. Машина зникла сама.',
                        'had been disappeared' => '❌ "Disappear" не має пасивного стану, бо це неперехідне дієслово. Машина зникла сама, її не зникали.',
                        'was being disappeared' => '❌ Неправильна форма. "Disappear" не має пасиву.',
                        'was disappear' => '❌ Неправильна граматична форма. Після "was" потрібен past participle або -ing форма.',
                    ],
                ],
            ],
            [
                'question' => '{a1} soon? Do you think you\'ll get the job?',
                'options' => [
                    'a1' => ['Will you be interviewed', 'Will you been interviewed', 'Will you interview', 'Will you being interviewed'],
                ],
                'answers' => ['a1' => 'Will you be interviewed'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'interview'],
                'hints' => [
                    '**Future Simple Passive** (will be + past participle) для питань про майбутню дію.',
                    '"Will you be interviewed" = тебе будуть інтерв\'ювати?',
                ],
                'explanations' => [
                    'a1' => [
                        'Will you be interviewed' => '✅ Future Simple Passive (will be interviewed) правильно утворює питання про майбутню дію. Формула: will + be + past participle.',
                        'Will you been interviewed' => '❌ Неправильна форма. Після "will" використовується інфінітив "be", а не "been".',
                        'Will you interview' => '❌ Це активний стан. Тебе будуть інтерв\'ювати (пасив), а не ти будеш інтерв\'ювати (актив).',
                        'Will you being interviewed' => '❌ Неправильна форма. Після "will" використовується "be", а не "being".',
                    ],
                ],
            ],
            [
                'question' => 'Lots of free gifts {a1} away among the participants.',
                'options' => [
                    'a1' => ['are being given', 'will been given', 'are give', 'are giving'],
                ],
                'answers' => ['a1' => 'are being given'],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'give'],
                'hints' => [
                    '**Present Continuous Passive** (are being + past participle) для дії, що відбувається зараз.',
                    '"Are being given" = роздаються зараз.',
                ],
                'explanations' => [
                    'a1' => [
                        'are being given' => '✅ Present Continuous Passive (are being given) правильно показує, що подарунки роздаються зараз. Формула: is/are + being + past participle.',
                        'will been given' => '❌ Неправильна форма. Після "will" використовується "be", а не "been". Також не підходить за часом.',
                        'are give' => '❌ Неправильна граматична форма. Після "are" потрібен "being given" для пасиву.',
                        'are giving' => '❌ Це активний стан. Подарунки роздають (пасив), а не подарунки роздають щось (актив).',
                    ],
                ],
            ],
            [
                'question' => 'The school {a1} accommodation for the students.',
                'options' => [
                    'a1' => ["doesn't normally provide", "isn't normally been provided", "isn't normally being provided", "isn't normally provide"],
                ],
                'answers' => ['a1' => "doesn't normally provide"],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'provide'],
                'hints' => [
                    '**Увага!** Тут потрібен активний стан, а не пасивний.',
                    'Школа (суб\'єкт) надає (активна дія) житло (об\'єкт).',
                ],
                'explanations' => [
                    'a1' => [
                        "doesn't normally provide" => '✅ Active Voice (doesn\'t provide) правильний, бо школа (суб\'єкт) сама надає житло. Це активна дія, не пасивна.',
                        "isn't normally been provided" => '❌ Неправильна форма і неправильний стан. Тут потрібен активний стан, не пасивний.',
                        "isn't normally being provided" => '❌ Неправильний стан. Школа надає (активно), а не школу надають (пасивно).',
                        "isn't normally provide" => '❌ Неправильна граматична форма. Після "isn\'t" потрібен past participle або -ing форма.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} of the accident yet.',
                'options' => [
                    'a1' => ["hasn't been informed", "hasn't informed", "hasn't being informed", "wasn't been informed"],
                ],
                'answers' => ['a1' => "hasn't been informed"],
                'level' => 'B1',
                'source' => 'page2',
                'verb_hints' => ['a1' => 'inform'],
                'hints' => [
                    '**Present Perfect Passive** (hasn\'t been + past participle) з "yet" для дії, що ще не відбулася.',
                    '"Hasn\'t been informed" = її ще не проінформували.',
                ],
                'explanations' => [
                    'a1' => [
                        "hasn't been informed" => '✅ Present Perfect Passive (hasn\'t been informed) правильно виражає дію, яка ще не відбулася до теперішнього моменту. Формула: has/have + been + past participle.',
                        "hasn't informed" => '❌ Це активний стан. Її не проінформували (пасив), а не вона не проінформувала (актив).',
                        "hasn't being informed" => '❌ Неправильна форма. "Being" не використовується після "has/have". Правильно: hasn\'t been.',
                        "wasn't been informed" => '❌ Неправильна форма. "Wasn\'t been" не існує. Правильно: wasn\'t informed або hasn\'t been informed.',
                    ],
                ],
            ],

            // Page 3
            [
                'question' => "I don't like {a1} what to do.",
                'options' => [
                    'a1' => ['being told', 'to be told', 'been told', 'be told'],
                ],
                'answers' => ['a1' => 'being told'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'tell'],
                'hints' => [
                    '**Gerund Passive** (being + past participle) після дієслів типу like, love, hate.',
                    'Після "like/don\'t like" можна використати герундій (being told) або інфінітив (to be told).',
                ],
                'explanations' => [
                    'a1' => [
                        'being told' => '✅ Gerund Passive (being told) правильно використовується після "like/don\'t like". Формула: being + past participle.',
                        'to be told' => '⚠️ Також можливий варіант. Після "like" можна використати інфінітив "to be told" або герундій "being told".',
                        'been told' => '❌ "Been" без допоміжного дієслова не може утворити правильну форму після "like".',
                        'be told' => '❌ Після "like" потрібен герундій (being told) або інфінітив з "to" (to be told), а не чистий інфінітив.',
                    ],
                ],
            ],
            [
                'question' => 'The hotel was closed because it {a1}.',
                'options' => [
                    'a1' => ['was being renovated', 'was renovated', 'had been renovated', 'is being renovated'],
                ],
                'answers' => ['a1' => 'was being renovated'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'renovate'],
                'hints' => [
                    '**Past Continuous Passive** (was being + past participle) для дії, що тривала в певний період у минулому.',
                    'Готель ремонтували в той період, коли він був закритий.',
                ],
                'explanations' => [
                    'a1' => [
                        'was being renovated' => '✅ Past Continuous Passive (was being renovated) правильно показує, що ремонт тривав у момент закриття. Формула: was/were + being + past participle.',
                        'was renovated' => '❌ Past Simple Passive показує завершену дію, а не процес, що тривав.',
                        'had been renovated' => '❌ Past Perfect показує завершену дію до іншої дії. Але готель закрили ПІД ЧАС ремонту, а не після.',
                        'is being renovated' => '❌ Неправильний час. "Was closed" (минулий час) вимагає минулого часу в другій частині речення.',
                    ],
                ],
            ],
            [
                'question' => 'I went to the doctor yesterday and I {a1} some medicine for my cough.',
                'options' => [
                    'a1' => ['was prescribed', 'have been prescribed', 'am prescribed', 'had been prescribed'],
                ],
                'answers' => ['a1' => 'was prescribed'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'prescribe'],
                'hints' => [
                    '**Past Simple Passive** (was prescribed) для дії, що відбулася в конкретний момент у минулому.',
                    '"Yesterday" вказує на конкретний час у минулому — Past Simple.',
                ],
                'explanations' => [
                    'a1' => [
                        'was prescribed' => '✅ Past Simple Passive (was prescribed) правильний для конкретної дії вчора. Формула: was/were + past participle.',
                        'have been prescribed' => '❌ Present Perfect не вживається з "yesterday". Past Simple потрібен для конкретного часу в минулому.',
                        'am prescribed' => '❌ Неправильний час. Present Simple не підходить для дії, що відбулася вчора.',
                        'had been prescribed' => '❌ Past Perfect показує дію до іншої дії в минулому. Тут немає такої послідовності.',
                    ],
                ],
            ],
            [
                'question' => 'My car {a1} yet, and I need it for tomorrow.',
                'options' => [
                    'a1' => ['has not been fixed', 'was not fixed', 'is not being fixed', 'will not be fixed'],
                ],
                'answers' => ['a1' => 'has not been fixed'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'fix'],
                'hints' => [
                    '**Present Perfect Passive** (hasn\'t been + past participle) з "yet" для дії, що ще не відбулася.',
                    '"Yet" зазвичай вживається з Present Perfect.',
                ],
                'explanations' => [
                    'a1' => [
                        'has not been fixed' => '✅ Present Perfect Passive (has not been fixed) правильний з "yet" для дії, що ще не відбулася. Формула: has/have + been + past participle.',
                        'was not fixed' => '❌ Past Simple не вживається з "yet". Present Perfect потрібен.',
                        'is not being fixed' => '❌ Present Continuous показує процес зараз, а "yet" вказує на незавершену дію до теперішнього моменту.',
                        'will not be fixed' => '❌ Future Simple не вживається з "yet". "Yet" вимагає Present Perfect.',
                    ],
                ],
            ],
            [
                'question' => "If she hadn't insulted the police officer, she wouldn't {a1}.",
                'options' => [
                    'a1' => ['have been arrested', 'be arrested', 'been arrested', 'have arrested'],
                ],
                'answers' => ['a1' => 'have been arrested'],
                'level' => 'B2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'arrest'],
                'hints' => [
                    '**Third Conditional Passive** у головній частині: wouldn\'t have been + past participle.',
                    'Виражає нереальну ситуацію в минулому (її заарештували, бо образила).',
                ],
                'explanations' => [
                    'a1' => [
                        'have been arrested' => '✅ Third Conditional Passive (wouldn\'t have been arrested) правильно виражає нереальну ситуацію в минулому. Формула: would/wouldn\'t + have been + past participle.',
                        'be arrested' => '❌ Second Conditional про теперішнє/майбутнє. Тут Third Conditional про минуле (hadn\'t insulted).',
                        'been arrested' => '❌ Не вистачає "have". Правильно: wouldn\'t have been arrested.',
                        'have arrested' => '❌ Це активний стан. Її заарештували (пасив), а не вона когось заарештувала (актив).',
                    ],
                ],
            ],
            [
                'question' => 'She is hoping {a1} president.',
                'options' => [
                    'a1' => ['to be elected', 'being elected', 'to elect', 'been elected'],
                ],
                'answers' => ['a1' => 'to be elected'],
                'level' => 'B2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'elect'],
                'hints' => [
                    '**Infinitive Passive** (to be + past participle) після дієслова "hope".',
                    '"Hope to be elected" = сподівається бути обраною.',
                ],
                'explanations' => [
                    'a1' => [
                        'to be elected' => '✅ Infinitive Passive (to be elected) правильно використовується після "hoping". Формула: to be + past participle.',
                        'being elected' => '❌ Після "hope" використовується інфінітив (to be elected), а не герундій (being elected).',
                        'to elect' => '❌ Це активний стан. Її оберуть (пасив), а не вона обере (актив).',
                        'been elected' => '❌ "Been" не може стояти після "hope" без "to have". Правильно: to be elected.',
                    ],
                ],
            ],
            [
                'question' => "Last night's fire might {a1} by lightning.",
                'options' => [
                    'a1' => ['have been caused', 'be caused', 'been caused', 'have caused'],
                ],
                'answers' => ['a1' => 'have been caused'],
                'level' => 'B2',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'cause'],
                'hints' => [
                    '**Modal Perfect Passive** (might have been + past participle) для припущення про минулу подію.',
                    '"Might have been caused" = можливо, була спричинена.',
                ],
                'explanations' => [
                    'a1' => [
                        'have been caused' => '✅ Modal Perfect Passive (might have been caused) правильно виражає припущення про причину минулої події. Формула: modal + have been + past participle.',
                        'be caused' => '❌ "Might be" стосується теперішнього/майбутнього. "Last night" вимагає Perfect (might have been).',
                        'been caused' => '❌ Не вистачає "have". Правильно: might have been caused.',
                        'have caused' => '❌ Це активний стан. Пожежа не спричинила блискавку, її спричинила блискавка (пасив).',
                    ],
                ],
            ],
            [
                'question' => 'Our house {a1} so we are staying at my parents\'.',
                'options' => [
                    'a1' => ['is being painted', 'is painted', 'has been painted', 'was being painted'],
                ],
                'answers' => ['a1' => 'is being painted'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'paint'],
                'hints' => [
                    '**Present Continuous Passive** (is being + past participle) для дії, що відбувається зараз.',
                    'Будинок фарбують зараз, тому ми у батьків.',
                ],
                'explanations' => [
                    'a1' => [
                        'is being painted' => '✅ Present Continuous Passive (is being painted) правильно показує, що будинок фарбують зараз. Формула: is/are + being + past participle.',
                        'is painted' => '❌ Present Simple не показує, що дія відбувається зараз. Потрібен Continuous.',
                        'has been painted' => '❌ Present Perfect показує завершену дію. Якщо б пофарбували, ми б вже повернулися додому.',
                        'was being painted' => '❌ Неправильний час. "Are staying" (теперішній час) вимагає теперішнього часу: is being painted.',
                    ],
                ],
            ],
            [
                'question' => 'The museum {a1} by millions of people next year.',
                'options' => [
                    'a1' => ['will be visited', 'is visited', 'is being visited', 'was visited'],
                ],
                'answers' => ['a1' => 'will be visited'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'visit'],
                'hints' => [
                    '**Future Simple Passive** (will be + past participle) для дії в майбутньому.',
                    '"Next year" вказує на майбутній час.',
                ],
                'explanations' => [
                    'a1' => [
                        'will be visited' => '✅ Future Simple Passive (will be visited) правильний для дії в наступному році. Формула: will + be + past participle.',
                        'is visited' => '❌ Present Simple не підходить для дії в майбутньому. "Next year" вимагає Future.',
                        'is being visited' => '❌ Present Continuous стосується теперішнього моменту, а не майбутнього року.',
                        'was visited' => '❌ Past Simple стосується минулого, а "next year" — майбутнє.',
                    ],
                ],
            ],
            [
                'question' => 'The suspect {a1} by the police at the moment.',
                'options' => [
                    'a1' => ['is being questioned', 'is questioned', 'has been questioned', 'was questioned'],
                ],
                'answers' => ['a1' => 'is being questioned'],
                'level' => 'B1',
                'source' => 'page3',
                'verb_hints' => ['a1' => 'question (допитувати)'],
                'hints' => [
                    '**Present Continuous Passive** (is being + past participle) для дії, що відбувається зараз.',
                    '"At the moment" вказує на дію, що відбувається прямо зараз.',
                ],
                'explanations' => [
                    'a1' => [
                        'is being questioned' => '✅ Present Continuous Passive (is being questioned) правильний для дії, що відбувається прямо зараз. Формула: is/are + being + past participle.',
                        'is questioned' => '❌ Present Simple не показує, що дія відбувається саме зараз. "At the moment" вимагає Continuous.',
                        'has been questioned' => '❌ Present Perfect показує завершену дію, а "at the moment" вказує на дію, що триває зараз.',
                        'was questioned' => '❌ Past Simple стосується минулого, а "at the moment" — теперішнього.',
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
