<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class PronounsTestTest extends TestCase
{
    /** @test */
    public function pronouns_test_page_loads(): void
    {
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182347_create_words_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182357_create_translates_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000001_create_tags_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000002_create_tag_word_table.php']);

        $this->seed(\Database\Seeders\PronounWordsSeeder::class);

        $response = $this->get('/pronouns/test');
        $response->assertStatus(200);
    }
}
