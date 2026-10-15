# M54 — Phrasal Verbs in Passive, Restrictions, Two-object Verbs

Статус: **три сторінки застосовано на gramlyze.loc; browser acceptance і фінальний read-only preservation audit завершено**. Виконано редакційний review, frozen sources, fresh local proof, reviewed preview, exclusive backup, transactional apply, два repeated no-op, postconditions, HTTP/SSR acceptance, ізольовані тести та реальні браузерні взаємодії. Тимчасовий proof route видалено з підтвердженим 404. Git-доставка обмежена робочою гілкою M54; фактичний commit SHA та незалежна remote parity наводяться в кінцевому повідомленні, без self-referential SHA всередині власного commit.

## База, робоча гілка та exact scope

- Прийнята база M53: `f4e1c0d94a590d630582e2bb613cbdc93654fc4a`.
- Робоча гілка: `codex/seo-m54-passive-patterns-standard-view`.
- Source worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Фактичний served ROOT: `D:/DEV/htdocs/gramlyze.loc`; document root: `D:/DEV/htdocs/gramlyze.loc/public`.
- Єдина ціль apply, HTTP та browser acceptance: `http://gramlyze.loc`.

Fresh physical/web/CLI workflow підтвердив локальний Windows application/document root, MySQL loopback `localhost:3306`, database `gr2` і SiteMode development. Наявне значення APP environment production не змінювалося й не використано як дозвіл production-деплою. `.env`, hosts, Apache/XAMPP configuration, ACL, dependencies і особистий прогрес не є цілями зміни.

Main, PR, merge, force push, workflow dispatch, SSH/deployment та запити до `.com`/`.ub` не входять до M54. Production canonical strings прочитані лише з локального HTML; їх наявність не означає звернення до production.

| Сторінка | Page / category | Збережений H1 | Hero / практика | Continuing blocks | Meaningful details |
|---|---|---|---|---:|---:|
| [Phrasal Verbs in Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-phrasal-verbs) | 99 / 1 | Пасив фразових дієслів (Phrasal Verbs in Passive) | B1–B2 / B1 | 9 | 2 |
| [Restrictions](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-restrictions) | 100 / 1 | Обмеження пасиву | B1–B2 / B1 | 10 | 3 |
| [Two-object Verbs in Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-two-object-verbs) | 101 / 1 | Two-object verbs | B1 / B1 | 9 | 2 |

Exact UK identities:

- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoicePhrasalVerbsTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceRestrictionsTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceTwoObjectVerbsTheorySeeder`.

Усі мають category ancestry `passive-voice`. Slugs із `theory-`, raw Page titles, category binding, continuing UUID/source index/type/column/level та старі anchors збережено. Існуючі B2 blocks не вирівнювалися до B1. Навігаційний slot не замінено практикою: кожному owner окремо додано один practice-set.

## Редакційні зміни

Phrasal: уточнено be/V3, перехід активного додатка в підмет, збереження частки/прийменника й ролі by-phrase. Розрізнено particle, prepositional та потрібні multi-word моделі без перейменування сторінки. Called off означає скасування, але called не оголошено автоматично неграматичним. Put off не перекладено як скасування, break into — як поломку, bring up — як довільне grow up; cancel залишено однослівним відповідником. Збережено корисну лексику й повністю перекладені Active/Passive таблиці. Уточнено perfect/continuous питання та заперечення, separable active проти passive і природне well looked after; немає глобальної вимоги фізичної суміжності або єдиної позиції кожного прислівника.

Restrictions: blacklist дієслів замінено аналізом конкретного значення та структури. Розрізнено неутворювану модель, зміну змісту й контекстно невдалий пасив. Stative сам по собі не забороняє passive: owned by не позначено помилкою. Have/own/possess, fit/suit, contain/hold/weigh та інші значення не змішуються в одну заборону; contained in і дія зважування відокремлені від маси предмета. Уточнено linking complements, обставини/міру, перехідні й неперехідні open/run/grow, reflexive/reciprocal ролі, cognate objects, keep an eye on, let і get + adjective. Прозорі happened/arrived corrections збережено. Правильна відповідь у частині практики навмисно є активною.

Two-object: відокремлено виконавця, отримувача та предмет передачі; person/thing не оголошено абсолютною класифікацією учасників. Дві passive побудови зіставлено за фокусом, без безумовного ранжування природності. Уточнено he/she/they проти об’єктних форм, agreement, to/for і by, збереження часу/аспекту/заперечення/modal та відомого агента. Give-модель не перенесено на explain/suggest. Варіант given him без to не залишається фальшивою універсальною grammar error; основна B1 модель із to явно задана у вправі. В усіх таблицях IO/DO-коментарі відокремлено від повних перекладів.

Повний поурочний BEFORE/AFTER mapping із причинами збереження, виправлення, доповнення, об’єднання повторів і заміни: `docs/content/m54-passive-patterns-editorial.v1.0.0.md`. Повна читабельна редакція: `docs/content/m54-passive-patterns.v1.0.0.uk.md`. Коректний старий матеріал не вилучався лише через незручність renderer.

## Канонічні компоненти та basic/detail

Збережено чинний шлях `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Використано hero, forms-grid, usage-panels, comparison-table, mistakes-grid, summary-list, navigation-chips і practice-set. Немає M54 views/styles, нового renderer/grader, Blade/CSS/frontend JS змін, line-clamp або зменшення шрифту.

