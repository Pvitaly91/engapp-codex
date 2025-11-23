@extends('layouts.app')

@section('title', 'Grammar Test Constructor V2')

@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6">
    @php
        $autocompleteRoute = url('/api/search?lang=en');
        $checkOneRoute = route('grammar-test.checkOne');
        $generateRoute = $generateRoute ?? route('grammar-test.generate');
        $saveRoute = $saveRoute ?? route('grammar-test.save-v2');
        $savePayloadField = $savePayloadField ?? 'question_uuids';
        $savePayloadKey = $savePayloadKey ?? 'uuid';
        $questions = collect($questions ?? []);
        $tagGroups = $tagsByCategory ?? collect();
        $allTagNames = $tagGroups
            ->flatMap(fn ($tags) => collect($tags)->pluck('name'))
            ->filter(fn ($name) => filled($name))
            ->unique()
            ->values();
        $sourceGroups = $sourcesByCategory ?? collect();
        $seederClasses = collect($seederClasses ?? [])->filter(fn ($value) => filled($value))->values();
        $rawSeederGroups = collect($seederSourceGroups ?? [])->filter(fn ($group) => filled($group['seeder'] ?? null));
        $seederSourceMap = $rawSeederGroups->mapWithKeys(fn ($group) => [
            $group['seeder'] => collect($group['sources'] ?? [])->filter()->values(),
        ]);

        $selectedSourceCollection = collect($selectedSources ?? [])->filter();
        $selectedSources = $selectedSourceCollection->values()->all();

        $recentTagIds = collect($recentTagIds ?? [])->filter()->values();
        $recentCategoryIds = collect($recentCategoryIds ?? [])->filter()->values();
        $recentSourceIds = collect($recentSourceIds ?? [])->filter()->values();
        $recentSeederClasses = collect($recentSeederClasses ?? [])->filter()->values();
        $recentTagOrdinals = collect($recentTagOrdinals ?? []);
        $recentCategoryOrdinals = collect($recentCategoryOrdinals ?? []);
        $recentSourceOrdinals = collect($recentSourceOrdinals ?? []);
        $recentSeederOrdinals = collect($recentSeederOrdinals ?? []);

        $seederGroups = $seederClasses
            ->map(function ($className) use ($seederSourceMap) {
                $sources = $seederSourceMap->get($className, collect());

                return [
                    'seeder' => $className,
                    'sources' => $sources,
                ];
            })
            ->filter(fn ($group) => filled($group['seeder']))
            ->values();

        $seederSearchItems = $seederGroups
            ->map(function ($group) {
                $className = $group['seeder'] ?? '';
                $displaySeederName = \Illuminate\Support\Str::after($className, 'Database\\Seeders\\');

                if ($displaySeederName === $className) {
                    $displaySeederName = $className;
                }

                return [
                    'seeder' => $displaySeederName,
                    'sources' => collect($group['sources'] ?? [])->pluck('name')->filter()->values()->all(),
                ];
            })
            ->filter(fn ($item) => filled($item['seeder']))
            ->values();

        $hasSelectedSeederSources = $seederGroups->contains(function ($group) use ($selectedSourceCollection) {
            return collect($group['sources'] ?? [])
                ->pluck('id')
                ->intersect($selectedSourceCollection)
                ->isNotEmpty();
        });

        $selectedCategories = collect($selectedCategories ?? [])->all();
        $selectedTags = collect($selectedTags ?? [])->all();
        $selectedSeederClasses = collect($selectedSeederClasses ?? [])->filter(fn ($value) => filled($value))->values()->all();
        $normalizedFilters = $normalizedFilters ?? null;

        $hasSelectedCategories = !empty($selectedCategories);
        $hasSelectedSources = !empty($selectedSources);
        $hasSelectedTags = !empty($selectedTags);
        $hasSelectedSeederClasses = !empty($selectedSeederClasses);

        $questionSearchRoute = route('grammar-test.search-questions');
        $questionRenderRoute = route('grammar-test.render-questions');
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h1 class="text-2xl font-bold">Конструктор тесту (v2)</h1>
        <a href="{{ route('saved-tests.list') }}"
           class="inline-flex items-center justify-center bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100 transition">
            Збережені тести
        </a>
    </div>

    <form action="{{ $generateRoute }}" method="POST" class="bg-white shadow rounded-2xl p-4 sm:p-6">
        @csrf
        
            <div class="space-y-6 ">
                <div x-data="{ openSeederFilter: {{ ($hasSelectedSeederClasses || $hasSelectedSeederSources) ? 'true' : 'false' }}, toggleSeederSources(open) { this.$dispatch('toggle-all-seeder-sources', { open }); } }"
                     x-init="$store.seederSearch.setSeeders(@js($seederSearchItems))"
                     @class([
                        'space-y-3 border border-transparent rounded-2xl p-3',
                        'border-blue-300 bg-blue-50' => ($hasSelectedSeederClasses || $hasSelectedSeederSources),
                     ])
                >
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-gray-700">Клас сидера питання</h2>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openSeederFilter = !openSeederFilter">
                                <span x-text="openSeederFilter ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSeederFilter }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button type="button"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="toggleSeederSources(true)">
                                <span>Розгорнути всі</span>
                            </button>
                            <button type="button"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="toggleSeederSources(false)">
                                <span>Згорнути всі</span>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3" x-show="openSeederFilter" x-transition style="display: none;">
                        @if($seederGroups->isEmpty())
                            <p class="text-sm text-gray-500">Немає доступних класів сидера.</p>
                        @else
                            <div class="relative">
                                <input
                                    type="search"
                                    placeholder="Пошук сидерів або джерел..."
                                    x-model.debounce.200ms="$store.seederSearch.query"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    autocomplete="off"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p x-show="$store.seederSearch.query && !$store.seederSearch.hasResults()" x-cloak class="text-sm text-gray-500">Сидери або джерела не знайдені.</p>
                            @if(isset($seederGroupsByDate) && $seederGroupsByDate->isNotEmpty())
                                <div class="space-y-4">
                                    @foreach($seederGroupsByDate as $date => $dateSeederClasses)
                                        @php
                                            $dateSeederGroups = $seederGroups->whereIn('seeder', $dateSeederClasses->all());
                                        @endphp
                                        <div class="border border-gray-300 rounded-2xl p-3 bg-gray-50">
                                            <h3 class="text-sm font-bold text-gray-800 mb-3">Дата виконання: {{ $date }}</h3>
                                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 items-start">
                                                @foreach($dateSeederGroups as $group)
                                                    @php
                                                        $className = $group['seeder'];
                                                        $seederSources = collect($group['sources'] ?? []);
                                                        $seederSourceNames = $seederSources->pluck('name')->filter()->values();
                                                        $seederIsSelected = in_array($className, $selectedSeederClasses, true);
                                                        $seederSourceIds = $seederSources->pluck('id');
                                                        $seederHasSelectedSources = $seederSourceIds->intersect($selectedSourceCollection)->isNotEmpty();
                                                        $groupIsActive = $seederIsSelected || $seederHasSelectedSources;
                                                        $seederInputId = 'seeder-' . md5($className);
                                                        $displaySeederName = \Illuminate\Support\Str::after($className, 'Database\\Seeders\\');
                                                        if ($displaySeederName === $className) {
                                                            $displaySeederName = $className;
                                                        }
                                                        $seederIsNew = $recentSeederClasses->contains($className);
                                                        $seederOrdinal = $recentSeederOrdinals->get($className);
                                                        
                                                        // Get execution time for this seeder
                                                        $executionTime = isset($seederExecutionTimes) && $seederExecutionTimes->has($className) 
                                                            ? \Illuminate\Support\Carbon::parse($seederExecutionTimes->get($className))
                                                            : null;
                                                    @endphp
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
                                                         x-show="$store.seederSearch.matchesSeeder(@js($displaySeederName), @js($seederSourceNames))"
                                                         x-effect="if ($store.seederSearch.normalized() && $store.seederSearch.matchesSeeder(@js($displaySeederName), @js($seederSourceNames))) { open = true }"
                                                         x-cloak
                                                         @toggle-all-seeder-sources.window="toggle($event.detail.open)"
                                                         @class([
                                                            'border rounded-2xl overflow-hidden transition',
                                                            'border-gray-200' => ! $groupIsActive,
                                                            'border-blue-400 shadow-sm bg-blue-50' => $groupIsActive,
                                                         ])
                                                    >
                                                        <div class="flex items-start justify-between gap-3 px-4 py-2 bg-gray-50 cursor-pointer"
                                                             @click="toggle()"
                                                        >
                                                            <label for="{{ $seederInputId }}"
                                                                   @class([
                                                                        'flex flex-1 min-w-0 items-start gap-2 text-sm font-semibold text-gray-800 cursor-pointer',
                                                                        'text-blue-800' => $seederIsSelected,
                                                                   ])
                                                                   @click.stop
                                                            >
                                                                <input type="checkbox" name="seeder_classes[]" value="{{ $className }}" id="{{ $seederInputId }}"
                                                                       {{ $seederIsSelected ? 'checked' : '' }}
                                                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded flex-shrink-0">
                                                                <span class="flex min-w-0 flex-col text-left" title="{{ $displaySeederName }}">
                                                                    <span class="truncate">{{ $displaySeederName }}</span>
                                                                    @if($executionTime)
                                                                        <span class="text-[11px] text-gray-500 mt-0.5">
                                                                            {{ $executionTime->format('d.m.Y H:i:s') }}
                                                                        </span>
                                                                    @endif
                                                                    @if($seederIsNew)
                                                                        <span class="mt-1 inline-flex text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                                            Новий{{ !is_null($seederOrdinal) ? ' #' . $seederOrdinal : '' }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </label>
                                                            <button type="button"
                                                                    class="inline-flex flex-shrink-0 items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                                                                    @click.stop="toggle()"
                                                                    :aria-expanded="open.toString()"
                                                                    aria-label="Перемкнути список джерел">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
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
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 items-start">
                                        @foreach($seederGroups as $group)
                                            @php
                                                $className = $group['seeder'];
                                                $seederSources = collect($group['sources'] ?? []);
                                                $seederSourceNames = $seederSources->pluck('name')->filter()->values();
                                                $seederIsSelected = in_array($className, $selectedSeederClasses, true);
                                                $seederSourceIds = $seederSources->pluck('id');
                                                $seederHasSelectedSources = $seederSourceIds->intersect($selectedSourceCollection)->isNotEmpty();
                                        $groupIsActive = $seederIsSelected || $seederHasSelectedSources;
                                        $seederInputId = 'seeder-' . md5($className);
                                        $displaySeederName = \Illuminate\Support\Str::after($className, 'Database\\Seeders\\');
                                        if ($displaySeederName === $className) {
                                            $displaySeederName = $className;
                                        }
                                        $seederIsNew = $recentSeederClasses->contains($className);
                                        $seederOrdinal = $recentSeederOrdinals->get($className);
                                        
                                        // Get execution time for this seeder
                                        $executionTime = isset($seederExecutionTimes) && $seederExecutionTimes->has($className) 
                                            ? \Illuminate\Support\Carbon::parse($seederExecutionTimes->get($className))
                                            : null;
                                    @endphp
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
                                                 x-show="$store.seederSearch.matchesSeeder(@js($displaySeederName), @js($seederSourceNames))"
                                                 x-effect="if ($store.seederSearch.normalized() && $store.seederSearch.matchesSeeder(@js($displaySeederName), @js($seederSourceNames))) { open = true }"
                                                 x-cloak
                                                 @toggle-all-seeder-sources.window="toggle($event.detail.open)"
                                                 @class([
                                                    'border rounded-2xl overflow-hidden transition',
                                                    'border-gray-200' => ! $groupIsActive,
                                            'border-blue-400 shadow-sm bg-blue-50' => $groupIsActive,
                                         ])
                                    >
                                        <div class="flex items-start justify-between gap-3 px-4 py-2 bg-gray-50 cursor-pointer"
                                             @click="toggle()"
                                        >
                                            <label for="{{ $seederInputId }}"
                                                   @class([
                                                        'flex flex-1 min-w-0 items-start gap-2 text-sm font-semibold text-gray-800 cursor-pointer',
                                                        'text-blue-800' => $seederIsSelected,
                                                   ])
                                                   @click.stop
                                            >
                                                <input type="checkbox" name="seeder_classes[]" value="{{ $className }}" id="{{ $seederInputId }}"
                                                       {{ $seederIsSelected ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded flex-shrink-0">
                                                <span class="flex min-w-0 flex-col text-left" title="{{ $displaySeederName }}">
                                                    <span class="truncate">{{ $displaySeederName }}</span>
                                                    @if($executionTime)
                                                        <span class="text-[11px] text-gray-500 mt-0.5">
                                                            {{ $executionTime->format('d.m.Y H:i:s') }}
                                                        </span>
                                                    @endif
                                                    @if($seederIsNew)
                                                        <span class="mt-1 inline-flex text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                            Новий{{ !is_null($seederOrdinal) ? ' #' . $seederOrdinal : '' }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </label>
                                            <button type="button"
                                                    class="inline-flex flex-shrink-0 items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                                                    @click.stop="toggle()"
                                                    :aria-expanded="open.toString()"
                                                    aria-label="Перемкнути список джерел">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
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
                                @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if($sourceGroups->isNotEmpty())
                    <div x-data="{ openSources: {{ ($hasSelectedSources || $hasSelectedCategories || $errors->has('categories')) ? 'true' : 'false' }} }"
                         @class([
                            'space-y-3 border border-transparent rounded-2xl p-3',
                            'border-blue-300 bg-blue-50' => ($hasSelectedSources || $hasSelectedCategories),
                         ])
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Джерела по категоріях</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openSources = !openSources">
                                <span x-text="openSources ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSources }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="openSources" x-transition style="display: none;">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 items-start">
                                @foreach($sourceGroups as $group)
                                    @php
                                        $category = $group['category'];
                                        $categoryId = $category->id;
                                        $groupSourceIds = collect($group['sources'])->pluck('id');
                                        $groupHasSelectedSources = $groupSourceIds->intersect($selectedSources)->isNotEmpty();
                                        $categoryIsSelected = in_array($categoryId, $selectedCategories);
                                        $groupIsActive = $groupHasSelectedSources || $categoryIsSelected;
                                        $categoryInputId = 'category-' . $categoryId;
                                        $categoryIsNew = $recentCategoryIds->contains($categoryId);
                                        $categoryOrdinal = $recentCategoryOrdinals->get($categoryId);
                                    @endphp
                                    <div x-data="{ open: {{ $groupIsActive ? 'true' : 'false' }} }"
                                         @class([
                                            'border rounded-2xl overflow-hidden transition',
                                            'border-gray-200' => ! $groupIsActive,
                                            'border-blue-400 shadow-sm bg-blue-50' => $groupIsActive,
                                         ])
                                    >
                                        <div class="flex items-start justify-between gap-3 px-4 py-2 bg-gray-50">
                                            <label for="{{ $categoryInputId }}"
                                                   @class([
                                                        'flex flex-1 min-w-0 items-start gap-2 text-sm font-semibold text-gray-800 cursor-pointer',
                                                        'text-blue-800' => $categoryIsSelected,
                                                   ])>
                                                <input type="checkbox" name="categories[]" value="{{ $categoryId }}" id="{{ $categoryInputId }}"
                                                       {{ $categoryIsSelected ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded flex-shrink-0">
                                                <span class="flex min-w-0 items-start gap-2" title="{{ ucfirst($category->name) }} (ID: {{ $categoryId }})">
                                                    <span class="truncate">{{ ucfirst($category->name) }} (ID: {{ $categoryId }})</span>
                                                    @if($categoryIsNew)
                                                        <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                            Новий{{ !is_null($categoryOrdinal) ? ' #' . $categoryOrdinal : '' }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </label>
                                            <button type="button"
                                                    class="inline-flex flex-shrink-0 items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-blue-100"
                                                    @click="open = !open"
                                                    aria-label="Перемкнути список джерел">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($group['sources'] as $source)
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
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('categories')
                                <div class="text-red-500 text-sm mt-3">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif

                @if(isset($levels) && count($levels))
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Level</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($levels as $lvl)
                                @php $levelId = 'level-' . md5($lvl); @endphp
                                <div>
                                    <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $levelId }}"
                                           class="hidden peer"
                                           {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $levelId }}" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white">
                                        {{ $lvl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($questionTypeOptions))
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Тип питання</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($questionTypeOptions as $value => $label)
                                @php $typeId = 'question-type-' . md5($value); @endphp
                                <div>
                                    <input type="checkbox" name="question_types[]" value="{{ $value }}" id="{{ $typeId }}"
                                           class="hidden peer"
                                           {{ in_array((string) $value, $selectedQuestionTypes ?? [], true) ? 'checked' : '' }}>
                                    <label for="{{ $typeId }}"
                                           class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-indigo-600 peer-checked:text-white">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($tagGroups->isNotEmpty())
                    <div x-data="{ openTags: {{ $hasSelectedTags ? 'true' : 'false' }} }"
                         x-init="$store.tagSearch.setAllTags(@js($allTagNames))"
                         @class([
                            'space-y-3 border border-transparent rounded-2xl p-3',
                            'border-blue-300 bg-blue-50' => $hasSelectedTags,
                         ])
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Tags</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openTags = !openTags">
                                <span x-text="openTags ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openTags }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3" x-show="openTags" x-transition style="display: none;">
                            <div class="relative">
                                <input
                                    type="search"
                                    placeholder="Пошук тегів..."
                                    x-model.debounce.200ms="$store.tagSearch.query"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    autocomplete="off"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p x-show="$store.tagSearch.query && !$store.tagSearch.hasResults()" x-cloak class="text-sm text-gray-500">Теги не знайдено.</p>
                            @foreach($tagGroups as $tagCategory => $tags)
                                @php
                                    $categoryTagNames = collect($tags)->pluck('name');
                                    $tagCategoryHasSelected = $categoryTagNames->intersect($selectedTags)->isNotEmpty();
                                    $tagCategoryHasRecent = collect($tags)->contains(fn ($tag) => $recentTagIds->contains($tag->id));
                                @endphp
                                <div x-data="{ open: {{ ($tagCategoryHasSelected || $loop->first) ? 'true' : 'false' }}, categoryTags: @js($categoryTagNames) }"
                                     x-show="$store.tagSearch.matchesAny(categoryTags)"
                                     x-effect="if ($store.tagSearch.normalized() && $store.tagSearch.matchesAny(categoryTags)) { open = true }"
                                     x-cloak
                                     @class([
                                        'border rounded-2xl overflow-hidden transition',
                                        'border-gray-200' => ! $tagCategoryHasSelected && ! $tagCategoryHasRecent,
                                        'border-amber-300 bg-amber-50 shadow-sm' => $tagCategoryHasRecent && ! $tagCategoryHasSelected,
                                        'border-blue-400 shadow-sm bg-blue-50' => $tagCategoryHasSelected,
                                        'ring-2 ring-amber-300' => $tagCategoryHasRecent && $tagCategoryHasSelected,
                                     ])
                                >
                                    <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                        <span class="flex items-center gap-2">
                                            <span>{{ $tagCategory }}</span>
                                            @if($tagCategoryHasRecent)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700">
                                                    Нові теги
                                                </span>
                                            @endif
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($tags as $tag)
                                                @php $tagId = 'tag-' . md5($tag->id . '-' . $tag->name); @endphp
                                                @php $tagIsNew = $recentTagIds->contains($tag->id); @endphp
                                                @php $tagOrdinal = $recentTagOrdinals->get($tag->id); @endphp
                                                <div x-show="$store.tagSearch.matches(@js($tag->name))" x-cloak>
                                                    <input type="checkbox" name="tags[]" value="{{ $tag->name }}" id="{{ $tagId }}" class="hidden peer"
                                                           {{ in_array($tag->name, $selectedTags) ? 'checked' : '' }}>
                                                    <label for="{{ $tagId }}" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white flex items-center gap-2">
                                                        <span>{{ $tag->name }}</span>
                                                        @if($tagIsNew)
                                                            <span class="text-[10px] uppercase font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                                Новий{{ !is_null($tagOrdinal) ? ' #' . $tagOrdinal : '' }}
                                                            </span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($aggregatedTagsByCategory) && $aggregatedTagsByCategory->isNotEmpty())
                    @php
                        $selectedAggregatedTags = collect($selectedAggregatedTags ?? [])->all();
                        $hasSelectedAggregatedTags = !empty($selectedAggregatedTags);
                        $allAggregatedTagNames = $aggregatedTagsByCategory
                            ->flatMap(fn ($tags) => $tags)
                            ->filter(fn ($name) => filled($name))
                            ->unique()
                            ->values();
                    @endphp
                    <div x-data="{ openAggregatedTags: {{ $hasSelectedAggregatedTags ? 'true' : 'false' }} }"
                         x-init="$store.aggregatedTagSearch.setAllTags(@js($allAggregatedTagNames))"
                         @class([
                            'space-y-3 border border-transparent rounded-2xl p-3',
                            'border-blue-300 bg-blue-50' => $hasSelectedAggregatedTags,
                         ])
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Агреговані теги</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openAggregatedTags = !openAggregatedTags">
                                <span x-text="openAggregatedTags ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openAggregatedTags }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3" x-show="openAggregatedTags" x-transition style="display: none;">
                            <div class="relative">
                                <input
                                    type="search"
                                    placeholder="Пошук агрегованих тегів..."
                                    x-model.debounce.200ms="$store.aggregatedTagSearch.query"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    autocomplete="off"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p x-show="$store.aggregatedTagSearch.query && !$store.aggregatedTagSearch.hasResults()" x-cloak class="text-sm text-gray-500">Агреговані теги не знайдено.</p>
                            @foreach($aggregatedTagsByCategory as $tagCategory => $tags)
                                @php
                                    $categoryTagNames = collect($tags);
                                    $tagCategoryHasSelected = $categoryTagNames->intersect($selectedAggregatedTags)->isNotEmpty();
                                @endphp
                                <div x-data="{ open: {{ ($tagCategoryHasSelected || $loop->first) ? 'true' : 'false' }}, categoryTags: @js($categoryTagNames) }"
                                     x-show="$store.aggregatedTagSearch.matchesAny(categoryTags)"
                                     x-effect="if ($store.aggregatedTagSearch.normalized() && $store.aggregatedTagSearch.matchesAny(categoryTags)) { open = true }"
                                     x-cloak
                                     @class([
                                        'border rounded-2xl overflow-hidden transition',
                                        'border-gray-200' => ! $tagCategoryHasSelected,
                                        'border-blue-400 shadow-sm bg-blue-50' => $tagCategoryHasSelected,
                                     ])
                                >
                                    <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                        <span>{{ $tagCategory }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($tags as $tagName)
                                                @php $tagId = 'aggregated-tag-' . md5($tagName); @endphp
                                                <div x-show="$store.aggregatedTagSearch.matches(@js($tagName))" x-cloak>
                                                    <input type="checkbox" name="aggregated_tags[]" value="{{ $tagName }}" id="{{ $tagId }}" class="hidden peer"
                                                           {{ in_array($tagName, $selectedAggregatedTags) ? 'checked' : '' }}>
                                                    <label for="{{ $tagId }}" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white flex items-center gap-2">
                                                        <span>{{ $tagName }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            
                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Кількість питань</label>
                        <input type="number" min="1" max="{{ $maxQuestions ?? $maxQuestions }}" name="num_questions"
                               value="{{ $numQuestions ?? $maxQuestions }}" class="border rounded-lg px-3 py-2 w-full">
                        @if(isset($maxQuestions))
                            <p class="text-xs text-gray-500 mt-1">Доступно: {{ $maxQuestions }}</p>
                        @endif
                    </div>

                    @if(($canRandomizeFiltered ?? false) || !empty($randomizeFiltered))
                        <label class="flex items-start gap-3 p-3 border border-blue-200 rounded-2xl bg-blue-50">
                            <input type="checkbox" name="randomize_filtered" value="1"
                                   class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded"
                                   {{ !empty($randomizeFiltered) ? 'checked' : '' }}>
                            <span>
                                <span class="block font-semibold">Рандомні питання по фільтру</span>
                                <span class="block text-xs text-gray-600">При генерації підбиратимуться випадкові питання,
                                    якщо їх більше за вказану кількість.</span>
                            </span>
                        </label>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2"
                     x-data="{
                        manual: {{ !empty($manualInput) ? 'true' : 'false' }},
                        auto: {{ !empty($autocompleteInput) ? 'true' : 'false' }},
                        checkOne: {{ !empty($checkOneInput) ? 'true' : 'false' }},
                        builder: {{ !empty($builderInput) ? 'true' : 'false' }}
                     }"
                     x-init="if (!manual) { auto = false; builder = false; }"
                >
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="manual_input" value="1"
                               class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded"
                               x-model="manual"
                               @change="if (!manual) { auto = false; builder = false; }"
                               {{ !empty($manualInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Ввести відповідь вручну</span>
                            <span class="block text-xs text-gray-500">Дає можливість вводити відповіді самостійно</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="autocomplete_input" value="1"
                               class="mt-1 h-5 w-5 text-green-600 border-gray-300 rounded"
                               x-model="auto"
                               :disabled="!manual"
                               {{ !empty($autocompleteInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Автозаповнення відповідей</span>
                            <span class="block text-xs text-gray-500">Підставляє правильні відповіді при ручному вводі</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="check_one_input" value="1"
                               class="mt-1 h-5 w-5 text-purple-600 border-gray-300 rounded"
                               x-model="checkOne"
                               {{ !empty($checkOneInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Перевіряти окремо</span>
                            <span class="block text-xs text-gray-500">Дозволяє перевіряти питання одне за одним</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="builder_input" value="1"
                               class="mt-1 h-5 w-5 text-emerald-600 border-gray-300 rounded"
                               x-model="builder"
                               :disabled="!manual"
                               {{ !empty($builderInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Вводити по словах</span>
                            <span class="block text-xs text-gray-500">Розбиває відповідь на окремі слова</span>
                        </span>
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai" value="1"
                               class="mt-1 h-5 w-5 text-yellow-600 border-gray-300 rounded"
                               {{ !empty($includeAi) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Додати AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Включає питання з прапорцем AI</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai" value="1"
                               class="mt-1 h-5 w-5 text-orange-600 border-gray-300 rounded"
                               {{ !empty($onlyAi) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Тільки AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Обмежує вибір лише AI питаннями</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-sky-600 border-gray-300 rounded"
                               {{ !empty($includeAiV2) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Додати AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Додає питання з новим AI прапорцем</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-cyan-600 border-gray-300 rounded"
                               {{ !empty($onlyAiV2) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Тільки AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Залишає лише питання з прапорцем 2</span>
                        </span>
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                        Згенерувати тест
                    </button>
                </div>
            </div>
       
    </form>

    @if(!empty($questions) && count($questions))
        <div x-data="questionPicker(@js($questionSearchRoute), @js($questionRenderRoute), {
            manualInput: {{ !empty($manualInput) ? 'true' : 'false' }},
            autocompleteInput: {{ !empty($autocompleteInput) ? 'true' : 'false' }},
            builderInput: {{ !empty($builderInput) ? 'true' : 'false' }},
            checkOneInput: {{ !empty($checkOneInput) ? 'true' : 'false' }},
            savePayloadKey: '{{ $savePayloadKey }}'
        })" x-init="init()" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-sm text-gray-500" id="question-count">Кількість питань: {{ count($questions) }}</div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="open = true"
                            class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
                        Додати питання
                    </button>
                    @if(count($questions) > 1)
                        <button type="button" id="shuffle-questions"
                                class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
                            Перемішати питання
                        </button>
                    @endif
                </div>
            </div>

            <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center px-4" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="close()"></div>
                <div class="relative z-10 w-full max-w-5xl bg-white rounded-2xl shadow-xl p-4 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-bold text-gray-800">Додати питання до тесту</h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700" @click="close()">&times;</button>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="relative">
                            <input type="search" x-model.debounce.300ms="query" placeholder="Пошук за текстом, тегами, сидером, джерелом, ID або UUID"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                   autocomplete="off">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Пошук у тексті питання, відповідях, тегах, сидерах, джерелах та варіантах.</span>
                            <span x-text="selectionLabel()"></span>
                        </div>
                        <div class="border border-gray-200 rounded-2xl divide-y max-h-[60vh] overflow-auto" x-ref="results">
                            <template x-if="loading">
                                <div class="p-4 text-sm text-gray-500">Завантаження...</div>
                            </template>
                            <template x-if="!loading && results.length === 0">
                                <div class="p-4 text-sm text-gray-500">Нічого не знайдено.</div>
                            </template>
                            <template x-for="item in results" :key="item.id + '-' + (item.uuid || '')">
                                <label class="flex items-start gap-3 p-4 hover:bg-gray-50 transition cursor-pointer">
                                    <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded" :checked="isSelected(item)" @change="toggle(item)">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span class="font-semibold text-gray-700" x-html="highlightText('ID: ' + item.id)"></span>
                                            <template x-if="item.uuid"><span class="text-gray-500" x-html="highlightText('UUID: ' + item.uuid)"></span></template>
                                            <template x-if="item.seeder"><span class="px-2 py-0.5 rounded-full bg-gray-100" x-html="highlightText(item.seeder)"></span></template>
                                            <template x-if="item.source"><span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-html="highlightText(item.source)"></span></template>
                                            <template x-if="item.level"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700" x-html="highlightText('Level: ' + item.level)"></span></template>
                                            <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700" x-html="highlightText('Складність: ' + item.difficulty)"></span>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800" x-html="renderQuestionPreview(item)"></p>
                                        <div class="flex flex-col gap-1 text-xs text-gray-600" x-show="item.answers && item.answers.length" x-cloak>
                                            <span class="font-semibold text-gray-700">Відповіді:</span>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="answer in item.answers" :key="(answer.marker || '') + (answer.text || '')">
                                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800" x-html="highlightText(answer.text || '')"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1 text-[11px] text-gray-600" x-show="item.options && item.options.length" x-cloak>
                                            <span class="font-semibold text-gray-700">Варіанти:</span>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="option in item.options" :key="option">
                                                    <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800" x-html="highlightText(option)"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-1" x-show="item.tags && item.tags.length" x-cloak>
                                            <template x-for="tag in item.tags" :key="tag">
                                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700" x-html="highlightText(tag)"></span>
                                            </template>
                                        </div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:justify-end gap-2">
                        <button type="button" class="inline-flex justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-2xl font-semibold" @click="close()">Скасувати</button>
                        <button type="button" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-2xl font-semibold disabled:opacity-60 disabled:cursor-not-allowed" :disabled="selected.length === 0 || loading" @click="apply()">Додати вибрані</button>
                    </div>
                </div>
            </div>

        <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
            @csrf
            <div id="questions-list" class="space-y-6">
                @foreach($questions as $q)
                    @include('components.grammar-test-question-item', [
                        'question' => $q,
                        'savePayloadKey' => $savePayloadKey,
                        'manualInput' => $manualInput,
                        'autocompleteInput' => $autocompleteInput,
                        'builderInput' => $builderInput,
                        'autocompleteRoute' => $autocompleteRoute,
                        'checkOneInput' => $checkOneInput,
                        'number' => $loop->iteration,
                    ])
                @endforeach
            </div>

            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                    Перевірити
                </button>
            </div>
        </form>

        <div class="bg-white shadow rounded-2xl p-4 sm:p-6">
            <form action="{{ $saveRoute }}" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-3" id="save-test-form">
                @csrf
                @php
                    $filtersForSave = $normalizedFilters ?? [
                        'categories' => $selectedCategories,
                        'difficulty_from' => $difficultyFrom,
                        'difficulty_to' => $difficultyTo,
                        'num_questions' => $numQuestions,
                        'manual_input' => (bool) $manualInput,
                        'autocomplete_input' => (bool) $autocompleteInput,
                        'check_one_input' => (bool) $checkOneInput,
                        'builder_input' => (bool) $builderInput,
                        'include_ai' => (bool) ($includeAi ?? false),
                        'only_ai' => (bool) ($onlyAi ?? false),
                        'include_ai_v2' => (bool) ($includeAiV2 ?? false),
                        'only_ai_v2' => (bool) ($onlyAiV2 ?? false),
                        'levels' => $selectedLevels ?? [],
                        'tags' => $selectedTags,
                        'sources' => $selectedSources,
                        'seeder_classes' => $selectedSeederClasses,
                        'randomize_filtered' => (bool) ($randomizeFiltered ?? false),
                    ];
                @endphp
                <input type="hidden" name="filters" value="{{ htmlentities(json_encode($filtersForSave)) }}">
                <input type="hidden" name="{{ $savePayloadField }}" id="questions-order-input" value="{{ htmlentities(json_encode($questions->pluck($savePayloadKey))) }}">
                <input type="text" name="name" value="{{ $autoTestName }}" placeholder="Назва тесту" required autocomplete="off"
                       class="border rounded-lg px-3 py-2 w-full sm:w-80">
                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" name="save_mode" value="questions" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-2xl shadow font-semibold transition">
                        Зберегти тест
                    </button>
                    <button type="submit" name="save_mode" value="filters" class="inline-flex justify-center bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-6 py-2 rounded-2xl shadow font-semibold transition">
                        Зберегти фільтр
                    </button>
                </div>
            </form>
        </div>
        </div>
    @elseif(isset($questions))
        <div class="text-red-600 font-bold text-lg">Питань по вибраних параметрах не знайдено!</div>
    @endif

    {{-- Confirmation Modal for Question Deletion --}}
    <div
        id="question-delete-confirmation-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="question-delete-confirmation-title"
    >
        <div class="absolute inset-0 bg-black/50" data-modal-overlay></div>
        <div class="relative w-full max-w-md space-y-5 rounded-2xl bg-white px-6 py-5 shadow-xl mx-4">
            <div class="space-y-2">
                <h2 id="question-delete-confirmation-title" class="text-lg font-semibold text-gray-800">Видалити питання?</h2>
                <p class="text-sm text-gray-600">Ви впевнені, що хочете видалити це питання з тесту? Цю дію не можна буде скасувати.</p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-2xl bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition"
                    data-modal-cancel
                >
                    Скасувати
                </button>
                <button
                    type="button"
                    class="rounded-2xl bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 transition"
                    data-modal-confirm
                >
                    Видалити
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('seederSearch', {
        query: '',
        all: [],
        setSeeders(items) {
            if (!Array.isArray(items)) {
                this.all = [];
                return;
            }

            this.all = items.map(item => ({
                seeder: (item && item.seeder) || '',
                sources: Array.isArray(item && item.sources) ? item.sources : [],
            }));
        },
        normalized() {
            return this.query.trim().toLowerCase();
        },
        matchesText(text) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return String(text || '').toLowerCase().includes(normalized);
        },
        matchesSeeder(seederName, sourceNames = []) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            if (this.matchesText(seederName)) {
                return true;
            }

            return (Array.isArray(sourceNames) ? sourceNames : []).some(source => this.matchesText(source));
        },
        hasResults() {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return this.all.some(item => this.matchesSeeder(item.seeder, item.sources));
        },
    });

    Alpine.store('tagSearch', {
        query: '',
        all: [],
        setAllTags(tags) {
            this.all = Array.isArray(tags) ? tags : [];
        },
        normalized() {
            return this.query.trim().toLowerCase();
        },
        matches(tagName) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return String(tagName || '').toLowerCase().includes(normalized);
        },
        matchesAny(tagNames) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return (Array.isArray(tagNames) ? tagNames : []).some(tag => this.matches(tag));
        },
        hasResults() {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return this.matchesAny(this.all);
        },
    });

    Alpine.store('aggregatedTagSearch', {
        query: '',
        all: [],
        setAllTags(tags) {
            this.all = Array.isArray(tags) ? tags : [];
        },
        normalized() {
            return this.query.trim().toLowerCase();
        },
        matches(tagName) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return String(tagName || '').toLowerCase().includes(normalized);
        },
        matchesAny(tagNames) {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return (Array.isArray(tagNames) ? tagNames : []).some(tag => this.matches(tag));
        },
        hasResults() {
            const normalized = this.normalized();

            if (!normalized) {
                return true;
            }

            return this.matchesAny(this.all);
        },
    });
});

function checkFullQuestionAjax(btn, questionId, markerList) {
    let markers = markerList.split(',');
    let answers = {};
    markers.forEach(function(marker) {
        let input = btn.closest('.bg-white').querySelector(`[name="question_${questionId}_${marker}"]`);
        answers[marker] = input ? input.value : '';
    });
    let resultSpan = document.getElementById(`result-question-${questionId}`);
    let empty = Object.values(answers).some(val => !val);
    if(empty) {
        resultSpan.textContent = 'Введіть всі відповіді';
        resultSpan.className = 'text-xs font-bold text-gray-500';
        return;
    }
    resultSpan.textContent = '...';
    resultSpan.className = 'text-xs font-bold text-gray-500';
    fetch("{{ $checkOneRoute }}", {
        method: "POST",
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: JSON.stringify({
            question_id: questionId,
            answers: answers
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.result === 'correct') {
            resultSpan.textContent = '✔ Вірно';
            resultSpan.className = 'text-xs font-bold text-green-700';
        } else if(data.result === 'incorrect') {
            let corrects = Object.values(data.correct).join(', ');
            resultSpan.textContent = '✘ Невірно (правильно: ' + corrects + ')';
            resultSpan.className = 'text-xs font-bold text-red-700';
        } else {
            resultSpan.textContent = '—';
            resultSpan.className = 'text-xs font-bold text-gray-500';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const manager = createQuestionsManager();
    manager.init();
});

function createQuestionsManager() {
    const container = document.getElementById('questions-list');
    const shuffleButton = document.getElementById('shuffle-questions');
    const orderInput = document.getElementById('questions-order-input');
    const saveForm = document.getElementById('save-test-form');

    const getItems = () => Array.from(container ? container.querySelectorAll('[data-question-id]') : []);

    const updateNumbers = () => {
        getItems().forEach((item, index) => {
            const numberEl = item.querySelector('.question-number');
            if (numberEl) {
                numberEl.textContent = `${index + 1}.`;
            }
        });

        const countLabel = document.getElementById('question-count');
        if (countLabel) {
            countLabel.textContent = `Кількість питань: ${getItems().length}`;
        }
    };

    const updateOrderInput = () => {
        if (!orderInput) {
            return;
        }

        const order = getItems().map(item => item.dataset.questionSave);
        orderInput.value = JSON.stringify(order);
    };

    const appendHtml = (html) => {
        if (!container || !html) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;

        const newItems = Array.from(wrapper.querySelectorAll('.question-item'));
        newItems.forEach(item => container.appendChild(item));

        updateNumbers();
        updateOrderInput();
    };

    const showDeleteModal = (questionItem) => {
        const modal = document.getElementById('question-delete-confirmation-modal');
        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const confirmBtn = modal.querySelector('[data-modal-confirm]');
        const cancelBtn = modal.querySelector('[data-modal-cancel]');
        const overlay = modal.querySelector('[data-modal-overlay]');

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', closeModal);
            overlay.removeEventListener('click', closeModal);
        };

        const handleConfirm = () => {
            questionItem.remove();
            updateNumbers();
            updateOrderInput();
            closeModal();
        };

        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);
    };

    const removeQuestion = (button) => {
        const questionItem = button.closest('.question-item');
        if (!questionItem) {
            return;
        }

        showDeleteModal(questionItem);
    };

    const init = () => {
        if (!container) {
            return;
        }

        // Add event listener for remove buttons
        container.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.remove-question-btn');
            if (removeBtn) {
                event.preventDefault();
                removeQuestion(removeBtn);
            }
        });

        if (shuffleButton) {
            shuffleButton.addEventListener('click', () => {
                const items = getItems();

                if (items.length <= 1) {
                    return;
                }

                for (let i = items.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [items[i], items[j]] = [items[j], items[i]];
                }

                items.forEach(item => container.appendChild(item));
                updateNumbers();
                updateOrderInput();
            });
        }

        if (saveForm) {
            saveForm.addEventListener('submit', updateOrderInput);
        }

        window.addEventListener('grammar-test:add-questions', (event) => {
            appendHtml(event.detail && event.detail.html ? event.detail.html : '');
        });

        updateNumbers();
        updateOrderInput();
    };

    return { init, updateNumbers, updateOrderInput, appendHtml };
}

function questionPicker(searchUrl, renderUrl, config = {}) {
    return {
        open: false,
        query: '',
        loading: false,
        results: [],
        selected: [],
        init() {
            this.$watch('query', () => {
                if (this.open) {
                    this.fetchResults();
                }
            });

            this.$watch('open', (value) => {
                if (value) {
                    this.fetchResults();
                }
            });
        },
        close() {
            this.open = false;
        },
        selectionLabel() {
            if (!this.selected.length) {
                return 'Не вибрано';
            }

            return `Вибрано: ${this.selected.length}`;
        },
        normalizedTerms() {
            return String(this.query || '')
                .toLowerCase()
                .split(/\s+/)
                .map(t => t.trim())
                .filter(Boolean);
        },
        escapeHtml(text = '') {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        escapeRegExp(string) {
            return String(string).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },
        highlightText(text = '') {
            return this.applyTermHighlight(this.escapeHtml(text));
        },
        applyTermHighlight(html = '') {
            const terms = this.normalizedTerms();

            if (!terms.length || !html) {
                return html;
            }

            let highlighted = html;

            terms.forEach(term => {
                const regex = new RegExp(`(${this.escapeRegExp(term)})`, 'gi');
                highlighted = highlighted.replace(regex, '<mark>$1</mark>');
            });

            return highlighted;
        },
        highlightAnswers(html = '', answers = []) {
            if (!html) {
                return html;
            }

            let rendered = html;

            (Array.isArray(answers) ? answers : []).forEach(answer => {
                const text = this.escapeHtml(answer?.text || '');

                if (!text) {
                    return;
                }

                const regex = new RegExp(`(${this.escapeRegExp(text)})`, 'gi');
                rendered = rendered.replace(
                    regex,
                    '<span class="bg-amber-100 text-amber-900 font-semibold px-1 rounded">$1</span>'
                );
            });

            return rendered;
        },
        renderQuestionPreview(item) {
            const base = this.escapeHtml(item?.rendered_question || item?.question || '');
            const withAnswers = this.highlightAnswers(base, item?.answers || []);

            return this.applyTermHighlight(withAnswers);
        },
        isSelected(item) {
            return this.selected.some(sel => sel.id === item.id && sel.uuid === item.uuid);
        },
        toggle(item) {
            if (this.isSelected(item)) {
                this.selected = this.selected.filter(sel => !(sel.id === item.id && sel.uuid === item.uuid));
            } else {
                this.selected = [...this.selected, item];
            }
        },
        fetchResults() {
            this.loading = true;
            fetch(`${searchUrl}?q=${encodeURIComponent(this.query || '')}`)
                .then(res => res.json())
                .then(data => {
                    this.results = Array.isArray(data.items) ? data.items : [];
                })
                .catch(() => {
                    this.results = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        apply() {
            if (!this.selected.length) {
                return;
            }

            this.loading = true;

            const payload = {
                question_ids: this.selected.map(item => item.id),
                question_uuids: this.selected.map(item => item.uuid).filter(Boolean),
                manual_input: !!config.manualInput,
                autocomplete_input: !!config.autocompleteInput,
                builder_input: !!config.builderInput,
                check_one_input: !!config.checkOneInput,
                save_payload_key: config.savePayloadKey || 'uuid',
            };

            fetch(renderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            })
                .then(res => res.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('grammar-test:add-questions', {
                        detail: { html: (data && data.html) || '' },
                    }));
                    this.selected = [];
                    this.query = '';
                    this.close();
                })
                .catch(() => {})
                .finally(() => {
                    this.loading = false;
                });
        },
    };
}

function builder(route, prefix) {
    const stored = [];
    for (let i = 0; ; i++) {
        const key = storageKey(`${prefix}${i}]`);
        const val = localStorage.getItem(key);
        if (val === null) break;
        stored.push(val);
    }
    if (stored.length === 0) stored.push('');
    return {
        words: stored,
        suggestions: stored.map(() => []),
        valid: stored.map(() => false),
        addWord() {
            this.words.push('');
            this.suggestions.push([]);
            this.valid.push(false);
        },
        removeWord() {
            if (this.words.length > 1) {
                this.words.pop();
                this.suggestions.pop();
                this.valid.pop();
            }
        },
        completeWord(index) {
            if (this.words[index].trim() !== '' && this.valid[index]) {
                if (index === this.words.length - 1) {
                    this.addWord();
                }
                this.$nextTick(() => {
                    const fields = this.$el.querySelectorAll(`input[name^="${prefix}"]`);
                    if (fields[index + 1]) {
                        fields[index + 1].focus();
                    }
                });
            }
        },
        fetchSuggestions(index) {
            const query = this.words[index];
            this.valid[index] = false;
            if (query.length === 0) {
                this.suggestions[index] = [];
                return;
            }
            fetch(route + '&q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    this.suggestions[index] = data.map(i => i.en);
                });
        },
        selectSuggestion(index, val) {
            this.words[index] = val;
            this.valid[index] = true;
            this.suggestions[index] = [];
        }
    }
}
</script>
@endsection
