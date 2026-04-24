<?php

namespace App\Modules\GitDeployment\Http\Controllers;

use App\Models\ContentOperationRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Modules\GitDeployment\Models\BackupBranch;
use App\Modules\GitDeployment\Models\BranchUsageHistory;
use App\Modules\GitDeployment\Http\Concerns\ParsesDeploymentPaths;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\DeploymentContentLockService;
use App\Services\ContentDeployment\ContentSyncApplyService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentOperationReplayService;
use App\Services\ContentDeployment\ContentOperationsDoctorService;
use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use App\Services\ContentDeployment\ContentOpsCiStatusService;
use App\Services\ContentDeployment\ContentReleaseGateService;
use App\Services\ContentDeployment\ContentSyncPlanService;
use Symfony\Component\Process\Process;
use ZipArchive;

class DeploymentController extends BaseController
{
    use ParsesDeploymentPaths;

    private const BACKUP_FILE = 'deployment_backups.json';

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        return view('git-deployment::deployment.index', $this->indexViewData(
            session('deployment_content_preview'),
            session('deployment_content_apply'),
            session('deployment_content_sync_preview'),
            session('deployment_content_sync_apply'),
            session('deployment_content_doctor'),
            session('deployment_content_release_gate'),
            session('deployment_content_ci_status'),
            session('deployment_content_ci_dispatch')
        ));
    }

    public function contentPreview(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $preview = $this->resolveContentPreview($request, $changedContentDeploymentPreviewService);

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($preview);
        }

        return view('git-deployment::deployment.index', $this->indexViewData($preview));
    }

    public function contentApplyPreview(
        Request $request,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $contentApply = $changedContentDeploymentApplyService->run(
            $this->deploymentPreviewContext($request, 'standard'),
            array_merge($this->contentApplyOptions($request), [
                'requested' => true,
                'dry_run' => true,
            ])
        );

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($contentApply);
        }

        return view('git-deployment::deployment.index', $this->indexViewData(
            is_array($contentApply['preview'] ?? null) ? $contentApply['preview'] : null,
            $contentApply,
            session('deployment_content_sync_preview'),
            session('deployment_content_sync_apply')
        ));
    }

    public function contentSyncPreview(
        Request $request,
        ContentSyncPlanService $contentSyncPlanService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $preview = $contentSyncPlanService->run($this->contentSyncPlanOptions($request));

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($preview);
        }

        return view('git-deployment::deployment.index', $this->indexViewData(
            session('deployment_content_preview'),
            session('deployment_content_apply'),
            $preview,
            session('deployment_content_sync_apply')
        ));
    }

    public function contentDoctor(
        Request $request,
        ContentOperationsDoctorService $contentOperationsDoctorService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $doctor = $contentOperationsDoctorService->run($this->contentDoctorOptions($request));

        if ($request->boolean('write_report')) {
            $path = $contentOperationsDoctorService->writeReport($doctor);
            $doctor['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($doctor);
        }

        return view('git-deployment::deployment.index', $this->indexViewData(
            session('deployment_content_preview'),
            session('deployment_content_apply'),
            session('deployment_content_sync_preview'),
            session('deployment_content_sync_apply'),
            $doctor
        ));
    }

    public function contentReleaseGate(
        Request $request,
        ContentReleaseGateService $contentReleaseGateService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

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

        return view('git-deployment::deployment.index', $this->indexViewData(
            session('deployment_content_preview'),
            session('deployment_content_apply'),
            session('deployment_content_sync_preview'),
            session('deployment_content_sync_apply'),
            session('deployment_content_doctor'),
            $releaseGate
        ));
    }

    public function contentCiStatus(
        Request $request,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $ciStatus = $contentOpsCiStatusService->run($this->contentCiStatusOptions($request));

        if ($request->boolean('write_report')) {
            $path = $contentOpsCiStatusService->writeReport($ciStatus);
            $ciStatus['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($ciStatus);
        }

        return view('git-deployment::deployment.index', $this->indexViewData(
            session('deployment_content_preview'),
            session('deployment_content_apply'),
            session('deployment_content_sync_preview'),
            session('deployment_content_sync_apply'),
            session('deployment_content_doctor'),
            session('deployment_content_release_gate'),
            $ciStatus,
            session('deployment_content_ci_dispatch')
        ));
    }

    public function contentCiDispatch(
        Request $request,
        ContentOpsCiDispatchService $contentOpsCiDispatchService
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

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
            ->route('deployment.index')
            ->with('deployment', [
                'status' => $status === 'failed' ? 'error' : 'success',
                'message' => $message,
                'commands' => [],
                'branch' => data_get($dispatch, 'target.branch'),
            ])
            ->with('deployment_content_ci_dispatch', $dispatch)
            ->with('deployment_content_ci_status', session('deployment_content_ci_status'));
    }

    public function contentSync(
        Request $request,
        ContentSyncApplyService $contentSyncApplyService,
        ContentOperationRunService $contentOperationRunService,
        ContentOperationLockService $contentOperationLockService
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $options = $this->contentSyncApplyOptions($request);
        $run = null;
        $historyWarning = null;

        try {
            $run = $contentOperationRunService->start([
                'operation_kind' => 'deployment_sync_repair',
                'trigger_source' => 'deployment_ui',
                'domains' => $options['domains'] ?? ['v3', 'page-v3'],
                'head_ref' => $options['head_ref'] ?? null,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'strict' => (bool) ($options['strict'] ?? false),
                'with_release_check' => $options['with_release_check'] ?? null,
                'skip_release_check' => $options['skip_release_check'] ?? null,
                'bootstrap_uninitialized' => (bool) ($options['bootstrap_uninitialized'] ?? false),
                'operator_user_id' => $request->user()?->getAuthIdentifier(),
                'meta' => [
                    'deployment_mode' => 'standard',
                    'repair_flow' => true,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            $historyWarning = 'Content operation history could not be initialized: ' . $exception->getMessage();
        }

        $lockLease = $contentOperationLockService->acquire([
            'operation_kind' => 'deployment_sync_repair',
            'trigger_source' => 'deployment_ui',
            'domains' => $options['domains'] ?? ['v3', 'page-v3'],
            'content_operation_run_id' => $run?->id,
            'operator_user_id' => $request->user()?->getAuthIdentifier(),
            'meta' => [
                'deployment_mode' => 'standard',
                'repair_flow' => true,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
            ],
        ], $request->boolean('content_sync_takeover_stale_lock'));

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $contentSyncApply = $this->lockBlockedContentSyncApply($lockLease, $options, 'deployment_ui');
        } else {
            try {
                $ownerToken = $lockLease['owner_token'] ?? null;
                $contentSyncApply = $contentSyncApplyService->run($options + [
                    'heartbeat' => fn (): null => $this->heartbeatContentLock($contentOperationLockService, $ownerToken),
                ]);
            } finally {
                $contentOperationLockService->release($lockLease['owner_token'] ?? null);
            }

            $contentSyncApply['lock'] = $this->contentLockPayload($lockLease, 'deployment_sync_repair', 'deployment_ui');
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

    public function contentRuns(
        Request $request,
        ContentOperationRunService $contentOperationRunService
    ): View|\Illuminate\Http\JsonResponse {
        $runs = $contentOperationRunService->latest([
            'kind' => $request->query('kind'),
            'status' => $request->query('status'),
            'domains' => $request->query('domains'),
        ], max(1, (int) $request->query('limit', 20)));

        $payload = [
            'summary' => [
                'total' => $runs->count(),
                'by_status' => $runs->countBy('status')->sortKeys()->all(),
                'by_kind' => $runs->countBy('operation_kind')->sortKeys()->all(),
            ],
            'runs' => $runs->map(fn (ContentOperationRun $run): array => $this->mapContentOperationRun($run))->values()->all(),
            'artifacts' => [
                'report_path' => null,
            ],
        ];

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($payload);
        }

        return view('git-deployment::deployment.content-runs', [
            'historyPayload' => $payload,
            'supportsShell' => $this->supportsShellCommands(),
        ]);
    }

    public function contentRun(
        Request $request,
        ContentOperationRun $contentOperationRun,
        ContentOperationRunService $contentOperationRunService
    ): View|\Illuminate\Http\JsonResponse {
        $run = $contentOperationRunService->findWithArtifacts((int) $contentOperationRun->id);
        abort_if($run === null, 404);

        $payload = [
            'run' => $this->mapContentOperationRun($run, true),
        ];

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($payload);
        }

        return view('git-deployment::deployment.content-run-detail', [
            'historyPayload' => $payload,
            'contentReplay' => session('content_operation_replay'),
            'supportsShell' => $this->supportsShellCommands(),
        ]);
    }

    public function retryContentRun(
        Request $request,
        ContentOperationRun $contentOperationRun,
        ContentOperationReplayService $contentOperationReplayService
    ): RedirectResponse|\Illuminate\Http\JsonResponse {
        $runMode = strtolower(trim((string) $request->input('run_mode', 'dry_run')));
        $reuseOriginalMode = $request->boolean('reuse_original_mode');
        $force = $runMode === 'live';
        $dryRun = ! $force;

        $triggerSource = in_array((string) $contentOperationRun->trigger_source, ['deployment_ui', 'native_deployment_ui'], true)
            ? (string) $contentOperationRun->trigger_source
            : 'deployment_ui';

        $result = $contentOperationReplayService->run($contentOperationRun, [
            'dry_run' => $dryRun,
            'force' => $force,
            'write_report' => true,
            'strict' => $request->boolean('strict'),
            'allow_success' => $request->boolean('allow_success'),
            'reuse_original_mode' => $reuseOriginalMode,
            'takeover_stale_lock' => $request->boolean('takeover_stale_lock'),
            'trigger_source' => $triggerSource,
            'operator_user_id' => $request->user()?->getAuthIdentifier(),
        ]);

        if ($request->expectsJson() || $request->boolean('json')) {
            return response()->json($result);
        }

        return redirect()
            ->route('deployment.content-runs.show', $contentOperationRun->id)
            ->with('content_operation_replay', $result);
    }

    public function deploy(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService,
        DeploymentContentLockService $deploymentContentLockService,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): RedirectResponse
    {
        $branch = $this->sanitizeBranchName((string) $request->input('branch', 'main')) ?: 'main';

        $autoPushBranch = $request->input('auto_push_branch', '');
        $autoPushBranch = $this->sanitizeBranchName((string) $autoPushBranch);
        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        $previewOptions = $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request);
        $previewOptions['content_apply_requested'] = $contentApplyRequested;

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'standard',
            'source_kind' => 'deploy',
            'branch' => $branch,
        ], $previewOptions);

        if ($changedContentDeploymentPreviewService->gateBlocks($contentPreview)) {
            return $this->redirectWithFeedback(
                'error',
                $this->blockedDeploymentMessage('Деплой', $contentPreview, $changedContentDeploymentPreviewService),
                [],
                $branch,
                $contentPreview
            );
        }

        if ((bool) config('git-deployment.contentops_ci_status.required_for_deploy', false)) {
            $contentCiStatus = $this->deploymentContentCiStatus($request, $contentOpsCiStatusService, $contentPreview, $branch);

            if ($this->contentCiStatusBlocksDeployment($contentCiStatus)) {
                return $this->redirectWithFeedback(
                    'error',
                    'Деплой зупинено ContentOps CI gate до початку git-оновлення. ' . (string) data_get($contentCiStatus, 'readiness.message', 'ContentOps CI status is not deploy-ready.'),
                    [],
                    $branch,
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
                    'trigger_source' => 'deployment_ui',
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
                    'Деплой зупинено content-operation lock gate до початку git-оновлення.',
                    [],
                    $branch,
                    $contentPreview,
                    $contentApply
                );
            }
        }

        try {
        $repoPath = base_path();
        $commandsOutput = [];

        $deploymentContentLockService->heartbeat($contentLockReservation);
        $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $currentCommit = trim($currentCommitProcess->getOutput());
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

        if (! $currentCommitProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося зчитати поточний коміт.', $commandsOutput, $branch);
        }

        $backupStored = false;
        if ($currentCommit !== '') {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
            $backupStored = true;
        }

        $deploymentContentLockService->heartbeat($contentLockReservation);
        $fetchProcess = $this->runCommand(['git', 'fetch', 'origin'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git fetch origin', $fetchProcess);

        if (! $fetchProcess->isSuccessful()) {
            return $this->redirectWithFeedback(
                'error',
                'Команда "git fetch" завершилась з помилкою.',
                $commandsOutput,
                $branch,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, 'Команда "git fetch" завершилась з помилкою.', array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        $deploymentContentLockService->heartbeat($contentLockReservation);
        $resetProcess = $this->runCommand(['git', 'reset', '--hard', "origin/{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git reset --hard origin/{$branch}", $resetProcess);

        if (! $resetProcess->isSuccessful()) {
            return $this->redirectWithFeedback(
                'error',
                'Не вдалося оновити код до останнього коміту.',
                $commandsOutput,
                $branch,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, 'Не вдалося оновити код до останнього коміту.', array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        $deploymentContentLockService->heartbeat($contentLockReservation);
        $cleanProcess = $this->runCommand(['git', 'clean', '-fd'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git clean -fd', $cleanProcess);

        if (! $cleanProcess->isSuccessful()) {
            return $this->redirectWithFeedback(
                'error',
                'Не вдалося видалити локальні файли, яких немає в репозиторії.',
                $commandsOutput,
                $branch,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, 'Не вдалося видалити локальні файли, яких немає в репозиторії.', array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branch,
            'deploy',
            'Оновлення сайту до останнього стану гілки'
        );

        $contentApply = null;
        $message = 'Сайт успішно оновлено до останнього стану гілки.';
        if (! $backupStored) {
            $message .= ' Увага: резервну копію не збережено.';
        }

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
            $commandsOutput[] = $this->formatContentApplyCommand($contentApply);

            if (($contentApply['status'] ?? null) === 'content_apply_failed' || is_array($contentApply['error'] ?? null)) {
                $message .= ' Код оновлено, але changed-content apply завершився з помилкою. Глобальний rollback не виконувався.';

                return $this->redirectWithFeedback(
                    'error',
                    $message,
                    $commandsOutput,
                    $branch,
                    $contentPreview,
                    $contentApply
                );
            }

            $message .= ' Changed content apply виконано успішно.';
        } else {
            $message .= ' Changed content apply пропущено оператором.';
        }

        // Auto-push to branch if specified
        if ($autoPushBranch !== '') {
            // Get current commit
            $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
            $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

            if ($currentCommitProcess->isSuccessful()) {
                $currentCommit = trim($currentCommitProcess->getOutput());

                // Check if branch exists locally
                $branchListProcess = $this->runCommand(['git', 'branch', '--list', $autoPushBranch], $repoPath);
                $commandsOutput[] = $this->formatProcess("git branch --list {$autoPushBranch}", $branchListProcess);

                $branchExists = trim($branchListProcess->getOutput()) !== '';

                if (! $branchExists) {
                    // Create branch locally if it doesn't exist
                    $createProcess = $this->runCommand(['git', 'branch', $autoPushBranch, $currentCommit], $repoPath);
                    $commandsOutput[] = $this->formatProcess("git branch {$autoPushBranch} {$currentCommit}", $createProcess);

                    if (! $createProcess->isSuccessful()) {
                        $message .= " Проте не вдалося створити гілку {$autoPushBranch} локально.";
                        return $this->redirectWithFeedback('success', $message, $commandsOutput, $branch, $contentPreview, $contentApply);
                    }
                }

                // Push branch to remote
                $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', $autoPushBranch], $repoPath);
                $commandsOutput[] = $this->formatProcess("git push --force origin {$autoPushBranch}", $pushProcess);

                if ($pushProcess->isSuccessful()) {
                    $message .= " Поточний стан також запушено на origin/{$autoPushBranch}.";
                    
                    // Track auto-push usage
                    BranchUsageHistory::trackUsage(
                        $autoPushBranch,
                        'auto_push',
                        "Автоматичний пуш після оновлення на віддалену гілку {$autoPushBranch}"
                    );

                    // Update backup branch record
                    BackupBranch::updateOrCreate(
                        ['name' => $autoPushBranch],
                        [
                            'commit_hash' => $currentCommit,
                            'pushed_at' => now(),
                        ]
                    );
                } else {
                    $message .= " Проте не вдалося запушити на origin/{$autoPushBranch}.";
                }
            }
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput, $branch, $contentPreview, $contentApply);
        } finally {
            $deploymentContentLockService->release($contentLockReservation);
        }
    }

    public function deployPartial(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

        $pathsInput = $request->input('paths', []);

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        // Парсимо та валідуємо шляхи
        $parsed = $this->parseAndValidatePaths($pathsInput);
        $paths = $parsed['valid'];
        $pathErrors = $parsed['errors'];

        if ($paths === []) {
            $errorMessage = 'Не вказано жодного валідного шляху для часткового деплою.';
            if ($pathErrors !== []) {
                $errorMessage .= ' Помилки: ' . implode('; ', $pathErrors);
            }
            return $this->redirectWithFeedback('error', $errorMessage, [], $branch);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        // Зберігаємо поточний коміт для резервної копії
        $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $currentCommit = trim($currentCommitProcess->getOutput());
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

        if (! $currentCommitProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося зчитати поточний коміт.', $commandsOutput, $branch);
        }

        if ($currentCommit !== '') {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
        }

        // Fetch origin
        $fetchProcess = $this->runCommand(['git', 'fetch', 'origin'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git fetch origin', $fetchProcess);

        if (! $fetchProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Команда "git fetch" завершилась з помилкою.', $commandsOutput, $branch);
        }

        // Визначаємо remote SHA
        $remoteShaProcess = $this->runCommand(['git', 'rev-parse', "origin/{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git rev-parse origin/{$branch}", $remoteShaProcess);

        if (! $remoteShaProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', "Гілку origin/{$branch} не знайдено.", $commandsOutput, $branch);
        }

        $remoteSha = trim($remoteShaProcess->getOutput());

        // Створюємо тимчасову директорію
        $tmpDir = storage_path('app/partial_deploy_' . Str::random(8));
        File::ensureDirectoryExists($tmpDir);

        // Перевіряємо які шляхи існують у remote
        $existingPaths = [];
        $missingPaths = [];

        foreach ($paths as $path) {
            $checkProcess = $this->runCommand(['git', 'cat-file', '-e', "origin/{$branch}:{$path}"], $repoPath);
            if ($checkProcess->isSuccessful()) {
                $existingPaths[] = $path;
            } else {
                $missingPaths[] = $path;
            }
        }

        $commandsOutput[] = [
            'command' => 'partial: перевірка шляхів у remote',
            'successful' => true,
            'output' => 'Існують: ' . (empty($existingPaths) ? '—' : implode(', ', $existingPaths)) . 
                        "\nВідсутні: " . (empty($missingPaths) ? '—' : implode(', ', $missingPaths)),
        ];

        // Якщо є шляхи для завантаження — створюємо архів
        $extractDir = $tmpDir . '/extract';
        File::ensureDirectoryExists($extractDir);

        if (! empty($existingPaths)) {
            $archivePath = $tmpDir . '/partial.zip';
            $archiveCommand = array_merge(
                ['git', 'archive', '--format=zip', "--output={$archivePath}", "origin/{$branch}"],
                $existingPaths
            );

            $archiveProcess = $this->runCommand($archiveCommand, $repoPath);
            $commandsOutput[] = $this->formatProcess('git archive ...', $archiveProcess);

            if (! $archiveProcess->isSuccessful()) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', 'Не вдалося створити архів вказаних шляхів.', $commandsOutput, $branch);
            }

            // Розпаковуємо архів
            if (! class_exists(ZipArchive::class)) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', 'PHP-клас ZipArchive недоступний на сервері. Перевірте чи встановлено та увімкнено розширення zip.', $commandsOutput, $branch);
            }

            $zip = new ZipArchive();
            $openResult = $zip->open($archivePath);
            if ($openResult !== true) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', "Не вдалося відкрити архів (код помилки: {$openResult}).", $commandsOutput, $branch);
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $commandsOutput[] = [
                'command' => 'partial: розпакування архіву',
                'successful' => true,
                'output' => "Розпаковано до {$extractDir}",
            ];
        }

        // Застосовуємо зміни до base_path()
        foreach ($paths as $path) {
            $localPath = $repoPath . '/' . $path;
            $sourcePath = $extractDir . '/' . $path;

            // Видаляємо локальний шлях (якщо існує)
            if (File::exists($localPath)) {
                if (File::isDirectory($localPath)) {
                    File::deleteDirectory($localPath);
                } else {
                    File::delete($localPath);
                }
                $commandsOutput[] = [
                    'command' => "partial: delete {$path}",
                    'successful' => true,
                    'output' => 'OK',
                ];
            }

            // Копіюємо з архіву (якщо шлях існує у remote)
            if (in_array($path, $existingPaths, true) && File::exists($sourcePath)) {
                File::ensureDirectoryExists(dirname($localPath));
                if (File::isDirectory($sourcePath)) {
                    File::copyDirectory($sourcePath, $localPath);
                } else {
                    File::copy($sourcePath, $localPath);
                }
                $commandsOutput[] = [
                    'command' => "partial: copy {$path}",
                    'successful' => true,
                    'output' => 'OK',
                ];
            }
        }

        // Видаляємо тимчасову директорію
        File::deleteDirectory($tmpDir);

        // Track branch usage
        $pathsList = implode(', ', $paths);
        BranchUsageHistory::trackUsage(
            $branch,
            'partial_deploy',
            "Частковий деплой шляхів: {$pathsList}",
            $paths
        );

        $message = "Частковий деплой виконано успішно з гілки {$branch}. Оновлено шляхи: {$pathsList}.";
        if ($pathErrors !== []) {
            $message .= ' Попередження: ' . implode('; ', $pathErrors);
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput, $branch);
    }

    public function pushCurrent(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'master');
        $branch = Str::of($branch)->trim()->value() ?: 'master';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'master';

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        $repoPath = base_path();
        $commandsOutput = [];

        $currentBranchProcess = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git rev-parse --abbrev-ref HEAD', $currentBranchProcess);

        if (! $currentBranchProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточну гілку.', $commandsOutput);
        }

        $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', "HEAD:{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push --force origin HEAD:{$branch}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити поточний стан на віддалену гілку.', $commandsOutput);
        }

        $currentBranch = trim($currentBranchProcess->getOutput());

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branch,
            'push',
            "Пуш поточного стану на віддалену гілку з {$currentBranch}"
        );

        return $this->redirectWithFeedback(
            'success',
            "Поточний стан гілки {$currentBranch} запушено на origin/{$branch}.",
            $commandsOutput
        );
    }

    public function rollback(
        Request $request,
        ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        ChangedContentDeploymentApplyService $changedContentDeploymentApplyService,
        DeploymentContentLockService $deploymentContentLockService,
        ContentOpsCiStatusService $contentOpsCiStatusService
    ): RedirectResponse
    {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $commit = $request->input('commit');
        $repoPath = base_path();

        $backups = $this->loadBackups();
        $selected = collect($backups)->firstWhere('commit', $commit);

        if (! $selected) {
            return $this->redirectWithFeedback('error', 'Обраного коміту немає в історії резервних копій.', []);
        }

        $contentApplyRequested = $this->contentApplyRequested($request);
        $contentApplyOptions = $this->contentApplyOptions($request);
        $previewOptions = $contentApplyRequested
            ? $this->contentPreviewOptionsFromApplyOptions($contentApplyOptions)
            : $this->contentPreviewOptions($request);
        $previewOptions['content_apply_requested'] = $contentApplyRequested;

        $contentPreview = $changedContentDeploymentPreviewService->preview([
            'mode' => 'standard',
            'source_kind' => 'backup_restore',
            'commit' => (string) $selected['commit'],
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
                    'Відкат зупинено ContentOps CI gate до початку git restore. ' . (string) data_get($contentCiStatus, 'readiness.message', 'ContentOps CI status is not deploy-ready.'),
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
                    'trigger_source' => 'deployment_ui',
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
                    'Відкат зупинено content-operation lock gate до початку git restore.',
                    [],
                    null,
                    $contentPreview,
                    $contentApply
                );
            }
        }

        try {
        $commandsOutput = [];
        $deploymentContentLockService->heartbeat($contentLockReservation);
        $resetProcess = $this->runCommand(['git', 'reset', '--hard', $selected['commit']], $repoPath);
        $commandsOutput[] = $this->formatProcess("git reset --hard {$selected['commit']}", $resetProcess);

        if (! $resetProcess->isSuccessful()) {
            return $this->redirectWithFeedback(
                'error',
                'Не вдалося виконати відкат до резервного коміту.',
                $commandsOutput,
                null,
                $contentPreview,
                $contentApplyRequested
                    ? $changedContentDeploymentApplyService->deploymentFailedResult($contentPreview, 'Не вдалося виконати відкат до резервного коміту.', array_merge($contentApplyOptions, $deploymentContentLockService->applyOptions($contentLockReservation), ['requested' => true, 'dry_run' => false]))
                    : null
            );
        }

        $contentApply = null;
        $message = 'Виконано відкат до вибраного робочого стану.';

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
            $commandsOutput[] = $this->formatContentApplyCommand($contentApply);

            if (($contentApply['status'] ?? null) === 'content_apply_failed' || is_array($contentApply['error'] ?? null)) {
                $message .= ' Restore виконано, але changed-content apply завершився з помилкою. Глобальний rollback не виконувався.';

                return $this->redirectWithFeedback('error', $message, $commandsOutput, null, $contentPreview, $contentApply);
            }

            $message .= ' Changed content apply виконано успішно.';
        } else {
            $message .= ' Changed content apply пропущено оператором.';
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput, null, $contentPreview, $contentApply);
        } finally {
            $deploymentContentLockService->release($contentLockReservation);
        }
    }

    public function createBackupBranch(Request $request): RedirectResponse
    {
        $branchName = Str::of($request->input('branch_name', ''))->trim()->value();
        $branchName = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branchName);

        if ($redirect = $this->redirectIfShellUnavailable($branchName ?: null)) {
            return $redirect;
        }

        if ($branchName === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки для резервного бекапу.', []);
        }

        $commitInput = Str::of($request->input('commit', 'current'))->trim()->value();
        $useCurrentHead = $commitInput === '' || $commitInput === 'current';

        if (! $useCurrentHead && ! preg_match('/^[0-9a-f]{7,40}$/i', $commitInput)) {
            return $this->redirectWithFeedback('error', 'Невірний формат коміту для створення гілки.', []);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        if ($useCurrentHead) {
            $revParseProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
            $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $revParseProcess);

            if (! $revParseProcess->isSuccessful()) {
                return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', $commandsOutput);
            }

            $resolvedCommit = trim($revParseProcess->getOutput());
        } else {
            $resolvedCommit = $commitInput;
        }

        $branchListProcess = $this->runCommand(['git', 'branch', '--list', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$branchName}", $branchListProcess);

        if (! $branchListProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявність існуючої гілки.', $commandsOutput);
        }

        if (trim($branchListProcess->getOutput()) !== '') {
            return $this->redirectWithFeedback('error', 'Гілка з такою назвою вже існує.', $commandsOutput);
        }

        $createProcess = $this->runCommand(['git', 'branch', $branchName, $resolvedCommit], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch {$branchName} {$resolvedCommit}", $createProcess);

        if (! $createProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося створити резервну гілку.', $commandsOutput);
        }

        BackupBranch::updateOrCreate(
            ['name' => $branchName],
            [
                'commit_hash' => $resolvedCommit,
                'pushed_at' => null,
            ]
        );

        return $this->redirectWithFeedback('success', 'Резервну гілку створено.', $commandsOutput);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        if ($redirect = $this->redirectIfShellUnavailable($backupBranch->name)) {
            return $redirect;
        }

        $repoPath = base_path();
        $commandsOutput = [];

        $branchCheck = $this->runCommand(['git', 'branch', '--list', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$backupBranch->name}", $branchCheck);

        if (! $branchCheck->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявність резервної гілки.', $commandsOutput);
        }

        if (trim($branchCheck->getOutput()) === '') {
            return $this->redirectWithFeedback('error', 'Резервну гілку не знайдено.', $commandsOutput);
        }

        $pushProcess = $this->runCommand(['git', 'push', 'origin', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push origin {$backupBranch->name}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити резервну гілку.', $commandsOutput);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', 'Резервну гілку надіслано на віддалений репозиторій.', $commandsOutput);
    }

    public function createAndPushBranch(Request $request): RedirectResponse
    {
        $branchName = Str::of($request->input('quick_branch_name', ''))->trim()->value();
        $branchName = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branchName);

        if ($redirect = $this->redirectIfShellUnavailable($branchName ?: null)) {
            return $redirect;
        }

        if ($branchName === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки.', []);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        // Get current commit
        $revParseProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $revParseProcess);

        if (! $revParseProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', $commandsOutput);
        }

        $currentCommit = trim($revParseProcess->getOutput());

        // Check if branch exists locally
        $branchListProcess = $this->runCommand(['git', 'branch', '--list', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$branchName}", $branchListProcess);

        $branchExists = trim($branchListProcess->getOutput()) !== '';

        if (! $branchExists) {
            // Create branch
            $createProcess = $this->runCommand(['git', 'branch', $branchName, $currentCommit], $repoPath);
            $commandsOutput[] = $this->formatProcess("git branch {$branchName} {$currentCommit}", $createProcess);

            if (! $createProcess->isSuccessful()) {
                return $this->redirectWithFeedback('error', 'Не вдалося створити гілку.', $commandsOutput);
            }
        }

        // Push branch to remote
        $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push --force origin {$branchName}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити гілку на віддалений репозиторій.', $commandsOutput);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branchName,
            'create_and_push',
            'Швидке створення та пуш гілки з поточним станом сайту'
        );

        // Update or create backup branch record
        BackupBranch::updateOrCreate(
            ['name' => $branchName],
            [
                'commit_hash' => $currentCommit,
                'pushed_at' => now(),
            ]
        );

        $action = $branchExists ? 'оновлено та запушено' : 'створено та запушено';
        return $this->redirectWithFeedback(
            'success',
            "Гілку {$branchName} успішно {$action} на віддалений репозиторій.",
            $commandsOutput
        );
    }

    private function redirectWithFeedback(
        string $status,
        string $message,
        array $commandsOutput,
        ?string $branch = null,
        ?array $contentPreview = null,
        ?array $contentApply = null,
        ?array $contentSyncPreview = null,
        ?array $contentSyncApply = null,
        ?array $contentCiStatus = null
    ): RedirectResponse
    {
        $redirect = redirect()
            ->route('deployment.index')
            ->with('deployment', [
                'status' => $status,
                'message' => $message,
                'commands' => $commandsOutput,
                'branch' => $branch,
            ]);

        if ($contentPreview !== null) {
            $redirect->with('deployment_content_preview', $contentPreview);
        }

        if ($contentApply !== null) {
            $redirect->with('deployment_content_apply', $contentApply);
        }

        if ($contentSyncPreview !== null) {
            $redirect->with('deployment_content_sync_preview', $contentSyncPreview);
        }

        if ($contentSyncApply !== null) {
            $redirect->with('deployment_content_sync_apply', $contentSyncApply);
        }

        if ($contentCiStatus !== null) {
            $redirect->with('deployment_content_ci_status', $contentCiStatus);
        }

        return $redirect;
    }

    private function loadBackups(): array
    {
        $path = $this->ensureBackupFile();

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeBackup(array $backup): void
    {
        $path = $this->ensureBackupFile();
        $backups = $this->loadBackups();
        $backups[] = $backup;

        File::put($path, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
        $feedback = session('deployment');
        $backupBranches = BackupBranch::query()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
        $branchProcess = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], base_path());
        $currentBranch = $branchProcess->isSuccessful()
            ? trim($branchProcess->getOutput())
            : null;

        return [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $currentBranch,
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
            $this->deploymentPreviewContext($request, 'standard'),
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

        return $actionLabel . ' зупинено content-aware safety gate до початку git-оновлення. '
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
     * @return array<string, mixed>
     */
    private function formatContentApplyCommand(array $contentApply): array
    {
        $applyResult = (array) ($contentApply['content_apply']['result'] ?? []);
        $summary = (array) ($applyResult['plan']['summary'] ?? []);
        $preflight = (array) ($applyResult['preflight']['summary'] ?? []);

        $output = [
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
            $output[] = 'report=' . (string) $contentApply['artifacts']['report_path'];
        }

        if (! empty($contentApply['error']['message'])) {
            $output[] = 'error=' . (string) $contentApply['error']['message'];
        }

        return [
            'command' => 'content:apply-changed ' . ((bool) ($contentApply['content_apply']['dry_run'] ?? false) ? '--dry-run' : '--force'),
            'successful' => empty($contentApply['error']),
            'output' => implode(PHP_EOL, $output),
        ];
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
    ): ContentOperationRun {
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

    /**
     * @return array<string, mixed>
     */
    private function mapContentOperationRun(ContentOperationRun $run, bool $withPayload = false): array
    {
        $mapped = [
            'id' => $run->id,
            'operation_kind' => $run->operation_kind,
            'trigger_source' => $run->trigger_source,
            'domains' => is_array($run->domains) ? $run->domains : [],
            'base_ref' => $run->base_ref,
            'head_ref' => $run->head_ref,
            'base_refs_by_domain' => is_array($run->base_refs_by_domain) ? $run->base_refs_by_domain : [],
            'replayed_from_run_id' => $run->replayed_from_run_id,
            'dry_run' => (bool) $run->dry_run,
            'status' => $run->status,
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'summary' => is_array($run->summary) ? $run->summary : [],
            'payload_json_path' => $run->payload_json_path ? storage_path('app/' . $run->payload_json_path) : null,
            'report_path' => $run->report_path ? storage_path('app/' . $run->report_path) : null,
            'error_excerpt' => $run->error_excerpt,
            'meta' => is_array($run->meta) ? $run->meta : [],
            'replay_ids' => method_exists($run, 'relationLoaded') && $run->relationLoaded('replays')
                ? $run->replays->pluck('id')->values()->all()
                : [],
        ];

        if ($withPayload) {
            $mapped['payload'] = is_array($run->payload_json ?? null) ? $run->payload_json : null;
        }

        return $mapped;
    }

    private function redirectIfShellUnavailable(?string $branch = null): ?RedirectResponse
    {
        if ($this->supportsShellCommands()) {
            return null;
        }

        $logs = $branch !== null
            ? ['Shell commands are disabled. Перемкніться на API режим.']
            : [];

        return redirect()
            ->route('deployment.native.index')
            ->with('deployment_native', [
                'status' => 'error',
                'message' => 'Режим через SSH недоступний на цьому сервері. Спробуйте альтернативний режим через GitHub API.',
                'logs' => $logs,
                'branch' => $branch,
            ]);
    }

    private function supportsShellCommands(): bool
    {
        return function_exists('proc_open');
    }

    private function sanitizeBranchName(string $branch): string
    {
        $normalized = Str::of($branch)->trim()->value();
        $sanitized = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $normalized);

        return $sanitized !== null && $sanitized !== '' ? $sanitized : $normalized;
    }

    private function runCommand(array $command, string $workingDirectory): Process
    {
        $process = new Process($command, $workingDirectory);
        $process->run();

        return $process;
    }

    private function formatProcess(string $command, Process $process): array
    {
        return [
            'command' => $command,
            'successful' => $process->isSuccessful(),
            'output' => trim($process->getErrorOutput() . $process->getOutput()),
        ];
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
