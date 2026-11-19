<?php
namespace App\Support;

/** Finite teaching copy; the eight historical ordering answers and all original controls stay intact. */
final class SentenceStructureNativePlans
{
    public static function retained():array{return[
        'cleft-sentences-basics'=>[
            ['It was Nora|who found|the spare key|yesterday.','Саме Нора, а не Лео, знайшла запасний ключ учора.','Виділи Нору як виконавицю; збережи запасний ключ і вчорашній час.'],
            ['It was|on Friday|that we signed|the agreement.','Ми підписали угоду саме в п’ятницю.','Виділи день підписання, а не виконавців; збережи минулий час.'],
        ],
        'cleft-sentences-emphasis'=>[
            ['When was it|that the supplier|changed|the delivery date?','Коли саме постачальник змінив дату доставки?','Почни з питання про час; збережи постачальника та зміну дати доставки.'],
            ['What the team|needed was|a clearer brief.','Обладнання вистачало, але команда не могла завершити дослідження. Їй потрібне було чіткіше завдання.','Виділи те, чого бракувало команді: чіткіше завдання, не додаткове обладнання.'],
        ],
        'ellipsis-substitution-and-reference'=>[
            ['This ease|of reference|made the handbook|useful for revision.','Порівнювали онлайнсемінар і друкований посібник. Зручність звернення саме до посібника зробила його корисним для повторення.','Відсилання стосується зручності користування посібником, не онлайнсемінару; збережи причину його корисності.'],
            ["Olena told Marta|that Olena's notes|were missing.",'Олена сказала Марті, що загубилися саме Оленині нотатки. Усунь неоднозначність власниці.','Олена повідомляє Марті про власні нотатки; не приписуй їх Марті.'],
        ],
        'complex-noun-phrases'=>[
            ['Leila,|the project coordinator,|approved the change.','Лейла, координаторка проєкту, схвалила зміну. Посада — додаткова довідка про вже названу особу.','Збережи Лейлу виконавицею, її посаду додатковою довідкою та схвалення конкретної зміни.'],
            ['We need|a new-staff|training plan.|The training|will start|in May.','Нам потрібен план навчання нових працівників. Саме навчання почнеться в травні.','План стосується навчання нових працівників; у травні починається навчання, не новий план.'],
        ],
    ];}

    public static function additions():array
    {
        $boats=[];foreach(['What we need','What we require','The thing we need']as$subject)foreach(['reliable','dependable']as$adjective)foreach(['boat','vessel']as$noun){$a=$subject.' is a '.$adjective.' '.$noun.'.';if($a!=='What we need is a reliable boat.')$boats[]=$a;}
        $bread=['It was my sister that baked this bread yesterday.'];
        $apology=['What she wanted was an apology in writing.'];
        $roads=[];foreach(['closed','shut']as$state){foreach(['took','used']as$verb)foreach(['another','a different']as$determiner)foreach(['road','route']as$noun){$a='It was because the bridge was '.$state.' that we '.$verb.' '.$determiner.' '.$noun.'.';if($a!=='It was because the bridge was closed that we took another road.')$roads[]=$a;}foreach(['another','a different']as$determiner)$roads[]='It was because the bridge was '.$state.' that we went '.$determiner.' way.';}
        $swim=['My brother can swim, and so can I.','My brother can swim, and I can as well.'];
        $gloves=['If you need these gloves, take the blue ones.'];
        $proposals=[];foreach(['Both proposals','Both of the proposals']as$subject)foreach(['for repairing the roof','for roof repairs','for the roof repairs']as$work){foreach(['need','require']as$verb)foreach(['review','revision','reviewing','a review']as$complement){$a=$subject.' '.$work.' '.$verb.' '.$complement.'.';if($a!=='Both proposals for repairing the roof need review.')$proposals[]=$a;}foreach(['reviewed','revised']as$verb)$proposals[]=$subject.' '.$work.' need to be '.$verb.'.';}
        $report=[];foreach(['on','about']as$prep)foreach(['causes of','reasons for']as$cause){foreach(['publication','publishing']as$noun){$a='The report '.$prep.' the '.$cause.' the delay is ready for '.$noun.'.';if($a!=='The report on the causes of the delay is ready for publication.')$report[]=$a;}$report[]='The report '.$prep.' the '.$cause.' the delay is ready to be published.';}
        return[
            'cleft-sentences-basics'=>[
                ['gap','It {a1} the blue folder that I need now, not the red one.','is','Теперішня потреба: встав теперішню форму дієслова-зв’язки; підмет уже наведено.'],
                ['gap','What the apprentices value {a1} patient guidance.','is','Теперішня оцінка учнів: узгодь дієслово-зв’язку з початковою частиною про те, що вони цінують.'],
                ['translation','Саме моя сестра спекла цей хліб учора.','It was|my sister|who baked|this bread|yesterday.','Виділи сестру як виконавицю випікання; збережи цей хліб і вчорашній час.',$bread],
                ['translation','Те, що нам потрібно, — надійний човен.','What we need|is a|reliable boat.','Почни з того, що нам потрібно; збережи теперішню потребу й надійність човна.',$boats],
            ],
            'cleft-sentences-emphasis'=>[
                ['gap','Why {a1} it that the courier arrived so late yesterday?','was','Запитання про вчорашню причину: встав минулу форму дієслова-зв’язки перед уже наведеним підметом.'],
                ['gap','What the consultant did yesterday {a1} recommend a second inspection.','was','Повідомлення про вчорашню дію: встав минулу форму дієслова-зв’язки, не повторюй наведені частини.'],
                ['translation','Те, чого вона хотіла, — письмове вибачення.','What she wanted|was a|written apology.','Виділи бажаний результат; збережи минулий час і вимогу письмової форми вибачення.',$apology],
                ['translation','Саме через те, що міст був закритий, ми поїхали іншою дорогою.','It was because|the bridge|was closed|that we took|another road.','Виділи причину вибору іншої дороги: міст був закритий; обидві події належать минулому.',$roads],
            ],
            'ellipsis-substitution-and-reference'=>[
                ['gap','The proofreader corrected the headings, and the editor {a1} too.','did','Минула стверджувальна дія: заміни повтор повної дії відповідним допоміжним дієсловом.'],
                ['gap','I prefer the plain notebook to the decorated {a1}.','one','Заміни повтор назви одного злічуваного предмета; не додавай назву зошита вдруге.'],
                ['translation','Мій брат уміє плавати, і я теж. Не повторюй назву дії в другій частині.','My brother|can swim,|and I|can too.','Почни з уміння брата, потім додай моє таке саме вміння без повтору основного дієслова.',$swim],
                ['translation','Якщо вам потрібні ці рукавички, візьміть сині. Заміни друге називання рукавичок.','If you need|these gloves,|take|the blue ones.','Умова стосується конкретних рукавичок; у другій частині назву предметів заміни формою множини.',$gloves],
            ],
            'complex-noun-phrases'=>[
                ['gap','The collection of ancient coins {a1} kept in a locked cabinet.','is','Теперішній стан: узгодь допоміжне дієслово з назвою колекції, а не монет; збережи пасив.'],
                ['gap','Two descriptions of the damaged mural {a1} missing from the catalogue.','are','Теперішній стан: встав дієслово-зв’язку за кількістю описів, не за числом назви розпису.'],
                ['translation','Обидві пропозиції щодо ремонту даху потребують перегляду.','Both proposals|for repairing|the roof|need review.','Почни з обох пропозицій; ремонт стосується даху, а перегляду потребують самі пропозиції.',$proposals],
                ['translation','Звіт про причини затримки готовий до публікації.','The report|on the causes|of the delay|is ready|for publication.','Головне слово називає один звіт; збережи причини конкретної затримки та готовність до публікації.',$report],
            ],
        ];
    }

