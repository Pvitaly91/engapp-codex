<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once dirname(__DIR__, 2).'/tools/diagnostics/verify-m41-design-evidence.php';

class M41DesignEvidenceTest extends TestCase
{
    private static function fixture(): array
    {
        $identities = array_map(static fn ($name) => 'Database\\Seeders\\Page_V3\\Tenses\\'.$name,
            ['TensesPastSimpleVsPastContinuousTheorySeeder', 'TensesPresentSimpleVsPresentContinuousTheorySeeder', 'TensesPresentPerfectVsPastSimpleTheorySeeder']);
        return ['at' => '2026-10-06T20:00:00Z',
            'target' => ['root' => 'D:/DEV/htdocs/gramlyze.loc', 'driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'database' => 'gr2'],
            'targets' => array_map(static fn ($identity) => ['identity' => $identity, 'page_sha256' => 'exact-page',
                'blocks' => [['id' => 1, 'uuid' => 'exact-uuid', 'locale' => 'uk', 'body_sha256' => 'exact-body', 'metadata' => ['created_at' => 'unchanged']]]], $identities),
            'source_fidelity' => array_fill_keys($identities, ['state' => 'author_after', 'source_db_exact' => true]),
            'linked_banks' => array_combine($identities, array_map(static fn ($count) => ['question_count' => $count, 'question_ids' => [1, 2]], [72, 72, 24])),
            'regressions' => array_fill(0, 47, ['sha256' => 'exact-prior-owner']),
            'fingerprints' => ['text_blocks' => ['count' => 6563, 'sha256' => 'full-raw-blocks'], 'pages' => ['count' => 200, 'sha256' => 'full-raw-pages-text-included'],
                ...array_fill_keys(array_map(static fn ($i) => 'protected-'.$i, range(1, 22)), ['count' => 1, 'sha256' => 'full-raw'])],
            'progress_tables' => ['progress'], 'non_target_text_blocks' => ['count' => 6533, 'sha256' => 'exact-non-target'],
            'anchor_reference_inventory' => ['sources' => ['point' => 'anchor'], 'references' => []]];
    }

    public function test_fresh_identical_raw_evidence_proves_zero_delta(): void
    {
        $old = self::fixture(); $new = $old; $new['at'] = '2026-10-06T21:00:00Z';
        $result = \verifyM41DesignEvidence($old, $new);
        self::assertTrue($result['pass']);
        self::assertSame([0, 0, 0], [$result['updated'], $result['inserted'], $result['deleted']]);
        self::assertSame(24, $result['protected_raw_tables']);
        self::assertSame(47, $result['prior_owners_unchanged']);
    }

    public static function changedEvidence(): array
    {
        return [['target', 'root'], ['target', 'driver'], ['target', 'host'], ['target', 'database'], ['target', 'port'],
            ['targets', 0, 'page_sha256'], ['targets', 0, 'identity'], ['targets', 0, 'blocks', 0, 'id'],
            ['targets', 0, 'blocks', 0, 'uuid'], ['targets', 0, 'blocks', 0, 'locale'], ['targets', 0, 'blocks', 0, 'body_sha256'],
            ['targets', 0, 'blocks', 0, 'metadata', 'created_at'], ['fingerprints', 'pages', 'sha256'],
            ['fingerprints', 'text_blocks', 'sha256'], ['fingerprints', 'text_blocks', 'count'],
            ['regressions', 0, 'sha256'], ['source_fidelity', 'Database\\Seeders\\Page_V3\\Tenses\\TensesPastSimpleVsPastContinuousTheorySeeder', 'state'],
            ['source_fidelity', 'Database\\Seeders\\Page_V3\\Tenses\\TensesPastSimpleVsPastContinuousTheorySeeder', 'source_db_exact'],
            ['linked_banks', 'Database\\Seeders\\Page_V3\\Tenses\\TensesPastSimpleVsPastContinuousTheorySeeder', 'question_count'],
            ['linked_banks', 'Database\\Seeders\\Page_V3\\Tenses\\TensesPastSimpleVsPastContinuousTheorySeeder', 'question_ids', 0],
            ['progress_tables', 0], ['non_target_text_blocks', 'sha256'], ['anchor_reference_inventory', 'sources', 'point']];
    }

    #[DataProvider('changedEvidence')]
    public function test_every_protected_delta_is_rejected(mixed ...$path): void
    {
        $old = self::fixture(); $new = $old;
        $value = &$new;
        foreach ($path as $segment) { $value = &$value[$segment]; }
        $value = is_int($value) ? $value + 1 : 'changed';
        unset($value);
        $this->expectException(RuntimeException::class);
        \verifyM41DesignEvidence($old, $new);
    }
}
