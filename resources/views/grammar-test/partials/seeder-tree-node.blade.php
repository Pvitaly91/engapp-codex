@php
    $nodeType = $node['type'] ?? 'folder';
    $depth = max(0, $depth ?? 0);
    $indent = $depth * 1.25;
@endphp

@if($nodeType === 'folder')
    @php
        $children = collect($node['children'] ?? [])->all();
        $hasActiveChild = $node['has_active_child'] ?? false;
        $initialOpen = $hasActiveChild;
        $seederCount = $node['seeder_count'] ?? 0;
        $fullPath = $node['full_path'] ?? ($node['name'] ?? '');
        $folderTooltip = str_replace('/', '\\', $fullPath);
    @endphp
    <div x-data="{ open: {{ $initialOpen ? 'true' : 'false' }} }"
         class="space-y-2"
         style="margin-left: {{ $indent }}rem;">
        <button type="button"
                class="flex items-center gap-2 w-full text-sm font-semibold text-gray-700 hover:text-gray-900 transition px-2 py-1 rounded-xl"
                @click="open = !open"
                :aria-expanded="open.toString()">
            <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100 transition"
                  aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </span>
            <i class="fa-solid fa-folder-tree text-slate-500 flex-shrink-0"></i>
            <span class="flex-1 min-w-0 truncate" title="{{ $folderTooltip }}">{{ $node['name'] ?? '' }}</span>
            <span class="flex-shrink-0 text-xs font-medium text-gray-500">({{ $seederCount }})</span>
        </button>
        <div x-show="open" x-transition style="display: none;" class="space-y-2">
            @foreach($children as $child)
                @include('grammar-test.partials.seeder-tree-node', [
                    'node' => $child,
                    'depth' => $depth + 1,
                    'selectedSourceCollection' => $selectedSourceCollection,
                    'recentSourceIds' => $recentSourceIds,
                    'recentSourceOrdinals' => $recentSourceOrdinals,
                ])
            @endforeach
        </div>
    </div>
@elseif($nodeType === 'seeder')
    @php
        $seederSources = collect($node['sources'] ?? []);
        $seederIsSelected = $node['is_selected'] ?? false;
        $seederHasSelectedSources = $node['has_selected_sources'] ?? false;
        $seederIsActive = $node['is_active'] ?? false;
        $inputId = $node['seeder_input_id'] ?? ('seeder-' . md5($node['full_class'] ?? ($node['name'] ?? uniqid())));
        $displayName = $node['display_name'] ?? ($node['name'] ?? '');
        $fullClassName = $node['full_class'] ?? $displayName;
        $isNew = $node['is_new'] ?? false;
        $recentOrdinal = $node['recent_ordinal'] ?? null;
    @endphp
    <div x-data="{
            open: {{ $seederIsActive ? 'true' : 'false' }},
            toggle(openState = undefined) {
                if (openState === undefined) {
                    this.open = !this.open;
                    return;
                }

                this.open = !!openState;
            }
        }"
         @toggle-all-seeder-sources.window="toggle($event.detail.open)"
         @class([
            'border rounded-2xl overflow-hidden transition',
            'border-gray-200 bg-white' => ! $seederIsActive,
            'border-blue-400 shadow-sm bg-blue-50' => $seederIsActive,
         ])
         style="margin-left: {{ $indent }}rem;">
        <div class="flex items-start gap-3 px-4 py-2 bg-gray-50 cursor-pointer"
             @click="toggle()">
            <button type="button"
                    class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100 transition"
                    @click.stop="toggle()"
                    :aria-expanded="open.toString()"
                    aria-label="Перемкнути список джерел">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <label for="{{ $inputId }}"
                   @class([
                        'flex items-start gap-2 text-sm font-semibold text-gray-800 cursor-pointer w-full',
                        'text-blue-800' => $seederIsSelected,
                   ])
                   @click.stop>
                <input type="checkbox" name="seeder_classes[]" value="{{ $node['full_class'] ?? '' }}" id="{{ $inputId }}"
                       {{ $seederIsSelected ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <div class="min-w-0 flex-1">
                    <span class="block truncate" title="{{ $fullClassName }}">{{ $displayName }}</span>
                    @if($isNew)
                        <span class="mt-1 inline-flex items-center justify-center text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full w-max">
                            Новий{{ !is_null($recentOrdinal) ? ' #' . $recentOrdinal : '' }}
                        </span>
                    @endif
                </div>
            </label>
        </div>
        <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
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
                            'flex items-start gap-2 px-3 py-1 rounded-full border text-sm transition text-left',
                            'border-gray-200 bg-white hover:border-blue-300' => ! $sourceIsSelected,
                            'border-blue-400 bg-blue-50 shadow-sm' => $sourceIsSelected,
                        ])
                               title="{{ $source->name }} (ID: {{ $source->id }})">
                            <input type="checkbox" name="sources[]" value="{{ $source->id }}"
                                   {{ $sourceIsSelected ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <span class="whitespace-normal break-words flex items-center gap-2 flex-wrap">
                                <span class="truncate">{{ $source->name }} (ID: {{ $source->id }})</span>
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
