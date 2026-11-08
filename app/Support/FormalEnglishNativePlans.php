<?php

namespace App\Support;

/** Finite reviewed conditions, lexical alternatives and retained-source groupings. */
final class FormalEnglishNativePlans
{
    public static function retained():array
    {
        return [
            'formal-register-and-nominalisation-basics'=>[
                ['We made|a decision|on Monday|to invite|two speakers.','У понеділок ми вирішили запросити двох доповідачів. Використай іменникову конструкцію рішення.','Збережи понеділок, двох доповідачів і нас як виконавців; назви рішення через іменник.'],
                ['Could you|send me|the seating plan|by noon tomorrow,|please?|I need it|to prepare|the name cards.','Ввічливо попроси план розсадження до завтра, полудня; він потрібен для карток з іменами.','Збережи адресата, строк та причину прохання; не роби його наказом.'],
                ['Yesterday we decided|to confirm|the booking today.','Учора ми вирішили сьогодні підтвердити бронювання. Збережи обидва часові орієнтири.','Рішення вже ухвалили вчора; підтвердження стосується сьогодні, а не вчора.'],
            ],
            'nominalisation-formal-register'=>[
                ['The conservators’ examination|of the mural|is currently|in progress.','Реставратори зараз досліджують настінний розпис. Склади іменникову конструкцію незавершеного процесу.','Збережи виконавців і предмет; дослідження триває, а не вже завершилося.'],
                ['The editors|reviewed the captions.|Their review|revealed two|incorrect dates.','Редактори перевірили підписи; ця перевірка виявила дві неправильні дати. Склади два речення.','Почни дієслівним реченням; у другому відсилай іменником до тієї самої перевірки.'],
                ['Repair of|the gate|by the volunteers|next week|is possible.','Волонтери, можливо, відремонтують ворота наступного тижня. Почни з іменника ремонту.','Можливість не є гарантією; збережи виконавців, ворота та наступний тиждень.'],
                ['We conducted|an analysis of|the booking requests.','Ми проаналізували запити на бронювання. Використай конструкцію «провели аналіз».','Назви нас як виконавців; аналіз стосується запитів на бронювання.'],
                ['It was announced|yesterday that|the workshop|had been cancelled.','Учора оголосили, що майстер-клас уже скасували. Збережи ранішу подію й час оголошення.','Оголошення було вчора; скасування передувало йому.'],
                ['The committee|evaluated the|two designs|on Tuesday.|This evaluation|revealed a|missing label.|The committee|plans to add|the label|on Friday.','У вівторок комітет оцінив два макети й виявив відсутню етикетку. Додати її планують у п’ятницю.','Збережи два макети, вівторок і лише план на п’ятницю; відсилай до оцінювання іменником.'],
            ],
            'register-tone-and-paraphrase'=>[
                ['The revised map|will be sent|to you|by our designer|by 4 p.m.|on Wednesday.','Наш дизайнер надішле вам виправлену карту до 16:00 у середу. Побудуй нейтральний пасив.','Почни з карти; збережи виконавця, адресата й кінцевий строк.'],
                ['Could you|send me|the invoice|by noon tomorrow,|please?','Ввічливо попроси надіслати рахунок до завтра, полудня.','Збережи адресата, рахунок і строк; обери ввічливе прохання, не наказ.'],
                ['If the hall|is unavailable,|the organiser|may move|only the|Saturday workshop online.|Friday’s session|will still|take place|in person.','Лише суботній майстер-клас можуть перенести онлайн, якщо зала недоступна. П’ятничний лишиться очним.','Збережи умову, можливість і обмеження суботою; п’ятничний захід не переноситься.'],
            ],
            'paraphrase-and-reformulation'=>[
                ['The organiser’s note|says that it|is possible for|two volunteers to|open the exhibition|on Monday if|the keys arrive|by noon.|It does not|identify those volunteers.','Записка допускає відкриття виставки двома волонтерами в понеділок за прибуття ключів до полудня; імена не вказані.','Збережи джерело, можливість, число, день, умову й невідомих виконавців; не перетворюй можливість на гарантію.'],
                ['According to|the note,|the museum tested|the audio guide|on a|single tablet and|did not test|it on|a phone.|The note therefore|provides no|phone-test result;|I cannot use|it to claim|either successful or|unsuccessful phone operation.','Музей перевірив аудіогід на одному планшеті, не на телефоні. Відділи записаний факт від невідомого результату.','Збережи один планшет і відсутність телефонного випробування; не стверджуй успіху або невдачі на телефоні.'],
            ],
            'subjunctive-and-formal-structures'=>[
                ['The coordinator recommends|that each reviewer|read the brief|by Friday.','Координатор рекомендує кожному рецензентові прочитати опис до п’ятниці. Використай базову форму без додаткового модального слова.','Не додавай закінчення третьої особи або минулий час; рекомендація не підтверджує виконання.'],
                ['She labelled|both folders|so that|the reviewers|would not|confuse them.','Вона позначила обидві папки, щоб рецензенти їх не переплутали. Використай сполучник мети й минулу модальну форму.','Збережи дві папки й мету запобігти плутанині, а не повідомлення про гарантований результат.'],
            ],
        ];
    }
    public static function authoredHints():array
    {
        return ['n2-answer'=>'Почни з іменника повторного оцінювання; збережи комітет, жовтень і лише можливість.',
            'n3-rewrite'=>'Назви план скорочення часу очікування, а не вже отримане поліпшення.',
            'n4-answer'=>'Почни з оцінювання; збережи куратора, страхові документи та понеділок.',
            'n5-answer'=>'Почни з оцифровування; роботу волонтерів розпочато в червні, але вона ще триває.',
            'n6-answer'=>'Розширення лише запропоновано; рішення та вимірювання нового часу очікування ще немає.'];
    }
    public static function retainedAliases():array{return[
        'register-tone-and-paraphrase'=>[0=>['The revised map will be sent to you by our designer by 4 pm on Wednesday.','The revised map will be sent to you by our designer by 4 p m on Wednesday.']],
    ];}
    public static function additions():array
    {
        return [
            'formal-register-and-nominalisation-basics'=>[
                ['gap','We would {a1} written confirmation of the appointment.','appreciate','Були б вдячні за: доповни нейтральне ввічливе прохання без нового підмета.'],
                ['gap','Please {a1} the completed form to the admissions office.','submit','Подати (передати): використай доречне для офіційного прохання дієслово передавання документа.',['send','forward','deliver']],
                ['translation','Будь ласка, повідомте нам про будь-які зміни.','Please inform us|of any changes.','Почни з ввічливого звернення; збережи нас як одержувачів повідомлення та всі можливі зміни.',['Please notify us of any changes.','Please inform us about any changes.','Please notify us about any changes.']],
                ['translation','Офіційне підтвердження надійде електронною поштою.','Official confirmation|will arrive|by email.','Збережи офіційність підтвердження, майбутній час і спосіб отримання.',['Official confirmation will be received by email.','Official confirmation will arrive via email.','Official confirmation will be received via email.']],
            ],
            'nominalisation-formal-register'=>[
                ['gap','The {a1} of the application took three working days.','assessment','Оцінювання: утвори іменникову назву процесу перевірки заяви; наведені слова не повторюй.',['evaluation','review']],
                ['gap','Their {a1} to cooperate delayed the negotiations.','refusal','Відмова: назви іменником рішення не співпрацювати; не підмінюй його нездатністю.'],
                ['translation','Завершення ремонту заплановане на вересень.','Completion of|the repairs|is scheduled|for September.','Почни з назви завершення; це план, не вже завершений ремонт.',['The completion of the repairs is scheduled for September.','Completion of the repairs is planned for September.','The completion of the repairs is planned for September.']],
                ['translation','Перевірка обладнання виявила два дефекти.','Inspection of|the equipment|revealed two defects.','Використай іменникову назву перевірки; збережи обладнання та рівно два виявлені дефекти.',['The inspection of the equipment revealed two defects.','Inspection of the equipment identified two defects.','The inspection of the equipment identified two defects.','Examination of the equipment revealed two defects.','The examination of the equipment revealed two defects.','Inspection of the equipment detected two defects.','The inspection of the equipment detected two defects.','Examination of the equipment detected two defects.','The examination of the equipment detected two defects.']],
            ],
            'register-tone-and-paraphrase'=>[
                ['gap','We regret to {a1} you that the service is unavailable today.','inform','Повідомити: доповни стримане офіційне повідомлення про недоступність послуги, не додаючи нового підмета.',['notify']],
                ['gap','Could you {a1} whether the estimate includes delivery?','clarify','Уточнити: попроси пояснити межі кошторису, а не погодитися з невідомим фактом.',['confirm']],
                ['translation','Чи могли б ви уточнити причину затримки?','Could you clarify|the reason|for the delay?','Склади ввічливе запитання про причину; не припускай, що її вже пояснили.',['Could you explain the reason for the delay?','Would you clarify the reason for the delay?','Would you explain the reason for the delay?']],
                ['translation','Ми поки що не отримали вашої відповіді.','We have not|yet received|your response.','Збережи відсутність відповіді донині та нейтральний офіційний тон; адресат уже визначений.',['We have not yet received your reply.','We have not received your response yet.','We have not received your reply yet.']],
            ],
            'paraphrase-and-reformulation'=>[
                ['gap','The rooms differ in size; in other {a1}, they are not equally large.','words','Словах: доповни сталий перехід до переформулювання того самого змісту.'],
                ['gap','The route is shorter, but this does not {a1} that the journey will be quicker.','mean','Означати: запереч логічний висновок про швидкість, не вже названу меншу відстань.',['imply']],
                ['translation','Інакше кажучи, дозволено лише одну спробу.','In other words,|only one attempt|is permitted.','Почни переходом до переформулювання; збережи дозвіл рівно на одну спробу.',['In other words, only one attempt is allowed.','In other words, only a single attempt is permitted.','In other words, only a single attempt is allowed.']],
                ['translation','Це уточнення не змінює початкової умови.','This clarification|does not change|the original condition.','Збережи заперечення зміни та відсилання до конкретної початкової умови.',['This clarification does not alter the original condition.','This clarification does not change the initial condition.','This clarification does not alter the initial condition.']],
            ],
            'subjunctive-and-formal-structures'=>[
                ['gap','It is essential that every visitor {a1} a visible badge.','wear','Носити: використай базову форму в офіційній вимозі без додаткового модального слова.'],
                ['gap','The board requested that the meeting {a1} until all members were present.','be postponed','Бути відкладеним: уведи повну пасивну базову форму вимоги; це не повідомлення про вже виконану дію.',['be delayed']],
                ['translation','Важливо, щоб кожна сторінка була пронумерована.','It is important|that every page|be numbered.','Використай офіційну базову пасивну модель без додаткового модального слова; це вимога до кожної сторінки.',['It is important that each page be numbered.']],
                ['translation','Лікар рекомендував, щоб вона більше відпочивала.','The doctor recommended|that she|rest more.','Використай базову форму після минулої рекомендації без додаткового модального слова; виконання не підтверджене.',['The doctor advised that she rest more.']],
            ],
            'nominal-style-and-information-density'=>[
                ['gap','The implementation of the revised procedure {a1} subject to approval.','is','Є: узгодь теперішню форму з упровадженням як єдиним процесом, а не з частинами процедури.'],
                ['gap','The report describes a proposed {a1} in annual maintenance costs.','reduction','Скорочення: назви лише запропоноване зниження витрат, не вже досягнуту економію.',['decrease']],
                ['translation','Запропоноване збільшення місткості ще не схвалили.','The proposed increase|in capacity|has not yet|been approved.','Почни з конкретного запропонованого збільшення; збережи відсутність схвалення донині.',['The proposed expansion in capacity has not yet been approved.','The proposed increase in capacity has not been approved yet.','The proposed expansion in capacity has not been approved yet.']],
                ['translation','Оцінювання ризиків комісією ще триває.','The commission’s assessment|of the risks|is still|in progress.','Почни з іменникової назви оцінювання; збережи комісію, ризики та незавершеність.',['The assessment of the risks by the commission is still in progress.','The commission’s evaluation of the risks is still in progress.','The evaluation of the risks by the commission is still in progress.','The committee’s assessment of the risks is still in progress.','The assessment of the risks by the committee is still in progress.','The committee’s evaluation of the risks is still in progress.','The evaluation of the risks by the committee is still in progress.']],
            ],
        ];
    }
    public static function copy():array
    {
        return [
            'formal-register-and-nominalisation-basics'=>['choices'=>[
                ['Обери доречне перше звернення до незнайомої працівниці музею.',[['en','A. Hey, give me the dates by Friday.'],['en','B. Could you send me the available dates by Friday, please?']]],
                ['Знайди іменник, утворений від дії.',[['en','A. the director’s approval of the poster — approve'],['en','B. the yellow poster — approve']]],
                ['Виправ невдале дієслово прибуття; збережи місце й час.',[['en','A. We arrived at the gallery at ten.'],['en','B. We got to the gallery at ten.'],['en','C. We obtained to the gallery at ten.']]],
            ]],
            'nominalisation-formal-register'=>[],
            'register-tone-and-paraphrase'=>['choices'=>[
                ['Обери нейтральну відповідь новому клієнту.',[['en','A. Hey, here’s how it works.'],['en','B. Thank you for your enquiry. The booking process has three steps.']]],
                ['Назви зміни кількості й упевненості.',[['en','Not all applicants can attend on Monday, and some may need an online option. → No applicants can attend on Monday, and all will need an online option.']],["Не всі → жоден; деякі → усі; можливість → прогноз.","Змінено лише порядок слів."]],
                ['Обери версію, яка зберігає всі подробиці.',[['en','The club may hold two outdoor sessions in June if the park is available.'],['en','A. If the park is available, it is possible that the club will run two outdoor sessions in June.'],['en','B. The club may run outdoor sessions.']]],
            ]],
            'paraphrase-and-reformulation'=>[
                'selects'=>[
                    ['Відрізни уточнення від рівнозначної заміни.',[['en','The workshop starts in the morning. More precisely, it starts at 9.15.']],["Уточнення: додано 9:15; лише зі слова «вранці» цей час не випливає.","Уточнення: 9:15 не можна вивести лише з in the morning.","Точне перефразування: in the morning означає саме 9:15.","Самовиправлення: ранковий час відкликано."]],
                    ['Розкрий визначений термін без нового факту.',[['uk','Термін означає тих, хто виконує всі три умови; дозволено реєструватися лише їм.'],['en','Only eligible applicants can register.']],["Only eligible applicants—that is, applicants who meet all three stated conditions—can register.","Only eligible applicants—that is to say, applicants who meet all three stated conditions—can register.","Only applicants who meet all three stated conditions can register.","All applicants who meet all three stated conditions will be registered.","Only applicants who meet at least one stated condition can register."]],
                ],
                'choices'=>[
                    ['Відділи записаний факт від можливого пояснення.',[['en','Four readers returned the guide after one day. No reasons were recorded.']],["Повернули через день; причини невідомі. Плутанина — лише можливе пояснення.","Повернули через день; причина невідома.","Запис доводить: повернули, бо посібник був незрозумілий."]],
                    ['Що змінюється в трьох замінах?', [['en','not all entries → no entries; at least four → exactly four; some may need help → all will need help']],["Змінюються кількість, нижня межа та сила прогнозу.","Усі заміни лише стилістичні."]],
                ],
            ],
            'subjunctive-and-formal-structures'=>[
                'selects'=>[
                    ['Зістав прохання та повідомлення про стан.',[['en','The organiser requests that the entrance be kept clear. The organiser reports that the entrance is clear.']],["Прохання не доводить виконання; друге речення передає повідомлення про стан.","Прохання доводить виконання; повідомлення називає лише бажання.","Обидва — вимоги й незалежний доказ виконання."]],
                    ['Зістав ознаку можливого факту, вимогу та наполягання.',[['en','The missing label suggests that a box is in the wrong room. The manager insists that the box be moved. The manager insists that the box is in the right room.']],["Ознака → вимога → наполягання на факті; is у першому й третьому доречне.","Усі три — вимоги: обидва is слід замінити на be; коробку вже перенесли.","Усі три — підтверджені виконані факти."]],
                ],
                'choices'=>[
                    ['Обери базову заперечну й пасивну вимогу без should.',[['uk','Асистент не має видаляти коментарі; повідомити рецензентів до полудня повинен саме асистент.']],["The assistant not delete…; the reviewers be informed by the assistant by noon.","The assistant doesn’t delete…; the reviewers are informed by noon."]],
                    ['Передай минулу рекомендацію, не її виконання.',[['uk','У понеділок Лео порадив Еммі перевірити заголовок перед публікацією. Виконання невідоме.']],["On Monday, Leo recommended that Emma check the heading before publication.","On Monday, Leo recommended that Emma checked the heading before publication, and she checked it."]],
                ],
            ],
        ];
    }
    public static function authoredCopy():array
    {
        return ['nominal-style-and-information-density'=>[
            '<p>Обери узгодження та головне слово.</p><p lang="en">The gradual expansion of the reading rooms ___ scheduled for autumn.</p>',
            '<p>Почни з іменника <span lang="en">A reassessment</span>; збережи комітет, жовтень і можливість.</p><p lang="en">The committee may reassess the exhibition plan in October.</p>',
            '<p>Зістав план і результат. Вибери додані факти та перепиши план з іменником <span lang="en">reduction</span>.</p><p lang="en">The team plans to reduce waiting times. → The reduction in waiting times has improved the service.</p>',
            '<p>Почни з іменника оцінювання; збережи всіх учасників і понеділок.</p><p lang="en">The curator assessed the insurance documents on Monday.</p>',
            '<p>Почни з іменника оцифровування каталогу. Збережи волонтерів, початок у червні та незавершеність; склади два речення.</p>',
            '<p>Виправ нотатку: команда лише запропонувала розширення залу; рішення й вимірювання нового часу очікування відсутні. Склади два-три речення.</p>',
        ]];
    }
}
