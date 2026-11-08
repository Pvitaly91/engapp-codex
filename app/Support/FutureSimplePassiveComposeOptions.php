<?php

namespace App\Support;

use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/** Finite, render-only compose choices. No shared controller, answer or DB mutation. */
final class FutureSimplePassiveComposeOptions
{
    public const RULES = 'docs/content/future-simple-passive-compose-options.v1.json';
    public const REVISION = 'future-simple-passive-compose-options-v1';
    private const SOURCE = 'database/seeders/V3/Polyglot/PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder/definition.json';
    private const SEEDER = 'Database\\Seeders\\V3\\Polyglot\\PolyglotPassiveVoiceFutureSimpleAllLevelsLessonSeeder';

    private static function root(): string { return dirname(__DIR__, 2); }

    private static function tokenKey(string $value): string
    {
        return mb_strtolower(rtrim(trim(str_replace(['’', '‘'], "'", $value)), '.,!?;:'));
    }

    private static function answerKey(string $value): string
    {
        return mb_strtolower(Str::ascii(trim(str_replace(['’', '‘'], "'", $value))));
    }

    private static function digest(mixed $value): string
    {
        return hash('sha256', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** File-only deterministic compilation; every token belongs to this pinned72-row source. */
    public static function compile(): array
    {
        $path = self::root().'/'.self::RULES; $sourcePath = self::root().'/'.self::SOURCE;
        if (!is_file($path) || is_link($path) || !is_file($sourcePath) || is_link($sourcePath)) {
            throw new RuntimeException('Future compose option source is missing or linked.');
        }
        $rules = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($rules['schema_version'] ?? null) !== 1 || ($rules['revision'] ?? null) !== self::REVISION
            || ($rules['source_path'] ?? null) !== self::SOURCE || ($rules['source_seeder'] ?? null) !== self::SEEDER
            || ($rules['question_prefix'] ?? null) !== 'pv-fut-simple-poly-' || ($rules['question_count'] ?? null) !== 72
            || ($rules['wrong_options_per_marker'] ?? null) !== 3
            || !is_string($rules['source_sha256'] ?? null) || !hash_equals($rules['source_sha256'], hash_file('sha256', $sourcePath))) {
            throw new RuntimeException('Future compose option finite source binding differs.');
        }
        $source = json_decode(file_get_contents($sourcePath), true, flags: JSON_THROW_ON_ERROR);
        if (($source['seeder']['class'] ?? null) !== self::SEEDER || count($source['questions'] ?? []) !== 72) {
            throw new RuntimeException('Future compose option bank identity differs.');
        }
        $profiles = []; $tokens = [];
        foreach ($rules['profiles'] ?? [] as $name => $profile) {
            if (!is_string($name) || !is_array($profile['tokens'] ?? null) || !is_array($profile['candidates'] ?? null)
                || count($profile['candidates']) < 6) { throw new RuntimeException('Future compose option profile differs.'); }
            $profiles[$name] = self::cleanCandidates($profile['candidates']);
            foreach ($profile['tokens'] as $token) {
                if (!is_string($token) || $token === '' || isset($tokens[self::tokenKey($token)])) {
                    throw new RuntimeException('Future compose token role is missing or duplicated.');
                }
                $tokens[self::tokenKey($token)] = $name;
            }
        }
        $verbs = [];
        foreach ($rules['verb_forms'] ?? [] as $token => $candidates) { $verbs[$token] = self::cleanCandidates($candidates); }
        $overrides = [];
        foreach ($rules['token_overrides'] ?? [] as $token => $candidates) { $overrides[$token] = self::cleanCandidates($candidates); }
        $compiled = [];
        foreach ($source['questions'] as $row) {
            if (!is_string($row['uuid'] ?? null) || !str_starts_with($row['uuid'], 'pv-fut-simple-poly-')
                || isset($compiled[$row['uuid']]) || (string) ($row['type'] ?? '') !== '4'
                || ($row['quality_revision'] ?? null) !== 'future-simple-passive-v1') {
                throw new RuntimeException('Future compose option row identity differs.');
            }
            $answers = array_map(static fn(array $marker): string => $marker['answer'], $row['markers']);
            if (array_values($answers) !== ($row['tokens_correct'] ?? null)) { throw new RuntimeException('Future compose marker sequence differs.'); }
            $banned = [];
            foreach (array_merge([$row['target_text']], $row['accepted_target_texts'] ?? []) as $sentence) {
                foreach (preg_split('/\s+/u', trim($sentence)) as $word) { $banned[self::tokenKey($word)] = true; }
            }
            foreach ($row['answer_synonyms'] ?? [] as $aliases) { foreach ($aliases as $alias) { $banned[self::tokenKey($alias)] = true; } }
            foreach ($row['display_answer_synonyms'] ?? [] as $aliases) { foreach ($aliases as $alias) { $banned[self::tokenKey($alias)] = true; } }
            $used = []; $choices = []; $wrongByMarker = []; $roles = [];
            foreach ($answers as $marker => $answer) {
                $key = self::tokenKey($answer); $role = $tokens[$key] ?? (isset($verbs[$key]) ? 'verb' : null);
                if ($role === null) { throw new RuntimeException('Future compose token has no reviewed role: '.$key); }
                $roles[$marker] = $role;
                $primary = $overrides[$key] ?? $verbs[$key] ?? $profiles[$role];
                $pool = self::rotated($primary, $row['uuid'].'|'.$marker.'|primary');
                $fallback = $role === 'verb' ? ['do', 'does', 'doing', 'make', 'makes', 'making', 'take', 'takes', 'taking', 'work', 'works', 'working'] : $profiles[$role];
                $pool = array_merge($pool, self::rotated($fallback, $row['uuid'].'|'.$marker.'|fallback'));
                $wrong = [];
                foreach ($pool as $candidate) {
                    $candidateKey = self::tokenKey($candidate);
                    if (isset($banned[$candidateKey]) || isset($used[$candidateKey])) { continue; }
                    $wrong[] = self::present($candidate, $answer);
                    $used[$candidateKey] = true;
                    if (count($wrong) === 3) { break; }
                }
                if (count($wrong) !== 3) { throw new RuntimeException('Future compose option pool exhausted: '.$row['uuid'].'/'.$marker); }
                $valid = [$answer];
                foreach ($row['display_answer_synonyms'][$marker] ?? [] as $alias) { $valid[] = $alias; }
                foreach ($row['answer_synonyms'][$marker] ?? [] as $alias) { $valid[] = $alias; }
                $valid = array_values(array_unique($valid));
                $choices[$marker] = array_merge($valid, $wrong); $wrongByMarker[$marker] = $wrong;
            }
            $entry = ['source_sha256' => self::digest($row), 'question' => $row['question'], 'answers' => $answers,
                'options_by_marker' => $choices, 'wrong_by_marker' => $wrongByMarker, 'roles' => $roles,
                'rules_sha256' => hash_file('sha256', $path)];
            $entry['revision'] = self::digest($entry); $compiled[$row['uuid']] = $entry;
        }
        return $compiled;
    }

    private static function cleanCandidates(array $values): array
    {
        $clean = [];
        foreach ($values as $value) {
            if (!is_string($value) || trim($value) !== $value || $value === ''
                || preg_match('/[<>\{\}]|&(?:#\d+|[a-z]+);/iu', $value) === 1) {
                throw new RuntimeException('Invalid reviewed Future compose candidate.');
            }
            $key = self::tokenKey($value);
            if (isset($clean[$key])) { throw new RuntimeException('Repeated reviewed Future compose candidate.'); }
            $clean[$key] = $value;
        }
        return array_values($clean);
    }

    private static function rotated(array $values, string $seed): array
    {
        $offset = hexdec(substr(hash('sha256', $seed), 0, 7)) % count($values);
        return array_merge(array_slice($values, $offset), array_slice($values, 0, $offset));
    }

    private static function present(string $candidate, string $answer): string
    {
        preg_match('/([.,!?;:]+)$/u', $answer, $suffix);
        if (preg_match('/^\p{Lu}/u', $answer) === 1 && preg_match('/^\p{Ll}/u', $candidate) === 1) {
            $candidate = mb_strtoupper(mb_substr($candidate, 0, 1)).mb_substr($candidate, 1);
        }
        return $candidate.($suffix[1] ?? '');
    }

    private static function catalog(): array
    {
        static $catalog;
        if ($catalog !== null) { return $catalog; }
        try { return $catalog = self::compile(); }
        catch (Throwable) { return $catalog = []; } // An unrecognised source retains its original, complete UI.
    }

    private static function matches(array $payload, array $source, array $entry): bool
    {
        return (string) ($payload['type'] ?? '') === '4' && ($payload['question'] ?? null) === $entry['question']
            && ($payload['uuid'] ?? null) === ($source['uuid'] ?? null)
            && self::digest($source) === $entry['source_sha256']
            && array_keys($payload['answer_map'] ?? []) === array_keys($entry['answers'])
            && array_map(self::answerKey(...), array_values($payload['answer_map'] ?? [])) === array_map(self::answerKey(...), array_values($entry['answers']));
    }

    public static function decorate(array $payload, ?array $source): array
    {
        $entry = self::catalog()[$payload['uuid'] ?? ''] ?? null;
        if (!$source || !$entry || !self::matches($payload, $source, $entry)) { return $payload; }
        $payload['options_by_marker'] = array_values($entry['options_by_marker']);
        $payload['fsp_compose_options_revision'] = $entry['revision'];
        // Existing progress merges refresh this content field and the choices,
        // while keeping answers, chosen tokens, attempts and input state intact.
        $payload['compose_content_revision'] = self::digest([
            'source' => $payload['psp_content_revision'] ?? self::digest($source), 'options' => $entry['revision'],
        ]);
        return $payload;
    }

    public static function cachedNeedsRefresh(array $payloads, array $sources): bool
    {
        foreach ($payloads as $payload) {
            $source = $sources[$payload['uuid'] ?? ''] ?? null;
            $entry = self::catalog()[$payload['uuid'] ?? ''] ?? null;
            if (!$source || !$entry || !self::matches($payload, $source, $entry)) { continue; }
            $expected = self::decorate($payload, $source);
            foreach (['options_by_marker', 'fsp_compose_options_revision', 'compose_content_revision'] as $key) {
                if (($payload[$key] ?? null) !== ($expected[$key] ?? null)) { return true; }
            }
        }
        return false;
    }
}
