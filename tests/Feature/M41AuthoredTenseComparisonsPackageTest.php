<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M41AuthoredTenseComparisonsPackage as Package;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M41AuthoredTenseComparisonsPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); app()->setLocale('uk'); $this->withoutVite();
    }

    private function block(array $target, int $slot): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot]; $block = new TextBlock;
        $block->forceFill(['id' => 41000 + $slot, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1, 'type' => $source['type'],
            'body' => $source['body'], 'level' => $source['level'] ?? null, 'heading' => $source['heading'] ?? null,
            'column' => $source['column']]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    private function data(array $target, int $slot): array
    {
        return json_decode($target['after']['page']['blocks'][$slot]['body'], true, flags: JSON_THROW_ON_ERROR);
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        // Native partials may begin with scoped @once style elements. Give the
        // HTML4 DOM parser an explicit body instead of guessing fragment roots.
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET); libxml_clear_errors();
        return new DOMXPath($dom);
    }

    public function test_exact_finite_projection_accepts_only_its_approved_owner_locale_uuid_position_and_bytes(): void
    {
        [$before, $projection] = Package::load(); Package::validate($before, $projection);
        foreach ($projection['targets'] as $target) {
            foreach ($target['plans'] as $plan) {
                $slot = $plan['slot']; $block = $this->block($target, $slot); $data = $this->data($target, $slot);
                self::assertNotNull(Package::presentation($block, $data));
                foreach (['seeder' => 'Database\\Seeders\\ForeignOwner', 'locale' => 'en', 'uuid' => '00000000-0000-0000-0000-000000000000',
                    'sort_order' => 999, 'type' => 'box'] as $field => $value) {
                    $foreign = clone $block; $foreign->setAttribute($field, $value);
                    self::assertNull(Package::presentation($foreign, $data), 'No marker may opt an unknown owner/locale/UUID/position/type into the finite view.');
                }
                $different = $data; $different['m41_v1']['master_sha256'] = str_repeat('0', 64);
                self::assertNull(Package::presentation($block, $different));
                $different = $data; $different['unapproved_extra'] = 'No executable view selected by DB data.';
                self::assertNull(Package::presentation($block, $different));
            }
        }
    }

    public function test_server_render_has_all_authored_basic_points_fourteen_closed_details_and_all_tables(): void
    {
        [, $projection] = Package::load(); $totals = [];
        foreach ($projection['targets'] as $target) {
            $detailCount = 0;
            foreach ($target['section_slots'] as $slot) {
                $data = $this->data($target, $slot); $block = $this->block($target, $slot);
                $html = view('theory.partials.content-block', compact('block', 'data'))->render();
                $xp = $this->xpath($html); $text = $xp->query('//body')->item(0)->textContent;
                foreach ($data['author_section']['points'] as $point) {
                    self::assertStringContainsString($point['title'], $text);
                    foreach ($point['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    foreach ($point['examples'] as $example) {
                        self::assertStringContainsString($example['en'], $text); self::assertStringContainsString($example['uk'], $text);
                    }
                    if (isset($point['detail'])) {
                        $detailCount++; self::assertStringContainsString($point['detail']['title'], $text);
                        foreach ($point['detail']['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    }
                }
                $expected = count(array_filter($data['author_section']['points'], fn ($point) => isset($point['detail'])));
                self::assertSame($expected, $xp->query('//details')->length);
                self::assertSame(0, $xp->query('//details[@open]')->length);
                if (isset($data['author_section']['table'])) {
                    // The unchanged author table is projected once into the
                    // existing six native form cards, not a duplicate table.
                    self::assertSame(0, $xp->query('//table')->length);
                    self::assertSame(6, $xp->query('//*[@data-m41-form-cell]')->length);
                    foreach ($data['author_section']['table']['columns'] as $column) { self::assertStringContainsString($column, $text); }
                    foreach ($data['author_section']['table']['rows'] as $rowIndex => $row) {
                        self::assertStringContainsString($row[0], $text);
                        foreach ([1, 2] as $columnIndex) {
                            $cell = $xp->query('//*[@data-m41-form-cell="'.$rowIndex.'-'.$columnIndex.'"]');
                            self::assertSame(1, $cell->length);
                            foreach (explode("\n", $row[$columnIndex]) as $line) { self::assertStringContainsString($line, $cell->item(0)->textContent); }
                        }
                    }
                }
            }
            $totals[] = $detailCount;
        }
        self::assertSame([4, 5, 5], $totals);
    }

    public function test_invalid_finite_identity_keeps_complete_basic_detail_and_table_readable_in_fallback(): void
    {
        [, $projection] = Package::load();
        foreach ($projection['targets'] as $target) {
            foreach ($target['section_slots'] as $slot) {
                $data = $this->data($target, $slot); $block = $this->block($target, $slot); $block->locale = 'en';
                self::assertNull(Package::presentation($block, $data));
                $html = view('theory.partials.content-block', compact('block', 'data'))->render();
                $xp = $this->xpath($html); $text = $xp->query('//body')->item(0)->textContent;
                foreach ($data['author_section']['points'] as $point) {
                    foreach ($point['paragraphs_uk'] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    foreach ($point['examples'] as $example) { self::assertStringContainsString($example['uk'], $text); }
                    foreach ($point['detail']['paragraphs_uk'] ?? [] as $paragraph) { self::assertStringContainsString($paragraph, $text); }
                    foreach ($point['detail']['examples'] ?? [] as $example) {
                        self::assertStringContainsString($example['en'], $text); self::assertStringContainsString($example['uk'], $text);
                    }
                }
                self::assertSame(0, $xp->query('//details')->length, 'Invalid identity keeps depth visible, not hidden behind a guessed disclosure.');
                if (isset($data['author_section']['table'])) {
                    self::assertSame(1, $xp->query('//table')->length);
                    foreach ($data['author_section']['table']['rows'] as $row) {
                        foreach ($row as $cell) { foreach (explode("\n", $cell) as $line) { self::assertStringContainsString($line, $text); } }
                    }
                }
            }
        }
    }

    public function test_invalid_practice_identity_keeps_all_author_controls_tokens_prompts_and_keys_static(): void
    {
        [, $projection] = Package::load();
        $labels = 0; $stimuli = 0; $options = 0; $tokens = 0; $prompts = 0; $keys = 0;
        foreach ($projection['targets'] as $target) {
            $slot = $target['practice_slot']; $data = $this->data($target, $slot); $block = $this->block($target, $slot);
            $block->locale = 'en';
            self::assertNull(Package::presentation($block, $data));
            $html = view('theory.partials.content-block', compact('block', 'data'))->render();
            $xp = $this->xpath($html);
            self::assertSame(0, $xp->query('//*[@x-data]')->length, 'Rejected identity must not select an interactive factory.');
            self::assertSame(0, $xp->query('//script')->length);
            self::assertStringNotContainsString('m41PracticeUi(', $html);
            self::assertSame(0, $xp->query('//*[@data-m41-check or @data-m41-reset]')->length);
            self::assertSame(count($data['cases']), $xp->query('//*[@data-m41-fallback-prompt]')->length);
            self::assertSame(count($data['cases']), $xp->query('//*[@data-m41-fallback-key]')->length);
            foreach ($data['cases'] as $i => $case) {
                $index = $case['source_index'];
                $prompt = $xp->query('//*[@data-m41-fallback-prompt="'.$index.'"]');
                $key = $xp->query('//*[@data-m41-fallback-key="'.$index.'"]');
                self::assertSame(1, $prompt->length); self::assertSame(1, $key->length);
                self::assertSame(trim($this->xpath($data['author_self_check']['prompts'][$i])->query('//body')->item(0)->textContent),
                    trim($prompt->item(0)->textContent));
                self::assertSame(trim($this->xpath($data['author_self_check']['answers'][$i])->query('//body')->item(0)->textContent),
                    trim($key->item(0)->textContent));
                $prompts++; $keys++;
                foreach ($case['controls'] as $control) {
                    $nodes = $xp->query('//*[@data-m41-fallback-control="'.$control['id'].'"]');
                    self::assertSame(1, $nodes->length); $node = $nodes->item(0);
                    $label = $xp->query('.//*[@data-m41-fallback-label]', $node);
                    self::assertSame(1, $label->length); self::assertSame($control['label'], trim($label->item(0)->textContent));
                    $labels++;
                    $stimulus = $xp->query('.//*[@data-m41-fallback-stimulus]', $node);
                    self::assertSame(isset($control['stimulus_en']) ? 1 : 0, $stimulus->length);
                    if (isset($control['stimulus_en'])) {
                        self::assertSame($control['stimulus_en'], trim($stimulus->item(0)->textContent)); $stimuli++;
                    }
                    $optionNodes = $xp->query('.//*[@data-m41-fallback-option]', $node);
                    self::assertSame(array_column($control['options'], 'label'),
                        array_map(fn ($option) => trim($option->textContent), iterator_to_array($optionNodes)));
                    $options += $optionNodes->length;
                    $tokenNodes = $xp->query('.//*[@data-m41-fallback-token]', $node);
                    self::assertSame($control['tokens'] ?? [],
                        array_map(fn ($token) => trim($token->textContent), iterator_to_array($tokenNodes)));
                    $tokens += $tokenNodes->length;
                }
            }
        }
        self::assertSame([32, 9, 35, 55, 18, 18], [$labels, $stimuli, $options, $tokens, $prompts, $keys]);
    }

    public static function authorMutations(): array
    {
        return [
            'past-event-not-process' => [0, 'Oksana repaired the drawer yesterday.', 'Oksana was repairing the drawer yesterday.'],
            'past-given-time-not-new-time' => [0, 'At 2.30, Lev was sanding the door.', 'At 3.30, Lev was sanding the door.'],
            'past-arrival-not-always-continuous' => [0, 'The bus arrived at eight.', 'The bus was arriving at eight.'],
            'past-when-process-not-sequence' => [0, 'When Danylo arrived, Iryna was making tea.', 'When Danylo arrived, Iryna made tea.'],
            'past-did-base-not-v2' => [0, 'Did she take the key?', 'Did she took the key?'],
            'past-we-were-not-was' => [0, 'We were waiting outside.', 'We was waiting outside.'],
            'past-reading-not-proven-stopping' => [0, 'Ні, наступну дію не зазначено.', 'Так, обов’язково припинилося.'],
            'past-next-event-no-invented-entry' => [0, 'Then the door opened.', 'Then I opened the door and went inside.'],
            'present-usual-not-now' => [1, 'Nina usually cooks dinner.', 'Nina is cooking dinner right now.'],
            'present-question-remains-question' => [1, 'Does Kateryna work here?', 'Kateryna works here.'],
            'present-current-period-not-this-second' => [1, 'Andrii is reading a new book this week.', 'Andrii is reading a new book right now.'],
            'present-state-not-knowing' => [1, 'I know the address now.', 'I am knowing the address now.'],
            'present-thinking-action-not-state' => [1, 'I am thinking about your suggestion right now.', 'I think your suggestion is correct.'],
            'present-temporary-not-permanent' => [1, 'I am working from home today.', 'I always work from home.'],
            'present-arrangement-not-finished-event' => [1, 'I am meeting Marta at eight tomorrow.', 'I met Marta at eight yesterday.'],
            'perfect-count-not-new-fact' => [2, 'I have checked three addresses so far today.', 'I have checked four addresses so far today.'],
            'perfect-yesterday-not-perfect' => [2, 'I checked your address yesterday.', 'I have checked your address yesterday.'],
            'perfect-experience-not-finished-period' => [2, 'Have you ever travelled by ferry?', 'Did you ever travel by ferry last summer?'],
            'perfect-result-not-found' => [2, 'I lost my pass yesterday, and I still haven’t found it.', 'I lost my pass yesterday, but I found it today.'],
            'perfect-has-v3-not-v2' => [2, 'She has written the note.', 'She has wrote the note.'],
            'perfect-did-base-not-v2' => [2, 'Did she write the note yesterday?', 'Did she wrote the note yesterday?'],
            'perfect-continuing-not-moved' => [2, 'We have lived here for three years.', 'We lived here for three years and moved.'],
            'perfect-author-correction-not-old-wording' => [2,
                'У прикладі нижче форму надіслали лише один раз: спочатку повідомляємо про результат, а потім уточнюємо час надсилання.',
                'Зразок нижче не означає, що форма написалася двічі: це одна подія, подана спочатку як новий результат, потім із часом.'],
            'perfect-translation-not-omitted' => [2, 'Я надіслав форму. Я надіслав її сьогодні о дев’ятій.', ''],
        ];
    }

    #[DataProvider('authorMutations')]
    public function test_actual_author_fragment_changes_are_rejected(int $owner, string $from, string $to): void
    {
        [$before, $projection] = Package::load(); $found = false;
        foreach ($projection['targets'][$owner]['after']['page']['blocks'] as &$source) {
            if (!str_contains($source['body'], $from)) { continue; }
            $original = $source['body']; $source['body'] = str_replace($from, $to, $source['body']);
            json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertNotSame($original, $source['body']); $found = true; break;
        }
        unset($source); self::assertTrue($found, 'Each fixture must mutate an actual approved source fragment.');
        $this->expectException(RuntimeException::class); Package::validate($before, $projection);
    }
}
