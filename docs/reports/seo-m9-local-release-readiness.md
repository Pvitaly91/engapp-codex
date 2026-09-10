# M9 — завершення локального SEO-пакета

Дата: 2026-09-10. Проєкт: Gramlyze. Реальні запити — тільки `http://gramlyze.loc`.

## Самері для власника

Пакет M1–M8.1 присутній у поточній історії повністю. Основні локальні контракти пройшли зведене приймання: **249 PHP-тестів / 4 112 assertions**, frontend build із npm lockfile, **30 Vitest-тестів**, **15 Node-тестів**, **7 Python-тестів** та чотири браузерні сценарії на desktop/mobile. Ці числа належать новим запускам M9, а не сумі історичних звітів.

Локальні навчальні дані вже відповідають кінцевим джерелам: 9 блоків One/Ones і 10 Reciprocal; службових вставок у Passive Voice немає. M9 не застосовував ремонти до робочої БД. На приватних fixtures виявлено й мінімально виправлено приймання змішаного старого/нового стану: тепер обидва pronoun-механізми відмовляють без записів.

**Повну відтворюваність release-пакета не підтверджено.** `composer.lock` не versioned, ігнорується Git, відрізняється від встановленого `vendor` у 38 зі 117 версій і не проходить platform-check на фактичному PHP 8.2.12. Це підтверджений невирішений блокер готової інструкції встановлення PHP-залежностей, не причина оголошувати зелені локальні тести недійсними. Потрібна окрема погоджена сумісна dependency-база з committed lockfile та перевіреним чистим встановленням. Поточний lock не додано примусово, залежності не оновлено.

Друга окрема передумова майбутнього перенесення — підтримуваний спосіб цільового оновлення даних в іншому середовищі: чинні repair-команди локальні. Нижче наведено план, **не дозвіл на його виконання**. Production SHA не підтверджений; `.com` і `.ub` не перевірялися й не оновлювалися. Звіт не доводить виправлення production або покращення індексації Google.

## 1. База, історія та межі змін

Прочитано `AGENTS.md` та 13 звітів `docs/reports/seo-m1-local-fixes.md` … `seo-m8-1-one-ones-editorial.md`, зокрема окремі M3.1/M3.2/M4.1/M4.2. Фактична початкова база після `git fetch origin`:

- робоча M8.1: `codex/seo-m8-1-one-ones-editorial`;
- HEAD та origin/M8.1: `94fc88eb9fe5552e888a4cb582a2226b4b585900`;
- origin/main: `c77b4326a92b2c1e92c80b07393d8e7000c0fe33`;
- нова робоча гілка: `codex/seo-m9-local-release-readiness`, створена від фактичного HEAD, не від main;
- повторна read-only звірка `git ls-remote origin` перед commit підтвердила ті самі main/M8.1; remote M9 ще не існував.

Усі наведені коміти перевірено `git merge-base --is-ancestor <SHA> HEAD` — exit 0. Merge/cherry-pick/reset не виконувалися.

| Етап | Commit | Ancestry | Прийнятий результат | Окрема дія для даних |
| --- | --- | --- | --- | --- |
| M1 | `d123a73800ded20ceca3ef6476597787fb153bd0` | є | HTML/JSON SEO-ідентичність Questions, course canonical, безпечний inline HTML; точковий Passive repair | Так, лише за точної debug-сигнатури |
| M2 | `b58c2769b96ad19846da1050d48e32ada6bf5ed0` | є | Ізоляція середовища та збереження стану тесту | Ні |
| M3 | `7c649117e747fb922da166c2e1fba59474fee203` | є | Публічні Vite assets, scoped Tailwind, один Alpine від Livewire | Ні; потрібна доставка assets |
| M3.1 | `54d34083a67e30084b967b572cc5a6b93c2eaf99` | є | Стабільність sidebar, захищений smoke runner | Ні; code/assets |
| M3.2 | `7938eebb3356dc6747cd4ed19e88ffb3bc16787d` | є | Локальне mobile/performance-приймання та інструменти | Ні |
| M4 | `9b8a16872fcc4b1d9d0d3a30cf8af788ccf16c6c` | є | Sitemap лише готових основних навчальних сторінок, без ненадійного lastmod | Ні |
| M4.1 | `4fdeb75b66440a5d46cdc65fbd32b4f7001d0932` | є | Швидкий request-local відбір sitemap, course metadata | Ні |
| M5 | `3a7c26ecda1cc4ae4f727b2a378d4e19aa51cc70` | є | Повні заголовки, описи, екранування social metadata | Ні |
| M4.2 | `63002109cd075610818926e2429f65345a4f918a` | є | Driver-aware квотування `question_options.option` | Ні |
| M6 | `e6ff1cb6f7158bdd2356c8794cfc5596b63effd8` | є | Legacy redirects із мовою/query, реальні 404 невідомих курсів | Ні |
| M7 | `fd4ad5d1eec74fb16f320bdf07649333b1ab17b5` | є | 43 українські редакційні описи за стабільною ідентичністю джерела | Ні, code-only |
| M8 | `be84d48e3071d6988c381f8020c89672e84938c4` | є | Нормалізовані definitions двох уроків; точкове відновлення 19 блоків | Так, лише зі старого порожнього стану |
| M8.1 | `94fc88eb9fe5552e888a4cb582a2226b4b585900` | є | Редакція п'яти body One/Ones та контрольований manifest patch | Так, лише з погодженого непорожнього M8 |

