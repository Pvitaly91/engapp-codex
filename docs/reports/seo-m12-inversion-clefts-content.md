# M12 — інверсія, cleft sentences та смисловий акцент

Дата: 27.09.2026. Проєкт: `Pvitaly91/engapp-codex`.

## База й межі

Після fetch прийнята гілка `origin/codex/seo-m11-linking-words-content` має SHA `fe2544e8364437f661d8e4cd6bfa084776604836`. Від неї створено `codex/seo-m12-inversion-clefts-content`. Повторно використано вільний ізольований checkout `storage/app/seo-m11-worktree`; його назва залишилась історичною. Основний checkout на `codex/production-ready-a14788dac`, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd`, зі сторонніми змінами не перемикався й не скидався. M11, його sources, manifest, backup/restore та правило локального застосування збережені.

Контентний scope — тільки три українські definitions, їхні наявні subtitle/hero/box. Сусідній C1 `CleftSentencesEmphasisTheorySeeder` прочитано, але не редаговано. Питання, answers/options/verb_hint, алгоритми Mixed, progress, EN/PL, routes, renderer, CSS/JS застосунку, sitemap і SEO middleware не змінювалися. Build не потрібний: змінені JSON не входять у Tailwind scan; нових build inputs немає.

Production не перевірявся й не змінювався. `.com` у canonical аналізується лише як рядок. Немає SSH, деплою, PR/merge, push у main, міграцій, пересівання робочої БД, повного дампу, cache clear, залежностей або нового SEO-crawl.

## Ідентичності та фактичні URL

Повні `Page.seeder` і source:

1. `Database\Seeders\Page_V3\BasicGrammar\WordOrder\InversionBasicsTheorySeeder` → `database/seeders/Page_V3/BasicGrammar/WordOrder/InversionBasicsTheorySeeder/definition.json`.
2. `Database\Seeders\Page_V3\SentenceStructure\CleftSentencesBasicsTheorySeeder` → `database/seeders/Page_V3/SentenceStructure/CleftSentencesBasicsTheorySeeder/definition.json`.
3. `Database\Seeders\Page_V3\BasicGrammar\WordOrder\AdvancedFrontingAndEmphasisTheorySeeder` → `database/seeders/Page_V3/BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder/definition.json`.

| Незмінний title/H1 | Рівень | Page ID | UK block IDs: subtitle / hero / box | Категорії від кореня |
|---|---|---:|---|---|
| Inversion Basics | B2 | 288 | 8752 / 8755 / 8758 | basic-grammar → word-order |
| Cleft Sentences Basics | B2 | 289 | 8751 / 8754 / 8757 | sentence-structure |
| Advanced Fronting And Emphasis | C2 | 313 | 8826 / 8827 / 8828 | basic-grammar → word-order |

Числові ID — інвентаризація саме робочої локальної БД, не переносні параметри. У кожного уроку рівно три UK блоки з порядком 0/1/2. Їхні UUID, типи, column, owner, category, seeder, levels і pivots збережено; updater знаходить їх за seeder/UUID та перевіряє всі незмінні поля.

| Урок | Теорія | Основний тест |
|---|---|---|
| Inversion Basics | [Локальний урок](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics) | [Mixed](http://gramlyze.loc/test/word-order/inversion-basics) |
| Cleft Sentences Basics | [Локальний урок](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-basics) | [Mixed](http://gramlyze.loc/test/sentence-structure/cleft-sentences-basics) |
| Advanced Fronting And Emphasis | [Локальний урок](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis) | [Mixed](http://gramlyze.loc/test/word-order/advanced-fronting-and-emphasis) |

Адреси отримано з фактичного ancestry та чинних `TheoryCourseManifestService` / `TheoryPageTestSlug`, потім підтверджено GET і наявними test href. Не виводилися лише з PHP namespace. Для кожної існує курсова адреса `/courses/english-grammar-theory/lesson/` + шлях категорій + slug. Для live-приймання обрано [курсову копію Inversion Basics](http://gramlyze.loc/courses/english-grammar-theory/lesson/basic-grammar/word-order/inversion-basics).

Посилання між трьома уроками — звичайні href. З Cleft Basics є чесно описане посилання на [наявний короткий C1 урок](http://gramlyze.loc/theory/sentence-structure/cleft-sentences-emphasis), без обіцянки відсутнього розгорнутого матеріалу. Кнопку «Пройти тест» не дубльовано, auto/filter/step не додано.

## Початкові джерела → недоліки → редакція

Read-only інвентаризація підтвердила byte-exact відповідність усіх трьох робочих сторінок початковим definitions: англомовні subtitle/hero, три короткі rules, службовий box. Невідомих ручних правок або часткового застосування не знайдено.

### Inversion Basics

Початкове правило про never/rarely та not only було надто широким без пояснення початкової позиції й ролі підмета. Замість службового «theory anchor» — український урок про допоміжне/модальне перед підметом, do/does/did + базову форму, never/rarely/seldom, only after/when, not until, not only … but also.

Власні пари: `Mira rarely misses a deadline.` → `Rarely does Mira miss a deadline.`; `We understood the map only after the guide explained it.` → `Only after the guide explained it did we understand the map.` Пояснено, чому інверсія стосується головної, а не часової частини. `Only the guide understood the map` і `Not only Lena but also Omar checked the figures` не потребують інверсії. Емфатичне `did know` не оголошено взагалі неграматичним: воно просто не вимагається subject-only. Великий розділ умовної інверсії не додавався.

### Cleft Sentences Basics

Замість короткого переліку шаблонів — одна ситуація про надсилання плану з різним фокусом «хто / що / коли», it-cleft і what-cleft, контекст, переклади, час і типові помилки. `It was Maya who sent the revised plan on Tuesday` протиставлено `It was on Tuesday that Maya sent the revised plan`. `What do we need is …` виправляється на `What we need is …`.

Для особи явно дозволені who/that; пояснено обмежене опускання сполучного елемента, зайві займенники та множинний підмет. `It is cold outside` не названо cleft. Узгодження часу не перетворено на абсолютну вимогу однакових форм у всіх частинах: наведено теперішнє уточнення минулої події. C1-сусід не змінений і не скопійований.

### Advanced Fronting And Emphasis

Початковий hero змішував fronting, locative inversion і what-cleft як приклад загального emphasis, а box не розкривав тему. Нова редакція окремо розрізняє fronting без інверсії, subject–auxiliary inversion і повну verb–subject inversion; показує контекст та обмеження, а не декоративне переставляння слів.

Власні приклади: `That explanation I can accept` не змінює порядку підмета й присудка; `Beyond the orchard stood a stone cottage` вводить новий іменниковий підмет після місця; `Here they come` зберігає порядок із особовим займенником. Не всі інверсії із займенниками заборонені: `Never have they seen this map` правильне. Авторський абзац про архівістку, рукописні карти й друковані копії показує перехід від сцени до контрасту та висновку; кожну конструкцію розібрано окремо.

### Самоперевірка

По шість авторських завдань і шість пояснених ключів у кожному уроці, 18 загалом. Інверсія тренує never, базову форму після does, only after, not until, not only та розпізнавання винятків. Clefts — фокус на особі/даті, зайвий займенник, порядок у what-clause, контекстну потребу та розпізнавання справжнього cleft. C2 — додаток без інверсії, locative inversion, personal pronoun, класифікацію трьох механізмів, нейтральний розклад і повернення до звичайного порядку.

Редакторськи перевірено всі 18 ключів. Відкриті відповіді або мають явні альтернативи (зокрема who/that), або обмежені конкретним початком/конструкцією. У C2 граматичне fronting без повної інверсії не названо помилкою мови — лише невиконанням точної інструкції. Збережено M11-оформлення h4/decimal ol, власний scroll-контейнер таблиць і нативний details. Немає нових SavedGrammarTest.

## Джерела граматичної перевірки

Використано для перевірки правил, не копіювання вправ або прикладів:

- [British Council — Inversion after negative adverbials](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/inversion-after-negative-adverbials): сторінка прочитана напряму; допоміжні дієслова, початкові обставини та інверсія головної частини.
- [Cambridge — Inversion](https://dictionary.cambridge.org/grammar/british-grammar/inversion), [Fronting](https://dictionary.cambridge.org/grammar/british-grammar/fronting), [Cleft sentences](https://dictionary.cambridge.org/grammar/british-grammar/cleft-sentences-it-was-in-june-we-got-ma): прямі відкриття дали HTTP 403; прочитано доступний індексований текст видавця. Не заявляється успішне пряме відкриття цих трьох сторінок.
- Індексований [Cambridge — Only](https://dictionary.cambridge.org/grammar/british-grammar/only): фокус на підметі; [Not only … but also](https://dictionary.cambridge.org/grammar/british-grammar/not-only-but-also): позиція й узгоджені конструкції; [Here and there](https://dictionary.cambridge.org/grammar/british-grammar/demonstratives-this-that-these-those-some): порядок з особовим займенником.

Пояснення й ключі перевірені редакційно окремо від автоматичних structural assertions. Рівні B2/B2/C2 збережено як класифікацію Gramlyze, без заяв про CEFR-сертифікацію.

## Локальний updater і докази цілі

`InversionCleftsContentPatch` використовує transaction/backup/restore існуючого `LinkingWordsContentPatch`, але має власний manifest `database/content-patches/m12-inversion-clefts-before.json` і три точні повні seeder identities. Для різних категорій перевіряється повний ancestry, включно з циклом/глибиною та language/type. М11 allowlist не замінений і не розширений.

Shared helper-и отримали лише фіксовані package hooks; M11 ID/version/names/sources/snapshot/backup залишилися сумісними. Це перевірено не тільки fixtures: read-only порівняння нормалізованого реального M11 backup з поточним M11 plan збіглося; поточний M11 plan має нуль змін.

М12 команда — `content:patch-inversion-clefts-m12`; adapter `tools/diagnostics/run-m12-working-local.php` завантажує незмінний bootstrap/vendor/.env основної робочої копії та definitions із M12 checkout. Він не є довільним SQL чи Artisan executor. M11 adapter лишився окремим.

Фізична перевірка підтвердила локальний MySQL `gr2`, hostname `DESKTOP-3C05HGF`, port 3306; реальний Windows listener/PID-файл, Apache vhost/DocumentRoot, loopback DNS і nonce-bound web/CLI digest збіглися. Одного імені БД чи APP_ENV недостатньо. `.env` та APP_KEY не редагувалися, `APP_ENV=production` збережено; write потребує явного `--local-target=gramlyze.loc` зі свіжим фактичним web proof.

Приватний тимчасовий GET diagnostic був обмежений Host/loopback/basePath, тільки SELECT і digest, без секретних значень та HTTP apply. Після apply/no-op його від’єднано; початковий user-modified `routes/api.php` відновлено byte-exact (SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`). Diagnostic URL після прибирання повернув 404. Приватні докази залишені в storage, не в Git.

