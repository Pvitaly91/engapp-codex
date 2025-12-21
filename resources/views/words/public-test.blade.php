@extends('layouts.engram')

@section('title', 'Тест слів')

@section('content')
<div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
<div class="min-h-screen" id="words-test-app">
    <div class="max-w-3xl -mx-3 sm:mx-auto px-0 sm:px-5 md:px-6 lg:px-8 py-6 sm:py-10">
        <!-- Header Section with Modern Design -->
        <header class="mb-6 sm:mb-12">
            <div class="text-center space-y-3 sm:space-y-4">
                <div class="inline-flex items-center px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 text-sm font-semibold mb-3 sm:mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Словниковий тест
                </div>
                <h1 class="text-[28px] sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Вивчення слів
                </h1>
                <p class="text-[15px] sm:text-lg text-gray-600 max-w-2xl mx-auto">
                    Обирай правильний переклад слова з варіантів нижче
                </p>
            </div>
        </header>

        <!-- Progress Section -->
        <div class="sticky top-0 z-30 max-h-[40vh] md:max-h-none mb-6">
            <div class="space-y-2.5 sm:space-y-4 rounded-2xl border border-indigo-100 bg-white/90 p-2.5 sm:p-5 lg:p-6 shadow backdrop-blur-md">
                <div class="bg-gradient-to-r from-indigo-50 via-white to-purple-50 rounded-2xl border border-indigo-100 p-2.5 sm:p-4 shadow-inner">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 mb-2 sm:mb-3">
                        <div class="flex items-center space-x-2.5 sm:space-x-3">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Прогрес</div>
                                <div id="progress-label" class="text-base sm:text-xl font-bold text-gray-900">0 / 0</div>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="text-center">
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Правильно</div>
                                <div id="correct-label" class="text-base sm:text-xl font-bold text-emerald-600">0</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Неправильно</div>
                                <div id="wrong-label" class="text-base sm:text-xl font-bold text-red-600">0</div>
                            </div>
                            <div class="text-right space-y-0.5">
                                <div class="text-[11px] sm:text-xs font-semibold uppercase tracking-wide text-gray-500">Відсоток</div>
                                <div id="score-label" class="text-base sm:text-xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">0%</div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full h-2.5 sm:h-3 bg-white border border-indigo-100 rounded-full overflow-hidden shadow-sm">
                        <div id="progress-bar" class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <main class="rounded-3xl bg-white shadow-xl border border-gray-100 p-4 sm:p-6 lg:p-8 space-y-5 sm:space-y-6">
            <!-- Restart Button -->
            <div class="flex justify-end">
                <button id="restart-test" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border-2 border-gray-200 bg-white text-gray-700 font-semibold hover:border-red-300 hover:bg-red-50 hover:text-red-700 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Почати знову
                </button>
            </div>

            <!-- Question Card -->
            <div id="question-card" class="hidden">
                <article class="group bg-white rounded-3xl shadow-md hover:shadow-xl border-2 border-gray-100 hover:border-indigo-200 p-4 sm:p-6 lg:p-8 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3.5 sm:gap-4 mb-5 sm:mb-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2.5 sm:gap-3 mb-2.5 sm:mb-3">
                                <span id="question-direction" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-100 to-indigo-100 text-indigo-700"></span>
                            </div>
                            <div id="question-word" class="text-2xl sm:text-3xl lg:text-4xl leading-relaxed text-gray-900 font-bold mb-2.5 sm:mb-3"></div>
                        </div>
                        <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 shrink-0 sm:self-start">
                            <div class="text-xs text-gray-500 font-medium">№</div>
                            <div id="question-number" class="text-lg font-bold text-indigo-600">1</div>
                        </div>
                    </div>

                    <div id="options-container" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3" role="group" aria-label="Варіанти відповідей"></div>
                </article>
            </div>

            <!-- Feedback Section -->
            <div id="feedback-section" class="hidden"></div>

            <!-- Summary Section with Celebration Design -->
            <div id="summary" class="hidden">
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl shadow-xl border-2 border-emerald-200 p-6 sm:p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Тест завершено! 🎉</h2>
                    <p id="summary-text" class="text-xl text-gray-700 mb-8"></p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button id="retry" class="group px-6 py-3.5 sm:px-8 sm:py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Спробувати знову
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- No Words Message -->
            <div id="no-words" class="hidden">
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-3xl shadow-xl border-2 border-amber-200 p-6 sm:p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Слів не знайдено</h2>
                    <p class="text-lg text-gray-700">Немає слів з українським перекладом для тестування.</p>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Loading Overlay -->
