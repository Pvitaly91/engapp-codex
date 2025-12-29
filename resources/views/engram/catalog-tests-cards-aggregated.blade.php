@extends('layouts.engram')

@section('title', 'Збережені тести')

@section('content')
<div class="space-y-8">
    {{-- Hero Section --}}
    <section class="rounded-3xl border border-[var(--border)] bg-[var(--card)] p-8 shadow-card">
        <div class="space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700">{{ __('public.nav.catalog') }}</span>
            <h1 class="text-3xl font-bold md:text-4xl">Каталог тестів</h1>
            <p class="max-w-2xl text-lg text-[var(--muted)]">Обирайте тести з різних граматичних тем і рівнів складності</p>
        </div>
    </section>

    <div class="flex flex-col md:flex-row gap-6">
        {{-- Mobile Filter Toggle --}}
        <div class="md:hidden">
            <button type="button" id="filter-toggle" class="inline-flex items-center gap-2 rounded-xl border border-[var(--border)] bg-[var(--card)] px-4 py-2 text-sm font-semibold shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                <span>Фільтр</span>
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        {{-- Sidebar Filters --}}
        <aside id="filters" class="md:w-64 w-full md:shrink-0 hidden md:block bg-[var(--card)] md:bg-transparent md:p-0 p-5 rounded-2xl shadow-card md:shadow-none border border-[var(--border)] md:border-0">
            <div class="flex justify-between items-center md:hidden mb-4">
                <h2 class="text-base font-semibold">Фільтр</h2>
                <button type="button" id="filter-close" class="text-sm text-brand-600 underline font-semibold">
                    Закрити
                </button>
            </div>

            <div class="space-y-5">
                <form id="tag-filter" action="{{ localized_route('catalog.tests-cards') }}" method="GET" class="space-y-5">
                    @if(isset($availableLevels) && $availableLevels->count())
                        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm">
                            <label class="block text-sm font-semibold mb-3 text-brand-600">Рівень:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableLevels as $lvl)
                                    @php $id = 'level-' . md5($lvl); @endphp
                                    <div>
                                        <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                        <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-sm bg-[var(--card)] peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500 transition hover:border-brand-500">{{ $lvl }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @foreach($tags as $category => $tagNames)
                        @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-4 shadow-sm">
                            @if($isOther)
                                <div id="others-filter" data-open="false">
                                    <h3 class="text-sm font-semibold mb-3 flex justify-between items-center text-brand-600">
                                        <span>{{ $category }}</span>
                                        <button type="button" id="toggle-others-btn" class="text-xs text-brand-600 underline">Show</button>
                                    </h3>
                                    <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                                        @foreach($tagNames as $tag)
                                            @php $id = 'tag-' . md5($tag); @endphp
                                            <div>
                                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                                <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-xs bg-[var(--card)] peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500 transition hover:border-brand-500">{{ $tag }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <h3 class="text-sm font-semibold mb-3 text-brand-600">{{ $category }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tagNames as $tag)
                                        @php $id = 'tag-' . md5($tag); @endphp
                                        <div>
                                            <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                            <label for="{{ $id }}" class="px-3 py-1.5 rounded-full border border-[var(--border)] cursor-pointer text-xs bg-[var(--card)] peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500 transition hover:border-brand-500">{{ $tag }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </form>
                
                @if(!empty($selectedTags) || !empty($selectedLevels))
                    <div class="text-center">
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 rounded-xl border border-[var(--border)] bg-[var(--card)] px-4 py-2 text-sm font-semibold text-[var(--muted)] shadow-sm transition hover:-translate-y-0.5 hover:shadow hover:text-brand-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Скинути фільтр
                        </a>
                    </div>
                @endif
            </div>
        </aside>
        {{-- Tests Grid --}}
        <div class="flex-1">
            @if($tests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($tests as $index => $test)
                        @php
                            $gradients = [
                                'from-indigo-500 to-purple-600',
                                'from-emerald-500 to-teal-600',
                                'from-blue-500 to-cyan-600',
                                'from-amber-500 to-orange-600',
                                'from-rose-500 to-pink-600',
                                'from-violet-500 to-purple-600',
                            ];
                            $gradient = $gradients[$index % count($gradients)];
                            
                            $preferredView = data_get($test->filters, 'preferred_view');
                            if ($preferredView === 'drag-drop') {
                                $testRoute = localized_route('saved-test.js.drag-drop', $test->slug);
                            } elseif ($preferredView === 'match') {
                                $testRoute = localized_route('saved-test.js.match', $test->slug);
                            } else {
                                $testRoute = localized_route('test.show', $test->slug);
                            }
                            
                            $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                            $levels = $test->levels
                                ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                ->map(fn($lvl) => $lvl ?? 'N/A');
                        @endphp
                        <div class="group relative overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--card)] transition-all hover:border-brand-500 hover:shadow-xl flex flex-col">
                            {{-- Card Header with Gradient --}}
                            <div class="relative bg-gradient-to-br {{ $gradient }} p-5 text-white">
                                {{-- Decorative elements --}}
                                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                                
                                {{-- Test number badge --}}
                                <div class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm text-xs font-bold">
                                    {{ $index + 1 }}
                                </div>
                                
                                <h3 class="relative text-lg font-bold leading-tight mb-2">{{ $test->name }}</h3>
                                
                                {{-- Stats badges --}}
                                <div class="relative flex flex-wrap gap-2 text-xs">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/20 backdrop-blur-sm px-2 py-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ count($test->questions) }}
                                    </span>
                                    @if($levels->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-white/20 backdrop-blur-sm px-2 py-1">
                                            {{ $levels->join(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Card Body --}}
                            <div class="flex flex-col flex-1 p-5">
                                @if($test->description)
                                    <p class="text-sm text-[var(--muted)] mb-3 line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 120) }}</p>
                                @endif
                                
                                {{-- Tags --}}
                                @if($test->tag_names->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        @foreach($test->tag_names->take(3) as $t)
                                            <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700">{{ $t }}</span>
                                        @endforeach
                                        @if($test->tag_names->count() > 3)
                                            <span class="inline-flex items-center rounded-md bg-[var(--muted)]/10 px-2 py-1 text-xs font-medium text-[var(--muted)]">+{{ $test->tag_names->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Date --}}
                                <div class="text-xs text-[var(--muted)] mb-4">
                                    {{ $test->created_at->format('d.m.Y') }}
                                </div>
                                
                                {{-- Action Button --}}
                                <a href="{{ $testRoute }}" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                    Пройти тест
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)] p-12 text-center shadow-sm">
                    <div class="flex justify-center mb-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Тестів не знайдено</h3>
                    <p class="text-[var(--muted)]">Спробуйте змінити фільтри або скинути їх</p>
                </div>
            @endif
        </div>
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
            toggleBtn.textContent = hidden ? 'Hide' : 'Show';
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
