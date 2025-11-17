<?php

namespace App\Modules\FileManager\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileManager\Services\FileSystemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class FileManagerController extends Controller
{
    private const ASSET_SOURCES = [
        'highlightjs/highlight.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js',
        ],
        'highlightjs/github-dark.min.css' => [
            'type' => 'text/css',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css',
        ],
        'alpinejs/alpine.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js',
        ],
        'codemirror/codemirror.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js',
        ],
        'codemirror/codemirror.min.css' => [
            'type' => 'text/css',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css',
        ],
        'codemirror/addon/mode/multiplex.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/mode/multiplex.min.js',
        ],
        'codemirror/mode/javascript/javascript.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js',
        ],
        'codemirror/mode/clike/clike.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js',
        ],
        'codemirror/mode/php/php.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js',
        ],
        'codemirror/mode/xml/xml.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js',
        ],
        'codemirror/mode/css/css.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js',
        ],
        'codemirror/mode/htmlmixed/htmlmixed.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js',
        ],
        'codemirror/mode/markdown/markdown.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/markdown/markdown.min.js',
        ],
        'codemirror/mode/sql/sql.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js',
        ],
        'codemirror/mode/shell/shell.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/shell/shell.min.js',
        ],
    ];

    /**
     * @var FileSystemService
     */
    protected $fileSystemService;

    public function __construct(FileSystemService $fileSystemService)
    {
        $this->fileSystemService = $fileSystemService;
    }

    /**
     * Display the file manager interface
     */
    public function index(Request $request): View
    {
        $basePath = $this->fileSystemService->getBasePath();
        $requestedPath = $this->sanitizePath($request->query('path'));
        $requestedSelection = $this->sanitizePath($request->query('select'));

        [$initialPath, $initialSelection] = $this->resolveInitialTargets(
            $requestedPath,
            $requestedSelection
        );

        return view('file-manager::index', [
            'basePath' => $basePath,
            'initialPath' => $initialPath,
            'initialSelection' => $initialSelection,
        ]);
    }

    /**
     * Display the IDE-style file manager interface
     */
    public function ide(Request $request): View
    {
        $basePath = $this->fileSystemService->getBasePath();
        $requestedPath = $this->sanitizePath($request->query('path'));
        $requestedSelection = $this->sanitizePath($request->query('select'));

        [$initialPath, $initialSelection] = $this->resolveInitialTargets(
            $requestedPath,
            $requestedSelection
        );

        return view('file-manager::ide', [
            'basePath' => $basePath,
            'initialPath' => $initialPath,
            'initialSelection' => $initialSelection,
        ]);
    }

    /**
     * Get file tree structure
     */
    public function tree(Request $request): JsonResponse
    {
        $path = $this->sanitizePath($request->input('path', ''));

        if ($path !== '') {
            $info = $this->fileSystemService->getFileInfo($path);

            if (! $info || $info['type'] !== 'directory') {
                return response()->json([
                    'success' => false,
                    'error' => 'Запитувана директорія недоступна.',
                ], 404);
            }
        }

        $tree = $this->fileSystemService->getFileTree($path ?: null);

        return response()->json([
            'success' => true,
            'path' => $path,
            'items' => $tree,
        ]);
    }

    /**
     * Get file information
     */
    public function info(Request $request): JsonResponse
    {
        $path = $request->input('path', '');

        if (empty($path)) {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $info = $this->fileSystemService->getFileInfo($path);

        if (! $info) {
            return response()->json([
                'success' => false,
                'error' => 'File not found or access denied',
            ], 404);
        }

        // Format size for display
        if (isset($info['size'])) {
            $info['size_formatted'] = $this->fileSystemService->formatFileSize($info['size']);
        }

        return response()->json([
            'success' => true,
            'info' => $info,
        ]);
    }

    /**
     * Preview file content
     */
    public function preview(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_preview', true)) {
            return response()->json([
                'success' => false,
                'error' => 'File preview is disabled',
            ], 403);
        }

        $path = $request->input('path', '');

        if (empty($path)) {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $content = $this->fileSystemService->getFileContent($path);

        if (! $content) {
            return response()->json([
                'success' => false,
                'error' => 'File not found or access denied',
            ], 404);
        }

        if (isset($content['error'])) {
            return response()->json([
                'success' => false,
                'error' => $content['error'],
                'size' => $content['size'] ?? null,
                'max_size' => $content['max_size'] ?? null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }

    /**
     * Load full file contents for editing without preview size limits.
     */
    public function content(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_edit', true)) {
            return response()->json([
                'success' => false,
                'error' => 'File editing is disabled',
            ], 403);
        }

        $path = $this->sanitizePath($request->input('path', ''));

        if ($path === '') {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $content = $this->fileSystemService->getFileContent($path, false);

        if (! $content) {
            return response()->json([
                'success' => false,
                'error' => 'File not found or access denied',
            ], 404);
        }

        if (isset($content['error'])) {
            return response()->json([
                'success' => false,
                'error' => $content['error'],
            ], 400);
        }

        if (! ($content['is_text'] ?? false)) {
            return response()->json([
                'success' => false,
                'error' => 'Цей файл не можна редагувати онлайн',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }

    /**
     * Download file
     */
    public function download(Request $request): Response|JsonResponse
    {
        if (! config('file-manager.allow_download', true)) {
            return response()->json([
                'success' => false,
                'error' => 'File download is disabled',
            ], 403);
        }

        $path = $request->input('path', '');

        if (empty($path)) {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $info = $this->fileSystemService->getFileInfo($path);

        if (! $info || $info['type'] !== 'file') {
            return response()->json([
                'success' => false,
                'error' => 'File not found or access denied',
            ], 404);
        }

        $fullPath = $this->fileSystemService->getBasePath().'/'.$path;

        return FacadeResponse::download($fullPath, $info['name']);
    }

    /**
     * Update file contents.
     */
    public function update(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_edit', true)) {
            return response()->json([
                'success' => false,
                'error' => 'File editing is disabled',
            ], 403);
        }

        $path = $this->sanitizePath($request->input('path'));
        $content = (string) $request->input('content', '');

        if ($path === '') {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $result = $this->fileSystemService->updateFileContent($path, $content);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Не вдалося зберегти файл',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'size' => $result['size'] ?? null,
            'message' => 'Файл успішно оновлено',
        ]);
    }

    public function asset(string $path): Response|BinaryFileResponse
    {
        try {
            $relativePath = $this->sanitizePath($path);

            if ($relativePath === '' || ! isset(self::ASSET_SOURCES[$relativePath])) {
                return FacadeResponse::make('Asset not found: ' . $path, 404);
            }

            $asset = self::ASSET_SOURCES[$relativePath];
            $localPath = storage_path('app/file-manager-assets/'.$relativePath);

            if (File::exists($localPath)) {
                return FacadeResponse::file($localPath, [
                    'Content-Type' => $asset['type'],
                ]);
            }

            $response = Http::timeout(10)->get($asset['source']);

            if (! $response->successful()) {
                Log::error('Failed to fetch asset from CDN', [
                    'path' => $relativePath,
                    'source' => $asset['source'],
                    'status' => $response->status()
                ]);
                return FacadeResponse::make('Не вдалося отримати ресурс з CDN', 502);
            }

            File::ensureDirectoryExists(dirname($localPath));
            File::put($localPath, $response->body());

            return FacadeResponse::make($response->body(), 200, [
                'Content-Type' => $asset['type'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Asset loading exception', [
                'path' => $path,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            return FacadeResponse::make('Помилка завантаження ресурсу: ' . $exception->getMessage(), 500);
        }
    }

    private function resolveInitialTargets(string $initialPath, string $initialSelection): array
    {
        if ($initialPath !== '') {
            $pathInfo = $this->fileSystemService->getFileInfo($initialPath);

            if (! $pathInfo) {
                $initialPath = '';
            } elseif ($pathInfo['type'] === 'file') {
                $initialSelection = $initialSelection ?: $initialPath;
                $initialPath = $this->sanitizePath(dirname($initialPath));
            }
        }

        if ($initialSelection !== '') {
            $selectionInfo = $this->fileSystemService->getFileInfo($initialSelection);

            if (! $selectionInfo) {
                $initialSelection = '';
            } elseif ($selectionInfo['type'] === 'directory' && $initialPath === '') {
                $initialPath = $initialSelection;
            } elseif ($initialPath === '' && $selectionInfo['type'] === 'file') {
                $initialPath = $this->sanitizePath(dirname($initialSelection));
            }
        }

        return [$initialPath, $initialSelection];
    }

    private function sanitizePath(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = trim((string) $value);
        $normalized = str_replace('\\', '/', $normalized);
        $normalized = trim($normalized, '/');

        if ($normalized === '.' || $normalized === '..') {
            return '';
        }

        return $normalized;
    }
}
