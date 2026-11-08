<?php

namespace App\Support;

use RuntimeException;

/** Pure finite-source compiler for grammatical per-slot choices, never a dictionary guess. */
final class FinitePracticeChoiceOptions
{
    public static function reviewedSlots(array $author): array
    {
        $slots = $author['compose_slots'] ?? preg_split('/\s+/u', $author['target']);
        if ((array_key_exists('compose_slots', $author) && ($author['kind'] ?? '') !== 'compose')
            || !is_array($slots) || count($slots) < 2) { throw new RuntimeException('Reviewed compose slots missing.'); }
        foreach ($slots as $slot) {
            if (!is_string($slot) || trim($slot) !== $slot || $slot === '' || count(preg_split('/\s+/u', $slot)) > 3) {
                throw new RuntimeException('Reviewed compose slots require1–3 words.');
            }
        }
        if (implode(' ', $slots) !== $author['target']) { throw new RuntimeException('Reviewed slots change the canonical target.'); }
        return $slots;
    }

    private static function choiceKey(string $text): string
    {
        return mb_strtolower(trim(preg_replace('/^[.,!?;:]+|[.,!?;:]+$/u', '', str_replace(['’', '‘'], "'", $text))));
    }

    /** The same deterministic policy used by the accepted pronoun catalog. */
    public static function compile(array $definition, array $source, string $topic, callable $slotsFor): array
    {
        $authors = []; $levelPools = []; $pools = $source['choice_pools'] ?? [];
        if (!is_array($pools) || $pools === []) { throw new RuntimeException('Reviewed grammatical choice pools required: '.$topic); }
        foreach ($pools as $role => $values) {
            if (!is_string($role) || preg_match('/^[a-z][a-z_]+$/D', $role) !== 1 || !is_array($values) || count($values) < 6) {
                throw new RuntimeException('Reviewed grammatical family needs six candidates: '.$topic.'/'.$role);
            }
            foreach ($values as $value) {
                if (!is_string($value) || $value === '' || trim($value) !== $value || count(preg_split('/\s+/u', $value)) > 3
                    || preg_match('/[<>\{\}]|&(?:#\d+|[a-z]+);/iu', $value)) {
                    throw new RuntimeException('Invalid reviewed grammatical choice.');
                }
            }
        }
        foreach ($source['questions'] as $author) {
            if ($author['kind'] !== 'compose') { continue; }
            $slots = $slotsFor($author); $roles = $author['slot_roles'] ?? null;
            if (!is_array($roles) || count($roles) !== count($slots)) { throw new RuntimeException('Explicit slot roles required: '.$topic.'/'.$author['id']); }
            foreach ($roles as $index => $role) {
                if (!is_string($role) || !isset($pools[$role])) { throw new RuntimeException('Missing reviewed role pool: '.$topic.'/'.$author['id']); }
                $levelPools[$author['level']][$role][] = trim(preg_replace('/^[.,!?;:]+|[.,!?;:]+$/u', '', $slots[$index]));
            }
            $authors[$author['id']] = $author;
        }
        foreach ($definition['questions'] as &$row) {
            $author = $authors[$row['author_id']] ?? null;
            if (!$author || ($row['target_text'] ?? null) !== $author['target']
                || array_column($row['markers'], 'answer') !== $slotsFor($author)) {
                throw new RuntimeException('Choice compiler requires exact canonical compose slots.');
            }
            $roles = $author['slot_roles']; $banned = []; $used = []; $choices = [];
            foreach (array_merge([$row['target_text']], $row['accepted_target_texts']) as $sentence) {
                foreach (preg_split('/\s+/u', $sentence) as $word) { $banned[self::choiceKey($word)] = true; }
            }
            foreach ($row['markers'] as $marker) { $banned[self::choiceKey($marker['answer'])] = true; }
            foreach ($row['answer_synonyms'] as $aliases) { foreach ($aliases as $alias) { $banned[self::choiceKey($alias)] = true; } }
            foreach (array_keys($row['markers']) as $index => $key) {
                $role = $roles[$index]; $answer = $row['markers'][$key]['answer']; $wrong = [];
                // Closed classes may reuse an authored candidate only when all unused
                // candidates are exhausted. Correct words and aliases are never wrong.
                foreach ([false, true] as $allowUsed) {
                    foreach ([$levelPools[$row['level']][$role] ?? [], $pools[$role]] as $priority => $pool) {
                        $pool = array_values(array_unique($pool));
                        if ($pool === []) { continue; }
                        $offset = hexdec(substr(hash('sha256', $row['uuid'].'|'.$key.'|'.$priority), 0, 7)) % count($pool);
                        for ($step = 0; $step < count($pool) && count($wrong) < 3; ++$step) {
                            $candidate = $pool[($offset + $step) % count($pool)]; $normalized = self::choiceKey($candidate);
                            if (isset($banned[$normalized]) || (!$allowUsed && isset($used[$normalized]))
                                || in_array($normalized, array_map(self::choiceKey(...), $wrong), true)) { continue; }
                            if ($role !== 'proper_noun' && $normalized !== 'i') { $candidate = mb_strtolower($candidate); }
                            if (preg_match('/^\p{Lu}/u', $answer) && preg_match('/^\p{Ll}/u', $candidate)) {
                                $candidate = mb_strtoupper(mb_substr($candidate, 0, 1)).mb_substr($candidate, 1);
                            }
                            $wrong[] = $candidate; $used[$normalized] = true;
                        }
                        if (count($wrong) === 3) { break; }
                    }
                    if (count($wrong) === 3) { break; }
                }
                if (count($wrong) !== 3) { throw new RuntimeException('Reviewed role choices exhausted: '.$topic.'/'.$row['author_id'].'/'.$key.'/'.$role); }
                $choices[$key] = array_merge([$answer], $wrong); $row['markers'][$key]['options'] = $choices[$key];
            }
            $row['options_by_marker'] = $choices;
            $row['options'] = array_values(array_unique(array_merge(...array_values($choices))));
            $row['slot_roles'] = array_combine(array_keys($row['markers']), $roles);
        }
        unset($row);
        return FiniteBuilderWordSlots::finalize($definition, $source);
    }
}
