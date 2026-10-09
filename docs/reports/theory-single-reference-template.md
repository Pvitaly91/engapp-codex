# M45.1 — канонічний шаблон теорії

Статус: presentation-only реалізацію та основне локальне приймання завершено. Парне content/HTTP покриття — 664/664, браузерна матриця — 24/24, БД — 0/0/0. Нижче окремо зазначені обмеження browser zoom, повної legacy asset build і старих тестових fixtures; вони не названі PASS.

## Межі та база

- Робоча гілка: `codex/theory-single-reference-template`.
- Actual base / перевірена база M45: `e58986a9dd5c5d0289389d41550d90ef28e8fba4`.
- Робочий checkout: окремий наявний worktree; served ROOT — `D:/DEV/htdocs/gramlyze.loc`.
- Served ROOT мав іншу історичну гілку, HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd` і сторонні dirty/untracked зміни. Його не reset/clean; перенесено лише власні presentation hunks. Збережено, зокрема, додаткові PPC answer-variants bootstrap і practice prompt/presentation hooks у ROOT.
- HTTP та браузер: тільки `http://gramlyze.loc`. Production, main, PR, deploy, DB apply/seeding/migrations, `.env`, hosts і серверні налаштування не змінювалися.

## Незалежний BEFORE та фактичний реєстр

Реєстр побудовано з реальних `Page` owners і їхніх локалізованих блоків, а не зі списку URL із `/theory`.

| Обсяг | Фактично знайдено |
|---|---:|
| Theory Page owners | 254 |
| Stored-content locale variants | 664 |
| Українські / англійські / польські variants | 254 / 205 / 205 |
| Додаткові requested-locale URL із fallback на український контент | 98 |
| Усі підтримані locale GET routes | 762 |
| Категорії, не зараховані до theory pages | 41 |

Взаємовиключний розподіл owners: 54 модернізовані, 4 M26, 171 інша native-сторінка, 25 rich-only. Ознаки native/rich можуть перетинатися всередині сторінки, тому їх не підсумовано як окремі owners.

Фактично використані типи блоків: `box`, `comparison-table`, `forms-grid`, `hero`, `mistakes-grid`, `navigation-chips`, `practice-set`, `subtitle`, `summary-list`, `usage-panels`. Невідомих типів у поточному реєстрі не було; malformed/foreign/unknown fallback перевірено ізольованими тестами. П'ять реальних `summary-list` блоків мають invalid-JSON empty-data fallback: 5760 (Page 193 UK), 5844 (Page 197 UK), 5858 (Page 197 PL), 5886 (Page 199 UK), 5907 (Page 200 UK). Їхній попередній fallback збережено без виправлення навчальних даних; ці сторінки входять у загальні 664 variants.

Санітизований каталог публічних маршрутів і rendering-класифікацій: [theory-single-reference-registry.json](theory-single-reference-registry.json). Повні приватні capture artifacts залишені локально й не додаються до Git.

Незалежний BEFORE збережено до зміни runtime: 24 browser states (12 M45, 4 PPC, 8 M44), 19 representative pages, 664/664 HTTP pages, 6 course states, 8 non-target controls та всі 18 M45 practice tasks / 13 details.

Обмеження реєстру: 98 locale-fallback URL виявлено додатковим аудитом після першого sync. Для них є незмінні BEFORE owner/content дані та точна перевірка незмінності routing/locale-selection sources, але немає незалежного live HTTP BEFORE. Вони перевіряються як AFTER-only і не підмішуються до 664 парних порівнянь.

## Канонічний шлях відображення

`theory.show` → caller-owned `theoryCanonical=true` → наявні owner/locale/UUID/body/order guards → render-only adapter → `theory.components.node` → спільні native-компоненти і CSS.

| Джерело | BEFORE | AFTER у theory |
|---|---|---|
| Native, M26, M27–M40 | Native blocks, point fragments, частково M42 package styles | `TheoryLegacyAdapter` / `TheoryPointDetailAdapter` → canonical components |
| M41 | Author/existing-design section/detail/example views та package styles | `TheoryAuthoredAdapter` → canonical components |
| M43 | Окремі native section/table/mistake/detail views | Тонкі guarded wrappers → `TheoryAuthoredAdapter` |
| M44 | Native та compact section/form/detail views | Finite compact grouping → той самий adapter і компоненти |
| M45 | Власні section/form/point/detail wrappers | Semantic form cards/usage/disclosure у canonical components |
| Legacy HTML | `TheoryPresentation` / rich box | Той самий pipeline + conservative `TheoryHtmlAdapter` |
| Курси та інші consumers | Історичний rendering | Явна non-theory compatibility-гілка з незмінною історичною версткою |

