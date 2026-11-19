<?php

namespace App\Support;

use RuntimeException;

/** Four exact accepted Sentence Structure owners; educational source bytes remain authoritative. */
final class SentenceStructurePracticeSource
{
    public const SOURCE='docs/content/sentence-structure-practice-source.v1.json';
    public const SOURCE_SHA='6c4c0e2c6e4a5523facdcb4dd6feac1a577de6a891627c7b8765d96fb7e1a556';
    public const SPECS=[
        'cleft-sentences-basics'=>[M28EmphasisPackage::class,0,289,'CleftSentencesBasicsTheorySeeder'],
        'cleft-sentences-emphasis'=>[M29SentenceStructurePackage::class,0,297,'CleftSentencesEmphasisTheorySeeder'],
        'ellipsis-substitution-and-reference'=>[M29SentenceStructurePackage::class,2,311,'EllipsisSubstitutionAndReferenceTheorySeeder'],
        'complex-noun-phrases'=>[M29SentenceStructurePackage::class,1,312,'ComplexNounPhrasesTheorySeeder'],
    ];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\SentenceStructure\\'.$s[3],self::SPECS);}
    public static function digest(array $v):string{return hash('sha256',M26DetailPackage::json($v));}
    private static function targets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as$topic=>[$provider,$index,$pageId,$name]){
            if(!isset($packages[$provider]))$packages[$provider]=$provider::load()[1];
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['after']['slug']??null)!==$topic)throw new RuntimeException('Sentence Structure accepted owner differs.');
            $found=[];foreach($target['after']['page']['blocks']as$i=>$b)if(($b['type']??null)==='practice-set')$found[]=[$i,$b];
            if(count($found)!==1)throw new RuntimeException('Sentence Structure requires one exact practice block.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>53];
            foreach(['type','column','heading','level','css_class']as$f)$binding[$f]=$source[$f]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);$presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation))throw new RuntimeException('Sentence Structure exact native presenter rejected '.$topic);
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase','cases']as$kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0,'cases'=>0])throw new RuntimeException('Sentence Structure retained control count differs.');
            $definition='database/seeders/Page_V3/SentenceStructure/'.$name.'/definition.json';$path=base_path($definition);$d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='sentence-structure'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Sentence Structure canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'mode'=>'native','identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/sentence-structure/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design']??null,'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==4)throw new RuntimeException('Sentence Structure four-owner scope differs.');return$out;
    }
    private static function manifest(array$targets):array{foreach($targets as&$t){unset($t['before_data'],$t['after_data']);}unset($t);return['schema_version'=>1,'purpose'=>'Exact accepted Sentence Structure native sources; historical educational bytes stay authoritative.','targets'=>$targets];}
    public static function detailed():array{return['schema_version'=>1,'targets'=>self::targets()];}
    public static function compile():array{return self::manifest(self::targets());}
    public static function validate(array$d):void{if($d!==self::compile())throw new RuntimeException('Sentence Structure source bindings differ.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Sentence Structure source bytes differ.');
        static$d;if($d===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::targets();if($candidate!==self::manifest($targets))throw new RuntimeException('Sentence Structure exact source bindings differ.');$d=['schema_version'=>1,'targets'=>$targets];}return$d;
    }
}
