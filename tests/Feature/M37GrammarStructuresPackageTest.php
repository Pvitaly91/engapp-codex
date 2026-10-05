<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Models\PageCategory;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M37GrammarStructuresPackage as Package;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M37GrammarStructuresPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
        self::assertCount(1, $blocks); return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_exact_accepted_blobs_identities_category_shape_ancestry_and_protected_metadata(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        $identities = ['Database\\Seeders\\Page_V3\\VerbPatterns\\AdvancedGerundInfinitivePatternsTheorySeeder',
            'Database\\Seeders\\Page_V3\\RelativeClauses\\ComplexRelativeClausesTheorySeeder',
            'Database\\Seeders\\Page_V3\\BasicGrammar\\WordOrder\\InversionAfterNegativeAdverbialsTheorySeeder'];
        $blobs = ['166cf0821826cf55a90347ccb57db61eba91884a', 'e6ac6befb2eb11ec3f1584cfc4b59054ce1212ca', '72e194f72431210c1c80708b192064809a07468b'];
        self::assertSame($identities, array_column($package['targets'], 'identity'));
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame('f6f1f22e00103118dd1641bbcf29ed4e69a9a33a', $before['base_sha']);
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes), 'Reconstruct the accepted source Git object, not merely its label.');
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks']; self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $after['page']['blocks'][0]);
            $first = $after['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            self::assertSame(['verb-patterns', 'relative-clauses', 'word-order'][$i], $after['page']['category']['slug']);
            self::assertSame([['verb-patterns'], ['relative-clauses'], ['basic-grammar', 'word-order']][$i], $target['ancestry']);
            self::assertSame(['B2', 'C1', 'C1'][$i], $after['page']['blocks'][0]['level']);
            self::assertSame('uk', $after['page']['locale']); self::assertSame('theory', $after['type']);
            self::assertSame($after, json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertCount(9, $after['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            $types = [
                ['hero', 'usage-panels', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
                ['hero', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
                ['hero', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'],
            ];
            self::assertSame($types[$i], array_column($after['page']['blocks'], 'type'));
            self::assertNotContains('box', array_column($after['page']['blocks'], 'type')); self::assertCount(8, array_unique(array_column($target['plans'], 'key')));
            foreach (array_slice($after['page']['blocks'], 2) as $block) { self::assertStringStartsWith('m37-', $block['uuid_key']); }
        }
        self::assertArrayNotHasKey('type', $package['targets'][0]['after']['page']['category'], 'Historical absent Gerund category.type is not mechanically added.');
        self::assertSame('theory', $package['targets'][1]['after']['page']['category']['type']);
        self::assertSame('theory', $package['targets'][2]['after']['page']['category']['type']);
    }

    public function test_exact_eighteen_author_cases_one_owner_two_per_kind_and_primary_widget_scope(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
            ['selects' => [1, 4], 'choices' => [2, 5], 'inputs' => [3, 6]],
        ];
        // Classes independently proven by the fresh read-only working-local linked-bank inventory.
        $banks = ['PolyglotAdvancedGerundInfinitivePatternsB2LessonSeeder', 'PolyglotComplexRelativeClausesC1LessonSeeder', 'PolyglotInversionAfterAdverbialsC1LessonSeeder'];
        $bankScopes = [];
        foreach ($package['targets'] as $i => $target) {
            $data = $this->practice($target); $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$before['targets'][$i]['before']['page']['blocks'][1]['body'], LIBXML_NONET);
            libxml_clear_errors(); $xpath = new DOMXPath($dom);
            $inner = static function ($node): string { $out = ''; foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); } return trim($out); };
            foreach (['prompts' => 'data-self-checks', 'answers' => 'data-self-check-answers'] as $field => $attribute) {
                $items = $xpath->query('//ol[@'.$attribute.']/li'); self::assertCount(6, $items);
                self::assertSame(array_map($inner, iterator_to_array($items)), $data['author_self_check'][$field]);
            }
            self::assertSame($inner($xpath->query('//section[starts-with(@id,"self-check-")]/p')->item(0)), $data['author_self_check']['intro']);
            self::assertSame('Ключ і пояснення', $data['author_self_check']['title']); $indices = [];
            foreach ($mappings[$i] as $kind => $mapping) {
                self::assertCount(2, $data[$kind]); self::assertSame($mapping, array_column($data[$kind], 'source_index'));
                foreach ($data[$kind] as $item) {
                    $case = $item['source_index']; $indices[] = $case;
                    self::assertSame($data['author_self_check']['prompts'][$case - 1], $item['context']);
                    self::assertSame($data['author_self_check']['answers'][$case - 1], $item['author_explanation']); self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertCount(1, $data['linked_practice']['seeder_classes']); $bankScopes[] = $data['linked_practice']['seeder_classes'][0];
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$i]], $data['linked_practice']['seeder_classes']);
            self::assertStringStartsWith('Database\\Seeders\\V3\\Polyglot\\', $bankScopes[$i]); self::assertStringNotContainsString('AllLevels', $bankScopes[$i]);
            self::assertSame(['B2', 'C1', 'C1'][$i], $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']); self::assertNotEmpty($item['m37_token_groups']);
                foreach ($item['m37_token_groups'] as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
        self::assertCount(3, array_unique($bankScopes), 'Distinct proven primary pools, no cross-owner generic bank.');
    }

    public function test_isolated_existing_category_type_and_nested_parent_survive_unchanged_source_shape(): void
    {
        [, $package] = Package::load();
        $parent = PageCategory::create(['title' => 'Basic Grammar', 'slug' => 'basic-grammar', 'language' => 'uk', 'type' => 'theory']);
        $gerund = PageCategory::create(['title' => 'Verb Patterns', 'slug' => 'verb-patterns', 'language' => 'uk', 'type' => 'theory']);
        $relative = PageCategory::create(['title' => 'Relative Clauses', 'slug' => 'relative-clauses', 'language' => 'uk', 'type' => 'theory']);
        $inversion = PageCategory::create(['title' => 'Word Order', 'slug' => 'word-order', 'language' => 'uk', 'type' => 'theory', 'parent_id' => $parent->id]);
        $resolver = new class extends JsonPageSeeder
        {
            protected function definitionPath(): string { return ''; }
            public function category(array $payload, string $identity): ?PageCategory { return $this->resolveOrCreateCategory($payload, 'uk', $identity); }
        };
        foreach ($package['targets'] as $i => $target) {
            $category = $resolver->category($target['after']['page']['category'], $target['identity']);
            self::assertSame([$gerund->id, $relative->id, $inversion->id][$i], $category->id);
            self::assertSame('theory', $category->type);
            self::assertSame($i === 2 ? $parent->id : null, $category->parent_id);
        }
        self::assertSame(4, PageCategory::count(), 'No flattening or extra root word-order created in isolated SQLite.');
    }

    public function test_multiple_subparts_and_author_allowed_open_answer_alternatives_survive(): void
    {
        [, $package] = Package::load(); [$gerund, $relative, $inversion] = array_map($this->practice(...), $package['targets']);
        foreach (['We avoid storing paint near the heater.', 'We decided to move the tins.', 'не доводить'] as $text) { self::assertStringContainsString($text, $gerund['selects'][0]['answer']); }
        foreach (['I look forward to meeting the curator.', 'I plan to meet her on Friday.', 'прийменник', 'маркер інфінітива'] as $text) { self::assertStringContainsString($text, $gerund['selects'][1]['answer']); }
        foreach (['I remember packing the vase yesterday.', 'Remember to label the box before sending it.', 'I remembered to return the trolley yesterday.'] as $text) { self::assertStringContainsString($text, $gerund['choices'][0]['prompt']); }
        foreach (['The students stopped whispering.', 'The students stopped to read the sign.', 'ходьбу'] as $text) { self::assertStringContainsString($text, $gerund['choices'][1]['prompt']); }
        self::assertSame('Mila tried to lift the heavy lid. Mila tried opening the side vent to cool the room.', $gerund['inputs'][0]['answer']);
        self::assertContains('Mila tried to lift the heavy lid. To cool the room, Mila tried opening the side vent.', $gerund['inputs'][0]['accepted']);
        self::assertSame('Yesterday we decided to fix the shelf. We tried to loosen the screw. We look forward to seeing our helper.', $gerund['inputs'][1]['answer']);
        self::assertContains('We decided to fix the shelf yesterday. We tried to loosen the screw. We look forward to seeing our helper.', $gerund['inputs'][1]['accepted']);
        self::assertGreaterThan(1, count($gerund['inputs'][1]['accepted']), 'Author explicitly permits another connected editing with the same facts.');
        foreach ($gerund['inputs'][1]['accepted'] as $answer) {
            self::assertStringNotContainsString('succeeded', $answer); self::assertStringNotContainsString('failed', $answer); self::assertStringNotContainsString('repaired', $answer);
        }
        self::assertStringContainsString('We rented a studio whose windows face the river.', $relative['selects'][0]['answer']);
        foreach (['The conservator to whom we spoke recommended a softer brush.', 'The frame on which the portrait rests is wooden.', 'кінцевим прийменником'] as $text) { self::assertStringContainsString($text, $relative['selects'][1]['answer']); }
        foreach (['The photographs which have white frames were moved.', 'дві фотографії з шести'] as $text) { self::assertStringContainsString($text, $relative['choices'][0]['prompt']); }
        foreach (['У а)', 'У б)', 'У в)', 'The restorer who we believe can repair the frame is away.'] as $text) { self::assertStringContainsString($text, $relative['choices'][1]['prompt']); }
        self::assertSame('They placed the folder beside the lamp. The folder was damaged.', $relative['inputs'][0]['answer']);
        self::assertContains('The folder was damaged. They placed it beside the lamp.', $relative['inputs'][0]['accepted']);
        self::assertGreaterThan(1, count($relative['inputs'][0]['accepted']), 'A clear two-sentence author-permitted alternate is not silently rejected.');
        self::assertSame('The workshop whose three presses need servicing has hired Iva, who maintains them.', $relative['inputs'][1]['answer']);
        foreach (['Rarely have the samples been stored outside the cold room.', 'Rarely'] as $text) { self::assertStringContainsString($text, $inversion['selects'][0]['answer']); }
        foreach (['Only the archivist could open the vault.', 'Only after the check was complete could the vault be opened.', 'можливість'] as $text) { self::assertStringContainsString($text, $inversion['selects'][1]['answer']); }
        foreach (['Hardly had the dispatcher finished checking the list when the alarm sounded.', 'Причинність не встановлена', 'три дні'] as $text) { self::assertStringContainsString($text, $inversion['choices'][0]['prompt']); }
        foreach (['Under no circumstances may visitors remove the seals.', 'At no time during the trial was the access code shared.'] as $text) { self::assertStringContainsString($text, $inversion['choices'][1]['prompt']); }
        self::assertSame('Only after the editor had confirmed consent was the material released.', $inversion['inputs'][0]['answer']);
        self::assertSame('No sooner had the display been installed than the visitors arrived. Not only the architect but also the caretaker welcomed them. The visitors rarely speak loudly in this hall.', $inversion['inputs'][1]['answer']);
    }

    public function test_full_initial_native_basic_no_js_prompts_keys_anchors_tables_and_zero_details(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4700 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
                    'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $j + 1, 'type' => $config['type'], 'body' => $config['body'], 'level' => $config['level'] ?? null]);
                $block->setRelation('tags', collect()); $block->setRelation('page', null);
                $data = json_decode($config['body'], true); $projection = Package::presentation($block, $data);
                self::assertNotNull($projection); self::assertSame([], $projection['points']);
                $fragment = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
                foreach ($data['sections'] ?? [] as $point) { self::assertStringContainsString($point['description'], $fragment); }
                if ($config['type'] === 'comparison-table') {
                    self::assertStringContainsString('min-width: 1000px', $fragment); self::assertStringContainsString('min-width: 250px', $fragment);
                    foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { self::assertStringContainsString($cell, $fragment); } }
                    self::assertStringContainsString($data['intro'], $fragment); self::assertStringContainsString($data['outro'], $fragment);
                }
                if ($config['type'] === 'practice-set') {
                    self::assertStringContainsString('data-m37-self-check-no-js', $fragment);
                    self::assertStringContainsString('id="'.$data['m37_v1']['legacy_practice_id'].'"', $fragment);
                    foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $text) { self::assertStringContainsString($text, $fragment); } }
                    self::assertStringContainsString('autocomplete="off"', $fragment);
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html); self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            self::assertSame(6, $xpath->query('//*[@data-m37-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m37-self-check-no-js]/ol/li')->length);
            $ids = []; foreach ($xpath->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
        }
    }

    public function test_explicit_semantic_decisions_keep_full_basic_without_runtime_word_threshold(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                self::assertNotEmpty($candidate['candidate_html']); self::assertGreaterThan(0, $candidate['detail_word_count']);
            }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); self::assertNotEmpty($point['basic']); } }
        }
        self::assertStringNotContainsString('detail_word_count', file_get_contents(base_path('app/Support/M37GrammarStructuresPackage.php')));
    }

    public function test_unknown_owner_locale_uuid_order_type_or_changed_body_retains_full_fallback(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][0]; $config = $target['after']['page']['blocks'][1];
        foreach (['owner', 'locale', 'empty-locale', 'uuid', 'order', 'type', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4701, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 2),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 2, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; } elseif ($kind === 'locale') { $block->locale = 'pl'; }
            elseif ($kind === 'empty-locale') { $block->locale = ''; } elseif ($kind === 'uuid') { $block->uuid = 'unknown'; }
            elseif ($kind === 'order') { $block->sort_order = 99; } elseif ($kind === 'type') { $block->type = 'unknown-native-type'; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data));
            if ($kind === 'type') { continue; } // Existing common presenter covers unknown-type plain fallback.
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
        }
    }

    public function test_tampered_frozen_source_is_rejected_without_weakening_file_hashes(): void
    {
        $root = storage_path('framework/m37-hash-fixture-'.bin2hex(random_bytes(6)));
        $folder = $root.'/database/content-patches'; mkdir($folder, 0777, true);
        $paths = [Package::BEFORE, Package::SOURCE];
        try {
            foreach ($paths as $path) { copy(base_path($path), $root.'/'.$path); }
            self::assertCount(2, Package::load($root));
            file_put_contents($root.'/'.Package::SOURCE, file_get_contents($root.'/'.Package::SOURCE)." ");
            try { Package::load($root); self::fail('Changed immutable bytes accepted.'); }
            catch (RuntimeException $e) { self::assertStringContainsString('immutable source differs', $e->getMessage()); }
        } finally {
            foreach ($paths as $path) { if (is_file($root.'/'.$path)) { unlink($root.'/'.$path); } }
            rmdir($folder); rmdir(dirname($folder)); rmdir($root);
        }
    }
}
