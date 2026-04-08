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
    public string $activeSeederTab = 'main';
    public array $seederTabCounts = [];
    public int $runnablePendingCount = 0;
    public array $theoryTestPages = [];
    
    public string $searchQuery = '';
    public string $statusMessage = '';
    public string $statusType = 'success';
    public array $statusLinks = [];
    public array $statusErrors = [];
    
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
    
    protected SeedRunsService $seedRunsService;
    protected $queryString = [
        'activeSeederTab' => ['except' => 'main', 'as' => 'tab'],
        'searchQuery' => ['except' => ''],
    ];

    public function boot(SeedRunsService $seedRunsService): void
    {
        $this->seedRunsService = $seedRunsService;
    }

    public function mount(): void
    {
        $this->activeSeederTab = $this->normalizeSeederTab($this->activeSeederTab);
        $this->refreshOverview();
    }

    public function refreshOverview(): void
    {
        $overview = $this->seedRunsService->assembleSeedRunOverview($this->activeSeederTab);
        
        $this->tableExists = $overview['tableExists'];
        $this->pendingSeederHierarchy = $overview['pendingSeederHierarchy']->toArray();
        $this->executedSeederHierarchy = $overview['executedSeederHierarchy']->toArray();
        $this->recentSeedRunOrdinals = $overview['recentSeedRunOrdinals']->toArray();
        $this->activeSeederTab = $this->normalizeSeederTab($overview['activeSeederTab'] ?? $this->activeSeederTab);
        $this->seederTabCounts = $overview['seederTabCounts'] ?? [];
        $this->runnablePendingCount = (int) ($overview['runnablePendingCount'] ?? 0);
        $this->theoryTestPages = collect($overview['theoryTestPages'] ?? collect())
            ->map(function ($pageGroup) {
                return [
                    'group_key' => (string) data_get($pageGroup, 'group_key', ''),
                    'page' => [
                        'label' => (string) data_get($pageGroup, 'page.label', ''),
                        'title' => (string) data_get($pageGroup, 'page.title', ''),
                        'url' => (string) data_get($pageGroup, 'page.url', ''),
                    ],
                    'seeders_count' => (int) data_get($pageGroup, 'seeders_count', 0),
                    'question_count' => (int) data_get($pageGroup, 'question_count', 0),
                    'tests_count' => (int) data_get($pageGroup, 'tests_count', 0),
                    'latest_ran_at' => (int) data_get($pageGroup, 'latest_ran_at', 0),
                    'seeders' => collect(data_get($pageGroup, 'seeders', []))
                        ->map(function ($seedRun) {
                            $ranAt = data_get($seedRun, 'ran_at');

                            if ($ranAt instanceof \DateTimeInterface) {
                                $ranAt = $ranAt->format('Y-m-d H:i:s');
                            } elseif (! is_string($ranAt)) {
                                $ranAt = null;
                            }

                            return [
                                'class_name' => (string) data_get($seedRun, 'class_name', ''),
                                'display_class_name' => (string) data_get($seedRun, 'display_class_name', ''),
                                'display_class_basename' => (string) data_get($seedRun, 'display_class_basename', ''),
                                'question_count' => (int) data_get($seedRun, 'question_count', 0),
                                'ran_at_formatted' => (string) data_get($seedRun, 'ran_at_formatted', $ranAt ?? ''),
                                'test_target' => data_get($seedRun, 'test_target'),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function runSeeder(string $className): void
    {
        $result = $this->seedRunsService->runSeeder($className);
        
        $this->applyResultStatus($result);
        
        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function runFolder(string $folderPath): void
    {
        $classNames = $this->resolvePendingFolderClassNames($folderPath);

        if ($classNames === []) {
            $this->setStatus(__('Не знайдено сидерів для вибраної папки.'), 'error');

            return;
        }

        $result = $this->seedRunsService->runSeedersInFolder($classNames, $folderPath);

        $this->applyResultStatus($result);

        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function runPendingLocalizations(string $className, string $displayName = ''): void
    {
        $localizationClassNames = $this->resolveExecutedSeederPendingLocalizationClassNames($className);

        if ($localizationClassNames === []) {
            $this->setStatus(__('Для цього сидера не знайдено невиконаних локалізацій.'), 'error');

            return;
        }

        $label = $displayName !== ''
            ? __('Локалізації для :name', ['name' => $displayName])
            : $className;
        $result = $this->seedRunsService->runSeedersInFolder($localizationClassNames, $label);

        $this->applyResultStatus($result);

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

    public function confirmRunFolder(string $folderPath, string $folderLabel): void
    {
        $this->confirmAction = 'runFolder';
        $this->confirmMessage = __('Виконати всі сидери в папці «:name»?', ['name' => $folderLabel]);
        $this->confirmData = ['path' => $folderPath];
        $this->showConfirmModal = true;
    }

    public function confirmRunPendingLocalizations(string $className, string $displayName): void
    {
        $this->confirmAction = 'runPendingLocalizations';
        $this->confirmMessage = __('Виконати невиконані локалізації для сидера «:name»?', ['name' => $displayName]);
        $this->confirmData = [
            'class_name' => $className,
            'display_name' => $displayName,
        ];
        $this->showConfirmModal = true;
    }

    public function runMissingSeeders(): void
    {
        $result = $this->seedRunsService->runMissingSeeders($this->activeSeederTab);
        
        $this->applyResultStatus($result);
        
        $this->refreshOverview();
    }

    public function confirmRunMissing(): void
    {
        $this->confirmAction = 'runMissingSeeders';
        $this->confirmMessage = $this->activeSeederTab === 'localizations'
            ? __('Виконати всі доступні невиконані локалізації?')
            : __('Виконати всі невиконані сидери?');
        $this->confirmData = null;
        $this->showConfirmModal = true;
    }

    public function setActiveSeederTab(string $tab): void
    {
        $this->activeSeederTab = $this->normalizeSeederTab($tab);
        $this->refreshOverview();
    }

    public function markAsExecuted(string $className): void
    {
        $result = $this->seedRunsService->markAsExecuted($className);
        
        $this->applyResultStatus($result);
        
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
        
        $this->applyResultStatus($result);
        
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
        
        $this->applyResultStatus($result);
        
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
        
        $this->applyResultStatus($result);
        
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

    public function refreshExecutedFolder(string $folderPath, string $folderLabel = ''): void
    {
        $classNames = $this->resolveExecutedFolderClassNames($folderPath);

        if ($classNames === []) {
            $this->setStatus(__('Не знайдено виконаних сидерів для вибраної папки.'), 'error');

            return;
        }

        $result = $this->seedRunsService->refreshSeedersInFolder($classNames, $folderLabel !== '' ? $folderLabel : $folderPath);

        $this->applyResultStatus($result);

        if ($result['success']) {
            $this->refreshOverview();
        }
    }

    public function confirmRefreshExecutedFolder(string $folderPath, string $folderLabel): void
    {
        $this->confirmAction = 'refreshExecutedFolder';
        $this->confirmMessage = __('Оновити дані всіх сидерів у папці «:name»? Поточні дані та пов’язані локалізації буде видалено і створено заново.', ['name' => $folderLabel]);
        $this->confirmData = [
            'path' => $folderPath,
            'label' => $folderLabel,
        ];
        $this->showConfirmModal = true;
    }

    public function deleteSeedRunWithData(int $seedRunId): void
    {
        $result = $this->seedRunsService->destroySeedRunWithData($seedRunId);
        
        $this->applyResultStatus($result);
        
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

    public function confirmDeleteLocalizationFromDatabase(int $seedRunId, string $displayName): void
    {
        $this->confirmAction = 'deleteSeedRunWithData';
        $this->confirmMessage = __('Видалити локалізацію «:name» з БД?', ['name' => $displayName]);
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
            'runFolder' => $this->runFolder($data['path'] ?? ''),
            'runPendingLocalizations' => $this->runPendingLocalizations($data['class_name'] ?? '', $data['display_name'] ?? ''),
            'runMissingSeeders' => $this->runMissingSeeders(),
            'markAsExecuted' => $this->markAsExecuted($data),
            'deleteSeedRun' => $this->deleteSeedRun($data),
            'deleteSeederFile' => $this->deleteSeederFile($data, false),
            'deleteSeederFileWithQuestions' => $this->deleteSeederFile($data, true),
            'refreshSeeder' => $this->refreshSeeder($data),
            'refreshExecutedFolder' => $this->refreshExecutedFolder($data['path'] ?? '', $data['label'] ?? ''),
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
        $this->setStatus($result['message'], 'error', [], $result['errors'] ?? []);
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
        
        $this->applyResultStatus($result);
        
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
        
        $this->applyResultStatus($result);
        
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
        $this->statusErrors = [];
        $this->statusLinks = [];
    }

    protected function setStatus(string $message, string $type = 'success', array $links = [], array $errors = []): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
        $this->statusLinks = $links;
        $this->statusErrors = array_values(array_filter(
            array_map(fn ($error) => trim((string) $error), $errors),
            fn (string $error) => $error !== ''
        ));
    }

    protected function applyResultStatus(array $result): void
    {
        $errors = is_array($result['errors'] ?? null) ? $result['errors'] : [];
        $status = (string) ($result['status'] ?? '');
        $success = array_key_exists('success', $result)
            ? (bool) $result['success']
            : $status === 'success';

        $type = match (true) {
            $status === 'partial' => 'warning',
            $success && $errors !== [] => 'warning',
            $success => 'success',
            default => 'error',
        };

        $this->setStatus(
            (string) ($result['message'] ?? ''),
            $type,
            is_array($result['test_targets'] ?? null) ? $result['test_targets'] : [],
            $errors
        );
    }

    protected function normalizeSeederTab(?string $tab): string
    {
        return in_array($tab, ['localizations', 'theory-tests'], true) ? $tab : 'main';
    }

    protected function resolvePendingFolderClassNames(string $folderPath): array
    {
        $node = $this->findPendingFolderNodeByPath($this->pendingSeederHierarchy, $folderPath);

        $classNames = $node['runnable_class_names'] ?? $node['class_names'] ?? null;

        return is_array($classNames)
            ? array_values(array_filter(array_map('strval', $classNames)))
            : [];
    }

    protected function resolveExecutedFolderClassNames(string $folderPath): array
    {
        $node = $this->findExecutedFolderNodeByPath($this->executedSeederHierarchy, $folderPath);
        $classNames = $node['class_names'] ?? null;

        return is_array($classNames)
            ? array_values(array_filter(array_map('strval', $classNames)))
            : [];
    }

    protected function findPendingFolderNodeByPath(array $nodes, string $folderPath): ?array
    {
        foreach ($nodes as $node) {
            if (($node['type'] ?? null) !== 'folder') {
                continue;
            }

            if (($node['path'] ?? '') === $folderPath) {
                return $node;
            }

            $nested = $this->findPendingFolderNodeByPath($node['children'] ?? [], $folderPath);

            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    protected function findExecutedFolderNodeByPath(array $nodes, string $folderPath): ?array
    {
        foreach ($nodes as $node) {
            if (($node['type'] ?? null) !== 'folder') {
                continue;
            }

            if (($node['path'] ?? '') === $folderPath) {
                return $node;
            }

            $nested = $this->findExecutedFolderNodeByPath($node['children'] ?? [], $folderPath);

            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    protected function resolveExecutedSeederPendingLocalizationClassNames(string $className): array
    {
        $node = $this->findExecutedSeederNodeByClassName($this->executedSeederHierarchy, $className);

        return collect($node['seed_run']['pending_localizations'] ?? [])
            ->pluck('class_name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    protected function findExecutedSeederNodeByClassName(array $nodes, string $className): ?array
    {
        foreach ($nodes as $node) {
            if (($node['type'] ?? null) === 'seeder' && (($node['seed_run']['class_name'] ?? '') === $className)) {
                return $node;
            }

            if (($node['type'] ?? null) !== 'folder') {
                continue;
            }

            $nested = $this->findExecutedSeederNodeByClassName($node['children'] ?? [], $className);

            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    public function render()
    {
        return view('seed-runs-v2::livewire.index');
    }
}
