# Практика теорії: спільний еталонний шаблон

Дата: 2026-10-10. Робоча гілка: `codex/theory-single-reference-template`.

База цього follow-up: `4ea3f2c30a98d95879c133ce7af2f781d5ea6370`. Це окреме presentation-only продовження єдиного шаблону, не новий контентний пакет і не повторне застосування контенту до БД. Дозволена локальна ціль — `http://gramlyze.loc`; main і production не входять до роботи.

## Статус і останнє уточнення користувача

Реалізацію внесено у вихідний код; цей статус не означає приймання інтерфейсу. Після двох safety stops користувач окремо наказав реалізувати зміни **без подальших перевірок**. Тому після цієї вказівки не запускаються browser/HTTP acceptance, PHP/Node/Vitest тести або read-only DB AFTER. Файлове редагування та огляд scoped diff не є тестовим чи браузерним прийманням.

Цей звіт не встановлює загальний PASS і не оголошує дизайн прийнятим за еталоном. Збережені нижче факти BEFORE не є доказом стану після реалізації. Історичні звіти попереднього етапу не переписані.

## Фактичний scope

Новий read-only реєстр, зібраний до останньої вказівки без подальших перевірок, виявив **18 авторських UK-сторінок, 108 завдань і 174 controls**. Спільний `authored-practice-ui` використовують M39, M40, M41, M43, M44 і M45 — по три сторінки кожного пакета. Кількості взяті з фактичних render-data, а не історичного звіту.