    public static function copy():array{return[
        'cleft-sentences-basics'=>[
            'selects'=>[
                ['Заповни пропуск, виділивши Нору, а не Лео.',[['en','It was ___ who found the spare key yesterday.']]],
                ['Заповни пропуск зі звичайним порядком слів.',[['en','What ___ is a quieter room.']]],
            ],
            'choices'=>[
                ['Обери речення без повторного підмета.',[['uk','Виділи майстра, який перезапустив сигналізацію.']],["It was the technician who reset the alarm.","It was the technician who she reset the alarm."]],
                ['У якому реченні виділено місце знахідки?',[],["It was difficult to find the entrance.","It was behind the library that we found the entrance."]],
            ],
        ],
        'cleft-sentences-emphasis'=>[
            'selects'=>[
                ['Заповни пропуск: відкинули не через вартість, а через брак доказів.',[['en',"It wasn't the ___ that led the committee to reject the proposal; it was the lack of evidence."]]],
                ['Заповни пропуск, зберігши модель називання дії.',[['en','What the panel did was ___ an independent review.']]],
            ],
            'choices'=>[
                ['Обери точніший фокус відповіді.',[['uk','Редактор прибрав додаток чи повторений абзац?']],["It was the editor who removed the repeated paragraph.","It was the repeated paragraph that the editor removed."]],
                ['Обери правильне узгодження.',[],["It is the local volunteers who help visitors.","It are the local volunteers who helps visitors."]],
            ],
        ],
        'ellipsis-substitution-and-reference'=>[
            'selects'=>[
                ['Укажи повну дію, яку можна опустити після заперечення.',[['en',"Lena can translate the abstract, but Max can't ___."]]],
                ['Заміни повтор незлічуваного іменника.',[['en','We need reliable information. Do you have ___?']]],
            ],
            'choices'=>[
                ['Обери природні короткі заперечні відповіді.',[['en','Will the inspection cause another delay?']],["I hope not. I don't think so.","I don't hope so. I think so not."]],
                ['Обери пару: заміна дії та пропуск після зв’язки.',[],["The curator promised to check the labels and did so before opening. The first display is clear, and the second is too.","The curator promised to check the labels and was so before opening. The first display is clear, and the second does so."]],
            ],
        ],
        'complex-noun-phrases'=>[
            'selects'=>[
                ['Назви головне слово іменникової групи.',[['en','those three carefully edited policy reports']]],
                ['Заповни обидва пропуски за числом головного слова.',[['en','The description of the experimental procedures ___ incomplete. The descriptions of the experimental procedures ___ incomplete.']]],
            ],
            'choices'=>[
                ['Розрізни зміст твердження та ознаку предмета.',[['en','the claim that the device saves energy; the device that the engineers tested']],["Перша частина передає зміст claim; друга ідентифікує device.","Перша ідентифікує claim; друга передає зміст device."]],
                ['Розрізни: технік на малюнку чи ми під час розгляду. Обери пару прочитань.',[['en','We examined the drawing of the technician in the workshop.']],["We examined the drawing showing the technician in the workshop. / While we were in the workshop, we examined the drawing of the technician.","We examined the drawing showing the technician in the workshop. / We examined the drawing of the technician who was in the workshop."]],
            ],
        ],
    ];}
}
