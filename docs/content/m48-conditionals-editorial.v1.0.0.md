# M48 — editorial BEFORE/AFTER 1.0.0

База: `662c58482c8fc36b4044f7aef8cd260223c3b242`. Незалежний source/DB/HTTP BEFORE збережено до definition sync. Потреби читача редакційні, не search metrics.

## in-case

### Reader needs

Зрозуміти, що in case пояснює пересторогу, а не є звичайною заміною if.

Відрізнити майбутню можливість від уже актуального, але невідомого стану.

Розповісти про реальну минулу підготовку, не стверджуючи здійснення можливої події.

Побудувати in case-частину, заперечення та пряме питання без зайвої інверсії.

Уживати just in case без непотрібного продовження.

Після in case of добирати іменникову групу та зберігати часову логіку цілої інструкції.

За контекстом відрізняти доречність повідомлення від справжньої помилки форми.

### Exact editorial mapping

before: source_pointer: /page/blocks/0/body/rules/0/example
en: Take an umbrella in case it rains.
after: status: retained
locations: /blocks/0/data/rules/0/example

/blocks/6/data/rows/1/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/0/body/rules/1/example
en: I'll write it down in case I forget.
after: status: retained
locations: /blocks/1/data/items/0/rules/1/example
reason: Приклад перенесено з hero до картки форми; майбутнє правило явно обмежене звичайним майбутнім контекстом.

before: source_pointer: /page/blocks/0/body/rules/2/example
en: Take an umbrella if it rains. / Take an umbrella in case it rains.
after: status: retained-split
locations: /blocks/6/data/rows/0/en

/blocks/6/data/rows/1/en
retained_sentences: Take an umbrella if it rains.

Take an umbrella in case it rains.
reason: Два старі речення розділено на рядки штатної comparison-table; службовий розділювач « / » не є навчальним реченням. Обидва тексти й початковий порядок if → in case збережено; час виконання та невідома інформація названі явно.

before: source_pointer: /page/blocks/1/body/items/0/subtitle
en: I'll save the file in case the computer crashes.
after: status: retained
locations: /blocks/1/data/items/0/rules/0/example
reason: Англійський приклад переміщено зі змішаного subtitle у підтримуваний rules[].example з окремим перекладом; subtitle тепер пояснює правило українською.

before: source_pointer: /page/blocks/1/body/items/1/subtitle
en: I took some cash in case the card didn't work.
after: status: retained
locations: /blocks/1/data/items/1/rules/0/example
reason: Збережено реальну минулу пересторогу. Українське «не спрацює» передає можливість із тодішньої точки зору, а не стверджує фактичний збій картки.

before: source_pointer: /page/blocks/1/body/items/2/subtitle
en: In case of fire, use the stairs.
after: status: retained
locations: /blocks/1/data/items/2/rules/0/example
reason: Збережено як мовний приклад оголошення з in case of + noun; окремий переклад додано.

before: source_pointer: /page/blocks/1/body/items/3/subtitle
en: I'll bring a charger just in case.
after: status: retained
locations: /blocks/1/data/items/3/rules/0/example
reason: Збережено в картці just in case із самодостатнім українським перекладом.

before: source_pointer: /page/blocks/2/body/sections/0/examples/0/en
en: Take a jacket in case it gets cold later.
after: status: retained
locations: /blocks/2/data/sections/0/examples/0/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/2/body/sections/0/examples/1/en
en: I'll send you the address in case you can't find the place.
after: status: retained
locations: /blocks/2/data/sections/0/examples/1/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/2/body/sections/1/examples/0/en
en: If it rains, we'll stay inside.
after: status: retained
locations: /blocks/2/data/sections/3/examples/0/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/2/body/sections/1/examples/1/en
en: We'll take umbrellas in case it rains.
after: status: retained
locations: /blocks/2/data/sections/3/examples/1/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/2/body/sections/2/examples/0/en
en: I'll print one more copy, just in case.
after: status: retained
locations: /blocks/2/data/sections/4/examples/0/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/2/body/sections/2/examples/1/en
en: Put some water in your bag, just in case.
after: status: retained
locations: /blocks/2/data/sections/4/examples/1/en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/3/body/items/0/wrong
en: Call me in case you need help.
after: status: reclassified
locations: /details/0/fragment/data/sections/0/examples/0/en
reason: Знято безумовне ❌. Фразу збережено як контекстно недостатній старий приклад: без заданого плану вона не є ні універсальною граматичною помилкою, ні рекомендованим механічним синонімом if. Прозорий варіант реакції й явний підготовчий контекст розібрано окремо.

before: source_pointer: /page/blocks/3/body/items/0/right
en: Call me if you need help.
after: status: retained
locations: /details/0/fragment/data/sections/0/examples/1/en
reason: Збережено для явно заданої реакції лише після виникнення потреби; не названо виправленням будь-якої фрази з call + in case.

before: source_pointer: /page/blocks/3/body/items/1/wrong
en: Take a coat in case it will get cold.
after: status: retained
locations: /blocks/3/data/items/0/wrong

/blocks/3/data/items/0/wrong_en
reason: Збережено як помилку саме в цільовому звичайному майбутньому значенні. Немає глобальної заборони всіх інших часових або модальних форм після in case.

before: source_pointer: /page/blocks/3/body/items/1/right
en: Take a coat in case it gets cold.
after: status: retained
locations: /blocks/3/data/items/0/right

/blocks/3/data/items/0/right_en
reason: Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст.

before: source_pointer: /page/blocks/3/body/items/2/wrong
en: I'll stay at home in case it rains.
after: status: reclassified
locations: /details/0/fragment/data/sections/1/examples/0/en
reason: Знято безумовне ❌: залишитися вдома може бути реальною пересторогою щодо можливого дощу. Контекст задає, чи рішення вже прийнято незалежно від підтвердження дощу.

before: source_pointer: /page/blocks/3/body/items/2/right
en: I'll stay at home if it rains.
after: status: retained
locations: /details/0/fragment/data/sections/1/examples/1/en
reason: Збережено як інший, умовний план дії: за відсутності дощу людина планує вийти. Не підміняє автоматично речення з in case.

before: source_pointer: /page/blocks/1/body/items/0/title
en: main clause + in case + present
after: status: retained
locations: /blocks/1/data/items/0/title
reason: Початкову англійську формулу збережено; її часові та смислові межі пояснює український текст.

before: source_pointer: /page/blocks/1/body/items/1/title
en: in case + past
after: status: retained
locations: /blocks/1/data/items/1/title
reason: Початкову англійську формулу збережено; її часові та смислові межі пояснює український текст.

before: source_pointer: /page/blocks/1/body/items/2/title
en: in case of + noun
after: status: retained
locations: /blocks/1/data/items/2/title
reason: Початкову англійську формулу збережено; її часові та смислові межі пояснює український текст.

before: source_pointer: /page/blocks/1/body/items/3/title
en: just in case
after: status: retained
locations: /blocks/1/data/items/3/title
reason: Початкову англійську формулу збережено; її часові та смислові межі пояснює український текст.

before: scope: Визначення
text: Запобіжна дія зараз для можливої ситуації пізніше.
after: locations: /subtitle_html

/subtitle_text

/blocks/0/data/intro

/blocks/2/data/sections/1

/blocks/2/data/sections/2
reason: Вилучено універсальну вісь «обов’язково зараз → пізніше». Додано невідомий теперішній стан і реальну минулу підготовку; випадкова можливість не оголошена фактом.

before: scope: Після in case
text: Після <strong>in case</strong> зазвичай ставимо <strong>present simple</strong>, а не <strong>will</strong>.
after: locations: /blocks/1/data/intro

/blocks/3/data/items/0

/blocks/4/data/items/1
reason: Present form замість will обмежено звичайним майбутнім значенням. Минуле не заборонене; формальне британське should позначене короткою видимою ремаркою.

before: scope: In case of
text: Була тільки формула й один приклад оголошення.
after: locations: /blocks/1/data/items/2

/blocks/2/data/sections/5

/blocks/7/data/choices/2
reason: Додано noun-group boundary і різницю між умовною інструкцією та підготовкою. Сама сполука in case of не задає однаковий час дії в усіх реченнях.

before: scope: Питання й заперечення
text: Окремого пояснення будови не було.
after: locations: /blocks/1/data/items/4

/blocks/3/data/items/2

/blocks/7/data/selects/1

/blocks/7/data/choices/1
reason: Додано головне питання без інверсії в in case-частині й заперечну форму be; без нового компонента або механіки.

before: scope: Практика
text: У шести первинних блоках practice-set відсутній.
after: locations: /blocks/7/data
tasks: 6
required_controls: 6
visible_options: 15
reason: Три пропуски перевіряють конкретну дієслівну форму/заперечення/час; три вибори — часову логіку, питання та збереження інструкції при перефразуванні. Це не квота M46/M47. Усі controls штатні, engine не змінюється.

before: scope: Навігація
text: 10 старих destinations, включно з current In case.
after: locations: /blocks/5/data
status: unchanged
destinations: 10
reason: Повний чинний footer збережено. Два доречні вже наявні URLs First та Unless повторно використані як пояснювальні посилання в summary; сусідні сторінки не редагуються.

### Primary sources and access

url: https://dictionary.cambridge.org/us/grammar/british-grammar/in-case-of
publisher: Cambridge English Grammar Today
checked_at: 2026-10-12
access: Прочитано індексований редакційний текст через web search. Прямі відкриття URL з /grammar/ та /us/grammar/ повернули Internal Error без підтвердженого HTTP-коду; повне пряме читання не заявлено.
requested_url: https://dictionary.cambridge.org/grammar/british-grammar/in-case-of
checked_rules: Підготовка до можливої ситуації; відмінність від if за інформацією й рішенням

Just in case без підрядної частини

In case of + noun в умовній інструкції
use: Перевірка правил; приклади й вправи видавця не копіювалися.

