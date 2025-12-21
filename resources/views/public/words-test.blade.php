@extends('layouts.engram')

@section('title', __('Тест слів'))

@section('content')
<div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
<div class="min-h-screen" id="words-test-app">
    <div class="max-w-3xl -mx-3 sm:mx-auto px-0 sm:px-5 md:px-6 lg:px-8 py-6 sm:py-10">
        <!-- Header Section -->
        <header class="mb-6 sm:mb-12">
            <div class="text-center space-y-3 sm:space-y-4">
                <div class="inline-flex items-center px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-3 sm:mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Словниковий тест
                </div>
                <h1 class="text-[28px] sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Тест слів
                </h1>
                <p class="text-[15px] sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Перевірте свої знання англійських слів. Оберіть правильний переклад.
                </p>
            </div>
        </header>

        <!-- Progress Section -->
        <div class="sticky top-0 z-30 mb-6" id="progress-section">
            <div class="space-y-2.5 sm:space-y-4 rounded-2xl border border-indigo-100 bg-white/90 p-2.5 sm:p-5 lg:p-6 shadow backdrop-blur-md transition-all duration-300">
                <div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 rounded-2xl border border-indigo-100 p-2.5 sm:p-4 shadow-inner">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-3">
                        <div class="flex items-center space-x-2.5 sm:space-x-3">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center" aria-hidden="true">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Прогрес</div>
                                <div id="progress-label" class="text-base sm:text-xl font-bold text-gray-900" aria-live="polite">0 / 0</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Правильно</div>
                                <div id="correct-label" class="text-base sm:text-xl font-bold text-emerald-600" aria-live="polite">0</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Точність</div>
                                <div id="score-label" class="text-base sm:text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent" aria-live="polite">0%</div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full h-2.5 sm:h-3 bg-white border border-indigo-100 rounded-full overflow-hidden shadow-sm mt-3" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar-container">
                        <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="rounded-3xl bg-white shadow-xl border border-gray-100 p-4 sm:p-6 lg:p-8 space-y-5 sm:space-y-6">
            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12">
                <svg class="h-12 w-12 animate-spin text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <p class="text-gray-600">Завантаження тесту...</p>
            </div>

            <!-- Question Container -->
            <div id="question-container" class="hidden">
                <article id="question-card" class="group bg-white rounded-3xl p-4 sm:p-6 lg:p-8 transition-all duration-300 focus-within:ring-4 ring-indigo-100 outline-none" tabindex="0" aria-labelledby="question-title">
                    <!-- Question content will be inserted here -->
                </article>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-12">
                <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 text-lg">Немає доступних слів для тесту.</p>
                <p class="text-gray-400 mt-2">Спробуйте пізніше або зверніться до адміністратора.</p>
            </div>

            <!-- Summary Section -->
            <div id="summary" class="hidden">
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-6 sm:p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center" aria-hidden="true">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Тест завершено! 🎉</h2>
                    <p id="summary-text" class="text-xl text-gray-700 mb-8" aria-live="polite"></p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button id="restart-btn" type="button" class="group px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Почати знову
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Loading Overlay -->
<div id="ajax-loader" class="hidden fixed inset-0 bg-gradient-to-br from-indigo-900/20 to-purple-900/20 backdrop-blur-sm flex items-center justify-center z-50" aria-hidden="true">
    <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center">
        <svg class="h-12 w-12 animate-spin text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="text-gray-700 font-medium">Завантаження...</p>
    </div>
</div>

