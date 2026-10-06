# M40 — M24 Perfect Comparison / Narrative Tenses / B1 Mixed Revision

Дата роботи: 2026-10-06. Scope: три exact accepted UK M24 owners, тільки `http://gramlyze.loc`.

Зміни застосовано до робочого gramlyze.loc. Finite projection, author/native fidelity, guarded transaction, postconditions, два no-op, proof-route cleanup та actual browser acceptance PASS. Production не перевірявся й не змінювався. Commit/push — лише в робочу M40 гілку; фінальні SHA та GitHub links наведено в handoff.

## База, immutable source та межі

Accepted base **`d92d759a5c950e0f9eabd2f0fa95557327f42005`**, після M39 Practice UI Quality; base branch `codex/seo-m39-m23-authored-revision-layers`. Working branch `codex/seo-m40-m24-tenses-b1-layers`. Не стартує від старого449f і не втрачає M39 UI fix.

M24 master `docs/content/m24-authored-content.v1.json`: Git blob **`2d7c450533795bdab3267bd029b54fb325ff7cbb`**, declared Git-LF SHA **`33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2`**, unchanged Windows CRLF raw SHA **`1f43fa07a98cbc4144abbc02ea3909d8d2d8d04589f25d225a380a256db42e4d`**. Git-LF і raw identity різні; working EOL не переписується.

Frozen notes `docs/content/m24-author-sources.md`: blob **`3330b22b22e401eec9c36275651e60645b4b87f6`**, raw SHA **`719e9528112a4ed7a581f8aff8a1e3d1dae56d76d9e8b4b95b295be6bb4d6f5a`**. Обидва documents unchanged. Rewrite/summarise/translate_again/add_examples/add_exercises/production_write/mixed_question_bank_write false. Це technical integration, не нова мовна редактура/незалежна граматична експертиза.

ROOT historical author documents відсутні й не копіюються. Before manifest binds exact accepted Git-LF master snapshot; actual source files independently checked when present. No private-worktree runtime dependency, no repaired/reinvented master. Immutable source → accepted definitions → actual pre-apply DB gate passed before source changes; conflicting versions не обирались.

No main/PR/force/deploy/workflow dispatch, dependency update, Apache/XAMPP/hosts change, working seeder/migration/full restore/cache/session clear. Primary dirty work preserved: ROOT HEAD `41820a2bebdf69004fa7209a2a38457f93efabbd` не змінено; робота Git ізольована у managed worktree, accepted d92 є ancestor. Normal push потребує remote==HEAD; фінальна перевірка зберігається у приватному Git handoff, не в runtime/secret файлах репозиторію.

## Exact targets та block structure

