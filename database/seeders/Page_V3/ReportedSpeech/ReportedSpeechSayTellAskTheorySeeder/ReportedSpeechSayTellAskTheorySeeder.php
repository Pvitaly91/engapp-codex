<?php

namespace Database\Seeders\Page_V3\ReportedSpeech;

use App\Support\Database\JsonPageSeeder;

class ReportedSpeechSayTellAskTheorySeeder extends JsonPageSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
