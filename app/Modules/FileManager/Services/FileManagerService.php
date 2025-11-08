<?php

namespace App\Modules\FileManager\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class FileManagerService
{
    private string $rootPath;

    private array $hiddenPaths;

    private int $maxPreviewSize;

    private array $previewExtensions;

    public function __construct()
    {
        $this->rootPath = rtrim(config('file-manager.root_path', base_path()), '/');
        $this->hiddenPaths = config('file-manager.hidden_paths', []);
        $this->maxPreviewSize = config('file-manager.max_preview_size', 1024 * 1024);
        $this->previewExtensions = config('file-manager.preview_extensions', []);
    }

    /**
     * Get directory tree structure
     */
    public function getDirectoryTree(?string $path = null): array
    {
        $fullPath = $this->buildFullPath($path);

        if (! File::isDirectory($fullPath)) {
            throw new RuntimeException('Path is not a directory');
        }

        $items = [];
        $entries = File::directories($fullPath);

        foreach ($entries as $directory) {
            $relativePath = $this->getRelativePath($directory);

            if ($this->isHidden($relativePath)) {
                continue;
            }

            $items[] = [
                'name' => basename($directory),
                'path' => $relativePath,
                'type' => 'directory',
                'size' => null,
                'modified' => File::lastModified($directory),
            ];
        }

        $files = File::files($fullPath);

        foreach ($files as $file) {
            $relativePath = $this->getRelativePath($file->getPathname());

            if ($this->isHidden($relativePath)) {
                continue;
            }

            $items[] = [
                'name' => $file->getFilename(),
                'path' => $relativePath,
                'type' => 'file',
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
                'extension' => $file->getExtension(),
            ];
        }

        return $items;
    }

    /**
     * Get file content
     */
    public function getFileContent(string $path): array
    {
        $fullPath = $this->buildFullPath($path);

        if (! File::exists($fullPath)) {
            throw new RuntimeException('File not found');
        }

        if (! File::isFile($fullPath)) {
            throw new RuntimeException('Path is not a file');
        }

        $size = File::size($fullPath);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

        $canPreview = $size <= $this->maxPreviewSize
            && in_array($extension, $this->previewExtensions);

        $content = null;
        if ($canPreview) {
            try {
                $content = File::get($fullPath);
            } catch (\Exception $e) {
                $content = 'Unable to read file content';
            }
        }

        return [
            'name' => basename($fullPath),
            'path' => $path,
            'size' => $size,
            'extension' => $extension,
            'modified' => File::lastModified($fullPath),
            'can_preview' => $canPreview,
            'content' => $content,
        ];
    }

    /**
     * Get breadcrumbs for path navigation
     */
    public function getBreadcrumbs(?string $path = null): array
    {
        $breadcrumbs = [
            ['name' => 'Root', 'path' => ''],
        ];

        if (! $path) {
            return $breadcrumbs;
        }

        $parts = explode('/', trim($path, '/'));
        $currentPath = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $currentPath .= '/'.$part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => ltrim($currentPath, '/'),
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Build full filesystem path from relative path
     */
    private function buildFullPath(?string $path = null): string
    {
        if (! $path) {
            return $this->rootPath;
        }

        // Prevent directory traversal attacks
        $path = str_replace(['..', "\0"], '', $path);
        $path = ltrim($path, '/');

        return $this->rootPath.'/'.$path;
    }

    /**
     * Get relative path from full path
     */
    private function getRelativePath(string $fullPath): string
    {
        return str_replace($this->rootPath.'/', '', $fullPath);
    }

    /**
     * Check if path should be hidden
     */
    private function isHidden(string $path): bool
    {
        foreach ($this->hiddenPaths as $hiddenPath) {
            if (str_starts_with($path, $hiddenPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get root path
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Get statistics about the current directory
     */
    public function getStatistics(?string $path = null): array
    {
        $fullPath = $this->buildFullPath($path);

        if (! File::isDirectory($fullPath)) {
            throw new RuntimeException('Path is not a directory');
        }

        $directories = 0;
        $files = 0;
        $totalSize = 0;

        foreach (File::directories($fullPath) as $directory) {
            $relativePath = $this->getRelativePath($directory);
            if (! $this->isHidden($relativePath)) {
                $directories++;
            }
        }

        foreach (File::files($fullPath) as $file) {
            $relativePath = $this->getRelativePath($file->getPathname());
            if (! $this->isHidden($relativePath)) {
                $files++;
                $totalSize += $file->getSize();
            }
        }

        return [
            'directories' => $directories,
            'files' => $files,
            'total_size' => $totalSize,
        ];
    }
}
