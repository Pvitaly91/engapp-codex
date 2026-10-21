# Deployment Gramlyze — 21 жовтня 2026

Фінальний звіт перенесення поточного погодженого стану на сервер.

## Результат і межі

- Деплой завершено 2026-10-21 16:17:25 UTC: код і повний SQL dump перенесені на `https://gramlyze.ub`.
- Користувач окремо дозволив деплой та використання спільного application root `.ub` / `.com`; наслідок спільного root погоджено.
- Application root: `/var/www/gramlyze.ub`. HTTP-приймання виконано тільки на `.ub`: запитів до `.com` — 0.
- `.env` збережений за exact hash guard; Apache/nginx/hosts та конфігурацію застосунку не змінювали.
- Maintenance вимкнений, PHP-FPM перезавантажений; final health підтверджує active nginx/FPM/MySQL.

## Код і збірка

- Accepted content commit: `fd1ad30e710b3d950e574848c886d86f6045f2e3`, уже опублікований у `codex/seo-m73-generic-irregular-collective-standard-view`.
- Усі 6 440 вихідних файлів пакета відповідають accepted Git-вмісту побайтово або після нормалізації LF; 309 відмінностей — лише EOL. Додатковий code-checkpoint не потрібний.
- Фінальний пакет: 6 445 файлів = 6 440 sources + 5 свіжих Vite assets. Щодо попереднього deploy manifest: 23 changed / 60 new sources.
- 15 case-aliases виключено; 46 720 історично відсутніх шляхів не відновлювали. Forward deployment не видаляє server-only paths.
- Ізольована Vite-збірка завершилася exit 0: 57 modules, без встановлення залежностей, змін source/locks або використання старого served build.
- Build warnings: outDir поза project root не очищався; Browserslist database застаріла. Залежності під час деплою не оновлювали.
- `composer dump-autoload` завершився exit 0 з наявними PSR-4 warnings для структури seeders; поза scope деплою їх не переписували. Міграції та seeders не запускалися.

## Повна база і резервні копії

- Перенесення виконано повним SQL dump, не частковими content patches: local `gr2` → server `gramlyze_ub`, усі 46 таблиць.
- Source MariaDB 12.0.2 → server MySQL 8.4.11; source/import/verify `data_hashes` збігаються для кожної з 46 таблиць.
- Порівняння pre-deploy DB backup з import: змінилися лише `pages` і `text_blocks`; інші 44 таблиці мають ті самі row counts і hashes, включно з users/progress у цьому фактичному snapshot.
- Raw dump: 125 983 354 bytes; gzip: 13 051 220 bytes. Events/routines/views — 0.
- До import створено окремі exclusive code/site та DB backups; gzip roundtrip DB backup перевірено.
- Server job: `/home/vitaly/gramlyze-ub-imports/20261021-m73-fd1ad30e7`, поза webroot; directory 0700, backup/artifact files 0600.
- Rollback підготовлений з maintenance fail-closed та import-start marker; у штатному успішному сценарії rollback не виконувався.
- Secrets, `.env`, SQL dumps, backups і private diagnostics не додаються до Git.

| Artifact | Bytes | SHA-256 |
|---|---:|---|
| Code archive | 17 450 030 | `e42bb5f3285db3f206ce1a06c4abc34eb05781a77542e02975a0ede52324abba` |
| Source DB gzip | 13 051 220 | `9834be68f810fe5e2fd54088327b450a9c8c9ed9e4be623e3eab95dc00224369` |
| Pre-deploy site backup | 237 201 176 | `6abac4be47bd70e8a5f0196430335fcc0d92a9315dd9638ca4499ad49bf7f88f` |
| Pre-deploy DB gzip backup | 12 985 302 | `8b32907dca637c42197ce3d1a797793096e94dc1da24126cf7ca4785642d82a6` |

## Фактичне HTTPS-приймання

- 17/17 сторінок: HTTP 200, очікувані page markers/H1; завершено 2026-10-21 16:19:09 UTC, verifier exit 0.
- На 12 сторінках M70–M73 підтверджено рівно 5 тематичних selected IDs у кожному embedded sentence builder: разом 60.
- Два assets, на які фактично посилається перевірена сторінка, отримано HTTPS GET: CSS 123 022 bytes і JS 13 486 bytes, обидва HTTP 200.
- Решта трьох generated assets перевірена у складі повного server package; окремих HTTP GET для них не заявляємо.
- TLS — звичайна Windows trust verification, без bypass; redirects не потрібні; `.com` requests — 0.
- Це deployment GET/SSR/asset acceptance, не повторне повне browser interaction-приймання.

| Перевірені сторінки | Посилання |
|---|---|
| Home | [Головна](https://gramlyze.ub/) |
| Regression | [Past Perfect Continuous](https://gramlyze.ub/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) · [Concessive](https://gramlyze.ub/theory/clauses-and-linking-words/concessive-and-contrastive-structures) · [Will vs Going To](https://gramlyze.ub/theory/maibutni-formy/future-simple/will-vs-be-going-to) · [PPC test](https://gramlyze.ub/test/past-perfect-continuous/forms) |
| M70 | [Intensifiers](https://gramlyze.ub/theory/prykmetniky-ta-pryslinknyky/intensifiers) · [Plural nouns](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/plural-nouns-s-es-ies) · [Countable / Uncountable](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/countable-vs-uncountable-nouns) |
| M71 | [Articles](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/articles-a-an-the) · [Zero article](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/zero-article) · [Quantifiers](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/quantifiers-much-many-a-lot-few-little) |
| M72 | [Partitives](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/partitives-with-uncountable-nouns-a-piece-of-a-cup-of) · [No / none / neither / either](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/no-none-neither-either) · [Geographical names](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/articles-with-geographical-names) |
| M73 | [Generic Reference](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/advanced-articles-generic-reference-the-rich-a-tiger-people) · [Irregular Plurals](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/irregular-plurals) · [Collective Nouns](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/collective-nouns) |

## Evidence і Git

- Code manifest: `efec6ab2fcd95682aed9da1de81c484fcee10bbca793dc54e010511fb7365382`.
- Database manifest: `02146895ed72dd25c3a1326b06894e2e096705aa2d1fe9a5b069e43295f548d9`.
- Import і independent verify receipts мають однаковий raw SHA: `79549a022d14a3f67f128a65259e041801fff17fc10eaefbd7e74a49ed029556a`.
- HTTPS acceptance receipt: `4b17a555af8d823f2fe2c676e320153603595eaa8e3765eb3d999f868f66281d`.
- Final health receipt: `b1774db3f80ea652c9084e5b9eb767b183597a5580d14bc4922ae174703d1caa`.
- Повне попереднє content/UI/test-приймання: [M73 report](../reports/seo-m73-generic-irregular-collective-standard-view.md); ці тести не заявляються як повторно запущені під час деплою.
- Цей звіт публікується окремим report-only commit у гілку `codex/seo-m73-generic-irregular-collective-standard-view`; повний SHA і незалежна перевірка remote ref наведені у фінальній відповіді після commit/push. `main`, PR, merge, force push та сторонні локальні зміни не входять у delivery.
