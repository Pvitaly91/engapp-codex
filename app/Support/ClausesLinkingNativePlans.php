<?php

namespace App\Support;

/** Finite reviewed sentence conditions and logical one-to-three-word answer groups. */
final class ClausesLinkingNativePlans
{
    public static function retained():array
    {
        return [
            'linking-words-reason-result-contrast'=>[
                ['The final bus|had left;|therefore,|we walked home.','Останній автобус уже поїхав; тому ми пішли додому пішки.','Почни з автобуса; передай наслідок окремою сполучною обставиною.'],
                ['In spite of|feeling tired,|Ben answered|every question.','Попри втому Бен відповів на кожне запитання.','Почни з поступки; Бен є тим, хто втомився й відповідав.'],
            ],
            'advanced-linking-devices'=>[
                ['Moreover,|the library|offers free|legal advice.','Крім того, бібліотека пропонує безкоштовні юридичні консультації.','Почни зі зв’язки додавання; не змінюй виконавця чи вид допомоги.'],
                ['The dataset|is incomplete;|therefore,|the estimate|is provisional.','Набір даних неповний; тому оцінка попередня.','Неповнота даних є причиною, попередній характер оцінки — наслідком.'],
            ],
            'concessive-and-contrastive-structures'=>[
                ['Despite the fact|that the instructions|were clear,|two participants|missed a step.','Попри те що інструкції були зрозумілі, двоє учасників пропустили крок.','Почни з повної поступової конструкції з іменником «факт».'],
                ['Although the budget|was tight,|the committee|approved the plan.','Хоча бюджет був обмежений, комітет затвердив план.','Почни з допустової частини з особовим дієсловом.'],
            ],
            'participle-clauses-basics'=>[
                ['The people|repairing the roof|need a ladder.','Людям, які ремонтують дах, потрібна драбина.','Скорочений опис називає дію людей; основна частина повідомляє їхню потребу.'],
                ['While Taras|was reading|the message,|the lights|went out.','Коли Тарас читав повідомлення, світло згасло.','Читач — Тарас, не світло; збережи повну часову підрядну частину.'],
            ],
            'participle-clauses'=>[
                ['Not having seen|the revised map,|Roman took|the old route.','Не бачивши оновленої карти, Роман поїхав старим шляхом.','Почни із заперечної форми передування; обидві дії стосуються Романа.'],
                ['Because Pavlo|saw the|warning light,|he stopped|the machine.','Оскільки Павло побачив сигнальну лампу, він зупинив машину.','Почни з явної причини; не замінюй причинний зв’язок простим часовим.'],
            ],
            'advanced-participle-and-absolute-clauses'=>[
                ['The lights|having been|switched off,|the guard|locked the hall.','Коли світло вимкнули, охоронець замкнув зал.','Почни з абсолютної пасивної конструкції зі своїм підметом.'],
                ['After the rehearsal|had ended,|the instruments|were packed.|The players|were tired.|The conductor|thanked everyone.','Після закінчення репетиції інструменти спакували. Музиканти були втомлені. Диригент подякував усім.','Збережи три речення і передування репетиції пакуванню; не називай невідомого пакувальника.'],
            ],
            'discourse-markers-and-cohesion'=>[
                ['The archive|is small;|however,|it contains|every issue|from 2020.','Архів невеликий; проте містить усі випуски за 2020 рік.','Збережи контраст, рік і всі слова; почни з архіву.'],
                ['This colour-based distinction|raises a|further question:|how clearly|would the|three routes|remain distinguishable|in a|black-and-white copy?','Це розрізнення за кольором порушує ще одне питання: наскільки чітко три маршрути залишатимуться розрізнюваними на чорно-білій копії?','Почни з відсилання до кольорового розрізнення; постав питання, не вигадуй результат перевірки.'],
            ],
            'relative-clauses'=>[
                ['The man|who she thanked|is our neighbour.','Чоловік, якому вона подякувала, — наш сусід.','Почни з чоловіка; відносне слово для людини залиш, подякувала саме вона.'],
                ['The students|who grow herbs|meet here.','Учні, які вирощують пряні трави, збираються тут.','Почни з учнів; опис виконує роль відбору й має підмет у множині.'],
            ],
            'defining-relative-clauses'=>[
                ['The box|that Oksana labelled|contains cables.','Коробка, яку підписала Оксана, містить кабелі.','Почни з коробки; опис відбирає підписану Оксаною коробку з кількох.'],
                ['The visitor|whose pass expired|is waiting outside.','Відвідувач, у якого закінчився строк дії перепустки, чекає надворі.','Перепустка належить саме цьому відвідувачеві; почни з нього.'],
            ],
            'non-defining-relative-clauses'=>[
                ['Tania,|who repairs clocks,|works upstairs.','Таня, яка лагодить годинники, працює нагорі.','Таню вже визначено; додай опис її роботи, не замінюй відносне слово.'],
                ['Our tent,|which we bought|in May,|did not leak.','Наш намет, який ми купили в травні, не протікав.','Намет уже визначений; збережи покупців, місяць і заперечення.'],
            ],
            'omitting-relative-pronouns'=>[
                ['The jacket|I tried on|was too small.','Куртка, яку я приміряв, була замала.','Почни з куртки; опусти лише додаткове відносне слово, збережи частку дієслова.'],
                ['The neighbour|we spoke to|owns the garage.','Сусід, з яким ми говорили, володіє гаражем.','Почни із сусіда; опусти відносне слово й залиш прийменник у кінці опису.'],
            ],
            'linking-words'=>[
                ['The seedlings|died because|the heater failed.','Саджанці загинули, тому що обігрівач зламався.','Почни з наслідку; причину подай після нього через підрядну частину.'],
                ['Danylo brought|a ladder|to reach|the shelf.','Данило приніс драбину, щоб дістатися до полиці.','Почни з Данила; передай мету його власної дії, не підтверджуй досягнення.'],
            ],
            'reason-because-since-as'=>[
                ['We did not|open the window|because it was|noisy outside.','Ми не відкрили вікно, бо надворі було шумно.','Почни з нас; шум був причиною рішення не відкривати. Ужий повне заперечення.'],
                ['Since the bus|was late,|we took|a taxi.','Оскільки автобус запізнився, ми взяли таксі.','Почни з відомої причини; збережи минулий час обох подій.'],
            ],
            'result-so-therefore-as-a-result'=>[
                ['The battery|was flat,|so we|could not|turn on|the lamp.','Батарея розрядилася, тому ми не могли ввімкнути лампу.','Почни з батареї; збережи заперечну можливість у наслідку.'],
                ['The lesson|was cancelled,|and therefore|we stayed home.','Урок скасували, і тому ми залишилися вдома.','Почни з уроку; об’єднай частини сполучником і зв’язкою наслідку.'],
            ],
            'contrast-although-though-however'=>[
                ['Though the route|was long,|we did not|turn back.','Хоча маршрут був довгий, ми не повернули назад.','Почни з допустової частини; збережи повне заперечення головної дії.'],
                ['The task|was hard;|however,|we did not|stop.','Завдання було складне; проте ми не зупинилися.','Почни із завдання; передай контраст окремою сполучною обставиною.'],
            ],
            'purpose-to-in-order-to-so-that'=>[
                ['We turned off|the radio|so that Pavlo|could hear|the bell.','Ми вимкнули радіо, щоб Павло міг почути дзвінок.','Вимикали ми; почути мав Павло. Збережи окремий підмет у частині мети.'],
                ['I closed|my bag|in order|not to lose|the tickets.','Я закрив сумку, щоб не загубити квитки.','Почни з мовця; заперечення належить меті, а не закриттю сумки.'],
            ],
            'relative-clauses-with-whose-and-which'=>[
                ['The studio|whose owner|we interviewed|will open|next month.','Студія, у власника якої ми взяли інтерв’ю, відкриється наступного місяця.','Почни зі студії; власник належить саме їй, інтерв’ю вже відбулося.'],
                ['We missed|the last bus,|which delayed|our arrival.','Ми пропустили останній автобус, що затримало наше прибуття.','Почни з нас; відносна частина стосується пропуску автобуса як цілої події.'],
            ],
        ];
    }
    public static function additions():array
    {
        return [
            'linking-words-reason-result-contrast'=>[
                ['gap','The lantern went out {a1} the oil ran out.','because','Тому що; після пропуску вже є повна причина.'],
                ['gap','We continued the survey {a1} the cold wind.','despite','Попри; далі стоїть іменникова група.',['in spite of']],
                ['translation','Хоча ворота були зачинені, Софія знайшла інший вхід. Почни з допустової частини.','Although the gate|was locked,|Sofiia found|another entrance.','Передай реальну перешкоду, яка не завадила результату.'],
                ['translation','Вода замерзла, тому рибалки залишилися на березі. Почни з води.','The water froze,|so the fishermen|stayed on|the bank.','Спочатку назви причину, потім наслідок для рибалок.'],
            ],
            'advanced-linking-devices'=>[
                ['gap','The proposal is acceptable {a1} the costs remain within the limit.','provided that','За умови що; прийнятність залежить від дотримання межі витрат.'],
                ['gap','The figures are outdated; {a1}, they cannot support this forecast.','consequently','Отже; друга частина є наслідком застарілості даних.',['therefore']],
                ['translation','Крім того, довідник містить карту району. Почни зі зв’язки додавання.','Furthermore,|the guidebook|contains a map|of the district.','Додай ще одну властивість довідника, не причинний висновок.',['Moreover, the guidebook contains a map of the district.']],
                ['translation','Зразок придатний лише тією мірою, якою він відображає місцеві умови.','The sample|is useful only|insofar as|it reflects|local conditions.','Збережи обмежений ступінь придатності, а не майбутню умову.'],
            ],
            'concessive-and-contrastive-structures'=>[
                ['gap','{a1} the road was icy, the courier arrived on time.','Even though','Хоча; ожеледь була фактом, а не припущенням.',['Although']],
                ['gap','{a1} losing the first match, the team reached the final.','Despite','Попри; після пропуску вже стоїть назва дії.',['In spite of']],
                ['translation','Ми підемо далі, навіть якщо почнеться дощ. Почни з головної частини.','We will continue|even if|it starts raining.','Дощ поки є можливістю; рішення від нього не залежить.'],
                ['translation','Тоді як Ірина працює вночі, Назар працює вдень. Почни з порівняльної частини.','Whereas Iryna|works at night,|Nazar works|during the day.','Зістав два робочі графіки, не одночасність дій.',['While Iryna works at night, Nazar works during the day.']],
            ],
            'participle-clauses-basics'=>[
                ['gap','The dog {a1} beside the fence belongs to Larysa.','sleeping','Який спить; собака сам виконує цю дію.'],
                ['gap','The chairs {a1} last week are already in the hall.','delivered','Доставлені; стільці є предметами дії, не її виконавцями.'],
                ['translation','Дівчина, яка тримає жовту парасолю, — моя племінниця. Почни з дівчини; використай скорочений активний опис.','The girl|holding a|yellow umbrella|is my niece.','Парасолю тримає саме дівчина; дію передай неособовою формою.'],
                ['translation','Листи, написані від руки, лежать у шухляді. Почни з листів; використай скорочений пасивний опис.','The letters|written by hand|are in|the drawer.','Листи не пишуть самі; назви результат дії над ними.'],
            ],
            'participle-clauses'=>[
                ['gap','{a1} the invoices, Daria sent the payment.','Having checked','Перевіривши; перевірка завершилася раніше за оплату.'],
                ['gap','{a1} about the closure, the visitors used the side entrance.','Having been warned','Будучи попередженими раніше; відвідувачі отримали попередження від інших.'],
                ['translation','Не отримавши відповіді, Олег надіслав нагадування. Почни із заперечної форми передування.','Not having received|a reply,|Oleh sent|a reminder.','Обидві дії стосуються Олега; відсутність відповіді передує нагадуванню.',['Not having received an answer, Oleh sent a reminder.']],
                ['translation','Закінчивши ескіз, Марія показала його клієнтові. Почни з форми передування.','Having finished|the sketch,|Mariia showed it|to the client.','Завершення ескізу передує показу; виконавиця та сама.'],
            ],
            'advanced-participle-and-absolute-clauses'=>[
                ['gap','The guests {a1}, we cleared the table.','having left','Коли гості вже пішли; зворот має власний підмет і передування.'],
                ['gap','The doors {a1}, no one could enter the studio.','locked','Були замкнені; двері є носієм пасивної ознаки.'],
                ['translation','Коли вітер ущух, моряки підняли вітрило. Почни з абсолютної конструкції про вітер із явним передуванням.','The wind|having dropped,|the sailors|raised the sail.','Вітер і моряки — різні підмети; спершу вщух вітер.'],
                ['translation','Оскільки мостом не можна було користуватися, ми обрали довший шлях. Почни з абсолютної конструкції з окремим підметом.','The bridge|being unusable,|we chose|a longer route.','Непридатним був міст, а вибір зробили ми; не змінюй ці ролі.'],
            ],
            'discourse-markers-and-cohesion'=>[
                ['gap','The hall has no lift. {a1}, visitors using wheelchairs cannot reach the upper floor.','Consequently','Отже; друга частина називає наслідок відсутності ліфта.',['Therefore','As a result']],
                ['gap','The first route is shorter. {a1}, the second has fewer steep sections.','However','Проте; зістав дві різні переваги без висновку про найкращий маршрут.'],
                ['translation','У звіті бракує дат. Через цю прогалину важко встановити послідовність подій. Почни друге речення з точної відсилки до прогалини.','The report|lacks dates.|This omission|makes the sequence|of events|hard to establish.','Відсилка має називати відсутність дат, а не вигадану помилку в самих подіях.'],
                ['translation','Посібник містить схеми. Крім того, кожен розділ має підсумок. Почни друге речення зі зв’язки додавання.','The manual|contains diagrams.|Moreover,|each chapter|has a summary.','Друга властивість доповнює першу, не випливає з неї.',['The manual contains diagrams. Furthermore, each chapter has a summary.']],
            ],
            'relative-clauses'=>[
                ['gap','The aunt {a1} teaches biology lives in Sumy.','who','Яка; пропущене слово є виконавцем викладання й позначає людину.',['that']],
                ['gap','The cafe {a1} we had lunch is closed today.','where','Де; назви місце обіду, а не його прямий додаток.'],
                ['translation','Людина, яка знайшла мій гаманець, залишила повідомлення. Почни з людини.','The person|who found|my wallet|left a message.','Людина є виконавцем обох дій; відносний підмет не опускай.',['The person that found my wallet left a message.']],
                ['translation','Це кімната, вікно якої виходить у сад. Почни з вказівки на кімнату.','This is|the room|whose window|faces the garden.','Передай належність вікна кімнаті, а не місце дії.'],
            ],
            'defining-relative-clauses'=>[
                ['gap','The customers who {a1} online receive a receipt by email.','pay','Платять; відносний підмет стосується кількох клієнтів.'],
                ['gap','The cottage {a1} roof is green belongs to my uncle.','whose','Чий; дах є частиною саме цього котеджу.'],
                ['translation','Книжка, яку мені порадила Ліда, має синю обкладинку. Почни з книжки; назви Ліду в описі.','The book|that Lida recommended|has a|blue cover.','Опис визначає потрібну книжку серед інших; Ліда є виконавицею рекомендації.',['The book which Lida recommended has a blue cover.','The book Lida recommended has a blue cover.']],
                ['translation','Водії, які мають перепустки, можуть паркуватися тут. Почни з водіїв.','The drivers|who have passes|can park here.','Обери лише водіїв із перепустками, не всіх водіїв узагалі.',['The drivers that have passes can park here.']],
            ],
            'non-defining-relative-clauses'=>[
                ['gap','My uncle Petro, {a1} lives in Lutsk, is visiting us.','who','Який; дядька вже визначено, додай відомість про людину.'],
                ['gap','The old mill, {a1} roof was repaired in June, is open again.','whose','Чий; назви належність даху вже визначеному млину.'],
                ['translation','Леся, яка вивчає астрономію, приїде в суботу. Лесю вже визначено; почни з імені.','Lesia,|who studies astronomy,|will arrive|on Saturday.','Додай факт про Лесю, не відбирай її з кількох людей.'],
                ['translation','Наш човен, який ми пофарбували навесні, досі не протікає. Почни з човна й збережи повне заперечення.','Our boat,|which we painted|in spring,|still does not|leak.','Човен уже визначений; ми фарбували його, а не навпаки.'],
            ],
            'omitting-relative-pronouns'=>[
                ['gap','The photograph {a1} is on the wall.','Nina took','Яку зробила Ніна; уведи виконавицю й минулу дію без відносного займенника.'],
                ['gap','The colleague I shared an office {a1} has moved away.','with','З яким; збережи прийменник спільного користування офісом.'],
                ['translation','Фільм, який ми дивилися в неділю, був смішний. Почни з фільму й опусти об’єктний відносний займенник.','The film|we watched|on Sunday|was funny.','Підмет підрядної частини збережи; опускається тільки відносне слово.',['The movie we watched on Sunday was funny.']],
                ['translation','Жінка, з якою ти говорив, керує аптекою. Почни з жінки; опусти відносний займенник, залиш прийменник після дієслова.','The woman|you spoke to|runs the pharmacy.','Говорив співрозмовник, а керує аптекою жінка; не міняй ролі.'],
            ],
            'linking-words'=>[
                ['gap','I wrote the address down {a1} I would not forget it.','so that','Щоб; після пропуску вже є окремий підмет і модальне дієслово.'],
                ['gap','The roof leaked, {a1} we moved the books downstairs.','so','Тому; назви наслідок протікання, не його причину.'],
                ['translation','Хоча суп охолов, діти з’їли його. Почни з допустової частини.','Although the soup|was cold,|the children|ate it.','Збережи реальну перешкоду й результат усупереч їй.'],
                ['translation','Віра взяла ліхтарик, тому що в коридорі було темно. Почни з Віри.','Vira took|a torch|because the corridor|was dark.','Темрява пояснює дію Віри; причину подай після головної частини.',['Vira took a flashlight because the corridor was dark.']],
            ],
            'reason-because-since-as'=>[
                ['gap','The concert was delayed {a1} the singer had lost her voice.','because','Тому що; далі наведено повне повідомлення про причину.'],
                ['gap','The ferry stopped running {a1} the fog.','because of','Через; після пропуску стоїть лише назва явища.',['due to']],
                ['translation','Оскільки склад був порожній, ми замовили нові ящики. Почни з причини через відповідник «оскільки».','Since the warehouse|was empty,|we ordered|new crates.','Обидві події минулі; порожній склад є відомою причиною замовлення.'],
                ['translation','Ми залишилися всередині, бо почалася гроза. Почни з нас і назви причину після головної частини.','We stayed inside|because a|thunderstorm began.','Збережи причину перебування всередині без заперечення.'],
            ],
            'result-so-therefore-as-a-result'=>[
                ['gap','The bridge was unsafe, {a1} the police closed the road.','so','Тому; поліція діяла внаслідок небезпечного стану мосту.'],
                ['gap','The lock was broken. {a1}, we could not secure the door.','Therefore','Отже; назви наслідок поломки окремим реченням.',['Consequently','As a result']],
                ['translation','Закінчилося борошно, тому пекар купив ще. Почни з того, що закінчилося.','The flour|ran out,|so the baker|bought more.','Спочатку причина, потім дія пекаря як наслідок.'],
                ['translation','Дорогу затопило. Унаслідок цього автобуси змінили маршрут. Почни друге речення з відповідника «унаслідок цього».','The road|was flooded.|As a result,|the buses|changed their route.','Збережи дві події й явний наслідковий зв’язок.'],
            ],
            'contrast-although-though-however'=>[
                ['gap','{a1} the tickets were expensive, the show sold out.','Although','Хоча; далі повна допустова частина з дієсловом.',['Though','Even though']],
                ['gap','The climb was difficult. {a1}, everyone reached the top.','However','Проте; друге речення суперечить можливому очікуванню невдачі.',['Nevertheless']],
                ['translation','Хоча Денис поспішав, він не забув ключі. Почни з допустової частини й ужий повне заперечення.','Although Denys|was in|a hurry,|he did not|forget the keys.','Поспіх був реальним, але забування не сталося.'],
                ['translation','Квартира маленька; проте в ній є балкон. Почни з квартири й використай окрему зв’язку контрасту.','The flat|is small;|however,|it has|a balcony.','Збережи обидві властивості, не перетворюй одну на причину іншої.',['The apartment is small; however, it has a balcony.']],
            ],
            'purpose-to-in-order-to-so-that'=>[
                ['gap','Maksym saved the receipt {a1} return the jacket if necessary.','to','Щоб; далі початкова форма дії, виконавець той самий.',['in order to']],
                ['gap','I lowered my voice {a1} wake the baby.','in order not to','Щоб не; мета — уникнути пробудження, уведи всю заперечну групу.',['so as not to']],
                ['translation','Ми відсунули стілець, щоб Ілля міг відчинити шафу. Почни з нас; назви Іллю в окремій частині мети.','We moved|the chair|so that Illia|could open|the cupboard.','Стілець пересунули ми; відчинити шафу мав Ілля.',['We moved the chair so that Illia could open the cabinet.']],
                ['translation','Я вдягнув рукавички, щоб не обпекти руки. Почни з мовця; використай повну заперечну конструкцію мети.','I put on|gloves in order|not to burn|my hands.','Рукавички вдягнуто; заперечене лише бажане уникнення опіку.'],
            ],
            'relative-clauses-with-whose-and-which'=>[
                ['gap','The workshop {a1} windows face east is on this street.','whose','Чиї; вікна належать саме майстерні, не сусідній будівлі.'],
                ['gap','The station closed early, {a1} surprised the passengers.','which','Що; відсилка стосується факту раннього закриття, не самих пасажирів.'],
                ['translation','Ми зустріли скульпторку, чиї роботи виставлені в музеї. Почни з нас.','We met|a sculptor|whose works|are displayed|in the museum.','Твори належать скульпторці; не підмінюй належність особовим займенником.'],
                ['translation','Софія загубила перепустку, що затримало її вхід. Почни із Софії.','Sofiia lost|her pass,|which delayed|her entry.','Причиною затримки був факт втрати; відносна частина описує всю ситуацію.'],
            ],
        ];
    }
}
