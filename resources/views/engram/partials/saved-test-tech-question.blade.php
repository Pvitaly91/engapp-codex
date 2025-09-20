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
    $questionChanges = collect(($pendingChangesByQuestion ?? collect())->get($question->id) ?? []);
    $questionChangeCount = $questionChanges->count();
@endphp
<article x-data="{ editingQuestion: false }" class="bg-white shadow rounded-2xl p-6 space-y-5 border border-stone-100">
    <header class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex-1">
                <div class="flex items-baseline gap-3 text-sm text-stone-500">
                    <span class="font-semibold uppercase tracking-wide">Питання {{ $iteration }}</span>
                    <span>ID: {{ $question->id }}</span>
                </div>
                <div x-show="!editingQuestion" x-ref="display">
                    <p class="mt-2 text-lg leading-relaxed text-stone-900">{!! $filledQuestion !!}</p>
                </div>
            </div>
            <div x-show="!editingQuestion" class="flex items-center gap-2">
                <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold">{{ $levelLabel }}</span>
            </div>
        </div>
        <div x-show="!editingQuestion" class="flex flex-wrap justify-end gap-2">
            <form method="POST"
                  action="{{ route('saved-test.question.destroy', [$test->slug, $question]) }}"
                  data-confirm="Видалити це питання з тесту?"
                  data-queue-change="true"
                  data-change-type="question.delete"
                  data-change-summary="Видалити питання"
                  data-question-id="{{ $question->id }}"
                  data-route-name="saved-test.question.destroy"
                  data-route-params="@js(['slug' => $test->slug, 'question' => $question->id])">
                @csrf
                @method('delete')
                <input type="hidden" name="from" value="{{ $returnUrl }}">
                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-100">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h8m-7 0V5a2 2 0 1 1 4 0v1m3 0v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6h10Z" />
                    </svg>
                    <span>Видалити</span>
                </button>
            </form>
            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="
                window.highlightEditable($refs.display);
                editingQuestion = true;
                $nextTick(() => window.highlightEditable($refs.form));
            ">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 6.5-8 8-3 3 .5-3.5 8-8 2.5 2.5Zm0 0 2-2a1.586 1.586 0 0 1 2.243 0 1.586 1.586 0 0 1 0 2.243l-2 2M10 4h-6a2 2 0 0 0-2 2v10c0 1.105.895 2 2 2h10a2 2 0 0 0 2-2v-6" />
                </svg>
                <span>Редагувати питання</span>
            </button>
        </div>
        <form x-show="editingQuestion" x-cloak x-ref="form" method="POST" action="{{ route('questions.update', $question) }}" class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-4"
              data-queue-change="true"
              data-change-type="question.update"
              data-change-summary="Оновити питання"
              data-question-id="{{ $question->id }}"
              data-route-name="questions.update"
              data-route-params="@js(['question' => $question->id])">
            @csrf
            @method('put')
            <input type="hidden" name="from" value="{{ $returnUrl }}">
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

    <section class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 4a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l3.414 3.414A1 1 0 0 1 16 6.414V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Z" opacity=".4" />
                    <path d="M6 0a4 4 0 0 0-4 4v12a4 4 0 0 0 4 4h8a4 4 0 0 0 4-4V6.414a3 3 0 0 0-.879-2.121l-3.414-3.414A3 3 0 0 0 11.586 0H6Zm.75 9a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 4a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" />
                </svg>
                <span>Черга змін</span>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-600/10 px-2 py-1 text-xs font-semibold text-amber-700"
                  data-question-change-count
                  data-question-id="{{ $question->id }}"
                  @class(['hidden' => $questionChangeCount === 0])>
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967A1 1 0 0 1 4.71 8.56l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                </svg>
                <span>{{ $questionChangeCount }}</span>
            </span>
        </div>
        <div class="space-y-3"
             data-question-change-list
             data-question-id="{{ $question->id }}"
             data-refresh-url="{{ route('saved-test.tech.changes.question', [$test->slug, $question->id]) }}">
            @include('engram.partials.saved-test-tech-question-change-list', [
                'test' => $test,
                'questionId' => $question->id,
                'changes' => $questionChanges,
            ])
        </div>
    </section>

    <details class="group">
        <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
            <span>Варіанти запитання</span>
            <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
            <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
        </summary>
        <ul class="mt-3 space-y-2 text-sm text-stone-800">
            @forelse($variantItems as $variant)
                <li x-data="{ editing: false }" class="rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                    <div x-show="!editing" x-ref="display" class="space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[11px] uppercase text-stone-500">Варіант {{ $loop->iteration }}</span>
                            <div class="flex items-center gap-2">
                                <form method="POST"
                                      action="{{ route('question-variants.destroy', $variant) }}"
                                      data-confirm="Видалити цей варіант?"
                                      data-queue-change="true"
                                      data-change-type="variant.delete"
                                      data-change-summary="Видалити варіант питання"
                                      data-question-id="{{ $question->id }}"
                                      data-route-name="question-variants.destroy"
                                      data-route-params="@js(['questionVariant' => $variant->id])">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                                    <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">
                                        <span>Видалити</span>
                                    </button>
                                </form>
                                <button type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="
                                    window.highlightEditable($refs.display);
                                    editing = true;
                                    $nextTick(() => window.highlightEditable($refs.form));
                                ">
                                    <span>Редагувати</span>
                                </button>
                            </div>
                        </div>
                        <span>{!! $highlightSegments($variant->text) !!}</span>
                    </div>
                    <form x-show="editing" x-cloak x-ref="form" method="POST" action="{{ route('question-variants.update', $variant) }}" class="space-y-2"
                          data-queue-change="true"
                          data-change-type="variant.update"
                          data-change-summary="Оновити варіант питання"
                          data-question-id="{{ $question->id }}"
                          data-route-name="question-variants.update"
                          data-route-params="@js(['questionVariant' => $variant->id])">
                        @csrf
                        @method('put')
                        <input type="hidden" name="from" value="{{ $returnUrl }}">
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
            @empty
                <li class="rounded-lg border border-dashed border-stone-200 bg-white px-3 py-2 text-sm text-stone-500">Поки немає варіантів цього питання.</li>
            @endforelse
            <li x-data="{ adding: false }" class="rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-3">
                <div x-show="!adding" class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-emerald-700">Додати варіант</span>
                    <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="adding = true; $nextTick(() => window.highlightEditable($refs.form))">
                        <span>Створити</span>
                    </button>
                </div>
                <form x-show="adding" x-cloak x-ref="form" method="POST" action="{{ route('question-variants.store') }}" class="mt-3 space-y-2"
                      data-queue-change="true"
                      data-change-type="variant.create"
                      data-change-summary="Додати варіант питання"
                      data-question-id="{{ $question->id }}"
                      data-route-name="question-variants.store"
                      data-route-params="@js([])">
                    @csrf
                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст варіанту</label>
                        <textarea name="text" rows="3" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="adding = false">Скасувати</button>
                    </div>
                </form>
            </li>
        </ul>
    </details>

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
                        <div x-show="!editingAnswer" x-ref="display" class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs uppercase text-emerald-500">{{ $marker }}</span>
                            <span class="font-semibold text-emerald-900">{{ $answerValue }}</span>
                            <form method="POST"
                                  action="{{ route('question-answers.destroy', $answer) }}"
                                  data-confirm="Видалити цю відповідь?"
                                  data-queue-change="true"
                                  data-change-type="answer.delete"
                                  data-change-summary="Видалити правильну відповідь"
                                  data-question-id="{{ $question->id }}"
                                  data-route-name="question-answers.destroy"
                                  data-route-params="@js(['questionAnswer' => $answer->id])">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="from" value="{{ $returnUrl }}">
                                <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 bg-white px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                    <span>Видалити</span>
                                </button>
                            </form>
                            <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-200 bg-white px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100" @click="
                                window.highlightEditable($refs.display);
                                editingAnswer = true;
                                $nextTick(() => window.highlightEditable($refs.form));
                            ">
                                <span>Редагувати відповідь</span>
                            </button>
                        </div>
                        <form x-show="editingAnswer" x-cloak x-ref="form" method="POST" action="{{ route('question-answers.update', $answer) }}" class="space-y-2"
                              data-queue-change="true"
                              data-change-type="answer.update"
                              data-change-summary="Оновити правильну відповідь"
                              data-question-id="{{ $question->id }}"
                              data-route-name="question-answers.update"
                              data-route-params="@js(['questionAnswer' => $answer->id])">
                            @csrf
                            @method('put')
                            <input type="hidden" name="from" value="{{ $returnUrl }}">
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
                            <div x-show="!editingHint" x-ref="display" class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold uppercase text-[10px] tracking-wide text-emerald-600">Verb hint</span>
                                <span class="text-sm text-emerald-800">{{ $verbHintValue }}</span>
                                <form method="POST"
                                      action="{{ route('verb-hints.destroy', $verbHintModel) }}"
                                      data-confirm="Видалити цю підказку?"
                                      data-queue-change="true"
                                      data-change-type="verb-hint.delete"
                                      data-change-summary="Видалити verb hint"
                                      data-question-id="{{ $question->id }}"
                                      data-route-name="verb-hints.destroy"
                                      data-route-params="@js(['verbHint' => $verbHintModel->id])">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                                    <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Видалити</button>
                                </form>
                                <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-200 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="
                                    window.highlightEditable($refs.display);
                                    editingHint = true;
                                    $nextTick(() => window.highlightEditable($refs.form));
                                ">
                                    <span>Редагувати підказку</span>
                                </button>
                            </div>
                            <form x-show="editingHint" x-cloak x-ref="form" method="POST" action="{{ route('verb-hints.update', $verbHintModel) }}" class="space-y-2"
                                  data-queue-change="true"
                                  data-change-type="verb-hint.update"
                                  data-change-summary="Оновити verb hint"
                                  data-question-id="{{ $question->id }}"
                                  data-route-name="verb-hints.update"
                                  data-route-params="@js(['verbHint' => $verbHintModel->id])">
                                @csrf
                                @method('put')
                                <input type="hidden" name="from" value="{{ $returnUrl }}">
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
                    @else
                        <div x-data="{ addingHint: false }" class="mt-3 rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-2">
                            <div x-show="!addingHint" class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Verb hint</span>
                                <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="addingHint = true; $nextTick(() => window.highlightEditable($refs.form))">
                                    <span>Додати</span>
                                </button>
                            </div>
                            <form x-show="addingHint" x-cloak x-ref="form" method="POST" action="{{ route('verb-hints.store') }}" class="mt-2 space-y-2"
                                  data-queue-change="true"
                                  data-change-type="verb-hint.create"
                                  data-change-summary="Додати verb hint"
                                  data-question-id="{{ $question->id }}"
                                  data-route-name="verb-hints.store"
                                  data-route-params="@js([])">
                                @csrf
                                <input type="hidden" name="from" value="{{ $returnUrl }}">
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                <input type="hidden" name="marker" value="{{ strtolower($marker) }}">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Підказка</label>
                                    <input type="text" name="hint" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="addingHint = false">Скасувати</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
            <li x-data="{ addingAnswer: false }" class="rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-3">
                <div x-show="!addingAnswer" class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-emerald-700">Додати правильну відповідь</span>
                    <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="addingAnswer = true; $nextTick(() => window.highlightEditable($refs.form))">
                        <span>Створити</span>
                    </button>
                </div>
                <form x-show="addingAnswer" x-cloak x-ref="form" method="POST" action="{{ route('question-answers.store') }}" class="mt-3 space-y-2"
                      data-queue-change="true"
                      data-change-type="answer.create"
                      data-change-summary="Додати правильну відповідь"
                      data-question-id="{{ $question->id }}"
                      data-route-name="question-answers.store"
                      data-route-params="@js([])">
                    @csrf
                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <div class="grid gap-3 sm:grid-cols-5">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Маркер</label>
                            <input type="text" name="marker" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Відповідь</label>
                            <input type="text" name="value" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="addingAnswer = false">Скасувати</button>
                    </div>
                </form>
            </li>
        </ul>
    </details>

    <details class="group">
        <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
            <span>Варіанти відповіді</span>
            <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
            <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
        </summary>
        <div class="mt-3 flex flex-wrap gap-2">
            @forelse($options as $optionItem)
                <div x-data="{ editing: false }" class="inline-flex flex-col items-start gap-2">
                    <div x-show="!editing" x-ref="display" @class([
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
                            <button x-show="!editing" type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="
                                window.highlightEditable($refs.display);
                                editing = true;
                                $nextTick(() => window.highlightEditable($refs.form));
                            ">
                                <span>Редагувати</span>
                            </button>
                            @if(! $optionItem->is_correct)
                                <form method="POST"
                                      action="{{ route('question-options.destroy', $optionItem->model) }}"
                                      data-confirm="Видалити цей варіант відповіді?"
                                      data-queue-change="true"
                                      data-change-type="option.delete"
                                      data-change-summary="Видалити варіант відповіді"
                                      data-question-id="{{ $question->id }}"
                                      data-route-name="question-options.destroy"
                                      data-route-params="@js(['questionOption' => $optionItem->model->id])">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                                    <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Видалити</button>
                                </form>
                            @endif
                        </div>
                        <form x-show="editing" x-cloak x-ref="form" method="POST" action="{{ route('question-options.update', $optionItem->model) }}" class="space-y-2 rounded-xl border border-stone-200 bg-white px-3 py-2"
                              data-queue-change="true"
                              data-change-type="option.update"
                              data-change-summary="Оновити варіант відповіді"
                              data-question-id="{{ $question->id }}"
                              data-route-name="question-options.update"
                              data-route-params="@js(['questionOption' => $optionItem->model->id])">
                            @csrf
                            @method('put')
                            <input type="hidden" name="from" value="{{ $returnUrl }}">
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
            @empty
                <div class="rounded-lg border border-dashed border-stone-200 bg-white px-3 py-2 text-sm text-stone-500">Поки немає варіантів відповіді.</div>
            @endforelse
            <div x-data="{ addingOption: false }" class="flex w-full max-w-sm flex-col gap-2 rounded-lg border border-dashed border-emerald-200 bg-white p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-emerald-700">Додати варіант відповіді</span>
                    <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="addingOption = true; $nextTick(() => window.highlightEditable($refs.form))" x-show="!addingOption">
                        <span>Створити</span>
                    </button>
                </div>
                <form x-show="addingOption" x-cloak x-ref="form" method="POST" action="{{ route('question-options.store') }}" class="space-y-2"
                      data-queue-change="true"
                      data-change-type="option.create"
                      data-change-summary="Додати варіант відповіді"
                      data-question-id="{{ $question->id }}"
                      data-route-name="question-options.store"
                      data-route-params="@js([])">
                    @csrf
                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Значення</label>
                        <input type="text" name="option" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="addingOption = false">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    </details>

    <details class="group">
        <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
            <span>Question hints</span>
            <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
            <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
        </summary>
        <ul class="mt-3 space-y-3 text-sm text-stone-800">
            @forelse($questionHints as $hint)
                <li x-data="{ editing: false }" class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-3">
                    <div x-show="!editing" x-ref="display" class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                            <span>{{ $hint->provider }}</span>
                            <span>·</span>
                            <span>{{ strtoupper($hint->locale) }}</span>
                            <form method="POST"
                                  action="{{ route('question-hints.destroy', $hint) }}"
                                  data-confirm="Видалити цю підказку?"
                                  data-queue-change="true"
                                  data-change-type="question-hint.delete"
                                  data-change-summary="Видалити question hint"
                                  data-question-id="{{ $question->id }}"
                                  data-route-name="question-hints.destroy"
                                  data-route-params="@js(['questionHint' => $hint->id])">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="from" value="{{ $returnUrl }}">
                                <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50">Видалити</button>
                            </form>
                            <button type="button" class="inline-flex items-center gap-1 rounded border border-blue-200 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100" @click="
                                window.highlightEditable($refs.display);
                                editing = true;
                                $nextTick(() => window.highlightEditable($refs.form));
                            ">
                                <span>Редагувати</span>
                            </button>
                        </div>
                        <div class="whitespace-pre-line text-stone-800">{{ $hint->hint }}</div>
                    </div>
                    <form x-show="editing" x-cloak x-ref="form" method="POST" action="{{ route('question-hints.update', $hint) }}" class="space-y-3 rounded-xl border border-blue-200 bg-white px-3 py-3"
                          data-queue-change="true"
                          data-change-type="question-hint.update"
                          data-change-summary="Оновити question hint"
                          data-question-id="{{ $question->id }}"
                          data-route-name="question-hints.update"
                          data-route-params="@js(['questionHint' => $hint->id])">
                        @csrf
                        @method('put')
                        <input type="hidden" name="from" value="{{ $returnUrl }}">
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
            @empty
                <li class="rounded-lg border border-dashed border-blue-200 bg-white px-3 py-2 text-sm text-stone-500">Поки немає додаткових підказок.</li>
            @endforelse
            <li x-data="{ addingHint: false }" class="rounded-lg border border-dashed border-blue-200 bg-white px-3 py-3">
                <div x-show="!addingHint" class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-blue-700">Додати question hint</span>
                    <button type="button" class="inline-flex items-center gap-1 rounded border border-blue-300 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100" @click="addingHint = true; $nextTick(() => window.highlightEditable($refs.form))">
                        <span>Створити</span>
                    </button>
                </div>
                <form x-show="addingHint" x-cloak x-ref="form" method="POST" action="{{ route('question-hints.store') }}" class="mt-3 space-y-2"
                      data-queue-change="true"
                      data-change-type="question-hint.create"
                      data-change-summary="Додати question hint"
                      data-question-id="{{ $question->id }}"
                      data-route-name="question-hints.store"
                      data-route-params="@js([])">
                    @csrf
                    <input type="hidden" name="from" value="{{ $returnUrl }}">
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Провайдер</label>
                            <input type="text" name="provider" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                            <input type="text" name="locale" maxlength="5" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Підказка</label>
                        <textarea name="hint" rows="3" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="addingHint = false">Скасувати</button>
                    </div>
                </form>
            </li>
        </ul>
    </details>

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
                    @forelse($explanations as $explanation)
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
                            <td x-show="!editing" x-ref="display" class="py-2">
                            <div class="flex items-start justify-between gap-3">
                                    <span class="flex-1">{{ $explanation->explanation }}</span>
                                    <div class="flex items-center gap-2">
                                        <form method="POST"
                                              action="{{ route('chatgpt-explanations.destroy', $explanation) }}"
                                              data-confirm="Видалити це пояснення?"
                                              data-queue-change="true"
                                              data-change-type="explanation.delete"
                                              data-change-summary="Видалити пояснення"
                                              data-question-id="{{ $question->id }}"
                                              data-route-name="chatgpt-explanations.destroy"
                                              data-route-params="@js(['chatgptExplanation' => $explanation->id])">
                                            @csrf
                                            @method('delete')
                                            <input type="hidden" name="from" value="{{ $returnUrl }}">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Видалити</button>
                                        </form>
                                        <button type="button" class="inline-flex items-center gap-1 rounded border border-stone-300 px-2 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100" @click="
                                            window.highlightEditable($refs.display);
                                            editing = true;
                                            $nextTick(() => window.highlightEditable($refs.form));
                                        ">
                                            <span>Редагувати</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td x-show="editing" x-cloak colspan="4" class="py-3">
                                <form x-ref="form" method="POST" action="{{ route('chatgpt-explanations.update', $explanation) }}" class="space-y-3 rounded-2xl border border-stone-200 bg-white px-4 py-3"
                                      data-queue-change="true"
                                      data-change-type="explanation.update"
                                      data-change-summary="Оновити пояснення"
                                      data-question-id="{{ $question->id }}"
                                      data-route-name="chatgpt-explanations.update"
                                      data-route-params="@js(['chatgptExplanation' => $explanation->id])">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="from" value="{{ $returnUrl }}">
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
                    @empty
                        <tr class="border-t border-dashed border-stone-200">
                            <td colspan="4" class="py-3 text-sm text-stone-500">Пояснень від ChatGPT ще немає.</td>
                        </tr>
                    @endforelse
                    <tr x-data="{ addingExplanation: false }" class="border-t border-dashed border-stone-200">
                        <td colspan="4" class="py-3">
                            <div x-show="!addingExplanation" class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-emerald-700">Додати пояснення</span>
                                <button type="button" class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50" @click="addingExplanation = true; $nextTick(() => window.highlightEditable($refs.form))">
                                    <span>Створити</span>
                                </button>
                            </div>
                            <form x-show="addingExplanation" x-cloak x-ref="form" method="POST" action="{{ route('chatgpt-explanations.store') }}" class="mt-3 space-y-3 rounded-2xl border border-stone-200 bg-white px-4 py-3"
                                  data-queue-change="true"
                                  data-change-type="explanation.create"
                                  data-change-summary="Додати пояснення"
                                  data-question-id="{{ $question->id }}"
                                  data-route-name="chatgpt-explanations.store"
                                  data-route-params="@js([])">
                                @csrf
                                <input type="hidden" name="from" value="{{ $returnUrl }}">
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Мова</label>
                                        <input type="text" name="language" maxlength="10" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Неправильна відповідь</label>
                                        <input type="text" name="wrong_answer" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Правильна відповідь</label>
                                        <input type="text" name="correct_answer" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст питання</label>
                                        <textarea name="question" rows="2" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ $question->question }}</textarea>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Пояснення</label>
                                    <textarea name="explanation" rows="3" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                                </div>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100" @click="addingExplanation = false">Скасувати</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>
</article>
