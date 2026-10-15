# M58 — редакційний аудит Past/Future Passive

Авторська редакція 1.0.0 схвалена після повного main та незалежного review locked DRAFT v3. Це не квитанція DB apply чи браузерного приймання.

## Межі й незалежний BEFORE

База: `7c4bac183d93227fbb5544b197538b75c3d579dc`. Лише UK-уроки Past Perfect Passive (Page 109, B1), Future Simple Passive (Page 107, B1), Future Perfect Passive (Page 106, B2), category 18, native ancestry `passive-voice → passive-voice-tenses`.

Незалежний приватний source/DB/HTTP BEFORE: SHA-256 `89dbf1213d3ebabcb1f4b30680c8600b58a4f7eb957038750addc5ed8cfe8ef0`. До редакції підтверджено 12/11/12 continuing slots, відповідно 11 B1 + null footer / 10 B1 + null footer / 11 B2 + null footer; попередньої практики немає. Оригінальні source order, owner, locale, type, column, технічний level і UUID не змінюються. Наприкінці кожної сторінки додається окремий practice-set того самого рівня.

Немає нових view/style компонентів, CSS, шрифтів, зміни кольорів, відступів, headers/sidebar, grader або чужих курсів/тестів. Серіалізація — чинний `M58PastFuturePassivesPackage::definition → TheoryNativeContentRelease → M49AdviceModalsPackage::definition`, а не новий pipeline. Metadata не є авторським дизайном.

## Аудит старих уроків і редакційні рішення

| Урок | Виявлено в старому джерелі | Нова редакція |
| --- | --- | --- |
| Past Perfect Passive | Повторені картки однакової форми; неперекладені приклади; Past Simple з before помилково маркувався неправильним; виправлення без чіткої точки відліку. | Had been + V3; минула перспектива, досвід, повтори й тривалий стан; before не змушує Perfect; форми had/’d/hadn’t; контрольоване порівняння Simple/Continuous/Perfect. |
| Future Simple Passive | Ready/available помилково подавалися як passive; старе in 2025; надлишкові повтори; бракувало відмінності deadline/duration. | Will be + V3 проти прикметника; прогноз/оголошення/рішення/запевнення без гарантії; by, until і not until; звичайна future when/if clause проти прямого запитання. |
| Future Perfect Passive | Will be finished by Friday помилково називалося неправильним; двозначне All ... not перекладалося як «не всі»; суперечність у коротких відповідях; нерелевантний медичний прогноз. | Обидва Future Simple/Perfect із by визнано граматичними; точка огляду з майбутнього; Not all / None окремо; звичайні Yes, it will / No, it won’t; нейтральні робочі приклади, повтори й стан з for/since. |

Активно-пасивні таблиці зберігають названих учасників, час і заперечення. Коли someone опущено, втрату інформації названо прямо. Sent не підміняється delivered/received. Been/being і V3/-ing розмежовано за повним ланцюжком. Заперечення до межі не перетворено на «ніколи», «не починали» чи «не виконали жодної частини». Умови на задану форму прямо називають її; не оголошують іншу граматичну перспективу неправильною взагалі.

## Basic/detail — п’ять явних семантичних рішень

| Detail key | Власник | Навіщо окреме поглиблення |
| --- | --- | --- |
| past-perfect-before-noncompletion | past-perfect-affirmative /sections/0 | Контраст повного ствердження з Perfect у before-частині: результат подачі вечері не можна механічно вивести з одного had been. |
| past-perfect-before-perspective | past-perfect-uses /sections/0 | Та сама кімната й послідовність приїзду: before вже задає порядок; Simple і Perfect різняться перспективою. |
| future-simple-when-clause | future-simple-questions /sections/1 | Пряме When will...? не є звичайною часовою when-частиною; заборона will за сусідством слова when хибна. |
| future-perfect-deadline-perspective | future-perfect-uses /sections/0 | Ті самі робота й п’ятниця; обидві моделі допускають кінцевий термін, Perfect виразно задає погляд назад. |
| future-perfect-duration-continuity | future-perfect-uses /sections/2 | Підтримуваний стан протягом тижня не мусить припинитися в майбутній точці відліку; продовження додається окремим реченням. |

Розподіл 2/1/2 — результат предметного рішення, не квота. Короткі застереження, переклади, часові значення й допоміжні форми лишаються в basic. Немає runtime word threshold або прихованого перенесення тексту за довжиною.

## Мова й практика

Усі пояснення — українською. Приклади розділено на native EN/UA fields або bilingual spans у полях hero/forms/table; помилки мають wrong_en/wrong_ua/right_en/right_ua, коментар окремий. У шести meaning choices повний переклад англійського стимулу відкривається після Check, бо видимий переклад розкривав би правильну відповідь. Українські контекст і критерій видимі одразу.

18 нових авторських завдань: на кожен урок 2 form-select + 2 meaning-choice + 2 manual/token input, рівні B1/B1/B2. Усі 36 варіантів мають конкретний bilingual feedback. Шість canonical input keys мають п’ять явних contraction aliases: 11 accepted variants загалом, не 11 aliases. Перевіряються had/’d, will/’ll, hadn’t/won’t; жодних grader changes.

