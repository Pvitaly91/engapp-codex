# Оновлення gramlyze.ub 27 жовтня 2026

Код поточного локального Gramlyze та повна база з SQL-дампу перенесені на [gramlyze.ub](https://gramlyze.ub). Основний перенос завершився 27.10.2026 о 00:27:44 за київським часом. Сайт виведений із maintenance, PHP-FPM перезавантажений. **Повне приймання не завершене: на перевіреній сторінці M94 конструктор показує оригінальні умови й пояснення замість погоджених розширених текстів.**

## Межі оновлення

Оновлювався тільки дозволений `.ub`. Запитів до `.com` та змін його сервера не було. Попереднє припущення про спільний hosting було помилковим: знайдений запис `server_name` у nginx сам по собі не доводить фактичне розміщення домену; користувач уточнив, що `.com` працює на іншому сервері.

Серверний `.env` і nginx-конфігурація залишилися побайтово незмінними. Локальні секрети не переносилися. Залежності не встановлювалися й не оновлювалися; міграції та сідери не запускалися. Серверні сторонні файли не видалялися.

Reference content commit — `765721c27161b78d21bbfa930c3f5bf52eb5c0f5`, робоча гілка — `codex/seo-m94-relative-clauses-whose-which-standard-view`. Джерело деплою — фактичний поточний локальний ROOT, а не чистий checkout цього commit: інвентаризація зберегла 458 non-EOL відмінностей і 11 ROOT-only файлів. Це не твердження про повний семантичний аудит цих сторонніх змін. Вони не включаються до commit звіту.

## Код і база

| Перевірка | Результат |
| --- | --- |
| Передані файли | 7403 sources + 5 свіжих generated assets = 7408 |
| Ізольована збірка assets | Vite exit 0, 57 modules; без dependency installs |
| SQL-дамп | Повний, 127594103 bytes; gzip 13627312 bytes |
| Імпорт і незалежна перевірка під maintenance | Усі 46 таблиць збігаються з дампом за кількістю рядків і fingerprints |
| Повторна read-only перевірка після HTTP | 46/46 таблиць exact; 7408/7408 файлів exact у двох скануваннях |
| Сервіси | nginx, PHP-FPM і MySQL active; maintenance відсутній |
| Віддані CSS/JS | 4/4 HTTP 200, точні розміри та SHA-256 |

Фінальна read-only перевірка завершилася 27.10.2026 о 00:43:11 за київським часом. Порівняно з попереднім серверним станом змінилися 13 learning-related таблиць; решта 33, включно з users і progress, мали однакові fingerprints. База все одно переносилася повним дампом, а не вибірковим patch.

Збірка зберегла warnings про ізольований outDir і застарілі Browserslist data. Composer autoload regeneration завершився, але видавав PSR-4 warnings для legacy seeders. Виправлення цих файлів не входило в деплой.

## Резервні копії

До імпорту в maintenance створені ексклюзивні backups поза webroot. Обидві копії збережені; rollback не застосовувався.

| Backup | Bytes | SHA-256 |
| --- | ---: | --- |
| `site-before.tar.gz` | 237836721 | `890e27dd7bd6c097951986dbf465eb96549a5ce3348c0651e265a1ad6906b636` |
| `database-before.sql.gz` | 13051614 | `497e418ac6ef8e19100ee263a6e6d7827d858846e9dddd9d1a26c0ddce19a604` |

DB-backup пройшов gzip roundtrip перевірку. SHA-256 переданого code archive — `0bdd9a848c9377e9f979aa24c4ef32aaee4d81fe663004f142f598277cc48fc1`; переданого стисненого локального дампу — `88e4e58c52690e55bd5c9aad8e48d89bb2037e18450f1d75ca57d4544a159b6b`.

## Перевірені сторінки

Усі 24 наведені URL відповіли HTTP 200 через звичайну перевірку TLS, без redirects і запитів до інших доменів. На 21 представницькій сторінці M74–M94 перевірено склад п’яти тематичних type-4 питань — 105 точних ID. Позначення 5/5 нижче стосується складу ID, а не інтерактивного оцінювання відповідей. Для M94 перевірка текстових проєкцій завершилася FAIL, тому загального успішного acceptance receipt немає. Це перевірка GET, серверного HTML і assets, не повторне інтерактивне проходження всіх тестів сайту.

| Сторінка | Результат |
| --- | --- |
| [Головна](https://gramlyze.ub/) | 200 |
| [Past Perfect Continuous Forms](https://gramlyze.ub/theory/tenses/past-perfect-continuous/past-perfect-continuous-forms) | 200, native widget |
| [Past Perfect Continuous test](https://gramlyze.ub/test/past-perfect-continuous/forms) | 200 |
| [Another Other The Other](https://gramlyze.ub/theory/imennyky-artykli-ta-kilkist/another-other-the-other) | 200, 5/5 |
| [Possessive Adjectives vs Pronouns](https://gramlyze.ub/theory/zaimennyky-ta-vkazivni-slova/possessive-adjectives-vs-pronouns-my-mine-your-yours) | 200, 5/5 |
| [Relative Pronouns](https://gramlyze.ub/theory/zaimennyky-ta-vkazivni-slova/relative-pronouns-who-which-that-whose) | 200, 5/5 |
| [One Ones](https://gramlyze.ub/theory/zaimennyky-ta-vkazivni-slova/one-ones) | 200, 5/5 |
| [Separable and Inseparable Phrasal Verbs](https://gramlyze.ub/theory/prepositions-and-phrasal-verbs/separable-and-inseparable-phrasal-verbs) | 200, 5/5 |
| [Alternative Questions](https://gramlyze.ub/theory/pytalni-rechennia-ta-zaperechennia/types-of-questions/alternative-questions-coffee-or-tea) | 200, 5/5 |
| [Negative Forms](https://gramlyze.ub/theory/pytalni-rechennia-ta-zaperechennia/negative-forms) | 200, 5/5 |
| [Sentence Transformations Conditionals](https://gramlyze.ub/theory/sentence-transformations/conditionals) | 200, 5/5 |
| [Present Perfect Common Mistakes](https://gramlyze.ub/theory/common-mistakes/present-perfect-common-mistakes) | 200, 5/5 |
| [B2 Mixed Revision](https://gramlyze.ub/theory/mixed-revision/b2-mixed-revision) | 200, 5/5 |
| [Basic Word Order](https://gramlyze.ub/theory/basic-grammar/word-order/theory-basic-word-order) | 200, 5/5 |
| [Verb to Be Present](https://gramlyze.ub/theory/basic-grammar/verb-to-be/verb-to-be-present) | 200, 5/5 |
| [Present Simple Forms](https://gramlyze.ub/theory/tenses/present-simple/present-simple-forms) | 200, 5/5 |
| [Present Continuous Questions](https://gramlyze.ub/theory/tenses/present-continuous/present-continuous-questions) | 200, 5/5 |
| [Past Continuous Forms](https://gramlyze.ub/theory/tenses/past-continuous/past-continuous-forms) | 200, 5/5 |
| [Present Perfect Questions](https://gramlyze.ub/theory/tenses/present-perfect/present-perfect-questions) | 200, 5/5 |
| [Past Perfect Forms](https://gramlyze.ub/theory/tenses/past-perfect/past-perfect-forms) | 200, 5/5 |
| [Future Simple Questions](https://gramlyze.ub/theory/maibutni-formy/future-simple/future-simple-questions) | 200, 5/5 |
| [Future Perfect Forms](https://gramlyze.ub/theory/maibutni-formy/future-perfect/future-perfect-forms) | 200, 5/5 |
| [Future Perfect Continuous Questions](https://gramlyze.ub/theory/maibutni-formy/future-perfect-continuous/future-perfect-continuous-questions) | 200, 5/5 |
| [Relative Clauses with Whose and Which](https://gramlyze.ub/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which) | 200, 5/5 IDs; розширені умови й пояснення FAIL |

## Незакрита проблема M94

Усі п’ять правильних питань рівня B2 вибрані в очікуваному порядку, але presenter повертає оригінальний банк замість погоджених condition/explanation clones. Read-only діагностика підтвердила: package, audit, block і presentation прив’язки проходять; для всіх п’яти не збігається `question_snapshot`. Решта п’яти компонентів snapshot для кожного питання збігаються. Окремий session-only UTC контраст результат не змінив і не є зміною web runtime.

Точне порівняння з первісним локальним snapshot визначило лише два відмінні поля для кожного питання: `created_at` і `updated_at`. Серверні рядки відповідають замороженим значенням мінус три години; інші 12 полів і всі типи збігаються. Зміна тільки цих двох полів у копії в пам’яті відтворила повний серверний digest. Отже, відмову спричиняє суворе порівняння часових рядків, а не відмінність навчального тексту чи відповідей. Дані в БД для обходу guard не переписувалися.

Фактичний перенос коду й бази перевірений; причину відмови snapshot guard не слід підміняти твердженням про невдалий імпорт. Guard не послаблювався, навчальні дані та спільний presenter під час діагностики не змінювалися. Для повного приймання потрібне окреме виправлення сумісності snapshot перевірки та повторний HTTPS acceptance M94.

Перший збій приватного checker також збережений: URL assets помилково зіставлявся з archive path без префікса `public/`. Виправлено лише приватний checker, після чого точні байти CSS/JS підтверджені окремо. Ця поправка не усуває й не приховує реальну проблему M94.

## Межі попередніх тестів і Git

Попередні локальні M94 результати не перезапускалися як частина деплою: targeted 51 tests / 5034 assertions у кожному з двох profiles, 0 failures/errors, 1 deprecation; browser checks — 6 layout states, 8 manual/token cases, widget 5/5. Registry — 254/254. Історичний suite зберігає 86 failures / 1 error проти 82 / 1 до M94; чотири нові M93 raw-profile boundary failures не виправлені. Збережені strict raw/full-HTTP parity limits і mobile overflow 6–10 px. Деталі — [звіт M94](seo-m94-relative-clauses-whose-which-standard-view.md).

Git scope цього завершення — тільки цей звіт у чинній робочій гілці. `main`, сторонні ROOT-правки, секрети, дампи, backups і приватні діагностичні файли до commit не включаються.

Контрольні підсумки: post-HTTP health receipt SHA-256 `598a3450ce5dc10497264f11e3ec7cf6ab62be85695b008cc6ea7128b30e9abe`; retained M94 HTTP observation SHA-256 `514caec95eb29742a2e8f1dcd148afae38947563f64d8d5de379f1700e6f6852`. Сирі receipts зберігаються приватно, а не в репозиторії.
