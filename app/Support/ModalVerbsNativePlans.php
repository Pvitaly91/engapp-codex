<?php

namespace App\Support;

/** Finite reviewed practice additions; the established native controls own all rendering and grading. */
final class ModalVerbsNativePlans
{
    /** kind, condition, canonical answer/grouping, Ukrainian hint, optional complete aliases. */
    public static function additions(): array
    {
        return [
            'modal-perfect-and-deduction'=>[
                ['gap','The sealed parcel is wet. Water {a1} through a tear in the wrapping.','must have leaked','Уведи сильний висновок про попереднє протікання; це висновок за ознакою, не встановлений факт.'],
                ['gap','The committee {a1} the appeal yet; the hearing is tomorrow.','cannot have rejected','Відкинь завершену відмову до ще не проведеного слухання; уведи всю заперечну перфектну групу.',["can't have rejected"]],
                ['translation','Можливо, свідки неправильно почули прізвище. Передай непідтверджене пояснення минулого.','The witnesses|may have misheard|the surname.','Почни зі свідків; збережи можливість, а не впевнений висновок.',['The witnesses might have misheard the surname.','The witnesses could have misheard the surname.']],
                ['translation','Тобі слід було зберегти оригінальний чек.','You should have|kept the|original receipt.','Передай оцінку невиконаної минулої дії; почни зі співрозмовника.',['You ought to have kept the original receipt.']],
            ],
            'subtle-modal-meanings'=>[
                ['gap','The supplier’s late reply {a1} the confusion, but no cause has been established.','may well explain','Уведи модальну групу правдоподібного, але непідтвердженого пояснення.', ['might well explain','could well explain']],
                ['gap','The venue is closed, so we {a1} in the courtyard instead.','might as well rehearse','Уведи практичну пропозицію репетирувати за відсутності кращого доступного варіанта.', ['may as well rehearse']],
                ['translation','Можливо, варто переглянути припущення перед публікацією. Використай безособову модель оцінки користі.','It may be|worth revisiting|the assumptions|before publication.','Почни з безособового займенника; після оцінки користі потрібна форма процесу.', ['It might be worth revisiting the assumptions before publication.','It may be worth reviewing the assumptions before publication.','It might be worth reviewing the assumptions before publication.']],
                ['translation','Немає потреби робити висновок, що редакторка схвалила кожну зміну. Почни з ми; передай відсутність логічної необхідності.','We need not|conclude that|the editor approved|every change.','Запереч необхідність висновку, а не можливість схвалення чи факт подальшої дії.', ["We needn't conclude that the editor approved every change."]],
            ],
            'must-have-to'=>[
                ['gap','According to the safety rules, every visitor {a1} protective glasses.','has to wear','Правила зобов’язують одного відвідувача носити захисні окуляри; уведи всю дієслівну групу.',['must wear']],
                ['gap','Yesterday we {a1} a different entrance because the main door was locked.','had to use','Передай вимушену минулу дію зі значенням «скористатися»; не повторюй підмет.'],
                ['translation','Тут не можна годувати тварин. Використай сувору заборону; почни з ти.','You must not|feed the animals|here.','Заборони годування, не підміняючи заборону відсутністю потреби.', ["You mustn't feed the animals here."]],
                ['translation','Олегові доведеться оплатити доставку завтра.','Oleh will have|to pay for|delivery tomorrow.','Почни з Олега й передай майбутню необхідність оплати.',['Oleh will have to pay for the delivery tomorrow.']],
            ],
            'should-ought-to'=>[
                ['gap','Your wrist still hurts. You {a1} it checked.','should get','Порадь організувати огляд зап’ястка; уведи модальне слово й дієслово отримання послуги.',['ought to get']],
                ['gap','The deadline is close. We {a1} any more time.','should not waste','Порадь не марнувати час; уведи всю заперечну дієслівну групу.', ["shouldn't waste",'ought not to waste',"oughtn't to waste"]],
                ['translation','Тобі слід перевірити адресу перед відправленням.','You should|check the address|before sending.','Почни зі співрозмовника; передай пораду, а не суворий обов’язок.',['You ought to check the address before sending.']],
                ['translation','Нам не слід переривати доповідачку.','We should not|interrupt the speaker.','Почни з нас і збережи заперечну пораду.', ["We shouldn't interrupt the speaker.",'We ought not to interrupt the speaker.',"We oughtn't to interrupt the speaker."]],
            ],
            'need-neednt-dont-have-to'=>[
                ['gap','The kettle is already full. You {a1} any more water.','need not add','Передай відсутність потреби доливати воду; це не заборона.', ["needn't add",'do not need to add',"don't need to add",'do not have to add',"don't have to add"]],
                ['gap','Rita {a1} the tickets; the organiser had already printed them.','need not have printed','Ріта все-таки надрукувала квитки, але потреби не було; уведи перфектну модальну групу.', ["needn't have printed"]],
                ['translation','Нам потрібно зарядити ліхтар перед походом.','We need to|charge the torch|before the hike.','Почни з нас; назви необхідну підготовку перед походом.',['We need to charge the flashlight before the hike.']],
                ['translation','Тобі не обов’язково відповідати сьогодні. Використай заперечне звичайне дієслово потреби.','You do not|need to reply|today.','Почни зі співрозмовника; уведи заперечну теперішню форму потреби.', ["You don't need to reply today.",'You do not need to respond today.',"You don't need to respond today."]],
            ],
            'modals-of-deduction'=>[
                ['gap','The floor is shaking and the engine is loud. The machine {a1} now.','must be running','За чіткими ознаками зроби сильний висновок про роботу машини саме зараз; уведи всю тривалу групу.'],
                ['gap','This ticket has Maria’s name on it. It {a1} to Ivan.','cannot belong','Відкинь належність квитка Іванові; решту дієслівної групи й прийменник не повторюй.', ["can't belong"]],
                ['translation','Можливо, кур’єр шукає інший вхід.','The courier|may be looking|for another entrance.','Почни з кур’єра; передай можливий процес, а не підтверджену дію.',['The courier might be looking for another entrance.','The courier could be looking for another entrance.']],
                ['translation','Цей підпис не може бути справжнім.','This signature|cannot be genuine.','Почни з підпису й відкинь справжність як неможливу за наявними ознаками.', ["This signature can't be genuine.",'This signature cannot be authentic.',"This signature can't be authentic."]],
            ],
            'past-modals'=>[
                ['gap','The shelves are empty. Someone {a1} all the folders.','must have removed','Зроби сильний висновок про попереднє прибирання папок; уведи всю перфектну групу.', ['must have taken away']],
                ['gap','You sent the file without checking it. You {a1} it first.','should have checked','Вислови критику зробленого минулого вибору; перевірка була потрібна раніше.', ['ought to have checked']],
                ['translation','Можливо, таксі поїхало іншим маршрутом.','The taxi|might have taken|a different route.','Почни з таксі; збережи непідтверджену можливість минулої дії.',['The taxi may have taken a different route.','The taxi could have taken a different route.']],
                ['translation','Ми могли попередити сусідів, але не зробили цього.','We could have|warned the neighbours,|but we|did not.','Збережи невикористану минулу можливість та явно названий результат.', ['We could have warned the neighbors, but we did not.',"We could have warned the neighbours, but we didn't.","We could have warned the neighbors, but we didn't."]],
            ],
            'requests-offers-suggestions'=>[
                ['gap','{a1} hold the ladder for a moment, please?','Could you','Уведи лише ввічливий початок прохання до співрозмовника; дія вже наведена.', ['Would you','Can you','Will you']],
                ['gap','Your hands are full. {a1} open the door for you?','Shall I','Мовець пропонує власну допомогу; уведи лише початок питання.'],
                ['translation','Може, зустрінемось біля годинника? Використай спільну пропозицію з питальним початком.','Shall we|meet by|the clock?','Дію виконаємо ми разом; почни зі спільної пропозиції, не прохання до однієї людини.'],
                ['translation','Хочеш шматок яблучного пирога?','Would you like|a slice of|apple pie?','Запропонуй співрозмовникові річ, не дію.'],
            ],
            'can-could'=>[
                ['gap','My grandmother {a1} three languages when she was young.','could speak','Передай загальне минуле вміння говорити; підмет і решта речення вже наведені.'],
                ['gap','The instructions are clear. I {a1} the tent without help.','can put up','Передай теперішню здатність установити намет; уведи всю дієслівну групу.', ['can erect']],
                ['translation','Моя сестра вміє грати на скрипці.','My sister|can play|the violin.','Почни із сестри; передай теперішнє вміння.'],
                ['translation','Не міг би ти потримати мою сумку?','Could you|hold my bag?','Почни з ввічливого модального питання; сумку триматиме співрозмовник.', ['Would you hold my bag?','Can you hold my bag?']],
            ],
            'may-might'=>[
                ['gap','We have not checked the timetable. The last ferry {a1} at six.','may leave','Передай непевний час відправлення; уведи можливість, а не факт.', ['might leave','could leave']],
                ['gap','{a1} borrow this atlas for the afternoon?','May I','Мовець формально просить дозволу позичити атлас; уведи лише початок питання.', ['Might I']],
                ['translation','Можливо, виставка ще відкрита.','The exhibition|may still|be open.','Почни з виставки й передай непевний теперішній стан.',['The exhibition might still be open.']],
                ['translation','Можливо, вони не помітили попередження.','They may not|have noticed|the warning.','Збережи можливе невиконання минулої дії, не заборону.', ['They might not have noticed the warning.']],
            ],
        ];
    }

