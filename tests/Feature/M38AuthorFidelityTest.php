<?php

namespace Tests\Feature;

use App\Support\M38ArticlesCollocationsPackage as Package;
use DOMDocument;
use DOMNode;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent punctuation/order/role-sensitive audit of the three accepted M22 author blobs. */
class M38AuthorFidelityTest extends TestCase
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
            else { throw new RuntimeException('Unexpected M38 teaching type.'); }
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
            self::assertSame($this->plain($old), $this->plain($new), 'Every author word, countability, quantity, group, referent, negation, role, modal force, translation and punctuation remains in order.');
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

    public function test_independent_exact_diagnostic_inventory_cannot_be_silently_rebased(): void
    {
        [, $package] = Package::load();
        $expected = [
            [[109,21,2],[149,24,2],[118,24,4],[116,16,1],[70,26,3],[55,25,2]],
            [[125,22,2],[118,33,3],[81,56,5],[164,52,3],[80,29,3],[66,38,3]],
            [[55,51,3],[142,37,3],[140,39,4],[196,28,3],[100,25,2],[108,43,3]],
        ];
        foreach ($package['targets'] as $owner => $target) {
            $metrics = array_map(fn ($candidate) => [$candidate['basic_word_count'], $candidate['detail_word_count'], $candidate['detail_sentence_count']], $target['detail_quality_audit']);
            self::assertSame($expected[$owner], $metrics);
        }
    }

    public static function semanticChanges(): array
    {
        return [
            'research-not-countable-by-a' => [0, 'Артикль a сам не перетворює research', 'Артикль a сам перетворює research'],
            'three-informations-not-target' => [0, 'three pieces of information and some useful research', 'three informations and some useful research'],
            'evidence-not-evidences' => [0, 'a piece of evidence', 'a piece of evidences'],
            'few-not-guaranteed-enough' => [0, 'Кілька є; чи вистачить — окреме питання.', 'Кілька є; їх гарантовано вистачить.'],
            'few-not-zero' => [0, 'Few labels remain.', 'No labels remain.'],
            'little-not-none' => [0, 'There is little ink left.', 'There is no ink left.'],
            'few-not-fixed-three-to-five' => [0, 'не фіксований діапазон', 'фіксований діапазон'],
            'several-not-exact-three' => [0, 'Several указує на кілька одиниць без точного числа.', 'Several указує рівно на три одиниці.'],
            'first-mention-not-hard-rule' => [0, 'the можливе одразу, якщо об’єкт спільно відомий.', 'the можливе тільки після попередньої згадки.'],
            'known-gate-not-unknown' => [0, 'Please close the gate.', 'Please close a gate.'],
            'hospital-patient-not-universally-wrong' => [0, 'Це не твердження, що in the hospital ніколи не стосується пацієнта.', 'In the hospital ніколи не стосується пацієнта.'],
            'a-number-plural-agreement' => [0, 'A number of labels are missing.', 'A number of labels is missing.'],
            'the-number-singular-agreement' => [0, 'The number of missing labels is not known.', 'The number of missing labels are not known.'],
            'little-evidence-not-no-evidence' => [0, 'There is not much evidence for this explanation.', 'There is no evidence for this explanation.'],
            'little-evidence-not-false-explanation' => [0, 'There is not much evidence for this explanation.', 'The explanation is false.'],
            'presence-not-sufficiency' => [0, 'We have a little ink, but not enough to print every label.', 'We have a little ink, enough to print every label.'],
            'generic-not-always-zero' => [1, 'Вибір не зводиться до заборони a у загальному значенні.', 'A завжди заборонене у загальному значенні.'],
            'identified-not-previous-mention-only' => [1, 'навіть без попереднього речення', 'тільки за попереднього речення'],
            'red-subgroup-not-all-four' => [1, 'The red-labelled folders were empty.', 'All four folders were empty.'],
            'some-not-logically-not-all' => [1, 'це не автоматичне логічне твердження про решту', 'це автоматичне логічне твердження про решту'],
            'any-not-negative-only' => [1, 'You can use any of these three desks.', 'Any можливе тільки в запереченні або питанні.'],
            'every-of-not-target' => [1, 'Every one of the three drawers has a label.', 'Every of the three drawers has a label.'],
            'each-of-requires-defined-group' => [1, 'each of the drawers', 'each of drawers'],
            'both-plural-agreement' => [1, 'Both of the two keys are on the desk.', 'Both of the two keys is on the desk.'],
            'formal-either-singular' => [1, 'Either of the two layouts is suitable.', 'Either of the two layouts are suitable.'],
            'formal-neither-singular' => [1, 'Neither of them is signed.', 'Neither of them are signed.'],
            'not-all-not-none' => [1, 'Not all six drawers are locked.', 'None of the six drawers is locked.'],
            'not-both-not-neither' => [1, 'Not both lamps work.', 'Neither lamp works.'],
            'untested-second-state-not-known' => [1, 'другу ще не перевірили', 'друга точно працює'],
            'not-both-not-exactly-one' => [1, '«Працює рівно одна» також не підтверджено', '«Працює рівно одна» підтверджено'],
            'dates-not-complete-records' => [1, 'Both of the checked cards had dates.', 'Both of the checked cards were complete.'],
            'two-checked-not-all-six' => [1, 'Two of the six folders were checked.', 'All six folders were checked.'],
            'three-group-not-two' => [1, 'Each of the three drawers has a label.', 'Each of the two drawers has a label.'],
            'pose-participant-role' => [2, 'The low doorway poses a challenge to the movers.', 'The movers pose a challenge to the low doorway.'],
            'face-participant-role' => [2, 'The movers face a challenge in carrying the cabinet upstairs.', 'The cabinet faces a challenge in carrying the movers upstairs.'],
            'challenge-not-impossibility' => [2, 'Не означає, що шафу неможливо підняти.', 'Означає, що шафу неможливо підняти.'],
            'precedent-not-guaranteed-repetition' => [2, 'Це не повідомляє, що повтор уже відбувся чи обов’язково відбудеться.', 'Повтор уже відбувся й обов’язково відбудеться.'],
            'could-not-will' => [2, 'The exception could set a precedent for later requests.', 'The exception will set a precedent for later requests.'],
            'draw-make-not-one-password' => [2, 'Make природне; зберігаємо обидва об’єкти.', 'Make неможливе; дозволене тільки draw.'],
            'set-establish-both-allowed' => [2, 'Establish можливе; could не замінюємо на will.', 'Establish неможливе; дозволене тільки set.'],
            'raise-not-answer-question' => [2, 'The missing label raises a question about the box’s contents.', 'The missing label answers a question about the box’s contents.'],
            'two-questions-not-one' => [2, 'It raises two questions about access, but answers only the first.', 'It raises a question about access, but answers it.'],
            'answer-on-not-target' => [2, 'answer the question, не answer on the question', 'answer on the question, не answer the question'],
            'small-difference-not-substantial-gap' => [2, 'a small difference</em> — невелика різниця — відповідає прямо заданій оцінці', 'a substantial gap</em> — значний розрив — відповідає прямо заданій оцінці'],
            'documentary-not-complete-proof' => [2, 'не гарантує, що вона вичерпна чи безпомилкова', 'гарантує, що вона вичерпна й безпомилкова'],
            'significant-not-statistical' => [2, 'це не автоматично «статистично значущий»', 'це автоматично «статистично значущий»'],
            'one-invoice-not-complete-history' => [2, 'не доводить повної історії речі', 'доводить повну історію речі'],
            'proposed-not-approved' => [2, 'the proposal has not been approved', 'the proposal has been approved'],
            'formal-wording-not-fact-strengthening' => [2, 'Формальні слова самі по собі не дають права написати answers all access questions.', 'Формальні слова дають право написати answers all access questions.'],
            'only-first-not-all-questions' => [2, 'answers only the first', 'answers all questions'],
            'articles-translation-not-lost' => [0, 'В архіві є корисні відомості про канал.', ''],
            'determiner-translation-not-lost' => [1, 'Етикетки біля цих банок вицвіли.', ''],
            'collocation-translation-not-lost' => [2, 'Низькі двері створюють труднощі вантажникам.', ''],
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
                $owner = $kind === 'order' ? 0 : 1;
                $blockIndex = $kind === 'order' ? 1 : 2;
                $data = json_decode($package['targets'][$owner]['after']['page']['blocks'][$blockIndex]['body'], true);
                if ($kind === 'order') { [$data['sections'][0], $data['sections'][1]] = [$data['sections'][1], $data['sections'][0]]; }
                else {
                    $old = Package::json($data); $mutated = str_replace('Two folders had red labels; the other two had blue labels.', 'Two folders had red labels, the other two had blue labels.', $old);
                    self::assertNotSame($old, $mutated); $data = json_decode($mutated, true, flags: JSON_THROW_ON_ERROR);
                }
                $package['targets'][$owner]['after']['page']['blocks'][$blockIndex]['body'] = Package::json($data);
            }
            try { Package::validate($before, $package); self::fail('Ownership/content mutation accepted.'); }
            catch (RuntimeException) { self::assertTrue(true); }
        }
    }
}
