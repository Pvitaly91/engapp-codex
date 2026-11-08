<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Exact render-only guidance for the existing thematic widgets; stored question IDs/answers remain unchanged. */
final class CommonMistakesWidgetHints
{
    public const SOURCE='docs/content/common-mistakes-widget-hints.v1.json';
    public const SOURCE_SHA='bf5bfa35e8ef19ce8187b868764c4a0915ab4db5415efb483b4a85d8c14ec651';
    public const FIELDS=['id','uuid','seeder','type','level','category_id','source_id','difficulty','flag','theory_text_block_uuid','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
    public const PRESENTATION_SOURCES=[
        'app/Support/M81BuilderSelectionPresentation.php'=>'9425591c9e8aefb1763e7aba417baaca5371ca14b10ff06adf6d88075f0e0485',
        'app/Support/M82BuilderSelectionPresentation.php'=>'37abcf1d4650c74946c049d6049263cef3d1827eaf51d580faee9853ece44a1d',
        'docs/content/m81-sentence-builder-bank-audit.v1.json'=>M81BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m82-sentence-builder-bank-audit.v1.json'=>M82BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/word-order-practice-supplement.v1.json'=>WordOrderPracticeSupplement::SOURCE_SHA,
    ];

    /** Only the two independently pinned complete prepared states are accepted. */
    private static function canonicalSnapshot(array $row):array
    {
        static $checked=false;if(!$checked){foreach(self::PRESENTATION_SOURCES as $file=>$hash)if(!is_file(base_path($file))||is_link(base_path($file))||!hash_equals($hash,hash_file('sha256',base_path($file))))throw new RuntimeException('Common Mistakes strict binder source differs.');$checked=true;}
        $pins=CommonMistakesWidgetPlans::SNAPSHOTS[$row['id']]??null;$raw=array_intersect_key($row,array_flip(self::FIELDS));$post=$row['canonical_server_snapshot']??null;
        if(!is_array($pins)||count($pins)!==2||!is_array($post)||!hash_equals($pins[0],M26DetailPackage::digest($raw))||!hash_equals($pins[1],M26DetailPackage::digest($post)))throw new RuntimeException('Common Mistakes complete raw or post-binder snapshot differs.');return $post;
    }

    /** Same answer preparation as the established widget, with exact eager relation snapshots. */
    public static function prepared(Question $question): array
    {
        foreach(['answers','options','hints','verbHints'] as $relation)if(!$question->relationLoaded($relation))throw new RuntimeException('Common Mistakes widget capture requires complete eager relations.');
        $answers=[];foreach($question->answers->sort(fn($a,$b)=>strnatcasecmp($a->marker,$b->marker)) as $answer){
            if(!$answer->relationLoaded('option'))throw new RuntimeException('Common Mistakes widget answer option missing.');
            $answers[]=['id'=>$answer->id,'question_id'=>$answer->question_id,'marker'=>$answer->marker,'position'=>$answer->position,'option_id'=>$answer->option_id,'option'=>$answer->option->option];
        }
        $tokens=LocalizedComposeText::normalizeTokens($question,array_values(array_filter(array_column($answers,'option'))));
        $options=$question->options->map(fn($o)=>['id'=>$o->id,'option'=>$o->option])->sortBy('id')->values()->all();
        $hints=$question->hints->map(fn($h)=>['id'=>$h->id,'provider'=>$h->provider,'locale'=>$h->locale,'hint'=>$h->hint])->sortBy('id')->values()->all();
        $verb=[];foreach($question->verbHints as $hint){if(!$hint->relationLoaded('option'))throw new RuntimeException('Common Mistakes widget verb hint option missing.');$verb[]=['id'=>$hint->id,'marker'=>$hint->marker,'locale'=>$hint->locale,'option_id'=>$hint->option_id,'option'=>$hint->option->option];}
        usort($verb,static fn($a,$b)=>$a['id']<=>$b['id']);
        return ['id'=>$question->id,'uuid'=>$question->uuid,'seeder'=>$question->seeder,'type'=>(string)$question->type,'level'=>$question->level,'category_id'=>$question->category_id,'source_id'=>$question->source_id,'difficulty'=>$question->getRawOriginal('difficulty'),'flag'=>$question->getRawOriginal('flag'),'theory_text_block_uuid'=>$question->getRawOriginal('theory_text_block_uuid'),'question'=>$question->question,'options_by_marker'=>$question->getRawOriginal('options_by_marker'),'payload_question'=>LocalizedComposeText::source($question,'uk'),'answers'=>$answers,'correct_tokens'=>$tokens,'option_snapshot'=>$options,'hints'=>$hints,'verb_hints'=>$verb];
    }


    public static function validate(array $doc):void
    {
        $native=CommonMistakesPracticeSource::load()['targets'];
        if(($doc['schema_version']??null)!==1||($doc['package']??null)!=='common-mistakes-native-widget-hints'
            ||($doc['source']??null)!==['path'=>CommonMistakesPracticeSupplement::SOURCE,'sha256'=>CommonMistakesPracticeSupplement::SOURCE_SHA]
            ||($doc['presentation_sources']??null)!==self::PRESENTATION_SOURCES
            ||array_column($doc['targets']??[],'topic')!==array_column($native,'topic')||count($doc['questions']??[])!==30)throw new RuntimeException('Common Mistakes widget scope differs.');
        $offset=0;$seen=[];
        foreach($doc['targets'] as $i=>$target){
            $expected=$native[$i];$banks=$expected['before_data']['linked_practice']['seeder_classes'];
            if(($target['binding']??null)!==$expected['binding']||($target['local_url']??null)!==$expected['local_url']||($target['bank_seeders']??null)!==$banks||count($target['selected_ids']??[])!==5||count(array_unique($target['selected_ids']))!==5)throw new RuntimeException('Common Mistakes widget target differs.');
            foreach($target['selected_ids'] as $id){
                $row=$doc['questions'][$offset++];$hint=CommonMistakesWidgetPlans::HINTS[$id]??null;$hintOnly=in_array($id,CommonMistakesWidgetPlans::HINT_ONLY_IDS,true);
                if(($row['id']??null)!==$id||isset($seen[$id])||($row['block_uuid']??null)!==$target['binding']['uuid']||($row['type']??null)!=='4'
                    ||($row['level']??null)!==$target['binding']['level']||!in_array($row['seeder']??null,$banks,true)
                    ||($row['hint_only']??false)!==$hintOnly||($row['hint_uk']??null)!==$hint
                    ||(!$hintOnly&&(($row['display_question']??null)!==(CommonMistakesWidgetPlans::CONDITIONS[$id]??$row['payload_question']).' '.(CommonMistakesWidgetPlans::INSTRUCTIONS[$id]??'')
                        ||($row['display_parts']??null)!==[['kind'=>'sentence','text'=>CommonMistakesWidgetPlans::CONDITIONS[$id]??$row['payload_question']],['kind'=>'instruction','text'=>' '.(CommonMistakesWidgetPlans::INSTRUCTIONS[$id]??'')]]))
                    ||($hintOnly&&(isset($row['display_question'])||isset($row['display_parts'])||$row['accepted_sentences']!==[]||$row['token_labels']!==[]||$row['derived_options']!==[]))
                    ||($row['canonical_server_snapshot']??null)!==self::canonicalSnapshot($row)
                    ||($row['accepted_sentences']??null)!==(CommonMistakesWidgetPlans::ALIASES[$id]??[])||($row['token_labels']??null)!==(CommonMistakesWidgetPlans::LABELS[$id]??[])
                    ||count($row['answers']??[])<2||count($row['correct_tokens']??[])!==count($row['answers']))throw new RuntimeException('Common Mistakes widget exact source differs.');
                if(!is_string($hint)||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Common Mistakes widget guidance must be Ukrainian.');
                foreach($row['token_labels'] as $token=>$caption)if(!in_array($token,$row['correct_tokens'],true)||!is_string($caption)||trim($caption)==='')throw new RuntimeException('Common Mistakes widget token caption differs.');
                foreach($row['answers'] as $a=>$answer)if(($answer['question_id']??null)!==$id||($answer['marker']??null)!=='a'.($a+1)||!is_int($answer['id']??null)||!is_int($answer['option_id']??null)||!is_string($answer['option']??null))throw new RuntimeException('Common Mistakes widget answer instances differ.');
                $seen[$id]=true;
            }
        }
        if(count(CommonMistakesWidgetPlans::HINTS)!==30||count(CommonMistakesWidgetPlans::INSTRUCTIONS)!==25)throw new RuntimeException('Common Mistakes widget hints need all30exactrows and25condition plans.');
    }

    public static function load():array
    {
        $path=base_path(self::SOURCE);
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Common Mistakes widget immutable source differs.');
        static $document;if($document===null){$document=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($document);}return $document;
    }
    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||!$question instanceof Question||(string)$question->type!=='4'||($payload['type']??null)!=='4')return $payload;
        try{foreach(self::load()['questions'] as $row){if($blockUuid!==$row['block_uuid']||$question->id!==$row['id'])continue;
            $state=self::prepared($question);$original=array_intersect_key($row,array_flip(self::FIELDS));
            if(($state!==$original&&$state!==$row['canonical_server_snapshot'])||($payload['id']??null)!==$state['id']||($payload['level']??null)!==$state['level']||($payload['question']??null)!==$state['payload_question']||($payload['correct_tokens']??null)!==$state['correct_tokens']||($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$state['answers'])||!is_array($payload['options']??null))return $payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($state['correct_tokens']) as $token=>$count)if(($available[$token]??0)<$count)return $payload;
            if(($row['hint_only']??false)===true){$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];$payload['common_mistakes_widget_hint_revision']=CommonMistakesPracticeSupplement::digest($row);return $payload;}
            $labels=array_intersect_key($row['token_labels'],array_fill_keys($state['correct_tokens'],true));
            $payload['question']=$row['display_question'];$payload['compose_prompt_parts']=$row['display_parts'];$payload['compose_prompt_revision']=self::SOURCE_SHA;$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];
            if($row['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$state['correct_tokens'])],$payload['accepted_sentences']??[],$row['accepted_sentences'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$labels);
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$row['derived_options'])));
            $payload['common_mistakes_widget_hint_revision']=CommonMistakesPracticeSupplement::digest($row);return $payload;
        }}catch(Throwable){}return $payload;
    }
}
