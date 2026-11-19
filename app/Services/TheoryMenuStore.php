<?php

namespace App\Services;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\SiteMode;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Immutable release pairs with an append-only activation log, separate from Laravel cache. */
final class TheoryMenuStore
{
    public const VARIANTS = ['desktop', 'mobile'];

    private const SCHEMA = 1;

    private const MAX_HTML_BYTES = 16 * 1024 * 1024;

    private array $reported = [];

    /** Optional root/locale injection is for isolated filesystem tests. */
    public function __construct(private ?string $root = null, private ?array $locales = null) {}

    /** Returns healthy pair metadata only, without reading or hashing either HTML file. */
    public function active(string $mode, string $locale, string $family = 'theory'): ?array
    {
        $context = $this->context($mode, $locale, $family);
        if ($context === null) return null;
        foreach ($this->candidates($context, $mode, $locale, $family, null) as [$manifest, , $fallback]) {
            return $manifest + ['fallback' => $fallback];
        }

        return null;
    }

    /** A versioned request may fall back to another copy of the same bytes, never another revision. */
    public function read(string $mode, string $locale, string $variant, ?string $revision = null, string $family = 'theory'): ?array
    {
        $context = $this->context($mode, $locale, $family);
        if ($context === null || !in_array($variant, self::VARIANTS, true)
            || ($revision !== null && !$this->isDigest($revision))) return null;
        $unreadable = false;
        foreach ($this->candidates($context, $mode, $locale, $family, $revision) as [$manifest, $dir, $fallback]) {
            $file = $manifest['files'][$variant];
            $html = @file_get_contents($dir.'/'.$file['name'], false, null, 0, $file['bytes'] + 1);
            if (!is_string($html) || strlen($html) !== $file['bytes'] || trim($html) === ''
                || ($revision !== null && !hash_equals($file['sha256'], hash('sha256', $html)))) {
                $unreadable = true;
                continue;
            }
            if ($unreadable) $this->warn($mode, $locale, $family, 'unreadable-fragment', $revision);

            return ['html' => $html, 'revision' => $manifest['revision'], 'sha256' => $file['sha256'],
                'bytes' => $file['bytes'], 'fallback' => $fallback || $unreadable];
        }
        if ($unreadable) $this->warn($mode, $locale, $family, 'unreadable-fragment', $revision);

        return null;
    }

    /** The callback runs inside the same interprocess lock as staging and activation. */
    public function build(string $mode, string $locale, callable $generate, string $family = 'theory'): array
    {
        return $this->locked($mode, $locale, $family, function (string $context) use ($mode, $locale, $family, $generate): array {
            $data = $generate();
            $fragments = $data['fragments'] ?? null;
            $counts = $data['counts'] ?? null;
            if (!is_array($fragments) || array_keys($fragments) !== self::VARIANTS || !$this->validCounts($counts)) {
                throw new RuntimeException('Incomplete theory menu snapshot.');
            }
            $files = [];
            foreach ($fragments as $variant => $html) {
                if (!is_string($html) || trim($html) === '' || strlen($html) > self::MAX_HTML_BYTES) {
                    throw new RuntimeException('Invalid theory menu HTML size.');
                }
                $files[$variant] = ['name' => $variant.'.html', 'bytes' => strlen($html), 'sha256' => hash('sha256', $html)];
            }
            $manifest = ['schema_version' => self::SCHEMA, 'mode' => $mode, 'locale' => $locale, 'family' => $family,
                'counts' => $counts, 'files' => $files];
            $manifest['revision'] = $this->revision($manifest);
            $activations = $this->activationPaths($context);
            $latest = $this->loadActivation($context, $activations[0] ?? '', $mode, $locale, $family, true);
            if ($latest !== null && $latest[0]['revision'] === $manifest['revision']) {
                return $manifest + ['status' => 'unchanged'];
            }

            // A damaged release can be rebuilt with the same content revision in a new immutable directory.
            $sequence = $this->nextSequence($activations);
            $release = $manifest['revision'].'-'.bin2hex(random_bytes(8));
            $releases = $context.'/releases';
            $staging = $releases.'/.staging-'.bin2hex(random_bytes(12));
            if (!@mkdir($staging, 0775)) throw new RuntimeException('Cannot create theory menu staging directory.');
            $releasesRoot = realpath($releases);
            if ($releasesRoot === false || realpath($staging) !== $releasesRoot.DIRECTORY_SEPARATOR.basename($staging)) {
                throw new RuntimeException('Theory menu staging directory escaped its release root.');
            }
            try {
                foreach ($fragments as $variant => $html) $this->writeNew($staging.'/'.$files[$variant]['name'], $html);
                $this->writeNew($staging.'/manifest.json', $this->json($manifest));
                if ($this->loadRelease($staging, $mode, $locale, $family, $manifest['revision'], true) !== $manifest) {
                    throw new RuntimeException('Theory menu staging validation failed.');
                }
                $destination = $releases.'/'.$release;
                if (realpath(dirname($destination)) !== $releasesRoot || file_exists($destination) || is_link($destination)
                    || !@rename($staging, $destination)) {
                    throw new RuntimeException('Cannot install immutable theory menu release.');
                }
                $this->activate($context, $sequence, $release, $manifest['revision']);
            } finally {
                // Only files created by this build, in its own staging directory. Never touch active releases.
                if (!is_link($staging) && realpath($staging) === $releasesRoot.DIRECTORY_SEPARATOR.basename($staging)) {
                    foreach (['desktop.html', 'mobile.html', 'manifest.json'] as $file) @unlink($staging.'/'.$file);
                    @rmdir($staging);
                }
            }

            return $manifest + ['status' => 'published'];
        });
    }

