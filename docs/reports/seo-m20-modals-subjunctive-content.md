# M20 — модальні конструкції, необхідність і формальні вимоги

Дата приймання: 28 вересня 2026 року. **Зміни застосовано до робочого gramlyze.loc.**

## Межі й фактична база

- Робоча гілка: `codex/seo-m20-modals-subjunctive-content`.
- База: `9a2421bf83d90114bab9852d67edfbcf361fe8ec`, прийнята M19, гілка `codex/seo-m19-passive-reporting-causative-content`. Після fetch відповідний origin ref збігався; M20 продовжує цю історію, а не старий main.
- Використано наявний вільний worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`. Основний checkout із численними сторонніми змінами не перемикався, не скидався і не комітився. Його вихідний HEAD — `41820a2bebdf69004fa7209a2a38457f93efabbd`.
- Прочитано AGENTS.md, звіт M19, звіт стилізації M12 та чинний TheoryRichContent.
- Змінено лише три навчальні definitions, вузький before-manifest, M20 wrappers/профіль діагностики, цільові тести та цей звіт.
- Не змінено renderer, Blade, JS/CSS застосунку, dependencies, старі manifests/allowlists, формати plan/backup/restore, Mixed-банки, verb_hint, відповіді, options або прогрес.
- Не виконувались production-запити, SSH, деплой, PR, merge, workflow dispatch, push у main, force push, full seed, міграції, full DB restore, очищення сесій чи масове очищення кешу.

Рівні C1/C1/C2 збережено як редакційні позначки сайту, не як заяву про офіційну CEFR-сертифікацію.

## Точні цілі та справжні URL

Відповідності отримано з робочої БД, resolvers і навігації. Перед редагуванням три sources збігалися з точними навчальними before-значеннями БД; невідомих ручних конфліктів не було.

| Повна identity | Page / subtitle, hero, box | Category / ancestry | Локальна теорія |
|---|---|---|---|
| `Database\Seeders\Page_V3\ModalVerbs\ModalPerfectAndDeductionTheorySeeder` | 303 / 8796, 8797, 8798 | `modal-verbs`, root, uk, theory | [Modal Perfect and Deduction — C1](http://gramlyze.loc/theory/modal-verbs/modal-perfect-and-deduction) |
| `Database\Seeders\Page_V3\FormalEnglish\SubjunctiveAndFormalStructuresTheorySeeder` | 304 / 8799, 8800, 8801 | `formal-english`, root, uk, theory | [Subjunctive and Formal Structures — C1](http://gramlyze.loc/theory/formal-english/subjunctive-and-formal-structures) |
| `Database\Seeders\Page_V3\ModalVerbs\SubtleModalMeaningsTheorySeeder` | 320 / 8843, 8844, 8847 | `modal-verbs`, root, uk, theory | [Subtle Modal Meanings — C2](http://gramlyze.loc/theory/modal-verbs/subtle-modal-meanings) |

Для кожної identity перевіряється **її власна** категорія, а не членство категорії у спільному allowlist. Перестановка між двома дозволеними категоріями відхиляється без запису. IDs, UUIDs, locale, типи/колонки блоків, порядок, зв’язки, title, slug та ancestry збережено.

Канонічні навчальні джерела:

- `database/seeders/Page_V3/ModalVerbs/ModalPerfectAndDeductionTheorySeeder/definition.json`
- `database/seeders/Page_V3/FormalEnglish/SubjunctiveAndFormalStructuresTheorySeeder/definition.json`
- `database/seeders/Page_V3/ModalVerbs/SubtleModalMeaningsTheorySeeder/definition.json`

Основні тести, **лише перевірені переходи; банки не редагувалися**:

- [Modal Perfect and Deduction](http://gramlyze.loc/test/modal-verbs/modal-perfect-and-deduction)
- [Subjunctive and Formal Structures](http://gramlyze.loc/test/formal-english/subjunctive-and-formal-structures)
- [Subtle Modal Meanings](http://gramlyze.loc/test/modal-verbs/subtle-modal-meanings)

[Курсова копія Modal Perfect and Deduction](http://gramlyze.loc/courses/english-grammar-theory/lesson/modal-verbs/modal-perfect-and-deduction) перевірена без обходу штатного gate; межі перевірки наведено нижче.

## Початковий стан, виправлення та доповнення

До M20 усі три сторінки мали короткий англомовний subtitle/hero і службовий box про те, що сторінка є опорою для Sentence Builder. Розгорнутого українського пояснення і шести пояснених завдань не було.

### Modal Perfect and Deduction

Навчальна мета: вибрати форму для висновку або оцінки минулої дії та відділити відомі факти від того, що лише припускає мовець.

Збережено Past certainty, Past possibility, Regret or criticism. Нові авторські доповнення: будова modal + have + V3, заперечення, скорочення, have/of, таблиця «ситуація → форма → зміст → невідоме», власні контекстні приклади з перекладом.

Редакційні уточнення: must have не є незалежним доказом; could have не завжди означає невикористану можливість; should/ought to have можуть позначати очікування, не лише жаль. Can’t/couldn’t have розглядаються в конкретному контексті висновку, окремо від здатності, дозволу й заборони. Modal perfect не названо окремим особовим часом; scope минулого не поширено на всі можливі часові контексти.

Приклад нового розмежування: `Ravi could have taken the earlier bus; we haven’t heard from him` залишає результат невідомим; продовження `but he chose to walk` у другій ситуації явно задає невикористану можливість.

### Subjunctive and Formal Structures

Навчальна мета: побудувати вимогу чи рекомендацію без перетворення її на повідомлення про виконаний факт.

Збережено Mandative subjunctive, Formal alternatives, Fixed formal patterns. Замість надто широкого узагальнення про прикметники/дієслова важливості наведено конкретні слова й значення. Окремо розрізнено suggest-пропозицію / suggest-ознаку та insist-вимогу / insist-наполегливе твердження.

Нові доповнення: базова форма без -s, be для різних підметів, not перед формою, пасив be + V3, збереження виконавця/адресатів/строку, минуле recommended без автоматичного backshift і без вигаданого виконання, should-альтернатива та не абсолютні регіональні тенденції.

Lest подано як сполучник, а не багатослівний вислів. Be that as it may і suffice it to say збережено у короткому окремому розділі з різними функціями.

Приклад: прохання `The organiser requests that the entrance be kept clear` не доводить виконання; `reports that the entrance is clear` передає повідомлення про стан, але саме по собі не є незалежною перевіркою.

### Subtle Modal Meanings

Навчальна мета: пояснити контекстну функцію близьких конструкцій і межі точного перефразування.

Збережено Likelihood, Soft advice, Past necessity nuance. Додано may/could well, might just, коротке зіставлення well / as well, три моделі поради та власний діалог із перекладом. Worth і wise пояснено як прикметники у відповідних моделях, не нові модальні дієслова.

Редакційні уточнення: довший вираз не автоматично ввічливіший; would be wise to може бути виразним застереженням; немає вигаданої відсоткової шкали ймовірності. Для didn’t need to необхідність відділено від факту виконання: явно виконано / явно не виконано / невідомо. Перетворення на needn’t have потребує відомого факту виконання; відсутність необхідності не дорівнює забороні чи обов’язковому докору.

Приклад: `I didn’t need to print the map` без контексту не визначає результат; `but I did, for convenience` явно додає друк без суперечності.

Усі англійські ситуації авторські, вигадані, з українським перекладом. Це не юридичні приписи або медичні рекомендації. C1 не повторює базові модальні уроки повністю; Subjunctive не перетворено на повтор умовних речень; C2 не дублює M17 hedging.

## Джерела та фактичні межі доступу

Перевірка виконана 27–28 вересня 2026 року. Зовнішні приклади та вправи не копіювалися. Учнівські коментарі не використовувалися як правила.

| Перевірене питання | Першоджерело | Фактичний доступ і застосування |
|---|---|---|
| Must/may/might/could/can’t/couldn’t have; висновок проти здатності | [British Council: deductions about the past](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/modals-deductions-about-past) | Прочитано основний текст. Відповідь редакційної команди Peter M від 11.02.2026 відділено від учнівських коментарів. Правило застосоване до заданих контекстів висновку. |
| Should have: критика й очікування; ought to | [Cambridge: Should](https://dictionary.cambridge.org/us/grammar/british-grammar/should) | Пряме відкриття Cambridge повертало 403; перевірено доступний індексований текст видавця, не повну відкриту статтю. Не виводимо невиконання з будь-якого should have. |
| Needn’t have / didn’t need to | [Cambridge: Need](https://dictionary.cambridge.org/grammar/british-grammar/need), [Cambridge dictionary: need](https://dictionary.cambridge.org/us/dictionary/english/need?q=need+to), [British Council: пояснення команди](https://learnenglish.britishcouncil.org/comment/164994) | Cambridge — індексовані розділи після 403. Словникова стаття прямо охоплює як виконану, так і невиконану дію з didn’t need to. BC permalink при відкритті дав timeout/400, наступне відкриття — Internal Error; використано лише індексований фрагмент відповіді Peter M від 08.03.2019 07:04, не повну сторінку й не запитання учня. |
| Bare form, be, минула рекомендація; формальні конструкції | [Merriam-Webster: Getting in the Subjunctive Mood](https://www.merriam-webster.com/grammar/getting-in-the-subjunctive-mood) | Прочитано основний текст; вузька сфера вимог/рекомендацій, не всі значення subjunctive. |
| Suggest: дія або ознака; insist: вимога або твердження | [Cambridge: Suggest](https://dictionary.cambridge.org/us/grammar/british-grammar/suggest), [suggesting](https://dictionary.cambridge.org/dictionary/english/suggesting), [insist](https://dictionary.cambridge.org/us/dictionary/learner-english/insist) | Доступні індексовані видавничі розділи. Не оголошуємо indicative помилкою лише через suggest/insist. |
| Регіональна варіативність mandative | [Cambridge University Press: The mandative subjunctive](https://www.cambridge.org/core/books/abs/one-language-two-grammars/mandative-subjunctive/39A859FDAD907967392482CDBF53EB6A) | Доступне видавниче резюме розділу William Crawford, 2009; повний розділ не читався. Воно підтверджує тенденції AmE/BrE, не взаємне виключення bare/should. Сучасні числові частотності не заявляються. |
| Lest та два різні усталені вислови | [Cambridge: lest](https://dictionary.cambridge.org/us/dictionary/english/lest), [Merriam-Webster: be that as it may](https://www.merriam-webster.com/dictionary/be%20that%20as%20it%20may), [suffice it to say](https://www.merriam-webster.com/dictionary/suffice%20it%20to%20say) | Lest — індексований словниковий розділ; обидві MW статті прочитано. Автоматично зібрані новинні приклади не слугували правилами. |
| Well / as well / just | [Cambridge: may well](https://dictionary.cambridge.org/us/dictionary/english/may-well), [may as well and might as well](https://dictionary.cambridge.org/us/grammar/british-grammar/may-as-well-and-might-as-well), [just](https://dictionary.cambridge.org/us/dictionary/english/just), [видавнича стаття might](https://dictionary.cambridge.org/us/dictionary/english-french/might) | Індексовані видавничі визначення, включно з ліцензованою GLOBAL/PASSWORD статтею might; не повні відкриті Cambridge сторінки. Функція just встановлена для явно низької, але ненульової можливості у власному контексті, а не як універсальне значення. |
| Worth + -ing; wise + to-infinitive | [Cambridge: worth](https://dictionary.cambridge.org/us/dictionary/english/worth), [wise](https://dictionary.cambridge.org/us/dictionary/english/wise), [Worth or worthwhile](https://dictionary.cambridge.org/de/grammatik/british-grammar/worth-or-worthwhile) | Індексовані словникові/граматичні розділи; останній доступний результат мав німецький UI й англійський текст граматики. Пряме відкриття англомовного grammar URL — 403. |

Вузький навчальний контраст, де didn’t need to супроводжується невиконанням, не можна перетворювати на універсальне граматичне значення. Ширші словникові пояснення й відповідь BC дозволяють відділити необхідність від результату. У матеріалі показано всі три контексти; не вигадано суперечності між джерелами там, де різниться лише ширина пояснення. Захист сайтів не обходився, піратські копії не використовувалися.

## Редакторське приймання всіх 18 ключів

Це змістова перевірка форми, часу, заперечення, учасників, виконання, модальності, перекладу та альтернатив, а не лише автоматичний підрахунок. Нумерація відповідає сторінкам; зразки відкритих відповідей не оголошено єдиним можливим формулюванням.

### 1. Modal Perfect and Deduction — 6 завдань

1. **«Ознака чи висновок?»** Відкрита коробка і розірвана пломба — відомі ознаки; `Someone must have opened the box` — упевнений висновок про попередню дію. Хто, коли саме й чому відкрив — невідомо. Переклад «напевно» передає висновок, не стовідсотковий доказ. Заборонено дописувати конкретну особу.
2. **«Два задані висновки»** `Nora must have unlocked the door.` / `Eli can’t have been at the studio at nine.` Перевірено have + unlocked/been, минулу дію й минулу присутність о дев’ятій, Нору/Елі та заперечний висновок. `Cannot have been` — повна рівнозначна форма. `Couldn’t have been` можливе за змістом, але не виконує прямо задану модель can’t. Переклад не додає очевидця дії Нори.
3. **«Два could have»** У випадку відсутніх звісток про Раві результат невідомий; may/might have taken також виражають можливе пояснення. У другому випадку `but he chose to walk` явно задає невикористану можливість автобуса. Невиконання виводиться з контексту, не з будь-якого could have; учасник і минулий час не змінені.
4. **«Критика чи очікування?»** Непідписані папки — відома невиконана дія й критика. Посилка, час якої вже мав настати, — очікування; tracking ще не перевірено, тому недоставлення не доведене. `Ought to have arrived` можливе для другого значення. Українські «треба було» / «мала б» зберігають різницю.
5. **«Виправ три граматичні помилки»** `She might have left the note.` / `He must have gone home.` / `They can’t have seen the rehearsal.` Виправлено of → have, went → gone, don’t can → can’t/cannot. Усі дії минулі, особи збережені, might не підсилено до must. Переклади передають можливість / упевнений висновок / відкидання можливості. Повна форма й тип апострофа не змінюють правильності.
6. **«Відредагуй повідомлення»** `The studio was dark yesterday. The rehearsal may have been cancelled.` Допустимі might/could have been cancelled. Збережено учорашнє спостереження, passive perfect і непідтвердженість скасування; не додано винуватця. Must have надмірно підсилює висновок, was cancelled робить його фактом. Переклад із «можливо» відповідає умові.

### 2. Subjunctive and Formal Structures — 6 завдань

1. **«Вимога чи факт?»** `Requests … be kept clear` — прохання про бажаний стан, пасивна базова форма без доказу виконання; `reports … is clear` — повідомлення організатора про стан. Переклад не підміняє бажане дійсним і не оголошує повідомлення незалежною перевіркою.
2. **«Задана базова форма»** `The coordinator recommends that each reviewer read the brief by Friday.` Read — базова форма з вимовою /riːd/, не Past Simple; кожен рецензент, короткий опис і строк до п’ятниці збережені. Should read граматично допустиме, але не виконує завдання «без should». Reads не відповідає заданій subjunctive-моделі; це не універсальна заборона indicative-рекомендацій.
3. **«Заперечна й пасивна вимога»** `The editor requires that the assistant not delete the comments.` / `The editor requires that the reviewers be informed by the assistant by noon.` Not стоїть перед delete; be informed — потрібний пасив, не are informed у заданій моделі. Збережено асистента, коментарі/рецензентів та строк. Перше by вводить виконавця, друге — noon; порядок by noon by the assistant можливий, але важчий, як і подвійне by загалом. Вимогу не видано за виконання.
4. **«Минула рекомендація»** `On Monday, Leo recommended that Emma check the heading before publication.` Recommended датує пораду, check не стає checked. Лео, Емма, понеділок та before publication збережені. Should check — допустима інша модель, не замовлена тут. Український переклад не додає факту перевірки.
5. **«Два значення suggest та insist»** Відсутня етикетка suggests … is — ознака можливого факту; insists … be moved — вимога; insists … is — наполягання на правдивості. Не замінюємо is на be в першому/третьому реченнях лише через головне дієслово. Коробку не оголошено вже перенесеною. Переклад «що» проти «щоб» зберігає функцію.
6. **«Формальний сполучник»** `She labelled both folders so that the reviewers would not confuse them.` Збережено минуле маркування двох папок, рецензентів і мету запобігання. Lest вводив небажану подію confuse; механічне not після lest змінило б її на неплутання саме в цій ситуації. Це не заборона будь-якого not після lest. Would not у заданому so that природне; фактичного успіху не додано.

### 3. Subtle Modal Meanings — 6 завдань

1. **«Well чи as well?»** Відсутній кабель may well explain — правдоподібне непідтверджене пояснення; might as well rehearse у дворі — практична пропозиція через зачинену залу. Не встановлює, що репетиція вже відбулася. Заміна well/as well змінює функцію, не лише ступінь упевненості; переклади це розрізняють.
2. **«Контекст just»** `Our sketch might just make the final selection.` За прямо заданих невеликих, але ненульових шансів — «можливо, таки». Just не означає тут «щойно» й не гарантує результат. Майбутній відбір не перетворено на минулу подію; інші значення just в інших контекстах не заперечуються.
3. **«Три задані моделі поради»** `You might want to check the participant list before printing.` / `It may be worth checking the participant list before printing.` / `You would be wise to check the participant list before printing: the previous copy listed one name twice.` Перевірено to check / checking / to check, спільну мету до друку, відому минулу підставу третьої репліки. Wise може бути виразним застереженням, не автоматично найслабшою порадою. Should check природне, але не одна з трьох заданих моделей. Українські переклади не роблять пораду наказом.
4. **«Необхідність окремо від виконання»** У трьох реченнях про карту необхідності немає. So I didn’t явно задає невиконання; but I did — виконання; без продовження результат невідомий. Немає заборони, суперечності чи обов’язкового докору за друк для зручності. Переклад третього варіанта не дописує результат.
5. **«Чи можна перетворити?»** Лише після доданого факту бронювання: `Omar needn’t have booked another room.` / `Omar need not have booked another room.` В а) бракує саме виконання; в б) воно відоме. Перевірено, що інструкція запитує відсутній факт **у випадку а)**. Perfect зберігає виконану минулу дію й непотрібність; переклад це називає прямо. Якщо Омар не бронював, цей ретроспективний needn’t have не підходить.
6. **«Відредагуй мінінотатки»** `Ira needn’t have printed the extra poster yesterday. Missing clips could well explain today’s delay. It may be worth checking the cupboard.` Збережено виконаний друк учора й відсутність потреби, нез’ясовану причину сьогоднішньої затримки, пораду на зараз, checking після worth. May well також природне, але прямо задано could well. Не додано знайдених скріпок чи успішного усунення затримки. Переклад розрізняє факт друку, припущення та пораду.

## Read-only суміжні уроки та внутрішні посилання

Усі наведені сторінки перевірені реальними GET до/після; status 200, основний текст і metadata незмінні. Повний префікс кожної identity — `Database\Seeders\Page_V3\`.

| Identity після префікса | Локальний URL / роль |
|---|---|
| `ModalVerbs\ModalVerbsDeductionTheorySeeder` | [Modals of Deduction](http://gramlyze.loc/theory/modal-verbs/modals-of-deduction), базовий контекст |
| `ModalVerbs\ModalVerbsMayMightTheorySeeder` | [May/Might](http://gramlyze.loc/theory/modal-verbs/may-might), базовий контекст |
| `ModalVerbs\ModalVerbsShouldOughtToTheorySeeder` | [Should/Ought to](http://gramlyze.loc/theory/modal-verbs/should-ought-to) |
| `ModalVerbs\ModalVerbsMustHaveToTheorySeeder` | [Must/Have to](http://gramlyze.loc/theory/modal-verbs/must-have-to) |
| `AcademicEnglish\HedgingAndCautiousLanguageTheorySeeder` | [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language), M17 control |
| `FormalEnglish\RegisterToneAndParaphraseTheorySeeder` | [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase), M16 |
| `Conditionals\AdvancedConditionalsTheorySeeder` | [Advanced Conditionals](http://gramlyze.loc/theory/conditionals/advanced-conditionals), M15 |
| `PassiveVoice\PassiveReportingStructuresTheorySeeder` | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures), styled M19 control |

Посилання в новому контенті ведуть на чинні theory URL; додаткових цілей запису не створено.

## Guarded local apply

1. Definitions і before-manifest відредаговано, відформатовано й перевірено через `git diff --check` **до** фінального preview.
2. Свіжий guard підтвердив фізичний Windows MySQL, loopback, робочу БД і справжній Apache vhost `gramlyze.loc`. Sandbox спочатку не дозволив необхідну read-only інспекцію listener/process; виконано вузьке дозволене читання без послаблення remote/forwarding/split перевірок.
3. `APP_ENV=production` лишився без змін; це локальний профіль, не production-сервер. `.env`, APP_KEY і підключення не редагувалися.
4. Перевірено точні 3 identities, категорії та scope свіжого `plan-final.json`; plan SHA-256:
   `63650a39f88056157cd2153e0e6d14c9a1ab475b9bc6898eb5766516e34e504d`.
5. Створено новий exclusive backup, виконано одну транзакційну apply-операцію з postcondition.
6. Фактичний результат: **applied, updated = 12** — три `pages.text` і дев’ять записів `text_blocks` із дозволеними навчальними heading/body. Це результат порівняння, не примус до числа 12.
7. Повторний виклик із тим самим plan: **no-op, updated = 0**; новий backup при no-op не створювався.
8. Після браузерного приймання hashes усіх трьох definition-файлів і before-manifest досі збігаються з plan. Не було restore/reapply заради форматування.
9. Тимчасовий HTTP proof route прибрано, допоміжний PHP endpoint видалено, реальний GET повертає **404**. `routes/api.php` відновлено побайтово до приватної before-копії (SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`), зі збереженням сторонніх правок.

