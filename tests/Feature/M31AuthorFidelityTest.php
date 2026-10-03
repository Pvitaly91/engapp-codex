<?php

namespace Tests\Feature;

use App\Support\M31ConditionalsPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent ordered-author checks for the three immutable accepted M15 owners. */
class M31AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(
            preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function inner(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
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

    private function tableContext(DOMNode $wrapper, string $direction): string
    {
        $paragraphs = [];
        for ($node = $wrapper->{$direction}; $node !== null; $node = $node->{$direction}) {
            if (in_array($node->nodeName, ['h4', 'section'], true)) {
                break;
            }
            if ($node->nodeName === 'p') {
                if ($direction === 'previousSibling') {
                    array_unshift($paragraphs, $this->inner($node));
                } else {
                    $paragraphs[] = $this->inner($node);
                }
            }
        }
        return implode('<br><br>', $paragraphs);
    }

    private function coreHtml(array $target): string
    {
        $parts = [];
        foreach (array_slice($target['after']['page']['blocks'], 1) as $block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($block['type'] === 'practice-set') {
                continue;
            }
            $parts[] = $data['title'];
            if ($block['type'] === 'usage-panels') {
                foreach ($data['sections'] as $point) {
                    $parts[] = $point['description'];
                }
            } elseif ($block['type'] === 'comparison-table') {
                $parts[] = $data['intro'];
                array_push($parts, ...$data['headers']);
                foreach ($data['rows'] as $row) {
                    array_push($parts, ...$row['cells']);
                }
                $parts[] = $data['outro'];
            } elseif ($block['type'] === 'summary-list') {
                array_push($parts, ...$data['items']);
            } else {
                throw new RuntimeException('Unexpected M31 teaching block.');
            }
        }
        return implode(' ', $parts);
    }

    private function practice(array $target): array
    {
        $blocks = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'practice-set'));
        self::assertCount(1, $blocks);
        return json_decode($blocks[0]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_frozen_source_identities_protected_fields_and_ordered_core_are_exact(): void
    {
        [$before, $package] = Package::load();
        Package::validate($before, $package);
        self::assertSame(['84b42d6b57c27b1c4d47719395758f19f57b9d63', 'cc056a2abdb231c7883e905703502d44e6885570',
            'c685fcea76e89659d9dd8b639f5ce9d3e5a905f6'], array_column($before['targets'], 'source_git_blob'));
        self::assertSame(['conditionals-with-unless-provided-as-long-as', 'advanced-conditionals',
            'conditional-alternatives-and-nuance'], array_column($package['targets'], 'slug'));
        foreach ($package['targets'] as $index => $target) {
            $original = $before['targets'][$index]['before'];
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']);
            self::assertSame(['conditionals'], $target['ancestry']);
            self::assertSame('uk', $target['after']['page']['locale']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0], 'Hero and level unchanged.');
            $restored = $target['after'];
            $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored, 'All subtitle/title/slug/category/ancestry/tag metadata remains unchanged.');
            $first = $target['after']['page']['blocks'][1];
            $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']);
            self::assertSame($oldFirst, $first, 'Old first-box identity/order fields remain unchanged.');
            $html = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $original['page']['blocks'][1]['body']);
            self::assertSame($this->plain($html), $this->plain($this->coreHtml($target)),
                'Ordered words, punctuation, examples, translations, tables and paragraphs preserved; not only a word bag.');
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'), 'No duplicate old box.');
        }
    }

    public function test_all_eighteen_author_cases_and_complete_keys_have_exact_ownership(): void
    {
        [$before, $package] = Package::load();
        $expectedMappings = [
            ['selects' => [1, 2], 'choices' => [3, 4], 'inputs' => [5, 6]],
            ['selects' => [1, 2], 'choices' => [3, 5], 'inputs' => [6, 4]],
            ['selects' => [2, 3], 'choices' => [1, 5], 'inputs' => [4, 6]],
        ];
        foreach ($package['targets'] as $index => $target) {
            $xpath = new DOMXPath($this->sourceDom($before['targets'][$index]['before']));
            $prompts = $xpath->query('//ol[@data-self-checks]/li');
            $keys = $xpath->query('//ol[@data-self-check-answers]/li');
            self::assertCount(6, $prompts); self::assertCount(6, $keys);
            $data = $this->practice($target); $author = $data['author_self_check'];
            self::assertSame(array_map($this->inner(...), iterator_to_array($prompts)), $author['prompts']);
            self::assertSame(array_map($this->inner(...), iterator_to_array($keys)), $author['answers']);
            self::assertSame('Ключ і пояснення', $author['title']);
            $indices = [];
            foreach ($expectedMappings[$index] as $kind => $mapping) {
                self::assertCount(2, $data[$kind]);
                self::assertSame($mapping, array_column($data[$kind], 'source_index'));
                foreach ($data[$kind] as $item) {
                    $case = $item['source_index']; $indices[] = $case;
                    self::assertSame($author['prompts'][$case - 1], $item['context']);
                    self::assertSame($author['answers'][$case - 1], $item['author_explanation']);
                    self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices); self::assertSame(range(1, 6), $indices);
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertCount(1, $data['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels', $data['linked_practice']['seeder_classes'][0]);
        }
        $c1 = $this->practice($package['targets'][1]);
        self::assertStringContainsString('could open the archive now', $c1['inputs'][0]['answer']);
        self::assertStringContainsString('would have finished the report on Tuesday', $c1['inputs'][1]['answer']);
        self::assertStringContainsString('would be ready to start the report now', $c1['inputs'][1]['answer']);
        $c2 = $this->practice($package['targets'][2]);
        foreach (['Should you need a printed copy', 'Were the ferry to stop running', 'Had the courier arrived earlier',
            'could have sent the sample'] as $fragment) {
            self::assertStringContainsString($fragment, $c2['inputs'][0]['answer'], 'All three original transformations preserved together.');
        }
        foreach (['Assuming the grant is confirmed', 'might open on Monday', 'written approval', 'otherwise'] as $fragment) {
            self::assertStringContainsString($fragment, $c2['inputs'][1]['answer'], 'Full grant/requirement/antecedent paragraph preserved.');
        }
    }

    public function test_full_comparison_tables_preserve_cells_and_column_counts(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']);
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount(1, $tables);
            $data = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $headers = array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th')));
            $columns = [4, 5, 3][$index];
            self::assertCount($columns, $headers); self::assertSame($headers, $data['headers']);
            $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
            self::assertCount([3, 3, 4][$index], $data['rows']);
            foreach ($rows as $rowIndex => $row) {
                $cells = array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td')));
                self::assertCount($columns, $cells); self::assertSame($cells, $data['rows'][$rowIndex]['cells']);
            }
            $wrapper = $dom->getElementsByTagName('table')->item(0)->parentNode;
            self::assertSame($this->tableContext($wrapper, 'previousSibling'), $data['intro'],
                'Preserve exact accepted table introduction, including B2’s intentionally empty introduction.');
            self::assertSame($this->tableContext($wrapper, 'nextSibling'), $data['outro'],
                'Preserve all accepted paragraphs after the table in source order.');
        }
    }

    public function test_only_two_coherent_paragraph_analyses_are_optional_details(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $pointIndex => $point) {
                    self::assertNotEmpty($point['basic']);
                    if ($point['detail'] !== '') {
                        $retained[] = $plan['source_section'];
                        self::assertSame(0, $pointIndex, 'Analysis belongs to its own paragraph point.');
                        self::assertGreaterThanOrEqual(5, preg_match_all('/[.!?](?:\s|$)/u', $this->plain($point['detail'])));
                        self::assertStringContainsString('Переклад:', $this->plain($point['basic']), 'Full paragraph and translation remain basic.');
                    }
                }
            }
            self::assertSame([[], [5], [6]][$index], $retained, 'Finite semantic decision, not a global word-count threshold.');
        }
    }

    public static function mutations(): array
    {
        return array_map(fn ($kind) => [$kind], ['unless-polarity', 'if-not-meaning', 'as-long-as-function', 'future-form',
            'past-present-result', 'mixed-direction', 'might-to-would', 'could-to-would', 'assumption-to-requirement',
            'otherwise-antecedent', 'but-for-exception', 'inversion-model', 'not-before-subject', 'question-as-condition',
            'translation-loss', 'neighbour-detail', 'duplicate-anchor', 'practice-answer-loss']);
    }

    private function changeFirstBody(array &$package, int $target, string $from, string $to): void
    {
        foreach ($package['targets'][$target]['after']['page']['blocks'] as &$block) {
            if ($block['type'] === 'hero' || $block['type'] === 'practice-set') {
                continue;
            }
            if (str_contains($block['body'], $from)) {
                $position = strpos($block['body'], $from);
                $block['body'] = substr_replace($block['body'], $to, $position, strlen($from));
                json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                return;
            }
        }
        self::fail('Mutation must change a real author fragment: '.$from);
    }

    #[DataProvider('mutations')]
    public function test_all_eighteen_semantic_negative_categories_are_rejected(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'unless-polarity' => [0, 'Unless you confirm the booking, we’ll release the room.', 'Unless you don’t confirm the booking, we’ll release the room.'],
            'if-not-meaning' => [0, 'If Olena hadn’t given us a map, we would have got lost.', 'Unless Olena had given us a map, we would have got lost.'],
            'as-long-as-function' => [0, 'Тривалість у розповіді про завершену подію:', 'Умова дозволу:'],
            'future-form' => [0, 'Provided that Lena arrives by six, we’ll start together.', 'Provided that Lena will arrive by six, we’ll start together.'],
            'past-present-result' => [1, 'She would have moved there in June.', 'She would move there now.'],
            'mixed-direction' => [1, 'If Oleh spoke Italian, he would have applied for that job.', 'If Oleh had spoken Italian, he would apply for that job now.'],
            'might-to-would' => [1, 'more visitors might be using the upper floor now', 'more visitors would be using the upper floor now'],
            'could-to-would' => [1, 'we could move the cabinet upstairs now', 'we would move the cabinet upstairs now'],
            'assumption-to-requirement' => [2, 'Assuming the hall is free, we could meet there.', 'On condition that the hall is free, we could meet there.'],
            'otherwise-antecedent' => [2, 'If you do not keep the receipt, you cannot collect the parcel.', 'If the receipt is unsigned, you cannot collect the parcel.'],
            'but-for-exception' => [2, 'The desk was bare but for a blue folder.', 'If it had not been for a blue folder, the desk would have been bare.'],
            'inversion-model' => [2, 'Were the bridge to close, we would use the ferry.', 'Were the bridge close, we would use the ferry.'],
            'not-before-subject' => [2, 'Had the guide not checked the tide, we might have been stranded.', 'Had not the guide checked the tide, we might have been stranded.'],
            'question-as-condition' => [2, 'Had the guide not checked the tide, we might have been stranded.', 'Hadn’t the guide checked the tide?'],
            'translation-loss' => [0, 'Якщо дорогу не перекрито, поїдемо вздовж узбережжя.', ''],
        ];
        if (isset($pairs[$kind])) {
            [$target, $from, $to] = $pairs[$kind];
            $originalCore = $this->plain($this->coreHtml($package['targets'][$target]));
            $this->changeFirstBody($package, $target, $from, $to);
            self::assertNotSame($originalCore, $this->plain($this->coreHtml($package['targets'][$target])), 'Independent ordered corpus detects the actual semantic mutation.');
        } elseif ($kind === 'practice-answer-loss') {
            foreach ($package['targets'][2]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') {
                    continue;
                }
                $data = json_decode($block['body'], true);
                unset($data['inputs'][0]['answer']);
                $block['body'] = Package::json($data);
                break;
            }
        } elseif ($kind === 'neighbour-detail') {
            $point = &$package['targets'][1]['plans'][4]['points'][0];
            $old = $point['detail'];
            $point['detail'] = $package['targets'][2]['plans'][5]['points'][0]['detail'];
            self::assertNotSame($old, $point['detail'], 'A real neighbouring-owner analysis is substituted.');
        } else {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true);
            $data['m31_v1']['key'] = $package['targets'][0]['plans'][0]['key'];
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
        }
        $this->expectException(RuntimeException::class);
        Package::validate($before, $package);
    }
}
