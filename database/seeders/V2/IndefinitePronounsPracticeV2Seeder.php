<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class IndefinitePronounsPracticeV2Seeder extends QuestionSeeder
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
        $categoryId = Category::firstOrCreate(['name' => 'Pronouns'])->id;
        $sourceIds = [
            'page1' => Source::firstOrCreate(['name' => 'Custom: Indefinite Pronouns Practice V2 (Page 1)'])->id,
            'page2' => Source::firstOrCreate(['name' => 'Custom: Indefinite Pronouns Practice V2 (Page 2)'])->id,
            'page3' => Source::firstOrCreate(['name' => 'Custom: Indefinite Pronouns Practice V2 (Page 3)'])->id,
        ];
        $defaultSourceId = $sourceIds['page1'];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Indefinite Pronouns Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Something / Anything / Nothing Exercises'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Indefinite Pronoun Compounds'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 2,
                'source_id' => $sourceIds[$entry['source']] ?? $defaultSourceId,
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
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
                'question' => "I can't find my keys {a1}.",
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => "Заперечення *can't find* вимагає any-слова: шукаємо **anywhere**.",
                ],
                'explanations' => [
                    'a1' => [
                        'somewhere' => "❌ *Somewhere* доречне у ствердженнях або запрошеннях, а не після заперечення.",
                        'anywhere' => "✅ **Anywhere** використовуємо після *can't* і *don't* для позначення «деінде/ніде».", 
                        'nowhere' => "❌ *Nowhere* уже несе заперечення і разом з *can't* створює подвійну негативну конструкцію.",
                    ],
                ],
            ],
            [
                'question' => "\"What did you have to drink?\" \"I didn't drink {a1}; only water.\"",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Після "didn\'t" обираємо any-слово для заперечення.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => "❌ *Something* звучить як ствердження, а не як заперечення.",
                        'anything' => '✅ **Anything** логічно завершує речення після "didn\'t".',
                        'nothing' => "❌ *Nothing* зробило б конструкцію подвійнозаперечною.",
                    ],
                ],
            ],
            [
                'question' => '{a1} was at the party; all our friends and family were there.',
                'options' => [
                    'a1' => ['Everybody', 'Anybody', 'Nobody'],
                ],
                'answers' => ['a1' => 'Everybody'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Фраза «all our friends and family» підказує тотальність → **Everybody**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Everybody' => '✅ **Everybody** означає всіх без винятку, що й описано в реченні.',
                        'Anybody' => '❌ *Anybody* частіше вживається у питаннях/запереченнях і не підкреслює «всі».',
                        'Nobody' => '❌ *Nobody* суперечить твердженню, що вечірка була заповнена людьми.',
                    ],
                ],
            ],
            [
                'question' => "\"Did you see {a1} interesting at the party?\" \"{a2}. Only boring people.\"",
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                    'a2' => ['somebody', 'nobody', 'anybody'],
                ],
                'answers' => ['a1' => 'anybody', 'a2' => 'nobody'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Загальне питання (Did you...?) потребує any-слова → **anybody**.',
                    'a2' => 'Відповідь описує повну відсутність цікавих людей → **nobody**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somebody' => '❌ *Somebody* звучить як ствердження, а не як нейтральне питання.',
                        'anybody' => '✅ **Anybody** стандартно використовується у загальних питаннях.',
                        'nobody' => '❌ *Nobody* є запереченням і не ставиться в питальній частині.',
                    ],
                    'a2' => [
                        'somebody' => '❌ Це б означало, що хтось цікавий був, але фраза каже протилежне.',
                        'nobody' => '✅ **Nobody** підкреслює відсутність цікавих людей.',
                        'anybody' => '❌ *Anybody* звучить невизначено й не передає «жодного».',
                    ],
                ],
            ],
            [
                'question' => '{a1} robbed a bank yesterday. They took a lot of money.',
                'options' => [
                    'a1' => ['Somebody', 'Anybody', 'Nobody'],
                ],
                'answers' => ['a1' => 'Somebody'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Подія точно сталася, але особа невідома → використовуємо some-слово.',
                ],
                'explanations' => [
                    'a1' => [
                        'Somebody' => '✅ **Somebody** підходить для ствердження про невідомого злочинця.',
                        'Anybody' => '❌ *Anybody* частіше стоїть у питаннях або запереченнях.',
                        'Nobody' => '❌ *Nobody* суперечить факту пограбування.',
                    ],
                ],
            ],
            [
                'question' => 'The police think the robber is hiding {a1} in the neighbourhood.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'somewhere'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Це ствердження з припущенням → потрібне **somewhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somewhere' => '✅ **Somewhere** вживаємо, коли впевнені, що місце існує, але не знаємо яке.',
                        'anywhere' => '❌ *Anywhere* використовують у запереченнях або для «будь-де».',
                        'nowhere' => '❌ *Nowhere* означає, що ніхто не ховається, що суперечить припущенню поліції.',
                    ],
                ],
            ],
            [
                'question' => "\"Have you eaten {a1}?\" \"{a2}: I'm very hungry.\"",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                    'a2' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything', 'a2' => 'nothing'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Так/ні питання про досвід → ставимо **anything**.',
                    'a2' => 'Він голодний, тож нічого не їв → **nothing**.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => '❌ *Something* підходить для пропозицій, а не для нейтрального питання.',
                        'anything' => '✅ **Anything** – стандарт для загальних питань.',
                        'nothing' => '❌ *Nothing* не вживається у питальній частині.',
                    ],
                    'a2' => [
                        'something' => '❌ Якщо б він щось їв, то не був би «дуже голодний».',
                        'anything' => '❌ *Anything* означає «що-небудь», а потрібно підкреслити відсутність їжі.',
                        'nothing' => '✅ **Nothing** показує, що їжі не було зовсім.',
                    ],
                ],
            ],
            [
                'question' => 'Can I stay here tonight? I have {a1} to go.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'nowhere'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Коли немає жодного місця, кажемо **nowhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somewhere' => '❌ *Somewhere* означає, що місце є, але невідоме.',
                        'anywhere' => '❌ *Anywhere* означає «де завгодно», але герою нема куди йти.',
                        'nowhere' => '✅ **Nowhere** чітко передає повну відсутність місця.',
                    ],
                ],
            ],
            [
                'question' => 'I think {a1} bad has happened, because there are police officers {a2}.',
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                    'a2' => ['everywhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'something', 'a2' => 'everywhere'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Є підозра на подію → уживаємо **something**.',
                    'a2' => 'Офіцери скрізь навколо → потрібне **everywhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => '✅ **Something** позначає невідому, але реальну подію.',
                        'anything' => '❌ *Anything* вказує на байдужість до вибору, а не на підозру.',
                        'nothing' => '❌ *Nothing* суперечить тому, що відбувається.',
                    ],
                    'a2' => [
                        'everywhere' => '✅ **Everywhere** показує, що поліція всюди.',
                        'anywhere' => '❌ *Anywhere* звучить як «де-небудь», без наголосу на масштаб.',
                        'nowhere' => '❌ *Nowhere* означало б, що поліції немає, а це неправда.',
                    ],
                ],
            ],
            [
                'question' => '{a1} is big in New York; the streets, the buildings, the cars, even the hamburgers.',
                'options' => [
                    'a1' => ['Something', 'Everything', 'Anything'],
                ],
                'answers' => ['a1' => 'Everything'],
                'level' => 'A2',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Перелік «вулиці, будинки, машини…» = всі речі → **Everything**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Something' => '❌ *Something* означає лише одну річ, а не весь список.',
                        'Everything' => '✅ **Everything** охоплює всі названі предмети.',
                        'Anything' => '❌ *Anything* означає «будь-що», але не «все підряд».',
                    ],
                ],
            ],
            [
                'question' => '{a1} used my computer yesterday. I need to know who did it.',
                'options' => [
                    'a1' => ['Somebody', 'Nobody', 'Anybody'],
                ],
                'answers' => ['a1' => 'Somebody'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Факт використання був → потрібне позитивне some-слово.',
                ],
                'explanations' => [
                    'a1' => [
                        'Somebody' => '✅ **Somebody** передає невідомого, але реального користувача.',
                        'Nobody' => '❌ *Nobody* заперечує факт.',
                        'Anybody' => '❌ *Anybody* доречне у питаннях/запереченнях.',
                    ],
                ],
            ],
            [
                'question' => "It happened very quickly and I couldn't see {a1}.",
                'options' => [
                    'a1' => ['anything', 'something', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Після "couldn\'t" ставимо **anything**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anything' => '✅ **Anything** відповідає заперечному дієслову.',
                        'something' => '❌ *Something* звучить як твердження.',
                        'nothing' => '❌ *Nothing* створило б подвійну негативну форму.',
                    ],
                ],
            ],
            [
                'question' => 'Have you talked to {a1} about your problem?',
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                ],
                'answers' => ['a1' => 'anybody'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Нейтральне питання вимагає **anybody**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somebody' => '❌ *Somebody* звучить як очікування позитивної відповіді.',
                        'anybody' => '✅ **Anybody** підходить для загального питання.',
                        'nobody' => '❌ *Nobody* є запереченням і не ставиться у питальній формі.',
                    ],
                ],
            ],
            [
                'question' => "I'm bored. I don't have {a1} to do.",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Після "don\'t have" ставимо any-слово.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => '❌ *Something* суперечить запереченню.',
                        'anything' => '✅ **Anything** логічно завершує заперечну фразу.',
                        'nothing' => '❌ *Nothing* створило б подвійну негативну конструкцію.',
                    ],
                ],
            ],
            [
                'question' => 'He lost his house and now he has {a1} to live.',
                'options' => [
                    'a1' => ['somewhere', 'anywhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'nowhere'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Коли житла немає зовсім, обираємо **nowhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somewhere' => '❌ *Somewhere* означало б наявність місця.',
                        'anywhere' => '❌ *Anywhere* = «будь-де», а не «ніде».',
                        'nowhere' => '✅ **Nowhere** підкреслює повну відсутність житла.',
                    ],
                ],
            ],
            [
                'question' => "She doesn't have {a1} in her life. She's very lonely.",
                'options' => [
                    'a1' => ['somebody', 'anybody', 'nobody'],
                ],
                'answers' => ['a1' => 'anybody'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Після заперечення "doesn\'t have" використовуємо **anybody**.',
                ],
                'explanations' => [
                    'a1' => [
                        'somebody' => '❌ *Somebody* означає, що хтось є, що суперечить запереченню.',
                        'anybody' => '✅ **Anybody** правильно поєднується з "doesn\'t have" для вираження самотності.',
                        'nobody' => '❌ *Nobody* створює подвійне заперечення з "doesn\'t have".',
                    ],
                ],
            ],
            [
                'question' => 'Would you like {a1} to eat?',
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'something'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'У ввічливих пропозиціях Would you like…? вживаємо **something**.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => '✅ **Something** звучить тепло в пропозиції.',
                        'anything' => '❌ *Anything* робить пропозицію занадто байдужою.',
                        'nothing' => '❌ *Nothing* заперечує пропозицію.',
                    ],
                ],
            ],
            [
                'question' => '"Do you know {a1} in Dublin?" "Yes, I know a few people."',
                'options' => [
                    'a1' => ['someone', 'anyone', 'no one'],
                ],
                'answers' => ['a1' => 'anyone'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Загальне питання → **anyone**.',
                ],
                'explanations' => [
                    'a1' => [
                        'someone' => '❌ *Someone* звучить як відповідь, а не запитання.',
                        'anyone' => '✅ **Anyone** стандарт для нейтральних питань.',
                        'no one' => '❌ *No one* суперечить позитивній відповіді.',
                    ],
                ],
            ],
            [
                'question' => 'Sarah told {a1} that she broke up with you. Now we all know.',
                'options' => [
                    'a1' => ['anyone', 'no one', 'everyone'],
                ],
                'answers' => ['a1' => 'everyone'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Фраза "Now we all know" означає, що вона розповіла всім → **everyone**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anyone' => '❌ *Anyone* звучить невизначено, а контекст говорить про «всіх».',
                        'no one' => '❌ *No one* суперечить твердженню "Now we all know".',
                        'everyone' => '✅ **Everyone** логічно пояснює, чому всі тепер знають.',
                    ],
                ],
            ],
            [
                'question' => "I'm going to bed. There's {a1} interesting on TV.",
                'options' => [
                    'a1' => ['something', 'anything', 'nothing'],
                ],
                'answers' => ['a1' => 'nothing'],
                'level' => 'A2',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Форма "There is ..." + негативний зміст → **nothing**.',
                ],
                'explanations' => [
                    'a1' => [
                        'something' => '❌ *Something* суперечить ідеї «йду спати».',
                        'anything' => '❌ *Anything* не дає відчуття повної відсутності інтересу.',
                        'nothing' => '✅ **Nothing** означає, що на ТВ немає цікавого.',
                    ],
                ],
            ],
            [
                'question' => "We have looked for Mike but we can't find him {a1}.",
                'options' => [
                    'a1' => ['anywhere', 'nowhere', 'somewhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Заперечення *can\'t find* → **anywhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anywhere' => '✅ **Anywhere** поєднується з *can\'t* і показує відсутність результату.',
                        'nowhere' => '❌ *Nowhere* уже містить заперечення, що звучало б дивно.',
                        'somewhere' => '❌ *Somewhere* означає, що знайшли місце.',
                    ],
                ],
            ],
            [
                'question' => '{a1} called you this morning, but I do not know who.',
                'options' => [
                    'a1' => ['Someone', 'Anybody', 'No one'],
                ],
                'answers' => ['a1' => 'Someone'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Телефонний дзвінок був → використовуємо **Someone**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Someone' => '✅ **Someone** передає, що дзвонив невідомий.',
                        'Anybody' => '❌ *Anybody* використовується у питаннях/запереченнях.',
                        'No one' => '❌ *No one* суперечить факту дзвінка.',
                    ],
                ],
            ],
            [
                'question' => "I didn't go {a1} yesterday. I stayed home all day.",
                'options' => [
                    'a1' => ['anywhere', 'somewhere', 'nowhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Після "didn\'t" ставимо **anywhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anywhere' => '✅ **Anywhere** – найприродніший вибір у запереченні.',
                        'somewhere' => '❌ *Somewhere* означало б, що він кудись ходив.',
                        'nowhere' => '❌ *Nowhere* повторює заперечення і звучить важкувато.',
                    ],
                ],
            ],
            [
                'question' => "I don't know {a1} in the class yet, but I know most of them.",
                'options' => [
                    'a1' => ['anyone', 'someone', 'everyone'],
                ],
                'answers' => ['a1' => 'anyone'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Заперечення *don\'t know* + загальне посилання → **anyone**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anyone' => '✅ **Anyone** показує, що не вистачає знайомств із рештою людей.',
                        'someone' => '❌ *Someone* означало б, що невідома лише одна людина.',
                        'everyone' => '❌ *Everyone* говорить про всіх, а тут потрібна група «ще незнайомих».',
                    ],
                ],
            ],
            [
                'question' => "I'm sorry but I can't help you. I don't know {a1} about Napoleon.",
                'options' => [
                    'a1' => ['anything', 'something', 'nothing'],
                ],
                'answers' => ['a1' => 'anything'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Після "don\'t know" – **anything**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anything' => '✅ **Anything** підтримує заперечення.',
                        'something' => '❌ *Something* означало б, що знання є.',
                        'nothing' => '❌ *Nothing* зробило б речення подвійно негативним.',
                    ],
                ],
            ],
            [
                'question' => "He's behaving very strangely. {a1} is wrong with him, but I don't know what.",
                'options' => [
                    'a1' => ['Something', 'Anything', 'Nothing'],
                ],
                'answers' => ['a1' => 'Something'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Є очевидна проблема, хоч і невідома → **Something**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Something' => '✅ **Something** вказує на конкретну, хоч і не названу проблему.',
                        'Anything' => '❌ *Anything* звучить як байдужість, а не як факт.',
                        'Nothing' => '❌ *Nothing* суперечить дивній поведінці.',
                    ],
                ],
            ],
            [
                'question' => '{a1} was there when I arrived. I was the only person there.',
                'options' => [
                    'a1' => ['Nobody', 'Anybody', 'Everyone'],
                ],
                'answers' => ['a1' => 'Nobody'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Я був сам → **Nobody**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Nobody' => '✅ **Nobody** означає повну відсутність людей.',
                        'Anybody' => '❌ *Anybody* не дає чіткого відчуття, що нікого не було.',
                        'Everyone' => '❌ *Everyone* суперечить твердженню.',
                    ],
                ],
            ],
            [
                'question' => "Have you seen my wallet? I can't find it {a1}.",
                'options' => [
                    'a1' => ['anywhere', 'nowhere', 'everywhere'],
                ],
                'answers' => ['a1' => 'anywhere'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Заперечення *can\'t find* → **anywhere**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anywhere' => '✅ **Anywhere** природно звучить у запереченні.',
                        'nowhere' => '❌ *Nowhere* дублює заперечення.',
                        'everywhere' => '❌ *Everywhere* означає «усюди», але краще використовувати його, коли щось знаходиш.',
                    ],
                ],
            ],
            [
                'question' => "We lost, so there is {a1} to celebrate today. Let's go home.",
                'options' => [
                    'a1' => ['nothing', 'anything', 'everything'],
                ],
                'answers' => ['a1' => 'nothing'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Поразка = нуль причин для свята → **nothing**.',
                ],
                'explanations' => [
                    'a1' => [
                        'nothing' => '✅ **Nothing** підкреслює відсутність приводу.',
                        'anything' => '❌ *Anything* звучало б як можливість святкувати.',
                        'everything' => '❌ *Everything* означає протилежне змісту.',
                    ],
                ],
            ],
            [
                'question' => 'The police thought they were hiding {a1} in the house, but they did not find {a2} hiding there.',
                'options' => [
                    'a1' => ['anyone', 'someone', 'no one'],
                    'a2' => ['anyone', 'someone', 'no one'],
                ],
                'answers' => ['a1' => 'someone', 'a2' => 'anyone'],
                'level' => 'A2',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Поліція думала, що хтось ховається → потрібне **someone**.',
                    'a2' => 'Після заперечення "did not find" використовуємо **anyone**.',
                ],
                'explanations' => [
                    'a1' => [
                        'anyone' => '❌ *Anyone* частіше для питань, а тут ствердження про підозру.',
                        'someone' => '✅ **Someone** підходить для ствердження, що поліція думала про конкретну людину.',
                        'no one' => '❌ *No one* суперечить припущенню поліції.',
                    ],
                    'a2' => [
                        'anyone' => '✅ **Anyone** правильно використовується після заперечення "did not find".',
                        'someone' => '❌ *Someone* означало б, що когось знайшли.',
                        'no one' => '❌ *No one* створює подвійне заперечення з "did not find".',
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
