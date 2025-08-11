@extends('layouts.app')

@section('title', $test->name . ' - Step Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $test->name }} - Step Mode</h1>
        <a href="{{ route('saved-test.show', $test->slug) }}" class="text-sm text-blue-600 underline">Back</a>
    </div>
    @if($test->description)
        <div class="test-description text-gray-800 flex justify-between">
            <span>{!! $test->description !!}</span>
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
                <button type="submit" class="text-xs text-blue-600 underline">Згенерувати опис</button>
            </form>
            <form method="POST" action="{{ route('saved-test.refresh-gemini', $test->slug) }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-blue-600 underline">Згенерувати опис Gemini</button>
            </form>
        </div>
    @endif
    <div class="mb-4 text-sm">
        <span class="mr-2">Порядок питань:</span>
        <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'order' => 'sequential']) }}" class="underline mr-2 {{ $order === 'sequential' ? 'font-bold' : '' }}">По порядку</a>
        <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'order' => 'random']) }}" class="underline {{ $order === 'random' ? 'font-bold' : '' }}">Випадково</a>
    </div>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <div class="mb-4 flex gap-2">
        <form method="POST" action="{{ route('saved-test.step.reset', $test->slug) }}">
            @csrf
            <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
        </form>
    </div>

    @if(isset($feedback))
        <div class="mb-4">
            @if($feedback['isCorrect'])
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">Correct!</div>
            @else
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">Wrong</div>
                @if(!empty($feedback['answers']))
                    <div class="text-sm text-gray-800 mb-2">Your answer: {{ implode(', ', $feedback['answers']) }}</div>
                @endif
                @if(!empty($feedback['explanations']))
                    <div class="bg-blue-50 text-gray-800 text-sm rounded px-3 py-2 space-y-1">
                        @foreach($feedback['explanations'] as $exp)
                            <p>{!! $exp !!}</p>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('saved-test.step.check', $test->slug) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">
        @php
            $autocompleteRoute = url('/api/search?lang=en');
        @endphp
        @include('components.question-input', [
            'question' => $question,
            'inputNamePrefix' => 'answers',
            'arrayInput' => true,
            'manualInput' => false,
            'autocompleteInput' => false,
            'builderInput' => true,
            'autocompleteRoute' => $autocompleteRoute,
            'showVerbHintEdit' => true,
        ])
        <div class="flex gap-2 mt-2">
            <a href="{{ route('question-review.edit', $question->id) }}" class="text-sm text-blue-600 underline">Edit</a>
            <button type="submit" form="delete-question-{{ $question->id }}" class="text-sm text-red-600 underline" onclick="return confirm('Delete this question?')">Delete</button>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
            {{ isset($feedback) ? 'Next' : 'Check' }}
        </button>
    </form>
    <form id="delete-question-{{ $question->id }}" action="{{ route('saved-test.question.destroy', [$test->slug, $question->id]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
<script>
function builder(route, prefix) {
    return {
        words: [''],
        suggestions: [[]],
        valid: [false],
        addWord() {
            this.words.push('');
            this.suggestions.push([]);
            this.valid.push(false);
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
                .then(data => { this.suggestions[index] = data.map(i => i.en); });
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
