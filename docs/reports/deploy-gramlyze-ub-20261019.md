# Gramlyze — деплой поточного стану, 19.10.2026

## Результат і межі

Деплой завершено 19.10.2026 о 13:33:20 UTC. На `https://gramlyze.ub` перенесено поточний робочий код із `gramlyze.loc`, включно з M69 та чинними виправленнями практики Past Perfect Continuous. Базу перенесено повним SQL-дампом, а не повторним запуском seeders.

Перед записом користувач окремо дозволив оновлення спільного application root для `.ub` і `.com`. Nginx, SSL, DNS/hosts та серверний `.env` не змінювалися. `main` не змінено; PR, merge, rebase, force push і reset робочих checkout не виконувалися.

## Git і склад релізу

- Гілка: `codex/deploy-gramlyze-ub-current-20261019`.
- Прийнята база M69: `b5ae76e06f3db056b18b1d63e1a68a59d72e7985`.
- Резервний commit коду перед деплоєм: `6f0625d54792cfe1bb8b6004d0d786aa6ad4fbc7`; push незалежно звірено через remote ref.
- Для checkpoint використано окремий Git index. Реальні HEAD/index і наявні сторонні зміни робочих каталогів не перезаписувалися.
- Release archive містить 6 386 перевірених файлів фактично наявного робочого runtime, навчальних джерел і зібраних CSS/JS.
- Історичні файли, яких уже немає в робочому `.loc` (зокрема старі patch commands і question JSON), не відновлювалися з Git у реліз. Точний список і byte-exact manifest залишено приватними. Git lineage цих файлів не переписувався.
- Чернетки M50, приватний `backup_branches.json`, `.env`, runtime caches, dumps/backups та діагностичні матеріали не включено до commit або публічних ресурсів.

Production assets зібрано з поточних джерел у чистій приватній build-копії з наявними залежностями. Навчальний текст, канонічний дизайн і dependency lockfiles під час деплою не редагувалися.

## Резервні копії й перенесення БД

Приватний серверний каталог: `/home/vitaly/gramlyze-ub-imports/20261019-m69-b5ae76e06`.

- `site-before.tar.gz`: повна копія застосунку до оновлення, 233 502 628 байтів.
- `database-before.sql.gz`: повний серверний DB backup до імпорту, 12 553 721 байт.
- Каталог має режим `0700`, обидві резервні копії — `0600`; вони розташовані поза webroot і не додані до Git.
- Перенесення: локальний `gr2` → перевірений серверний `gramlyze_ub`, 46 таблиць; SQL dump — 125 807 749 байтів, gzip — 12 984 814 байтів.
- Дані локальної БД до й після експорту збіглися. На сервері backup зроблено в режимі обслуговування, зі звіркою даних до й після backup.
- Після імпорту, а потім повторно після очищення кешів і компіляції views, для всіх 46 таблиць збіглися кількість рядків і SHA-256 послідовності всіх значень колонок у стабільному порядку (UTC, stringified fetches, length-prefixed values).
- Серверний `.env` збережено byte-exact. Міграції, масовий reseed та локальні DB-записи під час деплою не виконувалися.

Сценарій мав автоматичний rollback із перевірених копій. Відкат не знадобився. Після деплою maintenance прибрано; Nginx, PHP-FPM і MySQL — active.

Контрольні суми релізу:

- Code archive: `60838fa18a67e3f09a2e5ce6b8863875cfd136168ab010fcaed1fdde4d9f4e99`.
- Imported gzip dump: `34e8e7ec0dd8970660eb788cac8c2bc5c44ac63a8d1d78a75025a186d991d08b`.
- Site backup: `5bd754bb89a68a6b159d4f9bec46eacea96b4c82761d44a764292e0016ee4f3c`.
- Database backup: `406b993ea18d5a241632c5f537fa7ec20d1a0c13a3404831fca89ddcac8cc49b`.

## Післядеплойна HTTP-звірка

Звичайна перевірка TLS через Windows trust store, без обходу сертифікатів. Усі запити — read-only GET на `.ub`; запити до `.com` не виконувалися.

| Сторінка | HTTP | Вбудований builder |
| --- | --- | --- |
| [Головна](https://gramlyze.ub/) | 200 | не застосовується |
| [Past Perfect Continuous: Forms and Use](https://gramlyze.ub/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200 | 1 |
| [Concessive and Contrastive Structures](https://gramlyze.ub/theory/clauses-and-linking-words/concessive-and-contrastive-structures) | 200 | 1 |
| [Will vs Be Going To](https://gramlyze.ub/theory/maibutni-formy/future-simple/will-vs-be-going-to) | 200 | 1 |
| [Adjectives vs Adverbs](https://gramlyze.ub/theory/prykmetniky-ta-pryslinknyky/adjectives-vs-adverbs) | 200 | 1, 5 тематичних питань |
| [Order of Adjectives](https://gramlyze.ub/theory/prykmetniky-ta-pryslinknyky/order-of-adjectives) | 200 | 1, 5 тематичних питань |
| [-ed / -ing Adjectives](https://gramlyze.ub/theory/prykmetniky-ta-pryslinknyky/ed-ing-adjectives) | 200 | 1, 5 тематичних питань |
| [Тест Past Perfect Continuous: Forms](https://gramlyze.ub/test/past-perfect-continuous/forms) | 200 | окремий тест |

На шести theory pages звірено H1, canonical `theory-design` marker і builder. На трьох M69 сторінках звірено всі 15 погоджених question IDs. Нові `catalog-public-Cy8aaDRf.css` і `catalog-public-CgUm4gux.js` доступні через HTTPS з HTTP 200. Це HTTP-приймання деплою, не нове повне браузерне приймання всіх уроків.

## Додаткові перевірки та застереження

- JavaScript regression: 47 tests PASS для повторного ручного введення відповіді та прихованої підказки порядку слів PPC.
- Ізольований PHP regression: 6 tests / 161 assertions PASS; 231 захищений локальний файл без змін. Одна deprecation.
- Початкова sandbox-спроба PHP preflight не мала доступу до private bootstrap-каталогу; повторний ізольований запуск поза цим обмеженням пройшов.
- Vite build PASS. Попередження про застарілий Browserslist dataset не усувалося оновленням залежностей.
- `composer dump-autoload` завершився exit 0, але повідомив PSR-4 warnings для наявної структури seeders. Їх не приховували та не виправляли поза scope деплою. Runtime postconditions і всі зазначені HTTPS-сторінки пройшли.

Сирі DB-рядки, таблицеві fingerprints, приватні receipts, повні логи, резервні копії та deployment helpers залишено поза Git.