### Фактичний preview → backup → apply → no-op

Зміни застосовано до робочого `gramlyze.loc` 27.09.2026. Preview `storage/app/seo-m12-local/m12-preview-20260927.json`, SHA-256 плану `46e2ff2f66eed0de7b4a2364c5083e6feb4ce9f4f898d984aeb2bb69c0b31b44`, відповідає фінальним sources. Before-значення перевірені точно, без trimming/нормалізації; manual/stale/partial конфліктів немає.

Оновлено **12 записів**:

- `pages.text`: 288, 289, 313;
- `text_blocks.heading/body`: 8752, 8755, 8758, 8751, 8754, 8757, 8826, 8827, 8828.

Це фактичний результат, а не припущення за M11. Exclusive record-backup завершено до першого UPDATE, apply виконано з row locks, транзакцією й postcondition. Команда повернула `status=applied, updated=12`; повтор із тим самим preview — `status=no-op, updated=0`, зайвий backup не створений. No-op також підтвердив точну відповідність післястану definitions.

Backup: `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m12-local/m12-records-20260927.json`, 4 514 286 bytes, SHA-256 `32b8b0a37082114c7295d4bb80fd7c75e66f494e1a6f16ecb3bbc39a726fba01`. Це адресні records/контрольні зв’язки трьох уроків, не дамп усієї БД. Не комітиться. Restore перевірено на fixtures; робочий результат не відкочувався. Подальший restore потребує нового фактичного loopback runtime proof; збережений proof-файл без живої перевірки не дає дозволу на запис.

