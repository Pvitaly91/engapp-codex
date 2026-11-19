<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use App\Models\VerbHint;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;
use WeakMap;

/** Exact, package-bound selection and condition-only clones; no grading, token formatting, queries or original-model writes. */
abstract class TheoryBuilderSelectionPresentation
{
    /** Trusted PHP package and finite audit policy; never selected from learner content. */
    abstract protected static function packageClass(): string;

    abstract protected static function policy(): array;

    private static ?WeakMap $canonicalClones = null;

    public static function matchingBlock(TextBlock $block, bool $canonicalTheory): TextBlock
    {
        if (!$canonicalTheory || self::binding($block) === null) { return $block; }
        $copy=clone $block; self::$canonicalClones ??= new WeakMap; self::$canonicalClones[$copy]=static::class; return $copy;
    }

    public static function exclusions(TextBlock $block, array $config, array $callerExclusions): array
    {
        if (self::$canonicalClones===null || !isset(self::$canonicalClones[$block])
            || self::$canonicalClones[$block]!==static::class) { return $callerExclusions; }
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
        $package=static::packageClass();$policy=static::policy();$ownerCount=count($package::OWNERS);
        if (($audit['schema_version']??null)!==1 || ($audit['package']??null)!==$policy['package']
            || ($audit['status']??null)!=='frozen' || ($audit['version']??null)!==$policy['version'] || ($audit['scope']??null)!==$policy['scope']
            || ($audit['locale']??null)!=='uk' || ($audit['pending']??null)!==[]
            || ($audit['independent_before_sha256']??null)!==$policy['before_sha256']
            || $ownerCount<1 || count($audit['targets']??[])!==$ownerCount || count($audit['practice_bindings']??[])!==$ownerCount
            || count($patch['targets']??[])!==$ownerCount || count($contract['targets']??[])!==$ownerCount
            || M26DetailPackage::digest($audit['targets'])!==$policy['targets_sha256']) {
            throw new RuntimeException($policy['label'].' frozen finite teaching/bank scope differs.');
        }
        foreach ($package::OWNERS as $i=>$owner) {
            $target=$audit['targets'][$i];$binding=$audit['practice_bindings'][$i];$declaration=$contract['targets'][$i];
            $index=defined($package.'::PRACTICE_SOURCE_INDICES') ? $package::PRACTICE_SOURCE_INDICES[$i] : $package::CONTINUING_COUNTS[$i];
            $source=$patch['targets'][$i]['after']['page']['blocks'][$index]??[];
            if ($target['identity']!==$owner || $target['page_id']!==$package::PAGE_IDS[$i]
                || $target['category_id']!==$package::CATEGORY_IDS[$i]
                || $target['level']!==$package::LEVELS[$i]
                || array_column($target['category_ancestry'],'slug')!==$package::ANCESTRIES[$i]
                || $target['bank_seeders']!==($declaration['bank_seeders']??null) || $declaration['identity']!==$owner
                || $declaration['level']!==$target['level'] || $declaration['local_url']!==$target['local_url']
                || $patch['targets'][$i]['identity']!==$owner || $source['type']!=='practice-set'
                || ($binding['identity']??null)!==$owner || $binding['page_id']!==$target['page_id']
                || $binding['category_id']!==$target['category_id'] || $binding['locale']!=='uk'
                || $binding['source_index']!==$index || $binding['type']!=='practice-set' || $binding['level']!==$target['level']
                || $binding['uuid']!==M26DetailPackage::uuid($owner,$source,$index+1)
                || $binding['column']!==$source['column'] || $binding['heading']!==($source['heading']??null)
                || $binding['css_class']!==($source['css_class']??null) || $binding['body_sha256']!==hash('sha256',$source['body'])) {
                throw new RuntimeException($policy['label'].' exact practice/owner/category/level binding differs.');
            }
            $body=json_decode($source['body'],true,flags:JSON_THROW_ON_ERROR);
            if ($binding['linked_practice']!==($body['linked_practice']??null)
                || array_intersect_key($body['linked_practice']??[],array_flip(['source','question_types','seeder_classes']))!==self::config($target)) {
                throw new RuntimeException($policy['label'].' exact thematic bank config differs.');
            }
            $pool=array_merge(...array_values($target['expected_pool_ids_by_bank']));$selected=array_column($target['first_five'],'id');
            $excluded=array_values(array_diff($pool,$selected));sort($excluded);
            if (count($selected)!==5 || count(array_unique($selected))!==5 || $selected!==$target['expected_selected_ids']
                || array_column($target['first_five'],'uuid')!==$target['expected_selected_uuids'] || $excluded!==$target['excluded_question_ids']) {
                throw new RuntimeException($policy['label'].' finite five/pool partition differs.');
            }
            foreach ($target['first_five'] as $sample) {
                $hintOptIn = array_key_exists('verb_hint_snapshot_schema', $policy);
                if (array_key_exists('verb_hint_snapshot_schema', $sample) !== $hintOptIn
                    || ($hintOptIn && ($policy['verb_hint_snapshot_schema'] !== 'complete-loaded-verb-hints-v1'
                        || $sample['verb_hint_snapshot_schema'] !== $policy['verb_hint_snapshot_schema']
                        || preg_match('/^[a-f0-9]{64}$/D', $sample['snapshot_hashes']['verb_hints_snapshot'] ?? '') !== 1))) {
                    throw new RuntimeException($policy['label'].' exact verb-hint snapshot schema opt-in differs.');
                }
                if ($sample['token_presentations']!==[] || $sample['option_presentations']!==[]
                    || $sample['question_attribute_keys']!==M51QuestionSupplement::ROW_COLUMNS['questions']
                    || ComposeTokenCase::normalize($sample['correct_tokens'])!==$sample['correct_tokens']) {
                    throw new RuntimeException($policy['label'].' must use unchanged native token casing; no presentation formatter.');
                }
            }
        }
    }

