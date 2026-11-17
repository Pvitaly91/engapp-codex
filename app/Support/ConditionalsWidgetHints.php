<?php

namespace App\Support;

use App\Models\Question;
use RuntimeException;
use Throwable;

/** Exact render-only guidance for the existing thematic widgets; stored question IDs/answers remain unchanged. */
final class ConditionalsWidgetHints
{
    public const SOURCE='docs/content/conditionals-widget-hints.v1.json';
    public const SOURCE_SHA='710e283af26f52d56eb1ceb04d22d5adc66785a607e0bf727d7525ac9290859c';

    // Generated from the exact frozen targets/questions pair set (see PERF-10 report).
    // This is only a negative membership proof; eligible members still validate the full source.
    private const MEMBERSHIP = [
        'source_sha' => '710e283af26f52d56eb1ceb04d22d5adc66785a607e0bf727d7525ac9290859c',
        'pairs' => [
            '03b70723-7110-5194-bc15-29355a9fe57a' => [28875, 28876, 28877, 28878, 28879],
            '06f78006-775f-551c-994c-7539cadc0220' => [29679, 29680, 29681, 29682, 29683],
            '2cf832f6-8b24-53b5-9c62-9552feaae1fb' => [29607, 29608, 29609, 29610, 29611],
            '386140a6-2c9c-5c1a-9a30-35a089f1ee3c' => [29115, 29116, 29117, 29118, 29119],
            '6e5976e3-22db-59b1-955e-16c0033052eb' => [29823, 29824, 29825, 29826, 29827],
            '6f5bc572-a291-5a11-ad86-2448297123e3' => [18465, 18481, 18487, 18491, 18512],
            '711f83cc-e28d-5e34-a02b-f78846d03787' => [17603, 17611, 17617, 17629, 17643],
            '7640dc2b-5d95-5ea5-bccb-3617854df98e' => [29031, 29032, 29033, 29034, 29035],
            '951b4b66-dc6f-5e1d-b833-f2ab4afc0431' => [29751, 29752, 29753, 29754, 29755],
            'b86091c3-5bdc-55bb-a5c9-5db128088d97' => [29883, 29884, 29885, 29886, 29887],
            'ddee7c10-9260-54d8-8b38-5204ad9f77a3' => [29187, 29188, 29189, 29190, 29191],
            'e1d69f49-298e-56dc-808b-53131f1f2b6b' => [28947, 28948, 28949, 28950, 28951],
            'eae926cf-e017-52af-8120-d6a6914adc09' => [17276, 17290, 17307, 17308, 17310],
        ],
    ];
    private const MEMBERSHIP_FINGERPRINT = 'c17ea272692415dbb8232c2690b76ff230676893eb3ba3ce38cd7b04fd0bafa4';

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


    public static function validate(array $doc):void
    {
        $native=ConditionalsPracticeSupplement::load()['targets'];
        if(($doc['schema_version']??null)!==1||($doc['package']??null)!=='conditionals-native-widget-hints'
            ||($doc['source']??null)!==['path'=>ConditionalsPracticeSupplement::SOURCE,'sha256'=>ConditionalsPracticeSupplement::SOURCE_SHA]
            ||array_column($doc['targets']??[],'topic')!==array_column($native,'topic')||count($doc['questions']??[])!==65)throw new RuntimeException('Conditional widget scope differs.');
        $offset=0;$seen=[];
        foreach($doc['targets'] as $i=>$target){
            $expected=$native[$i];$banks=$expected['before_data']['linked_practice']['seeder_classes'];
            if(($target['binding']??null)!==$expected['binding']||($target['local_url']??null)!==$expected['local_url']||($target['bank_seeders']??null)!==$banks||count($target['selected_ids']??[])!==5||count(array_unique($target['selected_ids']))!==5)throw new RuntimeException('Conditional widget target differs.');
            foreach($target['selected_ids'] as $id){
                $row=$doc['questions'][$offset++];$hint=ConditionalsWidgetPlans::HINTS[$id]??null;
                if(($row['id']??null)!==$id||isset($seen[$id])||($row['block_uuid']??null)!==$target['binding']['uuid']||($row['type']??null)!=='4'
                    ||($row['level']??null)!==$target['binding']['level']||!in_array($row['seeder']??null,$banks,true)
                    ||($row['hint_uk']??null)!==$hint||($row['display_question']??null)!==(ConditionalsWidgetPlans::CONDITIONS[$id]??$row['payload_question'])
                    ||($row['accepted_sentences']??null)!==(ConditionalsWidgetPlans::ALIASES[$id]??[])||($row['token_labels']??null)!==(ConditionalsWidgetPlans::LABELS[$id]??[])
                    ||count($row['answers']??[])<2||count($row['correct_tokens']??[])!==count($row['answers']))throw new RuntimeException('Conditional widget exact source differs.');
                if(!is_string($hint)||!preg_match('/[А-Яа-яІіЇїЄєҐґ]/u',$hint)||preg_match('/[A-Za-z<>]|\{a\d+\}/u',$hint))throw new RuntimeException('Conditional widget guidance must be Ukrainian.');
                foreach($row['token_labels'] as $token=>$caption)if(!in_array($token,$row['correct_tokens'],true)||!is_string($caption)||trim($caption)==='')throw new RuntimeException('Conditional widget token caption differs.');
                foreach($row['answers'] as $a=>$answer)if(($answer['question_id']??null)!==$id||($answer['marker']??null)!=='a'.($a+1)||!is_int($answer['id']??null)||!is_int($answer['option_id']??null)||!is_string($answer['option']??null))throw new RuntimeException('Conditional widget answer instances differ.');
                $seen[$id]=true;
            }
        }
        if(count(ConditionalsWidgetPlans::HINTS)!==65)throw new RuntimeException('Conditional widget hints need all65exactrows.');
    }

