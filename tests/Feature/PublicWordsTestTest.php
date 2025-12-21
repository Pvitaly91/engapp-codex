<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PublicWordsTestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for words and translates tables
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182347_create_words_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182357_create_translates_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_31_000001_update_translates_unique_index.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000001_create_tags_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000002_create_tag_word_table.php']);
    }

    /** @test */
    public function public_words_test_page_loads(): void
    {
        $response = $this->get('/words/test');

        $response->assertStatus(200);
        $response->assertSee('Тест слів');
    }

    /** @test */
    public function check_answer_validates_required_fields(): void
    {
        $response = $this->postJson('/words/test/check', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['word_id', 'answer', 'questionType']);
    }

    /** @test */
    public function fetch_words_endpoint_works(): void
    {
        $response = $this->getJson('/words/test/fetch');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'questions',
            'total',
        ]);
    }
}
