<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Strict render-only guidance for existing exact thematic questions; no stored answer/ID changes. */
final class AcademicEnglishWidgetHints
{
    public const SOURCE='docs/content/academic-english-widget-hints.v1.json';
    public const SOURCE_SHA='d1ddeee1648d8656999e0bc5709504fb11e0311dac60b251d4bd36f35eab4efe';
    public const FIELDS=['id','uuid','seeder','type','level','category_id','source_id','difficulty','flag','theory_text_block_uuid','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
    // No historical binder changes these four banks; full raw/post states are still independently pinned.
    public const PRESENTATION_SOURCES=[];
    public static function prepared(Question$q):array{return VerbPatternsWidgetHints::prepared($q);}
    public static function snapshotPins(array$rows):array
    {
        $out=[];foreach($rows as$r){$raw=array_intersect_key($r,array_flip(self::FIELDS));$post=$r['canonical_server_snapshot']??null;if(!is_array($post))throw new RuntimeException('Academic English complete post-binder state absent.');$out[$r['id']]=[M26DetailPackage::digest($raw),M26DetailPackage::digest($post)];}return$out;
    }
    public static function validate(array$d):void
    {
        foreach(self::PRESENTATION_SOURCES as$f=>$sha)if(!is_file(base_path($f))||is_link(base_path($f))||!hash_equals($sha,hash_file('sha256',base_path($f))))throw new RuntimeException('Academic English complete binder source differs.');
        $native=AcademicEnglishPracticeSource::load()['targets'];
        if(($d['schema_version']??null)!==1||($d['package']??null)!=='academic-english-native-widget-hints'||($d['source']??null)!==['path'=>AcademicEnglishPracticeSupplement::SOURCE,'sha256'=>AcademicEnglishPracticeSupplement::SOURCE_SHA]||($d['presentation_sources']??null)!==self::PRESENTATION_SOURCES||array_column($d['targets']??[],'topic')!==array_column($native,'topic')||count($d['questions']??[])!==20||!hash_equals(AcademicEnglishWidgetPlans::SNAPSHOTS_SHA,M26DetailPackage::digest(self::snapshotPins($d['questions']))))throw new RuntimeException('Academic English widget exact source scope differs.');
        $seen=[];$offset=0;$instructions=AcademicEnglishWidgetPlans::instructions();$aliases=AcademicEnglishWidgetPlans::aliases();
        foreach($d['targets']as$i=>$t){$s=$native[$i];$ids=AcademicEnglishWidgetPlans::ids()[$s['topic']];$banks=$s['before_data']['linked_practice']['seeder_classes'];if(($t['binding']??null)!==$s['binding']||($t['local_url']??null)!==$s['local_url']||($t['selected_ids']??null)!==$ids||($t['bank_seeders']??null)!==$banks)throw new RuntimeException('Academic English widget target binding differs.');
            foreach($ids as$id){$r=$d['questions'][$offset++];$hint=AcademicEnglishWidgetPlans::hints()[$id];$condition=AcademicEnglishWidgetPlans::conditions()[$id]??$r['payload_question'];$parts=[['kind'=>'sentence','text'=>$condition],['kind'=>'instruction','text'=>' '.$instructions[$id]]];$accepted=$aliases[$id]??[];$labels=AcademicEnglishPracticeSupplement::tokenLabels($r['correct_tokens'],$accepted);$derived=[];foreach($accepted as$a)foreach(preg_split('/\s+/u',$a)as$v)if(!in_array($v,$r['correct_tokens'],true))$derived[]=$v;$derived=array_values(array_unique($derived));
                if(($r['id']??null)!==$id||isset($seen[$id])||($r['block_uuid']??null)!==$s['binding']['uuid']||($r['type']??null)!=='4'||($r['level']??null)!==$s['binding']['level']||!in_array($r['seeder']??null,$banks,true)||($r['hint_uk']??null)!==$hint||($r['display_parts']??null)!==$parts||($r['display_question']??null)!==$condition.' '.$instructions[$id]||($r['accepted_sentences']??null)!==$accepted||($r['token_labels']??null)!==$labels||($r['derived_options']??null)!==$derived)throw new RuntimeException('Academic English widget guidance differs.');
                if(!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Academic English widget hint must be Ukrainian.');
                if(count($r['answers'])<2||count($r['correct_tokens'])!==count($r['answers']))throw new RuntimeException('Academic English widget answer slot count differs.');foreach($r['answers']as$a=>$answer)if($answer['question_id']!==$id||$answer['marker']!=='a'.($a+1)||!is_int($answer['id'])||!is_int($answer['option_id'])||!is_string($answer['option']))throw new RuntimeException('Academic English widget answer identity differs.');$seen[$id]=true;
            }
        }if(count($seen)!==20||count(AcademicEnglishWidgetPlans::hints())!==20)throw new RuntimeException('Academic English needs twenty exact widget guidance rows.');
    }
    public static function load():array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Academic English widget source bytes differ.');static$d;if($d===null){$d=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($d);}return$d;
    }
    public static function project(object$q,?string$blockUuid,array$payload,bool$canonical):array
    {
        if(!$canonical||app()->getLocale()!=='uk'||!$q instanceof Question||(string)$q->type!=='4'||($payload['type']??null)!=='4'||!in_array($q->id,array_merge(...array_values(AcademicEnglishWidgetPlans::ids())),true))return$payload;
        try{foreach(self::load()['questions']as$r){if($blockUuid!==$r['block_uuid']||$q->id!==$r['id'])continue;$state=self::prepared($q);$raw=array_intersect_key($r,array_flip(self::FIELDS));if(($state!==$raw&&$state!==$r['canonical_server_snapshot'])||($payload['id']??null)!==$state['id']||($payload['level']??null)!==$state['level']||($payload['question']??null)!==$state['payload_question']||($payload['correct_tokens']??null)!==$state['correct_tokens']||($payload['answers']??null)!==array_map(fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$state['answers'])||!is_array($payload['options']??null))return$payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($state['correct_tokens'])as$token=>$count)if(($available[$token]??0)<$count)return$payload;
            $payload['question']=$r['display_question'];$payload['compose_prompt_parts']=$r['display_parts'];$payload['compose_prompt_revision']=self::SOURCE_SHA;$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$r['hint_uk'];if($r['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$state['correct_tokens'])],$payload['accepted_sentences']??[],$r['accepted_sentences'])));$payload['token_labels']=array_replace($payload['token_labels']??[],$r['token_labels']);$payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$r['derived_options'])));$payload['academic_english_widget_hint_revision']=AcademicEnglishPracticeSupplement::digest($r);return$payload;
        }}catch(Throwable){}return$payload;
    }
}