Usage-пункти мають явні назви, підтримувані semantic accents, правила, EN/UK приклади й примітки. Форми, таблиці, summary та navigation зберігають власні стандартні компоненти. Компактний basic самодостатній; сім details мають змістовні власні пояснення, а не лише приховані переклади:

| Detail | Exact owner |
|---|---|
| Чому well не має лише одного місця | `phrasal-vocabulary /items/0` |
| Прийменник не дає автоматичного дозволу на пасив | `phrasal-prepositions /items/4` |
| Чому have, own і possess не утворюють один blacklist | `restrictions-states /sections/0` |
| Інше значення не виправляє початковий зміст | `restrictions-states /sections/1` |
| Містити, утримувати та зважувати — не одна модель | `restrictions-states /sections/2` |
| Чому explain і suggest не копіюють give | `two-typical /sections/2` |
| Варіант без to й основний навчальний шаблон | `two-to-for /sections/0` |

Це finite semantic mapping, не quota й не runtime-поріг слів. M54 fidelity перевіряє також підтримувані forms rules, outro, table cells, correction translations/notes та окремий wrapper кожного point-level detail. Кілька details в одному блоці не зливаються в один загальний disclosure.

## Власна практика та окремі widgets

На кожній сторінці — **2 selects + 2 choices + 2 inputs**. Разом 18 власних tasks у дев’яти групах; кожний із шести inputs має manual і token спосіб, але рахується одним завданням. Умови й лексична допомога українською, slash-banks перемішані; готова відповідь не показується в hint/placeholder. Значеннєвий критерій видимий до Check, точний переклад, що розкривав би ключ, — після Check.

Phrasal практика перевіряє call off, continuous/passive, put off, active separability, perfect question та negative continuous. Restrictions — happened, weighed як дію, owned by, open без зміни моделі, актив arrived і дозволений ownership passive. Two-object — recipient agreement, beneficiary for, заданий фокус повідомлення, agent/beneficiary та дві побудови зі збереженим виконавцем. Finite accepted списки відповідають явно заданим моделям і не проголошують інші природні перефразування неграматичними.

| Сторінка | Exact тематичний банк | Придатний B1/type-4 пул | Відібрана п’ятірка |
|---|---|---:|---|
| Phrasal | `PolyglotPassiveVoicePhrasalVerbsAllLevelsLessonSeeder` | 12 | IDs 37887–37891 |
| Restrictions | `PolyglotPassiveVoiceRestrictionsAllLevelsLessonSeeder` | 12 | IDs 37959–37963 |
| Two-object | `PolyglotPassiveVoiceTwoObjectVerbsAllLevelsLessonSeeder` | 12 | IDs 37815–37819 |

Окремі widgets: **15 reused, 0 new**. Вони не входять у 18 авторських tasks. Нові questions/answers/options/pivots/theory links не створювалися; старі банки не пересівалися. Selected 15 hints і verb_hints порожні, тому новий render-only hint hook не потрібний; чинна M53 UK Causative projection не змінена. Повторювані ordered tokens і case-insensitive shared options збережено чинним engine.

