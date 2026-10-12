# M48 — In case, Wish / If only, Would rather

## Результат і межі

M48 застосовано до трьох наявних українських theory pages на робочому `http://gramlyze.loc`. Повні пояснення, переклади, контекстні виправлення та інтерактивна практика використовують чинну універсальну в’юху; редизайну немає.

Actual base: `662c58482c8fc36b4044f7aef8cd260223c3b242`.
Робоча гілка: `codex/seo-m48-conditionals-standard-view`.
Source checkout: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`; served application: `D:/DEV/htdocs/gramlyze.loc`.

| URL | Identity у Database\Seeders\Page_V3\Conditionals | Page ID | HTTP |
|---|---|---:|---:|
| [In case](http://gramlyze.loc/theory/conditionals/in-case) | ConditionalsInCaseTheorySeeder | 46 | 200 |
| [Wish / If only](http://gramlyze.loc/theory/conditionals/wish-if-only) | ConditionalsWishIfOnlyTheorySeeder | 51 | 200 |
| [Would rather](http://gramlyze.loc/theory/conditionals/would-rather) | ConditionalsWouldRatherTheorySeeder | 52 | 200 |

Locale `uk`, category ancestry `conditionals`, display level B1–B2 і technical level B1 збережено. Had better, M46/M47, інші lessons/locales, курси, банки, progress і окремі тести не модернізувалися. Main, production, server configuration, hosts, ACL, dependencies, migrations, workflows і generated assets не змінювалися. Production HTTP не використовувався.

## Потреби читача та редактура BEFORE → AFTER

| Урок | Що уточнено | Межа із сусідніми темами |
|---|---|---|
| In case | Підготовка до можливого майбутнього або вже актуального невідомого стану; минула пересторога; питання й заперечення; just in case; in case of + noun; прозоре зіставлення з if. Call/stay перенесено з безумовних «помилок» у контекстний розбір. | Не навчати in case як механічного if або як абсолютного правила «дія зараз → лише майбутня подія». First Conditional залишається окремою темою. |
| Wish / If only | Стан і процес зараз, жаль про завершене минуле, could, would/wouldn't для людей/погоди/предметів; реальна історія перед виправленням yesterday; was/were за регістром; самостійне If only та wish/hope. | Не перетворювати будь-яке бажання на умовне речення з обов’язковим результатом, не забороняти would глобально за підметом. |
| Would rather | Власна дія без to, not, паралельність із than, інший виконавець, пряме питання/коротка відмова, future reference past form; дві моделі минулого в змістовному detail. | Would prefer має іншу будову. Перевага не є згодою/фактом/обіцянкою; come/came не означає універсального поділу «правильно/неправильно». |

До модернізації кожна definition мала шість блоків і не містила M48 practice-set. Старі корисні приклади й навігацію збережено; точна карта кожного старого поля міститься у versioned editorial. Для In case старі call/stay не стерто, а явно перекласифіковано; для Would rather старе come/came зіставлення стало нейтральним. У Wish збережено корисні старі EN–UK пари й додано контекст процесу та відкритої надії.

SEO-підхід — точна відповідь на потребу читача, не keyword stuffing або обіцянка ranking. Орієнтир: [Google Search Central — helpful, reliable, people-first content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). Локальна перевірка не є вимірюванням зростання позицій у Google.

Граматичні джерела, точний спосіб доступу (full read / indexed excerpt / недоступна сторінка) і pre-freeze review notes наведено в `docs/content/m48-conditionals-editorial.v1.0.0.md`. Notes позначені як історичні; current master має статус editorially-reviewed. Не приписано експертних/корпусних досліджень, яких ця робота не виконувала.

## Frozen sources і контрольні суми

- Master: `docs/content/m48-conditionals.v1.0.0.json`, version 1.0.0.
- Readable: `docs/content/m48-conditionals.v1.0.0.uk.md`.
- Editorial: `docs/content/m48-conditionals-editorial.v1.0.0.md`.
- Незалежна versioned BEFORE: `database/content-patches/m48-conditionals-before.v1.json`.
- Finite projection: `database/content-patches/m48-conditionals.v1.json`.
- Raw SHA-256 inventory восьми файлів: `docs/content/m48-conditionals-checksums.v1.json` (master, readable, editorial, BEFORE, projection, три definitions).

| Immutable source | SHA-256 |
|---|---|
| Master | `6dfe8e8859894bc65fd1ecfcaae487331763a54eeedb0c0b685e680278e9819c` |
| BEFORE | `f5288fd9b2bee018417e40b08b2a9f4e63f58a6ccb9301b14a088d9c0a48c94a` |
| Projection | `4208b6f6bf760f898a260bdb1395ca3c5a47a6085cb41473b6feeb5a47c20a7d` |

Усі вісім файлів байтово збігаються ROOT/WT; definitions структурно точно відповідають projection. Раніше зафіксовані master/BEFORE/projection не переписувалися після freeze. Normalization зафіксувала null existing_source_index і стабільні IDs завдань Would rather, без зміни навчального тексту чи кількості controls. M48-specific .gitattributes зберігає LF для нових JSON/Markdown і raw bytes definitions; старі правила не змінені.

## Стандартні компоненти й виміряні counts

`hero` → вступ/три ключові картки; `forms-grid` → формули й rule rows; `usage-panels` → numbered point panels; `mistakes-grid` → bilingual corrections; `summary-list` → підсумок; `comparison-table` → зіставлення; `navigation-chips` → стара навігація; `practice-set` → штатні channels selects/choices. Details складаються з чинних fragment/usage компонентів, не з окремої M48 верстки.

| Урок | Blocks без subtitle | Forms / rule rows | Usage points / EN–UK pairs | Table rows | Mistakes | Summary | Details / fragments / EN–UK pairs | Tasks / controls / options |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| In case | 8 | 5 / 7 | 6 / 10 | 2 | 3 | 5 | 1 / 2 / 5 | 6 / 6 / 15 |
| Wish / If only | 9 | 4 / 6 | 5 / 10 | 2 | 3 | 5 | 1 / 1 / 2 | 6 / 6 / 15 |
| Would rather | 8 | 4 / 5 | 4 / 8 | 2 | 3 | 4 | 1 / 2 / 2 | 6 / 6 / 15 |
| Разом | 25 | 13 / 18 | 15 / 28 | 6 | 9 | 14 | 3 / 5 / 9 | 18 / 18 / 45 |

Це 18 continuing blocks + 7 нових; 16 основних section blocks без hero/navigation/practice. Кожна практика має три completion-select та три meaning-choice завдання за навчальною метою, не історичну квоту M47. Canonical answers: 18; прийнятні правильні видимі варіанти: 19, бо was і were належать одному control (were canonical, was accepted alias). Більше одного accepted не збільшує score.

### Лише meaningful point-level details

| Disclosure key | Власник | Що поглиблює |
|---|---|---|
| in-case-context-not-blacklist | in-case-uses / sections / 3 | Час дзвінка та два різні плани залишитися вдома; 2 фрагменти |
| wish-state-or-desired-change | wish-uses / sections / 2 | Небажаний стан пристрою проти очікуваної зміни роботи; 1 фрагмент |
| m48-rather-past-preferences | m48-rather-uses / sections / 3 | Власна минула дія have + V3 та інший виконавець Past Perfect; 2 фрагменти |

Короткі застереження, переклади й необхідна базова інформація лишилися visible basic. Рішення міститься у finite author projection; глобального word-count threshold немає. Усі п’ять detail-фрагментів та дев’ять EN–UK пар наявні повністю у фактичному server HTML всередині власних закритих details.

## Metadata, schema й посилання

До apply очікування обчислено окремо зі frozen hero через чинні PageMetadata і AddStructuredData services; AFTER перевірено проти цього запису, а не прийнято автоматично за еталон.

| Урок | Meta description BEFORE | Meta/OG/Twitter description AFTER |
|---|---|---|
| In case | In case не означає те саме, що if. | In case пояснює дію з огляду на можливу ситуацію — майбутню або вже наявну, але невідому мовцеві. |
| Wish / If only | Wish / If only. Wish і if only схожі за значенням: вони показують, що ми хочемо, щоб реальність була іншою. | Wish / If only допомагають уявити, що небажана реальність інша. |
| Would rather | Would rather допомагає сказати, що ми воліємо один варіант більше за інший. | Would rather виражає перевагу: що людина воліє зробити сама або чого хотіла б від іншої людини. |

Title/OG/Twitter title: відповідно «In case — правила | Gramlyze», «Wish / If only — правила | Gramlyze», «Would rather — правила | Gramlyze»; H1 незмінні. Canonical незмінний `https://gramlyze.com/theory/conditionals/{slug}`: це значення локального HTML, не production-запит.

