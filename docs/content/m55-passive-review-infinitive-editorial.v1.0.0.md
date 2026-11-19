# M55 — редакційний BEFORE/AFTER, редакція 1.0.0

Статус: reviewed-before-apply. Main незалежно перевірив усі три редакції та фінальні змістові виправлення до freeze. Цей документ фіксує редакційні рішення; він не є доказом локального apply, browser acceptance або доставки.

Base: `0cf633036f4c91f2376976651ec0519d80ec6191`. Незалежний fresh source/DB/HTTP BEFORE main: `7c9adc7e77bd37092cf7dcad7eb62516b260b6d76a8ea1609f2741c42a1a33be`.

## Межі й фактичні початкові джерела

Прочитано три definitions повністю, разом із усіма body JSON, AGENTS та обов’язковими content docs. Дизайн, універсальну в’юху й practice engine не змінено. Курси, category descriptions, Passive Gerund, EN/PL, старі банки, progression й standalone tests — не цілі редактури.

| Owner | Page/category за fresh BEFORE main | Старі slots | Збережений H1 | Hero / власна практика |
|---|---|---|---|---|
| PassiveVoiceCommonMistakesTheorySeeder | 93 / 1 | 0–10, тобто 11 total / 10 continuing | Приклади вживання та типові помилки | A2–B2 / A2 |
| PassiveVoiceFormalityStyleTheorySeeder | 94 / 1 | 0–9, тобто 10 total / 9 continuing | Формальність та стиль пасиву | B2–C1 / B2 |
| PassiveVoicePassiveInfinitiveTheorySeeder | 103 / 17 | 0–8, тобто 9 total / 8 continuing | Passive Infinitive | B2–C1 / B2 |

Common Mistakes і Formality мають ancestry `passive-voice`. Passive Infinitive має ancestry `passive-voice → passive-voice-infinitives-gerund`; це Page owner, не Category description. Category seeder залишається read-only.

Усі старі source indexes, stored type, column і technical level лишаються незмінними. Total continuing source slots разом із hero: 11/10/9; без hero: 10/9/8. Common Mistakes source 5–6 мають B1; Formality source 5–6 — C1; Passive Infinitive source 3 — C1. Вони не вирівнюються до основного рівня уроку. Навігаційний slot не замінено практикою: окремо дописується один practice-set кожному owner.

У Passive Infinitive початковий subtitle не має leading strong. Actual extractor визначив `Passive Infinitive` до em dash; це не припущення про fallback raw Page title. AFTER використовує supported leading strong з тим самим текстом, не змінюючи H1 або global extractor. Raw Page title/slug/category/tags main має зберегти exact при projection.

## Common Mistakes — алгоритм, а не дубль усіх Passive Basics

| Source index / stored type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero A2 | mistakes-hero | Збережено be/V3/питання; абсолютне «V3 ≠ V2» замінено функційним розрізненням зі збігом написання regular/cut/put. «Без do/does» явно обмежено be-passive. Усі hero sentences перекладено. |
| 1 mistakes-grid A2 | mistakes-be | Збережено чотири проблеми be: неповне самостійне речення, is/are, was/were, been у perfect. Letter written yesterday не заборонено як можливу частину більшого речення; додано повний контрприклад і переклад. Perfect лишився як явно розширений приклад, без зміни stored level. |
| 2 mistakes-grid A2 | mistakes-participle | Збережено speak/spoken, broke/broken, writed/written, writing/being written. V2/V3 не мусять мати різне написання. Continuous виправлення зберігає заданий поточний процес і роль листа. |
| 3 mistakes-grid A2 | mistakes-questions | Збережено справжні errors Does...is, wh-order і коротку відповідь. The letter is written? більше не red correction: перенесено в нейтральний contextual usage у slot 6. Додано коротке Did...get...? як межу правила, не переписування Get-Passive. |
| 4 mistakes-grid A2 | mistakes-negatives | Для is not write виправлено назву проблеми: not уже на місці, неправильне V3. Для doesn't finished явно задано задум пасивного повідомлення, не вгадується інший актив. Збережено no/not і been у perfect negative; перше допоміжне пояснено. |
| 5 mistakes-grid B1 | mistakes-agent-instrument | Замість false blanket blacklist by/with — дві прозорі context-bound corrections: відправниця, а не супутниця; неприпустиме by with перед одним інструментом. By не обмежено людьми: причина wind перекладена. Старий Shakespeare with може мати іншу спільну роль у контексті, тому не збережений як універсальний grammar error. |
| 6 usage-panels B1 | mistakes-context | Повторні часові списки скорочено до перевірки first auxiliary / been / being / modal, без втрати потрібних моделей. Тут видимо розміщено echo question, необов’язковий агент someone і контекстний нейтральний порядок. Жоден із цих випадків не перекреслений як автоматично неграматичний. |
| 7 comparison-table A2 | mistakes-transformations | Збережено всі чотири старі перетворення. Active і Passive мають повний переклад; окремо пояснено, яку інформацію про агента опускає коротша версія. Час, aspect, must і ролі не губляться. |
| 8 forms-grid A2 | mistakes-checklist | Старі повторні питання перегруповано в послідовність зміст → підмет → допоміжні → V3 → питання/not → ролі → доречність. Немає хибного універсального be-only або do-ban. |
| 9 summary-list A2 | mistakes-summary | Збережено короткі перевірки, прибрано категоричне «active природніше завжди». Не додаються десятки повторних прикладів із сусідніх уроків. |
| 10 navigation-chips footer | mistakes-navigation | Збережено 11 старих topics через explicit destinations accepted M54; current owner Common Mistakes. |

