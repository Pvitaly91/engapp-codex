<?php

namespace App\Support;

/** Finite teaching copy for the six revision owners; no styling or grading policy. */
final class MixedRevisionNativePlans
{
    public static function retained():array
    {
        return [
            'a1-mixed-revision'=>[
                ['Yesterday,|we went|to the park.','Учора ми пішли до парку.','Почни з учорашнього дня; далі назви нас, минулу дію та напрямок.'],
                ['I will buy|some apples|tomorrow.','Я куплю трохи яблук завтра.','Це рішення в момент мовлення; почни з мовця, назви невизначену кількість і постав час наприкінці.'],
            ],
            'a2-mixed-revision'=>[
                ['Olia said|that|she was tired.','Учора Оля сказала: «Я втомлена». Передай сьогодні її тодішній стан непрямою мовою.','Назви Олю на початку; її займенник першої особи заміни формою третьої особи. Сполучник можна пропустити.'],
                ['The windows|are cleaned|by Ivan|every Friday.','Вікна миє Іван щоп’ятниці. Передай це пасивною конструкцією.','Почни з вікон; назви виконавця й збережи регулярність. Час можна поставити перед виконавцем або після нього.'],
            ],
            'b2-mixed-revision'=>[
                ['Ivan said|that|he was tired.','Учора Іван сказав: «Я втомлений». Передай сьогодні його тодішній стан непрямою мовою.','Назви Івана на початку; не повідомляй окремо про його стан зараз. Сполучник можна пропустити.'],
                ['The implementation|of the plan,|which we approved,|will take time.','Виконання плану, який ми схвалили, потребуватиме часу.','Почни з іменникової назви виконання; план уже визначений, а підрядна частина лише додає відомості.'],
            ],
        ];
    }

