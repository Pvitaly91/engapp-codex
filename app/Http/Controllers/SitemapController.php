<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Services\CourseSitemapMetadataService;
use App\Services\TheoryPagePromptLinkedTestsService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(
        TheoryPagePromptLinkedTestsService $theoryTests,
        CourseSitemapMetadataService $courses,
    ): Response
    {
        $origin = rtrim((string) config('site-mode.production_origin', 'https://gramlyze.com'), '/');
        $urls = collect([
            $this->entry($origin.'/', '1.0'),
            $this->entry($origin.'/theory', '0.9'),
        ]);

        $categories = PageCategory::query()
            ->where('type', 'theory')
            ->where('language', 'uk')
            ->with('parent.parent.parent.parent.parent')
            ->orderBy('id')
            ->get();

        $urls->push(...$categories->map(fn (PageCategory $category): array => $this->entry(
            $origin.'/theory/'.rawurlencode((string) $category->slug),
            '0.8'
        )));

        $categoryPaths = $categories->mapWithKeys(
            fn (PageCategory $category): array => [$category->getKey() => $this->categoryPath($category)]
        );

        $pages = Page::query()
            ->forType('theory')
            ->whereIn('page_category_id', $categoryPaths->keys())
            ->orderBy('id')
            ->get();
        $categoriesById = $categories->keyBy('id');

        $pages->each(function (Page $page) use ($origin, $categoryPaths, $categoriesById, $urls): void {
            $page->setRelation('category', $categoriesById->get($page->page_category_id));
            $categoryPath = $categoryPaths->get($page->page_category_id);

            if (! is_string($categoryPath) || $categoryPath === '') {
                return;
            }

            $urls->push($this->entry(
                $origin.'/theory/'.$this->encodePath($categoryPath).'/'.rawurlencode((string) $page->slug),
                '0.7'
            ));
        });

        // Only stable main tests reconstructed from persistent theory metadata.
        // This path does not render cards or populate VirtualTestRegistry.
        foreach ($theoryTests->mainSitemapTests($pages, 'uk') as $test) {
            $urls->push($this->entry(
                $origin.'/test/'.$this->encodePath((string) $test->getAttribute('public_slug')),
                '0.6'
            ));
        }

        $coursePaths = $courses->eligiblePaths();
        if ($coursePaths !== []) {
            if ($courses->catalogueEligible()) {
                $urls->push($this->entry($origin.'/courses', '0.8'));
            }
            foreach ($coursePaths as $path) {
                $urls->push($this->entry($origin.$path, '0.7'));
            }
        }

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

    private function entry(string $location, string $priority): array
    {
        return [
            'loc' => $location,
            // Parent timestamps do not track block/question/pivot edits or deletions.
            // Omit optional lastmod until a reliable content revision is available.
            'lastmod' => null,
            'priority' => $priority,
        ];
    }
}
