# M70 — Intensifiers, Plural Nouns, Countable / Uncountable Nouns

Дата: 2026-10-20. Лише `http://gramlyze.loc`. Production, `main`, PR і деплой не змінювалися.

## Результат і межі

Три уроки доповнено навчальними даними в чинній канонічній в’юсі. Нової верстки, CSS, Blade, JS, шрифтів, кольорів, рамок чи grader-логіки M70 не додає.

Робоча гілка: `codex/seo-m70-intensifiers-noun-basics-standard-view`.
Фактичний accepted base: `be2b1c2549e70d91cc6f3c47cd7708a68ff7eb71`; збережено M69 1.0.1 та пізніші accepted PPC-виправлення. Фінальний commit SHA і підтверджений remote SHA наведено у повідомленні доставки.

| Сторінка | Page / Category | Owner seeder | Technical / display level |
|---|---|---|---|
| [Intensifiers](http://gramlyze.loc/theory/prykmetniky-ta-pryslinknyky/intensifiers) | 277 / 52 | `AdjectivesIntensifiersTheorySeeder` | B1 / B1 |
| [Plural Nouns](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies) | 89 / 16 | `NounsArticlesQuantityPluralNounsTheorySeeder` | NULL / A1–B1 |
| [Countable / Uncountable Nouns](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/countable-vs-uncountable-nouns) | 85 / 16 | `NounsArticlesQuantityCountableUncountableNounsTheorySeeder` | NULL / A1–A2 |

Повні class identities, ancestry, native-компоненти й UUID зафіксовано в master, patch і component map. H1 Plural залишається **Plural nouns**, raw Page.title — **Plural Nouns — Множина іменників**; global title extractor не змінено.

## Редакція й джерела

- Intensifiers: сполучуваність very/really/so та extreme adjectives; контекст quite/rather/pretty; надмірність too проти достатності enough; позиція enough; ствердження, заперечення, питання й короткі відповіді без вигаданих вікових/правових норм.
- Plural Nouns: -s/-es/-ies/-ves, винятки й словникові варіанти; число та узгодження; sheep/fish і plural-only вживання; pair/pairs; окремі значення customs, savings, congratulations, headphones, mouse тощо без хибних універсальних заборон.
- Countability: конкретне значення, а не фізична можливість підрахунку; idea проти rice; визначники, quantifiers, речовини/порції, chicken/glass/paper; узгодження з cup/cups і bottle/bottles.
- Короткі необхідні пояснення залишено у visible basic. Point-level details: **0**, за явним finite editorial рішенням; штучних «Докладніше» немає. Їхні keyboard/deep-link тести — N/A, не вигаданий PASS.
- Оригінальні приклади й переклади перевірено агентом за редакційними джерелами. Це не зовнішня людська рецензія. Direct/indexed/403/timeout статуси джерел розрізнено в editorial map; exercise banks не копіювалися з довідників.

Артефакти версії **1.0.0**:

- `docs/content/m70-intensifiers-noun-basics.v1.0.0.json` — frozen master.
- `docs/content/m70-intensifiers-noun-basics.v1.0.0.uk.md` — читабельна українська редакція.
- `docs/content/m70-intensifiers-noun-basics.editorial.v1.md` — editorial map і джерела.
- `docs/content/m70-intensifiers-noun-basics.components.v1.json` — 33 native blocks та task map.
- `docs/content/m70-before-source.v1.json` — незалежна source BEFORE-проєкція.
- `database/content-patches/m70-intensifiers-noun-basics.v1.json` — scoped patch.
- `docs/content/theory-sentence-builders/m70-intensifiers-noun-basics.v1.json` — окремі bank contracts.
- `docs/content/m70-intensifiers-noun-basics-bank-audit.v1.json` — exact-bound selection/condition contract, без сирих приватних DB snapshots.
- `docs/content/m70-source-checksums.v1.json` — raw UTF-8 SHA-256 та розміри всіх frozen artifacts і active definitions.

Master SHA-256: `35f566620b34183e982d8d8978730ccc325ad86a6c0e8fb599c76111338a405b`.
Patch SHA-256: `6609e7482723ec79d088de3d77b23fd185078d1bbd550dda03c30241d82a9708`.
Checksum manifest SHA-256: `491fb207b64c2566fd0e802e4d09973e22f2938b08a76aa31edc7e64a7ad109c`.

Freeze виконано після UTF-8/LF/EOF/whitespace, мовної, semantic/key/alias/plain-feedback перевірки. Найбільший body — 12 658 bytes проти фактичної TEXT-місткості 65 535; DDL не змінено. Після freeze навчальні bytes не переписувалися.

## Практика

| Урок | Corrected-retained graded tasks | Нові graded tasks | Усього own tasks | Select / meaning / input / rephrase | Окремий builder |
|---|---:|---:|---:|---|---|
| Intensifiers | 0 | 6 | 6 | 2 / 2 / 2 / 0 | 5/5, B1 |
| Plural Nouns | 11 | 4 | 15 | 4 / 2 / 6 / 3 | 5/5, selected A2 |
| Countability | 9 | 4 | 13 | 4 / 2 / 5 / 2 | 5/5, selected A2 |

Разом: **34 graded own tasks = 20 corrected-retained + 14 new**, плюс два ungraded rephrase demos. Два повні manual/token sentence inputs на кожному уроці — окремо від семи retained short-word inputs.

Старі rephrases фактично підтримуються чинною в’юсою: залишено всі п’ять, transferred = 0. У noun-практиці збережено порядок і початкову відсутність task IDs/source_index. Виправлено відхилення правильних countries та U/Uncountable через старі option/key невідповідності.

Наявні practice blocks збережено на місці:

- Plural: UUID `e480381a-dd8f-5388-af75-ff58d96827af`, source index 12, sort 13, technical NULL.
- Countability: UUID `132a698f-082e-5722-8315-bbea0dcbb824`, source index 10, sort 11, technical NULL.
- Лише Intensifiers отримав новий practice block: UUID `98ca65bc-c908-525d-9267-69a354170093`, sort 7, B1.

Конструктори використовують наявні тематичні Polyglot banks, type 4. Пули: 12 B1 для Intensifiers; фактичні NULL-filter noun pools по 72 питання. Intended A2 noun selection забезпечено finite clone-only exclusions, без зміни рівнів/розширення фільтрів або SQL-записів. Вибрано 15 existing questions; нових questions/options/answers/links/hints/progress rows — **0**. Публічні умови уточнюються лише на exact-bound theory clones; оригінальні моделі та standalone consumers не мутуються.

## Локальне застосування та збереження

Порядок: незалежні source/SQL/HTTP BEFORE → fresh physical/web/CLI nonce proof → fresh preview → exact field diff review → exclusive private backup → одна транзакція → незалежні postconditions → два повторні no-op → live browser acceptance → фінальний READ ONLY аудит.

Exact preview digest: `93708fc3852c2bf598af708609eb053861ab44b2d52a22cf7553e2fea33d9437`.

- **38 distinct existing rows updated**: 3 Page.text + 3 subtitle bodies + 2 existing practice bodies + 30 інших continuing UK native bodies.
- **1 text block inserted**, **0 deleted**. Metadata transitions — 0.
- Обидва повторні apply: updated 0 / inserted 0 / deleted 0; зайві backup files не створено.
- Збережено 32 existing source slots, усі old UUID, sort/level/null/heading/column/tags і timestamps.
- Незалежний фінальний аудит відновлює exact BEFORE fingerprints усіх **46 таблиць**, нормалізуючи лише перевірені цільові текстові поля та один новий practice row.
- 70 EN/PL blocks, question-family rows, прогрес, курси й category learning descriptions — без змін.
- **326 visual files і 2531 historical frozen files** у served ROOT — без raw-byte drift.
- Temporary proof route видалено; `routes/api.php` побайтово повернуто до початкового SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; окремий loopback GET дав **404**. Apache/XAMPP/hosts не змінювалися.

Приватні dumps/backups, SQL snapshots, physical identity, screenshots і proof receipts не входять до commit.

Фінальний SQL receipt SHA-256: `2c4eba6aa997bcde7fa2d73e351f8e4f630b269f353248931839d16e5a4685fe`.

## Фактичне приймання

- Три target HTTP 200; **766 SSR learning fields** звірено з frozen author projection. Повний basic є в server HTML, окремої SEO-версії немає.
- H1/title, breadcrumb, canonical, schema identity, локальний noindex, existing anchors збережено. SEO та LearningResource descriptions обчислено чинними сервісами **до apply** і звірено після нього.
- Усі змінені internal destinations перевірено actual local GET. 20 neighbor/category/target GET — 200; resolver-derived identities і labels збережено.
- Для 10 non-target сторінок/категорій learning DOM побайтово точний. На семи старих noun-сторінках існуючий random widget вибирає інші питання: окремо перевірено незмінність решти DOM після вилучення лише одного валідованого runtime question array; для No/None також його selection-dependent native hint markup. **Це не byte-identical full HTML claim.** Усі їхні versioned sources і SQL rows залишилися точними.
- 24 фактичні old-consumer HTTP SSR/JSON/compose перевірки: 3 курси, 6 saved tests, 432 original questions та page-aggregated consumers — BEFORE payloads/learning DOM збережено.
- Registry gate: **133 targets, failed 0, writes 0**; усі 130 попередніх owner-keyed structural records збережено, додано рівно три M70 owners. Transport hashes зберігаються окремо, без false full-HTML equality.
- Браузер: **18 states** — три уроки × 1440×1000 / 390×844 / 320×844 × light/dark; actual DOM viewport, fonts loaded, assets, контент до footer, один linked widget на кожній сторінці.
- Learning-area horizontal overflow — 0. У зовнішній mobile shell зафіксовано до 13 px переповнення; його не масковано CSS і не названо нульовим.
- **24 unique full-sentence task/mode/size cases**: manual/tokens на desktop/mobile; shuffle, exact token instances, partial/wrong/repeated wrong → correct, repeated Check, reset, delete/return/rebuild.
- Окремо всі **34 own tasks**, **38 options**, **37 accepted values**, retained word/rephrase tasks, plain bilingual feedback після Check та незалежні scores/reset груп — пройдено.
- Усі три linked widgets реально пройдено до **5/5**; visible conditions, Check/Next, case/multiplicity і client shuffle звірено. Натискалися лише BANK buttons; personal progress/global localStorage не очищалися.
- Zoom не змінювався; тестовий viewport override скинуто. Computer-use skill застосовано для реального UI-приймання, а не як заміну локальному apply.
- Print, no-JS та повний WCAG-аудит: **not_checked**.

Ключові приватні receipt hashes: SSR `07ca20e33abe1101d3dc56c7a2b9ff405a867808f166cf903616a26a3f5a9065`; old consumers `f34ebbf006bbf8b922afa3d7532ab4aebae12f707dffa989b2d809027f2522bf`; registry `a300ee3578634e8d9167c2129250308ab380158c79e841aca8d31618b776b58b`; navigation review `1f245f144ace9e0462c86f648c3ea64cdcedd525c85d10ea507ccfd94883c900`.

## Ізольовані тести та чесні обмеження

Тести виконувалися без робочої MySQL/.env: testing, SQLite :memory:, array cache/session, CLI OPcache off. Node factory checks — без SQL/network.

- Один широкий PHP run: **416 tests / 78 053 assertions / 5 failures**, errors 0, одна deprecation. Не названо green.
  - Три незмінні M68 historical raw shared-hash assertions очікують старі shared bytes.
  - Два M69 literal-source gates очікують попередній склад dispatcher/generic selection source; M70 додає finite continuing-practice index і власні opt-in dispatch hunks.
  - Старі assertions не послаблено й файли тестів не переписано.
- Новий M70 lineage gate перевіряє exact accepted-base код усіх 11 shared files після зворотного застосування лише явно переглянутих M70 hunks, з LF-normalized comparison. Це окрема current-source перевірка, не підміна historical raw assertions.
- Фінальний current-runtime PHP run: **79 tests / 26 147 assertions**, failures/errors/skips 0, одна deprecation. Включає M70, generic native guards, sentence-builder discovery, TheoryContentGroups, M69 1.0.1 subtitle/correction/clone/NULL compatibility та M68 current-runtime.
- Окремий незмінний historical literal-UI suite: **6 tests / 23 assertions / 4 known failures**. Не виправлявся редизайном і не змішаний з raw-source failures.
- Фінальний current Node run: **139 tests, 139 pass, 0 fail/skip**; M70 frozen native factory, answer variants, punctuation, contractions, PPC retries і PracticeCompletion states.
- Окремий чинний `SiteModeTest`: **10 tests / 39 assertions**, failures/errors/skips 0, одна deprecation. Production-host сценарії виконані лише в ізольованому Laravel test kernel, без мережевих звернень до .com/.ub.
- Окремий M68 shipped-state run на чинному explicit served-local ROOT profile: **24 pass, 0 fail**. Початковий запуск цього старого raw-profile тесту проти WT мав один asset-hash mismatch через різні existing raw EOL profiles; це залишено видимим, assertions не змінювалися.
- Початкові нові diagnostic/test-schema припущення (native table schema, component-map fields, corrected condition wording), locator/DOM settling deadlines і одна довга browser call були виправлені в тестуванні, не зміною accepted content чи engine. Остаточні UI tasks записані окремими checkpoint receipts. Неповні ранні receipts не використані як фінальний PASS.
- Повторні runs не додаються до кількості унікальних tests і не видаються за один full rerun.

## Доставка

До чинних Git-правил лише додано finite M70 `-text` paths: frozen hashes зберігаються й після Windows checkout; попередні правила не змінено. Нові payloads і active definitions додатково звірено з raw Git index bytes.

Scoped commit/push — тільки в робочу M70-гілку. Staged allowlist виключає історичні файли, secrets/.env, приватні diagnostics, runtime caches, dumps, screenshots і тимчасові routes. Для raw Windows CRLF hunks використано CR-at-EOL-aware `git diff --check`, без косметичного переписування foreign bytes. Normal push перевіряється незалежним `ls-remote`; `main` і production залишаються поза scope.
