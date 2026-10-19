# Компактні варіанти native-практики без передчасного авторського ключа

Дата: 2026-10-10. Гілка: `codex/theory-single-reference-template`.
Вихідний commit: `12af95640d4c200f3d75fd0e3c3374876ecc347d`.

## Причина follow-up

Попереднє [виправлення структури сторінок](theory-passed-pages-layout-correction.md) прибрало зайві картки теорії та uppercase у довгих варіантах практики. Воно не усунуло іншу помилку: деякі native-варіанти містили повний авторський ключ — правильне речення, переклад і пояснення — ще до відповіді користувача. Загальний нижній блок авторських пояснень також був відкритий до перевірки.

Причина міститься у presentation-шляху, а не в розмірі шрифту. У частині проєкцій M35–M38 `plain(author_self_check.answers[source_index - 1])` використано як правильний `select.options[]` або як варіант A усередині `choice.prompt`. Ці рядки одночасно є значеннями, на які спирається перевірка. Їх не можна замінювати в даних або JS заради коротших кнопок.

Цей follow-up відокремлює видимий варіант від незмінного значення відповіді. Він не переписує frozen sources, умови, правильні відповіді чи правила оцінювання.

## Межі та статус доказів

Scope — **36 UK native-практик M27–M38**, тобто сторінки 1–36 реєстру попереднього звіту. Їхній перелік, identities і локальні URL залишаються в [M42 registry](../../database/content-patches/m42-native-design-registry.v1.json). Authored-практики M39–M45, еталонні PPC, courses і окремі тести не є ціллю зміни.

Перед редагуванням збережено незалежний приватний `practice-compact-local/file-before-v1.json`: початкові байти чотирьох наявних файлів у ROOT і WT та відсутність нового display JSON. Snapshot походить від commit вище й не входить до Git.

За прямою вказівкою користувача цей прохід — **file-only**. HTTP, браузер, тести, PHP/Blade compile validation, app bootstrap, БД, apply, міграції, сервер і production не запускалися. Числа нижче отримані читанням versioned JSON/PHP/Blade як джерел. Це не live acceptance і не твердження, що сторінки вже перевірено в браузері.

## Аудит source-моделі

У 36 практиках є **216 завдань: 66 selects, 72 choices і 78 inputs**. Rephrase не додає завдань до цього набору.

| Джерела | Сторінок / завдань | Встановлена модель |
|---|---:|---|
| M27–M29 | 9 / 54 | Немає `author_self_check` та `source_index`; короткі варіанти й A/B/C є самими відповідями. Нової display-проєкції немає. |
| M30–M32 | 9 / 54 | Є повний авторський контекст і ключі; варіанти не потребують скорочення. Потрібне відкладене показування ключів. |
| M33–M34 | 6 / 36 | Відкладене показування ключів; лише два конкретні поля мають компактну display-редакцію. |
| M35–M38 | 12 / 72 | Відкладене показування ключів; у кожній практиці є поля з повним ключем/поясненням, що потребують окремого початкового display. |

Отже, **36 сторінок прочитано, 27 мають source-linked авторські ключі, 14 мають змінені display-поля**. Це різні множини, не 36 змінених сторінок і не 14 окремих завдань.

Для 162 завдань M30–M38 зв’язок заданий у джерелі:

- `source_index` посилається на початковий номер авторського завдання;
- `context` містить `author_self_check.prompts[source_index - 1]`;
- `author_explanation` містить `author_self_check.answers[source_index - 1]`;
- порядок native-груп може не збігатися з порядком авторських завдань. Наприклад, у M37 Inversion selects відповідають номерам 1/4, choices — 2/5, inputs — 3/6.

Тому ключ не можна відкривати за номером видимої групи або після будь-якого Check на сторінці. Потрібен зв’язок конкретного `source_index` із його `{group, index}` та станом заповненої відповіді.

## Скінченна display-проєкція

Новий [theory-native-practice-display.v1.json](../content/theory-native-practice-display.v1.json) містить **14 targets і 53 змінені поля**: 34 labels у 20 select items та 19 choice prompts. Незмінні варіанти не дублюються в map.

Pinned metadata SHA-256: `4d08ff3ce28a8746fdfdab4e112c37d34f50d7acbd205292e9949790d6ffb2f0`. Цей hash використовується кодом для довіри до скінченного display-файлу (UTF-8/LF, один кінцевий LF); він не є результатом HTTP, браузерного чи тестового приймання.

| Page ID | Сторінка | Пакет |
|---:|---|---|
| 298 | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language) | M33 |
| 322 | [Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) | M34 |
| 296 | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures) | M35 |
| 305 | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative) | M35 |
| 319 | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style) | M35 |
| 303 | [Modal Perfect and Deduction](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) | M36 |
| 304 | [Subjunctive and Formal Structures](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) | M36 |
| 320 | [Subtle Modal Meanings](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) | M36 |
| 286 | [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns) | M37 |
| 300 | [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) | M37 |
| 295 | [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials) | M37 |
| 306 | [Advanced Article and Quantifier Nuance](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance) | M38 |
| 317 | [Precision With Articles And Determiners](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners) | M38 |
| 316 | [Advanced Collocation And Lexical Choice](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) | M38 |

