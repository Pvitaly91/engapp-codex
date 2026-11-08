# M60 — авторська редакція 1.0.0

Frozen author source; це не підтвердження DB apply чи браузерного приймання.

18 власних завдань: 2 form-select + 2 meaning-choice + 2 manual/token inputs на сторінку. Окремий widget містить 5 тематичних питань на сторінку; він не замінюється own inputs.

## reported-statements

status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechStatementsTheorySeeder
slug: reported-statements
locale: uk
display_level: B1-B2
technical_block_level: B1
  category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/reported-statements
subtitle_html: <p><strong>Reported Statements</strong> — як передавати твердження через say і tell, зберігаючи учасників, час і зміст.</p>
subtitle_text: Reported Statements — як передавати твердження через say і tell, зберігаючи учасників, час і зміст.
  blocks[0].key: statements-hero
  blocks[0].type: hero
  blocks[0].column: header
  blocks[0].level: B1
  blocks[0].data.level: B1-B2
  blocks[0].data.intro: Reported Statements передає зміст чиїхось слів, а не оформлює дослівну цитату. Спершу визнач, хто говорив, кому й коли; тоді обери say або tell, займенники та потрібну часову перспективу.
    blocks[0].data.rules[0].label: ПОВІДОМЛЕННЯ
    blocks[0].data.rules[0].color: emerald
    blocks[0].data.rules[0].text: Reporting clause називає мовця; reported clause передає зміст.
    blocks[0].data.rules[0].example: <span lang="en">Oksana said that the room was cold.</span><br><span lang="uk">Оксана сказала, що в кімнаті холодно.</span>
    blocks[0].data.rules[1].label: АДРЕСАТ ПІСЛЯ TELL
    blocks[0].data.rules[1].color: blue
    blocks[0].data.rules[1].text: У моделі повідомлення tell називаємо того, кому сказали.
    blocks[0].data.rules[1].example: <span lang="en">Oksana told Petro that the room was cold.</span><br><span lang="uk">Оксана сказала Петрові, що в кімнаті холодно.</span>
    blocks[0].data.rules[2].label: КОНТЕКСТ, НЕ АВТОЗАМІНА
    blocks[0].data.rules[2].color: amber
    blocks[0].data.rules[2].text: Час, особа й місце залежать від нової ситуації переказу.
    blocks[0].data.rules[2].example: <span lang="en">I said that I needed a pen.</span><br><span lang="uk">Я сказав, що мені потрібна ручка.</span>
  blocks[1].key: statements-forms
  blocks[1].type: forms-grid
  blocks[1].column: left
  blocks[1].data.title: 1. Say, tell і частина з повідомленням
  blocks[1].data.intro: У цих моделях said/told — минулі форми say/tell. That вводить повідомлення й часто опускається без зміни змісту.
    blocks[1].data.items[0].label: SAY БЕЗ АДРЕСАТА
    blocks[1].data.items[0].title: підмет + said + (that) + повідомлення
    blocks[1].data.items[0].subtitle: <span lang="en">Nazar said that the shop was closed.</span><br><span lang="uk">Назар сказав, що магазин зачинений.</span>
    blocks[1].data.items[1].label: SAY І НАЗВАНИЙ АДРЕСАТ
    blocks[1].data.items[1].title: підмет + said to + адресат + (that) + повідомлення
    blocks[1].data.items[1].subtitle: <span lang="en">Nazar said to me that the shop was closed.</span><br><span lang="uk">Назар сказав мені, що магазин зачинений.</span>
    blocks[1].data.items[2].label: TELL І НАЗВАНИЙ АДРЕСАТ
    blocks[1].data.items[2].title: підмет + told + адресат + (that) + повідомлення
    blocks[1].data.items[2].subtitle: <span lang="en">Nazar told me that the shop was closed.</span><br><span lang="uk">Назар сказав мені, що магазин зачинений.</span>
    blocks[1].data.items[3].label: З THAT АБО БЕЗ НЬОГО
    blocks[1].data.items[3].title: said (that) / told someone (that)
    blocks[1].data.items[3].subtitle: <span lang="en">Nazar said the shop was closed.</span><br><span lang="uk">Назар сказав, що магазин зачинений.</span> Це те саме повідомлення без that.
  blocks[2].key: statements-comparison
  blocks[2].type: comparison-table
  blocks[2].column: left
  blocks[2].data.title: 2. Прямі слова й переказ: спершу учасники
  blocks[2].data.intro: Кожен рядок має власну ситуацію. Лапки належать до прямої мови; reported clause тут передає її зміст без лапок.
    blocks[2].data.headers[0]: Ситуація й пряма мова
    blocks[2].data.headers[1]: Непряма мова і переклад
    blocks[2].data.headers[2]: Що змінилося
      blocks[2].data.rows[0].cells[0]: У понеділок Ірина говорить Олегові про свій тодішній стан: <span lang="en">I am busy.</span><br><span lang="uk">Я зайнята.</span>
      blocks[2].data.rows[0].cells[1]: <span lang="en">Iryna told Oleh that she was busy.</span><br><span lang="uk">Ірина сказала Олегові, що зайнята.</span>
      blocks[2].data.rows[0].cells[2]: У вівторковому переказі she — Ірина; was передає минулу точку мовлення.
      blocks[2].data.rows[1].cells[0]: Тарас стоїть біля свого брата в майстерні й каже: <span lang="en">My brother is here.</span><br><span lang="uk">Мій брат тут.</span>
      blocks[2].data.rows[1].cells[1]: <span lang="en">Taras said that his brother was there.</span><br><span lang="uk">Тарас сказав, що його брат там.</span>
      blocks[2].data.rows[1].cells[2]: Пізніше Олена переказує це з дому: his — Тарасів; there — у майстерні, не біля Олени.
      blocks[2].data.rows[2].cells[0]: Петро каже Марії: <span lang="en">My sister will call you.</span><br><span lang="uk">Моя сестра зателефонує тобі.</span>
      blocks[2].data.rows[2].cells[1]: <span lang="en">Petro told me that his sister would call me.</span><br><span lang="uk">Петро сказав мені, що його сестра зателефонує мені.</span>
      blocks[2].data.rows[2].cells[2]: Це переказ самої Марії пізніше: his — Петрів; обидва me — Марія.
      blocks[2].data.rows[3].cells[0]: Катерина сказала: <span lang="en">I work at the library.</span><br><span lang="uk">Я працюю в бібліотеці.</span> Пізніше інша людина переказує це; робота Катерини досі актуальна.
      blocks[2].data.rows[3].cells[1]: <span lang="en">Kateryna said that she works at the library.</span><br><span lang="uk">Катерина сказала, що працює в бібліотеці.</span>
      blocks[2].data.rows[3].cells[2]: Без backshift можна підкреслити актуальність. Варіант worked теж можливий і сам по собі не означає, що вона звільнилася.
  blocks[3].key: statements-mistakes
  blocks[3].type: mistakes-grid
  blocks[3].column: right
  blocks[3].data.title: 3. Не змішуй моделі й точки зору
    blocks[3].data.items[0].label: АДРЕСАТ У TELL
    blocks[3].data.items[0].color: rose
    blocks[3].data.items[0].title: У цій моделі told потрібен адресат.
    blocks[3].data.items[0].wrong_en: He told that the lift was broken.
    blocks[3].data.items[0].wrong_ua: Задум: він сказав мені, що ліфт зламаний.
    blocks[3].data.items[0].right_en: He told me that the lift was broken.
    blocks[3].data.items[0].right_ua: Він сказав мені, що ліфт зламаний.
    blocks[3].data.items[0].note: Якщо адресат невідомий, можна обрати said, а не вигадувати me. Це правило моделі повідомлення: сполуки на кшталт tell a story — «розповідати історію» — мають іншу будову.
    blocks[3].data.items[1].label: SAY TO SOMEONE
    blocks[3].data.items[1].color: amber
    blocks[3].data.items[1].title: Не став адресата прямо після said.
    blocks[3].data.items[1].wrong_en: She said me that the train was late.
    blocks[3].data.items[1].wrong_ua: Задум: вона сказала мені, що потяг запізнюється.
    blocks[3].data.items[1].right_en: She said to me that the train was late.
    blocks[3].data.items[1].right_ua: Вона сказала мені, що потяг запізнюється.
    blocks[3].data.items[1].note: Інша правильна модель: <span lang="en">She told me that the train was late.</span><br><span lang="uk">Вона сказала мені, що потяг запізнюється.</span>
    blocks[3].data.items[2].label: НЕ ЦИТАТА ВСЕРЕДИНІ ПЕРЕКАЗУ
    blocks[3].data.items[2].color: sky
    blocks[3].data.items[2].title: Збережи одну систему осіб.
    blocks[3].data.items[2].wrong_en: Olena said that “I am busy.”
    blocks[3].data.items[2].wrong_ua: Задум: інша людина переказує слова Олени «Я зайнята».
    blocks[3].data.items[2].right_en: Olena said that she was busy.
    blocks[3].data.items[2].right_ua: Олена сказала, що зайнята.
    blocks[3].data.items[2].note: Проблема не в «інверсії»: твердження вже мають звичайний порядок слів. Тут змішані цитатне I й переказ від іншої особи. Дослівну цитату оформлюють окремо: <span lang="en">Olena said, “I am busy.”</span><br><span lang="uk">Олена сказала: «Я зайнята».</span>
  blocks[4].key: statements-summary
  blocks[4].type: summary-list
  blocks[4].column: right
  blocks[4].data.title: 4. Коротке правило
    blocks[4].data.items[0]: Спершу встанови мовця, адресата й нового оповідача. I може лишитися I, якщо людина переказує власні слова.
    blocks[4].data.items[1]: Для повідомлення: said (that), said to someone (that), told someone (that). That часто необов’язкове.
    blocks[4].data.items[2]: Після минулого said/told backshift частий, але не автоматичний. Минула форма не доводить, що факт уже неправдивий.
    blocks[4].data.items[3]: Зміни here, today, tomorrow та this потрібні тоді, коли змінилася точка відліку. Не перенось заперечення між reporting clause і повідомленням.
  blocks[5].key: statements-navigation
  blocks[5].type: navigation-chips
  blocks[5].column: footer
  blocks[5].data.title: Сторінки цього розділу
    blocks[5].data.items[0].label: Reported Statements
    blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
    blocks[5].data.items[1].label: Reported Questions
    blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
    blocks[5].data.items[2].label: Reported Commands and Requests
    blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
    blocks[5].data.items[3].label: Backshift of Tenses
    blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
    blocks[5].data.items[4].label: Time and Place Changes
    blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
    blocks[5].data.items[5].label: Say / Tell / Ask
    blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
  blocks[6].key: statements-context
  blocks[6].type: usage-panels
  blocks[6].column: left
  blocks[6].level: B1
  blocks[6].data.title: 5. Час, вказівні слова й заперечення
    blocks[6].data.sections[0].label: BACKSHIFT У ПОТРІБНІЙ ПЕРСПЕКТИВІ
    blocks[6].data.sections[0].color: emerald
    blocks[6].data.sections[0].description: Типові переходи для минулої точки переказу: Present Simple → Past Simple; Present Continuous → Past Continuous; Present Perfect → Past Perfect; will → would. Past Simple часто переходить у Past Perfect, а Past Perfect зазвичай лишається без зміни. Це не наказ зсувати будь-яку форму в будь-якому контексті.
      blocks[6].data.sections[0].examples[0].en: Sofiia said that she had lost her ticket.
      blocks[6].data.sections[0].examples[0].ua: Софія сказала, що загубила квиток.
    blocks[6].data.sections[0].note: Тут загублений квиток — частина повідомлення Софії. Повну систему часів і модальних форм розглядає окремий урок про backshift.
    blocks[6].data.sections[1].label: TOMORROW ЗАЛЕЖИТЬ ВІД ДНЯ ПЕРЕКАЗУ
    blocks[6].data.sections[1].color: blue
    blocks[6].data.sections[1].description: У понеділок Денис говорить про вівторок. Переказуючи ще в понеділок, можемо залишити tomorrow. Переказуючи пізніше, називаємо день відносно його репліки: the next day / the following day.
      blocks[6].data.sections[1].examples[0].en: Denys said that he would return tomorrow.
      blocks[6].data.sections[1].examples[0].ua: Денис сказав, що повернеться завтра.
      blocks[6].data.sections[1].examples[1].en: Denys said that he would return the next day.
      blocks[6].data.sections[1].examples[1].ua: Денис сказав, що повернеться наступного дня.
    blocks[6].data.sections[1].note: Today, here і this теж змінюються лише за потреби нової точки відліку, не за правилом «замінити завжди».
    blocks[6].data.sections[2].label: ЩО САМЕ ЗАПЕРЕЧЕНО
    blocks[6].data.sections[2].color: amber
    blocks[6].data.sections[2].description: Перше речення не приписує Олегові обіцянки прийти. Друге передає його повідомлення про те, що він не прийде. Це різні факти про його слова.
      blocks[6].data.sections[2].examples[0].en: Oleh did not say that he would come.
      blocks[6].data.sections[2].examples[0].ua: Олег не говорив, що прийде.
      blocks[6].data.sections[2].examples[1].en: Oleh said that he would not come.
      blocks[6].data.sections[2].examples[1].ua: Олег сказав, що не прийде.
  blocks[7].key: statements-practice
  blocks[7].type: practice-set
  blocks[7].column: left
  blocks[7].level: B1
  blocks[7].data.title: Практика
  blocks[7].data.select_title: Доповни задану модель
  blocks[7].data.select_intro: Збережи учасників і прямо задану форму повідомлення.
    blocks[7].data.selects[0].id: m60-statements-form-recipient
    blocks[7].data.selects[0].label: Марта повідомила Ірині, що автобус запізнюється. Доповни саме модель told + адресат + that.<br><span lang="en">Marta told ___ that the bus was late.</span><br><span lang="uk">Марта сказала Ірині, що автобус запізнюється.</span>
      blocks[7].data.selects[0].options[0]: to Iryna
      blocks[7].data.selects[0].options[1]: Iryna
      blocks[7].data.selects[0].options[2]: that Iryna
    blocks[7].data.selects[0].answer: Iryna
      blocks[7].data.selects[0].accepted[0]: Iryna
    blocks[7].data.selects[0].feedback.to iryna: У цій моделі адресат стоїть без to: <span lang="en">Marta told Iryna that the bus was late.</span><br><span lang="uk">Марта сказала Ірині, що автобус запізнюється.</span>
    blocks[7].data.selects[0].feedback.iryna: <span lang="en">Marta told Iryna that the bus was late.</span><br><span lang="uk">Марта сказала Ірині, що автобус запізнюється.</span> Told + Iryna + that-clause.
    blocks[7].data.selects[0].feedback.that iryna: That вводить саме повідомлення, не адресата: <span lang="en">Marta told Iryna that the bus was late.</span><br><span lang="uk">Марта сказала Ірині, що автобус запізнюється.</span>
    blocks[7].data.selects[1].id: m60-statements-form-backshift
    blocks[7].data.selects[1].label: У понеділок Олег сказав: «Я працюю над звітом». У вівторок інша людина передає його слова: he — Олег. Використай заданий backshift: Present Continuous → Past Continuous.<br><span lang="en">Oleh said that he ___ on the report.</span><br><span lang="uk">Олег сказав, що працює над звітом.</span>
      blocks[7].data.selects[1].options[0]: is working
      blocks[7].data.selects[1].options[1]: was working
      blocks[7].data.selects[1].options[2]: had work
    blocks[7].data.selects[1].answer: was working
      blocks[7].data.selects[1].accepted[0]: was working
    blocks[7].data.selects[1].feedback.is working: Is working можливе за доречного актуального контексту, але тут прямо задано Past Continuous: <span lang="en">Oleh said that he was working on the report.</span><br><span lang="uk">Олег сказав, що працює над звітом.</span>
    blocks[7].data.selects[1].feedback.was working: <span lang="en">Oleh said that he was working on the report.</span><br><span lang="uk">Олег сказав, що працює над звітом.</span> Was working передає процес із минулої точки повідомлення; це не доводить, що тепер роботу припинено.
    blocks[7].data.selects[1].feedback.had work: Past Continuous утворюємо як was + V-ing: <span lang="en">Oleh said that he was working on the report.</span><br><span lang="uk">Олег сказав, що працює над звітом.</span>
  blocks[7].data.choice_title: Обери точний зміст
  blocks[7].data.choice_intro: Не додавай фактів, яких повідомлення не стверджує. Переклад англійського стимулу відкривається після перевірки.
    blocks[7].data.choices[0].id: m60-statements-meaning-negation
    blocks[7].data.choices[0].label: У повідомленні he — Тарас. Який зміст передано точно? Не перенось заперечення на сам факт висловлювання.
      blocks[7].data.choices[0].options[0]: Тарас не говорив, що приєднається до нас.
      blocks[7].data.choices[0].options[1]: Тарас сказав, що не приєднається до нас.
      blocks[7].data.choices[0].options[2]: Тарас уже приєднався до нас.
    blocks[7].data.choices[0].answer: Тарас сказав, що не приєднається до нас.
      blocks[7].data.choices[0].accepted[0]: Тарас сказав, що не приєднається до нас.
    blocks[7].data.choices[0].feedback.тарас не говорив, що приєднається до нас.: <span lang="en">Taras said that he would not join us.</span><br><span lang="uk">Тарас сказав, що не приєднається до нас.</span> Він зробив повідомлення; заперечена участь, а не дієслово said.
    blocks[7].data.choices[0].feedback.тарас сказав, що не приєднається до нас.: <span lang="en">Taras said that he would not join us.</span><br><span lang="uk">Тарас сказав, що не приєднається до нас.</span> Not належить до would join.
    blocks[7].data.choices[0].feedback.тарас уже приєднався до нас.: <span lang="en">Taras said that he would not join us.</span><br><span lang="uk">Тарас сказав, що не приєднається до нас.</span> Речення передає його слова про подальшу участь, не здійснений прихід.
    blocks[7].data.choices[0].prompt: <span lang="en">Taras said that he would not join us.</span>
    blocks[7].data.choices[1].id: m60-statements-meaning-self-report
    blocks[7].data.choices[1].label: Марія сама переповідає власні попередні слова. Хто потребував ключа?
      blocks[7].data.choices[1].options[0]: Марія, яка зараз говорить.
      blocks[7].data.choices[1].options[1]: Інша жінка, не Марія.
      blocks[7].data.choices[1].options[2]: Співрозмовник Марії.
    blocks[7].data.choices[1].answer: Марія, яка зараз говорить.
      blocks[7].data.choices[1].accepted[0]: Марія, яка зараз говорить.
    blocks[7].data.choices[1].feedback.марія, яка зараз говорить.: <span lang="en">I said that I needed the key.</span><br><span lang="uk">Я сказала, що мені потрібен ключ.</span> У цьому контексті обидва I — сама Марія.
    blocks[7].data.choices[1].feedback.інша жінка, не марія.: <span lang="en">I said that I needed the key.</span><br><span lang="uk">Я сказала, що мені потрібен ключ.</span> Самопереказ не вимагає заміни I на she.
    blocks[7].data.choices[1].feedback.співрозмовник марії.: <span lang="en">I said that I needed the key.</span><br><span lang="uk">Я сказала, що мені потрібен ключ.</span> Потребу називає друге I, не неназваний адресат.
    blocks[7].data.choices[1].prompt: <span lang="en">I said that I needed the key.</span>
  blocks[7].data.input_title: Побудуй повне речення
  blocks[7].data.input_intro: Введи відповідь або склади її з усіх токенів. Для ручного вводу дозволені лише рівнозначні варіанти, описані в умові.
    blocks[7].data.inputs[0].id: m60-statements-build-tomorrow
    blocks[7].data.inputs[0].before: next / the / she / Olena / the / bring / that / day. / map / would / said
    blocks[7].data.inputs[0].after: У понеділок Олена сказала: «Я принесу карту завтра». У вівторок інша людина переповідає ці слова про Олену; у переказі she — Олена. Напиши: «Олена сказала, що принесе карту наступного дня». Почни з Olena said, використай would bring — принесе та задане the map — цю відому карту; обставину постав наприкінці. That можна опустити; дозволені the next day і the following day, а she would можна скоротити до she’d.
    blocks[7].data.inputs[0].answer: Olena said that she would bring the map the next day.
      blocks[7].data.inputs[0].accepted[0]: Olena said that she would bring the map the next day.
      blocks[7].data.inputs[0].accepted[1]: Olena said she would bring the map the next day.
      blocks[7].data.inputs[0].accepted[2]: Olena said that she would bring the map the following day.
      blocks[7].data.inputs[0].accepted[3]: Olena said she would bring the map the following day.
      blocks[7].data.inputs[0].accepted[4]: Olena said that she'd bring the map the next day.
      blocks[7].data.inputs[0].accepted[5]: Olena said she'd bring the map the next day.
      blocks[7].data.inputs[0].accepted[6]: Olena said that she'd bring the map the following day.
      blocks[7].data.inputs[0].accepted[7]: Olena said she'd bring the map the following day.
    blocks[7].data.inputs[0].feedback.olena said that she would bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she would bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she would bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she would bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she would bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she would bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she would bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she would bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she'd bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she'd bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she’d bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she’d bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she'd bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she'd bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she’d bring the map the next day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she’d bring the map the next day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she'd bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she'd bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she’d bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that she’d bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she'd bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she'd bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she’d bring the map the following day.: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said she’d bring the map the following day: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span> She — Олена; the next/following day відраховуємо від понеділкової репліки, тобто йдеться про вівторок.
    blocks[7].data.inputs[0].feedback.olena said that i would bring the map the next day.: I помилково робить виконавцем нового оповідача. Умова називає Олену: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span>
    blocks[7].data.inputs[0].feedback.olena said that i would bring the map the next day: I помилково робить виконавцем нового оповідача. Умова називає Олену: <span lang="en">Olena said that she would bring the map the next day.</span><br><span lang="uk">Олена сказала, що принесе карту наступного дня.</span>
    blocks[7].data.inputs[1].id: m60-statements-build-negative
    blocks[7].data.inputs[1].before: hungry. / not / was / he / that / said / Danylo
    blocks[7].data.inputs[1].after: Данило сказав іншій людині: «Я не голодний». Тепер інший оповідач передає: «Данило сказав, що він не голодний»; he — Данило. Почни з Danylo said, використай заданий Past Simple: was not hungry. That можна опустити; was not можна скоротити до wasn’t.
    blocks[7].data.inputs[1].answer: Danylo said that he was not hungry.
      blocks[7].data.inputs[1].accepted[0]: Danylo said that he was not hungry.
      blocks[7].data.inputs[1].accepted[1]: Danylo said he was not hungry.
      blocks[7].data.inputs[1].accepted[2]: Danylo said that he wasn't hungry.
      blocks[7].data.inputs[1].accepted[3]: Danylo said he wasn't hungry.
    blocks[7].data.inputs[1].feedback.danylo said that he was not hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said that he was not hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he was not hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he was not hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said that he wasn't hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said that he wasn't hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said that he wasn’t hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said that he wasn’t hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he wasn't hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he wasn't hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he wasn’t hungry.: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo said he wasn’t hungry: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span> Заперечення належить до його стану. Was not і wasn’t рівнозначні; that тут необов’язкове.
    blocks[7].data.inputs[1].feedback.danylo did not say that he was hungry.: Це «Данило не сказав, що голодний»: заперечено іншу частину. Потрібно: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span>
    blocks[7].data.inputs[1].feedback.danylo did not say that he was hungry: Це «Данило не сказав, що голодний»: заперечено іншу частину. Потрібно: <span lang="en">Danylo said that he was not hungry.</span><br><span lang="uk">Данило сказав, що він не голодний.</span>
  blocks[7].data.linked_practice.source: theory_links
    blocks[7].data.linked_practice.question_types[0]: 4
    blocks[7].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechStatementsAllLevelsLessonSeeder
  blocks[7].data.linked_practice.title: Вправа 4. Побудуй речення
  blocks[7].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова. Збережи мовця, адресата й зміст.
  blocks[7].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
  details[0].key: statements-backshift-current-truth
  details[0].title: Минулий переказ і теперішня правдивість
  details[0].owner_block_key: statements-context
  details[0].owner_pointer: /sections/0
  details[0].fragment.type: usage-panels
    details[0].fragment.data.sections[0].label: Минулий переказ і теперішня правдивість
    details[0].fragment.data.sections[0].color: slate
    details[0].fragment.data.sections[0].description: У понеділок Лідія повідомила про свою роботу в музеї. У вівторок це можна передати з worked, обравши минулу точку її слів, або з works, підкресливши актуальну роботу. Перша форма не повідомляє сама по собі, чи Лідія й далі там працює. Для висновку про звільнення потрібен додатковий контекст, а не лише backshift. Так само відсутність зсуву не змінює того, хто є джерелом повідомлення.
      details[0].fragment.data.sections[0].examples[0].en: Lidiia said that she worked at the museum.
      details[0].fragment.data.sections[0].examples[0].ua: Лідія сказала, що працює в музеї.
      details[0].fragment.data.sections[0].examples[1].en: Lidiia said that she works at the museum.
      details[0].fragment.data.sections[0].examples[1].ua: Лідія сказала, що працює в музеї.

