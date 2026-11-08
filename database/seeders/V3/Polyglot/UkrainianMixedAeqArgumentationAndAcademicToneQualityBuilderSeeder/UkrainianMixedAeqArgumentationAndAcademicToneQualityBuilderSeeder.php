<?php

namespace Database\Seeders\V3\Polyglot;

// Generated Academic English quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedAeqArgumentationAndAcademicToneQualityBuilderSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
