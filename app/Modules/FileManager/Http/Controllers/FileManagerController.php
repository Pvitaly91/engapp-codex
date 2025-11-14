<?php

namespace App\Modules\FileManager\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileManager\Services\FileSystemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\View\View;

class FileManagerController extends Controller
{
    public function __construct(
        protected FileSystemService $fileSystemService
    ) {}

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