Окремий embedded widget містить п’ять тематичних type 4 питань на урок із точним source seeder і CEFR. Він не замінюється авторськими inputs. Мінімальний новий банк дозволяється лише після окремого fresh source/DB quality audit; author freeze не засвідчує apply чи поточний actual options inventory.

## Primary-source journal

Джерела перевірено в межах M58; це внутрішній AI-assisted предметний review, не вигадані зовнішні human credentials. Main та незалежний bank agent прочитали всі три нові редакції, 18 ключів, 11 input variants, переклади й п’ять details. Власні вправи не скопійовано з source exercises.

- [Cambridge Passive: forms](https://dictionary.cambridge.org/grammar/british-grammar/passive-forms): в author-agent прямий запит повернув 403; фактичне publisher-indexed editorial body прочитано. Початковий прямий запит main повернув tool InternalError без підтвердженого HTTP status — ці події не змішано.
- [Cambridge Future perfect](https://dictionary.cambridge.org/us/grammar/british-grammar/future-perfect): прочитано фактичне publisher-indexed editorial body. Прямий requested URL із назвою future-perfect-simple-i-will-have-worked-eight-hours у author-agent повернув 403.
- [Cambridge Future: will and shall](https://dictionary.cambridge.org/grammar/british-grammar/future-will-): publisher-indexed editorial body; прямий requested /future-will-and-shall повернув 403 в author-agent.
- [Cambridge By](https://dictionary.cambridge.org/grammar/british-grammar/by): прямий 403; publisher-indexed editorial body прочитано: deadline, by-the-time, by-agent.
- [Cambridge Until](https://dictionary.cambridge.org/grammar/british-grammar/until): прямий tool InternalError без HTTP status; publisher-indexed body прочитано. Саме publisher прямо допускає Future Simple Passive з by-deadline; це не автоматичний тригер Perfect.
- [Cambridge Before](https://dictionary.cambridge.org/grammar/british-grammar/adjunct): прочитано publisher-indexed body з фактичним title Before за цим URL; прямий /before дав tool InternalError без HTTP status.
- [Cambridge Past perfect](https://dictionary.cambridge.org/grammar/british-grammar/past-perfect-i-): publisher-indexed editorial body прочитано; time up to then і ’d/hadn’t, без розгортання backshift/third conditional.
- [British Council Past perfect](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/past-perfect): пряме editorial main body прочитано. Окремо враховано signed Peter M Team response від 24 August 2026 про тривалість, повторення та можливе продовження; це не main body і не випадковий learner comment.
- [British Council Will have and would have](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/will-have-would-have): пряме editorial main body прочитано; використано future viewpoint, не перенесено медичний приклад і counterfactual scope.
- [Google helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content): пряму документацію прочитано. Додаємо корисне пояснення й оригінальний аналіз, а не word-count/SEO promises чи вигаданого експерта.

Відсутній direct access не названо direct-body review. Повний журнал actual URL/access/що звірено міститься також у master.sources.

## Footer та metadata

Дев’ять native resolver destinations свіжо підтверджено: proof SHA-256 `01cc80e245cc3aa64a92e96ee70ab1a53c7d6f3e408e6eaa0f354baddde77e9b`. Present/Past Simple мають flat ancestry, інші сім — nested category 18. У кожного поточного footer item немає зайвого посилання; решта 24 hrefs задані явно з цієї карти, не з одного універсального prefix.

Raw page title, rendered H1, breadcrumb/canonical та expected SEO проходять окрему перевірку через чинний PageMetadataService перед apply. Контентна редакція не змінює курсовий H1.

Окремий pre-apply metadata receipt SHA-256 `6e993792058aa99d80bcae78561e8911e7a3728e9e7d9532305fc81238f92a70` отримано до freeze з чинного PageMetadataService (`1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e`) та AddStructuredData (`ea3774ba90512bfa2bf7cee9277b87a3951e4a43fbd48eeed21ffb83ec2a58ac`). Очікувані title/description, LearningResource description та незмінні raw title/H1/breadcrumb/canonical збережено в master.editorial_review.pre_apply_metadata.

## Offline перевірка й межі доказу

Новий M58 suite використовує actual shipped native practice factory та EnglishAnswerVariants, не fake grader. Draft v3 дав 22/22 PASS: 36 option checks, 6 manual inputs, 6 strict shuffled token banks, 11 accepted variants, шість deferred translation cases, independent scores/reset/retry/mixed editing; network calls 0. Tests не bootstrap-ять Laravel, не читають working .env і не мають DB writes. Source hashes actual engine перевірено незмінними.

Offline PASS не є live browser/widget/DB/no-JS/print acceptance. Після freeze той самий suite слід прогнати в frozen mode, окремо від історичних literal UI failures. Фінальне guarded apply, repeated no-op, реальні сторінки й Git delivery документує completion report; цей редакційний аудит їх наперед не проголошує.
