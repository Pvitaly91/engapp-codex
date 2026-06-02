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
        'codemirror/mode/yaml/yaml.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/yaml/yaml.min.js',
        ],
        'codemirror/mode/properties/properties.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/properties/properties.min.js',
        ],
        'codemirror/mode/python/python.min.js' => [
            'type' => 'application/javascript',
            'source' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js',
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
    public function index(Request $request, ?string $path = null): View
    {
        return $this->renderManagerView('file-manager::index', $request, $path);
    }

    /**
     * Display the IDE-style file manager interface
     */
    public function ide(Request $request): View
    {
        return $this->renderManagerView('file-manager::ide', $request);
    }

    /**
     * Display the embeddable file editor page without the admin layout.
     */
    public function embed(Request $request, ?string $path = null): View
    {
        return $this->renderEmbedView('file-manager::embed-page', $request, $path);
    }

    /**
     * Display the embeddable file editor fragment for AJAX insertion.
     */
    public function embedFragment(Request $request, ?string $path = null): View
    {
        return $this->renderEmbedView('file-manager::embed-fragment', $request, $path);
    }

    /**
     * Serve the embeddable file editor bootstrap script.
     */
    public function embedBootstrap(): Response
    {
        return response()
            ->view('file-manager::partials.embed-bootstrap-js', [
                'fragmentUrl' => route('file-manager.embed.fragment'),
                'standaloneUrl' => route('file-manager.embed'),
            ])
            ->header('Content-Type', 'application/javascript; charset=UTF-8');
    }

    /**
     * Display the V2 file manager interface.
     */
    public function v2(Request $request, ?string $path = null): View
    {
        return $this->renderManagerView('file-manager::v2', $request, $path);
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
        $path = $this->sanitizePath($request->input('path', ''));

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

        $path = $this->sanitizePath($request->input('path', ''));

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
     * Read file contents for V2 without returning binary payloads.
     */
    public function read(Request $request): JsonResponse
    {
        $path = $this->sanitizePath($request->input('path', ''));

        if ($path === '') {
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

        $content = $this->fileSystemService->getFileContent($path, false);

        if (! $content) {
            return response()->json([
                'success' => false,
                'error' => 'File not found or access denied',
            ], 404);
        }

        $payload = [
            'path' => $info['path'],
            'name' => $info['name'],
            'size' => $info['size'],
            'modified' => $info['modified'],
            'readable' => $info['readable'],
            'writable' => $info['writable'],
            'extension' => $info['extension'] ?? '',
            'mime_type' => $info['mime_type'] ?? 'application/octet-stream',
            'is_text' => (bool) ($content['is_text'] ?? false),
        ];

        if ($payload['is_text']) {
            $payload['content'] = $content['content'] ?? '';
        }

        return response()->json([
            'success' => true,
            'file' => $payload,
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

        $path = $this->sanitizePath($request->input('path', ''));

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

    /**
     * Create a new file.
     */
    public function createFile(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_create', true)) {
            return response()->json([
                'success' => false,
                'error' => 'Створення файлів вимкнено',
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

        $result = $this->fileSystemService->createFile($path, $content);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Не вдалося створити файл',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'path' => $result['path'] ?? $path,
            'size' => $result['size'] ?? 0,
            'message' => 'Файл успішно створено',
        ]);
    }

    /**
     * Create a new directory.
     */
    public function createDirectory(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_create', true)) {
            return response()->json([
                'success' => false,
                'error' => 'Створення директорій вимкнено',
            ], 403);
        }

        $path = $this->sanitizePath($request->input('path'));

        if ($path === '') {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $result = $this->fileSystemService->createDirectory($path);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Не вдалося створити директорію',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'path' => $result['path'] ?? $path,
            'message' => 'Директорію успішно створено',
        ]);
    }

    /**
     * Delete a file or directory.
     */
    public function delete(Request $request): JsonResponse
    {
        if (! config('file-manager.allow_delete', true)) {
            return response()->json([
                'success' => false,
                'error' => 'Видалення вимкнено',
            ], 403);
        }

        $path = $this->sanitizePath($request->input('path'));

        if ($path === '') {
            return response()->json([
                'success' => false,
                'error' => 'Path is required',
            ], 400);
        }

        $result = $this->fileSystemService->deletePath($path);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Не вдалося видалити елемент',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'path' => $result['path'] ?? $path,
            'type' => $result['type'] ?? null,
            'message' => 'Елемент успішно видалено',
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

    private function renderManagerView(string $view, Request $request, ?string $targetPath = null): View
    {
        $basePath = $this->fileSystemService->getBasePath();
        $routeTarget = $this->sanitizePath($targetPath);
        $requestedPath = $routeTarget !== ''
            ? $routeTarget
            : $this->sanitizePath($request->query('path'));
        $requestedSelection = $routeTarget !== ''
            ? ''
            : $this->sanitizePath($request->query('select'));
        $requestedTarget = $requestedSelection !== '' ? $requestedSelection : $requestedPath;
        $initialMissingTarget = '';

        if ($requestedTarget !== '' && ! $this->fileSystemService->getFileInfo($requestedTarget)) {
            $initialMissingTarget = $requestedTarget;
        }

        [$initialPath, $initialSelection] = $this->resolveInitialTargets(
            $requestedPath,
            $requestedSelection
        );

        return view($view, [
            'basePath' => $basePath,
            'initialPath' => $initialPath,
            'initialSelection' => $initialSelection,
            'initialTarget' => $initialSelection ?: $initialPath,
            'initialMissingTarget' => $initialMissingTarget,
        ]);
    }

    private function renderEmbedView(string $view, Request $request, ?string $targetPath = null): View
    {
        $requestedPath = $this->sanitizePath($targetPath);

        if ($requestedPath === '') {
            $requestedPath = $this->sanitizePath($request->query('path'));
        }

        $requestedPath = $this->sanitizePath($requestedPath);
        $targetInfo = $requestedPath !== ''
            ? $this->fileSystemService->getFileInfo($requestedPath)
            : null;

        $missingTarget = $requestedPath !== '' && ! $targetInfo;
        $directoryTarget = (bool) ($targetInfo && $targetInfo['type'] === 'directory');
        $initialFilePath = $targetInfo && $targetInfo['type'] === 'file'
            ? $requestedPath
            : '';

        return view($view, [
            'basePath' => $this->fileSystemService->getBasePath(),
            'initialFilePath' => $initialFilePath,
            'requestedPath' => $requestedPath,
            'initialMissingTarget' => $missingTarget ? $requestedPath : '',
            'directoryTarget' => $directoryTarget,
            'targetInfo' => $targetInfo,
        ]);
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
