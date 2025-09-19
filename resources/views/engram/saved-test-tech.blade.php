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

                    return [strtolower($answer->marker) => $value];
                });
            $highlightSegments = function (string $text) use ($answersByMarker) {
                $segments = preg_split('/(\{a\d+\})/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

                return collect($segments)->map(function ($segment) use ($answersByMarker) {
                    if (preg_match('/^\{(a\d+)\}$/i', $segment, $matches)) {
                        $markerKey = strtolower($matches[1]);

                        if (! $answersByMarker->has($markerKey)) {
                            return e($segment);
                        }

                        $value = $answersByMarker->get($markerKey);

                        return '<mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">' . e($value) . '</mark>';
                    }

                    return e($segment);
                })->implode('');
            };
            $filledQuestion = $highlightSegments($question->question);
            $options = $question->options->pluck('option')->filter()->unique()->values();
            foreach ($answersByMarker as $value) {
                if ($value !== '' && ! $options->contains($value)) {
                    $options->push($value);
                }
            }
            $variantTexts = $question->relationLoaded('variants')
                ? $question->variants->pluck('text')->filter()->unique()->values()
                : collect();
            $verbHints = $question->verbHints
                ->sortBy('marker')
                ->mapWithKeys(function ($hint) {
                    $value = $hint->option->option ?? '';

                    return $value !== '' ? [strtolower($hint->marker) => $value] : [];
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
                    <p class="mt-2 text-lg leading-relaxed text-stone-900">{!! $filledQuestion !!}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold">{{ $levelLabel }}</span>
                </div>
            </header>

            <details open class="group">
                <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <span>Правильні відповіді</span>
                    <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                    <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                </summary>
                <ul class="mt-3 space-y-2 text-sm text-stone-800">
                    @foreach($question->answers as $answer)
                        @php
                            $marker = strtoupper($answer->marker);
                            $markerKey = strtolower($answer->marker);
                            $answerValue = $answersByMarker->get($markerKey, '');
                            $verbHint = $verbHints->get($markerKey);
                        @endphp
                        <li class="flex flex-wrap items-center gap-2 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2">
                            <span class="font-mono text-xs uppercase text-emerald-500">{{ $marker }}</span>
                            <span class="font-semibold text-emerald-900">{{ $answerValue }}</span>
                            @if($verbHint)
                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                    <span class="font-semibold uppercase text-[10px] tracking-wide">Verb hint</span>
                                    <span>{{ $verbHint }}</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>

            @if($options->isNotEmpty())
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Варіанти відповіді</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($options as $option)
                            @php $isCorrectOption = $answersByMarker->contains(function ($value) use ($option) { return $value === $option; }); @endphp
                            <span @class([
                                'inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm',
                                'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm' => $isCorrectOption,
                                'border-stone-200 bg-stone-50 text-stone-800' => ! $isCorrectOption,
                            ])>
                                @if($isCorrectOption)
                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span>{{ $option }}</span>
                            </span>
                        @endforeach
                    </div>
                </details>
            @endif

            @if($variantTexts->isNotEmpty())
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Варіанти запитання</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <ul class="mt-3 space-y-2 text-sm text-stone-800">
                        @foreach($variantTexts as $variant)
                            <li class="flex flex-col gap-1 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                                <span class="font-mono text-[11px] uppercase text-stone-500">Варіант {{ $loop->iteration }}</span>
                                <span>{!! $highlightSegments($variant) !!}</span>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif

            @if($questionHints->isNotEmpty())
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Question hints</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <ul class="mt-3 space-y-3 text-sm text-stone-800">
                        @foreach($questionHints as $hint)
                            <li class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2">
                                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $hint->provider }} · {{ strtoupper($hint->locale) }}</div>
                                <div class="mt-1 whitespace-pre-line text-stone-800">{{ $hint->hint }}</div>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif

            @if($explanations->isNotEmpty())
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>ChatGPT explanations</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-stone-800">
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
                                    @php
                                        $isStoredCorrect = $answersByMarker->contains(function ($value) use ($explanation) {
                                            return $value === $explanation->correct_answer;
                                        });
                                    @endphp
                                    <tr class="border-t border-stone-200">
                                        <td class="py-2 pr-4 font-semibold text-stone-600">{{ strtoupper($explanation->language) }}</td>
                                        <td class="py-2 pr-4">{{ $explanation->wrong_answer ?: '—' }}</td>
                                        <td @class([
                                            'py-2 pr-4 font-semibold',
                                            'text-emerald-700' => $isStoredCorrect,
                                            'text-stone-800' => ! $isStoredCorrect,
                                        ])>
                                            {{ $explanation->correct_answer }}
                                        </td>
                                        <td class="py-2">{{ $explanation->explanation }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </article>
    @endforeach
</div>
@endsection
