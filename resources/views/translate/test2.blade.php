@extends('layouts.app')

@section('title', 'Sentence Translation Test V2')

@section('content')
    <div class="mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-blue-700">Sentence Translation Test V2</h2>
            <a href="{{ route('translate.test') }}" class="text-sm text-blue-600 underline">Back to V1</a>
        </div>

        <div class="mb-4 flex gap-4 text-gray-600 text-base">
            <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
            <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
            <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
            <div>Percent: <b>{{ $percentage }}%</b></div>
        </div>
        <form method="POST" action="{{ route('translate.test2.reset') }}" class="mb-4">
            @csrf
            <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
        </form>

        @if(isset($feedback))
            <div class="mb-4">
                @if($feedback['isCorrect'])
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
                        Correct! <b>{{ $feedback['correct'] }}</b>
                    </div>
                @else
                    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
                        {{ $feedback['explanation'] }}<br>
                        Your answer: <b>{{ $feedback['userAnswer'] }}</b>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('translate.test2.check') }}" class="mt-4" x-data="builder()">
            @csrf
            <input type="hidden" name="sentence_id" value="{{ $sentence->id }}">
            <div class="mb-6 text-xl font-semibold">{{ $sentence->text_uk }}</div>
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <template x-for="(word, index) in words" :key="index">
                    <div class="relative">
                        <input type="text" :name="'words['+index+']'" class="border rounded px-2 py-1 w-[70%]" autocomplete="off"
                               x-model="words[index]"
                               pattern="^\S+$" title="One word only"
                               @keydown.space.prevent="completeWord(index)"
                               @focus="fetchSuggestions(index)" @input="fetchSuggestions(index)" required>
                        <template x-if="suggestions[index] && suggestions[index].length">
                            <ul class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full">
                                <template x-for="suggestion in suggestions[index]" :key="suggestion">
                                    <li class="cursor-pointer px-3 py-1 hover:bg-blue-100"
                                        @mousedown.prevent="selectSuggestion(index, suggestion)"
                                        x-text="suggestion"></li>
                                </template>
                            </ul>
                        </template>
                    </div>
                </template>
                <button type="button" @click="addWord" class="bg-gray-200 px-2 py-1 rounded order-last -ml-[30%]">+</button>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                {{ $attempts > 0 ? 'Submit' : 'Check' }}
            </button>
        </form>
        <script>
            function builder() {
                return {
                    words: [''],
                    suggestions: [[]],
                    valid: [false],
                    addWord() {
                        const idx = this.words.length - 1;
                        if (this.words[idx].trim() === '' || !this.valid[idx]) return;
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
                                const fields = this.$el.querySelectorAll('input[name^="words"]');
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
                        fetch('/api/search?q=' + encodeURIComponent(query) + '&lang=en')
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
    </div>
@endsection

