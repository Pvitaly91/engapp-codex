@extends('layouts.engram')

@section('title', 'Тест слів — Gramlyze')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8" data-animate>
        <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-2">Тест слів</h1>
        <p class="text-muted-foreground">Перевірте свій словниковий запас англійської мови</p>
    </div>

    <!-- Stats Card -->
    <div id="stats-card" class="bg-card border border-border rounded-3xl p-6 mb-6 shadow-soft" data-animate data-animate-delay="100">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-sm text-muted-foreground mb-1">Прогрес</div>
                <div class="text-2xl font-bold text-foreground">
                    <span id="answered-count">{{ $stats['total'] }}</span>/<span id="total-count">{{ $totalCount }}</span>
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-muted-foreground mb-1">Правильно</div>
                <div class="text-2xl font-bold text-success" id="correct-count">{{ $stats['correct'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-muted-foreground mb-1">Помилок</div>
                <div class="text-2xl font-bold text-destructive" id="wrong-count">{{ $stats['wrong'] }}</div>
            </div>
            <div class="text-center">
                <div class="text-sm text-muted-foreground mb-1">Відсоток</div>
                <div class="text-2xl font-bold text-primary" id="percentage">{{ $percentage }}%</div>
            </div>
        </div>
        <!-- Progress Bar -->
        <div class="mt-4 h-2 bg-muted rounded-full overflow-hidden">
            <div id="progress-bar" class="h-full bg-gradient-to-r from-primary to-secondary transition-all duration-500" style="width: {{ $totalCount > 0 ? ($stats['total'] / $totalCount) * 100 : 0 }}%"></div>
        </div>
    </div>

    <!-- Question Card -->
    <div id="question-card" class="bg-card border border-border rounded-3xl p-8 shadow-soft" data-animate data-animate-delay="200">
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent mb-4"></div>
            <p class="text-muted-foreground">Завантаження питання...</p>
        </div>

        <!-- Question Content -->
        <div id="question-content" class="hidden">
            <div class="text-center mb-8">
                <div id="question-type-label" class="text-sm text-muted-foreground mb-2"></div>
                <div id="question-text" class="text-3xl sm:text-4xl font-bold text-foreground"></div>
            </div>

            <input type="hidden" id="current-word-id" value="">
            <input type="hidden" id="current-question-type" value="">

            <!-- Options -->
            <div id="options-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6"></div>

            <!-- Submit Button -->
            <button id="submit-btn" class="w-full py-4 rounded-2xl font-semibold text-lg transition-all duration-200 bg-primary text-primary-foreground hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Перевірити
            </button>
        </div>

        <!-- Feedback -->
        <div id="feedback-container" class="hidden">
            <div id="feedback-content" class="text-center py-8"></div>
        </div>

        <!-- Completed State -->
        <div id="completed-state" class="{{ $isCompleted ? '' : 'hidden' }}">
            <div class="text-center py-8">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-2xl font-bold text-foreground mb-2">Тест завершено!</h2>
                <p class="text-muted-foreground mb-6">Ви відповіли на всі питання</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button id="restart-btn-completed" class="px-8 py-3 rounded-2xl font-semibold bg-primary text-primary-foreground hover:opacity-90 transition-all">
                        Почати знову
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restart Button -->
    <div class="mt-6 text-center" data-animate data-animate-delay="300">
        <button id="restart-btn" class="px-6 py-2 rounded-xl text-sm font-medium text-muted-foreground border border-border hover:border-primary hover:text-primary transition-all">
            Скинути прогрес
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const csrfToken = '{{ csrf_token() }}';
    
    // DOM Elements
    const statsCard = document.getElementById('stats-card');
    const answeredCount = document.getElementById('answered-count');
    const totalCount = document.getElementById('total-count');
    const correctCount = document.getElementById('correct-count');
    const wrongCount = document.getElementById('wrong-count');
    const percentage = document.getElementById('percentage');
    const progressBar = document.getElementById('progress-bar');
    
    const loadingState = document.getElementById('loading-state');
    const questionContent = document.getElementById('question-content');
    const questionTypeLabel = document.getElementById('question-type-label');
    const questionText = document.getElementById('question-text');
    const optionsContainer = document.getElementById('options-container');
    const submitBtn = document.getElementById('submit-btn');
    const currentWordId = document.getElementById('current-word-id');
    const currentQuestionType = document.getElementById('current-question-type');
    
    const feedbackContainer = document.getElementById('feedback-container');
    const feedbackContent = document.getElementById('feedback-content');
    const completedState = document.getElementById('completed-state');
    
    const restartBtn = document.getElementById('restart-btn');
    const restartBtnCompleted = document.getElementById('restart-btn-completed');

    let selectedOption = null;
    let isSubmitting = false;

    // Update stats display
    function updateStats(stats, perc, total, answered) {
        correctCount.textContent = stats.correct;
        wrongCount.textContent = stats.wrong;
        answeredCount.textContent = answered;
        totalCount.textContent = total;
        percentage.textContent = perc + '%';
        
        const progressPercent = total > 0 ? (answered / total) * 100 : 0;
        progressBar.style.width = progressPercent + '%';
    }

    // Render question
    function renderQuestion(question) {
        loadingState.classList.add('hidden');
        feedbackContainer.classList.add('hidden');
        completedState.classList.add('hidden');
        questionContent.classList.remove('hidden');

        currentWordId.value = question.word_id;
        currentQuestionType.value = question.question_type;

        if (question.question_type === 'en_to_uk') {
            questionTypeLabel.textContent = 'Оберіть правильний переклад українською:';
        } else {
            questionTypeLabel.textContent = 'Оберіть правильне англійське слово:';
        }

        questionText.textContent = question.question_text;

        // Render options
        optionsContainer.innerHTML = '';
        selectedOption = null;
        submitBtn.disabled = true;

        question.options.forEach((option, index) => {
            const optionBtn = document.createElement('button');
            optionBtn.type = 'button';
            optionBtn.className = 'option-btn p-4 rounded-2xl border-2 border-border text-left font-medium transition-all duration-200 hover:border-primary hover:bg-muted';
            optionBtn.textContent = option;
            optionBtn.dataset.value = option;
            
            optionBtn.addEventListener('click', function() {
                // Remove selection from all options
                optionsContainer.querySelectorAll('.option-btn').forEach(btn => {
                    btn.classList.remove('border-primary', 'bg-primary/10');
                    btn.classList.add('border-border');
                });
                
                // Select this option
                this.classList.remove('border-border');
                this.classList.add('border-primary', 'bg-primary/10');
                selectedOption = this.dataset.value;
                submitBtn.disabled = false;
            });

            optionsContainer.appendChild(optionBtn);
        });
    }

    // Show completed state
    function showCompleted() {
        loadingState.classList.add('hidden');
        questionContent.classList.add('hidden');
        feedbackContainer.classList.add('hidden');
        completedState.classList.remove('hidden');
    }

    // Show feedback
    function showFeedback(result, nextQuestion) {
        questionContent.classList.add('hidden');
        feedbackContainer.classList.remove('hidden');

        let html = '';
        if (result.isCorrect) {
            html = `
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-2xl font-bold text-success mb-2">Правильно!</h3>
                <p class="text-muted-foreground">
                    <strong>${escapeHtml(result.word)}</strong> = <strong>${escapeHtml(result.translation)}</strong>
                </p>
            `;
        } else {
            html = `
                <div class="text-6xl mb-4">❌</div>
                <h3 class="text-2xl font-bold text-destructive mb-2">Неправильно</h3>
                <p class="text-muted-foreground mb-2">
                    Ваша відповідь: <strong>${escapeHtml(result.userAnswer)}</strong>
                </p>
                <p class="text-foreground">
                    Правильна відповідь: <strong class="text-success">${escapeHtml(result.correctAnswer)}</strong>
                </p>
                <p class="text-sm text-muted-foreground mt-2">
                    ${escapeHtml(result.word)} = ${escapeHtml(result.translation)}
                </p>
            `;
        }

        feedbackContent.innerHTML = html;

        // Update stats
        updateStats(result.stats, result.percentage, result.totalCount, result.answeredCount);

        // Auto-advance to next question after delay
        setTimeout(() => {
            if (result.completed) {
                showCompleted();
            } else if (nextQuestion) {
                renderQuestion(nextQuestion);
            }
        }, result.isCorrect ? 800 : 1500);
    }

    // Load initial state
    async function loadState() {
        loadingState.classList.remove('hidden');
        questionContent.classList.add('hidden');
        feedbackContainer.classList.add('hidden');

        try {
            const response = await fetch('{{ route("public.words.test.state") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            updateStats(data.stats, data.percentage, data.totalCount, data.answeredCount);

            if (data.completed) {
                showCompleted();
            } else {
                renderQuestion(data.question);
            }
        } catch (error) {
            console.error('Failed to load state:', error);
            feedbackContent.innerHTML = '<p class="text-destructive">Помилка завантаження. Спробуйте оновити сторінку.</p>';
            feedbackContainer.classList.remove('hidden');
            loadingState.classList.add('hidden');
        }
    }

    // Submit answer
    async function submitAnswer() {
        if (!selectedOption || isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Перевірка...';

        try {
            const response = await fetch('{{ route("public.words.test.answer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    word_id: currentWordId.value,
                    answer: selectedOption,
                    question_type: currentQuestionType.value,
                })
            });

            const data = await response.json();

            if (response.ok) {
                showFeedback(data, data.nextQuestion);
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Failed to submit answer:', error);
            alert('Помилка при відправці відповіді. Спробуйте ще раз.');
        } finally {
            isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Перевірити';
        }
    }

    // Reset test
    async function resetTest() {
        try {
            const response = await fetch('{{ route("public.words.test.reset") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                // Reload the page to start fresh
                window.location.reload();
            } else {
                throw new Error('Reset failed');
            }
        } catch (error) {
            console.error('Failed to reset:', error);
            alert('Помилка при скиданні тесту. Спробуйте ще раз.');
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Event listeners
    submitBtn.addEventListener('click', submitAnswer);
    restartBtn.addEventListener('click', resetTest);
    restartBtnCompleted.addEventListener('click', resetTest);

    // Allow Enter key to submit
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !submitBtn.disabled && questionContent.classList.contains('hidden') === false) {
            submitAnswer();
        }
    });

    // Initial load - check if completed from server
    @if($isCompleted)
        showCompleted();
    @else
        loadState();
    @endif
})();
</script>
@endsection
