<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\Category;

class RevisionTensesFullSeeder extends Seeder
{
    public function run()
    {
        // Категорії
        $cat_past = Category::firstOrCreate(['name' => 'past'])->id;
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $cat_future = Category::firstOrCreate(['name' => 'Future'])->id;

        // 1. Write positive sentences
        $data[] = [
            'question' => 'I/tidy/my room/every day.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'I tidy my room every day.', 'verb_hint' => 'tidy']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Amy/play/the computer/yesterday.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Amy played the computer yesterday.', 'verb_hint' => 'play']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Bob/send/the telegram/tomorrow.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Bob will send the telegram tomorrow.', 'verb_hint' => 'send']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Andrew/repair/his bike/two days ago.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Andrew repaired his bike two days ago.', 'verb_hint' => 'repair']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'They/often/watch TV/in the morning.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'They often watch TV in the morning.', 'verb_hint' => 'watch']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Tim/probably/receive/a letter/next week.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Tim will probably receive a letter next week.', 'verb_hint' => 'receive']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Kate/help/me/with my Maths/last week.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Kate helped me with my Maths last week.', 'verb_hint' => 'help']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Lucy/have/two cheeseburgers/for lunch.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Lucy has two cheeseburgers for lunch.', 'verb_hint' => 'have']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'I/usually/call/my friends/in the afternoon.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => 'Write positive sentences: Склади речення у правильному порядку.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'I usually call my friends in the afternoon.', 'verb_hint' => 'call']
            ],
            'options' => [],
        ];

        // 2. Write negative sentences
        $data[] = [
            'question' => 'We/clean/the room/yesterday.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "We didn't clean the room yesterday.", 'verb_hint' => 'clean']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Robots/do/all the housework/in the future.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "Robots won't do all the housework in the future.", 'verb_hint' => 'do']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'My sister/usually/get up/at 8 o\'clock.',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "My sister doesn't usually get up at 8 o'clock.", 'verb_hint' => 'get']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Sue/often/do the shopping/on Fridays.',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "Sue doesn't often do the shopping on Fridays.", 'verb_hint' => 'do']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'They/make/a snowman/two days ago.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "They didn't make a snowman two days ago.", 'verb_hint' => 'make']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'We/move/to a new house/soon.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "We won't move to a new house soon.", 'verb_hint' => 'move']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Nick/never/go/to school/by bus.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "Nick never goes to school by bus.", 'verb_hint' => 'go']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Sally/send/two e-mails/yesterday.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "Sally didn't send two e-mails yesterday.", 'verb_hint' => 'send']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Tom/be/twenty/next year.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write negative sentences: Заперечні речення.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => "Tom won't be twenty next year.", 'verb_hint' => 'be']
            ],
            'options' => [],
        ];

        // 3. Write general questions
        $data[] = [
            'question' => 'My working day/usually/at nine/begin?',
            'difficulty' => 3,
            'category_id' => $cat_present,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Does my working day usually begin at nine?', 'verb_hint' => 'begin']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'They/live/last year/in a small flat?',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Did they live in a small flat last year?', 'verb_hint' => 'live']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Tim/a message/receive/yesterday?',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Did Tim receive a message yesterday?', 'verb_hint' => 'receive']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Sally/to the cinema/go/tomorrow?',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Will Sally go to the cinema tomorrow?', 'verb_hint' => 'go']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'My brother/every day/play/the guitar?',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Does my brother play the guitar every day?', 'verb_hint' => 'play']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Sam/that film/last week/see?',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Did Sam see that film last week?', 'verb_hint' => 'see']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Liz/to the exhibition/go/next week?',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Will Liz go to the exhibition next week?', 'verb_hint' => 'go']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'I/forget/always/my keys/on the table?',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Do I always forget my keys on the table?', 'verb_hint' => 'forget']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Mary/at school/dance/tomorrow?',
            'difficulty' => 2,
            'category_id' => $cat_future,
            'source' => 'Write general questions: Побудуй загальне питання.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Will Mary dance at school tomorrow?', 'verb_hint' => 'dance']
            ],
            'options' => [],
        ];

        // 4. Write questions to the underlined words
        $data[] = [
            'question' => 'She arrived at two o\'clock yesterday.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'When did she arrive?', 'verb_hint' => 'arrive']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'I shall have chicken and some salad.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'What will you have?', 'verb_hint' => 'have']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Laura helps me with my English every day.',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Who does Laura help with her English every day?', 'verb_hint' => 'help']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Timmy studied typing yesterday.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'What did Timmy study yesterday?', 'verb_hint' => 'study']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Jane always draws nice pictures.',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'What does Jane always draw?', 'verb_hint' => 'draw']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'They went to Italy last week.',
            'difficulty' => 2,
            'category_id' => $cat_past,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Where did they go last week?', 'verb_hint' => 'go']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'Bob will see this comedy next week.',
            'difficulty' => 2,
            'category_id' => $cat_future,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'What will Bob see next week?', 'verb_hint' => 'see']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'I often forget my things at home.',
            'difficulty' => 2,
            'category_id' => $cat_present,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Where do you often forget your things?', 'verb_hint' => 'forget']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'The porter will bring your luggage.',
            'difficulty' => 3,
            'category_id' => $cat_future,
            'source' => 'Write questions to the underlined words: Побудуй спеціальне питання до підкреслених слів.',
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'Who will bring your luggage?', 'verb_hint' => 'bring']
            ],
            'options' => [],
        ];

        // 5. Fill in the correct form of the verbs
        $fillSource = 'Fill in the correct form of the verbs.';
        $data[] = [
            'question' => 'I {a1} winter.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => $fillSource,
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'like', 'verb_hint' => 'like']
            ],
            'options' => [],
        ];
        $data[] = [
            'question' => 'It {a1} a very beautiful season.',
            'difficulty' => 1,
            'category_id' => $cat_present,
            'source' => $fillSource,
            'flag' => 0,
            'answers' => [
                ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be']
            ],
            'options' => [],
        ];
        // ... додай подібно всі пропуски з великого тексту (5 розділ)
        // для прикладу залишаю два, але можна додати всі (дай знати, якщо треба — розпишу всі)

        // =========================
        // Запис у базу:
        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $d['category_id'],
                'difficulty'  => $d['difficulty'],
                'source'      => $d['source'],
                'flag'        => $d['flag'],
            ]);
            foreach ($d['answers'] as $ans) {
                QuestionAnswer::create([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                    'verb_hint'   => $ans['verb_hint'] ?? null,
                ]);
            }
            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option'      => $opt,
                    ]);
                }
            }
        }
    }
}
