<?php

namespace Database\Seeders\V3\TestEnglish;

use Database\Seeders\V3\TestEnglish\A1\AAnPluralsSingularAndPluralFormsSeeder;
use Illuminate\Database\Seeder;

class TestEnglishV3Seeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AAnPluralsSingularAndPluralFormsSeeder::class,
        ]);
    }
}
