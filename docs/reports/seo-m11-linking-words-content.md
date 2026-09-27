# M11 — Linking Words B2–C2

Дата: 27.09.2026 (Europe/Kyiv). Репозиторій: `Pvitaly91/engapp-codex`.

## Результат і межі

Готовий versioned контент для трьох українських уроків: пояснення, авторські англійські приклади з перекладом, типові помилки, по шість завдань із розгорнутим ключем. Додано вузьку команду preview/apply/restore та цільові тести.

**Застосування до робочої локальної БД НЕ виконано.** Фізичне PDO-з’єднання перевірено як локальне MySQL, усі три уроки точно відповідають початковим sources. Однак поточний локальний `.env` має `APP_ENV=production`. Запис суперечив би local/testing guard; середовище, `.env` і guard не підмінювалися. Нові тексти перевірено на ізольованих SQLite fixtures і в тимчасовому DOM браузера з ресурсами `http://gramlyze.loc`. Живі локальні сторінки досі показують початковий матеріал. Це не завершене приймання оновленої робочої БД.

Production не перевірявся і не змінювався. Немає деплою, PR, merge, push у main, міграцій, пересівання робочої БД, дампу, очищення кешів чи змін залежностей. M10, www/:443, XAMPP/PHP/Composer не повторювалися.

## База і ізоляція

- Після fetch `origin/main`: `8b4d52b003f7f827b6a584f54fa28eb6934d14ab`, збігається з наданою базою.
- Робоча гілка: `codex/seo-m11-linking-words-content`, створена від цієї бази.
- Основний checkout мав іншу гілку й сторонні незакомічені зміни. Вони не скидалися і не включалися в пакет.
- Робота виконана в окремому worktree `storage/app/seo-m11-worktree`. Його sparse checkout не матеріалізує великий банк snapshots, але не видаляє його з Git-індексу.
- У worktree немає робочого `.env`; тести використовують чинний ізольований runner, SQLite `:memory:`, окремі storage/views, array cache/session. Guard-и не послаблено.

## Фактичні mappings та ідентичності

