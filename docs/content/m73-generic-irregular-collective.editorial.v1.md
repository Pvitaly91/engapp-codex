# M73 · Редакційна, component і task карта 1.0.0

База `cb55b329827e52a56cb6d41edb21e70768cf86f4`. Тільки Generic Reference, Irregular Plurals і Collective Nouns у чинній стандартній в’юсі.

## Межі й metadata

26 continuing structured slots (13/7/6) лишаються на своїх індексах, у своїх колонках і з чинними levels, включно з A1/A1–A2 та NULL. Generic зберігає practice UUID source11/NULL; нові Irregular source7/A2 і Collective source6/B1 додаються після всіх старих конфігурацій, не витісняючи navigation. 0 metadata transitions. Raw Page.title, перший strong subtitle/H1, slugs і category ancestry не змінюються.

## Мовні уточнення

- Generic: характеристика типу, kind-reference і конкретна референція розмежовані; the + singular не обмежено дуже формальним історичним стилем. Generic не означає all/every. The + adjective називає доречну групу людей, не довільний прикметник чи буквально всіх у світі. Національності зі звичайною множиною можуть мати the; старі оцінні стереотипи замінено явно заданими нейтральними навчальними ситуаціями. Only/main/same не є безумовним алгоритмом артикля; наукові й правові припущення не видаються за факти.
- Irregular: збережено основні irregular pairs, додано ox/oxen; 11 рядків таблиці мають справжній український переклад. People — звичайна множина person, але formal persons не заборонено. Fish/fishes не зведено до жорсткого правила; animal mouse і device mouse розрізнено. Determiner, число й дієслово узгоджено у повних реченнях.
- Collective: група, члени й кількість груп — різні поняття. Британське plural agreement збережено; singular unit-model не оголошено єдиною нормою. Police, staff/member, company і group of мають явні межі значення. Коректне team is wearing different shirts перенесено до видимого variant пояснення, а не залишено помилкою. Mistakes перевіряють однозначні форми.

## Практика OLD → AFTER

9 corrected-retained Generic learning positions + 16 нових = 25 own graded tasks; 1 Generic demo окремо. Generic: 4 selects, 2 choices, 5 inputs, 2 graded rephrases. Irregular і Collective: по 2 selects, 2 choices, 2 full-sentence inputs. Кожен новий input має українську умову в підтриманому after-полі, ручний і токенний спосіб. Це не заміна окремого widget.

Actual Generic BEFORE adapter відхилив усі чотири видимі ordinal choices (0/4). Порожній третій input був виключений з denominator (0/2), а не давав безкоштовний бал. AFTER literal options відповідають keys; третій короткий input вимагає явне «Без артикля», не порожній рядок або Ø-token.

Для These children are not hungry приймається також aren't. Для явно заданої моделі Collective приймаються The team are packing their bags. і The team is packing its bags.; змішана is/their не виконує саме цю умову, але не оголошується універсально неграматичною. Raw lower-trim feedback aliases мають plain text; глобальний normalizer і grader незмінні.

Unchanged factory pre-freeze: 25 tasks / 32 visible options / 27 accepted values, 6 manual + 6 token paths, empty/whitespace/reset/retry/multiplicity; це не live browser acceptance.

## Basic / detail та компоненти

23 Generic + 8 Irregular aggregate records + 15 Collective = 46 explicit visible-basic decision records; деякі записи Irregular охоплюють явно названу групу пунктів. Це не 46 окремих кнопок. Усі потрібні пояснення, переклади й короткі застереження видимі; 0 details. Немає word threshold, квоти кнопок чи m73 CSS. Чотири авторські usage accents purple спроєктовано на чинний supported blue finite-даними; hero accent не змінено.