| Page / theory | Primary test | Identity suffix under `Database\Seeders\Page_V3\` | Root / level / page ID | Accepted definition blob | Definition blocks before→after |
| --- | --- | --- | --- | --- | --- |
| [Present Perfect vs Present Perfect Continuous](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous) | [Тест](http://gramlyze.loc/test/tenses/present-perfect-vs-present-perfect-continuous) | `Tenses\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder` | tenses / A2–B1 /189 | `e4a950880b6f4fc0e1b865edc886c658805ffd3c` | 9→11 |
| [Narrative Tenses](http://gramlyze.loc/theory/tenses/narrative-tenses) | [Тест](http://gramlyze.loc/test/tenses/narrative-tenses) | `Tenses\TensesNarrativeTensesTheorySeeder` | tenses /B1 /280 | `55317a6a7bb48a640e804ec613afb83bdca05373` | 7→9 |
| [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision) | [Тест](http://gramlyze.loc/test/mixed-revision/b1-mixed-revision) | `BasicGrammar\BasicGrammarB1MixedRevisionTheorySeeder` | mixed-revision /B1 /327 | `8b6422baea0cf0c9f4ec1ca1ed921b9fab91c53d` | 5→7 |

Narrative Page.title remains `Narrative Tenses: Past Simple, Past Continuous and Past Perfect`; H1/subtitle strong remains **`Narrative tenses`**. Perfect/B1 Page.title and H1 original casing unchanged. Root categories/ancestry/locale/levels/tags/test relations protected.

Existing native blocks **8 /6 /4** are copied as complete exact configs, including **RAW body strings**. No flatten/regenerate/reorder/type/label/row/note/color/link changes. Existing comparison tables, forms grids, usage, mistakes, summaries, navigation remain visible basic. Navigation is page-footer content, not standalone content-block fallback.

Only appended `m24-authored-practice` box becomes:

| Owner | A: visible usage-panels intro | B: visible usage-panels intro | C: practice-set |
| --- | --- | --- | --- |
| Perfect | 8. Прочитай невелику ситуацію | 9. Що зберігати у відповіді | 10. Самоперевірка: шість завдань |
| Narrative | 6. Розбираємо коротку історію | 7. Перевіряй часовий зміст | 8. Самоперевірка: шість завдань |
| B1 | 3. Малий текст із кількома конструкціями | 4. Як оцінити власну відповідь | 5. Самоперевірка: шість ситуацій |

B1 existing navigation title remains unnumbered; it does not advance author section numbering. A/B HTML preserves exact source paragraphs/examples/translations; no hidden duplicate of the old whole box. Old final-box config re-used for A with only type/body change; deterministic `m40-*` UUID keys for B/C.

Pre-apply actual last-box IDs/UUIDs/orders: Perfect12296 /`cbfde3b3-8608-58d0-b3d4-c91e77d68163` /9; Narrative12297 /`d8069e93-67c5-5f82-8099-d5c9fa0f4919` /7; B112298 /`552324ad-7293-54fc-8fa8-3405b5070aa6` /5. Ці IDs/UUID/order збережені. Нові B/C rows: Perfect12622/12623 (orders10/11), Narrative12624/12625 (8/9), B112626/12627 (6/7). Subtitle/hero/existing native та всі EN/PL rows незмінні.

Legacy targets `lesson-block-{old-final-box-ID}-section-1/2/3` are relative box sections, **not** author numbers8/9/10. Finite presentation returns derived old-box UUID +legacy section1/2/3 so dynamic retained old ID resolves. Original self-check IDs retained: `self-check-m24-perfect-comparison`, `self-check-m24-narrative-tenses`, `self-check-m24-b1-mixed-revision`. Усі4 anchors кожної сторінки унікальні й відкриті real fragment GET у12 page states; actual DB reference inventory0, anchors збережені.

Before snapshot SHA **`67cb3dfe2f499479d7249df55e92c7a53bfc25c1afbf9db79e3992b71c390036`**; projection `m40-m24-tenses-b1.v1.json` SHA **`3b0734505576ff7628cd767c15f61c17aa9df59aa5e7726cddd44d1e1155ec9f`**. Pure generation validated all before states and actual bank artifact SHA **`a8f56124ceec15d900ba11d2453c942ca3b74696ba04dabb06ebb900abda4cab`**; final readonly check after/after/after. Final definitions SHA Perfect`fe9f11056be176f59702f5220ff7f89d2c743e8a5e35acc2a1b417465c6234eb`, Narrative`8ceb6da3795e78d47dff3fc7fab66d5604f4d8723f9fecf92af28a34509b24a8`, B1`65e9b8a8146e3c656ceab268f12376e1db296ef74638e1af41777e78ca9e4c5a`.

## Semantic detail audit — усі52 candidate units

Немає preset disclosure count або global runtime word threshold. Audit exhaustively covers structural warnings/table notes/usage notes, complete native summary units (including core material), explicit form/context supplements, and final paragraphs of two extracted sections per page. Every decision **visible_basic**; long B1 summary units contain essential review directions/examples/translations and cannot be hidden merely because long. Counts diagnostic only.

Path notation below is exact relative to a master lesson: `bN` = `existing_blocks[N].replacement_body_json`; `preA/preB` = `append_blocks[0].body_html#pre-section-1/2/final-paragraph`. Root section title excluded from context word count; learner labels/items/examples/translations included; color/level/url/href/current/icon metadata excluded. Block boundaries separated, entities/NBSP decoded; no punctuation/casing/numbers/order normalized away.

Reasons: **T** core table column explaining its bilingual example; **W** essential warning against mechanical rule; **U** core completion/recent-activity scope; **S** entire essential summary unit/model/translation or short caveat; **F** core form/reference boundary; **I** short instruction/overview boundary; **A** analysis required to understand the preceding story; **B** short facts/chronology instruction.

