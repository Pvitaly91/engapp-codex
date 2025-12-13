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
        <div class="space-y-2" style="margin-left: {{ max(0, $depth) * 1.5 }}rem">
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
                    @include('livewire.admin.seed-runs.partials.executed-node', ['node' => $childNode, 'depth' => $depth + 1])
                @endforeach
            </div>
        </div>
    @endif
@else
    @php
        $seedRun = $node['seed_run'] ?? null;
        $seedRunId = $seedRun->id ?? 0;
        $className = $seedRun->class_name ?? '';
        $displayName = $seedRun->display_class_name ?? $node['name'] ?? 'Unknown';
        $ranAt = $seedRun->ran_at ?? null;
        $questionCount = $seedRun->question_count ?? 0;
        $dataProfile = $node['data_profile'] ?? [];
        $recentOrdinal = $recentSeedRunOrdinals[$seedRunId] ?? null;
        
        $shouldShow = empty($searchQuery) || stripos($displayName, $searchQuery) !== false;
    @endphp
    @if($shouldShow)
        <div class="space-y-2" style="margin-left: {{ max(0, $depth) * 1.5 }}rem">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex-1">
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
                    <div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
                        @if($ranAt)
                            <span title="Виконано">
                                <i class="fa-regular fa-clock"></i>
                                {{ $ranAt->format('Y-m-d H:i') }}
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
                </div>
                <div class="flex flex-wrap gap-2">
                    <button 
                        type="button"
                        wire:click="openFileModal('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                        class="inline-flex items-center gap-1.5 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded hover:bg-indigo-200 transition"
                    >
                        <i class="fa-solid fa-file-code"></i>
                        Код
                    </button>
                    <a 
                        href="{{ route('seed-runs.preview', ['class_name' => $className]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-2 py-1 bg-sky-100 text-sky-700 text-xs font-medium rounded hover:bg-sky-200 transition"
                    >
                        <i class="fa-solid fa-eye"></i>
                        Перегляд
                    </a>
                    <button 
                        type="button"
                        wire:click="confirmDeleteSeedRun({{ $seedRunId }}, '{{ addslashes($displayName) }}')"
                        class="inline-flex items-center gap-1.5 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                        Видалити запис
                    </button>
                    @if($questionCount > 0)
                        <button 
                            type="button"
                            wire:click="confirmDeleteSeederFile('{{ addslashes($className) }}', '{{ addslashes($displayName) }}', true)"
                            class="inline-flex items-center gap-1.5 px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-500 transition"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                            З питаннями
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
