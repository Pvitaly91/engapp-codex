<?php

namespace App\Support;

/** Finite reviewed native copy; retained source answers remain unchanged. */
final class CommonMistakesNativePlans
{
    public static function retained():array
    {
        return [
            'articles-common-mistakes'=>[
                ['I saw|a teacher|at the station.','Я побачив на вокзалі одну вчительку.','Почни з мовця; це перша згадка про одну невідому слухачеві людину.'],
                ['I love coffee,|but I|do not|drink it|at night.','Я люблю каву, але не п’ю її вночі.','Кава — напій загалом; збережи протиставлення й нічне заперечення.'],
            ],
            'present-perfect-common-mistakes'=>[
                ['My sister|has been|to Paris|three times.','Моя сестра тричі побувала в Парижі.','Сестра повернулася після кожного візиту й зараз удома; почни з неї.'],
                ['I have not|finished the report|yet.','Я ще не закінчив звіт.','Звіт досі не готовий, завершення очікується; збережи заперечення.'],
            ],
            'countable-vs-uncountable-nouns-common-mistakes'=>[
                ['I need|one piece|of advice.','Мені потрібна одна порада.','Потрібна рівно одна рекомендація; назви злічувану одиницю поради.'],
                ['She gave me|a lot of|information.','Вона дала мені багато інформації.','Збережи минулу дію та одержувача; інформація тут незлічувана.'],
            ],
            'prepositions-common-mistakes'=>[
                ['We arrived|at the airport|late.','Ми пізно прибули до аеропорту.','Почни з нас; прибуття відбулося, аеропорт — пункт маршруту.'],
                ['She walked|into the room.','Вона увійшла до кімнати.','Вона була в коридорі та перетнула поріг; назви саме перехід усередину.'],
            ],
            'b1-b2-grammar-traps'=>[
                ['You should have|called me|last night.','Тобі слід було зателефонувати мені вчора ввечері.','Це дорікання за невиконаний минулий дзвінок; почни зі співрозмовника.'],
                ['Rarely|do we|see such|clear feedback.','Рідко ми бачимо такий зрозумілий відгук.','Почни з прислівника рідкісної події; у формальній моделі потрібна інверсія.'],
            ],
        ];
    }

    public static function additions():array
    {
        return [
            'articles-common-mistakes'=>[
                ['gap','This is {a1} useful tool.','a','Один корисний інструмент згадано вперше; перший звук прикметника приголосний.'],
                ['gap','I need {a1} umbrella because it is raining.','an','Потрібна одна будь-яка парасоля; її назва починається з голосного звука.'],
                ['translation','Його батько — водій автобуса. Почни з його батька; назви професію.','His father|is a|bus driver.','Після дієслова-зв’язки назви одну професію зі злічуваним іменником.'],
                ['translation','Ми чекали біля входу до вже згаданого парку. Почни з нас.','We waited|at the entrance|to the park.','Парк відомий слухачеві, вхід належить цьому парку; збережи минулий час.'],
            ],
            'present-perfect-common-mistakes'=>[
                ['gap','Oleh {a1} never driven a car.','has','Ніколи не керував автомобілем донині; потрібне допоміжне дієслово для однієї людини.'],
                ['gap','We have known each other {a1} ten years.','for','Уже десять років; названо тривалість знайомства, а не початкову дату.'],
                ['translation','Вона ще не погодувала кролика. Почни з неї й передай незавершеність донині.','She has not|fed the rabbit|yet.','Годування досі не відбулося, але очікується; збережи заперечення.'],
                ['translation','Ми щойно замкнули хвіртку. Почни з нас і використай повні форми.','We have just|locked the gate.','Хвіртка вже замкнена; дія відбулася безпосередньо перед повідомленням.'],
            ],
            'countable-vs-uncountable-nouns-common-mistakes'=>[
                ['gap','Two {a1} are sleeping on the sofa.','children','Діти: потрібна неправильна форма множини, не звичайне додавання закінчення.'],
                ['gap','I bought two {a1} of bread.','loaves','Два цілі буханці, не скибки; утвори множину назви буханця.'],
                ['translation','Трохи рису достатньо для цього супу. Почни з кількості рису.','A little rice|is enough|for this soup.','Рис незлічуваний; мала кількість тут достатня, не відсутня чи недостатня.',['Some rice is enough for this soup.']],
                ['translation','Ми купили два шматки сиру. Почни з нас і збережи минулий час.','We bought|two pieces|of cheese.','Сир — речовина; порахуй дві окремі частини, а не види сиру.',['We bought two chunks of cheese.']],
            ],
            'prepositions-common-mistakes'=>[
                ['gap','Are you worried {a1} your exam?','about','Через іспит: потрібний залежний прийменник після прикметника занепокоєння.'],
                ['gap','She is married {a1} a nurse.','to','З медпрацівником: назви залежний прийменник для сімейного статусу.'],
                ['translation','Він слухає радіо під час сніданку. Почни з нього й передай звичну дію.','He listens|to the radio|during breakfast.','Після дієслова слухання потрібний прийменник; сніданок називає час дії.',['He listens to the radio at breakfast.','He listens to the radio over breakfast.']],
                ['translation','Ми заплатили за обід учора. Почни з нас; назви прийом їжі без окремого означника.','We paid|for lunch|yesterday.','Збережи минулу оплату; після дієслова потрібний прийменник предмета оплати.'],
            ],
            'b1-b2-grammar-traps'=>[
                ['gap','If we had a bigger kitchen, we {a1} cook together.','would','Кухня зараз не більша; передай уявний наслідок теперішньої ситуації.'],
                ['gap','He {a1} be at work: his bike is outside the office.','must','Мовець упевнено робить висновок із видимого доказу, не називає просту можливість.'],
                ['translation','Вона відчинила пекарню, хоча була втомлена. Почни з неї.','She opened|the bakery|although she|was tired.','Та сама жінка відчинила пекарню попри втому; збережи обидві минулі дії.',['She opened the bakery though she was tired.','She opened the bakery even though she was tired.']],
                ['translation','Якби я жив ближче, я ходив би на роботу пішки. Почни з умови про місце проживання.','If I lived|closer,|I would walk|to work.','Це уявна теперішня ситуація, не втрачена нагода в минулому.',['If I lived nearer, I would walk to work.']],
            ],
        ];
    }
}