Backup збережений локально, **не в Git**:

`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m20-local/records-before-final.json`

Вузький versioned manifest для можливого майбутнього адресного перенесення:

`database/content-patches/m20-modals-subjunctive-before.json`

Він не містить секретів або повного дампа. Production-перенесення зараз не розроблялося.

## Незмінність даних

Read-only fingerprints отримано до запису, після запису й після browser acceptance (`protected-commit-ready.json`).

- **46/46 таблиць**: row counts і hashes захищених полів незмінні. Виключення рівно для погоджених полів 3 pages та 9 blocks; IDs/UUIDs, зв’язки й усі інші поля включені в перевірку.
- **27/27 прийнятих уроків M11–M19** досі збігаються з versioned sources у робочій БД.
- Mixed questions/answers/options, saved tests, їхні зв’язки, hints і прогрес залишилися незмінними за DB fingerprints. Динамічний HTML тестів не використовувався як побайтовий доказ банку.
- Hash `.env` незмінний; значення або його вміст не публікуються.
- Повний дамп БД не створювався.

## HTTP, metadata та sitemap

Before capture: `2026-09-28T00:05:00.409Z`; after: `2026-09-28T00:19:17.065Z`; comparison: `2026-09-28T00:20:50.436Z` (UTC).

**16/16 URL** до/після повернули 200: три теорії, три тести, одна курсова копія, вісім суміжних сторінок і sitemap. Порівняння status, Location, Content-Type, X-Robots-Tag, H1/title/canonical/robots та test href пройшло.

