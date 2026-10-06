<?php

namespace Tests\Feature;

use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M39AuthoredRevisionPackage as Package;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M39AuthoredRevisionPackageTest extends TestCase
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

    public function test_exact_accepted_git_blobs_identity_category_title_h1_and_protected_fields(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        $identities = ['Database\\Seeders\\Page_V3\\FormalEnglish\\NominalStyleAndInformationDensityTheorySeeder',
            'Database\\Seeders\\Page_V3\\BasicGrammar\\C1MixedRevisionTheorySeeder',
            'Database\\Seeders\\Page_V3\\BasicGrammar\\C2MixedRevisionTheorySeeder'];
        $blobs = ['cef77f1e1ab2493515c3db39cb51b39b06d81330', 'c6c1c404e3a61763a8f97bb7edf0278779bee85d', '9702e82b33b3aab38e5a7653d412e006622ebb64'];
        self::assertSame('a525a5e03b59904b9fe5987abacf5c038fe5293f', $before['base_sha']);
        self::assertSame($identities, array_column($package['targets'], 'identity'));
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes));
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks']; self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $after['page']['blocks'][0]);
            $first = $after['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            self::assertSame(['formal-english', 'mixed-revision', 'mixed-revision'][$i], $after['page']['category']['slug']);
            self::assertSame([['formal-english'], ['mixed-revision'], ['mixed-revision']][$i], $target['ancestry']);
            self::assertSame(['C2', 'C1', 'C2'][$i], $after['page']['blocks'][0]['level']);
            self::assertSame('uk', $after['page']['locale']); self::assertSame('theory', $after['type']);
            self::assertSame(['Nominal Style and Information Density', 'C1 Mixed Revision', 'C2 Mixed Revision'][$i], $after['page']['title']);
            preg_match('~<strong>(.*?)</strong>~', $after['page']['subtitle_html'], $strong);
            self::assertSame(['Nominal style and information density', 'C1 Mixed Revision', 'C2 mixed revision'][$i], $strong[1]);
            self::assertSame($after, json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertCount(9, $after['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            $types = [
                ['hero', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
                ['hero', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
                ['hero', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
            ];
            self::assertSame($types[$i], array_column($after['page']['blocks'], 'type'));
            self::assertNotContains('box', array_column($after['page']['blocks'], 'type'));
            self::assertCount(8, array_unique(array_column($target['plans'], 'key')));
            foreach (array_slice($after['page']['blocks'], 2) as $block) { self::assertStringStartsWith('m39-', $block['uuid_key']); }
        }
        self::assertSame($before['targets'][0]['before']['page']['category'], $package['targets'][0]['after']['page']['category']);
        self::assertArrayNotHasKey('title', $package['targets'][0]['after']['page']['category'], 'Accepted Nominal category title is absent, not guessed.');
    }

    public function test_all_eighteen_nested_author_cases_and_keys_once_two_per_kind_and_exact_own_widget(): void
    {
        [$before, $package] = Package::load();
        // Fresh SELECT-only relations prove that mixed-revision primary pools
        // are FinalDrill, not a seeder name guessed from the theory slug.
        $banks = ['PolyglotNominalStyleAndInformationDensityC2LessonSeeder', 'PolyglotFinalDrillC1LessonSeeder', 'PolyglotFinalDrillC2LessonSeeder'];
        $bankScopes = [];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$before['targets'][$i]['before']['page']['blocks'][1]['body'], LIBXML_NONET);
            libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $inner = static function ($node): string { $out = ''; foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); } return trim($out); };
            $section = $xpath->query('//section[starts-with(@id,"self-check-")]')->item(0);
            foreach (['prompts' => './ol/li', 'answers' => './details/ol/li'] as $field => $query) {
                $items = $xpath->query($query, $section); self::assertCount(6, $items);
                self::assertSame(array_map($inner, iterator_to_array($items)), $data['author_self_check'][$field]);
            }
            self::assertSame(trim($xpath->query('./h4', $section)->item(0)->textContent), $data['author_self_check']['section_title']);
            self::assertSame($data['author_self_check']['section_title'], $data['title'], 'Keep the original self-check section heading, not a generic replacement.');
            self::assertSame($inner($xpath->query('./p', $section)->item(0)), $data['author_self_check']['intro']);
            self::assertSame('Відповіді та пояснення', $data['author_self_check']['title']);
            $indices = [];
            foreach (['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]] as $kind => $mapping) {
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
            self::assertCount(1, $data['linked_practice']['seeder_classes']);
            $bankScopes[] = $data['linked_practice']['seeder_classes'][0];
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$i]], $data['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels', $bankScopes[$i]);
            self::assertSame(['C2', 'C1', 'C2'][$i], $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']); self::assertGreaterThan(1, count($item['m39_token_groups']));
                foreach ($item['m39_token_groups'] as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
        self::assertCount(3, array_unique($bankScopes), 'Three own primary classes, never the whole mixed pool.');
    }

    public function test_author_subparts_and_explicit_open_variants_preserve_all_facts(): void
    {
        [, $package] = Package::load(); [$nominal, $c1, $c2] = array_map($this->practice(...), $package['targets']);
        self::assertSame('The digitisation of the catalogue by the volunteers began in June. The work is still in progress.', $nominal['inputs'][0]['answer']);
        self::assertGreaterThan(1, count($nominal['inputs'][0]['accepted']));
        foreach ($nominal['inputs'][0]['accepted'] as $answer) {
            foreach (['volunteers', 'began in June', 'still in progress'] as $fact) { self::assertStringContainsString($fact, $answer); }
        }
        self::assertSame('The team has proposed an expansion of the hall. No decision has been made, and the new waiting times have not been measured.', $nominal['inputs'][1]['answer']);
        self::assertGreaterThan(1, count($nominal['inputs'][1]['accepted']), 'Author allows two or three sentences.');
        foreach (['A reassessment', 'committee', 'October', 'may'] as $fact) { self::assertStringContainsString($fact, $nominal['selects'][1]['answer']); }
        foreach (['The curator’s assessment', 'The assessment of the insurance documents by the curator'] as $variant) { self::assertStringContainsString($variant, $nominal['choices'][1]['prompt']); }
        self::assertSame('Leila may have sent the draft yesterday, but we have not checked the mailbox.', $c1['inputs'][0]['answer']);
        self::assertSame('The dates in the catalogue were checked yesterday. The descriptions have not yet been checked. Could you send the final file by Friday?', $c1['inputs'][1]['answer']);
        self::assertGreaterThan(1, count($c1['inputs'][1]['accepted']), 'First two sentences may have other faithful wording; last request remains.');
        foreach ($c1['inputs'][1]['accepted'] as $answer) { self::assertStringContainsString('Could you send the final file by Friday?', $answer); }
        foreach (['Marta needn’t have printed a second timetable.', 'need not have printed', 'факт друку не заданий'] as $part) { self::assertStringContainsString($part, $c2['choices'][0]['prompt']); }
        foreach (['принаймні один', 'точного числа', 'рівно один'] as $part) { self::assertStringContainsString($part, $c2['choices'][1]['prompt']); }
        self::assertSame('The register includes every member. However, two entries have no phone number. These incomplete entries need to be updated.', $c2['inputs'][0]['answer']);
        self::assertGreaterThan(1, count($c2['inputs'][0]['accepted']));
        self::assertTrue((bool) array_filter($c2['inputs'][0]['accepted'], fn ($answer) => str_contains(mb_strtolower($answer, 'UTF-8'), 'the two entries without phone numbers')), 'Author expressly allows this explicit noun phrase.');
        self::assertSame('Anika’s indoor test of the first prototype on Monday was successful. The second prototype has not been tested. According to Anika, the first prototype may also work outdoors, but no outdoor test has taken place.', $c2['inputs'][1]['answer']);
        self::assertGreaterThan(1, count($c2['inputs'][1]['accepted']), 'Author permits three or four sentences.');
        foreach ($c2['inputs'][1]['accepted'] as $answer) {
            foreach (['Anika', 'first prototype', 'Monday', 'may', 'outdoors'] as $fact) { self::assertStringContainsString($fact, $answer); }
            foreach (['both prototypes have been', 'proven reliable', 'guarantees'] as $unsupported) { self::assertStringNotContainsString($unsupported, $answer); }
        }
    }

    public function test_full_native_initial_basic_no_js_keys_anchor_table_scope_and_zero_details(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4900 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 1, 'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect()); $block->setRelation('page', null);
                $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
                $projection = Package::presentation($block, $data); self::assertNotNull($projection); self::assertSame([], $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($data['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                if ($config['type'] === 'comparison-table') {
                    self::assertStringContainsString('min-width: 720px', $fragment); self::assertStringContainsString('min-width: 240px', $fragment);
                    foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { self::assertStringContainsString($cell, $fragment); } }
                }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m39-self-check-no-js', $fragment);
                    self::assertStringContainsString('id="'.$data['m39_v1']['legacy_practice_id'].'"', $fragment);
                    foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $text) { self::assertStringContainsString($text, $fragment); } }
                    self::assertStringContainsString('autocomplete="off"', $fragment);
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            self::assertSame(6, $xpath->query('//*[@data-m39-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m39-self-check-no-js]/ol/li')->length);
            self::assertSame($i === 2 ? 0 : 1, $xpath->query('//table')->length);
            $ids = []; foreach ($xpath->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
        }
    }

    public function test_finite_decisions_not_runtime_length_and_prior_m26_to_m38_counts(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) { self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']); }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); self::assertNotEmpty($point['basic']); } }
        }
        self::assertStringNotContainsString('detail_word_count', file_get_contents(base_path('app/Support/M39AuthoredRevisionPackage.php')));
        $paths = ['m27-m11-linking-words.v2.json' => 13, 'm28-m12-emphasis-inversion.v2.json' => 0,
            'm29-m13-sentence-structure.v2.json' => 0, 'm30-m14-participle-clauses.v1.json' => 0,
            'm31-m15-conditionals.v1.json' => 2, 'm32-m16-formal-english.v1.json' => 1,
            'm33-m17-academic-english.v1.json' => 2, 'm34-m18-argumentation-cohesion.v1.json' => 2,
            'm35-m19-passive-reporting.v1.json' => 0, 'm36-m20-modals-subjunctive.v1.json' => 0,
            'm37-m21-grammar-structures.v1.json' => 0, 'm38-m22-articles-collocations.v1.json' => 0];
        $owners = 0;
        foreach ($paths as $path => $expected) {
            $source = json_decode(file_get_contents(base_path('database/content-patches/'.$path)), true, flags: JSON_THROW_ON_ERROR); $count = 0;
            foreach ($source['targets'] as $target) { $owners++; foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { if (trim($point['detail']) !== '') { $count++; } } } }
            self::assertSame($expected, $count, $path);
        }
        $m26 = json_decode(file_get_contents(base_path('database/content-patches/m26-ppc-point-details.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(56, array_sum(array_map('count', $m26['blocks'])));
        $m26Before = json_decode(file_get_contents(base_path('database/content-patches/m26-ppc-before.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(5, $m26Before['definitions']); self::assertSame(41, $owners + count($m26Before['definitions']));
    }

    public function test_wrong_owner_locale_uuid_order_type_or_edited_body_uses_complete_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][0]; $config = $target['after']['page']['blocks'][1];
        foreach (['owner', 'locale', 'empty-locale', 'uuid', 'order', 'type', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4901, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 2),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 2, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; } elseif ($kind === 'locale') { $block->locale = 'pl'; }
            elseif ($kind === 'empty-locale') { $block->locale = ''; } elseif ($kind === 'uuid') { $block->uuid = 'unknown'; }
            elseif ($kind === 'order') { $block->sort_order = 99; } elseif ($kind === 'type') { $block->type = 'unknown-native-type'; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data)); if ($kind === 'type') { continue; }
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
        }
    }

    public function test_frozen_manifest_tamper_is_rejected_and_snapshot_supports_runtime_without_master_copy(): void
    {
        $root = storage_path('framework/m39-hash-fixture-'.bin2hex(random_bytes(6)));
        $folder = $root.'/database/content-patches'; mkdir($folder, 0777, true);
        $paths = [Package::BEFORE, Package::SOURCE];
        try {
            foreach ($paths as $path) { copy(base_path($path), $root.'/'.$path); }
            self::assertCount(2, Package::load($root), 'The bound exact Git-LF snapshot permits ROOT runtime without copying frozen author files.');
            self::assertFileDoesNotExist($root.'/docs/content/m23-authored-content.v1.json');
            file_put_contents($root.'/'.Package::SOURCE, file_get_contents($root.'/'.Package::SOURCE)." ");
            try { Package::load($root); self::fail('Changed frozen bytes accepted.'); }
            catch (RuntimeException $e) { self::assertStringContainsString('immutable source differs', $e->getMessage()); }
        } finally {
            foreach ($paths as $path) { if (is_file($root.'/'.$path)) { unlink($root.'/'.$path); } }
            rmdir($folder); rmdir(dirname($folder)); rmdir($root);
        }
    }

    public function test_actual_master_and_policy_source_drift_are_rejected_without_repairing_them(): void
    {
        $root = storage_path('framework/m39-actual-source-fixture-'.bin2hex(random_bytes(6)));
        $manifests = $root.'/database/content-patches'; $docs = $root.'/docs/content';
        mkdir($manifests, 0777, true); mkdir($docs, 0777, true);
        $paths = [Package::BEFORE, Package::SOURCE, Package::MASTER_PATH, Package::NOTES_PATH];
        try {
            foreach ($paths as $path) { copy(base_path($path), $root.'/'.$path); }
            self::assertCount(2, Package::load($root));
            foreach ([Package::MASTER_PATH, Package::NOTES_PATH] as $path) {
                $original = file_get_contents($root.'/'.$path);
                file_put_contents($root.'/'.$path, $original.' Changed source.');
                try { Package::load($root); self::fail('Actual frozen source drift accepted.'); }
                catch (RuntimeException $e) { self::assertStringContainsString('actual author', $e->getMessage()); }
                self::assertSame($original.' Changed source.', file_get_contents($root.'/'.$path), 'Validation never repairs or overwrites the source.');
                file_put_contents($root.'/'.$path, $original);
            }
        } finally {
            foreach ($paths as $path) { if (is_file($root.'/'.$path)) { unlink($root.'/'.$path); } }
            rmdir($manifests); rmdir(dirname($manifests)); rmdir($docs); rmdir(dirname($docs)); rmdir($root);
        }
    }
}
