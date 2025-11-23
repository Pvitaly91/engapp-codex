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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div id="questions-count-display" class="text-sm text-gray-500">Кількість питань: {{ count($questions) }}</div>
            <div class="flex gap-3">
                <button type="button" id="add-questions-button"
                        class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
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

        <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
            @csrf
            <div id="questions-list" class="space-y-6">
                @foreach($questions as $q)
                    <div class="question-item" data-question-id="{{ $q->id }}" data-question-save="{{ $q->{$savePayloadKey} }}">
                        <input type="hidden" name="questions[{{ $q->id }}]" value="1">
                        <div class="bg-white shadow rounded-2xl p-4 sm:p-6 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2">
                                    <span class="uppercase px-2 py-1 rounded text-xs {{ $q->category->name === 'past' ? 'bg-red-100 text-red-700' : ($q->category->name === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                        {{ ucfirst($q->category->name) }}
                                    </span>
                                    @if($q->source)
                                        <span class="text-xs text-gray-500">Source: {{ $q->source->name }}</span>
                                    @endif
                                    @php
                                        $questionSeeder = $q->seeder ?? null;
                                        if ($questionSeeder) {
                                            $questionSeeder = \Illuminate\Support\Str::after($questionSeeder, 'Database\\Seeders\\');
                                        }
                                    @endphp
                                    @if($questionSeeder)
                                        <span class="text-xs text-gray-500">Seeder: {{ $questionSeeder }}</span>
                                    @endif
                                    @if($q->flag)
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                                    @endif
                                    <span class="text-xs text-gray-400">Складність: {{ $q->difficulty }}/10</span>
                                    <span class="text-xs text-gray-400">Level: {{ $q->level ?? 'N/A' }}</span>
                                </div>
                                <span class="text-xs text-gray-400">ID: {{ $q->id }} | UUID: {{ $q->uuid ?? '—' }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2 items-baseline">
                                <span class="question-number font-bold mr-2">{{ $loop->iteration }}.</span>
                                @php preg_match_all('/\{a(\d+)\}/', $q->question, $matches); @endphp
                                @include('components.question-input', [
                                    'question' => $q,
                                    'inputNamePrefix' => "question_{$q->id}_",
                                    'manualInput' => $manualInput,
                                    'autocompleteInput' => $autocompleteInput,
                                    'builderInput' => $builderInput,
                                    'autocompleteRoute' => $autocompleteRoute,
                                ])
                            </div>
                            @if($q->tags->count())
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                                    @endphp
                                    @foreach($q->tags as $tag)
                                        <a href="{{ route('saved-tests.cards', ['tag' => $tag->name]) }}" class="inline-flex px-2 py-0.5 rounded text-xs font-semibold hover:underline {{ $colors[$loop->index % count($colors)] }}">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($checkOneInput))
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="mt-1 bg-purple-600 text-white text-xs rounded px-3 py-1 hover:bg-purple-700"
                                        onclick="checkFullQuestionAjax(this, '{{ $q->id }}', '{{ implode(',', array_map(function($n){return 'a'.$n;}, $matches[1])) }}')"
                                    >
                                        Check answer
                                    </button>
                                    <span class="text-xs font-bold" id="result-question-{{ $q->id }}"></span>
                                </div>
                            @endif
                        </div>
                    </div>
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
    @elseif(isset($questions))
        <div class="text-red-600 font-bold text-lg">Питань по вибраних параметрах не знайдено!</div>
    @endif

    <!-- Modal for Adding Questions -->
    <div x-data="questionSearchModal()" x-show="open" @keydown.escape.window="closeModal()" 
         @open-question-search-modal.window="openModal()" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                 @click="closeModal()" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div x-show="open" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">
                            Пошук та вибір питань
                        </h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Search input -->
                    <div class="mb-4">
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="search()" 
                               placeholder="Пошук по тексту питання, ID, UUID, тегах, відповідях, джерелах, сидерах..."
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Можна шукати по тексту питання, ID, UUID, тегах, відповідях, варіантах відповідей, джерелах та сидерах
                        </p>
                    </div>

                    <!-- Loading indicator -->
                    <div x-show="loading" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Results -->
                    <div x-show="!loading" class="space-y-3 max-h-96 overflow-y-auto">
                        <template x-if="results.length === 0 && searchQuery">
                            <p class="text-gray-500 text-center py-4">Питання не знайдено</p>
                        </template>
                        
                        <template x-for="question in results" :key="question.id">
                            <div class="border border-gray-200 rounded-xl p-3 hover:bg-gray-50 transition">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" :value="question.uuid" 
                                           x-model="selectedQuestions"
                                           class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800" 
                                                  x-text="question.category || 'N/A'"></span>
                                            <span class="text-xs text-gray-500">
                                                ID: <span x-text="question.id"></span>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                UUID: <span x-text="question.uuid?.substring(0, 8) || '—'"></span>
                                            </span>
                                            <template x-if="question.flag">
                                                <span class="text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                                            </template>
                                            <template x-if="question.level">
                                                <span class="text-xs text-gray-500">Level: <span x-text="question.level"></span></span>
                                            </template>
                                            <span class="text-xs text-gray-500">
                                                Складність: <span x-text="question.difficulty"></span>/10
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-800 mb-1" x-text="question.question"></div>
                                        <template x-if="question.tags && question.tags.length > 0">
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                <template x-for="tag in question.tags" :key="tag">
                                                    <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800" x-text="tag"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="question.source">
                                            <div class="text-xs text-gray-500">
                                                Джерело: <span x-text="question.source"></span>
                                            </div>
                                        </template>
                                        <template x-if="question.seeder">
                                            <div class="text-xs text-gray-500">
                                                Сидер: <span x-text="question.seeder"></span>
                                            </div>
                                        </template>
                                    </div>
                                </label>
                            </div>
                        </template>
                    </div>

                    <!-- Pagination -->
                    <div x-show="!loading && totalPages > 1" class="flex items-center justify-between mt-4 pt-4 border-t">
                        <button type="button" @click="previousPage()" :disabled="currentPage <= 1"
                                class="px-3 py-1 border rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Попередня
                        </button>
                        <span class="text-sm text-gray-600">
                            Сторінка <span x-text="currentPage"></span> з <span x-text="totalPages"></span>
                            (всього: <span x-text="total"></span>)
                        </span>
                        <button type="button" @click="nextPage()" :disabled="currentPage >= totalPages"
                                class="px-3 py-1 border rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Наступна
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" @click="addSelectedQuestions()"
                            :disabled="selectedQuestions.length === 0"
                            class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm">
                        Додати вибрані (<span x-text="selectedQuestions.length"></span>)
                    </button>
                    <button type="button" @click="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-2xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Скасувати
                    </button>
                </div>
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