| Page | Exact path shorthand | basic_word_count | detail_word_count | detail_sentence_count | Decision / reason |
| --- | --- | ---: | ---: | ---: | --- |
| Perfect | b1.warning |151|24|2|visible_basic /W |
| Perfect | b1.rows[0].note |166|9|1|visible_basic /T |
| Perfect | b1.rows[1].note |170|5|1|visible_basic /T |
| Perfect | b1.rows[2].note |166|9|1|visible_basic /T |
| Perfect | b1.rows[3].note |167|8|1|visible_basic /T |
| Perfect | b1.rows[4].note |166|9|1|visible_basic /T |
| Perfect | b1.rows[5].note |167|8|1|visible_basic /T |
| Perfect | b3.sections[1].note |252|27|3|visible_basic /U |
| Perfect | b3.sections[2].note |258|21|2|visible_basic /U |
| Perfect | b5.items[0] |131|28|3|visible_basic /S |
| Perfect | b5.items[1] |120|39|4|visible_basic /S |
| Perfect | b5.items[2] |118|41|4|visible_basic /S |
| Perfect | b5.items[3] |136|23|2|visible_basic /S |
| Perfect | b5.items[4] |131|28|4|visible_basic /S |
| Perfect | b6.items[0] |109|23|2|visible_basic /S |
| Perfect | b6.items[1] |113|19|2|visible_basic /S |
| Perfect | b6.items[2] |109|23|3|visible_basic /S |
| Perfect | b6.items[3] |106|26|2|visible_basic /S |
| Perfect | b6.items[4] |112|20|2|visible_basic /S |
| Perfect | b6.items[5] |111|21|2|visible_basic /S |
| Perfect | preA (section8) |68|26|3|visible_basic /A |
| Perfect | preB (section9) |0|26|3|visible_basic /B |
| Narrative | b1.warning |159|26|2|visible_basic /W |
| Narrative | b1.rows[0].note |180|5|1|visible_basic /T |
| Narrative | b1.rows[1].note |174|11|1|visible_basic /T |
| Narrative | b1.rows[2].note |179|6|1|visible_basic /T |
| Narrative | b1.rows[3].note |176|9|1|visible_basic /T |
| Narrative | b1.rows[4].note |178|7|1|visible_basic /T |
| Narrative | b1.rows[5].note |177|8|1|visible_basic /T |
| Narrative | b2.items[0].subtitle#last-sentence |146|6|1|visible_basic /F |
| Narrative | b2.items[1].subtitle#last-sentence |139|13|1|visible_basic /F |
| Narrative | b2.items[2].subtitle#last-sentence |143|9|1|visible_basic /F |
| Narrative | b3.items[0] |226|33|3|visible_basic /S |
| Narrative | b3.items[1] |226|33|4|visible_basic /S |
| Narrative | b3.items[2] |225|34|4|visible_basic /S |
| Narrative | b3.items[3] |226|33|3|visible_basic /S |
| Narrative | b3.items[4] |221|38|3|visible_basic /S |
| Narrative | b3.items[5] |229|30|2|visible_basic /S |
| Narrative | b3.items[6] |221|38|3|visible_basic /S |
| Narrative | b3.items[7] |239|20|2|visible_basic /S |
| Narrative | preA (section6) |78|27|4|visible_basic /A |
| Narrative | preB (section7) |0|39|3|visible_basic /B |
| B1 | b1.items[0] |402|74|10|visible_basic /S |
| B1 | b1.items[1] |414|62|7|visible_basic /S |
| B1 | b1.items[2] |406|70|8|visible_basic /S |
| B1 | b1.items[3] |400|76|9|visible_basic /S |
| B1 | b1.items[4] |402|74|8|visible_basic /S |
| B1 | b1.items[5] |356|120|13|visible_basic /S |
| B1 | b2.intro |110|38|3|visible_basic /I |
| B1 | b2.items[3].subtitle |118|30|3|visible_basic /I |
| B1 | preA (section3) |79|39|4|visible_basic /A |
| B1 | preB (section4) |0|35|4|visible_basic /B |

Total52 =22/20/10; **32 short candidates (<30) =20/12/0**, all kept basic. Disclosures **0/0/0**. Native JSON itself untouched; no additional author material to manufacture details. Projection has explicit reasons and source values for every unit, independently checked against master diagnostics.

## All18 practice tasks — finite source mapping

No mechanical2+2+2. **32 controls =10select +6choice +16manual**, with **12 compound cases**. Full original18 prompts and18 explanation HTML entries preserved once in `author_self_check`, not candidate/fieldset/token/input value. SourceIndex1–6 per owner; whole sourceIndex score requires every control.

