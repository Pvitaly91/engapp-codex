<?php

namespace Tests\Feature;

use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use App\Support\FuturePerfectAnswerSynonyms;
use App\Support\SentenceReorderQuestionFactory;
use Tests\TestCase;

class PresentPerfectContinuousMixedUkrainianTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function test_every_ukrainian_mixed_bank_has_seven_unique_questions_per_level(): void
    {
        $targetUsage = [];
        $sourceUsage = [];
        $uuidUsage = [];

        foreach ($this->cases() as $case) {
            $original = $this->definition($case['original_standard_path']);
            $standard = $this->definition($case['standard_path']);
            $builder = $this->definition($case['builder_path']);
            $meta = $original['saved_test']['filters']['__meta'] ?? [];
            $caseTargets = [];
            $distractorSignatures = collect($builder['questions'])
                ->groupBy('level')
                ->map(static function ($questions): string {
                    $distractors = $questions
                        ->flatMap(static fn (array $question): array => $question['distractors'] ?? [])
                        ->map(static fn (mixed $token): string => mb_strtolower((string) $token))
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();

                    return implode('|', $distractors);
                });
            $this->assertCount(6, $distractorSignatures->unique(), $case['slug'].': distractors must be level-aware');
            $this->assertNotSame(
                $distractorSignatures['A1'] ?? null,
                $distractorSignatures['C2'] ?? null,
                $case['slug'].': A1 and C2 distractor banks must differ'
            );

            $this->assertTrue(
                $meta['theory_page_mixed_interleave_question_types'] ?? false,
                $case['slug'].': the type-interleave opt-in is missing'
            );
            $this->assertSame(
                [
                    $case['original_standard_class'] => $case['standard_class'],
                    $case['original_builder_class'] => $case['builder_class'],
                ],
                $meta['theory_page_mixed_locale_seeder_overrides']['uk'] ?? null,
                $case['slug'].': invalid UK-only mixed bank overrides'
            );

            foreach (['standard' => $standard, 'builder' => $builder] as $kind => $definition) {
                $questions = $definition['questions'] ?? [];
                $path = $kind === 'standard' ? $case['standard_path'] : $case['builder_path'];
                $class = $kind === 'standard' ? $case['standard_class'] : $case['builder_class'];
                $prefix = $kind === 'standard' ? $case['standard_prefix'] : $case['builder_prefix'];
                $packageDirectory = dirname($path);
                $packageName = basename($packageDirectory);

                $this->assertSame($class, $definition['seeder']['class'] ?? null);
                $this->assertFileExists($this->absolutePath($packageDirectory.'.php'));
                $this->assertFileExists($this->absolutePath($packageDirectory.'/'.$packageName.'.php'));
                $this->assertCount(42, $questions, $case['slug']." {$kind}: expected 42 questions");
                $this->assertSame(
                    array_column($questions, 'uuid'),
                    $definition['saved_test']['question_uuids'] ?? null,
                    $case['slug']." {$kind}: stale saved-test UUID list"
                );
                $this->assertSame('uk', $definition['saved_test']['filters']['__meta']['mixed_locale_only'] ?? null);

                $sourceKeys = array_keys($definition['sources'] ?? []);
                $this->assertCount(1, $sourceKeys);
                $this->assertStringStartsNotWith('theory_page_', (string) $sourceKeys[0]);

                $levelCounts = array_count_values(array_column($questions, 'level'));
                foreach (self::LEVELS as $level) {
                    $this->assertSame(7, $levelCounts[$level] ?? 0, $case['slug']." {$kind} {$level}");
                }

                foreach ($questions as $index => $question) {
                    $uuid = (string) ($question['uuid'] ?? '');
                    $level = (string) ($question['level'] ?? '');
                    $expectedUuid = sprintf('%s-%s-%02d', $prefix, strtolower($level), ($index % 7) + 1);
                    $this->assertSame($expectedUuid, $uuid);
                    $this->assertArrayNotHasKey($uuid, $uuidUsage, "Duplicate UUID {$uuid}");
                    $uuidUsage[$uuid] = true;
                    $this->assertSame($sourceKeys[0], $question['source'] ?? null, $uuid);

                    $target = $kind === 'standard'
                        ? $this->auditStandardQuestion($case['topic'], $question)
                        : $this->auditBuilderQuestion($case['topic'], $question);
                    $targetUsage[$this->normalize($target)][] = compact('kind', 'level', 'uuid') + [
                        'slug' => $case['slug'],
                    ];
                    $caseTargets[] = compact('kind', 'level', 'uuid', 'target');

                    if ($kind === 'builder') {
                        $source = $this->normalize((string) ($question['source_text_uk'] ?? $question['question'] ?? ''));
                        $sourceUsage[$source][] = $uuid;
                    }
                }
            }

            $this->assertCount(84, $caseTargets, $case['slug'].': incomplete displayed bank');
            $this->assertNoNearDuplicateTargets($caseTargets, $case['slug']);
        }

        foreach ($targetUsage as $target => $uses) {
            $this->assertCount(1, $uses, "English target is reused: {$target}");
        }
        foreach ($sourceUsage as $source => $uuids) {
            $this->assertCount(1, $uuids, "Ukrainian builder prompt is reused: {$source}");
        }

        $this->assertCount(336, $uuidUsage);
        $this->assertCount(336, $targetUsage);
        $this->assertCount(168, $sourceUsage);
    }

    public function test_the_three_question_types_are_evenly_interleaved_at_every_level(): void
    {
        $expectedOrder = [
            'gap', 'builder', 'reorder', 'builder', 'gap', 'builder', 'reorder',
            'builder', 'gap', 'builder', 'reorder', 'builder', 'gap', 'builder',
        ];

        foreach ($this->cases() as $case) {
            $standard = collect($this->definition($case['standard_path'])['questions'])->groupBy('level');
            $builder = collect($this->definition($case['builder_path'])['questions'])->groupBy('level');
            $questions = [];

            foreach (self::LEVELS as $level) {
                for ($index = 0; $index < 7; $index++) {
                    $gap = $standard[$level][$index];
                    $gap['type'] = '0';
                    $gap['answer_map'] = array_map(
                        static fn (array $marker): string => (string) ($marker['answer'] ?? ''),
                        $gap['markers']
                    );
                    $questions[] = $gap;
                    $questions[] = $builder[$level][$index];
                }
            }

            $presented = collect(SentenceReorderQuestionFactory::addToMixedTheoryTest($questions, [
                '__meta' => [
                    'theory_page_mixed_polyglot_test' => true,
                    'theory_page_mixed_interleave_question_types' => true,
                ],
            ]));

            foreach (self::LEVELS as $level) {
                $actual = $presented->where('level', $level)->values()->map(
                    static function (array $question): string {
                        if ((string) ($question['type'] ?? '') === '4') {
                            return 'builder';
                        }

                        return ($question['presentation'] ?? null) === SentenceReorderQuestionFactory::PRESENTATION
                            ? 'reorder'
                            : 'gap';
                    }
                )->all();

                $this->assertSame($expectedOrder, $actual, $case['slug']." {$level}");
                $this->assertSame(7, count(array_keys($actual, 'builder', true)));
                $this->assertSame(4, count(array_keys($actual, 'gap', true)));
                $this->assertSame(3, count(array_keys($actual, 'reorder', true)));
            }
        }
    }

    public function test_present_perfect_continuous_synonyms_are_context_scoped_and_cover_gap_answers(): void
    {
        $catalog = FuturePerfectAnswerSynonyms::presentPerfectContinuousCatalog();
        $builders = [];
        $standards = [];

        foreach ($this->cases() as $case) {
            foreach ($this->definition($case['builder_path'])['questions'] as $question) {
                $builders[$question['uuid']] = $question;
            }
            foreach ($this->definition($case['standard_path'])['questions'] as $question) {
                $standards[$question['uuid']] = $question;
            }
        }

        $this->assertGreaterThanOrEqual(35, count($catalog));
        foreach ($catalog as $uuid => $tokens) {
            $this->assertArrayHasKey($uuid, $builders, $uuid);
            $gapUuid = str_replace('-poly-', '-v3-', $uuid);
            $this->assertArrayHasKey($gapUuid, $standards, $gapUuid);
            $builderTokens = $this->lexicalTokens(implode(' ', array_values($builders[$uuid]['answers'] ?? [])));
            $gapTokens = array_map(
                fn (string $token): string => $this->lexicalToken($token),
                preg_split('/\s+/u', (string) ($standards[$gapUuid]['markers']['a1']['answer'] ?? '')) ?: []
            );

            foreach ($tokens as $canonical => $synonyms) {
                $this->assertSame($this->lexicalToken($canonical), $canonical, "{$uuid}: punctuated canonical label");
                $this->assertTrue(
                    in_array($canonical, $builderTokens, true) || in_array($canonical, $gapTokens, true),
                    "{$uuid}: missing canonical token {$canonical}"
                );
                $this->assertNotEmpty($synonyms);
                $this->assertSame($synonyms, array_values(array_unique($synonyms)));
                foreach ($synonyms as $synonym) {
                    $this->assertSame($this->lexicalToken($synonym), $synonym, "{$uuid}: punctuated synonym label");
                    $this->assertNotSame($canonical, mb_strtolower($synonym));
                }
            }
        }

        $formGap = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-ppcf-v3-a1-01',
            'type' => 0,
            'markers' => ['a1'],
            'answers' => ['have been studying'],
            'answer_map' => ['a1' => 'have been studying'],
        ]);
        $this->assertContains('have been learning', $formGap['accepted_answers_by_marker']['a1']);
        $this->assertSame(['studying' => ['learning']], $formGap['answer_synonym_tokens_by_marker']['a1']);

        $timeGap = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-ppcte-v3-a1-04',
            'type' => 0,
            'markers' => ['a1'],
            'answers' => ['recently'],
            'answer_map' => ['a1' => 'recently'],
        ]);
        $this->assertContains('lately', $timeGap['accepted_answers_by_marker']['a1']);

        $punctuatedBuilder = FuturePerfectAnswerSynonyms::decorate([
            'uuid' => 'ukm-ppcte-poly-a1-04',
            'type' => 4,
            'markers' => ['a1'],
            'answers' => ['lately.'],
            'answer_map' => ['a1' => 'lately.'],
        ]);
        $this->assertContains('recently.', $punctuatedBuilder['accepted_answers_by_marker']['a1']);
        $this->assertSame(['recently'], $punctuatedBuilder['answer_synonyms_by_marker']['a1']);
        $this->assertSame(
            ['lately' => ['recently']],
            $punctuatedBuilder['answer_synonym_tokens_by_marker']['a1']
        );
        $this->assertSame(
            ['recently'],
            FuturePerfectAnswerSynonyms::forToken('ukm-ppcte-poly-a1-04', 'lately.')
        );

        $unrelated = [
            'uuid' => 'unrelated-present-perfect-continuous-question',
            'type' => 4,
            'markers' => ['a1'],
            'answers' => ['team'],
            'answer_map' => ['a1' => 'team'],
        ];
        $this->assertSame($unrelated, FuturePerfectAnswerSynonyms::decorate($unrelated));
    }

    private function auditStandardQuestion(string $topic, array $question): string
    {
        $uuid = (string) ($question['uuid'] ?? '');
        $stem = (string) ($question['question'] ?? '');
        $markers = $question['markers'] ?? [];

        $this->assertSame(['a1'], array_keys($markers), $uuid);
        $this->assertSame(1, substr_count($stem, '{a1}'), $uuid);
        $this->assertMatchesRegularExpression('/[.!?]$/u', $stem, $uuid);
        $this->assertSame([$stem], $question['variants'] ?? null, $uuid);
        $this->assertNoTechnicalText($stem, $uuid);
        $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $stem, $uuid);

        $marker = $markers['a1'];
        $answer = trim((string) ($marker['answer'] ?? ''));
        $options = array_values(array_map('strval', $marker['options'] ?? []));
        $hint = trim((string) ($marker['verb_hint'] ?? ''));
        $this->assertNotSame('', $answer, $uuid);
        $this->assertSame($answer, $options[0] ?? null, $uuid);
        $this->assertSame(
            count($options),
            count(array_unique(array_map(fn (string $option): string => $this->normalize($option), $options))),
            $uuid.': duplicate options'
        );
        $this->assertCount(match ((string) ($question['level'] ?? '')) {
            'A1', 'A2' => 4,
            'B1', 'B2' => 5,
            'C1', 'C2' => 6,
            default => 0,
        }, $options, $uuid.': distractor count must scale with CEFR level');
        $this->assertDoesNotMatchRegularExpression(
            '/\b(?:have|has)\s+being\b|\b(?:do|does|did)\s+not\s+been\b|\b(?:am|is|are)\s+[^|]+?\s+been\b/iu',
            implode(' | ', $options),
            $uuid.': malformed distractor'
        );
        $this->assertOptionSubjectAgreement($topic, $stem, $answer, $options, $uuid);
        $this->assertNotSame('', $hint, $uuid);
        $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $hint, $uuid);
        $this->assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $hint, $uuid);
        $this->assertLessThanOrEqual(255, mb_strlen($hint), $uuid);
        $this->assertStringNotContainsString('Підмет:', $hint, $uuid);
        $this->assertStringNotContainsString('Базове дієслово', $hint, $uuid);
        $this->assertNoTechnicalText($hint, $uuid);

        if (in_array($topic, ['forms', 'negatives'], true)) {
            $this->assertStringNotContainsString('підмет', mb_strtolower($hint), $uuid);
        }

        $completed = str_replace('{a1}', $answer, $stem);
        $this->auditTarget($topic, $completed, $answer, $uuid);

        return $completed;
    }

    private function auditBuilderQuestion(string $topic, array $question): string
    {
        $uuid = (string) ($question['uuid'] ?? '');
        $answers = is_array($question['answers'] ?? null) ? $question['answers'] : [];
        $tokens = array_values(array_map('strval', $answers));
        $target = trim((string) ($question['target_text'] ?? ''));
        $source = trim((string) ($question['question'] ?? ''));
        $expectedMarkers = array_map(static fn (int $index): string => 'a'.($index + 1), array_keys($tokens));

        $this->assertSame('4', (string) ($question['type'] ?? ''), $uuid);
        $this->assertSame($expectedMarkers, array_keys($answers), $uuid);
        $this->assertSame($tokens, $question['tokens_correct'] ?? null, $uuid);
        $this->assertSame($this->normalize($target), $this->normalize(implode(' ', $tokens)), $uuid);
        $this->assertMatchesRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $source, $uuid);
        $this->assertMatchesRegularExpression('/[.!?]$/u', $source, $uuid);
        $this->assertMatchesRegularExpression('/[.!?]$/u', $target, $uuid);
        $this->assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄєҐґ]/u', $target, $uuid);
        $this->assertNoTechnicalText($source, $uuid);
        $this->assertNoTechnicalText($target, $uuid);

        // Production options are case-insensitively unique. The runtime bank
        // must nevertheless begin with every normalized answer token and keep
        // duplicates such as repeated articles or differently cased pronouns.
        $expectedRuntime = ComposeTokenCase::normalize($tokens);
        $runtime = ComposeTokenCase::mergeOptions($question['options'] ?? [], $tokens);
        $this->assertSame($expectedRuntime, array_slice($runtime, 0, count($expectedRuntime)), $uuid);
        foreach (array_count_values($expectedRuntime) as $token => $count) {
            $this->assertGreaterThanOrEqual($count, array_count_values($runtime)[$token] ?? 0, "{$uuid}: {$token}");
        }

        $correct = array_fill_keys(array_map(fn (string $token): string => $this->normalize($token), $tokens), true);
        foreach ($question['distractors'] ?? [] as $distractor) {
            $this->assertArrayNotHasKey($this->normalize((string) $distractor), $correct, $uuid);
        }

        $this->auditTarget($topic, $target, $target, $uuid);

        return $target;
    }

    private function auditTarget(string $topic, string $completed, string $answer, string $uuid): void
    {
        $normalized = AcceptedAnswerVariants::normalizeTypography($completed);

        if ($topic === 'forms') {
            $this->assertMatchesRegularExpression('/\b(?:have|has)\s+been\s+(?:\p{L}+ly\s+)*[\p{L}-]+ing\b/iu', $normalized, $uuid);
            $this->assertDoesNotMatchRegularExpression('/\bnot\b|n[’\']t\b/iu', $answer, $uuid);
        } elseif ($topic === 'negatives') {
            $this->assertMatchesRegularExpression(
                '/\b(?:have\s+not|has\s+not|haven[’\']t|hasn[’\']t)\s+been\s+(?:\p{L}+ly\s+)*[\p{L}-]+ing\b/iu',
                $normalized,
                $uuid
            );
        } elseif ($topic === 'questions') {
            $this->assertStringEndsWith('?', trim($completed), $uuid);
            $this->assertMatchesRegularExpression(
                '/^(?:(?:What|Where|Why|How long|Which(?:\s+\p{L}+){1,3})\s+)?(?:have|has)\s+.+?\s+been\s+(?:\p{L}+ly\s+)*[\p{L}-]+ing\b/iu',
                $normalized,
                $uuid
            );
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:Have|Has)\s+(?:She|He|It|We|You|They|The|My)\b/u',
                $completed,
                $uuid
            );
            if (preg_match('/^(What|Where|Why|Which|How long)\b/iu', $completed, $matches) === 1) {
                $this->assertMatchesRegularExpression('/^'.preg_quote($matches[1], '/').'\b/iu', (string) ($completed));
                if (str_contains($uuid, '-v3-')) {
                    $this->assertDoesNotMatchRegularExpression('/^(?:What|Where|Why|Which|How)\b/iu', $answer, $uuid);
                }
            }
        } else {
            $this->assertMatchesRegularExpression('/\b(?:have|has)\s+been\s+(?:\p{L}+ly\s+)*[\p{L}-]+ing\b/iu', $normalized, $uuid);
            if (str_contains($uuid, '-v3-')) {
                $this->assertDoesNotMatchRegularExpression('/\b(?:have|has|been|will)\b/iu', $answer, $uuid);
            }
            $this->assertDoesNotMatchRegularExpression('/Use the process form|process form|службов/u', $completed, $uuid);
        }
    }

    private function assertNoTechnicalText(string $text, string $uuid): void
    {
        $this->assertDoesNotMatchRegularExpression('/<\/?[a-z][^>]*>|&(?:#\d+|#x[0-9a-f]+|[a-z]+);/iu', $text, $uuid);
        $this->assertDoesNotMatchRegularExpression('/\{(?!a1\})[^{}]+\}/u', $text, $uuid);
    }

    /** @param array<int, string> $options */
    private function assertOptionSubjectAgreement(
        string $topic,
        string $stem,
        string $answer,
        array $options,
        string $uuid
    ): void {
        if ($topic === 'time-expressions') {
            return;
        }

        if ($topic === 'questions') {
            $this->assertMatchesRegularExpression('/^(have|has)\s+(.+?)\s+been\s+/iu', $answer, $uuid);
            preg_match('/^(have|has)\s+(.+?)\s+been\s+/iu', $answer, $matches);
            $expectedPerfect = mb_strtolower($matches[1]);
            $subject = mb_strtolower($matches[2]);
            $expectedPresentBe = $subject === 'i' ? 'am' : ($expectedPerfect === 'has' ? 'is' : 'are');
            $expectedPastBe = $expectedPerfect === 'has' || $subject === 'i' ? 'was' : 'were';

            foreach ($options as $option) {
                $normalized = mb_strtolower($option);

                if (preg_match('/^(have|has)\b/u', $normalized, $optionAux) === 1) {
                    $this->assertSame($expectedPerfect, $optionAux[1], "{$uuid}: {$option}");
                }
                if (preg_match('/^(am|is|are)\b/u', $normalized, $optionAux) === 1) {
                    $this->assertSame($expectedPresentBe, $optionAux[1], "{$uuid}: {$option}");
                }
                if (preg_match('/^(was|were)\b/u', $normalized, $optionAux) === 1) {
                    $this->assertSame($expectedPastBe, $optionAux[1], "{$uuid}: {$option}");
                }
            }

            return;
        }

        $expectedPerfect = preg_match('/^has(?:\s|n[’\']t)/iu', $answer) === 1 ? 'has' : 'have';
        $subject = trim((string) strstr($stem, '{a1}', true));
        $expectedPresentBe = $subject === 'I' ? 'am' : ($expectedPerfect === 'has' ? 'is' : 'are');
        $expectedPastBe = $expectedPerfect === 'has' || $subject === 'I' ? 'was' : 'were';

        foreach ($options as $option) {
            $normalized = mb_strtolower($option);

            if (preg_match('/^(have|has)(?:\s|n[’\']t)/u', $normalized, $optionAux) === 1) {
                $this->assertSame($expectedPerfect, $optionAux[1], "{$uuid}: {$option}");
            }
            if (preg_match('/^(am|is|are)\b/u', $normalized, $optionAux) === 1) {
                $this->assertSame($expectedPresentBe, $optionAux[1], "{$uuid}: {$option}");
            }
            if (preg_match('/^(was|were)\b/u', $normalized, $optionAux) === 1) {
                $this->assertSame($expectedPastBe, $optionAux[1], "{$uuid}: {$option}");
            }

            $completed = str_replace('{a1}', $option, $stem);
            $this->assertDoesNotMatchRegularExpression(
                '/\bI\s+(?:has|is|are|were)\b|\b(?:Has|Is|Are|Were)\s+I\b/u',
                $completed,
                "{$uuid}: {$option}"
            );
        }
    }

    /** @param array<int, array{kind: string, level: string, uuid: string, target: string}> $records */
    private function assertNoNearDuplicateTargets(array $records, string $bank): void
    {
        $count = count($records);

        for ($leftIndex = 0; $leftIndex < $count; $leftIndex++) {
            for ($rightIndex = $leftIndex + 1; $rightIndex < $count; $rightIndex++) {
                $left = $this->normalize($records[$leftIndex]['target']);
                $right = $this->normalize($records[$rightIndex]['target']);
                similar_text($left, $right, $characterSimilarity);

                if ($characterSimilarity < 88.0) {
                    continue;
                }

                $leftTokens = $this->contentTokens($left);
                $rightTokens = $this->contentTokens($right);
                $union = array_unique(array_merge($leftTokens, $rightTokens));
                $intersection = array_intersect($leftTokens, $rightTokens);
                $jaccard = $union === [] ? 0.0 : count(array_unique($intersection)) / count($union);

                $this->assertLessThan(
                    0.70,
                    $jaccard,
                    sprintf(
                        '%s: near-duplicate targets %s and %s (%.1f%% chars, %.2f tokens)',
                        $bank,
                        $records[$leftIndex]['uuid'],
                        $records[$rightIndex]['uuid'],
                        $characterSimilarity,
                        $jaccard
                    )
                );
            }
        }
    }

    /** @return array<int, string> */
    private function contentTokens(string $text): array
    {
        $stopWords = array_fill_keys([
            'a', 'all', 'an', 'and', 'been', 'for', 'has', 'hasn', 'have', 'haven', 'he', 'her', 'his',
            'how', 'i', 'in', 'it', 'lately', 'long', 'my', 'not', 'our', 'recently', 'she', 'since',
            'that', 'the', 'their', 'they', 'this', 'to', 'we', 'what', 'where', 'which', 'why', 'you', 'your',
        ], true);

        return array_values(array_unique(array_filter(
            $this->lexicalTokens($text),
            static fn (string $token): bool => ! isset($stopWords[$token])
        )));
    }

    /** @return array<int, string> */
    private function lexicalTokens(string $text): array
    {
        $tokens = preg_split('/\s+/u', AcceptedAnswerVariants::normalizeTypography($text)) ?: [];

        return array_values(array_filter(array_map(
            fn (string $token): string => $this->lexicalToken($token),
            $tokens
        )));
    }

    private function lexicalToken(string $token): string
    {
        $token = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($token));

        return preg_replace('/^[^\pL\pN]+|[^\pL\pN]+$/u', '', $token) ?? $token;
    }

    private function complete(array $question): string
    {
        $result = (string) ($question['question'] ?? '');
        foreach ($question['markers'] ?? [] as $marker => $payload) {
            $result = str_replace('{'.$marker.'}', (string) ($payload['answer'] ?? ''), $result);
        }

        return $result;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($value));

        return trim(preg_replace('/[^\pL\pN\']+/u', ' ', $value) ?? $value);
    }

    private function definition(string $relativePath): array
    {
        return json_decode(
            (string) file_get_contents($this->absolutePath($relativePath)),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function absolutePath(string $relativePath): string
    {
        return dirname(__DIR__, 2).'/'.$relativePath;
    }

    /** @return array<int, array<string, string>> */
    private function cases(): array
    {
        return [
            $this->caseDefinition('forms', 'ppcf', 'Forms'),
            $this->caseDefinition('negatives', 'ppcn', 'Negatives'),
            $this->caseDefinition('questions', 'ppcq', 'Questions'),
            $this->caseDefinition('time-expressions', 'ppcte', 'TimeExpressions'),
        ];
    }

    /** @return array<string, string> */
    private function caseDefinition(string $slug, string $prefix, string $topic): array
    {
        $standardStem = "PresentPerfectContinuous{$topic}AllLevelsV3Seeder";
        $builderStem = "PolyglotPresentPerfectContinuous{$topic}AllLevelsLessonSeeder";
        $standardName = "UkrainianMixedPresentPerfectContinuous{$topic}StandardSeeder";
        $builderName = "UkrainianMixedPresentPerfectContinuous{$topic}BuilderSeeder";

        return [
            'slug' => "present-perfect-continuous/{$slug}",
            'topic' => $slug,
            'original_standard_path' => "database/seeders/V3/Tenses/PresentPerfectContinuous/{$standardStem}/definition.json",
            'original_builder_path' => "database/seeders/V3/Polyglot/{$builderStem}/definition.json",
            'standard_path' => "database/seeders/V3/Tenses/PresentPerfectContinuous/{$standardName}/definition.json",
            'builder_path' => "database/seeders/V3/Polyglot/{$builderName}/definition.json",
            'original_standard_class' => "Database\\Seeders\\V3\\Tenses\\PresentPerfectContinuous\\{$standardStem}",
            'original_builder_class' => "Database\\Seeders\\V3\\Polyglot\\{$builderStem}",
            'standard_class' => "Database\\Seeders\\V3\\Tenses\\PresentPerfectContinuous\\{$standardName}",
            'builder_class' => "Database\\Seeders\\V3\\Polyglot\\{$builderName}",
            'standard_prefix' => "ukm-{$prefix}-v3",
            'builder_prefix' => "ukm-{$prefix}-poly",
        ];
    }
}
