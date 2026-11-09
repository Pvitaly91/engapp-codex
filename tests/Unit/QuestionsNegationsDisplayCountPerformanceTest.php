<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\QuestionsNegationsPracticeQuality as Quality;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Throwable;

/** Pure canonical-source fixtures: no Laravel bootstrap, content DB, HTTP or source writes. */
final class QuestionsNegationsDisplayCountPerformanceTest extends TestCase
{
    public function test_all_owned_builder_counts_and_rejected_contexts_match_the_before_oracle(): void
    {
        // This source contract predates the optimization; expected is never read
        // from savedBuilderDisplayCount(), nor from an AFTER snapshot.
        $patches = Quality::metadataPatches();
        self::assertCount(24, $patches);
        $builders = 0;

        foreach ($patches as $uuid => $patch) {
            $slug = $patch['before']['slug'];
            $filters = $patch['after']['filters'];
            if ($patch['kind'] !== 'builder') {
                $this->assertBeforeEquivalent($patches, null, $uuid, $slug, $filters, 'uk');
                continue;
            }
            ++$builders;
            $this->assertBeforeEquivalent($patches, 18, $uuid, $slug, $filters, 'uk');
            $this->assertBeforeEquivalent($patches, null, $uuid, $slug, $patch['before']['filters'], 'uk');
            $this->assertBeforeEquivalent($patches, null, $uuid, $slug.'-foreign', $filters, 'uk');
            foreach (['en', 'pl', 'ua', 'UK', ''] as $locale) {
                $this->assertBeforeEquivalent($patches, null, $uuid, $slug, $filters, $locale);
            }
            foreach (['slug', 'category_slug_path', 'page_seeder_class'] as $field) {
                $changed = $filters;
                Arr::set($changed, 'prompt_generator.theory_page.'.$field, 'foreign-owner');
                $this->assertBeforeEquivalent($patches, null, $uuid, $slug, $changed, 'uk');
            }
            foreach ([
                'seeder_classes' => ['Foreign\\Bank'],
                'theory_page_mixed_type_profile' => 'foreign-profile',
                'num_questions' => 19,
                'course_slug' => 'foreign-course',
            ] as $field => $value) {
                $changed = $filters;
                $changed[$field] = $value;
                $this->assertBeforeEquivalent($patches, null, $uuid, $slug, $changed, 'uk');
            }
            $extra = $filters + ['perf03_unrelated_metadata' => ['unchanged' => true]];
            $this->assertBeforeEquivalent($patches, 18, $uuid, $slug, $extra, 'uk');
        }
        self::assertSame(12, $builders);
    }

