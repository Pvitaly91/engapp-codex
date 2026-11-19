<?php

namespace App\Support;

use App\Models\Question;
use App\Models\TextBlock;
use RuntimeException;
use Throwable;
use WeakMap;

/** Exact canonical-widget content and one corrected thematic-bank binding; never rewrites stored questions. */
final class ModalVerbsWidgetHints
{
    public const SOURCE='docs/content/modal-verbs-widget-hints.v1.json';
    public const SOURCE_SHA='0fa79e0f30426de7f6b343d19be25175c2502498ccd6bf31e93b97cf31d3d84d';
    public const NEED_BANK='Database\\Seeders\\V3\\Polyglot\\PolyglotNeedNeedntDontHaveToAllLevelsLessonSeeder';
    public const NEED_SELECTED=[25779,25780,25781,25782,25788];
    public const NEED_EXCLUDED=[25783,25784,25785,25786,25787];
    public const LEGACY_CONTRACT='docs/content/theory-sentence-builders/m50-obligation-advice.v1.json';
    public const LEGACY_CONTRACT_SHA='582522782fc737825159bb2151d7f41526e0e2b23e2fe495ab4cecdb4a7933c9';
    public const CONTRACT='docs/content/theory-sentence-builders/revisions/modal-verbs-obligation-advice.v2.json';
    public const CONTRACT_SHA='44cd99215847858ef9040ad11752fc692fcf69fa8b8f2d3b7192f95c4041e8e4';
    private static ?WeakMap $matchingClones=null;

    /** Same answer preparation as the established widget, with exact eager relation snapshots. */
    public static function prepared(Question $question): array
    {
        foreach(['answers','options','hints','verbHints'] as $relation)if(!$question->relationLoaded($relation))throw new RuntimeException('Modal widget capture requires complete eager relations.');
        $answers=[];foreach($question->answers->sort(fn($a,$b)=>strnatcasecmp($a->marker,$b->marker)) as $answer){
            if(!$answer->relationLoaded('option'))throw new RuntimeException('Modal widget answer option missing.');
            $answers[]=['id'=>$answer->id,'question_id'=>$answer->question_id,'marker'=>$answer->marker,'position'=>$answer->position,'option_id'=>$answer->option_id,'option'=>$answer->option->option];
        }
        $tokens=LocalizedComposeText::normalizeTokens($question,array_values(array_filter(array_column($answers,'option'))));
        $options=$question->options->map(fn($o)=>['id'=>$o->id,'option'=>$o->option])->sortBy('id')->values()->all();
        $hints=$question->hints->map(fn($h)=>['id'=>$h->id,'provider'=>$h->provider,'locale'=>$h->locale,'hint'=>$h->hint])->sortBy('id')->values()->all();
        $verb=[];foreach($question->verbHints as $hint){if(!$hint->relationLoaded('option'))throw new RuntimeException('Modal widget verb hint option missing.');$verb[]=['id'=>$hint->id,'marker'=>$hint->marker,'locale'=>$hint->locale,'option_id'=>$hint->option_id,'option'=>$hint->option->option];}
        usort($verb,static fn($a,$b)=>$a['id']<=>$b['id']);
        return ['id'=>$question->id,'uuid'=>$question->uuid,'seeder'=>$question->seeder,'type'=>(string)$question->type,'level'=>$question->level,'category_id'=>$question->category_id,'source_id'=>$question->source_id,'question'=>$question->question,'options_by_marker'=>$question->getRawOriginal('options_by_marker'),'payload_question'=>LocalizedComposeText::source($question,'uk'),'answers'=>$answers,'correct_tokens'=>$tokens,'option_snapshot'=>$options,'hints'=>$hints,'verb_hints'=>$verb];
    }

