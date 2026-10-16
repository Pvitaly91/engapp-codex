# M61 авторська редакція 1.0.0

Заморожені навчальні дані для Backshift of Tenses, Time and Place Changes та Say / Tell / Ask. Це source artifact, а не підтвердження локального DB apply чи браузерного приймання.

18 власних завдань: по 2 form-select, 2 meaning-choice і 2 manual/token inputs. Шість canonical input answers і 13 aliases утворюють 19 accepted variants. Окремий конструктор містить п’ять тематичних питань на сторінку.

Пояснення практики від початку є plain text для чинного x-text. Підтриманий HTML у labels, prompts та навчальних прикладах збережено; renderer і grader не змінюються.

## backshift-of-tenses

schema_version: 1
status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechBackshiftTheorySeeder
slug: backshift-of-tenses
locale: uk
display_level: B1-B2
technical_block_level: B1
category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/backshift-of-tenses
subtitle_html: <p><strong>Backshift of Tenses</strong> — як змінити часову перспективу й зберегти зміст повідомлення.</p>
subtitle_text: Backshift of Tenses — як змінити часову перспективу й зберегти зміст повідомлення.
blocks[0].key: backshift-hero
blocks[0].existing_source_index: 0
blocks[0].type: hero
blocks[0].column: header
blocks[0].level: B1
blocks[0].data.level: B1-B2
blocks[0].data.intro: Backshift — типовий перехід часової форми в непрямій мові після минулого reporting verb. Він змінює перспективу переказу, а не дату самої події. Форму обираємо разом із контекстом та значенням.
blocks[0].data.rules[0].label: ТИПОВИЙ ЗСУВ
blocks[0].data.rules[0].color: emerald
blocks[0].data.rules[0].text: Present → past; past часто → past perfect; will → would.
blocks[0].data.rules[0].example: <span lang="en">We said that we were checking the cables.</span><br><span lang="uk">Ми сказали, що перевіряємо кабелі.</span>
blocks[0].data.rules[1].label: НЕ АВТОЗАМІНА
blocks[0].data.rules[1].color: blue
blocks[0].data.rules[1].text: Актуальне повідомлення може зберігати теперішню форму.
blocks[0].data.rules[1].example: <span lang="en">The guide said that the museum opens at nine.</span><br><span lang="uk">Екскурсовод сказав, що музей відчиняється о дев’ятій.</span>
blocks[0].data.rules[2].label: СЕНС НЕ ЗНИКАЄ
blocks[0].data.rules[2].color: amber
blocks[0].data.rules[2].text: Учасники, заперечення й характер дії залишаються тими самими.
blocks[0].data.rules[2].example: <span lang="en">Sofiia said that she had not finished the drawing.</span><br><span lang="uk">Софія сказала, що не завершила малюнок.</span>
blocks[1].key: backshift-table
blocks[1].existing_source_index: 1
blocks[1].type: comparison-table
blocks[1].column: left
blocks[1].level: null
blocks[1].data.title: 1. Типові переходи часів
blocks[1].data.intro: У кожному рядку та сама група пізніше переказує власні слова: we залишається we. Тут показано стандартний backshift після said, а не єдиний можливий переказ у будь-якому контексті.
blocks[1].data.headers[0]: Перехід форми
blocks[1].data.headers[1]: Прямі слова й переклад
blocks[1].data.headers[2]: Непряма мова й значення
blocks[1].data.rows[0].cells[0]: Present Simple → Past Simple
blocks[1].data.rows[0].cells[1]: <span lang="en">We repair bicycles.</span><br><span lang="uk">Ми ремонтуємо велосипеди.</span>
blocks[1].data.rows[0].cells[2]: <span lang="en">We said that we repaired bicycles.</span><br><span lang="uk">Ми сказали, що ремонтуємо велосипеди.</span> Звичну дію подано з минулої точки повідомлення.
blocks[1].data.rows[1].cells[0]: Present Continuous → Past Continuous
blocks[1].data.rows[1].cells[1]: <span lang="en">We are checking the cables.</span><br><span lang="uk">Ми перевіряємо кабелі.</span>
blocks[1].data.rows[1].cells[2]: <span lang="en">We said that we were checking the cables.</span><br><span lang="uk">Ми сказали, що перевіряємо кабелі.</span> Процес лишається процесом: were + -ing.
blocks[1].data.rows[2].cells[0]: Present Perfect → Past Perfect
blocks[1].data.rows[2].cells[1]: <span lang="en">We have packed the tools.</span><br><span lang="uk">Ми спакували інструменти.</span>
blocks[1].data.rows[2].cells[2]: <span lang="en">We said that we had packed the tools.</span><br><span lang="uk">Ми сказали, що спакували інструменти.</span> Результат був наявний до тодішньої репліки.
blocks[1].data.rows[3].cells[0]: Past Simple → Past Perfect
blocks[1].data.rows[3].cells[1]: <span lang="en">We missed the bus.</span><br><span lang="uk">Ми пропустили автобус.</span>
blocks[1].data.rows[3].cells[2]: <span lang="en">We said that we had missed the bus.</span><br><span lang="uk">Ми сказали, що пропустили автобус.</span> У заданому зсуві попередню подію позначає had + V3.
blocks[1].data.rows[4].cells[0]: Past Continuous → Past Perfect Continuous
blocks[1].data.rows[4].cells[1]: <span lang="en">We were waiting outside.</span><br><span lang="uk">Ми чекали надворі.</span>
blocks[1].data.rows[4].cells[2]: <span lang="en">We said that we had been waiting outside.</span><br><span lang="uk">Ми сказали, що до того чекали надворі.</span> Тривалість попереднього процесу не зникає.
blocks[1].data.rows[5].cells[0]: Present Perfect Continuous → Past Perfect Continuous
blocks[1].data.rows[5].cells[1]: <span lang="en">We have been painting the fence.</span><br><span lang="uk">Ми вже деякий час фарбуємо паркан.</span>
blocks[1].data.rows[5].cells[2]: <span lang="en">We said that we had been painting the fence.</span><br><span lang="uk">Ми сказали, що вже деякий час фарбуємо паркан.</span> Зберігаємо been + -ing.
blocks[1].data.rows[6].cells[0]: Past Perfect → Past Perfect
blocks[1].data.rows[6].cells[1]: <span lang="en">We had booked the room.</span><br><span lang="uk">Ми до того забронювали кімнату.</span>
blocks[1].data.rows[6].cells[2]: <span lang="en">We said that we had booked the room.</span><br><span lang="uk">Ми сказали, що до того забронювали кімнату.</span> Далі «назад» цю форму не зсуваємо.
blocks[1].data.rows[7].cells[0]: will → would
blocks[1].data.rows[7].cells[1]: <span lang="en">We will send the samples.</span><br><span lang="uk">Ми надішлемо зразки.</span>
blocks[1].data.rows[7].cells[2]: <span lang="en">We said that we would send the samples.</span><br><span lang="uk">Ми сказали, що надішлемо зразки.</span> Це майбутня дія відносно минулих слів, не обов’язково умовне «ми б».
blocks[1].data.outro: Одна форма had packed може походити і від have packed, і від packed. За непрямою мовою не завжди можна відновити єдину дослівну цитату.
blocks[2].key: backshift-use
blocks[2].existing_source_index: 2
blocks[2].type: usage-panels
blocks[2].column: left
blocks[2].level: null
blocks[2].data.title: 2. Обери часову перспективу, а не автозаміну
blocks[2].data.sections[0].label: МИНУЛЕ ПОВІДОМЛЕННЯ Й АКТУАЛЬНИЙ ФАКТ
blocks[2].data.sections[0].color: emerald
blocks[2].data.sections[0].description: Після said, told або explained зсув типовий. Але минула форма сама по собі не означає, що факт став неправдивим. Для актуального факту можна залишити теперішню форму; після says теперішній час теж природний.
blocks[2].data.sections[0].examples[0].en: The guide said that the museum opens at nine.
blocks[2].data.sections[0].examples[0].ua: Екскурсовод сказав, що музей відчиняється о дев’ятій.
blocks[2].data.sections[0].examples[1].en: The guide says that the museum opens at nine.
blocks[2].data.sections[0].examples[1].ua: Екскурсовод каже, що музей відчиняється о дев’ятій.
blocks[2].data.sections[0].note: Варіант said that the museum opened at nine теж можливий; лише opened не повідомляє, чи графік уже змінився. Навіть негайний переказ не забороняє backshift.
blocks[2].data.sections[1].label: МАЙБУТНЄ ЩЕ ПОПЕРЕДУ
blocks[2].data.sections[1].color: blue
blocks[2].data.sections[1].description: Якщо обіцяна подія ще не настала й повідомлення актуальне, після минулого said можливе will. Would дає звичайну перспективу «майбутнє з минулого».
blocks[2].data.sections[1].examples[0].en: On Monday, Iryna said that she will return on Friday.
blocks[2].data.sections[1].examples[0].ua: У понеділок Ірина сказала, що повернеться в п’ятницю.
blocks[2].data.sections[1].examples[1].en: On Monday, Iryna said that she would return on Friday.
blocks[2].data.sections[1].examples[1].ua: У понеділок Ірина сказала, що повернеться в п’ятницю.
blocks[2].data.sections[1].note: Оповідач говорить у вівторок того самого тижня; she — Ірина. Обидві версії стосуються тієї самої п’ятниці. Для вправи з однією формою має бути прямо задано стандартний зсув.
blocks[2].data.sections[2].label: МОДАЛЬНА ФОРМА ЗБЕРІГАЄ СЕНС
blocks[2].data.sections[2].color: amber
blocks[2].data.sections[2].description: Can як уміння або дозвіл часто переходить у could; значення визначає контекст. Must як обов’язок часто передаємо had to, але must як висновок може лишатися must. Заборона must not не означає відсутності потреби didn’t have to.
blocks[2].data.sections[2].examples[0].en: The technician said that she could fix the lock.
blocks[2].data.sections[2].examples[0].ua: Технікиня сказала, що може полагодити замок.
blocks[2].data.sections[2].examples[1].en: The organiser said that we could use the side door.
blocks[2].data.sections[2].examples[1].ua: Організатор сказав, що нам дозволено користуватися бічним входом.
blocks[2].data.sections[2].examples[2].en: The guard said that visitors must not touch the controls.
blocks[2].data.sections[2].examples[2].ua: Охоронець сказав, що відвідувачам не можна торкатися пульта.
blocks[2].data.sections[2].note: She — технікиня; could у першому прикладі передає її заявлене вміння, не доказ виконаного ремонту. У другому we — слухачі разом з оповідачем, яким організатор дав дозвіл. Порівняй didn’t have to touch — «не було потреби торкатися», а не «було заборонено».
blocks[2].data.sections[3].label: НЕ ЗСУВАЙ УЖЕ МОДАЛЬНУ АБО УМОВНУ ФОРМУ
blocks[2].data.sections[3].color: slate
blocks[2].data.sections[3].description: Should, would, could, might та ought to у багатьох звичайних переказах лишаються без зміни. Спершу перевір значення: форма минулого часу може позначати не минулу подію, а умову чи припущення.
blocks[2].data.sections[3].examples[0].en: Oleh said that we should wear gloves.
blocks[2].data.sections[3].examples[0].ua: Олег сказав, що нам слід надягати рукавички.
blocks[2].data.sections[3].note: We — оповідач і його колеги, яким адресовано пораду. Не додавай had лише тому, що перед повідомленням стоїть said; складні умовні моделі потребують окремого контексту.
blocks[3].key: backshift-mistakes
blocks[3].existing_source_index: 3
blocks[3].type: mistakes-grid
blocks[3].column: right
blocks[3].level: null
blocks[3].data.title: 3. Типові помилки: форма не повинна змінювати факт
blocks[3].data.items[0].label: ЗАДАНИЙ СТАНДАРТНИЙ ЗСУВ
blocks[3].data.items[0].color: rose
blocks[3].data.items[0].title: Виконай саме умову вправи.
blocks[3].data.items[0].wrong_en: Nina said that she will send the photos.
blocks[3].data.items[0].wrong_ua: Задум: Ніна сказала, що надішле фотографії; у вправі прямо задано will → would.
blocks[3].data.items[0].right_en: Nina said that she would send the photos.
blocks[3].data.items[0].right_ua: Ніна сказала, що надішле фотографії.
blocks[3].data.items[0].note: She — Ніна. Will не оголошуємо завжди неправильним після said: без такого обмеження актуальна майбутня подія може дозволяти will.
blocks[3].data.items[1].label: ЗБЕРЕЖИ ПРОЦЕС І ПАСИВ
blocks[3].data.items[1].color: amber
blocks[3].data.items[1].title: Не губи being або not.
blocks[3].data.items[1].wrong_en: The caretaker said that the gate was repaired.
blocks[3].data.items[1].wrong_ua: Задум: доглядач сказав, що ворота в той момент ремонтують.
blocks[3].data.items[1].right_en: The caretaker said that the gate was being repaired.
blocks[3].data.items[1].right_ua: Доглядач сказав, що ворота ремонтують.
blocks[3].data.items[1].note: Was repaired може бути граматичним, але не передає прямо заданий процес is being repaired. Backshift змінює часову перспективу, а не процес на завершену подію.
blocks[3].data.items[2].label: ЗАБОРОНА ≠ НЕОБОВ’ЯЗКОВІСТЬ
blocks[3].data.items[2].color: sky
blocks[3].data.items[2].title: Не перекладай must not як didn’t have to.
blocks[3].data.items[2].wrong_en: The guard said that we did not have to enter.
blocks[3].data.items[2].wrong_ua: Це: охоронець сказав, що нам не потрібно було входити; задум — заборонити вхід.
blocks[3].data.items[2].right_en: The guard said that we must not enter.
blocks[3].data.items[2].right_ua: Охоронець сказав, що нам не можна входити.
blocks[3].data.items[2].note: We — оповідач і його супутники. Обидві англійські моделі можливі, але мають різний зміст.
blocks[4].key: backshift-summary
blocks[4].existing_source_index: 4
blocks[4].type: summary-list
blocks[4].column: right
blocks[4].level: null
blocks[4].data.title: 4. Коротке правило
blocks[4].data.items[0]: Backshift подає повідомлення з минулої точки зору; календарна подія не пересувається на інший день.
blocks[4].data.items[1]: Зберігай учасників, заперечення, пасив і тривалість. Past Perfect і Past Perfect Continuous далі не зсуваємо; звичайний Past Simple теж може бути природним, якщо послідовність уже зрозуміла.
blocks[4].data.items[2]: Актуальність, загальні факти й майбутня подія дозволяють вибір перспективи. Минула форма не доводить, що факт більше не правдивий.
blocks[4].data.items[3]: Часова форма й tomorrow/here/this — різні рішення. Базову будову повідомлення дивись у <a href="/theory/reported-speech/reported-statements">Reported Statements</a>; зміну точки відліку — у <a href="/theory/reported-speech/time-and-place-changes">Time and Place Changes</a>.
blocks[5].key: backshift-navigation
blocks[5].existing_source_index: 5
blocks[5].type: navigation-chips
blocks[5].column: footer
blocks[5].level: null
blocks[5].data.title: Сторінки цього розділу
blocks[5].data.items[0].label: Reported Statements
blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
blocks[5].data.items[0].current: false
blocks[5].data.items[1].label: Reported Questions
blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
blocks[5].data.items[1].current: false
blocks[5].data.items[2].label: Reported Commands and Requests
blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
blocks[5].data.items[2].current: false
blocks[5].data.items[3].label: Backshift of Tenses
blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
blocks[5].data.items[3].current: true
blocks[5].data.items[4].label: Time and Place Changes
blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
blocks[5].data.items[4].current: false
blocks[5].data.items[5].label: Say / Tell / Ask
blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
blocks[5].data.items[5].current: false
blocks[6].key: backshift-practice
blocks[6].existing_source_index: null
blocks[6].type: practice-set
blocks[6].column: left
blocks[6].level: B1
blocks[6].data.title: Практика
blocks[6].data.select_title: Доповни задану модель
blocks[6].data.select_intro: Виконай саме зазначену часову або граматичну умову; збережи учасників і заперечення.
blocks[6].data.selects[0].id: m61-backshift-form-passive
blocks[6].data.selects[0].source_index: 1
blocks[6].data.selects[0].label: У понеділок Марта сказала колезі: «Вікна зараз миють» (The windows are being washed). У вівторок інша людина переказує ці слова. Задано стандартний зсув Present Continuous Passive → Past Continuous Passive; вікна ті самі.<br><span lang="en">Marta said that the windows ___ being washed.</span><br><span lang="uk">Марта сказала, що вікна миють.</span>
blocks[6].data.selects[0].options[0]: had
blocks[6].data.selects[0].options[1]: were
blocks[6].data.selects[0].options[2]: was
blocks[6].data.selects[0].answer: were
blocks[6].data.selects[0].accepted[0]: were
blocks[6].data.selects[0].feedback.had: Marta said that the windows were being washed.
  Марта сказала, що вікна миють. Had не утворює Past Continuous Passive; потрібне were being washed.
