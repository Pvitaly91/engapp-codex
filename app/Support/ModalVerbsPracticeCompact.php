<?php

namespace App\Support;

use RuntimeException;

/** Finite teaching-copy projection; the existing controls and answer values remain authoritative. */
final class ModalVerbsPracticeCompact extends TheoryPracticeCompactProjection
{
    public const SOURCE = 'docs/content/modal-verbs-practice-compact.v1.json';
    public const SOURCE_SHA = '8bc5fe09defa6f2dbace83b244d9ac34122b04eeae1e62e2febb887db73ee5f2';
    public const SUPPLEMENT = ModalVerbsPracticeSource::class;
    private const DEFERRED = ['context', 'label', 'prompt', 'before', 'after', 'options', 'author_explanation'];

    public static function validate(array $document): void
    {
        parent::validate($document);
        $sources = array_column(ModalVerbsPracticeSource::load()['targets'], null, 'topic');
        $total = 0; $cards = 0;
        foreach ($document['targets'] as $target) {
            if (($target['projection_family'] ?? null) !== 'modal-verbs-practice-compact-v1') { throw new RuntimeException('Modal projection family differs.'); }
            $source = $sources[$target['topic']]['after_data'];
            foreach ($target['groups'] as $group) {
                if (count($group['indices']) !== 2) { throw new RuntimeException('Modal exercise must retain two tasks.'); }
                ++$cards;
            }
            foreach (['selects', 'choices', 'inputs', 'rephrase'] as $kind) {
                foreach ($target['items'][$kind] as $row) {
                    ++$total; $item = $source[$kind][$row['index']];
                    if (($row['source_index'] ?? null) !== ($item['source_index'] ?? null)
                        || !is_array($row['deferred_source_fields'] ?? null)
                        || array_diff($row['deferred_source_fields'], self::DEFERRED) !== []) {
                        throw new RuntimeException('Modal original-field binding differs.');
                    }
                    foreach ($row['deferred_source_fields'] as $field) {
                        if (!array_key_exists($field, $item)) { throw new RuntimeException('Modal original field missing.'); }
                    }
                    if (!isset($row['option_labels'])) { continue; }
                    $options = $item['options'] ?? ($kind === 'choices' ? ($source['choice_options'] ?? ['a','b']) : ($source['options'] ?? []));
                    $labels = $row['option_labels'];
                    if (!is_array($labels) || count($labels) !== count($options)) { throw new RuntimeException('Modal label coverage differs.'); }
                    $visible = [];
                    foreach ($options as $i => $option) {
                        $label = $kind === 'selects' ? ($labels[$i] ?? null) : ($labels[$option] ?? null);
                        if (!is_string($label) || trim($label) === '' || mb_strlen($label) > 180
                            || preg_match('/<[^>]+>|\{a\d+\}|&(?:nbsp|amp|quot);/u', $label)
                            || in_array($label, $visible, true)) { throw new RuntimeException('Modal short labels must remain distinct and plain.'); }
                        $visible[] = $label;
                    }
                }
            }
        }
        if ($total !== 64 || $cards !== 32) { throw new RuntimeException('Modal compact coverage differs.'); }
    }

    public static function nativeDisplay(?array $compact, ?array $original): ?array
    {
        if (($compact['projection_family'] ?? null) !== 'modal-verbs-practice-compact-v1') { return $original; }
        $display = $original ?? ['selects'=>[], 'choices'=>[], 'answer_groups'=>[]];
        foreach (['selects', 'choices'] as $kind) {
            foreach ($compact['items'][$kind] as $row) {
                if (isset($row['option_labels'])) {
                    $display[$kind][$row['index']][$kind === 'selects' ? 'options' : 'option_labels'] = $row['option_labels'];
                }
            }
        }
        return $display;
    }

    /** Original trusted fields, available only after that control is checked (or in no-JS details). */
    public static function deferredFields(?array $row, array $item): array
    {
        if ($row === null || !isset($row['deferred_source_fields'])
            || ($row['source_item_sha256'] ?? null) !== ModalVerbsPracticeSource::digest($item)) { return []; }
        $fields = [];
        foreach ($row['deferred_source_fields'] as $field) {
            if (!in_array($field, self::DEFERRED, true)) { return []; }
            $value = $item[$field] ?? null;
            if (is_string($value) && trim($value) !== '') { $fields[] = ['kind'=>'html', 'value'=>$value]; }
            elseif ($field === 'options' && is_array($value)) { $fields[] = ['kind'=>'options', 'value'=>$value]; }
        }
        return $fields;
    }
}
