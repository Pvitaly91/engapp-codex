<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M44AuthoredFutureFormsPackage as Package;
use App\Support\M44SimplifiedPresentation as Presentation;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** The source of truth is the frozen author master, not compact generated HTML. */
class M44SimplifiedPresentationTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const MASTER_SHA = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function read(string $path): array
    {
        return json_decode(file_get_contents(base_path($path)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function master(): array
    {
        $path = 'docs/content/m44-authored-future-forms.v1.0.0.json';
        self::assertSame(self::MASTER_SHA, hash_file('sha256', base_path($path)));

        return $this->read($path);
    }

    private function block(array $target, int $slot): TextBlock
    {
        $config = $target['after']['page']['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill([
            'id' => 44000 + $slot,
            'uuid' => M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1,
            'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null,
            'heading' => $config['heading'] ?? null, 'column' => $config['column'],
            'css_class' => $config['css_class'] ?? null,
        ]);
        $block->setRelation('tags', collect());
        $block->setRelation('page', null);

        return $block;
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function normalized(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function learnerText(DOMNode $node): string
    {
        $copy = $node->cloneNode(true);
        $xp = new DOMXPath($copy->ownerDocument);
        foreach (iterator_to_array($xp->query('.//summary | .//noscript | .//script | .//style | .//*[@data-theory-ui]', $copy)) as $ui) {
            $ui->parentNode?->removeChild($ui);
        }

        return $this->normalized($copy->textContent);
    }

    private function exampleStrings(array $examples): array
    {
        $strings = [];
        foreach ($examples as $example) {
            array_push($strings, $example['en'], $example['uk']);
            if (isset($example['note_uk'])) {
                $strings[] = $example['note_uk'];
            }
        }

        return $strings;
    }

    private function pointStrings(array $point): array
    {
        $strings = [$point['title']];
        if (isset($point['formula'])) {
            $strings[] = $point['formula'];
        }
        array_push($strings, ...$point['paragraphs_uk']);
        foreach (['wrong_en', 'wrong_uk', 'right_en', 'right_uk'] as $field) {
            if (isset($point[$field])) {
                $strings[] = $point[$field];
            }
        }
        array_push($strings, ...$this->exampleStrings($point['examples']));
        if (isset($point['detail'])) {
            array_push($strings, $point['detail']['title'], ...$point['detail']['paragraphs_uk']);
            array_push($strings, ...$this->exampleStrings($point['detail']['examples']));
        }

        return $strings;
    }

    /** Count intentional source repetitions, including text contained in longer source fields. */
    private function assertSourceOccurrences(string $text, array $sourceStrings, array $addedStrings, string $context): void
    {
        $permitted = array_map($this->normalized(...), array_merge($sourceStrings, $addedStrings));
        foreach (array_unique(array_map($this->normalized(...), $sourceStrings)) as $needle) {
            self::assertNotSame('', $needle, $context);
            $expected = array_sum(array_map(fn ($value) => substr_count($value, $needle), $permitted));
            self::assertSame($expected, substr_count($text, $needle), $context.': '.$needle);
        }
    }

    public function test_finite_groups_cover_every_original_point_and_move_only_identical_examples(): void
    {
        $master = $this->master();
        self::assertSame(Presentation::SHA, hash_file('sha256', base_path(Presentation::PATH)));
        self::assertSame(Presentation::FORMS_SHA, hash_file('sha256', base_path(Presentation::FORMS_PATH)));
        self::assertSame(Presentation::SHA, $this->read(Presentation::FORMS_PATH)['base_sha256']);
        self::assertSame(Presentation::CONT_FORMS_SHA, hash_file('sha256', base_path(Presentation::CONT_FORMS_PATH)));
        self::assertSame(Presentation::SHA, $this->read(Presentation::CONT_FORMS_PATH)['base_sha256']);
        $projection = $this->read(Presentation::PATH);
        self::assertSame(self::MASTER_SHA, $projection['master_sha256']);
        $lessonCounts = []; $sectionCount = 0; $pointCount = 0;
        foreach ($master['lessons'] as $lesson) {
            $groupCount = 0;
            foreach ($lesson['sections'] as $section) {
                $sectionCount++;
                $groups = Presentation::section($section);
                self::assertNotNull($groups, $section['id']);
                $groupCount += count($groups);
                $points = array_column($section['points'], null, 'id');
                $covered = [];
                foreach ($groups as $group) {
                    self::assertNotSame('', trim($group['reason_uk']));
                    self::assertSame($group['points'][0], $group['id']);
                    self::assertSame(array_map(fn ($id) => $points[$id], $group['points']), $group['sources']);
                    array_push($covered, ...$group['points']);
                    $selectedKeys = []; $selectedExamples = [];
                    foreach ($group['example_refs'] as $reference) {
                        self::assertContains($reference['point'], $group['points']);
                        $key = $reference['point'].':'.$reference['index'];
                        self::assertNotContains($key, $selectedKeys, 'An example must not be promoted twice');
                        $selectedKeys[] = $key;
                        $selectedExamples[] = $points[$reference['point']]['examples'][$reference['index']];
                    }
                    self::assertSame($selectedExamples, $group['examples'], 'EN, translation and note move together unchanged');
                    self::assertSame($selectedKeys, array_keys($group['selected']));
                    if ($group['formula_from'] !== null) {
                        self::assertContains($group['formula_from'], $group['points']);
                        self::assertSame($points[$group['formula_from']]['formula'], $group['formula']);
                    } else {
                        self::assertNull($group['formula']);
                    }
                    if (!$group['disclosure']) {
                        self::assertCount(1, $group['sources']);
                        self::assertSame([], $group['basic_uk']);
                        self::assertSame([], $group['example_refs']);
                        self::assertNull($group['formula_from']);
                    }
                }
                self::assertSame(array_keys($points), $covered, 'Exact source coverage in author order');
                self::assertSame(count($covered), count(array_unique($covered)));
                $pointCount += count($covered);
            }
            $lessonCounts[] = $groupCount;
        }
        self::assertSame([19, 12, 16], $lessonCounts);
        self::assertSame([20, 69], [$sectionCount, $pointCount]);
    }

    public function test_compact_rendering_preserves_author_fields_once_and_existing_point_detail_anchors(): void
    {
        [, $package] = Package::load();
        $master = $this->master();
        $mapping = $this->read('docs/content/m44-native-mapping.v1.0.0.json');
        $details = 0; $points = 0; $tables = 0;
        foreach ($master['lessons'] as $lessonIndex => $lesson) {
            $lessonHtml = '';
            foreach ($lesson['sections'] as $sectionIndex => $section) {
                $block = $this->block($package['targets'][$lessonIndex], $mapping['targets'][$lessonIndex]['section_order'][$sectionIndex]['slot']);
                self::assertSame($section, json_decode($block->body, true, flags: JSON_THROW_ON_ERROR)['author_section']);
                $html = view('theory.partials.content-block', ['block' => $block, 'm44StyleContext' => true])->render();
                $lessonHtml .= $html;
                $xp = $this->xpath($html);
                self::assertSame(1, $xp->query('//*[@data-m44-simplified and @data-m44-author-section="'.$section['id'].'"]')->length);
                self::assertSame(0, $xp->query('//details[@open]')->length, 'Depth starts closed');
                $groups = Presentation::section($section);
                self::assertSame(count($groups), $xp->query('//*[@data-m44-compact-group]')->length);
                foreach ($groups as $group) {
                    $nodes = $xp->query('//*[@data-m44-compact-group="'.$group['id'].'"]');
                    self::assertSame(1, $nodes->length);
                    $node = $nodes->item(0);
                    $authorStrings = [];
                    foreach ($group['sources'] as $point) {
                        $points++;
                        array_push($authorStrings, ...$this->pointStrings($point));
                        $sourceNodes = $xp->query('.//*[@data-m44-basic-point="'.$point['id'].'"] | self::*[@data-m44-basic-point="'.$point['id'].'"]', $node);
                        self::assertSame(1, $sourceNodes->length, 'Unique original point '.$point['id']);
                        self::assertSame(1, $xp->query('//*[@id="'.$point['id'].'"]')->length, 'Original point deep link');
                        if (isset($point['detail'])) {
                            $details++;
                            self::assertSame(1, $xp->query('.//*[@id="block-'.$point['detail']['id'].'"]', $sourceNodes->item(0))->length);
                        }
                    }
                    $added = $group['disclosure'] ? array_merge([$group['title']], $group['basic_uk']) : [];
                    array_push($added, ...array_column($group['form_rows'] ?? [], 'label'));
                    $this->assertSourceOccurrences($this->learnerText($node), $authorStrings, $added, $group['id']);
                    if ($group['disclosure']) {
                        self::assertSame(1, $xp->query('.//details', $node)->length, 'One group opens only its own depth');
                        $basic = $node->cloneNode(true);
                        foreach (iterator_to_array((new DOMXPath($basic->ownerDocument))->query('.//details', $basic)) as $detail) {
                            $detail->parentNode->removeChild($detail);
                        }
                        $basicText = $this->learnerText($basic);
                        foreach ($group['basic_uk'] as $paragraph) {
                            self::assertStringContainsString($paragraph, $basicText);
                        }
                        foreach ($this->exampleStrings($group['examples']) as $value) {
                            self::assertStringContainsString($value, $basicText, 'Selected example stays outside details');
                        }
                        foreach ($group['form_rows'] ?? [] as $row) {
                            self::assertStringContainsString($row['formula'], $basicText, 'Formula stays outside details');
                            self::assertSame($section['points'][array_search($row['point'], array_column($section['points'], 'id'), true)]['formula'], $row['formula']);
                        }
                    }
                }

                if ($section['id'] === 'm44-will-forms') {
                    self::assertCount(2, $groups);
                    self::assertSame(6, $xp->query('//*[@data-m44-form-row and not(ancestor::details)]')->length);
                    foreach (['m44-will-form-question-more', 'm44-will-form-going-question-more'] as $legacyId) {
                        self::assertSame(1, $xp->query('//*[@id="'.$legacyId.'"]')->length);
                    }
                }
                if ($section['id'] === 'm44-cont-forms') {
                    self::assertCount(2, $groups);
                    self::assertSame(3, $xp->query('//*[@data-m44-form-row and not(ancestor::details)]')->length);
                    self::assertSame(1, $xp->query('//details')->length);
                    self::assertSame(1, $xp->query('//*[@id="m44-cont-form-question-more"]')->length);
                    self::assertSame(1, $xp->query('//*[@id="m44-cont-form-spelling" and not(ancestor::details)]')->length);
                    self::assertSame(2, $xp->query('//*[@data-m44-short-answers]/p')->length);
                }

                // Notes belong to the section, not its grouped points: check their own remaining DOM.
                $sectionNode = $xp->query('//*[@data-m44-author-section]')->item(0)->cloneNode(true);
                $sectionXp = new DOMXPath($sectionNode->ownerDocument);
                foreach (iterator_to_array($sectionXp->query('.//*[@data-m44-compact-group] | .//table', $sectionNode)) as $part) {
                    $part->parentNode->removeChild($part);
                }
                $notes = array_merge(isset($section['intro_uk']) ? [$section['intro_uk']] : [], $section['notes_uk'] ?? [], $this->exampleStrings($section['note_examples'] ?? []));
                $this->assertSourceOccurrences($this->learnerText($sectionNode), $notes, [], $section['id'].' section notes');

                if (isset($section['table'])) {
                    $tables++;
                    self::assertSame($section['table']['columns'], array_map(fn ($n) => trim($n->textContent), iterator_to_array($xp->query('//table/thead/tr/th'))));
                    self::assertSame(count($section['table']['rows']), $xp->query('//table/tbody/tr')->length);
                    foreach ($section['table']['rows'] as $rowIndex => $row) {
                        $cells = $xp->query('//table/tbody/tr['.($rowIndex + 1).']/td');
                        self::assertSame(count($row), $cells->length);
                        foreach ($row as $column => $cell) {
                            $values = is_string($cell) ? [$cell] : (isset($cell['en']) ? $this->exampleStrings([$cell]) : [$cell['text_uk'] ?? $cell['formula']]);
                            self::assertSame($this->normalized(implode(' ', $values)), $this->learnerText($cells->item($column)), 'Table content and order remain exact');
                        }
                    }
                }
            }
            $allIds = array_map(fn ($n) => $n->getAttribute('id'), iterator_to_array($this->xpath($lessonHtml)->query('//*[@id]')));
            self::assertSame(count($allIds), count(array_unique($allIds)), 'Unique anchors across all sections of a lesson');
        }
        self::assertSame([69, 7, 4], [$points, $details, $tables]);
    }

    public function test_changed_source_cannot_reuse_the_frozen_projection_even_after_warming_it(): void
    {
        $master = $this->master();
        $source = $master['lessons'][1]['sections'][1];
        self::assertNotNull(Presentation::section($source));
        $mutations = [];
        $changed = $source; $changed['points'][0]['paragraphs_uk'][0] .= ' Foreign text.'; $mutations['paragraph'] = $changed;
        $changed = $source; $changed['points'][0]['examples'][0]['uk'] = 'Інший переклад.'; $mutations['translation'] = $changed;
        $changed = $source; $changed['points'][0]['examples'][0]['en'] = 'Different example.'; $mutations['example'] = $changed;
        $changed = $source; $changed['points'][0]['detail']['paragraphs_uk'][0] .= ' Foreign detail.'; $mutations['detail'] = $changed;
        $changed = $source; $changed['points'][0]['id'] = 'foreign-point'; $mutations['point identity'] = $changed;
        $changed = $source; $changed['points'] = array_reverse($changed['points']); $mutations['author order'] = $changed;
        $changed = $source; $changed['id'] = 'foreign-section'; $mutations['section identity'] = $changed;
        foreach ($mutations as $name => $changed) {
            self::assertNull(Presentation::section($changed), 'Reject changed '.$name);
        }
        self::assertNotNull(Presentation::section($source), 'Rejections do not contaminate the valid source');
    }
}
