<?php

namespace App\Support;

use RuntimeException;

/** Exact six-owner accepted sources; historic native/authored payloads remain authoritative. */
final class MixedRevisionPracticeSource
{
    public const SOURCE='docs/content/mixed-revision-practice-source.v1.json';
    public const SOURCE_SHA='c67cb1c9e82f42e9b305785049740c80d08750c835260568109ac001bacb6f9a';
    public const SPECS=[
        'a1-mixed-revision'=>[M82CommonMistakesLevelRevision::class,4,325,'BasicGrammarA1MixedRevisionTheorySeeder','native'],
        'a2-mixed-revision'=>[M82CommonMistakesLevelRevision::class,5,326,'BasicGrammarA2MixedRevisionTheorySeeder','native'],
        'b1-mixed-revision'=>[M40TensesB1Package::class,2,327,'BasicGrammarB1MixedRevisionTheorySeeder','authored'],
        'b2-mixed-revision'=>[M83B2RevisionBasicGrammar::class,0,328,'B2MixedRevisionTheorySeeder','native'],
        'c1-mixed-revision'=>[M39PracticeUiPackage::class,1,329,'C1MixedRevisionTheorySeeder','authored'],
        'c2-mixed-revision'=>[M39PracticeUiPackage::class,2,330,'C2MixedRevisionTheorySeeder','authored'],
    ];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\BasicGrammar\\'.$s[3],self::SPECS);}
    public static function digest(array $v):string{return hash('sha256',M26DetailPackage::json($v));}
    private static function targets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as$topic=>[$provider,$index,$pageId,$name,$mode]){
            if(!isset($packages[$provider])){$loaded=$provider::load();$packages[$provider]=$provider===M39PracticeUiPackage::class?$loaded:$loaded[1];}
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['after']['slug']??null)!==$topic)throw new RuntimeException('Mixed Revision accepted owner differs.');
            $blocks=$target['after']['page']['blocks'];$found=[];foreach($blocks as$i=>$block)if(($block['type']??null)==='practice-set')$found[]=[$i,$block];
            if(count($found)!==1)throw new RuntimeException('Mixed Revision needs one exact practice block.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>59];
            foreach(['type','column','heading','level','css_class']as$field)$binding[$field]=$source[$field]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);$presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation))throw new RuntimeException('Mixed Revision accepted presenter rejected '.$topic);
            $render=$presentation['data']??$data;
            $counts=[];foreach(['selects','choices','inputs','rephrase','cases']as$kind)$counts[$kind]=count($render[$kind]??[]);
            if($mode==='native'&&$counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0,'cases'=>0])throw new RuntimeException('Mixed Revision native retained count differs.');
            if($mode==='authored'&&($counts['cases']!==6||array_sum(array_diff_key($counts,['cases'=>true]))!==0))throw new RuntimeException('Mixed Revision authored retained count differs.');
            $definition='database/seeders/Page_V3/BasicGrammar/'.$name.'/definition.json';$path=base_path($definition);$d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='mixed-revision'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Mixed Revision canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'mode'=>$mode,'identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/mixed-revision/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design']??null,'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==6)throw new RuntimeException('Mixed Revision six-owner source differs.');return$out;
    }
    private static function manifest(array $targets):array{foreach($targets as&$t){unset($t['before_data'],$t['after_data']);}unset($t);return['schema_version'=>1,'purpose'=>'Exact accepted Mixed Revision native/authored sources; frozen educational bytes remain authoritative.','targets'=>$targets];}
    public static function detailed():array{return['schema_version'=>1,'targets'=>self::targets()];}
    public static function compile():array{return self::manifest(self::targets());}
    public static function validate(array $doc):void{if($doc!==self::compile())throw new RuntimeException('Mixed Revision exact native source differs.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Mixed Revision source bytes differ.');
        static$doc;if($doc===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::targets();if($candidate!==self::manifest($targets))throw new RuntimeException('Mixed Revision source bindings differ.');$doc=['schema_version'=>1,'targets'=>$targets];}return$doc;
    }
}
