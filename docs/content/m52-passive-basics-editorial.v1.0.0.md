# M52 — редакційне BEFORE → AFTER зіставлення редакції 1.0.0

Це редакційний журнал підготовки release 1.0.0 після main independent review усіх learning blocks/details і точного existing-bank складу. Freeze, guarded apply та live browser acceptance фіксуються окремо; тут їх не оголошено виконаними. База — `0e2ed40c438400e2f2a3992314220fdf206db9a7`; лише три задані UK owners. Читано повністю точні три definitions разом із body JSON; активні definitions, PHP, views, CSS, JS, БД і старі question banks цим редакційним підзавданням не змінювалися.

## Ідентичність, slots і сумісність

| Owner | Точний slug | Display / technical level | Continuing source slots | Новий append |
|---|---|---|---|---|
| `PassiveVoiceFormationRulesTheorySeeder` | `theory-passive-voice-formation-rules` | A2 / A2 | 0–8, дев’ять | practice після source navigation |
| `PassiveVoiceNegativesQuestionsTheorySeeder` | `theory-passive-voice-negatives-questions` | B1 / B1 | 0–10, одинадцять | practice після source navigation |
| `PassiveVoiceModalVerbsTheorySeeder` | `theory-passive-voice-modal-verbs` | B1 / B1 | 0–10, одинадцять | practice після source navigation |

Схема master відповідає незміненому definition serializer M49: `lessons[]`, точні identity/locale/slug/ancestry/levels/subtitles, `blocks[{key, existing_source_index, type, column, level?, data}]`, `details[{key,title,owner_block_key,owner_pointer,fragment:{type:'usage-panels',data:{sections}}}]`. Тут не передбачається старе число шість slots. У continuing blocks змінюється тільки learning data; type/column/level та index збережені. Відсутність source practice підтверджена в усіх трьох definitions; практика append потрібна саме для цих трьох, не як глобальне припущення про інші пасивні сторінки. Footer column відображається окремо: append після navigation не є дозволом пересунути continuing UUID/index.

Leading strong зберігає main-confirmed live BEFORE H1: «Пасивний стан (Passive Voice)», «Negatives & Questions in Passive», «Пасив з модальними дієсловами». Raw двомовні Page titles не замінено цими назвами і не змінено. Subtitle описує новий зміст у підтримуваному форматі; main звіряє чинний metadata service, H1, breadcrumb, LearningResource, canonical й descriptions до freeze.

Використано тільки існуючі native типи: hero, forms-grid, usage-panels, comparison-table, mistakes-grid, summary-list, navigation-chips, practice-set. Для таблиць наявний `headers + rows[].cells` відділяє повні Active/Passive речення й переклади від коментаря; `TheoryLegacyAdapter` уже підтримує цей формат. Це не новий table renderer. Для corrections використано підтримувані `wrong_en/wrong_ua/right_en/right_ua/note`, а не англійське речення, сховане в ua. У data немає stylesheet/view/CSS class/style selection, line clamp, overflow rules або нових компонентів.

## Formation Rules

| Slot / draft key | Збережено | Виправлено / додано / об’єднано |
|---|---|---|
| 0 `formation-hero` | Фокус, be + V3, виконавець; Tom writes letters, They built a house, Shakespeare | Окремі UK переклади; they не вилучено автоматично; формула не є перестановкою довільних слів |
| 1 `formation-forms` | Active/Passive порівняння, V3, by | Present/Past basic узгодження за новим підметом; звичайні й неправильні V3; природний переклад замість «пишуться Томом» |
| 2 `formation-steps` | Три кроки з object, be/V3 та by | В examples.ua тепер переклад. «Letters стає підметом» і розбір часу перенесено в окремий note/description. Повний named-agent перехід не губить Tom або відому they-групу |
| 3 `formation-transformations` | Усі чотири старі Active ситуації: Mary/cake, they/English, someone/bike, company/employees | Обидві версії мають окремий повний переклад. У note більше немає неперекладеного Passive. Явно розрізнено повне збереження виконавця й повідомлення без нього; коротка межа arrive без object |
| 4 `formation-uses` | Чотири ситуації та всі дванадцять корисних прикладів: unknown, unimportant, focus, formal | Українські переклади природні, час не підмінено. Пасив не обмежено формальним стилем. Старе by someone збережено як граматичний контекстний варіант, не «неправильне речення». Зазначено Present Perfect із has been та спосіб by email |
| 5 `formation-by-with` | Mark Twain, pen, our team, chocolate | Виправлено універсальне «by тільки людина/організація»: додано storm як неодушевлену причину. Agent/cause відрізнено від instrument/material |
| 6 `formation-mistakes` | Letter без was, speak замість spoken, by/with Shakespeare | Неповна група the letter written yesterday допустима всередині речення; помилка саме в самостійному заданому реченні. Неправильну позначку by someone прибрано з corrections і збережено видимою в slot 4. Її місце займає реальна втрата named Tom за explicit full-information умови |
| 7 `formation-summary` | Фокус, V3, час, by/with, omission та стиль | Повтори об’єднано в п’ять самодостатніх підсумкових пунктів; видима межа be + -ed не завжди подієвий пасив |
| 8 `formation-navigation` | Усі одинадцять старих labels, current тільки цей owner | Explicit `url` взято з точних сусідніх source slugs із префіксом theory-; main має HTTP-підтвердити всі destinations |

