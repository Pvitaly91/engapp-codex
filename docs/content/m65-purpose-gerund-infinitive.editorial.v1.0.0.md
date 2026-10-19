# M65 — редакційна карта BEFORE → AFTER

База: `8371a96ad8b0254d5e88d2265dff57d91cb87190`. Тільки UK Page 35/195/198, categories 34/33/33; ancestry clauses-and-linking-words та verb-patterns. Навчальна редакція не змінює дизайн.

## Незмінні межі

Purpose має 7 continuing blocks плюс subtitle; Gerund і To-Infinitive — по 6 плюс subtitle. Не підганяємо всі сторінки під одну кількість. Збережено titles/H1/slugs/UUID/anchors/order/columns та вихідні technical levels: B1,null,null,null,null,null,null / A2,A2,A2,A2,A2,null / A2,A2,A2,A2,A2,null. У Purpose лише п’ять явних переходів box → forms-grid / usage-panels / comparison-table / mistakes-grid / summary-list із видаленням старого технічного heading, без просування null до B1. Native A2 owners не мають таких переходів. Кожному уроку додано власний practice-set B1/A2/A2, не замість navigation.

## Предметні рішення

Purpose: базові моделі to, in order to, so as not to та so that; той самий/інший виконавець; can/could, will/would, Present Simple у відповідному контексті; informal omission of that; функція for + -ing і for + noun; мета проти здійсненого результату. For the children to play наведено як окрему модель із явним виконавцем, не універсальну заборону. Заперечення головної дії й негативна мета не ототожнюються.

Gerund: дія як тема/доповнення, а не автоматичний Continuous; spelling e/-ing, consonant doubling, ie/y; verb patterns enjoy/avoid/mind/finish/keep; prepositions, noun/pronoun alternatives, prepositional to; not + -ing, ordinary questions and context-dependent time. Повні доповнення й природні короткі відповіді mind залишено у basic.

To-Infinitive: базова форма після to, be/have; want/decide/need/hope/promise/plan/learn; want someone to з точним виконавцем; purpose проти двох здійснених дій; adjective/noun + infinitive, too/enough, something to; help + bare або to та prepositional to. Не вигадано глобальної заборони split infinitive: not to / to not приймаються в явно дозволеному input. Did not decide і decided not передають різні заперечення.

Точні old-preserved/clarified/new/replaced навчальні рішення записано окремо в кожному master.lessons[].editorial_mapping, не видаючи нові приклади за старий source.

## Basic/detail

Лише три meaningful details: purpose-intention-outcome → purpose-roles /sections/2; gerund-time-function → gerund-usage /sections/3; infinitive-adjective-roles → infinitive-usage /sections/2. Кожна пояснює окреме змістовне розрізнення; усі необхідні правила, переклади, застереження й короткі ремарки — у visible basic. Немає глобального word-count threshold чи нової верстки.

## Власна практика та конструктор

18 tasks, 2 selects + 2 meaning choices + 2 manual/token inputs на урок. 30 visible options; 8 accepted input variants. У meaning-choice англійська умова й український критерій видимі до Check; повні переклади й пояснення — після перевірки кожного вибору. Feedback plain text для незмінного x-text, без HTML/entities. Невідомі typo зберігають generic fallback. Grammatical-but-not-requested alternatives відокремлені від помилок форми. Пунктуація не оцінюється через зміну global normalize.

Окремий нижній widget: 15 selected thematic TYPE4 questions, рівні B1/A2/A2. Прочитано 36 old candidates: використано 12; 24 лишилися незміненими поза finite selection. Purpose отримує лише 3 нові різні ситуації. Нуль нових options; усі 31 distinct word lookups мають єдиний exact SQL-collation match. Перед freeze third draft example замінено з shut/let на closed/wake, щоб не створювати новий регістр-normalizer і не змінювати старі shared options. Повторений the в нових реченнях має окремі answer instances. Старі 14-column rows, options, ordered answers, hints, verb hints і pivots захищено opaque hashes; clone-only conditions діють лише на exact canonical practice.

## Джерела й перевірка мови

British Council, Cambridge Grammar Today та Google Search Central: actual URLs, перевірені правила й direct/indexed-body limitations у master.sources. Direct 403/error не названо успішним читанням. Publisher exercise banks і учнівські коментарі не копіювалися; приклади власні. Не вигадано human reviewer, certification, search volume чи гарантій ranking.

## Freeze та приватність

Frozen teaching master 1.0.0, native AFTER patch, teaching BEFORE, builder contract, minimal question supplement і teaching-only bank audit. Приватні SQL rows/timestamps, runtime identity, proofs, backups, HTTP receipts і screenshots не versioned. SHA-256 прив’язує точні bytes; робочий шлях не є дозволом на production. Цей документ не замінює фінальний apply/browser report.