<script>
(function() {
    'use strict';

    const STORAGE_KEY = 'words_test_state';
    const FETCH_URL = '{{ route("public.words.test.fetch") }}';
    const CHECK_URL = '{{ route("public.words.test.check") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    const state = {
        questions: [],
        currentIndex: 0,
        correct: 0,
        wrong: 0,
        answered: [],
        isLoading: true,
    };

    // DOM Elements
    const loadingEl = document.getElementById('loading-state');
    const questionContainerEl = document.getElementById('question-container');
    const questionCardEl = document.getElementById('question-card');
    const emptyStateEl = document.getElementById('empty-state');
    const summaryEl = document.getElementById('summary');
    const summaryTextEl = document.getElementById('summary-text');
    const progressLabelEl = document.getElementById('progress-label');
    const correctLabelEl = document.getElementById('correct-label');
    const scoreLabelEl = document.getElementById('score-label');
    const progressBarEl = document.getElementById('progress-bar');
    const progressBarContainerEl = document.getElementById('progress-bar-container');
    const restartBtnEl = document.getElementById('restart-btn');
    const ajaxLoaderEl = document.getElementById('ajax-loader');

    // Utility functions
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function shuffleArray(arr) {
        const shuffled = [...arr];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    function showLoader(show) {
        ajaxLoaderEl.classList.toggle('hidden', !show);
        ajaxLoaderEl.setAttribute('aria-hidden', !show);
    }

    // State persistence using localStorage
    function saveState() {
        const stateToSave = {
            questions: state.questions,
            currentIndex: state.currentIndex,
            correct: state.correct,
            wrong: state.wrong,
            answered: state.answered,
            timestamp: Date.now(),
        };
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(stateToSave));
        } catch (e) {
            console.error('Failed to save state to localStorage:', e);
        }
    }

    function loadState() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved) return null;

            const parsed = JSON.parse(saved);
            // Check if state is not too old (24 hours)
            if (parsed.timestamp && Date.now() - parsed.timestamp > 24 * 60 * 60 * 1000) {
                clearState();
                return null;
            }
            return parsed;
        } catch (e) {
            console.error('Failed to load state from localStorage:', e);
            return null;
        }
    }

    function clearState() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {
            console.error('Failed to clear state from localStorage:', e);
        }
    }

    // Progress update
    function updateProgress() {
        const total = state.questions.length;
        const answered = state.answered.length;
        const percent = total > 0 ? Math.round((state.correct / total) * 100) : 0;

        progressLabelEl.textContent = `${answered} / ${total}`;
        correctLabelEl.textContent = state.correct;
        scoreLabelEl.textContent = `${percent}%`;

        const progressPercent = total > 0 ? (answered / total) * 100 : 0;
        progressBarEl.style.width = `${progressPercent}%`;
        progressBarContainerEl.setAttribute('aria-valuenow', Math.round(progressPercent));
    }

    // Render question
    function renderQuestion() {
        if (state.questions.length === 0) {
            showEmptyState();
            return;
        }

        if (state.currentIndex >= state.questions.length) {
            showSummary();
            return;
        }

        const q = state.questions[state.currentIndex];
        const isEnToUk = q.questionType === 'en_to_uk';
        const questionText = isEnToUk ? q.word : q.translation;
        const promptText = isEnToUk
            ? 'Оберіть правильний <b>український переклад</b> для:'
            : 'Оберіть правильне <b>англійське слово</b> для:';

        const tagsHtml = q.tags && q.tags.length > 0
            ? `<div class="flex flex-wrap gap-1.5 mb-3">
                ${q.tags.map(tag => `<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">${escapeHtml(tag)}</span>`).join('')}
               </div>`
            : '';

        const optionsHtml = q.options.map((opt, i) => {
            const isAnswered = state.answered.includes(state.currentIndex);
            const isCorrect = opt === q.correctAnswer;
            const wasChosen = q.userAnswer === opt;

            let btnClass = 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transform hover:-translate-y-0.5';
            let iconColor = 'text-gray-400 group-hover:text-indigo-500';

            if (isAnswered) {
                if (isCorrect) {
                    btnClass = 'border-emerald-300 bg-emerald-50 text-emerald-700';
                    iconColor = 'text-emerald-500';
                } else if (wasChosen) {
                    btnClass = 'border-red-300 bg-red-50 text-red-700';
                    iconColor = 'text-red-500';
                } else {
                    btnClass = 'border-gray-200 bg-gray-50 text-gray-500';
                    iconColor = 'text-gray-300';
                }
            }

            return `
                <button type="button"
                    class="option-btn group relative w-full text-left px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border-2 transition-all duration-200 font-medium text-[15px] sm:text-base ${btnClass}"
                    data-option="${escapeHtml(opt)}"
                    title="Натисніть ${i + 1}"
                    ${isAnswered ? 'disabled' : ''}
                    aria-pressed="${wasChosen ? 'true' : 'false'}">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-xl border-2 font-bold text-sm ${iconColor} transition-colors" aria-hidden="true">
                            ${i + 1}
                        </span>
                        <span class="flex-1">${escapeHtml(opt)}</span>
                        ${isAnswered && isCorrect ? '<svg class="w-5 h-5 text-emerald-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : ''}
                        ${isAnswered && wasChosen && !isCorrect ? '<svg class="w-5 h-5 text-red-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' : ''}
                    </div>
                </button>
            `;
        }).join('');

        const feedbackHtml = q.feedback ? `
            <div class="mt-4 ${q.feedback.isCorrect ? 'bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-200' : 'bg-gradient-to-r from-red-50 to-rose-50 border-red-200'} rounded-2xl border-2 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full ${q.feedback.isCorrect ? 'bg-emerald-500' : 'bg-red-500'} flex items-center justify-center" aria-hidden="true">
                        ${q.feedback.isCorrect
                            ? '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                            : '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
                        }
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold ${q.feedback.isCorrect ? 'text-emerald-800' : 'text-red-800'}">
                            ${q.feedback.isCorrect ? 'Правильно!' : 'Неправильно'}
                        </div>
                        ${!q.feedback.isCorrect ? `<div class="text-sm ${q.feedback.isCorrect ? 'text-emerald-700' : 'text-red-700'} mt-1">Правильна відповідь: <b>${escapeHtml(q.correctAnswer)}</b></div>` : ''}
                    </div>
                </div>
            </div>
        ` : '';

        const nextBtnHtml = state.answered.includes(state.currentIndex) ? `
            <div class="mt-6 flex justify-end">
                <button type="button" id="next-btn" class="group px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-indigo-300">
                    <span class="flex items-center">
                        ${state.currentIndex < state.questions.length - 1 ? 'Далі' : 'Завершити'}
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </button>
            </div>
        ` : '';

        questionCardEl.innerHTML = `
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3.5 sm:gap-4 mb-5 sm:mb-6">
                <div class="flex-1">
                    ${tagsHtml}
                    <p class="text-base sm:text-lg text-gray-600 mb-2">${promptText}</p>
                    <h2 id="question-title" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">${escapeHtml(questionText)}</h2>
                </div>
                <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0 sm:self-start" aria-hidden="true">
                    <div class="text-xs text-gray-500 font-medium">Q</div>
                    <div class="text-lg font-bold text-indigo-600">${state.currentIndex + 1}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3" role="group" aria-label="Варіанти відповідей">
                ${optionsHtml}
            </div>
            ${feedbackHtml}
            ${nextBtnHtml}
        `;

        // Attach event listeners to option buttons
        questionCardEl.querySelectorAll('.option-btn').forEach(btn => {
            btn.addEventListener('click', handleOptionClick);
        });

        // Attach event listener to next button
        const nextBtn = questionCardEl.querySelector('#next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', handleNextClick);
        }

        // Focus on question card for accessibility
        questionCardEl.focus();
    }

    // Handle option click
    async function handleOptionClick(e) {
        const btn = e.currentTarget;
        const option = btn.dataset.option;

        if (state.answered.includes(state.currentIndex)) return;

        const q = state.questions[state.currentIndex];
        showLoader(true);

        try {
            const response = await fetch(CHECK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    word_id: q.id,
                    answer: option,
                    questionType: q.questionType,
                }),
            });

            const result = await response.json();

            q.userAnswer = option;
            q.feedback = result;

            state.answered.push(state.currentIndex);

            if (result.isCorrect) {
                state.correct++;
            } else {
                state.wrong++;
            }

            saveState();
            updateProgress();
            renderQuestion();
        } catch (error) {
            console.error('Error checking answer:', error);
        } finally {
            showLoader(false);
        }
    }

    // Handle next button click
    function handleNextClick() {
        state.currentIndex++;
        saveState();
        updateProgress();
        renderQuestion();
    }

    // Show empty state
    function showEmptyState() {
        loadingEl.classList.add('hidden');
        questionContainerEl.classList.add('hidden');
        summaryEl.classList.add('hidden');
        emptyStateEl.classList.remove('hidden');
    }

    // Show summary
    function showSummary() {
        loadingEl.classList.add('hidden');
        questionContainerEl.classList.add('hidden');
        emptyStateEl.classList.add('hidden');
        summaryEl.classList.remove('hidden');

        const total = state.questions.length;
        const percent = total > 0 ? Math.round((state.correct / total) * 100) : 0;
        summaryTextEl.textContent = `Ви відповіли правильно на ${state.correct} з ${total} питань (${percent}%)!`;

        clearState();
    }

    // Show question UI
    function showQuestion() {
        loadingEl.classList.add('hidden');
        emptyStateEl.classList.add('hidden');
        summaryEl.classList.add('hidden');
        questionContainerEl.classList.remove('hidden');
        renderQuestion();
    }

    // Restart test
    async function restartTest() {
        clearState();
        showLoader(true);

        state.questions = [];
        state.currentIndex = 0;
        state.correct = 0;
        state.wrong = 0;
        state.answered = [];

        await fetchQuestions();
        showLoader(false);
    }

    // Fetch questions from server
    async function fetchQuestions() {
        try {
            const response = await fetch(FETCH_URL, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.questions && data.questions.length > 0) {
                state.questions = shuffleArray(data.questions);
                state.currentIndex = 0;
                state.correct = 0;
                state.wrong = 0;
                state.answered = [];
                saveState();
                updateProgress();
                showQuestion();
            } else {
                showEmptyState();
            }
        } catch (error) {
            console.error('Error fetching questions:', error);
            showEmptyState();
        }
    }

    // Initialize
    async function init() {
        // Try to restore saved state
        const savedState = loadState();

        if (savedState && savedState.questions && savedState.questions.length > 0) {
            state.questions = savedState.questions;
            state.currentIndex = savedState.currentIndex || 0;
            state.correct = savedState.correct || 0;
            state.wrong = savedState.wrong || 0;
            state.answered = savedState.answered || [];
            updateProgress();
            showQuestion();
        } else {
            await fetchQuestions();
        }

        state.isLoading = false;
    }

    // Event listeners
    restartBtnEl.addEventListener('click', restartTest);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (state.isLoading || state.answered.includes(state.currentIndex)) return;

        const n = parseInt(e.key, 10);
        if (n >= 1 && n <= 4) {
            const q = state.questions[state.currentIndex];
            if (q && q.options[n - 1]) {
                // Use querySelectorAll and filter to find the correct button (avoids CSS injection)
                const buttons = questionCardEl.querySelectorAll('.option-btn');
                const targetOption = q.options[n - 1];
                const btn = Array.from(buttons).find(b => b.dataset.option === targetOption);
                if (btn && !btn.disabled) {
                    btn.click();
                }
            }
        }
    });

    // Start the app
    init();
})();
</script>
@endsection
