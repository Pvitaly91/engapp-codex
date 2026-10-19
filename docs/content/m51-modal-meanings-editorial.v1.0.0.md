# M51 — редакційний BEFORE → draft AFTER 1.0.0

Статус: редакційний журнал підготовки авторської редакції 1.0.0, замороженої після main language/component/engine review 2026-10-14. Формулювання draft у таблицях описують процес до freeze. DB apply і browser AFTER acceptance фіксуються окремо. База — `7b0ca49b3cbfd7135b80f6931cdd42834eb24c53`.

Власні джерела цього документа:

- `docs/content/m51-modal-meanings.v1.0.0.json` — структурований master.
- `docs/content/m51-modal-meanings.v1.0.0.uk.md` — читабельна редакція всіх basic/detail/practice полів.
- `docs/content/m51-modal-meanings-components.v1.json` — скінченні pointers, component roles та власники meaningful details.

## Незалежні чинні source BEFORE

Прочитано всі три definitions цілком, включно з кожним вкладеним JSON body. Початкові дані мають hero, forms-grid, usage-panels, mistakes-grid, summary-list і navigation-chips, але не мають власного practice-set. Історичні джерела не змінювались під час підготовки цього draft.

| Урок | Чинна definition | SHA-256 сирих BEFORE bytes |
| --- | --- | --- |
| Modals of Deduction | `database/seeders/Page_V3/ModalVerbs/ModalVerbsDeductionTheorySeeder/definition.json` | `0ab4a4ecb973dc6c74c51d8875e1183e5c459df859d240e8df7982963a5d745d` |
| Past Modals | `database/seeders/Page_V3/ModalVerbs/ModalVerbsPastModalsTheorySeeder/definition.json` | `0ca31cd295260ff1abc551aa780d84d4c1b76e1e3965b28b0caecd06f33bbd8d` |
| Requests / Offers / Suggestions | `database/seeders/Page_V3/ModalVerbs/ModalVerbsRequestsOffersSuggestionsTheorySeeder/definition.json` | `68160cc327a22e62db7b139cb9a72bdb235b1925e79bdd83a93bdbdbe15d5d48` |

Ці source hashes не підміняють окремого свіжого DB/HTTP/physical-target BEFORE, який належить guarded процесу головного агента. Тут не заявлено їх проведення.

## Загальні рішення до freeze

Зберігаються exact owners, UK locale, category ancestry `modal-verbs`, Page titles, slugs, display/technical рівні та шість continuing source indices `0–5`. Новий practice-set має `existing_source_index: null`; IDs, UUID і anchors continuing blocks забезпечує patch, не цей редакційний документ. Чинні вісім navigation destinations збережені в тому самому порядку й з тим самим current flag.

Новий subtitle починається з `<strong>` із точним чинним Page.title. Це підготовка в підтриманому форматі для існуючого title extraction, не нова глобальна евристика або перенесення M50-only workaround. Реальний H1/breadcrumb/metadata шлях ще має перевірити головний агент до freeze.

Дизайн, Blade/CSS/fonts/frontend JS, компоненти й grader не змінюються. Правила передаються через supported semantic data, не anonymous flow чи окремі `m51-*` visual classes. Додавання нового контентного пункту не означає новий тип компонента.

Hero/form rule examples містять paired EN/UK spans без class/style; usage examples — окремі `en`/`ua`. Broken English у mistakes має український задум, явно позначений словом «Задум», а не претензію на точний переклад граматично правильного речення. Декоративні `✅` і `span.font-mono` у correct полях не перенесені.

## A. Modals of Deduction

