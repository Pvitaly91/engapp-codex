<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** Render-only shared instruction/feedback presentation, never answer authority. */
final class TheoryPracticePresentation
{
    public static function prompt(string $html, int $number): string
    {
        return preg_replace_callback('/<h4>(.*?)<\/h4>/s', static fn ($match) => view('components.theory-practice-heading', [
            'title' => new HtmlString($match[1]), 'number' => $number, 'level' => 'h4', 'technical' => true,
        ])->render(), $html, 1) ?? $html;
    }

    public static function author(array $author): array
    {
        $author['prompts'] = array_map(static fn ($html, $index) => self::prompt($html, $index + 1),
            $author['prompts'], array_keys($author['prompts']));
        $author['answers'] = array_map(static fn ($html) => TheoryHtmlAdapter::fragment($html)->toHtml(), $author['answers']);
        return $author;
    }
}
