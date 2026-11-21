<?php

namespace App\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use WeakReference;

/** Request-local reuse of validated, pinned source trees, never rendered blocks. */
final class ValidatedNativeContentSources
{
    private const STANDALONE_ROUTES = ['test.show' => '', 'test.step' => '/step', 'test.step-compose' => '/step/compose'];

    /**
     * @param Closure(string): array $loadAndValidate The unchanged cold path.
     * @param Closure(string): void $verifyAll Re-read and check every canonical artifact.
     */
    public static function read(array $profile, string $caller, Closure $loadAndValidate, Closure $verifyAll): array
    {
        $app = app();
        $root = $app->basePath();
        $request = $app->bound('request') ? $app->make('request') : null;
        $route = $request instanceof Request ? $request->route() : null;
        $label = $profile['label'] ?? null;
        if (!$route instanceof Route || !$route->hasParameters() || !is_string($label)
            || preg_match('/^M(?:5[3-9]|[678][0-9]|9[0-3])$/D', $label) !== 1
            || !in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $loadAndValidate($root);
        }

        $name = $route->getName();
        $locale = $app->getLocale();
        $theory = $caller === 'canonical_theory';
        $slug = $route->parameter($theory ? 'pageSlug' : 'slug');
        if (!is_string($slug) || preg_match('~^[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $slug) !== 1) {
            return $loadAndValidate($root);
        }

        $key = self::key($caller, $label, $name, $slug, $locale);
        if ($key === null) { return $loadAndValidate($root); }

        $category = $route->parameter('categoryPath');
        if ($theory && (!is_string($category) || preg_match('~^[a-z0-9-]+(?:/[a-z0-9-]+)*$~D', $category) !== 1)) {
            return $loadAndValidate($root);
        }
        $path = $theory ? '/theory/'.$category.'/'.$slug : '/test/'.$slug.self::STANDALONE_ROUTES[$name];
        if ($request->getPathInfo() !== $path && $request->getPathInfo() !== '/'.$locale.$path) {
            return $loadAndValidate($root);
        }
        $context = [$caller, $locale, $request->method(), $request->getHost(), $request->getPathInfo(),
            $name, $route->getActionName(), $slug, $category];
        $cached = $request->attributes->get($key);
        if (is_array($cached) && ($cached['request'] ?? null) instanceof WeakReference
            && $cached['request']->get() === $request && ($cached['app'] ?? null) instanceof WeakReference
            && $cached['app']->get() === $app && ($cached['route'] ?? null) instanceof WeakReference
            && $cached['route']->get() === $route && ($cached['root'] ?? null) === $root
            && ($cached['context'] ?? null) === $context && ($cached['profile'] ?? null) === $profile
            && is_array($cached['sources'] ?? null)) {
            try {
                $verifyAll($root);
            } catch (\Throwable $error) {
                $request->attributes->remove($key);
                throw $error;
            }
            return $cached['sources'];
        }

        $sources = $loadAndValidate($root);
        $request->attributes->set($key, [
            'request' => WeakReference::create($request), 'app' => WeakReference::create($app),
            'route' => WeakReference::create($route), 'context' => $context, 'root' => $root,
            'profile' => $profile, 'sources' => $sources,
        ]);
        return $sources;
    }

    private static function key(string $caller, string $label, ?string $name, string $slug, string $locale): ?string
    {
        if ($caller === 'canonical_theory') {
            return $name === 'theory.show' && $locale === 'uk' ? self::class.':'.$caller.':'.$label : null;
        }
        if ($caller !== 'standalone_test' || !isset(self::STANDALONE_ROUTES[$name])
            || !in_array($locale, ['uk', 'en', 'pl'], true)) {
            return null;
        }

        // Retain the two previously approved scopes; new packages use the shared policy.
        return match ($label) {
            'M84' => in_array($name, ['test.show', 'test.step'], true) && $slug === 'word-order/theory-basic-word-order'
                && $locale === 'uk' ? TheoryNativeContentRelease::class.'.standalone-word-order-sources' : null,
            'M90' => $name === 'test.show' && $slug === 'past-perfect/forms'
                ? TheoryNativeContentRelease::class.'.standalone-past-perfect-sources' : null,
            default => self::class.':'.$caller.':'.$label,
        };
    }
}
