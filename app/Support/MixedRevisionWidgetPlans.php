<?php

namespace App\Support;

/** Reviewed guidance and finite alternatives for the thirty retained thematic questions. */
final class MixedRevisionWidgetPlans
{
    public const SNAPSHOTS_SHA='85866750d1cd3a584f4df67438f65f42c023d47c905466e937942872d4d165fe';
    public const IDS=[
        'a1-mixed-revision'=>[46575,46577,46578,46584,46586],
        'a2-mixed-revision'=>[78,7548,7550,7551,7556],
        'b1-mixed-revision'=>[15278,16803,16804,16805,16806],
        'b2-mixed-revision'=>[46179,46180,46181,46182,46184],
        'c1-mixed-revision'=>[18417,18418,18419,18420,18421],
        'c2-mixed-revision'=>[19185,19186,19187,19188,19189],
    ];
    public const HINTS=[
        46575=>'Назви її професію: потрібна форма стану для однини й неозначений артикль перед назвою фаху.',
        46577=>'Передай теперішнє володіння синьою сумкою; обидві базові конструкції володіння приймаються.',
        46578=>'Це регулярна ранкова звичка мовця; час постав наприкінці.',
        46584=>'Назви потребу мовця; перед назвою парасолі потрібен неозначений артикль за голосним звуком.',
        46586=>'Передай її вміння плавати з високою оцінкою; не описуй дію саме зараз.',
        78=>'Передай вже наявний намір приготувати вечерю; час постав наприкінці.',
        7548=>'Це м’яка порада більше відпочивати, а не наказ чи суворий обов’язок.',
        7550=>'Рішення вже прийнято; після минулого рішення назви вивчення іспанської інфінітивом.',
        7551=>'Очікування автобуса тривало о сьомій у минулому; автобус уже відомий співрозмовникам.',
        7556=>'Передай колишній тривалий стан через конструкцію минулої звички; мовець уже не живе там.',
        15278=>'Робота почалася три роки тому й триває тут зараз; тривала й проста доконані форми приймаються.',
        16803=>'Підкресли готовий результат до теперішнього моменту доконаною теперішньою формою.',
        16804=>'Читання тривало весь сьогоднішній ранок; підкресли саме процес доконаною тривалою формою.',
        16805=>'Йдеться про електронні листи; передай вже наявний результат доконаним теперішнім часом.',
        16806=>'Відхід уже відбувся до її минулого дзвінка; почни з мовця та ранішої дії.',
        46179=>'Це процес у конкретний завтрашній момент; почни з часової обставини.',
        46180=>'Перевірку буде завершено до п’ятниці; почни зі звіту й використай майбутній доконаний пасив.',
        46181=>'Це докір щодо минулого: адресат мав повідомити мовцеві раніше, але не повідомив.',
        46182=>'Це невиконана минула умова й нереалізований наслідок; почни з умовної частини.',
        46184=>'Ризикованість не завадила схваленню; почни з поступової частини й збережи минулий пасив.',
        18417=>'Пропозицію не прийнято в минулому; наслідок — уявна робота за кордоном саме зараз.',
        18418=>'Уявна організованість стосується її загальної властивості; наслідок — уже пропущений строк подання звіту.',
        18419=>'Попередження було проігноровано; уяви протилежне минуле та відсутність проблем зараз.',
        18420=>'Питай, що адресат зробив би інакше за іншого знання в минулому; питальне слово введи сам.',
        18421=>'Почни з обмеження «ніколи раніше»; після нього інвертуй допоміжне дієслово й займенник мовця.',
        19185=>'Почни з інвертованої минулої умови відсутності його підтримки; збережи нереалізований минулий наслідок.',
        19186=>'Точність даних — умова висновку, не підтверджений факт; почни з умовного сполучника.',
        19187=>'Почни зі зв’язку «інакше»; йдеться про нереалізовану додаткову втрату часу в минулому.',
        19188=>'Почни з формальної інверсії можливого майбутнього; перегляд плану був би необхідним наслідком.',
        19189=>'Передай протиставний зв’язок на початку й збережи значущість результатів; не стверджуй їхню достовірність.',
    ];
    public static function instructions():array
    {
        $out=[];foreach(self::IDS as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи учасників, час і зміст.';return$out;
    }
    public static function aliases():array
    {
        $out=[
            46577=>['He has got a blue bag'],46578=>['I have coffee every morning'],
            46586=>['She can swim well','She swims very well'],
            78=>['I am going to make dinner this evening','I am going to prepare dinner this evening','I am going to cook supper this evening'],
            7548=>['You should get more rest','You should relax more'],7550=>['She decided to study Spanish'],7551=>["We were waiting for the bus at seven"],7556=>['I used to live by the sea'],
            15278=>['I have worked here for three years'],16805=>['They have already written three e-mails'],
            16806=>['I had already left when she phoned','I had already left when she rang','I had already gone when she called','I had already gone when she phoned','I had already gone when she rang'],
            46180=>['The report will have been reviewed by Friday'],46181=>['You should have told me sooner'],46182=>['If I had known, I would have assisted'],
            46184=>['Though the plan was risky, it was approved','Although the plan was risky, it was accepted','Though the plan was risky, it was accepted'],
            18417=>['If I had accepted the offer I would be working overseas now','If I had accepted the proposal I would be working abroad now','If I had accepted the proposal I would be working overseas now'],
            18419=>['If they had not disregarded the warning they would not have problems now','If they had not ignored the warning they would not have difficulties now','If they had not disregarded the warning they would not have difficulties now'],
            18420=>['What would you have done differently if you had known this sooner','What would you have done differently if you had known this before'],
            18421=>['Never before have I seen such compelling evidence','Never before have I seen such convincing proof','Never before have I seen such compelling proof'],
            19185=>['Had it not been for his help the project would have failed'],
            19186=>['Providing that the data are accurate the conclusion is convincing','Provided that the data are accurate the conclusion is compelling','Providing that the data are accurate the conclusion is compelling'],
            19188=>['Were the situation to change we would have to reconsider the plan'],
            19189=>['Nonetheless the results remain significant','However the results remain significant'],
        ];
        foreach(['organized','organised']as$a)foreach(['submitted','handed in','turned in']as$v){$s='If she were more '.$a.' she would have '.$v.' the report on time';if($s!=='If she were more organized she would have submitted the report on time')$out[18418][]=$s;}
        return$out;
    }
}
