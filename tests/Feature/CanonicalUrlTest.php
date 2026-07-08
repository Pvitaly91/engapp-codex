<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CanonicalUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site-mode.production_origin' => 'https://gramlyze.com/']);

        Route::middleware('web')->get('/canonical-public', fn () => response(
            '<!doctype html><html><head><title>Public</title></head><body></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        ))->name('pages.canonical-test');

        Route::middleware('web')->get('/admin/canonical-private', fn () => response(
            '<!doctype html><html><head><title>Admin</title></head><body></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        ))->name('admin.canonical-test');

        Route::middleware('web')->get('/en/canonical-localized', fn () => response(
            '<!doctype html><html><head><title>Localized</title></head><body></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        ))->name('pages.canonical-localized');
    }

    public function test_public_html_page_gets_queryless_production_canonical(): void
    {
        $this->get('http://gramlyze.ub/canonical-public?utm_source=test&page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://gramlyze.com/canonical-public">', false);
    }

    public function test_non_public_route_does_not_get_canonical(): void
    {
        $this->get('http://gramlyze.ub/admin/canonical-private')
            ->assertOk()
            ->assertDontSee('rel="canonical"', false);
    }

    public function test_locale_prefix_is_removed_from_production_canonical(): void
    {
        $this->get('http://engapp-codex.loc/en/canonical-localized')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://gramlyze.com/canonical-localized">', false);
    }

    public function test_existing_canonical_is_replaced_instead_of_duplicated(): void
    {
        Route::middleware('web')->get('/canonical-existing', fn () => response(
            '<html><head><link rel="canonical" href="http://old.test/wrong"></head><body></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ))->name('theory.canonical-existing');

        $response = $this->get('http://gramlyze.ub/canonical-existing');

        $response->assertOk()->assertDontSee('old.test', false);
        $this->assertSame(1, substr_count($response->getContent(), 'rel="canonical"'));
    }

}