    public static function validate(array $doc): void
    {
        $native=ModalVerbsPracticeSupplement::load()['targets'];
        if(($doc['schema_version']??null)!==1||($doc['package']??null)!=='modal-verbs-native-widget-hints'||($doc['source']??null)!==['path'=>ModalVerbsPracticeSupplement::SOURCE,'sha256'=>ModalVerbsPracticeSupplement::SOURCE_SHA]||array_column($doc['targets']??[],'topic')!==array_column($native,'topic')||count($doc['questions']??[])!==50)throw new RuntimeException('Modal widget exact scope differs.');
        $seen=[];$offset=0;
        foreach($doc['targets'] as $i=>$target){$expected=$native[$i];$need=$target['topic']==='need-neednt-dont-have-to';$banks=$need?[self::NEED_BANK]:$expected['before_data']['linked_practice']['seeder_classes'];
            if(($target['binding']??null)!==$expected['binding']||($target['local_url']??null)!==$expected['local_url']||($target['bank_seeders']??null)!==$banks||count($target['selected_ids']??[])!==5||count(array_unique($target['selected_ids']))!==5||($need&&$target['selected_ids']!==self::NEED_SELECTED))throw new RuntimeException('Modal widget target or thematic bank differs.');
            foreach($target['selected_ids'] as $n=>$id){$row=$doc['questions'][$offset++];$hint=ModalVerbsNativePlans::widgetHints()[$target['topic']][$n];
                if(($row['id']??null)!==$id||isset($seen[$id])||($row['block_uuid']??null)!==$target['binding']['uuid']||($row['type']??null)!=='4'||($row['level']??null)!==$target['binding']['level']||!in_array($row['seeder']??null,$banks,true)||($row['hint_uk']??null)!==$hint||($row['display_question']??null)!==(ModalVerbsNativePlans::WIDGET_CONDITIONS[$id]??$row['payload_question'])||($row['accepted_sentences']??null)!==(ModalVerbsNativePlans::WIDGET_ALIASES[$id]??[])||($row['token_labels']??null)!==(ModalVerbsNativePlans::WIDGET_TOKEN_LABELS[$id]??[])||($row['derived_options']??null)!==(ModalVerbsNativePlans::WIDGET_DERIVED_OPTIONS[$id]??[])||count($row['answers']??[])<2||count($row['correct_tokens']??[])!==count($row['answers']))throw new RuntimeException('Modal widget question binding differs.');
                if(!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Modal widget hint must be Ukrainian.');
                foreach($row['answers'] as $a=>$answer)if(($answer['question_id']??null)!==$id||($answer['marker']??null)!=='a'.($a+1)||!is_int($answer['id']??null)||!is_int($answer['option_id']??null)||!is_string($answer['option']??null))throw new RuntimeException('Modal widget answer instances differ.');
                $seen[$id]=true;
            }
            if($need){
                if(array_column($target['excluded_questions']??[],'id')!==self::NEED_EXCLUDED||count(array_unique(array_column($target['excluded_questions'],'uuid')))!==5)throw new RuntimeException('Modal need excluded teaching sources differ.');
                foreach($target['excluded_questions'] as $row)if(($row['seeder']??null)!==self::NEED_BANK||($row['level']??null)!=='A2'||($row['type']??null)!=='4'||($row['sha256']??null)!==ModalVerbsPracticeSupplement::digest(array_diff_key($row,['sha256'=>true])))throw new RuntimeException('Modal need exclusions require exact source hashes.');
            }
        }
    }

    public static function load(): array
    {
        $path=base_path(self::SOURCE);if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Modal widget immutable source differs.');
        static $document;if($document===null){$document=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($document);}return $document;
    }

    public static function contractFile(string $root,string $file): string
    {
        if(str_replace('\\','/',$file)!==rtrim(str_replace('\\','/',$root),'/').'/'.self::LEGACY_CONTRACT)return $file;
        $next=rtrim($root,'/\\').'/'.self::CONTRACT;
        foreach([$file=>self::LEGACY_CONTRACT_SHA,$next=>self::CONTRACT_SHA] as $path=>$sha)if(!is_file($path)||is_link($path)||!hash_equals($sha,hash_file('sha256',$path)))throw new RuntimeException('Modal exact builder contract predecessor/successor differs.');
        return $next;
    }

    private static function needTarget(TextBlock $block,bool $matching=false): ?array
    {
        if(app()->getLocale()!=='uk'||$block->seeder!==(ModalVerbsPracticeSource::OWNERS['need-neednt-dont-have-to']))return null;
        try{foreach(self::load()['targets'] as $target){if($target['topic']!=='need-neednt-dont-have-to')continue;
            foreach($target['binding'] as $field=>$expected)if($field!=='body_sha256'&&$block->$field!==$expected)return null;
            $sha=$matching?$target['matching_body_sha256']:$target['binding']['body_sha256'];if(hash('sha256',(string)$block->body)!==$sha)return null;return $target;
        }}catch(Throwable){}return null;
    }

    public static function matchingBlock(TextBlock $block,bool $canonicalTheory): TextBlock
    {
        if(!$canonicalTheory||self::needTarget($block)===null)return $block;
        $data=json_decode($block->body,true,flags:JSON_THROW_ON_ERROR);$data['linked_practice']['seeder_classes']=[self::NEED_BANK];$copy=clone $block;
        $copy->setRawAttributes(array_replace($block->getAttributes(),['body'=>M26DetailPackage::json($data)]),true);self::$matchingClones??=new WeakMap;self::$matchingClones[$copy]=true;return $copy;
    }

    public static function exclusions(TextBlock $block,array $config,array $caller): array
    {
        if(self::$matchingClones===null||!isset(self::$matchingClones[$block])||self::needTarget($block,true)===null||$config!==['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>[self::NEED_BANK]]||array_intersect($caller,self::NEED_SELECTED)!==[])return $caller;
        foreach(self::NEED_EXCLUDED as $id)if(!in_array($id,$caller,true))$caller[]=$id;return $caller;
    }

    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||!$question instanceof Question||(string)$question->type!=='4'||($payload['type']??null)!=='4')return $payload;
        try{foreach(self::load()['questions'] as $row){if($blockUuid!==$row['block_uuid']||$question->id!==$row['id'])continue;
            $fields=['id','uuid','seeder','type','level','category_id','source_id','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
            if(self::prepared($question)!==array_intersect_key($row,array_flip($fields))||($payload['id']??null)!==$row['id']||($payload['level']??null)!==$row['level']||($payload['question']??null)!==$row['payload_question']||($payload['correct_tokens']??null)!==$row['correct_tokens']||($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$row['answers'])||!is_array($payload['options']??null))return $payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($row['correct_tokens']) as $token=>$count)if(($available[$token]??0)<$count)return $payload;
            $payload['question']=$row['display_question'];$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];
            if($row['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$row['correct_tokens'])],$payload['accepted_sentences']??[],$row['accepted_sentences'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$row['token_labels']);
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$row['derived_options'])));
            $payload['modal_widget_hint_revision']=ModalVerbsPracticeSupplement::digest($row);return $payload;
        }}catch(Throwable){}return $payload;
    }
}
