# M63 Relative completion and Linking Words

Авторський пакет 1.0.0 для трьох UK сторінок підготовлено й погоджено. У ньому 24 native blocks, три змістовні point-level «Докладніше», 18 нових власних завдань та окремі п’ятірки з 15 вже наявних тематичних питань. Дизайн сторінок і grader не переробляються.

Локальне впровадження M63 прийнято. Apply, HTTP/SSR, браузерне приймання трьох сторінок, повне AFTER порівняння old consumers, два no-op, nonce404 cleanup, ізольована регресія, технічна source finalization та повтор тестів на фінальних bytes завершені. Новий контент застосований до реальної локальної БД й перевірений у браузері, не лише у fixture. Зміни підготовлені до scoped commit і normal push робочої гілки; факт Git-доставки ще не заявляється.

## Сторінки та межі

| Сторінка | Actual Page / category | OLD → actual AFTER blocks | Technical / display level |
| --- | --- | --- | --- |
| [Non-defining Relative Clauses](http://gramlyze.loc/theory/clauses-and-linking-words/non-defining-relative-clauses) |33 / 34, clauses-and-linking-words|7 →8|B1 /B1-B2|
| [Omitting Relative Pronouns](http://gramlyze.loc/theory/clauses-and-linking-words/omitting-relative-pronouns) |34 / 34, clauses-and-linking-words|7 →8|B1 /B1-B2|
| [Linking Words](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words) |32 / 34, clauses-and-linking-words|7 →8|B1 /B1-B2|

Exact owner identities у цьому порядку:

- Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder.
- Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder.
- Database\Seeders\Page_V3\ClausesAndLinkingWords\ClausesAndLinkingWordsLinkingWordsTheorySeeder.

Accepted base: c8502bc75b9d4258d79824a9be0b343ffbfc07f4, завершений M62. Робоча гілка: codex/seo-m63-relative-completion-linking-standard-view. Source WT — C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc; served ROOT — D:/DEV/htdocs/gramlyze.loc, document root — його public directory.

Scope — лише ці три UK Page owners. Category34 зберігає actual DB назву Clauses and linking words та чинну українську source/localization назву. Категорію й її learning descriptions не перейменовують. Окреме дерево relative-clauses з Complex Relative Clauses не підставляється замість ancestry. EN/PL, старі банки, курси, окремі тести, питання, links і особистий прогрес не є цілями запису. Main, PR, merge/rebase, force push, workflows і production deploy поза scope; HTTP/browser — тільки gramlyze.loc.

## Незалежний BEFORE

Fresh independent source/DB/HTTP BEFORE SHA256: daf7c1306cb8e292c5ccaa98aade4d611038509495871eb426a79be6fd67c585. Заново встановлено actual Page33/34/32, category34, по сім continuing slots, title/H1/subtitle, metadata, anchors, початкові питання й фізичні relations. Три theory та три відповідні course GET дали HTTP200. Контрольні fingerprints46 SQL tables до й після BEFORE HTTP збігаються. Зафіксовано 326 visual files,2403 попередні frozen files та 4 physical identity/config hashes.

Окремий old-consumer BEFORE має SHA 727adc6ccdedbcf6947b8e25ad59464316934ce0f73b7239c84adbbf1b85ceba; повне AFTER порівняння 62 маршрутів завершено й наведено нижче. Старий sentence-builder registry прочитано окремо:109 targets,0 failed,0 writes; receipt SHA e3309c0c7722b2512266fd31a2cefb9f4f80e65b19fc836a318b8ad8541a5ad9. Actual AFTER registry має 112 targets,0 failed,0 writes, SHA 55c9f3c25b7ab4053c9c5834537230e30de26d5659ac6e9373be8b5d926a479a. Окремий stable comparison усіх 109 старих identities дав порожній список differences; порівняно всі row fields, крім нестабільного anonymous raw HTTP SHA. Comparison SHA 36b4eb7601eea8615601e5230b77c02aa10676206850539a13ca25b767cdde4c. Registry comparison і ширший old-consumer audit є різними завершеними перевірками.

Свіжа browser BEFORE matrix охоплює 18 станів: три сторінки ×1440/390/320 ×light/dark. На кожній початковій сторінці було 0 власних practice groups і 0 embedded widgets. Main content мав однакові client/scroll widths; document/shell scrollWidth коливався й на mobile перевищував viewport. Omitting мав локально scrollable таблицю. Це зафіксований baseline, не твердження про нульовий overflow у всіх станах. BEFORE receipt SHA b40d5a93e0affc629f74c75ff62a25ae10327b8480d15f04f4aa0e21abe8f2b9; фактичний AFTER наведено окремо нижче.

## Погоджений native component map

Погоджений v2 SHA ddb4201fc611617f10965715c094f9a63eae1b654189946cae8431e6b7702054 заморожено вперше як master1.0.0. Final master відрізняється лише freeze/review metadata; навчальних змін після approval немає. DRAFT залишалися поза active release discovery.

Pure author projection SHA 1ce1eb9a4b81990671691322815228b99484668a8ecf821f80bfaf764bd8b384 сформовано до apply, не з AFTER GET. Вона містить 24 blocks:8/8/8. Найбільший UTF-8 body17 659 bytes за фактично перевіреної SQL TEXT capacity65 535 bytes.

На кожній сторінці застосовано той самий ряд компонентів:

hero → usage-panels → forms-grid → comparison-table → mistakes-grid → summary-list → navigation-chips → practice-set.

Індекси нижче — 0-based source slots. Рівно 15 box type/heading transitions заявлено явно; level лишається null, columns не переносяться:

| Owner / slot | OLD type / heading | AFTER type / heading | Незмінні column / level |
| --- | --- | --- | --- |
|Non-defining /1|box /1. Коли використовуємо non-defining clause|usage-panels /null|left /null|
|Non-defining /2|box /2. Типова форма|forms-grid /null|left /null|
|Non-defining /3|box /3. Як читати коми|comparison-table /null|right /null|
|Non-defining /4|box /4. Типові помилки|mistakes-grid /null|right /null|
|Non-defining /5|box /5. Коротке правило|summary-list /null|right /null|
|Omitting /1|box /1. Коли omission можливий|usage-panels /null|left /null|
|Omitting /2|box /2. Коли omission неможливий|forms-grid /null|left /null|
|Omitting /3|box /3. Порівняй приклади|comparison-table /null|right /null|
|Omitting /4|box /4. Типові помилки|mistakes-grid /null|right /null|
|Omitting /5|box /5. Коротке правило|summary-list /null|right /null|
|Linking /1|box /1. Основні сімейства linking words|usage-panels /null|left /null|
|Linking /2|box /2. Позиція в реченні|forms-grid /null|left /null|
|Linking /3|box /3. Пунктуація і стиль|comparison-table /null|right /null|
|Linking /4|box /4. Типові помилки|mistakes-grid /null|right /null|
|Linking /5|box /5. Коротке правило|summary-list /null|right /null|

Усі 21 continuing native slots мають збережені IDs/UUID/order/anchors/columns/levels. Додано лише три practice-set/left/B1, кожний source index7/sort8: actual DB IDs 12735 для Non-defining, 12736 для Omitting і 12737 для Linking. Hero лишаються header/B1 з displayB1-B2; navigation — footer/null. Educational navigation labels/hrefs/order/current flags збережені. Serializer додає m63_native_v1, тому raw navigation JSON body не оголошується byte-identical.

Детерміновані expected practice UUID:

- Non-defining: e1a87d22-cb15-5090-8dc9-3ad6cea4add0.
- Omitting:37d9cb29-e766-5200-b1c0-89ce196ae59e.
- Linking:376d66cd-6d77-5853-9cd6-89975cdd03d6.

Повний component map містить exact body SHA, UUID, byte count, metadata transition та point-detail owner кожного з 24 blocks; він є окремим versioned artifact, не новим runtime renderer.

## Редакційний зміст і basic detail

Non-defining пояснює додану інформацію про вже визначений у конкретній розмові об’єкт. Додаткова не означає неважлива. No-that і no-zero сформульовані прямо, зокрема для object who/which перед іншим підметом. Who/which/whose/where/when та компактне formal whom вибираються за роллю; where не підставляється після будь-якої назви місця. Whose не обмежено людьми. Потрібні I/you/Oleh збережені; зайвий повтор тієї самої ролі відрізнено від потрібного іншого підмета.

Вставлений опис має дві коми, фінальний — одну початкову кому й завершальний знак. Кількість родичів не виводиться з самих ком; власна назва без контексту не оголошена автоматично достатньою. Старий повний meeting-приклад збережено й перекладено як опис іменника meeting, не цілої ситуації. Окремий whole-message which має власний прозорий контекст.

Omitting дає алгоритм «тип опису → роль → прийменник → повне речення». Проста defining object-модель допускає пропуск, але не вимагає його завжди. Subject/non-defining/whose та preposition boundaries лишаються видимими; «далі інший підмет» не є універсальним тестом. Старий teacher fragment розмежовано з повним реченням: самостійне коротке повідомлення про зрозуміле пояснення граматичне, але не зберігає головного факту про відпустку. Головне питання не змінює порядку у relative clause. Object omission не ототожнено з participle reduction.

Linking — компактний overview чотирьох поширених зв’язків, не повний каталог чи дубль M27. Причина й наслідок мають явний напрямок, mere chronology не доводить причинності. Clause, infinitive та linking adjunct відрізняються будовою. However/therefore мають коректні period/semicolon/internal models; формальність не оголошена універсально кращою. Because на початку повного речення дозволено; fragment відділено від короткої розмовної відповіді. Because…so/although…but не дублюють той самий зв’язок у заданій простій моделі, але це не глобальний blacklist двох слів. Purpose має потрібного виконавця й не доводить досягнення.

| Meaningful detail | Точний owner |
| --- | --- |
|nondefining-whole-message|nondefining-reference /sections/1|
|omitting-versus-reduction|omitting-boundaries /items/0|
|linking-since-as-context|linking-functions /sections/0|

Основні правила, всі потрібні переклади та застереження видимі в basic. Три details залишено за змістом, без word quota чи глобального runtime threshold. Для всіх трьох у реальному браузері перевірено початковий закритий стан, Enter-open, Space-close, правильний deep link і відкриття після reload із повним читанням тексту.

## Власні завдання і тематичний конструктор

У кожного уроку 2 select +2 choice +2 manual/token inputs: разом 18 нових власних завдань,36 option values і 6 inputs. Ручний і токенний шляхи не подвоюють кількість завдань. Шість канонічних input answers плюс did not/didn’t для намету та to/in order to для мети Данила дають 8 finite accepted values; непогоджених комбінацій aliases немає.

Plain-text feedback має 52 унікальні пояснення з повними EN/UK реченнями й конкретною причиною. HTML/entities не передаються в x-text; x-html, runtime stripping або grader changes не додавалися. Шість meaning choices відкривають переклад після Check, щоб не розкрити відповідь; контекст і критерій українською видно одразу. Для п’яти повних grammatical relative-форм у двох Omitting inputs є 10 period/no-period rejected keys: вони граматичні, але не виконують явну умову zero-relative task.

Окремий «Вправа 4. Побудуй речення» використовує лише 5 питань своєї теми на кожній сторінці. Це 15 reused existing type4/B1 questions, не ці 18 власних завдань і не посилання на окремий тест.

| Owner | Actual selected existing question IDs | Exact bank |
| --- | --- | --- |
|Non-defining|30976,30978,30979,30980,30981|PolyglotNonDefiningRelativeClausesAllLevelsLessonSeeder|
|Omitting|31047,31049,31051,31055,31057|PolyglotOmittingRelativePronounsAllLevelsLessonSeeder|
|Linking|30328,30329,30330,30331,30335|PolyglotLinkingWordsAllLevelsLessonSeeder|

Банки прочитані за fresh actual snapshots:132 type4/practice-level candidates, із них 36 точних contract-pool candidates. Перевірено 1193 ordered answer instances,1963 фізичні option/pivot instances,453 unique shared options,96 legacy hint rows і 12 variant rows. Обрані 15 містять 130 correct-token instances і 41 чинний theory link. Це relation/multiplicity review, не твердження, що всі невибрані історичні питання граматично якісні.

Нових question/options/answer/link rows немає. Render-only15 token і 15 corresponding option presentation instances відновлюють погоджену пунктуацію/регістр для конкретних snapshots;13 пунктуаційних і 2 case corrections — не глобальний lowercase і не SQL cleanup. Visible UK condition уточнює референта, роль чи зв’язок, не змінюючи старі rows. Зокрема Одеса має видиме узгодження «прекрасна», а original historical wording не переписується. Final helper/audit/test опубліковані exact у WT і ROOT; mutation та ізольовані matcher/renderer tests пройдені. Кожний реальний widget пройдено від свіжого П1/5 до 5/5; окремо перевірено restored5/5 після reload.

## Пунктуаційні межі оцінювання

Native practice normalize ігнорує punctuation не лише в inputs, а й у select/choice values. Linked widget має окремий незмінний factory з таким самим punctuation/case-insensitive принципом. Правильний видимий comma/semicolon display не є доказом punctuation-skill grading.

Тому дві пунктуаційні ситуації мають A/B English stimuli та різні українські semantic answer values. Англійські варіанти, що різняться лише комами, не використовуються як raw candidate keys. Inputs перевіряють слова, порядок і ролі, не comma-only accuracy. Невідомий довільний typo/partial зберігає чинний generic fallback; переклад для будь-якого можливого typo не обіцяється.

Independent widget punctuation proof SHA 870841faffb86b74242858098dd702e8e3f0aa3a0774c04ec981f187f01d02d3 підтверджує фактичну shipped normalization лише в ізольованій пам’яті. Це не live interaction receipt.

## Незмінний дизайн і scoped PHP інтеграція

Не додаються незалежні m63-section/detail/styles, Blade layout, CSS, frontend JS, шрифти, кольори, рамки чи відступи. Усі навчальні дані проходять чинну theory.show → content-block → TheoryContentSource → TheoryContentRenderer → theory.content → shared components межу.

«Без зміни renderer» у component artifact означає без редизайну visual implementation, не відсутність будь-якої PHP diff. У 8 shared PHP files інтеграція додає 12 exact hunks /15 lines /0 removals: canonical registration, point/correction dispatch, course/standalone BEFORE preservation та чинні bank seams. TheoryContentRenderer отримує одну scoped bindings-line; executable view не обирається авторським payload. Native serializer, planner і M26 transaction writer не копіюються в новий framework.

Actual ROOT містить попередні сторонні PPC/widget presentation changes, які не перекрито WT-файлом. Original application source-sync-v1 receipt охоплює 31 path, SHA c1147f88e4b72a1fab7d8280570ea130c6b81536547e0a76ac57b102510a31e3. Цей inventory включає перевірені незмінні primitives та банки, а не 31 змінений файл. Окрема незалежна перевірка 25 shared paths на кожному боці дала 50 exact reverse-hunk checks; SHA 076d526d6467ea29bf2424e16abf0b28ec2742c90c8947afbc7a921ed3b6fd4d. Вона підтвердила 8 змінених shared paths із 15 доданими рядками, незмінні 326 visual та 2403 frozen files і збереження сторонніх ROOT bytes. Пізнішу обмежену finalization трьох нових technical files наведено окремо, не підставлено заднім числом у v1 receipt.

ROOT TestJsV2Controller після scoped M63 hunks має SHA c460af3c209eb0477d6f06293de3a468fd82f7e0ebbe91a981719d6580cb2175; WT — da1ce1585a3fa68af4aad6927d1cdcb539c65576504e6b762ced06c304e96391. Whole-file parity навмисно не заявляється: reverse checks відновлюють окремий exact BEFORE кожного боку. ROOT widget має SHA 94fc2ecd7525cacfac652cc39aebf3ac057d8f845daf1425fe8cd845c9c0dd80, WT widget — 5daee32edd5dc4b8c6d06f9eb17b56fcd7332eac15d132e2f0c5eab671925e0b; обидва лишилися своїми BEFORE bytes. Стороння відсутність ROOT .gitattributes та три попередні Discourse definitions також збережені.

## Локальне застосування і SQL postconditions

MAIN переглянув усі 30 before-to-author changes: 27 updates, 3 inserts, 0 deletes; зокрема всі 15 явних type/heading transitions. Updates охоплюють 24 continuing/subtitle rows і три Page.text поля; EN/PL, старі level/column та question/answer/option/pivot/link rows у план не входять. Internal plan digest — 40dd2fa634a3e889e0a30e5357fb3ea9de170f51b238bb15151a14dd017411ee; raw preview SHA — 1872aab657bd4e2892fb3f6d33d8a1a67387bad71c4672c3d2f45142adf4004a.

Перед записом створено exclusive backup D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m63-local/backup-v1.json із тим самим raw SHA. Transactional local apply завершився фактичними 27 updates /3 inserts /0 deletes. Backup залишається приватним і не комітиться.

Незалежний final-readonly-v1.json має SHA a220319581f5d8d146d76cf5ec099283d9fa6ff2df4cfcbb049a74ad010111ae. Він реконструює BEFORE на 46 SQL tables і підтверджує exact старі rows та timestamps, 24 continuing UK rows, незмінні 48 EN/PL rows і лише три нові practice blocks 12735–12737. New question families — порожній список; protected SQL projection, 326 visual files, 2403 frozen files і source-sync bytes незмінні. Сам audit виконав 0 DB writes і 0 HTTP requests.

Той самий повний SQL/source audit повторено після реальних UI interaction: final-readonly-v2.json SHA c833c2ebeb8ba77b901b86a1b1abb263417f545f86ebb33427dbd7f0cd2ca7c9. Усі 46 tables, старі rows/timestamps, 24 UK/48 ENPL, frozen/visual inventories і original source-sync preservation знову підтверджено; new question families лишаються порожніми. Browser actions не змінили захищені question families чи прогрес у робочій SQL БД. Цей receipt прив’язаний до прийнятих original raw sources до фінального EOF/private-filename оформлення; він не називається новим запуском generic postconditions wrapper проти пізніших bytes.

Перший повторний запуск зі старим планом дав actual NOOP 0/0/0. Fresh AFTER preview має state after, 0 updates /0 inserts /0 deletes та digest 5b0bc0c306a5c9fb955449dce22398e2d0d0f1f91dcbbaa0cbf78eeb23860849. Другий actual apply зі свіжим AFTER планом теж дав NOOP 0/0/0. Жоден із двох no-op не створив нового backup file; preview не підміняє ці фактичні запуски.

Fresh web/CLI local proof має SHA 618fb2b2b3f1899596cb70f2e60152e216d4aea6bcbd8e997b74d97a713ae436. Перевірено саме D:/DEV/htdocs/gramlyze.loc та його public document root, loopback127.0.0.1, MySQL localhost:3306/gr2, APP environment production із SiteMode development. Назва APP environment не означає доступ до production: фізична ціль і HTTP host — лише gramlyze.loc.

Тимчасовий nonce route видалено з routes/api.php; exact foreign-inclusive bytes відновлено до SHA 2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689. Окремий loopback GET після видалення повернув 404 без redirect; physical hashes незмінні. Cleanup receipt cleanup-896193b76ea7c71626a0ae7501163dce.json має SHA b0405f237d1763568a7c923d9c6549c7ef9660b7ff95de83b017637853d6707f. routes/web.php не змінювали.

## Фіналізація технічних sources

Після приймання виконано тільки три явні технічні зміни, exact однакові в шести ROOT/WT копіях:

| Файл | Дозволена finalization | Final raw SHA256 |
| --- | --- | --- |
|app/Support/M63BuilderSelectionPresentation.php|Прибрано рівно 2 зайві terminal LF bytes; решта bytes незмінна|21c89da48c3ee78b4f35b3e13dc9e83e2c416c53002f26c427de5c4fdb6aab6e|
|tests/Feature/M63BuilderSelectionPresentationTest.php|Прибрано рівно 1 зайвий terminal LF byte; решта bytes незмінна|be597f4dc1062463247d007dae5e9a2a2f5e7954bdf920a32f0cb1e3826cf799|
|app/Services/M63ContentPatch.php|Один private SYNC_RECEIPT basename source-sync-v1.json → source-sync-v2.json; решта bytes незмінна|06ab119dfedf5028e2bf59fcae38f8c07df65fcf394b8e8ae685a4a6b4e6e19b|

Окремий final-source-lineage-v1.json SHA 6e4d8778acb25e146d1da61fa5c5f960db221565909efc0d63915aa3fb026d7f відновлює exact accepted preimages усіх трьох файлів на обох боках:6 raw checks. Original preview, source-sync-v1 і обидва SQL postcondition receipts незмінні; усі 11 frozen teaching artifacts залишилися exact за manifest8a2c9cfb…95d. Нового навчального редагування, reapply чи SQL записів для цієї finalization не було.

Final source-sync-v2.json має SHA 2207dd087d477446c2b76a3210015343c65c0a74d87aa889892cc8e549a77577:31 paths із final source bytes,326 visual і2403 frozen files незмінні. Lineage та v2 filesystem proof — окремі докази від original SQL/HTTP/browser acceptance; original receipts не переписані й не видаються за перевірку нових raw bytes. Повтор 9-file SQLite suite на цих final sources завершився56/56; точний receipt наведено нижче.

## Виконані ізольовані перевірки

Фінальний frozen master пройшов 23/23 unique offline JS cases на accepted WT profile та повторний final frozen ROOT run 23/23. Попередній served-local run проти схваленого v2 теж був успішним; фінальне твердження вже не спирається лише на draft. Це 23 unique cases, а не 46 чи 69 різних tests. Покриття:18 tasks,36 options,6 manual і 6 token paths,8 accepted input values,10 grammatical-but-instruction rejections,6 deferred translations; empty/partial/wrong/repeated wrong/retry/correct/recheck/reset, token case/multiplicity/delete-return-rebuild/mixed edits, незалежні scores. Network0, без Laravel/bootstrap/working .env/DB/HTTP.

Exact extracted Alpine factory SHA ROOT і WT:43b4bd3e266594ce7b6ffe697e979ba64c3a824cfb816af06fd8beef31847f8b. Це не whole-view SHA. WT whole view ad7dd5027fb0202cf595ecc4d358e7329bcf7307ad52d93179557448171fb666; ROOT whole view 05103e6d6261298314508394447c00f133e92c100b03842c1527700920e7bd2e. Різниця — pre-existing PHP presentation hunks; саме extracted factory однакове. Answer variants/contractions/loader мають лише LF/CRLF різницю й pin-яться окремими actual profiles.

Незалежний read-only code review охопив package, writer/guard/CLI,8 shared seams,9 нових Feature files та diagnostics, включно з опублікованим final helper/test. Конкретного implementation defect у прочитаному не знайдено. Перевірено caller/locale/owner/body gates, WeakMap-only selection exclusions, all-five stale fallback, clone-only labels, exact pivots/hints і multiplicity. Це code review, не окреме browser acceptance.

Початковий suite з 9 M63 Feature files на прийнятих pre-finalization sources завершився exit0:56 tests,11 406 assertions,1 deprecation; receipt SHA 6198ade5a9dd5719c8cfdd3b22a2f8a8c972739b4d06a44e1c7773947fe29b61. Він не додає ще56 unique tests до наступного повтору.

Фінальний current-source suite повторив ті самі56 tests після технічної finalization:56/56,11 406 assertions,1 deprecation, PHPUnitchild2:28.783, exit0 і runner0. Receipt storage/app/seo-m2-local/m63-new-native-v2-f670f0b4078b46caa54cff7497ff2965-result.json має SHA 73623665e1226b0d63dfec7bf685ffaf75e1e072389595df7b461bea6ac22143. Isolated runtime — testing, SQLite :memory:, array cache/session, окремі storage/views, working_env_loaded:false. У fingerprint inventory46 661 actual protected files із46 662 enumerated paths, changes0; before/after digest однаковий e1ff9fe48ab06d5f3ca47e8478bfe95ff3778dd459be19248b59beacf6383933. Deprecation не прихована й не названа failure; assertions не послаблені.

Попередній regression v1 був передчасним і не є PASS: 439 tests, 39 class-missing errors та 2 HTTP500 failures. Публікація великого audit/helper payload зависла на 802 секунди, тому запуск отримав відсутній class; після завершення публікації окремого implementation fix чи послаблення assertions не робили. Результати v1 не замовчуються й не входять до passing union.

Фінальний regression v2 з82 files завершився exit0:439 tests,81 939 assertions,1 deprecation, child runtime14m54.406s,0 protected-file changes. Receipt storage/app/seo-m2-local/m63-accepted-regressions-v2-c2cd3c737a79448d89a468e743b4292a-result.json має SHA 3cdebae25a14d7ee1e57bf3c605f1165f8ccf76f6f84303536f3e448b8bc7969. Він виконаний до трьох обмежених technical changes; exact lineage доводить їхній несемантичний характер. MAIN звірив JUnit manifests: overlap між439 regression і56 final-current-source M63 tests —0; current deduplicated PHP union —495 tests,0 failures,93 345 assertions за двома запусками. Це не один full495 rerun і не весь repository suite; попередній56-test run не подвоюється. У кожному з двох урахованих запусків є1 deprecation;6 historical literal tests залишаються окремо.

## Реальні HTTP і браузерне приймання

Anonymous loopback GET для всіх трьох exact URLs дав HTTP200 без redirects, cookies чи proxy. Live SSR receipt live-pages-v1.json SHA ee0ee07fa45c3f407fa3921f0c35e0d112482675148bb1eaa073b8ca29082eb0 підтвердив 406 basic/detail learning fields:129/130/147, одну H1 і один правильний тематичний widget на кожній сторінці, по 3 groups із двома tasks, по одному закритому detail та всі 19 старих anchors кожної сторінки. Перевірені title, breadcrumb, description, local robots noindex/nofollow/noarchive та stable LearningResource identity. Canonical .com є лише прочитаним metadata value; запитів до production не було.

Scoped sentence-builder acceptance має 3 targets,0 failed,0 writes; receipt SHA a8330a6647e4e76e40ac966437dcc102e6a6b4cceb52cc69536f47093cf62eae. Для actual practice IDs12735–12737 підтверджено exact thematic source, type4/B1,12 eligible/5 selected, ordered tokens і один widget. Це CLI/HTTP proof; клієнтську взаємодію підтверджено окремим browser receipt.

Повний fresh AFTER old-consumer audit повторно перевірив усі 62 маршрути: кожний дав direct HTTP200 і exact порівняння з незалежним BEFORE. Перевірено 8 saved tests і 528 старих questions, course/standalone consumers та сусідні theory routes; усі 46 SQL tables незмінні до й після HTTP,0 DB writes. Receipt after-old-consumers-v1.json має SHA 92d6bf1f8e0561a0f90ffa787c03b134895839ff7c4b8a830397b280fab6eba7.

Перший AFTER run не називається PASS: після42 успішно порівняних маршрутів він зупинився на43-му, category timeout. Попри outer process exit0, exception і відсутність success receipt означають FAIL CLOSED. Окремий повторний GET категорії повернув HTTP200 за1.690s, після чого весь62-route audit заново пройдено з тими самими guards, timeouts, scope і sources. Успіх single-category retry не підміняє повний audit, частковий prefix не повторно використано як acceptance. Attempts receipt old-consumer-attempts-v1.json має SHA 82431ab10d8851ad85b45fff392079dd917827f59664e4bf0a827f38a142b944. Разом із трьома target GET перевірено всі9 navigation destinations: три target pages і шість сусідніх relative/defining/reason/result/contrast/purpose pages, а також фактичні нові contextual hrefs.

Browser acceptance виконано actual CUA controls із read-only DOM observations: без application-state injection, іншого browser driver чи очищення personal storage. Повністю прочитаний receipt browser-acceptance-v1.json має SHA b929f233b0b1f252e266f27e59cac1c9710edc1ba8bea808df2aa507c56ed105.

У 18 станах три сторінки ×1440/390/320 ×light/dark мають 3 groups,1 widget, початково закритий detail, доступний footer, computed H1 Archivo і body Manrope. AFTER main client/scroll widths —892/892 на desktop,358/358 при390 і288/288 при320. Таблиці мають local overflow-x:auto: desktop840/840, mobile322/576 чи252/576. Whole-document scrollWidth наведено окремо; нульовий shell overflow або його універсальне успадкування не заявляється.

| Сторінка / viewport | Document BEFORE light / dark | Document AFTER light / dark |
| --- | --- | --- |
|Non-defining /1440|1425 /1425|1440 /1440|
|Non-defining /390|1396 /403|404 /393|
|Non-defining /320|401 /403|331 /320|
|Omitting /1440|1425 /1425|1440 /1440|
|Omitting /390|1381 /1380|394 /395|
|Omitting /320|394 /395|328 /323|
|Linking /1440|1425 /1425|1440 /1440|
|Linking /390|1389 /1374|399 /390|
|Linking /320|330 /392|326 /327|

Усі 18 власних tasks пройдено; на кожній сторінці всі три group scores становили2/2. Прочитано фактичний full-text bilingual feedback усіх 36 options і 6 finite wrong inputs, canonical answers та declared aliases: без literal span/br або double escaping. Перевірено 24 input task/mode/size cases: manual і tokens на1440/390 для обох inputs трьох сторінок; усі 8 accepted values прийняті. Covered states: empty, partial, finite/repeated wrong, edit-correct, recheck без подвійного score, reset зі збереженням інших group scores, token delete/return/rebuild та mixed edits. П’ять full-relative форм у Omitting живцем відхилені через явну умову omission, не помилково названі граматично неправильними.

Actual normalization probe підтвердив межу: unlisted uppercase/comma-free правильна послідовність отримала generic «Правильно», а reviewed curly-apostrophe alias — повний authored EN/UK/reason feedback. Пунктуаційні A/B tasks прийняли правильний український semantic value й відхилили решту; answer-revealing UK translation була відсутня до Check. Глобальний grader не змінено.

Фактичний клієнтський shuffle у widgets збережено:

- Non-defining:30978 →30979 →30976 →30980 →30981, усі5 accepted.
- Omitting:31057 →31047 →31055 →31049 →31051, усі5 accepted.
- Linking:30330 →30335 →30331 →30328 →30329, усі5 accepted.

Кожний цикл почато зі fresh П1/5, слова вибиралися тільки з enabled BANK buttons, а повторювані tokens враховано. Final П5/5 і restored5/5 після reload перевірено окремо. Усі три details пройшли Enter/Space/deep-link/reload та повне читання. Збережено18 full-page screenshots; MAIN переглянув три actual detail viewport PNG і final widget5/5 screenshot. Дрібні автоматично зменшені320 full-page thumbnails не називаються детальним pixel-readability audit.

Приватний screenshot proof: [завершений widget5/5](D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m63-local/m63-widget-complete.png). Решта BEFORE/AFTER viewport PNG зберігаються в тому самому private directory та не комітяться.

CSS catalog-public-Cy8aaDRf.css123 022 bytes і JS catalog-public-CgUm4gux.js13 486 bytes отримані за actual DOM URLs з HTTP200; console errors0. Неправильне припущення harness щодо CSS wrapper input feedback та вузький classifier «книга/книжка» виправлено лише в процедурі спостереження, без application edits. Наприкінці відновлено viewport і light theme; personal storage не очищено. No-JS, print, full WCAG та network completion зовнішніх шрифтів не перевірялися.

## Відомі historical literal UI failures

Окремо існують 4 відомі baseline failures у Tests\Unit\TheoryPracticeSetTokenBankUiTest, задокументовані до M63:

- test_manual_practice_inputs_suppress_native_browser_suggestions — старий literal input locator.
- test_token_bank_input_keeps_manual_sync_without_registering_word_suggestion_handlers — той самий historical locator.
- test_token_bank_source_is_rendered_as_buttons_without_the_plain_slash_text — старий span-regex не відповідає чинному presentation attribute.
- test_separate_choice_exercise_has_its_own_answers_checking_and_reset_state — старе literal foreach очікування.

Свіжий окремий M63 baseline run завершився: 6 tests, 23 assertions, 2 PASS і ті самі 4 known FAIL, exit 1. Receipt: `storage/app/seo-m2-local/m63-literal-ui-before-v1-19c51f9d2cec47fca79f5ecf83b3ddab-result.json`, SHA-256 `d1a64d5ffc709e89000a49ee14f0e32f463fdaf65874cb9d0cc935b0fb9e67c8`; protected files не змінені. Усі 6 historical tests наведені окремо від passing regression union; assertions не послаблюються й не виправляються редизайном. Старі M62 run totals не видаються за результати M63.

## Джерела

Незалежно прочитані основні editorial bodies [British Council Non-defining](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-non-defining-relative-clauses), [British Council Defining](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/relative-clauses-defining-relative-clauses), [Purdue independent and dependent clauses](https://owl.purdue.edu/owl/general_writing/punctuation/independent_and_dependent_clauses/index.html) і [Google helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). Exercises і learner comments не копіювалися як нові завдання чи нормативні правила.

Чотири exact Cambridge URLs — [Relative pronouns](https://dictionary.cambridge.org/grammar/british-grammar/relative-pronouns), [Non-defining](https://dictionary.cambridge.org/grammar/british-grammar/non-defining-relative-clauses), [Conjunctions](https://dictionary.cambridge.org/grammar/british-grammar/conjunctions), [Causes reasons results and purpose](https://dictionary.cambridge.org/grammar/british-grammar/conjunctions-causes-reasons-results-and-purpose) — повернули 403 при direct open. Використано official indexed main bodies із явно записаними actual returned URLs; direct-success/redirect не вигадується. Повний access journal і предметні межі кожного джерела наведені в editorial artifact. Додатково перевірено since/as time versus reason; publisher examples не перенесено в own practice. Старий meeting-приклад збережено за прямою вимогою.

## Артефакти й точні SHA256

| Артефакт | SHA256 |
| --- | --- |
|[Author master](C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/docs/content/m63-relative-completion-linking.v1.0.0.json)|3e9db52c673b8a5c3c04b04d59fdcf395a14371c9d5e37a8f7b7048a64d19e0c|
|[Readable UK](C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/docs/content/m63-relative-completion-linking.v1.0.0.uk.md)|1783ba122dfaa6e3a8e0f32cd2c2f6b093e6831719dde90ed8f189d089977f0a|
|[Editorial BEFORE AFTER](C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/docs/content/m63-relative-completion-linking.editorial.v1.0.0.md)|34be80a0dd1e44f1b59578167110701ab2d45aa8b5ce482bea02574cc4904c16|
|[Component basic detail map](C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/docs/content/m63-relative-completion-linking.components.v1.json)|5ec6c475b7556ba114da551709d9a6a1f1c021da1bdd6ad38613800924465c55|
|[Native practice JS suite](C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc/tests/Browser/m63-native-practice-state.test.cjs)|3b28e3c50923b4f1ea27f3aef8cd3d2b2c7ef865f4d54e3b439fa6b25c2b4c2d|

Public source BEFORE: c89d4606e03cb639f94a482586701a8de2933b68d04a3a845f17c7ea8f322d2f. Native patch: b1351893c3669e9946e311c13fae07e6f378640bbc120b71256b2bcceb740a01. Builder contract:8676bb5932f1fe1efda6c97f20e17f010ffa87d2fd4b5e8e5fb62978e29fd96c. Bank audit:627dc8ebd915ee6555ce9d42de6251e4da738799d43eddbb055deb25f1316b6c. Final technical helper/test/writer hashes наведено в окремій таблиці finalization; їхні accepted preimages7587706b…1fb7 /af5b4600…beb2 /e7502f38…1134 збережено в lineage receipt. Checksums manifest frozen teaching artifacts опублікований і незмінний, SHA 8a2c9cfbd4af4c0f8044b51fcf4768d927f0fbcbb4c305a3489ff8c72bcea95d.

Master/readable/editorial/component/JS files мають LF, без BOM, рівно один завершальний newline. Приватні BEFORE/proof/backup/diagnostic receipts не комітяться; у звіті наведені тільки їхні безпечні fingerprints і назви.

## Статус етапів

| Gate | Підтверджений статус |
| --- | --- |
|Final bank helper і його isolated tests|Завершено; у suite 56 tests /11 406 assertions /1 deprecation, exit0|
|Source-sync exact hunks і збереження сторонніх ROOT changes|Завершено; original31-path v1/50 reverse checks та окремі final31-path v2/6 preimage checks|
|Fresh web/CLI proof receipt та nonce404 cleanup|Завершено; api.php exact відновлено, окремий GET404 без redirect|
|Fresh preview та exact diff review|Завершено; 27/3/0, усі 30 changes перевірені|
|Exclusive backup і transactional local apply|Завершено; actual 27 updates /3 inserts /0 deletes|
|Original apply/postUI SQL/source postconditions|PASS на original sources;46 tables,24 UK,48 EN/PL,3 нові blocks; final technical lineage окремо|
|Repeated no-op без нового backup|Два actual NOOP0/0/0: зі старим і fresh AFTER планом; жодного нового backup|
|Live SSR all-field coverage|PASS;406 learning fields,3 HTTP200,3 exact widget contracts|
|Ширше AFTER old-consumer порівняння|PASS;62 direct HTTP200/exact BEFORE,8 saved tests,528 old questions,46 SQL tables незмінні; перший timeout run окремо|
|Live browser18 states,24 inputcases,8 variants,36 options,details і 15widgets5/5|Завершено; фактичні document overflow та normalization limits наведено окремо|
|Actual AFTER registry112 targets та previous109 regression|PASS;112 targets,0 failed/writes;109 stable rows без differences, raw HTTP SHA виключено явно|
|Current PHP/regression union|495 deduplicated tests:439 regressionv2 +56 final-sourcev2,0 overlap/0 failures; v1 і historical2 PASS /4 known FAIL окремо|
|Фіналізація 3 technical files та final source proof|Завершено; exact6 ROOT/WT preimages, final syncv2;11 teaching artifacts незмінні|
|9-file suite на final technical sources|PASS56/56,11 406 assertions,1 deprecation,exit0,0 protected changes|
|Commit і push робочої гілки|Підготовлено до scoped commit і normal push; фактична Git-доставка ще не заявляється|

No-JS, print, повний WCAG audit та завантаження external fonts не перевірялися й не називаються PASS. Production доступу чи deploy немає. Локальна реалізація й приймання M63 завершені. Повний commit SHA та незалежне підтвердження рівності remote branch будуть наведені у фінальному повідомленні після Git-доставки, щоб звіт не посилався на SHA власного ще не створеного commit.