M4.2 фактично йде після M5; таблиця не припускає арифметичного порядку номерів. Відсутніх етапів не знайдено.

Початкові сторонні зміни збережені: два PPC audit-файли у `storage/framework/testing`, `.codex/`, PPC/deployment-архіви та старі приватні evidence. Вони не належать M9 й не включаються в commit. `.env`, APP_KEY, конфігурація Apache/PHP/hosts, schema/індекси, питання/відповіді/UUID/рівні/verb_hint, локалізація, метадані та навчальні джерела не змінювалися. Робочі caches не очищувалися.

Новий runtime diff — тільки 8 рядків перевірок змішаного стану у `app/Services/PronounContentRepair.php` і `app/Services/OneOnesEditorialPatch.php`. Решта M9 — regression-тести, обмежені діагностичні інструменти, ignore для приватного evidence та цей звіт.

## 2. Відтворюваність коду, залежностей та assets

### Frontend — новий source-only PASS

Виконано один build:

```powershell
node tools/diagnostics/verify-public-build.cjs m9-source-only-build
```

Наявний helper копіює явні build-inputs у приватний каталог без робочого `.env`, БД, `vendor` та caches. Усередині: `npm ci --no-audit --no-fund`, потім `npm run build`; обидва exit 0. Node 22.15.0, Vite 5.4.19, 192 npm packages, 55 modules. Інтервал 19:29:56–19:32:38 UTC. Inputs: package/lock, Vite/PostCSS/Tailwind configs, `tools/build`, `resources`, `app/Support`, versioned `Page_V3`/`V3` definitions. M9 не змінював цих inputs.

| Manifest entry | Файл source-only build | Bytes | SHA-256 |
| --- | --- | ---: | --- |
| `resources/css/catalog-public.css` | `assets/catalog-public-DkOvOqwd.css` | 96944 | `58b449fa028e356005e026c71c65358be8817704196c9517d30456579be9c92a` |
| `resources/js/catalog-public.js` | `assets/catalog-public-CzFNw-3Z.js` | 9531 | `7cdbd9ecbb6dc5b50abb3dc4e4cc740c43dcfc4eb00b8aebb6ac5e08b3670487` |
| `resources/js/app.js` | `assets/app-DNxiirP_.js` | 35320 | `a808a35c964a3f1599dca9663ef161deca27975f35ff26c761ad39a9cfef314d` |
| `resources/css/app.css` | `assets/app-DiDIcLPd.css` | 134116 | `9acbd9ac1cd2c0e6f4e402547a970f710d6c5b6c7508fc51eb244f000df68935` |

Робочий manifest має всі потрібні файли; публічні CSS/JS та app JS збігаються із source-only build. **Legacy app CSS не byte-identical**: робочий `app-DicKWADZ.css` відрізняється через широку Tailwind autodiscovery іншого workspace; це відоме обмеження старого entry, не приховано під твердженням «усі assets ідентичні». Публічний entry має явне scoped сканування й відтворився точно. Робочий build не перезаписано.

`public/hot` відсутній. HTML/браузер використовують локальні built public CSS/JS (200), а не HMR, Tailwind Play CDN чи Alpine з unpkg. Один `alpine:init`, Alpine 3.15.11 від Livewire. Build warnings: застарілі caniuse-lite дані та deprecated whatwg-encoding; `latest`/оновлення lockfile не запускались.

`public/build` ignored: не force-add. Для майбутнього перенесення потрібен **єдиний узгоджений artifact manifest + усі referenced assets**, зібраний із обраного revision/lockfile, або окремо погоджена збірка у цільовому середовищі. Не можна переносити лише manifest чи поєднувати його з файлами іншої збірки.

### PHP — підтверджена невирішена передумова

Перевірки без scripts/plugins та без install/update:

```powershell
git ls-files composer.lock package-lock.json
git check-ignore -v composer.lock
php C:/ProgramData/ComposerSetup/bin/composer.phar --no-plugins --no-scripts validate --no-check-publish
php C:/ProgramData/ComposerSetup/bin/composer.phar --no-plugins --no-scripts check-platform-reqs --lock --no-dev
```

`package-lock.json` versioned, `composer.lock` — ні (`.gitignore`). Composer validate exit 0 означає валідний формат, **не** придатність до встановлення. Local lock SHA-256: `c06f976273101f3a0cab86604a56843720655591b4176905bde56941f545dc90`.

Порівняння lock та `vendor/composer/installed.json`: 117/117 пакетів, **38 відмінних версій**. Приклади lock → установлене: Livewire 3.8.0 → 3.7.15, guzzle 7.10.6 → 7.10.0, symfony/css-selector 8.1.0 → 7.4.8. Framework 10.50.2 / PHPUnit 10.5.63 збігаються.