    private static function checkedSourcePath(): string
    {
        $path=base_path(self::SOURCE);
        if(!is_file($path)||is_link($path)||!hash_equals(self::SOURCE_SHA,hash_file('sha256',$path)))throw new RuntimeException('Conditional widget immutable source differs.');
        return $path;
    }

    private static function loadChecked(string $path): array
    {
        static $document;if($document===null){$document=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);self::validate($document);}return $document;
    }

    public static function load():array
    {
        return self::loadChecked(self::checkedSourcePath());
    }

    private static function verifiedMembership(mixed $registry): ?array
    {
        if (!is_array($registry) || array_keys($registry) !== ['source_sha', 'pairs']
            || $registry['source_sha'] !== self::SOURCE_SHA || !is_array($registry['pairs'])
            || !defined(self::class.'::MEMBERSHIP_FINGERPRINT')) {
            return null;
        }

        // The fingerprint covers exact keys, list order and types, not only cardinality.
        foreach ($registry['pairs'] as $uuid => $ids) {
            if (!is_string($uuid) || !is_array($ids) || !array_is_list($ids)) {
                return null;
            }
            foreach ($ids as $id) {
                if (!is_int($id)) {
                    return null;
                }
            }
        }
        $bytes = json_encode($registry, JSON_PRESERVE_ZERO_FRACTION);
        $fingerprint = self::MEMBERSHIP_FINGERPRINT;

        return is_string($fingerprint) && is_string($bytes) && hash_equals($fingerprint, hash('sha256', $bytes))
            ? $registry['pairs'] : null;
    }

    private static function isProvenForeign(?string $blockUuid, mixed $questionId): bool
    {
        if ($blockUuid === null || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $blockUuid)
            || !is_int($questionId) || $questionId <= 0 || !defined(self::class.'::MEMBERSHIP')) {
            return false;
        }
        $pairs = self::verifiedMembership(self::MEMBERSHIP);

        return $pairs !== null && !in_array($questionId, $pairs[$blockUuid] ?? [], true);
    }

    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if(!$canonicalCaller||app()->getLocale()!=='uk'||!$question instanceof Question||(string)$question->type!=='4'||($payload['type']??null)!=='4')return $payload;
        try{
            $path = self::checkedSourcePath();
            if (get_class($question) === Question::class && self::isProvenForeign($blockUuid, $question->id)) {
                return $payload;
            }
            foreach(self::loadChecked($path)['questions'] as $row){if($blockUuid!==$row['block_uuid']||$question->id!==$row['id'])continue;
            $fields=['id','uuid','seeder','type','level','category_id','source_id','question','options_by_marker','payload_question','answers','correct_tokens','option_snapshot','hints','verb_hints'];
            if(self::prepared($question)!==array_intersect_key($row,array_flip($fields))||($payload['id']??null)!==$row['id']||($payload['level']??null)!==$row['level']||($payload['question']??null)!==$row['payload_question']||($payload['correct_tokens']??null)!==$row['correct_tokens']||($payload['answers']??null)!==array_map(static fn($a)=>['marker'=>$a['marker'],'correct'=>$a['option']],$row['answers'])||!is_array($payload['options']??null))return $payload;
            $available=array_count_values(array_filter($payload['options'],'is_string'));foreach(array_count_values($row['correct_tokens']) as $token=>$count)if(($available[$token]??0)<$count)return $payload;
            $payload['question']=$row['display_question'];$payload['source_bound_compose_hint']=true;$payload['compose_preanswer_hint']=$row['hint_uk'];
            if($row['accepted_sentences']!==[])$payload['accepted_sentences']=array_values(array_unique(array_merge([implode(' ',$row['correct_tokens'])],$payload['accepted_sentences']??[],$row['accepted_sentences'])));
            $payload['token_labels']=array_replace($payload['token_labels']??[],$row['token_labels']);
            $payload['derived_compose_options']=array_values(array_unique(array_merge($payload['derived_compose_options']??[],$row['derived_options'])));
            $payload['conditionals_widget_hint_revision']=ConditionalsPracticeSupplement::digest($row);return $payload;
        }}catch(Throwable){}return $payload;
    }
}

