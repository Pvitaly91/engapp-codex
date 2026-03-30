<?php

namespace App\Services\V3PromptGenerator;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TheoryPageSearchService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query = '', int $limit = 12, string $siteDomain = 'gramlyze.com'): array
    {
        $normalizedQuery = trim($query);

        $pages = Page::query()
            ->with(['category.parent.parent.parent'])
            ->forType('theory')
            ->whereNotNull('page_category_id')
            ->when($normalizedQuery !== '', function (Builder $builder) use ($normalizedQuery) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $normalizedQuery) . '%';

                $builder->where(function (Builder $query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                });
            })
            ->orderBy('title')
            ->limit($limit)
            ->get();

        return $pages
            ->map(fn (Page $page) => $this->pageSummary($page, $siteDomain))
            ->values()
            ->all();
    }

    public function findSummaryById(int $pageId, string $siteDomain = 'gramlyze.com'): ?array
    {
        $page = Page::query()
            ->with(['category.parent.parent.parent'])
            ->forType('theory')
            ->whereNotNull('page_category_id')
            ->find($pageId);

        return $page ? $this->pageSummary($page, $siteDomain) : null;
    }

    public function promptContext(int $pageId, string $siteDomain = 'gramlyze.com'): ?array
    {
        $page = Page::query()
            ->with([
                'category.parent.parent.parent',
                'textBlocks' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->forType('theory')
            ->whereNotNull('page_category_id')
            ->find($pageId);

        if (! $page || ! $page->category) {
            return null;
        }

        $summary = $this->pageSummary($page, $siteDomain);

        return array_merge($summary, [
            'topic' => $page->title,
            'source_type' => 'theory_page',
            'source_label' => 'Existing theory page from current site',
            'context_excerpt' => $this->extractContextSnippet($page),
            'page_seeder_class' => $page->seeder ?: null,
            'category_seeder_class' => $page->category->seeder ?: null,
            'resolved_seeder_class' => $page->seeder ?: ($page->category->seeder ?: null),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageSummary(Page $page, string $siteDomain): array
    {
        $category = $page->category;
        $categoryTitles = $category ? $this->categoryTitlePath($category) : [];
        $categorySlugs = $category ? $this->categorySlugPath($category) : [];
        $resolvedSeederClass = $page->seeder ?: ($category?->seeder ?: null);

        return [
            'id' => $page->getKey(),
            'title' => $page->title,
            'slug' => $page->slug,
            'category_path' => $categoryTitles !== [] ? implode(' / ', $categoryTitles) : null,
            'category_slug_path' => $categorySlugs !== [] ? implode('/', $categorySlugs) : null,
            'category_title' => $category?->title,
            'url' => $category
                ? $this->buildPublicTheoryUrl($category->slug, $page->slug, $siteDomain)
                : null,
            'page_seeder_class' => $page->seeder ?: null,
            'category_seeder_class' => $category?->seeder ?: null,
            'resolved_seeder_class' => $resolvedSeederClass,
            'excerpt' => $this->extractContextSnippet($page),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function categoryTitlePath(PageCategory $category): array
    {
        return $this->categoryPath($category)->map(fn (PageCategory $item) => $item->title)->all();
    }

    /**
     * @return array<int, string>
     */
    protected function categorySlugPath(PageCategory $category): array
    {
        return $this->categoryPath($category)->map(fn (PageCategory $item) => $item->slug)->all();
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

    protected function extractContextSnippet(Page $page): ?string
    {
        $segments = [];

        if ($page->relationLoaded('textBlocks')) {
            foreach ($page->textBlocks as $block) {
                if (filled($block->heading)) {
                    $segments[] = $block->heading;
                }

                if (filled($block->body)) {
                    $segments[] = $block->body;
                }
            }
        }

        if ($segments === [] && filled($page->text)) {
            $segments[] = $page->text;
        }

        $plain = trim((string) preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode(strip_tags(implode("\n", $segments)))
        ));

        if ($plain === '') {
            return null;
        }

        return mb_strimwidth($plain, 0, 700, '...');
    }

    protected function buildPublicTheoryUrl(string $categorySlug, string $pageSlug, string $siteDomain): string
    {
        $path = localized_route('theory.show', ['category' => $categorySlug, 'pageSlug' => $pageSlug], false);
        $domain = trim(strtolower($siteDomain));

        if (str_contains($domain, '://')) {
            $domain = (string) parse_url($domain, PHP_URL_HOST);
        }

        $domain = preg_replace('/:\d+$/', '', $domain) ?? $domain;
        $domain = trim($domain, "/ \t\n\r\0\x0B");
        $domain = $domain !== '' ? $domain : 'gramlyze.com';

        return 'https://' . $domain . '/' . ltrim($path, '/');
    }
}
