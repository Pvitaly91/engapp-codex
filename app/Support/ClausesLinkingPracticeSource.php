<?php

namespace App\Support;

use RuntimeException;

/** Exact native sources for the finite Clauses and Linking Words owner set. */
final class ClausesLinkingPracticeSource
{
    public const SOURCE='docs/content/clauses-linking-practice-source.v1.json';
    public const SOURCE_SHA='df8da4fce64fbf69e313b49aa5711d3f3bcef031a6259f3872d0dfb673190dc4';
    public const SPECS=[
        'linking-words-reason-result-contrast'=>[M27LinkingWordsPackage::class,0,290,'LinkingWordsReasonResultContrastTheorySeeder'],
        'advanced-linking-devices'=>[M27LinkingWordsPackage::class,1,302,'AdvancedLinkingDevicesTheorySeeder'],
        'concessive-and-contrastive-structures'=>[M27LinkingWordsPackage::class,2,315,'ConcessiveAndContrastiveStructuresTheorySeeder'],
        'participle-clauses-basics'=>[M30ParticipleClausesPackage::class,0,285,'ParticipleClausesBasicsTheorySeeder'],
        'participle-clauses'=>[M30ParticipleClausesPackage::class,1,299,'ParticipleClausesTheorySeeder'],
        'advanced-participle-and-absolute-clauses'=>[M30ParticipleClausesPackage::class,2,318,'AdvancedParticipleAndAbsoluteClausesTheorySeeder'],
        'discourse-markers-and-cohesion'=>[M34ArgumentationCohesionPackage::class,1,310,'DiscourseMarkersAndCohesionTheorySeeder'],
        'relative-clauses'=>[M62ReportedReviewRelativeBasicsPackage::class,1,202,'ClausesAndLinkingWordsRelativeClausesTheorySeeder'],
        'defining-relative-clauses'=>[M62ReportedReviewRelativeBasicsPackage::class,2,31,'ClausesAndLinkingWordsDefiningRelativeClausesTheorySeeder'],
        'non-defining-relative-clauses'=>[M63RelativeCompletionLinkingPackage::class,0,33,'ClausesAndLinkingWordsNonDefiningRelativeClausesTheorySeeder'],
        'omitting-relative-pronouns'=>[M63RelativeCompletionLinkingPackage::class,1,34,'ClausesAndLinkingWordsOmittingRelativePronounsTheorySeeder'],
        'linking-words'=>[M63RelativeCompletionLinkingPackage::class,2,32,'ClausesAndLinkingWordsLinkingWordsTheorySeeder'],
        'reason-because-since-as'=>[M64ReasonResultContrastPackage::class,0,36,'ClausesAndLinkingWordsReasonTheorySeeder'],
        'result-so-therefore-as-a-result'=>[M64ReasonResultContrastPackage::class,1,37,'ClausesAndLinkingWordsResultTheorySeeder'],
        'contrast-although-though-however'=>[M64ReasonResultContrastPackage::class,2,30,'ClausesAndLinkingWordsContrastTheorySeeder'],
        'purpose-to-in-order-to-so-that'=>[M65PurposeGerundInfinitivePackage::class,0,35,'ClausesAndLinkingWordsPurposeTheorySeeder'],
        'relative-clauses-with-whose-and-which'=>[M94RelativeClausesWhoseWhich::class,0,284,'RelativeClausesWithWhoseAndWhichTheorySeeder'],
    ];
    public static function owners():array{return array_map(static fn($s)=>'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\'.$s[3],self::SPECS);}
    public static function digest(array $value):string{return hash('sha256',M26DetailPackage::json($value));}
    private static function deriveTargets():array
    {
        $packages=[];$out=[];
        foreach(self::SPECS as $topic=>[$provider,$index,$pageId,$name]){
            if(!isset($packages[$provider]))[, $packages[$provider]]=$provider::load();
            $target=$packages[$provider]['targets'][$index]??null;$identity=self::owners()[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['slug']??null)!==$topic)throw new RuntimeException('Clauses exact source owner differs.');
            if(isset($target['page_binding'])&&$target['page_binding']!==['id'=>$pageId,'page_category_id'=>34])throw new RuntimeException('Clauses exact source Page binding differs.');
            $blocks=$target['after']['page']['blocks'];$found=[];foreach($blocks as $i=>$b)if(($b['type']??null)==='practice-set')$found[]=[$i,$b];
            if(count($found)!==1)throw new RuntimeException('Clauses native practice cardinality differs.');
            [$slot,$source]=$found[0];$binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),'sort_order'=>$slot+1,'page_id'=>$pageId,'page_category_id'=>34];
            foreach(['type','column','heading','level','css_class'] as $field)$binding[$field]=$source[$field]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=new \App\Models\TextBlock();$block->setRawAttributes($binding+['id'=>99000,'body'=>$source['body']],true);
            $presentation=$provider::presentation($block,$data);
            $presentation=M42NativeDesignPackage::decorate($block,$data,$presentation,true)??$presentation;
            if(!is_array($presentation)||($presentation['m42_native_design']['component']??null)!=='practice-set')throw new RuntimeException('Clauses accepted presenter rejected '.$topic);
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase'] as $kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0])throw new RuntimeException('Clauses retained native count differs.');
            $definition='database/seeders/Page_V3/ClausesAndLinkingWords/'.$name.'/definition.json';$path=base_path($definition);
            if(!is_file($path)||is_link($path))throw new RuntimeException('Clauses canonical definition absent.');
            $d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'||($d['page']['category']['slug']??null)!=='clauses-and-linking-words'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Clauses canonical definition differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $out[]=['topic'=>$topic,'identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/clauses-and-linking-words/'.$topic,'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design'],'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($out)!==17)throw new RuntimeException('Clauses exact seventeen-owner scope differs.');return $out;
    }
    public static function detailed():array{return ['schema_version'=>1,'targets'=>self::deriveTargets()];}
    private static function manifest(array $targets):array{foreach($targets as &$t){unset($t['before_data'],$t['after_data']);}unset($t);return ['schema_version'=>1,'purpose'=>'Exact accepted Clauses and Linking Words native sources; frozen educational bytes remain authoritative.','targets'=>$targets];}
    public static function compile():array{return self::manifest(self::deriveTargets());}
    public static function validate(array $doc):void{if($doc!==self::compile())throw new RuntimeException('Clauses native exact manifest differs.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Clauses native source bytes differ.');
        static $doc;if($doc===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::deriveTargets();if($candidate!==self::manifest($targets))throw new RuntimeException('Clauses native source bindings differ.');$doc=['schema_version'=>1,'targets'=>$targets];}return $doc;
    }
}
