<?php

namespace Tests\Feature;

use App\Support\M33AcademicEnglishPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent ordered, punctuation-sensitive evidence against all three accepted M17 Git blobs. */
class M33AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(
            preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** Diagnostics count block-separated whitespace tokens, not standalone inline punctuation. */
    private function diagnosticText(string $html): string
    {
        $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function inner(DOMNode $node): string
    {
        $html = ''; foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); }
        return trim($html);
    }

    private function sourceDom(array $before): DOMDocument
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">'.$before['page']['blocks'][1]['body'], LIBXML_NONET);
        libxml_clear_errors(); return $dom;
    }

    private function coreHtml(array $target): string
    {
        $parts = [];
        foreach (array_slice($target['after']['page']['blocks'], 1) as $block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($block['type'] === 'practice-set') { continue; }
            $parts[] = $data['title'];
            if ($block['type'] === 'usage-panels') {
                foreach ($data['sections'] as $point) { $parts[] = $point['description']; }
            } elseif ($block['type'] === 'comparison-table') {
                $parts[] = $data['intro']; array_push($parts, ...$data['headers']);
                foreach ($data['rows'] as $row) { array_push($parts, ...$row['cells']); }
                $parts[] = $data['outro'];
            } elseif ($block['type'] === 'summary-list') { array_push($parts, ...$data['items']); }
            else { throw new RuntimeException('Unexpected M33 teaching block.'); }
        }
        return implode(' ', $parts);
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
        self::assertCount(1, $blocks); return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    private function tableContext(DOMNode $wrapper, string $direction): string
    {
        $paragraphs = [];
        for ($node = $wrapper->{$direction}; $node !== null; $node = $node->{$direction}) {
            if (in_array($node->nodeName, ['h4', 'section'], true)) { break; }
            if ($node->nodeName === 'p') {
                if ($direction === 'previousSibling') { array_unshift($paragraphs, $this->inner($node)); }
                else { $paragraphs[] = $this->inner($node); }
            }
        }
        return implode('<br><br>', $paragraphs);
    }

    public function test_exact_git_blobs_metadata_all_ordered_words_punctuation_and_links_are_preserved(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        $blobs = ['d5eb376fd2c55a30133704a61a6ecca3b8ea2220', '01f6fa20ed67d6cd01b101061e22f505750867c1',
            '5ec8de96bf6a11c19548ec1a5922905b0fd6b888'];
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame(['hedging-and-cautious-language-basics', 'hedging-and-cautious-language',
            'stance-register-and-evaluation'], array_column($package['targets'], 'slug'));
        foreach ($package['targets'] as $index => $target) {
            $original = $before['targets'][$index]['before'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$index], sha1('blob '.strlen($bytes)."\0".$bytes), 'Reconstruct the actual accepted blob, not just its label.');
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertSame(['academic-english'], $target['ancestry']);
            self::assertSame('uk', $target['after']['page']['locale']); self::assertSame('theory', $target['after']['type']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            $restored = $target['after']; $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored, 'All title/subtitle/slug/category/tag/level metadata remains exact.');
            $first = $target['after']['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            $html = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $original['page']['blocks'][1]['body']);
            self::assertSame($this->plain($html), $this->plain($this->coreHtml($target)),
                'Ordered core preserves every explanation/example/translation/cell and internal punctuation.');
            preg_match_all('~href="([^"]+)"~', $html, $oldLinks);
            preg_match_all('~href="([^"]+)"~', $this->coreHtml($target), $newLinks); self::assertSame($oldLinks[1], $newLinks[1]);
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
        }
    }

    public function test_all_eighteen_exact_prompts_keys_and_contexts_keep_two_per_type_mapping_and_own_banks(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [2, 5], 'choices' => [1, 3], 'inputs' => [4, 6]],
            ['selects' => [2, 4], 'choices' => [1, 3], 'inputs' => [5, 6]],
            ['selects' => [2, 5], 'choices' => [1, 4], 'inputs' => [3, 6]],
        ];
        $banks = ['PolyglotHedgingAndCautiousLanguageBasicsB2LessonSeeder',
            'PolyglotHedgingAndCautiousLanguageC1LessonSeeder', 'PolyglotStanceRegisterAndEvaluationC2LessonSeeder'];
        foreach ($package['targets'] as $index => $target) {
            $xpath = new DOMXPath($this->sourceDom($before['targets'][$index]['before']));
            $prompts = $xpath->query('//ol[@data-self-checks]/li'); $keys = $xpath->query('//ol[@data-self-check-answers]/li');
            self::assertCount(6, $prompts); self::assertCount(6, $keys);
            $data = $this->practice($target); $author = $data['author_self_check'];
            self::assertSame(array_map($this->inner(...), iterator_to_array($prompts)), $author['prompts']);
            self::assertSame(array_map($this->inner(...), iterator_to_array($keys)), $author['answers']);
            self::assertSame(trim($xpath->query('//section[starts-with(@id,"self-check-")]/h4')->item(0)->textContent), $author['section_title']);
            self::assertSame('', $author['intro'], 'Source has no self-check intro; no new author prose.');
            self::assertSame('Ключ і пояснення', $author['title']); $indices = [];
            foreach ($mappings[$index] as $kind => $mapping) {
                self::assertCount(2, $data[$kind]); self::assertSame($mapping, array_column($data[$kind], 'source_index'));
                foreach ($data[$kind] as $item) {
                    $case = $item['source_index']; $indices[] = $case;
                    self::assertSame($author['prompts'][$case - 1], $item['context']);
                    self::assertSame($author['answers'][$case - 1], $item['author_explanation']); self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$index]], $data['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels', implode(' ', $data['linked_practice']['seeder_classes']));
        }
        $b2 = $this->practice($package['targets'][0]);
        self::assertContains('The lamp came on after the battery was replaced. The old battery could explain the problem.', $b2['inputs'][1]['accepted']);
        $c1 = $this->practice($package['targets'][1]);
        foreach (['In this group of eight volunteers', 'The fixed order', 'cannot be ruled out'] as $text) { self::assertStringContainsString($text, $c1['selects'][0]['answer']); }
        self::assertStringContainsString('Обидві конструкції граматичні', $c1['selects'][1]['answer']);
        $c2 = $this->practice($package['targets'][2]);
        self::assertSame(['a', 'b', 'c'], $c2['choices'][1]['accepted']);
        self::assertStringContainsString('критерій другого — придатність для швидкого пошуку', mb_strtolower($c2['choices'][0]['prompt']));
        foreach ([[$c2['selects'][1]['answer'], 'contains no revision date.'], [$c2['selects'][1]['answer'], 'have not been observed.'],
            [$c2['inputs'][1]['answer'], 'only one device'], [$c2['inputs'][1]['answer'], 'remains unknown.']] as [$answer, $text]) {
            self::assertStringContainsString($text, $answer);
        }
    }

    public function test_all_table_headers_cells_context_and_accepted_widths_remain_exact(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']);
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount(1, $tables); $data = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $headers = array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th')));
            $columns = [4, 3, 3][$index]; self::assertCount($columns, $headers); self::assertSame($headers, $data['headers']);
            $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr'); self::assertCount([5, 4, 5][$index], $data['rows']);
            foreach ($rows as $rowIndex => $row) {
                $cells = array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td')));
                self::assertCount($columns, $cells); self::assertSame($cells, $data['rows'][$rowIndex]['cells']);
            }
            $table = $dom->getElementsByTagName('table')->item(0); $wrapper = $table->parentNode;
            self::assertSame($this->tableContext($wrapper, 'previousSibling'), $data['intro']);
            self::assertSame($this->tableContext($wrapper, 'nextSibling'), $data['outro']);
            self::assertSame([880, 800, 780][$index], $data['table_min_width']);
            self::assertSame([[130, null, null, null], [170, null, null], [null, null, null]][$index], $data['column_min_widths']);
            self::assertStringContainsString('min-width:'.$data['table_min_width'].'px;', $table->getAttribute('style'));
        }
    }

    public function test_all_eighteen_semantic_candidates_have_finite_decisions_and_two_own_analyses(): void
    {
        [, $package] = Package::load();
        $expected = [
            [[79,55,6],[158,34,4],[163,39,3],[28,52,4],[17,26,4],[0,49,6]],
            [[81,41,4],[177,23,3],[89,41,4],[0,181,16],[78,47,6],[32,38,3]],
            [[150,20,2],[218,56,5],[136,54,4],[123,22,2],[118,32,3],[25,58,5]],
        ];
        foreach ($package['targets'] as $index => $target) {
            self::assertCount(6, $target['detail_quality_audit']); $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $pointIndex => $point) {
                    self::assertNotEmpty($point['basic']);
                    if ($point['detail'] === '') { continue; }
                    $retained[] = [$plan['source_section'], $pointIndex];
                    self::assertStringContainsString($index === 1 ? 'Seven of the eight volunteers' : 'Using the number of screens', $point['basic']);
                    self::assertStringContainsString($index === 1 ? 'Семеро з восьми добровольців' : 'Якщо критерієм є кількість екранів', $point['basic']);
                    self::assertSame([0, 47, 32][$index], preg_match_all('/\S+/u', $this->plain($point['detail'])));
                    self::assertSame([0, 6, 3][$index], preg_match_all('/[.!?](?:\s|$)/u', $this->plain($point['detail'])));
                }
            }
            self::assertSame([[], [[5, 0]], [[5, 0]]][$index], $retained, 'Explicit 0/1/1 ownership; no global word threshold.');
            foreach ($target['detail_quality_audit'] as $section => $decision) {
                self::assertSame($expected[$index][$section], [$decision['basic_words'], $decision['detail_words'], $decision['detail_sentences']]);
                self::assertSame($index > 0 && $section === 4 ? 'meaningful_detail' : 'visible_basic', $decision['decision']);
                self::assertNotEmpty($decision['reason']);
                self::assertStringContainsString($this->plain($decision['candidate_html']), $this->plain($this->coreHtml($target)));
                self::assertSame(preg_match_all('/\S+/u', $this->diagnosticText($decision['candidate_html'])), $decision['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $this->diagnosticText($decision['candidate_html'])), $decision['detail_sentences']);
            }
        }
        self::assertSame('source-final-list', $package['targets'][1]['detail_quality_audit'][3]['point']);
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($kind) => [$kind], ['known-fact-hedged', 'uncertain-cause-proven', 'seven-of-eight-everyone',
            'fixed-order-removed', 'adverbs-conflated', 'suggest-proposal-sense', 'indicate-prove-force', 'not-necessarily-definitely-not',
            'may-not-categorical', 'significant-statistically', 'source-position-strengthened', 'one-source-consensus',
            'missing-revision-date-changed', 'criterion-removed', 'recommendation-completed', 'personal-attack-accepted',
            'translation-loss', 'neighbour-detail', 'duplicate-id', 'answer-loss']);
    }

    public function test_diagnostic_counter_preserves_real_block_boundaries_without_punctuation_tokens(): void
    {
        self::assertSame('Known. Next fact.', $this->diagnosticText('Known.<br><br><em>Next</em> fact.'));
        self::assertSame(3, preg_match_all('/\S+/u', $this->diagnosticText('Known.<br><br><em>Next</em> fact.')));
        self::assertSame('not supported: reason.', $this->diagnosticText('<strong>not supported</strong>: reason.'));
        self::assertSame(3, preg_match_all('/\S+/u', $this->diagnosticText('<strong>not supported</strong>: reason.')));
        self::assertSame('left cell right cell', $this->diagnosticText('<table><tr><td>left cell</td><td>right cell</td></tr></table>'));
        self::assertSame('not supported : reason.', $this->plain('<strong>not supported</strong>: reason.'),
            'Ordered-corpus normalization stays independent and unchanged; diagnostics cannot relax source fidelity.');
    }

    private function changeFirstBody(array &$package, int $target, string $from, string $to): void
    {
        foreach ($package['targets'][$target]['after']['page']['blocks'] as &$block) {
            if (in_array($block['type'], ['hero', 'practice-set'], true)) { continue; }
            if (!str_contains($block['body'], $from)) { continue; }
            $block['body'] = substr_replace($block['body'], $to, strpos($block['body'], $from), strlen($from));
            json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); return;
        }
        self::fail('Mutation must change a real ordered author fragment: '.$from);
    }

    #[DataProvider('semanticMutations')]
    public function test_all_twenty_requested_semantic_mutations_fail_closed(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'known-fact-hedged' => [0, 'The floor beside the window is wet.', 'The floor beside the window might perhaps be wet.'],
            'uncertain-cause-proven' => [0, 'Rain may be coming in through the window.', 'Rain is definitely coming in through the window.'],
            'seven-of-eight-everyone' => [1, 'Seven of the eight volunteers completed the second search faster.', 'Everyone completed the second search faster.'],
            'fixed-order-removed' => [1, 'Спершу всі користувалися старими підписами, потім — новими.', 'Порядок старих і нових підписів був випадковим.'],
            'adverbs-conflated' => [0, 'Можливість пояснення', 'Типовість без винятків'],
            'suggest-proposal-sense' => [0, 'I suggest checking the battery.', 'The light suggests that checking the battery has been completed.'],
            'indicate-prove-force' => [1, 'The recorded times indicate a faster second search for seven volunteers.', 'The recorded times prove that the labels caused the improvement.'],
            'not-necessarily-definitely-not' => [1, 'A faster second search does not necessarily mean that the labels are better.', 'A faster second search definitely means that the labels are not better.'],
            'may-not-categorical' => [1, 'The same labels may not help first-time users.', 'The same labels do not help first-time users.'],
            'significant-statistically' => [2, 'Без відповідних даних не пиши «статистично значущий».', 'Без відповідних даних пиши «статистично значущий».'],
            'source-position-strengthened' => [2, 'The new index may make this lookup easier.', 'The new index proves that every lookup is faster.'],
            'one-source-consensus' => [2, 'у нас лише одна вигадана навчальна нотатка.', 'усі дослідники одностайно погоджуються.'],
            'missing-revision-date-changed' => [2, 'The checklist contains no revision date.', 'The checklist contains a revision date.'],
            'criterion-removed' => [2, 'for tracking versions because it contains no revision date.', 'for every possible purpose.'],
            'recommendation-completed' => [2, 'The checklist should include a revision date.', 'The checklist has already included a revision date.'],
            'personal-attack-accepted' => [2, 'Це напад на людину та необґрунтоване загальне судження.', 'Це обґрунтована оцінка особистості автора.'],
            'translation-loss' => [0, 'Лоток для паперу порожній.', ''],
        ];
        if (isset($pairs[$kind])) {
            [$target, $from, $to] = $pairs[$kind]; $old = $this->plain($this->coreHtml($package['targets'][$target]));
            $this->changeFirstBody($package, $target, $from, $to);
            self::assertNotSame($old, $this->plain($this->coreHtml($package['targets'][$target])), 'Independent ordered-corpus mutation is substantive, never a no-op.');
        } elseif ($kind === 'answer-loss') {
            foreach ($package['targets'][1]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') { continue; }
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); unset($data['inputs'][0]['answer']);
                $block['body'] = Package::json($data); break;
            }
        } elseif ($kind === 'neighbour-detail') {
            $point = &$package['targets'][1]['plans'][4]['points'][0]; $old = $point['detail'];
            $point['detail'] = $package['targets'][2]['plans'][4]['points'][0]['detail']; self::assertNotSame($old, $point['detail']);
        } else {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m33_v1']['key'] = $package['targets'][0]['plans'][0]['key'];
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
        }
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_internal_punctuation_loss_and_reordered_core_are_rejected(): void
    {
        [$before, $package] = Package::load(); $original = $this->plain($this->coreHtml($package['targets'][1]));
        $this->changeFirstBody($package, 1, 'The new labels may have contributed to this change. However,', 'The new labels may have contributed to this change, However,');
        self::assertNotSame($original, $this->plain($this->coreHtml($package['targets'][1])));
        try { Package::validate($before, $package); self::fail('Internal sentence-boundary mutation must be rejected.'); }
        catch (RuntimeException) { self::assertTrue(true); }
        [$before, $package] = Package::load(); $original = $this->plain($this->coreHtml($package['targets'][0]));
        $data = json_decode($package['targets'][0]['after']['page']['blocks'][1]['body'], true, flags: JSON_THROW_ON_ERROR);
        [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]];
        $package['targets'][0]['after']['page']['blocks'][1]['body'] = Package::json($data);
        self::assertNotSame($original, $this->plain($this->coreHtml($package['targets'][0])), 'A word bag cannot establish author order.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }
}