    /** Ordered groups and contextual Ukrainian hint for each of the twenty retained manual tasks. */
    public static function retained(): array
    {
        return [
            'modal-perfect-and-deduction'=>[
                ['Nora must have|unlocked the door.|Eli can’t have|been at|the studio|at nine.','Склади два задані висновки; збережи Нору, неможливість присутності Елі, студію й дев’яту годину.'],
                ['The studio|was dark yesterday.|The rehearsal|may have been|cancelled.','Збережи спостереження про студію та лише можливе пояснення скасування репетиції.'],
            ],
            'subtle-modal-meanings'=>[
                ['You might want|to check|the participant list|before printing.|It may be|worth checking|the participant list|before printing.|You would be|wise to check|the participant list|before printing:|the previous copy|listed one name|twice.','Склади три моделі поради в заданому порядку; у третій збережи підставу про повторене ім’я.'],
                ['Ira needn’t have|printed the|extra poster yesterday.|Missing clips|could well explain|today’s delay.|It may be|worth checking|the cupboard.','Збережи зайвий виконаний друк, непідтверджену причину затримки й обережну пораду перевірити шафу.'],
            ],
            'must-have-to'=>[
                ["Tomorrow, Lena|doesn't have to|print the tickets.",'Передай відсутність потреби друкувати квитки завтра; почни з обставини часу.'],
                ['Do we|have to book|the room?','Запитай про нашу необхідність забронювати кімнату.'],
            ],
            'should-ought-to'=>[
                ["We oughtn't to|leave the tickets|at home.",'Дай нам заперечну пораду не залишати квитки вдома; збережи модель моральної поради.'],
                ['Should we|take a bus|to the station?','Запитай, чи варто нам поїхати автобусом до станції.'],
            ],
            'need-neednt-dont-have-to'=>[
                ["We needn't|bring lunch|tomorrow.",'Передай відсутність потреби приносити обід завтра; це не заборона.'],
                ['Does Mila|need to bring|an umbrella?','Запитай про потребу Міли принести парасолю; підмет стоїть після допоміжного.'],
            ],
            'modals-of-deduction'=>[
                ['They might be|waiting outside.','Передай можливий процес очікування надворі; почни з них.'],
                ["This code|can't have|four digits.",'Відкинь чотири цифри, бо код точно тризначний; почни з цього коду.'],
            ],
            'past-modals'=>[
                ["Alex can't have|read the message|yesterday.",'Відкинь учорашнє прочитання повідомлення, створеного лише сьогодні; почни з Алекса.'],
                ['Iryna should not|have told|the secret.','Оціни як помилкове зроблене розголошення таємниці; почни з Ірини.'],
            ],
            'requests-offers-suggestions'=>[
                ['Would you|close the gate,|please?','Попроси співрозмовника зачинити хвіртку; збережи ввічливість.'],
                ["Let's not|leave the bags|here.",'Запропонуй нам обом не залишати сумки тут.'],
            ],
            'can-could'=>[
                ['My little brother|cannot walk|yet.','Передай теперішню нездатність маленького брата ходити; збережи значення «ще».'],
                ['Could you|pass me|the salt?','Попроси співрозмовника передати сіль мовцеві, а не навпаки.'],
            ],
            'may-might'=>[
                ['The keys|might be|in the kitchen.','Кухня є лише можливим місцем ключів; не перетворюй припущення на факт.'],
                ['May we|take a photo|here?','Запитай дозволу для всієї нашої групи зробити тут одне фото.'],
            ],
        ];
    }

