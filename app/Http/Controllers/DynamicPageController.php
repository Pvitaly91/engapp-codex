<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicPageController extends PageController
{
    /**
     * Configure controller based on the page type from URL.
     */
    protected function configureForType(string $pageType): void
    {
        // Validate that pages with this type exist
        $exists = Page::where('type', $pageType)->exists();
        if (! $exists) {
            throw new NotFoundHttpException("Page type '{$pageType}' not found.");
        }

        $this->pageType = $pageType;
        $this->routePrefix = $pageType;
        $this->sectionTitle = Str::ucfirst($pageType);

        // Use type-specific views if they exist, otherwise fallback to pages views
        $this->indexView = view()->exists("engram.{$pageType}.index")
            ? "engram.{$pageType}.index"
            : 'engram.pages.index';

        $this->showView = view()->exists("engram.{$pageType}.show")
            ? "engram.{$pageType}.show"
            : 'engram.pages.show';
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
    public function categoryForType(string $pageType, PageCategory $category)
    {
        $this->configureForType($pageType);

        return parent::category($category);
    }

    /**
     * Display the specified page using structured text blocks for a specific page type.
     */
    public function showForType(string $pageType, PageCategory $category, string $pageSlug)
    {
        $this->configureForType($pageType);

        return parent::show($category, $pageSlug);
    }
}
