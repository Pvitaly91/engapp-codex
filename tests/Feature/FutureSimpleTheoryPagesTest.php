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

    public function test_old_will_vs_be_going_to_url_redirects_to_the_new_canonical_path(): void
    {
        $this->get('/theory/maibutni-formy/will-vs-be-going-to')
            ->assertRedirect('/theory/maibutni-formy/future-simple/will-vs-be-going-to');
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
