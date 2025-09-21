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

    @php
        $cefrLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    @endphp

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
            $questionLevel = in_array($question->level, $cefrLevels, true) ? $question->level : null;
            $levelLabel = $questionLevel ?? 'N/A';

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
            ];
        @endphp
        <article class="bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100"
                 data-question-id="{{ $question->id }}"
                 data-question='@json($techQuestionData)'>
            <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex items-baseline gap-3 text-sm text-stone-500">
                        <span class="font-semibold uppercase tracking-wide">Питання {{ $loop->iteration }}</span>
                        <span>ID: {{ $question->id }}</span>
                    </div>
                    <p class="mt-2 text-lg leading-relaxed text-stone-900" data-question-text>{!! $filledQuestion !!}</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold text-blue-600">
                        <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-blue-700 hover:bg-blue-100"
                                onclick="techEditor.editQuestion({{ $question->id }})">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M15.728 2.272a2.625 2.625 0 0 1 0 3.712l-8.1 8.1a3.5 3.5 0 0 1-1.563.888l-2.82.705a.625.625 0 0 1-.757-.757l.706-2.82a3.5 3.5 0 0 1 .888-1.564l8.1-8.1a2.625 2.625 0 0 1 3.712 0Zm-2.65 1.062-8.1 8.1a2.25 2.25 0 0 0-.57 1.006l-.46 1.838 1.838-.46a2.25 2.25 0 0 0 1.006-.57l8.1-8.1a1.375 1.375 0 1 0-1.94-1.94Z" />
                            </svg>
                            <span>Редагувати питання</span>
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
                                    <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                            onclick="techEditor.editVariant({{ $question->id }}, {{ $variant->id }})">Редагувати</button>
                                </div>
                                <span data-variant-text>{!! $highlightSegments($variant->text) !!}</span>
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
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                                    onclick="techEditor.editAnswer({{ $question->id }}, {{ $answer->id }})">Редагувати відповідь</button>
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
                    <div class="mt-3 flex flex-wrap gap-2" data-options-container>
                        @foreach($options as $option)
                            @php $isCorrectOption = (bool) ($option['is_correct'] ?? false); @endphp
                            <div @class([
                                'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm',
                                'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm' => $isCorrectOption,
                                'border-stone-200 bg-stone-50 text-stone-800' => ! $isCorrectOption,
                            ])
                            data-option-id="{{ $option['id'] ?? '' }}">
                                @if($isCorrectOption)
                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span>{{ $option['label'] }}</span>
                                @if(! empty($option['id']))
                                    <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                                            onclick="techEditor.editOption({{ $question->id }}, {{ $option['id'] }})">Редагувати</button>
                                @endif
                            </div>
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
                            <li class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2"
                                data-question-hint-id="{{ $hint->id }}">
                                <div class="flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                                    <span>{{ $hint->provider }} · {{ strtoupper($hint->locale) }}</span>
                                    <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800"
                                            onclick="techEditor.editQuestionHint({{ $question->id }}, {{ $hint->id }})">Редагувати</button>
                                </div>
                                <div class="mt-1 whitespace-pre-line text-stone-800" data-question-hint-text>{{ $hint->hint }}</div>
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
        </article>
    @endforeach
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
            question: '{{ url('/questions') }}',
            answer: '{{ url('/question-answers') }}',
            variant: '{{ url('/question-variants') }}',
            option: '{{ url('/questions') }}',
            questionHint: '{{ url('/question-hints') }}',
            verbHint: '{{ url('/verb-hints') }}',
        };

        const cefrLevels = @json($cefrLevels);

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
            }

            parts.push('</div>');

            return parts.join('');
        }

        function applyQuestionData(questionData) {
            if (!questionData || typeof questionData.id === 'undefined') {
                return;
            }

            let entry = state.get(questionData.id);

            if (!entry) {
                const element = document.querySelector(`[data-question-id="${questionData.id}"]`);

                if (!element) {
                    return;
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
                const levelValue = questionData.level;
                levelEl.textContent = levelValue ? levelValue : 'N/A';
            }

            (questionData.variants || []).forEach(variant => {
                const variantEl = entry.element.querySelector(`[data-variant-id="${variant.id}"] [data-variant-text]`);
                if (variantEl) {
                    variantEl.innerHTML = highlightSegments(variant.text, answersMap);
                }
            });

            (questionData.answers || []).forEach(answer => {
                const answerRow = entry.element.querySelector(`[data-answer-id="${answer.id}"]`);
                if (!answerRow) {
                    return;
                }

                const valueEl = answerRow.querySelector('[data-answer-value]');
                if (valueEl) {
                    valueEl.textContent = answer.value ?? '';
                }

                const verbHintEl = answerRow.querySelector('[data-verb-hint]');
                if (verbHintEl && answer.verb_hint) {
                    verbHintEl.setAttribute('data-verb-hint-id', answer.verb_hint.id);
                    const value = verbHintEl.querySelector('[data-verb-hint-value]');
                    if (value) {
                        value.textContent = answer.verb_hint.value ?? '';
                    }
                }
            });

            const optionsContainer = entry.element.querySelector('[data-options-container]');
            if (optionsContainer) {
                const html = (questionData.options || [])
                    .map(option => renderOptionHtml(questionData.id, option))
                    .join('');

                optionsContainer.innerHTML = html;
            }

            (questionData.question_hints || []).forEach(hint => {
                const hintEl = entry.element.querySelector(`[data-question-hint-id="${hint.id}"] [data-question-hint-text]`);
                if (hintEl) {
                    hintEl.textContent = hint.hint ?? '';
                }
            });
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

        function sendUpdate(url, payload) {
            return fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': techCsrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            }).then(async response => {
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
                        return sendUpdate(`${routes.question}/${questionId}`, { question: values.question })
                            .then(applyQuestionData);
                    },
                });
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
                        return sendUpdate(`${routes.question}/${questionId}`, { level: values.level || null })
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
                        return sendUpdate(`${routes.variant}/${variantId}`, { text: values.text })
                            .then(applyQuestionData);
                    },
                });
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
                        return sendUpdate(`${routes.answer}/${answerId}`, { value: values.value })
                            .then(applyQuestionData);
                    },
                });
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
                        return sendUpdate(`${routes.option}/${questionId}/options/${optionId}`, { value: values.value })
                            .then(applyQuestionData);
                    },
                });
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

                openModal({
                    title: 'Редагувати підказку',
                    fields: [
                        {
                            name: 'hint',
                            label: 'Текст підказки',
                            type: 'textarea',
                            value: hint.hint ?? '',
                            required: true,
                        },
                    ],
                    onSubmit(values) {
                        return sendUpdate(`${routes.questionHint}/${hintId}`, { hint: values.hint })
                            .then(applyQuestionData);
                    },
                });
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
                        return sendUpdate(`${routes.verbHint}/${verbHintId}`, { hint: values.hint })
                            .then(applyQuestionData);
                    },
                });
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
                } catch (error) {
                    console.error('Не вдалося розпізнати дані питання', error);
                }
            });
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
                            applyQuestionData(question);
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
