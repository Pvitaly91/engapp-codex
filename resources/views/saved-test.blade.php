@extends('layouts.app')

@section('title', $test->name)

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold mb-2">{{ $test->name }}</h1>
        <a href="{{ route('saved-tests.list') }}"
           class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100">
            ← До списку тестів
        </a>
    </div>
    <div class="text-sm text-gray-500 mb-6">
        Кількість питань: {{ count($questions) }}
    </div>
    <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
        @csrf
        @foreach($questions as $q)
            <input type="hidden" name="questions[{{ $q->id }}]" value="1">
            <div class="bg-white shadow rounded-2xl p-4 mb-4">
                <div class="flex flex-wrap gap-2">
                    @php
                        $questionText = $q->question;
                        preg_match_all('/\{a(\d+)\}/', $questionText, $matches);
                        $replacements = [];
                        foreach ($matches[0] as $i => $marker) {
                            $num = $matches[1][$i];
                            $markerKey = 'a' . $num;
                            $inputName = "question_{$q->id}_{$markerKey}";
                            $answerRow = $q->answers->where('marker', $markerKey)->first();
                            $autocompleteRoute = route('grammar-test.autocomplete');

                            // ==== AJAX autocomplete input ====
                            if(!empty($manualInput) && !empty($autocompleteInput)) {
                                $input = <<<HTML
<div
    x-data="{
        open: false,
        value: '',
        suggestions: [],
        fetchSuggestions() {
            if (this.value.length === 0) {
                this.suggestions = [];
                this.open = false;
                return;
            }
            fetch('{$autocompleteRoute}?q=' + encodeURIComponent(this.value))
                .then(res => res.json())
                .then(data => {
                    this.suggestions = data;
                    this.open = !!this.suggestions.length;
                });
        },
        pick(val) {
            this.value = val;
            this.open = false;
        }
    }"
    class="relative inline-block"
    @click.away="open=false"
    x-init="\$watch('value', fetchSuggestions)"
>
    <input
        type="text"
        name="{$inputName}"
        required
        autocomplete="off"
        class="border rounded px-2 py-1 mx-1"
        x-model="value"
        @focus="fetchSuggestions(); open = true"
        @input="fetchSuggestions(); open = true"
    >
    <template x-if="open && suggestions.length">
        <ul
            class="absolute left-0 z-10 bg-white shadow-lg border mt-1 max-h-40 rounded-md overflow-auto w-full"
            style="min-width:120px"
        >
            <template x-for="item in suggestions" :key="item">
                <li
                    @mousedown.prevent="pick(item)"
                    class="cursor-pointer px-3 py-1 hover:bg-blue-100"
                    x-text="item"
                ></li>
            </template>
        </ul>
    </template>
</div>
HTML;
                            }
                            // ==== Простий ручний інпут ====
                            elseif(!empty($manualInput)) {
                                $input = '<input type="text" name="'.$inputName.'" required class="border rounded px-2 py-1 mx-1">';
                            }
                            // ==== select ====
                            else {
                                $input = '<select name="'.$inputName.'" required class="border rounded px-2 py-1 mx-1">';
                                $input .= '<option value="">---</option>';
                                foreach($q->options as $opt){
                                    $input .= '<option value="'.$opt->option.'">'.$opt->option.'</option>';
                                }
                                $input .= '</select>';
                            }
                            $replacements[$marker] = $input;
                        }
                        $finalQuestion = strtr(e($questionText), $replacements);
                    @endphp
                    <label class="text-base" style="white-space:normal">{!! $finalQuestion !!}</label>
                </div>
            </div>
        @endforeach

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg">
            Перевірити
        </button>
    </form>
</div>
@endsection

