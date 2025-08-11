@extends('layouts.app')

@section('title', 'AI Grammar Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $topic }}</h1>
        <a href="{{ route('ai-test.form') }}" class="text-sm text-blue-600 underline">Start over</a>
    </div>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <form method="POST" action="{{ route('ai-test.reset') }}" class="mb-4">
        @csrf
        <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
    </form>
    <form method="POST" action="{{ route('ai-test.provider') }}" class="mb-4" x-data="{ provider: '{{ $provider }}' }">
        @csrf
        <label class="mr-4">
            <input type="radio" name="provider" value="chatgpt" x-model="provider" {{ $provider === 'chatgpt' ? 'checked' : '' }}>
            ChatGPT
        </label>
        <label class="mr-4">
            <input type="radio" name="provider" value="gemini" x-model="provider" {{ $provider === 'gemini' ? 'checked' : '' }}>
            Gemini
        </label>
        <label class="mr-4">
            <input type="radio" name="provider" value="mixed" x-model="provider" {{ $provider === 'mixed' ? 'checked' : '' }}>
            Mixed
        </label>
        <div class="mt-2" x-show="provider === 'chatgpt'">
            <label class="block font-bold mb-1">Model:</label>
            <select name="model" class="border rounded p-1">
                <option value="random" {{ $model === 'random' ? 'selected' : '' }}>Random</option>
                @foreach($models as $m)
                    <option value="{{ $m }}" {{ $model === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Switch</button>
    </form>

    @if(isset($feedback))
        <div class="mb-4">
            @if($feedback['isCorrect'])
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">Correct!</div>
            @else
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">Wrong</div>
                @if(!empty($feedback['answer_sentence']))
                    <div class="text-sm text-gray-800 mb-2">Your answer: {!! $feedback['answer_sentence'] !!}</div>
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

    @if($question)
        <form method="POST" action="{{ route('ai-test.check') }}" class="space-y-4">
            @csrf
            @php
                $answersCol = collect();
                $hintsCol = collect();
                foreach($question['answers'] as $m => $val){
                    $answersCol->push((object)['marker'=>$m, 'option'=>(object)['option'=>$val]]);
                    if(isset($question['verb_hints'][$m])){
                        $hintsCol->push((object)['marker'=>$m, 'option'=>(object)['option'=>$question['verb_hints'][$m]]]);
                    }
                }
                $obj = (object)['question'=>$question['question'], 'verbHints'=>$hintsCol, 'options'=>collect(), 'answers'=>$answersCol];
            @endphp
            <div class="mb-2">
                @if($provider === 'gemini' || ($provider === 'mixed' && $currentProvider === 'gemini'))
                    <span class="text-xs font-semibold px-2 py-1 rounded bg-purple-200 text-purple-800">Gemini</span>
                @else
                    <span class="text-xs font-semibold px-2 py-1 rounded bg-green-200 text-green-800">
                        ChatGPT{{ isset($question['model']) ? ' (' . $question['model'] . ')' : '' }}
                    </span>
                @endif
            </div>
        @include('components.question-input', [
                'question' => $obj,
                'inputNamePrefix' => 'answers',
                'arrayInput' => true,
                'manualInput' => false,
                'autocompleteInput' => false,
                'builderInput' => true,
            ])
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
                    {{ isset($feedback) ? 'Next' : 'Check' }}
                </button>
                @if(!isset($feedback))
                    <button type="submit" formaction="{{ route('ai-test.skip') }}" class="bg-gray-300 px-6 py-2 rounded-xl">Skip</button>
                @endif
            </div>
        </form>
    @else
        <div class="text-center text-gray-700">No question generated. <a href="{{ route('ai-test.step') }}" class="underline">Try again</a>.</div>
    @endif
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
                .then(data => { this.suggestions[index] = data.map(i => i.en); });
        },
        selectSuggestion(index, val) {
            this.words[index] = val;
            this.valid[index] = true;
            this.suggestions[index] = [];
        }
    }
}
@if($question)
fetch('{{ route('ai-test.next') }}');
@endif
</script>
@endsection
