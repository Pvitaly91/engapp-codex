<?php

namespace Database\Seeders\V2\Polyglot;

use App\Services\PolyglotLessonImportService;
use Database\Seeders\QuestionSeeder;

abstract class JsonPolyglotLessonSeeder extends QuestionSeeder
{
    final public function run(): void
    {
        app(PolyglotLessonImportService::class)->importFromFile(
            $this->lessonJsonPath(),
            true,
            static::class
        );
    }

    abstract protected function lessonJsonPath(): string;
}
