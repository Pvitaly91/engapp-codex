<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M45FutureComparisonsPackage as Package;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Collection;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Independent author expectations; only the guarded isolated test schema may be rebuilt. */
class M45FutureComparisonsTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const OWNERS = [
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFutureContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFuturePerfectVsFuturePerfectContinuousTheorySeeder',
        'Database\\Seeders\\Page_V3\\FutureForms\\FutureFormsFutureContinuousVsFuturePerfectContinuousTheorySeeder',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    private function read(string $path): array
    {
        return json_decode(file_get_contents(base_path($path)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function master(): array
    {
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', Package::MASTER_SHA, 'Master must be frozen before this suite runs');
        self::assertSame(Package::MASTER_SHA, hash_file('sha256', base_path(Package::MASTER_PATH)));

        return $this->read(Package::MASTER_PATH);
    }

    private function blocks(array $definition): Collection
    {
        $owner = $definition['seeder']['class'];
        $page = $definition['page'];
        $subtitle = ['type' => 'subtitle', 'column' => 'header', 'body' => $page['subtitle_html'],
            'level' => $page['subtitle_level'] ?? null, 'uuid_key' => $page['subtitle_uuid_key'] ?? 'subtitle'];
        $out = [];
        foreach ([$subtitle, ...$page['blocks']] as $position => $config) {
            $block = new TextBlock;
            $block->forceFill([
                'id' => 45000 + $position, 'page_id' => 450, 'page_category_id' => 451,
                'uuid' => M26DetailPackage::uuid($owner, $config, $position), 'seeder' => $owner,
                'locale' => 'uk', 'type' => $config['type'], 'sort_order' => $position,
                'body' => $config['body'], 'column' => $config['column'] ?? 'left',
                'heading' => $config['heading'] ?? null, 'css_class' => $config['css_class'] ?? null,
                'level' => $config['level'] ?? null, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
            ]);
            $block->setRelation('tags', collect());
            $block->setRelation('page', null);
            $out[] = $block;
        }

        return collect($out);
    }

    private function block(array $target, int $slot): TextBlock
    {
        return $this->blocks($target['after'])[$slot + 1];
    }

    private function render(TextBlock $block, bool $context = true): string
    {
        return view('theory.partials.content-block', ['block' => $block, 'm45StyleContext' => $context])->render();
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function normalized(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function learnerText(DOMNode $node, bool $closed = false): string
    {
        $copy = $node->cloneNode(true);
        $xp = new DOMXPath($copy->ownerDocument);
        $query = './/script | .//style | .//noscript | .//summary | .//*[@data-theory-ui]';
        if ($closed) { $query .= ' | .//details'; }
        foreach (iterator_to_array($xp->query($query, $copy)) as $ui) { $ui->parentNode?->removeChild($ui); }

        return $this->normalized($copy->textContent);
    }

    private function exampleStrings(array $examples): array
    {
        $strings = [];
        foreach ($examples as $example) {
            array_push($strings, $example['en'], $example['uk']);
            if (isset($example['note_uk'])) { $strings[] = $example['note_uk']; }
        }

        return $strings;
    }

    private function detailStrings(array $detail): array
    {
        return [$detail['title'], ...$detail['paragraphs_uk'], ...$this->exampleStrings($detail['examples'] ?? [])];
    }

    private function pointStrings(array $point): array
    {
        $strings = [$point['title'], ...$point['basic_uk'], ...$this->exampleStrings($point['examples'] ?? [])];
        if (isset($point['detail'])) { array_push($strings, ...$this->detailStrings($point['detail'])); }

        return $strings;
    }

    private function cardStrings(array $card): array
    {
        $strings = [$card['title']];
        foreach ($card['rows'] as $row) { array_push($strings, $row['label_uk'], $row['formula'], $row['en'], $row['uk']); }
        $strings[] = $card['note_uk'];
        if (isset($card['detail'])) { array_push($strings, ...$this->detailStrings($card['detail'])); }

        return $strings;
    }

    private function stringsInOrder(string $text, array $strings): void
    {
        $offset = 0;
        $text = $this->normalized($text);
        foreach ($strings as $string) {
            $needle = $this->normalized($string);
            $position = strpos($text, $needle, $offset);
            self::assertNotFalse($position, 'Missing/reordered author text: '.$string);
            $offset = $position + strlen($needle);
        }
    }

    private function languageText(DOMXPath $xp, string $text, string $lang): void
    {
        $needle = $this->normalized($text);
        $matches = array_filter(iterator_to_array($xp->query('//*[@lang="'.$lang.'"]')),
            fn ($node) => str_contains($this->normalized($node->textContent), $needle));
        self::assertNotEmpty($matches, 'Missing '.$lang.' language annotation: '.$text);
    }

    private function courseHtml(array $definition, Collection $blocks): string
    {
        $page = new Page;
        $page->forceFill(['id' => 450, 'title' => $definition['page']['title'], 'text' => $definition['page']['subtitle_text'],
            'seeder' => $definition['seeder']['class'], 'type' => 'theory', 'slug' => $definition['slug']]);
        $page->setRelation('textBlocks', $blocks);
        $page->setRelation('tags', collect());

        return view('courses.partials.theory-page-content', compact('page'))->render();
    }

    public function test_frozen_author_and_projection_have_three_owners_two_compact_cards_and_eighteen_tasks(): void
    {
        $master = $this->master();
        foreach ([Package::MASTER_PATH => Package::MASTER_SHA, Package::BEFORE => Package::BEFORE_SHA, Package::SOURCE => Package::SOURCE_SHA] as $path => $sha) {
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sha, $path);
            self::assertSame($sha, hash_file('sha256', base_path($path)), $path);
            $bytes = file_get_contents(base_path($path));
            self::assertStringNotContainsString("\r", $bytes, 'LF-only '.$path);
            self::assertFalse(str_starts_with($bytes, "\xef\xbb\xbf"), 'No BOM '.$path);
            self::assertTrue(str_ends_with($bytes, "\n") && !str_ends_with($bytes, "\n\n"), 'One final LF '.$path);
        }
        self::assertSame('gramlyze.m45.author.v1', $master['schema']);
        self::assertSame('1.0.0', $master['version']);
        self::assertSame(self::OWNERS, array_column($master['lessons'], 'identity'));
        $tasks = 0; $controls = 0;
        foreach ($master['lessons'] as $lesson) {
            self::assertCount(5, $lesson['sections']);
            self::assertSame([1, 2, 3, 4, 5], array_column($lesson['sections'], 'slot'));
            $forms = array_values(array_filter($lesson['sections'], fn ($section) => $section['kind'] === 'forms'));
            self::assertCount(1, $forms);
            self::assertCount(2, $forms[0]['cards']);
            foreach ($forms[0]['cards'] as $card) {
                self::assertCount(3, $card['rows']);
                self::assertSame(['Ствердження', 'Заперечення', 'Питання'], array_column($card['rows'], 'label_uk'));
                foreach ($card['rows'] as $row) { self::assertNotSame('', $row['formula']); self::assertNotSame('', $row['en']); self::assertNotSame('', $row['uk']); }
            }
            self::assertCount(6, $lesson['practice']);
            foreach ($lesson['practice'] as $task) {
                $tasks++; $controls += count($task['controls']);
                self::assertCount(1, $task['controls'], 'Each accepted M45 task has one clear required control');
                self::assertTrue($task['controls'][0]['required']);
            }
        }
        self::assertSame([18, 18], [$tasks, $controls]);
    }

    public function test_mechanical_projection_preserves_metadata_legacy_configuration_uuids_and_reviewed_navigation(): void
    {
        [$before, $source] = Package::load();
        $master = $this->master();
        self::assertSame(Package::project($before, $master), $source);
        self::assertSame(self::OWNERS, array_column($before['targets'], 'identity'));
        foreach ($source['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $after = $target['after']; $lesson = $master['lessons'][$i];
            $protected = $after; $protected['page']['blocks'] = $old['page']['blocks'];
            foreach (['subtitle_text', 'subtitle_html'] as $field) { $protected['page'][$field] = $old['page'][$field]; }
            self::assertSame($old, $protected, 'All metadata outside the reviewed content allowlist');
            self::assertSame($lesson['subtitle_uk'], $after['page']['subtitle_text']);
            self::assertSame('<p lang="uk">'.e($lesson['subtitle_uk']).'</p>', $after['page']['subtitle_html']);
            self::assertCount(8, $after['page']['blocks']);
            foreach ($old['page']['blocks'] as $slot => $config) {
                $changed = $after['page']['blocks'][$slot];
                self::assertSame(M26DetailPackage::uuid($target['identity'], $config, $slot + 1), M26DetailPackage::uuid($target['identity'], $changed, $slot + 1));
                $changed['body'] = $config['body'];
                self::assertSame($config, $changed, 'Existing type/level/heading/column/tags/configuration '.$slot);
            }
            foreach ($lesson['sections'] as $section) {
                self::assertSame($section, json_decode($after['page']['blocks'][$section['slot']]['body'], true, flags: JSON_THROW_ON_ERROR)['author_section']);
            }
            $hero = json_decode($after['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
            self::assertSame('<p lang="uk">'.e($lesson['hero_intro_uk']).'</p>', $hero['intro']);
            self::assertSame([], $hero['rules']);
            $navigation = json_decode($old['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR);
            foreach ($navigation['items'] as &$item) { $item['url'] = $before['targets'][$i]['navigation_urls'][$item['url']]; }
            unset($item);
            self::assertSame($navigation, json_decode($after['page']['blocks'][6]['body'], true, flags: JSON_THROW_ON_ERROR));
        }
    }

    public function test_renderer_keeps_independent_author_basic_detail_bilingual_forms_and_unique_anchors(): void
    {
        [, $source] = Package::load();
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $html = '';
            foreach ($lesson['sections'] as $section) {
                $block = $this->block($source['targets'][$i], $section['slot']);
                $rendered = $this->render($block); $html .= $rendered; $xp = $this->xpath($rendered);
                self::assertSame(1, $xp->query('//*[@data-m45-author-section="'.$section['id'].'"]')->length);
                self::assertSame(1, $xp->query('//*[@id="block-'.$block->id.'"]')->length, 'Original block anchor');
                self::assertSame(1, $xp->query('//*[@id="'.$section['id'].'"]')->length);
                self::assertSame(0, $xp->query('//details[@open]')->length, 'Depth starts closed');
                self::assertSame(0, $xp->query('//details//details')->length, 'No nested disclosures');
                foreach ($section['points'] as $point) {
                    $nodes = $xp->query('//*[@data-m45-basic-point and @id="'.$point['id'].'"]');
                    self::assertSame(1, $nodes->length);
                    $this->stringsInOrder($this->learnerText($nodes->item(0)), $this->pointStrings($point));
                    $this->stringsInOrder($this->learnerText($nodes->item(0), true), [$point['title'], ...$point['basic_uk'], ...$this->exampleStrings($point['examples'] ?? [])]);
                    foreach ($point['basic_uk'] as $paragraph) { $this->languageText($xp, $paragraph, 'uk'); }
                    foreach (array_merge($point['examples'] ?? [], $point['detail']['examples'] ?? []) as $example) {
                        $this->languageText($xp, $example['en'], 'en'); $this->languageText($xp, $example['uk'], 'uk');
                    }
                    if (isset($point['detail'])) {
                        self::assertSame(1, $xp->query('.//details[@data-m45-detail="'.$point['detail']['id'].'"]', $nodes->item(0))->length);
                        foreach ($point['detail']['paragraphs_uk'] as $paragraph) { $this->languageText($xp, $paragraph, 'uk'); }
                    }
                }
                foreach ($section['cards'] ?? [] as $card) {
                    $nodes = $xp->query('//*[@data-m45-form-card and @id="'.$card['id'].'"]');
                    self::assertSame(1, $nodes->length);
                    self::assertSame(3, $xp->query('.//*[@data-m45-form-row and not(ancestor::details)]', $nodes->item(0))->length);
                    $this->stringsInOrder($this->learnerText($nodes->item(0)), $this->cardStrings($card));
                    foreach ($card['rows'] as $row) {
                        $this->stringsInOrder($this->learnerText($nodes->item(0), true), [$row['formula'], $row['en'], $row['uk']]);
                        $this->languageText($xp, $row['formula'], 'en'); $this->languageText($xp, $row['en'], 'en'); $this->languageText($xp, $row['uk'], 'uk');
                    }
                    if (isset($card['detail'])) { self::assertSame(1, $xp->query('.//details[@data-m45-detail="'.$card['detail']['id'].'"]', $nodes->item(0))->length); }
                }
                if ($section['kind'] === 'forms') { self::assertSame(2, $xp->query('//*[@data-m45-form-card]')->length); }
            }
            $ids = array_map(fn ($node) => $node->getAttribute('id'), iterator_to_array($this->xpath($html)->query('//*[@id]')));
            self::assertSame(count($ids), count(array_unique($ids)), 'No duplicate author/legacy/detail anchors within a lesson');
        }
    }

    public function test_practice_projects_all_controls_feedback_and_nojs_keys_without_premature_answers(): void
    {
        [, $source] = Package::load();
        foreach ($this->master()['lessons'] as $i => $lesson) {
            $block = $this->block($source['targets'][$i], 7);
            $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($lesson['practice'], $data['author_practice']);
            $html = $this->render($block); $xp = $this->xpath($html);
            self::assertSame(6, $xp->query('//*[@data-m45-ui-case]')->length);
            self::assertSame(6, $xp->query('//details[@data-m45-ui-explanation and not(@open)]')->length);
            self::assertGreaterThan(0, $xp->query('//noscript')->length);
            self::assertStringContainsString('m45PracticeUi', $html);
            self::assertStringNotContainsString('m44PracticeUi', $html);
            foreach ($xp->query('//textarea') as $input) {
                self::assertSame('', trim($input->textContent));
                self::assertFalse($input->hasAttribute('placeholder'), 'No answer-bearing placeholder');
                self::assertFalse($input->hasAttribute('disabled'), 'A wrong answer must remain editable');
            }
            foreach ($lesson['practice'] as $j => $task) {
                $actual = $data['cases'][$j];
                self::assertSame($task['id'], $actual['id']);
                self::assertSame('all_required_controls', $actual['scoring']);
                $case = $xp->query('//*[@data-m45-ui-case="'.($j + 1).'"]')->item(0);
                $prompt = $xp->query('.//*[@data-m45-author-prompt]', $case)->item(0);
                $this->stringsInOrder($this->learnerText($prompt), [$task['title'], $task['prompt_uk'], ...(!empty($task['context_uk']) ? [$task['context_uk']] : [])]);
                foreach ($task['controls'] as $p => $control) {
                    $projected = $actual['controls'][$p];
                    self::assertSame($control['id'], $projected['id']); self::assertTrue($projected['required']);
                    self::assertSame($control['kind'], $projected['source_kind']); self::assertSame($control['label_uk'], $projected['label']);
                    foreach (['stimulus_en', 'stimulus_uk'] as $field) { self::assertSame($control[$field] ?? null, $projected[$field] ?? null); }
                    if (in_array($control['kind'], ['manual', 'tokens'], true)) {
                        self::assertSame('manual', $projected['kind']);
                        self::assertSame($control['canonical_answer'], $projected['answer']);
                        self::assertSame(array_values(array_unique([$control['canonical_answer'], ...($control['accepted_answers'] ?? [])])), $projected['accepted']);
                        self::assertSame($control['tokens'] ?? [], $projected['tokens']);
                        self::assertStringNotContainsString($control['canonical_answer'], $this->learnerText($prompt), 'Canonical answer is not an instruction hint');
                        if ($control['kind'] === 'tokens') { self::assertNotSame($control['canonical_answer'], implode(' ', $control['tokens']), 'Builder bank is not the ready answer'); }
                    } else {
                        self::assertSame($control['correct_value'], $projected['answer']);
                        self::assertSame(array_map(fn ($option) => ['value' => $option['value'], 'label' => $option['label_uk']], $control['options']), $projected['options']);
                    }
                }
                $feedback = $xp->query('.//*[@data-m45-self-check-answer="'.($j + 1).'"]', $case)->item(0);
                $this->stringsInOrder($this->learnerText($feedback), [...$task['feedback']['paragraphs_uk'], ...$this->exampleStrings($task['feedback']['answer_examples'])]);
                foreach ($task['feedback']['answer_examples'] as $example) { $this->languageText($xp, $example['en'], 'en'); $this->languageText($xp, $example['uk'], 'uk'); }
            }
        }
        $script = file_get_contents(public_path('js/m45-practice-ui.js'));
        self::assertStringNotContainsString('localStorage', $script); self::assertStringNotContainsString('sessionStorage', $script);
        self::assertDoesNotMatchRegularExpression('/(?:window|globalThis)\.[\w]*cache[\w]*/i', $script, 'No global answer/projection cache');
    }

    public function test_strict_owner_locale_uuid_payload_and_caller_guards_keep_complete_static_fallback_after_warming(): void
    {
        [$before, $source] = Package::load();
        Package::validate($before, $source);
        foreach ($source['targets'] as $i => $target) {
            $hero=$this->block($target,0);$hero->uuid='foreign-hero';
            self::assertStringContainsString($this->master()['lessons'][$i]['hero_intro_uk'],$this->render($hero));
            $block = $this->block($target, 1); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
            self::assertNotNull(Package::presentation($block, $data));
            foreach (['seeder' => 'ForeignOwner', 'locale' => 'pl', 'uuid' => 'foreign', 'sort_order' => 99, 'type' => 'box', 'column' => 'footer', 'level' => 'C2'] as $field => $value) {
                $foreign = clone $block; $foreign->setAttribute($field, $value);
                self::assertNull(Package::presentation($foreign, $data), 'Reject '.$field);
                $xp = $this->xpath($this->render($foreign));
                self::assertSame(1, $xp->query('//*[@data-m45-static-fallback]')->length);
                self::assertSame(0, $xp->query('//details[@data-m45-detail]')->length);
                foreach ($data['author_section']['points'] as $point) { $this->stringsInOrder($this->learnerText($xp->query('//body')->item(0)), $this->pointStrings($point)); }
            }
            $changed = $data; $changed['author_section']['points'][0]['basic_uk'][0] .= ' Змінений вхідний текст.';
            $tampered = clone $block; $tampered->body = Package::json($changed);
            self::assertNull(Package::presentation($tampered, $changed));
            self::assertStringContainsString('Змінений вхідний текст.', $this->render($tampered));
            $spoof = $data; $spoof['m44_v1'] = $spoof['m45_v1']; unset($spoof['m45_v1']);
            self::assertNull(Package::presentation($block, $spoof), 'M44 marker is not M45 authority');
            $unmarked = $data; unset($unmarked['m45_v1']);
            $unmarkedBlock = clone $block; $unmarkedBlock->body = Package::json($unmarked);
            self::assertNull(Package::presentation($unmarkedBlock, $unmarked));
            $unmarkedDom = $this->xpath($this->render($unmarkedBlock));
            self::assertSame(1, $unmarkedDom->query('//*[@data-m45-static-fallback]')->length, 'A missing marker cannot discard stored author prose');
            foreach ($data['author_section']['points'] as $point) { $this->stringsInOrder($this->learnerText($unmarkedDom->query('//body')->item(0)), $this->pointStrings($point)); }
            self::assertSame(1, $this->xpath($this->render($block, false))->query('//*[@data-m45-static-fallback]')->length, 'Caller-owned opt-in is required');
            $practice = $this->block($target, 7); $practice->uuid = 'foreign-practice';
            $practiceData = json_decode($practice->body, true, flags: JSON_THROW_ON_ERROR);
            self::assertNull(Package::presentation($practice, $practiceData));
            $practiceDom = $this->xpath($this->render($practice));
            self::assertSame(1, $practiceDom->query('//*[@data-m45-static-fallback]')->length);
            self::assertSame(0, $practiceDom->query('//*[@data-m45-practice-ui]')->length);
            $practiceText = $this->learnerText($practiceDom->query('//body')->item(0));
            foreach ($practiceData['author_practice'] as $task) {
                $strings = [$task['title'], $task['prompt_uk']];
                if (!empty($task['context_uk'])) { $strings[] = $task['context_uk']; }
                foreach ($task['controls'] as $control) {
                    $strings[] = $control['label_uk'];
                    foreach (['stimulus_en', 'stimulus_uk'] as $field) { if (isset($control[$field])) { $strings[] = $control[$field]; } }
                    array_push($strings, ...array_column($control['options'] ?? [], 'label_uk'), ...($control['tokens'] ?? []));
                }
                array_push($strings, ...$task['feedback']['paragraphs_uk'], ...$this->exampleStrings($task['feedback']['answer_examples']));
                $this->stringsInOrder($practiceText, $strings);
            }
        }
        foreach (['source-body', 'source-owner', 'before-title'] as $mutation) {
            $changedBefore = $before; $changedSource = $source;
            if ($mutation === 'source-body') { $changedSource['targets'][0]['after']['page']['blocks'][1]['body'] = '{}'; }
            elseif ($mutation === 'source-owner') { $changedSource['targets'][0]['identity'] = 'ForeignOwner'; }
            else { $changedBefore['targets'][0]['before']['page']['title'] = 'Changed incoming baseline'; }
            try { Package::validate($changedBefore, $changedSource); self::fail('Warm validation accepted '.$mutation); }
            catch (RuntimeException $error) { self::assertStringContainsString('M45', $error->getMessage()); }
        }
        Package::validate($before, $source);
    }

    public function test_native_and_rejected_source_variants_escape_formula_examples_translation_and_notes(): void
    {
        [, $source] = Package::load();
        $block = $this->block($source['targets'][0], 2); $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        $payloads = [
            'formula' => '<script>alert("formula")</script> & will have + V3',
            'en' => '<img src=x onerror="bad"> & original example.',
            'uk' => 'Точний <переклад> & незмінний текст.',
        ];
        foreach ($payloads as $field => $value) { $data['author_section']['cards'][0]['rows'][0][$field] = $value; }
        $data['author_section']['cards'][0]['note_uk'] = 'Окрема <svg onload="bad"> примітка & текст.';
        $block->body = Package::json($data);
        self::assertNull(Package::presentation($block, $data));
        foreach ([view('engram.theory.blocks-v3.m45-section', compact('block', 'data'))->render(), $this->render($block)] as $html) {
            $xp = $this->xpath($html); $text = $this->learnerText($xp->query('//body')->item(0));
            self::assertSame(0, $xp->query('//script | //img | //svg[@onload]')->length, 'Text is escaped; native header SVG icons are not injected payload');
            foreach ($payloads as $value) { self::assertStringContainsString($value, $text); }
            self::assertStringContainsString($data['author_section']['cards'][0]['note_uk'], $text);
            $this->languageText($xp, $payloads['formula'], 'en'); $this->languageText($xp, $payloads['en'], 'en'); $this->languageText($xp, $payloads['uk'], 'uk');
        }
        $practice = $this->block($source['targets'][0], 7); $practiceData = json_decode($practice->body, true, flags: JSON_THROW_ON_ERROR);
        $prompt = 'Умова <script>alert("prompt")</script> & текст.';
        $label = 'Варіант <img src=x onerror="bad"> & відповідь.';
        $practiceData['author_practice'][0]['prompt_uk'] = $prompt;
        $practiceData['author_practice'][0]['controls'][0]['options'][0]['label_uk'] = $label;
        $practice->body = Package::json($practiceData);
        self::assertNull(Package::presentation($practice, $practiceData));
        $xp = $this->xpath($this->render($practice));
        self::assertSame(0, $xp->query('//script | //img')->length);
        self::assertStringContainsString($prompt, $this->learnerText($xp->query('//body')->item(0)));
        self::assertStringContainsString($label, $this->learnerText($xp->query('//body')->item(0)));
    }

    public function test_course_clones_restore_before_byte_html_without_mutating_inputs_or_accepting_incomplete_collections(): void
    {
        [$before, $source] = Package::load();
        foreach ($source['targets'] as $i => $target) {
            $old = $before['targets'][$i]['before']; $original = $this->blocks($old); $current = $this->blocks($target['after']);
            $snapshot = $current->map(fn ($block) => $block->getAttributes())->all();
            self::assertTrue(Package::allowsTheoryContext($current, 'uk'));
            self::assertFalse(Package::allowsTheoryContext($current, 'en'));
            $preserved = Package::preserveCourseBlocks($current);
            self::assertSame($original->map(fn ($block) => $block->getAttributes())->all(), $preserved->map(fn ($block) => $block->getAttributes())->all());
            foreach ($preserved as $position => $block) { self::assertNotSame($current[$position], $block); }
            $expected = $this->courseHtml($old, $original); $actual = $this->courseHtml($target['after'], $current);
            self::assertSame($expected, $actual, $target['identity'].' protected course HTML');
            self::assertStringNotContainsString('data-m45-', $actual);
            self::assertSame($snapshot, $current->map(fn ($block) => $block->getAttributes())->all(), 'Rendering never mutates controller input models');
            self::assertSame($preserved->pluck('uuid')->all(), Package::preserveCourseBlocks($current->reverse())->pluck('uuid')->all(), 'Restored course order is deterministic');
            foreach (['locale' => 'en', 'seeder' => 'ForeignOwner', 'uuid' => 'unknown', 'body' => '{}'] as $field => $value) {
                $copy = $current->map(fn ($block) => clone $block); $copy[2]->setAttribute($field, $value);
                self::assertSame($copy, Package::preserveCourseBlocks($copy), 'No rewrite for '.$field);
                self::assertFalse(Package::allowsTheoryContext($copy, 'uk'));
            }
            foreach ([$current->slice(1), $current->concat([$current[0]]), collect()] as $unknown) {
                self::assertSame($unknown, Package::preserveCourseBlocks($unknown));
                self::assertFalse(Package::allowsTheoryContext($unknown, 'uk'));
            }
        }
    }

    public function test_explanatory_subtitle_keeps_controller_derived_m45_title_without_changing_other_owners_or_locales():void
    {
        [, $source]=Package::load();
        $controller=app(\App\Http\Controllers\TheoryController::class);
        $extract=new \ReflectionMethod($controller,'extractLocalizedTitleFromSubtitle');
        $localize=new \ReflectionMethod($controller,'applyLocalizedTitlesToModels');
        foreach($source['targets'] as $target){
            $definition=$target['after'];$page=new Page;
            $page->forceFill(['id'=>450,'seeder'=>$target['identity'],'type'=>'theory','slug'=>$definition['slug'],
                'title'=>$definition['page']['title'],'text'=>$definition['page']['subtitle_text']]);$page->syncOriginal();
            $snapshot=$page->getRawOriginal();
            $candidate=$extract->invoke($controller,$definition['page']['subtitle_html']);
            self::assertNotSame($definition['page']['title'],$candidate,'The legacy parser really mistakes explanation for title');
            $localize->invoke($controller,collect([$page]),[450=>['uk'=>$candidate]],'uk','en');
            self::assertSame($definition['page']['title'],$page->title);
            self::assertSame($snapshot,$page->getRawOriginal());
            self::assertFalse($page->isDirty('title'));
            self::assertNull(Package::displayTitle($page,'en'));
            self::assertNull(Package::displayTitle($page,'pl'));
            foreach(['seeder'=>'ForeignOwner','type'=>'course','slug'=>'foreign','title'=>'Changed title','text'=>'Manual change'] as $field=>$value){
                $foreign=clone $page;$foreign->setAttribute($field,$value);$foreign->syncOriginal();
                self::assertNull(Package::displayTitle($foreign,'uk'),'Reject '.$field);
            }
        }
    }
}