Заміни прикладів не робилися заради новизни. Нові singular/plural pairs у кроці be потрібні саме для узгодження; storm виправляє роль by; named Tom correction замінює хибний grammar verdict. Стан/подія має одне семантичне поглиблення, не нову квоту disclosures.

## Negatives & Questions in Passive

| Slot / draft key | Збережено | Виправлено / додано / об’єднано |
|---|---|---|
| 0 `negative-hero` | Заперечення, питання, короткі відповіді | Усі три формулювання тепер кажуть про перше допоміжне, не універсальне literal be; has/have been і will be узгоджені з прикладами |
| 1 `negative-present` | Чотири старі negatives + I'm not list; скорочення | Present не перекладено завершеною минулою дією заради форми. I am not invited — поточний статус. Стандартні I am not / I'm not, займенник I → you у відповіді співрозмовника |
| 2 `negative-past` | Усі чотири старі Past Simple examples, was/were, wasn't/weren't | Not після першого допоміжного; обмеження щодо did стосується цих конкретних be-passive моделей |
| 3 `negative-other` | Два Future та два Present Perfect examples | Компактно додано один named continuous point: is not being / were not being; being не been. Немає нового окремого tense уроку |
| 4 `negative-yes-no` | Present, Past, Future, Present Perfect моделі й корисні старі приклади | Інвертується лише перше допоміжне; continuous показано одним прикладом. Short statement-question caveat видимий у basic, не схований за detail |
| 5 `negative-short-answers` | Be, has/have і will відповіді | Універсальне «subject + be» замінено відповідним першим допоміжним; правильний займенник, повна positive ending, standard I'm not |
| 6 `negative-wh` | Час/місце/спосіб/причина; Who/What як підмет; усі шість старих прикладів | Wh + first auxiliary загальна будова; третій point питає виконавця через by. Who was invited — «Кого запросили?», не «Хто запросив?» |
| 7 `negative-table` | Усі чотири старі tense situations | ua більше не містить English negative. Кожна negative/question/short answer має переклад; tense у коментарі. Один continuous рядок доповнює first-auxiliary пояснення |
| 8 `negative-mistakes` | Три початкові приклади порядку, відсутнього допоміжного й незавершеного Yes, it | Для першого задано саме neutral inversion, statement questions не названо глобально неграматичними. Решта corrections не змінює intended tense/negation |
| 9 `negative-summary` | Not, yes/no, short answers, wh, subject Who/What | П’ять узгоджених тез про перше допоміжне й різних запитуваних учасників |
| 10 `negative-navigation` | Усі одинадцять labels | Exact explicit source-slug URLs, поточний тільки N/Q owner |

Statement-question поглиблення не повторює коротке caveat: воно показує відмінний комунікативний сценарій, інтонацію/попередній факт і два перекладені власні приклади. Немає глобальної заборони do або get-passive модернізації.

## Passive with Modal Verbs

