# M59 — editorial audit 1.0.0

## Scope і джерело

Три UK owners: Future Continuous Passive (Page 105/category 18, B2), By-phrase (104/1, B1; continuing slot 3 лишається B2), Advanced Passive Voice (282/1, B2). Base: `5c314ccfedaba52934cdfd7cae3aef3ce7f1aa89`. Незалежний source/DB/HTTP BEFORE: `27fbf37afc8efb9152b0f0a90d73dfab7b2114f72a15a9ceaf4c336b678d036d`; native resolver/GET18/18: `46c7688de37b3c64610c35d7738308c4612e3286bcbc509ce73cdd4d0a1fb112`.

Actual H1 збережено: Future Continuous Passive, By-phrase, Advanced Passive Voice. Raw Page title, slugs, category, локаль, старі UUID/anchors/order не змінюються. В Advanced окремо погоджено continuing slot1 box→usage-panels, null→B2 і вилучення технічного heading B2 focus; literal BEFORE зберігається, а виключені course/standalone consumers захищені чинною clone-only межею. Це не заміна course lesson.

## Семантичне рішення

- Future: grammar possibility відокремлено від стилістичної ваги be being; жодних 99% чи blanket ban. Процес у майбутній момент не дорівнює завершенню чи перериванню. Активна парафраза зберігає відомого виконавця, час та аспект; Simple, under repair і going to не оголошено точними синонімами.
- By: by називає виконавця/причину, не лише людину; with залежить від ролі інструмента. Об’єктні займенники та agent/recipient questions явні. Omission не доводить незнання. By the police, by people і with Tom не заборонені без контексту. Часове by Friday і спосіб by email/by hand відокремлено від agent.
- Advanced: компактний самодостатній B2 review: фокус→час/аспект→modal meaning→agent; процес/повторення/стан у Perfect, вимога/дозвіл/можливість/висновок/оцінка, get-passive vs service, tired як прикметник у явному контексті, дві reporting timelines і сфера not. Formal не означає true. Сусідні accepted C1/C2 уроки не переписані.

## Basic/detail mapping

4 meaningful details: Future process-paraphrase → affirmative/sections/1; By omission-context → omit/sections/0; Advanced modal-assessment-deduction → modals/sections/2; Advanced reporting-timeline → reporting/sections/0. Кожен має окреме пояснення з контрольованими прикладами; короткі правила, переклади, ремарки лишаються visible basic. Жодного runtime word threshold або нового дизайну.

## Практика

18 власних tasks: на кожній сторінці 2 form selects + 2 meaning choices + 2 manual/token inputs. 6 canonical input keys + 2 negative contraction variants = 8 accepted variants, не 8 aliases. Кожен slash-bank перемішаний, з точним регістром, пунктуацією і multiplicity. Умови задають форму/початок/місце обставини, тому matcher не вимагає вгадувати приховану фразу. Wrong feedback не оголошує інші природні перспективи неграматичними лише через незбіг.

Окремий widget5/5: finite contract `docs/content/theory-sentence-builders/m59-passive-future-agent-review.v1.json`, SHA `7746c3e0f55354269e87351af71b592db18894266fed2b00b17b83c734c19429`; 15 curated existing questions, 0 new, без old key/answer/options/coursebank writes. Exact bank classes задано у master (1/1/2). Фактичний DB inventory і browser5/5 перевіряються окремими кроками; цей editorial audit не підміняє їх.

Actual shipped-factory offline suite: 22/22 PASS на corrected private draft, 18 tasks/36 options/6 manual/6 token/8 accepted variants/6 deferred translations; network0, no DB/bootstrap. Freeze SHA private draft: `4e90e9750cdd491209b5ae0d6c8cc0d1d41b14b4dd8b045caaf309bfc06bfafb`. Final frozen run окремо. Offline не є browser/widget/DB/no-JS/print acceptance.

## Primary-source journal

### https://dictionary.cambridge.org/grammar/british-grammar/passive-forms

Access: author direct 403; publisher-indexed editorial body read.

Використання: Пасивні ланцюжки, modal continuous be being + V3; не плутати з perfect continuous passive.

### https://dictionary.cambridge.org/grammar/british-grammar/passives-with-and-without-an-agent

Access: author direct 403; publisher-indexed editorial body read at https://dictionary.cambridge.org/grammar/british-grammar/passives-with-and-.

Використання: By для виконавця/причини, with для інструмента; omission за інформаційним фокусом, не доказ незнання.

### https://dictionary.cambridge.org/grammar/british-grammar/future-continuous-i-will-be-working

Access: author direct 403; publisher-indexed editorial body read at https://dictionary.cambridge.org/grammar/british-grammar/future-be-.

Використання: Future Continuous як процес у майбутній момент; will/won’t/’ll.

### https://dictionary.cambridge.org/grammar/british-grammar/passive-uses

Access: publisher-indexed editorial body read.

Використання: Фокус і структура повідомлення, не більша істинність формального пасиву.

### https://dictionary.cambridge.org/grammar/british-grammar/future-perfect-continuous

Access: publisher-indexed editorial body read.

Використання: Will have been + V-ing як активна тривалість до майбутнього моменту, окрема від will be being + V3.

### https://learnenglish.britishcouncil.org/free-resources/grammar/c1/advanced-passives-review

Access: direct main editorial body read; signed/learner comments considered separately.

Використання: Процес, Perfect, модальні форми, get-passive, reporting-time/event-time. Рівень зовнішньої статті не переноситься на B2 target. Коментар Peter 30 May 2026 про зміну стану з had been не використано як загальне правило.

### https://learnenglish.britishcouncil.org/free-resources/grammar/english-grammar-reference/active-passive-voice

Access: direct main editorial body read; signed Peter 15 August 2025 reply separately identified.

Використання: Be + V3; by з людиною чи річчю; get/modal; позиція by-phrase залежить від контексту, без абсолютної заборони іншого порядку.

### https://learnenglish.britishcouncil.org/comment/135088

Access: direct page read; signed Kirk 15 August 2019 reply read separately from learner questions and other threads.

Використання: Прямо розрізнено will be being written і will have been being written. Слова про unusual у сусідньому thread стосуються passive imperatives і не приписуються Future Continuous. Корпусну частоту не стверджуємо.

### https://developers.google.com/search/docs/fundamentals/creating-helpful-content

Access: direct documentation read; no certification claim.

Використання: Самодостатній корисний контент, оригінальні приклади, відсутність word-count quota та вигаданих SEO-гарантій.

Усі навчальні речення/ситуації авторські. Learner comments не використані як нормативне джерело; signed team replies чітко відокремлені від main body. Жодних SEO certification або word-count claims. Усі нові internal href підтверджено fresh native resolver та anonymous local GET; production не відвідували.

## Pre-apply metadata proof

Fresh pure receipt `pre-apply-metadata-v1.json`, SHA `79c8c1af3b89b5f765ef6229f7e8e4b90ed885853878179178922941f45788a1`, побудовано до apply з private author v3 та незалежного BEFORE, без DB/HTTP. Master містить очікувані current PageMetadata title/description, LearningResource description і незмінні raw title/H1/breadcrumb/canonical. Це не AFTER-as-oracle.
