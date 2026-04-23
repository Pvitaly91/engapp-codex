<?php

namespace App\Modules\GitDeployment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Modules\GitDeployment\Models\BackupBranch;
use App\Modules\GitDeployment\Models\BranchUsageHistory;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Modules\GitDeployment\Http\Concerns\ParsesDeploymentPaths;

class NativeDeploymentController extends BaseController
{
    use ParsesDeploymentPaths;

    private const BACKUP_FILE = 'deployment_backups.json';

    public function __construct(private readonly NativeGitDeploymentService $deployment)
    {
    }

    public function index(): View
    {
        return view('git-deployment::deployment.native', $this->indexViewData(
            session('deployment_native_content_preview'),
            session('deployment_native_content_apply')
        ));
    }

    public function contentPreview(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService
    ): View|\Illuminate\Http\JsonResponse {
        $preview = $this->resolveContentPreview($request, $changedContentDeploymentPreviewService);

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($preview);
        }

        return view('git-deployment::deployment.native', $this->indexViewData($preview));
    }

    public function contentApplyPreview(
        Request $request,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService
    ): View|\Illuminate\Http\JsonResponse {
        $contentApply = $changedContentDeploymentApplyService->run(
            $this->deploymentPreviewContext($request, 'native'),
            array_merge($this->contentApplyOptions($request), [
                'requested' => true,
                'dry_run' => true,
            ])
        );

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($contentApply);
        }

        return view('git-deployment::deployment.native', $this->indexViewData(
            is_array($contentApply['preview'] ?? null) ? $contentApply['preview'] : null,
            $contentApply
        ));
    }

    public function deploy(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService
    ): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        $autoPushBranch = $request->input('auto_push_branch', '');
        $autoPushBranch = $this->sanitizeBranchName($autoPushBranch ?? '');
        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'native',
            'source_kind' => 'deploy',
            'branch' => $sanitized,
        ], $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request));

        if ($changedContentDeploymentPreviewService->gateBlocks($contentPreview)) {
            return $this->redirectWithFeedback(
                'error',
                $this->blockedDeploymentMessage('Деплой', $contentPreview, $changedContentDeploymentPreviewService),
                [],
                $sanitized,
                $contentPreview
            );
        }

        try {
            $result = $this->deployment->deploy($sanitized);
            $logs = $result['logs'];
            $message = $result['message'];
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback(
                'error',
                $throwable->getMessage(),
                [],
                $sanitized,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, $throwable->getMessage(), array_merge($contentApplyOptions, ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'deploy',
            'Оновлення сайту до останнього стану гілки через GitHub API'
        );

        $contentApply = null;

        if ($contentApplyRequested) {
            $contentApply = $changedContentDeploymentApplyService->runFromPreview($contentPreview, array_merge(
                $contentApplyOptions,
                [
                    'requested' => true,
                    'dry_run' => false,
                ]
            ));
            $logs[] = $this->formatContentApplyLog($contentApply);

            if (($contentApply['status'] ?? null) === 'content_apply_failed' || is_array($contentApply['error'] ?? null)) {
                $message .= ' Код оновлено, але changed-content apply завершився з помилкою. Глобальний rollback не виконувався.';

                return $this->redirectWithFeedback('error', $message, $logs, $sanitized, $contentPreview, $contentApply);
            }

            $message .= ' Changed content apply виконано успішно.';
        } else {
            $message .= ' Changed content apply пропущено оператором.';
        }

        // Auto-push to branch if specified
        if ($autoPushBranch !== '') {
            try {
                $currentCommit = $this->deployment->headCommit();
                
                // Create branch if it doesn't exist
                $createResult = $this->deployment->createBranch($autoPushBranch, 'current');
                $logs = array_merge($logs, $createResult['logs']);
                
                // Push branch to remote
                $pushResult = $this->deployment->pushBranch($autoPushBranch);
                $logs = array_merge($logs, $pushResult['logs']);
                
                $message .= " Поточний стан також запушено на origin/{$autoPushBranch}.";
                
                // Track auto-push usage
                BranchUsageHistory::trackUsage(
                    $autoPushBranch,
                    'auto_push',
                    "Автоматичний пуш після оновлення на віддалену гілку {$autoPushBranch} через GitHub API"
                );

                // Update backup branch record
                BackupBranch::updateOrCreate(
                    ['name' => $autoPushBranch],
                    [
                        'commit_hash' => $currentCommit,
                        'pushed_at' => now(),
                    ]
                );
            } catch (\Throwable $throwable) {
                $message .= " Проте не вдалося запушити на origin/{$autoPushBranch}: " . $throwable->getMessage();
            }
        }

        return $this->redirectWithFeedback('success', $message, $logs, $sanitized, $contentPreview, $contentApply);
    }

    public function deployPartial(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        $pathsInput = $request->input('paths', []);

        // Парсимо та валідуємо шляхи
        $parsed = $this->parseAndValidatePaths($pathsInput);
        $paths = $parsed['valid'];
        $pathErrors = $parsed['errors'];

        if ($paths === []) {
            $errorMessage = 'Не вказано жодного валідного шляху для часткового деплою.';
            if ($pathErrors !== []) {
                $errorMessage .= ' Помилки: ' . implode('; ', $pathErrors);
            }
            return $this->redirectWithFeedback('error', $errorMessage, [], $sanitized);
        }

        try {
            $result = $this->deployment->deployPartial($sanitized, $paths);
            $logs = $result['logs'];
            $message = $result['message'];
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], $sanitized);
        }

        // Track branch usage
        $pathsList = implode(', ', $paths);
        BranchUsageHistory::trackUsage(
            $sanitized,
            'partial_deploy',
            "Частковий деплой через GitHub API. Шляхи: {$pathsList}",
            $paths
        );

        if ($pathErrors !== []) {
            $message .= ' Попередження: ' . implode('; ', $pathErrors);
        }

        return $this->redirectWithFeedback('success', $message, $logs, $sanitized);
    }

    public function pushCurrent(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        try {
            $result = $this->deployment->push($sanitized);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], $sanitized);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'push',
            'Пуш поточного стану на віддалену гілку через GitHub API'
        );

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], $sanitized);
    }

    public function rollback(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService
    ): RedirectResponse
    {
        $commit = $request->input('commit');
        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'native',
            'source_kind' => 'backup_restore',
            'commit' => trim((string) $commit),
        ], $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request));

        if ($changedContentDeploymentPreviewService->gateBlocks($contentPreview)) {
            return $this->redirectWithFeedback(
                'error',
                $this->blockedDeploymentMessage('Відкат', $contentPreview, $changedContentDeploymentPreviewService),
                [],
                null,
                $contentPreview
            );
        }

        try {
            $result = $this->deployment->rollback($commit);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback(
                'error',
                $throwable->getMessage(),
                [],
                null,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, $throwable->getMessage(), array_merge($contentApplyOptions, ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        $contentApply = null;
        $message = $result['message'];
        $logs = $result['logs'];

        if ($contentApplyRequested) {
            $contentApply = $changedContentDeploymentApplyService->runFromPreview($contentPreview, array_merge(
                $contentApplyOptions,
                [
                    'requested' => true,
                    'dry_run' => false,
                ]
            ));
            $logs[] = $this->formatContentApplyLog($contentApply);

            if (($contentApply['status'] ?? null) === 'content_apply_failed' || is_array($contentApply['error'] ?? null)) {
                $message .= ' Restore виконано, але changed-content apply завершився з помилкою. Глобальний rollback не виконувався.';

                return $this->redirectWithFeedback('error', $message, $logs, null, $contentPreview, $contentApply);
            }

            $message .= ' Changed content apply виконано успішно.';
        } else {
            $message .= ' Changed content apply пропущено оператором.';
        }

        return $this->redirectWithFeedback('success', $message, $logs, null, $contentPreview, $contentApply);
    }

    public function createBackupBranch(Request $request): RedirectResponse
    {
        $branch = $request->input('branch_name', '');
        $commit = $request->input('commit', 'current');

        $sanitizedBranch = $this->sanitizeBranchName($branch ?? '');
        $resolvedCommit = $commit === 'current' ? ($this->deployment->headCommit() ?? '') : $commit;

        try {
            $result = $this->deployment->createBranch($sanitizedBranch, $commit);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        BackupBranch::updateOrCreate(
            ['name' => $sanitizedBranch],
            [
                'commit_hash' => $resolvedCommit,
                'pushed_at' => null,
            ]
        );

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], null);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        try {
            $result = $this->deployment->pushBranch($backupBranch->name);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], null);
    }

    public function createAndPushBranch(Request $request): RedirectResponse
    {
        $branchName = $request->input('quick_branch_name', '');
        $sanitized = $this->sanitizeBranchName($branchName ?? '');

        if ($sanitized === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки.', [], null);
        }

        $currentCommit = $this->deployment->headCommit();
        if (! $currentCommit) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', [], null);
        }

        try {
            // Create branch if it doesn't exist
            $createResult = $this->deployment->createBranch($sanitized, 'current');
            $logs = $createResult['logs'];

            // Push branch to remote
            $pushResult = $this->deployment->pushBranch($sanitized);
            $logs = array_merge($logs, $pushResult['logs']);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'create_and_push',
            'Швидке створення та пуш гілки з поточним станом сайту через GitHub API'
        );

        // Update or create backup branch record
        BackupBranch::updateOrCreate(
            ['name' => $sanitized],
            [
                'commit_hash' => $currentCommit,
                'pushed_at' => now(),
            ]
        );

        return $this->redirectWithFeedback(
            'success',
            "Гілку {$sanitized} успішно створено та запушено на віддалений репозиторій через GitHub API.",
            $logs,
            null
        );
    }

    private function redirectWithFeedback(
        string $status,
        string $message,
        array $logs,
        ?string $branch,
        ?array $contentPreview = null,
        ?array $contentApply = null
    ): RedirectResponse
    {
        $redirect = redirect()
            ->route('deployment.native.index')
            ->with('deployment_native', [
                'status' => $status,
                'message' => $message,
                'logs' => $logs,
                'branch' => $branch,
            ]);

        if ($contentPreview !== null) {
            $redirect->with('deployment_native_content_preview', $contentPreview);
        }

        if ($contentApply !== null) {
            $redirect->with('deployment_native_content_apply', $contentApply);
        }

        return $redirect;
    }

    private function loadBackups(): array
    {
        $path = $this->ensureBackupFile();

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function ensureBackupFile(): string
    {
        $path = storage_path('app/' . self::BACKUP_FILE);
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($path)) {
            File::put($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function indexViewData(?array $contentPreview = null, ?array $contentApply = null): array
    {
        $backups = array_reverse($this->loadBackups());
        $feedback = session('deployment_native');
        $backupBranches = BackupBranch::query()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $this->deployment->currentBranch(),
            'currentCommit' => $this->deployment->headCommit(),
            'supportsShell' => $this->supportsShellCommands(),
            'recentUsage' => BranchUsageHistory::getRecentUsage(10),
            'availableFolders' => $this->getAvailableFolders(),
            'contentPreview' => $contentPreview,
            'contentApply' => $contentApply,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveContentPreview(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService
    ): array {
        return $changedContentDeploymentPreviewService->preview(
            $this->deploymentPreviewContext($request, 'native'),
            $this->contentPreviewOptions($request)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function contentPreviewOptions(Request $request): array
    {
        return [
            'with_release_check' => $request->has('with_release_check')
                ? $request->boolean('with_release_check')
                : (bool) config('git-deployment.content_preview.with_release_check', true),
            'check_profile' => (string) $request->query(
                'check_profile',
                config('git-deployment.content_preview.check_profile', 'release')
            ),
            'strict' => $request->has('strict')
                ? $request->boolean('strict')
                : (bool) config('git-deployment.content_preview.strict', true),
        ];
    }

    /**
     * @param  array<string, mixed>  $contentApplyOptions
     * @return array<string, mixed>
     */
    private function contentPreviewOptionsFromApplyOptions(array $contentApplyOptions): array
    {
        return [
            'with_release_check' => (bool) ($contentApplyOptions['with_release_check'] ?? true),
            'check_profile' => (string) ($contentApplyOptions['check_profile'] ?? 'release'),
            'strict' => (bool) ($contentApplyOptions['strict'] ?? true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentApplyOptions(Request $request): array
    {
        return [
            'with_release_check' => $request->has('content_apply_with_release_check')
                ? $request->boolean('content_apply_with_release_check')
                : (bool) config('git-deployment.content_apply.with_release_check', true),
            'skip_release_check' => $request->has('content_apply_skip_release_check')
                ? $request->boolean('content_apply_skip_release_check')
                : (bool) config('git-deployment.content_apply.skip_release_check', false),
            'check_profile' => (string) $request->input(
                'content_apply_check_profile',
                config('git-deployment.content_apply.check_profile', 'release')
            ),
            'strict' => $request->has('content_apply_strict')
                ? $request->boolean('content_apply_strict')
                : (bool) config('git-deployment.content_apply.strict', true),
            'write_report' => true,
        ];
    }

    private function contentApplyRequested(Request $request): bool
    {
        if ($request->has('apply_changed_content')) {
            return $request->boolean('apply_changed_content');
        }

        return (bool) config('git-deployment.content_apply.enabled_by_default', true);
    }

    private function blockedDeploymentMessage(
        string $actionLabel,
        array $contentPreview,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService
    ): string {
        $reasons = $changedContentDeploymentPreviewService->gateReasons($contentPreview);

        return $actionLabel . ' зупинено content-aware safety gate до початку code update. '
            . ($reasons !== [] ? implode(' ', $reasons) : 'Content preview returned blockers.');
    }

    /**
     * @return array<string, mixed>
     */
    private function deploymentPreviewContext(Request $request, string $mode): array
    {
        $sourceKind = strtolower(trim((string) $request->query('source_kind', $request->input('source_kind', 'deploy'))));

        return match ($sourceKind) {
            'backup_restore' => [
                'mode' => $mode,
                'source_kind' => 'backup_restore',
                'commit' => trim((string) $request->query('commit', $request->input('commit', ''))),
            ],
            default => [
                'mode' => $mode,
                'source_kind' => 'deploy',
                'branch' => $this->sanitizeBranchName((string) $request->query('branch', $request->input('branch', 'main'))) ?: 'main',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $contentApply
     */
    private function formatContentApplyLog(array $contentApply): string
    {
        $applyResult = (array) ($contentApply['content_apply']['result'] ?? []);
        $summary = (array) ($applyResult['plan']['summary'] ?? []);
        $preflight = (array) ($applyResult['preflight']['summary'] ?? []);
        $parts = [
            'Changed content apply:',
            'status=' . (string) ($contentApply['status'] ?? 'completed'),
            'base=' . (string) (($contentApply['deployment']['base_ref'] ?? null) ?: '—'),
            'head=' . (string) (($contentApply['deployment']['head_ref'] ?? null) ?: '—'),
            sprintf(
                'changed=%d deleted-cleanup=%d seed=%d refresh=%d',
                (int) ($summary['changed_packages'] ?? 0),
                (int) ($summary['deleted_cleanup_candidates'] ?? 0),
                (int) ($summary['seed_candidates'] ?? 0),
                (int) ($summary['refresh_candidates'] ?? 0)
            ),
            sprintf(
                'preflight ok=%d warn=%d fail=%d',
                (int) ($preflight['ok'] ?? 0),
                (int) ($preflight['warn'] ?? 0),
                (int) ($preflight['fail'] ?? 0)
            ),
        ];

        if (! empty($contentApply['artifacts']['report_path'])) {
            $parts[] = 'report=' . (string) $contentApply['artifacts']['report_path'];
        }

        if (! empty($contentApply['error']['message'])) {
            $parts[] = 'error=' . (string) $contentApply['error']['message'];
        }

        return implode(' | ', $parts);
    }

    private function sanitizeBranchName(string $branch): string
    {
        $normalized = Str::of($branch)->trim()->value();
        $sanitized = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $normalized);

        return $sanitized !== null && $sanitized !== '' ? $sanitized : $normalized;
    }

    private function supportsShellCommands(): bool
    {
        return function_exists('proc_open');
    }

    /**
     * Отримує дерево папок для часткового деплою (до 5 рівнів глибини).
     *
     * @return array
     */
    private function getAvailableFolders(): array
    {
        $preservePaths = config('git-deployment.preserve_paths', []);
        $basePath = base_path();

        return $this->buildFolderTree($basePath, $preservePaths, 5);
    }

    /**
     * Рекурсивно будує дерево папок.
     *
     * @param string $path Шлях до директорії
     * @param array $preservePaths Захищені шляхи
     * @param int $maxDepth Максимальна глибина
     * @param int $currentDepth Поточна глибина
     * @return array
     */
    private function buildFolderTree(string $path, array $preservePaths, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        $tree = [];
        $dirs = File::directories($path);

        foreach ($dirs as $dir) {
            $name = basename($dir);
            
            // Пропускаємо захищені та приховані директорії на першому рівні
            if ($currentDepth === 0 && (in_array($name, $preservePaths, true) || str_starts_with($name, '.'))) {
                continue;
            }
            
            // Пропускаємо приховані директорії на всіх рівнях
            if (str_starts_with($name, '.')) {
                continue;
            }

            $children = $this->buildFolderTree($dir, $preservePaths, $maxDepth, $currentDepth + 1);
            
            $tree[$name] = $children;
        }

        ksort($tree);

        return $tree;
    }
}
