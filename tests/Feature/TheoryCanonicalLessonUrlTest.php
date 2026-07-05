<?php

namespace Tests\Feature;

use Database\Seeders\Page_V3\FutureForms\FutureFormsCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleCategorySeeder;
use Database\Seeders\Page_V3\FutureForms\FutureSimple\FutureSimpleFormsTheorySeeder;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheoryCanonicalLessonUrlTest extends TestCase
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
        ]);
    }

    public function test_only_the_nested_canonical_lesson_url_exists(): void
    {
        $this->get('/theory/future-simple/future-simple-forms')
            ->assertNotFound();

        $this->get('/theory/maibutni-formy/future-simple/future-simple-forms')
            ->assertOk()
            ->assertSee(
                '<link rel="canonical" href="https://gramlyze.com/theory/maibutni-formy/future-simple/future-simple-forms">',
                false
            );
    }

    public function test_theory_navigation_only_links_to_the_nested_lesson_url(): void
    {
        $response = $this->get('/theory/future-simple')->assertOk();

        $response->assertSee('/theory/maibutni-formy/future-simple/future-simple-forms', false);
        $response->assertDontSee('/theory/future-simple/future-simple-forms', false);
    }
}
