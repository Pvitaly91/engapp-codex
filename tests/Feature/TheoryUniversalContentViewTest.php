<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\M26DetailPackage;
use App\Support\M26InteractivePractice;
use App\Support\M26PointDetails;
use App\Support\M27LinkingWordsPackage;
use App\Support\M42NativeDesignPackage;
use App\Support\TheoryContentRenderer;
use App\Support\TheoryPresentation;
use App\Support\TheorySection;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** The new entry point must preserve the four approved pages, not invent a new design. */
class TheoryUniversalContentViewTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('uk');
        $this->withoutVite();
    }

    /** Frozen sources, not copied presentation HTML or the new resolver's output. */
    private function references(): array
    {
        [$master, $before] = M26DetailPackage::load();
        $references = [];
        foreach ($master['targets'] as $target) {
            if ($target['slug'] !== 'past-perfect-continuous-forms') {
                continue;
            }
            $definition = M26InteractivePractice::definition($target, $before['definitions'][$target['identity']]);
            $references[] = [
                'identity' => $target['identity'],
                'slug' => $target['slug'],
                'blocks' => $definition[$target['source_content_root']]['blocks'],
                'package' => 'm26',
            ];
        }
        [, $package] = M27LinkingWordsPackage::load();
        foreach ($package['targets'] as $target) {
            $references[] = [
                'identity' => $target['identity'],
                'slug' => $target['after']['slug'],
                'blocks' => $target['after']['page']['blocks'],
                'package' => 'm27',
            ];
        }
        self::assertSame([
            'past-perfect-continuous-forms',
            'linking-words-reason-result-contrast',
            'advanced-linking-devices',
            'concessive-and-contrastive-structures',
        ], array_column($references, 'slug'));

        return $references;
    }

    private function block(array $reference, int $slot, int $ownerIndex): TextBlock
    {
        $source = $reference['blocks'][$slot];
        $block = new TextBlock;
        $block->forceFill([
            'id' => 91000 + $ownerIndex * 100 + $slot,
            'uuid' => M26DetailPackage::uuid($reference['identity'], $source, $slot + 1),
            'seeder' => $reference['identity'],
            'locale' => 'uk',
            'sort_order' => $slot + 1,
            'type' => $source['type'],
            'body' => $source['body'],
            'column' => $source['column'] ?? null,
            'heading' => $source['heading'] ?? null,
            'level' => $source['level'] ?? null,
            'css_class' => $source['css_class'] ?? null,
        ]);
        $block->setRelation('tags', collect());
        $block->setRelation('page', null);

        return $block;
    }

    /**
     * Keep the pre-existing package and native-wrapper route as an independent
     * reference. Do not build expected output through TheoryContentSource.
     */
    private function oldBindings(array $reference, TextBlock $block): array
    {
        $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
        $presentation = $reference['package'] === 'm27'
            ? M27LinkingWordsPackage::presentation($block, $data) : null;
        if ($reference['package'] === 'm27') {
            self::assertNotNull($presentation, $reference['slug'].' exact source binding');
            $decorated = M42NativeDesignPackage::decorate($block, $data, $presentation, true);
            self::assertNotNull($decorated, $reference['slug'].' exact native design binding');
            $presentation = $decorated;
        }
        $bindings = [
            'theoryCanonical' => true,
            'block' => $block,
            'data' => $presentation['data'] ?? $data,
            'm42Design' => $presentation['m42_native_design'] ?? null,
            'practiceQuestions' => collect(),
            'lessonLinks' => [],
        ];
        $nativeView = TheoryPresentation::nativeView($block->type);
        self::assertNotNull($nativeView);
        $basic = $this->oldRender($nativeView, $bindings);
        $points = $presentation['points'] ?? M26PointDetails::fragmentsFor($block, $data);
        $sections = [];
        foreach ($points ?? [] as $index => $point) {
            $detail = '';
            foreach ($point['fragments'] as $fragment) {
                $detail .= $this->oldRender('theory.partials.point-detail-fragment', [
                    'fragment' => $fragment,
                    'm42Design' => $bindings['m42Design'],
                    'theoryCanonical' => true,
                ]);
            }
            $section = TheorySection::resolve(
                $point['key'],
                $point['title'],
                new HtmlString($basic.$detail),
                null,
                [
                    'source_key' => $point['key'],
                    'source_revision' => hash('sha256', $basic.$detail),
                    'main' => new HtmlString($basic),
                    'detail' => new HtmlString($detail),
                    'detail_references' => array_column($point['fragments'], 'id'),
                ],
                nativeHtml5: true,
            );
            self::assertNull($section->diagnosticCode, $reference['slug'].' '.$point['key']);
            self::assertNotNull($section->detail);
            $sections[$index] = $section;
        }
        $bindings['pointSections'] = $sections;

        return $bindings;
    }

    private function oldRender(string $view, array $bindings): string
    {
        app('view')->flushState();

        return view($view, $bindings)->render();
    }

    private function universalRender(string $view, array $bindings): string
    {
        app('view')->flushState();

        return TheoryContentRenderer::render($view, $bindings);
    }

    private function dispatcher(TextBlock $block): string
    {
        return $this->oldRender('theory.partials.content-block', [
            'block' => $block,
            'theoryCanonical' => true,
            'm42StyleContext' => true,
            'practiceQuestions' => collect(),
            'lessonLinks' => [],
        ]);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    /** Preserve all classes, IDs, ARIA, bindings and learner text; normalize whitespace only. */
    private function comparableDom(string $html): array
    {
        $walk = function (DOMNode $node) use (&$walk): mixed {
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));

                return $text === '' ? null : ['text' => $text];
            }
            if (!$node instanceof DOMElement) {
                return null;
            }
            $attributes = [];
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->name === 'class'
                    ? trim(preg_replace('/\s+/u', ' ', $attribute->value)) : $attribute->value;
            }
            ksort($attributes);
            $children = [];
            foreach ($node->childNodes as $child) {
                $value = $walk($child);
                if ($value !== null) {
                    $children[] = $value;
                }
            }

            return ['tag' => $node->tagName, 'attributes' => $attributes, 'children' => $children];
        };

        return $walk($this->xpath($html)->query('//body')->item(0));
    }

    private function assertIndependentClosedDetails(string $html, array $sections): void
    {
        $xpath = $this->xpath($html);
        $details = $xpath->query('//details[@data-theory-details]');
        self::assertSame(count($sections), $details->length);
        self::assertSame(0, $xpath->query('//details[@data-theory-details]//details[@data-theory-details]')->length);
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $element) {
            $id = $element->getAttribute('id');
            self::assertNotContains($id, $ids, 'No duplicate public or control anchors.');
            $ids[] = $id;
        }
        foreach ($sections as $section) {
            $matches = $xpath->query('//details[@data-theory-details and summary[@id="'.$section->detailsId().'"]]');
            self::assertSame(1, $matches->length);
            $detail = $matches->item(0);
            self::assertFalse($detail->hasAttribute('open'));
            foreach ($section->detailReferences as $reference) {
                self::assertSame(1, $xpath->query('.//*[@id="'.$reference.'"]', $detail)->length,
                    'An author fragment belongs only to its own disclosure.');
            }
        }
    }

    public function test_four_approved_pages_keep_the_exact_native_dom_through_universal_rendering(): void
    {
        $counts = [];
        foreach ($this->references() as $ownerIndex => $reference) {
            $counts[$reference['slug']] = 0;
            foreach ($reference['blocks'] as $slot => $source) {
                $nativeView = TheoryPresentation::nativeView($source['type']);
                if ($nativeView === null || $source['type'] === 'practice-set') {
                    continue;
                }
                $block = $this->block($reference, $slot, $ownerIndex);
                $bindings = $this->oldBindings($reference, $block);
                $originalBody = $block->body;
                $originalData = $bindings['data'];
                $before = $this->oldRender($nativeView, $bindings);
                $after = $this->universalRender($nativeView, $bindings);
                self::assertSame($this->comparableDom($before), $this->comparableDom($after),
                    $reference['slug'].' source block '.$slot);
                $this->assertIndependentClosedDetails($after, $bindings['pointSections']);
                self::assertSame($originalBody, $block->body);
                self::assertSame($originalData, $bindings['data']);
                $counts[$reference['slug']]++;
            }
        }
        foreach ($counts as $slug => $count) {
            self::assertGreaterThan(0, $count, $slug.' must actually exercise the content route');
        }
    }

    public function test_real_dispatcher_preserves_reference_content_and_all_thirteen_m27_details(): void
    {
        $detailTotals = [];
        foreach ($this->references() as $ownerIndex => $reference) {
            $detailTotals[$reference['slug']] = 0;
            foreach ($reference['blocks'] as $slot => $source) {
                $nativeView = TheoryPresentation::nativeView($source['type']);
                if ($nativeView === null || $source['type'] === 'practice-set') {
                    continue;
                }
                $block = $this->block($reference, $slot, $ownerIndex);
                $bindings = $this->oldBindings($reference, $block);
                $expected = $this->oldRender($nativeView, $bindings);
                $actual = $this->dispatcher($block);
                self::assertSame($this->comparableDom($expected), $this->comparableDom($actual),
                    $reference['slug'].' dispatcher source block '.$slot);
                $this->assertIndependentClosedDetails($actual, $bindings['pointSections']);
                $detailTotals[$reference['slug']] += count($bindings['pointSections']);
            }
        }
        self::assertSame([
            'past-perfect-continuous-forms' => 12,
            'linking-words-reason-result-contrast' => 3,
            'advanced-linking-devices' => 7,
            'concessive-and-contrastive-structures' => 3,
        ], $detailTotals);
    }

    public function test_practice_is_passthrough_with_unchanged_raw_model_actions_and_six_items_per_page(): void
    {
        $practiceCount = 0;
        foreach ($this->references() as $ownerIndex => $reference) {
            foreach ($reference['blocks'] as $slot => $source) {
                if ($source['type'] !== 'practice-set') {
                    continue;
                }
                $block = $this->block($reference, $slot, $ownerIndex);
                $bindings = $this->oldBindings($reference, $block);
                $view = TheoryPresentation::nativeView($block->type);
                $before = $this->oldRender($view, $bindings);
                $after = $this->universalRender($view, $bindings);
                self::assertSame($before, $after, $reference['slug'].' practice must remain a byte-exact passthrough');
                $oldModel = $this->xpath($before)->query('//*[@x-data]')->item(0)->getAttribute('x-data');
                $newModel = $this->xpath($after)->query('//*[@x-data]')->item(0)->getAttribute('x-data');
                self::assertSame($oldModel, $newModel);
                self::assertStringContainsString('theoryPracticeSet(', $newModel);
                foreach (['selects', 'choices', 'inputs'] as $group) {
                    self::assertCount(2, $bindings['data'][$group]);
                    self::assertStringContainsString('check(\''.$group.'\')', $after);
                    self::assertStringContainsString('resetGroup(\''.$group.'\')', $after);
                }
                self::assertSame($source['body'], $block->body);
                $practiceCount++;
            }
        }
        self::assertSame(4, $practiceCount);
    }

    public function test_wrong_m27_identity_or_changed_teaching_text_keeps_complete_native_fallback(): void
    {
        foreach ($this->references() as $ownerIndex => $reference) {
            if ($reference['package'] !== 'm27') {
                continue;
            }
            foreach ($reference['blocks'] as $slot => $source) {
                if ($source['type'] !== 'usage-panels') {
                    continue;
                }
                $original = $this->block($reference, $slot, $ownerIndex);
                $originalData = json_decode($original->body, true, flags: JSON_THROW_ON_ERROR);
                $presentation = M27LinkingWordsPackage::presentation($original, $originalData);
                if (empty($presentation['points'])) {
                    continue;
                }
                $mutations = ['seeder' => 'ForeignOwner', 'locale' => 'en', 'uuid' => 'foreign-uuid', 'sort_order' => 999];
                foreach ($mutations + ['body' => null] as $field => $value) {
                    $block = clone $original;
                    if ($field === 'body') {
                        $changed = $originalData;
                        $changed['sections'][0]['description'] = 'Ручне уточнення. '.$changed['sections'][0]['description'];
                        $block->body = M26DetailPackage::json($changed);
                    } else {
                        $block->$field = $value;
                    }
                    $data = json_decode($block->body, true, flags: JSON_THROW_ON_ERROR);
                    self::assertNull(M27LinkingWordsPackage::presentation($block, $data));
                    self::assertNull(M42NativeDesignPackage::binding($block, $data));
                    $fallback = $this->oldRender(TheoryPresentation::nativeView($block->type), [
                        'theoryCanonical' => true, 'block' => $block, 'data' => $data,
                        'm42Design' => null, 'practiceQuestions' => collect(), 'lessonLinks' => [],
                    ]);
                    $actual = $this->dispatcher($block);
                    self::assertSame($this->comparableDom($fallback), $this->comparableDom($actual),
                        $reference['slug'].' full fallback after '.$field.' mismatch');
                    self::assertSame(0, $this->xpath($actual)->query('//details[@data-theory-details]')->length);
                    if ($field === 'body') {
                        self::assertStringContainsString('Ручне уточнення.', $actual);
                    }
                }
                break;
            }
        }
    }

    public function test_payload_fields_cannot_select_a_view_and_unknown_formats_remain_escaped(): void
    {
        $reference = $this->references()[0];
        $block = $this->block($reference, 1, 0);
        $bindings = $this->oldBindings($reference, $block);
        $view = TheoryPresentation::nativeView($block->type);
        $before = $this->universalRender($view, $bindings);
        $bindings['data']['view'] = 'theory.show';
        $bindings['data']['native_view'] = 'admin.dashboard';
        self::assertSame($this->comparableDom($before), $this->comparableDom($this->universalRender($view, $bindings)));

        $block->type = 'theory.show';
        $block->heading = '<script>heading()</script>';
        $block->body = '@php throw new RuntimeException("payload"); @endphp <script>payload()</script>';
        $html = $this->dispatcher($block);
        $xpath = $this->xpath($html);
        self::assertSame(1, $xpath->query('//*[@data-theory-render-fallback="unknown-format"]')->length);
        self::assertSame(0, $xpath->query('//script')->length);
        self::assertStringContainsString('&lt;script&gt;payload()', $html);
        self::assertStringContainsString('@php throw new RuntimeException', $html);
    }

    public function test_canonical_renderer_rejects_an_unknown_even_existing_view(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TheoryContentRenderer::render('theory.show', ['theoryCanonical' => true]);
    }
}
