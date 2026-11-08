<?php

namespace App\Support;

/** Exact existing-text emphasis only; never an answer source or sentence splitter. */
final class TheorySentenceBuilderPresentation
{
    public const SOURCE = 'docs/content/theory-sentence-builder-prompt-parts.v1.json';
    public const SOURCE_SHA = '77a717063fa1ce01524b05c66d5c11259b7166bed152f79ba85c909880ccc0b6';

    public static function project(array $payload, bool $canonical, string $locale): array
    {
        if (!$canonical || $locale !== 'uk' || (string) ($payload['type'] ?? '') !== '4'
            || !is_string($payload['question'] ?? null) || trim($payload['question']) === '') {
            return $payload;
        }

        $payload['compact_theory_compose'] = true;
        // Unknown/new prompts retain every original byte as one visible unit.
        // No heuristic decides where their alleged target sentence begins.
        $payload['compose_prompt_parts'] = [['kind' => 'sentence', 'text' => $payload['question']]];
        $entry = self::annotations()[(string) ($payload['id'] ?? '')] ?? null;
        if ($entry === null || $entry['question'] !== $payload['question']
            || ($payload['correct_tokens'] ?? null) !== $entry['correct_tokens']) {
            return $payload;
        }
        $payload['compose_prompt_parts'] = $entry['parts'];
        $payload['compose_prompt_revision'] = self::SOURCE_SHA;
        return $payload;
    }

    private static function annotations(): array
    {
        static $entries = null;
        if ($entries !== null) { return $entries; }
        $entries = [];
        $path = dirname(__DIR__, 2).'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !hash_equals(self::SOURCE_SHA, hash_file('sha256', $path))) {
            return $entries;
        }
        try {
            $source = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (($source['schema_version'] ?? null) !== 1
                || ($source['projection'] ?? null) !== 'theory-sentence-builder-prompt-parts-v1'
                || ($source['locale'] ?? null) !== 'uk'
                || !is_array($source['questions'] ?? null)
                || ($source['counts']['questions'] ?? null) !== count($source['questions'])) {
                return [];
            }
            $validated = [];
            foreach ($source['questions'] as $entry) {
                if (!is_int($entry['id'] ?? null) || $entry['id'] < 1 || isset($validated[$entry['id']])
                    || !is_string($entry['question'] ?? null) || trim($entry['question']) === ''
                    || !hash_equals((string) ($entry['question_sha256'] ?? ''), hash('sha256', $entry['question']))
                    || !is_array($entry['correct_tokens'] ?? null) || !array_is_list($entry['correct_tokens'])
                    || !hash_equals((string) ($entry['correct_tokens_sha256'] ?? ''), hash('sha256', json_encode($entry['correct_tokens'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
                    || !is_array($entry['parts'] ?? null) || !array_is_list($entry['parts']) || $entry['parts'] === []) {
                    return [];
                }
                $whole = ''; $hasSentence = false;
                foreach ($entry['parts'] as $part) {
                    if (!is_array($part) || array_keys($part) !== ['kind', 'text']
                        || !in_array($part['kind'], ['sentence', 'instruction'], true)
                        || !is_string($part['text']) || $part['text'] === '') {
                        return [];
                    }
                    $whole .= $part['text'];
                    $hasSentence = $hasSentence || ($part['kind'] === 'sentence' && trim($part['text']) !== '');
                }
                if (!$hasSentence || $whole !== $entry['question']) { return []; }
                $validated[$entry['id']] = $entry;
            }
            return $entries = $validated;
        } catch (\Throwable) {
            return [];
        }
    }
}