Confirmed source identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsDeductionTheorySeeder`. Technical B1, display B1-B2. Фокус — теперішня ситуація: відомий факт, висновок мовця, сила переконання.

### Корисний зміст, який збережено

- Must як сильний висновок; may/might/could як можливе пояснення; can't як відкидання можливості.
- Базова форма дієслова та процес із be + V-ing.
- Корисні початкові речення `He is answering every question. He must know the topic well.` і `She has been studying all day. She must be exhausted.` з їхніми UK перекладами. Додано явний статус переконання мовця, не доказу.
- Три невизначені версії `She might be in a meeting now.`, `This key could be the right one.`, `They may be waiting outside.` та близькість may/might/could у багатьох контекстах.
- Межа deduction / requirement та deduction / ability; unchanged navigation.

### Exact finite replacements та прогалини

| BEFORE location / текст | Draft AFTER | Конкретна причина |
| --- | --- | --- |
| Hero rules examples без перекладу | Paired EN/UK examples | Приклад має читатися з точним українським значенням; old bare fragments не були самостійною додатковою функцією. |
| Forms `/items/0–2`: `be + noun / adjective`, але `at home` у subtitle | `be + потрібне доповнення`: ознака, особа, місце тощо | `At home` — прийменникова конструкція; колишній список не охоплював власний приклад. |
| Forms intro лише modal + be / be-ing | Додано modal + базове lexical V та доречні Can/Could questions | Know не потребує be; питання про можливість не утворюють штучної парадигми для кожного modal. |
| Usage `/sections/0`: `The lights are off. They must be asleep.` | Збережені stronger knowledge/work scenes + явна caveat про світло | Вимкнене світло не доводить сон; речення граматичне, але непридатне як compulsory inference без додаткового задуму. Це не «помилка англійської». |
| Usage `/sections/2`: `He can't be at home. I just saw him downtown.` | Потяг прямо ще стоїть біля платформи; `can't be on its way yet` | Стара сцена потребувала домислити відстань і час переміщення. Нова подає поточний факт прямо. |
| Usage `/sections/2`: `She can't be sleeping. The TV is still on.` | Та сама прозора train scene | Увімкнений TV сумісний зі сном; не називаємо граматичну конструкцію помилковою, замінюємо недостатню ситуацію. |
| Usage `/sections/2`: `This can't be the final version. It has too many mistakes.` | Train scene | Фінальна версія теж може містити помилки; потрібний статус «останній» не випливає логічно з кількості помилок. |
| Missing possible negation | Окремий visible basic point may not / might not | Можлива відсутність не тотожна неможливості; не ховаємо цю базову різницю в detail. |
| Mistakes can-positive warning | Конкретна не підтверджена версія, не глобальна заборона can | Стверджувальне can має інші допустимі наміри. У цьому завданні звичайні may/might/could. |
| Mistakes mustn't / can't з lights-off clue | Train-at-platform scene + scoped explanation | Can’t є цільовим відкиданням тут; mustn't звичайно забороняє. Немає глобального blacklist усіх must not в усіх контекстах. |
| `He doesn't must be tired.` → `He must be tired.` | Explicit intent «Не думаю, що він втомлений» → `I don't think he is tired.` | Старе виправлення прибирало заперечення. Спочатку визначено намір; не підсилюємо його автоматично до can't і не змінюємо на affirmative must. |
| Missing time boundary | Коротке видиме посилання на Past Modals | Не розширюємо теперішній урок у повний довідник perfect. |

### Basic / detail

Весь необхідний алгоритм, видимі приклади, переклади та застереження залишаються basic. Один meaningful detail належить `modals-of-deduction-uses` → `/sections/0`: окремо пояснює межу між відомою ознакою, переконанням і невідомими фактами; містить власний приклад та коментар. Це не кнопка заради ще одного прикладу й не прихований переклад. Немає runtime word threshold.

## B. Past Modals

Confirmed source identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsPastModalsTheorySeeder`. Technical B1, display B1-B2. Мета — доступна карта функцій modal + have + V3 для минулих ситуацій, не копія C1 `Modal Perfect and Deduction`.

Read-only сусідній `ModalPerfectAndDeductionTheorySeeder/definition.json` прочитано: він глибше відділяє ознаки/висновок, actual/unknown outcome та контекстні межі. Його content, practice, mappings і payload не змінюються й не переносяться дослівно. Новий B1-B2 basic починає з будови та шести чітко названих функцій.

### Корисний зміст, який збережено

- Modal + have + V3, сильний/можливий/негативний висновок, оцінка минулого вибору.
- `She must have forgotten.`, `He might have taken the wrong train.`, `You should have called me.` отримали точні hero переклади.
- Корисні `He might have missed the email.`, `They should have booked the tickets earlier.`, `You shouldn't have said that.` з точними UK перекладами й ясним контекстом.
- Усі три genuine form errors: must had → must have, went → gone, should of → should have. Виправлення не змінюють modal, час або заперечення.
- Unchanged navigation destinations.

