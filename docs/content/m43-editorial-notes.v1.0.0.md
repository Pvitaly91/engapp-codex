# M43 — редакційний аудит v1.0.0

Нові тексти Codex; не особисто погоджений користувачем sentence-by-sentence пакет. Нижче авторські BEFORE/AFTER mappings, окремі редакторські проходи й внутрішні cross-reviews. Це не незалежна зовнішня мовна сертифікація.

MAIN повністю перечитав три чернетки та всі 35 controls. Перед freeze виправлено feedback used-q4: повторюється getting, а не getting up. Інші teaching bytes drafts збережені. Master 1.0.0 зафіксовано як джерело інтеграції; змістові правки після цього потребують нової версії з diff.


---



---



---



---

# M43 cross-review — Used to / Would

2026-10-07. Окремий агент, який писав Stative Verbs, повністю прочитав `author-used-draft.json` і повторно зіставив його з предметними вимогами M43. Це внутрішній редакторський cross-review, не зовнішня незалежна мовна сертифікація.

Перевірений SHA-256: `a2ae69d0f938d0a110b461e3814690428680064a5ef66bfb39ebfa310d032979`.

Висновок: блокувальних граматичних помилок або неправильних ключів не знайдено. Є одне точне редакційне уточнення перед freeze.

## Знахідка

**P3 — `/lesson/practice/3/feedback/paragraphs_uk/0`, task `used-q4`.** Текст: «Повторення getting up у третій моделі потрібне: перше get належить до get used to, а друге — до дієслова get up». У `is getting used to getting up` повторюється слово `getting`, а не сполука `getting up`: перше getting не має up. Пояснення механізму далі правильне, але вступ формально неточний. Рекомендована точкова заміна: «Слово getting у третій моделі трапляється двічі: перше належить до get used to, а друге — до дієслова get up». Умови, відповідь і aliases не потребують змін.

## Перевірені навчальні положення

- Used to охоплює і повторювані дії, і тривалі минулі стани. Пояснення про зміну теперішньої ситуації відповідає цільовій навчальній моделі.
- Would отримує зрозумілу минулу рамку; допустимо, що вона стоїть у попередньому реченні. У basic прямо вказано різницю стану й дії, а не механічний список заборонених дієслів.
- Немає глобальної заборони `would be / would have / would live`. Have lunch має діяльнісне значення; be/live в уявних умовах винесено до змістовного поглиблення із повними перекладами.
- Past Simple явно включає регулярні дії й стани. Task q2 обмежений одноразовою подією через повну ситуацію, а не одним last Saturday.
- Did/didn’t use to описано як навчальну письмову модель. Після would немає to. Форми ’d не розгортаються універсально: вказано необхідність читати продовження.
- Be used to / get used to розрізняють знайому ситуацію й адаптацію; noun/pronoun/-ing та зміну часу be/get пояснено.
- Обидва details — самостійні поглиблення конкретних пунктів, не прихований необхідний basic. Два mistakes мають чітко заданий контекст і справжню помилку форми в ньому.

## Усі завдання й controls

| Task | Перевірка |
|---|---|
| `used-q1` | 2 controls. Колишнє житло задане як один тривалий стан; used to live — потрібний варіант. Would play / would to play перевіряє справжню форму після modal. Feedback не заперечує можливість played/used to play у вільній розповіді. |
| `used-q2` | 1 control. Один-єдиний ремонт без попередньої звички; repaired відповідає саме заданій події. Повний переклад збігається. |
| `used-q3` | 2 manual controls. Є лексичні підказки й canonical tokens. Питання не перетворено на твердження про реальну минулу звичку Марти. Did not/didn’t/didn't мають явні aliases. |
| `used-q4` | 3 controls. Умова розрізняє колишній розклад, уже звичний розклад і триваючу адаптацію; expected forms та переклади збігаються. Тільки опис повторення getting у feedback потребує уточнення вище. |
| `used-q5` | 1 manual control. Учень вводить лише пропущену дієслівну частину після we. used to walk / would walk / walked усі явно приймаються й мають окремі перекладені повні приклади. Форма we’d не повинна додаватися як alias у цей конкретний gap, бо we вже є поза ним. |
| `used-q6` | 2 controls. Former boat possession протиставлене діяльності have lunch; feedback визнає можливість іншого значення They would have a boat у іншому контексті. |