Platform-check exit 1: `symfony/css-selector v8.1.0` вимагає PHP `>=8.4.1`; фактичний runtime — **PHP 8.2.12**. Поточні тести виконані зі встановленим vendor, а не з чистим install цього lockfile. Без committed сумісного lockfile clone не гарантує повторення перевіреної PHP-бази; `composer install` без lock вирішуватиме залежності заново. CI workflows також налаштовані на PHP 8.2, але CI в M9 не запускали.

Не застосовано `--ignore-platform-reqs`, force-add lock, composer update чи перевстановлення робочого vendor. Наступна окрема дія: погодити PHP/dependency baseline, отримати відповідний versioned lock, виконати приватний чистий install і цільове приймання. Наявні Composer scripts (`package:discover`, post-update publish) потребують свідомого вибору середовища; не запускати їх сліпо у робочій/production-копії.

Runtime джерела M8: два versioned `database/seeders/Page_V3/PronounsDemonstratives/PronounsDemonstratives{OneOnes,ReciprocalPronouns}TheorySeeder/definition.json`; M8.1 manifest: `database/content-patches/one-ones-m8-1.json`. Локальні backups/evidence не є runtime dependencies. Перевірені механізми використовують `base_path`/`database_path`, а не прив'язку до старого `D:/...`. Project Pack не замінює чинні configs/lockfiles/цей звіт.

## 3. Нове автоматичне приймання

### Ізоляція й точна PHP-команда

```powershell
$python = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe'
$php = 'C:/Program Files/xampp/php/php.exe'
$suite = @(
  'tests/Feature/SmokeIsolationTest.php',
  'tests/Unit/ThreadSafeEnvironmentTest.php',
  'tests/Feature/ResolvedLearningPageSeoTest.php',
  'tests/Feature/SeoRobotsTest.php',
  'tests/Feature/CanonicalUrlTest.php',
  'tests/Feature/TheoryCanonicalLessonUrlTest.php',
  'tests/Unit/TheoryInlineHtmlTest.php',
  'tests/Feature/TheoryInlineHtmlRenderingTest.php',
  'tests/Unit/PageMetadataTest.php',
  'tests/Unit/TheoryEditorialDescriptionsTest.php',
  'tests/Feature/PageLocaleContentTest.php',
  'tests/Feature/SitemapTest.php',
  'tests/Feature/MainTheoryTestSitemapReadinessTest.php',
  'tests/Feature/CourseSitemapMetadataTest.php',
  'tests/Feature/LegacyPublicRedirectTest.php',
  'tests/Feature/SavedTestJsStateTest.php',
  'tests/Unit/SavedTestJsStateTest.php',
  'tests/Unit/SavedTestJsStateSynonymsTest.php',
  'tests/Unit/PassiveVoiceDebugContentRepairTest.php',
  'tests/Feature/PronounContentRepairTest.php',
  'tests/Feature/OneOnesEditorialPatchTest.php'
)
& $python -B tools/diagnostics/run-isolated-tests.py --php $php --label m9-consolidated @suite
```

**PASS: 249 tests, 4 112 assertions; 83.135 s PHP, 84 MiB; runner exit 0.** Один явний список із 21 suite без повторення за кожний історичний етап. PHP 8.2.12 / PHPUnit 10.5.63. Preflight: `testing`, фактичний PDO SQLite `:memory:`, array cache/session, приватні compiled views/storage/exports/bootstrap, робочий `.env` не завантажено, тимчасовий ключ, CLI OPcache вимкнений приватною конфігурацією. Production-профіль — тільки синтетичний Host в ізольованому Laravel kernel; зовнішніх production-запитів немає.

Protected guard завершився **19:37:41 UTC до першого HTTP-набору о 19:40**. Фактично захищено **46 739 файлів**, змінених **0**. Повний fingerprint до/після однаковий:
`00cc38fe5731920c4cef2b8d1a35b5e11a705e08519dbe862e88b756ce8efe04`.
Файли після тесту не «відновлювалися» замість ізоляції. Звичайні guest HTTP/browser-запити виконані вже після guard і можуть створювати власні sessions/views; guard не оголошується доказом відсутності цих штатних runtime-записів.

### Контракт → реально виконані тести

Шляхи suites — у команді вище; назви нижче не позначають додаткові запуски.

