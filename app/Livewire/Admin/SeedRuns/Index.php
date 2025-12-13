<?php

namespace App\Livewire\Admin\SeedRuns;

use App\Services\SeedRuns\SeedRunsService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    public array $overview = [];

    public string $statusMessage = '';

    public string $errorMessage = '';

    public string $search = '';

    protected SeedRunsService $service;

    public function boot(SeedRunsService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->refreshOverview();
    }

    public function refreshOverview(): void
    {
        $data = $this->service->overview();

        $this->overview = [
            'tableExists' => (bool) ($data['tableExists'] ?? false),
            'pendingSeeders' => collect($data['pendingSeeders'] ?? [])->map(fn ($item) => (array) $item)->values()->all(),
            'executedSeeders' => collect($data['executedSeeders'] ?? [])->map(fn ($item) => (array) $item)->values()->all(),
            'recentSeedRunOrdinals' => collect($data['recentSeedRunOrdinals'] ?? [])->toArray(),
        ];
    }

    public function runMissing(): void
    {
        $response = $this->service->runMissing();
        $this->handleResponse($response);
    }

    public function runSeeder(string $className): void
    {
        $response = $this->service->runSeeder($className);
        $this->handleResponse($response);
    }

    public function markExecuted(string $className): void
    {
        $response = $this->service->markSeederExecuted($className);
        $this->handleResponse($response);
    }

    public function getFilteredExecutedProperty(): Collection
    {
        $executed = collect($this->overview['executedSeeders'] ?? []);

        if (trim($this->search) === '') {
            return $executed;
        }

        $needle = mb_strtolower($this->search);

        return $executed->filter(function ($item) use ($needle) {
            $name = mb_strtolower((string) ($item['display_class_name'] ?? ''));

            return str_contains($name, $needle);
        })->values();
    }

    protected function handleResponse(array $response): void
    {
        $this->statusMessage = '';
        $this->errorMessage = '';

        $data = $response['data'] ?? [];

        if (! ($response['ok'] ?? false)) {
            $this->errorMessage = (string) ($data['message'] ?? __('Сталася помилка при виконанні операції.'));
        } else {
            $this->statusMessage = (string) ($data['message'] ?? __('Операцію виконано.'));
        }

        $this->refreshOverview();
    }

    public function render()
    {
        return view('livewire.admin.seed-runs.index')
            ->layout('layouts.app')
            ->layoutData(['title' => 'Seed Runs (V2)']);
    }
}
