# M56 — Passive Gerund / Present Simple Passive / Past Simple Passive

Статус: три уроки застосовано й прийнято на `gramlyze.loc`; поточна авторська версія — `1.0.1`. Окреме href-only виправлення застосовано `4/0/0`, усі три фінальні повтори — no-op `0/0/0`; тимчасовий proof route видалено з exact byte restoration та окремим GET 404. Фінальні SQL/SSR, consumer preservation, ізольовані тести та браузерна матриця завершені й наведені нижче. Відомі успадковані обмеження наведено окремо. Commit, що містить цей звіт, є scoped delivery M56; повний SHA і незалежне remote-порівняння повідомляються у фінальній відповіді, щоб не створювати self-referential hash у commit.

## База та межі

Accepted base: `fff71b7daa371497b3df100c2d97d108058dc175` (M55). Робоча гілка: `codex/seo-m56-gerund-simple-passives-standard-view`.

Єдина HTTP/браузерна й робоча DB-ціль — `http://gramlyze.loc`. Actual served application ROOT: `D:/DEV/htdocs/gramlyze.loc`; source worktree: `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`.

M56 змінює рівно три наявні UK owners. Не створює нових Page, category, URL або навчального шаблону.

| Урок | Page / category | Ancestry | Local URL |
| --- | --- | --- | --- |
| Passive Gerund | 102 / 17 | passive-voice → passive-voice-infinitives-gerund | [Відкрити](http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-gerund) |
| Present Simple Passive | 113 / 1 | passive-voice | [Відкрити](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-present-simple) |
| Past Simple Passive | 110 / 1 | passive-voice | [Відкрити](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-past-simple) |

Exact identities:

- `Database\Seeders\Page_V3\PassiveVoice\InfinitivesGerund\PassiveVoicePassiveGerundTheorySeeder`
- `Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePresentSimpleTheorySeeder`
- `Database\Seeders\Page_V3\PassiveVoice\Tenses\PassiveVoicePastSimpleTheorySeeder`

Для двох цільових M56 simple-уроків директорія `Tenses` у source не додає category до URL: Present/Past мають flat ancestry `passive-voice`. Інші сім сусідніх tense-уроків фактично належать вкладеній категорії `passive-voice-tenses`; їхні exact destinations виправлено окремою версією 1.0.1. Gerund є погодженою ціллю M56: історичний M55 read-only snapshot Gerund не переписується й не використовується як вимога зберегти його старий навчальний зміст. Натомість три accepted M55 pages і category learning description захищає новий незалежний M56 BEFORE.

## Незалежний BEFORE

Свіжий `before-v1.json`: SHA-256 `8198c1bcff87fbf270ab5d71a7d6c3343b728846ce9cc7be59e1ff44e5bee406`.

Це нове вимірювання actual source/DB/HTTP, не копія M55 identity: 6/6 anonymous loopback GET повернули 200; 46 SQL tables; 326 visual sources; 2292 frozen/non-target source files. Collector працює через READ ONLY / ROLLBACK, не запускає seeders і не записує learning/progress rows.

Свіжий `before-old-consumers-v1.json`: SHA-256 `8f6d8b9b415a28229eef8b11ad9029867f84beaeec636ab1c65cd3e6609b1c78`. Фактично 28 HTTP 200, 6 saved tests / 432 original questions, 0 DB writes. До списку входять старі M56 course/standalone consumers, три M55 target learning boundaries і category learning description; Gerund не додається як четвертий immutable neighbor.

Окремий pre-sync matcher/helper/routes baseline `extra-source-before-v1.json`: SHA-256 `535e5c7809f42f3da7e0ee6affc5aabcf4294c9d91d1ef70b03d75683ba13092`.

Actual continuing source slots: Gerund 10, Present 9, Past 10; subtitle row є окремим. Gerund має levels `B2/B2/B2/C1/B2/B2/B2/B2/B2/null`; Present — 9 × A2, Past — 10 × A2, включно з наявним navigation metadata. UUID/source index/type/column/level/anchors continuing slots зберігаються; практика додається окремо, не замість navigation.