    /** kind, condition, canonical phrase/groups, UK hint, finite additional accepted surfaces. */
    public static function additions():array
    {
        return [
            'a1-mixed-revision'=>[
                ['gap','Our small fridge {a1} empty.','is','Зараз немає продуктів: додай форму дієслова-зв’язки для однини.'],
                ['gap','{a1} your grandparents keep hens?','Do','Запитай про звичку тримати курей; потрібне лише допоміжне дієслово теперішнього часу.'],
                ['translation','Мій білий кролик зазвичай спить під лавкою.','My white rabbit|usually sleeps|under the bench.','Почни з мого білого кролика; передай звичку спати та місце під лавкою.',[
                    'My white rabbit normally sleeps under the bench.','My white rabbit generally sleeps under the bench.',
                    'My white rabbit usually sleeps beneath the bench.','My white rabbit normally sleeps beneath the bench.','My white rabbit generally sleeps beneath the bench.',
                ]],
                ['translation','У нашої сусідки є два сірі кошенята.','Our neighbour|has two|grey kittens.','Почни з нашої сусідки й назви володіння, точну кількість та колір; обидва нормативні написання приймаються.',[
                    'Our neighbor has two grey kittens.','Our neighbour has two gray kittens.','Our neighbor has two gray kittens.',
                    'Our neighbour has got two grey kittens.','Our neighbor has got two grey kittens.','Our neighbour has got two gray kittens.','Our neighbor has got two gray kittens.',
                ]],
            ],
            'a2-mixed-revision'=>[
                ['gap','Olena {a1} the old windmill twice.','has visited','Відвідала двічі донині: уведи допоміжну форму та дієслово відвідання в доконаному часі.'],
                ['gap','If it snows, we {a1} the picnic indoors.','will have','Влаштуємо пікнік у приміщенні за цієї майбутньої умови: уведи всю майбутню дієслівну частину.',['will hold']],
                ['translation','Наш старий папуга взагалі не говорив учора.','Our old parrot|did not speak|at all yesterday.','Назви папугу, повністю запереч минулу дію й збережи підсилення та вчорашній час.',[
                    'Our old parrot did not talk at all yesterday.','Our old parrot did not speak yesterday at all.','Our old parrot did not talk yesterday at all.',
                ]],
                ['translation','Ми збираємося прикрасити цей коридор у неділю.','We are|going to decorate|this corridor|on Sunday.','Передай вже наявний намір через конструкцію запланованої дії; час постав наприкінці.',['We are going to decorate this hallway on Sunday.']],
            ],
            'b1-mixed-revision'=>[
                ['gap','The cracked bowl {a1} by a specialist last Friday.','was restored','Відреставрували минулої п’ятниці: уведи всю минулу пасивну форму відновлення.',['was repaired']],
                ['gap','The beekeeper asked whether the bees {a1} enough food.','had','Мали достатньо їжі в той момент: після непрямого запитання встав лише минулу форму володіння.'],
                ['translation','Нам не дозволили підходити до краю скелі.','We were not|allowed to approach|the edge|of the cliff.','Почни з нас; передай минулу відсутність дозволу пасивом і назви край скелі.',[
                    'We were not permitted to approach the edge of the cliff.','We were not allowed to go near the edge of the cliff.','We were not permitted to go near the edge of the cliff.',
                ]],
                ['translation','Якби в мене була майстерня, я виготовляв би дерев’яні іграшки.','If I had|a workshop,|I would make|wooden toys.','Це уявна теперішня можливість, не минулий факт; почни з умови.',[
                    'If I had a workshop, I would craft wooden toys.','If I had a workshop, I would produce wooden toys.',
                ]],
            ],
            'b2-mixed-revision'=>[
                ['gap','The choir would have sounded clearer if the microphones {a1} correctly.','had been positioned','Покажи невиконану попередню умову минулого пасивною доконаною формою розміщення.',['had been placed']],
                ['gap','Not only {a1} the records, but she also corrected the index.','did Marta check','Після початкового обмежувального звороту назви Марту між минулим допоміжним дієсловом і початковою формою перевірки.'],
                ['translation','Якби ж ми перевірили акустику до концерту.','If only|we had checked|the acoustics|before the concert.','Передай жаль про невиконану минулу перевірку; не перетворюй його на реальний майбутній план.',['I wish we had checked the acoustics before the concert.']],
                ['translation','Архіваріус попросив мене не скріплювати оригінали степлером.','The archivist|asked me|not to staple|the originals.','Почни з архіваріуса; збережи мовця як адресата прохання та заперечення самої дії.',['The archivist requested me not to staple the originals.']],
            ],
            'c1-mixed-revision'=>[
                ['gap','Only when the audit had ended {a1} the seal had been broken.','did we discover','У головній частині інвертуй минулий допоміжний елемент і займенник «ми», потім передай «виявили» або «дізналися».',[
                    'did we find out','did we realise','did we realize','did we learn',
                ]],
                ['gap','The sealed ballot boxes are believed {a1} before the observers arrived.','to have been tampered with','Припущення стосується вже здійсненого стороннього втручання в урни; потрібен доконаний пасивний інфінітив із прийменниковим завершенням.'],
                ['translation','Якби не вчорашня затримка, наш висновок зараз був би іншим.','Had it not|been for|yesterday’s delay,|our conclusion|would be different|now.','Почни з нереалізованої минулої умови; наслідок стосується теперішнього висновку. Нормативні іменникові конструкції умови також приймаються.',[
                    'But for yesterday’s delay, our conclusion would be different now.','Without yesterday’s delay, our conclusion would be different now.',
                ]],
                ['translation','Комітет наполіг, щоб незалежний експерт оцінив докази.','The committee|insisted that|an independent expert|assess|the evidence.','Передай вимогу, а не твердження про вже зроблену оцінку; обидві нормативні моделі вимоги приймаються.',[
                    'The committee insisted that an independent expert evaluate the evidence.',
                    'The committee insisted that an independent expert should assess the evidence.','The committee insisted that an independent expert should evaluate the evidence.',
                    'The committee insisted an independent expert assess the evidence.','The committee insisted an independent expert evaluate the evidence.',
                    'The committee insisted an independent expert should assess the evidence.','The committee insisted an independent expert should evaluate the evidence.',
                ]],
            ],
            'c2-mixed-revision'=>[
                ['gap','Had the liquidity reserve been disclosed, the board {a1} the emergency sale.','might not have authorised','Передай лише можливість, що рада не дала б дозволу за іншої минулої умови; не стверджуй певного наслідку. Обидва нормативні написання приймаються.',['might not have authorized']],
                ['gap','The inference remains provisional, there {a1} no independent replication.','having been','Подай попередню відсутність незалежного відтворення як причину через доконаний абсолютний зворот; наведений формальний підмет не повторюй.'],
                ['translation','Дані, якби їх оприлюднили раніше, могли б змінити трактування свідчень.','The data,|had they been|released earlier,|might have changed|the interpretation|of the testimony.','Йдеться про усні свідчення, а не будь-які докази. Збережи вставну інверсію нереалізованої минулої умови та лише можливий наслідок.',self::dataAliases()],
                ['translation','Не можна виключити, що зміна облікової політики вплинула на зіставність цих цифр.','It cannot be|ruled out that|the change|in accounting policy|has affected|the comparability|of these figures.','Збережи обережну безособову оцінку; не подавай вплив як доведений факт. Минулий час і доконаний час із теперішньою актуальністю приймаються.',[
                    'It cannot be ruled out that the change in accounting policy affected the comparability of these figures.',
                    'It cannot be ruled out that changing the accounting policy has affected the comparability of these figures.',
                    'It cannot be ruled out that changing the accounting policy affected the comparability of these figures.',
                    'It cannot be excluded that the change in accounting policy has affected the comparability of these figures.',
                    'It cannot be excluded that the change in accounting policy affected the comparability of these figures.',
                    'It cannot be excluded that changing the accounting policy has affected the comparability of these figures.',
                    'It cannot be excluded that changing the accounting policy affected the comparability of these figures.',
                ]],
            ],
        ];
    }

