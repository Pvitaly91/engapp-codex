@extends('layouts.app')

@section('title', 'Збережені тести')

@section('content')
<div class="flex gap-6">
    <aside class="w-48 shrink-0">
        <h2 class="text-lg font-bold mb-2">Теги</h2>
        <form method="GET" action="{{ route('saved-tests.cards') }}">
            @foreach($tags->groupBy(fn($t) => optional($t->category)->name ?? 'Other') as $catName => $group)
                <div class="mb-1 font-semibold">{{ $catName }}</div>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($group as $tag)
                        <div>
                            <input
                                type="checkbox"
                                name="tags[]"
                                value="{{ $tag->name }}"
                                id="tag-{{ $tag->id }}"
                                class="hidden peer"
                                {{ in_array($tag->name, $selectedTags) ? 'checked' : '' }}
                            >
                            <label for="tag-{{ $tag->id }}" class="px-3 py-1 rounded border cursor-pointer text-sm bg-gray-200 peer-checked:bg-blue-600 peer-checked:text-white">
                                {{ $tag->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="flex gap-2 mt-2">
                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Застосувати</button>
                @if(count($selectedTags))
                    <a href="{{ route('saved-tests.cards') }}" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Скинути</a>
                @endif
            </div>
        </form>
    </aside>
    <div class="flex-1">
        @if($tests->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tests as $test)
                    <div class="bg-white p-4 rounded-2xl shadow flex flex-col">
                        <div class="font-bold text-lg mb-1">{{ $test->name }}</div>
                        <div class="text-xs text-gray-500 mb-2">
                            Створено: {{ $test->created_at->format('d.m.Y') }}<br>
                            Питань: {{ count($test->questions) }}
                        </div>
                        <div class="mb-3 text-xs">
                            @foreach($test->tag_names as $t)
                                <span class="inline-block bg-gray-200 px-2 py-0.5 mr-1 mb-1 rounded">{{ $t }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('saved-test.show', $test->slug) }}" class="mt-auto inline-block text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-sm font-semibold">Пройти тест</a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-gray-600">Ще немає збережених тестів.</div>
        @endif
    </div>
</div>
@endsection
