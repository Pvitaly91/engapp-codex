# M39 follow-up — Practice UI Quality

Дата локальної роботи: 2026-10-06. Scope: 18 original author tasks на трьох уже accepted M39 UK theory pages, тільки `http://gramlyze.loc`.

Правку застосовано до робочого `gramlyze.loc`: **3 existing practice body updates / 0 inserts / 0 deletes**. Усі 18 original tasks перевірені візуально на desktop/mobile до functional acceptance. Фінальні 36 functional case states, окремі compound/no-JS/regression перевірки та 49 guest HTTP GETs пройдено; author master і frozen V1 незмінні.

## Контракт і причина follow-up

Виявлена M39 UI regression: full author key, translation і rationale були використані як selectable answer; довгі rewrite tasks механічно підганялися під 2 select + 2 choice + 2 token/manual. Це не вважається правильною інтеграцією, навіть якщо original words технічно збережені й структурні тести PASS.

Latest task contract:

`prompt → короткі answer candidates → Перевірити → correct/wrong → окремий exact author explanation/key`.

Options містять тільки саму відповідь: без rationale/translation, службових критеріїв, дублювання prompt або full key. Answers/tokens/manual previews/explanations мають natural author casing, без `text-transform: uppercase`. Author translations/explanations не переписуються; вони переміщені в dedicated post-check/reveal area.

Interaction обирається за original task, а не механічно. 2+2+2 лишається цільовим балансом лише там, де не спотворює завдання. У цих M23 sources більшість tasks є rewrite/open edit, тому manual interaction пріоритетний. Заміна великої explanation-option на іншу величезну choice-option не вирішувала б проблему.

18 cases мають **23 controls = 17 manual + 1 select + 4 choice + 1 multi**. **14 manual cases + 4 compound cases**, кожен original source_index 1–6 на власній сторінці рівно once. Full original prompts/keys зберігаються solely в `author_self_check`; cases/controls не дублюють explanation/context/feedback.

## Immutable provenance та finite revision

Original M23 author master `docs/content/m23-authored-content.v1.json` не змінено:

- Git blob `34a03a7146141fbff50c66ec8e41f3fc2a59b787`.
- Declared Git-LF SHA-256 `eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6`.
- Actual unchanged Windows CRLF raw SHA-256 `3d919d5894ac37a11a107d31075078c419394a0b3f537f21e303523bd4213c32`.
- Frozen `docs/content/m23-author-sources.md`: Git blob `859c4263cb00c0ff318bf2b41f3e450ca65efc0d`; raw SHA `afe9ec2d6a5f10b496fc0fdcabcea1e546599f3063af8bbb1fd7d4ba34e78ffa`.

Git-LF identity не підмінюється raw-file identity й working EOL не переписується. Author policy rewrite/summarise/translate_again/add_examples/add_exercises/production_write/mixed_question_bank_write remains false. Це presentation fix, не новий author-content package.

Frozen accepted M39 V1 source `database/content-patches/m39-m23-authored-revision.v1.json` unchanged SHA-256 `bbfe17773121f6d13cda6beda3d15b5285c54fe73360c605045080e8beddb817`. Frozen before manifest unchanged SHA `76c445345923c1007c09806072a8a7421cdcab835bf492c1573b92c1d7c1a433`.

New finite snapshot: `database/content-patches/m39-practice-ui.v1.json`, version 1, patch `m39-practice-ui-quality-v1`, SHA-256 **`a155e4d53a37ced5bffd7a427305748a68c9eb6f8c8ee33c6b2b0d6e0d589e5f`**. Targets contain exact identity/path/full accepted V1 definition before and new definition after. Only existing practice body at index 7/order 8 changes; all other definition fields/rows are protected.

Strict generator first checked all three current definitions == frozen V1.after, master/notes identities unchanged, then created an exclusive snapshot and performed the mechanical practice-body generation. A subsequent approved draft-only N2 alias update required exact prior snapshot SHA `3551406de8fd6995a809ade707b5559362da1102640687291401fcc526c8a590` and all three current definitions == that old draft.after before replacement. No frozen accepted source was overwritten. Final readonly `--check` reports after/after/after.

