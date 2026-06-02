<?php

namespace App\Services\V3PromptGenerator;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ExternalTheoryUrlService
{
    public function normalizeAndValidatePublicUrl(string $url): string
    {
        $normalized = trim($url);

        if ($normalized === '') {
            throw new RuntimeException('Вкажіть коректний external URL.');
        }

        try {
            $uri = new Uri($normalized);
        } catch (Throwable) {
            throw new RuntimeException('Вкажіть коректний external URL.');
        }

        $scheme = strtolower($uri->getScheme());
        $host = strtolower($uri->getHost());

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('Дозволені тільки http:// та https:// URL.');
        }

        if ($host === '') {
            throw new RuntimeException('External URL повинен містити валідний host.');
        }

        if ($uri->getUserInfo() !== '') {
            throw new RuntimeException('External URL з логіном або паролем не підтримується.');
        }

        $uri = $uri
            ->withScheme($scheme)
            ->withHost($host)
            ->withFragment('');

        if ($uri->getPath() === '') {
            $uri = $uri->withPath('/');
        }

        $this->assertHostIsPublic($uri->getHost());

        return (string) $uri;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetch(string $validatedUrl): array
    {
        $currentUrl = $validatedUrl;

        try {
            for ($redirects = 0; $redirects < 4; $redirects++) {
                $response = Http::timeout(8)
                    ->connectTimeout(4)
                    ->accept('text/html,application/xhtml+xml')
                    ->withUserAgent('EngApp V3 Prompt Generator/1.0')
                    ->withOptions([
                        'allow_redirects' => false,
                        'http_errors' => false,
                    ])
                    ->get($currentUrl);

                if ($response->status() >= 300 && $response->status() < 400 && filled($response->header('Location'))) {
                    $redirectTarget = $this->resolveRedirectUrl($currentUrl, (string) $response->header('Location'));
                    $currentUrl = $this->normalizeAndValidatePublicUrl($redirectTarget);

                    continue;
                }

                if (! $response->successful()) {
                    return [
                        'fetched' => false,
                        'url' => $currentUrl,
                        'title' => null,
                        'snippet' => null,
                        'error' => 'Не вдалося підтягнути external URL: HTTP ' . $response->status() . '. Prompt буде згенеровано тільки на основі URL.',
                    ];
                }

                $parsed = $this->extractPageContext($response->body());

                return [
                    'fetched' => true,
                    'url' => $currentUrl,
                    'title' => $parsed['title'],
                    'snippet' => $parsed['snippet'],
                    'error' => null,
                ];
            }

            return [
                'fetched' => false,
                'url' => $currentUrl,
                'title' => null,
                'snippet' => null,
                'error' => 'External URL повертає забагато redirect-ів. Prompt буде згенеровано тільки на основі URL.',
            ];
        } catch (ConnectionException) {
            return [
                'fetched' => false,
                'url' => $currentUrl,
                'title' => null,
                'snippet' => null,
                'error' => 'Не вдалося з\'єднатися із зовнішньою сторінкою. Prompt буде згенеровано тільки на основі URL.',
            ];
        } catch (Throwable) {
            return [
                'fetched' => false,
                'url' => $currentUrl,
                'title' => null,
                'snippet' => null,
                'error' => 'Не вдалося безпечно завантажити зовнішню сторінку. Prompt буде згенеровано тільки на основі URL.',
            ];
        }
    }

    protected function assertHostIsPublic(string $host): void
    {
        $normalizedHost = strtolower(trim($host));

        if ($normalizedHost === '') {
            throw new RuntimeException('External URL повинен містити валідний host.');
        }

        foreach (['localhost', '.localhost', '.local', '.internal', '.test', '.example', '.invalid', '.home.arpa', '.lan'] as $blockedSuffix) {
            if ($normalizedHost === ltrim($blockedSuffix, '.') || str_ends_with($normalizedHost, $blockedSuffix)) {
                throw new RuntimeException('Локальні, loopback або internal host-и заборонені для external URL.');
            }
        }

        if (filter_var($normalizedHost, FILTER_VALIDATE_IP)) {
            if (! $this->isPublicIp($normalizedHost)) {
                throw new RuntimeException('Localhost, private IP або loopback адреси не можна використовувати як external URL.');
            }

            return;
        }

        if (! str_contains($normalizedHost, '.')) {
            throw new RuntimeException('Internal host-и без публічного домену не дозволені.');
        }

        $asciiHost = $this->toAsciiHost($normalizedHost);
        $records = @dns_get_record($asciiHost, DNS_A + DNS_AAAA);

        if (! is_array($records) || $records === []) {
            throw new RuntimeException('Не вдалося безпечно перевірити host зовнішнього URL.');
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;

            if (! $ip) {
                continue;
            }

            if (! $this->isPublicIp((string) $ip)) {
                throw new RuntimeException('External URL веде на private або reserved IP і не може бути використаний.');
            }
        }
    }

    protected function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    protected function toAsciiHost(string $host): string
    {
        if (! function_exists('idn_to_ascii')) {
            return $host;
        }

        $flags = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
        $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
        $converted = idn_to_ascii($host, $flags, $variant);

        return $converted ?: $host;
    }

    protected function resolveRedirectUrl(string $baseUrl, string $redirectTarget): string
    {
        $resolved = UriResolver::resolve(new Uri($baseUrl), new Uri($redirectTarget));

        return (string) $resolved;
    }

    /**
     * @return array{title: ?string, snippet: ?string}
     */
    protected function extractPageContext(string $html): array
    {
        $fallbackText = $this->normalizeText(strip_tags($html));

        if (! class_exists(DOMDocument::class)) {
            return [
                'title' => null,
                'snippet' => $fallbackText !== '' ? mb_strimwidth($fallbackText, 0, 1800, '...') : null,
            ];
        }

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
            $xpath = new DOMXPath($dom);

            $title = $this->normalizeText((string) $xpath->evaluate('string(//title[1])'));
            $scopeNode = $xpath->query('//main | //article | //body')->item(0) ?: $dom->documentElement;

            if ($scopeNode) {
                foreach ($xpath->query('.//script | .//style | .//noscript | .//svg | .//header | .//footer | .//nav', $scopeNode) ?: [] as $node) {
                    $node->parentNode?->removeChild($node);
                }
            }

            $text = $scopeNode ? $this->normalizeText($scopeNode->textContent ?? '') : $fallbackText;

            return [
                'title' => $title !== '' ? $title : null,
                'snippet' => $text !== '' ? mb_strimwidth($text, 0, 1800, '...') : null,
            ];
        } catch (Throwable) {
            return [
                'title' => null,
                'snippet' => $fallbackText !== '' ? mb_strimwidth($fallbackText, 0, 1800, '...') : null,
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    protected function normalizeText(string $text): string
    {
        $normalized = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
