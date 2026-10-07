<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/tools/diagnostics/verify-m42-design-evidence.php';

final class M42DesignEvidenceTest extends TestCase
{
    private function fixture(): array
    {
        $registry = json_decode(file_get_contents(dirname(__DIR__, 2).'/database/content-patches/m42-native-design-registry.v1.json'), true, flags: JSON_THROW_ON_ERROR);
        $identities = array_column($registry['targets'], 'identity');
        $record = ['at' => 'before', 'target' => ['root' => 'D:/DEV/htdocs/gramlyze.loc', 'document_root' => 'D:/DEV/htdocs/gramlyze.loc/public',
            'driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'database' => 'gr2', 'site_mode' => 'development'],
            'targets' => array_map(static fn ($identity) => ['identity' => $identity, 'locales' => ['en', 'pl', 'uk'],
                'blocks' => [['body' => 'exact accepted body', 'uuid' => 'source UUID', 'locale' => 'uk']],
                'linked_bank_ids' => ['own' => [1, 2, 3]], 'exact_accepted_source' => true], $identities),
            'm41_references' => array_fill(0, 3, ['locales' => ['uk'], 'blocks' => [['body' => 'exact M41']], 'exact_accepted_source' => true]),
            'fingerprints' => array_combine(array_map(static fn ($n) => 'table'.$n, range(1, 45)), array_fill(0, 45, ['count' => 1, 'sha256' => 'exact'])),
            'read_only_select_guard' => true];
        $record['fingerprints']['text_blocks'] = ['count' => 6563, 'sha256' => 'all exact'];
        return [$record, $identities];
    }

    public function test_presentation_only_accepts_only_timestamp_change(): void
    {
        [$before, $ids] = $this->fixture(); $after = $before; $after['at'] = 'after';
        $actual = \verifyM42DesignEvidence($before, $after, $ids);
        self::assertTrue($actual['pass']); self::assertSame([0, 0, 0], [$actual['updated'], $actual['inserted'], $actual['deleted']]);
        self::assertSame(46, $actual['protected_raw_tables']);
    }

    public static function owners(): array { return array_map(static fn ($n) => [$n], range(0, 41)); }

    #[DataProvider('owners')]
    public function test_every_identity_rejects_a_content_mutation(int $index): void
    {
        [$before, $ids] = $this->fixture(); $after = $before;
        $after['targets'][$index]['blocks'][0]['body'] = 'lost a source example';
        $this->expectException(\RuntimeException::class); \verifyM42DesignEvidence($before, $after, $ids);
    }

    #[DataProvider('owners')]
    public function test_every_identity_rejects_lost_bank_or_locale_rows(int $index): void
    {
        [$before, $ids] = $this->fixture(); $after = $before;
        $after['targets'][$index]['linked_bank_ids']['own'] = [1, 2];
        $this->expectException(\RuntimeException::class); \verifyM42DesignEvidence($before, $after, $ids);
    }

    public function test_full_raw_progress_fingerprint_delta_is_rejected(): void
    {
        [$before, $ids] = $this->fixture(); $after = $before; $after['fingerprints']['table10']['sha256'] = 'progress changed';
        $this->expectException(\RuntimeException::class); \verifyM42DesignEvidence($before, $after, $ids);
    }

    public function test_m41_reference_mutation_is_rejected(): void
    {
        [$before, $ids] = $this->fixture(); $after = $before; $after['m41_references'][0]['blocks'][0]['body'] = 'lost M41';
        $this->expectException(\RuntimeException::class); \verifyM42DesignEvidence($before, $after, $ids);
    }
}
