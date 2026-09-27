<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\TheoryRichContent;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TheoryRichContentTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $key => $expected) {
            if ((getenv($key) ?: ($_ENV[$key] ?? null)) !== $expected) {
                throw new \RuntimeException('Set '.$key.'='.$expected.' before rendering tests.');
            }
        }
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        \Tests\Support\IsolatedTestEnvironment::assertOwnedPath(config('view.compiled'));
        app()->setLocale('uk');
    }

    #[DataProvider('lessonDefinitions')]
    public function test_six_lessons_keep_every_text_node_link_heading_and_exercise(string $path): void
    {
        $definition = $this->definition($path);
        $body = collect($definition['page']['blocks'])->firstWhere('type', 'box')['body'];
        $result = TheoryRichContent::render($body);
        $this->assertNotNull($result);
        $before = $this->document($body);
        $after = $this->document((string) $result);
        $beforeXpath = new DOMXPath($before);
        $afterXpath = new DOMXPath($after);
        $this->assertSame($before->getElementsByTagName('body')->item(0)->textContent, $after->getElementsByTagName('body')->item(0)->textContent);
        foreach (['h4', 'p', 'li', 'em', 'strong', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'details', 'summary', 'a', 'blockquote'] as $tag) {
            $this->assertSame($before->getElementsByTagName($tag)->length, $after->getElementsByTagName($tag)->length, $tag);
            foreach ($before->getElementsByTagName($tag) as $index => $node) {
                $rendered = $after->getElementsByTagName($tag)->item($index);
                $this->assertSame($node->textContent, $rendered->textContent, $tag.' '.$index);
                foreach ($node->attributes as $attribute) {
                    if (! in_array($attribute->name, ['style', 'class'], true)) {
                        $this->assertSame($attribute->value, $rendered->getAttribute($attribute->name), $tag.' '.$attribute->name);
                    }
                }
            }
        }
        $this->assertSame(1, $afterXpath->query('//section[contains(@class,"theory-rich-practice")][@id][@aria-label]/details[summary][ol]')->length);
        $this->assertSame(0, $afterXpath->query('//details[@open]')->length);
        $this->assertSame(1, $afterXpath->query('//section[contains(@class,"theory-rich-next")]')->length);
        $this->assertGreaterThanOrEqual(2, $afterXpath->query('//span[@class="theory-rich-number"]')->length);
        $this->assertSame($beforeXpath->query('//table')->length, $afterXpath->query('//div[contains(@class,"theory-rich-table")][@tabindex="0"]/table')->length);
        $this->assertSame(0, $afterXpath->query('//h4[@style]|//li[@style]|//ol[@style]|//summary[@style]|//th[@style]|//td[@style]')->length);
        $this->assertGreaterThan(0, $afterXpath->query('//em[contains(@class,"theory-rich-example")][@lang="en"]')->length);
    }

    #[DataProvider('lessonDefinitions')]
    public function test_both_renderers_use_scoped_shells_without_changing_models(string $path): void
    {
        $definition = $this->definition($path);
        $page = new Page(['title' => $definition['page']['title']]);
        $page->setRelation('tags', collect());
        $blocks = collect($definition['page']['blocks'])->map(function ($data, $index) {
            $block = new TextBlock($data);
            $block->id = $index + 1;
            $block->setRelation('tags', collect());

            return $block;
        });
        $page->setRelation('textBlocks', $blocks);
        $originalBodies = $blocks->pluck('body')->all();
        foreach (['theory.show', 'courses.partials.theory-page-content'] as $view) {
            $html = view($view, ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render();
            $xpath = new DOMXPath($this->document($html));
            $this->assertSame(1, $xpath->query('//section[@class="theory-rich-shell"]')->length, $view);
            $this->assertSame(1, $xpath->query('//article[@class="theory-rich-article"]/div[contains(@class,"theory-rich-content")]')->length, $view);
            $this->assertGreaterThan(0, $xpath->query('//*[contains(@class,"theory-rich-rules")]')->length, $view);
            $this->assertSame($originalBodies, $blocks->pluck('body')->all());
        }
    }

    public function test_unstructured_and_malformed_html_retain_legacy_rendering(): void
    {
        $plain = '<p>Ordinary <em>inline</em> content.</p>';
        $this->assertNull(TheoryRichContent::render($plain));
        $valid = '<h4>1. First</h4><p>Text.</p><h4>2. Second</h4><p>More.</p><section id="self-check-example"><h4>Check</h4><ol><li>Task</li></ol><details><summary>Answers</summary><ol><li>Answer</li></ol></details></section>';
        foreach ([str_replace('</li>', '', $valid), str_replace('</h4>', '</h3>', $valid), str_replace('self-check-example', 'ordinary-example', $valid), str_replace('<h4>2. Second</h4>', '<h4>Second</h4>', $valid)] as $malformed) {
            $this->assertNull(TheoryRichContent::render($malformed));
        }
        $block = new TextBlock(['type' => 'box', 'heading' => 'Legacy', 'body' => $plain]);
        $html = view('components.theory-rich-box', ['block' => $block, 'richContent' => null])->render();
        $this->assertStringContainsString($plain, $html);
        $this->assertStringContainsString('prose prose-sm mt-4 max-w-none leading-7', $html);
        $this->assertStringNotContainsString('theory-rich-', $html);
    }

    public function test_custom_attributes_styles_and_inline_fragments_are_preserved(): void
    {
        $html = '<h4 id="one" style="color:purple">1. First</h4><p><em>although</em> <em>I have finished.</em> <em lang="fr">Je suis ici.</em></p><h4>2. Second</h4><p><a href="/theory/example#part" data-note="yes">Next</a></p><section id="self-check-example" aria-label="Check"><h4>Check</h4><ol start="3"><li value="3">Task</li></ol><details data-note="key"><summary>Answers</summary><ol><li>Answer</li></ol></details></section>';
        $result = TheoryRichContent::render($html);
        $this->assertNotNull($result);
        $xpath = new DOMXPath($this->document((string) $result));
        $this->assertSame(1, $xpath->query('//h4[@id="one"][@style="color:purple"]')->length);
        $this->assertSame(1, $xpath->query('//em[text()="although"][not(@class)][not(@lang)]')->length);
        $this->assertSame(1, $xpath->query('//em[text()="I have finished."][@class="theory-rich-example"][@lang="en"]')->length);
        $this->assertSame(1, $xpath->query('//em[@lang="fr"][not(@class)]')->length);
        $this->assertSame(1, $xpath->query('//ol[@start="3"]/li[@value="3"]')->length);
        $this->assertSame(1, $xpath->query('//details[@data-note="key"]')->length);
        $this->assertSame(1, $xpath->query('//a[@href="/theory/example#part"][@data-note="yes"]')->length);
    }

    public function test_bilingual_hero_examples_preserve_delimiter_and_inline_safety(): void
    {
        $text = 'I have finished. — Я завершив.';
        $dom = $this->document((string) TheoryRichContent::example($text));
        $this->assertSame($text, $dom->getElementsByTagName('body')->item(0)->textContent);
        $this->assertSame(1, (new DOMXPath($dom))->query('//span[@class="theory-rich-example-en"][@lang="en"]')->length);
        $this->assertStringNotContainsString('alert', (string) TheoryRichContent::example('<script>alert(1)</script><strong>Safe</strong> — Переклад'));
        foreach (['Tea &amp; coffee. — Переклад.', 'Tea &#38; coffee. — Переклад.', 'Tea & coffee. — Переклад.'] as $entityExample) {
            $this->assertSame((string) \App\Support\TheoryInlineHtml::render($entityExample), (string) TheoryRichContent::example($entityExample));
        }
    }

    public function test_present_perfect_reference_keeps_existing_v3_renderers(): void
    {
        $definition = $this->definition('Tenses/PresentPerfect/PresentPerfectFormsTheorySeeder');
        $page = new Page(['title' => $definition['page']['title']]);
        $page->setRelation('tags', collect());
        $page->setRelation('textBlocks', collect($definition['page']['blocks'])->map(function ($data, $index) {
            $block = new TextBlock($data);
            $block->id = $index + 1;
            $block->setRelation('tags', collect());

            return $block;
        }));
        foreach (['theory.show', 'courses.partials.theory-page-content'] as $view) {
            $html = view($view, ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render();
            $this->assertStringNotContainsString('theory-rich-', $html);
            $this->assertStringContainsString('have + V3', $html);
            $this->assertStringContainsString('rounded-[30px] border p-6 shadow-card surface-card-strong', $html);
        }
    }

    public static function lessonDefinitions(): array
    {
        return array_map(fn (string $path): array => [$path], [
            'ClausesAndLinkingWords/LinkingWordsReasonResultContrastTheorySeeder',
            'ClausesAndLinkingWords/ConcessiveAndContrastiveStructuresTheorySeeder',
            'ClausesAndLinkingWords/AdvancedLinkingDevicesTheorySeeder',
            'BasicGrammar/WordOrder/InversionBasicsTheorySeeder',
            'BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder',
            'SentenceStructure/CleftSentencesBasicsTheorySeeder',
        ]);
    }

    private function definition(string $path): array
    {
        return json_decode(file_get_contents(base_path('database/seeders/Page_V3/'.$path.'/definition.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function document(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }
}
