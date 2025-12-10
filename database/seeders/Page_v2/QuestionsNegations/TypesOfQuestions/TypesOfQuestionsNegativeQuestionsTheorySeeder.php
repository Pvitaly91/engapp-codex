<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Pages\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsNegativeQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'negative-questions-dont-you-know';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Negative Questions — Заперечні питання (Don\'t you know…?)',
            'subtitle_html' => '<p><strong>Negative questions</strong> (заперечні питання) — це питання з негативною формою, які зазвичай виражають здивування, подив або очікування позитивної відповіді. Вони можуть звучати як критика або сарказм, тому важливо розуміти контекст їх використання.</p>',
            'subtitle_text' => 'Теоретичний огляд заперечних питань (Negative questions) в англійській мові: структура, правила формування та контексти використання питань типу "Don\'t you know...?".',
            'locale' => 'uk',
            'category' => [
                'slug' => 'types-of-questions',
                'title' => 'Види питальних речень',
                'language' => 'uk',
            ],
            'tags' => [
                'Negative Questions',
                'Заперечні питання',
                'Don\'t you',
                'Isn\'t she',
                'Didn\'t they',
                'Grammar',
                'Theory',
                'B1',
                'B2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш <strong>заперечні питання (Negative Questions)</strong> — питання з негативною формою, які виражають здивування, подив або очікування певної відповіді.',
                        'rules' => [
                            [
                                'label' => 'СТРУКТУРА',
                                'color' => 'emerald',
                                'text' => '<strong>Негативна форма</strong> допоміжного дієслова:',
                                'example' => 'Don\'t you know? Isn\'t she coming?',
                            ],
                            [
                                'label' => 'ЗНАЧЕННЯ',
                                'color' => 'blue',
                                'text' => '<strong>Здивування</strong> або очікування:',
                                'example' => 'Don\'t you like coffee? (здивований)',
                            ],
                            [
                                'label' => 'ВІДПОВІДЬ',
                                'color' => 'amber',
                                'text' => '<strong>Yes/No</strong> за змістом, не за формою:',
                                'example' => 'Don\'t you know? — Yes, I do. / No, I don\'t.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Що таке Negative Questions?',
                        'intro' => 'Negative questions — це питання з негативною формою допоміжного дієслова:',
                        'items' => [
                            ['label' => 'Звичайне питання', 'title' => 'Do you know?', 'subtitle' => 'Нейтральне питання'],
                            ['label' => 'Заперечне питання', 'title' => 'Don\'t you know?', 'subtitle' => 'Здивування або очікування'],
                            ['label' => 'Відмінність', 'title' => 'Емоційний підтекст', 'subtitle' => 'Виражає ставлення мовця'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Negative Questions з DO/DOES/DID',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'У Present Simple використовуємо <strong>don\'t/doesn\'t</strong>:',
                                'examples' => [
                                    ['en' => 'Don\'t you like coffee?', 'ua' => 'Хіба тобі не подобається кава? (здивування)'],
                                    ['en' => 'Doesn\'t she work here?', 'ua' => 'Хіба вона тут не працює? (очікую "так")'],
                                    ['en' => 'Don\'t they know the answer?', 'ua' => 'Хіба вони не знають відповідь?'],
                                    ['en' => 'Doesn\'t he speak English?', 'ua' => 'Хіба він не розмовляє англійською?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'sky',
                                'description' => 'У Past Simple використовуємо <strong>didn\'t</strong>:',
                                'examples' => [
                                    ['en' => 'Didn\'t you see the movie?', 'ua' => 'Хіба ти не бачив фільм?'],
                                    ['en' => 'Didn\'t she call you?', 'ua' => 'Хіба вона тобі не телефонувала?'],
                                    ['en' => 'Didn\'t they arrive yesterday?', 'ua' => 'Хіба вони не приїхали вчора?'],
                                    ['en' => 'Didn\'t it rain last night?', 'ua' => 'Хіба вчора ввечері не йшов дощ?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'purple',
                                'description' => 'Формула заперечного питання з do/does/did:',
                                'examples' => [
                                    ['en' => 'Don\'t/Doesn\'t/Didn\'t + Subject + Verb?', 'ua' => 'Негативна форма + Підмет + Дієслово?'],
                                ],
                                'note' => '📌 Основне дієслово залишається у базовій формі',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Negative Questions з TO BE',
                        'sections' => [
                            [
                                'label' => 'Present Simple — ISN\'T/AREN\'T',
                                'color' => 'blue',
                                'description' => 'З <strong>to be</strong> у Present Simple використовуємо isn\'t/aren\'t:',
                                'examples' => [
                                    ['en' => 'Isn\'t she happy?', 'ua' => 'Хіба вона не щаслива?'],
                                    ['en' => 'Aren\'t you ready?', 'ua' => 'Хіба ти не готовий?'],
                                    ['en' => 'Isn\'t it expensive?', 'ua' => 'Хіба це не дорого?'],
                                    ['en' => 'Aren\'t they at home?', 'ua' => 'Хіба вони не вдома?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple — WASN\'T/WEREN\'T',
                                'color' => 'amber',
                                'description' => 'У Past Simple використовуємо wasn\'t/weren\'t:',
                                'examples' => [
                                    ['en' => 'Wasn\'t he at work yesterday?', 'ua' => 'Хіба він не був на роботі вчора?'],
                                    ['en' => 'Weren\'t you tired?', 'ua' => 'Хіба ти не був втомлений?'],
                                    ['en' => 'Wasn\'t it difficult?', 'ua' => 'Хіба це не було складно?'],
                                    ['en' => 'Weren\'t they happy?', 'ua' => 'Хіба вони не були щасливі?'],
                                ],
                            ],
                            [
                                'label' => 'Формальний варіант',
                                'color' => 'rose',
                                'description' => 'У формальній мові можна використовувати повну форму:',
                                'examples' => [
                                    ['en' => 'Is she not happy?', 'ua' => 'Вона не щаслива? (формально)'],
                                    ['en' => 'Are you not ready?', 'ua' => 'Ви не готові? (формально)'],
                                ],
                                'note' => '📌 Isn\'t she? — розмовне, Is she not? — формальне',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Negative Questions з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'CAN\'T / COULDN\'T',
                                'color' => 'emerald',
                                'description' => 'З <strong>can/could</strong> використовуємо can\'t/couldn\'t:',
                                'examples' => [
                                    ['en' => 'Can\'t you swim?', 'ua' => 'Хіба ти не вмієш плавати?'],
                                    ['en' => 'Couldn\'t she drive?', 'ua' => 'Хіба вона не вміла водити?'],
                                    ['en' => 'Can\'t they help us?', 'ua' => 'Хіба вони не можуть нам допомогти?'],
                                ],
                            ],
                            [
                                'label' => 'WON\'T / WOULDN\'T',
                                'color' => 'sky',
                                'description' => 'З <strong>will/would</strong> використовуємо won\'t/wouldn\'t:',
                                'examples' => [
                                    ['en' => 'Won\'t you come?', 'ua' => 'Хіба ти не прийдеш?'],
                                    ['en' => 'Wouldn\'t she like it?', 'ua' => 'Хіба їй це не сподобається?'],
                                    ['en' => 'Won\'t they be late?', 'ua' => 'Хіба вони не спізняться?'],
                                ],
                            ],
                            [
                                'label' => 'SHOULDN\'T / MUSTN\'T',
                                'color' => 'purple',
                                'description' => 'Інші модальні також можуть мати негативну форму:',
                                'examples' => [
                                    ['en' => 'Shouldn\'t we go?', 'ua' => 'Хіба нам не варто піти?'],
                                    ['en' => 'Mustn\'t I wait?', 'ua' => 'Хіба мені не треба чекати?'],
                                ],
                                'note' => '📌 Негативна форма модального + підмет + дієслово',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Negative Questions з іншими часами',
                        'sections' => [
                            [
                                'label' => 'Present Perfect',
                                'color' => 'blue',
                                'description' => 'У Present Perfect використовуємо haven\'t/hasn\'t:',
                                'examples' => [
                                    ['en' => 'Haven\'t you seen the movie?', 'ua' => 'Хіба ти не бачив фільм?'],
                                    ['en' => 'Hasn\'t she finished yet?', 'ua' => 'Хіба вона ще не закінчила?'],
                                    ['en' => 'Haven\'t they arrived?', 'ua' => 'Хіба вони не приїхали?'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous',
                                'color' => 'amber',
                                'description' => 'У Present Continuous використовуємо isn\'t/aren\'t + Ving:',
                                'examples' => [
                                    ['en' => 'Aren\'t you working now?', 'ua' => 'Хіба ти зараз не працюєш?'],
                                    ['en' => 'Isn\'t she coming?', 'ua' => 'Хіба вона не йде?'],
                                    ['en' => 'Aren\'t they playing?', 'ua' => 'Хіба вони не граються?'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'rose',
                                'description' => 'У Past Continuous використовуємо wasn\'t/weren\'t + Ving:',
                                'examples' => [
                                    ['en' => 'Weren\'t you sleeping?', 'ua' => 'Хіба ти не спав?'],
                                    ['en' => 'Wasn\'t she studying?', 'ua' => 'Хіба вона не вчилася?'],
                                ],
                                'note' => '📌 Структура: негативна форма допоміжного дієслова + підмет + основна частина',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Значення та використання Negative Questions',
                        'sections' => [
                            [
                                'label' => 'Здивування',
                                'color' => 'emerald',
                                'description' => 'Виражають <strong>здивування</strong>, що щось не так, як очікувалося:',
                                'examples' => [
                                    ['en' => 'Don\'t you like pizza? Everyone likes pizza!', 'ua' => 'Хіба тобі не подобається піца? Всім подобається піца!'],
                                    ['en' => 'Isn\'t she coming? I thought she would.', 'ua' => 'Хіба вона не йде? Я думав, вона прийде.'],
                                ],
                            ],
                            [
                                'label' => 'Очікування позитивної відповіді',
                                'color' => 'sky',
                                'description' => 'Очікуємо, що відповідь буде "так":',
                                'examples' => [
                                    ['en' => 'Didn\'t you receive my email?', 'ua' => 'Хіба ти не отримав моє повідомлення? (очікую "отримав")'],
                                    ['en' => 'Doesn\'t he work here?', 'ua' => 'Хіба він тут не працює? (думаю, що працює)'],
                                ],
                            ],
                            [
                                'label' => 'Ввічливе прохання або пропозиція',
                                'color' => 'purple',
                                'description' => 'Можуть звучати м\'якше та ввічливіше:',
                                'examples' => [
                                    ['en' => 'Won\'t you have some tea?', 'ua' => 'Хіба ви не вип\'єте чаю? (ввічливе запрошення)'],
                                    ['en' => 'Won\'t you sit down?', 'ua' => 'Хіба ви не сядете? (ввічливе прохання)'],
                                ],
                            ],
                            [
                                'label' => 'Критика або сарказм',
                                'color' => 'rose',
                                'description' => 'Можуть звучати як критика (залежить від тону):',
                                'examples' => [
                                    ['en' => 'Don\'t you ever listen?', 'ua' => 'Хіба ти взагалі слухаєш? (критика)'],
                                    ['en' => 'Didn\'t I tell you so?', 'ua' => 'Хіба я тобі не казав? (сарказм)'],
                                ],
                                'note' => '⚠️ Інтонація та контекст дуже важливі!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Як відповідати на Negative Questions',
                        'sections' => [
                            [
                                'label' => 'Відповідь за змістом',
                                'color' => 'emerald',
                                'description' => 'Відповідаємо <strong>Yes/No за змістом</strong>, а не за формою питання:',
                                'examples' => [
                                    ['en' => 'Q: Don\'t you like coffee? — A: Yes, I do. (я люблю)', 'ua' => 'Питання негативне, але відповідь позитивна'],
                                    ['en' => 'Q: Don\'t you like coffee? — A: No, I don\'t. (я не люблю)', 'ua' => 'Питання і відповідь обидва негативні'],
                                ],
                            ],
                            [
                                'label' => 'Увага до плутанини',
                                'color' => 'amber',
                                'description' => 'У деяких мовах логіка інша, тому будь уважним:',
                                'examples' => [
                                    ['en' => 'Don\'t you know? — Yes, I know. (англійська)', 'ua' => 'Хіба ти не знаєш? — Так, я знаю.'],
                                    ['en' => 'Don\'t you know? — No, I don\'t know. (англійська)', 'ua' => 'Хіба ти не знаєш? — Ні, я не знаю.'],
                                ],
                            ],
                            [
                                'label' => 'Повна відповідь краще',
                                'color' => 'sky',
                                'description' => 'Для ясності краще давати повну відповідь:',
                                'examples' => [
                                    ['en' => 'Q: Isn\'t she coming? — A: No, she isn\'t coming.', 'ua' => 'Чітка відповідь уникає плутанини'],
                                    ['en' => 'Q: Don\'t you like it? — A: Yes, I like it very much.', 'ua' => 'Повна відповідь зрозуміліша'],
                                ],
                                'note' => '📌 Yes = згоден з позитивом, No = згоден з негативом',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Порівняння звичайних та заперечних питань',
                        'intro' => 'Відмінності між звичайними та заперечними питаннями:',
                        'rows' => [
                            [
                                'en' => 'Positive Question',
                                'ua' => 'Звичайне питання',
                                'note' => 'Do you like coffee? — нейтральне запитання',
                            ],
                            [
                                'en' => 'Negative Question',
                                'ua' => 'Заперечне питання',
                                'note' => 'Don\'t you like coffee? — здивування або очікування',
                            ],
                            [
                                'en' => 'Emotion',
                                'ua' => 'Емоція',
                                'note' => 'Заперечне питання має емоційний підтекст',
                            ],
                            [
                                'en' => 'Expectation',
                                'ua' => 'Очікування',
                                'note' => 'Заперечне питання часто очікує певної відповіді',
                            ],
                            [
                                'en' => 'Answer',
                                'ua' => 'Відповідь',
                                'note' => 'Yes/No за змістом, не за формою питання',
                            ],
                        ],
                        'warning' => '📌 Заперечні питання виражають <strong>ставлення мовця</strong>, не просто запитують факт',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Плутанина з відповіддю (відповідати за формою, а не за змістом).',
                                'wrong' => '❌ Q: Don\'t you like it? A: Yes, I don\'t. (неправильно)',
                                'right' => '✅ <span class="font-mono">Q: Don\'t you like it? A: No, I don\'t. / Yes, I do.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів (формальний варіант).',
                                'wrong' => '❌ You don\'t like coffee?',
                                'right' => '✅ <span class="font-mono">Don\'t you like coffee?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма основного дієслова.',
                                'wrong' => '❌ Doesn\'t she likes it?',
                                'right' => '✅ <span class="font-mono">Doesn\'t she like it?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Використання заперечного питання без емоційного підтексту.',
                                'wrong' => '❌ Don\'t you have a pen? (якщо просто нейтрально питаєш)',
                                'right' => '✅ <span class="font-mono">Do you have a pen? (нейтрально)</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            '<strong>Negative questions</strong> — питання з негативною формою допоміжного дієслова.',
                            '<strong>Структура</strong>: Don\'t/Doesn\'t/Didn\'t + підмет + дієслово / Isn\'t/Aren\'t + підмет.',
                            '<strong>Значення</strong>: здивування, очікування позитивної відповіді, ввічливе прохання або критика.',
                            '<strong>З do/does/did</strong>: Don\'t you know? / Doesn\'t she work here? / Didn\'t they come?',
                            '<strong>З to be</strong>: Isn\'t she happy? / Aren\'t you ready? / Wasn\'t it good?',
                            '<strong>З модальними</strong>: Can\'t you swim? / Won\'t she come? / Shouldn\'t we go?',
                            '<strong>Відповідь</strong>: Yes/No за змістом, не за формою питання.',
                            '<strong>Yes = згода з позитивом</strong>: Don\'t you like it? — Yes, I do (я люблю).',
                            '<strong>No = згода з негативом</strong>: Don\'t you like it? — No, I don\'t (я не люблю).',
                            '<strong>Контекст важливий</strong>: інтонація визначає, чи це здивування, критика або ввічливість.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильну форму',
                        'select_intro' => 'Обери правильне заперечне питання.',
                        'selects' => [
                            ['label' => 'a) You don\'t like coffee? / b) Don\'t you like coffee? / c) Do you don\'t like coffee?', 'prompt' => 'Яка форма правильна?'],
                            ['label' => 'a) She isn\'t happy? / b) Isn\'t she happy? / c) Is she not happy?', 'prompt' => 'Яка форма розмовна?'],
                            ['label' => 'a) Can\'t you swim? / b) You can\'t swim? / c) Don\'t you can swim?', 'prompt' => 'Яка форма правильна?'],
                        ],
                        'options' => ['a', 'b', 'c'],
                        'input_title' => 'Вправа 2. Створи заперечне питання',
                        'input_intro' => 'Перетвори звичайне питання на заперечне.',
                        'inputs' => [
                            ['before' => 'Do you know? → ', 'after' => ''],
                            ['before' => 'Is she coming? → ', 'after' => ''],
                            ['before' => 'Can they help? → ', 'after' => ''],
                            ['before' => 'Did you see it? → ', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у заперечному питанні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'You don\'t like coffee?',
                                'example_target' => 'Don\'t you like coffee?',
                            ],
                            [
                                'original' => '1. Doesn\'t she likes it?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. You can\'t swim?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. Do you don\'t know?',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Види питальних речень',
                        'items' => [
                            [
                                'label' => 'Yes/No Questions — Загальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Alternative Questions — Альтернативні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Question Tags — Розділові питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Negative Questions — Заперечні питання (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
