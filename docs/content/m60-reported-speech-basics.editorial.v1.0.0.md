# M60 — editorial audit 1.0.0

## Межі й незалежний BEFORE

Рівно три UK theory owners: Reported Statements (Page 3), Reported Questions (143), Reported Commands and Requests (142), category 3. Base `69994e34da410c66d6807a0921b588f85b81908c`. Independent source/DB/HTTP BEFORE SHA `2cf4f9d9038a66546b90aee3d26e25a1a9b31aa5fd552ea870a70ef2d81f705f`; fresh old-consumer/nav proof SHA `b15e05b5eb3471967a755f87ab60ef348b9149271c03c148a44cf63efc95d220` (58/58 HTTP200, 9 saved banks, 528 old questions, no learning writes). Category «Непряма мова» read-only; page.category «Reported Speech» не перейменовано.

Actual English H1/raw titles/slugs/category/locale збережено. У кожному owner — 6 continuing slots: hero/header/B1 із display B1-B2; чотири HTML box slots left,left,right,right з null level; navigation/footer/null. Finite metadata map змінює рівно 12 box types на forms-grid, comparison-table, mistakes-grid, summary-list та прибирає рівно відповідні old headings. UUID/order/columns/null levels зберігаються. Додано context usage-panels/B1 і final practice-set/B1 на owner. Це data-only канонічні компоненти, не новий CSS/дизайн і не зміна course/standalone consumers.

## Предметне редагування

- Statements: розділено said + clause / said to someone + clause / told someone + clause; that optional у відповідних моделях. Хто говорить, кому, хто переповідає, де й коли — задано для контрольованих прикладів. Backshift не автоматичний і не доводить припинення стану; I може лишитися I у самопереказі. Time/place changes залежать від точки відліку. Заперечення said і reported clause не ототожнюються.
- Questions: if/whether для «чи», wh-порядок без вкладеної інверсії; subject who та object who збережено за ролями. Прибирається питальне auxiliary do, а не lexical do, час чи not. Питання не перетворюється на відповідь. Немає загальної заборони знака питання: outer Did…ask…? лишається питанням. Ability/request та now-politeness/past-report розділено без blanket ban asked if/could.
- Commands: object + to / object + not to з явним адресатом і виконавцем; ask/tell/order/advise/warn залежать від наміру. Ask to, ask someone to, ask if someone could мають різні ролі; offer/suggest не натягуються на одну модель. Told past не змінює to-infinitive на -ed. Negative instruction відділено від negated reporting verb; не кожне not to є забороною. Природні that-моделі поза цільовим object+to не оголошено неграматичними. Прохання/порада/наказ не доводять виконання.

## Basic і meaningful details

Явне семантичне рішення, не квота: 1/1/1 meaningful details. Statements current-truth → statements-context/sections/0; Questions ability-or-request → questions-context/sections/3; Commands negation-scope → commands-context/sections/1. Кожен detail має окреме поглиблення з контрольованими реченнями; короткі обов’язкові правила, застереження й переклади лишаються visible basic. Глобальний word threshold не додано.

## Практика й aliases

18 own tasks, по 2/2/2: 36 коротких options, 6 manual/token inputs. 17 finite accepted variants = 6 canonical + 11 aliases, не 17 aliases. Явно дозволені that/no-that, if/whether, the next/following day та full/contracted forms лише там, де це відповідає конкретній умові. She/he referents і known the map задано до Check. English stimuli в meaning choices мають видимий український контекст; повний переклад відкладено до feedback, щоби не розкрити відповідь. Інші sentence examples мають EN+UK поруч у підтриманих fields.

Actual unchanged shipped factory offline suite 22/22 PASS на private v5 SHA `cccc4b5431e2a41ca7c5e187bee0ad166944c71cc2b1f2ec130c6953073e0fe8`: 18 tasks, 36 options, 6 manual, 6 token, 17 variants, curly apostrophes, multiplicity/shuffled banks, empty/partial/wrong/retry/correct/recheck/reset, independent scores, 0 network. Перший приватний запуск знайшов escaped namespace у fixture й відсутні exact curly feedback keys; виправлено лише тест/finite author feedback, не grader. Offline не є browser/widget/DB/no-JS/print acceptance; final frozen run окремо.

