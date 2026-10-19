<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M43AuthoredTenseUsagePackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Expectations come from the independently frozen author master, never generated AFTER. */
class M43AuthorFidelityTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const SHA = 'd9afc130ec223202ea83781147f9966855227d3194ccd7707c9b3c037b62e2f5';

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function read(string $file): array
    {
        return json_decode(file_get_contents(base_path($file)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function master(): array
    {
        $file = 'docs/content/m43-authored-tense-usage.v1.0.0.json';
        self::assertSame(self::SHA, hash_file('sha256', base_path($file)));
        return $this->read($file);
    }

    private function block(array $target, int $slot): TextBlock
    {
        $config = $target['after']['page']['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill(['id' => 43000 + $slot, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1, 'type' => $config['type'],
            'body' => $config['body'], 'level' => $config['level'] ?? null, 'heading' => $config['heading'] ?? null,
            'column' => $config['column'], 'css_class' => $config['css_class'] ?? null]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors(); return new DOMXPath($dom);
    }

    private function normalized(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function stringsInOrder(string $text, array $strings): void
    {
        $offset = 0; $text = $this->normalized($text);
        foreach ($strings as $string) {
            $needle = $this->normalized($string); $position = strpos($text, $needle, $offset);
            self::assertNotFalse($position, 'Missing/reordered author text: '.$string);
            $offset = $position + strlen($needle);
        }
    }

    public function test_frozen_source_checksums_authorship_and_finite_counts(): void
    {
        $master = $this->master();
        foreach ($this->read('docs/content/m43-checksums.v1.0.0.json')['files'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)), $path);
        }
        self::assertTrue($master['policy']['codex_authors_new_teaching_text']);
        self::assertFalse($master['policy']['personally_sentence_approved_by_user']);
        self::assertSame('f1a552303cbc46713fa4a34e71fd6be01cc25a42', $master['base_commit']);
        $counts = [];
        foreach ($master['lessons'] as $lesson) {
            $points = array_merge(...array_column($lesson['sections'], 'points'));
            $counts[] = [count($lesson['sections']), count($points), count(array_filter($points, fn ($p) => isset($p['detail']))),
                count($lesson['practice']), array_sum(array_map(fn ($t) => count($t['controls']), $lesson['practice']))];
        }
        self::assertSame([[6, 22, 3, 6, 11], [7, 13, 1, 6, 13], [6, 15, 2, 6, 11]], $counts);
    }

    public function test_original_metadata_blocks_uuids_navigation_and_all_other_locales_remain_bound(): void
    {
        [$before, $package] = Package::load(); $master = $this->master();
        $map = $this->read('docs/content/m43-native-mapping.v1.0.0.json');
        foreach ($package['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $after = $target['after'];
            self::assertSame($master['lessons'][$i]['identity'], $target['identity']);
            self::assertSame($after, $this->read($master['lessons'][$i]['definition_path']));
            $restored = $after; $restored['page']['blocks'] = $old['page']['blocks'];
            $restored['page']['subtitle_text'] = $old['page']['subtitle_text'];
            $restored['page']['subtitle_html'] = $old['page']['subtitle_html'];
            self::assertSame($old, $restored);
            self::assertSame($master['lessons'][$i]['subtitle'], $after['page']['subtitle_text']);
            foreach ($old['page']['blocks'] as $slot => $config) {
                $changed = $after['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1),
                    M26DetailPackage::uuid($target['identity'], $changed, $slot + 1));
                $changed['type'] = $config['type']; $changed['body'] = $config['body'];
                self::assertSame($config, $changed, 'Existing identity/configuration '.$slot);
            }
            $nav = $map['targets'][$i]['navigation_slot'];
            self::assertSame($old['page']['blocks'][$nav], $after['page']['blocks'][$nav]);
        }
    }

    public function test_all_nineteen_native_sections_render_every_author_field_once_and_all_six_details_inside_own_points(): void
    {
        [, $package] = Package::load(); $master = $this->master(); $details = 0; $points = 0; $tables = 0;
        $map = $this->read('docs/content/m43-native-mapping.v1.0.0.json');
        foreach ($master['lessons'] as $i => $lesson) {
            foreach ($lesson['sections'] as $j => $section) {
                $block = $this->block($package['targets'][$i], $map['targets'][$i]['section_order'][$j]['slot']);
                $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($section, $data['author_section']);
                $html = view('theory.partials.content-block', ['block' => $block, 'm43StyleContext' => true])->render(); $xp = $this->xpath($html);
                self::assertSame(1, $xp->query('//*[@data-m43-author-section="'.$section['id'].'"]')->length);
                self::assertSame(0, $xp->query('//details[@open]')->length);
                foreach ($section['points'] as $point) {
                    $points++; $nodes = $xp->query('//*[@data-m43-basic-point="'.$point['id'].'"]');
                    self::assertSame(1, $nodes->length); $node = $nodes->item(0);
                    $expected = [$point['title']];
                    if (isset($point['formula'])) { $expected[] = $point['formula']; }
                    array_push($expected, ...$point['paragraphs_uk']);
                    foreach (['wrong_en', 'wrong_uk', 'right_en', 'right_uk'] as $field) {
                        if (isset($point[$field])) { $expected[] = $point[$field]; }
                    }
                    foreach ($point['examples'] as $example) {
                        array_push($expected, $example['en'], $example['uk']);
                        if (isset($example['note_uk'])) { $expected[] = $example['note_uk']; }
                    }
                    if (isset($point['detail'])) {
                        $details++;
                        self::assertSame(1, $xp->query('.//details', $node)->length);
                        self::assertSame(1, $xp->query('.//*[@id="block-'.$point['detail']['id'].'"]', $node)->length);
                        array_push($expected, $point['detail']['title'], ...$point['detail']['paragraphs_uk']);
                        foreach ($point['detail']['examples'] as $example) {
                            array_push($expected, $example['en'], $example['uk']);
                            if (isset($example['note_uk'])) { $expected[] = $example['note_uk']; }
                        }
                    } else { self::assertSame(0, $xp->query('.//details', $node)->length); }
                    $this->stringsInOrder($node->textContent, $expected);
                    // Repeated strings inside the author point are intentional; no additional renderer copy is allowed.
                    $learnerCopy = $node->cloneNode(true);
                    foreach ($xp->query('.//summary | .//noscript | .//*[@data-theory-ui]', $learnerCopy) as $ui) { $ui->parentNode->removeChild($ui); }
                    foreach (array_count_values($expected) as $value => $count) {
                        $containedByLongerValue = count(array_filter($expected, fn ($other) => $other !== $value && str_contains($other, $value))) > 0;
                        if (!$containedByLongerValue) { self::assertSame($count, substr_count($this->normalized($learnerCopy->textContent), $this->normalized($value)), $point['id'].': '.$value); }
                    }
                }
                $bodyText = $xp->query('//body')->item(0)->textContent;
                foreach (array_merge([$section['intro_uk'] ?? ''], $section['notes_uk'] ?? []) as $text) {
                    if ($text !== '') { self::assertStringContainsString($text, $bodyText); }
                }
                foreach ($section['note_examples'] ?? [] as $example) {
                    self::assertStringContainsString($example['en'], $bodyText); self::assertStringContainsString($example['uk'], $bodyText);
                    if (isset($example['note_uk'])) { self::assertStringContainsString($example['note_uk'], $bodyText); }
                }
                if (isset($section['table'])) {
                    $tables++;
                    self::assertSame($section['table']['columns'], array_map(fn ($n) => trim($n->textContent), iterator_to_array($xp->query('//table/thead/tr/th'))));
                    foreach ($xp->query('//*[@data-theory-ui]') as $ui) { $ui->parentNode->removeChild($ui); }
                    foreach ($section['table']['rows'] as $r => $row) {
                        $cells = $xp->query('//table/tbody/tr['.($r + 1).']/td'); self::assertSame(count($row), $cells->length);
                        foreach ($row as $c => $cell) {
                            $values = is_string($cell) ? [$cell] : (isset($cell['en']) ? [$cell['en'], $cell['uk']] : [$cell['text_uk']]);
                            if (is_array($cell) && isset($cell['note_uk'])) { $values[] = $cell['note_uk']; }
                            $this->stringsInOrder($cells->item($c)->textContent, $values);
                        }
                    }
                }
                $ids = array_map(fn ($n) => $n->getAttribute('id'), iterator_to_array($xp->query('//*[@id]')));
                self::assertSame(count($ids), count(array_unique($ids)), 'No duplicate learner DOM anchors');
            }
        }
        self::assertSame([50, 6, 4], [$points, $details, $tables]);
    }

    public function test_owner_locale_identity_mutations_reject_interactivity_but_keep_complete_static_material(): void
    {
        [, $package] = Package::load(); $master = $this->master();
        $map = $this->read('docs/content/m43-native-mapping.v1.0.0.json');
        self::assertFalse(Package::hasStoredAuthor(['author_section' => ['id' => 'm41-past-simple'], 'm41_v1' => ['role' => 'section']]));
        self::assertFalse(Package::hasStoredAuthor(['author_practice' => [['id' => 'foreign']]]));
        foreach ($package['targets'] as $i => $target) {
            foreach ($map['targets'][$i]['section_order'] as $sectionPlan) {
                $block = $this->block($target, $sectionPlan['slot']); $data = json_decode($block->body, true);
                foreach (['seeder' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'foreign', 'sort_order' => 999, 'type' => 'box'] as $field => $value) {
                    $foreign = clone $block; $foreign->setAttribute($field, $value);
                    self::assertNull(Package::presentation($foreign, $data));
                    $fallback = $this->xpath(view('theory.partials.content-block', ['block' => $foreign, 'm43StyleContext' => true])->render());
                    self::assertSame(1, $fallback->query('//*[@data-m43-static-fallback]')->length, $field);
                    foreach ($data['author_section']['points'] as $point) {
                        foreach (array_merge($point['paragraphs_uk'], $point['detail']['paragraphs_uk'] ?? []) as $p) {
                            self::assertStringContainsString($p, $fallback->query('//body')->item(0)->textContent);
                        }
                    }
                }
                $unmarked = clone $block; $unmarkedData = $data; unset($unmarkedData['m43_v1']);
                $unmarked->body = json_encode($unmarkedData, JSON_UNESCAPED_UNICODE);
                self::assertSame(1, $this->xpath(view('theory.partials.content-block', ['block' => $unmarked, 'm43StyleContext' => true])->render())->query('//*[@data-m43-static-fallback]')->length);
                $block->locale = 'pl';
                $html = view('theory.partials.content-block', compact('block'))->render(); $xp = $this->xpath($html);
                self::assertSame(1, $xp->query('//*[@data-m43-static-fallback]')->length);
                self::assertSame(0, $xp->query('//*[@data-m43-author-section]')->length);
                $text = $xp->query('//body')->item(0)->textContent;
                foreach ($master['lessons'][$i]['sections'][$sectionPlan['section_index']]['points'] as $point) {
                    foreach ($point['paragraphs_uk'] as $p) { self::assertStringContainsString($p, $text); }
                    foreach ($point['detail']['paragraphs_uk'] ?? [] as $p) { self::assertStringContainsString($p, $text); }
                    foreach (array_merge($point['examples'], $point['detail']['examples'] ?? []) as $e) {
                        self::assertStringContainsString($e['en'], $text); self::assertStringContainsString($e['uk'], $text);
                    }
                }
            }
        }
    }

    public function test_all_task_controls_aliases_tokens_and_feedback_are_exact_and_inputs_start_empty(): void
    {
        [, $package] = Package::load(); $master = $this->master(); $count = 0;
        $map = $this->read('docs/content/m43-native-mapping.v1.0.0.json');
        foreach ($master['lessons'] as $i => $lesson) {
            $block = $this->block($package['targets'][$i], $map['targets'][$i]['practice_slot']);
            $data = json_decode($block->body, true); self::assertSame($lesson['practice'], $data['author_practice']);
            $xp = $this->xpath(view('theory.partials.content-block', ['block' => $block, 'm43StyleContext' => true])->render());
            self::assertSame(6, $xp->query('//*[@data-m43-ui-case]')->length);
            foreach ($xp->query('//textarea') as $input) { self::assertSame('', trim($input->textContent)); }
            foreach ($lesson['practice'] as $j => $task) {
                self::assertSame($task['id'], $data['cases'][$j]['id']); self::assertSame('all_required_controls', $data['cases'][$j]['scoring']);
                foreach ($task['controls'] as $k => $c) {
                    $count++; $actual = $data['cases'][$j]['controls'][$k];
                    self::assertSame($c['id'], $actual['id']); self::assertTrue($actual['required']);
                    self::assertSame($c['label_uk'], $actual['label']); self::assertSame($c['stimulus_en'] ?? null, $actual['stimulus_en'] ?? null);
                    if ($c['kind'] === 'manual') {
                        self::assertSame($c['canonical_answer'], $actual['answer']); self::assertSame($c['accepted_answers'], $actual['accepted']);
                        self::assertSame($c['tokens'], $actual['tokens']); self::assertSame($c['canonical_answer'], implode(' ', $c['tokens']));
                    } else {
                        self::assertSame($c['options'], $actual['options']); self::assertSame($c['correct_value'], $actual['answer']);
                    }
                }
                $feedback = $xp->query('//*[@data-m43-self-check-answer="'.($j + 1).'"]')->item(0)->textContent;
                foreach ($task['feedback']['paragraphs_uk'] as $p) { self::assertStringContainsString($p, $feedback); }
                foreach ($task['feedback']['answer_examples'] as $e) {
                    self::assertStringContainsString($e['en'], $feedback); self::assertStringContainsString($e['uk'], $feedback);
                }
            }
        }
        self::assertSame(35, $count);
    }
}
