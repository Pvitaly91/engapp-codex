<?php

namespace App\Support;

final class FuturePerfectAnswerSynonyms
{
    /**
     * Context-specific, single-token alternatives for the Ukrainian
     * Future Perfect sentence-builder banks.
     *
     * The first-level key is the authored type-4 question UUID. Paired
     * type-0 gap questions use the same entry through their deterministic
     * `-v3-` to `-poly-` UUID mapping. Each inner key is the canonical token,
     * not a global vocabulary rule: words such as "checked", "reviewed",
     * and "tested" are interchangeable only in selected contexts.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private const CATALOG = [
        // Forms.
        'ukm-fpf-poly-a1-01' => ['finished' => ['completed']],
        'ukm-fpf-poly-a1-02' => ['dinner' => ['supper']],
        'ukm-fpf-poly-a1-03' => ['cleaned' => ['tidied']],
        'ukm-fpf-poly-a1-06' => ['completed' => ['finished']],
        'ukm-fpf-poly-a2-01' => [
            'completed' => ['finished'],
            'application' => ['form'],
        ],
        'ukm-fpf-poly-a2-02' => [
            'shop' => ['store'],
            'bought' => ['purchased'],
        ],
        'ukm-fpf-poly-a2-03' => ['repaired' => ['fixed']],
        'ukm-fpf-poly-a2-06' => [
            'prepared' => ['cooked'],
            'dinner' => ['supper'],
        ],
        'ukm-fpf-poly-a2-07' => [
            'built' => ['created'],
            'website' => ['site'],
        ],
        'ukm-fpf-poly-b1-01' => [
            'team' => ['group'],
            'checked' => ['reviewed'],
            'figures' => ['numbers'],
        ],
        'ukm-fpf-poly-b1-02' => ['whole' => ['entire']],
        'ukm-fpf-poly-b1-04' => [
            'produced' => ['prepared'],
            'alternatives' => ['options'],
        ],
        'ukm-fpf-poly-b1-05' => ['contract' => ['agreement']],
        'ukm-fpf-poly-b1-06' => ['completed' => ['finished']],
        'ukm-fpf-poly-b1-07' => ['guide' => ['manual']],
        'ukm-fpf-poly-b2-01' => [
            'team' => ['group'],
            'assessed' => ['evaluated'],
            'costs' => ['expenses'],
        ],
        'ukm-fpf-poly-b2-03' => [
            'team' => ['group'],
            'clause' => ['provision'],
        ],
        'ukm-fpf-poly-b2-05' => [
            'committee' => ['panel'],
            'chosen' => ['selected'],
            'supplier' => ['vendor'],
        ],
        'ukm-fpf-poly-b2-06' => ['departments' => ['divisions']],
        'ukm-fpf-poly-c1-02' => [
            'verified' => ['checked'],
            'figure' => ['number'],
        ],
        'ukm-fpf-poly-c1-03' => [
            'team' => ['group'],
            'identified' => ['determined'],
        ],
        'ukm-fpf-poly-c1-04' => ['committee' => ['panel']],
        'ukm-fpf-poly-c1-05' => [
            'examined' => ['reviewed'],
            'records' => ['documents'],
        ],
        'ukm-fpf-poly-c1-06' => ['chair' => ['chairperson']],
        'ukm-fpf-poly-c1-07' => ['bought' => ['purchased']],
        'ukm-fpf-poly-c2-01' => ['responded' => ['replied']],
        'ukm-fpf-poly-c2-02' => ['established' => ['created']],
        'ukm-fpf-poly-c2-04' => ['completed' => ['finished']],
        'ukm-fpf-poly-c2-05' => ['reconciled' => ['resolved']],
        'ukm-fpf-poly-c2-06' => ['redesigned' => ['reengineered']],

        // Negatives.
        'ukm-fpn-poly-a1-01' => ['finished' => ['completed']],
        'ukm-fpn-poly-a1-03' => [
            'cooked' => ['prepared'],
            'dinner' => ['supper'],
        ],
        'ukm-fpn-poly-a1-04' => ['contract' => ['agreement']],
        'ukm-fpn-poly-a1-05' => [
            'completed' => ['finished'],
            'task' => ['assignment'],
        ],
        'ukm-fpn-poly-a1-07' => ['deleted' => ['removed']],
        'ukm-fpn-poly-a2-04' => ['shop' => ['store']],
        'ukm-fpn-poly-a2-06' => ['completed' => ['finished']],
        'ukm-fpn-poly-a2-07' => ['amount' => ['sum']],
        'ukm-fpn-poly-b1-01' => ['repaired' => ['fixed']],
        'ukm-fpn-poly-b1-02' => [
            'manager' => ['supervisor'],
            'completed' => ['finished'],
        ],
        'ukm-fpn-poly-b1-04' => ['gathered' => ['collected']],
        'ukm-fpn-poly-b1-05' => ['team' => ['crew']],
        'ukm-fpn-poly-b1-07' => ['finished' => ['completed']],
        'ukm-fpn-poly-b2-01' => ['supplier' => ['vendor']],
        'ukm-fpn-poly-b2-02' => [
            'team' => ['group'],
            'reviewed' => ['examined'],
            'clause' => ['provision'],
        ],
        'ukm-fpn-poly-b2-05' => [
            'department' => ['division'],
            'reduced' => ['cut'],
        ],
        'ukm-fpn-poly-b2-07' => ['section' => ['area']],
        'ukm-fpn-poly-c1-01' => ['panel' => ['committee']],
        'ukm-fpn-poly-c1-03' => [
            'session,' => ['round,'],
            'resolved' => ['settled'],
        ],
        'ukm-fpn-poly-c1-04' => ['verified' => ['confirmed']],
        'ukm-fpn-poly-c1-05' => ['implemented' => ['introduced']],
        'ukm-fpn-poly-c1-06' => [
            'company' => ['firm'],
            'recovered' => ['recouped'],
        ],
        'ukm-fpn-poly-c2-01' => ['responded' => ['replied']],
        'ukm-fpn-poly-c2-02' => ['established' => ['created']],
        'ukm-fpn-poly-c2-03' => ['disclosed' => ['revealed']],
        'ukm-fpn-poly-c2-04' => ['replicated' => ['reproduced']],
        'ukm-fpn-poly-c2-05' => ['reconciled' => ['resolved']],
        'ukm-fpn-poly-c2-06' => ['redesigned' => ['reengineered']],

        // Questions.
        'ukm-fpq-poly-a1-01' => ['finished' => ['completed']],
        'ukm-fpq-poly-a1-02' => [
            'cleaned' => ['tidied'],
            'dinner' => ['supper'],
        ],
        'ukm-fpq-poly-a1-07' => ['checked' => ['reviewed']],
        'ukm-fpq-poly-a2-01' => [
            'completed' => ['finished'],
            'form' => ['application'],
        ],
        'ukm-fpq-poly-a2-04' => [
            'team' => ['crew'],
            'repaired' => ['fixed'],
        ],
        'ukm-fpq-poly-a2-05' => ['bought' => ['purchased']],
        'ukm-fpq-poly-a2-07' => [
            'shop' => ['store'],
            'parcel' => ['package'],
        ],
        'ukm-fpq-poly-b1-01' => ['tested' => ['checked', 'verified']],
        'ukm-fpq-poly-b1-02' => ['committee' => ['panel']],
        'ukm-fpq-poly-b1-04' => ['submitted' => ['sent']],
        'ukm-fpq-poly-b1-05' => [
            'team' => ['crew'],
            'reviewed' => ['examined'],
        ],
        'ukm-fpq-poly-b1-06' => ['collected' => ['gathered']],
        'ukm-fpq-poly-b1-07' => [
            'completed' => ['finished'],
            'tasks' => ['assignments'],
        ],
        'ukm-fpq-poly-b2-01' => [
            'team' => ['group'],
            'examined' => ['reviewed'],
            'clause' => ['provision'],
        ],
        'ukm-fpq-poly-b2-02' => ['learned' => ['discovered']],
        'ukm-fpq-poly-b2-03' => [
            'corrected' => ['fixed', 'resolved'],
            'issues' => ['problems'],
        ],
        'ukm-fpq-poly-b2-04' => [
            'committee' => ['panel'],
            'chosen' => ['selected'],
            'supplier' => ['vendor'],
        ],
        'ukm-fpq-poly-b2-05' => [
            'company' => ['firm'],
            'opened' => ['established'],
        ],
        'ukm-fpq-poly-b2-06' => ['chair' => ['chairperson']],
        'ukm-fpq-poly-c1-01' => ['mapped' => ['charted']],
        'ukm-fpq-poly-c1-02' => [
            'team' => ['group'],
            'discovered' => ['learned'],
        ],
        'ukm-fpq-poly-c1-03' => ['introduced' => ['implemented']],
        'ukm-fpq-poly-c1-04' => ['team' => ['crew']],
        'ukm-fpq-poly-c1-05' => ['documented' => ['recorded']],
        'ukm-fpq-poly-c2-01' => ['deciphered' => ['decoded']],
        'ukm-fpq-poly-c2-02' => ['secured' => ['obtained']],
        'ukm-fpq-poly-c2-03' => ['retrieved' => ['recovered']],
        'ukm-fpq-poly-c2-04' => ['team' => ['crew']],
        'ukm-fpq-poly-c2-05' => ['refined' => ['improved']],
        'ukm-fpq-poly-c2-06' => ['team' => ['crew']],

        // Time expressions.
        'ukm-fpte-poly-a1-01' => ['checked' => ['reviewed']],
        'ukm-fpte-poly-a1-03' => ['baked' => ['made']],
        'ukm-fpte-poly-a1-07' => [
            'team' => ['group'],
            'checked' => ['reviewed'],
        ],
        'ukm-fpte-poly-a2-02' => ['learned' => ['memorized']],
        'ukm-fpte-poly-a2-05' => [
            'team' => ['group'],
            'built' => ['created'],
        ],
        'ukm-fpte-poly-a2-07' => ['reviewed' => ['studied']],
        'ukm-fpte-poly-b1-01' => ['led' => ['conducted']],
        'ukm-fpte-poly-b1-02' => ['identified' => ['found']],
        'ukm-fpte-poly-b1-03' => ['inspected' => ['checked']],
        'ukm-fpte-poly-b1-04' => [
            'replaced' => ['changed'],
            'faulty' => ['broken'],
        ],
        'ukm-fpte-poly-b1-05' => ['processed' => ['handled']],
        'ukm-fpte-poly-b1-07' => [
            'published' => ['released'],
            'schedule' => ['timetable'],
        ],
        'ukm-fpte-poly-b2-01' => [
            'team' => ['group'],
            'issued' => ['published', 'released'],
        ],
        'ukm-fpte-poly-b2-02' => [
            'company' => ['firm'],
            'extended' => ['expanded'],
        ],
        'ukm-fpte-poly-b2-03' => ['collected' => ['gathered']],
        'ukm-fpte-poly-b2-05' => ['determined' => ['established']],
        'ukm-fpte-poly-b2-06' => [
            'chair' => ['chairperson'],
            'reviewed' => ['considered'],
        ],
        'ukm-fpte-poly-b2-07' => [
            'team' => ['group'],
            'resolved' => ['fixed'],
            'issues' => ['problems'],
        ],
        'ukm-fpte-poly-c1-02' => ['conservators' => ['restorers']],
        'ukm-fpte-poly-c1-03' => ['rerouted' => ['redirected']],
        'ukm-fpte-poly-c1-04' => [
            'department' => ['division'],
            'investigated' => ['examined'],
        ],
        'ukm-fpte-poly-c1-06' => ['mapped' => ['charted']],
        'ukm-fpte-poly-c1-07' => [
            'agency' => ['organization'],
            'positioned' => ['placed'],
            'depot' => ['warehouse'],
        ],
        'ukm-fpte-poly-c2-01' => [
            'committee' => ['panel'],
            'evaluated' => ['assessed'],
        ],
        'ukm-fpte-poly-c2-02' => ['resolved' => ['clarified']],
        'ukm-fpte-poly-c2-03' => [
            'panel' => ['committee'],
            'determined' => ['established'],
            'records' => ['documents'],
        ],
        'ukm-fpte-poly-c2-04' => [
            'panel' => ['committee'],
            'instituted' => ['implemented', 'introduced'],
        ],
        'ukm-fpte-poly-c2-05' => [
            'demonstrated' => ['shown'],
            'design' => ['structure'],
        ],
        'ukm-fpte-poly-c2-06' => ['pressure-tested' => ['stress-tested']],
        'ukm-fpte-poly-c2-07' => ['sites' => ['locations']],
    ];

    /**
     * @return array<string, array<string, array<int, string>>>
     */
    public static function catalog(): array
    {
        return self::CATALOG;
    }

