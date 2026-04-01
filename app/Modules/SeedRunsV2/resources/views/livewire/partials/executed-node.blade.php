@php
    $isFolder = ($node['type'] ?? '') === 'folder';
    $nodeKey = 'executed_' . ($node['path'] ?? uniqid());
    $searchQuery = $searchQuery ?? '';
@endphp

@if($isFolder)
    @php
        $folderName = $node['name'] ?? 'Unknown';
        $folderProfile = $node['folder_profile'] ?? [];
        $shouldShow = empty($searchQuery) || collect($node['class_names'] ?? [])->contains(fn($c) => 
            stripos($this->seedRunsService->formatSeederClassName($c), $searchQuery) !== false
        );
    @endphp
    @if($shouldShow)
        <div
            wire:key="executed-folder-{{ $nodeKey }}"
            class="space-y-2"
            style="margin-left: {{ max(0, $depth) * 1.5 }}rem"
        >
            <button 
                type="button"
                class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                @click="expandedFolders['{{ $nodeKey }}'] = !expandedFolders['{{ $nodeKey }}']"
            >
                <i 
                    class="fa-solid fa-chevron-down text-xs text-slate-500 transition-transform"
                    :class="{ '-rotate-90': !expandedFolders['{{ $nodeKey }}'] }"
                ></i>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span>{{ $folderName }}</span>
                <span class="text-xs font-normal text-slate-500">({{ $node['seeder_count'] ?? 0 }})</span>
            </button>
            <div class="space-y-4" x-show="expandedFolders['{{ $nodeKey }}']" x-collapse>
                @foreach(($node['children'] ?? []) as $childNode)
                    @include('seed-runs-v2::livewire.partials.executed-node', ['node' => $childNode, 'depth' => $depth + 1])
                @endforeach
            </div>
        </div>
    @endif
