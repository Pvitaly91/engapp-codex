@extends('layouts.app')

@section('title', 'Збережені тести')

@section('content')
<div class="flex gap-6">
    <aside class="w-48 shrink-0">
        <form id="tag-filter" action="{{ route('saved-tests.cards') }}" method="GET">
            @if(isset($availableLevels) && $availableLevels->count())
                <div class="mb-4">
                    <label class="block text-sm mb-1">Level:</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableLevels as $lvl)
                            @php $id = 'level-' . md5($lvl); @endphp
                            <div>
                                <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $id }}" class="hidden peer" {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                <label for="{{ $id }}" class="px-3 py-1 rounded border cursor-pointer text-sm bg-gray-200 peer-checked:bg-blue-600 peer-checked:text-white">{{ $lvl }}</label>
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
                            <button type="button" id="toggle-others-btn" class="text-xs text-blue-500 underline">Show</button>
                        </h3>
                        <div id="others-tags" class="flex flex-wrap gap-2" style="display:none;">
                            @foreach($tagNames as $tag)
                                @php $id = 'tag-' . md5($tag); @endphp
                                <div>
                                    <input type="checkbox" name="tags[]" value="{{ $tag }}" id="{{ $id }}" class="hidden peer" {{ in_array($tag, $selectedTags ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $id }}" class="px-3 py-1 rounded border cursor-pointer text-sm bg-gray-200 peer-checked:bg-blue-600 peer-checked:text-white">{{ $tag }}</label>
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
                                <label for="{{ $id }}" class="px-3 py-1 rounded border cursor-pointer text-sm bg-gray-200 peer-checked:bg-blue-600 peer-checked:text-white">{{ $tag }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </form>
        @if(!empty($selectedTags) || !empty($selectedLevels))
            <div class="mt-2">
                <a href="{{ route('saved-tests.cards') }}" class="text-xs text-gray-500 hover:underline">Скинути фільтр</a>
            </div>
        @endif
    </aside>
    <div class="flex-1">
        @if($tests->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tests as $test)
                    <div class="bg-white p-4 rounded-2xl shadow flex flex-col">
                        <div class="font-bold text-lg mb-1">{{ $test->name }}</div>
                        <div class="text-xs text-gray-500 mb-2">
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
                                <span class="inline-block bg-gray-200 px-2 py-0.5 mr-1 mb-1 rounded">{{ $t }}</span>
                            @endforeach
                        </div>
                        @if($test->description)
                            <div class="test-description text-sm text-gray-800 mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($test->description, 120)) }}</div>
                        @endif
                        <a href="{{ route('saved-test.show', $test->slug) }}" class="mt-auto inline-block text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-sm font-semibold">Пройти тест</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-gray-600">Ще немає збережених тестів.</div>
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