На кожній сторінці один JSON-LD із WebSite, BreadcrumbList, LearningResource. Повний AFTER graph дорівнює BEFORE після заміни лише LearningResource.description очікуваним повним hero intro. Ці descriptions мають 97/224/145 символів і не завжди тотожні короткому meta excerpt. Немає нових FAQ, авторів, ratings, claims або schema nodes.

Page Published = Modified збережені: In case `2026-04-23T17:34:16+03:00`, Wish `2026-04-23T17:34:27+03:00`, Would rather `2026-04-23T17:34:29+03:00`. Нові TextBlock timestamps не підміняють Page dates. Локальний X-Robots-Tag `noindex, nofollow, noarchive` збережений; robots meta був і лишився відсутнім.

Навігаційні learning data byte-exact на всіх трьох сторінках: дев’ять links і поточний пункт. Десять distinct URLs розділу (Zero, First, Second, Third, Mixed, Unless/Provided/As long as, In case, Wish, Would rather, Had better) повернули 200 на gramlyze.loc. Додаткові читачеві посилання в summary/comparison ведуть на ті самі існуючі теми. Sitemap BEFORE/AFTER містить ті самі 554 URLs і має той самий raw hash.

## Локальна БД, backup, apply і no-op

Незалежний BEFORE створено до зміни джерел, із фактичних Pages/TextBlocks та шести theory/course HTTP відповідей. Фізичний local-target proof звірив served application/document identity, loopback MySQL gr2:3306, environment і SiteMode; секрети не поверталися. Для поточного ROOT APP environment production, але SiteMode для gramlyze.loc development — вибір цілі доведений host/root/connection proof, не назвою environment.