    private static function dataAliases():array
    {
        $out=[];foreach(['released','disclosed','published']as$verb)foreach(['earlier','sooner']as$time)foreach(['might','could']as$modal){$s='The data, had they been '.$verb.' '.$time.', '.$modal.' have changed the interpretation of the testimony.';if($s!=='The data, had they been released earlier, might have changed the interpretation of the testimony.')$out[]=$s;}return$out;
    }

    public static function authoredHints():array
    {
        return [
            'm40-b3-question'=>'Це минуле непряме запитання; почни з Міли, збережи мовця як адресата та зміни порядок на розповідний.',
            'm40-b3-instruction'=>'Передай минулу заборонну вказівку мовцеві інфінітивною конструкцією із запереченням.',
            'm40-b4-passive'=>'Почни з коробок; збережи модальну необхідність і названих волонтерів як виконавців.',
            'm40-b4-causative'=>'Мовець організував ремонт велосипеда вчора; не стверджуй, що він сам ремонтував.',
            'm40-b5-relative'=>'Ім’я вже визначає особу; додай неозначальну відносну частину й збережи згоду допомогти.',
            'm40-b5-although'=>'Почни з поступової обставини пізнього часу й збережи факт продовження.',
            'm40-b6-wish'=>'Мовець шкодує про теперішній розмір столу; передай бажану, а не фактичну ситуацію.',
            'm40-b6-conditional'=>'Почни з невиконаної минулої умови й збережи нереалізований наслідок із автобусом.',
            'm40-b6-might'=>'Передай лише можливе теперішнє місце Лени, не певне знання про нього.',
            'c1-1-answer'=>'Минула умова не виконалася; наслідок стосується можливості відновлення саме зараз.',
            'c1-2-answer'=>'Почни з обмеження часу; після всієї обставини потрібна інверсія головної пасивної частини.',
            'c1-3-answer'=>'Передай припущення, не власне доведений факт; відкриття сталося раніше за повідомлення.',
            'c1-4-answer'=>'Саме завершення курсу відбирає двох гідів; збережи означальну частину та теперішнє проведення екскурсії.',
            'c1-5-answer'=>'Збережи лише можливість учорашнього надсилання й окремий факт неперевіреної скриньки.',
            'c1-6-answer'=>'Збережи три повідомлення: учора перевірили дати; описи ще не перевірено; остаточний файл ввічливо просять до п’ятниці.',
            'c2-1-answer'=>'Почни з інвертованої заперечної минулої умови; можливість застрягти не є твердженням про те, що це сталося.',
            'c2-2-answer'=>'Збережи пасивне повідомлення про припущення та попередність переміщення портретів.',
            'c2-3-a'=>'Непотрібний друк відбувся; передай зайвість уже виконаної дії доконаною модальною формою.',
            'c2-4-scope'=>'Українською назви саме відсутню кількісну інформацію; не приписуй точного числа.',
            'c2-5-answer'=>'Збережи повноту списку людей, два записи без телефонів і однозначне посилання саме на ці записи.',
            'c2-6-answer'=>'Збережи учасника, перший зразок, понеділок, успіх у приміщенні; другий зразок не перевіряли, зовнішнє випробування не проводили.',
        ];
    }
}
