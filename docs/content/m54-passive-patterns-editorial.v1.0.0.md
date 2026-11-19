# M54 — editorial BEFORE/AFTER — редакція 1.0.0

Ціль: лише три UK owners PassiveVoicePhrasalVerbsTheorySeeder, PassiveVoiceRestrictionsTheorySeeder та PassiveVoiceTwoObjectVerbsTheorySeeder. Це нова авторська редакція за M54, не технічна копія старого payload. Ні дизайн, ні answer engine не змінюються.

Base: `f4e1c0d94a590d630582e2bb613cbdc93654fc4a`. Незалежний source/DB/HTTP BEFORE main: `a0fc78b98a12e8af17c56d3f4fde8dccf95031d6de2a5a4c8081afeba15edb64`.

## Ідентичність і повнота

| Owner | Page/category | Slug | H1 leading strong | Continuing blocks | Hero/practice |
|---|---|---|---|---:|---|
| Phrasal | 99/1 | theory-passive-voice-phrasal-verbs | Пасив фразових дієслів (Phrasal Verbs in Passive) | 9 | B1–B2/B1 |
| Restrictions | 100/1 | theory-passive-voice-restrictions | Обмеження пасиву | 10 | B1–B2/B1 |
| Two-object | 101/1 | theory-passive-voice-two-object-verbs | Two-object verbs | 9 | B1/B1 |

Збережено кожний continuing source index, type, column і level, а отже механізм continuing UUID/anchor. Навігаційний slot не замінено практикою. Окремо дописано один B1 practice-set на кожній сторінці. Raw titles, slugs, categories, tags і display ranges не перейменовано. Наявні B2 blocks не вирівнюються до B1.

## Phrasal — 9 старих slots

| Source index / native type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero | phrasal-hero | Збережено основний be/V3/particle принцип; виправлено «не відривається» як хибну глобальну вимогу фізичної суміжності; додано переклади всіх трьох hero examples. |
| 1 forms-grid | phrasal-vocabulary | Збережено всі вісім корисних конструкцій look after/bring up/call off/put off/turn down/carry out/look into/break into. Виправлено неточний bring up/grow up та додано точні переклади. Matter приклад лишився в hero/tenses; forms отримали окрему скаргу, щоб не дублювати той самий приклад. |
| 2 comparison-table | phrasal-transformations | Збережено чотири ситуації call off/break into/look into/bring up. Active і Passive повністю перекладено; коментар ролей окремий. Відому grandparents збережено; they не втрачається при full-information трансформації; someone дозволено опустити як невідомого агента. |
| 3 usage-panels | phrasal-categories | Збережено чотири смислові категорії та їхні корисні дієслова. Cancel відокремлено як однослівний відповідник. Called/called off — видима різниця значень, а не false grammar error. Повтор meeting has been put off об’єднано з perfect-пунктом; brought about отримало конкретну причину для прозорого значення. |
| 4 usage-panels | phrasal-tenses | Чотири часові повтори перегруповано в три іменовані цілі: simple, perfect, continuous. Обидва simple часи збережено. Додано заперечення та питання з першим допоміжним, agreement, been/being; не повторюється весь M52. |
| 5 forms-grid B2 | phrasal-prepositions | Збережено laugh at/look at/speak to/refer to/rely on/deal with, кожний повний EN приклад перекладено. Додано межу з неперехідним break down, не загальний дозвіл для будь-якого прийменника. |
| 6 mistakes-grid | phrasal-mistakes | Старі граматичне called та стилістичний by-порядок не видаються за безумовно неграматичні. Для correction-grid обрано справжні form errors: повторений об’єкт, call замість V3, on замість at у заданому laugh at, were для однини child. Корисний нейтральний by-порядок збережено в basic. |
| 7 summary-list | phrasal-summary | Прибрано повтори й хибне «прислівник завжди після частки». Збережено принцип be/V3, потрібні частки, agent, питання та межі моделі. |
| 8 navigation-chips | phrasal-navigation | Збережено весь початковий набір 11 тем; labels отримали explicit чинні destinations з accepted M53. Поточним лишається Phrasal. |

