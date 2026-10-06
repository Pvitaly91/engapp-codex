<?php

namespace Tests\Feature;

use App\Support\M40TensesB1Package as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Independent audit of immutable M24 native and appended author material. */
class M40AuthorFidelityTest extends TestCase
{
    private function master(): array
    {
        $raw = file_get_contents(base_path('docs/content/m24-authored-content.v1.json'));
        $lf = str_replace("\r\n", "\n", $raw);
        self::assertSame('33373412ed077bf7fa0aea8dde8a6d2c211c126288b6cedfdde299ded43bf5a2', hash('sha256', $lf));
        self::assertSame('2d7c450533795bdab3267bd029b54fb325ff7cbb', sha1('blob '.strlen($lf)."\0".$lf));
        return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    }

    private function plain(string $html): string
    {
        $html = preg_replace('~</?(?:p|li|ul|ol|div|td|th|tr|table|thead|tbody|br|h[1-6])\b[^>]*>~i', ' ', $html);
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function learnerValues(mixed $value, bool $root = false): string
    {
        if (is_string($value)) { return $value; }
        if (!is_array($value)) { return ''; }
        $parts = [];
        foreach ($value as $key => $child) {
            if (in_array($key, ['color', 'url', 'href', 'current', 'level', 'icon'], true) || ($root && $key === 'title')) { continue; }
            $parts[] = $this->learnerValues($child);
        }
        return implode(' ', $parts);
    }

    private function dom(string $html): DOMXPath
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="UTF-8">'.$html, LIBXML_NONET); libxml_clear_errors();
        return new DOMXPath($dom);
    }

    private function inner($node): string
    {
        $out = ''; foreach ($node->childNodes as $child) { $out .= $node->ownerDocument->saveHTML($child); }
        return trim($out);
    }

    public function test_immutable_master_notes_snapshot_and_accepted_definition_sources_are_exact(): void
    {
        $master = $this->master(); [$before, $projection] = Package::load();
        self::assertSame('d92d759a5c950e0f9eabd2f0fa95557327f42005', $before['base_sha']);
        self::assertSame($master, json_decode($before['author_master_source']['git_lf_bytes'], true, flags: JSON_THROW_ON_ERROR));
        self::assertSame('1f43fa07a98cbc4144abbc02ea3909d8d2d8d04589f25d225a380a256db42e4d', $before['author_master_source']['working_raw_sha256']);
        $notes = str_replace("\r\n", "\n", file_get_contents(base_path('docs/content/m24-author-sources.md')));
        self::assertSame('3330b22b22e401eec9c36275651e60645b4b87f6', sha1('blob '.strlen($notes)."\0".$notes));
        self::assertSame('719e9528112a4ed7a581f8aff8a1e3d1dae56d76d9e8b4b95b295be6bb4d6f5a', $before['author_notes_source']['working_raw_sha256']);
        foreach (['rewrite', 'summarise', 'translate_again', 'add_examples', 'add_exercises', 'production_write', 'mixed_question_bank_write'] as $flag) { self::assertFalse($master['content_policy'][$flag]); }
        $blobs = ['e4a950880b6f4fc0e1b865edc886c658805ffd3c', '55317a6a7bb48a640e804ec613afb83bdca05373', '8b6422baea0cf0c9f4ec1ca1ed921b9fab91c53d'];
        foreach ($master['lessons'] as $i => $lesson) {
            $old = $before['targets'][$i]['before']; $new = $projection['targets'][$i]['after'];
            $bytes = json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            self::assertSame($blobs[$i], sha1('blob '.strlen($bytes)."\0".$bytes));
            self::assertSame($lesson['subtitle_html'], $old['page']['subtitle_html']); self::assertSame($old['page']['subtitle_html'], $new['page']['subtitle_html']);
            self::assertSame($lesson['subtitle_text'], $old['page']['subtitle_text']); self::assertSame($lesson['preserve_page_title'], $new['page']['title']);
            foreach ($lesson['existing_blocks'] as $block) {
                $index = $block['source_index'];
                self::assertSame($block['replacement_body_json'], json_decode($old['page']['blocks'][$index]['body'], true, flags: JSON_THROW_ON_ERROR));
                self::assertSame($old['page']['blocks'][$index], $new['page']['blocks'][$index], 'Full existing native config and raw body bytes remain untouched.');
            }
            self::assertSame($lesson['append_blocks'][0]['body_html'], $old['page']['blocks'][$lesson['baseline_source_block_count']]['body']);
        }
    }

