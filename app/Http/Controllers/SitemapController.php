<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $origin = rtrim((string) config('site-mode.production_origin', 'https://gramlyze.com'), '/');
        $urls = collect([
            $this->entry($origin.'/', null, '1.0'),
            $this->entry($origin.'/theory', null, '0.9'),
        ]);

        $categories = PageCategory::query()
            ->where('type', 'theory')
            ->where('language', 'uk')
            ->with('parent.parent.parent.parent.parent')
            ->orderBy('id')
            ->get();

        $urls->push(...$categories->map(fn (PageCategory $category): array => $this->entry(
            $origin.'/theory/'.rawurlencode((string) $category->slug),
            $category->updated_at,
            '0.8'
        )));

        $categoryPaths = $categories->mapWithKeys(
            fn (PageCategory $category): array => [$category->getKey() => $this->categoryPath($category)]
        );

        Page::query()
            ->forType('theory')
            ->whereIn('page_category_id', $categoryPaths->keys())
            ->orderBy('id')
            ->each(function (Page $page) use ($origin, $categoryPaths, $urls): void {
                $categoryPath = $categoryPaths->get($page->page_category_id);

                if (! is_string($categoryPath) || $categoryPath === '') {
                    return;
                }

                $urls->push($this->entry(
                    $origin.'/theory/'.$this->encodePath($categoryPath).'/'.rawurlencode((string) $page->slug),
                    $page->updated_at,
                    '0.7'
                ));
            });

        return response()
            ->view('sitemap', ['urls' => $urls->unique('loc')->values()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function categoryPath(PageCategory $category): string
    {
        $segments = [];
        $current = $category;
        $depth = 0;

        while ($current instanceof PageCategory && $depth++ < 10) {
            if ($current->slug !== '') {
                array_unshift($segments, $current->slug);
            }

            $current = $current->parent;
        }

        return implode('/', $segments);
    }

    private function encodePath(string $path): string
    {
        return collect(explode('/', $path))->map('rawurlencode')->implode('/');
    }

    private function entry(string $location, mixed $updatedAt, string $priority): array
    {
        return [
            'loc' => $location,
            'lastmod' => $updatedAt?->toAtomString(),
            'priority' => $priority,
        ];
    }
}
