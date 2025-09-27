@extends('layouts.app')

@section('title', 'Grammar Test Constructor V2')

@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6">
    @php
        $autocompleteRoute = url('/api/search?lang=en');
        $checkOneRoute = route('grammar-test.checkOne');
        $generateRoute = $generateRoute ?? route('grammar-test-v2.generate');
        $saveRoute = $saveRoute ?? route('grammar-test-v2.save');
        $savePayloadField = $savePayloadField ?? 'question_uuids';
        $savePayloadKey = $savePayloadKey ?? 'uuid';
        $questions = collect($questions ?? []);
        $categoryCollection = $categoriesDesc ?? $categories ?? collect();
        $tagGroups = $tagsByCategory ?? collect();
        $sourceGroups = $sourcesByCategory ?? collect();
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h1 class="text-2xl font-bold">Конструктор тесту (v2)</h1>
        <a href="{{ route('saved-tests.list') }}"
           class="inline-flex items-center justify-center bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-2xl font-semibold hover:bg-blue-100 transition">
            Збережені тести
        </a>
    </div>

    <form action="{{ $generateRoute }}" method="POST" class="bg-white shadow rounded-2xl p-4 sm:p-6">
        @csrf
        
            <div class="space-y-6 ">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Часи (категорії)</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categoryCollection as $cat)
                            <label class="flex items-center gap-2 text-sm bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 hover:border-blue-300 transition">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                    {{ isset($selectedCategories) && in_array($cat->id, $selectedCategories) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded">
                                <span class="truncate">{{ ucfirst($cat->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('categories')
                        <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>

                @if($sourceGroups->isNotEmpty())
                    <div x-data="{ openSources: false }" class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Джерела по категоріях</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openSources = !openSources">
                                <span x-text="openSources ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSources }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="openSources" x-transition style="display: none;">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($sourceGroups as $group)
                                    <div x-data="{ open: false }" class="border border-gray-200 rounded-2xl overflow-hidden h-full">
                                        <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                            <span class="truncate">{{ ucfirst($group['category']->name) }} (ID: {{ $group['category']->id }})</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($group['sources'] as $source)
                                                    <label class="flex items-start gap-2 px-3 py-1 rounded-full border border-gray-200 text-sm bg-white hover:border-blue-300 transition text-left">
                                                        <input type="checkbox" name="sources[]" value="{{ $source->id }}"
                                                            {{ isset($selectedSources) && in_array($source->id, $selectedSources) ? 'checked' : '' }}
                                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                        <span class="whitespace-normal break-words">{{ $source->name }} (ID: {{ $source->id }})</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($levels) && count($levels))
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Level</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($levels as $lvl)
                                @php $levelId = 'level-' . md5($lvl); @endphp
                                <div>
                                    <input type="checkbox" name="levels[]" value="{{ $lvl }}" id="{{ $levelId }}"
                                           class="hidden peer"
                                           {{ in_array($lvl, $selectedLevels ?? []) ? 'checked' : '' }}>
                                    <label for="{{ $levelId }}" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white">
                                        {{ $lvl }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($tagGroups->isNotEmpty())
                    <div x-data="{ openTags: true }" class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-gray-700">Tags</h2>
                            <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1 rounded-full bg-blue-50 hover:bg-blue-100 transition"
                                    @click="openTags = !openTags">
                                <span x-text="openTags ? 'Згорнути' : 'Розгорнути'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openTags }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3" x-show="openTags" x-transition style="display: none;">
                            @foreach($tagGroups as $tagCategory => $tags)
                                <div x-data="{ open: {{ $loop->index < 1 ? 'true' : 'false' }} }" class="border border-gray-200 rounded-2xl overflow-hidden">
                                    <button type="button" class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 text-left font-semibold text-gray-800"
                                            @click="open = !open">
                                        <span>{{ $tagCategory }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition style="display: none;" class="px-4 pb-4 pt-2">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($tags as $tag)
                                                @php $tagId = 'tag-' . md5($tag->id . '-' . $tag->name); @endphp
                                                <div>
                                                    <input type="checkbox" name="tags[]" value="{{ $tag->name }}" id="{{ $tagId }}" class="hidden peer"
                                                           {{ in_array($tag->name, $selectedTags ?? []) ? 'checked' : '' }}>
                                                    <label for="{{ $tagId }}" class="px-3 py-1 rounded-full border border-gray-200 cursor-pointer text-sm bg-gray-100 peer-checked:bg-blue-600 peer-checked:text-white">
                                                        {{ $tag->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Діапазон складності</label>
                        <div class="flex items-center gap-2">
                            <input type="number" min="{{ $minDifficulty }}" max="{{ $maxDifficulty }}" name="difficulty_from"
                                   value="{{ $difficultyFrom ?? $minDifficulty }}"
                                   class="border rounded-lg px-3 py-2 w-full">
                            <span class="text-gray-400">—</span>
                            <input type="number" min="{{ $minDifficulty }}" max="{{ $maxDifficulty }}" name="difficulty_to"
                                   value="{{ $difficultyTo ?? $maxDifficulty }}"
                                   class="border rounded-lg px-3 py-2 w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Кількість питань</label>
                        <input type="number" min="1" max="{{ $maxQuestions ?? $maxQuestions }}" name="num_questions"
                               value="{{ $numQuestions ?? $maxQuestions }}" class="border rounded-lg px-3 py-2 w-full">
                        @if(isset($maxQuestions))
                            <p class="text-xs text-gray-500 mt-1">Доступно: {{ $maxQuestions }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2"
                     x-data="{
                        manual: {{ !empty($manualInput) ? 'true' : 'false' }},
                        auto: {{ !empty($autocompleteInput) ? 'true' : 'false' }},
                        checkOne: {{ !empty($checkOneInput) ? 'true' : 'false' }},
                        builder: {{ !empty($builderInput) ? 'true' : 'false' }}
                     }"
                     x-init="if (!manual) { auto = false; builder = false; }"
                >
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="manual_input" value="1"
                               class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded"
                               x-model="manual"
                               @change="if (!manual) { auto = false; builder = false; }"
                               {{ !empty($manualInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Ввести відповідь вручну</span>
                            <span class="block text-xs text-gray-500">Дає можливість вводити відповіді самостійно</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="autocomplete_input" value="1"
                               class="mt-1 h-5 w-5 text-green-600 border-gray-300 rounded"
                               x-model="auto"
                               :disabled="!manual"
                               {{ !empty($autocompleteInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Автозаповнення відповідей</span>
                            <span class="block text-xs text-gray-500">Підставляє правильні відповіді при ручному вводі</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="check_one_input" value="1"
                               class="mt-1 h-5 w-5 text-purple-600 border-gray-300 rounded"
                               x-model="checkOne"
                               {{ !empty($checkOneInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Перевіряти окремо</span>
                            <span class="block text-xs text-gray-500">Дозволяє перевіряти питання одне за одним</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl bg-gray-50">
                        <input type="checkbox" name="builder_input" value="1"
                               class="mt-1 h-5 w-5 text-emerald-600 border-gray-300 rounded"
                               x-model="builder"
                               :disabled="!manual"
                               {{ !empty($builderInput) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Вводити по словах</span>
                            <span class="block text-xs text-gray-500">Розбиває відповідь на окремі слова</span>
                        </span>
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai" value="1"
                               class="mt-1 h-5 w-5 text-yellow-600 border-gray-300 rounded"
                               {{ !empty($includeAi) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Додати AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Включає питання з прапорцем AI</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai" value="1"
                               class="mt-1 h-5 w-5 text-orange-600 border-gray-300 rounded"
                               {{ !empty($onlyAi) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Тільки AI-згенеровані питання</span>
                            <span class="block text-xs text-gray-500">Обмежує вибір лише AI питаннями</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="include_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-sky-600 border-gray-300 rounded"
                               {{ !empty($includeAiV2) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Додати AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Додає питання з новим AI прапорцем</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-2xl">
                        <input type="checkbox" name="only_ai_v2" value="1"
                               class="mt-1 h-5 w-5 text-cyan-600 border-gray-300 rounded"
                               {{ !empty($onlyAiV2) ? 'checked' : '' }}>
                        <span>
                            <span class="block font-semibold">Тільки AI (flag = 2)</span>
                            <span class="block text-xs text-gray-500">Залишає лише питання з прапорцем 2</span>
                        </span>
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                        Згенерувати тест
                    </button>
                </div>
            </div>
       
    </form>

    @if(!empty($questions))
        <div class="text-sm text-gray-500">Кількість питань: {{ count($questions) }}</div>
    @endif

    @if(!empty($questions) && count($questions))
        <form action="{{ route('grammar-test.check') }}" method="POST" class="space-y-6">
            @csrf
            @foreach($questions as $q)
                <input type="hidden" name="questions[{{ $q->id }}]" value="1">
                <div class="bg-white shadow rounded-2xl p-4 sm:p-6 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2">
                            <span class="uppercase px-2 py-1 rounded text-xs {{ $q->category->name === 'past' ? 'bg-red-100 text-red-700' : ($q->category->name === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ ucfirst($q->category->name) }}
                            </span>
                            @if($q->source)
                                <span class="text-xs text-gray-500">Source: {{ $q->source->name }}</span>
                            @endif
                            @if($q->flag)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                            @endif
                            <span class="text-xs text-gray-400">Складність: {{ $q->difficulty }}/10</span>
                            <span class="text-xs text-gray-400">Level: {{ $q->level ?? 'N/A' }}</span>
                        </div>
                        <span class="text-xs text-gray-400">ID: {{ $q->id }} | UUID: {{ $q->uuid ?? '—' }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 items-baseline">
                        <span class="font-bold mr-2">{{ $loop->iteration }}.</span>
                        @php preg_match_all('/\{a(\d+)\}/', $q->question, $matches); @endphp
                        @include('components.question-input', [
                            'question' => $q,
                            'inputNamePrefix' => "question_{$q->id}_",
                            'manualInput' => $manualInput,
                            'autocompleteInput' => $autocompleteInput,
                            'builderInput' => $builderInput,
                            'autocompleteRoute' => $autocompleteRoute,
                        ])
                    </div>
                    @if($q->tags->count())
                        <div class="flex flex-wrap gap-1">
                            @php
                                $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                            @endphp
                            @foreach($q->tags as $tag)
                                <a href="{{ route('saved-tests.cards', ['tag' => $tag->name]) }}" class="inline-flex px-2 py-0.5 rounded text-xs font-semibold hover:underline {{ $colors[$loop->index % count($colors)] }}">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($checkOneInput))
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="mt-1 bg-purple-600 text-white text-xs rounded px-3 py-1 hover:bg-purple-700"
                                onclick="checkFullQuestionAjax(this, '{{ $q->id }}', '{{ implode(',', array_map(function($n){return 'a'.$n;}, $matches[1])) }}')"
                            >
                                Check answer
                            </button>
                            <span class="text-xs font-bold" id="result-question-{{ $q->id }}"></span>
                        </div>
                    @endif
                </div>
            @endforeach

            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl shadow font-semibold text-lg transition">
                    Перевірити
                </button>
            </div>
        </form>

        <div class="bg-white shadow rounded-2xl p-4 sm:p-6">
            <form action="{{ $saveRoute }}" method="POST" class="flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <input type="hidden" name="filters" value="{{ htmlentities(json_encode([
                    'categories' => $selectedCategories,
                    'difficulty_from' => $difficultyFrom,
                    'difficulty_to' => $difficultyTo,
                    'num_questions' => $numQuestions,
                    'manual_input' => $manualInput,
                    'autocomplete_input' => $autocompleteInput,
                    'check_one_input' => $checkOneInput,
                    'builder_input' => $builderInput,
                    'include_ai' => $includeAi ?? false,
                    'only_ai' => $onlyAi ?? false,
                    'include_ai_v2' => $includeAiV2 ?? false,
                    'only_ai_v2' => $onlyAiV2 ?? false,
                    'levels' => $selectedLevels ?? [],
                    'tags' => $selectedTags ?? [],
                    'sources' => $selectedSources ?? [],
                ])) }}">
                <input type="hidden" name="{{ $savePayloadField }}" value="{{ htmlentities(json_encode($questions->pluck($savePayloadKey))) }}">
                <input type="text" name="name" value="{{ $autoTestName }}" placeholder="Назва тесту" required autocomplete="off"
                       class="border rounded-lg px-3 py-2 w-full sm:w-80">
                <button type="submit" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-2xl shadow font-semibold transition">
                    Зберегти тест
                </button>
            </form>
        </div>
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
        resultSpan.className = 'text-xs font-bold text-gray-500';
        return;
    }
    resultSpan.textContent = '...';
    resultSpan.className = 'text-xs font-bold text-gray-500';
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
            resultSpan.className = 'text-xs font-bold text-green-700';
        } else if(data.result === 'incorrect') {
            let corrects = Object.values(data.correct).join(', ');
            resultSpan.textContent = '✘ Невірно (правильно: ' + corrects + ')';
            resultSpan.className = 'text-xs font-bold text-red-700';
        } else {
            resultSpan.textContent = '—';
            resultSpan.className = 'text-xs font-bold text-gray-500';
        }
    });
}

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