### Додаткова перевірка регістру widget tokens

Після початкового SSR-приймання strict-порівняння фактичних 15 payloads виявило успадковані shared option spellings `Given`/`Brought` усередині речення. Початковий `live-pages-v1` порівнював ordered tokens без урахування регістру: він доводив порядок і multiplicity, але **не** правильну capitalization кожного токена. Цей дефект не приховано загальним PASS.

Погоджене bounded виправлення — нова finite `M54BuilderTokenPresentation` та один hook у чинному theory renderer після M53 presentation hook. Межа реалізації: exact M54 practice/question binding, frozen bank-audit correct tokens і raw snapshots, all-or-nothing cloned Question/Answer/Option presentation, без мутації початкових models. Відновлюються лише три reviewed instances: 37889/a3 Brought→brought, 37815/a3 Given→given, 37816/a5 Given→given. Це не зміна БД, shared options, answer engine, старих банків, Blade або JS. Helper і чотири нові тести переглянуто; фінальні 133 PHP tests, actual strict capitalization усіх 15 selected payloads та окремий filesystem source audit пройдено. Browser interaction і повторний final 46-table audit описуються окремо, без нового content apply й без зміни вже застосованого SQL writer.

Перший focused helper run виявив залежність від `LocalizedComposeText`, наявного як стороння ROOT робота, але відсутнього у versioned worktree: 8 tests / 924 assertions, 1 missing-class error і 2 залежні failures, child/wrapper exit 2, 46661 protected files без змін. Receipt SHA-256: `6b060c046414745f2be02bc3c376d7da430bb6b5c8f258808fd1978e7bb052dd`. Однорядкове bounded виправлення використовує чинний `ComposeTokenCase::normalize($tokens)` для цих non-PPC M54 banks; не копіює сторонній helper і не змінює engine. Повтор тих самих helper/M53-hints cases дав **8 PASS, 1135 assertions**, child/wrapper exit 0 і 0 protected changes; receipt `e5b42c31af4b2cec05374aa46fc1cf634752463ca5ae1b6b52f2ab65b3dc1562`. Загальні 133 tests нижче повторно включають ці cases, тому 8 не додаються вдруге. Початкову невдалу перевірку не видаємо за PASS.

Reviewed corrected helper SHA-256: `68734166024d4c3f65b6ebb585a9f26f396f7d79204d0c4f9a7ff99d2164a1bb`; renderer AFTER: `47dada869deba7dc457e2b4b346e86d9ce9be3d482cc450502cbf46acb5303d0`. Вилучення одного exact hook відновлює renderer BEFORE `8783db815ab2d07beb5c1e6e8cdba95ce7e6b4583bb4dc91744889a3d84dba11`. Виконаний filesystem-only audit підтвердив exact ROOT/WT source parity, один hook, незмінний SQL writer/source-sync-v1, 326 visual і 2266 frozen hashes. Він не bootstrap-ить Laravel, не звертається до БД/HTTP й не замінює жодного writer guard. Receipt SHA-256: `4c59ec4079975450ceacf953681b8bcb74d36ca210deccd16151a7a2d4019ac4`.

Новий незалежний contract: `docs/content/theory-sentence-builders/m54-passive-patterns.v1.json`. Actual full read-only audit підтвердив **85/85 targets, failed 0, writes 0**: 82 попередні owners плюс три M54. Це результат discovery, не постійна межа 85. Receipt SHA-256: `84b02806ef401cf9c1ff6b148e1bf90e6bfc34e8b0fa00ff338e76da3e64c7f6`. CLI/model/server selection не замінює browser visibility або interaction.

## Frozen release та джерела

Редакція **1.0.0**. `docs/content/m54-passive-patterns-checksums.v1.json` містить 11 exact hashes; усі 11 звірено незалежним читанням файлів. Нові авторські artifacts — LF/один EOF; active definitions — CRLF/один EOF. Історичні frozen bytes не нормалізувалися.