Кожен target називає identity, source-файл, точний block index і body SHA-256. Кожне поле має native index, `source_index`, source SHA-256 і окремий display. Це code-owned presentation metadata, не нова версія навчального payload. Рішення не походить із кількості символів, мови рядка, регулярного обрізання речень чи номера пакета.

### Що залишається перед перевіркою

- Повний початковий `context`/author prompt, включно з усіма умовами, обмеженнями та підпунктами.
- Самі речення-кандидати, потрібні лексичні форми й смислові твердження, між якими треба обрати.
- Усі input/token-bank controls і M38 semantic checks. Останні є частиною оцінюваної відповіді, не feedback.
- Різниця між варіантами, якщо англійські речення однакові, а завдання перевіряє їхнє тлумачення.

Наприклад, Gerund/Infinitive та Collocation мають варіанти з однаковим англійським реченням, але протилежними твердженнями про значення. Українські смислові твердження тут не прибираються. У Modal/Subjunctive завдання може цілком перевіряти українське пояснення; така відповідь не перетворюється на порожню кнопку або штучний короткий ярлик.

Для Page300 окремі select labels показують граматичні речення-кандидати без довгого перекладу та розбору `whose/who’s`. Умова про власника й предмет належності залишається повністю видимою; повний розбір залишається у ключі після перевірки. У choice про пропуск relative pronoun display зберігає також другу частину завдання — вибір `who/whom` у реченні про restorer.

Два точкові рішення M33/M34:

- Hedging, source 4: лишаються обидві перевірювані відмінності — ступінь упевненості та прогноз/заборона; прибирається тільки додатковий коментар між ними.
- Paraphrase, source 1: прибирається лише окремий український переклад прикладу; зберігаються конкретизація часу та неможливість вивести 9.15 лише з `in the morning`. Інший accepted option залишається окремим значенням.

Довгі багатореченнєві кандидати M33/M34, які самі є потрібною редактурою або аргументованою відповіддю, не скорочуються. Український token-bank Causative, що пропонує скласти пояснення, теж не є зайвим ключем: він залишається навчальним завданням із незмінними токенами.

### Що доступне після перевірки

Повні source option strings/choice prompt доступні в штатному закритому disclosure «Повні варіанти й пояснення» з умовою `isChecked(group) && !isEmpty(group, index)`. Сама кнопка вибору продовжує передавати початкове значення, не компактний label. Disclosure не має `x-cloak`, тому без JavaScript його можна розгорнути й прочитати оригінал.

Загальний блок ключів показується після Check хоча б одного заповненого item; кожний його пункт має власну умову за `source_index → {group, index}`. Номери ключів зберігаються через `li value`. Порожній Check не відкриває всі відповіді; перевірка однієї групи не відкриває інші. Для початкового JS-стану контейнер має `x-cloak`, а окремий `noscript` містить повний ключ у штатному disclosure. Повні авторські умови в наявному no-JS блоці залишаються незмінними.

## Реалізаційна межа

Власні runtime-файли цього follow-up:

- `app/Support/TheoryPracticePresentation.php` — окрема render-only native display-проєкція;
- `resources/views/engram/theory/blocks-v3/practice-set.blade.php` — споживання display замість зміни raw values та показування ключів за item-state;
- `docs/content/theory-native-practice-display.v1.json` — скінченні exact-source presentation mappings.

`TheoryPracticePresentation::nativeDisplay()` вимагає caller-owned theory-контекст, чинний `M42NativeDesignPackage::binding()` і тотожний переданий design. Для display target додатково звіряються slug, source-файл, block index, body SHA та hashes конкретних рядків. Повний однозначний `source_index → {group, index}` coverage потрібен і для key-only owners. Різні raw answer values не можуть отримати однаковий display label; помилка чи невідповідність повертає `null` із початковим представленням. Ці твердження описують прочитаний код, а не виконану перевірку guards.

Цей report супроводжує файлову реалізацію. Опис взаємодій вище отримано читанням Blade-коду, не виконанням сторінки. Runtime-hunks, display JSON і документацію синхронізовано в served ROOT `D:/DEV/htdocs/gramlyze.loc`; наявні сторонні PPC hooks у native view збережено. Відсутній сторонній ROOT `.gitattributes` не відновлювався: LF-правило нового hash-bound JSON додано тільки у робочій гілці. Нові JS/CSS або rebuild для цієї зміни не потрібні. Сам report не є свідченням live-перевірки.

Незмінні контракти: frozen masters/plans/projection writers; raw `options`, `answer`, `accepted`, aliases, source indices, tokens та scoring; JS-модель і reset; linked-bank widget; theory basic/detail; caller-owned theory boundary. Курси та неідентифікований/непідтверджений payload мають зберігати початкове представлення. Невідповідність identity/body/field guard не дозволяє застосовувати map до схожого тексту.

Generated build, приватний BEFORE, runtime caches і сторонні зміни не входять до цього report або цільового commit. Нового візуального PASS, DB apply чи production deployment цей прохід не заявляє.