Final canonical definition SHA-256:

| Owner | SHA-256 |
| --- | --- |
| Nominal | `69a7f0fad8198c862f1268d46c70e2d21afdf26eb54d9886d87925411718e04a` |
| C1 Mixed | `50bf2e0d488935e2befaa91be82126cce1a9cbb556e456f65eb7d46477484fa9` |
| C2 Mixed | `d9cef5d7ec61ed221447666e3176ab577c6c5db587432c752a836bec3f7eaa76` |

Source full `author_self_check`, original title, `m39_v1` marker/legacy anchor and exact `linked_practice` remain byte-equivalent decoded arrays. Old `selects/choices/inputs` and obsolete UI group headers/options are removed; they are not kept as hidden selectable content. New marker `m39_practice_ui_v1.revision=1`, finite `cases` with `source_index`, interaction and controls.

## Адреси та незмінний own-bank scope

| Page | Theory | Primary test | Own / all linked |
| --- | --- | --- | ---: |
| Nominal Style and Information Density — C2 | [Теорія](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density) | [Тест](http://gramlyze.loc/test/formal-english/nominal-style-and-information-density) | 48 / 192 |
| C1 Mixed Revision — C1 | [Теорія](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision) | [Тест](http://gramlyze.loc/test/mixed-revision/c1-mixed-revision) | 48 / 192 |
| C2 Mixed Revision — C2 | [Теорія](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision) | [Тест](http://gramlyze.loc/test/mixed-revision/c2-mixed-revision) | 48 / 192 |

Previously proven exact primary classes preserved, full prefix `Database\Seeders\V3\Polyglot\`: `PolyglotNominalStyleAndInformationDensityC2LessonSeeder`, `PolyglotFinalDrillC1LessonSeeder`, `PolyglotFinalDrillC2LessonSeeder`, type4/levels C2,C1,C2. Fresh working DB before/after/final fingerprint checks підтвердили own48/all-linked192 та незмінність questions/answers/options/verb_hint/pivots/saved tests. AllLevels mixed pool не підміняє own widget.

## Усі 18 tasks — interaction і answer-only payloads

Повні original prompts, translations та explanations наведені в unchanged [M39 author-layer report](seo-m39-m23-authored-revision-layers.md) і immutable source. Нижче — original source titles та всі short candidates або canonical manual answers, без full-key payload усередині options.

### [Nominal Style and Information Density](http://gramlyze.loc/theory/formal-english/nominal-style-and-information-density#self-check-nominal-style-and-information-density)

| № / original title | Appropriate interaction | Short selectable candidates / canonical manual answer |
| --- | --- | --- |
| 1. Головне слово. | Compound: short select + head-word choice | Form candidates **is / are**, correct `is`; head candidates **expansion / rooms**, correct `expansion`. Full agreement/status rationale remains outside options. |
| 2. Та сама можливість. | Token/manual rewrite | `A reassessment of the exhibition plan by the committee may take place in October.` Canonical plus approved hidden by-placement alias below. |
| 3. Де з’явився новий факт? | Compound: equivalence choice + added-fact multi + rewrite | Candidates **Ні. / Так.**, correct `Ні.`. Added-fact candidates **час уже скоротився / обслуговування поліпшилося / лише план**, first two required, third not selected. Manual: `The team plans a reduction in waiting times.` |
| 4. Не загуби учасників. | Token/manual rewrite | `The curator’s assessment of the insurance documents took place on Monday.` Both exact author assessment constructions accepted. |
| 5. Початок не дорівнює завершенню. | Token/manual rewrite | `The digitisation of the catalogue by the volunteers began in June. The work is still in progress.` |
| 6. Відредагуй нотатку. | Token/manual open edit | `The team has proposed an expansion of the hall. No decision has been made, and the new waiting times have not been measured.` |

Explicit finite aliases:

- N2: `A reassessment by the committee of the exhibition plan may take place in October.` Approved technical hidden accepted variant under the author’s explicit natural-formulation permission. Same words, required start, committee, exhibition plan, may and October; not a new public example/explanation.
- N4: `The assessment of the insurance documents by the curator took place on Monday.` Exact additional source formulation.
- N5: `The digitisation by the volunteers of the catalogue began in June. The work is still in progress.` Previously accepted source-allowed by-placement variant preserved.
- N6: `The team has proposed an expansion of the hall. No decision has been made. The new waiting times have not been measured.` Explicit two/three-sentence permission preserved.

### [C1 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c1-mixed-revision#self-check-c1-mixed-revision)

| № / original title | Appropriate interaction | Canonical manual answer |
| --- | --- | --- |
| 1. Минуле → теперішнє. | Token/manual sentence construction | `If our technician had saved the settings, we would be able to restore them now.` |
| 2. Only after та пасив. | Token/manual transformation | `Only after the supervisor had approved the labels were the boxes dispatched.` |
| 3. Повідомлення про ранішу подію. | Token/manual transformation | `The envelope is believed to have been opened before delivery.` |
| 4. Які саме гіди? | Token/manual sentence construction | `The two guides who have completed the course are leading the tour.` |
| 5. Не роби припущення фактом. | Token/manual correction | `Leila may have sent the draft yesterday, but we have not checked the mailbox.` |
| 6. Точність і ввічливий запит. | Token/manual three-sentence edit | `The dates in the catalogue were checked yesterday. The descriptions have not yet been checked. Could you send the final file by Friday?` |

C1 task6 explicit alias: `The dates in the catalogue were checked yesterday. The descriptions have not been checked yet. Could you send the final file by Friday?`. Other tasks retain canonical source models. Task5 explicitly requests may; the general source comment about might/could does not override that instruction. No huge English-plus-translation choice replaces these original rewrite tasks.

### [C2 Mixed Revision](http://gramlyze.loc/theory/mixed-revision/c2-mixed-revision#self-check-c2-mixed-revision)

| № / original title | Appropriate interaction | Short selectable candidates / canonical manual answer |
| --- | --- | --- |
| 1. Інверсія без зміни можливості. | Token/manual transformation | `Had the guide not brought a spare lamp, we could have been stranded underground.` Not/could unchanged; rationale never part of a token/option. |
| 2. Перфектний пасивний інфінітив. | Token/manual transformation | `The portraits are thought to have been moved before the gallery closed.` Translation/reporting/passive rationale appears separately. |
| 3. Чи достатньо відомостей? | Compound: A manual + B short choice | A: `Marta needn’t have printed a second timetable.` Explicit full-form alias below. B candidates **факт друку не заданий / надрукувала / не надрукувала**, correct first; performance/nonperformance not inferred. |
| 4. Обсяг заперечення. | Compound: guaranteed choice + manual unknown scope | Candidates **кожний непридатний / принаймні один непридатний / рівно один непридатний**, correct second. Manual scope: `Не встановлено точного числа непридатних і придатних.` Exact author sentence, not a full explanatory option. |
| 5. Прозоре відсилання. | Token/manual three-sentence construction | `The register includes every member. However, two entries have no phone number. These incomplete entries need to be updated.` |
| 6. Редакція без перебільшення. | Token/manual open edit | `Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors, but no outdoor test has taken place.` |

Explicit finite aliases:

- C2 task3 A: `Marta need not have printed a second timetable.` Source explicitly accepts the full form; B remains a separate unknown-execution answer.
- C2 task5: `The register includes every member. However, two entries have no phone number. The two entries without phone numbers need to be updated.` Exact source noun-phrase alternative preserved.
- C2 task6: `Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors. No outdoor test has taken place.` Four-sentence permission preserved.
- C2 task6: `Anika’s indoor test of the first prototype on Monday was successful. The second prototype remains untested. According to Anika, the first prototype may also work outdoors; however, no outdoor test has taken place.` Source-note formulation preserved.

Finite lists are not a universal semantic-paraphrase scorer. Shared contraction comparison is not a display transformation; original answer casing stays intact. Wrong possessive `curator is` / `Anika is` instead of `curator’s` / `Anika’s` was separately checked read-only and rejected; no shared contraction change needed.

## Compound scoring: усі чотири cases

| Case | Required parts before a full correct case/score |
| --- | --- |
| Nominal1 | Correct `is` **and** correct head `expansion`. Only choosing the verb is incomplete. |
| Nominal3 | `Ні.` **and** both added facts with no false plan selection **and** the accurate reduction rewrite. No single subpart gives full credit. |
| C2 task3 | Source A sentence with needn’t/need not have printed **and** B unknown execution. A alone does not answer B. |
| C2 task4 | Guaranteed at-least-one choice **and** unknown exact usable/unusable counts. No none/exactly-one distribution added. |

Missing-part/wrong-part/reset/recheck — Node і live PASS. Supplemental окремо перевірив усі чотири compound cases: одна обов’язкова частина відсутня → повний case incorrect/score0; відновлена → correct. Package tests assert the full finite control mapping, and malformed missing-part/source mutations fail closed.

## Logical tokens, natural case та dedicated explanation

All **17 manual controls** have explicit logical groups of **1–3 words**, canonical concatenation == exact canonical answer, and no group crosses a sentence boundary. Runtime shuffles the canonical group sequence; groups are not incorrectly pre-reversed twice. Repeated groups remain distinct instances, e.g. Anika’s `the first prototype` occurs twice.

Had example groups: `Had` / `the guide` / `not brought` / `a spare lamp,` / `we` / `could have been` / `stranded underground.`. Portraits groups: `The portraits` / `are thought` / `to have been` / `moved` / `before` / `the gallery closed.`. C2 unknown-scope groups: `Не встановлено` / `точного числа` / `непридатних і придатних.`.

Manual typing, click tokens, removal/Backspace reuse, aliases, terminal punctuation and no autocomplete are UI acceptance requirements. Natural casing applies to selectable labels, tokens, textarea contents/accepted previews and author explanation. Comparison may normalize typography/case, but never changes displayed text.

Full original author explanation HTML is dedicated to each case and opens only after check/reveal; never nested in fieldset/answer candidate. Initial keys hidden, post-check keys exact, reset closes them — live PASS. Without JS native disclosure reveals the key: 3 pages / 18 keys checked with mouse, Space and Enter. Computed text-transform **none** для candidates/tokens/textarea/key; comparison normalization не змінює display.

Додаткове finite grading correction: тільки control `c2-1-answer` зберігає exact canonical first clause `Had the guide not brought a spare lamp`. Shared EnglishAnswerVariants не змінено. `Hadn't the guide brought…` і `Had not the guide brought…` відхиляються, бо не виконують explicit source instruction; допустиме main-clause `could've` приймається. Це contextual instruction check, не глобальна заборона скорочень чи твердження про всі можливі інверсії.

## Regression guards і автоматичні перевірки

No arbitrary global character limit. Guards are finite/semantic/structural:

- Independent exact answer-label inventory for all six selectable controls; appended rationale/translation fails even if it is not the entire key.
- `assertOptionContract(option, intendedAnswer, fullKey)` compares the actual intended fragment, not a generated label to itself.
- Real Had and portraits source fixtures prove clean answer == option, option != full author key/answer+translation+rationale, and explanation is a separate element. Test-only choice fixtures model the discovered bug; accepted rewrite tasks remain manual.
- All18 source indices, original full prompts/keys, logical groups, explicit aliases, compound parts and exact own-bank data tested.
- Source/owner/canonical-only-practice protection, full-key leak, changed translation/modal, missing part, wrong owner, lost prompt and cross-sentence token mutations rejected.
- Wrong owner/locale/UUID/order/body presentation refuses opt-in; isolated tests PASS. Source fallback template зберігає stored prompts/keys у readable native disclosures. Реальну БД навмисно не псували для перевірки fallback; це не live corruption/recovery claim.

| Command / scope | Actual result |
| --- | --- |
| New M39 Practice UI package/render PHP v1 | **8 tests / 1163 assertions PASS**, exit0, 09.818s, 58MB, one existing PDO deprecation; 46648 protected files, changes0. Initial run retained separately. |
| Strengthened independent option-label PHP v2 | **8 tests / 1169 assertions PASS**, exit0, 04.711s, 58MB, one existing PDO deprecation; 46648 protected files, changes0. |
| UI patch/local-target guard final v4 | **41 tests / 113 assertions PASS**, exit0, 02:56.388,66MB; one existing PDO deprecation,46648 protected files changes0. Failed v1–v3 retained separately, not counted PASS. |
| Complete related PHP, final11-class v2 | **283 tests / 4334 assertions PASS**, exit0,13:58.242,80MB; one existing PDO deprecation,46648 protected files changes0. |
| Node practice/scoring/quality | New UI **68/68 PASS**,1660.03ms. Final related **33 files / 932 tests PASS**,exit0,8884.8047ms; not a sum of reported partial runs. |
| Relevant Vitest | **8 files / 61 tests PASS**,exit0,135.56s,one worker; no dependency/build changes. |
| Generator/helper/new PHP lint and pure generation check | **15 PHP lint / 3 JS syntax / 4 JSON parse PASS**; pure generator after/after/after, immutable source hashes unchanged; staged diff and secret/private-artifact checks PASS. |

Exact outer v2 command, cwd `C:/Users/admin/.codex/worktrees/m37-grammar-layers/gramlyze.loc`:

```powershell
& 'C:/Users/admin/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe' tools/diagnostics/run-isolated-tests.py --php 'C:/Program Files/xampp/php/php.exe' --label m39-practice-ui-package-v2 tests/Feature/M39PracticeUiPackageTest.php
```

Runtime uses isolated SQLite `:memory:`, own test storage/views, array cache/session, no working .env/DB writes. Existing `PDO::MYSQL_ATTR_SSL_CA` deprecation under PHP8.5 is reported, not suppressed or fixed outside scope.

Private evidence retained, not committed:

- `storage/app/seo-m2-local/m39-practice-ui-package-v1-a011ecf7dce84d779eb65d218ad570c7-result.json`.
- `storage/app/seo-m2-local/m39-practice-ui-package-v2-42c883dcb3f341e8a8be6dcc9c186d19-result.json`.
- `storage/app/seo-m2-local/m39-practice-ui-guard-targeted-v4-bdd8cb00a4754ec890ee909808b357a5-result.json`.
- `storage/app/seo-m2-local/m39-practice-ui-final-php-v2-f66aa5fc9c2e460cb650302606ccaffb-result.json`.

Combined11 scope: 4 M38 classes, 4 original M39 classes, 3 new UI classes. Historical canonical assertion now strictly requires **frozen V1.after == SHA-bound UI.before** and **current canonical == UI.after**; all original metadata/theory/hero/blob assertions remain. Initial combined v1 failed only this obsolete current-canonical==V1 assertion (283/4260,13:02.294); it is retained, not claimed PASS. The UI revision does not relax frozen V1 validation.

These separate runs are not added to an invented aggregate total.

## Guarded actual-local pipeline

Fresh physical/vhost/CLI-web proof підтвердив localhost MySQL3306/gr2, `DESKTOP-3C05HGF`, actual application/document root `D:/DEV/htdocs/gramlyze.loc`/`public`; APP environment production, effective SiteMode development for `.loc`. Existing exact V1 practice rows IDs **12606/12613/12620**, UUIDs/order8/localeuk/metadata захищені. Before artifact `m39-practice-ui-before-v1.json` SHA `f16e1ea3a0e60f6960d1bac70ae839c21711b2f52dea89e93bc3b6ed25e163f9`, 41 owners/20 tables. Duplicate exclusive evidence attempt refused overwrite, existing artifact retained; no DB mutation from that attempt.

Original exclusive source backup `source-backup-practice-ui-4786ab95f418a224` preserves exact original V1 definitions and two ROOT/PPC shared sources. Reviewed final-at-apply backup `source-backup-practice-ui-ffa4b2fe8732d527`; later frontend-only rebind backup `source-backup-practice-ui-7d4c2149183d97af`. All in ROOT `storage/app/seo-m39-local/`, **12 finite records** each, shared files verify-only; no blind overwrite of existing PPC changes. The first preview-v1 was not applied after a narrow JSON-error-handling code correction; fresh preview-v2 was reviewed instead.

Fresh preview **v2** digest `17929bf5088dda9b5d2cdd7588ceca304d0f840c8810e1eb1fe96a1a6ec4da0f`, **3 body updates / 0 inserts / 0 deletes**, 25 bound sources. Exact helper + independent read-only review of both original/current source-backup chain — PASS. Transactional apply succeeded. Exclusive DB record backup `D:/DEV/htdocs/gramlyze.loc/storage/app/seo-m39-local/m39-practice-ui-backup-v1.json` is byte-identical to reviewed v2 preview, raw SHA **`c5d14dbe7dcab8d942421dde2bb4c00917338c6bd4156c5f45a9fbed82a82ab5`**.

After/final SELECT evidence confirms **6552 total text_blocks**, **6549 non-target blocks unchanged**, SHA `3789ab0058d14787832e828c5cd76eed4224405e2e245b70782a39fa9684353a`; **19 other tables**, **41 previous owners**, banks, basic/hero/subtitle/tables/title/H1/categories/locale/levels/tags/anchors unchanged. Initial after SHA `3c013763bb0a43c02c4cb308cb790f7b05d42e37232c8becb77c5d8317544a87`; final post-browser `m39-practice-ui-after-v3.json` SHA `407320117d0e6f044fd66e315bdc665792c8918b344a3043e44881c64a044bd1`; verifier PASS.

Repeated apply of reviewed v2 returned **no-op0/0**. After frontend-only finite conditional correction, fresh after-code preview-v3 stateafter0/0 digest `041eed84804d2612bce3720724a26541454d9d2ba55b470c0e11b9a776b844c3`; final after-code apply also **no-op0/0**. Unused no-op backups v1/v3 absent. Frontend correction did not change snapshot/DB body; after/no-op/final evidence equality excludes only `at`.

Both temporary proof nonces used local GET/raw loopback/SELECT-only safe identity, no forwarded/cookie/secret. Both removed. `routes/api.php` byte-identical to original SHA `2b172af47a23acc91fbdfc8c175a284ef0ce8c2b024a4ffbe2f308d72bad5689`; fresh final nonce GET **404**. No seeds/migrations/full restore/cache/session clear or Apache/XAMPP/hosts change.

## Live visual acceptance

Final PRE **quality-before-v2 PASS36/36**,67.7796s, **36 PNGs**: all18 tasks at1440×1000 and390×844. Main inspected every desktop/mobile card **before scoring**, including all selectable tasks. Candidate whitelist15 independent fragments, natural computed casing, correct source prompts, key initially hidden, clean wrapping/no rationale paragraph — PASS. Height may temporarily increase at unchanged390 width to capture an entire tall editorial card; layout/overflow measured at the original viewport, no full-page screenshots.

Final functional **quality-after-v3 PASS36/36**,104.5146s, **72 post-correct/post-wrong PNGs**. Every task correct/wrong/feedback/exact separate author key/reset, score6→0, keyboard/focus, radio arrows/multiSpace/manualCtrlEnter — PASS. All **17 manual controls ×2 viewport=34** token/manual/Backspace/alias/punctuation checks PASS. C2 forbidden first-clause contractions rejected and main could've accepted at both sizes. Child visually inspected all36 mobile post images; main additionally inspected desktop N3, Had, portraits-wrong and not-all feedback/key separation.

Final UI sources unchanged throughout PRE/functional: projectiona155..., frontend JS **`c5441b0303292ce90e03d332345ef5b43dac7b97c79e2d6e5d5801f30d3c2e45`**, partial **`6823468a02ed2f6b06fcbd0cc1dec268368abb4d280fab0093912f8606260052`**. Initial after-v1 diagnostic stopped on an immediate Alpine reset DOM timing check; corrected by waiting for the expected hidden state, not by sleeps or weakening UI. After-v2 was superseded by the subsequently discovered contextual Had rule. Neither run supplies final acceptance evidence.

Source→definition→actual DB→initial HTML→DOM fidelity PASS, not merely fixture output. Final page/console/HTTP/font/failed/policy errors **0**, violations `[]`. Supplemental verified browser subresults: **4 compound omissions→restore**, **3 no-JS pages/18 native keys** mouse/Space/Enter, **3 normal M38 regressions** with own-bank/practice/aliases/tokens, all PASS. Separate source fallback is isolated-test evidence, not deliberately corrupted live DB.

Learning main/cards/controls/candidates overflow **0px**. Desktop document overflow0. Final functional mobile decorative document overflow Nominal/C1/C2 **7/2/13px**, reported separately; background/global overflow hiding unchanged. Browser matrix is light desktop/mobile; this follow-up does not claim a new full dark-theme matrix. M39 **point-level** disclosures remain0 (author-key disclosures are distinct); accepted M26–M38 count/DB baseline unchanged. Historical **11 M26–M29 pages/22 hidden no-JS token banks** remain outside scope; no-JS readability/native key reveal is not automatic scoring.

Fresh HTTP **ui-quality-final-v2 PASS49/49 guest GET200**,28.1699s: metadata/title/H1/canonical/robots/OG/Twitter, anchors and unchanged control text preserved. Sitemap **554 ordered URLs**, same SHA `6ffdf977fa24124660ab47ce7e1f1a81b9a180eb490dada34a0be237e4adb334`. Earlier complete attempts had30s legacy PPC/final endpoint timeouts; separate PPC2000.5446s, course2000.3664s, sitemap2002.4026s confirmed availability before the fresh final complete run. No server/cache changes or hidden retry. Course check server-only6prompts/6keys; existing browser gate not bypassed or visually accepted.

Final private evidence in ROOT `storage/app/seo-m39-local/`:

- `quality-before-v2-visual.json`: SHA `5050481d253793b0275c3c805379e9057446d98528f4c037047556843542616a`.
- `quality-after-v3-acceptance.json`: SHA `200e88733160a4e6319a58aa4e916e7cf4d6c2096a457e6de85afa71dcd91486`.
- `quality-supplemental-v2-browser-verified.json`: SHA `19218d326ac85fd88a8843ee0bafd99fb1af05dba9e3615b8dee6dcd1ed7eb0a`.
- `ui-quality-final-v2-http.json`: SHA `faa514213b032224360e0e4b0ddf47101b3187c3d9ca92ee14b3e512cc34533b`.

Supplemental-v1 no-JS diagnostic initially omitted JavaScriptEnabled=false, incorrectly classifying expected CSP script blocking; diagnostic flag corrected, no app policy change. Supplemental-v2 browser8rows PASS is independently verified from retained artifact, while its overallHTTP timeout remains false; final fresh HTTP provenance is separate. Failed/superseded artifacts are retained, not converted to PASS.

## Git handoff та межі

Base **449f64d293277c200c64fc0b16807a9a25dfc26c**; working branch `codex/seo-m39-m23-authored-revision-layers`. ROOT HEAD41820a2bebdf69004fa7209a2a38457f93efabbd retained. Explicit allowlist **27 related files** (includes one strict historical test bridge), no foreign unfinished work. Final JSON/PHP/JS/diff/staged/secret/private audits and immutable master/notes/V1 no-diff verified. Normal push targets only this working branch; full40-character commit SHA/direct commit link and exact remote SHA==HEAD result are provided in final handoff, avoiding a self-referential SHA inside its own report commit.

No `.env`, runtime proof, private screenshot/diagnostic data, dump/backup, vendor/build, temporary route or unrelated dirty change committed. Main/PR/force/deploy not performed. Old M39 author-layer report untouched; this follow-up report supersedes its historical claim that full-key selectable controls were acceptable UI.

Production не перевірявся й не змінювався.