| Slot / draft key | Збережено | Виправлено / додано / об’єднано |
|---|---|---|
| 0 `modal-hero` | Task today: affirmative/negative/question | Переклади повні; modal meaning відділено від passive focus |
| 1 `modal-forms` | Усі вісім can/could/may/might/must/should/will/would examples | Одна додаткова компактна card для has to/needs to/ought to: to не заборонене, власне узгодження не плутається з must -s |
| 2 `modal-meaning` | Чотири початкові семантичні групи й усі дванадцять корисних examples | May possibility vs permission контекстний, без відсотків; must не тільки обов’язок. «Правила мають дотримуватися» виправлено на «Правил треба дотримуватися». Посилання на M49–M51, не переписування всього modal довідника |
| 3 `modal-negatives` | Три початкові negatives й шість examples | Додано дві потрібні групи: needn't/doesn't have to як необов’язковість; may not як possible negative result у явному delivery контексті. Заборона й необов’язковість не змішані |
| 4 `modal-questions` | Три yes/no та три wh examples | Short answers додано окремим point. Does have to/need to допустимі за іншою будовою. No, it needn't на Must питання не замінено mustn't |
| 5 `modal-transformations` | Усі чотири старі Active ситуації й модальні слова | Обидві версії перекладено; by you/them/us зберігають повний повідомлений склад. Неправильне «змінюється лише основне дієслово» виправлено без зміни modal |
| 6 `modal-perfect-forms` | Усі чотири початкові perfect моделі й приклади | Повний блок не видалено. Часова перспектива відділена від основного modal + be. Should have been не доводить невиконання сам; could have been залежить від контексту |
| 7 `modal-perfect-meaning` | Три початкові групи: deduction, criticism, unrealised possibility | Для критики додано явний known-late/unfixed/no-warning контекст, а не голе «не зробили». Для could unrealised прямо названа реальна подія/результат; alternative past guess видимий. Додано expectation point без автоматичної вини. «Помилки можна було запобігти» виправлено на «Їй можна було запобігти» |
| 8 `modal-mistakes` | Must done, can to be, should have be, musts be | Час, заперечення й modal не змінені; усі intended translations окремі; to-warning обмежене саме can |
| 9 `modal-summary` | Основна/negative/question/perfect, значення, незмінні modal | Шість тез замість повторів; expectation та have/need/ought відображені, універсальну «perfect тільки критика» прибрано |
| 10 `modal-navigation` | Усі одинадцять labels | Exact explicit URLs, поточний тільки modal owner |

Доповнення не претендує на нову повну класифікацію модальності. Perfect з майбутньої точки — одне meaningful поглиблення саме expectation point, не прихований базовий must/should формульний матеріал.

## Власна практика: 18 tasks, не 36 завдань

Кожен lesson має 2 selects (форма), 2 choices (зміст), 2 inputs (ручний ввід і tokens). Inputs — шість завдань із двома способами відповіді, не дванадцять завдань. `source_index` 1–6, сталі m52 IDs. Умова input у підтримуваному `after`, перемішаний slash bank у `before`; ready-answer hints/placeholders не додано. Feedback містить повну відповідь, переклад і короткий розбір, а options — лише кандидат, не авторський ключ.

| Lesson | Selects | Choices | Canonical inputs |
|---|---|---|---|
| Formation | parcels are sorted; sign written | named Sofia retained; by storm vs with scissors roles | The shelves were repaired by Nora yesterday. / The maps are printed by Iryna. |
| N/Q | has not been; will short answer | invited patient vs inviting agent; explicitly neutral inversion | Was the parcel sent on Monday? / The plans have not been approved yet. |
| Modal | must be checked; should have been copied | no obligation vs prohibition; should-have-been expectation | The gate must not be opened during the test. / The cables should have been checked by Olha. |

Для власних inputs у visible conditions задані потрібні passive model, words, subject start і відносний порядок обставини, де канонічний ключ строгий. Named-agent inputs зберігають by Nora/Iryna/Olha; gate завдання прямо просить не називати виконавця. Accepted aliases: haven't для have not; mustn't для must not; should've для should have. Full translations не підмінено формулою. У clause із gate дві різні instances the наявні в slash bank, не один token, який неможливо використати двічі.

У meaning choices точний переклад відповідного English candidate відкладено до Check, коли він безпосередньо назвав би правильного учасника або значення. Цей виняток явний; український контекст і всі смислові критерії видимі одразу. Main має перевірити кожен answer/accepted/feedback чинним engine, all empty/partial/wrong/retry/edit/repeated/reset transitions, 24 input task/mode/size cases й незалежність score груп перед freeze/live acceptance.

## Point-level basic/detail

| Detail key | Owner | Семантична причина | Що лишається в basic |
|---|---|---|---|
| `formation-event-or-state` | `formation-forms`, `/items/1` | Окреме пояснення event/state контексту й двох власних перекладених door examples, не ще один клік за формулою | Be + V3, V3, час, by/with, limits і коротка visible межа «не кожне be + -ed подія» |
| `negative-question-purpose` | `negative-yes-no`, `/sections/0` | Нейтральне нове питання проти перевірки вже почутого факту/здивування | Інверсія, first auxiliary, коротка caveat про grammar-valid statement questions |
| `modal-perfect-reference-point` | `modal-perfect-meaning`, `/sections/3` | Perfect оглядає завершення назад з майбутньої контрольної точки; переклад і контекст, не нова формула в hidden | Основна й perfect формули, criticism vs expectation, direct known/non-known action contrast |

Квоти немає: ці три поглиблення залишені за семантикою, а не runtime word threshold. Немає anonymous rule cards або панелі для кожного речення. Усі базові переклади видимі, крім явно обґрунтованих meaning-task check exceptions; detail сам має переклади.

## Окремі linked widgets 5/5

