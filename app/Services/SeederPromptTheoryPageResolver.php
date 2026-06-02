<?php

namespace App\Services;

use App\Models\Page;
use App\Support\Database\JsonTestSeeder;
use App\Support\PromptGeneratorFilterNormalizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SeederPromptTheoryPageResolver
{
    /**
     * @var array<string, array<string, mixed>|null>
     */
    private array $candidateCache = [];

    public function resolveForSeeder(string $className): ?array
    {
        return $this->resolveForSeeders([$className])->get($className);
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return Collection<string, array<string, mixed>>
     */
    public function resolveForSeeders(iterable $classNames): Collection
    {
        $normalizedClassNames = collect($classNames)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedClassNames->isEmpty()) {
            return collect();
        }

        $candidates = $normalizedClassNames
            ->mapWithKeys(function (string $className) {
                $candidate = $this->extractCandidate($className);

                return $candidate !== null ? [$className => $candidate] : [];
            });

        if ($candidates->isEmpty()) {
            return collect();
        }

        $pagesById = Page::query()
            ->with('category')
            ->where('type', 'theory')
            ->whereIn('id', $candidates->pluck('page_id')->filter()->map(fn ($id) => (int) $id)->all())
            ->get()
            ->keyBy('id');

        $pagesBySlug = Page::query()
            ->with('category')
            ->where('type', 'theory')
            ->whereIn('slug', $candidates->pluck('page_slug')->filter()->all())
            ->get()
            ->groupBy('slug');

        return $candidates
            ->mapWithKeys(function (array $candidate, string $className) use ($pagesById, $pagesBySlug) {
                $page = null;
                $pageId = (int) ($candidate['page_id'] ?? 0);
                $pageSlug = trim((string) ($candidate['page_slug'] ?? ''));

                if ($pageId > 0) {
                    $page = $pagesById->get($pageId);
                }

                if (! $page instanceof Page && $pageSlug !== '') {
                    $page = $this->matchPageBySlug(
                        collect($pagesBySlug->get($pageSlug, collect())),
                        $candidate
                    );
                }

                $target = $this->buildTarget($candidate, $page);

                return $target !== null ? [$className => $target] : [];
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractCandidate(string $className): ?array
    {
        if (array_key_exists($className, $this->candidateCache)) {
            return $this->candidateCache[$className];
        }

        if (! class_exists($className) || ! is_subclass_of($className, JsonTestSeeder::class)) {
            return $this->candidateCache[$className] = null;
        }

        $definition = $this->loadDefinition($className);

        if (! is_array($definition)) {
            return $this->candidateCache[$className] = null;
        }

        $promptGenerator = PromptGeneratorFilterNormalizer::normalize(
            Arr::get($definition, 'saved_test.filters.prompt_generator', Arr::get($definition, 'filters.prompt_generator'))
        );

        if (! is_array($promptGenerator)) {
            return $this->candidateCache[$className] = null;
        }

        $pageId = (int) (
            $promptGenerator['theory_page_id']
            ?? Arr::get($promptGenerator, 'theory_page.id', 0)
        );

        $pageIds = collect(is_array($promptGenerator['theory_page_ids'] ?? null) ? $promptGenerator['theory_page_ids'] : [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->values();

        if ($pageId <= 0) {
            $pageId = (int) ($pageIds->first() ?? 0);
        }

        $pageSlug = trim((string) Arr::get($promptGenerator, 'theory_page.slug', ''));
        $pageTitle = trim((string) Arr::get($promptGenerator, 'theory_page.title', ''));
        $pageUrl = trim((string) Arr::get($promptGenerator, 'theory_page.url', ''));
        $categorySlug = $this->normalizeCategorySlug((string) Arr::get($promptGenerator, 'theory_page.category_slug_path', ''));
        $sourceType = trim((string) ($promptGenerator['source_type'] ?? ''));

        if ($sourceType !== 'theory_page' && $pageId <= 0 && $pageSlug === '' && $pageUrl === '') {
            return $this->candidateCache[$className] = null;
        }

        return $this->candidateCache[$className] = [
            'page_id' => $pageId > 0 ? $pageId : null,
            'page_slug' => $pageSlug !== '' ? $pageSlug : null,
            'page_title' => $pageTitle !== '' ? $pageTitle : null,
            'page_url' => $pageUrl !== '' ? $pageUrl : null,
            'category_slug' => $categorySlug !== '' ? $categorySlug : null,
        ];
    }

    protected function normalizeCategorySlug(string $categorySlugPath): string
    {
        $segments = array_values(array_filter(
            explode('/', str_replace('\\', '/', trim($categorySlugPath))),
            'strlen'
        ));

        return trim((string) end($segments));
    }

    protected function matchPageBySlug(Collection $pages, array $candidate): ?Page
    {
        if ($pages->isEmpty()) {
            return null;
        }

        $categorySlug = trim((string) ($candidate['category_slug'] ?? ''));

        if ($categorySlug !== '') {
            $page = $pages->first(function ($page) use ($categorySlug) {
                return $page instanceof Page
                    && trim((string) ($page->category?->slug ?? '')) === $categorySlug;
            });

            if ($page instanceof Page) {
                return $page;
            }
        }

        $page = $pages->first();

        return $page instanceof Page ? $page : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildTarget(array $candidate, ?Page $page): ?array
    {
        if ($page instanceof Page && filled($page->slug) && filled($page->category?->slug)) {
            return [
                'label' => __('Пов’язана сторінка теорії'),
                'title' => (string) ($page->title ?: ($candidate['page_title'] ?? $page->slug)),
                'url' => localized_route('theory.show', [$page->category->slug, $page->slug]),
            ];
        }

        $url = trim((string) ($candidate['page_url'] ?? ''));

        if ($url === '') {
            return null;
        }

        $title = trim((string) ($candidate['page_title'] ?? ''));

        if ($title === '') {
            $title = trim((string) ($candidate['page_slug'] ?? ''));
        }

        if ($title === '') {
            $title = $url;
        }

        return [
            'label' => __('Пов’язана сторінка теорії'),
            'title' => $title,
            'url' => $url,
        ];
    }

    protected function loadDefinition(string $className): ?array
    {
        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract() || ! $reflection->isInstantiable() || ! $reflection->hasMethod('definitionPath')) {
                return null;
            }

            $instance = app()->make($className);
            $methodReflection = $reflection->getMethod('definitionPath');

            if (method_exists($methodReflection, 'setAccessible')) {
                $methodReflection->setAccessible(true);
            }

            $definitionPath = $methodReflection->invoke($instance);
        } catch (\Throwable) {
            return null;
        }

        if (! is_string($definitionPath) || $definitionPath === '' || ! File::exists($definitionPath)) {
            return null;
        }

        $decoded = json_decode((string) File::get($definitionPath), true);

        return is_array($decoded) ? $decoded : null;
    }
}
