@php
    $questionSaveValue = $question->{$savePayloadKey} ?? $question->id;
@endphp
<div class="question-item" data-question-id="{{ $question->id }}" data-question-save="{{ $questionSaveValue }}">
    <input type="hidden" name="questions[{{ $question->id }}]" value="1">
    <div class="bg-white shadow rounded-2xl p-4 sm:p-6 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="text-sm font-semibold text-gray-700 flex flex-wrap items-center gap-2">
                <span class="uppercase px-2 py-1 rounded text-xs {{ $question->category->name === 'past' ? 'bg-red-100 text-red-700' : ($question->category->name === 'present' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                    {{ ucfirst($question->category->name) }}
                </span>
                @if($question->source)
                    <span class="text-xs text-gray-500">Source: {{ $question->source->name }}</span>
                @endif
                @php
                    $questionSeeder = $question->seeder ?? null;
                    if ($questionSeeder) {
                        $questionSeeder = \Illuminate\Support\Str::after($questionSeeder, 'Database\\Seeders\\');
                    }
                @endphp
                @if($questionSeeder)
                    <span class="text-xs text-gray-500">Seeder: {{ $questionSeeder }}</span>
                @endif
                @if($question->flag)
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-yellow-200 text-yellow-800">AI</span>
                @endif
                <span class="text-xs text-gray-400">Складність: {{ $question->difficulty }}/10</span>
                <span class="text-xs text-gray-400">Level: {{ $question->level ?? 'N/A' }}</span>
            </div>
            <span class="text-xs text-gray-400">ID: {{ $question->id }} | UUID: {{ $question->uuid ?? '—' }}</span>
        </div>
        <div class="flex flex-wrap gap-2 items-baseline">
            <span class="question-number font-bold mr-2">{{ isset($number) ? $number . '.' : '?' }}</span>
            @php preg_match_all('/\{a(\d+)\}/', $question->question, $matches); @endphp
            @include('components.question-input', [
                'question' => $question,
                'inputNamePrefix' => "question_{$question->id}_",
                'manualInput' => $manualInput,
                'autocompleteInput' => $autocompleteInput,
                'builderInput' => $builderInput,
                'autocompleteRoute' => $autocompleteRoute,
            ])
        </div>
        @if($question->tags->count())
            <div class="flex flex-wrap gap-1">
                @php
                    $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800', 'bg-purple-200 text-purple-800', 'bg-pink-200 text-pink-800', 'bg-yellow-200 text-yellow-800', 'bg-indigo-200 text-indigo-800', 'bg-teal-200 text-teal-800'];
                @endphp
                @foreach($question->tags as $tag)
                    <a href="{{ route('saved-tests.cards', ['tag' => $tag->name]) }}" class="inline-flex px-2 py-0.5 rounded text-xs font-semibold hover:underline {{ $colors[$loop->index % count($colors)] }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif
        @if(!empty($checkOneInput))
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="mt-1 bg-purple-600 text-white text-xs rounded px-3 py-1 hover:bg-purple-700"
                    onclick="checkFullQuestionAjax(this, '{{ $question->id }}', '{{ implode(',', array_map(function($n){return 'a'.$n;}, $matches[1])) }}')"
                >
                    Check answer
                </button>
                <span class="text-xs font-bold" id="result-question-{{ $question->id }}"></span>
            </div>
        @endif
    </div>
</div>
