# M23 — авторський контент трьох уроків

Дата локального приймання: 28 вересня 2026 року. Основа: `codex/seo-m22-articles-collocations-content` / `f26419ff9d10504a013db4bddc81977f835ea348`. Робоча гілка: `codex/seo-m23-authored-revision-content`. Ціль застосування: виключно `http://gramlyze.loc`, фізична локальна MySQL `gr2` на `DESKTOP-3C05HGF:3306`. Production `.com` / `.ub` не перевірявся і не змінювався.

## Джерело та межі

Версія авторського пакета: 1.0.0. Незмінний [master](../content/m23-authored-content.v1.json) має SHA-256 `eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6` (UTF-8, один кінцевий LF). Хеші декодованих `body_html`: Nominal `ebcba2cc42076b5be3ff0e271dd0e3546ef7d258cccfa61c5c6714f72ebcf8b0`, C1 `2d4f2f06c44a12366202a105fbf2b3c78dd87cd3c62c59648394ede0c075f84f`, C2 `58c39d1cc3a95aa449b6c74a7beb68bdf568b06980a04bfa4b5b95fd15c3dbca`. Авторські джерела та редакторський контроль 18 завдань перенесені окремо в [нотатки](../content/m23-author-sources.md). Це технічне приймання наданого тексту, не незалежна мовна експертиза.

У кожному з трьох чинних `definition.json` замінено тільки `page.subtitle_html`, `page.subtitle_text`, серіалізований JSON наявного hero `body`, та `heading`/`body` наявного box. Схема, `page.title`, slug, seeder, категорія, tags, locale, типи/порядок/рівні блоків лишились незмінними. `preserve_*`, hashes та `expected_metadata` лишилися контрольними полями master, не потрапили в application definition чи БД. HTML-output renderer у БД не записувався; renderer/CSS/JS не змінювались. Presentation-атрибути, href і видимий текст не редагувалися.

