# M43.1 — оформлення за фактичним PPC Forms

Дата: 2026-10-08. Локальна реалізація: `http://gramlyze.loc`.

Користувач **не прийняв оформлення M43** і визначив точним візуальним еталоном [Past Perfect Continuous: Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms). Цей follow-up використовує його фактичну систему оформлення. Кольорові CSS overrides M41/M43 не є еталоном M43.1. Історичний звіт M43 збережено без змін.

## База й точний scope

- Actual base: `477897543e91c02dff90747b439cd4ac939bfc6a`; ancestry M43 і accepted M42 підтверджена.
- Робоча гілка: `codex/seo-m43-ppc-reference-design`.
- Worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Фактичний served ROOT: `D:/DEV/htdocs/gramlyze.loc`; document root — `public` цього каталогу.
- ROOT залишається на `codex/production-ready-a14788dac`, SHA `41820a2bebdf69004fa7209a2a38457f93efabbd`; на початку 46 649 deleted / 123 modified. Сторонні changes збережено. `main` — `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`, без змін.

| Ціль | Exact identity |
|---|---|
| [Past Perfect vs Past Perfect Continuous](http://gramlyze.loc/theory/tenses/past-perfect-vs-past-perfect-continuous) | `Database\Seeders\Page_V3\Tenses\TensesPastPerfectVsPastPerfectContinuousTheorySeeder` |
| [Stative Verbs](http://gramlyze.loc/theory/tenses/stative-verbs) | `Database\Seeders\Page_V3\Tenses\TensesStativeVerbsTheorySeeder` |
| [Used to / Would](http://gramlyze.loc/theory/tenses/used-to-would) | `Database\Seeders\Page_V3\Tenses\TensesUsedToWouldTheorySeeder` |

Це presentation-only зміна десяти application files. Немає content apply, seeding, migration, restore, зміни definitions/master/projection, runtime JS для перефарбування, global CSS зміни, build/dependency install, server/hosts/.env зміни, production HTTP/DB, deploy, main update, PR, merge чи force push.

## Причина розбіжності та виправлення

Фактичний `theory-unified-design.css` уже визначав нейтральні `.theory-item`, приклади з акцентною лінією та звичайною типографікою. Більш специфічні inline правила `m43-native-styles.blade.php` повертали кольорові заливки/рамки, monospace 12px / 18px, білу рамку прикладу та курсивні дрібні переклади. Helper також генерував ці utility classes. Наявність назви native component не забезпечувала відповідність еталону.

Прибрано M43 overrides background/border/radius/shadow для великих rule panels, dark palettes, English monospace/малих розмірів, курсиву UK, власних декоративних `.theory-rule` surfaces, кольорових error example surfaces і дрібного answer-key шрифту. Замість наступного шару CSS спільна система знову визначає оформлення.

Збережено технічні правила: padding 0 у вкладених native bodies, form composition, height/stretch, wrapping довгого тексту/кнопок, локальну прокрутку таблиць, min-width cells/examples і нормальний text-transform input/button. Не введено global overflow:hidden або broad !important reset. Локальне clipping заокругленого practice header стосується тільки його card; learning overflow перевіряється окремо.

`M43NativeHtml` продовжує escaping і збереження lang/text/note_uk. Знято невідповідні aesthetic utility classes. Frozen `detail_html` та feedback не змінено: renderer додає лише aria-hidden іконки й flex wrappers навколо exact inner HTML. Перший instruction paragraph отримує стиль короткої підказки еталона; повноцінний context та English content не зменшуються.

У shared usage/forms widgets тільки caller-owned M43 flag змінює presentation. Heading h3 збережено, малий колірний акцент переноситься на span; marker має сталий розмір і не стискається на mobile. Usage description і examples — окремі siblings, як у еталоні. Forms paragraph spacing застосовується лише до прямих дітей і не збільшує margin перекладу. Table cells успадковують table typography, th мають відповідні uppercase/tracking utilities; усі колонки/рядки/примітки збережено.

Практика M43 має нейтральний padded header і окремий controls/feedback body. Заголовок вправи оформлено як у reference; explicit cases, accepted aliases, tokens, scoring, reset, handlers, IDs і bank links збережено. Wrong/right examples мають символи, закреслення та доступні текстові позначення. Інші shared consumers отримують попередню розмітку.

Групи controls мають reference white surface, тонку рамку, radius8px, padding12px; body — padding16px/gap12px. Номер вправи береться з actual source_index1–6 і позначений aria-hidden/data-theory-ui. Реально виміряний badge —10px/20px/600,20×20,radius4; попередня inferred15px/400 перед застосуванням відхилена за CDP calibration.

Live feedback probe виявив, що generic paragraph selector робив і correct, і wrong P чорними. M43-only status DIV із reference12px/16px/600 усуває саме цей конфлікт; слова, expressions і ARIA не змінено, non-M43 лишається P. Actual reference light red/green —rgb190,18,60 / rgb4,120,87; dark —rgb253,164,175 / rgb110,231,183. Empty/partial/wrong/correct перевіряються за computed colors, не назвами класів.

Початкові authored controls використовували `bg-card`, `border-border`, `text-foreground`, які не давали потрібного public palette оформлення. Actual computed результат був transparent surface і bright framework borders у dark; public Tailwind config не має відповідних semantic color entries. Шість M43-only class hunks тепер використовують **наявні** reference utilities: `bg-white` спільною системою переводиться у theme panel/line, blue choice та emerald token accents, reference radii/padding/weights. Selected/correct/wrong expressions і всі input/keyboard bindings не змінено; text-transform:none і wrapping залишено для повних authored answers. Нових CSS palette overrides або JS-перефарбування немає.

Exact owner/locale/UUID/body guards, caller-owned theory opt-in, whitelist native views, повний static fallback, `preserveCourseBlocks` і `@once` поза discarded validation render збережені.

## Свіжий BEFORE і незалежний еталон

Докази цього завдання — `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local`; історичні M43 proofs не використовувалися як поточний BEFORE.

Fresh Apache config hashes, `httpd -S`, port-80 listener/process і DNS встановили `127.0.0.1 gramlyze.loc` та точний document root. CLI SELECT-only transaction підтвердила фізичну MySQL `localhost:3306`, `gr2`, exact identities/owners і відповідність поточних definitions/DB. Existing `/health` та `/dev/site-mode` підтвердили локальні web reads і development SiteMode. Ці endpoints не розкривають web DB name: прямого web SELECT proof з nonce route не заявляємо; route не додавався. Web identity звірено зі свіжим effective vhost і фактичним навчальним DOM/source/DB.

Приватні BEFORE:

| Доказ | SHA-256 |
|---|---|
| `physical-target-before-v1.json` | `2d9e8c065c1d721fe6cfaad88513f4b4482774ccf574a93b78fddf4caa3c8ef2` |
| `m43-1-before-v1.json` — усі 46 tables | `f97e65661269b7c5ca1f61f57218e3db38883763fc8640ee20a14829c7842f3f` |
| `source-before-v1.json` — 52 518 paths | `27dd715216e9c146257fdff1bb3a7db9bc11ec79573dd456c8978047aa3afa99` |
| `frozen-before-v1.json` — 10 source files | `32cc8703a7df4cb8e57dc1785dd2e659474b0789b933dc49ced6bc8b9c8e0a36` |
| `config-before-v1.json` — 55 paths | `2c597e6d008676943de29f2b3981d3675730fc7f26397c01389db76309c72701` |
| `before-v1-http.json` — 65 GET rows | `cf72430e01a7037c1d108ed5e35b08c646a340169120b0381693ffd46372e072` |
| `author-dom-before-v1.json` — 444 lang fragments | `161747665deb3cc2c257949984ac87fc00251bed4f2bfba340362c1f6d74bfce` |

BEFORE matrix: **12 targets + 4 reference = 16/16 capture states**; це CSS/fonts/DOM/screenshot evidence, не functional-after PASS. Viewports 1440×1000 і 390×844, DPR1, default browser zoom100%, fresh guests без cookie/Referer, однакова тема та font policy. Дозволені тільки GET `.loc` і потрібні Google Fonts resources. Manrope/Archivo faces фактично завантажено; CDP platform fonts підтверджують Manrope для body/examples і реальний Arial fallback для кириличних Archivo headings. Одна сторінка не порівнювалася із заблокованим font, а інша — із завантаженим.

Збережено computed properties, matched/inherited CSS rules і source URLs. Спільне CSS джерело — чинний локальний `build/assets/catalog-public-C70j-baD.css`; build не запускався. Незалежні expectations узято з read-only reference BEFORE, SHA `43a6c7bf701652c956221ffacadea91eb58af1b62ebb8671c620602323e08c60`, а не з M43 AFTER.

## Representative computed comparison

Наведені CSS px для desktop/light; mobile example padding відрізняється згідно зі спільним media rule. Actual raw values, variants і declarations збережено у manifests.

| Елемент | M43 BEFORE | PPC reference / узгоджений contract |
|---|---|---|
| Outer section card | Already native | White surface; neutral 1px line; radius24px; shadow `0 10px 28px`, text alpha .06 |
| Usage panel | Суцільні blue/green/sky/amber surfaces, 1px кольорова рамка | `--theory-soft`, border0, radius16px, content padding16px |
| English example | Monospace12px / 18px | Manrope14.4px / 25.2px, normal, weight500 у basic usage |
| UK translation | 12px / 18px, italic | Manrope14.4px / 25.2px, normal, weight400, muted, margin-top5.6px |
| Example box | White border1px; p12px | Blue inline-start3px; neutral soft surface; radius8px; padding13.6px16px desktop / 11.2px12.8px mobile |
| Usage caption / marker | Кольори + стискання long mobile caption marker | Caption12px/16px700 uppercase, невеликий accent; marker20px без стискання |
| Forms description | 14px/22.75; nested UK margin9.6px у pilot | Muted14px/20px400; translation uses its own5.6px margin |
| Table th / td | td utility14px/22.75; header без відповідного tracking | th12px/16px750 muted uppercase; td14.4px/24.48; cells padding13.6px16px |
| Disclosure summary | Параметри переважно збігалися | Blue13.6px/20.4px750, padding12px0; detail body padding8px0/gap16px |
| Practice header | Transparent/no border/padding0; h4 default400 | Soft surface, bottom line1px, padding12px16px; heading14px/20px600; hint12px/16px muted mt4px |

Кольори семантично різних table columns і невеликих markers перевіряються за реальною reference palette, без копіювання її контенту. Для компонентів, яких у read-only PPC reference немає (зокрема окремих wrong/right boxes і feedback states), ця відсутність записана явно. Не вигадано exact reference PASS: вони перевіряються на збереження семантики й спільні generic example/panel правила.

## Локальна синхронізація файлів

Переглянуто source-only proposals; DB доступ у sync tools відсутній. Спочатку дев’ять application files синхронізовано з exclusive source backup v1. Після пілота уточнено spacing і header markup у шести файлах. Загальний scope — **10 application files**, не 9+6 різних файлів.

- Source proposal v1 SHA `6999186ded1aace403c7f5bb400fdb749808a43f304367f455538f34a2fbe4b9`; result SHA `9068e4124cd9d77b6422aa5dcb87fb20934f692e55e8759ab8b7f8c97eb08ce4`.
- Preview v2 SHA `7469666676cde7fdab1ec4ff24adbbb4aa8a79daddbb1baf1df0ea3387c85e83` залишено immutable/unused після остаточного уточнення hint typography. Він не застосовувався.
- Final proposal v3 SHA `e834e8fc8cbfdfa7e39216c90207ad56a720cf872de35a5152976ac7f2a92582`; source result SHA `1e58e67eb4deeb27d8d2769e86c6a34d4acd79d9b0023d089c08c75e7ca9e418`.

- Practice proposal v4 залишився unused після actual badge measurement. Applied v5 SHA `abbd1fed1076222310b795fb04259fc8fc5a18373c61d8cefa4f3d947cc4f690`; source result SHA `49e72671a69f3d054b3aba7dd8f81f6d12c26279343ae0d4ffaed624db63137d`.
- Exact приватні one-file backups/receipts збережено для feedback tags/body gap, note classes та шести utility hunks controls. Common view final SHA `413e1944cede80a6037ff85ca2e9accab27936659232105bf09a85a7c01b798a`; own section final SHA `96049e0d44dbae9a0c3310e93b2ace5fcb91aaa6749ce2c9151b1210a8bd951b`. Root=WT перевірено. Content/DB apply не виконувався.

ROOT old hashes перевірялися проти fresh BEFORE або reviewed v1-after. Unchanged lines збережено byte/EOL exact; ні чужий текст, ні ROOT dirty changes не перезаписано. Source backups приватні, без `.env`. Невпорядкованість Mutex lines у `httpd -S` не є зміною конфігурації: guards звіряють exact vhost/source line, unchanged config/public hashes і sorted semantic dump, а raw SHA збережено як evidence.

## Реальне приймання та screenshots

Pilot v1 виявив реальні розбіжності form translation margin і practice header; візуальний перегляд додатково виявив неправильне розміщення usage examples у description container. Ці причини виправлено markup/selectors. Pilot v2 **3/3 PASS**: semantic computed roles, current master fidelity, metadata, fresh own banks, layout і keyboard table scroll. Pilot не підміняє основну 12-state matrix.

Final-v2 завершив повну **12-target functional matrix +4 reference states**:72 task-runs,140 control-runs,24 detail-runs. Усі18 tasks/35 controls перевірено з initial/empty/partial/wrong/correct/reset, aliases/tokens/keyboard, independent disclosures/deep links/TOC/print/reload. Final-v1 зупинено після5 complete states/210 PNG, коли додатковий visual audit уточнив control panels; interruption receipt не видається за matrix PASS.

Фінальний bounded style/state run **16/16 PASS**:12 targets+4 read-only reference, **1 756 component /16 688 property comparisons, zero differences**,396 PNG. Перевірено initial controls усіх72 task-runs/140 control-runs (420 actual elements) і48 live feedback states. Непідтверджених font/console/page/HTTP/network errors немає. Manifest `browser-after-style-final-v1/manifest.json`, SHA `0a320c34a987b30f28f536376d09396e0c59539a844eb43c92e018e76ab82282`.

Logical V2 target matrix **12/12 PASS** (fullaliases/tokens/mechanics); its4 reference captures had2 diagnostic width comparisons, де comparator помилково прирівняв second shrinking badge19.28125px доfirst20px. Це не regression еталона: original reference calibration містить обидва widths. Latest style-run порівнює reference index-to-index й проходить4/4; M43 marker20px має окремий wrapping safeguard. V2 manifest SHA `bc4d167b66bb3bb2727418c9c1fb1aae4d73b3e19fe243e1745c7426bb54174b`,714 PNG; його невдалі reference matcher checks не приховані в загальному16/16 logical PASS.

Supplemental **6/6 PASS**:3 no-JS (visible basic,6details,18static answer keys/manual tokens) +3 width320;24 PNG. SHA `22210982476ea8e0acf902cb6e191960d5abdb6ca873084829d4656932506a81`. Автооцінювання без JS не заявляється. У no-JS один script request закономірно скасовано disabled-JS/CSP policy; це recorded expected behavior, не нова network failure.

Fonts: усі16 final states завантажили12 needed faces; font checks/cdp actual font узгоджені. Target main/content overflow=0. Mobile tables576px/322px локально прокручуються клавіатурою; decorative document overflow0–10px записано окремо. Справжній Ctrl+zoom лишається непідтвердженим. Незмінну176-alias/36-token/24-detail логіку взято з повної V2 matrix; різні overlapping runs не підсумовуються.

Read-only mobile reviewer особисто переглянув154 AFTER section/detail/practice/feedback/footer PNGs і23 BEFORE/reference/calibration PNGs; MAIN — representative usage і всі3 authored practice blocks та correct feedback. A summary має окремі one-item groups з marker1: це вже було в BEFORE, не нова numbering regression; code/content цього scope не переписано. Last generic-note class-only fix торкнувся лише A sections2/4: `text-sm rounded-lg p-3`, matching reference14px/20px/radius8; B/C таких section notes не мають. example.note_uk не зливалися з перекладом і не приховувалися.

Readable reference/BEFORE/AFTER screenshots зберігаються приватно, з SHA і manifests. Наприклад reference usage: `browser-before-v2/before-v2-reference-1440-light-section-2-1.png`; M43 BEFORE A usage: `browser-before-v2/before-v2-A-1440-light-section-3-1.png`. Різна висота/кількість examples не прирівнюється до CSS mismatch.

BEFORE footer crop у main capture спершу брав верх документа через page.clip; full-page містив footer. Це diagnostic issue. Додаткові locator footer screenshots + actual sidebar audit дали15/16 successful states і один separate successful retry — загалом16 states covered. Перша спроба одного додаткового font load мала transient NetworkError, збережений чесно; main BEFORE16 fonts loaded успішно. Desktop sidebar visible/mobile hidden підтверджено screenshots і окремим actual selector. Попередній порожній `[data-theory-sidebar]` запис не видається за доказ цього стану.

Справжній Ctrl+zoom **не підтверджений**: read-only keyboard probe на reference+3targets не змінив DPR/innerWidth. `zoom-probe-v2/manifest.json` фіксує це; viewport/DPR/CSS/page-scale emulation не підміняє browser zoom. Перший probe мав також selector timeout на закритому reference example; після уточнення visible selector всі4 reads завершилися без цієї помилки, масштабування лишилося непідтвердженим.

## Контент, джерела, metadata і БД

Збережено **6 details / 18 tasks / 35 required controls**, 19 sections/50 points, усі tables/cells/notes, task/control IDs, aliases/tokens/scoring/reset, UUID/anchors/levels/tags, bank links і metadata. Static fallback і course preservation не послаблювалися. Frozen10files, initial full46tables і 55config paths порівнюються після всіх acceptance calls. Для M43.1 потрібний фактичний read-only DB diff **0 updated / 0 inserted / 0 deleted**, не історичний M43 content diff.

Фінальні AFTER докази **PASS після всіх diagnostic GETs**:

| Незалежна перевірка | Результат / SHA-256 |
|---|---|
| `m43-1-final-v1.json` | Усі46 raw/protected tables, exact owners/banks/runtime; `0be3d9df8e663aeb767133f4b67d376c3c7171a047430e464722402961a8922f` |
| `db-equality-final-v1.json` | **0 updated /0 inserted /0 deleted**, full table equality; `812c967237a10ea1c49f1498360a380926e48ac97d47fff7fec52299886c7ef7` |
| `source-compare-v1.json` |52 518 paths;20 дозволених changes (10 ROOT+10 WT),**0 поза allowlist**; `650f1de4c227054e8a6cca6cfac3ca68dae65da315323bb53d956abfd92e7e36` |
| `frozen-after-v1.json` |10 frozen/definition files exact; `b4284a473516611f3f13519ec7ff63ebd3744203b0f15fb37a101e0040fe03b2` |
| `config-after-v1.json` |55 config/dependency/bootstrap/metadata paths exact; `e7dbc536dff7a2302291f437275eac9062c63ddf6948e97d3309d01f4f05e054` |
| `after-v1-http.json` |65/65 GET200, exact metadata/JSON-LD/62controls/robots/sitemap; `73f3babf2f4705e782283318db7c3cc511866f9e09ff6786678f2a82429fbc22` |
| `author-dom-after-v2.json` |444 lang fragments, notes та6 details exact; `636950a88a26b4bddda0fbfdf38fdb89d1464305e6d03891c543f07fed5b54d9` |
| `courses-guest-after-v1.json` |3 GET200; actual learner text present,0 M43 style/author markers; `b15075fc066d8ad4a490df6742287fed2a8e14defc581708af9b12a87788ac03` |
| `local-identity-after-v1.json` |Fresh local root/runtime/config agreement; `a16f6c4ec99cd750479e6e5d6f2be01a3a1fd570bd9bc08b025d1179b9125127` |

Raw flat `textContent` equality спершу **не пройшла**: перенесення examples у sibling blocks додало27 separator-whitespace positions (9/10/8). Це не приховано під raw equality PASS. Strengthened comparison вимагає exact444 lang snippets/notes/details, кожен non-whitespace codepoint/punctuation, case/word boundaries/order; **кожна** changed whitespace position мусить бути actual HTML block boundary. Ці перевірки пройшли. Original BEFORE/failed AFTER-v1 залишені immutable; author content/master/DB не виправлялися заради PASS.

HTTP control scope: 65 pages (3 targets+62 controls), robots/sitemap; exact H1/title/description/canonical/robots/OG/Twitter/JSON-LD, learner content/links, ordered sitemap. Контролі включають42 M42,3 M41,4 M26, EN/PL, курси трьох тем, category/test views. `.com` у canonical — existing metadata value, не production request.

Course coverage limitation: initial HTTP helper має theory-specific learner selector, тому три course lesson rows містять learner:null. Їхній HTTP/metadata regression перевіряється, але він не є доказом live course-learning DOM BEFORE→AFTER. Course learning byte-equivalence захищено окремим isolated M43CoursePreservationTest (2 tests/81 assertions); actual AFTER guest checks можуть підтвердити відсутність M43 style leakage, без вигаданого browser BEFORE.

Readable comparisons з актуальними fonts:

| Компонент | Reference BEFORE | M43 BEFORE | M43 final AFTER |
|---|---|---|---|
| Usage/example | `browser-before-v2/before-v2-reference-1440-light-section-2-1.png` | `browser-before-v2/before-v2-A-1440-light-section-3-1.png` | `browser-after-style-final-v1/after-style-final-v1-A-1440-light-section-3-1.png` |
| Forms/note | `browser-before-v2/before-v2-reference-1440-light-section-1-1.png` | `browser-before-v2/before-v2-A-1440-light-section-2-2.png` | `browser-after-style-final-v1/after-style-final-v1-A-1440-light-section-2-2.png` |
| Practice | `browser-before-v2/before-v2-reference-1440-light-section-5-1.png` | `browser-before-v2/before-v2-A-1440-light-section-7-1.png` | `browser-after-style-final-v1/after-style-final-v1-A-1440-light-section-7-1.png` |
| Dark/mobile practice | `browser-before-v2/before-v2-reference-390-dark-section-5-1.png` | `browser-before-v2/before-v2-A-390-dark-section-7-1.png` | `browser-after-style-final-v1/after-style-final-v1-A-390-dark-section-7-1.png` |

Усі назви відносні до приватного evidence ROOT вище; screenshots не комітяться. Для table/summary/mistake/open details/feedback є окремі readable slices у кожному state manifest. MAIN переглянув також final C desktop-light practice, A mobile-dark practice, A note crop, B mobile-dark rules і green feedback. Peer review cumulative **290 PNGs** (усі mobile states та desktop-dark sections/practice/footer, latest feedback/notes), primary browser reviewer додатково48 desktop-light та representative final screenshots. Нерозбірливий resized full-page PNG не був єдиною візуальною перевіркою.

## Ізольовані тести

CWD — WT. PHP `C:/Program Files/xampp/php/php.exe`8.5.10; PHPUnit12.5.35. Runner `tools/diagnostics/run-isolated-tests.py` використовує SQLite memory, owned runtime/storage і array cache/session; working MySQL/progress не використовуються.

| Окремий run | Фактичний результат |
|---|---|
| Initial helper unit v1 | 3 tests/156 assertions PASS; до додаткових presentation cases |
| Native regressions v1 | **368 tests / 28 913 assertions, exit0**, 1 deprecation, protected46 661 / changes0; M43/M26/M41/M42/native/sidebar |
| Targeted rendering v2 | **30 tests / 4 905 assertions, exit0**, 1 deprecation, protected46 661 / changes0; після markup spacing/header змін |
| Node regression v1 | **659 contracts, exit0**; окремий pre-followup run |
| Node v2 | **659/659, exit0**, retained `storage/app/seo-m43-1-local/node-final-v2.log` |
| Feedback/numbered rendering v3 | **17 tests /3 721 assertions, exit0**,1 deprecation, protected46 661/changes0 |
| Final Node v3 | **659/659, exit0**,0 skipped; log SHA `d7b24c6c78d1dbc90bffc8f49527770db76b5619d664f76f8fa71a420760f3bf` |
| Final Node v4 після control utilities | **659/659, exit0**,0 skipped; log SHA `d19eee50797e81b310139eccb10f350b06c81505ef9e720cb2506c170e48bba2` |
| Vitest v1 | **61/61 у8files, exit0**, 0failed/pending; SHA `746323027d7d7476b639ef66a0e9d3dc487fff66ec227b3fe5e339784af6f8da` |

Runs не складаються в вигаданий total. Старі rejected color/mono/italic assertions не потрібні для PASS — existing M43 tests їх не вимагали. Fidelity/guard/fallback/scoring/security/course assertions збережено; новий unit перевіряє literal escaping, exact languages/notes, усі6 stored details/18 keys/18 prompt headings і відсутність author-text/source mutations.

Історичні baseline fixture failures, описані в M43 (no-op mutation у TheorySectionRenderingTest та legacy richFields expectation Present Perfect vs Past Simple), не виправлялися в presentation-only scope. Вони не оголошуються новими M43.1 failures або частиною PASS невиконаного full-project test run.

```powershell
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m431-native-regressions-v1 tests/Unit/M431NativePresentationTest.php tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php tests/Feature/M26PointDetailsTest.php tests/Feature/M41ExistingDesignPackageTest.php tests/Feature/M42NativeDesignPackageTest.php tests/Feature/M42DesignEvidenceTest.php tests/Feature/UnifiedTheoryNativePresentationTest.php tests/Feature/TheorySidebarPresentationTest.php
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m431-final-rendering-v2 tests/Unit/M431NativePresentationTest.php tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php tests/Feature/M41ExistingDesignPackageTest.php tests/Feature/M41AuthorFidelityTest.php
node --test --test-reporter=spec --test-reporter-destination=storage/app/seo-m43-1-local/node-final-v2.log tests/Browser/m41-practice-ui.test.cjs tests/Browser/m41-design-http.test.cjs tests/Browser/m42-native-style-policy.test.cjs tests/Browser/m42-design-http.test.cjs tests/Browser/seo-m43-local.test.cjs tests/Browser/m43-practice-ui.test.cjs
node node_modules/vitest/vitest.mjs run --reporter=json --outputFile=storage/app/seo-m43-1-local/vitest-v1.json --maxWorkers=1 --minWorkers=1
```

Final live commands (exclusive private directories; логічний V2 і bounded final styles — різні runs):

```powershell
node tools/diagnostics/capture-m43-1-reference.cjs D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local/browser-after-final-v2 after-final-v2
node tools/diagnostics/capture-m43-1-reference.cjs D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local/browser-after-style-final-v1 after-style-final-v1
node tools/diagnostics/capture-m43-1-reference.cjs D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m43-1-local/browser-supplemental-final-v1 supplemental-final-v1 --supplemental
python tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m431-final-feedback-v3 tests/Unit/M431NativePresentationTest.php tests/Feature/M43AuthorFidelityTest.php tests/Feature/M43CoursePreservationTest.php tests/Feature/M41AuthorFidelityTest.php
node tools/diagnostics/capture-m43-1-http.cjs after-v1 before-v1-http.json
node tools/diagnostics/capture-m43-1-author-dom.cjs after-v2 author-dom-before-v1.json
php -d opcache.enable_cli=0 tools/diagnostics/inspect-m43-1-working-local.php m43-1-final-v1.json
node tools/diagnostics/verify-m43-1-invariants.cjs db-compare m43-1-before-v1.json m43-1-final-v1.json final-v1
```

Фактичний `python`: `C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe`. Node22.15 `C:/Program Files/nodejs/node.exe`; NODE_PATH bundled dependencies. PHP runner додає do-not-cache-result/colors-never/JUnit в isolated owned runtime. Exclusive diagnostic labels для повтору змінюються; старі докази не перезаписуються.

## Git handoff

У Git входять тільки10 presentation files, meaningful unit/diagnostic tooling і цей новий звіт. Перед commit перевірено explicit26-file staged scope, diff-check і незмінність frozen sources/global CSS. Приватні screenshots, runtime/physical/DB proofs, source backups, `.env`, caches, vendor/build і сторонні dirty changes виключені. Implementation SHA визначається `git log -1 --format=%H -- docs/reports/seo-m43-1-ppc-reference-design.md`; full SHA й normal-push remote equality повідомляються у фінальному handoff. Main/production/PR/force push не виконувалися.
