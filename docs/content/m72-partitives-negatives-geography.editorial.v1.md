# M72 · Редакційна, component і task карта 1.0.0

База `2bcd468b25af3c44dcf34ccee0e012f3f683335d`. Тільки Partitives, No / None / Neither / Either та Articles with Geographical Names у чинному native-дизайні.

## Межі

42 continuing structured slots (14/13/15), три existing practice UUID на source indices 12/11/13, technical NULL збережені. Жодного нового Page, metadata transition, practice block, view, CSS, font чи grader. Raw Page.title та чинні H1 залишаються незмінними. Курси, standalone SSR/JSON і progress захищені окремо.

## Виправлення

- Partitives: одиниці, частини, порції, тара й міри; після of можливі mass і plural contents. Cup/glass за посудом, не температурою; carton не лише рідина; piece не універсальне; advice/information/paper перекладені за змістом; a bit of додано. Повні моделі з determiners, of, loaves, питаннями, запереченнями та короткими відповідями. Serving a water не оголошене універсально неграматичним.
- No/None/Neither/Either: синтаксис, визначений референт, позитивний вибір і нульова кількість розмежовані; any для ширшого позитивного вибору не замінено на none. None agreement залежить від референта, а не лише формальності. Neither…nor нормативне; not…either та Neither do/did/can I мають явні контексти. Not all не дорівнює none; дозвіл не є здійсненою дією.
- Geography: звична форма власної назви, не алгоритми «усі міста/гори/країни». Хребет і Mount, Lake і коротка назва, місто Dnipro і річка the Dnipro розмежовані. Amsterdam — столиця, уряд/парламент у The Hague. English Channel — природна протока. The Gambia не віднесено лише до історичних форм; старий неперевірений каталог Congo/Sudan усунуто.

## Практика OLD → AFTER

27 corrected-retained graded positions + 12 нових = 39 own tasks; 3 ungraded demos окремо. На урок: 4 selects, 2 meaning choices, 5 inputs і 2 graded rephrases. Старі IDs/source_index були відсутні й не додані. Geography три старі article inputs у тих самих індексах тепер вимагають повні речення; два порожні ключі усунуто. Без артикля — явна непорожня select-відповідь, не слово англійського речення; empty/whitespace/reset не оцінюються як correct.

6 NEW full-sentence inputs підтримують ручний і токенний спосіб; разом із трьома corrected-retained Geography маємо 9 full-sentence paths. Partitives фінальні here./tea. надані в даних, бо native append лише склеює токени. Canonical Neither у old input2 збережено, додано один змістовно обґрунтований alias None для двох явно відсутніх осіб, із власним UA feedback. Не створено глобального fuzzy matcher.

В unchanged factory перед freeze перевірено 39 tasks / 36 visible options / 40 accepted values, 525 raw state cases; відтворено 11 BEFORE defects. Це не browser PASS. На реальних BEFORE сторінках окремо спостерігалися ordinal options/key mismatch, двозначні короткі умови, empty Geography keys та tag-fallback quick practice.

## Basic / detail

Явні finite рішення: 29 Partitives + 21 Negatives + 21 Geography, усі visible-basic. Короткі потрібні пояснення, переклади й застереження не ховаються. 0 details; не застосовано runtime word-count threshold і не додано кнопок заради квоти.

## Component map