<div id="ajax-loader" class="hidden fixed inset-0 bg-gradient-to-br from-indigo-900/20 to-purple-900/20 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center">
        <svg class="h-12 w-12 animate-spin text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="text-gray-700 font-medium">Завантаження...</p>
    </div>
</div>

<script>
const WORDS_DATA = @json($wordData);
const CSRF_TOKEN = '{{ csrf_token() }}';
const CHECK_URL = '{{ route('public-words.test.check') }}';
const STATE_URL = '{{ route('public-words.test.state') }}';
const RESET_URL = '{{ route('public-words.test.reset') }}';
const SAVED_STATE = @json($savedState);

const state = {
    words: [],
    queue: [],
    current: null,
    currentOptions: [],
    questionType: 'en_to_uk',
    stats: {
        correct: 0,
        wrong: 0,
        total: 0,
    },
    answered: new Set(),
    totalCount: 0,
};

// DOM Elements
const questionCard = document.getElementById('question-card');
const questionWord = document.getElementById('question-word');
const questionDirection = document.getElementById('question-direction');
const questionNumber = document.getElementById('question-number');
const optionsContainer = document.getElementById('options-container');
const feedbackSection = document.getElementById('feedback-section');
const summary = document.getElementById('summary');
const noWords = document.getElementById('no-words');
const loaderEl = document.getElementById('ajax-loader');

function showLoader(show) {
    if (!loaderEl) return;
    loaderEl.classList.toggle('hidden', !show);
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function html(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function pct(a, b) {
    return Math.round((a / (b || 1)) * 100);
}

function saveState() {
    const payload = {
        queue: state.queue,
        stats: state.stats,
        answered: Array.from(state.answered),
        totalCount: state.totalCount,
    };

    fetch(STATE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({ state: payload }),
    }).catch(console.error);
}

function updateProgress() {
    document.getElementById('progress-label').textContent = `${state.stats.total} / ${state.totalCount}`;
    document.getElementById('correct-label').textContent = state.stats.correct;
    document.getElementById('wrong-label').textContent = state.stats.wrong;
    const percent = state.stats.total ? pct(state.stats.correct, state.stats.total) : 0;
    document.getElementById('score-label').textContent = `${percent}%`;
    document.getElementById('progress-bar').style.width = `${(state.stats.total / state.totalCount) * 100}%`;
}

function getRandomOptions(currentWord, questionType) {
    const others = state.words.filter(w => w.id !== currentWord.id);
    const shuffled = shuffle([...others]).slice(0, 4);
    
    let options;
    if (questionType === 'en_to_uk') {
        options = shuffled.map(w => w.translation);
        options.push(currentWord.translation);
    } else {
        options = shuffled.map(w => w.word);
        options.push(currentWord.word);
    }
    
    return shuffle(options);
}

function renderQuestion() {
    if (state.queue.length === 0 || (state.stats.total >= state.totalCount && state.totalCount > 0)) {
        showSummary();
        return;
    }

    const wordId = state.queue.shift();
    const word = state.words.find(w => w.id === wordId);
    
    if (!word) {
        renderQuestion();
        return;
    }

    state.current = word;
    state.questionType = Math.random() < 0.5 ? 'en_to_uk' : 'uk_to_en';
    state.currentOptions = getRandomOptions(word, state.questionType);

    // Update UI
    questionCard.classList.remove('hidden');
    summary.classList.add('hidden');
    feedbackSection.classList.add('hidden');
    feedbackSection.innerHTML = '';

    if (state.questionType === 'en_to_uk') {
        questionDirection.textContent = 'EN → UK';
        questionWord.textContent = word.word;
    } else {
        questionDirection.textContent = 'UK → EN';
        questionWord.textContent = word.translation;
    }

    questionNumber.textContent = state.stats.total + 1;

    // Render options
    optionsContainer.innerHTML = state.currentOptions.map((opt, i) => `
        <button type="button" 
                class="option-btn group relative w-full text-left px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border-2 border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-[15px] sm:text-base"
                data-option="${html(opt)}"
                title="Натисни ${i + 1}">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-xl border-2 font-bold text-sm text-gray-400 group-hover:text-indigo-500 transition-colors">
                    ${i + 1}
                </span>
                <span class="flex-1">${html(opt)}</span>
            </div>
        </button>
    `).join('');

    // Add click handlers
    optionsContainer.querySelectorAll('.option-btn').forEach(btn => {
        btn.addEventListener('click', () => onChoose(btn.dataset.option));
    });

    saveState();
}

async function onChoose(answer) {
    if (!state.current) return;

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
                word_id: state.current.id,
                answer: answer,
                questionType: state.questionType,
            }),
        });

        const result = await response.json();

        state.stats.total++;
        if (result.isCorrect) {
            state.stats.correct++;
        } else {
            state.stats.wrong++;
            // Add back to queue for retry later
            state.queue.push(state.current.id);
        }

        state.answered.add(state.current.id);

        // Show feedback
        showFeedback(result);
        updateProgress();
        saveState();

    } catch (error) {
        console.error('Error checking answer:', error);
    } finally {
        showLoader(false);
    }
}

