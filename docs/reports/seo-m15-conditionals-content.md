# M15 — Conditionals B2–C2

Дата приймання: 2026-09-27. Зміни застосовано до робочого `http://gramlyze.loc`, а не лише до fixtures або preview. Production не перевірявся й не змінювався.

## 1. База, межі та джерела

- Репозиторій: `Pvitaly91/engapp-codex`.
- Прийнята база M14: `f94346d6b9ac7db79bf6820d9c4aa6f27bcd8c55`, `codex/seo-m14-participle-clauses-content`. Після fetch перевірено remote ref та ancestry; новіших комітів у цьому продовженні M14 не було.
- Робоча гілка: `codex/seo-m15-conditionals-content`.
- Використано вже наявний вільний worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`; його історична назва не означає роботу над M11.
- Основний checkout мав сторонні незавершені зміни. Його гілку, HEAD і зміни не скидали й не переносили до цього коміту. Reset, clean, stash та force push не виконувалися.
- Прочитано AGENTS.md, звіти M14 і M12 styling; враховано контракт controller-derived H1, PageMetadata та TheoryRichContent.

Повні identities з точної allowlist:

| Рівень сайту | Page.seeder | Локальні записи |
|---|---|---|
| B2 | `Database\Seeders\Page_V3\Conditionals\ConditionalsWithUnlessProvidedAsLongAsTheorySeeder` | Page 287; UK blocks 9159, 9160, 9161 |
| C1 | `Database\Seeders\Page_V3\Conditionals\AdvancedConditionalsTheorySeeder` | Page 294; UK blocks 9162, 9163, 9164 |
| C2 | `Database\Seeders\Page_V3\Conditionals\ConditionalAlternativesAndNuanceTheorySeeder` | Page 309; UK blocks 9168, 9169, 9170 |

На старті definitions точно відповідали навчальним полям цих локальних записів. Перевірено locale `uk`, UUID/ID, порядок subtitle/hero/box, власників, категорію та зв’язки. Категорія — коренева `conditionals`, ID 10, language `uk`, type `theory`. У B2 source немає `page.category.type`: це збережено; чинна нормалізація й фактична категорія дають `theory`.

Read-only переглянуто First/Second/Third/Mixed Conditionals, Inversion Basics, Advanced Fronting And Emphasis, Advanced Linking Devices. Вони не стали цілями редагування. Усі 12 прийнятих уроків M11–M14, їхні manifests, helpers і restore-контракти збережено.

## 2. Фактичні URL

Адреси отримано через чинні resolvers/навігацію, а не з назв PHP-каталогів.

| Урок | Теорія | Основний mixed-тест, без зміни банку |
|---|---|---|
| B2 — Conditionals with Unless, Provided That, and As Long As | [Відкрити урок](http://gramlyze.loc/theory/conditionals/conditionals-with-unless-provided-as-long-as) | [Відкрити тест](http://gramlyze.loc/test/conditionals/with-unless-provided-as-long-as) |
| C1 — Advanced Conditionals | [Відкрити урок](http://gramlyze.loc/theory/conditionals/advanced-conditionals) | [Відкрити тест](http://gramlyze.loc/test/conditionals/advanced-conditionals) |
| C2 — Conditional Alternatives And Nuance | [Відкрити урок](http://gramlyze.loc/theory/conditionals/conditional-alternatives-and-nuance) | [Відкрити тест](http://gramlyze.loc/test/conditionals/conditional-alternatives-and-nuance) |

Перевірена [курсова копія B2](http://gramlyze.loc/courses/english-grammar-theory/lesson/conditionals/conditionals-with-unless-provided-as-long-as). Для нового гостя урок заблокований чинним course gate; його не обходили.

## 3. Початковий матеріал → недолік → нова редакція

| Урок / початковий матеріал Gramlyze | Підтверджений недолік | Нова редакція | Перевірка правила |
|---|---|---|---|
| B2: англомовний intro; `Unless usually means if not`; as long as пов’язано з тим, що умова «continues to be true» | Українська сторінка містила короткі англомовні опори й службовий box замість повного уроку. Бракувало меж перефразування, розрізнення умови/тривалості й практики | Український вступ, 6 розділів, порівняльна таблиця, конкретні дозволи/домовленості/вимоги/попередження, 6 вправ із ключами | Cambridge unless/conditionals; Collins as long as; British Council щодо negative unless |
| C1: правильні базові моделі past→present та present→past; приклад про організованість без розгорнутих фактів | Формули не пояснювали, чому стійка риса стосується минулого рішення; не було зіставлення часу й модальності, самоперевірки | Аналіз «факт → альтернатива → час умови → час результату → форма»; три результати однієї минулої умови; would/could/might; обмежена had-inversion | British Council third/mixed та inversion |
| C2: `provided that, assuming, supposing, and on condition that` разом описано як засоби встановлення precise requirements | Змішано вимогу з робочим припущенням; otherwise, часові варіанти but for, повні if-пари та місце not не розкриті | Окремі мовленнєві функції, відновлення неявної умови, but for «якби не» проти «за винятком», should/were/had із повними if-формами, 6 вправ | Cambridge assuming/but for/conditionals; British Council inversion |

Приклади конкретних змін:

- Замість службового B2 box про «theory anchor» — зіставлення умовного `You may keep the atlas as long as you renew the loan today` і часового `The lantern shone as long as the battery lasted`, з українськими перекладами.
- `Unless` не оголошено універсальною заміною будь-якого `if not`. Дощ/відсутність дощу показують зміну змісту; `I’ll bring the projector unless you don’t need it` показує можливий негативний виняток. Відомий минулий контрфактичний факт подано через `if ... hadn’t`.
- Замість безконтекстного «present→past» — стійкий страх висоти Данила, актуальний і минулого тижня, та пропущений тоді підйом на вежу. Це не зворотна причинність.
- На спільній ситуації Марти порівняно минулий переїзд, теперішній стан і поточне спостереження за птахами. Зміна результату не названа рівнозначним перефразуванням.
- Замість прирівнювання assuming до вимоги — робоче припущення щодо гранту та окрема обов’язкова умова письмового схвалення директора. `Might` зберігає непевність; `otherwise` явно відсилає до відсутності схвалення.
- Інверсія зіставлена з повними if-формами, включно з `Were the supplier not to deliver ...` та `Had Lena not checked ...`. Це умовні конструкції, не питання.

B2 навчає вибору сполучника та форми; C1 — часових зв’язків і збереження фактів; C2 — мовленнєвої функції, неявних умов і формальної інверсії. Позначки B2/C1/C2 залишаються редакційними рівнями сайту, не офіційною CEFR-сертифікацією.

## 4. Джерела граматичної перевірки та обмеження

Безпосередньо прочитані основні матеріали:

- [British Council — Conditionals: third and mixed](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-third-mixed): зіставлення минулої умови з теперішнім результатом і стійкої ситуації з минулим результатом.
- [British Council — Inversion and conditionals](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/inversion-conditionals): should/base form, were/to-infinitive, were/be-complement, had/V3, not після підмета, формальний регістр.
- [Collins — As long as](https://www.collinsdictionary.com/dictionary/english/as-long-as): умовне значення та тривалість. Автоматичні корпусні приклади не використані як правила.

Cambridge повертав HTTP 403 при прямому відкритті. Нижче використані тільки доступні індексовані фрагменти самого видавця; це не повне прочитання сторінок:

- [Conditionals: other expressions](https://dictionary.cambridge.org/grammar/british-grammar/conditionals-other-): unless/provided/otherwise/supposing та conditional inversion.
- [Unless](https://dictionary.cambridge.org/dictionary/english/unless) і [граматичний розділ Unless](https://dictionary.cambridge.org/grammar/british-grammar/unless): виняток, межі механічного if not, відомі минулі факти.
- [But for](https://dictionary.cambridge.org/dictionary/english/but-for): умовне without та окреме except.
- [Assuming](https://dictionary.cambridge.org/dictionary/english/assuming): прийняте для міркування припущення, не обов’язкова вимога.
- [Politeness](https://dictionary.cambridge.org/grammar/british-grammar/politeness_2): if will/would у ввічливих проханнях; [British Council — Verbs in time clauses and if clauses](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/verbs-time-clauses-if-clauses): звичайні майбутні умови. Тут також використано індексовані фрагменти, не заявлено повне прочитання.
- [Відповідь викладача Peter M, The LearnEnglish Team](https://learnenglish.britishcouncil.org/comment/147077): негативний виняток після unless може бути доречним; контекст має вирішальне значення. Використано індексований текст відповіді викладача, не учнівське запитання.

Межі спрощених правил пояснено, а не приховано: навчальне «не ставимо will» застосовано до звичайного майбутнього прогнозу, не до всіх значень if; негативне слово після unless не заборонено універсально. Змішаний conditional не визначається лише now/yesterday або механічною таблицею. Власні приклади підібрано під конкретні контексти; вправи чи статті видавців не копіювалися.

## 5. Редакторська перевірка всіх 18 завдань і ключів

Це самоперевірка всередині уроків, не нові питання Mixed-банку. У кожному уроці 6 завдань та 6 пояснених ключів. Перевірено час, заперечення, модальність, переклад, факти й допустимі альтернативи, а не лише кількість елементів.

| № | Завдання / опорна відповідь | Результат редакторської перевірки |
|---|---|---|
| B2-1 | Бронювання: `Unless you confirm the booking, we’ll release the room.` | Don’t прибрано при сумісному if not→unless; майбутній результат і факт підтвердження збережені |
| B2-2 | Workshop outside: `unless it rains` / `unless it doesn’t rain` | Перший варіант відповідає заданому плану; другий змінює виняток, а не оголошується завжди неграматичним |
| B2-3 | Студія/ключ: `You may use the studio provided that you return the key by eight.` | May/can допустимі. As long as природне за змістом, але не виконує інструкцію використати provided that |
| B2-4 | Атлас/продовження позики та ліхтар/батарея | Умова дозволу й тривалість однозначно розрізнені контекстом; переклади не змішують значення |
| B2-5 | `The organiser will reserve a desk provided that Eva confirms by Friday.` | Confirms у звичайній майбутній умові; will у результаті. Ввічливе прохання виключене інструкцією |
| B2-6 | `Unless you reply by noon ... provided that you pay today.` | Відновлено обидва факти: відповідь до полудня й оплата сьогодні. Will reply→reply — форма; don’t pay→pay — виправлення змісту. I’ll/I will рівнозначні |
| C1-1 | Батарея: `If we had charged ... the device would be working now.` | Умова вчора, процес зараз; український переклад та факти відповідають обом частинам |
| C1-2 | Ірина/музей: `If Iryna had accepted ... she would be working ... now.` | Минула умова й теперішня зайнятість; would work також можливе без акценту на процесі, would have worked last year змінює результат |
| C1-3 | Данило/висота: `If Danylo weren’t afraid ... he would have climbed ... last week.` | Страх актуальний і тоді, і тепер, а підйом минулий. Were not допускається; зворотної причинності немає |
| C1-4 | Installed Monday → finished Tuesday / ready to start now | Порівняння завершеної минулої дії та теперішньої готовності, а не рівнозначне перетворення; зайвих фактів про написаний звіт не додано |
| C1-5 | `Had the curator seen the letter, she might have postponed the exhibition.` | Might збережено. Would граматично можливе, але посилює уявний результат і порушує інструкцію |
| C1-6 | `Had we kept the access code, we could open the archive now.` | Had-інверсія змінює тільки виклад умови; could open залишається теперішньою можливістю |
| C2-1 | Accurate figures/assuming проти editor approval/on condition that | Припущення не видається перевіреним фактом або наказом; встановлена вимога окремо, механічної заміни немає |
| C2-2 | `If you do not send the signed form today, we will postpone the visit.` | Відновлено всю умову надсилання сьогодні, а не лише підписання. Don’t рівнозначне do not |
| C2-3 | `If it had not been for the backup generator, the archive would have lost its files.` | Минула подія і наслідок; were not потребувало б іншого контексту. Had not/hadn’t після if прийнятні |
| C2-4 | Printed copy → Should; ferry → Were ... to stop; courier → Had ... arrived | Усі три задані моделі виконано, головні частини й could have sent збережені; переклади умовні, не наказове should |
| C2-5 | `Had Lena not checked the address, the parcel might have gone to the wrong office.` | Not після підмета, минулий час та might незмінні. Hadn’t Lena checked...? було б питанням, не заданою умовою |
| C2-6 | `Assuming ... might open ... on condition that ...; otherwise ...` | Непідтверджений грант лишається припущенням, відкриття лише можливе, письмове схвалення — вимога. Provided that допустиме у другій частині; otherwise має конкретний antecedent |

Тексти вправ різні за цілями та ситуаціями; не є копіями зі зміною імен. Автоматично перевірено 18 унікальних prompts і відсутність точних повторів самоперевірок прийнятих M11–M14. Відкриті ключі позначено як приклади відповіді, альтернативи пояснено.

## 6. Реальне локальне застосування

Робоча БД — локальна MySQL `gr2`. Відповідність справжньому Apache vhost gramlyze.loc підтверджено свіжим фізичним web/CLI proof: loopback, процеси/порти, data directory та підписаний runtime доказ. Remote/forwarding/split перевірки не послаблено. `.env`, APP_KEY і `APP_ENV=production` залишилися незмінними; використано явний local-target opt-in.

Власні мінімальні wrappers:

- `ConditionalsContentPatch` наслідує прийнятий `LinkingWordsContentPatch`, має рівно три повні identities й власний M15 manifest.
- `M15LocalTargetGuard` використовує той самий guard-контракт, окремий приватний каталог і тимчасовий endpoint.
- `content:patch-conditionals-m15` — тільки явний CLI preview/apply/restore, без HTTP/startup/Composer/Git/scheduler hooks.
- `run-m15-working-local.php` завантажує незмінений робочий застосунок і лише M15 definitions із worktree.
- Versioned `database/content-patches/m15-conditionals-before.json` — старі публічні definitions з прийнятого Git-коміту M14, не дамп БД і не приватний record-backup. Перевірено точну відповідність усіх трьох definitions Git-базі.

Послідовність виконана після завершення редагування й форматування sources:

1. Свіжий фізичний proof: `2026-09-27T18:29:05+00:00`.
2. `preview-final.json`, перевірено рівно дозволені записи/поля та відсутність ручного конфлікту. SHA-256 preview: `5df73b73c6600b1557002a7ee81628a1a74ab833af8d9ba7327e7e878c7a637a`.
3. Новий exclusive `records-before-final.json` збережено до запису.
4. Транзакційний apply та postcondition: `status=applied`, `updated=12`.
5. Повторний apply того самого плану: `status=no-op`, `updated=0`; зайвий backup для no-op не створився.

Фактичні 12 записів — 3 `pages.text` і 9 UK `text_blocks` у межах дозволених heading/body. ID/UUID, порядок, locale, title, slug, категорії/ancestry, рівні, теги та relations не змінилися. Sources/manifest після фінального preview/apply не редагувалися; hashes звірені перед commit.

Приватні preview, record-backup, proof і діагностика залишені поза Git у:

`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m15-local/`

Backup: `records-before-final.json`. Він не видалений. Whole-DB restore, db:seed, міграції, truncate, масові UPDATE та очищення sessions/caches не виконувалися. Guarded restore перевірявся тільки в ізольованих тестах; робоче застосування не потребувало restore/reapply.

Тимчасовий read-only proof endpoint прибрано після приймання: GET повернув 404. `routes/api.php` відновлено побайтово до попереднього стану, включно зі сторонніми змінами. SHA-256 відновленого файлу: `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`.

## 7. Автоматичні перевірки

Ізольований PHP runner: SQLite `:memory:`, cache/session `array`, окремі storage/views; робочий `.env` не завантажується.

```text
run-isolated-tests.py --php "C:/Program Files/xampp/php/php.exe" --label m15-accepted
  tests/Feature/ConditionalsContentPackageTest.php
  tests/Feature/ConditionalsContentPatchTest.php
  tests/Feature/M11LocalTargetGuardTest.php
