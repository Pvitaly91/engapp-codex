# M70 · Редакційна карта 1.0.0

База: `be2b1c2549e70d91cc6f3c47cd7708a68ff7eb71`. Scope: тільки три UK Page owners; content/practice, не редизайн. Джерельні BEFORE bodies є в окремому незмінному JSON.

## Межі й незмінність

Intensifiers: 6 старих slots та 1 новий practice-set. Plural: 14 старих slots, практика залишається на source index 12 / UUID e480381a-dd8f-5388-af75-ff58d96827af. Countability: 12 старих slots, практика залишається на source index 10 / UUID 132a698f-082e-5722-8315-bbea0dcbb824.

32 continuing slots зберігають metadata, uuid_key, columns, levels, tags, base_tags/inherit_base_tags, порядок і anchors. Technical levels: B1 / NULL / NULL для практик; власні вправи орієнтовані на B1 / A2 / A2. NULL не замінено на A2 заради питань. H1: Intensifiers; Plural nouns; Countable / Uncountable Nouns — Злічувані й незлічувані. Raw Page.title незмінний.

## Явні змістові рішення

- Intensifiers: збережено групи very/really/so/too, absolutely/completely/totally, quite/rather/pretty та enough. Розширено rather, два значення quite, дві частини мови pretty, моделі з прислівником та really + дієслово.

- Старе very amazing як wrong-конструкцію не продовжено: стилістичну рекомендацію перенесено до видимого usage-пункту про природні сполуки. Це не нова граматична заборона.

- Старе The food is too good не оголошено помилкою; повний власний контекст too good to waste з перекладом лишається у visible basic. Помилки форми тепер перевіряють enough big, very like і too much heavy.

- Приклад вікового дозволу на водіння замінено самодостатніми ситуаціями достатності, без прихованої правової умови. Не додано відсоткових шкал.

- Plural: корисні -s/-es/y/f/o, неправильні й незмінні моделі лишаються у своїх старих блоках. Таблиця повторює лише стислу перевірку; зайві дубльовані lexical приклади в огляді скорочено, не правила.

- Уточнено /tʃ/ та написання quiz/stomach; mosquitoes/mosquitos, wharfs/wharves, curricula/curriculums і shrimp/shrimps не оголошені хибними альтернативами. Mouse у вправі прямо означає тварину; computer mouses не заборонено глобально.

- Sheep має число попри незмінну форму. Узгодження розгорнуто до is/are, was/were, has/have, do/does, заперечень і коротких відповідей. Старе to be → are обмежено Present Simple.

- Plural singular-only/plural-only списки перевірено за значеннями: archive має однину; savings/saving, customs/custom, congratulations/congratulation, headphone/headphones мають окремі словникові межі. History/English не названі закінченнями -s. Pair/pairs зберігає число пар і головне слово.

- Countability: фізична можливість порахувати зерна не визначає модель rice; абстрактна idea злічувана. Збережено chicken/coffee/glass/paper з перекладами конкретних значень.

- Source a coffee проти глобальних заборон a water/two waters/a milk узгоджено: речовина і замовлена порція — різні контексти. Не приховано the/this/my/some з mass-значенням.

- Quantifiers — компактне зіставлення, не дубль повного уроку. Стверджувальне much information не оголошено неграматичним; для нейтрального стилю пропонується a lot of. Piece of advice перекладено як одна порада.

- Усі point-level рішення скінченні й явні в master: visible-basic. Немає окремих поглиблень, які потребують disclosure; нуль details — редакційний результат, не word-count threshold.

## Практика OLD → AFTER

Збережено 20 оцінюваних старих noun tasks: 8 selects, 7 word inputs, 5 rephrase; два демонстраційні приклади не входять у score. Rephrase реально відображається старим native-шляхом, тому не перенесено й не залишено невидимим. Відсутність старих IDs/source_index і груповий порядок збережено.

Додано 14 власних tasks: Intensifiers 2/2/2; Plural і Countability — по 2 meaning і 2 full-sentence inputs. Разом 34 власні tasks: 10 selects, 6 choices, 13 inputs (7 коротких + 6 повних), 5 rephrase. Плюс 15 reused bank questions в окремих widgets.

