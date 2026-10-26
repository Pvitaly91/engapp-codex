# Модернізація шести уроків M93

Статус: шість сторінок застосовано локально та прийнято в реальному браузері. Нові цільові тести проходять; строгі legacy та whole-body порівняння мають явно наведені FAIL/обмеження. Пакет підготовлено для scoped commit і normal push робочої гілки, без деплою.

## Сторінки та межі

| Owner | Сторінка | Page | Category | Технічний practice level | Редакційний рівень |
| --- | --- | --- | --- | --- | --- |
| A | [Future Perfect Continuous Questions and Short Answers](http://gramlyze.loc/theory/maibutni-formy/future-perfect-continuous/future-perfect-continuous-questions) | 70 | 14 | NULL | B2 |
| B | [Future Perfect Continuous Time Expressions](http://gramlyze.loc/theory/maibutni-formy/future-perfect-continuous/future-perfect-continuous-time-expressions) | 71 | 14 | NULL | B2 |
| C | [Demonstratives this that these those](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/demonstratives-this-that-these-those) | 130 | 20 | A1 | A1 |
| D | [Some Any Places](http://gramlyze.loc/theory/some-any/theory-some-any-places) | 264 | 50 | NULL | A2 |
| E | [Some Any People](http://gramlyze.loc/theory/some-any/theory-some-any-people) | 263 | 50 | A2 | A2 |
| F | [Some Any Things](http://gramlyze.loc/theory/some-any/theory-some-any-things) | 265 | 50 | A2 | A2 |

Шість UK theory owners. Для A/B збережено ancestry maibutni-formy/future-perfect-continuous; C — zaimennyky-ta-vkazivni-slova; D/E/F — some-any, не Articles. NULL — реальний відсутній технічний фільтр, а не B2/A2 за замовчуванням.

Погоджена база M92: 6d3003884fb8d15e870285c1ac0875995ff84fc8. Робоча гілка: codex/seo-m93-future-completion-demonstratives-some-any-standard-view. Main, PR, production, .com/.ub та деплой не входять до цього виконання.

## Контент і практика

| Owner | Form controls | Meaning controls | Повні manual і token inputs | Збережені додаткові controls | Demo | Окремий widget |
| --- | --- | --- | --- | --- | --- | --- |
| A | 2 | 2 | 2 | 0 | 0 | 5 питань |
| B | 2 | 2 | 2 | 0 | 0 | 5 питань |
| C | 2 | 2 | 2 | 0 | 0 | 5 питань |
| D | 3 | 2 | 2 | 3 partial gaps і 2 manual rephrases | 1 | 5 питань |
| E | 2 | 2 | 2 | 0 | 0 | 5 питань |
| F | 2 | 2 | 2 | 0 | 0 | 5 питань |

Разом 42 graded controls: 8 retained/corrected та 34 new; 12 full inputs, 3 retained partial gaps, 2 retained graded rephrases й 1 ungraded demo. Manual/token — режими тих самих завдань, не подвоєний task count. Тридцять widget questions обліковуються окремо; нових записів questions не створено.

Сорок continuing native components збережено за позиціями, типами, columns, levels і UUID. Додано п’ять practice-set; у Places доповнено єдиний старий practice-set, а не створено дубль. Після apply — 45 components та 6 незмінених subtitles, усього 51 UK position. Усі шість subtitle HTML/plain і Page.text залишилися незмінними.

Point-level details: 0 за явними семантичними рішеннями цього пакета, не за глобальним порогом слів. Мапи компонентів, retained/corrected/new задач, aliases, перекладів і finite coverage збережені у versioned M93 artifacts. Не всі пояснені нюанси видаються за оцінювані: зокрема singular spatial that, подальше they/their та Nothing is wrong мають окремі basic-only межі.

## OLD → AFTER і мовна редактура по owner

| Owner | Continuing → AFTER components | Task map | Особливі збережені межі |
| --- | --- | --- | --- |
| A | 7 → 8; practice append | 0 → 6 new | NULL на всіх continuing blocks і новій практиці |
| B | 7 → 8; practice append | 0 → 6 new | NULL, календарний контекст не залежить від системної дати |
| C | 6 → 7; practice append | 0 → 6 new | mistakes/summary right, footer footer/NULL, practice left/A1 |
| D | 8 → 8; existing practice source index 6 | 8 retained/corrected + 4 new = 12; demo 1 → 1 | UUID 9fcd6c4f-a0e3-5e1b-95a3-64ce4ede412c, sort 7, left/NULL; старі absent keys не синтезовано |
| E | 6 → 7; practice append | 0 → 6 new | mistakes/summary right, footer footer/NULL, practice left/A2 |
| F | 6 → 7; practice append | 0 → 6 new | mistakes/summary right, footer footer/NULL, practice left/A2 |

Точні component/task source indices, UUID, retained/corrected/new classification, переклади, правила feedback і semantic boundaries наведено у versioned M93 components.v1.json, tasks.v1.json, coverage.v1.json та editorial.v1.json. Це навчальна проєкція author package у native-компоненти, не твердження про буквальну рівність raw author HTML і runtime HTML.

### A — Future Perfect Continuous Questions and Short Answers

Збережено hero, форми, usage, таблицю, помилки, підсумок і footer та вихідні англійські моделі; метапідписи замінено повними українськими перекладами. Розмежовано накопичену тривалість до майбутньої точки й дію лише в конкретну хвилину. Питання саме не підтверджує здійснення плану. Have been у відповіді не оголошується універсально неграматичним: найкоротший формат will/won’t явно задано умовою. Заперечення трьох годин за фактом двох не прирівнюється до відсутності діяльності.

Нові 2 form + 2 meaning + 2 full перевіряють порядок допоміжних дієслів, Who як підмет без зайвого he, тривалість та відповідь про названу особу. Widget не містить Who-subject: це покрито own practice, не приписано п’яти обраним питанням. DB: 7 continuing body updates + 1 practice insert; два repeated no-op 0/0. URL/levels, metadata, footer, browser і consumer межі підтверджено спільними перевірками цього звіту.

### B — Future Perfect Continuous Time Expressions

Попередня структура збережена. For позначає період, since — початок, by — майбутню точку огляду; дати й референт then задані контекстом. Початок діяльності може бути майбутнім відносно моменту мовлення. All morning/all day не означає роботу без жодної звичайної перерви й не гарантує завершення всього обсягу. Present після by the time обмежено звичайним майбутнім часовим підрядним.

Нові 2/2/2 перевіряють for/since, календарні відношення й висновок про ранок із перервами. Приймаються явно наведені we’ll та I’ll. Widget відтворює п’ять часових моделей, не всі можливі маркери й пунктуаційні варіанти. DB: 7 body updates + 1 practice insert; два no-op 0/0. Metadata/navigation/consumer acceptance — як наведено нижче, без зміни category teaser.

### C — This / That / These / Those

Збережено дев’ять старих EN/UA пар і right-column mistakes/summary. У просторових умовах названо мовця, кількість і відстань; множинні keys/cups не перетворено на один предмет. Нові 2/2/2 перевіряють форму й число, that як посилання на попередню пропозицію, this water з незлічуваним іменником, Those are my keys і these ones.

Widget охоплює spatial this/these/those. Singular spatial that, представлення людини та повний спектр discourse uses — basic-only. M77/One–Ones не редагувався. DB: 6 body updates + 1 practice insert; два no-op 0/0; A1 фільтр збережено. Metadata, footer й downstream DOM звірено без редизайну.

### D — Some / Any Places

Збережено всі вісім старих graded tasks: 3 selects, 3 partial gaps, 2 manual rephrases; окремий зразок лишився ungraded. Додано 2 meaning та 2 full, разом 12. Явні критерії розрізняють нейтральне питання, invitation з очікуванням згоди та affirmative some-модель. Інші граматично можливі наміри не оголошуються загальною помилкою. Для nowhere to sit задано зайняті місця, everywhere обмежено названими містами.

Збережено Friday evenings, without her phone, особу та час rephrases. Нові meaning/full перевіряють free-choice і near here/near the station. Приймаються There’s та don’t/do not. Старі hint-пояснення вже були visible; єдиний старий MC-widget налаштовано на точний тематичний type 4 без дубля. DB: 8 body updates, 0 insert; два no-op 0/0. SomeAny owner лишився в ancestry some-any, не Articles і не M75 Indefinite Pronouns.

### E — Some / Any People

Збережено дев’ять старих EN/UA прикладів. Уточнено people + plural, someone + singular, neutral any і питання з очікуванням some. Група при перефразуванні не губиться. Нові 2/2/2 перевіряють are/is, in our group і заперечення лише here, не відсутність знайомств у світі. Приймаються anyone/anybody та повне/скорочене do not.

Подальші they/their після someone пояснено у basic, не зараховано як окремо оцінене вміння. Widget arrived early не заперечує будь-який прихід узагалі. DB: 6 body updates + 1 practice insert; два no-op 0/0; A2, right-column blocks і footer збережені. Metadata/navigation/consumer межі — за спільними фактичними receipts.

### F — Some / Any Things

Збережено right-column структуру й попередні приклади. Розмежовано things + are, something + is, позицію прикметника після займенника та задум would like. Часткове not some things не підмінено нульовим not anything. Нові 2/2/2 перевіряють форму, потрібну/непотрібну частину списку й дозволений вибір із полиці. Приймаються I would like/I’d like.

Nothing is wrong означає «усе гаразд», не відсутність подій; універсальної заборони no things немає. Ці нюанси та affirmative free-choice не приписані п’яти widget questions. DB: 6 body updates + 1 practice insert; два no-op 0/0; A2 та ancestry some-any збережені. Metadata, footer й downstream teaching DOM не підмінені новою версткою.

Задекларовано 50 accepted keys включно з canonical: A/B/C/D/E/F = 6/8/6/14/9/7. Це не 50 незалежних синонімів. Усі ключі пройшли offline native factories в обох source contexts; усі 8 noncanonical keys також перевірено живими controls (7 full aliases на обох ширинах та 1 rephrase alias). Повна точність не заявляється за межами названих ключів, formatter і semantic contracts.

## Локальне застосування

Свіжі web/CLI та фізичний local-target proof пройдено. Preview незалежно зіставлено з авторською проєкцією та свіжим BEFORE. Exclusive backup raw-byte дорівнює preview; SHA256 dd28e1c8ea9b294e1520acffb888d4214dae3a95ec0c23951ad9e9da33e37a6e.

Транзакція завершилася успішно: 40 body update operations і 5 practice insert operations, разом 45 distinct changed rows. Page updates, subtitle writes, question-family writes і deletes — 0.

Повторний apply original plan дав no-op 0/0. Свіжий AFTER preview показав state after і 0/0; його повторний apply також дав no-op 0/0. Додаткові backup файли для no-op не створювалися. Тимчасовий nonce route видалено з точним поверненням foreign-inclusive raw routes file; окремий loopback GET повернув 404.

## Автоматичні перевірки

Новий цільовий набір: 55 unique methods, два виконання під ROOT application з ROOT та WT native-фабриками. По 23 169 assertions, 0 failures, 0 errors, 0 skips, 0 warnings, 1 PHP deprecation, child exit 0; разом 110 method executions і 46 338 assertions. Це не два незалежні повні application runs: WT application не bootstrap-ився. Deprecation походить зі старого PDO::MYSQL_ATTR_SSL_CA у конфігурації PHP 8.5; її не приховано.

Перший запуск обох контекстів мав 1 failure і 1 error. Збережено сирі результати; виправлено лише дві нові тестові фікстури: точний editorial basename та явний contract argument у negative case. Assertions, навчальний пакет, shared validator, formatter і grader не послаблювалися.

Строгий незмінений legacy набір: 23 profiles, 1246 method executions, 1153 unique methods, 93 overlap executions. BEFORE: 286095 assertions, 78 failures, 1 error. AFTER: 285607 assertions, 82 failures, 1 error, 0 skips, 1 warning, 23 deprecations. Строгий статус FAIL збережено, не перейменовано на PASS.

Чотири newly nonpassing methods належать M92SourceBoundaryTest: вони зупиняються на старому exact controller raw-profile guard після дванадцяти M93 additive registrations. Чотири changed existing failure bodies належать M77/M73/M74 source-lineage guards, не M91. Два still-passing methods змінили кількість assertions через динамічний registry. Повний literal comparison, warnings, deprecations та child exits залишаються у приватних receipts; старі тести, parser і comparator не змінювалися.

## Збереження джерел

Незалежна source-only перевірка до двох test-only уточнень підтвердила 17 192 незмінені парні source profiles, 25 foreign WT files, 326 visual paths та 3067 historical frozen paths. Обидва точні test-only виправлення додатково перевірено за retained private versions, original publication receipt і чотирма actual AFTER SHA. Нового pre-amendment backup заднім числом не створювали й не заявляли.

Шістнадцять shared context reversals повертають complete original raw bytes; по 12 additive lines на контекст. ROOT .gitattributes залишився відсутнім; WT зберіг exact 78806-byte prefix і отримав лише 39 literal LF rules. Дизайн, native views, formatter, grader, engine та M89 complete-loaded-verb-hints-v1 helper не змінено.

Final read-only audit після apply та повторно після UI підтвердив усі 46 schemas/row fingerprints, 46 continuing UK rows, 92 unchanged EN/PL rows і п’ять нових practice rows. Усі старі рядки та timestamps точні; питання, Page.text, subtitle, progress, saved tests і нецільові banks не записувалися. Final post-UI receipt SHA256: ba3984dda3ee588f03fcce73e92ceecda7938e93402abb36e4c91ca6527106be. Перший запуск з обмеженого sandbox зупинився на недоступному Windows CIM; read-only перевірку виконано успішно з дозволеним доступом до процесів, без зміни guard.

## Реальні сторінки, metadata та consumers

Усі шість локальних GET повернули 200; H1, canonical, description і JSON-LD зіставлено з погодженою проєкцією. Окремо шість HEAD пройшли. Canonical .com у HTML перевірено лише як рядок metadata: запитів на production не було. Усі 22 нижні посилання звірено: у A/B виправлено вісім коротких href на точні URL, labels/order/current збережено; C–F збережені. Hero рівень не підміняв SQL practice filter. Category descriptions, downstream teaching DOM і курси не редагувалися.

Реєстр sentence builders: цільові 6/6 та загальний 253/253, failed 0, writes 0. Попередні 247 semantic rows, membership і relative order точні; шість нових прив’язок не мають field diffs. Це acceptance конструктора, не автоматична сертифікація всіх SEO-вимог старих пакетів.

Повторне read-only приймання 67 старих consumers зроблено один раз: 28 literal EXACT і 39 literal FAIL лише за body_sha256. Усі 60 nonempty keyed payloads, 39 nonempty HTML facts і шість course learning DOM точні. Один pre-existing requested-SSR /test/future-perfect-continuous/questions повертає JSON-route 404; його не виправляли та не називали 200. Для додаткових 15 consumers усі GET — 200, але 0/15 complete literal EXACT: збережено whole-body/inline strict FAIL. Learning main DOM 15/15 і три непорожні category descriptions точні; у lesson captures відрізняються також static prefix/suffix серіалізованого списку. Причину raw inequality не доведено; «random shuffle» не заявляється. GET не повторювали до випадкового збігу.

Legacy child exits BEFORE: 0 × 3, 1 × 19, 2 × 1; AFTER: 0 × 2, 1 × 20, 2 × 1. До і після: 0 skips, 1 warning, 23 deprecations. Дві динамічні passing перевірки дали +42/+6 assertions, чотири нові byte-guard зупинки −536, сукупно −488. Окремі raw failure bodies і terminal issue prose залишені в приватних receipts. Жоден wrapper exit не підміняв child status.

## Браузерне приймання

Матриця: кожна з шести сторінок у 1440×1000, 390×844, 320×844, light/dark — 36 фактичних станів. Manrope 14px завантажений, broken images 0, learning overflow 0, рівно один widget на owner, 18 пар wrong/correct з поясненнями; 108 перевірок видимості приміток. Meaningful details 0: initial-closed/keyboard/deep-link/reload для details — N/A, не вигаданий PASS.

Learning main вкладається у 877/358/288 CSS px. Native tables A/B/D зберегли локальний overflow-auto; фактичний ArrowRight з фокусом рухає scrollLeft на всіх трьох, у D перевірено й повернення ArrowLeft. Перші заміри A/B показали 0 і залишені; наступні прямі key actions показали A 80→160 та B 0→40. На narrow документ/оболонка переповнюється у 24/24 BEFORE та 24/24 AFTER станах; це не приховано CSS і не названо виправленим. Для AFTER 390/320 document width: A 394/321, B 394/328, C 402/332, D 391/325, E 403/328, F 402/329, однаково для обох тем.

Виконано 48 nominal full-input cases: 12 завдань × manual/token × desktop/mobile. Додатково перевірено empty/whitespace, wrong, delete-return, mixed manual+token, clear/rebuild, stable recheck та reset; dropdown/autocomplete panels не з’являлися. Усі 78 displayed options натиснуто і оцінено, з перевіркою distractors, відновленням canonical та незалежним reset. Сім noncanonical full aliases перевірено на обох ширинах; окремо повне do not у перефразуванні D. У D три partial gaps перевірено правильно/неправильно, два rephrases — empty/whitespace/wrong/canonical/alias/reset; demo index 0 не має input і не входить до score 2/2.

Збережено 262 grade observations: 261 пройшла; перша harness observation залишена FAIL, бо parser очікував «0/2», а native UI правильно показав «0 з 2». Parser виправлено лише для фактичного українського роздільника, ту саму integer expectation повторно підтверджено. Це не помилка grader. Спроба reset('rephrase') не знайшла кнопку; після DOM-огляду використано наявний resetGroup('rephrase'), без зміни app source. Збережено також невдалий body-End і unsupported read-only animation probe; наступні законні UI дії не стирали початкових результатів.

Усі шість widgets пройдено 5/5: 30 distinct існуючих question IDs, правильний видимий settled result, green feedback та disabled answer chips після Check. Для кожного widget перевірено empty Check hidden, select/remove token, повернення multiplicity банку й незмінність score до перевірки. Порядок client questions фактично перемішаний; question зіставлявся за повною умовою, не source index. Глобальний state/cookies/progress не очищувалися.

Layout screenshots збережено приватно: початкові .png filenames фактично містять JPEG, а raster sizes 1390×990/375×812/305×804 відрізняються від DOM viewports. Причину не встановлено, pixel-exact claim відсутній; нові interaction screenshots мають правильне .jpg розширення. Browser zoom не змінювався. Print, no-JS та WCAG — not_checked.

## Тридцять фактичних settled answers

| Owner | Question ID | Видимий результат після Check |
| --- | --- | --- |
| A | 28404 | Where will she have been working since January by next month? |
| A | 28400 | Will she have been studying for two hours by then? Yes, she will. |
| A | 28398 | What will the team have been discussing all afternoon by then? |
| A | 28395 | Will you have been working for two hours by noon? |
| A | 28405 | How long will they have been testing by the end of the day? |
| B | 28471 | How long will Tom have been studying by six? |
| B | 28472 | By then Marta will have been training all day. |
| B | 28470 | By the time you arrive they will have been waiting all morning. |
| B | 28468 | By Friday she will have been staying here since Monday. |
| B | 28467 | By noon I will have been working for two hours. |
| C | 33331 | This document needs your signature today. |
| C | 33329 | These files on my desk are ready today. |
| C | 33337 | These shoes are too small today. |
| C | 33327 | This pen in my hand is blue today. |
| C | 33332 | Those chairs near the door are free today. |
| D | 2353 | Did you find anywhere to charge your laptop? |
| D | 2362 | Can we stop somewhere for coffee on the way? |
| D | 2355 | Tourists were everywhere in the old town. |
| D | 2354 | We had nowhere to wait during the storm. |
| D | 2363 | They did not have anywhere to store the boxes. |
| E | 2283 | Someone is waiting at reception. |
| E | 2288 | No people were sitting near the lake. |
| E | 2280 | Some people in my class work part-time. |
| E | 2282 | No one from the team arrived early. |
| E | 2281 | Did any people from the hotel complain? |
| F | 15963 | Did you buy anything for dinner? |
| F | 15964 | Nothing in the drawer belongs to me. |
| F | 15965 | I need something warm because it is cold. |
| F | 15972 | Some things are easier online than in person. |
| F | 15966 | We could not see anything in the dark. |

Це actual UI output, не переписаний еталон. B28470 зберігає відсутню comma у чинному formatter output; formatter у M93 не змінювався. Для A28400 перевірено multiplicity повторних she/will. Банки лишилися тематичними: A/B повні джерела 72/72, C–F 72 кожен; eligible 72/72/12/72/12/12, вибрано по п’ять. 222 інші eligible не підмішуються. Загалом старі 21 banks, 1961 actual SQL questions і 21 saved tests незмінні; source declarations 1968 та чотири pre-existing source/SQL mismatches окремо збережені, не виправлялися як M93.

## Залишок каталогу після M93

Завершальний SELECT-only audit: 289 definition files, 35 category/non-UK виключено, 254 унікальні UK page owners — це результат поточного сканування, не наперед задана квота. Чинний реєстр має 253 accepted owners; registered-but-unaccepted 0. DB writes 0, HTTP requests 0. Canonical bindings тут підтверджено resolver/config та фактичними Page/category rows, не новими HTTP-запитами до залишкових сторінок.

Єдиний unregistered owner: Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\RelativeClausesWithWhoseAndWhichTheorySeeder, Page 284, category 34: [Relative Clauses with Whose and Which](http://gramlyze.loc/theory/clauses-and-linking-words/relative-clauses-with-whose-and-which). Versioned AFTER і independent acceptance contract для нього відсутні. Сьому сторінку не починали.

Три конкретні source uncertainties — уже наявні decoded-definition розбіжності ROOT/WT для:

- [Present Simple Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-present-simple), Page 113, Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePresentSimpleTheorySeeder;
- [Past Simple Passive](http://gramlyze.loc/theory/passive-voice/theory-passive-voice-past-simple), Page 110, Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoicePastSimpleTheorySeeder;
- [Future Simple Passive](http://gramlyze.loc/theory/passive-voice/passive-voice-tenses/theory-passive-voice-future-simple), Page 107, Database\\Seeders\\Page_V3\\PassiveVoice\\Tenses\\PassiveVoiceFutureSimpleTheorySeeder.

Їх registry bindings accepted, але source parity не заявляється. Через ці три uncertainties literal catalogue child exit — 1, не PASS. Вони не є додатковими трьома необробленими owners і не були змінені у M93. Catalogue receipt SHA256: 698aab2e734d52d45b1dd9d3c135828b2660dd8eec8ac5333e3a06ca56bc2180.

## Межі доставки та EOL

Доставка обмежена explicit allowlist з 48 шляхів: 47 content/runtime/test/shared/attributes sources і цей звіт. ROOT не staging-иться; 25 foreign untracked WT files не включаються. Секрети, .env, dumps/backups, приватні proofs/screenshots/HTML, caches, generated assets і тимчасовий route виключені. Public JSON privacy scan не знайшов заборонених runtime/secret records.

System core.autocrlf=true не змінювався. Plain git diff --check не пройшов (native Git exit 2; shell wrapper повідомив exit 1) через чотири CR-at-EOL на нових рядках історично CRLF/-text TestJsV2Controller; це не пробіли перед CR. Перевірка з command-local core.whitespace=blank-at-eol,blank-at-eof,space-before-tab,cr-at-eol повернула exit 0. Raw CRLF shared file, інші raw shared EOL та точні reversals збережені; renormalize не запускали. WT .gitattributes лишив mixed working EOL з незмінним префіксом, Git index для нього має LF і попереджає про checkout CRLF. TextBlockToQuestionsMatcherService та TheoryCourseManifestService зберегли working CRLF, але їх index blobs мають LF, як і до M93: це чинний unspecified-text/core.autocrlf filter, не нове EOL-правило. Тому вимога «47 raw blobs + attributes» не проходить: 45 blobs raw-exact, а attributes і ці два service blobs перевіряються за точним історичним LF index-профілем та additive reversals. CRLF/-text controller лишається raw-exact. 39 literal text/eol=lf rules стосуються лише точних M93 paths; нові документи — UTF-8/no BOM/LF/один EOF.

Report scope не містить main/production/PR/deploy. Commit SHA та незалежно перевірений remote SHA наводяться у фінальному повідомленні після normal push, щоб не створювати self-referential hash усередині commit.