```text
php tools/diagnostics/run-m12-working-local.php content:patch-inversion-clefts-m12 --apply --local-target=gramlyze.loc --local-proof=<приватний-перевірений-basename> --plan=m12-preview-20260927.json --backup=m12-records-20260927.json --database=gr2
```

Вузький versioned manifest зберігає точні before definitions і три повні seeder identities; фінальні after definitions версіонуються поруч. Updater перетворює їх на адресні переходи існуючих Page/UUID, не використовує локальні числові ID як переносні і не створює production-деплой.

### Незмінність даних і метаданих

До/після прочитано **всі 46 таблиць**: кількості й SHA-256 відсортованих рядків збігаються після виключення лише дозволених content-полів конкретних 12 записів. Різниць захищених даних — **0**. Це охоплює M11, C1-сусід, інші pages/blocks/локалі, banks, answers/options/verb_hint, tags/pivots, users і progress. Окремо всі три M11 уроки точно відповідають прийнятим definitions; `.env` hash незмінний.

GET before/after: усі три theory, три основні тести й курсова копія — 200. Title/H1/canonical/meta robots/X-Robots-Tag незмінні; локальний X-Robots-Tag — `noindex, nofollow, noarchive`. Змінилися тільки дозволені descriptions трьох уроків; у кожного meta = OG = Twitter. Descriptions тестів і курсу незмінні. Глобальний PageMetadata не редагувався.