- Page.title та controller-derived H1 лишилися точними англійськими назвами з таблиці цілей.
- Title кожного уроку: `<назва> — правила | Gramlyze`; назва не дублюється.
- Meta robots на цих theory HTML відсутній як і до змін; локальний X-Robots-Tag збережений: `noindex, nofollow, noarchive`.
- Canonical залишився тим самим рядком `https://gramlyze.com/theory/…`. URL .com не запитувався.
- Змінилися лише description / OG description / Twitter description **трьох цільових theory**. Кожна трійка збігається; інші перевірені descriptions не змінені.

Нові фактичні descriptions:

1. Modal Perfect and Deduction. Навчися відрізняти відомі ознаки від висновку про минулу подію, обирати modal + have + V3 та не додавати непідтверджених контекстом результатів.
2. Subjunctive and Formal Structures. Побудуй формальну вимогу чи рекомендацію з базовою формою дієслова та відрізни бажану дію від повідомлення про факт і відомостей про виконання.
3. Subtle Modal Meanings. Розрізняй правдоподібне припущення, пораду й оцінку непотрібної дії.

Ordered sitemap: **554 URL фактично отримано**, не зашито як очікувану кількість. Порядок і весь список before/after однакові; SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Значення .com аналізувалися лише як рядки.

## Оформлення й реальний браузер

