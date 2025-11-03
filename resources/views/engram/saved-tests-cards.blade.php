@extends('layouts.engram')

@section('title', 'Збережені тести')

@section('content')
<div class="flex flex-col md:flex-row gap-6">
    <aside class="md:w-48 w-full md:shrink-0">
        <form id="tag-filter" action="{{ route('saved-tests.cards') }}" method="GET">
            @if(isset($availableLevels) && $availableLevels->count())
                <div class="mb-4">
                    <label class="block text-sm mb-1">Level:</label>
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
                            <button type="button" id="toggle-others-btn" class="text-xs text-primary underline">Show</button>
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
                <a href="{{ route('saved-tests.cards') }}" class="text-xs text-muted-foreground hover:underline">Скинути фільтр</a>
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
                                $testRoute = route('saved-test.js.drag-drop', $test->slug);
                            } elseif ($preferredView === 'match') {
                                $testRoute = route('saved-test.js.match', $test->slug);
                            } else {
                                $testRoute = route('saved-test.js', $test->slug);
                            }
                        @endphp
                        <a href="{{ $testRoute }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-2xl text-sm font-semibold">Пройти тест</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted-foreground">Ще немає збережених тестів.</div>
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
</script>
@endsection
