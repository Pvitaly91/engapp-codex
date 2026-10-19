<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M41AuthoredTenseComparisonsPackage as Author;
use App\Support\M41ExistingDesignPackage as Design;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M41ExistingDesignPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function block(array $target, int $slot): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot]; $block = new TextBlock;
        $block->forceFill(['id' => 42000 + $slot, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1, 'type' => $source['type'],
            'body' => $source['body'], 'level' => $source['level'] ?? null, 'heading' => $source['heading'] ?? null,
            'column' => $source['column']]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET); libxml_clear_errors();
        return new DOMXPath($dom);
    }

    public function test_mapping_is_exact_selector_only_source_with_thirty_five_unique_points(): void
    {
        [$before] = Author::load(); $master = Author::authorMaster($before); $mapping = Design::load();
        self::assertSame(Design::build($master), $mapping);
        self::assertSame(Design::SOURCE_SHA, hash_file('sha256', base_path(Design::SOURCE)));
        self::assertSame(Author::MASTER_SHA, hash_file('sha256', base_path(Author::MASTER_PATH)));
        self::assertSame(Author::SOURCE_SHA, hash_file('sha256', base_path(Author::SOURCE)));
        $points = []; $components = []; $sections = 0; $forms = 0;
        foreach ($mapping['targets'] as $owner => $target) {
            self::assertSame($master['lessons'][$owner]['identity'], $target['identity']);
            foreach ($target['sections'] as $section) {
                $sections++;
                foreach ($section['points'] as $point) {
                    $points[] = $point['id']; $components[] = $point['component'];
                    self::assertArrayNotHasKey('title', $point); self::assertArrayNotHasKey('paragraphs_uk', $point);
                    self::assertArrayNotHasKey('examples', $point); self::assertArrayNotHasKey('detail', $point);
                }
                if ($section['forms'] !== null) { $forms++; self::assertCount(6, $section['forms']['cells']); }
            }
        }
        self::assertSame(18, $sections); self::assertSame(3, $forms); self::assertCount(35, array_unique($points));
        self::assertContains('usage-panel', $components); self::assertContains('comparison-table', $components);
        self::assertContains('forms-note', $components); self::assertContains('mistakes-grid', $components);
        self::assertContains('summary-list', $components);
    }

    public function test_eight_semantic_error_pairs_partition_complete_author_paragraphs_without_repetition(): void
    {
        [$before] = Author::load(); $master = Author::authorMaster($before); $mapping = Design::load(); $pairs = [];
        foreach ($mapping['targets'] as $owner => $target) {
            foreach ($target['sections'] as $section) {
                foreach ($section['points'] as $point) {
                    $source = $master['lessons'][$owner]['sections'][$section['section_index']]['points'][$point['point_index']];
                    foreach ($point['error_paragraphs'] as $plan) {
                        $paragraph = $source['paragraphs_uk'][$plan['paragraph_index']];
                        $fragments = Design::paragraphFragments($paragraph, $plan);
                        self::assertSame($paragraph, implode('', array_column($fragments, 'text')));
                        $wrong = array_values(array_filter($fragments, fn ($fragment) => $fragment['role'] === 'wrong'));
                        $right = array_values(array_filter($fragments, fn ($fragment) => $fragment['role'] === 'right'));
                        self::assertCount(1, $wrong); self::assertCount(1, $right);
                        $pairs[] = [$wrong[0]['text'], $right[0]['text']];
                    }
                }
            }
        }
        self::assertSame([
            ['Did she took the key?', 'Did she take the key?'],
            ['We were wait outside.', 'We were waiting outside.'],
            ['Does she works here?', 'Does she work here?'],
            ['They waiting outside.', 'They are waiting outside.'],
            ['I am knowing the answer.', 'I know the answer.'],
            ['She has wrote the note.', 'She has written the note.'],
            ['Did she wrote the note?', 'Did she write the note?'],
            ['I have checked it yesterday.', 'I checked it yesterday.'],
        ], $pairs);
    }

    public static function mappingMutations(): array
    {
        return [
            'false-error-on-correct-alternative' => ['component'],
            'invented-universal-gray' => ['color'],
            'point-order' => ['order'],
            'unapproved-hidden-detail' => ['detail'],
            'owner' => ['owner'],
            'source-range' => ['range'],
            'extra-source-table' => ['duplicate'],
        ];
    }

    #[DataProvider('mappingMutations')]
    public function test_mapping_changes_cannot_select_an_unapproved_semantic_view(string $mutation): void
    {
        [$before] = Author::load(); $master = Author::authorMaster($before); $mapping = Design::load();
        switch ($mutation) {
            case 'component': $mapping['targets'][0]['sections'][4]['points'][2]['component'] = 'mistakes-grid'; break;
            case 'color': $mapping['targets'][0]['sections'][0]['points'][0]['color'] = 'gray'; break;
            case 'order': $mapping['targets'][0]['sections'][0]['points'] = array_reverse($mapping['targets'][0]['sections'][0]['points']); break;
            case 'detail': $mapping['targets'][0]['sections'][0]['points'][0]['detail'] = 'Invented short note'; break;
            case 'owner': $mapping['targets'][0]['identity'] = 'ForeignOwner'; break;
            case 'range': $mapping['targets'][0]['sections'][4]['points'][0]['error_paragraphs'][0]['fragments'][0]['length_bytes']++; break;
            case 'duplicate': $mapping['targets'][0]['sections'][1]['forms']['cells'][] = $mapping['targets'][0]['sections'][1]['forms']['cells'][0]; break;
        }
        $this->expectException(RuntimeException::class); Design::validate($master, $mapping);
    }

    public function test_decorator_preserves_all_accepted_content_and_only_selects_exact_physical_sections(): void
    {
        [, $projection] = Author::load(); $details = []; $formCells = 0;
        foreach ($projection['targets'] as $target) {
            $count = 0;
            foreach ($target['section_slots'] as $slot) {
                $block = $this->block($target, $slot); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $presentation = Author::presentation($block, $data);
                self::assertNotNull($presentation); self::assertSame(Design::VIEW, $presentation['native_view']);
                $originalPresentation = $presentation;
                unset($originalPresentation['data']['m41_existing_design']);
                foreach ($originalPresentation['points'] as &$originalPoint) {
                    unset($originalPoint['fragments'][0]['m41_existing_design_detail']);
                }
                unset($originalPoint);
                $originalPresentation['native_view'] = 'engram.theory.blocks-v3.m41-author-section';
                self::assertSame($presentation, Design::decorate($block, $data, $originalPresentation));
                $wrongPresentation = $originalPresentation;
                $wrongPresentation['native_view'] = 'foreign.executable.view';
                self::assertNull(Design::decorate($block, $data, $wrongPresentation));
                $wrongPresentation = $originalPresentation;
                $wrongPresentation['data']['author_section']['title'] = 'Unapproved heading';
                self::assertNull(Design::decorate($block, $data, $wrongPresentation));
                $render = $presentation['data']; $config = $render['m41_existing_design']; unset($render['m41_existing_design']);
                $expected = $data;
                foreach ($presentation['points'] as $index => $point) { unset($expected['sections'][$index]['note']); $count++; }
                self::assertSame($expected, $render, 'No raw author section/intro/basic field may be rewritten.');
                self::assertSame(array_column($data['author_section']['points'], 'id'), array_column($config['points'], 'id'));
                foreach ($presentation['points'] as $pointIndex => $point) {
                    self::assertSame('m41-author-html', $point['fragments'][0]['type']);
                    self::assertSame('block-'.$point['key'].'-detail', $point['fragments'][0]['id']);
                    self::assertSame($originalPresentation['points'][$pointIndex]['fragments'][0]['value'], $point['fragments'][0]['value']);
                    self::assertSame($data['author_section']['points'][$pointIndex]['detail'], $point['fragments'][0]['m41_existing_design_detail']);
                }
                if ($config['forms'] !== null) {
                    self::assertSame($data['author_section']['table']['columns'], $config['forms']['columns']);
                    foreach ($config['forms']['rows'] as $row) {
                        self::assertSame($data['author_section']['table']['rows'][$row['row_index']][0], $row['label']);
                        self::assertCount(2, $row['cells']);
                        foreach ($row['cells'] as $cell) {
                            self::assertSame($data['author_section']['table']['rows'][$row['row_index']][$cell['column_index']], $cell['en']."\n".$cell['uk']);
                            $formCells++;
                        }
                    }
                }
                foreach (['seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign-uuid', 'type' => 'box', 'sort_order' => 99] as $field => $value) {
                    $foreign = clone $block; $foreign->setAttribute($field, $value);
                    self::assertNull(Design::decorate($foreign, $data, $presentation));
                    self::assertNull(Author::presentation($foreign, $data));
                }
                $altered = $data; $altered['unknown'] = 'extra';
                self::assertNull(Design::decorate($block, $altered, $presentation));
            }
            $details[] = $count;
        }
        self::assertSame([4, 5, 5], $details); self::assertSame(18, $formCells);
    }

    public function test_native_render_uses_forms_colors_examples_comparisons_mistakes_and_summaries(): void
    {
        [, $projection] = Author::load(); $counts = ['points' => 0, 'forms' => 0, 'wrong' => 0, 'right' => 0, 'details' => 0];
        foreach ($projection['targets'] as $target) {
            foreach ($target['section_slots'] as $slot) {
                $block = $this->block($target, $slot); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $html = view('theory.partials.content-block', compact('block', 'data'))->render(); $xp = $this->xpath($html);
                $counts['points'] += $xp->query('//*[@data-m41-basic-point]')->length;
                $counts['forms'] += $xp->query('//*[@data-m41-form-cell]')->length;
                $counts['wrong'] += $xp->query('//*[@data-m41-error-fragment="wrong"]')->length;
                $counts['right'] += $xp->query('//*[@data-m41-error-fragment="right"]')->length;
                $counts['details'] += $xp->query('//details')->length;
                self::assertSame(0, $xp->query('//details[@open]')->length);
                self::assertStringNotContainsString('m41-section-styles', $html);
                foreach ($data['author_section']['points'] as $point) {
                    $nodes = $xp->query('//*[@data-m41-basic-point="'.$point['id'].'"]'); self::assertSame(1, $nodes->length);
                    $node = $nodes->item(0);
                    foreach ($point['examples'] as $example) {
                        self::assertStringContainsString($example['en'], $node->textContent);
                        self::assertStringContainsString($example['uk'], $node->textContent);
                    }
                    if (isset($point['detail'])) {
                        self::assertSame(1, $xp->query('.//details', $node)->length, 'Each detail remains inside its exact author point.');
                    }
                    if (in_array($point['id'], ['past-not-error', 'present-think', 'perfect-today', 'perfect-duration'], true)) {
                        self::assertSame(0, $xp->query('.//*[@data-m41-error-fragment]', $node)->length, 'A grammatical contrast is not a correction pair.');
                    }
                }
            }
        }
        self::assertSame(['points' => 35, 'forms' => 18, 'wrong' => 8, 'right' => 8, 'details' => 14], $counts);
    }

    public function test_rejected_render_detail_falls_back_to_complete_accepted_content_without_disclosures(): void
    {
        // Model an unavailable/corrupted detail renderer, not a source edit.
        // The finite owner/data guards still accept the original source bytes.
        View::composer('engram.theory.blocks-v3.m41-native-detail', static function ($view): void {
            $view->with('detail', ['title' => '', 'paragraphs_uk' => [], 'examples' => []]);
        });
        [, $projection] = Author::load(); $fallbacks = 0; $forms = 0;
        foreach ($projection['targets'] as $target) {
            foreach ($target['section_slots'] as $slot) {
                $block = $this->block($target, $slot); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $hasDetail = count(array_filter($data['author_section']['points'], fn ($point) => isset($point['detail']))) > 0;
                $html = view('theory.partials.content-block', compact('block', 'data'))->render();
                $xp = $this->xpath($html); $text = $xp->query('//body')->item(0)->textContent;
                self::assertSame(0, $xp->query('//details')->length, 'Rejected detail must remain visible instead of becoming an invalid disclosure.');
                self::assertSame(count($data['author_section']['points']), $xp->query('//*[@data-m41-basic-point]')->length);
                foreach ($data['author_section']['points'] as $point) {
                    $node = $xp->query('//*[@data-m41-basic-point="'.$point['id'].'"]')->item(0);
                    self::assertNotNull($node);
                    foreach ($point['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $node->textContent); }
                    foreach ($point['examples'] as $example) {
                        self::assertStringContainsString($example['en'], $node->textContent);
                        self::assertStringContainsString($example['uk'], $node->textContent);
                    }
                    if (isset($point['detail'])) {
                        self::assertSame(1, $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," theory-note ")]', $node)->length);
                        foreach ($point['detail']['paragraphs_uk'] as $paragraph) { self::assertSame(1, substr_count($node->textContent, $paragraph)); }
                        foreach ($point['detail']['examples'] as $example) {
                            self::assertStringContainsString($example['en'], $node->textContent);
                            self::assertStringContainsString($example['uk'], $node->textContent);
                        }
                    }
                }
                if ($hasDetail) {
                    $fallbacks++;
                    self::assertSame(1, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," m41-author-section ")]')->length,
                        'Missing ephemeral design config must select the fixed full accepted author view, not throw.');
                    self::assertSame(0, $xp->query('//*[@data-m41-native-layout]')->length);
                }
                if (isset($data['author_section']['table'])) {
                    $forms++;
                    self::assertSame(6, $xp->query('//*[@data-m41-form-cell]')->length);
                    self::assertSame(0, $xp->query('//table')->length);
                    foreach ($data['author_section']['table']['columns'] as $column) { self::assertStringContainsString($column, $text); }
                    // Exercise the raw-data router path for tables too. These
                    // three sections intentionally have no authored detail.
                    $raw = $this->xpath(view(Design::VIEW, compact('block', 'data'))->render());
                    self::assertSame(1, $raw->query('//table')->length);
                    self::assertSame(0, $raw->query('//*[@data-m41-form-cell]')->length);
                    $rawText = $raw->query('//body')->item(0)->textContent;
                    foreach ($data['author_section']['table']['rows'] as $row) {
                        foreach ($row as $cell) { foreach (explode("\n", $cell) as $line) { self::assertStringContainsString($line, $rawText); } }
                    }
                }
            }
        }
        self::assertSame(10, $fallbacks); self::assertSame(3, $forms);
        self::assertSame(Author::MASTER_SHA, hash_file('sha256', base_path(Author::MASTER_PATH)));
        self::assertSame(Author::SOURCE_SHA, hash_file('sha256', base_path(Author::SOURCE)));
    }

    public function test_three_real_m41_comparisons_have_two_columns_without_changing_default_native_table(): void
    {
        [, $projection] = Author::load(); $tables = 0;
        foreach ($projection['targets'] as $target) {
            foreach ($target['section_slots'] as $slot) {
                $block = $this->block($target, $slot); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                $presentation = Author::presentation($block, $data);
                $comparisons = array_filter($presentation['data']['m41_existing_design']['points'], fn ($point) => $point['component'] === 'comparison-table');
                if ($comparisons === []) { continue; }
                $xp = $this->xpath(view('theory.partials.content-block', compact('block', 'data'))->render());
                foreach ($comparisons as $config) {
                    $point = $data['author_section']['points'][$config['point_index']];
                    $node = $xp->query('//*[@data-m41-basic-point="'.$point['id'].'"]')->item(0);
                    self::assertNotNull($node); self::assertSame(1, $xp->query('.//table', $node)->length);
                    self::assertSame(2, $xp->query('.//table/thead/tr/th', $node)->length);
                    $rows = $xp->query('.//table/tbody/tr', $node);
                    self::assertSame(count($point['examples']), $rows->length);
                    foreach ($point['examples'] as $index => $example) {
                        $cells = $xp->query('./td', $rows->item($index)); self::assertSame(2, $cells->length);
                        self::assertSame($example['en'], trim($cells->item(0)->textContent));
                        self::assertSame($example['uk'], trim($cells->item(1)->textContent));
                        self::assertSame(1, substr_count($rows->item($index)->textContent, $example['en']));
                        self::assertSame(1, substr_count($rows->item($index)->textContent, $example['uk']));
                    }
                    $tables++;
                }
            }
        }
        self::assertSame(3, $tables);
        // An ordinary native owner still gets its original EN/UA/notes table.
        $block = new TextBlock;
        $block->forceFill(['id' => 42999, 'uuid' => 'ordinary-native-control', 'seeder' => 'UnchangedNativeControl',
            'locale' => 'uk', 'type' => 'comparison-table', 'level' => null, 'column' => 'right']);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        $data = ['title' => 'Контрольний native-блок', 'rows' => [['en' => 'A native example.', 'ua' => 'Звичайний приклад.', 'note' => 'Звичайна примітка.']]];
        $control = $this->xpath(view('engram.theory.blocks-v3.comparison-table', compact('block', 'data'))->render());
        self::assertSame(3, $control->query('//table/thead/tr/th')->length);
        self::assertSame(3, $control->query('//table/tbody/tr/td')->length);
        self::assertSame('A native example.', trim($control->query('//table/tbody/tr/td')->item(0)->textContent));
        self::assertSame('Звичайний приклад.', trim($control->query('//table/tbody/tr/td')->item(1)->textContent));
        self::assertSame('Звичайна примітка.', trim($control->query('//table/tbody/tr/td')->item(2)->textContent));
    }
}
