<?php

namespace App\Modules\SeedRunsV2\Http\Livewire;

use App\Modules\SeedRunsV2\Services\SeedRunsService;
use Livewire\Component;

class SeedRunsIndex extends Component
{
    public bool $tableExists = false;
    public $pendingSeederHierarchy = [];
    public $executedSeederHierarchy = [];
    public $recentSeedRunOrdinals = [];
    
    public string $searchQuery = '';
    public string $statusMessage = '';
    public string $statusType = 'success';
    
    public bool $showConfirmModal = false;
    public string $confirmAction = '';
    public string $confirmMessage = '';
    public $confirmData = null;
    
    public bool $showFileModal = false;
    public string $fileModalClassName = '';
    public string $fileModalDisplayName = '';
    public string $fileModalPath = '';
    public string $fileModalContents = '';
    public string $fileModalLastModified = '';
    
    public bool $showCreateModal = false;
    public string $newSeederClassName = '';
    public string $newSeederFolder = '';
    public string $newSeederContents = '';
    public array $seederFolders = [];
    
    public array $selectedPendingSeeders = [];
    public array $selectedExecutedSeeders = [];

    public int $pendingTreeVersion = 0;
    public int $executedTreeVersion = 0;
    
    protected SeedRunsService $seedRunsService;

    public function boot(SeedRunsService $seedRunsService): void
    {
        $this->seedRunsService = $seedRunsService;
    }

    public function mount(): void
    {
        $this->refreshOverview();
    }

    public function refreshOverview(): void
    {
        $overview = $this->seedRunsService->assembleSeedRunOverview();
        
        $this->tableExists = $overview['tableExists'];
        $this->pendingSeederHierarchy = $overview['pendingSeederHierarchy']->toArray();
        $this->executedSeederHierarchy = $overview['executedSeederHierarchy']->toArray();
        $this->recentSeedRunOrdinals = $overview['recentSeedRunOrdinals']->toArray();

        $this->pendingTreeVersion++;
        $this->executedTreeVersion++;

        // Force a full component refresh so Livewire updates all DOM fragments
        $this->emitSelf('$refresh');
    }