Канонічні views у `resources/views/theory/components/`:

- `section`, `usage`, `form`, `form-heading` — секції, пункти правил, картки та рядки форм.
- `example`, `note`, `table`, `tense-matrix`, `mistake`, `correction`, `summary` — навчальні елементи.
- `disclosure`, `fragment`, `paragraph`, `group`, `aliases`, `children`, `node` — обгортки, абзаци, композиція та anchors.
- Заголовок секції: `components.theory-native-header`.
- Point-level «Докладніше»: наявні `theory.partials.point-disclosure` і `section-disclosure`.
- Практика: спільні `components.theory-practice-exercise`, `theory-practice-heading`, `authored-practice-ui` та `practice-nojs-tokens`; різні механіки й scoring залишено окремими.

Номер M-пакета не вибирає візуальні класи. Provenance `data-*` збережено для перевірок. Дозволені semantic properties визначають форму/роль/акцент/групування. Таблична мінімальна ширина є обмеженим додатним числом, а не довільним CSS із payload.

Активне підключення естетичних M41/M42/M43/M44 overrides вимкнено саме для theory. Старі `mXX` назви лишилися тонкими compatibility wrappers без власної активної theory-верстки. Історичну реалізацію інших consumers ізольовано в `resources/views/courses/compatibility/theory/`; це не другий доступний шаблон theory.

Не створено новий frontend framework, M45-only stylesheet, broad `!important` або global `overflow:hidden`. Додано лише спільні технічні правила wrapping і перенесені правила tense matrix у `theory-unified-design.css`.

## Еталон та збереження даних

Основний візуальний еталон: [PPC Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms). Додаткові незалежні BEFORE-еталони для відсутніх на ньому елементів:

