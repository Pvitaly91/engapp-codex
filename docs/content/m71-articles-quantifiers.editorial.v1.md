# M71 · Редакційна, component і task карта 1.0.0

База `1291b576b47dc28e5ba9e0c58faa052f6e49460e`. Лише три UK Page owners у imennyky-artykli-ta-kilkist. Дизайн і grader не змінюються.

## Збережено

39 continuing source slots; три наявні practice blocks з незмінними UUID, source indices 10/12/11, technical NULL, columns/order/tags/anchors. Raw Page.title та перші strong/H1 збережені. Корисні правила a/an за звуком; неозначений/означений reference; звичайні zero patterns; count/mass quantifiers, мало/кілька/трохи.

## Виправлено й уточнено

- Articles: a/an — дві форми одного артикля, не три незалежні категорії. Наступний звук усієї групи, silent h, /j/, MBA задано як читання літер. Перша згадка та of/that/which не є автоматичними перемикачами. Професійний noun відділено від adjective; the має plural/mass uses; my/this зберігаються. Групи людей та інструменти обмежено перевіреними моделями.
- Zero: Ø — символ теорії, не порожня відповідь. Немає «всі без винятку» або «категорія завжди без артикля». Порція не вимагає the; окремий епізод може мати неозначені plural/mass. Школа/ліжко залежать від мети; hospital має UK/US межу; by — модель пересування, не будь-який прийменниковий додаток. Meals/days/seasons мають контрасти. Видалено хибне без Lake → the та приклад про поточного політика.
- Quantifiers: many/much у ствердженні — питання стилю, не загальна помилка. A lot of/lots of можливі в різних типах речень; of не додається механічно. Few/little підкреслюють малість, a few/a little — наявність; достатність та бажаність визначають факти. Few time — noun-type помилка. Not many/much перекладено як небагато.

## Нове й усунені повтори

Додано контекст референта, узгодження значень трьох уроків з M70, короткі питання/заперечення, дві semantic choices та два нові full-sentence manual/token inputs на урок. Повтори огляду скорочено до карти, не переписано сусідні Zero/Countability/SomeAny теми. Усі необхідні пояснення видимі: 0 details за явними finite point decisions, без runtime word threshold.

## Реально відтворені BEFORE дефекти

Articles: введене за інструкцією a → «Неправильно. Правильна відповідь: a university». Zero: натискання Ø (без артикля) → «Неправильно. Правильна відповідь: Ø»; два empty keys не входили в оцінювання, група мала score 0 з 1. Quantifiers: Перший варіант → wrong/much; a lot of у There are ___ people here → wrong/many. Окремий native factory run відтворив 14 source/engine розбіжностей; actual UI відокремлено від JSON-аудиту.

## Практика OLD → AFTER

27 corrected-retained graded positions + 12 new = 39 own tasks; 3 ungraded demos окремо. На кожному уроці: 4 selects / 2 meaning choices / 5 inputs / 2 graded rephrases. Нуль старих graded controls вилучено; rephrase лишається видимим. Старі IDs/source_index відсутні й не додані.

Articles short inputs прямо просять повну групу. Zero old input0 перенесено з двох невизначених пропусків до повного I go to work by bus у тому самому input index; old input1 приймає явне «Без артикля», а untouched/whitespace/reset не дають бал. Повні англійські речення не містять Ø або цієї позначки. Quantifiers options — фактичні much/many тощо; aliases many/a lot of/lots of обмежені явним задумом.

Всього 7 full-sentence paths (6 нових + 1 corrected-retained Zero); 10 native token banks включають також 3 Articles short phrases. 39 tasks / 44 visible options / 46 accepted values перевірено незмінним factory. Поля prompts, які native select не показує, прибрано; умови зі stimuli знаходяться в supported labels. UK rephrase інструкції відділено в supported intro від чистого EN original.

## Component map