### [Perfect comparison](http://gramlyze.loc/theory/tenses/present-perfect-vs-present-perfect-continuous#self-check-m24-perfect-comparison)

| № / original title | Interaction / short candidates / correct canonical answer |
| --- | --- |
|1. Готові результати й процес.|Compound two form selects. Both candidates `has packed` / `has been packing`; Oleh correct`has packed`, Marta correct`has been packing`. Exact4 finished parcels/40-minute ongoing activity/unknown Marta count retained. |
|2. Тривалість чи початкова точка?|Compound two short selects: `for` / `since`; twenty minutes→for, September→since. |
|3. Знання, що триває.|Manual/token: `I have known the password since Monday.` |
|4. Щойно завершена активність.|Compound choices: contradiction candidates`Ні`/`Так`, correctНі; all chairs ready candidates`Невідомо`/`Так`/`Ні`, correctНевідомо. No completion count inferred. |
|5. Два природні варіанти.|Compound choices: both natural? `Так`/`Ні`→Так; intention to move? `Ні`/`Так`→Ні. |
|6. Коротке повідомлення.|Manual two-sentence edit: `I have already translated the introduction. I have been translating the second chapter for two hours, but it is not ready yet.` |

P6 finite equivalent: same answer ending **`but it is not yet ready.`**. Both preserve original first-person prefixes, introduction completed, second chapter/two hours/unfinished; no third translated section. Full explanatory keys, including recent activity/stopping/live distinctions, are separate after check.

### [Narrative Tenses](http://gramlyze.loc/theory/tenses/narrative-tenses#self-check-m24-narrative-tenses)

| № / original title | Interaction / short candidates / correct canonical answer |
| --- | --- |
|1. Основна послідовність.|Manual/token: `Yesterday I unlocked the gate, switched on the light and opened the window.` |
|2. Дія в процесі.|Compound3 controls: process`was sorting`/`had sorted`→was sorting; arrival`arrived`/`was arriving`→arrived; stopping`Не сказано`/`Так`/`Ні`→Не сказано. |
|3. Раніше за минулу подію.|Manual/token: `When we arrived, the courier had already left.` |
|4. Не кожна різниця — помилка.|Short choice`Так`/`Ні`→Так: both given after-forms can convey stated order, explanation not inside option. |
|5. Виправ лише граматичну форму.|Compound two manual corrections: `Did she open the box?` **and** `The guide had gone home before I called.` |
|6. Мініісторія.|Manual3 sentences: `Someone had removed the note before I arrived. Olena was talking on the phone when I arrived. Then I opened the cupboard.` |

Source-explicit finite aliases:

- N1 shorter split: `Yesterday I unlocked the gate. I switched on the light and opened the window.` Same actor/three events/order.
- N3 already optional: `When we arrived, the courier had left.` Source explicitly allows it; no reason invented.
- N6 reordered first two sentences: `Olena was talking on the phone when I arrived. Someone had removed the note before I arrived. Then I opened the cupboard.` Same chronology, unknown remover stayssomeone, no finished phone call.

### [B1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/b1-mixed-revision#self-check-m24-b1-mixed-revision)

| № / original title | All required compound parts |
| --- | --- |
|1. Результат до зараз і раніша минула подія.|Two selects: `have written`/`wrote`→have written; `had prepared`/`prepared`→had prepared. Both forms explicitly requested. |
|2. Дві майбутні точки.|Two selects: `will be packing`/`will pack`→will be packing; `will have finished`/`will finish`→will have finished. |
|3. Непряме запитання й вказівка.|Two manual clauses: `Mila asked me if I was ready.` **and** `She told me not to open the parcel.` |
|4. Вимога та замовлена послуга.|Two manual transformations: `The boxes must be labelled by the volunteers.` **and** `I had my bicycle repaired yesterday.` |
|5. Додаткова інформація та although.|Two manual edits: `Marta, who lives nearby, agreed to help.` **and** `Although it was late, we continued.` |
|6. Факт чи уявна ситуація?|Three manual parts: `I wish I had a bigger desk.` **and** `If we had left earlier, we would have caught the bus.` **and** `Lena might be in the reading room.` |

