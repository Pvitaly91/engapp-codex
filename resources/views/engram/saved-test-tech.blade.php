@extends('layouts.app')

@section('title', $test->name . ' — Технічна сторінка')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div x-data="{ editing: false }" class="flex-1 space-y-3">
            <div x-show="!editing" class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-stone-900">{{ $test->name }}</h1>
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-600 hover:bg-stone-100"
                            @click="editing = true">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 6.5-8 8-3 3 .5-3.5 8-8 2.5 2.5Zm0 0 2-2a1.586 1.586 0 0 1 2.243 0 1.586 1.586 0 0 1 0 2.243l-2 2M10 4h-6a2 2 0 0 0-2 2v10c0 1.105.895 2 2 2h10a2 2 0 0 0 2-2v-6" />
                        </svg>
                        <span>Редагувати назву</span>
                    </button>
                </div>
                <p class="text-sm text-stone-600">Технічна інформація про тест · Питань: {{ $questions->count() }}</p>
            </div>
            <form x-show="editing" method="POST" action="{{ route('saved-tests.update', $test) }}" class="hidden rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                @csrf
                @method('put')
                <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Назва тесту</label>
                    <input type="text" name="name" value="{{ $test->name }}" required
                           class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                </div>
            </form>
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

            $optionItems = $question->options
                ->map(function ($option) use ($question) {
                    return (object) [
                        'model' => $option,
                        'text' => $option->option ?? '',
                        'is_correct' => $question->answers->contains('option_id', $option->id),
                        'editable' => true,
                    ];
                })
                ->filter(fn ($item) => $item->text !== '')
                ->values();

            $answerOnlyOptions = $question->answers
                ->filter(function ($answer) use ($question) {
                    return $answer->option && ! $question->options->contains('id', $answer->option_id);
                })
                ->map(function ($answer) {
                    return (object) [
                        'model' => $answer->option,
                        'text' => $answer->option->option ?? '',
                        'is_correct' => true,
                        'editable' => false,
                    ];
                })
                ->filter(fn ($item) => $item->text !== '')
                ->values();

            $options = $optionItems->concat($answerOnlyOptions)->unique('text')->values();

            $variantItems = $question->relationLoaded('variants')
                ? $question->variants->filter(fn ($variant) => filled($variant->text))->values()
                : collect();

            $verbHintsByMarker = $question->verbHints
                ->sortBy('marker')
                ->mapWithKeys(function ($hint) {
                    return [strtolower($hint->marker) => $hint];
                });

            $questionHints = $question->hints
                ->sortBy(function ($hint) {
                    return $hint->provider . '|' . $hint->locale;
                })
                ->values();

            $explanations = collect($explanationsByQuestionId[$question->id] ?? []);
            $levelLabel = $question->level ?: 'N/A';
        @endphp
        <article x-data="{ editingQuestion: false }" class="bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100">
            <header class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1">
                        <div class="flex items-baseline gap-3 text-sm text-stone-500">
                            <span class="font-semibold uppercase tracking-wide">Питання {{ $loop->iteration }}</span>
                            <span>ID: {{ $question->id }}</span>
                        </div>
                        <div x-show="!editingQuestion">
                            <p class="mt-2 text-lg leading-relaxed text-stone-900">{!! $filledQuestion !!}</p>
                        </div>
                    </div>
                    <div x-show="!editingQuestion" class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold">{{ $levelLabel }}</span>
                    </div>
                </div>
                <div x-show="!editingQuestion" class="flex justify-end">
                    <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="editingQuestion = true">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 6.5-8 8-3 3 .5-3.5 8-8 2.5 2.5Zm0 0 2-2a1.586 1.586 0 0 1 2.243 0 1.586 1.586 0 0 1 0 2.243l-2 2M10 4h-6a2 2 0 0 0-2 2v10c0 1.105.895 2 2 2h10a2 2 0 0 0 2-2v-6" />
                        </svg>
                        <span>Редагувати питання</span>
                    </button>
                </div>
                <form x-show="editingQuestion" method="POST" action="{{ route('questions.update', $question) }}" class="hidden rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-4">
                    @csrf
                    @method('put')
                    <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст питання</label>
                        <textarea name="question" rows="4" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">{{ $question->question }}</textarea>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Рівень</label>
                            <select name="level" class="mt-1 w-40 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                <option value="" @selected(empty($question->level))>N/A</option>
                                @foreach(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $levelOption)
                                    <option value="{{ $levelOption }}" @selected($question->level === $levelOption)>{{ $levelOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                            <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="editingQuestion = false">Скасувати</button>
                        </div>
                    </div>
                </form>
            </header>

            @if($variantItems->isNotEmpty())
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>Варіанти запитання</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <ul class="mt-3 space-y-2 text-sm text-stone-800">
                        @foreach($variantItems as $variant)
                            <li x-data="{ editing: false }" class="rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                                <div x-show="!editing" class="space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-[11px] uppercase text-stone-500">Варіант {{ $loop->iteration }}</span>
                                        <button type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="editing = true">
                                            <span>Редагувати</span>
                                        </button>
                                    </div>
                                    <span>{!! $highlightSegments($variant->text) !!}</span>
                                </div>
                                <form x-show="editing" method="POST" action="{{ route('question-variants.update', $variant) }}" class="hidden space-y-2">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст варіанту</label>
                                        <textarea name="text" rows="3" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">{{ $variant->text }}</textarea>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                                    </div>
                                </form>
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
                <ul class="mt-3 space-y-3 text-sm text-stone-800">
                    @foreach($question->answers as $answer)
                        @php
                            $marker = strtoupper($answer->marker);
                            $markerKey = strtolower($answer->marker);
                            $answerValue = $answersByMarker->get($markerKey, '');
                            $verbHintModel = $verbHintsByMarker->get($markerKey);
                            $verbHintValue = $verbHintModel ? ($verbHintModel->option->option ?? '') : null;
                        @endphp
                        <li class="rounded-lg border border-emerald-100 bg-emerald-50/70 px-3 py-3">
                            <div x-data="{ editingAnswer: false }" class="space-y-2">
                                <div x-show="!editingAnswer" class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs uppercase text-emerald-500">{{ $marker }}</span>
                                    <span class="font-semibold text-emerald-900">{{ $answerValue }}</span>
                                    <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-200 bg-white px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100" @click="editingAnswer = true">
                                        <span>Редагувати відповідь</span>
                                    </button>
                                </div>
                                <form x-show="editingAnswer" method="POST" action="{{ route('question-answers.update', $answer) }}" class="hidden space-y-2">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                    <div class="grid gap-3 sm:grid-cols-5">
                                        <div class="sm:col-span-1">
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Маркер</label>
                                            <input type="text" name="marker" value="{{ $marker }}" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                        <div class="sm:col-span-4">
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Відповідь</label>
                                            <input type="text" name="value" value="{{ $answerValue }}" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editingAnswer = false">Скасувати</button>
                                    </div>
                                </form>
                            </div>
                            @if($verbHintModel && $verbHintValue)
                                <div x-data="{ editingHint: false }" class="mt-3 rounded-lg border border-emerald-200 bg-white px-3 py-2">
                                    <div x-show="!editingHint" class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold uppercase text-[10px] tracking-wide text-emerald-600">Verb hint</span>
                                        <span class="text-sm text-emerald-800">{{ $verbHintValue }}</span>
                                        <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-200 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="editingHint = true">
                                            <span>Редагувати підказку</span>
                                        </button>
                                    </div>
                                    <form x-show="editingHint" method="POST" action="{{ route('verb-hints.update', $verbHintModel) }}" class="hidden space-y-2">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Підказка</label>
                                            <input type="text" name="hint" value="{{ $verbHintValue }}" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                            <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editingHint = false">Скасувати</button>
                                        </div>
                                    </form>
                                </div>
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
                        @foreach($options as $optionItem)
                            <div x-data="{ editing: false }" class="inline-flex flex-col items-start gap-2">
                                <div x-show="!editing" @class([
                                    'inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm shadow-sm',
                                    'border-emerald-200 bg-emerald-50 text-emerald-900 font-semibold' => $optionItem->is_correct,
                                    'border-stone-200 bg-stone-50 text-stone-800' => ! $optionItem->is_correct,
                                ])>
                                    @if($optionItem->is_correct)
                                        <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    <span>{{ $optionItem->text }}</span>
                                </div>
                                @if($optionItem->editable && $optionItem->model)
                                    <div class="flex flex-wrap gap-2">
                                        <button x-show="!editing" type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="editing = true">
                                            <span>Редагувати</span>
                                        </button>
                                    </div>
                                    <form x-show="editing" method="POST" action="{{ route('question-options.update', $optionItem->model) }}" class="hidden space-y-2 rounded-xl border border-stone-200 bg-white px-3 py-2">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                        <input type="hidden" name="question_id" value="{{ $question->id }}">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Значення</label>
                                            <input type="text" name="option" value="{{ $optionItem->text }}" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                            <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                                        </div>
                                    </form>
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
                            <li x-data="{ editing: false }" class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-3">
                                <div x-show="!editing" class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                                        <span>{{ $hint->provider }}</span>
                                        <span>·</span>
                                        <span>{{ strtoupper($hint->locale) }}</span>
                                        <button type="button" class="inline-flex items-center gap-1 rounded border border-blue-200 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100" @click="editing = true">
                                            <span>Редагувати</span>
                                        </button>
                                    </div>
                                    <div class="whitespace-pre-line text-stone-800">{{ $hint->hint }}</div>
                                </div>
                                <form x-show="editing" method="POST" action="{{ route('question-hints.update', $hint) }}" class="hidden space-y-3 rounded-xl border border-blue-200 bg-white px-3 py-3">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Провайдер</label>
                                            <input type="text" name="provider" value="{{ $hint->provider }}" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                                            <input type="text" name="locale" value="{{ $hint->locale }}" maxlength="5" required
                                                   class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Підказка</label>
                                        <textarea name="hint" rows="3" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">{{ $hint->hint }}</textarea>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                                    </div>
                                </form>
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
                                    <tr x-data="{ editing: false }" class="border-t border-stone-200">
                                        <td x-show="!editing" class="py-2 pr-4 font-semibold text-stone-600">{{ strtoupper($explanation->language) }}</td>
                                        <td x-show="!editing" class="py-2 pr-4">{{ $explanation->wrong_answer ?: '—' }}</td>
                                        <td x-show="!editing" @class([
                                            'py-2 pr-4 font-semibold',
                                            'text-emerald-700' => $isStoredCorrect,
                                            'text-stone-800' => ! $isStoredCorrect,
                                        ])>
                                            {{ $explanation->correct_answer }}
                                        </td>
                                        <td x-show="!editing" class="py-2">
                                            <div class="flex items-start justify-between gap-3">
                                                <span class="flex-1">{{ $explanation->explanation }}</span>
                                                <button type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="editing = true">
                                                    <span>Редагувати</span>
                                                </button>
                                            </div>
                                        </td>
                                        <td x-show="editing" colspan="4" class="hidden py-3">
                                            <form method="POST" action="{{ route('chatgpt-explanations.update', $explanation) }}" class="space-y-3 rounded-2xl border border-stone-200 bg-white px-4 py-3">
                                                @csrf
                                                @method('put')
                                                <input type="hidden" name="from" value="{{ request()->fullUrl() }}">
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                                                        <input type="text" name="language" value="{{ $explanation->language }}" maxlength="10" required
                                                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Неправильна відповідь</label>
                                                        <input type="text" name="wrong_answer" value="{{ $explanation->wrong_answer }}"
                                                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Правильна відповідь</label>
                                                        <input type="text" name="correct_answer" value="{{ $explanation->correct_answer }}" required
                                                               class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст питання</label>
                                                        <textarea name="question" rows="2" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">{{ $explanation->question }}</textarea>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Пояснення</label>
                                                    <textarea name="explanation" rows="3" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">{{ $explanation->explanation }}</textarea>
                                                </div>
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                                                </div>
                                            </form>
                                        </td>
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