url: https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/wishes-hypotheses
publisher: British Council
checked_at: 2026-10-12
access: Сторінку відкрито. Для цього уроку прочитано редакційний розділ Hypotheses / Expressions, зокрема present/future та in case; learner comments і відповіді в comment threads не використано як джерело.
checked_rules: In case може стосуватися теперішньої невідомої ситуації, а не лише події пізніше
use: Перевірка часової межі визначення. Видавничий приклад не перенесено до уроку.

url: https://www.oxfordlearnersdictionaries.com/definition/english/interpret
publisher: Oxford Advanced Learner’s Dictionary
checked_at: 2026-10-12
access: Словникову статтю відкрито; прочитано редакційний приклад минулої дії з in case + Past Simple у значенні усного перекладу. Це приклад уживання, не окремий повний граматичний трактат.
checked_rules: Реальна минула дія може поєднуватися з in case + past form
use: Незалежне первинне підтвердження минулої конструкції; словникове речення не копіюється.

url: https://dictionary.cambridge.org/dictionary/english/should
publisher: Cambridge Advanced Learner’s Dictionary
checked_at: 2026-10-12
access: Прочитано індексований редакційний фрагмент SHOULD / POSSIBILITY через web search. Пряме відкриття повернуло Internal Error; повне пряме читання не заявлено.
checked_rules: Формальне переважно британське in case + should; немає глобальної заборони модальних форм
use: Коротка видима межа правила, без окремої квоти поглиблення.

url: https://dictionary.cambridge.org/dictionary/english/in-case-of
publisher: Cambridge Advanced Learner’s Dictionary
checked_at: 2026-10-12
access: Прочитано індексовану редакційну словникову статтю через web search; прямого повного відкриття цього URL не заявлено.
checked_rules: In case of може називати і умову виникнення ситуації, і підготовку до неї
use: Запобігає хибній універсальній формулі «in case of завжди реакція лише після події»; власні побутові приклади.

### Pre-freeze review notes

# M48 — In case: редакційна чернетка

Статус: draft для review, не frozen. Дата підготовки: 2026-10-12. Записано тільки два нові draft-файли; definition, runtime, maps, tests, БД та Git не змінювалися. HTTP/browser acceptance не проводилось цим авторським підзавданням.

## Джерело та межі