B4 explicit AmE spelling alias **`The boxes must be labeled by the volunteers.`**. Must/volunteers/requirement preserved; bicycle repair remains requested service, not speaker’s self-repair. B5 commas/although/agreed status preserved. All3 B6 parts required: current unreal wish, hypothetical past, uncertain might; none converted to fact/must.

## UI quality, logical tokens та semantic negatives

Architecture: prompt→answer-only controls→check→correct/wrong→separate exact author key. No full key, translation, rationale or prompt duplication in selectable answer. Independent candidate whitelists and regression fixtures, not arbitrary character limit. Perfect4 `Ні` and Narrative4 `Так` never carry their rationale; B1three-part key never becomes a giant button.

All16 manual controls use canonical logical groups1–3 words, exact concatenation, no sentence boundary crossed, intact possessives/articles/aux chains where possible. Runtime shuffles once; repeated instances distinct. Manual/aliases/terminal punctuation/tokens/Backspace/autocomplete-off/computed natural casing і keyboard PASS actual browser. Keys initially hidden, post-check exact, reset hides, no-JS native reveal accessible. MAIN особисто переглянув усі36 initial PNG; gate `m40-pre-v1-main-review.json` перед функціональним прийманням. Потім72 correct/wrong PNG: разом108 M40 practice states.

Independent PHP **38 actual author-fragment mutations** cover required count/duration/know/for-since/completion/intention, narrative chronology/actor/stop/did/gone, B1 tense/order/not-to/passive/agent/causative/commas/although/wish/conditional/might, plus3 translations. No absent-string fake negatives. Node initial31 finite manual negatives plus **42 supplementary mutations =8Perfect/16Narrative/18B1** derived from real control answers PASS. Усі42 додаткові мутації також відхилено LIVE. У26 окремих omission checks кожна підчастина12 compound потрібна; повна відповідь після відновлення приймається. Projection corruption/full-key leak/missing compound part/native change/wrong metadata/UUID fail closed.

## Actual banks — not assumed48/192

