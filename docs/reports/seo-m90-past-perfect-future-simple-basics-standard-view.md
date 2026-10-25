# M90 — Past Perfect / Future Simple: standard view

Локальна реалізація всіх шести уроків завершена. Контент, власна практика й окремі конструктори застосовані та перевірені на `http://gramlyze.loc`.

Загальний historical regression gate **не зелений**: нижче окремо наведені нові source-guard FAIL, старі FAIL та буквальні HTTP-відмінності. Вони не приховані нормалізацією або зміною тестів.

## Шість сторінок

| Урок | Реальна сторінка | SQL practice level | Display / own focus | Питання окремого widget |
|---|---|---|---|---|
| A — Past Perfect: Forms and Use | [Відкрити](http://gramlyze.loc/theory/tenses/past-perfect/past-perfect-forms) | NULL | A2–B1 / A2 | 23115, 23116, 23119, 23120, 23125 |
| B — Past Perfect: Negatives | [Відкрити](http://gramlyze.loc/theory/tenses/past-perfect/past-perfect-negatives) | NULL | A2–B1 / A2 | 23187, 23189, 23191, 23192, 23194 |
| C — Past Perfect: Questions and Short Answers | [Відкрити](http://gramlyze.loc/theory/tenses/past-perfect/past-perfect-questions) | NULL | A2–B1 / A2 | 23257, 23259, 23262, 23264, 23265 |
| D — Past Perfect: Time Expressions | [Відкрити](http://gramlyze.loc/theory/tenses/past-perfect/past-perfect-time-expressions) | NULL | A2–B1 / A2 | 23319, 23327, 23331, 23339, 23354 |
| E — Future Simple: Forms and Use | [Відкрити](http://gramlyze.loc/theory/maibutni-formy/future-simple/future-simple-forms) | NULL | A1–A2 / A1 | 53604, 53608, 53611, 53613, 53616 |
| F — Future Simple: Negatives | [Відкрити](http://gramlyze.loc/theory/maibutni-formy/future-simple/future-simple-negatives) | NULL | A1–A2 / A1 | 53745, 53747, 53750, 53751, 53753 |

Display range, редакційний фокус і технічний NULL не ототожнювалися. Старі тематичні банки залишилися all-levels: по 72 питання. Рівні вибраних widgets: A/B — по п’ять A2; C — один A1 і чотири A2; D — два A1, два A2, один B1; E — три A1, два A2; F — п’ять A1. Це finite selection, не повне покриття рівнів або курсу.

## Контент і практика

- Збережено 42 наявні native block positions і 6 subtitle positions. На кожній сторінці продовжено один існуючий practice-set: source index 5 / sort order 6 / left / NULL. Нових блоків практики не вставляли.
- 36 graded controls: 24 старі збережено/уточнено, 12 нових перевіряють зміст. Кожна сторінка має 2 form + 2 meaning + 2 full-sentence inputs.
- Усі 12 повних inputs працюють вручну й токенами. Перемішані групи містять 1–3 слова; видимі умови визначають потрібну форму, лексику та порядок. Відсутні старі task IDs/source indices не вигадані.
- Збережено канонічні відповіді всіх 24 старих controls і всі 26 їхніх вихідних declared answers; у F збережено також порядок старих full/contracted accepted arrays.
- Перевірено 42 declared accepted answers і 52 displayed options. Окремий native widget кожного уроку містить п’ять перевірених existing type-4 питань, а не замінює власні inputs. Нових DB questions не створювали.
- Шість paired mistake notes у E/F видимі разом із wrong/right EN, обома українськими перекладами та поясненням. 21 comparison row має переклад.
- Нуль нових point-level «Докладніше» — явне семантичне рішення шести авторських частин, не квота й не runtime word threshold. Корисні короткі пояснення залишені у visible basic.
- Назви вправ не містять «поліглот». Немає raw HTML/службових маркерів у перевіреному видимому навчальному тексті.
- Виправлено лише href-дані 26 наявних footer entries, зберігши labels/order/current: 20 links і 6 current spans у native render. Стара short-навігація перевірена окремо; resolver/routes не змінювалися.

Past Perfect пояснюється через минулу точку відліку, ранішу дію/результат/стан; before/after не оголошуються механічним правилом, V2=V3 не оголошується помилкою. Розрізнено результат і неназваний додатковий факт, unfinished і unstarted, нейтральне питання і твердження, by і until. Для will збережено спонтанне рішення, прогноз, обіцянку, пропозицію, відмову; прогноз не перетворено на гарантію, пропозицію — на прийняту домовленість.

Повний текст, task ledger, primary-source access classes і 43 finite coverage rows збережені в versioned пакеті. Зокрема D-widget не заявляється покриттям усіх восьми часових маркерів: частина нюансів залишається theory-only або own-practice-only.

## Канонічний дизайн і межі

Не змінені Blade/native views, CSS, typography, formatter, grader, practice engine або глобальні пороги detail. У shared renderer додано лише finite registration із наведених нижче 12 рядків; алгоритми рендерингу й дизайн не змінювалися. 326 visual files і 3001 раніше frozen ROOT artifacts залишилися byte-exact.

Додано 5 тонких M90-класів і рівно 12 additive registrations у 8 shared files. Для двох фактичних ROOT/WT контекстів окремо перевірено raw/LF/EOL profiles: видалення лише цих 12 рядків відновлює повні незалежні BEFORE bytes. Сторонні відмінності ROOT/WT та 25 старих untracked files збережено.

Погоджений M89 `complete-loaded-verb-hints-v1` presenter повторно використано **без зміни будь-якого байта**. Loaded hint snapshots і separate hint options зв’язані точно; вони не додаються до English token BANK. Курси, EN/PL, standalone/saved tests і прогрес не отримали нових content writes.

## Guarded local apply

Fresh M90 source/SQL/HTTP BEFORE не запозичувався з M89. Виконано physical + web/CLI proof, fresh preview, незалежний exact body diff, exclusive backup, transactional apply, postconditions та два no-op: з вихідного plan і fresh AFTER plan.

Фактичний apply: **44 body updates = 42 teaching + 2 subtitles**, по owners **8/8/7/7/7/7**. Число 48 означає protected continuing UK positions, а не 48 UPDATE. Нуль inserts/deletes, Page writes, question-family writes і progress writes.

Після apply та повторно після браузера перевірено всі 46 таблиць/схеми, 48 UK і 96 EN/PL continuing rows, старі IDs/UUIDs/metadata/order/timestamps, 888 старих питань із повними зв’язками та 13 saved pools. Нормалізація тільки погоджених 44 body-змін повертає незалежний BEFORE; решта даних точна.

Тимчасовий read-only nonce route був тільки local loopback GET. Його видалено після apply/no-op; routes file відновлений byte-exact, окремий anonymous GET підтвердив 404. Apache/XAMPP/hosts/config/ACL не змінювалися. Backups, nonce/runtime identity, сирі SQL/HTTP captures, screenshot evidence і приватні діагностики не включаються в Git.

## Автоматичні перевірки

| Перевірка | Фактичний результат |
|---|---|
| M90 target, ROOT factory source | v1 і post-UI v2: 55 methods / 22 733 assertions / 0F / 0E кожного разу |
| M90 target, WT factory source | v1 і post-UI v2: ті самі 55 / 22 733 / 0F / 0E |
| Ізоляція target runs | ROOT Composer/Laravel testing runtime; SQLite memory; array cache/session; CLI OPcache off; 675 protected paths незмінні; WT app bootstrap не заявляється |
| Native own-practice replay | Обидва реальні незмінені factory sources: 36 controls, 12 full inputs, 42 aliases, 52 options, 12 wrong-context cases |
| Native selected-widget replay | 30 existing моделей у двох source contexts; canonical і selected-visible text точні, empty rejected, recheck stable, lexical negatives не зараховані |
| Actual targeted registry | 6/6, exit 0, stderr empty, 0 writes |
| Actual complete registry | 235/235, exit 0; старі 229 semantic rows/key order точні, виключено лише whole HTTP SHA; new-six fields/IDs/UUIDs/NULL72 exact |
| Registry DB guards | Свіжі 46-table/schema scans до, між і після обох команд точні |

### Незмінені historical suites: strict FAIL збережено

Fresh BEFORE: 20 profiles, 1081 method executions, 988 unique methods, 93 overlap executions, 222 031 assertions, **66F / 1E**.

Обидва AFTER runs: ті самі methods/populations, 221 517 assertions, **70F / 1E**, 0 skipped, 1 warning, 20 deprecations. V1→V2 method maps/counts/issue tails точні. Preflight paths та interaction stderr не оголошуються повністю byte-identical.

Чотири **нові**, а не історичні, FAIL належать `M89SourceBoundaryTest`:

- `test_attributes_keep_root_absent_and_worktree_exact_prefix_or_explicit_reviewed_suffix`
- `test_authorized_hint_presenter_delta_is_exact_and_reverses_to_both_independent_before_profiles`
- `test_exact_twelve_nonvisual_registrations_reverse_to_independent_lf_before_on_each_profile`
- `test_native_validator_formatter_and_grading_engine_keep_their_independent_raw_bytes`

Усі зупиняються на незміненому line-72 controller-profile assertion: старий M89 AFTER hash уже не є поточним M90 AFTER hash. Незалежний byte audit підтверджує, що M89 expected hashes дорівнюють fresh M90 BEFORE, а поточні hashes — точним reviewed M90 AFTER profiles. Це attribution, **не waiver і не PASS**; M89 helper/native bytes залишилися незмінними.

Окремо зафіксовано змінені literal failure bodies чотирьох уже failing M77/M73/M74 source guards та assertion-count growth двох still-passing catalogue/registry methods. Старі тести, pins, методи й критерії не послаблювалися; жодного пропущеного/доданого/resolved method немає.

## HTTP / SSR / старі споживачі

Шість target HTML bodies отримано одним проходом; усі HTTP200. З цих самих bytes перевірено H1/title/description/breadcrumb/canonical/robots meta, LearningResource та 26 footer entries, 36 own controls і 30 exact widget snapshots. Шість окремих HEAD підтвердили local noindex headers; це не повторні GET. Production canonical strings спостерігалися лише в локальній metadata, без production requests.

51 старий consumer URL: **20 complete entries exact / 31 strict FAIL-retained**. Усі 31 відмінності — тільки body SHA, причина не встановлена. 44/44 nonempty keyed question payloads, 31/31 nonempty HTML facts та 6/6 course learning DOM точні. Status/route/kind/count fields — 51/51 exact. Залишився старий `/test/past-perfect/questions` requested-SSR → actual-JSON HTTP404: його exact entry не робить endpoint accepted.

25 окремих nontarget controls: усі HTTP200, метадані/links/anchors/category descriptions точні. Literal complete-entry 0/25 та raw-body 0/25; bookkeeping filenames не виключалися. Main DOM **23/25**, nonempty serialized question JSON **19/21** exact. У двох сусідніх, не включених до M90, сторінок змінився лише один serialized `questions:[...]` рядок main region:

| Control | BEFORE visible IDs | AFTER visible IDs |
|---|---|---|
| Future Simple Questions | 55640, 55546, 53762, 53898, 54041 | 55770, 55617, 55562, 53751, 53762 |
| Future Simple Time Expressions | 55566, 55564, 53619, 55779, 55637 | 54054, 53765, 54039, 55567, 55564 |

Решта bytes цих main regions збігається після вилучення лише цього рядка — це окремий факт attribution, не перетворення strict FAIL на PASS. Причина вибору старих payloads не доведена. Повторів заради збігу, очищення cookies/progress або ремонту сусідніх сторінок не виконували. Усі 46 SQL tables/schema та canonical sources залишилися точними.

## Справжній браузер

На всіх шести уроках виконано:

- 48 distinct nominal cases: 24 manual + 24 token cases на 1440×1000 та 320×844.
- 52 unique displayed-option checks; canonical/full/contracted accepted variants; empty/whitespace rejection, delete→token return, mixed typing/tokens, recheck та reset усіх шести груп без скидання сусідніх scores.
- 12 wrong-context answers дали 0/2 по дві на урок; після редагування canonical відповіді дали 2/2.
- Усі 30 exact selected widget questions видимо склалися до перевіреного settled English text: шість разів 5/5. Повторний `I` у `I promise I will listen.` також пройшов.
- Practice anchors та actual reload усіх шести сторінок; збережений completed widget state не очищався.
- 36 станів: 1440×1000, 390×844, 320×844 × light/dark × 6 pages. Збережено й окремо переглянуто всі 36 fresh v2 screenshots; raster і theme відповідають кожному стану.

У перевірених content boxes поза native table scrollers: 0 виходів за viewport і 0 зафіксованих text-overflow boxes. **Глобально чистий overflow не заявляється:** у 24 станах document width перевищував viewport; точні значення залишені у private evidence, причина не приписується без доказу. Mobile comparison tables мають native local horizontal scroll; у реальному 320px сценарії клавіатурою досягнуто scrollLeft=max324. Це не твердження, що всі стовпці видно одночасно.

Попередні v1 screenshot raster/theme limitations збережені; вони не підмінені v2 без сліду. Print, no-JS і WCAG certification не заявляються. Дизайн не змінювався для «озеленення» перевірок.

## Versioned пакет і джерела

- [Авторська редакція](../content/m90-past-perfect-future-simple-basics.v1.0.0.uk.md)
- [Frozen master](../content/m90-past-perfect-future-simple-basics.v1.json)
- [Task ledger](../content/m90-past-perfect-future-simple-basics.tasks.v1.json)
- [Finite coverage](../content/m90-past-perfect-future-simple-basics.coverage.v1.json)
- [Bank audit](../content/m90-sentence-builder-bank-audit.v1.json)
- [Raw source checksums](../content/m90-source-checksums.v1.json)

Primary reference bodies включають [British Council: Past perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/past-perfect), [will and would](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/will-would) та [Future forms](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/future-forms-will-be-going-present-continuous). Власні приклади й умови не подаються як копії вправ джерела. У повному source catalogue окремо відзначені прямий body access, indexed excerpts і недоступні сторінки; для blocked body не заявлено direct reading або незалежний human signoff.

Основні opaque receipts (private files не комітяться):

| Evidence | SHA256 |
|---|---|
| Frozen source plan | `900f77d7838847e3a662c0b702319d76ff2435ac0fda03d8f5f9ed305b2ca87e` |
| Post-UI SQL/source reconstruction | `bdb37e2cf70f7fd3238a3dcf6669317191a944f1f872a736e6c3687e0d82e317` |
| Actual UI aggregate | `3e82983806f2f29c9a8af01b29b6a9d9004824d9183fbbd83a75843ecddbb0c9` |
| Review of all36 fresh screenshots | `c7054022a23d1047a74e842de7a1dce8c0bf81577d28e8c842bf40683c038c8a` |
| Registry targeted + whole | `5140897f9b32acd538f40982a82b5f22e31552436f6bdc564f6330f29475ba59` |
| Strict second AFTER comparison | `9b33a3a20cd3427d4a798c7b08021d2a38a22036a4ebb0048cb7841b6104d5f2` |

## Доставка

База: `9607ebefbbf870f4b303bbc05a0c6c0ed0341a17` (accepted M89).
Робоча гілка: `codex/seo-m90-past-perfect-future-simple-basics-standard-view`.

Звіт входить до scoped commit разом лише з перевіреними M90 paths. Результат commit/normal push і remote HEAD перевіряється після формування звіту та повідомляється в підсумку доставки. Main, PR, merge/rebase, force push, workflow dispatch і deploy не входять у цю роботу. Production `.com`/`.ub` не перевірялися й не змінювалися.

Staged allowlist — 48 точних paths. Звичайний `git diff --cached --check` позначив лише чотири CR-at-EOL у нових registration lines історичного `TestJsV2Controller.php`: index і working copy обидва CRLF, чинний атрибут `-text` збережено. Перевірка з явно описаним профілем `git -c core.whitespace=cr-at-eol diff --cached --check` — exit 0; файли, глобальні Git settings та історичні EOL заради цього не змінювалися.

Working/served bytes і Git index перевірено окремо. Звичайний `git add` нормалізує CRLF до LF у `.gitattributes`, `TextBlockToQuestionsMatcherService.php` та `TheoryCourseManifestService.php`; semantic staged diff цих shared paths залишається лише двома registration additions. Історичний attributes prefix у staged diff незмінний. Це не raw-byte тотожність working copy/index і не окрема перевірка checkout на іншій платформі.