Порядок: fresh proof → fresh preview → exact independent diff review → exclusive private backup → transaction → postconditions → fresh AFTER preview → repeated no-op → live acceptance → final read-only audit.

Фактичний diff:

- 3 updates `pages.text`: IDs 46/51/52.
- 21 updates старих UK `text_blocks.body`.
- 7 inserts UK TextBlocks: In case comparison/practice; Wish If-only usage/comparison/practice; Would rather comparison/practice.
- 0 deletes.
- Жодних змін інших колонок старих рядків, seeder/category/type/level/column/css_class/sort/UUID/ID/timestamps.

Exclusive `backup-v1.json` збережено лише у приватному ignored `storage/app/seo-m48-local`; у Git backup/proof/plan/HTML/screenshots не входять. Backup створений до write transaction. У незалежному BEFORE 63 старі rows, з них 21 UK; після apply 70 rows, з них 28 UK. EN/PL rows усіх трьох owners незмінні.

Повторний guarded AFTER preview = state after, 0 updates / 0 inserts / 0 deletes; apply цього збереженого плану = no-op 0/0/0, без нового backup. Перед правильним циклом один запит apply з ще нествореним AFTER-plan був безпечно відхилений до транзакції; після fresh preview повтор завершився no-op. Це операційний повтор, не новий тест і не прихована зміна БД.

