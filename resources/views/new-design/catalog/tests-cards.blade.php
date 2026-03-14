@extends('layouts.new-design')

@section('title', 'Каталог тестів')

@section('content')
@php
    $routePrefix = 'new-design.catalog';
@endphp

<div class="space-y-8">

    {{-- ─────────────── HERO ─────────────── --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-[linear-gradient(135deg,#1a3d6e_0%,#2f67b1_50%,#1a3d6e_100%)] text-white shadow-panel">
        {{-- Dot pattern --}}
        <div class="pointer-events-none absolute inset-0 opacity-20">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="nd-cat-dots" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" opacity="0.5"/>
                        <circle cx="12" cy="12" r="1.5" fill="currentColor" opacity="0.3"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#nd-cat-dots)"/>
            </svg>
        </div>
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-amber/20 blur-3xl"></div>

        <div class="relative px-8 py-12 md:px-12 md:py-16">
            <div class="max-w-3xl">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Каталог тестів
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-medium">
                        {{ $tests->count() }} тестів
                    </span>
                </div>
                <h1 class="font-display mb-4 text-3xl font-extrabold leading-tight tracking-tight md:text-4xl lg:text-5xl">
                    Каталог граматичних тестів
                </h1>
                <p class="max-w-2xl text-lg leading-relaxed text-white/85">
                    Обирайте тести з різних граматичних тем і рівнів складності. Перевір свої знання вже зараз!
                </p>
            </div>
        </div>
    </header>

    {{-- ─────────────── MAIN LAYOUT (sidebar + grid) ─────────────── --}}
    <div class="flex flex-col gap-6 md:flex-row">

        {{-- ── Mobile filter toggle ── --}}
        <div class="md:hidden">
            <button
                type="button"
                id="nd-filter-toggle"
                class="inline-flex items-center gap-2 rounded-xl border border-line bg-shell px-4 py-2 text-sm font-semibold shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:border-ocean"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd"/>
                </svg>
                Фільтр
            </button>
        </div>

        {{-- ── Sidebar filters ── --}}
        <aside
            id="nd-filters"
            class="w-full md:w-64 md:shrink-0 hidden md:block"
        >
            <div class="sticky top-24 space-y-4">
                {{-- Close button for mobile --}}
                <div class="flex items-center justify-between md:hidden">
                    <h2 class="text-base font-bold text-night">Фільтр</h2>
                    <button type="button" id="nd-filter-close" class="text-sm font-semibold text-ocean underline">Закрити</button>
                </div>

                <form id="nd-tag-filter" action="{{ localized_route('new-design.catalog.tests-cards') }}" method="GET" class="space-y-4">

                    {{-- Levels --}}
                    @if(isset($availableLevels) && $availableLevels->count())
                        <div class="nd-card p-4">
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-ocean">Рівень:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableLevels as $lvl)
                                    @php $id = 'nd-level-' . md5($lvl); @endphp
                                    <div>
                                        <input
                                            type="checkbox"
                                            name="levels[]"
                                            value="{{ $lvl }}"
                                            id="{{ $id }}"
                                            class="hidden peer"
                                            {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}
                                        >
                                        <label
                                            for="{{ $id }}"
                                            class="cursor-pointer rounded-full border border-line bg-shell px-3 py-1.5 text-sm transition hover:border-ocean peer-checked:border-ocean peer-checked:bg-ocean peer-checked:text-white"
                                        >{{ $lvl }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tag categories --}}
                    @foreach($tags as $category => $tagNames)
                        @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                        <div class="nd-card p-4">
                            @if($isOther)
                                <div id="nd-others-filter">
                                    <h3 class="mb-3 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-ocean">
                                        <span>{{ $category }}</span>
                                        <button type="button" id="nd-toggle-others-btn" class="text-xs font-semibold text-ocean underline">Показати</button>
                                    </h3>
                                    <div id="nd-others-tags" class="flex flex-wrap gap-2" style="display:none;">
                                        @foreach($tagNames as $tag)
                                            @php $id = 'nd-tag-' . md5($tag); @endphp
                                            <div>
                                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                                <label for="{{ $id }}" class="cursor-pointer rounded-full border border-line bg-shell px-3 py-1.5 text-xs transition hover:border-ocean peer-checked:border-ocean peer-checked:bg-ocean peer-checked:text-white">{{ $tag }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-ocean">{{ $category }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tagNames as $tag)
                                        @php $id = 'nd-tag-' . md5($tag); @endphp
                                        <div>
                                            <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                            <label for="{{ $id }}" class="cursor-pointer rounded-full border border-line bg-shell px-3 py-1.5 text-xs transition hover:border-ocean peer-checked:border-ocean peer-checked:bg-ocean peer-checked:text-white">{{ $tag }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </form>

                {{-- Reset filters --}}
                @if(!empty($selectedTags) || !empty($selectedLevels))
                    <div class="text-center">
                        <a
                            href="{{ localized_route('new-design.catalog.tests-cards') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-shell px-4 py-2 text-sm font-semibold text-steel shadow-sm transition hover:-translate-y-0.5 hover:border-ocean hover:text-ocean"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Скинути фільтр
                        </a>
                    </div>
                @endif
            </div>
        </aside>

        {{-- ── Tests grid ── --}}
        <div class="flex-1">
            @if($tests->count())
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($tests as $index => $test)
                        @php
                            $gradients = [
                                ['from' => '#1a3d6e', 'to' => '#2f67b1'],
                                ['from' => '#2f67b1', 'to' => '#1a3d6e'],
                                ['from' => '#14233b', 'to' => '#2f67b1'],
                                ['from' => '#2f67b1', 'to' => '#14233b'],
                                ['from' => '#1e4d8c', 'to' => '#245592'],
                                ['from' => '#245592', 'to' => '#1a3d6e'],
                            ];
                            $g = $gradients[$index % count($gradients)];

                            $preferredView = data_get($test->filters, 'preferred_view');
                            if ($preferredView === 'drag-drop') {
                                $testRoute = localized_route('saved-test.js.drag-drop', $test->slug);
                            } elseif ($preferredView === 'match') {
                                $testRoute = localized_route('saved-test.js.match', $test->slug);
                            } else {
                                $testRoute = localized_route('new-design.test.show', $test->slug);
                            }

                            $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                            $levels = $test->levels
                                ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                ->map(fn($lvl) => $lvl ?? 'N/A');
                        @endphp
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-line bg-shell shadow-sm transition-all hover:border-ocean hover:shadow-card">

                            {{-- Card gradient header --}}
                            <a
                                href="{{ $testRoute }}"
                                class="relative block p-5 text-white transition-opacity hover:opacity-90"
                                style="background: linear-gradient(135deg, {{ $g['from'] }} 0%, {{ $g['to'] }} 100%)"
                            >
                                <div class="pointer-events-none absolute right-0 top-0 h-20 w-20 translate-x-1/2 -translate-y-1/2 rounded-full bg-white/10"></div>
                                <div class="pointer-events-none absolute bottom-0 left-0 h-16 w-16 -translate-x-1/2 translate-y-1/2 rounded-full bg-white/10"></div>

                                {{-- Number badge --}}
                                <div class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-xs font-bold">
                                    {{ $index + 1 }}
                                </div>

                                <h3 class="relative pr-8 text-lg font-bold leading-tight">{{ $test->name }}</h3>

                                {{-- Stats --}}
                                <div class="relative mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-1">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ count($test->questions) }}
                                    </span>
                                    @if($levels->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-1">
                                            {{ $levels->join(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </a>

                            {{-- Card body --}}
                            <div class="flex flex-1 flex-col p-5">
                                @if($test->description)
                                    <p class="mb-3 line-clamp-3 text-sm text-steel">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 120) }}</p>
                                @endif

                                {{-- Tags --}}
                                @if($test->tag_names->isNotEmpty())
                                    <div class="mb-4 flex flex-wrap gap-1.5">
                                        @foreach($test->tag_names->take(3) as $t)
                                            <span class="inline-flex items-center rounded-md bg-ocean/10 px-2 py-1 text-xs font-medium text-ocean">{{ $t }}</span>
                                        @endforeach
                                        @if($test->tag_names->count() > 3)
                                            <span class="inline-flex items-center rounded-md bg-steel/10 px-2 py-1 text-xs font-medium text-steel">+{{ $test->tag_names->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Date --}}
                                <div class="mb-4 text-xs text-steel">
                                    {{ $test->created_at->format('d.m.Y') }}
                                </div>

                                {{-- Action button --}}
                                <a
                                    href="{{ $testRoute }}"
                                    class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-ocean px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-card"
                                >
                                    Пройти тест
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-line bg-shell p-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-ocean/10 text-ocean">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 font-display text-lg font-bold text-night">Тестів не знайдено</h3>
                    <p class="text-steel">Спробуйте змінити фільтри або скинути їх</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Auto-submit on filter checkbox change
    document.querySelectorAll('#nd-tag-filter input[type=checkbox]').forEach(el => {
        el.addEventListener('change', () => document.getElementById('nd-tag-filter').submit());
    });

    // "Other" tag toggle
    const ndToggleBtn = document.getElementById('nd-toggle-others-btn');
    if (ndToggleBtn) {
        ndToggleBtn.addEventListener('click', () => {
            const tags = document.getElementById('nd-others-tags');
            const hidden = tags.style.display === 'none';
            tags.style.display = hidden ? '' : 'none';
            ndToggleBtn.textContent = hidden ? 'Сховати' : 'Показати';
        });
    }

    // Mobile sidebar toggle
    const ndFilterToggle = document.getElementById('nd-filter-toggle');
    const ndFilterClose  = document.getElementById('nd-filter-close');
    const ndFilters      = document.getElementById('nd-filters');
    if (ndFilterToggle && ndFilters) {
        ndFilterToggle.addEventListener('click', () => ndFilters.classList.toggle('hidden'));
    }
    if (ndFilterClose && ndFilters) {
        ndFilterClose.addEventListener('click', () => ndFilters.classList.add('hidden'));
    }
</script>
@endsection
