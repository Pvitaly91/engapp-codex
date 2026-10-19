# M19 — Passive Reporting, Causative та Impersonal Style

Дата: 28 вересня 2026 року, Europe/Kyiv. **Зміни застосовано до робочого gramlyze.loc.**

## База й межі

Прочитано AGENTS.md, прийняті звіти M18 і M12 styling. Після fetch перевірено refs, ancestry та status: база — `codex/seo-m18-argumentation-cohesion-content`, `611588819e994abea5877e29954bd055d4bb3054`, збігалася з origin. Новішого M19 на remote не було. Створено `codex/seo-m19-passive-reporting-causative-content` від цієї бази, не від старого main.

Повторно використано вільний worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`. Основний checkout `codex/production-ready-a14788dac` / `41820a2bebdf69004fa7209a2a38457f93efabbd` містив сторонні незавершені зміни: його не перемикали, не скидали, не stash-или й не комітили. Тимчасову добавку до routes/api.php прибрано зі збереженням початкових байтів.

Scope — рівно три українські definitions і відповідні записи теорії. C1/C1/C2 — редакційні позначки сайту, не офіційна CEFR-сертифікація. Mixed-банки, відповіді, підказки та прогрес не змінювалися. Без full seed/migrate, cache/session clear, rebuild, dependency update, ремонту XAMPP/hosts, production HTTP/SSH, PR, merge, workflow dispatch або деплою.

## Ідентичності й локальні URL

Адреси отримано з робочих Page/test/course resolvers і навігації, потім перевірено реальними GET. Definitions розміщені в `database/seeders/Page_V3/PassiveVoice/<Seeder>/definition.json`.

| Повна Page.seeder identity | Теорія / рівень | Основний тест |
| --- | --- | --- |
| `Database\Seeders\Page_V3\PassiveVoice\PassiveReportingStructuresTheorySeeder` | [Passive Reporting Structures](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures), C1 | [Тест](http://gramlyze.loc/test/passive-voice/passive-reporting-structures) |
| `Database\Seeders\Page_V3\PassiveVoice\ComplexPassiveAndCausativeTheorySeeder` | [Complex Passive and Causative](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative), C1 | [Тест](http://gramlyze.loc/test/passive-voice/complex-passive-and-causative) |
| `Database\Seeders\Page_V3\PassiveVoice\ComplexPassiveImpersonalStyleTheorySeeder` | [Complex Passive Impersonal Style](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style), C2 | [Тест](http://gramlyze.loc/test/passive-voice/complex-passive-impersonal-style) |

Фактична категорія всіх трьох — коренева `passive-voice`, parent_id=null, language=uk, type=theory. Це перевірено в БД, не виведено з PHP-каталогу. M19 wrapper перевіряє саме цю ancestry; mappings трьох категорій M18 не копіювалися.

Page.title та controller-derived H1 збігаються з наведеними назвами, включно з регістром, і збережені. HTML title — та сама назва + ` — правила | Gramlyze`; title тесту — назва + ` — тест | Gramlyze`.

[Курсова копія Reporting](http://gramlyze.loc/courses/english-grammar-theory/lesson/passive-voice/passive-reporting-structures) перевірена без обходу штатного gate; обмеження нижче.

Read-only переглянуті суміжні уроки; їхній контент не змінено:

- [Advanced Passive Voice](http://gramlyze.loc/theory/passive-voice/advanced-passive-voice): наявна коротка опора, не додаткова ціль і не посилання на нібито готовий розгорнутий урок.
- [Formation Rules](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-formation-rules), [Causative](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-causative), [Impersonal Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-impersonal-passive), [Get-passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-get-passive).
- [Passive Infinitive](http://gramlyze.loc/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive).
- [Hedging and Cautious Language](http://gramlyze.loc/theory/academic-english/hedging-and-cautious-language), [Register Tone and Paraphrase](http://gramlyze.loc/theory/formal-english/register-tone-and-paraphrase), [Participle Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/participle-clauses).
- [Argumentation and Academic Tone](http://gramlyze.loc/theory/academic-english/argumentation-and-academic-tone) — styled-контроль M18.

## Before → after та розмежування уроків

Перед редагуванням робоча БД точно збігалася з Git-source за цільовими subtitle/hero/box і pages.text. Звірено seeder, slug, IDs/UUIDs, owner, locale, category, порядок і зв’язки. Невідомих ручних редакцій не виявлено.

У before кожен урок мав короткий англомовний intro, три hero-опори й службовий box замість повного уроку. Правильні вихідні ідеї збережено; додано українські пояснення, власні приклади з перекладом, таблицю, розбір контексту, шість завдань і шість пояснених ключів.

| Урок | Що вже було | Що розгорнуто / уточнено |
| --- | --- | --- |
| Reporting C1 | Impersonal passive, personal passive, perfect infinitive | Дві моделі, послідовність перетворення, підмет не обов’язково людина, два часові рівні, backshift, процес і майбутнє очікування, узгодження, джерело й сила reporting verb. Навчальна мета — самостійно побудувати точне повідомлення. |
| Causative C1 | Causative have/get, complex passive, formal focus | Окремо організатор, виконавець, об’єкт і результат; have/get object V3, have person do, get person to do; Past Perfect проти causative; минуле, звичка, процес, план, питання/заперечення; небажана подія без наміру потерпілої особи. Мета — вибір за ситуацією, а не друга копія reporting-таблиці. |
| Impersonal C2 | Безособова модель, винесений підмет, perfect passive infinitive | Два рівні повідомлення та різні by-учасники; час незалежно від voice; active/perfect/passive/continuous; майбутня точка відліку; been + adjective; сфера not, модальність, джерело, важкі форми й ясна that-альтернатива. Мета — аналіз і редагування без зміни відомостей. |

Уточнені межі старих коротких правил:

- Reporting: `to have + V3` показує попередність у заданому контексті, але Past Simple у that-clause не є автоматичним тригером. `was believed to be empty` описує одночасний минулий стан; `was believed to have left` — ранішу дію. Простий інфінітив у `is expected to arrive tomorrow` не означає «завжди теперішнє».
- Causative: старий опис домовленої послуги правильний для такого контексту, але не вичерпує `have + object + V3`: небажана подія не доводить замовлення чи вини. Формальний пасив не гарантує об’єктивності. Get не оголошено завжди неправильним у письмі або завжди результатом тривалого переконування.
- C2: старе «пасивний перфектний інфінітив для ранішої дії» розділено на дві перевірки. Час сам не обирає passive; підмет має зазнавати дії. `to have been empty` — не пасив лише через been. З очікуванням до майбутнього дедлайну perfect не обов’язково означає минуле відносно сьогодні.

Короткі нові контрольовані пари:

- `At noon, people believed that the studio was empty.` → `At noon, the studio was believed to be empty.` Одночасність, а не вигадана попередність.
- `Leo had his shelf painted on Monday.` за явно заданим замовленням ≠ `Leo had painted his shelf himself before Monday.` Різні виконавці, будова й часовий контекст.
- `The file is not known to have been deleted` ≠ `The file is known not to have been deleted`. Відсутність знання не перетворена на знання про невидалення.
- Неправильну за умовою нотатку `Forty programmes are known to have been delivered on Sunday` замінено повідомленням організаторки про друк, із неперевіреною кількістю та відсутністю відомостей про доставку. Це виправлення змісту, а не твердження, що сама англійська форма неграматична.

Усі навчальні ситуації явно вигадані. Відкриті ключі містять зразок і критерії; граматична помилка, зміна значення та невиконання заданого початку розрізнені.

## Першоджерела й фактичний доступ

Зовнішні сюжети й вправи не копіювалися. Нові приклади, інструкції, ключі та переклади — авторські для Gramlyze. Перевірка джерел не замінює окремого редакторського розбору нижче.

Повністю доступний основний навчальний текст [British Council — Advanced passives review](https://learnenglish.britishcouncil.org/free-resources/grammar/c1/advanced-passives-review) прочитано: дві reporting-моделі, simple/perfect/continuous і ролі учасників. Окремо розглянуто явно позначене пояснення команди LearnEnglish про два рівні та active/passive infinitive; учнівські коментарі не використовувалися як правила.

Прямі відкриття п’яти початкових Cambridge-адрес повернули **403 Forbidden**. Доступ не обходився. Для них використано лише доступні індексовані видавничі фрагменти; це **не повне прочитання сторінок**:

- [Have something done](https://dictionary.cambridge.org/grammar/british-grammar/have-something-done): послуга, небажаний досвід, відмінність від perfect та модель з виконавцем.
- [Passive: other forms](https://dictionary.cambridge.org/grammar/british-grammar/passive-other-forms): have/get object V3 та активний виконавець.
- [Get](https://dictionary.cambridge.org/grammar/british-grammar/get): get person to do / get object V3, контекстний регістр.
- [Infinitive: active or passive?](https://dictionary.cambridge.org/grammar/british-grammar/passive-active): вибір voice за відношенням підмета до дії.
- [Passive: uses](https://dictionary.cambridge.org/us/grammar/british-grammar/passive-uses): фокус і дистанція автора, а не автоматична істинність. Початкова адреса без /us/ також була недоступна; /us/ наведено як адресу отриманого індексованого фрагмента, не як обхід.

Додаткові індексовані пояснення самого видавця: [Perfect infinitive](https://dictionary.cambridge.org/grammar/british-grammar/perfect-infinitive) для майбутньої точки відліку; [Passive: forms](https://dictionary.cambridge.org/grammar/british-grammar/passive-forms) для process/perfect та читабельності важких поєднань; [Passives with and without an agent](https://dictionary.cambridge.org/grammar/british-grammar/passives-with-and-) для by-учасника.

Керування й значення конкретних дієслів додатково звірено з доступними редакційними словниковими фрагментами Cambridge: [say](https://dictionary.cambridge.org/dictionary/english/say), [believe](https://dictionary.cambridge.org/us/dictionary/english/believe), [report](https://dictionary.cambridge.org/dictionary/english/report), [be reported to be/do](https://dictionary.cambridge.org/us/dictionary/english/be-reported-to-be-do), [claim](https://dictionary.cambridge.org/dictionary/english/claimed), [expect](https://dictionary.cambridge.org/us/dictionary/english/expect), [be known to be/do](https://dictionary.cambridge.org/dictionary/english/be-known-to-be-do). Відділено моделі словника від випадкових корпусних/новинних прикладів. Не зроблено висновку, що всі reporting verbs взаємозамінні або допускають усі каркаси. Для claimed лишено контрольоване that-повідомлення; may збережено в that-clause, не створено `to may`.

Формулювання про незнання/знання негативного факту перевірене на явно заданих власних контекстах. Воно не поширюється як загальна теорема на всі belief-конструкції чи negative raising. Важку `to be being + V3` не оголошено неможливою: наведена прозоріша that-редакція з тим самим процесом.

## Окремий редакторський перегляд 18 ключів

Перевірено форми, часові опори, виконавців, заперечення, переклад і альтернативи. Це ручний змістовний перегляд, не висновок із кількості assertions.

### Reporting C1 — [урок](http://gramlyze.loc/theory/passive-voice/passive-reporting-structures)

| № / завдання | Перевірений ключ і критерії |
| --- | --- |
| 1. Гід повідомляє про відвідувачів | Джерело — guide; чекають visitors. Учасників не поміняно місцями; повідомлення й очікування — дві дії. |
| 2. Перебудова переконання про старий ліфт | `It is believed that the old lift is safe.` Теперішнє переконання збережене; known/proved без доказу не прийняті як рівнозначні. |
| 3. Двоє художників працюють зараз | `The two artists are reported to be painting a mural now.` Множинне are, процес to be painting. Perfect змінив би часовий зміст. |
| 4. Одночасна порожня кімната / попередній вихід / процес опівдні | Відповідно `to be empty`, `to have left`, `to be working`. Контексти явно задають час. Простий work граматичний в іншому значенні звички, але не виконує інструкцію про поточний процес. |
| 5. Узгодження та змішування that/to | `The halls are believed to be empty.` і `It is believed that the halls are empty.` Пояснено допустиме опущення that за збереження повної finite clause, а не halls to be. |
| 6. Координаторка в п’ятницю про кур’єра в четвер | `On Friday, the courier was reported by the coordinator to have arrived at four on Thursday. This has not been independently confirmed.` Збережено джерело, попередність, точний час at four (не by four) та непідтвердженість. Інші точні формулювання другого речення допустимі. |

### Causative C1 — [урок](http://gramlyze.loc/theory/passive-voice/complex-passive-and-causative)

| № / завдання | Перевірений ключ і критерії |
| --- | --- |
| 1. Олена, кравець і пальто | Olena — організаторка, tailor — виконавець, coat — об’єкт, shortened — завершене укорочення. Домовленість і завершення прямо задані, а не домислені. |
| 2. Замовлені 30 запрошень у середу | `Iryna had thirty invitations printed by the printing company on Wednesday.` Have object V3; кількість, виконавець і день збережені. 30 допустиме замість thirty. Звичайний passive не обов’язково неграматичний, але не виконує заданий початок з Iryna had. |
| 3. Полицю фарбував маляр / сам Лев | `had his shelf painted` — Past Simple causative за контекстом; `had painted his shelf himself` — Past Perfect і власноручна дія. Monday / before Monday та himself усувають неоднозначність. |
| 4. Питання й заперечення про вчорашню заміну екрана | `Did you have the screen replaced yesterday?` / `I did not have the screen replaced yesterday.` Перехід до I прямо заданий інструкцією. Після did — have, не had; replaced не змінюється. Didn't допустиме. Переклад зберігає саме замовлену послугу. |
| 5. У гості вкрали телефон під час вистави | Небажаний досвід; переклад «У гості вкрали телефон під час вистави». Не приписано організації, дозволу чи вини; виконавець невідомий. |
| 6. Ремонт сканера ще попереду | `I have booked my scanner in for repair on Thursday. It is expected to be ready on Friday.` Четвер — домовленість, п’ятниця — очікування. Going to have допустиме за збереження домовленості в контексті; минуле завершення чи definitely ready змінюють факти. |

### Impersonal C2 — [урок](http://gramlyze.loc/theory/passive-voice/complex-passive-impersonal-style)

| № / завдання | Перевірений ключ і критерії |
| --- | --- |
| 1. Куратор і реставратор карти | Curator повідомляє в середу; conservator реставрує карту в понеділок. Два by належать до різних рівнів; незалежної достовірності не додано. |
| 2. Техніки й датчики | `The technicians are reported to have calibrated the sensors yesterday.` / `The sensors are reported to have been calibrated by the technicians yesterday.` Обидві дії раніші, але active/passive обираються окремо за роллю підмета; виконавців збережено. |
| 3. Архівістка про опечатаний пакунок | `On Friday, the parcel was reported by the archivist to have been sealed on Thursday.` Was reported — п’ятниця, sealing — раніший четвер. Архівістку не названо виконавицею sealing; невідомого виконавця не вигадано. |
| 4. Місце not у відомості про файл | Not known: немає підтвердженої відомості про видалення. Known not: відомо, що не видалили. Незнання видалення не доводить збереження; обидва українські значення названі. |
| 5. Важкий continuous passive | `Today’s update reports that two decorators are repainting the hall now.` Також допустима passive that-clause з hall is being repainted by two decorators. Збережено update, two, виконавців і процес; has been repainted не підміняє його завершенням. |
| 6. Організаторка про друк 40 програм | `On Monday, the organiser reported that the printing company had printed forty programmes on Sunday. The number has not been independently checked, and there is no information about delivery.` Збережено Monday/Sunday, друкарню, forty, саме друк і межі відомого. «Не доставили» теж було б вигаданим твердженням. Інші точні редакції дозволені. |

## Guarded local apply

Використано незмінні LinkingWordsContentPatch/M11LocalTargetGuard через мінімальні M19 service/command/guard wrappers і worktree→working CLI-адаптер. Старі manifests/allowlists, формати plan/backup/restore та загальний механізм не змінено.

Guard підтвердив фізичний локальний MySQL і зв’язок із gramlyze.loc: loopback DNS, Windows PID/listener, Apache/vhost та nonce-bound web/CLI відповідність. Початкова sandbox-перевірка не змогла виконати Windows listener/vhost inspection; запису не було. Після вузького підвищення доступу guard успішно пройшов. Віддалені/forwarding/split-запобіжники не послаблювалися; APP_ENV лишився production.

Послідовність: fresh proof → свіжий preview → ручна перевірка точних IDs/полів → новий exclusive record-backup → транзакція/postcondition → повторний no-op.

- Початковий proof: 27 вересня 2026, 23:04:47 UTC; перший apply — `status=applied, updated=12`, повтор — `status=no-op, updated=0`.
- Перед commit staged diff --check виявив зайвий порожній рядок наприкінці нового before-manifest. Його байти входять до sources hash, тому просте форматування зробило б старий plan/restore несумісним. Навчальні definitions не змінювалися.
- За точних початкових source bytes і свіжого proof **23:22:48 UTC** виконано guarded record-restore: `status=restored, updated=12`. Початковий backup `records-before-final.json` і plan SHA `403670d64417140b4013b9eab11c0a52f2e9c494470b7aacb4a56b3146d0bb0e` збережені без перезапису.
- Після форматування manifest створено та перевірено новий preview: `m19-passive-reporting-causative-v1`, SHA-256 **`19e3cd9eb40dab3717d0f5c502aae971c6fd7642d2fdacaae06e4dc2758f759d`**. Pages, changes, before/after-значення й hashes definitions ідентичні першому plan; змінився тільки hash manifest.
- Фінальний apply: `status=applied, updated=12`, 28 вересня приблизно **02:24 Europe/Kyiv**. Наступне застосування — **`status=no-op, updated=0`**, зайвий backup не створився.
- Актуальний record-backup: **`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m19-local/records-before-formatted.json`**, 4 945 764 байти. Це приватна адресна резервна копія механізму, не повний дамп БД; до Git не включена. Початкова копія такого ж розміру теж збережена; для фінального source відповідає нова копія.
- Content corrections після apply не було. Реальний restore/reapply використано тільки для узгодження manifest formatting; відмови при конфліктах і штучний rollback тестувалися на ізольованих fixtures.

| Page ID | Subtitle / hero / box IDs |
| --- | --- |
| 296 | 9165 / 9166 / 9167 |
| 305 | 8802 / 8803 / 8804 |
| 319 | 8842 / 8846 / 8849 |

Оновлено лише pages.text і text_blocks.heading/body цих записів. IDs/UUIDs, seeder, slug, типи, order, locale, level, Page.title, категорія, tags/pivots та test relations збережено. Versioned before-manifest містить тільки старі публічні definitions, не приватну БД. HTTP/startup/hooks/scheduler apply не додавалися.

## Перевірки

### Автоматичні

- PassiveReportingCausativeContentPackageTest, PassiveReportingCausativeContentPatchTest, M11LocalTargetGuardTest: **42 тести, 730 assertions, exit 0**, включно з повторним фінальним прогоном після форматування manifest.
- Перевірено JSON/hero, 18 різних завдань/ключів, rich opt-in, controller-derived H1, title/meta/OG/Twitter, resolvers/href, immutable identities, точний full-identity allowlist і кореневу категорію, old→new/no-op, manual/stale conflict, exclusive backup, rollback, guarded restore та відмову непідтвердженій цілі.
- Окремий негативний кейс переміщає цільову сторінку та всі її блоки в іншу валідну українську theory-категорію: preview відмовляє без запису; незалежна цільова сторінка лишається придатною.
- PHP-тести виконано в ізольованому SQLite in-memory/runtime. Production-профіль metadata — тільки in-memory requests; production HTTP не було.
- Одна наявна PHP 8.5 PDO deprecation у конфігурації; не failure. Не ремонтувалася в M19.
- Node-контракти `seo-m19-local.test.cjs`: **8/8**. Pint — шість цільових PHP-файлів, passed.

### Реальні HTTP та метадані

Before capture: 27 вересня, 22:52:52 UTC; перший after: 23:10:07 UTC; фінальний після reapply: **23:25:18 UTC**. **18/18 HTTP 200**: три теорії, три основні тести, курсова копія, два контролі, вісім суміжних сторінок і sitemap. Обидва after порівняні з before. Запити без авторизації; .com у canonical/sitemap аналізувався лише як рядок.

Початковий server HTML кожної цілі має 6 завдань, 6 ключів, **8 rich-секцій**, відповідно 18/31/20 блоків прикладів. Немає службового anchor-box, plain fallback, видимих сирих HTML-тегів або технічних маркерів. Основні test href збережені; нових дубльованих кнопок тесту не додано.

Після reapply текстові hashes всіх theory-сторінок і serverContentSha256 шести браузерних сценаріїв ідентичні першому прийманню. Додаткова спроба порівняти також увесь текст Mixed HTML дала відмінності на трьох тестах: ці динамічні відповіді не є незмінним контентним snapshot. Незмінність банків підтверджено окремими DB fingerprints, а HTTP-приймання тестів перевіряє status, metadata та штатний перехід, не побайтний текст кожної сесії.

Page.title, фактичні H1, HTML title, canonical, robots і X-Robots-Tag не змінилися. На локальній теорії X-Robots-Tag — `noindex, nofollow, noarchive`, meta robots відсутній як у before. Змінені лише три дозволені descriptions через новий український hero intro. Meta/OG/Twitter узгоджені; чинний PageMetadata додає назву один раз, intro її повторно не містить. Описи тестів, курсу й сусідів не змінилися.

Ordered sitemap: спостережено **554 URL**; той самий порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Кількість не hardcode. Контент read-only сусідів, їхні metadata та links unchanged.

### Браузер і screenshots

Chromium **147.0.7727.15**. **6/6 основних сценаріїв**, повторно пройдені після фінального reapply: три цілі × desktop 1440×1000/mobile 390×844, кожна у світлій і темній темі. Нові contexts, звичайний reload, server/DOM content hash, матеріал нижче першого екрана, нумерація, details мишею та Enter/Space — passed. Три штатні переходи до основних тестів виконані; відповідей не надсилали.

Контролі M18 Argumentation і суміжний M17 Hedging: **4/4 сценарії** у двох темах, без регресії. Новий контент отриманий зі справжнього локального сервера: без page.setContent, route.fulfill, innerHTML чи fixture-підстановки.

Оформлення — незмінний TheoryRichContent та чинні картки. Renderer, Blade, глобальні CSS/JS не змінено; generated wrappers у БД не зберігаються. Таблиці 1000 px у mobile-контейнері 322 px, фактичний scrollLeft **678 px**; перша колонка мінімум 250 px, інші мають окремі content-specific ширини. Горизонтальна прокрутка локальна, document overflow немає. Вибіркові контрасти текстових елементів пройшли перевірку в обох темах; це не повний accessibility-аудит.

Переглянуті screenshots вступних карток, таблиць зліва/справа, довгих формул, перекладів, відкритих ключів, контрольних сторінок і курсу. Додатково створено 12 detail-screenshots двох секцій кожного уроку на desktop/mobile. Широкі таблиці не стискають слова у вертикальні фрагменти; довгі формули й пояснення переносяться всередині карток.

Фактичні обмеження цього прогону:

- Application pageerror — **0**. Google Fonts CSS повернув `net::ERR_NETWORK_ACCESS_DENIED`; браузерні скриншоти показують доступні fallback-шрифти, а не підтверджену фінальну Google Fonts typography. Це умова саме M19, не перенесене припущення з M18.
- Три автоматичні test-state POST у головному runner і два в контрольному навмисно заблоковано як stateful-request. Їхні ERR_FAILED/console errors не зараховано як application failure. Інших failed requests, крім цих і Google Fonts, у звітах немає. Прогрес не записувався.
- Курсова копія: HTTP 200, правильний H1, штатний екран «Урок заблоковано» для нового гостя. **Gate не обходився.** Server HTML містить 6 завдань/ключів; спільний resolver/blocks/render перевірений ізольовано. Відкрите читання захищеного контенту курсу для гостя live не приймалося.

### Захищений стан і cleanup

Read-only fingerprints **46 таблиць** before/after/commit-ready/final accepted збігаються після виключення лише дозволених полів 12 записів. **24 прийняті уроки M11–M18** збігаються з definitions. Questions/answers/options, verb_hint, links/relations, progress та всі інші захищені дані незмінні. .env hash незмінний; APP_ENV=production, APP_KEY і підключення не редагувалися.

Тимчасовий proof route прибрано; реальний GET повернув **404**. routes/api.php має початковий SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522`. Приватні inventory/proof/plan/record-backup/HTTP/browser/screenshots збережено в `storage/app/seo-m19-local`, поза Git.

## Git і межі завершення

До пакета включено тільки три definitions, вузький before-manifest, service/command/guard/CLI adapter, два PHP-тести, Node profile/test і цей звіт. .env, vendor/build, runtime caches, приватні proofs/backups та сторонні зміни виключені. Перевірка staged diff, secret-pattern scan і git diff --check виконуються перед commit; повний SHA та підтвердження звичайного push наведені у фінальній відповіді.

Гілка: `codex/seo-m19-passive-reporting-causative-content`. Без force push, main push, PR або деплою. Повний suite, M10, crawl, Lighthouse та performance-кампанія не запускалися. Build inputs не змінені — rebuild не потрібний. Mixed-питання не додавалися й не редагувалися. Наступний пакет не розпочато.

**Production не перевірявся й не змінювався.**
