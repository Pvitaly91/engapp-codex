<?php
namespace App\Support;

/** Complete finite lexical alternatives for the five retained C2 questions, with concise representative cues. */
final class VocabCollocationsWidgetPlans
{
    public const SNAPSHOTS_SHA='3b5e8efed87cdd4a3a2d5174b6ac1d4e20bf544b48f2c3f4da95b174590b1a4a';
    public static function ids():array{return['advanced-collocation-and-lexical-choice'=>[18769,18799,18777,18766,18768]];}
    public static function hints():array{return[
        18769=>'Почни з «вона»; передай виявлення тонкої, а не просто малої відмінності між двома підходами.',
        18799=>'Почни з автора; у теперішньому часі передай тонке зауваження, а не різку критику чи просте заперечення.',
        18777=>'Почни з «ми»; збережи теперішнє спостереження та різкість зростання попиту.',
        18766=>'Почни з «він»; передай рішуче заперечення в минулому, не додавай доведеного спростування.',
        18768=>'Почни з нашої потреби; ретельність стосується оцінювання ризиків, а не просто згадки про них.',
    ];}
    public static function conditions():array{return[
        18769=>'Вона виявила тонку відмінність між двома підходами.',
        18799=>'Автор робить тонке зауваження.',
        18777=>'Ми спостерігаємо різке зростання попиту.',
        18766=>'Він рішуче заперечив це твердження.',
        18768=>'Нам потрібно ретельно оцінити ризики.',
    ];}
    public static function instructions():array{$out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи учасників, час і точність оцінки.';return$out;}
    public static function corrections():array{return[18799=>[2,'Makes','makes'],18777=>[7,'Demand','demand']];}
    public static function correctedTokens(int$id,array$tokens):array
    {
        if(isset(self::corrections()[$id])){[$slot,$raw,$correct]=self::corrections()[$id];if(($tokens[$slot]??null)!==$raw)throw new \RuntimeException('Exact vocabulary token source differs.');$tokens[$slot]=$correct;}return$tokens;
    }
    public static function correctedOptions(int$id,array$options):array
    {
        if(!isset(self::corrections()[$id]))return$options;[, $raw,$correct]=self::corrections()[$id];return array_map(static fn($v)=>$v===$raw?$correct:$v,$options);
    }
    public static function tokenLabels(int$id,array$tokens,array$accepted):array
    {
        $finite=[
            18769=>['identified'=>'identified (found / discerned)','subtle'=>'subtle (fine)','distinction'=>'distinction (difference)'],
            18799=>['author'=>'author (writer)','point'=>'point (remark / observation)'],
            18777=>['seeing'=>'seeing (observing)','sharp'=>'sharp (steep)','increase'=>'increase (rise)'],
            18766=>['strongly'=>'strongly (firmly / vigorously)','disputed'=>'disputed (challenged / denied)'],
            18768=>['assess'=>'assess (evaluate)','carefully'=>'carefully (thoroughly)'],
        ];
        foreach(array_keys($finite[$id]??[])as$token)if(!in_array($token,$tokens,true))throw new \RuntimeException('Vocabulary representative cue needs its exact token.');
        return$finite[$id]??[];
    }
    public static function aliases():array
    {
        $out=[18769=>[],18799=>[],18777=>[],18766=>[],18768=>[]];
        foreach(['identified','found','noticed','discerned','recognised','recognized']as$verb)foreach(['subtle','fine']as$adjective)foreach(['distinction','difference']as$noun)foreach(['the two','two']as$number)$out[18769][]='She '.$verb.' a '.$adjective.' '.$noun.' between '.$number.' approaches';
        foreach(['author','writer']as$person)foreach(['point','remark','comment','observation']as$noun)$out[18799][]='The '.$person.' makes a subtle '.$noun;
        foreach(['are seeing','see','observe','are observing']as$verb)foreach(['sharp','steep']as$adjective)foreach(['increase','rise']as$noun)$out[18777][]='We '.$verb.' a '.$adjective.' '.$noun.' in demand';
        foreach(['strongly','firmly','vigorously']as$adverb)foreach(['disputed','challenged','contested','denied']as$verb){$out[18766][]='He '.$adverb.' '.$verb.' the claim';$out[18766][]='He '.$verb.' the claim '.$adverb;}$out[18766][]='He categorically denied the claim';
        foreach(['need to','have to']as$modal)foreach(['assess','evaluate']as$verb)foreach(['the risks','risks']as$noun)foreach(['carefully','thoroughly']as$adverb){$out[18768][]='We '.$modal.' '.$verb.' '.$noun.' '.$adverb;$out[18768][]='We '.$modal.' '.$adverb.' '.$verb.' '.$noun;}
        return array_map(static fn($rows)=>array_values(array_unique($rows)),$out);
    }
}
