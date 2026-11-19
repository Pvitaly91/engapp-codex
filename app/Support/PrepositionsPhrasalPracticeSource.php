<?php

namespace App\Support;

use RuntimeException;

/** Exact native sources for the finite Prepositions and Phrasal Verbs owner set. */
final class PrepositionsPhrasalPracticeSource
{
    public const SOURCE='docs/content/prepositions-phrasal-practice-source.v1.json';
    public const SOURCE_SHA='2e90d184b79fd5ac900a2bb668f549f752df03e0d84c23b93a91fccdcb5949d8';
    public const SPECS=[
        'prepositions-of-time'=>[M77OnePrepositionsPhrasal::class,1,120,'PrepositionsOfTimeTheorySeeder'],
        'prepositions-of-place'=>[M77OnePrepositionsPhrasal::class,2,119,'PrepositionsOfPlaceTheorySeeder'],
        'prepositions-of-movement'=>[M77OnePrepositionsPhrasal::class,3,118,'PrepositionsOfMovementTheorySeeder'],
        'dependent-prepositions'=>[M77OnePrepositionsPhrasal::class,4,115,'DependentPrepositionsTheorySeeder'],
        'phrasal-verbs-basics'=>[M77OnePrepositionsPhrasal::class,5,116,'PhrasalVerbsBasicsTheorySeeder'],
        'separable-and-inseparable-phrasal-verbs'=>[M78PhrasalQuestionBasics::class,0,117,'PhrasalVerbsSeparableInseparableTheorySeeder'],
        'common-phrasal-verbs-by-topic'=>[M78PhrasalQuestionBasics::class,1,114,'CommonPhrasalVerbsByTopicTheorySeeder'],
    ];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\PrepositionsAndPhrasalVerbs\\'.$s[3],self::SPECS);}
    public static function digest(array $value):string{return hash('sha256',M26DetailPackage::json($value));}
    private static function deriveTargets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as $topic=>[$provider,$index,$pageId,$name]){
            if(!isset($packages[$provider]))[, $packages[$provider]]=$provider::load();
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['slug']??null)!==$topic)throw new RuntimeException('Prepositions/Phrasal exact source owner differs.');
            if(isset($target['page_binding'])&&$target['page_binding']!==['id'=>$pageId,'page_category_id'=>19])throw new RuntimeException('Prepositions/Phrasal exact source Page binding differs.');
            $blocks=$target['after']['page']['blocks'];$found=[];foreach($blocks as $i=>$b)if(($b['type']??null)==='practice-set')$found[]=[$i,$b];
            if(count($found)!==1)throw new RuntimeException('Prepositions/Phrasal native practice cardinality differs.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>19];
            foreach(['type','column','heading','level','css_class'] as $field)$binding[$field]=$source[$field]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);
            $presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation)||($presentation['m42_native_design']['component']??null)!=='practice-set')throw new RuntimeException('Prepositions/Phrasal accepted presenter rejected '.$topic);
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase'] as $kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0])throw new RuntimeException('Prepositions/Phrasal retained native count differs.');
            $definition='database/seeders/Page_V3/PrepositionsAndPhrasalVerbs/'.$name.'/definition.json';$path=base_path($definition);
            if(!is_file($path)||is_link($path))throw new RuntimeException('Prepositions/Phrasal canonical definition absent.');
            $d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='prepositions-and-phrasal-verbs'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Prepositions/Phrasal canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/prepositions-and-phrasal-verbs/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design'],'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==7)throw new RuntimeException('Prepositions/Phrasal exact seven-owner scope differs.');return $out;
    }
    public static function detailed():array{return ['schema_version'=>1,'targets'=>self::deriveTargets()];}
    private static function manifest(array $targets):array{foreach($targets as &$t){unset($t['before_data'],$t['after_data']);}unset($t);return ['schema_version'=>1,'purpose'=>'Exact accepted Prepositions and Phrasal Verbs native sources; frozen educational bytes remain authoritative.','targets'=>$targets];}
    public static function compile():array{return self::manifest(self::deriveTargets());}
    public static function validate(array $doc):void{if($doc!==self::compile())throw new RuntimeException('Prepositions/Phrasal native exact manifest differs.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Prepositions/Phrasal native source bytes differ.');
        static $doc;if($doc===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::deriveTargets();if($candidate!==self::manifest($targets))throw new RuntimeException('Prepositions/Phrasal native source bindings differ.');$doc=['schema_version'=>1,'targets'=>$targets];}return $doc;
    }
}
