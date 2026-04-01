@php
    $isFolder = ($node['type'] ?? '') === 'folder';
    $nodeKey = 'pending_' . ($node['path'] ?? uniqid());
@endphp

@if($isFolder)
    <div
        wire:key="pending-folder-{{ $nodeKey }}"
        class="space-y-2"
        style="margin-left: {{ max(0, $depth) * 1.5 }}rem"
    >
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <button 
                type="button"
                class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition text-left"
                @click="expandedFolders['{{ $nodeKey }}'] = !expandedFolders['{{ $nodeKey }}']"
            >
                <i 
                    class="fa-solid fa-chevron-down text-xs text-slate-500 transition-transform"
                    :class="{ '-rotate-90': !expandedFolders['{{ $nodeKey }}'] }"
                ></i>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span>{{ $node['name'] ?? 'Unknown' }}</span>
                <span class="text-xs font-normal text-slate-500">({{ $node['seeder_count'] ?? 0 }})</span>
            </button>

            @if(!empty($node['runnable_class_names'] ?? []))
                <button
                    type="button"
                    wire:click="confirmRunFolder('{{ addslashes($node['path'] ?? '') }}', '{{ addslashes($node['path'] ?? ($node['name'] ?? 'Unknown')) }}')"
                    class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition w-full sm:w-auto"
                >
                    <i class="fa-solid fa-play"></i>
                    Виконати все
                </button>
            @elseif(($node['blocked_seeder_count'] ?? 0) > 0)
                <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-1.5">
                    Папка містить лише заблоковані локалізації
                </span>
            @endif
        </div>
        <div class="space-y-3" x-show="expandedFolders['{{ $nodeKey }}']" x-collapse>
            @foreach(($node['children'] ?? []) as $childNode)
                @include('seed-runs-v2::livewire.partials.pending-node', ['node' => $childNode, 'depth' => $depth + 1])
            @endforeach
        </div>
    </div>
@else
    @php
        $seeder = $node['pending_seeder'] ?? [];
        $className = $seeder['class_name'] ?? '';
        $displayName = $seeder['display_class_name'] ?? $node['name'] ?? 'Unknown';
        $displayNamespace = $seeder['display_class_namespace'] ?? null;
        $displayBasename = $seeder['display_class_basename'] ?? $displayName;
        $supportsPreview = $seeder['supports_preview'] ?? false;
        $availableLocalizations = collect($seeder['available_localizations'] ?? []);
        $dataType = $seeder['data_type'] ?? 'unknown';
        $isLocalizationSeeder = in_array($dataType, ['question_localizations', 'page_localizations'], true);
        $canExecute = (bool) ($seeder['can_execute'] ?? true);
        $executionBlockReason = $seeder['execution_block_reason'] ?? null;
        $isCategorySeeder = str_contains($displayBasename, 'Category');
        $labelClasses = $isLocalizationSeeder
            ? 'inline-flex items-center px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-semibold ring-1 ring-sky-200'
            : ($isCategorySeeder
            ? 'inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold ring-1 ring-emerald-200'
            : 'inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold');
    @endphp
    <div
        wire:key="pending-item-{{ $className }}"
        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        style="margin-left: {{ max(0, $depth) * 1.5 }}rem"
    >
        <div class="flex flex-col gap-3 sm:flex-1">
            <div class="flex items-center gap-3 min-w-0">
                <span class="inline-flex text-sm font-mono text-gray-700 break-all min-w-[12rem] sm:min-w-[15rem]">
                    @if($displayNamespace)
                        <span class="text-gray-500">{{ $displayNamespace }}</span><span class="text-gray-400">\</span>
                    @endif
                    <span class="{{ $labelClasses }}">{{ $displayBasename }}</span>
                </span>
            </div>

            @if($availableLocalizations->isNotEmpty())
                <div class="rounded-lg border border-sky-200 bg-sky-50/80 p-3 space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-800">
                            Існуючі локалізації
                        </p>
                        <p class="text-[11px] text-sky-700">
                            Знайдено {{ $availableLocalizations->count() }} файлів локалізацій.
                        </p>
                    </div>

                    <div class="space-y-2">
                        @foreach($availableLocalizations as $localization)
                            <div class="rounded-md border border-sky-100 bg-white/90 px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-800">
                                        {{ $localization['locale_label'] ?? 'N/A' }}
                                    </span>
                                    <span class="font-medium text-slate-700">
                                        {{ $localization['type_label'] ?? 'Локалізація' }}
                                    </span>
                                </div>
                                <p class="mt-1 break-all text-xs text-slate-600">
                                    {{ $localization['display_name'] ?? ($localization['class_name'] ?? '') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
            <button 
                type="button"
                wire:click="openFileModal('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-md hover:bg-indigo-200 transition w-full sm:w-auto"
            >
                <i class="fa-solid fa-file-code"></i>
                Код
            </button>
            @if($supportsPreview)
                <a 
                    href="{{ route('seed-runs.preview', ['class_name' => $className]) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-sky-100 text-sky-700 text-xs font-medium rounded-md hover:bg-sky-200 transition w-full sm:w-auto"
                >
                    <i class="fa-solid fa-eye"></i>
                    Переглянути
                </a>
            @endif
            <button 
                type="button"
                wire:click="confirmDeleteSeederFile('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition w-full sm:w-auto"
                >
                    <i class="fa-solid fa-file-circle-xmark"></i>
                    Видалити файл
                </button>
            <button 
                type="button"
                wire:click="confirmMarkAsExecuted('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-md hover:bg-amber-400 transition w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled(! $canExecute)
            >
                <i class="fa-solid fa-check"></i>
                Позначити виконаним
            </button>
            <button 
                type="button"
                wire:click="confirmRunSeeder('{{ addslashes($className) }}', '{{ addslashes($displayName) }}')"
                class="inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled(! $canExecute)
            >
                <i class="fa-solid fa-play"></i>
                Виконати
            </button>
            @if(! $canExecute && $executionBlockReason)
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 w-full sm:max-w-md">
                    {{ $executionBlockReason }}
                </p>
            @endif
        </div>
    </div>
@endif
