<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'articles-with-geographical-names';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Articles with geographical names — артиклі з географічними назвами',
            'subtitle_html' => '<p><strong>Географічні назви</strong> в англійській мові можуть вживатися <strong>з артиклем the</strong> або <strong>без артикля</strong> залежно від типу обʼєкта. Країни, міста, континенти зазвичай без артикля, але океани, річки, гірські системи та деякі країни-союзи потребують <strong>the</strong>.</p>',
            'subtitle_text' => 'Теоретичний огляд артиклів з географічними назвами: правила вживання the з країнами, містами, океанами, річками, горами та іншими географічними обʼєктами.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Articles',
                'The',
                'Geographical Names',
                'Countries',
                'Cities',
                'Rivers',
                'Mountains',
                'Oceans',
                'Geography',
                'Grammar',
                'Theory',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти вивчиш правила вживання артикля <strong>the</strong> з <strong>географічними назвами</strong> — коли він потрібен і коли можна обійтися без нього.',
                        'rules' => [
                            [
                                'label' => 'Без THE',
                                'color' => 'emerald',
                                'text' => '<strong>Більшість країн і міст</strong> — без артикля:',
                                'example' => 'Ukraine, London, Paris, Italy',
                            ],
                            [
                                'label' => 'З THE',
                                'color' => 'blue',
                                'text' => '<strong>Океани, річки, гори</strong> — з артиклем:',
                                'example' => 'the Pacific, the Dnipro, the Alps',
                            ],
                            [
                                'label' => 'Винятки',
                                'color' => 'amber',
                                'text' => '<strong>Країни-союзи</strong> — з артиклем:',
                                'example' => 'the USA, the UK, the Netherlands',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Загальне правило',
                        'intro' => 'Артикль the з географічними назвами залежить від типу обʼєкта:',
                        'items' => [
                            ['label' => 'Без THE', 'title' => 'Країни, міста, континенти', 'subtitle' => 'Ukraine, London, Europe — більшість не потребують артикля'],
                            ['label' => 'З THE', 'title' => 'Океани, річки, гори', 'subtitle' => 'the Pacific, the Dnipro, the Alps — завжди з артиклем'],
                            ['label' => 'Винятки', 'title' => 'Союзи і множина', 'subtitle' => 'the USA, the UK, the Philippines — особливі випадки'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Країни — без артикля (більшість)',
                        'sections' => [
                            [
                                'label' => 'Більшість країн — Ø',
                                'color' => 'emerald',
                                'description' => '<strong>Більшість країн</strong> вживаються <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Ukraine, Poland, Germany, France', 'ua' => 'Україна, Польща, Німеччина, Франція'],
                                    ['en' => 'Italy, Spain, Portugal, Greece', 'ua' => 'Італія, Іспанія, Португалія, Греція'],
                                    ['en' => 'Japan, China, India, Australia', 'ua' => 'Японія, Китай, Індія, Австралія'],
                                    ['en' => 'I live in Ukraine.', 'ua' => 'Я живу в Україні.'],
                                ],
                            ],
                            [
                                'label' => 'Країни-союзи — THE',
                                'color' => 'blue',
                                'description' => '<strong>Країни-союзи</strong> або назви у <strong>множині</strong> — з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the United States (the USA)', 'ua' => 'Сполучені Штати (США)'],
                                    ['en' => 'the United Kingdom (the UK)', 'ua' => 'Сполучене Королівство (Велика Британія)'],
                                    ['en' => 'the United Arab Emirates (the UAE)', 'ua' => 'Обʼєднані Арабські Емірати (ОАЕ)'],
                                    ['en' => 'the Netherlands', 'ua' => 'Нідерланди'],
                                ],
                            ],
                            [
                                'label' => 'Чому THE?',
                                'color' => 'amber',
                                'description' => 'Артикль <strong>the</strong> потрібен, якщо:',
                                'examples' => [
                                    ['en' => '1. Назва містить "States", "Kingdom", "Republic"', 'ua' => 'the United States, the Czech Republic'],
                                    ['en' => '2. Назва у множині', 'ua' => 'the Netherlands, the Philippines'],
                                    ['en' => '3. Історична назва з Union/Federation', 'ua' => 'the Soviet Union (історична)'],
                                ],
                                'note' => '📌 Винятки: the Gambia, the Congo, the Sudan (історично).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Міста та столиці — без артикля',
                        'sections' => [
                            [
                                'label' => 'Усі міста — Ø',
                                'color' => 'emerald',
                                'description' => '<strong>Всі міста</strong> вживаються <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Kyiv, Lviv, Odesa, Kharkiv', 'ua' => 'Київ, Львів, Одеса, Харків'],
                                    ['en' => 'London, Paris, Berlin, Rome', 'ua' => 'Лондон, Париж, Берлін, Рим'],
                                    ['en' => 'New York, Los Angeles, Tokyo', 'ua' => 'Нью-Йорк, Лос-Анджелес, Токіо'],
                                    ['en' => 'I was born in Kyiv.', 'ua' => 'Я народився в Києві.'],
                                ],
                            ],
                            [
                                'label' => 'Винятки з містами',
                                'color' => 'rose',
                                'description' => 'Дуже рідкісні винятки (історично):',
                                'examples' => [
                                    ['en' => 'The Hague (Гаага) — єдине місто з the', 'ua' => 'Столиця Нідерландів'],
                                    ['en' => 'I went to The Hague.', 'ua' => 'Я їздив до Гааги.'],
                                ],
                                'note' => '📌 The Hague — практично єдиний виняток серед міст!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Континенти — без артикля',
                        'sections' => [
                            [
                                'label' => 'Всі континенти — Ø',
                                'color' => 'emerald',
                                'description' => '<strong>Континенти</strong> завжди <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Europe, Asia, Africa', 'ua' => 'Європа, Азія, Африка'],
                                    ['en' => 'North America, South America', 'ua' => 'Північна Америка, Південна Америка'],
                                    ['en' => 'Australia, Antarctica', 'ua' => 'Австралія, Антарктида'],
                                    ['en' => 'I traveled across Europe.', 'ua' => 'Я подорожував Європою.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Океани, моря, річки — з артиклем THE',
                        'sections' => [
                            [
                                'label' => 'Океани — THE',
                                'color' => 'blue',
                                'description' => '<strong>Всі океани</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Pacific Ocean', 'ua' => 'Тихий океан'],
                                    ['en' => 'the Atlantic Ocean', 'ua' => 'Атлантичний океан'],
                                    ['en' => 'the Indian Ocean', 'ua' => 'Індійський океан'],
                                    ['en' => 'the Arctic Ocean', 'ua' => 'Північний Льодовитий океан'],
                                ],
                            ],
                            [
                                'label' => 'Моря — THE',
                                'color' => 'sky',
                                'description' => '<strong>Всі моря</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Black Sea', 'ua' => 'Чорне море'],
                                    ['en' => 'the Mediterranean Sea', 'ua' => 'Середземне море'],
                                    ['en' => 'the Baltic Sea', 'ua' => 'Балтійське море'],
                                    ['en' => 'the Red Sea', 'ua' => 'Червоне море'],
                                ],
                            ],
                            [
                                'label' => 'Річки — THE',
                                'color' => 'purple',
                                'description' => '<strong>Всі річки</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Dnipro (the Dnieper)', 'ua' => 'Дніпро'],
                                    ['en' => 'the Thames', 'ua' => 'Темза'],
                                    ['en' => 'the Danube', 'ua' => 'Дунай'],
                                    ['en' => 'the Amazon, the Nile', 'ua' => 'Амазонка, Ніл'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Гори — залежить від форми',
                        'sections' => [
                            [
                                'label' => 'Окремі вершини — Ø',
                                'color' => 'emerald',
                                'description' => '<strong>Окремі гори</strong> (з Mount, Peak) — <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Mount Everest', 'ua' => 'Гора Еверест'],
                                    ['en' => 'Mount Kilimanjaro', 'ua' => 'Гора Кіліманджаро'],
                                    ['en' => 'Hoverla (найвища в Україні)', 'ua' => 'Говерла'],
                                    ['en' => 'I climbed Mount Everest.', 'ua' => 'Я піднявся на Еверест.'],
                                ],
                            ],
                            [
                                'label' => 'Гірські системи — THE',
                                'color' => 'blue',
                                'description' => '<strong>Гірські системи</strong> (множина гір) — з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Alps', 'ua' => 'Альпи'],
                                    ['en' => 'the Carpathians', 'ua' => 'Карпати'],
                                    ['en' => 'the Himalayas', 'ua' => 'Гімалаї'],
                                    ['en' => 'the Rockies (the Rocky Mountains)', 'ua' => 'Скелясті гори'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Озера — залежить від форми',
                        'sections' => [
                            [
                                'label' => 'Lake + назва — Ø',
                                'color' => 'emerald',
                                'description' => 'Якщо є слово <strong>Lake</strong> перед назвою — <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Lake Baikal', 'ua' => 'Озеро Байкал'],
                                    ['en' => 'Lake Superior', 'ua' => 'Озеро Верхнє'],
                                    ['en' => 'Lake Geneva', 'ua' => 'Женевське озеро'],
                                    ['en' => 'I visited Lake Baikal.', 'ua' => 'Я відвідав озеро Байкал.'],
                                ],
                            ],
                            [
                                'label' => 'Назва без Lake — THE',
                                'color' => 'blue',
                                'description' => 'Якщо <strong>немає слова Lake</strong> — з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Baikal (без слова Lake)', 'ua' => 'Байкал'],
                                    ['en' => 'the Great Lakes', 'ua' => 'Великі озера (група)'],
                                ],
                                'note' => '📌 Група озер — завжди з the.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Острови — залежить від кількості',
                        'sections' => [
                            [
                                'label' => 'Окремий острів — Ø',
                                'color' => 'emerald',
                                'description' => '<strong>Окремі острови</strong> — <strong>без артикля</strong>.',
                                'examples' => [
                                    ['en' => 'Cyprus, Sicily, Crete', 'ua' => 'Кіпр, Сицилія, Крит'],
                                    ['en' => 'Madagascar, Jamaica', 'ua' => 'Мадагаскар, Ямайка'],
                                    ['en' => 'I went to Cyprus.', 'ua' => 'Я їздив на Кіпр.'],
                                ],
                            ],
                            [
                                'label' => 'Групи островів — THE',
                                'color' => 'blue',
                                'description' => '<strong>Групи островів</strong> (архіпелаги) — з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Canary Islands', 'ua' => 'Канарські острови'],
                                    ['en' => 'the British Isles', 'ua' => 'Британські острови'],
                                    ['en' => 'the Bahamas', 'ua' => 'Багамські острови'],
                                    ['en' => 'the Philippines', 'ua' => 'Філіппіни (і країна, і острови)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Пустелі, канали, затоки — з THE',
                        'sections' => [
                            [
                                'label' => 'Пустелі — THE',
                                'color' => 'amber',
                                'description' => '<strong>Всі пустелі</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Sahara (Desert)', 'ua' => 'Сахара'],
                                    ['en' => 'the Gobi Desert', 'ua' => 'Пустеля Гобі'],
                                    ['en' => 'the Kalahari', 'ua' => 'Калахарі'],
                                ],
                            ],
                            [
                                'label' => 'Канали — THE',
                                'color' => 'sky',
                                'description' => '<strong>Канали</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Suez Canal', 'ua' => 'Суецький канал'],
                                    ['en' => 'the Panama Canal', 'ua' => 'Панамський канал'],
                                    ['en' => 'the English Channel', 'ua' => 'Ла-Манш (протока)'],
                                ],
                            ],
                            [
                                'label' => 'Затоки — THE',
                                'color' => 'purple',
                                'description' => '<strong>Затоки</strong> вживаються з артиклем <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'the Persian Gulf', 'ua' => 'Перська затока'],
                                    ['en' => 'the Gulf of Mexico', 'ua' => 'Мексиканська затока'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Порівняльна таблиця',
                        'intro' => 'Швидкий довідник: коли потрібен артикль the:',
                        'rows' => [
                            [
                                'en' => 'Countries (most)',
                                'ua' => 'Країни (більшість)',
                                'note' => 'Ø: Ukraine, France, Japan',
                            ],
                            [
                                'en' => 'Countries (unions)',
                                'ua' => 'Країни (союзи)',
                                'note' => 'THE: the USA, the UK, the Netherlands',
                            ],
                            [
                                'en' => 'Cities',
                                'ua' => 'Міста',
                                'note' => 'Ø: Kyiv, London, Paris (except: The Hague)',
                            ],
                            [
                                'en' => 'Continents',
                                'ua' => 'Континенти',
                                'note' => 'Ø: Europe, Asia, Africa',
                            ],
                            [
                                'en' => 'Oceans, seas, rivers',
                                'ua' => 'Океани, моря, річки',
                                'note' => 'THE: the Pacific, the Dnipro, the Black Sea',
                            ],
                            [
                                'en' => 'Mountains (single)',
                                'ua' => 'Гори (окремі)',
                                'note' => 'Ø: Mount Everest, Hoverla',
                            ],
                            [
                                'en' => 'Mountains (ranges)',
                                'ua' => 'Гірські системи',
                                'note' => 'THE: the Alps, the Carpathians',
                            ],
                            [
                                'en' => 'Lakes (with Lake)',
                                'ua' => 'Озера (з Lake)',
                                'note' => 'Ø: Lake Baikal',
                            ],
                            [
                                'en' => 'Islands (single)',
                                'ua' => 'Острови (окремі)',
                                'note' => 'Ø: Cyprus, Sicily',
                            ],
                            [
                                'en' => 'Islands (groups)',
                                'ua' => 'Групи островів',
                                'note' => 'THE: the Canary Islands, the Bahamas',
                            ],
                        ],
                        'warning' => '📌 Загальне правило: якщо назва у <strong>множині</strong> або містить <strong>союз/республіку</strong> — використовуй <strong>the</strong>!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Артикль перед Україною.',
                                'wrong' => 'I live in the Ukraine.',
                                'right' => '✅ <span class="font-mono">I live in Ukraine.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Без артикля перед річкою.',
                                'wrong' => 'Dnipro is a long river.',
                                'right' => '✅ <span class="font-mono">The Dnipro is a long river.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Артикль перед Mount Everest.',
                                'wrong' => 'I climbed the Mount Everest.',
                                'right' => '✅ <span class="font-mono">I climbed Mount Everest.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Забути the перед USA.',
                                'wrong' => 'I visited USA.',
                                'right' => '✅ <span class="font-mono">I visited the USA.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '12. Короткий конспект',
                        'items' => [
                            '<strong>Без артикля</strong>: більшість країн, усі міста, континенти, окремі гори та острови.',
                            '<strong>З артиклем the</strong>: океани, моря, річки, гірські системи, групи островів, пустелі.',
                            '<strong>Країни з the</strong>: союзи (the USA, the UK), множина (the Netherlands, the Philippines).',
                            '<strong>Єдине місто з the</strong>: The Hague (Гаага).',
                            '<strong>Mount + назва</strong> — без артикля: Mount Everest.',
                            '<strong>Lake + назва</strong> — без артикля: Lake Baikal.',
                            '<strong>Гірські системи</strong> — з the: the Alps, the Carpathians.',
                            '<strong>Історичні винятки</strong>: the Gambia, the Congo, the Sudan.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '13. Практика',
                        'select_title' => 'Вправа 1. Чи потрібен артикль?',
                        'select_intro' => 'Визначте, чи потрібен артикль the з цими назвами.',
                        'selects' => [
                            ['label' => '___ Pacific Ocean (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => '___ London (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => '___ Alps (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                            ['label' => '___ USA (Ø / the)', 'prompt' => 'Потрібен артикль?'],
                        ],
                        'options' => ['Ø (без артикля)', 'the'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши Ø (без артикля) або the.',
                        'inputs' => [
                            ['before' => 'I live in ___ Ukraine.', 'after' => '→'],
                            ['before' => '___ Dnipro is a beautiful river.', 'after' => '→'],
                            ['before' => 'I climbed ___ Mount Everest.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку з артиклем.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I live in the Ukraine.',
                                'example_target' => 'I live in Ukraine.',
                            ],
                            [
                                'original' => '1. Thames is a long river.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. I visited the Mount Everest.',
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
                                'label' => 'Articles with geographical names (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
                                'current' => false,
                            ],
                            [
                                'label' => 'Zero Article — Нульовий артикль',
                                'current' => false,
                            ],
                            [
                                'label' => 'No / None / Neither / Either',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
