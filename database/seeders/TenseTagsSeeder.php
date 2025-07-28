<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\TagCategory;

class TenseTagsSeeder extends Seeder
{
    public function run()
    {
        $tenses = [
            'Present Simple',
            'Present Continuous',
            'Present Perfect',
            'Present Perfect Continuous',
            'Past Simple',
            'Past Continuous',
            'Past Perfect',
            'Past Perfect Continuous',
            'Future Simple',
            'Future Continuous',
            'Future Perfect',
            'Future Perfect Continuous',
        ];

        $category = TagCategory::firstOrCreate(['name' => 'Tenses']);

        foreach ($tenses as $name) {
            Tag::firstOrCreate([
                'name' => $name
            ], [
                'tag_category_id' => $category->id
            ]);
        }
    }
}
