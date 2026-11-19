<?php

namespace App\Support;

use RuntimeException;

/** Finite teaching copy only; the existing native cards and answer values are unchanged. */
final class CommonMistakesPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/common-mistakes-practice-compact.v1.json';
    public const SOURCE_SHA='de5f86723c90f3cc539202abce50f87dd3bce6999ac9ec8c63b84c05f4adb60c';
    public const SUPPLEMENT=CommonMistakesPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    public const DELEGATE_TOPIC='word-order-common-mistakes';
    public const DELEGATE_SOURCE=WordOrderPracticeSupplement::SOURCE;
    public const DELEGATE_SHA=WordOrderPracticeSupplement::SOURCE_SHA;

    /** Page43 already has ten WOQ tasks. Only their finite display is added here. */
    public static function delegatedSource():array
    {
        $target=array_column(WordOrderPracticeSupplement::load()['targets'],null,'topic')['common-mistakes']??null;
        $original=array_column(CommonMistakesPracticeSource::load()['targets'],null,'topic')[self::DELEGATE_TOPIC];
        if(!is_array($target)||($target['binding']['page_id']??null)!==43||($target['binding']['page_category_id']??null)!==9
            ||($target['binding']['seeder']??null)!==$original['identity']||($target['binding']['uuid']??null)!==$original['binding']['uuid']
            ||($target['binding']['body_sha256']??null)!==$original['binding']['body_sha256']
            ||array_map(static fn($k)=>count($target['after_data'][$k]??[]),['selects','choices','inputs','rephrase'])!==[4,2,4,0])throw new RuntimeException('Common Mistakes exact existing WOQ delegate differs.');
        return $target;
    }

    private static function delegatedTarget():array
    {
        $source=self::delegatedSource();$data=$source['after_data'];$copy=[
            'selects'=>[
                ['Побудуй пряме інформаційне питання.',[['en','Where ___ going?'],['uk','Питаємо співрозмовника, куди він іде зараз.']]],
                ['Запереч теперішній стан повною формою.',[['en','She ___ ready.'],['uk','Вона зараз не готова.']]],
                ['Постав прислівник частотності перед дією.',[['en','I ____ the side gate.'],['uk','Я зазвичай замикаю бічну хвіртку.']]],
                ['Постав «досі» після дієслова-зв’язки.',[['en','My gloves ____ wet.'],['uk','Мої рукавички досі мокрі.']]],
            ],
            'choices'=>[
                ['Чи треба виправляти питання про підмет?',[['en','Who broke the window?']],['Ні: це правильне питання про підмет.','Так: перед who завжди потрібне did.']],
                ['Збережи головне питання та вкладений підмет.',[['en','Do you know who called?']],['Прибрати Do й поставити called перед who.','Залишити: обидві частини мають правильний порядок.']],
            ],
            'inputs'=>[
                ['Склади ввічливе питання з усіх груп.',[['uk','Можеш сказати мені, де вокзал?'],['uk','Збережи ввічливе головне питання; у вкладеній частині підмет передує зв’язці.']]],
                ['Склади заперечне речення з усіх груп.',[['uk','Він не любить гостру їжу.'],['uk','Заперечення утворює допоміжне дієслово; основна дія не має другого особового закінчення.']]],
                ['Переклади речення англійською.',[['uk','Ми вже заплатили за квитки.'],['uk','Слово «вже» постав між допоміжною формою та дієприкметником оплати.']]],
                ['Переклади питання англійською.',[['uk','Твої двоюрідні брати й сестри отримали задоволення від пікніка?'],['uk','Запитай про минулу подію; постав допоміжне дієслово перед учасниками.']]],
            ],
            'rephrase'=>[],
        ];
        $target=['topic'=>self::DELEGATE_TOPIC,'binding'=>$source['binding'],'after_data_sha256'=>CommonMistakesPracticeSupplement::digest($data),'projection_family'=>'common-mistakes-practice-compact-v1','items'=>[],'groups'=>[],'delegated_source'=>['path'=>self::DELEGATE_SOURCE,'sha256'=>self::DELEGATE_SHA,'topic'=>'common-mistakes','task_mutations'=>0]];
        foreach(['selects','choices','inputs','rephrase']as$kind){$target['items'][$kind]=[];
            foreach($data[$kind]??[]as$index=>$item){$c=$copy[$kind][$index];$row=['index'=>$index,'source_item_sha256'=>CommonMistakesPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$c[0],'stimuli'=>array_map(static fn($v)=>['lang'=>$v[0],'text'=>$v[1]],$c[1]),'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if(isset($c[2]))$row['option_labels']=array_combine($item['options'],$c[2]);$target['items'][$kind][]=$row;}
            foreach(array_chunk(array_keys($data[$kind]??[]),2)as$indices)$target['groups'][]=['key'=>'cmq-woq-delegate-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$kind==='selects'?'Обери правильну форму':($kind==='choices'?'Обери правильну відповідь':($indices[0]===0?'Побудуй речення':'Переклади речення')),'instruction'=>$kind==='inputs'?'Введи відповідь вручну або натискай на перемішані групи.':'Обери одну відповідь для кожної умови.'];
        }return $target;
    }
    private static function copy():array
    {
        return [
            'articles-common-mistakes'=>[
                'selects'=>[
                    ['Назви одну кваліфікацію, орієнтуючись на перший звук.',[['en','She has ___ MBA.'],['uk','MBA читають «ем-бі-ей»; кваліфікацію згадано вперше.']]],
                    ['Чи потрібний артикль перед назвою напою загалом?',[['en','I drink ___ coffee every morning.']]],
                ],
                'choices'=>[
                    ['Чи треба виправляти артикль?',[['en','The coffee in this cup is cold.'],['uk','Співрозмовники бачать конкретну чашку з цією кавою.']],['Залишити: визначено конкретну каву.','Прибрати the: перед coffee його ніколи не буває.']],
                    ['Чи потрібно прибрати the?',[['en','I went to the school to meet the headteacher.'],['uk','Дорослий відвідувач ішов до відомої школи на зустріч, не навчатися.']],['Так: school завжди без артикля.','Ні: школа визначена в ситуації.']],
                ],
            ],
            'present-perfect-common-mistakes'=>[
                'selects'=>[
                    ['Доповни узгоджену форму із just.',[['en','Mia ___ the report. It is ready now.'],['uk','Мія щойно закінчила звіт.']]],
                    ['Познач початкову точку проживання.',[['en','We have lived here ___ 2022.'],['uk','Ми почали жити тут у 2022 році й живемо досі.']]],
                ],
                'choices'=>[
                    ['Чи потрібно прибрати have?',[['en','I have read the email that you sent yesterday.'],['uk','Лист уже прочитано; учора — час надсилання.']],['Ні: yesterday стосується sent.','Так: yesterday забороняє perfect у всьому реченні.']],
                    ['Де зараз Нора за заданим контекстом?',[['en','Nora has been in Paris for two days.'],['uk','Нора прибула два дні тому й досі там.']],['Удома: been завжди означає повернення.','У Парижі: описано її перебування.']],
                ],
            ],
            'countable-vs-uncountable-nouns-common-mistakes'=>[
                'selects'=>[
                    ['Обери слово кількості для окремих людей.',[['en','There are not ___ people here.']]],
                    ['Узгодь дієслово з незлічуваною інформацією.',[['en','The information ___ useful.']]],
                ],
                'choices'=>[
                    ['Чи треба змінювати замовлення двох порцій?',[['en','I would like two coffees, please.'],['uk','Меню продає каву окремими чашками.']],['Залишити: названо дві порції.','Замінити: coffee ніколи не буває злічуваним.']],
                    ['Чи вистачить стільців п’ятьом?',[['en','We have a few chairs: three.'],['uk','Потрібно п’ять стільців.']],['Ні: три стільці — менше за п’ять.','Так: a few завжди означає достатню кількість.']],
                ],
            ],
            'prepositions-common-mistakes'=>[
                'selects'=>[
                    ['Чи потрібний прийменник перед next Monday?',[['en','I will call you ___ next Monday.']]],
                    ['Доповни прийменникову модель залежності.',[['en','She depends ___ her parents.'],['uk','Їй потрібна підтримка батьків.']]],
                ],
                'choices'=>[
                    ['Чи обов’язково виправляти in?',[['en','He jumped in the pool.'],['uk','Хлопчик стояв біля басейну та стрибнув у воду.']],['Ні: з jump тут можливі in та into.','Так: після будь-якого руху in неможливе.']],
                    ['Чи доведено, що ми вже прибули?',[['en','We are going to the airport.']],['Так: to доводить прибуття.','Ні: повідомлено лише поточну поїздку.']],
                ],
            ],
            'b1-b2-grammar-traps'=>[
                'selects'=>[
                    ['Доповни уявну умову про теперішнє.',[['en','If I ___ the answer, I would tell you.'],['uk','Мовець зараз не знає відповіді.']]],
                    ['Передай упевнений висновок про минулу дію.',[['en','She ___ home.'],['uk','Її пальта вже немає; вона звичайно бере його, коли йде додому.']]],
                ],
                'choices'=>[
                    ['Чи потрібна інверсія в цьому порядку?',[['en','I rarely see such clear feedback.'],['uk','Потрібне нейтральне твердження; rarely не виносимо на початок.']],['Ні: залишити звичайний порядок.','Так: rarely завжди вимагає інверсії.']],
                    ['Збережи обох учасників у перебудові з despite.',[['en','Although Danylo was absent, Olena finished the report.'],['uk','Данило був відсутній; звіт завершила саме Олена.']],['Despite being absent, Olena finished the report.','Despite the fact that Danylo was absent, Olena finished the report.']],
                ],
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>CommonMistakesPracticeSupplement::SOURCE,'sha256'=>CommonMistakesPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(CommonMistakesPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'common-mistakes-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>str_replace('{a1}','____',$row['condition'])],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Common Mistakes finite teaching copy absent.');
                        $instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Common Mistakes option labels differ.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>CommonMistakesPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)))];
                    if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'cmq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }
        $core=array_column($doc['targets'],null,'topic');$core[self::DELEGATE_TOPIC]=self::delegatedTarget();$doc['targets']=[];
        foreach(array_keys(CommonMistakesPracticeSource::SPECS)as$topic)$doc['targets'][]=$core[$topic];
        return $doc;
    }
    public static function validate(array $doc):void
    {
        $core=$doc;$core['targets']=array_values(array_filter($core['targets']??[],static fn($t)=>$t['topic']!==self::DELEGATE_TOPIC));parent::validate($core);
        if($doc!==self::compile())throw new RuntimeException('Common Mistakes finite copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Common Mistakes option label must stay short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Common Mistakes cards need two tasks.');}}
        if($items!==60||$cards!==30)throw new RuntimeException('Common Mistakes compact coverage differs.');
    }
    public static function presentation(object $block,array $data,bool $canonicalCaller):?array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk')return null;
        if(($block->page_id??null)!==43)return parent::presentation($block,$data,$canonicalCaller);
        try{$source=self::delegatedSource();$target=array_column(self::load()['targets'],null,'topic')[self::DELEGATE_TOPIC];
            foreach($target['binding']as$field=>$expected)if($field!=='body_sha256'&&($block->$field??null)!==$expected)return null;
            if(!is_string($block->body??null)||!hash_equals($target['binding']['body_sha256'],hash('sha256',$block->body))||$data!==$source['after_data'])return null;
            $target['groups_by_kind']=[];foreach($target['groups']as$i=>$group){$group['number']=$i+1;$target['groups_by_kind'][$group['kind']][]=$group;}return $target;
        }catch(\Throwable){return null;}
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='common-mistakes-practice-compact-v1')return $original;
        $display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];
        foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
