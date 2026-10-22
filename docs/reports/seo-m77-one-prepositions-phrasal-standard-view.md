# M77 — One / Ones, прийменники й базові фразові дієслова

Статус: шість сторінок застосовано локально, фактичне браузерне приймання завершено. Автономне HTML-прев’ю не зараховувалося як приймання й не потребувало нового погодження користувача.

Прийнята база: `5270494e0f999eee2a3d1fba6fb7c3f46a914481`. Гілка: `codex/seo-m77-one-prepositions-phrasal-standard-view`. Робоче дерево доставки відокремлене від сторонніх ROOT-змін. HTTP/UI — тільки `http://gramlyze.loc`. Main, PR, production і deploy не змінювалися.

## Сторінки та результат

«Форма / зміст / речення» — власні graded tasks; п’ять питань окремого конструктора рахуються окремо. Кожен full-sentence input працює вручну та токенами.

| Сторінка | Page / category | Форма / зміст / речення | Practice SQL level | GET | Widget | Стани / manual-token |
| --- | --- | --- | --- | --- | --- | --- |
| [One / Ones](http://gramlyze.loc/theory/zaimennyky-ta-vkazivni-slova/one-ones) | 123 / 20 | 8 / 2 / 2 | NULL, збережено | 200 | 5/5 | 6 / 8 |
| [Prepositions of Time](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/prepositions-of-time) | 120 / 19 | 2 / 2 / 2 | A1, новий блок | 200 | 5/5 | 6 / 8 |
| [Prepositions of Place](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/prepositions-of-place) | 119 / 19 | 2 / 2 / 2 | A1, новий блок | 200 | 5/5 | 6 / 8 |
| [Prepositions of Movement](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/prepositions-of-movement) | 118 / 19 | 2 / 2 / 2 | A2, новий блок | 200 | 5/5 | 6 / 8 |
| [Dependent Prepositions](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/dependent-prepositions) | 115 / 19 | 2 / 2 / 2 | B1, новий блок | 200 | 5/5 | 6 / 8 |
| [Phrasal Verbs Basics](http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/phrasal-verbs-basics) | 116 / 19 | 2 / 2 / 2 | B1, новий блок | 200 | 5/5 | 6 / 8 |

Разом: **42 власні завдання = 8 перенесених + 34 нових**, 53 допустимі значення відповідей, 70 показаних варіантів, 12 inputs; **6 окремих widgets / 30 повторно використаних type-4 питань**. Нових DB-питань або question links не створено. H1 першої сторінки залишається **One**, raw title — One / Ones — Заміна іменника.

## Навчальні зміни

- One / Ones: зрозумілий обчислюваний референт, one/ones проти it/them, збереження означення, singular/plural be, питання й короткі відповіді. Не оголошуються універсальними помилками informal my/your one, these/those ones, some wooden ones, any smaller ones, two bigger ones. Вода як речовина відрізняється від названої пляшки чи порції. У таблиці справжній український зміст замість другої англійської форми й raw Unicode escapes.
- Time: at/on/in як орієнтири, не алгоритм за довжиною періоду; specific day + morning, at night / in the night, свято/день, BrE/AmE weekend, next week / in the next few days, два значення in two hours. Додано переклади й коротку межу by/until/since/for.
- Place: внутрішній простір, поверхня й точка/діяльність; at не означає «надворі». Контекстно розрізняються wall, home, picture, типова пасажирська модель on the bus і by bus; in a bus не забороняється глобально. Not in не називає іншого місця.
- Movement: пункт призначення, напрямок, перехід усередину/на поверхню, out of, through/across/along. Going to/towards самі не доводять завершене прибуття; природні on/onto та in/into приймаються в прямо заданому контексті.
- Dependent: adjective/verb/noun + preposition разом зі значенням; good at/for, agree with/on/to, прямий додаток після discuss, to-прийменник / to-інфінітив, збереження прийменника в питанні. Останній input прямо просить модель answer to, не називаючи інші моделі завжди неправильними.
- Phrasal: значення, перехідність і відокремлюваність — різні перевірки; займенник між частинами відокремлюваної сполуки, два природні порядки іменникового додатка; зміна частки відрізняється від багатозначності. Регістр залежить від виразу, не всі сполуки «завжди неформальні».

Пояснення, переклади, питання, заперечення й короткі відповіді залишено видимими. **0 point details** — явне змістове рішення для кожного пункту в finite author projection; глобального word-count порога немає.

## Канонічні джерела й дизайн

Frozen 1.0.0: [master](../content/m77-one-prepositions-phrasal.v1.json), [українська редакція](../content/m77-one-prepositions-phrasal.v1.0.0.uk.md), [components](../content/m77-one-prepositions-phrasal.components.v1.json), [task ledger](../content/m77-one-prepositions-phrasal.tasks.v1.json), [editorial review](../content/m77-one-prepositions-phrasal.editorial.v1.json), [bindings](../content/m77-native-bindings.v1.json), [bank audit](../content/m77-sentence-builder-bank-audit.v1.json), [builder contract](../content/theory-sentence-builders/m77-one-prepositions-phrasal.v1.json), [patch](../../database/content-patches/m77-one-prepositions-phrasal.v1.json).

Checksum manifest `ce5e2ad30a751fd318b21375347a4d61c2746195323233d3d088c1fa8face861`: 22 entries — 10 артефактів, 6 definitions, 6 author inputs. Авторські parts збережені окремо; finite projection відстежує технічні рівні й сім вилучень редакторських ремарок без зміни правил/відповідей. Після freeze навчальний payload не переписувався.

Нуль змін Blade/CSS/JS, formatter, grader або practice engine. 326 visual paths та 2662 historical frozen files збережені. M76 token-label fix (`every`, `shift`) чинний. Дев’ять shared files мають тільки 13 exact-owned hunks: finite реєстрації та hardcoded cardinality 3 → фактична кількість owners у source-contract validator, із відмовою для порожнього пакета. Це не нова нормалізація відповідей. [Reverse-proof fixture](../../tests/Fixtures/m77-owned-source-hunks.json) повертає всі дев’ять файлів до accepted M76 raw/LF bytes.

One: practice in-place, source index 8, UUID `3e5975ed-f03b-5639-b9fe-f5f0f051f734`, SQL NULL. Вісім старих HTML-пунктів перенесені зі збереженням функції/відповідей у selects; додано чотири нові. Дубльованого статичного готового ключа немає. Hero anchor `block-8966` збережений.

Інші п’ять: старі indexes 0–5, columns header/left/left/right/right/footer, SQL levels і UUID не пересунуто; practice appended index 6 / left / sort 7, explicit A1/A1/A2/B1/B1. Праві mistakes/summary залишилися правими. Без DDL і переповнення SQL TEXT.

## Походження власних завдань

Точні умови, options, accepted values, feedback і функції — у master/task ledger. Це оригінальні завдання, не вправи видавців.

One old block8 HTML ol/li[1–8] → selects[0–7]: `one`, `ones`, `one`, `one`, `yours`, `ones`, `some`, `this one`. Старих id/source_index у пунктах не було, вони не вигадані заднім числом.

| Сторінка | 34 нові task IDs |
| --- | --- |
| One | `m77-one-meaning-it`, `m77-one-meaning-bottle`, `m77-one-sentence-negative`, `m77-one-sentence-question` |
| Time | `m77-time-form-clock`, `m77-time-form-day-morning`, `m77-time-meaning-no-preposition`, `m77-time-meaning-future-point`, `m77-time-sentence-next-negative`, `m77-time-sentence-weekend-question` |
| Place | `m77-place-form-inside-bag`, `m77-place-form-clock-wall`, `m77-place-meaning-cinema`, `m77-place-meaning-not-in`, `m77-place-sentence-where-keys`, `m77-place-sentence-children-home` |
| Movement | `m77-movement-form-cup-surface`, `m77-movement-form-jump-interior`, `m77-movement-meaning-arrival`, `m77-movement-meaning-river-route`, `m77-movement-sentence-towards-station`, `m77-movement-sentence-out-of-house` |
| Dependent | `m77-dependent-form-good-at`, `m77-dependent-form-reason-for`, `m77-dependent-meaning-agree`, `m77-dependent-meaning-good-for`, `m77-dependent-sentence-depend`, `m77-dependent-sentence-answer` |
| Phrasal | `m77-phrasal-form-question`, `m77-phrasal-form-look-after`, `m77-phrasal-meaning-pickup`, `m77-phrasal-meaning-takeoff`, `m77-phrasal-sentence-pronoun`, `m77-phrasal-sentence-noun` |

Банки inputs перемішані, chunks по 1–3 слова. Приймаються оголошені aliases: скорочене/повне заперечення, BrE/AmE weekend, children/kids, toward/towards, формальне On what…, два порядки noun object. Порожнє поле не є правильною «відсутністю прийменника»: її перевіряє непорожній варіант або повне речення.

## Guarded local apply

Незалежний BEFORE до реєстрації: 12 theory/course GET200, 46 повних table fingerprints, 326 visual / 2662 frozen files; SHA `3ee7c26081727dd746dfe1e94a03dcc4273c48bcb40be5091b84d27bc52cab31`. Freshness gate перед apply повторно звірив 46 row fingerprints і 11 реально захоплених schema snapshots; не заявлено історичну перевірку всіх 46 schemas.

Фактичні served application/document root та локальна web/CLI DB identity підтверджені свіжим read-only GET nonce proof, тільки gramlyze.loc/loopback, без секретів. APP environment не використано окремо як доказ production-цілі: перевірено SiteMode і фізичну локальну identity. Private runtime identity у Git не входить.

1. Fresh preview `preview-v1.json`: canonical digest `2fc24f4845c8fa843da5efb678f5958cbe0de7e9bb42a70b2f6a9d7e9e96bf2f`.
2. Exact review: 6 Page.text + 47 UK body/subtitle updates; 57 metadata fields на 29 тих самих рядках; **53 update operations / 53 distinct old rows + 5 inserts**, 0 deletes / question writes.
3. Exclusive `backup-v1.json` — точні reviewed-plan bytes, SHA `8e97d50491542bd5887fb64c06c4d119d9941220a5215b73b3b5306263abc082`.
4. Scoped transactional CLI apply: **applied, updated 53, inserted 5, deleted 0**.
5. Незалежна SELECT-only реконструкція всіх BEFORE tables: дозволені зміни exact, решта rows/fields/timestamps збережені. 47 continuing UK та 74 foreign-locale blocks незмінні поза дозволеним diff; п’ять inserts мають очікувані identity/metadata.
6. Реальний повторний APPLY початкового плану: **no-op, updated 0, inserted 0, deleted 0**. `backup-noop-v1.json` не створений.
7. Nonce route видалений, routes file повернутий точно до власного BEFORE зі сторонніми bytes; окремий GET — **404, 0 redirects**. Cleanup SHA `29d077b9f8662a445a77bda25d24b20e6caccd4563d81b11d3f734c545f99f5a`.
8. Після ВСІХ UI-дій `final-readonly-v3.json` SHA `d63121fcdec2385c20bf0eb610a1a0049416bc8393f317f3a230d7e04b876ef8`: усі 46 AFTER fingerprints, включно з progress, exact щодо v1/v2; fullrow hashes/timestamps п’яти inserts незмінні. SQL writes / HTTP — 0 / 0.

Без mass reseed/migrations/truncate/reset/clean/відновлення всієї БД. ROOT дзеркалився тільки exact-owned helper’ом: shared hunks застосовані локально, цілі shared files не копіювалися; сторонній ROOT controller і mixed line endings збережені. ROOT `.gitattributes` не відновлювався й не копіювався.

## Сумісність, metadata, anchors і банки

- Старі consumers: 48/48 GET projections, 12 saved banks / 864 original questions, question families та course learning DOM збережені; AFTER SHA `d46fd833d380ab26cced2a4211d46d072ea37aee46339f6d3958ef1df6fa1686`. Повні dynamic HTTP bytes не проголошуються тотожними.
- Registry **151 → 157**, 0 failures / 0 writes. Усі 151 старі structural rows exact після виключення лише full dynamic HTTP-page hash; рівно шість нових owners. Preservation SHA `469136069da63cd19702a773d4b271e38e907cded9a99fd420c86e9572d1176f`.
- Targeted audit: 6 targets / 0 failures / 0 writes, exact UUID/type4/selected IDs/eligible pools. One NULL → pool72, інші focus pools по12, разом132. Вибрано30 існуючих; усі432 page-wide original type4 збережено.
- Navigation: 17/17 direct GET200 (6 targets,10 neighbours,1 category),39 clickable anchors,44 nav items +1 category-back =45; старі labels/current flags/destinations/anchors збережені. SHA `37f300d2d30b1961d3dda3249410657fd05ca935e7b05e5617793383e882e103`.
- H1/title/breadcrumb/canonical/local noindex і LearningResource identity збережені; descriptions exact щодо незалежної prospective projection. Canonical .com — тільки незмінене локальне metadata, production HTTP не було.
- Category metadata порівняно з двома незмінними native branches (tree ordering / cached-empty ordering), розрахованими до GET на незалежних raw clones. Matched cached-empty ordering. Кеші не очищалися/не змінювалися.
- Offline bank audit: **38 238 adversarial sequences** на незмінених ROOT/WT formatter/Check. 246 formatting-normalization matches на профіль не названі lexical acceptance; окремо48 synthetic matcher checks. Сумнівні кандидати виключені відбором без ремонту engine.

## Реальне браузерне приймання

**36 унікальних станів**:6 сторінок ×1440×1000 /390×844 /320×844 ×light/dark. У кожному підтверджено actual DOM viewport/theme, loaded Manrope, native компоненти/accents,3 practice groups,1 widget, learning overflow=0 та досягнення footer.

Широкі таблиці на390/320 мають штатний локальний scroll; його descendants не названі learning overflow. На One320 є невеликий decorative shell overflow, не вихід навчального тексту. Один проміжний Dependent screenshot повторно оглянуто в settled desktop-кадрі: звичайний native table, без зміни CSS/приховування контенту.

**48 унікальних full-sentence cases** =12 inputs ×manual/tokens ×desktop1440/mobile320. Прочитано settled input, feedback і score. Окремо перевірено70 показаних options (32 прийнятні /38 distractors),53 окремі task/accepted-value cases та42 graded tasks. Це46 distinct literal strings: однакове слово в різних завданнях перевіряється окремо. Повторні успішні harness-спроби не збільшують unique coverage.

Перевірено empty/whitespace, wrong→correct retry, повторний Check без подвійного бала, reset, незалежність scores, mixed manual+tokens, видалення/повернення/повторне складання токенів. У widgets — повернення першого токена в банк, читабельні labels, Next, прихований Check після правильного submit. Phrasal restart реально скинуто до✓0 і повторно пройдено до✓5.

Усі шість widgets завершено5/5; усі30 settled sentences прочитані після Check й звірені з finite reviewed projection:

| Widget | Existing ID | Фактичне видиме речення після Check |
| --- | --- | --- |
| One |47595| I want the red one, not the blue one. |
| One |47596| These shoes are old; I need new ones. |
| One |47597| I like this bag, but I prefer that one. |
| One |47599| The one on the left is mine. |
| One |47600| Do you want the small one or the large one? |
| Time |42831| The lesson starts at nine o’clock. |
| Time |42832| We have a meeting on Monday. |
| Time |42833| My birthday is in April. |
| Time |42834| I study in the evening. |
| Time |42840| I usually read at night. |
| Place |33183| She is in the kitchen. |
| Place |33185| The picture is on the wall. |
| Place |33186| I will meet you at the station. |
| Place |33192| Dad is at work now. |
| Place |33194| We are on the bus. |
| Movement |42915| We walked to the museum. |
| Movement |42916| She walked into the room. |
| Movement |42918| He came out of the house. |
| Movement |42919| We drove through the tunnel. |
| Movement |42923| She ran towards the door. |
| Dependent |42999| She is interested in music. |
| Dependent |43002| We depend on your support. |
| Dependent |43003| There is a reason for this delay. |
| Dependent |43006| What are you waiting for? |
| Dependent |43009| Who is responsible for the tickets? |
| Phrasal |43287| I usually wake up at seven before school. |
| Phrasal |43290| She did not give up after the first mistake. |
| Phrasal |43291| Please turn off the light. |
| Phrasal |43293| She will look after the children tonight. |
| Phrasal |43295| We ran out of milk, so I bought more. |

Private `browser-acceptance-v1.json` SHA `5fb6b0fb848bc0a34b2c88daf27dc941b2abead266ac8c19a8411c992b088bb9`; screenshots/observations не входять у Git. Theme light і viewport1280×720 відновлено.

Time мав початковий navigation timeout; та сама вкладка відновилася, URL/load/practice/матрицю перевірено — recovered, не blocked. Ранні harness-помилки (feedback parent class, stale closure, apostrophe locator escaping) виправлено тільки в приватній автоматизації; невдалі спроби не зараховані як PASS і не змінювали сайт.

## Автоматичні тести та baseline

PHP — isolated SQLite memory, без working .env/MySQL/progress writes;675 protected paths незмінні. Production profile — тільки ізольований. Skips у таблиці=0.

| Набір | BEFORE tests/assertions; F/E/W | AFTER tests/assertions; F/E/W | Deprecations / exit AFTER |
| --- | --- | --- | --- |
| Нові M77 | немає | **30/22258;0/0/0** |1/**0**|
| Active M76 + anchor/display |46/10575;0/0/0|46/10575;0/0/0|1/0|
| M75 |33/9522;1/0/0|33/9522;1/0/0|1/1|
| Wide preservation |136/27204;14/0/0|136/27203;**15/0/0**|1/1|
| Generic |30/14567;0/0/1|30/14609;0/0/1|1/1|
| Interaction |134/1588;8/1/0|134/1588;8/1/0|1/2|

M77 Content4,Runtime6,Builder6,ContentPatch9,OwnedSource4,Engine1 —30 methods. Final receipt SHA `c60b3f64bd272dd99c6dc26486a3cae5d9c138f9c52a5052adc3a79d5239656c`. Ранній negative fixture помилково додавав рядок до array hero content; виправлено тільки вкладений `content.html`, без послаблення guard. Final warnings=0; існуюча deprecation не прихована.

WT engine додатково1/29 PASS — той самий метод, не новий unique test. ROOT і WT offline Node contract: **4390 assertions кожен**,42 tasks/53 accepted/70 options/12 inputs; offline48 mode-size cases не замінюють фактичні48 browser cases.

**Одне нове wide-падіння:** `M74OwnedSharedSourceDeltaTest::test_other_three_primitives_remain_accepted_and_plain_subtitle_exception_is_not_a_new_normalizer_or_title_heuristic` очікує старий raw SHA TheoryBuilderSelectionPresentation після дозволеної generic owner-cardinality правки M77. Це не названо старим failure. M77 exact-owned13-hunk reverse-proof проходить; M74 тест не переписаний/не послаблений. Ще3 вже червоні messages змінили тільки actual SHA M77 shared sources (TheoryContentSource×2,controller×1);11 інших wide messages byte-identical.

Інші baseline-причини окремі: M75 old whole-file display assertion після погодженого M76 fix; M74 active-version/filtering/backup fixtures; prior frozen-source hashes; caller linked-practice expectation. Generic warning — `SavedTestStepWrongAnswerTest` без виявлених tests; +42 assertions від6 нових catalogue owners, не нові methods. Interaction:4 literal markup assertions,2 SavedTestClientAnswerLogic,PastPerfect compose fixture,SavedTestJsState та error через відсутню `question_hints` fixture — ті самі methods/messages. Широкі набори не оголошуються зеленими.

Шість основних AFTER наборів:409 test occurrences /360 unique methods;25 unique bad проти24 BEFORE. Додатковий WT engine не додається вдруге. Після UI source змін не було; весь baseline з нуля не повторювався.

## Редакторські джерела

Agent review не є людською рецензією; приклади/завдання оригінальні. Усі10 запитаних grammar URLs спробувано напряму. British Council adjective/verb/phrasal editorial bodies прочитані; Cambridge direct403, BC One timeout — прочитані окремо ідентифіковані офіційні indexed editorial bodies, без заяви про direct success. Learner comments не є доказом правил; LearnEnglish Team replies розрізняються з ними. Повний access ledger — editorial JSON.

- [British Council: one/ones](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/one-ones), [Cambridge: one](https://dictionary.cambridge.org/grammar/british-grammar/one), [substitution](https://dictionary.cambridge.org/grammar/british-grammar/substitution).
- [British Council: time](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2-grammar/prepositions-of-time-at-in-on), [Cambridge: time](https://dictionary.cambridge.org/grammar/british-grammar/at-in-on-time), [place](https://dictionary.cambridge.org/grammar/british-grammar/at-on-and-in-place), [in/into](https://dictionary.cambridge.org/grammar/british-grammar/in-into), [on](https://dictionary.cambridge.org/grammar/british-grammar/on), [across/over/through](https://dictionary.cambridge.org/grammar/british-grammar/across-over-or-through).
- [British Council: adjectives + prepositions](https://learnenglish.britishcouncil.org/free-resources/grammar/a1-a2/adjectives-prepositions), [verbs + prepositions](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/verbs-prepositions), [phrasal verbs](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/phrasal-verbs); [Cambridge: multi-word verbs](https://dictionary.cambridge.org/grammar/british-grammar/phrasal-verbs-and-multi-word).
- [Google: helpful content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content): змістовне доповнення без word quota чи гарантій ранжування/індексації.

## Межі й доставка

Prefreeze320 — **not_checked / deferred_to_real_after_apply** за прямим уточненням користувача. Шість справжніх сторінок після apply перевірено320px у двох темах. File-protocol security відмову не обходили; циклу дизайнерського погодження не створювали. Frozen prefreeze status не переписувався заднім числом; AFTER evidence окремий.

Не перевірялися: print,no-JS,повний WCAG-аудит. Реальних blocked M77 acceptance checks немає. .com/.ub сервери,Apache/XAMPP/hosts/ACL,залежності,progress,EN/PL не змінювалися; курси/окремі tests/test-help поза read-only regression scope не змінювалися.

Доставка — reviewed allowlist67 paths, включно зі звітом;14 старих untracked M75/M76 drafts виключені. Private raw diagnostics,backup,proof/nonce,screenshots,preview HTML,caches,секрети не входять у commit. Shared bytes не нормалізовано заради diff checker; per-command `core.whitespace=cr-at-eol` враховує збережений CRLF.

Commit, який містить цей звіт, належить лише M77-гілці. Після staged review виконується normal push і незалежна remote-SHA/HEAD звірка; фактичний SHA та результат доставки наведені у фінальному повідомленні. Без merge/main/PR/deploy.
