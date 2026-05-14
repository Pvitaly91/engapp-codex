<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminDomainSwitcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_admin-domain-switcher-test', function () {
            return response()->view('components.admin-domain-switcher');
        });
    }

    public function test_guest_does_not_see_admin_domain_switcher(): void
    {
        $this->get('/_admin-domain-switcher-test')
            ->assertOk()
            ->assertDontSee('admin-domain-switcher');
    }

    public function test_admin_on_production_gets_test_domain_link_for_same_page(): void
    {
        $this->withSession(['admin_authenticated' => true])
            ->get('https://gramlyze.com/_admin-domain-switcher-test?foo=bar')
            ->assertOk()
            ->assertSee('data-admin-domain-current', false)
            ->assertSee('PROD')
            ->assertSee('href="http://engapp-codex.loc/_admin-domain-switcher-test?foo=bar"', false)
            ->assertSee('Відкрити на тесті');
    }

    public function test_admin_on_test_domain_gets_production_link_for_same_page(): void
    {
        $this->withSession(['admin_authenticated' => true])
            ->get('http://engapp-codex.loc/_admin-domain-switcher-test?foo=bar')
            ->assertOk()
            ->assertSee('data-admin-domain-current', false)
            ->assertSee('TEST')
            ->assertSee('href="https://gramlyze.com/_admin-domain-switcher-test?foo=bar"', false)
            ->assertSee('Відкрити на проді');
    }
}