## reported-questions

status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechQuestionsTheorySeeder
slug: reported-questions
locale: uk
display_level: B1-B2
technical_block_level: B1
  category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/reported-questions
subtitle_html: <p><strong>Reported Questions</strong> — як передавати yes/no- і wh-питання без вкладеної інверсії, не гублячи учасників і заперечення.</p>
subtitle_text: Reported Questions — як передавати yes/no- і wh-питання без вкладеної інверсії, не гублячи учасників і заперечення.
  blocks[0].key: questions-hero
  blocks[0].type: hero
  blocks[0].column: header
  blocks[0].level: B1
  blocks[0].data.level: B1-B2
  blocks[0].data.intro: Reported Questions передає, що хтось запитав, а не відповідь на це питання. Для yes/no додаємо if або whether; для wh-питання зберігаємо питальне слово. Усередині reported clause немає інверсії прямого питання.
    blocks[0].data.rules[0].label: ПИТАННЯ «ЧИ»
    blocks[0].data.rules[0].color: emerald
    blocks[0].data.rules[0].text: Asked + if / whether + підмет + присудок.
    blocks[0].data.rules[0].example: <span lang="en">Marta asked if the gate was open.</span><br><span lang="uk">Марта запитала, чи відчинена хвіртка.</span>
    blocks[0].data.rules[1].label: WH-ПИТАННЯ
    blocks[0].data.rules[1].color: blue
    blocks[0].data.rules[1].text: Питальне слово лишається, а підмет стоїть перед присудком.
    blocks[0].data.rules[1].example: <span lang="en">Marta asked where the key was.</span><br><span lang="uk">Марта запитала, де ключ.</span>
    blocks[0].data.rules[2].label: ЗМІСТ НЕ СТАЄ ВІДПОВІДДЮ
    blocks[0].data.rules[2].color: amber
    blocks[0].data.rules[2].text: Питання не стверджує, що подія відбулася.
    blocks[0].data.rules[2].example: <span lang="en">Marta asked whether the parcel had arrived.</span><br><span lang="uk">Марта запитала, чи прибула посилка.</span>
  blocks[1].key: questions-forms
  blocks[1].type: forms-grid
  blocks[1].column: left
  blocks[1].data.title: 1. Як увести непряме питання
  blocks[1].data.intro: Ask можна вжити без названого адресата або з ним. Якщо текст не каже, кому поставили питання, не додавай me чи us від себе.
    blocks[1].data.items[0].label: YES / NO
    blocks[1].data.items[0].title: asked + if / whether + підмет + присудок
    blocks[1].data.items[0].subtitle: <span lang="en">Danylo asked whether the office was open.</span><br><span lang="uk">Данило запитав, чи відчинений офіс.</span>
    blocks[1].data.items[1].label: WH
    blocks[1].data.items[1].title: asked + where / why / when… + підмет + присудок
    blocks[1].data.items[1].subtitle: <span lang="en">Danylo asked when the meeting started.</span><br><span lang="uk">Данило запитав, коли починається зустріч.</span>
    blocks[1].data.items[2].label: НАЗВАНИЙ АДРЕСАТ
    blocks[1].data.items[2].title: asked + адресат + reported clause
    blocks[1].data.items[2].subtitle: <span lang="en">Danylo asked me where the exit was.</span><br><span lang="uk">Данило запитав мене, де вихід.</span>
    blocks[1].data.items[3].label: ПІДМЕТ WHO
    blocks[1].data.items[3].title: asked + who + присудок
    blocks[1].data.items[3].subtitle: <span lang="en">Danylo asked who had opened the window.</span><br><span lang="uk">Данило запитав, хто відчинив вікно.</span> Who тут уже є підметом.
  blocks[2].key: questions-comparison
  blocks[2].type: comparison-table
  blocks[2].column: left
  blocks[2].data.title: 2. Пряме питання → переказ
  blocks[2].data.intro: У цих прикладах новий оповідач передає питання пізніше й використовує backshift. Займенник визначаємо за названими учасниками.
    blocks[2].data.headers[0]: Хто питає кого; прямі слова
    blocks[2].data.headers[1]: Непряма мова і переклад
    blocks[2].data.headers[2]: Що збережено
      blocks[2].data.rows[0].cells[0]: Марія питає Олега: <span lang="en">Do you like jazz?</span><br><span lang="uk">Тобі подобається джаз?</span>
      blocks[2].data.rows[0].cells[1]: <span lang="en">Mariia asked Oleh if he liked jazz.</span><br><span lang="uk">Марія запитала Олега, чи подобається йому джаз.</span>
      blocks[2].data.rows[0].cells[2]: He — Олег; do як питальне допоміжне зникло, але час передано формою liked.
      blocks[2].data.rows[1].cells[0]: Петро питає Олену про гостей у понеділок увечері: <span lang="en">Are they coming tonight?</span><br><span lang="uk">Вони прийдуть сьогодні ввечері?</span>
      blocks[2].data.rows[1].cells[1]: <span lang="en">Petro asked Olena whether they were coming that night.</span><br><span lang="uk">Петро запитав Олену, чи вони прийдуть того вечора.</span>
      blocks[2].data.rows[1].cells[2]: Переказ відбувається у вівторок; that night — понеділковий вечір.
      blocks[2].data.rows[2].cells[0]: Ірина питає Тараса у вівторок про його ранній відхід у понеділок: <span lang="en">Why did you leave early?</span><br><span lang="uk">Чому ти пішов рано?</span>
      blocks[2].data.rows[2].cells[1]: <span lang="en">Iryna asked Taras why he had left early.</span><br><span lang="uk">Ірина запитала Тараса, чому він пішов рано.</span>
      blocks[2].data.rows[2].cells[2]: У пізнішому переказі задано had left; did не лишається перед he.
      blocks[2].data.rows[3].cells[0]: Олег питає про виконавця дії: <span lang="en">Who called you?</span><br><span lang="uk">Хто тобі телефонував?</span>
      blocks[2].data.rows[3].cells[1]: <span lang="en">Oleh asked me who had called me.</span><br><span lang="uk">Олег запитав мене, хто мені телефонував.</span>
      blocks[2].data.rows[3].cells[2]: Who — той, хто телефонував; me — адресат дзвінка й теперішній оповідач.
  blocks[3].key: questions-mistakes
  blocks[3].type: mistakes-grid
  blocks[3].column: right
  blocks[3].data.title: 3. Порядок слів і роль допоміжного
    blocks[3].data.items[0].label: БЕЗ ВКЛАДЕНОЇ ІНВЕРСІЇ
    blocks[3].data.items[0].color: rose
    blocks[3].data.items[0].title: Дієслово не переносимо перед підметом.
    blocks[3].data.items[0].wrong_en: He asked where was the station.
    blocks[3].data.items[0].wrong_ua: Задум: він запитав, де станція.
    blocks[3].data.items[0].right_en: He asked where the station was.
    blocks[3].data.items[0].right_ua: Він запитав, де станція.
    blocks[3].data.items[0].note: The station — підмет; was стоїть після нього.
    blocks[3].data.items[1].label: IF / WHETHER ДЛЯ «ЧИ»
    blocks[3].data.items[1].color: amber
    blocks[3].data.items[1].title: Не губи зв’язок yes/no-питання.
    blocks[3].data.items[1].wrong_en: She asked the door was locked.
    blocks[3].data.items[1].wrong_ua: Задум: вона запитала, чи замкнені двері.
    blocks[3].data.items[1].right_en: She asked if the door was locked.
    blocks[3].data.items[1].right_ua: Вона запитала, чи замкнені двері.
    blocks[3].data.items[1].note: Whether тут теж можливе. If означає «чи», не умовне «якщо».
    blocks[3].data.items[2].label: DO ЯК ДОПОМІЖНЕ Й ЯК ДІЯ
    blocks[3].data.items[2].color: sky
    blocks[3].data.items[2].title: Прибираємо питальне do/does/did, а не будь-яке слово do.
    blocks[3].data.items[2].wrong_en: She asked what did I do.
    blocks[3].data.items[2].wrong_ua: Задум: вона запитала, що я робив.
    blocks[3].data.items[2].right_en: She asked what I did.
    blocks[3].data.items[2].right_ua: Вона запитала, що я робив.
    blocks[3].data.items[2].note: У правильному реченні did — основне дієслово «робив». Заперечення теж треба зберегти: <span lang="en">She asked why I had not called.</span><br><span lang="uk">Вона запитала, чому я не зателефонував.</span>
  blocks[4].key: questions-summary
  blocks[4].type: summary-list
  blocks[4].column: right
  blocks[4].data.title: 4. Коротке правило
    blocks[4].data.items[0]: Yes/no-питання вводимо через if або whether. Wh-слово зберігаємо. Після них — порядок твердження, але зміст лишається питанням.
    blocks[4].data.items[1]: Who може бути підметом без нового he/she або об’єктом поряд з окремим підметом. Перевіряй, хто виконує дію.
    blocks[4].data.items[2]: Питальні do/does/did не переносимо всередину reported clause. Час і заперечення передаємо потрібними формами, а не викидаємо.
    blocks[4].data.items[3]: Крапка чи знак питання залежать від цілого речення. Переказ He asked… зазвичай закінчується крапкою; зовнішнє Did he ask…? — питанням.
  blocks[5].key: questions-navigation
  blocks[5].type: navigation-chips
  blocks[5].column: footer
  blocks[5].data.title: Сторінки цього розділу
    blocks[5].data.items[0].label: Reported Statements
    blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
    blocks[5].data.items[1].label: Reported Questions
    blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
    blocks[5].data.items[2].label: Reported Commands and Requests
    blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
    blocks[5].data.items[3].label: Backshift of Tenses
    blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
    blocks[5].data.items[4].label: Time and Place Changes
    blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
    blocks[5].data.items[5].label: Say / Tell / Ask
    blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
  blocks[6].key: questions-context
  blocks[6].type: usage-panels
  blocks[6].column: left
  blocks[6].level: B1
  blocks[6].data.title: 5. Контекст: час, намір і пунктуація
    blocks[6].data.sections[0].label: ЧАС НЕ ЗСУВАЄТЬСЯ МЕХАНІЧНО
    blocks[6].data.sections[0].color: emerald
    blocks[6].data.sections[0].description: Якщо питання щойно поставили й воно досі актуальне, можна залишити теперішню форму. Інша минула перспектива теж можлива. Tonight змінюється на that night, коли це вже не вечір нового оповідача.
      blocks[6].data.sections[0].examples[0].en: Oksana asked where you live.
      blocks[6].data.sections[0].examples[0].ua: Оксана запитала, де ти живеш.
    blocks[6].data.sections[0].note: Оповідач звертається до того самого you; місце проживання лишається актуальним.
    blocks[6].data.sections[1].label: IF І WHETHER: ДЕ ВИБІР ВІЛЬНИЙ
    blocks[6].data.sections[1].color: blue
    blocks[6].data.sections[1].description: У простому непрямому yes/no-питанні обидва варіанти можливі. Перед to-інфінітивом потрібне whether. У сполуці whether or not слова or not можуть стояти одразу після whether; після if вони стоять наприкінці відповідної частини.
      blocks[6].data.sections[1].examples[0].en: He asked if the shop was open. / He asked whether the shop was open.
      blocks[6].data.sections[1].examples[0].ua: Він запитав, чи відчинений магазин.
      blocks[6].data.sections[1].examples[1].en: She wondered whether to call.
      blocks[6].data.sections[1].examples[1].ua: Вона міркувала, чи телефонувати.
    blocks[6].data.sections[2].label: ПИТАЄМО ЗАРАЗ ЧИ ПЕРЕПОВІДАЄМО
    blocks[6].data.sections[2].color: amber
    blocks[6].data.sections[2].description: Ввічливе непряме питання до співрозмовника — не те саме, що розповідь про минуле питання. В обох вкладених частинах немає інверсії.
      blocks[6].data.sections[2].examples[0].en: Could you tell me where the office is?
      blocks[6].data.sections[2].examples[0].ua: Скажіть, будь ласка, де офіс?
      blocks[6].data.sections[2].examples[1].en: He asked where the office was.
      blocks[6].data.sections[2].examples[1].ua: Він запитав, де офіс.
      blocks[6].data.sections[2].examples[2].en: Did he ask where the office was?
      blocks[6].data.sections[2].examples[2].ua: Він запитав, де офіс?
    blocks[6].data.sections[3].label: ПИТАННЯ МОЖЕ БУТИ ПРОХАННЯМ
    blocks[6].data.sections[3].color: slate
    blocks[6].data.sections[3].description: Репліка з Could you…? може з’ясовувати можливість або ввічливо просити про дію. Обирай переказ за наміром і ситуацією; форма asked if… могла б також передати непряме прохання, тому її не оголошуємо завжди неправильною.
      blocks[6].data.sections[3].examples[0].en: Could you carry this box?
      blocks[6].data.sections[3].examples[0].ua: Ти міг би понести цю коробку?
  blocks[7].key: questions-practice
  blocks[7].type: practice-set
  blocks[7].column: left
  blocks[7].level: B1
  blocks[7].data.title: Практика
  blocks[7].data.select_title: Доповни задану модель
  blocks[7].data.select_intro: Збережи учасників і прямо задану форму повідомлення.
    blocks[7].data.selects[0].id: m60-questions-form-embedded-order
    blocks[7].data.selects[0].label: Ірина запитала, о котрій відчиняється музей. У непрямому питанні збережи звичайний порядок підмет + присудок.<br><span lang="en">Iryna asked what time ___.</span><br><span lang="uk">Ірина запитала, о котрій відчиняється музей.</span>
      blocks[7].data.selects[0].options[0]: did the museum open
      blocks[7].data.selects[0].options[1]: the museum opened
      blocks[7].data.selects[0].options[2]: opened the museum
    blocks[7].data.selects[0].answer: the museum opened
      blocks[7].data.selects[0].accepted[0]: the museum opened
    blocks[7].data.selects[0].feedback.did the museum open: У reported clause не переносимо did перед підметом: <span lang="en">Iryna asked what time the museum opened.</span><br><span lang="uk">Ірина запитала, о котрій відчиняється музей.</span>
    blocks[7].data.selects[0].feedback.the museum opened: <span lang="en">Iryna asked what time the museum opened.</span><br><span lang="uk">Ірина запитала, о котрій відчиняється музей.</span> Підмет the museum стоїть перед opened.
    blocks[7].data.selects[0].feedback.opened the museum: The museum — підмет, тому не ставимо opened перед ним: <span lang="en">Iryna asked what time the museum opened.</span><br><span lang="uk">Ірина запитала, о котрій відчиняється музей.</span>
    blocks[7].data.selects[1].id: m60-questions-form-subject-who
    blocks[7].data.selects[1].label: Олег запитав: «Хто забрав папку?» Передай питання із заданим Past Perfect. Who — підмет, нового he не додаємо.<br><span lang="en">Oleh asked who ___ the folder.</span><br><span lang="uk">Олег запитав, хто забрав папку.</span>
      blocks[7].data.selects[1].options[0]: had taken
      blocks[7].data.selects[1].options[1]: he had taken
      blocks[7].data.selects[1].options[2]: had he taken
    blocks[7].data.selects[1].answer: had taken
      blocks[7].data.selects[1].accepted[0]: had taken
    blocks[7].data.selects[1].feedback.had taken: <span lang="en">Oleh asked who had taken the folder.</span><br><span lang="uk">Олег запитав, хто забрав папку.</span> Who саме виконує роль підмета.
    blocks[7].data.selects[1].feedback.he had taken: Who вже є підметом; he тут дублює цю роль: <span lang="en">Oleh asked who had taken the folder.</span><br><span lang="uk">Олег запитав, хто забрав папку.</span>
    blocks[7].data.selects[1].feedback.had he taken: Інверсія had he не потрібна, і окремого he немає в умові: <span lang="en">Oleh asked who had taken the folder.</span><br><span lang="uk">Олег запитав, хто забрав папку.</span>
  blocks[7].data.choice_title: Обери точний зміст
  blocks[7].data.choice_intro: Не додавай фактів, яких повідомлення не стверджує. Переклад англійського стимулу відкривається після перевірки.
    blocks[7].data.choices[0].id: m60-questions-meaning-open-answer
    blocks[7].data.choices[0].label: У повідомленні he — Назар. Який висновок прямо випливає з повідомлення? Відповідь на питання не наведено.
      blocks[7].data.choices[0].options[0]: Назар користувався запасним ключем.
      blocks[7].data.choices[0].options[1]: Назар не користувався запасним ключем.
      blocks[7].data.choices[0].options[2]: Олена запитала Назара, чи користувався він запасним ключем.
    blocks[7].data.choices[0].answer: Олена запитала Назара, чи користувався він запасним ключем.
      blocks[7].data.choices[0].accepted[0]: Олена запитала Назара, чи користувався він запасним ключем.
    blocks[7].data.choices[0].feedback.назар користувався запасним ключем.: <span lang="en">Olena asked Nazar whether he had used the spare key.</span><br><span lang="uk">Олена запитала Назара, чи користувався він запасним ключем.</span> Це питання, не підтвердження використання.
    blocks[7].data.choices[0].feedback.назар не користувався запасним ключем.: <span lang="en">Olena asked Nazar whether he had used the spare key.</span><br><span lang="uk">Олена запитала Назара, чи користувався він запасним ключем.</span> Заперечної відповіді теж не наведено.
    blocks[7].data.choices[0].feedback.олена запитала назара, чи користувався він запасним ключем.: <span lang="en">Olena asked Nazar whether he had used the spare key.</span><br><span lang="uk">Олена запитала Назара, чи користувався він запасним ключем.</span> Whether означає «чи»; результат розмови невідомий.
    blocks[7].data.choices[0].prompt: <span lang="en">Olena asked Nazar whether he had used the spare key.</span>
    blocks[7].data.choices[1].id: m60-questions-meaning-object-who
    blocks[7].data.choices[1].label: Передано питання до Олега; he — Олег. Які ролі мають he і who в тому питанні?
      blocks[7].data.choices[1].options[0]: Питали, кому телефонував Олег, не хто телефонував йому.
      blocks[7].data.choices[1].options[1]: Питали, хто телефонував Олегові, не кому він телефонував.
      blocks[7].data.choices[1].options[2]: Питали, кому телефонувала Ірина, не Олег.
    blocks[7].data.choices[1].answer: Питали, кому телефонував Олег, не хто телефонував йому.
      blocks[7].data.choices[1].accepted[0]: Питали, кому телефонував Олег, не хто телефонував йому.
    blocks[7].data.choices[1].feedback.питали, кому телефонував олег, не хто телефонував йому.: <span lang="en">Iryna asked Oleh who he had called.</span><br><span lang="uk">Ірина запитала Олега, кому він телефонував.</span> He — Олег; who тут об’єкт call, а не той, хто телефонував.
    blocks[7].data.choices[1].feedback.питали, хто телефонував олегові, не кому він телефонував.: <span lang="en">Iryna asked Oleh who he had called.</span><br><span lang="uk">Ірина запитала Олега, кому він телефонував.</span> Це не питання «хто телефонував Олегові»: підмет reported clause — he.
    blocks[7].data.choices[1].feedback.питали, кому телефонувала ірина, не олег.: <span lang="en">Iryna asked Oleh who he had called.</span><br><span lang="uk">Ірина запитала Олега, кому він телефонував.</span> Iryna — авторка питання, а не підмет had called.
    blocks[7].data.choices[1].prompt: <span lang="en">Iryna asked Oleh who he had called.</span>
  blocks[7].data.input_title: Побудуй повне речення
  blocks[7].data.input_intro: Введи відповідь або склади її з усіх токенів. Для ручного вводу дозволені лише рівнозначні варіанти, описані в умові.
    blocks[7].data.inputs[0].id: m60-questions-build-if-whether
    blocks[7].data.inputs[0].before: Olena / if / asked / Taras / gate. / the / locked / had
    blocks[7].data.inputs[0].after: Тарас запитав Олену: «Ти замкнула хвіртку?» Інша людина переповідає це пізніше: «Тарас запитав, чи Олена замкнула хвіртку». Почни з Taras asked, використай заданий Past Perfect: had locked. Для «чи» приймаються if або whether; закінчи the gate.
    blocks[7].data.inputs[0].answer: Taras asked if Olena had locked the gate.
      blocks[7].data.inputs[0].accepted[0]: Taras asked if Olena had locked the gate.
      blocks[7].data.inputs[0].accepted[1]: Taras asked whether Olena had locked the gate.
    blocks[7].data.inputs[0].feedback.taras asked if olena had locked the gate.: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span> If і whether тут рівнозначні. Порядок після них: Olena + had locked; відповіді Олени ми не додаємо.
    blocks[7].data.inputs[0].feedback.taras asked if olena had locked the gate: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span> If і whether тут рівнозначні. Порядок після них: Olena + had locked; відповіді Олени ми не додаємо.
    blocks[7].data.inputs[0].feedback.taras asked whether olena had locked the gate.: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span> If і whether тут рівнозначні. Порядок після них: Olena + had locked; відповіді Олени ми не додаємо.
    blocks[7].data.inputs[0].feedback.taras asked whether olena had locked the gate: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span> If і whether тут рівнозначні. Порядок після них: Olena + had locked; відповіді Олени ми не додаємо.
    blocks[7].data.inputs[0].feedback.taras asked if had olena locked the gate.: Це інверсія прямого питання, а потрібен порядок Olena had locked: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span>
    blocks[7].data.inputs[0].feedback.taras asked if had olena locked the gate: Це інверсія прямого питання, а потрібен порядок Olena had locked: <span lang="en">Taras asked if Olena had locked the gate.</span><br><span lang="uk">Тарас запитав, чи Олена замкнула хвіртку.</span>
    blocks[7].data.inputs[1].id: m60-questions-build-main-question
    blocks[7].data.inputs[1].before: where / Petro / worked? / Did / Iryna / ask
    blocks[7].data.inputs[1].after: Тепер саме ти ставиш питання: «Ірина запитала, де працює Петро?» Почни з Did Iryna ask, далі where. У вкладеній частині використай заданий Past Simple: Petro worked; наприкінці постав знак питання.
    blocks[7].data.inputs[1].answer: Did Iryna ask where Petro worked?
      blocks[7].data.inputs[1].accepted[0]: Did Iryna ask where Petro worked?
    blocks[7].data.inputs[1].feedback.did iryna ask where petro worked?: <span lang="en">Did Iryna ask where Petro worked?</span><br><span lang="uk">Ірина запитала, де працює Петро?</span> Зовнішнє питання має Did Iryna ask і знак питання. Усередині лишається where Petro worked, не where did Petro work.
    blocks[7].data.inputs[1].feedback.did iryna ask where petro worked: <span lang="en">Did Iryna ask where Petro worked?</span><br><span lang="uk">Ірина запитала, де працює Петро?</span> Зовнішнє питання має Did Iryna ask і знак питання. Усередині лишається where Petro worked, не where did Petro work.
    blocks[7].data.inputs[1].feedback.did iryna ask where did petro work?: Інверсія потрібна тільки в зовнішньому питанні: <span lang="en">Did Iryna ask where Petro worked?</span><br><span lang="uk">Ірина запитала, де працює Петро?</span>
    blocks[7].data.inputs[1].feedback.did iryna ask where did petro work: Інверсія потрібна тільки в зовнішньому питанні: <span lang="en">Did Iryna ask where Petro worked?</span><br><span lang="uk">Ірина запитала, де працює Петро?</span>
  blocks[7].data.linked_practice.source: theory_links
    blocks[7].data.linked_practice.question_types[0]: 4
    blocks[7].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechQuestionsAllLevelsLessonSeeder
    blocks[7].data.linked_practice.seeder_classes[1]: Database\Seeders\V3\Polyglot\PolyglotReportedQuestionsLessonSeeder
  blocks[7].data.linked_practice.title: Вправа 4. Побудуй речення
  blocks[7].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова. Збережи мовця, адресата й зміст.
  blocks[7].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
  details[0].key: questions-ability-or-request
  details[0].title: Ті самі слова, різний намір
  details[0].owner_block_key: questions-context
  details[0].owner_pointer: /sections/3
  details[0].fragment.type: usage-panels
    details[0].fragment.data.sections[0].label: Ті самі слова, різний намір
    details[0].fragment.data.sections[0].color: slate
    details[0].fragment.data.sections[0].description: Під час перевірки фізичних можливостей інструктор справді питає, чи людина може підняти ящик: доречний переказ питання про можливість. У коридорі з вільними руками ці самі слова можуть бути проханням допомогти; object + to прямо передає бажану дію. Обидва перекази граматичні, але описують ситуацію з різним фокусом. Не роби висновок про намір лише зі слова could й не вигадуй, що адресат виконав прохання.
      details[0].fragment.data.sections[0].examples[0].en: The instructor asked me if I could lift the box.
      details[0].fragment.data.sections[0].examples[0].ua: Інструктор запитав мене, чи можу я підняти ящик.
      details[0].fragment.data.sections[0].examples[1].en: The instructor asked me to lift the box.
      details[0].fragment.data.sections[0].examples[1].ua: Інструктор попросив мене підняти ящик.