| Контракт | Функціональне покриття |
| --- | --- |
| HTML `/questions`, повний canonical, robots | `ResolvedLearningPageSeoTest::test_both_questions_topics_render_indexable_complete_html_for_html_and_wildcard_accept`; development robots, spoofed identity, source cleanup у тому ж suite; `SeoRobotsTest`, `CanonicalUrlTest` |
| Реальний JSON endpoint, дані й technical noindex | `ResolvedLearningPageSeoTest::test_accept_switching_and_real_json_endpoint_preserve_representation_and_bank`; `test_query_input_cannot_spoof_resolved_html_identity_or_remove_api_noindex` |
| Course → theory canonical, nested mapping, UI route | `ResolvedLearningPageSeoTest::test_course_copies_map_to_the_resolved_theory_pages_including_nested_categories`; `test_course_home_and_lesson_test_keep_their_own_canonicals_and_unknown_lesson_has_none`; `TheoryCanonicalLessonUrlTest`; native browser course |
| Safe inline renderer | `TheoryInlineHtmlTest`, `TheoryInlineHtmlRenderingTest` — дозволений inline, encoded markup, небезпечні атрибути/теги |
| M5 повні metadata та escaping | `PageMetadataTest` і metadata cases `ResolvedLearningPageSeoTest` — title/description/H1, social attributes, 2 000 formatter calls без SQL |
| 43 M7 описи | `TheoryEditorialDescriptionsTest::test_each_registered_identity_uses_its_exact_copy_without_changing_title` (43 cases); `ResolvedLearningPageSeoTest::test_every_editorial_description_reaches_one_meta_and_both_social_tags_only`; `PageLocaleContentTest` та locale/priority/query cases |
| Sitemap selection/order/origin/no lastmod | `SitemapTest::test_xml_is_utf8_unique_stable_and_uses_configured_origin_not_guest_input`; `test_lastmod_is_omitted_when_parent_dates_do_not_cover_content_edits_and_deletions`; `MainTheoryTestSitemapReadinessTest`, `CourseSitemapMetadataTest` — actual usable pool, холодний прямий тест і sitemap→test, доступні/закриті курси |
| M4.2 `option` | Новий `MainTheoryTestSitemapReadinessTest::test_m9_usable_builder_quotes_reserved_option_with_each_driver_grammar`: реальний builder компілюється через SQLite/MySQL grammars; suite виконує SQLite SQL. Це НЕ новий запуск MySQL-сервера |
| Legacy locale/query та gate | `LegacyPublicRedirectTest::test_catalog_aliases_keep_temporary_status_but_use_the_requested_locale`; `test_alias_query_values_survive_once_without_controlling_the_destination`; `test_production_locale_catalog_chain_is_bounded_and_keeps_the_gate_closed` |
| Unknown course 404 | `LegacyPublicRedirectTest::test_unknown_or_empty_courses_and_theory_paths_remain_real_404s` + локальний GET 404 |
| Questions persistence | `SavedTestJsStateTest` Feature: newest snapshot/test/mode і bodyless 204; Unit state/synonyms; Node queue/snapshot tests; browser незалежне server-only відновлення |
| M1/M8/M8.1 переходи | `PassiveVoiceDebugContentRepairTest`, `PronounContentRepairTest`, `OneOnesEditorialPatchTest`; стани та відмови — у §5 |
| Захист робочого runtime | `SmokeIsolationTest`, `ThreadSafeEnvironmentTest` + фактичні preflight/fingerprints protected runner |

Історична M4.2-матриця з `seo-m4-2-mysql-compatibility.md`: MySQL **9.4.0** — disposable fixture PASS (35 tests / 390 assertions), MariaDB **12.0.2** — локальна read-only HTTP перевірка, SQLite **3.39.2** — PASS. **MySQL 8.4.11 не був перевірений**; версія у старому deploy-log не є прийманням. Повний цикл СУБД в M9 не повторювали, SQL-path не змінено. Фактична поточна production СУБД невідома.

### Новий дефект: перша помилка → мінімальне виправлення

Перед зведеним запуском виконано тільки зачеплений suite для red proof:

```powershell
& $python -B tools/diagnostics/run-isolated-tests.py --php $php --label m9-mixed-red tests/Feature/OneOnesEditorialPatchTest.php
```

Збережений початковий результат: **21 tests / 286 assertions, 4 failures**, очікувався conflict, але операція дозволяла план. Чотири нові fixtures: один відновлений блок із 19; лише відновлений One/Ones із двох уроків; один або чотири відредаговані body із п'яти M8.1. Guard і тоді: 46 739 / 0 змін.

Мінімальні перевірки в двох сервісах відхиляють непорожній частковий набір changes. `test_m9_mixed_editions_refuse_a_new_plan_without_writes` перевіряє і новий preview, і застосування старого погодженого plan: без DB-змін, нового plan-файлу чи backup. У зведеному зеленому запуску всі чотири cases пройшли. Force/обхід hashes/поновлення невідомого стану не додані.

### JS та перевірки діагностичних інструментів

```powershell
npm test
node --test tests/Browser/seo-m9-browser.test.cjs tests/Browser/state-request-classification.test.cjs tests/Browser/saved-test-persistence.test.cjs
& $python -B -m unittest tests/diagnostics/test_seo_m9_readiness.py
php -l tools/diagnostics/seo-m9-data-state.php
```

Нові завершені результати: Vitest **5 files / 30 tests PASS**, 142.07 s (зокрема компіляційні public CSS fixtures, це не новий performance-бенчмарк); Node **15 PASS**; Python **7 PASS**; PHP helper syntax PASS. Повторні короткі self-tests після корекцій діагностичних selectors/robots не додаються до числа унікальних тестів. Guard-тести блокують production до навігації, сторонні write/navigation/redirect; дозволений лише власний Questions state POST. Python перевіряє повний ordered sitemap, унікальний реальний course mapping, timeout/empty HTML, robots-конфлікти й справжні Passive debug-сигнатури.

