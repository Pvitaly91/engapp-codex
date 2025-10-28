<?php

namespace App\Services\Deployment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GitHubApiClient
{
    private Client $client;

    public function __construct()
    {
        $owner = config('deployment.github.owner');
        $repo = config('deployment.github.repo');
        $token = config('deployment.github.token');

        if (! $owner || ! $repo) {
            throw new RuntimeException('GitHub repository configuration is missing.');
        }

        $headers = [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => config('deployment.github.user_agent'),
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => $headers,
        ]);
    }

    /**
     * @throws RuntimeException
     */
    public function getRepository(): array
    {
        return $this->request('GET', $this->repoUri());
    }

    /**
     * @throws RuntimeException
     */
    public function getBranch(string $branch): array
    {
        return $this->request('GET', $this->repoUri("git/ref/heads/{$branch}"));
    }

    /**
     * @throws RuntimeException
     */
    public function getCommit(string $sha): array
    {
        return $this->request('GET', $this->repoUri("git/commits/{$sha}"));
    }

    /**
     * @throws RuntimeException
     */
    public function getTree(string $sha): array
    {
        return $this->request('GET', $this->repoUri("git/trees/{$sha}"), [
            'query' => ['recursive' => 1],
        ]);
    }

    /**
     * @throws RuntimeException
     */
    public function downloadArchive(string $ref, string $format = 'zip'): string
    {
        $format = $format === 'tar' ? 'tar' : 'zip';
        $prefix = $format === 'tar' ? 'gh_tar_' : 'gh_zip_';
        $tempFile = tempnam(sys_get_temp_dir(), $prefix);

        if ($tempFile === false) {
            throw new RuntimeException('Не вдалося створити тимчасовий файл для архіву GitHub.');
        }

        $extension = $format === 'tar' ? '.tar.gz' : '.zip';
        $archivePath = $tempFile . $extension;

        if (! @rename($tempFile, $archivePath)) {
            $archivePath = $tempFile;
        }

        $endpoint = $format === 'tar' ? "tarball/{$ref}" : "zipball/{$ref}";

        try {
            $response = $this->client->request('GET', $this->repoUri($endpoint), [
                'sink' => $archivePath,
                'headers' => ['Accept' => 'application/vnd.github+json'],
            ]);
        } catch (GuzzleException $exception) {
            @unlink($archivePath);
            throw new RuntimeException('Не вдалося отримати архів із GitHub: ' . $exception->getMessage(), 0, $exception);
        }

        if ($response->getStatusCode() >= 300) {
            @unlink($archivePath);
            throw new RuntimeException('GitHub повернув помилку під час завантаження архіву.');
        }

        return $archivePath;
    }

    /**
     * @throws RuntimeException
     */
    public function createBlob(string $content, bool $binary = false): string
    {
        $payload = [
            'content' => $binary ? base64_encode($content) : $content,
        ];

        if ($binary) {
            $payload['encoding'] = 'base64';
        }

        $response = $this->request('POST', $this->repoUri('git/blobs'), [
            'json' => $payload,
        ]);

        return $response['sha'] ?? throw new RuntimeException('GitHub не повернув SHA блоба.');
    }

    /**
     * @throws RuntimeException
     */
    public function createTree(string $baseTreeSha, array $entries): string
    {
        $body = ['tree' => $entries];

        if ($baseTreeSha !== '') {
            $body['base_tree'] = $baseTreeSha;
        }

        $response = $this->request('POST', $this->repoUri('git/trees'), [
            'json' => $body,
        ]);

        return $response['sha'] ?? throw new RuntimeException('GitHub не повернув SHA дерева.');
    }

    /**
     * @throws RuntimeException
     */
    public function createCommit(string $message, string $treeSha, array $parents): string
    {
        $response = $this->request('POST', $this->repoUri('git/commits'), [
            'json' => [
                'message' => $message,
                'tree' => $treeSha,
                'parents' => $parents,
            ],
        ]);

        return $response['sha'] ?? throw new RuntimeException('GitHub не повернув SHA коміту.');
    }

    /**
     * @throws RuntimeException
     */
    public function updateRef(string $ref, string $sha, bool $force = false): void
    {
        $this->request('PATCH', $this->repoUri("git/refs/{$ref}"), [
            'json' => [
                'sha' => $sha,
                'force' => $force,
            ],
        ]);
    }

    /**
     * @throws RuntimeException
     */
    public function createRef(string $ref, string $sha): void
    {
        $this->request('POST', $this->repoUri('git/refs'), [
            'json' => [
                'ref' => 'refs/' . ltrim($ref, 'refs/'),
                'sha' => $sha,
            ],
        ]);
    }

    private function repoUri(string $path = ''): string
    {
        $owner = config('deployment.github.owner');
        $repo = config('deployment.github.repo');

        $base = sprintf('repos/%s/%s', $owner, $repo);

        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }

    /**
     * @throws RuntimeException
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $exception) {
            Log::warning('GitHub API call failed', [
                'method' => $method,
                'uri' => $uri,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Помилка звернення до GitHub API: ' . $exception->getMessage(), 0, $exception);
        }

        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if ($decoded === null && $body !== '' && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Не вдалося розпізнати відповідь GitHub.');
        }

        return $decoded ?? [];
    }
}
