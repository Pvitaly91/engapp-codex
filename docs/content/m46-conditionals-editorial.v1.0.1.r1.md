# M46 — редакторський BEFORE/AFTER та карта компонентів · 1.0.1.r1

Статус: окрема documentation-only repair редакція від 2026-10-11. Вона виправляє серіалізацію редакторських рішень і документацію джерел; не є новою версією навчального пакета чи дозволом на повторний apply.

Навчальне джерело: `docs/content/m46-conditionals.v1.0.1.json`, версія `1.0.1`, SHA-256 `9d9f41d4616fdda57e551de97534642dba8c6c2b6be85cdeeef522667fc29048`. Заморожені редакції 1.0.0 і 1.0.1, runtime, definitions, source projection, контрольні хеші пакета та БД не змінено. Цей файл не підмінює попередній editorial і не змінює навчальні тексти.

База початкового BEFORE: `c9d6e77f66f91688fd405463ed25acc8c18591ab`. Попередній editorial зафіксував незалежний BEFORE із served ROOT та звірення live UK rows до source sync. Редакція r1 не виконує нових HTTP/браузерних перевірок, тестів або операцій із БД.

Пошукові наміри нижче — редакційні потреби читача; вимірів частотності чи позицій немає. Повні карти старих англійських полів перенесено без змін: по 19 записів для кожного уроку, 57 разом. Позначення AFTER у цих історичних картах збережено для простежуваності; фактична структура й тексти визначаються immutable master 1.0.1.

## Фактична навігація master 1.0.1

- Zero Conditional: 2 елементи — Zero Conditional та First Conditional.
- First Conditional: 10 початкових елементів розділу збережено.
- Second Conditional: ті самі 10 початкових елементів розділу збережено; скорочення до First/Second немає.

Повний набір із 10 елементів: Zero Conditional; First Conditional; Second Conditional; Third Conditional; Mixed Conditionals; Unless / Provided / As long as; In case; Wish / If only; Would rather; Had better. Поточний елемент у кожному уроці відповідає його власному slug. Це опис даних master, не твердження про нову перевірку доступності посилань.

## Zero Conditional

- Розпізнати закономірність, звичну реакцію чи правило конкретної системи.
- Побудувати ствердження, заперечення та пряме запитання.
- Змінити порядок частин і поставити англійську кому.
- Розрізнити близькі граматично правильні моделі за заданим контекстом.
- Уникнути справжньої помилки форми й перевірити відповідь у вправі.

Компоненти: hero, forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips, usage-panels, comparison-table, usage-panels, practice-set. 0 meaningful detail; 6 tasks / 6 controls.

### Редакторські зміни з master 1.0.1

Об’єкти `editorial_changes` розгорнуто за полями BEFORE / AFTER / причина; технічний `change`, коли він є, збережено. Рядкові записи наведено повністю. Історичні формулювання про інтеграцію нижче є записами джерела, а не новим планом робіт r1.

1. Редакторське рішення.

   BEFORE: Hero: результат завжди правдивий; інструкція прирівняна до базової present + present моделі.

   AFTER: Типовий або повторюваний зв’язок; окремо описана наказова форма інструкції.

   Причина: Zero охоплює звички й локальні правила; imperative не є Present Simple результату.

2. Редакторське рішення.

   BEFORE: If you heat water to 100°C, it boils. Приклад без зазначення умов.

   AFTER: If ice gets warm enough, it melts. Збережено корисну ілюстрацію фізичного процесу без твердження про тиск.

   Причина: Уточнення обмежень не має перевантажувати базовий шар.

3. Редакторське рішення.

   BEFORE: If people eat too much sugar, they gain weight.; If you don't water plants, they die.

   AFTER: Приклади не перенесено; їхню роль загальних зв’язків виконують лід і явно задані правила пристрою.

   Причина: Ці категоричні біологічні узагальнення не потрібні для пояснення граматики; не представляємо умовну модель як гарантію.

4. Редакторське рішення.

   BEFORE: If we take the bus, we arrive on time.

   AFTER: If we take the bus, we usually arrive on time.

   Причина: Явно показано звичний, не гарантований наслідок; факт додавання usually задокументовано.

