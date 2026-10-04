<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M34ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M34ArgumentationCohesionPackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M34ArgumentationCohesionPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'practice-set'));
        self::assertCount(1, $blocks);
        return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_exact_three_distinct_categories_c2_metadata_and_eight_native_sections(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        self::assertSame(M34ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        self::assertSame(['a2ab4114f979ddc6a81e9b79d3b11836a7f9f7de', '93801c32885c33c451918022997081dabf2a094d',
            '57f0c9536cb92fae21bafd39dd17cb8b8b2e82e4'], array_column($before['targets'], 'source_git_blob'));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame([['academic-english'], ['clauses-and-linking-words'], ['formal-english']][$i], $target['ancestry']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true));
            $original = $before['targets'][$i]['before']; $restored = $target['after'];
            $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            self::assertSame('C2', $original['page']['blocks'][0]['level']);
            self::assertCount(8, $target['plans']); self::assertCount(9, $target['after']['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
            self::assertSame($original['page']['blocks'][1]['heading'], $target['after']['page']['blocks'][1]['heading']);
        }
    }

    public function test_all_eighteen_literal_prompts_keys_and_unique_case_mappings_and_own_banks(): void
    {
        [$before, $package] = Package::load();
        $banks = ['PolyglotArgumentationAndAcademicToneC2LessonSeeder', 'PolyglotDiscourseMarkersAndCohesionC2LessonSeeder',
            'PolyglotParaphraseAndReformulationC2LessonSeeder'];
        $mappings = [
            ['selects' => [3, 4], 'choices' => [1, 2], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [4, 6], 'inputs' => [3, 5]],
            ['selects' => [1, 5], 'choices' => [3, 4], 'inputs' => [2, 6]],
        ];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $source = $before['targets'][$i]['before']['page']['blocks'][1]['body'];
            $author = $data['author_self_check'];
            self::assertSame('Ключ і пояснення', $author['title']); self::assertNotEmpty($author['intro']);
            preg_match('~<section id="([^"]+)"><h4>(.*?)</h4><p>(.*?)</p>~s', $source, $heading);
            self::assertSame($heading[1], $data['m34_v1']['legacy_practice_id']);
            self::assertSame($heading[2], $author['section_title']); self::assertSame($heading[3], $author['intro']);
            foreach (['prompts' => 'data-self-checks', 'answers' => 'data-self-check-answers'] as $field => $attribute) {
                preg_match('~<ol '.$attribute.'>(.*?)</ol>~s', $source, $list);
                preg_match_all('~<li>(.*?)</li>~s', $list[1], $items);
                self::assertSame($items[1], $author[$field]); self::assertCount(6, $author[$field]);
            }
            $indices = [];
            foreach ($mappings[$i] as $kind => $mapping) {
                self::assertCount(2, $data[$kind]); self::assertSame($mapping, array_column($data[$kind], 'source_index'));
                foreach ($data[$kind] as $item) {
                    self::assertNotEmpty($item['answer']);
                    self::assertSame($author['prompts'][$item['source_index'] - 1], $item['context']);
                    self::assertSame($author['answers'][$item['source_index'] - 1], $item['author_explanation']);
                    $indices[] = $item['source_index'];
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$i]], $data['linked_practice']['seeder_classes']);
            self::assertSame('C2', $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']);
                foreach (explode(' / ', $item['before']) as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token));
                    self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
    }

    public function test_finite_approved_marker_and_open_formulation_alternatives_are_explicit(): void
    {
        [, $package] = Package::load();
        $a = $this->practice($package['targets'][0]); $d = $this->practice($package['targets'][1]); $p = $this->practice($package['targets'][2]);
        self::assertCount(2, $a['selects'][0]['accepted']); self::assertCount(2, $a['selects'][1]['accepted']);
        self::assertStringContainsString('Attendance increased on Tuesday.', $a['inputs'][0]['accepted'][1]);
        self::assertSame(3, preg_match_all('/[.!?](?:\s|$)/u', $a['inputs'][1]['accepted'][1]));
        self::assertCount(2, $d['selects'][0]['accepted']); self::assertCount(5, $d['selects'][1]['accepted']);
        self::assertStringContainsString('Nevertheless', $d['selects'][0]['accepted'][1]);
        foreach (['Moreover', 'Furthermore', 'In addition'] as $n => $marker) {
            self::assertStringContainsString($marker, $d['selects'][1]['accepted'][$n]);
        }
        self::assertSame('The handbook includes a glossary. It provides an index of names.', $d['selects'][1]['accepted'][3]);
        self::assertSame(['a', 'b'], $d['choices'][1]['accepted']);
        self::assertSame(['The archive is small; however, it contains every issue from 2020.',
            'The archive is small. However, it contains every issue from 2020.'], $d['inputs'][0]['accepted']);
        self::assertSame(['a', 'b'], $p['choices'][0]['accepted']);
        self::assertCount(3, $p['selects'][1]['accepted']);
        foreach ([$a['inputs'][1], $d['inputs'][1], $p['inputs'][0], $p['inputs'][1]] as $open) {
            self::assertCount(2, $open['accepted']); self::assertContains($open['answer'], $open['accepted']);
            self::assertNotEmpty($open['author_explanation']);
        }
    }

    public function test_native_render_full_basic_details_literal_no_js_and_table_widths(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4400 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 1, 'type' => $config['type'],
                    'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect()); $block->setRelation('page', null);
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                $projection = Package::presentation($block, $data); self::assertNotNull($projection);
                $details = array_filter($target['plans'][$j - 1]['points'], fn ($point) => $point['detail'] !== '');
                self::assertCount(count($details), $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($projection['data']['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                foreach ($details as $point) { self::assertStringContainsString($point['detail'], $fragment); }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m34-self-check-no-js', $fragment);
                    self::assertStringContainsString($data['author_self_check']['intro'], $fragment);
                    self::assertStringContainsString('id="'.$data['m34_v1']['legacy_practice_id'].'"', $fragment);
                    foreach ($data['author_self_check']['answers'] as $answer) { self::assertStringContainsString($answer, $fragment); }
                    foreach ($data['author_self_check']['prompts'] as $prompt) { self::assertStringContainsString($prompt, $fragment); }
                }
                if ($config['type'] === 'comparison-table') {
                    self::assertStringContainsString('min-width: 900px', $fragment);
                    foreach ([[190, 270, 330], [240, 250, 300], [210, 270, 300]][$i] as $min) {
                        self::assertStringContainsString('min-width: '.$min.'px', $fragment);
                    }
                }
                $html .= $fragment;
            }
            self::assertSame([1, 1, 0][$i], substr_count($html, 'data-theory-native-extension'));
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                self::assertNotContains($element->getAttribute('id'), $ids); $ids[] = $element->getAttribute('id');
            }
            self::assertSame(6, $xpath->query('//*[@data-m34-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m34-self-check-no-js]/ol/li')->length);
        }
    }

    public function test_only_two_coherent_analyses_have_details_and_four_short_candidates_stay_basic(): void
    {
        [, $package] = Package::load(); $owners = []; $short = [];
        foreach ($package['targets'] as $i => $target) {
            $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $point => $fragment) {
                    if ($fragment['detail'] === '') { continue; }
                    $retained[] = [$plan['key'], $plan['source_section'], $point];
                    self::assertStringContainsString($i === 0 ? 'For comparing two club timetables' : 'The revised timetable shows weekday classes', $fragment['basic']);
                    self::assertStringContainsString($i === 0 ? 'Для порівняння розкладів двох гуртків' : 'В оновленому розкладі', $fragment['basic']);
                    if ($i === 0) { self::assertStringContainsString('Вигадана навчальна ситуація', $fragment['basic']); }
                }
            }
            $owners[] = $retained; self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertNotEmpty($candidate['reason']); self::assertNotEmpty($candidate['candidate_html']);
                if ($candidate['detail_words'] >= 30) { continue; }
                $short[] = [$i, $candidate['source_section'], $candidate['detail_words'], $candidate['detail_sentences']];
                self::assertSame('visible_basic', $candidate['decision']);
            }
        }
        self::assertSame([[['m34-argumentation-section-2', 2, 0]], [['m34-discourse-section-5', 5, 0]], []], $owners);
        self::assertSame([[0, 6, 25, 3], [1, 2, 28, 2], [2, 1, 22, 3], [2, 2, 27, 2]], $short);
        self::assertSame('source-analysis-list', $package['targets'][0]['detail_quality_audit'][1]['point']);
        self::assertSame('source-paragraph-analysis', $package['targets'][1]['detail_quality_audit'][4]['point']);
        self::assertSame('source-final-list', $package['targets'][2]['detail_quality_audit'][3]['point']);
        self::assertCount(3, $package['targets'][1]['plans'][4]['points']);
    }

    public function test_foreign_owner_uuid_order_locale_changed_stored_text_have_full_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][1]; $config = $target['after']['page']['blocks'][5];
        foreach (['owner', 'uuid', 'order', 'locale', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4401, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 6),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 6, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; }
            elseif ($kind === 'uuid') { $block->uuid = 'foreign'; }
            elseif ($kind === 'order') { $block->sort_order = 99; }
            elseif ($kind === 'locale') { $block->locale = 'pl'; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data));
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
            self::assertStringNotContainsString('data-theory-native-extension', $html);
        }
    }
}
