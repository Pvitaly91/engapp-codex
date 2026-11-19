<?php

namespace App\Support;

/** Reviewed finite teaching plans; historical questions and answers are not rewritten. */
final class ConditionalsNativePlans
{
    /** Exact ordered groups, Ukrainian condition, Ukrainian lexical/grammar hint. */
    public static function retained(): array
    {
        return [
            'zero-conditional'=>[
                ['If I have|ripe tomatoes,|I make soup.','Якщо в мене є стиглі помідори, я готую суп. Це звичка.','Почни з умови; обидві дії подай як звичні.'],
                ['If I|do not understand|a word,|I use|a dictionary.','Якщо я не розумію якогось слова, я користуюся словником.','Почни з умови; збережи заперечення та звичну дію.'],
            ],
            'first-conditional'=>[
                ['If I finish|the poster tonight,|I will print|it tomorrow.','Якщо я закінчу плакат сьогодні ввечері, я надрукую його завтра.','Почни з умови; майбутній результат подай після неї.'],
                ['What will you|cook tomorrow|if our guests|do not|eat meat?','Що ти приготуєш завтра, якщо наші гості не їдять м’яса?','Почни з питального слова; заперечення залиш в умові.'],
            ],
            'second-conditional'=>[
                ['If I knew|sign language,|I would understand|this video.','Якби я знав жестову мову, я б розумів це відео. Зараз я її не знаю.','Почни з уявної умови про теперішнє.'],
                ['If I were|you, I would|ask for|a written estimate.','На твоєму місці я б попросив письмовий кошторис.','Використай стандартну уявну умову; попросити — з прийменником.'],
            ],
            'third-conditional'=>[
                ['If we|had waited|for the cake|to cool,|it would not|have broken.','Якби ми дочекалися, поки пиріг охолоне, він би не розламався. Учора ми не дочекалися.','Умова й заперечний результат стосуються завершеного минулого.'],
                ['Would the guide|have stopped|at the lake|yesterday if we|had asked?','Чи зупинився б учора гід біля озера, якби ми попросили?','Почни з питання про минулий результат; умову постав наприкінці.'],
            ],
            'mixed-conditionals'=>[
                ['If we|had planted trees|ten years ago,|we would be|in the shade|now.','Якби ми посадили дерева десять років тому, то зараз були б у затінку.','Поєднай нездійснену минулу умову з теперішнім результатом.'],
                ['If I liked|spicy food,|I would have|tried the curry|last night.','Якби я любив гостру їжу, то скуштував би карі вчора ввечері. Це мій сталий смак.','Стала риса в умові пояснює уявний минулий результат.'],
            ],
            'unless-provided-as-long-as'=>[
                ['We will meet|at the library|tomorrow unless it|closes early.','Ми зустрінемося завтра в бібліотеці, якщо вона не зачиниться рано.','Почни з плану; раннє закриття назви як виняток.'],
                ['You can publish|the photo|provided that|everyone agrees.','Ти можеш опублікувати фото за умови, що всі погодяться.','Почни з дозволу; використай заданий сполучник умови.'],
            ],
            'in-case'=>[
                ['I will charge|my phone|in case I|need to call|a taxi.','Я заряджу телефон на випадок, якщо мені потрібно буде викликати таксі.','Спочатку підготовка, потім можлива потреба; не майбутня форма в другій частині.'],
                ['She saved|the receipt|in case she|needed to return|the shoes.','Вона зберегла чек на випадок, якщо їй потрібно буде повернути взуття. Це розповідь про вчора.','Подай підготовку й можливу потребу з минулої точки зору.'],
            ],
            'wish-if-only'=>[
                ['I wish|our kitchen|had a window.','Шкода, що в нашій кухні немає вікна.','Вислови бажання іншої теперішньої ситуації; бажання належить мовцеві.'],
                ['If only I|had saved|the document|before closing it!','От якби я зберіг документ перед тим, як закрити його! Я цього не зробив.','Почни з емоційного жалю про минуле; збережи виконавця обох дій.'],
            ],
            'would-rather'=>[
                ['I would rather|repair this chair|than buy|a new one.','Я б краще відремонтував цей стілець, ніж купував новий.','Один виконавець обох дій; після вислову переваги початкова форма.'],
                ['We would rather|the visitors left|their shoes|by the door|tonight.','Ми б воліли, щоб відвідувачі сьогодні ввечері залишили взуття біля дверей.','Бажання стосується іншого виконавця; час постав наприкінці.'],
            ],
            'had-better'=>[
                ['I had better|set an alarm|for tomorrow.','Мені краще поставити будильник на завтра. Інакше я просплю.','Почни з мовця; передай конкретну пораду через можливий наслідок.'],
                ['You had better|not sit|on this bench.','Тобі краще не сідати на цю лавку. Фарба ще мокра.','Заперечення постав перед дією, якої не радять робити.'],
            ],
            'conditionals-with-unless-provided-as-long-as'=>[
                ['The organiser|will reserve|a desk|provided that|Eva confirms|by Friday.','Організатор забронює стіл за умови, що Єва підтвердить до п’ятниці.','Почни з організатора; звичайна майбутня умова не потребує майбутньої форми.'],
                ['Unless you reply|by noon,|I’ll cancel|your seat.|You can keep|it provided that|you pay today.','Якщо ти не відповіси до полудня, я скасую твоє місце. Зберегти його можна за умови оплати сьогодні.','Збережи виняток у першому реченні та умову дозволу в другому.'],
            ],
            'advanced-conditionals'=>[
                ['Had we kept|the access code,|we could open|the archive now.','Якби ми зберегли код доступу, то могли б зараз відкрити архів. Почни з інверсії.','Минула умова, теперішня можливість; не перетворюй результат на минулий.'],
                ['We would have|finished the report|on Tuesday.|We would be|ready to start|the report now.','Порівняй результати тієї самої умови: ми завершили б звіт у вівторок; ми були б готові почати звіт зараз.','Склади два окремі результати: завершений минулий і теперішній стан.'],
            ],
            'conditional-alternatives-and-nuance'=>[
                ['Should you need|a printed copy,|contact the librarian.|Were the ferry|to stop running,|we would stay|on the island.|Had the courier|arrived earlier,|we could have|sent the sample.','Побудуй три умовні інверсії: якщо знадобиться друкована копія — звернися до бібліотекаря; якби пором перестав курсувати — ми залишилися б на острові; якби кур’єр прибув раніше — ми могли б надіслати зразок.','Збережи три різні моделі: можливу умову, уявну перспективу та нездійснене минуле.'],
                ['Assuming the grant|is confirmed,|we might open|on Monday.|We may open|on condition that|the director gives|written approval;|otherwise, we will|postpone the opening.','Припускаючи, що грант підтвердять, ми, можливо, відкриємося в понеділок. Дозвіл залежить від письмового схвалення директора; інакше відкладемо відкриття.','Відокрем робоче припущення від обов’язкової умови; не додавай певності результату.'],
            ],
        ];
    }

