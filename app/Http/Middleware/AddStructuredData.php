<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\PageCategory;
use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\HtmlResponseContent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AddStructuredData
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $routeName = (string) $request->route()?->getName();

        if (! in_array($request->method(), ['GET', 'HEAD'], true)
            || $response->getStatusCode() !== Response::HTTP_OK
            || ! ($routeName === 'home' || Str::is('theory.*', $routeName))
            || ! Str::startsWith(Str::lower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || ! preg_match('/<\/head\s*>/i', $content)) {
            return $response;
        }

        $viewData = $this->viewData($response);
        $graph = [$this->websiteNode()];

        if (Str::is('theory.*', $routeName)) {
            $category = $viewData['selectedCategory'] ?? null;
            $page = $viewData['page'] ?? null;
            $graph[] = $this->breadcrumbNode(
                $category instanceof PageCategory ? $category : null,
                $page instanceof Page ? $page : null,
                $request
            );

            if ($page instanceof Page) {
                $graph[] = $this->learningResourceNode($page, $request);
            }
        }

        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
                | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );
        $script = '<script type="application/ld+json">'.$json.'</script>';
        $content = preg_replace('/<\/head\s*>/i', "    {$script}\n</head>", $content, 1) ?? $content;
        HtmlResponseContent::replace($response, $content);

        return $response;
    }

    private function websiteNode(): array
    {
        $origin = $this->origin();

        return [
            '@type' => 'WebSite',
            '@id' => $origin.'/#website',
            'url' => $origin.'/',
            'name' => 'Gramlyze',
            'description' => 'Платформа для вивчення та практики англійської граматики.',
            'inLanguage' => 'uk',
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Gramlyze',
                'url' => $origin.'/',
            ],
        ];
    }

    private function breadcrumbNode(?PageCategory $category, ?Page $page, Request $request): array
    {
        $origin = $this->origin();
        $items = [
            ['name' => 'Головна', 'url' => $origin.'/'],
            ['name' => 'Теорія', 'url' => $origin.'/theory'],
        ];

        foreach ($this->categoryAncestors($category) as $ancestor) {
            $items[] = [
                'name' => (string) $ancestor->title,
                'url' => $origin.'/theory/'.rawurlencode((string) $ancestor->slug),
            ];
        }

        if ($page) {
            $items[] = ['name' => (string) $page->title, 'url' => $this->canonicalUrl($request)];
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->canonicalUrl($request).'#breadcrumb',
            'itemListElement' => collect($items)->values()->map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ]
            )->all(),
        ];
    }

    private function learningResourceNode(Page $page, Request $request): array
    {
        $url = $this->canonicalUrl($request);
        $node = [
            '@type' => 'LearningResource',
            '@id' => $url.'#learning-resource',
            'url' => $url,
            'name' => (string) $page->title,
            'headline' => (string) $page->title,
            'description' => $this->pageDescription($page),
            'inLanguage' => 'uk',
            'learningResourceType' => 'Grammar lesson',
            'educationalUse' => 'instruction and practice',
            'isAccessibleForFree' => true,
            'isPartOf' => ['@id' => $this->origin().'/#website'],
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Gramlyze',
                'url' => $this->origin().'/',
            ],
        ];

        if ($page->created_at) {
            $node['datePublished'] = $page->created_at->toAtomString();
        }
        if ($page->updated_at) {
            $node['dateModified'] = $page->updated_at->toAtomString();
        }

        return $node;
    }

    private function pageDescription(Page $page): string
    {
        foreach ($page->textBlocks as $block) {
            $data = json_decode((string) $block->body, true);
            if (! is_array($data)) {
                continue;
            }

            foreach (['intro', 'description', 'summary', 'subtitle', 'text'] as $key) {
                $value = $data[$key] ?? null;
                if (is_string($value) && trim(strip_tags($value)) !== '') {
                    return Str::limit($this->plainText($value), 300, '…');
                }
            }
        }

        $fallback = trim(strip_tags((string) $page->text));

        return $fallback !== ''
            ? Str::limit($this->plainText($fallback), 300, '…')
            : 'Урок англійської граматики: '.$page->title.'.';
    }

    private function plainText(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5)));
    }

    /** @return array<int, PageCategory> */
    private function categoryAncestors(?PageCategory $category): array
    {
        $categories = [];
        $current = $category;
        $depth = 0;

        while ($current instanceof PageCategory && $depth++ < 10) {
            array_unshift($categories, $current);
            $current->loadMissing('parent');
            $current = $current->parent;
        }

        return $categories;
    }

    private function canonicalUrl(Request $request): string
    {
        $segments = $request->segments();
        $locales = array_map('strtolower', LocaleService::getSupportedLocaleCodes());

        if ($segments !== [] && in_array(Str::lower($segments[0]), $locales, true)) {
            array_shift($segments);
        }

        $path = collect($segments)->map('rawurlencode')->implode('/');

        return $this->origin().($path === '' ? '/' : '/'.$path);
    }

    private function origin(): string
    {
        return rtrim((string) config('site-mode.production_origin', 'https://gramlyze.com'), '/');
    }

    private function viewData(Response $response): array
    {
        if (! method_exists($response, 'getOriginalContent')) {
            return [];
        }

        $original = $response->getOriginalContent();

        return $original instanceof View ? $original->getData() : [];
    }
}
