<?php

namespace App\Support;

use RuntimeException;
use Throwable;

/** Finite, source-bound grammatical choices; answers, graders and DB rows remain unchanged. */
final class NounsArticlesQuantityComposeOptions
{
    public const MANIFESTS = [
        'docs/content/nouns-articles-quantity-quality/compose-roles-eight.v1.json' => '6a69de85390b783edc25b4e00e78e74c8996571beb4a2112d118a3d8fef331fb',
        'docs/content/nouns-articles-quantity-quality/compose-roles-four.v1.json' => '6464e74ea772b7b23d59ab44779a0d15aeddff1316b924c811c9cbe3332f2644',
    ];
    public const ROLES = ['cardinal_plural', 'cardinal_singular', 'ordinal', 'clock_number', 'article', 'determiner', 'pronoun', 'preposition', 'conjunction', 'auxiliary',
        'verb', 'noun', 'adjective', 'adverb', 'proper_noun', 'noun_phrase', 'verb_phrase', 'preposition_phrase', 'adverb_phrase'];

    private static function key(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/^[.,!?;:]+|[.,!?;:]+$/u', '', str_replace(['’', '‘'], "'", $value))));
    }
    private static function digest(mixed $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** Every role is explicitly authored per marker, never guessed from spelling at runtime. */
    public static function compile(): array
    {
        $root = dirname(__DIR__, 2); $rows = []; $pools = []; $topicPools = []; $topics = []; $hashes = [];
        foreach (self::MANIFESTS as $path => $sha) {
            $file = $root.'/'.$path;
            if (!is_file($file) || is_link($file) || !hash_equals($sha, hash_file('sha256', $file))) { throw new RuntimeException('Reviewed compose role manifest differs.'); }
            $doc = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR); $hashes[$path] = $sha;
            if (($doc['schema_version'] ?? null) !== 1 || !is_array($doc['topics'] ?? null) || !is_array($doc['rows'] ?? null)) { throw new RuntimeException('Reviewed compose role schema differs.'); }
            foreach ($doc['topics'] as $topic) {
                if (isset($topics[$topic]) || !isset(NounsArticlesQuantityPracticeQuality::TOPICS[$topic])) { throw new RuntimeException('Reviewed compose role topic scope differs.'); }
                $binding = $doc['sources'][$topic] ?? null;
                $expected = NounsArticlesQuantityPracticeQuality::mapping($topic)['author'];
                if (($binding['path'] ?? null) !== $expected || !is_file($root.'/'.$expected)
                    || !hash_equals((string) ($binding['sha256'] ?? ''), hash_file('sha256', $root.'/'.$expected))) { throw new RuntimeException('Reviewed compose author source differs.'); }
                $topics[$topic] = true;
            }
            foreach ($doc['pools'] ?? [] as $role => $values) {
                self::validatePool($role, $values); $pools[$role] = array_merge($pools[$role] ?? [], $values);
            }
            foreach ($doc['topic_pools'] ?? [] as $topic => $roles) {
                if (!in_array($topic, $doc['topics'], true)) { throw new RuntimeException('Foreign role topic pool.'); }
                foreach ($roles as $role => $values) { self::validatePool($role, $values); $topicPools[$topic][$role] = $values; }
            }
            foreach ($doc['rows'] as $row) {
                $topic = $row['topic'] ?? ''; $id = $row['author_id'] ?? ''; $key = $topic.'/'.$id;
                if (!in_array($topic, $doc['topics'], true) || isset($rows[$key]) || !is_array($row['marker_tokens'] ?? null)
                    || !is_array($row['marker_roles'] ?? null) || count($row['marker_tokens']) !== count($row['marker_roles'])) { throw new RuntimeException('Reviewed role row differs.'); }
                foreach ($row['marker_roles'] as $role) { if (!in_array($role, self::ROLES, true)) { throw new RuntimeException('Unreviewed compose role: '.$role); } }
                $rows[$key] = $row;
            }
        }
        if (count($topics) !== 12 || count($rows) !== 216) { throw new RuntimeException('Exactly twelve topics and216 compose role rows required.'); }
        $levelPools = []; $topicLevelPools = [];
        foreach ($rows as $row) {
            $level = explode('-', $row['author_id'])[0];
            foreach ($row['marker_tokens'] as $index => $token) {
                $role = $row['marker_roles'][$index];
                $token = trim(preg_replace('/^[.,!?;:]+|[.,!?;:]+$/u', '', $token));
                $levelPools[$level][$role][] = $token;
                $topicLevelPools[$row['topic']][$level][$role][] = $token;
            }
        }
        $compiled = [];
        foreach (NounsArticlesQuantityPracticeQuality::questions() as $source) {
            if ((string) $source['type'] !== '4') { continue; }
            $topic = null;
            foreach (array_keys(NounsArticlesQuantityPracticeQuality::TOPICS) as $candidate) {
                if (NounsArticlesQuantityPracticeQuality::questionUuid($candidate, $source['author_id']) === $source['uuid']) { $topic = $candidate; break; }
            }
            $row = $rows[$topic.'/'.$source['author_id']] ?? null; $tokens = array_column($source['markers'], 'answer');
            if (!$row || ($row['target'] ?? null) !== $source['target_text'] || $row['marker_tokens'] !== $tokens) { throw new RuntimeException('Exact compose token/role binding differs.'); }
            $banned = []; $used = []; $choices = []; $roleByMarker = [];
            foreach (array_merge([$source['target_text']], $source['accepted_target_texts']) as $sentence) {
                foreach (preg_split('/\s+/u', $sentence) as $word) { $banned[self::key($word)] = true; }
            }
            foreach ($tokens as $token) { $banned[self::key($token)] = true; }
            foreach ($source['answer_synonyms'] as $aliases) { foreach ($aliases as $alias) { $banned[self::key($alias)] = true; } }
            foreach (array_keys($source['markers']) as $index => $marker) {
                $answer = $tokens[$index]; $role = $row['marker_roles'][$index]; $roleByMarker[$marker] = $role;
                $wrong = [];
                // Prefer vocabulary actually authored for this exact CEFR level.
                // Reviewed fallback families cover scarce closed-class words.
                foreach ([$topicLevelPools[$topic][$source['level']][$role] ?? [], $levelPools[$source['level']][$role] ?? [],
                    $topicPools[$topic][$role] ?? [], $pools[$role] ?? []] as $priority => $pool) {
                    $pool = array_values(array_unique($pool));
                    if ($pool === []) { continue; }
                    $offset = hexdec(substr(hash('sha256', $source['uuid'].'|'.$marker.'|'.$priority), 0, 7)) % count($pool);
                    for ($step = 0; $step < count($pool) && count($wrong) < 3; ++$step) {
                        $candidate = $pool[($offset + $step) % count($pool)]; $candidateKey = self::key($candidate);
                        if (isset($banned[$candidateKey]) || isset($used[$candidateKey])) { continue; }
                        if ($role !== 'proper_noun' && $candidateKey !== 'i') { $candidate = mb_strtolower($candidate); }
                        // Capitalization follows the authored position, not arbitrary pool casing.
                        if (preg_match('/^\p{Lu}/u', $answer) && preg_match('/^\p{Ll}/u', $candidate)) {
                            $candidate = mb_strtoupper(mb_substr($candidate, 0, 1)).mb_substr($candidate, 1);
                        }
                        $wrong[] = $candidate; $used[$candidateKey] = true;
                    }
                    if (count($wrong) === 3) { break; }
                }
                if (count($wrong) !== 3) { throw new RuntimeException('Reviewed grammatical choice pool exhausted: '.$topic.'/'.$source['author_id'].'/'.$marker.'/'.$role); }
                $choices[$marker] = array_merge([$answer], $wrong);
            }
            $entry = ['source_sha256' => self::digest($source), 'question' => $source['question'], 'answers' => $tokens,
                'roles' => $roleByMarker, 'options_by_marker' => array_values($choices), 'manifest_hashes' => $hashes];
            $entry['revision'] = self::digest($entry); $compiled[$source['uuid']] = $entry;
        }
        if (count($compiled) !== 216) { throw new RuntimeException('Incomplete reviewed compose option coverage.'); }
        return $compiled;
    }

