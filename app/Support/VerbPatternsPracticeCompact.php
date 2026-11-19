<?php

namespace App\Support;

use RuntimeException;

/** Finite semantic copy; canonical components, stored values and grading remain unchanged. */
final class VerbPatternsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/verb-patterns-practice-compact.v1.json';
    public const SOURCE_SHA='56c06b9fcf6a64bb584dcd6f6a41bd4dd1aef0af2ca3e9ecc044f219e961a019';
    public const SUPPLEMENT=VerbPatternsPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];

    private static function copy():array
    {
        return [
            'bare-infinitive'=>[
                'selects'=>[
                    ['Доповни дію після модального слова.',[['en','Olena can ____ this gate.'],['uk','Олена може відчинити ці ворота.']]],
                    ['Назви тих, кому дозволили увійти.',[['en','Did the guard let ____ enter?'],['uk','Охоронець дозволив нам увійти?']]],
                ],
                'choices'=>[
                    ['Обери прямий наслідок, не вигадуй наміру.',[['en','The loud noise made the baby cry.']]],
                    ['Відокрем дозвіл від фактичного вибору.',[['en','My aunt let me use her bike, but I chose to walk.']]],
                ],
            ],
            'be-used-to-get-used-to-used-to'=>[
                'selects'=>[
                    ['Запитай про колишню звичку через допоміжне дієслово.',[['en','Did Mark ____ work on Saturdays?'],['uk','Марк раніше працював щосуботи; тепер його графік інший.']]],
                    ['Покажи, що пристосування вже відбулося.',[['en','After two short shifts, Inna ____ working with the new scanner; it felt normal to her.']]],
                ],
                'choices'=>[
                    ['Відокрем звичність від уподобання.',[['en','At my previous job, I was used to long meetings, but I disliked them.']]],
                    ['Звір колишню звичку з теперішньою поведінкою.',[['en','I used to take the tram every day, but now I usually cycle and only take it when it rains.']]],
                ],
            ],
            'gerund'=>[
                'selects'=>[
                    ['Назви дію після уникання.',[['en','She avoids ____ the files.'],['uk','Вона уникає втрати файлів.']]],
                    ['Доповни подяку за дію.',[['en','Thank you for ____ me.'],['uk','Дякую тобі за допомогу.']]],
                ],
                'choices'=>[
                    ['Чи підтверджено малювання саме зараз?',[['en','I enjoy drawing maps.']]],
                    ['Визнач, що саме заперечено.',[['en','A. I do not enjoy working on Fridays.'],['en','B. I enjoy not working on Fridays.']]],
                ],
            ],
            'gerund-vs-infinitive'=>[
                'selects'=>[
                    ['Назви заняття після задоволення.',[['en','Nazar enjoys ____ old radios.'],['uk','Назарові подобається ремонтувати старі радіоприймачі.']]],
                    ['Доповни ввічливе бажання.',[['en','We would like ____ a table.'],['uk','Ми хотіли б забронювати столик.']]],
                ],
                'choices'=>[
                    ['Порівняй початок дії без вигаданої паузи.',[['en','A. The children started drawing.'],['en','B. The children started to draw.']]],
                    ['Чи підтверджено успіх випробуваного способу?',[['en','Olha tried moving the antenna.'],['uk','Ольга перевіряла спосіб покращити прийом радіосигналу.']]],
                ],
            ],
            'stop-remember-forget-try-regret'=>[
                'selects'=>[
                    ['Припинилася саме звичка пити каву.',[['en','He stopped ____ coffee last month.']]],
                    ['Запропонуй перезапуск як спосіб перевірки.',[['en','Try ____ the router.']]],
                ],
                'choices'=>[
                    ['Де виконання повідомлено, а де лише уточнено?',[['en','A. Danylo remembered to turn off the oven.'],['en','B. Did Danylo remember to turn off the oven?']]],
                    ['Звір пропущену дію з пізнішим виконанням.',[['en','I forgot to send the form at noon, but I sent it at two.']]],
                ],
            ],
            'to-infinitive'=>[
                'selects'=>[
                    ['Доповни бажання піти разом.',[['en','My brother wants ____ with us.'],['uk','Мій брат хоче піти з нами.']]],
                    ['Рішення прийняте; заперечено надсилання.',[['en','She decided ____ the email.'],['uk','Вона вирішила не надсилати електронного листа.']]],
                ],
                'choices'=>[
                    ['Визнач виконавця бажаної дії.',[['en','Olia wants Mark to open the window.']]],
                    ['Оціни лише Ігореву можливість сьогодні.',[['en','This bag is too heavy for Ihor to carry today.']]],
                ],
            ],
            'verbs-plus-gerund'=>[
                'selects'=>[
                    ['Назви можливу оренду, не прийняте рішення.',[['en','We are considering ____ a smaller room for the club.'],['uk','Ми лише розглядаємо можливість орендувати кімнату.']]],
                    ['Доповни минулу пропозицію відкласти зустріч.',[['en','Marta suggested ____ the meeting until Friday.']]],
                ],
                'choices'=>[
                    ['Хто хоче відкрити вікно?',[['en','Do you mind me opening the window?']]],
                    ['Не додавай дію, про яку не сказано.',[['en','She finished writing the email.']]],
                ],
            ],
            'verbs-plus-infinitive'=>[
                'selects'=>[
                    ['Запитай про рішення забронювати квитки.',[['en','Did Iryna decide ____ the tickets?']]],
                    ['Прибуття очікуємо від нього, не від себе.',[['en','We hope ____ before eight.'],['uk','Ми сподіваємося, що він прибуде до восьмої.']]],
                ],
                'choices'=>[
                    ['Визнач виконавця дії й адресата обіцянки.',[['en','Nazar promised his sister that he would collect the parcel.']]],
                    ['Звір пропозицію допомоги з фактичною дією.',[['en','The guide offered to carry our bags, but we thanked him and carried them ourselves.']]],
                ],
            ],
            'advanced-gerund-infinitive-patterns'=>[
                'selects'=>[
                    ['Обери форми та висновок про виконання.',[['en','We avoid ____ (store) paint near the heater. We decided ____ (move) the tins.']],
                        ['storing / to move; рішення не доводить виконання','to store / moving; рішення доводить виконання','storing / to move; інфінітив доводить виконання']],
                    ['Обери форми й функції двох часток.',[['en','I look forward to ____ (meet) the curator. I plan to ____ (meet) her on Friday.']],
                        ['meeting / meet; спершу прийменник, далі маркер інфінітива','meet / meeting; обидва — прийменники','meeting / meet; обидва — маркери інфінітива']],
                ],
                'choices'=>[
                    ['Збережи спогад, нагадування та виконане доручення.',[['uk','А: пам’ятаю вчорашнє пакування вази. Б: нагадай підписати коробку. В: учора не забув повернути візок і повернув.']],
                        ['I remember packing the vase yesterday. Remember to label the box. I remembered to return the trolley yesterday.','I remember to pack the vase yesterday. Remember labelling the box. I remember returning the trolley yesterday.']],
                    ['Збережи припинену дію та мету зупинки.',[['uk','А: учні перестали шепотіти. Б: вони призупинили ходьбу, щоб прочитати вказівник.']],
                        ['The students stopped whispering. The students stopped to read the sign.','The students stopped to whisper. The students stopped reading the sign.']],
                ],
            ],
        ];
    }

    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>VerbPatternsPracticeSupplement::SOURCE,'sha256'=>VerbPatternsPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(VerbPatternsPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'verb-patterns-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){$target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$row['condition']):$row['condition']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Verb Patterns finite native copy absent.');$instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Verb Patterns option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>VerbPatternsPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'vpq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Verb Patterns finite native copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Verb Patterns option label must remain short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Verb Patterns cards need two tasks.');}}
        if($items!==90||$cards!==45)throw new RuntimeException('Verb Patterns compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='verb-patterns-practice-compact-v1')return $original;$display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
