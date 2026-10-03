<?php

namespace Tests\Feature;

use App\Support\M30ParticipleClausesPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent ordered-author checks, not merely package hashes or word bags. */
class M30AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(
            preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function inner(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); }
        return trim($html);
    }

    private function sourceDom(array $before): DOMDocument
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">'.$before['page']['blocks'][1]['body'], LIBXML_NONET);
        libxml_clear_errors();
        return $dom;
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
                $parts[] = $data['intro'];
                foreach ($data['headers'] as $header) { $parts[] = $header; }
                foreach ($data['rows'] as $row) { foreach ($row['cells'] as $cell) { $parts[] = $cell; } }
                $parts[] = $data['outro'];
            } elseif ($block['type'] === 'summary-list') {
                foreach ($data['items'] as $item) { $parts[] = $item; }
            } else { throw new RuntimeException('Unexpected M30 teaching block.'); }
        }
        return implode(' ', $parts);
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'practice-set'));
        self::assertCount(1, $blocks);
        return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_frozen_sources_protected_metadata_and_ordered_core_are_exact(): void
    {
        [$before, $package] = Package::load();
        Package::validate($before, $package);
        self::assertSame(['9e2b8dc2d32937332b0fc931c1cad38f9b1202a9', '47b17a940752a54aeda05bf1362febbe11e20e26',
            '0c9658497fd31b001d92c8a7f42f63f132723a7e'], array_column($before['targets'], 'source_git_blob'));
        foreach ($package['targets'] as $index => $target) {
            $original = $before['targets'][$index]['before'];
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0]);
            $restored = $target['after']; $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored, 'All page metadata/subtitle/tags remain unchanged.');
            $first = $target['after']['page']['blocks'][1]; $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']);
            self::assertSame($oldFirst, $first, 'Old first-box identity/order fields remain unchanged.');
            $html = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $original['page']['blocks'][1]['body']);
            self::assertSame($this->plain($html), $this->plain($this->coreHtml($target)),
                'Ordered teaching text, tables, examples, translations and punctuation are preserved.');
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'));
        }
    }

    public function test_all_eighteen_author_prompts_keys_and_six_case_mappings_are_exact(): void
    {
        [$before, $package] = Package::load();
        $banks = ['PolyglotParticipleClausesBasicsB2LessonSeeder', 'PolyglotParticipleClausesC1LessonSeeder',
            'PolyglotAdvancedParticipleAndAbsoluteClausesC2LessonSeeder'];
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']); $xpath = new DOMXPath($dom);
            $prompts = $xpath->query('//ol[@data-self-checks]/li'); $keys = $xpath->query('//ol[@data-self-check-answers]/li');
            self::assertCount(6, $prompts); self::assertCount(6, $keys);
            $practice = $this->practice($target); $author = $practice['author_self_check'];
            self::assertSame(array_map($this->inner(...), iterator_to_array($prompts)), $author['prompts']);
            self::assertSame(array_map($this->inner(...), iterator_to_array($keys)), $author['answers']);
            self::assertSame('Ключ і пояснення', $author['title']);
            $indices = [];
            foreach (['selects', 'choices', 'inputs'] as $kind) {
                self::assertCount(2, $practice[$kind]);
                foreach ($practice[$kind] as $item) {
                    $case = $item['source_index']; $indices[] = $case;
                    self::assertSame($author['prompts'][$case - 1], $item['context']);
                    self::assertSame($author['answers'][$case - 1], $item['author_explanation']);
                    self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices, 'No lost or duplicated author case.');
            self::assertSame(['4'], $practice['linked_practice']['question_types']);
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$index]], $practice['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels', implode(' ', $practice['linked_practice']['seeder_classes']));
        }
        $c2 = $this->practice($package['targets'][2]);
        self::assertTrue($c2['inputs'][0]['punctuation_sensitive']);
        self::assertTrue($c2['inputs'][1]['punctuation_sensitive']);
        self::assertSame('The lights having been switched off, the guard locked the hall.', $c2['inputs'][0]['answer']);
        self::assertSame('After the rehearsal had ended, the instruments were packed. The players were tired. The conductor thanked everyone.', $c2['inputs'][1]['answer']);
    }

    public function test_comparison_tables_retain_full_four_column_cells_in_source_order(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']);
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'comparison-table'));
            self::assertCount(1, $tables);
            $data = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $headers = array_map(fn ($n) => trim($n->textContent), iterator_to_array($dom->getElementsByTagName('th')));
            self::assertCount(4, $headers); self::assertSame($headers, $data['headers']);
            $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
            self::assertCount($rows->length, $data['rows']);
            foreach ($rows as $n => $row) {
                $cells = array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td')));
                self::assertCount(4, $cells); self::assertSame($cells, $data['rows'][$n]['cells']);
            }
            self::assertNotEmpty($data['intro']); self::assertNotEmpty($data['outro']);
        }
    }

    public function test_semantic_finite_decisions_keep_every_candidate_in_visible_basic(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); self::assertNotEmpty($point['basic']); }
            }
            foreach ($target['detail_quality_audit'] as $decision) {
                self::assertSame('visible_basic', $decision['decision']); self::assertNotEmpty($decision['reason']);
                self::assertStringContainsString($this->plain($decision['candidate_html']), $this->plain($this->coreHtml($target)));
                self::assertSame(preg_match_all('/\S+/u', trim(html_entity_decode(strip_tags($decision['candidate_html']), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $decision['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', trim(html_entity_decode(strip_tags($decision['candidate_html']), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $decision['detail_sentences']);
            }
        }
    }

    public static function mutations(): array
    {
        return array_map(fn ($kind) => [$kind], ['ing-v3', 'active-passive', 'subject-object', 'dangling-invented', 'not', 'might',
            'lexical-having', 'perfect-form', 'absolute-subject', 'comma-boundary', 'with-removal', 'comma-splice',
            'translation', 'neighbour', 'detail-neighbour', 'duplicate-id', 'answer']);
    }

    private function changeFirstBody(array &$package, int $target, string $from, string $to): void
    {
        foreach ($package['targets'][$target]['after']['page']['blocks'] as &$block) {
            if (str_contains($block['body'], $from)) {
                $position = strpos($block['body'], $from);
                $block['body'] = substr_replace($block['body'], $to, $position, strlen($from));
                json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                return;
            }
        }
        self::fail('Mutation must target a real author fragment: '.$from);
    }

    #[DataProvider('mutations')]
    public function test_every_semantic_negative_fixture_is_rejected(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'ing-v3' => [0, 'Present participle</strong> має форму -ing', 'Present participle</strong> має форму V3'],
            'active-passive' => [0, 'Forms — бланки; їх підписали.', 'Forms — бланки; вони підписали.'],
            'subject-object' => [0, 'Це додаток після checked, не підмет I.', 'Це підмет після checked, не додаток I.'],
            'dangling-invented' => [0, 'Не замінюй її довільно на «Оксана почула годинник»', 'Замінюй її на «Оксана почула годинник»'],
            'not' => [1, 'Not recognising the number, Olha let the phone ring.', 'Recognising the number, Olha let the phone ring.'],
            'might' => [1, 'the filter might be damaged', 'the filter is damaged'],
            'lexical-having' => [1, 'форма лексичного <strong>have</strong>', 'форма perfect <strong>have</strong>'],
            'perfect-form' => [1, 'having + found</strong>', 'having + find</strong>'],
            'absolute-subject' => [2, 'The passengers waiting on the platform, the steward checked the list.', 'Waiting on the platform, the steward checked the list.'],
            'comma-boundary' => [2, 'The volunteers, their task completed, went home.', 'The volunteers their task, completed, went home.'],
            'with-removal' => [2, 'With the wind rising, we secured the boat.', 'The wind rising, we secured the boat.'],
            'comma-splice' => [2, 'Because the wind was rising, we secured the boat.', 'The wind was rising, we secured the boat.'],
            'translation' => [0, 'Я перевірив бланки, підписані вчора.', ''],
        ];
        if (isset($pairs[$kind])) {
            [$target, $from, $to] = $pairs[$kind]; $this->changeFirstBody($package, $target, $from, $to);
        } elseif ($kind === 'answer') {
            foreach ($package['targets'][2]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') { continue; }
                $data = json_decode($block['body'], true); unset($data['inputs'][0]['answer']); $block['body'] = Package::json($data); break;
            }
        } elseif ($kind === 'detail-neighbour') {
            $package['targets'][0]['plans'][0]['points'][0]['detail'] = $package['targets'][0]['plans'][1]['points'][0]['basic'];
        } elseif ($kind === 'neighbour') {
            $originalCore = $this->plain($this->coreHtml($package['targets'][0]));
            $a = json_decode($package['targets'][0]['after']['page']['blocks'][1]['body'], true);
            $b = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true);
            [$a['sections'][0]['description'], $b['sections'][0]['description']] = [$b['sections'][0]['description'], $a['sections'][0]['description']];
            $package['targets'][0]['after']['page']['blocks'][1]['body'] = Package::json($a);
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($b);
            self::assertNotSame($originalCore, $this->plain($this->coreHtml($package['targets'][0])), 'Independent ordered fidelity catches neighbouring-point movement.');
        } else {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true);
            $data['m30_v1']['key'] = $package['targets'][0]['plans'][0]['key'];
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
        }
        $this->expectException(RuntimeException::class);
        Package::validate($before, $package);
    }
}
