<?php

namespace Database\Seeders\Pages\Articles;

use Database\Seeders\Pages\Articles\Concerns\SomeAnyContent;
use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class SomeAnyCategorySeeder extends PageCategoryDescriptionSeeder
{
    use SomeAnyContent;

    protected function slug(): string
    {
        return 'some-any';
    }

    protected function description(): array
    {
        return $this->someAnyContent();
    }
}
