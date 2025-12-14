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
            </div>
        </div>
    @endif
@endif