| Owner | Index | Native type | UUID | Level | Continuing |
|---|---:|---|---|---|---|
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 0 | hero | f427b081-1338-598c-84b2-f34da5bd3df6 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 1 | forms-grid | 70437bb1-0689-5d51-bd54-322efb51bad6 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 2 | usage-panels | 3db88ab3-cac8-53db-bb6c-146bce5d8216 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 3 | usage-panels | c644325d-d52c-559d-a1bb-9ed9722f9fb4 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 4 | usage-panels | 7449054f-f059-56a0-b957-449df2f152e7 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 5 | usage-panels | f0227efa-0a6a-525f-a5f0-fa3a96771c96 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 6 | usage-panels | e7635f1b-b1a9-5ec2-be91-49c90cd1752f | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 7 | usage-panels | 0f1a5b21-da69-59dc-9f39-e6aa055b1a62 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 8 | comparison-table | 31f56963-3074-5a75-b5a2-f988c50b7a4c | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 9 | mistakes-grid | bbe24bdf-bf20-5eaf-83e2-3290c04879f6 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 10 | summary-list | f9e8db4b-d22f-58de-bc00-38bf53cdaa4e | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 11 | practice-set | 4ec54834-db1f-55f8-983a-e6537527e477 | NULL | yes |
| NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder | 12 | navigation-chips | c2560084-07f6-5b72-9a22-673e207f4f15 | NULL | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 0 | hero | d4f15f70-7e6a-52f3-a78c-7354dfd2e5b7 | A1–A2 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 1 | forms-grid | 8d03cf85-866a-58c8-b9cc-791cf526e176 | A1 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 2 | usage-panels | 69d0b542-a253-53a8-8157-2bbf2754ae77 | A1–A2 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 3 | comparison-table | 41cfbc1e-1812-5619-9171-3bde6098d994 | A1–A2 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 4 | mistakes-grid | c4d8d727-971b-520b-a552-3af9b08d7c2f | A1 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 5 | summary-list | bf471bc7-ede5-5631-9559-ebaaa1b8f92e | A1–A2 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 6 | navigation-chips | 187ca366-12a7-58d1-9633-51b593de6b1f | A1–A2 | yes |
| NounsArticlesQuantityIrregularPluralsTheorySeeder | 7 | practice-set | 0a415e02-446b-58ac-b3a8-8bd12cec769f | A2 | new |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 0 | hero | b95128a9-8580-5dc3-a54b-2370b28604d0 | B1 | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 1 | forms-grid | 8434ae31-1c67-5f25-a52d-b5c63ce55da8 | B1 | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 2 | usage-panels | a1f89a7c-e422-5be8-b0f0-e95043906f63 | B1 | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 3 | mistakes-grid | 76c8ae80-da3f-5ccd-aa28-c136e2bb0205 | B1 | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 4 | summary-list | 4c1c3d7b-929f-579e-aaed-f4bba05f0596 | B1 | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 5 | navigation-chips | 67107952-9da5-575b-98b4-ca65bca59403 | NULL | yes |
| NounsArticlesQuantityCollectiveNounsTheorySeeder | 6 | practice-set | da76d650-7531-58f0-afca-0c6187764073 | B1 | new |

## Конструктор і межі доказів

15 existing type4 questions, 5 на тему. Actual pools: Generic NULL/all-level72, Irregular exactA2/12, Collective exactB1/12. Семантично розглянуто 36 кандидатів; решта 60 Generic — лише scope-excluded. 0 SQL question/option/answer/link/hint writes. Exact-bound clone змінює тільки українську умову, не original model. Source key → ordered tokens → native formatter offline показали 30 правильних рядків. Старий M72 32781 і Collective reserves 32633/32636 з дефектними internal capitals виключені, не виправляються рушієм. Live DOM після кожного Check, 5/5, Next і completion мають окремі докази.

## Перевірка ширини

62 title/formula fields × 4 локальні fallback fonts = 248 вимірів; 0 unbreakable-token risks за консервативних 194px для 320px viewport. Це не browser layout і не доказ завантаження Manrope/Archivo. Максимальний body19208/65535 bytes. Live18states — окреме приймання.

Повні OLD/AFTER maps і actual source-access records зберігаються в JSON. Direct body, indexed body, 403/tool errors, comments і editorial replies не змішуються; учнівські коментарі не є нормативним правилом. Нові приклади авторські, не копії publisher exercise banks.
