@extends('layouts.app')

@section('title', 'Grammar Test Constructor')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    @php
        $autocompleteRoute = route('grammar-test.autocomplete');
        $checkOneRoute = route('grammar-test.checkOne');
        $sources = \App\Models\Source::orderBy('name')->get();
    @endphp
    
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Конструктор тесту</h1>
            <a href="{{ route('saved-tests.list') }}"
               class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100">
                Збережені тести
            </a>
        </div>
    
    
    {{-- Конструктор тесту --}}
    <form action="{{ route('grammar-test.generate') }}" method="POST" class="mb-8 space-y-4 bg-white shadow rounded-2xl p-4">
        @csrf
        <div>
            <label class="block font-bold mb-1">Часи (категорії):</label>
            <div class="flex gap-4">
                @foreach($categories as $cat)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                            {{ isset($selectedCategories) && in_array($cat->id, $selectedCategories) ? 'checked' : '' }}
                            class="form-checkbox h-5 w-5 text-blue-600"
                        >
                        <span class="ml-2">{{ ucfirst($cat->name) }}</span>
                    </label>
                @endforeach
            </div>
            @error('categories')
                <div class="text-red-500 text-sm">{{ $message }}</div>
            @enderror
        </div>
        @if($sources->count())
            <div>
                <label class="block font-bold mb-1">Source:</label>
                <div class="flex flex-wrap gap-4">
                    @foreach($sources as $source)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="sources[]" value="{{ $source->id }}"
                                {{ isset($selectedSources) && in_array($source->id, $selectedSources) ? 'checked' : '' }}
                                class="form-checkbox h-5 w-5 text-indigo-600"
                            >
                            <span class="ml-2">{{ $source->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    
        <div>
            <label class="block font-bold mb-1">Діапазон складності:</label>
            <div class="flex gap-2 items-center">
                <input type="number" min="{{ $minDifficulty }}" max="{{ $maxDifficulty }}" name="difficulty_from"
                    value="{{ $difficultyFrom ?? $minDifficulty }}" class="border rounded p-1 w-20">
                <span class="mx-2">—</span>
                <input type="number" min="{{ $minDifficulty }}" max="{{ $maxDifficulty }}" name="difficulty_to"
                    value="{{ $difficultyTo ?? $maxDifficulty }}" class="border rounded p-1 w-20">
            </div>
           
        </div>
        <div>
            <label class="block font-bold mb-1">Кількість питань у тесті:</label>
            <input type="number" min="1" max="{{ $maxQuestions ?? $maxQuestions }}" name="num_questions"
                value="{{ $numQuestions ?? $maxQuestions }}" class="border rounded p-1 w-20">
            @if(isset($maxQuestions))
                <span class="text-xs text-gray-500">(доступно: {{ $maxQuestions }})</span>
            @endif
        </div>
        <div class="flex gap-8 items-center"
             x-data="{
                manual: {{ !empty($manualInput) ? 'true' : 'false' }},
                auto: {{ !empty($autocompleteInput) ? 'true' : 'false' }},
                checkOne: {{ !empty($checkOneInput) ? 'true' : 'false' }}
             }"
        >
            <label class="inline-flex items-center">
                <input type="checkbox" name="manual_input" value="1"
                    x-model="manual"
                    class="form-checkbox h-5 w-5 text-blue-600"
                    @change="if (!manual) auto = false"
                >
                <span class="ml-2">Ввести відповідь вручну</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="autocomplete_input" value="1"
                    x-model="auto"
                    :disabled="!manual"
                    class="form-checkbox h-5 w-5 text-green-600"
                >
                <span class="ml-2 text-gray-500">Автозаповнення всіх правильних відповідей</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="check_one_input" value="1"
                    x-model="checkOne"
                    class="form-checkbox h-5 w-5 text-purple-600"
                >
                <span class="ml-2 text-purple-700">Перевіряти окремо</span>
            </label>
        </div>

        <div class="flex gap-8 items-center">
            {{-- ...інші чекбокси... --}}
            <label class="inline-flex items-center">
                <input type="checkbox" name="include_ai" value="1"
                    {{ !empty($includeAi) ? 'checked' : '' }}
                    class="form-checkbox h-5 w-5 text-yellow-600"
                >
                <span class="ml-2 text-yellow-700">Додати AI-згенеровані питання</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="only_ai" value="1"
                    {{ !empty($onlyAi) ? 'checked' : '' }}
                    class="form-checkbox h-5 w-5 text-orange-600"
                >
                <span class="ml-2 text-orange-700">Тільки AI-згенеровані питання</span>
            </label>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg">
            Згенерувати тест
        </button>
    </form>
    @if(!empty($questions))
        <div class="text-sm text-gray-500 mb-6">
            Кількість питань: {{ count($questions) }}
        </div>
    @endif
    {{-- Сам тест --}}
    @if(!empty($questions) && count($questions))
        <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
            @csrf
            @foreach($questions as $q)
                <input type="hidden" name="questions[{{ $q->id }}]" value="1">
                <div class="bg-white shadow rounded-2xl p-4 mb-4">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-base font-bold">
                            Категорія:
                            <span class="uppercase px-2 py-1 rounded text-xs 
                                {{ $q->category->name === 'past' ? 'bg-red-100 text-red-700' : ($q->category->name === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ ucfirst($q->category->name) }}
                            </span>
                            @if($q->source)
                                <span class="ml-2 text-xs text-gray-500">Source: {{ $q->source->name }}</span>
                            @endif
                            @if($q->flag)
                                <span class="inline-block ml-2 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                            @endif
                            <span class="text-xs text-gray-400">Складність: {{ $q->difficulty }}/10</span>
                        </span>
                         
                    </div>
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

                                // ==== AJAX autocomplete інпут на Alpine.js ====
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
                    @if(!empty($checkOneInput))
                        <button
                            type="button"
                            class="mt-4 bg-purple-600 text-white text-xs rounded px-2 py-1 hover:bg-purple-700"
                            onclick="checkFullQuestionAjax(this, '{{ $q->id }}', '{{ implode(',', array_map(function($n){return 'a'.$n;}, $matches[1])) }}')"
                        >Check answer</button>
                        <span class="ml-2 text-xs font-bold" id="result-question-{{ $q->id }}"></span>
                    @endif
                </div>
            @endforeach

               
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg">
                Перевірити
            </button>
        </form>

        @if(!empty($questions) && count($questions))
        <div class="mb-4 mt-6">
            <form action="{{ route('grammar-test.save') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="hidden" name="filters" value="{{ htmlentities(json_encode([
                    'categories' => $selectedCategories,
                    'difficulty_from' => $difficultyFrom,
                    'difficulty_to' => $difficultyTo,
                    'num_questions' => $numQuestions,
                    'manual_input' => $manualInput,
                    'autocomplete_input' => $autocompleteInput,
                    'check_one_input' => $checkOneInput,
                    'include_ai' => $includeAi ?? false,
                    'only_ai' => $onlyAi ?? false
                ])) }}">
                <input type="hidden" name="questions" value="{{ htmlentities(json_encode($questions->pluck('id'))) }}">
                <input type="text" name="name" value="{{$autoTestName}}" placeholder="Назва тесту" required
                    class="border rounded px-2 py-1 w-72">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-2xl shadow font-semibold">
                    Зберегти тест
                </button>
            </form>
        </div>
    @endif    
    @elseif(isset($questions))
        <div class="text-red-600 font-bold text-lg">Питань по вибраних параметрах не знайдено!</div>
    @endif
</div>

<script>
function checkFullQuestionAjax(btn, questionId, markerList) {
    let markers = markerList.split(',');
    let answers = {};
    markers.forEach(function(marker) {
        let input = btn.closest('.bg-white').querySelector(`[name="question_${questionId}_${marker}"]`);
        answers[marker] = input ? input.value : '';
    });
    let resultSpan = document.getElementById(`result-question-${questionId}`);
    let empty = Object.values(answers).some(val => !val);
    if(empty) {
        resultSpan.textContent = 'Введіть всі відповіді';
        resultSpan.className = 'ml-2 text-xs font-bold text-gray-500';
        return;
    }
    resultSpan.textContent = '...';
    resultSpan.className = 'ml-2 text-xs font-bold text-gray-500';
    fetch("{{ $checkOneRoute }}", {
        method: "POST",
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: JSON.stringify({
            question_id: questionId,
            answers: answers
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.result === 'correct') {
            resultSpan.textContent = '✔ Вірно';
            resultSpan.className = 'ml-2 text-xs font-bold text-green-700';
        } else if(data.result === 'incorrect') {
            let corrects = Object.values(data.correct).join(', ');
            resultSpan.textContent = '✘ Невірно (правильно: ' + corrects + ')';
            resultSpan.className = 'ml-2 text-xs font-bold text-red-700';
        } else {
            resultSpan.textContent = '—';
            resultSpan.className = 'ml-2 text-xs font-bold text-gray-500';
        }
    });
}
</script>
@endsection

