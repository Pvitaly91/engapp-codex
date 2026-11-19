<?php

namespace App\Support;

use RuntimeException;

/** Finite teaching copy only; the existing native cards and answer values are unchanged. */
final class PrepositionsPhrasalPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/prepositions-phrasal-practice-compact.v1.json';
    public const SOURCE_SHA='df7a538f8a0ae508b479b19542550adccbb3ee6e0ce546074b9a725584d0ed58';
    public const SUPPLEMENT=PrepositionsPhrasalPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    private static function copy():array
    {
        return [
            'prepositions-of-time'=>[
                'selects'=>[
                    ['Назви точний час відправлення.',[['en','The train leaves ___ 8:30.']]],
                    ['Назви конкретний ранок понеділка.',[['en','We train ___ Monday morning.']]],
                ],
                'choices'=>[
                    ['Чи потрібний прийменник перед next week?', [['en','We start ___ next week.']]],
                    ['Визнач час відправлення, не тривалість поїздки.',[['en','The bus leaves in two hours.'],['uk','Зараз друга.']],['Автобус вирушає о четвертій.','Поїздка обов’язково триває дві години.']],
                ],
            ],
            'prepositions-of-place'=>[
                'selects'=>[
                    ['Назви перебування всередині сумки.',[['en','The keys are ___ my bag.']]],
                    ['Назви положення на поверхні стіни.',[['en','The clock is ___ the wall.'],['uk','Годинник не вбудований у стіну.']]],
                ],
                'choices'=>[
                    ['Обери лише повідомлене місце перебування.',[['en','I am at the cinema.'],['uk','Оля не уточнила, усередині вона чи зовні.']],['Кінотеатр — її місце перебування, точне положення не уточнено.','Вона обов’язково стоїть надворі.']],
                    ['Зроби висновок без вигаданого місця.',[['en','The phone is not in the bag.']],['Телефон обов’язково на сумці.','Відомо лише, що телефон не всередині сумки.']],
                ],
            ],
            'prepositions-of-movement'=>[
                'selects'=>[
                    ['Постав чашку на поверхню столу.',[['en','Put the cup ___ the table.'],['uk','Чашка зараз у руці, не всередині столу.']]],
                    ['Передай стрибок ззовні всередину.',[['en','She jumped ___ the box.'],['uk','До стрибка дівчинка була зовні коробки.']]],
                ],
                'choices'=>[
                    ['Чи доведено завершене прибуття?', [['en','He is going towards the station.'],['en','He is going to the station.'],['uk','Обидва повідомлення зроблено під час подорожі.']],['Друге речення вже підтверджує прибуття.','Жодне не підтверджує, що він уже прибув.']],
                    ['Обери шлях уздовж берега, не через річку.',[['uk','Марія йшла стежкою поряд із річкою, не переходила на інший берег.']],['She walked along the river.','She walked across the river.']],
                ],
            ],
            'dependent-prepositions'=>[
                'selects'=>[
                    ['Оціни вміння пояснювати, не користь пояснень.',[['en','Maksym is good ___ explaining difficult ideas.']]],
                    ['Доповни модель іменника «причина».',[['en','What is the reason ___ the change?'],['uk','Питаємо, чому змінили дату зустрічі.']]],
                ],
                'choices'=>[
                    ['Визнач значення згоди з людиною.',[['en','Olia agrees with Roman.'],['uk','Роман висловив думку, Оля вважає її правильною.']],['Оля поділяє думку Романа.','Оля погодилася виконати будь-яку його пропозицію.']],
                    ['Визнач користь, не вміння.',[['en','Short walks are good for your back.']],['Прогулянки корисні для спини.','Спина добре вміє здійснювати прогулянки.']],
                ],
            ],
            'phrasal-verbs-basics'=>[
                'selects'=>[
                    ['Доповни дієслово після did.',[['en','Did Olia ___ the lamp before leaving?']]],
                    ['Попроси доглянути за дитиною, не шукати її.',[['en','Please look ___ her until her mother returns.'],['uk','Дитина вже спить у своєму ліжку.']]],
                ],
                'choices'=>[
                    ['Визнач, що змінилося між прикладами.',[['en','Marta picked up the box.'],['en','Marta picked up a few words of Polish.']],['Змінилося значення pick up, не частка.','Змінилася частка, це різні сполуки.']],
                    ['Визнач значення сполуки у контексті польоту.',[['en','The plane took off at six.']],['Літак злетів.','Хтось зняв із літака предмет.']],
                ],
            ],
            'separable-and-inseparable-phrasal-verbs'=>[
                'selects'=>[
                    ['Заміни вже назване світло на it.',[['en','Please ___.'],['uk','Потрібен наказ вимкнути світло.']]],
                    ['Назви догляд за вже згаданими дітьми.',[['en','Does Nina ___ on Fridays?'],['uk','Використай look after та them.']]],
                ],
                'choices'=>[
                    ['Оціни обидва порядки з іменниковим додатком.',[['en','A. Write down the number from the message.'],['en','B. Write the number from the message down.'],['uk','В обох випадках потрібно записати той самий довгий номер.']],['Обидва можливі; А часто зручніше з довгим додатком.','Лише А можливе: довгий додаток ніколи не буває перед часткою.']],
                    ['Чи потрібний прямий додаток для цього значення?', [['en','The plane took off at noon.']],['Так, завжди потрібен предмет, який знімають.','Ні, значення «злетіти» не потребує прямого додатка.']],
                ],
            ],
            'common-phrasal-verbs-by-topic'=>[
                'selects'=>[
                    ['Назви створення резервних копій файлів.',[['en','I ___ every Friday.'],['uk','Them — вже названі файли; використай back up.']]],
                    ['Передай реєстрацію на курс, не вхід до акаунта.',[['en','Olia signed ___ the course.']]],
                ],
                'choices'=>[
                    ['Тарас розповідає про себе. Що сталося о шостій?',[['en','I woke up at six, but I didn’t get up until seven.']],['Тарас уже встав із ліжка.','Тарас прокинувся, але ще лежав.']],
                    ['Обери потребу, не вигаданий завершений результат.',[['en','We need to sort out the booking problem today.']],['Проблему потрібно владнати; результат не повідомлено.','Проблему вже успішно розв’язано.']],
                ],
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>PrepositionsPhrasalPracticeSupplement::SOURCE,'sha256'=>PrepositionsPhrasalPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(PrepositionsPhrasalPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'prepositions-phrasal-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>str_replace('{a1}','____',$row['condition'])],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Prepositions/Phrasal finite teaching copy absent.');
                        $instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Prepositions/Phrasal option labels differ.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>PrepositionsPhrasalPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)))];
                    if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'ppq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Prepositions/Phrasal finite copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Prepositions/Phrasal option label must stay short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Prepositions/Phrasal cards need two tasks.');}}
        if($items!==70||$cards!==35)throw new RuntimeException('Prepositions/Phrasal compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='prepositions-phrasal-practice-compact-v1')return $original;
        $display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];
        foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
