<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TranslateTestV2PageTest extends TestCase
{
    /** @test */
    public function translate_test_v2_page_loads(): void
    {
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_29_093218_create_sentences_table.php']);
        $this->seed(\Database\Seeders\SentenceTranslationSeeder::class);

        $response = $this->get('/translate/test2');
        $response->assertStatus(200);
    }
}