    public function test_exact_two_preliminary_sections_and_all_nested_author_tasks_keys_are_preserved(): void
    {
        $master = $this->master(); [, $projection] = Package::load();
        foreach ($master['lessons'] as $i => $lesson) {
            $xpath = $this->dom($lesson['append_blocks'][0]['body_html']);
            $body = $xpath->query('//body')->item(0); $parts = []; $part = -1;
            foreach ($body->childNodes as $node) {
                if ($node->nodeName === 'section') { break; }
                if ($node->nodeName === 'h4') { $parts[++$part] = ['title' => trim($node->textContent), 'html' => '']; }
                elseif ($node instanceof \DOMElement) { $parts[$part]['html'] .= $node->ownerDocument->saveHTML($node); }
            }
            self::assertCount(2, $parts); $target = $projection['targets'][$i];
            foreach ($parts as $j => $source) {
                $data = json_decode($target['after']['page']['blocks'][$target['native_count'] + $j]['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertSame($source['title'], $data['title']); self::assertSame($this->plain($source['html']), $this->plain($data['intro']));
                self::assertSame([], $data['sections']);
            }
            $selfCheck = $xpath->query('//section')->item(0);
            $practice = json_decode($target['after']['page']['blocks'][$target['native_count'] + 2]['body'], true, flags: JSON_THROW_ON_ERROR);
            $author = $practice['author_self_check'];
            self::assertSame(trim($xpath->query('./h4', $selfCheck)->item(0)->textContent), $practice['title']);
            self::assertSame($selfCheck->getAttribute('id'), $practice['m40_v1']['legacy_practice_id']);
            self::assertSame($this->inner($xpath->query('./p', $selfCheck)->item(0)), $author['intro']);
            foreach (['prompts' => './ol/li', 'answers' => './details/ol/li'] as $field => $query) {
                $items = $xpath->query($query, $selfCheck); self::assertSame(6, $items->length);
                self::assertSame(array_map($this->inner(...), iterator_to_array($items)), $author[$field]);
            }
            self::assertSame('Відповіді та пояснення', $author['title']);
        }
    }

    public function test_all_fifty_two_semantic_candidates_and_exact_diagnostics_keep_basic_complete(): void
    {
        $master = $this->master(); [, $projection] = Package::load();
        $expected = [
            [[151,24,2],[166,9,1],[170,5,1],[166,9,1],[167,8,1],[166,9,1],[167,8,1],[252,27,3],[258,21,2],
                [131,28,3],[120,39,4],[118,41,4],[136,23,2],[131,28,4],[109,23,2],[113,19,2],[109,23,3],[106,26,2],
                [112,20,2],[111,21,2],[68,26,3],[0,26,3]],
            [[159,26,2],[180,5,1],[174,11,1],[179,6,1],[176,9,1],[178,7,1],[177,8,1],[146,6,1],[139,13,1],[143,9,1],
                [226,33,3],[226,33,4],[225,34,4],[226,33,3],[221,38,3],[229,30,2],[221,38,3],[239,20,2],[78,27,4],[0,39,3]],
            [[402,74,10],[414,62,7],[406,70,8],[400,76,9],[402,74,8],[356,120,13],[110,38,3],[118,30,3],[79,39,4],[0,35,4]],
        ];
        $count = 0; $short = 0;
        foreach ($projection['targets'] as $i => $target) {
            self::assertSame($expected[$i], array_map(fn ($candidate) => [$candidate['basic_word_count'], $candidate['detail_word_count'], $candidate['detail_sentence_count']], $target['detail_quality_audit']));
            $paths = [];
            foreach ($target['detail_quality_audit'] as $candidate) {
                $count++; if ($candidate['detail_word_count'] < 30) { $short++; }
                self::assertNotContains($candidate['source_path'], $paths); $paths[] = $candidate['source_path'];
                self::assertSame('visible_basic', $candidate['decision']); self::assertNotEmpty($candidate['reason']);
                $text = $this->plain($candidate['candidate_html']);
                self::assertSame(preg_match_all('/\S+/u', $text), $candidate['detail_word_count']);
                self::assertSame(preg_match_all('/[.!?](?:\s|$)/u', $text), $candidate['detail_sentence_count']);
                self::assertStringContainsString($text, $this->plain($this->learnerValues($master['lessons'][$i]['existing_blocks']).' '.$master['lessons'][$i]['append_blocks'][0]['body_html']));
            }
            foreach ($target['plans'] as $plan) { self::assertSame([], $plan['points']); }
        }
        self::assertSame(52, $count); self::assertSame(32, $short);
    }

    public static function semanticChanges(): array
    {
        return [
            'perfect-four-not-five' => [0, 'Oleh has packed four parcels.', 'Oleh has packed five parcels.'],
            'perfect-forty-not-fifty' => [0, 'parcels for forty minutes', 'parcels for fifty minutes'],
            'perfect-process-not-count' => [0, 'Marta has been packing parcels for forty minutes.', 'Marta has packed all the parcels.'],
            'perfect-know-not-knowing' => [0, 'I have known the password since Monday.', 'I have been knowing the password since Monday.'],
            'perfect-for-not-since' => [0, 'We have been waiting for twenty minutes.', 'We have been waiting since twenty minutes.'],
            'perfect-since-not-for' => [0, 'I have known Danylo since September.', 'I have known Danylo for September.'],
            'perfect-stopped-not-all-ready' => [0, 'She stopped ten minutes ago.', 'All the chairs were ready ten minutes ago.'],
            'perfect-live-not-intention' => [0, 'намір переїхати з цієї форми не випливає', 'намір переїхати з цієї форми випливає'],
            'perfect-second-not-complete' => [0, 'but it is not ready yet', 'and it is ready'],
            'perfect-two-hours-not-three' => [0, 'the second chapter for two hours', 'the second chapter for three hours'],
            'narrative-sequence-not-all-perfect' => [1, 'Yesterday I unlocked the gate, switched on the light and opened the window.', 'Yesterday I had unlocked the gate, had switched on the light and had opened the window.'],
            'narrative-process-not-completion' => [1, 'Mila was sorting envelopes when Danylo arrived.', 'Mila had sorted the envelopes when Danylo arrived.'],
            'narrative-arrived-not-arriving' => [1, 'when Danylo arrived', 'when Danylo was arriving'],
            'narrative-process-not-stopped' => [1, 'Чи припинила Міла сортувати конверти після цього, не сказано.', 'Міла припинила сортувати конверти після цього.'],
            'narrative-before-not-after-arrival' => [1, 'the courier had already left', 'the courier left after our arrival'],
            'narrative-did-not-opened' => [1, 'Did she open the box?', 'Did she opened the box?'],
            'narrative-gone-not-went' => [1, 'The guide had gone home before I called.', 'The guide had went home before I called.'],
            'narrative-unknown-not-olena' => [1, 'Someone had removed the note before I arrived.', 'Olena had removed the note before I arrived.'],
            'narrative-call-not-finished' => [1, 'Olena was talking on the phone when I arrived.', 'Olena had finished the phone call when I arrived.'],
            'narrative-before-not-after' => [1, 'the note before I arrived', 'the note after I arrived'],
            'b1-written-not-wrote' => [2, 'I have written three addresses today.', 'I wrote three addresses today.'],
            'b1-prepared-explicit-perfect' => [2, 'I had prepared the envelopes before Danylo arrived yesterday.', 'I prepared the envelopes before Danylo arrived yesterday.'],
            'b1-future-process-not-simple' => [2, 'I will be packing the books', 'I will pack the books'],
            'b1-future-perfect-not-simple' => [2, 'I will have finished all the packing', 'I will finish all the packing'],
            'b1-reported-statement-order' => [2, 'Mila asked me if I was ready.', 'Mila asked me if was I ready.'],
            'b1-not-to-instruction' => [2, 'She told me not to open the parcel.', 'She told me to opened the parcel.'],
            'b1-requirement-not-completed' => [2, 'The boxes must be labelled by the volunteers.', 'The boxes have been labelled by the volunteers.'],
            'b1-volunteers-not-lost' => [2, 'The boxes must be labelled by the volunteers.', 'The boxes must be labelled.'],
            'b1-causative-not-self-repair' => [2, 'I had my bicycle repaired yesterday.', 'I repaired my bicycle myself yesterday.'],
            'b1-relative-commas-required' => [2, 'Marta, who lives nearby, agreed to help.', 'Marta who lives nearby agreed to help.'],
            'b1-although-not-but' => [2, 'Although it was late, we continued.', 'Although it was late, but we continued.'],
            'b1-wish-not-have' => [2, 'I wish I had a bigger desk.', 'I wish I have a bigger desk.'],
            'b1-hypothetical-not-fact' => [2, 'If we had left earlier, we would have caught the bus.', 'We left earlier and caught the bus.'],
            'b1-might-not-is' => [2, 'Lena might be in the reading room.', 'Lena is in the reading room.'],
            'b1-might-not-must' => [2, 'Lena might be in the reading room.', 'Lena must be in the reading room.'],
            'perfect-translation-preserved' => [0, 'Я знаю нашого сусіда два роки.', ''],
            'narrative-translation-preserved' => [1, 'Лена знайшла папку й відкрила її.', ''],
            'b1-translation-preserved' => [2, 'Учора мені відремонтували велосипед.', ''],
        ];
    }

    #[DataProvider('semanticChanges')]
    public function test_real_author_fragment_mutations_are_rejected(int $owner, string $from, string $to): void
    {
        [$before, $projection] = Package::load(); $found = false;
        foreach ($projection['targets'][$owner]['after']['page']['blocks'] as &$block) {
            if (!str_contains($block['body'], $from)) { continue; }
            $old = $block['body']; $block['body'] = substr_replace($old, $to, strpos($old, $from), strlen($from));
            json_decode($block['body'], true, flags: JSON_THROW_ON_ERROR); self::assertNotSame($old, $block['body']); $found = true; break;
        }
        unset($block); self::assertTrue($found, 'Every fixture mutates an actual source fragment, never an absent string.');
        $this->expectException(RuntimeException::class); Package::validate($before, $projection);
    }
}
