<?php

namespace App\Support;

/** Explicit teaching copy; every retained answer remains owned by its frozen release. */
final class SentenceTransformationsNativePlans
{
    /** The sentence boundary is authored, never guessed from punctuation at runtime. */
    public static function translationSentences():array
    {
        return [
            'tenses'=>['Ми поливаємо ці рослини щовечора.','Коли я повернувся, дощ уже припинився.'],
            'modals'=>['Нам не довелося купувати новий холодильник.','Тобі слід зберегти цю квитанцію.'],
            'passive'=>['Нову теплицю збудують наступної весни.','Садівниця зараз обрізає троянди.'],
            'reported-speech'=>['Данило сказав, що його поїзд запізнювався.','Леся запитала мене, де я купив цю лампу.'],
            'conditionals'=>['Якби в мене був сад, я вирощував би помідори.','Я волів би, щоб ти залишив посилку всередині.'],
            'relative-clauses-linking-words'=>['Це садівниця, яка вирощує наші овочі.','Ми залишилися вдома, бо дороги були слизькі.'],
            'articles-quantifiers'=>['Ми купили три буханці хліба для пікніка.','У цьому глечику залишилося трохи води.'],
        ];
    }
    public static function retained():array
    {
        return [
            'tenses'=>[
                ['Oleh has been|learning French|for three years.','Олег вивчає французьку вже три роки.','Станом на перше вересня дві тисячі двадцять четвертого навчання триває; передай процес і тривалість, почни з Олега.'],
                ['By the time|we arrived,|the film|had started.','До нашого прибуття фільм уже почався.','Почни з межі нашого прибуття; спочатку назви прибуття, потім раніший початок фільму.'],
            ],
            'modals'=>[
                ['I managed|to unlock|the gate|yesterday.','Мені вдалося відімкнути хвіртку вчора.','Мовець справді відімкнув хвіртку попри труднощі; передай досягнутий успіх, почни з мовця.'],
                ["We don't|have to|pay today.",'Нам не потрібно платити сьогодні.','Це відсутність потреби, не заборона; почни з нас і використай заперечення обов’язку.'],
            ],
            'passive'=>[
                ['The form|has not been|signed by Nina.','Ніна не підписала бланк.','Почни з бланка; збережи Ніну, заперечення та доконаний часовий план.'],
                ['Was the bike|repaired by Marta|yesterday?','Марта полагодила велосипед учора?','Почни з питання про велосипед; збережи Марту та вчорашній час.'],
            ],
            'reported-speech'=>[
                ['The teacher|asked us|to open|our books.','Учитель попросив нас відкрити наші книжки.','Учасник групи переказує прохання після уроку в коридорі; збережи адресатів і належність книжок.'],
                ['Olha asked me|if I knew|her address.','Ольга запитала мене, чи я знаю її адресу.','Олег у вівторок удома переказує понеділкове питання з офісу з минулої точки відліку; збережи обох учасників.'],
            ],
            'conditionals'=>[
                ['I’d rather|you didn’t call|after ten tonight.','Я волів би, щоб ти не телефонував після десятої сьогодні ввечері.','Почни з мовця; це його особиста перевага щодо іншої людини, не наказ.'],
                ['You had better|not touch|this wire|because it|may be live.','Тобі краще не торкатися цього дроту, бо він може бути під напругою.','Почни із сильної поради-попередження, потім назви можливу причину небезпеки.'],
            ],
            'relative-clauses-linking-words'=>[
                ['The office|which I visited|was empty.','Офіс, який я відвідав, був порожній.','Почни з офісу; збережи підмет відвідування й явний означальний займенник для предмета.'],
                ['Despite|being tired,|Danylo finished|the report.','Попри втому, Данило закінчив звіт.','Почни з поступки та форми дії; втомився й завершив звіт той самий Данило.'],
            ],
            'articles-quantifiers'=>[
                ['I found|no errors|in the report.','Я не знайшов жодних помилок у звіті.','Почни з мовця; передай минуле знаходження через нульову кількість, без другого заперечення.'],
                ['She gave me|exactly one|useful piece|of advice|about packing.','Вона дала мені рівно одну корисну пораду щодо пакування.','Почни з неї; порахуй одну одиницю незлічуваної поради, збережи одержувача та тему.'],
            ],
        ];
    }