| Owner | Index | Native type | UUID | Level |
|---|---:|---|---|---|
| NounsArticlesQuantityPartitivesTheorySeeder | 0 | hero | 6d3aa49c-a8b5-5794-a0be-e519229876bf | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 1 | forms-grid | 8a7fcc2b-1dd9-5634-b500-bb31759b5b11 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 2 | usage-panels | 9976177f-7da2-5dac-b525-28ea0fbe19f3 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 3 | usage-panels | 08a7ad32-9fbe-50f7-933c-887f223e3cca | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 4 | usage-panels | 66e11e66-30f0-5fdd-bb17-265d1bc18b66 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 5 | usage-panels | 71906dee-819d-581b-9552-c1da39c22f5a | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 6 | usage-panels | a5042d56-2f3f-569c-9a49-53639408f278 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 7 | usage-panels | 782feb8c-eeba-5ec5-a5a9-6070096e7d4e | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 8 | usage-panels | b6fe72e6-dda3-5592-adb7-31c49f179292 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 9 | comparison-table | b1c26191-0cd1-5fa4-b01c-1c64f4511a9a | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 10 | mistakes-grid | 43cc2fdb-f8b4-52e6-b229-5b17cb0a0233 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 11 | summary-list | e4d3c6b1-cc4c-5359-817b-8df2b617a990 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 12 | practice-set | 54ce9ed8-8e28-574c-9485-a780a04f9a37 | NULL |
| NounsArticlesQuantityPartitivesTheorySeeder | 13 | navigation-chips | 77e81834-281a-5281-a818-3614342e381d | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 0 | hero | 7c6b1128-945c-5c5b-9852-e44947770b03 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 1 | forms-grid | 6896a08d-8751-54e4-a046-e0e3351a4bdc | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 2 | usage-panels | 7b8c11cc-892c-541e-8598-6f24e1797369 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 3 | usage-panels | b87b42ec-5334-55c6-8a8d-a14ebc404982 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 4 | usage-panels | 833cbcf1-b2ee-54fa-9620-965b3c9cefdb | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 5 | usage-panels | a03ffc27-6b01-5b16-b93a-9684d5f6ff9e | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 6 | usage-panels | 4ef87ba3-6b24-5369-b40c-8684d244770e | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 7 | usage-panels | 9b396468-6882-553c-9253-c866f5078ef8 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 8 | comparison-table | 0f2535e2-679c-5bb8-961f-fcd18a0156c9 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 9 | mistakes-grid | fc7686aa-d934-56fe-a9f1-e600972e6abc | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 10 | summary-list | adbda223-5490-5966-96ff-3dde7a115e03 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 11 | practice-set | b2fd69af-1520-5e69-a5e9-6d315246ac01 | NULL |
| NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | 12 | navigation-chips | 66aa3159-288a-57be-a57f-44398422aa97 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 0 | hero | d460c2cb-d0b5-5213-8bf1-ef42c1c171b6 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 1 | forms-grid | f7840d89-7798-55b3-a31e-ec10f0be29d9 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 2 | usage-panels | c8666220-56ce-5617-bb09-11369fbbeb85 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 3 | usage-panels | 8ebbf117-812d-5eaf-8389-ec3b48aedacd | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 4 | usage-panels | 0b217edf-02c4-59ec-9c25-873d9a0c2690 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 5 | usage-panels | 7a8887fe-a653-58c3-aba3-b21f293729b5 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 6 | usage-panels | 768e4618-0345-5cd0-9ba2-81c5f8956a8c | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 7 | usage-panels | 3e9714d4-26ba-595c-bf02-55ae38d57a31 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 8 | usage-panels | 4c733793-cfe3-5957-b50b-9aa38b01473e | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 9 | usage-panels | ecac9845-20d3-5a9e-927c-0486f652b9cb | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 10 | comparison-table | 0c8c4c89-7f20-59e9-94ca-87540fa21522 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 11 | mistakes-grid | 8162cdb6-e63b-5fa8-9426-c01a2c15cdf7 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 12 | summary-list | 28b93ce0-11f3-5e5d-8599-ca8b150da4a2 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 13 | practice-set | 63edb425-3ba4-59e1-bfcd-aa676eac86a1 | NULL |
| NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder | 14 | navigation-chips | 376de187-b256-5f3c-b4db-c7d9462ffe97 | NULL |

Повний OLD/AFTER task map, editorial map і джерела — у versioned JSON. Нові приклади й вправи авторські; не копії publisher exercise keys. Прямі 403/timeout/cache miss та indexed publisher body позначені окремо, без удаваного прямого читання. Коментарі не є нормативними джерелами.

## Конструктор

15 existing type4 questions (5 на тему), 0 нових SQL question/option/answer/link rows. Семантично розглянуто 36 кандидатів A2/B1/A2; решта 180 у NULL-filter pools — лише scope-excluded, без заяви про семантичний аудит. Змінено тільки показані UA умови exact-bound clones, повні оригінальні snapshots і token case залишені. У двох geography reserves початкове the вже нормалізується native до The; це не defect. B1 none candidates мають внутрішнє None у незмінному embedded шляху: їх не виправляли мовчки; None перевіряється власними вправами.

## Ризик ширини

75 title/formula fields × 4 локальні fallback fonts = 300 вимірів; 0 unbreakable-token risks за 194px. Це не DOM/layout acceptance і не твердження про завантаження webfont. Максимальний serialized body — 15723 bytes із 65535. Live viewport/theme, footer та інтерактивні сценарії мають окремі докази.
