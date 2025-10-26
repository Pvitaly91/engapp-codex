<?php
namespace Database\Seeders\V1\Metadata;

use App\Support\Database\Seeder;
use App\Models\Tag;

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

        foreach ($tenses as $name) {
            Tag::firstOrCreate(
                ['name' => $name],
                ['category' => 'Tenses']
            );
        }
    }
}
