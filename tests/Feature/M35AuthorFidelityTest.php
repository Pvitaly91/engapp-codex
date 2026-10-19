<?php

namespace Tests\Feature;

use App\Support\M35PassiveReportingPackage as Package;
use DOMDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class M35AuthorFidelityTest extends TestCase
{
    private function plain(string $html): string
    {
        return preg_replace('/\s+/u', ' ', trim(html_entity_decode(preg_replace('/<[^>]+>/', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function projectedCore(array $target): string
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
            else { throw new RuntimeException('Unexpected teaching type.'); }
        }
        return implode(' ', $parts);
    }

    public function test_full_ordered_author_text_internal_punctuation_translations_and_links_are_exact(): void
    {
        [$before, $package] = Package::load();
        foreach ($package['targets'] as $i => $target) {
            $old = preg_replace('~<section id="self-check-[^"]+">[\s\S]*?</section>~', '', $before['targets'][$i]['before']['page']['blocks'][1]['body']);
            $new = $this->projectedCore($target);
            self::assertSame($this->plain($old), $this->plain($new), 'Every core word, example, translation, role, temporal relation and punctuation in original order.');
            preg_match_all('~href="([^"]+)"~', $old, $oldLinks); preg_match_all('~href="([^"]+)"~', $new, $newLinks);
            self::assertSame($oldLinks[1], $newLinks[1]);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$old); libxml_clear_errors();
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($b) => $b['type'] === 'comparison-table'));
            self::assertCount(1, $tables); $table = json_decode($tables[0]['body'], true);
            self::assertCount($i === 0 ? 4 : 3, $table['headers']); self::assertCount(4, $table['rows']);
            $inner = static function ($n) { $out = ''; foreach ($n->childNodes as $c) { $out .= $n->ownerDocument->saveHTML($c); } return trim($out); };
            self::assertSame(array_map(fn ($n) => trim($n->textContent), iterator_to_array($dom->getElementsByTagName('th'))), $table['headers']);
            foreach ($dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $j => $row) {
                self::assertSame(array_map($inner, iterator_to_array($row->getElementsByTagName('td'))), $table['rows'][$j]['cells']);
            }
            self::assertSame(1000, $table['table_min_width']);
            self::assertSame([[250, 280, 210, 260], [250, 370, 300], [250, 380, 300]][$i], $table['column_min_widths']);
        }
    }

    public function test_audit_metrics_are_diagnostic_and_independently_recomputed(): void
    {
        [, $package] = Package::load();
        foreach ($package['targets'] as $target) {
            foreach ($target['detail_quality_audit'] as $candidate) {
                $html = preg_replace('~</?(?:br|p|li|ul|ol|div|td|th|tr|table|thead|tbody|h[1-6])\b[^>]*>~i', ' ', $candidate['candidate_html']);
                $text = preg_replace('/\s+/u', ' ', trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                self::assertSame(preg_match_all('/\S+/u', $text), $candidate['detail_words']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $text), $candidate['detail_sentences']);
                self::assertSame('visible_basic', $candidate['decision']);
            }
        }
    }

    public static function semanticChanges(): array
    {
        return [
            'reporting-source-subject-swap' => [0, 'People say that the reading room is quiet.', 'The reading room says that people are quiet.'],
            'belief-strengthened-to-knowledge' => [0, 'the studio was believed to be empty', 'the studio was known to be empty'],
            'report-strengthened-to-proof' => [0, 'The volunteers are reported to be sorting books now.', 'The volunteers have been proved to be sorting books now.'],
            'simultaneous-state-made-earlier' => [0, 'At noon, the studio was believed to be empty.', 'At noon, the studio was believed to have been empty.'],
            'earlier-action-made-simultaneous' => [0, 'At noon, the group was believed to have left at eleven.', 'At noon, the group was believed to leave at eleven.'],
            'process-made-completed' => [0, 'The volunteers are reported to be sorting books now.', 'The volunteers are reported to have sorted books now.'],
            'future-expectation-made-fact' => [0, 'The delivery is expected to arrive tomorrow.', 'The delivery has arrived.'],
            'may-lost' => [0, 'Модальне may не можна просто записати як to may', 'Модальне may можна прибрати'],
            'that-to-frame-mixed' => [0, 'The rooms are believed to be empty.', 'The rooms are believed that empty.'],
            'plural-agreement-broken' => [0, 'The rooms are believed to be empty.', 'The rooms is believed to be empty.'],
            'organiser-made-performer' => [1, 'Організатор — Marta, виконавець — a technician', 'Організатор — a technician, виконавець — Marta'],
            'service-invented-from-passive' => [1, 'Звідси не знаємо, хто працював і чи Марта замовляла послугу.', 'Це доводить, що Марта замовила послугу.'],
            'causative-made-past-perfect' => [1, 'Це Past Simple конструкції have something done', 'Це Past Perfect конструкції have something done'],
            'object-v3-order-broken' => [1, 'Marta had her lamp repaired on Monday.', 'Marta had repaired her lamp on Monday.'],
            'have-person-wrong-to' => [1, 'I had the tailor replace the zip.', 'I had the tailor to replace the zip.'],
            'get-person-missing-to' => [1, 'I got the tailor to replace the zip.', 'I got the tailor replace the zip.'],
            'ongoing-service-made-completed' => [1, 'I am having my lamp repaired now.', 'I have had my lamp repaired.'],
            'plan-made-completed' => [1, 'I am going to have my lamp repaired next Tuesday.', 'I had my lamp repaired last Tuesday.'],
            'unwanted-event-made-arranged' => [1, 'Речення не означає, що він замовив крадіжку', 'Речення означає, що він замовив крадіжку'],
            'get-passive-made-causative' => [1, 'Sign — підмет, який зазнав дії.', 'Sign — організатор послуги.'],
            'impersonal-source-removed' => [2, 'On Tuesday, the administrator reported that a technician had inspected the lift on Monday.', 'A technician had inspected the lift on Monday.'],
            'actor-invented' => [2, 'by a technician on Monday.', 'by the administrator on Monday.'],
            'passive-from-tense-alone' => [2, 'Сам минулий час не вимагає пасивного інфінітива.', 'Сам минулий час вимагає пасивного інфінітива.'],
            'been-state-misclassified' => [2, 'не пасив тільки через слова have been', 'пасив тільки через слова have been'],
            'future-reference-made-past' => [2, 'by noon tomorrow.', 'by noon yesterday.'],
            'negation-scope-swapped' => [2, 'The spare key is not known to have been copied.', 'The spare key is known not to have been copied.'],
            'impersonal-modality-lost' => [2, 'The coordinator reports that the parcel may arrive on Thursday.', 'The coordinator reports that the parcel will arrive on Thursday.'],
            'claim-made-confirmation' => [2, 'не означає, що автор підтверджує заяву', 'означає, що автор підтверджує заяву'],
            'heavy-passive-declared-impossible' => [2, 'можливе <strong>to be being + V3</strong>', 'неможливе <strong>to be being + V3</strong>'],
            'safety-invented' => [2, 'it does not establish whether the lift is safe.', 'it establishes that the lift is safe.'],
            'translation-lost' => [0, 'Опівдні вважали, що студія порожня.', ''],
        ];
    }

    #[DataProvider('semanticChanges')]
    public function test_substantive_semantic_mutations_are_rejected(int $owner, string $from, string $to): void
    {
        [$before, $package] = Package::load(); $found = false;
        foreach ($package['targets'][$owner]['after']['page']['blocks'] as &$block) {
            if (in_array($block['type'], ['hero', 'practice-set'], true)) { continue; }
            if (str_contains($block['body'], $from)) {
                $original = $block['body']; $block['body'] = str_replace($from, $to, $block['body']);
                self::assertNotSame($original, $block['body']); $found = true; break;
            }
        }
        unset($block); self::assertTrue($found, 'Mutation must alter actual accepted educational text, never an absent string.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_neighbour_assignment_duplicate_id_and_missing_case_are_rejected(): void
    {
        foreach (['neighbour', 'duplicate', 'missing-case'] as $kind) {
            [$before, $package] = Package::load();
            if ($kind === 'neighbour') { $package['targets'][0]['plans'][0]['points'][0] = $package['targets'][1]['plans'][0]['points'][0]; }
            elseif ($kind === 'duplicate') { $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key']; }
            else { $data = json_decode($package['targets'][0]['after']['page']['blocks'][7]['body'], true); array_pop($data['inputs']); $package['targets'][0]['after']['page']['blocks'][7]['body'] = Package::json($data); }
            try { Package::validate($before, $package); self::fail('Ownership or case mutation accepted.'); } catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
