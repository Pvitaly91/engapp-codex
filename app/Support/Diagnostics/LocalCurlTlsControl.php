<?php

namespace App\Support\Diagnostics;

/**
 * Private, short-lived control data for the M9.4 web-SAPI cURL probe.
 *
 * The file is intentionally outside public/ and ignored by Git. It is created
 * by the local fixture runner, never by an HTTP request. Keeping validation in
 * one place prevents a diagnostic route from becoming a generic URL fetcher.
 */
final class LocalCurlTlsControl
{
    public const SCHEMA = 'gramlyze-m9-4-curl-tls-control-v1';

    private const DIRECTORY = 'app/seo-m9-4-local';

    private const FILENAME = 'curl-tls-control.json';

    public static function directory(): string
    {
        return storage_path(self::DIRECTORY);
    }

    public static function path(): string
    {
        return self::directory().DIRECTORY_SEPARATOR.self::FILENAME;
    }

    /**
     * @return array{schema:string,nonce:string,fixture?:array<string, mixed>}|null
     */
    public static function load(): ?array
    {
        $directory = self::directory();
        $path = self::path();

        if (! is_dir($directory) || is_link($directory) || ! is_file($path) || is_link($path)) {
            return null;
        }

        $realDirectory = realpath($directory);
        $realPath = realpath($path);

        if (! is_string($realDirectory) || ! is_string($realPath) || ! self::isInside($realPath, $realDirectory)) {
            return null;
        }

        try {
            $value = json_decode((string) file_get_contents($realPath), true, 16, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($value)
            || ($value['schema'] ?? null) !== self::SCHEMA
            || ! is_string($value['nonce'] ?? null)
            || ! preg_match('/\A[a-f0-9]{64}\z/D', $value['nonce'])) {
            return null;
        }

        return $value;
    }

    public static function nonce(): ?string
    {
        return self::load()['nonce'] ?? null;
    }

    /**
     * @return array{positive_url:string,negative_url:string,ca_path:string,port:int}|null
     */
    public static function fixture(): ?array
    {
        $control = self::load();
        $fixture = $control['fixture'] ?? null;

        if (! is_array($fixture)
            || ! is_string($fixture['positive_url'] ?? null)
            || ! is_string($fixture['negative_url'] ?? null)
            || ! is_string($fixture['ca_path'] ?? null)) {
            return null;
        }

        $positive = self::target($fixture['positive_url'], 'localhost');
        $negative = self::target($fixture['negative_url'], '127.0.0.1');
        $caPath = self::privateFile($fixture['ca_path']);

        if ($positive === null || $negative === null || $caPath === null || $positive['port'] !== $negative['port']) {
            return null;
        }

        return [
            'positive_url' => $positive['url'],
            'negative_url' => $negative['url'],
            'ca_path' => $caPath,
            'port' => $positive['port'],
        ];
    }

    /** @return array{url:string,port:int}|null */
    private static function target(string $url, string $host): ?array
    {
        $parts = parse_url($url);

        if (! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || strtolower((string) ($parts['host'] ?? '')) !== $host
            || ! is_int($parts['port'] ?? null)
            || ($parts['port'] ?? 0) < 1024
            || ($parts['port'] ?? 0) > 65535
            || ($parts['path'] ?? null) !== '/ok'
            || array_key_exists('query', $parts)
            || array_key_exists('fragment', $parts)
            || array_key_exists('user', $parts)
            || array_key_exists('pass', $parts)) {
            return null;
        }

        return ['url' => $url, 'port' => $parts['port']];
    }

    private static function privateFile(string $path): ?string
    {
        $directory = self::directory();

        if (is_link($path) || ! is_file($path)) {
            return null;
        }

        $realDirectory = realpath($directory);
        $realPath = realpath($path);

        if (! is_string($realDirectory) || ! is_string($realPath) || ! self::isInside($realPath, $realDirectory)) {
            return null;
        }

        return $realPath;
    }

    private static function isInside(string $path, string $directory): bool
    {
        $normalize = static function (string $value): string {
            $value = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            return DIRECTORY_SEPARATOR === '\\' ? strtolower($value) : $value;
        };

        return str_starts_with($normalize($path), $normalize($directory));
    }
}
