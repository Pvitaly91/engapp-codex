@php
    $node = $node ?? [];
    $depth = max(0, $depth ?? 0);
    $indent = $depth * 1.25;
@endphp

@if(($node['type'] ?? null) === 'folder')
    <div class="space-y-2" style="margin-left: {{ $indent }}rem;">
        <div x-data="{ open: {{ $depth === 0 ? 'true' : 'false' }} }" class="space-y-2">
            <button type="button"
                    class="flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition"
                    @click="open = !open"
                    :aria-expanded="open.toString()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-500 transition-transform" :class="{ '-rotate-90': !open }">
                    <path fill-rule="evenodd"
                          d="M6.22 4.47a.75.75 0 011.06 0l5 5a.75.75 0 010 1.06l-5 5a.75.75 0 01-1.06-1.06L10.69 10 6.22 5.53a.75.75 0 010-1.06z"
                          clip-rule="evenodd" />
                </svg>
                <i class="fa-solid fa-folder-tree text-slate-500"></i>
                <span>{{ $node['name'] }}</span>
            </button>

            <div x-show="open" x-transition style="display: none;" class="space-y-2">
                @foreach($node['children'] ?? [] as $child)
                    @include('grammar-test.partials.seeder-tree-node', [
                        'node' => $child,
                        'depth' => $depth + 1,
                        'selectedSeederClasses' => $selectedSeederClasses ?? [],
                        'selectedSourceCollection' => $selectedSourceCollection ?? collect(),
                        'recentSeederClasses' => $recentSeederClasses ?? collect(),
                        'recentSeederOrdinals' => $recentSeederOrdinals ?? collect(),
                        'recentSourceIds' => $recentSourceIds ?? collect(),
                        'recentSourceOrdinals' => $recentSourceOrdinals ?? collect(),
                    ])
                @endforeach
            </div>
        </div>
    </div>
@elseif(($node['type'] ?? null) === 'seeder')
    @php
        $group = $node['group'] ?? [];
        $className = $node['class'] ?? ($group['seeder'] ?? null);
        $displaySeederName = $node['name'] ?? $node['display_name'] ?? $className;
        $seederSources = collect($group['sources'] ?? []);
        $selectedSeederClasses = $selectedSeederClasses ?? [];
        $selectedSourceCollection = collect($selectedSourceCollection ?? []);
        $recentSeederClasses = collect($recentSeederClasses ?? []);
        $recentSeederOrdinals = collect($recentSeederOrdinals ?? []);
        $recentSourceIds = collect($recentSourceIds ?? []);
        $recentSourceOrdinals = collect($recentSourceOrdinals ?? []);

        $seederIsSelected = in_array($className, $selectedSeederClasses, true);
        $seederSourceIds = $seederSources->pluck('id');
        $seederHasSelectedSources = $seederSourceIds->intersect($selectedSourceCollection)->isNotEmpty();
        $groupIsActive = $seederIsSelected || $seederHasSelectedSources;
        $seederInputId = 'seeder-' . md5($className ?? uniqid('', true));
        $seederIsNew = $recentSeederClasses->contains($className);
        $seederOrdinal = $recentSeederOrdinals->get($className);
    @endphp

    <div style="margin-left: {{ $indent }}rem;">
        <div x-data="{
                open: {{ $groupIsActive ? 'true' : 'false' }},
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
                'border-gray-200 bg-white' => ! $groupIsActive,
                'border-blue-400 shadow-sm bg-blue-50' => $groupIsActive,
             ])
        >
            <div class="flex items-center justify-between gap-3 px-4 py-2 bg-gray-50 cursor-pointer"
                 @click="toggle()"
            >
                <label for="{{ $seederInputId }}"
                       @class([
                            'flex items-center gap-2 text-sm font-semibold text-gray-800 cursor-pointer',
                            'text-blue-800' => $seederIsSelected,
                       ])
                       @click.stop
                >
                    <input type="checkbox" name="seeder_classes[]" value="{{ $className }}" id="{{ $seederInputId }}"
                           {{ $seederIsSelected ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="truncate flex items-center gap-2" title="{{ $className }}">
                        <span class="truncate">{{ $displaySeederName }}</span>
                        @if($seederIsNew)
                            <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                Новий{{ !is_null($seederOrdinal) ? ' #' . $seederOrdinal : '' }}
                            </span>
                        @endif
                    </span>
                </label>
                <button type="button"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                        @click.stop="toggle()"
                        :aria-expanded="open.toString()"
                        aria-label="Перемкнути список джерел">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2 bg-white">
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
    </div>
@endif