Gerund BEFORE має plain subtitle `<p>Passive Gerund — Форми being done і having been done.</p>`: raw body SHA-256 `fbc30cdfc88a1916b9763c4c53f132947f4341bde9b76e03f99d5eb51b42660e`. Actual чинний extractor і HTTP H1 — `Passive Gerund`. Це власний exact-bound captured-plain opt-in, не перенесений M55 Infinitive binding і не глобальна зміна title extractor. Present/Past actual H1 — `Present Simple Passive` / `Past Simple Passive`.

## Редактура, freeze та практика

Спочатку заморожено авторську версію `1.0.0` після незалежного review проти source/DB/HTTP BEFORE: граматика, переклади, час, ролі, A2 доступність, native fields, 18 own keys та 4 meaningful details. Чотири погоджені wording уточнення внесено до freeze: запит про того, кого просять допомагати, не підмінює request обов’язком; виправлено переклад workshop і два англомовні допоміжні пояснення A2. DRAFT artifacts залишено поза active release discovery. Версія `1.0.1` є явним URL-only successor після окремого exact-diff review: навчальні формулювання, keys/aliases і basic/detail розподіл не змінені, frozen 1.0.0 не переписано.

Основні редакційні зміни:

- Gerund: `being / having been + V3`, обидва заперечення, verb/preposition patterns, власний учасник конструкції, спогад і контекстний час. Відокремлено gerund-доповнення від participle clause, прикметник `wrong` від пасивного V3; `worth reading` не названо помилкою. Perfect не нав’язується після after і не проголошується самостійним доказом причини.
- Present: am/is/are за підметом, singular you, V3 без зайвого -s, прозорі регулярні дії, заперечення, питання/короткі відповіді з правильним мовцем; стан і процес зараз зіставлено без модернізації сусіднього уроку.
- Past: was/were за підметом, V2/V3 і неправильні дієслова, одна подія та повтор у минулому періоді, питання/заперечення без втрати події. Надіслання не підміняється отриманням; запрошення не доводить присутності. Present/Past practice не є парними копіями зі зміненим yesterday.

Усі англійські навчальні речення мають український переклад у відповідному native полі. У шести meaning choices точний переклад, який розкривав би ключ, показується після Check; український критерій видимий одразу. Редакційні джерела й фактичні способи доступу збережені в master/editorial: direct reading, publisher-indexed body, 403 та tool error не змішані; видавничі exercises не копіювалися.

Історичний frozen manifest `docs/content/m56-gerund-simple-passives-checksums.v1.json`: SHA-256 `550fee08ad4dae0316a8eaa01de4dd93b7c1bc8b61a1eddc8ad2e817b3547944`. Його 11 raw hashes описують 1.0.0: master `5ba3c44ec4d039ee84c45284c6752f08b1c5ecdfe28ec908da11fe02e2363a51`, original public BEFORE, native patch `4593288480b9edf60336e640991225bc78e6045c9c0e07d574701f0697942d4a`, contract, bank audit, readable UK, editorial, component map і тодішні три active definitions. Усі вісім frozen learning artifacts та сам manifest збережені byte-for-byte. Два active Present/Past definitions тепер мають successor SHA з нового manifest; це не приховано зміною старих checksum entries. Нові author artifacts — UTF-8/LF/один EOF; active definitions зберегли CRLF/один EOF; історичні frozen bytes не нормалізувалися.

Поточний manifest `docs/content/m56-gerund-simple-passives-checksums.v1.0.1.json`: SHA-256 `4fd7548277e5ab76d73ac18fd0b17d619c1e5b5ed9f58cc9e4add240c5dde867`, 15 перевірених entries. Поточний master `docs/content/m56-gerund-simple-passives.v1.0.1.json` має SHA-256 `e0d9563ba73aa78140f5e1e473a41949985970a65ab70c9db60aecc2b516e3fb`; cumulative native patch `database/content-patches/m56-gerund-simple-passives.v1.0.1.json` — `3d2b4757255d9dde7ecb9826b1e37ef23e71cf3d985bed3a06479a3d978ef1ab`.

