<?php

namespace App\Support;

/** Finite teaching instructions and reviewed one-to-three-word answer groups. */
final class ReportedSpeechNativePlans
{
    public static function retained():array
    {
        return [
            'reported-statements'=>[
                ['Olena said that|she would bring|the map|the next day.','Олена сказала, що принесе карту наступного дня.','Почни з Олени; збережи повідомлення й відлік часу від її слів.'],
                ['Danylo said that|he was|not hungry.','Данило сказав, що він не голодний.','Заперечення стосується стану Данила, а не того, чи він говорив.'],
            ],
            'reported-questions'=>[
                ['Taras asked|if Olena|had locked|the gate.','Тарас запитав, чи Олена замкнула хвіртку.','Збережи питальне значення й прямий порядок слів усередині переказу.'],
                ['Did Iryna ask|where Petro worked?','Чи Ірина запитала, де працював Петро?','Зовнішня частина є питанням; внутрішня має прямий порядок слів.'],
            ],
            'reported-commands-and-requests'=>[
                ['Marta asked Oksana|to carry|the box.','Марта попросила Оксану понести коробку.','Почни з Марти; виконавицею прохання залишається Оксана.'],
                ['The coach|told us|not to run|to the door.','Тренер сказав нам не бігти до дверей.','Заперечення постав перед інфінітивом дії, яку заборонено.'],
            ],
            'backshift-of-tenses'=>[
                ['Sofiia said that|she had not|finished the drawing.','Софія сказала, що ще не закінчила малюнок.','Передай незавершеність до минулого повідомлення; збережи заперечення.'],
                ['Danylo said that|we should keep|the receipt.','Данило сказав, що нам слід зберегти чек.','Переказ поради; модальне слово тут не змінюється.'],
            ],
            'time-and-place-changes'=>[
                ['Iryna said that|she had received|the parcel|the day before.','Ірина сказала, що отримала посилку напередодні.','Почни з Ірини; час отримання відраховуй від її минулої репліки.'],
                ['Taras said that|that shop|would open|the next day.','Тарас сказав, що той магазин відчиниться наступного дня.','Збережи і сполучник повідомлення, і вказівне слово перед магазином.'],
            ],
            'say-tell-ask'=>[
                ['Marta asked Ihor|if he needed|a torch.','Марта запитала Ігоря, чи потрібен йому ліхтарик.','Почни з Марти; Ігор є адресатом питання та власником потреби.'],
                ['The assistant|told us|not to move|the boxes.','Помічник сказав нам не пересувати коробки.','Переказ заперечної вказівки; адресат і виконавець — ми.'],
            ],
            'advanced-reported-speech'=>[
                ['Danylo promised|not to publish|the photograph.','Данило пообіцяв не публікувати фотографію.','Стверди обіцянку, але запереч дію публікації.'],
                ['Olha apologised|for damaging|the cover.','Ольга перепросила за те, що пошкодила обкладинку.','Причину вибачення передай прийменником і формою дії із закінченням.'],
            ],
        ];
    }
    public static function additions():array
    {
        return [
            'reported-statements'=>[
                ['gap','Rita said that she {a1} near the bakery.','lived','Мешкала; передай стан із минулої точки повідомлення.'],
                ['gap','The boys said that they {a1} the tent.','would not sell','Не продадуть; уведи повну заперечну групу майбутнього в минулому.'],
                ['translation','Сергій сказав, що він утомився. Почни з імені; використай сполучник повідомлення.','Serhii said that|he was tired.','Почни із Сергія; описуваний стан належить йому.'],
                ['translation','Ніна сказала, що допоможе нам. Передай майбутню дію з минулої точки повідомлення.','Nina said that|she would|help us.','Збережи виконавицю допомоги та названих адресатів.'],
            ],
            'reported-questions'=>[
                ['gap','The guard asked where we {a1}.','were going','Куди прямували; питальне слово й підмет уже наведені.'],
                ['gap','I asked whether Nina {a1} the charger.','had brought','Чи принесла раніше; передай дію до моменту минулого питання.'],
                ['translation','Юрко запитав, де були його рукавички. Почни з імені.','Yurko asked|where his gloves|were.','У переказаному питанні спочатку назви рукавички, потім їхній стан.'],
                ['translation','Леся запитала, чи ми готові. Передай питання через «чи» з минулої точки; готовими були ми.','Lesia asked if|we were ready.','Після сполучника потрібен прямий порядок слів.',['Lesia asked whether we were ready.']],
            ],
            'reported-commands-and-requests'=>[
                ['gap','The librarian asked me {a1} my voice.','to lower','Говорити тихіше; уведи інфінітив дії після названого адресата.'],
                ['gap','Dad told us {a1} the wet paint.','not to touch','Не торкатися; уведи повну заперечну інфінітивну групу.'],
                ['translation','Віра попросила мене зачинити вікно. Почни з Віри.','Vira asked me|to close|the window.','Я є адресатом і виконавцем прохання.',['Vira asked me to shut the window.']],
                ['translation','Медсестра сказала дітям не бігати коридором. Почни з медсестри.','The nurse|told the children|not to run|in the corridor.','Збережи дітей як виконавців дії; заперечення належить бігу.'],
            ],
            'backshift-of-tenses'=>[
                ['gap','My aunt said that she {a1} a scarf at that moment.','was knitting','В’язала в той момент; передай процес із минулої точки повідомлення.'],
                ['gap','The pilot said that the storm {a1}.','had ended','Уже закінчилася до повідомлення; потрібна група передування.'],
                ['translation','Роман сказав, що бачив того птаха раніше. Передай передування минулому повідомленню.','Roman said that|he had seen|that bird before.','Спостереження відбулося раніше за слова Романа.'],
                ['translation','Тетяна сказала, що може полагодити замок. Передай можливість із минулої точки повідомлення.','Tetiana said that|she could repair|the lock.','Почни з Тетяни; здатність належить їй.',['Tetiana said that she could fix the lock.']],
            ],
            'time-and-place-changes'=>[
                ['gap','Orest said that he would return {a1}.','the following week','Наступного тижня після його слів; переказ відбувається пізніше.',['the next week']],
                ['gap','The twins said that they had met her {a1}.','two days earlier','За два дні до їхніх слів; уведи повну обставину минулого.',['two days before']],
                ['translation','Оля сказала, що була там напередодні. Переказ відбувається пізніше в іншому місці; збережи сполучник.','Olia said that|she had been|there|the previous day.','Місце віддалене від нинішнього мовця, день — перед попередніми словами.',['Olia said that she had been there the day before.']],
                ['translation','Лев сказав, що надішле листа наступного місяця. Передай це пізніше, після зміни календарної точки.','Lev said that|he would send|the letter|the following month.','Почни з Лева; місяць відраховуй від його репліки.',['Lev said that he would send the letter the next month.']],
            ],
            'say-tell-ask'=>[
                ['gap','Nazar {a1} me that the ladder was broken.','told','Повідомив мені; адресат уже стоїть після пропуску.'],
                ['gap','Lina {a1} whether the shop was open.','asked','Запитала; далі наведено зміст питання, не наказу.'],
                ['translation','Богдан сказав нам правду про човен. Почни з Богдана.','Bohdan told us|the truth|about the boat.','Передай повідомлення з прямо названим адресатом.'],
                ['translation','Орися запитала, де я залишив велосипед. У переказі «я» — нинішній оповідач; збережи минуле передування.','Orysia asked|where I|had left|the bicycle.','Питальне слово збережи, але не роби інверсії всередині повідомлення.',['Orysia asked where I had left the bike.']],
            ],
            'advanced-reported-speech'=>[
                ['gap','The gardener denied {a1} the young tree.','damaging','Заперечив, що пошкодив; після дієслова заперечення потрібна форма назви дії.'],
                ['gap','Eva reminded me {a1} the invoice.','to attach','Прикріпити; я є адресатом нагадування і виконавцем дії.'],
                ['translation','Соломія погодилася перевірити чернетку. Почни з імені; не називай іншого виконавця.','Solomiia agreed|to check|the draft.','Згоду і майбутню дію приписуй одній учасниці.'],
                ['translation','Андрій звинуватив мене в тому, що я розбив вазу. Почни з Андрія.','Andrii accused me|of breaking|the vase.','Після названого обвинуваченого передай причину через прийменник і назву дії.'],
            ],
        ];
    }
}
