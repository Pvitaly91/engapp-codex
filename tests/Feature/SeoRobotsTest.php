<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SeoRobotsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/seo/duplicate', fn () => $this->html())
            ->name('site.search');
        Route::middleware('web')->get('/seo/technical', fn () => $this->html())
            ->name('dev.seo-technical');
        Route::middleware('web')->get('/seo/public', fn () => $this->html())
            ->name('theory.seo-public');
    }

    public function test_duplicate_page_gets_noindex_follow_header_and_meta(): void
    {
        $this->get('http://gramlyze.ub/seo/duplicate')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_technical_page_gets_strict_noindex_header_and_meta(): void
    {
        $this->get('http://engapp-codex.loc/seo/technical')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
    }

    public function test_indexable_public_page_does_not_get_noindex(): void
    {
        $this->get('http://gramlyze.ub/seo/public')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag')
            ->assertDontSee('name="robots"', false);
    }

    private function html()
    {
        return response(
            '<!doctype html><html><head><title>SEO</title></head><body></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}