Початкова незалежна pure projection `author-projection-v1.json`: SHA-256 `fd4fb7f543abd04705a6a86dc43304d63f2c5c68c69ff73d5ea07aacbf14793e`. Для successor повторено `author-projection-v2.json`: SHA-256 `818d0099337ce8ca3d2eb9575e7411c1dbf80c3e147019adaa9638752384fce5`. Targets, original BEFORE та expected metadata точно збігаються з 1.0.1; єдина додаткова різниця форми full patch — top-level `version: 1.0.1`, яку початкова pure-процедура не додає. Не заявляється byte equality цих двох top-level objects. Expected SEO descriptions обчислені чинним сервісом до apply й не змінені href correction. Найбільший body — 9586 UTF-8 bytes при actual SQL TEXT capacity 65535; DDL не було.

Практика кожного уроку: чинний native `practice-set`, 2 selects + 2 choices + 2 inputs із manual/token шляхами; окремо один `text-block-practice-questions` widget із п’ятьма тематичними type 4 питаннями. Gerund — B2; Present/Past — A2; не розширюється CEFR filter заради п’ятірки.

Чотири details мають явні власні пункти: `gerund-time-context` → `gerund-simple /sections/2`; `gerund-negation-scope` → `gerund-patterns /sections/1`; `present-state-process` → `present-negative /sections/0`; `past-event-inference` → `past-affirmative /sections/0`. Це окремі змістовні поглиблення, не глобальний word-count threshold.

Банки: 15 reused questions, 0 new questions/answers/options/links. У кожному exact type 4/CEFR pool — 12 технічно eligible records; семантичну придатність і фінальну п’ятірку перевірено окремо. Gerund обрано IDs `39123/39125/39126/39128/39130` (`01/03/04/06/08`): like, without, remember, subject, proud of + perfect; це не п’ять finite passive речень або лише початкові participle clauses. Present — `36003–36007`; Past — `36075–36079`. Actual original conditions, keys, ordered tokens, options і hints збережені в audit `ee7caf9cc71f872d1a2cf5a97e3f80488016b67b3ba9ff68931873a625c34faf`; pending порожній. Own 18 tasks та reused 15 widget questions — різні totals.

## Мінімальна невізуальна інтеграція

- `M56GerundSimplePassivesPackage` — finite три-owner/locale/body/metadata policy поверх чинного `TheoryNativeContentRelease`; власний exact captured-plain Gerund BEFORE. M55 neutral component mapping і plain Infinitive guard не змінюються й не копіюються на M56.
- `TheoryContentSource` / `TheoryHtmlAdapter` — реєстрація точного frozen M56 presenter/design policy; нова в’юха або CSS не додаються.
- `TheoryLegacyAdapter` — M56 correction data до підтриманої native mistakes model; не нове оформлення.
- `TheoryCourseManifestService` / `TestJsV2Controller` — existing clone-only preservation seam для course і standalone theory-help; original models не мутуються, нова theory practice не просочується у виключені consumers.
- `M56ContentPatch` / `M56LocalTargetGuard` / CLI — finite data policy над чинними guarded planner, exclusive private backup і M26 transaction, без нового writer framework.
- `M56HrefCorrectionPatch` / `PatchM56Hrefs` — окремий finite zero-insert перехід із уже застосованого 1.0.0 до 1.0.1. Повторно використовує M26 transaction, candidate/stale-plan guard і exclusive backup; original native planner не послаблено й другу practice не додає.
- Діагностика — тонкі literal/hash-bound profiles immutable M52/M53 read-only процедур; full-copy pipeline або автоматичні apply hooks не створюються.
- `M56BuilderSelectionPresentation` — exact canonical-theory practice/owner/locale/category/body/contract binding. Через чинний clone/WeakMap seam й одну matcher exclusion call відбираються погоджені Gerund `01/03/04/06/08`; чотири українські context prefixes додаються лише до cloned question presentation. Original question models, їхні ключі/links та grader не мутуються; післяфактум заміни випадкової п’ятірки немає. Новий M56 case formatter не створювався; M53 hints, M54 bounded case presentation і M55 contexts збережені. У sync/source pins включені matcher, linked-practice seam, helper і renderer.

Зміна дизайну не входить у scope. Blade, CSS, frontend JS, grader, шрифти, header/sidebar/background, масштаби, .env, hosts, Apache/XAMPP config і ACL не змінюються. Немає production .com/.ub, SSH, deploy, PR, merge, force push або main changes.

## Guarded apply та приймання