@else
    @php
        $seedRun = $node['seed_run'] ?? [];
        $seedRunId = $seedRun['id'] ?? 0;
        $className = $seedRun['class_name'] ?? '';
        $displayName = $seedRun['display_class_name'] ?? $node['name'] ?? 'Unknown';
        $ranAtFormatted = $seedRun['ran_at_formatted'] ?? null;
        $questionCount = $seedRun['question_count'] ?? 0;
        $dataProfile = $node['data_profile'] ?? [];
        $recentOrdinal = $recentSeedRunOrdinals[$seedRunId] ?? null;
        $theoryTarget = $seedRun['theory_target'] ?? null;
        $testTarget = $seedRun['test_target'] ?? null;
        $relatedLocalizations = collect($seedRun['related_localizations'] ?? []);
        
        // Get delete button text from data profile
        $deleteButtonText = $dataProfile['delete_button'] ?? __('Видалити з даними');
        
        $shouldShow = empty($searchQuery) || stripos($displayName, $searchQuery) !== false;
    @endphp
    @if($shouldShow)
        <div
            wire:key="executed-item-{{ $seedRunId ?: $className }}"
            class="space-y-2"
            style="margin-left: {{ max(0, $depth) * 1.5 }}rem"
        >
            <div class="flex flex-col gap-3">
                {{-- Seeder info --}}
                <div class="flex items-center gap-2">
                    @if($recentOrdinal)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-blue-600 rounded-full">
                            {{ $recentOrdinal }}
                        </span>
                    @endif
                    <span class="text-sm font-mono text-gray-800 break-all">
                        @if($searchQuery && stripos($displayName, $searchQuery) !== false)
                            {!! preg_replace('/(' . preg_quote($searchQuery, '/') . ')/i', '<mark class="bg-yellow-200">$1</mark>', e($displayName)) !!}
                        @else
                            {{ $displayName }}
                        @endif
                    </span>
                </div>
                
                {{-- Metadata row --}}
                <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                    @if($ranAtFormatted)
                        <span title="Виконано">
                            <i class="fa-regular fa-clock"></i>
                            {{ $ranAtFormatted }}
                        </span>
                    @endif
                    <span title="Кількість питань">
                        <i class="fa-solid fa-question-circle"></i>
                        {{ $questionCount }}
                    </span>
                    @if(!empty($dataProfile['type']))
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] uppercase">
                            {{ $dataProfile['type'] }}
                        </span>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-2">
                    @if($testTarget)
                        <a
                            href="{{ $testTarget['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="{{ $testTarget['title'] ?? ($testTarget['label'] ?? 'Готовий тест') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 text-sky-700 text-xs font-medium rounded-md hover:bg-sky-200 transition"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            {{ $testTarget['label'] ?? 'Готовий тест' }}
                        </a>
                    @endif

                    @if($theoryTarget)
                        <a
                            href="{{ $theoryTarget['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="{{ $theoryTarget['title'] ?? $theoryTarget['label'] }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-md hover:bg-emerald-200 transition"
                        >
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            {{ $theoryTarget['label'] }}
                        </a>
                    @endif

                    {{-- Preview button --}}
                    <a 
                        href="{{ route('seed-runs.preview', ['class_name' => $className]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-md hover:bg-purple-200 transition"
                    >
                        <i class="fa-solid fa-eye"></i>
                        Попередній перегляд
                    </a>
                    
                    {{-- Edit file button --}}
                    <button 
                        type="button"
                        wire:click="openFileModal('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-200 transition"
                    >
                        <i class="fa-solid fa-file-code"></i>
                        Код
                    </button>
                    
                    {{-- Delete file button --}}
                    <button 
                        type="button"
                        wire:click="confirmDeleteSeederFile('{{ addslashes($className) }}', '{{ addslashes($displayName) }}', false)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition"
                    >
                        <i class="fa-solid fa-file-circle-xmark"></i>
                        Видалити файл
                    </button>
                    
                    {{-- Refresh button --}}
                    <button 
                        type="button"
                        wire:click="confirmRefreshSeeder({{ $seedRunId }}, '{{ addslashes($displayName) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-500 transition"
                    >
                        <i class="fa-solid fa-rotate"></i>
                        Оновити дані
                    </button>
                    
                    {{-- Delete with data button --}}
                    <button 
                        type="button"
                        wire:click="confirmDeleteSeedRunWithData({{ $seedRunId }}, '{{ addslashes($displayName) }}', '{{ addslashes($deleteButtonText) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition"
                    >
                        <i class="fa-solid fa-broom"></i>
                        {{ $deleteButtonText }}
                    </button>
                    
                    {{-- Delete record only button --}}
                    <button 
                        type="button"
                        wire:click="confirmDeleteSeedRun({{ $seedRunId }}, '{{ addslashes($displayName) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Видалити запис
                    </button>
                </div>

                @if($relatedLocalizations->isNotEmpty())
                    <div class="rounded-lg border border-sky-200 bg-sky-50/80 p-3 space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-sky-800">
                                Виконані локалізації
                            </p>
                            <p class="text-[11px] text-sky-700">
                                Доступно {{ $relatedLocalizations->count() }} записів локалізацій.
                            </p>
                        </div>

                        <div class="space-y-2">
                            @foreach($relatedLocalizations as $localization)
                                @php
                                    $localizationName = $localization['display_name'] ?? ($localization['class_name'] ?? 'Unknown localization');
                                @endphp
                                <div class="rounded-md border border-sky-100 bg-white/90 px-3 py-2">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-800">
                                                    {{ $localization['locale_label'] ?? 'N/A' }}
                                                </span>
                                                <span class="font-medium text-slate-700">
                                                    {{ $localization['type_label'] ?? 'Локалізація' }}
                                                </span>
                                            </div>
                                            <p class="mt-1 break-all text-xs text-slate-600">
                                                {{ $localizationName }}
                                            </p>
                                            @if(!empty($localization['ran_at']))
                                                <p class="mt-1 text-[11px] text-slate-500">
                                                    Останній запуск: {{ $localization['ran_at'] }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                            <button
                                                type="button"
                                                wire:click="confirmRefreshSeeder({{ (int) ($localization['seed_run_id'] ?? 0) }}, '{{ addslashes($localizationName) }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-500 transition"
                                            >
                                                <i class="fa-solid fa-rotate"></i>
                                                Оновити
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="confirmDeleteLocalizationFromDatabase({{ (int) ($localization['seed_run_id'] ?? 0) }}, '{{ addslashes($localizationName) }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition"
                                            >
                                                <i class="fa-solid fa-database"></i>
                                                Видалити з БД
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
