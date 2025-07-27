<?php

namespace Tests\Feature;

use Tests\TestCase;

class PronounsTestTest extends TestCase
{
    /** @test */
    public function pronouns_test_page_loads(): void
    {
        $response = $this->get('/pronouns/test');
        $response->assertStatus(200);
    }
}
