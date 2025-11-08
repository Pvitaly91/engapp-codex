<?php

namespace App\Modules\FileManager\Http\Controllers;

use App\Modules\FileManager\Services\FileManagerService;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileManagerController
{
    public function __construct(
        private FileManagerService $fileManagerService
    ) {}

    /**
     * Display the file manager interface
     */
    public function index(Request $request): View|ViewFactory
    {
        $path = $request->query('path', '');

        try {
            $items = $this->fileManagerService->getDirectoryTree($path);
            $breadcrumbs = $this->fileManagerService->getBreadcrumbs($path);
            $statistics = $this->fileManagerService->getStatistics($path);
            $rootPath = $this->fileManagerService->getRootPath();

            return view('file-manager::file-manager.index', [
                'items' => $items,
                'breadcrumbs' => $breadcrumbs,
                'statistics' => $statistics,
                'currentPath' => $path,
                'rootPath' => $rootPath,
            ]);
        } catch (\Throwable $exception) {
            return view('file-manager::file-manager.index', [
                'items' => [],
                'breadcrumbs' => [['name' => 'Root', 'path' => '']],
                'statistics' => ['directories' => 0, 'files' => 0, 'total_size' => 0],
                'currentPath' => '',
                'rootPath' => $this->fileManagerService->getRootPath(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get directory contents as JSON
     */
    public function browse(Request $request): JsonResponse
    {
        try {
            $path = $request->query('path', '');
            $items = $this->fileManagerService->getDirectoryTree($path);
            $breadcrumbs = $this->fileManagerService->getBreadcrumbs($path);
            $statistics = $this->fileManagerService->getStatistics($path);

            return response()->json([
                'items' => $items,
                'breadcrumbs' => $breadcrumbs,
                'statistics' => $statistics,
                'path' => $path,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    /**
     * Get file content
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $path = $request->query('path', '');

            if (! $path) {
                throw new \RuntimeException('File path is required');
            }

            $fileData = $this->fileManagerService->getFileContent($path);

            return response()->json($fileData);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