    /** Two complete-phrase gaps and two Ukrainian-to-English tasks per exact lesson. */
    public static function additions(): array
    {
        return [
            'zero-conditional'=>[
                ['gap','When the freezer door stays open, the ice cream {a1}.','melts','Тане — звичний результат; підмет уже наведено.'],
                ['gap','If these gloves get wet, they {a1}.','do not keep my hands warm','Не зігрівають мої руки; уведи всю заперечну групу.'],
                ['translation','Якщо я забуваю парасольку, я мокну. Це звична ситуація.','If I forget|my umbrella,|I get wet.','Почни з умови; обидві частини описують звичний зв’язок.'],
                ['translation','Коли цуценя голодне, воно скавчить.','When the puppy|is hungry,|it whines.','Почни з умови; стан голоду передай дієсловом-зв’язкою.'],
            ],
            'first-conditional'=>[
                ['gap','If the plumber comes before noon, I {a1} the bathroom sink.','will show him','Покажу йому — майбутній результат; підмет уже наведено.'],
                ['gap','What {a1} if the kettle breaks tomorrow?','will you use','Чим ти скористаєшся; уведи допоміжне слово, виконавця й дію.'],
                ['translation','Якщо цей конверт важитиме надто багато, я куплю ще одну марку.','If this envelope|weighs too much,|I will buy|another stamp.','Почни з умови; після неї передай майбутнє рішення.'],
                ['translation','Якщо ти принесеш рулетку, ми виміряємо полицю.','If you bring|a tape measure,|we will measure|the shelf.','Почни з умови; виміряти — дія результату.'],
            ],
            'second-conditional'=>[
                ['gap','If I owned a greenhouse, I {a1} in winter.','would grow lettuce','Вирощував би салат — уявний теперішній результат.'],
                ['gap','Where {a1} if you could work anywhere?','would you live','Де ти мешкав би; питальне слово вже наведено.'],
                ['translation','Якби в мене був мікроскоп, я досліджував би цю краплю води.','If I had|a microscope,|I would examine|this water drop.','Почни з уявної умови; досліджувати — вивчати зблизька.'],
                ['translation','Якби ця миска була більшою, я використав би її для тіста.','If this bowl|were bigger,|I would use|it for dough.','Почни з уявної властивості предмета; використай стандартну форму дієслова-зв’язки.'],
            ],
            'third-conditional'=>[
                ['gap','If I had oiled the hinges, the gate {a1}.','would not have squeaked','Не скрипіла б — уявний заперечний результат завершеного минулого.'],
                ['gap','If the gardener {a1} the seedlings, they would have survived.','had covered','Накрив — дія, якої тоді не виконали; уведи всю форму минулої умови.'],
                ['translation','Якби я підписав банку, ми не додали б сіль замість цукру.','If I had|labelled the jar,|we would not|have added salt|instead of sugar.','Почни з невиконаної минулої дії; збережи заперечний результат.', ['If I had labeled the jar, we would not have added salt instead of sugar.']],
                ['translation','Якби ми вимкнули праску, сорочка не згоріла б.','If we had|switched off|the iron,|the shirt would|not have burned.','Почни з минулої умови; збережи обидва предмети та заперечення.'],
            ],
            'mixed-conditionals'=>[
                ['gap','If I had kept the receipt, I {a1} the faulty toaster now.','could return','Міг би зараз повернути; минула умова дає теперішню можливість.'],
                ['gap','If Nora {a1} colour-blind, she would have noticed the red warning yesterday.','were not','Не мала б порушення кольорового зору — стала риса пояснює минулий результат.'],
                ['translation','Якби ми купили осушувач торік, у підвалі зараз не було б сиро.','If we had|bought a dehumidifier|last year,|the cellar would|not be damp|now.','Почни з невиконаної минулої дії; описуй теперішній стан підвалу.'],
                ['translation','Якби я вмів в’язати, я полагодив би той светр учора.','If I could|knit, I would|have repaired|that sweater yesterday.','Загальне вміння в умові, уявний завершений результат учора.'],
            ],
            'unless-provided-as-long-as'=>[
                ['gap','We will leave the porch light on {a1} you come home.','until','До моменту твого повернення; це межа тривалості, не виняток.'],
                ['gap','You may borrow my drill {a1} you return it this evening.','provided that','За умови, що; уведи заданий двослівний сполучник.'],
                ['translation','Ми не почнемо фарбувати, якщо стіна не буде сухою. Почни з винятку.','Unless the wall|is dry,|we will not|start painting.','Не додавай другого заперечення в частину винятку.'],
                ['translation','Ти можеш залишитися в сараї за умови, що не запалюватимеш свічок.','You can stay|in the shed|as long as|you do not|light candles.','Почни з дозволу; збережи заперечну умову безпеки.'],
            ],
            'in-case'=>[
                ['gap','Put a cloth under the vase {a1} it leaks.','in case','На випадок, якщо; підготовча дія виконується завчасно.'],
                ['gap','I kept the spare lace in case the old one {a1}.','broke','Порвалося б — можлива потреба з минулої точки зору.'],
                ['translation','Візьми ліхтарик на випадок, якщо в гаражі буде темно.','Take a torch|in case|the garage|is dark.','Почни з підготовчої дії; після перестороги не став майбутню форму.', ['Take a flashlight in case the garage is dark.']],
                ['translation','Ми сфотографували етикетку на випадок, якщо знадобиться номер моделі.','We photographed|the label|in case|we needed|the model number.','Минула підготовка й можлива потреба розглядаються з однієї минулої точки.'],
            ],
            'wish-if-only'=>[
                ['gap','I wish this tap {a1} dripping at night.','would stop','Припинив би — бажана зміна надокучливої поведінки.'],
                ['gap','If only we {a1} the picnic blanket yesterday.','had packed','Узяли із собою — жаль про невиконану дію вчора.'],
                ['translation','Шкода, що я не можу дістати до верхньої полиці.','I wish|I could reach|the top shelf.','Вислови бажання іншої теперішньої можливості.'],
                ['translation','От якби я не розбив ту кришку!','If only|I had not|broken that lid!','Жаль стосується вже завершеної минулої дії; збережи заперечення.'],
            ],
            'would-rather'=>[
                ['gap','I would rather {a1} the broken zip than replace the whole coat.','repair','Полагодити — дія того самого виконавця після вислову переваги.'],
                ['gap','We would rather the neighbour {a1} the hedge tomorrow.','trimmed','Підрізав — бажана майбутня дія іншого виконавця.'],
                ['translation','Я б краще пересадив цю папороть, ніж викидав її.','I would rather|repot this fern|than throw|it away.','Обидві дії виконує мовець; збережи порівняння двох варіантів.'],
                ['translation','Ми б воліли, щоб ти не пересував скриню сьогодні.','We would rather|you did not|move the chest|today.','Заперечна бажана дія іншого виконавця; день постав наприкінці.'],
            ],
            'had-better'=>[
                ['gap','The steps are icy. You {a1} the handrail.','had better hold','Краще триматися — конкретна порада через небезпеку падіння.'],
                ['gap','We had better {a1} this cracked cup.','not use','Не користуватися — заперечення стоїть перед дією.'],
                ['translation','Тобі краще зав’язати шнурівки, інакше спіткнешся.','You had better|tie your shoelaces|or you will|trip.','Почни з поради; після неї назви можливий небажаний наслідок.'],
                ['translation','Нам краще не торкатися цього дроту.','We had better|not touch|this wire.','Збережи конкретне застереження та заперечення перед дією.'],
            ],
            'conditionals-with-unless-provided-as-long-as'=>[
                ['gap','You may test the pottery wheel {a1} the instructor stays nearby.','as long as','За умови, що; уведи трислівний сполучник дозволу.'],
                ['gap','Unless the clay {a1}, we will leave it overnight.','is dry','Суха — стан глини; в умові винятку підмет уже наведено.'],
                ['translation','Я погоджуся відреставрувати рамку за умови, що ти принесеш оригінальні деталі.','I will agree|to restore|the frame|provided that|you bring|the original parts.','Почни з погодження; збережи точну умову з потрібними деталями.'],
                ['translation','Якщо реставратор не схвалить лак, ми не покриватимемо ним стіл.','Unless the restorer|approves the varnish,|we will not|coat the table|with it.','Почни з винятку; займенник наприкінці стосується лаку.'],
            ],
            'advanced-conditionals'=>[
                ['gap','Had the sculptor retained the mould, she {a1} another copy now.','could cast','Могла б зараз відлити; минула нездійснена умова та теперішня можливість.'],
                ['gap','Were the ceiling {a1}, the tapestry would fit.','to be higher','Бути вищою — уявна властивість у формальній інверсії.'],
                ['translation','Якби ми зберегли палітру, то могли б зараз точно відтворити колір. Почни з інверсії.','Had we kept|the palette,|we could now|reproduce the colour|accurately.','Минулу умову поєднай із теперішньою можливістю; не додавай минулого результату.', ['Had we kept the palette, we could now reproduce the color accurately.']],
                ['translation','Якби не герметичний контейнер, насіння втратило б схожість.','Had it not|been for|the airtight container,|the seeds would|have lost|their viability.','Почни з заперечної минулої інверсії; збережи насіння як підмет результату.'],
            ],
            'conditional-alternatives-and-nuance'=>[
                ['gap','Assuming the provenance is authentic, the curator {a1} the loan.','might approve','Міг би схвалити — робоче припущення не гарантує результату.'],
                ['gap','Submit the conservation report; {a1}, the insurer may withhold cover.','otherwise','Інакше — наслідок невиконання попередньої дії.'],
                ['translation','За умови згоди позикодавця ми можемо змінити освітлення; інакше експонат залишиться в сховищі.','Provided that|the lender agrees,|we may change|the lighting; otherwise,|the exhibit will|remain in storage.','Почни з умови дозволу; не змінюй можливість на певність.'],
                ['translation','Якби позначку інвентаризації виявили раніше, провенанс можна було б перевірити до продажу. Почни з інверсії.','Had the inventory|mark been found|earlier,|the provenance could|have been verified|before the sale.','Збережи пасивну минулу умову та можливість перевірки, а не гарантований результат.'],
            ],
        ];
    }
}