## 4. Обмежене HTTP та browser-приймання

### GET

```powershell
& $python -B tools/diagnostics/seo-m9-http.py --label acceptance-v1 --baseline storage/app/seo-m8-1-local/after-http.json
& $python -B tools/diagnostics/seo-m9-http.py --label accepted-v2 --reassess storage/app/seo-m9-local/acceptance-v1-http.json --recheck-timeout
php -d opcache.enable_cli=0 tools/diagnostics/seo-m9-data-state.php --local-read-only acceptance-v1
```

14 контрольних GET + один sitemap, **один** явний повтор лише timeout-категорії; загалом 16 HTTP-запитів цього CLI-набору. Без cookie/auth/Referer/proxy, автоматичних redirect/retry. Course path отримано з фактичної навігації; definition paths — з mappings джерел, не скорочені здогадки. Редирект alias збережено як окрему відповідь, його дозволений local destination перевірено окремим GET.

| Path на `http://gramlyze.loc` | Статус | Мс | Результат |
| --- | ---: | ---: | --- |
| `/` | 200 | 281 | HTML metadata/content PASS |
| `/theory` | 200 | 344 | HTML metadata/content PASS |
| `/theory/future-perfect` | 200 | 546 | Цільовий follow-up після первинного timeout 45 031 мс |
| `/theory/basic-grammar/sentence-types` | 200 | 1750 | Звичайний урок, контент/H1 |
| `/test/future-perfect/questions` | 200 | 4781 | Навчальний HTML, canonical з кінцевим `/questions` |
| `/test/future-perfect/questions/questions` | 200 | 2532 | JSON, 84 питання, technical noindex |
| `/courses/english-grammar-theory` | 200 | 26016 | Курсовий інтерфейс і реальний lesson mapping |
| `/theory/zaimennyky-ta-vkazivni-slova/one-ones` | 200 | 1266 | 9/9 body точно з поточного source |
| `/courses/english-grammar-theory/lesson/zaimennyky-ta-vkazivni-slova/one-ones` | 200 | 719 | Ті ж 9/9 body, canonical на відповідну теорію |
| `/theory/zaimennyky-ta-vkazivni-slova/reciprocal-pronouns-each-other-one-another` | 200 | 734 | 10/10 body точно з source |
| `/theory/passive-voice/theory-passive-voice-formation-rules` | 200 | 734 | Навчальний HTML; точний clean-state окремо read-only PDO |
| `/tests/cards` | 302 | 187 | Location `http://gramlyze.loc/catalog/tests-cards`; призначений temporary alias |
| `/catalog/tests-cards` | 200 | 4516 | Дозволений destination |
| `/courses/polyglot-english-a9` | 404 | 1563 | Справжній unknown course, без canonical, noindex |
| `/sitemap.xml` | 200 | 3312 | XML UTF-8, 82 263 bytes, повний ordered diff PASS |

Для 200 HTML підтверджено Content-Type, по одному непорожньому title/description/H1, основний контент, очікуваний canonical. SEO-origin canonical — `https://gramlyze.com`, без переходу туди. Development **X-Robots-Tag: noindex, nofollow, noarchive** збережений; окремий meta robots на цих HTML відсутній, що не скасовує HTTP-header.

Початковий новий helper помилково вимагав meta robots додатково до правильного header. Початковий JSON зі всіма відповідями та єдиним TimeoutError збережено; критерій інструмента виправлено й результати переоцінено **офлайн**, крім одного дозволеного повтору категорії. Це не другий повний GET-набір. Сирі запити не підмінялись локальними PHPUnit-результатами. Одноразовий course GET 26 с записаний, але не оголошений доведеною performance-регресією; оптимізацій/cache-clear не було.

Sitemap: повна послідовність **554 loc** однакова з M8.1, added=0, removed=0, lastmod=0. 554 — результат порівняння evidence, не hardcode відбору чи runtime. SHA-256 XML body: `9946ce106ba32cb9fe11655e0a03a76409cb0e3b3c411b78fdcf4b6266972885`. Повного crawl 554 адрес не було.

Read-only helper окремо підключився лише до фактичної локальної MariaDB 12.0.2 після звірки DB/hostname, в read-only transaction з rollback. One/Ones 9/9 та Reciprocal 10/10 збігаються за UUID/order/type/heading/body і Page identity; Passive має 28 блоків, 0 справжніх debug-маркерів і canonical title. Первинний HTML helper перевіряв не ті debug-фрази; достовірний clean-state тут спирається на PDO-перевірку справжніх англомовних сигнатур. Helper виправлено й покрито unit fixture без нового live GET. План/apply/restore/seeder в робочій БД не викликалися. Історичні повні DB-хеші не видаються за нову M9-перевірку всіх таблиць.

### Browser: чотири сімейства × два viewport

