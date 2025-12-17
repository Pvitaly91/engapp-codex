<?php

namespace Tests\Feature;

use Tests\TestCase;

class TheoryPageGuestAccessTest extends TestCase
{
    /**
     * Test that guests can access the theory index page without being redirected to login.
     * The page may return 500 if there's no database, but it should not redirect to login (302).
     */
    public function test_guest_can_access_theory_index_without_login_redirect(): void
    {
        $response = $this->get('/theory');

        // Should not redirect to login - theory pages are public
        $this->assertNotEquals(302, $response->status());
        $this->assertStringNotContainsString('/login', $response->headers->get('Location', ''));
    }
}