Два details мають точних власників: `phrasal-vocabulary /items/0` — adverb type/focus і well у двох природних позиціях; `phrasal-prepositions /items/4` — дієслівна прийменникова модель проти обставини прибуття. В basic вже є достатні правила та приклади, щоб виконати практику без кліку. Це не кнопки заради ще одного перекладу.

## Restrictions — 10 старих slots

| Source index / native type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero | restrictions-hero | Blacklist дієслів замінено перевіркою конкретної моделі; own — позитивний контрприклад до хибного stative ban. Arrive і open зберігають різницю події та перетворення. |
| 1 forms-grid | restrictions-intransitive | Всі вісім старих лексичних прикладів збережено з чистими перекладами: arrive/go/come/sleep/die/happen/exist/appear. Правило обмежено цими простими вживаннями. Додано linking complement і пояснення, що обставина не є об’єктом. |
| 2 usage-panels | restrictions-states | Виправлено blanket bans have/own/possess, fit/suit, hold/contain/weigh, resemble/equal/lack. Залишено компактний принцип і показові приклади; детальні лексичні межі винесено в три змістовні details. Старі формули та wrong-alternatives не маскуються під переклади. |
| 3 comparison-table | restrictions-transitivity | Збережено wrote/arrived/opened contrasts. Абсолютне «лише direct object» замінено узгодженим правилом із перевіреним look after пасивом. Кожний результат має переклад; неутворений пасив показано як збережений актив, а не текст «без пасиву» в колонці UA. |
| 4 usage-panels B2 | restrictions-reflexive | Збережено wash/hurt/enjoy та love/help/meet. Виправлено blacklist reflexive/reciprocal verbs: проблема механічного herself/each other як підмета не забороняє wash/help в інших моделях. Взаємність і учасники не губляться. |
| 5 forms-grid B2 | restrictions-other-patterns | Збережено cognate objects, keep an eye on, get + adjective і let як теми. Абсолютні «без пасиву» замінено прив’язкою до регістру/значення. Permission let → allowed to зберігає названого агента by him; let rent та сталі let go/in/out не підпадають під ту саму заборону. |
| 6 comparison-table B2 | restrictions-changing-meaning | Збережено run business/run daily, grow vegetables/children grow; додано open pair. Для пасивів збережено ті самі ролі, час і число; інша модель не видається за механічне перетворення неперехідної події. |
| 7 mistakes-grid | restrictions-mistakes | Збережено прозорі happened/arrived corrections; have обмежено possessive значенням, himself — конкретним невдалим підметом. Не використовується помилкове owned як неправильний passive. |
| 8 summary-list | restrictions-summary | Замість семи повторних категоричних заборон — шість перевірок моделі, стану, обставин, ролей і природності. |
| 9 navigation-chips | restrictions-navigation | Ті самі 11 topics і actual destinations; current owner Restrictions. |

Три details належать `restrictions-states /sections/0..2`: possession scope; fit різних значень; capacity/measure/contain/hold. Вони пояснюють окремі лексико-граматичні межі з контрастами, а не приховують основний принцип. Possess passive позначено формальним, не універсальним нейтральним B1 шаблоном. Source corpus observations не видаються за редакційне правило видавця.

## Two-object — 9 старих slots