blocks[6].data.selects[0].feedback.were: Marta said that the windows were being washed.
  Марта сказала, що вікна миють. Were being washed зберігає процес і пасив.
blocks[6].data.selects[0].feedback.was: Marta said that the windows were being washed.
  Марта сказала, що вікна миють. Підмет windows у множині: were, не was.
blocks[6].data.selects[1].id: m61-backshift-form-perfect
blocks[6].data.selects[1].source_index: 2
blocks[6].data.selects[1].label: У понеділок Петро сказав колезі: «Я вже до того замкнув хвіртку» (I had already locked the gate). У вівторок колега переповідає: he — Петро, хвіртка та сама. Збережи Past Perfect.<br><span lang="en">Petro said that he ___ already locked the gate.</span><br><span lang="uk">Петро сказав, що вже замкнув хвіртку.</span>
blocks[6].data.selects[1].options[0]: has
blocks[6].data.selects[1].options[1]: would
blocks[6].data.selects[1].options[2]: had
blocks[6].data.selects[1].answer: had
blocks[6].data.selects[1].accepted[0]: had
blocks[6].data.selects[1].feedback.has: Petro said that he had already locked the gate.
  Петро сказав, що вже замкнув хвіртку. Has already locked не зберігає заданий Past Perfect.
blocks[6].data.selects[1].feedback.would: Petro said that he had already locked the gate.
  Петро сказав, що вже замкнув хвіртку. Would змінює часову будову й не сполучається тут із locked.
blocks[6].data.selects[1].feedback.had: Petro said that he had already locked the gate.
  Петро сказав, що вже замкнув хвіртку. Past Perfect лишається had + locked.
blocks[6].data.choice_title: Обери точний зміст
blocks[6].data.choice_intro: Зістав повідомлення з українським контекстом. Точний переклад англійського стимулу відкривається після перевірки.
blocks[6].data.choices[0].id: m61-backshift-meaning-current
blocks[6].data.choices[0].source_index: 3
blocks[6].data.choices[0].label: У понеділок працівниця рецепції сказала відвідувачу про звичайний графік офісу. У вівторок той відвідувач переказує це іншій людині. Що саме дозволяє виснувати форма opened — без додаткових фактів про графік?
blocks[6].data.choices[0].options[0]: Офіс тепер точно відчиняється в інший час.
blocks[6].data.choices[0].options[1]: Графік подано з минулої точки повідомлення; чи змінився він, речення не встановлює.
blocks[6].data.choices[0].options[2]: Офіс узагалі ніколи не відчинявся.
blocks[6].data.choices[0].answer: Графік подано з минулої точки повідомлення; чи змінився він, речення не встановлює.
blocks[6].data.choices[0].accepted[0]: Графік подано з минулої точки повідомлення; чи змінився він, речення не встановлює.
blocks[6].data.choices[0].feedback.офіс тепер точно відчиняється в інший час.: The receptionist said that the office opened at eight.
  Працівниця рецепції сказала, що офіс відчиняється о восьмій. Past Simple у переказі сам по собі не доводить зміни графіка.
blocks[6].data.choices[0].feedback.графік подано з минулої точки повідомлення; чи змінився він, речення не встановлює.: The receptionist said that the office opened at eight.
  Працівниця рецепції сказала, що офіс відчиняється о восьмій. Opened передає минулу перспективу, а не новину про скасований графік.
