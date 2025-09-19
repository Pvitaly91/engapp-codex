@extends('layouts.app')

@section('title', $test->name . ' — Технічна сторінка')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">{{ $test->name }}</h1>
            <p class="text-sm text-stone-600 mt-1">Технічна інформація про тест · Питань: {{ $questions->count() }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('saved-test.show', $test->slug) }}"
               class="px-3 py-1.5 rounded-2xl border border-stone-200 bg-white shadow-sm text-sm font-semibold text-stone-700 hover:bg-stone-50">
                ← Основна сторінка
            </a>
            <a href="{{ route('saved-test.js', $test->slug) }}"
               class="px-3 py-1.5 rounded-2xl border border-stone-200 bg-white shadow-sm text-sm font-semibold text-stone-700 hover:bg-stone-50">
                JS режим
            </a>
        </div>
    </div>

    @foreach($questions as $question)
        @php
            $answersByMarker = $question->answers
                ->mapWithKeys(function ($answer) {
                    $value = $answer->option->option ?? $answer->answer ?? '';

                    return [$answer->marker => $value];
                });
            $filledQuestion = $question->renderQuestionText();
            $options = $question->options->pluck('option')->filter()->unique()->values();
            foreach ($answersByMarker as $value) {
                if ($value !== '' && ! $options->contains($value)) {
                    $options->push($value);
                }
            }
            $variantTexts = $question->relationLoaded('variants')
                ? $question->variants->pluck('text')->filter()->unique()->values()
                : collect();
            $fillVariant = function (string $text) use ($answersByMarker) {
                return preg_replace_callback('/\{(a\d+)\}/', function ($matches) use ($answersByMarker) {
                    return $answersByMarker->get($matches[1], $matches[0]);
                }, $text);
            };
            $verbHints = $question->verbHints
                ->sortBy('marker')
                ->mapWithKeys(function ($hint) {
                    $value = $hint->option->option ?? '';

                    return $value !== '' ? [$hint->marker => $value] : [];
                });
            $questionHints = $question->hints
                ->sortBy(function ($hint) {
                    return $hint->provider . '|' . $hint->locale;
                })
                ->values();
            $explanations = collect($explanationsByQuestionId[$question->id] ?? []);
            $levelLabel = $question->level ?: 'N/A';
        @endphp
        <article class="bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100">
            <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex items-baseline gap-3 text-sm text-stone-500">
                        <span class="font-semibold uppercase tracking-wide">Питання {{ $loop->iteration }}</span>
                        <span>ID: {{ $question->id }}</span>
                    </div>
                    <p class="mt-2 text-lg leading-relaxed text-stone-900">{{ $filledQuestion }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold">{{ $levelLabel }}</span>
                </div>
            </header>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Правильні відповіді</h3>
                <ul class="mt-2 space-y-1 text-sm text-stone-800">
                    @foreach($question->answers as $answer)
                        @php $marker = strtoupper($answer->marker); @endphp
                        <li>
                            <span class="font-mono text-xs text-stone-500 mr-2">{{ $marker }}</span>
                            <span class="font-medium">{{ $answersByMarker->get($answer->marker) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($options->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Варіанти відповіді</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($options as $option)
                            <span class="inline-flex items-center px-3 py-1 rounded-full border border-stone-200 bg-stone-50 text-sm text-stone-800">{{ $option }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($variantTexts->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Варіанти запитання</h3>
                    <ul class="mt-2 space-y-1 text-sm text-stone-800">
                        @foreach($variantTexts as $variant)
                            <li>
                                <span class="font-mono text-xs text-stone-500 mr-2">Варіант {{ $loop->iteration }}</span>
                                <span>{{ $fillVariant($variant) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($verbHints->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Verb hints</h3>
                    <ul class="mt-2 space-y-1 text-sm text-stone-800">
                        @foreach($verbHints as $marker => $hint)
                            <li>
                                <span class="font-mono text-xs text-stone-500 mr-2">{{ strtoupper($marker) }}</span>
                                <span>{{ $hint }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($questionHints->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Question hints</h3>
                    <ul class="mt-2 space-y-2 text-sm text-stone-800">
                        @foreach($questionHints as $hint)
                            <li>
                                <div class="font-semibold text-stone-600">{{ $hint->provider }} ({{ $hint->locale }})</div>
                                <div class="mt-1 whitespace-pre-line">{{ $hint->hint }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($explanations->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-500">ChatGPT explanations</h3>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm text-left text-stone-800">
                            <thead class="text-xs uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="py-2 pr-4">Мова</th>
                                    <th class="py-2 pr-4">Неправильна відповідь</th>
                                    <th class="py-2 pr-4">Правильна відповідь</th>
                                    <th class="py-2">Пояснення</th>
                                </tr>
                            </thead>
                            <tbody class="align-top">
                                @foreach($explanations as $explanation)
                                    <tr class="border-t border-stone-200">
                                        <td class="py-2 pr-4 font-semibold text-stone-600">{{ strtoupper($explanation->language) }}</td>
                                        <td class="py-2 pr-4">{{ $explanation->wrong_answer ?: '—' }}</td>
                                        <td class="py-2 pr-4 font-medium">{{ $explanation->correct_answer }}</td>
                                        <td class="py-2">{{ $explanation->explanation }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </article>
    @endforeach
</div>
@endsection
