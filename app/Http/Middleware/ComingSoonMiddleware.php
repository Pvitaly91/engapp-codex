<?php

namespace App\Http\Middleware;

use App\Models\SavedGrammarTest;
use App\Services\SavedTestResolver;
use App\Support\SiteMode;
use App\Support\VirtualTestRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('coming-soon.enabled', false)) {
            return $next($request);
        }

        if ($this->shouldBypassInDevelopment($request)) {
            return $next($request);
        }

        // Skip Coming Soon for admin users
        if ($request->hasSession() && $request->session()->get('admin_authenticated', false)) {
            return $next($request);
        }

        if ($this->shouldBypassForAllowedTestSlug($request)) {
            return $next($request);
        }

        // Check if current route name is in the list
        $routes = config('coming-soon.routes', []);
        $currentRouteName = $request->route()?->getName();

        if ($currentRouteName && in_array($currentRouteName, $routes, true)) {
            return $this->comingSoonResponse();
        }

        // Check if current path matches any prefix
        $prefixes = config('coming-soon.prefixes', []);
        $currentPath = '/'.ltrim($request->path(), '/');

        foreach ($prefixes as $prefix) {
            $normalizedPrefix = '/'.ltrim($prefix, '/');
            if (str_starts_with($currentPath, $normalizedPrefix)) {
                return $this->comingSoonResponse();
            }
        }

        return $next($request);
    }

    protected function shouldBypassInDevelopment(Request $request): bool
    {
        if (! app(SiteMode::class)->isDevelopment($request)) {
            return false;
        }

        return $this->pathMatchesAny(
            $request,
            config('coming-soon.development_bypass_prefixes', [])
        );
    }

    protected function pathMatchesAny(Request $request, array $prefixes): bool
    {
        $currentPath = '/'.ltrim($request->path(), '/');

        return collect($prefixes)->contains(function (mixed $prefix) use ($currentPath): bool {
            $normalizedPrefix = '/'.ltrim(trim((string) $prefix), '/');

            return $normalizedPrefix !== '/' && str_starts_with($currentPath, $normalizedPrefix);
        });
    }

    /**
     * Return the Coming Soon response.
     */
    protected function comingSoonResponse(): Response
    {
        $retryAfter = config('coming-soon.retry_after', 86400);

        return response()
            ->view('coming-soon', [], 503)
            ->header('Retry-After', $retryAfter);
    }

    protected function shouldBypassForAllowedTestSlug(Request $request): bool
    {
        $routeName = trim((string) $request->route()?->getName());
        $slug = trim((string) $request->route()?->parameter('slug'));

        if ($routeName === '' || ! str_starts_with($routeName, 'test.') || $slug === '') {
            return false;
        }

        if ($this->isTheoryTestRequest($request, $slug)) {
            $this->rememberTheoryTestSlug($request, $slug);

            return true;
        }

        if ($routeName === 'test.js.questions' && ! $request->wantsJson()) {
            $theorySlug = trim($slug, '/').'/questions';

            if (app(SavedTestResolver::class)->resolveTheoryPageSlug($theorySlug)) {
                $this->rememberTheoryTestSlug($request, $theorySlug);

                return true;
            }
        }

        if ($this->isRememberedTheoryTestSlug($request, $slug)) {
            return true;
        }

        if (VirtualTestRegistry::has($slug)) {
            return true;
        }

        $allowedPrefixes = config('coming-soon.allowed_test_slug_prefixes', []);

        foreach ($allowedPrefixes as $prefix) {
            $normalizedPrefix = trim((string) $prefix);

            if ($normalizedPrefix !== '' && str_starts_with($slug, $normalizedPrefix)) {
                return true;
            }
        }

        return $this->isTheoryLinkedSavedTest($slug);
    }

    protected function isTheoryLinkedSavedTest(string $slug): bool
    {
        $test = SavedGrammarTest::query()
            ->where('slug', $slug)
            ->first(['filters']);

        if (! $test) {
            return false;
        }

        $filters = is_array($test->filters) ? $test->filters : [];
        $courseSlug = strtolower(trim((string) data_get($filters, 'course_slug', '')));

        if ($courseSlug === 'polyglot-theory-pages') {
            return true;
        }

        return filled(data_get($filters, 'prompt_generator.theory_page_id'))
            || filled(data_get($filters, 'prompt_generator.theory_page.id'))
            || filled(data_get($filters, 'prompt_generator.theory_page.slug'))
            || filled(data_get($filters, 'prompt_generator.theory_page.page_seeder_class'))
            || filled(data_get($filters, 'prompt_generator.theory_page_ids'));
    }

    protected function isTheoryTestRequest(Request $request, string $slug): bool
    {
        if ($request->query('source') === 'theory') {
            return true;
        }

        $refererPath = parse_url((string) $request->headers->get('referer'), PHP_URL_PATH);

        return is_string($refererPath)
            && str_starts_with('/'.ltrim($refererPath, '/'), '/theory/')
            && $slug !== '';
    }

    protected function rememberTheoryTestSlug(Request $request, string $slug): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $allowedSlugs = $request->session()->get('coming_soon.allowed_theory_test_slugs', []);
        $allowedSlugs = is_array($allowedSlugs) ? $allowedSlugs : [];
        $allowedSlugs[$slug] = true;

        $request->session()->put('coming_soon.allowed_theory_test_slugs', $allowedSlugs);
    }

    protected function isRememberedTheoryTestSlug(Request $request, string $slug): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $allowedSlugs = $request->session()->get('coming_soon.allowed_theory_test_slugs', []);

        return is_array($allowedSlugs) && isset($allowedSlugs[$slug]);
    }
}
