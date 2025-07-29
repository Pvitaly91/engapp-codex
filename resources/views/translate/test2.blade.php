@extends('layouts.app')

@section('title', 'Sentence Translation Test V2')

@section('content')
    <div class="mx-auto mt-8 p-8 bg-white rounded-xl shadow w-[800px]">
        <h2 class="text-2xl font-bold mb-4 text-blue-700">Sentence Translation Test V2</h2>

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
                <button type="button" @click="addWord" class="bg-gray-200 px-2 py-1 rounded order-first">+</button>
                <template x-for="(word, index) in words" :key="index">
                    <div class="relative">
                        <input type="text" :name="'words['+index+']'" class="border rounded px-2 py-1 w-1/2" autocomplete="off"
                               x-model="words[index]"
                               pattern="^\S+$" title="One word only"
                               @keydown.space.prevent
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
                    addWord() {
                        this.words.push('');
                        this.suggestions.push([]);
                    },
                    fetchSuggestions(index) {
                        const query = this.words[index];
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
                        this.suggestions[index] = [];
                    }
                }
            }
        </script>
    </div>
@endsection

