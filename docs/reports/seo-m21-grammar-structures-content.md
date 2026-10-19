# M21 — Gerund/Infinitive, Relative Clauses та Negative Inversion

Дата приймання: 2026-09-28. Зміни застосовано до робочого **http://gramlyze.loc**.
Це три контентні уроки з 18 самоперевірками; Mixed-банки не розширювалися.

## База, межі та Git

- Прочитані AGENTS.md, звіти M20 і M12 styling, чинний TheoryRichContent, guarded patch, реальні definitions Inversion Basics та Advanced Fronting And Emphasis.
- Fetch виконано; прийнята база M20: `codex/seo-m20-modals-subjunctive-content`, `0c68712c7ba26e6a1c30a0c4f062790b7b1009a3`. Локальний HEAD і відповідний remote ref збігалися; ancestry підтверджено. Новішого погодженого продовження цієї бази не знайдено.
- Робоча гілка: `codex/seo-m21-grammar-structures-content`. Використано вільний наявний worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`.
- Основний checkout залишено на `codex/production-ready-a14788dac`, `41820a2bebdf69004fa7209a2a38457f93efabbd`. Численні сторонні видалення/редагування не скидалися, не переміщувалися й не включалися в пакет.
- Без PR, merge, force push, main, workflow dispatch, деплою, SSH, .com/.ub HTTP, зміни XAMPP/hosts, залежностей або кешів. `.com` у canonical/sitemap аналізувався лише як рядок.
- До Git входять лише три definitions, M21 before-manifest, п’ять вузьких wrappers/diagnostic-файлів, три цільові тести та цей звіт. Private proofs, screenshots, backups, .env, vendor/build не входять.
- Фінальний SHA commit і звірений remote SHA наведені у повідомленні про завершення; власний SHA неможливо записати всередині того самого commit без самопосилання.

## Точні identities і локальні адреси

| Урок / незмінний рівень | Повний Page.seeder | Page ID / UK block IDs | Категорія від кореня |
| --- | --- | --- | --- |
| Advanced Gerund and Infinitive Patterns / B2 | `Database\Seeders\Page_V3\VerbPatterns\AdvancedGerundInfinitivePatternsTheorySeeder` | 286 / 8745, 8746, 8747 | `verb-patterns` |
| Complex Relative Clauses / C1 | `Database\Seeders\Page_V3\RelativeClauses\ComplexRelativeClausesTheorySeeder` | 300 / 8787, 8788, 8789 | `relative-clauses` |
| Inversion After Negative Adverbials / C1 | `Database\Seeders\Page_V3\BasicGrammar\WordOrder\InversionAfterNegativeAdverbialsTheorySeeder` | 295 / 8772, 8773, 8774 | `basic-grammar → word-order` |

Адреси отримані з робочих Page/test/course resolvers і навігації, не лише з PHP namespace.

1. [Gerund/Infinitive — теорія](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns), [основний тест](http://gramlyze.loc/test/verb-patterns/advanced-gerund-infinitive-patterns).
2. [Relative Clauses — теорія](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses), [основний тест](http://gramlyze.loc/test/relative-clauses/complex-relative-clauses).
3. [Negative Inversion — теорія](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials), [основний тест](http://gramlyze.loc/test/word-order/inversion-after-negative-adverbials).
4. [Перевірена курсова копія Gerund/Infinitive](http://gramlyze.loc/courses/english-grammar-theory/lesson/verb-patterns/advanced-gerund-infinitive-patterns).

У Gerund/Infinitive `category.type` як і раніше відсутній у definition. Чинний `JsonPageSeeder::resolveOrCreateCategory` за відсутності явного type зберігає type наявної категорії. У робочій БД це `theory`; це відтворено ізольованим import-тестом. Поле механічно не додавалося. Вкладену word-order не перетворено на кореневу.

## Що було і що змінено

Початкові три sources побайтно збігалися з навчальними полями робочої БД: UK subtitle, hero, box та Page.text. У кожної сторінки було три UK text blocks; ID/UUID, sort order, owner, category, tags/pivots, інші локалі та тестові зв’язки зафіксовані до запису.

Наявні правильні теми збережено:

- Gerund/Infinitive: Verb pattern, Meaning change, After prepositions.
- Relative Clauses: Whose, Prepositions, Non-defining detail.
- Inversion: Negative adverbials, Hardly and no sooner, Limiting expressions.

Початковий box лише пояснював службове призначення theory anchor. Він замінений повним українським уроком. Англомовний вступ перекладено й розширено; повтор назви всередині Gerund intro прибрано. Page.title і controller-derived H1 не перейменовано.

### Авторські розширення

- **B2 Gerund/Infinitive:** невеликий набір керувань; ready to / interested in; два різновиди to; три явно задані контексти remember; припинення дії проти паузи з метою; мета проти перевірки способу після try; порівняльна таблиця, мінідіалог і 6 завдань.
- **C1 Relative Clauses:** власник і предмет належності; whose для речей/організацій; whom/which із семантично виправданим прийменником; кінцевий прийменник; роль займенника, опущення у конкретних моделях; вкладене who we believe can; коми за заданою групою; предметне й реченнєве which; редакція абзацу.
- **C1 Negative Inversion:** коротке базове нагадування, далі межі інверсії; часові пари; головна проти підрядної частини; only при підметі; not only між частинами підмета; пасив/perfect/modal; факт, заборона та частотність; розповідь і нейтральний відповідник.

### Before → after і редакційні межі

- Повтор назви в Gerund hero → один незмінний заголовок і змістовний intro без повтору.
- Коротке правило після прийменника → конкретні `interested in doing / look forward to doing`, зіставлені з `decide to do`. Це уточнення меж, не оголошення базового правила хибним.
- `remember doing / remember to do` → `I remember sealing…` (спогад), `Remember to take…` (нагадування), `I remembered to return…` (виконане доручення).
- Коротка вказівка Past Perfect перед when/than → основна модель залишається, але не подається як єдина; власний підтверджений за моделлю джерела приклад: `No sooner was the display ready than the first visitors arrived.`
- Загальний список limiting expressions → уточнено, яка частина інвертується і коли only/not only стосується підмета.
- Додаткова інформація в relative clause не названа «неважливою»; не виводимо кількість родичів/працівників лише з пунктуації без контексту.
- Формальне whom і інверсія — редакційні можливості, не універсально «кращий» стиль. Правильний альтернативний варіант, що не виконує заданої моделі, не оголошено неграматичним.

### Відмінність від прийнятого M12

Реальний Inversion Basics уже має базове auxiliary/do-support, never/rarely, only after/when/not until, область not only та прості трансформації. Його шість вправ не переписувалися зі зміною імен.

Нові завдання C1 вимагають збереження **Present Perfect Passive**, перевірки близькості подій і відсутності причинного висновку, виправлення інверсії саме в підрядній частині, розрізнення активного only-subject та пасивного only-after, відокремлення заборони з may від минулого факту, а також редакції зв’язного фрагмента з урахуванням емфатичного do.

Advanced Fronting And Emphasis зберігає власний C2 scope: object/fronted place, повна інверсія, займенники після here, дискурсивний контраст. Новий C1 не дублює цей пакет і не додає великий розділ умовної інверсії.

## Редакторський перегляд усіх 18 ключів

Нижче — окрема змістовна перевірка, не заміна її кількістю assertions. Повні умови, переклади та розгорнуті ключі є безпосередньо на відповідних сторінках.

### [Advanced Gerund and Infinitive Patterns](http://gramlyze.loc/theory/verb-patterns/advanced-gerund-infinitive-patterns#self-check-advanced-gerund-infinitive-patterns)

1. **Форма після заданого слова:** `We avoid storing paint near the heater. We decided to move the tins.` Avoid + -ing і decide + to; рішення не доводить факту переставлення банок.
2. **Два to:** `I look forward to meeting the curator. I plan to meet her on Friday.` У першій моделі to — прийменник, у другій — маркер інфінітива. Учасники й п’ятниця збережені.
3. **Три remember:** `I remember packing the vase yesterday.`; `Remember to label the box before sending it.`; `I remembered to return the trolley yesterday.` Розрізнені спогад, наказ-нагадування та виконане доручення; останній переклад прямо передає виконання, не майбутню обіцянку.
4. **Stop:** `The students stopped whispering.` проти `The students stopped to read the sign.` Припинене шепотіння проти призупиненої за умовою ходьби. Друге не означає «перестали читати».
5. **Try:** `Mila tried to lift the heavy lid.`; `Mila tried opening the side vent to cool the room.` Перше фокусує зусилля досягти мети, друге — випробування способу. Не заявляємо провалу піднімання чи успішного охолодження. Інший ракурс може допускати іншу форму.
6. **Редагування:** `Yesterday we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.` Виправлено decided fixing / look forward to see; вилучено непідтверджений успіх. Критерії не дозволяють додати й невдачу або завершення ремонту.

### [Complex Relative Clauses](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses#self-check-complex-relative-clauses)

1. **Whose:** `We rented a studio whose windows face the river.` Опора — studio, частини — windows; не людина, але зв’язок природний. Whose не замінено на who’s.
2. **Заданий формальний порядок:** `The conservator to whom we spoke recommended a softer brush.`; `The frame on which the portrait rests is wooden.` To походить від speak to, on — від rest on; початкові версії з кінцевим прийменником також допустимі.
3. **Шість фотографій, дві білі рамки:** `The photographs which have white frames were moved.` Без ком відбираємо дві з шести. Версія з комами приписала б білі рамки всій заданій групі, тому не відповідає фактам, хоча можлива в іншому контексті.
4. **Роль і опущення:** who-підмет у простому `who checked` не опускаємо; which-додаток у defining `The captions we checked…` можна опустити; у non-defining займенник залишається. У `The restorer who we believe can repair the frame is away.` who — підмет can repair, we — підмет believe; це не whom лише через сусіднє believe. Не сформульовано універсальної заборони опущення для всіх вкладених конструкцій.
5. **Неоднозначне which:** `They placed the folder beside the lamp. The folder was damaged.` Повтор folder виключає небажане відсилання до lamp. Не додано причини пошкодження чи факту пошкодження під час переміщення.
6. **Абзац про майстерню:** `The workshop whose three presses need servicing has hired Iva, who maintains them.` Збережено одну визначену з двох майстерень, її три преси та Іву як виконавицю. Вилучено дубль she, але залишено потрібне them як додаток maintains. Немає вигаданого завершення ремонту.

### [Inversion After Negative Adverbials](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-after-negative-adverbials#self-check-inversion-after-negative-adverbials)

1. **Perfect Passive:** `Rarely have the samples been stored outside the cold room.` Збережені have been stored, пасив, місце й частотність; не додані did або виконавець. Rarely не перетворено на never.
2. **Близькі події:** `Hardly had the dispatcher finished checking the list when the alarm sounded.` Перевірка перед сигналом, одразу слідом; причинності немає. Триденний проміжок зробив би цю пару неточною. No sooner…than було б граматичною альтернативою, але не заданою парою.
3. **Хибна підрядна інверсія:** `Only after the editor had confirmed consent was the material released.` Звичайне the editor had confirmed у підрядній; was the material released у головній. Збережено Past Perfect / Past Simple Passive та різні ролі учасників.
4. **Only-subject / only-after:** `Only the archivist could open the vault.`; `Only after the check was complete could the vault be opened.` Підмет проти часової межі; актив і пасив не змішані, could не перетворено на факт відкриття.
5. **Заборона й минулий факт:** `Under no circumstances may visitors remove the seals.`; `At no time during the trial was the access code shared.` У першому збережено may і прибрано зайве not; у другому — was shared без нового modal чи виконавця.
6. **Редагування фрагмента:** `No sooner had the display been installed than the visitors arrived. Not only the architect but also the caretaker welcomed them. The visitors rarely speak loudly in this hall.` Than, попередній пасив і обидва учасники збережені. Not only з’єднує підмети; rarely не винесено. Did welcome / do speak можливі за емфатичного контрасту, але його прямо виключено умовою — загальної заборони немає.

## Звірка першоджерел та межі доступу

Приклади, сцени, вправи, переклади й ключі написані для Gramlyze; зовнішні приклади не скопійовані.

Безпосередньо відкрито і прочитано основний матеріал British Council:

- [Verbs followed by -ing or infinitive to change meaning](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/verbs-followed-ing-or-infinitive-change-meaning): розмежування remember, stop і try.
- [Non-defining relative clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-non-defining-relative-clauses): додаткова інформація, коми, обов’язковий relative pronoun і відсутність that у цих моделях.
- [Inversion after negative adverbials](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/inversion-after-negative-adverbials): область інверсії та часові пари. Сам основний матеріал має модель no sooner з be, тому Past Perfect не оголошено єдиним варіантом.

Відповіді команди LearnEnglish відділено від учнівських коментарів: уточнення Kirk Moore від 16.04.2024 про контекст try; Peter M від 19.04.2025 про «зазвичай», а не «завжди» Past Perfect. Учнівські коментарі не використано як нормативний доказ.

Пряме відкриття наведених у завданні Cambridge URL повертало **403**. Захист не обходився; ці сторінки не називаються повністю прочитаними через пряме відкриття. Перевірені доступні індексовані тексти/фрагменти самого видавця:

| Запитаний матеріал / прямий доступ 403 | Доступний текст видавця й використана межа |
| --- | --- |
| [Infinitive verbs](https://dictionary.cambridge.org/us/grammar/british-grammar/infinitive-verbs) | [Verb patterns: verb + infinitive](https://dictionary.cambridge.org/grammar/british-grammar/verb-patterns-verb-infinitive): керування й зміна значення; невеликий набір, не повний словник |
| [Defining/non-defining](https://dictionary.cambridge.org/uk/grammar/british-grammar/relative-clauses-defining-and-non-defining) | [Індексований варіант адреси видавця](https://dictionary.cambridge.org/uk/grammar/british-grammar/relative-clauses-defining-and-non-): ідентифікація, пунктуація, відсутність дубльованого займенника, вкладені конструкції |
| [Relative pronouns](https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns) | [Індексована сторінка relative pronouns](https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns/): whose для речей/організацій, whom/which після прийменника, опущення у defining, whole-clause which |
| [No sooner](https://dictionary.cambridge.org/grammar/british-grammar/no-sooner) | Індексований текст цієї сторінки: близька послідовність, often Past Perfect, than, fronted auxiliary |
| [Only](https://dictionary.cambridge.org/grammar/british-grammar/only) | Індексований текст цієї сторінки: позиція залежить від фокуса, зокрема only при підметі |

Додаткова звірка конкретних моделей за доступними індексованими словниковими/граматичними матеріалами Cambridge:

- [Look forward to](https://dictionary.cambridge.org/grammar/british-grammar/look-forward-to.): прийменникове to з іменником/-ing.
- [Ready](https://dictionary.cambridge.org/dictionary/english/ready): ready + to-infinitive.
- [Interested](https://dictionary.cambridge.org/dictionary/english/interested): interested in + doing.
- [Not only…but also](https://dictionary.cambridge.org/grammar/british-grammar/not-only-but-also): відрізнено координацію компонентів від винесення not only перед цілою частиною з auxiliary inversion.

Read-only звірено базові [Gerund](http://gramlyze.loc/theory/verb-patterns/verbs-plus-gerund), [Infinitive](http://gramlyze.loc/theory/verb-patterns/verbs-plus-infinitive), [Meaning change](http://gramlyze.loc/theory/verb-patterns/stop-remember-forget-try-regret), [M14 Basics](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics), [M14 C1](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses), [Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases), [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics), [Advanced Fronting](http://gramlyze.loc/theory/basic-grammar/word-order/advanced-fronting-and-emphasis). Вони не редагувалися.

## Реальне локальне застосування

Використано наявний transactional/backup/restore контракт LinkingWordsContentPatch, без його зміни. Власні M21 identity/definition allowlists містять рівно три повні identities. Для кожної — точний ancestry; чужа дозволена категорія теж неприйнятна. Старі manifests, allowlists, формати plan/backup/restore не змінені.

1. Перед фінальним preview завершено контент і форматування, перевірено JSON/hero, rich opt-in, controller H1, кінцеві newline та staged `git diff --check`.
2. Read-only guard підтвердив loopback DNS, єдиний vhost і document root, фізичний локальний MySQL process/listener/PID та збіг web/CLI runtime. Sandbox спочатку обмежив інспекцію Windows; повторено з вузьким дозволом, не послаблюючи guard.
3. `.env`, APP_ENV=production, APP_KEY і підключення збережені. Local-target opt-in застосований саме до робочого gramlyze.loc, не до fixture.
4. Preview `m21-final-plan.json`: **12 змін**, тільки `pages.text` і `text_blocks.heading/body` за наведеними IDs.
5. Exclusive `m21-record-backup.json` створено до першого UPDATE. Transactional apply: **applied, updated=12**. Внутрішній postcondition пройшов.
6. Повторний запуск того самого plan: **no-op, updated=0**; `m21-unused-noop.json` не створено.
7. Окрема read-only перевірка: 12 after-значень/UUID збігаються; backup побайтно дорівнює preview; усі 4 source hashes незмінні після preview.
8. Тимчасовий loopback endpoint видалено; реальний GET повернув **404**. `routes/api.php` побайтно відновлено до попереднього стану зі збереженням сторонніх змін.

Private record-backup збережений у `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m21-local/m21-record-backup.json`, поза Git. Це адресна копія записів і пов’язаних snapshot-даних, не повний дамп.

## Дані, metadata й sitemap

- Before/after fingerprints **46 таблиць** збігаються після виключення лише погоджених полів 12 цільових рядків. Незмінні всі інші поля, IDs/UUIDs, локалі, теги, pivots, категорії, questions/answers/options/hints і прогрес.
- Окремо підтверджено збіг source/DB усіх **30 уроків M11–M20** до й після.
- Фактично спостережено: 44 460 questions; 197 312 question_answers; 27 154 question_options; 17 004 verb_hints; 750 saved_grammar_tests. Їх не змінювали.
- HTTP before 10:30 UTC / after 10:51 UTC: **17/17 адрес — 200**. Динамічний Mixed HTML не використовувався для доказу незмінності банків.
- Незмінні Page.title, controller H1, HTML title, canonical, robots, Content-Type та основні test href. Description/meta/OG/Twitter змінилися лише на трьох цільових theory URL через новий intro та чинний PageMetadata; між собою узгоджені, назва не дублюється.
- Локальний `X-Robots-Tag: noindex, nofollow, noarchive` збережено. Production-профіль robots/canonical перевірено лише ізольованим in-memory kernel.
- Ordered sitemap: фактичні **554 URL**, однаковий порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Це виміряний результат, не hardcoded вимога.
- SHA .env before/after однаковий. Контент/metadata всіх контрольних і read-only сусідніх сторінок незмінні.

## Автоматичне й браузерне приймання

| Перевірка | Фактичний результат |
| --- | --- |
| GrammarStructuresContentPackageTest + GrammarStructuresContentPatchTest + M11LocalTargetGuardTest через isolated runner | **43 tests, 774 assertions**, 0 failures, 1 deprecation |
| Уточнення deprecation вузьким повторним тестом | 1 test / 6 assertions; `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` deprecated у PHP 8.5; сторонній конфіг не редагувався |
| `node --test tests/Browser/seo-m21-local.test.cjs` | **8/8 pass** |
| JSON, hero JSON, rich opt-in, незмінні identities/levels/tags | pass для всіх 3 definitions |
| Patch contracts | old→new, no-op, manual/stale, exclusive backup, rollback, guarded restore, непідтверджена ціль — pass |
| Негативні category/ancestry тести | перестановки між дозволеними категоріями та неправильний parent word-order відхиляються без запису |
| Реальний HTTP before/after | 17 адрес; compare pass |
| Основні browser сценарії | **6/6 pass**: 3 уроки × desktop/mobile, кожний light/dark |
| Контролі M20 + Inversion Basics | **4/4 pass**, кожний light/dark |
| Reload / нові contexts / початковий HTML | 6 завдань і 6 ключів на кожній сторінці; source content не підмінювався |
| Details | mouse open/close, Enter open/reopen, Space close — pass |
| Основні тести | 3 штатні переходи через посилання, без відповідей і unlock |
| Таблиці | desktop/mobile без document overflow; mobile wrapper 322 px, content 1001 px, фактичний scrollLeft 679 px |
| Оформлення | по 8 rich-секцій; 31 / 37 / 36 example-елементів; нумерація, переклади, формули й keys читаються |
| Перевірені кольори | light/dark, мінімальний виміряний контраст вибірки 6.295:1; це не повний accessibility audit |
| PHP warnings у HTTP | не виявлено в повторних GET трьох theory, трьох test і однієї course адрес |

Середовище: PHP **8.5.10**, PHPUnit **12.5.35**, SQLite `:memory:`, testing, isolated storage, array cache/session, CLI OPcache off. Робочий .env у PHPUnit не завантажувався. Основний PHP прогін: 35.575 s, 80 MB. Файловий fingerprint runner не видається за перевірку робочої БД: для неї окремо виконані 46 table fingerprints.

Chromium **147.0.7727.15**; desktop 1440×1000, mobile 390×844. Переглянуто screenshots мобільних таблиць зліва/справа, довгих relative-конструкцій, часових моделей, світлих і темних відкритих ключів та нижніх секцій. Додатково збережено 18 detail screenshots; live HTML не підставлявся через setContent/route.fulfill/innerHTML.

### Фактичні обмеження

- Google Fonts: `net::ERR_NETWORK_ACCESS_DENIED` у цьому середовищі. Видимість і геометрія прийняті з фактично доступними fallback fonts; завантаження зовнішніх шрифтів не оголошується успішним.
- Автоматичні test `/state` POST свідомо заблоковані read-only runner. Їх `ERR_FAILED`/console записи класифіковані окремо; це не application failure. JS page errors — 0 у всіх основних і контрольних сценаріях.
- Курсова копія: 200, правильний H1, 6 завдань і 6 ключів у початковій server response; `contentVisible=false` за штатним course gate. Gate не обходився, видимість курсового уроку як розблокованого не підтверджена.
- Повний suite, full crawl, M10, Lighthouse/performance, build і оновлення залежностей не запускалися: build inputs не змінені.
- Показники SEO/ранжування та production-поведінка цим локальним пакетом не доводяться.

Приватні докази: `storage/app/seo-m21-local/` основного checkout — inventory, before/after HTTP, comparison, fingerprints, backup, apply-acceptance, browser reports і screenshots. Isolated test results — приватний `storage/app/seo-m2-local/` worktree. Нічого з цього не включено в Git.

Production не перевірявся й не змінювався.
