<?php

namespace App\Support;

use RuntimeException;

/** Finite semantic copy; canonical components, stored values and grading remain unchanged. */
final class ArticlesQuantifiersPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE='docs/content/articles-quantifiers-practice-compact.v1.json';
    public const SOURCE_SHA='068aa7e186eda50530079e6ffce851514489a8f114829409570807290b258995';
    public const SUPPLEMENT=ArticlesQuantifiersPracticeSupplement::class;
    private const FIELDS=['context','label','prompt','before','after','options','author_explanation'];

    private static function copy():array
    {
        return json_decode(<<<'JSON'
{
  "advanced-article-and-quantifier-nuance": {
    "selects": [
      [
        "Виправ злічуваність без нових фактів.",
        [
          [
            "uk",
            "Є три окремі відомості; кількість досліджень не задано."
          ],
          [
            "en",
            "We received three informations and a useful research about the old bridge."
          ]
        ],
        [
          "three pieces of information + some useful research",
          "three informations + a useful research",
          "three pieces of information + a useful study"
        ]
      ],
      [
        "Що повідомляє кількісна конструкція?",
        [
          [
            "en",
            "We have a few spare labels, but not enough for all the boxes."
          ],
          [
            "uk",
            "Порівняй з малою кількістю без уточнення точного числа."
          ]
        ],
        [
          "Кілька є, достатність не гарантована; точної кількості немає.",
          "Є суперечність: кількох завжди достатньо; мало означає жодної.",
          "Суперечності немає: кілька означає рівно п’ять; мало — нуль."
        ]
      ]
    ],
    "choices": [
      [
        "Обери артиклі за контекстом.",
        [
          [
            "uk",
            "Журнал єдиний і спільно відомий; олівець новий. Інформацію не конкретизовано."
          ],
          [
            "en",
            "Please put ____ pencil beside ____ logbook. We need ____ information about yesterday’s delivery."
          ]
        ],
        [
          "a / the / без артикля",
          "the / a / an"
        ]
      ],
      [
        "Обери узгодження у формальній нотатці.",
        [
          [
            "en",
            "A number of labels ____ missing. The number of missing labels ____ not known."
          ],
          [
            "uk",
            "Точного числа етикеток не наведено."
          ]
        ],
        [
          "are / is — кілька етикеток; число невідоме.",
          "is / are — бракує рівно п’яти етикеток."
        ]
      ]
    ]
  },
  "precision-with-articles-and-determiners": {
    "selects": [
      [
        "Визнач відсилання лише в межах тексту.",
        [
          [
            "en",
            "A volunteer brought a tray into the studio. The tray held six cups. Two cups were chipped; the other four were intact."
          ]
        ],
        [
          "Принесена таця; решта чотири з шести чашок. Інші чашки студії не пораховані.",
          "Нова таця; усі інші чашки студії. Поза тацею чашок немає.",
          "Принесена таця; чотири нові чашки поза згаданими шістьма."
        ]
      ],
      [
        "Обери артиклі зі збереженням відсилання.",
        [
          [
            "uk",
            "Шафу вводимо вперше; ключ будь-який придатний, не вже відомий. Останнє речення — про меблі загалом."
          ],
          [
            "en",
            "We found ____ cabinet in an empty room. ____ cabinet was locked, so we needed ____ key. ____ furniture needs care."
          ]
        ],
        [
          "a / The / a / без артикля",
          "the / A / the / A",
          "a / The / the / The"
        ]
      ]
    ],
    "choices": [
      [
        "Зістав пропозицію, відкрите питання та вільний вибір.",
        [
          [
            "en",
            "Would you like ____ biscuits? Are there ____ lockers available? You may choose ____ cup from these five."
          ],
          [
            "uk",
            "Пропонують печиво; наявність шафок невідома; дозволено будь-яку одну чашку."
          ]
        ],
        [
          "some / any / any",
          "any / some / some"
        ]
      ],
      [
        "Узгодь форми в заданій формальній моделі.",
        [
          [
            "en",
            "Each of the three drafts ____ a number. Every ____ of the three drafts has a number. Both layouts ____ ready. Either layout ____ suitable. Neither of them ____ signed."
          ],
          [
            "uk",
            "Три чернетки мають номер; обидва макети готові, будь-який підходить, жоден не підписаний. Використай однину після «будь-який» і «жоден»."
          ]
        ],
        [
          "has / one / are / is / is",
          "have / of / is / are / are"
        ]
      ]
    ]
  }
}
JSON, true, flags: JSON_THROW_ON_ERROR);
    }

    public static function compile():array
    {
        $doc=['schema_version'=>1,'source'=>['path'=>ArticlesQuantifiersPracticeSupplement::SOURCE,'sha256'=>ArticlesQuantifiersPracticeSupplement::SOURCE_SHA],'targets'=>[]];
        foreach(ArticlesQuantifiersPracticeSupplement::load()['targets'] as $source){
            $target=['topic'=>$source['topic'],'binding'=>$source['binding'],'after_data_sha256'=>$source['after_data_sha256'],'projection_family'=>'articles-quantifiers-practice-compact-v1','items'=>[],'groups'=>[]];
            foreach(['selects','choices','inputs','rephrase'] as $kind){$target['items'][$kind]=[];
                foreach($source['after_data'][$kind]??[] as $index=>$item){
                    $labels=null;
                    if($kind==='inputs'){
                        if($index<2){$row=$source['input_updates'][$index];$instruction='Склади речення з перемішаних груп слів.';$stimuli=[['lang'=>'uk','text'=>$row['condition_uk']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                        else{$row=$source['added'][$index-2];$gap=$row['kind']==='gap';$instruction=$gap?'Уведи всю відсутню частину.':'Переклади речення англійською.';$stimuli=[['lang'=>$gap?'en':'uk','text'=>$gap?str_replace('{a1}','____',$row['condition']):$row['condition']],['lang'=>'uk','text'=>$row['hint_uk']]];}
                    }else{
                        $copy=self::copy()[$source['topic']][$kind][$index]??null;if($copy===null)throw new RuntimeException('Articles and quantifiers finite native copy absent.');$instruction=$copy[0];$stimuli=[];foreach($copy[1] as [$lang,$text])$stimuli[]=['lang'=>$lang,'text'=>$text];
                        if(isset($copy[2])){$options=$item['options']??($kind==='selects'?($source['after_data']['options']??[]):($source['after_data']['choice_options']??['a','b']));if(count($options)!==count($copy[2]))throw new RuntimeException('Articles and quantifiers option cardinality differs.');$labels=$kind==='selects'?$copy[2]:array_combine($options,$copy[2]);}
                    }
                    $row=['index'=>$index,'source_item_sha256'=>ArticlesQuantifiersPracticeSupplement::digest($item),'source_index'=>$item['source_index']??null,'instruction_uk'=>$instruction,'stimuli'=>$stimuli,'deferred_source_fields'=>array_values(array_filter(self::FIELDS,static fn($f)=>array_key_exists($f,$item)))];if($labels!==null)$row['option_labels']=$labels;$target['items'][$kind][]=$row;
                }
                $groups=$kind==='inputs'?[[[0,1],'Побудуй речення','Введи речення вручну або натискай на перемішані групи слів.'],[[2,3],'Заповни пропуски','Уведи лише пропущену частину; наведені слова не повторюй.'],[[4,5],'Переклади речення','Відповідай вручну або складай речення з груп слів.']]:($kind==='rephrase'?[]:[[array_keys($source['after_data'][$kind]),$kind==='choices'?'Обери правильну відповідь':'Обери правильну форму','Обери одну відповідь для кожної умови.']]);
                foreach($groups as [$indices,$title,$intro])$target['groups'][]=['key'=>'aqv-native-'.$source['topic'].'-'.$kind.'-'.$indices[0],'kind'=>$kind,'indices'=>$indices,'title'=>$title,'instruction'=>$intro];
            }$doc['targets'][]=$target;
        }return $doc;
    }
    public static function validate(array $doc):void
    {
        parent::validate($doc);if($doc!==self::compile())throw new RuntimeException('Articles and quantifiers finite native copy differs.');$items=$cards=0;
        foreach($doc['targets'] as $target){foreach($target['items'] as $rows)foreach($rows as $row){$items++;foreach($row['option_labels']??[] as $label)if(!is_string($label)||$label===''||mb_strlen($label)>180||preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u',$label))throw new RuntimeException('Articles and quantifiers option label must remain short and plain.');}foreach($target['groups'] as $group){$cards++;if(count($group['indices'])!==2)throw new RuntimeException('Articles and quantifiers cards need two tasks.');}}
        if($items!==20||$cards!==10)throw new RuntimeException('Articles and quantifiers compact coverage differs.');
    }
    public static function nativeDisplay(?array $compact,?array $original):?array
    {
        if(($compact['projection_family']??null)!=='articles-quantifiers-practice-compact-v1')return $original;$display=$original??['selects'=>[],'choices'=>[],'answer_groups'=>[]];foreach(['selects','choices'] as $kind)foreach($compact['items'][$kind] as $row)if(isset($row['option_labels']))$display[$kind][$row['index']][$kind==='selects'?'options':'option_labels']=$row['option_labels'];return $display;
    }
}


