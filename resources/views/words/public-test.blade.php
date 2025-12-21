@extends('layouts.engram')

@section('title', 'Тест слів — Gramlyze')

@section('content')
<style>
    /* Smooth animations */
    .fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
    .fade-out {
        animation: fadeOut 0.3s ease-out forwards;
    }
    .slide-up {
        animation: slideUp 0.4s ease-out forwards;
    }
    .scale-in {
        animation: scaleIn 0.3s ease-out forwards;
    }
    .bounce-in {
        animation: bounceIn 0.5s ease-out forwards;
    }
    .shake {
        animation: shake 0.5s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    /* Option button states */
    .option-btn {
        transition: all 0.2s ease-out;
    }
    .option-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .option-btn:active:not(:disabled) {
        transform: translateY(0);
    }
    .option-btn.correct {
        background: hsl(var(--success) / 0.15) !important;
        border-color: hsl(var(--success)) !important;
        color: hsl(var(--success)) !important;
    }
    .option-btn.wrong {
        background: hsl(var(--destructive) / 0.15) !important;
        border-color: hsl(var(--destructive)) !important;
        color: hsl(var(--destructive)) !important;
    }
    .option-btn:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    /* Stats counter animation */
    .stat-update {
        animation: statPulse 0.3s ease-out;
    }
    @keyframes statPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8 slide-up" style="animation-delay: 0s">
        <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-2">Тест слів</h1>
        <p class="text-muted-foreground">Перевірте свій словниковий запас англійської мови</p>
    </div>

    <!-- Stats Card -->
    <div id="stats-card" class="bg-card border border-border rounded-3xl p-6 mb-6 shadow-soft slide-up" style="animation-delay: 0.1s">
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
            <div id="progress-bar" class="h-full bg-gradient-to-r from-primary to-secondary transition-all duration-500 ease-out" style="width: {{ $totalCount > 0 ? ($stats['total'] / $totalCount) * 100 : 0 }}%"></div>
        </div>
    </div>

    <!-- Question Card -->
    <div id="question-card" class="bg-card border border-border rounded-3xl p-8 shadow-soft slide-up" style="animation-delay: 0.2s">
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
            <div id="options-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
        </div>

        <!-- Feedback -->
        <div id="feedback-container" class="hidden">
            <div id="feedback-content" class="text-center py-8"></div>
        </div>

        <!-- Completed State -->
        <div id="completed-state" class="{{ $isCompleted ? '' : 'hidden' }}">
            <div class="text-center py-8">
                <div class="text-6xl mb-4 bounce-in">🎉</div>
                <h2 class="text-2xl font-bold text-foreground mb-2">Тест завершено!</h2>
                <p class="text-muted-foreground mb-6">Ви відповіли на всі питання</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button id="restart-btn-completed" class="px-8 py-3 rounded-2xl font-semibold bg-primary text-primary-foreground hover:opacity-90 transition-all duration-200 hover:scale-105">
                        Почати знову
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restart Button -->
    <div class="mt-6 text-center slide-up" style="animation-delay: 0.3s">
        <button id="restart-btn" class="px-6 py-2 rounded-xl text-sm font-medium text-muted-foreground border border-border hover:border-primary hover:text-primary transition-all duration-200">
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
    const currentWordId = document.getElementById('current-word-id');
    const currentQuestionType = document.getElementById('current-question-type');
    
    const feedbackContainer = document.getElementById('feedback-container');
    const feedbackContent = document.getElementById('feedback-content');
    const completedState = document.getElementById('completed-state');
    
    const restartBtn = document.getElementById('restart-btn');
    const restartBtnCompleted = document.getElementById('restart-btn-completed');

    let isSubmitting = false;

    // Animate stat update
    function animateStatUpdate(element) {
        element.classList.remove('stat-update');
        void element.offsetWidth; // Trigger reflow
        element.classList.add('stat-update');
    }

    // Update stats display with animation
    function updateStats(stats, perc, total, answered) {
        if (parseInt(correctCount.textContent) !== stats.correct) {
            correctCount.textContent = stats.correct;
            animateStatUpdate(correctCount);
        }
        if (parseInt(wrongCount.textContent) !== stats.wrong) {
            wrongCount.textContent = stats.wrong;
            animateStatUpdate(wrongCount);
        }
        answeredCount.textContent = answered;
        totalCount.textContent = total;
        percentage.textContent = perc + '%';
        
        const progressPercent = total > 0 ? (answered / total) * 100 : 0;
        progressBar.style.width = progressPercent + '%';
    }

    // Render question with animation
    function renderQuestion(question, animate = true) {
        loadingState.classList.add('hidden');
        feedbackContainer.classList.add('hidden');
        completedState.classList.add('hidden');
        questionContent.classList.remove('hidden');

        if (animate) {
            questionContent.classList.remove('fade-in');
            void questionContent.offsetWidth;
            questionContent.classList.add('fade-in');
        }

        currentWordId.value = question.word_id;
        currentQuestionType.value = question.question_type;

        if (question.question_type === 'en_to_uk') {
            questionTypeLabel.textContent = 'Оберіть правильний переклад українською:';
        } else {
            questionTypeLabel.textContent = 'Оберіть правильне англійське слово:';
        }

        questionText.textContent = question.question_text;

        // Render options with staggered animation
        optionsContainer.innerHTML = '';

        question.options.forEach((option, index) => {
            const optionBtn = document.createElement('button');
            optionBtn.type = 'button';
            optionBtn.className = 'option-btn p-4 rounded-2xl border-2 border-border text-left font-medium bg-card';
            optionBtn.textContent = option;
            optionBtn.dataset.value = option;
            
            if (animate) {
                optionBtn.style.opacity = '0';
                optionBtn.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    optionBtn.style.transition = 'all 0.3s ease-out';
                    optionBtn.style.opacity = '1';
                    optionBtn.style.transform = 'translateY(0)';
                }, index * 80);
            }
            
            optionBtn.addEventListener('click', function() {
                if (isSubmitting) return;
                submitAnswer(this.dataset.value, this);
            });

            optionsContainer.appendChild(optionBtn);
        });
    }

    // Show completed state with animation
    function showCompleted() {
        loadingState.classList.add('hidden');
        questionContent.classList.add('hidden');
        feedbackContainer.classList.add('hidden');
        completedState.classList.remove('hidden');
        completedState.classList.add('fade-in');
    }

    // Show inline feedback on options
    function showInlineFeedback(result, clickedBtn, nextQuestion) {
        // Disable all options
        const allOptions = optionsContainer.querySelectorAll('.option-btn');
        allOptions.forEach(btn => {
            btn.disabled = true;
        });
        
        // Find the correct answer button and mark it
        allOptions.forEach(btn => {
            if (btn.dataset.value === result.correctAnswer) {
                btn.classList.add('correct');
                if (result.isCorrect) {
                    btn.classList.add('scale-in');
                }
            }
        });
        
        // Mark clicked button as wrong if incorrect
        if (!result.isCorrect) {
            clickedBtn.classList.add('wrong', 'shake');
        }

        // Update stats
        updateStats(result.stats, result.percentage, result.totalCount, result.answeredCount);

        // Auto-advance to next question after delay
        const delay = result.isCorrect ? 600 : 1200;
        setTimeout(() => {
            if (result.completed) {
                showCompleted();
            } else if (nextQuestion) {
                renderQuestion(nextQuestion, true);
            }
        }, delay);
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
                renderQuestion(data.question, true);
            }
        } catch (error) {
            console.error('Failed to load state:', error);
            feedbackContent.innerHTML = '<p class="text-destructive">Помилка завантаження. Спробуйте оновити сторінку.</p>';
            feedbackContainer.classList.remove('hidden');
            loadingState.classList.add('hidden');
        }
    }

    // Submit answer immediately on option click
    async function submitAnswer(selectedOption, clickedBtn) {
        if (isSubmitting) return;

        isSubmitting = true;
        
        // Visual feedback that option was clicked
        clickedBtn.classList.add('border-primary', 'bg-primary/10');

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
                showInlineFeedback(data, clickedBtn, data.nextQuestion);
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Failed to submit answer:', error);
            alert('Помилка при відправці відповіді. Спробуйте ще раз.');
            isSubmitting = false;
        } finally {
            // isSubmitting will be reset when new question renders
            setTimeout(() => {
                isSubmitting = false;
            }, 1500);
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
    restartBtn.addEventListener('click', resetTest);
    restartBtnCompleted.addEventListener('click', resetTest);

    // Initial load - check if completed from server
    @if($isCompleted)
        showCompleted();
    @else
        loadState();
    @endif
})();
</script>
@endsection