| Source index / native type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero | two-hero | Збережено give-пару, прибрано безумовне «person природніше / thing рідше»; кожна повна EN фраза має UK. |
| 1 forms-grid | two-roles | Збережено indirect/direct object та обидві активні побудови. Додано виконавця як третю роль, him/her/them → he/she/they і об’єктні форми після прийменників. Person/thing не оголошено абсолютною онтологією. |
| 2 usage-panels | two-variants | Збережено recipient/item passive з give/send/offer. Вибір визначає тема розмови; обидва доречні варіанти не конкурують без заданого фокуса. Додано agreement і видиме застереження про відомого агента. |
| 3 usage-panels | two-typical | Збережено всі чотири групи дієслів і корисні значення: give/send/bring, offer/promise/lend, tell/show/teach, pay/award/grant. Додано perfect/modal-negative/short-answer у потрібному обсязі, без дублювання M52. |
| 4 comparison-table | two-transformations | IO/DO більше не замінює UA. Повністю перекладено Active і обидва Passive варіанти; виконавця явно збережено. Додано show/tell для зіставлення на тих самих ролях. |
| 5 usage-panels B2 | two-to-for | Збережено to/for і приклади give/send/tell та make/buy/find. Розрізнено recipient/beneficiary/agent; сумісність перевіряється за дієсловом, а не загальним перекладом «кому». |
| 6 mistakes-grid | two-mistakes | Засвідчене given him без to вилучено з red/struck grammar corrections; натомість справжнє V3 give → given. He was given to a book розглядається в конкретно заданих ролях (отримувач he, предмет book), не як глобальна заборона будь-якого was given to. Збережено make for і додано pronoun/agreement. |
| 7 summary-list | two-summary | Збережено ролі й дві моделі, прибрано blanket ranking природності. Додано agent/to/for, pronouns, agreement і межу explain/suggest. |
| 8 navigation-chips | two-navigation | Ті самі 11 topics і exact accepted destinations, current owner Two-object. |

Два details: `two-typical /sections/2` — explain/suggest не копіюють double-object give; `two-to-for /sections/0` — документований варіант без to не є універсально неможливим, але не стає основним B1 шаблоном. У correction-grid не маркуємо його автоматично червоним як неграматичне.

## Власна практика: 18 tasks, не 33 нові питання

| Тема | Selects: форма | Choices: зміст | Inputs: manual/tokens |
|---|---|---|---|
| Phrasal | скасування call off; continuous are being | перенесення, не скасування; active separability проти passive subject | perfect question про school trip; negative continuous look into |
| Restrictions | happened без штучного be; weigh як дія з пасивом | owned by попри стан; open без мовчазної зміни моделі | актив arrived; дозволений owned by з заданим підметом |
| Two-object | were за plural recipient; for для cook | letter як задана тема; agent і beneficiary | recipient They з відомим manager; tickets із to them та відомою sister |

Кожний input — одне завдання, два способи відповіді. Умови й лексична допомога українською; перемішаний slash-bank англійською. Порядок і новий підмет задані, тому finite accepted список не претендує на всі природні перефразування. Phrasal is not/isn't підтримано двома aliases; punctuation-free feedback keys відповідають уже чинній поведінці engine, не новому normalizer.

Правильні альтернативні речення у feedback пояснюються як інший заданий фокус або граматична інша модель, а не як доказ неграматичності. Meaning-task переклади, що розкривали б ключ, показуються після Check; before-choice український критерій видимий.

## Окремі widgets

Банківський агент незалежно прочитав п’ятірки й чинні live links. Три exact banks — PolyglotPassiveVoicePhrasalVerbsAllLevelsLessonSeeder, PolyglotPassiveVoiceRestrictionsAllLevelsLessonSeeder та PolyglotPassiveVoiceTwoObjectVerbsAllLevelsLessonSeeder. У кожному exact B1 pool 12 чинних питань; selected first five тематично придатні. 15 reused, 0 new; старі questions/answers/options/links не переписуються. У selected15 hints і verb_hints порожні; глобальна локалізація не потрібна.

Exact reviewed widget intros включено в master. Phrasal явно задає finite adverb positions без глобальної суміжності; Restrictions прямо попереджає, що в цих п’яти завданнях правильний актив; Two-object задає recipient/item фокус і прозоре to лише для цієї вправи.

## Native компоненти й невізуальний scope

Hero, forms-grid, usage-panels, comparison-table, mistakes-grid, summary-list, navigation-chips і practice-set залишаються чинними типами. Поля forms rules/outro, usage notes, переклади correction і table cells повинні бути перевірені як реальний SSR, не тільки наявний JSON. Payload не містить class/style/view чи власних m54-styles. Немає квоти details, приховування line-clamp або зменшення шрифту.

Повну авторську редакцію та наведені фінальні мовні виправлення звірено головним агентом до freeze. Очікувані SEO title/description отримано чинним PageMetadata до apply; UUID/body/component projection збережено в component map. Цей редакційний аудит не замінює окремі результати guarded apply та приймання у фінальному звіті.