Один повний ordered sitemap before/after: **554 URL** (фактичний count, не hardcode), порядок і склад однакові; SHA-256 ordered loc array `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.

## Підсумки приймання

### Автоматичні перевірки

- Фінальний ізольований PHP-прогін шести цільових suites M12/M11: **79 tests, 1008 assertions, exit 0**. Перевірено структуру/рендеринг, 18 завдань із ключами, immutable identity, old→new/no-op/conflict, backup/restore/rollback, відмову непідтвердженій цілі та сумісність M11. Використано окремий SQLite/runtime, без робочої `.env` і запису в робочу БД. Залишився один наявний PHP 8.5 deprecation для `PDO::MYSQL_ATTR_SSL_CA` у `config/database.php`; його виправлення поза scope.
- `node --test tests/Browser/seo-m11-local.test.cjs tests/Browser/seo-m12-local.test.cjs`: **10/10 успішно**. Перевірено вузькі URL, незмінність контрольних metadata/ordered sitemap, вимоги реального післястану та CSS color parser.
- `git diff --check`: без whitespace-помилок. Build і повний repository suite не запускалися: build inputs не змінено, приймання обмежене M12 та необхідними M11 регресіями.

PHP evidence: `storage/app/seo-m2-local/m12-final-117009cee15d4a56a9f6fb6f33222fbd-result.json` в ізольованому checkout. Це приватний runtime output, не файл пакета.

### Реальний браузер після apply

Chromium `147.0.7727.15`, desktop **1440×1000**, mobile **390×844**. Фінальний `browser-applied` завершився з exit 0: **6/6 сценаріїв** — кожен із трьох уроків в окремих desktop і mobile contexts. Кожен сценарій перевіряє світлу й темну тему через справжню кнопку інтерфейсу.

- Оригінальний HTTP response містить нові шість завдань і шість ключів. Server/DOM content hashes збігаються; reload зберігає контент і metadata. Немає `setContent`, `route.fulfill`, підміни `innerHTML` або fixture-приймання.
- Матеріал нижче першого екрана, український вступ, видима decimal-нумерація та розкриті пояснення перевірені автоматично й на збережених screenshots. Клавіші Enter → Space → Enter відкривають, закривають і знову відкривають details у всіх шести сценаріях.
- Горизонтального overflow всієї сторінки немає. На mobile таблиця 640 px прокручується всередині контейнера 266 px; реальний scroll до правого краю — 374 px. Текст поза таблицями переноситься нормально.
- Виміряний мінімальний контраст вибраних текстових елементів: світла тема **5.347:1**, темна **9.493:1**. Це вибіркова перевірка, не повний accessibility-аудит. Перша спроба хибно трактувала CSS `color(srgb 1 1 1)` як значення RGB 1 замість 255. Виправлено лише діагностичний parser, додано регресійні тести; пороги не послаблено, application CSS не змінювався. Початковий невдалий звіт збережений окремо від фінального успішного.
- З desktop реально натиснуто кожне з трьох основних посилань «Пройти тест», перевірено кінцевий URL і завантажений H1; на mobile додатково перевірено href. Тести й алгоритми відповідей не змінювалися.
- Uncaught JavaScript errors — **0**. Запити Google Fonts CSS дали `net::ERR_NETWORK_ACCESS_DENIED` через обмеження мережі середовища; використовувався fallback-шрифт. Окремо read-only browser guard навмисно зупинив три POST `/test/.../state`, щоб не записувати progress. Ці перервані запити не подаються як несправність контенту.

Для курсової копії Inversion Basics підтверджені GET 200, H1 та новий матеріал в оригінальному HTML. Звичайний гість бачить штатне «Урок заблоковано»: `contentVisible=false`. Gate не обходився, урок не розблоковувався. Повне відображення курсового renderer перевірено ізольованими тестами, але не заявляється видимим у живій гостьовій курсовій сторінці.

Приватні HTTP/browser докази та screenshots збережені в `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m12-local/`: `before-http.json`, `after-http.json`, `after-comparison.json`, `working-verified-browser.json`, `working-verified-*.png`. Зображення відкрито й візуально переглянуто для всіх трьох уроків на desktop/mobile у двох темах, з ключами, прокручуваними таблицями та окремою курсовою gate-сторінкою. Приватні докази, backup і proof не входять у commit.

## Передача пакета

Пакет містить тільки три definitions, вузький before-manifest, M12 updater/adapter, необхідні спільні hooks, цільові тести/діагностику й цей звіт. Зміни застосовані до робочого `gramlyze.loc`, а не лише до fixtures. Для Git використовується звичайний commit/push у `codex/seo-m12-inversion-clefts-content`; остаточний SHA та перевірку його збігу з remote наведено у фінальній відповіді.

Production не перевірявся й не змінювався. Пакет не застосований на `.com` або `.ub`; PR, merge і деплой не виконувалися.
