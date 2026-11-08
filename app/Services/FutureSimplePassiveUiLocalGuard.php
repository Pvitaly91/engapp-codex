<?php

namespace App\Services;

/** Local-only boundary for the finite eleven-row Future-passive UI follow-up. */
final class FutureSimplePassiveUiLocalGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'Future Simple Passive UI follow-up';
    protected const PRIVATE_DIRECTORY = 'future-simple-passive-ui-fixes';
    protected const ENDPOINT = 'future-passive-ui-';

    public static function scope(): array { return FutureSimplePassiveQualityLocalGuard::scope(); }

    /** The returned copy masks only approved mutable columns; IDs/timestamps stay protected. */
    public static function protectedRow(string $table, array $row, array $targetIds): ?array
    {
        $target = isset($targetIds[(string) ($row[$table === 'questions' ? 'id' : 'question_id'] ?? '')]);
        if (!$target) { return $row; }
        if ($table === 'question_option_question') { return null; }
        foreach (match ($table) {
            'questions' => ['question', 'options_by_marker'],
            'question_answers', 'verb_hints' => ['option_id'],
            'question_variants' => ['text'],
            default => [],
        } as $column) { $row[$column] = null; }
        return $row;
    }
}