blocks[6].data.choices[0].feedback.офіс узагалі ніколи не відчинявся.: The receptionist said that the office opened at eight.
  Працівниця рецепції сказала, що офіс відчиняється о восьмій. Речення не містить заперечення й передає повідомлення про графік.
blocks[6].data.choices[0].prompt: <span lang="en">The receptionist said that the office opened at eight.</span>
blocks[6].data.choices[1].id: m61-backshift-meaning-prohibition
blocks[6].data.choices[1].source_index: 4
blocks[6].data.choices[1].label: Охоронець звернувся до відвідувачів у залі; пізніше один зі слухачів переказує його слова. Controls — той самий пульт. Обери зміст переданої вказівки, а не висновок про те, чи її виконали.
blocks[6].data.choices[1].options[0]: Відвідувачам не було потреби торкатися пульта, але це було дозволено.
blocks[6].data.choices[1].options[1]: Відвідувачам наказали обов’язково торкнутися пульта.
blocks[6].data.choices[1].options[2]: Відвідувачам заборонили торкатися пульта.
blocks[6].data.choices[1].answer: Відвідувачам заборонили торкатися пульта.
blocks[6].data.choices[1].accepted[0]: Відвідувачам заборонили торкатися пульта.
blocks[6].data.choices[1].feedback.відвідувачам не було потреби торкатися пульта, але це було дозволено.: The guard said that visitors must not touch the controls.
  Охоронець сказав, що відвідувачам не можна торкатися пульта. Відсутність потреби передавало б did not have to, не must not.
blocks[6].data.choices[1].feedback.відвідувачам наказали обов’язково торкнутися пульта.: The guard said that visitors must not touch the controls.
  Охоронець сказав, що відвідувачам не можна торкатися пульта. Not заперечує дозволеність дії, а не вимагає її.
blocks[6].data.choices[1].feedback.відвідувачам заборонили торкатися пульта.: The guard said that visitors must not touch the controls.
  Охоронець сказав, що відвідувачам не можна торкатися пульта. Must not передає заборону; чи її дотрималися, невідомо.
blocks[6].data.choices[1].prompt: <span lang="en">The guard said that visitors must not touch the controls.</span>
blocks[6].data.input_title: Побудуй повне речення
blocks[6].data.input_intro: Введи відповідь або використай усі токени. Умови окремо називають допустимі ручні варіанти.
blocks[6].data.inputs[0].id: m61-backshift-build-perfect
blocks[6].data.inputs[0].source_index: 5
blocks[6].data.inputs[0].before: drawing. / not / Sofiia / finished / that / she / the / said / had
blocks[6].data.inputs[0].after: У понеділок Софія сказала колезі: «Я ще не завершила малюнок» (I have not finished the drawing). У вівторок інша людина переказує це: she — Софія; drawing — той самий відомий малюнок. Почни Sofiia said, використай заданий стандартний зсув have not finished → had not finished і the drawing. That можна опустити; had not можна скоротити до hadn’t. Передай: «Софія сказала, що не завершила малюнок».
blocks[6].data.inputs[0].answer: Sofiia said that she had not finished the drawing.
blocks[6].data.inputs[0].accepted[0]: Sofiia said that she had not finished the drawing.
blocks[6].data.inputs[0].accepted[1]: Sofiia said she had not finished the drawing.
blocks[6].data.inputs[0].accepted[2]: Sofiia said that she hadn't finished the drawing.
blocks[6].data.inputs[0].accepted[3]: Sofiia said she hadn't finished the drawing.
blocks[6].data.inputs[0].feedback.sofiia said that she had not finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said that she had not finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she had not finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she had not finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said that she hadn't finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said that she hadn't finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said that she hadn’t finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said that she hadn’t finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she hadn't finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she hadn't finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she hadn’t finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia said she hadn’t finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Had not finished зберігає заперечений результат до понеділкових слів; she — Софія.
blocks[6].data.inputs[0].feedback.sofiia did not say that she had finished the drawing.: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Did not say заперечує сам факт повідомлення, а не завершеність малюнка.
blocks[6].data.inputs[0].feedback.sofiia did not say that she had finished the drawing: Sofiia said that she had not finished the drawing.
  Софія сказала, що не завершила малюнок. Did not say заперечує сам факт повідомлення, а не завершеність малюнка.
blocks[6].data.inputs[1].id: m61-backshift-build-should
blocks[6].data.inputs[1].source_index: 6
blocks[6].data.inputs[1].before: receipt. / should / Danylo / we / the / that / keep / said
blocks[6].data.inputs[1].after: У понеділок Данило порадив своїй команді: «Нам слід зберегти чек» (We should keep the receipt). У вівторок інший учасник тієї самої команди переказує пораду: we — команда разом із Данилом та оповідачем; чек відомий усім. Почни Danylo said і збережи should keep the receipt. That можна опустити. Передай: «Данило сказав, що нам слід зберегти чек».
blocks[6].data.inputs[1].answer: Danylo said that we should keep the receipt.
blocks[6].data.inputs[1].accepted[0]: Danylo said that we should keep the receipt.
blocks[6].data.inputs[1].accepted[1]: Danylo said we should keep the receipt.
blocks[6].data.inputs[1].feedback.danylo said that we should keep the receipt.: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Should у цій пораді лишається should; речення не стверджує, що чек уже зберегли.
blocks[6].data.inputs[1].feedback.danylo said that we should keep the receipt: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Should у цій пораді лишається should; речення не стверджує, що чек уже зберегли.
blocks[6].data.inputs[1].feedback.danylo said we should keep the receipt.: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Should у цій пораді лишається should; речення не стверджує, що чек уже зберегли.
blocks[6].data.inputs[1].feedback.danylo said we should keep the receipt: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Should у цій пораді лишається should; речення не стверджує, що чек уже зберегли.
blocks[6].data.inputs[1].feedback.danylo said that we had kept the receipt.: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Had kept повідомляє про попередній результат, а не передає пораду.
blocks[6].data.inputs[1].feedback.danylo said that we had kept the receipt: Danylo said that we should keep the receipt.
  Данило сказав, що нам слід зберегти чек. Had kept повідомляє про попередній результат, а не передає пораду.
blocks[6].data.rephrase: []
blocks[6].data.linked_practice.source: theory_links
blocks[6].data.linked_practice.question_types[0]: 4
blocks[6].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechBackshiftOfTensesAllLevelsLessonSeeder
blocks[6].data.linked_practice.title: Вправа 4. Побудуй речення
blocks[6].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова.
blocks[6].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
details[0].key: backshift-modal-meaning
details[0].title: Модальні дієслова: спочатку значення
details[0].owner_block_key: backshift-use
details[0].owner_pointer: /sections/2
details[0].fragment.type: usage-panels
details[0].fragment.data.sections[0].label: MAY: МОЖЛИВІСТЬ АБО ДОЗВІЛ
details[0].fragment.data.sections[0].color: blue
details[0].fragment.data.sections[0].description: May може називати ймовірність або дозвіл. У типовому переказі можливість часто передає might; дозвіл можна передати could. Це не взаємозамінні відповіді в будь-якому реченні.
details[0].fragment.data.sections[0].examples[0].en: The organiser said that the talk might finish late.
details[0].fragment.data.sections[0].examples[0].ua: Організатор сказав, що виступ, можливо, закінчиться пізно.
details[0].fragment.data.sections[0].examples[1].en: The organiser said that we could leave early.
details[0].fragment.data.sections[0].examples[1].ua: Організатор сказав, що нам дозволено піти раніше.
details[0].fragment.data.sections[0].note: У другому прикладі we — присутні слухачі, серед яких оповідач; ідеться про дозвіл, не про їхню фізичну здатність.
details[0].fragment.data.sections[1].label: MUST: ВИМОГА АБО ВИСНОВОК
details[0].fragment.data.sections[1].color: amber
details[0].fragment.data.sections[1].description: Порівняй обов’язок і логічний висновок. Для минулої вимоги had to природне; у висновку must може зберігатися. Час висновку й час події визначає контекст.
details[0].fragment.data.sections[1].examples[0].en: The nurse said that I had to wait outside.
details[0].fragment.data.sections[1].examples[0].ua: Медсестра сказала, що мені тоді потрібно було зачекати надворі.
details[0].fragment.data.sections[1].examples[1].en: The nurse said that the patient must be asleep.
details[0].fragment.data.sections[1].examples[1].ua: Медсестра сказала, що пацієнт, мабуть, спить.
details[0].fragment.data.sections[1].note: I — пацієнт, якому адресовано вимогу, а в другому реченні оповідач переказує припущення медсестри про іншого пацієнта. Не підставляй had to автоматично в цьому переказі висновку: must тут передає логічне виведення, не припис пацієнтові.

## time-and-place-changes