Source sync receipt `source-sync-v1.json`: SHA-256 `f91590dfbd62411d44d0484d6b5613de90e28c68af056de13b5e9aac06f503c5`, 27 exact runtime/content paths. Shared файли перенесено власними exact hunks зі збереженням стороннього ROOT controller content і EOL; ROOT controller-after `9f17539f795107553a5d4b6994ea8acf9bff58d5ddea22eeff08e170bdd7a3cc`, WT controller-after `36f02df67681d0262c039108e32fee64110c0acccdd03702cf61a30c401ae43a`. Зворотне вилучення чотирьох M56 calls/двох null checks відтворює accepted M55 WT bytes. 326 visual sources і 2292 historical frozen/non-target sources звірено без змін.

Свіжий web/CLI/physical proof: nonce `40c262d4f98fa666daa31c34330e819c`, receipt SHA-256 `3b1d91d05e3edcde1e04f553cdcde331a97d8e5859ddfc612c91295ac49bb2ef`. Actual ROOT/public і loopback MySQL `localhost:3306/gr2` підтверджено заново. Наявні APP environment `production` та SiteMode `development` не змінювалися: це локальний runtime, не звернення до production домену. Тимчасовий endpoint — тільки nonce GET на `http://gramlyze.loc`, loopback, без cookies/auth/query/forwarding, секретів і SQL writes.

Reviewed preview digest `89633674fd38791d061ab332cca181044f0af328a6cb85b3842147107a5f04b1` (raw file SHA-256 `e00cd9e5c827bb31fbbcc6f97213a32013f94af8d05c7783af6dacd21299a470`): **35 updates = 3 Page.text + 3 subtitle bodies + 29 continuing block bodies; 3 new practices; 0 deletes**. План не дозволяє питання, answers, options, pivots або theory links. Exclusive private `backup-v1.json` створено до транзакції; apply завершився `35/3/0`.

Фактичні нові practice rows: Gerund `12699 / a3b3c3c3-4047-5794-a13e-2db18b57bf24`; Present `12700 / d70401dc-cefd-5b86-af69-084629f63663`; Past `12701 / 14bdb806-7325-5ec6-bab4-8f857a1f1769`. Повтор вихідного reviewed plan — no-op `0/0/0`. Свіжий AFTER plan має state `after`, digest `342d3a7bce3ca761afcab3d6c34d925a9e925a3ebcc97254e9b91715dea76c42`, 0 updates/0 inserts; його повтор — теж no-op `0/0/0`, без нових backups.

Read-only SQL/source postconditions `final-readonly-v1.json`: SHA-256 `499b2937a5accdd26c4211b49c24b74bd4b92e28bb378787a4708ac5761b6516`. Звірено 46 tables, 32 continuing UK rows включно з subtitles, 62 EN/PL rows без змін, три точні нові practices. Для всіх старих rows/timestamps поза exact allowlist відновлено незалежний BEFORE; question-family delta `0/0/0/0/0`. 326 visual files, 2292 frozen files та reviewed source sync незмінні; сам audit виконав 0 DB writes/0 HTTP.

Proof cleanup `cleanup-40c262d4f98fa666daa31c34330e819c.json`: SHA-256 `753342d8874386ec692f97a314d51368b507b63fdd9de38b41adea42339cbef1`. Exact foreign-inclusive routes bytes відновлено: BEFORE/AFTER SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; окремий anonymous loopback GET вилученого nonce endpoint повернув **404**, 0 redirects. Route не залишено у versioned source.

SSR `live-pages-v1.json`: SHA-256 `52c8ad09fdff3fe5abf9374e45867da2614ae0476942aae43cb979faf1f4ffa6`. Три target HTTP 200; незалежно звірено 538 basic/detail learning fields (`191/160/187`), старі anchors `22/21/22`, actual H1/title/canonical/LearningResource identity й expected descriptions, exact practice fields і widget payloads. Чотири details у SSR спочатку closed і мають точних owners. Це SSR-перевірка, не заміна client interaction або візуальної матриці; доступність усіх navigation destinations за межами трьох цілей цим receipt не доводиться.

### Окреме виправлення URL у 1.0.1