| Owner | Index | Native type | UUID | Level |
|---|---:|---|---|---|
| NounsArticlesQuantityArticlesTheorySeeder | 0 | hero | 21d01241-5e46-5748-bf81-9a8f7520e574 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 1 | forms-grid | a5edffa6-35d3-5a5b-86e1-fb4cc5ae380d | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 2 | usage-panels | c9213c55-9aa5-530e-940b-7e0b8bf49462 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 3 | usage-panels | 30f1dd0f-5c8e-5b00-a725-a96b1d2d1e40 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 4 | usage-panels | bf9ba966-3a73-5f7f-8ac3-ddc072cb0f9b | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 5 | usage-panels | c4fe796c-c12c-50e5-b087-591a8d8126fb | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 6 | comparison-table | e8a4dd39-6c88-57a6-bb0f-adc9a5a89dbd | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 7 | usage-panels | f54e82d1-ed4d-5948-83df-491dfdbadea4 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 8 | mistakes-grid | 7992a0bf-9a6d-5e43-bad9-dca7aec6a2d6 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 9 | summary-list | 7769e9d1-53c2-54ab-9c0e-5c9ad944c554 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 10 | practice-set | f1edde68-82a6-56d6-8aed-7b5dc7b969c0 | NULL |
| NounsArticlesQuantityArticlesTheorySeeder | 11 | navigation-chips | b569d640-c50a-5d14-bb26-b6eb5869e40a | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 0 | hero | a861f541-0c55-5721-a254-3e15e08bc58b | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 1 | forms-grid | d550dfd5-2abb-5d6d-9ccc-5f2d104ac9bc | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 2 | usage-panels | 6833ffb9-7e98-5a70-9008-707b137b04aa | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 3 | usage-panels | c3d09c9a-989d-5273-ae5d-4f6491724805 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 4 | usage-panels | 8a60bad7-5445-5361-92fd-a2477fcc4bb8 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 5 | usage-panels | 1c731e08-a6ad-5cb5-aaa2-1b13341fe76d | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 6 | usage-panels | 7f5471fb-c5b1-59e0-b441-3610d8dd3237 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 7 | usage-panels | 443df592-0dec-5c53-adf2-a8c897e9cfd0 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 8 | usage-panels | e859cbc4-54dc-5b49-8cd7-24877e826762 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 9 | comparison-table | 79e3064d-85cc-526f-9166-2f36635136ca | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 10 | mistakes-grid | e8c7f77a-18df-55ea-8d38-afd5610a2e4c | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 11 | summary-list | cd4376e5-c153-57c4-b66b-4f06453e5985 | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 12 | practice-set | e0348abd-f7ce-509e-9329-462c974db20a | NULL |
| NounsArticlesQuantityZeroArticleTheorySeeder | 13 | navigation-chips | 0b238767-c86f-53e5-88ee-e724b8d41de5 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 0 | hero | a8978834-8a98-57f7-a39f-a571bcaf9e06 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 1 | forms-grid | 2b172250-f1bb-51be-b79c-9fbcec5d3e34 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 2 | usage-panels | 1de000f1-8b0a-53d4-8c3a-6b09e5da79cb | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 3 | usage-panels | cf3f2e68-8f6d-5ea0-8779-7137ec5cfddb | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 4 | usage-panels | 2ea94831-edd2-5f4d-9ec4-772cbfeb999c | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 5 | usage-panels | 86191934-8a35-5e24-b859-4648d746d951 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 6 | usage-panels | e69533bc-184b-5c5e-b4db-e48076384fbb | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 7 | usage-panels | a87c2106-99e3-5c49-a8b8-a163995d7cc2 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 8 | comparison-table | e59ba16b-5660-598b-b9f1-7c5b2bd5b5b5 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 9 | mistakes-grid | 1e4cf9f1-3c04-5cc8-a007-157c909fbf41 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 10 | summary-list | 2922a6f5-e819-5cff-9421-e21896fc734d | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 11 | practice-set | 44661475-7c1a-5267-b9ba-261101001cc9 | NULL |
| NounsArticlesQuantityQuantifiersTheorySeeder | 12 | navigation-chips | b0f90342-1af0-5276-bafc-13ac6d3bd84d | NULL |

Повний OLD/AFTER кожного task, body hashes та metadata — у component/task JSON. Форматна передача old Zero input0 не створює нового task або нового UUID.

## Джерела й межі перевірки

Нові приклади й exercises авторські. Agent review не названо human review.

| Джерело | Фактичний доступ | Перевірено |
|---|---|---|
| [Джерело](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/indefinite-article) | direct editorial main body; direct: success | Indefinite singular reference, sound rather than spelling; main-body indefinite relative-clause example. Exercises not copied; learner comments excluded. |
| [Джерело](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2-grammar/articles-the-or-no-article) | direct editorial main body; direct: success | Common institutional phrases and ordinary place-name patterns. Learner comments excluded. |
| [Джерело](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/definite-article) | direct editorial main body; direct: success (independent agent read) | Identifiable reference, established adjective groups and instrument patterns; not an unrestricted productivity rule. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/a-an) | indexed primary editorial body; direct: 403 | Whole-phrase next sound, article/determiner interaction, definite plural and mass uses; direct fetch not claimed. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/much-many-a-lot-of-lots-of-quantifiers) | indexed primary editorial body; direct: 403 | Count/mass, register not grammatical ban, all sentence types for a lot of, noun omission and of with determined groups. |
| [Джерело](https://dictionary.cambridge.org/uk/grammar/british-grammar/few-a-few-little-a-little) | requested address unavailable; direct: 403 | No successful direct read or redirect claimed; actual indexed article is recorded separately. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/little-a-little-few-a-few%3A) | indexed primary editorial body; direct: not attempted at this distinct address | Small quantity/presence versus scarcity focus, count/mass and of; not an automatic task-sufficiency threshold. |
| [Джерело](https://dictionaryblog.cambridge.org/2015/08/19/a-an-and-the-how-to-use-articles-in-english/) | direct editorial main body and indexed body; direct: success (independent agent read) | Contextual reference and British/American hospital contrast. Comments not authority. |
| [Джерело](https://dictionaryblog.cambridge.org/2017/12/13/how-to-use-articles-another-look-1/) | direct editorial main body; direct: success (independent agent read) | Letter-by-letter MBA pronunciation and sense-specific countability. |
| [Джерело](https://www.nps.gov/apis/planyourvisit/its-not-just-another-lake-up-north.htm) | direct primary geographic usage; direct: success (independent agent read) | Lake Superior and bare Superior both occur; removing Lake does not mechanically require the. Lesson keeps the ordinary full name. |
| [Джерело](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) | direct editorial body; direct: success | Useful original people-first lessons, not word quotas or indexing guarantees. No Search Console action. |

403 не є прямим читанням; indexed body не є redirect або learner comment. Перевірено 72 title/formula fields × 4 локальні fallback fonts: 288 вимірів, 0 unbreakable-token risks за 194px; це не browser layout PASS. Максимальний body 15946 bytes із 65535. Реальне приймання обліковується окремо.
