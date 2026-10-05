<?php

namespace Tests\Unit;

use App\Services\PpcQualityLocalTargetGuard;
use App\Support\PpcOrderedTheoryLinks;
use Illuminate\Database\Connection;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PastPerfectContinuousExportRefreshToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! defined('PPC_QUALITY_EXPORT_REFRESH_LIBRARY_ONLY')) {
            define('PPC_QUALITY_EXPORT_REFRESH_LIBRARY_ONLY', true);
        }
        require_once dirname(__DIR__, 2).'/tools/diagnostics/refresh-ppc-quality-exports.php';
    }

    public function test_scope_is_exactly_the_public_624_persistent_uuids(): void
    {
        $scope = ppcQualityExportScope($this->inventory());
        $this->assertCount(624, $scope);
        $this->assertCount(624, array_unique(array_keys($scope)));
    }

    public static function badScopes(): array
    {
        return ['missing' => ['missing'], 'duplicate' => ['duplicate'], 'unsafe-path' => ['unsafe']];
    }

    #[DataProvider('badScopes')]
    public function test_scope_rejects_missing_duplicate_or_pathlike_uuids(string $case): void
    {
        $inventory = $this->inventory();
        match ($case) {
            'missing' => array_pop($inventory['questions']),
            'duplicate' => $inventory['questions'][1]['persistent_uuid'] = $inventory['questions'][0]['persistent_uuid'],
            'unsafe' => $inventory['questions'][0]['persistent_uuid'] = '../ROOT-question',
        };
        $this->expectException(RuntimeException::class);
        ppcQualityExportScope($inventory);
    }

    public function test_payload_verifies_exact_ordered_links_locales_and_marker_contract(): void
    {
        [$record, $authored, $payload, $links, $canonical] = $this->payload();
        $counts = ppcQualityExportPayloadCheck($record, $authored, $payload, $links, $canonical);
        $this->assertSame(3, $counts['compose_prompts']);
        $this->assertSame(count($links), $counts['theory_links']);
    }

    public static function badPayloads(): array
    {
        return ['links' => ['links'], 'locale-source' => ['source'], 'id' => ['id'], 'marker-options' => ['options']];
    }

    #[DataProvider('badPayloads')]
    public function test_payload_check_rejects_contract_drift(string $case): void
    {
        [$record, $authored, $payload, $links, $canonical] = $this->payload();
        match ($case) {
            'links' => $payload[PpcOrderedTheoryLinks::FIELD] = array_reverse($links),
            'source' => $payload['hints'][array_search('compose_prompt', array_column($payload['hints'], 'provider'), true)]['hint'] = 'Wrong source condition',
            'id' => $payload['question']['id'] = -1,
            'options' => $payload['question']['options_by_marker'] = [],
        };
        $this->expectException(RuntimeException::class);
        ppcQualityExportPayloadCheck($record, $authored, $payload, $links, $canonical);
    }

    public function test_readonly_guard_rejects_production_before_any_database_access(): void
    {
        $this->expectException(RuntimeException::class);
        (new PpcQualityLocalTargetGuard)->verifyReadOnlyExport(Mockery::mock(Connection::class), 'gramlyze.com', '', []);
    }

    public function test_readonly_guard_rejects_an_isolated_test_root_before_any_database_access(): void
    {
        $this->expectException(RuntimeException::class);
        (new PpcQualityLocalTargetGuard)->verifyReadOnlyExport(Mockery::mock(Connection::class), 'gramlyze.loc', '', []);
    }

    private function inventory(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2).'/docs/reports/past-perfect-continuous-quality-inventory.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    private function payload(): array
    {
        $record = $this->inventory()['questions'][0];
        $root = dirname(__DIR__, 2);
        $definition = json_decode(file_get_contents($root.'/'.$record['definition_path']), true, flags: JSON_THROW_ON_ERROR);
        $authored = collect($definition['questions'])->firstWhere('uuid', $record['editorial_uuid']);
        $payload = json_decode(file_get_contents($root.'/database/seeders/questions/'.$record['persistent_uuid'].'.json'), true, flags: JSON_THROW_ON_ERROR);
        $links = array_map(fn ($uuid, $position): array => ['text_block_uuid' => $uuid, 'position' => $position],
            $record['ordered_theory_text_block_uuids'], array_keys($record['ordered_theory_text_block_uuids']));
        $payload[PpcOrderedTheoryLinks::FIELD] = $links;
        $canonical = ['id' => $payload['question']['id'], 'options_by_marker' => $payload['question']['options_by_marker']];
        return [$record, $authored, $payload, $links, $canonical];
    }
}