    /**
     * Add display metadata and accepted lexical alternatives without
     * changing the canonical answer or the clickable option value.
     *
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    public static function decorate(array $question): array
    {
        if (! in_array((string) ($question['type'] ?? ''), ['0', '4'], true)) {
            return $question;
        }

        $uuid = trim((string) ($question['uuid'] ?? ''));
        $catalogUuid = self::catalogUuid($uuid);

        if ($catalogUuid === null) {
            return $question;
        }

        $markers = is_array($question['markers'] ?? null)
            ? array_values(array_map('strval', $question['markers']))
            : [];
        $answerMap = is_array($question['answer_map'] ?? null)
            ? $question['answer_map']
            : [];

        if ($answerMap === [] && is_array($question['answers'] ?? null)) {
            $answers = array_values($question['answers']);

            if ($markers === [] || count($markers) !== count($answers)) {
                $markers = array_map(
                    static fn (int $index): string => 'a'.($index + 1),
                    array_keys($answers)
                );
            }

            $answerMap = array_combine($markers, $answers) ?: [];
        }

        if ($markers === []) {
            $markers = array_keys($answerMap);
        }

        $existingByMarker = is_array($question['accepted_answers_by_marker'] ?? null)
            ? $question['accepted_answers_by_marker']
            : [];
        $synonymsByMarker = [];
        $synonymTokensByMarker = [];
        $acceptedByMarker = [];

        foreach ($markers as $index => $marker) {
            $answer = trim((string) ($answerMap[$marker] ?? ($question['answers'][$index] ?? '')));
            $synonymTokens = self::synonymTokensForAnswer($catalogUuid, $answer);
            $synonyms = self::uniqueValues(array_merge(...array_values($synonymTokens ?: [[]])));

            if ($synonyms !== []) {
                $synonymsByMarker[$marker] = $synonyms;
                $synonymTokensByMarker[$marker] = $synonymTokens;
            }

            $accepted = is_array($existingByMarker[$marker] ?? null)
                ? $existingByMarker[$marker]
                : [];

            foreach (self::answerVariants($answer, $synonymTokens) as $variant) {
                $accepted = array_merge($accepted, AcceptedAnswerVariants::for($variant));
            }

            $acceptedByMarker[$marker] = self::uniqueValues($accepted);
        }

        $question['accepted_answers_by_marker'] = $acceptedByMarker;
        $question['accepted_answers'] = array_map(
            static fn (string $marker): array => $acceptedByMarker[$marker] ?? [],
            $markers
        );
        $question['answer_synonyms_by_marker'] = $synonymsByMarker;
        $question['answer_synonym_tokens_by_marker'] = $synonymTokensByMarker;

        return $question;
    }

    /**
     * @return array<int, string>
     */
    public static function forToken(string $questionUuid, string $answer): array
    {
        $catalogUuid = self::catalogUuid($questionUuid);

        if ($catalogUuid === null) {
            return [];
        }

        $key = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($answer));

