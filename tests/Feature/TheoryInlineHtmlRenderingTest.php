<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TextBlock;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TheoryInlineHtmlRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        // This suite renders unsaved models only; fail before boot if it could use a working DB.
        foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $key => $expected) {
            if ((getenv($key) ?: ($_ENV[$key] ?? null)) !== $expected) {
                throw new \RuntimeException('Set '.$key.'='.$expected.' before running the rendering tests.');
            }
        }

        parent::setUp();

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $compiled = storage_path('app/seo-audit-20260907/test-richtext/views');
        File::ensureDirectoryExists($compiled);
        config(['view.compiled' => $compiled, 'cache.default' => 'array', 'session.driver' => 'array']);
        app()->setLocale('uk');
    }

    #[DataProvider('affectedDefinitions')]
    public function test_existing_affected_fields_render_as_markup_in_shared_theory_and_course_views(string $definitionPath): void
    {
        $definition = json_decode(File::get(base_path('database/seeders/Page_V3/'.$definitionPath.'/definition.json')), true, 512, JSON_THROW_ON_ERROR);
        $blocks = $definition['page']['blocks'] ?? $definition['description']['blocks'];
        $affected = 0;

        foreach ($blocks as $index => $blockData) {
            $type = $blockData['type'] ?? '';
            if (! in_array($type, ['hero', 'forms-grid', 'mistakes-grid', 'comparison-table', 'usage-panels'], true)) {
                continue;
            }
            $data = json_decode($blockData['body'], true, 512, JSON_THROW_ON_ERROR);
            if (! collect($this->richFields($type, $data))->contains(fn (string $value): bool => str_contains($value, '<strong>') || str_contains($value, '<span'))) {
                continue;
            }

            $affected++;
            foreach ($this->renderBoth($type, $data, $index + 1) as $interface => $html) {
                $dom = $this->document($html);
                $bodyText = $dom->getElementsByTagName('body')->item(0)->textContent;
                $this->assertDoesNotMatchRegularExpression('/<\/?(?:strong|span)\b[^>]*>/', $bodyText, $definitionPath.' '.$interface);
                $this->assertGreaterThan(0, (new DOMXPath($dom))->query('//strong|//span[contains(@class,"text-slate-500")]')->length, $definitionPath.' '.$interface);
            }
        }

        $this->assertGreaterThan(0, $affected, 'Expected one of the audited rich fields in '.$definitionPath);
    }

    public static function affectedDefinitions(): array
    {
        return array_map(fn (string $path): array => [$path], [
            'verb patterns' => 'VerbPatterns/VerbPatternsCategorySeeder',
            'verb to be future' => 'BasicGrammar/VerbToBe/VerbToBeFutureTheorySeeder',
            'choosing future form' => 'FutureForms/FutureFormsChoosingTheRightFutureFormTheorySeeder',
            'future perfect continuous' => 'FutureForms/FuturePerfectContinuous/FuturePerfectContinuousFormsTheorySeeder',
            'collective nouns' => 'NounsArticlesQuantity/NounsArticlesQuantityCollectiveNounsTheorySeeder',
            'one ones' => 'PronounsDemonstratives/PronounsDemonstrativesOneOnesTheorySeeder',
            'reciprocal pronouns' => 'PronounsDemonstratives/PronounsDemonstrativesReciprocalPronounsTheorySeeder',
            'past perfect' => 'Tenses/PastPerfect/PastPerfectFormsTheorySeeder',
            'past perfect continuous' => 'Tenses/PastPerfectContinuous/PastPerfectContinuousFormsTheorySeeder',
            'present perfect' => 'Tenses/PresentPerfect/PresentPerfectFormsTheorySeeder',
            'present perfect vs past simple' => 'Tenses/TensesPresentPerfectVsPastSimpleTheorySeeder',
            'used to would' => 'Tenses/TensesUsedToWouldTheorySeeder',
        ]);
    }

    #[DataProvider('richFieldLocations')]
    public function test_shared_rendering_removes_xss_but_keeps_plain_titles_escaped(string $type, array $data): void
    {
        foreach ($this->renderBoth($type, $data) as $interface => $html) {
            $dom = $this->document($html);
            $xpath = new DOMXPath($dom);
            $this->assertSame(0, $xpath->query('//script|//img|//*[@onclick or @onerror or @href="javascript:alert(1)"]')->length, $interface);
            $this->assertStringNotContainsString('alert(1)', $html, $interface);
            $this->assertSame(0, $xpath->query('//em[text()="plain-title"]')->length, $interface);
            $this->assertStringContainsString('<em>plain-title</em>', $dom->getElementsByTagName('body')->item(0)->textContent, $interface);
            $this->assertGreaterThan(0, $xpath->query('//strong[text()="безпечне"]')->length, $interface);
        }
    }

    public static function richFieldLocations(): array
    {
        $payload = '<script>alert(1)</script><strong onclick="alert(1)">безпечне</strong><a href="javascript:alert(1)">посилання</a><img src=x onerror="alert(1)">';
        $title = '<em>plain-title</em>';

        return [
            'subtitle' => ['forms-grid', ['title' => $title, 'items' => [['title' => 'Правило', 'subtitle' => $payload]]]],
            'wrong' => ['mistakes-grid', ['title' => $title, 'items' => [['wrong' => $payload]]]],
            'row en' => ['comparison-table', ['title' => $title, 'rows' => [['en' => $payload]]]],
            'row ua' => ['comparison-table', ['title' => $title, 'rows' => [['ua' => $payload]]]],
            'example en' => ['usage-panels', ['title' => $title, 'sections' => [['examples' => [['en' => $payload]]]]]],
            'example ua' => ['usage-panels', ['title' => $title, 'sections' => [['examples' => [['ua' => $payload]]]]]],
            'hero example' => ['hero', ['rules' => [['label' => $title, 'example' => $payload]]]],
        ];
    }

    private function richFields(string $type, array $data): array
    {
        return match ($type) {
            'hero' => array_column($data['rules'] ?? [], 'example'),
            'forms-grid' => array_column($data['items'] ?? [], 'subtitle'),
            'mistakes-grid' => array_column($data['items'] ?? [], 'wrong'),
            'comparison-table' => array_merge(array_column($data['rows'] ?? [], 'en'), array_column($data['rows'] ?? [], 'ua')),
            'usage-panels' => collect($data['sections'] ?? [])->flatMap(fn (array $section): array => array_merge(
                array_column($section['examples'] ?? [], 'en'), array_column($section['examples'] ?? [], 'ua')
            ))->all(),
        };
    }

    private function renderBoth(string $type, array $data, int $id = 1): array
    {
        $block = new TextBlock(['uuid' => 'inline-test-'.$id, 'type' => $type, 'body' => json_encode($data), 'locale' => 'uk']);
        $block->id = $id;
        $block->setRelation('tags', collect());
        $page = new Page(['title' => 'Test page']);
        $page->setRelation('textBlocks', collect([$block]));
        $page->setRelation('tags', collect());

        if ($type === 'hero') {
            $pageHtml = view('theory.show', ['page' => $page, 'categories' => collect(), 'categoryPages' => collect()])->render();
            $dom = $this->document($pageHtml);
            $main = (new DOMXPath($dom))->query('//*[@data-theory-main]')->item(0);
            $this->assertNotNull($main);
            $theoryHtml = $dom->saveHTML($main);
        } else {
            $theoryHtml = view('engram.theory.blocks-v3.'.$type, ['block' => $block, 'data' => $data])->render();
        }

        return [
            'shared theory block' => $theoryHtml,
            'theory category content' => view('theory.partials.category-description', [
                'page' => $page,
                'categoryDescription' => ['blocks' => collect([$block])],
            ])->render(),
            'course content' => view('courses.partials.theory-page-content', ['page' => $page])->render(),
        ];
    }

    private function document(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }
}
