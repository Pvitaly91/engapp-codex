<?php

namespace App\Support;

use RuntimeException;

/** Finite teaching copy only; the existing native cards and answer values are unchanged. */
final class ClausesLinkingPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/clauses-linking-practice-compact.v1.json';
    public const SOURCE_SHA='0563d7359d82f207e79d68d9a7aaef172d1a770b5c9f2a1048738cc5b7ee49aa';
    public const SUPPLEMENT=ClausesLinkingPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    private static function copy():array
    {
        return [
            'linking-words-reason-result-contrast'=>[
                'selects'=>[
                    ['Доповни причину перед повною частиною.',[['en','We moved the workshop online ___ the building was closed.']]],
                    ['Доповни причину перед іменниковою групою.',[['en','The workshop was moved online ___ a power cut.']]],
                ],
                'choices'=>[
                    ['Обери коректну межу самостійних частин.',[['uk','Потрібна зв’язка therefore замість so.']],['The final bus had left, therefore we walked home.','The final bus had left; therefore, we walked home.']],
                    ['Обери конструкцію з повною допустовою частиною.',[],['Although the hall was small, everyone found a seat.','Despite the hall was small, everyone found a seat.']],
                ],
            ],
            'advanced-linking-devices'=>[
                'selects'=>[
                    ['Доповни наслідок відсутності файлів.',[['en','Half the files were missing; ___, the audit was postponed.']]],
                    ['Назви необхідну умову використання записів.',[['en','You may use the recordings ___ every participant gives written consent.']]],
                ],
                'choices'=>[
                    ['Обери додавання ще однієї переваги.',[['en','The service costs less. ___, it includes evening support.']],['Moreover / Furthermore','Лише Therefore']],
                    ['Обери обмеження ступеня корисності.',[['en','The model is useful ___ it explains recurring errors.']],['provided that','insofar as']],
                ],
            ],
            'concessive-and-contrastive-structures'=>[
                'selects'=>[
                    ['Назви реальну перешкоду, яка не завадила.',[['en','___ the network was down, the team delivered the report on time.'],['uk','Записи підтверджують: учора зв’язку не було.']]],
                    ['Назви можливу умову, від якої рішення не залежить.',[['en','We will meet as planned ___ the network is down tomorrow.'],['uk','Завтрашній стан зв’язку ще невідомий.']]],
                ],
                'choices'=>[
                    ['Обери одночасність двох дій.',[['en','I took notes ___ the speaker was explaining the diagram.']],['while','whereas']],
                    ['Збережи справжнього носія втоми.',[['uk','Виснажена Мая; презентація тривала.']],['Although exhausted, the presentation continued.','Although Maya was exhausted, the presentation continued.']],
                ],
            ],
            'participle-clauses-basics'=>[
                'selects'=>[
                    ['Знайди дієприкметникову форму.',[['en','The woman carrying a red folder is our guide.']]],
                    ['Обери пасивний опис квитків.',[['en','The tickets ___ this morning are on the desk.'],['uk','Квитки надруковано, вони не друкують самі.']]],
                ],
                'choices'=>[
                    ['Визнач іменник, який описує зворот.',[['en','We photographed the bus parked beside the school.']],['Автобус; підмет головної частини — we.','We; автобус є лише місцем дії.']],
                    ['Оціни приєднання початкового звороту.',[['en','Opening the cupboard, a plate fell onto the floor.'],['uk','Задум: хтось відкривав шафку, тарілка впала.']],['Коректне приєднання.','Помилка: за будовою шафку відкриває тарілка.']],
                ],
            ],
            'participle-clauses'=>[
                'selects'=>[
                    ['Підкресли завершення попередньої дії.',[['en','___ the files, Sofia made a backup.'],['uk','Спершу Софія завершила сортування, потім створила копію.']]],
                    ['Обери пасивну форму з передуванням.',[['en','___ by a specialist, the lift was cleared for use.'],['uk','Фахівець оглянув ліфт до дозволу на використання.']]],
                ],
                'choices'=>[
                    ['Розрізни стан і попередню завершену дію.',[['en','Having a valid pass, Yana entered the archive.'],['en','Having obtained a valid pass, Yana entered the archive.']],['Перше — «маючи»; друге — perfect. After obtaining зберігає порядок.','Перше — perfect; друге — «маючи».']],
                    ['Перевір збереження змісту скорочення.',[['en','Because the filter might be damaged, the operator stopped the pump.'],['en','→ Damaged, the operator stopped the pump.']],['Зміст збережено.','Зникає можливість might; пошкодження приписано операторові.']],
                ],
            ],
            'advanced-participle-and-absolute-clauses'=>[
                'selects'=>[
                    ['Обери пасивну ознаку спорядження.',[['en','The equipment ___, the climbers left the hut.'],['uk','Спорядження вже спакували.']]],
                    ['Явно передай попереднє прибуття групи.',[['en','The last group ___, the coordinator began the briefing.'],['uk','Остання група вже прибула до початку інструктажу.']]],
                ],
                'choices'=>[
                    ['Визнач підмет початкового звороту.',[['en','The audience watching in silence, the pianist began the final movement.']],['Абсолютна конструкція: спостерігає публіка.','Помилка: спостереження приписане піаністові.']],
                    ['Розрізни абсолютну конструкцію й помилкове приєднання.',[['en','The guide reading the map, we waited by the gate.'],['en','Reading the map, the gate stood open.']],['Перше — абсолютна конструкція; друге — помилкове приєднання.','Навпаки: перше помилкове, друге абсолютне.']],
                ],
            ],
            'discourse-markers-and-cohesion'=>[
                'selects'=>[
                    ['Обери поступку без зміни змісту.',[['en','The instructions are brief. ___, they describe all three stages.']],['Nonetheless','Nevertheless','Conversely','Consequently']],
                    ['Додай ще одну перевагу посібника.',[['en','The handbook includes a glossary. ___, it provides an index of names.']],['Moreover','Furthermore','In addition','Без маркера: два речення.','Без маркера: поєднання через and.','Consequently']],
                ],
                'choices'=>[
                    ['Уточни відсилання лише до відсутніх дат.',[['en','The files have clear titles, but three files have no dates. In light of this, the chronology remains uncertain.']],['In light of these missing dates...','In light of these clear titles...']],
                    ['Прибери зайві зв’язки, зберігши два розділи.',[['uk','У звіті саме два основні розділи: підсумок і список витрат.']],['The report has two main sections: a summary and a list of costs.','The report contains a summary and a list of costs. These are its two main sections.','The report contains a summary; consequently, it lists the costs.']],
                ],
            ],
            'relative-clauses'=>[
                'selects'=>[
                    ['Назви належність скрипки учениці.',[['en','The student ___ violin needs repair is absent.']]],
                    ['Назви місце зустрічі.',[['en','The courtyard ___ we met is now closed.'],['uk','Йдеться про двір, де відбулася зустріч, не про об’єкт відвідування.']]],
                ],
                'choices'=>[
                    ['Визнач виконавців обох дій.',[['en','The assistant who she trained now checks the bookings.'],['uk','She — менеджерка Ольга.']],['Помічник навчав; Ольга перевіряє.','Ольга навчала; помічник перевіряє.','She зайве: виконавця треба видалити.']],
                    ['Розрізни ролі в головній і відносній частинах.',[['en','I thanked the volunteers who carried the boxes.']],['Volunteers — додаток thanked; who — підмет carried.','Volunteers і who — додатки обох дієслів.','Who — підмет thanked; волонтери виконують thanked.']],
                ],
            ],
            'defining-relative-clauses'=>[
                'selects'=>[
                    ['Узгодь відносний підмет у множині.',[['en','The technicians who ___ near the station arrive early.']]],
                    ['Назви музей як об’єкт відвідування.',[['en','The museum ___ we visited is being repaired.']]],
                ],
                'choices'=>[
                    ['Вибери лише двох екскурсоводів із шести.',[['uk','Покликати треба двох, які розмовляють польською.'],['en','A. The guides who speak Polish should come here.'],['en','B. The guides, who speak Polish, should come here.']],['A: відбирає двох за ознакою.','B: відбирає двох за ознакою.','Обидва однаково відбирають двох.']],
                    ['Оціни вилучення всього відносного опису.',[['en','The parcel that we weighed is on the counter.'],['uk','Є чотири посилки; зважили одну, слухач ще не знає яку.']],['У будь-якому контексті стане неграматичним.','Граматичне, але більше не визначає потрібну посилку.','Автоматично йтиметься про всі чотири посилки.']],
                ],
            ],
            'non-defining-relative-clauses'=>[
                'selects'=>[
                    ['Доповни додатковий опис визначеної камери.',[['en','This camera, ___ I borrowed for the trip, needs a new battery.']]],
                    ['Назви належність нашийника Рексові.',[['en','Rex, ___ collar is blue, belongs to Iryna.']]],
                ],
                'choices'=>[
                    ['Вибери двох музикантів, які читають ноти.',[['uk','У гурті шестеро; лише двоє читають ноти, саме їх запрошено.'],['en','A. The musicians who read music will join the first rehearsal.'],['en','B. The musicians, who read music, will join the first rehearsal.']],['B: опис додає факт про всю групу.','A: опис відбирає потрібних двох.','Обидва обов’язково запрошують усіх шістьох.']],
                    ['Збережи ролі й додатковий відносний займенник.',[['en','I greeted Svitlana, who Oleh had invited.'],['uk','Світлану вже визначено; її запросив Олег.']],['Світлана запросила Олега; who — підмет.','Олег запросив Світлану; Oleh і who залишаються.','Олег запросив Світлану; who обов’язково видаляємо.']],
                ],
            ],
            'omitting-relative-pronouns'=>[
                'selects'=>[
                    ['Опусти лише відносне слово, збережи виконавицю.',[['en','The recipe ___ is easy.'],['uk','Рецепт, який Яна мені надіслала, простий.']]],
                    ['Збережи прийменник інструмента.',[['en','The key I opened the cabinet ___ is on the ring.']]],
                ],
                'choices'=>[
                    ['Чи можна просто вилучити whom у цій моделі?', [['en','Our guide, whom Marta had met before, speaks Polish.'],['uk','Гіда вже визначено; зустріч Марти — додатковий факт.']],['Так, наступний підмет завжди дозволяє пропуск.','Ні, у додатковому описі whom і Marta залишаються.','Ні, але Marta зайва: виконавець уже whom.']],
                    ['Визнач роль that незалежно від головного питання.',[['en','Did you find the gloves that protect your hands?']],['Можна прибрати: головний підмет уже you.','That — підмет protect і залишається.','That треба замінити на do.']],
                ],
            ],
            'linking-words'=>[
                'selects'=>[
                    ['Назви причину закриття стежки.',[['en','The path was closed ___ a tree had fallen across it.']]],
                    ['Назви мету з окремим підметом.',[['en','The curator lowered the screen ___ the children could see the model.'],['uk','Екран опустили, щоб дітям було видно модель.']]],
                ],
                'choices'=>[
                    ['Обери коректну межу речень із however.',[['en','A. The screen was cracked, however, it still worked.'],['en','B. The screen was cracked. However, it still worked.']],['A: however робить другу частину залежною.','B: крапка розділяє самостійні речення.','Жоден: після крапки however неможливе.']],
                    ['Зроби висновок без вигаданої причини.',[['en','At noon the bell rang. At one the parcel arrived.'],['uk','Відомо лише час цих двох подій.']],['Дзвінок спричинив доставку.','Відомий лише часовий порядок.','Доставка спричинила попередній дзвінок.']],
                ],
            ],
            'reason-because-since-as'=>[
                'selects'=>[
                    ['Доповни причину перед повною частиною.',[['en','We waited ____ the train was late.']]],
                    ['Доповни причину перед іменником.',[['en','The lesson started late ____ the delay.']]],
                ],
                'choices'=>[
                    ['Де since називає причину, не початок періоду?', [['en','A. Since the hall is closed, we will meet outside.'],['en','B. We have met outside since the hall closed.']]],
                    ['Визнач, що саме заперечує мовець.',[['en','I left, but not because I was bored.']]],
                ],
            ],
            'result-so-therefore-as-a-result'=>[
                'selects'=>[
                    ['Доповни наслідок поломки автобуса.',[['en','The bus broke down, ____ we walked home.']]],
                    ['Обери коректну межу самостійних частин.',[['en','A. The file was lost; therefore, we repeated the work.'],['en','B. The file was lost, therefore, we repeated the work.']]],
                ],
                'choices'=>[
                    ['Обери повідомлений факт без домислу.',[['en','The printer is broken, so we will send the file by email.']]],
                    ['Чи доведено виконання названої мети?', [['en','I moved the box so that Olia could sit down.']]],
                ],
            ],
            'contrast-although-though-however'=>[
                'selects'=>[
                    ['Доповни допустову частину з дієсловом.',[['en','____ the bag was heavy, Olia carried it.']]],
                    ['Обери коректну межу самостійних частин.',[['en','A. The room was small; however, everyone had a seat.'],['en','B. The room was small, however, everyone had a seat.']]],
                ],
                'choices'=>[
                    ['Визнач значення всієї конструкції.',[['en','However late it was, we kept working.']]],
                    ['Обери зміст застереження наприкінці.',[['en','The walk was long. We enjoyed it, though.'],['uk','Співрозмовник вважав тривалість недоліком.']]],
                ],
            ],
            'purpose-to-in-order-to-so-that'=>[
                'selects'=>[
                    ['Назви мету зупинки Катерини.',[['en','Kateryna stopped ____ buy a notebook.']]],
                    ['Назви виконавицю дії у частині мети.',[['en','I moved the lamp so that ____ could see the page.'],['uk','Лампу пересунув мовець, бачити сторінку мала Оля.']]],
                ],
                'choices'=>[
                    ['Чи підтверджено досягнення мети?', [['en','I set an alarm so that I would not miss the train.']]],
                    ['Розрізни функцію речі й мету окремої дії.',[['en','A. This brush is for cleaning bottles.'],['en','B. I took this brush to clean a bottle.']]],
                ],
            ],
            'relative-clauses-with-whose-and-which'=>[
                'selects'=>[
                    ['Назви належність колекції галереї.',[['en','I visited a gallery ___ collection includes local ceramics.']]],
                    ['Доповни підмет додаткового опису проєктора.',[['en','The small projector, ___ stopped working yesterday, is under repair.'],['uk','Проєктор уже визначено; займенник сам виконує роль підмета.']]],
                ],
                'choices'=>[
                    ['Обери пояснення для двох пошкоджених із шести.',[['uk','Є шість коробок: дві пошкоджені, їх повернули; чотири цілі.'],['en','A. The boxes which were damaged were sent back.'],['en','B. The boxes, which were damaged, were sent back.']],['A відбирає дві; B приписувало б пошкодження всім шести.','B обов’язкове: which завжди вимагає ком.','A заперечує існування інших пошкоджених коробок у світі.']],
                    ['Визнач точне відсилання which.',[['en','Sofia closed the warehouse early, which forced the couriers to wait outside.'],['uk','Чекати змусило саме раннє закриття.']],['До самої будівлі, не факту закриття.','До факту раннього закриття складу.','До кур’єрів як тих, хто зачинив склад.']],
                ],
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>ClausesLinkingPracticeSupplement::SOURCE,'sha256'=>ClausesLinkingPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(ClausesLinkingPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'clauses-linking-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>str_replace('{a1}','____',$row['condition'])],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Clauses finite teaching copy absent.');
                        $instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Clauses option labels differ.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>ClausesLinkingPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)))];
                    if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'clq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Clauses finite copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Clauses option label must stay short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Clauses cards need two tasks.');}}
        if($items!==170||$cards!==85)throw new RuntimeException('Clauses compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='clauses-linking-practice-compact-v1')return $original;
        $display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];
        foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