Один meaningful detail `mistakes-echo-question` належить `mistakes-context /sections/0`. Basic уже відрізняє нейтральний запит від перепитування; detail розбирає попередню репліку, інтонацію й межі висновку, а не приховує основне правило або переклад.

### Окреме рішення про історичне fronting

BEFORE містив `By the team the report was prepared.` як нібито безумовно неправильний порядок. Requested primary sources не підтвердили exact rare sentence або універсальну заборону fronted by-phrase. Тому learner basic не оголошує всі винесення неможливими й не подає цей конкретний недостатньо перевірений порядок як нейтральну норму. Замість нього показано звичайний by-after-verb і контраст виконавців у тому самому нейтральному порядку. У A2 завданні нейтральний порядок задається явно. Це редакційне усунення непідтвердженої абсолютної оцінки, не нове grammar rule про всі види fronting.

## Formality & Style — мета повідомлення, не шкала formal/passive

| Source index / stored type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero B2 | style-hero | Active/passive пояснено як організацію теми й нової інформації. Вилучено false «пасив додає об’єктивність»; приклади оригінальні, нейтральні, повністю перекладені. |
| 1 usage-panels B2 | style-contexts | Збережено потреби процедури, оголошення, новини й технічного опису. Замість сторонніх наукових/правових тверджень — вигадані побутові й робочі ситуації. Кожний пункт названий, має semantic accent, rule, EN/UK і видиму межу. |
| 2 comparison-table B2 | style-information | Збережено report і mistakes examples. I/we не названо «informal»; показано додану явно названу відповідальність. З Mistakes were made не виводиться автоматично We made mistakes. |
| 3 usage-panels B2 | style-editing | John/ball і Shakespeare/book більше не Unnatural/Awkward grammar errors: обидві конструкції перекладені й оцінюються за фокусом. Повтор was у ряді — граматичний; показано скорочення повтору без вигаданого we. Непотрібну бібліографічну ремарку прибрано за main review. |
| 4 forms-grid B2 | style-be-get | Збережено be/get і позитивний promoted. «Тільки be в усіх наукових текстах» замінено на typical register і конкретні вимоги жанру. Got signed перекладено нейтрально без доданого «таки», якщо after all немає в англійському прикладі. |
| 5 usage-panels C1 | style-effects | Об’єктивність замінено розрізненням неособового reporting й доказовості. Незалежність походить зі слова independently, не пасиву. Невказаний агент відділений від припущення про мотиви автора; додано зв’язність given/new. |
| 6 comparison-table C1 | style-academic | Збережено academic editing need, але немає непідтвердженого universal rule журналів. APA представлено лише як конкретну редакційну систему. Усі EN версії повністю перекладено, не підмінено UA style labels. Змішаний стан не втрачає того, кого речення прямо не називало. |
| 7 stored mistakes-grid B2 → neutral usage data | style-alternatives | Початкові stylistic wrong/right не є справжніми grammar errors. Master має finite `semantic_component: usage-panels` для exact slot 7, data.sections і жодного wrong/right. Погоджений bounded nonvisual data mapping передає його до вже наявного компонента зі збереженням stored type/UUID. Немає нового view/CSS або довільного payload-driven дизайну. |
| 8 summary-list B2 | style-summary | Замість шкали active/informal passive/formal — перевірки мети, явного агента, змісту й вимог видання. |
| 9 navigation-chips footer | style-navigation | Ті самі 11 topics/explicit URLs, current owner Formality. |

