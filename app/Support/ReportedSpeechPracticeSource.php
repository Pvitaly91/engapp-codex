<?php

namespace App\Support;

use RuntimeException;

/** Exact accepted native sources, without rewriting the frozen M60–M62 releases. */
final class ReportedSpeechPracticeSource
{
    public const SOURCE='docs/content/reported-speech-practice-source.v1.json';
    public const SOURCE_SHA='9e47b28c6b0b8d87feb3ff825f53ec6aada4ac8f52bf35c1d2471957dc916275';
    private const PAGE_IDS=['reported-statements'=>3,'reported-questions'=>143,'reported-commands-and-requests'=>142,'backshift-of-tenses'=>141,'time-and-place-changes'=>145,'say-tell-ask'=>144,'advanced-reported-speech'=>283];
    public const OWNERS=[
        'reported-statements'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechStatementsTheorySeeder',
        'reported-questions'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechQuestionsTheorySeeder',
        'reported-commands-and-requests'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechCommandsRequestsTheorySeeder',
        'backshift-of-tenses'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechBackshiftTheorySeeder',
        'time-and-place-changes'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechTimePlaceChangesTheorySeeder',
        'say-tell-ask'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\ReportedSpeechSayTellAskTheorySeeder',
        'advanced-reported-speech'=>'Database\\Seeders\\Page_V3\\ReportedSpeech\\AdvancedReportedSpeechTheorySeeder',
    ];
    private const PROVIDERS=[
        'reported-statements'=>[M60ReportedSpeechBasicsPackage::class,0],
        'reported-questions'=>[M60ReportedSpeechBasicsPackage::class,1],
        'reported-commands-and-requests'=>[M60ReportedSpeechBasicsPackage::class,2],
        'backshift-of-tenses'=>[M61ReportedSpeechChangesPackage::class,0],
        'time-and-place-changes'=>[M61ReportedSpeechChangesPackage::class,1],
        'say-tell-ask'=>[M61ReportedSpeechChangesPackage::class,2],
        'advanced-reported-speech'=>[M62ReportedReviewRelativeBasicsPackage::class,0],
    ];
    public static function digest(array $value):string{return hash('sha256',M26DetailPackage::json($value));}
    public static function specs():array{return self::PROVIDERS;}
    private static function deriveTargets():array
    {
        $packages=[];$targets=[];
        foreach(self::PROVIDERS as $topic=>[$provider,$targetIndex]){
            if(!isset($packages[$provider]))[, $packages[$provider]]=$provider::load();
            $target=$packages[$provider]['targets'][$targetIndex]??null;$identity=self::OWNERS[$topic];
            if(!is_array($target)||($target['identity']??null)!==$identity||($target['slug']??null)!==$topic
                ||($target['page_binding']??null)!==['id'=>self::PAGE_IDS[$topic],'page_category_id'=>3])throw new RuntimeException('Reported practice exact owner differs.');
            $found=[];foreach($target['after']['page']['blocks'] as $i=>$block)if(($block['type']??null)==='practice-set')$found[]=[$i,$block];
            if(count($found)!==1)throw new RuntimeException('Reported practice cardinality differs.');
            [$slot,$source]=$found[0];
            $binding=['seeder'=>$identity,'locale'=>'uk','uuid'=>M26DetailPackage::uuid($identity,$source,$slot+1),
                'sort_order'=>$slot+1,'page_id'=>self::PAGE_IDS[$topic],'page_category_id'=>3];
            foreach(['type','column','heading','level','css_class'] as $field)$binding[$field]=$source[$field]??null;
            $data=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);$block=(object)($binding+['body'=>$source['body']]);
            $presentation=$provider::presentation($block,$data);
            if(!is_array($presentation)||($presentation['m42_native_design']['component']??null)!=='practice-set')throw new RuntimeException('Reported accepted presenter rejected its source.');
            $render=$presentation['data']??$data;$counts=[];foreach(['selects','choices','inputs','rephrase'] as $kind)$counts[$kind]=count($render[$kind]??[]);
            if($counts!==['selects'=>2,'choices'=>2,'inputs'=>2,'rephrase'=>0])throw new RuntimeException('Reported retained practice count differs.');
            $binding['body_sha256']=hash('sha256',$source['body']);
            $definition='database/seeders/Page_V3/ReportedSpeech/'.substr($identity,strrpos($identity,'\\')+1).'/definition.json';
            $path=base_path($definition);if(!is_file($path)||is_link($path))throw new RuntimeException('Reported canonical definition absent.');
            $d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
            if(($d['seeder']['class']??null)!==$identity||($d['slug']??null)!==$topic||($d['page']['locale']??null)!=='uk'
                ||($d['page']['category']['slug']??null)!=='reported-speech'||($d['page']['blocks'][$slot]['body']??null)!==$source['body'])throw new RuntimeException('Reported canonical definition differs.');
            $targets[]=['topic'=>$topic,'identity'=>$identity,'local_url'=>'http://gramlyze.loc/theory/reported-speech/'.$topic,
                'provider'=>$provider,'source'=>$provider::SOURCE,'source_sha256'=>$provider::SOURCE_SHA,'definition'=>$definition,
                'definition_sha256'=>hash_file('sha256',$path),'binding'=>$binding,'source_index'=>$slot,'design'=>$presentation['m42_native_design'],
                'before_data'=>$render,'after_data'=>$render,'before_data_sha256'=>self::digest($render),'after_data_sha256'=>self::digest($render),'counts'=>$counts];
        }
        if(count($targets)!==7)throw new RuntimeException('Reported seven-topic scope differs.');return $targets;
    }
    public static function detailed():array{return ['schema_version'=>1,'targets'=>self::deriveTargets()];}
    private static function manifest(array $targets):array
    {
        foreach($targets as &$t){unset($t['before_data'],$t['after_data']);}unset($t);
        return ['schema_version'=>1,'purpose'=>'Exact accepted Reported Speech native metadata; no question, answer or design mutation.','targets'=>$targets];
    }
    public static function compile():array{return self::manifest(self::deriveTargets());}
    public static function validate(array $doc):void{if($doc!==self::compile())throw new RuntimeException('Reported native source manifest differs.');}
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Reported native source bytes differ.');
        static $document;if($document===null){$candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);$targets=self::deriveTargets();if($candidate!==self::manifest($targets))throw new RuntimeException('Reported native source bindings differ.');$document=['schema_version'=>1,'targets'=>$targets];}return $document;
    }
}
