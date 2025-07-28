<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PronounsTestTest extends TestCase
{
    /** @test */
    public function pronouns_test_page_loads(): void
    {
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182347_create_words_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_18_182357_create_translates_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_31_000001_update_translates_unique_index.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000001_create_tags_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_07_30_000002_create_tag_word_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_08_01_000001_create_tag_categories_table.php']);
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_08_01_000002_add_tag_category_id_to_tags_table.php']);

        $this->seed(\Database\Seeders\PronounWordsSeeder::class);

        $response = $this->get('/words/test?tags[]=pronouns');
        $response->assertStatus(200);
    }
}