- Identity: `Database\Seeders\Page_V3\Conditionals\ConditionalsInCaseTheorySeeder`.
- Slug / locale: `in-case / uk`; category за джерелом — `conditionals`.
- URL із чинної навігації: [In case](http://gramlyze.loc/theory/conditionals/in-case). Це source-level destination, не нова заява про HTTP/DB acceptance.
- Display level: `B1-B2`; technical block level: `B1`. Рівні не вирівнювалися механічно.
- Прочитано повністю ROOT і WT `database/seeders/Page_V3/Conditionals/ConditionalsInCaseTheorySeeder/definition.json`, включно з decoded body усіх шести блоків.
- Raw SHA-256 обох original definitions: `4ea07e5d4d74f4149331cba54d07e6908f19836468199434534aed0a8bd0106e`.
- Прочитано AGENTS, canonical template, point-detail guidance, M46/M47 звіти, actual M47 lesson schema та релевантні native normalization / practice rendering / answer-feedback поля.
- Власник продовжує шість первинних slots 0–5: hero, forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips. Type/column/level цих slots збережено. Нові comparison-table та practice-set тільки appended після slot 5.

## Що потрібно читачеві

- Зрозуміти, що in case пояснює пересторогу, а не є звичайною заміною if.
- Відрізнити майбутню можливість від уже актуального, але невідомого стану.
- Розповісти про реальну минулу підготовку, не стверджуючи здійснення можливої події.
- Побудувати in case-частину, заперечення та пряме питання без зайвої інверсії.
- Уживати just in case без непотрібного продовження.
- Після in case of добирати іменникову групу та зберігати часову логіку цілої інструкції.
- За контекстом відрізняти доречність повідомлення від справжньої помилки форми.

Збережено корисну вісь «пересторога — умова», початкові побутові приклади й повний footer. Виправлено не дизайн, а надмірно загальні правила: «завжди дія зараз для майбутнього», «після in case лише Present Simple» та безумовні ❌ для call/stay.

Новий basic містить невідомий теперішній стан, реальну минулу підготовку без твердження про її необхідність, окрему логіку in case of, заперечення та головне питання без інверсії у підрядній частині. Коротка межа формального британського in case + should видима, а не захована за кнопкою.

## Карта чинних компонентів

| Draft index / key | Existing source index | Компонент | Навчальна роль |
| --- | --- | --- | --- |
| 0 / in-case-hero | 0 | hero | Коротке визначення; 3 акценти, 1 EN/UK приклад. |
| 1 / in-case-forms | 1 | forms-grid | 5 карток: present, past, noun group, just in case, питання/заперечення. |
| 2 / in-case-uses | 2 | usage-panels | 6 іменованих пунктів, 10 EN/UK пар; правило + приклади + окрема примітка. |
| 3 / in-case-mistakes | 3 | mistakes-grid | 3 контекстно кваліфіковані помилки форми; wrong/right мають окремі EN/UK поля. |
| 4 / in-case-summary | 4 | summary-list | 5 коротких пунктів, включно з доречними First/Unless links. |
| 5 / in-case-navigation | 5 | navigation-chips | Усі 10 старих destinations і current-позначки без змін. |
| 6 / in-case-timing-comparison | appended | comparison-table | 2 рядки про ту саму парасолю, невідому погоду й момент виконання. |
| 7 / in-case-practice | appended | practice-set | 6 завдань, 6 required controls, 15 option labels. |

Підсумок: 8 source blocks, 5 form cards, 6 visible usage points, 2 table rows, 3 mistakes, 1 meaningful detail, 6 tasks / 6 controls / 15 visible options.

У payload немає CSS-класів, inline styles, executable view names або нової в’юхи. Формули, таблиця та navigation не перетворюються на usage-панелі. EN/UK приклади в hero/forms використовують підтримувані `span lang + br`; usage — `examples[].en/ua`; таблиця — `rows[].en/ua/note`. Mistakes повторюють exact M47 data contract `wrong_en/wrong_ua/right_en/right_ua` з повними bilingual fallback fields.

## Basic/detail

Єдине поглиблення: `in-case-context-not-blacklist`, власник `in-case-uses /sections/3`.

Видимий basic уже пояснює, що if задає умову, а in case — пересторогу, і що саме дієслово call/stay не є доказом помилки. Detail додає дві окремі контекстні ситуації зі старих false-error прикладів; це не коротка прихована примітка і не потрібний переклад, відірваний від видимого прикладу.

- Call: старий рядок з in case збережений як контекстно недостатній приклад, не як універсально правильна заміна if. Реакція лише після виникнення потреби має прозорий if-варіант; для підготовчого контакту наведено нове речення з явним часом дзвінка.
- Stay: рішення вже залишитися вдома з перестороги відрізняється від плану лишитися лише за дощу. Стара пара більше не має безумовного wrong/right framing.

Жодна квота деталей із M47 не копіюється. Питання, час, not, just in case та in case of повністю пояснені в basic.

## Повна карта старих English sentence fields

19 старих sentence-bearing fields містять 20 реченнєвих входжень: один hero field містив дві фрази через « / ». 16 полів збережено, 2 колишні wrong-поля перекласифіковано зі збереженням точного EN, 1 складене поле розділено на два точні речення. Жодне старе англійське речення не вилучено й не переписано. Шляхи AFTER ведуть у draft JSON, не в editorial-переказ.

| № | BEFORE: JSON pointer і точний EN | Статус | AFTER: точні locations | Причина |
| ---: | --- | --- | --- | --- |
| 1 | `/page/blocks/0/body/rules/0/example`<br>Take an umbrella in case it rains. | retained | `/blocks/0/data/rules/0/example`<br>`/blocks/6/data/rows/1/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 2 | `/page/blocks/0/body/rules/1/example`<br>I'll write it down in case I forget. | retained | `/blocks/1/data/items/0/rules/1/example` | Приклад перенесено з hero до картки форми; майбутнє правило явно обмежене звичайним майбутнім контекстом. |
| 3 | `/page/blocks/0/body/rules/2/example`<br>Take an umbrella if it rains. / Take an umbrella in case it rains. | retained-split | `/blocks/6/data/rows/0/en`<br>`/blocks/6/data/rows/1/en` | Два старі речення розділено на рядки штатної comparison-table; службовий розділювач « / » не є навчальним реченням. Обидва тексти й початковий порядок if → in case збережено; час виконання та невідома інформація названі явно. |
| 4 | `/page/blocks/1/body/items/0/subtitle`<br>I'll save the file in case the computer crashes. | retained | `/blocks/1/data/items/0/rules/0/example` | Англійський приклад переміщено зі змішаного subtitle у підтримуваний rules[].example з окремим перекладом; subtitle тепер пояснює правило українською. |
| 5 | `/page/blocks/1/body/items/1/subtitle`<br>I took some cash in case the card didn't work. | retained | `/blocks/1/data/items/1/rules/0/example` | Збережено реальну минулу пересторогу. Українське «не спрацює» передає можливість із тодішньої точки зору, а не стверджує фактичний збій картки. |
| 6 | `/page/blocks/1/body/items/2/subtitle`<br>In case of fire, use the stairs. | retained | `/blocks/1/data/items/2/rules/0/example` | Збережено як мовний приклад оголошення з in case of + noun; окремий переклад додано. |
| 7 | `/page/blocks/1/body/items/3/subtitle`<br>I'll bring a charger just in case. | retained | `/blocks/1/data/items/3/rules/0/example` | Збережено в картці just in case із самодостатнім українським перекладом. |
| 8 | `/page/blocks/2/body/sections/0/examples/0/en`<br>Take a jacket in case it gets cold later. | retained | `/blocks/2/data/sections/0/examples/0/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 9 | `/page/blocks/2/body/sections/0/examples/1/en`<br>I'll send you the address in case you can't find the place. | retained | `/blocks/2/data/sections/0/examples/1/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 10 | `/page/blocks/2/body/sections/1/examples/0/en`<br>If it rains, we'll stay inside. | retained | `/blocks/2/data/sections/3/examples/0/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 11 | `/page/blocks/2/body/sections/1/examples/1/en`<br>We'll take umbrellas in case it rains. | retained | `/blocks/2/data/sections/3/examples/1/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 12 | `/page/blocks/2/body/sections/2/examples/0/en`<br>I'll print one more copy, just in case. | retained | `/blocks/2/data/sections/4/examples/0/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 13 | `/page/blocks/2/body/sections/2/examples/1/en`<br>Put some water in your bag, just in case. | retained | `/blocks/2/data/sections/4/examples/1/en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 14 | `/page/blocks/3/body/items/0/wrong`<br>Call me in case you need help. | reclassified | `/details/0/fragment/data/sections/0/examples/0/en` | Знято безумовне ❌. Фразу збережено як контекстно недостатній старий приклад: без заданого плану вона не є ні універсальною граматичною помилкою, ні рекомендованим механічним синонімом if. Прозорий варіант реакції й явний підготовчий контекст розібрано окремо. |
| 15 | `/page/blocks/3/body/items/0/right`<br>Call me if you need help. | retained | `/details/0/fragment/data/sections/0/examples/1/en` | Збережено для явно заданої реакції лише після виникнення потреби; не названо виправленням будь-якої фрази з call + in case. |
| 16 | `/page/blocks/3/body/items/1/wrong`<br>Take a coat in case it will get cold. | retained | `/blocks/3/data/items/0/wrong`<br>`/blocks/3/data/items/0/wrong_en` | Збережено як помилку саме в цільовому звичайному майбутньому значенні. Немає глобальної заборони всіх інших часових або модальних форм після in case. |
| 17 | `/page/blocks/3/body/items/1/right`<br>Take a coat in case it gets cold. | retained | `/blocks/3/data/items/0/right`<br>`/blocks/3/data/items/0/right_en` | Речення збережено дослівно; додано або уточнено окремий український переклад і відповідний контекст. |
| 18 | `/page/blocks/3/body/items/2/wrong`<br>I'll stay at home in case it rains. | reclassified | `/details/0/fragment/data/sections/1/examples/0/en` | Знято безумовне ❌: залишитися вдома може бути реальною пересторогою щодо можливого дощу. Контекст задає, чи рішення вже прийнято незалежно від підтвердження дощу. |
| 19 | `/page/blocks/3/body/items/2/right`<br>I'll stay at home if it rains. | retained | `/details/0/fragment/data/sections/1/examples/1/en` | Збережено як інший, умовний план дії: за відсутності дощу людина планує вийти. Не підміняє автоматично речення з in case. |

### Чотири старі англійські формули

Окремо від речень збережено всі 4 formula titles. Це формули, не речення; українські пояснення містяться поруч.

| Формула | BEFORE pointer | AFTER pointer | Рішення |
| --- | --- | --- | --- |
| `main clause + in case + present` | `/page/blocks/1/body/items/0/title` | `/blocks/1/data/items/0/title` | Збережено; межі пояснено українською. |
| `in case + past` | `/page/blocks/1/body/items/1/title` | `/blocks/1/data/items/1/title` | Збережено; межі пояснено українською. |
| `in case of + noun` | `/page/blocks/1/body/items/2/title` | `/blocks/1/data/items/2/title` | Збережено; межі пояснено українською. |
| `just in case` | `/page/blocks/1/body/items/3/title` | `/blocks/1/data/items/3/title` | Збережено; межі пояснено українською. |

Англійська назва сторінки In case та 10 англійських navigation labels також не переписуються. Українські переклади уточнено, зокрема can’t → «не зможеш», а можливість у минулій розповіді перекладено з тодішньої точки зору; вони не перетворюють можливу подію на факт.

## Шість власних завдань

| Source index | Native group/index | Навчальна мета | Canonical | Explicit accepted |
| ---: | --- | --- | --- | --- |
| 1 | selects[0] | Звичайна майбутня форма після in case | `gets` | `gets` |
| 2 | selects[1] | Заперечний теперішній стан, можливо актуальний уже зараз | `isn't` | `isn't`<br>`is not` |
| 3 | selects[2] | Минуле повідомлення про реальну пересторогу | `didn't work` | `didn't work`<br>`did not work` |
| 4 | choices[0] | Час виконання дії й невідома поточна інформація | `I'll take a snack in case the café is closed.` | `I'll take a snack in case the café is closed.`<br>`I will take a snack in case the café is closed.` |
| 5 | choices[1] | Пряме питання без інверсії в in case-частині | `Shall we leave early in case the bus is late?` | `Shall we leave early in case the bus is late?` |
| 6 | choices[2] | Перефразування умовної інструкції без підміни часу дії | `In case of rain, use the covered entrance.` | `In case of rain, use the covered entrance.` |

Три selects потрібні для конкретних дієслівних форм: звичайне майбутнє, заперечний можливий стан зараз і минула розповідь. Три choices потрібні для зіставлення часу дії, правильної будови питання та перефразування умовної інструкції. Це вибір за цілями, не правило «завжди 3+3» і не механічне копіювання M47.

У всіх шести завданнях лише один required control. Немає manual/rephrase input або token bank: для цих цілей вистачає вже підтримуваних selects/choices без вгадування авторського дієслова й без зміни grader.

Прочитаний native engine бере accepted із `accepted / answers / answer`, перевіряє EnglishAnswerVariants та нормалізовану відповідь; `feedbackText()` шукає точний `trim().toLowerCase()` raw ключ. Draft задає feedback для кожного видимого варіанта та кожного додаткового explicit alias. Скорочення isn’t/is not, didn’t/did not, I’ll/I will не змінюють ключового значення.

Умови українською; англійські стимули окремі. На кнопках лише відповіді, без перекладу та розбору. Після check кожний варіант має повну правильну EN відповідь, UK переклад і пояснення; для неправильного повного речення вказано його інший або задуманий зміст. До оцінювання не додаються готові порядок слів, answer hint чи placeholder.

Умови прямо задають:
- реальну підготовку перед прогулянкою;
- саме можливу відсутність Марти;
- минулий план обох частин;
- перекус до отримання інформації про кав’ярню;
- питання з in case без вкладеної інверсії;
- накритий вхід лише за фактичного дощу.

Після integration потрібні незалежні actual engine checks і live retry: empty → wrong → wrong → edit → correct → repeat correct → reset. Тут вони не виконувались; авторська чернетка не видається за runtime PASS.

Окремого native no-JS ключа ця чернетка не винаходить. Наявність answers у даних не означає видиму самоперевірку без JavaScript; фактичний fallback і можливості друку треба звітувати окремо, як вимагає M48.

## Сусідні теми й незмінна навігація

| Тема | Source-level local destination | Роль |
| --- | --- | --- |
| Zero Conditional | [/theory/conditionals/zero-conditional](http://gramlyze.loc/theory/conditionals/zero-conditional) | Історичний destination збережено; сусідній урок не змінюється. |
| First Conditional | [/theory/conditionals/first-conditional](http://gramlyze.loc/theory/conditionals/first-conditional) | Доречний наступний крок: майбутня if-умова; також посилання в summary. |
| Second Conditional | [/theory/conditionals/second-conditional](http://gramlyze.loc/theory/conditionals/second-conditional) | Історичний destination збережено; сусідній урок не змінюється. |
| Third Conditional | [/theory/conditionals/third-conditional](http://gramlyze.loc/theory/conditionals/third-conditional) | Історичний destination збережено; сусідній урок не змінюється. |
| Mixed Conditionals | [/theory/conditionals/mixed-conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals) | Історичний destination збережено; сусідній урок не змінюється. |
| Unless / Provided / As long as | [/theory/conditionals/unless-provided-as-long-as](http://gramlyze.loc/theory/conditionals/unless-provided-as-long-as) | Виняток та інші умовні сполучники; також посилання в summary. |
| In case | [/theory/conditionals/in-case](http://gramlyze.loc/theory/conditionals/in-case) | Поточний урок; current = true. |
| Wish / If only | [/theory/conditionals/wish-if-only](http://gramlyze.loc/theory/conditionals/wish-if-only) | Історичний destination збережено; сусідній урок не змінюється. |
| Would rather | [/theory/conditionals/would-rather](http://gramlyze.loc/theory/conditionals/would-rather) | Історичний destination збережено; сусідній урок не змінюється. |
| Had better | [/theory/conditionals/had-better](http://gramlyze.loc/theory/conditionals/had-better) | Історичний destination збережено; сусідній урок не змінюється. |

In case — урок про пересторогу та умову виконання, не дубль First, Unless або поглиблених умовних конструкцій. Нових destinations немає: First і Unless уже були у footer й тепер також пов’язані з конкретною потребою в summary. Немає модернізації Had better, перенесення тем, redirects або зміни категорії.

## Первинні джерела й фактичний доступ

### 1. Cambridge English Grammar Today

[https://dictionary.cambridge.org/us/grammar/british-grammar/in-case-of](https://dictionary.cambridge.org/us/grammar/british-grammar/in-case-of)

Доступ (2026-10-12): Прочитано індексований редакційний текст через web search. Прямі відкриття URL з /grammar/ та /us/grammar/ повернули Internal Error без підтвердженого HTTP-коду; повне пряме читання не заявлено.

Початковий запит: [https://dictionary.cambridge.org/grammar/british-grammar/in-case-of](https://dictionary.cambridge.org/grammar/british-grammar/in-case-of).

Перевірено: Підготовка до можливої ситуації; відмінність від if за інформацією й рішенням; Just in case без підрядної частини; In case of + noun в умовній інструкції. Перевірка правил; приклади й вправи видавця не копіювалися.

### 2. British Council

[https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/wishes-hypotheses](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/wishes-hypotheses)

Доступ (2026-10-12): Сторінку відкрито. Для цього уроку прочитано редакційний розділ Hypotheses / Expressions, зокрема present/future та in case; learner comments і відповіді в comment threads не використано як джерело.

Перевірено: In case може стосуватися теперішньої невідомої ситуації, а не лише події пізніше. Перевірка часової межі визначення. Видавничий приклад не перенесено до уроку.

### 3. Oxford Advanced Learner’s Dictionary

[https://www.oxfordlearnersdictionaries.com/definition/english/interpret](https://www.oxfordlearnersdictionaries.com/definition/english/interpret)

Доступ (2026-10-12): Словникову статтю відкрито; прочитано редакційний приклад минулої дії з in case + Past Simple у значенні усного перекладу. Це приклад уживання, не окремий повний граматичний трактат.

Перевірено: Реальна минула дія може поєднуватися з in case + past form. Незалежне первинне підтвердження минулої конструкції; словникове речення не копіюється.

### 4. Cambridge Advanced Learner’s Dictionary

[https://dictionary.cambridge.org/dictionary/english/should](https://dictionary.cambridge.org/dictionary/english/should)

Доступ (2026-10-12): Прочитано індексований редакційний фрагмент SHOULD / POSSIBILITY через web search. Пряме відкриття повернуло Internal Error; повне пряме читання не заявлено.

Перевірено: Формальне переважно британське in case + should; немає глобальної заборони модальних форм. Коротка видима межа правила, без окремої квоти поглиблення.

### 5. Cambridge Advanced Learner’s Dictionary

[https://dictionary.cambridge.org/dictionary/english/in-case-of](https://dictionary.cambridge.org/dictionary/english/in-case-of)

Доступ (2026-10-12): Прочитано індексовану редакційну словникову статтю через web search; прямого повного відкриття цього URL не заявлено.

Перевірено: In case of може називати і умову виникнення ситуації, і підготовку до неї. Запобігає хибній універсальній формулі «in case of завжди реакція лише після події»; власні побутові приклади.

Публічні граматичні джерела використані для перевірки правил. Нові речення й усі шість вправ написано самостійно; видавничі завдання не копіюються. Пошук повертав також comment threads British Council, але вони не використані як нормативне джерело. Internal Error не перейменовано в HTTP 403 без такого факту.

## Межі цієї чернетки та review

- Авторська робота лише In case, тільки нові `docs/content/m48-drafts/in-case.json` та `in-case-editorial.md` у ROOT.
- Немає source sync, freeze, DB preview/apply, HTTP/browser checks, tests, Git stage/commit/push у цьому підзавданні.
- Старий Call me in case you need help. умисно не отримує категоричної оцінки без контексту; reviewer має перевірити, що final integration зберігає цей обережний статус, а не перетворює його назад на ❌ або універсальний ✅.
- In case of не має універсальної часової заміни in case. Практика перевіряє конкретну умовну інструкцію, не всі можливі вживання сполуки.
- Минулі приклади не доводять, що картка/ручка зламалася або доріжка була темною.
- Frozen лише після окремого main review; поточні drafts допускають редакційні поправки до freeze.

## Уточнення після незалежного review, до freeze

- `/blocks/1/data/items/1/subtitle`: «ситуацію, якої тоді допускали» замінено на «ситуацію, можливість якої тоді допускали». Виправлено українське керування; ідеться про допущену можливість, а не про підтверджене здійснення ситуації.
- `/blocks/1/data/items/0/rules/0/example`: переклад `the computer crashes` уточнено до «комп’ютер дасть збій» замість «комп’ютер зависне». Збій не обов’язково є саме зависанням; англійське речення залишено дослівно.
- `/blocks/6/data/rows/0/note` та `/details/0/fragment/data/sections/1/description`: обмеження «лише за умови дощу» тепер прямо належить заданій інструкції або обраному плану. Саме `if` не створює зворотного висновку «без дощу дію не виконують» і не виключає інших причин узяти парасолю чи залишитися вдома. Усі EN приклади збережено дослівно, обидва підпункти detail залишено; практика, accepted answers і feedback не змінені.


## wish-if-only

### Reader needs

Визначити бажаний час і реальний факт, не зводячи теперішнє до Past Simple або майбутнє до would.

Розрізняти стан, процес, минулий жаль, можливість і бажану зміну.

Зберігати того, хто бажає, того, хто діяв, час та заперечення в англійському прикладі й перекладі.

Розуміти доречність was/were та не сприймати єдиний canonical key як заборону іншого нормативного варіанта.

Вживати самостійне If only; відрізняти hope та коротко впізнавати wish someone / wish to.

Потренувати шість цілей з окремим post-check перекладом і поясненням, не читаючи ключ у кнопках.

### Exact editorial mapping

change: hero-semantics-and-translations
before: Hero подає Past Simple для теперішнього й wish + would переважно як зміну чиєїсь поведінки; приклади без перекладів.
after: wish-hero розділяє реальність і бажання; wish-forms додає Continuous та could. Усі три старі hero EN речення збережено з окремими перекладами.
reason: Не всі теперішні ситуації є станами; погода й предмети також допускають would. Hero не дублює детальну таблицю форм.
english_fields: before_path: /page/blocks/0/body#/rules/0/example
before: I wish I knew the answer.
after_path: /blocks/0/data/rules/0/example
action: Exact EN retained; UK translation and present reality added.

before_path: /page/blocks/0/body#/rules/1/example
before: If only I had listened to you.
after_path: /blocks/0/data/rules/1/example
action: Exact EN retained; past non-listening and UK translation explicit.

before_path: /page/blocks/0/body#/rules/2/example
before: I wish the neighbours would be quieter.
after_path: /blocks/0/data/rules/2/example
action: Exact EN retained; speaker/neighbours and intended behavioural change clarified.

change: forms-chosen-by-four-meanings
before: Чотири картки: wish + Past Simple, Past Perfect, would та If only; intro називає лише три схеми.
after: Чотири реальні смислові моделі: present state/process, past regret, desired change, ability/possibility. If only перенесено до самостійного usage-пункту; це не п’ята часова форма.
reason: Кількість карток визначена потрібними формами, не повторенням еталона. Усі чотири старі приклади збережено.
english_fields: before_path: /page/blocks/1/body#/items/0/subtitle
before: I wish I had more time.
after_path: /blocks/1/data/items/0/rules/0/example
action: Exact EN retained with full UK translation.

before_path: /page/blocks/1/body#/items/1/subtitle
before: I wish I had studied harder.
after_path: /blocks/1/data/items/1/rules/0/example
action: Exact EN retained; past exam preparation context and UK translation added.

before_path: /page/blocks/1/body#/items/2/subtitle
before: I wish he would answer my messages.
after_path: /blocks/1/data/items/2/rules/0/example
action: Exact EN retained with UK translation; wouldn't pair added separately.

before_path: /page/blocks/1/body#/items/3/subtitle
before: If only we lived closer.
after_path: /blocks/6/data/sections/0/examples/0/en
action: Exact EN moved, not deleted; independent utterance explained with translation.

change: usage-kept-context-qualified
before: Три usage-пункти: теперішнє, минуле, роздратування/зміна; would названо «проханням до світу змінитися».
after: Усі шість EN/UK пар збережено в тих самих трьох пунктах. Описи називають реальну ситуацію, мовця й виконавця; додано коротку register note та четвертий could-пункт.
reason: Замість метафоричного пояснення — конкретні умови; короткі застереження й переклади залишаються у basic.
english_fields: before_path: /page/blocks/2/body#/sections/0/examples/0/en
before: I wish I were taller.
after_path: /blocks/2/data/sections/0/examples/0/en
action: Exact EN/UK pair retained.

before_path: /page/blocks/2/body#/sections/0/examples/1/en
before: If only we had more space in this flat.
after_path: /blocks/2/data/sections/0/examples/1/en
action: Exact EN/UK pair retained.

before_path: /page/blocks/2/body#/sections/1/examples/0/en
before: I wish I had called her earlier.
after_path: /blocks/2/data/sections/1/examples/0/en
action: Exact EN/UK pair retained; actual missed earlier call explicit.

before_path: /page/blocks/2/body#/sections/1/examples/1/en
before: If only they had checked the tickets first.
after_path: /blocks/2/data/sections/1/examples/1/en
action: Exact EN/UK pair retained; wisher and actors distinguished.

before_path: /page/blocks/2/body#/sections/2/examples/0/en
before: I wish it would stop raining.
after_path: /blocks/2/data/sections/2/examples/0/en
action: Exact EN/UK pair retained; weather is not restricted to human willingness.

before_path: /page/blocks/2/body#/sections/2/examples/1/en
before: I wish my phone would work properly.
after_path: /blocks/2/data/sections/2/examples/1/en
action: Exact EN/UK pair retained; malfunction and desired change explicit.

before_path: /page/blocks/2/body#/sections/2/note
before: Цю форму не вживають для стабільних фактів про себе на кшталт <em>I wish I would be taller</em>.
after_path: /blocks/3/data/items/2
action: Duplicate error phrase moved to the contextualised mistakes pair; current basic note states limits without an untranslated duplicate.

change: old-errors-retained-with-explicit-realities
before: Know/knew, knew yesterday/had known yesterday та would be taller/were taller оцінюються короткими загальними правилами.
after: Усі шість EN strings збережено як цільові wrong/right пари, але кожну прив’язано до точного задуму: невідоме зараз; учорашня завершена вікторина; наявний зріст. Для кожної пари додано переклад задуму й пояснення меж.
reason: Не діагностуємо за одним yesterday, не стираємо його і не створюємо глобальну заборону wish + would або was.
english_fields: before_path: /page/blocks/3/body#/items/0/wrong
before: I wish I know the answer.
after_path: /blocks/3/data/items/0/wrong_en
action: Exact EN retained; desired unreal present knowledge explicitly required.

before_path: /page/blocks/3/body#/items/0/right
before: I wish I knew the answer.
after_path: /blocks/3/data/items/0/right_en
action: Exact EN retained, translated; knew refers to the desired present.

before_path: /page/blocks/3/body#/items/1/wrong
before: I wish I knew the answer yesterday.
after_path: /blocks/3/data/items/1/wrong_en
action: Exact EN retained for an explicitly completed past knowledge episode, not judged by yesterday alone.

before_path: /page/blocks/3/body#/items/1/right
before: I wish I had known the answer yesterday.
after_path: /blocks/3/data/items/1/right_en
action: Exact EN retained; yesterday and the actor remain intact.

before_path: /page/blocks/3/body#/items/2/wrong
before: I wish I would be taller.
after_path: /blocks/3/data/items/2/wrong_en
action: Exact EN retained only for a wish about current height, not a claim about every possible would context.

before_path: /page/blocks/3/body#/items/2/right
before: I wish I were taller.
after_path: /blocks/3/data/items/2/right_en
action: Exact EN retained; acceptable informal was explicitly acknowledged in basic and practice.

change: summary-and-subtitle-expanded-without-new-design
before: Subtitle: «Жаль про теперішнє, жаль про минуле і бажана зміна». Summary: Past Simple, Past Perfect, would, If only.
after: Subtitle і summary враховують present process, could, would/wouldn't та самостійне If only. Додано компактний hope comparison й note про wish someone/wish to.
reason: Закрити відсутні потреби читача через наявні поля й компоненти, без SEO-повторів чи нової верстки.

change: one-owned-detail-not-a-quota
before: Окремих point-level details у старому definition немає.
after: Одна detail wish-state-or-desired-change належить wish-uses /sections/2; пояснює перетин state/change на предметі. Усі основні правила, переклади й register note залишено visible.
reason: Поглиблення має власний зміст і допомагає уникнути хибного single-key вибору; кількість не копіює M47.

change: six-native-practice-goals
before: Авторської practice-set у definition немає.
after: Додано 3 selects і 3 choices: continuous-now alternatives, negative past, could, hope contrast, wouldn't change, If-only paraphrase. 6 controls і 15 видимих options; was/were обидва приймаються у першій вправі.
reason: Форми зручні для select, вибір наміру й точне перефразування — для choice. Це не квота карток або копія M47; нова механіка не потрібна.

change: identity-metadata-and-navigation-preserved
before: UK wish-if-only, display B1-B2; старі slots 0..5: hero/header/B1, forms-grid/left/B1, usage-panels/left/B1, mistakes-grid/right/B1, summary-list/right/B1, navigation-chips/footer без level.
after: Ті самі identity, locale, slug, рівні, type/column старих шести slots і всі navigation data; три нові блоки додано після них.
reason: Немає нових маршрутів, редизайну чи змін сусідніх уроків. Existing source indices збережено для стабільних continuing block IDs.

### Primary sources and access

url: https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/wishes-hypotheses
publisher: British Council LearnEnglish
checked_at: 2026-10-12
access: Пряме успішне відкриття; прочитано редакційний розділ Wishes, включно з simple/continuous, was/were, could/would та Past Perfect. Коментарі не використовувалися як норма.
checked_rules: Past Simple і Past Continuous для теперішнього бажання

Was/were допустимі після I/he/she/it

Past Perfect для минулого жалю

Could/would для бажаної можливості або зміни
use: Звірка правил; нові приклади й вправи авторські, старі приклади збережені з definition.

url: https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/wishes-wish-if-only
publisher: British Council LearnEnglish
checked_at: 2026-10-12
access: Пряме успішне відкриття й читання редакційного Grammar explanation; learner comments та їхні відповіді не використано як нормативний доказ.
checked_rules: If only зазвичай сильніше емоційно

Would(n't) про те, що робить або не робить someone or something

Погода не виключена з моделі бажаної зміни
use: Уточнення меж would/wouldn't без твердження, що зміна гарантовано неможлива назавжди.

url: https://dictionary.cambridge.org/grammar/british-grammar/wish
publisher: Cambridge Dictionary, English Grammar Today
checked_at: 2026-10-12
access: Прямі open двічі повернули Internal Error без доступного HTTP-коду. Редакційний текст Wish прочитано в пошуковому індексі Cambridge; це не повне пряме читання сторінки й не підтверджений 403.
checked_rules: Wish + clause і past forms

Wish + would

Hope для відкритої надії

Wish to як формальніше want

Wish + адресат + побажання
use: Звірка значень і конструкцій; приклади та вправи видавця не копіюються.

url: https://dictionary.cambridge.org/grammar/british-grammar/if-only
indexed_url: https://dictionary.cambridge.org/grammar/british-grammar/if-only%29
publisher: Cambridge Dictionary, English Grammar Today
checked_at: 2026-10-12
access: Чистий URL при direct open повернув HTTP 403. Прочитано індексований редакційний текст видавця під варіантом URL із %29; статус доступу не підмінюється прямим успіхом.
checked_rules: Самостійне сильне бажання If only

Past forms, Past Perfect, would

Were у формальніших ситуаціях замість was
use: Перевірка регістру й відсутності обов’язкового conditional-result.

url: https://dictionaryblog.cambridge.org/2017/11/28/i-wish-id-studied-harder-expressing-regrets-and-wishes/
indexed_url: https://dictionaryblog.cambridge.org/2017/11/28/i-wish-id-studied-harder-expressing-regrets-and-wishes/?amp=1
publisher: Cambridge Dictionary Blog
checked_at: 2026-10-12
access: Direct open повернув HTTP 429 Too Many Requests. Доступний первинний індексований уривок редакційної статті про were/was; повна стаття напряму не прочитана.
checked_rules: Were доречне у формальному письмі; was прийнятне в розмові й неформальному письмі
use: Додаткова вузька перевірка регістру, не джерело неперевірених правил з решти статті.

### Pre-freeze review notes

# M48 — Wish / If only: авторська чернетка

Статус: draft для незалежного перегляду main перед freeze. Тільки UK `wish-if-only`, identity `Database\Seeders\Page_V3\Conditionals\ConditionalsWishIfOnlyTheorySeeder`. Очікуваний local URL — `/theory/conditionals/wish-if-only`; живий resolver/БД у цій підзадачі не перевірялися.

Старі definitions ROOT і WT прочитані повністю, body JSON декодовано. Обидва файли ідентичні: SHA-256 `b9bebb72348f177022b29b12a345147e84776942d588f13dd36261235f46824b`. Це file-source receipt, не підміна незалежного source/DB BEFORE, який збирає main.

## Обсяг і структура

9 native-блоків: 6 continuing slots + самостійне If only (usage), короткий wish/hope comparison-table і practice-set. Перші шість індексів, type, column та B1 technical metadata збережено; hero display B1–B2. Navigation без level, усі її URL та current flags без змін. Нові блоки додано після старих шести, footer залишається footer.

Чотири form-картки обрано за чотирма потребами: бажаний стан/процес, минула подія, зміна, здатність/можливість. Це не копіювання кількості з еталона. Present-картка має два смислові рядки: Simple і Continuous. If only не оголошується окремим п’ятим часом.

У basic видно всі потрібні правила, повні переклади, was/were register note, обмеження would та короткі інші значення wish. Одна detail прив’язана до `wish-uses` → `/sections/2`: змістовне порівняння бажаного стану предмета й бажаної зміни його роботи. Вона не приховує необхідний переклад і не створена за квотою M47.

## Старі приклади й точний журнал

Усі 13 корисних старих англійських прикладів із hero/forms/usage збережено; шість usage EN/UK пар не переписувалися. Старе `If only we lived closer.` переміщене з forms до самостійного If-only пункту, а не вилучене. У `editorial_changes[].english_fields` є 20 exact old-field mappings: 13 корисних examples, 6 wrong/right strings та duplicate wrong phrase зі старої usage note.

Позначення `body#` у before paths означає поле після декодування JSON-рядка body. After paths — звичайні JSON pointers від кореня нової чернетки. Для inline bilingual after field оригінальне EN речення є незмінним текстом усередині `span lang=en`; HTML wrapper не є частиною старого речення.

Три старі помилки тепер мають явний задум:

- `I wish I know the answer.`: я не знаю відповіді зараз і хочу знати; потрібне `knew`.
- `I wish I knew the answer yesterday.`: учора завершилася конкретна вікторина, тоді я не знав відповіді; сьогодні вже знаю й шкодую про той момент. Потрібне `had known`, а не механічне правило за одним `yesterday` чи його вилучення.
- `I wish I would be taller.`: задано інший теперішній зріст, не очікувану майбутню зміну; показано `were`, а доречне неформальне `was` не забороняється.

Would не описано як будь-яке майбутнє бажання або універсальний спосіб змінити власну характеристику. Збережені старі приклади про дощ і телефон прямо показують, що ця модель не обмежена людьми. Could відділяє здатність/можливість від простого бажаного факту. У hope comparison відома перешкода відрізняється від відкритої надії, а не від будь-якої згадки завтра.

## Практика за навчальними цілями

6 controls у 6 source-index задачах, 15 видимих options: 3 selects × 3 + 3 choices × 2. Розподіл 3+3 випливає з цілей, не є новою квотою для інших уроків.

1. Процес зараз: `were/was sitting`. Обидві видимі кнопки `were` та `was` правильні в заданій неформальній ситуації; accepted містить обидва. `am` не відповідає цільовій past-form моделі.
2. Заперечний минулий жаль: `hadn't left`, також `hadn’t left` та `had not left`.
3. Недоступна можливість завтра: `could`, не `can/will`.
4. Контраст wish/hope за відомістю обставин і наміром мовця.
5. `wouldn't` для бажаної зміни повторюваної роботи принтера, а не одноразового минулого жалю.
6. Перефразування wish → самостійне If only із збереженням мовця, часу та not.

Кожний видимий option має exact feedback key. Після Check feedback дає правильне повне EN речення, точний український переклад і причину; для двох семантично різних речень перекладено обидва. У задачі перефразування перекладено також вихідне речення. Кнопки — короткі форми або A/B, без навчального ключа.

Перевірено читанням чинний native engine: `accepted` підтримує альтернативи; feedback прив’язаний до raw lowercase option. Manual/rephrase API також переглянуто: для довільної непередбаченої відповіді fallback не дає повного авторського перекладу й причини. Тому довільне ручне введення не додається лише заради різноманітності; selected-response повністю відповідає цим шести цілям без нової механіки. Жодного нового поля з вигаданою runtime-поведінкою немає.

Важливо для інтеграційних тестів: не припускати, що рівно один visible option правильний. Перша задача навмисно має дві прийнятні відповіді, але рахується як один control і максимум один бал. Не виводити список accepted або canonical як початковий hint.

## Перевірені першоджерела

[British Council: Wishes and hypotheses](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/wishes-hypotheses) — успішне пряме читання редакційного Wishes. Звірено Continuous, Past Perfect, could/would і допустимість was/were. [British Council: Wishes — wish and if only](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/wishes-wish-if-only) — також пряме читання редакційного пояснення: would(n't) про людину або предмет/ситуацію, сильніше If only. Коментарі не є нормативною основою.

[Cambridge: Wish](https://dictionary.cambridge.org/grammar/british-grammar/wish) — direct open двічі повернув Internal Error без доступного HTTP-коду; прочитано індексований редакційний текст. Це не названо повним прямим доступом чи підтвердженим 403. Звірено wish-clause, hope, wish to і побажання адресатові.

[Cambridge: If only](https://dictionary.cambridge.org/grammar/british-grammar/if-only) — прямий HTTP 403; індексований текст видавця доступний за варіантом URL з `%29`. [Cambridge editorial blog про wish](https://dictionaryblog.cambridge.org/2017/11/28/i-wish-id-studied-harder-expressing-regrets-and-wishes/) — прямий HTTP 429; доступний лише первинний індексований уривок про формальніше were й прийнятне неформальне was. Це вузький доказ регістру, а не прочитання всього blog article.

Нові приклади й вправи авторські; видавничі завдання не скопійовано. Reader needs — редакційний аналіз, не вигадані keyword volumes чи ranking lift.

## Межі виконаного

Main review до freeze: hero intro починається точним «Wish / If only», щоб чинний metadata service не додавав повторний topic-prefix; зміст не змінено. Із title sidecar вилучено повторне «Докладніше:», оскільки підпис уже належить штатному disclosure. Англійські приклади й практика незмінні.

Створено тільки JSON і цей editorial файл. Definitions, попередні masters/maps, shared PHP/Blade/CSS/JS, БД, банки, курси та тести не редагувалися; HTTP/браузерні перевірки не виконувалися. Live apply, metadata, destinations, screenshots, retry/reset і JS-off/print лишаються завданнями main. Чернетка не заявляє нового no-JS ключа або автооцінювання без JavaScript.


## would-rather

### Reader needs

question: Коли після would rather потрібна базова форма, а коли Past Simple?
answer_location: m48-rather-forms /items/0 та /items/2
basis: Дві різні моделі у чинному уроці потребують явного розрізнення виконавців; не дані пошукової частотності.

question: Чому came може стосуватися tomorrow?
answer_location: m48-rather-uses /sections/3 та m48-rather-variation
basis: Стара безумовна позначка помилки змішувала модель уроку з усіма варіантами англійської.

question: Як відмовитися самому, не забороняючи дію іншому?
answer_location: m48-rather-uses /sections/2; m48-rather-practice /choices/2
basis: Практична різниця між I'd rather not та іншою особою.

question: Як зберегти вибір і виконавця під час перефразування?
answer_location: m48-rather-practice /choices/0 та /choices/1
basis: Смислова точність: напрям переваги, заперечення, адресат і час.

question: Як висловити перевагу щодо минулого?
answer_location: m48-rather-past-preferences
basis: Поглиблення відокремлене від самодостатнього basic про теперішнє й майбутнє.

### Exact editorial mapping

source_pointer: /page/subtitle_html
before: <p><strong>Would rather</strong> показує перевагу: що ми воліємо зробити самі або що хотіли б, щоб зробив хтось інший.</p>
after: subtitle_html
reason: Уточнено читачеві тему без зміни H1, slug, locale чи категорії; охоплено заперечення, запитання та коротку відмову.

source_pointer: /page/subtitle_text
before: Перевага зараз або в майбутньому з would rather.
after: subtitle_text
reason: Метадані описують реальну навчальну користь; немає нових зовнішніх SEO-обіцянок.

source_pointer: /page/blocks/0/body/intro
before: <strong>Would rather</strong> допомагає сказати, що ми <strong>воліємо один варіант більше за інший</strong>. Конструкція працює і для власної дії, і для ситуації, коли ми хочемо, щоб хтось інший поводився інакше.
after: m48-rather-hero /intro
reason: Скорочено вступ, головним орієнтиром зроблено виконавця; конструкція про іншу людину не обов’язково означає вимогу поводитися інакше.

source_pointer: /page/blocks/0/body/rules/0/text
before: Для власної дії ставимо <strong>would rather + base verb</strong>.
after: m48-rather-hero /rules/0/text
reason: Правило збережено, подано коротшою українською формою.

source_pointer: /page/blocks/0/body/rules/0/example
before: I'd rather stay home tonight.
after: m48-rather-hero /rules/0/example
reason: Повне старе EN речення збережено дослівно; додано окремий UK переклад: Я б краще залишився вдома сьогодні ввечері.

source_pointer: /page/blocks/0/body/rules/1/text
before: Для заперечення вживаємо <strong>would rather not + base verb</strong>.
after: m48-rather-hero /rules/1/text
reason: Правило збережено, подано коротшою українською формою.

source_pointer: /page/blocks/0/body/rules/1/example
before: I'd rather not talk about it now.
after: m48-rather-hero /rules/1/example
reason: Повне старе EN речення збережено дослівно; додано окремий UK переклад: Я б волів не говорити про це зараз.

source_pointer: /page/blocks/0/body/rules/2/text
before: Коли хочемо, щоб щось зробив хтось інший, ставимо <strong>would rather + someone + past simple</strong>.
after: m48-rather-hero /rules/2/text
reason: Past Simple позначено як навчальну модель, а не єдину можливість у всіх різновидах англійської.

source_pointer: /page/blocks/0/body/rules/2/example
before: I'd rather you came earlier tomorrow.
after: m48-rather-hero /rules/2/example
reason: Повне старе EN речення збережено дослівно; додано окремий UK переклад: Я б волів, щоб ти завтра прийшов раніше.

source_pointer: /page/blocks/1/body/items/0/title
before: would rather + base verb
after: m48-rather-forms /items/0/title
reason: Формулу уточнено за виконавцем; англійські службові елементи збережено, base verb / someone описано українською.

source_pointer: /page/blocks/1/body/items/0/subtitle
before: I'd rather take the train.
after: m48-rather-forms /items/0/rules/0/example
reason: Повне старе EN речення збережено дослівно й переведено в штатне bilingual example; subtitle тепер коротко пояснює форму.

source_pointer: /page/blocks/1/body/items/1/title
before: would rather not + base verb
after: m48-rather-forms /items/1/title
reason: Формулу уточнено за виконавцем; англійські службові елементи збережено, base verb / someone описано українською.

source_pointer: /page/blocks/1/body/items/1/subtitle
before: We'd rather not wait outside.
after: m48-rather-forms /items/1/rules/0/example
reason: Повне старе EN речення збережено дослівно й переведено в штатне bilingual example; subtitle тепер коротко пояснює форму.

source_pointer: /page/blocks/1/body/items/2/title
before: would rather + someone + past simple
after: m48-rather-forms /items/2/title
reason: Формулу уточнено за виконавцем; англійські службові елементи збережено, base verb / someone описано українською.

source_pointer: /page/blocks/1/body/items/2/subtitle
before: I'd rather he stayed here.
after: m48-rather-forms /items/2/rules/0/example
reason: Повне старе EN речення збережено дослівно й переведено в штатне bilingual example; subtitle тепер коротко пояснює форму.

source_pointer: /page/blocks/1/body/items/3/title
before: Would you rather ...?
after: m48-rather-forms /items/3/title
reason: Формулу уточнено за виконавцем; англійські службові елементи збережено, base verb / someone описано українською.

source_pointer: /page/blocks/1/body/items/3/subtitle
before: Would you rather go now or later?
after: m48-rather-forms /items/3/rules/0/example
reason: Повне старе EN речення збережено дослівно й переведено в штатне bilingual example; subtitle тепер коротко пояснює форму.

source_pointer: /page/blocks/2/body/sections/0/description
before: Порівнюємо два варіанти і показуємо, що один з них для нас кращий.
after: m48-rather-uses /sections/0/description
reason: Збережено вибір між діями; уточнено паралельну форму після rather / than і межу would prefer.

source_pointer: /page/blocks/2/body/sections/0/examples/0
before: en: I'd rather walk than wait for the bus.
ua: Я б краще пішов пішки, ніж чекав автобус.
after: m48-rather-uses /sections/0/examples/0
reason: Стару EN/UK пару збережено дослівно.

source_pointer: /page/blocks/2/body/sections/0/examples/1
before: en: She'd rather study at home tonight.
ua: Вона б краще повчилася сьогодні вдома.
after: m48-rather-uses /sections/0/examples/1
reason: EN збережено дослівно; UK явно зберігає вечір у tonight.

source_pointer: /page/blocks/2/body/sections/1/description
before: Так м'яко показуємо, якої дії очікуємо від іншої людини.
after: m48-rather-uses /sections/1/description
reason: Знято безумовне «м’яко»: пояснено виконавця, місце заперечення й відсутність гарантії згоди.

source_pointer: /page/blocks/2/body/sections/1/examples/0
before: en: I'd rather you didn't mention this to Alex.
ua: Я б волів, щоб ти не згадував про це Алексу.
after: m48-rather-uses /sections/1/examples/0
reason: Стару EN/UK пару збережено дослівно.

source_pointer: /page/blocks/2/body/sections/1/examples/1
before: en: We'd rather the children stayed inside.
ua: Ми б воліли, щоб діти залишилися всередині.
after: m48-rather-uses /sections/1/examples/1
reason: Стару EN/UK пару збережено дослівно.

source_pointer: /page/blocks/2/body/sections/2/description
before: Форма <strong>Would you rather...?</strong> допомагає ввічливо запропонувати вибір.
after: m48-rather-uses /sections/2/description
reason: Знято автоматичну ввічливість; додано short response без перетворення його на обов’язково ввічливу формулу.

source_pointer: /page/blocks/2/body/sections/2/examples/0
before: en: Would you rather sit by the window?
ua: Ти б волів сидіти біля вікна?
after: m48-rather-uses /sections/2/examples/0
reason: Стару EN/UK пару збережено дослівно.

source_pointer: /page/blocks/2/body/sections/2/examples/1
before: en: Would you rather meet on Friday or Saturday?
ua: Тобі більше підходить п'ятниця чи субота?
after: m48-rather-uses /sections/2/examples/1
reason: EN збережено дослівно; UK відновлює дію meet, яку старий переклад «підходить п’ятниця чи субота» не називав.

source_pointer: /page/blocks/2/body/sections/1/label
before: Would rather someone did
after: m48-rather-uses /sections/1/label
reason: English формульний заголовок замінено короткою українською смисловою назвою; сама формула залишається у forms.

source_pointer: /page/blocks/3/body/items/0/wrong
before: I'd rather to stay home.
after: m48-rather-mistakes /items/0/wrong_en
reason: Граматична помилка to збережена; додано окремі UK значення. Для неправильної форми переклад явно позначений як намір, не як оцінка граматичності української.

source_pointer: /page/blocks/3/body/items/0/right
before: I'd rather stay home.
after: m48-rather-mistakes /items/0/right_en
reason: Граматична помилка to збережена; додано окремі UK значення. Для неправильної форми переклад явно позначений як намір, не як оцінка граматичності української.

source_pointer: /page/blocks/3/body/items/0/title
before: Додавати <strong>to</strong> після <strong>would rather</strong>.
after: m48-rather-mistakes /items/0
reason: Граматична помилка to збережена; додано окремі UK значення. Для неправильної форми переклад явно позначений як намір, не як оцінка граматичності української.

source_pointer: /page/blocks/3/body/items/0/hint
before: Після <strong>would rather</strong> ставимо початкову форму без <strong>to</strong>.
after: m48-rather-mistakes /items/0
reason: Граматична помилка to збережена; додано окремі UK значення. Для неправильної форми переклад явно позначений як намір, не як оцінка граматичності української.

source_pointer: /page/blocks/3/body/items/1/wrong
before: I'd rather you come tomorrow.
after: m48-rather-variation /rows/1/en
reason: Come / came пара збережена дослівно, але вилучена з категоричних помилок. Past form залишається моделлю уроку; base form має обмежено описану американську варіантність.

source_pointer: /page/blocks/3/body/items/1/right
before: I'd rather you came tomorrow.
after: m48-rather-variation /rows/0/en
reason: Come / came пара збережена дослівно, але вилучена з категоричних помилок. Past form залишається моделлю уроку; base form має обмежено описану американську варіантність.

source_pointer: /page/blocks/3/body/items/1/title
before: Ставити present simple після <strong>someone</strong>.
after: m48-rather-variation /rows
reason: Come / came пара збережена дослівно, але вилучена з категоричних помилок. Past form залишається моделлю уроку; base form має обмежено описану американську варіантність.

source_pointer: /page/blocks/3/body/items/1/hint
before: Після <strong>would rather + someone</strong> зазвичай іде past simple.
after: m48-rather-variation /rows
reason: Come / came пара збережена дослівно, але вилучена з категоричних помилок. Past form залишається моделлю уроку; base form має обмежено описану американську варіантність.

source_pointer: /page/blocks/3/body/items/2/wrong
before: I rather stay home today.
after: m48-rather-mistakes /items/1/wrong_en
reason: Обидва EN речення збережені. Причина виправлення — пропуск would у цільовій конструкції, не нібито факт теперішнього часу.

source_pointer: /page/blocks/3/body/items/2/right
before: I'd rather stay home today.
after: m48-rather-mistakes /items/1/right_en
reason: Обидва EN речення збережені. Причина виправлення — пропуск would у цільовій конструкції, не нібито факт теперішнього часу.

source_pointer: /page/blocks/3/body/items/2/title
before: Плутати перевагу з фактом про теперішнє.
after: m48-rather-mistakes /items/1
reason: Обидва EN речення збережені. Причина виправлення — пропуск would у цільовій конструкції, не нібито факт теперішнього часу.

source_pointer: /page/blocks/3/body/items/2/hint
before: У природній англійській тут зазвичай потрібна форма <strong>would rather</strong>.
after: m48-rather-mistakes /items/1
reason: Обидва EN речення збережені. Причина виправлення — пропуск would у цільовій конструкції, не нібито факт теперішнього часу.

source_pointer: /page/blocks/4/body/items
before: <strong>Would rather + base verb</strong> = моя перевага.

<strong>Would rather not + base verb</strong> = небажаний варіант.

<strong>Would rather + someone + past simple</strong> = бажана дія іншої людини.

Після <strong>would rather</strong> не ставимо <strong>to</strong>.
after: m48-rather-summary /items
reason: Корисні моделі збережено. Загальне «після would rather не ставимо to» звужено до базової моделі; додано перевірку виконавця, часу й комунікативного наміру.

source_pointer: /page/blocks/5/body
before: title: Сторінки цього розділу
items: label: Zero Conditional
url: /theory/conditionals/zero-conditional
current: false

label: First Conditional
url: /theory/conditionals/first-conditional
current: false

label: Second Conditional
url: /theory/conditionals/second-conditional
current: false

label: Third Conditional
url: /theory/conditionals/third-conditional
current: false

label: Mixed Conditionals
url: /theory/conditionals/mixed-conditionals
current: false

label: Unless / Provided / As long as
url: /theory/conditionals/unless-provided-as-long-as
current: false

label: In case
url: /theory/conditionals/in-case
current: false

label: Wish / If only
url: /theory/conditionals/wish-if-only
current: false

label: Would rather
url: /theory/conditionals/would-rather
current: true

label: Had better
url: /theory/conditionals/had-better
current: false
after: m48-rather-navigation /data
reason: Усі 10 старих labels, URLs, порядок і current-позначка збережені без змін. Had better, Wish / If only та решта сусідів лише згадані як чинні destinations.

source_pointer:
before:
after: m48-rather-forms /items/3/rules/1 та m48-rather-mistakes /items/2
reason: Додано пряме запитання про іншого виконавця й виправлення подвійної інверсії; старі корисні приклади не вилучені.

source_pointer:
before:
after: m48-rather-uses /sections/3 та m48-rather-past-preferences
reason: Visible basic пояснює future-time past form; єдиний detail поглиблює цей пункт через дві різні моделі минулої переваги з явними завершеними фактами.

source_pointer:
before:
after: m48-rather-practice
reason: 6 власних selected-response завдань: 3 select + 3 choice, 6 controls, 15 варіантів. Вибір механік відповідає навчальним цілям, не квоті; кожен варіант має post-check EN/UK feedback. Inputs/rephrase не додаються, оскільки вільний wrong fallback чинного engine не гарантує повного UK перекладу.

source_pointer: pre-freeze draft /blocks/7/data/choices/2/label; /blocks/7/data/choices/2/feedback; /practice_review/5/ambiguity
before: UK tonight's party: «сьогодні на вечірці»; review: «дозволяє співрозмовнику співати».
after: UK tonight's party: «сьогодні ввечері на вечірці» в умові та обох feedback; review: «мовець не заперечує проти співу співрозмовника».
reason: Поправка до freeze явно зберігає вечірній час tonight і не підмінює відсутність заперечення наданням дозволу. Усі EN речення, варіанти, canonical та accepted-відповіді незмінні.

### Primary sources and access

url: https://dictionary.cambridge.org/grammar/british-grammar/would-rather-would-sooner
access_date: 2026-10-12
access: Direct UK and /us/ opens returned 403; official indexed page text read through web search.
claims: Розрізнення власної дії та іншого підмета; past form із теперішнім/майбутнім значенням; short response I'd rather not.

Для минулого: власна дія would rather have + V3, інший підмет + Past Perfect.
use: Граматичні межі; приклади уроку збережені з локального джерела або авторські, не скопійовані з довідника.

url: https://continuingstudies.uvic.ca/elc/studyzone/410/grammar/410-modals-of-preference
access_date: 2026-10-12
access: Direct open succeeded; full editorial lesson body read.
claims: Порівняння через than; not після rather для власної дії; інший підмет + didn't + базова форма.

Past Simple після іншого підмета може означати теперішнє чи майбутнє; скорочення з would.
use: Незалежна університетська перевірка моделей, не копіювання вправ.

url: https://dictionary.cambridge.org/grammar/british-grammar/prefer-would-prefer
access_date: 2026-10-12
access: Direct open returned 403; official indexed editorial page text read through web search.
claims: Would prefer допускає to-infinitive; для іншої особи — object + to-infinitive, зокрема заперечна дія.
use: Межа конструкцій у видимому basic та смисл контрольованого перефразування №5.

url: https://englishwithjennifer.wordpress.com/2019/03/01/grammar-wed-rather-avoid-variations-with-would-rather/
access_date: 2026-10-12
access: Direct open succeeded. Read author Jennifer Lebedev's original editorial discussion and reported COCA observations; not reader comments.
claims: Автор фіксує в американському вживанні і past form, і base form з іншим підметом.
use: Обмежена первинна редакційна підстава зняти універсальний ❌ з come. Це не твердження про однакову прийнятність у всіх діалектах і не власний кількісний корпусний аналіз; припущення автора про різний ступінь майбутньої реальності не перенесене.

### Pre-freeze review notes

# M48 draft — Would rather

Status: draft for independent editorial review; not frozen, not applied to a database.

## Scope and provenance

- Owner: `Database\Seeders\Page_V3\Conditionals\ConditionalsWouldRatherTheorySeeder`.
- Local destination: `/theory/conditionals/would-rather`.
- Locale: UK; display level B1–B2; technical block level B1.
- Existing source: `database/seeders/Page_V3/Conditionals/ConditionalsWouldRatherTheorySeeder/definition.json`.
- ROOT and WT old-source SHA-256 both: `4118b2cc5a90676a6a0b3da8df7e95c34eaf006e291fdba9a266168048dc104f`.
- Draft JSON SHA-256: `faf44d12c33eaf83e6f3460fd0b2682f280984cf10eedb37f3c746d77f650271`.
- The six existing slots retain source indices 0–5, type, column and technical level. Comparison and practice are appended, not inserted into those slots. The existing footer still belongs to navigation.
- All 10 old navigation entries, including order, labels, URLs and current flag, are unchanged. Wish / If only and Had better sources are not edited.

Only this note and `would-rather.json` are authored by this task. No definition, runtime, style, view, JavaScript, answer engine, bank, linked-widget scope, frozen map, database or Git change is part of this draft.

## Editorial structure

8 native blocks: hero; 4-card forms-grid; 4 whole usage points; 3 mistake items; summary-list; original navigation; 2-row comparison-table; one practice-set.

The basic layer explains own action versus another person's action, not / than, questions and short refusal. It explicitly states that a past form can refer to tomorrow and that preference does not prove agreement or automatic politeness.

One meaningful detail belongs to `m48-rather-uses /sections/3`, the point about the time of the event. It contrasts an already completed personal choice with another person's completed action. Both explanations contain explicit past facts, full EN/UK examples and an account of who acted; this is not a short footnote hidden behind a click.

No paragraph-per-card structure, new design, CSS classes or package-specific view is proposed. Structured native rules, examples, notes, comparison rows and mistakes use existing fields.

## Language decisions and primary evidence

The main distinction and the past-preference forms were checked against Cambridge's editorial grammar entry. The direct page open returned HTTP 403, including the US-prefixed path; the official indexed editorial text was read instead. This is not a claim to have loaded the complete page directly. [Cambridge: Would rather, would sooner](https://dictionary.cambridge.org/grammar/british-grammar/would-rather-would-sooner)

An independently authored university lesson confirms the base-form, negative and than patterns, and the present/future interpretation of the other-subject past form. Its complete lesson body was accessible through a direct open. [University of Victoria: Modals of Preference](https://continuingstudies.uvic.ca/elc/studyzone/410/grammar/410-modals-of-preference)

The separate pattern of would prefer was checked to keep the paraphrase task from mixing constructions. Direct open returned 403; official indexed editorial text was available. [Cambridge: Prefer](https://dictionary.cambridge.org/grammar/british-grammar/prefer-would-prefer)

The old `I'd rather you come tomorrow.` is **not** retained as a universal error. Jennifer Lebedev's original author discussion reports American base-form examples alongside the past-form variant, based on her own usage and COCA examination. Her editorial article opened successfully. It supports removing the absolute ban, not claiming universal acceptance, equal frequency or a special difference in likelihood. Reader comments, learner forums, and the author's unconfirmed explanation about degrees of future reality were not used. [Original editorial discussion](https://englishwithjennifer.wordpress.com/2019/03/01/grammar-wed-rather-avoid-variations-with-would-rather/)

Access date for all four sources: 2026-10-12. Source citations support grammar boundaries; the retained examples come from the local definition, and new instructional examples and task situations are authored for this lesson. No publisher exercise is copied.

## Exact preservation and correction map

The draft's 45 `editorial_changes` records give exact old field pointers, old values, destinations and reasons, including the pre-freeze translation clarification below. The 19 complete old English sentence fields all remain verbatim in the new native block data:

| Old English fields | Draft destination | Editorial treatment |
| --- | --- | --- |
| Hero `/rules/0..2/example` (3) | Hero, corresponding example fields | Exact EN; add separate UK translations. |
| Forms `/items/0..3/subtitle` (4) | Forms `/items/0..3/rules/0/example` | Exact EN; supported bilingual examples; subtitle now explains form. |
| Usage `/sections/0/examples/0..1/en` (2) | Same usage point/example positions | Exact EN; UK of tonight now explicitly says evening. |
| Usage `/sections/1/examples/0..1/en` (2) | Same usage point/example positions | Exact EN/UK; remove unconditional “soft/polite” characterization. |
| Usage `/sections/2/examples/0..1/en` (2) | Same usage point/example positions | Exact EN; UK Friday/Saturday sentence explicitly preserves meet. |
| Mistake 1 `wrong/right` (2) | Mistakes item 0, typed EN fields and bilingual fallback | Real form error retained; separate intended UK meaning. |
| Mistake 2 `wrong/right` (2) | Comparison rows 1/0 | Both sentences exact; remove categorical wrong/correct pair, explain limited variation. |
| Mistake 3 `wrong/right` (2) | Mistakes item 1, typed EN fields and bilingual fallback | Exact EN; correct the reason to missing would in the target construction. |

The replaced categorical come/came “mistake” is not silently deleted: both forms are visible in the comparison. A new concrete mistake concerns subordinate inversion. Incorrect English forms have Ukrainian **intended meanings**, explicitly labelled “Намір”, rather than pretending that the English is grammatically well formed.

Old formulas and the old English usage heading also have explicit mappings in `editorial_changes`. The unrestricted old past-form statement becomes a clearly named learning model; the old blanket politeness claims are removed. No old complete English sentence is omitted.

## Six authored practice tasks

3 select controls and 3 choice controls, 15 answer-only options, 15 contextual feedback entries. Each task has a canonical answer and an explicit accepted list. All answer strings remain plain engine values; translations/reasons are only in feedback, not baked into button labels.

| Source index | Control | Learning goal | Canonical answer |
| --- | --- | --- | --- |
| 1 | select | Own action; bare form; tomorrow and seven o'clock preserved | `take` |
| 2 | select | Own negation without do or to | `not wait` |
| 3 | select | Question about another actor; target past form for tomorrow; no subordinate inversion | `I waited` |
| 4 | choice | Preserve the direction of a preference expressed through than | `I'd rather cook tonight than order pizza.` |
| 5 | choice | Preserve another actor, negation, Olha and before noon when paraphrasing would prefer | `I'd rather you didn't send the file to Olha before noon.` |
| 6 | choice | Own short refusal versus asking another person not to act | `I'd rather not.` |

Choices are not mechanically three copies of a gap-fill: items 4–6 check different meanings, including grammatically possible but contextually wrong alternatives. The intended past-form model is explicit in item 3; unoffered American base-form alternatives are not declared globally ungrammatical.

Each offered option has a feedback key matching the current engine's exact trim-and-lowercase lookup. Feedback provides full EN/UK interpretations and the reason; the original stimulus and changed meaning are included where the exercise is a paraphrase or choice between meaningful alternatives. All six accepted lists contain the one offered canonical value; no second distinct offered value is also correct. Ordinary engine normalization remains unchanged.

Manual/rephrase/token controls are deliberately not added: an arbitrary wrong manual answer in the current native engine can fall back to an English-only canonical key. Selected-response tasks let the existing engine show complete contextual translations for every offered option without changing JavaScript. The independent linked sentence-builder bank is untouched.

Read-only review covered the native `practice-set` select/choice payload, answer lookup, accepted-values handling and feedback mechanics. This does not claim that the new draft was executed in the browser.

## File-only checks and remaining acceptance

A read-only JSON parse/count comparison found: 8 blocks; 4 forms; 4 basic usage points; 3 mistake items; 2 table rows; 1 detail; 6 controls; 15 options; 15 feedback entries. The 19 old English fields occur verbatim in the block data, and the 10-entry navigation matches exactly.

This is **draft verification**, not frozen-package validation, application, rendered-page acceptance or engine testing. No HTTP/browser, live DB, isolated PHP test or build was run for this author task. No claim is made about no-JS practice keys, autoscoring, print rendering, metadata output or local apply. Parent review, finite binding, full fallback preservation and actual allowed acceptance remain pending before freeze.

## Pre-freeze review correction

Main review уточнив переклад нового detail: «щоб вона тоді не поділилася нею» явно зберігає завершену минулу дію Ніни та уявне заперечення. English, participant, canonical answers і сам факт попереднього поширення фотографії незмінні.

Task 6 (`/blocks/7/data/choices/2`) now preserves the evening explicitly: `tonight's party` is translated as «сьогодні ввечері на вечірці» in the Ukrainian condition and both feedback entries. `/practice_review/5/ambiguity` now says that the speaker does not object to the other person's singing, rather than claiming that the speaker grants permission. An exact reason is appended to `editorial_changes`. English sentences, offered options, canonical answers and accepted lists are unchanged; this correction makes no runtime or browser acceptance claim.


## Main freeze normalization

Всі нові блоки отримали explicit null existing_source_index; Would rather tasks — deterministic IDs m48-would-rather-task-1…6. Це дані ідентичності, не дизайн або нові controls. Wish hero починається точним topic; sidecar title не дублює штатне «Докладніше». Новий переклад past preference Ніни явно зберігає тоді й завершення події. Всі EN sentences/options/answers незмінні від reviewed drafts; всі detail sections зберігаються, не лише перша.

[Google Search Central](https://developers.google.com/search/docs/fundamentals/creating-helpful-content): пряме читання редакційних настанов 2026-10-12; точність, самостійні пояснення й користь читачеві без штучного word target або ranking claims.
