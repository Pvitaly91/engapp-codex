<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M42NativeDesignPackage as Design;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Coverage of the finite M11–M24 owner set, independent of a particular package view. */
class TheoryUniversalRolloutTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const CONTENT_TYPES = [
        'usage-panels' => 216,
        'comparison-table' => 38,
        'summary-list' => 37,
        'forms-grid' => 3,
        'mistakes-grid' => 2,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
    }

    private function sources(): array
    {
        $records = Design::registry()['targets'];
        $targets = Design::sourceTargets();
        self::assertCount(42, $records);
        self::assertCount(42, $targets);
        self::assertCount(42, array_unique(array_column($records, 'identity')));
        self::assertCount(42, array_unique(array_column($records, 'local_url')));
        foreach ($records as $record) {
            self::assertArrayHasKey($record['identity'], $targets);
            self::assertSame(['uk'], $record['source_locales']);
            self::assertStringStartsWith('http://gramlyze.loc/theory/', $record['local_url']);
        }

        return [$records, $targets];
    }

    private function block(array $target, int $slot, int $owner): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill([
            'id' => 95000 + $owner * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'],
            'locale' => 'uk',
            'sort_order' => $slot + 1,
            'type' => $source['type'],
            'body' => $source['body'],
            'column' => $source['column'] ?? null,
            'heading' => $source['heading'] ?? null,
            'level' => $source['level'] ?? null,
            'css_class' => $source['css_class'] ?? null,
        ]);
        $block->setRelation('tags', collect());
        $block->setRelation('page', null);

        return $block;
    }

    private function bindings(TextBlock $block, array $resolved, bool $canonical = true): array
    {
        return [
            'theoryCanonical' => $canonical,
            'block' => $block,
            'data' => $resolved['data'],
            'm42Design' => $resolved['design'],
            'practiceQuestions' => collect(),
            'lessonLinks' => [],
        ];
    }

    private function render(string $view, array $bindings, bool $universal = false): string
    {
        app('view')->flushState();

        return $universal ? TheoryContentRenderer::render($view, $bindings) : view($view, $bindings)->render();
    }

    private function dispatcher(TextBlock $block): string
    {
        return $this->render('theory.partials.content-block', [
            'block' => $block,
            'theoryCanonical' => true,
            'm42StyleContext' => true,
            'practiceQuestions' => collect(),
            'lessonLinks' => [],
        ]);
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($dom);
    }

    private function frozenHashes(array $records): array
    {
        $paths = [Design::REGISTRY, Design::SOURCE];
        foreach ($records as $record) {
            $paths[] = $record['source'];
            if ($record['overlay_source'] !== null) {
                $paths[] = $record['overlay_source'];
            }
        }
        $out = [];
        foreach (array_unique($paths) as $path) {
            $out[$path] = hash_file('sha256', base_path($path));
        }

        return $out;
    }

    public function test_all_42_owners_and_296_content_blocks_use_the_universal_content_node_view(): void
    {
        [$records, $targets] = $this->sources();
        $frozenBefore = $this->frozenHashes($records);
        $types = array_fill_keys(array_keys(self::CONTENT_TYPES), 0);
        $owners = [];
        $auxiliary = ['hero' => 0, 'navigation-chips' => 0, 'practice-set' => 0];

        foreach ($records as $owner => $record) {
            $target = $targets[$record['identity']];
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (array_key_exists($source['type'], $auxiliary)) {
                    $auxiliary[$source['type']]++;
                    continue;
                }
                $label = $record['slug'].' source '.$slot;
                self::assertArrayHasKey($source['type'], $types, $label.' must not silently leave the coverage set');
                $block = $this->block($target, $slot, $owner);
                $attributes = $block->getAttributes();
                $decoded = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);

                self::assertSame($decoded, $resolved['stored'], $label);
                self::assertSame($record['blocks'][$slot]['uuid'], $block->uuid, $label);
                self::assertSame($record['blocks'][$slot]['body_sha256'], hash('sha256', $block->body), $label);
                self::assertNotNull($resolved['design'], $label.' must retain its exact M42 binding');
                self::assertSame(Design::binding($block, $decoded)['plan'], $resolved['design'], $label);
                self::assertSame($source['type'], $resolved['design']['component'], $label);
                self::assertSame('engram.theory.blocks-v3.'.$source['type'], $resolved['view'], $label);

                $bindings = $this->bindings($block, $resolved);
                $dataBefore = $bindings['data'];
                $node = TheoryContentRenderer::nativeNode($resolved['view'], $bindings);
                self::assertSame('section', $node['kind'], $label);
                self::assertSame('block-'.$block->id, $node['id'], $label);
                $html = $this->render($resolved['view'], $bindings, true);
                self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'), $label);
                self::assertStringNotContainsString('data-theory-render-fallback=', $html, $label);
                self::assertSame(1, $this->xpath($html)->query(
                    '//section[@data-theory-component="section" and @id="block-'.$block->id.'"]'
                )->length, $label);
                self::assertSame($attributes, $block->getAttributes(), $label.' changes stored block attributes');
                self::assertSame($dataBefore, $bindings['data'], $label.' changes caller data');
                $types[$source['type']]++;
                $owners[$record['identity']] = true;
            }
        }

        self::assertCount(42, $owners);
        self::assertSame(self::CONTENT_TYPES, $types);
        self::assertSame(296, array_sum($types));
        self::assertSame(['hero' => 42, 'navigation-chips' => 3, 'practice-set' => 42], $auxiliary);
        self::assertSame($frozenBefore, $this->frozenHashes($records));
    }

    public function test_real_canonical_dispatcher_keeps_all_20_owned_details_and_every_content_anchor(): void
    {
        [$records, $targets] = $this->sources();
        $total = 0;
        $packageTotals = [];
        $contentCount = 0;

        foreach ($records as $owner => $record) {
            $target = $targets[$record['identity']];
            $ownerDetails = 0;
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (!array_key_exists($source['type'], self::CONTENT_TYPES)) {
                    continue;
                }
                $label = $record['slug'].' source '.$slot;
                $block = $this->block($target, $slot, $owner);
                $attributes = $block->getAttributes();
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                $points = $resolved['presentation']['points'] ?? [];
                $html = $this->dispatcher($block);
                self::assertSame(1, substr_count($html, '<!-- theory.content:node -->'), $label);
                self::assertStringNotContainsString('data-theory-render-fallback=', $html, $label);
                $xpath = $this->xpath($html);
                self::assertSame(1, $xpath->query('//*[@id="block-'.$block->id.'"]')->length, $label);
                $details = $xpath->query('//details[@data-theory-details]');
                self::assertSame(count($points), $details->length, $label);
                self::assertSame(0, $xpath->query('//details[@data-theory-details]//details[@data-theory-details]')->length, $label);
                foreach ($details as $detail) {
                    self::assertFalse($detail->hasAttribute('open'), $label);
                }
                foreach ($points as $point) {
                    $owned = $xpath->query('//*[@data-theory-section="'.$point['key'].'"]/details[@data-theory-details]');
                    self::assertSame(1, $owned->length, $label.' detail owner '.$point['key']);
                    foreach ($point['fragments'] as $fragment) {
                        self::assertSame(1, $xpath->query('.//*[@id="'.$fragment['id'].'"]', $owned->item(0))->length, $label);
                    }
                }
                $ids = [];
                foreach ($xpath->query('//*[@id]') as $element) {
                    $ids[] = $element->getAttribute('id');
                }
                self::assertCount(count($ids), array_unique($ids), $label.' duplicates an anchor');
                self::assertSame($attributes, $block->getAttributes(), $label);
                $ownerDetails += $details->length;
                $contentCount++;
            }
            self::assertSame($record['teaching_details'], $ownerDetails, $record['slug']);
            $packageTotals[$record['current_package']] = ($packageTotals[$record['current_package']] ?? 0) + $ownerDetails;
            $total += $ownerDetails;
        }

        self::assertSame(296, $contentCount);
        self::assertSame(20, $total);
        self::assertSame([
            'M27' => 13, 'M28' => 0, 'M29' => 0, 'M30' => 0,
            'M31' => 2, 'M32' => 1, 'M33' => 2, 'M34' => 2,
            'M35' => 0, 'M36' => 0, 'M37' => 0, 'M38' => 0,
            'M39' => 0, 'M40' => 0,
        ], $packageTotals);
    }

    public function test_all_42_practice_blocks_are_exact_passthrough_with_unchanged_raw_data(): void
    {
        [$records, $targets] = $this->sources();
        $count = 0;
        $models = ['native' => 0, 'm39' => 0, 'm40' => 0];

        foreach ($records as $owner => $record) {
            $target = $targets[$record['identity']];
            $ownerCount = 0;
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if ($source['type'] !== 'practice-set') {
                    continue;
                }
                $label = $record['slug'].' practice';
                $block = $this->block($target, $slot, $owner);
                $attributes = $block->getAttributes();
                $raw = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                self::assertNotNull($resolved['design'], $label);
                self::assertSame($raw, $resolved['data'], $label);
                $bindings = $this->bindings($block, $resolved);
                self::assertNull(TheoryContentRenderer::nativeNode($resolved['view'], $bindings), $label);
                $before = $this->render($resolved['view'], $bindings);
                $after = $this->render($resolved['view'], $bindings, true);
                self::assertSame($before, $after, $label.' is not a byte-exact passthrough');
                self::assertStringNotContainsString('<!-- theory.content:node -->', $after, $label);
                self::assertStringNotContainsString('data-theory-render-fallback=', $after, $label);

                $model = match ($record['current_package']) {
                    'M39' => 'm39', 'M40' => 'm40', default => 'native',
                };
                $factory = $model === 'native' ? 'theoryPracticeSet(' : $model.'PracticeUi(';
                $xpath = $this->xpath($after);
                self::assertSame(1, $xpath->query('//*[@x-data and contains(@x-data, "'.$factory.'")]')->length, $label);
                self::assertGreaterThan(0, $xpath->query('//button')->length, $label);
                self::assertSame($raw, $bindings['data'], $label);
                self::assertSame($attributes, $block->getAttributes(), $label);
                $models[$model]++;
                $ownerCount++;
                $count++;
            }
            self::assertSame(1, $ownerCount, $record['slug'].' must retain one practice block');
        }

        self::assertSame(42, $count);
        self::assertSame(['native' => 36, 'm39' => 3, 'm40' => 3], $models);
    }

    public function test_canonical_opt_in_remains_caller_owned_for_all_14_packages(): void
    {
        [$records, $targets] = $this->sources();
        $seen = [];
        foreach ($records as $owner => $record) {
            if (isset($seen[$record['current_package']])) {
                continue;
            }
            $target = $targets[$record['identity']];
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                if (!array_key_exists($source['type'], self::CONTENT_TYPES)) {
                    continue;
                }
                $block = $this->block($target, $slot, $owner);
                $resolved = TheoryContentSource::resolve($block);
                self::assertNull($resolved['design']);
                $bindings = $this->bindings($block, $resolved, false);
                self::assertNull(TheoryContentRenderer::nativeNode($resolved['view'], $bindings));
                $old = $this->render($resolved['view'], $bindings);
                $compatible = $this->render($resolved['view'], $bindings, true);
                self::assertSame($old, $compatible, $record['current_package'].' changes non-theory compatibility');
                self::assertStringNotContainsString('<!-- theory.content:node -->', $compatible);
                $bindings['theoryCanonical'] = true;
                self::assertSame(1, substr_count($this->render($resolved['view'], $bindings, true), '<!-- theory.content:node -->'));
                $seen[$record['current_package']] = true;
                break;
            }
        }
        self::assertCount(14, $seen);
    }
}