Widget contract `docs/content/theory-sentence-builders/m60-reported-speech-basics.v1.json`, SHA `58ca869b0f34adb0c97f494780e9e267b6bec3f949fa7aabbf48516024647785`: main-approved 15 existing B1/type4 questions, 0 new questions/0 new links, classes 1/2/1. Bounded lowercase-wh presentation clones не переписують old source/DB question keys, options або course banks. Actual inventory і live widget5/5 перевіряються окремо, цим аудитом не підміняються.

## Primary-source journal

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-statements

Access: Author direct open: main editorial grammar body read in full. Signed replies and learner comments identified separately, not substituted for the main body.

Використання: Direct/report distinction, usual backshift rather than universal obligation, current relevance, context-dependent pronouns/time/place. Original scenarios/tasks not copied from publisher exercises.

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-questions

Access: Author direct open: main editorial grammar body read in full. Learner comments not normative.

Використання: If/whether and wh clause order; question versus request depends on context. No claim that indirect questions assert an answer.

### https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/reported-speech-reporting-verbs

Access: Author direct open: main editorial grammar body read in full; signed responses separately identified.

Використання: Reporting verb patterns, object + to, not to, offer + to and suggest + -ing. Not a blanket ban on other reporting-clause patterns.

### https://dictionary.cambridge.org/grammar/british-grammar/reported-speech-indirect-speech

Access: Main independent review: direct 403; official publisher-indexed main body read at the same URL and https://dictionary.cambridge.org/us/grammar/british-grammar/reported-speech-indirect-speech.

Використання: Pronoun perspective, no-backshift contexts, statements and commands; original examples use explicit speaker/addressee/reporter.

### https://dictionary.cambridge.org/grammar/british-grammar/reporting-clauses

Access: Main independent review: direct 403; official publisher-indexed editorial body read.

Використання: Said + clause; said to recipient + clause; told recipient + clause, limited to the intended message patterns.

### https://dictionary.cambridge.org/grammar/british-grammar/if-or-whether

Access: Main independent review: direct 403; official publisher-indexed editorial body read at https://dictionary.cambridge.org/us/grammar/british-grammar/if-or-whether.

Використання: Simple if/whether alternatives; whether to; whether or not versus if ... or not.

### https://dictionary.cambridge.org/grammar/british-grammar/ask-and-ask-for

Access: Main independent review: direct 403; official publisher-indexed editorial body read.

Використання: Ask to versus ask someone to: different actor roles, not a universal requirement for an object after ask.

### https://dictionary.cambridge.org/dictionary/english/ask

Access: Main independent review: direct 403; official publisher-indexed REQUEST entry read.

Використання: Formal ask + that exists; the B1 practice may specify object + to without declaring every that-clause ungrammatical.

### https://dictionary.cambridge.org/grammar/british-grammar/punctuation

Access: Main independent review: official publisher-indexed main body read; no direct-access success claimed.

Використання: Punctuation follows the complete main sentence: reported questions inside a statement versus an outer direct question.

### https://developers.google.com/search/docs/fundamentals/creating-helpful-content

Access: Main independent direct read of full official article; date reported by main 2026-10-05.

Використання: Useful original complete teaching for learners, not preferred word counts, fabricated authorship or ranking guarantees.

BC main grammar bodies прочитані author безпосередньо; Cambridge publisher-indexed main bodies та Google official article незалежно прочитані main reviewer. Direct403 не позначено як successful direct read. Learner comments не використані як нормативне джерело; signed team replies не підміняють main body. Навчальні сценарії та завдання авторські, publisher exercises не скопійовані. Немає word-count quota, вигаданого авторства чи гарантій ранжування.

## Pre-apply metadata

Independent pure metadata v2 SHA `bb5ff8906eb78fb8175a19f95bddf002cd216a5290f60252e0eb8523f5bff34e`, current PageMetadata SHA `1d782d27483bdc0cff63515a1d69153dc908adbf2c3b3eea47bec35f141e0a9e`. Очікувані title/description та full LearningResource description зафіксовано до apply; canonical/breadcrumb взято з actual BEFORE HTML. V1 із local navigation URL замість canonical явно superseded; metadata-only correction до freeze, без production requests. Це не AFTER-as-oracle.