    private static function validatePool(string $role, mixed $pool): void
    {
        if (!in_array($role, self::ROLES, true) || !is_array($pool) || $pool === []) { throw new RuntimeException('Reviewed role pool missing.'); }
        foreach ($pool as $value) {
            if (!is_string($value) || trim($value) !== $value || $value === '' || count(preg_split('/\s+/u', $value)) > 3
                || preg_match('/[<>\{\}]|&(?:#\d+|[a-z]+);/iu', $value)) { throw new RuntimeException('Invalid grammatical distractor.'); }
        }
        if (count(array_unique(array_map(self::key(...), $pool))) < 6) { throw new RuntimeException('At least six distinct reviewed role candidates required.'); }
    }
    private static function catalog(): array
    {
        static $catalog = null;
        if ($catalog !== null) { return $catalog; }
        try { return $catalog = self::compile(); } catch (Throwable) { return $catalog = []; }
    }
    public static function decorate(array $payload, ?array $source): array
    {
        if (!$source || (string) ($payload['type'] ?? '') !== '4' || ($payload['question'] ?? null) !== $source['question']
            || ($payload['uuid'] ?? null) !== $source['uuid'] || array_values($payload['answer_map'] ?? []) !== array_column($source['markers'], 'answer')
            || ($payload['psp_content_revision'] ?? null) !== self::digest($source)) { return $payload; }
        $entry = self::catalog()[$source['uuid']] ?? null;
        if (!$entry || $entry['source_sha256'] !== self::digest($source)) { return $payload; }
        $payload['options_by_marker'] = $entry['options_by_marker'];
        $payload['compose_content_revision'] = hash('sha256', $payload['psp_content_revision'].'|'.$entry['revision']);
        return $payload;
    }
    public static function cachedNeedsRefresh(array $payloads, array $sources): bool
    {
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            if (!$source || (string) $source['type'] !== '4') { continue; }
            $decorated = self::decorate($payload, $source);
            foreach (['options_by_marker', 'compose_content_revision'] as $key) {
                if (($payload[$key] ?? null) !== ($decorated[$key] ?? null)) { return true; }
            }
        }
        return false;
    }
}
