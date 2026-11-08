<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Frozen exact canonical-widget teaching overlay. Never selects, inserts or rewrites a question. */
final class PassiveVoiceWidgetHints
{
    public const SOURCE='docs/content/passive-voice-widget-hints.v1.json';
    public const SOURCE_SHA='090795fad9d0c21add94d5f42e8322e7f6e60783d4f775df55888e36e7995adb';
    public const CONDITIONS=[
        18283=>'Ми організували переписування звіту.',
        18320=>'Які документи, як повідомляють, загубили?',
        18310=>'Їй учора підстригли волосся за її замовленням?',
    ];
    public const CAPTIONS=[17715=>['Students'=>'students'],18920=>['Warning'=>'warning']];

    /** Replay the existing component's answer preparation, not an alternate answer authority. */
    public static function prepared(Question $question): array
    {
        if (!$question->relationLoaded('answers') || !$question->relationLoaded('options') || !$question->relationLoaded('hints')) {
            throw new RuntimeException('Passive widget capture requires eager relations.');
        }
        $answers=[];
        foreach ($question->answers->sort(fn($a,$b)=>strnatcasecmp($a->marker,$b->marker)) as $answer) {
            if (!$answer->relationLoaded('option')) { throw new RuntimeException('Passive widget capture requires eager answer options.'); }
            $answers[]=['id'=>$answer->id,'question_id'=>$answer->question_id,'marker'=>$answer->marker,'position'=>$answer->position,
                'option_id'=>$answer->option_id,'option'=>$answer->option->option];
        }
        $tokens=array_values(array_filter(array_column($answers,'option')));
        $source=PresentSimplePassivePracticeQuality::questions()[$question->uuid]
            ??PastSimplePassivePracticeQuality::questions()[$question->uuid]
            ??FutureSimplePassivePracticeQuality::questions()[$question->uuid]
            ??FutureSimplePassivePracticeQuality::widgetSource($question);
        $source=$source && $source['question']===$question->question?$source:null;
        if ($source) { $tokens=array_column($source['markers'],'answer'); }
        $tokens=LocalizedComposeText::normalizeTokens($question,$tokens);
        $options=$question->options->map(fn($o)=>['id'=>$o->id,'option'=>$o->option])->sortBy('id')->values()->all();
        return ['id'=>$question->id,'uuid'=>$question->uuid,'seeder'=>$question->seeder,'type'=>(string)$question->type,
            'level'=>$question->level,'category_id'=>$question->category_id,'source_id'=>$question->source_id,
            'question'=>$question->question,'payload_question'=>LocalizedComposeText::source($question,'uk'),
            'answers'=>$answers,'correct_tokens'=>$tokens,'option_snapshot'=>$options];
    }

