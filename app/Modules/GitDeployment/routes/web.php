<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GitDeployment\Http\Controllers\DeploymentController;
use App\Modules\GitDeployment\Http\Controllers\NativeDeploymentController;

Route::middleware(['web', 'auth.admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/deployment', [DeploymentController::class, 'index'])->name('deployment.index');
        Route::post('/deployment/deploy', [DeploymentController::class, 'deploy'])->name('deployment.deploy');
        Route::post('/deployment/push-current', [DeploymentController::class, 'pushCurrent'])->name('deployment.push-current');
        Route::post('/deployment/backup-branch', [DeploymentController::class, 'createBackupBranch'])->name('deployment.backup-branch');
        Route::post('/deployment/backup-branches/{backupBranch}/push', [DeploymentController::class, 'pushBackupBranch'])->name('deployment.backup-branch.push');
        Route::post('/deployment/quick-branch', [DeploymentController::class, 'createAndPushBranch'])->name('deployment.quick-branch');

        Route::prefix('deployment/native')->name('deployment.native.')->group(function () {
            Route::get('/', [NativeDeploymentController::class, 'index'])->name('index');
            Route::post('/deploy', [NativeDeploymentController::class, 'deploy'])->name('deploy');
            Route::post('/push-current', [NativeDeploymentController::class, 'pushCurrent'])->name('push-current');
            Route::post('/rollback', [NativeDeploymentController::class, 'rollback'])->name('rollback');
            Route::post('/backup-branch', [NativeDeploymentController::class, 'createBackupBranch'])->name('backup-branch');
            Route::post('/backup-branches/{backupBranch}/push', [NativeDeploymentController::class, 'pushBackupBranch'])->name('backup-branch.push');
            Route::post('/quick-branch', [NativeDeploymentController::class, 'createAndPushBranch'])->name('quick-branch');
        });
    });
