<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M42NativeDesignPackage as Design;
use App\Support\TheoryContentGroups;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryContentSource;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\HtmlString;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Finite presentation groups never edit, reorder or reassign their source children. */
class TheoryContentGroupsTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const MAPS = [
        'docs/content/theory-content-groups/emphasis-conditionals.v1.json',
        'docs/content/theory-content-groups/formal-academic-passive.v1.json',
        'docs/content/theory-content-groups/modals-revision.v1.json',
    ];

    private const M40_SLUGS = [
        'present-perfect-vs-present-perfect-continuous', 'narrative-tenses', 'b1-mixed-revision',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
    }

    private function maps(): array
    {
        return array_map(static fn (string $path) => json_decode(
            file_get_contents(base_path($path)), true, flags: JSON_THROW_ON_ERROR
        ), self::MAPS);
    }

    private function block(array $target, int $slot, int $owner): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill([
            'id' => 97000 + $owner * 100 + $slot,
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

    private function fixtures(bool $m40 = false, bool $comparison = false): array
    {
        $sources = Design::sourceTargets();
        $fixtures = [];
        $owner = 0;
        foreach ($this->maps() as $map) {
            foreach ($map['targets'] as $target) {
                if (in_array($target['slug'], self::M40_SLUGS, true) !== $m40) {
                    $owner++;
                    continue;
                }
                foreach ($target['blocks'] as $record) {
                    if (($record['component'] === 'comparison-table') !== $comparison) { continue; }
                    $block = $this->block($sources[$target['identity']], $record['source_index'], $owner);
                    $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
                    self::assertNotNull($resolved['design'], $target['slug'].' exact design guard');
                    $fixtures[] = [
                        'label' => $target['slug'].' source '.$record['source_index'],
                        'block' => $block,
                        'resolved' => $resolved,
                        'record' => $record,
                    ];
                }
                $owner++;
            }
        }

        return $fixtures;
    }

    private function sentinelNode(object $block, int $count): array
    {
        $items = [];
        for ($index = 0; $index < $count; $index++) {
            $items[] = [
                'kind' => 'fragment',
                'id' => 'original-'.$block->id.'-'.$index,
                'body_html' => new HtmlString('<p lang="uk">Точний текст '.$index.'.</p>'),
                'examples' => [['kind' => 'example', 'en' => 'Example '.$index.'.', 'uk' => 'Переклад '.$index.'.']],
                'tail' => [['kind' => 'note', 'html' => 'Примітка '.$index]],
                // Identity equality proves a detail is retained on the same child.
                'detail' => ['kind' => 'disclosure', 'index' => $index, 'section' => (object) [
                    'key' => 'point-'.$block->id.'-'.$index,
                    'detailReferences' => ['detail-'.$block->id.'-'.$index],
                ]],
            ];
        }

        return [
            'kind' => 'section', 'id' => 'block-'.$block->id, 'title' => 'Незмінна секція',
            'layout' => 'stack', 'variant' => 'plain', 'footer' => false,
            'intro_html' => new HtmlString('<p>Вступ.</p>'),
            'attrs' => ['data-existing-marker' => 'preserved'],
            'items' => $items,
            'tail' => [['kind' => 'fragment', 'body_html' => new HtmlString('<p>Завершення.</p>')]],
        ];
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

    public function test_all_finite_groups_match_exact_sources_and_cover_contiguous_indices_once(): void
    {
        $sources = Design::sourceTargets();
        $registry = array_column(Design::registry()['targets'], null, 'identity');
        $owners = [];
        $blocks = [];
        $groups = 0;
        $indices = 0;

        foreach ($this->maps() as $map) {
            self::assertSame(1, $map['schema_version']);
            foreach ($map['targets'] as $target) {
                self::assertNotContains($target['identity'], $owners);
                $owners[] = $target['identity'];
                self::assertArrayHasKey($target['identity'], $sources);
                self::assertSame($registry[$target['identity']]['slug'], $target['slug']);
                self::assertNotSame('M27', $registry[$target['identity']]['current_package']);
                // M40 preserves four labelled points and six intro-only blocks;
                // the separate test below covers its distinct semantic shapes.
                if (in_array($target['slug'], self::M40_SLUGS, true)) { continue; }
                foreach ($target['blocks'] as $record) {
                    if ($record['component'] === 'comparison-table') { continue; }
                    $source = $sources[$target['identity']]['after']['page']['blocks'][$record['source_index']];
                    $registered = $registry[$target['identity']]['blocks'][$record['source_index']];
                    $label = $target['slug'].' source '.$record['source_index'];
                    self::assertNotContains($record['uuid'], $blocks, $label);
                    $blocks[] = $record['uuid'];
                    self::assertSame('usage-panels', $record['component'], $label);
                    self::assertSame($source['type'], $record['component'], $label);
                    self::assertSame($registered['uuid'], $record['uuid'], $label);
                    self::assertSame($registered['body_sha256'], $record['body_sha256'], $label);
                    self::assertSame(hash('sha256', $source['body']), $record['body_sha256'], $label);
                    $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                    self::assertNotEmpty($data['sections'], $label);
                    foreach ($data['sections'] as $section) {
                        self::assertEmpty($section['label'] ?? '', $label.' must not replace an existing authored label');
                    }
                    $covered = [];
                    foreach ($record['groups'] as $group) {
                        self::assertIsString($group['label']);
                        self::assertNotSame('', trim($group['label']));
                        self::assertSame(strip_tags($group['label']), $group['label']);
                        self::assertContains($group['accent'], ['emerald', 'blue', 'amber', 'slate', 'sky', 'rose']);
                        self::assertNotEmpty($group['indices'], $label);
                        foreach ($group['indices'] as $index) {
                            self::assertIsInt($index);
                        }
                        self::assertSame(range($group['indices'][0], $group['indices'][count($group['indices']) - 1]), $group['indices'], $label);
                        array_push($covered, ...$group['indices']);
                        $groups++;
                    }
                    self::assertSame(range(0, count($data['sections']) - 1), $covered, $label.' must retain every source index exactly once in order');
                    $indices += count($covered);
                }
            }
        }

        self::assertCount(39, $owners);
        self::assertCount(184, $blocks);
        self::assertSame(364, $groups);
        self::assertSame(776, $indices);
    }

    public function test_grouping_preserves_every_normalized_child_and_its_detail_object_exactly(): void
    {
        $count = 0;
        foreach ($this->fixtures() as $fixture) {
            ['block' => $block, 'resolved' => $resolved, 'record' => $record, 'label' => $label] = $fixture;
            $node = $this->sentinelNode($block, count($resolved['data']['sections']));
            $sourceAttributes = $block->getAttributes();
            $dataBefore = $resolved['data'];
            $grouped = TheoryContentGroups::apply($node, $block, $resolved['data'], $resolved['design']);
            self::assertCount(count($record['groups']), $grouped['items'], $label);
            $flattened = [];
            foreach ($record['groups'] as $index => $expected) {
                $panel = $grouped['items'][$index];
                self::assertSame('usage', $panel['kind'], $label);
                self::assertSame($expected['label'], $panel['label'], $label);
                self::assertSame($expected['accent'], $panel['accent'], $label);
                self::assertSame($index + 1, $panel['number'], $label);
                self::assertArrayHasKey('data-theory-content-group', $panel['attrs'], $label);
                self::assertArrayHasKey('data-theory-point-panel', $panel['attrs'], $label);
                self::assertSame($expected['accent'], $panel['attrs']['data-theory-accent'], $label);
                self::assertCount(1, $panel['items'], $label);
                $children = $panel['items'][0];
                self::assertSame('group', $children['kind'], $label);
                self::assertSame('stack', $children['layout'], $label);
                $expectedChildren = array_map(static fn (int $sourceIndex) => $node['items'][$sourceIndex], $expected['indices']);
                self::assertSame($expectedChildren, $children['items'], $label.' moved or changed a child/detail');
                array_push($flattened, ...$children['items']);
            }
            self::assertSame($node['items'], $flattened, $label);
            foreach (['kind', 'id', 'title', 'layout', 'variant', 'footer', 'intro_html', 'tail'] as $key) {
                self::assertSame($node[$key], $grouped[$key], $label.' changed root '.$key);
            }
            self::assertSame('preserved', $grouped['attrs']['data-existing-marker'], $label);
            self::assertSame($sourceAttributes, $block->getAttributes(), $label);
            self::assertSame($dataBefore, $resolved['data'], $label);
            $count++;
        }
        self::assertSame(184, $count);
    }

    public function test_all_mapped_blocks_render_real_canonical_usage_panels_with_their_labels(): void
    {
        $blocks = 0;
        $groups = 0;
        foreach ($this->fixtures() as $fixture) {
            ['block' => $block, 'resolved' => $resolved, 'record' => $record, 'label' => $label] = $fixture;
            $bindings = [
                'theoryCanonical' => true, 'block' => $block, 'data' => $resolved['data'],
                'm42Design' => $resolved['design'], 'practiceQuestions' => collect(), 'lessonLinks' => [],
            ];
            $node = TheoryContentRenderer::nativeNode($resolved['view'], $bindings);
            self::assertCount(count($record['groups']), $node['items'], $label);
            app('view')->flushState();
            $html = TheoryContentRenderer::render($resolved['view'], $bindings);
            $xpath = $this->xpath($html);
            self::assertSame(1, $xpath->query('//section[@data-theory-component="section" and @id="block-'.$block->id.'"]')->length, $label);
            self::assertSame(count($record['groups']), $xpath->query('//article[@data-theory-component="usage" and @data-theory-content-group]')->length, $label);
            self::assertSame(0, $xpath->query('//*[@data-theory-render-fallback]')->length, $label);
            self::assertSame(0, $xpath->query('//*[@data-theory-content-group]//*[@data-theory-content-group]')->length, $label);
            foreach ($record['groups'] as $index => $group) {
                $panel = $xpath->query('//article[@data-theory-content-group]')->item($index);
                self::assertSame($group['accent'], $panel->getAttribute('data-theory-accent'), $label);
                self::assertStringContainsString($group['label'], $panel->textContent, $label);
                self::assertSame($group['label'], $node['items'][$index]['label'], $label);
            }
            $blocks++;
            $groups += count($record['groups']);
        }
        self::assertSame(184, $blocks);
        self::assertSame(364, $groups);
    }

    public function test_m40_keeps_six_intros_and_four_existing_labelled_points_without_nested_panels(): void
    {
        $blocks = 0;
        $intros = 0;
        $namedPoints = 0;
        foreach ($this->fixtures(true) as $fixture) {
            ['block' => $block, 'resolved' => $resolved, 'record' => $record, 'label' => $label] = $fixture;
            self::assertSame($block->uuid, $record['uuid'], $label);
            self::assertSame(hash('sha256', $block->body), $record['body_sha256'], $label);
            self::assertSame($block->sort_order - 1, $record['source_index'], $label);
            self::assertSame('usage-panels', $record['component'], $label);
            $data = $resolved['data'];
            $node = $this->sentinelNode($block, count($data['sections'] ?? []));
            if (isset($record['intro_group'])) {
                self::assertEmpty($data['sections'] ?? [], $label);
                self::assertNotEmpty($data['intro'], $label);
                self::assertNotSame('', trim($record['intro_group']['label']), $label);
                self::assertContains($record['intro_group']['accent'], ['emerald', 'blue', 'amber', 'slate', 'sky', 'rose']);
                $grouped = TheoryContentGroups::apply($node, $block, $data, $resolved['design']);
                self::assertArrayNotHasKey('intro_html', $grouped, $label.' must not duplicate the intro');
                self::assertCount(1, $grouped['items'], $label);
                self::assertSame($node['intro_html'], $grouped['items'][0]['body_html'], $label.' changed the exact normalized intro');
                self::assertSame($record['intro_group']['label'], $grouped['items'][0]['label'], $label);
                self::assertFalse($grouped['items'][0]['body_inline'], $label);
                $expectedPanels = 1;
                $intros++;
            } else {
                self::assertCount(4, $record['groups']);
                foreach ($record['groups'] as $index => $group) {
                    self::assertSame([$index], $group['indices'], $label);
                    self::assertSame($data['sections'][$index]['label'], $group['label'], $label.' changed an authored label');
                    $node['items'][$index]['kind'] = 'usage';
                    $node['items'][$index]['label'] = $data['sections'][$index]['label'];
                    $node['items'][$index]['number'] = $index + 1;
                    $node['items'][$index]['accent'] = $data['sections'][$index]['color'] ?? 'slate';
                    $node['items'][$index]['attrs'] = ['data-existing-item' => 'preserved'];
                }
                $grouped = TheoryContentGroups::apply($node, $block, $data, $resolved['design']);
                self::assertSame($node['intro_html'], $grouped['intro_html'], $label);
                self::assertCount(4, $grouped['items']);
                foreach ($record['groups'] as $index => $group) {
                    $expected = $node['items'][$index];
                    $actual = $grouped['items'][$index];
                    self::assertSame($group['accent'], $actual['accent'], $label);
                    self::assertArrayHasKey('data-theory-content-group', $actual['attrs'], $label);
                    self::assertSame('preserved', $actual['attrs']['data-existing-item'], $label);
                    unset($expected['accent'], $expected['attrs'], $actual['accent'], $actual['attrs']);
                    self::assertSame($expected, $actual, $label.' changed an existing named point or nested its detail');
                    self::assertArrayNotHasKey('items', $grouped['items'][$index], $label.' must not wrap an existing usage point');
                }
                $expectedPanels = 4;
                $namedPoints += 4;
            }
            self::assertSame($node['tail'], $grouped['tail'], $label);
            self::assertSame('preserved', $grouped['attrs']['data-existing-marker'], $label);
            $bindings = [
                'theoryCanonical' => true, 'block' => $block, 'data' => $data,
                'm42Design' => $resolved['design'], 'practiceQuestions' => collect(), 'lessonLinks' => [],
            ];
            app('view')->flushState();
            $xpath = $this->xpath(TheoryContentRenderer::render($resolved['view'], $bindings));
            self::assertSame($expectedPanels, $xpath->query('//article[@data-theory-content-group]')->length, $label);
            self::assertSame(0, $xpath->query('//*[@data-theory-content-group]//*[@data-theory-content-group]')->length, $label);
            self::assertSame(0, $xpath->query('//*[@data-theory-render-fallback]')->length, $label);
            $blocks++;
        }
        self::assertSame(7, $blocks);
        self::assertSame(6, $intros);
        self::assertSame(4, $namedPoints);
    }

    public function test_comparison_preludes_group_without_changing_the_trailing_table(): void
    {
        $blocks = 0;
        $sections = 0;
        foreach ($this->fixtures(false, true) as $fixture) {
            ['block' => $block, 'resolved' => $resolved, 'record' => $record, 'label' => $label] = $fixture;
            self::assertSame('comparison-table', $record['component'], $label);
            self::assertSame($block->uuid, $record['uuid'], $label);
            self::assertSame(hash('sha256', $block->body), $record['body_sha256'], $label);
            self::assertSame($block->sort_order - 1, $record['source_index'], $label);
            $sourceCount = count($resolved['data']['sections']);
            $node = $this->sentinelNode($block, $sourceCount);
            $table = [
                'kind' => 'table', 'caption' => 'Незмінна таблиця', 'headers' => ['English', 'Українська'],
                'rows' => [['cells' => [new HtmlString('<em>Exact source.</em>'), 'Точний переклад.']]],
                'attrs' => ['data-existing-table' => 'preserved'],
            ];
            $node['items'][] = $table;
            $grouped = TheoryContentGroups::apply($node, $block, $resolved['data'], $resolved['design']);
            self::assertCount(1, $record['groups'], $label);
            self::assertSame(range(0, $sourceCount - 1), $record['groups'][0]['indices'], $label);
            self::assertCount(2, $grouped['items'], $label);
            self::assertSame('usage', $grouped['items'][0]['kind'], $label);
            self::assertSame($record['groups'][0]['label'], $grouped['items'][0]['label'], $label);
            self::assertSame(array_slice($node['items'], 0, $sourceCount), $grouped['items'][0]['items'][0]['items'], $label);
            self::assertSame($table, $grouped['items'][1], $label.' changed or moved the exact table');
            self::assertSame($node['intro_html'], $grouped['intro_html'], $label);
            self::assertSame($node['tail'], $grouped['tail'], $label);
            $bindings = [
                'theoryCanonical' => true, 'block' => $block, 'data' => $resolved['data'],
                'm42Design' => $resolved['design'], 'practiceQuestions' => collect(), 'lessonLinks' => [],
            ];
            app('view')->flushState();
            $actual = TheoryContentRenderer::nativeNode($resolved['view'], $bindings);
            self::assertCount(2, $actual['items'], $label);
            self::assertSame('table', $actual['items'][1]['kind'], $label);
            $xpath = $this->xpath(TheoryContentRenderer::render($resolved['view'], $bindings));
            self::assertSame(1, $xpath->query('//article[@data-theory-content-group]')->length, $label);
            self::assertSame(1, $xpath->query('//*[@data-theory-component="table"]')->length, $label);
            self::assertSame(0, $xpath->query('//*[@data-theory-content-group]//*[@data-theory-component="table"]')->length, $label);
            $blocks++;
            $sections += $sourceCount;
        }
        self::assertSame(2, $blocks);
        self::assertSame(5, $sections);
    }

    public function test_foreign_binding_or_changed_data_keeps_complete_ungrouped_node(): void
    {
        $fixture = $this->fixtures()[0];
        ['block' => $block, 'resolved' => $resolved] = $fixture;
        $node = $this->sentinelNode($block, count($resolved['data']['sections']));
        $changedStored = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        $changedStored['sections'][0]['description'] .= ' Змінений авторський текст.';
        foreach ([
            'locale' => 'en', 'seeder' => 'ForeignOwner', 'uuid' => 'foreign-uuid',
            'sort_order' => 999, 'type' => 'comparison-table', 'body' => M26DetailPackage::json($changedStored),
        ] as $field => $value) {
            $foreign = clone $block;
            $foreign->$field = $value;
            self::assertSame($node, TheoryContentGroups::apply($node, $foreign, $resolved['data'], $resolved['design']), $field.' guard');
        }
        // Existing package guards compare exact decoded data, not JSON indentation.
        $formatOnly = clone $block;
        $formatOnly->body .= ' ';
        self::assertSame(
            TheoryContentGroups::apply($node, $block, $resolved['data'], $resolved['design']),
            TheoryContentGroups::apply($node, $formatOnly, $resolved['data'], $resolved['design'])
        );
        self::assertSame($node, TheoryContentGroups::apply($node, $block, $resolved['data'], null));
        $changedDesign = $resolved['design'];
        $changedDesign['color'] = 'unapproved-color';
        self::assertSame($node, TheoryContentGroups::apply($node, $block, $resolved['data'], $changedDesign));
        $changedData = $resolved['data'];
        $changedData['sections'][0]['description'] .= ' Доданий сторонній текст.';
        self::assertSame($node, TheoryContentGroups::apply($node, $block, $changedData, $resolved['design']));
        $changedData = $resolved['data'] + ['foreign_view' => 'theory.show'];
        self::assertSame($node, TheoryContentGroups::apply($node, $block, $changedData, $resolved['design']));
        $incomplete = $node;
        array_pop($incomplete['items']);
        self::assertSame($incomplete, TheoryContentGroups::apply($incomplete, $block, $resolved['data'], $resolved['design']));
    }
}
