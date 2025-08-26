<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AiTestRemoveTagTest extends TestCase
{
    /** @test */
    public function remove_tag_endpoint_updates_session_tags(): void
    {
        $migrations = [
            '2025_07_30_000001_create_tags_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/'.$file]);
        }

        $tag = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $response = $this->withSession(['ai_step.tags' => [$tag->id]])
            ->deleteJson('/ai-test/step/remove-tag', ['tag' => $tag->name]);

        $response->assertStatus(200);
        $response->assertJson(['tags' => []]);
        $this->assertEquals([], session('ai_step.tags'));
    }
}
