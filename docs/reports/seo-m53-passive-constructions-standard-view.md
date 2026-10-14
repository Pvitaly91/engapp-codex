# M53 — Causative, Get-Passive та Impersonal Passive

Статус: **локальну реалізацію завершено**. Редакційний review, frozen sources, guarded transactional apply, postconditions, два repeated no-op, реальні сторінки та browser acceptance виконані. Тимчасовий proof route видалено з підтвердженим GET 404. Повний delivery SHA і результат звірки remote після normal push наведено у фінальному повідомленні; production не змінювався.

## База, гілка та локальна ціль

- Прийнята база M52: `ba489be880d9a7c2b3dbc1698e887a05eb166fbe`.
- Робоча гілка: `codex/seo-m53-passive-constructions-standard-view`.
- Served application ROOT: `D:/DEV/htdocs/gramlyze.loc`; document root: `D:/DEV/htdocs/gramlyze.loc/public`.
- Джерело власних змін: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.
- Єдина ціль apply/HTTP/browser: [gramlyze.loc](http://gramlyze.loc). Production `.com`/`.ub`, main, PR, merge, force push і deployment не входять до цього етапу.

Fresh BEFORE зберіг шість локальних HTTP-відповідей зі статусом 200, Page/category identities, source/DB content, metadata/anchors, фізичні fingerprints і незалежний стан доступних course/standalone consumers. Наявність файлів іншого worktree не використана як доказ стану сайта. Source sync не писав у БД; погоджений запис відбувся лише наступним явним guarded apply.

## Три сторінки

| Сторінка | Page / category | Чинний H1 | Hero / практика | Continuing blocks | Meaningful details |
|---|---|---|---|---:|---:|
| [Causative — have/get something done](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-causative) | 92 / 1 | Каузатив (Causative) | B1–B2 / B1 | 10 | 2 |
| [Get-Passive — Розмовний пасив](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-get-passive) | 95 / 1 | Get-passive | B1 / B1 | 9 | 1 |
| [Impersonal Passive — Безособовий пасив](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-impersonal-passive) | 96 / 1 | Безособовий пасив (Impersonal Passive) | B2–C1 / B2 | 10 | 2 |

Exact UK owners:

- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceCausativeTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceGetPassiveTheorySeeder`.
- `Database\Seeders\Page_V3\PassiveVoice\Basics\PassiveVoiceImpersonalPassiveTheorySeeder`.

Slugs з префіксом `theory-`, category ancestry `passive-voice`, raw Page titles, continuing UUID/index/anchors і різні technical levels збережено в versioned projection. Зокрема continuing block 5 Causative та block 4 Get залишаються B2; display ranges не використовуються для обходу exact practice CEFR. Кожній сторінці додається один окремий practice-set, а не заміна footer чи продовження довільної кількості старих slots.

## Редакційні зміни

Causative: have/get + object + V3 пояснено через організатора/отримувача, об’єкт і виконавця. Уточнено have/has/had, get/gets/got, do-support, continuous/perfect/modal forms, short answers і wh-питання. Граматичні Past Perfect та інша have-модель не залишені як фальшиві wrong: їх перенесено до контекстного порівняння. Небажані події не названі замовленням або граматичним запереченням; переклад broken into передає проникнення в автомобіль, не поломку. Прибрано абсолютну шкалу формальності й висновок про зусилля за самим get.

Get-Passive: збережено негативні, позитивні та регулярні події; розрізнено get + V3, get + object + V3 і get + прикметник/стан. Simple питання й заперечення використовують do/does/did + get; continuous має повне допоміжне. Уточнено got/gotten за BrE/AmE та відмінність події від тривалості стану. Were married last June і contract got signed не оголошені неграматичними через можливу іншу конструкцію або жанрову рекомендацію. Get не подано як доказ вини.

Impersonal: дві reporting-моделі та структурне it; час повідомлення відокремлено від часу події. Simple infinitive не обмежено одночасністю, perfect не вибирається лише за минулим прислівником. Перетворення зберігають reporting verb/час, аспект, учасників і потрібне джерело. Reports say → is reported не видано за дослівно тотожне перетворення. Уточнено not-known scope, supposed to, alleged/claimed/known і межі достовірності; фальшиву стару correction is said + was working прибрано. Звинувачувальні приклади замінено самодостатніми вигаданими ситуаціями без реальних названих осіб.

Коректні старі приклади не переписувалися лише заради новизни. Повний поурочний mapping збереження/виправлення/доповнення/заміни та причини кожного detail міститься в `docs/content/m53-passive-constructions-editorial.v1.0.0.md`; читабельна повна редакція — `docs/content/m53-passive-constructions.v1.0.0.uk.md`.

## Компоненти та basic/detail

Чинний шлях збережено: `theory.show → theory.partials.content-block → TheoryContentSource → TheoryContentRenderer → theory.content → theory.components.*`. Контент використовує hero, forms-grid, usage-panels, comparison-table, mistakes-grid, summary-list, navigation-chips і practice-set. Немає окремих M53 views/styles, нового grader або frontend engine.

Usage-пункти мають явні labels/accents, правило та EN/UK приклади. Не створено анонімних карток на кожне речення. Основні правила, потрібні переклади, короткі застереження та посилання залишаються в basic. П’ять disclosures мають семантично визначених власників:

| Detail | Власник |
|---|---|
| Річ зазнає дії, людина її виконує | `causative-services /sections/1` |
| Perfect каузативу й досягнення результату | `causative-tenses /items/3` |
| Перехід у стан і тривалість стану | `get-tenses /sections/3` |
| Раніший процес і пасивний інфінітив | `impersonal-infinitives /sections/1` |
| Що саме заперечує not | `impersonal-more-forms /items/7` |

Це explicit finite mapping, не глобальний поріг слів і не квота кнопок. Нові власні sources нормалізовано до LF/одного EOF; active definitions — до їхнього погодженого CRLF/одного EOF. Історичні bytes не нормалізувалися.

## Власна практика та окремі конструктори

На кожній сторінці **2 selects + 2 choices + 2 inputs**: разом 18 власних завдань у дев’яти групах. Кожний із шести inputs — одне завдання з manual/token способами, не два завдання для статистики. Українські умови видимі через чинні bindings, slash-banks перемішані, готової відповіді в placeholder немає. У meaning tasks точний переклад, який розкривав би відповідь, показується після Check; достатній UK контекст доступний одразу.

Окремі widgets: **5 Causative + 5 Get + 5 Impersonal**, не частина 18 авторських tasks. Незалежний contract: `docs/content/theory-sentence-builders/m53-passive-constructions.v1.json`.

| Сторінка | Тематичний банк | Походження відібраних 5 |
|---|---|---|
| Causative B1 | `PolyglotCausativeHaveGetLessonSeeder` | 5 чинних |
| Get B1 | `PolyglotPassiveVoiceGetPassiveAllLevelsLessonSeeder` + `M53GetPassiveB1SupplementSeeder` | 2 чинних + 3 нових |
| Impersonal B2 | `PolyglotPassiveVoiceImpersonalPassiveAllLevelsLessonSeeder` | 5 чинних |

Загалом **12 reused + 3 new**, а не три нові банки й не 33 нові питання. Початкову Get-п’ятірку не прийнято механічно: вона містила adjectival got lost і невідповідність locked/closed між UK умовою та ключем. Мінімальні три нові питання перевіряють запрошення-питання, заперечення відбору й регулярну оплату. Вони додаються лише до exact UK Get practice; старі questions/answers/options/links не переписуються або видаляються.

Застосований supplement використовує **28 різних наявних shared option rows для 29 spellings**; case-insensitive Did/did не створюють дубль option і не змінюють старий рядок. Нових options — 0. П’ять історичних англомовних Causative hints мають finite UK render-only projection лише для підтвердженого canonical theory binding: оригінальні Question/Hint models і старі DB hints не мутуються. Це не зміна answer engine або standalone consumers; українську підказку додатково побачено в реальному widget після Check.

## Frozen sources та checksums

Редакція: **1.0.0**. Публічний checksum manifest `docs/content/m53-passive-constructions-checksums.v1.json` містить 13 exact hashes; guarded preview має 56 source pins (із потрібними приватними receipts), а source sync — 31 явно обраний шлях. Ці числа означають різні переліки, їх не змішано.

| Artifact | SHA-256 |
|---|---|
| `docs/content/m53-passive-constructions.v1.0.0.json` | `b140410b36b3e619fb6672cade249f9cf7fc1913a63993c88e35262b260f54d3` |
| `docs/content/m53-passive-constructions.v1.0.0.uk.md` | `f8bf0d6826b1b8d7b4a8cf881376094156aedab493c7ba8f3a66fee1b9cecb12` |
| `docs/content/m53-passive-constructions-editorial.v1.0.0.md` | `d7fe3f3059544db71b5e1ad188a8af74901f49d8ac597baf8f0948de6387d5fc` |
| `docs/content/m53-passive-constructions-components.v1.json` | `d38abf2807a60dcfd8427bf97911a4c9ea0479dcfedb250b64c7ca3aaa62aeeb` |
| `docs/content/m53-passive-constructions-bank-audit.v1.json` | `48e3b188b791ac0e98538da82fb564c01181fe2c60ed42fa238f2a31992add67` |
| `docs/content/m53-get-passive-question-supplement.v1.0.0.json` | `1c23bf05b8db505d737a2b6013c8c3680cf60047170f1a0f69477689678e7476` |
| `docs/content/theory-sentence-builders/m53-passive-constructions.v1.json` | `883241d932a2f8dfeee5089ac3c0a4b55cb621e6cf941150415c21fa1dd57531` |
| `database/content-patches/m53-passive-constructions-before.v1.json` | `052e8c1390339103d8a3e9d144e5361696213b57ce75ae52722dc58319017883` |
| `database/content-patches/m53-passive-constructions.v1.json` | `bf386f8f1880921e7dc144dec695e30d9da0335bf277b08aef6f35d37e0dca7d` |
| New Get supplement `definition.json` | `bd93d3d9ca293e32da87ccca347b70ad34deb6672bdb373b296dd7fd5e119fa6` |
| Causative `definition.json` | `17b2bc97237ccbdfc56739dbdc22ff8c8588fe0e530461c8df240f1e59f5fad7` |
| Get `definition.json` | `c9c9304a5929875228ae3541bbda27747485abebed876cff5fbddb64eabc336e` |
| Impersonal `definition.json` | `b051942805543ea856e7454367c8973f20f8b68f11b0217b71984e314897ffdb` |

Фактичний fresh SQL тип body — TEXT, межа 65535 UTF-8 bytes. Найбільші погоджені bodies: Causative practice 10877, Impersonal practice 10772, Get practice 9737 bytes. DDL не змінювався; сотні дубльованих feedback keys не додавалися. До freeze додано лише чотири finite punctuation-free feedback keys, бо чинний engine вже приймав ці відповіді, але без exact key втрачався повний двомовний feedback.

## Невізуальна інтеграція та preservation

- `M53PassiveConstructionsPackage` — finite profile exact owners/IDs/levels/артефактів. Спільний `TheoryNativeContentRelease` повторно використовує чинний M49 serializer та canonical components; не дозволяє payload вибирати довільні views/styles. Старі frozen packages/maps не розширені.
- `TheoryNativeContentPatch` повторно використовує M26 locks, stale-plan equality, exclusive backup і transaction; release надає дані та exact scope, не власний writer pipeline. `TheoryLocalReleaseGuard` використовує чинну фізичну/local-only proof policy.
- `M53QuestionInsertScope` — лише три reviewed Get inserts з exact evidence, без масового reseed або зміни старих shared rows. IDs самі по собі не виключають рядки з protected fingerprint.
- `M53BuilderHintPresentation` — guarded clone-only UK hints; non-theory caller або stale/foreign relation отримує історичний payload без часткового перекладу.
- Canonical source/adapter/renderer registration та course/standalone preservation зберігають caller-owned межу. Для наявного note використано чинне native `hint_html`, без CSS/Blade/JS/grader redesign. Null-chain checks не дозволяють наступному preservation hook отримати null.

Source-sync receipt підтвердив **0 DB writes, 0 HTTP requests**, 31 явно обраний шлях. Фінальний read-only audit після apply та браузера повторно підтвердив **0 visual source changes** для 326 файлів і незмінність **2249 historical frozen sources** від fresh BEFORE. Незмінні також 58 EN/PL blocks, старі question payloads і timestamps у погодженій BEFORE/AFTER-проєкції.

Окремий old-consumers audit підтвердив 27 локальних HTTP 200, сім saved tests і точний payload 480 старих питань. Course/standalone help зберігають live BEFORE; новий M53 контент не поширено на ці consumers. Receipt SHA-256: `abece70242a277d92738093b6692fa2c504ce14b6023749cd62c22d6103a52b6`.

Served ROOT має сторонні dirty зміни: загальний ROOT↔Git parity не заявляється. Зокрема ROOT practice-set містить сторонній історичний `input.prompt`; його raw hash `05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e` відповідає fresh BEFORE і не був переписаний M53. Власний worktree practice-set LF hash `fe0f7e6d3c6ee1fad226e62bb611412c10bbef655d79c506177cf86a7ef8bce3` збігається з accepted M52. Відмінність не прихована твердженням, що все оточення тотожне.

## Guarded apply, postconditions і repeated no-op

Fresh proof та reviewed exact `preview-v2` передували exclusive `backup-v2` і transactional apply. Приватний preview digest: `c41d7a3fa7d125d908b12c22e469b229dd4e6c69cf91f48f21afd7607b4180fb`. Реальні результати apply збіглися з reviewed scope:

| Сімейство | Фактичні updates | Фактичні inserts |
|---|---:|---:|
| `pages.text` | 3 | 0 |
| Наявні UK `text_blocks.body` (3 subtitles + 29 continuing) | 32 | 0 |
| Нові UK practice blocks | 0 | 3 |
| `questions` | 0 | 3 |
| `question_options` | 0 | 0 |
| `question_answers` | 0 | 26 |
| `question_option_question` | 0 | 35 |
| `question_theory_text_blocks` | 0 | 3 |

Разом 35 updates = 3 Page.text + 32 UK bodies; три нові practice blocks та окремий мінімальний question supplement. Deletions — 0. Немає migrations, truncate, full restore, автоматичних HTTP/startup apply hooks або progress/session cleanup. Guard містить 46 protected table fingerprints; лише конкретно погоджені нові supplement rows відокремлюються від non-target preservation.

Два повтори підтвердили no-op: із початковим reviewed preview та зі свіжим AFTER `after-v1`. Для кожного контентного й question/answer/option/pivot/link сімейства — **0 inserts / 0 updates / 0 deletes**. Невикористаний backup під час no-op не створювався.

Final read-only audit `final-readonly-v2` підтвердив точну погоджену проєкцію в усіх 46 таблицях, збереження старих рядків/timestamps поза exact allowlist, 58 EN/PL rows, visual/frozen sources і source sync. Receipt SHA-256: `0aa7a6e44392b697ba155e05782071920c57ce6a9485f234191fce8a7ac63dbd`.

Після apply/no-op тимчасовий nonce route видалено. Окремий GET повернув **404**; redirects не використовувалися. ROOT routes file повернуто byte-exact до BEFORE SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`. Cleanup receipt SHA-256: `6fb3273b8f7913102a815cd160eee6dbfbc15786be24a806c946de5c4338239a`. Route не є частиною доставки.

## SEO, metadata й джерела

Raw title і фактичний H1 не ототожнено: збережено старі Page titles та незалежні BEFORE H1, breadcrumb/title/canonical/LearningResource. Subtitle leading strong використовує чинний extractor, нового глобального M53 title hook немає. Navigation labels отримали exact чинні destinations; немає redirects, cross-canonical або коротших вигаданих slugs. Local noindex, schema types та набір sitemap URLs не є ціллю зміни.

Свіжі анонімні loopback GET без redirects підтвердили 200 на всіх трьох сторінках, один правильний H1, збережені breadcrumb/title/LearningResource identities і всі 65 старих anchors (22/21/22, жодного відсутнього). Перевірено **697 авторських basic/detail SSR learning fields**: 229 Causative, 229 Get, 239 Impersonal. П’ять details повністю присутні в серверному HTML та початково закриті; власні groups 2/2/2 і linked payload 5/5 правильні на кожній сторінці. Receipt SHA-256: `f01e796e15df42f60836fe68be660849dd29da9695536d07744ade3389bae62c`.

Чинний metadata service дав до apply та HTTP підтвердив після apply такі descriptions:

| Сторінка | Description |
|---|---|
| Causative | Causative — have/get something done. У конструкції have/get + object + V3 між have/get і третьою формою стоїть те, що зазнає дії. |
| Get-Passive | Get-Passive — Розмовний пасив. Get + V3 часто використовується в розмові, коли важлива подія або те, що сталося з підметом. |
| Impersonal | Impersonal Passive — Безособовий пасив. Безособовий пасив передає те, що кажуть, вважають, повідомляють або очікують. |

Локальний robots header лишився `noindex, nofollow, noarchive`; meta robots відсутній, як у BEFORE. Існуючі production canonical strings прочитані з локального HTML і збережені; жодного запиту до `.com`/`.ub` це не означає. Exact navigation URL strings підтверджені sources/SSR; availability всіх інших destinations поза трьома targets цим SSR-аудитом не перевірялася. Локальне приймання не є доказом зростання позицій Google.

Перевірені першоджерела та actual access зафіксовані у master і читабельній редакції. Cambridge прямі запити дали 403/tool error; використано publisher-indexed редакційний текст, без заяви про повне пряме читання. British Council Advanced passives review і Google Search Central прочитано як direct publisher HTML. Основний BC текст і підписану відповідь LearnEnglish Team відокремлено від учнівських коментарів. Видавничі вправи, вигадані рецензенти, рейтинги, FAQ schema та декоративні updated dates не додавалися.

## Перевірки: фактичний стан

- Existing offline JS suites: **131 PASS**; M53 offline native practice state: **22/22 PASS**. Це окремі набори, повторні runs не рахуються як нові тести.
- M53 охопив 18 IDs, 36 visible options, 6 manual + 6 token inputs, 8 declared aliases, punctuation-free форми всіх aliases, empty/partial/wrong/repeated wrong/edit-correct/recheck/reset/delete/return/rebuild і незалежність scores. Network calls — 0; engine source hashes захищені. Це offline factory checks, не browser visibility/widget grading/DB acceptance.
- Перший isolated PHP batch: **86 tests, 20171 assertions, 4 errors + 5 failures**; це не PASS. Чотири errors спричинили власні DRAFT artifacts у discovery — main прибрав лише свої тимчасові DRAFT. Одна failure виявила не відображений note — main переніс його в чинний native `hint_html` mapping, не переписуючи frozen навчальний текст.
- Інші чотири failures — успадковані literal practice-UI assertions. Самі старі тести не переписані: їхній LF hash `2e731050961d48982a33da3b3c90d0bd29a8fb9c3da0c13140b229628990dbc4` збережено; worktree practice-set збігається з accepted M52 LF bytes. Вони не оголошені виправленими або автоматично PASS.
- Другий isolated PHP batch без історичного literal-UI файла: **80 tests, 21057 assertions — 79 PASS, 1 FAIL**. Єдина failure була test-only DOM whitespace перед крапкою після inline anchor; виправлено лише нормалізацію тексту тесту. Focused повтор цього case: **1/1 PASS, 790 assertions**. Отже, **усі 80 унікальних PHP tests пройдені через виправлений повтор**; 790 повторних assertions не додаються до 21057 як нові унікальні перевірки.
- Фінальний повний повтор виправленого набору: **80/80 PASS, 21604 assertions**, PHPUnit exit 0. Ізольоване SQLite/array оточення не завантажувало робочу `.env`; **46661 protected files, 0 changes**. Залишилася одна успадкована PHP 8.5 deprecation для PDO MySQL SSL constant. Ці 80 tests не додаються повторно до кількості унікальних tests.
- Історичний literal-UI файл містить **шість**, не вісім tests: два проходять, чотири успадковано падають. Файл і accepted M52 view bytes залишилися незмінними; ці шість cases не входили до другого діагностичного batch. Чотири failures **не виправлені й не позначені PASS**. Перший run збережено, snapshots не оновлювалися для приховування результату.
- Read-only package audit `php artisan theory:audit-sentence-builders --target=gramlyze.loc --source=m53-passive-constructions.v1.json`: **3/3, zero failed**; receipt SHA-256 `1004ca2f543b4d97bdb014ba95ab32cb00b83beef24ae109f19b52cb45faf3c9`.
- Full dynamic registry `php artisan theory:audit-sentence-builders --target=gramlyze.loc`: **82/82, zero failed** — усі 79 попередніх owners і три нові. Receipt SHA-256 `55db4b87dcf838821b97e5790d8fb6768e025cdba04750ae8e1c2085fccc175d`. 82 не зашито як постійний поріг. CLI presence/selection не названо browser acceptance.
- Read-only `m53-live-pages` diagnostic main виправив щодо actual DOM ancestor практики й token arrays; це не зміна візуальних sources або навчального payload.

## Live browser acceptance

Пройдено **всі 18 target states**: три сторінки × 1440×1000 / 390×844 / 320×844 × light/dark. Переглянуто всі секції до footer, H1, assets/fonts і компоненти. Broken assets не виявлено; body font — 16 px, як у read-only PPC-еталоні. Main learning content overflow — 0 у прийнятих стабільних станах.

Усі **18 власних tasks** прийняли правильні відповіді. Шість inputs пройдено manual/tokens на desktop/mobile (1440/390): **24 task/mode/size cases**; дві явно погоджені повні negative aliases теж прийняті. На Impersonal додатково перевірено empty/partial/repeated wrong/recheck/reset, delete/return обох окремих is instances та незалежність score. Це не твердження, що всі error-state сценарії повторено в UI для кожного з 24 cases; повніше finite state coverage окремо дає offline suite.

| Сторінка | 6 viewport/theme states | Усі 6 власних tasks | Inputs manual/tokens desktop/mobile | Widget server/visible/interaction | Details |
|---|---|---|---|---|---|
| Causative | 6/6 | 6/6 | 8 cases PASS | 5/5 payload; visible; перша correct → Next 2/5 | 2/2 PASS |
| Get-Passive | 6/6 | 6/6 | 8 cases PASS | 5/5 payload; visible; перша correct → Next 2/5 | 1/1 PASS |
| Impersonal | 6/6 | 6/6 | 8 cases PASS | 5/5 payload; visible; перша correct → Next 2/5 | 2/2 PASS |

Усі п’ять point-level details початково закриті; Enter/Space перевірені для кожного, відкривається тільки власний point, текст повністю видимий. Deep link і reload зберігають потрібний block, disclosures знову закриті. У кожному з трьох widgets перевірено першу правильну відповідь і Next до 2/5; **не заявляється повне UI-оцінювання всіх 15 bank answers**. Українську Causative hint видно після Check.

Read-only регресії: PPC Forms на desktop і 320 px — той самий 16 px body, три practice groups і один widget; існуючий shell overflow 12 px. M52 Formation зберіг H1, font, groups і widget. Масштаб браузера не змінювався; viewport reset та початкова light theme відновлені. Особистий старий localStorage/cookies/progress не очищалися. Фінальний screenshot `m53-result.png` лишився приватним і не входить до commit.

JS-off і print: **not checked**. Serialized answers не називаються no-JS ключем; новий fallback engine не створено. На targets після стабілізації viewport залишався невеликий shell overflow приблизно 0–12 px; перехідні raw resize values не видаються за стабільні виміри. Низький dark pill contrast та успадкований shell overflow **не виправлялися** цим контентним завданням; learning overflow не приховувався CSS.

## Доставка

Локальні apply, no-op, preservation та live acceptance завершені. Доставка: exact staged review і `git diff --check`, scoped commit та normal push у `codex/seo-m53-passive-constructions-standard-view`, потім незалежна звірка remote SHA == HEAD.

Повний SHA цього delivery commit і фактичний результат звірки remote наведено у фінальному повідомленні. Самопосилальний SHA не записується всередину власного commit; його можна отримати через `git log -1 --format=%H -- docs/reports/seo-m53-passive-constructions-standard-view.md` у доставленій гілці.

Не включати `.env`, секрети, dumps/backups, screenshots, приватні receipts/proofs, caches, generated assets, сторонні dirty зміни або тимчасовий nonce route. Деплой не дозволений цим commit/push.
