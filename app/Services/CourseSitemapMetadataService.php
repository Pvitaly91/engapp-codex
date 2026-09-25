<?php

namespace App\Services;

use App\Support\SentenceBuilderBranding;
use JsonException;
use RuntimeException;

/** Read-only inclusion metadata; never renders pages or registers virtual tests. */
class CourseSitemapMetadataService
{
    public function __construct(
        private CourseCatalogService $catalog,
        private PolyglotCourseManifestService $manifests,
        private PolyglotCourseBlueprintService $blueprints,
        private TheoryCourseManifestService $theory,
    ) {}

    public function catalogueEligible(): bool
    {
        return in_array('uk', config('site-mode.production_locales', ['uk']), true)
            && ! $this->blockedByComingSoon('/courses', 'courses.index');
    }

    /** @return list<string> Canonical unprefixed course homes; /courses is added by the caller. */
    public function eligiblePaths(): array
    {
        // M4 deliberately indexes only the existing Ukrainian public course catalogue.
        if (! in_array('uk', config('site-mode.production_locales', ['uk']), true)) {
            return [];
        }
        $slugs = $this->catalog->canonicalCourseSlugs();
        $entries = $this->manifests->sitemapEntryMetadata(array_values(array_diff($slugs, [TheoryCourseManifestService::COURSE_SLUG])));
        $paths = [];
        foreach ($slugs as $slug) {
            $path = '/courses/'.rawurlencode($slug);
            $route = $slug === TheoryCourseManifestService::COURSE_SLUG ? 'courses.theory.show' : 'courses.show';
            if ($this->blockedByComingSoon($path, $route)) {
                continue;
            }
            if ($slug === TheoryCourseManifestService::COURSE_SLUG) {
                $entryPath = $this->theory->sitemapEntryPath('uk');
                if ($entryPath !== null && ! $this->blockedByComingSoon($entryPath, 'courses.theory.lesson')) {
                    $paths[] = $path;
                }
                continue;
            }
            $legacy = SentenceBuilderBranding::legacyCourseSlug($slug);
            $entry = $entries[$legacy] ?? null;
            if (! is_array($entry) || ! $entry['usable'] || ! $this->entryAllowed($entry['public_slug'])) {
                continue;
            }
            try {
                $blueprint = $this->blueprints->getCourseBlueprint($legacy);
            } catch (RuntimeException|JsonException) {
                // A missing/invalid blueprint makes the actual controller fail too.
                continue;
            }
            if (($blueprint['course_slug'] ?? null) === $legacy
                && in_array($entry['slug'], array_column($blueprint['lessons'] ?? [], 'slug'), true)) {
                $paths[] = $path;
            }
        }
        sort($paths, SORT_STRING);

        return array_values(array_unique($paths));
    }

    private function entryAllowed(string $slug): bool
    {
        if ($slug === '' || strpbrk($slug, '/?#\\') !== false) {
            return false;
        }
        $path = '/test/'.rawurlencode($slug).'/step/compose';
        if (! $this->blockedByComingSoon($path, 'test.step-compose')) {
            return true;
        }
        // A conservative subset of the existing cold-guest gate. Do not consult or
        // populate session/registry, and do not normalize away the current branding gate.
        foreach (config('coming-soon.allowed_test_slug_prefixes', []) as $prefix) {
            $prefix = trim((string) $prefix);
            if ($prefix !== '' && str_starts_with($slug, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function blockedByComingSoon(string $path, string $route): bool
    {
        if (! config('coming-soon.enabled', false)) {
            return false;
        }
        if (in_array($route, config('coming-soon.routes', []), true)) {
            return true;
        }
        foreach (config('coming-soon.prefixes', []) as $prefix) {
            if (str_starts_with($path, '/'.ltrim((string) $prefix, '/'))) {
                return true;
            }
        }

        return false;
    }
}