```powershell
$env:PLAYWRIGHT_MODULE = 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:CHROMIUM_EXECUTABLE = 'C:/Users/admin/AppData/Local/ms-playwright/chromium_headless_shell-1217/chrome-headless-shell-win64/chrome-headless-shell.exe'
node tools/diagnostics/seo-m9-browser.cjs acceptance-v1 storage/app/seo-m9-local/accepted-v2-http.json
node tools/diagnostics/seo-m9-browser.cjs followup-v2 storage/app/seo-m9-local/accepted-v2-http.json --questions-course-only
```

Chromium 147.0.7727.15; desktop 1440×1000, mobile 390×844, uk-UA; окремі тимчасові guest contexts, без storageState-файлів, service workers blocked. Production guard встановлено до першої навігації на рівні context routes та CDP. Звичайні зовнішні шрифти дозволено, production/сторонню навігацію та непогоджені writes — ні.

| Сценарій | Desktop/mobile | Доказ |
| --- | --- | --- |
| Теорія | PASS / PASS у першому запуску | 295 sidebar links, current-page marker, desktop collapse/mobile menu, theme toggle + reload |
| Questions | PASS / PASS у цільовому follow-up | 84 питання, відповідь 0→1 correct, resolved 204 saves; reload exact state; видалення лише власного local/session storage, повернення і незалежний server bootstrap = UI snapshot |
| Курс | PASS / PASS у цільовому follow-up | Native store лише у власному localStorage: locked→current; click фактичного відносного lesson href; course UI і canonical theory; без глобального unlock чи course POST |
| One/Ones | PASS / PASS у першому запуску | Видимі blocks 2/5/7/8/9 з точним нормалізованим DOM-текстом та li-count фінального definition; exercises і правила |

Не приховано перші помилки: initial browser **4/8 PASS**. У Questions збереження вже працювало, але загальний новий helper використав strict locator `main` за наявності двох main; виправлено `.first()` з окремим count. Course helper шукав абсолютний href, а реальне посилання відносне; тепер selector обирає фактичний resolved pathname. Це дефекти діагностичних selectors, не нові runtime-виправлення. Повторено тільки чотири зачеплені cases: **4/4 PASS**. Разом прийнято 8 запланованих сценаріїв, не «12 унікальних».

Фактичний Questions endpoint береться з `JS_TEST_PERSISTENCE` і викликається як `/test/future-perfect/questions/questions?mode=saved-test-js-v2`: JSON 200/84/noindex. Записано тільки sanitized URL без query values, кількості й hashes стану, не cookie/CSRF/відповіді. Raw `POST 204 / net::ERR_ABORTED` лишено в evidence (по 4 у прийнятому Questions-сценарії); класифікація успіху спирається на resolved fetch, 204 і незалежне server-only відновлення, а не лише статус.

Звичайні assets — 200, Alpine один, немає горизонтального overflow чи application pageerrors/неочікуваних HTTP failures/production attempts. **Google Fonts CSS: `net::ERR_NETWORK_ACCESS_DENIED`**; це окреме обмеження, не прихований PASS оригінальних шрифтів. Візуально оглянуто screenshots mobile theory, mobile Questions, desktop course exercises і desktop One/Ones rules. Screenshot Questions показує верхню частину сторінки; роботу відповідей доводить state-сценарій, не цей кадр. Діагностичні поля relative href та console event type після запуску виправлено без повторного browsing; первинні JSON не переписані.

### Приватні докази (не входять до commit)

- `storage/app/seo-m3-local/m9-source-only-build.json` та приватна build-копія;
- `storage/app/seo-m2-local/m9-mixed-red-4167c22276744fc0942a03cb24d6258c-result.json` — перші 4 failures;
- `storage/app/seo-m2-local/m9-consolidated-0f5ef7ff3d66417b9a850378902ec370-result.json` і JUnit у `test-runtime-0f5ef7ff3d66417b9a850378902ec370` — PHP/preflight/guard;
- `storage/app/seo-m9-local/acceptance-v1-http.json`, `accepted-v2-http.json`, `acceptance-v1-data.json`;
- `storage/app/seo-m9-local/acceptance-v1-browser.json`, `followup-v2-browser.json`, однойменні viewport screenshots.

Нові wrappers не містять deploy/apply/restore дій. HTTP wrapper читає попередній sitemap лише як необов'язкову runtime-independent базу порівняння діагностики; для нового приймання потрібен новий label, щоб не перезаписати докази. Приватні файли не є input застосунку чи release-артефактом.

## 5. Таблиця переходів даних — лише приватні fixtures M9

Нижче фактичні сигнатури наявних **локальних** CLI, не серверна інструкція. M8/M8.1 preview — default, прапорця `--dry-run` у них немає. `--plan`/`--backup` — прості нові JSON basenames у відповідному приватному каталозі.

```text
seo:repair-passive-voice-debug [--dry-run] [--apply] [--database=] [--backup=] [--restore=]
content:repair-pronoun-blocks --plan=<name.json> [--apply --database=<exact-local-db> --backup=<new.json>]
content:patch-one-ones-m8-1 --plan=<name.json> [--apply --database=<exact-local-db> --backup=<new.json>]
```

