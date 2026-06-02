<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityZeroArticleTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'zero-article';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Zero Article — Нульовий артикль',
            'subtitle_html' => '<p><strong>Zero article</strong> (нульовий артикль) — це відсутність артикля перед іменником. В англійській мові багато випадків, коли артикль <strong>не потрібен</strong>: з абстрактними поняттями, загальними назвами, власними іменами, множиною в загальному значенні та у сталих виразах.</p>',
            'subtitle_text' => 'Теоретичний огляд нульового артикля в англійській мові: коли артикль не потрібен, правила вживання та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Zero Article',
                'Articles',
                'No Article',
                'Nouns',
                'Abstract Nouns',
                'Proper Nouns',
                'Plural Nouns',
                'Fixed Expressions',
                'Grammar',
                'Theory',
                'A1',
                'A2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>нульовий артикль</strong> — коли в англійській мові <strong>артикль не потрібен</strong> перед іменником.',
                        'rules' => [
                            [
                                'label' => 'Абстракції',
                                'color' => 'emerald',
                                'text' => '<strong>Абстрактні поняття</strong> — загальне значення:',
                                'example' => 'Love is important. / I like music.',
                            ],
                            [
                                'label' => 'Множина',
                                'color' => 'blue',
                                'text' => '<strong>Множина в загальному</strong> — усі представники:',
                                'example' => 'Dogs are loyal. / I like cats.',
                            ],
                            [
                                'label' => 'Імена',
                                'color' => 'amber',
                                'text' => '<strong>Власні назви</strong> — країни, міста, імена:',
                                'example' => 'Ukraine, London, John',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке нульовий артикль?',
                        'intro' => 'Нульовий артикль (Zero Article) — це відсутність артикля перед іменником:',
                        'items' => [
                            ['label' => 'Ø Article', 'title' => 'Без артикля', 'subtitle' => 'Іменник без a/an або the — це нульовий артикль'],
                            ['label' => 'Common', 'title' => 'Дуже поширене', 'subtitle' => 'В англійській багато випадків, коли артикль не потрібен'],
                            ['label' => 'Rules', 'title' => 'Є правила', 'subtitle' => 'Певні категорії слів завжди без артикля'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Абстрактні та незлічувані іменники',
                        'sections' => [
                            [
                                'label' => 'Абстрактні поняття',
                                'color' => 'emerald',
                                'description' => '<strong>Абстрактні іменники</strong> в загальному значенні — без артикля.',
                                'examples' => [
                                    ['en' => 'Love is important.', 'ua' => 'Кохання важливе.'],
                                    ['en' => 'Happiness makes life better.', 'ua' => 'Щастя робить життя кращим.'],
                                    ['en' => 'Knowledge is power.', 'ua' => 'Знання — сила.'],
                                    ['en' => 'Freedom is valuable.', 'ua' => 'Свобода цінна.'],
                                ],
                            ],
                            [
                                'label' => 'Незлічувані загалом',
                                'color' => 'sky',
                                'description' => '<strong>Незлічувані іменники</strong> в загальному — без артикля.',
                                'examples' => [
                                    ['en' => 'I love music.', 'ua' => 'Я люблю музику. (музику взагалі)'],
                                    ['en' => 'Water is essential.', 'ua' => 'Вода необхідна. (вода загалом)'],
                                    ['en' => 'Money can\'t buy happiness.', 'ua' => 'Гроші не можуть купити щастя.'],
                                    ['en' => 'Information is everywhere.', 'ua' => 'Інформація скрізь.'],
                                ],
                            ],
                            [
                                'label' => 'Увага!',
                                'color' => 'amber',
                                'description' => 'Якщо йдеться про <strong>конкретну</strong> порцію, потрібен артикль <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'I love music. (загалом)', 'ua' => 'Я люблю музику.'],
                                    ['en' => 'I love the music in this film. (конкретна)', 'ua' => 'Мені подобається музика з цього фільму.'],
                                ],
                                'note' => '📌 Загальне поняття — Ø, конкретне — the.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Множина в загальному значенні',
                        'sections' => [
                            [
                                'label' => 'Загальні твердження',
                                'color' => 'blue',
                                'description' => 'Коли говоримо про <strong>всі представники групи</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'Dogs are loyal animals.', 'ua' => 'Собаки — вірні тварини. (усі собаки)'],
                                    ['en' => 'Cats like to sleep.', 'ua' => 'Коти люблять спати. (коти взагалі)'],
                                    ['en' => 'Books are expensive.', 'ua' => 'Книги дорогі. (книги загалом)'],
                                    ['en' => 'Children need love.', 'ua' => 'Діти потребують любові.'],
                                ],
                            ],
                            [
                                'label' => 'Уподобання і хобі',
                                'color' => 'emerald',
                                'description' => 'З дієсловами <strong>like, love, hate</strong> про загальні речі — без артикля.',
                                'examples' => [
                                    ['en' => 'I like apples.', 'ua' => 'Я люблю яблука. (яблука взагалі)'],
                                    ['en' => 'She loves flowers.', 'ua' => 'Вона любить квіти.'],
                                    ['en' => 'He hates spiders.', 'ua' => 'Він ненавидить павуків.'],
                                    ['en' => 'We enjoy movies.', 'ua' => 'Нам подобаються фільми.'],
                                ],
                            ],
                            [
                                'label' => 'Професії множиною',
                                'color' => 'sky',
                                'description' => 'Множина професій в загальному — без артикля.',
                                'examples' => [
                                    ['en' => 'Teachers work hard.', 'ua' => 'Вчителі важко працюють.'],
                                    ['en' => 'Doctors save lives.', 'ua' => 'Лікарі рятують життя.'],
                                    ['en' => 'Engineers solve problems.', 'ua' => 'Інженери вирішують проблеми.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Власні назви (імена, країни, міста)',
                        'sections' => [
                            [
                                'label' => 'Імена людей',
                                'color' => 'purple',
                                'description' => '<strong>Імена та прізвища</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'John is my friend.', 'ua' => 'Джон — мій друг.'],
                                    ['en' => 'Maria works here.', 'ua' => 'Марія працює тут.'],
                                    ['en' => 'Mr. Smith called yesterday.', 'ua' => 'Містер Сміт дзвонив вчора.'],
                                    ['en' => 'President Zelenskyy spoke today.', 'ua' => 'Президент Зеленський говорив сьогодні.'],
                                ],
                            ],
                            [
                                'label' => 'Країни і міста',
                                'color' => 'emerald',
                                'description' => '<strong>Більшість країн та міст</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'Ukraine is beautiful.', 'ua' => 'Україна прекрасна.'],
                                    ['en' => 'I live in Kyiv.', 'ua' => 'Я живу в Києві.'],
                                    ['en' => 'France is in Europe.', 'ua' => 'Франція в Європі.'],
                                    ['en' => 'London is a big city.', 'ua' => 'Лондон — велике місто.'],
                                ],
                            ],
                            [
                                'label' => 'Винятки з артиклем THE',
                                'color' => 'rose',
                                'description' => 'Деякі країни <strong>завжди з the</strong> (множина або союз):',
                                'examples' => [
                                    ['en' => 'the USA, the United States', 'ua' => 'США, Сполучені Штати'],
                                    ['en' => 'the UK, the United Kingdom', 'ua' => 'Велика Британія, Сполучене Королівство'],
                                    ['en' => 'the Netherlands', 'ua' => 'Нідерланди'],
                                    ['en' => 'the Philippines', 'ua' => 'Філіппіни'],
                                ],
                                'note' => '⚠️ Ці винятки треба запамʼятати!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Географічні обʼєкти без артикля',
                        'sections' => [
                            [
                                'label' => 'Континенти',
                                'color' => 'sky',
                                'description' => '<strong>Назви континентів</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'Europe, Asia, Africa', 'ua' => 'Європа, Азія, Африка'],
                                    ['en' => 'North America, South America', 'ua' => 'Північна Америка, Південна Америка'],
                                    ['en' => 'Australia is a continent.', 'ua' => 'Австралія — континент.'],
                                ],
                            ],
                            [
                                'label' => 'Гори (окремі вершини)',
                                'color' => 'emerald',
                                'description' => '<strong>Окремі гори</strong> — без артикля (але гірські системи — з the).',
                                'examples' => [
                                    ['en' => 'Mount Everest', 'ua' => 'Гора Еверест'],
                                    ['en' => 'Hoverla is the highest peak in Ukraine.', 'ua' => 'Говерла — найвища вершина в Україні.'],
                                    ['en' => 'BUT: the Alps, the Carpathians', 'ua' => 'АЛЕ: Альпи, Карпати (системи — з the)'],
                                ],
                            ],
                            [
                                'label' => 'Озера (з Lake)',
                                'color' => 'blue',
                                'description' => 'Якщо <strong>Lake + назва</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'Lake Baikal', 'ua' => 'Озеро Байкал'],
                                    ['en' => 'Lake Superior', 'ua' => 'Озеро Верхнє'],
                                    ['en' => 'BUT: the Baikal (без слова Lake)', 'ua' => 'АЛЕ: без слова Lake потрібен the'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Сталі вирази без артикля',
                        'sections' => [
                            [
                                'label' => 'Місця (at/to + місце)',
                                'color' => 'amber',
                                'description' => 'У сталих виразах з <strong>місцями</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'at home, at work, at school', 'ua' => 'вдома, на роботі, в школі'],
                                    ['en' => 'go to bed, go to work, go to church', 'ua' => 'лягти спати, йти на роботу, йти до церкви'],
                                    ['en' => 'in hospital (UK), in prison', 'ua' => 'в лікарні, у вʼязниці'],
                                    ['en' => 'at university, at college', 'ua' => 'в університеті, в коледжі'],
                                ],
                            ],
                            [
                                'label' => 'Транспорт (by + транспорт)',
                                'color' => 'sky',
                                'description' => 'З прийменником <strong>by</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'by car, by bus, by train', 'ua' => 'машиною, автобусом, поїздом'],
                                    ['en' => 'by plane, by boat, by bike', 'ua' => 'літаком, човном, велосипедом'],
                                    ['en' => 'BUT: on the bus, in the car', 'ua' => 'АЛЕ: з on/in потрібен артикль'],
                                ],
                            ],
                            [
                                'label' => 'Їжа (прийоми їжі)',
                                'color' => 'emerald',
                                'description' => 'Назви <strong>прийомів їжі</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'have breakfast, have lunch, have dinner', 'ua' => 'снідати, обідати, вечеряти'],
                                    ['en' => 'What\'s for dinner?', 'ua' => 'Що на вечерю?'],
                                    ['en' => 'after breakfast', 'ua' => 'після сніданку'],
                                    ['en' => 'BUT: the dinner was delicious', 'ua' => 'АЛЕ: конкретна вечеря — з the'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Спорт, мови, предмети',
                        'sections' => [
                            [
                                'label' => 'Види спорту',
                                'color' => 'purple',
                                'description' => '<strong>Назви спортивних ігор</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'play football, play tennis', 'ua' => 'грати у футбол, грати в теніс'],
                                    ['en' => 'I like basketball.', 'ua' => 'Мені подобається баскетбол.'],
                                    ['en' => 'She plays volleyball.', 'ua' => 'Вона грає у волейбол.'],
                                ],
                            ],
                            [
                                'label' => 'Мови',
                                'color' => 'blue',
                                'description' => '<strong>Назви мов</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'I speak English.', 'ua' => 'Я розмовляю англійською.'],
                                    ['en' => 'She studies Ukrainian.', 'ua' => 'Вона вивчає українську.'],
                                    ['en' => 'French is beautiful.', 'ua' => 'Французька прекрасна.'],
                                    ['en' => 'BUT: the English language', 'ua' => 'АЛЕ: з словом language потрібен the'],
                                ],
                            ],
                            [
                                'label' => 'Навчальні предмети',
                                'color' => 'emerald',
                                'description' => '<strong>Шкільні предмети</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'I like mathematics.', 'ua' => 'Мені подобається математика.'],
                                    ['en' => 'History is interesting.', 'ua' => 'Історія цікава.'],
                                    ['en' => 'She studies biology.', 'ua' => 'Вона вивчає біологію.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Час доби та свята',
                        'sections' => [
                            [
                                'label' => 'Дні тижня, місяці, свята',
                                'color' => 'rose',
                                'description' => '<strong>Дні, місяці та свята</strong> — без артикля.',
                                'examples' => [
                                    ['en' => 'Monday, Tuesday, Wednesday', 'ua' => 'понеділок, вівторок, середа'],
                                    ['en' => 'January, February, March', 'ua' => 'січень, лютий, березень'],
                                    ['en' => 'Christmas, Easter, New Year', 'ua' => 'Різдво, Великдень, Новий рік'],
                                    ['en' => 'on Monday, in January', 'ua' => 'у понеділок, у січні'],
                                ],
                            ],
                            [
                                'label' => 'Пори року',
                                'color' => 'amber',
                                'description' => '<strong>Пори року</strong> — зазвичай без артикля (але може бути варіація).',
                                'examples' => [
                                    ['en' => 'Spring is beautiful.', 'ua' => 'Весна прекрасна.'],
                                    ['en' => 'I like summer.', 'ua' => 'Я люблю літо.'],
                                    ['en' => 'in autumn, in winter', 'ua' => 'восени, взимку'],
                                    ['en' => 'BUT: the summer of 2023', 'ua' => 'АЛЕ: конкретне літо може бути з the'],
                                ],
                            ],
                            [
                                'label' => 'Частини доби (загалом)',
                                'color' => 'sky',
                                'description' => 'У деяких виразах — без артикля.',
                                'examples' => [
                                    ['en' => 'at night (without the!)', 'ua' => 'вночі (без the!)'],
                                    ['en' => 'by day, by night', 'ua' => 'вдень, вночі'],
                                    ['en' => 'BUT: in the morning, in the afternoon', 'ua' => 'АЛЕ: вранці, вдень (з the)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Порівняльна таблиця: Ø vs The',
                        'intro' => 'Основні випадки, коли НЕ потрібен артикль:',
                        'rows' => [
                            [
                                'en' => 'General / abstract',
                                'ua' => 'Загальне / абстрактне',
                                'note' => 'Ø: Love is important. / Music is great.',
                            ],
                            [
                                'en' => 'Plural in general',
                                'ua' => 'Множина загалом',
                                'note' => 'Ø: Dogs are loyal. / Books are expensive.',
                            ],
                            [
                                'en' => 'Countries, cities',
                                'ua' => 'Країни, міста',
                                'note' => 'Ø: Ukraine, London (but: the USA, the UK)',
                            ],
                            [
                                'en' => 'Names',
                                'ua' => 'Імена',
                                'note' => 'Ø: John, Maria, Mr. Smith',
                            ],
                            [
                                'en' => 'Fixed expressions',
                                'ua' => 'Сталі вирази',
                                'note' => 'Ø: at home, by car, have breakfast',
                            ],
                            [
                                'en' => 'Sports, languages',
                                'ua' => 'Спорт, мови',
                                'note' => 'Ø: play football, speak English',
                            ],
                        ],
                        'warning' => '📌 Якщо щось <strong>конкретне</strong> або <strong>означене</strong> — використовуємо <strong>the</strong>!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Артикль перед абстракціями.',
                                'wrong' => 'The love is important.',
                                'right' => '✅ <span class="font-mono">Love is important.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Артикль перед країною Ukraine.',
                                'wrong' => 'I live in the Ukraine.',
                                'right' => '✅ <span class="font-mono">I live in Ukraine.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Артикль перед множиною в загальному.',
                                'wrong' => 'The dogs are loyal.',
                                'right' => '✅ <span class="font-mono">Dogs are loyal. (усі собаки загалом)</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Артикль у сталих виразах.',
                                'wrong' => 'go to the bed, at the home',
                                'right' => '✅ <span class="font-mono">go to bed, at home</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Короткий конспект',
                        'items' => [
                            '<strong>Абстрактні поняття</strong> загалом — без артикля: <em>Love, happiness, freedom</em>.',
                            '<strong>Множина в загальному</strong> — без артикля: <em>Dogs are loyal, books are expensive</em>.',
                            '<strong>Більшість країн і міст</strong> — без артикля: <em>Ukraine, London, Paris</em>.',
                            '<strong>Винятки</strong>: the USA, the UK, the Netherlands (множина або союз).',
                            '<strong>Імена людей</strong> — без артикля: <em>John, Maria, Mr. Smith</em>.',
                            '<strong>Сталі вирази</strong>: at home, by car, have breakfast, go to bed.',
                            '<strong>Спорт і мови</strong>: play football, speak English.',
                            '<strong>Дні, місяці, свята</strong>: Monday, January, Christmas.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '12. Практика',
                        'select_title' => 'Вправа 1. Чи потрібен артикль?',
                        'select_intro' => 'Визначте, чи потрібен артикль у цих реченнях.',
                        'selects' => [
                            ['label' => '___ love is important. (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => 'I live in ___ Ukraine. (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => '___ dogs are loyal. (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => 'I speak ___ English. (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                        ],
                        'options' => ['Ø (без артикля)', 'the'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши Ø (без артикля) або the.',
                        'inputs' => [
                            ['before' => 'I go to ___ work by ___ bus.', 'after' => '→'],
                            ['before' => '___ freedom is important.', 'after' => '→'],
                            ['before' => 'She lives in ___ USA.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку з артиклем.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'The love is beautiful.',
                                'example_target' => 'Love is beautiful.',
                            ],
                            [
                                'original' => '1. I live in the Ukraine.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. The dogs are loyal animals.',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Іменники, артиклі та кількість',
                        'items' => [
                            [
                                'label' => 'Zero Article — Нульовий артикль (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
                                'current' => false,
                            ],
                            [
                                'label' => 'Plural Nouns — Множина іменників',
                                'current' => false,
                            ],
                            [
                                'label' => 'Countable vs Uncountable — Злічувані / незлічувані',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