    public static function loadAudit(?string $root=null): array
    {
        $package=static::packageClass();$policy=static::policy();
        $root??=base_path();$file=$root.'/'.$policy['audit'];
        if (!is_file($file)||is_link($file)||!hash_equals($policy['audit_sha256'],hash_file('sha256',$file))) {
            throw new RuntimeException($policy['label'].' reviewed teaching audit differs.');
        }
        $audit=json_decode(file_get_contents($file),true,flags:JSON_THROW_ON_ERROR);
        [, $patch, , $contract]=$package::load($root);
        self::validateAudit($audit,$patch,$contract);return $audit;
    }

    private static function config(array $target): array
    {
        return ['source'=>'theory_links','question_types'=>['4'],'seeder_classes'=>$target['bank_seeders']];
    }

    private static function binding(TextBlock $block): ?array
    {
        $package=static::packageClass();
        $i=array_search($block->seeder,$package::OWNERS,true);
        if (app()->getLocale()!=='uk'||$i===false||$block->locale!=='uk'||$block->type!=='practice-set'
            ||$block->level!==$package::LEVELS[$i]||$block->page_id!==$package::PAGE_IDS[$i]
            ||$block->page_category_id!==$package::CATEGORY_IDS[$i]) { return null; }
        try {
            $data=json_decode((string)$block->body,true,flags:JSON_THROW_ON_ERROR);
            if (!is_array($data)||$package::presentation($block,$data)===null) { return null; }
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
                || !$question->getRelation('hints') instanceof Collection || !$question->getRelation('verbHints') instanceof Collection) { return false; }
            $verbHints = self::verbHints($question, $sample);
            if ($verbHints === null) { return false; }
            $answers = self::answers($question); $options = self::options($question); $pivots = self::optionPivots($question);
            if ($answers === null || $options === null || $pivots === null
                || M26DetailPackage::digest($answers) !== $sample['snapshot_hashes']['ordered_answer_snapshot']
                || M26DetailPackage::digest($options) !== $sample['snapshot_hashes']['original_options_snapshot']
                || M26DetailPackage::digest($pivots) !== $sample['snapshot_hashes']['loaded_option_pivots']
                || M26DetailPackage::digest(self::hints($question)) !== $sample['snapshot_hashes']['hints_snapshot']
                || M26DetailPackage::digest($verbHints) !== $sample['snapshot_hashes']['verb_hints_snapshot']) { return false; }
        }
        return true;
    }

    /**
     * Explicit frozen opt-in only. Historical packages still require an empty
     * relation. Complete raw hint/option attributes and loaded row order are
     * bound without queries, locale/case normalization or original-model edits.
     */
    private static function verbHints(Question $question, array $sample): ?array
    {
        if (!$question->relationLoaded('verbHints') || !$question->getRelation('verbHints') instanceof Collection) { return null; }
        $hints = $question->getRelation('verbHints');
        if (!array_key_exists('verb_hint_snapshot_schema', $sample)) { return $hints->isEmpty() ? [] : null; }
        if ($sample['verb_hint_snapshot_schema'] !== 'complete-loaded-verb-hints-v1'
            || !$question->relationLoaded('options') || !$question->getRelation('options') instanceof Collection) { return null; }
        $hintKeys = ['id', 'question_id', 'option_id', 'marker', 'locale', 'created_at', 'updated_at'];
        $optionKeys = ['id', 'option', 'created_at', 'updated_at'];
        $exact = static function (array $attributes, array $keys): ?array {
            $actual = array_keys($attributes); $expected = $keys; sort($actual); sort($expected);
            if ($actual !== $expected) { return null; }
            $out = []; foreach ($keys as $key) { $out[$key] = $attributes[$key]; } return $out;
        };
        $options = [];
        foreach ($question->getRelation('options') as $option) {
            if (!$option instanceof QuestionOption) { return null; }
            $attributes = $exact($option->getAttributes(), $optionKeys);
            if ($attributes === null || !is_int($attributes['id']) || !is_string($attributes['option'])
                || isset($options[$attributes['id']])) { return null; }
            $options[$attributes['id']] = $attributes;
        }
        $rows = []; $ids = [];
        foreach ($hints as $hint) {
            if (!$hint instanceof VerbHint || !$hint->relationLoaded('option')) { return null; }
            $attributes = $exact($hint->getAttributes(), $hintKeys); $option = $hint->getRelation('option');
            if ($attributes === null || !is_int($attributes['id']) || isset($ids[$attributes['id']])
                || $attributes['question_id'] !== ($question->getAttributes()['id'] ?? null)
                || !is_int($attributes['option_id']) || !is_string($attributes['marker']) || !is_string($attributes['locale'])
                || !$option instanceof QuestionOption) { return null; }
            $optionAttributes = $exact($option->getAttributes(), $optionKeys);
            if ($optionAttributes === null || $optionAttributes['id'] !== $attributes['option_id']
                || (isset($options[$attributes['option_id']]) && $options[$attributes['option_id']] !== $optionAttributes)) { return null; }
            // Hint-only options have pivot flag1 and are intentionally absent
            // from Question::options(); their complete separate snapshot is
            // still bound below, never inserted into the native token bank.
            $ids[$attributes['id']] = true;
            $rows[] = ['attributes' => $attributes, 'option' => $optionAttributes];
        }
        return $rows;
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