### Exact finite replacements та прогалини

| BEFORE location / текст | Draft AFTER | Конкретна причина |
| --- | --- | --- |
| «минулі висновки / можлива подія» як майже одна функція | Окремі знання/висновок, unknown event, impossible event, unused opportunity, criticism, expected result | Спільна будова не означає однакового наміру або фактичного результату. |
| Must example `She isn't here. She must have gone home.` | Door unlocked / тільки Ілля мав код / висновок про unlocking | Відсутність не визначає місце призначення. Нова сцена відділяє дані мовця від припущення про виконавця. |
| Must example `They look exhausted. They must have worked all night.` | Та сама explicit door scene | Втома не доводить ночі роботи; старий actor/time clue не нав'язується учневі. |
| `The cake is gone. Someone must have eaten it.` | Door scene | Торт могли забрати, а не з'їсти; прибрано compulsory inference зі слабкого факту, не «неграматичне» речення. |
| `She could have taken a taxi.` без контексту | Два visible basic taxi contexts: невідомий спосіб / відомий вибір автобуса | Одна форма допускає різні функції. UK «можливо, взяла» / «могла взяти, але обрала» зберігає відомий результат. |
| Could unused — коротка bare note | Окремий повний basic point з явним but clause | Це центральна різниця B1-B2, не деталь за додатковим кліком. |
| Missing may have / couldn't have / possible negative | Видимі patterns і meaning notes | Попередній перелік був неповним; не створюємо довідник усіх модальних ідіом. |
| Should have як лише жаль / критика | Окремий expected-parcel-result basic point | Самої форми недостатньо, щоб довести невиконання; очікування має власний явно неперевірений результат. |
| Missing question/contractions | `Could ... have ...?`, should've/could've/might've, cannot/couldn't/shouldn't | Потрібні повні форми й доречне питання без do; have не замінюється на has/had/of. |
| Missing had to boundary | Коротка visible note + M50 link | Must have для висновку не підміняє звичайний минулий обов'язок. Не повторюємо сусідній урок. |

### Basic / detail

Одна осмислена деталь `past-modals-process-and-passive` належить forms-grid `past-modals-forms` → `/items/0`. Вона зіставляє have been + V-ing і have been + V3 у двох пояснених сюжетах: діє підмет / предмет отримує дію. Обидва переклади входять до тих самих complete detail fragments. Відображення точного forms owner має бути доведено до freeze; fallback не може приховати матеріал. Немає прихованого основного правила або універсальної таблиці всіх aspect/voice форм.

## C. Requests / Offers / Suggestions

Confirmed source identity: `Database\Seeders\Page_V3\ModalVerbs\ModalVerbsRequestsOffersSuggestionsTheorySeeder`. Technical A2, display A2-B1. Початковий критерій — намір та виконавець, а не «найввічливіше слово».

### Корисний зміст, який збережено

- Can/Could/Would you для прохань; Shall I для власної допомоги; Would you like для пропозиції; Let's/Why don't we/Shall we для спільного плану.
- Корисні речення про надіслане посилання, піднесену сумку, воду, easy part, meeting after lunch та go now. До них додано природні реакції; виконавці визначені в descriptions.
- Genuine form errors `Can you to help me?` та `Let's we start now.` з exact form repairs.
- Unchanged navigation destinations.

### Exact finite replacements та прогалини