Початковий M56 1.0.0 додав помилкові явні плоскі посилання на сім сусідніх уроків. Це **нова регресія explicit URLs M56, не успадковані неправильні href**: source BEFORE footer мав labels без URL. Target URLs трьох M56 owners не змінюються, але resolver підтвердив для семи сусідів category 18 та ancestry `passive-voice → passive-voice-tenses`.

Правильні перевірені адреси:

- [Present Continuous Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-continuous)
- [Present Perfect Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-present-perfect)
- [Past Continuous Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-continuous)
- [Past Perfect Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-past-perfect)
- [Future Simple Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple)
- [Future Continuous Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-continuous)
- [Future Perfect Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-perfect)

Незалежний main/bank review підтвердив рівно **16 href/url входжень → 4 existing `text_blocks.body`**: 7 footer links у Present, 7 у Past, 1 посилання на Present Continuous усередині Present detail та 1 на Past Continuous у Past time note.

| Page | Block ID | Source index / sort order | Тип | Href входжень |
| --- | --- | --- | --- | --- |
| 113 | 3701 | 3 / 4 | usage-panels | 1 |
| 113 | 3706 | 8 / 9 | navigation-chips | 7 |
| 110 | 3597 | 6 / 7 | usage-panels | 1 |
| 110 | 3600 | 9 / 10 | navigation-chips | 7 |

Усі чотири rows — UK/category 1, A2, heading/css null; UUID, metadata й порядок незмінні. Gerund не має жодної href/body зміни. Навчальні формулювання, формули, переклади, 18 own tasks/keys/aliases, три practice bodies і bindings, 15 selected questions, чотири detail owners, Page.text/subtitles/SEO та дизайн незмінні. Href-only correction має **0 Page updates, 0 inserts/deletes, 0 question/answer/option/link writes**.

`database/content-patches/m56-gerund-simple-passives-correction-before.v1.0.1.json`, SHA-256 `1ac5ff63359ceb303b80bfcab12d5d1717963b37505c002a0c24feac2c11bba0`, містить exact 1.0.0 AFTER трьох definitions, уже з практиками: 11/10/11 blocks. `database/content-patches/m56-gerund-simple-passives-correction-delta.v1.0.1.json`, SHA-256 `2c6e25b7983cb7e2928d5b9e12ddac864ac5390ea53cca4ca94013a7c8097cd7`, дозволяє лише наведені чотири body переходи. Original `m56-gerund-simple-passives-before.v1.json` лишається pre-M56 baseline для canonical cumulative projection і course/standalone preservation; applied 1.0.0 не підставляється замість нього. Старі frozen artifacts і checksum не переписано.

Оновлений `source-sync-v2.json`: SHA-256 `425c7f63c2cdff5aafbae32b235d5146e046b5dcd55c8cbe8124adc0fffe5a85`, 34 exact paths. Fresh web/CLI proof nonce `c3a0f5b4daa48e134523b56b7ab97309`, SHA-256 `ee1ae74a84e31bc38a7653bb266897825e3dc01bac2b10589bffd8c19e025222`, повторно підтвердив тільки локальний target. Reviewed href preview digest `6ae6f76b452593cc6c9d53b90dffb9b9cc9b6735f54f0dfa6b518a8ba8b33722`: 4 body updates, 0 inserts, 2657 source pins і 46 table fingerprints. Після exclusive `href-backup-v1.json` виконано transactional apply **4/0/0**.

Fresh href AFTER digest `d899d9652bdb0ab25f09e0417305a591894fb5932503f09243642b46b882b07b` — state `after`, 0 updates/0 inserts. Окремий cumulative native 1.0.1 AFTER digest `61fa8746c973bc10b01bcd9e74776c53aab7afcf8fbc812330c1f3e2264becd8` — теж state `after`, 0/0. Три фінальні повтори підтвердили **no-op 0/0/0**: повтор original href plan, повтор fresh href AFTER і повтор cumulative 1.0.1 AFTER. Створено тільки два дозволені backups — `backup-v1.json` та `href-backup-v1.json`; unused repeat-backup names не створені. Початкові 35 updates і correction 4 не видаються за 39 різних змінених rows.

