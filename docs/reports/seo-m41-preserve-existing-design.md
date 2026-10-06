# M41 follow-up — чинний native-дизайн, незмінний авторський контент

Попередні native-компоненти та їх оформлення відновлено на трьох реальних сторінках M41; новий авторський контент і практика збережені. Локальне приймання завершено — PASS. Реалізація presentation-only: SQL apply, seeding, migrations або restore не виконувалися. Exact Git handoff SHA фіксується окремо після normal commit/push.

## Межі та версії

- Репозиторій `Pvitaly91/engapp-codex`, accepted M41 base `b92854de75ced14865594137f139a3f7f3b12d03`.
- Робоча гілка `codex/seo-m41-preserve-existing-design`.
- Native templates/configurations reference `ab61310a81f2389353c264fe23266025fe49ffd6`.
- Реальні сторінки й browser checks — тільки `http://gramlyze.loc`. Production `.com/.ub`, main, PR, deploy, header/sidebar/background/layout/global typography поза змінами.
- Served ROOT `D:/DEV/htdocs/gramlyze.loc`; окремий worktree від accepted M41. Dirty ROOT та сторонні PPC/M39/M40 hunks збережено; shared-файли не відновлювались цілком зі старого Git.

| Сторінка | URL |
|---|---|
| Past Simple vs Past Continuous | [Теорія](http://gramlyze.loc/theory/tenses/past-simple-vs-past-continuous) |
| Present Simple vs Present Continuous | [Теорія](http://gramlyze.loc/theory/tenses/present-simple-vs-present-continuous) |
| Present Perfect vs Past Simple | [Теорія](http://gramlyze.loc/theory/tenses/present-perfect-vs-past-simple) |

## Причина та адресне виправлення

M41 призначав один `m41-author-section` view усім навчальним секціям, незалежно від змісту. Його власні section-styles і generic muted articles замінили forms-grid, usage-panels, mistakes-grid і summary-list. Додатково чинний глобальний M25 `theory-design` CSS нейтралізує native utility backgrounds/borders/font-mono навіть у старих шаблонах. Це окремий встановлений факт, не припущення про зміну production.

Нова SHA-bound finite presentation mapping повертає структури попередніх компонентів після всіх existing M41 author/owner/locale/UUID/type/order/body guards. На valid section path старий універсальний view/styles більше не застосовуються. Вони лишаються повним read-only fallback; frozen author sources і DB block types/body не переписуються.

Native utility кольори, рамки та monospace приклади відновлюються тільки всередині `.theory-design .m41-existing-design[data-m41-author-section]` для трьох exact owners. Глобальні CSS/resources/public build не редагуються. Залишаються колишні зовнішня біла картка, section header/number/level badge, відступи/радіуси, теги, розділювачі та theme shell. Не вводиться global overflow hiding або глобальне вимкнення uppercase.

Використані чинні компоненти:

- `usage-panels`: кольоровий border/background, круглий номер, службовий короткий заголовок, окремі світлі 💬 examples з EN та UK.
- `forms-grid` через `lesson-rule-cards`: три пари форм на сторінку, шість source-owned EN/UK cells. Original form-table текст має одне видиме місце; прихована дубльована таблиця не створюється. Додатковий authored forms-note — одна повноширинна native card, без зайвої вкладеної рамки.
- `comparison-table`: нейтральні зіставлення двох граматично можливих ситуацій; жодного guessed wrong/❌. Для M41 лише дві непорожні EN/UK колонки (source не містить notes), native 14px monospace English; default інших owners зберігає три колонки.
- `mistakes-grid`: тільки вісім explicit wrong/right пар, визначених author paragraph byte ranges; український контекст і кожний байт абзацу збережено рівно один раз.
- `summary-list`: повний авторський алгоритм/конспект, без зменшення до старої кількості пунктів.

## Новий контент → наявні компоненти

| Owner / source section | Native presentation |
|---|---|
| Past / `past-core` | Simple blue; Continuous emerald usage-panels |
| Past / `past-forms` | 6 forms-grid cells + visible be forms-note |
| Past / `past-together` | background emerald / parallel blue usage |
| Past / `past-markers` | clock sky / yesterday blue / state amber usage |
| Past / `past-mistakes` | 2 genuine correction points; neutral sky `past-not-error` |
| Past / `past-summary` | exact four-step summary-list |
| Present / `present-core` | routine blue / current emerald usage |
| Present / `present-forms` | 6 forms-grid cells + visible auxiliary forms-note |
| Present / `present-uses` | around emerald / temporary blue / change amber |
| Present / `present-states` | know blue usage + neutral think comparison-table |
| Present / `present-context` | markers blue / always emerald / future amber |
| Present / `present-check` | 3 genuine correction pairs + full summary-list |
| Perfect / `perfect-core` | now emerald / finished then blue usage |
| Perfect / `perfect-forms` | 6 forms-grid cells + visible V2/V3 forms-note |
| Perfect / `perfect-experience` | experience emerald / news then details blue |
| Perfect / `perfect-time` | neutral today sky / duration blue comparisons |
| Perfect / `perfect-markers` | recent emerald usage + 3 genuine correction pairs |
| Perfect / `perfect-summary` | full authored summary-list |

35 complete points зберігають source order та stable point IDs; 18 sections не підганяються до попередньої кількості або універсального типу. Extra author elements не переносяться в unrelated slots/owners. Вісім corrections: Did she took→Did she take; We were wait→We were waiting; Does she works→Does she work; They waiting→They are waiting; I am knowing→I know; She has wrote→She has written; Did she wrote→Did she write; I have checked it yesterday→I checked it yesterday. Це selectors існуючого source text, не нова редактура.

## Автор, details і практика незмінні

Author master `docs/content/m41-authored-tense-comparisons.v1.0.1.json` SHA-256:

`9020cf977e903c76f93c7165bbe402567b5fbf40a98a8139dc6751b223b34553`

Frozen M41 BEFORE `5966e2b5e330030fc399b77a5a80880a57462daac4b1869f94b4027cdbcae808` та accepted projection `1324f0627dcd53b70b135b78c06204c3a77b6e02fe0a48056ac7750b650becfc` не змінені, як і correction/approval history та canonical definitions. Окрема selector-only presentation mapping `database/content-patches/m41-existing-native-design.v1.json` має SHA `2b985fcc79b1481b38c7a6570fcc6829858bc99c76a9f2d569574958094de1d7`; вона не містить замінного teaching prose.

14 own-point details — 4/5/5. Кнопка всередині конкретного colored point, відкриває тільки його detail. Original fragment IDs/type/frozen HTML values незмінні; ephemeral exact raw author detail використано лише для native 💬/EN/UK markup. Full basic, main examples/translations не ховаються. Нових short details немає. Native mouse/Enter/Space/deep links/print/no-JS зберігаються. Answer-key disclosures у практиці рахуються окремо.

Практика: 18 tasks, 32 required controls (9 select / 8 choice / 15 manual), 14 compound tasks, 20 explicit accepted manual variants, 55 logical token groups. JS, authored prompts/keys/options/tokens/scoring/aliases/reset лишаються M41 accepted. Answer-only natural-case candidates; full bilingual author key окремо після check, ні rationale, ні переклади в кнопках. Primary unchanged banks — 72/72/24 type4; widget бере тільки свій bank.

## Реальні дані та no-write контракт

Fresh before SELECT `m41-design-before-v1.json` (2026-10-06 20:16:12 UTC), SHA `72096a886d4828757915a49a069bc79ff8ec71c8a052397fc4182c4399294f12`: 24 full raw protected table fingerprints (тепер target pages.text теж не виключено), усі target rows/all locales (28/28/24), IDs/UUID/order/full metadata, banks/progress/pivots та 47 prior owners. Це current working local evidence, не reused M41 pre-apply snapshot.

Повний авторський текст уже є в реальних M41 rows; finite rendering не потребує DB changes. Source/code sync: exclusive shared backup `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-local/source-before-e0c19e1645c6433f`, 8 shared originals. Нові файли перенесено лише за відсутності конфликтних ROOT paths; існуючі shared files змінено адресними reviewed hunks. Модель, `.env`, Apache/hosts, migrations, seeders, DB restore, cache clearing та startup/HTTP apply hooks не змінювалися/не запускалися.

Actual final DB diff: **0 updated / 0 inserted / 0 deleted**. Fresh SELECT після всіх browser/HTTP requests — `m41-design-after-final-v1.json` (2026-10-06 21:30:06 UTC), SHA `bc03188de10be365136042c5f6dfb93bcfe26faada17972fa76e85e565910ad2`. Незалежний comparator підтвердив byte/logical equality усієї before/after evidence, крім `at`: усі 24 full raw protected table fingerprints, 80 target rows/all locales/IDs/UUID/order/body/type/full timestamps, banks/progress/pivots та 47 prior owners незмінні. Total text_blocks6563; non-target6533/SHA `b941770db74c6511345575f2466bbaf5a32764ba4d1c04d6c483c91882232a40` незмінні. Private equality receipt SHA `31c9ea480adc7f28d3896319ac9d22a509e65a1ebebf01ce6d99de7ffe9d4a9c`.

## Visual reference і actual acceptance

Ізольований reference: exact `git show ab613` definitions/native templates, unsaved models, SQLite `:memory:`, 0 DB queries. Old teaching text використано тільки для позначеного reference, ніколи як after content. Reference вставлено лише в diagnostic browser DOM після GET чинного local shell/assets; working БД не відкочувалась, routes не додавались.

Reference: 12 states, desktop1440×1000/mobile390×844, light/dark, DPR1/zoom1, 64 bounded component screenshots + 12 fullpage. Визуальне порівняння не вимагає pixel equality всієї сторінки: source text/height інші. Raw reference показує нейтралізацію inner colors глобальним CSS; це прозоро зазначено. Повернені colors/borders/mono відповідають declared native utilities й прямій вимозі користувача, не видаються за старий flattened screenshot.

Final-v4 page acceptance: 12/12 PASS, 72 section-state component samples, 56 own-detail interactions; exact full master text/35points/form cells/source order. Main/cards learning overflow0; широкі comparison tables scroll locally з real focus+ArrowRight/end/back (3 table identities ×4 states, 6 mobile samples). No JS/page/HTTP/network/console errors у завершеному browser run. Outer native card border/radius/padding, header/title font/weight/casing збігаються з reference. Palette і correction backgrounds виміряні окремо, не лише inferred з CSS: light wrong rgb(255,241,242), right rgb(236,253,245); dark відповідні actual palette values. Native bank sampling підтверджено. У всіх comparison samples рівно2 headers/2 cells per row, жодної порожньої notes колонки; EN font реально monospace14px.

MAIN сам оглянув reference usage/forms/mistakes/summary, усі36 PREv3 images (18desktop/18mobile), 10 current-v4 representative components у light/dark та desktop/mobile, 3 current open details і 6 correct/wrong POST samples. Gate `design-main-pre-v3-review.json` bound до actual PRE SHA `c3959a8d4883a3e6edd80b698616ca1d9eb0d92008e24c807a8834d9c2159ce6` і freeze `3a053d8e7faa392f6b7e3f7d66c2f5195537a4547df761feb73b90b603e78fd6`.

Після двох останніх table-only rendering правок fresh PREv4 теж36/36 PASS. `design-final-v4-practice-delta.json` доводить: changed paths тільки comparison partial/scoped styles; усі36 actual prompt/key/control/labels/token counts/natural casing/initial states exact до MAIN-reviewed v3; practice JS/markup/master untouched, і практика поза кожним зміненим CSS ancestor scope. Reused visual gate підтверджено окремим MAIN receipt `design-main-v4-review.json`, не видається за повторний ручний огляд36v4 PNG.

Final-v4 page manifest SHA `62273dd54cafe2b1493394f7fb84ce36ee14a19c5eddd033e07b1489874c8b31`; component comparison receipt SHA `2c587ba749ec0b0caf78979da64f2e948a3ddc1d5e8d13aac3656ba7eb761f39`; fresh PRE SHA `8c8269cff0980a824444a044dc98acb1553a58cbd28c25365d251ed098cdd349`. All23 app/view/source bindings stable through final browser phases.

Final-v4 functional practice: 36/36 cases у двох viewport, 72 POST correct/wrong screenshots; разом із36PRE =108 required visual states. Keyboard, explicit aliases, token/manual construction, visible feedback, reset та score6/6→0/6 PASS. Post manifest SHA `4363077a9b439872ffade754a56e5ff2eaead02410dd78f65f8ef82df5d31cc8`.

Supplemental v4 PASS:28 compound-part omissions,64 semantic negatives,9 no-JS pages/54 native answer keys,15 M41 literal token banks/55groups, усі36 M39/M40 regression cases з власними accepted helpers (72 PNG). Zero browser failures/violations; receipt SHA `e192a312e3412a3f7d1f54e28a88fc4951bf464bf890f1e0ddc1fd821ca57418`. Контрольна незмінена native сторінка `/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms` у4states має exact main SHA та computed main/card/header/title/point/example/translation/sidebar/font/layout/color equality до fresh before; final control SHA `25d6a1b132fa788e3abafd619e1e595e6d16dbd8d265b1cd383354f669571c83`.

Fresh required HTTP:54/54 GET200; current M41 title/H1/description/canonical/meta robots/X-Robots/OG/Twitter equality, ordered sitemap554/SHA `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`, усі48 controls main-text/details/anchors unchanged. Before SHA `60cbdda9313499c16491437689cb4656af3f95222575a0dc15fbe064e35effd5`; after SHA `c7bf68009e68d5803e5e0ba9c684201fd3c01b4552f1fc85cf7f640805ee6b24`; equality receipt SHA `5bda371df2d1c9ebfe325f6b63278b52af8257fbad2f69fabfddafd791db7301`. Повний final SELECT зроблено після закриття всіх цих requests.

## Автоматичні перевірки

| Suite/check | Фактичний результат |
|---|---|
| Pure finite mapping/error selectors | 9 tests/190 assertions PASS (не додаються повторно до final suite count) |
| M41 author + package + native design final v5 | 46 tests/3408 assertions PASS, protected46,661 file changes0 |
| Raw design DB evidence guard | 24 tests/27 assertions PASS |
| M41/M39/M40 practice + design HTTP/visual helper Node suites | Final405/405 PASS, failures0 (HTTP helper22, visual helper18) |
| Immutable master/projection and deterministic new mapping | PASS; mapping18sections35points8errorpairs18cells, writes0 |

Негативні тести зберігають exact owner/locale/UUID/type/order/source/hash gates та reject mapping mutation/false error/reordered points/range corruption. Semantic-null detail resolver повертає full raw accepted content, all14details visible/no disclosure/500; три raw form tables показуються рівно один раз без six-card duplication. Complete source text не залежить від успіху нової presentation mapping.

Known diagnostic attempts збережено: initial inline-@php/multiline Blade tokenizer parse error (виправлено, final PHP suite PASS); screenshot-name collision на другій comparison table (helper indexed, не application failure); local `ERR_NO_BUFFER_SPACE` при JS GET (повтор послідовно, final12states без цього збою). Existing PHP8.5 PDO deprecation не виправлялася сторонньою config зміною. Earlier optional course GET timeout збережено окремо; course не входить у required design-follow-up acceptance.

У final HTTP старий category URL `/theory/past-perfect-continuous` мав30s timeout на першій спробі, друга дала200 за17.57s; точні attempts збережено, не названо «перший запитPASS» і не зроблено причинного висновку про M41. Browser phases виконано послідовно одним helper Chrome; налаштування сервера/мережі/кешу не змінювалися. Initial/final test runs не складаються як різні додаткові тести. Final syntax:8PHP +5Node files PASS; immutable/deterministic audits та whitespace diff PASS.

### Representative screenshots, оглянуті MAIN

Private screenshots збережено локально, не Git. Позначений raw reference містить старий teaching text тільки для порівняння компонентів.

| Компонент | Reference ab613 / actual after |
|---|---|
| Usage | [Reference](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-reference/reference-v1-past-simple-vs-past-continuous-1440-light-usage-panels-3.png) · [Current](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-past-simple-vs-past-continuous-1440-light-past-core-closed.png) |
| Forms | [Reference](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-reference/reference-v1-past-simple-vs-past-continuous-1440-light-forms-grid-2.png) · [Current](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-past-simple-vs-past-continuous-1440-light-past-forms-closed.png) |
| Mistakes | [Reference](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-reference/reference-v1-past-simple-vs-past-continuous-1440-light-mistakes-grid-4.png) · [Current mobile/dark](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-past-simple-vs-past-continuous-390-dark-past-mistakes-closed.png) |
| Summary | [Reference](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-design-reference/reference-v1-past-simple-vs-past-continuous-1440-light-summary-list-6.png) · [Current](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-past-simple-vs-past-continuous-390-light-past-summary-closed.png) |
| Own detail | [Current inside point](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-past-simple-vs-past-continuous-1440-light-past-event-open.png) |
| Neutral comparison | [Current two-column](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m41-local/design-pages-v4-present-simple-vs-present-continuous-1440-light-present-states-closed.png) |

Viewport/theme-matched comparison для всіх12states міститься в private `design-final-v4-design-comparison.json`; table links вище — оглянуті representative, не твердження про same viewport у кожній парі.

## Постійне правило і handoff

В AGENTS.md та `docs/content/theory-summary-detail-contract.md` додано правило: content change не дозволяє redesign; native components/colors/borders/typography/spacing/examples/headers зберігаються, нові explanations/practice вбудовуються, standalone author-preview не є design authority, redesign лише за прямим окремим запитом. ROOT AGENTS збережено адресним додаванням; існуючі сторонні правила не стиралися.

Local DB/HTTP equality, supplemental/control та visual acceptance завершено. Точний Git SHA/remote==HEAD після normal branch-only commit/push фіксується в private handoff і фінальній відповіді, не самопосилально всередині commit. Явно stage лише пов'язані source/mapping/tests/diagnostics/report/instruction files; приватні SQL inventories, source backup, proofs, cookies/tokens, captures/logs/screenshots не комітяться. Production/main/PR/force/deploy/M42 не виконуються.
