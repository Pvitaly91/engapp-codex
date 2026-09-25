<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\SiteTreeItem;
use App\Models\SiteTreeVariant;
use App\Models\TextBlock;
use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TheoryCourseManifestService
{
    public const COURSE_SLUG = 'english-grammar-theory';

    public function __construct(
        private TheoryCourseTestPoolService $testPoolService,
    ) {}

    /**
     * Inspect the real first course entry without warming the manifest/virtual-test caches.
     * Page.text is deliberately ignored: the course renderer only renders TextBlocks.
     */
    public function sitemapEntryPath(string $locale = 'uk'): ?string
    {
        $firstPage = $this->firstPageInTree($this->categoryTree($locale, true));
        if (! $firstPage || trim((string) $firstPage->slug) === '' || strpbrk((string) $firstPage->slug, '/?#\\') !== false
            // findLesson() compares normalized input against the manifest's raw page_slug.
            || (string) $firstPage->slug !== $this->normalizeSlug($firstPage->slug)
            || trim((string) $firstPage->title) === '') {
            return null;
        }
        $path = '/courses/'.self::COURSE_SLUG.'/lesson/'.$firstPage->getAttribute('sitemap_category_path')
            .'/'.rawurlencode($this->normalizeSlug($firstPage->slug));
        foreach (TextBlock::query()->where('page_id', $firstPage->getKey())->where('locale', $locale)
            ->orderBy('sort_order')->get(['type', 'body']) as $block) {
            $type = (string) $block->type;
            if ($type === '' || $type === 'box') {
                if ($this->hasVisibleText($block->body)) {
                    return $path;
                }
                continue;
            }
            $body = json_decode((string) $block->body, true);
            if (! is_array($body)) {
                continue;
            }
            // Only instructional fields rendered by the existing course partial qualify.
            $fields = match ($type) {
                'hero', 'hero-v2' => ['intro', 'rules.*.text', 'rules.*.example'],
                'forms-grid' => ['intro', 'items.*.subtitle', 'items.*.rules.*.formula', 'items.*.rules.*.text', 'items.*.rules.*.example'],
                'usage-panels' => ['sections.*.description', 'sections.*.examples.*.en', 'sections.*.examples.*.ua', 'sections.*.note'],
                'comparison-table' => ['intro', 'rows.*.en', 'rows.*.ua', 'rows.*.note', 'warning'],
                'mistakes-grid' => ['items.*.wrong', 'items.*.right', 'items.*.hint'],
                'summary-list' => ['items'],
                'practice-set' => ['selects.*.label', 'choices.*.label', 'choices.*.prompt', 'inputs.*.before', 'inputs.*.after', 'rephrase.*.original'],
                default => [],
            };
            foreach ($fields as $field) {
                if ($this->hasVisibleText(data_get($body, $field))) {
                    return $path;
                }
            }
        }

        return null;
    }

    private function firstPageInTree(Collection $categories, array $parents = []): ?Page
    {
        foreach ($categories as $category) {
            if (trim((string) $category->slug) === '' || strpbrk((string) $category->slug, '/?#\\') !== false) {
                return null;
            }
            $path = [...$parents, rawurlencode($this->normalizeSlug($category->slug))];
            foreach ($category->getAttribute('ordered_tree_items') ?? [] as $item) {
                if (($item['type'] ?? null) === 'page' && ($item['model'] ?? null) instanceof Page) {
                    return $item['model']->setAttribute('sitemap_category_path', implode('/', $path));
                }
                if (($item['type'] ?? null) === 'category' && ($item['model'] ?? null) instanceof PageCategory) {
                    $page = $this->firstPageInTree(collect([$item['model']]), $path);
                    if ($page) {
                        return $page;
                    }
                }
            }
        }

        return null;
    }

    private function hasVisibleText(mixed $value): bool
    {
        if (is_array($value)) {
            return array_is_list($value) && collect($value)->contains(fn (mixed $part): bool => $this->hasVisibleText($part));
        }
        if (! is_string($value)) {
            return false;
        }

        $visible = preg_replace('~<(script|style|template|noscript)\b[^>]*>.*?</\1\s*>~is', '', $value) ?? '';

        return preg_match('/[\p{L}\p{N}]/u', html_entity_decode(strip_tags($visible), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === 1;
    }

    public function build(string $courseSlug = self::COURSE_SLUG, bool $fresh = false): array
    {
        $courseSlug = $this->normalizeSlug($courseSlug);

        if ($courseSlug !== self::COURSE_SLUG) {
            return $this->emptyManifest($courseSlug);
        }

        $cacheKey = $this->cacheKey($courseSlug);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            $cacheKey,
            now()->addHour(),
            fn (): array => $this->buildUncached()
        );
    }

    private function buildUncached(): array
    {
        $categories = $this->categoryTree();
        $sections = [];
        $flatLessons = [];
        $lessonOrder = 1;
        $sectionOrder = 1;

        foreach ($categories as $category) {
            $this->appendCategoryLessons(
                $category,
                $sections,
                $flatLessons,
                $lessonOrder,
                $sectionOrder
            );
        }

        $this->attachAdjacentLessons($flatLessons);

        return [
            'course_slug' => self::COURSE_SLUG,
            'title' => 'English Grammar Theory Course',
            'description' => __('public.courses.theory_course_description'),
            'course' => [
                'slug' => self::COURSE_SLUG,
                'name' => 'English Grammar Theory Course',
                'description' => __('public.courses.theory_course_description'),
                'url' => $this->localizedPath('/courses/'.self::COURSE_SLUG),
            ],
            'sections' => array_values($sections),
            'flat_lessons' => array_values($flatLessons),
            'lessons' => array_values($flatLessons),
            'by_slug' => collect($flatLessons)->keyBy('lesson_slug')->all(),
            'first_lesson' => $flatLessons[0] ?? null,
            'total_lessons' => count($flatLessons),
        ];
    }

    public function findLesson(array $manifest, string $categoryPath, string $pageSlug): ?array
    {
        $categoryPath = $this->normalizeCategorySlugPath($categoryPath);
        $pageSlug = $this->normalizeSlug($pageSlug);

        foreach (($manifest['flat_lessons'] ?? []) as $lesson) {
            if (
                is_array($lesson)
                && ($lesson['category_slug_path'] ?? null) === $categoryPath
                && ($lesson['page_slug'] ?? null) === $pageSlug
            ) {
                return $lesson;
            }
        }

        return null;
    }

    public function findLessonBySlug(array $manifest, string $lessonSlug): ?array
    {
        $lesson = $manifest['by_slug'][$lessonSlug] ?? null;

        return is_array($lesson) ? $lesson : null;
    }

    public function firstLesson(array $manifest): ?array
    {
        $lesson = $manifest['first_lesson'] ?? null;

        return is_array($lesson) ? $lesson : null;
    }

    public function previousLesson(array $manifest, string $lessonSlug): ?array
    {
        $lesson = $this->findLessonBySlug($manifest, $lessonSlug);

        return is_array($lesson) && filled($lesson['previous_lesson_slug'] ?? null)
            ? $this->findLessonBySlug($manifest, $lesson['previous_lesson_slug'])
            : null;
    }

    public function nextLesson(array $manifest, string $lessonSlug): ?array
    {
        $lesson = $this->findLessonBySlug($manifest, $lessonSlug);

        return is_array($lesson) && filled($lesson['next_lesson_slug'] ?? null)
            ? $this->findLessonBySlug($manifest, $lesson['next_lesson_slug'])
            : null;
    }

    public function resolvePageForLesson(array $lesson): ?Page
    {
        $pageId = (int) ($lesson['page_id'] ?? 0);

        if ($pageId <= 0) {
            return null;
        }

        return Page::query()
            ->with([
                'category.parent.parent.parent.parent.parent',
                'tags',
                'textBlocks' => fn ($query) => $query
                    ->whereIn('locale', $this->contentLocales())
                    ->with('tags')
                    ->orderBy('sort_order'),
            ])
            ->whereKey($pageId)
            ->where('type', 'theory')
            ->first();
    }

    public function lessonUrl(string $categoryPath, string $pageSlug, bool $test = false): string
    {
        $path = sprintf(
            '/courses/%s/lesson/%s/%s',
            self::COURSE_SLUG,
            trim($this->normalizeCategorySlugPath($categoryPath), '/'),
            $this->normalizeSlug($pageSlug)
        );

        return $this->localizedPath($test ? $path.'/test' : $path);
    }

    public function theoryUrl(string $categoryPath, string $pageSlug): string
    {
        return $this->localizedPath(sprintf(
            '/theory/%s/%s',
            trim($this->normalizeCategorySlugPath($categoryPath), '/'),
            $this->normalizeSlug($pageSlug)
        ));
    }

    private function appendCategoryLessons(
        PageCategory $category,
        array &$sections,
        array &$flatLessons,
        int &$lessonOrder,
        int &$sectionOrder
    ): void {
        $sectionKey = $this->categorySlugPath($category);
        $sectionIndex = null;

        $items = $category->getAttribute('ordered_tree_items');
        $items = $items instanceof Collection ? $items : $this->categoryItems($category);

        foreach ($items as $item) {
            $type = $item['type'] ?? null;
            $model = $item['model'] ?? null;

            if ($type === 'category' && $model instanceof PageCategory) {
                $this->appendCategoryLessons($model, $sections, $flatLessons, $lessonOrder, $sectionOrder);

                continue;
            }

            if ($type !== 'page' || ! $model instanceof Page) {
                continue;
            }

            if ($sectionIndex === null) {
                $sectionIndex = count($sections);
                $sections[] = [
                    'category_slug' => $category->slug,
                    'category_slug_path' => $sectionKey,
                    'category_title' => $category->title,
                    'order' => $sectionOrder++,
                    'lessons' => [],
                ];
            }

            $summary = $this->testPoolService->summaryForPage($model);
            $lessonSlug = $this->lessonSlug($sectionKey, $model->slug);
            $lesson = [
                'slug' => $lessonSlug,
                'lesson_slug' => $lessonSlug,
                'category_slug' => $category->slug,
                'category_slug_path' => $sectionKey,
                'category_title' => $category->title,
                'page_slug' => $model->slug,
                'page_id' => (int) $model->getKey(),
                'title' => $model->title,
                'name' => $model->title,
                'theory_url' => $this->theoryUrl($sectionKey, $model->slug),
                'lesson_url' => $this->lessonUrl($sectionKey, $model->slug),
                'test_url' => $this->lessonUrl($sectionKey, $model->slug, true),
                'order' => $lessonOrder,
                'lesson_order' => $lessonOrder,
                'previous_lesson_slug' => null,
                'next_lesson_slug' => null,
                'related_tests' => $summary['tests'] ?? [],
                'has_tests' => (bool) ($summary['has_tests'] ?? false),
                'test_counts' => $summary['counts'] ?? [],
            ];

            $sections[$sectionIndex]['lessons'][] = $lesson;
            $flatLessons[] = $lesson;
            $lessonOrder++;
        }
    }

    private function attachAdjacentLessons(array &$flatLessons): void
    {
        $count = count($flatLessons);

        for ($index = 0; $index < $count; $index++) {
            $flatLessons[$index]['previous_lesson_slug'] = $flatLessons[$index - 1]['lesson_slug'] ?? null;
            $flatLessons[$index]['next_lesson_slug'] = $flatLessons[$index + 1]['lesson_slug'] ?? null;
        }
    }

    private function categoryTree(?string $locale = null, bool $metadataOnly = false): Collection
    {
        $language = $locale ?? $this->resolvedCategoryLanguage();
        $categories = PageCategory::query()
            ->whereNull('parent_id')
            ->where('type', 'theory')
            ->when($language, fn ($query, string $lang) => $query->where('language', $lang))
            ->with([
                'pages' => fn ($query) => $query->where('type', 'theory')->orderBy('title')
                    ->when($metadataOnly, fn ($q) => $q->select(['id', 'page_category_id', 'slug', 'title', 'type'])),
                'children' => function ($query) use ($language, $metadataOnly): void {
                    $this->applyChildRelations($query, $language, $metadataOnly);
                },
            ])
            ->orderBy('title')
            ->get();

        $ordering = $this->siteTreeOrdering($this->collectTheoryTitles($categories));

        return $this->applyTheoryRootCategoryOrdering(
            $this->applySiteTreeOrdering($categories, $ordering)
        );
    }

    private function applyChildRelations($query, ?string $language, bool $metadataOnly = false): void
    {
        $query->where('type', 'theory')
            ->when($language, fn ($q, string $lang) => $q->where('language', $lang))
            ->with([
                'pages' => fn ($q) => $q->where('type', 'theory')->orderBy('title')
                    ->when($metadataOnly, fn ($pageQuery) => $pageQuery->select(['id', 'page_category_id', 'slug', 'title', 'type'])),
                'children' => function ($childQuery) use ($language, $metadataOnly): void {
                    $this->applyChildRelations($childQuery, $language, $metadataOnly);
                },
            ])
            ->orderBy('title');
    }

    private function resolvedCategoryLanguage(): ?string
    {
        $preferred = app()->getLocale() ?: $this->fallbackLocale();
        $fallback = $this->fallbackLocale();

        $baseQuery = PageCategory::query()
            ->whereNull('parent_id')
            ->where('type', 'theory');

        if ((clone $baseQuery)->where('language', $preferred)->exists()) {
            return $preferred;
        }

        if ((clone $baseQuery)->where('language', $fallback)->exists()) {
            return $fallback;
        }

        return null;
    }

    private function contentLocales(): array
    {
        return array_values(array_unique(array_filter([
            app()->getLocale() ?: $this->fallbackLocale(),
            $this->fallbackLocale(),
        ])));
    }

    private function fallbackLocale(): string
    {
        return LocaleService::getDefaultLanguage()?->code
            ?? config('language-manager.fallback_locale', config('app.locale', 'uk'));
    }

    private function siteTreeOrdering(array $availableTitles): array
    {
        $variant = SiteTreeVariant::getBase();

        if (! $variant) {
            return [];
        }

        $availableLookup = [];
        foreach ($availableTitles as $title) {
            $availableLookup[$title] = true;
            $availableLookup[$this->normalizeTitle($title)] = true;
        }

        $roots = SiteTreeItem::query()
            ->where('variant_id', $variant->id)
            ->whereNull('parent_id')
            ->with(['children' => function ($query): void {
                $query->with('children')->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $ordering = [];
        $position = 0;
        $collect = function ($items) use (&$collect, &$ordering, &$position, $availableLookup): void {
            foreach ($items as $item) {
                $linkedTitle = $item->linked_page_title ?: $this->matchingTheoryTitle((string) $item->title, $availableLookup);

                if ($linkedTitle) {
                    $normalized = $this->normalizeTitle($linkedTitle);
                    $ordering[$linkedTitle] = $position;
                    $ordering[$normalized] = $position;
                    $position++;
                }

                if ($item->children?->isNotEmpty()) {
                    $collect($item->children);
                }
            }
        };

        $collect($roots);

        return $ordering;
    }

    private function applySiteTreeOrdering(Collection $categories, array $ordering): Collection
    {
        return $this->sortCollectionByOrdering(
            $categories->map(function (PageCategory $category) use ($ordering) {
                if ($category->relationLoaded('children')) {
                    $category->setRelation('children', $this->applySiteTreeOrdering($category->children, $ordering));
                }

                if ($category->relationLoaded('pages')) {
                    $category->setRelation(
                        'pages',
                        $this->sortCollectionByOrdering($category->pages, $ordering, fn (Page $page) => $page->title)
                    );
                }

                $category->setAttribute('ordered_tree_items', $this->categoryItems($category, $ordering));

                return $category;
            }),
            $ordering,
            fn (PageCategory $category) => $category->title
        );
    }

    private function categoryItems(PageCategory $category, array $ordering = []): Collection
    {
        $items = collect();

        if ($category->relationLoaded('children')) {
            $items = $items->concat($category->children->map(fn (PageCategory $child) => [
                'type' => 'category',
                'model' => $child,
            ]));
        }

        if ($category->relationLoaded('pages')) {
            $items = $items->concat($category->pages->map(fn (Page $page) => [
                'type' => 'page',
                'model' => $page,
            ]));
        }

        return $this->sortCollectionByOrdering($items, $ordering, fn (array $item) => $item['model']->title ?? '');
    }

    private function sortCollectionByOrdering(Collection $items, array $ordering, callable $getTitle): Collection
    {
        return $items
            ->sortBy(function ($item) use ($ordering, $getTitle) {
                $title = (string) $getTitle($item);

                return [
                    $this->orderingValue($title, $ordering),
                    mb_strtolower($title),
                ];
            })
            ->values();
    }

    private function applyTheoryRootCategoryOrdering(Collection $categories): Collection
    {
        $ordering = array_flip([
            'basic-grammar',
            'imennyky-artykli-ta-kilkist',
            'zaimennyky-ta-vkazivni-slova',
            'maibutni-formy',
            'pytalni-rechennia-ta-zaperechennia',
            'prykmetniky-ta-pryslinknyky',
            'some-any',
            'tenses',
            'passive-voice',
            'modal-verbs',
            'conditionals',
            'reported-speech',
            'clauses-and-linking-words',
            'prepositions-and-phrasal-verbs',
            'common-mistakes',
            'sentence-transformations',
            'verb-patterns',
            'mixed-revision',
        ]);

        return $categories
            ->sortBy(fn (PageCategory $category) => [
                $ordering[$category->slug] ?? PHP_INT_MAX,
                mb_strtolower($category->title),
            ])
            ->values();
    }

    private function collectTheoryTitles(Collection $categories): array
    {
        $titles = [];

        foreach ($categories as $category) {
            $titles[] = $category->title;

            if ($category->relationLoaded('pages')) {
                foreach ($category->pages as $page) {
                    $titles[] = $page->title;
                }
            }

            if ($category->relationLoaded('children') && $category->children->isNotEmpty()) {
                $titles = array_merge($titles, $this->collectTheoryTitles($category->children));
            }
        }

        return $titles;
    }

    private function matchingTheoryTitle(string $title, array $availableLookup): ?string
    {
        if (isset($availableLookup[$title])) {
            return $title;
        }

        $normalized = $this->normalizeTitle($title);

        return isset($availableLookup[$normalized]) ? $normalized : null;
    }

    private function orderingValue(string $title, array $ordering): int
    {
        $normalized = $this->normalizeTitle($title);

        return $ordering[$title] ?? $ordering[$normalized] ?? PHP_INT_MAX;
    }

    private function normalizeTitle(string $title): string
    {
        $cleaned = preg_replace('/^\d+[.\d\s]*\s*/u', '', $title) ?? $title;

        return Str::lower(trim($cleaned));
    }

    private function lessonSlug(string $categoryPath, string $pageSlug): string
    {
        return 'theory:'.$this->normalizeCategorySlugPath($categoryPath).':'.$this->normalizeSlug($pageSlug);
    }

    private function categorySlugPath(PageCategory $category): string
    {
        $segments = [];
        $current = $category;
        $depth = 0;

        while ($current instanceof PageCategory && $depth < 10) {
            $slug = $this->normalizeSlug($current->slug);

            if ($slug !== '') {
                array_unshift($segments, $slug);
            }

            $current->loadMissing('parent');
            $current = $current->parent;
            $depth++;
        }

        return $this->normalizeCategorySlugPath(implode('/', $segments));
    }

    private function normalizeCategorySlugPath(string $value): string
    {
        return implode('/', array_values(array_filter(
            explode('/', str_replace('\\', '/', Str::lower(trim($value, " \t\n\r\0\x0B/")))),
            'strlen'
        )));
    }

    private function normalizeSlug(mixed $value): string
    {
        return Str::lower(trim((string) ($value ?? '')));
    }

    private function localizedPath(string $path): string
    {
        $path = '/'.ltrim($path, '/');
        $locale = app()->getLocale();
        $defaultLocale = LocaleService::getDefaultLocaleCode();

        return $locale && $locale !== $defaultLocale
            ? '/'.trim($locale, '/').$path
            : $path;
    }

    private function emptyManifest(string $courseSlug): array
    {
        return [
            'course_slug' => $courseSlug,
            'title' => Str::of($courseSlug)->replace('-', ' ')->headline()->toString(),
            'description' => '',
            'course' => [
                'slug' => $courseSlug,
                'name' => Str::of($courseSlug)->replace('-', ' ')->headline()->toString(),
                'description' => '',
                'url' => null,
            ],
            'sections' => [],
            'flat_lessons' => [],
            'lessons' => [],
            'by_slug' => [],
            'first_lesson' => null,
            'total_lessons' => 0,
        ];
    }

    private function cacheKey(string $courseSlug): string
    {
        return implode(':', [
            'theory_course_manifest',
            $courseSlug,
            app()->getLocale() ?: $this->fallbackLocale(),
        ]);
    }
}