Read-only inventory `m40-bank-inventory-v1.json` SHAa8f561... established actual primary ownership before projection. All three primary banks B1/type4, even though Perfect page levelA2–B1. Full prefix `Database\Seeders\V3\Polyglot\`.

| Page | Primary class | Own / all linked | Exact own IDs |
| --- | --- | ---: | --- |
|189 Perfect|`PolyglotPresentPerfectContinuousVsPresentPerfectLessonSeeder`|24/168|15280;16310–16329;16331–16332;19273 |
|280 Narrative|`PolyglotNarrativeTensesBasicsLessonSeeder`|24/168|16358–16372;16374–16380;19263–19264 |
|327 B1|`PolyglotFinalDrillB1LessonSeeder`|48/192|639;650;15278;16330;16351;16357;16373;16382;16392;16402;16408;16415;16426;16432;16434;16448;16450;16581;16707;16803–16831 |

Each owner additionally has72 type0 AllLevelsV3 and72 type4 AllLevelsPolyglot, actual12/each A1–C2, not own widget. V3 prefixes/names: `Tenses\Comparisons\PresentPerfectVsPresentPerfectContinuousAllLevelsV3Seeder`; `Tenses\Extra\NarrativeTensesAllLevelsV3Seeder`; `MixedRevision\B1MixedRevisionAllLevelsV3Seeder`. Polyglot names `PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder`, `PolyglotNarrativeTensesAllLevelsLessonSeeder`, `PolyglotB1MixedRevisionAllLevelsLessonSeeder`. Questions/answers/options/verb_hint/pivots/progress/saved tests unchanged. У12 live page states віджет показав5 випадкових IDs тільки own primary bank/type4; усі належать exact24/24/48 lists. Final SELECT повторно підтвердив banks/pivots незмінними.

## Tests та historical fixture corrections

| Run | Actual result |
| --- | --- |
| Own2-class v1 |47tests/1281assertions, FAIL1,12.701s60MB; standalone fixture wrongly sent footer navigation through content-block fallback. No app/source change. Protected46648files changes0. Retained separately. |
| Own2 +historical M24/native classes v2 |**63tests/1827assertions PASS**,exit0,17.345s66MB,1existingPDOdeprecation; protected46648files changes0. |
| M40 guard/patch |**60 tests /158 assertions PASS**,exit0,03:36.307,68MB;1existingPDOdeprecation,46648 protected files changed0 |
| M39 package/author regression |**57 tests /1612 assertions PASS**,08.730s,62MB;1existingPDOdeprecation |
| M39 guarded UI patch/local-target regression |**41 tests /113 assertions PASS**,02:36.831,66MB;1existingPDOdeprecation |
| Node / M39 full practice regression |**230/230 PASS**,5394.507ms final MAIN rerun;3 targeted files |
| Relevant Vitest |**43/43 PASS**,5 files,childexit0,107.17s;stderr empty |
| Final JSON/PHP/JS/diff checks |**45 related files syntax/JSON PASS**; finite generator after/after/after reproduces exact snapshot/definitions; diff/private-source review before normal commit |

Historical M24 source tests now use accepted M24 data from SHA-bound M40.before and separately verify current canonical==M40.after. Master checksum checks Git-LF SHA+blob, never rewriting CRLF master. Isolated historical seeder fixture seeds accepted before data in `:memory:` only. Unified original-native no-disclosure guards retained; real-identity M40 practice separately asserts exact6 author keys, not zero summaries. Footer navigation remains page-template responsibility. M24NativeTitleNumber remains exact3-owner scoped; original numbering/unnumbered nav preserved.

Exact v2 outer command (cwd isolated worktree):

```powershell
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m40-author-package-historical-v2 tests/Feature/M40AuthorFidelityTest.php tests/Feature/M40TensesB1PackageTest.php tests/Feature/AuthoredNativeContentPackageTest.php tests/Feature/UnifiedTheoryNativePresentationTest.php
```

Evidence: `storage/app/seo-m2-local/m40-author-package-historical-v2-a684f04df28d4a6f83c27f17fd48fc82-result.json`. Isolated memory/storage, no working.env/DB write. ExistingPDOdeprecation reported; separate PHP/Node/Vitest runs not added to invented aggregate.

## Guarded local apply, live acceptance та final handoff

Physical local/vhost/CLI-web proof PASS (2026-10-06T13:04:29Z): ROOT `D:/DEV/htdocs/gramlyze.loc`, public document root, local Windows MySQL `gr2` on localhost:3306. APP environment production, effective SiteMode development for .loc. Safe runtime fields only, nonce-bound digest; no secrets/cookies/auth/forwarding.

Fresh `m40-preview-v1.json` digest **`feffcec8156b01302c631f72280fd6f0bac0caea07db53c6928413783d846f9b`**; independent exact-field review PASS. Actual transaction **3 updates /6 inserts /0 deletes**, only old final box type/body plus new B/C. Exclusive DB backup **`D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m40-local/m40-backup-v1.json`**, byte-exact preview, raw SHA **`ee3587970ce3c62819143c689416a373e55a66f38948e29dfa6f95d79682442e`**. Two repeated apply runs returned no-op0/0, unused backups absent.

Source backups: `bootstrap-source-backup-bd179666353ae447` (3 initially absent files) and `source-backup-1dc209ac95b8828c` (23 finite source records); six original shared ROOT bytes preserved before exact hunks. Important distinction: archived ROOT definitions match historical Git41820 (8/6/4 blocks), while **accepted M24 definitions** (9/7/5) are independently frozen in versioned before manifest67cb and match actual pre-apply DB/master. They are not conflated. ROOT PPC shared-template changes were preserved, not overwritten from WT. Frozen M24 documents were not copied to ROOT.

Postconditions `m40-after-v1.json` SHA `c498e878294459f130e9c4f4347dd33602201db5d29a86bbf581dd37105430c8`; final after ALL browser requests `m40-after-noop-v2.json` SHA **`12521caa62fb51380d1146c5247424064db1b9e862766e7ae90f237d3deae4ae`**. Actual text_blocks6552→6558. **6549 non-target blocks**, digest `d428227742093b993735f62071fd5439ec8bd76d94f529c579d701ff95dae419`, unchanged;24 fingerprints include23 unchanged other tables, all4 progress/review/state tables, and44 previous owners. Perfect all rows28→30 (UK10→12, EN9/PL9 preserved); Narrative8→10; B16→8. Final snapshot equals post-apply except capture time.

Temporary GET loopback route removed via exact patch. Raw routes/api.php SHA restored **`2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`**; guest GET old nonce returned404. No temporary route committed. Read-only diagnostic schema-discovery/variable-shadow failures produced no DB writes or partial evidence; corrected and final independent verifier PASS.

Live evidence PASS: **12 page states** (1440×1000 /390×844, light/dark) та **108 practice visual states =36initial+36correct+36wrong**. All18 tasks: source-only candidates, natural casing, wrapping, compound/manual/token/history/Backspace/aliases/terminal punctuation, exact post-check key/reset/keyboard; scores6/6→0/6. Source→definitions→actual DB→serverHTML→reloadDOM fidelity PASS; complete native18/config/titles/footer links, four legacy anchors per page, reload starts closed, print retains basic, own banks sampled. MAIN personally reviewed36 PRE images,12 page-top images, six representative POSTs та corrected four mobile-table right-column images.

Private artifacts under `storage/app/seo-m40-local`: `m40-pre-v1-visual.json`, `m40-pre-v1-main-review.json`, `m40-post-v1-acceptance.json`, `m40-pages-v3-pages.json`, `m40-supplemental-v1-supplemental.json`, `m40-supplemental-v1-http.json`, `m40-table-v1-tables.json`. No console/HTTP/font/local-network errors or unclassified violations. Screenshots/proofs/DB backups remain private and are not Git artifacts.

No-JS PASS on all3 M40 and3 M39 pages:36 exact keys, native mouse/Space/Enter reveal; six intentional script-CSP blocks classified, no unclassified failure. **All18 M39 practice cases** also passed correct/wrong/reset/token/manual/aliases/keyboard, including Had+subject+not semantic constraint. Generic authored UI mechanics are shared; finite M39 and M40 wrappers remain separate, and no M40 owner is inferred through the M39 package.

Historical11 M26–M29 pages/22 hidden no-JS token banks outside scope, not repaired or claimed fully usable. [Course lesson](http://gramlyze.loc/courses/english-grammar-theory/lesson/tenses/present-perfect-vs-present-perfect-continuous) GET200; H1 exact, server source6 tasks/6 keys/content present. This is **server-properties-only**, not course gate bypass or browser-visible course acceptance.

Learning main/cards overflow **0px**. Existing random decorative document overflow on six mobile states12/12/6/1/4/2px (max12; decorative rect max15.78px), reported separately, not called0. Background/global overflow hiding unchanged. Mobile tables client322/scroll576 use local auto-scroll. Four additional real Chromium keyboard states proved native focus/focus-visible and ArrowRight0→254px **without forced scrollLeft**. This is this Chromium evidence, not an older-browser/Tab guarantee.

52 guest local GETs before and after PASS: all3 theory/tests,45 unchanged M26–M39 controls, course. Title/description/H1/canonical/robots/X-Robots/OG/Twitter/content-type unchanged; control main payload/detail counts unchanged. Ordered sitemap554 entries, SHA **`6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`**, before==after; count observed, not imposed. .loc X-Robots noindex/nofollow/noarchive preserved; canonical.com values read only from local HTML, no production request.

Diagnostic limitations retained transparently: sandbox baseline v2 produced ENOTFOUND, superseded by successful unsandboxed52GET baseline v3; pages-v1/v2 used wrong historical footer selectors, corrected only in diagnostics. Four pages-v3 right captures after End showed next section, **not** table keyboard evidence; superseded by four correct bounded `m40-table-v1-*-table-right.png` captures and genuine ArrowRight measurements. There are172 raw successful-run PNG files;168 useful acceptance images after replacing those4, including108M40practice+24page+36M39regression.

M26–M39 disclosure baseline56/13/0/0/0/2/1/2/2/0/0/0/0/0 protected by44 actual owners and45 HTTP controls; M40semantic result0/0/0. Final post-browser SELECT/table/owner regression evidence PASS as above.

Git handoff uses explicit related staging and staged-diff/source/private audits. Immutable documents unchanged; no.env/dumps/backups/screenshots/proofs/runtime/vendor/build/temporary route/foreign work staged. Only `codex/seo-m40-m24-tenses-b1-layers`, normal commit/push; full final SHA/commit/report URLs and remote==HEAD confirmation supplied in final handoff. No main/PR/force/deploy or automatic next package.

Vitest child43/43 PASS evidence persisted before Python wrapper console-print failed on Windows cp1251 UnicodeEncodeError for ✓. Childexit0/stdout/stderr were independently reread; diagnostic wrapper failure is not called a Vitest failure. Earlier failed fixture/selector/capture attempts are retained privately, not counted as final PASS.

Production не перевірявся й не змінювався.
