<?php

namespace App\Support;

use RuntimeException;

/** Exact teaching-copy projection using only the established native practice controls. */
final class ConditionalsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/conditionals-practice-compact.v1.json';
    public const SOURCE_SHA='2fdf043a7e914c3ff1ef8b9e7a070bcb4a91559315b8468d882fd8dff28c995b';
    public const SUPPLEMENT=ConditionalsPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];

    private static function plain(string $html): string
    {
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags(str_replace(['<br>','<br/>','<br />'],"\n",$html)),ENT_QUOTES|ENT_HTML5,'UTF-8')));
    }

    /** Source-specific annotations retain every fact needed to distinguish the answers. */
    private static function advancedCopy(): array
    {
        return [
            'conditionals-with-unless-provided-as-long-as'=>[
                'selects'=>[
                    ['Почни з винятку й збережи зміст умови.', [['en','If you don’t confirm the booking, we’ll release the room.']]],
                    ['Обери речення для плану з винятком.', [['uk','Заняття проводимо надворі; виняток — дощ.']]],
                ],
                'choices'=>[
                    ['Обери дозвіл із заданим сполучником.', [['uk','Користуватися студією можна за умови повернення ключа до восьмої. Використай provided that.']]],
                    ['Розрізни умову дозволу й тривалість.', [['en','A) You may keep the atlas as long as you renew the loan today. B) The lantern shone as long as the battery lasted.']]],
                ],
            ],
            'advanced-conditionals'=>[
                'selects'=>[
                    ['Обери часовий зв’язок за фактами.', [['uk','Учора ми не зарядили батарею; зараз пристрій не працює. Уяви протилежний стан зараз.']]],
                    ['Збережи теперішній результат минулого вибору.', [['uk','Ірина відхилила пропозицію торік. Якби прийняла її, то й зараз працювала б у музеї.']]],
                ],
                'choices'=>[
                    ['Обери речення про сталу рису й минулий результат.', [['uk','Данило завжди боїться висоти. Саме через це минулого тижня він не піднявся на вежу.']]],
                    ['Збережи можливість, а не певність результату.', [['en','If the curator had seen the letter, she might have postponed the exhibition.']]],
                ],
            ],
            'conditional-alternatives-and-nuance'=>[
                'selects'=>[
                    ['Віднови умову, не втрачаючи дію й термін.', [['en','Please send the signed form today; otherwise, we will postpone the visit.']]],
                    ['Перефразуй минулу нездійснену умову.', [['uk','Генератор спрацював; архів не втратив файлів.'],['en','But for the backup generator, the archive would have lost its files.']]],
                ],
                'choices'=>[
                    ['Розрізни робоче припущення й установлену вимогу.', [['en','A) Assuming the figures are accurate, we could publish a summary. B) You may publish the summary on condition that the editor approves it.']]],
                    ['Виправ порядок заперечення в умовній інверсії.', [['en','Had not Lena checked the address, the parcel might have gone to the wrong office.']]],
                ],
            ],
        ];
    }

    public static function compile(): array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>ConditionalsPracticeSupplement::SOURCE,'sha256'=>ConditionalsPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(ConditionalsPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'conditionals-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $instruction=$kind==='choices'?'Обери правильне речення або пояснення.':'Обери правильну форму.';$stimuli=[];
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>str_replace('{a1}','____',$row['condition'])],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::advancedCopy()[$source['topic']][$kind][$index]??null;
                        if($copy!==null){$instruction=$copy[0];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];}
                        else{foreach(['context','label'] as $field){$text=self::plain($item[$field]??'');if($text!=='')$stimuli[]=['lang'=>'uk','text'=>$text];}}
                        $prompt=self::plain($item['prompt']??'');if($prompt!=='')$stimuli[]=['lang'=>'uk','text'=>$prompt];
                    }
                    $deferred=array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)));
                    $target['items'][$kind][]=['index'=>$index,'source_item_sha256'=>ConditionalsPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>$deferred];
                }
                if($kind==='inputs'){
                    foreach([[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складаючи речення з груп слів.']] as [$indices,$title,$intro])$target['groups'][]=['key'=>'cq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
                }else{
                    $indices=array_keys($source['after_data'][$kind]??[]);
                    while($indices!==[]){$take=count($indices)===3?3:2;$part=array_splice($indices,0,$take);$target['groups'][]=['key'=>'cq-native-'.$source['topic'].'-'.$kind.'-'.$part[0],'kind'=>$kind,'indices'=>$part,'title'=>$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','instruction'=>'Обери одну відповідь для кожної умови.'];}
                }
            }
            $doc['targets'][]=$target;
        }
        return $doc;
    }

    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Conditional finite teaching-copy projection differs.');
        $items=$cards=0;foreach($doc['targets'] as $target){foreach($target['items'] as $rows)$items+=count($rows);foreach($target['groups'] as $group){if(count($group['indices'])<2)throw new RuntimeException('Conditional exercise needs several tasks.');$cards++;}}
        if($items!==150||$cards!==69)throw new RuntimeException('Conditional compact coverage differs.');
    }
}