    /** Full checksum verification of the active publication, without falling back. */
    public function check(string $mode, string $locale, string $family = 'theory'): ?array
    {
        $context = $this->context($mode, $locale, $family);
        if ($context === null) return null;
        $paths = $this->activationPaths($context);

        return $this->loadActivation($context, $paths[0] ?? '', $mode, $locale, $family, true)[0] ?? null;
    }

    public function rollback(string $mode, string $locale, string $family = 'theory'): array
    {
        return $this->locked($mode, $locale, $family, function (string $context) use ($mode, $locale, $family): array {
            $paths = $this->activationPaths($context);
            $currentRevision = $paths !== [] ? substr(basename($paths[0]), 17, 64) : null;
            foreach (array_slice($paths, 1) as $path) {
                $previous = $this->loadActivation($context, $path, $mode, $locale, $family, true);
                if ($previous === null || $previous[0]['revision'] === $currentRevision) continue;
                [$manifest, $dir] = $previous;
                $this->activate($context, $this->nextSequence($paths), basename($dir), $manifest['revision']);

                return $manifest + ['status' => 'published'];
            }

            throw new RuntimeException('No previous healthy theory menu revision for this context.');
        });
    }

    private function context(string $mode, string $locale, string $family): ?string
    {
        if (!in_array($mode, [SiteMode::DEVELOPMENT, SiteMode::PRODUCTION], true) || $family !== 'theory'
            || preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/D', $locale) !== 1
            || !in_array($locale, $this->locales ?? LocaleService::getConfiguredSupportedLocales(), true)) return null;
        $root = rtrim($this->root ?? config('theory-menu.path') ?? storage_path('app/theory-menu'), '/\\');
        $dir = $root;
        if (is_link($dir)) return null;
        foreach ([$mode, $locale, $family] as $part) {
            $dir .= '/'.$part;
            if (is_link($dir)) return null;
        }

        return $dir;
    }

    private function candidates(string $context, string $mode, string $locale, string $family, ?string $revision): iterable
    {
        $bad = false;
        foreach ($this->activationPaths($context) as $path) {
            if ($revision !== null && substr(basename($path), 17, 64) !== $revision) continue;
            $release = $this->loadActivation($context, $path, $mode, $locale, $family, false);
            if ($release === null) {
                $bad = true;
                continue;
            }
            if ($bad) $this->warn($mode, $locale, $family, 'using-previous-snapshot', $revision);
            yield [$release[0], $release[1], $bad];
            // The caller may resume iteration only when this fragment failed to read.
        }
        $this->warn($mode, $locale, $family, $bad ? 'invalid-snapshot' : 'snapshot-missing', $revision);
    }

    private function activationPaths(string $context): array
    {
        if (is_link($context.'/activations') || !is_dir($context.'/activations')) return [];
        $paths = array_values(array_filter(glob($context.'/activations/*.json') ?: [],
            static fn (string $path): bool => preg_match('/^[0-9]{16}-[a-f0-9]{64}\.json$/D', basename($path)) === 1));
        rsort($paths, SORT_STRING);

        return $paths;
    }

    private function loadActivation(string $context, string $path, string $mode, string $locale, string $family, bool $hash): ?array
    {
        if ($path === '' || is_link($context.'/releases')) return null;
        $entry = $this->readJson($path);
        $revision = substr(basename($path), 17, 64);
        if (($entry['schema_version'] ?? null) !== self::SCHEMA || ($entry['revision'] ?? null) !== $revision
            || !is_string($entry['release'] ?? null)
            || preg_match('/^'.preg_quote($revision, '/').'-[a-f0-9]{16}$/D', $entry['release']) !== 1) return null;
        $dir = $context.'/releases/'.$entry['release'];
        $manifest = $this->loadRelease($dir, $mode, $locale, $family, $revision, $hash);

        return $manifest === null ? null : [$manifest, $dir];
    }

