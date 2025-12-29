@extends('layouts.engram')

@section('title', 'Збережені тести')

@section('content')
<div class="space-y-8">
    <div class="rounded-3xl border border-[var(--border)] bg-gradient-to-br from-brand-50/70 via-white to-white p-6 shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-700">Каталог</span>
                <h1 class="text-3xl font-bold text-[var(--fg)]">Збережені тести</h1>
                <p class="text-sm text-[var(--muted)]">Оберіть рівень, теги та запускайте практику в один клік.</p>
            </div>
            @if(!empty($selectedTags) || !empty($selectedLevels))
                <a href="{{ localized_route('catalog.tests-cards') }}" class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm hover:-translate-y-0.5 hover:shadow">
                    <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                    Скинути фільтр
                </a>
            @endif
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="md:hidden">
            <button type="button" id="filter-toggle" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand-200 bg-white text-sm font-medium shadow-sm">
                <span>Фільтр</span>
                <svg class="w-4 h-4 text-brand-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <aside id="filters" class="md:w-64 w-full md:shrink-0 hidden md:block md:sticky md:top-20 md:self-start bg-[var(--card)] md:bg-transparent md:p-0 p-4 rounded-2xl shadow-card md:shadow-none border border-[var(--border)]">
            <div class="flex justify-between items-center md:hidden mb-4">
                <h2 class="text-base font-semibold">Фільтр</h2>
                <button type="button" id="filter-close" class="text-sm text-primary underline">
                    Закрити
                </button>
            </div>
            <form id="tag-filter" action="{{ localized_route('catalog.tests-cards') }}" method="GET" class="space-y-5">
            @if(isset($availableLevels) && $availableLevels->count())
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[var(--muted)]">Рівень:</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($availableLevels as $lvl)
                                @php $id = 'level-' . md5($lvl); @endphp
                                <div>
                                    <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="px-3 py-1 rounded-full border border-[var(--border)] cursor-pointer text-sm bg-[var(--card)] shadow-sm peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500">{{ $lvl }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
            @endif
            @foreach($tags as $category => $tagNames)
                @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                @if($isOther)
                    <div class="space-y-2" id="others-filter" data-open="false">
                        <h3 class="text-base font-semibold flex justify-between items-center text-[var(--fg)]">
                            <span>{{ $category }}</span>
                            <button type="button" id="toggle-others-btn" class="text-xs text-brand-600 underline">Show</button>
                        </h3>
                        <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                            @foreach($tagNames as $tag)
                                @php $id = 'tag-' . md5($tag); @endphp
                                <div>
                                    <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="px-3 py-1 rounded-full border border-[var(--border)] cursor-pointer text-sm bg-[var(--card)] shadow-sm peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500">{{ $tag }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        <h3 class="text-base font-semibold text-[var(--fg)]">{{ $category }}</h3>
                        <div class="flex flex-wrap gap-2">
                        @foreach($tagNames as $tag)
                            @php $id = 'tag-' . md5($tag); @endphp
                            <div>
                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="px-3 py-1 rounded-full border border-[var(--border)] cursor-pointer text-sm bg-[var(--card)] shadow-sm peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-500">{{ $tag }}</label>
                            </div>
                        @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            </form>
        </aside>
        <div class="flex-1">
            @if($tests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($tests as $test)
                        <div class="bg-[var(--card)] text-[var(--fg)] p-5 rounded-2xl border border-[var(--border)] shadow-card flex flex-col gap-3 hover:-translate-y-0.5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="font-bold text-lg">{{ $test->name }}</div>
                                    <div class="text-xs text-[var(--muted)]">
                                        Створено: {{ $test->created_at->format('d.m.Y') }} · Питань: {{ count($test->questions) }}
                                    </div>
                                </div>
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 shadow-sm">{{ $test->levels->first() ?? 'A1' }}</span>
                            </div>
                            <div class="text-xs text-[var(--muted)]">
                                @php
                                    $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                    $levels = $test->levels
                                        ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                        ->map(fn($lvl) => $lvl ?? 'N/A');
                                @endphp
                                Рівні: {{ $levels->join(', ') }}
                            </div>
                            <div class="mb-1 text-xs flex flex-wrap gap-2">
                                @foreach($test->tag_names as $t)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-3 py-1 text-brand-700 shadow-sm">{{ $t }}</span>
                                @endforeach
                            </div>
                            @if($test->description)
                                <div class="test-description text-sm text-[var(--muted)] line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($test->description, 120)) }}</div>
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
                            <a href="{{ $testRoute }}" class="mt-auto inline-flex items-center justify-center gap-2 text-center bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-card transition hover:-translate-y-0.5">
                                Пройти тест
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-[var(--border)] bg-[var(--card)]/70 p-6 text-[var(--muted)] shadow-inner">Ще немає збережених тестів.</div>
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