| Page ID | Сторінка на gramlyze.loc | Завдань | Controls |
| --- | --- | ---: | ---: |
| 58 | [Future Forms: Choosing the Right Form](http://gramlyze.loc/theory/maibutni-formy/choosing-the-right-future-form) | 6 | 11 |
| 59 | [Future Continuous vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous) | 6 | 6 |
| 60 | [Future Perfect vs Future Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous) | 6 | 6 |
| 61 | [Future Perfect vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous) | 6 | 6 |
| 62 | [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future) | 6 | 12 |
| 63 | [Will vs Be Going To — Вибір форми](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) | 6 | 11 |
| 186 | [Past Perfect vs Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous) | 6 | 11 |
| 187 | [Past Simple vs Past Continuous](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous) | 6 | 11 |
| 188 | [Present Perfect vs Past Simple](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple) | 6 | 11 |
| 189 | [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | 6 | 10 |
| 190 | [Present Simple vs Present Continuous](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous) | 6 | 10 |
| 191 | [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) | 6 | 13 |
| 192 | [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would) | 6 | 11 |
| 280 | [Narrative Tenses: Past Simple, Past Continuous and Past Perfect](http://gramlyze.loc/theory/tenses/narrative-tenses) | 6 | 9 |
| 321 | [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | 6 | 9 |
| 327 | [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | 6 | 13 |
| 329 | [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | 6 | 6 |
| 330 | [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | 6 | 8 |
| **Разом** | **18 сторінок** | **108** | **174** |

П'ять із цих owners (280, 321, 327, 329, 330) мають лише UK stored content; десять відповідних EN/PL request URLs використовують locale fallback. Це не десять додаткових авторських редакцій. Загальний реєстр практики охоплює 120 owners / 244 stored locale variants; 226 native variants становлять окрему межу збереження попереднього оформлення, а не нові авторські сторінки для редизайну.

## Чим початковий authored UI відрізнявся від еталона

Еталон — [Past Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms).

На базовому commit спільні `theory-practice-exercise` і `theory-practice-heading` уже існували, але authored view самостійно задавав значну частину controls/actions/feedback. Усі authored Check та початкові варіанти отримували синій акцент; кожне ручне поле було `textarea rows="3"`; Reset відображався навіть у порожньому стані; Check залишався ліворуч; controls не мали еталонних літерних маркерів. Окремий початковий score створював додатковий рядок над вправами. `TheoryPracticePresentation::prompt()` передавав heading номер без явного accent, тому діяв default blue.

Мета — перенести ці візуальні частини на спільні native-компоненти, зберігаючи всі 108 завдань / 174 controls, compound grouping і незалежні JS-механіки. У PPC немає всіх авторських compound типів: їхнє оформлення має бути композицією чинних елементів, а не копіюванням відсутнього еталонного типу.

## Реалізація

Нижче описано реалізований код, а не результат виконаного acceptance.

| Візуальна частина | Спільна реалізація у `resources/views/components/` |
| --- | --- |
| Поверхня, header, номер, інструкція | `theory-practice-exercise`, `theory-practice-header`, `theory-practice-heading` |
| Підпункт і літера | `theory-practice-control`, `theory-practice-marker` |
| Варіанти відповіді | `theory-practice-option` |
| Банк і токени | `theory-practice-token-bank`, `theory-practice-token` |
| Поле | `theory-practice-input` |
| Рядок дій і кнопки | `theory-practice-actions`, `theory-practice-action` |
| Feedback і пояснення | `theory-practice-feedback`, `theory-practice-explanation` |

`TheoryPracticePresentation::task()` створює окрему render-only проєкцію поточного завдання. Тип `select` означає form-selection, `choice`/`multi` — meaning-selection, `manual` — sentence-building або явно визначене extended-writing. Скінченні винятки за точними control IDs враховують реальну навчальну мету, не змінюючи stored kind. Колір вибирається спільною функцією за **роллю**, а не номером M-пакета, URL чи номером вправи: form-selection/form-entry → blue; meaning-selection/mixed-response → amber; sentence-building/extended-writing → emerald. Дев'ять явно визначених compound signatures задають загальну роль змішаного завдання; окремі controls зберігають власні ролі.

Сім controls, які вимагають багатореченнєву нотатку/історію, отримують компактне дворядкове поле. Інші поточні manual controls залишаються `textarea` з одним початковим рядком: це зменшує початкову висоту без звуження допустимого введення і зберігає Enter, paste та Ctrl+Enter. Рішення не базується на довжині конкретної правильної відповіді.

Наявний числовий префікс заголовка переноситься у спільний номерний badge без видалення авторських слів. У compound controls літера є окремим технічним маркером. Якщо source label уже починається з літери, вона переноситься, а не дублюється; самостійна літера-label зберігається як доступне ім'я поля.

Візуальні компоненти приймають чинні JS/ARIA bindings. Native `practice-set` переведено на них для всіх чотирьох груп — selects, choices, inputs, rephrase — зі збереженням M38 semantic controls. Authored UI використовує ту саму family для поверхонь, заголовків, controls, токенів, полів, дій та feedback. Це не третя незалежна копія native markup.

У authored UI Check розміщений у спільному нижньому рядку, праворуч на desktop; Reset стає видимим після введення/вибору або перевірки. Початковий score не займає окремий великий верхній блок: компактний status розміщено після вправ і показано після перевірки без скидання відповідей. Видимість пояснення залишається прив'язаною до чинного checked state. No-JS повідомлення винесене за межі spacing stack, щоб прихований елемент не утворював зайвого верхнього відступу.

Пакетні wrappers залишають guards і фабрики механіки, не задають нову палітру. Для noncanonical caller попередній authored view збережено в compatibility boundary; теорія явно вибирає канонічне представлення. Код scoring, shared authored JS і контент нижнього sentence-builder widget не редагуються в цьому refactor. Це опис файлових меж реалізації, **не** підтвердження відсутності live-регресій курсів чи окремих тестів.

Обов'язкова межа змін: представлення header/control/token/input/action/feedback, без зміни source tasks, answers, aliases, tokens, scoring, retry/reset, linked-bank scope, UUID, anchors, metadata або прогресу. Masters, definitions і content-patches не є способом змінити висоту чи оформлення поля. Нижній own-bank sentence-builder залишається окремим функціональним widget, не копіюється до кожної авторської вправи.

## Уже збережений незалежний BEFORE

Ці матеріали зібрано **до** останньої вказівки не виконувати подальші перевірки. Приватні JSON, screenshots, snapshot-и та runtime proofs не включаються до commit.

| Доказ | Фактично зафіксовано |
| --- | --- |
| Файловий BEFORE | `storage/app/practice-template-local/source-before-v1.json`; окремий snapshot джерел, не браузерний proof |
| Read-only registry/DB BEFORE | `registry-before-v1.json`, SHA-256 `b1cb48864c8b4bdfcaf3eefaa331173807482ead021a378a97685560363503a3`; 46 таблиць / 1 290 473 рядки |
| Physical BEFORE | `physical-target-before-v1.json`; окремий локальний доказ цілі, не твердження про новий web/CLI nonce proof |
| PPC live BEFORE | `reference-before-v1/manifest.json`, SHA-256 `e605d8bc0a1706fa68dd7fe5e063ab5bbc491168889b7f0b42dccd65aaf476ec` |
| Reference matrix | **4/4 збережені стани**: 1440×1000 і 390×844, light/dark; у кожному initial → selected → checked → reset, разом 16 screenshots |
| Actual UI interaction BEFORE | Через видимі кнопки та поля, без прямого присвоєння відповідей Alpine: selects 2/2, choices 2/2, inputs 2/2; після Reset — порожні answers, checked=false, 0/2 у кожній групі |
| Assets/fonts BEFORE | `catalog-public-B6pkGWNt.css`, `catalog-public-CgUm4gux.js`; фактично завантажені Manrope/Archivo, viewport/DPR/sidebar записані у proof |
| Diagnostics BEFORE | 0 page/console/HTTP errors, 0 blocked requests і 0 виявленого overflow у цих чотирьох reference captures |
| Linked bank BEFORE | Scope із 5 питань зафіксований; випадковий банк **не** заявляється повністю пройденим |
| Offline freshness на момент відновлення | 118 runtime/source/asset hashes PPC capture збігалися; це перевірка до наступної реалізації, **не AFTER** |

Локальні reference screenshots (у кожній папці також є `selected`, `checked` і `reset`):

- [Desktop light BEFORE](D:/DEV/htdocs/gramlyze.loc/storage/app/practice-template-local/reference-before-v1/PPC-1440-light-initial.png)
- [Desktop dark BEFORE](D:/DEV/htdocs/gramlyze.loc/storage/app/practice-template-local/reference-before-v1/PPC-1440-dark-initial.png)
- [Mobile light BEFORE](D:/DEV/htdocs/gramlyze.loc/storage/app/practice-template-local/reference-before-v1/PPC-390-light-initial.png)
- [Mobile dark BEFORE](D:/DEV/htdocs/gramlyze.loc/storage/app/practice-template-local/reference-before-v1/PPC-390-dark-initial.png)

Affected-pages live BEFORE не завершено. Отже, **18×4 не є виконаним покриттям**, а лише встановленим обсягом матриці, яку передбачало початкове завдання. AFTER screenshots відсутні.

## Safety stops і зміна scope перевірок

Windows Computer Use двічі повернув однакову заборону:

> Computer Use has been stopped for this turn because it could not determine the current browser URL on Windows with enough confidence to enforce policy. Stop your work and send a final message noting why Computer Use ended.

Перший stop стався до уточнення про відновлення. Другий — після повідомлення користувача, що Edge відкритий на PPC, і повторного вибору вікна через `list_apps` / `get_window_state`. Жодного input через Windows Computer Use у цих спробах не виконано. Точні часи stops не збережено, тому тут вони не вигадуються.

URL guard не вимикався, очікувана адреса не підставлялася замість визначеної, заблокована дія не повторювалася іншим інструментом. Після stop робота була обмежена файлами. Надалі користувач наказав продовжити реалізацію без перевірок; це не дозвіл обходити safety barrier.

**Перевірка зміни browser zoom не виконувалася і не входила до цього етапу.** DPR/viewport не подаються як доказ фактичного browser zoom; непідтверджені «100%» не заявляються.

## Що не перевірено після реалізації

За останньою вказівкою користувача не запускаються:

- AFTER matrix 18×4 та повторні PPC reference states;
- усі 108 authored flows / 174 required controls, wrong → wrong → edit → correct → repeat → reset;
- нові keyboard/ARIA, duplicate tokens, окремий `?`, attached question mark, contractions, no-answer-leakage та long-answer browser checks;
- 320 px, no-JS, доступність self-check keys і fresh console/HTTP/overflow acceptance;
- live незмінність теорії над практикою, courses та `/test` routes;
- PHP/Node/Vitest acceptance і frozen-source validation tests;
- read-only DB AFTER та BEFORE/AFTER diff.

**DB diff 0 updated / 0 inserted / 0 deleted не заявляється**, оскільки нового AFTER snapshot немає. Презентаційна реалізація не передбачає content apply, seeding, migrations, restore або запису прогресу; це межа виконуваних дій, а не виміряний DB diff. Так само незмінність frozen sources, metadata і навчального контенту після рефакторингу не підміняється результатами попереднього етапу.

## Handoff

Файлові правки перенесено в served ROOT `D:/DEV/htdocs/gramlyze.loc` точковими hunks. Збережено дві сторонні локальні вставки Past Perfect Continuous у native practice-set; вони не включені до цього commit. Нижній bank widget та JS-моделі відповідей не редагувалися.

Локальну збірку лише `catalog-public.css` / `catalog-public.js` виконано: CSS `catalog-public-BG9H-hya.css`, JS `catalog-public-CgUm4gux.js`; інші manifest entries збережено. Це крок реалізації, не browser/test acceptance. Generated assets, приватні proofs і snapshot-и не включені до commit.

Handoff — scoped commit і normal push `codex/theory-single-reference-template`; остаточний SHA повідомляється окремо після push. Без deploy, main changes або production HTTP/DB. Подальше тестове/live-приймання не виконувалося за прямою вказівкою користувача.

## Виправлення помилки 500 після інтеграції

2026-10-10 користувач повідомив про HTTP 500 на Will vs Be Going To. Причина — змішування inline `@php(...)` із наступним `@php … @endphp` у `authored-practice-ui`: Laravel захоплював проміжну Blade-розмітку як PHP та отримував `unexpected token endforeach`. Усі PHP-вставки цього шаблону переведено в явні блоки; навчальні дані й JS не редагувалися.

Для діагностики конкретного збою скомпільовано 16 шаблонів практики й перевірено синтаксис отриманого PHP — помилок немає. GET `http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to` після виправлення повернув HTTP 200. Це підтверджує усунення серверного збою на цій сторінці; повна візуальна матриця та інтерактивне приймання не виконувалися.

