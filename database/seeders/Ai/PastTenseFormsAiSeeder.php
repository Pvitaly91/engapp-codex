<?php

namespace Database\Seeders\Ai;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\QuestionSeeder;

class PastTenseFormsAiSeeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'AI Past Tense Forms Test'])->id;

        $sectionSources = [
            'A' => Source::firstOrCreate(['name' => 'AI Past Tense Forms Test — Section A: Past Simple'])->id,
            'B' => Source::firstOrCreate(['name' => 'AI Past Tense Forms Test — Section B: Past Continuous'])->id,
            'C' => Source::firstOrCreate(['name' => 'AI Past Tense Forms Test — Section C: Past Perfect'])->id,
            'D' => Source::firstOrCreate(['name' => 'AI Past Tense Forms Test — Section D: Passive Voice'])->id,
        ];

        $sectionThemeTags = [
            'A' => Tag::firstOrCreate(['name' => 'Past Simple Review'], ['category' => 'Grammar Theme'])->id,
            'B' => Tag::firstOrCreate(['name' => 'Past Continuous Review'], ['category' => 'Grammar Theme'])->id,
            'C' => Tag::firstOrCreate(['name' => 'Past Perfect Review'], ['category' => 'Grammar Theme'])->id,
            'D' => Tag::firstOrCreate(['name' => 'Past Passive Review'], ['category' => 'Grammar Theme'])->id,
        ];

        $tenseTags = [
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses'])->id,
            'Past Continuous' => Tag::firstOrCreate(['name' => 'Past Continuous'], ['category' => 'Tenses'])->id,
            'Past Perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses'])->id,
            'Past Simple Passive' => Tag::firstOrCreate(['name' => 'Past Simple Passive'], ['category' => 'Tenses'])->id,
            'Past Perfect Passive' => Tag::firstOrCreate(['name' => 'Past Perfect Passive'], ['category' => 'Tenses'])->id,
        ];

        $detailTagDefinitions = [
            'past_simple_affirmative' => 'Past Simple Affirmative',
            'past_simple_question' => 'Past Simple Questions',
            'past_simple_negative' => 'Past Simple Negatives',
            'sequence_actions' => 'Sequence of Past Actions',
            'interruption' => 'Interrupted Past Actions',
            'simultaneous_actions' => 'Simultaneous Past Actions',
            'specific_time_reference' => 'Specific Time References',
            'past_continuous_affirmative' => 'Past Continuous Affirmative',
            'past_continuous_question' => 'Past Continuous Questions',
            'past_continuous_negative' => 'Past Continuous Negatives',
            'past_perfect_affirmative' => 'Past Perfect Affirmative',
            'past_perfect_question' => 'Past Perfect Questions',
            'past_perfect_negative' => 'Past Perfect Negatives',
            'past_perfect_inversion' => 'Past Perfect Inversion',
            'completion_before_event' => 'Completed Before Another Event',
            'prior_experience' => 'Past Experiences Before Events',
            'past_passive_affirmative' => 'Past Passive Statements',
            'past_passive_question' => 'Past Passive Questions',
            'past_passive_perfect' => 'Past Perfect Passive Statements',
        ];

        $detailTags = [];
        foreach ($detailTagDefinitions as $key => $name) {
            $detailTags[$key] = Tag::firstOrCreate(['name' => $name], ['category' => 'Grammar Detail'])->id;
        }

        $fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Question Status']);
        $questionFixTag = Tag::firstOrCreate(
            ['name' => 'double negation removed → correct question format'],
            ['category' => 'Seeder Fix Notes']
        );

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $sections = [
            'A' => [
                'questions' => [
                    [
                        'text' => 'As soon as Tom arrived home, he _____ his mother.',
                        'options' => ['called', 'was calling', 'had called', 'has called'],
                        'answer' => 'called',
                        'explanations' => [
                            'called' => '✅ Past Simple. Подія відбулася одразу після іншої.',
                            'was calling' => '❌ Past Continuous. Показує процес, але не послідовність.',
                            'had called' => '❌ Past Perfect. Тут немає «раніше».',
                            'has called' => '❌ Present Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Simple: Subject + V2.  
Приклад: *She smiled as soon as she arrived.*
HINT,
                        'tense' => 'Past Simple',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_affirmative', 'sequence_actions'],
                    ],
                    [
                        'text' => 'I was walking to school when it _____.',
                        'options' => ['rained', 'was raining', 'had rained', 'rains'],
                        'answer' => 'rained',
                        'explanations' => [
                            'rained' => '✅ Past Simple. Коротка дія, яка перервала процес.',
                            'was raining' => '❌ Past Continuous. Це процес, а не момент.',
                            'had rained' => '❌ Past Perfect. Не підходить.',
                            'rains' => '❌ Present Simple.',
                        ],
                        'hint' => <<<'HINT'
Past Simple: Subject + V2.  
Приклад: *The phone rang while I was cooking.*
HINT,
                        'tense' => 'Past Simple',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_affirmative', 'interruption'],
                    ],
                    [
                        'text' => 'They _____ football last weekend.',
                        'options' => ['play', 'played', 'had played', 'were playing'],
                        'answer' => 'played',
                        'explanations' => [
                            'play' => '❌ Present Simple.',
                            'played' => '✅ Past Simple. Завершена дія у минулому.',
                            'had played' => '❌ Past Perfect.',
                            'were playing' => '❌ Past Continuous.',
                        ],
                        'hint' => <<<'HINT'
Past Simple: Subject + V2.  
Приклад: *They played tennis yesterday.*
HINT,
                        'tense' => 'Past Simple',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_affirmative'],
                    ],
                    [
                        'text' => 'When _____ you arrive at the station yesterday?',
                        'options' => ['did', 'do', 'have', 'was'],
                        'answer' => 'did',
                        'explanations' => [
                            'did' => '✅ Past Simple Question. Формула: Did + subject + V1.',
                            'do' => '❌ Present Simple.',
                            'have' => '❌ Present Perfect.',
                            'was' => '❌ be, але не з цим дієсловом.',
                        ],
                        'hint' => <<<'HINT'
Past Simple (Interrogative): Did + subject + V1.  
Приклад: *Did you see her yesterday?*
HINT,
                        'tense' => 'Past Simple (Question)',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_question'],
                    ],
                    [
                        'text' => 'She _____ visit us last summer.',
                        'options' => ['did', 'does', 'did not', 'was'],
                        'answer' => 'did not',
                        'explanations' => [
                            'did' => '❌ Неповна форма.',
                            'does' => '❌ Present Simple.',
                            'did not' => '✅ Past Simple Negative. Subject + did not + V1.',
                            'was' => '❌ be. Не підходить.',
                        ],
                        'hint' => <<<'HINT'
Past Simple (Negative): Subject + did not + V1.  
Приклад: *I did not see him yesterday.*
HINT,
                        'tense' => 'Past Simple (Negative)',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_negative'],
                    ],
                    [
                        'text' => 'He _____ to Paris last year on holiday.',
                        'options' => ['go', 'goes', 'went', 'had gone'],
                        'answer' => 'went',
                        'explanations' => [
                            'go' => '❌ Base form.',
                            'goes' => '❌ Present Simple.',
                            'went' => '✅ Past Simple. Ствердження про минуле.',
                            'had gone' => '❌ Past Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Affirmative: Subject + V2.  
Приклад: *She went to London last year.*
HINT,
                        'tense' => 'Past Simple',
                        'level' => 'A1',
                        'detail_tags' => ['past_simple_affirmative', 'sequence_actions'],
                    ],
                    [
                        'text' => 'Did she _____ the match yesterday?',
                        'options' => ['win', 'won', 'winning', 'has won'],
                        'answer' => 'win',
                        'explanations' => [
                            'win' => '✅ Correct. Past Simple Question: Did + V1.',
                            'won' => '❌ Past form already, but after did потрібен V1.',
                            'winning' => '❌ V-ing форма.',
                            'has won' => '❌ Present Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Simple (Question): Did + subject + V1.  
Приклад: *Did he win the game?*
HINT,
                        'tense' => 'Past Simple (Question)',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_question'],
                    ],
                    [
                        'text' => 'They _____ like the food in that restaurant.',
                        'options' => ['did', 'did not', 'don’t', 'were not'],
                        'answer' => 'did not',
                        'explanations' => [
                            'did' => '❌ Неповна форма.',
                            'did not' => '✅ Past Simple Negative.',
                            'don’t' => '❌ Present Simple.',
                            'were not' => '❌ Past Continuous/Passive.',
                        ],
                        'hint' => <<<'HINT'
Past Simple (Negative): Subject + did not + V1.  
Приклад: *They did not like the film.*
HINT,
                        'tense' => 'Past Simple (Negative)',
                        'level' => 'A2',
                        'detail_tags' => ['past_simple_negative'],
                    ],
                ],
            ],
            'B' => [
                'questions' => [
                    [
                        'text' => 'He hurt his leg while he _____ football.',
                        'options' => ['played', 'was playing', 'had played', 'plays'],
                        'answer' => 'was playing',
                        'explanations' => [
                            'played' => '❌ Past Simple. Не показує одночасності.',
                            'was playing' => '✅ Past Continuous. Дія у процесі, коли сталося інше.',
                            'had played' => '❌ Past Perfect.',
                            'plays' => '❌ Present Simple.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous: Subject + was/were + V-ing.  
Приклад: *He fell while he was running.*
HINT,
                        'tense' => 'Past Continuous',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_affirmative', 'simultaneous_actions'],
                    ],
                    [
                        'text' => '_____ you sleeping when the phone rang?',
                        'options' => ['Were', 'Did', 'Was', 'Are'],
                        'answer' => 'Were',
                        'explanations' => [
                            'Were' => '✅ Past Continuous Question.',
                            'Did' => '❌ Past Simple.',
                            'Was' => '❌ Використовується з he/she/it.',
                            'Are' => '❌ Present Continuous.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous (Interrogative): Was/Were + subject + V-ing.  
Приклад: *Were you working at 8 pm?*
HINT,
                        'tense' => 'Past Continuous (Question)',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_question', 'interruption'],
                    ],
                    [
                        'text' => 'She _____ listening to me yesterday.',
                        'options' => ['was', 'was not', 'did not', 'were not'],
                        'answer' => 'was not',
                        'explanations' => [
                            'was' => '❌ Неповна форма.',
                            'was not' => '✅ Past Continuous Negative.',
                            'did not' => '❌ Past Simple.',
                            'were not' => '❌ Використовується з you/we/they.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous (Negative): Subject + was/were not + V-ing.  
Приклад: *He was not listening to me.*
HINT,
                        'tense' => 'Past Continuous (Negative)',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_negative'],
                    ],
                    [
                        'text' => 'At 7 pm yesterday, we _____ dinner.',
                        'options' => ['had', 'were having', 'had had', 'was having'],
                        'answer' => 'were having',
                        'explanations' => [
                            'had' => '❌ Past Simple.',
                            'were having' => '✅ Past Continuous. Процес у момент часу.',
                            'had had' => '❌ Past Perfect.',
                            'was having' => '❌ Узгодження з множиною неправильне.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous: Subject + was/were + V-ing.  
Приклад: *We were having lunch at noon.*
HINT,
                        'tense' => 'Past Continuous',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_affirmative', 'specific_time_reference'],
                    ],
                    [
                        'text' => 'While I _____ TV, my brother was cooking.',
                        'options' => ['watched', 'was watching', 'had watched', 'watch'],
                        'answer' => 'was watching',
                        'explanations' => [
                            'watched' => '❌ Past Simple.',
                            'was watching' => '✅ Past Continuous. Дві дії паралельно.',
                            'had watched' => '❌ Past Perfect.',
                            'watch' => '❌ Base form.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous: Subject + was/were + V-ing.  
Приклад: *I was watching TV while she was reading.*
HINT,
                        'tense' => 'Past Continuous',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_affirmative', 'simultaneous_actions'],
                    ],
                    [
                        'text' => 'They _____ working when I called them.',
                        'options' => ['were not', 'was not', 'did not', 'are not'],
                        'answer' => 'were not',
                        'explanations' => [
                            'were not' => '✅ Past Continuous Negative. Підмет у множині.',
                            'was not' => '❌ Використовується з одниною.',
                            'did not' => '❌ Past Simple.',
                            'are not' => '❌ Present Simple.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous (Negative): Subject + were not + V-ing.  
Приклад: *They were not working yesterday.*
HINT,
                        'tense' => 'Past Continuous (Negative)',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_negative', 'interruption'],
                    ],
                    [
                        'text' => 'Was she _____ when you saw her?',
                        'options' => ['crying', 'cried', 'cries', 'had cried'],
                        'answer' => 'crying',
                        'explanations' => [
                            'crying' => '✅ Past Continuous Question.',
                            'cried' => '❌ Past Simple.',
                            'cries' => '❌ Present Simple.',
                            'had cried' => '❌ Past Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous (Question): Was + subject + V-ing?  
Приклад: *Was she crying?*
HINT,
                        'tense' => 'Past Continuous (Question)',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_question'],
                    ],
                    [
                        'text' => 'He _____ his car when the rain started.',
                        'options' => ['was washing', 'washed', 'had washed', 'washes'],
                        'answer' => 'was washing',
                        'explanations' => [
                            'was washing' => '✅ Past Continuous. Дія у процесі.',
                            'washed' => '❌ Past Simple.',
                            'had washed' => '❌ Past Perfect.',
                            'washes' => '❌ Present Simple.',
                        ],
                        'hint' => <<<'HINT'
Past Continuous Affirmative: Subject + was + V-ing.  
Приклад: *He was washing the car when it started to rain.*
HINT,
                        'tense' => 'Past Continuous',
                        'level' => 'A2',
                        'detail_tags' => ['past_continuous_affirmative', 'interruption'],
                    ],
                ],
            ],
            'C' => [
                'questions' => [
                    [
                        'text' => 'By the time she was 30, she _____ three books.',
                        'options' => ['wrote', 'was writing', 'has written', 'had written'],
                        'answer' => 'had written',
                        'explanations' => [
                            'wrote' => '❌ Past Simple.',
                            'was writing' => '❌ Past Continuous.',
                            'has written' => '❌ Present Perfect.',
                            'had written' => '✅ Past Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect: Subject + had + V3.  
Приклад: *By 20, he had learned two languages.*
HINT,
                        'tense' => 'Past Perfect',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_affirmative', 'completion_before_event'],
                    ],
                    [
                        'text' => 'After they _____ dinner, they went for a walk.',
                        'options' => ['finished', 'had finished', 'have finished', 'were finishing'],
                        'answer' => 'had finished',
                        'explanations' => [
                            'finished' => '❌ Past Simple.',
                            'had finished' => '✅ Past Perfect.',
                            'have finished' => '❌ Present Perfect.',
                            'were finishing' => '❌ Past Continuous.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect: Subject + had + V3.  
Приклад: *After she had eaten, she left.*
HINT,
                        'tense' => 'Past Perfect',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_affirmative', 'completion_before_event'],
                    ],
                    [
                        'text' => 'They didn’t buy the car because they _____ it before.',
                        'options' => ['saw', 'had seen', 'were seeing', 'have seen'],
                        'answer' => 'had seen',
                        'explanations' => [
                            'saw' => '❌ Past Simple.',
                            'had seen' => '✅ Past Perfect.',
                            'were seeing' => '❌ Past Continuous.',
                            'have seen' => '❌ Present Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect: Subject + had + V3.  
Приклад: *She didn’t watch the film because she had seen it before.*
HINT,
                        'tense' => 'Past Perfect',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_affirmative', 'prior_experience'],
                    ],
                    [
                        'text' => 'No sooner _____ the teacher started the lesson than the fire alarm rang.',
                        'options' => ['had', 'had he started', 'he had started', 'did he start'],
                        'answer' => 'had he started',
                        'explanations' => [
                            'had' => '❌ Неповна форма.',
                            'had he started' => '✅ Інверсія з No sooner.',
                            'he had started' => '❌ Порушено інверсію.',
                            'did he start' => '❌ Past Simple.',
                        ],
                        'hint' => <<<'HINT'
No sooner had + subject + V3 … than …  
Приклад: *No sooner had I arrived than it started to rain.*
HINT,
                        'tense' => 'Past Perfect (inversion)',
                        'level' => 'B2',
                        'detail_tags' => ['past_perfect_inversion'],
                    ],
                    [
                        'text' => '_____ he left when you arrived?',
                        'options' => ['Had', 'Did', 'Has', 'Was'],
                        'answer' => 'Had',
                        'explanations' => [
                            'Had' => '✅ Past Perfect Question.',
                            'Did' => '❌ Past Simple.',
                            'Has' => '❌ Present Perfect.',
                            'Was' => '❌ be.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect (Question): Had + subject + V3.  
Приклад: *Had he left before you arrived?*
HINT,
                        'tense' => 'Past Perfect (Question)',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_question'],
                    ],
                    [
                        'text' => 'I _____ finished my project when the deadline came.',
                        'options' => ['had', 'had not', 'did not', 'was not'],
                        'answer' => 'had not',
                        'explanations' => [
                            'had' => '❌ Неповна форма.',
                            'had not' => '✅ Past Perfect Negative.',
                            'did not' => '❌ Past Simple.',
                            'was not' => '❌ Past Continuous.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect (Negative): Subject + had not + V3.  
Приклад: *I had not studied before the test.*
HINT,
                        'tense' => 'Past Perfect (Negative)',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_negative'],
                    ],
                    [
                        'text' => 'She _____ already left when I called her.',
                        'options' => ['left', 'was leaving', 'had left', 'has left'],
                        'answer' => 'had left',
                        'explanations' => [
                            'left' => '❌ Past Simple.',
                            'was leaving' => '❌ Past Continuous.',
                            'had left' => '✅ Past Perfect.',
                            'has left' => '❌ Present Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect: Subject + had + V3.  
Приклад: *She had left before I arrived.*
HINT,
                        'tense' => 'Past Perfect',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_affirmative', 'completion_before_event'],
                    ],
                    [
                        'text' => 'By the time we arrived, the train _____ already left.',
                        'options' => ['left', 'was leaving', 'had left', 'has left'],
                        'answer' => 'had left',
                        'explanations' => [
                            'left' => '❌ Past Simple.',
                            'was leaving' => '❌ Past Continuous.',
                            'had left' => '✅ Past Perfect.',
                            'has left' => '❌ Present Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect Affirmative: had + V3.  
Приклад: *The train had left before we arrived.*
HINT,
                        'tense' => 'Past Perfect',
                        'level' => 'B1',
                        'detail_tags' => ['past_perfect_affirmative', 'completion_before_event'],
                    ],
                ],
            ],
            'D' => [
                'questions' => [
                    [
                        'text' => 'Thousands of people _____ during the earthquake.',
                        'options' => ['were killed', 'had killed', 'was killing', 'have been killed'],
                        'answer' => 'were killed',
                        'explanations' => [
                            'were killed' => '✅ Past Simple Passive. Правильна форма.',
                            'had killed' => '❌ Active form.',
                            'was killing' => '❌ Past Continuous.',
                            'have been killed' => '❌ Present Perfect Passive.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Passive: was/were + V3.  
Приклад: *Many houses were destroyed in the war.*
HINT,
                        'tense' => 'Past Simple Passive',
                        'level' => 'B1',
                        'detail_tags' => ['past_passive_affirmative'],
                    ],
                    [
                        'text' => 'The letter _____ yesterday by the secretary.',
                        'options' => ['wrote', 'was written', 'had written', 'writes'],
                        'answer' => 'was written',
                        'explanations' => [
                            'wrote' => '❌ Active form.',
                            'was written' => '✅ Past Simple Passive.',
                            'had written' => '❌ Active.',
                            'writes' => '❌ Present Simple.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Passive: was/were + V3.  
Приклад: *The report was written yesterday.*
HINT,
                        'tense' => 'Past Simple Passive',
                        'level' => 'B1',
                        'detail_tags' => ['past_passive_affirmative'],
                    ],
                    [
                        'text' => 'By 5 pm, the project _____ by the team.',
                        'options' => ['was finished', 'had been finished', 'is finished', 'finished'],
                        'answer' => 'had been finished',
                        'explanations' => [
                            'was finished' => '❌ Past Simple Passive.',
                            'had been finished' => '✅ Past Perfect Passive.',
                            'is finished' => '❌ Present Simple.',
                            'finished' => '❌ Active.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect Passive: had been + V3.  
Приклад: *The project had been finished before the boss arrived.*
HINT,
                        'tense' => 'Past Perfect Passive',
                        'level' => 'B2',
                        'detail_tags' => ['past_passive_perfect', 'completion_before_event'],
                    ],
                    [
                        'text' => 'Were the rooms _____ before the guests arrived?',
                        'options' => ['cleaned', 'were cleaned', 'being cleaned', 'had cleaned'],
                        'answer' => 'cleaned',
                        'explanations' => [
                            'cleaned' => '✅ Past Simple Passive (Question). Were + object + V3?',
                            'were cleaned' => '❌ Подвійна форма.',
                            'being cleaned' => '❌ Past Continuous Passive.',
                            'had cleaned' => '❌ Active voice.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Passive (Question): Were + object + V3?  
Приклад: *Were the rooms cleaned yesterday?*
HINT,
                        'tense' => 'Past Simple Passive (Question)',
                        'level' => 'B1',
                        'detail_tags' => ['past_passive_question', 'completion_before_event'],
                    ],
                    [
                        'text' => 'The bridge _____ in 1890.',
                        'options' => ['built', 'was built', 'had built', 'has been built'],
                        'answer' => 'was built',
                        'explanations' => [
                            'built' => '❌ V3 без допоміжного.',
                            'was built' => '✅ Past Simple Passive.',
                            'had built' => '❌ Active.',
                            'has been built' => '❌ Present Perfect Passive.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Passive: was + V3.  
Приклад: *The castle was built centuries ago.*
HINT,
                        'tense' => 'Past Simple Passive',
                        'level' => 'A2',
                        'detail_tags' => ['past_passive_affirmative'],
                    ],
                    [
                        'text' => 'The documents _____ already signed when I arrived.',
                        'options' => ['were', 'were signed', 'had been signed', 'signed'],
                        'answer' => 'had been signed',
                        'explanations' => [
                            'were' => '❌ be без V3.',
                            'were signed' => '❌ Past Simple Passive.',
                            'had been signed' => '✅ Past Perfect Passive.',
                            'signed' => '❌ Active.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect Passive: had been + V3.  
Приклад: *The contract had been signed before he came.*
HINT,
                        'tense' => 'Past Perfect Passive',
                        'level' => 'B2',
                        'detail_tags' => ['past_passive_perfect', 'completion_before_event'],
                    ],
                    [
                        'text' => 'Was the book _____ in 1995?',
                        'options' => ['published', 'publish', 'was published', 'had published'],
                        'answer' => 'published',
                        'explanations' => [
                            'published' => '✅ Correct. Past Simple Passive Question.',
                            'publish' => '❌ Base form.',
                            'was published' => '❌ Подвійна форма.',
                            'had published' => '❌ Active Past Perfect.',
                        ],
                        'hint' => <<<'HINT'
Past Simple Passive (Question): Was + object + V3?  
Приклад: *Was the book published last year?*
HINT,
                        'tense' => 'Past Simple Passive (Question)',
                        'level' => 'A2',
                        'detail_tags' => ['past_passive_question'],
                    ],
                    [
                        'text' => 'The new law _____ before the election.',
                        'options' => ['was passed', 'had been passed', 'passes', 'is passed'],
                        'answer' => 'had been passed',
                        'explanations' => [
                            'was passed' => '❌ Past Simple Passive.',
                            'had been passed' => '✅ Past Perfect Passive.',
                            'passes' => '❌ Present Simple.',
                            'is passed' => '❌ Present Passive.',
                        ],
                        'hint' => <<<'HINT'
Past Perfect Passive: had been + V3.  
Приклад: *The law had been passed before the crisis.*
HINT,
                        'tense' => 'Past Perfect Passive',
                        'level' => 'B2',
                        'detail_tags' => ['past_passive_perfect', 'completion_before_event'],
                    ],
                ],
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        $meta = [];

        foreach ($sections as $sectionKey => $section) {
            foreach ($section['questions'] as $index => $question) {
                [$questionText, $markers] = $this->replaceBlanks($question['text']);
                $markers = $markers ?: ['a1'];

                $uuid = $this->generateQuestionUuid($sectionKey, $index + 1, $questionText);

                $answers = [];
                $optionMarkerMap = [];
                $firstMarker = $markers[0] ?? null;

                foreach ($question['options'] as $option) {
                    if ($firstMarker !== null) {
                        $optionMarkerMap[$option] = $firstMarker;
                    }
                }

                $verbHints = $this->extractVerbHints($question['text']);

                foreach ($markers as $marker) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $question['answer'],
                        'verb_hint' => $verbHints[$marker] ?? null,
                    ];
                    $optionMarkerMap[$question['answer']] = $marker;
                }

                $tagIds = [$sectionThemeTags[$sectionKey]];

                $tenseName = $this->normalizeTenseName($question['tense']);
                if (isset($tenseTags[$tenseName])) {
                    $tagIds[] = $tenseTags[$tenseName];
                }

                foreach ($question['detail_tags'] as $detailKey) {
                    if (isset($detailTags[$detailKey])) {
                        $tagIds[] = $detailTags[$detailKey];
                    }
                }

                // Add fixed tag for corrected questions
                $fixedQuestions = [
                    'She _____ visit us last summer.',
                    'They _____ like the food in that restaurant.',
                    'She _____ listening to me yesterday.',
                    'They _____ working when I called them.',
                    'I _____ finished my project when the deadline came.',
                ];

                if (in_array($question['text'], $fixedQuestions)) {
                    $tagIds[] = $fixedTag->id;
                    $tagIds[] = $questionFixTag->id;
                }

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $questionText,
                    'category_id' => $categoryId,
                    'difficulty' => $levelDifficulty[$question['level']] ?? 3,
                    'source_id' => $sectionSources[$sectionKey],
                    'flag' => 2,
                    'level' => $question['level'],
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $question['options'],
                    'variants' => [$question['text']],
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => [$markers[0] => $question['answer']],
                    'option_markers' => $optionMarkerMap,
                    'hint' => $question['hint'],
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

            $hintText = $this->formatHint($data['hint']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($data['explanations'] as $option => $text) {
                $marker = $data['option_markers'][$option] ?? array_key_first($data['answers']);
                $correct = $data['answers'][$marker] ?? reset($data['answers']);

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
            $marker = 'a'.$index;
            $text = preg_replace('/_____/', '{'.$marker.'}', $text, 1);
            $markers[] = $marker;
            $index++;
        }

        return [$text, $markers];
    }

    private function normalizeTenseName(string $tense): string
    {
        $tense = trim($tense);

        if (str_starts_with($tense, 'Past Simple Passive')) {
            return 'Past Simple Passive';
        }

        if (str_starts_with($tense, 'Past Perfect Passive')) {
            return 'Past Perfect Passive';
        }

        if (str_starts_with($tense, 'Past Simple')) {
            return 'Past Simple';
        }

        if (str_starts_with($tense, 'Past Continuous')) {
            return 'Past Continuous';
        }

        if (str_starts_with($tense, 'Past Perfect')) {
            return 'Past Perfect';
        }

        return $tense;
    }

    private function extractVerbHints(string $text): array
    {
        $hints = [];
        $index = 1;

        while (preg_match('/\(([^)]+)\)/', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $marker = 'a'.$index;
            $hints[$marker] = trim($matches[1][0]);
            $text = substr_replace($text, '', $matches[0][1], strlen($matches[0][0]));
            $index++;
        }

        return $hints;
    }

    private function formatHint(?string $hint): ?string
    {
        if ($hint === null) {
            return null;
        }

        $trimmed = trim($hint);

        return $trimmed === '' ? null : $trimmed;
    }
}
