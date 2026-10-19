<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M35ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M35PassiveReportingPackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M35PassiveReportingPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'practice-set'));
        self::assertCount(1, $blocks); return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_exact_sources_identity_category_levels_metadata_and_native_structure(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        self::assertSame(M35ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        $blobs = ['d6108186897211e6b8c705f805e60403e5a0b5b0', 'efa7568192b82787e2091d19cb5983be55c68224', '52163bdf7870623bccba1ed1ad5687cfcd5cf006'];
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame('d2581e3bbcc8a5b89d0a93a454bb010d3c4606eb', $before['base_sha']);
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $restored = $target['after'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes));
            $restored['page']['blocks'] = $original['page']['blocks']; self::assertSame($original, $restored);
            self::assertSame(['passive-voice'], $target['ancestry']);
            self::assertSame(['C1', 'C1', 'C2'][$i], $original['page']['blocks'][0]['level']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true));
            self::assertCount(8, $target['plans']); self::assertCount(9, $target['after']['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
            self::assertCount(8, array_unique(array_column($target['plans'], 'key')));
        }
    }

    public function test_all_eighteen_author_cases_appear_once_and_bank_scope_is_finite(): void
    {
        [$before, $package] = Package::load();
        $banks = ['PolyglotPassiveReportingStructuresC1LessonSeeder', 'PolyglotComplexPassiveAndCausativeC1LessonSeeder', 'PolyglotComplexPassiveImpersonalStyleC2LessonSeeder'];
        $mappings = [
            ['selects' => [2, 4], 'choices' => [1, 3], 'inputs' => [5, 6]],
            ['selects' => [2, 4], 'choices' => [1, 5], 'inputs' => [3, 6]],
            ['selects' => [2, 4], 'choices' => [1, 3], 'inputs' => [5, 6]],
        ];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$before['targets'][$i]['before']['page']['blocks'][1]['body']);
            libxml_clear_errors(); $xp = new DOMXPath($dom);
            $inner = static function ($node): string { $html = ''; foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); } return trim($html); };
            foreach (['prompts' => 'data-self-checks', 'answers' => 'data-self-check-answers'] as $field => $attribute) {
                $items = $xp->query('//ol[@'.$attribute.']/li'); self::assertCount(6, $items);
                self::assertSame(array_map($inner, iterator_to_array($items)), $data['author_self_check'][$field]);
            }
            self::assertSame('', $data['author_self_check']['intro']); // M19 has no paragraph before its cases.
            $indices = [];
            foreach ($mappings[$i] as $kind => $mapping) {
                self::assertCount(2, $data[$kind]); self::assertSame($mapping, array_column($data[$kind], 'source_index'));
                foreach ($data[$kind] as $item) {
                    $case = $item['source_index']; $indices[] = $case;
                    self::assertSame($data['author_self_check']['prompts'][$case - 1], $item['context']);
                    self::assertSame($data['author_self_check']['answers'][$case - 1], $item['author_explanation']);
                    self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame(['C1', 'C1', 'C2'][$i], $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']);
                self::assertNotEmpty($item['m35_token_groups']);
                foreach ($item['m35_token_groups'] as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token));
                    self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
    }

    public function test_multiple_subdecisions_and_explicit_accepted_alternatives_are_not_dropped(): void
    {
        [, $package] = Package::load(); [$r, $c, $i] = array_map($this->practice(...), $package['targets']);
        foreach (['to be empty', 'to have left', 'to be working'] as $part) { self::assertStringContainsString($part, $r['selects'][1]['answer']); }
        foreach (['The halls are believed to be empty.', 'It is believed that the halls are empty.'] as $part) { self::assertStringContainsString($part, $r['inputs'][0]['answer']); }
        self::assertStringContainsString('It is believed the halls are empty.', $r['inputs'][0]['accepted'][1]);
        foreach (['by the coordinator', 'at four on Thursday', 'not been independently confirmed'] as $part) { self::assertStringContainsString($part, $r['inputs'][1]['answer']); }
        self::assertStringContainsString('30 invitations', $c['selects'][0]['accepted'][1]);
        self::assertStringContainsString("I didn't have the screen replaced yesterday.", $c['selects'][1]['accepted'][1]);
        foreach (['Past Simple', 'Past Perfect', 'Himself', 'on/before'] as $part) { self::assertStringContainsString($part, $c['inputs'][0]['answer']); }
        self::assertTrue((bool) array_filter($c['inputs'][0]['m35_token_groups'], fn ($token) => str_contains($token, 'on/before')));
        self::assertStringContainsString('I am going to have my scanner repaired on Thursday.', $c['inputs'][1]['accepted'][1]);
        foreach (['The technicians are reported to have calibrated', 'The sensors are reported to have been calibrated by the technicians'] as $part) { self::assertStringContainsString($part, $i['selects'][0]['answer']); }
        foreach (['немає підтверджених відомостей', 'відомо, що файл не видалили'] as $part) { self::assertStringContainsString($part, $i['selects'][1]['answer']); }
        self::assertStringContainsString('the hall is being repainted by two decorators now.', $i['inputs'][0]['accepted'][1]);
        foreach (['on Sunday', 'independently checked', 'no information about delivery'] as $part) { self::assertStringContainsString($part, $i['inputs'][1]['answer']); }
    }

    public function test_full_native_render_no_js_anchors_tables_and_zero_disclosures(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4500 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 1, 'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect()); $block->setRelation('page', null);
                $data = json_decode($config['body'], true); $projection = Package::presentation($block, $data);
                self::assertNotNull($projection); self::assertSame([], $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($data['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                if ($config['type'] === 'comparison-table') {
                    self::assertStringContainsString('min-width: 1000px', $fragment);
                    foreach ([[250, 280, 210, 260], [250, 370, 300], [250, 380, 300]][$i] as $min) { self::assertStringContainsString('min-width: '.$min.'px', $fragment); }
                    foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { self::assertStringContainsString($cell, $fragment); } }
                }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m35-self-check-no-js', $fragment);
                    self::assertStringContainsString('id="'.$data['m35_v1']['legacy_practice_id'].'"', $fragment);
                    foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $text) { self::assertStringContainsString($text, $fragment); } }
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html);
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xp = new DOMXPath($dom);
            self::assertSame(6, $xp->query('//*[@data-m35-author-prompt]')->length);
            self::assertSame(6, $xp->query('//noscript//*[@data-m35-self-check-no-js]/ol/li')->length);
            $ids = []; foreach ($xp->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
        }
    }

    public function test_semantic_decisions_explicitly_keep_every_candidate_basic_without_word_filter(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                self::assertNotEmpty($candidate['candidate_html']); self::assertGreaterThan(0, $candidate['detail_words']);
            }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); } }
        }
        self::assertStringNotContainsString('detail_words', file_get_contents(base_path('app/Support/M35PassiveReportingPackage.php')));
    }

    public function test_unknown_owner_locale_uuid_order_or_modified_data_falls_back_to_full_content(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][0]; $config = $target['after']['page']['blocks'][1];
        foreach (['owner', 'locale', 'uuid', 'order', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4501, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 2),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 2, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; } elseif ($kind === 'locale') { $block->locale = 'pl'; }
            elseif ($kind === 'uuid') { $block->uuid = 'unknown'; } elseif ($kind === 'order') { $block->sort_order = 99; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data));
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
        }
    }
}
