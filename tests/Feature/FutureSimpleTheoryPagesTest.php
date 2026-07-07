<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureFormsWillVsBeGoingToTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleFormsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleNegativesTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleQuestionsTheorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleTimeExpressionsTheorySeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class FutureSimpleTheoryPagesTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coming-soon.enabled' => false]);
        $this->rebuildComposeTestSchema();

        $this->seed([
            FutureFormsCategorySeeder::class,
            FutureSimpleCategorySeeder::class,
            FutureSimpleFormsTheorySeeder::class,
            FutureSimpleNegativesTheorySeeder::class,
            FutureSimpleQuestionsTheorySeeder::class,
            FutureSimpleTimeExpressionsTheorySeeder::class,
            FutureFormsWillVsBeGoingToTheorySeeder::class,
        ]);
    }

    public function test_future_simple_is_a_child_topic_with_five_relevant_pages(): void
    {
        $category = PageCategory::query()
            ->with(['parent', 'pages'])
            ->where('slug', 'future-simple')
            ->firstOrFail();

        $this->assertSame('maibutni-formy', $category->parent?->slug);
        $this->assertSame('theory', $category->type);
        $this->assertEqualsCanonicalizing([
            'future-simple-forms',
            'future-simple-negatives',
            'future-simple-questions',
            'future-simple-time-expressions',
            'will-vs-be-going-to',
        ], $category->pages->pluck('slug')->all());

        $this->assertSame(
            $category->id,
            Page::query()->where('slug', 'will-vs-be-going-to')->value('page_category_id')
        );
    }

    public function test_future_simple_category_and_pages_are_publicly_available(): void
    {
        $this->get('/theory/future-simple')
            ->assertOk()
            ->assertSee('Future Simple: Forms and Use')
            ->assertSee('Will vs Be Going To');

        foreach ([
            'future-simple-forms',
            'future-simple-negatives',
            'future-simple-questions',
            'future-simple-time-expressions',
            'will-vs-be-going-to',
        ] as $slug) {
            $this->get('/theory/maibutni-formy/future-simple/'.$slug)->assertOk();
        }
    }

    public function test_future_simple_page_has_website_breadcrumb_and_learning_resource_json_ld(): void
    {
        $response = $this->get('/theory/maibutni-formy/future-simple/future-simple-forms');

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<script type="application\/ld\+json">.*<\/script>/s',
            $response->getContent()
        );

        preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $response->getContent(),
            $matches
        );
        $payload = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $graph = collect($payload['@graph'])->keyBy('@type');

        $this->assertSame('https://schema.org', $payload['@context']);
        $this->assertTrue($graph->has('WebSite'));
        $this->assertTrue($graph->has('BreadcrumbList'));
        $this->assertTrue($graph->has('LearningResource'));
        $this->assertSame(
            'https://gramlyze.com/theory/maibutni-formy/future-simple/future-simple-forms',
            $graph->get('LearningResource')['url']
        );
        $this->assertSame('Future Simple: Forms and Use', $graph->get('LearningResource')['name']);
        $this->assertNotSame('', $graph->get('LearningResource')['description']);
        $this->assertSame(
            [1, 2, 3, 4, 5],
            collect($graph->get('BreadcrumbList')['itemListElement'])->pluck('position')->all()
        );
    }

    public function test_future_simple_category_and_pages_have_unique_seo_metadata(): void
    {
        $paths = [
            '/theory/future-simple',
            '/theory/maibutni-formy/future-simple/future-simple-forms',
            '/theory/maibutni-formy/future-simple/future-simple-negatives',
            '/theory/maibutni-formy/future-simple/future-simple-questions',
            '/theory/maibutni-formy/future-simple/future-simple-time-expressions',
            '/theory/maibutni-formy/future-simple/will-vs-be-going-to',
        ];
        $titles = [];
        $descriptions = [];

        foreach ($paths as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match('/<title>(.*?)<\/title>/s', $html, $titleMatch);
            preg_match('/<meta name="description" content="([^"]*)"/s', $html, $descriptionMatch);
            preg_match('/<meta property="og:title" content="([^"]*)"/s', $html, $openGraphTitleMatch);
            preg_match('/<meta property="og:description" content="([^"]*)"/s', $html, $openGraphDescriptionMatch);

            $title = html_entity_decode($titleMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);
            $description = html_entity_decode($descriptionMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

            $this->assertNotSame('', $title);
            $this->assertNotSame('', $description);
            $this->assertLessThanOrEqual(60, mb_strlen($title));
            $this->assertLessThanOrEqual(160, mb_strlen($description));
            $this->assertSame($title, html_entity_decode($openGraphTitleMatch[1] ?? '', ENT_QUOTES | ENT_HTML5));
            $this->assertSame($description, html_entity_decode($openGraphDescriptionMatch[1] ?? '', ENT_QUOTES | ENT_HTML5));
            $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image"', $html);
            $titles[] = $title;
            $descriptions[] = $description;
        }

        $this->assertCount(count($paths), array_unique($titles));
        $this->assertCount(count($paths), array_unique($descriptions));
    }

    public function test_old_will_vs_be_going_to_url_does_not_exist(): void
    {
        $this->get('/theory/maibutni-formy/will-vs-be-going-to')
            ->assertNotFound();
    }

    public function test_new_pages_have_english_and_polish_content(): void
    {
        $this->get('/en/theory/maibutni-formy/future-simple/future-simple-forms')
            ->assertOk()
            ->assertSee('Basic formula');

        $this->get('/pl/theory/maibutni-formy/future-simple/future-simple-forms')
            ->assertOk()
            ->assertSee('Podstawowy wzór');
    }
}