    private function loadRelease(string $dir, string $mode, string $locale, string $family, string $revision, bool $hash): ?array
    {
        if (!is_dir($dir) || is_link($dir)) return null;
        $manifest = $this->readJson($dir.'/manifest.json');
        if (($manifest['schema_version'] ?? null) !== self::SCHEMA || ($manifest['mode'] ?? null) !== $mode
            || ($manifest['locale'] ?? null) !== $locale || ($manifest['family'] ?? null) !== $family
            || ($manifest['revision'] ?? null) !== $revision || !$this->validCounts($manifest['counts'] ?? null)
            || !is_array($manifest['files'] ?? null) || array_keys($manifest['files']) !== self::VARIANTS) return null;
        foreach (self::VARIANTS as $variant) {
            $entry = $manifest['files'][$variant];
            $path = $dir.'/'.$variant.'.html';
            if (!is_array($entry) || ($entry['name'] ?? null) !== $variant.'.html' || !$this->isDigest($entry['sha256'] ?? null)
                || !is_int($entry['bytes'] ?? null) || $entry['bytes'] <= 0 || $entry['bytes'] > self::MAX_HTML_BYTES
                || !is_file($path) || is_link($path)) return null;
            clearstatcache(true, $path);
            if (@filesize($path) !== $entry['bytes'] || ($hash && @hash_file('sha256', $path) !== $entry['sha256'])) return null;
            // PHP's Windows ACL probe can report false even when opening a file succeeds.
            if (!$hash) {
                $probe = @fopen($path, 'rb');
                if ($probe === false) return null;
                fclose($probe);
            }
        }

        return $this->revision($manifest) === $revision ? $manifest : null;
    }

    private function revision(array $manifest): string
    {
        return hash('sha256', $this->json([$manifest['schema_version'], $manifest['mode'], $manifest['locale'],
            $manifest['family'], $manifest['counts'], $manifest['files']]));
    }

    private function validCounts(mixed $counts): bool
    {
        return is_array($counts) && array_keys($counts) === ['categories', 'lessons']
            && is_int($counts['categories']) && $counts['categories'] > 0
            && is_int($counts['lessons']) && $counts['lessons'] > 0;
    }

    private function isDigest(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) === 1;
    }

    private function readJson(string $path): ?array
    {
        if (!is_file($path) || is_link($path)) return null;
        $data = @file_get_contents($path, false, null, 0, 8193);
        if (!is_string($data) || strlen($data) > 8192) return null;
        $decoded = json_decode($data, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    }

    private function locked(string $mode, string $locale, string $family, callable $operation): array
    {
        $context = $this->context($mode, $locale, $family);
        if ($context === null) throw new RuntimeException('Unsupported theory menu context.');
        foreach ([$context, $context.'/releases', $context.'/activations'] as $dir) {
            if (is_link($dir) || (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir))) {
                throw new RuntimeException('Cannot create theory menu directory.');
            }
        }
        $lockPath = $context.'/build.lock';
        $lock = is_link($lockPath) ? false : @fopen($lockPath, 'cb');
        if ($lock === false) throw new RuntimeException('Cannot open theory menu build lock.');
        try {
            if (!flock($lock, LOCK_EX)) throw new RuntimeException('Cannot acquire theory menu build lock.');

            return $operation($context);
        } finally {
            fclose($lock);
        }
    }

    private function nextSequence(array $paths): string
    {
        $next = ($paths === [] ? 0 : (int) substr(basename($paths[0]), 0, 16)) + 1;
        if ($next > 9999999999999999) throw new RuntimeException('Theory menu activation sequence exhausted.');

        return sprintf('%016d', $next);
    }

    private function activate(string $context, string $sequence, string $release, string $revision): void
    {
        $dir = $context.'/activations';
        $pending = $dir.'/.pending-'.bin2hex(random_bytes(12));
        $destination = $dir.'/'.$sequence.'-'.$revision.'.json';
        try {
            $this->writeNew($pending, $this->json(['schema_version' => self::SCHEMA, 'revision' => $revision, 'release' => $release]));
            // Atomic visibility is a same-directory rename to a new filename, not a Windows replace-existing operation.
            if (file_exists($destination) || is_link($destination) || !@rename($pending, $destination)) {
                throw new RuntimeException('Cannot activate theory menu release.');
            }
        } finally {
            if (is_file($pending)) @unlink($pending);
        }
    }

    private function writeNew(string $path, string $data): void
    {
        $file = @fopen($path, 'xb');
        if ($file === false) throw new RuntimeException('Cannot create theory menu artifact.');
        try {
            if (fwrite($file, $data) !== strlen($data) || !fflush($file) || (function_exists('fsync') && !fsync($file))) {
                throw new RuntimeException('Cannot write complete theory menu artifact.');
            }
        } finally {
            fclose($file);
        }
    }

    private function warn(string $mode, string $locale, string $family, string $reason, ?string $revision): void
    {
        $key = implode(':', [$mode, $locale, $family, $reason, $revision]);
        if (isset($this->reported[$key])) return;
        $this->reported[$key] = true;
        try {
            Log::warning('Theory menu snapshot unavailable or degraded.', compact('mode', 'locale', 'family', 'reason', 'revision'));
        } catch (Throwable) {
            // A missing logging backend must not take down an otherwise usable lesson.
        }
    }
}