Чинний TheoryRichContent використано без змін: hero із трьома картками; нумеровані секції; оформлені приклади; practice і розгортані пояснені ключі; блок внутрішніх посилань. У sources не збережено HTML-вихід renderer. Не додавались CSS/JS/CDN чи нова палітра.

Playwright / Chromium **147.0.7727.15**:

- **6/6 основних сценаріїв**: три уроки × desktop 1440×1000 / mobile 390×844, кожний у light і dark.
- Справжній початковий server HTML, новий browser context та reload: 6 питань + 6 ключів на кожному уроці зберігаються; content hashes server/DOM/reload збігаються.
- 8 rich sections на кожному уроці; 25/26/25 оформлених прикладів відповідно. Є контент нижче першого екрана, нумерація і переклади.
- Details відкриваються мишею, Enter і Space. Сирі HTML-теги/службові placeholders не проявилися.
- Document overflow відсутній в обох темах.
- Кожна mobile-таблиця: viewport 322 px, content 1001 px, реальний scrollLeft 679 px. Колонки мають читабельну ширину приблизно 250 px замість вузького переносу по слову.
- Три штатні desktop-переходи до основних тестів пройшли; status 200, очікувані href/H1. Відповіді не вводилися.
- **4/4 контрольні сценарії**: M19 Passive Reporting Structures і M17 Hedging and Cautious Language × desktop/mobile, обидві теми й reload — без регресій.
- Application page errors: 0. Автоматично перевірено контраст вибраних текстових зразків, не заявляється повний accessibility-аудит.
- Вручну переглянуто screenshots: вступи/hero всіх трьох сторінок, усі три mobile-таблиці включно з правими колонками, довгі формули, C2 діалог з перекладом, довгі ключі в dark theme. Окремо знято 18 detail screenshots для секцій 0/4/5 на desktop/mobile. Підміни через setContent/route.fulfill/innerHTML/fixtures не було.

