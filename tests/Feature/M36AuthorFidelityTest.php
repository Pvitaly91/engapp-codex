<?php

namespace Tests\Feature;

use App\Support\M36ModalsSubjunctivePackage as Package;
use DOMDocument;
use DOMNode;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent punctuation/order-sensitive audit against the three exact accepted M20 blobs. */
class M36AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function inner(DOMNode $node): string
    {
        $html = ''; foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); } return trim($html);
    }

    private function core(array $target): string
    {
        $parts = [];
        foreach (array_slice($target['after']['page']['blocks'], 1) as $block) {
            $data = json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            if ($block['type'] === 'practice-set') { continue; }
            $parts[] = $data['title'];
            if ($block['type'] === 'usage-panels') { foreach ($data['sections'] as $point) { $parts[] = $point['description']; } }
            elseif ($block['type'] === 'comparison-table') {
                $parts[] = $data['intro']; array_push($parts, ...$data['headers']);
                foreach ($data['rows'] as $row) { array_push($parts, ...$row['cells']); } $parts[] = $data['outro'];
            } elseif ($block['type'] === 'summary-list') { array_push($parts, ...$data['items']); }
            else { throw new RuntimeException('Unexpected M36 teaching type.'); }
        }
        return implode(' ', $parts);
    }

    private function diagnosticText(string $html): string
    {
        $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $html);
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    public function test_full_ordered_author_text_punctuation_translations_tables_and_links_are_exact(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $old = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $before['targets'][$i]['before']['page']['blocks'][1]['body']);
            $new = $this->core($target);
            self::assertSame($this->plain($old), $this->plain($new), 'Every core word, modality, role, translation, time and punctuation in author order.');
            preg_match_all('~href="([^"]+)"~', $old, $oldLinks); preg_match_all('~href="([^"]+)"~', $new, $newLinks);
            self::assertSame($oldLinks[1], $newLinks[1]);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$old, LIBXML_NONET); libxml_clear_errors();
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'comparison-table'));
            self::assertCount(1, $tables); $table = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertCount(4, $table['headers']); self::assertCount(4, $table['rows']);
            self::assertSame(array_map(fn ($n) => trim($n->textContent), iterator_to_array($dom->getElementsByTagName('th'))), $table['headers']);
            foreach ($dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $j => $row) {
                self::assertSame(array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td'))), $table['rows'][$j]['cells']);
            }
            self::assertSame(1000, $table['table_min_width']); self::assertSame([250, 250, 250, 250], $table['column_min_widths']);
        }
    }

    public function test_diagnostic_metrics_do_not_select_details_and_candidates_remain_visible(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $owner => $target) {
            $dom = new DOMDocument; libxml_use_internal_errors(true);
            $dom->loadHTML('<meta charset="UTF-8">'.$before['targets'][$owner]['before']['page']['blocks'][1]['body'], LIBXML_NONET);
            libxml_clear_errors(); $sections = []; $section = -1;
            foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
                if ($node->nodeName === 'section') { break; }
                if ($node->nodeName === 'h4') { $sections[++$section] = ''; }
                elseif ($node instanceof \DOMElement) { $sections[$section] .= ' '.$dom->saveHTML($node); }
            }
            self::assertCount(6, $sections);
            self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $index => $candidate) {
                $text = $this->diagnosticText($candidate['candidate_html']);
                self::assertSame(preg_match_all('/\S+/u', $text), $candidate['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $text), $candidate['detail_sentences']);
                self::assertSame(preg_match_all('/\S+/u', $this->diagnosticText($sections[$index])) - $candidate['detail_words'], $candidate['basic_words']);
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
            'must-deduction-made-proven-fact' => [0, 'Сила його переконання не дорівнює незалежно встановленому факту.', 'Сила його переконання дорівнює незалежно встановленому факту.'],
            'might-strengthened-to-must' => [0, 'I might have left it in the other bag.', 'I must have left it in the other bag.'],
            'may-not-conflated-with-cannot' => [0, 'Marta may not have read the updated timetable.', 'Marta cannot have read the updated timetable.'],
            'could-always-unused-opportunity' => [0, 'Це можлива подія з невідомим результатом.', 'Це завжди невикористана можливість з відомим невиконанням.'],
            'should-always-nonperformance' => [0, 'Це очікування, а не доказ збою чи докір.', 'Це доказ того, що дію не виконано.'],
            'parcel-known-failed-delivery' => [0, 'Чи посилка фактично прибула.', 'Посилка точно не прибула.'],
            'might-of-accepted' => [0, 'might’ve = might have', 'might’ve = might of'],
            'have-past-simple-accepted' => [0, 'go → gone', 'go → went'],
            'do-modal-negation-accepted' => [0, 'cannot have seen', 'do not can have seen'],
            'possible-cancellation-made-confirmed' => [0, 'The rehearsal may have been cancelled.', 'The rehearsal was cancelled.'],
            'request-made-completed-fact' => [1, 'Речення не стверджує, що план уже надіслано.', 'Речення стверджує, що план уже надіслано.'],
            'mandative-send-made-sends' => [1, 'The coordinator requests that Lina send the outline today.', 'The coordinator requests that Lina sends the outline today.'],
            'requested-be-made-is' => [1, 'every volunteer be ready at ten.', 'every volunteer is ready at ten.'],
            'negative-base-made-doesnt' => [1, 'that she not remove', 'that she doesn’t remove'],
            'passive-base-made-are' => [1, 'that the guests be informed by the secretary by six.', 'that the guests are informed by the secretary by six.'],
            'past-request-backshifted' => [1, 'Yesterday, Ada recommended that Ben revise the outline.', 'Yesterday, Ada recommended that Ben revised the outline.'],
            'suggest-evidence-made-mandative' => [1, 'The uneven spacing suggests that the file is an older draft.', 'The uneven spacing suggests that the file be an older draft.'],
            'insist-assertion-made-demand' => [1, 'Nina insists that the team has kept the original title.', 'Nina insists that the team keep the original title.'],
            'regional-tendency-made-absolute' => [1, 'Обидві моделі існують по обидва боки Атлантики: це тенденція, не кордон правильної англійської.', 'Bare subjunctive існує лише в AmE, should лише в BrE.'],
            'lest-mechanical-not' => [1, 'lest a reader confuse their order.', 'lest a reader not confuse their order.'],
            'request-as-proof-of-compliance' => [1, 'Прохання не доводить повідомлення.', 'Прохання доводить повідомлення.'],
            'may-well-made-as-well' => [2, 'The loose plug may well explain the intermittent sound.', 'The loose plug may as well explain the intermittent sound.'],
            'might-as-well-made-well' => [2, 'We might as well use the courtyard.', 'We might well use the courtyard.'],
            'might-just-made-guarantee' => [2, 'our model might just be ready for the display.', 'our model will certainly be ready for the display.'],
            'worth-to-infinitive' => [2, 'It may be worth checking the labels before printing.', 'It may be worth to check the labels before printing.'],
            'wise-ing' => [2, 'You would be wise to check the labels before printing;', 'You would be wise checking the labels before printing;'],
            'advice-made-obligation' => [2, 'You might want to check the labels before printing.', 'You must check the labels before printing.'],
            'didnt-need-automatically-not-done' => [2, 'Без контексту невідоме.', 'Завжди не виконано.'],
            'neednt-without-known-performance' => [2, 'I didn’t need to copy the schedule.', 'I needn’t have copied the schedule.'],
            'no-obligation-made-prohibition' => [2, 'Він теж не є забороною.', 'Він означає заборону.'],
            'possible-clips-made-definite-cause' => [2, 'Missing clips could well explain today’s delay.', 'Missing clips definitely explain today’s delay.'],
            'translation-lost' => [1, 'Координатор просить, щоб Ліна надіслала план сьогодні.', ''],
            'participant-changed' => [2, 'Ben: It may be worth comparing it with the latest version.', 'Jo: It may be worth comparing it with the latest version.'],
        ];
    }

    #[DataProvider('semanticChanges')]
    public function test_substantive_semantic_mutations_of_actual_author_fragments_are_rejected(int $owner, string $from, string $to): void
    {
        [$before, $package] = Package::load(); $found = false;
        foreach ($package['targets'][$owner]['after']['page']['blocks'] as &$block) {
            if ($block['type'] === 'hero' || !str_contains($block['body'], $from)) { continue; }
            $old = $block['body']; $block['body'] = substr_replace($old, $to, strpos($old, $from), strlen($from));
            json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertNotSame($old, $block['body']); $found = true; break;
        }
        unset($block); self::assertTrue($found, 'Mutation must change actual accepted content, not an absent string.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_neighbour_point_duplicate_anchor_lost_case_and_internal_punctuation_fail_closed(): void
    {
        foreach (['neighbour', 'duplicate', 'missing-case', 'punctuation', 'order'] as $kind) {
            [$before, $package] = Package::load();
            if ($kind === 'neighbour') { $package['targets'][0]['plans'][0]['points'][0] = $package['targets'][1]['plans'][0]['points'][0]; }
            elseif ($kind === 'duplicate') { $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key']; }
            elseif ($kind === 'missing-case') {
                $data = json_decode($package['targets'][0]['after']['page']['blocks'][7]['body'], true); array_pop($data['inputs']);
                $package['targets'][0]['after']['page']['blocks'][7]['body'] = Package::json($data);
            } else {
                $data = json_decode($package['targets'][0]['after']['page']['blocks'][2]['body'], true);
                if ($kind === 'order') { [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]]; }
                else {
                    $old = $data['sections'][1]['description'];
                    $data['sections'][1]['description'] = str_replace('The paper is still warm. Someone', 'The paper is still warm, Someone', $old);
                    self::assertNotSame($old, $data['sections'][1]['description']);
                }
                $package['targets'][0]['after']['page']['blocks'][2]['body'] = Package::json($data);
            }
            try { Package::validate($before, $package); self::fail('Ownership/content mutation accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
