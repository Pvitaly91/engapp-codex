<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GitDeployment\Http\Controllers\DeploymentController;
use App\Modules\GitDeployment\Http\Controllers\NativeDeploymentController;

Route::middleware(['web', 'auth.admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/deployment', [DeploymentController::class, 'index'])->name('deployment.index');
        Route::get('/deployment/content-preview', [DeploymentController::class, 'contentPreview'])->name('deployment.content-preview');
        Route::get('/deployment/content-apply-preview', [DeploymentController::class, 'contentApplyPreview'])->name('deployment.content-apply-preview');
        Route::get('/deployment/content-sync-preview', [DeploymentController::class, 'contentSyncPreview'])->name('deployment.content-sync-preview');
        Route::get('/deployment/content-doctor', [DeploymentController::class, 'contentDoctor'])->name('deployment.content-doctor');
        Route::get('/deployment/content-release-gate', [DeploymentController::class, 'contentReleaseGate'])->name('deployment.content-release-gate');
        Route::get('/deployment/content-ci-status', [DeploymentController::class, 'contentCiStatus'])->name('deployment.content-ci-status');
        Route::post('/deployment/content-ci-dispatch', [DeploymentController::class, 'contentCiDispatch'])->name('deployment.content-ci-dispatch');
        Route::get('/deployment/content-runs', [DeploymentController::class, 'contentRuns'])->name('deployment.content-runs');
        Route::get('/deployment/content-runs/{contentOperationRun}', [DeploymentController::class, 'contentRun'])->name('deployment.content-runs.show');
        Route::post('/deployment/content-runs/{contentOperationRun}/retry', [DeploymentController::class, 'retryContentRun'])->name('deployment.content-runs.retry');
        Route::post('/deployment/content-sync', [DeploymentController::class, 'contentSync'])->name('deployment.content-sync');
        Route::post('/deployment/deploy', [DeploymentController::class, 'deploy'])->name('deployment.deploy');
        Route::post('/deployment/deploy-partial', [DeploymentController::class, 'deployPartial'])->name('deployment.deploy-partial');
        Route::post('/deployment/push-current', [DeploymentController::class, 'pushCurrent'])->name('deployment.push-current');
        Route::post('/deployment/rollback', [DeploymentController::class, 'rollback'])->name('deployment.rollback');
        Route::post('/deployment/backup-branch', [DeploymentController::class, 'createBackupBranch'])->name('deployment.backup-branch');
        Route::post('/deployment/backup-branches/{backupBranch}/push', [DeploymentController::class, 'pushBackupBranch'])->name('deployment.backup-branch.push');
        Route::post('/deployment/quick-branch', [DeploymentController::class, 'createAndPushBranch'])->name('deployment.quick-branch');

        Route::prefix('deployment/native')->name('deployment.native.')->group(function () {
            Route::get('/', [NativeDeploymentController::class, 'index'])->name('index');
            Route::get('/content-preview', [NativeDeploymentController::class, 'contentPreview'])->name('content-preview');
            Route::get('/content-apply-preview', [NativeDeploymentController::class, 'contentApplyPreview'])->name('content-apply-preview');
            Route::get('/content-sync-preview', [NativeDeploymentController::class, 'contentSyncPreview'])->name('content-sync-preview');
            Route::get('/content-doctor', [NativeDeploymentController::class, 'contentDoctor'])->name('content-doctor');
            Route::get('/content-release-gate', [NativeDeploymentController::class, 'contentReleaseGate'])->name('content-release-gate');
            Route::get('/content-ci-status', [NativeDeploymentController::class, 'contentCiStatus'])->name('content-ci-status');
            Route::post('/content-ci-dispatch', [NativeDeploymentController::class, 'contentCiDispatch'])->name('content-ci-dispatch');
            Route::post('/content-sync', [NativeDeploymentController::class, 'contentSync'])->name('content-sync');
            Route::post('/deploy', [NativeDeploymentController::class, 'deploy'])->name('deploy');
            Route::post('/deploy-partial', [NativeDeploymentController::class, 'deployPartial'])->name('deploy-partial');
            Route::post('/push-current', [NativeDeploymentController::class, 'pushCurrent'])->name('push-current');
            Route::post('/rollback', [NativeDeploymentController::class, 'rollback'])->name('rollback');
            Route::post('/backup-branch', [NativeDeploymentController::class, 'createBackupBranch'])->name('backup-branch');
            Route::post('/backup-branches/{backupBranch}/push', [NativeDeploymentController::class, 'pushBackupBranch'])->name('backup-branch.push');
            Route::post('/quick-branch', [NativeDeploymentController::class, 'createAndPushBranch'])->name('quick-branch');
        });
    });
