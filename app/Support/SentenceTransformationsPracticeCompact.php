<?php

namespace App\Support;

use RuntimeException;

/** Finite teaching copy only; the existing native cards and answer values are unchanged. */
final class SentenceTransformationsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/sentence-transformations-practice-compact.v1.json';
    public const SOURCE_SHA='f0a752701bbfa33c6eb641e2e59872b6b519a5a45d25880fcf10b17ac2391e3c';
    public const SUPPLEMENT=SentenceTransformationsPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    public const DELEGATE_TOPIC='word-order-emphasis';
    public const DELEGATE_SOURCE=WordOrderPracticeSupplement::SOURCE;
    public const DELEGATE_SHA=WordOrderPracticeSupplement::SOURCE_SHA;

    /** Page153 already has ten WOQ tasks. Only their finite display is added here. */
    public static function delegatedSource():array
    {
        $target=array_column(WordOrderPracticeSupplement::load()['targets'],null,'topic')['transformations']??null;
        $original=array_column(SentenceTransformationsPracticeSource::load()['targets'],null,'topic')[self::DELEGATE_TOPIC];
        if(!is_array($target)||($target['binding']['page_id']??null)!==153||($target['binding']['page_category_id']??null)!==23
            ||($target['binding']['seeder']??null)!==$original['identity']||($target['binding']['uuid']??null)!==$original['binding']['uuid']
            ||($target['binding']['body_sha256']??null)!==$original['binding']['body_sha256']
            ||array_map(static fn($k)=>count($target['after_data'][$k]??[]),['selects','choices','inputs','rephrase'])!==[4,2,4,0])throw new RuntimeException('Sentence Transformations exact existing WOQ delegate differs.');
        return $target;
    }

    private static function delegatedTarget():array
    {
        $source=self::delegatedSource();$data=$source['after_data'];$copy=[
            'selects'=>[
                ['Збережи минулі дії; інвертуй головну частину.',[['en','Only after I read the example ___ the rule.']]],
                ['Підтвердь теперішню звичку всупереч запереченню.',[['en','She ___ every Friday.'],['uk','Ольга таки телефонує щоп’ятниці, хоча співрозмовник це заперечує.']]],
                ['Доповни виділення виконавця його дією.',[['en','It was the night porter ____.'],['uk','Саме нічний портьє помітив витік.']]],
                ['Заверши виділення потрібного предмета.',[['en','What the residents need ____.'],['uk','Мешканцям потрібне саме надійне водопостачання.']]],
            ],
            'choices'=>[
                ['Чи потрібна інверсія після цього підмета?',[['en','Only Tom understood the rule.'],['uk','Правило зрозумів лише Том.']],['Ні: «лише» обмежує підмет.','Так: після початкового «лише» завжди потрібна інверсія.']],
                ['Чи завжди сила бажання залишається однаковою?',[['en','I really want to help. → I do want to help.'],['uk','Мовець описує сильне бажання, а не спростовує сумнів у ньому.']],['Ні: підтвердження факту не обов’язково передає силу.','Так: підсилення завжди означає дуже сильне бажання.']],
            ],
            'inputs'=>[
                ['Виділи виконавця й збережи протиставлення.',[['uk','Саме Том зламав принтер, а не я.'],['uk','Почни з розщепленої конструкції; не додавай значення «тільки».']]],
                ['Почни з часової межі та інвертуй головну частину.',[['uk','Лише після того, як я прочитав приклад, я зрозумів правило.'],['uk','Збережи обидва підмети й обидві прості минулі дії.']]],
                ['Переклади речення англійською.',[['uk','Людина, з якою нам треба порадитися, — начальник порту.'],['uk','Спочатку назви потрібного фахівця, після зв’язки — його посаду.']]],
                ['Почни з прислівника рідкісної дії.',[['uk','Ці прилади рідко потребують повторного калібрування.'],['uk','Постав допоміжне дієслово перед підметом у формальній моделі.']]],
            ],
            'rephrase'=>[],
        ];
        $target=['topic'=>self::DELEGATE_TOPIC,'binding'=>$source['binding'],'after_data_sha256'=>SentenceTransformationsPracticeSupplement::digest($data),'projection_family'=>'sentence-transformations-practice-compact-v1','items'=>[],'groups'=>[],'delegated_source'=>['path'=>self::DELEGATE_SOURCE,'sha256'=>self::DELEGATE_SHA,'topic'=>'transformations','task_mutations'=>0]];
        foreach(['selects','choices','inputs','rephrase']as$kind){$target['items'][$kind]=[];
            foreach($data[$kind]??[]as$index=>$item){$c=$copy[$kind][$index];$row=['index'=>$index,'source_item_sha256'=>SentenceTransformationsPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$c[0],'stimuli'=>array_map(static fn($v)=>['lang'=>$v[0],'text'=>$v[1]],$c[1]),'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if(isset($c[2]))$row['option_labels']=array_combine($item['options'],$c[2]);$target['items'][$kind][]=$row;}
            foreach(array_chunk(array_keys($data[$kind]??[]),2)as$indices)$target['groups'][]=['key'=>'stq-woq-delegate-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$kind==='selects'?'Обери правильну форму':($kind==='choices'?'Обери правильну відповідь':($indices[0]===0?'Побудуй речення':'Переклади речення')),'instruction'=>$kind==='inputs'?'Введи відповідь вручну або натискай на перемішані групи.':'Обери одну відповідь для кожної умови.'];
        }return $target;
    }
    private static function copy():array
    {
        return [
            'tenses'=>[
                'selects'=>[
                    ['Познач подію, яка відбулася раніше за прибуття.',[['en','By the time we arrived, the film ___.']]],
                    ['Назви початкову точку триваючого проживання.',[['en','She has lived here ___ March 2020.']]],
                ],
                'choices'=>[
                    ['Чи однаковий порядок подій?',[['en','When we arrived, the film started.'],['en','By the time we arrived, the film had started.']],['Ні: у другій версії фільм почався раніше.','Так: початок одночасний із прибуттям в обох.']],
                    ['Чи доведено, що навчання триває досі?',[['en','I started learning French three years ago.'],['uk','Навчальна дата — перше вересня дві тисячі двадцять четвертого; інших відомостей немає.']],['Так: дата початку доводить продовження.','Ні: продовження треба підтвердити окремо.']],
                ],
            ],
            'modals'=>[
                'selects'=>[
                    ['Збережи минулий обов’язок, не роби висновку.',[['en','Olha ___ leave before six yesterday.'],['uk','Учора Ольга мусила піти до шостої.']]],
                    ['Передай заборону короткою модальною формою.',[['en','You ___ enter this room.'],['uk','Вхід до кімнати заборонено.']]],
                ],
                'choices'=>[
                    ['Чи підтверджено успішне завершення?',[['en','We could finish the report yesterday, and we did.']],['Так: завершення прямо підтверджено.','Ні: друга частина повідомляє про незавершення.']],
                    ['Що передає це речення?',[['en','Oleh must have left the office.'],['uk','Офіс порожній, пальта Олега немає.']],['Упевнений висновок про минуле за ознаками.','Правило, яке зобов’язувало Олега піти.']],
                ],
            ],
            'passive'=>[
                'selects'=>[
                    ['Перебудуй завершену минулу дію в пасив.',[['en','The letter ___ by Lina yesterday.'],['uk','Ліна написала лист учора.']]],
                    ['Збережи незавершений процес зараз.',[['en','The gate ___.'],['uk','Працівники зараз фарбують хвіртку; робота триває.']]],
                ],
                'choices'=>[
                    ['Збережи названу виконавицю та майбутній час.',[['en','Marta will inspect the bridge tomorrow.']],['The bridge will be inspected tomorrow.','The bridge will be inspected by Marta tomorrow.']],
                    ['Збережи питання, відправника й одержувачку.',[['en','Did Oleh send Iryna the files yesterday?']],['Were the files sent to Iryna by Oleh yesterday?','Were the files sent to Oleh by Iryna yesterday?']],
                ],
            ],
            'reported-speech'=>[
                'selects'=>[
                    ['Збережи процес у переказі з минулої точки.',[['en','Iryna told Oleh that she ___ that day.'],['uk','У понеділок на роботі Ірина сказала, що працює; у середу вдома Олег переказує її слова.']]],
                    ['Передай заперечне прохання після адресата.',[['en','The guide asked us ___ the map.'],['uk','Після екскурсії учасник нашої групи переказує в іншій кімнаті: гід просив не торкатися карти.']]],
                ],
                'choices'=>[
                    ['Збережи Ліну, звіт і наступний щодо її слів день.',[['en','“I will finish this report tomorrow.”'],['uk','Ліна сказала це Олегові в понеділок в офісі; у середу вдома Олег переказує її слова.']],['Lina said that she would finish that report the next day.','Lina said that I would finish that report the next day.']],
                    ['Збережи статус переказу, а не окремого факту.',[['en','“I have posted the letter.”'],['uk','Олег сказав це Марії в понеділок в офісі; у вівторок удома вона переказує без незалежного підтвердження.']],['Oleh had posted the letter.','Oleh said that he had posted the letter.']],
                ],
            ],
            'conditionals'=>[
                'selects'=>[
                    ['Збережи негативну умову без другого заперечення.',[['en','Unless you ___, you’ll miss the train.'],['uk','Якщо не поквапишся, пропустиш поїзд.']]],
                    ['Передай теперішній жаль про минуле навчання.',[['en','I wish I ___ harder last year.'],['uk','Минулорічне навчання вже закінчилося; мовець шкодує зараз.']]],
                ],
                'choices'=>[
                    ['Чи гарантує поспіх, що ти встигнеш?',[['en','Unless you hurry, you’ll miss the train.'],['uk','Відоме лише це попередження.']],['Ні: можуть бути інші перешкоди.','Так: поспіх сам гарантує успіх.']],
                    ['Чи незмінна сила звичайної нейтральної поради?',[['en','You should take a short break. → You had better take a short break.'],['uk','Терміновість і небажаний наслідок не задано.']],['Так: обидва вислови завжди нейтральні.','Ні: друга версія додає попередження.']],
                ],
            ],
            'relative-clauses-linking-words'=>[
                'selects'=>[
                    ['Назви займенник предмета, який ми відвідали.',[['en','The office ___ we visited was empty.']]],
                    ['Збережи причину перед іменниковою групою.',[['en','We stayed indoors ___ the heavy rain.'],['uk','Дощ був причиною, не перешкодою всупереч якій ми залишилися вдома.']]],
                ],
                'choices'=>[
                    ['Чи збережено того, хто був утомлений?',[['en','Although Marta was tired, Olena finished the report.'],['en','Despite being tired, Olena finished the report.']],['Так: скорочення завжди зберігає учасників.','Ні: скорочення приписує втому Олені.']],
                    ['Чи збережено того, хто викладає?',[['en','I met a woman. Her son teaches chemistry.'],['en','I met a woman who teaches chemistry.']],['Ні: у другій версії викладає жінка, не син.','Так: виконавець не змінився.']],
                ],
            ],
            'articles-quantifiers'=>[
                'selects'=>[
                    ['Назви дві злічувані одиниці незлічуваної поради.',[['en','She gave me two useful ___.'],['uk','Дві корисні рекомендації; збережи незлічуваний іменник.']]],
                    ['Передай відсутність порад без другого заперечення.',[['en','Lena didn’t receive ___ advice yesterday.']]],
                ],
                'choices'=>[
                    ['Чи можна додати точну кількість без нових даних?',[['en','She gave me some useful advice. → She gave me exactly one useful piece of advice.']],['Так: неозначена кількість завжди означає одну пораду.','Ні: точну одиничну кількість не підтверджено.']],
                    ['Чи доведено, що хоч один стілець залишився?',[['en','There aren’t many chairs left.']],['Ні: речення не підтверджує ненульову кількість.','Так: мала кількість обов’язково ненульова.']],
                ],
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>SentenceTransformationsPracticeSupplement::SOURCE,'sha256'=>SentenceTransformationsPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(SentenceTransformationsPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'sentence-transformations-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';
                            if($gap){$sentence=str_replace('{a1}','____',$row['condition']);$guidance=$row['hint_uk'];}
                            else{$sentence=SentenceTransformationsNativePlans::translationSentences()[$source['topic']][$index-4];if(!str_starts_with($row['condition'],$sentence))throw new RuntimeException('Sentence Transformations authored sentence boundary differs.');$guidance=trim(mb_substr($row['condition'],mb_strlen($sentence))).' '.$row['hint_uk'];}
                            $stimuli=[['lang'=>$gap?'en':'uk','text'=>$sentence],['lang'=>'uk','text'=>$guidance]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Sentence Transformations finite teaching copy absent.');
                        $instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Sentence Transformations option labels differ.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>SentenceTransformationsPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)))];
                    if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'stq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }
        $core=array_column($doc['targets'],null,'topic');$core[self::DELEGATE_TOPIC]=self::delegatedTarget();$doc['targets']=[];
        foreach(array_keys(SentenceTransformationsPracticeSource::SPECS)as$topic)$doc['targets'][]=$core[$topic];
        return $doc;
    }
    public static function validate(array $doc):void
    {
        $core=$doc;$core['targets']=array_values(array_filter($core['targets']??[],static fn($t)=>$t['topic']!==self::DELEGATE_TOPIC));parent::validate($core);
        if($doc!==self::compile())throw new RuntimeException('Sentence Transformations finite copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Sentence Transformations option label must stay short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Sentence Transformations cards need two tasks.');}}
        if($items!==80||$cards!==40)throw new RuntimeException('Sentence Transformations compact coverage differs.');
    }
    public static function presentation(object $block,array $data,bool $canonicalCaller):?array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk')return null;
        if(($block->page_id??null)!==153)return parent::presentation($block,$data,$canonicalCaller);
        try{$source=self::delegatedSource();$target=array_column(self::load()['targets'],null,'topic')[self::DELEGATE_TOPIC];
            foreach($target['binding']as$field=>$expected)if($field!=='body_sha256'&&($block->$field??null)!==$expected)return null;
            if(!is_string($block->body??null)||!hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))||$data!==$source['after_data'])return null;
            $target['groups_by_kind']=[];foreach($target['groups']as$i=>$group){$group['number']=$i+1;$target['groups_by_kind'][$group['kind']][]=$group;}return $target;
        }catch(\Throwable){return null;}
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='sentence-transformations-practice-compact-v1')return $original;
        $display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];
        foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