У реальному BEFORE браузері другий варіант countries давав «Неправильно. Правильна відповідь: countries», а Uncountable для water — «Неправильно. Правильна відповідь: U». Тепер власні options містять точні graded values; engine не змінено. Дубльовані countrys/potatos замінено різними distractors.

Scissor correction зберігає одну пару; milk correction задає речовину та some. Much/a lot of/lots of information приймаються за заданим значенням. Canonical/aliases/38 options перевірені чинним factory, не новим normalizer. Feedback для x-text plain, EN/UK та пояснення відкриваються після Check.

## OLD block → AFTER component

| Owner | Slot | Source key | Native type | Continuing | Level |
|---|---:|---|---|---|---|
| Adjectives\AdjectivesIntensifiersTheorySeeder | 0 | intensifiers-hero | hero | yes | B1 |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 1 | intensifiers-forms | forms-grid | yes | B1 |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 2 | intensifiers-use | usage-panels | yes | B1 |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 3 | intensifiers-mistakes | mistakes-grid | yes | B1 |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 4 | intensifiers-summary | summary-list | yes | B1 |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 5 | intensifiers-navigation | navigation-chips | yes | NULL |
| Adjectives\AdjectivesIntensifiersTheorySeeder | 6 | intensifiers-practice | practice-set | new practice | B1 |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 0 | hero-overview | hero | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 1 | main-patterns | forms-grid | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 2 | rule-plus-s | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 3 | rule-plus-es | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 4 | rule-y-ies | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 5 | rule-f-ves | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 6 | irregular-plurals | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 7 | quick-table | comparison-table | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 8 | singular-only | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 9 | plural-only | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 10 | common-mistakes | mistakes-grid | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 11 | summary | summary-list | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 12 | practice | practice-set | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityPluralNounsTheorySeeder | 13 | related-pages | navigation-chips | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 0 | countability-0 | hero | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 1 | countability-1 | forms-grid | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 2 | countability-2 | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 3 | countability-3 | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 4 | countability-4 | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 5 | countability-5 | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 6 | countability-6 | comparison-table | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 7 | countability-7 | usage-panels | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 8 | countability-8 | mistakes-grid | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 9 | countability-9 | summary-list | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 10 | countability-10 | practice-set | yes | NULL |
| NounsArticlesQuantity\NounsArticlesQuantityCountableUncountableNounsTheorySeeder | 11 | countability-11 | navigation-chips | yes | NULL |

Повні metadata/body hashes і OLD/AFTER task fields — у component/task JSON; це не derived expected із live AFTER.

## Первинна мовна перевірка

Прочитано definitions повністю. Нові приклади й завдання авторські; exercise banks не копіювалися. Перевірка агентна, не human review.

