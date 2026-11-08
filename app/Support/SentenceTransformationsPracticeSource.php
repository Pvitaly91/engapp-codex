<?php

namespace App\Support;

use RuntimeException;

/** Exact native sources for the finite Sentence Transformations owner set. */
final class SentenceTransformationsPracticeSource
{
    public const SOURCE='docs/content/sentence-transformations-practice-source.v1.json';
    public const SOURCE_SHA='776c1b26120c3523156f5158edd637db0aae1a7520fa3b4bc3755ef53e0c1c4c';
    public const SPECS=[
        'tenses'=>[M80NegativesTransformations::class,2,152,'SentenceTransformationsTensesTheorySeeder'],
        'modals'=>[M80NegativesTransformations::class,3,148,'SentenceTransformationsModalsTheorySeeder'],
        'passive'=>[M80NegativesTransformations::class,4,149,'SentenceTransformationsPassiveTheorySeeder'],
        'reported-speech'=>[M80NegativesTransformations::class,5,151,'SentenceTransformationsReportedSpeechTheorySeeder'],
        'conditionals'=>[M81TransformationsCommonMistakes::class,0,147,'SentenceTransformationsConditionalsTheorySeeder'],
        'relative-clauses-linking-words'=>[M81TransformationsCommonMistakes::class,1,150,'SentenceTransformationsRelativeClausesLinkingWordsTheorySeeder'],
        'articles-quantifiers'=>[M81TransformationsCommonMistakes::class,2,146,'SentenceTransformationsArticlesQuantifiersTheorySeeder'],
        'word-order-emphasis'=>[M81TransformationsCommonMistakes::class,3,153,'SentenceTransformationsWordOrderEmphasisTheorySeeder'],
    ];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\SentenceTransformations\\'.$s[3],self::SPECS);}
    public static function digest(array $value):string{return hash('sha256',M26DetailPackage::json($value));}
    private static function deriveTargets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as $topic=>[$provider,$index,$pageId,$name]){
            if(!isset($packages[$provider]))[, $packages[$provider]]=$provider::load();
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['slug']??null)!==$topic)throw new RuntimeException('Sentence Transformations exact source owner differs.');
            if(isset($target['page_binding'])&&$target['page_binding']!==['id'=>$pageId,'page_category_id'=>23])throw new RuntimeException('Sentence Transformations exact source Page binding differs.');
            $blocks=$target['after']['page']['blocks'];$found=[];foreach($blocks as $i=>$b)if(($b['type']??null)==='practice-set')$found[]=[$i,$b];
            if(count($found)!==1)throw new RuntimeException('Sentence Transformations native practice cardinality differs.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>23];
            foreach(['type','column','heading','level','css_class'] as $field)$binding[$field]=$source[$field]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);
            $presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation)||($presentation['m42_native_design']['component']??null)!=='practice-set')throw new RuntimeException('Sentence Transformations accepted presenter rejected '.$topic);
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase'] as $kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0])throw new RuntimeException('Sentence Transformations retained native count differs.');
            $definition='database/seeders/Page_V3/SentenceTransformations/'.$name.'/definition.json';$path=base_path($definition);
            if(!is_file($path)||is_link($path))throw new RuntimeException('Sentence Transformations canonical definition absent.');
            $d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='sentence-transformations'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Sentence Transformations canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/sentence-transformations/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design'],'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==8)throw new RuntimeException('Sentence Transformations exact eight-owner scope differs.');return $out;
    }
    public static function detailed():array{return ['schema_version'=>1,'targets'=>self::deriveTargets()];}
    private static function manifest(array $targets):array{foreach($targets as &$t){unset($t['before_data'],$t['after_data']);}unset($t);return ['schema_version'=>1,'purpose'=>'Exact accepted Sentence Transformations native sources; frozen educational bytes remain authoritative.','targets'=>$targets];}
    public static function compile():array{return self::manifest(self::deriveTargets());}
    public static function validate(array $doc):void{if($doc!==self::compile())throw new RuntimeException('Sentence Transformations native exact manifest differs.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Sentence Transformations native source bytes differ.');
        static $doc;if($doc===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::deriveTargets();if($candidate!==self::manifest($targets))throw new RuntimeException('Sentence Transformations native source bindings differ.');$doc=['schema_version'=>1,'targets'=>$targets];}return $doc;
    }
}
