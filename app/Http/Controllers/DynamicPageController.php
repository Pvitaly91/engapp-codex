<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicPageController extends PageController
{
    /**
     * Configure controller based on the page type from URL.
     */
    protected function configureForType(string $pageType): void
    {
        // Cache the validation check for 5 minutes to avoid database queries on every request
        $cacheKey = "page_type_exists:{$pageType}";
        $exists = Cache::remember($cacheKey, 300, fn () => Page::where('type', $pageType)->exists());

        if (! $exists) {
            throw new NotFoundHttpException("Page type '{$pageType}' not found.");
        }

        $this->pageType = $pageType;
        $this->routePrefix = $pageType;
        $this->sectionTitle = Str::ucfirst($pageType);

        // Cache view existence checks for the entire request lifecycle
        // These are unlikely to change during runtime
        $viewCacheKey = "page_type_views:{$pageType}";
        $views = Cache::remember($viewCacheKey, 3600, function () use ($pageType) {
            return [
                'index' => view()->exists("engram.{$pageType}.index")
                    ? "engram.{$pageType}.index"
                    : 'engram.pages.index',
                'show' => view()->exists("engram.{$pageType}.show")
                    ? "engram.{$pageType}.show"
                    : 'engram.pages.show',
            ];
        });

        $this->indexView = $views['index'];
        $this->showView = $views['show'];
    }

    /**
     * Display the available categories with the first category's pages for a specific page type.
     */
    public function indexForType(string $pageType)
    {
        $this->configureForType($pageType);

        return parent::index();
    }

    /**
     * Display a specific category with its related pages for a specific page type.
     */
    public function categoryForType(string $pageType, string $category)
    {
        $this->configureForType($pageType);

        $categoryModel = PageCategory::where('slug', $category)->firstOrFail();

        return parent::category($categoryModel);
    }

    /**
     * Display the specified page using structured text blocks for a specific page type.
     */
    public function showForType(string $pageType, string $category, string $pageSlug)
    {
        $this->configureForType($pageType);

        $categoryModel = PageCategory::where('slug', $category)->firstOrFail();

        return parent::show($categoryModel, $pageSlug);
    }
}
