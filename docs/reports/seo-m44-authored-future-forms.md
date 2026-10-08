# M44 — авторські Future Forms у native-дизайні PPC / M43.1

Дата виконання: 8–9 жовтня 2026. Єдина HTTP/DB/browser-ціль — `http://gramlyze.loc`. Тексти створено й перевірено в межах дозволеного авторського завдання; це не особисте погодження користувачем кожного речення й не зовнішня мовна сертифікація.

## База, scope і реальна ціль

- Робоча гілка: `codex/seo-m44-authored-future-forms`.
- Actual base: `d3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0`; пізні accepted PPC виправлення збережено.
- Required M43.1 ancestor: `be3c724053aa57024b2b404251104aa465d61424`; `git merge-base --is-ancestor` завершився exit 0.
- WT: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Served ROOT: `D:/DEV/htdocs/gramlyze.loc`, public document root цього каталогу. ROOT не дорівнює WT. ROOT лишився на `codex/production-ready-a14788dac`, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd`; його сторонні dirty/deleted файли не reset/clean і не перезаписано.
- `main` лишився `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`. Без PR, merge, rebase, force push, workflow dispatch, deploy або звернень до production `.com/.ub`.
- Фізичний proof: Windows Apache, loopback `.loc`, саме ROOT/public; web і CLI використовують MySQL `localhost:3306`, БД `gr2`, сервер `DESKTOP-3C05HGF`. APP environment — `production`, SiteMode — `development`: назви профілів самі по собі не були доказом physical target.

Identities та URL підтверджені локальною БД і штатним Laravel resolver, не назвою директорії FutureForms:

| Урок / Page ID | Local URL | Exact identity | Ancestry / display / technical level |
|---|---|---|---|
| A / 63 | [Will vs Be Going To — Вибір форми](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) | `Database\Seeders\Page_V3\FutureForms\FutureFormsWillVsBeGoingToTheorySeeder` | `maibutni-formy → future-simple`; A2–B1 / A2 |
| B / 62 | [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future) | `Database\Seeders\Page_V3\FutureForms\FutureFormsPresentContinuousForFutureTheorySeeder` | `maibutni-formy`; A2 / A2 |
| C / 58 | [Choosing the Right Future Form](http://gramlyze.loc/theory/maibutni-formy/choosing-the-right-future-form) | `Database\Seeders\Page_V3\FutureForms\FutureFormsChoosingTheRightFutureFormTheorySeeder` | `maibutni-formy`; B1–B2 / B1–B2 |

Курсові consumers — `/courses/english-grammar-theory/lesson/` + наведені resolver paths. A також згадується в `polyglot-english-a1.json`, `polyglot-english-a2.json`, `theory-driven.json`. Перевірено TheoryCourseManifest/TestPool, theory/category, question-theory та test-related consumers. Курси, EN/PL, банки й прогрес не є ціллю модернізації.

## BEFORE → редакторські зміни

До source sync та content apply збережено незалежні raw definitions, повний SELECT inventory 46 таблиць, source hashes, HTTP/навчальний DOM/metadata, target/reference screenshots, computed styles та доступні курси. Пізніший AFTER не використано як історичний BEFORE.

| Урок | Збережено за змістом | Виправлено / доповнено |
|---|---|---|
| A | Will, попередній намір, пропозиції/обіцянки/прохання, прогнози, побудова форм, алгоритм вибору | Коментарі відокремлено від точних перекладів; додано заперечення/питання/короткі відповіді, контекстні альтернативи, межі узагальнень, віддалений намір і `be going to + verb` проти `go to + place`. Немає заборон will після попереднього рішення/доказу/I think. |
| B | Домовленості й організовані плани, am/is/are + -ing, питання про плани, зіставлення з going to | Tomorrow/квиток/інша людина не є обов’язковим граматичним тестом. `We are having dinner` та `I am answering it` перенесено до нейтрального контекстного порівняння, не названо неграматичними. Додано короткі відповіді, зміну плану, розклад Present Simple. |
| C | Усі п’ять наявних моделей, процес/результат/тривалість, контрасти й алгоритм | Замість автоматичних by/at/for-перемикачів — навігація за значенням. Три старі граматично можливі ❌ збережено в нейтральному порівнянні. Додано стани, часову точку відліку, межі висновків про початок/завершення, компактні Present Continuous/Present Simple. |

Повний finite editorial mapping та source reading records — у `docs/content/m44-editorial-notes.v1.0.0.md` і master. Пройдено окремі grammar → context → translation → level → theory/practice → alternative-answer проходи. Приклади й 18 вправ оригінальні, не копії видавничих вправ. UK-переклад і `note_uk` — різні поля; hero/formulas/tables/mistakes/details/feedback не втрачають прикладів чи перекладів.

Джерела: редакційні [British Council Future forms](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-forms-will-be-going-present-continuous), [Talking about the future](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/talking-about-future), [Future continuous and future perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-continuous-future-perfect); Cambridge Grammar Today для will/going to/present forms/future perfect/continuous. Для більшості Cambridge URLs прочитано індексований editorial text, а пряме відкриття дало 403 — це явно записано, не названо успішним повним прямим читанням. British Council Stative verbs direct opens дали timeout, тому записано indexed reading. Учнівські коментарі й відповіді в comment threads не використано як нормативні джерела. URL variants мають окремі source IDs/reading records.

## Frozen master та native projection

Версія 1.0.0, UTF-8 без BOM, LF, рівно один кінцевий LF. Після freeze жодного авторського файла не переписано. `.gitattributes` містить лише сім точних `-text` paths M44; whitespace checks не послаблено.

| Файл | SHA-256 |
|---|---|
| `docs/content/m44-authored-future-forms.v1.0.0.json` | `541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888` |
| `docs/content/m44-authored-future-forms.v1.0.0.uk.md` | `84d7bc770a1c0253490814c71400fb04ef8b6634a0177a839f6b5569f3294a8f` |
| `docs/content/m44-editorial-notes.v1.0.0.md` | `285ad443a51d8f6029bedd5bdbd0c54d5a75c39a6d92a1eff93eb96edfa842df` |
| `docs/content/m44-native-mapping.v1.0.0.json` | `9e9e3897d1858d5d305182cacd52e9da7761ab87c91c08876fed69fb46c8dcbd` |
| `docs/content/m44-checksums.v1.0.0.json` | `b185ebbb08b613b1616fabf86728507d8a773aafdb410f83d8bc374c39df6b0a` |
| `database/content-patches/m44-authored-future-forms-before.json` | `9ef93832e6d10d743e96965d5d38c49f7f8b2d531e0d2098720ee15106f5d176` |
| `database/content-patches/m44-authored-future-forms.v1.0.0.json` | `c86656dfe22adc99868e2e4e58a8d045d92691c6a511e43c034128e5a04cfc43` |

Master → exact finite mapping → canonical definitions → served source → working DB → HTTP/DOM/reload перевіряються незалежно. M44 має власний `m44_v1`, own `m44-author-html` fragment, exact caller-owned UK opt-in після owner/UUID/order/body guards та повний escaped fallback. Frozen M41/M42/M43 registries не розширено; M43 identity/marker не підроблено. `M44NativeHtml` повторно використовує лише чисте форматування post-M43.1, не його authority.

Shared hunks: theory/show, content-block, point-detail-fragment, course partial, authored-practice-ui, usage-panels, lesson-rule-cards, summary-list. False/old caller paths збережено. Нові M44 views/helpers/JS не вводять глобальну палітру, !important reset, MutationObserver або прихований duplicate DOM. Header/sidebar/background/global CSS, залежності й build не змінено.

Course-preservation використовує exact validated AFTER collection, повертає клони старих блоків зі frozen BEFORE та виключає тільки нові M44 блоки. Controller models/relations не мутуються; це не rollback БД. English/Polish projections не opt-in.

## Реальні обсяги й практика

| Урок | Sections / points | Meaningful point details | Нові tasks | Required controls |
|---|---:|---:|---:|---:|
| A | 8 / 27 | 2 | 6 | 11 |
| B | 5 / 17 | 3 | 6 | 12 |
| C | 7 / 25 | 2 | 6 | 11 |
| Разом | 20 / 69 | 7 | 18 | 34 |

Чотири structured tables; 12 manual/tokens controls. A q4 — авторський `tokens` зі двома незалежними `to`; runtime projection явно `manual + source_kind=tokens`, без зміни author original. Канонічний порядок відновлюється з answer і multiset tokens, не з shuffled bank order. Повторне використання токенів після очищення typed answer виправлено тільки у власній M44 JS factory; shared scoring JS не змінено.

Stable details і точні owners:

- A `m44-going-evidence-detail` → `m44-going-evidence`; `m44-will-parallel-intention-detail` → `m44-will-parallel-intention`.
- B `m44-cont-arrangement-detail` → `m44-cont-arrangement`; `m44-cont-future-context-detail` → `m44-cont-future-context`; `m44-cont-going-to-detail` → `m44-cont-going-to`.
- C `m44-choice-duration-timeline-detail` → `m44-choice-duration`; `m44-choice-duration-overlap-detail` → `m44-choice-duration-state`.

Basic повноцінний без відкривання. Деталі — явні finite semantic decisions, не квота/word threshold. Короткі застереження, переклади й необхідні приклади лишаються visible basic.

| URL вище | Task IDs та назви (controls) |
|---|---|
| A | `m44-will-q1` Побудуй рішення й намір (2); q2 Пропозиція і прохання: що робить мовець? (2); q3 Заперечний намір і коротка відповідь (2); q4 Побудуй питання про намір піти (1); q5 Прогноз із двома доречними формами (1); q6 Дія після to, місце після to і віддалений намір (3) |
| B | `m44-cont-q1` Два повідомлення про записаний план (2); q2 Відповідай коротко, але з be (2); q3 Час уже є в розмові (2); q4 Одна домовленість, два способи її подати (2); q5 Розклад екскурсії та наш особистий план (2); q6 Уточни місце та повідом про зміну (2) |
| C | `m44-choice-q1` Рішення й намір: прочитай передісторію (2); q2 Процес тоді й готовий результат до межі (2); q3 Тривалість діяльності й знайомства (2); q4 Домовленість і програма події (2); q5 Що можна стверджувати про фінальний етап? (1); q6 Future Perfect: заперечення й питання (2) |

Compound task має повний бал тільки за всі required controls. Перевіряються empty/partial/wrong/correct/edit/reset, aliases, straight/curly apostrophes, contractions, not, числа й внутрішня пунктуація, tokens/return/order/keyboard/focus. Умова відділяє directly requested form від contextual meaning choice. Finite matcher не називає незбіг іншого природного перефразування доказом неграматичності. Keys/повні приклади з перекладом показуються після check; no-JS self-check — окремий механізм, не автоматичний scoring.

## Read-only bank inventory

| Урок | Actual linked/global bank | Count / type |
|---|---|---:|
| A | `V3\AI\ChatGpt\WillVsBeGoingToFutureFormsAllLevelsV3Seeder` | 71 / 0 |
| A own builder | `V3\Polyglot\PolyglotWillVsBeGoingToAllLevelsLessonSeeder` | 72 / 4 |
| A legacy | `PolyglotBeGoingToLessonSeeder`; `PolyglotFutureSimpleWillLessonSeeder` | 24 A2 / 4; 24 A1 / 4 |
| B | `V3\FutureForms\PresentContinuousForFutureAllLevelsV3Seeder`; own `PolyglotPresentContinuousForFutureAllLevelsLessonSeeder` | 72 / 0; 72 / 4 |
| C | `V3\FutureForms\ChoosingTheRightFutureFormAllLevelsV3Seeder`; own `PolyglotChoosingTheRightFutureFormAllLevelsLessonSeeder` | 72 / 0; 72 / 4 |

Повний namespace починається `Database\Seeders\`. Linked/global counts і exact sets збігаються; A standard bank реально 71, не механічно 72 з fixture. Existing widgets лишили свої точні own builder pools. Questions/answers/options/verb_hint/pivots/saved tests/attempts/progress не редаговано.

## Guarded local apply та незмінність

Приватні докази збережено у `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m44-local`; вони не входять до Git, не містять .env/password/APP_KEY/cookies/tokens. Source BEFORE `ad86a11c5c0fda76ad054f23f7566cd1e508cae0324a94ae736750d2692eadb5`; DB BEFORE v1 `98b1b445c3eeacc28e9cee2469403d0681982c1e77840bdb1fae25e572634ce7`; fresh pre-apply DB v2 `dafea179cae4805159ba427c93b7f8337c6284ff314c0cfc8187fa2ecc1b2fe7`.

46 exact source sync paths, у shared files тільки reviewed hunks; original ROOT bytes/EOL сторонніх рядків збережено. Applied source proposal v2 SHA `f8337013237161d57c14df0fc54cdef20834cb780aba13ce5fdc1be16ac88dd2`; v1 залишено unused. Оригінали й manifest — `source-sync-before-v1/`. Canonical definitions після mechanical projection мають SHA A `ef3c35da55b2985169d625088653edbfb81d46b7953c70fa40b09aa6e4eda895`, B `6d3ad4f5d8ed9bb2eb5ea1384c98d3d52698eb4f61f69000fa9f4132c96a4d9e`, C `961002a36464362455e4c6ee73b3b4d58ffe11941ce88f040993fb78a0edbaf9`.

До writes: fresh physical v2 SHA `496bd1dcf7b4e5f4c85af6108fdda5e9579c26d64ad8db9651c0ddcf5f82e144`, nonce-bound web/CLI proof, exact owners/locale/ancestry/bodies, fresh preview і exact plan review. Preview v1 SHA `df67a7b84a5d4bcb302be17cb997552aa55d41fd39e733c20b2d00810b57e078`.

- Exclusive backup: `storage/app/seo-m44-local/m44-backup-v1.json`, 4 287 428 bytes, SHA `dcc731552adbf78dbaa219981672eba0ab55b3e646a99f3ed9ba10b9c147518b`, збережено перед transaction.
- Actual apply: **28 updated / 7 inserted / 0 deleted**. A +2, B +2, C +3 нових deterministic блоків; існуючі UUID/metadata збережено.
- Write allowlist: тільки `TextBlock.type/body` exact UK owners та `Page.text`; нові блоки exact package. Без timestamp mass update, migration/reseed/cache clear/full restore.
- Fresh AFTER preview SHA `973ddc5ab3d10af9880e234b32ac22f53fd5b49e4534efbd2b5d1e8a7b1dfcbc`; repeated apply **no-op 0/0/0**. No-op backup не створювався.
- AFTER SELECT v1 SHA `ded0a0e04fdd92b5b55143cf0f99eac539f8071ee268d3ea6be50c5f23df0162`: усі 46 protected table fingerprints дорівнюють fresh BEFORE. Raw змінилися тільки `pages` і `text_blocks`. Target canonical source=DB exact; banks, categories/ancestry та всі Page fields поза text unchanged.
- Тимчасовий local-only GET proof route видалено. `routes/api.php` точно повернувся до SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; actual GET nonce URL повернув **404**, доказ `proof-route-removed-v1.json`. Route не комітиться.

Source AFTER: 52 539 union paths, ROOT 5 832 / WT 52 530 наявних файлів; SHA `7b679d9947c317f3ddc1d1866a4aadc80902865d40fdafd2e466ac4a8f5cb52e`. Allowlist отримано з reviewed sync paths до зіставлення, не зі списку AFTER differences: 31 path на ROOT і WT у source scopes. **62 changes / 0 unapproved**, comparison SHA `2172616208def28318ebfbb0500bb66eb27b3b01f626e23440c11589f480ad31`. Усі позаскопні source scopes, включно з frozen M41/M42/M43, global CSS, old views/controllers/routes, незмінні. `tools/tests/docs/reports/.gitattributes` перевіряються окремим scoped Git review, не включені до цього inventory.

Read-only DB comparison v1 підтвердив protected BEFORE→FINAL exact та raw AFTER→FINAL exact для всіх 46 таблиць. Final SELECT v1 SHA `32b4c2322aad18fb8bfa22c88b397a8a0bddef37b5e083ce889c46e13a43d16f`. Hashes трьох Apache configs і public/index.php збіглися з фізичним BEFORE (`physical-config-after-v1.json`); жодних server/hosts/dependency writes.

Після повної browser/scroll acceptance повторний final SELECT v2 SHA `ceb914f1e2704e4dd243acc04d99d5db06c99e84a4c5167f3fcfe8e372fa1c9c`: усі 46 protected BEFORE→FINAL та raw AFTER→FINAL fingerprints exact, усі target source=DB exact (`db-comparison-v2.json`). Source AFTER v2 SHA `9f54844711327597072a494709885d0a72dfe9bf98c22df957b81c90fe1cdd95`: всі source records дорівнюють AFTER v1; comparison v2 SHA `49123c7f3ff58b239b4e9de3631af41bb380b51c75a1f0d62c66fac32f795f45`, 62 allowed / 0 unapproved changes. Перевірки не змінили робочі дані чи sources.

Після первинного no-op дозволений локальний acceptance виправив **лише presentation**: generated M44 note отримав exact native muted class, table — post-M43.1 embedded wrapper зі збереженим зовнішнім gap, practice note — scoped 8px gap. Guarded old/new source transfers мають окремі exclusive backups/receipts `presentation-sync-v1/v2.json`. Author/master/mapping/package/definitions/DB та первинні preview/apply/source-sync receipts не змінено. Diagnostic-only HTTP schema/calibration/comparator changes також мають окрему provenance; старі plans не переприв’язано заднім числом і не перевикористовували для writes після source changes.

## HTTP, metadata, navigation та курси

Незалежний before — 92 GET rows плюс robots/sitemap; ті самі запити repeated after. Запити гостьові, без Cookie/Authorization/Referer. Три цілі — 200, title/H1/canonical/robots не змінено; нові description/OG/Twitter/LearningResource.description збіглися з expectations, отриманими **до apply** з master через чинний metadata service (`metadata-expectations-before-v1.json`, SHA `1a8bf804578a1bdff1c1c27f133808ddb9303a359d3e64b0fd39fdf08f8f2454`). Canonical `.com` у локальному HTML — існуючий metadata contract, не production HTTP request. Локальний `X-Robots-Tag: noindex, nofollow, noarchive`, robots.txt і точний ordered sitemap URL set збережено.

90/92 первинних HTTP comparisons exact PASS. Окремо `/theory/maibutni-formy`: рівно два похідні анонси B/C змінилися зі frozen BEFORE.subtitle_text на frozen master.subtitle; exact projection порівняла весь решта learner DOM/links/tables/anchors/metadata без відмінностей (`category-comparison-v2.json`). Це очікуваний derived target-content diff, не модернізація category definition.

`/theory/future-simple` мав timeout і у BEFORE, і у AFTER collection. Окремі read-only retries до й після змін повернули 200; початкові timeout-докази не переписано. Для цієї однієї категорії немає історичного live learner-DOM BEFORE→AFTER; source/DB invariants та current GET/isolated tests — окремі докази, не підміна.

Адресно змінено тільки 7 navigation URLs усередині цілей: A5/B1/C1 на resolver-confirmed destinations. Redirect/canonical middleware не змінено. Старі anchors збережено, додано stable M44 IDs.

Курси: fresh `course-before-v1` знято **до sync та apply**, SHA `adba95eb0c06d2365e3b3cc63962636d04f5c9e0037a2d39016d42a2ed6fae39`; `course-after-v1` — після, SHA `76c02d41fabd43c98776675e3cd8345551e511e1ebe0365dbd4a54fa0a213e4a`, 34 PNG. **6/6 PASS**: три сторінки × JS/no-JS, exact normalized learner text/sections/tables/links/anchors/metadata/HTTP/gate equality. JS fresh guest content лишається locked/hidden; gate не обходили. No-JS public fallback видимий і зіставлений окремо, не названий unlocked JS session. Google Fonts блокувалися однаково в обох course runs; це окрема policy від target/reference style matrix.

Live learner projection прибирає script/style/noscript/input/textarea, dynamic sentence-builder та UI-only nodes і нормалізує whitespace; anchors/links/tables/metadata зіставляються окремо. Це не claim raw HTTP-byte equality всього сайту. Окремий isolated course test доводить byte-equivalence саме захищеного course rendering і незмінність input models. Footer UI збережено: builder72/72 та mixed84/84; змішаний UI count не підміняє actual seeder bank inventory71/72/24 вище.

## Live design і браузерне приймання

Read-only visual reference: [PPC Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms), плюс відповідні post-M43.1 компоненти [Past Perfect comparison](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous), [Stative verbs](http://gramlyze.loc/theory/tenses/stative-verbs), [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would). Нейтральні панелі, normal readable English/UK, left-accent examples, native forms/table/disclosures/practice. Висоту різних уроків не прирівнюємо.

Fresh pre-sync BEFORE matrix `browser-before-v2`: **28/28** (12 targets + 4 PPC + 12 M43 states), SHA `7d4877b5c48da01e52d6b538f719f5ea5f2df810542a497a20dd885b16e6b683`, 256 PNG. Supplement v3: **16/16 reference states**, 38 semantic roles × 32 computed properties, open details і 80 client practice states, SHA `d1b61830ca9899c4fd2b5fd1f9671b3cf85b3864c1bf015d0eb1fb37fb16c666`.

Desktop 1440×1000/mobile 390×844; light/dark, DPR1, default100% browser zoom, sidebar uncollapsed, та сама Google Fonts policy. Перевірено фактично loaded font faces і CDP platform fonts, не лише CSS font-family. English/UK learning body реально використовує custom Manrope. H1 A має Archivo для Latin та **існуючий Arial Black fallback для 10 українських glyphs**; exact postScriptName/custom/glyph-count projection збіглася з target BEFORE. Не заявляємо, що Archivo покриває всі UK glyphs, і не міняємо font dependencies. Chrome `155.0.8059.39`. Non-GET/progress/AI/production requests блокуються в ізольованих contexts.

Фінальна основна матриця `browser-after-v5/after-v5-pages.json`: **12/12 target states PASS**, exit0, SHA `dc92534be37c3e57cb4443100c5f5ecb7c9d736a30667d3a4160c590b3c5e1d5`. Саме цей один run: **72 task-runs / 136 required-control-runs / 260 alias-variant runs / 48 keyboard token-build-clear runs / 28 independent detail-runs**. Усі 18 вправ, 34 controls, contractions/apostrophes, repeated-to, повернення/очищення tokens, empty/partial/wrong/correct/edit/reset/reanswer перевірено. TOC/keyboard/deep links/print/reload — PASS; після reload авторський текст знову exact.

**7 904 component comparisons / 252 928 computed-property comparisons, 0 differences/uncovered**, 432 PNG. Це comparisons повторних states, не вигадане число унікальних компонентів і не сума retry runs. Learning main/control/point overflow — 0. Декоративний document overflow на деяких mobile states 1–13px записано окремо, не приховано global overflow:hidden. Реальний scrolling до footer; висоти desktop 12.7–15.6k / mobile 19.2–24.2k CSS px підтверджено DOM, а не розміром PNG.

Додатковий run `browser-supplemental-v1/supplemental-v1-supplemental.json`: **13/13 PASS**, exit0, SHA `275fb89ee7ef6ea63f79e24459a635eed90753aed6d55e261e93ed2baa8252d0`: 3 no-JS, 3 width320, 3 DPR2, 4 protected PPC/M43 learner/metadata comparisons зі старим незалежним helper. У no-JS доступні всі 18 keys, 7 details та потрібні tokens. Три CSP-disabled script записи очікувані при вимкненому JS, не приховані network failures. В обох фінальних runs немає unexpected console/page/HTTP/GET errors або attempted non-GET writes.

Read-only calibration v4 (`browser-reference-calibration-v4`): 16/16, 80 settled client states, SHA `b7b5b01595f42d05a9a4657f333e06985bd04841ca7e3fe225b92889a35ebd50`. Initial/open-detail styles/metadata/HTTP усіх 16 references **exactly equal** історичному BEFORE v3 (64 row-field structures). Fresh calibration — не новий історичний BEFORE і не M44 AFTER expectations. Finite CSS transitions очікуються до завершення (≤2s), не cancel/ignore; infinite decorative animations обліковуються окремо.

Для трьох exact violet→native-slate caption IDs і одного native-rose caption ID — явні finite expectations: H3→SPAN layout/typography з історичного M43-A, один `color` із історичної PPC palette. У slate PPC SPAN є flex-blockification, якої немає в M43.1 H3 composition; rose caption не потрапляє під practice-only dark override. Кожна з 32 properties має named independent origin, жодної не пропущено. Actual matcher mutant tests відхиляють неправильні display/color/font-size/font-style/line-height. Немає broad matcher exception чи зміни palette для PASS.

Real viewport overlap capture `visual-scroll-v2/manifest.json`: **12 contexts реально дійшли footer**, 576 PNG, SHA `c218d25aff0954707dd25b91264052473b6feeadf77e2b8e7601b6aab4092858`; крок 0.6 viewport, normal header без CSS hiding, opened-detail tiles. Цей run — evidence для особистого візуального перегляду, не окремий automated design PASS. Relevant source hashes unchanged, 0 page errors/blocked requests. Readable component crops і overlap tiles використано замість припущень за стисненим giant PNG.

Візуальний AI-review завершено для всіх трьох сторінок у 12 states до footer: A/B — усі 52 teaching-section crops і 8 footers, C — 28 section crops і 4 footers. Для A/B reviewer відкрив 142 конкретні AFTER PNG (перелік у приватному `ab-human-visual-review-v1.json`, SHA `af38dccdec95bfe7943d0d247ee7436a9b2c0849ec41c5e381f16cf2ca4b973d`); C має окремий exact review inventory. Назва приватного файла не означає зовнішнього людського QA: це візуальний перегляд асистентами, не user approval/certification. Перевірено representative practice/details, duplicate-to success, mixed red/green part feedback та normal-header overlap viewports. Нового візуального release blocker не знайдено. Не стверджуємо, що особисто відкрили кожен із 576/432 PNG або кожен wrong/reset screenshot.

Успадковані native quirks не виправляли редизайном: single-item summary groups кожен мають badge1; у dark theme деякі маленькі blue captions/pale level pills мають слабший контраст; mobile tables закономірно потребують локального горизонтального scroll. Числового WCAG/contrast score немає. Sticky-header та bottom crop artifacts звірено з реальними viewport overlaps: текст і формули доступні повністю. Це обмеження доказів/старого еталона, не приховані нові M44 помилки чи привід змінювати глобальний дизайн.

Історія перевірок не прихована:

- Initial BEFORE CDP CSS agent не був enabled; v1 failed, v2 окремо виправлено без weakening assertions.
- AFTER v1: усі 12 HTTP/author fidelity passed, потім harness schema mismatch — browser BEFORE не містив X-Robots-Tag. Diagnostic-only correction бере unique hash-pinned independent HTTP BEFORE row; actual noindex header не змінювався.
- AFTER v2 виявив справжній presentation defect generated detail/feedback note class і practice-note gap, structural table wrapper mismatch і semantic caption comparator mismatch. Frozen prose/package/DB не змінено для PASS.
- Reference state CSS read на 60ms потрапляв у color transitions; transient samples не є settled palette. Historical evidence залишено; вимірювання settled state має окрему calibration provenance.
- AFTER v3 зупинився до запуску браузера: initial source manifest правильно відхилив approved presentation follow-ups. Diagnostic harness додано exact hash-pinned chain двох receipts тільки для трьох конкретних own presentation files; foreign/tampered/broken chains відхиляються. Initial apply/source-sync evidence не переписано.
- AFTER v4 дійшов до mixed wrong task: правильні частини законно зелені, але comparator використав all-wrong part reference. V5 використовує **заздалегідь заданий scenario map за stable control IDs**, не actual AFTER partCorrect. Whole-task score/rejection assertions збережено. V4 manifest SHA `fa578571c48c3434224c1599f009056742ef2adbcc6f7fc5921b1732e6a78954` та його PNG лишилися.

No-JS — умови/теорія/tokens/keys доступні; scoring без JS не заявляється. Viewport320, DPR2 і browser Ctrl+zoom — різні поняття; непідтверджений справжній zoom не оголошується PASS, CSS zoom окремо не запускався. Learning overflow відділено від декоративного; global overflow:hidden не додавався. Sticky header overlap у locator crop — screenshot artifact, перевіряється реальним scrolling, не втратою тексту.

## Ізольовані тести та обмеження

CWD — WT; PHP `C:/Program Files/xampp/php/php.exe` 8.5.10, Node 22.15.0, системний Chrome. PHP runner `tools/diagnostics/run-isolated-tests.py` — SQLite-memory, own runtime/storage, array session/cache; working MySQL і user progress не використовуються. Runs нижче перетинаються, їх не складаємо у вигаданий total.

| Run | Результат |
|---|---|
| M44 author/course/content v2 | 78 tests / 3 313 assertions, exit0; protected46 661/changes0 |
| M44 guard/source + inherited local guard | 126 tests / 141 assertions, exit0 |
| M44 pure helper + M43.1 unit v1 | 10 tests / 316 assertions, exit0 |
| M44 helper + author/course після note/table fix | 21 tests / 3 384 assertions, exit0; protected46 661/changes0 |
| Final helper/source-note + unchanged M43.1 unit | 12 tests / 328 assertions, exit0; protected46 661/changes0 |
| Existing M41/M42/M43 + new M44 Node v3 | 721/721, exit0; старі assertions не видалено |
| Browser-harness dry contracts v1 | 20/20, exit0; це не live acceptance |
| Final actual-matcher/receipt/browser contracts | 41/41, exit0; локальні private-evidence залежності, не fresh-CI portable suite |
| Vitest | 61/61, 8/8 files, exit0, no-cache/single worker; synthetic jsdom storage, не живий прогрес |
| Final M44 fidelity/course/native HTML | 17 tests / 3 102 assertions, exit0; protected46 661/changes0; 1 known deprecation |
| Broader 16-suite PHP regression | **417 tests / 31 176 assertions, 10 failures, exit1**; 407 cases passed, errors0/skips0, 1 known deprecation; protected46 661/changes0 |

У broad run **14 інших suites / 389 cases** пройшли без failures: M43 fidelity/course, M43.1 helper, M42 package/evidence, M41 package/fidelity, M26 details/practice, native/sidebar/active-state, FutureForms own banks і Polyglot course blueprint. Не оголошуємо весь 417-case run PASS. Evidence `storage/app/seo-m2-local/m44-shared-regressions-v1-2b3f5362d9374506893c00f82852bbb0-result.json`, SHA `cb964212a4d4566e3c215c2b624dd46f351d95e9f16f5d00a6996dfcc4f5d2a8`; final M44 run SHA `0377d8ee96435ffd5a32c2af7dcca2e15a583565ee5335d34ce7d09ea447c202`.

Невиправлені failures поза content/design scope:

1. `FutureSimpleTheoryPagesTest::test_future_simple_category_and_pages_have_unique_seo_metadata`, line139: title64 порушує старий `≤60` assertion. URL `/theory/maibutni-formy/future-simple/future-simple-questions`, title «Future Simple: питання та короткі відповіді — правила | Gramlyze» був **таким самим до M44** (independent BEFORE200) й після. M44 A має54 characters. Це доказ існуючої невідповідності test constraint, не заява про production SEO наслідки.
2. Дев’ять `PolyglotTheoryPageTest` failures: очікували200 за old category paths, отримали404. Exact paths і line/method pairs наведено нижче. Strict full-ancestry resolver, routes/models та relevant old page/category definitions (20 independent before/after hashes) незмінні; A category також immutable. Це **source-proven inference про старі path expectations**, не вигаданий pre-task live404 run і не baseline-checkout rerun. Старі tests/routes/metadata не змінювали для PASS.

| `PolyglotTheoryPageTest.php` line | Старий test URL → canonical ancestry path |
|---:|---|
| 258 | `/theory/verb-to-be/verb-to-be-present` → `/theory/basic-grammar/verb-to-be/verb-to-be-present` |
| 411 | `/theory/present-perfect/present-perfect-forms` → `/theory/tenses/present-perfect/present-perfect-forms` |
| 540 | `/theory/maibutni-formy/will-vs-be-going-to` → `/theory/maibutni-formy/future-simple/will-vs-be-going-to` |
| 669 | `/theory/past-continuous/past-continuous-forms` → `/theory/tenses/past-continuous/past-continuous-forms` |
| 712 | `/theory/present-perfect/present-perfect-time-expressions` → `/theory/tenses/present-perfect/present-perfect-time-expressions` |
| 1013 | `/theory/basic-grammar/a2-mixed-revision` → `/theory/mixed-revision/a2-mixed-revision` |
| 1056 | `/theory/present-perfect-continuous/present-perfect-continuous-forms` → `/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms` |
| 1145 | `/theory/past-perfect/past-perfect-forms` → `/theory/tenses/past-perfect/past-perfect-forms` |
| 1267 | `/theory/future-continuous/future-continuous-forms` → `/theory/maibutni-formy/future-continuous/future-continuous-forms` (first loop item) |

Повні method names, source-hash pairs, commands і JUnit breakdown — у приватному `c-regression-summary-v1.json`. Обидва final PHP runs мають protected dictionaries **46 661→46 661, exact digest equality / 0 changes**. Додаткова raw directory enumeration не була зміною захищеного файла. Final 41 browser-contract cases перевіряють саме actual matcher та receipt tampering, але потребують локальних private captures; переносимими fixture tests/CI proof їх не називаємо.

Попередній author run v1 мав 2 failures: canonical definitions ще BEFORE у момент запуску та втрачена non-forms formula. Обидва виправлено інтеграцією; не checksum-підміною master. Node v1/v2 мав 1 compatibility failure через old literal no-JS gate; старий two-member M41/M43 контракт повернуто буквально, M44 має окремий own partial. Final v3 пройшов без зміни old test. PHP8.5 deprecation `PDO::MYSQL_ATTR_SSL_CA` у unchanged config/database.php:62 підтверджена окремим origin test; конфігурацію не змінювали й warning не приховано як новий M44 defect.

Ключові команди (аргументи plan/backup/proof — приватні filenames, не секрети):

```text
php tools/diagnostics/project-m44-sources.php --apply-canonical --sha256 c86656dfe22adc99868e2e4e58a8d045d92691c6a511e43c034128e5a04cfc43
php tools/diagnostics/run-m44-working-local.php content:patch-authored-future-forms-m44 --plan=m44-preview-v1.json --local-target=gramlyze.loc --local-proof=<fresh-private-proof.json>
php tools/diagnostics/review-m44-plan.php m44-preview-v1.json source-sync-before-v1 m44-before-v2.json
php tools/diagnostics/run-m44-working-local.php content:patch-authored-future-forms-m44 --plan=m44-preview-v1.json --apply --backup=m44-backup-v1.json --database=gr2 --local-target=gramlyze.loc --local-proof=<fresh-private-proof.json>
php tools/diagnostics/run-m44-working-local.php content:patch-authored-future-forms-m44 --plan=m44-preview-after-v1.json --apply --backup=m44-noop-unused-v1.json --database=gr2 --local-target=gramlyze.loc --local-proof=<fresh-private-proof.json>
node tools/diagnostics/seo-m44-local.cjs --pages <private-exclusive-directory> <label>
node tools/diagnostics/seo-m44-local.cjs --supplemental <private-exclusive-directory> <label>
node tools/diagnostics/capture-m44-reference-supplement.cjs --capture <private-exclusive-directory>
python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-author-guards-v2 tests/Feature/M44AuthorFidelityTest.php tests/Feature/M44CoursePreservationTest.php tests/Feature/M44ContentPatchTest.php
node --test tests/Browser/m44-browser-acceptance-contracts.test.cjs tests/Browser/m44-practice-ui.test.cjs
python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-final-presentation-fidelity-v1 tests/Feature/M44AuthorFidelityTest.php tests/Feature/M44CoursePreservationTest.php tests/Unit/M44NativeHtmlTest.php
python tools/diagnostics/run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m44-shared-regressions-v1 tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php tests/Unit/M431NativePresentationTest.php tests/Feature/M42NativeDesignPackageTest.php tests/Feature/M42DesignEvidenceTest.php tests/Feature/M41ExistingDesignPackageTest.php tests/Feature/M41AuthorFidelityTest.php tests/Feature/M26PointDetailsTest.php tests/Feature/M26InteractivePracticePackageTest.php tests/Feature/UnifiedTheoryNativePresentationTest.php tests/Feature/TheorySidebarPresentationTest.php tests/Feature/Theory/TheorySidebarActiveStateTest.php tests/Feature/FutureFormsTheoryPageTestsSeedersTest.php tests/Feature/FutureSimpleTheoryPagesTest.php tests/Feature/PolyglotCourseBlueprintTest.php tests/Feature/PolyglotTheoryPageTest.php
node node_modules/vitest/vitest.mjs run tests/js/publicAssets.test.js tests/js/unifiedTheoryDesign.test.js tests/js/theorySidebarLayout.test.js tests/js/theorySections.test.js tests/js/theoryNavigation.test.js tests/js/polyglot/lessonProgressState.test.js tests/js/polyglot/courseProgressState.test.js tests/js/polyglot/browserStore.test.js --maxWorkers=1 --minWorkers=1 --no-file-parallelism --no-cache --no-color
```

## Commit і normal push

До commit явно staged **56 task-related paths**: exact46 source-sync paths, 8 нових тестів, `.gitattributes` та цей звіт. Shared diff переглянуто; `git diff --cached --check` пройшов. Усі сім frozen SHA перевірено **також у staged blobs**, не лише в working files, щоб EOL normalization не підмінила snapshots. Старі frozen files/global CSS/main не входять до змін. Приватні runtime/proofs/backups/screenshots/SQLite exports не staged.

Normal-push процедура й перевірка в final handoff:

```text
git commit -m "feat(content): author native Future Forms lessons for M44"
git push -u origin codex/seo-m44-authored-future-forms
git rev-parse HEAD
git ls-remote origin refs/heads/codex/seo-m44-authored-future-forms
```

Implementation full SHA отримується через `git log -1 --format=%H -- docs/reports/seo-m44-authored-future-forms.md` і повідомляється у final handoff після normal push та exact remote SHA==HEAD. Приватні proofs/backups/screenshots/dumps/caches/storage, .env, vendor/build та сторонні dirty changes не комітяться. Старі звіти не переписано.