Для всіх трьох: `type=theory`, категорія `clauses-and-linking-words`, мова категорії та definition `uk`. Повний `Page.seeder` — `Database\Seeders\Page_V3\ClausesAndLinkingWords\` + назва нижче. Source — `database/seeders/Page_V3/ClausesAndLinkingWords/<Seeder>/definition.json`.

| Урок / незмінний H1 | Seeder | Рівень | Локальний Page ID | Існуючі block ID: subtitle / hero / box |
|---|---|---|---:|---|
| Linking Words for Reason, Result and Contrast | LinkingWordsReasonResultContrastTheorySeeder | B2 | 290 | 8753 / 8756 / 8759 |
| Advanced Linking Devices | AdvancedLinkingDevicesTheorySeeder | C1 | 302 | 8793 / 8794 / 8795 |
| Concessive And Contrastive Structures | ConcessiveAndContrastiveStructuresTheorySeeder | C2 | 315 | 8834 / 8835 / 8840 |

Числові ID вище — лише інвентаризація цієї локальної БД, не параметри перенесення. Patch знаходить сторінки за seeder і перевіряє slug/category/title, а блоки — за незмінними UUID та власником.

| Seeder (скорочено) | subtitle UUID | hero UUID | box UUID |
|---|---|---|---|
| LinkingWordsReasonResultContrast | 374d5b38-809b-576d-9e1b-2e06a668028c | 766fa4db-6dbe-57f6-b30a-1053f2800eea | e99502d5-8d19-51ad-9a76-a0e747001c1c |
| AdvancedLinkingDevices | b9bbb5a3-78fc-5457-ab21-696d7dce1a0f | 17074786-c004-5142-9d70-d83888c2e83e | befc0f3c-3efd-53e0-9fe4-adaa13ed475a |
| ConcessiveAndContrastiveStructures | 14900d34-9da7-5bb0-a44b-cc11a2c2ff31 | 975d29af-99e7-58ba-9082-2c7bd7dd0fe3 | c5ce8f61-5409-55ae-9a94-76828593912e |

Порядок рядків — 0/1/2; у definition залишилися ті самі hero/box, subtitle створюється за чинним правилом. Не додано блоків, UUID, aliases чи нових рівнів. Наявних EN/PL блоків цих трьох сторінок у робочій БД не знайдено; EN/PL sources не редагувалися, збереження інших локалей додатково перевірено синтетичними fixtures.

### URL теорії та основних тестів

| Урок | Теорія | Основний тест |
|---|---|---|
| B2 | [Linking Words](http://gramlyze.loc/theory/clauses-and-linking-words/linking-words-reason-result-contrast) | [Mixed — Linking Words](http://gramlyze.loc/test/clauses-and-linking-words/linking-words-reason-result-contrast) |
| C1 | [Advanced Linking Devices](http://gramlyze.loc/theory/clauses-and-linking-words/advanced-linking-devices) | [Mixed — Advanced Linking](http://gramlyze.loc/test/clauses-and-linking-words/advanced-linking-devices) |
| C2 | [Concessive and Contrastive Structures](http://gramlyze.loc/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | [Mixed — Concession and Contrast](http://gramlyze.loc/test/clauses-and-linking-words/concessive-and-contrastive-structures) |

Resolver `TheoryPageTestSlug` і фактичні href збігаються. Кожна існуюча картка тесту вже має title-link та одну кнопку «Пройти тест»; нової кнопки в body немає. Кожний урок отримав два доречні посилання на інші уроки пакета. Посилання без `/step`, auto/filter параметрів чи обходу Coming Soon.

Курсова копія існує через `TheoryCourseManifestService` і використовує ту саму Page/textBlocks:
[урок B2 у курсі](http://gramlyze.loc/courses/english-grammar-theory/lesson/clauses-and-linking-words/linking-words-reason-result-contrast).
Аналогічні paths C1/C2 підтверджено resolver-тестами. Гостьовий GET курсу B2 повертає 200, але вміст уроку закритий прогресом курсу. Його не розблоковували; тотожність повного контенту доведено лише ізольованим рендером, не гостьовим live-переглядом.

## Наявний матеріал → недолік → новий матеріал

Перевірка початкового main і локальних записів підтвердила англомовні subtitle/hero та короткі службові box. Ручних розбіжностей немає. Це висновок про sources і локальну БД, **не про поточну production-БД**.

### B2: конструкція після зв’язки

Було: перелік because of, due to, therefore, as a result, although, despite, however у hero та `theory anchor` у box; не було достатнього пояснення синтаксису, пунктуації й самоперевірки.

Додано українські пояснення причини, результату й протиставлення; таблицю «зв’язка → конструкція → приклад → переклад»; because + clause проти because of/due to + noun phrase; so проти therefore/as a result; although/even though проти despite/in spite of; межі речень із however/nevertheless. Збережено правильну наявну навчальну ідею, розгорнувши її у повноцінний урок.

Авторський приклад: `We changed our route because the bridge was closed.` → `We changed our route because of the bridge closure.` Пояснено зміну повної підрядної частини на іменникову групу. Шість завдань перевіряють конструкцію, пунктуацію та перебудову речення; для відкритих відповідей подано допустимі альтернативи.

### C1: логіка формального аргументу

Було: `compact anchors for precise logical relations` та службовий box; перелік засобів не пояснював логічних обмежень вибору.

Додано розмежування поступки, висновку, умови й додаткового аргументу; therefore/consequently, moreover/furthermore, provided that; окремий розділ про insofar as як міру/межі твердження, а не автоматичний синонім provided that. Авторський абзац про пілотний проєкт у школах має повний переклад і розбір кожного переходу. Є помилки надмірності та необґрунтованого висновку.

Приклад: `The service is cheaper. Moreover, it is easier to use.` не тотожний варіанту з `therefore`: другий потребує причинного обґрунтування. У самоперевірці контекст розрізняє письмову згоду як умову й пояснювальну здатність моделі як межу оцінки. Ключ явно допускає обидва доречні варіанти додавання аргументу.

### C2: контраст, допустовість і контекст

Було: короткі anchors для although/while/whereas/even though без достатнього контексту й опрацювання скорочених частин.

Додано контраст двох фактів проти результату всупереч очікуванню; even though для заданого факту проти even if для ще невідомої умови; contrastive while/whereas і окреме часове while; although/though, despite/in spite of/the fact that; вимоги до спільного підмета в reduced clauses; вплив позиції допустової частини на акцент.

Приклад помилки: `Although exhausted, the presentation continued.` → `Although Maya was exhausted, the presentation continued.` Або `Although exhausted, Maya continued the presentation.` Ключ пояснює хибного носія ознаки, а не лише показує виправлення. Шість контекстних завдань із поясненнями; рівень C2 збережено як наявну позначку уроку, без заяв про сертифікацію чи «C2 через один сполучник».

### Відображення

Збережено чинні hero/box renderer. Після візуальної перевірки додано лише inline-оформлення всередині трьох body: виразні h4, нумерація ol, відступи, клікабельний summary, рамки/відступи таблиці. Це запобігає зникненню нумерації через чинний CSS reset. Ключі — нативний `<details>`, без JS/framework. На mobile таблиця прокручується у власному контейнері, сторінка не розширюється.

Build не запускався: Vite/Tailwind inputs, JS, CSS, Blade та utility-класи не змінювалися; JSON definitions не входять у чинний Tailwind content scan. Сформований PHP HTML і локальні inline styles не потребують нових bundled assets.

## Джерела перевірки граматики

Джерела використовувалися для правил, не для копіювання статей чи вправ. Навчальні приклади й 18 завдань створено для цього пакета.

- Повністю доступна сторінка [British Council — contrasting ideas](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/contrasting-ideas-although-despite-others): clause/noun/-ing, although/even though/despite, позиція.
- Повністю доступний [Cambridge publisher blog — Whereas, despite and nevertheless](https://dictionaryblog.cambridge.org/2022/11/16/whereas-despite-and-nevertheless-ways-to-link-ideas-1/): контраст і допустовість, позиція зв’язок.
- Прочитано доступні індексовані видавничі фрагменти [Cambridge — conjunctions](https://dictionary.cambridge.org/grammar/british-grammar/conjunctions), [because/because of](https://dictionary.cambridge.org/grammar/british-grammar/because-because-of-and-cos-cos-of), [whereas](https://dictionary.cambridge.org/grammar/british-grammar/whereas): конструкція і синтаксичний зв’язок.
- Індексовані фрагменти [Cambridge — though](https://dictionary.cambridge.org/us/grammar/british-grammar/though), [even](https://dictionary.cambridge.org/us/grammar/british-grammar/even), [conditionals: other](https://dictionary.cambridge.org/uk/grammar/british-grammar/conditionals-other): reduced clauses, even though/even if, provided that.
- Індексовані словникові фрагменти [insofar as](https://dictionary.cambridge.org/us/dictionary/english/insofar-as), [due to](https://dictionary.cambridge.org/us/dictionary/english/due-to), [furthermore](https://dictionary.cambridge.org/us/dictionary/english/furthermore), [therefore](https://dictionary.cambridge.org/us/dictionary/english/therefore): межі значення, причина, додавання та висновок.

Пряме відкриття п’яти Cambridge URL, наведених у завданні (`conjunctions`, `conjunctions-contrasting`, `although`, `in-spite-of-and-despite`, `insofar-as`), повертало HTTP 403 у засобі перегляду. Їх **не позначено як повністю прочитані**. Індексовані фрагменти/доступні сторінки відокремлені від цих невдалих прямих відкриттів.

## Адресний patch, preview і відкат

Файли:

- `app/Services/LinkingWordsContentPatch.php`;
- `app/Console/Commands/PatchLinkingWordsContent.php`;
- `database/content-patches/m11-linking-words-before.json` — тільки початкові versioned definitions, не дамп і не приватний record-backup.

Команда `content:patch-linking-words-m11` повторно використовує фізичний PDO/host/database guard `PronounContentRepair`, не змінюючи старі M8/M8.1 patches. Дозволені лише `pages.text` і `heading/body` існуючих UK subtitle/hero/box. Перевіряються власник, UUID, порядок, тип, категорія, точні before-значення, sources, локалі, tags/pivots і пов’язані question records. Частковий/ручний/неоднозначний стан спричиняє відмову. Можна перевірити незалежну сторінку через `--only=<точний Seeder basename>`.

Read-only preview робочої БД створено приватно: `storage/app/seo-m11-local/m11-preview-final.json`. У ньому **12 змін рядків: 3 pages + 9 text_blocks**, тільки дозволені поля. SHA-256 плану: `da114bfffe0752db7f517d0006e3a016fcae65836e96404f95ffffa483ebdacf`. Початковий preview збережено окремо, він застарів після фінального оформлення і не призначений для apply.

Apply заблоковано поточним production-профілем локальної програми. **Record-backup робочої БД не створювався, бо запис не починався.** Не слід називати preview резервною копією вже виконаної операції.

Перед майбутнім дозволеним локальним apply потрібні окремо погоджене виправлення невідповідності локального профілю та нова перевірка фізичної БД. Не треба підміняти APP_ENV одноразовою змінною. Після цього вузький workflow у checkout з цим пакетом:

```text
php artisan content:patch-linking-words-m11 --plan=m11-preview-NEW.json
# Прочитати точні записи/поля приватного preview; звірити фізичне ім’я БД.
php artisan content:patch-linking-words-m11 --apply --plan=m11-preview-NEW.json --backup=m11-records-NEW.json --database=<перевірена-локальна-БД>
```

Plan і backup створюються виключно як нові файли у приватному `storage/app/seo-m11-local`; існуючий файл не перезаписується. Перед UPDATE — блокування рядків, повторна перевірка плану, exclusive record-backup, транзакція та postcondition. Повторне застосування того самого прийнятого плану — no-op. Зміна джерел/даних/зв’язків після preview — відмова.

Реальний адресний restore, **перевірений на fixture, не виконаний на робочій БД**:

```text
php artisan content:patch-linking-words-m11 --restore --backup=m11-records-NEW.json --database=<та-сама-перевірена-локальна-БД>
```

Restore вимагає відповідності поточних records і sources збереженому after-стану, повертає лише дозволені поля, перевіряє byte-exact before, відмовляє при подальших редагуваннях. Немає reseed/масового rollback.

## Приймання та обмеження

### Автоматичні тести

Запуск лише цільових suites через `tools/diagnostics/run-isolated-tests.py` з PHP 8.5.10:

```text
python tools/diagnostics/run-isolated-tests.py --php "C:\Program Files\xampp\php\php.exe" --label seo-m11-final tests/Feature/LinkingWordsContentPackageTest.php tests/Feature/LinkingWordsContentPatchTest.php
node --test tests/Browser/seo-m11-local.test.cjs
```

- PHP: **21 тест, 449 assertions, exit 0** (6 content/render + 15 patch).
- Node: **1 тест, pass**, local-only/read-only allowlist.
- Збережено старе попередження PHP 8.5: `config/database.php:62`, deprecated `PDO::MYSQL_ATTR_SSL_CA`. Воно не виправлялося в цьому контентному етапі.
- Перевірено JSON/вкладений hero JSON; незмінні title/category/locale/tags/рівні/UUID; 18 prompts і 18 пояснених ключів; links/resolvers; old→new; no-op; manual/stale/source conflict; exclusive backup; transaction rollback; exact restore; EN/PL; question pivots; fresh import parity.
- Fresh `JsonPageSeeder` запускався лише в SQLite fixture. Паритет patched fixture ↔ fresh fixture пройшов. Паритет **оновленої робочої БД** не перевірено, оскільки apply не відбувся.
- Ізольовані request objects для local/production-host проходять реальні SEO middleware; HTTP на `.com` не виконується.

### HTTP і браузер

Усі три theory, три основні test, один course URL і sitemap перевірено GET на `gramlyze.loc`: **8/8 HTTP 200**. Метадані записано до/після. Немає авторизації, відповідей на тести, course unlock чи обходу доступу.

Chromium 147.0.7727.15: desktop 1440×1000, mobile 390×844, нові контексти, українська locale.

1. **Живий початковий контент:** 6/6 сценаріїв; box видимий, сирого HTML/горизонтального overflow немає. Три desktop переходи відкрили точні основні тести. Усі мали 0 нових самоперевірок, як і очікується до apply.
2. **Новий контент fixture:** 6/6 сценаріїв. Справжній Blade HTML із fresh fixture вставлявся тільки в `[data-theory-main]` тимчасового браузерного DOM з локальними assets. Це не зміна сайту/БД. Перевірено матеріал нижче першого екрана, переклади, шість пронумерованих prompts/keys, відкриття details, відсутність сирого HTML й overflow. Таблиця mobile: контейнер 266 px, вміст 640 px, реальне прокручування на 374 px. Збережені й візуально переглянуті screenshots, у тому числі правий край таблиці.
3. **Курс:** гостьовий 200, повний матеріал закритий; його рендер перевірено тільки fixture, обмеження не обходили.

У перевірених браузерних сторінках немає uncaught JS errors. Google Fonts CSS не завантажувався через `net::ERR_NETWORK_ACCESS_DENIED` середовища перевірки; використовувався fallback font. При переході до тестів діагностичний guard свідомо блокував state POST, зафіксований як невдалий запит. Тому це приймання контенту/переходів, не повний інтерактивний тест банків або webfonts.

Приватні матеріали, не включені в commit: `storage/app/seo-m11-local/{inventory-before,inventory-after,before-http,after-http,live-before-browser,final-render-fixtures,m11-preview-final}.json`, screenshots; ізольовані renders/result у worktree `storage/app/seo-m2-local`. Не зберігали токени, cookie чи `.env` у звіті.

## Метадані: дозволені зміни лише трьох descriptions

H1 і Page.title збережено дослівно; title має формат `<Page.title> — правила | Gramlyze`. Canonical залишився `https://gramlyze.com` + той самий theory path. На локальному хості X-Robots-Tag — `noindex, nofollow, noarchive`, meta robots відсутній. Фікстури до/після перевіряють незмінність цих значень; на ізольованому production-host немає цього локального X-Robots-Tag. Загальні SEO правила не редагувалися.

