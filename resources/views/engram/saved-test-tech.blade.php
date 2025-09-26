@extends('layouts.app')

@section('title', $test->name . ' — Технічна сторінка')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">{{ $test->name }}</h1>
            <p class="text-sm text-stone-600 mt-1">
                Технічна інформація про тест · Питань:
                <span class="font-semibold text-stone-700" data-questions-count>{{ $questions->count() }}</span>
            </p>
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
            <button type="button"
                    class="px-3 py-1.5 rounded-2xl border border-blue-600 bg-blue-600 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                    onclick="techEditor.createQuestion()">
                + Нове питання
            </button>
        </div>
    </div>

    @php
        $cefrLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        $availableHintProviders = collect($hintProviders ?? [])
            ->filter(fn ($provider) => is_string($provider) && trim($provider) !== '')
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="space-y-5" data-questions-container>
        @forelse($questions as $question)
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
            $correctOptionIds = $question->answers
                ->pluck('option_id')
                ->filter()
                ->unique()
                ->values();

            $options = $question->options
                ->map(function ($option) use ($correctOptionIds) {
                    return [
                        'id' => $option->id,
                        'label' => $option->option,
                        'is_correct' => $correctOptionIds->contains($option->id),
                    ];
                });
            foreach ($question->answers as $answer) {
                $option = $answer->option;

                if (! $option) {
                    continue;
                }

                if ($options->contains(fn ($item) => $item['id'] === $option->id)) {
                    continue;
                }

                $options->push([
                    'id' => $option->id,
                    'label' => $option->option,
                    'is_correct' => true,
                ]);
            }
            $options = $options
                ->filter(fn ($item) => filled($item['label']))
                ->unique('id')
                ->values();
            $hasAnswerOptions = $options->isNotEmpty();
            $hasCorrectAnswerOption = $options->contains(fn ($item) => $item['is_correct']);
            $canAddChatGptExplanation = $hasAnswerOptions && $hasCorrectAnswerOption;
            $variants = $question->relationLoaded('variants')
                ? $question->variants->filter(function ($variant) {
                    return is_string($variant->text) && trim($variant->text) !== '';
                })
                : collect();
            $verbHintsByMarker = $question->verbHints
                ->sortBy('marker')
                ->keyBy(fn ($hint) => strtolower($hint->marker));
            $questionHints = $question->hints
                ->sortBy(function ($hint) {
                    return $hint->provider . '|' . $hint->locale;
                })
                ->values();
            $explanations = collect($explanationsByQuestionId[$question->id] ?? []);

            $answersData = $question->answers
                ->map(function ($answer) use ($answersByMarker, $verbHintsByMarker) {
                    $markerKey = strtolower($answer->marker);
                    $option = $answer->relationLoaded('option') ? $answer->option : $answer->option()->first();
                    $verbHint = $verbHintsByMarker->get($markerKey);

                    return [
                        'id' => $answer->id,
                        'marker' => strtoupper($answer->marker),
                        'marker_key' => $markerKey,
                        'value' => $answersByMarker->get($markerKey, ''),
                        'option' => $option ? [
                            'id' => $option->id,
                            'label' => $option->option,
                        ] : null,
                        'verb_hint' => $verbHint ? [
                            'id' => $verbHint->id,
                            'value' => optional($verbHint->option)->option,
                        ] : null,
                    ];
                })
                ->values();

            $markerMatches = [];
            $markers = collect();

            if (is_string($question->question) && preg_match_all('/\{(a\d+)\}/i', $question->question, $markerMatches)) {
                $markers = collect($markerMatches[1])
                    ->map(fn ($marker) => strtolower($marker))
                    ->unique()
                    ->values();
            }

            $markers = $markers
                ->merge($answersData->pluck('marker_key'))
                ->unique()
                ->values();

            $explanationsData = $explanations
                ->sortBy(function ($explanation) {
                    return sprintf(
                        '%s|%s|%s',
                        strtolower($explanation->language ?? ''),
                        $explanation->wrong_answer ?? '',
                        $explanation->correct_answer ?? ''
                    );
                })
                ->map(function ($explanation) {
                    return [
                        'id' => $explanation->id,
                        'language' => $explanation->language,
                        'wrong_answer' => $explanation->wrong_answer,
                        'correct_answer' => $explanation->correct_answer,
                        'explanation' => $explanation->explanation,
                    ];
                })
                ->values();
            $questionLevel = in_array($question->level, $cefrLevels, true) ? $question->level : null;
            $levelLabel = $questionLevel ?? 'N/A';

            $variantsData = $variants
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'text' => $variant->text,
                    ];
                })
                ->values();

            $questionHintsData = $questionHints
                ->map(function ($hint) {
                    return [
                        'id' => $hint->id,
                        'provider' => $hint->provider,
                        'locale' => $hint->locale,
                        'hint' => $hint->hint,
                    ];
                })
                ->values();

            $techQuestionData = [
                'id' => $question->id,
                'question' => $question->question,
                'level' => $questionLevel,
                'answers' => $answersData->toArray(),
                'answers_by_marker' => $answersByMarker->toArray(),
                'variants' => $variantsData->toArray(),
                'options' => $options->toArray(),
                'question_hints' => $questionHintsData->toArray(),
                'markers' => $markers->toArray(),
                'chatgpt_explanations' => $explanationsData->toArray(),
                'can_add_chatgpt_explanation' => $canAddChatGptExplanation,
            ];
        @endphp
        <article class="bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100"
                 data-question-id="{{ $question->id }}"
                 data-question='@json($techQuestionData)'>
            <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex items-baseline gap-3 text-sm text-stone-500">
                        <span class="font-semibold uppercase tracking-wide">Питання <span data-question-number>{{ $loop->iteration }}</span></span>
                        <span>ID: {{ $question->id }}</span>
                    </div>
                    <p class="mt-2 text-lg leading-relaxed text-stone-900" data-question-text>{!! $filledQuestion !!}</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                        <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-blue-700 hover:bg-blue-100"
                                onclick="techEditor.editQuestion({{ $question->id }})">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M15.728 2.272a2.625 2.625 0 0 1 0 3.712l-8.1 8.1a3.5 3.5 0 0 1-1.563.888l-2.82.705a.625.625 0 0 1-.757-.757l.706-2.82a3.5 3.5 0 0 1 .888-1.564l8.1-8.1a2.625 2.625 0 0 1 3.712 0Zm-2.65 1.062-8.1 8.1a2.25 2.25 0 0 0-.57 1.006l-.46 1.838 1.838-.46a2.25 2.25 0 0 0 1.006-.57l8.1-8.1a1.375 1.375 0 1 0-1.94-1.94Z" />
                            </svg>
                            <span>Редагувати питання</span>
                        </button>
                        <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-red-700 hover:bg-red-100"
                                onclick="techEditor.deleteQuestion({{ $question->id }})">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.5 3a1.5 1.5 0 0 1 1.5-1.5h2a1.5 1.5 0 0 1 1.5 1.5H15a.75.75 0 0 1 0 1.5h-.556l-.67 10.057A2.25 2.25 0 0 1 11.53 16.75H8.47a2.25 2.25 0 0 1-2.244-2.193L5.556 4.5H5a.75.75 0 0 1 0-1.5h2.5Zm3.75 3a.75.75 0 0 1 1.5 0l-.375 7.5a.75.75 0 0 1-1.5 0l.375-7.5Zm-3 0a.75.75 0 1 0-1.5 0l.375 7.5a.75.75 0 0 0 1.5 0l-.375-7.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>Видалити питання</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold" data-question-level>{{ $levelLabel }}</span>
                    <button type="button"
                            class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                            onclick="techEditor.editQuestionLevel({{ $question->id }})">
                        Змінити
                    </button>
                </div>
            </header>

            <div data-variants-section>
                @if($variants->isNotEmpty())
                    <details class="group">
                        <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <span>Варіанти запитання</span>
                            <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                            <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                        </summary>
                        <ul class="mt-3 space-y-2 text-sm text-stone-800">
                            @foreach($variants as $variant)
                                <li class="flex flex-col gap-1 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2"
                                    data-variant-id="{{ $variant->id }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-[11px] uppercase text-stone-500">Варіант {{ $loop->iteration }}</span>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                                    onclick="techEditor.editVariant({{ $question->id }}, {{ $variant->id }})">Редагувати</button>
                                            <button type="button" class="text-[11px] font-semibold text-red-600 underline hover:text-red-700"
                                                    onclick="techEditor.deleteVariant({{ $question->id }}, {{ $variant->id }})">Видалити</button>
                                        </div>
                                    </div>
                                    <span data-variant-text>{!! $highlightSegments($variant->text) !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>

            <details class="group">
                <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <span>Правильні відповіді</span>
                    <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                    <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                </summary>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addAnswer({{ $question->id }})">
                        <span>Додати відповідь</span>
                    </button>
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                            onclick="techEditor.addVerbHint({{ $question->id }})">
                        <span>Додати verb hint</span>
                    </button>
                </div>
                <ul class="mt-3 space-y-2 text-sm text-stone-800" data-answers-container>
                    @forelse($question->answers as $answer)
                        @php
                            $marker = strtoupper($answer->marker);
                            $markerKey = strtolower($answer->marker);
                            $answerValue = $answersByMarker->get($markerKey, '');
                            $verbHintModel = $verbHintsByMarker->get($markerKey);
                            $verbHintValue = $verbHintModel?->option?->option;
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2"
                            data-answer-id="{{ $answer->id }}">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="font-mono text-xs uppercase text-emerald-500">{{ $marker }}</span>
                                <span class="font-semibold text-emerald-900" data-answer-value>{{ $answerValue }}</span>
                                @if($verbHintValue)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-emerald-700"
                                        data-verb-hint @if($verbHintModel) data-verb-hint-id="{{ $verbHintModel->id }}" @endif>
                                        <span class="font-semibold uppercase text-[10px] tracking-wide">Verb hint</span>
                                        <span data-verb-hint-value>{{ $verbHintValue }}</span>
                                        @if($verbHintModel)
                                            <button type="button" class="text-[10px] font-semibold text-blue-600 underline hover:text-blue-800"
                                                    onclick="techEditor.editVerbHint({{ $question->id }}, {{ $verbHintModel->id }})">Редагувати</button>
                                            <button type="button" class="text-[10px] font-semibold text-red-600 underline hover:text-red-700"
                                                    onclick="techEditor.deleteVerbHint({{ $question->id }}, {{ $verbHintModel->id }})">Видалити</button>
                                        @endif
                                    </span>
                                @else
                                    <button type="button"
                                            class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                            onclick="techEditor.addVerbHint({{ $question->id }}, '{{ $markerKey }}')">
                                        Додати verb hint
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                                        onclick="techEditor.editAnswer({{ $question->id }}, {{ $answer->id }})">Редагувати відповідь</button>
                                <button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700"
                                        onclick="techEditor.deleteAnswer({{ $question->id }}, {{ $answer->id }})">Видалити</button>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-stone-200 bg-stone-50 px-3 py-4 text-sm text-stone-500">
                            Відповіді ще не додані.
                        </li>
                    @endforelse
                </ul>
            </details>

            <details class="group">
                <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <span>Варіанти відповіді</span>
                    <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                    <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                </summary>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addOption({{ $question->id }})">
                        <span>Додати варіант</span>
                    </button>
                </div>
                <div class="mt-3 flex flex-wrap gap-2" data-options-container>
                    @forelse($options as $option)
                        @php $isCorrectOption = (bool) ($option['is_correct'] ?? false); @endphp
                        <div @class([
                            'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm',
                            'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm' => $isCorrectOption,
                            'border-stone-200 bg-stone-50 text-stone-800' => ! $isCorrectOption,
                        ])
                        data-option-id="{{ $option['id'] ?? '' }}">
                            @if($isCorrectOption)
                                <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            <span>{{ $option['label'] }}</span>
                            @if(! empty($option['id']))
                                <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                                        onclick="techEditor.editOption({{ $question->id }}, {{ $option['id'] }})">Редагувати</button>
                                <button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700"
                                        onclick="techEditor.deleteOption({{ $question->id }}, {{ $option['id'] }})">Видалити</button>
                            @endif
                        </div>
                    @empty
                        <p class="rounded-full border border-dashed border-stone-200 bg-stone-50 px-3 py-1 text-xs font-semibold text-stone-500">
                            Варіанти ще не додані.
                        </p>
                    @endforelse
                </div>
            </details>

            <details class="group">
                <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <span>Question hints</span>
                    <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                    <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                </summary>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addQuestionHint({{ $question->id }})">
                        <span>Додати підказку</span>
                    </button>
                </div>
                <ul class="mt-3 space-y-3 text-sm text-stone-800" data-question-hints-container>
                    @forelse($questionHints as $hint)
                        <li class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2"
                            data-question-hint-id="{{ $hint->id }}">
                            <div class="flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                                <span>{{ $hint->provider }} · {{ strtoupper($hint->locale) }}</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                            onclick="techEditor.editQuestionHint({{ $question->id }}, {{ $hint->id }})">Редагувати</button>
                                    <button type="button" class="text-[11px] font-semibold text-red-600 underline hover:text-red-700"
                                            onclick="techEditor.deleteQuestionHint({{ $question->id }}, {{ $hint->id }})">Видалити</button>
                                </div>
                            </div>
                            <div class="mt-1 whitespace-pre-line text-stone-800" data-question-hint-text>{{ $hint->hint }}</div>
                        </li>
                    @empty
                        <li class="rounded-lg border border-dashed border-blue-200 bg-blue-50/60 px-3 py-4 text-sm text-blue-600">
                            Підказок ще немає.
                        </li>
                    @endforelse
                </ul>
            </details>

            <details class="group">
                <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <span>ChatGPT explanations</span>
                    <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                    <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                </summary>
                    <div class="mt-3 flex flex-col gap-2">
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100 @unless($canAddChatGptExplanation) opacity-60 cursor-not-allowed @endunless"
                                data-add-chatgpt-explanation
                                data-question-id="{{ $question->id }}"
                                @unless($canAddChatGptExplanation) disabled @endunless
                                onclick="techEditor.addChatGptExplanation({{ $question->id }})">
                                <span>Додати пояснення</span>
                            </button>
                        </div>
                        <p class="text-xs text-stone-500 @if($canAddChatGptExplanation) hidden @endif" data-chatgpt-explanation-hint>
                            Додайте варіанти відповіді та позначте правильні, щоб створити пояснення.
                        </p>
                    </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-stone-800">
                        <thead class="text-xs uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="py-2 pr-4">Мова</th>
                                <th class="py-2 pr-4">Неправильна відповідь</th>
                                <th class="py-2 pr-4">Правильна відповідь</th>
                                <th class="py-2">Пояснення</th>
                                <th class="py-2 text-right">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="align-top" data-explanations-container>
                            @forelse($explanations as $explanation)
                                @php
                                    $isStoredCorrect = $answersByMarker->contains(function ($value) use ($explanation) {
                                        return $value === $explanation->correct_answer;
                                    });
                                @endphp
                                <tr class="border-t border-stone-200" data-explanation-id="{{ $explanation->id }}">
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
                                    <td class="py-2 text-right">
                                        <button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700"
                                                onclick="techEditor.deleteChatGptExplanation({{ $question->id }}, {{ $explanation->id }})">Видалити</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-3 text-center text-sm font-semibold text-stone-500">Пояснення ще не додані.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </details>
        </article>
        @empty
            <div class="rounded-2xl border border-dashed border-stone-200 bg-white/60 p-6 text-center text-sm text-stone-500" data-empty-state>
                Ще немає жодного питання у цьому тесті.
            </div>
        @endforelse
    </div>
</div>
<div id="tech-editor-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-900/60 px-4 py-6">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <h2 class="text-lg font-semibold text-stone-900" data-modal-title></h2>
            <button type="button"
                    class="rounded-full p-2 text-stone-500 transition hover:bg-stone-100 hover:text-stone-800"
                    data-modal-close>
                <span class="sr-only">Закрити</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z"
                          clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <form class="mt-4 space-y-5" data-modal-form>
            <div class="space-y-4" data-modal-fields></div>
            <p class="text-sm text-red-600 hidden" data-modal-error></p>
            <div class="flex justify-end gap-3">
                <button type="button"
                        class="rounded-lg border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                        data-modal-cancel>Скасувати</button>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        data-modal-submit>Зберегти</button>
            </div>
        </form>
    </div>
</div>
<script>
    (() => {
        const techCsrfToken = '{{ csrf_token() }}';
        const routes = {
            createQuestion: '{{ route('saved-test.questions.store', $test->slug) }}',
            question: '{{ url('/questions') }}',
            answer: '{{ url('/question-answers') }}',
            variant: '{{ url('/question-variants') }}',
            option: '{{ url('/questions') }}',
            questionHint: '{{ url('/question-hints') }}',
            verbHint: '{{ url('/verb-hints') }}',
            chatgptExplanation: '{{ url('/chatgpt-explanations') }}',
            deleteQuestion: '{{ url('/test/' . $test->slug . '/question') }}',
        };

        const cefrLevels = @json($cefrLevels);
        const initialHintProviders = @json($availableHintProviders->all());

        const hintProviderSet = new Set(
            Array.isArray(initialHintProviders)
                ? initialHintProviders
                    .filter(provider => typeof provider === 'string' && provider.trim() !== '')
                    .map(provider => provider.trim())
                : []
        );

        function normaliseProvider(value) {
            if (typeof value !== 'string') {
                return '';
            }

            return value.trim();
        }

        function getSortedHintProviders() {
            return Array.from(hintProviderSet).sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
        }

        function getHintProviderOptions(selectedValue = '') {
            const providers = getSortedHintProviders();
            const normalisedSelected = normaliseProvider(selectedValue);

            if (normalisedSelected && !providers.includes(normalisedSelected)) {
                providers.push(normalisedSelected);
                providers.sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }));
            }

            if (!providers.length) {
                return [];
            }

            const options = [
                { value: '', label: 'Оберіть постачальника' },
            ];

            providers.forEach(provider => {
                options.push({ value: provider, label: provider });
            });

            return options;
        }

        function buildHintProviderField(defaultValue = '') {
            const options = getHintProviderOptions(defaultValue);
            const normalised = normaliseProvider(defaultValue);

            if (options.length) {
                return {
                    name: 'provider',
                    label: 'Постачальник',
                    type: 'select',
                    required: true,
                    value: normalised,
                    options,
                };
            }

            return {
                name: 'provider',
                label: 'Постачальник',
                type: 'text',
                required: true,
                value: normalised,
            };
        }

        function syncHintProviders(questionData) {
            if (!questionData) {
                return;
            }

            const hints = Array.isArray(questionData.question_hints) ? questionData.question_hints : [];

            hints.forEach(hint => {
                const provider = normaliseProvider(hint.provider);

                if (provider && !hintProviderSet.has(provider)) {
                    hintProviderSet.add(provider);
                }
            });
        }

        const state = new Map();

        const modalElement = document.getElementById('tech-editor-modal');
        const modal = {
            element: modalElement,
            form: modalElement ? modalElement.querySelector('[data-modal-form]') : null,
            fields: modalElement ? modalElement.querySelector('[data-modal-fields]') : null,
            title: modalElement ? modalElement.querySelector('[data-modal-title]') : null,
            error: modalElement ? modalElement.querySelector('[data-modal-error]') : null,
            submitButton: modalElement ? modalElement.querySelector('[data-modal-submit]') : null,
            cancelButton: modalElement ? modalElement.querySelector('[data-modal-cancel]') : null,
            closeButton: modalElement ? modalElement.querySelector('[data-modal-close]') : null,
        };

        let submitHandler = null;

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function highlightSegments(text, answers) {
            if (!text) {
                return '';
            }

            const segments = text.split(/(\{a\d+\})/gi);

            return segments
                .map(segment => {
                    const match = segment.match(/^\{(a\d+)\}$/i);
                    if (match) {
                        const key = match[1].toLowerCase();
                        const value = answers[key];

                        if (!value) {
                            return escapeHtml(segment);
                        }

                        return `<mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">${escapeHtml(value)}</mark>`;
                    }

                    return escapeHtml(segment);
                })
                .join('');
        }

        function renderOptionHtml(questionId, option) {
            const classes = ['inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm'];

            if (option.is_correct) {
                classes.push('border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm');
            } else {
                classes.push('border-stone-200 bg-stone-50 text-stone-800');
            }

            const parts = [`<div class="${classes.join(' ')}" data-option-id="${option.id ?? ''}">`];

            if (option.is_correct) {
                parts.push('<svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd"></path></svg>');
            }

            parts.push(`<span>${escapeHtml(option.label ?? '')}</span>`);

            if (option.id) {
                parts.push(`<button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800" onclick="techEditor.editOption(${questionId}, ${option.id})">Редагувати</button>`);
                parts.push(`<button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700" onclick="techEditor.deleteOption(${questionId}, ${option.id})">Видалити</button>`);
            }

            parts.push('</div>');

            return parts.join('');
        }

        function renderAnswersHtml(question) {
            const answers = Array.isArray(question.answers) ? question.answers : [];

            if (!answers.length) {
                return '<li class="rounded-lg border border-dashed border-stone-200 bg-stone-50 px-3 py-4 text-sm text-stone-500">Відповіді ще не додані.</li>';
            }

        return answers.map(answer => {
            const marker = escapeHtml(answer.marker ?? '');
            const value = escapeHtml(answer.value ?? '');
            const markerKey = answer.marker_key ?? '';
            const verbHint = answer.verb_hint || null;
            let verbHintHtml = '';

            if (verbHint && verbHint.id) {
                const hintValue = escapeHtml(verbHint.value ?? '');
                verbHintHtml = `
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-emerald-700"
                        data-verb-hint data-verb-hint-id="${verbHint.id}">
                        <span class="font-semibold uppercase text-[10px] tracking-wide">Verb hint</span>
                        <span data-verb-hint-value>${hintValue}</span>
                        <button type="button" class="text-[10px] font-semibold text-blue-600 underline hover:text-blue-800"
                            onclick="techEditor.editVerbHint(${question.id}, ${verbHint.id})">Редагувати</button>
                        <button type="button" class="text-[10px] font-semibold text-red-600 underline hover:text-red-700"
                            onclick="techEditor.deleteVerbHint(${question.id}, ${verbHint.id})">Видалити</button>
                    </span>
                `;
            } else {
                verbHintHtml = `
                    <button type="button"
                            class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                            onclick="techEditor.addVerbHint(${question.id}, '${markerKey}')">
                            Додати verb hint
                        </button>
                    `;
                }

                return `
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2"
                        data-answer-id="${answer.id}">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-mono text-xs uppercase text-emerald-500">${marker}</span>
                            <span class="font-semibold text-emerald-900" data-answer-value>${value}</span>
                            ${verbHintHtml}
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                                onclick="techEditor.editAnswer(${question.id}, ${answer.id})">Редагувати відповідь</button>
                            <button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700"
                                onclick="techEditor.deleteAnswer(${question.id}, ${answer.id})">Видалити</button>
                        </div>
                    </li>
                `;
            }).join('');
        }

        function renderOptionsHtml(question) {
            const options = Array.isArray(question.options) ? question.options : [];

            if (!options.length) {
                return '<p class="rounded-full border border-dashed border-stone-200 bg-stone-50 px-3 py-1 text-xs font-semibold text-stone-500">Варіанти ще не додані.</p>';
            }

            return options.map(option => renderOptionHtml(question.id, option)).join('');
        }

        function renderQuestionHintsHtml(question) {
            const hints = Array.isArray(question.question_hints) ? question.question_hints : [];

            if (!hints.length) {
                return '<li class="rounded-lg border border-dashed border-blue-200 bg-blue-50/60 px-3 py-4 text-sm text-blue-600">Підказок ще немає.</li>';
            }

            return hints.map(hint => {
                const provider = escapeHtml(normaliseProvider(hint.provider) || '');
                const locale = escapeHtml(String(hint.locale ?? '').toUpperCase());
                const hintText = escapeHtml(hint.hint ?? '');

                return `
                    <li class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2" data-question-hint-id="${hint.id}">
                        <div class="flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                            <span>${provider} · ${locale}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                    onclick="techEditor.editQuestionHint(${question.id}, ${hint.id})">Редагувати</button>
                                <button type="button" class="text-[11px] font-semibold text-red-600 underline hover:text-red-700"
                                    onclick="techEditor.deleteQuestionHint(${question.id}, ${hint.id})">Видалити</button>
                            </div>
                        </div>
                        <div class="mt-1 whitespace-pre-line text-stone-800" data-question-hint-text>${hintText}</div>
                    </li>
                `;
            }).join('');
        }

        function renderChatgptExplanationsHtml(question) {
            const explanations = Array.isArray(question.chatgpt_explanations) ? question.chatgpt_explanations : [];
            const answersMap = question.answers_by_marker || {};

            if (!explanations.length) {
                return '<tr><td colspan="5" class="py-3 text-center text-sm font-semibold text-stone-500">Пояснення ще не додані.</td></tr>';
            }

            return explanations.map(explanation => {
                const language = escapeHtml(String(explanation.language ?? '').toUpperCase());
                const wrongAnswer = escapeHtml(explanation.wrong_answer ?? '—');
                const correctAnswer = escapeHtml(explanation.correct_answer ?? '');
                const explanationText = escapeHtml(explanation.explanation ?? '');
                const isStoredCorrect = Object.values(answersMap).some(value => value === explanation.correct_answer);
                const correctClass = isStoredCorrect ? 'text-emerald-700' : 'text-stone-800';

                return `
                    <tr class="border-t border-stone-200" data-explanation-id="${explanation.id}">
                        <td class="py-2 pr-4 font-semibold text-stone-600">${language}</td>
                        <td class="py-2 pr-4">${wrongAnswer || '—'}</td>
                        <td class="py-2 pr-4 font-semibold ${correctClass}">${correctAnswer}</td>
                        <td class="py-2">${explanationText}</td>
                        <td class="py-2 text-right">
                            <button type="button" class="text-xs font-semibold text-red-600 underline hover:text-red-700"
                                onclick="techEditor.deleteChatGptExplanation(${question.id}, ${explanation.id})">Видалити</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function getAnswerChoices(question) {
            const options = Array.isArray(question.options) ? question.options : [];
            const seen = new Set();

            return options.reduce((accumulator, option) => {
                if (!option || typeof option.label !== 'string') {
                    return accumulator;
                }

                const label = option.label.trim();

                if (!label) {
                    return accumulator;
                }

                const key = option.id !== undefined && option.id !== null
                    ? `id:${option.id}`
                    : `label:${label.toLowerCase()}`;

                if (seen.has(key)) {
                    return accumulator;
                }

                seen.add(key);
                accumulator.push({
                    value: label,
                    label,
                    isCorrect: !!option.is_correct,
                });

                return accumulator;
            }, []);
        }

        function getCorrectAnswerChoices(question) {
            return getAnswerChoices(question).filter(choice => choice.isCorrect);
        }

        function getIncorrectAnswerChoices(question) {
            return getAnswerChoices(question).filter(choice => !choice.isCorrect);
        }

        function canAddChatGptExplanation(question) {
            const choices = getAnswerChoices(question);

            if (!choices.length) {
                return false;
            }

            return choices.some(choice => choice.isCorrect);
        }

        function updateChatgptExplanationAvailability(entry) {
            if (!entry || !entry.element) {
                return;
            }

            const button = entry.element.querySelector('[data-add-chatgpt-explanation]');
            const hint = entry.element.querySelector('[data-chatgpt-explanation-hint]');
            const canAdd = canAddChatGptExplanation(entry.data || {});

            if (button) {
                button.disabled = !canAdd;
                button.classList.toggle('opacity-60', !canAdd);
                button.classList.toggle('cursor-not-allowed', !canAdd);
            }

            if (hint) {
                hint.classList.toggle('hidden', canAdd);
            }
        }

        function renderVariantsSection(question) {
            const variants = Array.isArray(question.variants)
                ? question.variants.filter(variant => typeof variant.text === 'string' && variant.text.trim() !== '')
                : [];

            if (!variants.length) {
                return '';
            }

            const answersMap = question.answers_by_marker || {};

            const itemsHtml = variants.map((variant, index) => {
                const variantText = highlightSegments(variant.text ?? '', answersMap);
                const iteration = index + 1;

                return `
                    <li class="flex flex-col gap-1 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2"
                        data-variant-id="${variant.id}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[11px] uppercase text-stone-500">Варіант ${iteration}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                    onclick="techEditor.editVariant(${question.id}, ${variant.id})">Редагувати</button>
                                <button type="button" class="text-[11px] font-semibold text-red-600 underline hover:text-red-700"
                                    onclick="techEditor.deleteVariant(${question.id}, ${variant.id})">Видалити</button>
                            </div>
                        </div>
                        <span data-variant-text>${variantText}</span>
                    </li>
                `;
            }).join('');

            return `
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Варіанти запитання</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <ul class="mt-3 space-y-2 text-sm text-stone-800">
                        ${itemsHtml}
                    </ul>
                </details>
            `;
        }

        function getQuestionsContainer() {
            return document.querySelector('[data-questions-container]');
        }

        function getQuestionCountElement() {
            return document.querySelector('[data-questions-count]');
        }

        function removeEmptyState() {
            const emptyState = document.querySelector('[data-empty-state]');
            if (emptyState) {
                emptyState.remove();
            }
        }

        function showEmptyState() {
            const container = getQuestionsContainer();

            if (!container) {
                return;
            }

            if (container.querySelector('[data-question-id]') || container.querySelector('[data-empty-state]')) {
                return;
            }

            const placeholder = document.createElement('div');
            placeholder.className = 'rounded-2xl border border-dashed border-stone-200 bg-white/60 p-6 text-center text-sm text-stone-500';
            placeholder.setAttribute('data-empty-state', '');
            placeholder.textContent = 'Ще немає жодного питання у цьому тесті.';
            container.appendChild(placeholder);
        }

        function updateQuestionCount() {
            const countElement = getQuestionCountElement();
            const container = getQuestionsContainer();

            if (!countElement) {
                return;
            }

            const questionCount = container
                ? container.querySelectorAll('[data-question-id]').length
                : 0;

            countElement.textContent = questionCount;
        }

        function refreshQuestionNumbers() {
            const container = getQuestionsContainer();

            if (!container) {
                return;
            }

            const items = container.querySelectorAll('[data-question-id]');

            items.forEach((element, index) => {
                const numberElement = element.querySelector('[data-question-number]');

                if (numberElement) {
                    numberElement.textContent = index + 1;
                }
            });
        }

        function createQuestionElement(question) {
            const container = getQuestionsContainer();

            if (!container) {
                return null;
            }

            removeEmptyState();

            const answersMap = question.answers_by_marker || {};
            const questionText = highlightSegments(question.question ?? '', answersMap);
            const levelValue = typeof question.level === 'string' && question.level.trim() !== '' ? question.level : null;
            const levelLabel = levelValue || 'N/A';
            const questionIndex = container.querySelectorAll('[data-question-id]').length + 1;
            const variantsHtml = renderVariantsSection(question);
            const answersHtml = renderAnswersHtml(question);
            const optionsHtml = renderOptionsHtml(question);
            const hintsHtml = renderQuestionHintsHtml(question);
            const explanationsHtml = renderChatgptExplanationsHtml(question);
            const canAddExplanation = canAddChatGptExplanation(question);
            const addExplanationButtonDisabledAttr = canAddExplanation ? '' : ' disabled';
            const addExplanationButtonExtraClasses = canAddExplanation ? '' : ' opacity-60 cursor-not-allowed';
            const addExplanationHintClass = canAddExplanation ? 'hidden ' : '';

            const article = document.createElement('article');
            article.className = 'bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100';
            article.setAttribute('data-question-id', question.id);
            article.setAttribute('data-question', JSON.stringify(question));

            article.innerHTML = `
                <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex items-baseline gap-3 text-sm text-stone-500">
                            <span class="font-semibold uppercase tracking-wide">Питання <span data-question-number>${questionIndex}</span></span>
                            <span>ID: ${question.id}</span>
                        </div>
                        <p class="mt-2 text-lg leading-relaxed text-stone-900" data-question-text>${questionText}</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                            <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-blue-700 hover:bg-blue-100"
                                onclick="techEditor.editQuestion(${question.id})">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M15.728 2.272a2.625 2.625 0 0 1 0 3.712l-8.1 8.1a3.5 3.5 0 0 1-1.563.888l-2.82.705a.625.625 0 0 1-.757-.757l.706-2.82a3.5 3.5 0 0 1 .888-1.564l8.1-8.1a2.625 2.625 0 0 1 3.712 0Zm-2.65 1.062-8.1 8.1a2.25 2.25 0 0 0-.57 1.006l-.46 1.838 1.838-.46a2.25 2.25 0 0 0 1.006-.57l8.1-8.1a1.375 1.375 0 1 0-1.94-1.94Z" />
                                </svg>
                                <span>Редагувати питання</span>
                            </button>
                            <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-red-700 hover:bg-red-100"
                                onclick="techEditor.deleteQuestion(${question.id})">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.5 3a1.5 1.5 0 0 1 1.5-1.5h2a1.5 1.5 0 0 1 1.5 1.5H15a.75.75 0 0 1 0 1.5h-.556l-.67 10.057A2.25 2.25 0 0 1 11.53 16.75H8.47a2.25 2.25 0 0 1-2.244-2.193L5.556 4.5H5a.75.75 0 0 1 0-1.5h2.5Zm3.75 3a.75.75 0 0 1 1.5 0l-.375 7.5a.75.75 0 0 1-1.5 0l.375-7.5Zm-3 0a.75.75 0 1 0-1.5 0l.375 7.5a.75.75 0 0 0 1.5 0l-.375-7.5Z" clip-rule="evenodd" />
                                </svg>
                                <span>Видалити питання</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold" data-question-level>${escapeHtml(levelLabel)}</span>
                        <button type="button"
                            class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                            onclick="techEditor.editQuestionLevel(${question.id})">
                            Змінити
                        </button>
                    </div>
                </header>
                <div data-variants-section>
                    ${variantsHtml}
                </div>
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Правильні відповіді</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addAnswer(${question.id})">
                            <span>Додати відповідь</span>
                        </button>
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                            onclick="techEditor.addVerbHint(${question.id})">
                            <span>Додати verb hint</span>
                        </button>
                    </div>
                    <ul class="mt-3 space-y-2 text-sm text-stone-800" data-answers-container>
                        ${answersHtml}
                    </ul>
                </details>
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Варіанти відповіді</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addOption(${question.id})">
                            <span>Додати варіант</span>
                        </button>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2" data-options-container>
                        ${optionsHtml}
                    </div>
                </details>
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Question hints</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100"
                            onclick="techEditor.addQuestionHint(${question.id})">
                            <span>Додати підказку</span>
                        </button>
                    </div>
                    <ul class="mt-3 space-y-3 text-sm text-stone-800" data-question-hints-container>
                        ${hintsHtml}
                    </ul>
                </details>
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>ChatGPT explanations</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 flex flex-col gap-2">
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100${addExplanationButtonExtraClasses}"
                                data-add-chatgpt-explanation
                                data-question-id="${question.id}"
                                ${addExplanationButtonDisabledAttr}
                                onclick="techEditor.addChatGptExplanation(${question.id})">
                                <span>Додати пояснення</span>
                            </button>
                        </div>
                        <p class="${addExplanationHintClass}text-xs text-stone-500" data-chatgpt-explanation-hint>
                            Додайте варіанти відповіді та позначте правильні, щоб створити пояснення.
                        </p>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-stone-800">
                            <thead class="text-xs uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="py-2 pr-4">Мова</th>
                                    <th class="py-2 pr-4">Неправильна відповідь</th>
                                    <th class="py-2 pr-4">Правильна відповідь</th>
                                    <th class="py-2">Пояснення</th>
                                    <th class="py-2 text-right">Дії</th>
                                </tr>
                            </thead>
                            <tbody class="align-top" data-explanations-container>
                                ${explanationsHtml}
                            </tbody>
                        </table>
                    </div>
                </details>
            `;

            container.appendChild(article);

            updateChatgptExplanationAvailability({ element: article, data: question });

            return article;
        }

        function getMarkersFromEntry(entry) {
            if (!entry || !entry.data) {
                return [];
            }

            const markers = Array.isArray(entry.data.markers) ? entry.data.markers.filter(Boolean) : [];

            if (markers.length) {
                return markers.map(marker => marker.toLowerCase());
            }

            if (typeof entry.data.question === 'string') {
                const matches = entry.data.question.match(/\{(a\d+)\}/gi) || [];
                return matches.map(match => match.replace(/[{}]/g, '').toLowerCase());
            }

            return [];
        }

        function applyQuestionData(questionData) {
            if (!questionData || typeof questionData.id === 'undefined') {
                return null;
            }

            syncHintProviders(questionData);

            let entry = state.get(questionData.id);
            let isNewEntry = false;

            if (!entry) {
                let element = document.querySelector(`[data-question-id="${questionData.id}"]`);

                if (!element) {
                    element = createQuestionElement(questionData);

                    if (!element) {
                        return null;
                    }

                    isNewEntry = true;
                }

                entry = { element, data: questionData };
                state.set(questionData.id, entry);
            } else {
                entry.data = questionData;
            }

            entry.element.setAttribute('data-question', JSON.stringify(questionData));

            const answersMap = questionData.answers_by_marker || {};
            const questionTextEl = entry.element.querySelector('[data-question-text]');
            if (questionTextEl) {
                questionTextEl.innerHTML = highlightSegments(questionData.question, answersMap);
            }

            const levelEl = entry.element.querySelector('[data-question-level]');
            if (levelEl) {
                const levelValue = typeof questionData.level === 'string' && questionData.level.trim() !== ''
                    ? questionData.level
                    : null;

                levelEl.textContent = levelValue || 'N/A';
            }

            const variantsSection = entry.element.querySelector('[data-variants-section]');
            if (variantsSection) {
                variantsSection.innerHTML = renderVariantsSection(questionData);
            }

            const optionsContainer = entry.element.querySelector('[data-options-container]');
            if (optionsContainer) {
                optionsContainer.innerHTML = renderOptionsHtml(questionData);
            }

            const answersContainer = entry.element.querySelector('[data-answers-container]');
            if (answersContainer) {
                answersContainer.innerHTML = renderAnswersHtml(questionData);
            }

            const hintsContainer = entry.element.querySelector('[data-question-hints-container]');
            if (hintsContainer) {
                hintsContainer.innerHTML = renderQuestionHintsHtml(questionData);
            }

            const explanationsContainer = entry.element.querySelector('[data-explanations-container]');
            if (explanationsContainer) {
                explanationsContainer.innerHTML = renderChatgptExplanationsHtml(questionData);
            }

            updateChatgptExplanationAvailability(entry);

            if (isNewEntry) {
                refreshQuestionNumbers();
            }

            updateQuestionCount();

            return { entry, isNew: isNewEntry };
        }

        function removeQuestion(questionId) {
            const entry = state.get(questionId);

            if (entry && entry.element) {
                entry.element.remove();
            } else {
                const element = document.querySelector(`[data-question-id="${questionId}"]`);
                if (element) {
                    element.remove();
                }
            }

            state.delete(questionId);

            const container = getQuestionsContainer();
            if (!container || !container.querySelector('[data-question-id]')) {
                showEmptyState();
            }

            refreshQuestionNumbers();
            updateQuestionCount();
        }

        function clearError() {
            if (modal.error) {
                modal.error.classList.add('hidden');
                modal.error.textContent = '';
            }
        }

        function showError(message) {
            if (!modal.error) {
                return;
            }

            modal.error.textContent = message;
            modal.error.classList.remove('hidden');
        }

        function setLoading(isLoading) {
            if (modal.submitButton) {
                modal.submitButton.disabled = isLoading;
                modal.submitButton.classList.toggle('opacity-60', isLoading);
            }
        }

        function closeModal() {
            submitHandler = null;

            if (modal.form) {
                modal.form.reset();
            }

            if (modal.fields) {
                modal.fields.innerHTML = '';
            }

            clearError();

            if (modal.element) {
                modal.element.classList.add('hidden');
                modal.element.classList.remove('flex');
            }
        }

        function openModal(config) {
            if (!modal.element) {
                return;
            }

            clearError();

            if (modal.title) {
                modal.title.textContent = config.title || '';
            }

            if (modal.fields) {
                modal.fields.innerHTML = '';

                const focusables = [];

                (config.fields || []).forEach(field => {
                    const fieldId = `tech-field-${field.name}-${Math.random().toString(16).slice(2)}`;

                    const wrapper = document.createElement('div');
                    const label = document.createElement('label');

                    label.setAttribute('for', fieldId);
                    label.className = 'block text-sm font-semibold text-stone-700';
                    label.textContent = field.label || '';

                    wrapper.appendChild(label);

                    let input;
                    const fieldType = field.type || 'text';

                    if (fieldType === 'textarea') {
                        input = document.createElement('textarea');
                        input.rows = field.rows || 4;
                    } else if (fieldType === 'select') {
                        input = document.createElement('select');

                        const options = Array.isArray(field.options) ? field.options : [];

                        options.forEach(option => {
                            const optionElement = document.createElement('option');

                            if (option && typeof option === 'object') {
                                optionElement.value = option.value ?? '';
                                optionElement.textContent = option.label ?? option.value ?? '';

                                if (option.disabled) {
                                    optionElement.disabled = true;
                                }

                                if (option.hidden) {
                                    optionElement.hidden = true;
                                }
                            } else {
                                optionElement.value = option;
                                optionElement.textContent = option;
                            }

                            input.appendChild(optionElement);
                        });

                        if (field.multiple) {
                            input.multiple = true;
                        }
                    } else {
                        input = document.createElement('input');
                        input.type = fieldType;
                    }

                    input.id = fieldId;
                    input.name = field.name;
                    input.required = !!field.required;

                    if (field.placeholder && 'placeholder' in input) {
                        input.placeholder = field.placeholder;
                    }

                    input.className = 'mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 shadow-sm focus:border-blue-500 focus:ring-blue-500';

                    if (field.autocomplete && 'autocomplete' in input) {
                        input.autocomplete = field.autocomplete;
                    }

                    if (fieldType === 'textarea') {
                        input.value = field.value ?? '';
                    } else if (fieldType === 'select') {
                        let defaultValue = field.value;

                        if (defaultValue === null || defaultValue === undefined) {
                            defaultValue = '';
                        }

                        input.value = String(defaultValue);
                    } else {
                        input.value = field.value ?? '';
                    }

                    wrapper.appendChild(input);
                    modal.fields.appendChild(wrapper);
                    focusables.push(input);
                });

                if (focusables.length > 0) {
                    window.requestAnimationFrame(() => focusables[0].focus());
                }
            }

            submitHandler = config.onSubmit || null;

            modal.element.classList.remove('hidden');
            modal.element.classList.add('flex');
        }

        function sendMutation(url, payload, method = 'PUT') {
            const options = {
                method,
                headers: {
                    'X-CSRF-TOKEN': techCsrfToken,
                    'Accept': 'application/json',
                },
            };

            if (method !== 'GET') {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(payload);
            }

            return fetch(url, options).then(async response => {
                const contentType = response.headers.get('Content-Type') || '';

                if (response.ok) {
                    if (contentType.includes('application/json')) {
                        const data = await response.json();
                        return data.data ?? data;
                    }

                    throw new Error('Порожня відповідь сервера.');
                }

                let message = 'Не вдалося зберегти зміни.';

                if (response.status === 422 && contentType.includes('application/json')) {
                    const data = await response.json();
                    const errors = data.errors ? Object.values(data.errors).flat() : [];
                    if (errors.length) {
                        message = errors.join(' ');
                    }
                }

                throw new Error(message);
            });
        }

        const techEditor = {
            createQuestion() {
                openModal({
                    title: 'Створити нове питання',
                    fields: [
                        {
                            name: 'question',
                            label: 'Текст питання',
                            type: 'textarea',
                            required: true,
                            placeholder: 'Введіть текст питання з маркерами {a1}, {a2}, …',
                        },
                    ],
                    onSubmit(values) {
                        const questionText = typeof values.question === 'string' ? values.question.trim() : '';

                        if (!questionText) {
                            return Promise.reject(new Error('Введіть текст питання.'));
                        }

                        const markerMatches = questionText.match(/\{a\d+\}/gi) || [];

                        if (!markerMatches.length) {
                            return Promise.reject(new Error('Додайте щонайменше одну позначку {a1} у тексті питання.'));
                        }

                        return sendMutation(routes.createQuestion, { question: questionText }, 'POST');
                    },
                });
            },
            editQuestion(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                openModal({
                    title: 'Редагувати питання',
                    fields: [
                        {
                            name: 'question',
                            label: 'Текст питання',
                            type: 'textarea',
                            value: entry.data.question ?? '',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.question}/${questionId}`, { question: values.question })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteQuestion(questionId) {
                if (!window.confirm('Видалити це питання?')) {
                    return;
                }

                return sendMutation(`${routes.deleteQuestion}/${questionId}`, {}, 'DELETE')
                    .then(() => {
                        removeQuestion(questionId);
                    })
                    .catch(error => window.alert(error.message || 'Не вдалося видалити питання.'));
            },
            editQuestionLevel(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const options = [
                    { value: '', label: 'Не вибрано' },
                ];

                if (Array.isArray(cefrLevels) && cefrLevels.length) {
                    options.push(
                        ...cefrLevels.map(level => ({ value: level, label: level }))
                    );
                }

                openModal({
                    title: 'Змінити рівень питання',
                    fields: [
                        {
                            name: 'level',
                            label: 'Рівень',
                            type: 'select',
                            value: entry.data.level ?? '',
                            options,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.question}/${questionId}`, { level: values.level || null })
                            .then(applyQuestionData);
                    },
                });
            },
            editVariant(questionId, variantId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const variant = (entry.data.variants || []).find(item => item.id === variantId);
                if (!variant) {
                    return;
                }

                openModal({
                    title: 'Редагувати варіант питання',
                    fields: [
                        {
                            name: 'text',
                            label: 'Текст варіанту',
                            type: 'textarea',
                            value: variant.text ?? '',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.variant}/${variantId}`, { text: values.text })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteVariant(questionId, variantId) {
                if (!window.confirm('Видалити цей варіант запитання?')) {
                    return;
                }

                return sendMutation(`${routes.variant}/${variantId}`, { question_id: questionId }, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити варіант.'));
            },
            editAnswer(questionId, answerId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const answer = (entry.data.answers || []).find(item => item.id === answerId);
                if (!answer) {
                    return;
                }

                openModal({
                    title: `Редагувати відповідь (${answer.marker})`,
                    fields: [
                        {
                            name: 'value',
                            label: 'Відповідь',
                            type: 'text',
                            value: answer.value ?? '',
                            required: true,
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.answer}/${answerId}`, { value: values.value })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteAnswer(questionId, answerId) {
                if (!window.confirm('Видалити цю відповідь?')) {
                    return;
                }

                return sendMutation(`${routes.answer}/${answerId}`, {}, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити відповідь.'));
            },
            editOption(questionId, optionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const option = (entry.data.options || []).find(item => item.id === optionId);
                if (!option) {
                    return;
                }

                openModal({
                    title: 'Редагувати варіант відповіді',
                    fields: [
                        {
                            name: 'value',
                            label: 'Варіант відповіді',
                            type: 'text',
                            value: option.label ?? '',
                            required: true,
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.option}/${questionId}/options/${optionId}`, { value: values.value })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteOption(questionId, optionId) {
                if (!window.confirm('Видалити цей варіант відповіді?')) {
                    return;
                }

                return sendMutation(`${routes.option}/${questionId}/options/${optionId}`, {}, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити варіант.'));
            },
            editQuestionHint(questionId, hintId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const hint = (entry.data.question_hints || []).find(item => item.id === hintId);
                if (!hint) {
                    return;
                }

                const providerField = buildHintProviderField(hint.provider || '');

                openModal({
                    title: 'Редагувати підказку',
                    fields: [
                        providerField,
                        {
                            name: 'locale',
                            label: 'Мова (locale)',
                            type: 'text',
                            value: hint.locale ?? 'uk',
                            required: true,
                        },
                        {
                            name: 'hint',
                            label: 'Текст підказки',
                            type: 'textarea',
                            value: hint.hint ?? '',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.questionHint}/${hintId}`, {
                            hint: values.hint,
                            provider: values.provider,
                            locale: values.locale,
                        })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteQuestionHint(questionId, hintId) {
                if (!window.confirm('Видалити цю підказку?')) {
                    return;
                }

                return sendMutation(`${routes.questionHint}/${hintId}`, {}, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити підказку.'));
            },
            editVerbHint(questionId, verbHintId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const answer = (entry.data.answers || []).find(item => item.verb_hint && item.verb_hint.id === verbHintId);
                if (!answer || !answer.verb_hint) {
                    return;
                }

                openModal({
                    title: 'Редагувати verb hint',
                    fields: [
                        {
                            name: 'hint',
                            label: 'Verb hint',
                            type: 'text',
                            value: answer.verb_hint.value ?? '',
                            required: true,
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.verbHint}/${verbHintId}`, { hint: values.hint })
                            .then(applyQuestionData);
                    },
                });
            },
            deleteVerbHint(questionId, verbHintId) {
                if (!window.confirm('Видалити цей verb hint?')) {
                    return;
                }

                return sendMutation(`${routes.verbHint}/${verbHintId}`, {}, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити verb hint.'));
            },
            addAnswer(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const markers = getMarkersFromEntry(entry);
                const usedMarkers = new Set((entry.data.answers || []).map(answer => answer.marker_key));
                const availableMarkers = markers.filter(marker => !usedMarkers.has(marker));

                if (!availableMarkers.length) {
                    window.alert('Усі маркери вже мають відповіді.');
                    return;
                }

                const markerOptions = availableMarkers.map(marker => ({ value: marker, label: marker.toUpperCase() }));

                openModal({
                    title: 'Додати відповідь',
                    fields: [
                        {
                            name: 'marker',
                            label: 'Позначка',
                            type: 'select',
                            value: markerOptions[0]?.value ?? '',
                            options: markerOptions,
                        },
                        {
                            name: 'value',
                            label: 'Відповідь',
                            type: 'text',
                            required: true,
                            autocomplete: 'off',
                        },
                        {
                            name: 'verb_hint',
                            label: 'Verb hint (необовʼязково)',
                            type: 'text',
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        const payload = {
                            question_id: questionId,
                            marker: values.marker,
                            value: values.value,
                        };

                        if (values.verb_hint && values.verb_hint.trim() !== '') {
                            payload.verb_hint = values.verb_hint;
                        }

                        return sendMutation(routes.answer, payload, 'POST')
                            .then(applyQuestionData);
                    },
                });
            },
            addVerbHint(questionId, markerKey = null) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const candidates = (entry.data.answers || []).filter(answer => !answer.verb_hint);

                if (!candidates.length) {
                    window.alert('Усі відповіді вже мають verb hint.');
                    return;
                }

                const options = candidates.map(answer => ({
                    value: answer.marker_key,
                    label: answer.marker,
                }));

                let defaultValue = options[0]?.value ?? '';
                if (markerKey && options.some(option => option.value === markerKey)) {
                    defaultValue = markerKey;
                }

                openModal({
                    title: 'Додати verb hint',
                    fields: [
                        {
                            name: 'marker',
                            label: 'Позначка',
                            type: 'select',
                            value: defaultValue,
                            options,
                        },
                        {
                            name: 'hint',
                            label: 'Verb hint',
                            type: 'text',
                            required: true,
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(routes.verbHint, {
                            question_id: questionId,
                            marker: values.marker,
                            hint: values.hint,
                        }, 'POST').then(applyQuestionData);
                    },
                });
            },
            addOption(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                openModal({
                    title: 'Додати варіант відповіді',
                    fields: [
                        {
                            name: 'value',
                            label: 'Варіант відповіді',
                            type: 'text',
                            required: true,
                            autocomplete: 'off',
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(`${routes.option}/${questionId}/options`, {
                            value: values.value,
                        }, 'POST').then(applyQuestionData);
                    },
                });
            },
            addQuestionHint(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                const providerField = buildHintProviderField('');

                openModal({
                    title: 'Додати підказку',
                    fields: [
                        providerField,
                        {
                            name: 'locale',
                            label: 'Мова (locale)',
                            type: 'text',
                            required: true,
                            value: 'uk',
                        },
                        {
                            name: 'hint',
                            label: 'Текст підказки',
                            type: 'textarea',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(routes.questionHint, {
                            question_id: questionId,
                            provider: values.provider,
                            locale: values.locale,
                            hint: values.hint,
                        }, 'POST').then(applyQuestionData);
                    },
                });
            },
            addChatGptExplanation(questionId) {
                const entry = state.get(questionId);
                if (!entry) {
                    return;
                }

                if (!canAddChatGptExplanation(entry.data || {})) {
                    window.alert('Спочатку додайте варіанти відповіді та позначте правильні.');
                    return;
                }

                const correctChoices = getCorrectAnswerChoices(entry.data || {});
                const incorrectChoices = getIncorrectAnswerChoices(entry.data || {});

                if (!correctChoices.length) {
                    window.alert('Спочатку позначте хоча б одну правильну відповідь.');
                    return;
                }

                const correctOptions = [
                    { value: '', label: 'Оберіть правильну відповідь' },
                    ...correctChoices.map(choice => ({ value: choice.value, label: choice.label })),
                ];

                const wrongOptions = [
                    { value: '', label: '— Не обрано —' },
                    ...incorrectChoices.map(choice => ({ value: choice.value, label: choice.label })),
                ];

                openModal({
                    title: 'Додати пояснення',
                    fields: [
                        {
                            name: 'language',
                            label: 'Мова',
                            type: 'text',
                            value: 'uk',
                            required: true,
                        },
                        {
                            name: 'correct_answer',
                            label: 'Правильна відповідь',
                            type: 'select',
                            required: true,
                            options: correctOptions,
                        },
                        {
                            name: 'wrong_answer',
                            label: 'Неправильна відповідь',
                            type: 'select',
                            options: wrongOptions,
                        },
                        {
                            name: 'explanation',
                            label: 'Пояснення',
                            type: 'textarea',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendMutation(routes.chatgptExplanation, {
                            question_id: questionId,
                            language: values.language,
                            correct_answer: values.correct_answer,
                            wrong_answer: values.wrong_answer || '',
                            explanation: values.explanation,
                        }, 'POST').then(applyQuestionData);
                    },
                });
            },
            deleteChatGptExplanation(questionId, explanationId) {
                if (!window.confirm('Видалити це пояснення?')) {
                    return;
                }

                return sendMutation(`${routes.chatgptExplanation}/${explanationId}`, { question_id: questionId }, 'DELETE')
                    .then(applyQuestionData)
                    .catch(error => window.alert(error.message || 'Не вдалося видалити пояснення.'));
            },
            applyQuestionData,
        };

        function initialiseState() {
            document.querySelectorAll('[data-question-id]').forEach(element => {
                const raw = element.getAttribute('data-question');
                if (!raw) {
                    return;
                }

                try {
                    const data = JSON.parse(raw);
                    state.set(data.id, { element, data });
                    syncHintProviders(data);
                    updateChatgptExplanationAvailability({ element, data });
                } catch (error) {
                    console.error('Не вдалося розпізнати дані питання', error);
                }
            });

            refreshQuestionNumbers();
            updateQuestionCount();
        }

        function setupModalHandlers() {
            if (!modal.element || !modal.form) {
                return;
            }

            modal.form.addEventListener('submit', event => {
                event.preventDefault();

                if (!submitHandler) {
                    return;
                }

                const formData = new FormData(modal.form);
                const values = Object.fromEntries(formData.entries());

                clearError();
                setLoading(true);

                Promise.resolve()
                    .then(() => submitHandler(values))
                    .then(question => {
                        if (question) {
                            const result = applyQuestionData(question);

                            if (result && result.isNew && result.entry && result.entry.element) {
                                window.requestAnimationFrame(() => {
                                    result.entry.element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                });
                            }
                        }
                        closeModal();
                    })
                    .catch(error => {
                        showError(error.message || 'Не вдалося зберегти зміни.');
                    })
                    .finally(() => setLoading(false));
            });

            if (modal.cancelButton) {
                modal.cancelButton.addEventListener('click', closeModal);
            }

            if (modal.closeButton) {
                modal.closeButton.addEventListener('click', closeModal);
            }

            modal.element.addEventListener('click', event => {
                if (event.target === modal.element) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !modal.element.classList.contains('hidden')) {
                    closeModal();
                }
            });
        }

        initialiseState();
        setupModalHandlers();

        window.techEditor = techEditor;
    })();
</script>
@endsection
