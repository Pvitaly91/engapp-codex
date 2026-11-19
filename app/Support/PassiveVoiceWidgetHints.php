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

    // Exact projection of both targets[].binding.uuid/selected_ids and questions[].
    // The membership contract is deliberately bound to these reviewed SOURCE bytes.
    private const MEMBERSHIP_FINGERPRINT = '2bb4dc563c97fd8e4cab5d950c9bc82b56a854a8476a7c450733587eb8a49039';
    private const MEMBERSHIP = [
        'source_sha' => '090795fad9d0c21add94d5f42e8322e7f6e60783d4f775df55888e36e7995adb',
        'pairs' => [
            'bed2f132-8884-5ec8-a108-23bb1f7ba151' => [240, 241, 242, 243, 244],
            'c0da83d7-4b44-5b6b-8a15-35c8f52bcb9a' => [16659, 16660, 16661, 16662, 16663],
            '6b35d525-4d00-5c07-afdf-10e0945fbb11' => [37371, 37372, 37373, 37374, 37375],
            '8bd5f10d-f5ee-5cbc-9703-59d2ce80d57f' => [36243, 36244, 36245, 36246, 36247],
            'b98b2d83-ea88-5a72-aac1-9b94dd1a74be' => [59676, 59677, 59678, 37743, 37744],
            'dfead706-e540-5323-b5b0-7550047e1c72' => [38403, 38404, 38405, 38406, 38407],
            '34c75a6d-baf4-5a3c-b65d-3e3ea62d06ae' => [37239, 37240, 37241, 37242, 37243],
            'caa7f817-8da7-55e2-b440-b9fe44510d63' => [37311, 37312, 37313, 37314, 37315],
            '393cd4a7-ff40-5825-84d8-43d9339e6be7' => [37887, 37888, 37889, 37890, 37891],
            '45b3390b-c647-575c-b15e-6020f75d3e49' => [37959, 37960, 37961, 37962, 37963],
            '399ac7bd-9a4b-596b-a33a-78c4de0ba368' => [37815, 37816, 37817, 37818, 37819],
            'a3b3c3c3-4047-5794-a13e-2db18b57bf24' => [39123, 39125, 39126, 39128, 39130],
            '99dec4ab-3b87-582f-8d0b-9157a8cd1877' => [38979, 38980, 38981, 38982, 38983],
            '84e6989a-9bec-5d3d-bfa6-f6d057277868' => [38247, 38250, 38251, 38252, 38256],
            'a564e5bd-92c4-5d7a-9c44-4a31fbf1318e' => [51651, 51653, 51656, 51657, 51658],
            '7ea4ed25-1826-5b16-88b2-7edcc4a595b9' => [59704, 59705, 59706, 59707, 59708],
            '4990c3a9-12f3-5228-b52f-145c2b747389' => [59699, 59700, 59701, 59702, 59703],
            'b867786b-039e-51aa-b9a2-25c365359d0d' => [59684, 59685, 59686, 59687, 59688],
            'deeaa567-4e1c-5648-96a2-94c8be3c26c4' => [59694, 59695, 59696, 59697, 59698],
            '14bdb806-7325-5ec6-bab4-8f857a1f1769' => [36075, 36076, 36077, 36078, 36079],
            'b7fd0912-27ae-5b7a-a5b1-f3d3460bb077' => [59679, 59680, 59681, 59682, 59683],
            '0ad11aa3-b2db-5418-a20e-99e3f9dbfc29' => [59689, 59690, 59691, 59692, 59693],
            'd70401dc-cefd-5b86-af69-084629f63663' => [36003, 36004, 36005, 36006, 36007],
            'e4e8af7f-804d-5e1b-b1e0-b2c79b12e9e5' => [17064, 17066, 17042, 17031, 38767],
            'e5ccb508-fb43-50df-a335-9a3b75ad1d59' => [17743, 17722, 17724, 17718, 17715],
            '44270234-70cc-59ff-a74c-92cd3ade6457' => [18283, 18320, 18287, 18315, 18310],
            '64c03f29-3a4a-5699-ab21-78121dd8d22a' => [18920, 18916, 18897, 18926, 18924],
        ],
    ];

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

    private static function checkedSourcePath(): string
    {
        $path=dirname(__DIR__,2).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA,hash_file('sha256',$path))) { throw new RuntimeException('Passive widget immutable source bytes differ.'); }
        return $path;
    }

    private static function loadChecked(string $path): array
    {
        static $document;
        if ($document===null) { $candidate=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR); self::validate($candidate); $document=$candidate; }
        return $document;
    }

    public static function load(): array
    {
        return self::loadChecked(self::checkedSourcePath());
    }

    private static function verifiedMembership(mixed $registry): ?array
    {
        if (!is_array($registry) || array_keys($registry) !== ['source_sha', 'pairs']
            || $registry['source_sha'] !== self::SOURCE_SHA || !is_array($registry['pairs'])) {
            return null;
        }

        // Cover types and order too, not just cardinality or owner names.
        $bytes = json_encode($registry, JSON_PRESERVE_ZERO_FRACTION);
        return is_string($bytes) && hash_equals(self::MEMBERSHIP_FINGERPRINT, hash('sha256', $bytes))
            ? $registry['pairs'] : null;
    }

    private static function isProvenForeign(?string $blockUuid, mixed $questionId): bool
    {
        if ($blockUuid === null || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $blockUuid)
            || !is_int($questionId) || $questionId <= 0) {
            return false;
        }

        $pairs = self::verifiedMembership(self::MEMBERSHIP);
        if ($pairs === null || isset($pairs[$blockUuid])) {
            return false; // Owned and partial owner matches take the full path.
        }
        foreach ($pairs as $ids) {
            if (in_array($questionId, $ids, true)) {
                return false; // Partial question match: don't skip validation.
            }
        }

        return true;
    }

    public static function project(object $question,?string $blockUuid,array $payload,bool $canonicalCaller): array
    {
        if (!$canonicalCaller || app()->getLocale()!=='uk' || !$question instanceof Question || (string)$question->type!=='4'
            || ($payload['type']??null)!=='4' || !$question->relationLoaded('answers') || !$question->relationLoaded('options') || !$question->relationLoaded('hints')) { return $payload; }
        try {
            $path = self::checkedSourcePath();
            if (get_class($question) === Question::class && ($payload['id'] ?? null) === $question->id
                && self::isProvenForeign($blockUuid, $question->id)) {
                return $payload;
            }
            foreach (self::loadChecked($path)['questions'] as $row) {
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