| Стан / передумови | Механізм і очікувані зміни | Backup і перевірка | Безпечна відмова |
| --- | --- | --- | --- |
| А. Обидва pronoun-уроки: усі 19 погоджених legacy layout-блоків порожні, identity/source точні | Чинний `PronounContentRepair`: 19 нормалізованих блоків **одразу з кінцевим M8.1 текстом**; подальший `OneOnesEditorialPatch` — no-op | Новий exclusive M8 backup перед writes; повний projected snapshot, source-equivalence, idempotence; `OneOnesEditorialPatchTest` legacy→current→noop, `PronounContentRepairTest` fresh import parity | Manual/nonempty body, mixed 1–18 блоків, інший source/hash, відсутня/неоднозначна Page/UUID, stale plan |
| Б. Погоджений непорожній M8 до редакції, всі 5 before-body точні | Тільки `OneOnesEditorialPatch`: body orders **2,5,7,8,9**; M8 repair відмовляє й не перетворюється на editor nonempty text | Новий M8.1 backup, manifest/source hashes, усі незмінювані поля/інші блоки/locales/links в snapshot; existing transition tests PASS | Будь-який невідомий body, 1–4 already-new body, змінений manifest/source, stale snapshot |
| В. Усі блоки вже current | Відповідний M8/M8.1 механізм — no-op; Passive clean — no-op | Існуючі idempotence cases; нуль changes, немає потреби у новому record backup для фактичних writes | Невідомі сторонні зміни не трактуються як clean |
| Г. Ручний/змішаний/неповний/неоднозначний стан | Conflict, не автоматичне «дозавершення»; чотири нові mixed cases + наявні manual/missing/duplicate/stale tests | Snapshot незмінний; новий preview/backup не створюється при mixed refusal; transaction rollback на помилці | Немає force, підміни hashes, повторного масового пересівання |
| Д. Passive Voice з точними M1 debug-сигнатурами | `PassiveVoiceDebugContentRepair`: тільки погоджені UUID/locale/body/owner/order/type/поля та за потреби відомі Page title/seeder; навчальні записи лишаються | Новий record backup до writes; exact clean/leftover/debug/manual/link/restore fixture cases | Інший текст/heading/level/category/title, question/junction/unknown FK dependencies, UUID reuse, existing backup або змінений post-state |

M8/M8.1 CLI допускають лише `APP_ENV=local/testing`. Сервіси перевіряють локальну connection/фактичний PDO driver і точну DB для MySQL writes, забороняють remote/read-write split та stale identities. Passive також має локальний DB guard. SQLite `:memory:` використовувався лише для приватних fixtures. Обмеження не знято; не рекомендується APP_ENV/host/tunnel-підміна для production.

При застосуванні після належного дозволу завжди потрібен **новий preview і backup саме цільової БД**, під точну перевірену source revision. Старий локальний plan/backup не переноситься як дозвіл запису в іншу БД. Існуючі механізми не утворюють готовий production content-manager; підтримуваний міжсередовищний спосіб і його відкат мають бути окремо реалізовані/перевірені, якщо справді потрібні зміни даних.

## 6. Майбутнє перенесення — план, не виконання

1. **Встановити ціль і стан.** За окремого дозволу прочитати фактичний серверний release SHA, dirty state, PHP/розширення, MySQL/MariaDB версію, DB identity, asset manifest. Не припускати, що main або local HEAD уже розгорнуті. Не переносити localhost `.env`, APP_KEY чи credentials. Зберегти поточні серверні availability/locale/Coming Soon правила.
2. **Закрити dependency-передумову.** Погодити сумісний versioned Composer lock і перевірений vendor; окремо підтвердити platform requirements на цілі. Нинішній local lock з PHP >=8.4.1 не є готовим рішенням для PHP 8.2.12. SQL matrix M4.2 — контекст, не сертифікація невідомої серверної версії.
3. **Код та assets.** Обрати точний перевірений M9 revision із всіма ancestors, доставити code + узгоджений vendor + manifest/assets. `npm ci`/build лише за lock у підготовленому середовищі. Стару робочу release-копію та її manifest/assets зберегти для code rollback. Не переносити runtime caches/public hot/private evidence/.codex/backups. Будь-які майбутні server cache дії погоджуються окремо; у M9 їх немає.
4. **Налаштування без секретів у Git.** Перевірити цільові `APP_ENV`, `APP_DEBUG`, `APP_URL`, `SITE_PRODUCTION_DOMAINS`, `SITE_PRODUCTION_ORIGIN`, `SITE_DEVELOPMENT_ORIGIN`, `SITE_PRODUCTION_LOCALES`; їхню взаємодію з host-aware конфігурацією, response cache exclusions/Vary і noindex. `.com` в metadata — SEO-origin, не доказ фактичного host. Чинні `.env` та keys не заміняти локальними.
5. **Окрема дія для даних.** Read-only класифікувати M1/M8/M8.1 за §5, не запускати все механічно. Якщо current — нуль writes. Якщо потрібен відомий перехід — новий target backup, перевірений підтримуваний спосіб із локальними guards без обходу, точкові updates в транзакції та перевірка всіх unchanged полів. Якщо mixed/manual — зупинка й окреме рішення. Нові міграції, сідери чи full dump restore для кількох блоків тут не потрібні й не дозволені.
6. **Післяперенесеневе приймання лише після дозволеного перенесення.** Нові guest GET з фіксацією release/time/redirect/status/content-type/robots/canonical/title/description/H1: Questions HTML + фактичний JSON, теорія і course copy, One/Ones/Reciprocal/Passive, sitemap full ordered diff, alias/unknown course. Перевірити assets 200 і один Alpine, меню/theme, native course, Questions save/reload/server restore; source/body hashes та відсутність debug. Будь-які sitemap added/removed пояснити реальними даними, не підганяти під 554. У production навчальний HTML має бути indexable за відповідним профілем, API — technical noindex; не знімати noindex з усіх `test.js.*`.