Другий proof route видалено. Exact foreign-inclusive routes bytes повернуто до SHA-256 `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; незалежний GET повернув 404. Receipt `cleanup-c3a0f5b4daa48e134523b56b7ab97309.json`: SHA-256 `6cf05d7748908f941b264cc14d659e7b8b2e1b50b6b9c870feb86f518e024ccd`. Temporary route не залишено у source/commit.

Aggregate read-only `final-readonly-v3.json`: SHA-256 `38270c2e5252ae7ff70839a70435f8f28b4efbb20f526783e0d1c7c29607cc0a`. Зворотна exact projection відтворює старі rows/timestamps за незалежним BEFORE у всіх 46 таблицях; поза погодженими content/body переходами змін немає. Final 1.0.1 SSR `live-pages-v2.json`: SHA-256 `5695d7c61555f536b88d057a49af167d81f4e93001bf47bd08bdba50fa447951`, 3 target HTTP 200 та всі 538 learning fields. Окремий `internal-links-v2.json`: SHA-256 `ede0e1e6ddece542395625075dc10368c8edb0078944da265071925e7994ccf0`, **12/12 unique actual href destinations HTTP 200**, 0 redirects/cookies/SQL writes/production requests.

Fresh scoped `m56-sentence-builders-v2.json`: SHA-256 `3c80469b297502f021eae04ea1d03d85bdde4e6d4f13d62ece3bc0b7e2d84573`, 3 targets / 0 failures / 0 writes. Незалежний фінальний bank audit підтвердив усі 15 current manifest entries, старі вісім M56 frozen artifacts та 11 M55 hashes, три owners, 18 own tasks, 15 bank bindings і вісім additive shared hooks без Blade/CSS/JS/grader edits.

Registry BEFORE: 88 targets, failed 0, writes 0; receipt SHA-256 `8631f0d561c5b0cc17fd95a263711399c6e99c7af4330abdf3a28351e898b20f`. Новий scoped M56 audit: 3 targets, failed 0, writes 0; receipt SHA-256 `5ec551450faceaafc4bae7210b5d8faa920fcbc6b9bc3c2d93c896d8c876067f`. Кожний має один practice widget і 5 selected type 4 IDs за exact CEFR. Повний dynamic registry AFTER: **91 targets / 0 failed / 0 writes**, receipt `registry-sentence-builders-v1.json`, SHA-256 `d227a1a88f80ebbffb79eae763544177e115c667c84a657d2eb2198fe3bc5a77`. Попередні 88 збережено; 91 не зашито як постійний cap.

Fresh full registry після 1.0.1: `registry-sentence-builders-v2.json`, SHA-256 `58d5282aebd1c0dcb645de7efc0dd310c6bd1294daa5656e4f2fcf6415fd097d`, **91 targets / 0 failed / 0 writes**. Нові versioned sources не подвоїли owners; попередні 88 та три M56 збережено.

Final AFTER old-consumer audit вимагає reviewed independent `final-readonly` receipt і SHA-256 та звіряє всі 46 current table fingerprints із reviewed AFTER, відновлюючи точний BEFORE. Немає blanket ignore для `pages` / `text_blocks`. Після href correction фактично виконано `after-old-consumers-v2.json`, SHA-256 `c3594448a78e41e38fe761ea04e79d1febf8146c55a418e3086e9a0c7d7de528`, із pin на `final-readonly-v3.json` / `38270c2e5252ae7ff70839a70435f8f28b4efbb20f526783e0d1c7c29607cc0a`: **28/28 HTTP 200**, exact BEFORE-порівняння 6 saved tests / 432 original questions, трьох M55 learning boundaries і category learning description; усі table hashes до/після HTTP однакові, 0 DB writes. Це read-only preservation gate, не браузерна взаємодія.

## Ізольовані тести

Початковий batch 1.0.0: Через незмінений `tools/diagnostics/run-isolated-tests.py` виконано 38 explicit suites: **196 unique tests / 36950 assertions**, 0 errors/failures/skips; child exit 0 / runner exit 0. SQLite `:memory:`, array cache/session, OPcache CLI off, `working_env_loaded=false`; 46661 actual protected files, 0 changes. Receipt `m56-php-infra-regression-v1-7da9745b591e444aa558a523388d8ca8-result.json`: SHA-256 `6ceccd36c6f552891a1d067ca18abc0460aa7affe1f51a4eda70c668bcb20ee5`.

У цьому batch нові M56 infra suites — 19 tests / 1868 assertions: Package `4/1447`, ContentPatch `4/163`, Contract `3/50`, Guard `3/34`, Controller compatibility `2/136`, Native compatibility `3/38`. Інші 177 tests / 35082 assertions захищають M55 package/patch/native/plain-subtitle/contract/guard/context, M54 package/patch/contract/guard/token-case, M53 package/supplement/contract/guard/hints, M52 package, dynamic builder acceptance, universal/canonical view і groups, linked practice, accepted variants/case/client logic та isolated SEO/SiteMode/canonical/native release/planner. M55 raw controller test не запущено напряму проти нового controller: M56 compatibility відтворює accepted M55 bytes і запускає незмінений історичний chain на private fixture.

Окремі M56 bank-selection tests — **6 unique tests / 5529 assertions**, child/runner 0 і 0 protected changes; receipt SHA-256 `c7fe64c9a8fbcf6d1449aaff7c21236efc9dd536028a9c2261031391c5cd89fb`. Вони не включені у початковий batch 196: історичний aggregate 1.0.0 становив **202 unique passing PHP tests / 42479 assertions**, з них нові M56 `25/7397`. Ці попередні runs не додаються вдруге до фінального total нижче.

Фінальний batch 1.0.1: **40 PHP suite files / 208 unique tests / 42640 assertions / 0 failures / 0 errors / 0 skips**, одна deprecation; 46661 protected files, 0 changes. Receipt `m56-final-php-v101-4a8e9d8807a54e29bba9156bda051958-result.json`: SHA-256 `231953052af4a4eb09ac34816c2ba6cdf814a44a8efe19ca35b249e14b9e98b1`; protected inventory SHA-256 `e1ff9fe48ab06d5f3ca47e8478bfe95ff3778dd459be19248b59beacf6383933`. До 208 уже включені нові 5 href-correction cases / 144 assertions та один package case; повтори початкових suites, focused runs і 1.0.1 reruns не збільшують unique total.

Початковий owned href suite мав 5 fixture SETUP errors / 0 assertions; виправлено лише test fixture, не production logic чи frozen expectations. Початковий receipt збережено: SHA-256 `8198fd12db39307f52a7c187d9b1ddef798ffbec5c129fafcf3961eded4e61b1`. Той самий focused suite потім пройшов 5/144, receipt SHA-256 `bb0717a70a1a5dbd6f5fcc9604644c1972dad3ae1179965223b02442430aec26`; ці 5 cases вже входять у фінальні 208.

Перший owned bank-test run мав 1 error: fixture викликав неіснуючий `Application::useBasePath`. Виправлено тільки тестовий виклик на підтриманий `setBasePath`, після чого той самий suite пройшов `6/5529`; початковий receipt збережено, SHA-256 `46fe2d6bf241c89701d6e9edbc19bf790f52baba90575080f2004f046acd8bbd`, protected changes 0. Це новий тестовий дефект, а не inherited failure; повтор не додано до unique total.

Історичний literal-UI suite перевірено окремо без зміни cases, snapshots чи дизайну: **6 tests, 2 PASS / 4 FAIL, 23 assertions**, protected changes 0; receipt SHA-256 `225654be81ca892e9972de27e1cd243b18c2b1c7c3c91307c5c9ac23c484f2d7`. Ці чотири підтверджені успадковані failures не оголошуються PASS і не входять у фінальні 208 passing tests.

Frozen offline JS suite `tests/Browser/m56-native-practice-state.test.cjs` повторно перевірений із final master 1.0.1: **22/22 PASS**. Незалежні очікування перевіряють 18 own tasks, 36 options, manual/token construction шести inputs, 6 canonical + 2 contraction accepted variants і шість deferred translations. Draft/1.0.0/1.0.1 повтори не додаються до unique total. Це 22 offline JS tests, окремо від 208 passing PHP tests і браузерних сценаріїв.

## Live browser acceptance

Computer-use skill використано для реального браузера й URL-bound native UI, без Playwright/CDP в shell, прихованого app state або викликів answer engine через evaluate. Read-only evaluate використовувалося лише для DOM/геометрії/видимого тексту. Zoom не змінювався. Еталон Past Perfect Continuous відкрито read-only; він не рахується як target state.

**18 унікальних target states**: 3 уроки × (1440×1000, 390×844, 320×844) × light/dark. Кожний viewport підтверджено actual `innerWidth/innerHeight` активної вкладки, а не screenshot pixels. На кожній сторінці переглянуто матеріал до footer, є 3 own groups, 2 manual/token inputs та рівно 1 widget; fonts loaded, broken images 0. Усі власні details без fragment спочатку closed. Початковий `browser-layout-matrix-v1.json`, SHA-256 `6ea7d6132151184cd50bc32cf38bfbe51ce5311cd2804bb25d0daccf6b55cca9`, збережено як історію. Final `browser-layout-matrix-v2.json`, SHA-256 `b22b243674481f781c9bd0d3045924a520f316ad4d8f1d94486a1f8914242d5c`, містить нові captures усіх 18 actual states уже на final 1.0.1. Повторні captures/матриці не додаються до unique total.

У навчальній межі main client/scroll width збігаються: 877/877, 358/358, 288/288 CSS px; нових visible-overflow paragraph/article/input/button/heading не виявлено. Таблиці використовують штатний horizontal scroll region, не новий CSS workaround. Успадковане shell overflow на вузьких екранах до 14 px і низький dark-контраст золотих CEFR badges не виправлено й не оголошується виправленим.

Кожний урок фактично прийняв усі 6 reviewed own answers: групи **2/2/2**. Для дев’яти груп пройдено empty → partial → wrong → repeated wrong → edit correct → recheck → reset: visible score 0/2 → 1/2 → 0/2 → 0/2 → 2/2 → 2/2 → cleared, без подвійного score. Це 9 груп із 7 станами, не 63 окремі unit tests.

**24 input task/mode/size cases**: 6 inputs × manual/tokens × desktop 1440/mobile 390. На desktop canonical manual acceptance також зафіксовано у group-state проходженні. Окремо прийнято `I'm not paid in cash.` та `The forms weren't signed at the meeting.`; ці повтори не збільшують 24. На кожному уроці перевірено змішане введення: manual prefix → token → видалення token із поля → token знову доступний → rebuild → правильний Check → reset; інші scores залишаються незалежними. Строго збережено `The`/`the`, multiplicity, not, punctuation і question marks.

Повний run 18 own answers / 24 inputs / 15 widget answers виконано перед href correction. У final 1.0.1 всі три practice bodies, keys/aliases, 15 bank questions та bindings byte-identical; повторна final layout matrix підтвердила ті самі видимі компоненти й відновлені 5/5 без очищення прогресу. Не заявляється другий повний run тих самих 24 cases після href-only зміни.

Усі три embedded widgets пройдено до **5/5** із видимими UK умовами, банком слів, складанням, accepted answer і Next: 15 reused bank questions, не нові DB questions. Gerund перевірив саме like/without/remember/subject/perfect passive -ing; Present/Past — свої exact A2 ситуації. Початок свіжої M56 спроби був П1/5; після reload відновлювалася власна щойно завершена спроба 5/5. Особисті cached attempts не очищувалися; fresh server payload і restored attempt не підміняють одне одного.

Усі 4 meaningful details перевірено Enter/Space: відкривається лише власне поглиблення. Для кожного перевірено exact summary fragment і reload: потрібне detail відкривається, весь текст і переклади доступні, clientHeight == scrollHeight без clipping. Відкриття за fragment не суперечить initial-closed правилу без fragment.

Один останній Past mobile batch перервався через 30-second browser-runtime timeout. Відновлено ту саму вкладку 6 без нового DB apply або очищення спроб: фактичний DOM показав частково складену другу відповідь, її завершено; manual/alias і mixed-edit повторно перевірено. Повторна DOM-матриця підтвердила ті самі 18 унікальних states. Помилки підключення й незавершений batch не названі PASS.

Останній actual proof-of-work capture `m56-final-preview.jpg` збережено приватно: light, viewport повернуто до 1280 px, owned tab 6 позначена deliverable. Browser zoom і особистий прогрес не змінювалися.

JS-off і print **не перевірені**: у підключеному browser API не було потрібного перемикача/print surface; це обмеження способу перевірки, не твердження про відсутність підтримки сайтом. Serialized answers не називаються доступним no-JS ключем. Локальне приймання не доводить ranking gain.
