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
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold text-blue-600">
                        <button type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-blue-700 hover:bg-blue-100"
                                onclick="editQuestion({{ $question->id }}, @js($question->question))">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M15.728 2.272a2.625 2.625 0 0 1 0 3.712l-8.1 8.1a3.5 3.5 0 0 1-1.563.888l-2.82.705a.625.625 0 0 1-.757-.757l.706-2.82a3.5 3.5 0 0 1 .888-1.564l8.1-8.1a2.625 2.625 0 0 1 3.712 0Zm-2.65 1.062-8.1 8.1a2.25 2.25 0 0 0-.57 1.006l-.46 1.838 1.838-.46a2.25 2.25 0 0 0 1.006-.57l8.1-8.1a1.375 1.375 0 1 0-1.94-1.94Z" />
                            </svg>
                            <span>Редагувати питання</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold">{{ $levelLabel }}</span>
                    <button type="button"
                            class="text-xs font-semibold text-blue-600 underline hover:text-blue-800"
                            onclick="editQuestionLevel({{ $question->id }}, @js($question->level))">
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
                            <li class="flex flex-col gap-1 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] uppercase text-stone-500">Варіант {{ $loop->iteration }}</span>
                                    <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800" onclick="editVariant({{ $variant->id }}, @js($variant->text))">Редагувати</button>
                                </div>
                                <span>{!! $highlightSegments($variant->text) !!}</span>
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
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-2">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="font-mono text-xs uppercase text-emerald-500">{{ $marker }}</span>
                                <span class="font-semibold text-emerald-900">{{ $answerValue }}</span>
                                @if($verbHintValue)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                        <span class="font-semibold uppercase text-[10px] tracking-wide">Verb hint</span>
                                        <span>{{ $verbHintValue }}</span>
                                        @if($verbHintModel)
                                            <button type="button" class="text-[10px] font-semibold text-blue-600 underline hover:text-blue-800" onclick="editVerbHintValue({{ $verbHintModel->id }}, @js($verbHintValue))">Редагувати</button>
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800" onclick="editAnswer({{ $answer->id }}, @js($marker), @js($answerValue))">Редагувати відповідь</button>
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
                            @php $isCorrectOption = (bool) ($option['is_correct'] ?? false); @endphp
                            <div @class([
                                'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm',
                                'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold shadow-sm' => $isCorrectOption,
                                'border-stone-200 bg-stone-50 text-stone-800' => ! $isCorrectOption,
                            ])>
                                @if($isCorrectOption)
                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span>{{ $option['label'] }}</span>
                                @if(! empty($option['id']))
                                    <button type="button" class="text-xs font-semibold text-blue-600 underline hover:text-blue-800" onclick="editOption({{ $question->id }}, {{ $option['id'] }}, @js($option['label']))">Редагувати</button>
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
                            <li class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-2">
                                <div class="flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                                    <span>{{ $hint->provider }} · {{ strtoupper($hint->locale) }}</span>
                                    <button type="button" class="text-[11px] font-semibold text-blue-600 underline hover:text-blue-800" onclick="editQuestionHint({{ $hint->id }}, @js($hint->hint))">Редагувати</button>
                                </div>
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
        </article>
    @endforeach
</div>
<script>
    const techCsrfToken = '{{ csrf_token() }}';
    const questionUpdateUrl = '{{ url('/questions') }}';
    const answerUpdateUrl = '{{ url('/question-answers') }}';
    const variantUpdateUrl = '{{ url('/question-variants') }}';
    const optionUpdateBaseUrl = '{{ url('/questions') }}';
    const questionHintUpdateUrl = '{{ url('/question-hints') }}';
    const verbHintUpdateUrl = '{{ url('/verb-hints') }}';

    function sendUpdate(url, payload) {
        return fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': techCsrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(response => {
            if (!response.ok) {
                throw new Error('Request failed');
            }
        });
    }

    function handleSuccess() {
        location.reload();
    }

    function handleError(error) {
        console.error(error);
        alert('Не вдалося зберегти зміни.');
    }

    function editQuestion(id, currentText) {
        const updated = prompt('Оновіть текст питання', currentText ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${questionUpdateUrl}/${id}`, { question: updated })
            .then(handleSuccess)
            .catch(handleError);
    }

    function editQuestionLevel(id, currentLevel) {
        const updated = prompt('Вкажіть рівень питання', currentLevel ?? '');
        if (updated === null) {
            return;
        }

        const payload = { level: updated === '' ? null : updated };

        sendUpdate(`${questionUpdateUrl}/${id}`, payload)
            .then(handleSuccess)
            .catch(handleError);
    }

    function editVariant(id, currentText) {
        const updated = prompt('Оновіть варіант питання', currentText ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${variantUpdateUrl}/${id}`, { text: updated })
            .then(handleSuccess)
            .catch(handleError);
    }

    function editAnswer(id, marker, currentValue) {
        const label = marker ? `Оновіть відповідь для ${marker}` : 'Оновіть відповідь';
        const updated = prompt(label, currentValue ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${answerUpdateUrl}/${id}`, { value: updated })
            .then(handleSuccess)
            .catch(handleError);
    }

    function editOption(questionId, optionId, currentValue) {
        const updated = prompt('Оновіть варіант відповіді', currentValue ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${optionUpdateBaseUrl}/${questionId}/options/${optionId}`, { value: updated })
            .then(handleSuccess)
            .catch(handleError);
    }

    function editQuestionHint(id, currentValue) {
        const updated = prompt('Оновіть текст підказки', currentValue ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${questionHintUpdateUrl}/${id}`, { hint: updated })
            .then(handleSuccess)
            .catch(handleError);
    }

    function editVerbHintValue(id, currentValue) {
        const updated = prompt('Оновіть verb hint', currentValue ?? '');
        if (updated === null) {
            return;
        }

        sendUpdate(`${verbHintUpdateUrl}/${id}`, { hint: updated })
            .then(handleSuccess)
            .catch(handleError);
    }
</script>
@endsection