Тимчасовий GET-only nonce-route приймав лише raw local host/loopback, без cookie/auth/forwarded headers; не писав у БД. Після apply/no-op його видалено, routes file відновлено byte-exact із сторонніми початковими змінами; окремий GET повернув 404. Route не входить у commit.

Обидва фактичні AFTER audits підтвердили:

- три theory й три course URL = 200;
- 46 protected table fingerprints незмінні, bank/progress changes = 0;
- topic links/banks 432 + 580 + 432 = 1444 збережені;
- course content/title/H1 parity 3/3; сильніше file comparison: весь HTML однаковий, крім двох явно визначених CSRF значень на сторінку;
- 326 freshly inventoried visual files незмінні;
- 1521 freshly inventoried non-target content/source files незмінні, включно з попередніми packages/maps;
- 8 raw checksum files ROOT/WT і 15 source-sync paths підтверджені після browser acceptance.

## Невізуальна інтеграція

Точні shared hunks:

- `app/Support/TheoryContentSource.php`: один M48 presenter у чинному registry/context.
- `app/Support/TheoryHtmlAdapter.php`: дві finite dispatch-гілки pointPanels/validDesign для m48-native-v1.
- `app/Support/TheoryLegacyAdapter.php`: M48-specific bilingual correction на власному confirmed data plan.
- `app/Services/TheoryCourseManifestService.php`: clone presentation повертає незалежний BEFORE лише для трьох точно розпізнаних M48 UK AFTER owners; оригінальний model/DB та інші callers не змінює.

Нові `app/Support/M48ConditionalsPackage.php`, `app/Services/M48ContentPatch.php`, `app/Services/M48LocalTargetGuard.php`, `app/Console/Commands/PatchM48Content.php` і `tools/diagnostics/m48-working-local.php` — finite package/planner/explicit CLI entry поверх M26 writer і M11 guard. Немає автоматичних HTTP/startup/scheduler apply hooks.

Data adapter перевіряє exact before/after owner, hash/source projection, усі detail sections, stable UUID і caller boundary. Author learning data не можуть задавати view/style/class/script. Це підключення даних до наявних компонентів, не новий rendering engine. Blade, універсальна в’юха, CSS/fonts/frontend JS/practice factory, assets, header/sidebar/background, framing/spacing/colors не редагувалися; Git visual diff = 0 та served visual hashes = 0 changes.

## Перевірки

### Ізольовані PHP

Working .env не завантажувався; SQLite :memory:, array cache/session, CLI opcache off.

| Run | Tests | Assertions | Результат |
|---|---:|---:|---|
| M46/M47 patch + local guards regression | 124 | 551 | PASS |
| M48 patch + local guard | 71 | 391 | PASS |
| M48/M46/M47 packages + TheoryContentGroups + rollout 42 + universal view | 37 | 26257 | PASS |
| Унікальні PHP tests | 232 | 27199 | 0 failures/errors/skips |

M48 package охоплює всі detail sections і native fallback; guard tests перевіряють substantive source mutation, stale/manual states, rollback, protected metadata/ENPL/IDs/dates, collisions, schema і no-op. Кожен PHP run повідомив одну deprecation, не failure; причина не приписується без окремого trace. Перший sandbox preflight однієї тестової обгортки був відхилений до PHP через private output directory access; правильний isolated run успішний. Повторні спроби не підсумовані як нові тести.

### Offline actual practice factory

- Новий M48 Node suite: 20/20 PASS, 18 controls, 45 options, 105 group combinations; all candidate feedback, normalization/aliases, retry/reset, was/were one-point і stale feedback.
- Окремий M47 Node suite: 19/19 PASS; це груповий результат, не доданий повторно до загального baseline.
- Старий ROOT aggregate: 208 PASS / 6 FAIL із 214. Усі шість — legacy contractions harness `this.item is not a function`, а не M48 data/runtime failure.
- Уже прийнята WT contractions обгортка містить потрібний stub і окремо проходить 6/6. Старий ROOT harness не редагували/не синхронізували для штучного PASS. Фактичні inline factory, AnswerVariants і contractions data LF-equivalent ROOT/WT; увесь Blade markup не оголошується тотожним, бо у ROOT є збережені сторонні правки.