        return self::CATALOG[$catalogUuid][$key] ?? [];
    }

    private static function catalogUuid(string $questionUuid): ?string
    {
        $questionUuid = trim($questionUuid);

        if (isset(self::CATALOG[$questionUuid])) {
            return $questionUuid;
        }

        $builderUuid = str_replace('-v3-', '-poly-', $questionUuid);

        return isset(self::CATALOG[$builderUuid]) ? $builderUuid : null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function synonymTokensForAnswer(string $catalogUuid, string $answer): array
    {
        $available = self::CATALOG[$catalogUuid] ?? [];
        $tokens = preg_split('/\s+/u', AcceptedAnswerVariants::normalizeTypography($answer)) ?: [];
        $matched = [];

        foreach ($tokens as $token) {
            $key = mb_strtolower($token);

            if (isset($available[$key])) {
                $matched[$key] = $available[$key];
            }
        }

        return $matched;
    }

    /**
     * Generate the canonical answer plus every valid combination of its
     * independently curated token alternatives.
     *
     * @param  array<string, array<int, string>>  $synonymTokens
     * @return array<int, string>
     */
    private static function answerVariants(string $answer, array $synonymTokens): array
    {
        $variants = [$answer];

        foreach ($synonymTokens as $canonical => $synonyms) {
            $expanded = $variants;

            foreach ($variants as $variant) {
                foreach ($synonyms as $synonym) {
                    $expanded[] = self::replaceToken($variant, $canonical, $synonym);
                }
            }

            $variants = self::uniqueValues($expanded);
        }

        return $variants;
    }

    private static function replaceToken(string $answer, string $canonical, string $replacement): string
    {
        $parts = preg_split('/(\s+)/u', $answer, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$answer];

        foreach ($parts as $index => $part) {
            if (mb_strtolower(AcceptedAnswerVariants::normalizeTypography($part)) !== $canonical) {
                continue;
            }

            $parts[$index] = self::matchTokenCase($part, $replacement);
        }

        return implode('', $parts);
    }

    private static function matchTokenCase(string $canonical, string $replacement): string
    {
        $first = mb_substr($canonical, 0, 1);

        if ($first !== '' && $first === mb_strtoupper($first) && $first !== mb_strtolower($first)) {
            return mb_strtoupper(mb_substr($replacement, 0, 1)).mb_substr($replacement, 1);
        }

        return $replacement;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private static function uniqueValues(array $values): array
    {
        $unique = [];
        $seen = [];

        foreach ($values as $value) {
            $value = AcceptedAnswerVariants::normalizeTypography((string) $value);
            $key = mb_strtolower($value);

            if ($value === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $value;
        }

        return $unique;
    }
}
