<?php

namespace App\Support;

use RuntimeException;

/** Finite semantic copy; canonical components, stored values and grading remain unchanged. */
final class AcademicEnglishPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/academic-english-practice-compact.v1.json';
    public const SOURCE_SHA='a79a80d9cca6318eef09ee1584f63460e1a9a1a9ca1cc571d3822776cbd143ee';
    public const SUPPLEMENT=AcademicEnglishPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];

    private static function copy():array
    {
        return json_decode(<<<'JSON'
{
  "hedging-and-cautious-language-basics": {
    "selects": [
      [
        "Обери два правильно побудовані припущення.",
        [
          [
            "uk",
            "Індикатор блимає; лоток ще не відкривали."
          ],
          [
            "en",
            "the printer / may / need paper; the printer / seem / need paper"
          ]
        ]
      ],
      [
        "Виправ форму після модального дієслова.",
        [
          [
            "en",
            "The handle might needs repair."
          ],
          [
            "uk",
            "Можливо, ручку потрібно полагодити."
          ]
        ]
      ]
    ],
    "choices": [
      [
        "Відокрем спостереження від можливого пояснення.",
        [
          [
            "uk",
            "Килим мокрий біля відчинених дверей; як він намок, невідомо."
          ],
          [
            "en",
            "The carpet is wet. Rain may be coming through the doorway."
          ]
        ],
        [
          "Перше — спостереження, друге — припущення.",
          "Дощ доведено причиною мокрого килима."
        ]
      ],
      [
        "Зістав теперішню можливість і типову роботу.",
        [
          [
            "en",
            "The lift could be out of order. The lift generally works well."
          ]
        ],
        [
          "Можлива несправність зараз; зазвичай працює добре, але є винятки.",
          "Зазвичай працює добре, отже зараз гарантовано справний."
        ]
      ]
    ]
  },
  "hedging-and-cautious-language": {
    "selects": [
      [
        "Обери висновок у межах цих даних.",
        [
          [
            "uk",
            "Семеро з восьми шукали швидше вдруге. Усі використали нові підписи другим проходженням."
          ]
        ],
        [
          "7 із 8 швидше вдруге; вплив практики не виключено.",
          "Нові підписи прискорюють пошук для всіх.",
          "7 із 8 швидше вдруге; причиною доведено нові підписи."
        ]
      ],
      [
        "Порівняй силу двох прогнозів.",
        [
          [
            "uk",
            "Нових користувачів ще не перевіряли."
          ],
          [
            "en",
            "The labels may not help new users. → The labels do not help new users."
          ]
        ],
        [
          "Можливість стала категоричним твердженням; початковий прогноз не був забороною.",
          "Сила однакова; початкове речення було забороною.",
          "Початкове речення категорично заперечувало будь-яку користь."
        ]
      ]
    ],
    "choices": [
      [
        "Де потрібна обережність щодо причини?",
        [
          [
            "en",
            "One volunteer took the same amount of time in both searches. The new labels may have helped the other volunteers."
          ]
        ],
        [
          "Однаковий час — відомий результат; допомога підписів — можливе пояснення.",
          "Навіть відомий однаковий час треба подати як непевний."
        ]
      ],
      [
        "Чи заперечено будь-яке покращення?",
        [
          [
            "en",
            "A shorter completion time is not necessarily evidence of a clearer interface."
          ]
        ],
        [
          "Не обов’язково: зрозуміліший інтерфейс лишається можливим поясненням.",
          "Інтерфейс точно не став зрозумілішим."
        ]
      ]
    ]
  },
  "stance-register-and-evaluation": {
    "selects": [
      [
        "Оціни достатність даних, не їхню правдивість.",
        [
          [
            "uk",
            "Дві версії мають однаковий час у двох пробах; швидшу визначити не вдалося."
          ]
        ]
      ],
      [
        "Обери пряму констатацію та обережний наслідок.",
        [
          [
            "uk",
            "Дати перегляду в списку немає. Вибір користувачів не спостерігали."
          ]
        ],
        [
          "Дати немає; визначити найновішу версію може бути важче, але вибір не перевіряли.",
          "Можливо, дати начебто немає; кожен гарантовано вибере хибну версію.",
          "Дати немає; кожен гарантовано вибере хибну версію."
        ]
      ]
    ],
    "choices": [
      [
        "Зістав функції та критерій оцінки.",
        [
          [
            "en",
            "The guide has no index."
          ],
          [
            "en",
            "The guide is difficult to use for quick lookup because it has no index."
          ],
          [
            "en",
            "The next edition should include an index."
          ]
        ],
        [
          "Опис → оцінка → рекомендація; критерій — швидкий пошук.",
          "Опис → рекомендація → оцінка; посібник непридатний для будь-якого читання."
        ]
      ],
      [
        "Збережи позицію джерела та неперевіреність.",
        [
          [
            "en",
            "The shorter form may reduce copying errors, but this has not been tested."
          ]
        ],
        [
          "Source B suggests: можливе зменшення помилок; ще не перевірено.",
          "Source B states: можливе зменшення помилок; ще не перевірено.",
          "Source B writes: можливе зменшення помилок; ще не перевірено.",
          "Source B proves: зменшення помилок доведено."
        ]
      ]
    ]
  },
  "argumentation-and-academic-tone": {
    "selects": [
      [
        "Обери тезу без вигаданого результату.",
        [
          [
            "uk",
            "Новий макет показує два читабельні розклади поруч на настільному екрані; старий — один. Швидкість не вимірювали."
          ]
        ],
        [
          "На цьому настільному екрані можна зіставити два розклади без перемикання вікон.",
          "Перевірений настільний макет підтримує порівняння розкладів поруч.",
          "Новий дизайн покращує всі аспекти бронювання для всіх.",
          "Усі бронюють швидше на кожному пристрої."
        ]
      ],
      [
        "Обери заперечення й відповідне звуження рекомендації.",
        [
          [
            "uk",
            "Два розклади читабельні на настільному екрані; телефон не перевіряли. Пропонують макет для всіх пристроїв."
          ]
        ],
        [
          "На телефоні читати може бути важче; поки рекомендуємо лише настільне порівняння.",
          "Вузький екран — доречне застереження; без перевірки обмежуємо висновок настільним екраном.",
          "Телефон уже успішно перевірено; рекомендуємо всі пристрої.",
          "Настільна перевірка доводить читабельність на кожному телефоні."
        ]
      ]
    ],
    "choices": [
      [
        "Визнач тезу, опору, міркування та межу.",
        [
          [
            "uk",
            "1. Макет має перевагу для порівняння на настільному екрані. 2. Обидва розклади видимі й читабельні. 3. Час можна зіставити без перемикання. 4. Це одночасний доступ, не доведена швидкість."
          ]
        ],
        [
          "Теза — 1, опора — 2, міркування — 3; 4 обмежує висновок одночасним доступом.",
          "Речення 4 доводить швидше бронювання: обидва розклади видно."
        ]
      ],
      [
        "Знайди неперевірену умову.",
        [
          [
            "en",
            "The side-by-side layout will be equally useful on every phone because both timetables fit on the screen."
          ]
        ],
        [
          "Потрібно перевірити читабельність на конкретному телефоні; усі телефони не досліджено.",
          "Якщо розклади вміщаються, читабельність і користь на всіх телефонах уже доведено."
        ]
      ]
    ]
  }
}
JSON, true, flags: JSON_THROW_ON_ERROR);
    }

    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>AcademicEnglishPracticeSupplement::SOURCE,'sha256'=>AcademicEnglishPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(AcademicEnglishPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'academic-english-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){$target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$row['condition']):$row['condition']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Academic English finite native copy absent.');$instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Academic English option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>AcademicEnglishPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'aeq-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Academic English finite native copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Academic English option label must remain short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Academic English cards need two tasks.');}}
        if($items!==40||$cards!==20)throw new RuntimeException('Academic English compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='academic-english-practice-compact-v1')return $original;$display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}