| BEFORE location / текст | Draft AFTER | Конкретна причина |
| --- | --- | --- |
| Hero/templates без перекладів | Paired EN/UK речення | Учень має бачити точний зміст, а не англійські фрагменти без акторів. |
| Usage heading «Як змінюється ввічливість» | «Прохання, пропозиції та короткі реакції» | Спочатку потрібні намір і виконавець; polite scale не пояснювала різні дії. |
| «Чим м'якше формулювання, тим ввічливіше» | Can normal in everyday requests; could/would often less direct; tone/context explicit | Непряма форма не гарантує ввічливості, а Can you не автоматично неввічливе. |
| «спільний план без тиску» | Контекстна пропозиція з можливістю зміни/відмови | Let's може створювати тиск; старе абсолютне формулювання замінено точним. |
| Неповне Would you like | Окремі noun / to V / me to V patterns | Річ, дія співрозмовника та дія мовця не взаємозамінні. |
| Missing own-help forms/replies | Can I help / I'll + natural acceptance/refusal | Реакція не повинна повторювати той самий modal без урахування значення. |
| Missing negative shared plan | Let's not + V | Основний порядок заперечення — visible basic. |
| Optional How about | Compact visible -ing model + explicitly non-exclusive note | How about також підтримує інші будови; не проголошуємо одну модель абсолютним правилом. |
| `Shall you open the window?` → `Shall I ...? / Could you ...?` | Explicit request to listener → лише `Could you open the window?`; Shall I окремо пояснює зміну actor | Старі дві правки змінювали виконавця і не були взаємозамінними. Не оголошуємо rare formal shall you неможливим скрізь. |
| «після can/could/would без to» без уточнення like | Visible distinction would + like + to V | Не створюємо хибну заборону `would like to` через базове правило modal + V. |

### Basic / detail

Основні A2 моделі, actor distinctions, реакції та застереження залишаються видимими. Один B1 detail `requests-mind-actors-and-replies` належить requests usage `/sections/0`: порівнює mind + V-ing (діє you) і mind if I + past (діє I), уточнює неминуле polite past та meaning-dependent reply. Дві повні діалогові сцени й явна відмова — meaningful пояснення, не окремий приклад без розбору.

## Повна власна практика

Кожний lesson має рівно 2 selects, 2 choices та 2 inputs: загалом 18 own tasks. Ручний ввід і токени — два способи відповісти на той самий input, не подвійна статистика. Збережені source_index `1–6`, стабільні `m51-*` task IDs, typed groups і native fields.

| Урок | Selects: форма | Choices: зміст | Inputs: нові сюжети |
| --- | --- | --- | --- |
| Deduction | must + know; might + be + working | may not як possible negative; can't як відкидання sleeping з прямо заданого live action | можливий процес waiting outside; відкидання four-digit code за прямо заданими three digits |
| Past | must + have + opened; shouldn't have + deleted | could have unused ferry за explicit train choice; expected download за unchecked result | message першої появи today / неможливе прочитання yesterday; known Iryna told secret / retrospective negative criticism |
| Requests | Could you + help; Would you like + to come | me to = діє Marta; Why don't we = спільний план | Would you close the gate; Let's not leave the bags |

Select завдання називають конкретну модель, тому не вимагають «вгадати» єдиний modal з недостатньої ознаки. Input умова видима в підтриманому `after`, shuffled slash-bank — у `before`. Готове англійське речення не передане як початкова instruction/hint/placeholder.

UK пояснення feedback містять complete canonical English + точний переклад + правило. Known wrong probes компактні; не роздуваються сотнями aliases. Existing EnglishAnswerVariants має ще перевірити contraction aliases до freeze, без M51-only normalizer. Граматично доречна альтернатива, що не виконує explicit target model, не називається глобально неграматичною.

Translations для шести meaning-choice стимулів показуються лише після перевірки, тому що точне UK формулювання наперед називало б відповідь. Ukrainian situation/instruction при цьому visible одразу. Це явні finite editorial exceptions, не global language/length heuristic.

Під час draft review усунено пряме дублювання сюжетів theory/practice: теоретичний deduction використовує train-at-platform, а власна практика — live video та digit count; теоретичний negative past використовує exact simultaneous city presence, а input — message written today; expectation theory — parcel delivery, практика — download. Авторські inputs не є переформатованими select завданнями.

