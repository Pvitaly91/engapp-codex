<?php

namespace Database\Seeders\Page_V3\PassiveVoice;

use App\Support\Database\JsonPageSeeder;

class PassiveVoiceCategorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}