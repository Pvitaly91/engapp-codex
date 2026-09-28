# M22 — артиклі, кількісні слова та collocations

Дата приймання: 2026-09-28. **Зміни застосовано до робочого gramlyze.loc.** Це три допрацьовані українські уроки, 18 самоперевірок із поясненими ключами, а не новий Mixed-банк.

## Межі та фактична база

- Робоча гілка пакета: `codex/seo-m22-articles-collocations-content`.
- Після fetch локальний HEAD і remote прийнятого M21 збігалися: `ff0db69cbd93fdf5cceddc7eb6198fee1999ef9f`; ancestry перевірено. Новішого погодженого продовження не виявлено.
- Використано вільний існуючий worktree `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m11-worktree`. Основний checkout на `codex/production-ready-a14788dac`, SHA `41820a2bebdf69004fa7209a2a38457f93efabbd`, мав 46 646 видалень tracked-файлів, 8 модифікацій і untracked-матеріали. Його не скидали, не перемикали й не включали ці зміни в пакет.
- Прочитано AGENTS.md, звіти M21 та M12 styling. Старі 33 уроки, manifests/allowlists, apply/restore-контракти, renderer, Blade, CSS та build inputs не редагувалися.
- HTTP/браузерні запити приймання — тільки `http://gramlyze.loc`. Production-поведінка guard перевірялася ізольованими тестами. Адреси .com у canonical/sitemap аналізувалися лише як рядки.
- Немає PR, merge, force push, workflow dispatch, деплою, migrations, робочого db:seed, повного дампа, cache/session clear, ремонту XAMPP або оновлення залежностей.

## Точні цілі, джерела і URL

Повні identities (не скорочені basename):

1. `Database\Seeders\Page_V3\ArticlesAndQuantifiers\AdvancedArticleAndQuantifierNuanceTheorySeeder`
2. `Database\Seeders\Page_V3\ArticlesAndQuantifiers\PrecisionWithArticlesAndDeterminersTheorySeeder`
3. `Database\Seeders\Page_V3\VocabularyAndCollocations\AdvancedCollocationAndLexicalChoiceTheorySeeder`

Канонічні definition-файли:

- `database/seeders/Page_V3/ArticlesAndQuantifiers/AdvancedArticleAndQuantifierNuanceTheorySeeder/definition.json`
- `database/seeders/Page_V3/ArticlesAndQuantifiers/PrecisionWithArticlesAndDeterminersTheorySeeder/definition.json`
- `database/seeders/Page_V3/VocabularyAndCollocations/AdvancedCollocationAndLexicalChoiceTheorySeeder/definition.json`