## reported-commands-and-requests

status: frozen
identity: Database\Seeders\Page_V3\ReportedSpeech\ReportedSpeechCommandsRequestsTheorySeeder
slug: reported-commands-and-requests
locale: uk
display_level: B1-B2
technical_block_level: B1
  category_ancestry[0]: reported-speech
local_url: http://gramlyze.loc/theory/reported-speech/reported-commands-and-requests
subtitle_html: <p><strong>Reported Commands and Requests</strong> — прохання, вказівки й поради через object + to / not to зі збереженням наміру та ролей.</p>
subtitle_text: Reported Commands and Requests — прохання, вказівки й поради через object + to / not to зі збереженням наміру та ролей.
  blocks[0].key: commands-hero
  blocks[0].type: hero
  blocks[0].column: header
  blocks[0].level: B1
  blocks[0].data.level: B1-B2
  blocks[0].data.intro: Reported Commands and Requests передає, що одна людина попросила, порадила або веліла зробити іншій. У базовій моделі називаємо адресата й дію через to; для негативної вказівки — not to. Дієслово обираємо за наміром і контекстом, не лише за словом please.
    blocks[0].data.rules[0].label: АДРЕСАТ І ДІЯ
    blocks[0].data.rules[0].color: emerald
    blocks[0].data.rules[0].text: Reporting verb + object + to + базова форма.
    blocks[0].data.rules[0].example: <span lang="en">The teacher told us to open our books.</span><br><span lang="uk">Учитель сказав нам відкрити книжки.</span>
    blocks[0].data.rules[1].label: НЕГАТИВНА ВКАЗІВКА
    blocks[0].data.rules[1].color: rose
    blocks[0].data.rules[1].text: Reporting verb + object + not to + базова форма.
    blocks[0].data.rules[1].example: <span lang="en">The teacher told us not to write yet.</span><br><span lang="uk">Учитель сказав нам поки не писати.</span>
    blocks[0].data.rules[2].label: СИЛА Й НАМІР
    blocks[0].data.rules[2].color: blue
    blocks[0].data.rules[2].text: Ask — прохання; advise — порада; order — наказ.
    blocks[0].data.rules[2].example: <span lang="en">The guide asked us to wait.</span><br><span lang="uk">Гід попросив нас зачекати.</span>
  blocks[1].key: commands-forms
  blocks[1].type: forms-grid
  blocks[1].column: left
  blocks[1].data.title: 1. Хто просить і хто має виконати дію
  blocks[1].data.intro: У цій моделі підмет називає того, хто дає вказівку або просить. Object — адресат і передбачуваний виконавець to-дії. Після to потрібна базова форма, не минуле закінчення.
    blocks[1].data.items[0].label: СТВЕРДЖУВАЛЬНА ВКАЗІВКА
    blocks[1].data.items[0].title: told / asked / ordered + object + to + дієслово
    blocks[1].data.items[0].subtitle: <span lang="en">Sofiia asked Denys to close the window.</span><br><span lang="uk">Софія попросила Дениса зачинити вікно.</span>
    blocks[1].data.items[1].label: НЕГАТИВНА ВКАЗІВКА
    blocks[1].data.items[1].title: told / asked / warned + object + not to + дієслово
    blocks[1].data.items[1].subtitle: <span lang="en">Sofiia asked Denys not to close the window.</span><br><span lang="uk">Софія попросила Дениса не зачиняти вікно.</span>
    blocks[1].data.items[2].label: ОБ’ЄКТНІ ЗАЙМЕННИКИ
    blocks[1].data.items[2].title: me / you / him / her / us / them
    blocks[1].data.items[2].subtitle: <span lang="en">Sofiia told him to call her.</span><br><span lang="uk">Софія сказала йому зателефонувати їй.</span> У цьому контексті him — Денис, her — Софія.
    blocks[1].data.items[3].label: ЗОВНІШНЄ ПИТАННЯ
    blocks[1].data.items[3].title: Did + підмет + ask / tell + object + to…?
    blocks[1].data.items[3].subtitle: <span lang="en">Did Sofiia ask Denys to wait?</span><br><span lang="uk">Софія попросила Дениса зачекати?</span> Інверсія зовні; to wait лишається без зміни.
  blocks[2].key: commands-comparison
  blocks[2].type: comparison-table
  blocks[2].column: left
  blocks[2].data.title: 2. Обирай дієслово за ситуацією
  blocks[2].data.intro: Однакова наказова форма прямої репліки може бути вказівкою, проханням або застереженням. Намір і учасників у цих рядках задано явно.
    blocks[2].data.headers[0]: Ситуація й прямі слова
    blocks[2].data.headers[1]: Непряма мова і переклад
    blocks[2].data.headers[2]: Намір і ролі
      blocks[2].data.rows[0].cells[0]: Керівник дає мені звичайну робочу вказівку: <span lang="en">Send the file today.</span><br><span lang="uk">Надішли файл сьогодні.</span>
      blocks[2].data.rows[0].cells[1]: <span lang="en">The manager told me to send the file that day.</span><br><span lang="uk">Керівник сказав мені надіслати файл того дня.</span>
      blocks[2].data.rows[0].cells[2]: Told — вказівка. Я переповідаю її пізніше, тому today → that day.
      blocks[2].data.rows[1].cells[0]: Олена просить Петра допомогти з її сумкою: <span lang="en">Please help me with my bag.</span><br><span lang="uk">Будь ласка, допоможи мені з моєю сумкою.</span>
      blocks[2].data.rows[1].cells[1]: <span lang="en">Olena asked Petro to help her with her bag.</span><br><span lang="uk">Олена попросила Петра допомогти їй з її сумкою.</span>
      blocks[2].data.rows[1].cells[2]: Допомогти має Петро; her і her bag стосуються Олени. Це переказ іншої людини, а не підтвердження виконання.
      blocks[2].data.rows[2].cells[0]: Інженер попереджає нашу групу про оголений дріт: <span lang="en">Do not touch that wire.</span><br><span lang="uk">Не торкайтеся того дроту.</span>
      blocks[2].data.rows[2].cells[1]: <span lang="en">The engineer warned us not to touch that wire.</span><br><span lang="uk">Інженер застеріг нас не торкатися того дроту.</span>
      blocks[2].data.rows[2].cells[2]: Warned відповідає названій небезпеці. Us — та сама група.
      blocks[2].data.rows[3].cells[0]: Друг дає мені пораду через утому: <span lang="en">You should take a break.</span><br><span lang="uk">Тобі варто зробити перерву.</span>
      blocks[2].data.rows[3].cells[1]: <span lang="en">My friend advised me to take a break.</span><br><span lang="uk">Мій друг порадив мені зробити перерву.</span>
      blocks[2].data.rows[3].cells[2]: Порада, не сильний наказ і не повідомлення, що я вже відпочив.
  blocks[3].key: commands-mistakes
  blocks[3].type: mistakes-grid
  blocks[3].column: right
  blocks[3].data.title: 3. Не губи адресата, to й not
    blocks[3].data.items[0].label: АДРЕСАТ У TELL
    blocks[3].data.items[0].color: rose
    blocks[3].data.items[0].title: У моделі tell someone to do не пропускай someone.
    blocks[3].data.items[0].wrong_en: He told to sit down.
    blocks[3].data.items[0].wrong_ua: Задум: він сказав мені сісти.
    blocks[3].data.items[0].right_en: He told me to sit down.
    blocks[3].data.items[0].right_ua: Він сказав мені сісти.
    blocks[3].data.items[0].note: To sit — інфінітив, не наказова форма. Me тут задане змістом; якщо адресат невідомий, не вигадуй його.
    blocks[3].data.items[1].label: БАЗОВА ФОРМА ПІСЛЯ TO
    blocks[3].data.items[1].color: amber
    blocks[3].data.items[1].title: Минуле told не робить сам інфінітив минулим.
    blocks[3].data.items[1].wrong_en: She told us to waited.
    blocks[3].data.items[1].wrong_ua: Задум: вона сказала нам зачекати.
    blocks[3].data.items[1].right_en: She told us to wait.
    blocks[3].data.items[1].right_ua: Вона сказала нам зачекати.
    blocks[3].data.items[1].note: Told виражає минуле повідомлення; to wait лишається інфінітивом.
    blocks[3].data.items[2].label: МІСЦЕ ЗАПЕРЕЧЕННЯ
    blocks[3].data.items[2].color: sky
    blocks[3].data.items[2].title: Для негативної вказівки в цій моделі потрібне not to.
    blocks[3].data.items[2].wrong_en: They told us not be late.
    blocks[3].data.items[2].wrong_ua: Задум: вони сказали нам не запізнюватися.
    blocks[3].data.items[2].right_en: They told us not to be late.
    blocks[3].data.items[2].right_ua: Вони сказали нам не запізнюватися.
    blocks[3].data.items[2].note: Не замінюй це на didn’t tell us to be late: там заперечено факт вказівки, а не задано не запізнюватися.
  blocks[4].key: commands-summary
  blocks[4].type: summary-list
  blocks[4].column: right
  blocks[4].data.title: 4. Коротке правило
    blocks[4].data.items[0]: Базова модель: reporting verb + адресат + to + дія; негативна вказівка — адресат + not to + дія.
    blocks[4].data.items[1]: Назви того самого адресата й виконавця; займенники my, me, you залежать від того, хто зараз переповідає слова.
    blocks[4].data.items[2]: Tell, ask, order, advise і warn не рівнозначні за наміром. Please — підказка ввічливості, а не автоматичний вибір одного reporting verb.
    blocks[4].data.items[3]: Вказівка, прохання й порада не доводять, що їх виконали. Told us not to… не те саме, що did not tell us to… .
  blocks[5].key: commands-navigation
  blocks[5].type: navigation-chips
  blocks[5].column: footer
  blocks[5].data.title: Сторінки цього розділу
    blocks[5].data.items[0].label: Reported Statements
    blocks[5].data.items[0].url: /theory/reported-speech/reported-statements
    blocks[5].data.items[1].label: Reported Questions
    blocks[5].data.items[1].url: /theory/reported-speech/reported-questions
    blocks[5].data.items[2].label: Reported Commands and Requests
    blocks[5].data.items[2].url: /theory/reported-speech/reported-commands-and-requests
    blocks[5].data.items[3].label: Backshift of Tenses
    blocks[5].data.items[3].url: /theory/reported-speech/backshift-of-tenses
    blocks[5].data.items[4].label: Time and Place Changes
    blocks[5].data.items[4].url: /theory/reported-speech/time-and-place-changes
    blocks[5].data.items[5].label: Say / Tell / Ask
    blocks[5].data.items[5].url: /theory/reported-speech/say-tell-ask
  blocks[6].key: commands-context
  blocks[6].type: usage-panels
  blocks[6].column: left
  blocks[6].level: B1
  blocks[6].data.title: 5. Близькі моделі з різними ролями
    blocks[6].data.sections[0].label: ASK SOMEONE TO / ASK TO / ASK IF
    blocks[6].data.sections[0].color: emerald
    blocks[6].data.sections[0].description: Object перед to змінює, хто має діяти. У питанні через if з’ясовують можливість; за контекстом це може бути й непряме прохання. Не вважай ці три моделі взаємозамінними лише через ask.
      blocks[6].data.sections[0].examples[0].en: Olena asked me to leave.
      blocks[6].data.sections[0].examples[0].ua: Олена попросила мене піти.
      blocks[6].data.sections[0].examples[1].en: Olena asked to leave.
      blocks[6].data.sections[0].examples[1].ua: Олена попросила дозволу піти.
      blocks[6].data.sections[0].examples[2].en: Olena asked if I could leave.
      blocks[6].data.sections[0].examples[2].ua: Олена запитала, чи можу я піти.
    blocks[6].data.sections[1].label: НЕ КОЖНЕ NOT TO — ЗАБОРОНА
    blocks[6].data.sections[1].color: blue
    blocks[6].data.sections[1].description: Слова підтримки можуть мати таку саму негативну форму, не перетворюючись на сувору заборону. Характер репліки визначає ситуація.
      blocks[6].data.sections[1].examples[0].en: Mum told me not to worry.
      blocks[6].data.sections[1].examples[0].ua: Мама сказала мені не хвилюватися.
    blocks[6].data.sections[1].note: У цьому контексті мама заспокоює, а не карає за хвилювання.
    blocks[6].data.sections[2].label: ПРОХАННЯ, ПРОПОЗИЦІЯ ДОПОМОГИ Й ІДЕЯ
    blocks[6].data.sections[2].color: amber
    blocks[6].data.sections[2].description: Asked me to називає бажану дію адресата; offered to — дію того, хто пропонує; suggested може вводити -ing або that-clause. Не поширюй модель object + to на всі дієслова повідомлення.
      blocks[6].data.sections[2].examples[0].en: He asked me to drive.
      blocks[6].data.sections[2].examples[0].ua: Він попросив мене повести машину.
      blocks[6].data.sections[2].examples[1].en: He offered to drive.
      blocks[6].data.sections[2].examples[1].ua: Він запропонував сам повести машину.
      blocks[6].data.sections[2].examples[2].en: He suggested taking a taxi.
      blocks[6].data.sections[2].examples[2].ua: Він запропонував узяти таксі.
    blocks[6].data.sections[2].note: Цей урок тренує object + to. Інші that-моделі теж бувають граматичні; навіть формальне ask that існує, але не є цільовою моделлю цих базових вправ.
  blocks[7].key: commands-practice
  blocks[7].type: practice-set
  blocks[7].column: left
  blocks[7].level: B1
  blocks[7].data.title: Практика
  blocks[7].data.select_title: Доповни задану модель
  blocks[7].data.select_intro: Збережи учасників і прямо задану форму повідомлення.
    blocks[7].data.selects[0].id: m60-commands-form-negative
    blocks[7].data.selects[0].label: Охоронець наказав нам не входити. Доповни саме object + not to + базова форма.<br><span lang="en">The guard ordered us ___ enter.</span><br><span lang="uk">Охоронець наказав нам не входити.</span>
      blocks[7].data.selects[0].options[0]: to not to
      blocks[7].data.selects[0].options[1]: not to
      blocks[7].data.selects[0].options[2]: not
    blocks[7].data.selects[0].answer: not to
      blocks[7].data.selects[0].accepted[0]: not to
    blocks[7].data.selects[0].feedback.to not to: У заданій моделі одне to після not: <span lang="en">The guard ordered us not to enter.</span><br><span lang="uk">Охоронець наказав нам не входити.</span>
    blocks[7].data.selects[0].feedback.not to: <span lang="en">The guard ordered us not to enter.</span><br><span lang="uk">Охоронець наказав нам не входити.</span> Us — адресати, які не мають входити.
    blocks[7].data.selects[0].feedback.not: Для інфінітива enter у цій моделі потрібне to: <span lang="en">The guard ordered us not to enter.</span><br><span lang="uk">Охоронець наказав нам не входити.</span>
    blocks[7].data.selects[1].id: m60-commands-form-object
    blocks[7].data.selects[1].label: Директорка дала Андрієві вказівку зачекати. Доповни модель told + адресат + to wait.<br><span lang="en">The director told ___ to wait.</span><br><span lang="uk">Директорка сказала Андрієві зачекати.</span>
      blocks[7].data.selects[1].options[0]: to Andrii
      blocks[7].data.selects[1].options[1]: Andrii
      blocks[7].data.selects[1].options[2]: that Andrii
    blocks[7].data.selects[1].answer: Andrii
      blocks[7].data.selects[1].accepted[0]: Andrii
    blocks[7].data.selects[1].feedback.to andrii: У цій моделі адресат без прийменника to; to стоїть перед дією: <span lang="en">The director told Andrii to wait.</span><br><span lang="uk">Директорка сказала Андрієві зачекати.</span>
    blocks[7].data.selects[1].feedback.andrii: <span lang="en">The director told Andrii to wait.</span><br><span lang="uk">Директорка сказала Андрієві зачекати.</span> Andrii — адресат і передбачуваний виконавець wait.
    blocks[7].data.selects[1].feedback.that andrii: Умова вимагає object + to, не that-clause: <span lang="en">The director told Andrii to wait.</span><br><span lang="uk">Директорка сказала Андрієві зачекати.</span>
  blocks[7].data.choice_title: Обери точний зміст
  blocks[7].data.choice_intro: Не додавай фактів, яких повідомлення не стверджує. Переклад англійського стимулу відкривається після перевірки.
    blocks[7].data.choices[0].id: m60-commands-meaning-requester
    blocks[7].data.choices[0].label: Ірина звернулася до керівника. Хто просив дозволу вийти?
      blocks[7].data.choices[0].options[0]: Ірина просила дозволу вийти сама.
      blocks[7].data.choices[0].options[1]: Ірина попросила керівника вийти.
      blocks[7].data.choices[0].options[2]: Керівник наказав Ірині вийти.
    blocks[7].data.choices[0].answer: Ірина просила дозволу вийти сама.
      blocks[7].data.choices[0].accepted[0]: Ірина просила дозволу вийти сама.
    blocks[7].data.choices[0].feedback.ірина просила дозволу вийти сама.: <span lang="en">Iryna asked to leave early.</span><br><span lang="uk">Ірина попросила дозволу піти раніше.</span> Без object перед to leave виконавиця цієї дії — Ірина.
    blocks[7].data.choices[0].feedback.ірина попросила керівника вийти.: <span lang="en">Iryna asked to leave early.</span><br><span lang="uk">Ірина попросила дозволу піти раніше.</span> Тут немає object, який називав би іншу людину виконавцем leave.
    blocks[7].data.choices[0].feedback.керівник наказав ірині вийти.: <span lang="en">Iryna asked to leave early.</span><br><span lang="uk">Ірина попросила дозволу піти раніше.</span> Суб’єкт asked — Ірина, не керівник; наказу немає.
    blocks[7].data.choices[0].prompt: <span lang="en">Iryna asked to leave early.</span>
    blocks[7].data.choices[1].id: m60-commands-meaning-no-compliance
    blocks[7].data.choices[1].label: Що речення повідомляє напевно? Про подальші дії Павла нічого не сказано.
      blocks[7].data.choices[1].options[0]: Павло зупинився відпочити.
      blocks[7].data.choices[1].options[1]: Павлові порадили зробити перерву.
      blocks[7].data.choices[1].options[2]: Павлові заборонили працювати назавжди.
    blocks[7].data.choices[1].answer: Павлові порадили зробити перерву.
      blocks[7].data.choices[1].accepted[0]: Павлові порадили зробити перерву.
    blocks[7].data.choices[1].feedback.павло зупинився відпочити.: <span lang="en">The nurse advised Pavlo to take a break.</span><br><span lang="uk">Медсестра порадила Павлові зробити перерву.</span> Порада не доводить, що він її виконав.
    blocks[7].data.choices[1].feedback.павлові порадили зробити перерву.: <span lang="en">The nurse advised Pavlo to take a break.</span><br><span lang="uk">Медсестра порадила Павлові зробити перерву.</span> Advised передає пораду, не факт виконання.
    blocks[7].data.choices[1].feedback.павлові заборонили працювати назавжди.: <span lang="en">The nurse advised Pavlo to take a break.</span><br><span lang="uk">Медсестра порадила Павлові зробити перерву.</span> Не додавай заборони й назавжди: цих значень у повідомленні немає.
    blocks[7].data.choices[1].prompt: <span lang="en">The nurse advised Pavlo to take a break.</span>
  blocks[7].data.input_title: Побудуй повне речення
  blocks[7].data.input_intro: Введи відповідь або склади її з усіх токенів. Для ручного вводу дозволені лише рівнозначні варіанти, описані в умові.
    blocks[7].data.inputs[0].id: m60-commands-build-request
    blocks[7].data.inputs[0].before: box. / the / carry / to / Oksana / asked / Marta
    blocks[7].data.inputs[0].after: Марта ввічливо попросила Оксану: «Понеси цю коробку, будь ласка». Передай: «Марта попросила Оксану понести коробку». Почни з Marta asked, використай саме модель asked + Oksana + to carry; закінчи the box. У цьому переказі назви адресатку на ім’я, не займенником.
    blocks[7].data.inputs[0].answer: Marta asked Oksana to carry the box.
      blocks[7].data.inputs[0].accepted[0]: Marta asked Oksana to carry the box.
    blocks[7].data.inputs[0].feedback.marta asked oksana to carry the box.: <span lang="en">Marta asked Oksana to carry the box.</span><br><span lang="uk">Марта попросила Оксану понести коробку.</span> Марта просить; Оксана — адресатка й та, хто має нести. Виконання прохання не стверджується.
    blocks[7].data.inputs[0].feedback.marta asked oksana to carry the box: <span lang="en">Marta asked Oksana to carry the box.</span><br><span lang="uk">Марта попросила Оксану понести коробку.</span> Марта просить; Оксана — адресатка й та, хто має нести. Виконання прохання не стверджується.
    blocks[7].data.inputs[0].feedback.marta asked to carry the box.: Без Oksana цей варіант робить виконавицею carry саму Марту. Потрібно: <span lang="en">Marta asked Oksana to carry the box.</span><br><span lang="uk">Марта попросила Оксану понести коробку.</span>
    blocks[7].data.inputs[0].feedback.marta asked to carry the box: Без Oksana цей варіант робить виконавицею carry саму Марту. Потрібно: <span lang="en">Marta asked Oksana to carry the box.</span><br><span lang="uk">Марта попросила Оксану понести коробку.</span>
    blocks[7].data.inputs[1].id: m60-commands-build-negative
    blocks[7].data.inputs[1].before: coach / not / to / The / us / run / door. / told / to / the
    blocks[7].data.inputs[1].after: Тренер звернувся до нашої групи: «Не біжіть до дверей». Ми передаємо його вказівку: «Тренер сказав нам не бігти до дверей». Почни з The coach told us, використай модель not to run, закінчи to the door. Не заперечуй told.
    blocks[7].data.inputs[1].answer: The coach told us not to run to the door.
      blocks[7].data.inputs[1].accepted[0]: The coach told us not to run to the door.
    blocks[7].data.inputs[1].feedback.the coach told us not to run to the door.: <span lang="en">The coach told us not to run to the door.</span><br><span lang="uk">Тренер сказав нам не бігти до дверей.</span> Перше to вводить run, друге належить до напрямку to the door. Not заперечує дію run, не факт вказівки.
    blocks[7].data.inputs[1].feedback.the coach told us not to run to the door: <span lang="en">The coach told us not to run to the door.</span><br><span lang="uk">Тренер сказав нам не бігти до дверей.</span> Перше to вводить run, друге належить до напрямку to the door. Not заперечує дію run, не факт вказівки.
    blocks[7].data.inputs[1].feedback.the coach did not tell us to run to the door.: Це «Тренер не казав нам бігти до дверей» — не те саме, що негативна вказівка. Потрібно: <span lang="en">The coach told us not to run to the door.</span><br><span lang="uk">Тренер сказав нам не бігти до дверей.</span>
    blocks[7].data.inputs[1].feedback.the coach did not tell us to run to the door: Це «Тренер не казав нам бігти до дверей» — не те саме, що негативна вказівка. Потрібно: <span lang="en">The coach told us not to run to the door.</span><br><span lang="uk">Тренер сказав нам не бігти до дверей.</span>
  blocks[7].data.linked_practice.source: theory_links
    blocks[7].data.linked_practice.question_types[0]: 4
    blocks[7].data.linked_practice.seeder_classes[0]: Database\Seeders\V3\Polyglot\PolyglotReportedSpeechCommandsRequestsAllLevelsLessonSeeder
  blocks[7].data.linked_practice.title: Вправа 4. Побудуй речення
  blocks[7].data.linked_practice.intro: Склади англійське речення за українською умовою, клікаючи на слова. Збережи мовця, адресата й зміст.
  blocks[7].data.linked_practice.footer: Завдання підібрані лише для цієї теми.
  details[0].key: commands-negation-scope
  details[0].title: Не було вказівки чи була негативна вказівка
  details[0].owner_block_key: commands-context
  details[0].owner_pointer: /sections/1
  details[0].fragment.type: usage-panels
    details[0].fragment.data.sections[0].label: Не було вказівки чи була негативна вказівка
    details[0].fragment.data.sections[0].color: slate
    details[0].fragment.data.sections[0].description: Порівняй дві розмови про зачинені двері. У першій керівниця не давала вказівки замикати їх: це не доводить ані протилежної вказівки, ані того, що двері лишили відчиненими. У другій керівниця прямо веліла не замикати двері. Це різні повідомлення; обидва не описують автоматично, що працівники зробили насправді. Зміна місця not змінює зміст, а не лише стиль.
      details[0].fragment.data.sections[0].examples[0].en: The manager did not tell us to lock the door.
      details[0].fragment.data.sections[0].examples[0].ua: Керівниця не казала нам замикати двері.
      details[0].fragment.data.sections[0].examples[1].en: The manager told us not to lock the door.
      details[0].fragment.data.sections[0].examples[1].ua: Керівниця сказала нам не замикати двері.
