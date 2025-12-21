@extends('layouts.engram')

@section('title', 'Тест слів — Gramlyze')

@section('content')
<div class="space-y-8" id="word-test-app">
    <div class="bg-card/70 backdrop-blur border border-border rounded-3xl shadow-soft p-6 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <p class="text-sm uppercase tracking-wide text-primary font-semibold">Лексика</p>
                <h1 class="text-3xl sm:text-4xl font-bold">Тест слів</h1>
                <p class="text-muted-foreground max-w-2xl">Перевірте, наскільки впевнено ви перекладаєте слова між українською та англійською. Прогрес зберігається навіть після оновлення сторінки.</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="restart-btn" class="inline-flex items-center gap-2 rounded-full bg-secondary px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lg">
                    <span>Почати спочатку</span>
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-border bg-background/70 p-4 shadow-sm">
                <p class="text-sm text-muted-foreground">Відповіли</p>
                <div class="mt-1 text-2xl font-bold"><span id="answered-count">0</span>/<span id="total-count">0</span></div>
            </div>
            <div class="rounded-2xl border border-border bg-background/70 p-4 shadow-sm">
                <p class="text-sm text-muted-foreground">Правильні</p>
                <div class="mt-1 flex items-baseline gap-2 text-2xl font-bold text-success"><span id="correct-count">0</span></div>
            </div>
            <div class="rounded-2xl border border-border bg-background/70 p-4 shadow-sm">
                <p class="text-sm text-muted-foreground">Помилки</p>
                <div class="mt-1 flex items-baseline gap-2 text-2xl font-bold text-destructive"><span id="wrong-count">0</span></div>
            </div>
            <div class="rounded-2xl border border-border bg-background/70 p-4 shadow-sm">
                <p class="text-sm text-muted-foreground">Точність</p>
                <div class="mt-1 flex items-baseline gap-2 text-2xl font-bold"><span id="percentage">0</span>%</div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
                <span>Прогрес</span>
                <span id="progress-label">0%</span>
            </div>
            <div class="h-2 rounded-full bg-muted overflow-hidden">
                <div id="progress-bar" class="h-full w-0 rounded-full bg-primary transition-all duration-500"></div>
            </div>
        </div>
    </div>

    <div id="question-wrapper" class="bg-card border border-border rounded-3xl shadow-soft p-6 sm:p-8">
        <div id="question-content" class="space-y-6">
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span class="inline-flex items-center justify-center rounded-full bg-primary/10 text-primary px-3 py-1 font-semibold">Питання</span>
                <span id="question-type" class="font-medium"></span>
            </div>

            <div>
                <p id="question-label" class="text-lg text-muted-foreground"></p>
                <h2 id="question-text" class="mt-2 text-3xl sm:text-4xl font-bold"></h2>
            </div>

            <div id="options" class="grid gap-3 sm:grid-cols-2"></div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div id="feedback" class="hidden text-sm font-medium"></div>
                <div class="flex gap-3">
                    <button id="next-btn" class="hidden rounded-xl border border-border px-4 py-2 font-semibold text-foreground transition hover:-translate-y-0.5 hover:shadow-soft">Наступне</button>
                    <button id="submit-btn" class="rounded-xl bg-primary px-5 py-3 font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">Перевірити</button>
                </div>
            </div>
        </div>

        <div id="completion" class="hidden text-center space-y-4">
            <div class="inline-flex items-center justify-center rounded-full bg-primary/10 px-4 py-2 text-primary font-semibold">Тест завершено</div>
            <h2 class="text-3xl font-bold">Ви пройшли всі слова!</h2>
            <p class="text-muted-foreground">Спробуйте ще раз, щоб покращити результат.</p>
            <div class="flex justify-center">
                <button id="restart-btn-bottom" class="inline-flex items-center gap-2 rounded-full bg-secondary px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lg">
                    <span>Перезапустити</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
  const initialState = @json($state);
  const routes = {
    state: '{{ url('/words/test/state') }}',
    answer: '{{ url('/words/test/answer') }}',
    reset: '{{ url('/words/test/reset') }}',
  };
  const csrfToken = '{{ csrf_token() }}';

  document.addEventListener('DOMContentLoaded', () => {
    const answeredEl = document.getElementById('answered-count');
    const totalCountEl = document.getElementById('total-count');
    const correctEl = document.getElementById('correct-count');
    const wrongEl = document.getElementById('wrong-count');
    const percentageEl = document.getElementById('percentage');
    const progressLabel = document.getElementById('progress-label');
    const progressBar = document.getElementById('progress-bar');
    const questionLabel = document.getElementById('question-label');
    const questionText = document.getElementById('question-text');
    const questionType = document.getElementById('question-type');
    const optionsWrapper = document.getElementById('options');
    const submitBtn = document.getElementById('submit-btn');
    const nextBtn = document.getElementById('next-btn');
    const feedbackEl = document.getElementById('feedback');
    const questionContent = document.getElementById('question-content');
    const completionBlock = document.getElementById('completion');
    const restartButtons = [document.getElementById('restart-btn'), document.getElementById('restart-btn-bottom')];

    let currentQuestion = null;
    let selectedAnswer = null;
    let pendingState = initialState;
    let feedbackTimeout = null;

    const updateStats = (state) => {
      answeredEl.textContent = state.stats.total;
      totalCountEl.textContent = state.totalCount;
      correctEl.textContent = state.stats.correct;
      wrongEl.textContent = state.stats.wrong;
      percentageEl.textContent = state.percentage;
      const total = state.totalCount || 1;
      const progressValue = Math.min(100, Math.round((state.stats.total / total) * 100));
      progressLabel.textContent = `${progressValue}%`;
      progressBar.style.width = `${progressValue}%`;
    };

    const renderCompletion = () => {
      questionContent.classList.add('hidden');
      completionBlock.classList.remove('hidden');
    };

    const renderQuestion = (question) => {
      currentQuestion = question;
      selectedAnswer = null;
      submitBtn.disabled = true;
      nextBtn.classList.add('hidden');
      feedbackEl.classList.add('hidden');
      feedbackEl.textContent = '';

      questionContent.classList.remove('hidden');
      completionBlock.classList.add('hidden');

      questionLabel.textContent = question.promptLabel;
      questionText.textContent = question.prompt;
      questionType.textContent = question.questionType === 'en_to_uk' ? 'EN → UK' : 'UK → EN';

      optionsWrapper.innerHTML = '';
      question.options.forEach((option, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'w-full rounded-2xl border border-border bg-background px-4 py-3 text-left font-semibold transition hover:-translate-y-0.5 hover:shadow-soft focus:outline-none focus:ring-2 focus:ring-primary';
        button.textContent = option;
        button.dataset.value = option;

        button.addEventListener('click', () => {
          selectedAnswer = option;
          submitBtn.disabled = false;
          Array.from(optionsWrapper.children).forEach((child) => child.classList.remove('ring-2', 'ring-primary'));
          button.classList.add('ring-2', 'ring-primary');
        });

        optionsWrapper.appendChild(button);
      });
    };

    const applyState = (state) => {
      pendingState = state;
      updateStats(state);

      if (state.completed || !state.question) {
        renderCompletion();
        return;
      }

      renderQuestion(state.question);
    };

    const showFeedback = (isCorrect, correctAnswer) => {
      feedbackEl.classList.remove('hidden');
      feedbackEl.textContent = isCorrect ? 'Вірно! Гарна робота.' : `Невірно. Правильна відповідь: ${correctAnswer}`;
      feedbackEl.className = isCorrect
        ? 'text-sm font-semibold text-success'
        : 'text-sm font-semibold text-destructive';
    };

    const submitAnswer = async () => {
      if (!currentQuestion || !selectedAnswer) {
        return;
      }

      submitBtn.disabled = true;

      const response = await fetch(routes.answer, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          word_id: currentQuestion.word_id,
          answer: selectedAnswer,
          questionType: currentQuestion.questionType,
        }),
      });

      if (!response.ok) {
        feedbackEl.classList.remove('hidden');
        feedbackEl.className = 'text-sm font-semibold text-destructive';
        feedbackEl.textContent = 'Сталася помилка. Оновіть сторінку і спробуйте ще раз.';
        submitBtn.disabled = false;
        return;
      }

      const result = await response.json();
      const nextState = {
        question: result.question,
        stats: result.stats,
        percentage: result.percentage,
        totalCount: result.totalCount,
        completed: result.completed,
      };

      showFeedback(result.isCorrect, result.correctAnswer);
      updateStats(nextState);

      if (feedbackTimeout) {
        clearTimeout(feedbackTimeout);
      }

      if (result.completed) {
        renderCompletion();
        return;
      }

      nextBtn.classList.remove('hidden');

      feedbackTimeout = setTimeout(() => applyState(nextState), 800);

      nextBtn.onclick = () => {
        if (feedbackTimeout) {
          clearTimeout(feedbackTimeout);
        }
        applyState(nextState);
      };
    };

    const fetchState = async () => {
      const response = await fetch(routes.state, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) return;
      const data = await response.json();
      applyState(data);
    };

    const resetProgress = async () => {
      await fetch(routes.reset, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      });

      fetchState();
    };

    submitBtn.addEventListener('click', submitAnswer);
    restartButtons.forEach((button) => button.addEventListener('click', resetProgress));

    applyState(pendingState);
  });
</script>
@endsection
