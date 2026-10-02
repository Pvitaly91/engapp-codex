<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage as Package;
use App\Support\M26InteractivePractice;
use App\Support\M26PointDetails;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

class M26PointDetailsTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); app()->setLocale('uk'); $this->withoutVite(); }

    private function block(array $target, array $config, int $index): TextBlock
    {
        $block = new TextBlock;
        $block->forceFill(['id' => 100 + $index, 'uuid' => Package::uuid($target['identity'], $config, $index + 1),
            'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null,
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $index + 1]);
        $block->setRelation('tags', collect());
        return $block;
    }

    private function extendedBlocks(): iterable
    {
        [$master, $manifest] = Package::load();
        foreach ($master['targets'] as $target) {
            $latest = M26InteractivePractice::definition($target, $manifest['definitions'][$target['identity']]);
            foreach ($target['blocks'] as $author) {
                if (!isset($author['progressive_v1'])) { continue; }
                $index = $author['source_index']; $config = $latest[$target['source_content_root']]['blocks'][$index];
                $block = $this->block($target, $config, $index);
                yield [$target, $author, $block, json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR)];
            }
        }
    }

    private function field(string $type): string
    {
        return match ($type) { 'forms-grid', 'summary-list' => 'items', 'usage-panels' => 'sections', 'comparison-table' => 'rows' };
    }

    private function fragmentFingerprint(string $type, mixed $value): string
    {
        return Package::digest(['type' => $type, 'value' => $value]);
    }

    public function test_all_fourteen_author_extensions_have_fifty_six_nonempty_per_point_controls_with_exact_once_author_coverage(): void
    {
        $blockCount = 0; $pointCount = 0; $authorItems = 0; $ids = []; $expectedByOwner = []; $actualByOwner = [];
        foreach ($this->extendedBlocks() as [$target, $author, $block, $data]) {
            $points = M26PointDetails::fragmentsFor($block, $data);
            self::assertIsArray($points); self::assertSame([0, 1, 2, 3], array_keys($points));
            $owner = $target['identity'];
            foreach ($author['native_data'][$this->field($author['type'])] as $value) {
                $expectedByOwner[$owner][] = $this->fragmentFingerprint($author['type'], $value); $authorItems++;
            }
            foreach (['intro', 'warning'] as $field) {
                if (!empty($author['native_data'][$field])) {
                    $expectedByOwner[$owner][] = $this->fragmentFingerprint($field, $author['native_data'][$field]);
                }
            }
            foreach ($points as $index => $point) {
                self::assertSame($author['progressive_v1']['key'].'-point-'.($index + 1), $point['key']);
                self::assertNotSame('', trim(strip_tags($point['title'])));
                self::assertNotEmpty($point['fragments'], 'No empty buttons or fabricated detail placeholders.');
                foreach ($point['fragments'] as $fragment) {
                    self::assertMatchesRegularExpression('/^[A-Za-z][A-Za-z0-9_.:-]*$/D', $fragment['id']);
                    self::assertNotContains($fragment['id'], $ids, 'A fragment must not appear under multiple points.');
                    $ids[] = $fragment['id'];
                    self::assertContains($fragment['type'], ['forms-grid', 'usage-panels', 'comparison-table', 'summary-list', 'intro', 'warning', 'supplement']);
                    self::assertNotEmpty($fragment['value']);
                    if ($fragment['type'] !== 'supplement') {
                        $actualByOwner[$owner][] = $this->fragmentFingerprint($fragment['type'], $fragment['value']);
                    }
                }
                $pointCount++;
            }
            $blockCount++;
        }
        foreach ($expectedByOwner as $owner => $expected) {
            $actual = $actualByOwner[$owner] ?? []; sort($expected); sort($actual);
            // Semantic routing may use another block on the SAME page, never
            // another lesson, duplicate an item or drop any author context.
            self::assertSame($expected, $actual, 'Every unchanged author item, intro and warning must appear exactly once: '.$owner);
        }
        self::assertSame(14, $blockCount); self::assertSame(56, $pointCount); self::assertSame(54, $authorItems);
        foreach (array_keys(M26PointDetails::load()['blocks']) as $key) {
            self::assertContains('block-'.$key, $ids, 'Existing author detail deep link is retained once.');
        }
        self::assertSame(Package::MASTER_SHA, hash_file('sha256', base_path(Package::MASTER)));
    }

    public function test_forms_usage_past_cause_maps_to_cause_instead_of_positional_finished_or_still_detail(): void
    {
        foreach ($this->extendedBlocks() as [$target, $author, $block, $data]) {
            if ($author['progressive_v1']['key'] !== 'm26-ppc-forms-2') { continue; }
            $points = M26PointDetails::fragmentsFor($block, $data);
            self::assertStringContainsString('Причина', $data['sections'][2]['label']);
            $fragments = array_values(array_filter($points[2]['fragments'], fn ($fragment) => $fragment['type'] === 'usage-panels'));
            self::assertContains($author['native_data']['sections'][1], array_column($fragments, 'value'));
            self::assertNotContains($author['native_data']['sections'][2], array_column($fragments, 'value'),
                'Equal item counts do not authorize a positional zip.');
            return;
        }
        self::fail('Forms usage extension not found.');
    }

    public function test_foreign_owner_locale_order_or_mutated_metadata_produces_no_point_controls(): void
    {
        foreach ($this->extendedBlocks() as [$target, $author, $block, $data]) {
            foreach (['locale' => 'en', 'seeder' => 'foreign', 'uuid' => 'foreign', 'type' => 'box', 'sort_order' => 999] as $field => $value) {
                $foreign = clone $block; $foreign->$field = $value;
                self::assertNull(M26PointDetails::fragmentsFor($foreign, $data));
            }
            $wrong = $data; unset($wrong['progressive_v2']);
            self::assertNull(M26PointDetails::fragmentsFor($block, $wrong));
            $wrong = $data; $wrong['progressive_v2']['key'] = 'foreign-key';
            self::assertNull(M26PointDetails::fragmentsFor($block, $wrong));
            $wrong = $data; $wrong['progressive_v2']['detail_native_sha256'] = str_repeat('0', 64);
            self::assertNull(M26PointDetails::fragmentsFor($block, $wrong));
            $field = $this->field($author['type']);
            $wrong = $data; array_pop($wrong['progressive_v2']['detail_native_data'][$field]);
            self::assertNull(M26PointDetails::fragmentsFor($block, $wrong));
            $wrong = $data; $wrong[$field] = array_reverse($wrong[$field]);
            self::assertNull(M26PointDetails::fragmentsFor($block, $wrong));
        }
    }

    public function test_modified_point_source_is_rejected_without_touching_working_sources(): void
    {
        $root = storage_path('app/m26-point-source-fixture-'.bin2hex(random_bytes(8)));
        IsolatedTestEnvironment::assertOwnedPath(dirname($root));
        File::makeDirectory($root, 0700);
        IsolatedTestEnvironment::assertOwnedPath($root);
        foreach ([Package::MASTER, Package::BEFORE, 'database/content-patches/m26-ppc-point-details.v1.json'] as $path) {
            File::makeDirectory(dirname($root.'/'.$path), 0700, true, true);
            File::copy(base_path($path), $root.'/'.$path);
        }
        self::assertSame(M26PointDetails::load(), M26PointDetails::load($root));
        File::append($root.'/database/content-patches/m26-ppc-point-details.v1.json', ' ');
        $this->expectException(RuntimeException::class);
        M26PointDetails::load($root);
    }

    public function test_map_validator_rejects_missing_duplicate_foreign_and_executable_fragment_shapes(): void
    {
        [$master, $manifest] = Package::load(); $map = M26PointDetails::load();
        $keys = array_keys($map['blocks']); $sourceKey = null; $sourcePoint = null; $ref = null;
        foreach ($map['blocks'] as $key => $points) {
            foreach ($points as $index => $point) {
                if ($point['fragments'] !== []) { $sourceKey = $key; $sourcePoint = $index; $ref = $point['fragments'][0]; break 2; }
            }
        }
        self::assertNotNull($ref);
        $cases = [];
        $wrong = $map; unset($wrong['blocks'][$keys[0]]); $cases['missing-block'] = $wrong;
        $wrong = $map; array_pop($wrong['blocks'][$keys[0]]); $cases['missing-point'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][] = $ref; $cases['duplicate-author'] = $wrong;
        $wrong = $map; array_shift($wrong['blocks'][$sourceKey][$sourcePoint]['fragments']); $cases['missing-author'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][0]['source'] = 'foreign-source'; $cases['unknown-source'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][0]['source'] = 'm26-ppc-negatives-1'; $cases['foreign-owner'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][0]['field'] = 'blade_view'; $cases['unknown-field'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][0] = 'blade_view'; $cases['scalar-reference'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'][0]['source'] = ['foreign']; $cases['array-source'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['view'] = 'admin.index'; $cases['executable-shape'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['fragments'] = []; unset($wrong['blocks'][$sourceKey][$sourcePoint]['supplement']); $cases['empty-explanation'] = $wrong;
        $wrong = $map; $wrong['blocks'][$sourceKey][$sourcePoint]['supplement'] = ['description' => '<script>alert(1)</script>', 'examples' => [['en' => 'Example.', 'ua' => 'Приклад.']]]; $cases['html-supplement'] = $wrong;
        foreach ($cases as $name => $wrong) {
            try { M26PointDetails::validate($wrong, $master, $manifest); self::fail('Unsafe point map accepted: '.$name); }
            catch (RuntimeException) {}
        }
    }
}
