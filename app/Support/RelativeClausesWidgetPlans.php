<?php
namespace App\Support;

/** Finite guidance, grammatical casing and complete alternatives for the five retained C1 tasks. */
final class RelativeClausesWidgetPlans
{
    public const SNAPSHOTS_SHA='4fc2efd78b5f0e63f43ac2ade00bf68353e38a08ade50aa70b7da9bfe87a332c';
    public static function ids():array{return['complex-relative-clauses'=>[18020,18029,18026,18022,18004]];}
    public static function hints():array{return[
        18020=>'Почни з «вони»; відносне слово має передати наслідок скорочення витрат для фінансового результату.',
        18029=>'Почни з конкретного періоду; відносна частина описує тодішню нечіткість правил, що спричинила плутанину.',
        18026=>'Збережи письменника як власника творів; ми вивчаємо їх зараз, а він жив у вигнанні раніше.',
        18022=>'Почни з указування на конкретний рік; компанія вперше отримала прибуток саме тоді.',
        18004=>'Назви конкретну компанію: вона працює саме для неї; збережи теперішній час і Берлін як місце базування.',
    ];}
    public static function conditions():array{return[
        18020=>'Вони скоротили витрати, що покращило фінансовий результат.',
        18029=>'Період, коли правила були нечіткими, спричинив плутанину.',
        18026=>'Письменник, твори якого ми зараз вивчаємо, жив у вигнанні.',
        18022=>'Це рік, коли компанія вперше отримала прибуток.',
        18004=>'Компанія, для якої вона працює, базується в Берліні.',
    ];}
    public static function instructions():array{$out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи відносний зв’язок і зміст.';return$out;}
    public static function correctedTokens(int$id,array$tokens):array
    {
        $slot=[18029=>2,18022=>4][$id]??null;if($slot!==null){if(($tokens[$slot]??null)!=='When')throw new \RuntimeException('Exact relative-adverb case source differs.');$tokens[$slot]='when';}return$tokens;
    }
    public static function correctedOptions(int$id,array$options):array{return in_array($id,[18029,18022],true)?array_map(static fn($v)=>$v==='When'?'when':$v,$options):$options;}
    public static function tokenLabels(int$id,array$tokens,array$accepted):array{return RelativeClausesPracticeSupplement::tokenLabels($tokens,$accepted);}
    public static function aliases():array
    {
        $out=[18020=>[],18029=>['The period during which the rules were unclear caused confusion','The period in which the rules were unclear caused confusion'],18026=>['The author whose works we are studying lived in exile','The writer whose writings we are studying lived in exile','The author whose writings we are studying lived in exile'],18022=>[],18004=>[]];
        foreach(['reduced','cut','lowered']as$verb)foreach(['costs','expenses']as$costs)foreach(['result','outcome','performance']as$noun){$a='They '.$verb.' '.$costs.' which improved the financial '.$noun;if($a!=='They reduced costs which improved the financial result')$out[18020][]=$a;}
        foreach(['when','in which','that','']as$link)foreach(['made','earned','turned']as$verb){$a='This is the year'.($link!==''?' '.$link:'').' the company first '.$verb.' a profit';if($a!=='This is the year when the company first made a profit')$out[18022][]=$a;}
        foreach(['company','firm']as$noun)foreach(['for which she works','which she works for','that she works for','she works for']as$relative){$a='The '.$noun.' '.$relative.' is based in Berlin';if($a!=='The company for which she works is based in Berlin')$out[18004][]=$a;}
        return$out;
    }
}
