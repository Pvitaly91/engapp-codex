<?php

namespace App\Support;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\TextBlock;
use Illuminate\Support\Collection;
use Throwable;

/** Three reviewed word-instance case restorations, only in exact canonical M54 practice clones. */
final class M54BuilderTokenPresentation
{
    public const AUDIT = 'docs/content/m54-passive-patterns-bank-audit.v1.json';
    public const AUDIT_SHA = '2ae1efb300d3fccc719a7a7ddd4865738a33cf60df40720c54fa9c86d1d0034c';

    private const RESTORATIONS = [
        37889 => ['a3' => ['Brought', 'brought']],
        37815 => ['a3' => ['Given', 'given']],
        37816 => ['a5' => ['Given', 'given']],
    ];

    /** Sorted {id, option} snapshots from the independent pre-apply BEFORE, not generated AFTER. */
    private const OPTION_SNAPSHOT_SHA = [
        37815 => '1b2d978d927ad4cf30b976f9ffdf34f6fc04522b6e0daf1869af643d313426af',
        37816 => '24fc626a3b427deb8297b6d6f93eb31ca5bdf13bd93e12625b5e81dfbf1ba436',
        37817 => '830f11f5ca87368e2882553ab216b24aba2322f794246d0fda8688ebe6267fa2',
        37818 => 'bb365a403737d6eef35b9098f29c81732e5bd78da2863a79add7b570b6e865e4',
        37819 => '1240834f67acfa8c08892ec4ec2b75753132040903c6ee841eb2fa550e8b358c',
        37887 => '2511793749d9fd5cd5a70adc46d1cf8dfeb0d44fd4d6585e83d3db9de233e783',
        37888 => '0709152c59847777705b964cb71383ea7544dea90fc7f0b799e3e83f4af761c7',
        37889 => 'f891d1a7591ab89fd0720b5b90947073bbbe2b123b22c2b3ccc7ba144a4377d3',
        37890 => '862dd8c93e305e3a1f4682d7d2f96ec3631311006b850af28427afd96d0a4bac',
        37891 => '376d5a6a4e6e7a83533dd45d88979dc3a554412c5101ac2183762575f02530f2',
        37959 => '5c027b8f317feca1c9afc0496fe7b4b6bcd9bdc2a9ac00c5a4f29f7f758381bc',
        37960 => '28ccfde1e0dae8ac065b45ee0ce78fe6f5176031d54b2e0a3eb00adeec31f75d',
        37961 => 'a79827db54a07aff7f6c02ff6b69581ae83582ec6288260c9495258cd86fd657',
        37962 => '89e6268cc0efafda70222345a512eb637746153ed8af68932a0c121a1619a024',
        37963 => '2667df35c8a7d6ad913dab1e78a248df7b02a25ad11ec5e50bc0ef03099af8bf',
    ];

    public static function bindings(array $bindings): array
    {
        $block = $bindings['block'] ?? null; $questions = $bindings['practiceQuestions'] ?? null;
        $i = $block instanceof TextBlock ? array_search($block->seeder, M54PassivePatternsPackage::OWNERS, true) : false;
        if (($bindings['theoryCanonical'] ?? false) !== true || app()->getLocale() !== 'uk'
            || $i === false || $block->type !== 'practice-set' || $block->locale !== 'uk'
            || $block->page_id !== M54PassivePatternsPackage::PAGE_IDS[$i]
            || $block->page_category_id !== M54PassivePatternsPackage::CATEGORY_IDS[$i]
            || $block->level !== 'B1' || !$questions instanceof Collection || $questions->count() !== 5) {
            return $bindings;
        }
        try {
            $data = json_decode((string) $block->body, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($data) || !isset($data['m54_native_v1'])
                || M54PassivePatternsPackage::presentation($block, $data) === null) { return $bindings; }
            $audit = self::audit(); $target = $audit['targets'][$i] ?? null; $practice = $audit['practice_bindings'][$i] ?? null;
            if (!is_array($target) || !is_array($practice) || ($audit['pending'] ?? null) !== []
                || ($target['identity'] ?? null) !== $block->seeder || ($target['page_id'] ?? null) !== $block->page_id
                || ($target['category_id'] ?? null) !== $block->page_category_id || ($target['locale'] ?? null) !== 'uk'
                || ($target['level'] ?? null) !== $block->level || ($practice['identity'] ?? null) !== $block->seeder
                || ($practice['uuid'] ?? null) !== $block->uuid || ($practice['level'] ?? null) !== $block->level
                || ($practice['type'] ?? null) !== $block->type || ($practice['source_index'] ?? null) !== $block->sort_order - 1
                || ($practice['body_sha256'] ?? null) !== hash('sha256', (string) $block->body)) { return $bindings; }
            $copy = self::questions($questions, $target);
            if ($copy !== null) { $bindings['practiceQuestions'] = $copy; }
        } catch (Throwable) {
            // A stale/foreign/incomplete snapshot keeps the complete original bindings, never a partial projection.
        }
        return $bindings;
    }