schema_version: 1
status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechTimePlaceChangesTheorySeeder
slug: time-and-place-changes
locale: uk
display_level: B1-B2
technical_block_level: B1
category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/time-and-place-changes
subtitle_html: <p><strong>Time and Place Changes</strong> — як зберегти день, місце й предмет у непрямій мові.</p>
subtitle_text: Time and Place Changes — як зберегти день, місце й предмет у непрямій мові.
blocks[0].key: time-place-hero
blocks[0].existing_source_index: 0
blocks[0].type: hero
blocks[0].column: header
blocks[0].level: B1
blocks[0].data.level: B1-B2
blocks[0].data.intro: Time and Place Changes допомагає зберегти ту саму подію, коли змінюється точка відліку оповідача. Today, here та this не замінюємо автоматично: перевіряємо календар, місце й конкретний предмет.
blocks[0].data.rules[0].label: СПЕРШУ КАЛЕНДАР
blocks[0].data.rules[0].color: emerald
blocks[0].data.rules[0].text: Tomorrow первинного мовця може стати today для наступного дня.
blocks[0].data.rules[0].example: <span lang="en">Olena said that the delivery would arrive today.</span><br><span lang="uk">Олена сказала, що доставку привезуть сьогодні.</span>
blocks[0].data.rules[1].label: ТОЧНЕ МІСЦЕ
blocks[0].data.rules[1].color: blue
blocks[0].data.rules[1].text: Here може зберігатися, якщо оповідач говорить із того самого місця.
blocks[0].data.rules[1].example: <span lang="en">Ihor said that the meeting was here today.</span><br><span lang="uk">Ігор сказав, що зустріч тут сьогодні.</span>
blocks[0].data.rules[2].label: КОНКРЕТНИЙ ПРЕДМЕТ
blocks[0].data.rules[2].color: amber
blocks[0].data.rules[2].text: This/that залежить від того, що й звідки зараз показують.
blocks[0].data.rules[2].example: <span lang="en">Marta said that this key was hers.</span><br><span lang="uk">Марта сказала, що цей ключ її.</span>
blocks[1].key: time-place-table
blocks[1].existing_source_index: 1
blocks[1].type: comparison-table
blocks[1].column: left
blocks[1].level: null
blocks[1].data.title: 1. Типові відповідники — не обов’язкові заміни
blocks[1].data.intro: Таблиця допомагає переказати слова з іншої точки відліку. Спершу встанови, який саме день, місце чи предмет названо; лише потім обери слово.
blocks[1].data.headers[0]: Первинне слово та значення
blocks[1].data.headers[1]: Варіант зі зміненою точкою відліку
blocks[1].data.headers[2]: Що перевірити
blocks[1].data.rows[0].cells[0]: now — зараз
blocks[1].data.rows[0].cells[1]: then / at that moment — тоді / у той момент
blocks[1].data.rows[0].cells[2]: Поточний момент мовця вже став минулим моментом оповіді.
blocks[1].data.rows[1].cells[0]: today / tonight — сьогодні / сьогодні ввечері або вночі
blocks[1].data.rows[1].cells[1]: that day / that night — того дня / того вечора або тієї ночі
blocks[1].data.rows[1].cells[2]: За зміненого відліку; якщо оповідач орієнтується на свій теперішній день, вечір чи ніч, today / tonight можуть зберігатися.
blocks[1].data.rows[2].cells[0]: tomorrow — завтра
blocks[1].data.rows[2].cells[1]: the next / following day — наступного дня
blocks[1].data.rows[2].cells[2]: Відлік від дня первинної репліки, не автоматично від сьогоднішнього дня оповідача.
blocks[1].data.rows[3].cells[0]: yesterday — учора
blocks[1].data.rows[3].cells[1]: the day before / the previous day — попереднього дня
blocks[1].data.rows[3].cells[2]: Потрібна зрозуміла попередня точка відліку. Точна дата може бути яснішою.
blocks[1].data.rows[4].cells[0]: two days ago — два дні тому
blocks[1].data.rows[4].cells[1]: two days earlier / before — двома днями раніше / до того
blocks[1].data.rows[4].cells[2]: Зберігай той самий інтервал і вихідну дату.
blocks[1].data.rows[5].cells[0]: in two days — через два дні
blocks[1].data.rows[5].cells[1]: two days later — через два дні після того
blocks[1].data.rows[5].cells[2]: Понеділок 1 червня 2026 + два дні = середа 3 червня. Навіть у п’ятничному переказі two days later від понеділка лишається 3 червня, не 7 червня.
blocks[1].data.rows[6].cells[0]: last week / next week — минулого / наступного тижня
blocks[1].data.rows[6].cells[1]: the previous week / the following week — попереднього / наступного тижня
blocks[1].data.rows[6].cells[2]: Тиждень рахуємо від первинної ситуації; календарні дати усувають двозначність.
blocks[1].data.rows[7].cells[0]: here — тут
blocks[1].data.rows[7].cells[1]: there — там
blocks[1].data.rows[7].cells[2]: Має значення фактичне місце переказу. Якщо воно те саме, here може залишитися.
blocks[1].data.rows[8].cells[0]: this / these — цей, ця, це / ці
blocks[1].data.rows[8].cells[1]: that / those — той, та, те / ті
blocks[1].data.rows[8].cells[2]: Залежить від конкретного предмета й жесту мовця. Іноді природніше the + назва або it / them.
blocks[2].key: time-place-context
blocks[2].existing_source_index: 2
blocks[2].type: usage-panels
blocks[2].column: left
blocks[2].level: null
blocks[2].data.title: 2. Збережи ту саму дату, місце й предмет
blocks[2].data.sections[0].label: НАСТУПНИЙ ДЕНЬ МОЖЕ СТАТИ СЬОГОДНІ
blocks[2].data.sections[0].color: emerald
blocks[2].data.sections[0].description: Навчальний календар: у понеділок 1 червня 2026 року Олена каже колезі про доставку. У вівторок 2 червня той колега переказує її слова; she — Олена. Її tomorrow уже відповідає його today.
blocks[2].data.sections[0].examples[0].en: Olena said, “The delivery will arrive tomorrow.”
blocks[2].data.sections[0].examples[0].ua: Олена сказала: «Доставку привезуть завтра».
blocks[2].data.sections[0].examples[1].en: Olena said that the delivery would arrive today.
blocks[2].data.sections[0].examples[1].ua: Олена сказала, що доставку привезуть сьогодні.
blocks[2].data.sections[0].note: Обидві репліки стосуються 2 червня. On Tuesday — «у вівторок» — теж може прямо назвати цей день. The next day зрозуміле лише з правильною прив’язкою до понеділкових слів, а не як нова дата 3 червня.
blocks[2].data.sections[1].label: ПІЗНІШИЙ ПЕРЕКАЗ: НАЗВИ ДАТУ
blocks[2].data.sections[1].color: blue
blocks[2].data.sections[1].description: П’ятниця 5 червня: колега переказує ті самі понеділкові слова Олени. Today уже означало б 5 червня, тому точна назва дня ясніша.
blocks[2].data.sections[1].examples[0].en: Olena said that the delivery would arrive on Tuesday, 2 June.
blocks[2].data.sections[1].examples[0].ua: Олена сказала, що доставку привезуть у вівторок, 2 червня.
blocks[2].data.sections[1].note: Would передає майбутню подію з понеділкової перспективи. Сам переказ обіцянки не підтверджує, що доставка справді прибула. Зміна часу дієслова й вибір дати — окремі рішення.
blocks[2].data.sections[2].label: ТЕ САМЕ МІСЦЕ АБО ТОЙ САМИЙ ПРЕДМЕТ
blocks[2].data.sections[2].color: amber
blocks[2].data.sections[2].description: Переказ того самого дня в тій самій кімнаті може зберігати today і here. This теж може залишитися, якщо оповідач тримає й показує той самий предмет, навіть уже в іншому місці.
blocks[2].data.sections[2].examples[0].en: Ihor said that the meeting was here today.
blocks[2].data.sections[2].examples[0].ua: Ігор сказав, що зустріч тут сьогодні.
blocks[2].data.sections[2].examples[1].en: Ihor said that this folder was his.
blocks[2].data.sections[2].examples[1].ua: Ігор сказав, що ця папка його.
blocks[2].data.sections[2].note: Перший приклад: оповідач досі в кімнаті зустрічі того самого дня. Другий: він пізніше переніс Ігореву папку додому й зараз показує саме її; his — Ігорів. Зміна кімнати не змушує замінити this на that.
blocks[2].data.sections[3].label: ДВА THAT МАЮТЬ РІЗНУ РОБОТУ
blocks[2].data.sections[3].color: slate
blocks[2].data.sections[3].description: That перед підрядною частиною означає «що». Друге that перед назвою предмета — «той / та / те». Вони можуть стояти поряд без помилки.
blocks[2].data.sections[3].examples[0].en: Taras said that that shop was closed.
blocks[2].data.sections[3].examples[0].ua: Тарас сказав, що той магазин зачинений.
blocks[2].data.sections[3].note: Перше that часто можна опустити: Taras said that shop was closed — «Тарас сказав, що той магазин зачинений». Друге обираємо за конкретним магазином і перспективою, а не видаляємо як нібито повтор.
blocks[3].key: time-place-mistakes
blocks[3].existing_source_index: 3
blocks[3].type: mistakes-grid
blocks[3].column: right
blocks[3].level: null
blocks[3].data.title: 3. Типові помилки з точкою відліку
blocks[3].data.items[0].label: НЕ ПЕРЕСУНЬ ПОДІЮ НА ЩЕ ОДИН ДЕНЬ
blocks[3].data.items[0].color: rose
blocks[3].data.items[0].title: Tomorrow первинного мовця — не завжди tomorrow оповідача.
blocks[3].data.items[0].wrong_en: Olena said that the delivery would arrive tomorrow.
blocks[3].data.items[0].wrong_ua: Це вівторкове tomorrow означає середу, 3 червня; задум — доставку 2 червня.
blocks[3].data.items[0].right_en: Olena said that the delivery would arrive today.
blocks[3].data.items[0].right_ua: Олена сказала, що доставку привезуть сьогодні.
blocks[3].data.items[0].note: У цьому навчальному контексті пряма репліка була 1 червня, переказ — 2 червня. Не виправляй дату механічно на «наступного дня» без ясного відліку.
blocks[3].data.items[1].label: МІСЦЕ НЕ ЗМІНИЛОСЯ
blocks[3].data.items[1].color: amber
blocks[3].data.items[1].title: Минулий said не вимагає there.
blocks[3].data.items[1].wrong_en: Ihor said that the meeting was there today.
blocks[3].data.items[1].wrong_ua: Це: Ігор сказав, що зустріч там сьогодні; задум — тут, у кімнаті самого оповідача.
blocks[3].data.items[1].right_en: Ihor said that the meeting was here today.
blocks[3].data.items[1].right_ua: Ігор сказав, що зустріч тут сьогодні.
blocks[3].data.items[1].note: There можливе з іншою просторовою перспективою, але в заданій ситуації оповідач прямо вказує на свою теперішню кімнату словом «тут».
blocks[3].data.items[2].label: НЕ ВИДАЛЯЙ ВКАЗІВНЕ THAT
blocks[3].data.items[2].color: sky
blocks[3].data.items[2].title: Збережи і «що», і «той магазин», якщо цього потребує зміст.
blocks[3].data.items[2].wrong_en: Taras said that shop was closed.
blocks[3].data.items[2].wrong_ua: Задум: зберегти обидва окремі слова — «що» та «той магазин».
blocks[3].data.items[2].right_en: Taras said that that shop was closed.
blocks[3].data.items[2].right_ua: Тарас сказав, що той магазин зачинений.
blocks[3].data.items[2].note: Перша версія сама по собі граматична: у ній опущено сполучник that. Вона не відповідає лише явно заданій умові зберегти обидва слова; повтор у другій версії не помилка.
blocks[4].key: time-place-summary
blocks[4].existing_source_index: 4
blocks[4].type: summary-list
blocks[4].column: right
blocks[4].level: null
blocks[4].data.title: 4. Коротке правило
blocks[4].data.items[0]: Зафіксуй день репліки, день переказу, місце й конкретний предмет. Не перенось подію разом зі словом tomorrow.
blocks[4].data.items[1]: Того самого дня today може лишитися today; у тому самому місці here може лишитися here. Для пізнішого переказу точна дата іноді найясніша.
blocks[4].data.items[2]: Зберігай інтервал у two days ago / two days earlier. This/that описує перспективу на предмет, а that — «що» — може бути окремим сполучником.
blocks[4].data.items[3]: Будову повідомлення дивись у <a href="/theory/reported-speech/reported-statements">Reported Statements</a>, часові форми — у <a href="/theory/reported-speech/backshift-of-tenses">Backshift of Tenses</a>. Це пов’язані, але не тотожні рішення.
blocks[5].key: time-place-navigation
blocks[5].existing_source_index: 5
blocks[5].type: navigation-chips
blocks[5].column: footer
blocks[5].level: null
blocks[5].data.title: Сторінки цього розділу
blocks[5].data.items[0].label: Reported Statements
blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
blocks[5].data.items[0].current: false
blocks[5].data.items[1].label: Reported Questions
blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
blocks[5].data.items[1].current: false
blocks[5].data.items[2].label: Reported Commands and Requests
blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
blocks[5].data.items[2].current: false
blocks[5].data.items[3].label: Backshift of Tenses
blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
blocks[5].data.items[3].current: false
blocks[5].data.items[4].label: Time and Place Changes
blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
blocks[5].data.items[4].current: true
blocks[5].data.items[5].label: Say / Tell / Ask
blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
blocks[5].data.items[5].current: false
blocks[6].key: time-place-practice
blocks[6].existing_source_index: null
blocks[6].type: practice-set
blocks[6].column: left
blocks[6].level: B1
blocks[6].data.title: Практика
blocks[6].data.select_title: Доповни задану модель
blocks[6].data.select_intro: Виконай саме зазначену часову або граматичну умову; збережи учасників і заперечення.
blocks[6].data.selects[0].id: m61-time-place-form-today
blocks[6].data.selects[0].source_index: 1
blocks[6].data.selects[0].label: Навчальний календар: у понеділок 1 червня 2026 року Назар сказав колезі: «Посилка прибуде завтра» (The parcel will arrive tomorrow). У вівторок 2 червня колега переказує це іншій людині. Доповни словом, що означає саме «сьогодні» для вівторкового оповідача; потрібна дата — 2 червня.<br><span lang="en">Nazar said that the parcel would arrive ___.</span><br><span lang="uk">Назар сказав, що посилка прибуде сьогодні.</span>
blocks[6].data.selects[0].options[0]: yesterday
blocks[6].data.selects[0].options[1]: tomorrow
blocks[6].data.selects[0].options[2]: today
blocks[6].data.selects[0].answer: today
blocks[6].data.selects[0].accepted[0]: today
blocks[6].data.selects[0].feedback.yesterday: Nazar said that the parcel would arrive today.
  Назар сказав, що посилка прибуде сьогодні. Yesterday у вівторок називає 1 червня, не 2 червня.
