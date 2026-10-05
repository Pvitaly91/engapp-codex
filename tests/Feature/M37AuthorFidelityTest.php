<?php

namespace Tests\Feature;

use App\Support\M37GrammarStructuresPackage as Package;
use DOMDocument;
use DOMNode;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent punctuation/order/role-sensitive audit of the three accepted M21 author blobs. */
class M37AuthorFidelityTest extends TestCase
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
            else { throw new RuntimeException('Unexpected M37 teaching type.'); }
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
            self::assertSame($this->plain($old), $this->plain($new), 'Every author word, role, tense, translation, referent, comma and conclusion remains in order.');
            preg_match_all('~href="([^"]+)"~', $old, $oldLinks); preg_match_all('~href="([^"]+)"~', $new, $newLinks);
            self::assertSame($oldLinks[1], $newLinks[1]);
            $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$old, LIBXML_NONET); libxml_clear_errors();
            $tables = array_values(array_filter($target['after']['page']['blocks'], fn ($block) => $block['type'] === 'comparison-table'));
            self::assertCount(1, $tables); $table = json_decode($tables[0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertCount(4, $table['headers']); self::assertCount(4, $table['rows']);
            self::assertSame(array_map(fn ($node) => trim($node->textContent), iterator_to_array($dom->getElementsByTagName('th'))), $table['headers']);
            foreach ($dom->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr') as $rowIndex => $row) {
                self::assertSame(array_map($this->inner(...), iterator_to_array($row->getElementsByTagName('td'))), $table['rows'][$rowIndex]['cells']);
            }
            self::assertSame(1000, $table['table_min_width']); self::assertSame([250, 250, 250, 250], $table['column_min_widths']);
        }
    }

    public function test_diagnostic_metrics_are_exact_but_do_not_choose_hidden_details(): void
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
            self::assertCount(6, $sections); self::assertCount(6, $target['detail_quality_audit']);
            foreach ($target['detail_quality_audit'] as $index => $candidate) {
                $text = $this->diagnosticText($candidate['candidate_html']);
                self::assertSame(preg_match_all('/\S+/u', $text), $candidate['detail_word_count']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $text), $candidate['detail_sentence_count']);
                self::assertSame(preg_match_all('/\S+/u', $this->diagnosticText($sections[$index])) - $candidate['detail_word_count'], $candidate['basic_word_count']);
                self::assertSame($index + 1, $candidate['source_section']);
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                self::assertStringContainsString($this->plain($candidate['candidate_html']), $this->plain($this->core($target)));
            }
            foreach ($target['plans'] as $plan) { foreach ($plan['points'] as $point) { self::assertSame('', $point['detail']); } }
        }
    }

    public function test_five_accepted_gerund_checklist_items_are_not_rewritten_into_six(): void
    {
        [$before, $package] = Package::load();
        $source = $before['targets'][0]['before']['page']['blocks'][1]['body'];
        preg_match('~<h4>6\. Як перевірити свій вибір</h4>([\s\S]*?)<section~', $source, $section);
        $dom = new DOMDocument; libxml_use_internal_errors(true); $dom->loadHTML('<meta charset="UTF-8">'.$section[1], LIBXML_NONET); libxml_clear_errors();
        self::assertSame(5, $dom->getElementsByTagName('li')->length);
        $data = json_decode($package['targets'][0]['after']['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($package['targets'][0]['plans'][5]['source_section'], 6);
        self::assertCount(6, $data['sections'], 'Five exact checklist li plus its existing correction paragraph, not an invented sixth li.');
        self::assertStringContainsString('Для stop назви припинену дію; для try відділи мету від перевірки способу.', $data['sections'][3]['description']);
    }

    public static function semanticChanges(): array
    {
        return [
            'decide-is-not-completion' => [0, 'Рішення не означає, що друк уже відбувся.', 'Рішення означає, що друк уже відбувся.'],
            'ing-is-not-automatically-past' => [0, '-ing не дорівнює минулому', '-ing завжди дорівнює минулому'],
            'infinitive-is-not-automatically-future' => [0, 'to-infinitive — майбутньому', 'to-infinitive завжди позначає майбутнє'],
            'forward-preposition-not-infinitive-marker' => [0, 'I look forward to visiting the workshop.', 'I look forward to visit the workshop.'],
            'remember-memory-not-reminder' => [0, 'I remember sealing the envelope yesterday.', 'I remember to seal the envelope yesterday.'],
            'completed-obligation-not-unperformed-reminder' => [0, 'I remembered to return the tripod yesterday.', 'I remembered returning the tripod yesterday.'],
            'stop-activity-not-purpose' => [0, 'Oleh stopped sanding the door.', 'Oleh stopped to sand the door.'],
            'stop-purpose-not-activity' => [0, 'Oleh stopped to answer a call.', 'Oleh stopped answering a call.'],
            'try-effort-not-failure' => [0, 'Успіх або невдача не задані.', 'Спроба завжди завершилася невдачею.'],
            'try-method-not-success' => [0, 'Це пропозиція перевірити спосіб, не гарантія результату.', 'Це гарантований успішний результат.'],
            'dialogue-does-not-prove-result' => [0, 'Діалог не каже, чи регулювання вдалося і чи порада з фетром допоможе.', 'Регулювання вдалося і порада з фетром точно допомогла.'],
            'adjective-pattern-not-universal' => [0, 'Не поширюй одну з цих схем на будь-який прикметник.', 'Поширюй ready to do на будь-який прикметник.'],
            'whose-is-not-human-only' => [1, 'Не обмежуй whose людьми', 'Обмежуй whose тільки людьми'],
            'possessive-whose-not-whos' => [1, 'a potter whose kiln runs', 'a potter who’s kiln runs'],
            'cooperative-rents-not-owns' => [1, 'rents this field.', 'owns this field.'],
            'work-with-retains-preposition' => [1, 'The adviser with whom we worked', 'The adviser about whom we worked'],
            'rely-on-retains-preposition' => [1, 'The checklist on which we relied', 'The checklist for which we relied'],
            'no-that-after-fronted-preposition' => [1, 'whom/which, не that', 'that, не whom/which'],
            'simple-defining-subject-cannot-be-omitted' => [1, 'The artist who painted this screen is here.', 'The artist painted this screen is here.'],
            'non-defining-pronoun-cannot-be-omitted' => [1, 'This screen, which we bought yesterday, is damaged.', 'This screen, we bought yesterday, is damaged.'],
            'nested-subject-not-adjacent-object' => [1, 'The designer who we believe can restore the mural is available.', 'The designer whom we believe can restore the mural is available.'],
            'defining-commas-not-swappable' => [1, 'The panels which have faded will be replaced.', 'The panels, which have faded, will be replaced.'],
            'two-of-six-not-all-six' => [1, 'дві фотографії з шести', 'усі шість фотографій'],
            'which-referent-not-caretaker' => [1, 'Тут which відсилає до факту раннього прибуття', 'Тут which відсилає до доглядача'],
            'no-duplicate-subject' => [1, 'The restorer who we believe can repair the frame is away.', 'The restorer who she we believe can repair the frame is away.'],
            'no-completed-repair-invented' => [1, 'whose three presses need servicing has hired Iva', 'whose three presses have been repaired has hired Iva'],
            'first-auxiliary-and-aspect-preserved' => [2, 'Seldom has the archive been open after sunset.', 'Seldom did the archive open after sunset.'],
            'rarely-not-never' => [2, 'Rarely do staff delete original files.', 'Never do staff delete original files.'],
            'present-perfect-passive-not-simple-past' => [2, 'Rarely have the samples been stored outside the cold room.', 'Rarely were the samples stored outside the cold room.'],
            'sequence-not-causality' => [2, 'не доводять, що показ спричинив збій', 'доводять, що показ спричинив збій'],
            'distant-events-not-immediate' => [2, 'За проміжку в три дні така модель спотворила б близькість подій', 'За проміжку в три дні така модель точно передає близькість подій'],
            'no-sooner-than-not-when' => [2, 'No sooner had the projection started than the power failed.', 'No sooner had the projection started when the power failed.'],
            'only-after-subordinate-not-inverted' => [2, 'Only after the technician had checked the wiring could the room be reopened.', 'Only after had the technician checked the wiring could the room be reopened.'],
            'only-after-passive-not-active' => [2, 'could the room be reopened', 'could the technician reopen the room'],
            'prohibition-not-past-fact' => [2, 'Under no circumstances may staff delete the original file.', 'At no time did staff delete the original file.'],
            'no-extra-negative' => [2, 'Under no circumstances may visitors remove the seals.', 'Under no circumstances may visitors not remove the seals.'],
            'only-subject-not-inverted' => [2, 'Only the coordinator had received the final schedule.', 'Only had the coordinator received the final schedule.'],
            'not-only-subject-coordination-not-inverted' => [2, 'Not only the illustrator but also the editor signed the copies.', 'Not only did the illustrator but also the editor sign the copies.'],
            'no-neutral-emphatic-do' => [2, 'The visitors rarely speak loudly in this hall.', 'The visitors rarely do speak loudly in this hall.'],
            'passive-no-invented-actor' => [2, 'Scarcely had the shutters been raised when the first customers appeared.', 'Scarcely had the caretaker raised the shutters when the first customers appeared.'],
            'translation-not-lost' => [1, 'Ми зустріли гончарку, чия піч працює на електриці.', ''],
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
        unset($block); self::assertTrue($found, 'Mutation must change actual accepted content, not an absent string.');
        $this->expectException(RuntimeException::class); Package::validate($before, $package);
    }

    public function test_wrong_point_duplicate_anchor_missing_case_punctuation_and_order_fail_closed(): void
    {
        foreach (['wrong-point', 'duplicate', 'missing-case', 'punctuation', 'order'] as $kind) {
            [$before, $package] = Package::load();
            if ($kind === 'wrong-point') {
                $package['targets'][0]['plans'][0]['points'][0]['detail'] = $package['targets'][1]['plans'][0]['points'][0]['basic'];
            } elseif ($kind === 'duplicate') {
                $package['targets'][0]['plans'][1]['key'] = $package['targets'][0]['plans'][0]['key'];
            } elseif ($kind === 'missing-case') {
                $data = json_decode($package['targets'][0]['after']['page']['blocks'][7]['body'], true); array_pop($data['inputs']);
                $package['targets'][0]['after']['page']['blocks'][7]['body'] = Package::json($data);
            } else {
                $data = json_decode($package['targets'][1]['after']['page']['blocks'][4]['body'], true);
                if ($kind === 'order') { [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]]; }
                else {
                    $old = Package::json($data); $mutated = str_replace('The panels, which have faded, will be replaced.', 'The panels which have faded will be replaced.', $old);
                    self::assertNotSame($old, $mutated); $data = json_decode($mutated, true, flags: JSON_THROW_ON_ERROR);
                }
                $package['targets'][1]['after']['page']['blocks'][4]['body'] = Package::json($data);
            }
            try { Package::validate($before, $package); self::fail('Ownership/content mutation accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