5. Редакторське рішення.

   BEFORE: «will після if» + wrong: If you heat ice, it will melt.

   AFTER: Вилучено хибну позначку wrong. First зі will у головній частині пояснено як правильну модель конкретного наслідку.

   Причина: У старому прикладі will стоїть у результаті, не після if; правильний First не є помилкою.

6. Редакторське рішення.

   BEFORE: wrong: If I drank coffee late, I don't sleep well.

   AFTER: Не використано як безконтекстно неграматичний приклад. Збережено Present Simple coffee habit у hero; mistakes тепер містять однозначні помилки узгодження.

   Причина: Змішані часові відношення можуть залежати від контексту, тому для A2 потрібна справжня помилка форми.

7. Редакторське рішення.

   BEFORE: wrong: If it rains tomorrow, the match is cancelled.

   AFTER: Приклад збережено, перекладено та контекстуалізовано як вже ухвалене правило/рішення; порівняно з will be cancelled.

   Причина: Tomorrow самостійно не визначає правильність граматичної моделі.

8. Редакторське рішення.

   BEFORE: Форми, hero і частина інших прикладів без перекладів; немає питань, do/does-заперечень, коми, меж if/when або власної практики.

   AFTER: Додано ці навчальні цілі у підтримуваних forms/usage/table/practice полях із перекладами.

   Причина: Покриття редакційних потреб читача, а не keyword stuffing.

9. Редакторське рішення.

   BEFORE: Великий однаковий footer із 10 тем.

   AFTER (історичний запис у master): У чернетці лише Zero та First; остаточні destinations підтверджує main до apply.

   Причина: Доречне близьке порівняння замість розгортання позаскопових уроків.

   Фактичний стан master 1.0.1: footer уже містить рівно два елементи — Zero Conditional і First Conditional. Чернеткове формулювання наведено лише для точного розгортання вихідного рішення; воно не означає, що destinations досі очікують вибору.

10. Редакторське рішення.

   BEFORE: Native manual engine має загальний неперекладений fallback для довільної хибної відповіді.

   AFTER: Шість авторських завдань використовують тільки підтримані selects/choices із повним перекладеним feedback для кожного доступного варіанта.

   Причина: Це вибір навчальних даних без зміни JS; ручне введення, token bank і linked own-bank лишаються поза новими авторськими controls.

11. Видимі назви й початкові slots узгоджено; переклад неправильного речення описує його задум, а не узаконює помилкову англійську форму.

### Старі англійські поля: збереження або причина заміни

- BEFORE `hero/rules/0/example`: If you heat water to 100°C, it boils.
  AFTER: zero-hero («Огляд»). Замінено коротким оглядовим прикладом у тому самому hero: відокремлено значення від гарантії результату, додано точний переклад.
- BEFORE `hero/rules/1/example`: If I drink coffee late, I don't sleep well.
  AFTER: zero-hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `hero/rules/2/example`: If the light is red, stop.
  AFTER: zero-hero («Огляд»). Замінено коротким оглядовим прикладом у тому самому hero: відокремлено значення від гарантії результату, додано точний переклад.
- BEFORE `forms-grid/items/0/subtitle`: If people eat too much sugar, they gain weight.
  AFTER: zero-forms («1. Форма: умова й результат»). Картку форми перебудовано для явного поділу умови/результату, заперечення або питання; відповідна конструкція збережена в підтримуваних rules[].example.
