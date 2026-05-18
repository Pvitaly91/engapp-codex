# Polyglot Ukrainian Source Quality Audit

Prompt ID: GLZ-CODEX-SENTENCE-BUILDER-UK-SOURCE-QUALITY-AUDIT-017

- Total scanned files: 168
- Total scanned questions: 8538
- Files with quality issues: 20
- Total fixed source fields: 1436
- Cloze prompts fixed: 360
- Ellipsis placeholders fixed: 355
- Generated context/label artifacts fixed: 851

## Changed Files

- database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotBasicWordOrderAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotConjunctionsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotNarrativeTensesAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastPerfectFormsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastPerfectQuestionsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastSimpleQuestionsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastSimpleTimeExpressionsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotStativeVerbsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotUsedToWouldAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotWordOrderAdverbsAdverbialsAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotWordOrderQuestionsNegativesAllLevelsLessonSeeder/definition.json
- database/seeders/V3/Polyglot/PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder/definition.json

## Examples

| File | Question UUID | Field | Before | After | Reason |
|---|---|---|---|---|---|
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-01 | question | Склади пропущену частину речення: Ніколи... такий гарний захід сонця. | Я ніколи не бачив такого гарного заходу сонця. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotConjunctionsAllLevelsLessonSeeder/definition.json | poly-conj-c1-02 | question | Склади речення зі словом «не тільки... але також»: Вона не лише знайшла проблему, а й виправила її. | Вона не лише знайшла проблему, а й виправила її. | ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotNarrativeTensesAllLevelsLessonSeeder/definition.json | nt-poly-a1-01 | question | Я готував вечеря, коли задзвонив телефон | Я готував вечерю, коли задзвонив телефон | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotNarrativeTensesAllLevelsLessonSeeder/definition.json | nt-poly-a1-01 | source_text_uk | Я готував вечеря, коли задзвонив телефон | Я готував вечерю, коли задзвонив телефон | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotPastPerfectNegativesAllLevelsLessonSeeder/definition.json | pp-negatives-poly-a2-03 | question | Клас не переглянула проект до понеділка | Клас не переглянув проєкт до понеділка | unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder/definition.json | pppcpast-poly-a2-01 | question | до полудень вона вже завершений звіт (A2) | До полудня вона вже закінчила звіт | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPastPerfectVsPastPerfectContinuousAllLevelsLessonSeeder/definition.json | pppcpast-poly-a2-01 | source_text_uk | до полудень вона вже завершений звіт (A2) | До полудня вона вже закінчила звіт | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder/definition.json | pspc-poly-a2-01 | question | телефон задзвонив, поки я був готує (A2) | Телефон задзвонив, поки я готував | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPastSimpleVsPastContinuousAllLevelsLessonSeeder/definition.json | pspc-poly-a2-01 | source_text_uk | телефон задзвонив, поки я був готує (A2) | Телефон задзвонив, поки я готував | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPresentPerfectContinuousTimeExpressionsAllLevelsLessonSeeder/definition.json | ppc-time-poly-a1-03 | question | Весь ранок вона працює над проектом весь ранок | Вона працює над проєктом весь ранок | unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder/definition.json | ppps-poly-a2-05 | question | Він загубив ключовий, так він не може відкрити цей двері зараз (A2) | Він загубив ключ, тому зараз не може відчинити двері | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPastSimpleAllLevelsLessonSeeder/definition.json | ppps-poly-a2-05 | source_text_uk | Він загубив ключовий, так він не може відкрити цей двері зараз (A2) | Він загубив ключ, тому зараз не може відчинити двері | generated_context_label, unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder/definition.json | pppc-poly-a2-07 | question | ми прибрав кухонний і це є прибрати зараз (A2) | Ми прибрали кухню, тому тепер вона чиста | generated_context_label |
| database/seeders/V3/Polyglot/PolyglotPresentPerfectVsPresentPerfectContinuousAllLevelsLessonSeeder/definition.json | pppc-poly-a2-07 | source_text_uk | ми прибрав кухонний і це є прибрати зараз (A2) | Ми прибрали кухню, тому тепер вона чиста | generated_context_label |
| database/seeders/V3/Polyglot/PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder/definition.json | psvc-poly-a1-03 | question | Мої батьки постійно живуть у Львів | Мої батьки постійно живуть у Львові | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotPresentSimpleVsPresentContinuousAllLevelsLessonSeeder/definition.json | psvc-poly-a1-03 | source_text_uk | Мої батьки постійно живуть у Львів | Мої батьки постійно живуть у Львові | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotStativeVerbsAllLevelsLessonSeeder/definition.json | sv-poly-a1-03 | question | Ця сумка належить моєму брат | Ця сумка належить моєму братові | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotStativeVerbsAllLevelsLessonSeeder/definition.json | sv-poly-a1-03 | source_text_uk | Ця сумка належить моєму брат | Ця сумка належить моєму братові | source_target_mismatch |
| database/seeders/V3/Polyglot/PolyglotUsedToWouldAllLevelsLessonSeeder/definition.json | utw-poly-a1-01 | question | Колись я жив біля річка у дитинстві | У дитинстві я жив біля річки | unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotUsedToWouldAllLevelsLessonSeeder/definition.json | utw-poly-a1-01 | source_text_uk | Колись я жив біля річка у дитинстві | У дитинстві я жив біля річки | unnatural_ukrainian |
| database/seeders/V3/Polyglot/PolyglotWordOrderVerbsObjectsAllLevelsLessonSeeder/definition.json | poly-wovo-b2-03 | question | Склади пропущену частину речення: Вона... Фразове дієслово нерозлучне. | Вона натрапила на стару фотографію. | cloze_prompt, ellipsis_placeholder, generated_context_label |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-02 | question | Склади пропущену частину речення: Це... хто дзвонив тобі вчора. | Саме Том дзвонив тобі вчора. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-03 | question | Склади пропущену частину речення: Те, що мені потрібно... хороший відпочинок. | Мені потрібен хороший відпочинок. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-04 | question | Склади пропущену частину речення: Мені... подобається твоя нова сукня! | Мені подобається твоя нова сукня! | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-05 | question | Склади пропущену частину речення: Рідко... стільки снігу в квітні. | Рідко буває так багато снігу в квітні. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-06 | question | Склади пропущену частину речення: Це... те, що я купив, а не спідницю. | Я купила сукню, а не спідницю. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-07 | question | Склади пропущену частину речення: Вона... хоче прийти на вечірку. | Вона хоче прийти на вечірку. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-08 | question | Склади пропущену частину речення: Найбільше я люблю... шоколадний торт. | Найбільше я люблю шоколадний торт. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-09 | question | Склади пропущену частину речення: Рідко... запізнюється на заняття. | Вона рідко запізнюється на уроки. | cloze_prompt, ellipsis_placeholder |
| database/seeders/V3/Polyglot/PolyglotAdvancedWordOrderEmphasisAllLevelsLessonSeeder/definition.json | poly-woadv-a1-10 | question | Склади пропущену частину речення: Це... в Лондоні я зустрів її. | Саме в Лондоні я зустрів її. | cloze_prompt, ellipsis_placeholder |

Full changed-field details are summarized in the JSON audit artifact; no full definition dumps are included.