У neutral slot 7 залишено чотири корисні питання: be/get у конкретній меті, м’яч/John/Mary, мій досвід/учитель, сталий `A good time was had by all`. Idiom перевірено за редакційним словником Cambridge; його не оголошено неграматичним і не зроблено продуктивним правилом для кожного have. After all у новому прикладі get має окремо перекладене значення й не маскується як повна тотожність be.

Два meaningful details:

- `style-new-agent-focus` → `style-editing /sections/1`: given object/new agent і причина зберігати важливу by-phrase.
- `style-responsibility-inference` → `style-effects /sections/1`: структура речення проти сильнішого висновку про мотиви автора.

На mapped slot 7 details не додаються: всі відмінності видимі в basic. Короткі примітки не сховані.

## Passive Infinitive — ролі та відносний час

| Source index / stored type | AFTER key | Редакційне рішення |
|---|---|---|
| 0 hero B2 | infinitive-hero | Збережено to be / to have been, але прибрано false «окремий теперішній/майбутній час». Усі hero речення мають переклади; hope/expect/seem не гарантують події. |
| 1 forms-grid B2 | infinitive-forms | Збережено чотири active/passive simple/perfect форми й старі корисні write/letter/book приклади. Окремо передано роль виконавця/отримувача та claim як твердження, не доказ. |
| 2 usage-panels B2 | infinitive-simple | Збережено need/want/expect, seem/appear, would like/prefer. Правила не надають усім дієсловам однакового object-pattern. Locked явно показано як можливий стан, поруч є однозначніший repeated cleaning. Додано питання повного речення й not to / matrix-negation із видимим смисловим контрастом. |
| 3 usage-panels C1 | infinitive-perfect | Збережено попередню дію й reporting; додано минулу точку відліку та очікуване завершення до майбутнього строку. Modal форми винесені в чітко названий related pattern без to; should/could не зведено до одного past deduction. Нереалізований could результат має явне nobody took it, а не двозначне luckily we found it. |
| 4 usage-panels B2 | infinitive-patterns | Збережено потрібні want/need/expect/would like/prefer/wish/seem/appear/happen/turn out/reporting потреби; розрізнено I invite / I invited / Maya invited за ролями. Перевантажене theory turned out to be proven wrong аргументовано замінено ключем із turn out, не підміною seem/prove. Intelligent відділено як adjective complement, не passive. Додано adjective ready та noun plan + for-participant, не універсальне for-rule. |
| 5 comparison-table B2 | infinitive-time-comparison | False колонку «тепер/майбутнє проти минулого» замінено конкретними відносними часовими сценаріями: now routine, past hope, previous event, expected future completion. Should без to не названо to-perfect прикладом. |
| 6 mistakes-grid B2 | infinitive-mistakes | Залишено справжні помилки V3, попередньої події, порядку have/been і зайвого to після must. Regional needs finished прибрано з universal red error й розглянуто у detail; основна модель й needs finishing видимі в basic/summary. |
| 7 summary-list B2 | infinitive-summary | Збережено формули/моделі, виправлено часові й модальні абсолюти. Короткі EN рольові фрагменти одразу перекладено. |
| 8 navigation-chips footer | infinitive-navigation | Збережено лише два початкові topics; Passive Gerund — explicit вкладений URL, без модернізації сусіда/category. |

Два meaningful details:

- `infinitive-need-variation` → `infinitive-simple /sections/0`: standard need-to-passive, допустиме need-ing у відкритому завданні, regional need+participle за Yale. Останній приклад явно названий регіональним, не основним ключем.
- `infinitive-work-to-do` → `infinitive-patterns /sections/0`: noun/task construction, work to do / work to be done, чому механічна passive-вимога після кожного іменника хибна.

## Власна практика — 18 tasks, не 33 нові питання