    public function runSeeder(string $className): void
    {
        $result = $this->seedRunsService->runSeeder($className);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmRunSeeder(string $className, string $displayName): void
    {
        $this->confirmAction = 'runSeeder';
        $this->confirmMessage = __('Виконати сидер «:name»?', ['name' => $displayName]);
        $this->confirmData = $className;
        $this->showConfirmModal = true;
    }

    public function runMissingSeeders(): void
    {
        $result = $this->seedRunsService->runMissingSeeders();
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        $this->refreshOverview();
    }

    public function confirmRunMissing(): void
    {
        $this->confirmAction = 'runMissingSeeders';
        $this->confirmMessage = __('Виконати всі невиконані сидери?');
        $this->confirmData = null;
        $this->showConfirmModal = true;
    }

    public function markAsExecuted(string $className): void
    {
        $result = $this->seedRunsService->markAsExecuted($className);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmMarkAsExecuted(string $className, string $displayName): void
    {
        $this->confirmAction = 'markAsExecuted';
        $this->confirmMessage = __('Позначити сидер «:name» як виконаний?', ['name' => $displayName]);
        $this->confirmData = $className;
        $this->showConfirmModal = true;
    }

    public function deleteSeedRun(int $seedRunId): void
    {
        $result = $this->seedRunsService->destroySeedRun($seedRunId);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmDeleteSeedRun(int $seedRunId, string $displayName): void
    {
        $this->confirmAction = 'deleteSeedRun';
        $this->confirmMessage = __('Видалити запис про виконання «:name»?', ['name' => $displayName]);
        $this->confirmData = $seedRunId;
        $this->showConfirmModal = true;
    }

    public function deleteSeederFile(string $className, bool $withQuestions = false): void
    {
        $result = $this->seedRunsService->deleteSeederFile($className, $withQuestions);
        
        $this->statusMessage = $result['message'];
        $this->statusType = ($result['status'] ?? '') === 'success' ? 'success' : 'error';
        
        $this->refreshOverview();
    }

    public function confirmDeleteSeederFile(string $className, string $displayName, bool $withQuestions = false): void
    {
        $this->confirmAction = $withQuestions ? 'deleteSeederFileWithQuestions' : 'deleteSeederFile';
        $message = $withQuestions
            ? __('Видалити файл сидера «:name» та пов\'язані питання?', ['name' => $displayName])
            : __('Видалити файл сидера «:name»?', ['name' => $displayName]);
        $this->confirmMessage = $message;
        $this->confirmData = $className;
        $this->showConfirmModal = true;
    }

    public function refreshSeeder(int $seedRunId): void
    {
        $result = $this->seedRunsService->refreshSeeder($seedRunId);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmRefreshSeeder(int $seedRunId, string $displayName): void
    {
        $this->confirmAction = 'refreshSeeder';
        $this->confirmMessage = __('Оновити дані сидера «:name»? Всі поточні дані будуть видалені та створені заново.', ['name' => $displayName]);
        $this->confirmData = $seedRunId;
        $this->showConfirmModal = true;
    }

    public function deleteSeedRunWithData(int $seedRunId): void
    {
        $result = $this->seedRunsService->destroySeedRunWithData($seedRunId);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmDeleteSeedRunWithData(int $seedRunId, string $displayName, string $buttonLabel): void
    {
        $this->confirmAction = 'deleteSeedRunWithData';
        $this->confirmMessage = __('Видалити запис та дані сидера «:name»?', ['name' => $displayName]);
        $this->confirmData = $seedRunId;
        $this->showConfirmModal = true;
    }

    public function executeConfirmedAction(): void
    {
        // Store action and data before clearing modal state
        $action = $this->confirmAction;
        $data = $this->confirmData;
        
        // Close modal immediately by clearing all modal state
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmMessage = '';
        $this->confirmData = null;

        // Execute the action after modal is closed
        match ($action) {
            'runSeeder' => $this->runSeeder($data),
            'runMissingSeeders' => $this->runMissingSeeders(),
            'markAsExecuted' => $this->markAsExecuted($data),
            'deleteSeedRun' => $this->deleteSeedRun($data),
            'deleteSeederFile' => $this->deleteSeederFile($data, false),
            'deleteSeederFileWithQuestions' => $this->deleteSeederFile($data, true),
            'refreshSeeder' => $this->refreshSeeder($data),
            'deleteSeedRunWithData' => $this->deleteSeedRunWithData($data),
            default => null,
        };

        // Ensure the trees are fully refreshed after any confirmed action
        $this->refreshOverview();
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmMessage = '';
        $this->confirmData = null;
    }

    public function openFileModal(string $className, string $displayName): void
    {
        $result = $this->seedRunsService->getSeederFile($className);
        
        if (!$result['success']) {
            $this->statusMessage = $result['message'];
            $this->statusType = 'error';
            return;
        }
        
        $this->fileModalClassName = $className;
        $this->fileModalDisplayName = $result['display_class_name'] ?? $displayName;
        $this->fileModalPath = $result['path'] ?? '';
        $this->fileModalContents = $result['contents'] ?? '';
        $this->fileModalLastModified = $result['last_modified'] ?? '';
        $this->showFileModal = true;
    }

    public function saveFile(): void
    {
        $result = $this->seedRunsService->updateSeederFile($this->fileModalClassName, $this->fileModalContents);
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->fileModalLastModified = $result['last_modified'] ?? '';
            $this->fileModalContents = $result['contents'] ?? $this->fileModalContents;
        }
    }

    public function closeFileModal(): void
    {
        $this->showFileModal = false;
        $this->fileModalClassName = '';
        $this->fileModalDisplayName = '';
        $this->fileModalPath = '';
        $this->fileModalContents = '';
        $this->fileModalLastModified = '';
    }

    public function openCreateModal(): void
    {
        $this->seederFolders = $this->seedRunsService->getSeederFolders();
        $this->newSeederClassName = '';
        $this->newSeederFolder = '';
        $this->newSeederContents = '';
        $this->showCreateModal = true;
    }

    public function createSeeder(): void
    {
        $result = $this->seedRunsService->storeSeederFile(
            $this->newSeederClassName,
            $this->newSeederContents,
            $this->newSeederFolder
        );
        
        $this->statusMessage = $result['message'];
        $this->statusType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->closeCreateModal();
            $this->refreshOverview();
        }
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->newSeederClassName = '';
        $this->newSeederFolder = '';
        $this->newSeederContents = '';
    }

    public function clearStatus(): void
    {
        $this->statusMessage = '';
    }

    public function render()
    {
        return view('seed-runs-v2::livewire.index');
    }
}
