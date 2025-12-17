<?php

namespace Tests\Feature;

use Tests\TestCase;

class TheoryPageGuestAccessTest extends TestCase
{
    /**
     * Test that guests can access the theory index page.
     */
    public function test_guest_can_access_theory_index(): void
    {
        $response = $this->get('/theory');

        $response->assertStatus(200);
    }
}
