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

    @php
        $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
    @endphp

    @if($question)
        <p><strong>Refferance:</strong> {{session('ai_step.refferance')}}</p>
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
                $obj = (object)['question'=>$question['question'], 'verbHints'=>$hintsCol, 'options'=>collect(), 'answers'=>$answersCol, 'level'=>$question['level'] ?? null];
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
            <div class="text-xs text-gray-500 mb-1" id="question-level">Level: {{ $question['level'] ?? 'N/A' }}</div>
        @include('components.question-input', [
                'question' => $obj,
                'inputNamePrefix' => 'answers',
                'arrayInput' => true,
                'manualInput' => false,
                'autocompleteInput' => false,
                'builderInput' => true,
            ])
            <div id="question-tags" class="mt-1 space-x-1">
                @foreach($tenseNames as $tag)
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $colors[$loop->index % count($colors)] }}">{{ $tag }}</span>
                @endforeach
            </div>
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
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
                    {{ isset($feedback) ? 'Next' : 'Check' }}
                </button>
                @if(!isset($feedback) || !$feedback['isCorrect'])
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

function renderTags(tags) {
    const container = document.getElementById('question-tags');
    container.innerHTML = '';
    tags.forEach((name, index) => {
        const span = document.createElement('span');
        span.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold ' + tagColors[index % tagColors.length];
        span.textContent = name;
        container.appendChild(span);
    });
}

function addTag(tag) {
    fetch('{{ route('ai-test.step.add-tag') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({tag})
    })
        .then(r => r.json())
        .then(d => {
            if (Array.isArray(d.tags)) {
                renderTags(d.tags);
            }
        });
}

document.getElementById('determine-tense-gpt').addEventListener('click', () => {
    fetch('{{ route('ai-test.step.determine-tense') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
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
                    btn.addEventListener('click', () => addTag(tag));
                    wrapper.appendChild(span);
                    wrapper.appendChild(btn);
                    container.appendChild(wrapper);
                });
            }
        });
});

document.getElementById('determine-tense-gemini').addEventListener('click', () => {
    fetch('{{ route('ai-test.step.determine-tense-gemini') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
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
                    btn.addEventListener('click', () => addTag(tag));
                    wrapper.appendChild(span);
                    wrapper.appendChild(btn);
                    container.appendChild(wrapper);
                });
            }
        });
});

document.getElementById('determine-level-gpt').addEventListener('click', () => {
    fetch('{{ route('ai-test.step.determine-level') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
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
    fetch('{{ route('ai-test.step.determine-level-gemini') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
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
    fetch('{{ route('ai-test.step.set-level') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({level})
    }).then(() => {
        document.getElementById('question-level').textContent = 'Level: ' + level;
    });
});

document.getElementById('set-level-gemini').addEventListener('click', () => {
    const level = document.getElementById('level-result-gemini').dataset.level;
    if (!level) return;
    fetch('{{ route('ai-test.step.set-level') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({level})
    }).then(() => {
        document.getElementById('question-level').textContent = 'Level: ' + level;
    });
});

@if($question)
fetch('{{ route('ai-test.next') }}');
@endif
</script>
@endsection