- [Linking Words for Reason, Result and Contrast](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast), Page 290 — native comparison table із чотирма колонками.
- [Reported Statements](http://gramlyze.loc/theory/reported-speech/reported-statements), Page 3 — legacy rich heading/list.
- [Formation Rules](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formation-rules), Page 1 — чинний rose rule marker, відсутній у палітрі конкретного уроку PPC Forms.

Первинні сторінки приймання:

- [Future Perfect vs Future Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous).
- [Future Perfect vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous).
- [Future Continuous vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous).

BEFORE PPC: rule marker 10px/700, label 12px/700 uppercase, form label 10px/700, formula 16px/700, point toggle 13.6px/750. Видимі glyphs перевірено через CDP: заголовки Archivo, англійський/український текст Manrope, `customFont=true`; не лише CSS font-family. Матриця фіксує sidebar/theme/viewport/DPR1/visualViewport scale1. Порівняння робиться між однаковими semantic roles, не між формулою і допоміжним підписом.

M45 зберігає 13 поглиблень, 18 завдань і 18 controls. Форми залишено двома картками по три рядки, без повернення до шести великих карток. Basic/detail grouping не переписано. Авторські формули, examples/translations, notes, levels, table columns, aliases/tokens, answers/scoring і metadata/URLs не редагувалися.

Frozen projection helpers залишилися незмінними: render-only нормалізація відділена від deterministic package generation. Усі 18 перевірених пакетів проходять exact package validation. Guard failure показує повний доступний матеріал, а не приховує його.

Trusted HTML не класифікується довільно за `<em>` або тире. Однозначні bilingual/example/table/section структури нормалізуються; неоднозначна проза, невідомі semantic attributes, rich headings і malformed fragments зберігаються повністю. Непідтримувані ARIA IDREF не переносяться на порожні aliases. Це безпечний fallback, не незалежна пакетна дизайн-система.

## Автоматичні перевірки

| Набір | Результат |
|---|---|
| `TheoryCanonicalTemplateTest` | 11 tests / 517 assertions, PASS |
| `TheoryHtmlAdapterTest` | 8 tests / 71 assertions, PASS |
| `TheoryAuthoredAdapterFallbackTest` | 1 test / 187 assertions, PASS; усі 14 реальних M41 details без ID лишаються доступними |
| `TheoryNativePointDetailParityTest` разом із HTML fidelity | 10 tests / 105 assertions, PASS; усі 7 native-типів деталей зберігають попередню структуру, тон і відступи |
| `TheoryCompactDisclosureAnchorsTest` | 1 test / 356 assertions, PASS; усі 20 M44 sections і попередні public anchors/disclosures |
| `TheoryLegacyMixedExamplesTest` | 3 tests / 63 assertions, PASS; scalar/mixed legacy examples та іменовані ключі зберігають попередній rendering без HTTP 500 |
| Node M45/M44/M43/M41 practice + contractions | 276 tests, PASS |
| Vitest theorySections/navigation/sidebarLayout/unifiedTheoryDesign | 39 tests, PASS; включно з двома computed-style перевірками CSS-межі theory/courses |
| Vitest publicAssets, п’ять релевантних перевірок | 5 PASS; 1 unchanged app.css-v4 case не виконаний у повторному scoped run |

Canonical suite перевіряє DOM/класи/ARIA для однакових legacy/authored даних; незалежність компонентів від M provenance; escaping/closed view whitelist; foreign/malformed guards; повноту таблиць/перекладів/приміток і порядок outro→warning; shared practice з чотирма механіками; відсутність змін frozen payload. HTML suite перевіряє exact fallback, anchors, inline markup, attributes, ARIA references, caption/colspan/cell fields і порядок тексту.

Combined PHP run усіх шести нових suites: 26 tests / 1,228 assertions, PASS. Використано in-memory DB/private runtime, без запису до served DB. Перевірено 46,661 protected files: 0 змін. Є одна наявна deprecation у test runtime.

Додатковий read-only аудит фактичних legacy data shapes: 3,133/3,133 normalize+render — PASS, 0 errors; 356 guarded author/package блоків перевіряються окремими шляхами. Аудит включає п'ять invalid-JSON fallback і три реальні EN-блоки з associative keys. Це діагностика, не заміна HTTP/браузерного приймання. SHA-256: `8e43c436bc89b139381e3f5d598c5ffb27e92d6ac55a02ebac7db1dbbfb727f3`.

### Відокремлені baseline/stale failures

П’ять базових PHP suites до змін: 54 tests / 5,712 assertions, один failure у `TheorySectionRenderingTest` dataset 7 (`detail-reference-changed` expectation для рядка, якого fixture не містить).

Розширений післярефакторинговий run десяти PHP files: 104 tests / 14,638 assertions, 6 errors + 10 failures. Він не позначений PASS:

- один failure той самий, виконаний у свіжому BEFORE;
- шість rich-content fixtures уже не містили `box` і шість відповідних rich-shell expectations уже не відповідали native definitions;
- два inline-HTML fixtures уже не містили старих rich fields;
- один M23 hardcoded-hash expectation не збігався з незмінним master ще до цього завдання.

Для додаткових п’яти suites немає повного executed BEFORE run; старі умови доведені незалежними source-before hashes. Контрольні hashes/accepted data не оновлювалися заради PASS. M45, M44 Simplified, M41 Existing Design, Unified Native, M44 Course Preservation та M44 Author Fidelity у цьому run нових failures не дали.

### Assets та ресурсне обмеження

Публічні `catalog-public` CSS/JS зібрано успішно окремою Vite build у приватний staging; `app.css`/`app.js` entries не змінено. Фінальна scoped build після звуження CSS до canonical components — 17.14 s: CSS `catalog-public-B6pkGWNt.css`, JS `catalog-public-CgUm4gux.js`; manifest SHA-256 `6de0716a5ceb623a69edd6bd6916584a868cf880c21968476f78d1f8d1f8d0b2`.

Повна build і один legacy `app.css` Tailwind-v4 test вичерпали ресурси/не завершилися. Зупинено тільки власний завислий build; сторонні процеси не зупинялися. Unchanged legacy asset case не названо PASS. Runtime assets/build/private helpers не входять до commit.

## Live AFTER, DB та handoff

Основна AFTER-матриця v11: PPC 4/4, M45 12/12 і M44 8/8 — загалом 24/24 PASS. SHA-256: `4cfb9811a4df56f37eb2b0d03ee243073616fa0e70766d5dd026517f7a7fc748`. Порівняно content, metadata, anchors, practice та computed styles в однакових умовах viewport/theme/sidebar/fonts. Для PPC звірено 189 same-role nodes × 32 computed properties у кожному стані. Знімок першої секції PPC до/після також byte-identical: SHA-256 `b1061fcc990f82933ce6e87b2c9ed33c27b43d0f982aa13be803fc3cc90e695e` (892×561).

Додаткові representatives: 19/19 PASS, усі мігровані M26–M43 групи та фактичні типи блоків; SHA-256 `9278f29a67fb8e0d36a2549b8bb3328b100425817772cf9af1e408134dc6b51a`. Перевірки 320 px/no-JS: 6/6 PASS, усі 18 self-check keys доступні без JavaScript, 13 details керуються клавіатурою, learning overflow — 0; SHA-256 `b69bfc817b5a99cafe638b76e55adf22e81c1040b154bdba0eaad4e9f3fedbba`.

Приймання виявило й усунуло реальні presentation-регресії: зайву обгортку табличних прикладів M44, колізію внутрішнього disclosure key зі збереженим legacy anchor, scalar legacy examples, іменовані ключі старих EN-блоків і надто широку область нового CSS wrapping-правила. Виправлено спільну нормалізацію й scope технічних правил, без package CSS, переписування джерел або зміни `TheorySection` guards. Табличний приклад використовує прямі абзаци English/translation/note; public disclosure IDs та aliases зберігаються. Scalar legacy entries мають той самий display fallback, що до рефакторингу, без додавання раніше невідображуваного тексту. Нові CSS-правила потребують canonical `data-theory-component` й не впливають на course compatibility. Попередні failed diagnostic captures не названі PASS. Після матриці v11 змінено лише обробку malformed associative legacy keys (не numeric/authored paths цих 24 станів) і пробіли порожнього рядка course compatibility; фінальний HTTP-обхід перевіряє саме останній код.

Фінальний свіжий HTTP-обхід (`registry-http-after-v5`, без resume): 664/664 stored-content variants повернули HTTP 200; SHA-256 `3ef0ad524f131cf9ff72ed3ccb7ccbf98b9b031cbb02e24e97470238b2f65bd2`. Усі 254 owners охоплено. Незалежне парне порівняння `registry-http-comparison-v2.json`: **664/664 exact, differences=[]**, SHA-256 `1d8a4dd045e83e7f475220a5df1adee34f63bbbec8614324b7cec65b7ed92116`. Metadata, JSON-LD, practice payloads, ordered learning words, punctuation-sensitive projection і table cells — exact.

| Сумарно за 664 локалізованими representations | BEFORE | AFTER |
|---|---:|---:|
| Нормалізовані навчальні слова | 610,241 | 610,241 |
| DOM `<details>` (разом із tags, не лише point details) | 887 | 887 |
| Practice `x-data` containers (не кількість питань) | 244 | 244 |
| Таблиці | 570 | 570 |
| Anchors / DOM IDs | 6,569 | 6,604 |

Усі 6,569 старих anchors збережено; додано лише 35 внутрішніх point/expanded-content IDs, без нових дублікатів. Per-page counts і exact comparisons збережені в приватному manifest. Це сума локалізованих відображень, не кількість унікальних навчальних об'єктів у 254 owners. Окрема перевірка meaningful M45 details і tasks наведена нижче.

Курси: незалежне парне live-порівняння 6/6 PASS (3 JavaScript і 3 no-JS стани): metadata, текст, секції, таблиці, links/anchors, native layout та guest gate незмінні. Access gates не обходилися; gated material не названо перевіреним. SHA-256: `f72243611ef50cea340acc60ce589ceca43a34d2bb37628a0636f774fbd58e02`.

Нецільові consumers: 8/8 HTTP 200 і exact paired metadata/JSON-LD/text/anchors/headings — PASS: головна, theory index, дві категорії, course landing та три окремі test routes. SHA-256: `8e915d36cca9abcd17a81a0b476bc7aea8e12b91546e681751ebd6af1ded6687`.

Додаткові locale-fallback routes: 98/98 HTTP 200, перевірені owner і наявність learning structure; SHA-256 `854aded37daa4c95214fe314c9713cb88e356e4d43441d77d247f30374eb2ad4`. Це AFTER-only перевірка. Експериментальне порівняння з українським BEFORE того самого owner дало 6 exact / 92 відмінності локалізованого UI: tags/category labels, default table headers, Check/Try again, practice `i18n` та locale word-search endpoints. Воно не є рівноцінним same-locale BEFORE/AFTER і не назване content PASS або регресією сайта. Парні assertions для основних 664 stored variants не послаблювалися заради цього додаткового порівняння.

Функціональне M45 приймання `interaction-after-v2`: 18/18 завдань, 18 controls і 13/13 поглиблень — PASS. Реальні UI-дії перевірили keyboard/deep links/print restoration, wrong → wrong → edit → correct, reset, відсутність подвійного score, aliases/curly apostrophes, повторювані tokens і окремий `?`. SHA-256 manifest: `bbe8664491829791e23bc989357820a9782d8132878888d37a1d1aee7c8d4ca5`.

Реальний browser zoom 125%/200% не перевірено. Спроба через Windows computer-use зупинилася на читанні стану Edge: helper не зміг упевнено визначити поточний URL для policy enforcement. Input/zoom не виконувалися; це обмеження не обходилося. Перевірки CSS viewport 1440/390/320 і DPR1 не видаються за browser zoom.

### Незмінність БД і локальної цілі

Свіжий read-only AFTER виконано після всіх HTTP/браузерних перевірок. Повний набір: **46 таблиць / 1,290,473 рядки; 0 updated / 0 inserted / 0 deleted**. Schema, усі rows/columns/timestamps, registry/category/non-theory sources — exact BEFORE/AFTER, `changed_tables=[]`. AFTER містить ті самі 254 owners / 664 stored variants і 0 adapter errors. SHA-256 registry AFTER: `8ffb2ae4935d99164c02a75bd1c5bc86b9fe33f261d38a59ae5f34a74191153b`; DB comparison: `204b9a807c3fc49e413233f1e65eb02aa4e8cd0f13d7517cf7232bcde17ff5f8`.

Фізичний AFTER повторно підтвердив document root `gramlyze.loc`, local listeners/DNS; три Apache config hashes, `public/index.php` і effective-vhost hash дорівнюють BEFORE. SHA-256: `8aafe6aa70793120bc3894eec5de35d71490161214e44fbd05e218abb2157d2c`. Налаштування, routes і БД не змінювалися.

Source-аудит BEFORE/AFTER охопив 53,835 union paths, 51,681 immutable path entries та 47 protected-config paths у кожному root: **0 unapproved / 0 protected-config changes / 0 existing immutable author-source changes**. Усі зміни належать явному allowlist; єдиний виняток у `docs/content` — новий прямо замовлений контракт `theory-template.md`, не редагування accepted author data. SHA-256 comparison v1: `d5514395262fe6d4c2d55499c2e497eac181a59a1d68a73b48c9282ec3125e8c`. Після фінального копіювання цього звіту до served ROOT виконується повторний source-аудит; його приватний hash залишається поза самим hashed report. Сторонній dirty стан ROOT не перезаписано.

## Відтворення та приватні докази

Versioned read-only diagnostics: `capture-theory-template.cjs`, `capture-theory-template-http.cjs`, `check-theory-template-m45.cjs`, `compare-theory-template-http.cjs`, `inspect-theory-template-local.php`, `capture-theory-template-physical.cjs`, `capture-theory-template-sources.cjs`, `compare-theory-template-state.cjs`, `capture-theory-template-request-locales.php` у `tools/diagnostics/`.

Приватні HTML/screenshots/manifests: served ROOT `storage/app/theory-template-local/`; isolated test results: worktree `storage/app/seo-m2-local/`. Вони не комітяться. Незалежні BEFORE manifests мають SHA-256:

- Source inventory: `140ee07d04e358703745355b288058ac7be1ef2ace61e36ff19afdeea123927b`.
- Physical local target: `c5c55ed4400562c1138460823990c39ad00cccad7572eea6fe4a2db05facda0c`.
- Registry v1: `0a2109e6aa0fc6ee0dba207f9eb2b864af94f08f8e1e604fdcb6008b41eef628`.
- Registry v2 (лише виправлені rendering annotations, ті самі DB/content): `ced519a20d3b8c9d69e2188e38746e384b6d98f6feccceb3b8e280d887b78fdb`.
- Browser matrix: `c11b8cc7e287b3ba1463672780349bb274cc7caaea1d15945a1b3a2f8990e485`.
- Representatives: `0a8d1bf2f593d30de4553ace8443e2e0b83adf39932e780308de70cd8bcdb02e`.
- HTTP 664: `11cce54e1e70666a7bd6a6304e494c6a84dec55669c47ed6882a9bd3c414fe49`.
- Courses: `eeae562e5cf04305d9a17ca2641b00f909058b27df9ca0da991c1c8e9bc77acb`.
- M45 interactions: `eb89341cb24b61ec994cd25142131161e4acfb8133427ae91a4ac049e7860495`.

Правило подальших SEO-етапів закріплено в `AGENTS.md` і [theory-template.md](../content/theory-template.md): пакет змінює навчальні дані, а не дизайн; нові типи компонентів і редизайн потребують окремого завдання.

## Git handoff

Для commit явно обрано 124 пов'язані файли: canonical rendering, тонкі wrappers, ізольована course compatibility, тести, read-only diagnostics, AGENTS і поточні звіт/реєстр/контракт. Секрети, `.env`, vendor/build, screenshots, DB backups/dumps, runtime evidence та сторонні ROOT-зміни виключені. Перевірено staged path allowlist, diff/whitespace і відсутність секретів; історичні звіти не переписано.

Публікація — scoped commit і звичайний push тільки `codex/theory-single-reference-template`, без main/PR/deploy. Commit SHA та незалежне підтвердження `remote SHA == HEAD` наводяться у фінальному handoff повідомленні, щоб не створювати self-referential commit hash у самому звіті.