function questionSearchModal() {
    return {
        open: false,
        searchQuery: '',
        results: [],
        selectedQuestions: [],
        loading: false,
        currentPage: 1,
        total: 0,
        totalPages: 0,
        
        openModal() {
            this.open = true;
            this.searchQuery = '';
            this.results = [];
            this.selectedQuestions = [];
            this.currentPage = 1;
        },
        
        closeModal() {
            this.open = false;
        },
        
        async search() {
            this.loading = true;
            this.currentPage = 1;
            await this.fetchQuestions();
        },
        
        async fetchQuestions() {
            try {
                const url = new URL('{{ route('grammar-test.search-questions') }}');
                url.searchParams.append('q', this.searchQuery);
                url.searchParams.append('page', this.currentPage);
                
                const response = await fetch(url);
                const data = await response.json();
                
                this.results = data.items || [];
                this.total = data.total || 0;
                this.totalPages = data.lastPage || 0;
            } catch (error) {
                console.error('Error fetching questions:', error);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
        
        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loading = true;
                await this.fetchQuestions();
            }
        },
        
        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loading = true;
                await this.fetchQuestions();
            }
        },
        
        async addSelectedQuestions() {
            if (this.selectedQuestions.length === 0) {
                return;
            }
            
            // Fetch full question details for selected questions
            const questionsToAdd = this.results.filter(q => 
                this.selectedQuestions.includes(q.uuid)
            );
            
            // Add questions to the list
            const container = document.getElementById('questions-list');
            if (!container) {
                console.error('Questions list container not found');
                return;
            }
            
            for (const question of questionsToAdd) {
                // Check if question already exists
                const existingQuestion = container.querySelector(`[data-question-id="${question.id}"]`);
                if (existingQuestion) {
                    console.log(`Question ${question.id} already exists, skipping`);
                    continue;
                }
                
                // Create question element
                const questionEl = this.createQuestionElement(question);
                container.appendChild(questionEl);
            }
            
            // Update question numbers and order
            updateQuestionNumbers();
            updateQuestionsOrder();
            
            // Show questions count - using more specific selector
            const countElement = document.getElementById('questions-count-display');
            if (countElement) {
                const totalQuestions = container.querySelectorAll('[data-question-id]').length;
                countElement.textContent = `Кількість питань: ${totalQuestions}`;
            }
            
            this.closeModal();
        },
        
        createQuestionElement(question) {
            const div = document.createElement('div');
            div.className = 'question-item';
            div.setAttribute('data-question-id', question.id);
            div.setAttribute('data-question-save', question.uuid);
            
            // Helper function to escape HTML
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };
            
            const categoryClass = question.category === 'past' ? 'bg-red-100 text-red-700' : 
                                 (question.category === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700');
            
            const tagColors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
            
            const tagsHtml = question.tags && question.tags.length > 0 ? `
                <div class="flex flex-wrap gap-1">
                    ${question.tags.map((tag, idx) => {
                        return `<span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold ${tagColors[idx % tagColors.length]}">${escapeHtml(tag)}</span>`;
                    }).join('')}
                </div>
            ` : '';
            
            div.innerHTML = `
                <input type="hidden" name="questions[${escapeHtml(question.id)}]" value="1">
                <div class="bg-white shadow rounded-2xl p-4 sm:p-6 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2">
                            <span class="uppercase px-2 py-1 rounded text-xs ${categoryClass}">
                                ${escapeHtml(question.category || 'N/A')}
                            </span>
                            ${question.source ? `<span class="text-xs text-gray-500">Source: ${escapeHtml(question.source)}</span>` : ''}
                            ${question.seeder ? `<span class="text-xs text-gray-500">Seeder: ${escapeHtml(question.seeder)}</span>` : ''}
                            ${question.flag ? '<span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>' : ''}
                            <span class="text-xs text-gray-400">Складність: ${escapeHtml(question.difficulty)}/10</span>
                            <span class="text-xs text-gray-400">Level: ${escapeHtml(question.level || 'N/A')}</span>
                        </div>
                        <span class="text-xs text-gray-400">ID: ${escapeHtml(question.id)} | UUID: ${escapeHtml(question.uuid || '—')}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 items-baseline">
                        <span class="question-number font-bold mr-2"></span>
                        <span class="text-base">${escapeHtml(question.question)}</span>
                    </div>
                    ${tagsHtml}
                </div>
            `;
            
            return div;
        }
    };
}

function updateQuestionNumbers() {
    const container = document.getElementById('questions-list');
    if (!container) return;
    
    const items = Array.from(container.querySelectorAll('[data-question-id]'));
    items.forEach((item, index) => {
        const numberEl = item.querySelector('.question-number');
        if (numberEl) {
            numberEl.textContent = `${index + 1}.`;
        }
    });
}

function updateQuestionsOrder() {
    const container = document.getElementById('questions-list');
    const orderInput = document.getElementById('questions-order-input');
    if (!container || !orderInput) return;
    
    const items = Array.from(container.querySelectorAll('[data-question-id]'));
    const order = items.map(item => item.dataset.questionSave);
    orderInput.value = JSON.stringify(order);
}

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
    const container = document.getElementById('questions-list');
    const shuffleButton = document.getElementById('shuffle-questions');
    const orderInput = document.getElementById('questions-order-input');
    const saveForm = document.getElementById('save-test-form');

    if (!container) {
        return;
    }

    const getItems = () => Array.from(container.querySelectorAll('[data-question-id]'));

    const updateNumbers = () => {
        getItems().forEach((item, index) => {
            const numberEl = item.querySelector('.question-number');
            if (numberEl) {
                numberEl.textContent = `${index + 1}.`;
            }
        });
    };

    const updateOrderInput = () => {
        if (!orderInput) {
            return;
        }

        const order = getItems().map(item => item.dataset.questionSave);
        orderInput.value = JSON.stringify(order);
    };

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

    updateNumbers();
    updateOrderInput();
    
    // Add event listener for the "Add Questions" button
    const addQuestionsButton = document.getElementById('add-questions-button');
    if (addQuestionsButton) {
        addQuestionsButton.addEventListener('click', () => {
            // Trigger Alpine.js modal opening using custom event
            window.dispatchEvent(new CustomEvent('open-question-search-modal'));
        });
    }
});

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
