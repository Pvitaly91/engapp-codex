<?php

namespace App\Support;

use RuntimeException;

/** Finite short teaching copy in the unchanged canonical native controls. */
final class ReportedSpeechPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/reported-speech-practice-compact.v1.json';
    public const SOURCE_SHA='832c1ace4e4b16423d085265c65f89ab28de2a0d9aebeec48fb66006ac61ffa1';
    public const SUPPLEMENT=ReportedSpeechPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];
    private static function copy():array
    {
        return [
            'reported-statements'=>[
                'selects'=>[
                    ['Назви адресатку після дієслова повідомлення.', [['en','Marta told ___ that the bus was late.'],['uk','Марта сказала Ірині, що автобус запізнюється.']]],
                    ['Передай процес із минулої точки повідомлення.', [['en','Oleh said that he ___ on the report.'],['uk','Олег сказав: «Я працюю над звітом». Переказуємо пізніше, he — Олег; потрібен Past Continuous.']]],
                ],
                'choices'=>[
                    ['Обери точний зміст, зберігши заперечення.', [['en','Taras said that he would not join us.'],['uk','He — Тарас.']]],
                    ['Назви людину, якій був потрібен ключ.', [['en','I said that I needed the key.'],['uk','Марія сама переповідає власні попередні слова.']]],
                ],
            ],
            'reported-questions'=>[
                'selects'=>[
                    ['Збережи прямий порядок слів усередині питання.', [['en','Iryna asked what time ___.'],['uk','Ірина запитала, о котрій відчиняється музей.']]],
                    ['Передай дію до минулого питання.', [['en','Oleh asked who ___ the folder.'],['uk','Олег запитав, хто забрав папку. Who — підмет; потрібен Past Perfect.']]],
                ],
                'choices'=>[
                    ['Обери лише те, що повідомлено напевно.', [['en','Olena asked Nazar whether he had used the spare key.'],['uk','He — Назар; відповідь на питання не наведена.']]],
                    ['Розрізни того, хто телефонував, і адресата дзвінка.', [['en','Iryna asked Oleh who he had called.'],['uk','He — Олег.']]],
                ],
            ],
            'reported-commands-and-requests'=>[
                'selects'=>[
                    ['Доповни заперечну вказівку.', [['en','The guard ordered us ___ enter.'],['uk','Охоронець наказав нам не входити.']]],
                    ['Уведи названого адресата вказівки.', [['en','The director told ___ to wait.'],['uk','Директорка сказала Андрієві зачекати.']]],
                ],
                'choices'=>[
                    ['Назви того, хто просив дозволу вийти.', [['en','Iryna asked to leave early.'],['uk','Ірина звернулася до керівника.']]],
                    ['Не приписуй реченню виконання поради.', [['en','The nurse advised Pavlo to take a break.'],['uk','Про подальші дії Павла не повідомлено.']]],
                ],
            ],
            'backshift-of-tenses'=>[
                'selects'=>[
                    ['Передай процес у пасиві з минулої точки.', [['en','Marta said that the windows ___ being washed.'],['uk','Пряма мова: The windows are being washed. Потрібен Past Continuous Passive.']]],
                    ['Збережи групу передування минулому.', [['en','Petro said that he ___ already locked the gate.'],['uk','Петро сказав: I had already locked the gate. He — Петро; збережи Past Perfect.']]],
                ],
                'choices'=>[
                    ['Обери висновок без домислу про нинішній графік.', [['en','The receptionist said that the office opened at eight.'],['uk','Пізніше переказують її слова про звичайний графік офісу.']]],
                    ['Обери зміст вказівки, а не її результат.', [['en','The guard said that visitors must not touch the controls.'],['uk','Охоронець звертався до відвідувачів; controls — той самий пульт.']]],
                ],
            ],
            'time-and-place-changes'=>[
                'selects'=>[
                    ['Назви день відносно нинішнього оповідача.', [['en','Nazar said that the parcel would arrive ___.'],['uk','У понеділок 1 червня Назар сказав «завтра». Переказують у вівторок 2 червня: потрібно саме «сьогодні».']]],
                    ['Назви ключ, який оповідач зараз показує.', [['en','Marta said that ___ key was hers.'],['uk','Марта раніше назвала ключ своїм. Тепер інша людина тримає й показує той самий ключ: потрібно «цей». Hers — Мартин.']]],
                ],
                'choices'=>[
                    ['Збережи дату події відносно початкових слів.', [['en','Iryna said that she had posted the letter two days earlier.'],['uk','Ірина сказала це в понеділок 1 червня 2026 року. Переказують у п’ятницю 5 червня; earlier відраховуємо від понеділка.']]],
                    ['Визнач дату повернення за словами, не за результатом.', [['en','Danylo said that he would return in two days.'],['uk','Данило говорить у понеділок 1 червня 2026 року; переказують того самого дня. In two days — від цього понеділка.']]],
                ],
            ],
            'say-tell-ask'=>[
                'selects'=>[
                    ['Збережи дієслово повідомлення й адресата.', [['en','Lina said ___ that the workshop was full.'],['uk','Ліна сказала нам, що місць немає; us — попередні слухачі, зокрема нинішній оповідач.']]],
                    ['Передай прохання про річ.', [['en','Maksym asked Olena ___ a spare pen.'],['uk','Максим попросив в Олени запасну ручку, не запитував про її колір.']]],
                ],
                'choices'=>[
                    ['Обери тип мовленнєвої дії.', [['en','Daryna told me where the spare key was.'],['uk','Me — Ігор, який зараз переповідає Даринині слова. Місце ключа не вгадуємо.']]],
                    ['Назви того, хто просив сісти.', [['en','Roman asked to sit near the window.'],['uk','Роман звернувся до провідниці Ірини. Немає відомостей, чи місце надали.']]],
                ],
            ],
            'advanced-reported-speech'=>[
                'selects'=>[
                    ['Доповни пропозицію без підрядного речення.', [['en','Marta suggested ___ the early train.'],['uk','Марта запропонувала двом колегам спільно взяти ранній потяг; виконавці — всі троє.']]],
                    ['Збережи адресата й заперечну пораду.', [['en','The doctor advised Ihor ___ run.'],['uk','Лікарка порадила Ігореві не бігти після травми; потрібна модель object + not to.']]],
                ],
                'choices'=>[
                    ['Збережи рішення й виконавців без домислу про виконання.', [['en','After Maksym agreed to collect the samples, Iryna reminded him to bring a cool bag.'],['uk','Him — Максим; сумку має взяти він.']]],
                    ['Розрізни пропозицію, наказ і відмову.', [['en','Vira suggested taking a short break.'],['uk','Віра зверталася до двох колег із пропозицією спільної перерви.']]],
                ],
            ],
        ];
    }
    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>ReportedSpeechPracticeSupplement::SOURCE,'sha256'=>ReportedSpeechPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(ReportedSpeechPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'reported-speech-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){
                $target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>str_replace('{a1}','____',$row['condition'])],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;
                        if($copy===null)throw new RuntimeException('Reported finite teaching copy absent.');
                        $instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                    }
                    $target['items'][$kind][]=['index'=>$index,'source_item_sha256'=>ReportedSpeechPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($field)=>array_key_exists($field,$item)))];
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'rs-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }
            $doc['targets'][]=$target;
        }
        return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Reported finite teaching-copy projection differs.');
        $items=$cards=0;foreach($doc['targets'] as $target){foreach($target['items'] as $rows)$items+=count($rows);foreach($target['groups'] as $group){if(count($group['indices'])!==2)throw new RuntimeException('Reported exercise needs two tasks.');$cards++;}}
        if($items!==70||$cards!==35)throw new RuntimeException('Reported compact coverage differs.');
    }
}