### Live browser

CUA/browser API з Computer Use skill і local URL guard; без native UI bypass, CSS zoom, DevTools hidden-state writes або стороннього browser launch.

Основна матриця завершена: 3 × (1440×1000, 390×844) × (light, dark) = 12 states. Додатково 3 × 320×844 × light/dark = 6 states. У всіх 18 actual dimensions збігаються, fonts loaded, learning controls/panels не мають viewport overflow. Переглянуто всі section blocks до navigation/tests/footer, збережено full-page screenshots і DOM/style evidence.

Чотири еталони перевірені окремо при 1440×1000/light/expanded sidebar, тих самих loaded Manrope/Archivo і catalog-public-Cy8aaDRf.css: PPC Forms, Linking Words Reason/Result/Contrast, Advanced Linking Devices, Concessive and Contrastive Structures. Нові M48 panels мають той самий нейтральний background і border 0; кольорові badges/headings та оформлення прикладів штатні. Кількість reference visits не включена в 12/6 M48 states.

На 390/320 px document scrollWidth має невеликий декоративний вихід; read-only offender check відокремив порожні background spans від навчальних елементів. Comparison table скролиться у власному labelled region. Global overflow:hidden не додано; decorative overflow не названо виправленим. Full live print/JS-off не підмінено цим responsive test.

Усі 18 controls пройшли empty → wrong → другий wrong (повтор того самого там, де є лише один неправильний варіант) → edit correct → check → repeat check → reset. Перевірені всі 45 видимих candidate options. Кожна група доходить до 3/3, repeated check не додає балів, reset прибирає score/feedback; відредагована відповідь відразу має актуальний, не старий feedback. У Wish were і was дали 3/3 тієї самої групи. Невидимі typed aliases перевірені offline, не оголошені введеними через UI, де textbox відсутній.

Details: initial closed, one-point open, повний текст/усі фрагменти, Enter і Space, deep-link та reload — PASS для всіх трьох. Одна кнопка не розкриває sibling points. Один UI test locator після reset першої групи втратив другий nth через прихований reset button; fresh DOM підтвердив стан і remaining visible reset завершив цикл. Це тестовий locator recovery, не defect сторінки.

## JS-off / print: можливість окремо від тесту

| Властивість | Факт |
|---|---|
| Server teaching text | Повні basic та detail texts, переклади і всі п’ять fragments присутні у фактичному HTML. |
| Detail interaction | Чинний native details/summary; live JS-on keyboard/deep-link/reload PASS. Live JS-off не виконано. |
| No-JS practice answer key | Окремого підтримуваного native M48 ключа самоперевірки немає. Serialized JS data не є usable no-JS ключем. |
| Practice grading | Чинний Alpine/AnswerVariants потребує JS. Автооцінювання без JS не заявляється. |
| Print | Live print run не підтверджений; наявність старого print CSS не оголошена PASS. |
| Backend capabilities | Підключений browser має viewport/visibility та pageAssets/webmcp, не JS-disable/print emulation. Native automation у цьому сеансі disabled. Іншим інструментом safety boundary не обходили. |

Новий no-JS/print механізм, hidden key у звичайному JS сценарії або зміна template/engine не додавалися. Ці обмеження збережено чесно, а не названо «все підтримується, просто не перевіряли».

## Delivery

У commit входять лише M48 sources/definitions, four scoped shared hooks, finite package/guard/command/workflow, чотири тести, checksum/EOL rules і цей звіт. Приватні дані/backup/HTML/screenshots/proofs/caches та сторонні unfinished files виключені.

Delivery виконується scoped commit → normal push `codex/seo-m48-conditionals-standard-view` → remote SHA == HEAD. Повний SHA наводиться у фінальній відповіді після push, щоб не створювати самопосилальний hash цього звіту. Main/PR/merge/deploy не входять у M48.