| Незмінний Page.title / controller H1 | Рівень | Page / text_blocks IDs | Фактичні URL |
| --- | --- | --- | --- |
| Advanced Article and Quantifier Nuance | C1 | 306 / 8805, 8806, 8807 | [Теорія](http://gramlyze.loc/theory/articles-and-quantifiers/advanced-article-and-quantifier-nuance), [основний тест](http://gramlyze.loc/test/articles-and-quantifiers/advanced-article-and-quantifier-nuance) |
| Precision With Articles And Determiners | C2 | 317 / 8832, 8836, 8838 | [Теорія](http://gramlyze.loc/theory/articles-and-quantifiers/precision-with-articles-and-determiners), [основний тест](http://gramlyze.loc/test/articles-and-quantifiers/precision-with-articles-and-determiners) |
| Advanced Collocation And Lexical Choice | C2 | 316 / 8833, 8837, 8839 | [Теорія](http://gramlyze.loc/theory/vocabulary-and-collocations/advanced-collocation-and-lexical-choice), [основний тест](http://gramlyze.loc/test/vocabulary-and-collocations/advanced-collocation-and-lexical-choice) |

URL встановлено через чинні Page/course/test resolvers та навігацію, а не виведено з namespace. Для перших двох ancestry — тільки `articles-and-quantifiers`; для третього — тільки `vocabulary-and-collocations`. Перехресне потрапляння до іншої дозволеної категорії M22 відхиляється тестом. Різницю `Articles and Quantifiers` / `Articles and quantifiers` у sources збережено.

Перед записом sources збігалися з робочими Page.text, subtitle/hero/box; перевірено IDs, UUIDs, власників, sort_order, локаль, category ancestry та pivots. Невідомих ручних конфліктів у трьох цілях не було.

## Початковий матеріал і виконане розширення

До M22 кожний урок мав короткий англомовний hero з трьома опорами та службовий box про призначення сторінки як theory anchor. Це не означає, що всі короткі правила були помилковими: основний недолік — недостатність контексту, пояснення й практики.

Після M22 кожний має український intro, три початкові тематичні опори, шість змістовних секцій, компактну таблицю, власні англійські приклади з українським перекладом, шість завдань, шість пояснених ключів і доречні наступні посилання.

- **C1:** вибір форми за конкретним значенням іменника; research/information/evidence та одиниці; few/a few/little/a little; кількість, оцінка та достатність; much/many/several і регістр; a/the/zero; a number of/the number of; одна BrE/AmE інституційна пара; редактура архівної нотатки.
- **C2 articles:** зв’язний текст і відсилання, а не повтор C1-таблиці. Явно задані предмети/групи, generic/specific, some/any як пропозиція/відкрите питання/вільний вибір, each/every та of-моделі, група з двох, not all/none і not both/neither, мінітекст без домислених результатів.
- **C2 collocations:** дев’ять навчальних моделей із варіантами: pose/present a challenge, face a challenge, set/establish a precedent, draw/make a distinction, raise a question, answer a question, substantial gap, documentary evidence, significant challenge. Перевірено всю модель, ролі, значення і межі заміни. Є коротка театральна нотатка до/після редагування та інструкція роботи зі словником. Третій урок не зроблено штучним обов’язковим продовженням двох попередніх.

Рівні C1/C2/C2 залишено як редакційні позначки Gramlyze; офіційної CEFR-сертифікації або SEO-гарантій не заявлено.

### До / після та статус уточнень

| Початкова опора Gramlyze | Після | Статус |
| --- | --- | --- |
| Article choice як короткий перелік general/specific/countable/institutional | Спочатку значення іменника; артикль не робить будь-який іменник довільно злічуваним. Окремі контексти для відомого предмета і закладу | Уточнено межі; не оголошено весь початковий перелік помилкою |
| Quantifier nuance без розбору достатності | `We have a few spare labels, but not enough for all the boxes.` | Авторський приклад: позитивна оцінка наявної кількості не гарантує достатності |
| Generic vs specific / Determiner nuance у трьох коротких правилах | Задана група, відсилання і заперечення; неперевірена друга лампа не стає доведено несправною | Авторське контекстне розширення |
| Іменник начебто очікує одне specific verb | Показано допустимі set/establish, draw/make, pose/present з межами; face змінює граматичну роль | Підтверджене виправлення надмірно вузького формулювання |
| Формальна лексика без контекстної шкали | Мала різниця не стає substantial gap; significant не автоматично статистичне | Підтверджене семантичне уточнення |
| Службові theory anchor boxes | Повноцінні уроки й 18 самоперевірок | Погоджене авторське доповнення |

Редакційна рекомендація на майбутнє: зберігати ці контекстні межі при перекладі та розширенні банків. Це не виконана зміна інших уроків або тестових даних.

## Read-only контекст і внутрішні посилання

Нижче — фактичні identities відносно `Database\Seeders\Page_V3\` та перевірені локальні цілі; вони не є цілями apply:

| Identity suffix | Локальний урок |
| --- | --- |
| NounsArticlesQuantity\NounsArticlesQuantityArticlesTheorySeeder | [Articles](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/articles-a-an-the) |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | [Countable / Uncountable](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/countable-vs-uncountable-nouns) |
| NounsArticlesQuantity\NounsArticlesQuantityQuantifiersTheorySeeder | [Quantifiers](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little) |
| PronounsDemonstratives\PronounsDemonstrativesEachEveryAllTheorySeeder | [Each / Every / All](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/each-every-all) |
| NounsArticlesQuantity\NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder | [No / None / Neither / Either](http://gramlyze.loc/theory/imennyky-artykli-ta-kilkist/no-none-neither-either) |
| SentenceStructure\ComplexNounPhrasesTheorySeeder | [M13 Complex Noun Phrases](http://gramlyze.loc/theory/sentence-structure/complex-noun-phrases) |
| FormalEnglish\NominalisationFormalRegisterTheorySeeder | [M16 Nominalisation and Formal Register](http://gramlyze.loc/theory/formal-english/nominalisation-formal-register) |
| AcademicEnglish\StanceRegisterAndEvaluationTheorySeeder | [M17 Stance Register And Evaluation](http://gramlyze.loc/theory/academic-english/stance-register-and-evaluation) |
| FormalEnglish\ParaphraseAndReformulationTheorySeeder | [M18 Paraphrase and Reformulation](http://gramlyze.loc/theory/formal-english/paraphrase-and-reformulation) |
| RelativeClauses\ComplexRelativeClausesTheorySeeder | [M21 контроль](http://gramlyze.loc/theory/relative-clauses/complex-relative-clauses) |

Додатково read-only переглянуто definitions `Articles\SomeAny\SomeAnyCategorySeeder` та `Articles\SomeAny\SomeAnyThingsTheorySeeder`: базовий розділ зосереджений на people/places/things, не замінює C2 reference/scope. [Some / Any](http://gramlyze.loc/theory/some-any) і [Things](http://gramlyze.loc/theory/some-any/theory-some-any-things) дали HTTP 200 у додатковому GET; це не частина основного before/after профілю з 18 URL. Звичайний PowerShell-запит не завершився; його зупинено і виконано прямий curl GET без proxy з timeout.

Посилання нових уроків ведуть до релевантної бази/продовження; штатний основний test href залишився єдиним, без дублювання кнопки й підміни auto/filter/step.

## Первинні джерела і межі доступу

Перевірка 2026-09-28. Навчальні тексти й приклади створено для Gramlyze; зовнішні вправи та абзаци не копіювалися.

**British Council — доступний редакційний текст:** [Quantifiers](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2-grammar/quantifiers-few-a-few-little-a-bit) підтверджує сумісність few/little, відмінність оцінки й типову, не гарантовану, достатність; [The definite article](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/definite-article) — упізнаваність і ситуаційне/текстове відсилання. Учнівські коментарі не використано як нормативний матеріал.

**Cambridge — прямі відкриття потрібних статей повертали HTTP 403.** Захист не обходився. Використано доступний індексований текст/фрагменти саме видавця; це не заявка на повне прочитання недоступних сторінок. Зокрема початковий URL `some-any` не видавав доступної статті; знайдено індексований матеріал `some-and-any`. Локалізований UI Cambridge не означає стороннього джерела.

- Кількість та форми: [Little / a little / few / a few](https://dictionary.cambridge.org/uk/grammar/british-grammar/little-), [Some and any](https://dictionary.cambridge.org/no/grammatikk/british-grammar/some-and-any), [Some](https://dictionary.cambridge.org/uk/grammar/british-grammar/some), [Determiners and types of noun](https://dictionary.cambridge.org/uk/grammar/british-grammar/determiners-and-types-of-noun).
- Склад групи й моделі: [Each or every](https://dictionary.cambridge.org/grammar/british-grammar/each-or-every.), [All or every](https://dictionary.cambridge.org/grammar/british-grammar/all-or-every), [Both](https://dictionary.cambridge.org/grammar/british-grammar/both), [Each](https://dictionary.cambridge.org/grammar/british-grammar/each), [Determiners: position and order](https://dictionary.cambridge.org/us/grammar/british-grammar/determiners-position-and-order), [No / none](https://dictionary.cambridge.org/grammar/british-grammar/no-none-). Зіставлено singular formal agreement із допустимими розмовними відмінностями; контекст вправ прямо обмежує модель.
- Значення й одиниці: [Number](https://dictionary.cambridge.org/grammar/british-grammar/number), [Number — dictionary](https://dictionary.cambridge.org/dictionary/english/number), [Evidence](https://dictionary.cambridge.org/dictionary/english/evidence), [Hospital](https://dictionary.cambridge.org/dictionary/english/hospital?q=hospitals), [Hospital — позначені UK/US моделі](https://dictionary.cambridge.org/us/dictionary/english-urdu/hospital). Додатково переглянуто індексований редакційний матеріал видавця про countable/uncountable nouns і much/many; не переносили рідкісні спеціальні значення на звичайні research/information/evidence.
- Collocations та альтернативи: [Collocation](https://dictionary.cambridge.org/grammar/british-grammar/collocation_2), [Distinction](https://dictionary.cambridge.org/dictionary/english/distinction), [Make/draw a distinction between](https://dictionary.cambridge.org/us/dictionary/english/make-a-distinction-between), [Set/establish a precedent](https://dictionary.cambridge.org/dictionary/english/set-a-precedent), [Challenge](https://dictionary.cambridge.org/dictionary/english/challenge?topic=difficult-situations-and-unpleasant-experiences), [Raise](https://dictionary.cambridge.org/dictionary/english/raise?q=raises), [Question](https://dictionary.cambridge.org/dictionary/english/question).
- Сила твердження: [Substantial](https://dictionary.cambridge.org/dictionary/english/substantial), [Gap](https://dictionary.cambridge.org/dictionary/english/gap), [Significant](https://dictionary.cambridge.org/dictionary/english/significant), [Statistically](https://dictionary.cambridge.org/dictionary/english/statistically). [Substantial gap — examples](https://dictionary.cambridge.org/us/example/english/substantial-gap) містить корпусні/новинні приклади: вони не трактувалися як універсальні редакційні правила.

## Окремий редакторський перегляд усіх 18 ключів

Це перевірка змісту, не висновок із кількості assertions. Зіставлено інструкцію, англійську модель, український переклад, кількість/заперечення, ролі й допустимі альтернативи. Нижче наведено модель ключа та причину рішення; повні пояснення й переклади — у відповідному уроці.

### C1 — Advanced Article and Quantifier Nuance

1. **Злічуваність.** `We received three pieces of information and some useful research about the old bridge.` Три — кількість окремих відомостей, не досліджень. Information/research у цьому значенні незлічувані. Items of information допустимо; a useful study додало б непідтверджене одиничне дослідження.
2. **Оцінка й достатність.** `We have a few spare labels, but not enough for all the boxes.` Немає суперечності: запас є, але його недостатньо для заданої потреби. Few змістило б акцент до браку; жодного вигаданого числа «3–5».
3. **Артикль у контексті.** `Please put a pencil beside the logbook. We need information about yesterday’s delivery.` Будь-який олівець, відомий спільний журнал, невизначені відомості. The не залежить від попередньої згадки саме в цьому уривку. Some information природно, але поза заданим набором a/an/the/zero.
4. **Кількісна конструкція.** `A number of labels are missing. The number of missing labels is not known.` У першому — кілька етикеток і plural agreement, у другому — число як singular head. Не додано точного числа чи нульової кількості.
5. **Обсяг перефразування.** `There is not much evidence for this explanation.` Передає little evidence, не no evidence і не доведену хибність пояснення. Переклад зберігає невелику кількість доказів.
6. **Нотатка.** `We have a little useful information. It is not enough for a complete catalogue.` Відновлено незлічуване information і задану позитивну наявність, але не достатність. Не додано завершеного каталогу.

### C2 — Precision With Articles And Determiners

1. **Референти.** The tray — згаданий піднос; the other four — решта чотири з явно заданих шести чашок після двох надщерблених. Не всі чашки в майстерні й не невідома нова четвірка.
2. **Фрагмент з артиклями.** `We found a cabinet in an empty room. The cabinet was locked, so we needed a key. Furniture needs care.` An перед empty; повторне відсилання the cabinet; конкретний ключ не визначено умовою; загальне furniture без артикля. The key можливе в іншому контексті, а не універсально неграматичне.
3. **Some/any за функцією.** `Would you like some biscuits? Are there any lockers available? You may choose any cup from these five.` Пропозиція, відкрите питання і вільний вибір одного з п’яти. Ключ не забороняє some в питаннях або any у ствердженнях.
4. **Задані формальні моделі.** `Each of the three drafts has a number. Every one of the three drafts has a number. Both of the final layouts are ready. Either of the two layouts is suitable. Neither of them is signed.` Три чернетки та два фінальні макети відомі; every of замінено every one of, both має are. Singular either/neither заданий формальним стилем, розмовний plural не оголошено неможливим.
5. **Заперечення.** За однієї доведено несправної лампи й неперевіреної другої підтримується `Not both lamps work.`, але не `Neither lamp works.` і не «рівно одна працює». Це логічна достатність фактів, не граматична заборона neither.
6. **Мінітекст.** `Six folders were placed in the cabinet. Two of the six folders were checked. Both of the checked folders were dry.` Висновок обмежений двома перевіреними. The two checked folders were dry також допустимо. Dry не замінено всебічно safe, властивість не поширено на всі шість.

### C2 — Advanced Collocation And Lexical Choice

1. **Намір мовця.** `I would like to raise a question about spare-key storage.` Завдання порушує ще не розглянуте питання, не стверджує, що відповідь відома. Answer змінило б дію, а не лише стиль.
2. **Ролі.** `The narrow passage poses a challenge to the team moving the scenery. The team faces a challenge in moving the scenery through the narrow passage.` Прохід створює труднощі, команда стикається з ними. In + -ing; не додано неможливості перенесення. Presents природна альтернатива поза явно заданою парою poses/faces.
3. **Допустимі альтернативи.** `The committee set / established a precedent. The note draws / makes a distinction between repair and replacement.` Прийнято обидві пари; between з двома об’єктами. Прецедент — можливий орієнтир, не факт майбутнього повторення.
4. **Повна модель.** `The guide draws a distinction between repair and replacement. It raises two questions about the warranty.` Виправлено артикль, between A and B та plural questions після two. Makes теж природно; у ключі збережено задане draw.
5. **Сила оцінки.** За бюджету 1000 і витрат 1002, прямо названих малою практично неважливою різницею, доречно `a small difference`, не substantial gap. Це контекстна оцінка, не універсальний числовий поріг. Significant без статистичного контексту не означає statistically significant.
6. **Редактура без нових фактів.** `The team faces a challenge in using the cramped store. The note raises a question about storage but does not answer it. One dated invoice provides documentary evidence of the purchase, not a complete account of the object’s history.` Збережено команду, тісне приміщення, одне невирішене питання й один документ. Документ підтверджує покупку, не всю історію; не додано втрати/пошкодження речей, причинності чи готового рішення.

## Guarded apply, backup та no-op

Вузький before-manifest: `database/content-patches/m22-articles-collocations-before.json`, package ID `m22-articles-collocations-v1`. Мінімальні M22 wrappers використовують чинний механізм транзакції, backup, конфліктів і restore; старі класи/формати не змінені.

Робочу фізичну MySQL-ціль підтверджено прийнятим Windows local-target guard: loopback, процес сервера, Apache/vhost/document root, узгодженість web/CLI. Початкова sandbox-відмова read-only інспекції усунута вузьким дозволом, не послабленням guard. `.env`, APP_ENV=production, APP_KEY і підключення залишилися незмінними.

Послідовність:

1. Редактура, JSON/hero/rich/controller, форматування, newline та staged diff check завершені до фінального preview.
2. Свіжий proof → `m22-final-plan.json`; digest `e0f797d223a7724174584f2a628df85f933dc73651b5e85b5873305998f5d68a`.
3. Перевірено точний scope: **12 записів** — 3 Page.text і 9 subtitle/hero/box записів, тільки погоджені навчальні поля.
4. Перед першим UPDATE створено exclusive `storage/app/seo-m22-local/m22-record-backup.json` у головному локальному каталозі. Backup байтово дорівнює перевіреному plan, містить exact before/after і hashes; не включений у Git.
5. Transactional apply повернув **applied / updated: 12**. Postcondition звірив усі after-поля і UUIDs.
6. Повторний запуск того самого плану: **no-op / updated: 0**; зайвий backup не створився.
7. Чотири source/manifest hashes після apply незмінні. Пізніша корекція діагностичного порівняння не змінювала source bytes або дані.
8. Тимчасовий read-only endpoint видалено, GET повернув **404**. `routes/api.php` байтово відновлено до збереженого стану; сторонні зміни не затерто.

Private докази: `inventory-before.json`, `before-http.json`, `after-http.json`, `after-comparison.json`, `protected-before.json`, `protected-after.json`, `apply-acceptance.json`, браузерні JSON і screenshots — тільки в `storage/app/seo-m22-local`, не в commit. У звіт не включені proof-токени, cookies, credentials або вміст backup.

## Автоматичне приймання

Ізольований PHP runner: PHP 8.5.10, PHPUnit 12.5.35, SQLite :memory:, окремі testing storage/cache/session; без запису в робочу БД.

```text
tools/diagnostics/run-isolated-tests.py --php <XAMPP PHP> --label m22-final --
  tests/Feature/ArticlesCollocationsContentPackageTest.php
  tests/Feature/ArticlesCollocationsContentPatchTest.php
  tests/Feature/M11LocalTargetGuardTest.php
  --display-deprecations

node --test tests/Browser/seo-m22-local.test.cjs
```

- **43 PHP tests / 793 assertions: пройшли**, 0 failures, 1 deprecation.
- **9 Node tests: пройшли.**
- Pint: 6 нових PHP-файлів пройшли; JSON/rich/no raw presentation wrappers і newline перевірені.
- Покрито identities/category/ancestry, H1/title/meta/OG/Twitter, resolver/href, 18 окремих self-checks, незмінність попередніх sources, old→new/no-op/manual/stale conflict, exclusive backup, rollback, guarded restore та непідтверджені цілі.
- Негативні сценарії чужої дозволеної категорії M22/неправильної ancestry не змінюють БД.
- Deprecation: чинний `config/database.php:62`, `PDO::MYSQL_ATTR_SSL_CA` у PHP 8.5; рекомендований `Pdo\Mysql::ATTR_SSL_CA`. Сторонню конфігурацію в M22 не змінено.
- Ранню синтаксичну помилку нового тесту виправлено до фінального прогону; одиничний запуск із неточним filter не виконав тестів і не зарахований.

## Реальні HTTP, браузер і стилізація

Основний before: **2026-09-28 11:48:53 UTC**, after: **12:02:39 UTC**. Усі **18 URL** before/after — HTTP 200: три теорії, три основні тести, курсова копія, два контролі, вісім контекстних сторінок і sitemap.

Реальний Chromium **147.0.7727.15**:

- **6/6 основних сценаріїв:** 3 уроки × desktop 1440×1000 / mobile 390×844; кожний у світлій і темній темі.
- На кожній сторінці: 8 rich-секцій, 6 завдань, 6 ключів; rich examples відповідно 29 / 31 / 23. Не було plain fallback.
- Новий матеріал є в початковій HTTP-відповіді, зберігається після reload і в новому browser context. DOM/fixtures не підставлялися.
- Details відкриваються й закриваються мишею, Enter і Space. Перевірено нижні секції, нумерацію, переклади, довгі ключі.
- Document overflow відсутній. Мобільна таблиця: видима область 322 px, content width 1000 px, фактичний scrollLeft 678 px. Чотири колонки по 250 px залишаються читабельними замість стискання до кількох літер.
- Переглянуто screenshots усіх трьох мобільних таблиць зліва і справа, довгої collocation-нотатки, українських перекладів, відкритих ключів і нижніх секцій; додатково знято 18 детальних кадрів. У перевірених кадрах текст не перекривається й не обрізається поза локальним scroll.
- Мінімальне виміряне співвідношення контрасту вибірки — 6.295:1; це не повний accessibility-аудит.
- Три штатні переходи відкрили відповідні основні тести; відповіді не надсилалися. Три автоматичні state POST навмисно блоковані read-only runner і відділені від application failures.
- **0 JavaScript page errors.** Google Fonts дали `net::ERR_NETWORK_ACCESS_DENIED`; перевірена фактична fallback-типографіка, не заявлено успішного завантаження зовнішніх шрифтів.
- [Курсова копія C1](http://gramlyze.loc/courses/english-grammar-theory/lesson/articles-and-quantifiers/advanced-article-and-quantifier-nuance): HTTP 200, правильний H1, у server HTML 6 завдань/6 ключів; контент прихований чинним gate, `contentVisible=false`, `gateBypassed=false`. Не заявлено повного візуального проходження закритого уроку.
- Контролі M21 Complex Relative Clauses і M16 Nominalisation and Formal Register: **4/4 desktop/mobile**, обидві теми, без overflow/page errors. Контрольна курсова копія також без обходу gate.
- Окремі GET трьох теорій, трьох тестів і курсу: **7/7 HTTP 200, без PHP warnings у відповідях**.

### Динамічний HTML сусідів

Перше after-порівняння виявило різний повний main.textContent на чотирьох старих базових сторінках. Причина — вбудований script із випадково підібраними practice questions, не зміна уроку.

Два додаткові GET кожної з чотирьох сторінок показали: full hash змінюється, видимий текст після вилучення script із діагностичної копії — однаковий. Порівняння не отримало безумовного винятку: M22 runner дозволяє цей конкретний випадок лише з незмінними before/after fingerprints захищених таблиць, .env і попередніх sources. Додано негативний Node-тест, що відхиляє змінений банк/.env/старий урок. Інші контрольні сторінки досі проходять строге текстове порівняння. Фінальне порівняння пройшло.

## Метадані, sitemap і захищені дані

- Page.title, controller H1 (включно з capitalization), HTML title, canonical, robots, Content-Type, test href, IDs/UUIDs, locale, рівні, taxonomy, tags/pivots і зв’язки збережені.
- Локальний `X-Robots-Tag: noindex, nofollow, noarchive` залишено. Відсутність окремого meta robots не підміняється твердженням про індексацію.
- Змінилися лише descriptions трьох теорій через чинний PageMetadata. Description = OG description = Twitter description; назва не повторюється двічі:
  - `Advanced Article and Quantifier Nuance. Розрізняй злічуваність, кількість і оцінку достатності.`
  - `Precision With Articles And Determiners. Керуй відсиланням у зв’язному тексті: відрізняй загальний клас від визначеної групи, добирай означники та зберігай точний обсяг заперечення.`
  - `Advanced Collocation And Lexical Choice. Добирай природні дієслівно-іменникові й прикметникові сполучення.`
- Ordered sitemap фактично містив **554 URL** до і після; кількість не hardcoded. Ordered SHA-256: `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`.
- **46 таблиць** пройшли стабільне порівняння row counts + повних row fingerprints, виключивши лише погоджені поля 12 цільових записів. Банки questions/answers/options/verb_hint, saved tests і progress не змінені.
- **33 прийняті уроки M11–M21** залишилися source/DB-identical; їхні versioned файли й контракти не входять у diff.
- .env та початковий routes/api.php — байтово незмінні.

## Обмеження й завершення

Cambridge 403 / індексовані фрагменти, зовнішні шрифти, закритий курс і PHP deprecation описані вище за фактичними результатами. Не запускалися full repository suite, M10, full crawl, Lighthouse, performance-кампанія або build: build inputs не змінювалися. Редакторська перевірка не є незалежною CEFR-сертифікацією.

До M22 commit належать лише три definitions, вузький manifest, wrappers/локальний CLI adapter, цільові PHP/Node тести, read-only діагностичний runner і цей звіт. Private proofs/backups, .env, vendor/build, caches і сторонні зміни виключено. Звичайний push — тільки в погоджену робочу гілку; остаточний SHA і збіг remote повідомляються в підсумку задачі.

**Production не перевірявся й не змінювався.**
