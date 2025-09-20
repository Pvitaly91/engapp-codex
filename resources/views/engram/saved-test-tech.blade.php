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
            $draft = $drafts->get($question->id) ?? [];
            $draftQuestion = $draft['question'] ?? [];
            $draftMeta = $draft['meta'] ?? [];
            $draftOptions = collect($draftQuestion['options'] ?? []);
            $draftAnswers = collect($draftQuestion['answers'] ?? []);
            $draftVerbHints = collect($draftQuestion['verb_hints'] ?? []);
            $draftVariants = collect($draftQuestion['variants'] ?? []);
            $draftHints = collect($draftQuestion['hints'] ?? []);
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

            @if($variantTexts->isNotEmpty())
                <details class="group">
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

            <details class="group">
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
                <details class="group">
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

            @if($questionHints->isNotEmpty())
                <details class="group">
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
                <details class="group">
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

            <section class="mt-6 border-t border-dashed border-stone-200 pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-stone-900">Редагування питання</h3>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-stone-500 sm:text-sm">
                        @if(($draftMeta['source'] ?? '') === 'draft')
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-amber-800">
                                Незастосовані зміни
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-stone-200 px-3 py-1 text-stone-700">
                                Дані з бази
                            </span>
                        @endif
                        @if(!empty($draftMeta['exported_at']))
                            <span class="font-medium text-stone-500">
                                Оновлено: {{ $draftMeta['exported_at'] }}
                            </span>
                        @endif
                    </div>
                </div>

                <div
                    class="mt-4 space-y-6 rounded-2xl border border-stone-200 bg-white/80 p-4 shadow-sm"
                    data-tech-editor
                    data-question-id="{{ $question->id }}"
                    data-question-uuid="{{ $question->uuid }}"
                    data-save-url="{{ route('saved-test.tech.question.draft.update', [$test->slug, $question]) }}"
                    data-apply-url="{{ route('saved-test.tech.question.apply', [$test->slug, $question]) }}"
                    data-dump-url="{{ route('saved-test.tech.question.dump', [$test->slug, $question]) }}"
                    data-draft-source="{{ $draftMeta['source'] ?? 'database' }}"
                >
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-stone-700">Текст питання</label>
                            <textarea
                                rows="3"
                                data-field="question"
                                class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ $draftQuestion['question'] ?? $question->question }}</textarea>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Складність</label>
                                <input
                                    type="text"
                                    data-field="difficulty"
                                    value="{{ $draftQuestion['difficulty'] ?? '' }}"
                                    class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Рівень</label>
                                <input
                                    type="text"
                                    data-field="level"
                                    value="{{ $draftQuestion['level'] ?? '' }}"
                                    class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Прапорець</label>
                                <input
                                    type="number"
                                    data-field="flag"
                                    value="{{ $draftQuestion['flag'] ?? '' }}"
                                    class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Категорія ID</label>
                                <input
                                    type="number"
                                    data-field="category_id"
                                    value="{{ $draftQuestion['category_id'] ?? '' }}"
                                    class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Джерело ID</label>
                                <input
                                    type="number"
                                    data-field="source_id"
                                    value="{{ $draftQuestion['source_id'] ?? '' }}"
                                    class="mt-1 w-full rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Варіанти відповіді</h4>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                data-action="add-option"
                            >
                                Додати варіант
                            </button>
                        </div>
                        <div class="space-y-2" data-collection="options" data-empty-text="Варіанти поки не додані.">
                            @forelse($draftOptions as $option)
                                <div
                                    class="flex flex-wrap items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 shadow-sm"
                                    data-role="option"
                                    data-id="{{ $option['id'] ?? '' }}"
                                >
                                    <input
                                        type="text"
                                        data-field="option-text"
                                        value="{{ $option['option'] ?? '' }}"
                                        class="min-w-[120px] flex-1 rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200"
                                        data-action="remove-option"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            @empty
                                <p class="text-sm text-stone-500" data-role="empty-message">Варіанти поки не додані.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Правильні відповіді</h4>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                data-action="add-answer"
                            >
                                Додати відповідь
                            </button>
                        </div>
                        <div class="space-y-2" data-collection="answers" data-empty-text="Немає відповідей.">
                            @forelse($draftAnswers as $answer)
                                <div
                                    class="grid gap-2 sm:grid-cols-[100px,1fr,auto]"
                                    data-role="answer"
                                    data-id="{{ $answer['id'] ?? '' }}"
                                >
                                    <input
                                        type="text"
                                        placeholder="A1"
                                        data-field="answer-marker"
                                        value="{{ $answer['marker'] ?? '' }}"
                                        class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                    <select
                                        data-field="answer-option"
                                        class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                        <option value=""></option>
                                        @foreach($draftOptions as $option)
                                            @php $optionText = $option['option'] ?? ''; @endphp
                                            <option value="{{ $optionText }}" @selected($optionText === ($answer['option'] ?? ''))>{{ $optionText }}</option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200"
                                        data-action="remove-answer"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            @empty
                                <p class="text-sm text-stone-500" data-role="empty-message">Немає відповідей.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Verb hints</h4>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                data-action="add-verb-hint"
                            >
                                Додати hint
                            </button>
                        </div>
                        <div class="space-y-2" data-collection="verb-hints" data-empty-text="Verb hints відсутні.">
                            @forelse($draftVerbHints as $hint)
                                <div
                                    class="grid gap-2 sm:grid-cols-[100px,1fr,auto]"
                                    data-role="verb-hint"
                                    data-id="{{ $hint['id'] ?? '' }}"
                                >
                                    <input
                                        type="text"
                                        placeholder="A1"
                                        data-field="verb-marker"
                                        value="{{ $hint['marker'] ?? '' }}"
                                        class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                    <select
                                        data-field="verb-option"
                                        class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                        <option value=""></option>
                                        @foreach($draftOptions as $option)
                                            @php $optionText = $option['option'] ?? ''; @endphp
                                            <option value="{{ $optionText }}" @selected($optionText === ($hint['option'] ?? ''))>{{ $optionText }}</option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200"
                                        data-action="remove-verb-hint"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            @empty
                                <p class="text-sm text-stone-500" data-role="empty-message">Verb hints відсутні.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Варіанти запитання</h4>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                data-action="add-variant"
                            >
                                Додати варіант
                            </button>
                        </div>
                        <div class="space-y-2" data-collection="variants" data-empty-text="Варіанти запитання відсутні.">
                            @forelse($draftVariants as $variant)
                                <div
                                    class="rounded-lg border border-stone-200 bg-white p-3 shadow-sm"
                                    data-role="variant"
                                    data-id="{{ $variant['id'] ?? '' }}"
                                >
                                    <textarea
                                        rows="2"
                                        data-field="variant-text"
                                        class="w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ $variant['text'] ?? '' }}</textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200"
                                            data-action="remove-variant"
                                        >
                                            Видалити
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-stone-500" data-role="empty-message">Варіанти запитання відсутні.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Підказки</h4>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                data-action="add-hint"
                            >
                                Додати підказку
                            </button>
                        </div>
                        <div class="space-y-3" data-collection="hints" data-empty-text="Підказки відсутні.">
                            @forelse($draftHints as $hint)
                                <div
                                    class="rounded-lg border border-stone-200 bg-white p-3 shadow-sm"
                                    data-role="hint"
                                    data-id="{{ $hint['id'] ?? '' }}"
                                >
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Провайдер</label>
                                            <input
                                                type="text"
                                                data-field="hint-provider"
                                                value="{{ $hint['provider'] ?? '' }}"
                                                class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                            >
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                                            <input
                                                type="text"
                                                data-field="hint-locale"
                                                value="{{ $hint['locale'] ?? '' }}"
                                                class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                            >
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Текст підказки</label>
                                        <textarea
                                            rows="2"
                                            data-field="hint-text"
                                            class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ $hint['hint'] ?? '' }}</textarea>
                                    </div>
                                    <div class="mt-2 flex justify-end">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200"
                                            data-action="remove-hint"
                                        >
                                            Видалити
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-stone-500" data-role="empty-message">Підказки відсутні.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-stone-200 pt-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                            data-action="apply"
                        >
                            Застосувати зміни
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            data-action="refresh-dump"
                        >
                            Оновити дамп
                        </button>
                        <span class="text-sm text-stone-500" data-role="status"></span>
                    </div>

                    <details class="group rounded-xl border border-stone-200 bg-white/90 p-4">
                        <summary class="flex cursor-pointer select-none items-center justify-between text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <span>JSON дамп</span>
                            <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                            <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                        </summary>
                        <pre class="mt-3 max-h-80 overflow-auto rounded-lg bg-stone-900/90 p-4 text-xs leading-relaxed text-emerald-100" data-role="dump-viewer">{{ json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>

                    <template data-template="option">
                        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 shadow-sm" data-role="option">
                            <input type="text" data-field="option-text" class="min-w-[120px] flex-1 rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <button type="button" class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200" data-action="remove-option">Видалити</button>
                        </div>
                    </template>
                    <template data-template="answer">
                        <div class="grid gap-2 sm:grid-cols-[100px,1fr,auto]" data-role="answer">
                            <input type="text" placeholder="A1" data-field="answer-marker" class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <select data-field="answer-option" class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"></select>
                            <button type="button" class="inline-flex items-center justify-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200" data-action="remove-answer">Видалити</button>
                        </div>
                    </template>
                    <template data-template="verb-hint">
                        <div class="grid gap-2 sm:grid-cols-[100px,1fr,auto]" data-role="verb-hint">
                            <input type="text" placeholder="A1" data-field="verb-marker" class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <select data-field="verb-option" class="rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"></select>
                            <button type="button" class="inline-flex items-center justify-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200" data-action="remove-verb-hint">Видалити</button>
                        </div>
                    </template>
                    <template data-template="variant">
                        <div class="rounded-lg border border-stone-200 bg-white p-3 shadow-sm" data-role="variant">
                            <textarea rows="2" data-field="variant-text" class="w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                            <div class="mt-2 flex justify-end">
                                <button type="button" class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200" data-action="remove-variant">Видалити</button>
                            </div>
                        </div>
                    </template>
                    <template data-template="hint">
                        <div class="rounded-lg border border-stone-200 bg-white p-3 shadow-sm" data-role="hint">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Провайдер</label>
                                    <input type="text" data-field="hint-provider" class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                                    <input type="text" data-field="hint-locale" class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Текст підказки</label>
                                <textarea rows="2" data-field="hint-text" class="mt-1 w-full rounded border border-stone-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"></textarea>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <button type="button" class="inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-600 hover:bg-stone-200" data-action="remove-hint">Видалити</button>
                            </div>
                        </div>
                    </template>
                </div>
            </section>
        </article>
    @endforeach
</div>

@push('scripts')
    <script src="{{ asset('js/tech-editor.js') }}" defer></script>
@endpush
@endsection
