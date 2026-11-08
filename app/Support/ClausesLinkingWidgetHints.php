<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Exact render-only guidance for the existing thematic widgets; stored question IDs/answers remain unchanged. */
final class ClausesLinkingWidgetHints
{
    public const SOURCE='docs/content/clauses-linking-widget-hints.v1.json';
    public const SOURCE_SHA='1591191d1e9f9605eccb2b3c66a6516b579f9786a2484a381e27d923d4ed68c7';
    public const FIELDS=['id','uuid','seeder','type','level','category_id','source_id','difficulty','flag','theory_text_block_uuid','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
    // The actual optional-punctuation bank renders this exact answer instance
    // as "bike". Keep the frozen raw key too; no general token normalizer.
    private const TOKEN_DISPLAY_KEYS=[44665=>['bike,'=>'bike']];
    public const PRESENTATION_SOURCES=[
        'app/Support/M62BuilderSelectionPresentation.php'=>'aa549f10ade64f7383c6c7c5928c01f24e54b24ec2dad8b9a9fcf55ac64f7773',
        'app/Support/M63BuilderSelectionPresentation.php'=>'21c89da48c3ee78b4f35b3e13dc9e83e2c416c53002f26c427de5c4fdb6aab6e',
        'app/Support/M64BuilderSelectionPresentation.php'=>'9e857d85a0eaa20e0d84926d4a547a816fa71c1f412d2d7ccebc60d701dde630',
        'app/Support/M65BuilderSelectionPresentation.php'=>'717df5288d50250c53e40d125f80523d50c9b8080f2ce56545ef7e3a67525f13',
        'app/Support/M94BuilderSelectionPresentation.php'=>'9783c49c2da09aaba05b7837bc62a83b7c77f45bfc83a748841d28aac04e5e43',
        'docs/content/m62-reported-review-relative-basics-bank-audit.v1.json'=>M62BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m63-relative-completion-linking-bank-audit.v1.json'=>M63BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m64-reason-result-contrast-bank-audit.v1.json'=>M64BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m65-purpose-gerund-infinitive-bank-audit.v1.json'=>M65BuilderSelectionPresentation::AUDIT_SHA,
        'docs/content/m94-sentence-builder-bank-audit.v1.json'=>M94BuilderSelectionPresentation::AUDIT_SHA,
    ];

    /** Only the two independently pinned complete prepared states are accepted. */
    private static function canonicalSnapshot(array $row):array
    {
        static $checked=false;if(!$checked){foreach(self::PRESENTATION_SOURCES as $file=>$hash)if(!is_file(base_path($file))||is_link(base_path($file))||!hash_equals($hash,hash_file('sha256',base_path($file))))throw new RuntimeException('Clauses strict binder source differs.');$checked=true;}
        $pins=ClausesLinkingWidgetPlans::SNAPSHOTS[$row['id']]??null;$raw=array_intersect_key($row,array_flip(self::FIELDS));$post=$row['canonical_server_snapshot']??null;
        if(!is_array($pins)||count($pins)!==2||!is_array($post)||!hash_equals($pins[0],M26DetailPackage::digest($raw))||!hash_equals($pins[1],M26DetailPackage::digest($post)))throw new RuntimeException('Clauses complete raw or post-binder snapshot differs.');return $post;
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
        $native=ClausesLinkingPracticeSupplement::load()['targets'];
        if(($doc['schema_version']??null)!==1||($doc['package']??null)!=='clauses-linking-native-widget-hints'
            ||($doc['source']??null)!==['path'=>ClausesLinkingPracticeSupplement::SOURCE,'sha256'=>ClausesLinkingPracticeSupplement::SOURCE_SHA]
            ||($doc['presentation_sources']??null)!==self::PRESENTATION_SOURCES
            ||array_column($doc['targets']??[],'topic')!==array_column($native,'topic')||count($doc['questions']??[])!==85)throw new RuntimeException('Clauses and Linking Words widget scope differs.');
        $offset=0;$seen=[];
        foreach($doc['targets'] as $i=>$target){
            $expected=$native[$i];$banks=$expected['before_data']['linked_practice']['seeder_classes'];
            if(($target['binding']??null)!==$expected['binding']||($target['local_url']??null)!==$expected['local_url']||($target['bank_seeders']??null)!==$banks||count($target['selected_ids']??[])!==5||count(array_unique($target['selected_ids']))!==5)throw new RuntimeException('Clauses and Linking Words widget target differs.');
            foreach($target['selected_ids'] as $id){
                $row=$doc['questions'][$offset++];$hint=ClausesLinkingWidgetPlans::HINTS[$id]??null;
                if(($row['id']??null)!==$id||isset($seen[$id])||($row['block_uuid']??null)!==$target['binding']['uuid']||($row['type']??null)!=='4'
                    ||($row['level']??null)!==$target['binding']['level']||!in_array($row['seeder']??null,$banks,true)
                    ||($row['hint_uk']??null)!==$hint||($row['display_question']??null)!==(ClausesLinkingWidgetPlans::CONDITIONS[$id]??$row['payload_question']).' '.(ClausesLinkingWidgetPlans::INSTRUCTIONS[$id]??'')
                    ||($row['display_parts']??null)!==[['kind'=>'sentence','text'=>ClausesLinkingWidgetPlans::CONDITIONS[$id]??$row['payload_question']],['kind'=>'instruction','text'=>' '.(ClausesLinkingWidgetPlans::INSTRUCTIONS[$id]??'')]]
                    ||($row['canonical_server_snapshot']??null)!==self::canonicalSnapshot($row)
                    ||($row['accepted_sentences']??null)!==(ClausesLinkingWidgetPlans::ALIASES[$id]??[])||($row['token_labels']??null)!==(ClausesLinkingWidgetPlans::LABELS[$id]??[])
                    ||count($row['answers']??[])<2||count($row['correct_tokens']??[])!==count($row['answers']))throw new RuntimeException('Clauses and Linking Words widget exact source differs.');
                if(!is_string($hint)||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Clauses and Linking Words widget guidance must be Ukrainian.');
                foreach($row['token_labels'] as $token=>$caption)if(!in_array($token,$row['correct_tokens'],true)||!is_string($caption)||trim($caption)==='')throw new RuntimeException('Clauses and Linking Words widget token caption differs.');
                foreach($row['answers'] as $a=>$answer)if(($answer['question_id']??null)!==$id||($answer['marker']??null)!=='a'.($a+1)||!is_int($answer['id']??null)||!is_int($answer['option_id']??null)||!is_string($answer['option']??null))throw new RuntimeException('Clauses and Linking Words widget answer instances differ.');
                $seen[$id]=true;
            }
        }
        if(count(ClausesLinkingWidgetPlans::HINTS)!==85||count(ClausesLinkingWidgetPlans::INSTRUCTIONS)!==85)throw new RuntimeException('Clauses and Linking Words widget hints need all85exactrows.');
    }

    public static function load():array
    {
        $path=base_path(self::SOURCE);
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Clauses and Linking Words widget immutable source differs.');
        static $document;if($document===null){$document=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($document);}return $document;
    }
    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||!$question instanceof Question||(string)$question->type!=='4'||($payload['type']??null)!=='4')return $payload;
        try{foreach(self::load()['questions'] as $row){if($blockUuid!==$row['block_uuid']||$question->id!==$row['id'])continue;
            $state=self::prepared($question);$original=array_intersect_key($row,array_flip(self::FIELDS));
            if(($state!==$original&&$state!==$row['canonical_server_snapshot'])||($payload['id']??null)!==$state['id']||($payload['level']??null)!==$state['level']||($payload['question']??null)!==$state['payload_question']||($payload['correct_tokens']??null)!==$state['correct_tokens']||($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$state['answers'])||!is_array($payload['options']??null))return $payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($state['correct_tokens']) as $token=>$count)if(($available[$token]??0)<$count)return $payload;
            $labels=array_intersect_key($row['token_labels'],array_fill_keys($state['correct_tokens'],true));
            foreach(self::TOKEN_DISPLAY_KEYS[$row['id']]??[] as $raw=>$key){
                if(!array_key_exists($raw,$labels)||$key===''||!is_array($payload['token_labels']??[])
                    ||(array_key_exists($key,$labels)&&$labels[$key]!==$labels[$raw])
                    ||(array_key_exists($key,$payload['token_labels']??[])&&$payload['token_labels'][$key]!==$labels[$raw]))return $payload;
                $labels[$key]=$labels[$raw];
            }
            $payload['question']=$row['display_question'];$payload['compose_prompt_parts']=$row['display_parts'];$payload['compose_prompt_revision']=self::SOURCE_SHA;$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];
            if($row['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$state['correct_tokens'])],$payload['accepted_sentences']??[],$row['accepted_sentences'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$labels);
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$row['derived_options'])));
            $payload['clauses_linking_widget_hint_revision']=ClausesLinkingPracticeSupplement::digest($row);return $payload;
        }}catch(Throwable){}return $payload;
    }
}