| Джерело | Фактичний доступ | Використана межа |
|---|---|---|
| [Джерело](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/intensifiers) | direct editorial body; direct: success | Ordinary degree modifiers, strong adjectives as a usual collocational guide, meaning-dependent combinations; examples and exercises not copied. Learner comments excluded. |
| [Джерело](https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/common-problems-count-uncount-nouns) | indexed editorial body; direct: 400 timeout on both direct attempts | Substance versus serving/type; paper/glass meaning; singular agreement for named disciplines on -s. Learner comments not used as authority. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/nouns-countable) | indexed editorial body; direct: 403 | Separate-item versus mass model, determiner choices, measures, meaning-dependent count uses. |
| [Джерело](https://dictionary.cambridge.org/dictionary/english/archive) | indexed dictionary editorial body; direct: 403 | Archive has countable singular and plural/usually plural uses; not an absolute plural-only word. |
| [Джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/adjectives-gradable-and-non-gradable) | indexed Quite editorial body; direct: 403 | Quite can signal moderate degree or completeness. The supplied /quite URL also returned 403; this is the indexed article address, not a claimed redirect. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/too-enough) | indexed Too editorial body; direct: 403 | Too exceeds the desired boundary; adjective/adverb position; too much/many. Supplied /too also returned 403; no redirect claim. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/enough) | indexed editorial body; direct: 403 | Enough before nouns, after adjectives/adverbs; enough of is a separate model. |
| [Джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/nouns-form) | indexed editorial body; direct: 403 | Plural spelling and agreement; models are bounded by spelling/pronunciation. |
| [Джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/rather) | indexed editorial body; direct: not attempted | Degree use; often unexpected/surprising; no fixed percentage scale. |
| [Джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/pretty) | indexed editorial body; direct: not attempted | Informal degree use distinguished from the adjective meaning. |
| [Джерело](https://dictionary.cambridge.org/grammar/british-grammar/plural-nouns) | indexed editorial body; direct: not attempted | Plural-only uses, pair/pairs, singular discipline names; used with sense-specific dictionary checks, not as a blanket lexical ban. |
| [Джерело](https://dictionary.cambridge.org/us/dictionary/english/wharf) | indexed dictionary editorial body; direct: not attempted | Wharves and wharfs both recorded. |
| [Джерело](https://dictionary.cambridge.org/dictionary/english/mosquito) | indexed dictionary editorial body; direct: not attempted | Mosquitoes and mosquitos both recorded. |
| [Джерело](https://dictionary.cambridge.org/dictionary/learner-english/curriculum) | indexed dictionary editorial body; direct: not attempted | Curricula and curriculums both recorded. |
| [Джерело](https://www.oxfordlearnersdictionaries.com/definition/english/shrimp) | indexed dictionary editorial body; direct: not attempted | Shrimp and shrimps for the animal. |
| [Джерело](https://www.oxfordlearnersdictionaries.com/definition/english/mouse_1) | indexed dictionary editorial body; direct: not attempted | Animal mice; computing mice or mouses. |
| [Джерело](https://dictionary.cambridge.org/dictionary/english/fish) | indexed dictionary editorial body; direct: 403 | Fish/fishes; count animal versus mass food. No exclusive species-only rule invented. |
| [Джерело](https://www.merriam-webster.com/dictionary/headphone) | direct dictionary editorial body; direct: success | Headphone exists; usually plural. Web/corpus examples not used as normative rules. |
| [Джерело](https://dictionary.cambridge.org/us/dictionary/english/congratulation) | indexed dictionary editorial body; direct: not attempted | Plural congratulations for greetings, singular uncount congratulation for the act; not interchangeable count forms. |
| [Джерело](https://dictionary.cambridge.org/dictionary/english/saving) | indexed dictionary editorial body; direct: not attempted | Savings as saved money; a saving as an amount not spent. |
| [Джерело](https://dictionary.cambridge.org/us/dictionary/english/customs) | indexed dictionary editorial body; direct: not attempted | Customs in official-check/department senses distinguished from custom/customs as traditions. Avoid unsupported department-is rule. |
| [Джерело](https://dictionary.cambridge.org/us/dictionary/english/quizzes) | indexed dictionary editorial body; direct: not attempted | Quizzes is plural of quiz. |
| [Джерело](https://dictionary.cambridge.org/dictionary/english/stomach) | indexed dictionary editorial body; direct: not attempted | Stomachs, with /k/ pronunciation: no universal written-ch algorithm. |
| [Джерело](https://dictionary.cambridge.org/us/grammar/british-grammar/person-persons-or-people) | indexed editorial body; direct: not attempted | Person singular, people for ordinary plural, persons in legalistic/formal contexts; no unrestricted substitution. |
| [Джерело](https://developers.google.com/search/docs/fundamentals/creating-helpful-content) | direct editorial body; direct: success | People-first usefulness/originality/accuracy, not word quotas or ranking/indexation guarantees. No Search Console action. |

403/timeout не названо успішним direct читанням; indexed editorial body не змішано з learner comments. URL альтернативної редакційної статті не є доказом redirect. Без обіцянок індексації, search volumes чи вигаданих авторів.

## Додаткові перед-freeze перевірки

71 title/formula fields × 4 локальні font profiles: 284 вимірювання, 0 unbreakable-token risks за 194/254 px; найбільший токен 134.219 px. Це offline fallback-font risk check для 320 px, не DOM/webfont/browser PASS. Реальна матриця 18 states після guarded apply обліковується окремо. Найбільший TEXT body: 12658 bytes із 65535.