blocks[6].data.selects[0].feedback.tomorrow: Nazar said that the parcel would arrive today.
  Назар сказав, що посилка прибуде сьогодні. Tomorrow для вівторкового оповідача означає 3 червня й зсуває подію.
blocks[6].data.selects[0].feedback.today: Nazar said that the parcel would arrive today.
  Назар сказав, що посилка прибуде сьогодні. Today у вівторковому переказі зберігає дату понеділкового tomorrow — 2 червня.
blocks[6].data.selects[1].id: m61-time-place-form-this
blocks[6].data.selects[1].source_index: 2
blocks[6].data.selects[1].label: У понеділок Марта показала колезі конкретний ключ у майстерні й сказала: «Цей ключ мій» (This key is mine). У вівторок колега вдома тримає той самий ключ перед собою й показує його співрозмовнику. Hers — Мартин. Доповни саме значенням «цей ключ, який я зараз показую».<br><span lang="en">Marta said that ___ key was hers.</span><br><span lang="uk">Марта сказала, що цей ключ її.</span>
blocks[6].data.selects[1].options[0]: those
blocks[6].data.selects[1].options[1]: this
blocks[6].data.selects[1].options[2]: there
blocks[6].data.selects[1].answer: this
blocks[6].data.selects[1].accepted[0]: this
blocks[6].data.selects[1].feedback.those: Marta said that this key was hers.
  Марта сказала, що цей ключ її. Those вказує на множину й не поєднується тут з одниною key.
blocks[6].data.selects[1].feedback.this: Marta said that this key was hers.
  Марта сказала, що цей ключ її. Оповідач показує той самий ключ перед собою; інше місце не забороняє this.
blocks[6].data.selects[1].feedback.there: Marta said that this key was hers.
  Марта сказала, що цей ключ її. There означає «там», а не вказівне «цей» перед key.
blocks[6].data.choice_title: Обери точний зміст
blocks[6].data.choice_intro: Зістав повідомлення з українським контекстом. Точний переклад англійського стимулу відкривається після перевірки.
blocks[6].data.choices[0].id: m61-time-place-meaning-earlier
blocks[6].data.choices[0].source_index: 3
blocks[6].data.choices[0].label: Навчальний календар: у понеділок 1 червня 2026 року Ірина сказала колезі: «Я відправила листа два дні тому». У п’ятницю 5 червня колега переказує ці понеділкові слова; she — Ірина, лист той самий. Earlier відраховано від понеділкової репліки. Яку дату відправлення потрібно зберегти?
blocks[6].data.choices[0].options[0]: Суботу, 30 травня.
blocks[6].data.choices[0].options[1]: Середу, 3 червня.
blocks[6].data.choices[0].options[2]: П’ятницю, 5 червня.
blocks[6].data.choices[0].answer: Суботу, 30 травня.
blocks[6].data.choices[0].accepted[0]: Суботу, 30 травня.
blocks[6].data.choices[0].feedback.суботу, 30 травня.: Iryna said that she had posted the letter two days earlier.
  Ірина сказала, що відправила листа двома днями раніше. Два дні до понеділка 1 червня — субота 30 травня.
blocks[6].data.choices[0].feedback.середу, 3 червня.: Iryna said that she had posted the letter two days earlier.
  Ірина сказала, що відправила листа двома днями раніше. 3 червня — за два дні до п’ятничного переказу, але відлік тут від первинного понеділка.
blocks[6].data.choices[0].feedback.п’ятницю, 5 червня.: Iryna said that she had posted the letter two days earlier.
  Ірина сказала, що відправила листа двома днями раніше. Дата самого переказу не стає датою відправлення.
blocks[6].data.choices[0].prompt: <span lang="en">Iryna said that she had posted the letter two days earlier.</span>
blocks[6].data.choices[1].id: m61-time-place-meaning-two-days
blocks[6].data.choices[1].source_index: 4
blocks[6].data.choices[1].label: Данило говорить з Оленою в понеділок 1 червня 2026 року; Олена того самого дня переказує його слова іншій людині. He — Данило. In two days рахуємо від цього понеділка. Яку дату повернення називають слова Данила, не доводячи, що він справді повернеться?
blocks[6].data.choices[1].options[0]: Вівторок, 2 червня.
blocks[6].data.choices[1].options[1]: Середу, 3 червня.
blocks[6].data.choices[1].options[2]: П’ятницю, 5 червня.
blocks[6].data.choices[1].answer: Середу, 3 червня.
blocks[6].data.choices[1].accepted[0]: Середу, 3 червня.
blocks[6].data.choices[1].feedback.вівторок, 2 червня.: Danylo said that he would return in two days.
  Данило сказав, що повернеться через два дні. 2 червня — лише через один день після понеділка.
blocks[6].data.choices[1].feedback.середу, 3 червня.: Danylo said that he would return in two days.
  Данило сказав, що повернеться через два дні. Від 1 червня додаємо два дні: 3 червня. Той самий день переказу дозволяє зберегти in two days.
blocks[6].data.choices[1].feedback.п’ятницю, 5 червня.: Danylo said that he would return in two days.
  Данило сказав, що повернеться через два дні. Речення не відраховує ще два дні від іншого неназваного дня.
blocks[6].data.choices[1].prompt: <span lang="en">Danylo said that he would return in two days.</span>
blocks[6].data.input_title: Побудуй повне речення
blocks[6].data.input_intro: Введи відповідь або використай усі токени. Умови окремо називають допустимі ручні варіанти.
blocks[6].data.inputs[0].id: m61-time-place-build-before
blocks[6].data.inputs[0].source_index: 5
blocks[6].data.inputs[0].before: before. / she / parcel / Iryna / day / the / had / that / received / the / said
blocks[6].data.inputs[0].after: Навчальний календар: у понеділок 1 червня 2026 року Ірина сказала колезі: «Я отримала посилку вчора» (I received the parcel yesterday). У п’ятницю 5 червня інша людина переказує понеділкові слова; she — Ірина, parcel — та сама відома посилка. Отримання було в неділю 31 травня. Почни Iryna said, використай задане had received the parcel, а наприкінці — the day before або the previous day, прив’язані до понеділкових слів. That можна опустити; she had можна скоротити до she’d, де ’d означає had. Передай: «Ірина сказала, що отримала посилку попереднього дня».
blocks[6].data.inputs[0].answer: Iryna said that she had received the parcel the day before.
blocks[6].data.inputs[0].accepted[0]: Iryna said that she had received the parcel the day before.
blocks[6].data.inputs[0].accepted[1]: Iryna said she had received the parcel the day before.
blocks[6].data.inputs[0].accepted[2]: Iryna said that she had received the parcel the previous day.
blocks[6].data.inputs[0].accepted[3]: Iryna said she had received the parcel the previous day.
blocks[6].data.inputs[0].accepted[4]: Iryna said that she'd received the parcel the day before.
blocks[6].data.inputs[0].accepted[5]: Iryna said she'd received the parcel the day before.
blocks[6].data.inputs[0].accepted[6]: Iryna said that she'd received the parcel the previous day.
blocks[6].data.inputs[0].accepted[7]: Iryna said she'd received the parcel the previous day.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she had received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she had received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she had received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she had received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she'd received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she'd received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she’d received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she’d received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she'd received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she'd received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she’d received the parcel the day before.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she’d received the parcel the day before: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she'd received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she'd received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she’d received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she’d received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she'd received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she'd received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she’d received the parcel the previous day.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said she’d received the parcel the previous day: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. The day before / the previous day відраховуємо від понеділка 1 червня: це неділя 31 травня, а не день перед п’ятничним переказом. She’d received тут означає she had received.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel yesterday.: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. Без окремої прив’язки yesterday у п’ятницю означало б четвер 4 червня, не потрібну неділю.
blocks[6].data.inputs[0].feedback.iryna said that she had received the parcel yesterday: Iryna said that she had received the parcel the day before.
  Ірина сказала, що отримала посилку попереднього дня. Без окремої прив’язки yesterday у п’ятницю означало б четвер 4 червня, не потрібну неділю.
