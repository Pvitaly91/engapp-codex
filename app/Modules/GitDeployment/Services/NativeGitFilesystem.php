<?php

namespace App\Modules\GitDeployment\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class NativeGitFilesystem
{
    public function __construct(private readonly string $repositoryPath)
    {
    }

    public function getRepositoryPath(): string
    {
        return $this->repositoryPath;
    }

    public function getHeadRef(): ?string
    {
        $headFile = $this->repositoryPath . '/.git/HEAD';

        if (! File::exists($headFile)) {
            return null;
        }

        $content = trim(File::get($headFile));

        if (Str::startsWith($content, 'ref:')) {
            return trim(Str::after($content, 'ref:'));
        }

        return null;
    }

    public function getCurrentBranch(): ?string
    {
        $ref = $this->getHeadRef();

        if (! $ref) {
            return null;
        }

        if (Str::startsWith($ref, 'refs/heads/')) {
            return Str::after($ref, 'refs/heads/');
        }

        return null;
    }

    public function getHeadCommit(): ?string
    {
        $ref = $this->getHeadRef();

        if (! $ref) {
            $headFile = $this->repositoryPath . '/.git/HEAD';

            if (File::exists($headFile)) {
                $content = trim(File::get($headFile));

                return $content !== '' ? $content : null;
            }

            return null;
        }

        return $this->readRef($ref);
    }

    public function readRef(string $ref): ?string
    {
        $refPath = $this->repositoryPath . '/.git/' . ltrim($ref, '/');

        if (File::exists($refPath)) {
            return trim(File::get($refPath));
        }

        $packedRefs = $this->repositoryPath . '/.git/packed-refs';

        if (! File::exists($packedRefs)) {
            return null;
        }

        foreach (File::lines($packedRefs) as $line) {
            $line = trim($line);

            if ($line === '' || Str::startsWith($line, '#')) {
                continue;
            }

            if (str_contains($line, ' ')) {
                [$hash, $name] = explode(' ', $line, 2);

                if ($name === $ref) {
                    return trim($hash);
                }
            }
        }

        return null;
    }

    public function writeRef(string $ref, string $sha): void
    {
        if (! preg_match('/^[0-9a-f]{7,40}$/i', $sha)) {
            throw new RuntimeException('Некоректний SHA для запису в ref.');
        }

        $refPath = $this->repositoryPath . '/.git/' . ltrim($ref, '/');
        File::ensureDirectoryExists(dirname($refPath));
        File::put($refPath, $sha . PHP_EOL);
    }

    public function branchExists(string $branch): bool
    {
        return $this->readRef('refs/heads/' . $branch) !== null;
    }

    public function scanWorkingTree(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->repositoryPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $path = $fileInfo->getPathname();
            $relative = ltrim(Str::after($path, $this->repositoryPath), '/');

            if ($relative === '' || Str::startsWith($relative, '.git/')) {
                continue;
            }

            if (in_array(Str::before($relative, '/'), config('git-deployment.preserve_paths'), true)) {
                continue;
            }

            if ($fileInfo->isDir()) {
                continue;
            }

            $content = File::get($path);
            $mode = $this->detectMode($fileInfo);
            $blobSha = $this->calculateBlobSha($content);
            $isBinary = str_contains($content, "\0");

            $files[$relative] = [
                'path' => $relative,
                'content' => $content,
                'mode' => $mode,
                'sha' => $blobSha,
                'binary' => $isBinary,
            ];
        }

        return $files;
    }

    public function replaceWorkingTree(string $sourceDirectory): void
    {
        $preserve = collect(config('git-deployment.preserve_paths'));

        foreach (File::directories($this->repositoryPath) as $directory) {
            $name = basename($directory);

            if ($preserve->contains($name)) {
                continue;
            }

            File::deleteDirectory($directory);
        }

        foreach (File::files($this->repositoryPath) as $file) {
            $name = basename($file);

            if ($preserve->contains($name)) {
                continue;
            }

            File::delete($file);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDirectory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetRelative = ltrim(Str::after($item->getPathname(), $sourceDirectory), '/');

            if ($targetRelative === '') {
                continue;
            }

            $targetPath = $this->repositoryPath . '/' . $targetRelative;

            if ($item->isDir()) {
                File::ensureDirectoryExists($targetPath);
            } else {
                File::ensureDirectoryExists(dirname($targetPath));
                File::put($targetPath, File::get($item->getPathname()));
            }
        }
    }

    private function detectMode(\SplFileInfo $file): string
    {
        return $file->isExecutable() ? '100755' : '100644';
    }

    private function calculateBlobSha(string $content): string
    {
        $size = strlen($content);
        $header = 'blob ' . $size . "\0";

        return sha1($header . $content);
    }
}
