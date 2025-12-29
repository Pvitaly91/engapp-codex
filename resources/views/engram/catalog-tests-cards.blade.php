@extends('layouts.engram')

@section('title', __('public.catalog.title', ['default' => 'Збережені тести']))

@section('content')
<div class="space-y-8">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-brand-400 p-8 text-white shadow-card">
        {{-- Decorative background elements --}}
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-white/15 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur-sm px-4 py-2 text-sm font-semibold mb-4">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ __('public.catalog.badge', ['default' => 'Каталог тестів']) }}
            </span>
            <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ __('public.catalog.title', ['default' => 'Збережені тести']) }}</h1>
            <p class="text-white/80 max-w-2xl text-lg">{{ __('public.catalog.description', ['default' => 'Виберіть тест для практики англійської граматики']) }}</p>
            
            <div class="mt-6 flex flex-wrap gap-4">
                <div class="rounded-2xl bg-white/20 backdrop-blur-sm px-5 py-3 border border-white/20">
                    <p class="text-2xl font-bold">{{ $tests->count() }}</p>
                    <p class="text-xs uppercase tracking-wider text-white/80">{{ __('public.catalog.tests_count', ['default' => 'тестів']) }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-col md:flex-row gap-6">
        {{-- Mobile Filter Toggle --}}
        <div class="md:hidden">
            <button type="button" id="filter-toggle" class="inline-flex items-center gap-2 px-5 py-3 rounded-full border border-[var(--border)] bg-[var(--card)] text-sm font-semibold shadow-sm hover:border-brand-500 transition">
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
                </svg>
                <span>{{ __('public.catalog.filter', ['default' => 'Фільтр']) }}</span>
            </button>
        </div>
        
        {{-- Filter Sidebar --}}
        <aside id="filters" class="md:w-56 w-full md:shrink-0 hidden md:block md:sticky md:top-20 md:self-start">
            <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
                <div class="flex justify-between items-center md:hidden mb-4 pb-4 border-b border-[var(--border)]">
                    <h2 class="text-base font-semibold">{{ __('public.catalog.filter', ['default' => 'Фільтр']) }}</h2>
                    <button type="button" id="filter-close" class="text-sm text-brand-600 font-semibold hover:underline">
                        {{ __('public.common.close', ['default' => 'Закрити']) }}
                    </button>
                </div>
                
                <div class="hidden md:flex items-center gap-2 mb-4 pb-4 border-b border-[var(--border)]">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <h2 class="text-base font-semibold">{{ __('public.catalog.filter', ['default' => 'Фільтр']) }}</h2>
                </div>
                
                <form id="tag-filter" action="{{ localized_route('catalog.tests-cards') }}" method="GET">
                    @if(isset($availableLevels) && $availableLevels->count())
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-[var(--muted)] uppercase tracking-wider mb-3">{{ __('public.catalog.level', ['default' => 'Level']) }}:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableLevels as $lvl)
                                    @php $id = 'level-' . md5($lvl); @endphp
                                    <div>
                                        <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                        <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-sm font-medium bg-[var(--card)] hover:border-brand-400 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition">{{ $lvl }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @foreach($tags as $category => $tagNames)
                        @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                        @if($isOther)
                            <div class="mb-4" id="others-filter" data-open="false">
                                <h3 class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider mb-3 flex justify-between items-center">
                                    <span>{{ $category }}</span>
                                    <button type="button" id="toggle-others-btn" class="text-xs text-brand-600 font-semibold hover:underline">{{ __('public.common.show', ['default' => 'Show']) }}</button>
                                </h3>
                                <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                                    @foreach($tagNames as $tag)
                                        @php $id = 'tag-' . md5($tag); @endphp
                                        <div>
                                            <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                            <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-sm font-medium bg-[var(--card)] hover:border-brand-400 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition">{{ $tag }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="mb-4">
                                <h3 class="text-xs font-semibold text-[var(--muted)] uppercase tracking-wider mb-3">{{ $category }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tagNames as $tag)
                                        @php $id = 'tag-' . md5($tag); @endphp
                                        <div>
                                            <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                            <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-sm font-medium bg-[var(--card)] hover:border-brand-400 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition">{{ $tag }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </form>
                @if(!empty($selectedTags) || !empty($selectedLevels))
                    <div class="mt-4 pt-4 border-t border-[var(--border)]">
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 text-sm text-brand-600 font-semibold hover:underline">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ __('public.catalog.reset_filter', ['default' => 'Скинути фільтр']) }}
                        </a>
                    </div>
                @endif
            </div>
        </aside>
        
        {{-- Test Cards Grid --}}
        <div class="flex-1">
            @if($tests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($tests as $test)
                        <div class="group rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-sm hover:shadow-md hover:border-brand-500 transition flex flex-col overflow-hidden">
                            {{-- Card Header with Gradient --}}
                            <div class="bg-gradient-to-r from-brand-500 to-brand-600 p-4 text-white">
                                <div class="font-bold text-lg leading-tight line-clamp-2">{{ $test->name }}</div>
                            </div>
                            
                            {{-- Card Body --}}
                            <div class="p-4 flex-1 flex flex-col">
                                <div class="text-xs text-[var(--muted)] mb-3 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ __('public.catalog.created', ['default' => 'Створено']) }}: {{ $test->created_at->format('d.m.Y') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ __('public.catalog.questions', ['default' => 'Питань']) }}: {{ count($test->questions) }}
                                    </div>
                                    @php
                                        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                        $levels = $test->levels
                                            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                            ->map(fn($lvl) => $lvl ?? 'N/A');
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        {{ __('public.catalog.levels', ['default' => 'Рівні']) }}: {{ $levels->join(', ') }}
                                    </div>
                                </div>
                                
                                {{-- Tags --}}
                                <div class="mb-3 flex flex-wrap gap-1.5">
                                    @foreach($test->tag_names as $t)
                                        <span class="inline-block bg-brand-50 text-brand-700 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $t }}</span>
                                    @endforeach
                                </div>
                                
                                @if($test->description)
                                    <div class="text-sm text-[var(--muted)] mb-3 line-clamp-2">{{ strip_tags($test->description) }}</div>
                                @endif
                                
                                @php
                                    $preferredView = data_get($test->filters, 'preferred_view');
                                    if ($preferredView === 'drag-drop') {
                                        $testRoute = localized_route('saved-test.js.drag-drop', $test->slug);
                                    } elseif ($preferredView === 'match') {
                                        $testRoute = localized_route('saved-test.js.match', $test->slug);
                                    } else {
                                        $testRoute = localized_route('test.show', $test->slug);
                                    }
                                @endphp
                                <a href="{{ $testRoute }}" class="mt-auto inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-full text-sm font-semibold shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ __('public.catalog.start_test', ['default' => 'Пройти тест']) }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-12 text-center shadow-sm">
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                    </div>
                    <p class="text-lg font-semibold mb-2">{{ __('public.catalog.no_tests', ['default' => 'Ще немає збережених тестів']) }}</p>
                    <p class="text-[var(--muted)]">{{ __('public.catalog.no_tests_hint', ['default' => 'Спробуйте змінити параметри фільтра']) }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('#tag-filter input[type=checkbox]').forEach(el => {
        el.addEventListener('change', () => document.getElementById('tag-filter').submit());
    });
    const toggleBtn = document.getElementById('toggle-others-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const tags = document.getElementById('others-tags');
            const hidden = tags.style.display === 'none';
            tags.style.display = hidden ? '' : 'none';
            toggleBtn.textContent = hidden ? '{{ __("public.common.hide", ["default" => "Hide"]) }}' : '{{ __("public.common.show", ["default" => "Show"]) }}';
        });
    }
    const filterToggle = document.getElementById('filter-toggle');
    const filterClose = document.getElementById('filter-close');
    const filters = document.getElementById('filters');
    if (filterToggle && filters) {
        filterToggle.addEventListener('click', () => {
            filters.classList.toggle('hidden');
        });
    }
    if (filterClose && filters) {
        filterClose.addEventListener('click', () => {
            filters.classList.add('hidden');
        });
    }
</script>
@endsection