```

Результат: **41 тест, 617 assertions, exit 0**. Одна наявна deprecation у `config/database.php:62`: `PDO::MYSQL_ATTR_SSL_CA` на PHP 8.5; окремо локалізована, не спричинена M15 і не прихована.

Перевіряються JSON/hero JSON, rich opt-in, 18 різних вправ і пояснених ключів, реальний TheoryController H1 та довга B2-назва, title/description/OG/Twitter без повтору назви, курсова partial, resolver/test href, UUID/order/category normalization. Production SEO-профіль перевірено виключно in-memory Request/Response, без production HTTP.

Patch-тести покривають old→new/no-op, exact allowlist, ручні зміни, stale sources/ancestry/locales/pivots, partial state, exclusive backup, corrupt preview, rollback apply/restore, refusal restore після стороннього редагування, непідтверджений target і прийнятий local opt-in. Спільні helpers не змінювалися.

```text
node --test tests/Browser/seo-m15-local.test.cjs
```

Результат: **8/8 тестів**. Фіксований локальний inventory, заборона production/stateful запитів, окремі allowlists, fail-fast для стиснутої/непрокручуваної таблиці, зміни захищених metadata/уроків/sitemap, plain-render fallback і неповної практики.

Pint застосовано лише до шести нових PHP-файлів; syntax та `git diff --check` перевірено. Повний repository suite, dependency updates та build не запускалися: build inputs не змінені.

## 8. HTTP, браузер та оформлення

HTTP before: `2026-09-27T18:17:19.852Z`; after: `2026-09-27T18:31:25.608Z`. Використано GET до локального сайту, не fixtures/підміну HTML. Порівняння `final-comparison.json`: pass.

13 фіксованих адрес повернули 200 before/after:

- три цільові theory URL та три основні mixed-тести з таблиці вище;
- курсова копія B2;
- [Participle Clauses Basics, контроль M14](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses-basics);
- [Advanced Linking Devices, суміжний контроль](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices);
- [First Conditional](http://gramlyze.loc/theory/conditionals/first-conditional);
- [Mixed Conditionals](http://gramlyze.loc/theory/conditionals/mixed-conditionals);
- [Inversion Basics](http://gramlyze.loc/theory/basic-grammar/word-order/inversion-basics);
- [sitemap.xml](http://gramlyze.loc/sitemap.xml).

Браузер: Chromium/Playwright, desktop 1440×1000 і mobile 390×844. **6/6 основних сценаріїв**, кожний у світлій і темній темах, із новими context і звичайним reload. Новий матеріал присутній у початковій відповіді сервера та після reload. Перевірено нижню частину сторінки, 6 завдань/6 ключів, нумерацію, приклади/переклади, відкриття details мишею та Enter, закриття Space й повторне відкриття Enter. Три штатні desktop-переходи до основних mixed-тестів пройшли; відповіді не вводилися.

Картки побудовані чинним TheoryRichContent з raw top-level h4 і однією self-check section. Згенеровані theory-rich wrappers у definitions/БД не зберігалися. Renderer, Blade-компонент, CSS, JS, палітра й CDN не змінені.

| Таблиця | Мінімальна ширина | Mobile wrapper | Фактичний scrollLeft | Документний overflow |
|---|---:|---:|---:|---|
| B2, 4 колонки вираз/значення/приклад/переклад | 720 px | 322 px | 398 px | немає |
| C1, 5 колонок часу/прикладу/форми/перекладу | 840 px | 322 px | 518 px | немає |
| C2, 3 колонки if/інверсії/пояснення | 680 px | 322 px | 358 px | немає |

Скриншоти збережено приватно й переглянуто: усі три мобільні таблиці ліворуч/праворуч, відкриті темні ключі, desktop intro/ключі, контроль M14 із прокрученою таблицею, Advanced Linking Devices, guest course. Довгу B2-назву не скорочено; вона нормально переноситься. Приклади та пояснення читабельні, широкі таблиці реально прокручуються локально, не стискаються до вузьких колонок.

Додатково **4/4 контрольні browser-сценарії**: два незмінені уроки × desktop/mobile, обидві теми.

Браузерні обмеження, окремо від application failures:

- JavaScript page errors не виявлено.
- У головному проході 15 CSS-запитів Google Fonts завершилися `net::ERR_NETWORK_ACCESS_DENIED`. Приймання відбувалося з fallback fonts; оригінальні Google Fonts не названо завантаженими. Це обмежує висновок про їхню фактичну типографіку.
- Три автоматичні state POST навмисно перервані read-only runner, `net::ERR_FAILED`; це не помилки застосунку. Пов’язані console resource errors класифіковані окремо.
- Курсова копія відкривається 200, але навчальний блок гостю не показується через gate. Gate не обходили; відображення відкритого курсового контенту покривається ізольованим partial-тестом, не заявляється як live guest acceptance.

## 9. Metadata, sitemap і незмінність даних

Page.title, фактичні H1, title, canonical, robots та основні test href незмінні. Для довгої B2-назви збережено повний текст `Conditionals with Unless, Provided That, and As Long As`. Локальний X-Robots-Tag лишився `noindex, nofollow, noarchive`; meta robots відсутній як раніше. `.com` canonical/sitemap аналізувалися тільки як рядки.

Через чинний PageMetadata змінилися лише описи трьох цільових уроків. Meta description = OG description = Twitter description; назву автоматично додано рівно один раз:

- B2: `Conditionals with Unless, Provided That, and As Long As. Умову можна подати як виняток, чітку вимогу або домовленість.`
- C1: `Advanced Conditionals. Змішані умовні речення пов’язують різні часові плани.`
- C2: `Conditional Alternatives And Nuance. Вимога, припущення й неявна умова виконують різні функції.`

Ordered sitemap: фактично отримано **554 URL**, а не припущено за baseline. Список і порядок before/after однакові; SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.

Перед apply, після apply та перед commit прочитано й порівняно hashes усіх **46 таблиць**, виключивши тільки явно дозволені поля цільових записів. Решта даних незмінна, зокрема question banks, answers/options/verb_hint, progress/sessions і relations. Додатково всі 12 accepted M11–M14 sources точно відповідають своїм робочим записам; контрольні тексти й metadata HTTP не змінилися. `.env` побайтово незмінний.

## 10. Артефакти, Git та залишкові межі

У пакет входять тільки три definitions, M15 before-manifest, три мінімальні PHP wrappers, локальний CLI adapter, вузький diagnostic profile, два PHP-тести, один Node-тест і цей звіт. Приватні proofs/HTML/screenshots/backup, runtime caches, vendor, .env та сторонні зміни до Git не включаються.

Перед commit перевірені staged diff, список файлів, секрети й whitespace. Коміт і звичайний push виконуються лише в `codex/seo-m15-conditionals-content`; точний підсумковий SHA та результат звірки remote HEAD наводяться у фінальній відповіді, щоб не записувати самопосилальний SHA всередині коміту.

Залишкові межі: Cambridge direct 403, fallback fonts у browser runner, чинний guest course gate, наявна PHP deprecation. Це не замінено припущеннями про production. Не виконувалися повний crawl, Lighthouse, LCP/CLS-кампанія, перевірка відповідей Mixed, платні AI API, реальна пошта, production HTTP/SSH/БД, PR, merge, workflow dispatch або деплой.

**Production не перевірявся й не змінювався.**
