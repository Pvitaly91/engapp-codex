# M45 — компактні порівняння майбутніх часів

## Scope та база

Робоча гілка: `codex/seo-m45-compact-future-comparisons`. Actual base: `e5b3ee339410afe3bd6b6bad33eb62fced4d6671` (актуальна спрощена M44). Ancestry retry-fix `d3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0` підтверджено exit 0. Reused attached worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`; фактичний served ROOT: `D:/DEV/htdocs/gramlyze.loc/public`. Сторонній dirty ROOT не очищався й не reset-ився.

Єдина HTTP/DB/browser ціль — `http://gramlyze.loc`, локальна MySQL `gr2:3306`. Main, production `.com/.ub`, PR, merge, deploy, dependencies/build, `.env`, hosts та Apache/XAMPP не є scope.

| Урок і local URL | Page ID | Identity suffix | Display / technical level | Details / tasks / controls |
|---|---:|---|---|---|
| [Future Perfect vs Future Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-continuous) | 60 | `FutureFormsFuturePerfectVsFutureContinuousTheorySeeder` | B1–B2 / B2 | 4 / 6 / 6 |
| [Future Perfect vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-perfect-vs-future-perfect-continuous) | 61 | `FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder` | B2 / B2 | 4 / 6 / 6 |
| [Future Continuous vs Future Perfect Continuous](http://gramlyze.loc/theory/maibutni-formy/future-continuous-vs-future-perfect-continuous) | 59 | `FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder` | B2 / B2 | 5 / 6 / 6 |

Повний identity prefix: `Database\Seeders\Page_V3\FutureForms\`. UK locale, category ID 12 / ancestry `maibutni-formy`, canonical destinations та IDs підтверджено штатним resolver, read-only inventory й реальними GET.

## Авторська редакція та компактність

Basic визначено в авторському master до проєкції: короткий контраст → форми → ситуації → обмеження → підсумок → практика. Немає runtime-переказу, word threshold, line-clamp, max-height, global overflow masking або загального «Показати урок».

На кожній сторінці **дві картки форм × три рядки**. Кожний рядок: формула, один англійський приклад, точний український переклад; приклади всередині форм без окремих вкладених рамок. Видимі groups A/B/C: 8/8/9; 13 semantic details прив’язані до конкретних власників. Основні застереження та переклади не заховано. `reason_uk` — явна editorial-meta причина рішення, не learner-параграф і не прихований дублікат.

Read-only дизайн-еталон: [PPC Forms](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms). Композиція: фінальна спрощена [Will / Be going to](http://gramlyze.loc/theory/maibutni-formy/future-simple/will-vs-be-going-to) та [Present Continuous for Future](http://gramlyze.loc/theory/maibutni-formy/present-continuous-for-future). Не відновлено початкові великі M44/M41/M43 panels; global CSS не змінено.

Editorial mapping збережено у `docs/content/m45-editorial-notes.v1.0.0.md`; complete readable edition — `m45-author-readable.v1.0.0.md`; point-to-detail mapping — `m45-presentation-mapping.v1.0.0.md`.

Ключові зміни:

- A: процес у майбутній момент проти попередньої дії/результату; Perfect не гарантує припинення ширшої діяльності. Старі at/by приклади розібрано за змістом, а `will be finish` — як помилку форми.
- B: результат/кількість, стан або період проти процесу/тривалості. `will have studied for two hours` не оголошено неграматичним; study/work/live та know/been розмежовано. Саме been не дорівнює Continuous.
- C: одна часова ситуація з узгодженими годинами; planned period проти accumulated duration. For/how long не є автоматичним перемикачем; кінець процесу не домислюється.
- Пояснення, captions, instructions, notes та translations — UK; назви часів, формули та навчальні стимули — EN. Немає ready-English-order hint чи answer placeholder у reorder завданнях.

Мовний та окремий редакторський review виконані моделями, не зовнішньою сертифікацією. Видавничі вправи/приклади не копіювалися; learner comments не були нормативним джерелом. Direct British Council editorial reading та Cambridge 403/indexed-only доступ розрізнено по actual URLs у editorial notes. Контекстні висновки зі study/work/live позначено як авторський перенос аспектних принципів.

## Freeze та незалежна fidelity

Версія `1.0.0`, UTF-8 без BOM, LF, один кінцевий LF; exact path `.gitattributes` захищає frozen bytes та три цільові definitions/candidate inputs від CRLF normalization.

| Frozen source | SHA-256 |
|---|---|
| Author master | `333b39ead3e5bb20c2bcde9c42878cf98f176158d7ae6324d0c6510b09b2afb7` |
| Independent BEFORE | `7f9b208f9e52dc566556e5ca41de121af8ca2fada2c3c055bc9252b517caa9c0` |
| Pure projection | `209a0bdb62716eb5cd35718b0496d31eda6616f951c6a14c94f087092dc8ca19` |

Exact reviewed drafts → master equality, 757 independent expected fields та 13 explicit editorial-meta reasons перевірено до served apply. Token multisets/declared aliases перевірено незалежно. `m45-checksums.v1.0.0.json` і `m45-canonical-checksums.v1.0.0.json` зберігають повний ланцюг, а не очікування, зняті з AFTER.

M45 opt-in окремий: caller + повна UK collection + owner/UUID/order/type/column/heading/level/body та exact frozen package. M44 IDs/registries не розширювалися. Cache перевіряє bytes до повернення memoized JSON. Відхилені дані мають повний escaped static fallback, включно з formulas/options/hero; arbitrary view names не беруться з БД. Course models не мутуються, exact BEFORE blocks повертаються cloned/sorted; новий practice block у course не додається.

## Дані, банки та source sync

Independent DB BEFORE: `m45-before-v1.json`, SHA `2aa704d62c3e24419bb9b7ef99327e442286f1d9cc830cc851f900fa19aa15c9`; 46 фактичних таблиць. На кожній цілі два own-linked banks: 72 type `0` та 72 type `4`, linked IDs == global IDs, A1–C2. Нові 18 вправ — лише `practice-set`; Questions, answers/options, verb_hint, pivots, saved tests та реальний прогрес не є write scope.

Full managed-source BEFORE: 53,739 union paths, ROOT 6,549 existing / WT 53,711; protected env/runtime config — digest-only. SHA `f050d33fc844dad01125bd6268836836c8b8bf177bef37a8a7ebeed21c4a59e3`. Private byte-exact backups shared4 + definitions3 створено до sync. Чотири shared files перенесено **8 exact reviewed hunks**, не wholesale copy; ROOT-only bytes/EOL у theory/show та course partial збережено. Exact sync proposal: 20 files, SHA `1abb3c686906be6df2d2a6d10ec10ce0df5203eeade44e88e0d5295e6b691fd5`.

## Перевірки, виконані до apply

- Guard-only isolated run: `run-isolated-tests.py --label m45-target-guard-v1 tests/Feature/M45LocalTargetGuardTest.php` — exit 0, 67 tests, protected 46,661 files: 0 changes; shared PHPUnit deprecation попередження збережено.
- Focused new tests v2: `run-isolated-tests.py --label m45-fidelity-patch-v2 tests/Feature/M45FutureComparisonsTest.php tests/Feature/M45ContentPatchTest.php` — exit 0, 12 tests, 2,164 assertions; protected files 0 changes. Final title/fidelity run `--label m45-final-title-fidelity-v3` з тими самими двома files — exit 0, 13 tests, 2,197 assertions, protected files 0 changes; доданий case перевіряє реальну controller title-localization гілку та foreign-owner/locale/raw-metadata negatives.
- Earlier broad run `m45-fidelity-patch-regression-v1`: 302 tests, exit 2. П’ять new fixture setup errors (owned-path assertion перед mkdir) та один new harness false positive (legitimate native SVG icon) виправлено тільки в нових M45 tests і повторно перевірено. Один unchanged historical `TheorySectionRenderingTest` case #7 залишено: mutation `Past Simple → Past Changed` у вже іншому fixture content є no-op; історичний опис є у M43.1 report. Старий test не видалено/не послаблено; broad run не оголошено PASS.
- Node final command: `node --test tests/Browser/m45-practice-ui.test.cjs tests/Browser/m44-practice-ui.test.cjs tests/Browser/manual-answer-retry.test.cjs tests/Browser/answer-punctuation.test.cjs` — exit 0, 144 tests у фінальному одному run. Власна M45 група — 13 cases, зокрема replay всіх 18 tasks/aliases/tokens/retry/reset, standalone `?` initial/partial/clear/reset/build та attached-question tracking/history. Попередні runs 142/143 і повторні focused runs не додаються до total.
- Vitest final command: `node node_modules/vitest/vitest.mjs run tests/JS/theorySections.test.js tests/JS/unifiedTheoryDesign.test.js tests/JS/theorySidebarLayout.test.js tests/JS/theoryNavigation.test.js` — exit 0, 4 files / 37 tests. Окремий run із `publicAssets.test.js` перервано через тривалу in-memory Tailwind compilation, exit 1; він не PASS, build artifacts не створювалися.

Course live BEFORE отримано до source sync/DB apply: 6/6 GET states, 33 PNG, JS guest lock збережено, no-JS fallback видимий без заяви про unlock. Theory BEFORE — 12 M45 states плюс 12 окремих reference states, усі PASS; Manrope/Archivo faces завантажені. Representative glyph probes не видаються за повний glyph-font proof.

Original reference role coverage не включало dedicated M44 compact-row formula/en/uk samples; generic forms-formula був H3, не row formula. Raw BEFORE theory-height sums також включали practice: коректна theory-only сума виводиться зі збережених section IDs/rectangles без перезапису evidence. AFTER reference calibration та geometry будуть звірені з immutable BEFORE, не з очікуваннями власного AFTER.

## Застосування та live acceptance

Fresh physical vhost/DNS/listener/Web–CLI proof: ROOT/public, localhost gr2:3306, APP environment production / HTTP SiteMode development. Config/DNS/hosts/Apache не редагувалися. Nonce GET routes були exact-host/loopback-only, без секретів/DB writes, з explicit HEAD denial; після перевірок кожний видалено, `routes/api.php` byte-exact SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, live nonce GET 404.

Fresh DB inventory v2 SHA `274c28596d0bcddfbd7489160c51a9bbfe3a6f16ee55807928415d796a92fe67`. Preview `bdc6a2bce56a4b4014941f5cbe60aee49f7945ca849408fed2fb6fe0aa38f979`; independent exact field review PASS перед записом. Exclusive private backup `storage/app/seo-m45-local/m45-backup-v1.json`, не комітиться.

Transactional apply: **27 updated / 3 inserted / 0 deleted** — 3 `pages.text`, 24 existing UK `text_blocks.type/body` (type фактично збережений), три deterministic UK practice blocks. IDs/UUIDs/levels/titles/slugs/category/locales/tags/relations та timestamps старих rows збережено. Postconditions all 46 actual tables PASS; repeated original-plan no-op **0/0/0**, unused backup не створювався. Після technical code corrections також виконані fresh AFTER zero-diff previews/source-bound no-ops без DB updates.

Independent snapshot verifier `node tools/diagnostics/verify-m45-state.cjs m45-after-noop-v1.json state-verification-v1.json` — exit 0, SHA `c921438886dae222cd80d51f11f0888c2bb12fa9355441034b16449bf312a56c`: initial/fresh BEFORE всі 46 raw tables exact; після apply raw changes лише pages/text_blocks, protected data всіх 46 exact, reviewed updates/inserts accounted, banks IDs/types/levels exact, old timestamps unchanged.

Live checks знайшли та адресно виправили дві native integration omissions: plain UK subtitle був сприйнятий legacy controller як назва, а compatibility subtitle view не показував його в main header. Frozen author/master/source не переписувалися: finite UK/owner/raw-type+slug+title+text `displayTitle` та один Page-only controller hook повернули попередні H1/names; guarded native subtitle paragraph показав уже схвалений текст. Кожний technical source follow-up мав exact hashes/hunks та exclusive private backups. Final source scope — 21 files / п’ять shared files, загалом 10 finite hunks із збереженням ROOT-only bytes.

Metadata expectations визначено **до apply** через чинний view/service/middleware precedence: hero intro перший, а не Page subtitle. Private v1 помилково припускав subtitle precedence та був superseded ще до DB apply; final v2 SHA `36a50d4fa0f0548c56537af939f060c8c6e9a4d846c1feb59201f891dd6597fb`. Дозволені зміни — description/ogDescription/twitterDescription/LearningResource.description; H1, name/headline, canonical, breadcrumbs, URL set sitemap та noindex збережено.

Actual UI supplemental QA виявив M45-only punctuation-token bug: shared `usedTokens` помилково зіставляв пустий рядок із bare `?`, залишаючи кнопку disabled після clear/reset. M45 wrapper повертає empty Set лише для empty/whitespace answers. Додаткова typed-input перевірка виявила active bare `?` після `airport?`: тепер лише bare-question instances зіставляються з literal counts у history order; слова, periods/`p.m.`, contractions та grader залишені незмінними. Frozen tasks/aliases/tokens та shared JS не змінено. Final wrapper SHA `807649a2eff21f8212ee82ddabc60c096a18d5461a155b4157dfcde86fab929d`; reviewed sync receipt `d4b975c8613f902c9f9d8829e4d3658f9436623fadfa319b3f96d5f484065a3f`.

Невдалі pilots/broad runs не приховані: H1/subtitle defects зупинили відповідні checks до UI; Chrome 155 closed-details має clientRects, але `checkVisibility() === false`, тому geometric visibility expectation виправлено на native paint visibility без втрати content/open-state assertions. Diagnostic role mapping прозорого M44 outer wrapper та PPC hidden detail paragraph не видається за стиль видимої картки: final comparisons мають independent visible semantic origins, включно з PPC outer surface + inner p4 padding. EN visible usage має weight 500, тому нормалізації до помилково виміряних 400 не робили.

Final-source AFTER preview `4615ea9b5910e59b264835519e442e8c50992a5303f8e2f5aef69dee6f5430e9` після всіх technical fixes — **0 updated / 0 inserted / 0 deleted**, повторний apply також **no-op 0/0/0**. Final live read-only DB snapshot SHA `d360199b298a94d257d5f421230914b51d3fc20f91a601daf83369a72206c194`; незалежний final verifier `state-verification-v3.json` SHA `5fa246148801b4291c687e005b9e8812cb7046f06001213b6c8433315452c711`, exit 0: усі 46 protected tables unchanged, лише reviewed 27/3/0 та ті самі банки. Final nonce видалено, GET404, API bytes exact.

## Definitive live acceptance та обмеження

Команди (Chrome 155, site/fonts GET only, client-only ephemeral contexts):

```text
node tools/diagnostics/capture-m45-theory.cjs --after theory-after-v4 theory-before-v1
node tools/diagnostics/capture-m45-theory.cjs --supplement theory-supplement-v4 theory-before-v1
node tools/diagnostics/capture-m45-courses.cjs --after course-after-v1 course-before-v1
```

Усі завершилися exit 0. Final definitive matrix — **12/12 M45 states** та окремі **12/12 unchanged reference states**, SHA `c0f11c49ae8aa3d8b47e7f84b28494e0e532b34e884dee38474f46ad981c3c0f`. Supplement — **6/6** (3 no-JS + 3 width320), SHA `db8f963b0dfd2448e405c906de90c328ae0cc3fc78da0acd6a801bc44239bf87`. Prior successful v3 retained separately; runs не сумуються у coverage total.

Повний frozen-master DOM basic/detail mapping перевірено закритим, відкритим та після reload; titles/H1/subtitle/hero/metadata/JSON-LD/7 original anchors per page exact. Усі 18 unique tasks виконано actual clicks/fill/keyboard на desktop/light; 9 повторних viewport/theme smokes записано окремо, не як додаткові unique tasks. Додатково 26 alias/curly/spacing attempts, empty/partial/wrong→wrong→edit→correct, stale red disappearance, score без подвоєння, all-correct6 та reset0. Token banks A/B/C 10/14/9 instances, duplicate instances і `?` повертаються після clear/reset та повторно складаються клавіатурою.

Усі 13 details ×4 states мають незалежні Enter/Space toggles, deep links у parent disclosure, print expansion/restored prior state та reload-closed fidelity. Normal-header scroll tiles дійшли до footer; 192 tiles — coverage evidence, не test total. Actual loaded fonts/visible glyph probes пройшли усі target states. Selected corresponding native CSS comparisons мають 0 differences, з explicit independent semantic origins; це не універсальний pixel-perfect proof усіх selector combinations.

No-JS зберігає теорію, умовні тексти, банки токенів та шість self-check keys на сторінку; автооцінювання без JS не заявляється. Очікувана CSP-disabled script diagnostic відокремлена від site error. На 320/390/1440 learning overflow — 0; decorative mobile overflow 0–7px зазначено окремо, без CSS masking.

| Урок | Closed theory desktop / mobile, px | Independent BEFORE desktop / mobile, px | Visible groups |
|---|---|---|---:|
| A | 2670.94 / 4091.69 | 3665.36 / 4839.70 | 8 |
| B | 2870.02 / 4354.77 | 3818.50 / 5312.25 | 8 |
| C | 3396.50 / 5464.92 | 3865.20 / 5465.94 | 9 |

Hero, practice та gaps виміряно окремо в private rows, не додано до theory-height. C mobile майже дорівнює старій неповній/comment-translation версії; для штучного відсотка шрифт/контент не зменшували. Corrected compact references theory-only: Will7703.36/10980.59, PC4664.14/6781.98, PPC3184.39/4135.44. Дві картки форм, видимі потрібні застереження, відсутність nested example frames і повна доступність поглиблень — основа compactness verdict, не лише відсоток висоти.

Обсяг visible author basic slots1–5 (без details, UI numbers, hero і practice): A531 / B554 / C741 Unicode letter/number tokens, включно з формулами/прикладами/перекладами; 3269/3413/4384 Unicode characters. Hero+subtitle окремо33/41/33 tokens. Це descriptive master-derived measurement після exact DOM fidelity, не runtime threshold і не правило скорочення.

Окремі final geometry measurements desktop/mobile, px: hero A247.27/327.58, B271.27/446.97, C313.73/390.97; practice container (включно з own-bank widget) A3124.83/4228.06, B3027.58/3914.31, C3103.83/4297.56. Сума чотирьох проміжків між п’ятьма theory cards —96px у кожному стані. Ці величини не додаються до theory-only у таблиці.

Course AFTER6/6 відповідає independent live BEFORE: metadata/learner text/sections/tables/links/anchors/markers/access status exact. Guest JS gate не обходився; no-JS public fallback не названо unlock. Isolated clone tests також перевіряють input-model immutability, reversed order та invalid collection rejection.

Broader HTTP — 74/74 GET200, 73 rows мають usable independent BEFORE. `maibutni-formy` BEFORE timeout45s / AFTER200 — без вигаданого live before/after equality. Robots/sitemap URL set/noindex exact. Три UK M43 theory routes прямо не включені до цього HTTP набору (їхні EN/PL/course/test controls включені); для M43 збережено full source/DB/isolated proofs, а не вигадане пряме HTTP покриття.

Обмеження: реальний Ctrl/browser zoom та DPR2 не перевірялися; viewport/DPR1 не видаються за zoom. Описаний historical PHPUnit fixture failure та interrupted publicAssets run залишені відкритими поза scope; нових M45 failures після final corrections немає. Успадкований слабкий contrast pale B2 badge у dark повторює read-only M44 еталон; палітру не редизайнили. Private screenshots/proofs/backups/runtime caches не комітяться.

## Фінальний source inventory і Git

Full final source AFTER SHA `1a13404f2a91cf541a8bd425206371ce8f73a0bff8249abdea5fb87f601116d4`: 53,754 union paths, ROOT6,562 / WT53,726 existing. Exact literal allowlist — ROOT21 / WT52. Comparison SHA `0685135629ade7f013bfbfd7341e86e70e16b45bea7b90248673bb67c39a0b01`, exit0/PASS: 52 before/after change records, **0 unapproved**, **0 protected env/runtime/config changes**. Відсутні сторонні ROOT файли не відновлювалися, dirty/deleted user work не reset/clean-илися. Final service EOF normalization видалила лише один зайвий blank line; executable/content logic не змінювалася й receipt оновлено з private backup.

Main переглянув actual A form PNG; додатковий reviewer —20 B/C PNG (8 form states, closed sections, normal-scroll/footer, composition thumbnails). Overall visual review: A/B98 saved PNG, C43 saved PNG — усі12states мають section1–5/top/footer review, representative normal-scroll/practice/detail snapshots. Review sets overlap, їх не сумують у вигаданий unique total чи заяву про перегляд усіх PNG. Відсутній new visual blocker; full-page thumbnails не видаються за prose-level reading. V4 додає actual typed-question bookkeeping checks; content/geometry не змінювалися. Private media не входять до Git.

Scoped staging — лише52 declared paths; staged path set exact, private paths0. Frozen source/checksum та canonical definition Git blob bytes:10 hash-bound files PASS; new files UTF-8/noBOM/LF/one final LF PASS, `git diff --cached --check` exit0. Normal push лише робочої гілки, remote SHA==HEAD перевіряється після push. Цей звіт не дозволяє main update, PR або deploy.