    public static function validate(array $document): void
    {
        $native=PassiveVoicePracticeSupplement::load()['targets'];
        if (($document['schema_version']??null)!==1 || ($document['package']??null)!=='passive-voice-native-widget-hints'
            || ($document['source']??null)!==['path'=>PassiveVoicePracticeSupplement::SOURCE,'sha256'=>PassiveVoicePracticeSupplement::SOURCE_SHA]
            || array_column($document['targets']??[],'topic')!==PassiveVoiceNativePlans::TOPICS || count($document['questions']??[])!==135) {
            throw new RuntimeException('Passive widget exact source/scope differs.');
        }
        $seen=[]; $offset=0;
        foreach ($document['targets'] as $i=>$target) {
            $expected=$native[$i];
            if (($target['binding']??null)!==$expected['binding'] || ($target['local_url']??null)!==$expected['local_url']
                || ($target['bank_seeders']??null)!==$expected['before_data']['linked_practice']['seeder_classes']
                || count($target['selected_ids']??[])!==5 || count(array_unique($target['selected_ids']))!==5) {
                throw new RuntimeException('Passive widget owner/bank/selection differs.');
            }
            foreach ($target['selected_ids'] as $n=>$id) {
                $row=$document['questions'][$offset++]; $hint=PassiveVoiceNativePlans::widgetHints()[$target['topic']][$n];
                if (($row['id']??null)!==$id || isset($seen[$id]) || ($row['block_uuid']??null)!==$target['binding']['uuid']
                    || ($row['type']??null)!=='4' || ($row['level']??null)!==$target['binding']['level']
                    || !in_array($row['seeder']??null,$target['bank_seeders'],true) || ($row['hint_uk']??null)!==$hint
                    || ($row['display_question']??null)!==(self::CONDITIONS[$id]??$row['payload_question'])
                    || ($row['token_labels']??null)!==(self::CAPTIONS[$id]??[]) || count($row['answers']??[])<2
                    || count($row['correct_tokens']??[])!==count($row['answers'])) {
                    throw new RuntimeException('Passive widget question teaching binding differs.');
                }
                if (!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint) || preg_match('/[A-Za-z<>]|\{a\d+\}|&(?:[a-z]+|#\d+);/iu',$hint)) {
                    throw new RuntimeException('Passive widget hint must be plain Ukrainian.');
                }
                foreach ($row['answers'] as $a=>$answer) {
                    if (($answer['question_id']??null)!==$id || ($answer['marker']??null)!=='a'.($a+1)
                        || !is_int($answer['id']??null) || !is_int($answer['option_id']??null) || !is_string($answer['option']??null)) {
                        throw new RuntimeException('Passive widget answer instances differ.');
                    }
                }
                $seen[$id]=true;
            }
        }
    }

    public static function load(): array
    {
        $path=dirname(__DIR__,2).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))) { throw new RuntimeException('Passive widget immutable source bytes differ.'); }
        static $document;
        if ($document===null) { $candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR); self::validate($candidate); $document=$candidate; }
        return $document;
    }

    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if (!$canonicalCaller || app()->getLocale()!=='uk' || !$question instanceof Question || (string)$question->type!=='4'
            || ($payload['type']??null)!=='4' || !$question->relationLoaded('answers') || !$question->relationLoaded('options') || !$question->relationLoaded('hints')) { return $payload; }
        try {
            foreach (self::load()['questions'] as $row) {
                if ($blockUuid!==$row['block_uuid'] || $question->id!==$row['id']) { continue; }
                foreach (['uuid','seeder','level','category_id','source_id','question'] as $field) {
                    if ($question->$field!==$row[$field]) { return $payload; }
                }
                if (($payload['id']??null)!==$row['id'] || ($payload['level']??null)!==$row['level']
                    || ($payload['question']??null)!==$row['payload_question'] || ($payload['correct_tokens']??null)!==$row['correct_tokens']
                    || ($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$row['answers'])
                    || !is_array($payload['options']??null) || self::prepared($question)!==array_intersect_key($row,array_flip(['id','uuid','seeder','type','level','category_id','source_id','question','payload_question','answers','correct_tokens','option_snapshot']))) { return $payload; }
                $available=array_count_values(array_filter($payload['options'],'is_string'));
                foreach (array_count_values($row['correct_tokens']) as $token=>$count) {
                    if (($available[$token]??0)<$count) { return $payload; }
                }
                $payload['question']=$row['display_question'];
                $payload['token_labels']=array_replace($payload['token_labels']??[],$row['token_labels']);
                $existing=$payload['compose_preanswer_hint']??null;
                if (!is_string($existing) || trim($existing)==='' || preg_match('/[A-Za-z<>]|\{a\d+\}/u',$existing)) {
                    $payload['source_bound_compose_hint']=true; $payload['compose_preanswer_hint']=$row['hint_uk'];
                }
                $payload['pv_widget_hint_revision']=PassiveVoicePracticeSupplement::digest($row);
                return $payload;
            }
        } catch (Throwable) { /* Unknown sources, incomplete clones and noncanonical callers retain the entire old payload. */ }
        return $payload;
    }
}
