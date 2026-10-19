# Gramlyze Past Perfect Continuous interactive practice and card disclosures

Подальше оновлення: 14 спільних кнопок замінено 56 окремими поясненнями для пунктів. Поточний стан описує [звіт про пояснення для кожного пункту](seo-m26-past-perfect-continuous-point-details.md); наведені нижче результати належать попередньому етапу інтерактивної практики.

Оновлено 2 жовтня 2026 року тільки на `http://gramlyze.loc`. Усі 14 кнопок «Докладніше» тепер знаходяться всередині відповідних контентних карток. На чотирьох сторінках підтем статичну практику замінено інтерактивною за зразком Present Perfect Continuous Forms and Use.

## Перевірені сторінки

- [Огляд Past Perfect Continuous](http://gramlyze.loc/theory/past-perfect-continuous) — 2 внутрішні disclosures, без нового блоку практики.
- [Forms and Use](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) — 3 disclosures та оновлена практика.
- [Negatives](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-negatives) — 3 disclosures та оновлена практика.
- [Questions and Short Answers](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-questions) — 3 disclosures та оновлена практика.
- [Time Expressions](http://gramlyze.loc/theory/tenses/past-perfect-continuous/past-perfect-continuous-time-expressions) — 3 disclosures та оновлена практика.

## Що змінилося

Кожна сторінка підтеми має чотири вправи: заповнення пропуску, вибір правильного твердження, порядок груп слів і побудову речення за українським. Перші три містять по два завдання, видимі номери, кнопки перевірки та скидання. Вибрані відповіді підсвічуються. У третій вправі перемішані групи по 1–3 слова можна натискати або вводити вручну; Backspace повертає доступність токенів, браузерний autocomplete вимкнено. Повні та скорочені заперечення приймаються; кінцева пунктуація не обов’язкова.

Четверта вправа показує п’ять наявних Compose Tokens питань лише своєї підтеми, через explicit theory links та точний seeder. Виправлено фільтр linked pool: діапазон `A2–B1` є позначкою блоку, а не неіснуючим рівнем питання. Точні рівні `A1`–`C2` і далі обмежують вибірку. Tag fallback незмінний.

Усі 24 авторські ситуації адаптовано окремим versioned payload із зіставленням з первинними case IDs. Immutable author master не редагувався. Усі 19 повних basic блоків та 14 author detail payloads збережені; для DOM-порівняння виключено тільки новий code-owned disclosure wrapper. Header, sidebar, background, навчальні банки, Mixed-тести, progress та інші локалі не змінено.

## Локальне застосування

Робоча гілка — `codex/seo-m26-past-perfect-continuous-layers-r2`; початкова версія цього follow-up — `6e0369b4167460320200e4e5d2fbe39b0030d901`. Використано наявний чистий worktree. Сторонні зміни основного checkout не додавалися до Git і не скидалися.

Фізичний guard підтвердив loopback DNS, єдиний Apache vhost з application root `D:/DEV/htdocs/gramlyze.loc`, локальний MySQL `gr2` та рівність CLI/web runtime. Локальний production-профіль `.env` не змінювався.

Preview `interactive-preview-20261002.json` має SHA-256 `f8aca57afb5b9243a7ec1a8ec1dbfdcc9b5b90e42e3c50bf9bf70560fb2440a7`. Застосовано рівно **4 existing row updates, 0 inserts, 0 deletes**: тільки `type`, `heading`, `body` блоків 12307–12310. ID, UUID, sort order, level, column, tags та timestamps збережені. Повторне застосування — no-op, 0/0; зайвий backup не створено. Незмінність 20 захищених таблиць перевіряється транзакційними postconditions і повторним no-op.

Exclusive backup збережено в `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m26-local/interactive-backup-20261002.json`; він не входить до Git. Тимчасовий SELECT-only runtime-proof route видалено, GET повертає 404. Міграції, робочі сідери, full reseed, очищення кешу чи sessions не виконувалися. `.ub` та `.com` не відвідувалися і не змінювалися.

## Результати перевірок

- **86 PHP tests, 1 258 assertions, 0 failures** у двох остаточних ізольованих SQLite suites: patch/package guards 35/554, rendering/token-bank/matcher 51/704. Є одна наявна PHP deprecation. 11 старих matcher test annotations переведено на PHPUnit 12 attributes, щоб ці регресії справді виконувалися.
- **32 JavaScript tests пройшли** для disclosures, navigation та sidebar layout.
- Vite build у приватний локальний output пройшла з наявними залежностями; manifest byte-identical до чинного build, SHA-256 `0bf06ba1b59c2f5782f2f5348e2319c507058fd12eb32bf272b538250614286a`. Залежності та згенеровані assets не комітяться.
- **20 браузерних станів пройшли**: 5 сторінок × desktop 1440/mobile 390 × light/dark. Усі GET — новий гість. Перевірено повний basic/detail текст, внутрішню геометрію кнопки, mouse, Enter/Space, focus-visible, independent open, reload, deep fragment, print та відсутність fetch після відкриття.
- На всіх чотирьох сторінках перевірено вибір і підсвічування, результати 2/2, скидання, повне складання токенами, ручні accepted variants без пунктуації, Backspace reuse та власний linked widget. Реальні question IDs звірено read-only з відповідними seeders/type 4 у робочій БД.
- **5 no-JS сценаріїв пройшли** для native disclosures. У фінальному браузерному запуску 0 JS/HTTP/network/font errors і 0 порушень GET-only scope. Чисті screenshots content card та desktop/mobile practice переглянуто візуально.
- Контрольні реальні сторінки [Present Perfect Continuous Forms](http://gramlyze.loc/theory/tenses/present-perfect-continuous/present-perfect-continuous-forms) і [Present Perfect Forms](http://gramlyze.loc/theory/tenses/present-perfect/present-perfect-forms) повертають 200 без M26 extensions. На сторінці-зразку працюють 3 групи практики, 3 кнопки перевірки та linked widget. Навчальний SSR-текст Present Perfect збігається з попереднім baseline; для сторінки-зразка попереднього baseline немає, тому її незмінність на цій підставі не заявляється.

Діагностичні матеріали залишені приватно в `storage/app/seo-m26-local`; головний браузерний результат — `interactive-after-v2-browser.json`. Це перевірка реальних сторінок після apply, не fixture preview.

## Обмеження поза цим оновленням

Strict document overflow не зелений: старий mobile-декор створює 0–15 px горизонтального виходу. Learning-content overflow — 0. Глобальний background не змінювався.

У незмінених наявних банках є змішані українсько-англійські prompts, які можуть потрапити у четверту вправу. Підтверджені приклади: Forms #23681 «she вчилася до того як the teacher came in», Forms #23683 «they тихо розмовляли до того як the teacher came in», Negatives #23753 «she не вчилася до того як the teacher came in», Negatives #23755 «they не розмовляли голосно до того як the teacher came in», Questions #23825 «Вони вчилися до того як the teacher came in?». Редакційна перевірка цих банків потребує окремого завдання; за попереднім M26 scope їх не дозволено переписувати.

Початковий схвалений HTML-preview Negatives залишається недоступним, тому pixel-parity з ним не заявляється. Цей follow-up орієнтується на нові screenshot-зауваження користувача та штатний shared practice-set renderer. Попередній [звіт M26](seo-m26-past-perfect-continuous-layers-r2.md) описує історичний статичний етап; поточна практика — інтерактивна, як описано тут.
