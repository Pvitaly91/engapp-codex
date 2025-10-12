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
                    @if($executedSeederHierarchy->isEmpty())
                        <p class="text-sm text-gray-500">Поки що немає виконаних сидерів.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($executedSeederHierarchy as $node)
                                @include('seed-runs.partials.executed-node', ['node' => $node, 'depth' => 0])
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