- BEFORE `forms-grid/items/1/subtitle`: If you press this button, the machine starts.
  AFTER: zero-forms («1. Форма: умова й результат»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/2/subtitle`: The machine starts if you press this button.
  AFTER: zero-forms («1. Форма: умова й результат»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/3/subtitle`: If you need help, call reception.
  AFTER: zero-forms («1. Форма: умова й результат»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/0/examples/0/en`: If ice gets warm, it melts.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Той самий випадок вживання уточнено в іменованому пункті з власними EN/UK парами; редакція зменшує дублювання й неоднозначність контексту.
- BEFORE `usage-panels/sections/0/examples/1/en`: If you don't water plants, they die.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Той самий випадок вживання уточнено в іменованому пункті з власними EN/UK парами; редакція зменшує дублювання й неоднозначність контексту.
- BEFORE `usage-panels/sections/1/examples/0/en`: If she goes to bed late, she is tired in the morning.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/1/examples/1/en`: If we take the bus, we arrive on time.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Той самий випадок вживання уточнено в іменованому пункті з власними EN/UK парами; редакція зменшує дублювання й неоднозначність контексту.
- BEFORE `usage-panels/sections/2/examples/0/en`: If visitors want a badge, they sign in here.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/2/examples/1/en`: If the alarm rings, leave the building immediately.
  AFTER: zero-uses («2. Коли вживаємо Zero Conditional»). Той самий випадок вживання уточнено в іменованому пункті з власними EN/UK парами; редакція зменшує дублювання й неоднозначність контексту.
- BEFORE `mistakes-grid/items/0/wrong`: If you heat ice, it will melt.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Вилучено false-error framing або неоднозначну часову умову; замість неї показано справжню помилку форми. Контекстні відмінності пояснено у visible basic.
- BEFORE `mistakes-grid/items/0/right`: If you heat ice, it melts.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Вилучено false-error framing або неоднозначну часову умову; замість неї показано справжню помилку форми. Контекстні відмінності пояснено у visible basic.
- BEFORE `mistakes-grid/items/1/wrong`: If I drank coffee late, I don't sleep well.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Вилучено false-error framing або неоднозначну часову умову; замість неї показано справжню помилку форми. Контекстні відмінності пояснено у visible basic.
- BEFORE `mistakes-grid/items/1/right`: If I drink coffee late, I don't sleep well.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/2/wrong`: If it rains tomorrow, the match is cancelled.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/2/right`: If it rains tomorrow, the match will be cancelled.
  AFTER: zero-mistakes («3. Справжні помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.

## First Conditional

- Пов’язати значення умови з часом і формою результату.
- Побудувати ствердження, заперечення та пряме запитання.
- Змінити порядок частин і поставити англійську кому.
- Розрізнити близькі граматично правильні моделі за заданим контекстом.
- Уникнути справжньої помилки форми й перевірити відповідь у вправі.

Компоненти: hero, forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips, practice-set. 1 meaningful detail; 6 tasks / 6 controls.

### Редакторські зміни з master 1.0.1

Об’єкти `editorial_changes` розгорнуто за полями BEFORE / AFTER / причина; технічний `change`, коли він є, збережено. Рядкові записи наведено повністю. Історичні формулювання про інтеграцію нижче є записами джерела, а не новим планом робіт r1.

1. Шість початкових source slots збережено за type/column/level; нова практика додається окремим сьомим блоком, без зміни renderer.

2. Hero більше не представляє First Conditional як гарантію результату або фіксований рівень імовірності.

3. Коротке правило про відсутність will після if обмежено звичайною майбутньою умовою; meaningful sidecar пояснює готовність.

4. Формули доповнено запереченнями, стандартним прямим запитанням, короткою відповіддю, питальним словом, порядком частин і комою.

5. Старий приклад із press this key tomorrow/file opens перенесено з false-error framing до видимого контекстного порівняння; альтернативний системний зміст збережено.

6. Реальну помилку will to start збережено з точним перекладом і поясненням; додано справжню помилку doesn't arrives.

7. Власна практика складається лише з supported selected-response controls: 3 selects + 3 choices; немає manual/token fallback або змін answer engine.

8. Наявні navigation destinations скопійовано без зміни; їхню локальну доступність має перевірити інтеграційний етап.

   Фактичний стан master 1.0.1: усі 10 початкових navigation destinations збережені. Редакція r1 не робить нового висновку щодо HTTP-доступності.

9. Усі нові EN речення мають UK пару в тому самому basic/detail матеріалі або в per-option feedback; переклади не замінюють граматичного коментаря.

10. Редакторська інтеграція зберегла корисні старі приклади про дощ, дзвінок, потяг, прогулянку, автобус, взаємну допомогу, каву, перерву й прохання до Анни; додала точні переклади там, де їх не було. Старе передбачення успіху на іспиті замінено практичним рішенням без відтінку життєвої гарантії.

11. Видимі назви й початкові slots узгоджено; переклад неправильного речення описує його задум, а не узаконює помилкову англійську форму.

### Старі англійські поля: збереження або причина заміни

- BEFORE `hero/rules/0/example`: If it rains, we'll stay at home.
  AFTER: m46-first-hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `hero/rules/1/example`: If you study, you'll pass the exam.
  AFTER: m46-first-hero («Огляд»). Замінено коротким оглядовим прикладом у тому самому hero: відокремлено значення від гарантії результату, додано точний переклад.
- BEFORE `hero/rules/2/example`: If she calls, I'll answer.
  AFTER: m46-first-hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/0/subtitle`: If we leave now, we will catch the train.
  AFTER: m46-first-forms («1. Форма: ствердження, заперечення й запитання»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/1/subtitle`: If she studies, she will feel more confident.
  AFTER: m46-first-forms («1. Форма: ствердження, заперечення й запитання»). Картку форми перебудовано для явного поділу умови/результату, заперечення або питання; відповідна конструкція збережена в підтримуваних rules[].example.
- BEFORE `forms-grid/items/2/subtitle`: I will call you if I arrive late.
  AFTER: m46-first-forms («1. Форма: ствердження, заперечення й запитання»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/3/subtitle`: If you finish early, you can go home.
  AFTER: m46-first-forms («1. Форма: ствердження, заперечення й запитання»). Картку форми перебудовано для явного поділу умови/результату, заперечення або питання; відповідна конструкція збережена в підтримуваних rules[].example.
- BEFORE `usage-panels/sections/0/examples/0/en`: If the weather improves, we'll go for a walk.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/0/examples/1/en`: If you don't hurry, you'll miss the bus.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/1/examples/0/en`: If you help me now, I'll help you later.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/1/examples/1/en`: If they arrive early, we'll make coffee.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/2/examples/0/en`: If you finish this section, you can take a break.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/2/examples/1/en`: If Anna calls, tell her I'm in a meeting.
  AFTER: m46-first-uses («2. Коли вживаємо First Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/0/wrong`: If it will rain, we'll stay at home.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Вилучено false-error framing або неоднозначну часову умову; замість неї показано справжню помилку форми. Контекстні відмінності пояснено у visible basic.
- BEFORE `mistakes-grid/items/0/right`: If it rains, we'll stay at home.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/1/wrong`: If you press this key tomorrow, the file opens.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/1/right`: If you press this key tomorrow, the file will open.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/2/wrong`: If she arrives early, we will to start on time.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/2/right`: If she arrives early, we will start on time.
  AFTER: m46-first-mistakes («3. Типові помилки у формі»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.

## Second Conditional

- Зрозуміти уявний сценарій, а не вважати кожну ситуацію неможливою.
- Побудувати умову й результат, заперечення та питання.
- Відрізнити минулу форму від минулого часу і were від розмовного was.
- Обрати would, could чи might за потрібним значенням.
- Порівняти First і Second у визначеному контексті та перевірити форму на власних вправах.

Компоненти: hero, forms-grid, usage-panels, mistakes-grid, summary-list, navigation-chips, usage-panels, usage-panels, comparison-table, comparison-table, practice-set. 0 meaningful detail; 6 tasks / 6 controls.

### Редакторські зміни з master 1.0.1

Об’єкти `editorial_changes` розгорнуто за полями BEFORE / AFTER / причина; технічний `change`, коли він є, збережено. Рядкові записи наведено повністю. Історичні формулювання про інтеграцію нижче є записами джерела, а не новим планом робіт r1.

1. Ідентифікатор: `yesterday-case-replaced-explicitly`.

   BEFORE: If I knew her number yesterday, I would call now. → If I knew her number, I would call her now.

   AFTER: Basic містить лише уявне теперішнє: If I knew her number, I would call her now.; передумова прямо вказує, що зараз я не знаю номера.

   Причина: У старій парі часовий зміст silently змінювався через вилучення yesterday. Старий mixed-time задум не оголошено виправленим реченням; його вилучено з базового уроку й задокументовано. Mixed Conditionals не переписується.

2. Ідентифікатор: `was-register-not-error`.

   BEFORE: If I was you, I would leave. позначено як неправильне.

   AFTER: Порада зі were збережена; were/was пояснено окремими semantic points із контекстом формальної навчальної моделі та неформального мовлення.

   Причина: Регістр і вибір стандартного навчального варіанта не дорівнюють глобальній неграматичності if + was.

3. Ідентифікатор: `bounded-if-would-rule`.

   BEFORE: Заголовок: Ставити would у if-clause.

   AFTER: Конкретна помилка описує умову про наявність часу; basic note відрізняє її від ввічливих if + would.

   Причина: Немає глобального blacklist чи нового grader.

4. Ідентифікатор: `forms-reorganised`.

   BEFORE: Картки основної моделі, If I were you, result first та could/might без перекладів.

   AFTER: Чотири компактні картки ствердження, заперечної умови, заперечного результату й питання. Порада, порядок частин і could/might перенесені до відповідних usage/table blocks, старі корисні приклади збережені.

   Причина: Формули в forms-grid; приклади та значення не дублюють повне правило в кожному блоці.

5. Ідентифікатор: `new-supported-practice`.

   BEFORE: Окремого practice-set у definition немає.

   AFTER: Шість власних завдань із шістьма controls, явними відповідями та feedback; linked bank не редагується.

   Причина: Потрібне покриття значення, форми, заперечення, питання, контексту й типової помилки.

6. Ідентифікатор: `selected-response-data-only`.

   BEFORE: Авторські інтерактивні вправи відсутні.

   AFTER: Один native practice-set: 3 selects + 3 choices; один control на завдання. Власні manual/token tasks не додаються; чинний own-bank widget залишається.

   Причина: Поточний feedback map забезпечує окремий переклад і розбір кожного відомого вибору без зміни Blade/JS. Немає нової перевірки довільного тексту чи прихованого ключа в початкових options.

7. Видимі назви й початкові slots узгоджено; переклад неправильного речення описує його задум, а не узаконює помилкову англійську форму.

### Старі англійські поля: збереження або причина заміни

- BEFORE `hero/rules/0/example`: If I had more free time, I would learn Italian.
  AFTER: hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `hero/rules/1/example`: If we lived by the sea, we would swim every day.
  AFTER: hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `hero/rules/2/example`: If I were you, I would wait.
  AFTER: hero («Огляд»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/0/subtitle`: If I knew the answer, I would tell you.
  AFTER: forms («1. Форма Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/1/subtitle`: If I were you, I would apologise.
  AFTER: forms («1. Форма Second Conditional»). Картку форми перебудовано для явного поділу умови/результату, заперечення або питання; відповідна конструкція збережена в підтримуваних rules[].example.
- BEFORE `forms-grid/items/2/subtitle`: I would travel more if I had enough money.
  AFTER: forms («1. Форма Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `forms-grid/items/3/subtitle`: If she practised more, she could win.
  AFTER: forms («1. Форма Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/0/examples/0/en`: If I had a car, I would drive to work.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/0/examples/1/en`: If she lived closer, we would see her more often.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/1/examples/0/en`: If he won the lottery, he would buy a house in Spain.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/1/examples/1/en`: If they moved abroad, they would need better English.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/2/examples/0/en`: If I were you, I would talk to her directly.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `usage-panels/sections/2/examples/1/en`: If I were in your position, I would wait one more day.
  AFTER: usage («2. Коли вживаємо Second Conditional»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/0/wrong`: If I would have more time, I would read more.
  AFTER: mistakes («3. Типові помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/0/right`: If I had more time, I would read more.
  AFTER: mistakes («3. Типові помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/1/wrong`: If I knew her number yesterday, I would call now.
  AFTER: mistakes («3. Типові помилки форми»). Не виправляється видаленням yesterday: старий mixed-time задум не розгортається в цьому базовому уроці. Новий приклад прямо задає незнання номера зараз; причину заміни зазначено.
- BEFORE `mistakes-grid/items/1/right`: If I knew her number, I would call her now.
  AFTER: mistakes («3. Типові помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.
- BEFORE `mistakes-grid/items/2/wrong`: If I was you, I would leave.
  AFTER: mistakes («3. Типові помилки форми»). Перенесено з категоричної помилки до пояснення стандартного were й розмовного was; регістр не прирівняно до будь-якої неграматичності.
- BEFORE `mistakes-grid/items/2/right`: If I were you, I would leave.
  AFTER: mistakes («3. Типові помилки форми»). Збережено в післяредакційному матеріалі; додано або уточнено переклад.

## Спільні рішення

Початкові шість source slots кожного уроку збережені; subtitle має позицію 0, старі блоки — 1–6. Нові компоненти додані після них зі сталими uuid_key. IDs/UUID/anchors чинних блоків не змінюються. Навігація залишається footer-компонентом.

Практика використовує один штатний practice-set на урок: 3 selects + 3 choices, по одному required control на завдання. Всі 45 видимих кандидатів мають окремий перекладений feedback; пояснення не є option label. Manual/token author controls не потрібні для цих шести навчальних цілей. Чинний engine не має перекладеного fallback для довільної хибної manual відповіді; тому цю непідтриману потребу усунуто вибором готових selected-response механік, без нового grader або JS. Наявні linked banks і їхні sentence-builder widgets не редагуються. Випробування duplicate tokens, окремого «?» і ручного редагування для нових author controls не застосовуються. R1 не запускає і не засвідчує нових регресійних перевірок цих механік.

Zero/Second: короткі потрібні ремарки залишилися у visible basic, details не вигадувалися. First: окреме пояснення готовності з will має один точний owner /items/0. Повний текст присутній у stored native rules для complete fallback; в погодженій проєкції той самий текст переходить у штатний point-level disclosure.

SEO description у чинній UK в’юсі береться з першого речення hero.intro через PageMetadata::theory; subtitle/Page.text нижчого пріоритету. Title складається зі сталої назви теми. Окремий per-page title override не підтримується, тому нової metadata-інфраструктури немає. Вимога початкового інтеграційного етапу стосувалася перевірки Meta/OG/Twitter/structured data на live HTML після apply зі збереженням local noindex. Ця документаційна редакція не виконує apply і не додає нового твердження про приймання.

## Джерела: фактичні URL та зафіксований спосіб доступу

Нижче наведено всі 13 записів `sources` із master 1.0.1 з поділом за уроком. URL та `access` відтворено точно; повторні записи одного URL не злиті, щоб не втратити походження й різні зафіксовані способи доступу. Дату наведено лише там, де вона є у master. R1 не відкриває джерела повторно й не перетворює доступний пошуковий витяг на успішне повне відкриття сторінки.

### Zero Conditional

1. [British Council — Conditionals: zero, first and second](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-zero-first-second)

   Access (точний запис master): Successful open; editorial grammar section read; learner comments excluded.

   Перевірені положення (точні записи master):

   - Zero for generally true relationships, laws/rules; present + present.
   - First for possible future situations; clause order can vary.

2. [Cambridge English Grammar Today — Conditionals: if](https://dictionary.cambridge.org/grammar/british-grammar/mixed-conditionals)

   Access (точний запис master): Publisher's substantive indexed editorial text read through search. Direct opens of conditionals-if, us/conditionals-if and this alias failed403; not claimed as full-page open.

   Перевірені положення (точні записи master):

   - Real conditions include habitual actions; present + present is supported.
   - First is not a grammatical error merely because a Zero lesson is being studied.
   - Will in a main clause is distinct from will in an if-clause; no global if+will ban.

3. [Cambridge English Grammar Today — If](https://dictionary.cambridge.org/us/grammar/british-grammar/if)

   Access (точний запис master): Publisher's indexed editorial text read through search; direct open403.

   Перевірені положення (точні записи master):

   - If introduces real, imagined or uncertain conditions; context matters.

4. [Cambridge English Grammar Today — If or when?](https://dictionary.cambridge.org/de/grammatik/britisch-grammatik/if-or-when)

   Access (точний запис master): Publisher's indexed English editorial text read through search; original /grammar/british-grammar/if-or-when direct open403.

   Перевірені положення (точні записи master):

   - If/when can both describe repeated or predictable situations.
   - Future if and when do not always have the same implication.

5. [Cambridge English Grammar Today — Conditionals](https://dictionary.cambridge.org/grammar/british-grammar/conditional-sentences)

   Access (точний запис master): Publisher's indexed editorial section Order of clauses read through search.

   Перевірені положення (точні записи master):

   - Initial conditional clauses normally take a comma; final ones normally do not.

6. [Cambridge English Grammar Today — Imperative clauses](https://dictionary.cambridge.org/uk/grammar/british-grammar/imperative)

   Access (точний запис master): Publisher's indexed editorial text read through search.

   Перевірені положення (точні записи master):

   - Imperatives normally use the base form without a subject; negative imperatives use do not/don't.

### First Conditional

1. [British Council LearnEnglish — Conditionals: zero, first and second](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-zero-first-second)

   Access (точний запис master): Direct page opened; current redirect destination recorded.

   Дата, записана у master: 2026-10-11.

   Перевірені положення (точні записи master):

   - First Conditional: future situations treated as possible.
   - Basic if + Present Simple / will + infinitive; clause order may change.
   - Zero Conditional describes general rules; the distinction concerns use and meaning.

   Використання (точний запис master): Rule verification only. Lesson prose, examples and exercises are authored for Gramlyze; source examples are not reproduced.

2. [Cambridge Dictionary — Conditionals: if](https://dictionary.cambridge.org/grammar/british-grammar/mixed-conditionals)

   Access (точний запис master): Official indexed page text read in web search; direct open returned HTTP 403.

   Дата, записана у master: 2026-10-11.

   Перевірені положення (точні записи master):

   - The main clause can contain a modal with future meaning.
   - Will/would in an if-clause can express willingness; the ordinary-future restriction is not universal.

   Використання (точний запис master): Verification of qualified will rule and limited modal-result extension; no copied examples.

3. [Cambridge Dictionary — Conjunctions](https://dictionary.cambridge.org/grammar/british-grammar/conjunctions)

   Access (точний запис master): Official indexed page text read in web search.

   Дата, записана у master: 2026-10-11.

   Перевірені положення (точні записи master):

   - A subordinate clause before the main clause usually ends with a comma; a final subordinate clause normally does not require one.

   Використання (точний запис master): Verification of the order/comma card; no copied examples.

4. [Cambridge Dictionary — Clause types](https://dictionary.cambridge.org/grammar/british-grammar/clause-types)

   Access (точний запис master): Official indexed page text read in web search.

   Дата, записана у master: 2026-10-11.

   Перевірені положення (точні записи master):

   - Standard interrogative order places the auxiliary/modal before the subject.
   - Imperatives use the base verb and normally omit the subject.

   Використання (точний запис master): Verification of question and instruction forms; no copied examples.

### Second Conditional

1. [British Council — Conditionals: zero, first and second](https://learnenglish.britishcouncil.org/free-resources/grammar/b1-b2/conditionals-zero-first-second)

   Access (точний запис master): Read editorial Grammar explanation through Second conditional; learner comments excluded.

   Перевірені положення (точні записи master):

   - Second Conditional: imagined present/future
   - usual past + would structure
   - were with singular persons and common spoken was
   - clause order

2. [Cambridge English Grammar Today — Conditionals: if](https://dictionary.cambridge.org/uk/grammar/british-grammar/conditionals-if)

   Access (точний запис master): Direct open returned403. Read search-provided editorial extract, not a claimed successful full-page open.

   Перевірені положення (точні записи master):

   - imagined present/future result
   - past form signals distance rather than past time
   - would/could/might in result
   - First/Second speaker perspective

3. [Cambridge English Grammar Today — If](https://dictionary.cambridge.org/grammar/british-grammar/if)

   Access (точний запис master): Direct open returned403. Read search-provided editorial If and politeness extract; learner comments not used.

   Перевірені положення (точні записи master):

   - if can mark real/imagined/uncertain conditions
   - polite if + would exists; no universal blacklist

Доступ через індексований текст не названо успішним повним відкриттям сторінки. Приклади й вправи Gramlyze написані самостійно або збережені з його попередніх definitions; видавничі завдання не копіювалися. Зовнішнього рецензента чи сертифікації немає.

## Межі цієї документаційної редакції

Навчальна редакція 1.0.1 уже містить виправлену назву imperative у примітці First Conditional зі збереженим прикладом «tell her». R1 її не змінює. Виправлено лише подання редакторських об’єктів, точність бібліографічних URL/access та відділення історичних draft-формулювань від фактичної навігації master. Нових навчальних тверджень, прикладів, відповідей, деталей, компонентів, hash bindings або записів у БД не додано.
