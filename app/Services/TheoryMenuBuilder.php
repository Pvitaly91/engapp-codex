<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\SiteMode;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/** Command-only coordination of fresh catalog, neutral render, validation and publication. */
final class TheoryMenuBuilder
{
    public function __construct(private TheoryMenuCatalog $catalog, private TheoryMenuStore $store, private SiteMode $siteMode) {}

    public function context(): array
    {
        $origin = config('app.url');
        $parts = is_string($origin) ? parse_url($origin) : false;
        if (!is_array($parts) || !in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new RuntimeException('A valid configured APP_URL is required for theory menu generation.');
        }
        $request = Request::create($origin, 'GET');
        $mode = $this->siteMode->current($request);
        $locales = array_values(array_intersect(
            $this->siteMode->availableLocales(LocaleService::getSupportedLocaleCodes(), $request),
            LocaleService::getConfiguredSupportedLocales()
        ));
        if ($locales === []) throw new RuntimeException('No enabled, routed theory menu locales for this site mode.');

        return ['mode' => $mode, 'locales' => $locales, 'request' => $request];
    }

    public function build(string $locale): array
    {
        $context = $this->context();
        if (!in_array($locale, $context['locales'], true)) throw new RuntimeException('Theory menu locale is not enabled in this site mode.');

        return $this->store->build($context['mode'], $locale, function () use ($context, $locale): array {
            return $this->inBuildContext($context['request'], $locale, function (): array {
                $categories = $this->catalog->fresh();
                $expected = $this->expectedLinks($categories);
                $fragments = [];
                foreach (TheoryMenuStore::VARIANTS as $variant) {
                    $html = view('theory.partials.menu-cache-fragment', [
                        'categories' => $categories, 'routePrefix' => 'theory', 'variant' => $variant,
                        'selectedCategory' => null, 'currentPage' => null,
                    ])->render();
                    $this->validateFragment($html, $variant, $expected);
                    $fragments[$variant] = $html;
                }

                return ['fragments' => $fragments, 'counts' => ['categories' => count($expected['categories']), 'lessons' => count($expected['lessons'])]];
            });
        });
    }

    private function inBuildContext(Request $request, string $locale, callable $callback): array
    {
        $previousLocale = app()->getLocale();
        $previousRequest = app('request');
        app()->instance('request', $request);
        URL::setRequest($request);
        app()->setLocale($locale);
        try {
            return $callback();
        } finally {
            app()->instance('request', $previousRequest);
            URL::setRequest($previousRequest);
            app()->setLocale($previousLocale);
        }
    }

    private function expectedLinks(Collection $categories): array
    {
        $links = ['categories' => [], 'lessons' => []];
        $visit = function (Collection $nodes) use (&$visit, &$links): void {
            foreach ($nodes as $category) {
                if (!$category instanceof PageCategory || (int) $category->getKey() < 1 || isset($links['categories'][$category->getKey()])
                    || !$category->relationLoaded('pages') || !$category->relationLoaded('children')) {
                    throw new RuntimeException('Theory menu has a duplicate, invalid or unloaded category.');
                }
                $links['categories'][$category->getKey()] = localized_route('theory.category', $category->slug, false);
                foreach ($category->pages as $page) {
                    if (!$page instanceof Page || (int) $page->getKey() < 1 || isset($links['lessons'][$page->getKey()])) {
                        throw new RuntimeException('Theory menu has a duplicate or invalid lesson.');
                    }
                    $links['lessons'][$page->getKey()] = localized_route('theory.show', [$category->public_slug_path ?? $category->slug, $page->slug], false);
                }
                $visit($category->children);
            }
        };
        $visit($categories);
        if ($links['categories'] === [] || $links['lessons'] === []) throw new RuntimeException('Fresh theory menu is empty.');

        return $links;
    }

    private function validateFragment(string $html, string $variant, array $expected): void
    {
        if (trim($html) === '' || preg_match('~<\s*/?\s*(?:script|style|html|head|body|iframe|form|link|meta)\b~i', $html)) {
            throw new RuntimeException('Theory menu fragment is empty or contains document assets.');
        }
        $dom = new DOMDocument('1.0', 'UTF-8');
        if (!@$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new RuntimeException('Theory menu fragment is not valid HTML.');
        }
        $xpath = new DOMXPath($dom);
        $root = $xpath->query('/html/body/*');
        if ($root->length !== 1 || !$root->item(0) instanceof DOMElement || $root->item(0)->getAttribute('data-theory-menu-fragment') !== $variant) {
            throw new RuntimeException('Theory menu fragment root does not match its variant.');
        }
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $element) {
            $id = $element->getAttribute('id');
            if ($id === '' || isset($ids[$id])) throw new RuntimeException('Theory menu fragment has duplicate element IDs.');
            $ids[$id] = true;
        }
        foreach ($xpath->query('//@*') as $attribute) {
            if (preg_match('/^(?:on[a-z]+|aria-current|data-(?:theory-nav-current-page|current-page|selected-category|user-id|session-id|csrf-token))$/i', $attribute->name)
                || ($attribute->name === 'name' && $attribute->value === '_token')) {
                throw new RuntimeException('Theory menu fragment has request-specific or executable attributes.');
            }
        }
        $actual = ['categories' => [], 'lessons' => []];
        foreach ($xpath->query('//a[@href]') as $anchor) {
            $href = $anchor->getAttribute('href');
            if (!str_starts_with($href, '/') || str_starts_with($href, '//') || str_contains($href, '\\')) {
                throw new RuntimeException('Theory menu fragment has a non-relative link.');
            }
            if ($anchor->hasAttribute('data-theory-menu-category-link')) {
                $node = $xpath->query('ancestor::*[@data-theory-menu-category][1]', $anchor)->item(0);
                $key = $node instanceof DOMElement ? $node->getAttribute('data-theory-menu-category') : '';
                $kind = 'categories';
            } else {
                $key = $anchor->getAttribute('data-theory-menu-page');
                $kind = 'lessons';
            }
            if (preg_match('/^[1-9][0-9]*$/D', $key) !== 1 || isset($actual[$kind][$key]) || ($expected[$kind][$key] ?? null) !== $href) {
                throw new RuntimeException('Theory menu fragment has an unbound or duplicate public link.');
            }
            $actual[$kind][$key] = $href;
        }
        foreach ($expected as $kind => $links) {
            if (count($actual[$kind]) !== count($links)) throw new RuntimeException('Theory menu fragment omits public links.');
        }
    }
}