blocks[6].data.inputs[1].id: m61-time-place-build-that-that
blocks[6].data.inputs[1].source_index: 6
blocks[6].data.inputs[1].before: day. / that / open / Taras / shop / the / that / said / next / would
blocks[6].data.inputs[1].after: Навчальний календар: у понеділок 1 червня 2026 року Тарас, показуючи на магазин через дорогу, сказав колезі: «Той магазин відкриється завтра» (That shop will open tomorrow). У п’ятницю 5 червня колега з дому переповідає ті слова про той самий магазин і ту саму дату — 2 червня. Почни Taras said; у цій вправі збережи ОБИДВА that: сполучник «що» та вказівне «той». Використай would open; обставину the next day або the following day постав наприкінці й відраховуй від понеділка. Передай: «Тарас сказав, що той магазин відкриється наступного дня».
blocks[6].data.inputs[1].answer: Taras said that that shop would open the next day.
blocks[6].data.inputs[1].accepted[0]: Taras said that that shop would open the next day.
blocks[6].data.inputs[1].accepted[1]: Taras said that that shop would open the following day.
blocks[6].data.inputs[1].feedback.taras said that that shop would open the next day.: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Перше that — «що», друге — «той». The next/following day тут означає 2 червня; повідомлення саме по собі не доводить відкриття магазину.
blocks[6].data.inputs[1].feedback.taras said that that shop would open the next day: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Перше that — «що», друге — «той». The next/following day тут означає 2 червня; повідомлення саме по собі не доводить відкриття магазину.
blocks[6].data.inputs[1].feedback.taras said that that shop would open the following day.: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Перше that — «що», друге — «той». The next/following day тут означає 2 червня; повідомлення саме по собі не доводить відкриття магазину.
blocks[6].data.inputs[1].feedback.taras said that that shop would open the following day: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Перше that — «що», друге — «той». The next/following day тут означає 2 червня; повідомлення саме по собі не доводить відкриття магазину.
blocks[6].data.inputs[1].feedback.taras said that shop would open the next day.: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Варіант з одним that граматичний, але не виконує явної умови зберегти і сполучник, і вказівне слово.
blocks[6].data.inputs[1].feedback.taras said that shop would open the next day: Taras said that that shop would open the next day.
  Тарас сказав, що той магазин відкриється наступного дня. Варіант з одним that граматичний, але не виконує явної умови зберегти і сполучник, і вказівне слово.
blocks[6].data.rephrase: []
blocks[6].data.linked_practice.source: theory_links
blocks[6].data.linked_practice.question_types[0]: 4
blocks[6].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechTimePlaceChangesAllLevelsLessonSeeder
blocks[6].data.linked_practice.title: Вправа 4. Побудуй речення
blocks[6].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова.
blocks[6].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
details[0].key: time-place-motion-perspective
details[0].title: Come/go і bring/take: звідки дивиться мовець
details[0].owner_block_key: time-place-context
details[0].owner_pointer: /sections/2
details[0].fragment.type: usage-panels
details[0].fragment.data.sections[0].label: НЕ МЕХАНІЧНА ЗАМІНА ДІЄСЛОВА
details[0].fragment.data.sections[0].color: blue
details[0].fragment.data.sections[0].description: Come часто позначає рух до місця мовця або співрозмовника; go — до іншого місця. Bring і take теж залежать від напряму щодо обраної точки зору. Спочатку назви реальний пункт призначення.
details[0].fragment.data.sections[0].examples[0].en: Olena said, “I will come to your office.”
details[0].fragment.data.sections[0].examples[0].ua: Олена сказала: «Я прийду до твого офісу».
details[0].fragment.data.sections[0].examples[1].en: Olena said that she would go to Ihor’s office.
details[0].fragment.data.sections[0].examples[1].ua: Олена сказала, що піде до Ігоревого офісу.
details[0].fragment.data.sections[0].note: Олена говорила телефоном з Ігорем у його офісі. Інший оповідач пізніше говорить із дому: she — Олена, первинне your office — Ігорів офіс. Якщо сам Ігор досі в офісі, come може зберігатися. Не замінюй come на go лише через said.

## say-tell-ask