| Урок | Selects: форма | Choices: зміст | Inputs: manual / tokens |
|---|---|---|---|
| Common A2 | doors/were у вчорашній події; taken як V3 | named Olia-agent; past-negative yesterday проти habitual-negative | Past Simple neutral question; Present Simple negative verbal passive |
| Formality B2 | Present Perfect passive; continuous passive question | explicit We responsibility; задана bench-theme + збережений agent/day | draft-theme passive зі збереженим team; explicit We active зі збереженим before lunch |
| Infinitive B2 | simple to-passive need; previous-event perfect after seem | Nina як patient; hope не підтверджує event | need + to-passive printer/deadline; want + object + to-passive guests |

Кожний input — одне завдання з двома способами відповіді. Передано чинні supported fields before/after/answer/accepted/feedback, source_index 1–6. Slash-banks містять усі canonical instances в перемішаному порядку; немає готового речення в placeholder/hint. Формулювання задають час, лексику, потрібний початок і конструкцію там, де інакше можливі кілька природних відповідей. Ізольована перевірка реальної незміненої shipped factory підтвердила 18 own tasks, 6 manual і 6 token inputs, 7 aliases та 34 visible options. Це offline перевірка, не браузерне приймання; фактичні runtime результати наводяться в окремому звіті.

Common negative input підтримує full і contracted isn't. Стилістичні або граматичні альтернативи у feedback не названо неграматичними лише за невідповідність finite task: active з іншим початком, природне shop is not open як опис стану, needs repairing поза явно заданим to-infinitive. Meaning stimuli, чиї переклади розкривали б відповідь, повністю перекладені в after-Check feedback; видимий український критерій достатній до перевірки.

Усі 18 власних вправ авторські, не копія першої п’ятірки linked banks. Частина навчальних прикладів навмисно збережена з початкової теорії, оскільки була корисною та правильною; самі нові задачі створено під точні потреби уроку.

## Окремі widgets — 15 reused, 0 new за незалежним bank review

Bank peer прочитав source/live умови, answers, tokens/options/hints. Exact banks:

- `Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceCommonMistakesAllLevelsLessonSeeder`, A2, pool 12, selected 37371–37375.
- `Database\Seeders\V3\Polyglot\PolyglotPassiveVoiceFormalityStyleAllLevelsLessonSeeder`, B2, pool 12, selected 36243–36247.
- `Database\Seeders\V3\Polyglot\PolyglotPassiveVoicePassiveInfinitiveAllLevelsLessonSeeder`, B2, pool 12, selected 38979–38983.

Банківські питання не входять у 18 own tasks. Нових questions/answers/options/links не передбачено. All 15 selected hints/verb_hints порожні; exact native token case підтверджено bank peer, тому M54 case map не копіюється.

П’ять Formality UK context prefixes погоджені main і збережені у фінальному `docs/content/m55-passive-review-infinitive-bank-audit.v1.json`. Вони явно задають тему або автора, не змінюючи англійських ключів, tokens/options та оригінальних умов. Bounded render-only projection додає лише reviewed prefix до клонованої моделі; runtime guard та фактичне SSR/browser приймання перевіряються окремо. Жодного global lowercase, grader або JS change.

## Джерела і невизначеність

Повний actual URL/access journal — у `docs/content/m55-passive-review-infinitive.v1.0.0.json`, поле `sources`. Direct 403 не названо direct reading. Cambridge primary publisher-indexed texts використано як окремий спосіб доступу; запитаний `infinitives` не підтверджено як actual indexed page, записано фактичну localisation `infinitives-with-and-without-to`.

APA та Yale прочитані прямо. APA — конкретна редакційна система, не правило всіх жанрів. Yale — опис варіативності, не вимога зробити regional модель основною. British Council і Google Search Central прочитані як direct editorial body; learner comments і exercise banks не копіювалися. Corpus rows Cambridge не видано за редакційну універсальну норму.

Немає вигаданих author/reviewer/certification, rating/FAQ schema, keyword quotas, traffic metrics або ranking gains. Нові приклади — навчальні ситуації, а не твердження, для яких потрібна юридична/медична/інша високоризикова перевірка.

## Межа редакційного погодження

Редакційний review і frozen master не підміняють guarded local apply із preview/backup/transaction/postconditions/no-op та реальні HTTP/browser перевірки. Exact metadata/descriptions, body/UUID/anchors/ancestry, усі supported SSR fields, inputs/aliases/multiplicity й widget projection мають власні технічні докази. Їхні завершені або неперевірені статуси фіксує `docs/reports/seo-m55-passive-review-infinitive-standard-view.md`. Цей editorial artifact не заявляє наперед runtime PASS.
