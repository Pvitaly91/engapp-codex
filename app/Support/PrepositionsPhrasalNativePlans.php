<?php

namespace App\Support;

/** Finite reviewed conditions and logical one-to-three-word groups, without source rewriting. */
final class PrepositionsPhrasalNativePlans
{
    public static function retained():array
    {
        return [
            'prepositions-of-time'=>[
                ["We don't|work next Monday.",'Ми не працюємо наступного понеділка.','Почни з нас; збережи заперечення й не додавай прийменник до самостійної часової обставини.'],
                ['Do you|visit your grandparents|at the weekend?','Ти відвідуєш своїх бабусю й дідуся у вихідні?','Постав загальне питання про звичку; збережи належність родичів співрозмовнику.'],
            ],
            'prepositions-of-place'=>[
                ['Where are|the keys?','Де ключі?','Почни з питального слова; ключі — уже визначені предмети в множині.'],
                ['The children|are at home.','Діти вдома.','Почни з дітей; назви перебування вдома без руху до нього.'],
            ],
            'prepositions-of-movement'=>[
                ['We walked|towards the station.','Ми йшли в напрямку вокзалу.','Почни з нас; напрямок не підтверджує завершеного прибуття.'],
                ['Do not run|out of|the house.','Не вибігай із будинку.','Почни з повної заперечної вказівки; рух відбувається зсередини назовні.'],
            ],
            'dependent-prepositions'=>[
                ['What does|the decision|depend on?','Від чого залежить рішення?','Почни з питального слова; прийменник залежності залиш у кінці.'],
                ['We need|an answer|to this question.','Нам потрібна відповідь на це запитання.','Почни з нас; відпрацюй прийменникову модель іменника «відповідь».'],
            ],
            'phrasal-verbs-basics'=>[
                ['Please turn|it off.','Будь ласка, вимкни її.','Лампу вже названо; заміни її займенником між дієсловом і часткою.'],
                ['She picked up|the bag.','Вона підняла сумку.','Йдеться про фізичне підняття сумки з підлоги; почни з неї.'],
            ],
            'separable-and-inseparable-phrasal-verbs'=>[
                ['She picked|the box up.','Вона підняла коробку.','Почни з неї; іменниковий додаток може стояти перед часткою або після неї.'],
                ['Did you|write it down?','Ти його записав?','Йдеться про відомий номер телефону; займенник постав між дієсловом і часткою.'],
            ],
            'common-phrasal-verbs-by-topic'=>[
                ['We set off|for Lviv|at dawn.','Ми вирушили до Львова на світанку.','Почни з нас; назви початок подорожі, не її доведене завершення.'],
                ['She didn’t|log in|to the system.','Вона не ввійшла до системи.','Почни з неї; збережи минуле заперечення й прийменникову модель входу.'],
            ],
        ];
    }
    public static function additions():array
    {
        return [
            'prepositions-of-time'=>[
                ['gap','The last bus leaves {a1} midnight.','at','Опівночі; потрібний прийменник перед точною часовою точкою.'],
                ['gap','I visit my aunt {a1} August.','in','У серпні; названо місяць, не конкретний день.'],
                ['translation','Мій урок англійської у вівторок. Почни з мого уроку.','My English lesson|is on Tuesday.','Перед назвою дня тижня потрібний прийменник; урок один.'],
                ['translation','Я не воджу вночі. Почни з мовця й використай повне заперечення.','I do not|drive at night.','Запереч звичну дію; обставина називає ніч як період доби.'],
            ],
            'prepositions-of-place'=>[
                ['gap','The cat is sleeping {a1} the roof.','on','На поверхні даху; кіт не перебуває всередині будівлі.'],
                ['gap','I am waiting {a1} the bus stop.','at','На зупинці; назви місце очікування як пункт, не внутрішній простір.'],
                ['translation','Валіза під лавкою. Почни з валізи.','The suitcase|is under|the bench.','Назви положення нижче лавки, не поруч із нею.',['The suitcase is beneath the bench.','The suitcase is underneath the bench.']],
                ['translation','Дзеркало між вікнами. Почни з дзеркала.','The mirror|is between|the windows.','Два вікна є окремими орієнтирами з обох боків дзеркала.'],
            ],
            'prepositions-of-movement'=>[
                ['gap','They walked {a1} the bridge to the opposite bank.','across','Через міст на інший берег; рух перетинає його від одного кінця до другого.'],
                ['gap','The cyclist rode {a1} the tunnel and emerged at the other end.','through','Крізь тунель; рух іде його внутрішнім простором до іншого виходу.'],
                ['translation','Ми забігли всередину укриття. Почни з нас.','We ran|into the shelter.','Рух почався зовні й закінчився всередині; це не просте місце перебування.'],
                ['translation','Кіт перестрибнув через паркан. Почни з кота.','The cat|jumped over|the fence.','Рух проходить над перешкодою з одного боку на інший.'],
            ],
            'dependent-prepositions'=>[
                ['gap','Sofiia apologised {a1} being late.','for','За запізнення; назви причину вибачення після дієслова.'],
                ['gap','He insists {a1} checking every ticket.','on','Наполягає на перевірці; далі вже стоїть назва дії.'],
                ['translation','Ти усвідомлюєш ці ризики? Почни з форми дієслова-зв’язки для питання.','Are you|aware of|these risks?','Запитай про обізнаність; після прикметника потрібний його залежний прийменник.'],
                ['translation','Я цікавлюся місцевою історією. Почни з мовця й використай повну форму дієслова-зв’язки.','I am interested|in local history.','Передай власний інтерес прикметниковою моделлю, не вплив історії на інших.'],
            ],
            'phrasal-verbs-basics'=>[
                ['gap','The alarm went {a1} at five and woke us.','off','Пролунав; сигнал розбудив нас, а не просто перестав працювати.'],
                ['gap','Please take {a1} your wet coat before sitting down.','off','Зніми мокре пальто; йдеться про одяг, не початок польоту.'],
                ['translation','Я зупинив ремонт, бо в мене закінчився клей. Почни з мовця; для клею використай фразову модель вичерпання запасу.','I stopped|the repair|because I|ran out of|glue.','Клей закінчився в мовця; збережи причину зупинки ремонту.',['I stopped the repair because I ran out of adhesive.']],
                ['translation','Вона відхилила пропозицію орендувати гараж. Почни з неї й використай фразове дієслово відмови.','She turned down|the offer|to rent|the garage.','Відхилено саме пропозицію; не додавай повідомлення про виконану оренду.'],
            ],
            'separable-and-inseparable-phrasal-verbs'=>[
                ['gap','The printer is too heavy. Please help me lift {a1}.','it up','Підняти його; займенник називає вже згаданий принтер і стоїть перед часткою.'],
                ['gap','The patients need someone to look {a1} during the night.','after them','Доглянути за ними; займенник іде після нероздільної сполуки.'],
                ['translation','Поклади ці валізи. Почни з наказу; використай фразове дієслово зі значенням опускання.','Put these|suitcases down.','Іменниковий додаток можна поставити між дієсловом і часткою або після неї.',['Put down these suitcases.']],
                ['translation','Ти вимкнув його перед обідом? Йдеться про названий принтер; почни з допоміжного дієслова минулого питання.','Did you|switch it off|before lunch?','Принтер заміни займенником; частка має йти після нього.',['Did you turn it off before lunch?']],
            ],
            'common-phrasal-verbs-by-topic'=>[
                ['gap','Our flight leaves soon. We need to check {a1} now.','in','Зареєструватися на рейс; йдеться не про перевірку предмета.'],
                ['gap','Before deleting the account, please log {a1}.','out','Вийди з облікового запису; це дія виходу, не входу.'],
                ['translation','Вони відкрили спільний рахунок, щоб відкладати гроші на ремонт. Почни з них; для відкладання використай фразове дієслово.','They opened|a joint account|to put aside|money for repairs.','Йдеться про заощадження на майбутні витрати, не фізичне відкладання купюр убік.',['They opened a joint account to set aside money for repairs.']],
                ['translation','Літак приземлився, але ми ще не вийшли з нього. Почни з літака й ужий повне заперечення.','The plane landed,|but we|have not|got off yet.','Посадка вже відбулася; вихід пасажирів досі не відбувся.',['The plane landed, but we have not gotten off yet.']],
            ],
        ];
    }
}
