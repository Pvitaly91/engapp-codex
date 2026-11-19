<?php

namespace App\Support;

use RuntimeException;

/** One exact accepted Vocabulary and collocations owners; educational source bytes remain authoritative. */
final class VocabCollocationsPracticeSource
{
    public const SOURCE='docs/content/vocabulary-collocations-practice-source.v1.json';
    public const SOURCE_SHA='fb9ffb21d64678fe0ff4c97cea3078507cdd7c56f69393b4b4215db8c34baf61';
    public const SPECS=['advanced-collocation-and-lexical-choice'=>[M38ArticlesCollocationsPackage::class,2,316,'AdvancedCollocationAndLexicalChoiceTheorySeeder']];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\VocabularyAndCollocations\\'.$s[3],self::SPECS);}
    public static function digest(array $v):string{return hash('sha256',M26DetailPackage::json($v));}
    private static function targets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as$topic=>[$provider,$index,$pageId,$name]){
            if(!isset($packages[$provider]))$packages[$provider]=$provider::load()[1];
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['after']['slug']??null)!==$topic)throw new RuntimeException('Vocabulary and collocations accepted owner differs.');
            $found=[];foreach($target['after']['page']['blocks']as$i=>$b)if(($b['type']??null)==='practice-set')$found[]=[$i,$b];
            if(count($found)!==1)throw new RuntimeException('Vocabulary and collocations requires one exact practice block.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>58];
            foreach(['type','column','heading','level','css_class']as$f)$binding[$f]=$source[$f]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);$presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation))throw new RuntimeException('Vocabulary and collocations exact native presenter rejected '.$topic);
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase','cases']as$kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0,'cases'=>0])throw new RuntimeException('Vocabulary and collocations retained control count differs.');
            $definition='database/seeders/Page_V3/VocabularyAndCollocations/'.$name.'/definition.json';$path=base_path($definition);$d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='vocabulary-and-collocations'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Vocabulary and collocations canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'mode'=>'native','identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/vocabulary-and-collocations/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design']??null,'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==1)throw new RuntimeException('Vocabulary and collocations one-owner scope differs.');return$out;
    }
    private static function manifest(array$targets):array{foreach($targets as&$t){unset($t['before_data'],$t['after_data']);}unset($t);return['schema_version'=>1,'purpose'=>'Exact accepted Vocabulary and collocations native sources; historical educational bytes stay authoritative.','targets'=>$targets];}
    public static function detailed():array{return['schema_version'=>1,'targets'=>self::targets()];}
    public static function compile():array{return self::manifest(self::targets());}
    public static function validate(array$d):void{if($d!==self::compile())throw new RuntimeException('Vocabulary and collocations source bindings differ.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Vocabulary and collocations source bytes differ.');
        static$d;if($d===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::targets();if($candidate!==self::manifest($targets))throw new RuntimeException('Vocabulary and collocations exact source bindings differ.');$d=['schema_version'=>1,'targets'=>$targets];}return$d;
    }
}
