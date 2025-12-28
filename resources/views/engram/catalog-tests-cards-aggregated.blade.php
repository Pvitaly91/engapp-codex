@extends('layouts.public-v2')

@section('title', 'Збережені тести')

@section('content')
<div class="grid gap-6 md:grid-cols-[280px_1fr]">
    <div class="md:hidden">
        <button type="button" id="filter-toggle" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white">
            <span>Фільтр</span>
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    <aside id="filters" class="hidden rounded-2xl border border-white/5 bg-white/5 p-4 shadow-soft md:block">
        <div class="flex justify-between items-center md:hidden mb-4">
            <h2 class="text-base font-semibold text-white">Фільтр</h2>
            <button type="button" id="filter-close" class="text-sm text-lilac underline">Закрити</button>
        </div>
        <form id="tag-filter" action="{{ route('catalog.tests-cards') }}" method="GET" class="space-y-4">
            @if(isset($availableLevels) && $availableLevels->count())
                <div class="space-y-2">
                    <label class="block text-sm text-slate-300">Level:</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableLevels as $lvl)
                            @php $id = 'level-' . md5($lvl); @endphp
                            <div>
                                <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                <label for="{{ $id }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-100 transition peer-checked:border-lilac peer-checked:bg-lilac/10 peer-checked:text-white">{{ $lvl }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @foreach($tags as $category => $tagNames)
                @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                @if($isOther)
                    <div class="space-y-2" id="others-filter" data-open="false">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-white">{{ $category }}</h3>
                            <button type="button" id="toggle-others-btn" class="text-xs text-lilac underline">Show</button>
                        </div>
                        <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                            @foreach($tagNames as $tag)
                                @php $id = 'tag-' . md5($tag); @endphp
                                <div>
                                    <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-100 transition peer-checked:border-lilac peer-checked:bg-lilac/10 peer-checked:text-white">{{ $tag }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <h3 class="text-sm font-semibold text-white">{{ $category }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tagNames as $tag)
                            @php $id = 'tag-' . md5($tag); @endphp
                            <div>
                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                <label for="{{ $id }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-100 transition peer-checked:border-lilac peer-checked:bg-lilac/10 peer-checked:text-white">{{ $tag }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </form>
        @if(!empty($selectedTags) || !empty($selectedLevels))
            <div class="pt-2">
                <a href="{{ route('catalog.tests-cards') }}" class="text-xs text-slate-300 underline">Скинути фільтр</a>
            </div>
        @endif
    </aside>
    <div class="flex-1 space-y-4">
        @if($tests->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tests as $test)
                    <div class="glass-card text-white p-5 rounded-2xl shadow-soft flex flex-col border border-white/5">
                        <div class="font-bold text-lg mb-1">{{ $test->name }}</div>
                        <div class="text-xs text-slate-300 mb-2 leading-relaxed">
                            Створено: {{ $test->created_at->format('d.m.Y') }}<br>
                            Питань: {{ count($test->questions) }}<br>
                            @php
                                $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                $levels = $test->levels
                                    ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                    ->map(fn($lvl) => $lvl ?? 'N/A');
                            @endphp
                            Рівні: {{ $levels->join(', ') }}
                        </div>
                        <div class="mb-3 text-[11px] text-slate-200">
                            @foreach($test->tag_names as $t)
                                <span class="inline-block bg-white/10 px-2 py-1 mr-1 mb-1 rounded-full">{{ $t }}</span>
                            @endforeach
                        </div>
                        @if($test->description)
                            <div class="test-description text-sm mb-3 text-slate-200">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 120) }}</div>
                        @endif
                        @php
                            $preferredView = data_get($test->filters, 'preferred_view');
                            if ($preferredView === 'drag-drop') {
                                $testRoute = route('saved-test.js.drag-drop', $test->slug);
                            } elseif ($preferredView === 'match') {
                                $testRoute = route('saved-test.js.match', $test->slug);
                            } else {
                                $testRoute = route('test.show', $test->slug);
                            }
                        @endphp
                        <a href="{{ $testRoute }}" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-lilac to-mint px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lifted">Пройти тест</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-white/10 bg-white/5 p-10 text-center text-slate-300">Ще немає збережених тестів.</div>
        @endif
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
