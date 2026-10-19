<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Http\Controllers\TheoryController;
use App\Http\Middleware\AddCanonicalUrl;
use App\Http\Middleware\ApplySeoRobots;
use App\Http\Middleware\ApplySiteMode;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Services\TheoryCourseManifestService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Services\VirtualSavedTest;
use App\Support\Database\JsonPageSeeder;
use App\Support\PageMetadata;
use App\Support\TextBlock\TextBlockUuidGenerator;
use App\Support\TheoryPageTestSlug;
use App\Support\TheoryRichContent;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Isolated content/renderer contracts; never seeds working question banks. */
class PassiveReportingCausativeContentPackageTest extends TestCase
{
    use RebuildsComposeTestSchema;

    public static function lessons(): array
    {
        return [
            'reporting' => ['PassiveVoice/PassiveReportingStructuresTheorySeeder', 'passive-reporting-structures', 'Passive Reporting Structures', 'C1', 'passive-voice', 'Passive Reporting Structures'],
            'causative' => ['PassiveVoice/ComplexPassiveAndCausativeTheorySeeder', 'complex-passive-and-causative', 'Complex Passive and Causative', 'C1', 'passive-voice', 'Complex Passive and Causative'],
            'impersonal' => ['PassiveVoice/ComplexPassiveImpersonalStyleTheorySeeder', 'complex-passive-impersonal-style', 'Complex Passive Impersonal Style', 'C2', 'passive-voice', 'Complex Passive Impersonal Style'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        $this->withoutVite();
        app()->setLocale('uk');
        $parent = PageCategory::create(['title' => 'Basic Grammar', 'slug' => 'basic-grammar', 'language' => 'uk', 'type' => 'theory']);
        PageCategory::create(['title' => 'Word Order', 'slug' => 'word-order', 'language' => 'uk', 'type' => 'theory', 'parent_id' => $parent->id]);
        PageCategory::create(['title' => 'Passive Voice', 'slug' => 'passive-voice', 'language' => 'uk', 'type' => 'theory']);
    }

    private function definition(string $name): array
    {
        return json_decode(file_get_contents(database_path('seeders/Page_V3/'.$name.'/definition.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function before(string $seeder): array
    {
        return json_decode(file_get_contents(database_path('content-patches/m19-passive-reporting-causative-before.json')), true, flags: JSON_THROW_ON_ERROR)['definitions'][$seeder];
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function seoResponse(string $html, string $path, string $origin): Response
    {
        // Production profile is exercised entirely in memory, never over the network.
        $request = Request::create($origin.$path);
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => null);

        return app(ApplySiteMode::class)->handle($request, fn ($request) => app(ApplySeoRobots::class)->handle($request, fn ($request) => app(AddCanonicalUrl::class)->handle($request, fn () => new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8'])
        )
        )
        );
    }

    #[DataProvider('lessons')]
    public function test_sources_preserve_identity_and_complete_six_explained_checks(
        string $name, string $slug, string $title, string $level, string $categoryPath, string $h1
    ): void {
        $definition = $this->definition($name);
        $seeder = 'Database\\Seeders\\Page_V3\\'.str_replace('/', '\\', $name);
        $before = $this->before($seeder);
        $protected = static function (array $data): array {
            unset($data['page']['subtitle_html'], $data['page']['subtitle_text']);
            foreach ($data['page']['blocks'] as &$block) {
                unset($block['body'], $block['heading']);
            }
            unset($block);

            return $data;
        };
        self::assertSame($protected($before), $protected($definition));
        self::assertSame($seeder, $definition['seeder']['class']);
        self::assertSame($slug, $definition['slug']);
        self::assertSame($title, $definition['page']['title']);
        self::assertSame('uk', $definition['page']['locale']);
        self::assertSame(basename($categoryPath), $definition['page']['category']['slug']);
        self::assertSame(['hero', 'box'], array_column($definition['page']['blocks'], 'type'));
        self::assertSame(['header', 'left'], array_column($definition['page']['blocks'], 'column'));
        $hero = json_decode($definition['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($level, $hero['level']);
        self::assertSame($level, $definition['page']['blocks'][0]['level']);
        self::assertCount(3, $hero['rules']);
        foreach (array_merge([$definition['page']['subtitle_text'], $hero['intro']], array_column($hero['rules'], 'label'), array_column($hero['rules'], 'text'), array_column($hero['rules'], 'example')) as $ukText) {
            self::assertMatchesRegularExpression('/[а-яіїєґ]/ui', $ukText);
        }
        $body = $definition['page']['blocks'][1]['body'];
        self::assertDoesNotMatchRegularExpression('/theory anchor|compact anchors?|lesson package|\{a\d+\}|&lt;|&gt;|&amp;/i', $body.$hero['intro']);
        $dom = $this->dom($body);
        $section = '//section[@id="self-check-'.$slug.'"]';
        self::assertSame(1, $dom->query($section)->length);
        self::assertSame(6, $dom->query($section.'/ol[@data-self-checks]/li')->length);
        self::assertSame(6, $dom->query($section.'/details/ol[@data-self-check-answers]/li')->length);
        self::assertSame(2, $dom->query($section.'//ol')->length);
        self::assertNotNull(TheoryRichContent::render($body), 'Every actual source must opt into the rich renderer.');
        self::assertStringNotContainsString('theory-rich-', $body, 'Do not store generated presentation wrappers.');
        self::assertDoesNotMatchRegularExpression('/(?:color:|background:|<script|<style)/i', $body);
        self::assertSame('Ключ і пояснення', trim($dom->evaluate('string('.$section.'/details/summary)')));
        self::assertGreaterThanOrEqual(5, $dom->query('//h4')->length);
        self::assertSame(1, $dom->query('//table/parent::*[contains(@style,"overflow-x:auto")][@tabindex="0"]')->length);
        $minWidth = 1000;
        self::assertSame(1, $dom->query('//table[contains(@style,"min-width:'.$minWidth.'px")]')->length, 'Content-specific tables must scroll, not squash words.');
        $firstColumn = 250;
        self::assertSame(1, $dom->query('//table/thead/tr/th[1][contains(@style,"min-width:'.$firstColumn.'px")]')->length);
        self::assertStringNotContainsString($title, $hero['intro'], 'Do not repeat the title in the description.');
        self::assertSame('theory', $definition['page']['category']['type'] ?? $definition['type']);
        self::assertSame(0, $dom->query('//script | //style | //iframe | //form')->length);
        foreach ($dom->query($section.'/ol/li | '.$section.'/details/ol/li') as $item) {
            self::assertMatchesRegularExpression('/[а-яіїєґ]/ui', $item->textContent);
            self::assertDoesNotMatchRegularExpression('/^\s*[A-D]\.?\s*$/', $item->textContent, 'Keys explain the choice, not just a letter.');
        }
        $expected = match ($slug) {
            'passive-reporting-structures' => ['/theory/passive-voice/complex-passive-and-causative', '/theory/passive-voice/complex-passive-impersonal-style', '/theory/passive-voice/theory-passive-voice-formation-rules', '/theory/academic-english/hedging-and-cautious-language'],
            'complex-passive-and-causative' => ['/theory/passive-voice/passive-reporting-structures', '/theory/passive-voice/complex-passive-impersonal-style', '/theory/passive-voice/theory-passive-voice-get-passive', '/theory/formal-english/register-tone-and-paraphrase'],
            'complex-passive-impersonal-style' => ['/theory/passive-voice/passive-reporting-structures', '/theory/passive-voice/complex-passive-and-causative', '/theory/passive-voice/passive-voice-infinitives-gerund/theory-passive-voice-passive-infinitive', '/theory/clauses-and-linking-words/participle-clauses'],
        };
        $actual = [];
        foreach ($dom->query('//a[@href]') as $link) {
            $href = $link->getAttribute('href');
            $actual[] = $href;
            self::assertStringStartsWith('/theory/', $href);
            self::assertStringNotContainsString('?', $href);
            self::assertNotSame('', trim($link->textContent));
            self::assertSame('theory.show', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc'.$href))->getName());
        }
        sort($expected);
        sort($actual);
        self::assertSame(array_values($expected), $actual, 'Only verified, relevant lesson links; no duplicate primary-test link.');
    }

    #[DataProvider('lessons')]
    public function test_isolated_import_renders_material_and_preserves_urls_metadata_and_uuid_contracts(
        string $name, string $slug, string $title, string $level, string $categoryPath, string $h1
    ): void {
        $source = $this->definition($name);
        $path = database_path('seeders/Page_V3/'.$name.'/definition.json');
        (new class($path) extends JsonPageSeeder
        {
            public function __construct(private readonly string $path) {}

            protected function definitionPath(): string
            {
                return $this->path;
            }
        })->run();
        $page = Page::where('seeder', $source['seeder']['class'])->sole();
        $blocks = TextBlock::where('page_id', $page->id)->orderBy('sort_order')->get();
        self::assertSame($title, $page->title);
        // Live navigation can derive H1 from the subtitle, unlike a bare Blade render.
        $controller = (new \ReflectionClass(PageController::class))->newInstanceWithoutConstructor();
        $titleExtractor = new \ReflectionMethod($controller, 'extractLocalizedTitleFromSubtitle');
        self::assertSame($h1, $titleExtractor->invoke($controller, $source['page']['subtitle_html']));
        self::assertSame(
            $titleExtractor->invoke($controller, $this->before($source['seeder']['class'])['page']['subtitle_html']),
            $titleExtractor->invoke($controller, $source['page']['subtitle_html']),
            'Keep the actual controller-derived H1, not just Page.title.'
        );
        self::assertSame($slug, $page->slug);
        self::assertSame($source['page']['subtitle_text'], $page->text);
        self::assertSame(basename($categoryPath), $page->category->slug);
        self::assertSame(null, $page->category->parent?->slug);
        self::assertSame(['uk' => 3], $blocks->countBy('locale')->all());
        self::assertSame([0, 1, 2], $blocks->pluck('sort_order')->all());
        $scope = $source['seeder']['class'].'::uk';
        self::assertSame([
            TextBlockUuidGenerator::generateWithKey($scope, 'subtitle'),
            TextBlockUuidGenerator::generate($scope, 1),
            TextBlockUuidGenerator::generate($scope, 2),
        ], $blocks->pluck('uuid')->all());
        self::assertSame([$source['page']['subtitle_html'], $source['page']['blocks'][0]['body'], $source['page']['blocks'][1]['body']], $blocks->pluck('body')->all());
        self::assertSame(0, DB::table('questions')->count());
        self::assertSame(0, DB::table('saved_grammar_tests')->count());
        $page->setRelation('textBlocks', $blocks);
        $testSlug = TheoryPageTestSlug::forPage($page);
        $expectedTestSlug = $categoryPath.'/'.$slug;
        self::assertSame($expectedTestSlug, $testSlug, 'Use the actual main test slug resolver.');
        self::assertSame('theory', $page->category->type);
        $testRoute = app('router')->getRoutes()->match(Request::create('http://gramlyze.loc/test/'.$testSlug));
        self::assertSame('test.show', $testRoute->getName());
        self::assertSame($testSlug, $testRoute->parameter('slug'));
        $test = (new VirtualSavedTest($title.' (Mixed A1-C2)', 'theory-page-'.$page->id.'-mixed-a1-c2', [
            '__meta' => ['theory_page_static_slug' => true],
        ]))->setPublicSlug($testSlug);
        $view = fn ($model) => view('theory.show', [
            'page' => $model, 'categories' => collect(), 'categoryPages' => collect(),
            'selectedCategory' => $model->category, 'topicTests' => collect([$test]),
        ])->render();
        $linked = \Mockery::mock(TheoryPagePromptLinkedTestsService::class);
        $linked->shouldReceive('buildForPage')->andReturn(collect([$test]));
        app()->instance(TheoryPagePromptLinkedTestsService::class, $linked);
        $actualView = app(TheoryController::class)->showByCategoryPath($categoryPath, $slug);
        self::assertSame($h1, $actualView->getData()['page']->title);
        $html = $actualView->render();
        $course = view('courses.partials.theory-page-content', ['page' => $page])->render();
        $hero = json_decode($source['page']['blocks'][0]['body'], true, flags: JSON_THROW_ON_ERROR);
        foreach ([$html, $course] as $rendered) {
            $richBody = TheoryRichContent::render($source['page']['blocks'][1]['body']);
            self::assertNotNull($richBody);
            self::assertStringContainsString((string) $richBody, $rendered);
            self::assertStringContainsString($hero['intro'], $rendered);
            self::assertStringNotContainsString(htmlspecialchars($source['page']['blocks'][1]['body']), $rendered);
            self::assertSame(6, $this->dom($rendered)->query('//ol[@data-self-checks]/li')->length);
            self::assertSame(6, $this->dom($rendered)->query('//ol[@data-self-check-answers]/li')->length);
        }
        $dom = $this->dom($html);
        self::assertSame(1, $dom->query('//h1')->length);
        self::assertSame($h1, trim($dom->evaluate('string(//h1)')));
        self::assertSame(2, $dom->query('//a[@href="http://gramlyze.loc/test/'.$testSlug.'"]')->length);
        $metadata = PageMetadata::theory($title, '', $hero['intro'], $source['seeder']['class'], 'uk');
        self::assertSame(1, substr_count($metadata['description'], $title), 'PageMetadata adds the unchanged title once, never duplicates it.');
        self::assertSame($title.' — правила | Gramlyze', $metadata['title']);
        self::assertSame($metadata['title'], $dom->evaluate('string(//title)'));
        foreach (['//meta[@name="description"]', '//meta[@property="og:description"]', '//meta[@name="twitter:description"]'] as $selector) {
            self::assertSame($metadata['description'], $dom->evaluate('string('.$selector.'/@content)'));
            self::assertDoesNotMatchRegularExpression('/<[^>]+>|&(?:lt|gt|amp);/', $dom->evaluate('string('.$selector.'/@content)'));
        }
        $before = $this->before($source['seeder']['class']);
        $beforePage = clone $page;
        $beforePage->title = $h1;
        $beforePage->text = $before['page']['subtitle_text'];
        $beforePage->setRelation('textBlocks', $blocks->map(function (TextBlock $block) use ($before): TextBlock {
            $copy = clone $block;
            if ($copy->sort_order === 0) {
                $copy->body = $before['page']['subtitle_html'];
            } else {
                $old = $before['page']['blocks'][$copy->sort_order - 1];
                $copy->body = $old['body'];
                $copy->heading = $old['heading'] ?? null;
            }

            return $copy;
        }));
        $oldHtml = $view($beforePage);
        $oldDom = $this->dom($oldHtml);
        self::assertSame($oldDom->evaluate('string(//title)'), $dom->evaluate('string(//title)'));
        self::assertSame($oldDom->evaluate('string(//h1)'), $dom->evaluate('string(//h1)'));
        self::assertNotSame($oldDom->evaluate('string(//meta[@name="description"]/@content)'), $metadata['description']);
        $theoryPath = '/theory/'.$categoryPath.'/'.$slug;
        foreach (['http://gramlyze.loc', 'https://gramlyze.com'] as $origin) {
            $previous = $this->seoResponse($oldHtml, $theoryPath, $origin);
            $next = $this->seoResponse($html, $theoryPath, $origin);
            $oldSeo = $this->dom($previous->getContent());
            $newSeo = $this->dom($next->getContent());
            self::assertSame('https://gramlyze.com'.$theoryPath, $newSeo->evaluate('string(//link[@rel="canonical"]/@href)'));
            self::assertSame($oldSeo->evaluate('string(//link[@rel="canonical"]/@href)'), $newSeo->evaluate('string(//link[@rel="canonical"]/@href)'));
            self::assertSame($oldSeo->evaluate('string(//meta[@name="robots"]/@content)'), $newSeo->evaluate('string(//meta[@name="robots"]/@content)'));
            self::assertSame($previous->headers->get('X-Robots-Tag'), $next->headers->get('X-Robots-Tag'));
            self::assertSame($origin === 'http://gramlyze.loc' ? 'noindex, nofollow, noarchive' : null, $next->headers->get('X-Robots-Tag'));
        }
        $manifest = app(TheoryCourseManifestService::class);
        $courseUrl = $manifest->lessonUrl($categoryPath, $slug);
        self::assertSame('/courses/english-grammar-theory/lesson/'.$categoryPath.'/'.$slug, $courseUrl);
        self::assertSame('courses.theory.lesson', app('router')->getRoutes()->match(Request::create('http://gramlyze.loc'.$courseUrl))->getName());
        $resolved = $manifest->resolvePageForLesson(['page_id' => $page->id]);
        self::assertSame($page->id, $resolved->id);
        self::assertSame($blocks->pluck('uuid')->all(), $resolved->textBlocks->pluck('uuid')->all());
        self::assertSame($blocks->pluck('body')->all(), $resolved->textBlocks->pluck('body')->all());
    }

    public function test_editorial_boundaries_and_all_eighteen_prompts_are_distinct(): void
    {
        $prompts = [];
        $bodies = [];
        foreach (self::lessons() as [$name, $slug]) {
            $body = $this->definition($name)['page']['blocks'][1]['body'];
            $bodies[$slug] = PageMetadata::plain($body);
            foreach ($this->dom($body)->query('//ol[@data-self-checks]/li') as $item) {
                $prompts[] = preg_replace('/\s+/u', ' ', trim($item->textContent));
            }
        }
        self::assertCount(18, $prompts);
        self::assertCount(18, array_unique($prompts));
        $expected = [
            'passive-reporting-structures' => ['personal passive', 'backshift', 'to be painting', 'to have left', 'джерело', 'to may'],
            'complex-passive-and-causative' => ['Past Perfect', 'did not have', 'get-passive', 'had had', 'небажану подію', 'не приписує'],
            'complex-passive-impersonal-style' => ['to have been inspected', 'to be being', 'not known', 'known not', 'прикметник', 'незалежно не підтверджено', 'by noon tomorrow'],
        ];
        foreach ($expected as $slug => $terms) {
            foreach ($terms as $term) {
                self::assertStringContainsString(mb_strtolower($term), mb_strtolower($bodies[$slug]));
            }
        }
        // Distinct from all twenty-four accepted M11–M18 lessons.
        foreach (['AcademicEnglish/ArgumentationAndAcademicToneTheorySeeder', 'ClausesAndLinkingWords/DiscourseMarkersAndCohesionTheorySeeder', 'FormalEnglish/ParaphraseAndReformulationTheorySeeder', 'AcademicEnglish/HedgingAndCautiousLanguageBasicsTheorySeeder', 'AcademicEnglish/HedgingAndCautiousLanguageTheorySeeder', 'AcademicEnglish/StanceRegisterAndEvaluationTheorySeeder', 'FormalEnglish/FormalRegisterAndNominalisationBasicsTheorySeeder', 'FormalEnglish/NominalisationFormalRegisterTheorySeeder', 'FormalEnglish/RegisterToneAndParaphraseTheorySeeder',
            'Conditionals/ConditionalsWithUnlessProvidedAsLongAsTheorySeeder', 'Conditionals/AdvancedConditionalsTheorySeeder', 'Conditionals/ConditionalAlternativesAndNuanceTheorySeeder', 'ClausesAndLinkingWords/LinkingWordsReasonResultContrastTheorySeeder',
            'ClausesAndLinkingWords/AdvancedLinkingDevicesTheorySeeder', 'ClausesAndLinkingWords/ConcessiveAndContrastiveStructuresTheorySeeder',
            'SentenceStructure/CleftSentencesBasicsTheorySeeder', 'BasicGrammar/WordOrder/InversionBasicsTheorySeeder',
            'BasicGrammar/WordOrder/AdvancedFrontingAndEmphasisTheorySeeder', 'SentenceStructure/CleftSentencesEmphasisTheorySeeder',
            'SentenceStructure/ComplexNounPhrasesTheorySeeder', 'SentenceStructure/EllipsisSubstitutionAndReferenceTheorySeeder',
            'ClausesAndLinkingWords/ParticipleClausesBasicsTheorySeeder', 'ClausesAndLinkingWords/ParticipleClausesTheorySeeder',
            'ClausesAndLinkingWords/AdvancedParticipleAndAbsoluteClausesTheorySeeder'] as $name) {
            foreach ($this->dom($this->definition($name)['page']['blocks'][1]['body'])->query('//section[starts-with(@id,"self-check-")]/ol/li') as $item) {
                self::assertNotContains(preg_replace('/\\s+/u', ' ', trim($item->textContent)), $prompts);
            }
        }
    }

    public function test_category_type_is_preserved_for_all_three_sources(): void
    {
        foreach (self::lessons() as [$name]) {
            $source = $this->definition($name);
            self::assertSame('theory', $source['page']['category']['type']);
            self::assertSame('theory', $source['type']);
        }
    }
}