Фактичні обмеження:

- Google Fonts `https://fonts.googleapis.com/css2` у цьому browser runner повернув **net::ERR_NETWORK_ACCESS_DENIED**. Приймання відбулося з доступними fallback-шрифтами; це не доказ дефекту сайту або доступності зовнішнього шрифту в іншому середовищі.
- Три автоматичні `/test/…/state` запити свідомо заблоковані read-only runner: **stateful-request / net::ERR_FAILED**. Вони відділені від application failures; дані прогресу не записувалися.
- Курсова копія: 200, правильний H1, server HTML містить нові 6 питань і 6 ключів, але **contentVisible=false** через штатний gate. Gate не обходився. Не заявляється візуальне приймання розблокованого курсу; course partial окремо перевірено ізольованим тестом.
- Build не запускався, бо build inputs не змінювалися. Full suite, Lighthouse, M10/performance campaign та full crawl не виконувалися.

## Автоматичні перевірки

| Перевірка | Результат |
|---|---|
| `tests/Feature/ModalsSubjunctiveContentPackageTest.php`, `ModalsSubjunctiveContentPatchTest.php`, `M11LocalTargetGuardTest.php` через чинний isolated runner | **42 tests, 765 assertions**, 0 failures, 1 deprecation |
| `node --test tests/Browser/seo-m20-local.test.cjs` | **8/8 pass** |
| PHP Pint `--test` для шести нових PHP-файлів | PASS |
| JSON/hero, rich opt-in, 18 різних питань/ключів, H1/title/meta, resolvers/course href | PASS у цільових PHP tests |
| Old → new, no-op, exclusive backup, rollback, guarded restore, stale source і manifest bytes, manual/partial/identity/ancestry/relations conflicts | PASS; відмови до запису перевірені |
| Неправильна **інша дозволена M20 category** для кожної з трьох identities | PASS; БД і інші цілі після відмови незмінні |
| Непідтверджена ціль і production default без explicit verified local opt-in | Відмова перевірена |
| Live HTTP comparison, шість browser scenarios, чотири controls | PASS з явно описаними мережевими/gate обмеженнями |
| Staged diff / `git diff --check` / перевірка scope і секретів | PASS; лише 13 пов’язаних файлів M20, без секретів і заборонених шляхів |

