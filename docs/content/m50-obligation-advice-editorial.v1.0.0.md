# Редакційна карта M50

Погоджена авторська редакція 1.0.0 для трьох чинних UK сторінок у modal-verbs. Master: `m50-obligation-advice.v1.0.0.json`; читабельний текст: `m50-obligation-advice.v1.0.0.uk.md`; семантична карта: `m50-obligation-advice-components.v1.json`. Основний навчальний зміст відповідає reviewed draft fc4e7a745200da4953fa7265d7412365f14bfe5a5ef057e2ec54695fc9a99ac3; перед остаточним пакуванням змінено лише погоджений добір linked bank questions і пов’язані технічні анотації.

## Межі й облік

Перші шість slots, їхні типи, колонки, technical A2 та footer без нового level продовжуються в тому самому порядку. Display A2-B1 не підміняє technical A2. Кожна сторінка отримує один новий practice-set після старих slots: 2 selects + 2 choices + 2 inputs. Кожний input має manual і token способи, але рахується одним завданням. Разом 18 власних завдань; окремі 15 linked-bank questions не є новими авторськими завданнями.

Нові візуальні компоненти, стилі, класи, в’юхи або grader не створюються. Існуючі вісім navigation destinations кожного уроку збережено. Frozen M46–M49 / Completion sources та maps не редагуються. Сам цей авторський документ не змінює definitions або БД. Точне додавання нового practice-set і п’яти потрібних зв’язків виконує окремий guarded інтеграційний етап.

| Урок | Старі sentence fields | EN збережено/перенесено | Змінено/вилучено | Inputs accepted | Practice data JSON UTF-8 bytes |
| --- | ---: | ---: | ---: | ---: | ---: |
| must-have-to | 18 | 15 | 3 | 7 | 14487 |
| should-ought-to | 18 | 16 | 2 | 4 | 11342 |
| need-neednt-dont-have-to | 18 | 15 | 3 | 7 | 12951 |

Окремо зафіксовано 12 старих form-records із title та subtitle — 24 поля формул/фрагментів. Це не 24 повні речення. Таким чином карта нижче охоплює всі 54 старі поля з реченнями та всі 24 English-bearing поля forms; технічна упаковка, component markers і локальна SQL capacity перевіряються інтегратором окремо.

Розмір вище — UTF-8 від compact JSON власного data до технічної упаковки. Це не PASS фактичної SQL-колонки. Немає сотень комбінаторних feedback keys: тільки оголошені aliases, потрібні апострофи, відсутня кінцева пунктуація, окремий пробіл перед ? та по одному відомому wrong probe для input.

## Редакційні рішення

- Must / have to мають контекстний перетин. Різницю заборони та необов’язковості задано явно; future must допустиме, помилка — will must.
- Should / ought to передають пораду та очікування. Регістр не перетворено на автоматичний осуд або посилений переклад. Одне meaning task явно приймає дві доречні поради.
- Need розділено на звичайне need to / need + noun та модальне needn't. Контекст визначає дозволеність додаткової їжі. Didn't need to не є доказом невиконання.
- Basic самодостатній. Must має 0 details. Should має 1 detail свого usage-пункту з двома поясненими минулими прочитаннями; Need — 1 detail про необхідність і фактичне виконання. Короткі правила, переклади та застереження не приховано.

## Must / Have to

