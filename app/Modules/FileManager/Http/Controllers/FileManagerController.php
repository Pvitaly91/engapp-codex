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
    public function index(): View
    {
        $basePath = $this->fileSystemService->getBasePath();

        return view('file-manager::index', [
            'basePath' => $basePath,
        ]);
    }

    /**
     * Get file tree structure
     */
    public function tree(Request $request): JsonResponse
    {
        $path = $request->input('path', '');
        $tree = $this->fileSystemService->getFileTree($path);

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
}
