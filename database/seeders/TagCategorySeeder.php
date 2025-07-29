<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TagCategory;

class TagCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tenses', 'Words'] as $name) {
            TagCategory::firstOrCreate(['name' => $name]);
        }
    }
}
