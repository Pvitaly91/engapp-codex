<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Services\M33ContentPatch;
use App\Support\M26DetailPackage;
use App\Support\M33AcademicEnglishPackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M33AcademicEnglishPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
        self::assertCount(1, $blocks);
        return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_exact_source_owners_metadata_hero_and_eight_native_sections_are_preserved(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        self::assertSame(M33ContentPatch::NAMES, array_column($package['targets'], 'identity'));
        foreach ($package['targets'] as $i => $target) {
            self::assertSame(['academic-english'], $target['ancestry']);
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true));
            $original = $before['targets'][$i]['before'];
            $restored = $target['after']; $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            self::assertCount(8, $target['plans']); self::assertCount(9, $target['after']['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
        }
    }

    public function test_exact_six_prompts_keys_and_two_per_type_have_unique_case_and_actual_bank_ownership(): void
    {
        [$before, $package] = Package::load();
        $banks = ['PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder',
            'PolyglotHedgingAndCautiousLanguageC1LessonSeeder', 'PolyglotStanceRegisterAndEvaluationC2LessonSeeder'];
        $mappings = [
            ['selects' => [2, 5], 'choices' => [1, 3], 'inputs' => [4, 6]],
            ['selects' => [2, 4], 'choices' => [1, 3], 'inputs' => [5, 6]],
            ['selects' => [2, 5], 'choices' => [1, 4], 'inputs' => [3, 6]],
        ];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $source = $before['targets'][$i]['before']['page']['blocks'][1]['body'];
            $author = $data['author_self_check'];
            self::assertSame('Ключ і пояснення', $author['title']); self::assertSame('', $author['intro']);
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
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']);
                foreach (explode(' / ', $item['before']) as $token) {
                    self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token), 'Finite groups contain one to three words.');
                }
            }
        }
        $b2 = $this->practice($package['targets'][0]);
        self::assertSame('The printer may need paper. The printer seems to need paper.', $b2['selects'][0]['answer']);
        self::assertTrue($b2['selects'][0]['punctuation_sensitive']);
        self::assertSame('The lamp came on after the battery was replaced. The old battery may have caused the problem.', $b2['inputs'][1]['answer']);
        self::assertContains('The lamp came on after the battery was replaced. The old battery could explain the problem.', $b2['inputs'][1]['accepted']);
        $c1 = $this->practice($package['targets'][1]);
        self::assertStringContainsString('The fixed order means that practice cannot be ruled out', $c1['selects'][0]['answer']);
        self::assertStringContainsString('Початкове may not було прогнозом, не забороною.', $c1['selects'][1]['answer']);
        self::assertStringContainsString('fewer visitors came that day.', $c1['inputs'][0]['answer']);
        self::assertStringContainsString('everyone used them second, so practice may also explain', $c1['inputs'][1]['answer']);
        $c2 = $this->practice($package['targets'][2]);
        self::assertSame(['a', 'b', 'c', 'd'], $c2['choices'][1]['options']);
        self::assertSame(['a', 'b', 'c'], $c2['choices'][1]['accepted']);
        self::assertStringContainsString("users' choices have not been observed.", $c2['selects'][1]['answer']);
        self::assertStringContainsString('checked on only one device', $c2['inputs'][1]['answer']);
    }

    public function test_native_render_is_complete_no_js_readable_and_has_unique_ids_and_exact_table_widths(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4000 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
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
                    self::assertStringContainsString('data-m33-self-check-no-js', $fragment);
                    foreach ($data['author_self_check']['answers'] as $answer) { self::assertStringContainsString($answer, $fragment); }
                    foreach ($data['author_self_check']['prompts'] as $prompt) { self::assertStringContainsString($prompt, $fragment); }
                }
                if ($config['type'] === 'comparison-table') {
                    self::assertStringContainsString('min-width: '.[880, 800, 780][$i].'px', $fragment);
                    if ($i < 2) { self::assertStringContainsString('min-width: '.[130, 170][$i].'px', $fragment); }
                }
                $html .= $fragment;
            }
            self::assertSame([0, 1, 1][$i], substr_count($html, 'data-theory-native-extension'));
            self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $ids = [];
            foreach ($xpath->query('//*[@id]') as $element) {
                self::assertNotContains($element->getAttribute('id'), $ids); $ids[] = $element->getAttribute('id');
            }
            self::assertSame(6, $xpath->query('//*[@data-m33-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m33-self-check-no-js]/ol/li')->length);
        }
    }

    public function test_explicit_detail_ownership_keeps_full_paragraph_and_translation_basic(): void
    {
        [, $package] = Package::load(); $owners = []; $short = 0;
        foreach ($package['targets'] as $i => $target) {
            $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $point => $fragment) {
                    if ($fragment['detail'] === '') { continue; }
                    $retained[] = [$plan['key'], $plan['source_section'], $point];
                    self::assertStringContainsString($i === 1 ? 'Seven of the eight volunteers' : 'Using the number of screens', $fragment['basic']);
                    self::assertStringContainsString($i === 1 ? 'Семеро з восьми добровольців' : 'Якщо критерієм є кількість екранів', $fragment['basic']);
                }
            }
            $owners[] = $retained;
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertNotEmpty($candidate['reason']); self::assertNotEmpty($candidate['candidate_html']);
                if ($candidate['detail_words'] >= 30) { continue; }
                $short++; self::assertSame('visible_basic', $candidate['decision']);
            }
        }
        self::assertSame([[], [['m33-c1-section-5', 5, 0]], [['m33-c2-section-5', 5, 0]]], $owners);
        self::assertSame(4, $short);
        self::assertSame('source-final-list', $package['targets'][1]['detail_quality_audit'][3]['point']);
    }

    public function test_wrong_owner_uuid_order_locale_or_stored_text_keeps_complete_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][1]; $config = $target['after']['page']['blocks'][5];
        foreach (['owner', 'uuid', 'order', 'locale', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4001, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 6),
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