    /** Ukrainian full-sentence stimuli replace only the ten meaningless legacy builder headings. */
    public const WIDGET_CONDITIONS=[
        25551=>'Я вмію добре плавати.',
        25552=>'Коли мені було шість, я вмів читати прості книжки.',
        25553=>'Тобі можна сісти тут.',
        25554=>'Не міг би ти допомогти мені?',
        25555=>'Можливо, пізніше піде дощ.',
        25779=>'Тобі потрібно показати свій квиток.',
        25780=>'Нам не потрібно бронювати квитки сьогодні.',
        25781=>'Нам зараз не треба поспішати.',
        25782=>'Нам не обов’язково приходити завтра.',
        25788=>'Їй потрібно поновити свою картку.',
        59661=>'Цей сигналізатор, мабуть, несправний.',
        59662=>'Гість, можливо, у фоє.',
        59663=>'Ця шухляда не може бути порожньою.',
        59664=>'Принтер, можливо, зараз друкує етикетки.',
        59665=>'Запасного кабелю, можливо, немає у шафці.',
        59666=>'Архівістка, мабуть, відімкнула шафку.',
        59667=>'Кур’єр, можливо, скористався бічним входом.',
        59668=>'Ми могли поставити коробки всередині, але залишили їх надворі.',
        59669=>'Юрієві не слід було пересувати драбину.',
        59670=>'Потяг мав би вже прибути на станцію.',
        59671=>'Не могла б ти повторити назву?',
        59672=>'Понести тобі папку?',
        59673=>'Хочеш закладку?',
        59674=>'Хочеш приєднатися до екскурсії?',
        59675=>'Може, відвідаємо галерею після обіду?',
    ];

