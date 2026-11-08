<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Exact render-only guidance for the existing thematic widgets; stored question IDs/answers remain unchanged. */
final class ReportedSpeechWidgetHints
{
    public const SOURCE='docs/content/reported-speech-widget-hints.v1.json';
    public const SOURCE_SHA='b2014be87e454ad34a0d37e6905865dd61be83b57c44871e68d5682809fb36da';
    public const FIELDS=['id','uuid','seeder','type','level','category_id','source_id','difficulty','flag','theory_text_block_uuid','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
    public const PRESENTATION_SOURCES=[
        'app/Support/M60BuilderSelectionPresentation.php'=>'b2b7f2a0eb2d3926cc3f21cc1b12266d08500ffd2d76bac86de862e9021de778',
        'app/Support/M61BuilderSelectionPresentation.php'=>'566ea586c737b0fd752ea909b97e62bf0c02a4139ce0ea43d7dea1478c555f76',
        'app/Support/M62BuilderSelectionPresentation.php'=>'aa549f10ade64f7383c6c7c5928c01f24e54b24ec2dad8b9a9fcf55ac64f7773',
        'docs/content/m60-reported-speech-basics-bank-audit.v1.0.1.json'=>M60BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m61-reported-speech-changes-bank-audit.v1.json'=>M61BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m62-reported-review-relative-basics-bank-audit.v1.json'=>M62BuilderSelectionPresentation::AUDIT_SHA,
    ];

    /** Derive only the already-approved display clone, retaining every physical row and case guard. */
    private static function canonicalSnapshot(array $row):array
    {
        static $samples;
        if($samples===null){
            foreach(self::PRESENTATION_SOURCES as $file=>$hash)if(!is_file(base_path($file))||is_link(base_path($file))||!hash_equals($hash,hash_file('sha256',base_path($file))))throw new RuntimeException('Reported approved binder source differs.');
            $samples=[];
            foreach([M60BuilderSelectionPresentation::class=>M60ReportedSpeechBasicsPackage::class,M61BuilderSelectionPresentation::class=>M61ReportedSpeechChangesPackage::class,M62BuilderSelectionPresentation::class=>M62ReportedReviewRelativeBasicsPackage::class] as $presenter=>$package){
                $audit=json_decode(file_get_contents(base_path($presenter::AUDIT)),true,flags:JSON_THROW_ON_ERROR);[, $patch,, $contract]=$package::load();$presenter::validateAudit($audit,$patch,$contract);
                foreach($audit['targets'] as $target)if($target['category_id']===3)foreach($target['first_five'] as $sample){if(isset($samples[$sample['id']]))throw new RuntimeException('Reported approved question repeats.');$samples[$sample['id']]=$sample;}
            }
            if(count($samples)!==35)throw new RuntimeException('Reported approved binder scope differs.');
        }
        $sample=$samples[$row['id']]??null;if(!$sample)throw new RuntimeException('Reported approved binder row absent.');
        foreach($sample['question_snapshot'] as $field=>$value)if(array_key_exists($field,$row)&&$row[$field]!==$value)throw new RuntimeException('Reported original question differs from approved binder.');
        $answers=array_map(static function($a){unset($a['question_id']);return $a;},$row['answers']);
        $hints=array_map(static function($h){unset($h['question_id']);return $h;},$sample['hints_snapshot']);
        if($answers!==$sample['ordered_answer_snapshot']||$row['option_snapshot']!==$sample['original_options_snapshot']||$row['hints']!==$hints||$row['verb_hints']!==[])throw new RuntimeException('Reported original loaded relations differ from approved binder.');
        $bound=array_intersect_key($row,array_flip(self::FIELDS));$bound['question']=$sample['visible_condition_uk'];$bound['payload_question']=$sample['visible_condition_uk'];
        foreach($sample['case_restorations'] as $case){$found=false;foreach($bound['answers'] as &$answer){if($answer['marker']!==$case['marker'])continue;if($answer['id']!==$case['answer_id']||$answer['option_id']!==$case['option_id']||$answer['option']!==$case['raw'])throw new RuntimeException('Reported approved case instance differs.');$answer['option']=$case['rendered'];$found=true;}unset($answer);if(!$found)throw new RuntimeException('Reported case marker absent.');}
        $bound['correct_tokens']=ComposeTokenCase::normalize(array_column($bound['answers'],'option'));
        if($bound['correct_tokens']!==$sample['correct_tokens'])throw new RuntimeException('Reported approved visible tokens differ.');return $bound;
    }

    /** Same answer preparation as the established widget, with exact eager relation snapshots. */
    public static function prepared(Question $question): array
    {
        foreach(['answers','options','hints','verbHints'] as $relation)if(!$question->relationLoaded($relation))throw new RuntimeException('Reported widget capture requires complete eager relations.');
        $answers=[];foreach($question->answers->sort(fn($a,$b)=>strnatcasecmp($a->marker,$b->marker)) as $answer){
            if(!$answer->relationLoaded('option'))throw new RuntimeException('Reported widget answer option missing.');
            $answers[]=['id'=>$answer->id,'question_id'=>$answer->question_id,'marker'=>$answer->marker,'position'=>$answer->position,'option_id'=>$answer->option_id,'option'=>$answer->option->option];
        }
        $tokens=LocalizedComposeText::normalizeTokens($question,array_values(array_filter(array_column($answers,'option'))));
        $options=$question->options->map(fn($o)=>['id'=>$o->id,'option'=>$o->option])->sortBy('id')->values()->all();
        $hints=$question->hints->map(fn($h)=>['id'=>$h->id,'provider'=>$h->provider,'locale'=>$h->locale,'hint'=>$h->hint])->sortBy('id')->values()->all();
        $verb=[];foreach($question->verbHints as $hint){if(!$hint->relationLoaded('option'))throw new RuntimeException('Reported widget verb hint option missing.');$verb[]=['id'=>$hint->id,'marker'=>$hint->marker,'locale'=>$hint->locale,'option_id'=>$hint->option_id,'option'=>$hint->option->option];}
        usort($verb,static fn($a,$b)=>$a['id']<=>$b['id']);
        return ['id'=>$question->id,'uuid'=>$question->uuid,'seeder'=>$question->seeder,'type'=>(string)$question->type,'level'=>$question->level,'category_id'=>$question->category_id,'source_id'=>$question->source_id,'difficulty'=>$question->getRawOriginal('difficulty'),'flag'=>$question->getRawOriginal('flag'),'theory_text_block_uuid'=>$question->getRawOriginal('theory_text_block_uuid'),'question'=>$question->question,'options_by_marker'=>$question->getRawOriginal('options_by_marker'),'payload_question'=>LocalizedComposeText::source($question,'uk'),'answers'=>$answers,'correct_tokens'=>$tokens,'option_snapshot'=>$options,'hints'=>$hints,'verb_hints'=>$verb];
    }


    public static function validate(array $doc):void
    {
        $native=ReportedSpeechPracticeSupplement::load()['targets'];
        if(($doc['schema_version']??null)!==1||($doc['package']??null)!=='reported-speech-native-widget-hints'
            ||($doc['source']??null)!==['path'=>ReportedSpeechPracticeSupplement::SOURCE,'sha256'=>ReportedSpeechPracticeSupplement::SOURCE_SHA]
            ||($doc['presentation_sources']??null)!==self::PRESENTATION_SOURCES
            ||array_column($doc['targets']??[],'topic')!==array_column($native,'topic')||count($doc['questions']??[])!==35)throw new RuntimeException('Reported Speech widget scope differs.');
        $offset=0;$seen=[];
        foreach($doc['targets'] as $i=>$target){
            $expected=$native[$i];$banks=$expected['before_data']['linked_practice']['seeder_classes'];
            if(($target['binding']??null)!==$expected['binding']||($target['local_url']??null)!==$expected['local_url']||($target['bank_seeders']??null)!==$banks||count($target['selected_ids']??[])!==5||count(array_unique($target['selected_ids']))!==5)throw new RuntimeException('Reported Speech widget target differs.');
            foreach($target['selected_ids'] as $id){
                $row=$doc['questions'][$offset++];$hint=ReportedSpeechWidgetPlans::HINTS[$id]??null;
                if(($row['id']??null)!==$id||isset($seen[$id])||($row['block_uuid']??null)!==$target['binding']['uuid']||($row['type']??null)!=='4'
                    ||($row['level']??null)!==$target['binding']['level']||!in_array($row['seeder']??null,$banks,true)
                    ||($row['hint_uk']??null)!==$hint||($row['display_question']??null)!==(ReportedSpeechWidgetPlans::CONDITIONS[$id]??$row['payload_question']).' '.(ReportedSpeechWidgetPlans::INSTRUCTIONS[$id]??'')
                    ||($row['display_parts']??null)!==[['kind'=>'sentence','text'=>ReportedSpeechWidgetPlans::CONDITIONS[$id]??$row['payload_question']],['kind'=>'instruction','text'=>' '.(ReportedSpeechWidgetPlans::INSTRUCTIONS[$id]??'')]]
                    ||($row['canonical_server_snapshot']??null)!==self::canonicalSnapshot($row)
                    ||($row['accepted_sentences']??null)!==(ReportedSpeechWidgetPlans::ALIASES[$id]??[])||($row['token_labels']??null)!==(ReportedSpeechWidgetPlans::LABELS[$id]??[])
                    ||count($row['answers']??[])<2||count($row['correct_tokens']??[])!==count($row['answers']))throw new RuntimeException('Reported Speech widget exact source differs.');
                if(!is_string($hint)||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Reported Speech widget guidance must be Ukrainian.');
                foreach($row['token_labels'] as $token=>$caption)if(!in_array($token,$row['correct_tokens'],true)||!is_string($caption)||trim($caption)==='')throw new RuntimeException('Reported Speech widget token caption differs.');
                foreach($row['answers'] as $a=>$answer)if(($answer['question_id']??null)!==$id||($answer['marker']??null)!=='a'.($a+1)||!is_int($answer['id']??null)||!is_int($answer['option_id']??null)||!is_string($answer['option']??null))throw new RuntimeException('Reported Speech widget answer instances differ.');
                $seen[$id]=true;
            }
        }
        if(count(ReportedSpeechWidgetPlans::HINTS)!==35||count(ReportedSpeechWidgetPlans::INSTRUCTIONS)!==35)throw new RuntimeException('Reported Speech widget hints need all35exactrows.');
    }

    public static function load():array
    {
        $path=base_path(self::SOURCE);
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Reported Speech widget immutable source differs.');
        static $document;if($document===null){$document=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($document);}return $document;
    }
    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||!$question instanceof Question||(string)$question->type!=='4'||($payload['type']??null)!=='4')return $payload;
        try{foreach(self::load()['questions'] as $row){if($blockUuid!==$row['block_uuid']||$question->id!==$row['id'])continue;
            $state=self::prepared($question);$original=array_intersect_key($row,array_flip(self::FIELDS));
            if(($state!==$original&&$state!==$row['canonical_server_snapshot'])||($payload['id']??null)!==$state['id']||($payload['level']??null)!==$state['level']||($payload['question']??null)!==$state['payload_question']||($payload['correct_tokens']??null)!==$state['correct_tokens']||($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$state['answers'])||!is_array($payload['options']??null))return $payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($state['correct_tokens']) as $token=>$count)if(($available[$token]??0)<$count)return $payload;
            $payload['question']=$row['display_question'];$payload['compose_prompt_parts']=$row['display_parts'];$payload['compose_prompt_revision']=self::SOURCE_SHA;$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];
            if($row['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$state['correct_tokens'])],$payload['accepted_sentences']??[],$row['accepted_sentences'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],array_intersect_key($row['token_labels'],array_fill_keys($state['correct_tokens'],true)));
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$row['derived_options'])));
            $payload['reported_speech_widget_hint_revision']=ReportedSpeechPracticeSupplement::digest($row);return $payload;
        }}catch(Throwable){}return $payload;
    }
}
