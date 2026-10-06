<?php

namespace Tests\Feature;

use App\Support\M39AuthoredRevisionPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent audit against the immutable M23 author master, not an after fixture. */
class M39AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function diagnosticText(string $html): string
    {
        $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function dom(string $html): DOMDocument
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET);
        libxml_clear_errors();
        return $dom;
    }

    private function inner(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); }
        return trim($html);
    }

    private function master(): array
    {
        $raw = file_get_contents(base_path('docs/content/m23-authored-content.v1.json'));
        $gitLf = str_replace("\r\n", "\n", $raw);
        self::assertSame('eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6', hash('sha256', $gitLf));
        self::assertSame('34a03a7146141fbff50c66ec8e41f3fc2a59b787', sha1('blob '.strlen($gitLf)."\0".$gitLf));
        return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    }

    private function core(array $target): string
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
            } elseif ($block['type'] === 'summary-list') {
                array_push($parts, ...$data['items']);
            } else { throw new RuntimeException('Unexpected M39 native teaching type.'); }
        }
        return implode(' ', $parts);
    }

    public function test_immutable_master_policy_definition_and_finite_snapshot_are_exact(): void
    {
        $master = $this->master(); [$before, $package] = Package::load();
        $snapshot = $before['author_master_source'];
        self::assertSame('eae0e632ae528e3500eeb40188d4b61d275703bfe722cd2fa9fe495f1d2b1ff6', $snapshot['git_lf_sha256']);
        self::assertSame($master, json_decode($snapshot['git_lf_bytes'], true, flags: JSON_THROW_ON_ERROR));
        self::assertSame('3d919d5894ac37a11a107d31075078c419394a0b3f537f21e303523bd4213c32', $snapshot['working_raw_sha256'], 'Captured Windows raw checksum remains distinct from the immutable Git-LF checksum.');
        $actualMaster = file_get_contents(base_path($snapshot['path']));
        self::assertSame($snapshot['git_lf_bytes'], str_replace("\r\n", "\n", $actualMaster));
        if (str_contains($actualMaster, "\r\n")) { self::assertSame($snapshot['working_raw_sha256'], hash('sha256', $actualMaster)); }
        $notes = file_get_contents(base_path($before['author_notes_source']['path']));
        $notesLf = str_replace("\r\n", "\n", $notes);
        self::assertSame('859c4263cb00c0ff318bf2b41f3e450ca65efc0d', sha1('blob '.strlen($notesLf)."\0".$notesLf));
        self::assertSame('afe9ec2d6a5f10b496fc0fdcabcea1e546599f3063af8bbb1fd7d4ba34e78ffa', $before['author_notes_source']['working_raw_sha256']);
        if (str_contains($notes, "\r\n")) { self::assertSame(hash('sha256', $notes), $before['author_notes_source']['working_raw_sha256']); }
        foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $flag) {
            self::assertFalse($master['content_policy'][$flag]);
        }
        foreach ($master['lessons'] as $owner => $lesson) {
            $accepted = $before['targets'][$owner]['before'];
            self::assertSame($lesson['body_html'], $accepted['page']['blocks'][1]['body']);
            self::assertSame($lesson['body_sha256'], hash('sha256', $accepted['page']['blocks'][1]['body']));
            self::assertSame($lesson['hero'], json_decode($accepted['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR));
            self::assertSame($lesson['subtitle_html'], $accepted['page']['subtitle_html']);
            self::assertSame($lesson['subtitle_text'], $accepted['page']['subtitle_text']);
            self::assertSame($lesson['preserve_page_title'], $accepted['page']['title']);
            self::assertSame($lesson['box_heading'], $accepted['page']['blocks'][1]['heading']);
            self::assertSame($lesson['hero'], json_decode($package['targets'][$owner]['after']['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_ordered_master_content_examples_translations_tables_and_links_survive(): void
    {
        $master = $this->master(); [, $package] = Package::load();
        foreach ($master['lessons'] as $i => $lesson) {
            $old = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $lesson['body_html']);
            $new = $this->core($package['targets'][$i]);
            self::assertSame($this->plain($old), $this->plain($new), 'Punctuation, casing, numbers, actors, time, modality, voice, scope and order are never normalized away.');
            preg_match_all('~href="([^"]+)"~', $old, $oldLinks); preg_match_all('~href="([^"]+)"~', $new, $newLinks);
            self::assertSame($oldLinks[1], $newLinks[1]);
            $dom = $this->dom($old);
            $tables = array_values(array_filter($package['targets'][$i]['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount($i === 2 ? 0 : 1, $tables, 'C2 has no author table; do not invent one for visual consistency.');
            if ($i === 2) { self::assertSame(0, $dom->getElementsByTagName('table')->length); continue; }
            $table = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertCount(3, $table['headers']); self::assertCount(3, $table['rows']);
            self::assertSame(array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th'))), $table['headers']);
            foreach ($dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $rowIndex => $row) {
                self::assertSame(array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td'))), $table['rows'][$rowIndex]['cells']);
            }
            self::assertSame(720, $table['table_min_width']); self::assertSame([240, 240, 240], $table['column_min_widths']);
        }
    }

    public function test_eighteen_independent_diagnostic_decisions_keep_core_visible(): void
    {
        $master = $this->master(); [, $package] = Package::load();
        $expected = [
            [[118,23,1],[109,50,3],[105,27,3],[113,31,3],[133,27,3],[164,44,4]],
            [[114,31,2],[134,26,3],[130,22,2],[148,32,3],[119,30,3],[111,39,2]],
            [[99,22,2],[113,25,2],[134,20,3],[127,32,3],[111,28,3],[201,34,3]],
        ];
        foreach ($package['targets'] as $owner => $target) {
            $dom = $this->dom($master['lessons'][$owner]['body_html']); $sections = []; $section = -1;
            foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
                if ($node->nodeName === 'section') { break; }
                if ($node->nodeName === 'h4') { $sections[++$section] = []; }
                elseif ($node instanceof \DOMElement) { $sections[$section][] = $node; }
            }
            self::assertCount(6, $sections); self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $index => $candidate) {
                $paragraphs = array_values(array_filter($sections[$index], fn ($node) => $node->nodeName === 'p'));
                self::assertSame($this->inner($paragraphs[count($paragraphs) - 1]), $candidate['candidate_html']);
                $text = $this->diagnosticText($candidate['candidate_html']);
                $all = implode(' ', array_map(fn ($node) => $dom->saveHTML($node), $sections[$index]));
                $metrics = [preg_match_all('/\S+/u', $this->diagnosticText($all)) - preg_match_all('/\S+/u', $text),
                    preg_match_all('/\S+/u', $text), preg_match_all('/[.!?](?:\s|$)/u', $text)];
                self::assertSame($expected[$owner][$index], $metrics);
                self::assertSame($metrics, [$candidate['basic_word_count'], $candidate['detail_word_count'], $candidate['detail_sentence_count']]);
                self::assertSame($index + 1, $candidate['source_section']);
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                self::assertStringContainsString($this->plain($candidate['candidate_html']), $this->plain($this->core($target)));
            }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); } }
        }
    }

    public static function semanticChanges(): array
    {
        return [
            'proposal-not-completed' => [0, 'The team has proposed an expansion of the hall.', 'The team has completed an expansion of the hall.'],
            'may-not-will' => [0, 'may take place next month', 'will take place next month'],
            'planned-not-completed' => [0, 'is scheduled for May', 'was completed in May'],
            'began-not-completed' => [0, 'began in May', 'was completed in May'],
            'available-not-correct' => [0, 'is available to staff', 'is correct and approved'],
            'head-not-nearby-plural' => [0, 'The gradual expansion of the reading rooms is scheduled for autumn.', 'The gradual expansion of the reading rooms are scheduled for autumn.'],
            'unknown-cost-not-estimated' => [0, 'has not yet estimated the cost', 'has estimated the cost at 500'],
            'unmeasured-not-improved' => [0, 'the new waiting times have not been measured', 'the new waiting times have improved'],
            'nominalisation-not-positive-result' => [0, 'The team plans a reduction in waiting times.', 'The team’s reduction in waiting times has improved the service.'],
            'present-not-past-result' => [1, 'I would understand these diagrams now', 'I would have understood these diagrams yesterday'],
            'would-not-could' => [1, 'I would understand these diagrams now', 'I could understand these diagrams now'],
            'would-not-might' => [1, 'I would understand these diagrams now', 'I might understand these diagrams now'],
            'perfect-passive-not-lost' => [1, 'Rarely have the original documents been displayed outside the archive.', 'Rarely have the original documents displayed outside the archive.'],
            'rarely-not-never' => [1, 'Rarely have the original documents been displayed', 'Never have the original documents been displayed'],
            'subordinate-not-inverted' => [1, 'Only after the editor had checked the dates did we publish the notice.', 'Only after had the editor checked the dates did we publish the notice.'],
            'passive-not-active' => [1, 'The notice is believed to have been revised before publication.', 'The notice is believed to have revised before publication.'],
            'believed-not-known' => [1, 'The notice is believed to have been revised', 'The notice is known to have been revised'],
            'defining-not-decorative-commas' => [1, 'The two volunteers who have received training are using the scanner.', 'The two volunteers, who have received training, are using the scanner.'],
            'who-not-duplicate-subject' => [1, 'The two volunteers who have received training', 'The two volunteers who they have received training'],
            'inference-not-fact' => [1, 'Someone may have left it outside overnight.', 'Someone left it outside overnight.'],
            'request-not-past-possibility' => [1, 'Could you send the final file by Friday?', 'You could have sent the final file by Friday.'],
            'unchecked-not-correct' => [1, 'The descriptions have not yet been checked.', 'Every description is certainly correct.'],
            'untested-not-failed' => [2, 'The second prototype has not been tested.', 'The second prototype has failed the test.'],
            'might-not-would' => [2, 'we might have taken the wrong path', 'we would have taken the wrong path'],
            'could-not-would' => [2, 'we could have been stranded underground', 'we would have been stranded underground'],
            'hypothetical-not-actual' => [2, 'we might have taken the wrong path', 'we took the wrong path'],
            'thought-not-confirmed' => [2, 'The files are thought to have been copied', 'The files are confirmed to have been copied'],
            'negation-scope-not-moved' => [2, 'It has not been confirmed that the switch failed.', 'It has been confirmed that the switch did not fail.'],
            'didnt-need-not-nonperformance' => [2, 'Marta didn’t need to print a second timetable.', 'Marta didn’t print a second timetable.'],
            'didnt-need-not-performance' => [2, 'Marta didn’t need to print a second timetable.', 'Marta printed a second timetable.'],
            'no-necessity-not-prohibition' => [2, 'Marta didn’t need to print a second timetable.', 'Marta was forbidden to print a second timetable.'],
            'not-all-not-none' => [2, 'Not all five samples were usable.', 'None of the five samples was usable.'],
            'not-all-not-exactly-one' => [2, 'Not all five samples were usable.', 'Exactly one of the five samples was unusable.'],
            'incomplete-assessment-not-failed' => [2, 'The preliminary assessment of both prototypes is incomplete.', 'Both prototypes have failed.'],
            'untested-not-defective' => [2, 'The second prototype remains untested.', 'The second prototype is defective.'],
            'indoor-not-outdoor-reliability' => [2, 'Anika’s indoor test of the first prototype on Monday was successful.', 'The first prototype has been proven reliable outdoors.'],
            'first-not-both' => [2, 'Anika tested the first prototype indoors on Monday.', 'Anika tested both prototypes indoors on Monday.'],
            'may-not-guaranteed' => [2, 'the first prototype may also work outdoors', 'the first prototype is guaranteed to work outdoors'],
            'nominal-translation-not-lost' => [0, 'Команда почала впроваджувати переглянутий розклад у травні.', ''],
            'c1-translation-not-lost' => [1, 'Оригінали документів рідко виставляли за межами архіву.', ''],
            'c2-translation-not-lost' => [2, 'Другий прототип не випробовували.', ''],
            'nominal-month-not-changed' => [0, 'began in May', 'began in June'],
            'c1-number-not-changed' => [1, 'The two guides who have completed the course', 'The three guides who have completed the course'],
            'c2-date-not-changed' => [2, 'indoors on Monday', 'indoors on Tuesday'],
            'actor-not-changed' => [2, 'Anika tested the first prototype', 'Marta tested the first prototype'],
        ];
    }

    #[DataProvider('semanticChanges')]
    public function test_actual_author_semantic_mutations_are_rejected(int $owner, string $from, string $to): void
    {
        [$before, $package] = Package::load(); $found = false;
        foreach ($package['targets'][$owner]['after']['page']['blocks'] as &$block) {
            if ($block['type'] === 'hero' || !str_contains($block['body'], $from)) { continue; }
            $old = $block['body']; $block['body'] = substr_replace($old, $to, strpos($old, $from), strlen($from));
            json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertNotSame($old, $block['body']); $found = true; break;
        }
        unset($block); self::assertTrue($found, 'Each mutation must change an actual accepted fragment; absent strings are not a negative fixture.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_wrong_detail_owner_duplicate_anchor_lost_case_punctuation_and_order_are_rejected(): void
    {
        foreach (['wrong-detail-owner', 'duplicate-anchor', 'missing-case', 'punctuation', 'order'] as $kind) {
            [$before, $package] = Package::load();
            if ($kind === 'wrong-detail-owner') {
                $package['targets'][0]['plans'][0]['points'][0]['detail'] = $package['targets'][1]['plans'][1]['points'][0]['basic'];
            } elseif ($kind === 'duplicate-anchor') {
                $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            } elseif ($kind === 'missing-case') {
                $data = json_decode($package['targets'][0]['after']['page']['blocks'][7]['body'], true, flags: JSON_THROW_ON_ERROR);
                array_pop($data['inputs']); $package['targets'][0]['after']['page']['blocks'][7]['body'] = Package::json($data);
            } else {
                $data = json_decode($package['targets'][2]['after']['page']['blocks'][1]['body'], true, flags: JSON_THROW_ON_ERROR);
                if ($kind === 'order') { [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]]; }
                else { $old = $data['sections'][0]['description']; $data['sections'][0]['description'] = str_replace('конструкцію, а', 'конструкцію; а', $old); self::assertNotSame($old, $data['sections'][0]['description']); }
                $package['targets'][2]['after']['page']['blocks'][1]['body'] = Package::json($data);
            }
            try { Package::validate($before, $package); self::fail('Invalid ownership/content mutation accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