Нижче «після» — **фактично зрендерене значення fixture**, не вже змінена live-сторінка. Для кожного meta description = OG description = Twitter description.

| Урок | До (live і старий fixture) | Після (новий fixture) |
|---|---|---|
| B2 | Зв’язок причини, наслідку й протиставлення: because of перед іменником, so для наслідку та although для контрасту. Короткий огляд споріднених слів-зв’язок. | Linking Words for Reason, Result and Contrast — як пояснити причину, показати наслідок і протиставити факти англійською. |
| C1 | Логічні зв’язки у формальному викладі: допустове значення з although, наслідок із consequently та умова з provided that. Також наведено засоби додавання аргументів. | Advanced Linking Devices — як будувати послідовний аргумент і точно позначати причину висновку, умову та межі твердження. |
| C2 | Допустове значення з although і despite та зіставлення різних позицій через while і whereas. Як розмістити уточнення, щоб головне твердження залишалося зрозумілим. | Concessive and Contrastive Structures — як зіставити факти та показати результат усупереч очікуванню. |

Фактичні live title/H1/description/canonical/robots усіх семи HTML URL до/після цієї роботи однакові, бо робоча БД не змінена. Правила `PageMetadata` і 43 M7 editorial descriptions не змінювалися.

## Незмінність даних і sitemap

