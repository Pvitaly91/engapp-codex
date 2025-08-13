<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\Tag;

class AiTestAddTagTest extends TestCase
{
    /** @test */
    public function add_tag_endpoint_updates_session_tags(): void
    {
        $migrations = [
            '2025_07_30_000001_create_tags_table.php',
            '2025_08_01_000001_add_category_to_tags_table.php',
        ];
        foreach ($migrations as $file) {
            Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
        }

        $tag = Tag::create(['name' => 'Past Simple', 'category' => 'Tenses']);

        $response = $this->withSession(['ai_step.tags' => []])
            ->postJson('/ai-test/step/add-tag', ['tag' => $tag->name]);

        $response->assertStatus(200);
        $response->assertJson(['tags' => [$tag->name]]);
        $this->assertEquals([$tag->id], session('ai_step.tags'));
    }
}