| Урок / URL | Identity / категорія | Рівень / запис | Авторські розділи, завдання, ключі |
| --- | --- | --- | --- |
| [Nominal style and information density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | `FormalEnglish/NominalStyleAndInformationDensityTheorySeeder`; `formal-english` | C2; Page 321; blocks 8850–8852 | 8 / 6 / 6; 1 таблиця |
| [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | `BasicGrammar/C1MixedRevisionTheorySeeder`; `mixed-revision` | C1; Page 329; blocks 8887–8889 | 8 / 6 / 6; 1 таблиця |
| [C2 mixed revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | `BasicGrammar/C2MixedRevisionTheorySeeder`; `mixed-revision` | C2; Page 330; blocks 8890–8892 | 8 / 6 / 6; таблиці свідомо немає |

У всіх трьох source/DB перевірено збереження незмінних полів Page та TextBlock, включно з ID, наявним UUID-станом, owner, sort order і зв’язками. Відомі H1 залишилися `Nominal style and information density`, `C1 Mixed Revision`, `C2 mixed revision`; окремі `Page.title` не уніфікувалися. Усі 14 унікальних внутрішніх посилань авторського пакета дали HTTP 200 на `.loc`; зовнішні авторські посилання збережено як передано, не перевірялися повторно.

## Адресне застосування

До запису зафіксовано приватні inventory/target-snapshot, fingerprint 46 таблиць та 36 прийнятих раніше уроків, HTTP стан десяти адрес і впорядкований sitemap. Фізичний guard звірив Windows CLI, loopback DNS, єдиний Apache vhost, document root, PID/listener MySQL та nonce-bound digest реального web runtime з CLI. `APP_ENV`, `.env`, ключ і підключення не підмінялися. Після цього fresh preview показав **12 змін**: Pages 321/329/330 і дев’ять їхніх TextBlock. Preview SHA-256: `2af6d3666e835aa44e45582e837a2f3c58d6899f68ee7a9732cc0b95d0f95774`.

Transactional apply виконав рівно 12 змін із новим exclusive record-backup `storage/app/seo-m23-local/m23-record-backup.json`. Це приватний локальний файл, не Git-дамп. Повторний guarded preview після застосування: **0 змін** (`m23-noop-plan.json`). Тимчасовий read-only proof endpoint видалено: він дає 404; `routes/api.php` має початковий SHA-256 `5dba0c2624f8a8040aafe16230cc57bd050d6184c1e9e69297f5db7b4104a522` і нуль Git-diff. Міграції, повне пересівання, масове оновлення або cache clear не виконувалися.

## Точність та локальне приймання

- Незалежні очікування будуються лише з незмінного author master. Усі три ланцюги `master → decoded definition → локальний Page/TextBlock → server HTML → DOM після reload` збіглися за точним порядком subtitle sentence, intro, hero labels/text/examples, заголовків і абзаців, клітинок таблиць, умов шести завдань і відповідних шести ключів. Нормалізуються лише entities, пробіли та NBSP, але не пунктуація, регістр, числа чи порядок. Чинний шаблон показує авторський strong-заголовок як H1, а решту subtitle як окремий опис без розділового тире; слова збережені.
- Негативні fidelity fixtures для вилученого `not`, посиленого `may → will`, зміненого числа, пропущеного українського перекладу й переставлених ключів відхиляються. Source/DB/HTML порівнюються окремо, не з власним after-станом.
- Ізольований PHPUnit: **41 тест, 304 assertions**, pass; одне deprecation повідомлення з тестового середовища. Node `--test`: **8 тестів**, pass. Реальний `TheoryRichContent` opt-in, category ancestry, old→new/no-op, conflict/rollback/restore і локальний guard входять до цільового набору.
- HTTP: усі 3 theory, 3 штатні test URL, 1 course URL, 2 незмінені control theory та sitemap — **200**. Штатні переходи до трьох тестів перевірені без відповідей. Локальний `noindex`, title/H1/canonical/robots і посилання на тести збережено; змінені тільки description/OG/Twitter на трьох цільових сторінках. Canonical `.com` аналізувався як HTML-рядок, без запиту до `.com`.
- Ordered sitemap: **554 URL до і після**, той самий порядок і SHA-256 `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Fingerprints усіх захищених таблиць, банків питань і 36 уроків M11–M22 не змінились. Обидва контрольні уроки зберегли HTTP-вміст і metadata.
- Реальний Chromium: 3 уроки × desktop/mobile, кожен у light/dark; intro, hero, нижні секції, шість завдань/ключів, native `details` mouse/Enter/Space, reload, новий context, довгі приклади й відсутність загального overflow пройшли. Дві таблиці на mobile мають локальний горизонтальний scroll (720 px content / 322 px viewport, left 398 px). C2 без таблиці. Скріншоти інтродукції, повних сторінок, ключів і таблиць збережені приватно; візуально оглянуті. Два незмінені уроки також пройшли desktop/mobile (4 додаткові сценарії, HTTP 200, наявний rich-content, без page errors/overflow).

Курсова копія Nominal Style повернула HTTP 200; server HTML містить 6 умов і 6 ключів, але browser `contentVisible=false` через чинний gate. Gate не обходився, тому відкритий вигляд курсового контенту **не** заявляється як візуально прийнятий. Ізольований PHP-тест підтвердив штатний курсовий URL, але не візуальне відображення закритого уроку. Browser runner свідомо заблокував state POST, тож відповідний `net::ERR_FAILED` — read-only обмеження, не встановлена помилка застосунку. Google Fonts CSS був недоступний у цьому середовищі (`net::ERR_NETWORK_ACCESS_DENIED`), сторінки відобразились fallback-шрифтом. Uncaught page errors або PHP warnings у цільовому прийманні не зафіксовано. Приватні screenshots, proof, plan і backup не включені до Git.

Production не перевірявся і не змінювався. Нові залежності/build inputs не змінювались, тому production build не запускався.