Перед commit виявлено відсутні M54 checkout attributes: стандартний Windows autocrlf міг перетворити нові LF snapshots і нормалізувати CRLF active definitions. До versioned `.gitattributes` додано тільки finite M54 `-text` правила за чинним підходом M53; для трьох active definitions збережено стандартний `cr-at-eol` whitespace contract. Це зберігає raw frozen hashes у staged blobs і майбутньому checkout без переформатування джерел. Сторонній deleted `.gitattributes` у served ROOT не відновлювався. Остаточний scoped commit містить 44 файли, не 43; приватні runtime artifacts і сторонні зміни не включаються.

| Artifact | SHA-256 |
|---|---|
| Master | `d34d35d9ff0882160c0fd91fbfddda32294c73282eccc87fb9040d1fe8a1e9fb` |
| Independent public BEFORE | `66268c26cfea551c925a4374ec35f17dc31cc79147a8336977c66ef6671478eb` |
| Content patch | `0523683ff2c5b4621831d7fa10ae9675941069c7206ac44fa56ea7df45d6f9f9` |
| Builder contract | `f91a4366af510eeeac0482517529675683038036f377628ece5ca704e95a45d8` |
| Bank audit | `2ae1efb300d3fccc719a7a7ddd4865738a33cf60df40720c54fa9c86d1d0034c` |
| UK readable edition | `5418af98800ac084ee33f427e980c71526d9fdf4daebfd4e9e22075fc77233ca` |
| Editorial mapping | `ce3406d3ba697a8e6b54b87c127b443f1064ef998c45030003dfdfaf88bab812` |
| Component/basic-detail map | `df9b4d1033ab0886e2e68bf57a75c8d03a9a6ba8d932784a99d6749c7e9deb58` |

Active definition hashes також є в manifest. Фактична SQL body capacity — TEXT, 65535 UTF-8 bytes; largest approved body — 10528 bytes. DDL не змінювався.

