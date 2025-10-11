@extends('layouts.app')

@section('title', 'Seed Runs')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Seed Runs</h1>
                    <p class="text-sm text-gray-500">Керуйте виконаними та невиконаними сидарами.</p>
                </div>
                @if($tableExists)
                    <form method="POST" action="{{ route('seed-runs.run-missing') }}" data-preloader>
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-500 transition disabled:opacity-50" @if($pendingSeeders->isEmpty()) disabled @endif>
                            <i class="fa-solid fa-play"></i>
                            Виконати всі невиконані
                        </button>
                    </form>
                @endif
            </div>

            @if(session('status'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="seed-run-ajax-feedback" class="hidden mb-4 rounded-md border px-4 py-3 text-sm"></div>

            @unless($tableExists)
                <div class="rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800">
                    Таблиця <code class="font-mono">seed_runs</code> ще не створена. Запустіть міграції, щоб продовжити.
                </div>
            @endunless
        </div>

        @if($tableExists)
            <div >
                <div class="bg-white shadow rounded-lg p-6 my-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Невиконані сидери</h2>
                    @if($pendingSeeders->isEmpty())
                        <p class="text-sm text-gray-500">Усі сидери вже виконані.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($pendingSeeders as $pendingSeeder)
                                <li class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-mono text-gray-700 break-all">{{ $pendingSeeder->display_class_name }}</span>
                                    <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader>
                                        @csrf
                                        <input type="hidden" name="class_name" value="{{ $pendingSeeder->class_name }}">
                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                                            <i class="fa-solid fa-play"></i>
                                            Виконати
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white shadow rounded-lg p-6 overflow-hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Виконані сидери</h2>
                    @if($executedSeeders->isEmpty())
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($executedSeeders as $seedRun)
                                <div class="border border-gray-200 rounded-xl shadow-sm">
                                    <div class="p-4 md:p-6">
                                        <div class="md:grid md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)] md:items-start md:gap-6">
                                            <div class="text-xs text-gray-700 break-words">
                                                <div class="font-mono text-sm text-gray-800">{{ $seedRun->display_class_name }}</div>

                                                <p class="text-xs text-gray-500 mt-2 {{ $seedRun->question_count > 0 ? 'hidden' : '' }}" data-no-questions-message data-seed-run-id="{{ $seedRun->id }}">
                                                    Питання відсутні.
                                                </p>

                                                @if($seedRun->question_count > 0)
                                                    <div x-data="{ open: false }" class="mt-3 space-y-3" data-seed-run-question-wrapper data-seed-run-id="{{ $seedRun->id }}">
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                                                @click="open = !open">
                                                            <span x-show="!open" x-cloak>
                                                                Показати питання (
                                                                <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $seedRun->question_count }}</span>
                                                                )
                                                            </span>
                                                            <span x-show="open" x-cloak>
                                                                Сховати питання (
                                                                <span class="font-semibold" data-seed-run-question-count data-seed-run-id="{{ $seedRun->id }}">{{ $seedRun->question_count }}</span>
                                                                )
                                                            </span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>

                                                        <div x-show="open" x-transition style="display: none;" class="space-y-4">
                                                            @foreach($seedRun->question_groups as $categoryIndex => $categoryGroup)
                                                                <div x-data="{ openCategory: true }" class="border border-slate-200 rounded-xl overflow-hidden" data-category-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}">
                                                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-slate-100">
                                                                        <div class="space-y-1">
                                                                            <h3 class="text-sm font-semibold text-gray-800">{{ $categoryGroup['display_name'] }}</h3>
                                                                            <p class="text-xs text-gray-500">
                                                                                @if($categoryGroup['category'])
                                                                                    Категорія ID: {{ $categoryGroup['category']['id'] }}
                                                                                @else
                                                                                    Категорія не вказана
                                                                                @endif
                                                                                · Питань:
                                                                                <span class="font-semibold" data-category-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}">
                                                                                    {{ $categoryGroup['question_count'] }}
                                                                                </span>
                                                                            </p>
                                                                        </div>
                                                                        <button type="button"
                                                                                class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-100 hover:bg-blue-200 transition w-full sm:w-auto"
                                                                                @click="openCategory = !openCategory">
                                                                            <span x-text="openCategory ? 'Згорнути' : 'Розгорнути'"></span>
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openCategory }" viewBox="0 0 20 20" fill="currentColor">
                                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                    <div x-show="openCategory" x-transition style="display: none;" class="space-y-3 px-4 pb-4 pt-3 bg-white">
                                                                        @foreach($categoryGroup['sources'] as $sourceIndex => $sourceGroup)
                                                                            <div x-data="{ openSource: true }" class="border border-slate-200 rounded-lg overflow-hidden" data-source-wrapper data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 py-2 bg-slate-50">
                                                                                    <div class="space-y-1">
                                                                                        <h4 class="text-sm font-semibold text-slate-700">{{ $sourceGroup['display_name'] }}</h4>
                                                                                        <p class="text-xs text-slate-500">
                                                                                            @if($sourceGroup['source'])
                                                                                                Джерело ID: {{ $sourceGroup['source']['id'] }}
                                                                                            @else
                                                                                                Джерело не вказане
                                                                                            @endif
                                                                                            · Питань:
                                                                                            <span class="font-semibold" data-source-question-count data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                                                                {{ $sourceGroup['questions']->count() }}
                                                                                            </span>
                                                                                        </p>
                                                                                    </div>
                                                                                    <button type="button"
                                                                                            class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-700 px-3 py-1.5 rounded-full bg-blue-50 hover:bg-blue-100 transition w-full sm:w-auto"
                                                                                            @click="openSource = !openSource">
                                                                                        <span x-text="openSource ? 'Сховати' : 'Показати'"></span>
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openSource }" viewBox="0 0 20 20" fill="currentColor">
                                                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                                <div x-show="openSource" x-transition style="display: none;" class="space-y-2 px-3 pb-3 pt-2 bg-white">
                                                                                    @foreach($sourceGroup['questions'] as $question)
                                                                                        <div class="border border-slate-200 rounded-lg bg-slate-50 px-3 py-2 text-left text-sm leading-relaxed" data-question-container data-question-id="{{ $question['id'] }}" data-seed-run-id="{{ $seedRun->id }}" data-category-index="{{ $categoryIndex }}" data-source-index="{{ $sourceIndex }}">
                                                                                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                                                                                <div class="text-gray-800 space-y-1">{!! $question['highlighted_text'] !!}</div>
                                                                                                <form method="POST"
                                                                                                      action="{{ route('seed-runs.questions.destroy', $question['id']) }}"
                                                                                                      data-question-delete-form
                                                                                                      data-confirm="Видалити це питання?"
                                                                                                      data-question-id="{{ $question['id'] }}"
                                                                                                      data-seed-run-id="{{ $seedRun->id }}"
                                                                                                      data-category-index="{{ $categoryIndex }}"
                                                                                                      data-source-index="{{ $sourceIndex }}">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-red-700 px-2.5 py-1 rounded-full bg-red-50 hover:bg-red-100 transition w-full sm:w-auto">
                                                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                                                        Видалити
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mt-4 md:mt-0 text-sm text-gray-600 md:text-right">
                                                <span class="font-semibold text-gray-700 block md:hidden">Виконано:</span>
                                                <span>{{ optional($seedRun->ran_at)->format('Y-m-d H:i:s') }}</span>
                                            </div>

                                            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row md:flex-col md:items-end gap-2">
                                                <form method="POST" action="{{ route('seed-runs.destroy-with-questions', $seedRun->id) }}" data-preloader data-confirm="Видалити лог та пов’язані питання?" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-md hover:bg-amber-500 transition">
                                                        <i class="fa-solid fa-broom"></i>
                                                        Видалити з питаннями
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seed-runs.destroy', $seedRun->id) }}" data-preloader data-confirm="Видалити лише запис про виконання?" class="flex-1 sm:flex-none md:flex-1 md:w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Видалити запис
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div id="seed-run-preloader" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-3 text-sm text-gray-700">
            <span class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
            <span>Виконується операція…</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preloader = document.getElementById('seed-run-preloader');
            const feedback = document.getElementById('seed-run-ajax-feedback');
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            const successClasses = ['bg-emerald-50', 'border-emerald-200', 'text-emerald-700'];
            const errorClasses = ['bg-red-50', 'border-red-200', 'text-red-700'];
            let feedbackTimeout;

            document.querySelectorAll('form[data-preloader]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmMessage = form.dataset.confirm;

                    if (confirmMessage && !window.confirm(confirmMessage)) {
                        event.preventDefault();

                        return;
                    }

                    if (preloader) {
                        preloader.classList.remove('hidden');
                    }
                });
            });

            const decrementNumericContent = function (elements) {
                elements.forEach(function (element) {
                    const current = parseInt(element.textContent.trim(), 10);

                    if (Number.isNaN(current)) {
                        return;
                    }

                    const nextValue = Math.max(0, current - 1);
                    element.textContent = nextValue;
                });
            };

            const cleanupEmptyGroups = function (seedRunId, categoryIndex, sourceIndex) {
                const sourceWrappers = document.querySelectorAll('[data-source-wrapper][data-seed-run-id="' + seedRunId + '"][data-category-index="' + categoryIndex + '"][data-source-index="' + sourceIndex + '"]');
                sourceWrappers.forEach(function (wrapper) {
                    if (!wrapper.querySelector('[data-question-container]')) {
                        wrapper.remove();
                    }
                });

                const categoryWrappers = document.querySelectorAll('[data-category-wrapper][data-seed-run-id="' + seedRunId + '"][data-category-index="' + categoryIndex + '"]');
                categoryWrappers.forEach(function (wrapper) {
                    if (!wrapper.querySelector('[data-question-container]')) {
                        wrapper.remove();
                    }
                });

                const seedRunWrapper = document.querySelector('[data-seed-run-question-wrapper][data-seed-run-id="' + seedRunId + '"]');
                const noQuestionsMessage = document.querySelector('[data-no-questions-message][data-seed-run-id="' + seedRunId + '"]');

                if (seedRunWrapper && !seedRunWrapper.querySelector('[data-question-container]')) {
                    seedRunWrapper.remove();

                    if (noQuestionsMessage) {
                        noQuestionsMessage.classList.remove('hidden');
                    }
                }
            };

            const showFeedback = function (message, type = 'success') {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('hidden');
                feedback.classList.remove(...successClasses, ...errorClasses);

                const classes = type === 'error' ? errorClasses : successClasses;
                classes.forEach(function (className) {
                    feedback.classList.add(className);
                });

                window.clearTimeout(feedbackTimeout);
                feedbackTimeout = window.setTimeout(function () {
                    feedback.classList.add('hidden');
                }, 5000);
            };

            document.querySelectorAll('form[data-question-delete-form]').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const confirmMessage = form.dataset.confirm;

                    if (confirmMessage && !window.confirm(confirmMessage)) {
                        return;
                    }

                    const questionId = form.dataset.questionId;
                    const seedRunId = form.dataset.seedRunId;
                    const categoryIndex = form.dataset.categoryIndex;
                    const sourceIndex = form.dataset.sourceIndex;
                    const submitButton = form.querySelector('button[type="submit"]');

                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.classList.add('opacity-60', 'cursor-not-allowed');
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        const payload = await response.json().catch(function () {
                            return null;
                        });

                        if (!response.ok) {
                            const errorMessage = payload && typeof payload.message === 'string'
                                ? payload.message
                                : 'Не вдалося видалити питання.';
                            throw new Error(errorMessage);
                        }

                        const questionContainer = document.querySelector('[data-question-container][data-question-id="' + questionId + '"]');

                        if (questionContainer) {
                            questionContainer.remove();
                        }

                        decrementNumericContent(document.querySelectorAll('[data-seed-run-question-count][data-seed-run-id="' + seedRunId + '"]'));
                        decrementNumericContent(document.querySelectorAll('[data-category-question-count][data-seed-run-id="' + seedRunId + '"][data-category-index="' + categoryIndex + '"]'));
                        decrementNumericContent(document.querySelectorAll('[data-source-question-count][data-seed-run-id="' + seedRunId + '"][data-category-index="' + categoryIndex + '"][data-source-index="' + sourceIndex + '"]'));

                        cleanupEmptyGroups(seedRunId, categoryIndex, sourceIndex);

                        const successMessage = payload && typeof payload.message === 'string'
                            ? payload.message
                            : 'Питання успішно видалено.';

                        showFeedback(successMessage);
                    } catch (error) {
                        const fallbackErrorMessage = error && typeof error.message === 'string' && error.message
                            ? error.message
                            : 'Не вдалося видалити питання.';

                        showFeedback(fallbackErrorMessage, 'error');

                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
                        }
                    }
                });
            });
        });
    </script>
@endsection
