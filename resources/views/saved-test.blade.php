@extends('layouts.app')

@section('title', $test->name)

@section('content')
<style>
    .text-base {

    line-height: 2.5rem;
}
 </style>   
<div class="max-w-3xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold mb-2">{{ $test->name }}</h1>
        <a href="/tests/cards"
           class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100">
            ← До списку тестів
        </a>
    </div>
    <div class="text-sm text-gray-500 mb-6">
        Кількість питань: {{ count($questions) }}
        <div class="mt-2 space-x-4">
            <a href="{{ route('saved-test.random', $test->slug) }}" class="text-blue-600 underline">Random Inputs</a>
            <a href="{{ route('saved-test.step', $test->slug) }}" class="text-blue-600 underline">Step Mode</a>
        </div>
    </div>
    @if($test->description)
        <div x-data="{ open: false }" class="test-description text-gray-800 flex justify-between">
            <div>
                <button type="button" @click="open = !open" class="text-xs text-blue-600 underline mb-2">
                    <span x-show="!open">Показати опис</span>
                    <span x-show="open" style="display: none;">Сховати опис</span>
                </button>
                <div x-show="open" style="display: none;">
                    <span>{!! $test->description !!}</span>
                </div>
            </div>
            <div class="ml-2 space-x-2">
                <form method="POST" action="{{ route('saved-test.refresh', $test->slug) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 underline">Оновити опис</button>
                </form>
                <form method="POST" action="{{ route('saved-test.refresh-gemini', $test->slug) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 underline">Оновити опис Gemini</button>
                </form>
            </div>
        </div>
    @else
        <div class="mb-4 space-x-2">
            <form method="POST" action="{{ route('saved-test.refresh', $test->slug) }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-blue-600 underline">Оновити опис</button>
            </form>
            <form method="POST" action="{{ route('saved-test.refresh-gemini', $test->slug) }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-blue-600 underline">Оновити опис Gemini</button>
            </form>
        </div>
    @endif
    @include('components.word-search')
    <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="test_slug" value="{{ $test->slug }}">
        @foreach($questions as $q)
            <input type="hidden" name="questions[{{ $q->id }}]" value="1">
            <div class="bg-white shadow rounded-2xl p-4 mb-4">
                <div class="text-xs text-gray-500 mb-1">Level: {{ $q->level ?? 'N/A' }}</div>
                <div class="flex gap-2 items-baseline">
                    <span class="font-bold text-base">{{ $loop->iteration }}.</span>

@php preg_match_all('/\{a(\d+)\}/', $q->question, $matches); @endphp
@include('components.question-input', [
    'question' => $q,
    'inputNamePrefix' => "question_{$q->id}_",
    'manualInput' => $manualInput,
    'autocompleteInput' => $autocompleteInput,
    'builderInput' => $builderInput,
    'showVerbHintEdit' => true,
    'showQuestionEdit' => true,
    'testSlug' => $test->slug,
])
<a href="{{ route('question-review.edit', $q->id) }}" class="ml-2 text-sm text-blue-600 underline">Review</a>
<button type="submit" form="delete-question-{{ $q->id }}" class="text-sm text-red-600 underline" onclick="return confirm('Delete this question?')">Delete</button>
                </div>
                @if($q->tags->count())
                    <div class="mt-1 space-x-1">
                        @php
                            $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                        @endphp
                        @foreach($q->tags as $tag)
                            <a href="{{ route('saved-tests.cards', ['tag' => $tag->name]) }}" class="inline-block px-2 py-0.5 rounded text-xs font-semibold hover:underline {{ $colors[$loop->index % count($colors)] }}">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg">
            Перевірити
        </button>
    </form>
@foreach($questions as $q)
    <form id="delete-question-{{ $q->id }}" action="{{ route('saved-test.question.destroy', [$test->slug, $q->id]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach
</div>
<script>
function builder(route, prefix) {
    const stored = [];
    for (let i = 0; ; i++) {
        const key = storageKey(`${prefix}${i}]`);
        const val = localStorage.getItem(key);
        if (val === null) break;
        stored.push(val);
    }
    if (stored.length === 0) stored.push('');
    return {
        words: stored,
        suggestions: stored.map(() => []),
        valid: stored.map(() => false),
        addWord() {
            this.words.push('');
            this.suggestions.push([]);
            this.valid.push(false);
        },
        removeWord() {
            if (this.words.length > 1) {
                this.words.pop();
                this.suggestions.pop();
                this.valid.pop();
            }
        },
        completeWord(index) {
            if (this.words[index].trim() !== '' && this.valid[index]) {
                if (index === this.words.length - 1) {
                    this.addWord();
                }
                this.$nextTick(() => {
                    const fields = this.$el.querySelectorAll(`input[name^="${prefix}"]`);
                    if (fields[index + 1]) {
                        fields[index + 1].focus();
                    }
                });
            }
        },
        fetchSuggestions(index) {
            const query = this.words[index];
            this.valid[index] = false;
            if (query.length === 0) {
                this.suggestions[index] = [];
                return;
            }
            fetch(route + '&q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    this.suggestions[index] = data.map(i => i.en);
                });
        },
        selectSuggestion(index, val) {
            this.words[index] = val;
            this.valid[index] = true;
            this.suggestions[index] = [];
        }
    }
}
</script>
@endsection