Банки не створює editorial agent; фактичний source/live audit — окремий підпроцес. Draft використовує source-reviewed existing exact classes:

- Formation: `Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceBasicsLessonSeeder`, A2.
- N/Q: `Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceNegativesQuestionsAllLevelsLessonSeeder`, B1.
- Modal: `Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceModalVerbsAllLevelsLessonSeeder`, B1.

Draft `linked_practice.intro` — вже підтримуване rendered поле. Воно явно задає вправні критерії для конкретної відібраної п’ятірки: Formation Present Simple/start/place-before-time і shop для слова «магазин»; N/Q exact tense per referent, object-first, not після first auxiliary, обставини/yet наприкінці та on Sundays для офісу; Modal саме modal + be + V3, exact modal per referent, object-first, carefully після checked. Це не глобальна граматична заборона інших природних формулювань і не prompt rewrite новим helper. Banks agent fresh live source/IDs audit підтвердив Formation 240–244, N/Q 37311–37315, Modal 37239–37243; main остаточно перевіряє незалежний builder contract. П’ятнадцять selected questions обліковуються окремо від 18 own tasks; нові question rows не потрібні, банки не мутовано.

## Джерела й чесність доступу

У master наведено точні фактичні URLs і способи доступу. British Council Passives, Active and passive voice та Perfect aspect прочитано через прямий publisher HTML, тільки основний граматичний текст. Коментарі учнів не використано як нормативну підставу. Google Search Central прочитано прямим publisher HTML; тверджень про майбутні ranking gains немає.

Cambridge direct open для заданих passive-forms US, passives-with-and-without-an-agent, statement-questions, should і could повертав tool internal error, не підтверджене повне пряме читання й не вигадане 403. Перевірено publisher-indexed текст canonical passive-forms, actual agent endpoint `/passives-with-and-`, Danish publisher localisation statement-questions із English explanatory body, should/could. Тут не заявляється full direct reading цих сторінок. Examples і tasks створено/збережено за scoped editorial work; видавничих exercise banks не скопійовано.

## Технічний freeze / apply / live acceptance — окремі докази

Main independent language review усіх blocks/details завершено; offline unchanged-native-engine 22/22 PASS для 18 tasks/36 options/6 manual/6 token/9 canonical-or-contraction accepted answers. Відмінність чотирьох deferred English-stimulus translations і двох явно заданих UK question criteria відображено в тесті, а Check-guarded feedback перевірено для всіх шести choices. Окремо потрібні actual metadata/internal URL checks; owner IDs, category ancestry й runtime proof; точні existing widget IDs та links; aliases/token multiset/answer-engine review; SQL TEXT body byte sizes; readable edition, component mapping і patch/contract/checksums. Нові файли мають стати LF із одним final newline до hashes; цей draft перевіряється за фактичними raw bytes. Старі frozen sources/bytes не нормалізуються. Жодного apply, commit або push editorial agent не виконував.


## Публічні редакційні артефакти й independent source BEFORE

- `docs/content/m52-passive-basics.v1.0.0.json` — structured release master, підготовку freeze виконує main.
- `docs/content/m52-passive-basics.v1.0.0.uk.md` — повна читабельна версія всіх learning полів, details, own tasks/feedback і exact 15 existing widget question conditions/keys/token instances.
- `docs/content/m52-passive-basics-components.v1.json` — скінченний component/basic/detail map; він не вибирає новий дизайн.

Raw source BEFORE hashes, прочитані до source projection (не заміна приватного DB/HTTP/physical proof):

| Урок | Точна definition | SHA-256 |
|---|---|---|
| Formation Rules | `database/seeders/Page_V3/PassiveVoice/Basics/PassiveVoiceFormationRulesTheorySeeder/definition.json` | `6b91d3f7cc5676ab8152756a9e00f8d97951c5cc938e62fad54701f38ad0ff68` |
| Negatives & Questions | `database/seeders/Page_V3/PassiveVoice/Basics/PassiveVoiceNegativesQuestionsTheorySeeder/definition.json` | `18861ab0ff681a1b89cec6029c1e024ae2665cd6705cf049ade327450128db51` |
| Passive with Modal Verbs | `database/seeders/Page_V3/PassiveVoice/Basics/PassiveVoiceModalVerbsTheorySeeder/definition.json` | `42a5853d12c71f87540b2ba6a0e4c6d04bffda6b743b9444289604bcf24fef24` |

Review draft SHA: `5df35c446e5d6cb3e24ff90671534254aa25f290bcf0bcc6f62333cfeaf6ddb8`. Фінальний SHA буде іншим лише після явного freeze статусу/метаданих; навчальний зміст не змінюється без нового review. Незмінність H1 leading strong підтвердив main на live BEFORE: три strings, raw bilingual title й slug не замінені.
