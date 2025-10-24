@php
    $nodeType = $node['type'] ?? null;
    $depth = $depth ?? 0;
    $indent = max(0, $depth) * 1.25;
@endphp

@if($nodeType === 'folder')
    @php
        $defaultOpen = $node['default_open'] ?? false;
        $children = collect($node['children'] ?? []);
        $folderTitle = $node['path'] ?? $node['name'] ?? '';
        $folderName = $node['name'] ?? '';
        $totalSeederCount = $node['total_seeder_count'] ?? 0;
    @endphp
    <div x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }" class="space-y-2">
        <div class="flex items-center" style="margin-left: {{ $indent }}rem;">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-gray-900 transition min-w-0"
                    @click="open = !open"
                    :aria-expanded="open.toString()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-gray-500 transition-transform"
                     :class="open ? 'rotate-90' : '-rotate-90'">
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-gray-500"></i>
                <span class="truncate" title="{{ $folderTitle }}">{{ $folderName }}</span>
                <span class="text-xs font-normal text-gray-500 flex-shrink-0">({{ $totalSeederCount }})</span>
            </button>
        </div>
        <div x-show="open" x-transition style="display: none;" class="space-y-3">
            @foreach($children as $child)
                @include('grammar-test.partials.seeder-node', [
                    'node' => $child,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    </div>
@elseif($nodeType === 'seeder')
    @php
        $defaultOpen = $node['default_open'] ?? false;
        $seederSources = collect($node['sources'] ?? []);
        $className = $node['class_name'] ?? '';
        $displayName = $node['display_name'] ?? $className;
        $fullDisplayName = $node['full_display_name'] ?? $className;
        $seederInputId = 'seeder-' . md5($className ?: uniqid('seeder', true));
        $seederIsSelected = $node['is_selected'] ?? false;
        $seederHasSelectedSources = $node['has_selected_sources'] ?? false;
        $isNew = $node['is_new'] ?? false;
        $ordinal = $node['ordinal'] ?? null;
        $isActive = $seederIsSelected || $seederHasSelectedSources;
    @endphp
    <div x-data="{ openSources: {{ $defaultOpen ? 'true' : 'false' }} }"
         @toggle-all-seeder-sources.window="if ($event.detail && Object.prototype.hasOwnProperty.call($event.detail, 'open')) { openSources = !!$event.detail.open; }"
         @class([
            'border rounded-2xl overflow-hidden transition bg-white',
            'border-gray-200' => ! $isActive,
            'border-blue-400 shadow-sm bg-blue-50' => $isActive,
         ])
         style="margin-left: {{ $indent }}rem;">
        <div class="flex items-start justify-between gap-3 px-4 py-3">
            <label for="{{ $seederInputId }}" class="flex-1 flex items-start gap-3">
                <input type="checkbox" name="seeder_classes[]" value="{{ $className }}" id="{{ $seederInputId }}"
                       {{ $seederIsSelected ? 'checked' : '' }}
                       class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded">
                <span class="min-w-0 flex flex-col text-sm text-gray-800">
                    <span class="truncate font-semibold" title="{{ $fullDisplayName }}">{{ $displayName }}</span>
                    @if($isNew)
                        <span class="mt-1 text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full block w-fit">
                            Новий{{ !is_null($ordinal) ? ' #' . $ordinal : '' }}
                        </span>
                    @endif
                </span>
            </label>
            <button type="button"
                    class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                    @click="openSources = !openSources"
                    :aria-expanded="openSources.toString()"
                    aria-label="Перемкнути список джерел">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSources }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div x-show="openSources" x-transition style="display: none;" class="px-4 pb-4 pt-2 bg-gray-50">
            @if($seederSources->isEmpty())
                <p class="text-xs text-gray-500">Для цього сидера немає пов'язаних джерел.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($seederSources as $source)
                        @php
                            $sourceIsSelected = $selectedSourceCollection->contains($source->id);
                            $sourceIsNew = $recentSourceIds->contains($source->id);
                            $sourceOrdinal = $recentSourceOrdinals->get($source->id);
                        @endphp
                        <label @class([
                            'flex items-start gap-2 px-3 py-1 rounded-full border text-sm transition text-left bg-white',
                            'border-gray-200 hover:border-blue-300' => ! $sourceIsSelected,
                            'border-blue-400 bg-blue-50 shadow-sm' => $sourceIsSelected,
                        ])>
                            <input type="checkbox" name="sources[]" value="{{ $source->id }}"
                                   {{ $sourceIsSelected ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <span class="whitespace-normal break-words flex items-center gap-2 flex-wrap">
                                <span>{{ $source->name }} (ID: {{ $source->id }})</span>
                                @if($sourceIsNew)
                                    <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                        Новий{{ !is_null($sourceOrdinal) ? ' #' . $sourceOrdinal : '' }}
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
