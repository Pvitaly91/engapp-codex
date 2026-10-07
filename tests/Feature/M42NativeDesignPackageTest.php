<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M39PracticeUiPackage;
use App\Support\M41AuthoredTenseComparisonsPackage;
use App\Support\M42NativeDesignPackage as Design;
use App\Support\TheoryPresentation;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class M42NativeDesignPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp(); $this->rebuildComposeTestSchema(); $this->withoutVite(); app()->setLocale('uk');
    }

    public static function owners(): array
    {
        $root = dirname(__DIR__, 2); $records = Design::registry($root)['targets']; $out = [];
        foreach ($records as $record) { $out[$record['original_stage'].'/'.$record['slug']] = [$record['identity']]; }
        return $out;
    }

    private function block(array $target, int $slot): TextBlock
    {
        $source = $target['after']['page']['blocks'][$slot]; $block = new TextBlock;
        $block->forceFill(['id' => 43000 + $slot, 'uuid' => M26DetailPackage::uuid($target['identity'], $source, $slot + 1),
            'seeder' => $target['identity'], 'locale' => 'uk', 'sort_order' => $slot + 1,
            'type' => $source['type'], 'body' => $source['body'], 'level' => $source['level'] ?? null,
            'heading' => $source['heading'] ?? null, 'column' => $source['column'], 'css_class' => $source['css_class'] ?? null]);
        $block->setRelation('tags', collect()); $block->setRelation('page', null);
        return $block;
    }

    private function presentation(array $target, TextBlock $block, array $data): ?array
    {
        $class = $target['package_class'];
        return $target['package_number'] === 39
            ? (M39PracticeUiPackage::presentation($block, $data) ?? $class::presentation($block, $data))
            : $class::presentation($block, $data);
    }

    public function test_exact_registry_contains_only_42_accepted_owners_and_independent_counts(): void
    {
        $registry = Design::registry(); $mapping = Design::load(); $sources = Design::sourceTargets();
        self::assertCount(42, $registry['targets']); self::assertCount(42, $mapping['targets']); self::assertCount(42, $sources);
        self::assertSame(Design::REFERENCE_SHA, $registry['reference_sha']);
        self::assertSame(Design::REGISTRY_SHA, hash_file('sha256', base_path(Design::REGISTRY)));
        self::assertSame(Design::SOURCE_SHA, hash_file('sha256', base_path(Design::SOURCE)));
        self::assertSame(Design::build(), $mapping);
        self::assertSame(383, array_sum(array_map(fn ($target) => count($target['blocks']), $registry['targets'])));
        self::assertSame(20, array_sum(array_column($registry['targets'], 'teaching_details')));
        self::assertSame(252, array_sum(array_column($registry['targets'], 'exercise_count')));
        self::assertSame(274, array_sum(array_column($registry['targets'], 'control_count')));
        self::assertCount(42, array_unique(array_column($registry['targets'], 'local_url')));
        foreach ($mapping['targets'] as $owner => $target) {
            self::assertSame($registry['targets'][$owner]['identity'], $target['identity']);
            foreach ($target['blocks'] as $slot => $plan) {
                $source = $sources[$target['identity']]['after']['page']['blocks'][$slot];
                self::assertSame($source['type'], $plan['component']);
                self::assertSame($registry['targets'][$owner]['blocks'][$slot]['uuid'], $plan['uuid']);
                foreach (['view', 'title', 'body', 'description', 'examples', 'detail', 'answer', 'correct'] as $field) {
                    self::assertArrayNotHasKey($field, $plan, 'A style mapping cannot copy/rewrite teaching data.');
                }
            }
        }
    }

    #[DataProvider('owners')]
    public function test_every_owner_preserves_all_accepted_data_points_keys_order_and_native_components(string $identity): void
    {
        $target = Design::sourceTargets()[$identity];
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            $block = $this->block($target, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $this->presentation($target, $block, $data);
            self::assertNull(Design::decorate($block, $data, $presentation));
            $decorated = Design::decorate($block, $data, $presentation, true);
            self::assertNotNull($decorated, $identity.' slot '.$slot);
            self::assertArrayHasKey('m42_native_design', $decorated);
            self::assertArrayNotHasKey('m42_native_design', $decorated['data']);
            self::assertSame($source['type'], $decorated['m42_native_design']['component']);
            self::assertSame($slot, $decorated['m42_native_design']['source_index']);
            $restored = $decorated; unset($restored['m42_native_design']);
            self::assertSame($presentation ?? ['data' => $data, 'points' => [], 'legacy_section' => null, 'legacy_practice_id' => null], $restored);
            self::assertSame($presentation, $this->presentation($target, $block, $data), 'Repeated native guards are unchanged.');
            if ($source['type'] === 'practice-set') {
                // The practice partial independently re-runs exact package guards.
                self::assertSame($presentation, $this->presentation($target, $block, $decorated['data']));
                self::assertSame($data, $decorated['data']);
            }
        }
    }

    #[DataProvider('owners')]
    public function test_all_42_owner_guards_fail_closed_for_foreign_locale_uuid_order_type_or_teaching_byte(string $identity): void
    {
        $target = Design::sourceTargets()[$identity]; $block = $this->block($target, 0);
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        foreach (['locale' => 'pl', 'uuid' => 'foreign', 'sort_order' => 999, 'type' => 'box', 'seeder' => 'ForeignOwner',
            'column' => 'unapproved', 'heading' => 'Changed heading', 'level' => 'C3', 'css_class' => 'unapproved-class'] as $field => $value) {
            $foreign = clone $block; $foreign->$field = $value;
            self::assertNull(Design::binding($foreign, $data));
            self::assertNull(Design::decorate($foreign, $data, null, true));
        }
        $changed = $data; $changed['intro'] = ($changed['intro'] ?? '').' Changed source byte';
        self::assertNull(Design::binding($block, $changed));
        self::assertNull(Design::decorate($block, $changed, null, true));
        $invented = ['data' => $data, 'points' => ['invented-hidden-basic'], 'native_view' => 'foreign.executable'];
        self::assertNull(Design::decorate($block, $data, $invented, true));
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            $native = $this->block($target, $slot); $original = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $tampered = $original; $tampered['m42_unapproved'] = 'Dropped translation, changed example, alias, scoring or key';
            self::assertNull(Design::binding($native, $tampered), 'Every block compares the complete source, not selected fields.');
        }
    }

    public static function mappingMutations(): array
    {
        return ['foreign-owner' => ['owner'], 'wrong-component' => ['component'], 'random-color' => ['color'],
            'source-order' => ['order'], 'lost-block' => ['lost'], 'duplicate-anchor' => ['duplicate'],
            'unapproved-prose' => ['prose'], 'unapproved-view' => ['view']];
    }

    #[DataProvider('mappingMutations')]
    public function test_style_metadata_mutations_cannot_rewrite_content_or_extend_scope(string $mutation): void
    {
        $mapping = Design::load();
        switch ($mutation) {
            case 'owner': $mapping['targets'][0]['identity'] = 'ForeignOwner'; break;
            case 'component': $mapping['targets'][0]['blocks'][2]['component'] = 'mistakes-grid'; break;
            case 'color': $mapping['targets'][0]['blocks'][1]['color'] = 'gray'; break;
            case 'order': $mapping['targets'][0]['blocks'] = array_reverse($mapping['targets'][0]['blocks']); break;
            case 'lost': array_pop($mapping['targets'][0]['blocks']); break;
            case 'duplicate': $mapping['targets'][0]['blocks'][] = $mapping['targets'][0]['blocks'][1]; break;
            case 'prose': $mapping['targets'][0]['blocks'][1]['description'] = 'New unauthorized paragraph'; break;
            case 'view': $mapping['targets'][0]['blocks'][1]['view'] = 'foreign.executable.view'; break;
        }
        $this->expectException(\RuntimeException::class); Design::validate($mapping);
    }

    private function learningText(string $html): string
    {
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors(); $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//script|//style') as $node) { $node->parentNode->removeChild($node); }
        return trim(preg_replace('/\s+/u', ' ', $dom->textContent));
    }

    #[DataProvider('owners')]
    public function test_all_native_renderers_preserve_independent_before_text_and_all_fields_after_style_hints(string $identity): void
    {
        $target = Design::sourceTargets()[$identity];
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            $block = $this->block($target, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $this->presentation($target, $block, $data);
            $decorated = Design::decorate($block, $data, $presentation, true);
            $view = TheoryPresentation::nativeView($source['type']);
            if (in_array($source['type'], ['hero', 'navigation-chips'], true)) {
                // Hero/navigation are rendered by theory/show, not the native block dispatcher.
                self::assertNull($view); self::assertSame($data, $decorated['data']);
                continue;
            }
            self::assertNotNull($view, $identity.' '.$slot.' unrecognized native component');
            $args = ['block' => $block, 'data' => $presentation['data'] ?? $data, 'practiceQuestions' => collect(), 'lessonLinks' => []];
            $before = view($view, $args + ['m42Design' => null])->render();
            $after = view($view, $args + ['m42Design' => $decorated['m42_native_design']])->render();
            self::assertSame($this->learningText($before), $this->learningText($after), $identity.' '.$slot.' teaching text changed/doubled/lost');
            self::assertSame(substr_count($before, 'id="block-'.$block->id.'"'), substr_count($after, 'id="block-'.$block->id.'"'));
            foreach (['select', 'input', 'textarea', 'button', 'option', 'fieldset'] as $tag) {
                self::assertSame(preg_match_all('/<'.$tag.'\b/', $before), preg_match_all('/<'.$tag.'\b/', $after), $identity.' '.$slot.' '.$tag.' lost');
            }
        }
    }

    public function test_all_three_m41_reference_owners_are_outside_m42_even_with_valid_sources(): void
    {
        [, $package] = M41AuthoredTenseComparisonsPackage::load();
        foreach ($package['targets'] as $target) {
            foreach ($target['after']['page']['blocks'] as $slot => $source) {
                $block = $this->block($target, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
                self::assertNull(Design::binding($block, $data));
                self::assertNull(Design::decorate($block, $data, M41AuthoredTenseComparisonsPackage::presentation($block, $data), true));
            }
        }
    }

    private function pointerValue(array $data, string $pointer): string
    {
        $value = $data;
        foreach (explode('/', ltrim($pointer, '/')) as $part) {
            $value = $value[str_replace(['~1', '~0'], ['/', '~'], $part)];
        }
        self::assertIsString($value); return $value;
    }

    #[DataProvider('owners')]
    public function test_rich_em_annotations_preserve_every_source_byte_except_added_attributes_for_all_42_owners(string $identity): void
    {
        $target = Design::sourceTargets()[$identity];
        $mapping = array_values(array_filter(Design::load()['targets'], fn ($record) => $record['identity'] === $identity))[0];
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR); $plan = $mapping['blocks'][$slot];
            foreach ($plan['rich_fields'] as $pointer => $variants) {
                if (str_starts_with($pointer, '/details/')) {
                    $values = [];
                    foreach ($target['plans'][$slot - 1]['points'] ?? [] as $pointIndex => $point) {
                        $fragmentId = 'block-'.$target['plans'][$slot - 1]['key'].'-point-'.($pointIndex + 1).'-detail';
                        if ($pointer === '/details/'.$fragmentId && ($point['detail'] ?? '') !== '') { $values[] = $point['detail']; }
                    }
                    self::assertCount(1, $values, 'An annotation detail pointer must bind its existing exact point fragment.');
                } else {
                    $values = [$this->pointerValue($data, $pointer)];
                }
                if (preg_match('~^/sections/(\d+)/description$~', $pointer, $match)
                    && ($target['plans'][$slot - 1]['points'][(int) $match[1]]['detail'] ?? '') !== '') {
                    $values[] = $target['plans'][$slot - 1]['points'][(int) $match[1]]['basic'];
                }
                foreach (array_unique($values) as $html) {
                    $after = Design::richFragment($html, $plan, $pointer);
                    preg_match_all('~<em\b[^>]*>~u', $html, $beforeTags);
                    preg_match_all('~<em\b[^>]*>~u', $after, $afterTags);
                    self::assertCount(count($beforeTags[0]), $afterTags[0]);
                    $variant = array_values(array_filter($variants, fn ($candidate) => $candidate['sha256'] === hash('sha256', $html)))[0];
                    foreach ($afterTags[0] as $index => $tag) {
                        $language = in_array($index, $variant['uk_em_indices'], true) ? 'uk-template' : 'en';
                        self::assertStringContainsString('data-m42-em-language="'.$language.'"', $tag);
                        self::assertStringStartsWith(substr($beforeTags[0][$index], 0, -1), $tag);
                        if ($language === 'en') { self::assertStringContainsString('lang="en"', $tag); }
                    }
                    $index = -1;
                    $restored = preg_replace_callback('~<em\b[^>]*>~u', function () use (&$index, $beforeTags) {
                        return $beforeTags[0][++$index];
                    }, $after);
                    self::assertSame($html, $restored, 'Annotation cannot rewrite punctuation/prose/tags/links/source order.');
                    self::assertSame($html, Design::richFragment($html, null, $pointer));
                    self::assertSame($html, Design::richFragment($html, $plan, '/foreign/pointer'));
                    self::assertSame($html.' altered', Design::richFragment($html.' altered', $plan, $pointer));
                    $tampered = $plan; $tampered['color'] = 'gray';
                    self::assertSame($html, Design::richFragment($html, $tampered, $pointer));
                }
            }
        }
    }

    public function test_exactly_four_explicit_mixed_language_em_exceptions_without_language_heuristics(): void
    {
        $exceptions = [];
        foreach (Design::load()['targets'] as $target) {
            foreach ($target['blocks'] as $block) {
                foreach ($block['rich_em_exceptions'] as $pointer => $indices) {
                    foreach (array_keys($indices) as $index) { $exceptions[] = [$target['slug'], $block['source_index'], $pointer, $index]; }
                }
            }
        }
        self::assertSame([
            ['advanced-linking-devices', 5, '/sections/0/description', 0],
            ['advanced-linking-devices', 5, '/sections/0/description', 1],
            ['ellipsis-substitution-and-reference', 3, '/sections/3/description', 2],
            ['c2-mixed-revision', 2, '/sections/4/description', 2],
        ], $exceptions);
    }

    #[DataProvider('owners')]
    public function test_real_dispatcher_hooks_preserve_complete_text_existing_details_controls_and_unique_anchors_for_all42(string $identity): void
    {
        $target = Design::sourceTargets()[$identity]; $expectedDetails = 0; $actualDetails = 0;
        foreach ($target['after']['page']['blocks'] as $slot => $source) {
            if (in_array($source['type'], ['hero', 'navigation-chips'], true)) { continue; }
            $block = $this->block($target, $slot); $data = json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR);
            $presentation = $this->presentation($target, $block, $data);
            $plan = Design::decorate($block, $data, $presentation, true)['m42_native_design'];
            $args = ['block' => $block, 'practiceQuestions' => collect(), 'lessonLinks' => []];
            $before = view('theory.partials.content-block', $args + ['m42StyleContext' => false])->render();
            $after = view('theory.partials.content-block', $args + ['m42StyleContext' => true])->render();
            self::assertStringNotContainsString('data-m42-native-design=', $before, 'Course/non-theory context remains unchanged.');
            self::assertStringContainsString('data-m42-native-design="v1"', $after, $identity.' '.$slot.' actual dispatcher hook missing');
            self::assertStringContainsString('data-m42-native-kind="'.$source['type'].'"', $after);
            self::assertStringContainsString('data-m42-color="'.($plan['color'] ?? 'slate').'"', $after);
            self::assertSame($this->learningText($before), $this->learningText($after), 'Actual hook loses/doubles/reorders learner text.');
            preg_match_all('/\bid="([^"]+)"/', $before, $beforeIds);
            preg_match_all('/\bid="([^"]+)"/', $after, $afterIds);
            self::assertSame($beforeIds[1], $afterIds[1], 'Existing anchors/order must remain exact.');
            self::assertCount(count($afterIds[1]), array_unique($afterIds[1]), 'A block may not duplicate any anchor.');
            foreach (['select', 'input', 'textarea', 'button', 'option', 'fieldset'] as $tag) {
                self::assertSame(preg_match_all('/<'.$tag.'\b/', $before), preg_match_all('/<'.$tag.'\b/', $after), 'Actual hook loses '.$tag);
            }
            $expectedDetails += count($presentation['points'] ?? []);
            $actualDetails += substr_count($after, 'data-theory-details');
            foreach ($presentation['points'] ?? [] as $point) {
                self::assertStringContainsString('data-theory-section="'.$point['key'].'"', $after);
                foreach ($point['fragments'] as $fragment) {
                    self::assertStringContainsString('id="'.$fragment['id'].'"', $after);
                    if (str_contains($fragment['value'], '<em')) {
                        $annotated = Design::richFragment($fragment['value'], $plan, '/details/'.$fragment['id']);
                        self::assertStringContainsString('data-m42-em-language="en"', $annotated);
                        self::assertStringContainsString('data-m42-em-language="en"', $after);
                    }
                }
            }
        }
        self::assertSame($expectedDetails, $actualDetails, 'Teacher details are counted separately from practice answer keys.');
    }

    public static function mixedFields(): array
    {
        $pp = 'Database\\Seeders\\Page_V3\\Tenses\\TensesPresentPerfectVsPresentPerfectContinuousTheorySeeder';
        $narrative = 'Database\\Seeders\\Page_V3\\Tenses\\TensesNarrativeTensesTheorySeeder';
        return [
            'perfect-form-affirmative' => [$pp, 2, '/items/0/subtitle', 'She has labelled the boxes.'],
            'perfect-form-negative' => [$pp, 2, '/items/1/subtitle', 'She has not labelled the boxes yet.'],
            'perfect-form-question' => [$pp, 2, '/items/2/subtitle', 'Have you labelled the boxes?'],
            'continuous-form-affirmative' => [$pp, 2, '/items/3/subtitle', 'She has been labelling boxes for an hour.'],
            'continuous-form-negative' => [$pp, 2, '/items/4/subtitle', 'She has not been sleeping well lately.'],
            'continuous-form-question' => [$pp, 2, '/items/5/subtitle', 'How long have you been labelling boxes?'],
            'perfect-correction-has' => [$pp, 4, '/items/0/right', '✅ He has checked the list.'],
            'perfect-correction-know' => [$pp, 4, '/items/1/right', '✅ I have known Vera since April.'],
            'continuous-correction-been' => [$pp, 4, '/items/2/right', '✅ How long have they been waiting?'],
            'continuous-correction-for' => [$pp, 4, '/items/3/right', '✅ We have been packing for two hours.'],
            'narrative-simple-forms' => [$narrative, 2, '/items/0/subtitle', 'She opened the gate. / She did not open the gate. / Did she open the gate?'],
            'narrative-continuous-forms' => [$narrative, 2, '/items/1/subtitle', 'They were waiting. / They were not waiting. / Were they waiting?'],
            'narrative-perfect-forms' => [$narrative, 2, '/items/2/subtitle', 'She had left. / She had not left. / Had she left?'],
            'narrative-correction-did' => [$narrative, 4, '/items/0/right', '✅ Did Marta open the cupboard?'],
            'narrative-correction-were' => [$narrative, 4, '/items/1/right', '✅ The visitors were waiting when we arrived.'],
            'narrative-correction-v3' => [$narrative, 4, '/items/2/right', '✅ The guide had gone home before I called.'],
        ];
    }

    #[DataProvider('mixedFields')]
    public function test_each_of16_explicit_mixed_fields_preserves_all_source_bytes_and_keeps_uk_outside_english_font_scope(
        string $identity, int $slot, string $pointer, string $expectedEnglish
    ): void {
        $target = Design::sourceTargets()[$identity]; $block = $this->block($target, $slot);
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR); $original = $data;
        $source = $this->pointerValue($data, $pointer);
        self::assertSame($source, (string) \App\Support\TheoryInlineHtml::render($source), 'These sixteen known fields are source-plain, not inferred HTML.');
        $presentation = $this->presentation($target, $block, $data);
        $decorated = Design::decorate($block, $data, $presentation, true); $plan = $decorated['m42_native_design'];
        $mapping = $plan['rich_mixed_fields'][$pointer]; $parts = Design::mixedFragments($source, $mapping);
        self::assertSame($source, implode('', array_column($parts, 'text')));
        $english = array_values(array_filter($parts, fn ($part) => $part['role'] === 'en'));
        $translation = array_values(array_filter($parts, fn ($part) => $part['role'] === 'translation'));
        self::assertCount(1, $english); self::assertCount(1, $translation);
        self::assertSame($expectedEnglish, $english[0]['text']);
        self::assertDoesNotMatchRegularExpression('/[А-Яа-яІіЇїЄє]/u', $english[0]['text']);
        self::assertStringStartsWith(' — ', $translation[0]['text'], 'Original separator belongs to the preserved translation range.');
        $html = Design::richFragment($source, $plan, $pointer);
        self::assertSame($source, html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        self::assertStringContainsString('class="m42-english" lang="en"', $html);
        self::assertStringContainsString('class="m42-paired-translation" lang="uk"', $html);
        self::assertStringNotContainsString('font-mono', $html);
        self::assertSame($data, $original); self::assertSame($data, $decorated['data']);
        self::assertSame($source, Design::richFragment($source, null, $pointer));
        self::assertSame($source, Design::richFragment($source, $plan, '/foreign/mixed'));
        self::assertSame($source.' changed', Design::richFragment($source.' changed', $plan, $pointer));
        $fake = $plan; $fake['rich_mixed_fields'][$pointer]['ranges'][0]['length_bytes']++;
        self::assertSame($source, Design::richFragment($source, $fake, $pointer));
        $render = view(TheoryPresentation::nativeView($block->type), ['block' => $block, 'data' => $data,
            'm42Design' => $plan, 'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
        $dom = new DOMDocument; libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$render.'</body></html>', LIBXML_NONET); libxml_clear_errors();
        $xpath = new DOMXPath($dom); $matched = [];
        foreach ($xpath->query('//*[@data-m42-mixed-role="en"]') as $node) {
            if ($node->textContent === $expectedEnglish) { $matched[] = $node; }
        }
        self::assertCount(1, $matched, 'The actual native field must render exactly one own English example.');
        $parent = $matched[0]->parentNode; $ownTranslations = [];
        foreach ($xpath->query('.//*[@data-m42-mixed-role="translation"]', $parent) as $node) {
            if ($node->textContent === $translation[0]['text']) { $ownTranslations[] = $node; }
        }
        self::assertCount(1, $ownTranslations);
        self::assertSame('uk', $ownTranslations[0]->getAttribute('lang'));
        self::assertSame(0, $xpath->query('ancestor::*[@lang="en" or contains(@class,"m42-english") or contains(@class,"font-mono")]', $ownTranslations[0])->length,
            'A UK translation must never inherit a whole-field English/monospace wrapper.');
    }
}