## Окремий конструктор: інше джерело

Три exact new thematic bank names надав головний агент після незалежного inventory. Це не reuse історичної AllLevels п'ятірки, не копіювання Need і не дозвіл переписувати старі questions/answers/progress. Заплановані 15 new bank questions рахуються окремо від 18 own tasks.

| Target | Exact seeder | Type / CEFR |
| --- | --- | --- |
| Deduction | `Database\Seeders\V3\Polyglot\PolyglotModalsOfDeductionThematicLessonSeeder` | 4 / B1 |
| Past | `Database\Seeders\V3\Polyglot\PolyglotPastModalsThematicLessonSeeder` | 4 / B1 |
| Requests | `Database\Seeders\V3\Polyglot\PolyglotRequestsOffersSuggestionsThematicLessonSeeder` | 4 / A2 |

Native `linked_practice` використовує `theory_links`, `question_types: ["4"]` і один exact seeder відповідної теми. Його factual five questions, snapshots, stable keys, answers, token instances, links та contract — окрема main-owned перевірка перед guarded apply. Цей авторський draft не заявляє перевірку їхніх фактичних DB IDs або browser 1/5.

## Джерела та чесний статус доступу

Нормативні джерела використано для конкретних спірних форм/значень, не для копіювання готових вправ. Усі нові exercise situations власні. Учнівські коментарі, навіть на видавничій сторінці, не використовуються як мовна норма.

- [British Council: present deductions](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-deductions-about-present): direct publisher HTML, основний Grammar explanation.
- [British Council: past deductions](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-deductions-about-past): direct publisher HTML, основний Grammar explanation.
- [British Council: perfect aspect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/perfect-aspect): direct publisher HTML, основний Modals with the perfect; підтверджує точку оцінювання та expected should have.
- [Cambridge: could](https://dictionary.cambridge.org/grammar/british-grammar/could), [should](https://dictionary.cambridge.org/grammar/british-grammar/should), [must](https://dictionary.cambridge.org/grammar/british-grammar/must), [offers](https://dictionary.cambridge.org/grammar/british-grammar/offers), [suggestions](https://dictionary.cambridge.org/grammar/british-grammar/suggestions), [mind](https://dictionary.cambridge.org/grammar/british-grammar/mind): прямі запити отримали 403; використано індексований видавничий main text, не заявлено повне пряме читання.
- [Cambridge original Requests](https://dictionary.cambridge.org/grammar/british-grammar/requests): direct 403. Фактичний indexed publisher URL, який повернув потрібний main text: [Requests, PL publisher localization](https://dictionary.cambridge.org/pl/grammar/british-grammar/requests). Англійський body має ті самі навчальні моделі; спосіб доступу не приховано.
- [Cambridge: would like](https://dictionary.cambridge.org/grammar/british-grammar/would-like): publisher-indexed main text про noun/to-infinitive та відмінність від загальних уподобань.
- [Cambridge: may](https://dictionary.cambridge.org/grammar/british-grammar/may.): direct недоступний; publisher-indexed main text про possible negative.

Точна дата зовнішнього рецензування, сертифікація або приріст Google ranking/CTR не вигадуються. SEO мета — корисні відповіді про форму, значення, контекст і практику без stuffing або прихованої копії.

## Підсумок перевірок до freeze та наступні кроки

Main перевірив усі навчальні поля та 15 нових bank questions. Ізольований native render/H1/course/standalone review: 14 тестів, 2003 assertions; чинний незмінений practice engine: 22 тести, 18 завдань, 36 видимих варіантів, 12 declared aliases. Це не готовий acceptance report. Попереду точний preview/backup/guarded transaction, repeated no-op та live browser matrix; їх результати не підміняються цими fixture/engine перевірками.

Після freeze змістова правка означає нову version +diff, не переписування історичного manifest. Старі frozen sources/maps та візуальні файли залишаються незмінними. Три погоджені active definitions підготовлено в робочій гілці; застосування до served ROOT і БД — окремий guarded крок.
