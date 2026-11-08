<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;
use WeakMap;

/** Finite M66 selection and condition-only clones; never grades, formats tokens, queries or mutates original models. */
final class M66BuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m66-verb-patterns-core-bank-audit.v2.json';
    public const AUDIT_SHA = 'ad99635d51a6d9bfa3d246e1458f3e18f37e59bd9377ba585969689bc3e87e5e';
    private const TARGETS_SHA = '3d10008932f4509efa8e699137c5f7a129f6a3b782f43afd916de5f44c6d1f9b';
    private const SCOPE = ['selected_questions'=>15,'reused_questions'=>15,'new_questions'=>0,'question_writes'=>0,'option_writes'=>0,'answer_writes'=>0,'option_link_writes'=>0,'link_writes'=>0,'old_hint_writes'=>0,'old_question_updates'=>0,'old_option_updates'=>0,'old_answer_updates'=>0,'grader_changes'=>0,'progress_writes'=>0];
    private static ?WeakMap $canonicalClones = null;

    public static function matchingBlock(TextBlock $block, bool $canonicalTheory): TextBlock
    {
        if (!$canonicalTheory || self::binding($block) === null) { return $block; }
        $copy=clone $block; self::$canonicalClones ??= new WeakMap; self::$canonicalClones[$copy]=true; return $copy;
    }

    public static function exclusions(TextBlock $block, array $config, array $callerExclusions): array
    {
        if (self::$canonicalClones===null || !isset(self::$canonicalClones[$block])) { return $callerExclusions; }
        $target=self::binding($block);
        if ($target===null || $config!==self::config($target)
            || array_intersect($callerExclusions,$target['expected_selected_ids'])!==[]) { return $callerExclusions; }
        foreach ($target['excluded_question_ids'] as $id) { if (!in_array($id,$callerExclusions,true)) { $callerExclusions[]=$id; } }
        return $callerExclusions;
    }

    /** All five original 14-column attributes and loaded answer/option/hint/pivot snapshots must match. */
    public static function bindings(array $bindings): array
    {
        $block=$bindings['block']??null; $questions=$bindings['practiceQuestions']??null;
        if (($bindings['theoryCanonical']??false)!==true || !$block instanceof TextBlock
            || !$questions instanceof Collection || $questions->count()!==5) { return $bindings; }
        try {
            $target=self::binding($block);
            if ($target===null || !self::matchesQuestions($questions,$target)) { return $bindings; }
            $bindings['practiceQuestions']=$questions->values()->map(static function(Question $question,int $i) use($target):Question {
                $copy=clone $question;
                $copy->setRawAttributes(array_replace($question->getAttributes(),['question'=>$target['first_five'][$i]['visible_condition_uk']]),true);
                return $copy;
            });
        } catch (Throwable) { /* Foreign, incomplete or stale data retains the complete original collection. */ }
        return $bindings;
    }

    public static function validateAudit(array $audit,array $patch,array $contract): void
    {
        if (($audit['schema_version']??null)!==1 || ($audit['package']??null)!=='m66-verb-patterns-core-bank-audit'
            || ($audit['status']??null)!=='frozen' || ($audit['version']??null)!=='1.0.1' || ($audit['scope']??null)!==self::SCOPE
            || ($audit['locale']??null)!=='uk' || ($audit['pending']??null)!==[]
            || ($audit['independent_before_sha256']??null)!=='517118d0fe7cf764229b5926e82fa7044b10a92535923a7301bc46602311d6a3'
            || count($audit['targets']??[])!==3 || count($audit['practice_bindings']??[])!==3
            || count($patch['targets']??[])!==3 || count($contract['targets']??[])!==3
            || M26DetailPackage::digest($audit['targets'])!==self::TARGETS_SHA) {
            throw new RuntimeException('M66 frozen finite teaching/bank scope differs.');
        }
        foreach (M66VerbPatternsCorePackage::OWNERS as $i=>$owner) {
            $target=$audit['targets'][$i];$binding=$audit['practice_bindings'][$i];$declaration=$contract['targets'][$i];
            $index=M66VerbPatternsCorePackage::CONTINUING_COUNTS[$i];$source=$patch['targets'][$i]['after']['page']['blocks'][$index]??[];
            if ($target['identity']!==$owner || $target['page_id']!==M66VerbPatternsCorePackage::PAGE_IDS[$i]
                || $target['category_id']!==M66VerbPatternsCorePackage::CATEGORY_IDS[$i]
                || $target['level']!==M66VerbPatternsCorePackage::LEVELS[$i]
                || array_column($target['category_ancestry'],'slug')!==M66VerbPatternsCorePackage::ANCESTRIES[$i]
                || $target['bank_seeders']!==($declaration['bank_seeders']??null) || $declaration['identity']!==$owner
                || $declaration['level']!==$target['level'] || $declaration['local_url']!==$target['local_url']
                || $patch['targets'][$i]['identity']!==$owner || $source['type']!=='practice-set'
                || ($binding['identity']??null)!==$owner || $binding['page_id']!==$target['page_id']
                || $binding['category_id']!==$target['category_id'] || $binding['locale']!=='uk'
                || $binding['source_index']!==$index || $binding['type']!=='practice-set' || $binding['level']!==$target['level']
                || $binding['uuid']!==M26DetailPackage::uuid($owner,$source,$index+1)
                || $binding['column']!==$source['column'] || $binding['heading']!==($source['heading']??null)
                || $binding['css_class']!==($source['css_class']??null) || $binding['body_sha256']!==hash('sha256',$source['body'])) {
                throw new RuntimeException('M66 exact practice/owner/category/level binding differs.');
            }
            $body=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);
            if ($binding['linked_practice']!==($body['linked_practice']??null)
                || array_intersect_key($body['linked_practice']??[],array_flip(['source','question_types','seeder_classes']))!==self::config($target)) {
                throw new RuntimeException('M66 exact thematic bank config differs.');
            }
            $pool=array_merge(...array_values($target['expected_pool_ids_by_bank']));$selected=array_column($target['first_five'],'id');
            $excluded=array_values(array_diff($pool,$selected));sort($excluded);
            if (count($selected)!==5 || count(array_unique($selected))!==5 || $selected!==$target['expected_selected_ids']
                || array_column($target['first_five'],'uuid')!==$target['expected_selected_uuids'] || $excluded!==$target['excluded_question_ids']) {
                throw new RuntimeException('M66 finite five/pool partition differs.');
            }
            foreach ($target['first_five'] as $sample) {
                if ($sample['token_presentations']!==[] || $sample['option_presentations']!==[]
                    || $sample['question_attribute_keys']!==M51QuestionSupplement::ROW_COLUMNS['questions']
                    || ComposeTokenCase::normalize($sample['correct_tokens'])!==$sample['correct_tokens']) {
                    throw new RuntimeException('M66 must use unchanged native token casing; no presentation formatter.');
                }
            }
        }
    }

    public static function loadAudit(?string $root=null): array
    {
        $root??=base_path();$file=$root.'/'.self::AUDIT;
        if (!is_file($file)||is_link($file)||!hash_equals(self::AUDIT_SHA,hash_file('sha256',$file))) {
            throw new RuntimeException('M66 reviewed teaching audit differs.');
        }
        $audit=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
        [, $patch, , $contract]=M66VerbPatternsCorePackage::load($root);
        self::validateAudit($audit,$patch,$contract);return $audit;
    }

    private static function config(array $target): array
    {
        return ['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$target['bank_seeders']];
    }

    private static function binding(TextBlock $block): ?array
    {
        $i=array_search($block->seeder,M66VerbPatternsCorePackage::OWNERS,true);
        if (app()->getLocale()!=='uk'||$i===false||$block->locale!=='uk'||$block->type!=='practice-set'
            ||$block->level!==M66VerbPatternsCorePackage::LEVELS[$i]||$block->page_id!==M66VerbPatternsCorePackage::PAGE_IDS[$i]
            ||$block->page_category_id!==M66VerbPatternsCorePackage::CATEGORY_IDS[$i]) { return null; }
        try {
            $data=json_decode((string)$block->body,true,flags:JSON_THROW_ON_ERROR);
            if (!is_array($data)||M66VerbPatternsCorePackage::presentation($block,$data)===null) { return null; }
            $audit=self::loadAudit();$p=$audit['practice_bindings'][$i];
            if ($block->uuid!==$p['uuid']||$block->sort_order-1!==$p['source_index']||$block->column!==$p['column']
                ||$block->heading!==$p['heading']||$block->css_class!==$p['css_class']||hash('sha256',(string)$block->body)!==$p['body_sha256']) { return null; }
            return $audit['targets'][$i];
        } catch (Throwable) { return null; }
    }

    private static function matchesQuestions(Collection $questions, array $target): bool
    {
        if ($questions->pluck('id')->all() !== $target['expected_selected_ids']
            || $questions->pluck('uuid')->all() !== $target['expected_selected_uuids']) { return false; }
        foreach ($questions->values() as $i => $question) {
            $sample = $target['first_five'][$i];
            if (!$question instanceof Question) { return false; }
            $attributes = $question->getAttributes(); $orderedAttributes = [];
            $expectedKeys = $sample['question_attribute_keys'];
            // Existing explicit-link matcher decorates models after hydration; these are not DB columns.
            $decorations = array_diff_key($attributes, array_flip($expectedKeys));
            if ($decorations !== [] && (array_keys($decorations) !== ['match_score', 'matched_tag_ids', 'marker_tags']
                || $decorations['match_score'] !== null || $decorations['matched_tag_ids'] !== []
                || !is_array($decorations['marker_tags']))) { return false; }
            $keys = array_keys(array_diff_key($attributes, $decorations)); sort($keys); $sorted = $expectedKeys; sort($sorted);
            if ($keys !== $sorted) { return false; }
            foreach ($expectedKeys as $field) { $orderedAttributes[$field] = $attributes[$field]; }
            if (M26DetailPackage::digest($orderedAttributes) !== $sample['snapshot_hashes']['question_snapshot']
                || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || !$question->relationLoaded('hints') || !$question->relationLoaded('verbHints')
                || !$question->getRelation('answers') instanceof Collection || !$question->getRelation('options') instanceof Collection
                || !$question->getRelation('hints') instanceof Collection || !$question->getRelation('verbHints') instanceof Collection
                || $question->getRelation('verbHints')->isNotEmpty()) { return false; }
            $answers = self::answers($question); $options = self::options($question); $pivots = self::optionPivots($question);
            if ($answers === null || $options === null || $pivots === null
                || M26DetailPackage::digest($answers) !== $sample['snapshot_hashes']['ordered_answer_snapshot']
                || M26DetailPackage::digest($options) !== $sample['snapshot_hashes']['original_options_snapshot']
                || M26DetailPackage::digest($pivots) !== $sample['snapshot_hashes']['loaded_option_pivots']
                || M26DetailPackage::digest(self::hints($question)) !== $sample['snapshot_hashes']['hints_snapshot']
                || M26DetailPackage::digest([]) !== $sample['snapshot_hashes']['verb_hints_snapshot']) { return false; }
        }
        return true;
    }

    private static function answers(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('answers') as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option') || $answer->question_id !== $question->id) { return null; }
            $option = $answer->getRelation('option');
            if (!$option instanceof QuestionOption || $option->id !== $answer->option_id) { return null; }
            $rows[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position,
                'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));
        return $rows;
    }

    private static function options(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option instanceof QuestionOption || !is_int($option->id) || !is_string($option->option)) { return null; }
            $rows[] = ['id' => $option->id, 'option' => $option->option];
        }
        usort($rows, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
        return $rows;
    }

    private static function hints(Question $question): array
    {
        return $question->getRelation('hints')->map(static fn ($hint): array => [
            'id' => $hint->id, 'question_id' => $hint->question_id, 'provider' => $hint->provider,
            'locale' => $hint->locale, 'hint' => $hint->hint,
        ])->values()->all();
    }

    /** The existing ORM loads these three pivot fields, not the physical pivot ID; no additional query. */
    private static function optionPivots(Question $question): ?array
    {
        $rows = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option->relationLoaded('pivot')) { return null; }
            $pivot = $option->getRelation('pivot');
            if (!is_object($pivot) || !method_exists($pivot, 'getAttributes')) { return null; }
            $attrs = $pivot->getAttributes();
            if (!array_key_exists('flag', $attrs) || ($attrs['question_id'] ?? null) !== $question->id
                || ($attrs['option_id'] ?? null) !== $option->id) { return null; }
            $rows[] = ['question_id' => $attrs['question_id'], 'option_id' => $attrs['option_id'], 'flag' => $attrs['flag']];
        }
        usort($rows, static fn (array $a, array $b): int => $a['option_id'] <=> $b['option_id']);
        return $rows;
    }

}