    public static function additions():array
    {
        return [
            'tenses'=>[
                ['gap','The courier {a1} before I opened the door.','had left','Кур’єр пішов раніше за відчинення дверей; введи всю форму попередньої минулої дії.'],
                ['gap','I {a1} this coat since last winter.','have owned','Пальто належить мовцеві від минулої зими дотепер; передай весь стан володіння або наявності донині.',['have had']],
                ['translation','Ми поливаємо ці рослини щовечора. Почни з нас і передай звичку.','We water|these plants|every evening.','Це регулярна дія, не процес просто зараз; збережи ці рослини й щовечірню частоту.'],
                ['translation','Коли я повернувся, дощ уже припинився. Почни з мого повернення.','When I returned,|the rain|had already stopped.','Повернення було пізніше; припинення дощу передай як попередню минулу дію.',['When I came back, the rain had already stopped.']],
            ],
            'modals'=>[
                ['gap','You {a1} feed the ducks here: it is forbidden.','must not','Годувати качок тут заборонено; введи всю модальну заборону, не відсутність потреби.',["mustn't"]],
                ['gap','I {a1} repair the lock myself, and it works now.','managed to','Мовець успішно полагодив замок попри труднощі; введи минулу форму досягнутого результату.'],
                ['translation','Нам не довелося купувати новий холодильник. Почни з нас і використай повні форми.','We did not|have to buy|a new fridge.','Необхідності купівлі в минулому не було; не стверджуй, чи покупка фактично відбулася.',['We did not have to buy a new refrigerator.']],
                ['translation','Тобі слід зберегти цю квитанцію. Почни зі співрозмовника; дай звичайну пораду.','You should|keep this receipt.','Це нейтральна порада, не наказ чи попередження про негайну небезпеку.',['You ought to keep this receipt.']],
            ],
            'passive'=>[
                ['gap','The kitchen window {a1} by Dad last Saturday.','was cleaned','Тато помив вікно минулої суботи; введи всю форму завершеної минулої дії в пасиві.'],
                ['gap','These parcels {a1} by a courier every morning.','are delivered','Посилки регулярно доставляє кур’єр; потрібна повна пасивна форма для множини.'],
                ['translation','Нову теплицю збудують наступної весни. Почни з теплиці й передай майбутню дію.','The new greenhouse|will be built|next spring.','Виконавця не названо; теплиця отримує дію, сезон має залишитися майбутнім.'],
                ['translation','Садівниця зараз обрізає троянди. Перебудуй у пасив, почни з троянд і збережи виконавицю.','The roses|are being pruned|by the gardener|now.','Обрізання триває просто зараз; садівниця залишається виконавицею, троянди — об’єктом.'],
            ],
            'reported-speech'=>[
                ['gap','Lina told me that she {a1} the spare key.','had found','У понеділок Ліна повідомила про вже знайдений ключ; у вівторок я переказую це з минулої точки відліку.'],
                ['gap','My neighbour asked me {a1} the cat while she was away.','to feed','Сусідка попросила мене погодувати кота під час її відсутності; введи всю форму прохання після адресата.'],
                ['translation','Данило сказав, що його поїзд запізнювався. Почни з Данила й залиш сполучник.','Danylo said|that his train|was late.','Він сказав це на вокзалі вранці; я переказую ввечері з минулої точки відліку, не підтверджую факт окремо.'],
                ['translation','Леся запитала мене, де я купив цю лампу. Почни з Лесі.','Lesia asked me|where I|had bought|that lamp.','Питання було в понеділок біля лампи; у вівторок в іншому місці я переказую його з минулої точки відліку.'],
            ],
            'conditionals'=>[
                ['gap','Unless it {a1}, we will eat outside tomorrow.','rains','Завтра їстимемо надворі, якщо не піде дощ; після заперечного сполучника не додавай друге заперечення.'],
                ['gap','I wish I {a1} the greenhouse door last night.','had closed','Мовець шкодує зараз про незачинені вчора двері; потрібна вся форма жалю про минуле.'],
                ['translation','Якби в мене був сад, я вирощував би помідори. Почни з умови.','If I had|a garden,|I would grow|tomatoes.','Зараз саду немає; це уявна теперішня ситуація, не втрачена минула нагода.'],
                ['translation','Я волів би, щоб ти залишив посилку всередині. Почни з мовця; назви особисту перевагу.','I would rather|you left|the parcel inside.','Дію має виконати співрозмовник; після переваги щодо іншого учасника потрібна минула форма.'],
            ],
            'relative-clauses-linking-words'=>[
                ['gap','The gardener {a1} shed we borrowed lives nearby.','whose','Ми позичили сарай цього садівника; назви відносне слово власника перед назвою речі.'],
                ['gap','We covered the seedlings {a1} protect them from frost.','to','Захист від морозу був метою накривання; введи короткий показник мети перед дієсловом.'],
                ['translation','Це садівниця, яка вирощує наші овочі. Почни з вказівки на людину.','This is|the gardener|who grows|our vegetables.','Означальна частина називає дію самої садівниці; збережи належність овочів нам.',['This is the gardener that grows our vegetables.']],
                ['translation','Ми залишилися вдома, бо дороги були слизькі. Почни з нас; назви причину.','We stayed|at home|because the roads|were slippery.','Минуле перебування спричинене станом доріг; це причина, не поступка чи мета.',['We stayed home because the roads were slippery.']],
            ],
            'articles-quantifiers'=>[
                ['gap','There are {a1} apples left: exactly three, enough for the pie.','a few','Яблук небагато, але вони є й їх вистачає; введи весь вираз позитивної малої кількості.'],
                ['gap','I did not add {a1} salt to the soup.','any','Солі не додано зовсім; заперечення вже є, потрібне лише слово кількості.'],
                ['translation','Ми купили три буханці хліба для пікніка. Почни з нас.','We bought|three loaves|of bread|for the picnic.','Хліб незлічуваний; порахуй цілі буханці, а не скибки, збережи минулу купівлю.'],
                ['translation','У цьому глечику залишилося трохи води. Почни з конструкції існування.','There is|a little water|left in|this jug.','Вода ще є; передай невелику ненульову кількість і цей глечик.',['There is some water left in this jug.']],
            ],
        ];
    }
}
