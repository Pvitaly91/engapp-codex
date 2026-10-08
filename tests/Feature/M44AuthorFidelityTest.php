<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M44AuthoredFutureFormsPackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Expectations come from the independently frozen author master, never generated AFTER. */
class M44AuthorFidelityTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const SHA = '541db4dec57ed372d23832e849f84c0554fb40faf73693c5b7ab2d306fa7b888';

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
        $file = 'docs/content/m44-authored-future-forms.v1.0.0.json';
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
        foreach ($this->read('docs/content/m44-checksums.v1.0.0.json')['files'] as $path => $sha) {
            self::assertSame($sha, hash_file('sha256', base_path($path)), $path);
            $bytes = file_get_contents(base_path($path));
            self::assertStringNotContainsString("\r", $bytes, $path.' is LF-only');
            self::assertFalse(str_starts_with($bytes, "\xEF\xBB\xBF"), $path.' has no BOM');
            self::assertTrue(str_ends_with($bytes, "\n") && !str_ends_with($bytes, "\n\n"), $path.' has one final LF');
        }
        self::assertStringContainsString('original teaching prose and practice', $master['author']);
        self::assertTrue($master['policy']['implementation_authorized_by_user']);
        self::assertFalse($master['policy']['frozen_master_edit']);
        self::assertFalse($master['policy']['personally_sentence_approved_by_user']);
        self::assertSame('d3c5fa811e3e7eb5de2e348a6cd2d0bed3a76ba0', $master['base_commit']);
        $counts = [];
        foreach ($master['lessons'] as $lesson) {
            $points = array_merge(...array_column($lesson['sections'], 'points'));
            $counts[] = [count($lesson['sections']), count($points), count(array_filter($points, fn ($p) => isset($p['detail']))),
                count($lesson['practice']), array_sum(array_map(fn ($t) => count($t['controls']), $lesson['practice']))];
        }
        self::assertSame([[8, 27, 2, 6, 11], [5, 17, 3, 6, 12], [7, 25, 2, 6, 11]], $counts);
    }

    public function test_original_metadata_blocks_uuids_navigation_and_all_other_locales_remain_bound(): void
    {
        [$before, $package] = Package::load(); $master = $this->master();
        $map = $this->read('docs/content/m44-native-mapping.v1.0.0.json');
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
            // Canonical corrections are a finite independently frozen map, never broad URL rewriting.
            $expectedNavigation = json_decode($old['page']['blocks'][$nav]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ($map['targets'][$i]['navigation_changes'] as $change) {
                self::assertSame($nav, $change['slot']); $matches = 0;
                foreach ($expectedNavigation['items'] as &$item) {
                    if ($item['label'] === $change['label'] && $item['url'] === $change['old_url']) {
                        $item['url'] = $change['url']; $matches++;
                    }
                }
                unset($item); self::assertSame(1, $matches, 'Exact old label/URL pair required for correction');
            }
            self::assertSame($expectedNavigation, json_decode($after['page']['blocks'][$nav]['body'], true, flags: JSON_THROW_ON_ERROR));
            self::assertSame(Package::json($expectedNavigation), $after['page']['blocks'][$nav]['body']);
        }
    }

    // Full rendered author-field multiplicity, table content and all old anchors
    // are verified by M44SimplifiedPresentationTest. The follow-up intentionally
    // groups related points, so the original per-point disclosure layout is obsolete.

    public function test_owner_locale_identity_mutations_reject_interactivity_but_keep_complete_static_material(): void
    {
        [, $package] = Package::load(); $master = $this->master();
        $map = $this->read('docs/content/m44-native-mapping.v1.0.0.json');
        self::assertFalse(Package::hasStoredAuthor(['author_section' => ['id' => 'm41-past-simple'], 'm41_v1' => ['role' => 'section']]));
        self::assertFalse(Package::hasStoredAuthor(['author_practice' => [['id' => 'foreign']]]));
        foreach ($package['targets'] as $i => $target) {
            foreach ($map['targets'][$i]['section_order'] as $sectionPlan) {
                $block = $this->block($target, $sectionPlan['slot']); $data = json_decode($block->body, true);
                foreach (['seeder' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'foreign', 'sort_order' => 999, 'type' => 'box'] as $field => $value) {
                    $foreign = clone $block; $foreign->setAttribute($field, $value);
                    self::assertNull(Package::presentation($foreign, $data));
                    $fallback = $this->xpath(view('theory.partials.content-block', ['block' => $foreign, 'm44StyleContext' => true])->render());
                    self::assertSame(1, $fallback->query('//*[@data-m44-static-fallback]')->length, $field);
                    foreach ($data['author_section']['points'] as $point) {
                        foreach (array_merge($point['paragraphs_uk'], $point['detail']['paragraphs_uk'] ?? []) as $p) {
                            self::assertStringContainsString($p, $fallback->query('//body')->item(0)->textContent);
                        }
                    }
                }
                $unmarked = clone $block; $unmarkedData = $data; unset($unmarkedData['m44_v1']);
                $unmarked->body = json_encode($unmarkedData, JSON_UNESCAPED_UNICODE);
                self::assertSame(1, $this->xpath(view('theory.partials.content-block', ['block' => $unmarked, 'm44StyleContext' => true])->render())->query('//*[@data-m44-static-fallback]')->length);
                $block->locale = 'pl';
                $html = view('theory.partials.content-block', compact('block'))->render(); $xp = $this->xpath($html);
                self::assertSame(1, $xp->query('//*[@data-m44-static-fallback]')->length);
                self::assertSame(0, $xp->query('//*[@data-m44-author-section]')->length);
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
        $map = $this->read('docs/content/m44-native-mapping.v1.0.0.json');
        foreach ($master['lessons'] as $i => $lesson) {
            $block = $this->block($package['targets'][$i], $map['targets'][$i]['practice_slot']);
            $data = json_decode($block->body, true); self::assertSame($lesson['practice'], $data['author_practice']);
            $xp = $this->xpath(view('theory.partials.content-block', ['block' => $block, 'm44StyleContext' => true])->render());
            self::assertSame(6, $xp->query('//*[@data-m44-ui-case]')->length);
            foreach ($xp->query('//textarea') as $input) { self::assertSame('', trim($input->textContent)); }
            foreach ($lesson['practice'] as $j => $task) {
                self::assertSame($task['id'], $data['cases'][$j]['id']); self::assertSame('all_required_controls', $data['cases'][$j]['scoring']);
                foreach ($task['controls'] as $k => $c) {
                    $count++; $actual = $data['cases'][$j]['controls'][$k];
                    self::assertSame($c['id'], $actual['id']); self::assertTrue($actual['required']);
                    self::assertSame($c['kind'], $actual['source_kind']);
                    self::assertSame($c['label_uk'], $actual['label']); self::assertSame($c['stimulus_en'] ?? null, $actual['stimulus_en'] ?? null);
                    if (in_array($c['kind'], ['manual', 'tokens'], true)) {
                        self::assertSame($c['canonical_answer'], $actual['answer']); self::assertSame($c['accepted_answers'], $actual['accepted']);
                        self::assertSame($c['tokens'], $actual['tokens']); self::assertSame('manual', $actual['kind']);
                        if ($c['kind'] === 'tokens') {
                            self::assertSame('tokens', $actual['source_kind']);
                            self::assertSame(2, count(array_filter($c['tokens'], fn ($t) => $t === 'to')));
                            self::assertNotSame($c['canonical_answer'], implode(' ', $c['tokens']), 'Author sentence-builder bank is scrambled');
                        } else { self::assertSame($c['canonical_answer'], implode(' ', $c['tokens'])); }
                    } else {
                        self::assertSame($c['options'], $actual['options']); self::assertSame($c['correct_value'], $actual['answer']);
                    }
                }
                $feedback = $xp->query('//*[@data-m44-self-check-answer="'.($j + 1).'"]')->item(0)->textContent;
                foreach ($task['feedback']['paragraphs_uk'] as $p) { self::assertStringContainsString($p, $feedback); }
                foreach ($task['feedback']['answer_examples'] as $e) {
                    self::assertStringContainsString($e['en'], $feedback); self::assertStringContainsString($e['uk'], $feedback);
                }
            }
        }
        self::assertSame(34, $count);
    }

    public function test_m44_does_not_borrow_authority_from_m43_marker_or_owner(): void
    {
        [, $package] = Package::load();
        $map = $this->read('docs/content/m44-native-mapping.v1.0.0.json');
        foreach ($package['targets'] as $i => $target) {
            $slot = $map['targets'][$i]['section_order'][0]['slot'];
            $block = $this->block($target, $slot);
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            $spoof = $data; $spoof['m43_v1'] = $spoof['m44_v1']; unset($spoof['m44_v1']);
            self::assertNull(Package::presentation($block, $spoof));
            $foreign = clone $block;
            $foreign->seeder = 'Database\\Seeders\\Page_V3\\Tenses\\TensesStativeVerbsTheorySeeder';
            self::assertNull(Package::presentation($foreign, $data));
            foreach (['en', 'pl'] as $locale) {
                $localized = clone $block; $localized->locale = $locale;
                self::assertNull(Package::presentation($localized, $data));
                $rows = collect([$localized]);
                self::assertSame($rows, Package::orderBlocks($rows));
                self::assertSame($rows, Package::preserveCourseBlocks($rows));
            }
        }
    }

    public function test_m44_formula_and_bilingual_example_markup_escape_data_and_keep_languages(): void
    {
        $formula = '<script>alert("formula")</script> & will have + V3';
        $xp = $this->xpath(\App\Support\M44NativeHtml::cell(['formula' => $formula]));
        self::assertSame(0, $xp->query('//script')->length);
        self::assertSame($formula, $xp->query('//body')->item(0)->textContent);
        $example = ['en' => '<img src=x onerror="bad"> & test.',
            'uk' => 'Точний <переклад> & текст.', 'note_uk' => 'Окрема "примітка".'];
        $xp = $this->xpath(\App\Support\M44NativeHtml::examples([$example]));
        self::assertSame(0, $xp->query('//img | //script')->length);
        self::assertSame($example['en'], $xp->query('//*[@lang="en"]')->item(0)->textContent);
        $uk = iterator_to_array($xp->query('//*[@lang="uk"]'));
        self::assertSame([$example['uk'], $example['note_uk']], array_map(fn ($node) => $node->textContent, $uk));
        self::assertSame(1, $xp->query('//*[@aria-hidden="true" and @data-theory-ui]')->length);
    }

    public function test_warmed_projection_cache_never_accepts_changed_incoming_author_or_owner_payload(): void
    {
        [$before, $package] = Package::load();
        Package::validate($before, $package); // Warm the request-local immutable projection cache.
        foreach (['body', 'owner', 'mapping-sha', 'target-order'] as $mutation) {
            $changed = $package;
            if ($mutation === 'body') { $changed['targets'][0]['after']['page']['blocks'][1]['body'] = '{}'; }
            elseif ($mutation === 'owner') { $changed['targets'][0]['identity'] = 'ForeignOwner'; }
            elseif ($mutation === 'mapping-sha') { $changed['mapping_sha256'] = hash('sha256', 'foreign'); }
            else { $changed['targets'] = array_reverse($changed['targets']); }
            try {
                Package::validate($before, $changed);
                self::fail('Warm cache accepted '.$mutation);
            } catch (\RuntimeException $error) {
                self::assertStringContainsString('M44', $error->getMessage());
            }
        }
        $changedBefore = $before;
        $changedBefore['targets'][0]['before']['page']['title'] = 'Foreign cached title';
        try {
            Package::validate($changedBefore, $package);
            self::fail('Warm cache accepted changed baseline');
        } catch (\RuntimeException $error) {
            self::assertStringContainsString('M44', $error->getMessage());
        }
        Package::validate($before, $package);
        self::assertTrue(true, 'Original exact package still validates after rejected mutations');
    }
}
