<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use Illuminate\Support\Carbon;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RebuildsComposeTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
        config([
            'app.debug' => false,
            'site-mode.production_domains' => ['seo.production.test'],
            'site-mode.production_origin' => 'https://gramlyze.com',
            'site-mode.production_locales' => ['uk'],
        ]);
    }

    public function test_sitemap_contains_canonical_theory_urls_from_production_origin(): void
    {
        config(['site-mode.production_origin' => 'https://gramlyze.com/']);

        $root = PageCategory::create([
            'title' => 'Майбутні форми',
            'slug' => 'maibutni-formy',
            'language' => 'uk',
            'type' => 'theory',
        ]);
        $child = PageCategory::create([
            'title' => 'Future Simple',
            'slug' => 'future-simple',
            'language' => 'uk',
            'type' => 'theory',
            'parent_id' => $root->id,
        ]);
        Page::create([
            'title' => 'Forms and Use',
            'slug' => 'future-simple-forms',
            'type' => 'theory',
            'page_category_id' => $child->id,
        ]);

        $response = $this->get('https://seo.production.test/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('https://gramlyze.com/theory', false);
        $response->assertSee('https://gramlyze.com/theory/maibutni-formy', false);
        $response->assertSee(
            'https://gramlyze.com/theory/maibutni-formy/future-simple/future-simple-forms',
            false
        );
        $response->assertDontSee('gramlyze.ub/theory', false);
        $locations = $this->locations($response->getContent());
        $this->assertContains('https://gramlyze.com/theory/future-simple', $locations);
        $this->assertNotContains('https://gramlyze.com/theory/maibutni-formy/future-simple', $locations);
    }

    public function test_sitemap_excludes_non_theory_and_non_ukrainian_content(): void
    {
        PageCategory::create([
            'title' => 'English theory',
            'slug' => 'english-theory',
            'language' => 'en',
            'type' => 'theory',
        ]);
        PageCategory::create([
            'title' => 'Other content',
            'slug' => 'other-content',
            'language' => 'uk',
            'type' => 'pages',
        ]);

        $this->get('https://seo.production.test/sitemap.xml')
            ->assertOk()
            ->assertDontSee('english-theory', false)
            ->assertDontSee('other-content', false);
    }

    public function test_xml_is_utf8_unique_stable_and_uses_configured_origin_not_guest_input(): void
    {
        $category = PageCategory::create([
            'title' => 'Речення & частини', 'slug' => 'речення & частини',
            'language' => 'uk', 'type' => 'theory',
        ]);
        foreach (['форми + питання', 'форми + питання'] as $slug) {
            Page::create([
                'title' => 'Повторний slug', 'slug' => $slug,
                'type' => 'theory', 'page_category_id' => $category->id,
            ]);
        }

        $first = $this->get('https://seo.production.test/sitemap.xml', ['Accept' => 'application/xml']);
        $first->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeaderMissing('X-Robots-Tag');
        $locations = $this->locations($first->getContent());
        $this->assertSame(count($locations), count(array_unique($locations)));
        $this->assertContains('https://gramlyze.com/theory/'.rawurlencode($category->slug).'/'.rawurlencode('форми + питання'), $locations);

        $this->withSession(['locale' => 'en', 'coming_soon.allowed_theory_test_slugs' => ['fake' => true]]);
        $other = $this->get('http://gramlyze.loc/sitemap.xml?source=theory&canonical=https://invalid.example/', [
            'Accept' => 'text/html', 'Referer' => 'http://gramlyze.loc/theory/example',
        ]);
        $other->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertSame($first->getContent(), $other->getContent());
        $this->assertSame($locations, $this->locations($other->getContent()));
    }

    public function test_lastmod_is_omitted_when_parent_dates_do_not_cover_content_edits_and_deletions(): void
    {
        Carbon::setTestNow('2025-01-01 10:00:00');
        try {
            $category = PageCategory::create([
                'title' => 'Test theory', 'slug' => 'lastmod-topic', 'language' => 'uk', 'type' => 'theory',
            ]);
            $page = Page::create([
                'title' => 'Forms', 'slug' => 'lastmod-topic-forms', 'type' => 'theory',
                'page_category_id' => $category->id, 'text' => 'Stable legacy educational content.',
            ]);
            TextBlock::create([
                'page_id' => $page->id, 'locale' => 'uk', 'type' => 'box',
                'body' => '<p>Stable Ukrainian teaching content for the course entry.</p>',
            ]);
            $initial = $this->get('https://seo.production.test/sitemap.xml')->assertOk()->getContent();
            $this->assertStringNotContainsString('<lastmod>', $initial);

            Carbon::setTestNow('2025-02-02 12:00:00');
            $block = TextBlock::create([
                'page_id' => $page->id, 'locale' => 'uk', 'type' => 'box', 'body' => 'New teaching content.',
            ]);
            $block->update(['body' => 'Updated teaching content.']);
            $this->assertSame($initial, $this->get('https://seo.production.test/sitemap.xml')->assertOk()->getContent());

            TextBlock::create(['page_id' => $page->id, 'locale' => 'en', 'type' => 'box', 'body' => 'Unrelated language.']);
            TextBlock::create(['page_category_id' => $category->id, 'locale' => 'uk', 'type' => 'box', 'body' => 'Category content.']);
            $block->delete();
            $this->assertSame($initial, $this->get('https://seo.production.test/sitemap.xml')->assertOk()->getContent());
            $this->assertSame('2025-01-01', $page->fresh()->updated_at->format('Y-m-d'));
        } finally {
            Carbon::setTestNow();
        }
    }

    /** @return array<int, string> */
    private function locations(string $body): array
    {
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $body);
        $this->assertSame(1, preg_match('//u', $body));
        $this->assertStringNotContainsString('<html', $body);
        $this->assertStringNotContainsString('<script', $body);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($body, LIBXML_NONET));
        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $document->documentElement->namespaceURI);
        $this->assertSame('urlset', $document->documentElement->localName);
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $locations = [];
        foreach ($xpath->query('/s:urlset/s:url/s:loc') as $node) {
            $location = $node->textContent;
            $this->assertStringStartsWith('https://gramlyze.com/', $location);
            $this->assertNull(parse_url($location, PHP_URL_QUERY));
            $this->assertNull(parse_url($location, PHP_URL_FRAGMENT));
            $locations[] = $location;
        }

        return $locations;
    }
}
