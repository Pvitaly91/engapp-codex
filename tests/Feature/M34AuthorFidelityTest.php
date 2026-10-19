<?php

namespace Tests\Feature;

use App\Support\M34ArgumentationCohesionPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent full, ordered author fidelity against the three accepted M18 Git blobs. */
class M34AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(
            preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

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
            else { throw new RuntimeException('Unexpected M34 teaching block.'); }
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

    public function test_exact_git_blobs_three_categories_metadata_ordered_words_punctuation_and_links_are_preserved(): void
    {
        [$before, $package] = Package::load(); Package::validate($before, $package);
        $blobs = ['a2ab4114f979ddc6a81e9b79d3b11836a7f9f7de', '93801c32885c33c451918022997081dabf2a094d',
            '57f0c9536cb92fae21bafd39dd17cb8b8b2e82e4'];
        $categories = ['academic-english', 'clauses-and-linking-words', 'formal-english'];
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame(['argumentation-and-academic-tone', 'discourse-markers-and-cohesion',
            'paraphrase-and-reformulation'], array_column($package['targets'], 'slug'));
        foreach ($package['targets'] as $index => $target) {
            $original = $before['targets'][$index]['before'];
            $bytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$index], sha1('blob '.strlen($bytes)."\0".$bytes), 'Reconstruct actual accepted bytes, not merely the label.');
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']); self::assertSame([$categories[$index]], $target['ancestry']);
            self::assertSame($categories[$index], $target['after']['page']['category']['slug']);
            self::assertSame('uk', $target['after']['page']['locale']); self::assertSame('theory', $target['after']['type']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            self::assertSame('C2', json_decode($target['after']['page']['blocks'][0]['body'], true)['level']);
            $restored = $target['after']; $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored, 'All title/subtitle/slug/category/tag/level metadata remains exact.');
            $first = $target['after']['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']); self::assertSame($oldFirst, $first);
            $html = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $original['page']['blocks'][1]['body']);
            self::assertSame($this->plain($html), $this->plain($this->coreHtml($target)),
                'Full ordered core preserves every explanation/example/translation/cell and internal punctuation.');
            preg_match_all('~href="([^"]+)"~', $html, $oldLinks);
            preg_match_all('~href="([^"]+)"~', $this->coreHtml($target), $newLinks); self::assertSame($oldLinks[1], $newLinks[1]);
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
        }
    }

    public function test_all_eighteen_exact_prompts_keys_intros_contexts_and_own_c2_banks_have_two_per_type(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [3, 4], 'choices' => [1, 2], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [4, 6], 'inputs' => [3, 5]],
            ['selects' => [1, 5], 'choices' => [3, 4], 'inputs' => [2, 6]],
        ];
        $banks = ['PolyglotArgumentationAndAcademicToneC2LessonSeeder',
            'PolyglotDiscourseMarkersAndCohesionC2LessonSeeder', 'PolyglotParaphraseAndReformulationC2LessonSeeder'];
        foreach ($package['targets'] as $index => $target) {
            $xpath = new DOMXPath($this->sourceDom($before['targets'][$index]['before']));
            $prompts = $xpath->query('//ol[@data-self-checks]/li'); $keys = $xpath->query('//ol[@data-self-check-answers]/li');
            self::assertCount(6, $prompts); self::assertCount(6, $keys);
            $data = $this->practice($target); $author = $data['author_self_check'];
            self::assertSame(array_map($this->inner(...), iterator_to_array($prompts)), $author['prompts']);
            self::assertSame(array_map($this->inner(...), iterator_to_array($keys)), $author['answers']);
            self::assertSame(trim($xpath->query('//section[starts-with(@id,"self-check-")]/h4')->item(0)->textContent), $author['section_title']);
            self::assertSame($this->inner($xpath->query('//section[starts-with(@id,"self-check-")]/p')->item(0)), $author['intro']);
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
        $a = $this->practice($package['targets'][0]);
        self::assertStringContainsString('речення 4 обмежує висновок', $a['choices'][0]['prompt']);
        self::assertStringContainsString('every phone виходить за дані', $a['choices'][1]['prompt']);
        foreach (['Guide A has no index', 'every definition', 'page of a particular definition', 'search times were not measured'] as $text) {
            self::assertStringContainsString($text, $a['inputs'][1]['answer']);
        }
        $d = $this->practice($package['targets'][1]);
        self::assertCount(5, $d['selects'][1]['accepted']); self::assertSame(['a', 'b'], $d['choices'][1]['accepted']);
        self::assertSame(['The archive is small; however, it contains every issue from 2020.',
            'The archive is small. However, it contains every issue from 2020.'], $d['inputs'][0]['accepted']);
        $p = $this->practice($package['targets'][2]);
        self::assertSame(['a', 'b'], $p['choices'][0]['accepted']);
        foreach (['Not all', 'no заперечує', 'At least four', 'exactly four', 'Some may → all will'] as $text) {
            self::assertStringContainsString($text, $p['choices'][1]['prompt']);
        }
        foreach (['two volunteers', 'on Monday', 'if the keys arrive by noon', 'does not identify those volunteers'] as $text) {
            self::assertStringContainsString($text, $p['inputs'][0]['answer']);
        }
        foreach ($p['inputs'][1]['accepted'] as $answer) {
            self::assertStringContainsString('museum tested the audio guide', $answer);
            self::assertStringContainsString('no phone-test result', $answer);
            self::assertStringContainsString('successful or unsuccessful phone operation', $answer);
        }
    }

    public function test_full_tables_cells_context_and_all_accepted_column_widths_are_preserved(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']);
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount(1, $tables); $data = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $headers = array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th')));
            self::assertCount(3, $headers); self::assertSame($headers, $data['headers']);
            $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
            self::assertCount([6, 7, 5][$index], $data['rows']);
            foreach ($rows as $rowIndex => $row) {
                $cells = array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td')));
                self::assertCount(3, $cells); self::assertSame($cells, $data['rows'][$rowIndex]['cells']);
            }
            $table = $dom->getElementsByTagName('table')->item(0); $wrapper = $table->parentNode;
            self::assertSame($this->tableContext($wrapper, 'previousSibling'), $data['intro']);
            self::assertSame($this->tableContext($wrapper, 'nextSibling'), $data['outro']);
            self::assertSame(900, $data['table_min_width']);
            self::assertSame([[190, 270, 330], [240, 250, 300], [210, 270, 300]][$index], $data['column_min_widths']);
            self::assertStringContainsString('min-width:900px;', $table->getAttribute('style'));
        }
    }

    public function test_all_eighteen_quality_decisions_are_explicit_and_only_two_coherent_analyses_are_details(): void
    {
        [, $package] = Package::load();
        $expected = [
            [[127,40,3],[175,44,5],[115,51,3],[107,37,3],[72,43,6],[71,25,3]],
            [[74,36,3],[94,28,2],[88,54,7],[167,32,2],[228,51,3],[122,64,6]],
            [[179,22,3],[177,27,2],[230,51,4],[0,151,16],[82,42,3],[86,62,6]],
        ];
        foreach ($package['targets'] as $index => $target) {
            self::assertCount(6, $target['detail_quality_audit']); $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $pointIndex => $point) {
                    self::assertNotEmpty($point['basic']);
                    if ($point['detail'] === '') { continue; }
                    $retained[] = [$plan['source_section'], $pointIndex];
                    self::assertStringContainsString($index === 0 ? 'For comparing two club timetables' : 'This separation of weekday and weekend classes also creates', $point['basic']);
                    self::assertStringContainsString($index === 0 ? 'Для порівняння розкладів двох гуртків' : 'Такий поділ занять у будні й вихідні', $point['basic']);
                    self::assertSame([44, 51, 0][$index], preg_match_all('/\S+/u', $this->diagnosticText($point['detail'])));
                    self::assertSame([5, 3, 0][$index], preg_match_all('/[.!?](?:\s|$)/u', $this->diagnosticText($point['detail'])));
                }
            }
            self::assertSame([[[2, 0]], [[5, 0]], []][$index], $retained, 'Finite 1/1/0 ownership, never a global word threshold.');
            foreach ($target['detail_quality_audit'] as $section => $decision) {
                self::assertSame($expected[$index][$section], [$decision['basic_words'], $decision['detail_words'], $decision['detail_sentences']]);
                $detail = ($index === 0 && $section === 1) || ($index === 1 && $section === 4);
                self::assertSame($detail ? 'meaningful_detail' : 'visible_basic', $decision['decision']);
                self::assertNotEmpty($decision['reason']);
                self::assertStringContainsString($this->plain($decision['candidate_html']), $this->plain($this->coreHtml($target)));
                self::assertSame(preg_match_all('/\S+/u', $this->diagnosticText($decision['candidate_html'])), $decision['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $this->diagnosticText($decision['candidate_html'])), $decision['detail_sentences']);
            }
        }
        self::assertSame('source-analysis-list', $package['targets'][0]['detail_quality_audit'][1]['point']);
        self::assertSame('source-paragraph-analysis', $package['targets'][1]['detail_quality_audit'][4]['point']);
        self::assertSame('source-final-list', $package['targets'][2]['detail_quality_audit'][3]['point']);
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($kind) => [$kind], ['claim-evidence-reasoning-swapped', 'desktop-scope-lost', 'no-phone-test-lost',
            'simultaneous-to-speed', 'sequence-to-cause', 'every-all-added', 'limitation-lost', 'invented-counterargument-evidence',
            'cohesion-coherence-conflated', 'nevertheless-conversely', 'moreover-consequence', 'consequently-unsupported',
            'accordingly-generic-synonym', 'ambiguous-this', 'comma-splice-accepted', 'needless-causal-transition',
            'some-all', 'may-will', 'permission-prediction', 'before-ten-at-ten', 'not-all-no', 'at-least-exactly',
            'condition-dropped', 'source-strengthened', 'own-interpretation-source', 'after-because', 'paraphrase-specification',
            'new-fact-added', 'actor-removed', 'translation-loss', 'neighbour-detail', 'duplicate-id', 'answer-loss']);
    }

    private function changeFirstBody(array &$package, int $target, string $from, string $to): void
    {
        foreach ($package['targets'][$target]['after']['page']['blocks'] as &$block) {
            if (in_array($block['type'], ['hero', 'practice-set'], true)) { continue; }
            if (!str_contains($block['body'], $from)) { continue; }
            $block['body'] = substr_replace($block['body'], $to, strpos($block['body'], $from), strlen($from));
            json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); return;
        }
        self::fail('Mutation must alter a real ordered author fragment: '.$from);
    }

    #[DataProvider('semanticMutations')]
    public function test_all_thirty_three_requested_semantic_mutations_fail_closed(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'claim-evidence-reasoning-swapped' => [0, 'Перше речення — обмежена теза', 'Перше речення — фактична опора'],
            'desktop-scope-lost' => [0, 'on the desktop screen described here', 'on every possible screen'],
            'no-phone-test-lost' => [0, 'На телефоні макет не перевіряли.', 'На телефоні макет перевірили.'],
            'simultaneous-to-speed' => [0, 'one of simultaneous access, not demonstrated speed or accuracy.', 'one of proven faster booking and greater accuracy.'],
            'sequence-to-cause' => [0, 'Сам порядок подій не доводить причини.', 'Сам порядок подій доводить причину.'],
            'every-all-added' => [0, 'This allows the reader to compare the times without switching views.', 'This makes all readers compare every timetable faster.'],
            'limitation-lost' => [0, 'Час виконання завдань, помилки й уподобання користувачів не вимірювали.', ''],
            'invented-counterargument-evidence' => [0, 'Since no phone test is available', 'Since a successful phone test is available'],
            'cohesion-coherence-conflated' => [1, 'смислова цілісність', 'та сама видима мовна зв’язність'],
            'nevertheless-conversely' => [1, 'Nevertheless, it covers every step', 'Conversely, it covers every step'],
            'moreover-consequence' => [1, 'Moreover, it gives the deadline', 'Consequently, it gives the deadline'],
            'consequently-unsupported' => [1, 'Додавання <code>consequently</code> приписало б причинний зв’язок, для якого тут немає опори.', 'Consequently тут завжди доводить причинний зв’язок.'],
            'accordingly-generic-synonym' => [1, 'показує узгодження дії з місткістю', 'є універсальним синонімом будь-якого переходу'],
            'ambiguous-this' => [1, 'This separation of weekday and weekend classes', 'This'],
            'comma-splice-accepted' => [1, 'The index is short; however, it covers every chapter.', 'The index is short, however, it covers every chapter.'],
            'needless-causal-transition' => [1, 'The guide has an index and a glossary.', 'The guide has an index; consequently, it has a glossary.'],
            'some-all' => [2, 'Some registered members may use', 'All registered members may use'],
            'may-will' => [2, 'Some registered members may use', 'Some registered members will use'],
            'permission-prediction' => [2, 'Saturday use of the reading room is permitted', 'Saturday use of the reading room is predicted'],
            'before-ten-at-ten' => [2, 'Visitors are not allowed to enter before ten.', 'Visitors are allowed to enter at ten.'],
            'not-all-no' => [2, 'Not all requests were approved.', 'No requests were approved.'],
            'at-least-exactly' => [2, 'at least three', 'exactly three'],
            'condition-dropped' => [2, 'if a supervisor is present', 'regardless of supervision'],
            'source-strengthened' => [2, 'According to the coordinator’s note', 'The coordinator conclusively proves'],
            'own-interpretation-source' => [2, 'I interpret this as a way', 'The coordinator states that this is a way'],
            'after-because' => [2, 'Three participants left after the break.', 'Three participants left because the break was boring.'],
            'paraphrase-specification' => [2, 'не є точним перефразуванням самої першої фрази', 'є точним перефразуванням самої першої фрази'],
            'new-fact-added' => [2, 'Their reasons were not recorded.', 'Their reasons were recorded as dissatisfaction.'],
            'actor-removed' => [2, 'Two proposals were rejected by the review panel.', 'Two proposals were rejected.'],
            'translation-loss' => [2, 'Жодному відвідувачу не дозволено входити до десятої.', ''],
        ];
        if (isset($pairs[$kind])) {
            [$target, $from, $to] = $pairs[$kind]; $old = $this->plain($this->coreHtml($package['targets'][$target]));
            $this->changeFirstBody($package, $target, $from, $to);
            self::assertNotSame($old, $this->plain($this->coreHtml($package['targets'][$target])), 'Substantive ordered-corpus mutation, never a no-op.');
        } elseif ($kind === 'answer-loss') {
            foreach ($package['targets'][2]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') { continue; }
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); unset($data['inputs'][0]['answer']);
                $block['body'] = Package::json($data); break;
            }
        } elseif ($kind === 'neighbour-detail') {
            $point = &$package['targets'][0]['plans'][1]['points'][0]; $old = $point['detail'];
            $point['detail'] = $package['targets'][1]['plans'][4]['points'][0]['detail']; self::assertNotSame($old, $point['detail']);
        } else {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m34_v1']['key'] = $package['targets'][0]['plans'][0]['key'];
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
        }
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_internal_sentence_boundary_and_author_order_loss_are_rejected(): void
    {
        [$before, $package] = Package::load(); $old = $this->plain($this->coreHtml($package['targets'][0]));
        $this->changeFirstBody($package, 0, 'with every time legible. This allows', 'with every time legible, This allows');
        self::assertNotSame($old, $this->plain($this->coreHtml($package['targets'][0])));
        try { Package::validate($before, $package); self::fail('Internal punctuation loss must fail.'); }
        catch (RuntimeException) { self::assertTrue(true); }
        [$before, $package] = Package::load(); $old = $this->plain($this->coreHtml($package['targets'][1]));
        $data = json_decode($package['targets'][1]['after']['page']['blocks'][3]['body'], true, flags: JSON_THROW_ON_ERROR);
        [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]];
        $package['targets'][1]['after']['page']['blocks'][3]['body'] = Package::json($data);
        self::assertNotSame($old, $this->plain($this->coreHtml($package['targets'][1])), 'A word bag does not prove ordered fidelity.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_diagnostic_counter_does_not_change_ordered_core_punctuation_normalization(): void
    {
        self::assertSame('Known. Next fact.', $this->diagnosticText('Known.<br><br><em>Next</em> fact.'));
        self::assertSame(3, preg_match_all('/\S+/u', $this->diagnosticText('Known.<br><br><em>Next</em> fact.')));
        self::assertSame('not supported: reason.', $this->diagnosticText('<strong>not supported</strong>: reason.'));
        self::assertSame('left cell right cell', $this->diagnosticText('<table><tr><td>left cell</td><td>right cell</td></tr></table>'));
        self::assertSame('not supported : reason.', $this->plain('<strong>not supported</strong>: reason.'));
    }
}
