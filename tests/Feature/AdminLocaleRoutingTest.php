<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLocaleRoutingTest extends TestCase
{
    /** @test */
    public function admin_routes_are_available_without_locale_prefix(): void
    {
        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/set-locale?lang=en', ['HTTP_REFERER' => '/admin']);

        $response->assertStatus(302);
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function localized_admin_urls_redirect_to_non_localized_paths(): void
    {
        $response = $this->get('/en/admin/words?filter=1');

        $response->assertStatus(301);
        $response->assertRedirect('/admin/words?filter=1');
    }
}