schema_version: 1
status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechSayTellAskTheorySeeder
slug: say-tell-ask
locale: uk
display_level: B1-B2
technical_block_level: B1
category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/say-tell-ask
subtitle_html: <p><strong>Say / Tell / Ask</strong> — як обрати дієслово за наміром, адресатом і виконавцем дії.</p>
subtitle_text: Say / Tell / Ask — як обрати дієслово за наміром, адресатом і виконавцем дії.
blocks[0].key: say-tell-ask-hero
blocks[0].existing_source_index: 0
blocks[0].type: hero
blocks[0].column: header
blocks[0].level: B1
blocks[0].data.level: B1-B2
blocks[0].data.intro: Say, tell та ask не є взаємозамінними позначками «сказати». Спочатку визнач, чи людина повідомляє зміст, шукає відповідь, просить річ або просить виконати дію; потім обери модель з потрібними учасниками.
blocks[0].data.rules[0].label: SAY — ЩО СКАЗАНО
blocks[0].data.rules[0].color: emerald
blocks[0].data.rules[0].text: Адресат після say вводиться через to.
blocks[0].data.rules[0].example: <span lang="en">Lina said to us that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span>
blocks[0].data.rules[1].label: TELL — КОМУ ПОВІДОМЛЕНО
blocks[0].data.rules[1].color: blue
blocks[0].data.rules[1].text: У моделі tell someone that… адресат стоїть без to.
blocks[0].data.rules[1].example: <span lang="en">Lina told us that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span>
blocks[0].data.rules[2].label: ASK — ЗАПИТ АБО ПРОХАННЯ
blocks[0].data.rules[2].color: amber
blocks[0].data.rules[2].text: Модель показує, чи потрібна відповідь, річ або дія.
blocks[0].data.rules[2].example: <span lang="en">Maksym asked Olena for a spare pen.</span><br><span lang="uk">Максим попросив в Олени запасну ручку.</span>
blocks[1].key: say-tell-ask-patterns
blocks[1].existing_source_index: 1
blocks[1].type: forms-grid
blocks[1].column: left
blocks[1].level: null
blocks[1].data.title: 1. Спочатку намір і модель
blocks[1].data.intro: Say передає сказане, tell у моделі повідомлення називає адресата, ask запитує або просить. Форми минулого часу: said, told, asked; теперішнього для he/she — says, tells, asks.
blocks[1].data.items[0].label: SAY: ЗМІСТ І НЕОБОВ’ЯЗКОВИЙ АДРЕСАТ
blocks[1].data.items[0].title: say (that)… / say to someone (that)…
blocks[1].data.items[0].subtitle: <span lang="en">Lina said to us that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span> Say має додаток-зміст; перед названим адресатом ставимо to.
blocks[1].data.items[1].label: TELL: ПОВІДОМЛЕННЯ АДРЕСАТОВІ
blocks[1].data.items[1].title: tell someone (that)…
blocks[1].data.items[1].subtitle: <span lang="en">Lina told us that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span> У цій моделі us — кому сказали; that вводить зміст і часто опускається.
blocks[1].data.items[2].label: ASK: ПОТРІБНА ВІДПОВІДЬ
blocks[1].data.items[2].title: ask (someone) if / whether / wh-word…
blocks[1].data.items[2].subtitle: <span lang="en">Marta asked Ihor where the key was.</span><br><span lang="uk">Марта запитала Ігоря, де ключ.</span> Можна також ask a question — «поставити запитання». Після ask у підрядному питанні звичайний порядок слів.
blocks[1].data.items[3].label: TELL: ВКАЗІВКА АБО ЗАБОРОНА
blocks[1].data.items[3].title: tell someone to… / tell someone not to…
blocks[1].data.items[3].subtitle: <span lang="en">The assistant told us not to move the boxes.</span><br><span lang="uk">Помічник сказав нам не пересувати коробки.</span> Назви адресата-виконавця; not to заперечує саме дію.
blocks[1].data.items[4].label: ASK: ХТО МАЄ ЗРОБИТИ ДІЮ
blocks[1].data.items[4].title: ask to… / ask someone to…
blocks[1].data.items[4].subtitle: <span lang="en">Roman asked to sit near the window.</span><br><span lang="uk">Роман попросив дозволу сісти біля вікна.</span><br><span lang="en">Roman asked Iryna to sit near the window.</span><br><span lang="uk">Роман попросив Ірину сісти біля вікна.</span> У першому випадку сісти хоче Роман; у другому він просить про дію Ірину.
blocks[1].data.items[5].label: ASK FOR: ЩОСЬ ПОТРІБНО ОТРИМАТИ
blocks[1].data.items[5].title: ask (someone) for something
blocks[1].data.items[5].subtitle: <span lang="en">Maksym asked Olena for a spare pen.</span><br><span lang="uk">Максим попросив в Олени запасну ручку.</span> Адресат перед for; for вводить те, що просять дати. Не додавай for перед a question у значенні «поставити запитання».
blocks[2].key: say-tell-ask-meaning
blocks[2].existing_source_index: 2
blocks[2].type: comparison-table
blocks[2].column: left
blocks[2].level: null
blocks[2].data.title: 2. Однакові слова — різні мовленнєві дії
blocks[2].data.intro: Кожен рядок має окрему ситуацію. Tell не завжди грубе, а ask не завжди ввічливе: важливі намір, тон і контекст. Переказ прохання не доводить, що його виконали.
blocks[2].data.headers[0]: Контекст і прямі слова
blocks[2].data.headers[1]: Непрямий переказ і переклад
blocks[2].data.headers[2]: Хто й що робить
blocks[2].data.rows[0].cells[0]: Марта не знає місця ключа й запитує Ігоря: <span lang="en">Where is the key?</span><br><span lang="uk">Де ключ?</span>
blocks[2].data.rows[0].cells[1]: <span lang="en">Marta asked Ihor where the key was.</span><br><span lang="uk">Марта запитала Ігоря, де ключ.</span>
blocks[2].data.rows[0].cells[2]: Марта шукає інформацію. Наявності відповіді речення не стверджує.
blocks[2].data.rows[1].cells[0]: Марта знає місце ключа й пояснює його Ігореві: <span lang="en">The key is in the drawer.</span><br><span lang="uk">Ключ у шухляді.</span>
blocks[2].data.rows[1].cells[1]: <span lang="en">Marta told Ihor where the key was.</span><br><span lang="uk">Марта повідомила Ігореві, де ключ.</span>
blocks[2].data.rows[1].cells[2]: Told + wh-clause правильне: це інформування, не запит. Цей стислий переказ називає факт інформування, не повторюючи слово drawer — «шухляда».
blocks[2].data.rows[2].cells[0]: Роман хоче сам сісти біля вікна й звертається до провідниці: <span lang="en">May I sit near the window?</span><br><span lang="uk">Можна мені сісти біля вікна?</span>
blocks[2].data.rows[2].cells[1]: <span lang="en">Roman asked to sit near the window.</span><br><span lang="uk">Роман попросив дозволу сісти біля вікна.</span>
blocks[2].data.rows[2].cells[2]: Виконавець sit — сам Роман. Не вигадуй іншого виконавця після asked.
blocks[2].data.rows[3].cells[0]: Роман хоче, щоб біля вікна сіла Ірина, й просить її: <span lang="en">Could you sit near the window?</span><br><span lang="uk">Ти могла б сісти біля вікна?</span>
blocks[2].data.rows[3].cells[1]: <span lang="en">Roman asked Iryna to sit near the window.</span><br><span lang="uk">Роман попросив Ірину сісти біля вікна.</span>
blocks[2].data.rows[3].cells[2]: Первинне you — Ірина; дія після to стосується її. Це прохання про дію, а не перевірка її вміння сидіти.
blocks[3].key: say-tell-ask-mistakes
blocks[3].existing_source_index: 3
blocks[3].type: mistakes-grid
blocks[3].column: right
blocks[3].level: null
blocks[3].data.title: 3. Типові помилки з моделлю й наміром
blocks[3].data.items[0].label: НЕ ГУБИ ВІДОМОГО АДРЕСАТА
blocks[3].data.items[0].color: rose
blocks[3].data.items[0].title: Після said перед адресатом потрібне to.
blocks[3].data.items[0].wrong_en: Lina said us that the workshop was full.
blocks[3].data.items[0].wrong_ua: Задум: Ліна сказала нам, що в майстерні немає вільних місць.
blocks[3].data.items[0].right_en: Lina said to us that the workshop was full.
blocks[3].data.items[0].right_ua: Ліна сказала нам, що в майстерні немає вільних місць.
blocks[3].data.items[0].note: Також правильно: <span lang="en">Lina told us that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span> Said that без us граматичне, але не зберігає явно названого адресата.
blocks[3].data.items[1].label: TOLD WHERE НЕ Є ПОМИЛКОЮ
blocks[3].data.items[1].color: amber
blocks[3].data.items[1].title: Розрізняй отримання й надання інформації.
blocks[3].data.items[1].wrong_en: Daryna asked me where the spare key was.
blocks[3].data.items[1].wrong_ua: Це: Дарина запитала мене, де запасний ключ; задум — вона повідомила мені місце ключа.
blocks[3].data.items[1].right_en: Daryna told me where the spare key was.
blocks[3].data.items[1].right_ua: Дарина повідомила мені, де запасний ключ.
blocks[3].data.items[1].note: Обидві моделі граматичні, але asked шукає відповідь, told передає інформацію. Me — той самий оповідач, з яким говорила Дарина.
blocks[3].data.items[2].label: ЩО САМЕ ЗАПЕРЕЧЕНО
blocks[3].data.items[2].color: sky
blocks[3].data.items[2].title: Не переносити not із дії на повідомлення.
blocks[3].data.items[2].wrong_en: The assistant did not tell us to move the boxes.
blocks[3].data.items[2].wrong_ua: Це: помічник не казав нам пересувати коробки; задум — він сказав не пересувати їх.
blocks[3].data.items[2].right_en: The assistant told us not to move the boxes.
blocks[3].data.items[2].right_ua: Помічник сказав нам не пересувати коробки.
blocks[3].data.items[2].note: Did not tell не доводить протилежної вказівки. Так само didn’t ask someone to й asked someone not to мають різний зміст. Формальне ask that можливе, але вправа може прямо вимагати базову модель object + to.
blocks[4].key: say-tell-ask-summary
blocks[4].existing_source_index: 4
blocks[4].type: summary-list
blocks[4].column: right
blocks[4].level: null
blocks[4].data.title: 4. Коротке правило
blocks[4].data.items[0]: Say називає сказане: say something / say that…; адресат — to someone. Tell у моделі повідомлення: tell someone something / tell someone that… .
blocks[4].data.items[1]: Tell не завжди потребує названої особи: tell the truth — «казати правду», tell a lie — «казати неправду», tell a story — «розповідати історію». Це окремі сталі сполуки.
blocks[4].data.items[2]: Ask + питання шукає відповідь; tell + wh повідомляє відповідь. Ask to й ask someone to називають різних виконавців; ask for називає потрібну річ або інформацію.
blocks[4].data.items[3]: Повні моделі дивись у <a href="/theory/reported-speech/reported-statements">Reported Statements</a>, <a href="/theory/reported-speech/reported-questions">Reported Questions</a> і <a href="/theory/reported-speech/reported-commands-and-requests">Reported Commands and Requests</a>. Зберігай адресата, намір і місце заперечення.
blocks[5].key: say-tell-ask-navigation
blocks[5].existing_source_index: 5
blocks[5].type: navigation-chips
blocks[5].column: footer
blocks[5].level: null
blocks[5].data.title: Сторінки цього розділу
blocks[5].data.items[0].label: Reported Statements
blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
blocks[5].data.items[0].current: false
blocks[5].data.items[1].label: Reported Questions
blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
blocks[5].data.items[1].current: false
blocks[5].data.items[2].label: Reported Commands and Requests
blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
blocks[5].data.items[2].current: false
blocks[5].data.items[3].label: Backshift of Tenses
blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
blocks[5].data.items[3].current: false
blocks[5].data.items[4].label: Time and Place Changes
blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
blocks[5].data.items[4].current: false
blocks[5].data.items[5].label: Say / Tell / Ask
blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
blocks[5].data.items[5].current: true
blocks[6].key: say-tell-ask-practice
blocks[6].existing_source_index: null
blocks[6].type: practice-set
blocks[6].column: left
blocks[6].level: B1
blocks[6].data.title: Практика
blocks[6].data.select_title: Доповни задану модель
blocks[6].data.select_intro: Виконай саме зазначену часову або граматичну умову; збережи учасників і заперечення.
blocks[6].data.selects[0].id: m61-say-tell-ask-form-recipient
blocks[6].data.selects[0].source_index: 1
blocks[6].data.selects[0].label: У понеділок Ліна повідомила двом колегам, що в майстерні немає вільних місць. У вівторок один із цих колег переповідає іншій людині: us — обидва первинні адресати, зокрема сам оповідач. Збережи said і названого адресата; that-clause вже задана.<br><span lang="en">Lina said ___ that the workshop was full.</span><br><span lang="uk">Ліна сказала нам, що в майстерні немає вільних місць.</span>
blocks[6].data.selects[0].options[0]: us
blocks[6].data.selects[0].options[1]: to us
blocks[6].data.selects[0].options[2]: for us
blocks[6].data.selects[0].answer: to us
blocks[6].data.selects[0].accepted[0]: to us
blocks[6].data.selects[0].feedback.us: Lina said to us that the workshop was full.
  Ліна сказала нам, що в майстерні немає вільних місць. Після said не ставимо адресата просто як us; потрібне to us.
blocks[6].data.selects[0].feedback.to us: Lina said to us that the workshop was full.
  Ліна сказала нам, що в майстерні немає вільних місць. Said to us зберігає явно названих адресатів.
blocks[6].data.selects[0].feedback.for us: Lina said to us that the workshop was full.
  Ліна сказала нам, що в майстерні немає вільних місць. For us не позначає адресата в заданій моделі said to someone.
blocks[6].data.selects[1].id: m61-say-tell-ask-form-for
blocks[6].data.selects[1].source_index: 2
blocks[6].data.selects[1].label: У понеділок Максим попросив Олену дати йому запасну ручку; це прохання про річ, не запитання про її колір. У вівторок свідок переказує прохання. Названий адресат Olena вже стоїть після asked; доповни модель ask someone for something.<br><span lang="en">Maksym asked Olena ___ a spare pen.</span><br><span lang="uk">Максим попросив в Олени запасну ручку.</span>
blocks[6].data.selects[1].options[0]: to
blocks[6].data.selects[1].options[1]: that
blocks[6].data.selects[1].options[2]: for
blocks[6].data.selects[1].answer: for
blocks[6].data.selects[1].accepted[0]: for
blocks[6].data.selects[1].feedback.to: Maksym asked Olena for a spare pen.
  Максим попросив в Олени запасну ручку. To потребувало б дієслова дії в іншій моделі, а тут названо річ.
blocks[6].data.selects[1].feedback.that: Maksym asked Olena for a spare pen.
  Максим попросив в Олени запасну ручку. That не вводить потрібну річ у моделі ask for something.
blocks[6].data.selects[1].feedback.for: Maksym asked Olena for a spare pen.
  Максим попросив в Олени запасну ручку. For стоїть перед потрібною річчю; Olena — адресат перед for. Чи дала вона ручку, речення не повідомляє.
