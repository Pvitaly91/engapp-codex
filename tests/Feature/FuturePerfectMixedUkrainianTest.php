<?php

namespace Tests\Feature;

use App\Models\SavedGrammarTest;
use App\Services\SavedTestResolver;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\AcceptedAnswerVariants;
use App\Support\ComposeTokenCase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FuturePerfectMixedUkrainianTest extends TestCase
{
    private const LEVELS = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    private const DIFFICULTIES = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function test_all_ukrainian_future_perfect_mixed_banks_are_isolated_unique_and_unambiguous(): void
    {
        $cases = $this->cases();
        $this->assertSame(
            [
                'future-perfect/forms',
                'future-perfect/negatives',
                'future-perfect/questions',
                'future-perfect/time-expressions',
            ],
            array_column($cases, 'slug')
        );

        $violations = [];
        $uuidUsage = [];
        $targetUsage = [];

        foreach ($cases as $case) {
            $originalStandard = $this->definition($case['original_standard_path']);
            $originalBuilder = $this->definition($case['original_builder_path']);
            $standard = $this->definition($case['standard_path']);
            $builder = $this->definition($case['builder_path']);
            $manifest = $this->definition($case['manifest_path']);

            $this->assertSame(
                $case['original_standard_questions_hash'],
                $this->questionsHash($originalStandard),
                $case['slug'].': the standalone standard bank content changed'
            );
            $this->assertSame(
                $case['original_builder_questions_hash'],
                $this->questionsHash($originalBuilder),
                $case['slug'].': the theory Sentence Builder bank content changed'
            );

            $this->assertSame($case['page_slug'], $manifest['page']['slug'] ?? null);
            $this->assertSame($case['standard_class'], $standard['seeder']['class'] ?? null);
            $this->assertSame($case['builder_class'], $builder['seeder']['class'] ?? null);

            $expectedOverrides = [
                $case['original_standard_class'] => $case['standard_class'],
                $case['original_builder_class'] => $case['builder_class'],
            ];
            $this->assertSame(
                $expectedOverrides,
                $originalStandard['saved_test']['filters']['__meta']['theory_page_mixed_locale_seeder_overrides']['uk'] ?? null,
                $case['slug'].': invalid UK-only seeder override'
            );

            foreach ([
                'standard' => [$standard, $case['standard_uuid_prefix']],
                'builder' => [$builder, $case['builder_uuid_prefix']],
            ] as $kind => [$definition, $uuidPrefix]) {
                $definitionPath = $kind === 'standard' ? $case['standard_path'] : $case['builder_path'];
                $packageDirectory = dirname($definitionPath);
                $packageName = basename($packageDirectory);
                $loaderPath = $this->absolutePath($packageDirectory.'.php');
                $this->assertFileExists($loaderPath);
                $this->assertFileExists($this->absolutePath($packageDirectory.'/'.$packageName.'.php'));
                $this->assertStringContainsString(
                    "require_once __DIR__ . '/{$packageName}/{$packageName}.php';",
                    (string) file_get_contents($loaderPath)
                );
                $this->assertFileDoesNotExist($this->absolutePath($packageDirectory.'/localizations/en.json'));
                $this->assertFileDoesNotExist($this->absolutePath($packageDirectory.'/localizations/pl.json'));

                $questions = $definition['questions'] ?? [];
                $this->assertCount(42, $questions, $case['slug']." {$kind}: expected 42 questions");
                $this->assertSame(
                    array_column($questions, 'uuid'),
                    $definition['saved_test']['question_uuids'] ?? null,
                    $case['slug']." {$kind}: saved-test links are stale"
                );
                $this->assertArrayNotHasKey('prompt_generator', $definition['saved_test']['filters'] ?? []);
                $this->assertSame('uk', $definition['saved_test']['filters']['__meta']['mixed_locale_only'] ?? null);

                $sourceKeys = array_keys($definition['sources'] ?? []);
                $this->record(count($sourceKeys) === 1, $violations, $case['slug']." {$kind}: expected one source key");
                $this->record(
                    ! str_starts_with((string) ($sourceKeys[0] ?? ''), 'theory_page_'),
                    $violations,
                    $case['slug']." {$kind}: internal bank must not auto-link as a theory package"
                );

                $levelCounts = array_count_values(array_column($questions, 'level'));
                foreach (self::LEVELS as $level) {
                    $this->record(
                        ($levelCounts[$level] ?? 0) === 7,
                        $violations,
                        $case['slug']." {$kind}: expected 7 {$level} questions"
                    );
                }

                foreach ($questions as $index => $question) {
                    $uuid = (string) ($question['uuid'] ?? '');
                    $level = (string) ($question['level'] ?? '');
                    $expectedUuid = sprintf('%s-%s-%02d', $uuidPrefix, strtolower($level), ($index % 7) + 1);
                    $difficulty = (int) ($question['difficulty'] ?? ($definition['defaults']['level_difficulty'][$level] ?? 0));

                    $this->record($uuid === $expectedUuid, $violations, "Unexpected deterministic UUID {$uuid}");
                    $this->record($difficulty === (self::DIFFICULTIES[$level] ?? 0), $violations, "{$uuid}: wrong difficulty");
                    $this->record(($question['source'] ?? null) === ($sourceKeys[0] ?? null), $violations, "{$uuid}: unresolved source key");
                    $uuidUsage[$uuid][$case['slug']][$level] = true;

                    if ($kind === 'standard') {
                        $target = $this->auditStandardQuestion($case['topic'], $question, $violations);
                    } else {
                        $target = $this->auditBuilderQuestion($case['topic'], $question, $violations);
                    }

                    $targetUsage[$this->normalize($target)][] = [
                        'slug' => $case['slug'],
                        'level' => $level,
                        'kind' => $kind,
                        'uuid' => $uuid,
                    ];
                }
            }
        }

        foreach ($uuidUsage as $uuid => $uses) {
            $usedLevels = [];
            foreach ($uses as $levels) {
                $usedLevels = array_merge($usedLevels, array_keys($levels));
            }
            $levelCount = count(array_unique($usedLevels));
            $this->record($levelCount === 1, $violations, "{$uuid}: UUID is reused across levels");
        }

        foreach ($targetUsage as $target => $uses) {
            $levelKeys = array_values(array_unique(array_map(
                static fn (array $use): string => $use['slug'].'|'.$use['level'],
                $uses
            )));
            $this->record(
                count($levelKeys) === 1,
                $violations,
                "Completed target is duplicated across tests or levels: {$target} (".
                    implode(', ', array_column($uses, 'uuid')).')'
            );
        }

        $this->assertSame([], $violations, implode("\n", $violations));
        $this->assertCount(336, $uuidUsage, 'Expected 4 tests × 84 unique question UUIDs');
    }

    public function test_uk_locale_uses_dedicated_seeders_while_en_pl_and_unconfigured_tests_keep_originals(): void
    {
        $service = new class extends TheoryPagePromptLinkedTestsService
        {
            public function overrides(Collection $seeders, Collection $tests, string $locale): Collection
            {
                return $this->applyLocaleMixedSeederOverrides($seeders, $tests, $locale);
            }
        };

        foreach ($this->cases() as $case) {
            $original = collect([$case['original_standard_class'], $case['original_builder_class']]);
            $expectedUk = [$case['standard_class'], $case['builder_class']];
            $standardDefinition = $this->definition($case['original_standard_path']);

            $standardTest = new SavedGrammarTest;
            $standardTest->setAttribute('filters', $standardDefinition['saved_test']['filters']);
            $builderTest = new SavedGrammarTest;
            $builderTest->setAttribute('filters', ['seeder_classes' => [$case['original_builder_class']]]);
            $linkedTests = collect([$standardTest, $builderTest]);

            $this->assertSame($expectedUk, $service->overrides($original, $linkedTests, 'uk')->all());
            $this->assertSame($expectedUk, $service->overrides($original, $linkedTests, 'ua')->all());
            $this->assertSame($original->all(), $service->overrides($original, $linkedTests, 'en')->all());
            $this->assertSame($original->all(), $service->overrides($original, $linkedTests, 'pl')->all());

            $unconfigured = new SavedGrammarTest;
            $unconfigured->setAttribute('filters', ['seeder_classes' => [$case['original_standard_class']]]);
            $this->assertSame(
                $original->all(),
                $service->overrides($original, collect([$unconfigured, $builderTest]), 'uk')->all()
            );
        }
    }

    public function test_cached_static_mixed_filters_are_refreshed_from_the_locale_aware_page_definition(): void
    {
        $resolver = new class extends SavedTestResolver
        {
            public function __construct() {}

            public function shouldRefresh(array $filters): bool
            {
                return $this->shouldResolveFreshTheoryPageMixedTest($filters);
            }
        };

        $this->assertTrue($resolver->shouldRefresh([
            '__meta' => [
                'theory_page_static_slug' => true,
                'theory_page_mixed_all_levels_test' => true,
            ],
        ]));
        $this->assertFalse($resolver->shouldRefresh([
            '__meta' => ['theory_page_static_slug' => true],
        ]));
        $this->assertFalse($resolver->shouldRefresh([
            '__meta' => ['theory_page_mixed_all_levels_test' => true],
        ]));
    }

    private function auditStandardQuestion(string $topic, array $question, array &$violations): string
    {
        $uuid = (string) ($question['uuid'] ?? '');
        $stem = (string) ($question['question'] ?? '');
        $markers = $question['markers'] ?? [];
        $this->record(array_keys($markers) === ['a1'], $violations, "{$uuid}: expected exactly marker a1");
        $this->record(substr_count($stem, '{a1}') === 1, $violations, "{$uuid}: marker count is not one");
        $this->record(preg_match('/[.!?]$/u', $stem) === 1, $violations, "{$uuid}: missing terminal punctuation");
        $this->record(($question['variants'] ?? null) === [$stem], $violations, "{$uuid}: stale variant");
        $this->record(preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $stem) !== 1, $violations, "{$uuid}: Cyrillic in English stem");

        $marker = is_array($markers['a1'] ?? null) ? $markers['a1'] : [];
        $answer = trim((string) ($marker['answer'] ?? ''));
        $options = array_values(array_map('strval', $marker['options'] ?? []));
        $normalizedOptions = array_map(fn (string $option): string => $this->normalize($option), $options);
        $accepted = array_map(fn (string $option): string => $this->normalize($option), AcceptedAnswerVariants::for($answer));

        $this->record($answer !== '', $violations, "{$uuid}: empty answer");
        $this->record(($options[0] ?? null) === $answer, $violations, "{$uuid}: correct answer is not first");
        $this->record(count($normalizedOptions) === count(array_unique($normalizedOptions)), $violations, "{$uuid}: duplicate options");
        $this->record(count(array_filter($normalizedOptions, fn (string $option): bool => $option === $this->normalize($answer))) === 1, $violations, "{$uuid}: answer must occur exactly once");
        foreach (array_slice($normalizedOptions, 1) as $distractor) {
            $this->record(! in_array($distractor, $accepted, true), $violations, "{$uuid}: accepted answer variant used as distractor");
        }

        $hint = trim((string) ($marker['verb_hint'] ?? ''));
        $pattern = match ($topic) {
            'negatives' => '/^Підмет: «([^»]+)»\. Базове дієслово із запереченням: «не ([^»]+)»\.$/u',
            'questions' => '/^Підмет у запитанні: «([^»]+)»\. Базове дієслово: «([^»]+)»\.$/u',
            default => '/^Підмет: «([^»]+)»\. Базове дієслово: «([^»]+)»\.$/u',
        };
        $matches = [];
        $hintMatches = preg_match($pattern, $hint, $matches) === 1;
        $this->record($hintMatches, $violations, "{$uuid}: hint lacks subject/base verb or question/negative cue");
        $this->record(mb_strlen($hint) <= 255, $violations, "{$uuid}: hint exceeds 255 characters");
        $this->record(! str_contains($this->normalize($hint), $this->normalize($answer)), $violations, "{$uuid}: hint leaks full answer");
        $this->record(preg_match('/[A-Za-z]/u', $hint) !== 1, $violations, "{$uuid}: hint contains English text");

        $completed = str_replace('{a1}', $answer, $stem);
        if ($hintMatches) {
            $subject = (string) $matches[1];
            $baseVerb = (string) $matches[2];
            $this->record(
                preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $subject) === 1,
                $violations,
                "{$uuid}: hinted subject is not Ukrainian"
            );
            $this->record(
                preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $baseVerb) === 1,
                $violations,
                "{$uuid}: hinted base verb is not Ukrainian"
            );
        }

        $this->auditTopicTarget($topic, $completed, $answer, $uuid, $violations, true);

        return $completed;
    }

    private function auditBuilderQuestion(string $topic, array $question, array &$violations): string
    {
        $uuid = (string) ($question['uuid'] ?? '');
        $answers = is_array($question['answers'] ?? null) ? $question['answers'] : [];
        $tokens = array_values(array_map('strval', $answers));
        $expectedMarkers = array_map(static fn (int $index): string => 'a'.($index + 1), array_keys($tokens));
        $target = trim((string) ($question['target_text'] ?? ''));
        $source = trim((string) ($question['question'] ?? ''));

        $this->record((string) ($question['type'] ?? '') === '4', $violations, "{$uuid}: builder type must be 4");
        $this->record(array_keys($answers) === $expectedMarkers, $violations, "{$uuid}: non-contiguous answer markers");
        $this->record($tokens === ($question['tokens_correct'] ?? null), $violations, "{$uuid}: tokens_correct is stale");
        $this->record($this->normalize(implode(' ', $tokens)) === $this->normalize($target), $violations, "{$uuid}: answers do not reconstruct target_text");
        $this->record(preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $source) === 1, $violations, "{$uuid}: missing Ukrainian source prompt");
        $this->record(preg_match('/[.!?]$/u', $source) === 1, $violations, "{$uuid}: source prompt lacks punctuation");
        $this->record(preg_match('/[А-Яа-яІіЇїЄєҐґ]/u', $target) !== 1, $violations, "{$uuid}: Cyrillic in English target");

        $runtimeBank = ComposeTokenCase::mergeOptions($question['options'] ?? [], $tokens);
        $rawOptions = array_values(array_map('strval', $question['options'] ?? []));
        $normalizedRawOptions = array_map(fn (string $option): string => $this->normalize($option), $rawOptions);
        $this->record(
            count($normalizedRawOptions) === count(array_unique($normalizedRawOptions)),
            $violations,
            "{$uuid}: raw token options are duplicated case-insensitively"
        );
        $this->record(
            array_slice($runtimeBank, 0, count($tokens)) === ComposeTokenCase::normalize($tokens),
            $violations,
            "{$uuid}: runtime token bank loses correct-token multiplicity"
        );
        $correctLookup = array_fill_keys(array_map(fn (string $token): string => $this->normalize($token), $tokens), true);
        foreach ($question['distractors'] ?? [] as $distractor) {
            $this->record(
                ! isset($correctLookup[$this->normalize((string) $distractor)]),
                $violations,
                "{$uuid}: distractor duplicates a correct token"
            );
        }

        $this->auditTopicTarget($topic, $target, $target, $uuid, $violations);

        return $target;
    }

    private function auditTopicTarget(
        string $topic,
        string $completed,
        string $answer,
        string $uuid,
        array &$violations,
        bool $requireQuestionMark = false
    ): void
    {
        $normalizedAnswer = AcceptedAnswerVariants::normalizeTypography($answer);
        $this->record(
            preg_match('/(?:\bwill\b.*\bhave\b|\bwon[’\']?t\s+have\b)/iu', $normalizedAnswer) === 1,
            $violations,
            "{$uuid}: main answer is not Future Perfect"
        );
        $this->record(
            preg_match('/\b(?:will(?:\s+not)?|won[’\']?t)\s+have\s+been\s+\p{L}+ing\b/iu', $normalizedAnswer) !== 1,
            $violations,
            "{$uuid}: Future Perfect Continuous used instead of Future Perfect"
        );

        if ($topic === 'forms') {
            $this->record(preg_match('/\bwill\s+have\b/iu', $normalizedAnswer) === 1, $violations, "{$uuid}: invalid affirmative form");
            $this->record(preg_match('/\bnot\b|won[’\']t/iu', $normalizedAnswer) !== 1, $violations, "{$uuid}: unexpected negation");
        } elseif ($topic === 'negatives') {
            $this->record(preg_match('/\b(?:will\s+not|won[’\']t)\s+have\b/iu', $normalizedAnswer) === 1, $violations, "{$uuid}: invalid negative form");
        } elseif ($topic === 'questions') {
            $this->record(preg_match('/\bwill\b.+\bhave\b/iu', $normalizedAnswer) === 1, $violations, "{$uuid}: invalid interrogative form");
            if ($requireQuestionMark) {
                $this->record(str_ends_with(trim($completed), '?'), $violations, "{$uuid}: question lacks a question mark");
            }
        } else {
            $this->record(preg_match('/\bwill(?:\s+already)?\s+have\b/iu', $normalizedAnswer) === 1, $violations, "{$uuid}: invalid time-expression form");
            $this->record(preg_match('/\b(?:by|before|within|in|no\s+later\s+than)\b/iu', $completed) === 1, $violations, "{$uuid}: missing future boundary");
        }
    }

    private function questionsHash(array $definition): string
    {
        return hash('sha256', json_encode(
            $definition['questions'] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(AcceptedAnswerVariants::normalizeTypography($value));

        return trim(preg_replace('/[^\pL\pN\']+/u', ' ', $value) ?? $value);
    }

    private function record(bool $condition, array &$violations, string $message): void
    {
        if (! $condition) {
            $violations[] = $message;
        }
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
        $base = 'database/seeders/V3/';

        return [
            $this->caseDefinition($base, 'forms', 'fpf', 'Forms', 'FuturePerfectForms',
                'f2e9de8b2e5e9f60209f810c2c1a127532d082ede82c929b8bcf20edcf88f991',
                '3f3ef7e4f6ca4dd918562d0408b8833e52923feb83f4c6c5483a92aa8121afea'),
            $this->caseDefinition($base, 'negatives', 'fpn', 'Negatives', 'FuturePerfectNegatives',
                '83ab4ae05a70e5512c8f4b09306bc33f66c0a7bf8ff0053eff8824111a362ccb',
                'e7f0487dec351ecf2c4d595ef8b6de90bee89df2fcd93f347c960f0657fc3702'),
            $this->caseDefinition($base, 'questions', 'fpq', 'Questions', 'FuturePerfectQuestions',
                'c68ebc53756b98b263bff9436853b532bd93c507a61584c207f37c70b57d0fe6',
                '8ee8efe005f7ef30d7bebaacd752ddbceaa619050f7dd26fe03133c4f9da65f5'),
            $this->caseDefinition($base, 'time-expressions', 'fpte', 'TimeExpressions', 'FuturePerfectTimeExpressions',
                '13bfb29e4d03683320d6e0704b2cd6832910c9231166f1c54a56fefb6d18ec95',
                '946eaf04479f71c73a1fc04c8e30ce19666d4a8f3851ecca37a59462c899581e'),
        ];
    }

    /** @return array<string, string> */
    private function caseDefinition(
        string $base,
        string $slug,
        string $prefix,
        string $topic,
        string $classStem,
        string $standardHash,
        string $builderHash
    ): array {
        $standardName = "UkrainianMixedFuturePerfect{$topic}StandardSeeder";
        $builderName = "UkrainianMixedFuturePerfect{$topic}BuilderSeeder";
        $pageSuffix = $slug === 'time-expressions' ? 'time-expressions' : $slug;

        return [
            'slug' => "future-perfect/{$slug}",
            'topic' => $slug,
            'page_slug' => "future-perfect-{$pageSuffix}",
            'manifest_path' => "{$base}TheoryLinks/data/future-perfect-{$pageSuffix}-theory-links.json",
            'original_standard_path' => "{$base}FutureForms/FuturePerfect/{$classStem}AllLevelsV3Seeder/definition.json",
            'original_builder_path' => "{$base}Polyglot/Polyglot{$classStem}AllLevelsLessonSeeder/definition.json",
            'standard_path' => "{$base}FutureForms/FuturePerfect/{$standardName}/definition.json",
            'builder_path' => "{$base}Polyglot/{$builderName}/definition.json",
            'original_standard_class' => "Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\{$classStem}AllLevelsV3Seeder",
            'original_builder_class' => "Database\\Seeders\\V3\\Polyglot\\Polyglot{$classStem}AllLevelsLessonSeeder",
            'standard_class' => "Database\\Seeders\\V3\\FutureForms\\FuturePerfect\\{$standardName}",
            'builder_class' => "Database\\Seeders\\V3\\Polyglot\\{$builderName}",
            'standard_uuid_prefix' => "ukm-{$prefix}-v3",
            'builder_uuid_prefix' => "ukm-{$prefix}-poly",
            'original_standard_questions_hash' => $standardHash,
            'original_builder_questions_hash' => $builderHash,
        ];
    }
}
