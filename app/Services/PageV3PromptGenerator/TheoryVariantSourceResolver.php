<?php

namespace App\Services\PageV3PromptGenerator;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Modules\LanguageManager\Services\LocaleService;
use App\Services\PageV3PromptGenerator\Data\TheoryVariantPromptGenerationInput;
use App\Support\TheoryVariantPayloadSanitizer;
use Illuminate\Support\Collection;
use RuntimeException;

class TheoryVariantSourceResolver
{
    /**
     * @return array<string, string>
     */
    public function localeOptions(): array
    {
        return collect(LocaleService::getSupportedLocaleCodes())
            ->mapWithKeys(fn (string $locale) => [$locale => strtoupper($locale)])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pageOptions(): array
    {
        return Page::query()
            ->with(['category.parent.parent.parent.parent'])
            ->forType('theory')
            ->whereNotNull('page_category_id')
            ->orderBy('title')
            ->get()
            ->filter(fn (Page $page) => $page->category instanceof PageCategory)
            ->map(fn (Page $page) => $this->pageOptionSummary($page))
            ->sortBy([
                ['category_path', 'asc'],
                ['title', 'asc'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(TheoryVariantPromptGenerationInput $input): array
    {
        $preferredLocale = TheoryVariantPayloadSanitizer::sanitizeLocale($input->locale);
        $fallbackLocale = LocaleService::getDefaultLanguage()?->code
            ?? config('language-manager.fallback_locale', 'uk');

        $category = PageCategory::query()
            ->with(['parent.parent.parent.parent'])
            ->where('slug', $input->targetCategorySlug)
            ->where('type', 'theory')
            ->first();

        if (! $category instanceof PageCategory) {
            throw new RuntimeException(__('Не знайдено категорію теорії зі slug :slug.', [
                'slug' => $input->targetCategorySlug,
            ]));
        }

        if ($input->targetType === 'category') {
            return $this->resolveCategorySource($category, $preferredLocale, $fallbackLocale);
        }

        if ($input->targetType !== 'page') {
            throw new RuntimeException(__('Непідтримуваний тип джерела: :type.', ['type' => $input->targetType]));
        }

        $page = Page::query()
            ->with(['category.parent.parent.parent.parent', 'textBlocks' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->forType('theory')
            ->where('slug', (string) $input->targetPageSlug)
            ->where('page_category_id', $category->getKey())
            ->first();

        if (! $page instanceof Page) {
            throw new RuntimeException(__('Не знайдено сторінку теорії зі slug :slug.', [
                'slug' => $input->targetPageSlug,
            ]));
        }

        return $this->resolvePageSource($page, $category, $preferredLocale, $fallbackLocale);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvePageSource(Page $page, PageCategory $category, string $preferredLocale, string $fallbackLocale): array
    {
        $category->loadMissing([
            'textBlocks' => fn ($query) => $query->whereNull('page_id')->orderBy('sort_order')->orderBy('id'),
        ]);

        $allBlocks = ($page->textBlocks ?? collect())
            ->filter(fn ($block) => $block instanceof TextBlock)
            ->values();
        [$blocks, $usedLocale] = $this->filterBlocksByChosenLocale($allBlocks, $preferredLocale, $fallbackLocale);

        $subtitleBlock = $blocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');
        $contentBlocks = $blocks->reject(fn (TextBlock $block) => $block->type === 'subtitle')->values();
        $resolvedPageTitle = $this->resolveLocalizedTitle($page->title, $allBlocks, $preferredLocale, $fallbackLocale);
        $resolvedCategoryTitle = $this->resolveLocalizedTitle(
            $category->title,
            ($category->textBlocks ?? collect())
                ->filter(fn ($block) => $block instanceof TextBlock)
                ->whereNull('page_id')
                ->values(),
            $preferredLocale,
            $fallbackLocale
        );
        $normalizedPayload = $this->normalizedPayload($resolvedPageTitle, $subtitleBlock, $contentBlocks, $usedLocale);

        return [
            'target_type' => 'page',
            'target_category_slug' => $category->slug,
            'target_page_slug' => $page->slug,
            'requested_locale' => $preferredLocale,
            'source_locale' => $usedLocale,
            'source_url' => localized_route('theory.show', [
                'category' => $category->slug,
                'pageSlug' => $page->slug,
            ], false, $preferredLocale),
            'page_title' => $resolvedPageTitle,
            'category_title' => $resolvedCategoryTitle,
            'page_seeder_class' => $page->seeder ?: null,
            'category_seeder_class' => $category->seeder ?: null,
            'normalized_payload' => $normalizedPayload,
            'normalized_payload_json' => $this->encodePrettyJson($normalizedPayload),
            'blocks_count' => count($normalizedPayload['blocks']),
            'child_pages' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveCategorySource(PageCategory $category, string $preferredLocale, string $fallbackLocale): array
    {
        $category->loadMissing([
            'textBlocks' => fn ($query) => $query->whereNull('page_id')->orderBy('sort_order')->orderBy('id'),
            'pages' => fn ($query) => $query->forType('theory')->orderBy('title'),
        ]);

        $allBlocks = ($category->textBlocks ?? collect())
            ->filter(fn ($block) => $block instanceof TextBlock)
            ->whereNull('page_id')
            ->values();
        [$blocks, $usedLocale] = $this->filterBlocksByChosenLocale($allBlocks, $preferredLocale, $fallbackLocale);

        $subtitleBlock = $blocks->firstWhere(fn (TextBlock $block) => $block->type === 'subtitle');
        $contentBlocks = $blocks->reject(fn (TextBlock $block) => $block->type === 'subtitle')->values();
        $resolvedCategoryTitle = $this->resolveLocalizedTitle($category->title, $allBlocks, $preferredLocale, $fallbackLocale);
        $normalizedPayload = $this->normalizedPayload($resolvedCategoryTitle, $subtitleBlock, $contentBlocks, $usedLocale);

        $childPages = collect($category->pages ?? [])
            ->map(fn (Page $page) => [
                'title' => $page->title,
                'slug' => $page->slug,
            ])
            ->values()
            ->all();

        return [
            'target_type' => 'category',
            'target_category_slug' => $category->slug,
            'target_page_slug' => null,
            'requested_locale' => $preferredLocale,
            'source_locale' => $usedLocale,
            'source_url' => localized_route('theory.category', ['category' => $category->slug], false, $preferredLocale),
            'page_title' => null,
            'category_title' => $resolvedCategoryTitle,
            'page_seeder_class' => null,
            'category_seeder_class' => $category->seeder ?: null,
            'normalized_payload' => $normalizedPayload,
            'normalized_payload_json' => $this->encodePrettyJson($normalizedPayload),
            'blocks_count' => count($normalizedPayload['blocks']),
            'child_pages' => $childPages,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pageOptionSummary(Page $page): array
    {
        $category = $page->category;
        $categoryPath = $category instanceof PageCategory ? $this->categoryPath($category) : collect();
        $categoryTitles = $categoryPath->map(fn (PageCategory $item) => $item->title)->all();
        $categorySlugs = $categoryPath->map(fn (PageCategory $item) => $item->slug)->all();

        return [
            'id' => $page->getKey(),
            'title' => $page->title,
            'slug' => $page->slug,
            'category_title' => $category?->title,
            'category_slug' => $category?->slug,
            'category_path' => implode(' / ', $categoryTitles),
            'category_slug_path' => implode('/', $categorySlugs),
            'page_seeder_class' => $page->seeder ?: null,
            'source_url' => $category instanceof PageCategory
                ? localized_route('theory.show', ['category' => $category->slug, 'pageSlug' => $page->slug], false)
                : null,
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

        while ($current instanceof PageCategory && $depth < 10) {
            $path->prepend($current);
            $current->loadMissing('parent');
            $current = $current->parent;
            $depth++;
        }

        return $path->values();
    }

    /**
     * @param  Collection<int, TextBlock>  $blocks
     * @return array{0: Collection<int, TextBlock>, 1: string}
     */
    protected function filterBlocksByChosenLocale(Collection $blocks, string $preferredLocale, string $fallbackLocale): array
    {
        $usedLocale = $this->chooseLocaleForBlocks($blocks, $preferredLocale, $fallbackLocale);

        return [
            $blocks->where('locale', $usedLocale)->values(),
            $usedLocale,
        ];
    }

    /**
     * @param  Collection<int, TextBlock>  $blocks
     */
    protected function chooseLocaleForBlocks(Collection $blocks, string $preferredLocale, string $fallbackLocale): string
    {
        if ($blocks->firstWhere('locale', $preferredLocale)) {
            return $preferredLocale;
        }

        if ($blocks->firstWhere('locale', $fallbackLocale)) {
            return $fallbackLocale;
        }

        return $preferredLocale;
    }

    /**
     * @param  Collection<int, TextBlock>  $contentBlocks
     * @return array<string, mixed>
     */
    protected function normalizedPayload(string $title, ?TextBlock $subtitleBlock, Collection $contentBlocks, string $locale): array
    {
        return [
            'title' => $title,
            'subtitle_html' => $subtitleBlock?->body ?: null,
            'subtitle_text' => $subtitleBlock?->body
                ? TheoryVariantPayloadSanitizer::sanitizeText($subtitleBlock->body)
                : null,
            'locale' => TheoryVariantPayloadSanitizer::sanitizeLocale($locale),
            'blocks' => $contentBlocks
                ->map(fn (TextBlock $block) => [
                    'column' => $block->column ?: null,
                    'type' => $block->type ?: null,
                    'heading' => filled($block->heading) ? $block->heading : null,
                    'body' => filled($block->body) ? $block->body : null,
                    'sort_order' => (int) ($block->sort_order ?? 0),
                    'level' => filled($block->level) ? $block->level : null,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function encodePrettyJson(array $payload): string
    {
        return (string) json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param  Collection<int, TextBlock>  $blocks
     */
    protected function resolveLocalizedTitle(
        string $fallbackTitle,
        Collection $blocks,
        string $preferredLocale,
        string $fallbackLocale
    ): string {
        $subtitleBlocks = $blocks
            ->filter(fn (TextBlock $block) => $block->type === 'subtitle')
            ->values();

        foreach (array_values(array_unique(array_filter([$preferredLocale, $fallbackLocale]))) as $locale) {
            $localizedTitle = $subtitleBlocks
                ->first(function (TextBlock $block) use ($locale) {
                    if ($block->locale !== $locale) {
                        return false;
                    }

                    return $this->extractLocalizedTitleFromSubtitle($block->body) !== null;
                });

            if (! $localizedTitle instanceof TextBlock) {
                continue;
            }

            $resolved = $this->extractLocalizedTitleFromSubtitle($localizedTitle->body);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return $fallbackTitle;
    }

    protected function extractLocalizedTitleFromSubtitle(?string $body): ?string
    {
        if (! is_string($body) || trim($body) === '') {
            return null;
        }

        $plainText = $this->normalizeLocalizedTitleCandidate(
            strip_tags(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        );

        if ($plainText === '') {
            return null;
        }

        $candidates = [];

        if (preg_match('/^\s*(?:<p\b[^>]*>\s*)?<strong\b[^>]*>(.*?)<\/strong>/isu', $body, $matches) === 1) {
            $candidate = $this->normalizeLocalizedTitleCandidate($matches[1] ?? null);

            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        foreach ([
            '/^(.+?)\s+[—-]\s+/u',
            '/^(.+?)\s+\b(?:to|jest|są|służy|używa się|używane|używany|oznacza|opisuje|pokazuje|is|are|means|describes|shows|used)\b/iu',
        ] as $pattern) {
            if (preg_match($pattern, $plainText, $matches) === 1) {
                $candidate = $this->normalizeLocalizedTitleCandidate($matches[1] ?? null);

                if ($candidate !== '') {
                    $candidates[] = $candidate;
                }
            }
        }

        if (preg_match('/<strong\b[^>]*>(.*?)<\/strong>/isu', $body, $matches) === 1) {
            $candidate = $this->normalizeLocalizedTitleCandidate($matches[1] ?? null);

            if ($candidate !== '' && ! in_array($candidate, $candidates, true)) {
                $candidates[] = $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            if ($this->isValidLocalizedTitleCandidate($candidate)) {
                return $candidate;
            }
        }

        return $this->isValidLocalizedTitleCandidate($plainText) ? $plainText : null;
    }

    protected function normalizeLocalizedTitleCandidate(?string $value): string
    {
        $normalized = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', strip_tags($normalized)) ?? $normalized;

        return trim($normalized, " \t\n\r\0\x0B-–—:;,.|");
    }

    protected function isValidLocalizedTitleCandidate(string $candidate): bool
    {
        $length = mb_strlen($candidate);

        if ($length < 3 || $length > 110) {
            return false;
        }

        if (preg_match('/[.?!]$/u', $candidate) === 1 && $length > 40) {
            return false;
        }

        return true;
    }
}