blocks[6].data.choice_title: Обери точний зміст
blocks[6].data.choice_intro: Зістав повідомлення з українським контекстом. Точний переклад англійського стимулу відкривається після перевірки.
blocks[6].data.choices[0].id: m61-say-tell-ask-meaning-inform
blocks[6].data.choices[0].source_index: 3
blocks[6].data.choices[0].label: У понеділок Дарина говорила з Ігорем про відомий їм запасний ключ. У вівторок сам Ігор переповідає сказане: me — Ігор. Яку мовленнєву дію передає речення? Не вгадуй конкретне місце ключа.
blocks[6].data.choices[0].options[0]: Дарина запитала в Ігоря, де ключ.
blocks[6].data.choices[0].options[1]: Дарина повідомила Ігореві, де ключ.
blocks[6].data.choices[0].options[2]: Дарина наказала Ігореві зробити новий ключ.
blocks[6].data.choices[0].answer: Дарина повідомила Ігореві, де ключ.
blocks[6].data.choices[0].accepted[0]: Дарина повідомила Ігореві, де ключ.
blocks[6].data.choices[0].feedback.дарина запитала в ігоря, де ключ.: Daryna told me where the spare key was.
  Дарина повідомила мені, де запасний ключ. Asked me where означало б запит. Тут використано told me where.
blocks[6].data.choices[0].feedback.дарина повідомила ігореві, де ключ.: Daryna told me where the spare key was.
  Дарина повідомила мені, де запасний ключ. Told + wh-clause тут передає надання інформації, а не запитання.
blocks[6].data.choices[0].feedback.дарина наказала ігореві зробити новий ключ.: Daryna told me where the spare key was.
  Дарина повідомила мені, де запасний ключ. У реченні немає вказівки виготовити ключ; where вводить інформацію про місце.
blocks[6].data.choices[0].prompt: <span lang="en">Daryna told me where the spare key was.</span>
blocks[6].data.choices[1].id: m61-say-tell-ask-meaning-actor
blocks[6].data.choices[1].source_index: 4
blocks[6].data.choices[1].label: У понеділок Роман звернувся до провідниці Ірини в потязі; у вівторок Ірина переказує цей епізод іншій людині. Хто в переданому проханні хотів сісти біля вікна? Не роби висновку, що місце справді надали.
blocks[6].data.choices[1].options[0]: Ірина: Роман попросив сісти її.
blocks[6].data.choices[1].options[1]: Роман: він попросив дозволу сісти сам.
blocks[6].data.choices[1].options[2]: Усі пасажири: це був наказ для всіх.
blocks[6].data.choices[1].answer: Роман: він попросив дозволу сісти сам.
blocks[6].data.choices[1].accepted[0]: Роман: він попросив дозволу сісти сам.
blocks[6].data.choices[1].feedback.ірина: роман попросив сісти її.: Roman asked to sit near the window.
  Роман попросив дозволу сісти біля вікна. Прохання до Ірини виконати дію потребувало б asked Iryna to sit.
blocks[6].data.choices[1].feedback.роман: він попросив дозволу сісти сам.: Roman asked to sit near the window.
  Роман попросив дозволу сісти біля вікна. У asked to sit виконавець sit — підмет Roman; речення не гарантує виконання прохання.
blocks[6].data.choices[1].feedback.усі пасажири: це був наказ для всіх.: Roman asked to sit near the window.
  Роман попросив дозволу сісти біля вікна. Немає ані всіх пасажирів, ані наказу; asked to передає прохання самого Романа.
blocks[6].data.choices[1].prompt: <span lang="en">Roman asked to sit near the window.</span>
blocks[6].data.input_title: Побудуй повне речення
blocks[6].data.input_intro: Введи відповідь або використай усі токени. Умови окремо називають допустимі ручні варіанти.
blocks[6].data.inputs[0].id: m61-say-tell-ask-build-question
blocks[6].data.inputs[0].source_index: 5
blocks[6].data.inputs[0].before: torch. / he / Marta / needed / if / Ihor / a / asked
blocks[6].data.inputs[0].after: У понеділок Марта запитала Ігоря: «Тобі потрібен ліхтарик?» (Do you need a torch?). У вівторок інший свідок переповідає саме запитання, а не відповідь; he — Ігор, адресат первинного you. Почни Marta asked Ihor, використай заданий минулий needed a torch; для yes/no дозволені if і whether. Передай: «Марта запитала Ігоря, чи потрібен йому ліхтарик». Це повідомлення про запит, тому заверши крапкою.
blocks[6].data.inputs[0].answer: Marta asked Ihor if he needed a torch.
blocks[6].data.inputs[0].accepted[0]: Marta asked Ihor if he needed a torch.
blocks[6].data.inputs[0].accepted[1]: Marta asked Ihor whether he needed a torch.
blocks[6].data.inputs[0].feedback.marta asked ihor if he needed a torch.: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. He — Ігор; if/whether рівнозначні в цьому простому питанні. Речення не повідомляє його відповіді.
blocks[6].data.inputs[0].feedback.marta asked ihor if he needed a torch: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. He — Ігор; if/whether рівнозначні в цьому простому питанні. Речення не повідомляє його відповіді.
blocks[6].data.inputs[0].feedback.marta asked ihor whether he needed a torch.: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. He — Ігор; if/whether рівнозначні в цьому простому питанні. Речення не повідомляє його відповіді.
blocks[6].data.inputs[0].feedback.marta asked ihor whether he needed a torch: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. He — Ігор; if/whether рівнозначні в цьому простому питанні. Речення не повідомляє його відповіді.
blocks[6].data.inputs[0].feedback.marta told ihor that he needed a torch.: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. Told Ihor that передає твердження, а не початкове yes/no-питання.
blocks[6].data.inputs[0].feedback.marta told ihor that he needed a torch: Marta asked Ihor if he needed a torch.
  Марта запитала Ігоря, чи потрібен йому ліхтарик. Told Ihor that передає твердження, а не початкове yes/no-питання.
blocks[6].data.inputs[1].id: m61-say-tell-ask-build-negative
blocks[6].data.inputs[1].source_index: 6
blocks[6].data.inputs[1].before: boxes. / us / not / assistant / to / the / told / The / move
blocks[6].data.inputs[1].after: У понеділок помічник звернувся до двох колег: «Не пересувайте ці коробки» (Do not move the boxes). У вівторок один із цих колег переповідає вказівку іншій людині: us — обидва первинні адресати, зокрема оповідач; boxes — ті самі відомі коробки. Почни The assistant told us, використай саме not to move the boxes. Передай: «Помічник сказав нам не пересувати коробки». Не змінюй вказівку на відсутність повідомлення.
blocks[6].data.inputs[1].answer: The assistant told us not to move the boxes.
blocks[6].data.inputs[1].accepted[0]: The assistant told us not to move the boxes.
blocks[6].data.inputs[1].feedback.the assistant told us not to move the boxes.: The assistant told us not to move the boxes.
  Помічник сказав нам не пересувати коробки. Not to стосується move; us називає тих, хто не має пересувати коробки. Виконання вказівки не підтверджено.
blocks[6].data.inputs[1].feedback.the assistant told us not to move the boxes: The assistant told us not to move the boxes.
  Помічник сказав нам не пересувати коробки. Not to стосується move; us називає тих, хто не має пересувати коробки. Виконання вказівки не підтверджено.
blocks[6].data.inputs[1].feedback.the assistant did not tell us to move the boxes.: The assistant told us not to move the boxes.
  Помічник сказав нам не пересувати коробки. Did not tell us означає, що вказівки пересувати не було; це не те саме, що сказана заборона.
blocks[6].data.inputs[1].feedback.the assistant did not tell us to move the boxes: The assistant told us not to move the boxes.
  Помічник сказав нам не пересувати коробки. Did not tell us означає, що вказівки пересувати не було; це не те саме, що сказана заборона.
blocks[6].data.rephrase: []
blocks[6].data.linked_practice.source: theory_links
blocks[6].data.linked_practice.question_types[0]: 4
blocks[6].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechSayTellAskAllLevelsLessonSeeder
blocks[6].data.linked_practice.title: Вправа 4. Побудуй речення
blocks[6].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова.
blocks[6].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
details[0].key: say-tell-ask-utterance-intent
details[0].title: Дієслово всередині репліки й намір усієї репліки
details[0].owner_block_key: say-tell-ask-patterns
details[0].owner_pointer: /items/2
details[0].fragment.type: usage-panels
details[0].fragment.data.sections[0].label: COULD YOU TELL ME… МОЖЕ БУТИ ПРОХАННЯМ
details[0].fragment.data.sections[0].color: blue
details[0].fragment.data.sections[0].description: Не визначай reporting verb лише за дієсловом усередині цитати. Людина може вимовити tell, але всією реплікою попросити надати інформацію.
details[0].fragment.data.sections[0].examples[0].en: Ihor said to Marta, “Could you tell me the price?”
details[0].fragment.data.sections[0].examples[0].ua: Ігор сказав Марті: «Ти могла б сказати мені ціну?»
details[0].fragment.data.sections[0].examples[1].en: Ihor asked Marta to tell him the price.
details[0].fragment.data.sections[0].examples[1].ua: Ігор попросив Марту сказати йому ціну.
details[0].fragment.data.sections[0].note: Первинне you — Марта, me й reported him — Ігор. Це прохання до Марти про дію; воно не стверджує, що вона вже назвала ціну.
details[0].fragment.data.sections[1].label: МОДЕЛЬ ВПРАВИ НЕ Є ЗАБОРОНОЮ ІНШИХ МОДЕЛЕЙ
details[0].fragment.data.sections[1].color: amber
details[0].fragment.data.sections[1].description: У базовому проханні зручно ask someone to. Формальне ask that також можливе, тож різниця між моделями не означає, що всяке речення з ask that неграматичне.
details[0].fragment.data.sections[1].examples[0].en: The chair asked the members to remain seated.
details[0].fragment.data.sections[1].examples[0].ua: Голова попросив учасників залишатися на місцях.
details[0].fragment.data.sections[1].examples[1].en: The chair asked that the members remain seated.
details[0].fragment.data.sections[1].examples[1].ua: Голова попросив, щоб учасники залишалися на місцях.
details[0].fragment.data.sections[1].note: У першому прикладі прямо названа модель object + to. У другому — формальна that-clause. Якщо завдання перевіряє першу модель, воно має це явно сказати.
