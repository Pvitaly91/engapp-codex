@extends('layouts.app')

@section('title', $test->name . ' - Step Test')

@section('content')
<div class="max-w-xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $test->name }} - Step Mode</h1>
        <a href="{{ route('saved-test.show', $test->slug) }}" class="text-sm text-blue-600 underline">Back</a>
    </div>
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
            $questionText = $question->question;
            preg_match_all('/\{a(\d+)\}/', $questionText, $matches);
            $replacements = [];
            foreach ($matches[0] as $i => $marker) {
                $num = $matches[1][$i];
                $markerKey = 'a' . $num;
                $inputName = "answers[{$markerKey}]";
                $verbHintRow = $question->verbHints->where('marker', $markerKey)->first();
                $verbHint = $verbHintRow?->option?->option;
                $methods = ['select', 'text', 'autocomplete', 'builder'];
                $method = $methods[array_rand($methods)];
                $autocompleteRoute = route('grammar-test.autocomplete');
                if($method === 'autocomplete') {
                    $input = <<<HTML
<div x-data="{open:false,value:'',suggestions:[],fetch(){if(this.value.length==0){this.suggestions=[];this.open=false;return;}fetch('{$autocompleteRoute}?q='+encodeURIComponent(this.value)).then(res=>res.json()).then(data=>{this.suggestions=data;this.open=!!this.suggestions.length;});},pick(val){this.value=val;this.open=false;}}" class="relative inline-block" @click.away="open=false" x-init="\$watch('value', fetch)">
    <input type="text" name="{$inputName}" required autocomplete="off" class="border rounded px-2 py-1 mx-1" x-model="value" @focus="fetch(); open=true" @input="fetch(); open=true">
    <template x-if="open && suggestions.length">
        <ul class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full" style="min-width:120px">
            <template x-for="item in suggestions" :key="item">
                <li @mousedown.prevent="pick(item)" class="cursor-pointer px-3 py-1 hover:bg-blue-100" x-text="item"></li>
            </template>
        </ul>
    </template>
</div>
HTML;
                } elseif($method === 'builder') {
                    $input = <<<HTML
<div x-data="builder('{$autocompleteRoute}', '{$inputName}[')" class="inline-flex items-center gap-[3px]">
    <template x-for="(word, index) in words" :key="index">
        <div class="relative">
            <input type="text" :name="'{$inputName}['+index+']'" class="border rounded px-2 py-1 w-[70%]" autocomplete="off"
                   x-model="words[index]" pattern="^\\S+$" title="One word only"
                   @keydown.space.prevent="completeWord(index)" @focus="fetchSuggestions(index)" @input="fetchSuggestions(index)" required>
            <template x-if="suggestions[index] && suggestions[index].length">
                <ul class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full">
                    <template x-for="suggestion in suggestions[index]" :key="suggestion">
                        <li class="cursor-pointer px-3 py-1 hover:bg-blue-100" @mousedown.prevent="selectSuggestion(index, suggestion)" x-text="suggestion"></li>
                    </template>
                </ul>
            </template>
        </div>
    </template>
    <button type="button" @click="addWord" class="bg-gray-200 px-2 py-1 rounded order-last ml-[3px]">+</button>
</div>
HTML;
                } elseif($method === 'text') {
                    $input = '<input type="text" name="'.$inputName.'" required autocomplete="off" class="border rounded px-2 py-1 mx-1">';
                } else {
                    $input = '<select name="'.$inputName.'" required class="border rounded px-2 py-1 mx-1">';
                    $input .= '<option value="">---</option>';
                    foreach($question->options as $opt){
                        $input .= '<option value="'.$opt->option.'">'.$opt->option.'</option>';
                    }
                    $input .= '</select>';
                }
                if($verbHint){
                    $input .= ' <span class="text-red-700 text-xs font-bold">('.e($verbHint).')</span>';
                }
                $replacements[$marker] = $input;
            }
            $finalQuestion = strtr(e($questionText), $replacements);
        @endphp
        <label class="text-base block" style="white-space:normal">{!! $finalQuestion !!}</label>
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
            fetch(route + '?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => { this.suggestions[index] = data; });
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