[Точний local URL](http://gramlyze.loc/theory/modal-verbs/must-have-to)

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsMustHaveToTheorySeeder`. Display A2-B1; technical A2.

| BEFORE pointer і текст | AFTER | Причина |
| --- | --- | --- |
| `/page/blocks/0/body/rules/0/example`<br>I must finish this today. | retained-or-moved; `/blocks/0/data/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/1/example`<br>I have to wear a uniform at work. | retained-or-moved; `/blocks/0/data/rules/1/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/2/example`<br>You mustn't park here. / You don't have to come early. | retained-or-moved; `/blocks/0/data/rules/2/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/0/en`<br>I must call my mother tonight. | retained-or-moved; `/blocks/2/data/sections/2/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/1/en`<br>You must wear your seat belt. | replaced-or-removed; `/blocks/2/data` | Вилучено зайвий третій приклад вимоги; натомість парні речення про правило клубу показують головну уточнену межу: і must, і have to можуть описувати те саме правило. Вимогу безпеки не використовуємо як тест світових знань. |
| `/page/blocks/2/body/sections/0/examples/2/en`<br>We must be careful here. | replaced-or-removed; `/blocks/2/data` | Вилучено загальну вимогу без виразного контексту для компактності. Будову must покривають forms, а взаємозамінність у конкретному правилі — парний приклад клубу. |
| `/page/blocks/2/body/sections/1/examples/0/en`<br>I have to get up at six for work. | replaced-or-removed; `/blocks/2/data` | Не повторюємо третій приклад зовнішньої необхідності. Форми has to та минуле had to збережені в інших точних місцях; нова парна вимога не підтримує хибного абсолютного внутрішнього/зовнішнього поділу. |
| `/page/blocks/2/body/sections/1/examples/1/en`<br>She has to show her ID at the entrance. | retained-or-moved; `/blocks/1/data/items/1/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/2/en`<br>They had to cancel the trip because of the storm. | retained-or-moved; `/blocks/2/data/sections/3/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/0/en`<br>You mustn't touch this button. | retained-or-moved; `/blocks/2/data/sections/1/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/1/en`<br>You don't have to wash the cups. I'll do it. | retained-or-moved; `/blocks/2/data/sections/1/examples/1/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/2/en`<br>We won't have to rush if we leave now. | retained-or-moved; `/blocks/1/data/items/5/rules/1/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/3/body/items/0/wrong`<br>He musts leave now. | retained-or-moved; `/blocks/3/data/items/0/wrong_en` | EN збережено в цільовому mistakes-контексті; переклад передає або справжній зміст, або прямо названий задум помилкової форми. |
| `/page/blocks/3/body/items/0/right`<br>He must leave now. | retained-or-moved; `/blocks/1/data/items/0/rules/0/example`, `/blocks/3/data/items/0/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/3/body/items/1/wrong`<br>You don't have to smoke here. | retained-or-moved; `/blocks/3/data/items/1/wrong_en` | EN збережено як граматично можливе речення з іншим змістом, не як безумовна граматична помилка. Перед парою прямо задано заборону куріння. |
| `/page/blocks/3/body/items/1/right`<br>You mustn't smoke here. | retained-or-moved; `/blocks/3/data/items/1/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/3/body/items/2/wrong`<br>I will must finish it tomorrow. | retained-or-moved; `/blocks/3/data/items/2/wrong_en` | Збережено справжню помилку will must; hint і visible usage прямо підтверджують правильне must із tomorrow. |
| `/page/blocks/3/body/items/2/right`<br>I will have to finish it tomorrow. | retained-or-moved; `/blocks/1/data/items/5/rules/0/example`, `/blocks/3/data/items/2/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/1/body/items/0/title`<br>must + V<br>`/page/blocks/1/body/items/0/subtitle`<br>must leave / must study / mustn't forget | `/blocks/1/data/items/0/title`, `/blocks/1/data/items/0/rules` | Формулу розгорнуто з явним підметом та повним запереченням; окремі фрагменти leave / study / forget замінено повними EN–UK прикладами без зайвого -s. |
| `/page/blocks/1/body/items/1/title`<br>have to / has to + V<br>`/page/blocks/1/body/items/1/subtitle`<br>have to work / has to pay | `/blocks/1/data/items/1/title`, `/blocks/1/data/items/1/rules` | Модель have / has to збережено, але назва не зводить її лише до зовнішніх причин; повний приклад показує третю особу. |
| `/page/blocks/1/body/items/2/title`<br>don't / doesn't have to + V<br>`/page/blocks/1/body/items/2/subtitle`<br>don't have to hurry | `/blocks/1/data/items/2/title`, `/blocks/1/data/items/2/rules` | Модель заперечення збережено й пояснено have після doesn't; фрагмент hurry замінено повним прикладом з перекладом. |
| `/page/blocks/1/body/items/3/title`<br>had to / will have to<br>`/page/blocks/1/body/items/3/subtitle`<br>had to leave / will have to wait | `/blocks/1/data/items/4`, `/blocks/1/data/items/5` | Минулий і майбутній час рознесено у дві native form cards; додано didn't / won't have to та уточнення про можливий future must. |
| Subtitle, intro, labels і summary: `/page/subtitle_html`, `/page/subtitle_text`, `/page/blocks/0/body/intro`, `/page/blocks/2/body/sections`, `/page/blocks/4/body/items` | `/subtitle_html`, `/subtitle_text`, `/blocks/0/data/intro`, `/blocks/2/data/sections`, `/blocks/4/data/items` | Замість дублювання сильного/зовнішнього обов’язку додано видимі межі значень, будову питань і часів. Виправлено граматику українського формулювання про базову форму; немає абсолютного поділу must / have to. |
| Навігація: `/page/blocks/5/body` | unchanged; `/blocks/5/data` | Усі вісім старих destinations, labels і current збережено точно. Додані контекстні посилання не змінюють сусідні уроки; авторська робота не заявляє HTTP-приймання. |
| Власна практика:  | `/blocks/6/data` | Додано всі три різні native групи. Два inputs — два завдання з ручним та token-способом, не чотири завдання. Linked bank widget — окреме джерело з п’ятьма незміненими питаннями. |

### Практика за навчальною метою

| № | ID | Механіка | Ціль |
| ---: | --- | --- | --- |
| 1 | `m50-must-have-to-agreement` | selects | Третя особа has to без втрати to |
| 2 | `m50-must-have-to-future` | selects | Will have to без хибної заборони future must |
| 3 | `m50-must-have-to-prohibition` | choices | Задана заборона проти необов’язковості |
| 4 | `m50-must-have-to-negative-reply` | choices | Негативна відповідь про необхідність без випадкової заборони |
| 5 | `m50-must-have-to-build-negative` | inputs | Нове заперечне have to; скорочення, перестановка tomorrow і кома |
| 6 | `m50-must-have-to-build-question` | inputs | Do + we + have to; знак питання та достатній словник |

Відомі wrong probes для незалежних engine-тестів:

- `m50-must-have-to-build-negative`: `Tomorrow, Lena doesn't has to print the tickets.`.
- `m50-must-have-to-build-question`: `Does we have to book the room?`.

### Basic і point-level поглиблення

Увесь зміст видимий. Окреме поглиблення тут не потрібне.

### Тематичне підключення

- Джерело `theory_links`, `question_types: ["4"]`; exact bank_seeders `Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder`. Погоджені local question IDs: 121, 122, 123, 124, 125.
- Очікування: один native widget, п’ять різних наявних type 4, точний A2. Власні inputs не замінюють цей widget.
- Очікування зареєстровано незалежно від AFTER matcher. Для Must / Should достатньо чинних page-wide links; для Need погоджено рівно п’ять нових pivots до нового practice block. Це не дозвіл на нові питання або зміну старих відповідей.

### Першоджерела та фактичний доступ

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/must), 2026-10-13. Пряме відкриття URL повернуло 403. Прочитано індексований редакційний текст через web search; успішне повне пряме читання не заявлено.
  Перевірено: Must без -s та do; базова форма без to. Must може стосуватися майбутнього, але не поєднується з will. Минулий обов’язок — had to; must have + V3 має інший смисл. Must / have to мають контекстний перетин; mustn't не дорівнює don't have to. Перевірка правил і меж; нові приклади та вправи авторські. Збіги старих прикладів походять із незалежної BEFORE definition.