Програмно read-only перевірено: valid JSON; унікальні section/point/detail/task/control IDs; 6 tasks / 11 controls; усі required; all_required_controls у кожному task; усі correct_value містяться в options; canonical є в accepted_answers; join tokens дорівнює canonical для всіх трьох manual controls. Жоден answer input не має наперед заповненого value. Options короткі, без захованих keys/feedback. Видалення not і заміна понять не містяться в aliases.

Усі theory приклади, table examples, correct mistake pairs і feedback answer examples мають українські переклади, відділені від коментарів. Перевірено negation, число forty, час six/nine/eight, рік 2020, повторюваність every Saturday/almost every day; зміст збережений. Два неправильні речення mistakes мають поруч переклад відповідного правильного вислову. В інструкціях немає потреби відгадувати приховану лексику.

## Додаткова перевірка редакційних джерел

Під час цього cross-review реально відкрито й прочитано лише редакційні частини:

- [British Council: Past habits](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2-grammar/past-habits-used-to-would-past-simple), рядки 21–54: past states, repeated actions, usual limits of questions/negatives with habitual would, Past Simple для станів і повторення.
- [British Council: Different uses of used to](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2-grammar/different-uses-of-used-to), рядки 22–46: колишня ситуація, звичність, процес адаптації, noun/pronoun/-ing.
- [Cambridge English Grammar Today: Would](https://dictionary.cambridge.org/grammar/british-grammar/will-or-would), індексований редакційний текст у пошуку: habitual actions, conditional uses, past willingness/refusal. Це додаткова перевірка пояснення wouldn’t, а не перевірка через учнівські коментарі.
- [Cambridge: Word choice — used to and be used to](https://dictionary.cambridge.org/us/grammar/british-grammar/be-and-be-), індексований редакційний текст у пошуку: did + use to і accustomed construction.

Коментарі користувачів і відповіді в коментарях не використані. Жодні видавничі вправи чи приклади в draft не переносилися під час review.

Runtime/UI не перевірялися в цьому subtask. Correct/wrong/partial/empty/reset, токени, клавіатуру й no-JS MAIN перевіряє після інтеграції. Draft, definitions, БД, HTTP.loc та Git не змінено; створено тільки цей private review.

---

# M43 — незалежний агентський cross-review Stative Verbs

Дата: 2026-10-07. Перевірив агент `m43_author_used`, який не писав цей урок. Це окремий агентський редакторський прохід, не людське погодження й не сертифікований зовнішній мовний аудит.

Повністю прочитано `author-stative-draft.json`: sources, subtitle, hero, editorial changes, усі 7 секцій, 13 points, 1 detail, обидві таблиці, wrong/right-пари, 6 tasks, усі 13 required controls, aliases/tokens і feedback. SHA-256 перевіреної редакції: `bb6fa39b805259e523b49a944f064bbddc38345853288d0109da96b8223c1366`. Draft не редагувався.

## Висновок

Змістових блокерів не виявлено. Урок відповідає предметним вимогам M43: пояснення будуються навколо значення в контексті; need відділено від володіння; think/have/see/taste/smell/be мають належні контрасти; немає універсальної заборони Continuous або правила «тимчасове — отже Continuous». Correct keys відповідають заданим ситуаціям. Обов’язковий basic не заховано в detail.

## Перевірка теорії та перекладів

- `stative-knowledge`: now не перетворює знання на процес. Know, understand, remember, believe подані як звичайні нейтральні значення. Пояснення тимчасовості стану не створює нового хибного правила.
- `stative-wishes-feelings`: потреба need класифікована окремо від власності. Нейтральні like/love/prefer відділено від розмовного loving. Переклад `I'm loving this quiet weekend ...` передає захоплення саме цими вихідними, а не довічну вподобу.
- `stative-possession`: own і belong to природні; приклади та переклади узгоджені.
- `stative-perception`: can hear / can smell природно передають фактичне сприйняття. Seem і taste-предмет описують враження/властивість. Не все smell зведено до свідомої дії.
- `stative-think-have`: think як думка, am thinking about як процес і often think about як повторення прямо запобігають механічній класифікації за about. Have lunch має як Continuous, так і habitual Simple; володіння відокремлено.
- `stative-senses`: зустріч із майстром явно домовлена, тому seeing не видається за зорове сприйняття. Tasting і smelling мають навмисну мету. Усі 6 bilingual cells мають повні окремі переклади, без підміни коментарем.
- `stative-be-being`: tired подано як тимчасовий стан; rude to як поведінку щодо конкретної людини. Не робиться висновку, що будь-який прикметник допускає being.
- `stative-perfect`: have known, have had і How long have you known ... граматичні. For four years / since 2021 не примушують Continuous. Числа й часові значення збережені в перекладах.
- `stative-forms`: Present Simple do/does і Continuous be + -ing розмежовані. Таблиця має ствердження, заперечення й питання з повними UK перекладами. Питання про be окремо пояснює відсутність do.
- `stative-mistakes`: `does not owns` і `He having` — справжні помилки побудови повного речення; right-пари виправляють саме форму. Немає ❌ на допустимому loving або іншому значенні дієслова.
- `stative-summary`: висновки відповідають основному тексту. Немає нової прихованої умови або непоясненого винятку.

## Перевірка всіх controls

| Control | Ключ | Чому однозначний у цьому завданні |
|---|---|---|
| stative-q1a | know | Нейтральне повідомлення про відомий код, не діяльність. |
| stative-q1b | needs | Нейтральна поточна потреба; now прямо не використано як перемикач. |
| stative-q2a | think | Контекст прямо задає власну думку про читабельність. |
| stative-q2b | am thinking | Умова прямо просить процес обмірковування в Continuous. |
| stative-q2c | has | Задано володіння велосипедом, не діяльність із have. |
| stative-q2d | is having | Обід відбувається зараз; умова просить показати процес. |
| stative-q3a | Домовлена зустріч | Контекст задає домовленість на завтра; see не означає «бачити зараз». |
| stative-q3b | Соус має солоний смак | Підмет — соус, описано його смак. |
| stative-q3c | Покупець навмисно нюхає мило | Явно задано дію покупця й мету вибрати аромат. |
| stative-q4a | I do not own a car. | Задано I, own, a car і заперечення; перефразування іншим дієсловом не потрібне. |
| stative-q5a | is | Tired передає стан навіть за обставини today. |
| stative-q5b | is being | Умова прямо просить being для поведінки; feedback чесно визнає граматичність is rude в іншому поданні. |
| stative-q6a | We have known Nina for four years. | Задано know, Present Perfect, We, Nina й точний період; знайомство триває досі. |

6 tasks мають `all_required_controls`. Складені q1/q2/q3/q5 містять відповідно 2/4/3/2 обов’язкові частини. Це не гарантія runtime scoring — лише правильний авторський контракт; UI має окремо перевірити partial/wrong/empty/reset.

Обидва manual controls дають достатню лексику й форму. Canonical входить до explicit accepted answers; concatenation canonical tokens дослівно збігається з canonical answer. Для q4 приймаються повне do not і ASCII/типографічне don’t. Для q6 — We have і ASCII/типографічне We’ve. Глобальне розгортання 's не потрібне. Feedback містить повні правильні речення з перекладами. Option labels не містять правильного ключа з довгим поясненням чи перекладом.

JSON parse, required flags, присутність correct_value в options, canonical в aliases, tokens join і відсутність duplicate IDs перевірено read-only командою: проблем 0.

## Detail quality

Єдиний `stative-detail-loving-register` має конкретного власника `stative-wishes-feelings`. Два пояснювальні абзаци розрізняють нейтральну вподобу та актуальне розмовне захоплення, далі йдуть два контрастні приклади з перекладами. Основна застережна теза вже видима в basic. Отже, detail є окремим поглибленням, а не обов’язковим правилом за кнопкою. Квота details не потрібна.

## Незалежна звірка джерел

Повторно відкрито напряму [British Council — Stative verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/stative-verbs): успішний HTML, 378 рядків. Прочитано редакційні рядки 23–65; коментарі не використано. Вони підтверджують семантичний контраст think/have/see/be/taste і те, що Continuous для станів зазвичай не вживається, а не абсолютно неможливий.

Також прямий [British Council — Present perfect simple and continuous](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/present-perfect-simple-continuous) успішно відкрився; URL у draft дійсний. Cambridge access limitations у draft сформульовано чесно як індексований матеріал/403; цей cross-review не приписує собі прямого прочитання Cambridge.

## Межі висновку

Немає редакційної вимоги змінювати цей draft. Технічна інтеграція ще має підтвердити native render, усі тексти/переклади, незмінність ключів, independent detail, no-JS і клавіатуру. Я не торкався DB, HTTP `.loc`, runtime, Git або draft-файла. Записаний лише цей приватний review.

