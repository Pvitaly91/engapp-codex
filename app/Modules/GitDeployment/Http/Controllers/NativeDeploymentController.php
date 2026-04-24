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
use App\Modules\GitDeployment\Services\DeploymentContentLockService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Modules\GitDeployment\Http\Concerns\ParsesDeploymentPaths;
use App\Services\ContentDeployment\ContentSyncApplyService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentOperationsDoctorService;
use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use App\Services\ContentDeployment\ContentOpsCiStatusService;
use App\Services\ContentDeployment\ContentReleaseGateService;
use App\Services\ContentDeployment\ContentSyncPlanService;

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
            session('deployment_native_content_apply'),
            session('deployment_native_content_sync_preview'),
            session('deployment_native_content_sync_apply'),
            session('deployment_native_content_doctor'),
            session('deployment_native_content_release_gate'),
            session('deployment_native_content_ci_status'),
            session('deployment_native_content_ci_dispatch')
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
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if (! $this->supportsShellCommands()) {
            $message = 'content-aware changed-content apply preview недоступний, оскільки proc_open вимкнено на цьому сервері.';

            if ($request->expectsJson() || $request->boolean('json')) {
                return response()->json([
                    'status' => 'blocked',
                    'message' => $message,
                ]);
            }

            return redirect()
                ->route('deployment.native.index')
                ->with('deployment_native', [
                    'status' => 'error',
                    'message' => $message,
                    'logs' => [],
                    'branch' => (string) $request->input('branch', $request->query('branch', 'main')),
                ]);
        }

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
            $contentApply,
            session('deployment_native_content_sync_preview'),
            session('deployment_native_content_sync_apply')
        ));
    }

    public function contentSyncPreview(
        Request $request,
        ContentSyncPlanService $contentSyncPlanService
    ): View|\Illuminate\Http\JsonResponse {
        $preview = $contentSyncPlanService->run($this->contentSyncPlanOptions($request));

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($preview);
        }

        return view('git-deployment::deployment.native', $this->indexViewData(
            session('deployment_native_content_preview'),
            session('deployment_native_content_apply'),
            $preview,
            session('deployment_native_content_sync_apply')
        ));
    }

    public function contentDoctor(
        Request $request,
        ContentOperationsDoctorService $contentOperationsDoctorService
    ): View|\Illuminate\Http\JsonResponse {
        $doctor = $contentOperationsDoctorService->run($this->contentDoctorOptions($request));

        if ($request->boolean('write_report')) {
            $path = $contentOperationsDoctorService->writeReport($doctor);
            $doctor['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($doctor);
        }

        return view('git-deployment::deployment.native', $this->indexViewData(
            session('deployment_native_content_preview'),
            session('deployment_native_content_apply'),
            session('deployment_native_content_sync_preview'),
            session('deployment_native_content_sync_apply'),
            $doctor
        ));
    }

    public function contentReleaseGate(
        Request $request,
        ContentReleaseGateService $contentReleaseGateService
    ): View|\Illuminate\Http\JsonResponse {
        $target = trim((string) $request->query('target', $request->input('target', '')));
        $releaseGate = $contentReleaseGateService->run(
            $target !== '' ? $target : null,
            $this->contentReleaseGateOptions($request)
        );

        if ($request->boolean('write_report')) {
            $path = $contentReleaseGateService->writeReport($releaseGate);
            $releaseGate['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($releaseGate);
        }

        return view('git-deployment::deployment.native', $this->indexViewData(
            session('deployment_native_content_preview'),
            session('deployment_native_content_apply'),
            session('deployment_native_content_sync_preview'),
            session('deployment_native_content_sync_apply'),
            session('deployment_native_content_doctor'),
            $releaseGate
        ));
    }

    public function contentCiStatus(
        Request $request,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): View|\Illuminate\Http\JsonResponse {
        $ciStatus = $contentOpsCiStatusService->run($this->contentCiStatusOptions($request));

        if ($request->boolean('write_report')) {
            $path = $contentOpsCiStatusService->writeReport($ciStatus);
            $ciStatus['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($ciStatus);
        }

        return view('git-deployment::deployment.native', $this->indexViewData(
            session('deployment_native_content_preview'),
            session('deployment_native_content_apply'),
            session('deployment_native_content_sync_preview'),
            session('deployment_native_content_sync_apply'),
            session('deployment_native_content_doctor'),
            session('deployment_native_content_release_gate'),
            $ciStatus,
            session('deployment_native_content_ci_dispatch')
        ));
    }

    public function contentCiDispatch(
        Request $request,
        ContentOpsCiDispatchService $contentOpsCiDispatchService
    ): View|\Illuminate\Http\JsonResponse|RedirectResponse {
        $dispatch = $contentOpsCiDispatchService->run($this->contentCiDispatchOptions($request));

        if ($request->boolean('write_report')) {
            $path = $contentOpsCiDispatchService->writeReport($dispatch);
            $dispatch['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($dispatch);
        }

        $status = (string) data_get($dispatch, 'dispatch.status', 'failed');
        $message = match ($status) {
            'simulated' => 'ContentOps CI dispatch dry-run completed.',
            'dispatched' => 'ContentOps CI Release Gate workflow dispatch accepted.',
            default => (string) data_get($dispatch, 'error.message', 'ContentOps CI dispatch failed.'),
        };

        return redirect()
            ->route('deployment.native.index')
            ->with('deployment_native', [
                'status' => $status === 'failed' ? 'error' : 'success',
                'message' => $message,
                'logs' => [],
                'branch' => data_get($dispatch, 'target.branch'),
            ])
            ->with('deployment_native_content_ci_dispatch', $dispatch)
            ->with('deployment_native_content_ci_status', session('deployment_native_content_ci_status'));
    }

    public function contentSync(
        Request $request,
        ContentSyncApplyService $contentSyncApplyService,
        ContentOperationRunService $contentOperationRunService,
        ContentOperationLockService $contentOperationLockService
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $options = $this->contentSyncApplyOptions($request);
        $run = null;
        $historyWarning = null;

        try {
            $run = $contentOperationRunService->start([
                'operation_kind' => 'deployment_sync_repair',
                'trigger_source' => 'native_deployment_ui',
                'domains' => $options['domains'] ?? ['v3', 'page-v3'],
                'head_ref' => $options['head_ref'] ?? null,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'strict' => (bool) ($options['strict'] ?? false),
                'with_release_check' => $options['with_release_check'] ?? null,
                'skip_release_check' => $options['skip_release_check'] ?? null,
                'bootstrap_uninitialized' => (bool) ($options['bootstrap_uninitialized'] ?? false),
                'operator_user_id' => $request->user()?->getAuthIdentifier(),
                'meta' => [
                    'deployment_mode' => 'native',
                    'repair_flow' => true,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            $historyWarning = 'Content operation history could not be initialized: ' . $exception->getMessage();
        }

        $lockLease = $contentOperationLockService->acquire([
            'operation_kind' => 'deployment_sync_repair',
            'trigger_source' => 'native_deployment_ui',
            'domains' => $options['domains'] ?? ['v3', 'page-v3'],
            'content_operation_run_id' => $run?->id,
            'operator_user_id' => $request->user()?->getAuthIdentifier(),
            'meta' => [
                'deployment_mode' => 'native',
                'repair_flow' => true,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
            ],
        ], $request->boolean('content_sync_takeover_stale_lock'));

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $contentSyncApply = $this->lockBlockedContentSyncApply($lockLease, $options, 'native_deployment_ui');
        } else {
            try {
                $ownerToken = $lockLease['owner_token'] ?? null;
                $contentSyncApply = $contentSyncApplyService->run($options + [
                    'heartbeat' => fn (): null => $this->heartbeatContentLock($contentOperationLockService, $ownerToken),
                ]);
            } finally {
                $contentOperationLockService->release($lockLease['owner_token'] ?? null);
            }

            $contentSyncApply['lock'] = $this->contentLockPayload($lockLease, 'deployment_sync_repair', 'native_deployment_ui');
        }

        if ($run !== null) {
            try {
                $recordedRun = $this->recordSyncRepairRun($contentOperationRunService, $run, $contentSyncApply);
                $contentSyncApply['operation_run'] = [
                    'id' => $recordedRun->id,
                    'status' => $recordedRun->status,
                    'payload_json_path' => $recordedRun->payload_json_path ? storage_path('app/' . $recordedRun->payload_json_path) : null,
                    'report_path' => $recordedRun->report_path ? storage_path('app/' . $recordedRun->report_path) : null,
                ];
            } catch (\Throwable $exception) {
                report($exception);
                $historyWarning = 'Content operation history could not be finalized: ' . $exception->getMessage();
            }
        }

        if ($historyWarning !== null) {
            $contentSyncApply['operation_run']['warning'] = $historyWarning;
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($contentSyncApply);
        }

        return $this->redirectWithFeedback(
            empty($contentSyncApply['error']) ? 'success' : 'error',
            empty($contentSyncApply['error'])
                ? (((bool) ($contentSyncApply['apply']['dry_run'] ?? false))
                    ? 'Dry-run content sync repair completed.'
                    : 'Content sync repair completed.')
                : (string) ($contentSyncApply['error']['message'] ?? 'Content sync repair failed.'),
            [],
            null,
            null,
            null,
            $contentSyncApply['plan'] ?? null,
            $contentSyncApply
        );
    }

    public function deploy(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService,
        DeploymentContentLockService $deploymentContentLockService,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        $autoPushBranch = $request->input('auto_push_branch', '');
        $autoPushBranch = $this->sanitizeBranchName($autoPushBranch ?? '');
        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);

        $previewOptions = $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request);
        $previewOptions['content_apply_requested'] = $contentApplyRequested;

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'native',
            'source_kind' => 'deploy',
            'branch' => $sanitized,
        ], $previewOptions);

        if ($changedContentDeploymentPreviewService->gateBlocks($contentPreview)) {
            return $this->redirectWithFeedback(
                'error',
                $this->blockedDeploymentMessage('Деплой', $contentPreview, $changedContentDeploymentPreviewService),
                [],
                $sanitized,
                $contentPreview
            );
        }

        if ((bool) config('git-deployment.contentops_ci_status.required_for_deploy', false)) {
            $contentCiStatus = $this->deploymentContentCiStatus($request, $contentOpsCiStatusService, $contentPreview, $sanitized);

            if ($this->contentCiStatusBlocksDeployment($contentCiStatus)) {
                return $this->redirectWithFeedback(
                    'error',
                    'Деплой зупинено ContentOps CI gate до початку code update. ' . (string) data_get($contentCiStatus, 'readiness.message', 'ContentOps CI status is not deploy-ready.'),
                    [],
                    $sanitized,
                    $contentPreview,
                    null,
                    null,
                    null,
                    $contentCiStatus
                );
            }
        }

        $contentLockReservation = null;

        if ($contentApplyRequested) {
            $contentLockReservation = $deploymentContentLockService->reserve($contentPreview, array_merge(
                $contentApplyOptions,
                [
                    'requested' => true,
                    'dry_run' => false,
                    'trigger_source' => 'native_deployment_ui',
                    'operator_user_id' => $request->user()?->getAuthIdentifier(),
                ]
            ));

            if ((bool) ($contentLockReservation['blocked'] ?? false)) {
                $contentApply = $changedContentDeploymentApplyService->lockBlockedResult($contentPreview, $contentLockReservation, array_merge(
                    $contentApplyOptions,
                    [
                        'requested' => true,
                        'dry_run' => false,
                    ]
                ));

                return $this->redirectWithFeedback(
                    'error',
                    'Деплой зупинено content-operation lock gate до початку code update.',
                    [],
                    $sanitized,
                    $contentPreview,
                    $contentApply
                );
            }
        }

        try {
            $deploymentContentLockService->heartbeat($contentLockReservation);
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
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, $throwable->getMessage(), array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
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
            $deploymentContentLockService->heartbeat($contentLockReservation);
            $contentApply = $changedContentDeploymentApplyService->runFromPreview($contentPreview, array_merge(
                $contentApplyOptions,
                $deploymentContentLockService->applyOptions($contentLockReservation),
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
        } finally {
            $deploymentContentLockService->release($contentLockReservation);
        }
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
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService,
        DeploymentContentLockService $deploymentContentLockService,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): RedirectResponse
    {
        $commit = $request->input('commit');
        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);

        $previewOptions = $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request);
        $previewOptions['content_apply_requested'] = $contentApplyRequested;

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'native',
            'source_kind' => 'backup_restore',
            'commit' => trim((string) $commit),
        ], $previewOptions);

        if ($changedContentDeploymentPreviewService->gateBlocks($contentPreview)) {
            return $this->redirectWithFeedback(
                'error',
                $this->blockedDeploymentMessage('Відкат', $contentPreview, $changedContentDeploymentPreviewService),
                [],
                null,
                $contentPreview
            );
        }

        if ((bool) config('git-deployment.contentops_ci_status.required_for_deploy', false)) {
            $contentCiStatus = $this->deploymentContentCiStatus($request, $contentOpsCiStatusService, $contentPreview, null);

            if ($this->contentCiStatusBlocksDeployment($contentCiStatus)) {
                return $this->redirectWithFeedback(
                    'error',
                    'Відкат зупинено ContentOps CI gate до початку restore. ' . (string) data_get($contentCiStatus, 'readiness.message', 'ContentOps CI status is not deploy-ready.'),
                    [],
                    null,
                    $contentPreview,
                    null,
                    null,
                    null,
                    $contentCiStatus
                );
            }
        }

        $contentLockReservation = null;

        if ($contentApplyRequested) {
            $contentLockReservation = $deploymentContentLockService->reserve($contentPreview, array_merge(
                $contentApplyOptions,
                [
                    'requested' => true,
                    'dry_run' => false,
                    'trigger_source' => 'native_deployment_ui',
                    'operator_user_id' => $request->user()?->getAuthIdentifier(),
                ]
            ));

            if ((bool) ($contentLockReservation['blocked'] ?? false)) {
                $contentApply = $changedContentDeploymentApplyService->lockBlockedResult($contentPreview, $contentLockReservation, array_merge(
                    $contentApplyOptions,
                    [
                        'requested' => true,
                        'dry_run' => false,
                    ]
                ));

                return $this->redirectWithFeedback(
                    'error',
                    'Відкат зупинено content-operation lock gate до початку restore.',
                    [],
                    null,
                    $contentPreview,
                    $contentApply
                );
            }
        }

        try {
            $deploymentContentLockService->heartbeat($contentLockReservation);
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
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, $throwable->getMessage(), array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        $contentApply = null;
        $message = $result['message'];
        $logs = $result['logs'];

        if ($contentApplyRequested) {
            $deploymentContentLockService->heartbeat($contentLockReservation);
            $contentApply = $changedContentDeploymentApplyService->runFromPreview($contentPreview, array_merge(
                $contentApplyOptions,
                $deploymentContentLockService->applyOptions($contentLockReservation),
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
        } finally {
            $deploymentContentLockService->release($contentLockReservation);
        }
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
        ?array $contentApply = null,
        ?array $contentSyncPreview = null,
        ?array $contentSyncApply = null,
        ?array $contentCiStatus = null
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

        if ($contentSyncPreview !== null) {
            $redirect->with('deployment_native_content_sync_preview', $contentSyncPreview);
        }

        if ($contentSyncApply !== null) {
            $redirect->with('deployment_native_content_sync_apply', $contentSyncApply);
        }

        if ($contentCiStatus !== null) {
            $redirect->with('deployment_native_content_ci_status', $contentCiStatus);
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
    private function indexViewData(
        ?array $contentPreview = null,
        ?array $contentApply = null,
        ?array $contentSyncPreview = null,
        ?array $contentSyncApply = null,
        ?array $contentDoctor = null,
        ?array $contentReleaseGate = null,
        ?array $contentCiStatus = null,
        ?array $contentCiDispatch = null
    ): array
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
            'contentSyncPreview' => $contentSyncPreview,
            'contentSyncApply' => $contentSyncApply,
            'contentDoctor' => $contentDoctor,
            'contentReleaseGate' => $contentReleaseGate,
            'contentCiStatus' => $contentCiStatus,
            'contentCiDispatch' => $contentCiDispatch,
            'recentContentRuns' => $this->recentContentRuns(),
            'contentOperationLockStatus' => $this->contentOperationLockStatus(),
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
            'content_apply_requested' => $request->has('apply_changed_content')
                ? $request->boolean('apply_changed_content')
                : (bool) config('git-deployment.content_apply.enabled_by_default', true),
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
            'takeover_stale_lock' => $request->boolean('content_apply_takeover_stale_lock'),
        ];
    }

    private function contentApplyRequested(Request $request): bool
    {
        if (! $this->supportsShellCommands()) {
            return false;
        }

        if ($request->has('apply_changed_content')) {
            return $request->boolean('apply_changed_content');
        }

        return (bool) config('git-deployment.content_apply.enabled_by_default', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function contentSyncPlanOptions(Request $request): array
    {
        return [
            'domains' => $request->input('domains'),
            'head_ref' => (string) ($this->deployment->headCommit() ?? ''),
            'with_release_check' => $request->has('content_sync_with_release_check')
                ? $request->boolean('content_sync_with_release_check')
                : (bool) config('git-deployment.content_apply.with_release_check', true),
            'check_profile' => (string) $request->input(
                'content_sync_check_profile',
                config('git-deployment.content_apply.check_profile', 'release')
            ),
            'strict' => $request->has('content_sync_strict')
                ? $request->boolean('content_sync_strict')
                : (bool) config('git-deployment.content_apply.strict', true),
            'write_report' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentSyncApplyOptions(Request $request): array
    {
        $runMode = strtolower(trim((string) $request->input('run_mode', 'live')));
        $dryRun = $runMode === 'dry_run' || $request->boolean('dry_run');

        return [
            'domains' => $request->input('domains'),
            'head_ref' => (string) ($this->deployment->headCommit() ?? ''),
            'dry_run' => $dryRun,
            'force' => ! $dryRun,
            'with_release_check' => $request->has('content_sync_with_release_check')
                ? $request->boolean('content_sync_with_release_check')
                : (bool) config('git-deployment.content_apply.with_release_check', true),
            'skip_release_check' => $request->has('content_sync_skip_release_check')
                ? $request->boolean('content_sync_skip_release_check')
                : (bool) config('git-deployment.content_apply.skip_release_check', false),
            'check_profile' => (string) $request->input(
                'content_sync_check_profile',
                config('git-deployment.content_apply.check_profile', 'release')
            ),
            'strict' => $request->has('content_sync_strict')
                ? $request->boolean('content_sync_strict')
                : (bool) config('git-deployment.content_apply.strict', true),
            'bootstrap_uninitialized' => $request->boolean('content_sync_bootstrap_uninitialized'),
            'write_report' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentDoctorOptions(Request $request): array
    {
        return [
            'domains' => $request->query('domains', $request->input('domains')),
            'strict' => $request->boolean('strict'),
            'with_git' => $request->boolean('with_git'),
            'with_artifacts' => $request->boolean('with_artifacts'),
            'with_deployment' => $request->boolean('with_deployment', true),
            'with_package_roots' => $request->boolean('with_package_roots', true),
            'with_dry_plan' => $request->boolean('with_dry_plan'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentReleaseGateOptions(Request $request): array
    {
        return [
            'base' => (string) $request->query('base', $request->input('base', '')),
            'head' => (string) $request->query('head', $request->input('head', '')),
            'staged' => $request->boolean('staged'),
            'working_tree' => $request->boolean('working_tree'),
            'include_untracked' => $request->boolean('include_untracked'),
            'domains' => $request->query('domains', $request->input('domains')),
            'with_release_check' => $request->boolean('with_release_check', true),
            'check_profile' => (string) $request->query('check_profile', $request->input('check_profile', 'release')),
            'with_doctor' => true,
            'with_git' => $request->boolean('with_git'),
            'with_artifacts' => $request->boolean('with_artifacts'),
            'with_deployment' => $request->boolean('with_deployment', true),
            'with_package_roots' => $request->boolean('with_package_roots', true),
            'with_dry_plan' => $request->boolean('with_dry_plan'),
            'profile' => (string) $request->query('profile', $request->input('profile', 'deployment')),
            'strict' => $request->boolean('strict'),
            'fail_on_warnings' => $request->boolean('fail_on_warnings'),
            'fail_on_lock' => $request->boolean('fail_on_lock'),
            'fail_on_stale_lock' => $request->boolean('fail_on_stale_lock'),
            'fail_on_sync_drift' => $request->boolean('fail_on_sync_drift'),
            'fail_on_uninitialized_sync' => $request->boolean('fail_on_uninitialized_sync'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentCiStatusOptions(Request $request): array
    {
        return [
            'ref' => (string) $request->query('ref', $request->input('ref', '')),
            'branch' => (string) $request->query('branch', $request->input('branch', '')),
            'sha' => (string) $request->query('sha', $request->input('sha', '')),
            'workflow' => (string) $request->query(
                'workflow',
                $request->input('workflow', config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml'))
            ),
            'strict' => $request->boolean('strict'),
            'require_success' => $request->has('require_success')
                ? $request->boolean('require_success')
                : (bool) config('git-deployment.contentops_ci_status.required_for_deploy', false),
            'allow_in_progress' => $request->has('allow_in_progress')
                ? $request->boolean('allow_in_progress')
                : (bool) config('git-deployment.contentops_ci_status.allow_in_progress', false),
            'max_age_minutes' => $request->query(
                'max_age_minutes',
                $request->input('max_age_minutes', config('git-deployment.contentops_ci_status.max_age_minutes'))
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentCiDispatchOptions(Request $request): array
    {
        $runMode = strtolower(trim((string) $request->input('run_mode', 'dry_run')));
        $dryRun = $runMode !== 'live' || $request->boolean('dry_run');

        return [
            'ref' => (string) $request->input('ref', $request->query('ref', '')),
            'branch' => (string) $request->input('branch', $request->query('branch', '')),
            'sha' => (string) $request->input('sha', $request->query('sha', '')),
            'workflow' => (string) $request->input(
                'workflow',
                config('git-deployment.contentops_ci_status.workflow_file', 'contentops-release-gate.yml')
            ),
            'domains' => (string) $request->input('domains', 'v3,page-v3'),
            'profile' => (string) $request->input('profile', 'ci'),
            'with_release_check' => $request->has('with_release_check')
                ? $request->boolean('with_release_check')
                : (bool) config('git-deployment.contentops_ci_status.dispatch_with_release_check', true),
            'strict' => $request->has('strict')
                ? $request->boolean('strict')
                : (bool) config('git-deployment.contentops_ci_status.dispatch_strict', true),
            'base_ref' => (string) $request->input('base_ref', $request->query('base_ref', '')),
            'head_ref' => (string) $request->input('head_ref', $request->query('head_ref', '')),
            'dry_run' => $dryRun,
            'force' => $request->boolean('force'),
        ];
    }

    /**
     * @param  array<string, mixed>  $contentPreview
     * @return array<string, mixed>
     */
    private function deploymentContentCiStatus(
        Request $request,
        ContentOpsCiStatusService $contentOpsCiStatusService,
        array $contentPreview,
        ?string $branch
    ): array {
        $headRef = trim((string) data_get($contentPreview, 'deployment.head_ref', ''));

        return $contentOpsCiStatusService->run(array_merge($this->contentCiStatusOptions($request), [
            'ref' => $headRef,
            'branch' => $branch,
            'require_success' => true,
        ]));
    }

    /**
     * @param  array<string, mixed>  $contentCiStatus
     */
    private function contentCiStatusBlocksDeployment(array $contentCiStatus): bool
    {
        return (bool) config('git-deployment.contentops_ci_status.required_for_deploy', false)
            && (bool) data_get($contentCiStatus, 'readiness.exit_would_fail', true);
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

    private function recentContentRuns(): \Illuminate\Support\Collection
    {
        try {
            return app(ContentOperationRunService::class)->latest([], 8);
        } catch (\Throwable $exception) {
            report($exception);

            return collect();
        }
    }

    private function recordSyncRepairRun(
        ContentOperationRunService $contentOperationRunService,
        mixed $run,
        array $result
    ): object {
        $status = match ((string) ($result['status'] ?? 'failed')) {
            'dry_run' => 'dry_run',
            'completed' => 'success',
            'partial' => 'partial',
            'blocked' => 'blocked',
            default => 'failed',
        };

        return match ($status) {
            'dry_run' => $contentOperationRunService->finishDryRun($run, $result),
            'blocked' => $contentOperationRunService->finishBlocked($run, $result),
            'partial' => $contentOperationRunService->finishPartial($run, $result),
            'success' => $contentOperationRunService->finishSuccess($run, $result),
            default => $contentOperationRunService->finishFailure($run, $result['error'] ?? [], $result),
        };
    }

    /**
     * @param  array<string, mixed>  $lease
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function lockBlockedContentSyncApply(array $lease, array $options, string $triggerSource): array
    {
        return [
            'domains_before' => [],
            'plan' => [
                'deployment_refs' => [
                    'current_deployed_ref' => $options['head_ref'] ?? null,
                ],
                'domains' => [],
                'summary' => [
                    'synced_domains' => 0,
                    'drifted_domains' => 0,
                    'uninitialized_domains' => 0,
                    'blocked' => 1,
                    'warnings' => 0,
                    'changed_packages' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                ],
                'content_plan' => null,
                'bootstrap' => [
                    'required_domains' => [],
                ],
                'error' => null,
            ],
            'apply' => [
                'executed' => false,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'changed_content_result' => null,
                'bootstrap' => [
                    'requested' => (bool) ($options['bootstrap_uninitialized'] ?? false),
                    'simulated' => [],
                    'applied' => [],
                ],
            ],
            'domains_after' => [],
            'status' => 'blocked',
            'artifacts' => [
                'report_path' => null,
            ],
            'lock' => $this->contentLockPayload($lease, 'deployment_sync_repair', $triggerSource),
            'error' => [
                'stage' => 'content_operation_lock',
                'reason' => (string) data_get($lease, 'error.reason', 'active_lock_present'),
                'message' => (string) data_get($lease, 'error.message', 'Content sync repair is blocked by the global content-operation lock.'),
                'lock' => $lease['lock'] ?? null,
            ],
        ];
    }

    private function heartbeatContentLock(ContentOperationLockService $contentOperationLockService, mixed $ownerToken): null
    {
        $contentOperationLockService->heartbeat(is_string($ownerToken) ? $ownerToken : null);

        return null;
    }

    /**
     * @param  array<string, mixed>  $lease
     * @return array<string, mixed>
     */
    private function contentLockPayload(array $lease, string $operationKind, string $triggerSource): array
    {
        return [
            'acquired' => (bool) ($lease['acquired'] ?? false),
            'status' => (string) ($lease['status'] ?? 'unknown'),
            'owner_token' => $lease['owner_token'] ?? null,
            'operation_kind' => $operationKind,
            'trigger_source' => (string) ($lease['lock']['trigger_source'] ?? $triggerSource),
            'domains' => (array) ($lease['lock']['domains'] ?? []),
            'content_operation_run_id' => $lease['lock']['content_operation_run_id'] ?? null,
            'acquired_at' => $lease['lock']['acquired_at'] ?? null,
            'heartbeat_at' => $lease['lock']['heartbeat_at'] ?? null,
            'expires_at' => $lease['lock']['expires_at'] ?? null,
            'warnings' => array_values((array) ($lease['warnings'] ?? [])),
            'takeover' => (array) ($lease['takeover'] ?? ['requested' => false, 'performed' => false]),
            'lock' => $lease['lock'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentOperationLockStatus(): array
    {
        try {
            return app(ContentOperationLockService::class)->status();
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'status' => 'unavailable',
                'lock' => null,
                'error' => $exception->getMessage(),
            ];
        }
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
