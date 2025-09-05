@extends('layouts.app')

@section('title', $test->name . ' - Step Test')

@section('content')
<div class="w-[800px] mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $test->name }} - Step Mode</h1>
        <a href="{{ route('saved-test.show', $test->slug) }}" class="text-sm text-blue-600 underline">Back</a>
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
    <div class="mb-4 text-sm">
        <span class="mr-2">Порядок питань:</span>
        <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'order' => 'sequential']) }}" class="underline mr-2 {{ $order === 'sequential' ? 'font-bold' : '' }}">По порядку</a>
        <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'order' => 'random']) }}" class="underline {{ $order === 'random' ? 'font-bold' : '' }}">Випадково</a>
    </div>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Question: <b>{{ $questionNumber }} / {{ $totalCount }}</b></div>
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

    <div class="mb-4 flex justify-between">
        @if($hasPrev)
            <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'nav' => 'prev', 'order' => $order]) }}" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Prev</a>
        @else
            <span class="bg-gray-100 px-4 py-1 rounded text-sm text-gray-400">Prev</span>
        @endif
        @if($hasNext)
            <a href="{{ route('saved-test.step', ['slug' => $test->slug, 'nav' => 'next', 'order' => $order]) }}" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Next</a>
        @else
            <span class="bg-gray-100 px-4 py-1 rounded text-sm text-gray-400">Next</span>
        @endif
    </div>

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

    <form method="POST" action="{{ route('saved-test.step.check', $test->slug) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">
        @php
            $autocompleteRoute = url('/api/search?lang=en');
        @endphp
        <div id="question-level" class="mt-1 space-x-1"></div>
        @include('components.question-input', [
            'question' => $question,
            'inputNamePrefix' => 'answers',
            'arrayInput' => true,
            'manualInput' => false,
            'autocompleteInput' => false,
            'builderInput' => true,
            'autocompleteRoute' => $autocompleteRoute,
            'showVerbHintEdit' => true,
            'showQuestionEdit' => true,
        ])
        <div class="flex gap-2 mt-2">
            <a href="{{ route('question-review.edit', $question->id) }}" class="text-sm text-blue-600 underline">Review</a>
            <button type="submit" form="delete-question-{{ $question->id }}" class="text-sm text-red-600 underline" onclick="return confirm('Delete this question?')">Delete</button>
        </div>
        @php
            $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
        @endphp
        <div id="question-tags" class="mt-1 space-x-1"></div>
        <div class="mt-2 space-y-2">
            <div class="space-x-2">
                <button type="button" id="determine-tense-gpt" class="text-xs text-blue-600 underline">Визначити час ChatGPT</button>
                <button type="button" id="determine-tense-gemini" class="text-xs text-blue-600 underline">Визначити час Gemini</button>
            </div>
            <div class="flex gap-4">
                <div id="tense-result-gpt" class="text-sm text-gray-700 space-y-1"></div>
                <div id="tense-result-gemini" class="text-sm text-gray-700 space-y-1"></div>
            </div>
            <div class="space-y-1">
                <div class="space-x-2">
                    <button type="button" id="determine-level-gpt" class="text-xs text-blue-600 underline">Визначити рівень ChatGPT</button>
                    <span id="level-result-gpt" class="inline text-sm text-gray-700" data-level=""></span>
                    <button type="button" id="set-level-gpt" class="text-xs text-blue-600 underline hidden">Set level</button>
                </div>
                <div class="space-x-2">
                    <button type="button" id="determine-level-gemini" class="text-xs text-blue-600 underline">Визначити рівень Gemini</button>
                    <span id="level-result-gemini" class="inline text-sm text-gray-700" data-level=""></span>
                    <button type="button" id="set-level-gemini" class="text-xs text-blue-600 underline hidden">Set level</button>
                </div>
            </div>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
            Check
        </button>
    </form>
    <form id="delete-question-{{ $question->id }}" action="{{ route('saved-test.question.destroy', [$test->slug, $question->id]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
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
                .then(data => { this.suggestions[index] = data.map(i => i.en); });
        },
        selectSuggestion(index, val) {
            this.words[index] = val;
            this.valid[index] = true;
            this.suggestions[index] = [];
        }
    }
}

const tagColors = @json($colors);
let selectedGptTags = [];
let selectedGeminiTags = [];

function renderTags(tags) {
    const container = document.getElementById('question-tags');
    container.innerHTML = '';
    tags.forEach((name, index) => {
        const wrap = document.createElement('span');
        wrap.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ' + tagColors[index % tagColors.length];
        const link = document.createElement('a');
        link.href = '{{ route('saved-tests.cards') }}?tag=' + encodeURIComponent(name);
        link.className = 'hover:underline';
        link.textContent = name;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = 'x';
        btn.className = 'ml-1 text-xs text-red-600';
        btn.addEventListener('click', () => removeTag(name));
        wrap.appendChild(link);
        wrap.appendChild(btn);
        container.appendChild(wrap);
    });
}