    private static function audit(): array
    {
        $path = base_path(self::AUDIT);
        if (!is_file($path) || is_link($path)) { return []; }
        $bytes = file_get_contents($path);
        if (!is_string($bytes) || !hash_equals(self::AUDIT_SHA, hash('sha256', $bytes))) { return []; }
        $audit = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        return is_array($audit) && ($audit['schema_version'] ?? null) === 1 ? $audit : [];
    }

    /** All five questions are verified before any clone is created. No lazy loading or model mutation. */
    private static function questions(Collection $questions, array $target): ?Collection
    {
        $samples = $target['first_five'] ?? []; $banks = $target['bank_seeders'] ?? [];
        if (count($samples) !== 5 || count($banks) !== 1 || $questions->count() !== 5
            || $questions->pluck('id')->all() !== ($target['expected_existing_selected_ids'] ?? null)
            || $questions->pluck('uuid')->all() !== ($target['expected_selected_uuids'] ?? null)) { return null; }
        $plans = [];
        foreach ($questions->values() as $i => $question) {
            $sample = $samples[$i];
            if (!$question instanceof Question || $question->id !== ($sample['id'] ?? null)
                || $question->uuid !== ($sample['uuid'] ?? null) || $question->seeder !== $banks[0]
                || (string) $question->type !== Question::TYPE_COMPOSE_TOKENS || $question->level !== 'B1'
                || $question->question !== ($sample['condition_uk'] ?? null)
                || !$question->relationLoaded('answers') || !$question->relationLoaded('options')
                || self::answerSnapshot($question) !== ($sample['ordered_answer_snapshot'] ?? null)
                || self::optionHash($question) !== (self::OPTION_SNAPSHOT_SHA[$question->id] ?? null)) { return null; }
            $rawOptions = $question->getAttributes()['options_by_marker'] ?? null;
            if (!is_string($rawOptions) || hash('sha256', $rawOptions) !== ($sample['question_options_by_marker_sha256'] ?? null)) { return null; }
            $snapshot = $sample['ordered_answer_snapshot']; $expected = $sample['correct_tokens'] ?? [];
            $tokens = array_column($snapshot, 'option');
            $native = ComposeTokenCase::normalize($tokens);
            if (!is_array($expected) || !array_is_list($expected) || count($native) !== count($expected)) { return null; }
            foreach ($native as $n => $token) {
                if (!is_string($expected[$n]) || mb_strtolower($token, 'UTF-8') !== mb_strtolower($expected[$n], 'UTF-8')) { return null; }
                if ($token === $expected[$n]) { continue; }
                $marker = $snapshot[$n]['marker'];
                if ((self::RESTORATIONS[$question->id][$marker] ?? null) !== [$snapshot[$n]['option'], $expected[$n]]) { return null; }
                $plans[$i][$marker] = $expected[$n];
            }
        }
        if ($plans === []) { return $questions; }
        return $questions->values()->map(static function (Question $question, int $i) use ($plans): Question {
            if (!isset($plans[$i])) { return $question; }
            $copy = clone $question;
            $copy->setRelation('answers', $question->getRelation('answers')->map(static function (QuestionAnswer $answer) use ($plans, $i): QuestionAnswer {
                if (!isset($plans[$i][$answer->marker])) { return $answer; }
                $cloned = clone $answer; $option = clone $answer->getRelation('option');
                $option->setRawAttributes(array_replace($option->getAttributes(), ['option' => $plans[$i][$answer->marker]]), true);
                $cloned->setRelation('option', $option);
                return $cloned;
            }));
            return $copy;
        });
    }

    private static function answerSnapshot(Question $question): ?array
    {
        $answers = $question->getRelation('answers');
        if (!$answers instanceof Collection) { return null; } $out = [];
        foreach ($answers as $answer) {
            if (!$answer instanceof QuestionAnswer || !$answer->relationLoaded('option') || $answer->question_id !== $question->id) { return null; }
            $option = $answer->getRelation('option');
            if (!$option instanceof QuestionOption || $option->id !== $answer->option_id) { return null; }
            $out[] = ['id' => $answer->id, 'marker' => $answer->marker, 'position' => $answer->position,
                'option_id' => $answer->option_id, 'option' => $option->option];
        }
        usort($out, static fn (array $a, array $b): int => strnatcasecmp($a['marker'], $b['marker']));
        return $out;
    }

    private static function optionHash(Question $question): ?string
    {
        $options = $question->getRelation('options');
        if (!$options instanceof Collection) { return null; } $out = [];
        foreach ($options as $option) {
            if (!$option instanceof QuestionOption || !is_int($option->id) || !is_string($option->option)) { return null; }
            $out[] = ['id' => $option->id, 'option' => $option->option];
        }
        usort($out, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
        return hash('sha256', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