PHP **8.5.10**, PHPUnit **12.5.35**, SQLite `:memory:`, environment testing, isolated storage, array cache/session, CLI opcache off; робочий .env не завантажувався в PHPUnit. Основний прогін тривав 10.598 s, 80 MB.

Фактична deprecation перевірена окремим цільовим прогоном: `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` deprecated у PHP 8.5; рекомендована константа `Pdo\Mysql::ATTR_SSL_CA`. Це не failure M20; конфіг поза scope не редагувався. Порожній файловий fingerprint isolated runner не видається за перевірку реальної БД: для неї окремо застосовані описані вище 46 table fingerprints.

## Приватні докази та Git

Приватний каталог, не включений у commit:

`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m20-local/`

Там збережені inventory-before, protected-before/after/commit-ready, final plan, exclusive record backup, before/after HTTP, comparison, accepted/controls browser JSON, detail screenshots. Proof-дані та backups не публікуються. PHPUnit result збережено окремо в worktree `storage/app/seo-m2-local/m20-content-final-37beef96e20b4033bed803fd438e5651-result.json`.

До commit призначені лише 13 пов’язаних файлів M20. Без .env, vendor/build, dumps/backups, runtime caches, приватних diagnostics/proofs або сторонніх правок. Фінальний SHA та підтвердження звірки remote/local після звичайного push наведено у відповіді; звіт не підставляє власний ще не створений commit hash.

Production не перевірявся й не змінювався.