- [British Council LearnEnglish](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-permission-obligation), 2026-10-13. Пряме відкриття успішне; прочитано редакційні розділи про obligation, no obligation та prohibition. Коментарі учнів не використано.
  Перевірено: Must / have to для необхідності; mustn't для заборони; don't have to для відсутності обов’язку. Перевірка смислових меж. Видавничі приклади й завдання не копіювалися.

- [Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content), 2026-10-13. Пряме відкриття успішне; прочитано редакційний матеріал про people-first content. Це не оцінка індексації або позицій Gramlyze.
  Перевірено: Корисна повна відповідь на запит читача, точність, оригінальні приклади; без квоти слів і keyword stuffing. Структура трьох уроків та редакційна самоперевірка; жодних claims про CTR, ranking gains чи стороннього рецензента.

## Should / Ought to

[Точний local URL](http://gramlyze.loc/theory/modal-verbs/should-ought-to)

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsShouldOughtToTheorySeeder`. Display A2-B1; technical A2.

| BEFORE pointer і текст | AFTER | Причина |
| --- | --- | --- |
| `/page/blocks/0/body/rules/0/example`<br>You should get some rest. | retained-or-moved; `/blocks/0/data/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/1/example`<br>The train should arrive soon. | retained-or-moved; `/blocks/0/data/rules/1/example`, `/blocks/3/data/items/2/right_en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/2/example`<br>You ought to apologize. | retained-or-moved; `/blocks/0/data/rules/2/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/0/en`<br>You should talk to your teacher. | retained-or-moved; `/blocks/2/data/sections/0/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/1/en`<br>We ought to leave earlier next time. | retained-or-moved; `/blocks/2/data/sections/0/examples/1/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/2/en`<br>He shouldn't spend all his money at once. | retained-or-moved; `/blocks/1/data/items/0/rules/1/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/0/en`<br>The package should arrive tomorrow. | retained-or-moved; `/blocks/2/data/sections/1/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/1/en`<br>This key should open the back door. | retained-or-moved; `/blocks/2/data/sections/1/examples/2/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/2/en`<br>Everything should be easier after the update. | replaced-or-removed; `/blocks/2/data` | Вилучено додатковий загальний прогноз про оновлення. Очікування показано прозорою парою про посилку з should / ought to; новий контент не обіцяє технічного результату. |
| `/page/blocks/2/body/sections/2/examples/0/en`<br>You should call her. | retained-or-moved; `/blocks/2/data/sections/2/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/1/en`<br>You ought to call her. | retained-or-moved; `/blocks/2/data/sections/2/examples/1/en` | EN збережено; старий переклад «Тобі справді слід їй подзвонити» замінено на «Тобі варто їй зателефонувати», щоб ought to не отримувало штучно сильнішого значення. |
| `/page/blocks/2/body/sections/2/examples/2/en`<br>People ought to respect the rules. | replaced-or-removed; `/blocks/2/data` | Вилучено абстрактне нормативне твердження, яке підсилювало старе моралізаторське трактування. Точна пара поради із call her лишається без штучного посилення перекладу. |
| `/page/blocks/3/body/items/0/wrong`<br>You should to rest. | retained-or-moved; `/blocks/3/data/items/0/wrong_en` | EN збережено в цільовому mistakes-контексті; переклад передає або справжній зміст, або прямо названий задум помилкової форми. |
| `/page/blocks/3/body/items/0/right`<br>You should rest. | retained-or-moved; `/blocks/1/data/items/0/rules/0/example`, `/blocks/3/data/items/0/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/3/body/items/1/wrong`<br>We ought leave now. | retained-or-moved; `/blocks/3/data/items/1/wrong_en` | EN збережено в цільовому mistakes-контексті; переклад передає або справжній зміст, або прямо названий задум помилкової форми. |
| `/page/blocks/3/body/items/1/right`<br>We ought to leave now. | retained-or-moved; `/blocks/1/data/items/2/rules/0/example`, `/blocks/3/data/items/1/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/3/body/items/2/wrong`<br>The train should arrives soon. | retained-or-moved; `/blocks/3/data/items/2/wrong_en` | EN збережено в цільовому mistakes-контексті; переклад передає або справжній зміст, або прямо названий задум помилкової форми. |
| `/page/blocks/3/body/items/2/right`<br>The train should arrive soon. | retained-or-moved; `/blocks/0/data/rules/1/example`, `/blocks/3/data/items/2/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/1/body/items/0/title`<br>should + V<br>`/page/blocks/1/body/items/0/subtitle`<br>should study / shouldn't worry / Should I call? | `/blocks/1/data/items/0`, `/blocks/1/data/items/1` | Should + базова форма, заперечення й питання розділено за будовою; Should I call? збережене в повному перекладеному діалозі. |
| `/page/blocks/1/body/items/1/title`<br>ought to + V<br>`/page/blocks/1/body/items/1/subtitle`<br>ought to rest / ought not to wait | `/blocks/1/data/items/2` | Збережено потрібне to; негативна повна форма та oughtn't to пояснені явно, фрагменти rest / wait замінено повними реченнями. |
| `/page/blocks/1/body/items/2/title`<br>should + V<br>`/page/blocks/1/body/items/2/subtitle`<br>should arrive / should be ready | `/blocks/2/data/sections/1` | Очікування перенесено до іменованого usage-пункту з should / ought to; форми не дублюють той самий зміст окремою карткою. |
| `/page/blocks/1/body/items/3/title`<br>not: should to<br>`/page/blocks/1/body/items/3/subtitle`<br>should go, but ought to go | `/blocks/1/data/items/0/title`, `/blocks/1/data/items/2/title`, `/blocks/3/data/items/0`, `/blocks/3/data/items/1` | Реальне should to лишається у mistakes; пара формул не вдає, що сама по собі є перекладеним реченням. Додано формальне ought-питання. |
| Subtitle, intro, labels і summary: `/page/subtitle_html`, `/page/subtitle_text`, `/page/blocks/0/body/intro`, `/page/blocks/2/body/sections`, `/page/blocks/4/body/items` | `/subtitle_html`, `/subtitle_text`, `/blocks/0/data/intro`, `/blocks/2/data/sections`, `/blocks/4/data/items` | Прибрано автоматичне «моралізаторськи / повчальніше»; обидві конструкції можуть передавати ту саму пораду та очікування. Регістр питань відокремлено від сили поради. |
| Навігація: `/page/blocks/5/body` | unchanged; `/blocks/5/data` | Усі вісім старих destinations, labels і current збережено точно. Додані контекстні посилання не змінюють сусідні уроки; авторська робота не заявляє HTTP-приймання. |
| Власна практика:  | `/blocks/6/data` | Додано всі три різні native групи. Два inputs — два завдання з ручним та token-способом, не чотири завдання. Linked bank widget — окреме джерело з п’ятьма незміненими питаннями. |

### Практика за навчальною метою

| № | ID | Механіка | Ціль |
| ---: | --- | --- | --- |
| 1 | `m50-should-ought-base` | selects | Базова форма після should, без to/-s |
| 2 | `m50-should-ought-to-form` | selects | To в цільовому стверджувальному ought to |
| 3 | `m50-should-ought-expectation` | choices | Очікування через ought to, не наказ або гарантія |
| 4 | `m50-should-ought-overlap` | choices | Перетин поради: should та ought to приймаються обидва |
| 5 | `m50-should-ought-build-negative` | inputs | Oughtn't to / ought not to; to зберігається |
| 6 | `m50-should-ought-build-question` | inputs | Should-питання в новій ситуації подорожі |

Відомі wrong probes для незалежних engine-тестів:

- `m50-should-ought-build-negative`: `We oughtn't leave the tickets at home.`.
- `m50-should-ought-build-question`: `Do we should take a bus to the station?`.

### Basic і point-level поглиблення

- `should-ought-to-past-advice-and-expectation`: Порада про минуле чи очікування до певного часу. Власник `should-ought-to-uses` → `/sections/1`; fragment `usage-panels`, 2 змістових секцій.

### Тематичне підключення

- Джерело `theory_links`, `question_types: ["4"]`; exact bank_seeders `Database\Seeders\V3\Polyglot\PolyglotShouldOughtToLessonSeeder`. Погоджені local question IDs: 97, 98, 99, 100, 101.
- Очікування: один native widget, п’ять різних наявних type 4, точний A2. Власні inputs не замінюють цей widget.
- Очікування зареєстровано незалежно від AFTER matcher. Для Must / Should достатньо чинних page-wide links; для Need погоджено рівно п’ять нових pivots до нового practice block. Це не дозвіл на нові питання або зміну старих відповідей.

### Першоджерела та фактичний доступ

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/should), 2026-10-13. Пряме відкриття URL повернуло 403. Прочитано індексований редакційний текст через web search; успішне повне пряме читання не заявлено.
  Індексований URL: [https://dictionary.cambridge.org/us/grammar/british-grammar/Should](https://dictionary.cambridge.org/us/grammar/british-grammar/Should).
  Перевірено: Should: порада та очікуваний результат; базова форма, not, інверсія без do. Should have може описувати пораду про минуле або очікування вже здійсненого результату. Перевірка правил і меж; нові приклади та вправи авторські. Збіги старих прикладів походять із незалежної BEFORE definition.

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/ought-to), 2026-10-13. Пряме відкриття URL повернуло 403. Прочитано індексований редакційний текст через web search; успішне повне пряме читання не заявлено.
  Перевірено: Ought to: порада й очікуваний результат; not / n't перед to. Ought-питання правильні, але нечасті й дуже формальні; should звичніше. Ought to не додає автоматично сильнішого осуду чи іншого значення в тій самій пораді. Перевірка правил і меж; нові приклади та вправи авторські. Збіги старих прикладів походять із незалежної BEFORE definition.

