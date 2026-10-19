<?php

namespace Tests\Feature;

use App\Support\M32FormalEnglishPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent checks against the three accepted M16 author blobs, not a word bag. */
class M32AuthorFidelityTest extends TestCase
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
                throw new RuntimeException('Unexpected M32 teaching block.');
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

    public function test_frozen_git_blobs_metadata_and_full_ordered_core_are_exact(): void
    {
        [$before, $package] = Package::load();
        Package::validate($before, $package);
        $blobs = ['2f36b86d9e19af0739387d9117f0074cb2ba92c7', '0679c7b3c84e3317a184280ad4a5ff1054863515',
            '79966df02d62d62f3481ddd4bb06a16955ad260c'];
        self::assertSame($blobs, array_column($before['targets'], 'source_git_blob'));
        self::assertSame(['formal-register-and-nominalisation-basics', 'nominalisation-formal-register',
            'register-tone-and-paraphrase'], array_column($package['targets'], 'slug'));
        foreach ($package['targets'] as $index => $target) {
            $original = $before['targets'][$index]['before'];
            $sourceBytes = json_encode($original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$index], sha1('blob '.strlen($sourceBytes)."\0".$sourceBytes),
                'Independently reconstruct the exact accepted Git blob, not only its manifest label.');
            self::assertSame($target['after'], json_decode(file_get_contents(base_path($target['path'])), true, flags: JSON_THROW_ON_ERROR));
            self::assertCount(8, $target['plans']);
            self::assertSame(['formal-english'], $target['ancestry']);
            self::assertSame('uk', $target['after']['page']['locale']);
            self::assertSame('theory', $target['after']['type']);
            self::assertSame($original['page']['blocks'][0], $target['after']['page']['blocks'][0], 'Hero/level remain unchanged.');
            $restored = $target['after'];
            $restored['page']['blocks'] = $original['page']['blocks'];
            self::assertSame($original, $restored, 'All title/subtitle/slug/category/tag metadata remains exact.');
            $first = $target['after']['page']['blocks'][1];
            $oldFirst = $original['page']['blocks'][1];
            unset($first['type'], $first['body'], $oldFirst['type'], $oldFirst['body']);
            self::assertSame($oldFirst, $first, 'The old first-box owner/order fields remain unchanged.');
            $html = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $original['page']['blocks'][1]['body']);
            self::assertSame($this->plain($html), $this->plain($this->coreHtml($target)),
                'All ordered words, punctuation, translations, examples, paragraphs and table cells remain.');
            preg_match_all('~href="([^"]+)"~', $html, $oldLinks);
            preg_match_all('~href="([^"]+)"~', $this->coreHtml($target), $newLinks);
            self::assertSame($oldLinks[1], $newLinks[1], 'Accepted continuation links retain source order.');
            self::assertNotContains('box', array_column($target['after']['page']['blocks'], 'type'), 'No hidden duplicate author box.');
        }
    }

    public function test_all_eighteen_exact_cases_keys_and_variable_ui_mappings_have_own_bank(): void
    {
        [$before, $package] = Package::load();
        $mappings = [
            ['selects' => [], 'choices' => [1, 2, 4], 'inputs' => [3, 5, 6]],
            ['selects' => [], 'choices' => [], 'inputs' => [1, 2, 3, 4, 5, 6]],
            ['selects' => [], 'choices' => [1, 3, 5], 'inputs' => [2, 4, 6]],
        ];
        $banks = ['PolyglotFormalRegisterAndNominalisationBasicsB2LessonSeeder', 'PolyglotNominalisationFormalRegisterC1LessonSeeder',
            'PolyglotRegisterToneAndParaphraseC1LessonSeeder'];
        foreach ($package['targets'] as $index => $target) {
            $xpath = new DOMXPath($this->sourceDom($before['targets'][$index]['before']));
            $prompts = $xpath->query('//ol[@data-self-checks]/li');
            $keys = $xpath->query('//ol[@data-self-check-answers]/li');
            self::assertCount(6, $prompts);
            self::assertCount(6, $keys);
            $data = $this->practice($target);
            $author = $data['author_self_check'];
            self::assertSame(array_map($this->inner(...), iterator_to_array($prompts)), $author['prompts']);
            self::assertSame(array_map($this->inner(...), iterator_to_array($keys)), $author['answers']);
            self::assertSame('Самоперевірка', $author['section_title']);
            self::assertSame($this->inner($xpath->query('//section[starts-with(@id,"self-check-")]/p')->item(0)), $author['intro']);
            self::assertSame('Ключ і пояснення', $author['title']);
            $indices = [];
            foreach ($mappings[$index] as $kind => $mapping) {
                self::assertSame($mapping, array_column($data[$kind] ?? [], 'source_index'));
                foreach ($data[$kind] ?? [] as $item) {
                    $case = $item['source_index'];
                    $indices[] = $case;
                    self::assertSame($author['prompts'][$case - 1], $item['context']);
                    self::assertSame($author['answers'][$case - 1], $item['author_explanation']);
                    self::assertNotEmpty($item['answer']);
                }
            }
            sort($indices);
            self::assertSame(range(1, 6), $indices, 'No invented, duplicated or lost case; no forced two-per-type curriculum.');
            self::assertSame(['4'], $data['linked_practice']['question_types']);
            self::assertSame(['Database\\Seeders\\V3\\Polyglot\\'.$banks[$index]], $data['linked_practice']['seeder_classes']);
            self::assertStringNotContainsString('AllLevels', implode(' ', $data['linked_practice']['seeder_classes']));
        }
        $b2 = $this->practice($package['targets'][0]);
        self::assertStringContainsString('on Monday to invite two speakers', $b2['inputs'][0]['answer']);
        self::assertStringContainsString('by noon tomorrow', $b2['inputs'][1]['answer']);
        self::assertStringContainsString('I need it to prepare the name cards.', $b2['inputs'][1]['answer'], 'Both message sentences remain.');
        self::assertSame('Yesterday we decided to confirm the booking today.', $b2['inputs'][2]['answer']);
        $nominal = $this->practice($package['targets'][1]);
        foreach (['conservators', 'of the mural', 'currently in progress'] as $fragment) {
            self::assertStringContainsString($fragment, $nominal['inputs'][0]['answer']);
        }
        foreach (['by the volunteers', 'next week', 'is possible'] as $fragment) {
            self::assertStringContainsString($fragment, $nominal['inputs'][2]['answer']);
        }
        self::assertStringContainsString('announced yesterday', $nominal['inputs'][4]['answer']);
        self::assertStringContainsString('had been cancelled', $nominal['inputs'][4]['answer']);
        foreach (['two designs', 'Tuesday', 'This evaluation', 'plans to add', 'Friday'] as $fragment) {
            self::assertStringContainsString($fragment, $nominal['inputs'][5]['answer']);
        }
        $tone = $this->practice($package['targets'][2]);
        foreach (['to you', 'by our designer', 'by 4 p.m. on Wednesday'] as $fragment) {
            self::assertStringContainsString($fragment, $tone['inputs'][0]['answer']);
        }
        foreach (['If the hall is unavailable', 'may move only the Saturday workshop', 'Friday’s session will'] as $fragment) {
            self::assertStringContainsString($fragment, $tone['inputs'][2]['answer']);
        }
    }

    public function test_full_comparison_tables_preserve_every_cell_and_accepted_empty_context(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            $dom = $this->sourceDom($before['targets'][$index]['before']);
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount(1, $tables);
            $data = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            $headers = array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th')));
            $columns = [4, 3, 3][$index];
            self::assertCount($columns, $headers);
            self::assertSame($headers, $data['headers']);
            $rows = $dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
            self::assertCount([4, 4, 3][$index], $data['rows']);
            foreach ($rows as $rowIndex => $row) {
                $cells = array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td')));
                self::assertCount($columns, $cells);
                self::assertSame($cells, $data['rows'][$rowIndex]['cells']);
            }
            $wrapper = $dom->getElementsByTagName('table')->item(0)->parentNode;
            self::assertSame($this->tableContext($wrapper, 'previousSibling'), $data['intro']);
            self::assertSame($this->tableContext($wrapper, 'nextSibling'), $data['outro']);
        }
    }

    public function test_finite_semantic_audit_preserves_all_eighteen_candidates_and_one_own_analysis(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $index => $target) {
            self::assertCount(6, $target['detail_quality_audit']);
            $retained = [];
            foreach ($target['plans'] as $plan) {
                foreach ($plan['points'] as $pointIndex => $point) {
                    self::assertNotEmpty($point['basic']);
                    if ($point['detail'] !== '') {
                        $retained[] = [$plan['source_section'], $pointIndex];
                        self::assertSame(55, preg_match_all('/\S+/u', trim(html_entity_decode(strip_tags($point['detail']), ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
                        self::assertSame(4, preg_match_all('/[.!?](?:\s|$)/u', $this->plain($point['detail'])));
                        foreach (['The archivists compared the two catalogues on Monday.', 'three missing entries',
                            'The performance of a comparison', 'У понеділок архівісти порівняли два каталоги.',
                            'Виконання порівняння двох каталогів архівістами'] as $fragment) {
                            self::assertStringContainsString($fragment, $this->plain($point['basic']), 'Full source and overloaded revision with both translations remain basic.');
                        }
                    }
                }
            }
            self::assertSame([[], [[5, 0]], []][$index], $retained, 'Explicit 0/1/0 semantic ownership, not a runtime length filter.');
            foreach ($target['detail_quality_audit'] as $decision) {
                $meaningful = $index === 1 && $decision['source_section'] === 5;
                self::assertSame($meaningful ? 'meaningful_detail' : 'visible_basic', $decision['decision']);
                self::assertNotEmpty($decision['reason']);
                self::assertStringContainsString($this->plain($decision['candidate_html']), $this->plain($this->coreHtml($target)));
                $plain = trim(html_entity_decode(strip_tags($decision['candidate_html']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                self::assertSame(preg_match_all('/\S+/u', $plain), $decision['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $plain), $decision['detail_sentences']);
            }
        }
    }

    public static function semanticMutations(): array
    {
        return array_map(fn ($kind) => [$kind], ['formal-automatically-better', 'i-we-removal', 'approve-approval-loss',
            'noun-phrase-misclassification', 'status-change', 'ongoing-completed', 'may-will', 'some-all', 'not-all-none',
            'by-on-deadline', 'should-must', 'condition-loss', 'after-because', 'known-agent-loss', 'invented-agent',
            'attribution-loss', 'translation-loss', 'neighbour-detail', 'duplicate-id', 'practice-answer-loss']);
    }

    private function changeFirstBody(array &$package, int $target, string $from, string $to): void
    {
        foreach ($package['targets'][$target]['after']['page']['blocks'] as &$block) {
            if (in_array($block['type'], ['hero', 'practice-set'], true)) {
                continue;
            }
            if (str_contains($block['body'], $from)) {
                $block['body'] = substr_replace($block['body'], $to, strpos($block['body'], $from), strlen($from));
                json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                return;
            }
        }
        self::fail('Mutation must change a real core author fragment: '.$from);
    }

    #[DataProvider('semanticMutations')]
    public function test_all_twenty_requested_semantic_negative_categories_are_rejected(string $kind): void
    {
        [$before, $package] = Package::load();
        $pairs = [
            'formal-automatically-better' => [0, 'Професійний текст не зобов’язаний звучати урочисто.', 'Професійний текст зобов’язаний звучати урочисто.'],
            'i-we-removal' => [0, 'We obtained permission to use the hall.', 'Permission was obtained to use the hall.'],
            'approve-approval-loss' => [0, 'approve → approval', 'approve → cover'],
            'noun-phrase-misclassification' => [0, 'Не кожна група з іменником є прикладом номіналізації.', 'Кожна група з іменником є прикладом номіналізації.'],
            'status-change' => [1, 'The team’s revision of the guide is planned for June.', 'The team’s revision of the guide was completed in June.'],
            'ongoing-completed' => [1, 'The engineers’ assessment of the bridge is in progress.', 'The engineers’ assessment of the bridge is complete.'],
            'may-will' => [2, 'Some guests may arrive late.', 'Some guests will arrive late.'],
            'some-all' => [2, 'Some guests may arrive late.', 'All guests may arrive late.'],
            'not-all-none' => [2, 'Not all</strong> означає «не всі», а не «жоден».', 'No applicants</strong> означає «не всі», а не «жоден».'],
            'by-on-deadline' => [0, 'I would be grateful if you could send the plan by Tuesday.', 'I would be grateful if you could send the plan on Tuesday.'],
            'should-must' => [2, 'Visitors should send comments by Friday.', 'Visitors must send comments by Friday.'],
            'condition-loss' => [2, 'We will publish the guide only if the editor approves it.', 'We will publish the guide.'],
            'after-because' => [2, 'The queue was shorter after the signs were replaced.', 'The queue was shorter because the signs were replaced.'],
            'known-agent-loss' => [2, 'The schedule will be sent by the organiser on Thursday.', 'The schedule will be sent on Thursday.'],
            'invented-agent' => [1, 'The rejection of the application occurred yesterday; the decision-maker is not named.', 'The manager rejected the application yesterday.'],
            'attribution-loss' => [2, 'Перефразування зовнішнього матеріалу не скасовує посилання на джерело.', ''],
            'translation-loss' => [2, 'Організатор надішле розклад у четвер.', ''],
        ];
        if (isset($pairs[$kind])) {
            [$target, $from, $to] = $pairs[$kind];
            $oldCore = $this->plain($this->coreHtml($package['targets'][$target]));
            $this->changeFirstBody($package, $target, $from, $to);
            self::assertNotSame($oldCore, $this->plain($this->coreHtml($package['targets'][$target])),
                'Independent ordered corpus detects a substantive mutation, not a no-op fixture.');
        } elseif ($kind === 'practice-answer-loss') {
            foreach ($package['targets'][1]['after']['page']['blocks'] as &$block) {
                if ($block['type'] !== 'practice-set') {
                    continue;
                }
                $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
                unset($data['inputs'][0]['answer']);
                $block['body'] = Package::json($data);
                break;
            }
        } elseif ($kind === 'neighbour-detail') {
            $point = &$package['targets'][1]['plans'][4]['points'][0];
            $old = $point['detail'];
            $point['detail'] = 'Перефразування зовнішнього матеріалу не скасовує посилання на джерело.';
            self::assertNotSame($old, $point['detail'], 'Replace the own analysis with a real foreign tone-page reminder.');
        } else {
            $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true, flags: JSON_THROW_ON_ERROR);
            $data['m32_v1']['key'] = $package['targets'][0]['plans'][0]['key'];
            $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
        }
        $this->expectException(RuntimeException::class);
        Package::validate($before, $package);
    }

    public function test_changed_internal_punctuation_and_reordered_core_are_rejected(): void
    {
        [$before, $package] = Package::load();
        $original = $this->plain($this->coreHtml($package['targets'][0]));
        $this->changeFirstBody($package, 0, 'room plan by 3 p.m. on Tuesday? I need it', 'room plan by 3 p.m. on Tuesday, I need it');
        self::assertNotSame($original, $this->plain($this->coreHtml($package['targets'][0])));
        try {
            Package::validate($before, $package);
            self::fail('Internal punctuation mutation must be rejected.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }
        [$before, $package] = Package::load();
        $original = $this->plain($this->coreHtml($package['targets'][1]));
        $a = json_decode($package['targets'][1]['after']['page']['blocks'][1]['body'], true, flags: JSON_THROW_ON_ERROR);
        $b = json_decode($package['targets'][1]['after']['page']['blocks'][2]['body'], true, flags: JSON_THROW_ON_ERROR);
        [$a['sections'][0]['description'], $b['sections'][0]['description']] = [$b['sections'][0]['description'], $a['sections'][0]['description']];
        $package['targets'][1]['after']['page']['blocks'][1]['body'] = Package::json($a);
        $package['targets'][1]['after']['page']['blocks'][2]['body'] = Package::json($b);
        self::assertNotSame($original, $this->plain($this->coreHtml($package['targets'][1])), 'A word bag cannot prove point order.');
        $this->expectException(RuntimeException::class);
        Package::validate($before, $package);
    }
}
