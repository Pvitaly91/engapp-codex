<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M36ModalsSubjunctivePackage as Package;
use DOMDocument;
use DOMXPath;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M36ModalsSubjunctivePackageTest extends TestCase
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

    public function test_exact_accepted_sources_identities_categories_levels_and_protected_metadata(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        $identities = ['Database\\Seeders\\Page_V3\\ModalVerbs\\ModalPerfectAndDeductionTheorySeeder',
            'Database\\Seeders\\Page_V3\\FormalEnglish\\SubjunctiveAndFormalStructuresTheorySeeder',
            'Database\\Seeders\\Page_V3\\ModalVerbs\\SubtleModalMeaningsTheorySeeder'];
        $blobs = ['06bc76a1db559c4e1c3fd49d9279c82b79227503', 'e38c3d1b80c339098b8404df66377f11a6367907', 'b0ea70c81d37fc750f1a2725e6dc9f130ef65f1d'];
        self::assertSame($identities, array_column($package['targets'], 'identity'));
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame('4644a1729d326b9a967d03c397998506f00d704b', $before['base_sha']);
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes), 'Reconstruct accepted Git source, not only its label.');
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks']; self::assertSame($original, $restored);
            self::assertSame($original['page']['blocks'][0], $after['page']['blocks'][0]);
            $first = $after['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            self::assertSame(['modal-verbs', 'formal-english', 'modal-verbs'][$i], $after['page']['category']['slug']);
            self::assertSame([['modal-verbs'], ['formal-english'], ['modal-verbs']][$i], $target['ancestry']);
            self::assertSame(['C1', 'C1', 'C2'][$i], $after['page']['blocks'][0]['level']);
            self::assertSame('uk', $after['page']['locale']); self::assertSame('theory', $after['type']);
            self::assertSame($after, json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertCount(9, $after['page']['blocks']);
            self::assertSame(range(1, 8), array_column($target['plans'], 'source_section'));
            $types = $i < 2
                ? ['hero', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list']
                : ['hero', 'usage-panels', 'usage-panels', 'usage-panels', 'comparison-table', 'usage-panels', 'usage-panels', 'practice-set', 'summary-list'];
            self::assertSame($types, array_column($after['page']['blocks'], 'type'));
            self::assertNotContains('box', array_column($after['page']['blocks'], 'type')); self::assertCount(8, array_unique(array_column($target['plans'], 'key')));
            foreach (array_slice($after['page']['blocks'], 2) as $block) { self::assertStringStartsWith('m36-', $block['uuid_key']); }
        }
    }

    public function test_all_eighteen_exact_author_cases_have_one_owner_and_two_interactions_of_each_type(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [1, 4], 'choices' => [3, 5], 'inputs' => [2, 6]],
            ['selects' => [1, 5], 'choices' => [3, 4], 'inputs' => [2, 6]],
            ['selects' => [1, 4], 'choices' => [2, 5], 'inputs' => [3, 6]],
        ];
        // Exact primary classes independently established by read-only working-local inventory.
        $banks = ['PolyglotModalPerfectAndDeductionC1LessonSeeder', 'PolyglotSubjunctiveAndFormalStructuresC1LessonSeeder', 'PolyglotSubtleModalMeaningsC2LessonSeeder'];
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
            self::assertSame(['C1', 'C1', 'C2'][$i], $target['after']['page']['blocks'][7]['level']);
            foreach ($data['inputs'] as $item) {
                self::assertTrue($item['punctuation_sensitive']); self::assertNotEmpty($item['m36_token_groups']);
                foreach ($item['m36_token_groups'] as $token) {
                    self::assertGreaterThanOrEqual(1, preg_match_all('/\S+/u', $token)); self::assertLessThanOrEqual(3, preg_match_all('/\S+/u', $token));
                }
            }
        }
        self::assertCount(3, array_unique($bankScopes), 'No cross-owner widget pool or shared generic bank.');
    }

    public function test_multiple_semantic_subparts_and_author_allowed_alternatives_are_preserved(): void
    {
        [, $package] = Package::load(); [$modal, $subjunctive, $subtle] = array_map($this->practice(...), $package['targets']);
        foreach (['відкрита коробка', 'розірвана пломба', 'не незалежний доказ', 'Особу, точний час і причину'] as $text) {
            self::assertStringContainsString($text, $modal['selects'][0]['answer']);
        }
        foreach (['невиконаної дії', 'фактичне прибуття не перевірене', 'Ought to have arrived'] as $text) {
            self::assertStringContainsString($text, $modal['selects'][1]['answer']);
        }
        foreach (['чи сів Раві на автобус, невідомо', 'but he chose to walk', 'невикористану можливість'] as $text) {
            self::assertStringContainsString($text, $modal['choices'][0]['prompt']);
        }
        foreach (['She might have left the note.', 'He must have gone home.', 'They can’t have seen the rehearsal.'] as $text) {
            self::assertStringContainsString($text, $modal['choices'][1]['prompt']);
        }
        self::assertMatchesRegularExpression('/записку\.\s+He/u', $modal['choices'][1]['feedback']['a'], 'Plain feedback must preserve paragraph word boundaries.');
        self::assertSame('Nora must have unlocked the door. Eli can’t have been at the studio at nine.', $modal['inputs'][0]['answer']);
        self::assertContains('Nora must have unlocked the door. Eli cannot have been at the studio at nine.', $modal['inputs'][0]['accepted']);
        self::assertNotContains('Nora must have unlocked the door. Eli couldn’t have been at the studio at nine.', $modal['inputs'][0]['accepted']);
        foreach (['may', 'might', 'could'] as $modalWord) {
            self::assertContains('The studio was dark yesterday. The rehearsal '.$modalWord.' have been cancelled.', $modal['inputs'][1]['accepted']);
        }
        foreach (['assistant not delete the comments.', 'reviewers be informed by the assistant by noon.'] as $text) {
            self::assertStringContainsString($text, $subjunctive['choices'][0]['prompt']);
        }
        self::assertStringContainsString('On Monday, Leo recommended that Emma check the heading before publication.', $subjunctive['choices'][1]['prompt']);
        foreach (['можливий факт', 'Insist означає вимогу', 'наполягання на істинності', 'is доречне'] as $text) {
            self::assertStringContainsString($text, $subjunctive['selects'][1]['answer']);
        }
        self::assertSame('The coordinator recommends that each reviewer read the brief by Friday.', $subjunctive['inputs'][0]['answer']);
        self::assertContains('She labelled both folders so that the reviewers would not confuse them.', $subjunctive['inputs'][1]['accepted']);
        self::assertContains('She labelled both folders so that the reviewers wouldn’t confuse them.', $subjunctive['inputs'][1]['accepted']);
        foreach (['may well explain', 'might as well rehearse', 'Про фактичну репетицію нічого не сказано'] as $text) {
            self::assertStringContainsString($text, $subtle['selects'][0]['answer']);
        }
        foreach (['so I didn’t', 'but I did', 'Виконання невідоме', 'не означає заборону'] as $text) {
            self::assertStringContainsString($text, $subtle['selects'][1]['answer']);
        }
        foreach (['Omar needn’t have booked another room.', 'У а) бракує факту виконання', 'need not have booked'] as $text) {
            self::assertStringContainsString($text, $subtle['choices'][1]['prompt']);
        }
        self::assertMatchesRegularExpression('/потреби\.\s+У а\)/u', $subtle['choices'][1]['prompt']);
        foreach (['You might want to check', 'It may be worth checking', 'You would be wise to check', 'the previous copy listed one name twice.'] as $text) {
            self::assertStringContainsString($text, $subtle['inputs'][0]['answer']);
        }
        self::assertSame('Ira needn’t have printed the extra poster yesterday. Missing clips could well explain today’s delay. It may be worth checking the cupboard.', $subtle['inputs'][1]['answer']);
        self::assertContains('Ira need not have printed the extra poster yesterday. Missing clips could well explain today’s delay. It may be worth checking the cupboard.', $subtle['inputs'][1]['accepted']);
    }

    public function test_full_native_basic_no_js_prompts_keys_anchors_tables_and_zero_disclosures(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $html = '';
            foreach (array_slice($target['after']['page']['blocks'], 1, null, true) as $j => $config) {
                $block = new TextBlock;
                $block->forceFill(['id' => 4600 + $i * 100 + $j, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, $j + 1),
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
                    self::assertStringContainsString('data-m36-self-check-no-js', $fragment);
                    self::assertStringContainsString('id="'.$data['m36_v1']['legacy_practice_id'].'"', $fragment);
                    foreach (['prompts', 'answers'] as $kind) { foreach ($data['author_self_check'][$kind] as $text) { self::assertStringContainsString($text, $fragment); } }
                    self::assertStringContainsString('autocomplete="off"', $fragment);
                }
                $html .= $fragment;
            }
            self::assertStringNotContainsString('data-theory-native-extension', $html); self::assertStringNotContainsString('theory-section-toggle-arrow', $html);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors(); $xpath = new DOMXPath($dom);
            self::assertSame(6, $xpath->query('//*[@data-m36-author-prompt]')->length);
            self::assertSame(6, $xpath->query('//noscript//*[@data-m36-self-check-no-js]/ol/li')->length);
            $ids = []; foreach ($xpath->query('//*[@id]') as $node) { self::assertNotContains($node->getAttribute('id'), $ids); $ids[] = $node->getAttribute('id'); }
        }
    }

    public function test_explicit_semantic_decisions_not_runtime_length_heuristic_keep_full_basic(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $candidate) {
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                self::assertNotEmpty($candidate['candidate_html']); self::assertGreaterThan(0, $candidate['detail_words']);
            }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); self::assertNotEmpty($point['basic']); } }
        }
        self::assertStringNotContainsString('detail_words', file_get_contents(base_path('app/Support/M36ModalsSubjunctivePackage.php')));
    }

    public function test_unknown_owner_locale_uuid_order_type_or_changed_body_falls_back_to_full_content(): void
    {
        [, $package] = Package::load(); $target = $package['targets'][0]; $config = $target['after']['page']['blocks'][1];
        foreach (['owner', 'locale', 'uuid', 'order', 'type', 'body'] as $kind) {
            $block = new TextBlock;
            $block->forceFill(['id' => 4601, 'uuid' => M26DetailPackage::uuid($target['identity'], $config, 2),
                'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => 2, 'type' => $config['type'], 'body' => $config['body']]);
            $data = json_decode($config['body'], true);
            if ($kind === 'owner') { $block->seeder = 'Foreign'; } elseif ($kind === 'locale') { $block->locale = 'pl'; }
            elseif ($kind === 'uuid') { $block->uuid = 'unknown'; } elseif ($kind === 'order') { $block->sort_order = 99; }
            elseif ($kind === 'type') { $block->type = 'unknown-native-type'; }
            else { $data['sections'][0]['description'] .= ' Additional stored material.'; }
            $block->body = Package::json($data); $block->setRelation('tags', collect()); $block->setRelation('page', null);
            self::assertNull(Package::presentation($block, $data));
            if ($kind === 'type') { continue; } // Unknown-type plain fallback is exercised by the existing presentation suite.
            $html = view('theory.partials.content-block', ['block' => $block, 'practiceQuestions' => collect()])->render();
            foreach ($data['sections'] as $point) { self::assertStringContainsString($point['description'], $html); }
        }
    }
}
