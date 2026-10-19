<?php

namespace Tests\Feature;

use App\Http\Middleware\AddCanonicalUrl;
use App\Http\Middleware\ApplySeoRobots;
use App\Http\Middleware\ApplySiteMode;
use App\Models\Page;
use App\Models\TextBlock;
use App\Services\TheoryCourseManifestService;
use App\Services\VirtualSavedTest;
use App\Support\Database\JsonPageSeeder;
use App\Support\PageMetadata;
use App\Support\TextBlock\TextBlockUuidGenerator;
use App\Support\TheoryEditorialDescriptions;
use App\Support\TheoryPageTestSlug;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Content contracts only: no real question banks or working database are seeded. */
class LinkingWordsContentPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const CATEGORY = 'clauses-and-linking-words';

    public static function lessons(): array
    {
        return [
            'reason-result-contrast' => [
                'LinkingWordsReasonResultContrastTheorySeeder',
                'linking-words-reason-result-contrast',
                'Linking Words for Reason, Result and Contrast',
                'B2',
                ['because', 'because of', 'due to', 'therefore', 'as a result', 'although', 'even though', 'despite', 'in spite of', 'however', 'nevertheless'],
            ],
            'advanced-linking' => [
                'AdvancedLinkingDevicesTheorySeeder',
                'advanced-linking-devices',
                'Advanced Linking Devices',
                'C1',
                ['therefore', 'consequently', 'moreover', 'furthermore', 'provided that', 'insofar as'],
            ],
            'concession-and-contrast' => [
                'ConcessiveAndContrastiveStructuresTheorySeeder',
                'concessive-and-contrastive-structures',
                'Concessive And Contrastive Structures',
                'C2',
                ['even though', 'even if', 'while', 'whereas', 'although', 'though', 'despite', 'in spite of', 'despite the fact that'],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
    }

    private function definitionPath(string $name): string
    {
        return database_path('seeders/Page_V3/ClausesAndLinkingWords/'.$name.'/definition.json');
    }

    private function definition(string $name): array
    {
        return json_decode(file_get_contents($this->definitionPath($name)), true, flags: JSON_THROW_ON_ERROR);
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $before = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($before);
        }

        return new DOMXPath($document);
    }

    /** Exercise real SEO middleware in memory; no HTTP request is sent to production. */
    private function seoResponse(string $html, string $slug, string $origin): Response
    {
        $request = Request::create($origin.'/theory/'.self::CATEGORY.'/'.$slug);
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => null);

        return app(ApplySiteMode::class)->handle($request, fn ($request) =>
            app(ApplySeoRobots::class)->handle($request, fn ($request) =>
                app(AddCanonicalUrl::class)->handle($request, fn () =>
                    new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8'])
                )
            )
        );
    }

    #[DataProvider('lessons')]
    public function test_sources_keep_their_identity_and_offer_complete_self_checks(
        string $name, string $slug, string $title, string $level, array $constructions
    ): void {
        $definition = $this->definition($name);
        $before = json_decode(file_get_contents(database_path('content-patches/m11-linking-words-before.json')), true, flags: JSON_THROW_ON_ERROR)['definitions'][$name];
        $unchangedFields = static function (array $data): array {
            unset($data['page']['subtitle_html'], $data['page']['subtitle_text']);
            foreach ($data['page']['blocks'] as &$block) {
                unset($block['body'], $block['heading']);
            }
            unset($block);

            return $data;
        };
        self::assertSame($unchangedFields($before), $unchangedFields($definition), 'Only subtitle and existing body/heading content may change.');
        $page = $definition['page'];
        $seeder = 'Database\\Seeders\\Page_V3\\ClausesAndLinkingWords\\'.$name;
        self::assertSame('page', $definition['content_type']);
        self::assertSame('theory', $definition['type']);
        self::assertSame($slug, $definition['slug']);
        self::assertSame($seeder, $definition['seeder']['class']);
        self::assertSame($title, $page['title']);
        self::assertSame('uk', $page['locale']);
        self::assertSame(self::CATEGORY, $page['category']['slug']);
        self::assertSame('uk', $page['category']['language']);
        self::assertSame('theory', $page['category']['type']);
        self::assertSame([$title, 'English Sentence Builder '.$level, 'Theory', $level], $page['tags']);
        self::assertSame(['hero', 'box'], array_column($page['blocks'], 'type'));
        self::assertSame(['header', 'left'], array_column($page['blocks'], 'column'));
        foreach ($page['blocks'] as $block) {
            self::assertArrayNotHasKey('uuid', $block);
            self::assertArrayNotHasKey('uuid_key', $block);
        }

        $hero = json_decode($page['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($level, $page['blocks'][0]['level']);
        self::assertSame($level, $hero['level']);
        self::assertMatchesRegularExpression('/[іїєґа-я]/ui', $hero['intro']);
        self::assertMatchesRegularExpression('/[іїєґа-я]/ui', $page['subtitle_text']);
        self::assertCount(3, $hero['rules']);
        foreach ($hero['rules'] as $rule) {
            self::assertMatchesRegularExpression('/[іїєґа-я]/ui', $rule['label']);
            self::assertMatchesRegularExpression('/[іїєґа-я]/ui', $rule['text']);
            self::assertNotEmpty($rule['example']);
        }

        $body = $page['blocks'][1]['body'];
        self::assertDoesNotMatchRegularExpression('/theory anchor|compact anchors?|lesson package|\{a\d+\}/i', $body.$hero['intro']);
        $plain = PageMetadata::plain($body);
        foreach ($constructions as $construction) {
            self::assertStringContainsString($construction, $plain);
        }
        $dom = $this->dom($body);
        $section = '//section[@id="self-check-'.$slug.'"]';
        self::assertSame(1, $dom->query($section)->length);
        self::assertSame(6, $dom->query($section.'/ol/li')->length);
        self::assertSame(6, $dom->query($section.'/details/ol/li')->length);
        self::assertSame(2, $dom->query($section.'//ol[contains(@style,"decimal")]')->length, 'Numbering must survive the current CSS reset.');
        self::assertSame('Ключ і пояснення', trim($dom->evaluate('string('.$section.'/details/summary)')));
        foreach ($dom->query($section.'/ol/li | '.$section.'/details/ol/li') as $item) {
            self::assertMatchesRegularExpression('/[іїєґа-я]/ui', $item->textContent);
        }
        self::assertGreaterThanOrEqual(5, $dom->query('//h4')->length);
        self::assertSame(0, $dom->query('//script | //iframe | //style')->length);
        self::assertStringNotContainsString('&lt;', $body);
        if ($level === 'B2') {
            self::assertSame(4, $dom->query('//table/thead/tr/th')->length);
            self::assertGreaterThanOrEqual(6, $dom->query('//table/tbody/tr')->length);
            self::assertSame(1, $dom->query('//table/parent::*[contains(@style,"overflow")]')->length);
        }

        $expectedLinks = array_values(array_map(
            fn (array $case): string => '/theory/'.self::CATEGORY.'/'.$case[1],
            array_filter(self::lessons(), fn (array $case): bool => $case[1] !== $slug)
        ));
        $actualLinks = [];
        foreach ($dom->query('//a[@href]') as $link) {
            $href = $link->getAttribute('href');
            $actualLinks[] = $href;
            $route = app('router')->getRoutes()->match(Request::create('http://gramlyze.loc'.$href));
            self::assertSame('theory.show', $route->getName());
            self::assertSame(self::CATEGORY, $route->parameter('categoryPath'));
        }
        sort($expectedLinks);
        sort($actualLinks);
        self::assertSame($expectedLinks, $actualLinks, 'Two distinct lesson links; no duplicate primary-test button.');
    }

    #[DataProvider('lessons')]
    public function test_fresh_import_renders_same_material_in_theory_and_course(
        string $name, string $slug, string $title, string $level, array $constructions
    ): void {
        $source = $this->definition($name);
        $path = $this->definitionPath($name);
        (new class($path) extends JsonPageSeeder
        {
            public function __construct(private readonly string $path) {}
            protected function definitionPath(): string { return $this->path; }
        })->run();
        $page = Page::where('seeder', $source['seeder']['class'])->sole();
        $blocks = TextBlock::where('page_id', $page->id)->orderBy('sort_order')->get();
        self::assertSame($title, $page->title);
        self::assertSame($slug, $page->slug);
        self::assertSame($source['page']['subtitle_text'], $page->text);
        self::assertSame(self::CATEGORY, $page->category->slug);
        self::assertSame(['uk' => 3], $blocks->countBy('locale')->all());
        self::assertSame([0, 1, 2], $blocks->pluck('sort_order')->all());
        $scope = $source['seeder']['class'].'::uk';
        self::assertSame([
            TextBlockUuidGenerator::generateWithKey($scope, 'subtitle'),
            TextBlockUuidGenerator::generate($scope, 1),
            TextBlockUuidGenerator::generate($scope, 2),
        ], $blocks->pluck('uuid')->all());
        self::assertSame([
            $source['page']['subtitle_html'],
            $source['page']['blocks'][0]['body'],
            $source['page']['blocks'][1]['body'],
        ], $blocks->pluck('body')->all());
        self::assertSame(0, DB::table('questions')->count());
        self::assertSame(0, DB::table('saved_grammar_tests')->count());

        $page->setRelation('textBlocks', $blocks);
        $publicTestSlug = TheoryPageTestSlug::forPage($page);
        self::assertSame(self::CATEGORY.'/'.$slug, $publicTestSlug);
        $testRoute = app('router')->getRoutes()->match(Request::create('http://gramlyze.loc/test/'.$publicTestSlug));
        self::assertSame('test.show', $testRoute->getName());
        self::assertSame($publicTestSlug, $testRoute->parameter('slug'));
        $test = (new VirtualSavedTest($title.' (Mixed A1-C2)', 'theory-page-'.$page->id.'-mixed-a1-c2', [
            '__meta' => ['theory_page_static_slug' => true],
        ]))->setPublicSlug($publicTestSlug);
        $theory = view('theory.show', [
            'page' => $page, 'categories' => collect(), 'categoryPages' => collect(),
            'selectedCategory' => $page->category, 'topicTests' => collect([$test]),
        ])->render();
        $course = view('courses.partials.theory-page-content', ['page' => $page])->render();

        $beforeDefinitions = json_decode(file_get_contents(database_path('content-patches/m11-linking-words-before.json')), true, flags: JSON_THROW_ON_ERROR);
        $beforeSource = $beforeDefinitions['definitions'][$name];
        $beforePage = clone $page;
        $beforePage->text = $beforeSource['page']['subtitle_text'];
        $beforePage->setRelation('textBlocks', $blocks->map(function (TextBlock $block) use ($beforeSource): TextBlock {
            $copy = clone $block;
            if ($copy->sort_order === 0) {
                $copy->body = $beforeSource['page']['subtitle_html'];
            } else {
                $prior = $beforeSource['page']['blocks'][$copy->sort_order - 1];
                $copy->body = $prior['body'];
                $copy->heading = $prior['heading'] ?? null;
            }

            return $copy;
        }));
        $beforeHtml = view('theory.show', [
            'page' => $beforePage, 'categories' => collect(), 'categoryPages' => collect(),
            'selectedCategory' => $page->category, 'topicTests' => collect([$test]),
        ])->render();
        $beforeDom = $this->dom($beforeHtml);
        $afterDom = $this->dom($theory);
        self::assertSame($beforeDom->evaluate('string(//h1)'), $afterDom->evaluate('string(//h1)'));
        self::assertSame($beforeDom->evaluate('string(//title)'), $afterDom->evaluate('string(//title)'));
        self::assertSame(TheoryEditorialDescriptions::forPageSeeder($source['seeder']['class'], 'uk'), $beforeDom->evaluate('string(//meta[@name="description"]/@content)'));
        self::assertNotSame($beforeDom->evaluate('string(//meta[@name="description"]/@content)'), $afterDom->evaluate('string(//meta[@name="description"]/@content)'));
        foreach (['http://gramlyze.loc', 'https://gramlyze.com'] as $origin) {
            $beforeResponse = $this->seoResponse($beforeHtml, $slug, $origin);
            $afterResponse = $this->seoResponse($theory, $slug, $origin);
            $beforeSeo = $this->dom($beforeResponse->getContent());
            $afterSeo = $this->dom($afterResponse->getContent());
            $canonical = 'https://gramlyze.com/theory/'.self::CATEGORY.'/'.$slug;
            self::assertSame($canonical, $beforeSeo->evaluate('string(//link[@rel="canonical"]/@href)'));
            self::assertSame($canonical, $afterSeo->evaluate('string(//link[@rel="canonical"]/@href)'));
            self::assertSame($beforeSeo->evaluate('string(//meta[@name="robots"]/@content)'), $afterSeo->evaluate('string(//meta[@name="robots"]/@content)'));
            self::assertSame($beforeResponse->headers->get('X-Robots-Tag'), $afterResponse->headers->get('X-Robots-Tag'));
            self::assertSame($origin === 'http://gramlyze.loc' ? 'noindex, nofollow, noarchive' : null, $afterResponse->headers->get('X-Robots-Tag'));
        }
        $evidencePath = storage_path('app/m11-'.$slug.'.html');
        IsolatedTestEnvironment::assertOwnedPath(dirname($evidencePath));
        File::put($evidencePath, $this->seoResponse($theory, $slug, 'http://gramlyze.loc')->getContent());
        File::put(storage_path('app/m11-course-'.$slug.'.html'), $course);
        fwrite(STDOUT, "\nM11_RENDER_FIXTURE ".$evidencePath."\n");
        $body = $source['page']['blocks'][1]['body'];
        $hero = json_decode($source['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        foreach ([$theory, $course] as $html) {
            $richBody = \App\Support\TheoryRichContent::render($body);
            self::assertNotNull($richBody);
            self::assertStringContainsString((string) $richBody, $html);
            self::assertStringContainsString($hero['intro'], $html);
            self::assertStringNotContainsString(htmlspecialchars($body), $html);
            self::assertSame(6, $this->dom($html)->query('//section[@id="self-check-'.$slug.'"]/details/ol/li')->length);
        }
        $dom = $this->dom($theory);
        self::assertSame($title, trim($dom->evaluate('string(//h1)')));
        self::assertSame(1, $dom->query('//h1')->length);
        // Existing test card has its title link and exactly one action button.
        self::assertSame(2, $dom->query('//a[@href="http://gramlyze.loc/test/'.$publicTestSlug.'"]')->length);
        $metadata = PageMetadata::theory($title, '', $hero['intro'], $source['seeder']['class'], 'uk');
        self::assertSame($title.' — правила | Gramlyze', $metadata['title']);
        self::assertNotSame(TheoryEditorialDescriptions::forPageSeeder($source['seeder']['class'], 'uk'), $metadata['description']);
        self::assertSame($metadata['title'], $dom->evaluate('string(//title)'));
        foreach (['//meta[@name="description"]', '//meta[@property="og:description"]', '//meta[@name="twitter:description"]'] as $selector) {
            self::assertSame($metadata['description'], $dom->evaluate('string('.$selector.'/@content)'));
        }

        $manifest = app(TheoryCourseManifestService::class);
        $coursePath = $manifest->lessonUrl(self::CATEGORY, $slug);
        self::assertSame('/courses/english-grammar-theory/lesson/'.self::CATEGORY.'/'.$slug, $coursePath);
        self::assertSame('courses.theory.lesson', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc'.$coursePath))->getName());
        $resolved = $manifest->resolvePageForLesson(['page_id' => $page->id]);
        self::assertSame($page->id, $resolved->id);
        self::assertSame($blocks->pluck('uuid')->all(), $resolved->textBlocks->pluck('uuid')->all());
        self::assertSame($blocks->pluck('body')->all(), $resolved->textBlocks->pluck('body')->all());
    }
}
