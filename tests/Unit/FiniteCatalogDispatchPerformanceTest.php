<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\FinitePracticeQualityCatalog;
use App\Support\PassivePracticeQuality;
use App\Support\WordOrderPracticeQuality;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * PERF-03 dispatch regression, not a benchmark: no Laravel boot, HTTP or DB.
 * Each process starts with empty request-local catalogs, making skipped work
 * observable without timing assertions or production instrumentation.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class FiniteCatalogDispatchPerformanceTest extends TestCase
{
    private const BASIC_BANKS = [
        'standard' => [
            'database/seeders/V3/WordOrder/UkrainianMixedBasicWordOrderQualityStandardSeeder/definition.json',
            '8725ae140959022edfc78e4a85dbb1fd8433760de089b5044c3190821057fb93',
            36,
        ],
        'builder' => [
            'database/seeders/V3/Polyglot/UkrainianMixedBasicWordOrderQualityBuilderSeeder/definition.json',
            '248bef8ae514598be3da8bd90a363971fb37c39930b2aab0a6331d890a40ccca',
            18,
        ],
    ];

    public function test_every_registered_finite_identity_preserves_its_unique_dispatch_prefix(): void
    {
        $prefixes = [];
        $topicCount = 0;
        $overrides = 0;

        foreach (self::finiteCatalogs() as $catalog) {
            $reflection = new ReflectionClass($catalog);
            $prefix = $reflection->getConstant('UUID_PREFIX');
            self::assertIsString($prefix);
            self::assertNotSame('', $prefix);
            // QuestionUuidResolver retains 27 leading characters before its hash.
            self::assertLessThan(27, strlen($prefix));
            self::assertNotContains($prefix, $prefixes);
            foreach ($prefixes as $previous) {
                self::assertFalse(str_starts_with($prefix, $previous));
                self::assertFalse(str_starts_with($previous, $prefix));
            }
            $prefixes[] = $prefix;
            self::assertSame(FinitePracticeQualityCatalog::class, $reflection->getMethod('questions')->getDeclaringClass()->getName());
            if ($reflection->getMethod('questionUuid')->getDeclaringClass()->getName() !== FinitePracticeQualityCatalog::class) {
                ++$overrides;
            }

            foreach (array_keys($catalog::TOPICS) as $topic) {
                ++$topicCount;
                foreach ($catalog::LEVELS as $level) {
                    foreach (['g01', 'g99', 'c01', 'c99'] as $suffix) {
                        $uuid = $catalog::questionUuid($topic, $level.'-'.$suffix);
                        self::assertStringStartsWith($prefix, $uuid, $catalog.'/'.$topic);
                        self::assertLessThanOrEqual(36, strlen($uuid));
                        self::assertTrue($catalog::mayOwnQuestionUuid($uuid));
                    }
                }
            }
            $sample = $catalog::questionUuid(array_key_first($catalog::TOPICS), 'A1-g01');
            foreach (self::finiteCatalogs() as $other) {
                if ($other !== $catalog) {
                    self::assertFalse($other::mayOwnQuestionUuid($sample), $other.' must not claim '.$sample);
                }
            }
        }

        self::assertCount(25, $prefixes);
        self::assertSame(193, $topicCount);
        self::assertSame(15, $overrides);
        self::assertSame([], self::loadedFiniteCatalogs(), 'Identity dispatch must not compile any source bank.');
    }

    public function test_prefix_gate_is_conservative_and_does_not_validate_or_normalize_identity(): void
    {
        foreach (self::finiteCatalogs() as $catalog) {
            $prefix = (new ReflectionClass($catalog))->getConstant('UUID_PREFIX');
            foreach ([$prefix, $prefix.'unknown-topic-and-invalid-row', $prefix."\0invalid"] as $candidate) {
                self::assertTrue($catalog::mayOwnQuestionUuid($candidate), 'Matching prefixes must retain original source validation.');
            }
            foreach (['', 'unknown', ' '.$prefix.'a1-g01', strtoupper($prefix).'a1-g01', 'foreign-'.$prefix] as $candidate) {
                self::assertFalse($catalog::mayOwnQuestionUuid($candidate));
            }
            foreach ([null, false, true, 0, 1, 1.5, [], new \stdClass()] as $malformed) {
                self::assertTrue($catalog::mayOwnQuestionUuid($malformed), 'Non-string values must retain the existing dispatch/error path.');
            }
        }
        self::assertSame([], self::loadedFiniteCatalogs());
    }

    public function test_foreign_payloads_skip_all_finite_source_loads_but_keep_legacy_catalog_dispatch(): void
    {
        $payload = ['uuid' => 'unregistered-question', 'type' => '0', 'question' => 'Unchanged {a1}.',
            'answer_map' => ['a1' => 'answer'], 'verb_hint' => 'Незмінна підказка',
            'progress' => ['selected' => 'answer', 'attempts' => 2], 'locale' => 'en'];
        self::assertSame($payload, PassivePracticeQuality::decorateKnown($payload));
        self::assertFalse(PassivePracticeQuality::knownCatalogNeedsRefresh([$payload]));
        self::assertFalse(PassivePracticeQuality::knownCatalogNeedsRefresh([]));
        self::assertSame([], self::loadedFiniteCatalogs());

        $legacy = array_values(array_diff(self::catalogs(), self::finiteCatalogs()));
        self::assertCount(3, $legacy);
        foreach ($legacy as $catalog) {
            self::assertNotEmpty((new ReflectionMethod($catalog, 'questions'))->getStaticVariables(), $catalog.' was not dispatched.');
            self::assertNotNull((new ReflectionMethod($catalog, 'questions'))->getStaticVariables()['entries'] ?? null);
        }
    }

    public function test_foreign_hint_restoration_never_mutates_payload_or_loads_sources(): void
    {
        $payload = ['uuid' => 'unregistered-question', 'type' => '4', 'question' => 'Незмінна умова.',
            'answer_map' => ['a1' => 'Unchanged'], 'verb_hint' => 'Незмінна підказка',
            'verb_hints' => ['a1' => 'Незмінна підказка'], 'hint' => 'Keep this hint',
            'psp_content_revision' => str_repeat('a', 64)];
        foreach (self::finiteCatalogs() as $catalog) {
            foreach (['restoreClarifiedHints', 'restoreGenericReferenceHint'] as $method) {
                if (!method_exists($catalog, $method)) {
                    continue;
                }
                foreach (['uk', 'en', 'ua'] as $locale) {
                    self::assertSame($payload, $catalog::$method($payload, $locale), $catalog.'::'.$method);
                }
            }
        }
        self::assertSame([], self::loadedFiniteCatalogs());
    }

    public function test_unknown_matching_prefix_still_loads_its_original_source_guard_path(): void
    {
        $payload = ['uuid' => 'woq-not-a-real-question', 'type' => '0', 'question' => 'Unknown {a1}.', 'answer_map' => ['a1' => 'answer']];
        self::assertSame($payload, PassivePracticeQuality::decorateKnown($payload));
        self::assertFalse(PassivePracticeQuality::knownCatalogNeedsRefresh([$payload]));
        self::assertSame([WordOrderPracticeQuality::class], self::loadedFiniteCatalogs());
    }

    public function test_full_real_word_order_banks_and_payloads_equal_the_frozen_before_oracle(): void
    {
        $sources = self::beforeBasicSources();
        $decorated = [];
        foreach ($sources as $source) {
            $raw = self::payload($source);
            $before = self::beforeDecorate($raw, $sources);
            self::assertSame($before, PassivePracticeQuality::decorateKnown($raw), $source['uuid']);
            self::assertSame($before, PassivePracticeQuality::decorateKnown($before), $source['uuid'].' must remain idempotent.');
            $decorated[] = $before;
        }
        self::assertCount(54, $decorated);
        self::assertFalse(self::beforeNeedsRefresh($decorated, $sources));
        self::assertSame(self::beforeNeedsRefresh($decorated, $sources), PassivePracticeQuality::knownCatalogNeedsRefresh($decorated));
        self::assertSame([WordOrderPracticeQuality::class], self::loadedFiniteCatalogs(), 'Unrelated catalogs must not be materialized.');
    }

    public function test_refresh_and_source_bound_rejections_equal_the_before_oracle(): void
    {
        $sources = self::beforeBasicSources();
        $gap = array_values(array_filter($sources, static fn ($row) => (string) $row['type'] === '0'))[0];
        $builder = array_values(array_filter($sources, static fn ($row) => (string) $row['type'] === '4'))[0];
        $foreign = ['uuid' => 'unregistered-question', 'question' => 'Keep this question', 'type' => '0', 'answer_map' => ['a1' => 'untouched']];
        foreach ([$gap, $builder] as $source) {
            $raw = self::payload($source);
            $valid = self::beforeDecorate($raw, $sources);
            $variants = [
                $raw,
                $valid,
                array_replace($valid, ['question' => $valid['question'].' Changed']),
                array_replace($valid, ['type' => '2']),
                array_replace($valid, ['answer_map' => ['a1' => 'different answer']]),
                array_replace($valid, ['psp_content_revision' => 'stale']),
                array_replace($valid, ['passive_gap_phrase_input' => false]),
                array_replace($valid, ['finite_word_slots' => ['uuid' => 'stale']]),
            ];
            $missingAnswers = $raw;
            unset($missingAnswers['answer_map']);
            $variants[] = $missingAnswers;

            foreach ($variants as $variant) {
                self::assertSame(self::beforeDecorate($variant, $sources), PassivePracticeQuality::decorateKnown($variant));
                // Keep foreign rows and original ordering in the refresh input.
                foreach ([[$foreign, $variant], [$variant, $foreign]] as $payloads) {
                    self::assertSame(self::beforeNeedsRefresh($payloads, $sources), PassivePracticeQuality::knownCatalogNeedsRefresh($payloads));
                }
            }
        }
        self::assertSame([WordOrderPracticeQuality::class], self::loadedFiniteCatalogs());
    }

    public function test_matching_prefix_does_not_bypass_bad_original_pin_fail_closed_behavior(): void
    {
        $payload = ['uuid' => 'perf03-invalid-source-a1-g01', 'type' => '0', 'question' => 'Question {a1}.', 'answer_map' => ['a1' => 'answer']];
        self::assertTrue(Perf03InvalidSourceCatalog::mayOwnQuestionUuid($payload['uuid']));
        // The dispatcher only excludes nonowners. For an owner, the existing
        // inherited loader must still reject both banks with a bad source pin.
        self::assertSame($payload, Perf03InvalidSourceCatalog::decorate($payload));
        self::assertFalse(Perf03InvalidSourceCatalog::cachedNeedsRefresh([$payload]));
        self::assertSame(2, Perf03InvalidSourceCatalog::$compileCalls);
        self::assertSame([], Perf03InvalidSourceCatalog::questions());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Word Order original canonical source differs');
        Perf03InvalidSourceCatalog::originalDefinition('invalid-source', 'standard');
    }

    private static function catalogs(): array
    {
        return (new ReflectionClass(PassivePracticeQuality::class))->getConstant('CATALOGS');
    }

    private static function finiteCatalogs(): array
    {
        return array_values(array_filter(self::catalogs(), static fn ($catalog) => is_subclass_of($catalog, FinitePracticeQualityCatalog::class)));
    }

    private static function loadedFiniteCatalogs(): array
    {
        return array_keys((new ReflectionMethod(FinitePracticeQualityCatalog::class, 'questions'))->getStaticVariables()['catalogs'] ?? []);
    }

    /**
     * BEFORE data pins, not generated from optimized dispatch output. WT CRLF
     * and served ROOT LF copies were independently compared and differ only
     * in line endings (1,620 standard / 4,070 builder CRLFs). Normalize those
     * bytes only; retain every content byte and strict decoded compile equality.
     */
    private static function beforeBasicSources(): array
    {
        $sources = [];
        foreach (self::BASIC_BANKS as $kind => [$relative, $sha, $count]) {
            $path = dirname(__DIR__, 2).'/'.$relative;
            $bytes = file_get_contents($path);
            self::assertSame($sha, hash('sha256', str_replace("\r\n", "\n", $bytes)));
            $definition = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            self::assertCount($count, $definition['questions']);
            self::assertSame($definition, WordOrderPracticeQuality::compile('basic', $kind), 'Full canonical definition changed.');
            foreach ($definition['questions'] as $row) {
                $sources[$row['uuid']] = $row;
            }
        }
        return $sources;
    }

    private static function payload(array $source): array
    {
        return ['uuid' => $source['uuid'], 'question' => $source['question'], 'type' => (string) $source['type'],
            'answer_map' => array_map(static fn ($marker) => $marker['answer'], $source['markers']),
            'options' => $source['options'] ?? $source['markers']['a1']['options'],
            'level' => $source['level'], 'verb_hint' => $source['verb_hint'],
            'progress' => ['attempts' => 2, 'selected' => 'preserve'], 'locale' => 'uk', 'custom_passthrough' => ['stable' => true]];
    }

    /** Frozen FinitePracticeQualityCatalog::payloadMatches before PERF-03. */
    private static function beforeMatches(array $payload, array $source): bool
    {
        $key = static fn ($value) => mb_strtolower(Str::ascii((string) $value));
        return ($payload['uuid'] ?? null) === $source['uuid'] && ($payload['question'] ?? null) === $source['question']
            && (string) ($payload['type'] ?? '') === (string) $source['type']
            && array_map($key, array_values($payload['answer_map'] ?? [])) === array_map($key, array_column($source['markers'], 'answer'));
    }

    /** Frozen inherited decorator; shared answer mechanics are outside this dispatch change. */
    private static function beforeDecorate(array $payload, array $questions): array
    {
        $source = $questions[$payload['uuid'] ?? ''] ?? null;
        if (!$source || !self::beforeMatches($payload, $source)) { return $payload; }
        $payload = PassivePracticeQuality::decorate($payload, $source);
        if ((string) $source['type'] === '0') {
            $payload['passive_gap_phrase_input'] = true;
            $payload['reorder_accepted_answers'] = array_values(array_unique(array_merge(
                $payload['reorder_accepted_answers'] ?? [], $source['accepted_target_texts'])));
        }
        return $payload;
    }

    /** Frozen inherited refresh method; deliberately does not call optimized dispatch. */
    private static function beforeNeedsRefresh(array $payloads, array $questions): bool
    {
        if (PassivePracticeQuality::cachedNeedsRefresh($payloads, $questions)) { return true; }
        foreach ($payloads as $payload) {
            $source = $questions[$payload['uuid'] ?? ''] ?? null;
            if ($source && (!self::beforeMatches($payload, $source)
                || ((string) $source['type'] === '0' && ($payload['passive_gap_phrase_input'] ?? null) !== true))) { return true; }
        }
        return false;
    }
}

/** A bad pin exercises the real inherited source checker without changing repo files. */
final class Perf03InvalidSourceCatalog extends FinitePracticeQualityCatalog
{
    protected const UUID_PREFIX = 'perf03-';
    public const TOPICS = ['invalid-source' => []];
    public static int $compileCalls = 0;

    public static function mapping(string $topic): array
    {
        $bank = ['definition' => 'database/seeders/V3/WordOrder/UkrainianMixedBasicWordOrderQualityStandardSeeder/definition.json',
            'old_definition' => 'database/seeders/V3/WordOrder/UkrainianMixedBasicWordOrderQualityStandardSeeder/definition.json',
            'old_sha256' => str_repeat('0', 64)];
        return ['standard' => $bank, 'builder' => $bank];
    }

    public static function source(string $topic): array
    {
        return ['questions' => []];
    }

    public static function compile(string $topic, string $kind): array
    {
        ++self::$compileCalls;
        return parent::compile($topic, $kind);
    }
}
