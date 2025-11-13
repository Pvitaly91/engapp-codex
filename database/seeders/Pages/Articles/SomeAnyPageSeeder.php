<?php

namespace Database\Seeders\Pages\Articles;

use Database\Seeders\Pages\Articles\Concerns\SomeAnyContent;

class SomeAnyPageSeeder extends ArticlePageSeeder
{
    use SomeAnyContent;

    protected function slug(): string
    {
        return 'some-any';
    }

    protected function page(): array
    {
        return $this->someAnyContent();
    }

    protected function category(): array
    {
        return [
            'slug' => 'some-any',
            'title' => 'Some / Any — Кількість у ствердженні та запереченні',
            'language' => 'uk',
        ];
    }
}
