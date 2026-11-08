<?php
namespace App\Support;

/** Finite reviewed copy and additions; all six historical task values and the attached inference stay authoritative. */
final class VocabCollocationsNativePlans
{
    public static function retained():array{return['advanced-collocation-and-lexical-choice'=>[
        ['a|small difference','План — 1 000 одиниць, витрати — 1 002. Різницю прямо оцінено як малу й практично неважливу. Склади фразу та перевір статистичний висновок.','Потрібна мала, практично неважлива різниця; окремо не видавай описову оцінку за статистичний висновок.'],
        ['The team|faces a challenge|in using|the cramped store.|The note|raises a question|about storage|but does not|answer it.|One dated invoice|provides documentary evidence|of the purchase,|not a complete|account of|the object’s history.','Команді важко користуватися тісним складом. Нотатка порушує питання зберігання без відповіді. Один датований рахунок свідчить про купівлю, а не повну історію предмета.','Збережи труднощі, не неможливість; питання без відповіді; один рахунок як свідчення купівлі, не доказ усієї історії.'],
    ]];}
    public static function additions():array
    {
        $legal=[];foreach(['lease','rental agreement']as$document)foreach(['tenant','renter']as$person)foreach(['sought','requested','asked for']as$verb)foreach(['legal advice','legal counsel']as$advice){$a='Before signing the '.$document.', the '.$person.' '.$verb.' '.$advice.'.';if($a!=='Before signing the lease, the tenant sought legal advice.')$legal[]=$a;}
        $agreement=[];foreach(['experts','specialists']as$people)foreach(['reached agreement on','reached an agreement on','came to an agreement on','reached consensus on','reached a consensus on','agreed on']as$verb)foreach(['main','principal','primary']as$adjective)foreach(['causes of','reasons for']as$cause){$a='The '.$people.' '.$verb.' the '.$adjective.' '.$cause.' the leak.';if($a!=='The experts reached agreement on the main causes of the leak.')$agreement[]=$a;}
        return['advanced-collocation-and-lexical-choice'=>[
            ['gap',"The new testimony {a1} doubt on the witness's claim.",'casts','Передай поставлення під сумнів наведеного твердження; встав дієслово теперішнього часу, решту сполучення вже наведено.',['throws'],'casts (throws)'],
            ['gap','Before authorising the loan, the bank carried out a thorough {a1} of the risks.','assessment','Оцінювання, аналіз або перегляд ризиків: потрібен іменник після вже наведеного прикметника; підмет не повторюй.',['evaluation','appraisal','analysis','review','examination'],'assessment (evaluation / appraisal / analysis / review / examination)'],
            ['translation','Перед підписанням договору оренди орендар звернувся по юридичну пораду.','Before signing|the lease,|the tenant|sought legal advice.','Почни з дії перед підписанням; орендар просив поради, але її одержання тут не встановлене.',$legal],
            ['translation','Експерти дійшли згоди щодо основних причин витоку.','The experts|reached agreement|on the|main causes|of the leak.','Почни з експертів; вони погодилися щодо причин конкретного витоку, не обов’язково усунули його.',$agreement],
        ]];
    }
    public static function tokenLabels(array$groups,array$accepted):array
    {
        $labels=ArticlesQuantifiersPracticeSupplement::tokenLabels($groups,$accepted);
        // Representative lexical cues are explicit, not every finite full-sentence variant.
        $representative=[
            'the lease,'=>'the lease (the rental agreement)',
            'the tenant'=>'the tenant (the renter)',
            'sought legal advice.'=>'sought legal advice (asked for legal advice)',
            'The experts'=>'The experts (the specialists)',
            'reached agreement'=>'reached agreement (agreed)',
            'main causes'=>'main causes (principal causes)',
        ];
        foreach($groups as$group)if(isset($representative[$group]))$labels[$group]=$representative[$group];
        return$labels;
    }
    public static function copy():array{return['advanced-collocation-and-lexical-choice'=>[
        'selects'=>[
            ['Обери вислів і правильний висновок.',[['uk','Учасник уперше пропонує обговорити зберігання запасних ключів; відповіді ще немає.'],['en','I would like to ___ a question about spare-key storage.']],[
                'raise a question — порушити питання; відповіді ще не встановлено.',
                'answer a question — відповідь уже відома й питання вирішено.',
                'raise a question — відповідь уже надано.',
            ]],
            ['Збережи ролі джерела труднощів і команди.',[['uk','Вузький прохід створює труднощі для команди, яка переносить декорації. Використай дві задані моделі.'],['en','The narrow passage poses… / The team faces…']],[
                'The passage poses a challenge to the team; the team faces a challenge in moving the scenery.',
                'The passage faces a challenge; the team poses a challenge to the passage. Ролі ті самі.',
                'The passage makes moving the scenery impossible. Труднощі означають неможливість.',
            ]],
        ],
        'choices'=>[
            ['Які пари допустимі та чи гарантоване повторення рішення?',[['en','set / established a precedent; draws / makes a distinction between repair and replacement']],[
                'Обидві пари допустимі; прецедент не гарантує повторення рішення.',
                'Лише set і draws допустимі; прецедент гарантує повторення рішення.',
            ]],
            ['Виправ модель розрізнення й число питань; використай a та between A and B.',[['en','The guide draws distinction among repair and replacement. It raises two question about the warranty.']],[
                'The guide draws a distinction between repair and replacement. It raises two questions about the warranty.',
                'The guide draws distinction among repair. It answers two question on the warranty.',
            ]],
        ],
    ]];}
}
