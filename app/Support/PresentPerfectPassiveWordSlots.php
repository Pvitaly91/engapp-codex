<?php
declare(strict_types=1);

namespace App\Support;

/** Exact Present Perfect Passive sentence paths; no other lesson changes its slot policy. */
final class PresentPerfectPassiveWordSlots
{
    public static function decorate(array $payload, array $source): array
    {
        if (preg_match('/^pvq-present-perfect-(?:a1|a2|b1|b2|c1|c2)-c0[1-3]$/D', $source['uuid'] ?? '') !== 1
            || (string) ($source['type'] ?? '') !== '4'
            || ($payload['uuid'] ?? null) !== $source['uuid']
            || ($payload['question'] ?? null) !== $source['question']
            || ($payload['answers'] ?? null) !== array_column($source['markers'], 'answer')) {
            return $payload;
        }
        $revision = hash('sha256', json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if (($payload['psp_content_revision'] ?? null) !== $revision) { return $payload; }
        foreach ($payload['answers'] as $word) {
            if (!is_string($word) || preg_match('/\s/u', $word)) { return $payload; }
        }
        static $previous;
        $previous ??= json_decode(file_get_contents(__DIR__.'/../../docs/content/passive-voice-quality/present-perfect.word-slots.previous.v1.json'), true, 512, JSON_THROW_ON_ERROR);
        $payload['pvq_word_slots'] = [
            'revision' => $revision,
            'question' => $source['question'],
            'answers' => array_column($source['markers'], 'answer'),
            'sentences' => $source['accepted_target_texts'],
            'options_by_marker' => $source['options_by_marker'],
            'previous' => $previous[$source['uuid']] ?? null,
        ];
        return $payload;
    }
}
