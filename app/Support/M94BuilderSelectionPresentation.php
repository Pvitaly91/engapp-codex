<?php

namespace App\Support;

/** Exact theory-only condition and Ukrainian explanation clones; no original bank mutations. */
final class M94BuilderSelectionPresentation extends TheoryBuilderSelectionPresentation
{
    public const AUDIT = 'docs/content/m94-sentence-builder-bank-audit.v1.json';
    public const AUDIT_SHA = '630831ea9d24a8f2f1d20ba980c631e54fa9610d825c2e1a99265680c917046c';
    public const TARGETS_SHA = '3e83f261611986774eae9eb6dc9e749d08c31da8a60282dae2abb8a5be6c0747';
    public const BEFORE_SHA = 'efad9a30f6f24c139a57e09f39916bdb815266dc938927d176d53a6e5a3c4cfd';

    protected static function packageClass(): string { return M94RelativeClausesWhoseWhich::class; }
    protected static function policy(): array
    {
        return ['label' => 'M94', 'package' => 'm94-relative-clauses-whose-which-bank-audit', 'version' => '1.0.0',
            'scope' => '5 selected existing B2 questions; theory-only condition and Ukrainian explanation clones; no question/answer/option/grader/progress writes',
            'audit' => self::AUDIT, 'audit_sha256' => self::AUDIT_SHA, 'targets_sha256' => self::TARGETS_SHA,
            'verb_hint_snapshot_schema' => 'complete-loaded-verb-hints-v1',
            'before_sha256' => self::BEFORE_SHA];
    }

    /** Translate only the already snapshot-proven five clones, never original hint models. */
    public static function bindings(array $bindings): array
    {
        $projected = parent::bindings($bindings);
        $original = $bindings['practiceQuestions'] ?? null;
        $questions = $projected['practiceQuestions'] ?? null;
        if (!$original instanceof \Illuminate\Support\Collection
            || !$questions instanceof \Illuminate\Support\Collection
            || $questions === $original || $questions->count() !== 5) { return $projected; }
        try {
            $target = self::loadAudit()['targets'][0];
            $copies = self::projectLoadedHints($original, $questions, $target);
            if ($copies === null) { return $bindings; }
            $projected['practiceQuestions'] = $copies;
            return $projected;
        } catch (\Throwable) { return $bindings; }
    }

    /** Pure clone operation; this private seam never grants snapshot or caller authority. */
    private static function projectLoadedHints(\Illuminate\Support\Collection $original, \Illuminate\Support\Collection $questions, array $target): ?\Illuminate\Support\Collection
    {
        try {
            $copies = [];
            foreach ($questions->values() as $index => $question) {
                $sample = $target['first_five'][$index]; $old = $original->values()[$index];
                if (!$question instanceof \App\Models\Question || $question === $old
                    || $question->id !== $sample['id'] || $question->question !== $sample['visible_condition_uk']
                    || !$question->relationLoaded('hints')) { return null; }
                $hints = $question->getRelation('hints'); $map = $sample['visible_hints_uk'] ?? null;
                if (!$hints instanceof \Illuminate\Support\Collection || !is_array($map) || !array_is_list($map)
                    || array_column($map, 'id') !== $hints->pluck('id')->all()) { return null; }
                $translated = [];
                foreach ($hints->values() as $n => $hint) {
                    $text = $map[$n]['hint'] ?? null;
                    if (!$hint instanceof \App\Models\QuestionHint || !is_string($text) || trim($text) === ''
                        || preg_match('/[\x{0400}-\x{04FF}]/u', $text) !== 1
                        || preg_match('/<[^>]+>|\{a[0-9]+\}|&(?:#[0-9]+|[a-z]+);/iu', $text) === 1) { return null; }
                    $copy = clone $hint;
                    $copy->setRawAttributes(array_replace($hint->getAttributes(), ['hint' => $text]), true);
                    $translated[] = $copy;
                }
                $copy = clone $question;
                $copy->setRelation('hints', new \Illuminate\Database\Eloquent\Collection($translated));
                $copies[] = $copy;
            }
            return new \Illuminate\Support\Collection($copies);
        } catch (\Throwable) { return null; }
    }
}
