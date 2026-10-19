<?php

namespace Tests\Feature;

use App\Models\TextBlock;
use App\Support\TheoryLegacyAdapter;
use App\Support\TheorySection;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryLegacyMixedExamplesTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        app()->setLocale('en');
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function comparable(DOMNode $node): mixed
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            return $text === '' ? null : ['text' => $text];
        }
        if (!$node instanceof DOMElement) { return null; }
        $attrs = [];
        foreach ($node->attributes as $attribute) {
            // Canonical examples add explicit language semantics and a marker;
            // every original class, element, text and translation is compared.
            if (in_array($attribute->name, ['lang', 'data-theory-component'], true)) { continue; }
            $attrs[$attribute->name] = $attribute->value;
        }
        ksort($attrs);
        $children = [];
        foreach ($node->childNodes as $child) {
            $value = $this->comparable($child);
            if ($value !== null) { $children[] = $value; }
        }
        return ['tag' => $node->tagName, 'attributes' => $attrs, 'children' => $children];
    }

    public function test_mixed_scalar_and_array_examples_keep_legacy_rendering_without_type_errors(): void
    {
        $examples = [
            'This legacy scalar was not displayed.', '<script>notExecutable()</script>', '', null, false, 42,
            ['en' => 'I <strong>can</strong> read.', 'ua' => 'Я <em>можу</em> читати.'],
            ['en' => 'I can swim.'], ['ua' => 'Лише переклад.'], [],
        ];
        $data = ['title' => 'Legacy examples', 'sections' => [['label' => 'Examples', 'examples' => $examples]]];
        $snapshot = serialize($data);
        $block = new TextBlock;
        $block->forceFill(['id' => 991, 'type' => 'usage-panels', 'body' => json_encode($data)]);
        $block->setRelation('tags', collect());
        $block->setRelation('page', null);
        $before = $this->xpath(view('courses.compatibility.theory.usage-panels', compact('block', 'data'))->render());
        $after = $this->xpath(view('engram.theory.blocks-v3.usage-panels', ['block' => $block, 'data' => $data, 'theoryCanonical' => true])->render());
        $selector = '//div[contains(concat(" ", normalize-space(@class), " "), " theory-example ")]';
        $oldExamples = $before->query($selector);
        $newExamples = $after->query($selector);
        self::assertSame(count($examples), $oldExamples->length);
        self::assertSame($oldExamples->length, $newExamples->length);
        foreach ($oldExamples as $index => $oldExample) {
            self::assertSame($this->comparable($oldExample), $this->comparable($newExamples->item($index)), 'Original example '.$index);
        }
        self::assertSame(0, $after->query('//script')->length);
        self::assertStringNotContainsString('This legacy scalar was not displayed.', $after->query('//body')->item(0)->textContent);
        foreach (array_slice($examples, 0, 6) as $scalar) {
            $node = TheoryLegacyAdapter::example($scalar);
            self::assertSame('', $node['en']->toHtml());
            self::assertSame('', $node['uk']->toHtml());
        }
        self::assertSame($snapshot, serialize($data));
    }

    public function test_actual_localized_associative_sections_keep_their_original_unlabeled_articles(): void
    {
        $fixtures = [
            'NounsArticlesQuantity/NounsArticlesQuantityArticlesWithGeographicalNamesTheorySeeder',
            'PassiveVoice/Basics/PassiveVoiceNegativesQuestionsTheorySeeder',
            'PassiveVoice/Tenses/PassiveVoicePastSimpleTheorySeeder',
        ];
        $checked = 0;
        foreach ($fixtures as $fixture) {
            $localization = json_decode(file_get_contents(base_path('database/seeders/Page_V3/'.$fixture.'/localizations/en.json')), true, flags: JSON_THROW_ON_ERROR);
            foreach ($localization['blocks'] as $config) {
                $data = json_decode($config['body'] ?? '', true);
                if (!is_array($data['sections'] ?? null) || array_is_list($data['sections'])) { continue; }
                $snapshot = serialize($data);
                $block = new TextBlock;
                $block->forceFill(['id' => 992 + $checked, 'type' => 'usage-panels', 'body' => $config['body']]);
                $block->setRelation('tags', collect());
                $block->setRelation('page', null);
                $before = $this->xpath(view('courses.compatibility.theory.usage-panels', compact('block', 'data'))->render());
                $after = $this->xpath(view('engram.theory.blocks-v3.usage-panels', ['block' => $block, 'data' => $data, 'theoryCanonical' => true])->render());
                $selector = '//article[contains(concat(" ", normalize-space(@class), " "), " theory-item ")]';
                $oldItems = $before->query($selector);
                $newItems = $after->query($selector);
                self::assertSame(count($data['sections']), $oldItems->length, $fixture);
                self::assertSame($oldItems->length, $newItems->length, $fixture);
                foreach ($oldItems as $index => $oldItem) {
                    self::assertSame($this->comparable($oldItem), $this->comparable($newItems->item($index)), $fixture.' original article '.$index);
                }
                self::assertSame(0, $after->query('//article//span[contains(@class,"rounded-full")]')->length, 'Do not invent numeric labels for associative legacy data');
                self::assertSame($snapshot, serialize($data));
                $checked++;
            }
        }
        self::assertSame(3, $checked, 'Exercise each actual EN regression source');
    }

    public function test_legacy_named_collection_keys_keep_matching_point_details_and_numeric_labels(): void
    {
        $detailHtml = '<p id="keyed-detail-content">Preserved detail.</p>';
        $main = '<p>Basic.</p>';
        $full = $main.$detailHtml;
        $section = TheorySection::resolve('keyed-point', 'Detail', new HtmlString($full), null, [
            'source_key' => 'keyed-point', 'source_revision' => hash('sha256', $full), 'main' => new HtmlString($main),
            'detail' => new HtmlString($detailHtml), 'detail_references' => ['keyed-detail-content'],
        ], nativeHtml5: true);
        self::assertNull($section->diagnosticCode);
        foreach ([
            'usage-panels' => ['sections' => ['named' => ['description' => 'Rule text.']]],
            'mistakes-grid' => ['items' => ['named' => ['wrong' => 'Wrong.', 'right' => 'Right.']]],
            'forms-grid' => ['items' => ['named' => ['title' => 'Formula']]],
            'comparison-table' => ['rows' => ['named' => ['en' => 'Example.', 'ua' => 'Приклад.', 'note' => 'Note.']]],
        ] as $type => $data) {
            $block = new TextBlock;
            $block->forceFill(['id' => 997, 'type' => $type]);
            $block->setRelation('tags', collect());
            $block->setRelation('page', null);
            $after = $this->xpath(view('engram.theory.blocks-v3.'.$type, ['block' => $block, 'data' => $data,
                'theoryCanonical' => true, 'pointSections' => ['named' => $section]])->render());
            self::assertSame(1, $after->query('//*[@data-theory-point-index="named"]')->length, $type);
            self::assertSame(1, $after->query('//details//*[@id="keyed-detail-content"]')->length, $type);
        }
        foreach (['usage-panels' => 'sections', 'mistakes-grid' => 'items'] as $type => $collection) {
            $block = (object) ['id' => 998, 'type' => $type];
            $node = TheoryLegacyAdapter::section($block, [$collection => [4 => ['label' => 'Rule'], 9 => ['label' => 'Next rule']]]);
            self::assertSame([5, 10], array_column($node['items'], 'number'), 'Preserve original numeric keys, not new sequential numbers');
        }
    }
}
