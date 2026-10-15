# M55 — Common Mistakes, Formality & Style, Passive Infinitive

Статус: **локальне впровадження та приймання завершено**. Fresh незалежні source/DB/HTTP BEFORE та старі consumers зафіксовано до source sync. Авторську редакцію 1.0.0 заморожено після review, reviewed preview v2 застосовано транзакційно з exclusive backup. SQL postconditions, SSR, два repeated no-op, 18 actual browser states, 24 input cases, усі три widgets до 5/5, фінальний preservation audit та proof route cleanup/404 пройдено. Commit/push і незалежний remote SHA наведено у фінальному повідомленні; приватні докази до Git не входять.

## База та exact scope

Прийнята база M54: `0cf633036f4c91f2376976651ec0519d80ec6191`. Робоча гілка: `codex/seo-m55-passive-review-infinitive-standard-view`. Source worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`. Фактичний application ROOT: `D:/DEV/htdocs/gramlyze.loc`, document root: `D:/DEV/htdocs/gramlyze.loc/public`. Єдина ціль apply/HTTP/browser — `http://gramlyze.loc`.

| Урок | Підтверджений local URL | Page / category | Збережений actual H1 | Hero / практика | Continuing slots |
|---|---|---|---|---|---:|
| Common Mistakes in Passive | [Відкрити](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-common-mistakes) | 93 / 1 | Приклади вживання та типові помилки | A2–B2 / A2 | 11 |
| Formality & Style in Passive | [Відкрити](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formality-style) | 94 / 1 | Формальність та стиль пасиву | B2–C1 / B2 | 10 |
| Passive Infinitive | [Відкрити](http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive) | 103 / 17 | Passive Infinitive | B2–C1 / B2 | 9 |

Exact UK identities:

- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceCommonMistakesTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceFormalityStyleTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoicePassiveInfinitiveTheorySeeder`.

Перші дві ancestry — `passive-voice`; третя — `passive-voice → passive-voice-infinitives-gerund`, category IDs `1 → 17`. Вкладений URL підтверджений штатним resolver, не скопійований із плоскої Basics-категорії. Усі старі metadata, UUID/order/level vectors і anchors зберігаються. Category description та Passive Gerund — read-only preservation consumers, не четверта/п’ята цілі оновлення.

## Незалежний BEFORE та non-target consumers

`before-v1.json` SHA-256 `7c9adc7e77bd37092cf7dcad7eb62516b260b6d76a8ea1609f2741c42a1a33be`: 6/6 HTTP 200 для трьох theory/course пар, 46 фактичних SQL tables, 326 visual files, 2279 historical frozen files. Capture виконано до source sync. Він містить exact raw source bytes, Page/category ancestry, всі continuing blocks, bank inventory, H1/title/canonical/breadcrumb/LearningResource та повні SQL fingerprints.

`before-old-consumers-v1.json` SHA-256 `b65b3cfa7af97a8f7c8c4487e04eae1cdfa5380a808b306629e991e8d7315449`: 26 actual anonymous loopback HTTP consumers, 6 saved tests, 432 старі questions, 0 DB writes. Два додаткові consumers — окремі read-only category description та Passive Gerund. Для category порівнюється actual `.theory-category-learning`, не картки цільової Infinitive сторінки, preview якої дозволено оновити. Для Gerund — actual `[data-theory-main]`. Native GET route name/action, exact destination і unique DOM boundaries перевіряються, access gates не обходяться.

Registry BEFORE: **85 targets, failed 0, writes 0**, receipt SHA-256 `1c68788d22a957ad079a739c819e18affdc9882259b42ae5579652f4e94827bc`. AFTER: **88 targets, failed 0, writes 0**, SHA-256 `b666c7d889871c24bae469b9ffd214a3761c71edba1e92b3c9a90a32e077e0f3`. Окремий M55 contract gate: **3 targets, failed 0**, SHA-256 `c93670be8ccbb46b765b332dddd64b50f79a95d5e970b30436dc6be1297dddb0`. Це actual discovery трьох нових owners, не hardcoded registry limit.

Фінальний `after-old-consumers-v1.json`: **26 HTTP 200, 6 saved tests / 432 old questions, 0 DB writes**, SHA-256 `f3752beaed6ce4cb0031930a73510d83bc95f22de6c115b364b3c865605844a7`. Actual course boundaries, payloads, category learning description та Passive Gerund exact збережено; повний SQL baseline після browser acceptance звірено окремо. Це read-only gate, не повторне проходження сотень старих питань у UI.

## Мінімальна невізуальна сумісність

Чинний canonical шлях та компоненти не замінюються. Жодних нових Blade/CSS/frontend JS/grader, `m55-section`/`m55-detail`/`m55-styles` або нового діагностичного framework.

1. Passive Infinitive BEFORE subtitle не має leading strong. Чинний native title extractor фактично повернув `Passive Infinitive` із plain тексту перед тире. Новий finite descriptor фіксує exact old subtitle SHA-256 `0fa42a349d06ce69204e4531678883d4a5cbe3c15fdd9f5e1f91d8bbc7b4fb4e`, raw Page title, незалежний extracted title та actual H1. Опційний generic BEFORE guard приймає лише ці code-owned facts. Default leading-strong guard попередніх releases не змінено; AFTER як і раніше повинен містити exact leading title. Global PageController/title extractor не змінюється.
2. Formality continuing source index 7 зберігає stored type `mistakes-grid`, UUID `70e88baa-d5b1-5007-b7ea-5b08af8a3b51`, Page/category `94/1`, sort order 8, column left, level B2. Його нові нейтральні порівняння використовують підтриманий `usage-panels` тільки через code-owned finite component override, а не довільний payload view. Valid design/body guard дає native authority; bounded metadata-only fallback зберігає повний текст нейтральних sections без disclosure/grading authority. Original model не мутується; правильні стилістичні альтернативи не отримують wrong/right або перекреслення.
3. Source/DB/HTTP capture, consumers, audit, projection, proof, source sync та postconditions повторно використовують immutable перевірені M52/M53 read-only procedures через exact source SHA та literal-count substitutions. Вони не виконують автоматичного apply. M54 case helper, M53 українські Causative hints і previous frozen sources не переписуються.

## Редакція, практика та frozen data

Frozen версія **1.0.0**: versioned master, independent public BEFORE, content patch, readable UK edition, editorial mapping, component/basic-detail map, independent builder contract і bank audit. Checksum manifest `docs/content/m55-passive-review-infinitive-checksums.v1.json` містить 11 exact author/source hashes. Нові authored artifacts — UTF-8/LF з одним EOF; три active definitions — CRLF з одним EOF. Historical bytes не нормалізувалися. Найбільший body — **10315 UTF-8 bytes**, фактична body capacity TEXT — 65535; DDL не змінюється.

| Artifact | SHA-256 |
|---|---|
| Master | `d99299587c3876973869c1b9593bb158cf3904cae2df1c972d07866ce573cd1d` |
| Public BEFORE | `31779edfe50d7a1c00059ffea591eced2e068ca89976707e833a38a477f4ac51` |
| Content patch | `af0cf5fcc06550d0b8c71e6dfc1ec208f9d18d636d070a3ddc02bd6df006fa0c` |
| Builder contract | `4811578f527df86ee5c7835bab6913fa7d57629e3bfab1c49ae075234c70c612` |
| Bank audit | `861d36dc270f4a016951e57c42255b59b97606bb87229f22b97765394f68a848` |
| UK readable edition | `e347a6888b1f3577c2cf24c3a9876e383809786c4540b973ba442c7c000ec56b` |
| Editorial mapping | `60346157affe95f15ccf80596d092a27a812b59e2d52b9f624b2d755d740957e` |
| Component/basic-detail mapping | `accd69824d85c80a5a67e51005d94e4c68aecc4010858ab2726a0464b846a16a` |

Common Mistakes побудовано як алгоритм перевірки змісту, підмета, повного допоміжного ланцюжка та V3. Справжні помилки залишено в mistakes-grid із окремим українським задумом і перекладом правильної версії. Обмежено правила звичайного be-passive, збережено межу з get-passive, V2/V3 не вимагають різного написання. Echo questions, by someone і нейтральний порядок відокремлено від абсолютної неграматичності.

Formality пояснює інформаційний фокус, зв’язність, нового/відомого агента, явну відповідальність і конкретну мету. Active не дорівнює лише розмовному, Passive не гарантує об’єктивності. Граматичні стилістичні альтернативи не маскуються під wrong/right; невідомий агент не домислюється як we. Науковий active з we та вибір жанру/видання розрізняються. Ідіоматичне A good time was had by all не оголошено граматичною помилкою.

Passive Infinitive відрізняє non-finite `to be + V3` та `to have been + V3`, ролі учасників, object patterns, заперечення й питання особової конструкції. Час визначає контекст, а perfect — точка відліку, не обов’язково минуле до тепер. Modal bare forms відокремлено від to-infinitive, should/could не зведено до одного значення припущення. Need + to be / -ing та регіональний needs + participle описані без перетворення навчальної норми на заборону всіх діалектів.

П’ять meaningful details мають exact point owners; це семантичне рішення, не квота/word-count threshold:

| Detail | Owner |
|---|---|
| Чому питання без інверсії іноді можливе | `mistakes-context /sections/0` |
| Чому важливий агент може стояти наприкінці | `style-editing /sections/1` |
| Структура речення й висновок про мотиви | `style-effects /sections/1` |
| Need: стандартна модель, -ing та регіональний варіант | `infinitive-simple /sections/0` |
| Чому work to do не виправляється автоматично | `infinitive-patterns /sections/0` |

Власні **18 tasks** — по 2 selects + 2 choices + 2 inputs на кожній сторінці. Шість inputs підтримують manual/token, але не рахуються двічі. Критерій meaning choice видно до Check; переклад, що відкриває відповідь, і feedback — після Check. Finite accepted aliases не проголошують інші природні перефразування неграматичними.

| Widget | Exact bank | Type / level / eligible pool | Selected existing IDs |
|---|---|---|---|
| Common Mistakes | `PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder` | 4 / A2 / 12 | 37371–37375 |
| Formality & Style | `PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder` | 4 / B2 / 12 | 36243–36247 |
| Passive Infinitive | `PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder` | 4 / B2 / 12 | 38979–38983 |

Окремі widgets — **15 reused, 0 new**, не частина 18 власних tasks. Question-family SQL supplements не потрібні; банки, ключі, ordered instances та shared options не переписуються. Native server-token capitalization для всіх 15 відповідає reviewed source без нового case map. П’ять Formality умов мають bounded render-only контекстну projection: exact owner/Page/category/practice/question snapshots, all-or-nothing cloned models; початковий condition та всі English keys/tokens збережено. Це не hint, не зміна grader/DB і не масове редагування банку. Personal attempts не інвалідуються; fresh server payload та відновлений attempt перевіряються окремо.

Actual source-access журнал міститься у frozen master. Cambridge direct requests дали 403; publisher-indexed editorial text не названо повним direct reading. Для requested infinitives URL наведено фактично прочитаний indexed URL. [APA active/passive guidance](https://apastyle.apa.org/style-grammar-guidelines/grammar/active-passive-voice), [Yale needs washed](https://ygdp.yale.edu/phenomena/needs-washed), [British Council active/passive](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice) та [Google helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) прочитані як direct editorial/research body. APA — конкретна редакційна система; Yale — опис варіативності, не головний навчальний ключ. Учнівські коментарі й видавничі exercise banks не є джерелом нових готових вправ. Немає вигаданого reviewer, certification/rating/FAQ schema, декоративної дати або ranking claims.

## Guarded apply, перевірки та обмеження

Fresh proof → exact preview review → exclusive backup → transaction → SQL postconditions → два repeated no-op → actual browser acceptance → фінальний read-only preservation audit → proof route cleanup/404: **виконано**. Жодний fixture/SSR marker/HTTP 200 не зараховано замість interaction acceptance. Actual APP environment — `production`, SiteMode — `development`; це наявний локальний runtime `gramlyze.loc`/loopback, а не звернення до production. Environment/config не змінювалися.

Початкові source sync/preview виконано: 25 exact paths, 326 visual / 2279 frozen без змін; source-sync-v1 receipt SHA-256 `94b33d51333bafa6f27e5de93c692e87695216fc07312a6edadead075f1c447d`. Preview v1 показав 36 content updates і 3 practice inserts, 0 question-family writes; digest `ee94dfb209450be27a175c445c609b5b3582af78d5941fa752f337b72ae94b12`. **Цей preview не застосовувався**: isolated fidelity test нижче виявив відсутню M55 correction registration. Старі receipts не перезаписані; після мінімального runtime fix виконано окремі source-sync-v2, fresh preview v2 та повторний exact review. M55 writer вказує новий basename; frozen author content не переписувався.

Після correction fix source-sync-v2 пройдено: **25 paths, 326 visual / 2279 frozen unchanged**, receipt SHA-256 `08a1f1b8147b3bd28d9016cf6f6a2851f5b412d50fc0bb9deb670b1b7bc3b963`. Новий preview v2: digest `7098842121fc954d2cf938290a6f3737ca047597cce076ba28da222071d10a65`. Всі 36 updates / 3 inserts, pages/names/connection/state та protected rows exact збігаються з v1; змінилися тільки reviewed source pins двох runtime files. Main незалежно повторно переглянув allowlist. Старий preview не видається за чинну авторизацію після зміни source.

Після повторного isolated fidelity/preservation PHP PASS застосовано **саме reviewed preview v2**: transaction **36 updates / 3 inserts / 0 deletes**, перед ним створено exclusive `backup-v1`. Existing updates — 3 Page texts, 3 UK subtitles та 30 UK continuing bodies; inserts — по одному practice block на точний target (actual IDs 12696, 12697, 12698). Жодного запису до question/answer/option families, links або pivots.

SQL postconditions `final-readonly-v1` пройдено, receipt SHA-256 `89425fec23e7af6438caf2f60da874c1af8ebb11265ed676c41378fc19ed7753`. Фінальний повтор після всіх browser interactions `final-readonly-v2` також PASS, SHA-256 `9a5781269a398a0fd7678b8064d82d04a781cbeed2b067e01c759f6b281a92d8`: усі 46 tables звірено з allowlist і independent BEFORE; 33 старі UK blocks зберегли metadata, 60 EN/PL blocks та всі старі timestamps незмінні. Обидва AFTER plans мають state `after`, **0 updates / 0 inserts / 0 deletes**, digest `1adf3f8edc87fd021d6bda7a6fb0af5c912f16065d1e3defaaecc8cff4295361`. Два guarded повтори вихідного reviewed plan завершено **no-op 0/0/0**; нові backup files не створені.

Тимчасовий read-only proof route видалено. Actual routes file відновлено byte-exact до captured BEFORE зі збереженням сторонніх змін; окремий GET nonce URL повернув **404**. Cleanup receipt SHA-256 `a0263a7ecff59f1b8b0e710e7aef1d666db04968185eaaea7bbc95770c7aec94`. Тимчасового route у source worktree немає.

Actual SSR `live-pages-v1`: **3/3 HTTP 200, 684 learning fields, 15 strict ordered widget keys і approved context projections — PASS**, receipt SHA-256 `44293ed3ed3a85e68c56278f92d172e03394e951ac7f47f4ab1971d03f990860`. Окремі 12 unique frozen navigation destinations повернули actual loopback HTTP 200 без redirects. Це read-only server acceptance; воно не замінює натискання Check, роботу token/manual modes або 5/5 widget у браузері.

Початковий focused run: **16 PASS / 2817 assertions**, M55 compatibility 4 + unchanged M54 package 4 + M54 token 4 + M53 hints 4. Child/wrapper exit 0, SQLite `:memory:`, array cache/session, робоча `.env` не завантажена, 46661 protected files — 0 changes. Receipt SHA-256 `ded466b15405b1477df4430650fc2f73fe4165c4786030fb009f2a68d853e1d1`. Одна inherited PHP 8.5 PDO SSL deprecation. Після цього run додано explicit Page-vs-Category check і negative assertion; їх покриває фінальний full run, а не цей initial receipt.

Historical literal-UI suite фактично повторено окремим isolated wrapper: **6 tests / 23 assertions, 2 PASS / 4 FAIL**, child/wrapper exit 1, 46661 protected files — 0 changes. Receipt SHA-256 `f84e8d091b7ff2786269e186167809e784c01a8d848ddb85504323c0da3d7e4c`. Це ті самі чотири успадковані M54 literal assertions: два шукають старий input markup замість чинного component/AttributeBag, один очікує plain span без native class, один — стару global choice-options змінну замість item-owned options. LF SHA-256 тесту `2e731050961d48982a33da3b3c90d0bd29a8fb9c3da0c13140b229628990dbc4` і `resources/views/engram/theory/blocks-v3/practice-set.blade.php` — `fe0f7e6d3c6ee1fad226e62bb611412c10bbef655d79c506177cf86a7ef8bce3` — exact збігаються з accepted M54 baseline. Tests/snapshots/native view не змінювалися. Ці failures не перейменовано на PASS й не зараховано до 176 passing tests; повторні runs не додаються до унікального total.

Перший повний new/preservation run: **112 tests / 32379 assertions, 1 NEW failure, child/wrapper exit 1**, 46661 protected files — 0 changes. Receipt SHA-256 `4c4efde0bf42c4339926685c0072ecb23f28eb0ef081048cf8146ffd1a30b69a`. Failure — M55 native renderer fidelity не побачив `wrong_en` у Common Mistakes. Причина: chain `TheoryLegacyAdapter` закінчувався M54, тож двомовні M55 correction fields не передавалися підтриманому компоненту. Додано лише M55 correction call та replacement результату після чинного M54 call; дизайну, навчального тексту, engine або historical guards це не змінює. Чотири inherited literal-UI failures не входили до цього run і не пояснюють цей новий дефект.

Повтор **тих самих** 112 cases після fix: **112/112 PASS, 33205 assertions**, child/wrapper exit 0, 46661 protected files — 0 changes. Receipt SHA-256 `01fd59d211d64ae248d6c2af1b98b2a8e30585d99c5664f838104882ba7791b5`. Жодних assertions/cases не видалено і frozen author fields не переписано. Це 24 нові M55 tests (Compatibility 4, Package 4, ContentPatch 4, Guard 3, Controller 2, Contract 3, ContextPresentation 4) плюс 88 інших acceptance/renderer/M52–M54/answer regressions. Initial 16 та перший 112 run є повторами цих cases й не додаються до унікального total. Залишається одна inherited PHP 8.5 PDO SSL deprecation на batch.

Окремий **непересічний** production-profile/native-policy batch: **64/64 PASS, 1949 assertions**, child/wrapper exit 0, 46661 protected files — 0 changes; receipt SHA-256 `5daebc5be6d1c18206c6c0f4b0c31c348c7d5016692d43918be6428a0991ca1d`. Це 35 SiteMode/SEO/Canonical/PageMetadata tests та 29 native release/planner/M53 supplement/contract/guard/M54 patch/contract/guard regressions. Production-profile requests — тільки isolated in-process kernel, не `.com`/`.ub` network. Робоча `.env` і MySQL не використовувалися; SQLite `:memory:`, array cache/session, owned temporary runtime та byte-exact protected inventory.

Разом **176 унікальних passing PHP tests / 35154 assertions** у двох непересічних фінальних batches. Historical literal-UI suite обліковується окремо; старі failed runs не приховані, але не додаються до passing total.

M55 offline JS frozen actual practice factory: **22/22 PASS**, suite source raw SHA-256 `29fbbd519ca124b4edd49dc712191e23447d89e346c6c0f3c3e8d76a3c5a5689`: 18 незалежних own keys, 34 options, шість manual + шість token inputs, сім aliases. Фінальний повтор також 22/22, network calls 0. Це offline engine coverage, не 24 desktop/mobile browser cases; повтор не додається до unique total.

## Actual browser acceptance

Computer-use skill, same IAB session, loopback-only URLs; zoom не змінено, storage/session/progress не очищувалися. Кожний урок переглянуто до footer, відповідно 10 / 9 / 8 body sections. Збережений actual H1, loaded fonts/assets, 3 own practice groups, 2 inputs і один окремий sentence builder підтверджено в кожному стані.

| CSS viewport, actual DOM | Common Mistakes | Formality & Style | Passive Infinitive |
|---|---|---|---|
| 1440×1000 | light + dark PASS | light + dark PASS | light + dark PASS |
| 390×844 | light + dark PASS | light + dark PASS | light + dark PASS |
| 320×844 | light + dark PASS | light + dark PASS | light + dark PASS |

Це **18 унікальних actual target states**. Reference PPC та M54 Two-object — ще 2 read-only visits, не target states. Learning main client/scroll widths exact 877/877, 358/358, 288/288; нового learning overflow не виявлено. Native semantic panels: 16px, border 0, чинний light/dark фон. На references також main 358/358, 3 groups і 1 widget; M54 попередній стан П5/5 збережено.

Всі **18 власних tasks** приймають незалежно reviewed правильні відповіді. Для всіх 9 groups у live UI перевірено empty, partial, wrong, repeated wrong, retry, correct, recheck та reset. Selects/choices: результат 0 → 1 partial → 0 wrong → 2 correct → 0 reset; inputs: 0 partial → 0 wrong → 2 manual → 2 tokens → 0 після видалення → 2 rebuild → 0 reset. Усі 6 canonical inputs повторено manual/tokens на actual 1440 та 390: **24 unique task/mode/size cases**. Token used instances, deletion/return/rebuild спостерігалися в UI. Contracted Common Mistakes alias також прийнято. У live UI перевірено canonical та representative wrong варіанти; **всі 34 options / 7 aliases** — окреме offline coverage, не перебільшений UI total.

Кожний embedded widget пройдено всіма п’ятьма питаннями: **Common 5/5, Formality 5/5, Infinitive 5/5**. Умови й банки видимі, exact capitalization/multiplicity прийнято, Next веде далі; random order не підмінено source order. Для п’яти Formality render-only contexts збережено первісні умови й повний зміст. M55 case map не додавався.

Усі **5 details**: initial closed; Enter/Space; відкривається тільки власний owner; deep-link і reload відкривають власний detail; повний текст (621/693/672/861/703 characters) без clipping, наприкінці закрито. Final proof показує нейтральні Formality alternatives без `s`/`del` і wrong/right. Browser error log після reference visits порожній. Початкову помилково підписану desktop capture з actual DOM 1280 виключено; matrix повторено на active owned tab з actual DOM 1440/390/320. Скріншоти — незмінені JPEG bytes із збереженими `.png` назвами: pixel sizes 1390×990 / 375×812 / 305×804 записано окремо від CSS viewport, їх не видано за DOM dimensions.

Приватний матеріалізований підсумок actual observations `browser-acceptance-v1.json`, SHA-256 `1624896d3877d2922610aaf95d9fdd55f09271c1902d095e20b7f998057196bd`. Це summary виконаних interactions, не fixture, що генерує PASS. Final screenshot `m55-final-formality.png` SHA-256 `7f5f588bedb7b97340b53052f57572123f824f17b29f85cd07939efb850f9522`; DOM viewport 1280×720, image 1265×712. Theme повернуто light, viewport override скинуто.

JS-off/print: **не перевірено**; capability у цій сесії не exposed, це не твердження про відсутність підтримки сайтом. Успадковані decorative shell overflow (320–331 при CSS 320; 394–404 при CSS 390) і низький dark contrast CEFR badge/secondary labels залишаються, не оголошуються виправленими. Нового fallback engine або CSS-маскування не додано.

## Доставка та межі

Перед commit independent scope review та staged `git diff --check` пройдено. У staging рівно **42 related files**, **11/11 frozen raw hashes = Git index hashes**, 0 forbidden/private artifacts і 0 unstaged tracked changes. Фінальна served ROOT звірка підтвердила всі 25 reviewed source-sync paths та незмінність 326 visual / 2279 historical frozen files. Shared diff не містить нових visual sources або змін grader/engine. Сторонні untracked файли збережено й виключено зі staging.

Доставка: scoped staged review → `git diff --check` → scope/frozen-source validation → commit → normal push → незалежний remote SHA == HEAD. Exact full commit SHA і результат push повідомляються після фактичної Git-операції у фінальній відповіді, не вигадуються наперед у самореферентному report. Main/production, PR/merge/force push, SSH/deploy, `.env`, hosts/config/ACL/dependencies і особистий прогрес поза scope. Секрети, dumps/backups, screenshots/proofs, runtime receipts/cache/generated assets, тимчасові proof routes і сторонні dirty changes до commit не входять. Історичні звіти не переписуються. Локальне приймання не є доказом ranking/CTR/traffic gain.
