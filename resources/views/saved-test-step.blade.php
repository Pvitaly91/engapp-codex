@extends('layouts.app')

@section('title', $test->name . ' - Step Test')

@section('content')
<div class="max-w-xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $test->name }} - Step Mode</h1>
        <a href="{{ route('saved-test.show', $test->slug) }}" class="text-sm text-blue-600 underline">Back</a>
    </div>
    @if($test->description)
        <div class="test-description text-gray-800">{{ $test->description }}</div>
    @endif
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <form method="POST" action="{{ route('saved-test.step.reset', $test->slug) }}" class="mb-4">
        @csrf
        <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
    </form>

    @if(isset($feedback))
        <div class="mb-4">
            @if($feedback['isCorrect'])
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">Correct!</div>
            @else
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">Wrong</div>
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
            $methods = ['select', 'text', 'autocomplete', 'builder'];
            $method = $methods[array_rand($methods)];
            $autocompleteRoute = url('/api/search?lang=en');
        @endphp
        @include('components.question-input', [
            'question' => $question,
            'inputNamePrefix' => 'answers',
            'arrayInput' => true,
            'manualInput' => in_array($method, ['text','autocomplete','builder']),
            'autocompleteInput' => $method === 'autocomplete',
            'builderInput' => $method === 'builder',
            'autocompleteRoute' => $autocompleteRoute,
        ])
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
            {{ isset($feedback) ? 'Next' : 'Check' }}
        </button>
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
