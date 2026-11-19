<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite concise teaching copy using the existing native and authored components only. */
final class MixedRevisionPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/mixed-revision-practice-compact.v1.json';
    public const SOURCE_SHA='948add488a42a2aa897bbf93de82c2aba291cb54a8a0559583901af202f5e86f';
    public const SUPPLEMENT=MixedRevisionPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    private static function copy():array
    {
        return [
            'a1-mixed-revision'=>[
                'selects'=>[
                    ['Запитай про кількість молока, не порцій.',[['en','How ____ milk is there?']]],
                    ['Доповни запитання про те, що є в Мії.',[['en','What ____ Mia got?']]],
                ],
                'choices'=>[
                    ['Обери пару: звичка й дія саме зараз.',[['uk','У будні Оля йде до школи пішки; зараз вона їде автобусом.'],['en','A. Olia walks to school on weekdays. She is taking the bus now.'],['en','B. Olia is walking to school now. She takes the bus on weekdays.']]],
                    ['Порівняй лише дві задані сумки.',[['uk','Синя сумка — 4 кг, червона — 2 кг.'],['en','A. The blue bag is the heaviest bag in the shop.'],['en','B. The blue bag is heavier than the red bag.']]],
                ],
            ],
            'a2-mixed-revision'=>[
                'selects'=>[
                    ['Запитай про досвід донині.',[['en','____ you ever visited Rome?']]],
                    ['Доповни звичайний заперечний хвостик.',[['en','Olia can swim, ____?']]],
                ],
                'choices'=>[
                    ['Друк необов’язковий, але не заборонений.',[['uk','Форму можна подати електронно або на папері.'],['en','A. You must not print the form.'],['en','B. You do not have to print the form.']]],
                    ['Обери уявну теперішню умову.',[['uk','В Івана зараз немає автомобіля; це не план купівлі.'],['en','A. If Ivan had a car, he would drive to work.'],['en','B. If Ivan buys a car, he will drive to work.']]],
                ],
            ],
            'b2-mixed-revision'=>[
                'selects'=>[
                    ['Доповни майбутній доконаний пасив.',[['uk','Завтра до п’ятої аудитор уже завершить перевірку файлів.'],['en','By five tomorrow, the files ____ by the auditor.']]],
                    ['Доповни пасив після модального слова.',[['en','The documents must ____ before publication.']]],
                ],
                'choices'=>[
                    ['Що встановлює форма без контексту почуттів?',[['en','If I had known, I would have called earlier.']],['Інше минуле та наслідок; жаль не доведений.','Жаль мовця обов’язково доведений.']],
                    ['Оціни нинішнє очікування ради.',[['en','The proposal is expected to be approved next week.']],['Вже затверджено; очікування гарантує результат.','Рішення ще очікують; затвердження не підтверджене.']],
                ],
            ],
        ];
    }
    private static function authoredCopy():array
    {
        return [
            'b1-mixed-revision'=>[
                '<p>Доповни результат сьогодні та дію раніше за вчорашнє прибуття.</p><p lang="en">I ___ three addresses today. I ___ the envelopes before Danylo arrived yesterday.</p>',
                '<p>Пакування триватиме з 14:00 до 16:00; до 17:00 його буде завершено. Обери процес і завершення.</p><p lang="en">At three, I ___ the books. By five, I ___ all the packing.</p>',
                '<p>Сьогодні передай вчорашні слова Міли непрямою мовою; почни з наведених початків.</p><p lang="en">“Are you ready?” → Mila asked me if…<br>“Do not open the parcel.” → She told me…</p>',
                '<p>А: Перебудуй у пасив, збережи волонтерів і необхідність, не завершений факт. Б: Учора майстер полагодив мій велосипед на замовлення; передай замовлену послугу.</p><p lang="en">A. The volunteers must label the boxes. → The boxes…<br>B. I had…</p>',
                '<p>А: Марта вже відома; додай через відносну частину її місце проживання та згоду допомогти. Б: Виправ поступову конструкцію, збережи її початок.</p><p lang="en">A. Marta lives nearby. Marta agreed to help.<br>B. Although it was late, but we continued.</p>',
                '<p>А: Стіл малий, мовець хоче більший. Б: Через пізній вихід ми пропустили автобус; уяви протилежне минуле. В: Місце Лени невідоме, читальна зала лише можлива.</p><p lang="en">A. I wish I…<br>B. If we ___ earlier, we ___ the bus.<br>C. Lena might…</p>',
            ],
            'c1-mixed-revision'=>[
                '<p>Технік не зберіг налаштування, тому зараз відновлення неможливе. Побудуй уявну минулу умову з теперішнім результатом.</p><p lang="en">If our technician… / would be able to…</p>',
                '<p>Почни з <span lang="en">Only after</span>; збережи обидва часи та головний пасив.</p><p lang="en">The boxes were dispatched only after the supervisor had approved the labels.</p>',
                '<p>Почни з <span lang="en">The envelope</span>; збережи <span lang="en">is believed</span> і повідомлення про ранішу подію.</p><p lang="en">It is believed that the envelope was opened before delivery.</p>',
                '<p>Із п’ятьох гідів двоє завершили курс; саме вони зараз проводять екскурсію. Склади одне речення з означальною частиною, без нових фактів.</p><p lang="en">The two guides… / who have completed the course</p>',
                '<p>Надсилання лише можливе, а скриньку ще не перевірено. Виправ категоричну нотатку через <span lang="en">may have</span>, зберігши обидва факти.</p><p lang="en">Leila sent the draft yesterday. We have not checked the mailbox.</p>',
                '<p>Учора перевірили лише дати; описи ще не перевірено. Остаточний файл ввічливо попроси до п’ятниці. Виправ нотатку трьома реченнями; останнє почни з <span lang="en">Could you</span>.</p><p lang="en">The catalogue was checked yesterday. Therefore, every description is certainly correct. Please to send the final file by Friday.</p>',
            ],
            'c2-mixed-revision'=>[
                '<p>Почни з <span lang="en">Had</span> замість <span lang="en">if</span>; збережи заперечення та <span lang="en">could have</span>.</p><p lang="en">If the guide had not brought a spare lamp, we could have been stranded underground.</p>',
                '<p>Почни з портретів; збережи теперішній статус повідомлення та попередність переміщення.</p><p lang="en">It is thought that the portraits were moved before the gallery closed.</p>',
                '<p>А: Марта надрукувала другий розклад без потреби; передай це через <span lang="en">needn’t have</span>. Б: За другим повідомленням обери, чи доведено друк.</p><p lang="en">B. Marta didn’t need to print a second timetable.</p>',
                '<p>Обери гарантований висновок і українською назви невідому кількісну інформацію.</p><p lang="en">Not all five samples were usable.</p>',
                '<p>У реєстрі є всі учасники, але два записи не мають телефонів. Оновити треба саме ці записи. Склади три речення з <span lang="en">however</span> і конкретним іменниковим відсиланням.</p>',
                '<p>Аніка успішно випробувала перший зразок у приміщенні в понеділок. Другий не перевіряли. Робота першого надворі — лише Анікине припущення; зовнішнього тесту не було. Виправ перебільшення трьома-чотирма реченнями.</p><p lang="en">Both prototypes have been proven reliable outdoors. Their successful validation guarantees good performance in every setting.</p>',
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>MixedRevisionPracticeSupplement::SOURCE,'sha256'=>MixedRevisionPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(MixedRevisionPracticeSupplement::load()['targets']as$s){$t=['topic'=>$s['topic'],'binding'=>$s['binding'],'after_data_sha256'=>$s['after_data_sha256'],'projection_family'=>'mixed-revision-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase']as$kind){$t['items'][$kind]=[];foreach($s['after_data'][$kind]??[]as$i=>$item){$labels=null;if($kind==='inputs'){if($i<2){$r=$s['input_updates'][$i];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$r['condition_uk']],['lang'=>'uk','text'=>$r['hint_uk']]];}else{$r=$s['added'][$i-2];$gap=$r['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$r['condition']):$r['condition']],['lang'=>'uk','text'=>$r['hint_uk']]];}}
                    else{$copy=self::copy()[$s['topic']][$kind][$i]??null;if($copy===null)throw new RuntimeException('Mixed Revision finite native copy missing.');$instruction=$copy[0];$stimuli=[];foreach($copy[1]as[$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];if(isset($copy[2])){$options=$item['options']??[];if(count($options)!==count($copy[2]))throw new RuntimeException('Mixed Revision native option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}}
                    $r=['index'=>$i,'source_item_sha256'=>MixedRevisionPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$r['option_labels']=$labels;$t['items'][$kind][]=$r;}
                if($s['mode']==='authored'||$kind==='rephrase')continue;$groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:[[array_keys($s['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']];foreach($groups as[$indices,$title,$intro])$t['groups'][]=['key'=>'mrq-native-'.$s['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }
            if($s['mode']==='authored'){$prompts=self::authoredCopy()[$s['topic']];$prompts[]='<p>Уведи лише пропущену частину в обох реченнях.</p>';$prompts[]='<p>Переклади обидва речення англійською; вводь вручну або натискай на групи слів.</p>';$t['authored_cases']=[];foreach($s['after_data']['cases']as$i=>$case)$t['authored_cases'][]=['index'=>$i,'source_case_sha256'=>MixedRevisionPracticeSupplement::digest($case),'source_prompt_sha256'=>hash('sha256',$s['after_data']['author_self_check']['prompts'][$i]),'prompt_html'=>$prompts[$i]];}
            $doc['targets'][]=$t;
        }return$doc;
    }
    public static function validate(array$d):void{parent::validate($d);if($d!==self::compile())throw new RuntimeException('Mixed Revision compact semantic copy differs.');$normal=$authored=0;foreach($d['targets']as$t){foreach($t['groups']as$g){$normal++;if(count($g['indices'])!==2)throw new RuntimeException('Mixed Revision native card needs2tasks.');}$authored+=count($t['authored_cases']??[]);}if($normal!==15||$authored!==24)throw new RuntimeException('Mixed Revision compact coverage differs.');}
    public static function nativeDisplay(?array$c,?array$o):?array{if(($c['projection_family']??null)!=='mixed-revision-practice-compact-v1')return$o;$d=$o??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices']as$kind)foreach($c['items'][$kind]as$r)if(isset($r['option_labels']))$d[$kind][$r['index']][$kind==='selects'?'options':'option_labels']=$r['option_labels'];return$d;}
    public static function authoredPresentation(object$b,array$d,array$a,bool$c):array
    {
        if(!$c||!MixedRevisionPracticeSupplement::isProjected($b,$d))return$a;
        try{foreach(self::load()['targets']as$t){if(($b->uuid??null)!==$t['binding']['uuid']||!isset($t['authored_cases']))continue;if(!hash_equals($t['after_data_sha256'],MixedRevisionPracticeSupplement::digest($d)))return$a;foreach($t['authored_cases']as$r){$i=$r['index'];if(!isset($a['presentation'][$i]))return$a;
                // Keep the already-rendered canonical heading and technical badge.
                // Only the finite exact-bound instruction body is replaced.
                $original=(string)$a['presentation'][$i]['prompt_html'];$heading=preg_match('~^\s*(<h4\b[^>]*>.*?</h4>)~su',$original,$match)===1?$match[0]:'';
                $a['presentation'][$i]['prompt_html']=$heading.$r['prompt_html'];}return$a;}}catch(Throwable){}return$a;
    }
}