function showFeedback(result) {
    feedbackSection.classList.remove('hidden');
    
    if (result.isCorrect) {
        feedbackSection.innerHTML = `
            <div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-emerald-800">Правильно! ✓</div>
                    <div class="text-sm text-emerald-700 mt-1">
                        <b>${html(result.word)}</b> = <b>${html(result.translation)}</b>
                    </div>
                </div>
            </div>
        `;
    } else {
        feedbackSection.innerHTML = `
            <div class="flex items-start gap-3 p-3 sm:p-4 rounded-2xl bg-gradient-to-r from-red-50 to-rose-50 border-2 border-red-200">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-red-800">Неправильно</div>
                    <div class="text-sm text-red-700 mt-1">
                        Твоя відповідь: <b>${html(result.userAnswer)}</b><br>
                        Правильна відповідь: <b>${html(result.correctAnswer)}</b>
                    </div>
                </div>
            </div>
        `;
    }

    // Auto-proceed to next question after delay
    setTimeout(() => {
        renderQuestion();
    }, 1500);
}

function showSummary() {
    questionCard.classList.add('hidden');
    feedbackSection.classList.add('hidden');
    summary.classList.remove('hidden');

    const percent = state.stats.total ? pct(state.stats.correct, state.stats.total) : 0;
    document.getElementById('summary-text').textContent = 
        `Ти відповів правильно на ${state.stats.correct} з ${state.stats.total} питань (${percent}%)!`;

    document.getElementById('retry').addEventListener('click', restart);
}

async function restart() {
    showLoader(true);

    try {
        await fetch(RESET_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
        });

        init(true);
    } catch (error) {
        console.error('Error resetting test:', error);
    } finally {
        showLoader(false);
    }
}

function init(fresh = false) {
    state.words = [...WORDS_DATA];

    if (state.words.length === 0) {
        noWords.classList.remove('hidden');
        questionCard.classList.add('hidden');
        summary.classList.add('hidden');
        return;
    }

    noWords.classList.add('hidden');

    // Restore state if available and not fresh start
    if (!fresh && SAVED_STATE && SAVED_STATE.queue) {
        state.queue = SAVED_STATE.queue;
        state.stats = SAVED_STATE.stats || { correct: 0, wrong: 0, total: 0 };
        state.answered = new Set(SAVED_STATE.answered || []);
        state.totalCount = SAVED_STATE.totalCount || state.words.length;
    } else {
        // Fresh start
        state.queue = shuffle(state.words.map(w => w.id));
        state.stats = { correct: 0, wrong: 0, total: 0 };
        state.answered = new Set();
        state.totalCount = state.words.length;
    }

    updateProgress();
    renderQuestion();
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    const n = Number(e.key);
    if (!Number.isInteger(n) || n < 1 || n > state.currentOptions.length) return;
    
    const opt = state.currentOptions[n - 1];
    if (!opt) return;
    
    onChoose(opt);
});

// Restart button
document.getElementById('restart-test').addEventListener('click', restart);

// Initialize
init();
</script>
@endsection
