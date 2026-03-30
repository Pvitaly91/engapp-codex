<?php

namespace App\Services\PageV3PromptGenerator;

use App\Models\PageCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TheoryCategorySearchService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForPicker(): array
    {
        return $this->baseQuery()
            ->get()
            ->map(fn (PageCategory $category) => $this->categorySummary($category))
            ->sortBy([
                ['path', 'asc'],
                ['title', 'asc'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function catalog(): array
    {
        return $this->allForPicker();
    }

    public function findSummaryById(int $categoryId): ?array
    {
        $category = $this->baseQuery()->find($categoryId);

        return $category ? $this->categorySummary($category) : null;
    }

    protected function baseQuery()
    {
        return PageCategory::query()
            ->where('type', 'theory')
            ->with(['parent.parent.parent.parent.parent'])
            ->withCount('pages')
            ->orderBy('title');
    }

    /**
     * @return array<string, mixed>
     */
    protected function categorySummary(PageCategory $category): array
    {
        $path = $this->categoryPath($category);
        $titles = $path->map(fn (PageCategory $item) => $item->title)->values()->all();
        $slugs = $path->map(fn (PageCategory $item) => $item->slug)->values()->all();
        $seederClass = $category->seeder ?: null;
        $inferredNamespace = $this->inferNamespace($seederClass, $titles);

        return [
            'id' => $category->getKey(),
            'title' => $category->title,
            'slug' => $category->slug,
            'language' => $category->language,
            'type' => $category->type,
            'path' => implode(' / ', $titles),
            'path_titles' => $titles,
            'slug_path' => implode('/', $slugs),
            'slug_path_segments' => $slugs,
            'parent_id' => $category->parent_id,
            'parent_title' => $category->parent?->title,
            'parent_slug' => $category->parent?->slug,
            'seeder_class' => $seederClass,
            'seeder_relative_path' => $this->classToRelativePath($seederClass),
            'namespace' => $inferredNamespace,
            'namespace_path' => $inferredNamespace ? str_replace('\\', '/', $inferredNamespace) : null,
            'page_count' => (int) ($category->pages_count ?? 0),
        ];
    }

    /**
     * @return Collection<int, PageCategory>
     */
    protected function categoryPath(PageCategory $category): Collection
    {
        $path = collect();
        $current = $category;
        $depth = 0;

        while ($current && $depth < 10) {
            $path->prepend($current);
            $current->loadMissing('parent');
            $current = $current->parent;
            $depth++;
        }

        return $path->values();
    }

    /**
     * @param  array<int, string>  $titles
     */
    protected function inferNamespace(?string $seederClass, array $titles): ?string
    {
        if (filled($seederClass) && Str::startsWith($seederClass, 'Database\\Seeders\\Page_V3\\')) {
            $withoutRoot = Str::after($seederClass, 'Database\\Seeders\\Page_V3\\');

            return Str::contains($withoutRoot, '\\')
                ? Str::beforeLast($withoutRoot, '\\')
                : null;
        }

        if ($titles === []) {
            return null;
        }

        return collect($titles)
            ->map(function (string $title) {
                $studly = Str::studly(Str::slug($title, ' '));

                return $studly !== '' ? $studly : null;
            })
            ->filter()
            ->implode('\\');
    }

    protected function classToRelativePath(?string $className): ?string
    {
        if (! filled($className) || ! Str::startsWith($className, 'Database\\Seeders\\Page_V3\\')) {
            return null;
        }

        return 'database/seeders/Page_V3/' . str_replace('\\', '/', Str::after($className, 'Database\\Seeders\\Page_V3\\')) . '.php';
    }
}
