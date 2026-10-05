<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Models\PageCategory;
use App\Support\Database\JsonPageSeeder;
use App\Support\M26DetailPackage;
use App\Support\M38ArticlesCollocationsPackage as Package;
use DOMDocument;
use DOMXPath;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M38ArticlesCollocationsPackageTest extends TestCase
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
        $identities = ['Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\AdvancedArticleAndQuantifierNuanceTheorySeeder',
            'Database\\Seeders\\Page_V3\\ArticlesAndQuantifiers\\PrecisionWithArticlesAndDeterminersTheorySeeder',
            'Database\\Seeders\\Page_V3\\VocabularyAndCollocations\\AdvancedCollocationAndLexicalChoiceTheorySeeder'];
        $blobs = ['7c11647100e2c47d7c00c6e8be053822373f573b', 'ab364f6f74540a473d4856376830aaa5fda2ecf5', '0f978eedae3fc7bee2f8f0d449fb3aeb7e131937'];
        self::assertSame($identities, array_column($package['targets'], 'identity'));
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame('8607e2394d347a16c0465a7fb331b467399dab74', $before['base_sha']);
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes), 'Reconstruct the accepted source Git object, not merely its label.');
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks']; self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $after['page']['blocks'][0]);
            $first = $after['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            self::assertSame(['articles-and-quantifiers', 'articles-and-quantifiers', 'vocabulary-and-collocations'][$i], $after['page']['category']['slug']);
            self::assertSame([['articles-and-quantifiers'], ['articles-and-quantifiers'], ['vocabulary-and-collocations']][$i], $target['ancestry']);
            self::assertSame(['C1', 'C2', 'C2'][$i], $after['page']['blocks'][0]['level']);
            self::assertSame('uk', $after['page']['locale']); self::assertSame('theory', $after['type']);
            self::assertSame($after, json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertCount(9, $after['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            $types = array_fill(0, 3, ['hero', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list']);
            self::assertSame($types[$i], array_column($after['page']['blocks'], 'type'));
            self::assertNotContains('box', array_column($after['page']['blocks'], 'type')); self::assertCount(8, array_unique(array_column($target['plans'], 'key')));
            foreach (array_slice($after['page']['blocks'], 2) as $block) { self::assertStringStartsWith('m38-', $block['uuid_key']); }
        }
        self::assertSame('Articles and Quantifiers', $package['targets'][0]['after']['page']['category']['title']);
        self::assertSame('Articles and quantifiers', $package['targets'][1]['after']['page']['category']['title']);
        self::assertSame('Vocabulary and collocations', $package['targets'][2]['after']['page']['category']['title']);
        foreach ($package['targets'] as $target) { self::assertSame('theory', $target['after']['page']['category']['type']); }
    }

    public function test_exact_eighteen_author_cases_one_owner_two_per_kind_and_primary_widget_scope(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
        ];
        // Classes independently proven by the fresh read-only working-local linked-bank inventory.
        $banks = ['PolyglotAdvancedArticleAndQuantifierNuanceC1LessonSeeder', 'PolyglotPrecisionWithArticlesAndDeterminersC2LessonSeeder', 'PolyglotAdvancedCollocationAndLexicalChoiceC2LessonSeeder'];
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
            self::assertSame(['C1', 'C2', 'C2'][$i], $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']); self::assertNotEmpty($item['m38_token_groups']);
                self::assertGreaterThan(1, count($item['m38_token_groups']), 'Even a short phrase must expose at least two usable click groups.');
                foreach ($item['m38_token_groups'] as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
        self::assertCount(3, array_unique($bankScopes), 'Distinct proven primary pools, no cross-owner generic bank.');
    }

    public function test_isolated_exact_root_category_mapping_and_source_casing_survive_without_cross_category_creation(): void
    {
        [, $package] = Package::load();
        $articles = PageCategory::create(['title' => 'Articles and Quantifiers', 'slug' => 'articles-and-quantifiers', 'language' => 'uk', 'type' => 'theory']);
        $collocations = PageCategory::create(['title' => 'Vocabulary and collocations', 'slug' => 'vocabulary-and-collocations', 'language' => 'uk', 'type' => 'theory']);
        $resolver = new class extends JsonPageSeeder
        {
            protected function definitionPath(): string { return ''; }
            public function category(array $payload, string $identity): ?PageCategory { return $this->resolveOrCreateCategory($payload, 'uk', $identity); }
        };
        foreach ($package['targets'] as $i => $target) {
            $payload = $target['after']['page']['category'];
            $category = $resolver->category($payload, $target['identity']);
            self::assertSame($i === 2 ? $collocations->id : $articles->id, $category->id);
            self::assertSame('theory', $category->type); self::assertNull($category->parent_id);
            self::assertSame(['Articles and Quantifiers', 'Articles and quantifiers', 'Vocabulary and collocations'][$i], $payload['title'], 'Accepted definition casing remains intact.');
        }
        self::assertSame(2, PageCategory::count(), 'No cross-category copy or guessed parent is created.');
    }

    public function test_multiple_subparts_and_author_allowed_open_answer_alternatives_survive(): void
    {
        [, $package] = Package::load(); [$articles, $determiners, $collocations] = array_map($this->practice(...), $package['targets']);
        foreach (['three pieces of information', 'some useful research', 'three items of information', 'a useful study'] as $text) { self::assertStringContainsString($text, $articles['selects'][0]['author_explanation']); }
        foreach (['Суперечності немає', 'не гарантує достатність', 'не вказана'] as $text) { self::assertStringContainsString($text, $articles['selects'][1]['answer']); }
        foreach (['Please put a pencil beside the logbook.', 'We need information about yesterday’s delivery.'] as $text) { self::assertStringContainsString($text, $articles['choices'][0]['prompt']); }
        foreach (['A number of labels are missing.', 'The number of missing labels is not known.', 'точного числа'] as $text) { self::assertStringContainsString($text, $articles['choices'][1]['prompt']); }
        self::assertSame('There is not much evidence for this explanation.', $articles['inputs'][0]['answer']);
        self::assertContains('For this explanation, there is not much evidence.', $articles['inputs'][0]['accepted'], 'A faithful fronted phrase retains not much and the same evidential target.');
        self::assertSame('We have a little useful information. It is not enough for a complete catalogue.', $articles['inputs'][1]['answer']);
        self::assertGreaterThan(1, count($articles['inputs'][1]['accepted']), 'The author expressly permits but without inventing enough information.');
        self::assertContains('We have a little useful information, but it is not enough for a complete catalogue.', $articles['inputs'][1]['accepted']);
        foreach (['принесена таця', 'решта чотири', 'текст не встановлює'] as $text) { self::assertStringContainsString($text, $determiners['selects'][0]['answer']); }
        self::assertStringContainsString('We found a cabinet in an empty room. The cabinet was locked, so we needed a key. Furniture needs care.', $determiners['selects'][1]['answer']);
        foreach (['Would you like some biscuits?', 'Are there any lockers available?', 'You may choose any cup from these five.'] as $text) { self::assertStringContainsString($text, $determiners['choices'][0]['prompt']); }
        foreach (['Each of the three drafts has a number.', 'Every one of the three drafts has a number.', 'Both of the final layouts are ready.', 'Either of the two layouts is suitable.', 'Neither of them is signed.', 'формальним'] as $text) { self::assertStringContainsString($text, $determiners['choices'][1]['prompt']); }
        self::assertSame('Not both lamps work.', $determiners['inputs'][0]['answer']);
        self::assertStringContainsString('другу ще не перевірили', $determiners['inputs'][0]['author_explanation']);
        self::assertCount(2, $determiners['inputs'][0]['m38_semantic_checks'], 'The conclusion alone does not answer the missing second-lamp fact and unknown exactly-one subparts.');
        self::assertSame('Neither lamp works. — Жодна з двох ламп не працює — потребувало б знання, що друга теж несправна.', $determiners['inputs'][0]['m38_semantic_checks'][0]['answer']);
        self::assertSame('«Працює рівно одна» також не підтверджено: другу ще не перевірили.', $determiners['inputs'][0]['m38_semantic_checks'][1]['answer']);
        self::assertSame('Six folders were placed in the cabinet. Two of the six folders were checked. Both of the checked folders were dry.', $determiners['inputs'][1]['answer']);
        self::assertContains('Six folders were placed in the cabinet. Two of the six folders were checked. The two checked folders were dry.', $determiners['inputs'][1]['accepted']);
        foreach (['I would like to raise a question about spare-key storage.', 'вирішення проблеми'] as $text) { self::assertStringContainsString($text, $collocations['selects'][0]['answer']); }
        foreach (['The narrow passage poses a challenge to the team moving the scenery.', 'The team faces a challenge in moving the scenery through the narrow passage.'] as $text) { self::assertStringContainsString($text, $collocations['selects'][1]['answer']); }
        foreach (['The committee set / established a precedent.', 'The note draws / makes a distinction between repair and replacement.', 'не гарантує'] as $text) { self::assertStringContainsString($text, $collocations['choices'][0]['prompt']); }
        foreach (['The guide draws a distinction between repair and replacement.', 'It raises two questions about the warranty.', 'Makes a distinction'] as $text) { self::assertStringContainsString($text, $collocations['choices'][1]['prompt']); }
        self::assertSame('a small difference', $collocations['inputs'][0]['answer']);
        self::assertArrayHasKey('m38_semantic_checks', $collocations['inputs'][0], 'Case 5 must retain the separate statistical-significance question, not merely its phrase.');
        self::assertCount(1, $collocations['inputs'][0]['m38_semantic_checks']);
        $check = $collocations['inputs'][0]['m38_semantic_checks'][0];
        self::assertStringContainsString('значущ', json_encode($check, JSON_UNESCAPED_UNICODE));
        self::assertStringContainsString('Без статистичного контексту додавати «статистично» не можна; сама числова різниця також не визначає статистичної значущості.', json_encode($check, JSON_UNESCAPED_UNICODE));
        self::assertSame('The team faces a challenge in using the cramped store. The note raises a question about storage but does not answer it. One dated invoice provides documentary evidence of the purchase, not a complete account of the object’s history.', $collocations['inputs'][1]['answer']);
        self::assertGreaterThan(1, count($collocations['inputs'][1]['accepted']), 'Open editing explicitly allows other clear versions retaining all three semantic models and facts.');
        foreach ($collocations['inputs'][1]['accepted'] as $answer) {
            foreach (['faces a challenge', 'raises a question', 'documentary evidence'] as $model) { self::assertStringContainsString($model, $answer); }
            foreach (['damaged', 'lost', 'has answered', 'approved'] as $unsupported) { self::assertStringNotContainsString($unsupported, $answer); }
        }
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
                    self::assertStringContainsString('data-m38-self-check-no-js', $fragment);
                    self::assertStringContainsString('id="'.$data['m38_v1']['legacy_practice_id'].'"', $fragment);
                    foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $text) { self::assertStringContainsString($text, $fragment); } }
                    self::assertStringContainsString('autocomplete="off"', $fragment);
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html); self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            self::assertSame(6, $xpath->query('//*[@data-m38-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m38-self-check-no-js]/ol/li')->length);
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
        self::assertStringNotContainsString('detail_word_count', file_get_contents(base_path('app/Support/M38ArticlesCollocationsPackage.php')));
    }

    public function test_exact_prior_accepted_disclosure_baseline_including_m37_is_preserved(): void
    {
        $paths = [
            'm27-m11-linking-words.v2.json' => 13,
            'm28-m12-emphasis-inversion.v2.json' => 0,
            'm29-m13-sentence-structure.v2.json' => 0,
            'm30-m14-participle-clauses.v1.json' => 0,
            'm31-m15-conditionals.v1.json' => 2,
            'm32-m16-formal-english.v1.json' => 1,
            'm33-m17-academic-english.v1.json' => 2,
            'm34-m18-argumentation-cohesion.v1.json' => 2,
            'm35-m19-passive-reporting.v1.json' => 0,
            'm36-m20-modals-subjunctive.v1.json' => 0,
            'm37-m21-grammar-structures.v1.json' => 0,
        ];
        $ownerCount = 0;
        foreach ($paths as $path => $expected) {
            $source = json_decode(file_get_contents(base_path('database/content-patches/'.$path)), true, flags: JSON_THROW_ON_ERROR);
            $count = 0;
            foreach ($source['targets'] as $target) {
                $ownerCount++;
                foreach ($target['plans'] as $plan) {
                    foreach ($plan['points'] as $point) { if (trim($point['detail']) !== '') { $count++; } }
                }
            }
            self::assertSame($expected, $count, 'Accepted finite projection: '.$path);
        }
        $m26 = json_decode(file_get_contents(base_path('database/content-patches/m26-ppc-point-details.v1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(56, array_sum(array_map('count', $m26['blocks'])));
        $m26Before = json_decode(file_get_contents(base_path('database/content-patches/m26-ppc-before.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(5, $m26Before['definitions']); self::assertSame(38, $ownerCount + count($m26Before['definitions']));
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
        $root = storage_path('framework/m38-hash-fixture-'.bin2hex(random_bytes(6)));
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
