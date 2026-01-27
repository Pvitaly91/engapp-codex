<?php

namespace Database\Seeders\V2;

use App\Models\Category;
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
        $categoryId = Category::firstOrCreate(['name' => 'Passive Voice (All Tenses)'])->id;

        $sourceIds = [
            'page1' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/b1-b2/passive-voice-all-tenses/ - Page 1',
            ])->id,
            'page2' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/b1-b2/passive-voice-all-tenses/ - Page 2',
            ])->id,
            'page3' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/b1-b2/passive-voice-all-tenses/ - Page 3',
            ])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice (All Tenses)'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Passive Voice Form Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $focusTagId = Tag::firstOrCreate(
            ['name' => 'Passive Tense/Modal Choice'],
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
                    'verb_hint' => $this->normalizeVerbHint($entry['verb_hints'][$marker] ?? null),
                ];
            }

            [$options, $optionMarkerMap] = $this->prepareOptions($entry);

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
                'option_markers' => $optionMarkerMap,
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function prepareOptions(array $question): array
    {
        $options = $question['options'];

        if (array_is_list($options)) {
            $marker = array_key_first($question['answers']);
            $map = [];
            foreach ($options as $option) {
                $map[$option] = $marker;
            }

            return [$options, $map];
        }

        $flat = [];
        $map = [];
        foreach ($options as $marker => $list) {
            foreach ($list as $option) {
                $flat[] = $option;
                $map[$option] = $marker;
            }
        }

        return [$flat, $map];
    }

    private function normalizeVerbHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        return trim($hint, "() \t\n\r");
    }

    private function questionEntries(): array
    {
        return [
            // Page 1
            [
                'question' => 'The new chemical {a1} when it exploded.',
                'options' => ['had being tested', 'was testing', 'was being tested'],
                'answers' => ['a1' => 'was being tested'],
                'verb_hints' => ['a1' => 'Past Continuous Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Past Continuous Passive = **was/were + being + V3**.',
                    'Описує процес у минулому, який відбувався у момент іншої події.',
                ],
                'explanations' => [
                    'had being tested' => '❌ Неправильна форма: *had being* не використовується.',
                    'was testing' => '❌ Active Past Continuous, а потрібен пасив.',
                    'was being tested' => '✅ Правильно: дія тривала в минулому й виконувалась над об’єктом.',
                ],
            ],
            [
                'question' => 'How could you insult the manager? You {a1} fired.',
                'options' => ['might have', 'might have been', 'might have being'],
                'answers' => ['a1' => 'might have been'],
                'verb_hints' => ['a1' => 'Modal Perfect Passive'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    'Modal Perfect Passive = **might + have + been + V3**.',
                    'Вживаємо для минулої можливості у пасиві.',
                ],
                'explanations' => [
                    'might have' => '❌ Немає форми пасиву без *been*.',
                    'might have been' => '✅ Правильно: можливість у минулому в пасиві.',
                    'might have being' => '❌ Неправильна форма: *being* після *have* не використовується.',
                ],
            ],
            [
                'question' => 'She {a1} of everything.',
                'options' => ['had been inform', 'has being informed', 'has been informed'],
                'answers' => ['a1' => 'has been informed'],
                'verb_hints' => ['a1' => 'Present Perfect Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Present Perfect Passive = **has/have been + V3**.',
                    'Вказує на результат до теперішнього моменту.',
                ],
                'explanations' => [
                    'had been inform' => '❌ Неправильна форма V3: має бути *informed*.',
                    'has being informed' => '❌ Потрібно *been*, не *being*.',
                    'has been informed' => '✅ Правильна форма Present Perfect Passive.',
                ],
            ],
            [
                'question' => 'When I opened the cupboard, I saw that all the cookies {a1}.',
                'options' => ['had been eaten', 'had eaten', 'were eaten'],
                'answers' => ['a1' => 'had been eaten'],
                'verb_hints' => ['a1' => 'Past Perfect Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Past Perfect Passive = **had been + V3**.',
                    'Дія завершилась до іншої події в минулому.',
                ],
                'explanations' => [
                    'had been eaten' => '✅ Правильно: печиво вже було з’їдене до моменту відкриття.',
                    'had eaten' => '❌ Active Past Perfect: потрібен виконавець дії.',
                    'were eaten' => '❌ Past Simple Passive не підкреслює, що дія сталася раніше.',
                ],
            ],
            [
                'question' => 'You {a1} to be so late if you worked for me.',
                'options' => ["wouldn't allow", "wouldn't have allowed", "wouldn't be allowed"],
                'answers' => ['a1' => "wouldn't be allowed"],
                'verb_hints' => ['a1' => 'Modal Passive (permission)'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    'Modal Passive = **would + be + V3** для правил/дозволів.',
                    'Тут описують правило для працівника.',
                ],
                'explanations' => [
                    "wouldn't allow" => '❌ Active форма; підмет має бути виконавцем дії.',
                    "wouldn't have allowed" => '❌ Modal Perfect, але контекст загального правила.',
                    "wouldn't be allowed" => '✅ Пасивна форма правила: *You wouldn’t be allowed*.',
                ],
            ],
            [
                'question' => 'After {a1}, he insulted the referee.',
                'options' => ['being sent off', 'be sending off', 'sending off'],
                'answers' => ['a1' => 'being sent off'],
                'verb_hints' => ['a1' => 'Gerund Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Після *after* використовуємо V-ing.',
                    'Для пасиву: **being + V3**.',
                ],
                'explanations' => [
                    'being sent off' => '✅ Пасивна герундійна форма: *After being sent off*.',
                    'be sending off' => '❌ Неузгоджена форма після *after*.',
                    'sending off' => '❌ Active форма: він не відправляв себе.',
                ],
            ],
            [
                'question' => 'A new restaurant {a1} in our street next week.',
                'options' => ['is going to be opened', 'is going to been opened', 'will being opened'],
                'answers' => ['a1' => 'is going to be opened'],
                'verb_hints' => ['a1' => 'Be going to Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Be going to Passive = **is/are going to be + V3**.',
                    'Вказує на заплановану дію.',
                ],
                'explanations' => [
                    'is going to be opened' => '✅ Правильна пасивна форма з *be going to*.',
                    'is going to been opened' => '❌ Потрібно *be*, не *been* після *to*.',
                    'will being opened' => '❌ Після *will* не використовується *being*.',
                ],
            ],
            [
                'question' => 'The question {a1} at the meeting.',
                'options' => ['is still being discussed', 'is still discussing', 'is still been discussed'],
                'answers' => ['a1' => 'is still being discussed'],
                'verb_hints' => ['a1' => 'Present Continuous Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Present Continuous Passive = **am/is/are + being + V3**.',
                    'Підкреслює процес зараз.',
                ],
                'explanations' => [
                    'is still being discussed' => '✅ Правильна форма для процесу.',
                    'is still discussing' => '❌ Active: потрібно вказати, хто обговорює.',
                    'is still been discussed' => '❌ Має бути *being*, не *been*.',
                ],
            ],
            [
                'question' => 'The car isn’t there anymore. It {a1} stolen.',
                'options' => ['must be', 'must have been', 'must have being'],
                'answers' => ['a1' => 'must have been'],
                'verb_hints' => ['a1' => 'Modal Perfect Passive'],
                'level' => 'B2',
                'source' => 'page1',
                'hints' => [
                    'Modal Perfect Passive = **must + have + been + V3**.',
                    'Логічний висновок про минулу подію.',
                ],
                'explanations' => [
                    'must be' => '❌ Це висновок про теперішній момент, але подія в минулому.',
                    'must have been' => '✅ Правильно: логічний висновок про минуле.',
                    'must have being' => '❌ Неправильна форма з *being*.',
                ],
            ],
            [
                'question' => 'He {a1} about the decision and got angry.',
                'options' => ["wasn't being told", "hadn't told", "hadn't been told"],
                'answers' => ['a1' => "hadn't been told"],
                'verb_hints' => ['a1' => 'Past Perfect Passive'],
                'level' => 'B1',
                'source' => 'page1',
                'hints' => [
                    'Past Perfect Passive = **had been + V3**.',
                    'Підкреслює, що дія відбулася раніше іншої події.',
                ],
                'explanations' => [
                    "wasn't being told" => '❌ Past Continuous Passive не підкреслює завершеність до реакції.',
                    "hadn't told" => '❌ Active форма без об’єкта-одержувача.',
                    "hadn't been told" => '✅ Правильна пасивна форма для дії до минулої події.',
                ],
            ],

            // Page 2
            [
                'question' => 'The report {a1} two days ago.',
                'options' => ['should been finished', 'should finish', 'should have been finished', 'should have being finished'],
                'answers' => ['a1' => 'should have been finished'],
                'verb_hints' => ['a1' => 'Modal Perfect Passive'],
                'level' => 'B2',
                'source' => 'page2',
                'hints' => [
                    'Modal Perfect Passive = **should + have + been + V3**.',
                    'Використовується для критики/очікувань у минулому.',
                ],
                'explanations' => [
                    'should been finished' => '❌ Потрібно *have been* після *should*.',
                    'should finish' => '❌ Active форма; немає пасиву та минулого часу.',
                    'should have been finished' => '✅ Правильна пасивна форма для минулих очікувань.',
                    'should have being finished' => '❌ Неправильна форма з *being*.',
                ],
            ],
            [
                'question' => 'This kind of job used {a1} only by professionals in the past.',
                'options' => ['to being done', 'to be done', 'to do', 'to been done'],
                'answers' => ['a1' => 'to be done'],
                'verb_hints' => ['a1' => 'Used to Passive'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '*Used to* + **be + V3** для пасиву в минулому.',
                    'Підкреслює звичну дію в минулому.',
                ],
                'explanations' => [
                    'to being done' => '❌ Після *to* потрібен інфінітив без *-ing*.',
                    'to be done' => '✅ Правильна пасивна форма.',
                    'to do' => '❌ Active форма; тут підмет не виконавець дії.',
                    'to been done' => '❌ Неправильна форма *been* після *to*.',
                ],
            ],
            [
                'question' => 'They hated {a1} mercenaries, but that’s what they were.',
                'options' => ['being called', 'to being called', 'be called', 'been called'],
                'answers' => ['a1' => 'being called'],
                'verb_hints' => ['a1' => 'Gerund Passive'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Після *hate* використовуємо V-ing.',
                    'Пасив: **being + V3**.',
                ],
                'explanations' => [
                    'being called' => '✅ Правильна пасивна герундійна форма.',
                    'to being called' => '❌ Після *to* не ставимо *being*.',
                    'be called' => '❌ Інфінітив не підходить після *hated*.',
                    'been called' => '❌ Форма V3 без *being* не працює як герундій.',
                ],
            ],
            [
                'question' => 'The new product {a1} by the FDA.',
                'options' => ['is now examined', 'is now examining', 'is now been examined', 'is now being examined'],
                'answers' => ['a1' => 'is now being examined'],
                'verb_hints' => ['a1' => 'Present Continuous Passive'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Present Continuous Passive = **is/are + being + V3**.',
                    'Описує процес, який відбувається зараз.',
                ],
                'explanations' => [
                    'is now examined' => '❌ Present Simple Passive – звучить як регулярна дія, не процес.',
                    'is now examining' => '❌ Active: підмет не є виконавцем.',
                    'is now been examined' => '❌ Потрібно *being*, не *been*.',
                    'is now being examined' => '✅ Правильна форма процесу в пасиві.',
                ],
            ],
            [
                'question' => 'Why {a1} about the meeting yesterday?',
                'options' => ["didn't I tell", "hasn't I being told", "wasn't I told", "wasn't I been told"],
                'answers' => ['a1' => "wasn't I told"],
                'verb_hints' => ['a1' => 'Past Simple Passive Question'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Past Simple Passive Question = **was/were + subject + V3**.',
                    'Питання про минулу подію.',
                ],
                'explanations' => [
                    "didn't I tell" => '❌ Active питання: тут важливо, що мені не сказали.',
                    "hasn't I being told" => '❌ Неправильна форма Present Perfect + being.',
                    "wasn't I told" => '✅ Правильне пасивне питання у Past Simple.',
                    "wasn't I been told" => '❌ Має бути *told* без *been*.',
                ],
            ],
            [
                'question' => 'When we arrived, the car {a1}.',
                'options' => ['had been disappeared', 'had disappeared', 'was being disappeared', 'was disappear'],
                'answers' => ['a1' => 'had disappeared'],
                'verb_hints' => ['a1' => 'Past Perfect (intransitive)'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    '*Disappear* — неперехідне дієслово, пасив не використовується.',
                    'Past Perfect = **had + V3**.',
                ],
                'explanations' => [
                    'had been disappeared' => '❌ Пасив неможливий з *disappear*.',
                    'had disappeared' => '✅ Правильно: дія відбулась до моменту нашого прибуття.',
                    'was being disappeared' => '❌ Неправильна пасивна форма з неперехідним дієсловом.',
                    'was disappear' => '❌ Неправильна граматика: потрібна форма V3.',
                ],
            ],
            [
                'question' => '{a1} soon? Do you think you’ll get the job?',
                'options' => ['Will you been interviewed', 'Will you interview', 'Will you being interviewed', 'Will you be interviewed'],
                'answers' => ['a1' => 'Will you be interviewed'],
                'verb_hints' => ['a1' => 'Future Passive Question'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Future Passive Question = **Will + subject + be + V3**.',
                    'Питання про майбутню подію.',
                ],
                'explanations' => [
                    'Will you been interviewed' => '❌ Після *will* потрібне *be*.',
                    'Will you interview' => '❌ Active форма: не ви проводите співбесіду.',
                    'Will you being interviewed' => '❌ Неправильна форма з *being* після *will*.',
                    'Will you be interviewed' => '✅ Правильне пасивне питання.',
                ],
            ],
            [
                'question' => 'Lots of free gifts {a1} away among the participants.',
                'options' => ['will been given', 'are give', 'are being given', 'are giving'],
                'answers' => ['a1' => 'are being given'],
                'verb_hints' => ['a1' => 'Present Continuous Passive'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Present Continuous Passive = **are + being + V3**.',
                    'Описує процес роздачі зараз.',
                ],
                'explanations' => [
                    'will been given' => '❌ Потрібно *be* після *will*.',
                    'are give' => '❌ Неправильна форма V1.',
                    'are being given' => '✅ Правильна пасивна форма процесу.',
                    'are giving' => '❌ Active: потрібен виконавець дії.',
                ],
            ],
            [
                'question' => 'The school {a1} accommodation for the students.',
                'options' => ["doesn't normally provide", "isn't normally been provided", "isn't normally being provided", "isn't normally provide"],
                'answers' => ['a1' => "doesn't normally provide"],
                'verb_hints' => ['a1' => 'Present Simple (active)'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Підмет *the school* є виконавцем дії, тому потрібен актив.',
                    'Present Simple = **do/does + not + V1**.',
                ],
                'explanations' => [
                    "doesn't normally provide" => '✅ Активна форма правильна: школа надає/не надає.',
                    "isn't normally been provided" => '❌ Неправильна пасивна форма (*been*).',
                    "isn't normally being provided" => '❌ Пасив у процесі не підходить для звичного факту.',
                    "isn't normally provide" => '❌ Потрібно допоміжне *doesn’t*.',
                ],
            ],
            [
                'question' => 'She {a1} of the accident yet.',
                'options' => ["hasn't informed", "hasn't been informed", "hasn't being informed", "wasn't been informed"],
                'answers' => ['a1' => "hasn't been informed"],
                'verb_hints' => ['a1' => 'Present Perfect Passive'],
                'level' => 'B1',
                'source' => 'page2',
                'hints' => [
                    'Present Perfect Passive = **has/have been + V3**.',
                    'Заперечення з *yet* для теперішнього результату.',
                ],
                'explanations' => [
                    "hasn't informed" => '❌ Active форма: потрібен виконавець дії.',
                    "hasn't been informed" => '✅ Правильна пасивна форма з *yet*.',
                    "hasn't being informed" => '❌ Потрібно *been*, не *being*.',
                    "wasn't been informed" => '❌ Неправильна форма та час.',
                ],
            ],

            // Page 3
            [
                'question' => 'I don’t like {a1} what to do.',
                'options' => ['being told', 'to be told', 'telling', 'to tell'],
                'answers' => ['a1' => 'being told'],
                'verb_hints' => ['a1' => 'tell'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Після *don’t like* використовуємо V-ing.',
                    'Пасив: **being + V3**.',
                ],
                'explanations' => [
                    'being told' => '✅ Правильна пасивна герундійна форма.',
                    'to be told' => '❌ Інфінітив не підходить після *don’t like*.',
                    'telling' => '❌ Active форма: не ви говорите іншим.',
                    'to tell' => '❌ Інфінітив і активна форма.',
                ],
            ],
            [
                'question' => 'The hotel was closed because it {a1}.',
                'options' => ['was renovated', 'was being renovated', 'had renovated', 'was renovating'],
                'answers' => ['a1' => 'was being renovated'],
                'verb_hints' => ['a1' => 'renovate'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Past Continuous Passive = **was/were + being + V3**.',
                    'Підкреслює процес ремонту в минулому.',
                ],
                'explanations' => [
                    'was renovated' => '❌ Past Simple Passive – більше про результат, не процес.',
                    'was being renovated' => '✅ Правильно: процес ремонту в той момент.',
                    'had renovated' => '❌ Active Past Perfect: потрібен виконавець.',
                    'was renovating' => '❌ Active Past Continuous.',
                ],
            ],
            [
                'question' => 'I went to the doctor yesterday and I {a1} some medicine for my cough.',
                'options' => ['was prescribed', 'were prescribed', 'prescribed', 'has been prescribed'],
                'answers' => ['a1' => 'was prescribed'],
                'verb_hints' => ['a1' => 'prescribe'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Past Simple Passive = **was/were + V3**.',
                    'Подія сталася вчора.',
                ],
                'explanations' => [
                    'was prescribed' => '✅ Правильно: мені виписали ліки.',
                    'were prescribed' => '❌ Підмет *I* → *was*.',
                    'prescribed' => '❌ Active форма без виконавця.',
                    'has been prescribed' => '❌ Present Perfect Passive не підходить для *yesterday*.',
                ],
            ],
            [
                'question' => 'My car {a1} yet, and I need it for tomorrow.',
                'options' => ['has not been fixed', 'is not fixed', 'was not fixed', 'has not fixed'],
                'answers' => ['a1' => 'has not been fixed'],
                'verb_hints' => ['a1' => 'fix'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Present Perfect Passive = **has/have been + V3**.',
                    '*Yet* вказує на незавершену дію до теперішнього моменту.',
                ],
                'explanations' => [
                    'has not been fixed' => '✅ Правильно: результат ще не досягнутий.',
                    'is not fixed' => '❌ Present Simple Passive – звучить як загальний факт.',
                    'was not fixed' => '❌ Past Simple: не підходить до *yet*.',
                    'has not fixed' => '❌ Active форма без об’єкта-одержувача.',
                ],
            ],
            [
                'question' => 'If she hadn’t insulted the police officer, she wouldn’t {a1}.',
                'options' => ['have been arrested', 'be arrested', 'have arrested', 'have been arresting'],
                'answers' => ['a1' => 'have been arrested'],
                'verb_hints' => ['a1' => 'arrest'],
                'level' => 'B2',
                'source' => 'page3',
                'hints' => [
                    'Third Conditional Passive = **would + have + been + V3**.',
                    'Гіпотетична ситуація в минулому.',
                ],
                'explanations' => [
                    'have been arrested' => '✅ Правильно: пасив у третій умові.',
                    'be arrested' => '❌ Infinitive без *have* не для минулого.',
                    'have arrested' => '❌ Active: вона не арештовувала.',
                    'have been arresting' => '❌ Непотрібна форма Continuous.',
                ],
            ],
            [
                'question' => 'She is hoping {a1} president.',
                'options' => ['to be elected', 'to elect', 'being elected', 'be elected'],
                'answers' => ['a1' => 'to be elected'],
                'verb_hints' => ['a1' => 'elect'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Після *hope* вживаємо інфінітив.',
                    'Пасив: **to be + V3**.',
                ],
                'explanations' => [
                    'to be elected' => '✅ Правильна пасивна форма інфінітива.',
                    'to elect' => '❌ Active: вона не обирає.',
                    'being elected' => '❌ Герундій не підходить після *hoping*.',
                    'be elected' => '❌ Потрібно *to* після *hoping*.',
                ],
            ],
            [
                'question' => 'Last night’s fire might {a1} by lightning.',
                'options' => ['have been caused', 'be caused', 'have caused', 'been caused'],
                'answers' => ['a1' => 'have been caused'],
                'verb_hints' => ['a1' => 'cause'],
                'level' => 'B2',
                'source' => 'page3',
                'hints' => [
                    'Modal Perfect Passive = **might + have + been + V3**.',
                    'Припущення щодо минулої причини.',
                ],
                'explanations' => [
                    'have been caused' => '✅ Правильно: пасивна форма для припущення про минуле.',
                    'be caused' => '❌ Потрібно *have* для минулого.',
                    'have caused' => '❌ Active: блискавка могла спричинити, але підмет тут *fire*.',
                    'been caused' => '❌ Немає допоміжного *have/might have*.',
                ],
            ],
            [
                'question' => 'Our house {a1} so we are staying at my parents’.',
                'options' => ['is being painted', 'is painted', 'has been painted', 'was being painted'],
                'answers' => ['a1' => 'is being painted'],
                'verb_hints' => ['a1' => 'paint'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Present Continuous Passive = **is/are + being + V3**.',
                    'Показує дію, що триває зараз.',
                ],
                'explanations' => [
                    'is being painted' => '✅ Правильно: ремонт відбувається зараз.',
                    'is painted' => '❌ Present Simple Passive – загальний факт, не процес.',
                    'has been painted' => '❌ Present Perfect Passive – дія вже завершена.',
                    'was being painted' => '❌ Past Continuous Passive – дія в минулому.',
                ],
            ],
            [
                'question' => 'The museum {a1} by millions of people next year.',
                'options' => ['will be visited', 'is visited', 'will have visited', 'will be visiting'],
                'answers' => ['a1' => 'will be visited'],
                'verb_hints' => ['a1' => 'visit'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Future Simple Passive = **will + be + V3**.',
                    'Прогноз на майбутнє.',
                ],
                'explanations' => [
                    'will be visited' => '✅ Правильна пасивна форма майбутнього.',
                    'is visited' => '❌ Present Simple Passive не відповідає *next year*.',
                    'will have visited' => '❌ Active Future Perfect.',
                    'will be visiting' => '❌ Active Future Continuous.',
                ],
            ],
            [
                'question' => 'The suspect {a1} by the police at the moment.',
                'options' => ['is being questioned', 'is questioned', 'has been questioned', 'was being questioned'],
                'answers' => ['a1' => 'is being questioned'],
                'verb_hints' => ['a1' => 'question'],
                'level' => 'B1',
                'source' => 'page3',
                'hints' => [
                    'Present Continuous Passive = **is/are + being + V3**.',
                    'Позначає процес зараз.',
                ],
                'explanations' => [
                    'is being questioned' => '✅ Правильно: процес відбувається зараз.',
                    'is questioned' => '❌ Present Simple Passive – загальний факт.',
                    'has been questioned' => '❌ Present Perfect Passive – дія вже завершена.',
                    'was being questioned' => '❌ Past Continuous Passive – минулий процес.',
                ],
            ],
        ];
    }
}