    /** Reviewed lexical alternatives only; no automatic modal-strength or fact substitutions. */
    public const WIDGET_ALIASES=[
        18147=>['The message may have been removed by accident'],
        18144=>['The client may have misunderstood the terms'],
        19027=>['It may be worth recognising the limitations of the study','It may be worth recognizing the limitations of the study'],
        19004=>['This could well have influenced the result'],
        19016=>['She would read every email again before sending it'],
        19031=>['They did not need to rewrite the entire document'],
        19040=>['Why might this have seemed too blunt'],
        123=>['He must present his ticket at the entrance'],
        125=>['I have to rise early tomorrow'],
        97=>['You should visit a doctor'],
        98=>['She should sleep more'],
        99=>['We should go now'],
        100=>['He should speak to his teacher'],
        101=>['They should put aside some money'],
        25779=>['You need to present your ticket'],
        25780=>["We don't need to reserve tickets today"],
        59661=>['This alarm must be faulty','This alarm must be malfunctioning'],
        59662=>['The guest may be in the lobby'],
        59665=>['The spare cable may not be in the cupboard'],
        59667=>['The courier might have taken the side entrance'],
        59668=>['We could have placed the boxes inside, but we left them outside'],
        59669=>["Yurii shouldn't have shifted the ladder"],
        59670=>['The train should have reached the station by now'],
        59675=>['Shall we go to the gallery after lunch'],
        25552=>['When I was six I could read easy books'],
        25553=>['You can take a seat here'],
        25554=>['Could you assist me'],
        47955=>['She might arrive later'],
        47957=>['It may rain tonight'],
        47958=>['He might be occupied now'],
    ];
    public const WIDGET_TOKEN_LABELS=[
        18147=>['deleted'=>'deleted (removed)'],18144=>['conditions'=>'conditions (terms)'],
        19027=>['acknowledging'=>'acknowledging (recognising / recognizing)'],19004=>['affected'=>'affected (influenced)'],
        19031=>['whole'=>'whole (entire)'],19040=>['harsh'=>'harsh (blunt)'],
        123=>['show'=>'show (present)'],97=>['see'=>'see (visit)'],
        99=>['leave'=>'leave (go)'],100=>['talk'=>'talk (speak)'],
        25779=>['show'=>'show (present)'],25780=>["Don't"=>"don't",'book'=>'book (reserve)'],25782=>["Don't"=>"don't"],
        59661=>['broken'=>'broken (faulty / malfunctioning)'],59662=>['foyer'=>'foyer (lobby)'],59665=>['cabinet'=>'cabinet (cupboard)'],
        59667=>['used'=>'used (taken)'],59668=>['put'=>'put (placed)'],59669=>['moved'=>'moved (shifted)'],
        25552=>['simple'=>'simple (easy)'],25554=>['help'=>'help (assist)'],
        47955=>['come'=>'come (arrive)'],47958=>['busy'=>'busy (occupied)'],
    ];
    public const WIDGET_DERIVED_OPTIONS=[
        18147=>['removed'],18144=>['terms'],19027=>['recognising','recognizing'],19004=>['influenced'],19016=>['read','again'],19031=>['entire'],19040=>['blunt'],
        123=>['present'],125=>['rise'],97=>['visit'],98=>['sleep'],99=>['go'],100=>['speak'],101=>['put','aside'],25779=>['present'],25780=>['reserve'],
        59661=>['faulty','malfunctioning'],59662=>['lobby'],59665=>['cupboard'],59667=>['taken'],59668=>['placed'],59669=>['shifted'],59670=>['reached'],59675=>['go','to'],
        25552=>['easy'],25553=>['take','a','seat'],25554=>['assist'],47955=>['arrive'],47957=>['tonight'],47958=>['occupied'],
    ];

