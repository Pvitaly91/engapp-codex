<?php

namespace Tests\Feature;

use Database\Seeders\V2\Polyglot\PolyglotToBeLessonSeeder;
use Illuminate\Testing\TestResponse;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class SentenceBuilderPublicBrandingTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coming-soon.enabled' => false,
            'tests.tech_info_enabled' => false,
        ]);

        $this->rebuildComposeTestSchema();
        $this->seed(PolyglotToBeLessonSeeder::class);
    }

    public function test_course_page_uses_sentence_builder_branding_on_canonical_and_legacy_urls(): void
    {
        $canonical = $this->get('/courses/sentence-builder-english-a1');
        $legacy = $this->get('/courses/polyglot-english-a1');

        $canonical->assertOk();
        $legacy->assertOk();

        $this->assertVisibleBrandingClean($canonical);
        $this->assertVisibleBrandingClean($legacy);
        $canonical->assertSee('English Sentence Builder A1');
        $canonical->assertSee('/test/sentence-builder-to-be-a1/step/compose', false);
    }

    public function test_compose_page_uses_sentence_builder_branding_on_canonical_and_legacy_urls(): void
    {
        $canonical = $this->get('/test/sentence-builder-to-be-a1/step/compose');
        $legacy = $this->get('/test/polyglot-to-be-a1/step/compose');

        $canonical->assertOk();
        $legacy->assertOk();

        $this->assertVisibleBrandingClean($canonical);
        $this->assertVisibleBrandingClean($legacy);
        $canonical->assertSee('Sentence Builder: To Be (A1)');
        $canonical->assertSee('English Sentence Builder A1');
    }

    public function test_course_catalog_and_test_catalog_do_not_show_legacy_branding(): void
    {
        $courses = $this->get('/courses');
        $tests = $this->get('/catalog/tests-cards');

        $courses->assertOk();
        $tests->assertOk();

        $this->assertVisibleBrandingClean($courses);
        $this->assertVisibleBrandingClean($tests);
        $courses->assertSee('English Sentence Builder');
        $tests->assertSee('Sentence Builder: To Be (A1)');
    }

    private function assertVisibleBrandingClean(TestResponse $response): void
    {
        $visible = $this->visibleText($response);

        foreach (['Polyglot', 'Poliglot', 'Поліглот', 'Полиглот', 'Polyglot 16', 'Поліглот 16'] as $banned) {
            $this->assertStringNotContainsString($banned, $visible);
        }
    }

    private function visibleText(TestResponse $response): string
    {
        $html = $response->getContent();
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;

        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
