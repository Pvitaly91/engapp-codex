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
            'page1' => Source::firstOrCreate(['name' => 'Custom: Passive Voice All Tenses V2 (Page 1)'])->id,
            'page2' => Source::firstOrCreate(['name' => 'Custom: Passive Voice All Tenses V2 (Page 2)'])->id,
            'page3' => Source::firstOrCreate(['name' => 'Custom: Passive Voice All Tenses V2 (Page 3)'])->id,
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
            ['name' => 'Passive Constructions'],
            ['category' => 'English Grammar Structure']
        )->id;

        $focusTagId = Tag::firstOrCreate(
            ['name' => 'Active to Passive Transformation'],
            ['category' => 'English Grammar Focus']
        )->id;

        $questions = $this->questionEntries();

        $tagIds = [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $focusTagId,
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
                'verb_hints' => ['a1' => '(test)'],
                'options' => [
                    'a1' => ['was being tested', 'had being tested', 'was testing'],
                ],
                'answers' => ['a1' => 'was being tested'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    '**Past Continuous Passive** = was/were + being + V3.',
                    'Використовується для дії, що відбувалася у момент у минулому.',
                    'Приклад: *The house was being painted when I arrived.*',
                ],
                'explanations' => [
                    'a1' => [
                        'was being tested' => '✅ Past Continuous Passive – дія відбувалася у момент вибуху. Формула: **was/were + being + V3**. Приклад: *The new drug was being tested when the accident happened.*',
                        'had being tested' => '❌ Неправильна форма – немає конструкції "had being". Правильна форма Past Perfect Passive: had been tested.',
                        'was testing' => '❌ Active Voice (Past Continuous) – але хімічна речовина була об\'єктом тестування, не виконувала дію.',
                    ],
                ],
            ],
            [
                'question' => 'How could you insult the manager? You {a1} fired.',
                'verb_hints' => ['a1' => '(might fire)'],
                'options' => [
                    'a1' => ['might have been', 'might have', 'might have being'],
                ],
                'answers' => ['a1' => 'might have been'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Modal + have been + V3** – пасивна форма модального дієслова у минулому.',
                    'Виражає можливість у минулому: might/could/should have been done.',
                    'Приклад: *He might have been injured in the accident.*',
                ],
                'explanations' => [
                    'a1' => [
                        'might have been' => '✅ Модальна пасивна форма минулого часу: **might + have + been + V3** (fired). Приклад: *You might have been punished for that.*',
                        'might have' => '❌ Неповна форма – не вистачає "been" для пасивного стану.',
                        'might have being' => '❌ Неправильна форма – правильно: might have been.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} of everything.',
                'verb_hints' => ['a1' => '(inform)'],
                'options' => [
                    'a1' => ['has been informed', 'had been inform', 'has being informed'],
                ],
                'answers' => ['a1' => 'has been informed'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    '**Present Perfect Passive** = has/have + been + V3.',
                    'Використовується для дії, що завершилася з результатом у теперішньому.',
                    'Приклад: *The report has been completed.*',
                ],
                'explanations' => [
                    'a1' => [
                        'has been informed' => '✅ Present Perfect Passive – дію завершено, є результат зараз. Формула: **has/have + been + V3**. Приклад: *She has been told about the changes.*',
                        'had been inform' => '❌ Неправильна форма – потрібно V3 (informed), а також це Past Perfect, не Present Perfect.',
                        'has being informed' => '❌ Неправильна форма – правильно: has been informed (не being).',
                    ],
                ],
            ],
            [
                'question' => 'When I opened the cupboard, I saw that all the cookies {a1}.',
                'verb_hints' => ['a1' => '(eat)'],
                'options' => [
                    'a1' => ['had been eaten', 'had eaten', 'were eaten'],
                ],
                'answers' => ['a1' => 'had been eaten'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Past Perfect Passive** = had + been + V3.',
                    'Використовується для дії, що завершилася до іншої у минулому.',
                    'Приклад: *The work had been finished before he arrived.*',
                ],
                'explanations' => [
                    'a1' => [
                        'had been eaten' => '✅ Past Perfect Passive – печиво з\'їли до того, як я відкрив шафу. Формула: **had + been + V3**. Приклад: *All the food had been eaten by the time we arrived.*',
                        'had eaten' => '❌ Active Voice (Past Perfect) – але печиво не могло само себе з\'їсти.',
                        'were eaten' => '❌ Past Simple Passive – не показує, що дія була раніше відкриття шафи.',
                    ],
                ],
            ],
            [
                'question' => "You {a1} to be so late if you worked for me.",
                'verb_hints' => ['a1' => '(allow)'],
                'options' => [
                    'a1' => ["wouldn't be allowed", "wouldn't allow", "wouldn't have allowed"],
                ],
                'answers' => ['a1' => "wouldn't be allowed"],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Second Conditional Passive** = would + be + V3.',
                    'Використовується для гіпотетичних ситуацій у теперішньому/майбутньому.',
                    'Приклад: *He would be fired if he did that.*',
                ],
                'explanations' => [
                    'a1' => [
                        "wouldn't be allowed" => '✅ Second Conditional Passive – гіпотетична ситуація. Формула: **would + be + V3**. Приклад: *Students wouldn\'t be allowed to use phones in class.*',
                        "wouldn't allow" => '❌ Active Voice – але "you" є об\'єктом дозволу, не суб\'єктом.',
                        "wouldn't have allowed" => '❌ Third Conditional – але тут йдеться про теперішнє/майбутнє, не минуле.',
                    ],
                ],
            ],
            [
                'question' => 'After {a1}, he insulted the referee.',
                'verb_hints' => ['a1' => '(send off)'],
                'options' => [
                    'a1' => ['being sent off', 'be sending off', 'sending off'],
                ],
                'answers' => ['a1' => 'being sent off'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Gerund Passive** = being + V3.',
                    'Використовується після прийменників (after, before, without).',
                    'Приклад: *After being told the news, she cried.*',
                ],
                'explanations' => [
                    'a1' => [
                        'being sent off' => '✅ Gerund Passive після прийменника "after". Формула: **being + V3**. Приклад: *After being warned, he continued to misbehave.*',
                        'be sending off' => '❌ Неправильна форма – після "after" потрібен герундій (being).',
                        'sending off' => '❌ Active Voice – але гравця відправили, він не відправляв.',
                    ],
                ],
            ],
            [
                'question' => 'A new restaurant {a1} in our street next week.',
                'verb_hints' => ['a1' => '(open)'],
                'options' => [
                    'a1' => ['is going to be opened', 'is going to been opened', 'will being opened'],
                ],
                'answers' => ['a1' => 'is going to be opened'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    '**Future Passive (be going to)** = is/are going to + be + V3.',
                    'Використовується для запланованих дій у майбутньому.',
                    'Приклад: *The building is going to be demolished next month.*',
                ],
                'explanations' => [
                    'a1' => [
                        'is going to be opened' => '✅ Future Passive з "be going to". Формула: **is/are going to + be + V3**. Приклад: *A new store is going to be opened downtown.*',
                        'is going to been opened' => '❌ Неправильна форма – потрібно "be", не "been".',
                        'will being opened' => '❌ Неправильна форма – правильно: will be opened.',
                    ],
                ],
            ],
            [
                'question' => 'The question {a1} at the meeting.',
                'verb_hints' => ['a1' => '(discuss)'],
                'options' => [
                    'a1' => ['is still being discussed', 'is still discussing', 'is still been discussed'],
                ],
                'answers' => ['a1' => 'is still being discussed'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    '**Present Continuous Passive** = is/are + being + V3.',
                    'Використовується для дії, що відбувається зараз.',
                    'Приклад: *The problem is being solved right now.*',
                ],
                'explanations' => [
                    'a1' => [
                        'is still being discussed' => '✅ Present Continuous Passive – дія відбувається зараз. Формула: **is/are + being + V3**. Приклад: *The issue is being discussed at the moment.*',
                        'is still discussing' => '❌ Active Voice – але питання не може обговорювати саме себе.',
                        'is still been discussed' => '❌ Неправильна форма – потрібно "being", не "been".',
                    ],
                ],
            ],
            [
                'question' => "The car isn't there anymore. It {a1} stolen.",
                'verb_hints' => ['a1' => '(steal)'],
                'options' => [
                    'a1' => ['must have been', 'must be', 'must have being'],
                ],
                'answers' => ['a1' => 'must have been'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Modal Perfect Passive** = must/might/could + have + been + V3.',
                    'Виражає припущення про минуле.',
                    'Приклад: *The keys must have been taken by someone.*',
                ],
                'explanations' => [
                    'a1' => [
                        'must have been' => '✅ Modal Perfect Passive – припущення про минуле. Формула: **must + have + been + V3** (stolen). Приклад: *The wallet must have been stolen yesterday.*',
                        'must be' => '❌ Present form – але машини вже немає, крадіжка у минулому.',
                        'must have being' => '❌ Неправильна форма – правильно: must have been.',
                    ],
                ],
            ],
            [
                'question' => 'He {a1} about the decision and got angry.',
                'verb_hints' => ['a1' => '(tell)'],
                'options' => [
                    'a1' => ["hadn't been told", "wasn't being told", "hadn't told"],
                ],
                'answers' => ['a1' => "hadn't been told"],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    '**Past Perfect Passive** = had + been + V3.',
                    'Використовується для дії, що не відбулася до іншої у минулому.',
                    'Приклад: *She hadn\'t been invited to the party.*',
                ],
                'explanations' => [
                    'a1' => [
                        "hadn't been told" => '✅ Past Perfect Passive (negative) – йому не сказали до того, як він дізнався. Формула: **had + been + V3**. Приклад: *He hadn\'t been warned about the risks.*',
                        "wasn't being told" => '❌ Past Continuous Passive – неправильний час для послідовності подій.',
                        "hadn't told" => '❌ Active Voice – але він не розповідав, йому розповіли.',
                    ],
                ],
            ],

            // Page 2
            [
                'question' => 'The report {a1} two days ago.',
                'verb_hints' => ['a1' => '(finish)'],
                'options' => [
                    'a1' => ['should have been finished', 'should been finished', 'should finish', 'should have being finished'],
                ],
                'answers' => ['a1' => 'should have been finished'],
                'level' => 'B2',
                'source' => 'page2',
                'hints' => [
                    '**Should + have been + V3** – критика минулої дії (не зроблено).',
                    'Виражає, що щось мало бути зроблено, але не було.',
                    'Приклад: *The work should have been completed last week.*',
                ],
                'explanations' => [
                    'a1' => [
                        'should have been finished' => '✅ Modal Perfect Passive – щось мало бути зроблено. Формула: **should + have + been + V3**. Приклад: *The project should have been finished by now.*',
                        'should been finished' => '❌ Неповна форма – не вистачає "have".',
                        'should finish' => '❌ Active Voice Present – неправильна форма для минулого.',
                        'should have being finished' => '❌ Неправильна форма – правильно: should have been.',
                    ],
                ],
            ],
            [
                'question' => 'This kind of job used {a1} only by professionals in the past.',
                'verb_hints' => ['a1' => '(do)'],
                'options' => [
                    'a1' => ['to be done', 'to being done', 'to do', 'to been done'],
                ],
                'answers' => ['a1' => 'to be done'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Used to + be + V3** – звичка у минулому (пасивна форма).',
                    'Формула: used to + be + V3.',
                    'Приклад: *This work used to be done manually.*',
                ],
                'explanations' => [
                    'a1' => [
                        'to be done' => '✅ Infinitive Passive після "used". Формула: **to + be + V3**. Приклад: *Letters used to be written by hand.*',
                        'to being done' => '❌ Неправильна форма – потрібно "be", не "being".',
                        'to do' => '❌ Active Voice – але робота не виконувала, її виконували.',
                        'to been done' => '❌ Неправильна форма – правильно: to be done.',
                    ],
                ],
            ],
            [
                'question' => 'They hated {a1} mercenaries, but that\'s what they were.',
                'verb_hints' => ['a1' => '(call)'],
                'options' => [
                    'a1' => ['being called', 'to being called', 'be called', 'been called'],
                ],
                'answers' => ['a1' => 'being called'],
                'level' => 'B2',
                'source' => 'page2',
                'hints' => [
                    '**Hate/like/love + being + V3** – герундій у пасивному стані.',
                    'Після дієслів емоцій часто використовується герундій.',
                    'Приклад: *I don\'t like being criticized.*',
                ],
                'explanations' => [
                    'a1' => [
                        'being called' => '✅ Gerund Passive після "hated". Формула: **being + V3**. Приклад: *She hates being interrupted during meetings.*',
                        'to being called' => '❌ Неправильна форма – після "hate" використовується або герундій, або інфінітив, але не "to being".',
                        'be called' => '❌ Після "hate" потрібен герундій (being called) або інфінітив (to be called).',
                        'been called' => '❌ Неправильна форма герундія – правильно: being called.',
                    ],
                ],
            ],
            [
                'question' => 'The new product {a1} by the FDA.',
                'verb_hints' => ['a1' => '(examine)'],
                'options' => [
                    'a1' => ['is now being examined', 'is now examined', 'is now examining', 'is now been examined'],
                ],
                'answers' => ['a1' => 'is now being examined'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Present Continuous Passive** = is/are + being + V3.',
                    'Дія відбувається зараз або у цей період.',
                    'Приклад: *The case is being investigated by police.*',
                ],
                'explanations' => [
                    'a1' => [
                        'is now being examined' => '✅ Present Continuous Passive – процес відбувається зараз. Формула: **is/are + being + V3**. Приклад: *The application is now being reviewed.*',
                        'is now examined' => '❌ Present Simple Passive – не показує процес.',
                        'is now examining' => '❌ Active Voice – продукт не може сам себе досліджувати.',
                        'is now been examined' => '❌ Неправильна форма – потрібно "being", не "been".',
                    ],
                ],
            ],
            [
                'question' => 'Why {a1} about the meeting yesterday?',
                'verb_hints' => ['a1' => '(tell)'],
                'options' => [
                    'a1' => ["wasn't I told", "didn't I tell", "hasn't I being told", "wasn't I been told"],
                ],
                'answers' => ['a1' => "wasn't I told"],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Past Simple Passive (question)** = was/were + subject + V3?',
                    'Використовується для питань про минулу дію.',
                    'Приклад: *Were you invited to the party?*',
                ],
                'explanations' => [
                    'a1' => [
                        "wasn't I told" => '✅ Past Simple Passive (negative question). Формула: **wasn\'t/weren\'t + subject + V3**. Приклад: *Why wasn\'t I informed about this?*',
                        "didn't I tell" => '❌ Active Voice – але мені не розповідали, а мені мали розповісти.',
                        "hasn't I being told" => '❌ Неправильна форма – і час (Present Perfect замість Past), і "being" замість "been".',
                        "wasn't I been told" => '❌ Неправильна форма – потрібно V3 (told) без "been".',
                    ],
                ],
            ],
            [
                'question' => 'When we arrived, the car {a1}.',
                'verb_hints' => ['a1' => '(disappear)'],
                'options' => [
                    'a1' => ['had disappeared', 'had been disappeared', 'was being disappeared', 'was disappear'],
                ],
                'answers' => ['a1' => 'had disappeared'],
                'level' => 'B2',
                'source' => 'page2',
                'hints' => [
                    '**Дієслово "disappear" зазвичай не має пасивної форми** – воно непереходне.',
                    'Використовується Active Voice у Past Perfect.',
                    'Приклад: *The keys had disappeared by the time I returned.*',
                ],
                'explanations' => [
                    'a1' => [
                        'had disappeared' => '✅ Past Perfect Active – дієслово "disappear" непереходне і не має пасивної форми. Приклад: *The money had disappeared before the police arrived.*',
                        'had been disappeared' => '❌ Дієслово "disappear" не використовується у пасивному стані.',
                        'was being disappeared' => '❌ Неправильна форма – "disappear" не має пасиву.',
                        'was disappear' => '❌ Неправильна форма – потрібна минула форма: disappeared.',
                    ],
                ],
            ],
            [
                'question' => '{a1} soon? Do you think you\'ll get the job?',
                'verb_hints' => ['a1' => '(interview)'],
                'options' => [
                    'a1' => ['Will you be interviewed', 'Will you been interviewed', 'Will you interview', 'Will you being interviewed'],
                ],
                'answers' => ['a1' => 'Will you be interviewed'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Future Simple Passive (question)** = Will + subject + be + V3?',
                    'Використовується для питань про майбутню дію.',
                    'Приклад: *Will the meeting be held tomorrow?*',
                ],
                'explanations' => [
                    'a1' => [
                        'Will you be interviewed' => '✅ Future Simple Passive (question). Формула: **Will + subject + be + V3**. Приклад: *Will you be contacted next week?*',
                        'Will you been interviewed' => '❌ Неправильна форма – потрібно "be", не "been".',
                        'Will you interview' => '❌ Active Voice – але вас буде хтось інтерв\'ювати.',
                        'Will you being interviewed' => '❌ Неправильна форма – правильно: be interviewed.',
                    ],
                ],
            ],
            [
                'question' => 'Lots of free gifts {a1} away among the participants.',
                'verb_hints' => ['a1' => '(give)'],
                'options' => [
                    'a1' => ['are being given', 'will been given', 'are give', 'are giving'],
                ],
                'answers' => ['a1' => 'are being given'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Present Continuous Passive** = is/are + being + V3.',
                    'Дія відбувається зараз або у цей період.',
                    'Приклад: *Prizes are being distributed at the moment.*',
                ],
                'explanations' => [
                    'a1' => [
                        'are being given' => '✅ Present Continuous Passive – подарунки роздають зараз. Формула: **are + being + V3**. Приклад: *Free samples are being given away today.*',
                        'will been given' => '❌ Неправильна форма – правильно: will be given.',
                        'are give' => '❌ Неправильна форма – потрібно V3: given.',
                        'are giving' => '❌ Active Voice – але подарунки не роздають себе самі.',
                    ],
                ],
            ],
            [
                'question' => 'The school {a1} accommodation for the students.',
                'verb_hints' => ['a1' => '(provide)'],
                'options' => [
                    'a1' => ["doesn't normally provide", "isn't normally been provided", "isn't normally being provided", "isn't normally provide"],
                ],
                'answers' => ['a1' => "doesn't normally provide"],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Active Voice** тут правильна – школа надає (активна дія).',
                    'Present Simple для звичок та регулярних дій.',
                    'Приклад: *The university provides housing for students.*',
                ],
                'explanations' => [
                    'a1' => [
                        "doesn't normally provide" => '✅ Active Voice Present Simple – школа виконує дію надання. Приклад: *The company doesn\'t provide free parking.*',
                        "isn't normally been provided" => '❌ Неправильна форма пасиву – і граматично неправильно, і логічно (школа активна).',
                        "isn't normally being provided" => '❌ Passive Voice – але школа надає, а не їй надають.',
                        "isn't normally provide" => '❌ Неправильна форма – потрібно допоміжне дієслово: doesn\'t provide.',
                    ],
                ],
            ],
            [
                'question' => 'She {a1} of the accident yet.',
                'verb_hints' => ['a1' => '(inform)'],
                'options' => [
                    'a1' => ["hasn't been informed", "hasn't informed", "hasn't being informed", "wasn't been informed"],
                ],
                'answers' => ['a1' => "hasn't been informed"],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '**Present Perfect Passive (negative)** = hasn\'t/haven\'t + been + V3.',
                    'Використовується для дії, що не відбулася до цього моменту.',
                    'Приклад: *The decision hasn\'t been made yet.*',
                ],
                'explanations' => [
                    'a1' => [
                        "hasn't been informed" => '✅ Present Perfect Passive (negative). Формула: **hasn\'t/haven\'t + been + V3**. Приклад: *The results haven\'t been announced yet.*',
                        "hasn't informed" => '❌ Active Voice – але їй не повідомили, а не вона не повідомила.',
                        "hasn't being informed" => '❌ Неправильна форма – правильно: hasn\'t been informed.',
                        "wasn't been informed" => '❌ Неправильна форма Past Simple – тут потрібен Present Perfect.',
                    ],
                ],
            ],

            // Page 3
            [
                'question' => "I don't like {a1} what to do.",
                'verb_hints' => ['a1' => '(tell)'],
                'options' => [
                    'a1' => ['being told', 'to be told', 'been told', 'be told'],
                ],
                'answers' => ['a1' => 'being told'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Like/hate/love + being + V3** або **like + to be + V3**.',
                    'Після "like" можна використати герундій або інфінітив.',
                    'Приклад: *I don\'t like being criticized.*',
                ],
                'explanations' => [
                    'a1' => [
                        'being told' => '✅ Gerund Passive після "like". Формула: **being + V3**. Приклад: *She doesn\'t like being interrupted.*',
                        'to be told' => '✅ Також можливо – Infinitive Passive після "like". Приклад: *I don\'t like to be rushed.*',
                        'been told' => '❌ Неправильна форма герундія – правильно: being told.',
                        'be told' => '❌ Після "like" потрібен герундій або інфінітив з "to".',
                    ],
                ],
            ],
            [
                'question' => 'The hotel was closed because it {a1}.',
                'verb_hints' => ['a1' => '(renovate)'],
                'options' => [
                    'a1' => ['was being renovated', 'was renovating', 'had renovated', 'was renovated'],
                ],
                'answers' => ['a1' => 'was being renovated'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Past Continuous Passive** = was/were + being + V3.',
                    'Дія відбувалася у певний період у минулому.',
                    'Приклад: *The road was being repaired last week.*',
                ],
                'explanations' => [
                    'a1' => [
                        'was being renovated' => '✅ Past Continuous Passive – ремонт відбувався у той період. Формула: **was/were + being + V3**. Приклад: *The building was being demolished when I passed by.*',
                        'was renovating' => '❌ Active Voice – готель не міг сам себе ремонтувати.',
                        'had renovated' => '❌ Active Voice Past Perfect – неправильний стан і час.',
                        'was renovated' => '❌ Past Simple Passive – не показує тривалість процесу.',
                    ],
                ],
            ],
            [
                'question' => 'I went to the doctor yesterday and I {a1} some medicine for my cough.',
                'verb_hints' => ['a1' => '(prescribe)'],
                'options' => [
                    'a1' => ['was prescribed', 'prescribed', 'have been prescribed', 'am prescribed'],
                ],
                'answers' => ['a1' => 'was prescribed'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Past Simple Passive** = was/were + V3.',
                    'Використовується для завершеної дії у минулому.',
                    'Приклад: *I was given a prescription yesterday.*',
                ],
                'explanations' => [
                    'a1' => [
                        'was prescribed' => '✅ Past Simple Passive – дія завершилася вчора. Формула: **was/were + V3**. Приклад: *I was given antibiotics last week.*',
                        'prescribed' => '❌ Active Voice – але лікар прописав мені, а не я прописав.',
                        'have been prescribed' => '❌ Present Perfect – але є чіткий маркер "yesterday" (Past Simple).',
                        'am prescribed' => '❌ Present Simple – але дія була вчора.',
                    ],
                ],
            ],
            [
                'question' => 'My car {a1} yet, and I need it for tomorrow.',
                'verb_hints' => ['a1' => '(not fix)'],
                'options' => [
                    'a1' => ['has not been fixed', "hasn't fixed", "wasn't fixed", 'is not fixed'],
                ],
                'answers' => ['a1' => 'has not been fixed'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Present Perfect Passive (negative)** = hasn\'t/haven\'t + been + V3.',
                    'Маркер "yet" вказує на Present Perfect.',
                    'Приклад: *The problem hasn\'t been solved yet.*',
                ],
                'explanations' => [
                    'a1' => [
                        'has not been fixed' => '✅ Present Perfect Passive з "yet". Формула: **hasn\'t/haven\'t + been + V3**. Приклад: *The issue hasn\'t been resolved yet.*',
                        "hasn't fixed" => '❌ Active Voice – але машину ремонтують, а не вона ремонтує.',
                        "wasn't fixed" => '❌ Past Simple – але "yet" потребує Present Perfect.',
                        'is not fixed' => '❌ Present Simple – з "yet" використовується Present Perfect.',
                    ],
                ],
            ],
            [
                'question' => "If she hadn't insulted the police officer, she wouldn't {a1}.",
                'verb_hints' => ['a1' => '(arrest)'],
                'options' => [
                    'a1' => ['have been arrested', 'be arrested', 'have arrested', 'been arrested'],
                ],
                'answers' => ['a1' => 'have been arrested'],
                'level' => 'B2',
                'source' => 'page3',
                'hints' => [
                    '**Third Conditional Passive** = would + have + been + V3.',
                    'Нереальна умова у минулому.',
                    'Приклад: *If he had studied, he would have been accepted.*',
                ],
                'explanations' => [
                    'a1' => [
                        'have been arrested' => '✅ Third Conditional Passive. Формула: **would + have + been + V3**. Приклад: *If I had been there, I would have been chosen.*',
                        'be arrested' => '❌ Second Conditional – але умова у минулому (hadn\'t insulted).',
                        'have arrested' => '❌ Active Voice – але її арештували, а не вона арештувала.',
                        'been arrested' => '❌ Неповна форма – не вистачає "have".',
                    ],
                ],
            ],
            [
                'question' => 'She is hoping {a1} president.',
                'verb_hints' => ['a1' => '(elect)'],
                'options' => [
                    'a1' => ['to be elected', 'being elected', 'to elect', 'be elected'],
                ],
                'answers' => ['a1' => 'to be elected'],
                'level' => 'B2',
                'source' => 'page3',
                'hints' => [
                    '**Hope + to + be + V3** – інфінітив пасивний.',
                    'Після "hope" використовується інфінітив.',
                    'Приклад: *He hopes to be promoted next year.*',
                ],
                'explanations' => [
                    'a1' => [
                        'to be elected' => '✅ Infinitive Passive після "hope". Формула: **to + be + V3**. Приклад: *She hopes to be chosen for the team.*',
                        'being elected' => '❌ Після "hope" використовується інфінітив, не герундій.',
                        'to elect' => '❌ Active Voice – але її обиратимуть, а не вона обиратиме.',
                        'be elected' => '❌ Після "hope" потрібен інфінітив з "to".',
                    ],
                ],
            ],
            [
                'question' => "Last night's fire might {a1} by lightning.",
                'verb_hints' => ['a1' => '(cause)'],
                'options' => [
                    'a1' => ['have been caused', 'be caused', 'have caused', 'been caused'],
                ],
                'answers' => ['a1' => 'have been caused'],
                'level' => 'B2',
                'source' => 'page3',
                'hints' => [
                    '**Might + have + been + V3** – припущення про минуле.',
                    'Пасивна форма модального дієслова у минулому.',
                    'Приклад: *The accident might have been prevented.*',
                ],
                'explanations' => [
                    'a1' => [
                        'have been caused' => '✅ Modal Perfect Passive – припущення про минуле. Формула: **might + have + been + V3**. Приклад: *The damage might have been caused by the storm.*',
                        'be caused' => '❌ Present form – але пожежа була вчора вночі.',
                        'have caused' => '❌ Active Voice – але пожежу спричинила блискавка.',
                        'been caused' => '❌ Неповна форма – не вистачає "have".',
                    ],
                ],
            ],
            [
                'question' => 'Our house {a1} so we are staying at my parents\'.',
                'verb_hints' => ['a1' => '(paint)'],
                'options' => [
                    'a1' => ['is being painted', 'is painted', 'has been painted', 'was being painted'],
                ],
                'answers' => ['a1' => 'is being painted'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Present Continuous Passive** = is/are + being + V3.',
                    'Дія відбувається зараз (у цей період).',
                    'Приклад: *The walls are being painted this week.*',
                ],
                'explanations' => [
                    'a1' => [
                        'is being painted' => '✅ Present Continuous Passive – процес відбувається зараз. Формула: **is/are + being + V3**. Приклад: *The office is being renovated at the moment.*',
                        'is painted' => '❌ Present Simple – не показує процес.',
                        'has been painted' => '❌ Present Perfect – але процес ще не завершений.',
                        'was being painted' => '❌ Past Continuous – але вони зараз перебувають у батьків.',
                    ],
                ],
            ],
            [
                'question' => 'The museum {a1} by millions of people next year.',
                'verb_hints' => ['a1' => '(visit)'],
                'options' => [
                    'a1' => ['will be visited', 'will visit', 'is visited', 'will be visiting'],
                ],
                'answers' => ['a1' => 'will be visited'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Future Simple Passive** = will + be + V3.',
                    'Використовується для майбутньої дії.',
                    'Приклад: *The event will be attended by many people.*',
                ],
                'explanations' => [
                    'a1' => [
                        'will be visited' => '✅ Future Simple Passive – дія у майбутньому. Формула: **will + be + V3**. Приклад: *The exhibition will be seen by thousands.*',
                        'will visit' => '❌ Active Voice – але музей відвідують, а не він відвідує.',
                        'is visited' => '❌ Present Simple – але є маркер "next year".',
                        'will be visiting' => '❌ Future Continuous Active – неправильна форма.',
                    ],
                ],
            ],
            [
                'question' => 'The suspect {a1} by the police at the moment.',
                'verb_hints' => ['a1' => '(question)'],
                'options' => [
                    'a1' => ['is being questioned', 'is questioned', 'is questioning', 'has been questioned'],
                ],
                'answers' => ['a1' => 'is being questioned'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    '**Present Continuous Passive** = is/are + being + V3.',
                    'Маркер "at the moment" вказує на Present Continuous.',
                    'Приклад: *The witness is being interviewed now.*',
                ],
                'explanations' => [
                    'a1' => [
                        'is being questioned' => '✅ Present Continuous Passive з "at the moment". Формула: **is/are + being + V3**. Приклад: *The documents are being examined right now.*',
                        'is questioned' => '❌ Present Simple – не показує процес у даний момент.',
                        'is questioning' => '❌ Active Voice – але підозрюваного допитують, а не він допитує.',
                        'has been questioned' => '❌ Present Perfect – але дія відбувається саме зараз.',
                    ],
                ],
            ],
        ];
    }
}