Read-only snapshot трьох локальних pages, усіх їх blocks і перевірених pivots до/після ідентичний. Для цілих таблиць нижче збігаються кількість і SHA-256 потоку всіх відсортованих рядків:

| Таблиця | Рядків |
|---|---:|
| questions | 44 460 |
| question_answers | 197 312 |
| question_options | 27 154 |
| question_option_question | 541 151 |
| question_tag | 140 637 |
| saved_grammar_tests | 750 |
| verb_hints | 17 004 |

Не редагувалися banks, answers/options/hints, filters/алгоритми, теги, EN/PL чи question snapshots. Нових SavedGrammarTest немає.

Один before/after ordered sitemap comparison: **554 URL**, порядок і склад збігаються; SHA-256 ordered loc array в обох випадках `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Це перевірка незмінного робочого сайту протягом етапу, не post-apply результат. Routes, sitemap implementation, canonical і robots не редагувалися.

## Майбутнє перенесення саме цього пакета

Окремо потрібні дозвіл на production-операцію, read-only інвентаризація саме трьох server-side identities і точних before-значень, приватний record-backup на цільовій БД та погоджений вузький transactional updater із server-specific guard. Чинна M11 команда свідомо **не дозволяє production-запис**; не слід обходити це підміною APP_ENV.

Не переносити локальні числові ID або всю локальну БД. Якщо серверні записи редагувалися вручну — окремо узгодити розбіжності; не пересівати. Після адресного перенесення потрібні перевірка трьох сторінок/тестових посилань і курсового рендера, metadata та ordered sitemap comparison. Ні це перенесення, ні production-приймання в M11 не виконані.

## Git-пакет

До commit включаються лише три definitions, початковий source manifest, M11 service/command, два PHP tests, один Node guard test, вузький local diagnostic tool і цей звіт. `.env`, backups/dumps, vendor, build, caches, приватні snapshots і сторонні зміни виключені. Перед commit — staged diff та `git diff --check`; push звичайний у `codex/seo-m11-linking-words-content`, без force/PR/merge/deploy. Підсумковий SHA та звірку remote наведено у фінальному повідомленні.