    public function test_foreign_standard_and_course_uuids_stay_outside_the_owned_builder_contract(): void
    {
        $patches = Quality::metadataPatches();
        $builder = $patches['qn-yn-poly-all'];
        foreach (['', 'foreign', 'wo-bwo-poly-test', 'QN-YN-POLY-ALL', 'qn-yn-v3-all'] as $uuid) {
            $this->assertBeforeEquivalent($patches, null, $uuid, $builder['before']['slug'], $builder['after']['filters'], 'uk');
        }
        $root = dirname(__DIR__, 2);
        $extra = Quality::mapping('tags')['additional_originals'][0];
        $course = json_decode(file_get_contents($root.'/'.$extra['path']), true, flags: JSON_THROW_ON_ERROR)['saved_test'];
        self::assertArrayNotHasKey($course['uuid'], $patches);
        $this->assertBeforeEquivalent($patches, null, $course['uuid'], $course['slug'], $course['filters'], 'uk');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_unrelated_uk_uuids_do_not_open_or_hash_a_practice_catalog(): void
    {
        $this->installIsolatedSourceSpy();
        foreach (['foreign', 'wo-bwo-poly-test', 'qn-yn-v3-all', 'polyglot-qtags-basics-a2-saved-test'] as $uuid) {
            self::assertNull(Quality::savedBuilderDisplayCount($uuid, 'any-slug', [], 'uk'));
        }
        self::assertSame([], $GLOBALS['perf03_qn_source_reads']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_owned_builder_keeps_all_catalog_integrity_checks_including_unrelated_topics(): void
    {
        $this->installIsolatedSourceSpy();
        $patches = Quality::metadataPatches();
        $builder = $patches['qn-yn-poly-all'];
        $GLOBALS['perf03_qn_source_reads'] = [];
        self::assertSame(18, Quality::savedBuilderDisplayCount('qn-yn-poly-all', $builder['before']['slug'], $builder['after']['filters'], 'uk'));
        self::assertNotEmpty($GLOBALS['perf03_qn_source_reads']);

        // Keep the original all-catalog failure boundary. A corruption in a
        // different topic must not become acceptable after a cheap UUID gate.
        $other = Quality::mapping('question-forms');
        foreach ([
            ['missing', $other['standard']['old_definition']],
            ['hash', $other['builder']['old_definition']],
            ['compiled', $other['builder']['definition']],
        ] as [$mode, $suffix]) {
            $GLOBALS['perf03_qn_fault'] = ['mode' => $mode, 'suffix' => $suffix];
            self::assertNull(Quality::savedBuilderDisplayCount('qn-yn-poly-all', $builder['before']['slug'], $builder['after']['filters'], 'uk'), $mode);
            $GLOBALS['perf03_qn_fault'] = null;
        }
        // Failures are not cached; with the same original bytes it works again.
        self::assertSame(18, Quality::savedBuilderDisplayCount('qn-yn-poly-all', $builder['before']['slug'], $builder['after']['filters'], 'uk'));
    }

    private function assertBeforeEquivalent(array $patches, ?int $expected, string $uuid, string $slug, array $filters, string $locale): void
    {
        $beforeFilters = $filters;
        $context = $uuid.' / '.$locale.' / '.$slug;
        self::assertSame($expected, $this->beforeOracle($patches, $uuid, $slug, $filters, $locale), 'BEFORE '.$context);
        self::assertSame($expected, Quality::savedBuilderDisplayCount($uuid, $slug, $filters, $locale), 'AFTER '.$context);
        self::assertSame($beforeFilters, $filters, 'Caller metadata is immutable: '.$context);
    }

    /**
     * Frozen pre-PERF03 display-count algorithm. The immutable source patch list
     * is supplied once for the equivalence matrix, rather than recompiled for
     * each expected value. The independent I/O test covers fresh source guards.
     */
    private function beforeOracle(array $patches, string $uuid, string $slug, array $filters, string $locale): ?int
    {
        if ($locale !== 'uk') { return null; }
        try {
            foreach ($patches as $patch) {
                if ($patch['kind'] !== 'builder' || $uuid !== $patch['before']['uuid'] || $slug !== $patch['before']['slug']
                    || Quality::stateNamespace($filters, $locale, $uuid) === null) { continue; }
                foreach (['slug', 'category_slug_path', 'page_seeder_class'] as $key) {
                    if (Arr::get($filters, 'prompt_generator.theory_page.'.$key) !== Arr::get($patch['before']['filters'], 'prompt_generator.theory_page.'.$key)) { return null; }
                }
                return count(Quality::compile($patch['topic'], 'builder')['questions']);
            }
        } catch (Throwable) { }
        return null;
    }

    /** These namespace spies exist only in dedicated PHPUnit child processes. */
    private function installIsolatedSourceSpy(): void
    {
        $GLOBALS['perf03_qn_source_reads'] = [];
        $GLOBALS['perf03_qn_fault'] = null;
        eval(<<<'PHP'
namespace App\Support;
function is_file($filename): bool {
    $fault = $GLOBALS['perf03_qn_fault'] ?? null;
    if (($fault['mode'] ?? '') === 'missing' && str_ends_with(str_replace('\\', '/', $filename), $fault['suffix'])) { return false; }
    return \is_file($filename);
}
function hash_file($algorithm, $filename, ...$args) {
    $GLOBALS['perf03_qn_source_reads'][] = ['hash', (string) $filename];
    $fault = $GLOBALS['perf03_qn_fault'] ?? null;
    if (($fault['mode'] ?? '') === 'hash' && str_ends_with(str_replace('\\', '/', $filename), $fault['suffix'])) { return str_repeat('0', 64); }
    return \hash_file($algorithm, $filename, ...$args);
}
function file_get_contents($filename, ...$args) {
    $GLOBALS['perf03_qn_source_reads'][] = ['read', (string) $filename];
    $contents = \file_get_contents($filename, ...$args);
    $fault = $GLOBALS['perf03_qn_fault'] ?? null;
    if (($fault['mode'] ?? '') === 'compiled' && str_ends_with(str_replace('\\', '/', $filename), $fault['suffix'])) {
        $changed = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        $changed['questions'] = [];
        return json_encode($changed, JSON_THROW_ON_ERROR);
    }
    return $contents;
}
PHP);
    }
}