function renderSelected(container, tags) {
    container.innerHTML = '';
    tags.forEach(tag => {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-1';
        const span = document.createElement('span');
        span.textContent = tag;
        const btn = document.createElement('button');
        btn.textContent = 'x';
        btn.className = 'text-xs text-red-600 underline';
        btn.type = 'button';
        btn.addEventListener('click', () => removeTag(tag));
        wrapper.appendChild(span);
        wrapper.appendChild(btn);
        container.appendChild(wrapper);
    });
}

function addTag(tag) {
    return fetch('{{ route('saved-test.step.add-tag', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }}, tag})
    })
        .then(r => r.json())
        .then(d => {
            if (Array.isArray(d.tags)) {
                renderTags(d.tags);
            }
        });
}

function removeTag(tag) {
    return fetch('{{ route('saved-test.step.remove-tag', $test->slug) }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }}, tag})
    })
        .then(r => r.json())
        .then(d => {
            if (Array.isArray(d.tags)) {
                renderTags(d.tags);
                selectedGptTags = selectedGptTags.filter(t => t !== tag);
                selectedGeminiTags = selectedGeminiTags.filter(t => t !== tag);
                renderSelected(document.getElementById('tense-result-gpt'), selectedGptTags);
                renderSelected(document.getElementById('tense-result-gemini'), selectedGeminiTags);
            }
        });
}

renderTags(@json($question->tags->pluck('name')));

const levels = ['A1','A2','B1','B2','C1','C2'];

function renderLevel(selected) {
    const container = document.getElementById('question-level');
    container.innerHTML = '';
    levels.forEach(level => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.level = level;
        btn.textContent = level;
        btn.className = 'px-2 py-0.5 rounded text-xs font-semibold mr-1 ' + (level === selected ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800');
        btn.addEventListener('click', () => setLevel(level));
        container.appendChild(btn);
    });
}

function setLevel(level) {
    fetch('{{ route('saved-test.step.set-level', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }}, level})
    }).then(() => renderLevel(level));
}

renderLevel(@json($question->level));

document.getElementById('determine-tense-gpt').addEventListener('click', () => {
    fetch('{{ route('saved-test.step.determine-tense', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }} })
    })
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('tense-result-gpt');
            container.innerHTML = '';
            if (Array.isArray(d.tags)) {
                d.tags.forEach(tag => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center gap-1';
                    const span = document.createElement('span');
                    span.textContent = tag;
                    const btn = document.createElement('button');
                    btn.textContent = 'Додати тег';
                    btn.className = 'text-xs text-blue-600 underline';
                    btn.type = 'button';
                    btn.addEventListener('click', () => {
                        addTag(tag).then(() => {
                            if (!selectedGptTags.includes(tag)) {
                                selectedGptTags.push(tag);
                            }
                            renderSelected(container, selectedGptTags);
                        });
                    });
                    wrapper.appendChild(span);
                    wrapper.appendChild(btn);
                    container.appendChild(wrapper);
                });
            }
        });
});

document.getElementById('determine-tense-gemini').addEventListener('click', () => {
    fetch('{{ route('saved-test.step.determine-tense-gemini', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }} })
    })
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('tense-result-gemini');
            container.innerHTML = '';
            if (Array.isArray(d.tags)) {
                d.tags.forEach(tag => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center gap-1';
                    const span = document.createElement('span');
                    span.textContent = tag;
                    const btn = document.createElement('button');
                    btn.textContent = 'Додати тег';
                    btn.className = 'text-xs text-blue-600 underline';
                    btn.type = 'button';
                    btn.addEventListener('click', () => {
                        addTag(tag).then(() => {
                            if (!selectedGeminiTags.includes(tag)) {
                                selectedGeminiTags.push(tag);
                            }
                            renderSelected(container, selectedGeminiTags);
                        });
                    });
                    wrapper.appendChild(span);
                    wrapper.appendChild(btn);
                    container.appendChild(wrapper);
                });
            }
        });
});

document.getElementById('determine-level-gpt').addEventListener('click', () => {
    fetch('{{ route('saved-test.step.determine-level', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }} })
    })
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('level-result-gpt');
            container.textContent = d.level ? 'ChatGPT: ' + d.level : '';
            container.dataset.level = d.level || '';
            document.getElementById('set-level-gpt').classList.toggle('hidden', !d.level);
        });
});

document.getElementById('determine-level-gemini').addEventListener('click', () => {
    fetch('{{ route('saved-test.step.determine-level-gemini', $test->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({question_id: {{ $question->id }} })
    })
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('level-result-gemini');
            container.textContent = d.level ? 'Gemini: ' + d.level : '';
            container.dataset.level = d.level || '';
            document.getElementById('set-level-gemini').classList.toggle('hidden', !d.level);
        });
});

document.getElementById('set-level-gpt').addEventListener('click', () => {
    const level = document.getElementById('level-result-gpt').dataset.level;
    if (!level) return;
    setLevel(level);
});

document.getElementById('set-level-gemini').addEventListener('click', () => {
    const level = document.getElementById('level-result-gemini').dataset.level;
    if (!level) return;
    setLevel(level);
});
</script>
@endsection
