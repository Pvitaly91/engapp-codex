@extends('layouts.engram')

@section('title', __('frontend.catalog.title'))

@section('content')
<div class="flex flex-col md:flex-row gap-6">
    <div class="md:hidden">
        <button type="button" id="filter-toggle" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-border text-sm font-medium">
            <span>{{ __('frontend.catalog.filter') }}</span>
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3.5 5a.75.75 0 01.75-.75h11.5a.75.75 0 01.53 1.28L12 10.06v4.19a.75.75 0 01-1.13.65l-2.5-1.5a.75.75 0 01-.37-.65v-2.69L3.97 5.53A.75.75 0 013.5 5z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    <aside id="filters" class="md:w-48 w-full md:shrink-0 hidden md:block md:sticky md:top-20 md:self-start bg-card md:bg-transparent md:p-0 p-4 rounded-2xl shadow-soft md:shadow-none">
        <div class="flex justify-between items-center md:hidden mb-4">
            <h2 class="text-base font-semibold">{{ __('frontend.catalog.filter') }}</h2>
            <button type="button" id="filter-close" class="text-sm text-primary underline">
                {{ __('frontend.catalog.close') }}
            </button>
        </div>
        <form id="tag-filter" action="{{ localized_route('catalog.tests-cards') }}" method="GET">
            @if(isset($availableLevels) && $availableLevels->count())
                <div class="mb-4">
                    <label class="block text-sm mb-1">{{ __('frontend.catalog.level') }}:</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableLevels as $lvl)
                            @php $id = 'level-' . md5($lvl); @endphp
                            <div>
                                <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                <label for="{{ $id }}" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground">{{ $lvl }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @foreach($tags as $category => $tagNames)
                @php $isOther = in_array(strtolower($category), ['other', 'others']); @endphp
                @if($isOther)
                    <div class="mb-4" id="others-filter" data-open="false">
                        <h3 class="text-lg font-bold mb-2 flex justify-between items-center">
                            <span>{{ $category }}</span>
                            <button type="button" id="toggle-others-btn" class="text-xs text-primary underline">{{ __('frontend.catalog.show') }}</button>
                        </h3>
                        <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                            @foreach($tagNames as $tag)
                                @php $id = 'tag-' . md5($tag); @endphp
                                <div>
                                    <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground">{{ $tag }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <h3 class="text-lg font-bold mb-2">{{ $category }}</h3>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($tagNames as $tag)
                            @php $id = 'tag-' . md5($tag); @endphp
                            <div>
                                <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                <label for="{{ $id }}" class="px-3 py-1 rounded border border-border cursor-pointer text-sm bg-muted peer-checked:bg-primary peer-checked:text-primary-foreground">{{ $tag }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </form>
        @if(!empty($selectedTags) || !empty($selectedLevels))
            <div class="mt-2">
                <a href="{{ localized_route('catalog.tests-cards') }}" class="text-xs text-muted-foreground hover:underline">{{ __('frontend.catalog.reset') }}</a>
            </div>
        @endif
    </aside>
    <div class="flex-1">
        @if($tests->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tests as $test)
                    <div class="bg-card text-card-foreground p-4 rounded-2xl shadow-soft flex flex-col">
                        <div class="font-bold text-lg mb-1">{{ $test->name }}</div>
                        <div class="text-xs text-muted-foreground mb-2">
                            {{ __('frontend.catalog.created') }}: {{ $test->created_at->format('d.m.Y') }}<br>
                            {{ __('frontend.catalog.questions') }}: {{ count($test->questions) }}<br>
                            @php
                                $order = array_flip(['A1','A2','B1','B2','C1','C2']);
                                $levels = $test->levels
                                    ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                                    ->map(fn($lvl) => $lvl ?? __('frontend.tests.hero.na'));
                            @endphp
                            {{ __('frontend.catalog.levels') }}: {{ $levels->join(', ') }}
                        </div>
                        <div class="mb-3 text-xs">
                            @foreach($test->tag_names as $t)
                                <span class="inline-block bg-muted px-2 py-0.5 mr-1 mb-1 rounded">{{ $t }}</span>
                            @endforeach
                        </div>
                        @if($test->description)
                            <div class="test-description text-sm mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($test->description, 120)) }}</div>
                        @endif
                        @php
                            $preferredView = data_get($test->filters, 'preferred_view');
                            if ($preferredView === 'drag-drop') {
                                $testRoute = localized_route('test.drag-drop', $test->slug);
                            } elseif ($preferredView === 'match') {
                                $testRoute = localized_route('test.match', $test->slug);
                            } elseif ($preferredView === 'dialogue') {
                                $testRoute = localized_route('test.dialogue', $test->slug);
                            } else {
                                $testRoute = localized_route('test.show', $test->slug);
                            }
                        @endphp
                        <a href="{{ $testRoute }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-2xl text-sm font-semibold">{{ __('frontend.catalog.take_test') }}</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted-foreground">{{ __('frontend.catalog.empty') }}</div>
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
            toggleBtn.textContent = hidden ? @json(__('frontend.catalog.hide')) : @json(__('frontend.catalog.show'));
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
