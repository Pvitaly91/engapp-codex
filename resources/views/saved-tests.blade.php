@extends('layouts.app')

@section('title', 'Збережені тести')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Збережені тести</h1>
        <a href="{{ route('grammar-test') }}"
           class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100">
            + До фільтра / конструктора тестів
        </a>
    </div>
    @if($tests->count())
        <ul class="divide-y">
            @foreach($tests as $test)
            <li class="py-3 flex items-center justify-between">
                <div>
                    <a href="{{ route('saved-test.show', $test->slug) }}" class="text-lg text-blue-700 font-semibold hover:underline">
                        {{ $test->name }}
                    </a>
                    <div class="text-xs text-gray-500 flex gap-3">
                        <span>Створено: {{ $test->created_at->format('d.m.Y H:i') }}</span>
                        <span>Питань: {{ is_array($test->questions) ? count($test->questions) : (is_string($test->questions) ? count(json_decode($test->questions, true) ?? []) : 0) }}</span>
                    </div>
                    @if($test->description)
                        <div class="text-sm text-gray-800 mt-1">{{ \Illuminate\Support\Str::limit($test->description, 120) }}</div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('saved-test.show', $test->slug) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded-2xl text-sm font-semibold">Пройти тест</a>
                    <form action="{{ route('saved-tests.destroy', $test->id) }}" method="POST"
                          onsubmit="return confirm('Видалити цей тест?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-2xl text-sm font-semibold">
                            Видалити
                        </button>
                    </form>
                </div>
            </li>
            
            @endforeach
        </ul>
        <div class="mt-6">
            {{ $tests->links() }}
        </div>
    @else
        <div class="text-gray-600">Ще немає збережених тестів.</div>
    @endif
</div>
@endsection