- [British Council LearnEnglish](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/probability), 2026-10-13. Пряме відкриття успішне; прочитано редакційний розділ про should / should have як очікуваний результат. Коментарі учнів не використано.
  Перевірено: Очікування через should та should have не є гарантією і не зводиться до жалю про невиконане. Перевірка другого смислу point-level поглиблення; приклади створені окремо.

- [Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content), 2026-10-13. Пряме відкриття успішне; прочитано редакційний матеріал про people-first content. Це не оцінка індексації або позицій Gramlyze.
  Перевірено: Корисна повна відповідь на запит читача, точність, оригінальні приклади; без квоти слів і keyword stuffing. Структура трьох уроків та редакційна самоперевірка; жодних claims про CTR, ranking gains чи стороннього рецензента.

## Need / Needn't / Don't have to

[Точний local URL](http://gramlyze.loc/theory/modal-verbs/need-neednt-dont-have-to)

Identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsNeedNeedntDontHaveToTheorySeeder`. Display A2-B1; technical A2.

| BEFORE pointer і текст | AFTER | Причина |
| --- | --- | --- |
| `/page/blocks/0/body/rules/0/example`<br>I need to finish this report. | retained-or-moved; `/blocks/0/data/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/1/example`<br>You needn't wait. / You don't have to wait. | retained-or-moved; `/blocks/0/data/rules/1/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/0/body/rules/2/example`<br>You mustn't touch it. / You don't have to touch it. | retained-or-moved; `/blocks/0/data/rules/2/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/0/en`<br>I need to charge my phone. | retained-or-moved; `/blocks/2/data/sections/0/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/1/en`<br>She needs to speak to the manager. | retained-or-moved; `/blocks/1/data/items/0/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/0/examples/2/en`<br>Do we need to book a table? | retained-or-moved; `/blocks/1/data/items/4/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/0/en`<br>You needn't bring any food. I've already cooked. | retained-or-moved; `/blocks/2/data/sections/1/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/1/en`<br>You don't have to come early tomorrow. | retained-or-moved; `/blocks/1/data/items/3/rules/0/example` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/1/examples/2/en`<br>We needn't rush. There is plenty of time. | replaced-or-removed; `/blocks/2/data` | Вилучено повторне пояснення необов’язковості. В іменованому пункті натомість зіставлено needn't / don't need to / don't have to в одному явно дозволеному контексті. |
| `/page/blocks/2/body/sections/2/examples/0/en`<br>You mustn't park here. | retained-or-moved; `/blocks/2/data/sections/2/examples/0/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/1/en`<br>You don't have to drive. We can walk. | retained-or-moved; `/blocks/2/data/sections/2/examples/1/en` | Корисний EN приклад збережено дослівно; він має окремий український переклад у вказаному native полі. |
| `/page/blocks/2/body/sections/2/examples/2/en`<br>You needn't wash the floor today. | replaced-or-removed; `/blocks/2/data` | Вилучено ще один приклад того самого значення для компактності. Це не визнання речення помилковим; необов’язковість повністю лишається в basic. |
| `/page/blocks/3/body/items/0/wrong`<br>She needn't to come. | retained-or-moved; `/blocks/3/data/items/0/wrong_en` | EN збережено в цільовому mistakes-контексті; переклад передає або справжній зміст, або прямо названий задум помилкової форми. |
| `/page/blocks/3/body/items/0/right`<br>She needn't come. | retained-or-moved; `/blocks/1/data/items/2/rules/0/example`, `/blocks/3/data/items/0/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/3/body/items/1/wrong`<br>You mustn't bring food. I already ordered. | retained-or-moved; `/blocks/3/data/items/1/wrong_en` | EN збережено для контрасту значення: тепер умова прямо дозволяє власну їжу, але називає її непотрібною. Саме mustn't не оголошується неграматичним. |
| `/page/blocks/3/body/items/1/right`<br>You don't have to bring food. I already ordered. | replaced-or-removed; `/blocks/3/data/items/1/right_en` | Уточнено результат замовлення як enough і додано явну дозволеність своєї їжі. Нове речення: You don't have to bring food. I've already ordered enough. Факт замовлення сам по собі не визначав різниці між забороною й непотрібністю. |
| `/page/blocks/3/body/items/2/wrong`<br>He need go now. | retained-or-moved; `/blocks/3/data/items/2/wrong_en` | Стару форму розглянуто лише в звичайному теперішньому ствердженні з need to, а не як глобальну заборону будь-якого модального need. |
| `/page/blocks/3/body/items/2/right`<br>He needs to go now. | retained-or-moved; `/blocks/3/data/items/2/right_en` | Правильне EN речення збережено без старої декоративної обгортки; додано окремий український переклад. |
| `/page/blocks/1/body/items/0/title`<br>need to / needs to + V<br>`/page/blocks/1/body/items/0/subtitle`<br>need to leave / needs to study | `/blocks/1/data/items/0` | Need / needs to збережено як звичайну модель; фрагменти leave / study замінено повним прикладом із she та перекладом. |
| `/page/blocks/1/body/items/1/title`<br>don't / doesn't need to + V<br>`/page/blocks/1/body/items/1/subtitle`<br>don't need to hurry | `/blocks/1/data/items/1` | Заперечення з do / does розгорнуто з явною базовою формою need; замість hurry — повний приклад з he. |
| `/page/blocks/1/body/items/2/title`<br>needn't + V<br>`/page/blocks/1/body/items/2/subtitle`<br>needn't wait / needn't worry | `/blocks/1/data/items/2` | Модальне need not / needn't показано без to та -s; фрагменти wait / worry замінено повним перекладеним реченням. |
| `/page/blocks/1/body/items/3/title`<br>don't have to + V<br>`/page/blocks/1/body/items/3/subtitle`<br>don't have to come early | `/blocks/1/data/items/3` | Модель don't / doesn't have to уточнює обидві особові форми; повний приклад із tomorrow переноситься сюди з usage. |
| Subtitle, intro, labels і summary: `/page/subtitle_html`, `/page/subtitle_text`, `/page/blocks/0/body/intro`, `/page/blocks/2/body/sections`, `/page/blocks/4/body/items` | `/subtitle_html`, `/subtitle_text`, `/blocks/0/data/intro`, `/blocks/2/data/sections`, `/blocks/4/data/items` | Необов’язковість описана для трьох допустимих форм; додано звичайне need + іменник і питання. Заборона потребує явного контексту; граматику українського правила про базову форму виправлено. |
| Навігація: `/page/blocks/5/body` | unchanged; `/blocks/5/data` | Усі вісім старих destinations, labels і current збережено точно. Додані контекстні посилання не змінюють сусідні уроки; авторська робота не заявляє HTTP-приймання. |
| Власна практика:  | `/blocks/6/data` | Додано всі три різні native групи. Два inputs — два завдання з ручним та token-способом, не чотири завдання. Linked bank widget — окреме джерело з п’ятьма незміненими питаннями. |

### Практика за навчальною метою

| № | ID | Механіка | Ціль |
| ---: | --- | --- | --- |
| 1 | `m50-need-needs-to` | selects | Needs to у звичайному ствердженні |
| 2 | `m50-neednt-base` | selects | Базова форма після модального needn't |
| 3 | `m50-need-no-obligation` | choices | Необов’язковість прямо відрізняється від заборони |
| 4 | `m50-need-past-fact` | choices | Факт виконання прямо заданий, didn't need to сам його не визначає |
| 5 | `m50-need-build-negative` | inputs | Needn't / need not у новому контексті обіду завтра |
| 6 | `m50-need-build-question` | inputs | Does + need to; не needs після Does |

Відомі wrong probes для незалежних engine-тестів:

- `m50-need-build-negative`: `We needn't to bring lunch tomorrow.`.
- `m50-need-build-question`: `Does Mila needs to bring an umbrella?`.

### Basic і point-level поглиблення

- `need-past-necessity-and-completed-action`: Не було необхідності: чи відбулася дія. Власник `need-neednt-dont-have-to-uses` → `/sections/1`; fragment `usage-panels`, 1 змістових секцій.

### Тематичне підключення

- Джерело `theory_links`, `question_types: ["4"]`; exact bank_seeders `Database\Seeders\V3\Polyglot\PolyglotGerundVsInfinitiveLessonSeeder`, `Database\Seeders\V3\Polyglot\PolyglotMustHaveToLessonSeeder`. Погоджені local question IDs: 158, 164, 166, 133, 135.
- Очікування: один native widget, п’ять різних наявних type 4, точний A2. Власні inputs не замінюють цей widget.
- Очікування зареєстровано незалежно від AFTER matcher. Для Must / Should достатньо чинних page-wide links; для Need погоджено рівно п’ять нових pivots до нового practice block. Це не дозвіл на нові питання або зміну старих відповідей.

### Першоджерела та фактичний доступ

- [Cambridge English Grammar Today](https://dictionary.cambridge.org/grammar/british-grammar/need), 2026-10-13. Пряме відкриття URL повернуло 403. Прочитано індексований редакційний текст через web search; успішне повне пряме читання не заявлено.
  Перевірено: Звичайне need to / needs to з do; модальне needn't без to й без do. Формальне Need I/we …?; need + іменник не перетворюється на needn't + іменник. Didn't need to / didn't have to — минула відсутність необхідності; needn't have + V3 у розглянутому смислі — вже виконана непотрібна дія. Перевірка правил і меж; нові приклади та вправи авторські. Збіги старих прикладів походять із незалежної BEFORE definition.

- [Cambridge Dictionary](https://dictionary.cambridge.org/dictionary/english/need), 2026-10-13. Пряме відкриття /dictionary/english/need повернуло 403. Прочитано індексовані редакційні словникові уривки need / didn't need to; це не повне пряме читання сторінки.
  Індексований URL: [https://dictionary.cambridge.org/us/dictionary/english/need](https://dictionary.cambridge.org/us/dictionary/english/need).
  Індексований URL: [https://dictionary.cambridge.org/dictionary/english/didn-t-need-to](https://dictionary.cambridge.org/dictionary/english/didn-t-need-to).
  Перевірено: Із самого didn't need to не випливає, чи дія відбулася; словникове пояснення допускає обидва випадки. Needn't have done стосується зробленого, хоча в ньому не було необхідності. Перевірка відмінності факту виконання від необхідності; ніяких learner comments.

- [British Council LearnEnglish](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-permission-obligation), 2026-10-13. Пряме відкриття успішне; прочитано редакційні розділи про obligation, no obligation та prohibition. Коментарі учнів не використано.
  Перевірено: Must / have to для необхідності; mustn't для заборони; don't have to для відсутності обов’язку. Перевірка смислових меж. Видавничі приклади й завдання не копіювалися.

- [Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content), 2026-10-13. Пряме відкриття успішне; прочитано редакційний матеріал про people-first content. Це не оцінка індексації або позицій Gramlyze.
  Перевірено: Корисна повна відповідь на запит читача, точність, оригінальні приклади; без квоти слів і keyword stuffing. Структура трьох уроків та редакційна самоперевірка; жодних claims про CTR, ranking gains чи стороннього рецензента.

## Review перед freeze

Main review Must: додано видимий точний UK переклад question stimulus у choice №4 без розкриття відповіді. Усі шість input wrong probes отримали компактний feedback із правильною EN відповіддю, точним UK перекладом та поясненням. Metadata залишає established `technical_block_level`, без дублювального level key.

Main review Should / Need: у Need summary заборона звужена до needn't прямо перед іменником; short-answer ellipsis на кшталт No, you needn't дозволена й перекладена. Needn't have + V3 має переклад, який явно зберігає виконану дію. Коротке застереження про didn't need to / didn't have to перенесене у visible basic; розгорнуті контрасти лишаються у власному detail. Should choice №3 має видимий EN–UK stimulus.

Виняток для meaning tasks Need №3–4 явний: точний український переклад English stimulus доступний у feedback після перевірки, бо його показ до відповіді безпосередньо розкрив би перевірюваний зміст. Українська умова й повний контекст видимі одразу. Це не відсутній переклад; він не використовується як option label або прихований hint.

Відоме обмеження чинного native engine: lookup feedback використовує trim + lowercase, не весь normalizer. Оголошені aliases і потрібні пунктуаційні форми мають явні ключі. Довільна незнайома неправильна відповідь отримує чинний generic fallback; новий grader або frontend-fix не вигадується.

Main review усіх трьох уроків, усіх 18 завдань та виправлень завершено; незалежний semantic/token review не виявив blockers у власному навчальному payload. Остаточний пакет потребує exact source bindings, технічного peer review, native rendering/engine tests і guarded local apply. Server presence, browser visibility та interaction фіксує інтеграційний звіт; цей авторський документ не підміняє їх PASS.

Сторонню експертну рецензію, сертифікацію, search volumes, CTR або покращення ранжування не заявлено.

## Заміна початкового добору конструктора

Початковий план із трьома AllLevels-банками відхилено до остаточного прийняття їхнього підключення: у Must виявлено неправильне «She have to…», а generic UK labels не були достатніми повними умовами речень. Це не виправляється переписуванням старих questions, answers або прогресу. Оригінальні bank definitions та питання лишаються поза content diff.

- Must використовує чинний `PolyglotMustHaveToLessonSeeder`, local IDs 121–125.
- Should використовує чинний `PolyglotShouldOughtToLessonSeeder`, local IDs 97–101.
- Need використовує скінченну незалежно погоджену п’ятірку: 158 / 164 / 166 з `PolyglotGerundVsInfinitiveLessonSeeder` (source q14 / q20 / q22; ствердження, заперечення та питання з need to), 133 / 135 з `PolyglotMustHaveToLessonSeeder` (source q13 / q15; відсутність обов’язку). Ширші назви корпусів не замінюють перевірки конкретних українських умов, канонічних відповідей і type 4 / A2.
- Для Need потрібні лише п’ять нових pivots до нового M50 practice block. Нових questions — 0; змін старих answers — 0. Course callers мають залишатися на точній попередній проєкції без нового practice block.

Це явна вузька curation, а не послаблення bank/owner/locale/level filters або довільне добирання сторонньої теми. Окремий контракт, generic discovery/gate, фактичний matcher і live widget повинні підтвердити ці п’ятірки під час інтеграції. Статус виконання link apply / browser acceptance тут не заявляється. Основний English/UK контент і всі 18 власних tasks через цей вибір банків не переписуються.
