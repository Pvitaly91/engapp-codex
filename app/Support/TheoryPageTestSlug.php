<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Str;

class TheoryPageTestSlug
{
    public static function forPage(Page $page): string
    {
        $topicSlug = Str::slug((string) ($page->category?->slug ?? 'theory'));
        $pageSlug = Str::slug((string) $page->slug);
        $topicPrefix = $topicSlug.'-';

        if ($topicSlug !== '' && Str::startsWith($pageSlug, $topicPrefix)) {
            $pageSlug = Str::after($pageSlug, $topicPrefix);
        }

        return implode('/', array_filter([
            $topicSlug !== '' ? $topicSlug : 'theory',
            $pageSlug !== '' ? $pageSlug : 'page-'.$page->getKey(),
        ]));
    }

    public static function forLevelPair(Page $page, string $levelFrom, string $levelTo): string
    {
        return self::forPage($page).'/'.Str::lower($levelFrom).'-'.Str::lower($levelTo);
    }
}