    public static function widgetHints(): array
    {
        return [
            'modal-perfect-and-deduction'=>[
                'Почни з повідомлення; передай можливу минулу пасивну дію й випадковість, не встановлений факт.',
                'Почни з модального питання; запитай про можливу минулу пасивну дію та неправильне місце.',
                'Почни з них; зроби сильний висновок про попередній тривалий процес очікування понад годину.',
                'Передай можливе неотримання запрошення в минулому; не підмінюй його забороною.',
                'Почни з клієнта; припусти неправильне розуміння умов у минулому.',
            ],
            'subtle-modal-meanings'=>[
                'Почни з безособового займенника; запропонуй оцінити користь визнання обмежень дослідження.',
                'Збережи правдоподібність минулого впливу на результат, не перетворюючи пояснення на доказ.',
                'Передай звичну повторювану минулу дію перед відправленням; не втрачай кожного листа.',
                'Передай відсутність минулої необхідності; не стверджуй, що переписування справді відбулося.',
                'Почни з питання про причину; передай можливе минуле враження надмірної різкості.',
            ],
            'must-have-to'=>[
                'Почни зі співрозмовника; передай обов’язок носити шолом саме на цій дорозі.',
                'Почни з нас; назви обов’язок дотримуватися тиші в бібліотеці.',
                'Почни з нього; збережи обов’язок показати власний квиток на вході.',
                'Почни з неї; збережи ліки й частоту двічі на день.',
                'Почни з мовця; передай необхідність раннього підйому завтра через дієслівну модель обов’язку.',
            ],
            'should-ought-to'=>[
                'Почни зі співрозмовника; дай пораду звернутися до лікаря, не наказ.',
                'Почни з неї; порадь збільшити тривалість сну.',
                'Почни з нас; порадь піти саме зараз.',
                'Почни з нього; порадь поговорити з власним учителем.',
                'Почни з них; дай пораду заощадити певну кількість грошей.',
            ],
            'need-neednt-dont-have-to'=>[
                'Почни зі співрозмовника; назви необхідність показати власний квиток.',
                'Почни з нас; запереч потребу бронювати квитки саме сьогодні через звичайне дієслово потреби.',
                'Почни з нас; використай скорочену модальну відсутність потреби поспішати зараз.',
                'Почни з нас; запереч обов’язок приходити завтра, не висловлюй заборону.',
                'Почни з неї; узгодь теперішню дієслівну потребу поновити власну картку.',
            ],
            'modals-of-deduction'=>[
                'Почни із цього сигналізатора; зроби сильний висновок про його теперішню несправність.',
                'Почни з гостя; фоє є лише одним можливим місцем, не встановленим фактом.',
                'Почни із цієї шухляди; відкинь порожнечу скороченою формою неможливості.',
                'Почни з принтера; збережи можливий процес друку етикеток зараз, обставину часу постав наприкінці.',
                'Почни із запасного кабелю; передай можливу відсутність у шафці, не заборону.',
            ],
            'past-modals'=>[
                'Почни з архівістки; зроби сильний висновок про попереднє відімкнення шафки.',
                'Почни з кур’єра; передай можливий минулий вибір бічного входу, не підтверджений факт.',
                'Почни з нас; збережи невикористану можливість поставити коробки всередині й те, що вони залишилися надворі.',
                'Почни з Юрія; скороченою формою вислови критику зробленого пересування драбини.',
                'Почни з потяга; передай очікуване прибуття на станцію до цього моменту, обставину постав наприкінці.',
            ],
            'requests-offers-suggestions'=>[
                'Попроси співрозмовницю повторити назву; дію виконає вона, почни з ввічливого питання.',
                'Мовець пропонує сам понести папку; почни з питальної пропозиції власної допомоги.',
                'Запропонуй співрозмовникові одну закладку як річ, не виконання дії.',
                'Запроси співрозмовника долучитися до екскурсії; після ввічливої пропозиції потрібна дія.',
                'Запропонуй нам разом відвідати галерею; збережи час після обіду наприкінці.',
            ],
            'can-could'=>[
                'Почни з мовця; передай теперішнє вміння добре плавати.',
                'Почни з часової частини про шість років; передай загальне минуле вміння читати прості книжки.',
                'Почни зі співрозмовника; передай дозвіл сісти тут, не здатність сидіти.',
                'Попроси співрозмовника допомогти мовцеві; почни з ввічливого модального питання.',
                'Передай непевну можливість пізнішого дощу; почни з безособового підмета.',
            ],
            'may-might'=>[
                'Почни з неї; припусти пізніший прихід, не підтверджений план.',
                'Почни зі співрозмовника; збережи дозвіл піти раніше та умову згоди керівника.',
                'Передай можливий дощ сьогодні ввечері; почни з безособового підмета.',
                'Почни з нього; припусти теперішню зайнятість, не встановлений факт.',
                'Почни з них; передай можливе незнання відповіді, не заборону знати.',
            ],
        ];
    }
}