Actual access журнал міститься у frozen master. Cambridge direct requests дали 403; publisher-indexed редакційні тексти не названо повним прямим читанням. Для requested `verb-patterns-with-and-without-objects` окремо зафіксовано недоступність і фактичний indexed URL. Own/contain/weigh/possess та adverb/let статті використано за перевіреними значеннями; corpus observations не видано за універсальну редакційну норму. Основні publisher references: [Cambridge passive forms](https://dictionary.cambridge.org/grammar/british-grammar/passive-forms), [multi-word verbs](https://dictionary.cambridge.org/grammar/british-grammar/verbs-multi-word-verbs), [objects](https://dictionary.cambridge.org/grammar/british-grammar/objects).

British Council [Active and passive voice](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice) та Google Search Central [helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) прочитані як direct editorial body. Для обмеження абсолютної заборони passive без to використано direct university-hosted publisher PDF [A Grammar Writer’s Cookbook, §3.3.3–3.3.4](https://typo.uni-konstanz.de/csli-konstanz/books/cookbook.pdf); рідкісний варіант не став основним B1 шаблоном. Учнівські коментарі не є мовною нормою; видавничі exercise banks не копіювалися. Немає вигаданого рецензента, сертифікації, ratings/FAQ schema, keyword quota або ranking claims.

## Guarded apply, no-op та preservation

Незалежний fresh source/DB/HTTP BEFORE: `a0fc78b98a12e8af17c56d3f4fde8dccf95031d6de2a5a4c8081afeba15edb64`. Він зберіг exact owners, metadata/anchors, raw definitions, bank/consumer inventory, 46 table fingerprints, 326 visual source hashes і 2266 historical frozen hashes до source sync.

Source sync переніс лише власні exact hunks/нові файли; 23 paths, **0 DB writes / 0 HTTP requests**. Receipt SHA-256: `1111f0107be49e29ac118d913f5e208aa9995a2c4ec4a0e3004633342a946139`. Відновленням тільки власних controller insertions підтверджено збереження foreign-inclusive BEFORE bytes. Served ROOT має сторонні dirty changes; повна ROOT↔Git parity не заявляється.

Цей reviewed writer receipt/preview збережено незмінними. Token-case presentation доповнення не змінює `M54ContentPatch` або його 23-path sync policy: новий helper і один renderer hook перевірені окремими exact source pins/read-only evidence. Це відокремлює невізуальний formatter від завершеного SQL write scope, не послаблюючи жодної старої source/BEFORE перевірки. Presentation audit і повторний final SQL audit після всіх browser interactions завершено.

Fresh nonce proof → reviewed exact preview → exclusive private backup → transaction → postconditions → repeated no-op виконано явними CLI кроками, без reseed/migration/truncate/full restore або HTTP/startup/scheduler apply hooks. Reviewed preview digest: `2511ed208a4a27637a7e8e397130f0442a8c270afcaae9356c492e76075f8301`; 44 source/evidence pins. Exclusive `backup-v1.json` існує й містить exact reviewed BEFORE plan; не включається до commit.

| Сімейство | Updates | Inserts | Deletes |
|---|---:|---:|---:|
| `pages.text` | 3 | 0 | 0 |
| Continuing UK `text_blocks.body` (3 subtitles + 28 blocks) | 31 | 0 | 0 |
| Нові B1 practice-set blocks | 0 | 3 | 0 |
| Questions / answers / options / option pivots / theory links | 0 | 0 | 0 |

Разом **34 updates, 3 inserts, 0 deletes**. Нові practice IDs 12693/12694/12695; UUIDs `393cd4a7-ff40-5825-84d8-43d9339e6be7`, `45b3390b-c647-575c-b15e-6020f75d3e49`, `399ac7bd-9a4b-596b-a33a-78c4de0ba368`.

Два повторні apply завершилися no-op: **0 updates / 0 inserts / 0 deletes**, без нового backup. Обидва AFTER plans мають порожні updates/inserts та ті самі protected fingerprints. Початковий postconditions receipt `final-readonly-v1.json` незалежно реконструював всі 46 BEFORE table fingerprints, повертаючи лише exact approved content fields і виключаючи тільки три перевірені нові practices. Жоден question-family рядок не виключено. Збережено всі старі rows/timestamps, 56 EN/PL blocks, прогрес і нецільові payloads; 326 visual та 2266 frozen source files незмінні. Receipt SHA-256: `90e529db4a3153e56f1485f77bc328d0def2e4106ebd3b3427c4ccf5d69a699d`.

Old-consumers audit після apply: **24 HTTP 200, 6 saved tests, 432 старі questions** з exact per-question payloads/theory help та доступним course DOM; access gates не обходилися. Під час GETs усі SQL fingerprints незмінні. Receipt SHA-256: `a85e958273f2886eb541d6217fec3f6ada9792924cd437a769a8531bb36e18dd`.

Після formatter, повного browser acceptance і `final-readonly-v2` цей old-consumers audit повторено: знову **24 HTTP 200, 6 saved tests, 432 exact old questions, 0 DB writes**. Receipt `after-old-consumers-v2.json` SHA-256: `bead24b3738e2b3dd223efea53f77d7768384b2a3d49ef42852233055de4cc69`. Це actual HTML/model preservation, не UI-проходження всіх старих питань.

Proof route після apply/no-op видалено. Незалежний fixed-loopback GET повернув **404**, route file повернуто byte-exact до SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`, physical hashes незмінні. Cleanup receipt: `87d0d57e21bf94edc08d08b522121525cf4a6face7e16fca63bffa1a755d3d1e`. Route не входить до доставки.

Після всіх браузерних взаємодій і token-case formatter виконано новий незалежний `final-readonly-v2.json`: **46 tables, усі старі rows/timestamps exact, 31 approved UK updates, 56 EN/PL blocks незмінні, тільки три approved new practices, 0 question-family writes**. Реконструйовані BEFORE fingerprints збігаються з незалежним fresh BEFORE; source/physical/visual/frozen guards також пройдено. Receipt SHA-256: `02bf535afa3e9fa3b4c82833492fd8490b7851476afa465f774baf22465eb921`. Audit не виконує apply, restore, seeder або HTTP; нових записів контенту після первинної transaction не було.

Останню незалежну звірку повторено також після фінальних 24 old-consumer GETs: `final-readonly-v3.json`, SHA-256 `ffaa43345d0151b53fa17c929f68ffd9f182334d283233bb7eacec5479fbea6e`. Результат незмінний: усі 46 BEFORE fingerprints реконструйовано exact, всі старі rows/timestamps збережено, question families 0/0/0/0/0, audit writes 0. Усі 11 frozen author hashes окремо повторно звірено в ROOT і source worktree.

## Metadata, links та реальний SSR

Чинний PageMetadata дав expected descriptions до apply; supported subtitle leading strong зберіг незалежні H1. Global title extractor не змінено. Actual anonymous loopback GETs підтвердили три прямі HTTP 200, H1/title/breadcrumb/canonical/LearningResource identities, local `noindex, nofollow, noarchive` та **64 старі anchors** (21/22/21). Redirects/cross-canonical/коротші routes не створювалися; sitemap URL set не є ціллю зміни.

| Сторінка | Повні basic/detail SSR learning fields | Details початково closed | Власна практика / linked payload |
|---|---:|---:|---|
| Phrasal | 225 | 2 | 2/2/2 + 5/5 |
| Restrictions | 257 | 3 | 2/2/2 + 5/5 |
| Two-object | 213 | 2 | 2/2/2 + 5/5 |

Разом **695 learning fields**, включно з forms rules/outro, перекладами, таблицями, notes і всіма detail fragments. Звірено exact selected widget sequence, type, B1, ordered tokens без урахування регістру й multiplicity. Окрема strict capitalization перевірка виявила описаний вище inherited token-case дефект; її не підмінено цим receipt. Serialized answers не використовувалися для доведення видимості навчального тексту. Receipt SHA-256: `f97abf64fcc2712ebfe517c6ba67259d6ee4820766a8b101cc260475f1a10fd6`.

Після завершення formatter і всіх browser interactions той самий full SSR audit повторено: **3 HTTP 200, 695/695 fields, 64 anchors, 7 окремих closed details**, без втрати supported rules/outro/translations. Новий `live-pages-v2.json` SHA-256: `1c68daebaecc2707c82912a7c9d5febb6b98fd03f260d85a063309b4a9e45d26`. Strict case перевіряє окремий наступний finite diagnostic, а не case-folded SSR assertion.

Navigation зберігає 11 topics і exact explicit destinations між M52/M53/M54 та чинними сусідніми уроками. Окрема actual fixed-loopback GET процедура підтвердила **11/11 HTTP 200**, без redirects, failed URLs, cookies, proxy або production requests. Для трьох M54 pages вона також виконала **15 strict selected-token capitalization/condition checks** після bounded presentation fix, без SQL access/content writes. Receipt SHA-256: `78d8d87ff3b3923cf1c9d63535d3576e0c85358bd8a9920ffbe864498fb74055`. Повний навчальний зміст восьми сусідніх уроків цією процедурою не перевірявся. SSR не доводить browser layout/visibility, Check/Reset або token interaction.

## Ізольовані тести та невізуальна інтеграція

Нові PHP tests — 20: LocalGuard 3, ControllerCompatibility 2, ContentPatch 4, Package/H1/fidelity 4, SentenceBuilderContract 3, BuilderTokenPresentation 4. Вони покривають exact source/owner/locale/body/metadata/ancestry, відсутній contract, foreign bank, discovery невключеної цілі, stale plan/progress/question/source, partial release, rollback/exclusive backup, 9/10/9 actual slots і нуль question writes. Presentation cases перевіряють correct case, незмінність вихідних models, guard fallback та all-or-nothing відмову на drift. Спільний acceptance regression окремо перевіряє short pool, wrong bank/type/level, repeated IDs та duplicate widget.

| Набір | Результат | Межа |
|---|---|---|
| Фінальний combined: M54 new 20 + unchanged isolated production-profile/SEO 35 + theory/M52/M53/answer regressions 78 | **133/133 PASS, 25664 assertions** | 23 files, SQLite/array, in-process requests; не production network |
| M54 frozen native practice factory | **22/22 PASS** | 18 tasks, 36 options, 6 manual + 6 token inputs, 7 aliases, network 0 |
| Existing M53 frozen native practice factory | **22/22 PASS** | 18 попередніх tasks, 8 aliases, network 0; окремий незмінений файл |
| Existing offline M52/retry/contractions/practice completion | **385/385 PASS** | шість чинних JS suites; не browser acceptance |
| Historical literal-UI source assertions | **2 PASS, 4 FAIL** із 6 tests, 23 assertions | успадковані failures, не приховані й не виправлені M54 |

Разом **133 унікальних passing PHP tests** у фінальному combined run, child/wrapper exit 0. Раніші 51/3168 та 78/21560 batches складали 129 tests до token presentation fix; focused 5-test, 7-test і 8-test runs є повторами частини цих cases й не додаються до total. Чотири нові presentation cases підняли total до 133. Перед виправленим regression run була невдала CLI invocation з двома помилковими test filenames: PHPUnit виконав 0 tests; це не PASS і не failure навчального payload. Private evidence збережено, tests не редагувалися заради приховування результату.

Offline JS: **429/429 PASS** = M54 22 + окремий M53 22 + existing 385. Це непересічні test files; попередні runs не додано вдруге.

У кожному isolated runtime робоча `.env` не завантажувалася, DB — SQLite `:memory:`, cache/session — array; **46661 protected files, 0 changes**. Залишилася одна успадкована PHP 8.5 PDO MySQL SSL constant deprecation на відповідний batch. Production-profile assertions виконані тільки через локальний in-process test kernel.

Чотири historical literal failures відтворені окремо на M54 до невізуального token presentation hook: два `<input>` literal expectations не бачать чинний shared component/AttributeBag; span expectation не допускає чинний native class; choice expectation не допускає item-specific options. Source test LF SHA-256 `2e731050961d48982a33da3b3c90d0bd29a8fb9c3da0c13140b229628990dbc4` і native practice view LF SHA-256 `fe0f7e6d3c6ee1fad226e62bb611412c10bbef655d79c506177cf86a7ef8bce3` незалежно збігаються з accepted M53 Git blobs; обидва файли також незмінні після hook. Жодного з цих шести cases не видалено; дизайн не перероблявся заради literal snapshots.

Final combined 133 evidence SHA-256: `cc68a718877ca766c5dfc1f389a960f1669fc632dd5b7d65a613063baa31b08a`. Раніший new/production evidence: `c90c2e9221b5ddf54c2f847498cee07079da77749b691f14ede5e32f247e545d`; regression evidence: `b01b5069de970cb110abca2a3a57e8feb825363974b82500c933f0e9d1012085`; inherited literal baseline: `5b98269bea122226524bc9b841ab5a9d09c00e53e72def348a636ab6854bf59b`. Це приватні receipts, не файли для commit.

Невізуальні зміни обмежені finite M54 descriptor, thin shared planner/guard/explicit CLI wrappers, exact canonical registration і caller-owned course/standalone preservation. Shared renderer, answer engine та компоненти не замінювалися. Діагностичні profiles повторно використовують immutable M52/M53 procedures із SHA/literal-count checks; allowlist розширено лише трьома явними read-only M53 filenames. M54 SSR profile додає перевірку існуючих полів і point ownership, не нову поведінку сайту. Exact вилучення чотирьох M54 controller calls і двох null checks реконструює accepted M53 bytes; старі compatibility methods M53→M52→M51/M50 пройдено без переписування в приватному fixture.

## Browser acceptance — завершено

Реальне приймання виконано у Codex browser на gramlyze.loc. Переглянуто всі секції до footer, повні сторінки та окремі screenshots кожного viewport/theme state. Native grey borderless points, semantic labels, приклади з лівим синім акцентом, typography, spacing і sidebar залишено чинними. Наявність тексту доводиться видимим DOM/screenshots, а не serialized answers.

| Сторінка | 1440×1000 / 390×844 / 320×844 × light/dark | 6 власних tasks | Inputs manual/tokens desktop/mobile | Окремий widget | Details keyboard/deep-link/reload |
|---|---|---|---|---|---|
| Phrasal | 6/6 states | 6/6 correct після error/reset | 8/8 cases | 5/5 правильних | 2/2 independent; PASS |
| Restrictions | 6/6 states | 6/6 correct після error/reset | 8/8 cases | 5/5 правильних | 3/3 independent; PASS |
| Two-object | 6/6 states | 6/6 correct після error/reset | 8/8 cases | 5/5 правильних | 2/2 independent; PASS |

Разом **18 viewport/theme states, 18 власних tasks, 24 input task/mode/size cases, 15 перевірених widget answers**. Для кожної з дев’яти власних груп спершу надіслано дві конкретні неправильні/context-mismatched відповіді й отримано 0/2; після Reset — дві правильні й 2/2. Це 18 перевірених error attempts, а не заява про всі можливі комбінації помилок. Незалежність дев’яти scores перевірено. Обидва inputs кожної сторінки пройдено manual і token способами на desktop 1440 та mobile 390; phrasal mobile також прийняв заявлений contracted alias. Вибіркове видалення/повернення `noon.` і `sister.` відновило доступність відповідних токенів і правильну відповідь; вичерпні інші sync/retry cases залишаються в offline JS suite.

Усі сім details початково closed. Enter відкривав тільки власний point detail, Space закривав; видимість перекладеного змісту перевірено разом із ненульовою висотою. Три disclosures Restrictions у спільному блоці не відкривають один одного. Deep-link/reload збережених `#block-3244`, `#block-3271`, `#block-3302` повернув правильний блок нижче sticky header; H1 і widget залишилися правильними, details closed.

Manrope завантажено, broken images 0. Після фінальних widget взаємодій browser warnings/errors — 0. PPC Forms and Use, M52 formation та M53 Causative переглянуто read-only: правильний H1, завантажені fonts, три practice groups й один widget на кожній. Старі питання в цих references не проходилися й старий прогрес не скидався. Немає localStorage/session clear або зміни zoom; viewport capability повернуто до початкового default, тему — до початкової light. Одна створена M54 вкладка залишена як browser deliverable.

Widget acceptance не змішується з 18 власними tasks. Кожний окремий тематичний widget пройдено до **5/5**. Дві початкові widget відповіді й layout matrix були перевірені до чистого case formatter; решту 13 відповідей та affected fresh tokens — після нього. Formatter не змінює visual sources. В одному вже розпочатому Two-object attempt чинний engine відновив раніше збережений token bank із `Given`; ця відповідь прийнята чинним case-insensitive engine. Перехід Next отримав fresh data, подальший `given` підтверджено в lower case. Особистий attempt cache примусово не інвалідовано: виправлення exact 15 server payloads підтверджено strict HTTP audit, але не заявляється, що воно переписує вже розпочаті особисті token banks.

Невдала перша capture Two-object/320/dark показала ще light state; після явного підтвердження dark class screenshot виконано повторно, фінальний artifact правильний. Перша коротка keyboard спроба після viewport reset дала timeout; актуальний DOM залишався правильною сторінкою, фінальний screenshot отримано після свіжого H1 interaction. Ці transient automation failures не замінено вигаданими PASS.

Фінальний private browser receipt `browser-acceptance-v2.json` із фактичними DOM observations, screenshot hashes, interaction coverage й обмеженнями: SHA-256 `cc43441584130bf9fc42d2b9cd608a36c2b44e3022ed17ff4c663e63a0071519`. Screenshots та receipts не включаються до Git.

JS-off/print: **не перевірено**. Serialized answers не є доступним no-JS ключем; новий fallback engine не створено. Успадкований decorative shell overflow на 390 px — приблизно 10 px порожнього background, тоді як learning cards вкладаються в client width; широкі native tables мають власний horizontal scroll. Успадкований низький dark contrast жовтого level badge не виправлявся. Ці обмеження явно залишено поза scope; жодного CSS маскування overflow або редизайну не додано.

## Git-доставка та межі

Доставка: scoped staged review → `git diff --check` → scope/frozen-source validation → commit → normal push лише в `codex/seo-m54-passive-patterns-standard-view` → незалежна звірка remote SHA == HEAD. Exact commit і результат remote comparison наведено в кінцевому повідомленні; власний SHA не дублюється self-referential записом усередині commit. Main/production залишаються поза scope.

Не включати `.env`, секрети, dumps/backups, screenshots/proofs, private receipts, caches/generated assets, сторонні dirty changes чи тимчасовий route. Історичні звіти не переписувалися. Локальне впровадження не є доказом зростання Google positions/CTR/traffic.