### Реальні можливості відкату

- **Код/assets:** повернення окремо збереженої попередньої узгодженої release-копії (код, сумісний vendor, manifest + assets). M9 не створює універсального atomic deploy/rollback. Git rollback коду не відновлює DB-записи.
- **Passive:** існує command `--restore=<own-backup>` для inspection, `--apply` для guarded restoration. Exact before/after identities і відсутність нових ручних змін/dependencies обов'язкові; функціональні restore cases пройшли.
- **M8:** існує сервіс `PronounContentRepair::restore(backupPath, expectedDatabase)`, **немає CLI `--restore`**. Він перевіряє hashes/current post-state/цілісний snapshot і працює у транзакції. Старий backup під інший definition/revision може бути непридатний; не обіцяти сліпий restore через нову версію сервісу.
- **M8.1:** є транзакційний rollback незавершеного apply, але **немає реалізованого автоматичного restore після commit** ні в CLI, ні в сервісі. Для повернення п'яти body після успішного patch потрібен окремо погоджений і перевірений conditional record-restore, не вигаданий прапорець. Це частина майбутньої data-передумови.
- Локальний backup не є backup іншої БД. Повний restore бази заради кількох блоків із втратою новішого прогресу/сторонніх змін не пропонується.

## 7. Залишкові питання та статус

| Клас | Питання | Рішення у M9 |
| --- | --- | --- |
| Блокувало локальне функціональне приймання, виправлено | Mixed-state repair safety | Red proof збережено, 8 рядків guards, 4 нові cases PASS; робоча БД не змінена |
| Блокує повне приймання відтворюваності release / передумова перенесення | Не versioned і не сумісний з установленою PHP-базою composer.lock | Підтверджений **невирішений** блокер; окремий dependency baseline/install/check, без автоматичного update |
| Передумова майбутнього перенесення | Фактичні production SHA/PHP/СУБД, підтримуваний data спосіб і M8.1 post-commit restore | Не встановлені/не реалізовані в M9; потрібні окремі доступ, дозвіл і перевірка |
| Обмеження перевірки, не функціональний блокер M9 | Google Fonts недоступні | Fallback-font приймання; оригінальну типографіку перевірити окремо |
| Відкладене, не блокер цього етапу | Закриті курси, EN/PL, HTTP/HTTPS/www server rules | Політики не змінювати; окреме рішення/серверна перевірка |
| Відкладене, не блокер цього етапу | Один timeout категорії, повільний course GET, legacy CSS autodiscovery, browsers data warning, два main у test shell | Обмеження записано; без performance-циклів, редизайну чи залежностей latest |

Не виконано: production HTTP/SSH/БД/SC, новий Lighthouse/LCP/CLS/A/B, crawl 554 сторінок, новий повний multi-DB цикл, clean Composer install, remote content writes або production cache commands. Зелені синтетичні production-profile тести — не production audit.

## 8. Фіксація в Git

Перевірено всі чотири чинні `.github/workflows/*.yml`: push-тригери тільки для `main`, PR на main або manual dispatch; deployment кроків для звичайного push M9 немає. Активних local Git hooks/custom hooksPath не виявлено. Read-only `gh api` для branch protection/rulesets недоступний: GitHub CLI не авторизовано. Приховані серверні integrations цим не перевірені. PR-only політика main з `AGENTS.md` збережена.

До commit призначені тільки M9 report, 3 diagnostic helpers, 2 helper-test files, 2 PHP test files, 2 мінімальні service guards та один ignore rule. Явний staged diff/secret scan і `git diff --check` обов'язкові; не додавати приватні evidence, `.env`, locks із невирішеною проблемою, runtime файли або сторонні PPC-аудити. Звичайний push — лише `codex/seo-m9-local-release-readiness`, без PR/merge/workflow dispatch/main/force. Повний фінальний commit SHA та результат звірки remote SHA наводяться у підсумковій відповіді, а не самопосилально в цьому документі.

Зведене приймання виконано локально. План майбутнього перенесення підготовлений, але не виконаний. Production `.com/.ub` не перевірялися й не оновлювалися; PR, merge та деплой не виконувалися. Статус: **локальні функціональні контракти прийняті; повна відтворюваність PHP release залишається заблокованою до окремого рішення щодо Composer**.
